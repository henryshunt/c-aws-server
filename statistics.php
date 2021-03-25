<?php
require_once "php/utilities.php";

$config = load_config("config.json");
if ($config === false)
{
    echo "Failed to load configuration file";
    error_log("Failed to load configuration file");
    exit();
}

$title = "AWS " . $config["stationName"] . " " .
    ($config["isRemote"] ? "[Remote]" : "[Local]");
?>

<meta charset="UTF-8">
<!DOCTYPE html>

<html>
    <head>
        <title><?php echo $title; ?></title>
        <meta name="viewport" content="width=device-width">

        <link href="resources/css/reset.css" rel="stylesheet">
        <link href="resources/css/globals.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Bitter:wght@400;700&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <script src="resources/js/utilities.js"></script>

        <script>
            const awsTimeZone = "<?php echo $config["timeZone"]; ?>";
        </script>

        <link href="resources/css/header.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/luxon@1.26.0/build/global/luxon.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <link href="resources/css/grouping.css" rel="stylesheet">
        <script src="resources/js/pages/statistics.js"></script>
        <link href="resources/css/pages/statistics.css" rel="stylesheet">
    </head>

    <body>
        <header>
            <div class="main-col">
                <p class="title">AWS - <?php echo $config["stationName"]; ?></p>

                <nav>
                    <a class="menu-item" href=".">Reports</a>
                    <a class="menu-item active-menu-item" href="statistics.php">Statistics</a>
                    <a class="menu-item" href="camera.php">Camera</a>
                    <span class="menu-sep">|</span>
                    <span class="menu-sep">Graph:</span>
                    <a class="menu-item" href="graph-day.php">Day</a>
                    <a class="menu-item" href="graph-year.php">Year</a>
                    <span class="menu-sep">|</span>
                    <a class="menu-item" href="climate.php">Climate</a>
                    <span class="menu-sep">|</span>
                    <a class="menu-item" href="station.php">Station</a>
                </nav>
            </div>
        </header>

        <main class="main-col">
            <div class="group scroller">
                <button class="scroller-btn solid-btn" id="scroller-left-btn">
                    <i class="material-icons">chevron_left</i>
                </button>

                <div class="scroller-centre">
                    <button class="scroller-time text-btn" id="scroller-time-btn"></button>
                </div>

                <button class="scroller-btn solid-btn" id="scroller-right-btn">
                    <i class="material-icons">chevron_right</i>
                </button>
            </div>

            <div class="group">
                <div class="group-header">
                    <h3 class="group-name">Ambient Temperature</h3>
                </div>

                <table class="field-table">
                    <tr>
                        <td>Air Temperature</td>
                        <td><span>AVG: </span><span id="item_AirT_Avg"></span></td>
                        <td><span>MIN: </span><span id="item_AirT_Min"></span></td>
                        <td><span>MAX: </span><span id="item_AirT_Max"></span></td>
                    </tr>
                </table>
            </div>

            <div class="group">
                <div class="group-header">
                    <h3 class="group-name">Moisture</h3>
                </div>

                <table class="field-table">
                    <tr>
                        <td>Relative Humidity</td>
                        <td><span>AVG: </span><span id="item_RelH_Avg"></span></td>
                        <td><span>MIN: </span><span id="item_RelH_Min"></span></td>
                        <td><span>MAX: </span><span id="item_RelH_Max"></span></td>
                    </tr>
                    <tr>
                        <td>Dew Point</td>
                        <td><span>AVG: </span><span id="item_DewP_Avg"></span></td>
                        <td><span>MIN: </span><span id="item_DewP_Min"></span></td>
                        <td><span>MAX: </span><span id="item_DewP_Max"></span></td>
                    </tr>
                </table>
            </div>

            <div class="group">
                <div class="group-header">
                    <h3 class="group-name">Wind</h3>
                </div>

                <table class="field-table">
                    <tr>
                        <td>Wind Speed</td>
                        <td><span>AVG: </span><span id="item_WSpd_Avg"></span></td>
                        <td><span>MIN: </span><span id="item_WSpd_Min"></span></td>
                        <td><span>MAX: </span><span id="item_WSpd_Max"></span></td>
                    </tr>
                    <tr>
                        <td>Wind Direction</td>
                        <td><span>AVG: </span><span id="item_WDir_Avg"></span></td>
                    </tr>
                    <tr>
                        <td>Wind Gust</td>
                        <td><span>AVG: </span><span id="item_WGst_Avg"></span></td>
                        <td><span>MIN: </span><span id="item_WGst_Min"></span></td>
                        <td><span>MAX: </span><span id="item_WGst_Max"></span></td>
                    </tr>
                </table>
            </div>

            <div class="group">
                <div class="group-header">
                    <h3 class="group-name">Solar Radiation</h3>
                </div>

                <table class="field-table">
                    <tr>
                        <td>Sunshine Duration</td>
                        <td><span>AVG: </span><span id="item_SunD_Ttl"></span></td>
                    </tr>
                </table>
            </div>

            <div class="group">
                <div class="group-header">
                    <h3 class="group-name">Precipitation</h3>
                </div>

                <table class="field-table">
                    <tr>
                        <td>Rainfall</td>
                        <td><span>AVG: </span><span id="item_Rain_Ttl"></span></td>
                    </tr>
                </table>
            </div>

            <div class="group final">
                <div class="group-header">
                    <h3 class="group-name">Barometric Pressure</h3>
                </div>

                <table class="field-table">
                    <tr>
                        <td>Mean Sea Level Pressure</td>
                        <td><span>AVG: </span><span id="item_MSLP_Avg"></span></td>
                        <td><span>MIN: </span><span id="item_MSLP_Min"></span></td>
                        <td><span>MAX: </span><span id="item_MSLP_Max"></span></td>
                    </tr>
                </table>
            </div>
        </main>
    </body>
</html>