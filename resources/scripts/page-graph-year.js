var isLoading = false;
var requestedTime = null;

var openGraphs = ["temperature"];
var graphsLoaded = 0;
var graphs = {
    "temperature": null, "humidity": null, "wind": null, "direction": null,
    "sunshine": null, "rainfall": null, "pressure": null, "soil": null
};
var graphFields = {
    "temperature": "AirT_Avg,AirT_Min,AirT_Max",
    "humidity": "RelH_Avg,RelH_Min,RelH_Max",
    "wind": "WSpd_Avg,WGst_Max",
    "direction": "WDir_Avg",
    "sunshine": "SunD_Ttl",
    "rainfall": "Rain_Ttl",
    "pressure": "MSLP_Avg,MSLP_Min,MSLP_Max",
    "soil": "ST10_Avg,ST30_Avg,ST00_Avg"
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
        lineSmooth: false, height: $(".main").width() > 1050 ? 500 : 400,

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

function updateData(autoUpdate) {
    isLoading = true;
    graphsLoaded = 0;

    if (autoUpdate === true)
        requestedTime = moment().utc().millisecond(0).second(0);

    var localTime = moment(requestedTime).tz(awsTimeZone);
    document.getElementById("scroller_time").innerHTML
        = localTime.format("[Year Ending] DD/MM/YYYY");

    // Reload data for open graphs
    if (openGraphs.length > 0) {
        for (var i = 0; i < openGraphs.length; i++)
            loadGraphData(openGraphs[i], true);
    } else isLoading = false;
}

function loadGraphData(graph, check) {
    var url = "data/graph-year.php?time=" + requestedTime.format(
        "YYYY-MM-DD[T]HH-mm-ss") + "&fields=" + graphFields[graph];
    
    var localTime = moment(requestedTime).tz(awsTimeZone);
    var xEnd = moment(localTime).hour(0).minute(0).unix();
    var xStart = moment(
        localTime).subtract(365, "day").hour(0).minute(0).unix();

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
    if (isLoading === false) {
        requestedTime.subtract(1, "months");
        updateData(false);
    }
}

function scrollerRight() {
    if (isLoading === false) {
        requestedTime.add(1, "months");
        updateData(false);
    }
}