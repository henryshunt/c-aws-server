<?php
function record_for_time($pdo, $time, $table)
{
    $QUERY = "SELECT * FROM %s WHERE %s = ?";

    try
    {
        if ($table == DbTable::DAYSTATS)
        {
            $query = query_database($pdo, sprintf($QUERY, $table, "Date"),
                [$time->format("Y-m-d")]);
        }
        else
        {
            $query = query_database($pdo, sprintf($QUERY, $table, "Time"),
                [$time->format("Y-m-d H:i:s")]);
        }
        
        if ($query)
        {
            $result = $query->fetch();
            return empty($result) ? NULL : $result;
        } else return false;
    }
    catch (Exception $e) { return false; }
}

function fields_in_range($pdo, $start, $end, $fields, $table)
{
    $QUERY = "SELECT %s FROM %s WHERE %s BETWEEN ? AND ?";

    try
    {
        if ($table == DbTable::DAYSTATS)
        {
            $query = query_database($pdo,
                sprintf($QUERY, $fields, $table, "Date"),
                [$start->format("Y-m-d"), $end->format("Y-m-d")]);
        }
        else
        {
            $query = query_database($pdo,
                sprintf($QUERY, $fields, $table, "Time"),
                [$start->format("Y-m-d H:i:s"), $end->format("Y-m-d H:i:s")]);
        }

        if ($query)
        {
            $result = $query->fetchAll();
            return empty($result) ? NULL : $result;
        } else return false;
    }
    catch (Exception $e) { return false; }
}

function stats_for_year($config, $pdo, $year)
{
    if ($config->get_is_remote())
    {
        $QUERY = "SELECT ROUND(AVG(AirT_Avg), 3) AS AirT_Avg_Year, "
            . "ROUND(MIN(AirT_Min), 3) AS AirT_Min_Year, ROUND(MAX(AirT_Max), 3) "
            . "AS AirT_Max_Year FROM dayStats WHERE YEAR(Date) = ?";
    }
    else
    {
        $QUERY = "SELECT ROUND(AVG(AirT_Avg), 3) AS AirT_Avg_Year, "
            . "ROUND(MIN(AirT_Min), 3) AS AirT_Min_Year, ROUND(MAX(AirT_Max), 3) "
            . "AS AirT_Max_Year FROM dayStats WHERE strftime('%Y', Date) = ?";
    }

    try
    {
        $query = query_database($pdo, $QUERY, [$year]);

        if ($query)
        {
            $result = $query->fetch();
            return empty($result) ? NULL : $result;
        } else return false;
    }
    catch (Exception $e) { return false; }
}

function stats_for_months($config, $pdo, $year)
{
    if ($config->get_is_remote())
    {
        $QUERY = "SELECT MONTH(Date) AS Month, ROUND(AVG(AirT_Avg), 3) AS "
            . "AirT_Avg_Months, ROUND(MIN(AirT_Min), 3) AS AirT_Min_Months, "
            . "ROUND(MAX(AirT_Max), 3) AS AirT_Max_Months, ROUND(AVG(RelH_Avg), 3) "
            . "AS RelH_Avg_Months, ROUND(AVG(WSpd_Avg), 3) AS WSpd_Avg_Months, "
            . "ROUND(MAX(WSpd_Max), 3) AS WSpd_Max_Months, ROUND(AVG(WDir_Avg), 3) "
            . "AS WDir_Avg_Months, ROUND(MAX(WGst_Max), 3) AS WGst_Max_Months, "
            . "ROUND(SUM(SunD_Ttl) / 60.0 / 60.0, 3) AS SunD_Ttl_Months, "
            . "ROUND(SUM(Rain_Ttl), 3) AS Rain_Ttl_Months, ROUND(AVG(MSLP_Avg), 3) "
            . "AS MSLP_Avg_Months, ROUND(AVG(ST10_Avg), 3) AS ST10_Avg_Months, "
            . "ROUND(AVG(ST30_Avg), 3) AS ST30_Avg_Months, ROUND(AVG(ST00_Avg), 3) "
            . "AS ST00_Avg_Months "
            . "FROM dayStats WHERE YEAR(Date) = ? GROUP BY MONTH(Date)";
    }
    else
    {
        $QUERY = "SELECT strftime('%m', Date) AS Month, ROUND(AVG(AirT_Avg), 3) AS "
            . "AirT_Avg_Months, ROUND(MIN(AirT_Min), 3) AS AirT_Min_Months, "
            . "ROUND(MAX(AirT_Max), 3) AS AirT_Max_Months, ROUND(AVG(RelH_Avg), 3) "
            . "AS RelH_Avg_Months, ROUND(AVG(WSpd_Avg), 3) AS WSpd_Avg_Months, "
            . "ROUND(MAX(WSpd_Max), 3) AS WSpd_Max_Months, ROUND(AVG(WDir_Avg), 3) "
            . "AS WDir_Avg_Months, ROUND(MAX(WGst_Max), 3) AS WGst_Max_Months, "
            . "ROUND(SUM(SunD_Ttl) / 60.0 / 60.0, 3) AS SunD_Ttl_Months, "
            . "ROUND(SUM(Rain_Ttl), 3) AS Rain_Ttl_Months, ROUND(AVG(MSLP_Avg), 3) "
            . "AS MSLP_Avg_Months, ROUND(AVG(ST10_Avg), 3) AS ST10_Avg_Months, "
            . "ROUND(AVG(ST30_Avg), 3) AS ST30_Avg_Months, ROUND(AVG(ST00_Avg), 3) "
            . "AS ST00_Avg_Months FROM dayStats WHERE strftime('%Y', Date) "
            . "= ? GROUP BY strftime('%m', Date)";
    }

    try
    {
        $query = query_database($pdo, $QUERY, [$year]);

        if ($query)
        {
            $result = $query->fetchAll();
            return empty($result) ? NULL : $result;
        } else return false;
    }
    catch (Exception $e) { return false; }
}

function past_hour_total($pdo, $time, $column)
{
    $QUERY = "SELECT SUM(%s) AS %s_PHr FROM reports WHERE Time BETWEEN ? AND ?";
    $past_hour = clone $time;
    $past_hour->sub(new DateInterval("PT59M"));

    try
    {
        $query = query_database($pdo, sprintf($QUERY,
            $column, $column), [$past_hour->format("Y-m-d H:i:s"),
            $time->format("Y-m-d H:i:s")]);

        if ($query)
        {
            $result = $query->fetch();
            return empty($result) ? NULL : $result;
        } else return false;
    }
    catch (Exception $e) { return false; }
}