<?php
require_once('../../../config.php');
require_once($CFG->dirroot . '/lib/accesslib.php');
require_once($CFG->dirroot."/local/analytics/reports/administrationlib.php");

$enabletracking = get_config('local_analytics', 'enabletracking');
$showenhancedanalytics = get_config('local_analytics', 'showenhancedanalytics');

if(! $enabletracking){
    redirect($CFG->wwwroot . '/administration/index.php');
    return;
}

global $USER;
$courseid = optional_param('course', 1, PARAM_INT);
$urlparams = array('id' => $courseid );

$isInstructor = true;
if ($courseid > 1) {
    require_once($CFG->dirroot."/local/analytics/reports/mq_functions.php");
    $isInstructor = checkIfInstructorOfCourse($courseid) || checkIfUnitAdminOfCourse($courseid);
} 
if ( !hasMasterAdminRole($USER->id) && !$isInstructor) {
    redirect($CFG->wwwroot . '/administration/index.php');
    return;
}

// To verify if user is an instructor in the course
$course = $DB->get_record('course', $urlparams, '*', MUST_EXIST);
require_login($course);

if ($courseid > 1) {
    $PAGE->set_title($COURSE->fullname . ' / Top Contributors');
} else {
    $PAGE->set_title('Site Top Contributors');
}
$PAGE->set_url('/local/analytics/reports/topcontributor.php', $urlparams); // Defined here to avoid notices on errors etc
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
$report = getTopContributors($startdate, $enddate, $courseid);

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
        width: 49%;
        padding: 10px;
        display: inline-block;
    }
    .nav-tabs-custom>.nav-tabs {
        margin-bottom: 0 !important;
        border-bottom: 1px solid #D4D4D4;
     }
    .nav-tabs-custom>.nav-tabs>li {
        margin-bottom: -1px;
        margin-right: 2px;
        border-top: 0;
    }
    .nav-tabs-custom>.nav-tabs>li.active {
        border-top: 0;
        border-bottom: 1px solid #ffffff;
    }
    .nav-tabs-custom>.nav-tabs>li a {
        background-color: #efe8eb;
        border-radius: 4px 4px 0 0;
        border-top: 1px solid #d4d4d4 !important;
        border-left: 1px solid #d4d4d4 !important;
        border-right: 1px solid #d4d4d4 !important;
        border-bottom: 0 !important;
        color: #999999;
        margin: 0 auto;
    }
    .nav-tabs-custom>.nav-tabs>li a:hover {
        background-color: #585858;
        color: #ffffff;
    }
    .nav-tabs-custom>.nav-tabs>li.active a {
        color: #000000;
        font-weight: 600;
        background-color: #ffffff;
        border-bottom: 1px solid transparent;
    }
    .nav-tabs-custom>.tab-content {
        border-left: 1px solid #d4d4d4;
        border-right: 1px solid #d4d4d4;
        border-bottom: 1px solid #d4d4d4;
    }
