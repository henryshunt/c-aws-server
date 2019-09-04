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
        <link href="resources/styles/flatpickr.css" rel="stylesheet" type="text/css">
        <script src="resources/scripts/flatpickr.js" type="text/javascript"></script>
        <link href="resources/styles/groups.css" rel="stylesheet" type="text/css">
        <link href="resources/styles/graphing.css" rel="stylesheet" type="text/css">
        <script src="resources/scripts/page-station.js" type="text/javascript"></script>

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

                    <a href="climate.php">Climate</a>
                    <a class="ami" href="station.php">Station</a>
                    
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

            <div class="group g_last">
                <div class="group_header">
                    <p class="group_title">Air Temperature</p>
                    <span class="group_key">°C (<span>Enclosure Temp</span>, <span>CPU Temp</span>)</span>
                </div>
    
                <table class="field_table">
                    <tr>
                        <td><p class="field_label">Idle CPU Temperature:</p></td>
                        <td><p id="item_CPUT" class="field_value"></p></td>
                    </tr>
                    <tr>
                        <td><p class="field_label">Enclosure Temperature:</p></td>
                        <td><p id="item_EncT" class="field_value"></p></td>
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
                        if ($static_info[0] != NULL && $static_info[0] != "null")
                        {
                            $startup_time = $static_info[0];
                            $startup_time = date_create_from_format("Y-m-d H:i:s", $startup_time);
                            $startup_time->setTimezone(
                                new DateTimeZone($config->get_aws_time_zone()));
                            $startup_time = $startup_time->format("d/m/Y \a\\t H:i");
                        } else $startup_time = "No Data";

                        // Internal drive space
                        if ($static_info[1] != NULL && $static_info[1] != "null")
                        {
                            $internal_drive_space = $static_info[1];
                            $internal_drive_space = round(floatval($internal_drive_space), 2) . " GB";
                        } else $internal_drive_space = "No Data";

                        // Camera drive space
                        if ($static_info[2] != NULL && $static_info[2] != "null")
                        {
                            $camera_drive_space = $static_info[2];
                            $camera_drive_space = round(floatval($camera_drive_space), 2) . " GB";
                        } else $camera_drive_space = "No Data";
                    }

                    echo "<div class=\"group g_last\" style=\"margin-top: 15px\">"
                         . "<div class=\"group_header\">"
                         . "<p class=\"group_title\">Station Computer</p></div>"
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