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
        url: "data/now.php?time=" + requestedTime.format(
            "YYYY-MM-DD[T]HH-mm-00"),

        success: function (data) { processData(data); },
        error: function() {
            document.getElementById("item_data_time").innerHTML
                = moment(requestedTime).tz(awsTimeZone).format("HH:mm");
                
            document.getElementById("item_AirT").innerHTML = "no data";
            document.getElementById("item_ExpT").innerHTML = "no data";
            document.getElementById("item_RelH").innerHTML = "no data";
            document.getElementById("item_DewP").innerHTML = "no data";
            document.getElementById("item_WSpd").innerHTML = "no data";
            document.getElementById("item_WDir").innerHTML = "no data";
            document.getElementById("item_WGst").innerHTML = "no data";
            document.getElementById("item_SunD").innerHTML = "no data";
            document.getElementById("item_SunD_PHr").innerHTML = "no data";
            document.getElementById("item_Rain").innerHTML = "no data";
            document.getElementById("item_Rain_PHr").innerHTML = "no data";
            document.getElementById("item_StaP").innerHTML = "no data";
            document.getElementById("item_MSLP").innerHTML = "no data";
            document.getElementById("item_StaP_PTH").innerHTML = "no data";
            document.getElementById("item_ST10").innerHTML = "no data";
            document.getElementById("item_ST30").innerHTML = "no data";
            document.getElementById("item_ST00").innerHTML = "no data";
        }
    });
}

function processData(data) {
    var utc = moment.utc(data["Time"], "YYYY-MM-DD HH:mm:ss");
    document.getElementById("item_data_time").innerHTML
        = moment(utc).tz(awsTimeZone).format("HH:mm");
    requestedTime = moment(utc);

    displayValue(data["AirT"], "item_AirT", "°C", 1);
    displayValue(data["ExpT"], "item_ExpT", "°C", 1);
    displayValue(data["RelH"], "item_RelH", "%", 1);
    displayValue(data["DewP"], "item_DewP", "°C", 1);
    displayValue(data["WSpd"], "item_WSpd", " mph", 1);

    if (data["WDir"] != null) {
        var formatted = data["WDir"]
            .toFixed(0) + "° (" + degreesToCompass(data["WDir"]) + ")"
        document.getElementById("item_WDir").innerHTML = formatted;
    } else { document.getElementById("item_WDir").innerHTML = "no data"; }

    displayValue(data["WGst"], "item_WGst", " mph", 1);
    displayValue(data["SunD"], "item_SunD", " sec", 0);

    if (data["SunD_PHr"] != null) {
        var formatted = moment.utc(data["SunD_PHr"] * 1000).format("HH:mm:ss");
        document.getElementById("item_SunD_PHr").innerHTML = formatted;
    } else { document.getElementById("item_SunD_PHr").innerHTML = "no data"; }

    displayValue(data["Rain"], "item_Rain", " mm", 2);
    displayValue(data["Rain_PHr"], "item_Rain_PHr", " mm", 2);
    displayValue(data["StaP"], "item_StaP", " hPa", 1);
    displayValue(data["MSLP"], "item_MSLP", " hPa", 1);
    
    if (data["StaP_PTH"] != null) {
         var formatted = data["StaP_PTH"].toFixed(1) + " hpa";
         if (data["StaP_PTH"] > 0) { formatted = "+" + formatted; }
         document.getElementById("item_StaP_PTH").innerHTML = formatted;
    } else { document.getElementById("item_StaP_PTH").innerHTML = "no data"; }
    
    displayValue(data["ST10"], "item_ST10", "°C", 1);
    displayValue(data["ST30"], "item_ST30", "°C", 1);
    displayValue(data["ST00"], "item_ST00", "°C", 1);
}