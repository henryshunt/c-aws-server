var isLoading = false;
var dataTime = null;
var updateTimeout = null;
var datePicker = null;

window.addEventListener("load", () =>
{
    document.getElementById("browser-left-btn")
        .addEventListener("click", onBrowserLeftBtnClick);
    document.getElementById("browser-right-btn")
        .addEventListener("click", onBrowserRightBtnClick);
    document.getElementById("browser-time-btn")
        .addEventListener("click", onBrowserTimeBtnClick);

    updateData(luxon.DateTime.utc(), true);
});


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
        const url = "api.php/statistics/daily/" +
            dataTime.setZone(awsTimeZone).toFormat("yyyy-LL-dd");

        getJson(url)
            .then(data =>
            {
                displayData(data, showTime);
                resolve();
            })
            .catch(() =>
            {
                let timeString = dataTime.setZone(awsTimeZone).toFormat("dd/LL/yyyy");
                if (showTime)
                    timeString += dataTime.setZone(awsTimeZone).toFormat(" ('at' HH:mm)");
                document.getElementById("browser-time-btn").innerHTML = timeString;

                document.getElementById("air-temp-avg").innerHTML = "No Data";
                document.getElementById("air-temp-min").innerHTML = "No Data";
                document.getElementById("air-temp-max").innerHTML = "No Data";
                document.getElementById("rel-hum-avg").innerHTML = "No Data";
                document.getElementById("rel-hum-min").innerHTML = "No Data";
                document.getElementById("rel-hum-max").innerHTML = "No Data";
                document.getElementById("dew-point-avg").innerHTML = "No Data";
                document.getElementById("dew-point-min").innerHTML = "No Data";
                document.getElementById("dew-point-max").innerHTML = "No Data";
                document.getElementById("wind-speed-avg").innerHTML = "No Data";
                document.getElementById("wind-speed-min").innerHTML = "No Data";
                document.getElementById("wind-speed-max").innerHTML = "No Data";
                document.getElementById("wind-dir-avg").innerHTML = "No Data";
                document.getElementById("wind-gust-avg").innerHTML = "No Data";
                document.getElementById("wind-gust-min").innerHTML = "No Data";
                document.getElementById("wind-gust-max").innerHTML = "No Data";
                document.getElementById("sun-dur-ttl").innerHTML = "No Data";
                document.getElementById("rainfall-ttl").innerHTML = "No Data";
                document.getElementById("msl-pres-avg").innerHTML = "No Data";
                document.getElementById("msl-pres-min").innerHTML = "No Data";
                document.getElementById("msl-pres-max").innerHTML = "No Data";
                resolve();
            });
    });
}

