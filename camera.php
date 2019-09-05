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
        <link href="resources/styles/grouping.css" rel="stylesheet" type="text/css">
        <link href="resources/styles/page-camera.css" rel="stylesheet" type="text/css">
        <script src="resources/scripts/page-camera.js" type="text/javascript"></script>

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
                    <a class="ami" href="camera.php">Camera</a>
                    <span>|</span>
                    <span>Graph:</span>
                    <a href="graph-day.php">Day</a>
                    <a href="graph-year.php">Year</a>
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

            <div class="group">
                <div class="group_header">
                    <p class="group_title">Astronomical Data</p>
                </div>

                <table class="field_table">
                    <tr>
                        <td><p class="field_label">Time of Sunrise:</p></td>
                        <td><p id="item_SRis" class="field_value"></p></td>
                    </tr>
                    <tr>
                        <td><p class="field_label">Time of Sunset:</p></td>
                        <td><p id="item_SSet" class="field_value"></p></td>
                    </tr>
                    <tr>
                        <td><p class="field_label" style="margin-top: 10px">Time of Solar Noon:</p></td>
                        <td><p id="item_Noon" class="field_value" style="margin-top: 10px"></p></td>
                    </tr>
                </table>
            </div>

            <div class="image_group">
                <p>Loading Image...</p>
                <img id="item_CImg"
                    src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">
            </div>
        </div>

    </body>
</html>