</style>
    <script type="text/javascript" src="js/moment.min.js"></script>
    <script type="text/javascript" src="js/daterangepicker.js"></script>
    <link rel="stylesheet" type="text/css" href="css/daterangepicker.css" />
    <link rel="stylesheet" type="text/css" href="css/admin.css" />
    <link rel="stylesheet" type="text/css" href="css/admin-mobile.css">
    <link rel="stylesheet" href="<?php echo $CFG->wwwroot ?>/theme/<?php echo $CFG->theme ?>/style/jquery.dataTables.min.css">
    <link rel="stylesheet" href="<?php echo $CFG->wwwroot ?>/theme/<?php echo $CFG->theme ?>/style/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $CFG->wwwroot ?>/theme/<?php echo $CFG->theme ?>/style/responsive.bootstrap.min.css">
    <script type="text/javascript" src="<?php echo $CFG->wwwroot ?>/theme/<?php echo $CFG->theme ?>/javascript/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="<?php echo $CFG->wwwroot ?>/theme/<?php echo $CFG->theme ?>/javascript/js/dataTables.bootstrap.min.css"></script>
    <script type="text/javascript" src="<?php echo $CFG->wwwroot ?>/theme/<?php echo $CFG->theme ?>/javascript/js/dataTables.responsive.min.js"></script>
    <section class="content-header">
        <h1>
            Top Contributors
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
                    param = "?course="+<?php echo $courseid?>+"&start="+start+"&end="+end;
                }
                window.location.href = "topcontributor.php"+param;
            });
        </script>
    </section>
    <section class="content">
        <!-- Info boxes -->
        <div class="row">
            <div class="col-md-12">
                <!-- Custom Tabs -->
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#tab_1" data-toggle="tab">Forum Posts</a></li>
                        <?php if($showenhancedanalytics) { ?>
                        <li><a href="#tab_2" data-toggle="tab">Blogs</a></li>
                        <li><a href="#tab_3" data-toggle="tab">Files</a></li>
                        <?php } ?>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab_1">
                            <h3>Top 50 forum post contributors</h3>
                            <table id="example1" class="table table-striped dt-responsive nowrap admin-different-data-table2" width="100%">
                                <thead>
                                <tr>
                                    <th>User Name</th>
                                    <th>Total Posts</th>
                                    <th>Total Posts in Period</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($report->forums as $user) { ?>
                                    <tr>
                                        <td><a href='../../user/view.php?id=<?php echo $user->userid; ?>'><?php echo $user->firstname; ?></a></td>
                                        <td><?php echo $user->cnt; ?></td>
                                        <td><?php echo $user->periodcnt; ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th>User Name</th>
                                    <th>Total Posts</th>
                                    <th>Total Posts in Period</th>
                                </tr>
                                </tfoot>
                            </table>
                        </div><!-- /.tab-pane -->
                        <?php if($showenhancedanalytics) { ?>
                        <div class="tab-pane" id="tab_2">
                            <h3>Top 50 bloggers</h3>
                            <table id="example2" class="table table-striped dt-responsive nowrap admin-different-data-table2" width="100%">
                                <thead>
                                <tr>
                                    <th>User Name</th>
                                    <th>Total Blogs</th>
                                    <th>Total Blogs in Period</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($report->blogs as $user) { ?>
                                    <tr>
                                        <td><a href='../../user/view.php?id=<?php echo $user->userid; ?>'><?php echo $user->firstname; ?></a></td>
                                        <td><?php echo $user->cnt; ?></td>
                                        <td><?php echo $user->periodcnt; ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th>User Name</th>
                                    <th>Total Blogs</th>
                                    <th>Total Blogs in Period</th>
                                </tr>
                                </tfoot>
                            </table>
                        </div><!-- /.tab-pane -->
                        <div class="tab-pane" id="tab_3">
                            <h3>Top 50 file uploaders</h3>
                            <table id="example3" class="table table-striped dt-responsive nowrap admin-different-data-table2" width="100%">
                                <thead>
                                <tr>
                                    <th>User Name</th>
                                    <th>Total Files</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($report->files as $user) { ?>
                                    <tr>
                                        <td><a href='../../user/view.php?id=<?php echo $user->id; ?>'><?php echo $user->firstname; ?></a></td>
                                        <td><?php echo $user->cnt; ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th>User Name</th>
                                    <th>Total Files</th>
                                </tr>
                                </tfoot>
                            </table>
                        </div><!-- /.tab-pane -->
                        <?php } ?>
                    </div><!-- /.tab-content -->
                </div><!-- nav-tabs-custom -->
            </div><!-- /.col -->
        </div><!-- /.row -->
    </section>
    <script>
        $(function () {
            $("#example1").DataTable();
            <?php if($showenhancedanalytics) { ?>
            $("#example2").DataTable();
            $("#example3").DataTable();
            <?php } ?>
        });
    </script>

<?php
echo $OUTPUT->footer();
?>
