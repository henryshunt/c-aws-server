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
            }; isLoading = false;
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

    displayMonths(data["AirT_Avg_Months"], "item_AirT_Avg_Months", 1);
    displayMonths(data["AirT_Min_Months"], "item_AirT_Min_Months", 1);
    displayMonths(data["AirT_Max_Months"], "item_AirT_Max_Months", 1);
    displayMonths(data["RelH_Avg_Months"], "item_RelH_Avg_Months", 1);
    displayMonths(data["WSpd_Avg_Months"], "item_WSpd_Avg_Months", 1);
    displayMonths(data["WSpd_Max_Months"], "item_WSpd_Max_Months", 1);
    displayMonths(data["WDir_Avg_Months"], "item_WDir_Avg_Months", -1);
    displayMonths(data["WGst_Max_Months"], "item_WGst_Max_Months", 1);
    displayMonths(data["SunD_Ttl_Months"], "item_SunD_Ttl_Months", 1);
    displayMonths(data["Rain_Ttl_Months"], "item_Rain_Ttl_Months", 1);
    displayMonths(data["MSLP_Avg_Months"], "item_MSLP_Avg_Months", 1);
    displayMonths(data["ST10_Avg_Months"], "item_ST10_Avg_Months", 1);
    displayMonths(data["ST30_Avg_Months"], "item_ST30_Avg_Months", 1);
    displayMonths(data["ST00_Avg_Months"], "item_ST00_Avg_Months", 1);
    isLoading = false;
}

function displayMonths(data, row, precision) {
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