<!DOCTYPE html>
<?php include_once("res/php-config.php"); ?>

<html>
    <head>
        <title>C-AWS <?php echo $aws_location; ?> [Remote]</title>
        <link href="res/css-trebuchet.ttf" type="x-font-ttf">
        <link href="res/css-global.css" rel="stylesheet" type="text/css">
        <script src="res/js-global.js" type="text/javascript"></script>
        <script src="res/js-jquery.js" type="text/javascript"></script>
        <script src="res/js-moment.js" type="text/javascript"></script>
        <script src="res/js-moment-tz.js" type="text/javascript"></script>

        <script>
            const awsTimeZone = "<?php echo $aws_time_zone; ?>";

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

            function updateData() {
                clearTimeout(updaterTimeout);
                clearTimeout(countTimeout);

                document.getElementById("item_update").innerHTML = "0";
                timeToUpdate = 60;

                updaterTimeout = setInterval(function() {
                    updateData();
                }, 60000);
                    
                countTimeout = setInterval(function() {
                    document.getElementById("item_update").innerHTML
                        = --timeToUpdate;
                }, 1000);

                getAndProcessData();
            }

            function getAndProcessData() {
                requestedTime = moment().utc().millisecond(0).second(0);

                $.ajax({
                    dataType: "json",
                    url: "data/now.php?time=" + requestedTime.format(
                        "YYYY-MM-DD[T]HH-mm-00"),

                    success: function (data) { processData(data); },
                    error: function() {
                        document.getElementById("item_data_time").innerHTML
                            = moment(requestedTime).tz(awsTimeZone).format("HH:mm");
                            
                        document.getElementById("item_AirT").innerHTML = "no data";
                        document.getElementById("item_ExpT").innerHTML = "no data";
                        document.getElementById("item_RelH").innerHTML = "no data";
                        document.getElementById("item_DewP").innerHTML = "no data";
                        document.getElementById("item_WSpd").innerHTML = "no data";
                        document.getElementById("item_WDir").innerHTML = "no data";
                        document.getElementById("item_WGst").innerHTML = "no data";
                        document.getElementById("item_SunD").innerHTML = "no data";
                        document.getElementById("item_SunD_PHr").innerHTML = "no data";
                        document.getElementById("item_Rain").innerHTML = "no data";
                        document.getElementById("item_Rain_PHr").innerHTML = "no data";
                        document.getElementById("item_StaP").innerHTML = "no data";
                        document.getElementById("item_MSLP").innerHTML = "no data";
                        document.getElementById("item_StaP_PTH").innerHTML = "no data";
                        document.getElementById("item_ST10").innerHTML = "no data";
                        document.getElementById("item_ST30").innerHTML = "no data";
                        document.getElementById("item_ST00").innerHTML = "no data";
                    }
                });
            }

            function processData(data) {
                var utc = moment.utc(data["Time"], "YYYY-MM-DD HH:mm:ss");
                document.getElementById("item_data_time").innerHTML
                    = moment(utc).tz(awsTimeZone).format("HH:mm");
                requestedTime = moment(utc);

                displayValue(data["AirT"], "item_AirT", "°C", 1);
                displayValue(data["ExpT"], "item_ExpT", "°C", 1);
                displayValue(data["RelH"], "item_RelH", "%", 1);
                displayValue(data["DewP"], "item_DewP", "°C", 1);
                displayValue(data["WSpd"], "item_WSpd", " mph", 1);

                if (data["WDir"] != null) {
                    var formatted = data["WDir"]
                        .toFixed(0) + "° (" + degreesToCompass(data["WDir"]) + ")"
                    document.getElementById("item_WDir").innerHTML = formatted;
                } else { document.getElementById("item_WDir").innerHTML = "no data"; }

                displayValue(data["WGst"], "item_WGst", " mph", 1);
                displayValue(data["SunD"], "item_SunD", " sec", 0);

                if (data["SunD_PHr"] != null) {
                    var formatted = moment.utc(data["SunD_PHr"] * 1000).format("HH:mm:ss");
                    document.getElementById("item_SunD_PHr").innerHTML = formatted;
                } else { document.getElementById("item_SunD_PHr").innerHTML = "no data"; }

                displayValue(data["Rain"], "item_Rain", " mm", 2);
                displayValue(data["Rain_PHr"], "item_Rain_PHr", " mm", 2);
                displayValue(data["StaP"], "item_StaP", " hPa", 1);
                displayValue(data["MSLP"], "item_MSLP", " hPa", 1);
                
                if (data["StaP_PTH"] != null) {
                     var formatted = data["StaP_PTH"].toFixed(1) + " hpa";
                     if (data["StaP_PTH"] > 0) { formatted = "+" + formatted; }
                     document.getElementById("item_StaP_PTH").innerHTML = formatted;
                } else { document.getElementById("item_StaP_PTH").innerHTML = "no data"; }
                
                displayValue(data["ST10"], "item_ST10", "°C", 1);
                displayValue(data["ST30"], "item_ST30", "°C", 1);
                displayValue(data["ST00"], "item_ST00", "°C", 1);
            }
        </script>
    </head>

    <body>
        <div id="header">
            <div id="header_items">
                <h1 id="header_left"><?php echo $aws_location ?></h1>
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

                    <span>Accessing <b>REMOTE</b> Data Stores</span>
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
