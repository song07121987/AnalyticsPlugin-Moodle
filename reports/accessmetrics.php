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
    $isInstructor = checkIfInstructorOfCourse($courseid) || checkIfUnitAdminOfCourse($courseid);    
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
    $PAGE->set_title($COURSE->fullname . ' / Access Metrics');
} else {
    $PAGE->set_title('Site Access Metrics');
}
$PAGE->set_url('/local/analytics/reports/accessmetrics.php', $urlparams); // Defined here to avoid notices on errors etc
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
$report = getDownloadReport($startdate, $enddate, $courseid);

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
            Access Metrics
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
                window.location.href = "accessmetrics.php"+param;
            });
        </script>
    </section>
    <section class="content">
        <!-- Info boxes -->
        <div class="row">
            <div class="col-md-6">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Device Type</h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="chart-responsive">
                                    <canvas id="accessMetricsDeviceTypeChart" height="150"></canvas>
                                </div><!-- ./chart-responsive -->
                            </div><!-- /.col -->
                            <div class="col-md-4">
                                <ul class="chart-legend clearfix">
                                    <li><i class="fa fa-circle-o text-red"></i> Desktop</li>
                                    <li><i class="fa fa-circle-o text-green"></i> Notebook</li>
                                    <li><i class="fa fa-circle-o text-yellow"></i> Feature Phone</li>
                                    <li><i class="fa fa-circle-o text-aqua"></i> Smart Phone</li>
                                    <li><i class="fa fa-circle-o text-light-blue"></i> Tablet</li>
                                </ul>
                            </div><!-- /.col-md-4 -->
                        </div><!-- /.row -->
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div> <!-- end device type .col-md-6 -->
            <div class="col-md-6">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Browser Usage</h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="chart-responsive">
                                    <canvas id="accessMetricsBrowserChart" height="150"></canvas>
                                </div><!-- ./chart-responsive -->
                            </div><!-- /.col -->
                            <div class="col-md-4">
                                <ul class="chart-legend clearfix">
                                    <li><i class="fa fa-circle-o text-red"></i> Chrome</li>
                                    <li><i class="fa fa-circle-o text-green"></i> IE</li>
                                    <li><i class="fa fa-circle-o text-yellow"></i> FireFox</li>
                                    <li><i class="fa fa-circle-o text-aqua"></i> Safari</li>
                                    <li><i class="fa fa-circle-o text-light-blue"></i> Opera</li>
                                    <li><i class="fa fa-circle-o text-gray"></i> Navigator</li>
                                </ul>
                            </div><!-- /.col-md-4 -->
                        </div><!-- /.row -->
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div> <!-- end browser usage .col-md-6 -->
        </div>
        <br>
        <div class="row">
            <div class="col-md-6">

                <div class="box box-default">

                    <div class="box-header with-border">

                        <h3 class="box-title">Device Model</h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div><!-- /.box-header -->

                    <div class="box-body">

                        <div class="row">

                            <div class="col-md-8">
                                <div class="chart-responsive">
                                    <canvas id="accessMetricsDeviceModelChart" height="240"></canvas>
                                </div><!-- ./chart-responsive -->
                            </div><!-- /.col -->

                            <div class="col-md-4">
                                <ul class="chart-legend clearfix">
                                    <li><i class="fa fa-circle-o text-red"></i> Apple</li>
                                    <li><i class="fa fa-circle-o text-green"></i> Dell</li>
                                    <li><i class="fa fa-circle-o text-yellow"></i> Lenevo </li>
                                    <li><i class="fa fa-circle-o text-aqua"></i> Samsung </li>
                                    <li><i class="fa fa-circle-o text-light-blue"></i> Acer </li>
                                    <li><i class="fa fa-circle-o text-dark-orchid"></i> Toshiba </li>
                                    <li><i class="fa fa-circle-o text-dark-cyan"></i> Asus </li>
                                    <li><i class="fa fa-circle-o text-aquamarine"></i> Oppo </li>
                                    <li><i class="fa fa-circle-o text-golden-rod"></i> Nokia </li>
                                    <li><i class="fa fa-circle-o text-cadet-blue"></i> Blackberry </li>
                                    <li><i class="fa fa-circle-o text-chocolate"></i> Vodaphone </li>
                                    <li><i class="fa fa-circle-o text-coral"></i> Gigabyte </li>
                                </ul>
                            </div><!-- /.col-md-4 -->
                        </div><!-- /.row -->
                    </div><!-- /.box-body -->


                </div><!-- /.box -->

            </div> <!-- end device model .col-md-6 -->

            <div class="col-md-6">

                <div class="box box-default">

                    <div class="box-header with-border">

                        <h3 class="box-title">Operating System</h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div><!-- /.box-header -->

                    <div class="box-body">

                        <div class="row">

                            <div class="col-md-8">
                                <div class="chart-responsive">
                                    <canvas id="accessMetricsOperatingSystemChart" height="210"></canvas>
                                </div><!-- ./chart-responsive -->
                            </div><!-- /.col -->

                            <div class="col-md-4">
                                <ul class="chart-legend clearfix">
                                    <li><i class="fa fa-circle-o text-red"></i> Windows 8</li>
                                    <li><i class="fa fa-circle-o text-green"></i> Ubuntu</li>
                                    <li><i class="fa fa-circle-o text-yellow"></i> Windows 7 </li>
                                    <li><i class="fa fa-circle-o text-aqua"></i> Linux Mint </li>
                                    <li><i class="fa fa-circle-o text-light-blue"></i> Windows 8.1 </li>
                                    <li><i class="fa fa-circle-o text-dark-orchid"></i> Macintosh OSX </li>
                                    <li><i class="fa fa-circle-o text-dark-cyan"></i> Android </li>
                                    <li><i class="fa fa-circle-o text-aquamarine"></i> Windows XP </li>
                                    <li><i class="fa fa-circle-o text-golden-rod"></i> Fedora </li>
                                    <li><i class="fa fa-circle-o text-cadet-blue"></i> Chrome OS </li>
                                </ul>
                            </div><!-- /.col-md-4 -->
                        </div><!-- /.row -->
                    </div><!-- /.box-body -->

                </div><!-- /.box -->
            </div> <!-- end operating system .col-md-6 -->
        </div><!-- /.row -->
        <div class="row">

            <div class="col-md-6">

                <div class="box box-default">

                    <div class="box-header with-border">

                        <h3 class="box-title">Device Resolutions</h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div><!-- /.box-header -->

                    <div class="box-body">

                        <div class="row">

                            <div class="col-md-8">
                                <div class="chart-responsive">
                                    <canvas id="accessMetricsDeviceResolutionChart" height="310"></canvas>
                                </div><!-- ./chart-responsive -->
                            </div><!-- /.col -->

                            <div class="col-md-4">
                                <ul class="chart-legend clearfix">
                                    <li><i class="fa fa-circle-o text-red"></i> 1366x768</li>
                                    <li><i class="fa fa-circle-o text-green"></i> 1920x1080</li>
                                    <li><i class="fa fa-circle-o text-yellow"></i> 1280x800 </li>
                                    <li><i class="fa fa-circle-o text-aqua"></i> 320x568 </li>
                                    <li><i class="fa fa-circle-o text-light-blue"></i> 1440x900 </li>
                                    <li><i class="fa fa-circle-o text-dark-orchid"></i> 1280x1024 </li>
                                    <li><i class="fa fa-circle-o text-dark-cyan"></i> 320x480 </li>
                                    <li><i class="fa fa-circle-o text-aquamarine"></i> 1600x900 </li>
                                    <li><i class="fa fa-circle-o text-golden-rod"></i> 768x1024 </li>
                                    <li><i class="fa fa-circle-o text-cadet-blue"></i> 1024x768 </li>
                                    <li><i class="fa fa-circle-o text-chocolate"></i> 1680x1050 </li>
                                    <li><i class="fa fa-circle-o text-coral"></i> 360x640 </li>
                                    <li><i class="fa fa-circle-o text-sea-green"></i> 1920x1200 </li>
                                    <li><i class="fa fa-circle-o text-maroon"></i> 720x1280 </li>
                                    <li><i class="fa fa-circle-o text-orange-red"></i> 480x800 </li>
                                </ul>
                            </div><!-- /.col-md-4 -->
                        </div><!-- /.row -->
                    </div><!-- /.box-body -->

                </div><!-- /.box -->

            </div> <!-- end device resolution .col-md-6 -->

            <div class="col-md-6">

                <div class="box box-default">

                    <div class="box-header with-border">

                        <h3 class="box-title">Configurations</h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div><!-- /.box-header -->

                    <div class="box-body">

                        <div class="row">

                            <div class="col-md-7">
                                <div class="chart-responsive">
                                    <canvas id="accessMetricsConfigurationsChart" height="310"></canvas>
                                </div><!-- ./chart-responsive -->
                            </div><!-- /.col -->

                            <div class="col-md-5">
                                <ul class="chart-legend clearfix">
                                    <li><i class="fa fa-circle-o text-red"></i> Windows/chrome/1366x768</li>
                                    <li><i class="fa fa-circle-o text-green"></i> Windows/Firefox/1920x1080</li>
                                    <li><i class="fa fa-circle-o text-yellow"></i> Linux/Chrome/1280x800 </li>
                                    <li><i class="fa fa-circle-o text-aqua"></i> Windows/Safari/320x568 </li>
                                    <li><i class="fa fa-circle-o text-light-blue"></i> Windows/firefox/1440x900 </li>
                                    <li><i class="fa fa-circle-o text-dark-orchid"></i> Ubuntu/Chrome/1280x1024 </li>
                                    <li><i class="fa fa-circle-o text-dark-cyan"></i> Windows/Safari/320x480 </li>
                                    <li><i class="fa fa-circle-o text-aquamarine"></i> MAC/Safari/1600x900 </li>
                                    <li><i class="fa fa-circle-o text-golden-rod"></i> Windows/Opera/768x1024 </li>
                                    <li><i class="fa fa-circle-o text-cadet-blue"></i> MAC/Safari/1024x768 </li>
                                    <li><i class="fa fa-circle-o text-chocolate"></i> Windows/Chrome/1680x1050 </li>
                                    <li><i class="fa fa-circle-o text-coral"></i> Ubuntu/Opera/360x640 </li>
                                    <li><i class="fa fa-circle-o text-sea-green"></i> Windows/Firefox/1920x1200 </li>
                                    <li><i class="fa fa-circle-o text-maroon"></i> Windows/Chrome/720x1280 </li>
                                    <li><i class="fa fa-circle-o text-orange-red"></i> MAC/safari/480x800 </li>
                                </ul>
                            </div><!-- /.col-md-4 -->
                        </div><!-- /.row -->
                    </div><!-- /.box-body -->

                </div><!-- /.box -->

            </div> <!-- end device configuration .col-md-6 -->

        </div><!-- /.row -->
    </section>
    <script src="js/pages/accessmetrics.js"></script>
    <script>
        $(function () {
            $("#example1").DataTable( { "bSort" : false, "paging": false, "info": false, "searching": false });
            $("#example2").DataTable( { "bSort" : false, "paging": false, "info": false, "searching": false  });
        });
    </script>
<?php
echo $OUTPUT->footer();
?>
