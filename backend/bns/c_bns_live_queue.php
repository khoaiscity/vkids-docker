<?php
include_once "bns_db_connect.php";
include_once "a_func_bns_custom_vkids.php";

// exit;

echo "<LI>START - ".date("Y-m-d H:i:s");

if (date("H:i") == "23:59" || date("H:i") == "00:00" || date("H:i") == "00:01" || date("H:i") == "00:02") {
	exit;
}

$SQL="SELECT *,TIMESTAMPDIFF(MINUTE , d_update, NOW()) AS minute_passed FROM tbl_bonus_live_queue ORDER BY id DESC LIMIT 1 ";
$r=GetSQLAssoc($SQL);

$pid=$r['process_id'];
$status=$r['t_status'];
checkQueue("tbl_bonus_live_queue",$pid,$status);

$mypid=getMySQLID();
$dt_create = date("Y-m-d H:i:s");
$t_ref="";
$t_batch="";

$SQL="INSERT INTO tbl_bonus_live_queue (process_id, dt_create, t_batch, t_ref) VALUE ('$mypid', NOW(), '$t_batch', '$t_ref')";
RunSQL($SQL);

CalculateDailyLive();

// $SQL="SELECT t_doc_no FROM tbl_bonus_sales WHERE (dt_bns_cal='0000-00-00 00:00:00' OR dt_bns_cal is NULL) AND (t_batch='' OR t_batch is NULL) LIMIT 1";
// $r = GetSQLAssoc($SQL);

$SQL="UPDATE tbl_bonus_live_queue SET t_status='Done',dt_process=NOW() WHERE process_id='$mypid' AND (t_status is NULL OR t_status = '')";
RunSQL($SQL);

// if ($r['t_doc_no']) {
	
// 	$t_domain = "localhost";
// 	bgRunLiveBNS();
// }

echo "<LI>END - ".date("Y-m-d H:i:s");
echo "<LI>===========
";
exit;