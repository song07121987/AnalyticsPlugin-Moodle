<?php
require_once('../../../config.php');
require_once($CFG->dirroot . '/lib/accesslib.php');

$enabletracking = get_config('local_analytics', 'enabletracking');
$showenhancedanalytics = get_config('local_analytics', 'showenhancedanalytics');

if(! $enabletracking){
    redirect($CFG->wwwroot . '/administration/index.php');
    return;
}
if(! $showenhancedanalytics) {
    redirect($CFG->wwwroot . '/administration/index.php');
    return;
}

global $USER;
$courseid = optional_param('course', 1, PARAM_INT);
$urlparams = array('id' => $courseid );

$isInstructor = true;
if ($courseid > 1) {
    require_once($CFG->dirroot."/local/analytics/reports/mq_functions.php");
    $isInstructor = checkIfInstructorOfCourse($courseid) || checkIfUnitAdminOfCourse($courseid);;    
}
require_once($CFG->dirroot."/local/analytics/reports/administrationlib.php");
if ( !hasMasterAdminRole($USER->id) && !$isInstructor) {
    redirect($CFG->wwwroot . '/administration/index.php');
    return;
}

// To verify if user is an instructor in the course
$course = $DB->get_record('course', $urlparams, '*', MUST_EXIST);
require_login($course);

if ($courseid > 1) {
    $PAGE->set_title($COURSE->fullname . ' / Engagament Analytics');
} else {
    $PAGE->set_title('Site Engagament Analytics');
}
$PAGE->set_url('/local/analytics/reports/engagement.php', $urlparams); // Defined here to avoid notices on errors etc
$PAGE->set_cacheable(false);
$mobile = optional_param('mobile', 0, PARAM_INT);
if ($mobile == 1)
    $PAGE->set_pagelayout('adminmobile');
else
    $PAGE->set_pagelayout('administration');
$PAGE->set_pagetype('admin');
$PAGE->set_heading($COURSE->fullname);

require_once('lib/analyticslib.php');
$startdate = analytics_getStartDate();
$enddate = analytics_getEndDate();
$report = getEngagementReport($startdate, $enddate, $courseid);

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
</style>
    <script type="text/javascript" src="js/moment.min.js"></script>
    <script type="text/javascript" src="js/daterangepicker.js"></script>
    <link rel="stylesheet" type="text/css" href="css/daterangepicker.css" />
    <script src="js/Chart.min.js"></script>
    <script src="js/jquery.sparkline.min.js"></script>
    <script src="js/d3.js"></script>
    <script src="js/d3-tip.js"></script>
    <section class="content-header">
        <h1>
            Engagement
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
                <?php if (isset($startdate) && isset($enddate)) { ?>
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
                var param = "";
                if (start != '') {
                    param = "?start="+start+"&end="+end;
                }
                window.location.href = "engagement.php"+param;
            });
        </script>
    </section>
    <section class="content">
        <!-- Info boxes -->
        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Returning Visitors from - <?php echo $rptdate; ?></h3>
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
                        <div class="col-md-6"> <i class="fa fa-check-circle"></i> <?php echo $report->stats->repeatvisit; ?> returning visits - Web (<?php echo $report->stats->repeatwebvisit; ?>) / Mobile (<?php echo $report->stats->repeatmobilevisit; ?>)</div>
                        <div class="col-md-6"> <i class="fa fa-check-circle"></i> <?php echo secondsToTime((int) safediv($report->stats->repeatactiontime, $report->stats->repeatvisit)); ?> average visit duration for returning visitors</div>
                        <div class="col-md-6"> <i class="fa fa-check-circle"></i> <?php echo secondsToTime((int) safediv($report->stats->repeatwebtime , $report->stats->repeatwebvisit)); ?> average visit duration for web returning visitors</div>
                        <div class="col-md-6"> <i class="fa fa-check-circle"></i> <?php echo secondsToTime((int) safediv($report->stats->repeatmobiletime , $report->stats->repeatmobilevisit)); ?> average visit duration for mobile returning visitors</div>
                        <div class="col-md-6"> <i class="fa fa-check-circle"></i> <?php echo $report->stats->repeatactions; ?> actions by the returning visits - Web (<?php echo $report->stats->repeatwebactions; ?>) / Mobile (<?php echo $report->stats->repeatmobileactions; ?>)</div>
                        <div class="col-md-6"> <i class="fa fa-check-circle"></i> <?php echo number_format(safediv($report->stats->repeatactions , $report->stats->repeatvisit), 2); ?> actions per returning visit</div>
                        <div class="col-md-6"> <i class="fa fa-check-circle"></i> <?php echo number_format(safediv($report->stats->repeatwebactions , $report->stats->repeatwebvisit), 2); ?> actions per web returning visit</div>
                        <div class="col-md-6"> <i class="fa fa-check-circle"></i> <?php echo number_format(safediv($report->stats->repeatmobileactions , $report->stats->repeatmobilevisit), 2); ?> actions per mobile returning visit</div>
                    </div><!-- ./box-body -->
                </div><!-- /.box -->
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-12">
                <div class="box halfbox">
                    <div class="box-header">
                        <h3 class="box-title">Visits Per Visit Duration</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div style="margin-top: 40px;"></div>
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>Time Period</th>
                                <th>Number of visits</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($report->visitduration as $rows) { ?>
                                <tr>
                                    <td><?php echo $rows->display; ?></td>
                                    <td><?php echo $rows->cnt; ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div><!-- /.box-body -->
                </div>
                <div class="box halfbox">
                    <div class="box-header">
                        <h3 class="box-title">Visits Per Number of Pages</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div style="margin-top: 40px;"></div>
                        <table id="example2" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>Number of Pages</th>
                                <th>Number of visits</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($report->visitpages as $rows) { ?>
                                <tr>
                                    <td><?php echo $rows->display; ?></td>
                                    <td><?php echo $rows->cnt; ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div><!-- /.box-body -->
                </div>
            </div>
        </div><!-- /.row -->
    </section>
    <script>
        $(function () {
            $("#example1").DataTable( { "bSort" : false, "paging": false, "info": false, "searching": false });
            $("#example2").DataTable( { "bSort" : false, "paging": false, "info": false, "searching": false  });
        });
    </script>
<?php
echo $OUTPUT->footer();
?>
