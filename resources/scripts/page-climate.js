function setupGraph(graph) {
    var options = {
        showPoint: false, lineSmooth: false, height: 350,

        axisY: {
            offset: 38,
            labelInterpolationFnc: function(value) {
                return value.toFixed(1);
            }
        },

        axisX: {
            type: Chartist.FixedScaleAxis, divisor: 11, offset: 20,    
            labelInterpolationFnc: function(value) {
                return moment(value, "M").format("MMM");
            }
        }
    };

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
    }

    var url = "data/climate.php?time=" + requestedTime.format(
        "YYYY-MM-DD[T]HH-mm-00");

    $.ajax({
        dataType: "json", url: url,
        success: function (data) { processData(data); },

        error: function() {
            var localTime = moment(requestedTime).tz(awsTimeZone);
            document.getElementById("scroller_time").innerHTML
                = localTime.format("YYYY");

            document.getElementById("item_AirT_Avg_Year").innerHTML = "No Data";
            document.getElementById("item_AirT_Min_Year").innerHTML = "No Data";
            document.getElementById("item_AirT_Max_Year").innerHTML = "No Data";
            
            var table = document.getElementById("climate_months_a");
            for (var i = 1, row; row = table.rows[i]; i++) {
                for (var j = 1, col; col = row.cells[j]; j++) {
                    col.innerHTML = "No Data";
                }  
            }

            table = document.getElementById("climate_months_b");
            for (var i = 1, row; row = table.rows[i]; i++) {
                for (var j = 1, col; col = row.cells[j]; j++) {
                    col.innerHTML = "No Data";
                }  
            }
        }
    });

    if ($.inArray("temperature", openGraphs) != -1)
    { loadGraphData("temperature", "AirT_Avg,AirT_Min,AirT_Max"); }
    if ($.inArray("humidity", openGraphs) != -1)
    { loadGraphData("humidity", "RelH_Avg,RelH_Min,RelH_Max"); }
    if ($.inArray("wind", openGraphs) != -1)
    { loadGraphData("wind", "WSpd_Avg,WSpd_Max,WGst_Max"); }
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
        $.inArray("sunshine", openGraphs) == -1 &&
        $.inArray("rainfall", openGraphs) == -1 &&
        $.inArray("pressure", openGraphs) == -1 &&
        $.inArray("soil", openGraphs) == -1) {
        isLoading = false;
    }
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
    isLoading = false;
}

function displayMonth(data, row, precision) {
    for (var i = 1, col; 
        col = document.getElementById(row).cells[i]; i++) {

        if (data[i] != null) {
            if (precision == -1) {
                var formatted = data[i]
                    .toFixed(0) + " (" + degreesToCompass(data[i]) + ")"
                col.innerHTML = formatted; 
            } else { col.innerHTML = data[i].toFixed(precision); }
        } else { col.innerHTML = "No Data";}
    }
}

function loadGraphData(graph, fields) {
    var url = "data/graph-climate.php?time=" + requestedTime.format(
        "YYYY-MM-DD[T]HH-mm-00") + "&fields=" + fields;
    
    $.getJSON(url, function(response) {
        var options = graphs[graph].options;
        options.axisX.low = 1; options.axisX.high = 12;
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
        requestedTime.subtract(1, "years");
        updateData(false);
    }
}

function scrollerRight() {
    if (isLoading == false) {
        requestedTime.add(1, "years");
        updateData(false);
    }
}