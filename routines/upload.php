<?php
date_default_timezone_set("UTC");
include_once("config.php");

try { $config = new Config("../config.ini"); }
catch (Exception $e)
{
    echo "1";
    exit(); 
}

// Create connection and check for error
$db_conn = mysqli_connect($config->get_remote_host(), $config->get_remote_username(),
    $config->get_remote_password(), $config->get_remote_database());

if (!$db_conn)
{
    echo "1";
    exit(); 
}

$error_report = false;
$error_envReport = false;
$error_dayStat = false;

// Insert report if supplied
if ($_POST["has_report"] == 1)
{
    $Time = $_POST["report_Time"];
    $AirT = ($_POST["report_AirT"] == "null") ? null : $_POST["report_AirT"];
    $ExpT = ($_POST["report_ExpT"] == "null") ? null : $_POST["report_ExpT"];
    $RelH = ($_POST["report_RelH"] == "null") ? null : $_POST["report_RelH"];
    $DewP = ($_POST["report_DewP"] == "null") ? null : $_POST["report_DewP"];
    $WSpd = ($_POST["report_WSpd"] == "null") ? null : $_POST["report_WSpd"];
    $WDir = ($_POST["report_WDir"] == "null") ? null : $_POST["report_WDir"];
    $WGst = ($_POST["report_WGst"] == "null") ? null : $_POST["report_WGst"];
    $SunD = ($_POST["report_SunD"] == "null") ? null : $_POST["report_SunD"];
    $Rain = ($_POST["report_Rain"] == "null") ? null : $_POST["report_Rain"];
    $StaP = ($_POST["report_StaP"] == "null") ? null : $_POST["report_StaP"];
    $MSLP = ($_POST["report_MSLP"] == "null") ? null : $_POST["report_MSLP"];
    $ST10 = ($_POST["report_ST10"] == "null") ? null : $_POST["report_ST10"];
    $ST30 = ($_POST["report_ST30"] == "null") ? null : $_POST["report_ST30"];
    $ST00 = ($_POST["report_ST00"] == "null") ? null : $_POST["report_ST00"];

    $query = $db_conn->prepare(
        "INSERT INTO reports VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($query)
    {
        $query->bind_param("sdddddididddddd", $Time, $AirT, $ExpT, $RelH, $DewP, $WSpd,
            $WDir, $WGst, $SunD, $Rain, $StaP, $MSLP, $ST10, $ST30, $ST00);
        
        if (!$query->execute())
        {
            if ($db_conn->errno != 1062)
                $error_report = true;
        }
    } else $error_report = true;
}

// Insert envReport if supplied
if ($_POST["has_envReport"] == 1)
{
    $Time = $_POST["envReport_Time"];
    $EncT = ($_POST["envReport_EncT"] == "null") ? null : $_POST["envReport_EncT"];
    $CPUT = ($_POST["envReport_CPUT"] == "null") ? null : $_POST["envReport_CPUT"];

    $query = $db_conn->prepare(
        "INSERT INTO envReports VALUES (?, ?, ?)");
    
    if ($query)
    {
        $query->bind_param("sdd", $Time, $EncT, $CPUT);
        
        if (!$query->execute())
        {
            if ($db_conn->errno != 1062)
                $error_envReport = true;
        }
    } else $error_envReport = true;
}

// Insert dayStat if supplied
if ($_POST["has_dayStat"] == "1")
{
    $Date = $_POST["dayStat_Date"];
    $AirT_Avg = ($_POST[
        "dayStat_AirT_Avg"] == "null") ? null : $_POST["dayStat_AirT_Avg"];
    $AirT_Min = ($_POST[
        "dayStat_AirT_Min"] == "null") ? null : $_POST["dayStat_AirT_Min"];
    $AirT_Max = ($_POST[
        "dayStat_AirT_Max"] == "null") ? null : $_POST["dayStat_AirT_Max"];
    $RelH_Avg = ($_POST[
        "dayStat_RelH_Avg"] == "null") ? null : $_POST["dayStat_RelH_Avg"];
    $RelH_Min = ($_POST[
        "dayStat_RelH_Min"] == "null") ? null : $_POST["dayStat_RelH_Min"];
    $RelH_Max = ($_POST[
        "dayStat_RelH_Max"] == "null") ? null : $_POST["dayStat_RelH_Max"];
    $DewP_Avg = ($_POST[
        "dayStat_DewP_Avg"] == "null") ? null : $_POST["dayStat_DewP_Avg"];
    $DewP_Min = ($_POST[
        "dayStat_DewP_Min"] == "null") ? null : $_POST["dayStat_DewP_Min"];
    $DewP_Max = ($_POST[
        "dayStat_DewP_Max"] == "null") ? null : $_POST["dayStat_DewP_Max"];
    $WSpd_Avg = ($_POST[
        "dayStat_WSpd_Avg"] == "null") ? null : $_POST["dayStat_WSpd_Avg"];
    $WSpd_Min = ($_POST[
        "dayStat_WSpd_Min"] == "null") ? null : $_POST["dayStat_WSpd_Min"];
    $WSpd_Max = ($_POST[
        "dayStat_WSpd_Max"] == "null") ? null : $_POST["dayStat_WSpd_Max"];
    $WDir_Avg = ($_POST[
        "dayStat_WDir_Avg"] == "null") ? null : $_POST["dayStat_WDir_Avg"];
    $WDir_Min = ($_POST[
        "dayStat_WDir_Min"] == "null") ? null : $_POST["dayStat_WDir_Min"];
    $WDir_Max = ($_POST[
        "dayStat_WDir_Max"] == "null") ? null : $_POST["dayStat_WDir_Max"];
    $WGst_Avg = ($_POST[
        "dayStat_WGst_Avg"] == "null") ? null : $_POST["dayStat_WGst_Avg"];
    $WGst_Min = ($_POST[
        "dayStat_WGst_Min"] == "null") ? null : $_POST["dayStat_WGst_Min"];
    $WGst_Max = ($_POST[
        "dayStat_WGst_Max"] == "null") ? null : $_POST["dayStat_WGst_Max"];
    $SunD_Ttl = ($_POST[
        "dayStat_SunD_Ttl"] == "null") ? null : $_POST["dayStat_SunD_Ttl"];
    $Rain_Ttl = ($_POST[
        "dayStat_Rain_Ttl"] == "null") ? null : $_POST["dayStat_Rain_Ttl"];
    $MSLP_Avg = ($_POST[
        "dayStat_MSLP_Avg"] == "null") ? null : $_POST["dayStat_MSLP_Avg"];
    $MSLP_Min = ($_POST[
        "dayStat_MSLP_Min"] == "null") ? null : $_POST["dayStat_MSLP_Min"];
    $MSLP_Max = ($_POST[
        "dayStat_MSLP_Max"] == "null") ? null : $_POST["dayStat_MSLP_Max"];
    $ST10_Avg = ($_POST[
        "dayStat_ST10_Avg"] == "null") ? null : $_POST["dayStat_ST10_Avg"];
    $ST10_Min = ($_POST[
        "dayStat_ST10_Min"] == "null") ? null : $_POST["dayStat_ST10_Min"];
    $ST10_Max = ($_POST[
        "dayStat_ST10_Max"] == "null") ? null : $_POST["dayStat_ST10_Max"];
    $ST30_Avg = ($_POST[
        "dayStat_ST30_Avg"] == "null") ? null : $_POST["dayStat_ST30_Avg"];
    $ST30_Min = ($_POST[
        "dayStat_ST30_Min"] == "null") ? null : $_POST["dayStat_ST30_Min"];
    $ST30_Max = ($_POST[
        "dayStat_ST30_Max"] == "null") ? null : $_POST["dayStat_ST30_Max"];
    $ST00_Avg = ($_POST[
        "dayStat_ST00_Avg"] == "null") ? null : $_POST["dayStat_ST00_Avg"];
    $ST00_Min = ($_POST[
        "dayStat_ST00_Min"] == "null") ? null : $_POST["dayStat_ST00_Min"];
    $ST00_Max = ($_POST[
        "dayStat_ST00_Max"] == "null") ? null : $_POST["dayStat_ST00_Max"];

    $check_query = "SELECT Date FROM dayStats WHERE Date = '" . $Date . "'";
    $check_result = $db_conn->query($check_query);

    if ($check_result)
    {
        if ($check_result->num_rows == 0)
        {
            $query = $db_conn->prepare(
                "INSERT INTO dayStats VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,"
                    . " ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if ($query)
            {
                $query -> bind_param("sdddddddddddddiidddiddddddddddddd", $Date, $AirT_Avg,
                    $AirT_Min, $AirT_Max, $RelH_Avg, $RelH_Min, $RelH_Max, $DewP_Avg, $DewP_Min,
                    $DewP_Max, $WSpd_Avg, $WSpd_Min, $WSpd_Max, $WDir_Avg, $WDir_Min, $WDir_Max,
                    $WGst_Avg, $WGst_Min, $WGst_Max, $SunD_Ttl, $Rain_Ttl, $MSLP_Avg, $MSLP_Min,
                    $MSLP_Max, $ST10_Avg, $ST10_Min, $ST10_Max, $ST30_Avg, $ST30_Min, $ST30_Max,
                    $ST00_Avg, $ST00_Min, $ST00_Max);
                
                if (!$query->execute())
                {
                    if ($db_conn->errno != 1062)
                        $error_dayStat = true;
                }
            } else $error_dayStat = true;
        }
        else
        {
            $query = $db_conn->prepare(
                "UPDATE dayStats SET AirT_Avg = ?, AirT_Min = ?, AirT_Max = ?, RelH_Avg = ?, "
                    . "RelH_Min = ?, RelH_Max = ?, DewP_Avg = ?, DewP_Min = ?, DewP_Max = ?, "
                    . "WSpd_Avg = ?, WSpd_Min = ?, WSpd_Max = ?, WDir_Avg = ?, WDir_Min = ?, "
                    . "WDir_Max = ?, WGst_Avg = ?, WGst_Min = ?, WGst_Max = ?, SunD_Ttl = ?, "
                    . "Rain_Ttl = ?, MSLP_Avg = ?, MSLP_Min = ?, MSLP_Max = ?, ST10_Avg = ?, "
                    . "ST10_Min = ?, ST10_Max = ?, ST30_Avg = ?, ST30_Min = ?, ST30_Max = ?, "
                    . "ST00_Avg = ?, ST00_Min = ?, ST00_Max = ? WHERE Date = ?");
            
            if ($query)
            {
                $query->bind_param("dddddddddddddiidddiddddddddddddds", $AirT_Avg, $AirT_Min,
                    $AirT_Max, $RelH_Avg, $RelH_Min, $RelH_Max, $DewP_Avg, $DewP_Min, $DewP_Max,
                    $WSpd_Avg, $WSpd_Min, $WSpd_Max, $WDir_Avg, $WDir_Min, $WDir_Max, $WGst_Avg,
                    $WGst_Min, $WGst_Max, $SunD_Ttl, $Rain_Ttl, $MSLP_Avg, $MSLP_Min, $MSLP_Max,
                    $ST10_Avg, $ST10_Min, $ST10_Max, $ST30_Avg, $ST30_Min, $ST30_Max, $ST00_Avg,
                    $ST00_Min, $ST00_Max, $Date);
                
                if (!$query->execute()) $error_dayStat = true;
            } else $error_dayStat = true;
        }

    } else $error_dayStat = true;
}

echo ($error_report == false && $error_envReport
    == false && $error_dayStat == false) ? "0" : "1";