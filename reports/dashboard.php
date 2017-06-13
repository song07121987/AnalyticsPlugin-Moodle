<?php
require_once('../../../config.php');
require_once($CFG->dirroot.'/lib/accesslib.php');
require_once($CFG->dirroot.'/local/analytics/reports/administrationlib.php');
require_once($CFG->dirroot.'/local/analytics/reports/mq_functions.php');

$enabletracking = get_config('local_analytics', 'enabletracking');
$showenhancedanalytics = get_config('local_analytics', 'showenhancedanalytics');

if(! $enabletracking){
    redirect($CFG->wwwroot . '/administration/index.php');
    return;
}
if($showenhancedanalytics) {
    redirect($CFG->wwwroot . '/local/analytics/reports/dashboard2.php');
    return;
}

global $USER;
$courseid = optional_param('course', 1, PARAM_INT);
$courseidcompare = optional_param('course2', 0, PARAM_INT);
$unit = optional_param('unit', 0, PARAM_INT);
$urlparams = array('id' => $courseid);

$isInstructor = true;
if($courseid > 1) {
    $isInstructor = checkIfInstructorOfCourse($courseid) || checkIfUnitAdminOfCourse($courseid);
} else if ($courseid == 1) {
    if( !hasMasterAdminRole($USER->id) && !hasUnitAdminRole($USER->id)) {
        redirect($CFG->wwwroot . '/administration/index.php');
        return;
    }
}

if( !hasMasterAdminRole($USER->id) && !$isInstructor) {
    redirect($CFG->wwwroot . '/administration/index.php');
    return;
}

$userlevel = 3; //instructor
if(hasUnitAdminRole($USER->id)) {
    $userlevel = 2;
}
if(hasMasterAdminRole($USER->id)) {
    $userlevel = 1;
}

// To verify if user is an instructor in the course
$course = $DB->get_record('course', $urlparams, '*', MUST_EXIST);
require_login($course);

if ($courseid > 1) {
    $PAGE->set_title($COURSE->fullname . ' / System Usage Reporting');
} else {
    $PAGE->set_title('System Usage Reporting');
}
$PAGE->set_url('/local/analytics/reports/dashboard.php', $urlparams); // Defined here to avoid notices on errors etc
$PAGE->set_cacheable(false);
$PAGE->set_pagelayout('administration');
$PAGE->set_pagetype('admin');

require_once('lib/analyticslib.php');
$startdate = analytics_getStartDate();
$enddate = analytics_getEndDate();
$report = getLandingReport($startdate, $enddate , $courseid, $courseidcompare);

if ($userlevel == 2) {
    $report->graphdata = json_encode(array());
    $report->pages = array();
    $report->tbldatadl = array();
}

// When we display - we subtract 3 day , except for Yesterday (not sure why the 3 days not working with yesterday)
$startdatetemp = new DateTime();
$startdatetemp->setTime(0,0);
$startdatetemp->sub(new DateInterval("P1D"));
if ($startdate->getTimestamp() !== $startdatetemp->getTimestamp()) {
    $enddate->sub(new DateInterval("P3D"));
}

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
    .info-box-past {
        box-shadow: 1px 1px 1px 2px rgba(0,0,0,0.2);
        margin-top: -15px;
        margin-bottom: 10px;
        padding-left: 15px;
    }
    .info-box-past .info-box-text,
    .info-box-past .info-box-number {
        display: inline;
    }
    .uniticon {
        background-image: url('<?php echo $CFG->wwwroot; ?>/theme/lntheme/images/action/dashboard-icons/units-programme.png');
        background-size: 60px 60px;
        background-repeat: no-repeat;
        background-position: center;
    }
    .usersicon {
        background-image: url('<?php echo $CFG->wwwroot; ?>/theme/lntheme/images/action/dashboard-icons/users.png');
        background-size: 60px 60px;
        background-repeat: no-repeat;
        background-position: center;
    }
    .metaicon {
        background-image: url('<?php echo $CFG->wwwroot; ?>/theme/lntheme/images/action/dashboard-icons/courses-modules.png');
        background-size: 60px 60px;
        background-repeat: no-repeat;
        background-position: center;
    }
    .courseicon {
        background-image: url('<?php echo $CFG->wwwroot; ?>/theme/lntheme/images/action/dashboard-icons/courseruns-classes.png');
        background-size: 60px 60px;
        background-repeat: no-repeat;
        background-position: center;
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
    .axis path,
    .axis line {
      fill: none;
      stroke: #000;
      shape-rendering: crispEdges;
    }

    .x.axis path {
      display: none;
    }

    .line1 {
      fill: none;
      stroke: steelblue;
      stroke-width: 1.5px;
      color: steelblue;;
    }

    .lineA {
      fill: none;
      stroke: orange;
      stroke-width: 1.5px;
      color: orange;;
    }

    .line2 {
      fill: none;
      stroke: red;
      stroke-width: 1.5px;
      color: red;
    }

    #reportrange {
        float: left;
        margin: 10px 0;
    }

    #coursedrop1 {
        padding: 5px 10px;
        margin: 10px 0;
    }
