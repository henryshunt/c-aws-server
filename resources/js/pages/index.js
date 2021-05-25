let isColumned = false;
let dataGroups = [];
let resizeObserver = null;
let isLoading = false;
let dataTime = null;
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

    for (const dataGroup of document.getElementById("groups").children)
        dataGroups.push(dataGroup);

    resizeObserver = new ResizeObserver(entries => setColumns(entries[0]));
    resizeObserver.observe(document.getElementById("groups"));

    updateData(luxon.DateTime.utc(), true, true);
});

function setColumns(entry)
{
    if ((entry.contentRect.width >= 690 && isColumned) ||
        (entry.contentRect.width < 690 && !isColumned))
    {
        return;
    }

    while (document.getElementById("groups").children.length > 0)
        document.getElementById("groups").children[0].remove();

    document.getElementById("groups").classList.remove("groups-columned");
    
    if (entry.contentRect.width >= 690)
    {
        const column0 = document.createElement("div");
        column0.className = "groups-column";
        document.getElementById("groups").appendChild(column0);
        const column1 = document.createElement("div");
        column1.className = "groups-column";
        document.getElementById("groups").appendChild(column1);
        
        for (const group of dataGroups)
        {
            if (column0.children.length === 0 ||
                column0.offsetHeight <= column1.offsetHeight)
            {
                column0.appendChild(group);
            }
            else column1.appendChild(group);
        }

        document.getElementById("groups").classList.add("groups-columned");
        isColumned = true;
    }
    else
    {
        for (const group of dataGroups)
            document.getElementById("groups").append(group);

        isColumned = false;
    }
}


function updateData(time, auto, autoUpdate)
{
    isLoading = true;
    clearTimeout(updateTimeout);

    document.getElementById("scroller-left-btn").disabled = true;
    document.getElementById("scroller-right-btn").disabled = true;
    document.getElementById("scroller-time-btn").disabled = true;

    dataTime = time;

    if (autoUpdate)
    {
        updateTimeout = setInterval(() =>
            updateData(luxon.DateTime.utc(), true, true), 60000);
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
        let url = "api.php/observations/{0}?extras=true".format(
            dataTime.toFormat("yyyy-LL-dd'T'HH-mm-00"));
    
        if (auto)
            url += "&auto=true";

        getJson(url)
            .then(data =>
            {
                if (auto)
                    dataTime = luxon.DateTime.fromSQL(data["time"], { zone: "UTC" });

                displayData(data);
                resolve();
            })
            .catch(() =>
            {
                document.getElementById("scroller-time-btn").innerHTML
                    = dataTime.setZone(awsTimeZone).toFormat("dd/LL/yyyy 'at' HH:mm");
                
                document.getElementById("air-temp").innerHTML = "No Data";
                document.getElementById("rel-hum").innerHTML = "No Data";
                document.getElementById("dew-point").innerHTML = "No Data";
                document.getElementById("wind-speed").innerHTML = "No Data";
                document.getElementById("wind-dir").innerHTML = "No Data";
                document.getElementById("wind-gust").innerHTML = "No Data";
                document.getElementById("rainfall").innerHTML = "No Data";
                document.getElementById("rainfall-ph").innerHTML = "No Data";
                document.getElementById("sun-dur").innerHTML = "No Data";
                document.getElementById("sun-dur-ph").innerHTML = "No Data";
                document.getElementById("sta-pres").innerHTML = "No Data";
                document.getElementById("msl-pres").innerHTML = "No Data";
                document.getElementById("pres-tend").innerHTML = "No Data";
                resolve();
            });
    });
}

function displayData(data)
{
    document.getElementById("scroller-time-btn").innerHTML = 
        dataTime.setZone(awsTimeZone).toFormat("dd/LL/yyyy 'at' HH:mm");

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
    {
        document.getElementById("wind-speed").innerText =
            roundPlaces(data["windSpeed"] * 2.237, 1) + " mph"; // m/s to mph
    }
    else document.getElementById("wind-speed").innerText = "No Data";

    if (data["windDir"] !== null)
    {
        document.getElementById("wind-dir").innerHTML = 
            "{0}° ({1})".format(data["windDir"], degreesToCompass(data["windDir"]));
    }
    else document.getElementById("wind-dir").innerHTML = "No Data";

    if (data["windGust"] !== null)
    {
        document.getElementById("wind-gust").innerText =
            roundPlaces(data["windGust"] * 2.237, 1) + " mph"; // m/s to mph
    }
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

    if (data["pressureTendency"] !== null)
    {
        let formatted = (data["pressureTendency"] > 0 ? "+" : "") +
            data["pressureTendency"];
            
        document.getElementById("pres-tend").innerHTML = formatted + " hpa";
    }
    else document.getElementById("pres-tend").innerHTML = "No Data";
}


function onScrollerLeftBtnClick()
{
    if (!isLoading)
        scrollerChange(dataTime.minus({ minutes: 10 }));
}

function onScrollerRightBtnClick()
{
    if (!isLoading)
        scrollerChange(dataTime.plus({ minutes: 10 }));
}

function onScrollerTimeBtnClick()
{
    if (datePicker !== null)
        return;

    datePicker = flatpickr("#scroller-time-btn",
    {
        defaultDate: dataTime.toJSDate(),
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
        selected.day !== dataTime.day ||
        selected.month !== dataTime.month ||
        selected.year !== dataTime.year ||
        selected.hour !== dataTime.hour ||
        selected.minute !== dataTime.minute;

    if (different)
        scrollerChange(selected);
}

function scrollerChange(time)
{
    const now = luxon.DateTime.utc();

    const autoUpdate =
        time.day === now.day &&
        time.month === now.month &&
        time.year === now.year &&
        time.hour === now.hour &&
        time.minute === now.minute;

    updateData(time, false, autoUpdate);
}