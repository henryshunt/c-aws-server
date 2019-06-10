function updateData(restartTimers, absolute) {
    isLoading = true;
    clearTimeout(updaterTimeout);
    clearTimeout(countTimeout);

    if (restartTimers == true) {
        document.getElementById("item_update").innerHTML = "0";
    } else { document.getElementById("item_update").innerHTML = ""; }
    timeToUpdate = 300;

    if (restartTimers == true) {
        updaterTimeout = setInterval(function() {
            updateData(true, false);
        }, 300000);
            
        countTimeout = setInterval(function() {
            document.getElementById("item_update").innerHTML
                = --timeToUpdate;
        }, 1000);
    }

    getAndProcessData(restartTimers, absolute);
}

function getAndProcessData(setTime, absolute) {
    if (setTime == true) {
        requestedTime = moment().utc().millisecond(0).second(0);
        requestedMinute = requestedTime.get("minute").toString();

        while (!requestedMinute.endsWith("0") && !requestedMinute.endsWith("5")) {
            requestedTime.subtract(1, "minute");
            requestedMinute = requestedTime.get("minute").toString();
        }
    }

    var url = "data/camera.php?time=" + requestedTime.format(
        "YYYY-MM-DD[T]HH-mm-00");
    if (absolute == true) { url += "&abs=1"; }

    $.ajax({
        dataType: "json", url: url,
        success: function (data) { processData(data); },

        error: function() {
            var localTime = moment(requestedTime).tz(awsTimeZone);
            document.getElementById("item_data_time").innerHTML
                = localTime.format("HH:mm");
            document.getElementById("scroller_time").innerHTML
                = localTime.format("DD/MM/YYYY [at] HH:mm");
            
            document.getElementById("item_SRis").innerHTML = "no data";
            document.getElementById("item_SSet").innerHTML = "no data";
            document.getElementById("item_Noon").innerHTML = "no data";
            document.getElementById("item_CImg").src
                = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7";
            isLoading = false;
        }
    });
}

function processData(data) {
    var utc = moment.utc(data["Time"], "YYYY-MM-DD HH:mm:ss");
    var localTime = moment(utc).tz(awsTimeZone);

    document.getElementById("item_data_time").innerHTML
        = localTime.format("HH:mm");
    document.getElementById("scroller_time").innerHTML
        = localTime.format("DD/MM/YYYY [at] HH:mm");
    requestedTime = moment(utc);

    if (data["SRis"] != null) {
        var utc = moment.utc(data["SRis"], "YYYY-MM-DD HH:mm:ss");
        var formatted = utc.tz(awsTimeZone).format("HH:mm");
        document.getElementById("item_SRis").innerHTML = formatted;
    } else { document.getElementById("item_SRis").innerHTML = "no data"; }

    if (data["SSet"] != null) {
        var utc = moment.utc(data["SSet"], "YYYY-MM-DD HH:mm:ss");
        var formatted = utc.tz(awsTimeZone).format("HH:mm");
        document.getElementById("item_SSet").innerHTML = formatted;
    } else { document.getElementById("item_SSet").innerHTML = "no data"; }

    if (data["Noon"] != null) {
        var utc = moment.utc(data["Noon"], "YYYY-MM-DD HH:mm:ss");
        var formatted = utc.tz(awsTimeZone).format("HH:mm");
        document.getElementById("item_Noon").innerHTML = formatted;
    } else { document.getElementById("item_Noon").innerHTML = "no data"; }

    if (data["CImg"] != null) {
        document.getElementById("item_CImg").src = data["CImg"];
    } else {
        document.getElementById("item_CImg")
            .src = "resources/images/no-camera.png";
    }
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
    document.getElementById("item_CImg").src
        = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7";
        
    var utc = moment().utc().millisecond(0).second(0);
    utcMinute = utc.get("minute").toString();

    while (!utcMinute.endsWith("0") && !utcMinute.endsWith("5")) {
        utc.subtract(1, "minute");
        utcMinute = utc.get("minute").toString();
    }

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

    utcMinute = selTime.get("minute").toString();
    while (!utcMinute.endsWith("0") && !utcMinute.endsWith("5")) {
        if (utcMinute.endsWith("3") || utcMinute.endsWith("4") ||
            utcMinute.endsWith("8") || utcMinute.endsWith("9")) {
                selTime.add(1, "minute");
        } else { selTime.subtract(1, "minute"); }
        
        utcMinute = selTime.get("minute").toString();
    }

    if (selTime.toString() != requestedTime.toString()) {
        requestedTime = moment(selTime);
        scrollerChange();
    }; datePicker.close(); 
}

function pickerCancel() {
    datePicker.close();
}