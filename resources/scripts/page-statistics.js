function updateData(restartTimers, absolute) {
    isLoading = true;
    clearTimeout(updaterTimeout);

    if (restartTimers == true) {
        updaterTimeout = setInterval(function() {
            updateData(true, false);
        }, 60000);
    }

    getAndProcessData(restartTimers, absolute);
}

function getAndProcessData(setTime, absolute) {
    if (setTime == true) {
        requestedTime = moment().utc().millisecond(0).second(0);
    }

    var url = "data/statistics.php?time=" + requestedTime.format(
        "YYYY-MM-DD[T]HH-mm-00");
    if (absolute == true) { url += "&abs=1"; }

    $.ajax({
        dataType: "json", url: url,
        success: function (data) { processData(data, setTime); },

        error: requestError = function() {
            var localTime = moment(requestedTime).tz(awsTimeZone);

            if (setTime == true) {
                document.getElementById("scroller_time").innerHTML
                    = localTime.format("DD/MM/YYYY ([at] HH:mm)");
            } else {
                document.getElementById("scroller_time").innerHTML
                    = localTime.format("DD/MM/YYYY");
            }

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
            document.getElementById("item_ST10_Avg").innerHTML = "No Data";
            document.getElementById("item_ST10_Min").innerHTML = "No Data";
            document.getElementById("item_ST10_Max").innerHTML = "No Data";
            document.getElementById("item_ST30_Avg").innerHTML = "No Data";
            document.getElementById("item_ST30_Min").innerHTML = "No Data";
            document.getElementById("item_ST30_Max").innerHTML = "No Data";
            document.getElementById("item_ST00_Avg").innerHTML = "No Data";
            document.getElementById("item_ST00_Min").innerHTML = "No Data";
            document.getElementById("item_ST00_Max").innerHTML = "No Data";
            isLoading = false;
        }
    });
}

function processData(data, showTime) {
    if (data["Time"] == null) {
        requestError();
        return;
    }

    var utc = moment.utc(data["Time"], "YYYY-MM-DD HH:mm:ss");
    var localTime = moment(utc).tz(awsTimeZone);

    if (showTime == true) {
        document.getElementById("scroller_time").innerHTML
            = localTime.format("DD/MM/YYYY ([at] HH:mm)");
    } else {
        document.getElementById("scroller_time").innerHTML
            = localTime.format("DD/MM/YYYY");
    }
    
    requestedTime = moment(utc);
    displayValue(data["AirT_Avg"], "item_AirT_Avg", "°C", 1);
    displayValue(data["AirT_Min"], "item_AirT_Min", "°C", 1);
    displayValue(data["AirT_Max"], "item_AirT_Max", "°C", 1);
    displayValue(data["RelH_Avg"], "item_RelH_Avg", "%", 1);
    displayValue(data["RelH_Min"], "item_RelH_Min", "%", 1);
    displayValue(data["RelH_Max"], "item_RelH_Max", "%", 1);
    displayValue(data["DewP_Avg"], "item_DewP_Avg", "°C", 1);
    displayValue(data["DewP_Min"], "item_DewP_Min", "°C", 1);
    displayValue(data["DewP_Max"], "item_DewP_Max", "°C", 1);
    displayValue(data["WSpd_Avg"], "item_WSpd_Avg", " mph", 1);
    displayValue(data["WSpd_Min"], "item_WSpd_Min", " mph", 1);
    displayValue(data["WSpd_Max"], "item_WSpd_Max", " mph", 1);

    if (data["WDir_Avg"] != null) {
        var formatted = roundPlaces(data["WDir_Avg"], 0)
            + "° (" + degreesToCompass(data["WDir_Avg"]) + ")"
        document.getElementById("item_WDir_Avg").innerHTML = formatted;
    } else { document.getElementById("item_WDir_Avg").innerHTML = "No Data"; }

    displayValue(data["WGst_Avg"], "item_WGst_Avg", " mph", 1);
    displayValue(data["WGst_Min"], "item_WGst_Min", " mph", 1);
    displayValue(data["WGst_Max"], "item_WGst_Max", " mph", 1);

    if (data["SunD_Ttl"] != null) {
        var formatted = moment.utc(data["SunD_Ttl"] * 1000).format("HH:mm:ss");
        var formatted2 = data["SunD_Ttl"] / 60 / 60;
        document.getElementById("item_SunD_Ttl").innerHTML
            = formatted + " (" + roundPlaces(formatted2, 1) + " hrs)";
    } else { document.getElementById("item_SunD_Ttl").innerHTML = "No Data"; }

    displayValue(data["Rain_Ttl"], "item_Rain_Ttl", " mm", 2);
    displayValue(data["MSLP_Avg"], "item_MSLP_Avg", " hPa", 1);
    displayValue(data["MSLP_Min"], "item_MSLP_Min", " hPa", 1);
    displayValue(data["MSLP_Max"], "item_MSLP_Max", " hPa", 1);
    displayValue(data["ST10_Avg"], "item_ST10_Avg", "°C", 1);
    displayValue(data["ST10_Min"], "item_ST10_Min", "°C", 1);
    displayValue(data["ST10_Max"], "item_ST10_Max", "°C", 1);
    displayValue(data["ST30_Avg"], "item_ST30_Avg", "°C", 1);
    displayValue(data["ST30_Min"], "item_ST30_Min", "°C", 1);
    displayValue(data["ST30_Max"], "item_ST30_Max", "°C", 1);
    displayValue(data["ST00_Avg"], "item_ST00_Avg", "°C", 1);
    displayValue(data["ST00_Min"], "item_ST00_Min", "°C", 1);
    displayValue(data["ST00_Max"], "item_ST00_Max", "°C", 1);
    isLoading = false;
}

