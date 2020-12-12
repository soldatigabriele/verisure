<style>
  body{
    line-height: 1.4;
  }
  .filters{
    padding-top: 3px;
    padding-bottom: 8px;
  }
  .card-header {
      font-size: 18px;
  }
  .search {
    position:relative;
    float:right;
  }
  .table th{
    padding: 5px;
  }
  .table td{
    padding: 5px;
    vertical-align: middle;
  }
  .table{
    margin-bottom: 0px;
  }
  .id_cell{
    width: 50px;
  }
  .request_cell {
    width: 130px;
  }
  .status_cell {
    width: 55px;
  }
  .date_cell {
    width: 240px;
  }
  .completed_message {
    font-size:12px;
    margin-left: 4px;
    padding: 1px;
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
    td.full:nth-of-type(1):before { content: "#"; }
    td.full:nth-of-type(2):before { content: "Request"; }
    td.full:nth-of-type(3):before { content: "Status"; }
    td.full:nth-of-type(4):before { content: "Body";}
    td.full:nth-of-type(5):before { content: "Date"; }

    td.home:nth-of-type(1):before { content: "#";}
    td.home:nth-of-type(2):before { content: "Body";}
    td.home:nth-of-type(3):before { content: "Date"; }
  }

  #overlay {
    position:absolute;
    top:0px;
    left:0px;
    right:0px;
    bottom:0px;
    background-color:rgba(255, 255, 255, 0.85);
    z-index:9999;
    color:#aeaeae;
    text-align: center;
  }
  .spinner-border{
    position: relative;
    top: 200px;
  }

  .ck-button {
      margin:4px;
      background-color:#38c172;
      color:#fff;
      border-radius:4px;
      border:1px solid #D0D0D0;
      overflow:auto;
      float:left;
  }

  .ck-button:hover {
      background:gray;
  }

  .ck-button label {
      float:left;
      width:5.5em;
  }

  .ck-button label span {
      text-align:center;
      padding:3px 0px 3px 0px;
      display:block;
  }

  .ck-button label input {
      display:none;
  }

  .ck-button input:checked + span {
      background-color:#EFEFEF;
      color: black;
  }

  label {
    display: inline-block;
    margin-bottom: 0.0rem;
  }

  .filters-label {
    float: left;
    position: relative;
    top: 5px;
    margin-right: 10px;
  }
</style>

<template>
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">

        <i class="fas fa-angle-double-down"></i> Responses
        <div v-if="home" class="search">
          <a :href="responses_link">
            <i class="fas fa-search"></i>
          </a>
        </div>

      </div>
      <div class="card-body">

        <div v-if="loading" id="overlay">
          <div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
          </div>
        </div>

        <div v-if="!home" class="filters">

          <div>

            <span>Per page</span>
            <select v-model="axiosparams.per_page">
              <option disabled>13</option>
              <option>10</option>
              <option>20</option>
              <option>30</option>
              <option>50</option>
              <option>100</option>
            </select>
          
            <div class="filters-label">Filters:</div>

            <div class="ck-button">
              <label for="working">
                <input type="checkbox" id="working" value="working" v-model="axiosparams.excluded_statuses">
                <span>working</span>
              </label>
            </div>

            <div class="ck-button">
              <label for="queued">
                <input type="checkbox" id="queued" value="queued" v-model="axiosparams.excluded_statuses">
                <span>queued</span>
              </label>
            </div>

            <div class="ck-button">
              <label for="completed">
                <input type="checkbox" id="completed" value="completed" v-model="axiosparams.excluded_statuses">
                <span>completed</span>
              </label>
            </div>

            <div class="ck-button">
              <label for="failed">
                <input type="checkbox" id="failed" value="failed" v-model="axiosparams.excluded_statuses">
                <span>failed</span>
              </label>
            </div>

            <div class="ck-button">
              <label for="jobs">
                <input type="checkbox" id="jobs" value="jobs" v-model="axiosparams.excluded_statuses">
                <span>jobs</span>
              </label>
            </div>

            <div class="ck-button">
              <label for="login">
                <input type="checkbox" id="login" value="login" v-model="axiosparams.excluded_statuses">
                <span>login</span>
              </label>
            </div>

            <div class="ck-button">
              <label for="logout">
                <input type="checkbox" id="logout" value="logout" v-model="axiosparams.excluded_statuses">
                <span>logout</span>
              </label>
            </div>

          </div>

        </div>

        <table class="table">
          <thead>
            <tr>
              <th class="id_cell">#</th>
              <th v-if="!home" class="request_cell">Request</th>
              <th v-if="!home" class="status_cell">Status</th>
              <th class="boby_cell">Body</th>
              <th class="date_cell">Date</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="response in responses" v-bind:key="response.id">
              <td v-bind:class="{'home': home}" class="id_cell">{{ response.id }}</td>
              <td v-if="!home" v-bind:class="{'full': !home}" class="request_cell full">{{ response.request_type }}</td>
              <td v-if="!home" v-bind:class="{'full': !home}" class="status_cell full">{{ response.status }}</td>
              <td v-bind:class="{'home': home}" class="body_cell full" v-html="parsedBody(response.body)"></td>
              <td v-bind:class="{'home': home}" class="date_cell full">{{ longAgo(response.created_at) }} - {{ formattedDate(response.created_at) }}</td>
            </tr>

            <nav aria-label="navigation" v-if="!home">
              <ul class="pagination">
                <li class="page-item"><a href="#" class="page-link" @click="firstpage()">First</a></li>
                <li class="page-item" v-bind:class="{'disabled': pagination.is_first_page}"><a href="#" class="page-link" @click="decrementpage()"><<</a></li>
                <li class="page-item disabled"><a class="page-link">{{axiosparams.page}}</a></li>
                <li class="page-item" v-bind:class="{'disabled': pagination.is_last_page}"><a href="#" class="page-link" @click="incrementpage()">>></a></li>
                <li class="page-item disabled"><a class="page-link">{{pagination.lastpage}}</a></li>
                <li class="page-item"><a href="#" class="page-link" @click="lastpage()">Last</a></li>
              </ul>
            </nav>

          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import moment from "moment";
