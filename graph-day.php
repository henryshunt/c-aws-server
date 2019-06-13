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
        <link href="resources/styles/chartist.css" rel="stylesheet" type="text/css">
        <link href="resources/styles/graphs.css" rel="stylesheet" type="text/css">
        <link href="resources/styles/flatpickr.css" rel="stylesheet" type="text/css">
        <script src="resources/scripts/global.js" type="text/javascript"></script>
        <script src="resources/scripts/jquery.js" type="text/javascript"></script>
        <script src="resources/scripts/chartist.js" type="text/javascript"></script>
        <script src="resources/scripts/moment.js" type="text/javascript"></script>
        <script src="resources/scripts/moment-tz.js" type="text/javascript"></script>
        <script src="resources/scripts/flatpickr.js" type="text/javascript"></script>
        <script src="resources/scripts/page-graph-day.js" type="text/javascript"></script>

        <script>
            const awsTimeZone
                = "<?php echo $config->get_aws_time_zone(); ?>";

            var isLoading = true;
            var datePicker = null;
            var requestedTime = null;
            var graphs = { "temperature": null, "humidity": null, "wind": null,
                "direction": null, "sunshine": null, "rainfall": null,
                "pressure": null, "soil": null };
            var openGraphs = ["temperature"];
            var graphsLoaded = 0;
            var updaterTimeout = null;

            $(document).ready(function() {
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
                        <a class="menu_item" href="statistics.php">Statistics</a>
                        <a class="menu_item" href="camera.php">Camera</a>
                        <span>|</span>
    
                        <span>Graph:</span>
                        <a class="menu_item" id="ami" href="graph-day.php">Day</a>
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
        
    </body>
</html>