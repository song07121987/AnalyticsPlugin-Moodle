<?php
require_once('../../../config.php');
require_once($CFG->dirroot . '/lib/accesslib.php');
require_once('lib/analyticslib.php');
require_once($CFG->dirroot."/local/analytics/reports/administrationlib.php");

$enabletracking = get_config('local_analytics', 'enabletracking');
$showenhancedanalytics = get_config('local_analytics', 'showenhancedanalytics');

if(! $enabletracking){
    redirect($CFG->wwwroot . '/administration/index.php');
    return;
}
if (! $showenhancedanalytics) {
    redirect($CFG->wwwroot . '/administration/index.php');
    return;
}

global $USER;
$courseid = optional_param('course', 1, PARAM_INT);
$page = optional_param('page', '', PARAM_ALPHANUM);

$isInstructor = true;
if ($courseid > 1) {
    require_once($CFG->dirroot."/local/analytics/reports/mq_functions.php");
    $isInstructor = checkIfInstructorOfCourse($courseid) || checkIfUnitAdminOfCourse($courseid);
}

if ( !hasMasterAdminRole($USER->id) && !$isInstructor) {
    redirect($CFG->wwwroot . '/administration/index.php');
    return;
}

$urlparams = array('id' => $courseid);

// To verify if user is an instructor in the course
$course = $DB->get_record('course', $urlparams, '*', MUST_EXIST);
require_login($course);

$PAGE->set_title($COURSE->fullname.' / Analytic / Depth Statistics');
$PAGE->set_url('/local/analytics/reports/most_active_page.php', $urlparams); // Defined here to avoid notices on errors etc
$PAGE->set_cacheable(false);
$mobile = optional_param('mobile', 0, PARAM_INT);
if ($mobile == 1)
    $PAGE->set_pagelayout('adminmobile');
else
    $PAGE->set_pagelayout('administration');
$PAGE->set_pagetype('admin');
$PAGE->set_heading($COURSE->fullname);
echo $OUTPUT->header();

$startdate = analytics_getStartDate();
$enddate = analytics_getEndDate();
$report = getDetailsReport($startdate, $enddate, $courseid);
$enddate->sub(new DateInterval("P1D"));

