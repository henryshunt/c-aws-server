<!DOCTYPE html>
<?php include_once("res/php-config.php"); ?>

<html>
    <head>
        <title>C-AWS <?php echo explode(",", $aws_location)[0]; ?> [Remote]</title>
        <link href="res/css-trebuchet.ttf" type="x-font-ttf">
        <link href="res/css-global.css" rel="stylesheet" type="text/css">
        <link href="res/css-climate.css" rel="stylesheet" type="text/css">
        <script src="res/js-global.js" type="text/javascript"></script>
        <script src="res/js-jquery.js" type="text/javascript"></script>
        <script src="res/js-moment.js" type="text/javascript"></script>
        <script src="res/js-moment-tz.js" type="text/javascript"></script>

        <script>
            const awsTimeZone = "<?php echo $aws_time_zone; ?>";

            var isLoading = true;
            var requestedTime = null;

            $(document).ready(function() {
                setInterval(function() {
                    displayLocalTime();
                }, 250);

                updateData(true);
            });

            function updateData(setTime) {
                isLoading = true;
                getAndProcessData(setTime);
            }

            function getAndProcessData(setTime) {
                if (setTime == true) {
                    requestedTime = moment().utc().millisecond(0).second(0);
                }

                var url = "data/climate.php?time=" + requestedTime.format(
                    "YYYY-MM-DD[T]HH-mm-00");

                $.ajax({
                    dataType: "json", url: url,
                    success: function (data) { processData(data); },

                    error: function() {
                        var localTime = moment(requestedTime).tz(awsTimeZone);
                        document.getElementById("scroller_time").innerHTML
                            = localTime.format("YYYY");

                        document.getElementById("item_AirT_Avg_Year").innerHTML = "no data";
                        document.getElementById("item_AirT_Min_Year").innerHTML = "no data";
                        document.getElementById("item_AirT_Max_Year").innerHTML = "no data";
                        
                        var table = document.getElementById("climate_months_a");
                        for (var i = 1, row; row = table.rows[i]; i++) {
                            for (var j = 1, col; col = row.cells[j]; j++) {
                                col.innerHTML = "no data";
                            }  
                        }

                        table = document.getElementById("climate_months_b");
                        for (var i = 1, row; row = table.rows[i]; i++) {
                            for (var j = 1, col; col = row.cells[j]; j++) {
                                col.innerHTML = "no data";
                            }  
                        }; isLoading = false;
                    }
                });
            }

            function processData(data) {
                var localTime = moment(requestedTime).tz(awsTimeZone);
                document.getElementById("scroller_time").innerHTML
                    = localTime.format("YYYY");

                displayValue(data["AirT_Avg_Year"], "item_AirT_Avg_Year", "°C", 1);
                displayValue(data["AirT_Min_Year"], "item_AirT_Min_Year", "°C", 1);
                displayValue(data["AirT_Max_Year"], "item_AirT_Max_Year", "°C", 1);

                displayMonths(data["AirT_Avg_Months"], "item_AirT_Avg_Months", 1);
                displayMonths(data["AirT_Min_Months"], "item_AirT_Min_Months", 1);
                displayMonths(data["AirT_Max_Months"], "item_AirT_Max_Months", 1);
                displayMonths(data["RelH_Avg_Months"], "item_RelH_Avg_Months", 1);
                displayMonths(data["WSpd_Avg_Months"], "item_WSpd_Avg_Months", 1);
                displayMonths(data["WSpd_Max_Months"], "item_WSpd_Max_Months", 1);
                displayMonths(data["WDir_Avg_Months"], "item_WDir_Avg_Months", -1);
                displayMonths(data["WGst_Avg_Months"], "item_WGst_Avg_Months", 1);
                displayMonths(data["WGst_Max_Months"], "item_WGst_Max_Months", 1);
                displayMonths(data["SunD_Ttl_Months"], "item_SunD_Ttl_Months", 1);
                displayMonths(data["Rain_Ttl_Months"], "item_Rain_Ttl_Months", 1);
                displayMonths(data["MSLP_Avg_Months"], "item_MSLP_Avg_Months", 1);
                displayMonths(data["ST10_Avg_Months"], "item_ST10_Avg_Months", 1);
                displayMonths(data["ST30_Avg_Months"], "item_ST30_Avg_Months", 1);
                displayMonths(data["ST00_Avg_Months"], "item_ST00_Avg_Months", 1);
                isLoading = false;
            }

            function displayMonths(data, row, precision) {
                for (var i = 1, col; 
                    col = document.getElementById(row).cells[i]; i++) {

                    if (data[i] != null) {
                        if (precision == -1) {
                            var formatted = data[i]
                                .toFixed(0) + " (" + degreesToCompass(data[i]) + ")"
                            col.innerHTML = formatted; 
                        } else { col.innerHTML = data[i].toFixed(precision); }
                    } else { col.innerHTML = "no data";}
                }
            }

            function scrollerLeft() {
                if (isLoading == false) {
                    requestedTime.subtract(1, "years");
                    updateData(false);
                }
            }
            
            function scrollerRight() {
                if (isLoading == false) {
                    requestedTime.add(1, "years");
                    updateData(false);
                }
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
                        <a class="menu_item" href=".">Now</a>
                        <a class="menu_item" href="statistics.php">Statistics</a>
                        <a class="menu_item" href="camera.php">Camera</a>
                        <span>|</span>
    
                        <span>Graph:</span>
                        <a class="menu_item" href="graph-day.php">Day</a>
                        <a class="menu_item" href="graph-year.php">Year</a>
    
                        <span>|</span>
                        <a class="menu_item" id="ami" href="climate.php">Climate</a>
                        <a class="menu_item" href="about.php">About</a>
                    </div>

                    <span>Accessing <b>REMOTE</b> Data Stores</span>
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

                <div><p id="scroller_time"></p></div>

                <div id="scroller_right" class="scroller_button" onclick="scrollerRight()">
                    <svg width="20" height="20" viewBox="0 0 512 512">
                        <polygon points="160,128.4 192.3,96 352,256 352,256 352,256 192.3,416 160,383.6 287.3,256"/>
                    </svg>
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

                <table id="climate_months_a" class="climate_table">
                    <thead>
                        <tr>
                            <td></td><td>Jan</td><td>Feb</td><td>Mar</td><td>Apr</td>
                            <td>May</td><td>Jun</td><td>Jul</td><td>Aug</td><td>Sep</td>
                            <td>Oct</td><td>Nov</td><td>Dec</td>
                        </tr>
                    </thead>

                    <tbody>
                        <tr id="item_AirT_Avg_Months">
                            <td>Air Temperature Average (°C)</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr id="item_AirT_Min_Months">
                            <td>Air Temperature Minimum (°C)</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr id="item_AirT_Max_Months">
                            <td>Air Temperature Maximum (°C)</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                    </tbody>
                </table>

                <table id="climate_months_b" class="climate_table">
                    <tbody>
                        <tr id="item_RelH_Avg_Months">
                            <td>Relative Humidity Average (%)</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr id="item_WSpd_Avg_Months">
                            <td>Wind Speed Average (mph)</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr id="item_WSpd_Max_Months">
                            <td>Wind Speed Maximum (mph)</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr id="item_WDir_Avg_Months">
                            <td>Wind Direction Average (°)</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr id="item_WGst_Avg_Months">
                            <td>Wind Gust Average (mph)</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr id="item_WGst_Max_Months">
                            <td>Wind Gust Maximum (mph)</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr id="item_SunD_Ttl_Months">
                            <td>Sunshine Duration Total (hrs)</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr id="item_Rain_Ttl_Months">
                            <td>Rainfall Total (mm)</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr id="item_MSLP_Avg_Months">
                            <td>Mean Sea Level Pressure Avg (hPa)</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr id="item_ST10_Avg_Months">
                            <td>Soil Temp 10CM Average (°C)</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr id="item_ST30_Avg_Months">
                            <td>Soil Temp 30CM Average (°C)</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr id="item_ST00_Avg_Months">
                            <td>Soil Temp 1M Average (°C)</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="footer">
            <div id="footer_items">
                <p class="footer_label">Local Time:</p>
                <p id="item_local_time" class="footer_value" style="width: 210px"></p>
            </div>
        </div>

    </body>
</html>