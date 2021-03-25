let isLoading = false;
let requestedTime = null;
let updateTimeout = null;
let datePicker = null;

window.addEventListener("load", () =>
{
    document.getElementById("scroller-left-btn")
        .addEventListener("click", onScrollerLeftBtnClick);
    document.getElementById("scroller-right-btn")
        .addEventListener("click", onScrollerRightBtnClick);
    document.getElementById("scroller-time-btn")
        .addEventListener("click", onScrollerTimeBtnClick);

    setColumns();
    updateData(true, true);
});

function setColumns()
{
    const column0 = document.createElement("div");
    column0.className = "column column0";
    const column1 = document.createElement("div");
    column1.className = "column";
    
    let column = 0;
    while (document.getElementById("groups").children.length > 0)
    {
        const group = document.getElementById("groups").children[0];

        if (column === 0)
        {
            column0.appendChild(group);
            column = 1;
        }
        else
        {
            column1.appendChild(group);
            column = 0;
        }
    }

    column0.children[column0.children.length - 1].classList.add("final");
    document.getElementById("groups").appendChild(column0);
    column1.children[column1.children.length - 1].classList.add("final");
    document.getElementById("groups").appendChild(column1);
    document.getElementById("groups").classList.add("groups-split");
}


function updateData(autoUpdate, auto)
{
    isLoading = true;
    clearTimeout(updateTimeout);

    document.getElementById("scroller-left-btn").disabled = true;
    document.getElementById("scroller-right-btn").disabled = true;
    document.getElementById("scroller-time-btn").disabled = true;

    if (autoUpdate)
    {
        requestedTime = luxon.DateTime.utc();
        updateTimeout = setInterval(() => updateData(true, true), 60000);
    }

    loadData(auto)
        .then(() =>
        {
            document.getElementById("scroller-left-btn").disabled = false;
            document.getElementById("scroller-right-btn").disabled = false;
            document.getElementById("scroller-time-btn").disabled = false;
            isLoading = false;
        });
}

function loadData(auto)
{
    return new Promise(resolve =>
    {
        let url = "api.php/reports/{0}?extras=true".format(
            requestedTime.toFormat("yyyy-LL-dd'T'HH-mm-00"));
    
        if (auto)
            url += "&auto=true";

        getJson(url)
            .then(data =>
            {
                displayData(data);
                resolve();
            })
            .catch(() =>
            {
                document.getElementById("scroller-time-btn").innerHTML
                    = requestedTime.setZone(awsTimeZone).toFormat("dd/LL/yyyy 'at' HH:mm");
                
                document.getElementById("item_AirT").innerHTML = "No Data";
                document.getElementById("item_RelH").innerHTML = "No Data";
                document.getElementById("item_DewP").innerHTML = "No Data";
                document.getElementById("item_WSpd").innerHTML = "No Data";
                document.getElementById("item_WDir").innerHTML = "No Data";
                document.getElementById("item_WGst").innerHTML = "No Data";
                document.getElementById("item_Rain").innerHTML = "No Data";
                document.getElementById("item_Rain_PHr").innerHTML = "No Data";
                document.getElementById("item_SunD").innerHTML = "No Data";
                document.getElementById("item_SunD_PHr").innerHTML = "No Data";
                document.getElementById("item_StaP").innerHTML = "No Data";
                document.getElementById("item_MSLP").innerHTML = "No Data";
                document.getElementById("item_StaP_PTH").innerHTML = "No Data";
                resolve();
            });
    });
}

function displayData(data)
{
    requestedTime = luxon.DateTime.fromSQL(data["time"], { zone: "UTC" });

    document.getElementById("scroller-time-btn").innerHTML = 
        requestedTime.setZone(awsTimeZone).toFormat("dd/LL/yyyy 'at' HH:mm");

    if (data["airTemp"] !== null)
        document.getElementById("air-temp").innerText = data["airTemp"] + "°C";
    else document.getElementById("air-temp").innerText = "No Data";
    
    if (data["relHum"] !== null)
        document.getElementById("rel-hum").innerText = data["relHum"] + "%";
    else document.getElementById("rel-hum").innerText = "No Data";

    if (data["dewPoint"] !== null)
        document.getElementById("dew-point").innerText = data["dewPoint"] + "°C";
    else document.getElementById("dew-point").innerText = "No Data";

    if (data["windSpeed"] !== null)
        document.getElementById("wind-speed").innerText = data["windSpeed"] + " mph";
    else document.getElementById("wind-speed").innerText = "No Data";

    if (data["windDir"] !== null)
    {
        const formatted = "{0}° ({1})".format(data["windDir"],
            degreesToCompass(data["windDir"]));
        document.getElementById("wind-dir").innerHTML = formatted;
    }
    else document.getElementById("wind-dir").innerHTML = "No Data";

    if (data["windGust"] !== null)
        document.getElementById("wind-gust").innerText = data["windGust"] + " mph";
    else document.getElementById("wind-gust").innerText = "No Data";

    if (data["rainfall"] !== null)
        document.getElementById("rainfall").innerText = data["rainfall"] + " mm";
    else document.getElementById("rainfall").innerText = "No Data";

    if (data["rainfallPastHour"] !== null)
    {
        document.getElementById("rainfall-ph").innerText =
            data["rainfallPastHour"] + " mm";
    }
    else document.getElementById("rainfall-ph").innerText = "No Data";

    if (data["sunDur"] !== null)
        document.getElementById("sun-dur").innerText = data["sunDur"] + " sec";
    else document.getElementById("sun-dur").innerText = "No Data";

    if (data["sunDurPastHour"] !== null)
    {
        document.getElementById("sun-dur-ph").innerHTML =
            roundPlaces(data["sunDurPastHour"] / 60 / 60, 2) + " hr";
    }
    else document.getElementById("sun-dur-ph").innerHTML = "No Data";

    if (data["staPres"] !== null)
        document.getElementById("sta-pres").innerText = data["staPres"] + " hPa";
    else document.getElementById("sta-pres").innerText = "No Data";

    if (data["mslPres"] !== null)
        document.getElementById("msl-pres").innerText = data["mslPres"] + " hPa";
    else document.getElementById("msl-pres").innerText = "No Data";

    if (data["mslPresTendency"] !== null)
    {
        let formatted = data["mslPresTendency"] + " hpa";
        if (data["mslPresTendency"] > 0)
            formatted = "+" + formatted;
            
        document.getElementById("msl-pres-tend").innerHTML = formatted;
    }
    else document.getElementById("msl-pres-tend").innerHTML = "No Data";
}


function onScrollerLeftBtnClick()
{
    if (isLoading)
        return;
    
    requestedTime = requestedTime.minus({ minutes: 10 });
    scrollerChange();
}

function onScrollerRightBtnClick()
{
    if (isLoading)
        return;

    requestedTime = requestedTime.plus({ minutes: 10 });
    scrollerChange();
}

function onScrollerTimeBtnClick()
{
    if (datePicker !== null)
        return;

    datePicker = flatpickr("#scroller-time-btn",
    {
        defaultDate: requestedTime.toJSDate(),
        enableTime: true,
        time_24hr: true,
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
        selected.year !== requestedTime.year ||
        selected.hour !== requestedTime.hour ||
        selected.minute !== requestedTime.minute;

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
        requestedTime.year === now.year &&
        requestedTime.hour === now.hour &&
        requestedTime.minute === now.minute;

    updateData(autoUpdate, false);
}