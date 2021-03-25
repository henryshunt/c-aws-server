var isLoading = false;
var requestedTime = null;

var graphsLoaded = 0;
var graphs = {
    "temperature": null, "humidity": null, "wind": null, "sunshine": null,
    "rainfall": null, "soil": null
};
var graphFields = {
    "temperature": "AirT_Avg,AirT_Min,AirT_Max",
    "humidity": "RelH_Avg,RelH_Min,RelH_Max",
    "wind": "WSpd_Avg,WGst_Max",
    "sunshine": "SunD_Ttl",
    "rainfall": "Rain_Ttl",
    "soil": "ST10_Avg,ST30_Avg,ST00_Avg"
};

$(document).ready(function() {
    var graphKeys = Object.keys(graphs);
    for (var i = 0; i < graphKeys.length; i++)
        graphs[graphKeys[i]] = setUpGraph(graphKeys[i]);
    updateData(true);
});


function setUpGraph(graph) {
    if (graph === "sunshine" || graph === "rainfall") {
        var options = {
            height: 400,

            axisX: {
                offset: 20,
                labelInterpolationFnc: function(value) {
                    return moment(value, "M").format("MMM");
                }
            }
        };
    
        return new Chartist.Bar("#graph_" + graph, null, options);

    } else {
        var options = {
            lineSmooth: false, height: 400,
            
            axisY: {
                offset: 38,
                labelInterpolationFnc: function(value) {
                    return roundPlaces(value, 1);
                }
            },

            axisX: {
                type: Chartist.FixedScaleAxis, divisor: 11, offset: 20,    
                labelInterpolationFnc: function(value) {
                    return moment(value, "M").format("MMM");
                }
            }
        };

        return new Chartist.Line("#graph_" + graph, null, options);
    }
}

function updateData(autoUpdate) {
    isLoading = true;
    graphsLoaded = 0;

    if (autoUpdate === true)
        requestedTime = moment().utc().millisecond(0).second(0);
    loadNewData();
}

function loadNewData() {
    var url = "data/climate.php?time=" + requestedTime.format(
        "YYYY-MM-DD[T]HH-mm-ss");
    
    $.ajax({
        dataType: "json", url: url,
        success: function (data) {
            data === "1" ? requestError() : processData(data);
        },

        error: requestError = function() {
            var localTime = moment(requestedTime).tz(awsTimeZone);
            document.getElementById("scroller_time").innerHTML
                = localTime.format("YYYY");

            document.getElementById("item_AirT_Avg_Year").innerHTML = "No Data";
            document.getElementById("item_AirT_Min_Year").innerHTML = "No Data";
            document.getElementById("item_AirT_Max_Year").innerHTML = "No Data";
            
            var table = document.getElementById("climate_months");
            for (var i = 1, row; row = table.rows[i]; i++) {
                for (var j = 2, col; col = row.cells[j]; j++) 
                    col.innerHTML = "No Data";
            }

            // Reload data for graphs
            var graphKeys = Object.keys(graphs);
            for (var i = 0; i < graphKeys.length; i++)
                loadGraphData(graphKeys[i]);
        }
    });
}

function processData(data) {
    var localTime = moment(requestedTime).tz(awsTimeZone);
    document.getElementById("scroller_time").innerHTML
        = localTime.format("YYYY");

    displayValue(data["AirT_Avg_Year"], "item_AirT_Avg_Year", "°C", 1);
    displayValue(data["AirT_Min_Year"], "item_AirT_Min_Year", "°C", 1);
    displayValue(data["AirT_Max_Year"], "item_AirT_Max_Year", "°C", 1);

    displayMonth(data["AirT_Avg_Month"], "item_AirT_Avg_Months", 1);
    displayMonth(data["AirT_Min_Month"], "item_AirT_Min_Months", 1);
    displayMonth(data["AirT_Max_Month"], "item_AirT_Max_Months", 1);
    displayMonth(data["RelH_Avg_Month"], "item_RelH_Avg_Months", 1);
    displayMonth(data["WSpd_Avg_Month"], "item_WSpd_Avg_Months", 1);
    displayMonth(data["WSpd_Max_Month"], "item_WSpd_Max_Months", 1);
    displayMonth(data["WDir_Avg_Month"], "item_WDir_Avg_Months", -1);
    displayMonth(data["WGst_Max_Month"], "item_WGst_Max_Months", 1);
    displayMonth(data["SunD_Ttl_Month"], "item_SunD_Ttl_Months", 1);
    displayMonth(data["Rain_Ttl_Month"], "item_Rain_Ttl_Months", 1);
    displayMonth(data["MSLP_Avg_Month"], "item_MSLP_Avg_Months", 1);
    displayMonth(data["ST10_Avg_Month"], "item_ST10_Avg_Months", 1);
    displayMonth(data["ST30_Avg_Month"], "item_ST30_Avg_Months", 1);
    displayMonth(data["ST00_Avg_Month"], "item_ST00_Avg_Months", 1);

    // Reload data for graphs
    var graphKeys = Object.keys(graphs);
    for (var i = 0; i < graphKeys.length; i++)
        loadGraphData(graphKeys[i]);
}

function displayMonth(data, row, precision) {
    console.log(data);
    for (var i = 2, col; col = document.getElementById(row).cells[i]; i++) {
        if (data[i - 1] !== null) {
            if (precision === -1) {
                var formatted = roundPlaces(data[i - 1], 0)
                    + " (" + degreesToCompass(data[i - 1]) + ")"
                col.innerHTML = formatted; 
            } else col.innerHTML = roundPlaces(data[i - 1], precision);
        } else col.innerHTML = "No Data";
    }
}

function loadGraphData(graph) {
    var url = "data/graph-climate.php?time=" + requestedTime.format(
        "YYYY-MM-DD[T]HH-mm-ss") + "&fields=" + graphFields[graph];
    
    // Draw new graph
    var options = graphs[graph].options;
    options.axisX.low = 1;
    options.axisX.high = 12;

    $.getJSON(url, function(response) {
        if (response !== "1") {
            var data = null;

            if (graph == "sunshine" || graph == "rainfall") {
                var data = {
                    labels: response[0].map(function(element) {
                        return element.x;
                    }),

                    series: [response[0].map(function(element) {
                        return element.y;
                    })]
                };

                graphs[graph].update(data, options);

            } else {
                data = response;
                graphs[graph].update({ series: data }, options);
            }

            graphsLoaded += 1;
            if (graphsLoaded === Object.keys(graphs).length)
                isLoading = false;
        } else requestErrorGraph();

    }).fail(requestErrorGraph = function() {
        graphs[graph].update({ series: null }, options);
        
        graphsLoaded += 1;
        if (graphsLoaded === Object.keys(graphs).length)
            isLoading = false;
    });
}


function scrollerLeft() {
    if (isLoading === false) {
        requestedTime.subtract(1, "years");
        updateData(false);
    }
}

function scrollerRight() {
    if (isLoading === false) {
        requestedTime.add(1, "years");
        updateData(false);
    }
}