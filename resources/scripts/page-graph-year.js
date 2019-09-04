var isLoading = true;
var requestedTime = null;

var graphs = {
    "temperature": null, "humidity": null, "wind": null, "direction": null,
    "sunshine": null, "rainfall": null, "pressure": null, "soil": null
};
var graphFields = {
    "temperature": "AirT_Avg,AirT_Min,AirT_Max",
    "humidity": "RelH_Avg,RelH_Min,RelH_Max", "wind": "WSpd_Avg,WGst_Max",
    "direction": "WDir_Avg", "sunshine": "SunD_Ttl", "rainfall": "Rain_Ttl",
    "pressure": "MSLP_Avg,MSLP_Min,MSLP_Max",
    "soil": "ST10_Avg,ST30_Avg,ST00_Avg"
};

var openGraphs = ["temperature"];
var graphsLoaded = 0;

$(document).ready(function() {
    graphs["temperature"] = setUpGraph("temperature");
    graphs["humidity"] = setUpGraph("humidity");
    graphs["wind"] = setUpGraph("wind");
    graphs["direction"] = setUpGraph("direction");
    graphs["sunshine"] = setUpGraph("sunshine");
    graphs["rainfall"] = setUpGraph("rainfall");
    graphs["pressure"] = setUpGraph("pressure");
    graphs["soil"] = setUpGraph("soil");

    isLoading = true;
    requestedTime = moment().utc().millisecond(0).second(0);
    getAndProcessData(true);
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
            type: Chartist.FixedScaleAxis, divisor: 12, offset: 20,    
            labelInterpolationFnc: function(value) {
                return moment.unix(value).format("MMM DD");
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

    if (graph === "sunshine" || graph === "rainfall")
        return new Chartist.Bar("#graph_" + graph, null, options);
    else return new Chartist.Line("#graph_" + graph, null, options);
}

function getAndProcessData() {
    graphsLoaded = 0;

    document.getElementById("scroller_time").innerHTML
        = moment(requestedTime).tz(
        awsTimeZone).format("[Year Ending] DD/MM/YYYY");

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
    var url = "data/graph-year.php?time=" + requestedTime.format(
        "YYYY-MM-DD[T]HH-mm-ss") + "&fields=" + graphFields[graph];
    
    var localTime = moment(requestedTime).tz(awsTimeZone);
    var xEnd = moment(localTime).hour(0).minute(0).unix();
    var xStart = moment(
        localTime).subtract(365, "day").hour(0).minute(0).unix();

    // Draw new graph
    $.getJSON(url, function(response) {
        if (response !== "1") {
            var options = graphs[graph].options;
            options.axisX.low = xStart; options.axisX.high = xEnd;
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
    if (isLoading === false) {
        isLoading = true;
        requestedTime.subtract(1, "months");
        scrollerChange();
    }
}

function scrollerRight() {
    if (isLoading === false) {
        isLoading = true;
        requestedTime.add(1, "months");
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

    getAndProcessData(false);
}