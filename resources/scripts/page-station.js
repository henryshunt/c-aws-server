function updateData() {
    clearTimeout(updaterTimeout);
    clearTimeout(countTimeout);

    document.getElementById("item_update").innerHTML = "0";
    timeToUpdate = 60;

    updaterTimeout = setInterval(function() {
        updateData();
    }, 60000);
        
    countTimeout = setInterval(function() {
        document.getElementById("item_update").innerHTML
            = --timeToUpdate;
    }, 1000);

    getAndProcessData();
}

function getAndProcessData() {
    requestedTime = moment().utc().millisecond(0).second(0);

    $.ajax({
        dataType: "json",
        url: "data/station.php?time=" + requestedTime.format(
            "YYYY-MM-DD[T]HH-mm-00"),

        success: function (data) { processData(data); },
        error: function() {
            document.getElementById("item_data_time").innerHTML
                = moment(requestedTime).tz(awsTimeZone).format("HH:mm");

            document.getElementById("item_EncT").innerHTML = "no data";
            document.getElementById("item_CPUT").innerHTML = "no data";
        }
    });

    loadGraphData();
}

function processData(data) {
    var utc = moment.utc(data["Time"], "YYYY-MM-DD HH:mm:ss");
    document.getElementById("item_data_time").innerHTML
        = moment(utc).tz(awsTimeZone).format("HH:mm");
    requestedTime = moment(utc);

    displayValue(data["EncT"], "item_EncT", "°C", 1);
    displayValue(data["CPUT"], "item_CPUT", "°C", 1);
}

function loadGraphData() {
    var url = "data/graph-station.php?time=" + requestedTime.format(
        "YYYY-MM-DD[T]HH-mm-00") + "&fields=EncT,CPUT";
    
    var xEnd = moment(requestedTime).tz(awsTimeZone).unix();
    var xStart = moment(requestedTime).subtract(6, "hour").tz(awsTimeZone).unix();

    $.getJSON(url, function(response) {
        var options = graph.options;
        options.axisX.low = xStart; options.axisX.high = xEnd;
        graph.update({ series: response }, options);

    }).fail(function() {
        var options = graph.options;
        delete options.axisX.low; delete options.axisX.high;
        graph.update({ series: null }, options);
    });
}