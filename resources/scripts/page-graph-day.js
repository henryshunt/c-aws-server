var isLoading = true;
var datePicker = null;
var requestedTime = null;

var graphs = {
    "temperature": null, "humidity": null, "wind": null,
    "direction": null, "sunshine": null, "rainfall": null, "pressure": null,
    "soil": null
};
var graphFields = {
    "temperature": "AirT,ExpT,DewP", "humidity": "RelH", "wind": "WSpd,WGst",
    "direction": "WDir", "sunshine": "SunD", "rainfall": "Rain",
    "pressure": "MSLP", "soil": "ST10,ST30,ST00"
};

var openGraphs = ["temperature"];
var graphsLoaded = 0;
var updaterTimeout = null;

$(document).ready(function() {
    graphs["temperature"] = setUpGraph("temperature");
    graphs["humidity"] = setUpGraph("humidity");
    graphs["wind"] = setUpGraph("wind");
    graphs["direction"] = setUpGraph("direction");
    graphs["sunshine"] = setUpGraph("sunshine");
    graphs["rainfall"] = setUpGraph("rainfall");
    graphs["pressure"] = setUpGraph("pressure");
    graphs["soil"] = setUpGraph("soil");
    updateData(true);
});


function setUpGraph(graph) {
    var options = {
        showPoint: false, lineSmooth: false, height: 400,

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
            
function updateData(restartTimers) {
    isLoading = true;
    clearTimeout(updaterTimeout);

    // Create new timeout to handle next refresh
    if (restartTimers === true) {
        updaterTimeout = setInterval(function() {
            updateData(true);
        }, 300000);
    }

    getAndProcessData(restartTimers);
}

function getAndProcessData(setTime) {
    if (setTime === true)
        requestedTime = moment().utc().millisecond(0).second(0);
    graphsLoaded = 0;

    // If we're auto updating then show time of data in the date
    var localTime = moment(requestedTime).tz(awsTimeZone);
    if (setTime === true) {
        document.getElementById("scroller_time").innerHTML
            = localTime.format("DD/MM/YYYY ([at] HH:mm)");
    } else {
        document.getElementById("scroller_time").innerHTML
            = localTime.format("DD/MM/YYYY");
    }

    // Reload data for all open graphs
    if ($.inArray("temperature", openGraphs) !== -1)
        loadGraphData("temperature");
    if ($.inArray("humidity", openGraphs) !== -1)
        loadGraphData("humidity");
    if ($.inArray("wind", openGraphs) !== -1)
        loadGraphData("wind");
    if ($.inArray("direction", openGraphs) !== -1)
        loadGraphData("direction");
    if ($.inArray("sunshine", openGraphs) !== -1)
        loadGraphData("sunshine");
    if ($.inArray("rainfall", openGraphs) !== -1)
        loadGraphData("rainfall");
    if ($.inArray("pressure", openGraphs) !== -1)
        loadGraphData("pressure");
    if ($.inArray("soil", openGraphs) !== -1)
        loadGraphData("soil");

    if (openGraphs.length === 0) isLoading = false;
}

function loadGraphData(graph) {
    var url = "data/graph-day.php?time=" + requestedTime.format(
        "YYYY-MM-DD[T]HH-mm-ss") + "&fields=" + graphFields[graph];
    
    var localTime = moment(requestedTime).tz(awsTimeZone);
    var xEnd = moment(localTime).add(1, "day").hour(0).minute(0).unix();
    var xStart = moment(localTime).hour(0).minute(0).unix();

    // Draw new graph
    $.getJSON(url, function(response) {
        if (response !== "1") {
            var options = graphs[graph].options;
            options.axisX.low = xStart;
            options.axisX.high = xEnd;
            document.getElementById("graph_" + graph).style.display = "block";
            graphs[graph].update({ series: response }, options);

            graphsLoaded += 1;
            if (graphsLoaded === openGraphs.length) {
                isLoading = false;
                graphsLoaded = 0;
            }
        } else requestError();

    }).fail(requestError = function() {
        var options = graphs[graph].options;
        delete options.axisX.low;
        delete options.axisX.high;
        graphs[graph].update({ series: null }, options);
        
        graphsLoaded += 1;
        if (graphsLoaded === openGraphs.length) {
            isLoading = false;
            graphsLoaded = 0;
        }
    });
}

function scrollerLeft() {
    if (datePicker != null) datePicker.close();
    if (isLoading === false) {
        requestedTime.subtract(1, "days");
        scrollerChange();
    }
}

function scrollerRight() {
    if (datePicker != null) datePicker.close();
    if (isLoading === false) {
        requestedTime.add(1, "days");
        scrollerChange();
    }
}

function scrollerChange() {
    // Clear and reset all open graphs
    for (var graph in graphs) {
        var options = graphs[graph].options;
        delete options.axisX.low;
        delete options.axisX.high;
        graphs[graph].update({ series: null }, options);
    }
    
    var utc = moment().utc().millisecond(0).second(0);
    var localUtc = moment(utc).tz(awsTimeZone);
    var localReq = moment(requestedTime).tz(awsTimeZone);

    // If at current date then restart timers
    if (localUtc.format("DD/MM/YYYY") === localReq.format("DD/MM/YYYY"))
        updateData(true)
    else updateData(false);
}

function openPicker() {
    if (datePicker === null) {
        if (requestedTime !== null && isLoading === false) {
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
    } else datePicker.close();
}

function pickerSubmit() {
    var selTime = datePicker.selectedDates[0];
    selTime = moment.utc({
        year: selTime.getUTCFullYear(), month: selTime.getUTCMonth(),
        day: selTime.getUTCDate(), hour: selTime.getUTCHours(),
        minute: 0, second: 0 });

    var localSel = moment(selTime).tz(awsTimeZone);
    var localReq = moment(requestedTime).tz(awsTimeZone);

    // Submit if selected date different from current date
    if (localSel.format("DD/MM/YYYY") !== localReq.format("DD/MM/YYYY")) {
        requestedTime = moment(selTime);
        scrollerChange();
    }
    
    datePicker.close(); 
}

function pickerCancel() {
    datePicker.close();
}