
<style>
</style>

<template>
  <div class="col-md-12">
    <canvas id="myChart" width="400" max-height="400"></canvas>
  </div>
</template>

<script>
import _ from "lodash";
import moment from "moment";
export default {
  data() {
    return {
      percentages: [],
      colors: [],
      labels: []
    };
  },
  mounted() {
    var vue = this;
    axios.get("/stats?days=10").then(response => {
      vue.labels = [];
      _.each(response.data, (item, key) => {
        vue.percentages.push(item["percentage"]);
        vue.labels.push(moment(key, "YY-MM-DD").format("ddd DD MMM"));
        vue.colors.push("rgba(253, 0, 51)");
      });
      vue.percentages = vue.percentages.reverse();
      vue.labels = vue.labels.reverse();

      var ctx = document.getElementById("myChart").getContext("2d");
      var myChart = new Chart(ctx, {
        type: "bar",
        data: {
          labels: vue.labels,
          datasets: [
            {
              data: vue.percentages,
              backgroundColor: vue.colors,
              borderColor: vue.colors,
              borderWidth: 1
            }
          ]
        },
        options: {
          legend: {
            display: false
          },
          title: {
            display: true,
            text: "outages"
          },
          scales: {
            xAxes: [
              {
                stacked: true
              }
            ],
            yAxes: [
              {
                stacked: true
              }
            ]
          }
        }
      });
    });
  }
};
</script>

