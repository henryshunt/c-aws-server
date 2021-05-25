var isLoading = false;
var dataTime = null;
var updateTimeout = null;
var datePicker = null;
const charts = {};

window.addEventListener("load", () =>
{
    document.getElementById("browser-left-btn")
        .addEventListener("click", onBrowserLeftBtnClick);
    document.getElementById("browser-right-btn")
        .addEventListener("click", onBrowserRightBtnClick);
    document.getElementById("browser-time-btn")
        .addEventListener("click", onBrowserTimeBtnClick);

    setUpCharts();
    updateData(luxon.DateTime.utc(), true);
});

function setUpCharts()
{
    charts.airTemp = setUpChart("air-temp-chart", true,
        ["Air Temperature (°C)", "Dew Point (°C)"]);

    charts.relHum = setUpChart("rel-hum-chart", true,
        ["Relative Humidity (%)"]);

    charts.windSpeed = setUpChart("wind-speed-chart", true,
        ["Wind Speed (mph)", "Wind Gust (mph)"], { ticks: { min: 0 } });

    charts.windDir = setUpChart("wind-dir-chart", false,
        ["Wind Direction (from) (°)"], { ticks: { min: 0, max: 360, stepSize: 90 } });

    charts.rainfall = setUpChart("rainfall-chart", true,
        ["Rainfall (mm)"], { ticks: { min: 0 } });

    charts.sunDur = setUpChart("sun-dur-chart", true,
        ["Sunshine Duration (hr)"], { ticks: { min: 0 } });

    charts.mslPres = setUpChart("msl-pres-chart", true,
        ["Mean Sea Level Pressure (hPa)"]);
}

function setUpChart(element, line, seriesLabels, yOptions = null)
{
    const parameters =
    {
        type: "scatter",
        data: { datasets: [] },

        options:
        {
            elements:
            {
                line: { borderWidth: 1, tension: 0, fill: false },
                point: { hitRadius: 15, radius: 2, hoverRadius: 2 }
            },

            scales:
            {
                xAxes:
                [{
                    type: "time",

                    time:
                    {
                        unit: "hour",
                        stepSize: 3,
                        tooltipFormat: "dd/LL/yyyy 'at' HH:mm",

                        parser: (time) =>
                        {
                            if (typeof time === "string")
                            {
                                return luxon.DateTime.fromSQL(time, { zone: "UTC" })
                                    .setZone(awsTimeZone);
                            }
                            else return time.setZone(awsTimeZone);
                        }
                    },
                }],

                yAxes: [{ }]
            },

            responsive: true,
            animation: false
        }
    }

    if (line)
        parameters.options.elements.point.radius = 0;

    if (yOptions !== null)
        Object.assign(parameters.options.scales.yAxes[0], yOptions);

    for (let i = 0; i < seriesLabels.length; i++)
    {
        const dataset = { label: seriesLabels[i] };
        
        if (line)
            dataset.showLine = true;

        switch (i)
        {
            case 0: dataset.borderColor = "rgb(195, 39, 40)"; break;
            case 1: dataset.borderColor = "rgb(50, 136, 195)"; break;
            case 2: dataset.borderColor = "rgb(55, 145, 109)"; break;
        }

        parameters.data.datasets.push(dataset);
    }

    return new Chart(document.getElementById(element), parameters);
}


function updateData(time, autoUpdate)
{
    isLoading = true;
    clearTimeout(updateTimeout);

    document.getElementById("browser-left-btn").disabled = true;
    document.getElementById("browser-right-btn").disabled = true;
    document.getElementById("browser-time-btn").disabled = true;

    dataTime = time;
    
    if (autoUpdate)
    {
        updateTimeout = setInterval(() =>
            updateData(luxon.DateTime.utc(), true), 60000);
    }

    loadData(autoUpdate)
        .then(() =>
        {
            document.getElementById("browser-left-btn").disabled = false;
            document.getElementById("browser-right-btn").disabled = false;
            document.getElementById("browser-time-btn").disabled = false;
            isLoading = false;
        });
}

