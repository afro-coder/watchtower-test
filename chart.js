//First we fetch the data
var ctx = document.getElementById("cpu-chart");
var chart = function(ctx,config){
  return new Chart(ctx,config);
};
var ch = null;
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
    maintainAspectRatio:true,
		title: {
			display: true,
			text: 'Individual Cpu Usage'
		},
		scales: {
			xAxes: [{
				type: 'realtime',
				realtime: {
					duration: 4400,
					refresh:5000,
					delay:6900,
					onRefresh: onRefreshvar
				}
			}],
			yAxes: [{
        ticks: {
                beginAtZero: true
            },
				scaleLabel: {
					display: false,
					labelString: 'Percentage'
				}
			}]
		},
		tooltips: {
			mode: 'nearest',
			intersect: false
		},
		hover: {
			mode: 'nearest',
			intersect: false
		}
	}
};

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

function onRefreshvar(){
  return fetch("http://127.0.0.1:8080/server.php/?command=cpu_usage",{
        method:'get',headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
  })
  .then(function(response){
    if(response.status === 404 || response.status === 500 || response.status === 204)
    {
      throw new Error("Error in response "+response);
    }
    else {
      return response.json();
    }
  })
  .then(function(data){
    if(chart !== "undefined" && data.length > config.data.datasets.length)
    {
      var color = Chart.helpers.color;
      data.forEach(function(data,index){
        let newDataset = {
          label:data.cpu,
          label: data.cpu,
          backgroundColor:color(randomProperty(chartColors)).alpha(0.5).rgbString(),
          borderColor:color(randomProperty(chartColors)).alpha(0.5).rgbString(),
          data:[{x:Date.now(),y:data.value}],
          fill:false
        }
        config.data.datasets.push(newDataset);

      });
    ch =  chart(ctx,config);
    }
    else {

      if(ch.config.data.datasets.length === data.length)
      {
        data.forEach(function(coldata,index){

          ch.config.data.datasets[index].label = coldata.cpu;
          ch.config.data.datasets[index].data.push({

            x:Date.now(),
            y:coldata.value

          });

        });
        if(ch === null)
        {
          throw new Error("Error in Chart Update");

        }
        else {
          ch.update();
        }

      }
    }
  })
  .catch(function(err){
    if(ch === null)
    {
      document.getElementById("cpu-id").text="Connection or command failed";
      throw new Error("Error in Chart Update");
    }
    else {
      ch.options.plugins.streaming.pause = true;
      // chart.update();
    }
  })
};



window.onLoad = onRefreshvar()
