<?php
require_once('../../../config.php');
require_once($CFG->dirroot . '/lib/accesslib.php');

$enabletracking = get_config('local_analytics', 'enabletracking');

if(! $enabletracking){
    redirect($CFG->wwwroot . '/administration/index.php');
    return;
}

global $USER;
$siteid = optional_param('siteid', 1, PARAM_INT);
$page = optional_param('page', '', PARAM_ALPHANUM);

$urlparams = array('id' => $siteid);

// To verify if user is an instructor in the course
$course = $DB->get_record('course', $urlparams, '*', MUST_EXIST);
require_login($course);

$visitorlink = $CFG->wwwroot . '/local/analytics/reports/visitor.php';
$dashboardlink = $CFG->wwwroot . '/local/analytics/reports/dashboard.php';

if ($siteid > 1) {
    $visitorlink .= '?siteid='.$siteid;
    $dashboardlink .= '?siteid='.$siteid;
}

$PAGE->set_title($COURSE->fullname.' / Analytic / Active Pages');
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
    <!-- Please add additional javascripts here -->
    <script type="text/javascript" src="js/moment.min.js"></script>
    <script type="text/javascript" src="js/daterangepicker.js"></script>
    <link rel="stylesheet" type="text/css" href="css/daterangepicker.css" />
    <script type="text/javascript" src="js/pages/activepages.js"></script>

    <!-- Content Header (Page header) -->
    <section class="content-header serv-per-header">
        <h1 style="padding: 5px 0px 5px 10px;">
            Most Active Pages
        </h1>
        <div id="reportrange" class="pull-right" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; margin-top:-30px;">
            <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp; <span></span> <b class="caret"></b>
        </div>
        <script type="text/javascript">
            $(function() {
                function cb(start, end) {
                    $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
                }
                cb(moment().subtract(29, 'days'), moment());
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
                alert ($('#reportrange'));
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
                        <h3 class="box-title">Server Performance</h3>
                    </div><!-- /.box-header -->

                    <div class="box-body">
                        <br>
                        <br>
                        <table id="mostActivePageDetailsTbl" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>Page Name</th>
                                <th>Page Views</th>
                                <th>Unique <br> Page Views</th>
                                <th>Bounce <br> Rate</th>
                                <th>Avg. Time <br> On Page</th>
                                <th>Exit <br>Rate</th>
                                <th>Avg. Generation <br> Time</th>
                            </tr>
                            </thead>

                            <tbody>
                            <tr>
                                <td>Learn Moodle : Log in to the site</td>
                                <td>13</td>
                                <td>6</td>
                                <td>14%</td>
                                <td>00:3:33</td>
                                <td>33%</td>
                                <td>0.84s</td>
                            </tr>
                            <tr>
                                <td>Training Programme </td>
                                <td>33</td>
                                <td>20</td>
                                <td>40%</td>
                                <td>00:6:30</td>
                                <td>22%</td>
                                <td>0.94s</td>
                            </tr>
                            <tr>
                                <td>Analytic : Dashboard</td>
                                <td>61</td>
                                <td>48</td>
                                <td>50%</td>
                                <td>00:8:33</td>
                                <td>21%</td>
                                <td>1s</td>
                            </tr>
                            <tr>
                                <td>Course : Programming Class One</td>
                                <td>87</td>
                                <td>56</td>
                                <td>45%</td>
                                <td>00:7:53</td>
                                <td>33%</td>
                                <td>2.14s</td>
                            </tr>
                            <tr>
                                <td>Learnet Moodle : Administration</td>
                                <td>78</td>
                                <td>47</td>
                                <td>51%</td>
                                <td>00:5:55</td>
                                <td>37%</td>
                                <td>1.84s</td>
                            </tr>
                            <tr>
                                <td>Learn Moodle : Log in to the site</td>
                                <td>13</td>
                                <td>6</td>
                                <td>14%</td>
                                <td>00:3:33</td>
                                <td>33%</td>
                                <td>0.84s</td>
                            </tr>
                            <tr>
                                <td>Training Programme </td>
                                <td>33</td>
                                <td>20</td>
                                <td>40%</td>
                                <td>00:6:30</td>
                                <td>22%</td>
                                <td>0.94s</td>
                            </tr>
                            <tr>
                                <td>Analytic : Dashboard</td>
                                <td>61</td>
                                <td>48</td>
                                <td>50%</td>
                                <td>00:8:33</td>
                                <td>21%</td>
                                <td>1s</td>
                            </tr>
                            <tr>
                                <td>Course : Programming Class One</td>
                                <td>87</td>
                                <td>56</td>
                                <td>45%</td>
                                <td>00:7:53</td>
                                <td>33%</td>
                                <td>2.14s</td>
                            </tr>
                            <tr>
                                <td>Learnet Moodle : Administration</td>
                                <td>78</td>
                                <td>47</td>
                                <td>51%</td>
                                <td>00:5:55</td>
                                <td>37%</td>
                                <td>1.84s</td>
                            </tr>
                            <tr>
                                <td>Learn Moodle : Log in to the site</td>
                                <td>13</td>
                                <td>6</td>
                                <td>14%</td>
                                <td>00:3:33</td>
                                <td>33%</td>
                                <td>0.84s</td>
                            </tr>
                            <tr>
                                <td>Training Programme </td>
                                <td>33</td>
                                <td>20</td>
                                <td>40%</td>
                                <td>00:6:30</td>
                                <td>22%</td>
                                <td>0.94s</td>
                            </tr>
                            <tr>
                                <td>Analytic : Dashboard</td>
                                <td>61</td>
                                <td>48</td>
                                <td>50%</td>
                                <td>00:8:33</td>
                                <td>21%</td>
                                <td>1s</td>
                            </tr>
                            <tr>
                                <td>Course : Programming Class One</td>
                                <td>87</td>
                                <td>56</td>
                                <td>45%</td>
                                <td>00:7:53</td>
                                <td>33%</td>
                                <td>2.14s</td>
                            </tr>
                            <tr>
                                <td>Learnet Moodle : Administration</td>
                                <td>78</td>
                                <td>47</td>
                                <td>51%</td>
                                <td>00:5:55</td>
                                <td>37%</td>
                                <td>1.84s</td>
                            </tr>
                            </tbody>
                        </table> <!-- end table -->
                    </div> <!-- /.box-body -->
                </div> <!-- end .box -->
            </div> <!-- end .col-xs-12 -->
        </div><!-- /.row -->
    </section><!-- /.content -->
<?php
echo $OUTPUT->footer();
?>
