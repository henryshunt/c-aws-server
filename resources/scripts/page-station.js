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

    var url = "data/station.php?time=" + requestedTime.format(
        "YYYY-MM-DD[T]HH-mm-00");
    if (absolute == true) { url += "&abs=1"; }

    $.ajax({
        dataType: "json", url: url,
        success: function (data) { processData(data); },

        error: requestError = function() {
            var localTime = moment(requestedTime).tz(awsTimeZone);
            document.getElementById("scroller_time").innerHTML
                = localTime.format("DD/MM/YYYY [at] HH:mm");

            document.getElementById("item_EncT").innerHTML = "No Data";
            document.getElementById("item_CPUT").innerHTML = "No Data";
            isLoading = false;
        }
    });

    loadGraphData();
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
    displayValue(data["EncT"], "item_EncT", "°C", 1);
    displayValue(data["CPUT"], "item_CPUT", "°C", 1);
    isLoading = false;
}

function loadGraphData() {
    var url = "data/graph-station.php?time=" + requestedTime.format(
        "YYYY-MM-DD[T]HH-mm-00") + "&fields=EncT,CPUT";
    
    var xEnd = moment(requestedTime).tz(awsTimeZone).unix();
    var xStart = moment(requestedTime).subtract(6, "hour").tz(awsTimeZone).unix();

    $.getJSON(url, function(response) {
        var options = graph.options;
        options.axisX.low = xStart; options.axisX.high = xEnd;
        graph.update({ series: response }, options);

    }).fail(function() {
        var options = graph.options;
        delete options.axisX.low; delete options.axisX.high;
        graph.update({ series: null }, options);
    });
}

function send_command(command) {
    var dialog = null;
    if (command == "shutdown" || command == "restart") {
        dialog = confirm("Are you sure you wish to send this command?");
    }

    if (dialog == true) {
        $.get("routines/station.php?cmd=" + command);
        
        if (command == "shutdown" || command == "restart") {
            alert("Power command will activate between the seconds :35 and :55.");
        }
    }
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