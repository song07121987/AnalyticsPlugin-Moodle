// Data table (List View)
$(function () {
  var url1 = "ajax/getvisitors.php";
  if ( start != '') {
    url1 = url1 + "?start=" + start + "&end=" + end;
  }

  $('#visitorLogListViewTable').DataTable({
      "processing": true,
      "serverSide": true,
      "searchDelay": 1000,
      "ajax": {
          "url": "ajax/getvisitors.php",
          "type": "GET",
          "data": { "userid" : userid},
          "dataType": "json",
          "complete" : function(x) {
              console.dir(x);
          }
      },
  });

  $("#visitor-log-details-view").DataTable({
      "processing": true,
      "serverSide": true,
      "searchDelay": 1000,
      "ajax": {
          "url": "ajax/getvisitorsdetails.php",
          "type": "GET",
          "data": { "userid" : userid},
          "dataType": "json",
          "complete" : function(x) {
              console.dir(x);
          }
      },
  });
});