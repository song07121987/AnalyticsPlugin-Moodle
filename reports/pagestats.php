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
    $PAGE->set_title($COURSE->fullname . ' / Page Statistics');
} else {
    $PAGE->set_title('Site Pages Statistics');
}
$PAGE->set_url('/local/analytics/reports/pagestats.php', $urlparams); // Defined here to avoid notices on errors etc
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
$report = getPageReport($startdate, $enddate, $courseid);

// When we display - we subtract 1 day
$enddate->sub(new DateInterval("P1D"));
$rptdate = $startdate->format("d M Y").' to '.$enddate->format("d M Y");

echo $OUTPUT->header();
?>
    <style>
        @media screen and (min-width: 769px) {
            table#pageAnalyticsDetailsTbl tr th:first-child {
                min-width: 300px;
            }
        }
    </style>
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

    <!-- Content Header (Page header) -->
    <section class="content-header serv-per-header">
        <h1 style="padding: 5px 0px 5px 10px;">
            Page Statistics
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
                window.location.href = "pagestats.php"+param;
            });
        </script>
    </section>
    <br>
    <!-- Main content -->
    <section class="content">
        <!-- Info boxes -->
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Statistics from <?php echo $rptdate; ?></h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <br><br>
                        <table id="example1" class="table table-striped dt-responsive nowrap admin-different-data-table2" width="100%">
                            <thead>
                            <tr>
                                <th>Page Name</th>
                                <th>Page Views</th>
                                <th>Unique Page Views</th>
                                <th>Avg. Time On Page</th>
                                <th>Avg. Generation Time</th>
                                <th>Bounce Rate</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($report->pages as $pg) { ?>
                                <tr>
                                    <td><?php echo $pg->name; ?></td>
                                    <td><?php echo $pg->visitcount; ?></td>
                                    <td><?php echo $pg->uniqueview; ?></td>
                                    <td><?php echo secondsToTime($pg->timespent); ?></td>
                                    <td><?php echo number_format($pg->generationtime, 2); ?> secs</td>
                                    <td><?php echo number_format(safediv ($pg->bouncepages, $pg->visitcount) * 100, 2); ?>%</td>
                                </tr>
                            <?php } ?>
                          </tbody>
                        </table> <!-- end table -->
                    </div> <!-- /.box-body -->
                </div> <!-- end .box -->
            </div> <!-- end .col-xs-12 -->
        </div><!-- /.row -->
    </section><!-- /.content -->
    <script>
        $(function () {
            $("#example1").DataTable( { "order" : [[1, 'desc']] });
        });
    </script>
<?php
echo $OUTPUT->footer();
?>
