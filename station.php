<!DOCTYPE html>
<?php include_once("res/php-config.php"); ?>

<html>
    <head>
        <title>C-AWS <?php echo $aws_location; ?> [Remote]</title>
        <link href="res/css-trebuchet.ttf" type="x-font-ttf">
        <link href="res/css-global.css" rel="stylesheet" type="text/css">
        <link href="res/css-chartist.css" rel="stylesheet" type="text/css">
        <link href="res/css-graphs.css" rel="stylesheet" type="text/css">
        <script src="res/js-global.js" type="text/javascript"></script>
        <script src="res/js-jquery.js" type="text/javascript"></script>
        <script src="res/js-chartist.js" type="text/javascript"></script>
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
                    url: "data/station.php?time=" + requestedTime.format(
                        "YYYY-MM-DD[T]HH-mm-00"),

                    success: function (data) { processData(data); },
                    error: function() {
                        document.getElementById("item_data_time").innerHTML
                            = moment(requestedTime).tz(awsTimeZone).format("HH:mm");

                        document.getElementById("item_EncT").innerHTML = "no data";
                        document.getElementById("item_CPUT").innerHTML = "no data";
                    }
                });

                loadGraphData();
            }

            function processData(data) {
                var utc = moment.utc(data["Time"], "YYYY-MM-DD HH:mm:ss");
                document.getElementById("item_data_time").innerHTML
                    = moment(utc).tz(awsTimeZone).format("HH:mm");
                requestedTime = moment(utc);

                displayValue(data["EncT"], "item_EncT", "°C", 1);
                displayValue(data["CPUT"], "item_CPUT", "°C", 1);
            }

            function loadGraphData() {
                var url = "data/graph-station.php?time=" + requestedTime.format(
                    "YYYY-MM-DD[T]HH-mm-00") + "&fields=EncT,CPUT";
                
                var xEnd = moment(requestedTime).tz(awsTimeZone).unix();
                var xStart = moment(requestedTime).subtract(6, "hour").tz(awsTimeZone).unix();

                $.getJSON(url, function(response) {
                    var options = graph.options;
                    options.axisX.low = xStart; options.axisX.high = xEnd;
                    graph.update({ series: response }, options);

                }).fail(function() {
                    var options = graph.options;
                    delete options.axisX.low; delete options.axisX.high;
                    graph.update({ series: null }, options);
                });
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
                        <a class="menu_item" href="climate.php">Climate</a>
                        <a class="menu_item" id="ami" href="station.php">Station</a>
                    </div>

                    <span>Accessing <b>REMOTE</b> Data Stores</span>
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
                        <td><p class="field_label">Location:</p></td>
                        <td><p class="field_value"><?php echo $aws_location; ?></p></td>
                    </tr>
                    <tr>
                        <td><p class="field_label">Time Zone Name:</p></td>
                        <td><p class="field_value"><?php echo $aws_time_zone; ?></p></td>
                    </tr>
                    <tr>
                        <td><p class="field_label" style="margin-top: 10px">Elevation:</p></td>
                        <td><p class="field_value" style="margin-top: 10px"><?php echo $aws_elevation; ?> m</p></td>
                    </tr>
                    <tr>
                        <td><p class="field_label">Latitude:</p></td>
                        <td><p class="field_value"><?php echo $aws_latitude; ?></p></td>
                    </tr>
                    <tr>
                        <td><p class="field_label">Longitude:</p></td>
                        <td><p class="field_value"><?php echo $aws_longitude; ?></p></td>
                    </tr>
                    <tr>
                        <td><p class="field_label" style="margin-top: 10px">System Version:</p></td>
                        <td><p class="field_value" style="margin-top: 10px">5D.0.2 (Dec 2018)</p></td>
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