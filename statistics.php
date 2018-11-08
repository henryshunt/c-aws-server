<!DOCTYPE html>
<?php include_once("res/php-config.php"); ?>

<html>
    <head>
        <title>C-AWS <?php echo explode(",", $aws_location)[0]; ?> [Remote]</title>
        <link href="res/css-trebuchet.ttf" type="x-font-ttf">
        <link href="res/css-global.css" rel="stylesheet" type="text/css">
        <link href="res/css-flatpickr.css" rel="stylesheet" type="text/css">
        <script src="res/js-global.js" type="text/javascript"></script>
        <script src="res/js-jquery.js" type="text/javascript"></script>
        <script src="res/js-moment.js" type="text/javascript"></script>
        <script src="res/js-moment-tz.js" type="text/javascript"></script>
        <script src="res/js-flatpickr.js" type="text/javascript"></script>

        <script>
            const awsTimeZone = "<?php echo $aws_time_zone; ?>";

            var isLoading = true;
            var datePicker = null;
            var requestedTime = null;
            var updaterTimeout = null;
            var countTimeout = null;
            var timeToUpdate = 0;

            $(document).ready(function() {
                setInterval(function() {
                    displayLocalTime();
                }, 250);

                updateData(true, false);
            });

            function updateData(restartTimers, absolute) {
                isLoading = true;
                clearTimeout(updaterTimeout);
                clearTimeout(countTimeout);

                if (restartTimers == true) {
                    document.getElementById("item_update").innerHTML = "0";
                } else { document.getElementById("item_update").innerHTML = ""; }
                timeToUpdate = 60;

                if (restartTimers == true) {
                    updaterTimeout = setInterval(function() {
                        updateData(true, false);
                    }, 60000);
                        
                    countTimeout = setInterval(function() {
                        document.getElementById("item_update").innerHTML
                            = --timeToUpdate;
                    }, 1000);
                }

                getAndProcessData(restartTimers, absolute);
            }

            function getAndProcessData(setTime, absolute) {
                if (setTime == true) {
                    requestedTime = moment().utc().millisecond(0).second(0);
                }

                var url = "data/statistics.php?time=" + requestedTime.format(
                    "YYYY-MM-DD[T]HH-mm-00");
                if (absolute == true) { url += "&abs=1"; }

                $.ajax({
                    dataType: "json", url: url,
                    success: function (data) { processData(data, setTime); },

                    error: function() {
                        var localTime = moment(requestedTime).tz(awsTimeZone);
                        if (setTime == true) {
                            document.getElementById("item_data_time").innerHTML
                                = localTime.format("HH:mm");
                        } else { document.getElementById("item_data_time").innerHTML = ""; }
                        document.getElementById("scroller_time").innerHTML
                            = localTime.format("DD/MM/YYYY");

                        document.getElementById("item_AirT_Avg").innerHTML = "no data";
                        document.getElementById("item_AirT_Min").innerHTML = "no data";
                        document.getElementById("item_AirT_Max").innerHTML = "no data";
                        document.getElementById("item_RelH_Avg").innerHTML = "no data";
                        document.getElementById("item_RelH_Min").innerHTML = "no data";
                        document.getElementById("item_RelH_Max").innerHTML = "no data";
                        document.getElementById("item_DewP_Avg").innerHTML = "no data";
                        document.getElementById("item_DewP_Min").innerHTML = "no data";
                        document.getElementById("item_DewP_Max").innerHTML = "no data";
                        document.getElementById("item_WSpd_Avg").innerHTML = "no data";
                        document.getElementById("item_WSpd_Min").innerHTML = "no data";
                        document.getElementById("item_WSpd_Max").innerHTML = "no data";
                        document.getElementById("item_WDir_Avg").innerHTML = "no data";
                        document.getElementById("item_WDir_Min").innerHTML = "no data";
                        document.getElementById("item_WDir_Max").innerHTML = "no data";
                        document.getElementById("item_WGst_Avg").innerHTML = "no data";
                        document.getElementById("item_WGst_Min").innerHTML = "no data";
                        document.getElementById("item_WGst_Max").innerHTML = "no data";
                        document.getElementById("item_SunD_Ttl").innerHTML = "no data";
                        document.getElementById("item_Rain_Ttl").innerHTML = "no data";
                        document.getElementById("item_MSLP_Avg").innerHTML = "no data";
                        document.getElementById("item_MSLP_Min").innerHTML = "no data";
                        document.getElementById("item_MSLP_Max").innerHTML = "no data";
                        document.getElementById("item_ST10_Avg").innerHTML = "no data";
                        document.getElementById("item_ST10_Min").innerHTML = "no data";
                        document.getElementById("item_ST10_Max").innerHTML = "no data";
                        document.getElementById("item_ST30_Avg").innerHTML = "no data";
                        document.getElementById("item_ST30_Min").innerHTML = "no data";
                        document.getElementById("item_ST30_Max").innerHTML = "no data";
                        document.getElementById("item_ST00_Avg").innerHTML = "no data";
                        document.getElementById("item_ST00_Min").innerHTML = "no data";
                        document.getElementById("item_ST00_Max").innerHTML = "no data";
                        isLoading = false;
                    }
                });
            }

            function processData(data, showTime) {
                var utc = moment.utc(data["Time"], "YYYY-MM-DD HH:mm:ss");
                var localTime = moment(utc).tz(awsTimeZone);

                if (showTime == true) {
                    document.getElementById("item_data_time").innerHTML
                        = localTime.format("HH:mm");
                } else { document.getElementById("item_data_time").innerHTML = ""; }
                document.getElementById("scroller_time").innerHTML
                    = localTime.format("DD/MM/YYYY");
                requestedTime = moment(utc);

                displayValue(data["AirT_Avg"], "item_AirT_Avg", "°C", 1);
                displayValue(data["AirT_Min"], "item_AirT_Min", "°C", 1);
                displayValue(data["AirT_Max"], "item_AirT_Max", "°C", 1);
                displayValue(data["RelH_Avg"], "item_RelH_Avg", "%", 1);
                displayValue(data["RelH_Min"], "item_RelH_Min", "%", 1);
                displayValue(data["RelH_Max"], "item_RelH_Max", "%", 1);
                displayValue(data["DewP_Avg"], "item_DewP_Avg", "°C", 1);
                displayValue(data["DewP_Min"], "item_DewP_Min", "°C", 1);
                displayValue(data["DewP_Max"], "item_DewP_Max", "°C", 1);
                displayValue(data["WSpd_Avg"], "item_WSpd_Avg", " mph", 1);
                displayValue(data["WSpd_Min"], "item_WSpd_Min", " mph", 1);
                displayValue(data["WSpd_Max"], "item_WSpd_Max", " mph", 1);

                if (data["WDir_Avg"] != null) {
                    var formatted = data["WDir_Avg"]
                        .toFixed(0) + "° (" + degreesToCompass(data["WDir_Avg"]) + ")"
                    document.getElementById("item_WDir_Avg").innerHTML = formatted;
                } else { document.getElementById("item_WDir_Avg").innerHTML = "no data"; }

                if (data["WDir_Min"] != null) {
                    var formatted = data["WDir_Min"]
                        .toFixed(0) + "° (" + degreesToCompass(data["WDir_Min"]) + ")"
                    document.getElementById("item_WDir_Min").innerHTML = formatted;
                } else { document.getElementById("item_WDir_Min").innerHTML = "no data"; }

                if (data["WDir_Max"] != null) {
                    var formatted = data["WDir_Max"]
                        .toFixed(0) + "° (" + degreesToCompass(data["WDir_Max"]) + ")"
                    document.getElementById("item_WDir_Max").innerHTML = formatted;
                } else { document.getElementById("item_WDir_Max").innerHTML = "no data"; }

                displayValue(data["WGst_Avg"], "item_WGst_Avg", " mph", 1);
                displayValue(data["WGst_Min"], "item_WGst_Min", " mph", 1);
                displayValue(data["WGst_Max"], "item_WGst_Max", " mph", 1);

                if (data["SunD_Ttl"] != null) {
                    var formatted = moment.utc(data["SunD_Ttl"] * 1000).format("HH:mm:ss");
                    var formatted2 = data["SunD_Ttl"] / 60 / 60;
                    document.getElementById("item_SunD_Ttl").innerHTML
                        = formatted + " (" + formatted2.toFixed(1) + " hrs)";
                } else { document.getElementById("item_SunD_Ttl").innerHTML = "no data"; }

                displayValue(data["Rain_Ttl"], "item_Rain_Ttl", " mm", 2);
                displayValue(data["MSLP_Avg"], "item_MSLP_Avg", " hPa", 1);
                displayValue(data["MSLP_Min"], "item_MSLP_Min", " hPa", 1);
                displayValue(data["MSLP_Max"], "item_MSLP_Max", " hPa", 1);
                displayValue(data["ST10_Avg"], "item_ST10_Avg", "°C", 1);
                displayValue(data["ST10_Min"], "item_ST10_Min", "°C", 1);
                displayValue(data["ST10_Max"], "item_ST10_Max", "°C", 1);
                displayValue(data["ST30_Avg"], "item_ST30_Avg", "°C", 1);
                displayValue(data["ST30_Min"], "item_ST30_Min", "°C", 1);
                displayValue(data["ST30_Max"], "item_ST30_Max", "°C", 1);
                displayValue(data["ST00_Avg"], "item_ST00_Avg", "°C", 1);
                displayValue(data["ST00_Min"], "item_ST00_Min", "°C", 1);
                displayValue(data["ST00_Max"], "item_ST00_Max", "°C", 1);
                isLoading = false;
            }

            function scrollerLeft() {
                if (datePicker != null) { datePicker.close(); }

                if (isLoading == false) {
                    requestedTime.subtract(1, "days");
                    scrollerChange();
                }
            }
            
            function scrollerRight() {
                if (datePicker != null) { datePicker.close(); }
                
                if (isLoading == false) {
                    requestedTime.add(1, "days");
                    scrollerChange();
                }
            }

            function scrollerChange() {
                var utc = moment().utc().millisecond(0).second(0);
                var localUtc = moment(utc).tz(awsTimeZone);
                var localReq = moment(requestedTime).tz(awsTimeZone);

                if (localUtc.format("DD/MM/YYYY") == localReq.format("DD/MM/YYYY")) {
                    updateData(true, true)
                } else { updateData(false, true); }
            }

            function openPicker() {
                if (datePicker == null) {
                    if (requestedTime != null && datePicker == null && isLoading == false) {
                        var localTime = moment(requestedTime).tz(awsTimeZone);
                        var initTime = new Date(localTime.get("year"), localTime.get("month"),
                            localTime.get("date"));

                        datePicker = flatpickr("#scroller_time", {
                            defaultDate: initTime, disableMobile: true,
                            onClose: function() { datePicker.destroy(); datePicker = null; }
                        });
                        
                        datePicker.open();
                    }
                } else { datePicker.close(); }
            }

            function pickerSubmit() {
                var selTime = datePicker.selectedDates[0];
                selTime = moment.utc({
                    year: selTime.getUTCFullYear(), month: selTime.getUTCMonth(), day: 
                    selTime.getUTCDate(), hour: selTime.getUTCHours(), minute:
                    0, second: 0 });

                var localSel = moment(selTime).tz(awsTimeZone);
                var localReq = moment(requestedTime).tz(awsTimeZone);

                if (localSel.format("DD/MM/YYYY") != localReq.format("DD/MM/YYYY")) {
                    requestedTime = moment(selTime);
                    scrollerChange();
                }; datePicker.close(); 
            }

            function pickerCancel() {
                datePicker.close();
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
                        <a class="menu_item" id="ami" href="statistics.php">Statistics</a>
                        <a class="menu_item" href="camera.php">Camera</a>
                        <span>|</span>
    
                        <span>Graph:</span>
                        <a class="menu_item" href="graph-day.php">Day</a>
                        <a class="menu_item" href="graph-year.php">Year</a>
    
                        <span>|</span>
                        <a class="menu_item" href="climate.php">Climate</a>
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