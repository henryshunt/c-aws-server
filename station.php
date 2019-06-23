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
        <link href="resources/styles/fira-sans.ttf" type="x-font-ttf">
        <link href="resources/styles/global.css" rel="stylesheet" type="text/css">
        <link href="resources/styles/flatpickr.css" rel="stylesheet" type="text/css">
        <link href="resources/styles/chartist.css" rel="stylesheet" type="text/css">
        <link href="resources/styles/graphs.css" rel="stylesheet" type="text/css">
        <script src="resources/scripts/global.js" type="text/javascript"></script>
        <script src="resources/scripts/jquery.js" type="text/javascript"></script>
        <script src="resources/scripts/chartist.js" type="text/javascript"></script>
        <script src="resources/scripts/moment.js" type="text/javascript"></script>
        <script src="resources/scripts/moment-tz.js" type="text/javascript"></script>
        <script src="resources/scripts/flatpickr.js" type="text/javascript"></script>
        <script src="resources/scripts/page-station.js" type="text/javascript"></script>

        <script>
            const awsTimeZone
                = "<?php echo $config->get_aws_time_zone(); ?>";

            var isLoading = true;
            var datePicker = null;
            var requestedTime = null;
            var updaterTimeout = null;

            $(document).ready(function() {
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
                updateData(true, false);
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
            <div class="group" id="scroller">
                <div id="scroller_left" class="scroller_button" onclick="scrollerLeft()">
                    <svg width="20" height="20" viewBox="0 0 512 512">
                        <polygon points="352,128.4 319.7,96 160,256 160,256 160,256 319.7,416 352,383.6 224.7,256"/>
                    </svg>
                </div>

                <div><p id="scroller_time" onclick="openPicker()" style="cursor: pointer; text-decoration: underline"></p></div>

                <div id="scroller_right" class="scroller_button" onclick="scrollerRight()">
                    <svg width="20" height="20" viewBox="0 0 512 512">
                        <polygon points="160,128.4 192.3,96 352,256 352,256 352,256 192.3,416 160,383.6 287.3,256"/>
                    </svg>
                </div>
            </div>

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

            <?php
                if (!$config->get_is_remote())
                {
                    include_once("routines/station.php");
                    $static_info = get_static_info();

                    if ($static_info == NULL)
                    {
                        $startup_time = "No Data";
                        $internal_drive_space = "No Data";
                        $camera_drive_space = "No Data";
                    }
                    else
                    {
                        // System startup time
                        if ($static_info[0] != NULL && $static_info[0] != "None")
                        {
                            $startup_time = $static_info[0];
                            $startup_time = date_create_from_format("Y-m-d H:i:s", $startup_time);
                            $startup_time->setTimezone(
                                new DateTimeZone($config->get_aws_time_zone()));
                            $startup_time = $startup_time->format("d/m/Y \a\\t H:i");
                        } else $startup_time = "No Data";

                        // Internal drive space
                        if ($static_info[1] != NULL && $static_info[1] != "None")
                        {
                            $internal_drive_space = $static_info[1];
                            $internal_drive_space = round(floatval($internal_drive_space), 2) . " GB";
                        } else $internal_drive_space = "No Data";

                        // Camera drive space
                        if ($static_info[2] != NULL && $static_info[2] != "None")
                        {
                            $camera_drive_space = $static_info[2];
                            $camera_drive_space = round(floatval($camera_drive_space), 2) . " GB";
                        } else $camera_drive_space = "No Data";
                    }

                    echo "<div class=\"group\" style=\"margin-bottom: 0px\">"
                         . "<div class=\"group_header\">"
                         . "<p class=\"group_title\">Local Only</p></div>"
                         . "<table class=\"field_table\">"
                         . "<tr><td><p class=\"field_label\">System Start Time:</p></td>"
                         . "<td><p class=\"field_value\">" . $startup_time . "</p></td></tr>"
                         . "<tr><td><p class=\"field_label\" style=\"margin-top: 10px\">"
                         . "Internal Drive Remaining Space:</p></td>"
                         . "<td><p class=\"field_value\" style=\"margin-top: 10px\">"
                         . $internal_drive_space . "</p></td></tr>"
                         . "<tr><td><p class=\"field_label\">Camera Drive Remaining Space:</p></td>"
                         . "<td><p class=\"field_value\">" . $camera_drive_space . "</p></td></tr>"
                         . "<tr><td><p class=\"field_label\" style=\"margin-top: 10px\">"
                         . "Shutdown Station Computer:</p></td>"
                         . "<td><p class=\"field_value\" style=\"margin-top: 10px\">"
                         . "<button onclick=\"send_command('shutdown')\">Send Command</button></p></td>"
                         . "</tr><tr><td><p class=\"field_label\">Restart Station Computer:</p></td>"
                         . "<td><p class=\"field_value\">"
                         . "<button onclick=\"send_command('restart')\">Send Command</button></p></td>"
                         . "</tr></table></div>";
                }
            ?>
        </div>

    </body>
</html>