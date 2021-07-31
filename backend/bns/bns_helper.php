<?php
    
    function UpdatePersonalSales($t_bns_id,$a_mem,$a_sales){

        $t_member_id=$a_mem['t_member_id'];
        $f_ppv=$a_sales['f_pv'];
        $f_pbv=$a_sales['f_bv'];
        $t_rank_old=$a_mem['t_rank_old'];
        $t_rank_eff=$a_mem['t_rank_eff'];
        $t_user_id=$a_mem['t_user_id'];
        $t_full_name=$a_mem['t_full_name'];
        $t_status=$a_mem['t_status'];

        $SQL="SELECT * FROM tbl_bonus WHERE t_bns_id='$t_bns_id' AND t_member_id='$t_member_id'";
        $r_bns=GetSQLAssoc($SQL);

        if (!$r_bns) {     
            $SQL="INSERT INTO tbl_bonus (t_bns_id,t_member_id,t_user_id,t_full_name,t_rank_eff,t_status,f_ppv,f_pbv) VALUES ('$t_bns_id','$t_member_id','$t_user_id','$t_full_name','$t_rank_eff','$t_status','$f_ppv','$f_pbv')";
            RunSQL($SQL);
        }else{
            $SQL="UPDATE tbl_bonus SET t_rank_eff='$t_rank_eff',f_ppv=f_ppv+$f_ppv,f_pbv=f_pbv+$f_pbv WHERE t_bns_id='$t_bns_id' AND t_member_id='$t_member_id'";
            RunSQL($SQL);
        }
    }

    function PromoteRank($a_sales,$a_mem,$t_bns_id){

// echo "<LI>PromoteRank0";

        $tbl_detail="tbl_bonus_rank";
        $t_member_id=$a_mem['t_member_id'];
        $t_member_lot=$a_mem['t_member_lot'];
        $t_rank_eff="00";
        $t_rank_old="00";
        $t_status=$a_mem['t_status'];
        $t_package_id=$a_sales['t_item_id'];

        $where="WHERE t_member_id='$t_member_id' AND t_member_lot='$t_member_lot' AND t_bns_fr<='$t_bns_id' AND t_bns_to>='$t_bns_id'";

        $SQL="SELECT * FROM tbl_bonus_rank $where";
// echo "<LI>SQL : $SQL";

        $r_today=GetSQLAssoc($SQL);   

// echo "<LI>SQL : $SQL";


        if (!$r_today || $r_today['t_bns_fr'] < $t_bns_id) {

            if ($r_today && $r_today['t_bns_fr'] < $t_bns_id) {
                $SQL="UPDATE $tbl_detail SET t_bns_to=DATE_SUB('$t_bns_id',INTERVAL 1 DAY),b_latest=0 $where";
                RunSQL($SQL);
                $r_yest=$r_today;
                $r_today=false;
            }else{  
                $SQL="SELECT * FROM $tbl_detail WHERE t_member_id='$t_member_id' AND t_member_lot='$t_member_lot' AND t_bns_fr<=DATE_SUB('$t_bns_id',INTERVAL 1 DAY) AND t_bns_to>=DATE_SUB('$t_bns_id',INTERVAL 1 DAY)";
                $r_yest=GetSQLAssoc($SQL); 
            }

            if ($r_yest) {
                $t_rank_cur=$r_yest['t_rank_eff'];
                $t_rank_eff=$r_yest['t_rank_eff'];
                $t_status=$r_yest['t_status'];
            }

        }else{
            $t_rank_cur=$r_today['t_rank_old'];
            $t_rank_eff=$r_today['t_rank_eff'];
        }

        $SQL="SELECT * FROM prd_master WHERE id='$t_package_id'";
        $r_package=GetSQLAssoc($SQL);
// echo "<LI>Rank : $SQL";
        // if (!$r_rank_package) {
            $t_rank_eff=$r_package['lot_rank'];
        // }

        if (!$r_today || ($r_today['t_bns_fr']<$t_bns_id && $r_today['t_bns_to']>=$t_bns_id)) {


            if ($r_today && $r_today['t_bns_to']>=$t_bns_id) {
                $SQL="UPDATE $tbl_detail SET t_bns_to=DATE_SUB('$t_bns_id',INTERVAL 1 DAY),b_latest=0 $where";
                RunSQL($SQL);              
            }

            $SQL="INSERT INTO $tbl_detail (t_bns_fr, t_bns_to, t_member_id, t_member_lot, t_rank_old, t_rank_eff, t_package_id, t_status, b_latest)
            VALUES('$t_bns_id', '$t_bns_id', '$t_member_id', '$t_member_lot', '$t_rank_old', '$t_rank_eff', '$t_package_id', '$t_status' ,1)";
            RunSQL($SQL);

            $SQL="UPDATE $tbl_detail SET b_latest=0 WHERE t_member_id='$t_member_id' AND t_member_lot='$t_member_lot' AND t_bns_to=DATE_SUB('$t_bns_id',INTERVAL 1 DAY)";
            RunSQL($SQL);

        }else{
            $SQL="UPDATE $tbl_detail SET t_rank_eff='$t_rank_eff', t_package_id='$t_package_id', t_status='$t_status' $where";
            RunSQL($SQL);
        }
    }

    function ExtendBonusToDate($t_bns_id){

        $extend_date = "2099-12-31";

        $SQL="UPDATE tbl_bonus_pair SET t_bns_to='$extend_date' WHERE t_bns_to=DATE_SUB('$t_bns_id',INTERVAL 1 DAY)";
        RunSQL($SQL);

        $SQL="UPDATE tbl_bonus_rank SET t_bns_to='$extend_date' WHERE t_bns_to=DATE_SUB('$t_bns_id',INTERVAL 1 DAY)";
        RunSQL($SQL);

    }

    function ExtendBonusToMth($t_bns_id){

        $t_bns_mth=ConvertBonusMth($t_bns_id);

        $SQL="UPDATE tbl_bonus_world_pool SET t_bns_mth_to='$t_bns_mth' WHERE t_bns_mth_to=DATE_SUB('$t_bns_id',INTERVAL 1 MONTH) AND b_latest=1";
        RunSQL($SQL);
    }

    function GetSQLAssoc($SQL){
        
        Global $DBLink;
        $rs = mysqli_query($DBLink,$SQL);
        
        if(!$rs){
            echo "<LI>Error SQL : $SQL";
            echo "<LI>".mysqli_error($DBLink) . ": " . mysqli_error($DBLink);
            return false;
        }

        $r=mysqli_fetch_assoc($rs);
        return $r;
    }

    function RunSQL($SQL){
        Global $DBLink;
        $rs = mysqli_query($DBLink,$SQL);
        
        if(!$rs){
            echo "<LI>ERROR SQL : $SQL";
            echo "<LI>".mysqli_errno($DBLink) . ": " . mysqli_error($DBLink);
            return false;
        }else{
            return true;
        }
    }

    
    function GetMemberData($t_member_id,$t_member_lot="1"){
        
        Global $DBLink;
        $SQL = "Select a.id t_member_id,'$t_member_lot' t_member_lot,a.nick_name t_user_id,a.first_name t_full_name,a.status AS mast_status,date(a.join_date) d_join,a.country_id t_country_id 
                FROM ent_member a WHERE a.id='$t_member_id'";
        $r_member = GetSQLAssoc($SQL);

        $SQL = "SELECT sponsor_id t_sponsor_id, sponsor_lot t_sponsor_lot
                FROM ent_member_tree_sponsor
                WHERE member_id = '$t_member_id' and member_lot='0'";
        $r_sponsor = GetSQLAssoc($SQL);

        $SQL = "SELECT b.member_id as t_upline_id, b.member_lot as t_upline_lot, a.leg_no t_leg_no
                FROM ent_member_tree_sponsor a 
                INNER JOIN ent_member_tree_sponsor b on a.upline_id = b.id
                WHERE a.member_id = '$t_member_id' and a.member_lot='$t_member_lot' and a.upline_id <> 0";
        $r_upline = GetSQLAssoc($SQL);
        
        if (!$r_sponsor) {
            $r_sponsor = array();
            $r_sponsor['t_sponsor_id'] = 0;
            $r_sponsor['t_sponsor_lot'] = 0;
        }

        if (!$r_upline) {
            $r_upline = array();
            $r_upline['t_upline_id'] = 0;
            $r_upline['t_upline_lot'] = 0;
            $r_upline['t_leg_no'] = 0;
        }

        $r_return = array_merge($r_member,$r_sponsor,$r_upline);

        return $r_return;
    }

    function GetUserID($t_member_id){
        Global $DBLink;
        $SQL = "Select t_user_id FROM tbl_member WHERE t_member_id='$t_member_id'";
        $rs = mysqli_query($DBLink,$SQL);
        
        if(!$rs){
            echo "<LI>".mysql_errno() . ": " . mysql_error();
            return false;
        }
        $r = mysql_fetch_assoc($rs);

        return $r['t_user_id'];
    }

    function InsertWalletTran($t_wallet_id, $t_wallet_type, $dt_tran, $t_center_id, $t_doc_no, $t_wallet_tran_type, $t_remark, $f_cash_point,$allow_negative=0){

        if ($allow_negative<>1 && $f_cash_point<=0) {
            return false;
        }

        // $f_cash_point = number_format($f_cash_point,2,".","");

        $SQL="INSERT INTO tbl_wallet_tran (t_wallet_id, t_wallet_type, dt_tran, t_center_id, t_doc_no, t_wallet_tran_type, t_remark, f_cash_point)
        VALUES ('$t_wallet_id', '$t_wallet_type', '$dt_tran', '$t_center_id', '$t_doc_no', '$t_wallet_tran_type', '$t_remark', $f_cash_point)";
 
        if(RunSQL($SQL)){
            $SQL="SELECT * FROM tbl_wallet_type_bal WHERE t_wallet_id='$t_wallet_id' AND t_wallet_type='$t_wallet_type'";
            $r=GetSQLAssoc($SQL);

            if ($r) {
                $SQL="UPDATE tbl_wallet_type_bal SET f_cash_point=f_cash_point+($f_cash_point) WHERE t_wallet_id='$t_wallet_id' AND t_wallet_type='$t_wallet_type'";
                RunSQL($SQL);
        
            }else{
                $SQL="INSERT INTO tbl_wallet_type_bal (t_wallet_id,t_wallet_type,f_cash_point) VALUES ('$t_wallet_id','$t_wallet_type','$f_cash_point')";
                RunSQL($SQL);
            }
            return true;
        }else{
            return false;
        }
    }

    function TransferBonusFromWallet($t_member_id, $t_wallet_type_fr, $t_wallet_type_to, $f_amount, $f_perc, $t_remark, $t_doc_no, $allow_negative=0){

// echo "<LI> Transfer IN : $t_member_id, $t_wallet_type_fr, $t_wallet_type_to, $f_amount, $f_perc, $t_remark, $allow_negative";
        
        $f_bal=CheckWalletBal($t_member_id,$t_wallet_type_fr);
// echo "<LI> Bal : $f_bal";
        if ($allow_negative<>1 && $f_bal <= 0) {
            return false;
        }
// echo "<LI> Amount : $f_amount <= 0 || $f_perc <= 0";
        if ($f_amount <= 0 || $f_perc <= 0) {
            return false;
        }
        $f_perc = $f_perc / 100;
        $f_cash_point = ROUND($f_amount * $f_perc, 2);

        if ($f_bal < $f_cash_point) {
            return false;
        }

        InsertWalletTran($t_member_id, $t_wallet_type_fr, date("Y-m-d H:i:s"), "MAIN", $t_doc_no, "分配", $t_remark, $f_cash_point * (-1),1);
        InsertWalletTran($t_member_id, $t_wallet_type_to, date("Y-m-d H:i:s"), "MAIN", $t_doc_no, "分配", $t_remark, $f_cash_point);

        return true;
    }

    function CheckWalletBal($t_member_id,$t_wallet_type){

        $SQL="SELECT * FROM ewt_summary WHERE member_id='$t_member_id' AND ewallet_type_id='$t_wallet_type'";
        $a_bal = GetSQLAssoc($SQL);

        if ($a_bal && $a_bal['balance']) {
            return $a_bal['balance'];
        }else{
            return 0;
        }
    }

    // Build bonus Sales from tbl_sales_mast, ignore tbl_sales_lot
    // Example param $arr_plan_purchase= array("REG","UPG","REP")
    function BuildLiveSalesFrMast($arr_plan_purchase,$t_bns_id=""){
        Global $DBLink;

    	 if ($arr_plan_purchase<>"" && is_array($arr_plan_purchase)) {
            foreach ($arr_plan_purchase as $key => $value) {
                
                if ($key>0) {
                    $SQL_con_in .=",";
                }
                $SQL_con_in .= "'$value'";
            }
            $SQL_con = " AND (s.action IN ($SQL_con_in) OR s.bns_action IN ($SQL_con_in))";
        }

        if ($t_bns_id == "") {
            $t_bns_id = date("Y-m-d");
        }

        $SQL_con .= " AND s.bns_batch = '$t_bns_id'";
        

        $SQL="INSERT INTO tbl_bonus_sales (t_center_id, t_doc_no, t_mobile_id, t_member_id, t_member_lot, d_approve, t_bns_id, t_item_id, t_type, t_plan_type, t_price_type, t_currency_code, f_dp, f_pv, f_bv, f_sv, f_leverage, t_user_create, dt_user_create)
        SELECT s.center_id, s.doc_no, '', s.member_id, '01', date(s.created_at), date(s.bns_batch), s.prd_master_id, s.grp_type, IF(s.bns_action IS NULL,s.action,s.bns_action ), '', '', s.total_amount, s.total_pv, s.total_bv, s.total_sv, s.leverage, s.created_by, s.created_at
        FROM sls_master s 
        INNER JOIN prd_master p ON  s.prd_master_id=p.id
        LEFT JOIN tbl_bonus_sales b ON s.doc_no=b.t_doc_no
        WHERE s.bns_batch IS NOT NULL AND b.t_doc_no is NULL AND (s.cancelled_by IS NULL || s.cancelled_by = '' ) ".$SQL_con; 
        
echo "<LI>SALES SQL : $SQL";

        if(!mysqli_query($DBLink,$SQL)){
            echo "<LI>".mysqli_errno($DBLink) . ": " . mysqli_error($DBLink);
            return false;
        }
// echo "<LI>SALES SQL return";
        return true;
    }

    function BuildAdjustSales($t_bns_id){

        $SQL="INSERT INTO tbl_bonus_sales SELECT * FROM tbl_bonus_sales_adj WHERE t_bns_id = '$t_bns_id'";
        RunSQL($SQL);

        return true;
    }

    function GetPackageRank($t_package_id){
        $SQL="SELECT * FROM tbl_item WHERE t_item_id='$t_package_id'";
            $r_package=GetSQLAssoc($SQL);

            if ($r_package['t_lot_package']<>"") {
                $SQL="SELECT * FROM tbl_rank_package WHERE t_rank_package_id='".$r_package['t_lot_package']."'";
                $r_rank_package=GetSQLAssoc($SQL);

                if ($r_rank_package) {
                    $a_sales['t_rank_package_id']=$r_rank_package['t_rank_package_id'];
                    $a_sales['t_rank_eff']=$r_rank_package['t_lot_rank'];
                }else{
                    return false;
                }
            }

        return $a_sales;
    }

    function GetMemberRank($t_member_id,$t_member_lot="01",$t_bns_id){
        
        $SQL="SELECT * FROM tbl_bonus_rank WHERE t_member_id='$t_member_id' AND t_member_lot='$t_member_lot' AND t_bns_fr <= '$t_bns_id' AND t_bns_to>='$t_bns_id'";        
        $r_rank=GetSQLAssoc($SQL);

        if (!$r_rank) {
            $SQL="SELECT * FROM tbl_bonus_rank WHERE t_member_id='$t_member_id' AND t_member_lot='$t_member_lot' AND t_bns_fr <= DATE_SUB('$t_bns_id',INTERVAL 1 DAY) AND t_bns_to>=DATE_SUB('$t_bns_id',INTERVAL 1 DAY)";
            $r_rank=GetSQLAssoc($SQL);
        }

        if (!$r_rank) {
            $SQL="SELECT a.member_id t_member_id, a.member_lot t_member_lot, b.lot_rank AS t_rank_eff, a.package AS t_package_id, 'A' t_status FROM ent_member_tree_sponsor a LEFT JOIN prd_master b ON a.package=b.sku_code WHERE member_id='$t_member_id' AND member_lot='$t_member_lot'";
            $r_rank=GetSQLAssoc($SQL);
// echo "<LI>RANK SQL : $SQL";            
        }
/*
        $SQL = "SELECT a.t_member_id,'01' AS t_member_lot,a.t_package_id,b.t_lot_rank AS t_rank_eff, 'A' t_status FROM tbl_sales_mast a INNER JOIN
tbl_item b ON a.t_package_id=b.t_item_id
WHERE a.t_member_id='$t_member_id' AND a.t_plan_purchase in ('REG','UPG','RENP') AND a.d_expire > '$t_bns_id' AND a.d_bonus_pv_month <= '$t_bns_id' ORDER BY b.t_lot_rank DESC LIMIT 1";
    
        $r_rank=GetSQLAssoc($SQL);
*/
        return $r_rank;
    }

    function GetMemberMatchRank($t_member_id,$t_member_lot="01",$t_bns_id){
        
        $SQL="SELECT t_rank_eff FROM tbl_bonus_pair_acc WHERE t_member_id='$t_member_id' AND t_member_lot='$t_member_lot' AND t_bns_fr <= '$t_bns_id' AND t_bns_to>='$t_bns_id'";        
        $r_rank=GetSQLAssoc($SQL);

        if (!$r_rank) {
            $SQL="SELECT t_rank_eff FROM tbl_bonus_pair_acc WHERE t_member_id='$t_member_id' AND t_member_lot='$t_member_lot' AND t_bns_fr <= DATE_SUB('$t_bns_id',INTERVAL 1 DAY) AND t_bns_to>=DATE_SUB('$t_bns_id',INTERVAL 1 DAY)";
            $r_rank=GetSQLAssoc($SQL);
        }

        if (!$r_rank) {         
            $r_rank['t_rank_eff']="00";
        }

        return $r_rank;
    }

    function GetMemberStarRank($t_member_id,$t_member_lot="01",$t_bns_id){
        
        $SQL="SELECT t_rank_eff FROM tbl_bonus_rank_star WHERE t_bns_fr <='$t_bns_id' AND t_bns_to >='$t_bns_id' AND t_member_id='$t_member_id' AND t_member_lot='$t_member_lot'";        
        $r_rank=GetSQLAssoc($SQL);

        if (!$r_rank) {         
            $r_rank['t_rank_eff']="00";
        }

        return $r_rank['t_rank_eff'];
    }

    function ConvertBonusMth($t_bns_id){
        
        $t_bns_mth=substr(str_replace("-", "", $t_bns_id), 0,6);
        return$t_bns_mth;

    }

    function CheckMemberMaintain($t_bns_id,$t_member_id,$sales_time){
  
        $SQL="SELECT * FROM active_history WHERE member_id = '$t_member_id' AND start_time <= DATE_ADD('$sales_time', INTERVAL 5 second) ORDER BY start_time DESC LIMIT 1";
        $r_maintain=GetSQLAssoc($SQL);
// echo "<LI>Maintain SQL : $SQL";
     
        if (!$r_maintain ||  ($r_maintain['end_time'] < $sales_time && $r_maintain['end_time'] <> NULL) ||  $r_maintain['status'] <> 'A') {
            return false;
        }else{
            return true;
        }
    }

    ## $a_tables_bonus = array("tbl_bonus","tbl_bonus_unilevel") - tables with t_bns_id as key
    ## $a_tables_fr = array("tbl_bonus_pair","tbl_bonus_lvl_pair") - tables that hv t_bns_fr, t_bns_to
    function PrepareRerunTables($t_bns_id, $a_tables_bonus="", $a_tables_fr="",$a_tables_mth="",$a_tables_mth_fr=""){
        
        if (is_array($a_tables_bonus)) {
            
            foreach ($a_tables_bonus as $key => $value) {
                $SQL="DELETE FROM $value WHERE t_bns_id >= '$t_bns_id'";
                RunSQL($SQL);
            }
        }

        if (is_array($a_tables_fr)) {
            foreach ($a_tables_fr as $key => $value) {
                $SQL="DELETE FROM $value WHERE t_bns_fr >= '$t_bns_id'";
                RunSQL($SQL);
                // $SQL="UPDATE $value SET t_bns_to=DATE_SUB('$t_bns_id',INTERVAL 1 DAY), b_latest=1 WHERE t_bns_fr <= DATE_SUB('$t_bns_id',INTERVAL 1 DAY) AND t_bns_to >= DATE_SUB('$t_bns_id',INTERVAL 1 DAY)";
                $SQL="UPDATE $value SET t_bns_to='$t_bns_id', b_latest=1 WHERE t_bns_to = DATE_SUB('$t_bns_id',INTERVAL 1 DAY)";
                RunSQL($SQL);
            }
        }

        if (is_array($a_tables_mth)) {
            $t_bns_mth=ConvertBonusMth($t_bns_id);
            foreach ($a_tables_mth as $key => $value) {
                $SQL="DELETE FROM $value WHERE t_bns_mth >= '$t_bns_mth'";
                RunSQL($SQL);
            }
        }

         if (is_array($a_tables_mth_fr)) {
            $t_bns_mth=ConvertBonusMth($t_bns_id);
            foreach ($a_tables_mth_fr as $key => $value) {
                $SQL="DELETE FROM $value WHERE t_bns_mth_fr >= '$t_bns_mth'";
                RunSQL($SQL);
                $SQL="UPDATE $value SET t_bns_mth_to=DATE_FORMAT(DATE_SUB('$t_bns_id',INTERVAL 1 MONTH),'%Y%m'), b_latest=1 WHERE t_bns_mth_fr<=DATE_FORMAT(DATE_SUB('$t_bns_id',INTERVAL 1 MONTH),'%Y%m') AND t_bns_mth_to>=DATE_FORMAT(DATE_SUB('$t_bns_id',INTERVAL 1 MONTH),'%Y%m')";
                RunSQL($SQL);
            }
        }

        return true;
    }

    function UpdateDailyPeriod($t_bns_id){

        $datime=date("Y-m-d H:i:s");
        $SQL="UPDATE tbl_bns_period SET dt_user_calc='$datime'";
        RunSQL($SQL);

    }

    function AddDailyPeriod($t_bns_id){

        $SQL="INSERT rwd_period (type, batch_code, date_from, date_to, active, created_by) VALUES ('DAILY','$t_bns_id','$t_bns_id 00:00:00','$t_bns_id 23:59:59',1,0)";

        RunSQL($SQL);
    }

    function LoopUp($table,$id_field,$lot_field,$start_id,$start_lot="01",$call_loop_function,$a_data=array()){            

        $a_loop=array();

        $a_loop['loop_id']=$start_id;
        $a_loop['loop_lot']=$start_lot;
        $a_loop['lvl']=1;
        $loop_chk=array();
        while ($a_loop['loop_id']<>false && $a_loop['loop_id']<>"0") {
           
            $a_sponsor=GetMemberData($a_loop['loop_id'],$a_loop['loop_lot']);

            if ($a_loop['lvl']==1) {
                $a_mem=$a_sponsor;
                $a_sponsor=GetMemberData($a_sponsor[$id_field],$a_sponsor[$lot_field]);
            }

            if (!$a_sponsor){
                $a_loop['loop_id']=0;
                echo "<LI>ERROR : sponsor ID not found ".$a_loop['loop_id'];
            }else{
                $a_loop['loop_id']=$a_sponsor[$id_field];
                $a_loop['loop_lot']=$a_sponsor[$lot_field];
            }

            //Check if infinity loop
            if ($loop_chk[$a_loop['loop_id']][$a_loop['loop_lot']]) {
                echo "<LI>ERROR : sponsor ID Infinity Loop";
                break;
            }else{
                $loop_chk[$a_loop['loop_id']][$a_loop['loop_lot']]=1;
            }

            if ($call_loop_function) {

                ## $a_mem : Downline Data, $a_sponsor : Upline Data, $a_loop :  next Sponsor
                $a_return=$call_loop_function($a_mem,$a_sponsor,$a_loop,$a_return,$a_data);

                if ($a_return['break']==1) {
                    break;
                }
            }
            $a_mem=$a_sponsor;

            $a_loop['lvl']++;
        }

        return true;
    }


    function bgRunLiveBNS(){

        $sub_domain = substr(strtolower($_SERVER['HTTP_HOST']), 0,4);

        $folder = "";

        $domain = "localhost";

        $cmd = "curl 'http://$domain/bns/c_bns_live_queue.php'";

// echo "<LI>$cmd";

        background_process($cmd,date('Ymd')."bns_live");

    }
    
    function background_process($cmd,$log_filename="bg_log"){
    
        $command = 'nohup '.$cmd.' >> /tmp/'.date("Ymd").'_'.$log_filename.'.log 2>&1 & echo $!';
        $process_id=exec($command ,$op);  

        return $process_id;
    }

    ## Check if a linux process if running
    function checkProcessID($pid){
        exec("ps -p $pid", $output);

        // echo "<LI>COUNT ".count($output);

        // echo "<pre>";
        // print_r($output);
        // echo "</pre>";

        if (count($output) > 1) {
            return true;
        }else{
            return false;       
        }
    }

    function getMySQLID(){
        Global $DBLink;
        return $DBLink->thread_id;
    }

    function checkMySQLProcessID($pid){
        
        $SQL="SELECT * FROM information_schema.processlist WHERE ID='$pid'";
        $process=GetSQLAssoc($SQL);

        if ($process) {
            return true;
        }else{
            return false;
        }
    }


    function checkQueue($table,$pid,$status){
        if ($pid && $status=="") {
            $running=checkMySQLProcessID($pid);

            if (!$running) {
                $SQL="UPDATE $table SET t_status='Error' WHERE id='".$r['id']."'";
                RunSQL($SQL);
            }elseif ($r['minute_passed']>60) {
                $SQL="UPDATE $table SET t_status='Late' WHERE id='".$r['id']."'";
                RunSQL($SQL);
            }else{
                    echo "<LI>Process Still Running
                    ";
                    exit;
            }
        }

        return true;
    }    

    function getCustomSetting($t_bns_id, $t_member_id, $t_field){

        $SQL="SELECT * FROM tbl_bonus_custom_setting WHERE t_bns_id <= '$t_bns_id' AND t_member_id='$t_member_id' ORDER BY t_bns_id DESC, id DESC LIMIT 1";
        $bns_setting=GetSQLAssoc($SQL);

// echo "<LI>CUSTOM $t_field: $SQL";

        if ($bns_setting[$t_field] >= 0 ) {
            
            // echo "<LI>Return ".$bns_setting[$t_field];
            return $bns_setting[$t_field] / 100;

        }else{
            // echo "<LI>Return No";
            return "no";
        } 
    }

    function getIncomeCap($t_member_id,$t_bns_id){

/*
        $SQL = "SELECT t_center_id, t_doc_no FROM tbl_sales_mast WHERE t_package_id LIKE 'EP%' AND d_bonus_pv_month >= '$t_bns_id' AND t_user_approve <> '' AND t_user_cancel = '' AND t_member_id ='$t_member_id'";
        $ep=GetSQLAssoc($SQL);

        if ($ep) {
            $bns_cap['f_cap'] = 9999999999;
        }else{
            $SQL = "SELECT SUM(f_cap) f_cap FROM tbl_sales_mast 
                WHERE t_user_approve <> '' AND t_user_cancel = '' AND d_expire >= '$t_bns_id' AND t_member_id ='$t_member_id'";

            $bns_cap=GetSQLAssoc($SQL);

            if ($bns_cap['f_cap'] == NULL) {
                $bns_cap['f_cap'] = 0;
            }else{
                $bns_cap['f_cap'] = 9999999999;
            }
        }
*/
        return $bns_cap['f_cap'];
    }

    function GetNoteRate($t_bns_id){

        $SQL = "SELECT token_price FROM token_price_movement WHERE created_at <= '$t_bns_id 23:59:59' ORDER BY created_at DESC,id DESC LIMIT 1";
// echo "<LI>NotePrice : $SQL";        
        $a_rate=GetSQLAssoc($SQL);
        $f_rate = $a_rate['token_price'];
// echo "<LI>rate : $f_rate";
        return $f_rate;

    }

    function GetUnitRate($t_bns_id){

        $SQL = "SELECT unit_price FROM unit_price_movement WHERE created_at <= '$t_bns_id 23:59:59' ORDER BY created_at DESC,id DESC LIMIT 1";
// echo "<LI>NotePrice : $SQL";        
        $a_rate=GetSQLAssoc($SQL);
        $f_rate = $a_rate['unit_price'];
// echo "<LI>rate : $f_rate";
        return $f_rate;

    }
    
    function GetDaysDiff($date1,$date2){
        $d1=new DateTime($date1); 
        $d2=new DateTime($date2); 
        $diff=$d2->diff($d1); 
        // print_r( $diff ) ;
        return $diff->days;
    }

    function GetLeverage($t_member_id){
        $SQL = "SELECT leverage FROM ent_member_tree_sponsor WHERE member_id='$t_member_id'";
        $a_leverage=GetSQLAssoc($SQL);
        $f_leverage = $a_leverage['f_index'];
        return $f_leverage;
    }