function scrollerLeft() {
    if (datePicker != null) { datePicker.close(); }

    if (isLoading == false) {
        requestedTime.subtract(1, "days");
        scrollerChange();
    }
}

function scrollerRight() {
    if (datePicker != null) { datePicker.close(); }
    
    if (isLoading == false) {
        requestedTime.add(1, "days");
        scrollerChange();
    }
}

function scrollerChange() {
    var utc = moment().utc().millisecond(0).second(0);
    var localUtc = moment(utc).tz(awsTimeZone);
    var localReq = moment(requestedTime).tz(awsTimeZone);

    if (localUtc.format("DD/MM/YYYY") == localReq.format("DD/MM/YYYY")) {
        updateData(true, true)
    } else { updateData(false, true); }
}

function openPicker() {
    if (datePicker == null) {
        if (requestedTime != null && datePicker == null && isLoading == false) {
            var localTime = moment(requestedTime).tz(awsTimeZone);
            var initTime = new Date(localTime.get("year"), localTime.get("month"),
                localTime.get("date"));

            datePicker = flatpickr("#scroller_time", {
                defaultDate: initTime, disableMobile: true,
                onClose: function() { datePicker.destroy(); datePicker = null; }
            });
            
            datePicker.open();
        }
    } else { datePicker.close(); }
}

function pickerSubmit() {
    var selTime = datePicker.selectedDates[0];
    selTime = moment.utc({
        year: selTime.getUTCFullYear(), month: selTime.getUTCMonth(), day: 
        selTime.getUTCDate(), hour: selTime.getUTCHours(), minute:
        0, second: 0 });

    var localSel = moment(selTime).tz(awsTimeZone);
    var localReq = moment(requestedTime).tz(awsTimeZone);

    if (localSel.format("DD/MM/YYYY") != localReq.format("DD/MM/YYYY")) {
        requestedTime = moment(selTime);
        scrollerChange();
    }; datePicker.close(); 
}

function pickerCancel() {
    datePicker.close();
}