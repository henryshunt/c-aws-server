<meta charset="UTF-8">
<!DOCTYPE html>

<?php
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
        <link href="resources/styles/trebuchet.ttf" type="x-font-ttf">
        <link href="resources/styles/global.css" rel="stylesheet" type="text/css">
        <link href="resources/styles/flatpickr.css" rel="stylesheet" type="text/css">
        <script src="resources/scripts/global.js" type="text/javascript"></script>
        <script src="resources/scripts/jquery.js" type="text/javascript"></script>
        <script src="resources/scripts/moment.js" type="text/javascript"></script>
        <script src="resources/scripts/moment-tz.js" type="text/javascript"></script>
        <script src="resources/scripts/flatpickr.js" type="text/javascript"></script>
        <script src="resources/scripts/page-statistics.js" type="text/javascript"></script>

        <script>
            const awsTimeZone
                = "<?php echo $config->get_aws_time_zone(); ?>";

            var isLoading = true;
            var datePicker = null;
            var requestedTime = null;
            var updaterTimeout = null;

            $(document).ready(function() {
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
                        <a class="menu_item" id="ami" href="statistics.php">Statistics</a>
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
                                    <td style="width: 33.3%"><p><b>MIN: </b><span id="item_WDir_Min"></span></p></td>
                                    <td style="width: 33.3%"><p><b>MAX: </b><span id="item_WDir_Max"></span></p></td>
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

            <div class="group" style="margin-bottom: 0px">
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