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
$siteid = optional_param('siteid', 1, PARAM_INT);
$page = optional_param('page', '', PARAM_ALPHANUM);

$isInstructor = true;
if ($siteid > 1) {
    require_once($CFG->dirroot."/local/analytics/reports/mq_functions.php");
    $isInstructor = checkIfInstructorOfCourse($siteid) || checkIfUnitAdminOfCourse($courseid);
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

$PAGE->set_title($COURSE->fullname.' / Analytic / Server Performance');
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
?>
    <style>
        @media screen and (min-width: 769px) {
           table#pageAnalyticsDetailsTbl tr th:first-child {
                min-width: 300px;
            }  
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
    <script type="text/javascript" src="js/pages/serverperformance.js"></script>
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
            Server Performance
        </h1>
        <div id="server-perform-reportrange" class="pull-right" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; margin-top:-34px;">
            <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp; <span></span> <b class="caret"></b>
        </div>
    </section>
    <!-- Main content -->
    <section class="content">
        <!-- Info boxes -->
        <div class="row">

            <div class="col-md-12">

                <div class="box">

                    <div class="box-header with-border">
                        <h3 class="box-title"> Application Server - CPU Usage </h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div><!-- /.box-header -->

                    <div class="box-body in-depth-stat-pad">
                        <p class="text-center">
                            <strong>Period: 1 Jan, 2016 - 24 June, 2016</strong>
                        </p>
                        <div class="chart">
                            <!-- No of visits over number of unique visit chart -->
                            <canvas id="serverPerformAppCpuUsage" style="height: 280px;"></canvas>
                        </div>
                    </div><!-- /.box-body -->

                </div> <!-- end .box -->

                <div class="box">

                    <div class="box-header with-border">
                        <h3 class="box-title">Application Server - Memory Usage</h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div><!-- /.box-header -->

                    <div class="box-body in-depth-stat-pad">
                        <p class="text-center">
                            <strong>Period: 1 Jan, 2016 - 30 june, 2016</strong>
                        </p>
                        <div class="chart">
                            <!-- No of actions over number of unique actions chart -->
                            <canvas id="serverPerformAppMemoryUsage" style="height: 280px;"></canvas>
                        </div>
                    </div><!-- /.box-body -->

                </div> <!-- end .box -->

                <div class="box">

                    <div class="box-header with-border">
                        <h3 class="box-title">Web Services - CPU / Memory Usage</h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div><!-- /.box-header -->

                    <div class="box-body in-depth-stat-pad">
                        <p class="text-center">
                            <strong>Period: 1 Jan, 2016 - 30 june, 2016</strong>
                        </p>
                        <div class="chart">
                            <!-- No of actions over number of unique actions chart -->
                            <canvas id="serverPerformWebServiceCpuMemoryUse" style="height: 280px;"></canvas>
                        </div>
                    </div><!-- /.box-body -->

                </div> <!-- end .box -->

                <div class="box">

                    <div class="box-header with-border">
                        <h3 class="box-title">Web Services - Utilisation (no of API Calls)</h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div><!-- /.box-header -->

                    <div class="box-body">
                        <p class="text-center">
                            <strong>Period: 1 Jan, 2016 - 30 Jun, 2016</strong>
                        </p>
                        <div class="chart">
                            <!-- Course Metrics (This Course / The average ) -->
                            <canvas id="serverPerformWebServiceUtilisation" style="height: 300px;"></canvas>
                        </div>
                    </div><!-- /.box-body -->

                </div> <!-- end .box -->

                <div class="box">

                    <div class="box-header with-border">
                        <h3 class="box-title">Search Services - CPU / Memory Usagee</h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div><!-- /.box-header -->

                    <div class="box-body in-depth-stat-pad">
                        <p class="text-center">
                            <strong>Period: 1 Jan, 2016 - 30 june, 2016</strong>
                        </p>
                        <div class="chart">
                            <!-- No of actions over number of unique actions chart -->
                            <canvas id="serverPerformSearchServiceCpuMemoryUse" style="height: 280px;"></canvas>
                        </div>
                    </div><!-- /.box-body -->

                </div> <!-- end .box -->

                <div class="box">

                    <div class="box-header with-border">
                        <h3 class="box-title">Search Indexes Time</h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div><!-- /.box-header -->

                    <div class="box-body in-depth-stat-pad">
                        <p class="text-center">
                            <strong>Period: 1 Jan, 2016 - 30 Feb, 2016</strong>
                        </p>
                        <div class="chart">
                            <!-- No of actions over number of unique actions chart -->
                            <canvas id="serverPerformSearchIndexTime" style="height: 280px;"></canvas>
                        </div>
                    </div><!-- /.box-body -->
                </div> <!-- end .box -->
            </div> <!-- end .col-md-12 -->
        </div><!-- /.row -->
    </section><!-- /.content -->
<?php
echo $OUTPUT->footer();
?>
