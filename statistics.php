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
        <script src="resources/scripts/page-statistics.js" type="text/javascript"></script>

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
                    <a href=".">Now</a>
                    <a class="ami" href="statistics.php">Statistics</a>
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
                        <td style="width: 45%">
                            <p class="field_label">Air Temperature:</p>
                        </td>
                        <td>
                            <table style="width: 100%; border-collapse: collapse">
                                <tr>
                                    <td style="width: 33.3%"><p><b>AVG: </b><span id="item_AirT_Avg"></span></p></td>
                                    <td style="width: 33.3%"><p><b>MIN: </b><span id="item_AirT_Min"></span></p></td>
                                    <td style="width: 33.3%"><p><b>MAX: </b><span id="item_AirT_Max"></span></p></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="group">
                <div class="group_header">
                    <p class="group_title">Moisture</p>
                </div>

                <table class="field_table">
                    <tr>
                        <td style="width: 45%">
                            <p class="field_label">Relative Humidity:</p>
                        </td>
                        <td>
                            <table style="width: 100%; border-collapse: collapse">
                                <tr>
                                    <td style="width: 33.3%"><p><b>AVG: </b><span id="item_RelH_Avg"></span></p></td>
                                    <td style="width: 33.3%"><p><b>MIN: </b><span id="item_RelH_Min"></span></p></td>
                                    <td style="width: 33.3%"><p><b>MAX: </b><span id="item_RelH_Max"></span></p></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 45%">
                            <p class="field_label">Dew Point:</p>
                        </td>
                        <td>
                            <table style="width: 100%; border-collapse: collapse">
                                <tr>
                                    <td style="width: 33.3%"><p><b>AVG: </b><span id="item_DewP_Avg"></span></p></td>
                                    <td style="width: 33.3%"><p><b>MIN: </b><span id="item_DewP_Min"></span></p></td>
                                    <td style="width: 33.3%"><p><b>MAX: </b><span id="item_DewP_Max"></span></p></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="group">
                <div class="group_header">
                    <p class="group_title">Wind</p>
                </div>

                <table class="field_table">
                    <tr>
                        <td style="width: 45%">
                            <p class="field_label">Wind Speed:</p>
                        </td>
                        <td>
                            <table style="width: 100%; border-collapse: collapse">
                                <tr>
                                    <td style="width: 33.3%"><p><b>AVG: </b><span id="item_WSpd_Avg"></span></p></td>
                                    <td style="width: 33.3%"><p><b>MIN: </b><span id="item_WSpd_Min"></span></p></td>
                                    <td style="width: 33.3%"><p><b>MAX: </b><span id="item_WSpd_Max"></span></p></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 45%">
                            <p class="field_label">Wind Direction:</p>
                        </td>
                        <td>
                            <table style="width: 100%; border-collapse: collapse">
                                <tr>
                                    <td style="width: 33.3%"><p><b>AVG: </b><span id="item_WDir_Avg"></span></p></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 45%">
                            <p class="field_label">Wind Gust:</p>
                        </td>
                        <td>
                            <table style="width: 100%; border-collapse: collapse">
                                <tr>
                                    <td style="width: 33.3%"><p><b>AVG: </b><span id="item_WGst_Avg"></span></p></td>
                                    <td style="width: 33.3%"><p><b>MIN: </b><span id="item_WGst_Min"></span></p></td>
                                    <td style="width: 33.3%"><p><b>MAX: </b><span id="item_WGst_Max"></span></p></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="group">
                <div class="group_header">
                    <p class="group_title">Solar Radiation</p>
                </div>

                <table class="field_table">
                    <tr>
                        <td style="width: 45%">
                            <p class="field_label">Sunshine Duration:</p>
                        </td>
                        <td>
                            <table style="width: 100%; border-collapse: collapse">
                                <tr>
                                    <td style="width: 33.3%"><p><b>TTL: </b><span id="item_SunD_Ttl"></span></p></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="group">
                <div class="group_header">
                    <p class="group_title">Precipitation</p>
                </div>

                <table class="field_table">
                    <tr>
                        <td style="width: 45%">
                            <p class="field_label">Rainfall:</p>
                        </td>
                        <td>
                            <table style="width: 100%; border-collapse: collapse">
                                <tr>
                                    <td style="width: 33.3%"><p><b>TTL: </b><span id="item_Rain_Ttl"></span></p></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="group">
                <div class="group_header">
                    <p class="group_title">Barometric Pressure</p>
                </div>

                <table class="field_table">
                    <tr>
                        <td style="width: 45%">
                            <p class="field_label">Mean Sea Level Pressure:</p>
                        </td>
                        <td>
                            <table style="width: 100%; border-collapse: collapse">
                                <tr>
                                    <td style="width: 33.3%"><p><b>AVG: </b><span id="item_MSLP_Avg"></span></p></td>
                                    <td style="width: 33.3%"><p><b>MIN: </b><span id="item_MSLP_Min"></span></p></td>
                                    <td style="width: 33.3%"><p><b>MAX: </b><span id="item_MSLP_Max"></span></p></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="group g_last">
                <div class="group_header">
                    <p class="group_title">Soil Temperature</p>
                </div>

                <table class="field_table">
                    <tr>
                        <td style="width: 45%">
                            <p class="field_label">10 Centimetres Down:</p>
                        </td>
                        <td>
                            <table style="width: 100%; border-collapse: collapse">
                                <tr>
                                    <td style="width: 33.3%"><p><b>AVG: </b><span id="item_ST10_Avg"></span></p></td>
                                    <td style="width: 33.3%"><p><b>MIN: </b><span id="item_ST10_Min"></span></p></td>
                                    <td style="width: 33.3%"><p><b>MAX: </b><span id="item_ST10_Max"></span></p></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 45%">
                            <p class="field_label">30 Centimetres Down:</p>
                        </td>
                        <td>
                            <table style="width: 100%; border-collapse: collapse">
                                <tr>
                                    <td style="width: 33.3%"><p><b>AVG: </b><span id="item_ST30_Avg"></span></p></td>
                                    <td style="width: 33.3%"><p><b>MIN: </b><span id="item_ST30_Min"></span></p></td>
                                    <td style="width: 33.3%"><p><b>MAX: </b><span id="item_ST30_Max"></span></p></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 45%">
                            <p class="field_label">1 Metre Down:</p>
                        </td>
                        <td>
                            <table style="width: 100%; border-collapse: collapse">
                                <tr>
                                    <td style="width: 33.3%"><p><b>AVG: </b><span id="item_ST00_Avg"></span></p></td>
                                    <td style="width: 33.3%"><p><b>MIN: </b><span id="item_ST00_Min"></span></p></td>
                                    <td style="width: 33.3%"><p><b>MAX: </b><span id="item_ST00_Max"></span></p></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

    </body>
</html>