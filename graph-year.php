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
        <script src="resources/scripts/global.js" type="text/javascript"></script>
        <script src="resources/scripts/jquery.js" type="text/javascript"></script>
        <script src="resources/scripts/chartist.js" type="text/javascript"></script>
        <script src="resources/scripts/moment.js" type="text/javascript"></script>
        <script src="resources/scripts/moment-tz.js" type="text/javascript"></script>
        <script src="resources/scripts/page-graph-year.js" type="text/javascript"></script>

        <script>
            const awsTimeZone
                = "<?php echo $config->get_aws_time_zone(); ?>";

            var isLoading = true;
            var requestedTime = null;
            var graphs = { "temperature": null, "humidity": null, "wind": null,
                "direction": null, "sunshine": null, "rainfall": null,
                "pressure": null, "soil": null };
            var openGraphs = ["temperature"];
            var graphsLoaded = 0;

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
                        <a class="menu_item" href="graph-day.php">Day</a>
                        <a class="menu_item" id="ami" href="graph-year.php">Year</a>
    
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

    </body>
</html>