<template>
  <div class="row justify-content-center">
    <div class="col-md-12">

      <div class="card">
        <div class="card-header">Add Magic Token</div>
        <div class="card-body">

        
            <div class="row token-create">
                <div class="form-group col-md-4">
                    <input type="text" class="form-control" v-model="form.minutes" id="duration" placeholder="duration in minutes" @keyup="parseMinutes()">
                    <small id="duration_in_hours" class="form-text text-muted"></small>
                </div>
                <div class="form-group col-md-4">
                    <select class="form-control" v-model="form.user">
                        <option v-for="user in this.users" v-bind:key="user.id" v-bind:value="user.id">{{user.name}}</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary" @click="submit()">Create</button>
                </div>
            </div>

        </div>
    </div>
    
    <div class="card" v-if="tokens.length">
        <div class="card-header">Magic Tokens</div>
        <div class="card-body">

            <table class="table">
            <thead>
                <tr>
                <th class="">#</th>
                <th class="">Token</th>
                <th class="">User</th>
                <th class="">Expiration</th>
                <th class="">Expiring in</th>
                <th class="">Delete</th>
                <th class="">Share</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="token in tokens" v-bind:key="token.id">
                <td>{{ token.id }}</td>
                <td>{{ token.token }}</td>
                <td>{{ token.user.name }}</td>
                <td>{{ formattedDate(token.expiration_date) }}</td>
                <td>{{ longAgo(token.expiration_date) }}</td>
                <td><button class="btn btn-sm btn-danger" @click="del(token.id)">Delete</button></td>
                <td><button class="btn btn-sm btn-info" @click="share(token.token)">Share</button></td>
                </tr>

            </tbody>
            </table>

        </div>
      </div>
     
    </div>
  </div>
</template>

<style>
.tokens-header{
    font-weight: 600;
}
.token-create{
    margin-bottom:-28px;
}
 @media
  only screen and (max-width: 1000px) {
    table, thead, tbody, th, td, tr {
      display: block;
    }
    thead tr {
      display: none;
    }
    tr { border: 1px solid #ccc; }
    td {
      border: none;
      border-bottom: 1px solid #eee;
      position: relative;
      padding-left: 200px;
      margin-left: 150px;
    }
    td:before {
      position: absolute;
      top: 12px;
      left: 6px;
      width: 200px;
      padding-right: 40px;
      white-space: nowrap;
      margin-left: -150px;
    }
    td:nth-of-type(1):before { content: "#"; }
    td:nth-of-type(2):before { content: "Token"; }
    td:nth-of-type(3):before { content: "User"; }
    td:nth-of-type(4):before { content: "Expiration";}
    td:nth-of-type(5):before { content: "Expiriing"; }
    .token-create{
        margin-bottom:0px;
    }
  }
</style>

<script>
import moment from "moment";
import _ from "lodash";
import notify from "bootstrap-notify";
import parser from "cron-parser";

export default {
  props: ['url'],
  data() {
    return {
        tokens: [],
        users: [],
        form: {
            minutes: 120,
            user: null,
        },
    };
  },
  methods: {
    formattedDate(date) {
      return moment(date).format("DD/MM/YY HH:mm:ss");
    },
    longAgo(date) {
      return moment(date).fromNow();
    },
    parseMinutes(){
        let hours = Math.floor(this.form.minutes/60, 2);
        let result = hours + " hours";
        if (hours >= 24) {
            let days = Math.floor(hours/24);
            hours = hours % 24;
            $('#duration_in_hours').html(days + " days " + hours + " hours");
            return;
        }
        $('#duration_in_hours').html(result);
    },
    submit() {
      axios
        .post("/magic-logins", {
            duration: this.form.minutes,
            user_id: this.form.user,
        })
        .then(
          response => {
            if (response.data.status == "ok") {
                this.fetchTokens()
              $.notify("<strong>" + response.data.message + "</strong>", {
                type: "success",
                newest_on_top: true
              });
              return;
            }
            $.notify(response.data.message, {
              type: "danger",
              newest_on_top: true
            });
          },
          error => {
            $.notify("<strong>Error</strong>", {
              type: "danger",
              newest_on_top: true
            });
          }
        );
    },
    del(id) {
      axios
        .delete("/magic-logins/" + id)
        .then(
          response => {
            if (response.data.status == "ok") {
                this.fetchTokens()
              $.notify("<strong>" + response.data.message + "</strong>", {
                type: "success",
                newest_on_top: true
              });
              return;
            }
            $.notify(response.data.message, {
              type: "danger",
              newest_on_top: true
            });
          },
          error => {
            $.notify("<strong>Error</strong>", {
              type: "danger",
              newest_on_top: true
            });
          }
        );
    },
    share(token) {
      var dummy = document.createElement("input");
      document.body.appendChild(dummy);
      dummy.setAttribute("id", "dummy_id");
      document.getElementById("dummy_id").value=this.url + '/' + token;
      dummy.select();
      document.execCommand("copy");
      document.body.removeChild(dummy);
      $.notify("<strong>Link copied to clipboard</strong>", {
        type: "success",
        newest_on_top: true
      });
    },
    parseCron(key){
        let expression = this.settings[key]
        let cron = parser.parseExpression(expression);
        $('#'+key+'_label').html(cron.next().toString() + "<br>" + cron.next().toString() )
    },
    parseTtl(key){
        let minutes = this.settings[key]/60;
        $('#'+key+'_label').html(minutes + ' hours')
    },
    fetchTokens(){
        axios.get("/magic-logins/all").then(response => {
            this.tokens = response.data
        });
    }
  },
  mounted() {
    this.fetchTokens();
    this.parseMinutes();
    axios.get("/users").then(response => {
      this.users = response.data
      // pre select the user
      this.form.user = response.data[0].id
    });
  }
};
</script>
