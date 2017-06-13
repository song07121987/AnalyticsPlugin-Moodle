// Report range calender of Visitor Log Page

$(function() {
    function cb(start, end) {
      $('#most-active-page-reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    }
    cb(moment().subtract(29, 'days'), moment());
    $('#most-active-page-reportrange').daterangepicker({
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

  $('#most-active-page-reportrange').on('apply.daterangepicker', function(ev, picker) {
    //do something, like clearing an input
    alert ($('#most-active-page-reportrange'));

  });


// Data table (List View)


$(function () {

    $("#mostActivePageDetailsTbl").DataTable();
    
});