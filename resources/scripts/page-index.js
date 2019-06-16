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

    var url = "data/now.php?time=" + requestedTime.format(
        "YYYY-MM-DD[T]HH-mm-00");
    if (absolute == true) { url += "&abs=1"; }

    $.ajax({
        dataType: "json", url: url,
        success: function (data) { processData(data); },

        error: requestError = function() {
            var localTime = moment(requestedTime).tz(awsTimeZone);
            document.getElementById("scroller_time").innerHTML
                = localTime.format("DD/MM/YYYY [at] HH:mm");
            
            document.getElementById("item_AirT").innerHTML = "No Data";
            document.getElementById("item_ExpT").innerHTML = "No Data";
            document.getElementById("item_RelH").innerHTML = "No Data";
            document.getElementById("item_DewP").innerHTML = "No Data";
            document.getElementById("item_WSpd").innerHTML = "No Data";
            document.getElementById("item_WDir").innerHTML = "No Data";
            document.getElementById("item_WGst").innerHTML = "No Data";
            document.getElementById("item_SunD").innerHTML = "No Data";
            document.getElementById("item_SunD_PHr").innerHTML = "No Data";
            document.getElementById("item_Rain").innerHTML = "No Data";
            document.getElementById("item_Rain_PHr").innerHTML = "No Data";
            document.getElementById("item_StaP").innerHTML = "No Data";
            document.getElementById("item_MSLP").innerHTML = "No Data";
            document.getElementById("item_StaP_PTH").innerHTML = "No Data";
            document.getElementById("item_ST10").innerHTML = "No Data";
            document.getElementById("item_ST30").innerHTML = "No Data";
            document.getElementById("item_ST00").innerHTML = "No Data";
            isLoading = false;
        }
    });
}

function processData(data) {
    if (data["Time"] == null) {
        requestError();
        return;
    }
    
    var utc = moment.utc(data["Time"], "YYYY-MM-DD HH:mm:ss");
    var localTime = moment(utc).tz(awsTimeZone);

    document.getElementById("scroller_time").innerHTML
        = localTime.format("DD/MM/YYYY [at] HH:mm");

    requestedTime = moment(utc);
    displayValue(data["AirT"], "item_AirT", "°C", 1);
    displayValue(data["ExpT"], "item_ExpT", "°C", 1);
    displayValue(data["RelH"], "item_RelH", "%", 1);
    displayValue(data["DewP"], "item_DewP", "°C", 1);
    displayValue(data["WSpd"], "item_WSpd", " mph", 1);

    if (data["WDir"] != null) {
        var formatted = data["WDir"]
            .toFixed(0) + "° (" + degreesToCompass(data["WDir"]) + ")"
        document.getElementById("item_WDir").innerHTML = formatted;
    } else { document.getElementById("item_WDir").innerHTML = "No Data"; }

    displayValue(data["WGst"], "item_WGst", " mph", 1);
    displayValue(data["SunD"], "item_SunD", " sec", 0);

    if (data["SunD_PHr"] != null) {
        var formatted = moment.utc(data["SunD_PHr"] * 1000).format("HH:mm:ss");
        document.getElementById("item_SunD_PHr").innerHTML = formatted;
    } else { document.getElementById("item_SunD_PHr").innerHTML = "No Data"; }

    displayValue(data["Rain"], "item_Rain", " mm", 2);
    displayValue(data["Rain_PHr"], "item_Rain_PHr", " mm", 2);
    displayValue(data["StaP"], "item_StaP", " hPa", 1);
    displayValue(data["MSLP"], "item_MSLP", " hPa", 1);
    
    if (data["StaP_PTH"] != null) {
         var formatted = data["StaP_PTH"].toFixed(1) + " hpa";
         if (data["StaP_PTH"] > 0) { formatted = "+" + formatted; }
         document.getElementById("item_StaP_PTH").innerHTML = formatted;
    } else { document.getElementById("item_StaP_PTH").innerHTML = "No Data"; }
    
    displayValue(data["ST10"], "item_ST10", "°C", 1);
    displayValue(data["ST30"], "item_ST30", "°C", 1);
    displayValue(data["ST00"], "item_ST00", "°C", 1);
    isLoading = false;
}

function scrollerLeft() {
    if (datePicker != null) { datePicker.close(); }

    if (isLoading == false) {
        requestedTime.subtract(5, "minutes");
        scrollerChange();
    }
}

function scrollerRight() {
    if (datePicker != null) { datePicker.close(); }

    if (isLoading == false) {
        requestedTime.add(5, "minutes");
        scrollerChange();
    }
}

function scrollerChange() {
    var utc = moment().utc().millisecond(0).second(0);

    if (utc.toString() == requestedTime.toString()) {
        updateData(true, true)
    } else { updateData(false, true); }
}

function openPicker() {
    if (datePicker == null) {
        if (requestedTime != null && isLoading == false) {
            var localTime = moment(requestedTime).tz(awsTimeZone);
            var initTime = new Date(localTime.get("year"), localTime.get("month"),
                localTime.get("date"), localTime.get("hour"), localTime.get("minute"), 0);

            datePicker = flatpickr("#scroller_time", {
                enableTime: true, time_24hr: true, defaultDate: initTime, disableMobile: true,
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
        selTime.getUTCMinutes(), second: 0 });

    if (selTime.toString() != requestedTime.toString()) {
        requestedTime = moment(selTime);
        scrollerChange();
    }; datePicker.close();  
}

function pickerCancel() {
    datePicker.close();
}