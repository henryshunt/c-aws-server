var isLoading = true;
var datePicker = null;
var requestedTime = null;
var updaterTimeout = null;

$(document).ready(function() {
    var options = {
        showPoint: false, lineSmooth: false, height: 400,

        axisY: {
            offset: 27,
            labelInterpolationFnc: function(value) {
                return value.toFixed(1);
            }
        },

        axisX: {
            type: Chartist.FixedScaleAxis, divisor: 6, offset: 20,    
            labelInterpolationFnc: function(value) {
                return moment.unix(
                    value).utc().tz(awsTimeZone).format("HH:mm");
            }
        }
    };

    graph = new Chartist.Line("#graph_temperature", null, options);
    updateData(true, false);
});

function updateData(autoUpdate, abs) {
    isLoading = true;
    clearTimeout(updaterTimeout);

    // Update loaded time and set timeout for next refresh
    if (autoUpdate === true) {
        requestedTime = moment().utc().millisecond(0).second(0);
        updaterTimeout = setInterval(function() {
            updateData(true, false);
        }, 60000);
    }

    loadNewData(abs);
}

function loadNewData(abs) {
    var url = "data/station.php?time=" + requestedTime.format(
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

            document.getElementById("item_EncT").innerHTML = "No Data";
            document.getElementById("item_CPUT").innerHTML = "No Data";
            loadGraphData();
        }
    });
}

function processData(data) {
    var utc = moment.utc(data["Time"], "YYYY-MM-DD HH:mm:ss");
    var localTime = moment(utc).tz(awsTimeZone);

    document.getElementById("scroller_time").innerHTML
        = localTime.format("DD/MM/YYYY [at] HH:mm");
    requestedTime = moment(utc);

    displayValue(data["EncT"], "item_EncT", "°C", 1);
    displayValue(data["CPUT"], "item_CPUT", "°C", 1);
    loadGraphData();
}

function loadGraphData() {
    var url = "data/graph-station.php?time=" + requestedTime.format(
        "YYYY-MM-DD[T]HH-mm-ss") + "&fields=EncT,CPUT";
    
    var xEnd = moment(requestedTime).tz(awsTimeZone).unix();
    var xStart = moment(
        requestedTime).subtract(6, "hour").tz(awsTimeZone).unix();

    // Draw new graph
    var options = graph.options;
    options.axisX.low = xStart;
    options.axisX.high = xEnd;

    $.getJSON(url, function(response) {
        if (response !== "1") {
            graph.update({ series: response }, options);
            isLoading = false;
        } else requestErrorGraph();

    }).fail(requestErrorGraph = function() {
        graph.update({ series: null }, options);
        isLoading = false;
    });
}

function send_command(command) {
    var dialog = null;
    if (command === "shutdown" || command === "restart")
        dialog = confirm("Are you sure you wish to send this command?");

    if (dialog === true) {
        $.get("routines/station.php?cmd=" + command);
        
        if (command === "shutdown" || command === "restart")
            alert("Power command will activate between the seconds :40 and :55.");
    }
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
        var initTime = new Date(localTime.get("year"), 
            localTime.get("month"), localTime.get("date"),
            localTime.get("hour"), localTime.get("minute"), 0);

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

    // Submit if selected time different from loaded time
    if (selTime.toString() !== requestedTime.toString()) {
        requestedTime = moment(selTime);
        scrollerChange();
    }  
}

function pickerCancel() {
    datePicker.close();
}

function scrollerChange() {
    var utc = moment().utc().millisecond(0).second(0);

    // Load data and start auto update if needed
    updateData(utc.toString() === requestedTime.toString()
        ? true : false, true);
}