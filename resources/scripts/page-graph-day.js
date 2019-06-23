function setupGraph(graph) {
    var options = {
        showPoint: false, lineSmooth: false, height: 350,

        axisY: {
            offset: 38,
            labelInterpolationFnc: function(value) {
                if (graph == "direction") { return roundPlaces(value, 0); }
                else if (graph == "sunshine") { return roundPlaces(value, 2); }
                else if (graph == "rainfall") { return roundPlaces(value, 2); }
                else { return roundPlaces(value, 1); }
            }
        },

        axisX: {
            type: Chartist.FixedScaleAxis, divisor: 8, offset: 20,    
            labelInterpolationFnc: function(value) {
                return moment.unix(value).utc().tz(awsTimeZone).format("HH:mm");
            }
        }
    };

    if (graph == "direction") {
        options.axisY.type = Chartist.FixedScaleAxis;
        options.axisY.divisor = 8;
        options.axisY.low = 0; options.axisY.high = 360;
        options.showPoint = true; options.showLine = false;
    }

    return new Chartist.Line("#graph_" + graph, null, options);
}

function updateData(restartTimers) {
    isLoading = true;
    clearTimeout(updaterTimeout);

    if (restartTimers == true) {
        updaterTimeout = setInterval(function() {
            updateData(true);
        }, 300000);
    }

    getAndProcessData(restartTimers);
}

function getAndProcessData(setTime) {
    if (setTime == true) {
        requestedTime = moment().utc().millisecond(0).second(0);
    }; graphsLoaded = 0;

    var localTime = moment(requestedTime).tz(awsTimeZone);
    if (setTime == true) {
        document.getElementById("scroller_time").innerHTML
            = localTime.format("DD/MM/YYYY ([at] HH:mm)");
    } else {
        document.getElementById("scroller_time").innerHTML
            = localTime.format("DD/MM/YYYY");
    }

    if ($.inArray("temperature", openGraphs) != -1)
    { loadGraphData("temperature", "AirT,ExpT,DewP"); }
    if ($.inArray("humidity", openGraphs) != -1)
    { loadGraphData("humidity", "RelH"); }
    if ($.inArray("wind", openGraphs) != -1)
    { loadGraphData("wind", "WSpd,WGst"); }
    if ($.inArray("direction", openGraphs) != -1)
    { loadGraphData("direction", "WDir"); }
    if ($.inArray("sunshine", openGraphs) != -1)
    { loadGraphData("sunshine", "SunD"); }
    if ($.inArray("rainfall", openGraphs) != -1)
    { loadGraphData("rainfall", "Rain"); }
    if ($.inArray("pressure", openGraphs) != -1)
    { loadGraphData("pressure", "MSLP"); }
    if ($.inArray("soil", openGraphs) != -1)
    { loadGraphData("soil", "ST10,ST30,ST00"); }

    if ($.inArray("temperature", openGraphs) == -1 &&
        $.inArray("humidity", openGraphs) == -1 &&
        $.inArray("wind", openGraphs) == -1 &&
        $.inArray("direction", openGraphs) == -1 &&
        $.inArray("sunshine", openGraphs) == -1 &&
        $.inArray("rainfall", openGraphs) == -1 &&
        $.inArray("pressure", openGraphs) == -1 &&
        $.inArray("soil", openGraphs) == -1) {
        isLoading = false;
    }
}

function loadGraphData(graph, fields) {
    var url = "data/graph-day.php?time=" + requestedTime.format(
        "YYYY-MM-DD[T]HH-mm-00") + "&fields=" + fields;
    
    var localTime = moment(requestedTime).tz(awsTimeZone);
    var xEnd = moment(localTime).add(1, "day").hour(0).minute(0).unix();
    var xStart = moment(localTime).hour(0).minute(0).unix();

    $.getJSON(url, function(response) {
        var options = graphs[graph].options;
        options.axisX.low = xStart; options.axisX.high = xEnd;
        document.getElementById("graph_" + graph).style.display = "block";
        graphs[graph].update({ series: response }, options);

        graphsLoaded += 1;
        if (graphsLoaded == openGraphs.length) {
            isLoading = false; graphsLoaded = 0;
        }

    }).fail(function() {
        var options = graphs[graph].options;
        delete options.axisX.low; delete options.axisX.high;
        graphs[graph].update({ series: null }, options);
        
        graphsLoaded += 1;
        if (graphsLoaded == openGraphs.length) {
            isLoading = false; graphsLoaded = 0;
        }
    });
}

function toggleGraph(graph, fields, button) {
    if (isLoading == true) { return; }
    
    if (button.innerHTML == "-") {
        document.getElementById("graph_" + graph).style.display = "none";

        var options = graphs[graph].options;
        delete options.axisX.low; delete options.axisX.high;
        graphs[graph].update({ series: null }, options);
        
        button.innerHTML = "+";
        openGraphs.splice(openGraphs.indexOf(graph), 1);

    } else {
        button.innerHTML = "-";
        openGraphs.push(graph);
        loadGraphData(graph, fields);
    }
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
    for (var graph in graphs) {
        var options = graphs[graph].options;
        delete options.axisX.low; delete options.axisX.high;
        graphs[graph].update({ series: null }, options);
    }
    
    var utc = moment().utc().millisecond(0).second(0);
    var localUtc = moment(utc).tz(awsTimeZone);
    var localReq = moment(requestedTime).tz(awsTimeZone);

    if (localUtc.format("DD/MM/YYYY") == localReq.format("DD/MM/YYYY")) {
        updateData(true)
    } else { updateData(false); }
}

function openPicker() {
    if (datePicker == null) {
        if (requestedTime != null && isLoading == false) {
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