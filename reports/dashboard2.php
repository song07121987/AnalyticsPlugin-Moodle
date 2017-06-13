<?php
require_once('../../../config.php');
require_once($CFG->dirroot.'/lib/accesslib.php');

$enabletracking = get_config('local_analytics', 'enabletracking');
$showenhancedanalytics = get_config('local_analytics', 'showenhancedanalytics');

if(! $enabletracking){
    redirect($CFG->wwwroot . '/administration/index.php');
    return;
}
if(! $showenhancedanalytics) {
    redirect($CFG->wwwroot . '/local/analytics/reports/dashboard.php');
    return;
}

global $USER;
$courseid = optional_param('course', 1, PARAM_INT);
$urlparams = array('id' => $courseid);

$isInstructor = true;
if ($courseid > 1) {
    require_once($CFG->dirroot."/local/analytics/reports/mq_functions.php");
    $isInstructor = checkIfInstructorOfCourse($courseid);    
}
require_once($CFG->dirroot."/local/analytics/reports/administrationlib.php");
if ( !hasAdminRole($USER->id) && !$isInstructor) {
    redirect($CFG->wwwroot . '/administration/index.php');
    return;
}

// To verify if user is an instructor in the course
$course = $DB->get_record('course', $urlparams, '*', MUST_EXIST);
require_login($course);

if ($courseid > 1) {
    $PAGE->set_title($COURSE->fullname . ' / Analytics');
} else {
    $PAGE->set_title('System Analytics');
}
$PAGE->set_url('/local/analytics/reports/dashboard2.php', $urlparams); // Defined here to avoid notices on errors etc
$PAGE->set_cacheable(false);

$mobile = optional_param('mobile', 0, PARAM_INT);
if ($mobile == 1)
    $PAGE->set_pagelayout('adminmobile');
else
    $PAGE->set_pagelayout('administration');
$PAGE->set_pagetype('admin');
$PAGE->set_heading($COURSE->fullname);

require_once('lib/analyticslib.php');
$startdate = analytics_getStartDate30();
$enddate = analytics_getEndDate();

$report = getLandingReport2($startdate, $enddate, $courseid);
// When we display - we subtract 1 day
$enddate->sub(new DateInterval("P1D"));
$rptdate = $startdate->format("d M Y").' to '.$enddate->format("d M Y");

$params = "";
if ($courseid > 1)
    $params = '?course='.$courseid;

