<template>
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header">Actions</div>
        <div class="card-body">
          House
          <br />
          <button class="btn btn-primary" @click="activate('house', 'full')">Full</button>
          <button class="btn btn-primary" @click="activate('house', 'day')">Day</button>
          <button class="btn btn-primary" @click="activate('house', 'night')">Night</button>
          <button class="btn btn-primary" @click="deactivate('house')">Deactivate</button>
          <hr />Garage
          <br />
          <button class="btn btn-primary" @click="activate('garage')">Activate</button>
          <button class="btn btn-primary" @click="deactivate('garage')">Deactivate</button>
          <hr />
          <button class="btn btn-primary" @click="logout()">Logout</button>
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
  methods: {
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
      
    }
  }
};
</script>
