var isLoading = false;
var requestedTime = null;
var updateTimeout = null;
const charts = {};
var datePicker = null;

window.addEventListener("load", () =>
{
    document.getElementById("scroller-left-btn")
        .addEventListener("click", onScrollerLeftBtnClick);
    document.getElementById("scroller-right-btn")
        .addEventListener("click", onScrollerRightBtnClick);
    document.getElementById("scroller-time-btn")
        .addEventListener("click", onScrollerTimeBtnClick);

    setUpCharts();
    updateData(true);
});

function setUpCharts()
{
    charts.temp = setUpChart("temp-chart", true,
        ["Air Temperature (°C)", "Dew Point (°C)"]);

    charts.relHum = setUpChart("rel-hum-chart", true,
        ["Relative Humidity (%)"]);

    charts.windVel = setUpChart("wind-vel-chart", true,
        ["Wind Speed (mph)", "Wind Gust (mph)"], { ticks: { min: 0 } });

    charts.windDir = setUpChart("wind-dir-chart", false,
        ["Wind Direction (°)"], { ticks: { min: 0, max: 360 } });

    charts.rainfall = setUpChart("rainfall-chart", true,
        ["Rainfall Accumulated (mm)"], { ticks: { min: 0 } });

    charts.sunDur = setUpChart("sun-dur-chart", true,
        ["Sunshine Duration Accumulated (hrs)"], { ticks: { min: 0 } });

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
            responsive: true,
            animation: false,

            elements:
            {
                line: { borderWidth: 1, lineTension: 0, fill: false },
                point: { hitRadius: 15, hoverRadius: 0 }
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
                        tooltipFormat: "dd/LL/yyyy 'at' HH:mm"
                    }
                }],

                yAxes: [{ }]
            }
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
            default:
            case 0: dataset.borderColor = "rgb(195, 39, 40)"; break;
            case 1: dataset.borderColor = "rgb(50, 136, 195)"; break;
            case 2: dataset.borderColor = "rgb(55, 145, 109)"; break;
        }

        parameters.data.datasets.push(dataset);
    }

    return new Chart(document.getElementById(element), parameters);
}


function updateData(autoUpdate)
{
    isLoading = true;
    clearTimeout(updateTimeout);

    document.getElementById("scroller-left-btn").disabled = true;
    document.getElementById("scroller-right-btn").disabled = true;
    document.getElementById("scroller-time-btn").disabled = true;
    
    if (autoUpdate === true)
    {
        requestedTime = luxon.DateTime.fromObject({ zone: awsTimeZone });
        updateTimeout = setInterval(() => updateData(true), 60000);
    }

    let timeStr = requestedTime.toFormat("dd/LL/yyyy");
    if (autoUpdate)
        timeStr += requestedTime.toFormat(" ('at' HH:mm)");
    document.getElementById("scroller-time-btn").innerHTML = timeStr;

    loadData(requestedTime)
        .then(() =>
        {
            document.getElementById("scroller-left-btn").disabled = false;
            document.getElementById("scroller-right-btn").disabled = false;
            document.getElementById("scroller-time-btn").disabled = false;
            isLoading = false;
        });
}

function loadData()
{
    return new Promise(resolve =>
    {
        const start = requestedTime.startOf("day").toUTC();
        const end = requestedTime.endOf("day").plus({ minutes: 1 }).toUTC();

        const url = "api.php/reports?start={0}&end={1}".format(
            start.toFormat("yyyy-LL-dd'T'HH-mm-ss"),
            end.toFormat("yyyy-LL-dd'T'HH-mm-ss"));
        
        getJson(url)
            .then(data =>
            {
                displayData(data, start, end);
                resolve();
            })
            .catch(() =>
            {
                for (const chart of Object.values(charts))
                {
                    chart.data.datasets[0].data = null;
                    chart.update();
                }

                resolve();
            });
    });
}

function displayData(data, start, end)
{
    const airTemp = [];
    const dewPoint = [];
    const relHum = [];
    const windSpeed = [];
    const windGust = [];
    const windDir = [];
    const rainfall = [];
    let rainfallTtl = 0;
    const sunDur = [];
    let sunDurTtl = 0;
    const mslPres = [];

    for (const report of data)
    {
        const time = report.time.replace(" ", "T");
        airTemp.push({ x: time, y: report.airTemp });
        dewPoint.push({ x: time, y: report.dewPoint });
        relHum.push({ x: time, y: report.relHum });
        windSpeed.push({ x: time, y: report.windSpeed });
        windGust.push({ x: time, y: report.windGust });
        windDir.push({ x: time, y: report.windDir });

        if (report.rainfall != null)
            rainfallTtl += report.rainfall;
        rainfall.push({ x: time, y: rainfallTtl });

        if (report.sunDur != null)
            sunDurTtl += report.sunDur;
        sunDur.push({ x: time, y: sunDurTtl / 3600 });

        mslPres.push({ x: time, y: report.mslPres });
    }

    charts.temp.data.datasets[0].data = airTemp;
    charts.temp.data.datasets[1].data = dewPoint;
    charts.relHum.data.datasets[0].data = relHum;
    charts.windVel.data.datasets[0].data = windSpeed;
    charts.windVel.data.datasets[1].data = windGust;
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


function onScrollerLeftBtnClick()
{
    if (isLoading)
        return;
    
    requestedTime = requestedTime.minus({ days: 1 });
    scrollerChange();
}

function onScrollerRightBtnClick()
{
    if (isLoading)
        return;

    requestedTime = requestedTime.plus({ days: 1 });
    scrollerChange();
}

function onScrollerTimeBtnClick()
{
    if (datePicker !== null)
        return;

    datePicker = flatpickr("#scroller-time-btn",
    {
        defaultDate: requestedTime.toJSDate(),
        disableMobile: true,
        onClose: onDatePickerClose
    });
    
    datePicker.open();
}

function onDatePickerClose()
{
    const selected = luxon.DateTime.fromJSDate(
        datePicker.selectedDates[0]).setZone("UTC");

    datePicker.destroy();
    datePicker = null;

    const different =
        selected.day !== requestedTime.day ||
        selected.month !== requestedTime.month ||
        selected.year !== requestedTime.year;

    if (different)
    {
        requestedTime = selected;
        scrollerChange();
    }
}

function scrollerChange()
{
    const now = luxon.DateTime.utc();

    const autoUpdate =
        requestedTime.day === now.day &&
        requestedTime.month === now.month &&
        requestedTime.year === now.year;

    updateData(autoUpdate);
}