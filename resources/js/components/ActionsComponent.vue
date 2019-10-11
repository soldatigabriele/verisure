<style>
.btn-danger {
    color: #fff;
    background-color: rgb(253, 0, 51);
    border-color: rgb(253, 0, 51);
}
</style>
<template>
  <div class="col-md-6">
    <div class="card action-card">
      <div class="card-header">Actions</div>
      <div class="card-body">
        House <br />
        <button v-bind:class="houseFullClass" @click="activate('house', 'full')">Full - <i class="fas fa-lock"></i></button>
        <button v-bind:class="houseDayClass" @click="activate('house', 'day')">Day - <i class="fas fa-sun"></i></button>
        <button v-bind:class="houseNightClass" @click="activate('house', 'night')">Night - <i class="fas fa-moon"></i></button>
        <button v-bind:class="houseDeactivateClass" @click="deactivate('house')">Off - <i class="fas fa-lock-open"></i></button>
        <hr />
        Garage <br />
        <div class="row">
          <div class="col-6">
            <button v-bind:class="garageActivateClass" @click="activate('garage')">On - <i class="fas fa-lock"></i></button>
            <button v-bind:class="garageDeactivateClass" @click="deactivate('garage')">Off - <i class="fas fa-lock-open"></i></button>
          </div>
          <div class="col-6" style="">
            <button class="btn btn-outline-primary" @click="status()">Status</button>
            <button class="btn btn-outline-primary" @click="logout()">Logout</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import _ from "lodash";
import notify from "bootstrap-notify";
import swal from "sweetalert2";
export default {

  data(){
    return {
      garageActivateClass: '',
      garageDeactivateClass: '',
      houseFullClass: '',
      houseNightClass: '',
      houseDayClass: '',
      houseDeactivateClass: '',
    }
  },
  mounted() {
    var that = this
    that.fetchRecord()
    setInterval(function(){ 
      that.fetchRecord()
    }, 5000)
  },
  methods: {
    resetClasses(){
        this.garageActivateClass = 'btn btn-outline-secondary';
        this.garageDeactivateClass = 'btn btn-outline-secondary';

        this.houseDeactivateClass = 'btn btn-outline-secondary';
        this.houseFullClass = 'btn btn-outline-secondary';
        this.houseNightClass = 'btn btn-outline-secondary';
        this.houseDayClass = 'btn btn-outline-secondary';
    },
    status() {
      swal.fire({
        title: "Request status?",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Confirm'
      }).then((result) => {
        if (result.value) {
          axios.get("/status").then(
            response => {
              if (response.status == 200) {
                $.notify("<strong>Status requested successfully</strong>", {
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
        }
      })
    },
    logout() {
      swal.fire({
        title: "Logout?",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Confirm'
      }).then((result) => {
        if (result.value) {
          axios.delete("/sessions").then(
            response => {
              if (response.status == 200) {
                $.notify("<strong>Logged out successfully</strong>", {
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
        }
      })
    },
    activate(system, mode = "") {
      let modeLabel= mode ? " - " + mode + " mode" : mode
      swal.fire({
        title: "Activate " + system + modeLabel,
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Confirm'
      }).then((result) => {
        if (result.value) {
          axios.post("activate/"+system+"/" + mode).then(
            response => {
              if (response.status == 202) {
                $.notify("<strong>Request for activating the " + system + " accepted</strong>", {
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
        }
      })
    },
    deactivate(system) {
      swal.fire({
        title: "Deactivate " + system,
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Confirm'
      }).then((result) => {
        if (result.value) {
          axios.post("deactivate/" + system).then(
            response => {
              if (response.status == 202) {
                $.notify("<strong>Request for deactivating the Garage accepted</strong>", {
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
        }
      })
    },
    fetchRecord(){
      var qs = require('qs');
        axios.get('/records').then(response => {
          this.resetClasses()
          switch (response.data.garage) {
            case 0:
              this.garageDeactivateClass = 'btn btn-secondary';
              break;
            case 1:
                this.garageActivateClass = 'btn btn-danger';
                break;
        
            default:
                break;
          }
          switch (response.data.house) {
            case 0:
                this.houseDeactivateClass = 'btn btn-secondary';
                break;
            case 1:
                this.houseFullClass = 'btn btn-danger';
                break;
            case 2:
                this.houseDayClass = 'btn btn-danger';
                break;
            case 3:
                this.houseNightClass = 'btn btn-danger';
                break;
        
            default:
                break;
          }
          this.status.age = response.data.age;
        });
    }
  }
};
</script>
