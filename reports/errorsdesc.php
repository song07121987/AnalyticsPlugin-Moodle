<?php
require_once('../../../config.php');
require_once($CFG->dirroot . '/lib/accesslib.php');

$enabletracking = get_config('local_analytics', 'enabletracking');

if(! $enabletracking){
    redirect($CFG->wwwroot . '/administration/index.php');
    return;
}

global $USER;
$siteid = optional_param('siteid', 1, PARAM_INT);
$page = optional_param('page', '', PARAM_ALPHANUM);
$errorid = optional_param('id', 0, PARAM_INT);

$isInstructor = true;
if ($siteid > 1) {
    require_once($CFG->dirroot."/local/analytics/reports/mq_functions.php");
    $isInstructor = checkIfInstructorOfCourse($siteid) || checkIfUnitAdminOfCourse($courseid);;    
}
require_once($CFG->dirroot."/local/analytics/reports/administrationlib.php");
if ( !hasMasterAdminRole($USER->id) && !$isInstructor) {
    redirect($CFG->wwwroot . '/administration/index.php');
    return;
}

$urlparams = array('id' => $siteid);

// To verify if user is an instructor in the course
$course = $DB->get_record('course', $urlparams, '*', MUST_EXIST);
require_login($course);

$PAGE->set_title($COURSE->fullname.' / Analytic');
$PAGE->set_url('/local/analytics/reports/index.php', $urlparams); // Defined here to avoid notices on errors etc
$PAGE->set_cacheable(false);
$mobile = optional_param('mobile', 0, PARAM_INT);
if ($mobile == 1)
    $PAGE->set_pagelayout('adminmobile');
else
    $PAGE->set_pagelayout('administration');
$PAGE->set_pagetype('admin');
$PAGE->set_heading("Error Analytics");

$appname = optional_param('appname', '', PARAM_TEXT);
$version = optional_param('version', '', PARAM_TEXT);

require_once('lib/errorlib.php');
require_once('lib/analyticslib.php');
$startdate = analytics_getStartDate();
$enddate = analytics_getEndDate();

$report = getErrorStats2($startdate, $enddate, $appname, $version, $errorid);

// When we display - we subtract 1 day
$enddate->sub(new DateInterval("P1D"));
$rptdate = $startdate->format("d M Y").' to '.$enddate->format("d M Y");

echo $OUTPUT->header();

?>
<style>
    .info-box {
        box-shadow: 1px 1px 1px 2px rgba(0,0,0,0.2)
    }
    .halfbox {
        width:50%;
        padding:10px;
        float:left;
    }
    .bar {  fill: steelblue; }
    .bar:hover {  fill: brown;  }
    .axis { font: 10px sans-serif; }
    .axis path, .axis line {
        fill: none;
        stroke: #000;
        shape-rendering: crispEdges;
    }
    .d3-tip {
        line-height: 1;
        font-family: sans-serif;
        padding: 5px;
        background: rgba(0, 0, 0, 0.8);
        color: #fff;
        border-radius: 8px;
    }
    #error3-bug-description {
        float: left;
        border: 1px solid gray;
    }
    #error3-bug-description .bug-error-details {
        float: left;
    }
    .bug-error-details .bug-error-unresolved {
        background-color: #ddd;
        padding: 11px 15px;
        float: left;
        width: 100%;
    }
    .bug-error-details .error-unresolved {
        float: left;
        font-size: 16px;
        font-weight: 600;
    }
    .bug-error-details .error-unresolved i {
        font-size: 35px;
    }
    .bug-error-details .mark-resolved {
        float: right;
        padding: 5px 8px;
        border: 1px solid gray;
        color: #018601;
        font-size: 16px;
    }
    .bug-error-details .error-unresolved span {
        vertical-align: top;
        display: inline-block;
        padding-top: 6px;
        padding-left: 7px;
    }
    .bug-error-details .bug-error-description {
        padding: 15px 20px;
        float: left;
    }
