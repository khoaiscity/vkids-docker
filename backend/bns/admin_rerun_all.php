<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

include_once "bns_db_connect.php";
include_once "a_func_bns_custom_vkids.php";
// include_once "bns_add_daily_period.php";

// include_once "c_bns_pay_fvi.php";

// include_once "../cgi/admin/a_func.php";
// include_once "../cgi/admin/bns/a_func_bns_pay.php";
// include_once "../cgi/admin/bns/a_func_bns.php";
// include_once "../token/token_helper.php";

// autoAddDailyPeriod();
// echo "<LI>YES;";
// exit;

// $t_bns_id=date("Y-m-d", strtotime('-1 days'));
// $t_bns_id=date("Y-m-d");

// $_GET['bns_id']="2020-04-01";
$t_bns_id=$_GET['bns_id'];


if (!$_GET['bns_id'] || !preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$t_bns_id)) {
    
	$json['status']=1;
	$json['msg']="Bonus Period Error";
    echo json_encode($json);
    exit;
}

echo "<LI>vKids Live - ".$t_bns_id;
// exit;

while ($t_bns_id <= date("Y-m-d")) {
	$SQL="SELECT * FROM rwd_period WHERE batch_code = '$t_bns_id'";
	$check = GetSQLAssoc($SQL);

// echo "<LI>$SQL <LI>";

	if ($check['active'] == 0 || $check['paid'] <> 0 || $check['calculate'] == 2) {
	
		$json['status']=1;
		$json['msg']="Bonus already close or paid or calculating";
	    echo json_encode($json);
	    exit;
	}

	CalculateDailyRerun($t_bns_id);

	$t_bns_id=date("Y-m-d", strtotime($t_bns_id.'+1 days'));
}

$json['status']=0;
$json['msg']="Bonus Rerun Successful";
echo json_encode($json);
exit;

?>