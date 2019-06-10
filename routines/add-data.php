<?php
date_default_timezone_set("UTC");
include_once("database.php");

$error_report = false; $error_envReport = false; $error_dayStat = false;

if ($db_conn) {
    if ($_POST["has_report"] == 1) {
        $Time = $_POST["Time"];
        $AirT = ($_POST["AirT"] == "null") ? null : $_POST["AirT"];
        $ExpT = ($_POST["ExpT"] == "null") ? null : $_POST["ExpT"];
        $RelH = ($_POST["RelH"] == "null") ? null : $_POST["RelH"];
        $DewP = ($_POST["DewP"] == "null") ? null : $_POST["DewP"];
        $WSpd = ($_POST["WSpd"] == "null") ? null : $_POST["WSpd"];
        $WDir = ($_POST["WDir"] == "null") ? null : $_POST["WDir"];
        $WGst = ($_POST["WGst"] == "null") ? null : $_POST["WGst"];
        $SunD = ($_POST["SunD"] == "null") ? null : $_POST["SunD"];
        $Rain = ($_POST["Rain"] == "null") ? null : $_POST["Rain"];
        $StaP = ($_POST["StaP"] == "null") ? null : $_POST["StaP"];
        $MSLP = ($_POST["MSLP"] == "null") ? null : $_POST["MSLP"];
        $ST10 = ($_POST["ST10"] == "null") ? null : $_POST["ST10"];
        $ST30 = ($_POST["ST30"] == "null") ? null : $_POST["ST30"];
        $ST00 = ($_POST["ST00"] == "null") ? null : $_POST["ST00"];

        $query = $db_conn -> prepare(
            "INSERT INTO reports VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if ($query) {
            $query -> bind_param("sdddddididddddd", $Time, $AirT, $ExpT, $RelH, $DewP, $WSpd,
                $WDir, $WGst, $SunD, $Rain, $StaP, $MSLP, $ST10, $ST30, $ST00);
            
            if ($query -> execute() == false) {
                if ($db_conn -> errno != 1062) {
                    $error_report = true;
                }
            }
        } else { $error_report = true; }
    }

    if ($_POST["has_envReport"] == 1) {
        $Time = $_POST["Time"];
        $EncT = ($_POST["EncT"] == "null") ? null : $_POST["EncT"];
        $CPUT = ($_POST["CPUT"] == "null") ? null : $_POST["CPUT"];

        $query = $db_conn -> prepare(
            "INSERT INTO envReports VALUES (?, ?, ?)");
        
        if ($query) {
            $query -> bind_param("sdd", $Time, $EncT, $CPUT);
            
            if ($query -> execute() == false) {
                if ($db_conn -> errno != 1062) {
                    $error_envReport = true;
                }
            }
        } else { $error_envReport = true; }
    }

    if ($_POST["has_dayStat"] == "1") {
        $Date = $_POST["Date"];
        $AirT_Avg = ($_POST["AirT_Avg"] == "null") ? null : $_POST["AirT_Avg"];
        $AirT_Min = ($_POST["AirT_Min"] == "null") ? null : $_POST["AirT_Min"];
        $AirT_Max = ($_POST["AirT_Max"] == "null") ? null : $_POST["AirT_Max"];
        $RelH_Avg = ($_POST["RelH_Avg"] == "null") ? null : $_POST["RelH_Avg"];
        $RelH_Min = ($_POST["RelH_Min"] == "null") ? null : $_POST["RelH_Min"];
        $RelH_Max = ($_POST["RelH_Max"] == "null") ? null : $_POST["RelH_Max"];
        $DewP_Avg = ($_POST["DewP_Avg"] == "null") ? null : $_POST["DewP_Avg"];
        $DewP_Min = ($_POST["DewP_Min"] == "null") ? null : $_POST["DewP_Min"];
        $DewP_Max = ($_POST["DewP_Max"] == "null") ? null : $_POST["DewP_Max"];
        $WSpd_Avg = ($_POST["WSpd_Avg"] == "null") ? null : $_POST["WSpd_Avg"];
        $WSpd_Min = ($_POST["WSpd_Min"] == "null") ? null : $_POST["WSpd_Min"];
        $WSpd_Max = ($_POST["WSpd_Max"] == "null") ? null : $_POST["WSpd_Max"];
        $WDir_Avg = ($_POST["WDir_Avg"] == "null") ? null : $_POST["WDir_Avg"];
        $WDir_Min = ($_POST["WDir_Min"] == "null") ? null : $_POST["WDir_Min"];
        $WDir_Max = ($_POST["WDir_Max"] == "null") ? null : $_POST["WDir_Max"];
        $WGst_Avg = ($_POST["WGst_Avg"] == "null") ? null : $_POST["WGst_Avg"];
        $WGst_Min = ($_POST["WGst_Min"] == "null") ? null : $_POST["WGst_Min"];
        $WGst_Max = ($_POST["WGst_Max"] == "null") ? null : $_POST["WGst_Max"];
        $SunD_Ttl = ($_POST["SunD_Ttl"] == "null") ? null : $_POST["SunD_Ttl"];
        $Rain_Ttl = ($_POST["Rain_Ttl"] == "null") ? null : $_POST["Rain_Ttl"];
        $MSLP_Avg = ($_POST["MSLP_Avg"] == "null") ? null : $_POST["MSLP_Avg"];
        $MSLP_Min = ($_POST["MSLP_Min"] == "null") ? null : $_POST["MSLP_Min"];
        $MSLP_Max = ($_POST["MSLP_Max"] == "null") ? null : $_POST["MSLP_Max"];
        $ST10_Avg = ($_POST["ST10_Avg"] == "null") ? null : $_POST["ST10_Avg"];
        $ST10_Min = ($_POST["ST10_Min"] == "null") ? null : $_POST["ST10_Min"];
        $ST10_Max = ($_POST["ST10_Max"] == "null") ? null : $_POST["ST10_Max"];
        $ST30_Avg = ($_POST["ST30_Avg"] == "null") ? null : $_POST["ST30_Avg"];
        $ST30_Min = ($_POST["ST30_Min"] == "null") ? null : $_POST["ST30_Min"];
        $ST30_Max = ($_POST["ST30_Max"] == "null") ? null : $_POST["ST30_Max"];
        $ST00_Avg = ($_POST["ST00_Avg"] == "null") ? null : $_POST["ST00_Avg"];
        $ST00_Min = ($_POST["ST00_Min"] == "null") ? null : $_POST["ST00_Min"];
        $ST00_Max = ($_POST["ST00_Max"] == "null") ? null : $_POST["ST00_Max"];

        $check_query = "SELECT Date FROM dayStats WHERE Date = '" . $Date . "'";
        $check_result = $db_conn -> query($check_query);

        if ($check_result == true) {
            if ($check_result -> num_rows == 0) {
                $query = $db_conn -> prepare(
                    "INSERT INTO dayStats VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,"
                        . " ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                if ($query) {
                    $query -> bind_param("sdddddddddddddiidddiddddddddddddd", $Date, $AirT_Avg,
                        $AirT_Min, $AirT_Max, $RelH_Avg, $RelH_Min, $RelH_Max, $DewP_Avg, $DewP_Min,
                        $DewP_Max, $WSpd_Avg, $WSpd_Min, $WSpd_Max, $WDir_Avg, $WDir_Min, $WDir_Max,
                        $WGst_Avg, $WGst_Min, $WGst_Max, $SunD_Ttl, $Rain_Ttl, $MSLP_Avg, $MSLP_Min,
                        $MSLP_Max, $ST10_Avg, $ST10_Min, $ST10_Max, $ST30_Avg, $ST30_Min, $ST30_Max,
                        $ST00_Avg, $ST00_Min, $ST00_Max);
                    
                    if ($query -> execute() == false) {
                        if ($db_conn -> errno != 1062) {
                            $error_dayStat = true;
                        }
                    }
                } else { $error_dayStat = true; }

            } else {
                $query = $db_conn -> prepare(
                    "UPDATE dayStats SET AirT_Avg = ?, AirT_Min = ?, AirT_Max = ?, RelH_Avg = ?, "
                        . "RelH_Min = ?, RelH_Max = ?, DewP_Avg = ?, DewP_Min = ?, DewP_Max = ?, "
                        . "WSpd_Avg = ?, WSpd_Min = ?, WSpd_Max = ?, WDir_Avg = ?, WDir_Min = ?, "
                        . "WDir_Max = ?, WGst_Avg = ?, WGst_Min = ?, WGst_Max = ?, SunD_Ttl = ?, "
                        . "Rain_Ttl = ?, MSLP_Avg = ?, MSLP_Min = ?, MSLP_Max = ?, ST10_Avg = ?, "
                        . "ST10_Min = ?, ST10_Max = ?, ST30_Avg = ?, ST30_Min = ?, ST30_Max = ?, "
                        . "ST00_Avg = ?, ST00_Min = ?, ST00_Max = ? WHERE Date = ?");
                
                if ($query) {
                    $query -> bind_param("dddddddddddddiidddiddddddddddddds", $AirT_Avg, $AirT_Min,
                        $AirT_Max, $RelH_Avg, $RelH_Min, $RelH_Max, $DewP_Avg, $DewP_Min, $DewP_Max,
                        $WSpd_Avg, $WSpd_Min, $WSpd_Max, $WDir_Avg, $WDir_Min, $WDir_Max, $WGst_Avg,
                        $WGst_Min, $WGst_Max, $SunD_Ttl, $Rain_Ttl, $MSLP_Avg, $MSLP_Min, $MSLP_Max,
                        $ST10_Avg, $ST10_Min, $ST10_Max, $ST30_Avg, $ST30_Min, $ST30_Max, $ST00_Avg,
                        $ST00_Min, $ST00_Max, $Date);
                    
                    if ($query -> execute() == false) { $error_dayStat = true; }
                } else { $error_dayStat = true; }
            }

        } else { $error_dayStat = true; }
    }

    echo ($error_report == false && $error_envReport
        == false && $error_dayStat == false) ? "0" : "1";
} else { echo "1"; }