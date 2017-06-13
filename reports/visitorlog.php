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

// To verify if user is an instructor in the course
$course = $DB->get_record('course', $urlparams, '*', MUST_EXIST);
require_login($course);

if ($courseid > 1) {
    $PAGE->set_title($COURSE->fullname . ' / Visitor Logs');
} else {
    $PAGE->set_title('Site Visitor Logs');
}
$PAGE->set_url('/local/analytics/reports/visitorlog.php', $urlparams); // Defined here to avoid notices on errors etc
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
$userid = optional_param('userid', '', PARAM_INT);
$report = getVisitorLandingContent($startdate, $enddate, $userid, $courseid);

// When we display - we subtract 1 day
$enddate->sub(new DateInterval("P1D"));
$rptdate = $startdate->format("d M Y").' to '.$enddate->format("d M Y");

echo $OUTPUT->header();
?>
    <!-- Please add additional javascripts here -->
    <script type="text/javascript" src="js/moment.min.js"></script>
    <script type="text/javascript" src="js/daterangepicker.js"></script>
    <link rel="stylesheet" type="text/css" href="css/daterangepicker.css" />
    <link rel="stylesheet" type="text/css" href="css/admin.css" />
    <link rel="stylesheet" type="text/css" href="css/admin-mobile.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $CFG->wwwroot ?>/theme/<?php echo $CFG->theme ?>/style/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $CFG->wwwroot ?>/theme/<?php echo $CFG->theme ?>/style/dataTables.bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $CFG->wwwroot ?>/theme/<?php echo $CFG->theme ?>/style/responsive.bootstrap.min.css">
    <script type="text/javascript" src="<?php echo $CFG->wwwroot ?>/theme/<?php echo $CFG->theme ?>/javascript/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="<?php echo $CFG->wwwroot ?>/theme/<?php echo $CFG->theme ?>/javascript/js/dataTables.bootstrap.min.css"></script>
    <script type="text/javascript" src="<?php echo $CFG->wwwroot ?>/theme/<?php echo $CFG->theme ?>/javascript/js/dataTables.responsive.min.js"></script>

    <style>
        .info-box {
            box-shadow: 1px 1px 1px 2px rgba(0,0,0,0.2)
        }
        .visitor-log-details-view table td ul {
            padding-left: 10px;
        }
        .visitor-log-details-view table td li {
            list-style-type: none;
            margin-bottom: 10px;
        }
        .visitor-log-details-view table td img {
            padding-right: 4px;
        }
        .visitor-log-details-view table td span {
            display: block;
        }
    </style>

    <section class="content-header">
        <h1>
            Visitor Logs from <?php echo $rptdate ?>
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
                window.location.href = "visitorlog.php"+param;
            });
        </script>
    </section>
    <!-- Main content -->
    <section class="content">
        <!-- Info boxes -->
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="ion ion-ios-gear-outline"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">No of Visitors</span>
                        <span class="info-box-number"><?php echo $report['visitorNo']['count']?><small></small></span>
                        <span class="info-box-number"><small>(<?php echo $report['visitorNo']['web']?> Web, <?php echo $report['visitorNo']['mobile']?> Mobile)</small></span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->
            </div><!-- /.col -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="fa fa-google-plus"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">No of Unique Visitors</span>
                        <span class="info-box-number"><?php echo $report['visitorUniqueNo']['count']?></span>
                        <span class="info-box-number"><small>(<?php echo $report['visitorUniqueNo']['web']?> Web, <?php echo $report['visitorUniqueNo']['mobile']?> Mobile)</small></span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->
            </div><!-- /.col -->

            <!-- fix for small devices only -->
            <div class="clearfix visible-sm-block"></div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="ion ion-ios-cart-outline"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">No of Actions</span>
                        <span class="info-box-number"><?php echo $report['actionNo']['count']?></span>
                        <span class="info-box-number"><small>(<?php echo $report['actionNo']['web']?> Web, <?php echo $report['actionNo']['mobile']?> Mobile)</small></span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->
            </div><!-- /.col -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-yellow"><i class="ion ion-ios-people-outline"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">No of Unique Actions</span>
                        <span class="info-box-number"><?php echo $report['actionUniqueNo']['count']?></span>
                        <span class="info-box-number"><small>(<?php echo $report['actionUniqueNo']['web']?> Web, <?php echo $report['actionUniqueNo']['mobile']?> Mobile)</small></span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->
            </div><!-- /.col -->
        </div><!-- /.row -->

        <!-- Main content -->
        <div class="row">

            <div class="col-xs-12">

                <div class="box">

                    <div class="box-header">
                        <h3 class="box-title">Visitor Log : List View</h3>
                    </div><!-- /.box-header -->

                    <div class="box-body">
                        <div style="margin-top: 40px;"></div>
                        <table id="visitorLogListViewTable" class="table table-striped dt-responsive nowrap admin-different-data-table2" width="100%">
                            <thead>
                            <tr>
                                <th>Date</th>
                                <th>Name</th>
                                <th>Browser</th>
                                <th>OS </th>
                                <th>Country </th>
                                <th>No. Of Actions</th>
                                <th>Total Time (mins)</th>
                                <th>Access Type <br>(Web / Mobile)</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                            <tfoot>
                            <tr>
                                <th>Date</th>
                                <th>Name</th>
                                <th>Browser</th>
                                <th>OS </th>
                                <th>Country </th>
                                <th>No. Of Actions</th>
                                <th>Total Time (mins)</th>
                                <th>Access Type <br>(Web / Mobile)</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div><!-- /.box-body -->
                </div> <!-- end .box -->
            </div> <!-- end .col-xs-12 -->
            <div class="clearfix"></div>
        </div><!-- /.row -->
    </section><!-- /.content -->
    <script>
        $(function () {
            $("#visitorLogListViewTable").DataTable( { "order" : [[1, 'desc']] });
        });
    </script>
<?php
echo $OUTPUT->footer();
?>
