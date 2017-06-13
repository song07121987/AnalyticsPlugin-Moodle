
  //--------------------------------------------------------------------
  //- Device Type Pie Chart -
  //--------------------------------------------------------------------
  // Get context with jQuery - using jQuery's .get() method.
  var accessMetricsDeviceTypeChartCanvas = $("#accessMetricsDeviceTypeChart").get(0).getContext("2d");
  var accessMetricsDeviceTypeChart = new Chart(accessMetricsDeviceTypeChartCanvas);
  var accessMetricsDeviceTypeChartData = [
    {
      value: 7000,
      color: "#f56954",
      highlight: "#f56954",
      label: "Desktop"
    },
    {
      value: 5000,
      color: "#00a65a",
      highlight: "#00a65a",
      label: "Notebook"
    },
    {
      value: 4000,
      color: "#f39c12",
      highlight: "#f39c12",
      label: "Smart Phone"
    },
    {
      value: 1200,
      color: "#00c0ef",
      highlight: "#00c0ef",
      label: "Feature Phone"
    },
    {
      value: 1000,
      color: "#3c8dbc",
      highlight: "#3c8dbc",
      label: "Tablet"
    }
  ];
   var pieOptions = {
    //Boolean - Whether we should show a stroke on each segment
    segmentShowStroke: true,
    //String - The colour of each segment stroke
    segmentStrokeColor: "#fff",
    //Number - The width of each segment stroke
    segmentStrokeWidth: 1,
    //Number - The percentage of the chart that we cut out of the middle
    percentageInnerCutout: 50, // This is 0 for Pie charts
    //Number - Amount of animation steps
    animationSteps: 100,
    //String - Animation easing effect
    animationEasing: "easeOutBounce",
    //Boolean - Whether we animate the rotation of the Doughnut
    animateRotate: true,
    //Boolean - Whether we animate scaling the Doughnut from the centre
    animateScale: false,
    //Boolean - whether to make the chart responsive to window resizing
    responsive: true,
    // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio: false,
    //String - A legend template
    legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>",
    //String - A tooltip template
    tooltipTemplate: "<%=value %> <%=label%> users"
  };
  //Create pie or douhnut chart
  // You can switch between pie and douhnut using the method below.
  accessMetricsDeviceTypeChart.Doughnut(accessMetricsDeviceTypeChartData, pieOptions);

  //-----------------------------------------------------------------------------------
  //- Browser Type Pie Chart -
  //-----------------------------------------------------------------------------------
  // Get context with jQuery - using jQuery's .get() method.
  var accessMetricsBrowserChartCanvas = $("#accessMetricsBrowserChart").get(0).getContext("2d");
  var accessMetricsBrowserChart = new Chart(accessMetricsBrowserChartCanvas);
  var accessMetricsBrowserChartData = [
    {
      value: 700,
      color: "#f56954",
      highlight: "#f56954",
      label: "Chrome"
    },
    {
      value: 500,
      color: "#00a65a",
      highlight: "#00a65a",
      label: "IE"
    },
    {
      value: 400,
      color: "#f39c12",
      highlight: "#f39c12",
      label: "FireFox"
    },
    {
      value: 600,
      color: "#00c0ef",
      highlight: "#00c0ef",
      label: "Safari"
    },
    {
      value: 300,
      color: "#3c8dbc",
      highlight: "#3c8dbc",
      label: "Opera"
    },
    {
      value: 100,
      color: "#d2d6de",
      highlight: "#d2d6de",
      label: "Navigator"
    }
  ];
  var pieOptions = {
    //Boolean - Whether we should show a stroke on each segment
    segmentShowStroke: true,
    //String - The colour of each segment stroke
    segmentStrokeColor: "#fff",
    //Number - The width of each segment stroke
    segmentStrokeWidth: 1,
    //Number - The percentage of the chart that we cut out of the middle
    percentageInnerCutout: 50, // This is 0 for Pie charts
    //Number - Amount of animation steps
    animationSteps: 100,
    //String - Animation easing effect
    animationEasing: "easeOutBounce",
    //Boolean - Whether we animate the rotation of the Doughnut
    animateRotate: true,
    //Boolean - Whether we animate scaling the Doughnut from the centre
    animateScale: false,
    //Boolean - whether to make the chart responsive to window resizing
    responsive: true,
    // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio: false,
    //String - A legend template
    legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>",
    //String - A tooltip template
    tooltipTemplate: "<%=value %> <%=label%> users"
  };
  //Create pie or douhnut chart
  // You can switch between pie and douhnut using the method below.
  accessMetricsBrowserChart.Doughnut(accessMetricsBrowserChartData, pieOptions);


  //-----------------------------------------------------------------------------------
  //- Device Model Pie Chart -
  //-----------------------------------------------------------------------------------
  // Get context with jQuery - using jQuery's .get() method.
  var accessMetricsDeviceModelChartCanvas = $("#accessMetricsDeviceModelChart").get(0).getContext("2d");
  var accessMetricsDeviceModelChart = new Chart(accessMetricsDeviceModelChartCanvas);
  var accessMetricsDeviceModelChartData = [
    {
      value: 700,
      color: "#f56954",
      highlight: "#f56954",
      label: "Apple"
    },
    {
      value: 500,
      color: "#00a65a",
      highlight: "#00a65a",
      label: "Dell"
    },
    {
      value: 400,
      color: "#f39c12",
      highlight: "#f39c12",
      label: "Lenevo"
    },
    {
      value: 600,
      color: "#00c0ef",
      highlight: "#00c0ef",
      label: "Samsung"
    },
    {
      value: 300,
      color: "#3c8dbc",
      highlight: "#3c8dbc",
      label: "Acer"
    },
    {
      value: 100,
      color: "#9932CC",
      highlight: "#9932CC",
      label: "Toshiba"
    },
    {
      value: 455,
      color: "#008B8B",
      highlight: "#008B8B",
      label: "Asus"
    },
    {
      value: 320,
      color: "#7FFFD4",
      highlight: "#7FFFD4",
      label: "Oppo"
    },
    {
      value: 1100,
      color: "#DAA520",
      highlight: "#DAA520",
      label: "Nokia"
    },
    {
      value: 670,
      color: "#5F9EA0",
      highlight: "#5F9EA0",
      label: "Blackberry"
    },
    {
      value: 319,
      color: "#D2691E",
      highlight: "#D2691E",
      label: "Vodaphone"
    },
    {
      value: 234,
      color: "#FF7F50",
      highlight: "#FF7F50",
      label: "Gigabyte"
    }
  ];
  var pieOptions = {
    //Boolean - Whether we should show a stroke on each segment
    segmentShowStroke: true,
    //String - The colour of each segment stroke
    segmentStrokeColor: "#fff",
    //Number - The width of each segment stroke
    segmentStrokeWidth: 1,
    //Number - The percentage of the chart that we cut out of the middle
    percentageInnerCutout: 50, // This is 0 for Pie charts
    //Number - Amount of animation steps
    animationSteps: 100,
    //String - Animation easing effect
    animationEasing: "easeOutBounce",
    //Boolean - Whether we animate the rotation of the Doughnut
    animateRotate: true,
    //Boolean - Whether we animate scaling the Doughnut from the centre
    animateScale: false,
    //Boolean - whether to make the chart responsive to window resizing
    responsive: true,
    // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio: false,
    //String - A legend template
    legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>",
    //String - A tooltip template
    tooltipTemplate: "<%=value %> <%=label%> users"
  };
  //Create pie or douhnut chart
  // You can switch between pie and douhnut using the method below.
  accessMetricsDeviceModelChart.Doughnut(accessMetricsDeviceModelChartData, pieOptions);


  //-----------------------------------------------------------------------------------
  //- Operating System Pie Chart -
  //-----------------------------------------------------------------------------------
  // Get context with jQuery - using jQuery's .get() method.
  var accessMetricsOperatingSystemChartCanvas = $("#accessMetricsOperatingSystemChart").get(0).getContext("2d");
  var accessMetricsOperatingSystemChart = new Chart(accessMetricsOperatingSystemChartCanvas);
  var accessMetricsOperatingSystemChartData = [
    {
      value: 17000,
      color: "#f56954",
      highlight: "#f56954",
      label: "Windows 8"
    },
    {
      value: 12000,
      color: "#00a65a",
      highlight: "#00a65a",
      label: "Ubuntu"
    },
    {
      value: 24000,
      color: "#f39c12",
      highlight: "#f39c12",
      label: "Windows 7"
    },
    {
      value: 9000,
      color: "#00c0ef",
      highlight: "#00c0ef",
      label: "Linux Mint"
    },
    {
      value: 13000,
      color: "#3c8dbc",
      highlight: "#3c8dbc",
      label: "Windows 8.1"
    },
    {
      value: 10000,
      color: "#9932CC",
      highlight: "#9932CC",
      label: "Macintosh OSX"
    },
    {
      value: 45000,
      color: "#008B8B",
      highlight: "#008B8B",
      label: "Android"
    },
    {
      value: 13200,
      color: "#7FFFD4",
      highlight: "#7FFFD4",
      label: "Windows XP"
    },
    {
      value: 4000,
      color: "#DAA520",
      highlight: "#DAA520",
      label: "Fedora"
    },
    {
      value: 3000,
      color: "#5F9EA0",
      highlight: "#5F9EA0",
      label: "Chrome OS"
    }
  ];
  var pieOptions = {
    //Boolean - Whether we should show a stroke on each segment
    segmentShowStroke: true,
    //String - The colour of each segment stroke
    segmentStrokeColor: "#fff",
    //Number - The width of each segment stroke
    segmentStrokeWidth: 1,
    //Number - The percentage of the chart that we cut out of the middle
    percentageInnerCutout: 50, // This is 0 for Pie charts
    //Number - Amount of animation steps
    animationSteps: 100,
    //String - Animation easing effect
    animationEasing: "easeOutBounce",
    //Boolean - Whether we animate the rotation of the Doughnut
    animateRotate: true,
    //Boolean - Whether we animate scaling the Doughnut from the centre
    animateScale: false,
    //Boolean - whether to make the chart responsive to window resizing
    responsive: true,
    // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio: false,
    //String - A legend template
    legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>",
    //String - A tooltip template
    tooltipTemplate: "<%=value %> <%=label%> users"
  };
  //Create pie or douhnut chart
  // You can switch between pie and douhnut using the method below.
  accessMetricsOperatingSystemChart.Doughnut(accessMetricsOperatingSystemChartData, pieOptions);



  //-----------------------------------------------------------------------------------
  //- Device Resolution Pie Chart -
  //-----------------------------------------------------------------------------------
  // Get context with jQuery - using jQuery's .get() method.
  var accessMetricsDeviceResolutionChartCanvas = $("#accessMetricsDeviceResolutionChart").get(0).getContext("2d");
  var accessMetricsDeviceResolutionChart = new Chart(accessMetricsDeviceResolutionChartCanvas);
  var accessMetricsDeviceResolutionChartData = [
    {
      value: 19.1,
      color: "#f56954",
      highlight: "#f56954",
      label: "1366x768"
    },
    {
      value: 9.4,
      color: "#00a65a",
      highlight: "#00a65a",
      label: "1920x1080"
    },
    {
      value: 8.5,
      color: "#f39c12",
      highlight: "#f39c12",
      label: "1280x800"
    },
    {
      value: 6.4,
      color: "#00c0ef",
      highlight: "#00c0ef",
      label: "320x568"
    },
    {
      value: 5.7,
      color: "#3c8dbc",
      highlight: "#3c8dbc",
      label: "1440x900"
    },
    {
      value: 5.5,
      color: "#9932CC",
      highlight: "#9932CC",
      label: "1280x1024"
    },
    {
      value: 5.2,
      color: "#008B8B",
      highlight: "#008B8B",
      label: "320x480"
    },
    {
      value: 4.6,
      color: "#7FFFD4",
      highlight: "#7FFFD4",
      label: "1600x900"
    },
    {
      value: 4.5,
      color: "#DAA520",
      highlight: "#DAA520",
      label: "768x1024"
    },
    {
      value: 3.9,
      color: "#5F9EA0",
      highlight: "#5F9EA0",
      label: "1024x768"
    },
    {
      value: 2.8,
      color: "#D2691E",
      highlight: "#D2691E",
      label: "1680x1050"
    },
    {
      value: 2.3,
      color: "#FF7F50",
      highlight: "#FF7F50",
      label: "360x640"
    },
    {
      value: 1.7,
      color: "#20B2AA",
      highlight: "#20B2AA",
      label: "1920x1200"
    },
    {
      value: 1.6,
      color: "#800000",
      highlight: "#800000",
      label: "720x1280"
    },
    {
      value: 1.1,
      color: "#FF4500",
      highlight: "#FF4500",
      label: "480x800"
    }

  ];
  var pieOptions = {
    //Boolean - Whether we should show a stroke on each segment
    segmentShowStroke: true,
    //String - The colour of each segment stroke
    segmentStrokeColor: "#fff",
    //Number - The width of each segment stroke
    segmentStrokeWidth: 1,
    //Number - The percentage of the chart that we cut out of the middle
    percentageInnerCutout: 50, // This is 0 for Pie charts
    //Number - Amount of animation steps
    animationSteps: 100,
    //String - Animation easing effect
    animationEasing: "easeOutBounce",
    //Boolean - Whether we animate the rotation of the Doughnut
    animateRotate: true,
    //Boolean - Whether we animate scaling the Doughnut from the centre
    animateScale: false,
    //Boolean - whether to make the chart responsive to window resizing
    responsive: true,
    // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio: false,
    //String - A legend template
    legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>",
    //String - A tooltip template
    tooltipTemplate: "<%=value %> % for <%=label%>"
  };
  //Create pie or douhnut chart
  // You can switch between pie and douhnut using the method below.
  accessMetricsDeviceResolutionChart.Doughnut(accessMetricsDeviceResolutionChartData, pieOptions);


  //-----------------------------------------------------------------------------------
  //- Device Configurations Pie Chart -
  //-----------------------------------------------------------------------------------
  // Get context with jQuery - using jQuery's .get() method.
  var accessMetricsConfigurationsChartCanvas = $("#accessMetricsConfigurationsChart").get(0).getContext("2d");
  var accessMetricsConfigurationsChart = new Chart(accessMetricsConfigurationsChartCanvas);
  var accessMetricsConfigurationsChartData = [
    {
      value: 15.1,
      color: "#f56954",
      highlight: "#f56954",
      label: "Windows/chrome/1366x768"
    },
    {
      value: 8.4,
      color: "#00a65a",
      highlight: "#00a65a",
      label: "Windows/Firefox/1920x1080"
    },
    {
      value: 8.5,
      color: "#f39c12",
      highlight: "#f39c12",
      label: "Linux/Chrome/1280x800"
    },
    {
      value: 5.4,
      color: "#00c0ef",
      highlight: "#00c0ef",
      label: "Windows/Safari/320x568"
    },
    {
      value: 5.7,
      color: "#3c8dbc",
      highlight: "#3c8dbc",
      label: "Windows/firefox/1440x900"
    },
    {
      value: 5.5,
      color: "#9932CC",
      highlight: "#9932CC",
      label: "Ubuntu/Chrome/1280x1024"
    },
    {
      value: 5.2,
      color: "#008B8B",
      highlight: "#008B8B",
      label: "Windows/Safari/320x480"
    },
    {
      value: 4.6,
      color: "#7FFFD4",
      highlight: "#7FFFD4",
      label: "MAC/Safari/1600x900"
    },
    {
      value: 4.5,
      color: "#DAA520",
      highlight: "#DAA520",
      label: "Windows/Opera/768x1024"
    },
    {
      value: 3.9,
      color: "#5F9EA0",
      highlight: "#5F9EA0",
      label: "MAC/Safari/1024x768"
    },
    {
      value: 3.8,
      color: "#D2691E",
      highlight: "#D2691E",
      label: "Windows/Chrome/1680x1050"
    },
    {
      value: 3.3,
      color: "#FF7F50",
      highlight: "#FF7F50",
      label: "Ubuntu/Opera/360x640"
    },
    {
      value: 2.7,
      color: "#20B2AA",
      highlight: "#20B2AA",
      label: "Windows/Firefox/1920x1200"
    },
    {
      value: 2.6,
      color: "#800000",
      highlight: "#800000",
      label: "Windows/Chrome/720x1280"
    },
    {
      value: 3.1,
      color: "#FF4500",
      highlight: "#FF4500",
      label: "MAC/safari/480x800"
    }

  ];
  var pieOptions = {
    //Boolean - Whether we should show a stroke on each segment
    segmentShowStroke: true,
    //String - The colour of each segment stroke
    segmentStrokeColor: "#fff",
    //Number - The width of each segment stroke
    segmentStrokeWidth: 1,
    //Number - The percentage of the chart that we cut out of the middle
    percentageInnerCutout: 50, // This is 0 for Pie charts
    //Number - Amount of animation steps
    animationSteps: 100,
    //String - Animation easing effect
    animationEasing: "easeOutBounce",
    //Boolean - Whether we animate the rotation of the Doughnut
    animateRotate: true,
    //Boolean - Whether we animate scaling the Doughnut from the centre
    animateScale: false,
    //Boolean - whether to make the chart responsive to window resizing
    responsive: true,
    // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio: false,
    //String - A legend template
    legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>",
    //String - A tooltip template
    tooltipTemplate: "<%=value %> % for <%=label%>"
  };
  //Create pie or douhnut chart
  // You can switch between pie and douhnut using the method below.
  accessMetricsConfigurationsChart.Doughnut(accessMetricsConfigurationsChartData, pieOptions);