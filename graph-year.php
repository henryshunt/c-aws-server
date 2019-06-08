<meta charset="UTF-8">
<!DOCTYPE html>
<?php include_once("data/config.php"); ?>

<html>
    <head>
        <title>C-AWS <?php echo $aws_location; ?> [Remote]</title>
        <link href="resources/styles/trebuchet.ttf" type="x-font-ttf">
        <link href="resources/styles/global.css" rel="stylesheet" type="text/css">
        <link href="resources/styles/chartist.css" rel="stylesheet" type="text/css">
        <link href="resources/styles/graphs.css" rel="stylesheet" type="text/css">
        <script src="resources/scripts/global.js" type="text/javascript"></script>
        <script src="resources/scripts/jquery.js" type="text/javascript"></script>
        <script src="resources/scripts/chartist.js" type="text/javascript"></script>
        <script src="resources/scripts/moment.js" type="text/javascript"></script>
        <script src="resources/scripts/moment-tz.js" type="text/javascript"></script>

        <script>
            const awsTimeZone = "<?php echo $aws_time_zone; ?>";

            var isLoading = true;
            var requestedTime = null;
            var graphs = { "temperature": null, "humidity": null, "wind": null,
                "direction": null, "sunshine": null, "rainfall": null,
                "pressure": null, "soil": null };
            var openGraphs = ["temperature"];
            var graphsLoaded = 0;

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
                    showPoint: false, lineSmooth: false, height: 350,

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
                        type: Chartist.FixedScaleAxis, divisor: 12, offset: 20,    
                        labelInterpolationFnc: function(value) {
                            return moment.unix(value).utc().tz(awsTimeZone).format("MMM DD");
                        }
                    }
                };

                if (graph == "direction") {
                    options.axisY.type = Chartist.FixedScaleAxis;
                    options.axisY.divisor = 8;
                    options.axisY.low = 0; options.axisY.high = 360;
                    options.showPoint = true; options.showLine = false;
                }

                if (graph == "sunshine" || graph == "rainfall") {
                    return new Chartist.Bar("#graph_" + graph, null, options);
                } else { return new Chartist.Line("#graph_" + graph, null, options); }
            }

            function updateData(setTime) {
                isLoading = true;
                getAndProcessData(setTime);
            }

            function getAndProcessData(setTime) {
                if (setTime == true) {
                    requestedTime = moment().utc().millisecond(0).second(0);
                }; graphsLoaded = 0;

                document.getElementById("scroller_time").innerHTML
                    = moment(requestedTime).tz(
                    awsTimeZone).format("[Year Ending] DD/MM/YYYY");

                if ($.inArray("temperature", openGraphs) != -1)
                { loadGraphData("temperature", "AirT_Avg,AirT_Min,AirT_Max"); }
                if ($.inArray("humidity", openGraphs) != -1)
                { loadGraphData("humidity", "RelH_Avg,RelH_Min,RelH_Max"); }
                if ($.inArray("wind", openGraphs) != -1)
                { loadGraphData("wind", "WSpd_Avg,WGst_Max"); }
                if ($.inArray("direction", openGraphs) != -1)
                { loadGraphData("direction", "WDir_Avg,WDir_Min,WDir_Avg"); }
                if ($.inArray("sunshine", openGraphs) != -1)
                { loadGraphData("sunshine", "SunD_Ttl"); }
                if ($.inArray("rainfall", openGraphs) != -1)
                { loadGraphData("rainfall", "Rain_Ttl"); }
                if ($.inArray("pressure", openGraphs) != -1)
                { loadGraphData("pressure", "MSLP_Avg,MSLP_Min,MSLP_Max"); }
                if ($.inArray("soil", openGraphs) != -1)
                { loadGraphData("soil", "ST10_Avg,ST30_Avg,ST00_Avg"); }
            }

            function loadGraphData(graph, fields) {
                var url = "data/graph-year.php?time=" + requestedTime.format(
                    "YYYY-MM-DD[T]HH-mm-00") + "&fields=" + fields;
                
                var localTime = moment(requestedTime).tz(awsTimeZone);
                var xEnd = moment(localTime).hour(0).minute(0).unix();
                var xStart = moment(localTime).subtract(365, "day").hour(0).minute(0).unix();

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
                if (isLoading == false) {
                    requestedTime.subtract(1, "months");
                    scrollerChange();
                }
            }
            
            function scrollerRight() {
                if (isLoading == false) {
                    requestedTime.add(1, "months");
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
                        <a class="menu_item" id="ami" href="graph-year.php">Year</a>
    
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

                <div><p id="scroller_time"></p></div>

                <div id="scroller_right" class="scroller_button" onclick="scrollerRight()">
                    <svg width="20" height="20" viewBox="0 0 512 512">
                        <polygon points="160,128.4 192.3,96 352,256 352,256 352,256 192.3,416 160,383.6 287.3,256"/>
                    </svg>
                </div>
            </div>

            <div class="group">
                <div class="group_header">
                    <p class="group_title">Air Temperature</p>

                    <div>
                        <span class="group_key">(<span>Average</span>, <span>Minimum</span>, <span>Maximum</span>) [°C]</span>
                        <p class="group_toggle" onclick="toggleGraph('temperature', 'AirT_Avg,AirT_Min,AirT_Max', this)">-</p>
                    </div>
                </div>

                <div id="graph_temperature" class="ct-chart"></div>
            </div>

            <div class="group">
                <div class="group_header">
                    <p class="group_title">Relative Humidity</p>

                    <div>
                        <span class="group_key">(<span>Average</span>, <span>Minimum</span>, <span>Maximum</span>) [%]</span>
                        <p class="group_toggle" onclick="toggleGraph('humidity', 'RelH_Avg,RelH_Min,RelH_Max', this)">+</p>
                    </div>
                </div>

                <div id="graph_humidity" class="ct-chart"></div>
            </div>

            <div class="group">
                <div class="group_header">
                    <p class="group_title">Wind Velocity</p>

                    <div>
                        <span class="group_key">(<span>Average Speed</span>, <span>Maximum Gust</span>) [mph]</span>
                        <p class="group_toggle" onclick="toggleGraph('wind', 'WSpd_Avg,WGst_Max', this)">+</p>
                    </div>
                </div>

                <div id="graph_wind" class="ct-chart"></div>
            </div>
            
            <div class="group">
                <div class="group_header">
                    <p class="group_title">Wind Direction</p>

                    <div>
                        <span class="group_key">(<span>Average</span>, <span>Minimum</span>, <span>Maximum</span>) [°]</span>
                        <p class="group_toggle" onclick="toggleGraph('direction', 'WDir_Avg,WDir_Min,WDir_Max', this)">+</p>
                    </div>
                </div>

                <div id="graph_direction" class="ct-chart"></div>
            </div>

            <div class="group">
                <div class="group_header">
                    <p class="group_title">Sunshine Duration</p>

                    <div>
                        <span class="group_key">[hrs]</span>
                        <p class="group_toggle" onclick="toggleGraph('sunshine', 'SunD_Ttl', this)">+</p>
                    </div>
                </div>

                <div id="graph_sunshine" class="ct-chart"></div>
            </div>

            <div class="group">
                <div class="group_header">
                    <p class="group_title">Rainfall</p>

                    <div>
                        <span class="group_key">[mm]</span>
                        <p class="group_toggle" onclick="toggleGraph('rainfall', 'Rain_Ttl', this)">+</p>
                    </div>
                </div>

                <div id="graph_rainfall" class="ct-chart"></div>
            </div>

            <div class="group">
                <div class="group_header">
                    <p class="group_title">Mean Sea Level Pressure</p>

                    <div>
                        <span class="group_key">(<span>Average</span>, <span>Minimum</span>, <span>Maximum</span>) [hPa]</span>
                        <p class="group_toggle" onclick="toggleGraph('pressure', 'MSLP_Avg,MSLP_Min,MSLP_Max', this)">+</p>
                    </div>
                </div>

                <div id="graph_pressure" class="ct-chart"></div>
            </div>

            <div class="group" style="margin-bottom: 0px">
                <div class="group_header">
                    <p class="group_title">Soil Temperature</p>

                    <div>
                        <span class="group_key">(<span>10CM Average</span>, <span>30CM Average</span>, <span>1M Average</span>) [°C]</span>
                        <p class="group_toggle" onclick="toggleGraph('soil', 'ST10_Avg,ST30_Avg,ST00_Avg', this)">+</p>
                    </div>
                </div>

                <div id="graph_soil" class="ct-chart"></div>
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