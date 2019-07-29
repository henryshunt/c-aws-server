function setupGraph(graph) {
    var options = {
        showPoint: false, lineSmooth: false, height: 400,

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
            type: Chartist.FixedScaleAxis, divisor: 12, offset: 20,    
            labelInterpolationFnc: function(value) {
                return moment.unix(value).format("MMM DD");
            }
        }
    };

    if (graph == "direction") {
        options.axisY.type = Chartist.FixedScaleAxis;
        options.axisY.divisor = 8;
        options.axisY.low = 0; options.axisY.high = 360;
        options.showPoint = true; options.showLine = false;
    }

    if (graph == "sunshine" || graph == "rainfall") {
        return new Chartist.Bar("#graph_" + graph, null, options);
    } else { return new Chartist.Line("#graph_" + graph, null, options); }
}

function updateData(setTime) {
    isLoading = true;
    getAndProcessData(setTime);
}

function getAndProcessData(setTime) {
    if (setTime == true) {
        requestedTime = moment().utc().millisecond(0).second(0);
    }; graphsLoaded = 0;

    document.getElementById("scroller_time").innerHTML
        = moment(requestedTime).tz(
        awsTimeZone).format("[Year Ending] DD/MM/YYYY");

    if ($.inArray("temperature", openGraphs) != -1)
    { loadGraphData("temperature", "AirT_Avg,AirT_Min,AirT_Max"); }
    if ($.inArray("humidity", openGraphs) != -1)
    { loadGraphData("humidity", "RelH_Avg,RelH_Min,RelH_Max"); }
    if ($.inArray("wind", openGraphs) != -1)
    { loadGraphData("wind", "WSpd_Avg,WGst_Max"); }
    if ($.inArray("direction", openGraphs) != -1)
    { loadGraphData("direction", "WDir_Avg,WDir_Min,WDir_Avg"); }
    if ($.inArray("sunshine", openGraphs) != -1)
    { loadGraphData("sunshine", "SunD_Ttl"); }
    if ($.inArray("rainfall", openGraphs) != -1)
    { loadGraphData("rainfall", "Rain_Ttl"); }
    if ($.inArray("pressure", openGraphs) != -1)
    { loadGraphData("pressure", "MSLP_Avg,MSLP_Min,MSLP_Max"); }
    if ($.inArray("soil", openGraphs) != -1)
    { loadGraphData("soil", "ST10_Avg,ST30_Avg,ST00_Avg"); }

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
    var url = "data/graph-year.php?time=" + requestedTime.format(
        "YYYY-MM-DD[T]HH-mm-00") + "&fields=" + fields;
    
    var localTime = moment(requestedTime).tz(awsTimeZone);
    var xEnd = moment(localTime).hour(0).minute(0).unix();
    var xStart = moment(localTime).subtract(365, "day").hour(0).minute(0).unix();

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
    if (isLoading == false) {
        requestedTime.subtract(1, "months");
        scrollerChange();
    }
}

function scrollerRight() {
    if (isLoading == false) {
        requestedTime.add(1, "months");
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