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
        <link href="resources/styles/groups.css" rel="stylesheet" type="text/css">
        <script src="resources/scripts/page-index.js" type="text/javascript"></script>

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
                    <a class="ami" href=".">Reports</a>
                    <a href="statistics.php">Statistics</a>
                    <a href="camera.php">Camera</a>
                    <span>|</span>

                    <span>Graph:</span>
                    <a href="graph-day.php">Day</a>
                    <a href="graph-year.php">Year</a>
                    <span>|</span>

                    <a href="climate.php">Climate</a>
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
                    <p id="scroller_time" class="st_picker" onclick="openPicker()"></p>
                </div>
                <div class="scroller_button" onclick="scrollerRight()">
                    <i class="material-icons">chevron_right</i>
                </div>
            </div>

            <div class="group">
                <div class="group_header">
                    <p class="group_title">Ambient Temperature</p>
                </div>

                <table class="field_table">
                    <tr>
                        <td><p class="field_label">Air Temperature:</p></td>
                        <td><p id="item_AirT" class="field_value"></p></td>
                    </tr>
                    <tr>
                        <td><p class="field_label">Exposed Thermometer (affected by rain/sun):</p></td>
                        <td><p id="item_ExpT" class="field_value"></p></td>
                    </tr>
                </table>
            </div>

            <div class="group">
                <div class="group_header">
                    <p class="group_title">Moisture</p>
                </div>

                <table class="field_table">
                    <tr>
                        <td><p class="field_label">Relative Humidity:</p></td>
                        <td><p id="item_RelH" class="field_value"></p></td>
                    </tr>
                    <tr>
                        <td><p class="field_label">Dew Point:</p></td>
                        <td><p id="item_DewP" class="field_value"></p></td>
                    </tr>
                </table>
            </div>

            <div class="group">
                <div class="group_header">
                    <p class="group_title">Wind</p>
                </div>

                <table class="field_table">
                    <tr>
                        <td><p class="field_label">Wind Speed:</p></td>
                        <td><p id="item_WSpd" class="field_value"></p></td>
                    </tr>
                    <tr>
                        <td><p class="field_label">Wind Direction (blowing from):</p></td>
                        <td><p id="item_WDir" class="field_value"></p></td>
                    </tr>
                    <tr>
                        <td><p class="field_label" style="margin-top: 10px">Wind Gust:</p></td>
                        <td><p id="item_WGst" class="field_value" style="margin-top: 10px"></p></td>
                    </tr>
                </table>
            </div>

            <div class="group">
                <div class="group_header">
                    <p class="group_title">Solar Radiation</p>
                </div>

                <table class="field_table">
                    <tr>
                        <td><p class="field_label">Sunshine Duration:</p></td>
                        <td><p id="item_SunD" class="field_value"></p></td>
                    </tr>
                    <tr>
                        <td><p class="field_label">Sunshine Duration over Past Hour:</p></td>
                        <td><p id="item_SunD_PHr" class="field_value"></p></td>
                    </tr>
                </table>
            </div>

            <div class="group">
                <div class="group_header">
                    <p class="group_title">Precipitation</p>
                </div>

                <table class="field_table">
                    <tr>
                        <td><p class="field_label">Rainfall:</p></td>
                        <td><p id="item_Rain" class="field_value"></p></td>
                    </tr>
                    <tr>
                        <td><p class="field_label">Rainfall over Past Hour:</p></td>
                        <td><p id="item_Rain_PHr" class="field_value"></p></td>
                    </tr>
                </table>
            </div>

            <div class="group">
                <div class="group_header">
                    <p class="group_title">Barometric Pressure</p>
                </div>

                <table class="field_table">
                    <tr>
                        <td><p class="field_label">Station Pressure (at station elevation):</p></td>
                        <td><p id="item_StaP" class="field_value"></p></td>
                    </tr>
                    <tr>
                        <td><p class="field_label">Mean Sea Level Pressure:</p></td>
                        <td><p id="item_MSLP" class="field_value"></p></td>
                    </tr>
                    <tr>
                        <td><p class="field_label" style="margin-top: 10px">3-Hour Pressure Tendency:</p></td>
                        <td><p id="item_StaP_PTH" class="field_value" style="margin-top: 10px"></p></td>
                    </tr>
                </table>
            </div>

            <div class="group g_last">
                <div class="group_header">
                    <p class="group_title">Soil Temperature</p>
                </div>

                <table class="field_table">
                    <tr>
                        <td><p class="field_label">10 Centimetres Down:</p></td>
                        <td><p id="item_ST10" class="field_value"></p></td>
                    </tr>
                    <tr>
                        <td><p class="field_label">30 Centimetres Down:</p></td>
                        <td><p id="item_ST30" class="field_value"></p></td>
                    </tr>
                    <tr>
                        <td><p class="field_label">1 Metre Down:</p></td>
                        <td><p id="item_ST00" class="field_value"></p></td>
                    </tr>
                </table>
            </div>
        </div>

    </body>
</html>
