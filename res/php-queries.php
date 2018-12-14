<?php
$SELECT_SINGLE_REPORT = "SELECT * FROM reports WHERE Time = '%s'";
$SELECT_SINGLE_ENVREPORT = "SELECT * FROM envReports WHERE Time = '%s'";
$SELECT_SINGLE_DAYSTAT = "SELECT * FROM dayStats WHERE Date = '%s'";

$SELECT_FIELDS_REPORTS = "SELECT %s FROM reports WHERE Time BETWEEN '%s' AND '%s'";
$SELECT_PAST_HOUR_REPORTS = "SELECT SUM(%s) AS %s_PHr FROM reports WHERE Time BETWEEN '%s' AND '%s'";
$SELECT_FIELDS_ENVREPORTS = "SELECT %s FROM envReports WHERE Time BETWEEN '%s' AND '%s'";
$SELECT_FIELDS_DAYSTATS = "SELECT %s FROM dayStats WHERE Date BETWEEN '%s' AND '%s'";

$GENERATE_YEAR_STATS = "SELECT ROUND(AVG(AirT_Avg), 3) AS AirT_Avg, ROUND(MIN(AirT_Min), 3) AS AirT_Min, "
    . "ROUND(MAX(AirT_Max), 3) AS AirT_Max FROM dayStats WHERE YEAR(Date) = %s";
$GENERATE_MONTHS_STATS = "SELECT MONTH(Date) AS Month, ROUND(AVG(AirT_Avg), 3) AS AirT_Avg, "
    . "ROUND(MIN(AirT_Min), 3) AS AirT_Min, ROUND(MAX(AirT_Max), 3) AS AirT_Max, "
    . "ROUND(AVG(RelH_Avg), 3) AS RelH_Avg, ROUND(AVG(WSpd_Avg), 3) AS WSpd_Avg, "
    . "ROUND(MAX(WSpd_Max), 3) AS WSpd_Max, ROUND(AVG(WDir_Avg), 3) AS WDir_Avg, "
    . "ROUND(AVG(WGst_Avg), 3) AS WGst_Avg, ROUND(MAX(WGst_Max), 3) AS WGst_Max, "
    . "ROUND(SUM(SunD_Ttl) / 60.0 / 60.0, 3) AS SunD_Ttl, ROUND(SUM(Rain_Ttl), 3) AS Rain_Ttl, "
    . "ROUND(AVG(MSLP_Avg), 3) AS MSLP_Avg, ROUND(AVG(ST10_Avg), 3) AS ST10_Avg, "
    . "ROUND(AVG(ST30_Avg), 3) AS ST30_Avg, ROUND(AVG(ST00_Avg), 3) AS ST00_Avg "
    . "FROM dayStats WHERE YEAR(Date) = %s GROUP BY MONTH(Date)";