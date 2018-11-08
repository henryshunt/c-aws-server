<!DOCTYPE html>
<?php include_once("res/php-config.php"); ?>

<html>
    <head>
        <title>C-AWS <?php echo explode(",", $aws_location)[0]; ?> [Remote]</title>
        <link href="res/css-trebuchet.ttf" type="x-font-ttf">
        <link href="res/css-global.css" rel="stylesheet" type="text/css">
        <link href="res/css-chartist.css" rel="stylesheet" type="text/css">
        <link href="res/css-graphs.css" rel="stylesheet" type="text/css">
        <link href="res/css-flatpickr.css" rel="stylesheet" type="text/css">
        <script src="res/js-global.js" type="text/javascript"></script>
        <script src="res/js-jquery.js" type="text/javascript"></script>
        <script src="res/js-chartist.js" type="text/javascript"></script>
        <script src="res/js-moment.js" type="text/javascript"></script>
        <script src="res/js-moment-tz.js" type="text/javascript"></script>
        <script src="res/js-flatpickr.js" type="text/javascript"></script>

        <script>
            const awsTimeZone = "<?php echo $aws_time_zone; ?>";

            var isLoading = true;
            var datePicker = null;
            var requestedTime = null;
            var graphs = { "temperature": null, "humidity": null, "wind": null,
                "direction": null, "sunshine": null, "rainfall": null,
                "pressure": null, "soil": null };
            var openGraphs = ["temperature"];
            var graphsLoaded = 0;
            var updaterTimeout = null;
            var countTimeout = null;
            var timeToUpdate = 0;

            $(document).ready(function() {
                setInterval(function() {
                    displayLocalTime();
                }, 250);

                graphs["temperature"] = setupGraph("temperature");
                graphs["humidity"] = setupGraph("humidity");
                graphs["wind"] = setupGraph("wind");
                graphs["direction"] = setupGraph("direction");
                graphs["sunshine"] = setupGraph("sunshine");
                graphs["rainfall"] = setupGraph("rainfall");
                graphs["pressure"] = setupGraph("pressure");
                graphs["soil"] = setupGraph("soil");

                updateData(true);
            });

            function setupGraph(graph) {
                var options = {
                    showPoint: false, lineSmooth: false, height: 300,

                    axisY: {
                        offset: 38,
                        labelInterpolationFnc: function(value) {
                            if (graph == "direction") { return value.toFixed(0); }
                            else if (graph == "sunshine") {
                                return (value / 60 / 60).toFixed(1);
                            }
                            else if (graph == "rainfall") { return value.toFixed(2) }
                            else { return value.toFixed(1); }
                        }
                    },

                    axisX: {
                        type: Chartist.FixedScaleAxis, divisor: 8, offset: 20,    
                        labelInterpolationFnc: function(value) {
                            return moment.unix(value).utc().tz(awsTimeZone).format("HH:mm");
                        }
                    }
                };

                if (graph == "direction") {
                    options.axisY.type = Chartist.FixedScaleAxis;
                    options.axisY.divisor = 8;
                    options.axisY.low = 0; options.axisY.high = 360;
                    options.showPoint = true; options.showLine = false;
                }

                return new Chartist.Line("#graph_" + graph, null, options);
            }

            function updateData(restartTimers) {
                isLoading = true;
                clearTimeout(updaterTimeout);
                clearTimeout(countTimeout);

                if (restartTimers == true) {
                    document.getElementById("item_update").innerHTML = "0";
                } else { document.getElementById("item_update").innerHTML = ""; }
                timeToUpdate = 300;

                if (restartTimers == false) {
                    document.getElementById("item_data_time").innerHTML = "";
                }

                if (restartTimers == true) {
                    updaterTimeout = setInterval(function() {
                        updateData(true);
                    }, 300000);
                        
                    countTimeout = setInterval(function() {
                        document.getElementById("item_update").innerHTML
                            = --timeToUpdate;
                    }, 1000);
                }

                getAndProcessData(restartTimers);
            }

            function getAndProcessData(setTime) {
                if (setTime == true) {
                    requestedTime = moment().utc().millisecond(0).second(0);
                }; graphsLoaded = 0;

                var localTime = moment(requestedTime).tz(awsTimeZone);
                if (setTime == true) {
                    document.getElementById("item_data_time").innerHTML
                        = localTime.format("HH:mm");
                }
                document.getElementById("scroller_time").innerHTML
                    = localTime.format("DD/MM/YYYY");
                
                if ($.inArray("temperature", openGraphs) != -1)
                { loadGraphData("temperature", "AirT,ExpT,DewP"); }
                if ($.inArray("humidity", openGraphs) != -1)
                { loadGraphData("humidity", "RelH"); }
                if ($.inArray("wind", openGraphs) != -1)
                { loadGraphData("wind", "WSpd,WGst"); }
                if ($.inArray("direction", openGraphs) != -1)
                { loadGraphData("direction", "WDir"); }
                if ($.inArray("sunshine", openGraphs) != -1)
                { loadGraphData("sunshine", "SunD"); }
                if ($.inArray("rainfall", openGraphs) != -1)
                { loadGraphData("rainfall", "Rain"); }
                if ($.inArray("pressure", openGraphs) != -1)
                { loadGraphData("pressure", "MSLP"); }
                if ($.inArray("soil", openGraphs) != -1)
                { loadGraphData("soil", "ST10,ST30,ST00"); }
            }

            function loadGraphData(graph, fields) {
                var url = "data/graph-day.php?time=" + requestedTime.format(
                    "YYYY-MM-DD[T]HH-mm-00") + "&fields=" + fields;
                
                var localTime = moment(requestedTime).tz(awsTimeZone);
                var xEnd = moment(localTime).add(1, "day").hour(0).minute(0).unix();
                var xStart = moment(localTime).hour(0).minute(0).unix();

                $.getJSON(url, function(response) {
                    var options = graphs[graph].options;
                    options.axisX.low = xStart; options.axisX.high = xEnd;
                    document.getElementById("graph_" + graph).style.display = "block";
                    graphs[graph].update({ series: response }, options);

                    graphsLoaded += 1;
                    if (graphsLoaded == openGraphs.length) {
                        isLoading = false; graphsLoaded = 0;
                    }

                }).fail(function() {
                    var options = graphs[graph].options;
                    delete options.axisX.low; delete options.axisX.high;
                    graphs[graph].update({ series: null }, options);
                    
                    graphsLoaded += 1;
                    if (graphsLoaded == openGraphs.length) {
                        isLoading = false; graphsLoaded = 0;
                    }
                });
            }

            function toggleGraph(graph, fields, button) {
                if (button.innerHTML == "-") {
                    document.getElementById("graph_" + graph).style.display = "none";

                    var options = graphs[graph].options;
                    delete options.axisX.low; delete options.axisX.high;
                    graphs[graph].update({ series: null }, options);
                    
                    button.innerHTML = "+";
                    openGraphs.splice(openGraphs.indexOf(graph), 1);

                } else {
                    button.innerHTML = "-";
                    openGraphs.push(graph);
                    loadGraphData(graph, fields);
                }
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
                for (var graph in graphs) {
                    var options = graphs[graph].options;
                    delete options.axisX.low; delete options.axisX.high;
                    graphs[graph].update({ series: null }, options);
                }
                
                var utc = moment().utc().millisecond(0).second(0);
                var localUtc = moment(utc).tz(awsTimeZone);
                var localReq = moment(requestedTime).tz(awsTimeZone);

                if (localUtc.format("DD/MM/YYYY") == localReq.format("DD/MM/YYYY")) {
                    updateData(true)
                } else { updateData(false); }
            }

            function openPicker() {
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
                        <a class="menu_item" href="statistics.php">Statistics</a>
                        <a class="menu_item" href="camera.php">Camera</a>
                        <span>|</span>
    
                        <span>Graph:</span>
                        <a class="menu_item" id="ami" href="graph-day.php">Day</a>
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

                    <div>
                        <span class="group_key">(<span>Air Temp</span>, <span>Exposed Temp</span>, <span>Dew Point</span>) [°C]</span>
                        <p class="group_toggle" onclick="toggleGraph('temperature', 'AirT,ExpT,DewP', this)">-</p>
                    </div>
                </div>

                <div id="graph_temperature" class="ct-chart"></div>
            </div>

            <div class="group">
                <div class="group_header">
                    <p class="group_title">Relative Humidity</p>

                    <div>
                        <span class="group_key">[%]</span>
                        <p class="group_toggle" onclick="toggleGraph('humidity', 'RelH', this)">+</p>
                    </div>
                </div>

                <div id="graph_humidity" class="ct-chart"></div>
            </div>

            <div class="group">
                <div class="group_header">
                    <p class="group_title">Wind Velocity</p>
                    
                    <div>
                        <span class="group_key">(<span>Wind Speed</span>, <span>Wind Gust</span>) [mph]</span>
                        <p class="group_toggle" onclick="toggleGraph('wind', 'WSpd,WGst', this)">+</p>
                    </div>
                </div>

                <div id="graph_wind" class="ct-chart"></div>
            </div>

            <div class="group">
                <div class="group_header">
                    <p class="group_title">Wind Direction</p>
                    
                    <div>
                        <span class="group_key">[°]</span>
                        <p class="group_toggle" onclick="toggleGraph('direction', 'WDir', this)">+</p>
                    </div>
                </div>

                <div id="graph_direction" class="ct-chart"></div>
            </div>

            <div class="group">
                <div class="group_header">
                    <p class="group_title">Sunshine Accumulation</p>
                    
                    <div>
                        <span class="group_key">[hrs]</span>
                        <p class="group_toggle" onclick="toggleGraph('sunshine', 'SunD', this)">+</p>
                    </div>
                </div>

                <div id="graph_sunshine" class="ct-chart"></div>
            </div>

            <div class="group">
                <div class="group_header">
                    <p class="group_title">Rainfall Accumulation</p>
                    
                    <div>
                        <span class="group_key">[mm]</span>
                        <p class="group_toggle" onclick="toggleGraph('rainfall', 'Rain', this)">+</p>
                    </div>
                </div>

                <div id="graph_rainfall" class="ct-chart"></div>
            </div>

            <div class="group">
                <div class="group_header">
                    <p class="group_title">Mean Sea Level Pressure</p>
                    
                    <div>
                        <span class="group_key">[hPa]</span>
                        <p class="group_toggle" onclick="toggleGraph('pressure', 'MSLP', this)">+</p>
                    </div>
                </div>

                <div id="graph_pressure" class="ct-chart"></div>
            </div>

            <div class="group" style="margin-bottom: 0px">
                <div class="group_header">
                    <p class="group_title">Soil Temperature</p>
                    
                    <div>
                        <span class="group_key">(<span>10CM</span>, <span>30CM</span>, <span>1M</span>) [°C]</span>
                        <p class="group_toggle" onclick="toggleGraph('soil', 'ST10,ST30,ST00', this)">+</p>
                    </div>
                </div>

                <div id="graph_soil" class="ct-chart"></div>
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