</style>
    <script type="text/javascript" src="js/moment.min.js"></script>
    <script type="text/javascript" src="js/daterangepicker.js"></script>
    <link rel="stylesheet" type="text/css" href="css/daterangepicker.css" />
    <script src="js/Chart.min.js"></script>
    <script src="js/jquery.sparkline.min.js"></script>
    <script src="js/d3.js"></script>
    <script src="js/d3-tip.js"></script>
    <script src="js/gauge.js"></script>

    <section class="content-header">
        <h1>
            Error Description
            <small>Version 2.1</small>
        </h1>
        <div id="reportrange" class="pull-right" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; margin-top:-30px;">
            <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp; <span></span> <b class="caret"></b>
        </div>
        <script type="text/javascript">
            $(function() {
                function cb(start, end) {
                    $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
                }
                function cb2(start, end) {
                    $('#reportrange span').html(start + ' - ' + end);
                }
                <?php if (isset($startdate) && isset($enddate)) { 

                    ?>
                cb2('<?php echo $startdate->format("M d, Y"); ?>', '<?php echo $enddate->format("M d, Y"); ?>');
                <?php } else { ?>
                cb(moment().subtract(29, 'days'), moment());
                <?php } ?>
                $('#reportrange').daterangepicker({
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
            $('#reportrange').on('apply.daterangepicker', function(ev, picker) {
                //do something, like clearing an input
                var start = $('#reportrange').data('daterangepicker').startDate;
                var end = $('#reportrange').data('daterangepicker').endDate;
                var errorid = <?php echo $errorid ?>;
                var param = "";
                if (start != '') {
                    param = "?id="+errorid+"&start="+start+"&end="+end;
                }
                window.location.href = "errorsdesc.php"+param;
            });
        </script>
    </section>
    <section class="content">

        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">No of Crashes over time - <?php echo $rptdate; ?></h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <p class="text-center">
                                </p>
                                <div class="chart">
                                    <!-- Sales Chart Canvas -->
                                    <svg class="chart" data-bar-chart data-data='<?php echo $report->graphdata; ?>' style="width:100%; height:350px;"></svg>
                                    <script>
                                        $('[data-bar-chart]').each(function (i, svg) {
                                            var el = d3.select('svg').node()
                                            var twidth = el.clientWidth;
                                            var theight = el.clientHeight;
                                            console.log(twidth);

                                            var margin = {top: 20, right: 20, bottom: 60, left: 50},
                                                width = twidth - margin.left - margin.right,
                                                height = theight - margin.top - margin.bottom;

                                            var parseDate = d3.time.format("%d-%m-%Y").parse;

                                            var $svg = $(svg);
                                            var data = $svg.data('data');

                                            data.forEach(function(d) {
                                                d.date = parseDate(d.date);
                                                d.count = +d.count;
                                            });
                                            data.sort(function (a, b) {
                                                return a.date - b.date;
                                            });

                                            var countfn = function (d) { return d.count; }
                                            var datefn = function (d) { return d.date; }
                                            var datefn2 = function (d) { var d1 = d.date.getTime() + (1 * 86400000) ; return new Date(d1); }

                                            var x = d3.time.scale()
                                                .range([0, width])
                                                .domain([d3.min(data, datefn), d3.max(data, datefn2)]);

                                            var y = d3.scale.linear()
                                                .domain([0, d3.max (data, countfn)])
                                                .range([height, 0]);

                                            var xAxis = d3.svg.axis()
                                                .scale(x)
                                                .orient("bottom")
                                                .ticks(d3.time.day, 1)
                                                .tickFormat(d3.time.format('%d %b %Y'));

                                            var yAxis = d3.svg.axis()
                                                .scale(y)
                                                .orient("left");

                                            var tip = d3.tip()
                                                .attr('class', 'd3-tip')
                                                .offset([-10, 0])
                                                .html(function(d) {
                                                    return d.count + " errors";
                                                })

                                            var svg = d3.select(svg)
                                                .attr("width", width + margin.left + margin.right)
                                                .attr("height", height + margin.top + margin.bottom)
                                                .append("g")
                                                .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

                                            svg.append("g")
                                                .attr("class", "x axis")
                                                .attr("transform", "translate(0," + height + ")")
                                                .call(xAxis)
                                                .selectAll("text")
                                                .style("text-anchor", "end")
                                                .attr("dx", "-.9em")
                                                .attr("dy", ".25em")
                                                .attr("transform", "rotate(-40)");

                                            svg.append("g")
                                                .attr("class", "y axis")
                                                .call(yAxis)
                                                .append("text")
                                                .attr("transform", "rotate(-90)")
                                                .attr("y", 6)
                                                .attr("dy", ".71em")
                                                .style("text-anchor", "end")
                                                .text("Frequency");

                                            svg.call(tip);

                                            svg.selectAll(".bar")
                                                .data(data)
                                                .enter().append("rect")
                                                .attr("class", "bar")
                                                .attr("x", function(d) { return x(d.date); })
                                                // .attr("width", function (d) { var next = d3.time.month.offset(d.date, 1); return (x(next) - x(d)); })
                                                .attr("width", width / 30)
                                                .attr("y", height)
                                                .attr("height", 0)
                                                /*
                                                .transition().duration(1000)
                                                .attr("y", function(d) { return y(d.count); })
                                                .attr("height", function(d) { return height - y(d.count); })
                                                */
                                                .transition().delay(function (d,i){ return i * 100;})
                                                .duration(100)
                                                .attr("height", function(d) { return height - y(d.count); })
                                                .attr("y", function(d) { return y(d.count); })
                                                .on('mouseover', tip.show)
                                                .on('mouseout', tip.hide);
                                        });

                                    </script>
                                    <!--
                                    <canvas id="salesChart" style="height: 140px;"></canvas>
                                    -->
                                </div><!-- /.chart-responsive -->
                            </div><!-- /.col -->
                        </div><!-- /.row -->
                    </div><!-- ./box-body -->
                </div><!-- /.box -->
            </div><!-- /.col -->
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="box" id="error-3-tab-area">

                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active"><a href="#error3-bug-description" aria-controls="#error3-bug-description" role="tab" data-toggle="tab">Bug Description </a></li>
                        <li role="presentation"><a href="#error3-device-breakdown" aria-controls="#error3-device-breakdown" role="tab" data-toggle="tab">Device Breakdown </a></li>
                        <li role="presentation"><a href="#error3-users-listing" aria-controls="#error3-users-listing" role="tab" data-toggle="tab">Users Listing</a></li>
                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content" style="overflow:hidden;">

                        <div role="tabpanel" class="tab-pane active" id="error3-bug-description" style="width:100%">

                            <div class="bug-error-details" style="width:inherit">

                                <div class="bug-error-unresolved">
                                    <div class="error-unresolved">
                                        <span> Error Information </span>
                                    </div>
                                    <!-- <div class="mark-resolved">
                                        <i class="fa fa-check" aria-hidden="true"></i> Mark Resolved
                                    </div> -->
                                </div>
                                <div class="bug-error-description">
                                    <p>
                                    Description:<br><br>
                                    <?php echo $report->description; ?>
                                    <?php if ($report->stack != '') {  ?>
                                    <br><br>Stack:<br>
                                    <?php echo $report->stack; 
                                    } ?>
                                    </p>
                                </div>

                            </div>

                        </div> <!-- end #error3-bug-description -->

                        <div role="tabpanel" class="tab-pane" id="error3-device-breakdown">
                            <h4>Device Statistics</h4>
                            <div class="row">

                                <div class="col-md-6 device">
                                    <h5>Top Three Devices</h5>
                                    <ul>
                                        <?php 
                                            $imgmap = array(
                                                    'WINDOWS' => '<i class="fa fa-windows" aria-hidden="true"></i>',
                                                    'ANDROID' => '<i class="fa fa-android" aria-hidden="true"></i>',
                                                    'APPLE' => '<i class="fa fa-apple" aria-hidden="true"></i>'
                                                );

                                            foreach ($report->top3d as $key => $value) {?>
                                            <li><?php echo $key . ' ('. number_format( $value / $report->errorcount * 100, 2 ) . '%)' ?></li>
                                        <?php } ?>
                                    </ul>
                                </div>
                                <div class="col-md-6 device">
                                    <h5>Top Three OS</h5>
                                    <ul>
                                        <?php 
                                            foreach ($report->top3o as $key => $value) {?>
                                            <li><?php echo $imgmap[$key]; ?> <?php echo $key . ' ('. number_format( $value / $report->errorcount * 100, 2 ) . '%)' ?></li>
                                        <?php } ?>
                                    </ul>
                                </div>
                                <!-- <div class="col-md-9 gauce">
                                    <span id="dashboardContainer"></span>
                                    <span id="memoryGaugeContainer"></span>
                                    <span id="cpuGaugeContainer"></span>
                                    <span id="networkGaugeContainer"></span>
                                </div> -->

                            </div> <!-- end .row -->

                        </div> <!-- end  #error3-device-breakdown -->

                        <div role="tabpanel" class="tab-pane" id="error3-users-listing">
                            <div class="box">

                                <div class="box-header">
                                    <h3 class="box-title">User Listing</h3>
                                </div><!-- /.box-header -->

                                <div class="box-body">

                                    <table id="errorLogThreeUserListingTbl" class="table table-bordered table-striped dt-responsive errorTbl23" width="100%">
                                        <thead>
                                        <tr>
                                            <th>NRIC</th>
                                            <th>User Name</th>
                                            <th>Last Error Occurence</th>
                                            <th>No. of Occurence</th>
                                        </tr>
                                        </thead>

                                        <tbody>
                                        <?php foreach ($report->userdata as $key => $value) {?>
                                            <tr>
                                                <td> <?php echo $value->idnumber ?></td>
                                                <td> <?php echo $value->firstname ?> </td>
                                                <td> <?php echo $value->lastoccured ?></td>
                                                <td> <?php echo $value->occurence ?> </td>
                                            </tr>
                                        <?php } ?>
                                        
                                        
                                    </table>

                                </div><!-- /.box-body -->

                            </div> <!-- end .box -->
                        </div> <!-- end #error3-users-listing -->

                    </div> <!-- end .tab-content -->
                </div>
            </div>
        </div><!-- /.row -->
    </section>
    <script>
        //alert (document.getElementById('dashboardContainer').clientWidth);
        createDashboard();

        function createDashboard() {
            var pwidth = 600;
            var pheight = 300;
            var sb = pwidth/200 * 50;
            createDash(pwidth, pheight);
            readings["memory"] = 62;
            readings["cpu"] = 82;
            readings["network"] = 25;
            createGauge(dashContainer, "memory", "Memory", sb, (pwidth/4)-50,pheight/2, {
                from: 80, to: 100 }, {
                from: 65, to: 80 }, {
                from:  0, to: 65});
            createGauge(dashContainer, "cpu", "Free Space", sb+64, pwidth/2,pheight/2, {// third is +size bias.
                from: 90, to: 100 }, {
                from: 75, to: 90 }, {
                from: 0, to: 75});
            createGauge(dashContainer, "network", "Battery", sb, (pwidth/4)+(pwidth/2)+50, pheight/2, {
                from: 70, to: 100 }, {
                from: 55, to: 70 }, {
                from: 0, to: 55});
        }

    </script>
<?php
echo $OUTPUT->footer();
?>
