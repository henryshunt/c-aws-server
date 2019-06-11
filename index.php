<meta charset="UTF-8">
<!DOCTYPE html>

<?php
    include_once("routines/config.php");
    $config = new Config("config.ini");

    if (!$config) { echo "Configuration file error"; exit(); }
    
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
        <link href="resources/styles/trebuchet.ttf" type="x-font-ttf">
        <link href="resources/styles/global.css" rel="stylesheet" type="text/css">
        <script src="resources/scripts/global.js" type="text/javascript"></script>
        <script src="resources/scripts/jquery.js" type="text/javascript"></script>
        <script src="resources/scripts/moment.js" type="text/javascript"></script>
        <script src="resources/scripts/moment-tz.js" type="text/javascript"></script>
        <script src="resources/scripts/page-index.js" type="text/javascript"></script>

        <script>
            const awsTimeZone
                = "<?php echo $config->get_aws_time_zone(); ?>";

            var requestedTime = null;
            var updaterTimeout = null;
            var countTimeout = null;
            var timeToUpdate = 0;

            $(document).ready(function() {
                setInterval(function() {
                    displayLocalTime();
                }, 250);

                updateData();
            });
        </script>
    </head>

    <body>
        <div id="header">
            <div id="header_items">
                <h1 id="header_left"><?php echo $config->get_aws_name(); ?></h1>
                <h2 id="header_right">C - AWS</h2>
            </div>

            <div id="menu">
                <div id="menu_items">
                    <div>
                        <a class="menu_item" id="ami" href=".">Now</a>
                        <a class="menu_item" href="statistics.php">Statistics</a>
                        <a class="menu_item" href="camera.php">Camera</a>
                        <span>|</span>
    
                        <span>Graph:</span>
                        <a class="menu_item" href="graph-day.php">Day</a>
                        <a class="menu_item" href="graph-year.php">Year</a>
    
                        <span>|</span>
                        <a class="menu_item" href="climate.php">Climate</a>
                        <a class="menu_item" href="station.php">Station</a>
                    </div>

                    <span><?php echo $scope; ?></span>
                </div>
            </div>
        </div>

        <div id="main">
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

            <div class="group" style="margin-bottom: 0px">
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

        <div id="footer">
            <div id="footer_items">
                <p class="footer_label">Local Time:</p>
                <p id="item_local_time" class="footer_value" style="width: 210px"></p>
    
                <div style="float: right; margin-left: 10px">
                    <p class="footer_label">Data Time:</p>
                    <p id="item_data_time" class="footer_value" style="width: 60px"></p>

                    <p class="footer_label" style="margin-left: 10px">Update:</p>
                    <p id="item_update" class="footer_value" style="width: 35px"></p>
                </div>
            </div>
        </div>

    </body>
</html>
