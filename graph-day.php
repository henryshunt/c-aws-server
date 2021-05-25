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
        <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-luxon@0.2.2/dist/chartjs-adapter-luxon.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <link href="resources/css/grouping.css" rel="stylesheet">
        <script src="resources/js/pages/graph-day.js"></script>
    </head>

    <body>
        <header>
            <div class="main-col">
                <p class="title">AWS - <?php echo $config["stationName"]; ?></p>

                <nav>
                    <a class="menu-item" href=".">Observations</a>
                    <a class="menu-item" href="statistics.php">Statistics</a>
                    <a class="menu-item" href="camera.php">Camera</a>
                    <span class="menu-sep">|</span>
                    <span class="menu-sep">Graph:</span>
                    <a class="menu-item active-menu-item" href="graph-day.php">Day</a>
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
                <canvas id="air-temp-chart"></canvas>
            </div>

            <div class="group">
                <canvas id="rel-hum-chart"></canvas>
            </div>

            <div class="group">
                <canvas id="wind-speed-chart"></canvas>
            </div>

            <div class="group">
                <canvas id="wind-dir-chart"></canvas>
            </div>

            <div class="group">
                <canvas id="rainfall-chart"></canvas>
            </div>

            <div class="group">
                <canvas id="sun-dur-chart"></canvas>
            </div>

            <div class="group final">
                <canvas id="msl-pres-chart"></canvas>
            </div>
        </main>
    </body>
</html>