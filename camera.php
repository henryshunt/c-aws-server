<meta charset="UTF-8">
<!DOCTYPE html>
<?php include_once("data/config.php"); ?>

<html>
    <head>
        <title>C-AWS <?php echo $aws_location; ?> [Remote]</title>
        <link href="resources/styles/trebuchet.ttf" type="x-font-ttf">
        <link href="resources/styles/global.css" rel="stylesheet" type="text/css">
        <link href="resources/styles/flatpickr.css" rel="stylesheet" type="text/css">
        <script src="resources/scripts/global.js" type="text/javascript"></script>
        <script src="resources/scripts/jquery.js" type="text/javascript"></script>
        <script src="resources/scripts/moment.js" type="text/javascript"></script>
        <script src="resources/scripts/moment-tz.js" type="text/javascript"></script>
        <script src="resources/scripts/flatpickr.js" type="text/javascript"></script>

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
                timeToUpdate = 300;

                if (restartTimers == true) {
                    updaterTimeout = setInterval(function() {
                        updateData(true, false);
                    }, 300000);
                        
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
                    requestedMinute = requestedTime.get("minute").toString();

                    while (!requestedMinute.endsWith("0") && !requestedMinute.endsWith("5")) {
                        requestedTime.subtract(1, "minute");
                        requestedMinute = requestedTime.get("minute").toString();
                    }
                }

                var url = "data/camera.php?time=" + requestedTime.format(
                    "YYYY-MM-DD[T]HH-mm-00");
                if (absolute == true) { url += "&abs=1"; }

                $.ajax({
                    dataType: "json", url: url,
                    success: function (data) { processData(data); },

                    error: function() {
                        var localTime = moment(requestedTime).tz(awsTimeZone);
                        document.getElementById("item_data_time").innerHTML
                            = localTime.format("HH:mm");
                        document.getElementById("scroller_time").innerHTML
                            = localTime.format("DD/MM/YYYY [at] HH:mm");
                        
                        document.getElementById("item_SRis").innerHTML = "no data";
                        document.getElementById("item_SSet").innerHTML = "no data";
                        document.getElementById("item_Noon").innerHTML = "no data";
                        document.getElementById("item_CImg").src
                            = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7";
                        isLoading = false;
                    }
                });
            }

            function processData(data) {
                var utc = moment.utc(data["Time"], "YYYY-MM-DD HH:mm:ss");
                var localTime = moment(utc).tz(awsTimeZone);

                document.getElementById("item_data_time").innerHTML
                    = localTime.format("HH:mm");
                document.getElementById("scroller_time").innerHTML
                    = localTime.format("DD/MM/YYYY [at] HH:mm");
                requestedTime = moment(utc);

                if (data["SRis"] != null) {
                    var utc = moment.utc(data["SRis"], "YYYY-MM-DD HH:mm:ss");
                    var formatted = utc.tz(awsTimeZone).format("HH:mm");
                    document.getElementById("item_SRis").innerHTML = formatted;
                } else { document.getElementById("item_SRis").innerHTML = "no data"; }

                if (data["SSet"] != null) {
                    var utc = moment.utc(data["SSet"], "YYYY-MM-DD HH:mm:ss");
                    var formatted = utc.tz(awsTimeZone).format("HH:mm");
                    document.getElementById("item_SSet").innerHTML = formatted;
                } else { document.getElementById("item_SSet").innerHTML = "no data"; }

                if (data["Noon"] != null) {
                    var utc = moment.utc(data["Noon"], "YYYY-MM-DD HH:mm:ss");
                    var formatted = utc.tz(awsTimeZone).format("HH:mm");
                    document.getElementById("item_Noon").innerHTML = formatted;
                } else { document.getElementById("item_Noon").innerHTML = "no data"; }

                if (data["CImg"] != null) {
                    document.getElementById("item_CImg").src = data["CImg"];
                } else {
                    document.getElementById("item_CImg")
                        .src = "resources/images/no-camera.png";
                }
                isLoading = false;
            }
        
            function scrollerLeft() {
                if (datePicker != null) { datePicker.close(); }

                if (isLoading == false) {
                    requestedTime.subtract(5, "minutes");
                    scrollerChange();
                }
            }

            function scrollerRight() {
                if (datePicker != null) { datePicker.close(); }

                if (isLoading == false) {
                    requestedTime.add(5, "minutes");
                    scrollerChange();
                }
            }

            function scrollerChange() {
                document.getElementById("item_CImg").src
                    = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7";
                    
                var utc = moment().utc().millisecond(0).second(0);
                utcMinute = utc.get("minute").toString();

                while (!utcMinute.endsWith("0") && !utcMinute.endsWith("5")) {
                    utc.subtract(1, "minute");
                    utcMinute = utc.get("minute").toString();
                }

                if (utc.toString() == requestedTime.toString()) {
                    updateData(true, true)
                } else { updateData(false, true); }
            }

            function openPicker() {
                if (datePicker == null) {
                    if (requestedTime != null && isLoading == false) {
                        var localTime = moment(requestedTime).tz(awsTimeZone);
                        var initTime = new Date(localTime.get("year"), localTime.get("month"),
                            localTime.get("date"), localTime.get("hour"), localTime.get("minute"), 0);

                        datePicker = flatpickr("#scroller_time", {
                            enableTime: true, time_24hr: true, defaultDate: initTime, disableMobile: true,
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
                    selTime.getUTCMinutes(), second: 0 });

                utcMinute = selTime.get("minute").toString();
                while (!utcMinute.endsWith("0") && !utcMinute.endsWith("5")) {
                    if (utcMinute.endsWith("3") || utcMinute.endsWith("4") ||
                        utcMinute.endsWith("8") || utcMinute.endsWith("9")) {
                            selTime.add(1, "minute");
                    } else { selTime.subtract(1, "minute"); }
                    
                    utcMinute = selTime.get("minute").toString();
                }

                if (selTime.toString() != requestedTime.toString()) {
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
                        <a class="menu_item" href="statistics.php">Statistics</a>
                        <a class="menu_item" id="ami" href="camera.php">Camera</a>
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
                    <p class="group_title">Astronomical Data</p>
                </div>

                <table class="field_table">
                    <tr>
                        <td><p class="field_label">Today's Sunrise:</p></td>
                        <td><p id="item_SRis" class="field_value"></p></td>
                    </tr>
                    <tr>
                        <td><p class="field_label">Today's Sunset:</p></td>
                        <td><p id="item_SSet" class="field_value"></p></td>
                    </tr>
                    <tr>
                        <td><p class="field_label" style="margin-top: 10px">Today's Solar Noon:</p></td>
                        <td><p id="item_Noon" class="field_value" style="margin-top: 10px"></p></td>
                    </tr>
                </table>
            </div>

            <div id="img_container">
                <p id="img_loading">Loading Image...</p>
                <img id="item_CImg"
                    src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">
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
                    <p id="item_update" class="footer_value" style="width: 45px"></p>
                </div>
            </div>
        </div>

    </body>
</html>