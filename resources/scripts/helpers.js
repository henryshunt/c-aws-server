function queryParam(key) {
    key = key.replace(/[*+?^$.\[\]{}()|\\\/]/g, "\\$&");
    var match = location.search.match(new RegExp("[?&]" + key + "=([^&]+)(&|$)"));
    return match && decodeURIComponent(match[1].replace(/\+/g, " "));
}

function displayValue(value, id, units, precision) {
    if (value != null) {
        if (precision == null) {
            document.getElementById(id).innerHTML = value + units;

        } else {
            document.getElementById(id).innerHTML
                = roundPlaces(value, precision) + units;
        }
    } else { document.getElementById(id).innerHTML = "No Data"; }
}

function degreesToCompass(degrees) {
    if (degrees >= 338 || degrees < 23) { return "N"; }
    else if (degrees >= 23 && degrees < 68) { return "NE"; }
    else if (degrees >= 68 && degrees < 113) { return "E"; }
    else if (degrees >= 113 && degrees < 158) { return "SE"; }
    else if (degrees >= 158 && degrees < 203) { return "S"; }
    else if (degrees >= 203 && degrees < 248) { return "SW"; }
    else if (degrees >= 248 && degrees < 293) { return "W"; }
    else if (degrees >= 293 && degrees < 338) { return "NW"; }
}

function roundPlaces(value, places) {
    return Number(
        Math.round(value + 'e' + places) + 'e-' + places).toFixed(places);
}

function toggleGraph(graph, button) {
    if (isLoading === true) return;

    // Graph collapsed
    if (button.children[0].children[0].innerHTML === "expand_more") {
        button.children[0].children[0].innerHTML = "chevron_right";
        document.getElementById("graph_" + graph).style.display = "none";
        openGraphs.splice(openGraphs.indexOf(graph), 1);

    } else {
        button.children[0].children[0].innerHTML = "expand_more";
        document.getElementById("graph_" + graph).style.display = "block";
        loadGraphData(graph, false);
    }
}

function graphHeightCheck() {
    if (typeof openGraphs === "undefined")
        var _openGraphs = Object.keys(graphs);
    else var _openGraphs = openGraphs;

    if ($(".main").width() > 1050) {
        for (var i = 0; i < _openGraphs.length; i++) {
            var options = graphs[_openGraphs[i]].options;
            options.height = 500;
            graphs[_openGraphs[i]].update(null, options);
        }
    } else {
        for (var i = 0; i < _openGraphs.length; i++) {
            var options = graphs[_openGraphs[i]].options;
            options.height = 400;
            graphs[_openGraphs[i]].update(null, options);
        }
    }
}