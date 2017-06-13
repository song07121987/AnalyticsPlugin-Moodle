
// Report Range calender of server performance page
$(function() {
    function cb(start, end) {
      $('#server-perform-reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    }
    cb(moment().subtract(29, 'days'), moment());
    $('#server-perform-reportrange').daterangepicker({
      ranges: {
        'Today': [moment(), moment()],
        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
        'This Month': [moment().startOf('month'), moment().endOf('month')],
        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
      }
    }, cb);
  });

  $('#server-perform-reportrange').on('apply.daterangepicker', function(ev, picker) {
    //do something, like clearing an input
    alert ($('#server-perform-reportrange'));


});


$(function () {

  'use strict';

  /* ChartJS
   * -------
   * Here we will create a few charts using ChartJS
   */

  /* --------------------------------------------------------------------------------------------
  // -------- Application Server - CPU Usage .
  // -------- A three line chart which shows – the max/min and avg cpu usage
  //-------------------------------------------------------------------------------------------- */

  // Get context with jQuery - using jQuery's .get() method.
  var serverPerformAppCpuUsageCanvas = $("#serverPerformAppCpuUsage").get(0).getContext("2d");
  // This will get the first returned node in the jQuery collection.
  var serverPerformAppCpuUsage = new Chart(serverPerformAppCpuUsageCanvas);

  var serverPerformAppCpuUsageData = {
    labels: ["January", "February", "March", "April", "May", "June", "July"],
    datasets: [
      {
        label: "Maximum CPU Usage",
        fillColor: "rgb(210, 214, 222)",
        strokeColor: "rgb(210, 214, 222)",
        pointColor: "rgb(210, 214, 222)",
        pointStrokeColor: "#c1c7d1",
        pointHighlightFill: "#fff",
        pointHighlightStroke: "rgb(220,220,220)",
        data: [65, 59, 80, 81, 56, 55, 90]
      },
      {
        label: "Minimum CPU Usage",
        fillColor: "rgba(60,141,188,0.9)",
        strokeColor: "rgba(60,141,188,0.9)",
        pointColor: "#3b8bba",
        pointStrokeColor: "rgba(60,141,188,1)",
        pointHighlightFill: "#fff",
        pointHighlightStroke: "rgba(60,141,188,1)",
        data: [28, 48, 40, 19, 86, 27, 40]
      },
      {
        label: "Average CPU Usage",
        fillColor: "rgba(91, 192, 222, 0.6)",
        strokeColor: "rgba(91, 192, 222, 0.8)",
        pointColor: "rgb(91, 192, 222)",
        pointStrokeColor: "rgba(91, 192, 222,1)",
        pointHighlightFill: "#fff",
        pointHighlightStroke: "rgba(91, 192, 222, 1)",
        data: [38, 54, 60, 59, 68, 36, 65]
      }
    ]
  };

  var serverPerformAppCpuUsageOptions = {
    //Boolean - If we should show the scale at all
    showScale: true,
    //Boolean - Whether grid lines are shown across the chart
    scaleShowGridLines: false,
    //String - Colour of the grid lines
    scaleGridLineColor: "rgba(0,0,0,.05)",
    //Number - Width of the grid lines
    scaleGridLineWidth: 1,
    //Boolean - Whether to show horizontal lines (except X axis)
    scaleShowHorizontalLines: true,
    //Boolean - Whether to show vertical lines (except Y axis)
    scaleShowVerticalLines: true,
    //Boolean - Whether the line is curved between points
    bezierCurve: true,
    //Number - Tension of the bezier curve between points
    bezierCurveTension: 0.3,
    //Boolean - Whether to show a dot for each point
    pointDot: false,
    //Number - Radius of each point dot in pixels
    pointDotRadius: 4,
    //Number - Pixel width of point dot stroke
    pointDotStrokeWidth: 1,
    //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
    pointHitDetectionRadius: 20,
    //Boolean - Whether to show a stroke for datasets
    datasetStroke: true,
    //Number - Pixel width of dataset stroke
    datasetStrokeWidth: 2,
    //Boolean - Whether to fill the dataset with a color
    datasetFill: true,
    //String - A legend template
    legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].lineColor%>\"></span><%=datasets[i].label%></li><%}%></ul>",
    //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio: true,
    //Boolean - whether to make the chart responsive to window resizing
    responsive: true
  };

  //Create the line chart
  serverPerformAppCpuUsage.Line(serverPerformAppCpuUsageData, serverPerformAppCpuUsageOptions);



  /* --------------------------------------------------------------------------------------------
  // -------- Application Server - Memory Usage
  // -------- A three line chart which shows – the max/min and avg cpu memory usage
  //-------------------------------------------------------------------------------------------- */

  // Get context with jQuery - using jQuery's .get() method.
  var serverPerformAppMemoryUsageCanvas = $("#serverPerformAppMemoryUsage").get(0).getContext("2d");
  // This will get the first returned node in the jQuery collection.
  var serverPerformAppMemoryUsage = new Chart(serverPerformAppMemoryUsageCanvas);

  var serverPerformAppMemoryUsageData = {
    labels: ["January", "February", "March", "April", "May", "June", "July"],
    datasets: [
      {
        label: "Maximum CPU Memory Usage",
        fillColor: "rgb(210, 214, 222)",
        strokeColor: "rgb(210, 214, 222)",
        pointColor: "rgb(210, 214, 222)",
        pointStrokeColor: "#c1c7d1",
        pointHighlightFill: "#fff",
        pointHighlightStroke: "rgb(220,220,220)",
        data: [65, 59, 80, 81, 56, 55, 90]
      },
      {
        label: "Minimum CPU Memory Usage",
        fillColor: "rgba(60,141,188,0.9)",
        strokeColor: "rgba(60,141,188,0.9)",
        pointColor: "#3b8bba",
        pointStrokeColor: "rgba(60,141,188,1)",
        pointHighlightFill: "#fff",
        pointHighlightStroke: "rgba(60,141,188,1)",
        data: [28, 48, 40, 19, 86, 27, 40]
      },
      {
        label: "Average CPU Memory Usage",
        fillColor: "rgba(91, 192, 222, 0.6)",
        strokeColor: "rgba(91, 192, 222, 0.8)",
        pointColor: "rgb(91, 192, 222)",
        pointStrokeColor: "rgba(91, 192, 222,1)",
        pointHighlightFill: "#fff",
        pointHighlightStroke: "rgba(91, 192, 222, 1)",
        data: [38, 54, 60, 59, 68, 36, 65]
      }
    ]
  };

  var serverPerformAppMemoryUsageOptions = {
    //Boolean - If we should show the scale at all
    showScale: true,
    //Boolean - Whether grid lines are shown across the chart
    scaleShowGridLines: false,
    //String - Colour of the grid lines
    scaleGridLineColor: "rgba(0,0,0,.05)",
    //Number - Width of the grid lines
    scaleGridLineWidth: 1,
    //Boolean - Whether to show horizontal lines (except X axis)
    scaleShowHorizontalLines: true,
    //Boolean - Whether to show vertical lines (except Y axis)
    scaleShowVerticalLines: true,
    //Boolean - Whether the line is curved between points
    bezierCurve: true,
    //Number - Tension of the bezier curve between points
    bezierCurveTension: 0.3,
    //Boolean - Whether to show a dot for each point
    pointDot: false,
    //Number - Radius of each point dot in pixels
    pointDotRadius: 4,
    //Number - Pixel width of point dot stroke
    pointDotStrokeWidth: 1,
    //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
    pointHitDetectionRadius: 20,
    //Boolean - Whether to show a stroke for datasets
    datasetStroke: true,
    //Number - Pixel width of dataset stroke
    datasetStrokeWidth: 2,
    //Boolean - Whether to fill the dataset with a color
    datasetFill: true,
    //String - A legend template
    legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].lineColor%>\"></span><%=datasets[i].label%></li><%}%></ul>",
    //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio: true,
    //Boolean - whether to make the chart responsive to window resizing
    responsive: true
  };

  //Create the line chart
  serverPerformAppMemoryUsage.Line(serverPerformAppMemoryUsageData, serverPerformAppMemoryUsageOptions);


  /* --------------------------------------------------------------------------------------------
  // -------- Web Services - CPU / Memory Usage
  // -------- A two line chart which shows – the avg cpu and avr memory usage (left shows cpu scale,
  // --- right show memory scale)
  // -------------------------------------------------------------------------------------------- */

  // Get context with jQuery - using jQuery's .get() method.
  var serverPerformWebServiceCpuMemoryUseCanvas = $("#serverPerformWebServiceCpuMemoryUse").get(0)
  .getContext("2d");
  // This will get the first returned node in the jQuery collection.
  var serverPerformWebServiceCpuMemoryUse = new Chart(serverPerformWebServiceCpuMemoryUseCanvas);

  var serverPerformWebServiceCpuMemoryUseData = {
    labels: ["10", "20", "30", "40", "50", "60", "70", "80", "90", "100"],
    datasets: [
      {
        label: "Average CPU Memory Usage",
        fillColor: "rgb(210, 214, 222)",
        strokeColor: "rgb(210, 214, 222)",
        pointColor: "rgb(210, 214, 222)",
        pointStrokeColor: "#c1c7d1",
        pointHighlightFill: "#fff",
        pointHighlightStroke: "rgb(220,220,220)",
        data: [65, 59, 80, 81, 56, 55, 90, 35, 45, 67]
      },
      {
        label: "Average CPU Memory Usage",
        fillColor: "rgba(60,141,188,0.7)",
        strokeColor: "rgba(60,141,188,0.7)",
        pointColor: "#3b8bba",
        pointStrokeColor: "rgba(60,141,188,1)",
        pointHighlightFill: "#fff",
        pointHighlightStroke: "rgba(60,141,188,1)",
        data: [28, 48, 40, 19, 86, 27, 40, 44, 53, 61]
      }
    ]
  };

  var serverPerformWebServiceCpuMemoryUseOptions = {
    //Boolean - If we should show the scale at all
    showScale: true,
    //Boolean - Whether grid lines are shown across the chart
    scaleShowGridLines: false,
    //String - Colour of the grid lines
    scaleGridLineColor: "rgba(0,0,0,.05)",
    //Number - Width of the grid lines
    scaleGridLineWidth: 1,
    //Boolean - Whether to show horizontal lines (except X axis)
    scaleShowHorizontalLines: true,
    //Boolean - Whether to show vertical lines (except Y axis)
    scaleShowVerticalLines: true,
    //Boolean - Whether the line is curved between points
    bezierCurve: true,
    //Number - Tension of the bezier curve between points
    bezierCurveTension: 0.3,
    //Boolean - Whether to show a dot for each point
    pointDot: false,
    //Number - Radius of each point dot in pixels
    pointDotRadius: 4,
    //Number - Pixel width of point dot stroke
    pointDotStrokeWidth: 1,
    //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
    pointHitDetectionRadius: 20,
    //Boolean - Whether to show a stroke for datasets
    datasetStroke: true,
    //Number - Pixel width of dataset stroke
    datasetStrokeWidth: 2,
    //Boolean - Whether to fill the dataset with a color
    datasetFill: true,
    //String - A legend template
    legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].lineColor%>\"></span><%=datasets[i].label%></li><%}%></ul>",
    //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio: true,
    //Boolean - whether to make the chart responsive to window resizing
    responsive: true
  };

  //Create the line chart
  serverPerformWebServiceCpuMemoryUse.Line(serverPerformWebServiceCpuMemoryUseData,
  serverPerformWebServiceCpuMemoryUseOptions);


    //----------------------------------------------------------------------
    //- Web Services - Utilisation. No of API calls
    //------------------------------------------------------------------------
    var serverPerformWebServiceUtilisationCanvas = $("#serverPerformWebServiceUtilisation").get(0).getContext("2d");
    var serverPerformWebServiceUtilisation = new Chart(serverPerformWebServiceUtilisationCanvas);
    var serverPerformWebServiceUtilisationData = {
        labels: ["January", "February", "March", "April", "May", "June", "July"],
    datasets: [
      {
        label: "Maximum API Calls",
        fillColor: "rgb(210, 214, 222)",
        strokeColor: "rgb(210, 214, 222)",
        pointColor: "rgb(210, 214, 222)",
        pointStrokeColor: "#c1c7d1",
        pointHighlightFill: "#fff",
        pointHighlightStroke: "rgb(220,220,220)",
        data: [65, 59, 80, 81, 56, 55, 90]
      },
      {
        label: "Minimum API Calls",
        fillColor: "rgba(60,141,188,0.9)",
        strokeColor: "rgba(60,141,188,0.9)",
        pointColor: "#3b8bba",
        pointStrokeColor: "rgba(60,141,188,1)",
        pointHighlightFill: "#fff",
        pointHighlightStroke: "rgba(60,141,188,1)",
        data: [28, 48, 40, 19, 86, 27, 40]
      }
    ]
      };
    
    serverPerformWebServiceUtilisationData.datasets[1].fillColor = "#00a65a";
    serverPerformWebServiceUtilisationData.datasets[1].strokeColor = "#00a65a";
    serverPerformWebServiceUtilisationData.datasets[1].pointColor = "#00a65a";
    var serverPerformWebServiceUtilisationOptions = {
      //Boolean - Whether the scale should start at zero, or an order of magnitude down from the lowest value
      scaleBeginAtZero: true,
      //Boolean - Whether grid lines are shown across the chart
      scaleShowGridLines: true,
      //String - Colour of the grid lines
      scaleGridLineColor: "rgba(0,0,0,.05)",
      //Number - Width of the grid lines
      scaleGridLineWidth: 1,
      //Boolean - Whether to show horizontal lines (except X axis)
      scaleShowHorizontalLines: true,
      //Boolean - Whether to show vertical lines (except Y axis)
      scaleShowVerticalLines: true,
      //Boolean - If there is a stroke on each bar
      barShowStroke: true,
      //Number - Pixel width of the bar stroke
      barStrokeWidth: 2,
      //Number - Spacing between each of the X value sets
      barValueSpacing: 5,
      //Number - Spacing between data sets within X values
      barDatasetSpacing: 1,
      //String - A legend template
      legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].fillColor%>\"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>",
      //Boolean - whether to make the chart responsive
      responsive: true,
      maintainAspectRatio: true
    };

    serverPerformWebServiceUtilisationOptions.datasetFill = false;
    serverPerformWebServiceUtilisation.Bar(serverPerformWebServiceUtilisationData, serverPerformWebServiceUtilisationOptions);



  /* --------------------------------------------------------------------------------------------
  // -------- Search Services - CPU / Memory Usage
  // -------- A two line chart which shows – the avg cpu and avr memory usage (left shows cpu scale,
  // --- right show memory scale)
  // -------------------------------------------------------------------------------------------- */

  // Get context with jQuery - using jQuery's .get() method.
  var serverPerformSearchServiceCpuMemoryUseCanvas = $("#serverPerformSearchServiceCpuMemoryUse").get(0)
  .getContext("2d");
  // This will get the first returned node in the jQuery collection.
  var serverPerformSearchServiceCpuMemoryUse = new Chart(serverPerformSearchServiceCpuMemoryUseCanvas);

  var serverPerformSearchServiceCpuMemoryUseData = {
    labels: ["10", "20", "30", "40", "50", "60", "70", "80", "90", "100"],
    datasets: [
      {
        label: "Maximum CPU Memory Usage",
        fillColor: "rgb(210, 214, 222)",
        strokeColor: "rgb(210, 214, 222)",
        pointColor: "rgb(210, 214, 222)",
        pointStrokeColor: "#c1c7d1",
        pointHighlightFill: "#fff",
        pointHighlightStroke: "rgb(220,220,220)",
        data: [65, 59, 80, 81, 56, 55, 90, 35, 45, 67]
      },
      {
        label: "Minimum CPU Memory Usage",
        fillColor: "rgba(60,141,188,0.7)",
        strokeColor: "rgba(60,141,188,0.7)",
        pointColor: "#3b8bba",
        pointStrokeColor: "rgba(60,141,188,1)",
        pointHighlightFill: "#fff",
        pointHighlightStroke: "rgba(60,141,188,1)",
        data: [28, 48, 40, 19, 86, 27, 40, 44, 53, 61]
      }
    ]
  };

  var serverPerformSearchServiceCpuMemoryUseOptions = {
    //Boolean - If we should show the scale at all
    showScale: true,
    //Boolean - Whether grid lines are shown across the chart
    scaleShowGridLines: false,
    //String - Colour of the grid lines
    scaleGridLineColor: "rgba(0,0,0,.05)",
    //Number - Width of the grid lines
    scaleGridLineWidth: 1,
    //Boolean - Whether to show horizontal lines (except X axis)
    scaleShowHorizontalLines: true,
    //Boolean - Whether to show vertical lines (except Y axis)
    scaleShowVerticalLines: true,
    //Boolean - Whether the line is curved between points
    bezierCurve: true,
    //Number - Tension of the bezier curve between points
    bezierCurveTension: 0.3,
    //Boolean - Whether to show a dot for each point
    pointDot: false,
    //Number - Radius of each point dot in pixels
    pointDotRadius: 4,
    //Number - Pixel width of point dot stroke
    pointDotStrokeWidth: 1,
    //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
    pointHitDetectionRadius: 20,
    //Boolean - Whether to show a stroke for datasets
    datasetStroke: true,
    //Number - Pixel width of dataset stroke
    datasetStrokeWidth: 2,
    //Boolean - Whether to fill the dataset with a color
    datasetFill: true,
    //String - A legend template
    legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].lineColor%>\"></span><%=datasets[i].label%></li><%}%></ul>",
    //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio: true,
    //Boolean - whether to make the chart responsive to window resizing
    responsive: true
  };

  //Create the line chart
  serverPerformSearchServiceCpuMemoryUse.Line(serverPerformSearchServiceCpuMemoryUseData,
  serverPerformSearchServiceCpuMemoryUseOptions);


  /* --------------------------------------------------------------------------------------------
  // -------- Search Indexes Time
  // -------- One line chart to show time taken for indexing to run
  // -------------------------------------------------------------------------------------------- */

  // Get context with jQuery - using jQuery's .get() method.
  var serverPerformSearchIndexTimeCanvas = $("#serverPerformSearchIndexTime").get(0)
  .getContext("2d");
  // This will get the first returned node in the jQuery collection.
  var serverPerformSearchIndexTime = new Chart(serverPerformSearchIndexTimeCanvas);

  var serverPerformSearchIndexTimeData = {
    labels: ["One Week", "Two Week", "Three Week", "Four Week", "Five Week", "Six Week", "Seven Week"],
    datasets: [
      {
        label: "Indexing Time",
        fillColor: "rgba(60,141,188,0.7)",
        strokeColor: "rgba(60,141,188,0.7)",
        pointColor: "#3b8bba",
        pointStrokeColor: "rgba(60,141,188,1)",
        pointHighlightFill: "#fff",
        pointHighlightStroke: "rgba(60,141,188,1)",
        data: [25, 59, 70, 81, 56, 55, 90]
      }
    ]
  };

  var serverPerformSearchIndexTimeOptions = {
    //Boolean - If we should show the scale at all
    showScale: true,
    //Boolean - Whether grid lines are shown across the chart
    scaleShowGridLines: false,
    //String - Colour of the grid lines
    scaleGridLineColor: "rgba(0,0,0,.05)",
    //Number - Width of the grid lines
    scaleGridLineWidth: 1,
    //Boolean - Whether to show horizontal lines (except X axis)
    scaleShowHorizontalLines: true,
    //Boolean - Whether to show vertical lines (except Y axis)
    scaleShowVerticalLines: true,
    //Boolean - Whether the line is curved between points
    bezierCurve: true,
    //Number - Tension of the bezier curve between points
    bezierCurveTension: 0.3,
    //Boolean - Whether to show a dot for each point
    pointDot: false,
    //Number - Radius of each point dot in pixels
    pointDotRadius: 4,
    //Number - Pixel width of point dot stroke
    pointDotStrokeWidth: 1,
    //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
    pointHitDetectionRadius: 20,
    //Boolean - Whether to show a stroke for datasets
    datasetStroke: true,
    //Number - Pixel width of dataset stroke
    datasetStrokeWidth: 2,
    //Boolean - Whether to fill the dataset with a color
    datasetFill: true,
    //String - A legend template
    legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].lineColor%>\"></span><%=datasets[i].label%></li><%}%></ul>",
    //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio: true,
    //Boolean - whether to make the chart responsive to window resizing
    responsive: true
  };

  //Create the line chart
  serverPerformSearchIndexTime.Line(serverPerformSearchIndexTimeData,
  serverPerformSearchIndexTimeOptions);










});