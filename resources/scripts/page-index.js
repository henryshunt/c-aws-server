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
        requestedTime = luxon.DateTime.utc()
            .set({ seconds: 0, milliseconds: 0 });

        updateTimeout = setInterval(
            () => updateData(true, true), 60000);
    }

    loadData(requestedTime, auto)
        .then(() =>
        {
            document.getElementById("scroller-left-btn").disabled = false;
            document.getElementById("scroller-right-btn").disabled = false;
            document.getElementById("scroller-time-btn").disabled = false;
            isLoading = false;
        });
}

function loadData(time, auto)
{
    return new Promise(resolve =>
    {
        let url = "api.php/reports/{0}?extra=true".format(
            time.toFormat("yyyy-LL-dd'T'HH-mm-ss"));
    
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
                    = time.setZone(awsTimeZone).toFormat("dd/LL/yyyy 'at' HH:mm");
                
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

    displayValue(data["airTemp"], "item_AirT", "°C", 1);
    displayValue(data["relHum"], "item_RelH", "%", 1);
    displayValue(data["dewPoint"], "item_DewP", "°C", 1);
    displayValue(data["windSpeed"], "item_WSpd", " mph", 1);

    if (data["windDir"] !== null)
    {
        const formatted = "{0}° ({1})".format(
            data["windDir"], degreesToCompass(data["windDir"]));
        document.getElementById("item_WDir").innerHTML = formatted;
    }
    else document.getElementById("item_WDir").innerHTML = "No Data";

    displayValue(data["windGust"], "item_WGst", " mph", 1);
    displayValue(data["rainfall"], "item_Rain", " mm", 2);
    // displayValue(data["rainfall_pth"], "item_Rain_PHr", " mm", 2);
    displayValue(data["sun_dur"], "item_SunD", " sec", 0);

    // if (data["sun_dur_phr"] !== null)
    // {
    //     var formatted = moment.utc(
    //         data["sun_dur_phr"] * 1000).format("HH:mm:ss");
    //     document.getElementById("item_SunD_PHr").innerHTML = formatted;
    // }
    // else document.getElementById("item_SunD_PHr").innerHTML = "No Data";

    displayValue(data["staPres"], "item_StaP", " hPa", 1);
    displayValue(data["mslPres"], "item_MSLP", " hPa", 1);
    
    // if (data["sta_pres_pth"] !== null)
    // {
    //     var formatted = roundPlaces(data["sta_pres_pth"], 1) + " hpa";
    //     if (data["sta_pres_pth"] > 0) formatted = "+" + formatted;
    //     document.getElementById("item_StaP_PTH").innerHTML = formatted;
    // }
    // else document.getElementById("item_StaP_PTH").innerHTML = "No Data";
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