function loadData(showTime)
{
    return new Promise(resolve =>
    {
        const start = dataTime.setZone(awsTimeZone).startOf("day").toUTC();
        const end = dataTime.setZone(awsTimeZone).endOf("day").toUTC()
            .plus({ minutes: 1 });

        const url = "api.php/observations?start={0}&end={1}".format(
            start.toFormat("yyyy-LL-dd'T'HH-mm-ss"),
            end.toFormat("yyyy-LL-dd'T'HH-mm-ss"));
        
        getJson(url)
            .then(data =>
            {
                displayData(data, start, end, showTime);
                resolve();
            })
            .catch(() =>
            {
                let timeString = dataTime.setZone(awsTimeZone).toFormat("dd/LL/yyyy");
                if (showTime)
                    timeString += dataTime.setZone(awsTimeZone).toFormat(" ('at' HH:mm)");
                document.getElementById("browser-time-btn").innerHTML = timeString;

                for (const chart of Object.values(charts))
                {
                    chart.data.datasets[0].data = null;
                    chart.update();
                }

                resolve();
            });
    });
}

function displayData(data, start, end, showTime)
{
    let timeString = dataTime.setZone(awsTimeZone).toFormat("dd/LL/yyyy");
    if (showTime)
        timeString += dataTime.setZone(awsTimeZone).toFormat(" ('at' HH:mm)");
    document.getElementById("browser-time-btn").innerHTML = timeString;

    const airTemp = [], dewPoint = [], relHum = [], windSpeed = [], mslPres = [],
        windGust = [], windDir = [], rainfall = [], sunDur = [];
    let rainfallTtl = 0, sunDurTtl = 0;

    for (const observation of data)
    {
        const time = observation.time;

        if (observation.airTemp !== null)
            airTemp.push({ x: time, y: observation.airTemp });
        if (observation.dewPoint !== null)
            dewPoint.push({ x: time, y: observation.dewPoint });
        if (observation.relHum !== null)
            relHum.push({ x: time, y: observation.relHum });
        if (observation.windSpeed !== null)
            windSpeed.push({ x: time, y: roundPlaces(observation.windSpeed * 2.237, 1) }); // m/s to mph
        if (observation.windGust !== null)
            windGust.push({ x: time, y: roundPlaces(observation.windGust * 2.237, 1) }); // m/s to mph
        if (observation.windDir !== null)
            windDir.push({ x: time, y: observation.windDir });

        if (observation.rainfall != null)
            rainfallTtl += observation.rainfall;
        rainfall.push({ x: time, y: rainfallTtl });

        if (observation.sunDur != null)
            sunDurTtl += observation.sunDur;
        sunDur.push({ x: time, y: roundPlaces(sunDurTtl / 60 / 60, 2) }); // sec to hr

        if (observation.mslPres !== null)
            mslPres.push({ x: time, y: observation.mslPres });
    }

    charts.airTemp.data.datasets[0].data = airTemp;
    charts.airTemp.data.datasets[1].data = dewPoint;
    charts.relHum.data.datasets[0].data = relHum;
    charts.windSpeed.data.datasets[0].data = windSpeed;
    charts.windSpeed.data.datasets[1].data = windGust;
    charts.windDir.data.datasets[0].data = windDir;
    charts.rainfall.data.datasets[0].data = rainfall;
    charts.sunDur.data.datasets[0].data = sunDur;
    charts.mslPres.data.datasets[0].data = mslPres;

    for (const chart of Object.values(charts))
    {
        chart.options.scales.xAxes[0].ticks.min = start;
        chart.options.scales.xAxes[0].ticks.max = end;
        chart.update();
    }
}


function onBrowserLeftBtnClick()
{
    if (!isLoading)
        browserChange(dataTime.minus({ days: 1 }));
}

function onBrowserRightBtnClick()
{
    if (!isLoading)
        browserChange(dataTime.plus({ days: 1 }));
}

function onBrowserTimeBtnClick()
{
    if (datePicker !== null)
        return;

    datePicker = flatpickr("#browser-time-btn",
    {
        defaultDate: dataTime.toJSDate(),
        disableMobile: true,
        onClose: onDatePickerClose
    });
    
    datePicker.open();
}

function onDatePickerClose()
{
    const selected = luxon.DateTime.fromJSDate(
        datePicker.selectedDates[0]);

    datePicker.destroy();
    datePicker = null;

    const dataTimeLocal = dataTime.setZone(awsTimeZone);

    const different =
        selected.day !== dataTimeLocal.day ||
        selected.month !== dataTimeLocal.month ||
        selected.year !== dataTimeLocal.year;

    if (different)
        browserChange(selected);
}

function browserChange(time)
{
    const timeLocal = time.setZone(awsTimeZone);
    const now = luxon.DateTime.utc();
    const nowLocal = now.setZone(awsTimeZone);

    const theSame =
        timeLocal.day === nowLocal.day &&
        timeLocal.month === nowLocal.month &&
        timeLocal.year === nowLocal.year;

    if (theSame)
        updateData(now, true);
    else updateData(time, false);
}