</style>
    <script type="text/javascript" src="js/moment.min.js"></script>
    <script type="text/javascript" src="js/daterangepicker.js"></script>
    <link rel="stylesheet" type="text/css" href="css/daterangepicker.css" />
    <link rel="stylesheet" href="css/admin.css">
    <script src="js/Chart.min.js"></script>
    <script src="js/jquery.sparkline.min.js"></script>
    <script src="js/d3.js"></script>
    <script src="js/d3-tip.js"></script>
    <section class="content-header">
        <h1>
            <?php if ($userlevel < 3 ) { ?>
            Welcome, <?php echo $USER->firstname; ?>
            <?php } else { ?>
            Course Statistics
            <?php } ?>
        </h1>
    </section>
    <section class="content">
        <!-- Info boxes -->
        <?php if ($userlevel < 3) {?>
        <div class="row">
            <?php if ($userlevel == 2) {?>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-red uniticon"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">UNIT</span>
                        <?php echo printDroplist(getUnits(), 'unitdrop'); ?>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->
            </div><!-- /.col -->
            <?php } ?>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua usersicon"></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Users</span>
                        <span class="info-box-number"><?php echo $report->topdashboard->users; ?> </span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->
                <div class="info-box-past">
                    <span class="info-box-text">past week :</span>
                    <span class="info-box-number">+<?php echo $report->topdashboard->pusers; ?> </span>
                </div>
            </div><!-- /.col -->
            <?php if ($userlevel == 1) {?>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-red uniticon"></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Units</span>
                        <span class="info-box-number"><?php echo $report->topdashboard->units; ?></span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->
                <div class="info-box-past">
                    <span class="info-box-text">past week :</span>
                    <span class="info-box-number">+<?php echo $report->topdashboard->punits; ?> </span>
                </div>
            </div><!-- /.col -->
            <?php } ?>
            <!-- fix for small devices only -->
            <div class="clearfix visible-sm-block"></div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-green metaicon"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Meta Courses</span>
                        <span class="info-box-number" id="metacount"><?php echo $report->topdashboard->courses; ?></span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->
                <div class="info-box-past">
                    <span class="info-box-text">past week :</span>
                    <span class="info-box-number" id="pmetacount">+<?php echo $report->topdashboard->pcourses; ?> </span>
                </div>
            </div><!-- /.col -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-yellow courseicon"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Course Runs</span>
                        <span class="info-box-number" id="coursecount"><?php echo $report->topdashboard->courseruns; ?></span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->
                <div class="info-box-past">
                    <span class="info-box-text">past week :</span>
                    <span class="info-box-number" id="pcoursecount">+<?php echo $report->topdashboard->pcourseruns; ?> </span>
                </div>
            </div><!-- /.col -->
        </div><!-- /.row -->
        <?php } ?>
        <div>
            <div id="reportrange" class="clearfix" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc;">
                <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp; <span></span> <b class="caret"></b>
            </div>
            <?php if ($userlevel == 2) { ?>
            <div><strong>Select Course</strong> <?php echo printDroplist(getCoursesFromUnit(), 'coursedrop1'); ?></div>
            <?php } ?>
        </div>

        <input type="hidden" name="unit" id="unit" value="<?php echo $unit?>">
        <input type="hidden" name="course1" id="course1" value="<?php echo $courseid?>" >
        <input type="hidden" name="course2" id="course2" value="<?php echo $courseidcompare?>">

        <div class="row">
            <?php if ($userlevel < 3) { ?>
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Visits Over Time from <?php echo $rptdate; ?></span>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <?php if ($userlevel == 1) { ?>
                                <p>
                                    <strong>Legend</strong> <span class="line1"><strong>&#x25A0; Visits</strong></span> <span class="lineA"><strong>&#x25A0; Unique Visitors</strong></span> 
                                </p>
                                <?php } ?>
                                <?php if ($userlevel == 2) { ?>
                                <p>
                                    <strong>Compare With </strong><?php echo printDroplist(getCoursesFromUnit(), 'coursedrop2'); ?>
                                    <br>
                                    <strong>Legend</strong> <span class="line1"><strong>&#x25A0; <span id="course1name">Select Course 1</span></strong></span> <span class="line2"><strong>&#x25A0; <span id="course2name">Select Course 2</span></strong></span> 
                                </p>
                                <?php } ?>
                                <div class="chart">
                                    <!-- Sales Chart Canvas -->
                                    <svg id="chart1" class="chart" preserveAspectRatio="xMinYMin" data-bar-chart data-data='<?php echo $report->graphdata; ?>' style="width:100%; height:250px;"></svg>
                                    <script>
                                        ee = document.getElementById("chart1");
                                        ww = ee.clientWidth;
                                        hh = ee.clientHeight;
                                        ee.setAttribute("viewBox","0 0 "+ww+" "+hh);
                                        var x;
                                        var y;
                                        var xAxis;
                                        var yAxis;
                                        $('[data-bar-chart]').each(function (i, svg) {
                                            var el = d3.select('svg').node()
                                            var twidth = ww;
                                            var theight = hh;

                                            var margin = {top: 20, right: 20, bottom: 60, left: 50},
                                                width = twidth - margin.left - margin.right,
                                                height = theight - margin.top - margin.bottom;

                                            var parseDate = d3.time.format("%d-%m-%Y").parse;

                                            var $svg = $(svg);
                                            var data = $svg.data('data');

                                            data.forEach(function(d) {
                                                d.date = parseDate(d.date);
                                                d.totalvisit1 = +d.totalvisit1;
                                                d.totalvisit2 = +d.totalvisit2;
                                                d.uniquevisit1 = +d.uniquevisit1;
                                            });
                                            data.sort(function (a, b) {
                                                return a.date - b.date;
                                            });

                                            var countfn = function (d) { return ((d.totalvisit1 < d.totalvisit2) ? d.totalvisit2 : d.totalvisit1); }
                                            var datefn = function (d) { return d.date; }
                                            var datefn2 = function (d) { var d1 = d.date.getTime() ; return new Date(d1); }

                                            x = d3.time.scale()
                                                .range([0, width])
                                                .domain([d3.min(data, datefn), d3.max(data, datefn2)]);

                                            y = d3.scale.linear()
                                                .domain([0, d3.max (data, countfn)])
                                                .range([height, 0]);

                                            xAxis = d3.svg.axis()
                                                .scale(x)
                                                .orient("bottom")
                                                .ticks(d3.time.day, 1)
                                                .tickFormat(d3.time.format('%d %b %Y'));

                                            yAxis = d3.svg.axis()
                                                .scale(y)
                                                .orient("left");


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
                                                    .attr("transform", "rotate(-45)");

                                            svg.append("g")
                                                .attr("class", "y axis")
                                                .call(yAxis)
                                                .append("text")
                                                .attr("transform", "rotate(-90)")
                                                .attr("y", 6)
                                                .attr("dy", ".71em")
                                                .style("text-anchor", "end")
                                                .text("Frequency");

                                            var line = d3.svg.line()
                                                .x(function(d) { return x(d.date); })
                                                .y(function(d) { return y(d.totalvisit1); });

                                            var line2 = d3.svg.line()
                                                .x(function(d) { return x(d.date); })
                                                .y(function(d) { return y(d.totalvisit2); });

                                            var lineA = d3.svg.line()
                                                .x(function(d) { return x(d.date); })
                                                .y(function(d) { return y(d.uniquevisit1); });

                                            svg.append("path")
                                              .datum(data)
                                              .attr("class", "line1")
                                              .attr("d", line)
                                              ;

                                            svg.append("path")
                                              .datum(data)
                                              .attr("class", "lineA")
                                              .attr("d", lineA)
                                              ;

                                             <?php if ($userlevel == 2) { ?>
                                            svg.append("path")
                                              .datum(data)
                                              .attr("class", "line2")
                                              .attr("d", line2)
                                              ;
                                              <?php } ?>

                                        });

                                    </script>
                                </div><!-- /.chart-responsive -->
                            </div><!-- /.col -->
                        </div><!-- /.row -->
                    </div><!-- ./box-body -->
                 
                </div><!-- /.box -->
            </div><!-- /.col -->
            <?php } ?>
            
            <div class="col-md-12" id="pagediv">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Page Access from <?php echo $rptdate; ?></h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <br><br>
                        <table id="pagetable" class="table table-bordered table-striped">
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
            
            
            
            <div class="col-xs-12" id="downloaddiv">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Top Downloads from <?php echo $rptdate; ?></h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div style="margin-top: 40px;"></div>
                        <table id="downloadtable" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <?php if ($courseid <= 1) { ?>
                                <th>Course</th>
                                <?php } ?>
                                <th>File</th>
                                <th>Number of downloads</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($report->tbldatadl as $key => $rows) { 

                                $filecourseid = $rows->custom_var_v1;
                                //get context
                                $ccontextid = $DB->get_field('context', 'id', array('instanceid' => $filecourseid, 'contextlevel' => 50));
                                $mfid = $DB->get_field('files', 'id', array('contextid' => $ccontextid, 'component' => 'course', 'filearea' => 'repository', 'filename' => $rows->custom_var_v3));
                                ?>
                                <tr>
                                    <?php if ($courseid <= 1) { ?>
                                    <td><?php echo $rows->fullname; ?></td>
                                    <?php } ?>
                                    <td><a href='<?php echo $CFG->wwwroot. "/blocks/course_filemanager/"; ?>download_thumb.php?cid=<?php echo $filecourseid; ?>&mfid=<?php echo $mfid ?>&contextid=<?php echo $ccontextid; ?>&component=course&repo=course&d=/<?php echo $rows->custom_var_v3; ?>' title = 'Click to download ' style="font-weight: normal; color: #999999;"><?php echo $rows->custom_var_v3; ?></a></td>
                                    <td><?php echo $rows->cnt; ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div><!-- /.box-body -->
                </div>
            </div>
            
            <?php if ($userlevel == 3) { ?>
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Course Forum</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12 device">
                                <h5>Top Five Most Active Threads</h5>
                                <ul>
                                    <?php 
                                        foreach ($report->topthreads as $tt) {?>
                                        <li><a href="<?php echo $CFG->wwwroot. '/mod/forum/discuss.php?d='.$tt->discussion ?> "><?php echo $tt->name; ?></a>, <?php echo $tt->count;?> posts</li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 device">
                                <h5>Top Five Most Active Learners</h5>
                                <ul>
                                    <?php 
                                        foreach ($report->toplearners as $tl) {?>
                                        <li><a href="<?php echo $CFG->wwwroot. '/user/profile.php?id='.$tl->userid ?> "><?php echo getUserById($tl->userid)->firstname; ?></a>, <?php echo $tl->count;?> posts</li>
                                    <?php } ?>
                                </ul>
                            </div>
                            <div class="col-md-12 device">
                                <h5>Top Five Most Active Instructors</h5>
                                <ul>
                                    <?php 
                                        foreach ($report->topinstructors as $ti) {?>
                                        <li><a href="<?php echo $CFG->wwwroot. '/user/profile.php?id='.$ti->userid ?> "><?php echo getUserById($ti->userid)->firstname; ?></a>, <?php echo $ti->count;?> posts</li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div> <!-- end .row -->
                    </div><!-- /.box-body -->
                </div>
            </div>
            <?php } ?>

        </div><!-- /.row -->
    </section>    

    <script>
        $(function() {
            var s1 = '<?php echo $startdate->format("MMMM D, YYYY"); ?>';
            var e1 = '<?php echo $enddate->format("MMMM D, YYYY"); ?>';
            function cb(start, end) {
                $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            }
            function cb2(start, end) {
                $('#reportrange span').html(start + ' - ' + end);
            }
            <?php if (isset($startdate) && isset($enddate)) { ?>
            cb2('<?php echo $startdate->format("M d, Y"); ?>', '<?php echo $enddate->format("M d, Y"); ?>');
            <?php } else { ?>
            cb(moment().subtract(7, 'days'), moment());
            <?php } ?>
            var drp = $('#reportrange').daterangepicker({
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                startDate: new Date('<?php echo $startdate->format("D M d Y H:i:s O"); ?>'),
                endDate: new Date('<?php echo $enddate->format("D M d Y H:i:s O"); ?>')
            }, cb);

            //set values
            <?php if($unit > 0) {?>
            $('#unitdrop').val(<?php echo $unit?>);
            onChangeUnit(<?php echo $unit?>);
            <?php } ?>
        });
        $('#reportrange').on('apply.daterangepicker', function(ev, picker) {
            //do something, like clearing an input
            var start = $('#reportrange').data('daterangepicker').startDate;
            var end = $('#reportrange').data('daterangepicker').endDate;
            var param = "";
            if (start != '') {
                param = "?course="+$('#course1').val()+"&start="+start+"&end="+end+"&course2="+$('#course2').val()+"&unit="+$('#unitdrop').val();
            }
            window.location.href = "dashboard.php"+param;
        });

        $('#unitdrop').change(function() {
            onChangeUnit(0);
        });

        function onChangeUnit(initunit) {
            var unit;
            if (initunit == 0) {
                unit = $('#unitdrop').val();
            } else {
                unit = initunit;
            }
            $('#unit').val(unit);

            $.ajax({
                url: '<?php echo $CFG->wwwroot . '/blocks/analytics/ajax/getcourses.php'; ?>',
                dataType: 'json',
                type: 'GET',
                data: {unit : unit},
                success: function(data) {
                    $( "#coursedrop1" ).replaceWith( data[0] );
                    $( "#coursedrop2" ).replaceWith( data[1] );
                    if (initunit > 0 ) {
                        $( "#coursedrop1" ).val(<?php echo $courseid?>);
                        updateData(<?php echo $courseid?>, 'coursedrop1');
                        $( "#coursedrop2" ).val(<?php echo $courseidcompare?>);                
                        updateData(<?php echo $courseidcompare?>, 'coursedrop2');
                    } else {
                        $( "#coursedrop1" ).val(1);
                        updateData(1, 'coursedrop1');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                },
                complete: function(data) {
                }
            });

            $.ajax({
                url: '<?php echo $CFG->wwwroot . '/blocks/analytics/ajax/getunitdashboardcount.php'; ?>',
                dataType: 'json',
                type: 'GET',
                data: {unit : unit},
                success: function(data) {
                    $('#metacount').text(data['courses']);
                    $('#coursecount').text(data['courseruns']);
                    $('#pmetacount').text(data['pcourses']);
                    $('#pcoursecount').text(data['pcourseruns']);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                },
                complete: function(data) {
                }
            });

        }

        function updateData(cid, courseinput) {
            if(courseinput == "coursedrop1") {
                $('#course1').val(cid);
                var c1name = $('#coursedrop1 option[value='+cid+']').text();
                $('#course1name').text(c1name);

                //update the other 2 tables
                updatePageAccess();
                updateTopDownloads();
            }
            if(courseinput == "coursedrop2") {
                $('#course2').val(cid);
                var c2name = $('#coursedrop2 option[value='+cid+']').text();
                $('#course2name').text(c2name);

                //hide the other 2 stuffs
                if (cid > 1) {
                    $('#pagediv').slideUp();
                    $('#downloaddiv').slideUp();                    
                } else {
                    $('#pagediv').slideDown();
                    $('#downloaddiv').slideDown();                    
                }
            }

            $.ajax({
                url: '<?php echo $CFG->wwwroot . '/blocks/analytics/ajax/comparecourses.php'; ?>',
                dataType: 'json',
                type: 'GET',
                data: {sdate : getParameterByName('start'), edate: getParameterByName('end'), courseid : $('#course1').val(), courseid2 : $('#course2').val(), unit: $('#unit').val()},
                success: function(data) {

                    var parseDate = d3.time.format("%d-%m-%Y").parse;

                    data.forEach(function(d) {
                        d.date = parseDate(d.date);
                        d.totalvisit1 = +d.totalvisit1;
                        d.totalvisit2 = +d.totalvisit2;
                    });
                    data.sort(function (a, b) {
                        return a.date - b.date;
                    });


                    var countfn = function (d) { return ((d.totalvisit1 < d.totalvisit2) ? d.totalvisit2 : d.totalvisit1); }
                    var datefn = function (d) { return d.date; }
                    var datefn2 = function (d) { var d1 = d.date.getTime() ; return new Date(d1); }


                    var line = d3.svg.line()
                        .x(function(d) { return x(d.date); })
                        .y(function(d) { return y(d.totalvisit1); });

                    var line2 = d3.svg.line()
                        .x(function(d) { return x(d.date); })
                        .y(function(d) { return y(d.totalvisit2); });


                    // Scale the range of the data again 
                    x.domain([d3.min(data, datefn), d3.max(data, datefn2)]);
                    y.domain([0, d3.max (data, countfn)]);

                    // Select the section we want to apply our changes to
                    var svg = d3.select("[data-bar-chart]").transition();

                    // Make the changes
                    svg.select(".line1")   // change the line
                        .duration(750)
                        .attr("d", line(data));
                    <?php if ($userlevel == 2) { ?>
                    svg.select(".line2")   // change the line
                        .duration(750)
                        .attr("d", line2(data));
                    <?php } ?>
                    svg.select(".x.axis") // change the x axis
                        .duration(750)
                        .call(xAxis)
                        .selectAll("text")
                            .style("text-anchor", "end")
                            .attr("dx", "-.9em")
                            .attr("dy", ".25em")
                            .attr("transform", "rotate(-45)");
                    svg.select(".y.axis") // change the y axis
                        .duration(750)
                        .call(yAxis);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.dir(errorThrown);
                },
                complete: function(data) {
                    //console.dir(data);
                }
            });
        }

        function updatePageAccess() {
            $.ajax({
                url: '<?php echo $CFG->wwwroot . '/blocks/analytics/ajax/getpageaccess.php'; ?>',
                dataType: 'json',
                type: 'GET',
                data: {sdate : getParameterByName('start'), edate: getParameterByName('end'), courseid : $('#course1').val(), unit: $('#unit').val()},
                success: function(data) {

                    var newtable = '';
                    for (var key in data) {
                        newtable += '<tr>';
                        newtable += '<td>'+data[key].name+'</td>';
                        newtable += '<td>'+data[key].visitcount+'</td>';
                        newtable += '<td>'+data[key].uniqueview+'</td>';
                        newtable += '<td>'+data[key].timespent.secondsToTime()+'</td>';
                        newtable += '<td>'+parseFloat(data[key].generationtime).toFixed(2)+' secs</td>';
                        newtable += '<td>'+parseFloat((data[key].bouncepages / data[key].visitcount) * 100).toFixed(2) +'%</td>';
                        newtable += '</tr>';
                    }

                    $('#pagetable tbody').html(newtable);

                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.dir(errorThrown);
                },
                complete: function(data) {
                }
            });

        }

        function updateTopDownloads() {
            $.ajax({
                url: '<?php echo $CFG->wwwroot . '/blocks/analytics/ajax/gettopdownloads.php'; ?>',
                dataType: 'json',
                type: 'GET',
                data: {sdate : getParameterByName('start'), edate: getParameterByName('end'), courseid : $('#course1').val(), unit: $('#unit').val()},
                success: function(data) {

                    var newtable = '';
                    for (var key in data) {
                        newtable += '<tr>';
                        newtable += '<td>'+data[key].fullname+'</td>';
                        newtable += '<td>'+data[key].hreflink+'</td>';
                        newtable += '<td>'+data[key].cnt+'</td>';
                        newtable += '</tr>';
                    }

                    $('#downloadtable tbody').html(newtable);

                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.dir(errorThrown);
                },
                complete: function(data) {
                }
            });

        }

        String.prototype.secondsToTime = function () {
            var sec_num = parseInt(this, 10); // don't forget the second param
            var hours   = Math.floor(sec_num / 3600);
            var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
            var seconds = sec_num - (hours * 3600) - (minutes * 60);

            if (hours   < 10) {hours   = "0"+hours;}
            if (minutes < 10) {minutes = "0"+minutes;}
            if (seconds < 10) {seconds = "0"+seconds;}
            if (hours > 0) {
                return hours+' hrs '+minutes+' mins '+seconds+ " secs";
            } 
            if (minutes > 0) {
                return minutes+" mins "+seconds + " secs";    
            } 
            return seconds + " secs";
        }

        function getParameterByName(name, url) {
            if (!url) url = window.location.href;
            name = name.replace(/[\[\]]/g, "\\$&");
            var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
                results = regex.exec(url);
            if (!results) return null;
            if (!results[2]) return '';
            return decodeURIComponent(results[2].replace(/\+/g, " "));
        }

    </script>
<?php
echo $OUTPUT->footer();
?>
