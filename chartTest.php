<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title></title>
    <style media="screen">
      body{
        width: 100vw;
        height:100vh;
      }
      .row
      {
        margin-left: 10vw;
        margin-right: 10vw;
      }
      #cpu-chart
      {
        width: 50vw;
        height: 70vh;
      }
    </style>
  </head>
  <body>
    <div class="row">
      <div>
  		<canvas id="cpu-chart"></canvas>
  	</div>

    </div>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.24.0/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0/dist/Chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-streaming@1.8.0/dist/chartjs-plugin-streaming.min.js"></script>


    <script>
    var chartColors = {
    	red: 'rgb(255, 99, 132)',
    	orange: 'rgb(255, 159, 64)',
    	yellow: 'rgb(255, 205, 86)',
    	green: 'rgb(75, 192, 192)',
    	blue: 'rgb(54, 162, 235)',
    	purple: 'rgb(153, 102, 255)',
    	grey: 'rgb(201, 203, 207)'
    };
    var randomProperty = function (object) {
      var keys = Object.keys(object);
      return object[keys[Math.floor(keys.length * Math.random())]];
    };

// First Draw the chart
// Then set an onRefresh
// in the onRefresh the chart has to only update the existing data



var config = {
	type: 'line',
	data: {
		datasets: []
	},
	options: {
    plugins: {
                streaming: {            // per-chart option
                    frameRate: 35       // chart is drawn 30 times every second
                }
            },
    responsive: true,
		title: {
			display: true,
			text: 'Cpu Usage'
		},
		scales: {
			xAxes: [{
				type: 'realtime',
				realtime: {
					duration: 40000,
					refresh:9000,
					delay:18000,
					onRefresh: onRefresh
				}
			}],
			yAxes: [{
        ticks: {
                beginAtZero: true
            },
				scaleLabel: {
					display: true,
					labelString: 'Percentage'
				}
			}]
		},
		tooltips: {
			mode: 'nearest',
			intersect: false
		},
		hover: {
			mode: 'nearest'i,
			intersect: false
		}
	}
};
function onRefresh(chart)
{
  fetch("http://127.0.0.1:8080/server.php/?command=cpu_usage",{
        method:'get',headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
  })
  .then((response) => response.json())
  .then(function(rdata){
    var color = Chart.helpers.color;
    console.log("Delay "+ chart.config.options.scales.xAxes[0].realtime.delay);
    // chart.config.options.scales.xAxes[0].realtime.delay = 8700;

    console.log("Fetch called "+chart.config.data.datasets.length);

    if(rdata.length > chart.config.data.datasets.length){
      console.log(rdata.length)
      console.log(chart.config.data.datasets.length)

      rdata.forEach(function(data,index){
        var newDataset = {
          label: data.cpu,
          backgroundColor:color(randomProperty(chartColors)).alpha(0.5).rgbString(),
          borderColor:color(randomProperty(chartColors)).alpha(0.5).rgbString(),

          data: [{y:data.value,x:Date.now()}],
          fill: false,
          line:true
        };
        chart.config.data.datasets.push(newDataset);
        chart.update();

      });
    }
    else {
      rdata.forEach(function(data,index){
        chart.config.data.datasets[index].data.push({y:data.value,x:Date.now()});

      });
      chart.update(0);
    }


  })
  .catch(function(err){
    throw new Error("Chart Error "+err);
  });
}
function chartCreate(){
  var ctx = document.getElementById('cpu-chart').getContext('2d');
  var cpuChart = new Chart(ctx, config);
  console.log("Delay "+ cpuChart.config.options.scales.xAxes[0].realtime.delay);
  // cpuChart.config.options.scales.xAxes[0].realtime.delay = 0;
  onRefresh(cpuChart);
  cpuChart.update(0);

  // return cpuChart
}
window.onload = chartCreate();


    </script>
  </body>
</html>
