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
    $PAGE->set_title($COURSE->fullname . ' / Visitor Map');
} else {
    $PAGE->set_title('Site Visitor Map');
}
$PAGE->set_url('/local/analytics/reports/visitormap.php', $urlparams); // Defined here to avoid notices on errors etc
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
$report = getVisitorMap($startdate, $enddate, $courseid);

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
</style>
    <script type="text/javascript" src="js/moment.min.js"></script>
    <script type="text/javascript" src="js/daterangepicker.js"></script>
    <link rel="stylesheet" type="text/css" href="css/daterangepicker.css" />
    <link rel="stylesheet" href="css/jquery-jvectormap-1.2.2.css">
    <link rel="stylesheet" type="text/css" href="css/admin.css" />
    <link rel="stylesheet" type="text/css" href="css/admin-mobile.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $CFG->wwwroot ?>/theme/<?php echo $CFG->theme ?>/style/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $CFG->wwwroot ?>/theme/<?php echo $CFG->theme ?>/style/dataTables.bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $CFG->wwwroot ?>/theme/<?php echo $CFG->theme ?>/style/responsive.bootstrap.min.css">
    <script type="text/javascript" src="<?php echo $CFG->wwwroot ?>/theme/<?php echo $CFG->theme ?>/javascript/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="<?php echo $CFG->wwwroot ?>/theme/<?php echo $CFG->theme ?>/javascript/js/dataTables.bootstrap.min.css"></script>
    <script type="text/javascript" src="<?php echo $CFG->wwwroot ?>/theme/<?php echo $CFG->theme ?>/javascript/js/dataTables.responsive.min.js"></script>

    <section class="content-header">
        <h1>
            Visitors Map
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
                window.location.href = "visitormap.php"+param;
            });
        </script>
    </section>
    <section class="content">
        <!-- Info boxes -->
        <div class="row">
            <div class="col-md-12">
                <div class="box visitor-report-area">

                    <div class="box-header with-border">
                        <h3 class="box-title">Visit On Map</h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div><!-- /.box-header -->

                    <div class="box-body no-padding">

                        <div class="row">
                            <div class="col-md-9 col-sm-8">
                                <div class="pad">
                                    <!-- Map will be created here -->
                                    <div id="visitorMapMarkers" style="height: 425px;"></div>
                                </div>
                            </div><!-- /.col -->

                            <div class="col-md-3 col-sm-4">
                                <div class="pad box-pane-right bg-green" style="min-height: 450px">
                                    <div class="description-block margin-bottom">
                                        <div class="visitor-map-sparkbar pad" data-color="#fff">90,70,90,70,75,80,70</div>
                                        <h5 class="description-header"><?php echo $report->totalvisits; ?></h5>
                                        <span class="description-text">Visits</span>
                                    </div><!-- /.description-block -->

                                    <div class="description-block margin-bottom">
                                        <div class="visitor-map-sparkbar pad" data-color="#fff">90,50,90,70,61,83,63</div>
                                        <h5 class="description-header"><?php echo $report->totalusers; ?></h5>
                                        <span class="description-text">Users</span>
                                    </div><!-- /.description-block -->

                                    <div class="description-block">
                                        <div class="visitor-map-sparkbar pad" data-color="#fff">90,50,90,70,61,83,63</div>
                                        <h5 class="description-header"><?php echo $report->totalactions; ?></h5>
                                        <span class="description-text">Actions</span>
                                    </div><!-- /.description-block -->
                                </div> <!-- end .pad -->
                            </div><!-- /.col -->
                        </div><!-- /.row -->
                    </div><!-- /.box-body -->
                    <br><br>
                </div><!-- /.box -->
                <div class="box">

                    <div class="box-header">
                        <h3 class="box-title">Visit By Countries</h3>
                    </div><!-- /.box-header -->

                    <div class="box-body">
                        <br><br>
                        <table id="example1" class="table table-striped dt-responsive nowrap admin-different-data-table2" width="100%">
                            <thead>
                            <tr>
                                <th>Country</th>
                                <th>Number of Visits</th>
                                <th>Number of Unique Users</th>
                                <th>Number of Actions</th>
                                <th>TotalTime Spent</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($report->countries as $country) {
                                if ($country->visitsperiod > 0) {
                                ?>
                                <tr>
                                    <td><?php echo getCountry ($country->location_country); ?></td>
                                    <td><?php echo $country->visitsperiod; ?></td>
                                    <td><?php echo $country->usersperiod; ?></td>
                                    <td><?php echo $country->actionsperiod; ?></td>
                                    <td><?php echo secondsToTime($country->timeperiod); ?></td>
                                </tr>
                            <?php }
                            } ?>
                        </table>
                    </div><!-- /.box-body -->
                </div> <!-- end .box -->
            </div><!-- /.col -->
        </div><!-- /.row -->
    </section>
    <script>
        $(function () {
            $("#example1").DataTable( { "order" : [[1, 'desc']] });
        });
    </script>
    <!-- Sparkline -->
    <script src="js/jquery.sparkline.min.js"></script>
    <!-- jvectormap -->
    <script src="js/jquery-jvectormap-1.2.2.min.js"></script>
    <script src="js/jquery-jvectormap-world-mill-en.js"></script>
<script>
    $('#visitorMapMarkers').vectorMap({
        map: 'world_mill_en',
        normalizeFunction: 'polynomial',
        hoverOpacity: 0.7,
        hoverColor: false,
        backgroundColor: 'transparent',
        regionStyle: {
            initial: {
                fill: 'rgba(210, 214, 222, 1)',
                "fill-opacity": 1,
                stroke: 'none',
                "stroke-width": 0,
                "stroke-opacity": 1
            },
            hover: {
                "fill-opacity": 0.7,
                cursor: 'pointer'
            },
            selected: {
                fill: 'yellow'
            },
            selectedHover: {
            }
        },
        markerStyle: {
            initial: {
                fill: '#00a65a',
                stroke: '#111'
            }
        },
        markers: [<?php echo $report->countrydata; ?>
        ]
    });

    $('.visitor-map-sparkbar').each(function () {
        var $this = $(this);
        $this.sparkline('html', {
            type: 'bar',
            height: $this.data('height') ? $this.data('height') : '30',
            barColor: $this.data('color')
        });
    });
</script>
<?php
echo $OUTPUT->footer();
?>