echo $OUTPUT->header();
?>
<style>
    .info-box {
        box-shadow: 1px 1px 1px 2px rgba(0,0,0,0.2)
    }
    .halfbox {
        width: 49%;
        padding: 10px;
        display: inline-block;
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
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/admin-mobile.css">
    <script src="js/Chart.min.js"></script>
    <script src="js/jquery.sparkline.min.js"></script>
    <script src="js/d3.js"></script>
    <script src="js/d3-tip.js"></script>
    <section class="content-header">
        <h1>
            Dashboard
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
                window.location.href = "dashboard2.php"+param;
            });
        </script>
    </section>
    <section class="content">
        <!-- Info boxes -->
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <a href="usersummary.php<?php echo $params; ?>">
                        <span class="info-box-icon bg-aqua"><i class="ion ion-ios-people-outline"></i></span>
                    </a>
                    <div class="info-box-content">
                        <span class="info-box-text">Users / Visits</span>
                        <span class="info-box-number"><?php echo number_format($report->stats->uniquevisitor); ?> / <?php echo number_format($report->stats->totalvisit); ?> </span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->
            </div><!-- /.col -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <a href="engagement.php<?php echo $params; ?>">
                        <span class="info-box-icon bg-red"><i class="ion ion-android-people"></i></span>
                    </a>
                    <div class="info-box-content">
                        <span class="info-box-text">NO of repeat users</span>
                        <span class="info-box-number"><?php echo number_format($report->stats->totalrepeatvisit); ?></span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->
            </div><!-- /.col -->

            <!-- fix for small devices only -->
            <div class="clearfix visible-sm-block"></div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <a href="pagestats.php<?php echo $params; ?>">
                        <span class="info-box-icon bg-green"><i class="ion ion-clipboard"></i></span>
                    </a>
                    <div class="info-box-content">
                        <span class="info-box-text">No of actions</span>
                        <span class="info-box-number"><?php echo number_format($report->stats->totalactions, 0); ?></span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->
            </div><!-- /.col -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <a href="visitorlog.php<?php echo $params; ?>">
                        <span class="info-box-icon bg-yellow"><i class="ion ion-clock"></i></span>
                    </a>
                    <div class="info-box-content">
                        <span class="info-box-text">Average Time On Page</span>
                        <span class="info-box-number"><?php echo (int)  (safediv($report->stats->totaltime, $report->stats->totalactions)); ?> secs</span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->
            </div><!-- /.col -->
        </div><!-- /.row -->
        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Visit Over Time</h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-8">
                                <p class="text-center">
                                    <strong><?php echo $rptdate; ?></strong>
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
                                                d.totalvisit = +d.totalvisit;
                                            });
                                            data.sort(function (a, b) {
                                                return a.date - b.date;
                                            });

                                            var countfn = function (d) { return d.totalvisit; }
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
                                                    return d.totalvisit + " visits";
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
                                                .attr("height", function(d) { return height - y(d.totalvisit); })
                                                .attr("y", function(d) { return y(d.totalvisit); })
                                                .on('mouseover', tip.show)
                                                .on('mouseout', tip.hide);

                                            console.log(data);

                                        });

                                    </script>
                                </div><!-- /.chart-responsive -->
                            </div><!-- /.col -->
                            <div class="col-md-4">
                                <p class="text-center">
                                    <strong>Monthly Metrics</strong>
                                </p>
                                <div class="progress-group">
                                    <span class="progress-text">Unique Visitors / Visitors</span>
                                    <span class="progress-number"><?php echo $report->stats->uniquevisitor; ?>/<?php echo $report->stats->totalvisit; ?></span>
                                    <div class="progress sm">
                                        <div class="progress-bar progress-bar-aqua" style="width: <?php echo $report->stats->uniquevisitor / $report->stats->totalvisit * 100; ?>%"></div>
                                    </div>
                                </div><!-- /.progress-group -->
                                <div class="progress-group">
                                    <span class="progress-text">Returning Visitors / Visitors</span>
                                    <span class="progress-number"><?php echo $report->stats->totalrepeatvisit; ?>/<?php echo $report->stats->totalvisit; ?></span>
                                    <div class="progress sm">
                                        <div class="progress-bar progress-bar-yellow" style="width: <?php echo $report->stats->totalrepeatvisit / $report->stats->totalvisit * 100; ?>%"></div>
                                    </div>
                                </div><!-- /.progress-group -->
                                <div class="progress-group">
                                    <span class="progress-text">Average Actions / Maximum Actions</span>
                                    <span class="progress-number"><?php echo (int) (safediv($report->stats->totalactions, $report->stats->totalvisit)); ?>/<?php echo $report->stats->maxactions; ?></span>
                                    <div class="progress sm">
                                        <div class="progress-bar progress-bar-yellow" style="width: <?php echo $report->stats->totalactions / $report->stats->totalvisit / $report->stats->maxactions * 100; ?>%"></div>
                                    </div>
                                </div><!-- /.progress-group -->
                                <div class="progress-group">
                                    <span class="progress-text">Visit Duration / Maximum Duration</span>
                                    <span class="progress-number"><?php echo (int) (safediv($report->stats->totaltime, $report->stats->totalactions)); ?>/<?php echo $report->stats->maxtime; ?></span>
                                    <div class="progress sm">
                                        <div class="progress-bar progress-bar-red" style="width: <?php echo $report->stats->totaltime / $report->stats->totalactions / $report->stats->maxtime * 100; ?>%"></div>
                                    </div>
                                </div><!-- /.progress-group -->
                                <div class="progress-group">
                                    <span class="progress-text">Page Load Time / Max Load Time</span>
                                    <span class="progress-number"><b>250</b>/500</span>
                                    <div class="progress sm">
                                        <div class="progress-bar progress-bar-yellow" style="width: 80%"></div>
                                    </div>
                                </div><!-- /.progress-group -->
                                <!--
                                <div class="progress-group">
                                    <span class="progress-text">Bounce Rate / Number of Visits</span>
                                    <span class="progress-number"><b>480</b>/800</span>
                                    <div class="progress sm">
                                        <div class="progress-bar progress-bar-green" style="width: 80%"></div>
                                    </div>
                                </div><!-- /.progress-group -->
                                <div class="progress-group">
                                    <span class="progress-text">Unique Downloads / Total Downloads</span>
                                    <span class="progress-number"><b>250</b>/500</span>
                                    <div class="progress sm">
                                        <div class="progress-bar progress-bar-yellow" style="width: 80%"></div>
                                    </div>
                                </div><!-- /.progress-group -->
                                <div class="progress-group">
                                    <span class="progress-text">Unique Keyword / Total Searches</span>
                                    <span class="progress-number"><b>250</b>/500</span>
                                    <div class="progress sm">
                                        <div class="progress-bar progress-bar-yellow" style="width: 80%"></div>
                                    </div>
                                </div><!-- /.progress-group -->
                                <!--
                                <div class="progress-group">
                                    <span class="progress-text">Unique Outlinks / Outlinks</span>
                                    <span class="progress-number"><b>250</b>/500</span>
                                    <div class="progress sm">
                                        <div class="progress-bar progress-bar-yellow" style="width: 80%"></div>
                                    </div>
                                </div><!-- /.progress-group -->
                            </div><!-- /.col -->
                        </div><!-- /.row -->
                    </div><!-- ./box-body -->
                    <div class="box-footer">
                        <div class="row">
                            <div class="col-sm-3 col-xs-6">
                                <div class="description-block border-right">
                                    <span class="description-percentage text-green"><i class="fa fa-caret-up"></i> 17%</span>
                                    <h5 class="description-header"><?php echo $report->stats->totalwebvisit; ?></h5>
                                    <span class="description-text">WEB VISITS</span>
                                </div>
                            </div>
                            <div class="col-sm-3 col-xs-6">
                                <div class="description-block border-right">
                                    <span class="description-percentage text-yellow"><i class="fa fa-caret-left"></i> 0%</span>
                                    <h5 class="description-header"><?php echo $report->stats->totalmobilevisit; ?></h5>
                                    <span class="description-text">MOBILE VISITS</span>
                                </div>
                            </div>
                            <div class="col-sm-3 col-xs-6">
                                <div class="description-block border-right">
                                    <span class="description-percentage text-green"><i class="fa fa-caret-up"></i> 20%</span>
                                    <h5 class="description-header"><?php echo $report->stats->totalwebactions; ?></h5>
                                    <span class="description-text">WEB ACTIONS</span>
                                </div>
                            </div>
                            <div class="col-sm-3 col-xs-6">
                                <div class="description-block">
                                    <span class="description-percentage text-red"><i class="fa fa-caret-down"></i> 18%</span>
                                    <h5 class="description-header"><?php echo $report->stats->totalmobileactions; ?></h5>
                                    <span class="description-text">MOBILE ACTIONS</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- /.box -->
            </div><!-- /.col -->

            <div class="col-md-12">
                <div class="halfbox box" style="padding-left:0px;">
                    <div class="box-header">
                        <h3 class="box-title">Top Visited Pages</h3>
                    </div>
                    <div class="box-body no-padding">
                        <table class="table table-condensed">
                            <tr>
                                <th style="width: 10px">#</th>
                                <th>Page</th>
                                <th>Activity</th>
                                <th style="width: 40px">Visits</th>
                            </tr>
                            <?php
                            $id = 1;
                            $maxcnt = 0;
                            foreach ($report->toppages as $rec) {
                                if ($rec->custom_var_v1 == 'Web') {
                                    if ($rec->cnt > $maxcnt)
                                        $maxcnt = $rec->cnt;

                            ?>
                            <tr>
                                <td><?php echo $id; ?>.</td>
                                <td><?php echo $rec->name; ?></td>
                                <td>
                                    <div class="progress progress-xs progress-striped active">
                                        <div class="progress-bar progress-bar-primary" style="width: <?php echo safediv($rec->cnt, $maxcnt) * 100; ?>%"></div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light-blue"><?php echo $rec->cnt; ?></span></td>
                            </tr>
                            <?php
                                $id++;
                                }
                            } ?>
                        </table>
                    </div>
                </div>

                <div class="halfbox box" style="padding-right:0px;">
                    <div class="box-header">
                        <h3 class="box-title">Top Mobile Actions</h3>
                    </div>
                    <div class="box-body no-padding">
                        <table class="table table-condensed">
                            <tr>
                                <th style="width: 10px">#</th>
                                <th>Action</th>
                                <th>Activity</th>
                                <th style="width: 40px">Visits</th>
                            </tr>
                            <?php
                            $id = 1;
                            $maxcnt = 0;
                            foreach ($report->toppages as $rec) {
                                if ($rec->custom_var_v1 == 'Mobile') {
                                    if ($rec->cnt > $maxcnt)
                                        $maxcnt = $rec->cnt;

                                    ?>
                                    <tr>
                                        <td><?php echo $id; ?>.</td>
                                        <td><?php echo $rec->name; ?></td>
                                        <td>
                                            <div class="progress progress-xs progress-striped active">
                                                <div class="progress-bar progress-bar-primary" style="width: <?php echo safediv($rec->cnt, $maxcnt) * 100; ?>%"></div>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-light-blue"><?php echo $rec->cnt; ?></span></td>
                                    </tr>
                                    <?php
                                    $id++;
                                }
                            } ?>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Activity / Resources Breakdown</h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            <div class="btn-group">
                                <button class="btn btn-box-tool dropdown-toggle" data-toggle="dropdown"><i class="fa fa-wrench"></i></button>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="#">Action</a></li>
                                    <li><a href="#">Another action</a></li>
                                    <li><a href="#">Something else here</a></li>
                                    <li class="divider"></li>
                                    <li><a href="#">Separated link</a></li>
                                </ul>
                            </div>
                            <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div><!-- /.box-header -->
                        <div class="col-md-4">
                        <!-- Info Boxes Style 2 -->
                        <div class="info-box bg-yellow" style="margin-top:10px">
                            <span class="info-box-icon"><i class="ion ion-clipboard"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Assignments</span>
                                <span class="info-box-number"><?php echo getActivityCount($report->tbldata, $courseid, 'assign'); ?></span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                                <span class="progress-description">
                                    <?php echo getActivityDesc($report->tbldata, $courseid, 'assign'); ?>
                                </span>
                            </div><!-- /.info-box-content -->
                        </div><!-- /.info-box -->
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-book"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">EPub Packages</span>
                                <span class="info-box-number"><?php echo getActivityCount($report->tbldata, $courseid, 'vmepub'); ?></span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                              <span class="progress-description">
                                    <?php echo getActivityDesc($report->tbldata, $courseid, 'vmepub'); ?>
                              </span>
                            </div><!-- /.info-box-content -->
                        </div><!-- /.info-box -->
                        <div class="info-box bg-red">
                            <span class="info-box-icon"><i class="ion ion-calculator"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Quizzes</span>
                                <span class="info-box-number"><?php echo getActivityCount($report->tbldata, $courseid, 'quiz'); ?></span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                              <span class="progress-description">
                                    <?php echo getActivityDesc($report->tbldata, $courseid, 'quiz'); ?>
                              </span>
                            </div><!-- /.info-box-content -->
                        </div><!-- /.info-box -->
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="ion-ios-chatbubble-outline"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Surveys</span>
                                <span class="info-box-number"><?php echo getActivityCount($report->tbldata, $courseid, 'questionnaire'); ?></span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                              <span class="progress-description">
                                    <?php echo getActivityDesc($report->tbldata, $courseid, 'questionnaire'); ?>
                              </span>
                            </div><!-- /.info-box-content -->
                        </div><!-- /.info-box -->
                    </div>
                    <div class="col-md-4">
                        <!-- Info Boxes Style 2 -->
                        <div class="info-box bg-yellow" style="margin-top:10px">
                            <span class="info-box-icon"><i class="fa fa-edit"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">blog Activities</span>
                                <span class="info-box-number"><?php echo getActivityCount($report->tbldata, $courseid, 'blogactivity'); ?></span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                                <span class="progress-description">
                                    <?php echo getActivityDesc($report->tbldata, $courseid, 'blogactivity'); ?>
                                </span>
                            </div><!-- /.info-box-content -->
                        </div><!-- /.info-box -->
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="ion ion-chatboxes"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Forum Posts</span>
                                <span class="info-box-number"><?php echo getActivityCount($report->tbldata, $courseid, 'forumposts'); ?></span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                              <span class="progress-description">
                                    <?php echo getActivityDesc($report->tbldata, $courseid, 'forumposts'); ?>
                              </span>
                            </div><!-- /.info-box-content -->
                        </div><!-- /.info-box -->
                        <div class="info-box bg-red">
                            <span class="info-box-icon"><i class="ion ion-images"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Resources</span>
                                <span class="info-box-number"><?php echo getActivityCount($report->tbldata, $courseid, 'resource'); ?></span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                              <span class="progress-description">
                                    <?php echo getActivityDesc($report->tbldata, $courseid, 'resource'); ?>
                              </span>
                            </div><!-- /.info-box-content -->
                        </div><!-- /.info-box -->
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="ion ion-link"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Web Links</span>
                                <span class="info-box-number"><?php echo getActivityCount($report->tbldata, $courseid, 'url'); ?></span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                              <span class="progress-description">
                                     <?php echo getActivityDesc($report->tbldata, $courseid, 'url'); ?>
                             </span>
                            </div><!-- /.info-box-content -->
                        </div><!-- /.info-box -->
                    </div>
                    <div class="col-md-4">
                        <!-- Info Boxes Style 2 -->
                        <div class="info-box bg-yellow" style="margin-top:10px">
                            <span class="info-box-icon"><i class="fa fa-folder-open-o"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Course Files</span>
                                <span class="info-box-number"><?php echo getActivityCount($report->tbldata, $courseid, 'files'); ?></span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                                <span class="progress-description">
                                     <?php echo getActivityDesc($report->tbldata, $courseid, 'files'); ?>
                                </span>
                            </div><!-- /.info-box-content -->
                        </div><!-- /.info-box -->
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="ion ion-person-stalker"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Peer Appraisals</span>
                                <span class="info-box-number"><?php echo getActivityCount($report->tbldata, $courseid, 'peerappraisal'); ?></span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                              <span class="progress-description">
                                    <?php echo getActivityDesc($report->tbldata, $courseid, 'peerappraisal'); ?>
                              </span>
                            </div><!-- /.info-box-content -->
                        </div><!-- /.info-box -->
                        <div class="info-box bg-red">
                            <span class="info-box-icon"><i class="ion ion-cube"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">SCORM Packages</span>
                                <span class="info-box-number"><?php echo getActivityCount($report->tbldata, $courseid, 'scorm'); ?></span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                              <span class="progress-description">
                                    <?php echo getActivityDesc($report->tbldata, $courseid, 'scorm'); ?>
                              </span>
                            </div><!-- /.info-box-content -->
                        </div><!-- /.info-box -->
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="ion ion-ios-copy-outline"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Web Pages</span>
                                <span class="info-box-number"><?php echo getActivityCount($report->tbldata, $courseid, 'page'); ?></span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                              <span class="progress-description">
                                    <?php echo getActivityDesc($report->tbldata, $courseid, 'page'); ?>
                              </span>
                            </div><!-- /.info-box-content -->
                        </div><!-- /.info-box -->
                    </div>
                </div>
            </div>
        </div><!-- /.row -->
    </section>
<?php
echo $OUTPUT->footer();
?>
