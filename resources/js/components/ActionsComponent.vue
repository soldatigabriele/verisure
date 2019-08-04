<template>
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header">Actions</div>
        <div class="card-body">
          House
          <br />
          <button class="btn btn-primary" @click="activateHouse('full')" disabled>Full</button>
          <button class="btn btn-primary" @click="activateHouse('day')" disabled>Day</button>
          <button class="btn btn-primary" @click="activateHouse('night')" disabled>Night</button>
          <button class="btn btn-primary" @click="deactivate('house')" disabled>Deactivate</button>
          <hr />Garage
          <br />
          <button class="btn btn-primary" @click="activateAnnex()" disabled>Activate</button>
          <button class="btn btn-primary" @click="deactivateAnnex()" disabled>Deactivate</button>
          <hr />
          <button class="btn btn-primary" @click="logout()">logout</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import _ from "lodash";
import notify from "bootstrap-notify";

export default {
  methods: {
    logout() {
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
  }
};
</script>