import _ from "lodash";
import notify from "bootstrap-notify";
import parser from "cron-parser";

export default {
  props: ['limit', 'hide', 'home', 'responses_link'],
  data() {
    return {
      loading: true,
      responses: [],
      badges: {
        completed: "badge-success",
        working: "badge-light",
        queued: "badge-light",
        failed: "badge-danger"
      },
      pagination: {
        lastpage: 0,
        is_first_page: true,
        is_last_page: false,
      },
      axiosparams: {
        page: 1,
        per_page: this.limit || 30,
        excluded_statuses: [
          "working", "queued", "login", "logout", "jobs"
        ],
      },
      latest_id: 1,
    };
  },
  methods: {
    fetchResponses(){
      this.loading = true
      var qs = require('qs');
        axios.get('/api/responses?latest_id=' + this.latest_id, {
          params: this.axiosparams,
          'paramsSerializer': function(params) {
            return qs.stringify(params, {
              arrayFormat: 'comma',
              encodeValuesOnly: true
            })
          }
        }).then(response => {
          this.loading = false
          // If we got some responses in the data, update all the local responses
          if (Object.keys(response.data.data).length > 0) {
            this.responses = response.data.data
            this.latest_id = response.data.data[0]['id'];
            this.pagination.lastpage = response.data.last_page;
            this.axiosparams.per_page = response.data.per_page;
          }
        });
    },
    formattedDate(date) {
      return moment(date).format("DD/MM/YY HH:mm:ss");
    },
    longAgo(date) {
      return moment(date).fromNow();
    },
    parsedBody(body) {
      if (typeof body.job_id !== "undefined") {
        return '<span class="badge badge-info">requested</span><span class="badge badge-light">job_id: ' + body.job_id + "</span>";
      } else if (typeof body.status !== "undefined") {
        let badge = this.badges[body.status] ? this.badges[body.status] : "badge-danger";
        let message = "";
        if (body.status == "completed") message = body.message.message;
        if (body.status == "failed") message = body.message;
        return '<span class="badge ' + badge + '">' + body.status + "</span>" + '<span class="completed_message">' + message + "</span>";
      } else {
        body = JSON.stringify(body);
        if (body.length > 100) body = body.substring(0, 100) + "...";
        return '<span class="badge badge-light">' + body + "</span>";
      }
      return body;
    },
    incrementpage() {
      this.axiosparams.page++;
    },
    firstpage() {
      this.axiosparams.page = 1;
    },
    lastpage() {
      this.axiosparams.page = this.pagination.lastpage;
    },
    decrementpage() {
      this.axiosparams.page--;
    }
  },
  mounted() {
    if (this.hide) {
      this.axiosparams.excluded_statuses = this.hide.split(',');
    }
    var that = this
    that.fetchResponses()
  },
  watch: {
    "axiosparams.page": {
      handler(newval, oldval) {
        this.loading = true;
        this.pagination.is_first_page = false;
        this.pagination.is_last_page = false;
        if (this.axiosparams.page <= 1) {
          this.pagination.is_first_page = true;
        }
        if (this.axiosparams.page >= this.pagination.lastpage) {
          this.pagination.is_last_page = true;
        }

        axios.get('/api/responses?page=' + this.axiosparams.page, {
          params: this.axiosparams
        }).then(response => {
          this.loading = false;
          this.responses = response.data.data;
        });
      }
    },
    "axiosparams": {
      deep: true,
      handler(newval, oldval) {
        this.latest_id = 1
        this.fetchResponses()
      }
    }
  }
};
</script>