function displayData(data, showTime)
{
    let timeString = dataTime.setZone(awsTimeZone).toFormat("dd/LL/yyyy");
    if (showTime)
        timeString += dataTime.setZone(awsTimeZone).toFormat(" ('at' HH:mm)");
    document.getElementById("browser-time-btn").innerHTML = timeString;

    if (data["airTempAvg"] !== null)
        document.getElementById("air-temp-avg").innerText = data["airTempAvg"] + "°C";
    else document.getElementById("air-temp-avg").innerText = "No Data";

    if (data["airTempMin"] !== null)
        document.getElementById("air-temp-min").innerText = data["airTempMin"] + "°C";
    else document.getElementById("air-temp-min").innerText = "No Data";

    if (data["airTempMax"] !== null)
        document.getElementById("air-temp-max").innerText = data["airTempMax"] + "°C";
    else document.getElementById("air-temp-max").innerText = "No Data";

    if (data["relHumAvg"] !== null)
        document.getElementById("rel-hum-avg").innerText = data["relHumAvg"] + "%";
    else document.getElementById("rel-hum-avg").innerText = "No Data";

    if (data["relHumMin"] !== null)
        document.getElementById("rel-hum-min").innerText = data["relHumMin"] + "%";
    else document.getElementById("rel-hum-min").innerText = "No Data";

    if (data["relHumMax"] !== null)
        document.getElementById("rel-hum-max").innerText = data["relHumMax"] + "%";
    else document.getElementById("rel-hum-max").innerText = "No Data";

    if (data["dewPointAvg"] !== null)
        document.getElementById("dew-point-avg").innerText = data["dewPointAvg"] + "°C";
    else document.getElementById("dew-point-avg").innerText = "No Data";

    if (data["dewPointMin"] !== null)
        document.getElementById("dew-point-min").innerText = data["dewPointMin"] + "°C";
    else document.getElementById("dew-point-min").innerText = "No Data";

    if (data["dewPointMax"] !== null)
        document.getElementById("dew-point-max").innerText = data["dewPointMax"] + "°C";
    else document.getElementById("dew-point-max").innerText = "No Data";

    if (data["windSpeedAvg"] !== null)
    {
        document.getElementById("wind-speed-avg").innerText =
            roundPlaces(data["windSpeedAvg"] * 2.237, 1) + " mph"; // m/s to mph
    }
    else document.getElementById("wind-speed-avg").innerText = "No Data";

    if (data["windSpeedMin"] !== null)
    {
        document.getElementById("wind-speed-min").innerText =
            roundPlaces(data["windSpeedMin"] * 2.237, 1) + " mph"; // m/s to mph
    }
    else document.getElementById("wind-speed-min").innerText = "No Data";

    if (data["windSpeedMax"] !== null)
    {
        document.getElementById("wind-speed-max").innerText =
            roundPlaces(data["windSpeedMax"] * 2.237, 1) + " mph"; // m/s to mph
    }
    else document.getElementById("wind-speed-max").innerText = "No Data";

    if (data["windDirAvg"] !== null)
    {
        document.getElementById("wind-dir-avg").innerHTML =
            "{0}° ({1})".format(data["windDirAvg"], degreesToCompass(data["windDirAvg"]));
    }
    else document.getElementById("wind-dir-avg").innerHTML = "No Data";

    if (data["windGustAvg"] !== null)
    {
        document.getElementById("wind-gust-avg").innerText = 
            roundPlaces(data["windGustAvg"] * 2.237, 1) + " mph"; // m/s to mph
    }
    else document.getElementById("wind-gust-avg").innerText = "No Data";

    if (data["windGustMin"] !== null)
    {
        document.getElementById("wind-gust-min").innerText =
            roundPlaces(data["windGustMin"] * 2.237, 1) + " mph"; // m/s to mph
    }
    else document.getElementById("wind-gust-min").innerText = "No Data";

    if (data["windGustMax"] !== null)
    {
        document.getElementById("wind-gust-max").innerText = 
            roundPlaces(data["windGustMax"] * 2.237, 1) + " mph"; // m/s to mph
    }
    else document.getElementById("wind-gust-max").innerText = "No Data";

    if (data["sunDurTtl"] !== null)
    {
        document.getElementById("sun-dur-ttl").innerHTML
            = roundPlaces(data["sunDurTtl"] / 60 / 60, 2) + " hr"; // sec to hr
    }
    else document.getElementById("sun-dur-ttl").innerHTML = "No Data";

    if (data["rainfallTtl"] !== null)
        document.getElementById("rainfall-ttl").innerText = data["rainfallTtl"] + " mm";
    else document.getElementById("rainfall-ttl").innerText = "No Data";

    if (data["mslPresAvg"] !== null)
        document.getElementById("msl-pres-avg").innerText = data["mslPresAvg"] + " hPa";
    else document.getElementById("msl-pres-avg").innerText = "No Data";

    if (data["mslPresMin"] !== null)
        document.getElementById("msl-pres-min").innerText = data["mslPresMin"] + " hPa";
    else document.getElementById("msl-pres-min").innerText = "No Data";

    if (data["mslPresMax"] !== null)
        document.getElementById("msl-pres-max").innerText = data["mslPresMax"] + " hPa";
    else document.getElementById("msl-pres-max").innerText = "No Data";
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