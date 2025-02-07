var isLoading = false;
var requestedTime = null;
var updaterTimeout = null;
var datePicker = null;

$(document).ready(function() {
    updateData(true, false);
});


function updateData(autoUpdate, abs) {
    isLoading = true;
    clearTimeout(updaterTimeout);

    document.getElementById("item_CImg").src = "data:image/gif;base64,"
        + "R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7";

    // Update loaded time and set timeout for next refresh
    if (autoUpdate === true) {
        requestedTime = moment().utc().millisecond(0).second(0);
        requestedMin = requestedTime.get("minute").toString();

        // Go back to last five minute interval
        while (!requestedMin.endsWith("0") && !requestedMin.endsWith("5")) {
            requestedTime.subtract(1, "minute");
            requestedMin = requestedTime.get("minute").toString();
        }

        updaterTimeout = setInterval(function() {
            updateData(true, false);
        }, 300000);
    }

    loadNewData(abs);
}

function loadNewData(abs) {
    var url = "data/camera.php?time=" + requestedTime.format(
        "YYYY-MM-DD[T]HH-mm-ss");
    if (abs === true) url += "&abs=1";

    $.ajax({
        dataType: "json", url: url,
        success: function (data) {
            data === "1" ? requestError() : processData(data);
        },

        error: requestError = function() {
            var localTime = moment(requestedTime).tz(awsTimeZone);
            document.getElementById("scroller_time").innerHTML
                = localTime.format("DD/MM/YYYY [at] HH:mm");
            
            document.getElementById("item_SRis").innerHTML = "No Data";
            document.getElementById("item_SSet").innerHTML = "No Data";
            document.getElementById("item_Noon").innerHTML = "No Data";
            document.getElementById("item_CImg").src = "data:image/gif;base64,"
                + "R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7";
            isLoading = false;
        }
    });
}

function processData(data) {
    var utc = moment.utc(data["Time"], "YYYY-MM-DD HH:mm:ss");
    var localTime = moment(utc).tz(awsTimeZone);

    document.getElementById("scroller_time").innerHTML
        = localTime.format("DD/MM/YYYY [at] HH:mm");
    requestedTime = moment(utc);

    if (data["SRis"] !== null) {
        var utc = moment.utc(data["SRis"], "YYYY-MM-DD HH:mm:ss");
        var formatted = utc.tz(awsTimeZone).format("HH:mm");
        document.getElementById("item_SRis").innerHTML = formatted;
    } else document.getElementById("item_SRis").innerHTML = "No Data";

    if (data["SSet"] !== null) {
        var utc = moment.utc(data["SSet"], "YYYY-MM-DD HH:mm:ss");
        var formatted = utc.tz(awsTimeZone).format("HH:mm");
        document.getElementById("item_SSet").innerHTML = formatted;
    } else document.getElementById("item_SSet").innerHTML = "No Data";

    if (data["Noon"] !== null) {
        var utc = moment.utc(data["Noon"], "YYYY-MM-DD HH:mm:ss");
        var formatted = utc.tz(awsTimeZone).format("HH:mm");
        document.getElementById("item_Noon").innerHTML = formatted;
    } else document.getElementById("item_Noon").innerHTML = "No Data";

    if (data["CImg"] === null) {
        document.getElementById("item_CImg")
            .src = "resources/images/no-camera.png";
    } else document.getElementById("item_CImg").src = data["CImg"];
    isLoading = false;
}


function scrollerLeft() {
    if (datePicker !== null) datePicker.close();
    if (isLoading === false) {
        requestedTime.subtract(5, "minutes");
        scrollerChange();
    }
}

function scrollerRight() {
    if (datePicker !== null) datePicker.close();
    if (isLoading === false) {
        requestedTime.add(5, "minutes");
        scrollerChange();
    }
}

function pickerOpen() {
    if (datePicker !== null) {
        datePicker.close();
        return;
    }

    if (isLoading === false) {
        var localTime = moment(requestedTime).tz(awsTimeZone);
        var initTime = new Date(localTime.get("year"), localTime.get("month"),
            localTime.get("date"), localTime.get("hour"),
            localTime.get("minute"), 0);

        datePicker = flatpickr("#scroller_time", {
            enableTime: true, time_24hr: true, defaultDate: initTime,
            disableMobile: true,

            onClose: function() {
                datePicker.destroy();
                datePicker = null;
            }
        });
        
        datePicker.open();
    }
}

function pickerSubmit() {
    var selTime = datePicker.selectedDates[0];
    selTime = moment.utc({
        year: selTime.getUTCFullYear(), month: selTime.getUTCMonth(),
        day: selTime.getUTCDate(), hour: selTime.getUTCHours(),
        minute: selTime.getUTCMinutes(), second: 0
    });

    datePicker.close();

    // Round selected time to nearest five minutes
    utcMinute = selTime.get("minute").toString();

    while (!utcMinute.endsWith("0") && !utcMinute.endsWith("5")) {
        if (utcMinute.endsWith("3") || utcMinute.endsWith("4") ||
            utcMinute.endsWith("8") || utcMinute.endsWith("9"))
            selTime.add(1, "minute");
        else selTime.subtract(1, "minute");
        
        utcMinute = selTime.get("minute").toString();
    }

    // Submit if selected time different from loaded time
    if (selTime.toString() != requestedTime.toString()) {
        requestedTime = moment(selTime);
        scrollerChange();
    }
}

function pickerCancel() {
    datePicker.close();
}

function scrollerChange() {
    var utc = moment().utc().millisecond(0).second(0);
    utcMinute = utc.get("minute").toString();

    // Go back to last five minute interval
    while (!utcMinute.endsWith("0") && !utcMinute.endsWith("5")) {
        utc.subtract(1, "minute");
        utcMinute = utc.get("minute").toString();
    }

    // Load data and start auto update if needed
    updateData(utc.toString() === requestedTime.toString()
        ? true : false, true);
}