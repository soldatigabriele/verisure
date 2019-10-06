<style>
    .card-header{
        font-size: 18px;
    }
</style>

<template>
  <div>

    <div class="card" style="margin: 0px 20px;">
      <div class="card-header">
        <i class="fas fa-angle-double-down"></i> Responses
      </div>
      <div class="card-body">
        <span>Per page</span>
        <select v-model="axiosparams.per_page">
          <option disabled>13</option>
          <option>10</option>
          <option>20</option>
          <option>30</option>
          <option>50</option>
          <option>100</option>
        </select>

        Hide statuses:
        <input type="checkbox" id="working" value="working" v-model="axiosparams.excluded_statuses">
        <label for="working">working</label>
        <input type="checkbox" id="queued" value="queued" v-model="axiosparams.excluded_statuses">
        <label for="queued">queued</label>
        <input type="checkbox" id="completed" value="completed" v-model="axiosparams.excluded_statuses">
        <label for="completed">completed</label>
        <input type="checkbox" id="failed" value="failed" v-model="axiosparams.excluded_statuses">
        <label for="failed">failed</label>
        
        <input type="checkbox" id="jobs" value="jobs" v-model="axiosparams.excluded_statuses">
        <label for="jobs">jobs</label>

        <input type="checkbox" id="login" value="login" v-model="axiosparams.excluded_statuses">
        <label for="login">login</label>
        <input type="checkbox" id="logout" value="logout" v-model="axiosparams.excluded_statuses">
        <label for="logout">logout</label>
        <!--
        <input type="checkbox" id="requested" value="status request" v-model="axiosparams.excluded_statuses">
        <label for="requested">requested</label>
        <input type="checkbox" id="activate garage" value="activate garage" v-model="axiosparams.excluded_statuses">
        <label for="activate garage">activate garage</label>
        <input type="checkbox" id="deactivate garage" value="deactivate garage" v-model="axiosparams.excluded_statuses">
        <label for="deactivate garage">deactivate garage</label>
        <input type="checkbox" id="activate house" value="activate house" v-model="axiosparams.excluded_statuses">
        <label for="activate house">activate house</label>
        <input type="checkbox" id="deactivate house" value="deactivate house" v-model="axiosparams.excluded_statuses">
        <label for="deactivate house">deactivate house</label>
        -->
        <table class="table">
          <thead>
            <tr>
              <th>#</th>
              <th>Request</th>
              <th>Status</th>
              <th>Body</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="response in responses" v-bind:key="response.id">
              <td>{{ response.id }}</td>
              <td>{{ response.request_type }}</td>
              <td>{{ response.status }}</td>
              <td v-html="parsedBody(response.body)"></td>
              <td>{{ longAgo(response.created_at) }} - {{ formattedDate(response.created_at) }}</td>
            </tr>

            <nav aria-label="Page navigation example">
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

<style>
</style>

<script>
import moment from "moment";
import _ from "lodash";
import notify from "bootstrap-notify";
import parser from "cron-parser";

export default {
  data() {
    return {
      responses: {},
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
        per_page: 13,
        excluded_statuses: [],
      }
    };
  },
  methods: {
    fetchResponses(){
      var qs = require('qs');
        axios.get('/api/responses', {
          params: this.axiosparams,
          'paramsSerializer': function(params) {
            return qs.stringify(params, {
              arrayFormat: 'comma',
              encodeValuesOnly: true
            })
          },

        }).then(response => {
          this.responses = response.data.data;
          this.pagination.lastpage = response.data.last_page;
          this.axiosparams.per_page = response.data.per_page;
        });
    },
    formattedDate(date) {
      return moment(date).format("DD/MM/YYYY HH:mm:ss");
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
        return '<span class="badge ' + badge + '">' + body.status + "</span>" + '<span class="badge badge-light">' + message + "</span>";
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
    this.fetchResponses()
  },
  watch: {
    "axiosparams.page": {
      handler(newval, oldval) {
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
          this.responses = response.data.data;
        });
      }
    },
    "axiosparams": {
      deep: true,
      handler(newval, oldval) {
        this.fetchResponses()
      }
    }
  }
};
</script>

