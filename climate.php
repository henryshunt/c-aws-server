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
        <link href="resources/styles/chartist.css" rel="stylesheet" type="text/css">
        <script src="resources/scripts/chartist.js" type="text/javascript"></script>
        <link href="resources/styles/grouping.css" rel="stylesheet" type="text/css">        
        <link href="resources/styles/graphing.css" rel="stylesheet" type="text/css">
        <link href="resources/styles/page-climate.css" rel="stylesheet" type="text/css">
        <script src="resources/scripts/page-climate.js" type="text/javascript"></script>

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
                    <a href="graph-year.php">Year</a>
                    <span>|</span>
                    <a class="ami" href="climate.php">Climate</a>
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
                <div class="scroller_time"><p id="scroller_time"></p></div>
                <div class="scroller_button" onclick="scrollerRight()">
                    <i class="material-icons">chevron_right</i>
                </div>
            </div>

            <div class="group">
                <div class="group_header">
                    <p class="group_title">Year Statistics</p>
                </div>

                <table class="field_table">
                    <tr>
                        <td><p class="field_label">Air Temperature Average:</p></td>
                        <td><p id="item_AirT_Avg_Year" class="field_value"></p></td>
                    </tr>
                    <tr>
                        <td><p class="field_label">Air Temperature Minimum:</p></td>
                        <td><p id="item_AirT_Min_Year" class="field_value"></p></td>
                    </tr>
                    <tr>
                        <td><p class="field_label">Air Temperature Maximum:</p></td>
                        <td><p id="item_AirT_Max_Year" class="field_value"></p></td>
                    </tr>
                </table>
            </div>

            <div class="group">
                <div class="group_header">
                    <p class="group_title">Monthly Statistics</p>
                </div>

                <table id="climate_months" class="climate_table">
                    <thead>
                        <tr>
                            <td></td><td></td><td>Jan</td><td>Feb</td><td>Mar</td><td>Apr</td>
                            <td>May</td><td>Jun</td><td>Jul</td><td>Aug</td><td>Sep</td>
                            <td>Oct</td><td>Nov</td><td>Dec</td>
                        </tr>
                    </thead>

                    <tbody>
                        <tr id="item_AirT_Avg_Months">
                            <td>Air Temperature (°C)</td>
                            <td>AVG</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr id="item_AirT_Min_Months">
                            <td>Air Temperature (°C)</td>
                            <td>MIN</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr id="item_AirT_Max_Months">
                            <td>Air Temperature (°C)</td>
                            <td>MAX</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr id="item_RelH_Avg_Months">
                            <td>Relative Humidity (%)</td>
                            <td>AVG</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr id="item_WSpd_Avg_Months">
                            <td>Wind Speed (mph)</td>
                            <td>AVG</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr id="item_WSpd_Max_Months">
                            <td>Wind Speed (mph)</td>
                            <td>MAX</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr id="item_WDir_Avg_Months">
                            <td>Wind Direction (°)</td>
                            <td>AVG</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr id="item_WGst_Max_Months">
                            <td>Wind Gust (mph)</td>
                            <td>MAX</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr id="item_SunD_Ttl_Months">
                            <td>Sunshine Duration (hrs)</td>
                            <td>TTL</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr id="item_Rain_Ttl_Months">
                            <td>Rainfall (mm)</td>
                            <td>TTL</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr id="item_MSLP_Avg_Months">
                            <td>MSL Pressure (hPa)</td>
                            <td>AVG</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr id="item_ST10_Avg_Months">
                            <td>Soil Temp 10CM (°C)</td>
                            <td>AVG</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr id="item_ST30_Avg_Months">
                            <td>Soil Temp 30CM (°C)</td>
                            <td>AVG</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr id="item_ST00_Avg_Months">
                            <td>Soil Temp 1M (°C)</td>
                            <td>AVG</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="group">
                <div class="group_header gh_no_separator">
                    <p class="group_title">Air Temperature</p>
                    <span class="group_key">°C (<span>Average</span>, <span>Minimum</span>, <span>Maximum</span>)</span>
                </div>

                <div id="graph_temperature" class="ct-chart g_open"></div>
            </div>

            <div class="group">
                <div class="group_header gh_no_separator">
                    <p class="group_title">Relative Humidity</p>
                    <span class="group_key">% (<span>Average</span>, <span>Minimum</span>, <span>Maximum</span>)</span>
                </div>

                <div id="graph_humidity" class="ct-chart  g_open"></div>
            </div>

            <div class="group">
                <div class="group_header gh_no_separator">
                    <p class="group_title">Wind Velocity</p>
                    <span class="group_key">mph (<span>Average Speed</span>, <span>Maximum Gust</span>)</span>
                </div>

                <div id="graph_wind" class="ct-chart g_open"></div>
            </div>

            <div class="group">
                <div class="group_header gh_no_separator">
                    <p class="group_title">Sunshine Duration</p>
                    <span class="group_key">hrs (<span>Total</span>)</span>
                </div>

                <div id="graph_sunshine" class="ct-chart g_open"></div>
            </div>

            <div class="group">
                <div class="group_header gh_no_separator">
                    <p class="group_title">Rainfall</p>
                    <span class="group_key">mm (<span>Total</span>)</span>
                </div>

                <div id="graph_rainfall" class="ct-chart g_open"></div>
            </div>

            <div class="group g_last">
                <div class="group_header gh_no_separator">
                    <p class="group_title">Soil Temperature</p>
                    <span class="group_key">°C (<span>10CM Average</span>, <span>30CM Average</span>, <span>1M Average</span>)</span>
                </div>

                <div id="graph_soil" class="ct-chart g_open"></div>
            </div>
        </div>

    </body>
</html>