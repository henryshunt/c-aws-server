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
        <link href="resources/styles/chartist.css" rel="stylesheet" type="text/css">
        <link href="resources/styles/graphs.css" rel="stylesheet" type="text/css">
        <script src="resources/scripts/global.js" type="text/javascript"></script>
        <script src="resources/scripts/jquery.js" type="text/javascript"></script>
        <script src="resources/scripts/chartist.js" type="text/javascript"></script>
        <script src="resources/scripts/moment.js" type="text/javascript"></script>
        <script src="resources/scripts/moment-tz.js" type="text/javascript"></script>
        <script src="resources/scripts/page-station.js" type="text/javascript"></script>

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

                var options = {
                    showPoint: false, lineSmooth: false, height: 350,

                    axisY: {
                        offset: 27,
                        labelInterpolationFnc: function(value) {
                            return value.toFixed(1);
                        }
                    },

                    axisX: {
                        type: Chartist.FixedScaleAxis, divisor: 6, offset: 20,    
                        labelInterpolationFnc: function(value) {
                            return moment.unix(value).utc().tz(awsTimeZone).format("HH:mm");
                        }
                    }
                };

                document.getElementById("graph_temperature").style.display = "block";
                graph = new Chartist.Line("#graph_temperature", null, options);
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
                        <a class="menu_item" href=".">Now</a>
                        <a class="menu_item" href="statistics.php">Statistics</a>
                        <a class="menu_item" href="camera.php">Camera</a>
                        <span>|</span>
    
                        <span>Graph:</span>
                        <a class="menu_item" href="graph-day.php">Day</a>
                        <a class="menu_item" href="graph-year.php">Year</a>
    
                        <span>|</span>
                        <a class="menu_item" href="climate.php">Climate</a>
                        <a class="menu_item" id="ami" href="station.php">Station</a>
                    </div>

                    <span><?php echo $scope; ?></span>
                </div>
            </div>
        </div>

        <div id="main">
            <div class="group">
                <div class="group_header">
                    <p class="group_title">Station Information</p>
                </div>
    
                <table class="field_table">
                    <tr>
                        <td><p class="field_label">Name:</p></td>
                        <td><p class="field_value"><?php echo $config->get_aws_name(); ?></p></td>
                    </tr>
                    <tr>
                        <td><p class="field_label">Time Zone Name:</p></td>
                        <td><p class="field_value"><?php echo $config->get_aws_time_zone(); ?></p></td>
                    </tr>
                    <tr>
                        <td><p class="field_label" style="margin-top: 10px">Elevation:</p></td>
                        <td>
                            <p class="field_value" style="margin-top: 10px">
                                <?php echo $config->get_aws_elevation(); ?> m
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td><p class="field_label">Latitude:</p></td>
                        <td><p class="field_value"><?php echo $config->get_aws_latitude(); ?></p></td>
                    </tr>
                    <tr>
                        <td><p class="field_label">Longitude:</p></td>
                        <td><p class="field_value"><?php echo $config->get_aws_longitude(); ?></p></td>
                    </tr>
                </table>
            </div>

            <div class="group">
                <div class="group_header">
                    <p class="group_title">Computer Environment</p>

                    <div style="padding-right: 10px">
                        <span class="group_key">(<span>Enclosure Temp</span>, <span>CPU Temp</span>) [°C]</span>
                    </div>
                </div>
    
                <table class="field_table">
                    <tr>
                        <td><p class="field_label">Enclosure Temperature:</p></td>
                        <td><p id="item_EncT" class="field_value"></p></td>
                    </tr>
                    <tr>
                        <td><p class="field_label">Idle CPU Temperature:</p></td>
                        <td><p id="item_CPUT" class="field_value"></p></td>
                    </tr>
                </table>

                <div id="graph_temperature" class="ct-chart" style="margin-top: -10px"></div>
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