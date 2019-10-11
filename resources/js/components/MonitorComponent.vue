
<style>
    .card-header {
        font-size: 18px;
    }
    .search {
      position:relative;
      float:right;
    }
    .badge-danger{
      background-color: rgb(253, 0, 51);
    }
</style>

<template>
<div class="col-md-6">
    <div class="card">
        <div class="card-header">Monitor</div>
        <div class="card-body">
            Upadted: {{ status.age }}
            <br>
            House:
            <span v-html="status.house"></span>
            <br>
            Garage:
            <span v-html="status.garage"></span>
            <br>
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
  data() {
    return {
      responses: {},
      badges: {
        completed: "badge-success",
        working: "badge-light",
        queued: "badge-light",
        failed: "badge-danger"
      },
      status: {
        house: 0,
        garage: 0,
        age: '',
      },
    };
  },
  methods: {
    fetchRecord(){
      var qs = require('qs');
        axios.get('/records').then(response => {

          switch (response.data.garage) {
              case 0:
                  this.status.garage = '<span class="badge badge-secondary">OFF</span>'
                  break;
              case 1:
                  this.status.garage = '<span class="badge badge-danger">ON</span>'
                  break;
          
              default:
                  this.status.garage = '<span class="badge badge-light">-</span>'
                  break;
          }
          switch (response.data.house) {
              case 0:
                  this.status.house = '<span class="badge badge-secondary">OFF</span>'
                  break;
              case 1:
                  this.status.house = '<span class="badge badge-danger">FULL</span>'
                  break;
              case 2:
                  this.status.house = '<span class="badge badge-danger">DAY</span>'
                  break;
              case 3:
                  this.status.house = '<span class="badge badge-danger">NIGHT</span>'
                  break;
          
              default:
                  this.status.house = '<span class="badge badge-light">-</span>'
                  break;
          }
          this.status.age = response.data.age;
        });
    },
  },
  mounted() {
    var that = this
    that.fetchRecord()
    setInterval(function(){ 
      that.fetchRecord()
    }, 5000)
  },
};
</script>

