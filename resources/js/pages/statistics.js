var isLoading = false;
var requestedTime = null;
var updateTimeout = null;
var datePicker = null;

window.addEventListener("load", () =>
{
    document.getElementById("scroller-left-btn")
        .addEventListener("click", onScrollerLeftBtnClick);
    document.getElementById("scroller-right-btn")
        .addEventListener("click", onScrollerRightBtnClick);
    document.getElementById("scroller-time-btn")
        .addEventListener("click", onScrollerTimeBtnClick);

    updateData(true);
});


function updateData(autoUpdate)
{
    isLoading = true;
    clearTimeout(updateTimeout);

    document.getElementById("scroller-left-btn").disabled = true;
    document.getElementById("scroller-right-btn").disabled = true;
    document.getElementById("scroller-time-btn").disabled = true;

    if (autoUpdate)
    {
        requestedTime = luxon.DateTime.fromObject({ zone: awsTimeZone });
        updateTimeout = setInterval(() => updateData(true), 60000);
    }

    loadData(autoUpdate)
        .then(() =>
        {
            document.getElementById("scroller-left-btn").disabled = false;
            document.getElementById("scroller-right-btn").disabled = false;
            document.getElementById("scroller-time-btn").disabled = false;
            isLoading = false;
        });
}

function loadData(showTime)
{
    return new Promise(resolve =>
    {
        let url = "api.php/statistics/daily/" +
            requestedTime.toFormat("yyyy-LL-dd");

        getJson(url)
            .then(data =>
            {
                displayData(data, showTime);
                resolve();
            })
            .catch(() =>
            {
                let timeStr = requestedTime.toFormat("dd/LL/yyyy");
                if (showTime)
                    timeStr += requestedTime.toFormat(" ('at' HH:mm)");

                document.getElementById("scroller-time-btn").innerHTML = timeStr;
                document.getElementById("item_AirT_Avg").innerHTML = "No Data";
                document.getElementById("item_AirT_Min").innerHTML = "No Data";
                document.getElementById("item_AirT_Max").innerHTML = "No Data";
                document.getElementById("item_RelH_Avg").innerHTML = "No Data";
                document.getElementById("item_RelH_Min").innerHTML = "No Data";
                document.getElementById("item_RelH_Max").innerHTML = "No Data";
                document.getElementById("item_DewP_Avg").innerHTML = "No Data";
                document.getElementById("item_DewP_Min").innerHTML = "No Data";
                document.getElementById("item_DewP_Max").innerHTML = "No Data";
                document.getElementById("item_WSpd_Avg").innerHTML = "No Data";
                document.getElementById("item_WSpd_Min").innerHTML = "No Data";
                document.getElementById("item_WSpd_Max").innerHTML = "No Data";
                document.getElementById("item_WDir_Avg").innerHTML = "No Data";
                document.getElementById("item_WGst_Avg").innerHTML = "No Data";
                document.getElementById("item_WGst_Min").innerHTML = "No Data";
                document.getElementById("item_WGst_Max").innerHTML = "No Data";
                document.getElementById("item_SunD_Ttl").innerHTML = "No Data";
                document.getElementById("item_Rain_Ttl").innerHTML = "No Data";
                document.getElementById("item_MSLP_Avg").innerHTML = "No Data";
                document.getElementById("item_MSLP_Min").innerHTML = "No Data";
                document.getElementById("item_MSLP_Max").innerHTML = "No Data";
                resolve();
            });
    });
}

function displayData(data, showTime)
{
    let timeStr = requestedTime.toFormat("dd/LL/yyyy");
    if (showTime)
        timeStr += requestedTime.toFormat(" ('at' HH:mm)");

    document.getElementById("scroller-time-btn").innerHTML = timeStr;

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

    if (data["windSpeedAvg"] !== null)
        document.getElementById("wind-speed-avg").innerText = data["windSpeedAvg"] + " mph";
    else document.getElementById("wind-speed-avg").innerText = "No Data";

    if (data["windSpeedMin"] !== null)
        document.getElementById("wind-speed-min").innerText = data["windSpeedMin"] + " mph";
    else document.getElementById("wind-speed-min").innerText = "No Data";

    if (data["windSpeedMax"] !== null)
        document.getElementById("wind-speed-max").innerText = data["windSpeedMax"] + " mph";
    else document.getElementById("wind-speed-max").innerText = "No Data";

    if (data["windDirAvg"] !== null)
    {
        const formatted = "{0}° ({1})".format(data["windDirAvg"],
            degreesToCompass(data["windDirAvg"]));
        document.getElementById("wind-dir-avg").innerHTML = formatted;
    }
    else document.getElementById("wind-dir-avg").innerHTML = "No Data";

    if (data["windGustAvg"] !== null)
        document.getElementById("wind-gust-avg").innerText = data["windGustAvg"] + " mph";
    else document.getElementById("wind-gust-avg").innerText = "No Data";

    if (data["windGustMin"] !== null)
        document.getElementById("wind-gust-min").innerText = data["windGustMin"] + " mph";
    else document.getElementById("wind-gust-min").innerText = "No Data";

    if (data["windGustMax"] !== null)
        document.getElementById("wind-gust-max").innerText = data["windGustMax"] + " mph";
    else document.getElementById("wind-gust-max").innerText = "No Data";

    if (data["sunDurTtl"] !== null)
    {
        document.getElementById("sun-dur-ttl").innerHTML
            = roundPlaces(data["sunDurTtl"] / 60 / 60, 2) + " hr";
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