?>
    <style>
        @media screen and (min-width: 769px) {
            table#pageAnalyticsDetailsTbl tr th:first-child {
                min-width: 300px;
            }
        }

        .bar {  fill: steelblue; }
        .area1 {  fill: rgb(210, 214, 222); }
        .bar:hover {  fill: brown;  }
        .bar2 {  fill: green; }
        .area2 {  fill: rgba(60,141,188,0.9); }
        .bar2:hover {  fill: maroon;  }
        .axis { font: 10px sans-serif; }
        .axis path, .axis line {
            fill: none;
            stroke: #000;
            shape-rendering: crispEdges;
        }
        .focus circle {
          fill: none;
          stroke: steelblue;
        }
        .m_name {
            color: cyan;
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
    <!-- ChartJS 1.0.1 -->
    <script src="js/Chart.min.js"></script>
    <!-- Please add additional javascripts here -->
    <script type="text/javascript" src="js/moment.min.js"></script>
    <script type="text/javascript" src="js/daterangepicker.js"></script>
    <link rel="stylesheet" type="text/css" href="css/daterangepicker.css" />
    <link rel="stylesheet" type="text/css" href="css/admin.css" />
    <link rel="stylesheet" type="text/css" href="css/admin-mobile.css">
    <script type="text/javascript" src="js/d3.js"></script>
    <script type="text/javascript" src="js/d3-tip.js"></script>
    <script type="text/javascript" src="js/pages/detailstats.js"></script>

    <style>
        .in-depth-stat-pad {
            padding-bottom: 20px;
        }
        .depth-stat-goal-com {
            padding: 0 20px 5px 20px;
        }
        .depth-stat-goal-com-heading {
            padding-top: 20px;
        }

    </style>
    <!-- Content Header (Page header) -->
    <section class="content-header serv-per-header">
        <h1 style="padding: 5px 0px 5px 10px;">
            In Depth Statistics
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
                window.location.href = "detailstats.php"+param;
            });
        </script>
    </section>
    <!-- Main content -->
    <section class="content">
        <!-- Info boxes -->
        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">No of visits over number of unique visit</h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div><!-- /.box-header -->
                    <div class="box-body in-depth-stat-pad">
                        <p class="text-center">
                            <strong class="visitPeriod">Period: 1 Jan, 2016 - 24 Apr, 2016</strong>
                        </p>
                        <div class="chart">
                            <!-- No of visits over number of unique visit chart -->
                            <!-- <canvas id="depthStatVisit" style="height: 280px;"></canvas> -->
                            <svg class="chart" data-area-visit style="height:280px;"></svg>
                        </div>
                    </div><!-- /.box-body -->
                </div>
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">No of actions over number of unique actions</h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div><!-- /.box-header -->

                    <div class="box-body in-depth-stat-pad">
                        <p class="text-center">
                            <strong class="visitPeriod">Period: 1 Jan, 2016 - 24 Apr, 2016</strong>
                        </p>
                        <div class="chart">
                            <!-- No of actions over number of unique actions chart -->
                            <!-- <canvas id="depthStatActions" style="height: 280px;"></canvas> -->
                            <svg class="chart" data-area-action style="height:280px;"></svg>
                        </div>
                    </div><!-- /.box-body -->

                </div>

            </div> <!-- end .col-md-12 -->

            <div class="col-md-12 in-depth-stat-pad"> <!-- Start No of visit by Web and Mobile -->

                <div class="row">

                    <div class="col-md-6">

                        <div class="box">

                            <div class="box-header with-border">
                                <h3 class="box-title">No of visit by Web</h3>
                                <div class="box-tools pull-right">
                                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                </div>
                            </div><!-- /.box-header -->

                            <div class="box-body">
                                <p class="text-center">
                                    <strong class="visitPeriod">Period: 1 Jan, 2016 - 24 Apr, 2016</strong>
                                </p>
                                <div class="chart">
                                    <!-- Sales Chart Canvas -->
                                    <!-- <canvas id="depthStatVisitWeb" style="height: 280px;"></canvas> -->
                                    <svg class="chart" data-area-web style="height:280px;"></svg>
                                </div><!-- /.chart-responsive -->
                            </div><!-- /.box-body -->

                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="box">

                            <div class="box-header with-border">
                                <h3 class="box-title">No of visit by Mobile</h3>
                                <div class="box-tools pull-right">
                                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                </div>
                            </div><!-- /.box-header -->

                            <div class="box-body">
                                <p class="text-center">
                                    <strong class="visitPeriod">Period: 1 Jan, 2016 - 24 Apr, 2016</strong>
                                </p>
                                <div class="chart">
                                    <!-- Sales Chart Canvas -->
                                    <!-- <canvas id="depthStatVisitMobile" style="height: 280px;"></canvas> -->
                                    <svg class="chart" data-area-mobile data-data='<?php echo json_encode($report->graphdata); ?>' style="height:280px;"></svg>
                                </div>
                            </div><!-- /.box-body -->

                        </div>

                    </div>

                </div> <!-- end .row -->

            </div> <!-- end .col-md-12 -->

            <div class="col-md-12"> <!-- start Course Metrics -->

                <div class="row">

                    <div class="col-md-8">

                        <div class="box box-success">

                            <div class="box-header with-border">
                                <h3 class="box-title">Course Metrics (This Course / The average )</h3>
                                <div class="box-tools pull-right">
                                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                    <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                                </div>
                            </div><!-- /.box-header -->

                            <div class="box-body">
                                <p class="text-center">
                                    <strong class="daterange">Period: 1 Jan, 2016 - 24 Apr, 2016</strong>
                                </p>
                                <div class="chart">
                                    <!-- Course Metrics (This Course / The average ) -->
                                    <!-- <canvas id="depthStatCourseMatrics" style="height: 600px;"></canvas> -->
                                    <svg class="chart" data-bar-chart data-data='<?php echo json_encode($report->tbldata); ?>' style="height:600px; width:100%;"</svg>
                                </div>
                            </div><!-- /.box-body -->

                        </div> <!-- end .box-success -->

                    </div>

                    <div class="col-md-4">

                        <div class="box depth-stat-goal-com">

                            <div class="box-header with-border">
                                <h3 class="box-title">Goal Completion</h3>
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
                            </div>

                            <div class="box-body">
                                <div class="progress-group">
                                    <span class="progress-text">Assignments</span>
                                    <span class="progress-number"><b><?php echo $x = getActivityCount($report->tbldata, $courseid, 'assign'); ?></b>/<?php echo $y=getActivityCountAverage($report->tbldata, $courseid, 'assign'); ?></span>
                                    <div class="progress sm">
                                        <div class="progress-bar progress-bar-aqua" style="width: <?php echo ($x/$y * 100)?>%"></div>
                                    </div>
                                </div><!-- /.progress-group -->
                                <div class="progress-group">
                                    <span class="progress-text">Blog Activities</span>
                                    <span class="progress-number"><b><?php echo $x = getActivityCount($report->tbldata, $courseid, 'blogactivity'); ?></b>/<?php echo $y=getActivityCountAverage($report->tbldata, $courseid, 'blogactivity'); ?></span>
                                    <div class="progress sm">
                                        <div class="progress-bar progress-bar-red" style="width: <?php echo ($x/$y * 100)?>%"></div>
                                    </div>
                                </div><!-- /.progress-group -->
                                <div class="progress-group">
                                    <span class="progress-text">Course Files</span>
                                    <span class="progress-number"><b><?php echo $x = getActivityCount($report->tbldata, $courseid, 'files'); ?></b>/<?php echo $y=getActivityCountAverage($report->tbldata, $courseid, 'files'); ?></span>
                                    <div class="progress sm">
                                        <div class="progress-bar progress-bar-success" style="width:<?php echo ($x/$y * 100)?>%"></div>
                                    </div>
                                </div><!-- /.progress-group -->
                                <div class="progress-group">
                                    <span class="progress-text">EPUB Packages</span>
                                    <span class="progress-number"><b><?php echo $x = getActivityCount($report->tbldata, $courseid, 'vmepub'); ?></b>/<?php echo $y=getActivityCountAverage($report->tbldata, $courseid, 'vmepub'); ?></span>
                                    <div class="progress sm">
                                        <div class="progress-bar progress-bar-yellow" style="width: <?php echo ($x/$y * 100)?>%"></div>
                                    </div>
                                </div><!-- /.progress-group -->
                                <div class="progress-group">
                                    <span class="progress-text">Forum Posts</span>
                                    <span class="progress-number"><b><?php echo $x = getActivityCount($report->tbldata, $courseid, 'forumposts'); ?></b>/<?php echo $y=getActivityCountAverage($report->tbldata, $courseid, 'forumposts'); ?></span>
                                    <div class="progress sm">
                                        <div class="progress-bar progress-bar-aqua" style="width: <?php echo ($x/$y * 100)?>%"></div>
                                    </div>
                                </div><!-- /.progress-group -->
                                <div class="progress-group">
                                    <span class="progress-text">Peer Appraisals</span>
                                    <span class="progress-number"><b><?php echo $x = getActivityCount($report->tbldata, $courseid, 'peerappraisal'); ?></b>/<?php echo $y=getActivityCountAverage($report->tbldata, $courseid, 'peerappraisal'); ?></span>
                                    <div class="progress sm">
                                        <div class="progress-bar progress-bar-red" style="width: <?php echo ($x/$y * 100)?>%"></div>
                                    </div>
                                </div><!-- /.progress-group -->
                                <div class="progress-group">
                                    <span class="progress-text">Quizzes</span>
                                    <span class="progress-number"><b><?php echo $x = getActivityCount($report->tbldata, $courseid, 'quiz'); ?></b>/<?php echo $y=getActivityCountAverage($report->tbldata, $courseid, 'quiz'); ?></span>
                                    <div class="progress sm">
                                        <div class="progress-bar progress-bar-success" style="width: <?php echo ($x/$y * 100)?>%"></div>
                                    </div>
                                </div><!-- /.progress-group -->
                                <div class="progress-group">
                                    <span class="progress-text">Resources</span>
                                    <span class="progress-number"><b><?php echo $x = getActivityCount($report->tbldata, $courseid, 'resource'); ?></b>/<?php echo $y=getActivityCountAverage($report->tbldata, $courseid, 'resource'); ?></span>
                                    <div class="progress sm">
                                        <div class="progress-bar progress-bar-yellow" style="width: <?php echo ($x/$y * 100)?>%"></div>
                                    </div>
                                </div><!-- /.progress-group -->
                                <div class="progress-group">
                                    <span class="progress-text">Scorm Packages</span>
                                    <span class="progress-number"><b><?php echo $x = getActivityCount($report->tbldata, $courseid, 'scorm'); ?></b>/<?php echo $y=getActivityCountAverage($report->tbldata, $courseid, 'scorm'); ?></span>
                                    <div class="progress sm">
                                        <div class="progress-bar progress-bar-aqua" style="width: <?php echo ($x/$y * 100)?>%"></div>
                                    </div>
                                </div><!-- /.progress-group -->
                                <div class="progress-group">
                                    <span class="progress-text">Surveys</span>
                                    <span class="progress-number"><b><?php echo $x = getActivityCount($report->tbldata, $courseid, 'questionnaire'); ?></b>/<?php echo $y=getActivityCountAverage($report->tbldata, $courseid, 'questionnaire'); ?></span>
                                    <div class="progress sm">
                                        <div class="progress-bar progress-bar-red" style="width: <?php echo ($x/$y * 100)?>%"></div>
                                    </div>
                                </div><!-- /.progress-group -->
                                <div class="progress-group">
                                    <span class="progress-text">Web Links</span>
                                    <span class="progress-number"><b><?php echo $x = getActivityCount($report->tbldata, $courseid, 'url'); ?></b>/<?php echo $y=getActivityCountAverage($report->tbldata, $courseid, 'url'); ?></span>
                                    <div class="progress sm">
                                        <div class="progress-bar progress-bar-success" style="width: <?php echo ($x/$y * 100)?>%"></div>
                                    </div>
                                </div><!-- /.progress-group -->
                                <div class="progress-group">
                                    <span class="progress-text">Web Pages</span>
                                    <span class="progress-number"><b><?php echo $x = getActivityCount($report->tbldata, $courseid, 'page'); ?></b>/<?php echo $y= getActivityCountAverage($report->tbldata, $courseid, 'page'); ?></span>
                                    <div class="progress sm">
                                        <div class="progress-bar progress-bar-yellow" style="width: <?php echo ($x/$y * 100)?>%"></div>
                                    </div>
                                </div><!-- /.progress-group -->
                            </div>
                        </div><!-- /.box-->
                    </div>
                </div> <!-- end .row -->
            </div> <!-- end .col-md-12 -->
        </div><!-- /.row -->
    </section><!-- /.content -->
    <script>
        var frompage = 'detailstats';
        var graphdata = <?php echo json_encode($report->graphdata); ?>
    </script>
    <script type="text/javascript" src="js/d3functions.js"></script>
<?php
echo $OUTPUT->footer();
?>
