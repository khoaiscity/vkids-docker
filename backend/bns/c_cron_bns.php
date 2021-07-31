<?php
include_once "bns_db_connect.php";
include_once "a_func_bns_custom_vkids.php";
// include_once "bns_add_daily_period.php";

// include_once "c_bns_pay_fvi.php";

// include_once "../cgi/admin/a_func.php";
// include_once "../cgi/admin/bns/a_func_bns_pay.php";
// include_once "../cgi/admin/bns/a_func_bns.php";
// include_once "../token/token_helper.php";

// autoAddDailyPeriod();

// exit;

$t_bns_id=date("Y-m-d", strtotime('-1 days'));
// $t_bns_id=date("Y-m-d");
// $t_bns_id="2018-12-18";

echo "<LI>Vkids Live - ".$t_bns_id;
// exit;

$SQL="SELECT * FROM rwd_period WHERE batch_code = '$t_bns_id'";

echo "<LI>check : $SQL";
$check = GetSQLAssoc($SQL);
echo "<LI>check2 : $SQL";

if ($check['active'] == 0 || $check['paid'] <> 0 || $check['calculate'] == 2) {
	
	echo "<LI>Bonus already close or paid or calculating";
	exit;
}

$a_data['cron']=1;
CalculateDailyRerun($t_bns_id,$a_data);

## Run Today's BNS

$t_bns_id=date("Y-m-d");
CalculateDailyRerun($t_bns_id);

?>