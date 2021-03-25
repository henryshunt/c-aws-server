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
    displayValue(data["airTempAvg"], "item_AirT_Avg", "°C", 1);
    displayValue(data["airTempMin"], "item_AirT_Min", "°C", 1);
    displayValue(data["airTempMax"], "item_AirT_Max", "°C", 1);
    displayValue(data["relHumAvg"], "item_RelH_Avg", "%", 1);
    displayValue(data["relHumMin"], "item_RelH_Min", "%", 1);
    displayValue(data["relHumMax"], "item_RelH_Max", "%", 1);
    displayValue(data["dewPointAvg"], "item_DewP_Avg", "°C", 1);
    displayValue(data["dewPointMin"], "item_DewP_Min", "°C", 1);
    displayValue(data["dewPointMax"], "item_DewP_Max", "°C", 1);
    displayValue(data["windSpeedAvg"], "item_WSpd_Avg", " mph", 1);
    displayValue(data["windSpeedMin"], "item_WSpd_Min", " mph", 1);
    displayValue(data["windSpeedMax"], "item_WSpd_Max", " mph", 1);

    if (data["windDirAvg"] !== null)
    {
        const formatted = "{0}° ({1})".format(
            data["windDirAvg"], degreesToCompass(data["windDirAvg"]));
        document.getElementById("item_WDir_Avg").innerHTML = formatted;
    }
    else document.getElementById("item_WDir_Avg").innerHTML = "No Data";

    displayValue(data["windGustAvg"], "item_WGst_Avg", " mph", 1);
    displayValue(data["windGustMin"], "item_WGst_Min", " mph", 1);
    displayValue(data["windGustMax"], "item_WGst_Max", " mph", 1);

    // if (data["sunDurTtl"] !== null)
    // {
    //     var formatted = moment.utc(data["SunD_Ttl"] * 1000).format("HH:mm:ss");
    //     var formatted2 = data["SunD_Ttl"] / 60 / 60;
    //     document.getElementById("item_SunD_Ttl").innerHTML
    //         = formatted + " (" + roundPlaces(formatted2, 1) + " hrs)";
    // } else document.getElementById("item_SunD_Ttl").innerHTML = "No Data";

    displayValue(data["rainfallTtl"], "item_Rain_Ttl", " mm", 2);
    displayValue(data["mslPresAvg"], "item_MSLP_Avg", " hPa", 1);
    displayValue(data["mslPresMin"], "item_MSLP_Min", " hPa", 1);
    displayValue(data["mslPresMax"], "item_MSLP_Max", " hPa", 1);
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