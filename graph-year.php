<meta charset="UTF-8">
<!DOCTYPE html>

<?php
    date_default_timezone_set("UTC");
    include_once("routines/config.php");
    
    try { $config = new Config("config.ini"); }
    catch (Exception $e)
    {
        echo "Bad configuration file";
        exit(); 
    }
    
    // Create the page title and scope text
    $title = "C-AWS " . $config->get_aws_name();
    $title .= ($config->get_is_remote()
        ? " [Remote]" : " [Local]");

    $scope = "Accessing <b>"
        . ($config->get_is_remote() ? "REMOTE" : "LOCAL")
        . "</b> data stores";
?>

<html>
    <head>
        <title><?php echo $title; ?></title>
        <link href="resources/styles/defaults.css" rel="stylesheet" type="text/css">
        <script src="resources/scripts/helpers.js" type="text/javascript"></script>
        <script src="resources/scripts/jquery.js" type="text/javascript"></script>

        <link href="resources/styles/header.css" rel="stylesheet" type="text/css">
        <script src="resources/scripts/moment.js" type="text/javascript"></script>
        <script src="resources/scripts/moment-tz.js" type="text/javascript"></script>
        <link href="resources/styles/flatpickr.css" rel="stylesheet" type="text/css">
        <script src="resources/scripts/flatpickr.js" type="text/javascript"></script>
        <link href="resources/styles/chartist.css" rel="stylesheet" type="text/css">
        <script src="resources/scripts/chartist.js" type="text/javascript"></script>
        <link href="resources/styles/grouping.css" rel="stylesheet" type="text/css">
        <link href="resources/styles/graphing.css" rel="stylesheet" type="text/css">
        <script src="resources/scripts/page-graph-year.js" type="text/javascript"></script>

        <script>
            const awsTimeZone = "<?php echo $config->get_aws_time_zone(); ?>";
        </script>
    </head>

    <body>
        <div class="header">
            <div class="titles">
                <h1><?php echo $config->get_aws_name(); ?></h1>
                <h2>C - AWS</h2>
            </div>

            <div class="menu">
                <div>
                    <a href=".">Reports</a>
                    <a href="statistics.php">Statistics</a>
                    <a href="camera.php">Camera</a>
                    <span>|</span>
                    <span>Graph:</span>
                    <a href="graph-day.php">Day</a>
                    <a class="ami" href="graph-year.php">Year</a>
                    <span>|</span>
                    <a href="climate.php">Climate</a>
                    <span>|</span>
                    <a href="station.php">Station</a>
                    
                    <span><?php echo $scope; ?></span>
                </div>
            </div>
        </div>

        <div class="main">
            <div class="group g_scroller">
                <div class="scroller_button" onclick="scrollerLeft()">
                    <i class="material-icons">chevron_left</i>
                </div>
                <div class="scroller_time">
                    <p id="scroller_time" class="st_picker" onclick="pickerOpen()"></p>
                </div>
                <div class="scroller_button" onclick="scrollerRight()">
                    <i class="material-icons">chevron_right</i>
                </div>
            </div>

            <div class="group g_wide">
                <div class="group_header gh_no_separator gh_collapsible" onclick="toggleGraph('temperature', this)">
                    <div class="group_toggle">
                        <i class="material-icons">expand_more</i>
                    </div>
                    <p class="group_title">Air Temperature</p>
                    <span class="group_key">°C (<span>Average</span>, <span>Minimum</span>, <span>Maximum</span>)</span>
                </div>

                <div id="graph_temperature" class="ct-chart g_open"></div>
            </div>

            <div class="group g_wide">
                <div class="group_header gh_no_separator gh_collapsible" onclick="toggleGraph('humidity', this)">
                    <div class="group_toggle">
                        <i class="material-icons">chevron_right</i>
                    </div>
                    <p class="group_title">Relative Humidity</p>
                    <span class="group_key">% (<span>Average</span>, <span>Minimum</span>, <span>Maximum</span>)</span>
                </div>

                <div id="graph_humidity" class="ct-chart"></div>
            </div>

            <div class="group g_wide">
                <div class="group_header gh_no_separator gh_collapsible" onclick="toggleGraph('wind', this)">
                    <div class="group_toggle">
                        <i class="material-icons">chevron_right</i>
                    </div>
                    <p class="group_title">Wind Velocity</p>
                    <span class="group_key">mph (<span>Average Speed</span>, <span>Maximum Gust</span>)</span>
                </div>

                <div id="graph_wind" class="ct-chart"></div>
            </div>
            
            <div class="group g_wide">
                <div class="group_header gh_no_separator gh_collapsible" onclick="toggleGraph('direction', this)">
                    <div class="group_toggle">
                        <i class="material-icons">chevron_right</i>
                    </div>
                    <p class="group_title">Wind Direction</p>
                    <span class="group_key">° (<span>Average</span>)</span>
                </div>

                <div id="graph_direction" class="ct-chart"></div>
            </div>

            <div class="group g_wide">
                <div class="group_header gh_no_separator gh_collapsible" onclick="toggleGraph('sunshine', this)">
                    <div class="group_toggle">
                        <i class="material-icons">chevron_right</i>
                    </div>
                    <p class="group_title">Sunshine Duration</p>
                    <span class="group_key">hrs (<span>Total</span>)</span>
                </div>

                <div id="graph_sunshine" class="ct-chart"></div>
            </div>

            <div class="group g_wide">
                <div class="group_header gh_no_separator gh_collapsible" onclick="toggleGraph('rainfall', this)">
                    <div class="group_toggle">
                        <i class="material-icons">chevron_right</i>
                    </div>
                    <p class="group_title">Rainfall</p>
                    <span class="group_key">mm (<span>Total</span>)</span>
                </div>

                <div id="graph_rainfall" class="ct-chart"></div>
            </div>

            <div class="group g_wide">
                <div class="group_header gh_no_separator gh_collapsible" onclick="toggleGraph('pressure', this)">
                    <div class="group_toggle">
                        <i class="material-icons">chevron_right</i>
                    </div>
                    <p class="group_title">Mean Sea Level Pressure</p>
                    <span class="group_key">hPa (<span>Average</span>, <span>Minimum</span>, <span>Maximum</span>)</span>
                </div>

                <div id="graph_pressure" class="ct-chart"></div>
            </div>

            <div class="group g_wide g_last">
                <div class="group_header gh_no_separator gh_collapsible" onclick="toggleGraph('soil', this)">
                    <div class="group_toggle">
                        <i class="material-icons">chevron_right</i>
                    </div>
                    <p class="group_title">Soil Temperature</p>
                    <span class="group_key">°C (<span>10CM Average</span>, <span>30CM Average</span>, <span>1M Average</span>)</span>
                </div>

                <div id="graph_soil" class="ct-chart"></div>
            </div>
        </div>

    </body>
</html>