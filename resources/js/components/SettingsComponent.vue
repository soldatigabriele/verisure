<template>
  <div class="row justify-content-center">
    <div class="col-md-8">

      <div class="card">
        <div class="card-header">Settings</div>
        <div class="card-body">
          <div v-if="settings">
            API Authentication Token
            <br>
            <div class="input-group mb-3">
            <div class="input-group-prepend">
                <div class="input-group-text">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" @change="updateSetting('auth_active')" v-model="settings.auth_active" class="custom-control-input" id="auth_active">
                        <label class="custom-control-label" for="auth_active"></label>
                    </div>
                </div>
            </div>
            <input type="text" class="form-control" v-model="settings.auth_token" :disabled="!settings.auth_active">
            <div class="input-group-append">
                <button @click="regenerate('auth_token')" class="btn btn-primary" :disabled="!settings.auth_active">regenerate</button>
            </div>
            </div>
            Error Notifications
          
            <div class="input-group mb-3">
              <div class="input-group-prepend">
                  <div class="input-group-text">
                      <div class="custom-control custom-switch">
                          <input type="checkbox" @change="updateSetting('notifications_errors_enabled')" v-model="settings.notifications_errors_enabled" class="custom-control-input" id="notifications_errors_enabled">
                          <label class="custom-control-label" for="notifications_errors_enabled"></label>
                      </div>
                  </div>
              </div>
              <input type="text" class="form-control" v-model="settings.notifications_errors_url" :disabled="!settings.notifications_errors_enabled">
              <div class="input-group-append">
                  <button class="btn btn-primary" @click="testWebhook('notifications_errors_url')">test</button>
              </div>
              <div class="input-group-append">
                <button class="btn btn-primary" @click="updateSetting('notifications_errors_url')" :disabled="!settings.notifications_errors_enabled">update</button>
              </div>
            </div>
            <small class="form-text text-muted">
              Call the webhook when we receive an unmapped message from Verisure
            </small>
            Alarm Status changed Notifications
            <div class="input-group mb-3">
              <div class="input-group-prepend">
                  <div class="input-group-text">
                      <div class="custom-control custom-switch">
                          <input type="checkbox" @change="updateSetting('notifications_status__updated_enabled')" v-model="settings.notifications_status__updated_enabled" class="custom-control-input" id="notifications_status__updated_enabled">
                          <label class="custom-control-label" for="notifications_status__updated_enabled"></label>
                      </div>
                  </div>
              </div>
              <input type="text" class="form-control" v-model="settings.notifications_status__updated_url" :disabled="!settings.notifications_status__updated_enabled">
              <div class="input-group-append">
                <button class="btn btn-primary" @click="testWebhook('notifications_status__updated_url')">test</button>
              </div>
              <div class="input-group-append">
                  <button class="btn btn-primary" @click="updateSetting('notifications_status__updated_url')" :disabled="!settings.notifications_status__updated_enabled">update</button>
              </div>
            </div>
             <small class="form-text text-muted">
              Call the webhook after a manual request that change the status of the alarm
            </small>
            <small class="form-text text-muted">
                The URL of the webhook you want to call (IFTTT webhook with token, HomeAssistant webhook)
            </small>

            Censure Responses
            <div class="input-group mb-3">
              <div class="input-group-prepend">
                  <div class="input-group-text">
                      <div class="custom-control custom-switch">
                          <input type="checkbox" @change="updateSetting('censure__responses')" v-model="settings.censure__responses" class="custom-control-input" id="censure__responses">
                          <label class="custom-control-label" for="censure__responses"></label>
                      </div>
                  </div>
              </div>
              <input type="text" class="form-control" value="..." disabled>
              <div class="input-group-append">
                  <button class="btn btn-primary" @click="updateSetting('censure__responses')" disabled>update</button>
              </div>
            </div>
            <small class="form-text text-muted">
                The placeholder that will replace the portion of response that will be removed
            </small>

             <hr>
            Status job

            <br />max calls
            <div class="input-group mb-3">
            <input type="text" class="form-control" v-model="settings.status__job_max__calls" />
            <div class="input-group-append">
                <button class="btn btn-primary" @click="updateSetting('status__job_max__calls')">update</button>
            </div>
            </div>
            sleep between calls
            <div class="input-group mb-3">
            <input type="text" class="form-control" v-model="settings.status__job_sleep__between__calls" />
            <div class="input-group-append">
                <button class="btn btn-primary" @click="updateSetting('status__job_sleep__between__calls')">update</button>
            </div>
            </div>

            <hr />Session
            <div class="custom-control custom-switch">
                <input type="checkbox" @change="updateSetting('session_keep__alive')" v-model="settings.session_keep__alive" class="custom-control-input" id="session_keep__alive">
                <label class="custom-control-label" for="session_keep__alive">Keep alive</label>
            </div>

            <br />TTL
            <div class="input-group mb-3">
            <input type="text" class="form-control" v-model="settings.session_ttl" @keyup="parseTtl('session_ttl')" />
            <div class="input-group-append">
                <button class="btn btn-primary" @click="updateSetting('session_ttl')">update</button>
            </div>
            </div>
            <small  id="session_ttl_label" class="form-text text-muted">
                Session TTL
            </small>

          </div>
        </div>
      </div>
     
      <div class="card">
        <div class="card-header">Schedule</div>
        <div class="card-body">
          <div v-if="settings">

            House full
            <br>
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <div class="input-group-text">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" v-model="settings.schedule_house_full_enabled" class="custom-control-input" id="schedule_house_full_enabled">
                            <label class="custom-control-label" for="schedule_house_full_enabled"></label>
                        </div>
                    </div>
                </div>
                <input type="text" class="form-control" v-model="settings.schedule_house_full_cron" @keyup="parseCron('schedule_house_full_cron')" :disabled="!settings.schedule_house_full_enabled">
                <div class="input-group-append">
                    <button @click="updateSetting('schedule_house_full_cron'), updateSetting('schedule_house_full_enabled')" class="btn btn-primary">Set</button>
                </div>
            </div>
            <small id="schedule_house_full_cron_label" class="form-text text-muted">
            </small>

            House Day
            <br>
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <div class="input-group-text">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" v-model="settings.schedule_house_day_enabled" class="custom-control-input" id="schedule_house_day_enabled">
                            <label class="custom-control-label" for="schedule_house_day_enabled"></label>
                        </div>
                    </div>
                </div>
                <input type="text" class="form-control" v-model="settings.schedule_house_day_cron" @keyup="parseCron('schedule_house_day_cron')" :disabled="!settings.schedule_house_day_enabled">
                <div class="input-group-append">
                    <button @click="updateSetting('schedule_house_day_cron'), updateSetting('schedule_house_day_enabled')" class="btn btn-primary">Set</button>
                </div>
            </div>
            <small id="schedule_house_day_cron_label" class="form-text text-muted">
            </small>

            House Night
            <br>
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <div class="input-group-text">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" v-model="settings.schedule_house_night_enabled" class="custom-control-input" id="schedule_house_night_enabled">
                            <label class="custom-control-label" for="schedule_house_night_enabled"></label>
                        </div>
                    </div>
                </div>
                <input type="text" class="form-control" v-model="settings.schedule_house_night_cron" @keyup="parseCron('schedule_house_night_cron')" :disabled="!settings.schedule_house_night_enabled">
                <div class="input-group-append">
                    <button @click="updateSetting('schedule_house_night_cron'), updateSetting('schedule_house_night_enabled')" class="btn btn-primary">Set</button>
                </div>
            </div>
            <small id="schedule_house_night_cron_label" class="form-text text-muted">
            </small>

            House Deactivate
            <br>
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <div class="input-group-text">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" v-model="settings.schedule_house_deactivate_enabled" class="custom-control-input" id="schedule_house_deactivate_enabled">
                            <label class="custom-control-label" for="schedule_house_deactivate_enabled"></label>
                        </div>
                    </div>
                </div>
                <input type="text" class="form-control" v-model="settings.schedule_house_deactivate_cron" @keyup="parseCron('schedule_house_deactivate_cron')" :disabled="!settings.schedule_house_deactivate_enabled">
                <div class="input-group-append">
                    <button @click="updateSetting('schedule_house_deactivate_cron'), updateSetting('schedule_house_deactivate_enabled')" class="btn btn-primary">Set</button>
                </div>
            </div>
            <small id="schedule_house_deactivate_cron_label" class="form-text text-muted">
            </small>

            Garage Activate
            <br>
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <div class="input-group-text">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" v-model="settings.schedule_annex_activate_enabled" class="custom-control-input" id="schedule_annex_activate_enabled">
                            <label class="custom-control-label" for="schedule_annex_activate_enabled"></label>
                        </div>
                    </div>
                </div>
                <input type="text" class="form-control" v-model="settings.schedule_annex_activate_cron" @keyup="parseCron('schedule_annex_activate_cron')" :disabled="!settings.schedule_annex_activate_enabled">
                <div class="input-group-append">
                    <button @click="updateSetting('schedule_annex_activate_cron'), updateSetting('schedule_annex_activate_enabled')" class="btn btn-primary">Set</button>
                </div>
            </div>
            <small id="schedule_annex_activate_cron_label" class="form-text text-muted">
            </small>

            Garage Deactivate
            <br>
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <div class="input-group-text">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" v-model="settings.schedule_annex_deactivate_enabled" class="custom-control-input" id="schedule_annex_deactivate_enabled">
                            <label class="custom-control-label" for="schedule_annex_deactivate_enabled"></label>
                        </div>
                    </div>
                </div>
                <input type="text" class="form-control" v-model="settings.schedule_annex_deactivate_cron" @keyup="parseCron('schedule_annex_deactivate_cron')" :disabled="!settings.schedule_annex_deactivate_enabled">
                <div class="input-group-append">
                    <button @click="updateSetting('schedule_annex_deactivate_cron'), updateSetting('schedule_annex_deactivate_enabled')" class="btn btn-primary">Set</button>
                </div>
            </div>
            <small id="schedule_annex_deactivate_cron_label" class="form-text text-muted">
            </small>
            
          </div>
        </div>
      </div>
     
    </div>
  </div>
