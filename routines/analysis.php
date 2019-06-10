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
            if ($query->rowCount() == 0)
                return NULL;
            else return $query->fetch();
        } else return false;
    }
    catch (Exception $e) { return false; }
}

function fields_in_range($pdo, $start, $end, $fields, $table)
{
    $QUERY = "SELECT %s FROM %s WHERE Time BETWEEN ? AND ?";

    try
    {
        $query = query_database($pdo, sprintf($QUERY, $fields, $table),
            [$start->format("Y-m-d H:i:s"), $end->format("Y-m-d H:i:s")]);

        if ($query)
        {
            if ($query->rowCount() == 0)
                return NULL;
            else return $query->fetchAll();
        } else return false;
    }
    catch (Exception $e) { return false; }
}

function stats_for_year($pdo, $year)
{
    $QUERY = "SELECT ROUND(AVG(AirT_Avg), 3) AS AirT_Avg_Year, "
        . "ROUND(MIN(AirT_Min), 3) AS AirT_Min_Year, ROUND(MAX(AirT_Max), 3) "
        . "AS AirT_Max_Year, FROM dayStats WHERE YEAR(Date) = ?";

    try
    {
        $query = query_database($pdo, $QUERY, [$year]);

        if ($query)
        {
            if ($query->rowCount() == 0)
                return NULL;
            else return $query->fetch();
        } else return false;
    }
    catch (Exception $e) { return false; }
}

function stats_for_months($pdo, $year)
{
    $QUERY = "SELECT MONTH(Date) AS Month, ROUND(AVG(AirT_Avg), 3) AS AirT_Avg, "
        . "ROUND(MIN(AirT_Min), 3) AS AirT_Min, ROUND(MAX(AirT_Max), 3) AS AirT_Max, "
        . "ROUND(AVG(RelH_Avg), 3) AS RelH_Avg, ROUND(AVG(WSpd_Avg), 3) AS WSpd_Avg, "
        . "ROUND(MAX(WSpd_Max), 3) AS WSpd_Max, ROUND(AVG(WDir_Avg), 3) AS WDir_Avg, "
        . "ROUND(MAX(WGst_Max), 3) AS WGst_Max, "
        . "ROUND(SUM(SunD_Ttl) / 60.0 / 60.0, 3) AS SunD_Ttl, "
        . "ROUND(SUM(Rain_Ttl), 3) AS Rain_Ttl, ROUND(AVG(MSLP_Avg), 3) AS MSLP_Avg, "
        . "ROUND(AVG(ST10_Avg), 3) AS ST10_Avg, ROUND(AVG(ST30_Avg), 3) AS ST30_Avg, "
        . "ROUND(AVG(ST00_Avg), 3) AS ST00_Avg "
        . "FROM dayStats WHERE YEAR(Date) = ? GROUP BY MONTH(Date)";

    try
    {
        $query = query_database($pdo, $QUERY, [$year]);

        if ($query)
        {
            if ($query->rowCount() == 0)
                return NULL;
            else return $query->fetchAll();
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
            if ($query->rowCount() == 0)
                return NULL;
            else return $query->fetch();
        } else return false;
    }
    catch (Exception $e) { return false; }
}