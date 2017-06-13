<?php
require_once('../../../config.php');
require_once($CFG->dirroot . '/lib/accesslib.php');
require_once($CFG->dirroot."/local/analytics/reports/administrationlib.php");

$enabletracking = get_config('local_analytics', 'enabletracking');

if(! $enabletracking){
    redirect($CFG->wwwroot . '/administration/index.php');
    return;
}

global $USER;
$courseid = optional_param('course', 1, PARAM_INT);
$urlparams = array('id' => $courseid);

if ( !hasAdminRole($USER->id)) {
    redirect($CFG->wwwroot . '/administration/index.php');
    return;
}

// To verify if user is an instructor in the course
$course = $DB->get_record('course', $urlparams, '*', MUST_EXIST);
require_login($course);

$PAGE->set_title('Site Analytics / Courses');
$PAGE->set_url('/local/analytics/reports/courses.php', $urlparams); // Defined here to avoid notices on errors etc
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
$report = getCourseBreakdownReport($startdate, $enddate);

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
        width:50%;
        padding:10px;
        float:left;
    }
</style>
    <script type="text/javascript" src="js/moment.min.js"></script>
    <script type="text/javascript" src="js/daterangepicker.js"></script>
    <script type="text/javascript" src="<?php echo $CFG->wwwroot ?>/theme/<?php echo $CFG->theme ?>/javascript/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="<?php echo $CFG->wwwroot ?>/theme/<?php echo $CFG->theme ?>/javascript/js/dataTables.bootstrap.min.css"></script>
    <script type="text/javascript" src="<?php echo $CFG->wwwroot ?>/theme/<?php echo $CFG->theme ?>/javascript/js/dataTables.responsive.min.js"></script>
    <link rel="stylesheet" type="text/css" href="css/daterangepicker.css" />
    <link rel="stylesheet" href="<?php echo $CFG->wwwroot ?>/theme/<?php echo $CFG->theme ?>/style/jquery.dataTables.min.css">
    <link rel="stylesheet" href="<?php echo $CFG->wwwroot ?>/theme/<?php echo $CFG->theme ?>/style/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $CFG->wwwroot ?>/theme/<?php echo $CFG->theme ?>/style/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/admin-mobile.css">
    <section class="content-header">
        <h1>
            Courses Overview
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
                window.location.href = "courses.php"+param;
            });
        </script>
    </section>
    <section class="content">
        <!-- Info boxes -->
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="ion ion-ios-people-outline"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Users / Visits</span>
                        <span class="info-box-number"><?php echo $report->stats->uniquevisitor; ?> / <?php echo $report->stats->totalvisit; ?> </span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->
            </div><!-- /.col -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="ion ion-android-people"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">NO of repeat users</span>
                        <span class="info-box-number"><?php echo $report->stats->totalrepeatvisit; ?></span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->
            </div><!-- /.col -->

            <!-- fix for small devices only -->
            <div class="clearfix visible-sm-block"></div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="ion ion-clipboard"></i></span>                    
                    <div class="info-box-content">
                        <span class="info-box-text">No of page visits</span>
                        <span class="info-box-number"><?php echo $report->stats->totalactions; ?></span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->
            </div><!-- /.col -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-yellow"><i class="ion ion-clock"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Average Time On Page</span>
                        <span class="info-box-number"><?php echo (int)  (safediv($report->stats->totaltime, $report->stats->totalactions)); ?></span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->
            </div><!-- /.col -->
        </div><!-- /.row -->
        <!-- Main content -->
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Course Activities</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div style="margin-top: 40px;"></div>
                        <table id="example1" class="table table-striped dt-responsive nowrap admin-different-data-table2" width="100%">
                            <thead>
                            <tr>
                                <th>Course Name</th>
                                <th>Total Visits</th>
                                <th>Web / Mobile</th>
                                <th>Unique Users</th>
                                <th>Web / Mobile</th>
                                <th>Total Actions</th>
                                <th>Web / Mobile</th>
                                <th>Average Actions</th>
                                <th>Web / Mobile</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($report->courses as $cse) { ?>
                                <tr>
                                    <td><a href='dashboard2.php?course=<?php echo $cse->courseid; ?>'><?php echo $cse->fullname; ?></a></td>
                                    <td><?php echo $cse->totalvisits; ?></td>
                                    <td><?php echo $cse->totalwebvisit."/".$cse->totalmobilevisit; ?></td>
                                    <td><?php echo $cse->totaluniqueuser; ?></td>
                                    <td><?php echo $cse->uniquewebuser."/".$cse->uniquemobileuser; ?></td>
                                    <td><?php echo $cse->totalaction; ?></td>
                                    <td><?php echo $cse->totalwebactions."/".$cse->totalmobileactions; ?></td>
                                    <td><?php echo $cse->avgactions; ?></td>
                                    <td><?php echo $cse->avgwebactions."/".$cse->avgmobileactions; ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                            <tfoot>
                            <tr>
                                <th>Course Name</th>
                                <th>Total Visits</th>
                                <th>Web / Mobile</th>
                                <th>Unique Users</th>
                                <th>Web / Mobile</th>
                                <th>Total Actions</th>
                                <th>Web / Mobile</th>
                                <th>Average Actions</th>
                                <th>Web / Mobile</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div><!-- /.box-body -->
                </div>
            </div>
        </div><!-- /.row -->
    </section>
    <script>
        $(function () {
            $("#example1").DataTable( { "order" : [[1, 'desc']] });
        });
    </script>

<?php
echo $OUTPUT->footer();
?>