</template>

<script>
import _ from "lodash";
import notify from "bootstrap-notify";
import parser from "cron-parser";

export default {
  data() {
    return {
      settings: {
        auth_active: undefined,
        auth_token: undefined,
        notifications_errors_enabled: undefined,
        notifications_errors_url: undefined,
        notifications_status__updated_enabled: undefined,
        notifications_status__updated_url: undefined,
        status__job_max__calls: undefined,
        status__job_sleep__between__calls: undefined,
        session_keep__alive: undefined,
        session_ttl: undefined,
        censure__responses: undefined,
        schedule_house_full_enabled: undefined,
        schedule_house_full_cron: undefined,
        schedule_house_day_enabled: undefined,
        schedule_house_day_cron: undefined,
        schedule_house_night_enabled: undefined,
        schedule_house_night_cron: undefined,
        schedule_house_deactivate_enabled: undefined,
        schedule_house_deactivate_cron: undefined,
        schedule_annex_activate_enabled: undefined,
        schedule_annex_activate_cron: undefined,
        schedule_annex_deactivate_enabled: undefined,
        schedule_annex_deactivate_cron: undefined
      }
    };
  },
  methods: {
    regenerate(key, length) {
      let r =
        Math.random()
          .toString(36)
          .substr(2, 10) +
        Math.random()
          .toString(36)
          .substr(2, 10) +
        Math.random()
          .toString(36)
          .substr(2, 10);

      this.settings[key] = r;
      this.updateSetting(key);
    },
    updateSetting(key) {
      axios
        .post("/settings", {
          // Recreate the original key name with . and _ in the right place
          key: key
            .split("__")
            .join("+")
            .split("_")
            .join(".")
            .split("+")
            .join("_"),
          value: this.settings[key]
        })
        .then(
          response => {
            if (response.data.status == "ok") {
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
    testWebhook(url) {
      axios
        .post("/webhook", {
          url: this.settings[url],
          payload: {
            title: "test webhook",
            message: "test message content"
          }
        })
        .then(
          response => {
            if (response.data.status == "ok") {
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
    },
    parseCron(key){
        let expression = this.settings[key]
        let cron = parser.parseExpression(expression);
        $('#'+key+'_label').html(cron.next().toString() + "<br>" + cron.next().toString() )
    },
    parseTtl(key){
        let minutes = this.settings[key]/60;
        $('#'+key+'_label').html(minutes + ' hours')
    }
  },
  mounted() {
    axios.get("/settings").then(response => {
      _.each(response.data, setting => {
        let value = setting.value;
        if (setting.value == "0") value = false;
        if (setting.value == "1") value = true;
        // We have to deal with keys like "status_job.sleep_between_calls"
        // se we need to do a bit of processing to make it readable by js
        setting.key = setting.key
          .split("_")
          .join("__")
          .split(".")
          .join("_");
        this.settings[setting.key] = value;
      });
      this.parseTtl('session_ttl')
    });
  }
};
</script>
