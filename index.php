<?php
require_once "php/helpers.php";

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

        <link href="resources/styles/reset.css" rel="stylesheet">
        <link href="resources/styles/defaults.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Bitter:wght@400;700&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <script src="resources/scripts/helpers.js"></script>

        <script>
            const awsTimeZone = "<?php echo $config["timeZone"]; ?>";
        </script>

        <link href="resources/styles/header.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/luxon@1.26.0/build/global/luxon.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <link href="resources/styles/grouping.css" rel="stylesheet">
        <script src="resources/scripts/page-index.js"></script>
        <link href="resources/styles/page-index.css" rel="stylesheet">
    </head>

    <body>
        <header>
            <div class="main-col">
                <p class="title">AWS - <?php echo $config["stationName"]; ?></p>

                <nav>
                    <a class="menu-item active-menu-item" href=".">Reports</a>
                    <a class="menu-item" href="statistics.php">Statistics</a>
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

            <div id="groups">
                <div class="group">
                    <div class="group-header">
                        <h3 class="group-name">Ambient Temperature</h3>
                    </div>

                    <table class="field-table">
                        <tr>
                            <td>Air Temperature</td>
                            <td id="item_AirT"></td>
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
                            <td id="item_RelH"></td>
                        </tr>
                        <tr>
                            <td>Dew Point</td>
                            <td id="item_DewP"></td>
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
                            <td id="item_WSpd"></td>
                        </tr>
                        <tr>
                            <td>Wind Direction (from)</td>
                            <td id="item_WDir"></td>
                        </tr>
                        <tr>
                            <td>Wind Gust</td>
                            <td id="item_WGst"></td>
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
                            <td id="item_SunD"></td>
                        </tr>
                        <tr>
                            <td>Sunshine Duration (Past Hour)</td>
                            <td id="item_SunD_PHr"></td>
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
                            <td id="item_Rain"></td>
                        </tr>
                        <tr>
                            <td>Rainfall (Past Hour)</td>
                            <td id="item_Rain_PHr"></td>
                        </tr>
                    </table>
                </div>

                <div class="group final">
                    <div class="group-header">
                        <h3 class="group-name">Barometric Pressure</h3>
                    </div>

                    <table class="field-table">
                        <tr>
                            <td>Station Pressure (At Station Elev)</td>
                            <td id="item_StaP"></td>
                        </tr>
                        <tr>
                            <td>Mean Sea Level Pressure</td>
                            <td id="item_MSLP"></td>
                        </tr>
                        <tr>
                            <td>3-Hour Pressure Tendency</td>
                            <td id="item_StaP_PTH"></td>
                        </tr>
                    </table>
                </div>
            </div>
        </main>
    </body>
</html>