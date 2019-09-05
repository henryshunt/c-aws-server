var isLoading = false;
var requestedTime = null;
var updaterTimeout = null;
var datePicker = null;

var openGraphs = ["temperature"];
var graphsLoaded = 0;
var graphs = {
    "temperature": null, "humidity": null, "wind": null,
    "direction": null, "sunshine": null, "rainfall": null, "pressure": null,
    "soil": null
};
var graphFields = {
    "temperature": "AirT,ExpT,DewP",
    "humidity": "RelH", "wind": "WSpd,WGst",
    "direction": "WDir",
    "sunshine": "SunD",
    "rainfall": "Rain",
    "pressure": "MSLP",
    "soil": "ST10,ST30,ST00"
};

// Make graphs taller when wider
$(window).resize(function() {
    if (isLoading === true) return;
    graphHeightCheck();
});

$(document).ready(function() {
    var graphKeys = Object.keys(graphs);
    for (var i = 0; i < graphKeys.length; i++)
        graphs[graphKeys[i]] = setUpGraph(graphKeys[i]);
    updateData(true);
});


function setUpGraph(graph) {
    var options = {
        showPoint: false, lineSmooth: false, height: 400,
        height: $(".main").width() > 1050 ? 500 : 400,

        axisY: {
            offset: 38,
            labelInterpolationFnc: function(value) {
                if (graph === "direction") return roundPlaces(value, 0);
                else if (graph === "sunshine") return roundPlaces(value, 2);
                else if (graph === "rainfall") return roundPlaces(value, 2);
                else return roundPlaces(value, 1);
            }
        },

        axisX: {
            type: Chartist.FixedScaleAxis, divisor: 8, offset: 20,    
            labelInterpolationFnc: function(value) {
                return moment.unix(value).utc().tz(awsTimeZone).format("HH:mm");
            }
        }
    };

    if (graph === "direction") {
        options.axisY.type = Chartist.FixedScaleAxis;
        options.axisY.divisor = 8;
        options.axisY.low = 0;
        options.axisY.high = 360;
        options.showPoint = true;
        options.showLine = false;
    }

    return new Chartist.Line("#graph_" + graph, null, options);
}

function updateData(autoUpdate) {
    isLoading = true;
    clearTimeout(updaterTimeout);
    graphsLoaded = 0;
    
    // Update loaded time and set timeout for next refresh
    if (autoUpdate === true) {
        requestedTime = moment().utc().millisecond(0).second(0);
        updaterTimeout = setInterval(function() {
            updateData(true);
        }, 300000);
    }

    var localTime = moment(requestedTime).tz(awsTimeZone);
    document.getElementById("scroller_time").innerHTML = localTime.format(
        "DD/MM/YYYY" + (autoUpdate === true ? " ([at] HH:mm)" : ""));

    // Reload data for open graphs
    if (openGraphs.length > 0) {
        for (var i = 0; i < openGraphs.length; i++)
            loadGraphData(openGraphs[i], true);
    } else isLoading = false;
}

function loadGraphData(graph, check) {
    var url = "data/graph-day.php?time=" + requestedTime.format(
        "YYYY-MM-DD[T]HH-mm-ss") + "&fields=" + graphFields[graph];
    
    var localTime = moment(requestedTime).tz(awsTimeZone);
    var xEnd = moment(localTime).add(1, "day").hour(0).minute(0).unix();
    var xStart = moment(localTime).hour(0).minute(0).unix();

    // Draw new graph
    var options = graphs[graph].options;
    options.axisX.low = xStart;
    options.axisX.high = xEnd;

    $.getJSON(url, function(response) {
        if (response !== "1") {
            graphs[graph].update({ series: response }, options);

            if (check === true) {
                graphsLoaded += 1;
                if (graphsLoaded === openGraphs.length) {
                    graphHeightCheck();
                    isLoading = false;
                }
            } else openGraphs.push(graph);
        } else requestError();

    }).fail(requestError = function() {
        graphs[graph].update({ series: null }, options);
        
        if (check === true) {
            graphsLoaded += 1;
            if (graphsLoaded === openGraphs.length) {
                graphHeightCheck();
                isLoading = false;
            }
        } else openGraphs.push(graph);
    });
}


function scrollerLeft() {
    if (datePicker !== null) datePicker.close();
    if (isLoading === false) {
        requestedTime.subtract(1, "days");
        scrollerChange();
    }
}

function scrollerRight() {
    if (datePicker !== null) datePicker.close();
    if (isLoading === false) {
        requestedTime.add(1, "days");
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
            localTime.get("month"), localTime.get("date"));

        datePicker = flatpickr("#scroller_time", {
            defaultDate: initTime, disableMobile: true,

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
        minute: 0, second: 0
    });

    datePicker.close();

    // Submit if selected date different from loaded date
    var localSel = moment(selTime).tz(awsTimeZone);
    var localReq = moment(requestedTime).tz(awsTimeZone);

    if (localSel.format("DD/MM/YYYY") !== localReq.format("DD/MM/YYYY")) {
        requestedTime = moment(selTime);
        scrollerChange();
    }
}

function pickerCancel() {
    datePicker.close();
}

function scrollerChange() {
    var utc = moment().utc().millisecond(0).second(0);
    var localUtc = moment(utc).tz(awsTimeZone);
    var localReq = moment(requestedTime).tz(awsTimeZone);

    // Load data and start auto update if needed
    updateData(localUtc.format("DD/MM/YYYY") === localReq.format(
        "DD/MM/YYYY") ? true : false, true);
}