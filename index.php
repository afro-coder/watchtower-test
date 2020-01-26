<!DOCTYPE HTML>
<html>
<head>
  <title>System Dashboard</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@2.8.0/dist/Chart.min.css">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <style>
  body {
padding-top: 5rem;
}
.starter-template {
padding: 3rem 1.5rem;
text-align: center;
}


.loader {
  display: none;
  border: 5px solid #f3f3f3; /* Light grey */
  border-top: 5px solid #3498db; /* Blue */
  border-radius: 50%;
  width: 25px;
  height: 25px;
  animation: spin 2s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

#cpu-chart
{
  height: auto;
  width: auto;
}
  </style>
</head>
<body>
  <nav class=" navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
    <a class="navbar-brand" href="#">WatchTower</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto"></ul>
        <form class="form-inline my-2 my-lg-0">
          <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">
          <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
        </form>
      </div>

    </div>
  </nav>
  <main class="container" role="main">
    <div class="starter-template">



    <div class="row">
    <div class="col-md-12 col-lg-12 col-sm-12">
      <div class="card p-3">
        <h4 class="card-title">Dashboard</h4>
        <div class="card-body">

          <div class="row mt-2 justify-content-center">
            <div class="col-lg-10 col-md-10 col-sm-10 p-0">
              <div class="card w-100 p-3">
                <h4 class="card-title lead">CPU Usage</h4>
                <div class="card-body p-0" id="cpu_usage_data">
                  <div class="row justify-content-center">
                    <div class="col-lg-9 col-sm-12 h-50">
                      <div id="cpu-id" class="h-50">
                        <canvas id="cpu-chart"></canvas>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>



          <div class="row mt-4 justify-content-between" id="dash">
            <div class="col-md-5 col-sm-6 mt-2">
              <div class="card p-4">
                <h5 class="card-title lead">Uptime</h5>
                <div class="row">
                  <div class="col-12">
                    <div class="card-body" id="uptime"></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-5 col-sm-6 mt-2">
              <div class="card p-4">
                <h5 class="card-title lead">Load Average</h5>
                <div class="row">
                  <div class="col-12">
                    <div class="card-body" id="sysload"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="row mt-4 justify-content-center">
            <div class="col-lg-8 col-md-8">
              <div class="card p-3">
                <h4 class="card-title lead">Disk Usage</h4>
                <div class="card-body" id="disk_usage_data">
                  <div class="row justify-content-center">
                    <div class="col-lg-5 col-sm-6">
                      <div id="disk-id">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>


        </div>
      </div>
    </div>
  </div>

  </div>
</main>




  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>

  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

  <script src="https://cdn.jsdelivr.net/npm/moment@2.24.0/moment.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0/dist/Chart.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-streaming@1.8.0/dist/chartjs-plugin-streaming.min.js"></script>
  <script src="../chart.js"></script>

  <script>

  'use strict'
  document.addEventListener('DOMContentLoaded', (event) => {

  (function uptime(){

  var uptimedata = document.getElementById("uptime");
  var sysload = document.getElementById("sysload");
  fetch("http://127.0.0.1:8080/server.php/?command=uptime",
  {
    method:'get',
  })
  .then((response)=>response.json())
  .then(function(data){
    if(!data.message)
    {
      uptimedata.innerHTML = data.uptime;
      sysload.innerHTML = data.load_average;
      setTimeout(uptime,60000);
    }
    else {
      uptimedata.innerHTML = sysload.innerHTML = data.message;
    }
  })
  .catch(function(err){
    throw new Error("Failed to connect to Server");
    return false;
  });
})();

  let button = document.createElement("button");
  let disk_data = document.querySelector("#disk-id");

  button.innerHTML = "Click to Load";
  button.className = "btn btn-outline-primary col";
  disk_data.appendChild(button);

  let loader = document.createElement("div");
  loader.className="loader mt-3";

  let disk_usage=document.getElementById("disk-id").addEventListener("click",diskUsage);
  function diskUsage()
  {
    loader.style.display = "inline-block";

    disk_data.appendChild(loader);
    fetch("http://127.0.0.1:8080/server.php/?command=disk_usage",{method:'get'})
    .then(function(response)
    {
      var contentType = response.headers.get('content-type');


      if(response.status === 404 || response.status === 500 || response.status === 204 )
      {

        throw new Error("Failed to get information");
      }
      else if (contentType && contentType.indexOf('application/json') !== -1) {

        let loader = document.querySelector(".loader").style.display="none";
        return response.json();

      }
      else {

        throw new Error("Error while parsing the request");
      }

    })
    .then(function(data)
    {

      let disk_usage_data = document.querySelector("#disk_usage_data");

      var divElement=document.getElementById("disk_div");
      if(document.getElementById("text-node") !== null)
      {
        document.getElementById("text-node").remove();
      }
      if(divElement !== null)
      {

        // document.getElementById("disk_div").child.remove();
        const myNode = document.getElementById("disk_div");
        while (myNode.firstChild) {
          myNode.removeChild(myNode.firstChild);
          }
      }
      else {

         var divElement = document.createElement("div");
      }
      if(data.message)
      {

        let text = document.createTextNode(data.message);
        let text_node = document.createElement('p');
        text_node.id = "text-node";
        text_node.className = "lead p-2";
        text_node.appendChild(text);
        divElement.text = "";
        divElement.appendChild(text_node);
        disk_usage_data.appendChild(divElement);
        return false;
      }



      divElement.className = "table-responsive";
      // divElement.id = 'usage-table';
      divElement.id = "disk_div";

      let table = document.createElement("table");
      table.className = "table table-striped table-bordered mt-3";

      let header=table.createTHead();
      header.className = "font-weight-bold";

      let header_row = header.insertRow();

      let size_cell = header_row.insertCell();
      // size_cell.className = "text-bold";
      size_cell.innerHTML = "Size";

      let path_cell = header_row.insertCell();
      path_cell.innerHTML = "Path";
      let tblBody=document.createElement("TBODY");
      for(var i=0;i<data.length;i++)
      {
        let row = table.insertRow();
        let cell = row.insertCell();
        let text = document.createTextNode(data[i].size);
        cell.appendChild(text);
        let cell1= row.insertCell();
        let text1 = document.createTextNode(data[i].path);
        cell1.appendChild(text1);
        tblBody.appendChild(row);

      }
      table.appendChild(tblBody);
      divElement.appendChild(table);
      disk_usage_data.appendChild(divElement);
    });
  }



});


</script>

</body>


</html>
