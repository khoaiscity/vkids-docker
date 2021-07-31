<?php
include_once "bns_helper.php";
include_once "bns_sponsor_single_lvl.php";
include_once "bns_binary_pair.php";

    function CalculateBonus($t_bns_id){
        CalculateDailyRerun($t_bns_id);
    }

    //Daily Bonus (Pairing)
    function CalculateDailyLive($t_bns_id="",$a_data=array()) {
        Global $DBLink;
        $start_time = time();

        echo "<LI> LIVESTART - ".date("Y-m-d H:i:s");

        if (!$t_bns_id) {
            $t_bns_id=date("Y-m-d");
        }

        $arr_plan_purchase=array("REG","UPG","REN","RENP","TOPUP","ACT","CONTRACT");
        
        BuildLiveSalesFrMast($arr_plan_purchase,$t_bns_id);

        // $t_bns_id=date("Y-m-d");
        // echo "<LI>BNS ID : ".$t_bns_id; 

        $time=microtime();
        if ($t_bns_id=="") {  
                // $t_bns_id=date("Y-m-d");
                $SQL="UPDATE tbl_bonus_sales SET t_batch='$time' WHERE (dt_bns_cal is NULL) AND (t_batch='' OR t_batch is NULL)";
                RunSQL($SQL);
        }else{
                $SQL="UPDATE tbl_bonus_sales SET t_batch='$time' WHERE t_bns_id='$t_bns_id' AND (dt_bns_cal is NULL) AND (t_batch='' OR t_batch is NULL)";
                RunSQL($SQL);
        }
echo "<LI>UPT Sales : $SQL";

        $SQL="SELECT * FROM tbl_bonus_sales WHERE t_batch='$time' AND t_bns_id='$t_bns_id' ORDER BY dt_user_create";
        $rs=mysqli_query($DBLink, $SQL);
echo "<LI>LIve Sales : $SQL";
        $a_data['live']=1;
        CalculateDailyLoop($t_bns_id,$rs,$a_data);
    }

    function CalculateDailyLoop($t_bns_id,$rs,$a_data=""){
        Global $DBLink;

        $a_tpack_type = array('0','S','T');
        $a_mpack_type = array('M','ML');

        while ($r = mysqli_fetch_assoc($rs)) {
            
            echo "<LI>Doc ".$r['t_doc_no'];
            echo "<LI>Mem ".$r['t_member_id'];


            $a_mem=GetMemberData($r['t_member_id']);

            // echo "<LI>Mem Mast : "; print_r($a_mem);
            
            if ($a_mem) {
                
                if ($r['t_plan_type']=='REG' || $r['t_plan_type']=='UPG') {
                    
// echo "<LI>PromoteRank";
                    PromoteRank($r,$a_mem,$t_bns_id);
// echo "<LI>PromoteRank2";

                }

                $r_rank=GetMemberRank($a_mem['t_member_id'],$a_mem['t_member_lot'],$t_bns_id);
             
                // echo "<LI>Mem : ".print_r($r_rank);

                if ($r_rank) {
                    $a_mem['t_rank_eff']=$r_rank['t_rank_eff'];
                    $a_mem['t_rank_old']=$r_rank['t_rank_old'];
                }else{
                    $a_mem['t_rank_eff']="00";
                    $a_mem['t_rank_old']="00";
                }
            }

            if (!$a_mem){
                echo "<LI>ERROR : Member ID not found ".$r['t_member_id'];
                continue;
            }


            if ($r['t_plan_type']=='REG' || $r['t_plan_type']=='UPG' || $r['t_plan_type']=='REN' || $r['t_plan_type']=='CONTRACT' || $r['t_plan_type']=='TOPUP') {
                // CalculateDailyKeyin($t_bns_id,$r,$a_data);
                
                if ($r['f_bv']>0) {
                    DocPassupSponsor($t_bns_id,$r,$a_mem,1,$a_data);
                    DocPassupUpline($t_bns_id,$r,$a_mem,"",$a_data);
                }

                // if ($r['f_bv']>0 && in_array($r['t_type'], $a_tpack_type)) {
                
                // }
            
            }

            $SQL="UPDATE tbl_bonus_sales SET dt_bns_cal='".date("Y-m-d H:i:s")."' WHERE t_center_id='".$r['t_center_id']."' AND t_doc_no='".$r['t_doc_no']."'";
            RunSQL($SQL);
         }
    }

    function CalculateDailyRerun($t_bns_id,$a_data="") {
        Global $DBLink;
        $start_time = time();

        echo "<LI> BiaoDi START - ".date("Y-m-d H:i:s");


        $SQL="SELECT * FROM rwd_period WHERE batch_code = '$t_bns_id'";
        $check = GetSQLAssoc($SQL);

        if ($check['active'] == 0 || $check['paid'] <> 0 || $check['calculate'] == 2) {
    
            echo "<LI>Bonus already close or paid or calculating";
            exit;
        }

        $mypid=getMySQLID();
        $dt_create = date("Y-m-d H:i:s");
        $t_ref="";
        $t_batch="";

        $SQL="INSERT INTO tbl_bonus_live_queue (process_id, dt_create, t_batch, t_ref) VALUE ('$mypid', NOW(), '$t_batch', '$t_ref')";
        RunSQL($SQL);

        $SQL = "UPDATE rwd_period 
                        SET calculate = 2, 
                            updated_by = '".$_SESSION["t_admin_user_id"]."',
                            updated_at = now()
                        WHERE batch_code = '$t_bns_id'";
        
        // $SQL="UPDATE tbl_bns_period SET b_calculating=1 WHERE t_bns_id = '$t_bns_id'";
        RunSQL($SQL);
        
        // sleep(10); // let live process finish
// echo "<LI>table bonus";
        $a_tables_bonus=array("tbl_bonus","tbl_bonus_sponsor","tbl_bonus_sales"); //"tbl_bonus_generation_passup","tbl_bonus_leverage_passup",
// echo "<LI>table bonus2";
        $a_tables_fr=array("tbl_bonus_pair","tbl_bonus_rank"); //,"tbl_bonus_pair_acc","tbl_bonus_limit","tbl_bonus_pair",
// echo "<LI>table bonus fr";        
        // $a_tables_mth=array("tbl_bonus_maintain");
        // $a_tables_mth_fr=array("tbl_bonus_world_pool");
        PrepareRerunTables($t_bns_id, $a_tables_bonus, $a_tables_fr,$a_tables_mth,$a_tables_mth_fr);
echo "<LI>Prepare table bonus";
        
        // BuildAdjustSales($t_bns_id);

        // while ($t_bns_id <= date("Y-m-d")) {
            echo "<LI>BNS ID : ".$t_bns_id; 

            echo "<LI>Build sales bonus";
            $arr_plan_purchase=array("REG","UPG","REN","RENP","TOPUP","ACT","CONTRACT");
            BuildLiveSalesFrMast($arr_plan_purchase,$t_bns_id);
            
            ExtendBonusToDate($t_bns_id);

            $t_bns_prev=$t_bns_id;        
            // $SQL="SELECT * FROM tbl_bonus_sales WHERE t_bns_id='$t_bns_id' AND t_type IN ('0','S','T') ORDER BY dt_user_create";
            $SQL="SELECT * FROM tbl_bonus_sales WHERE t_bns_id='$t_bns_id' ORDER BY dt_user_create";
            $rs=mysqli_query($DBLink, $SQL);

            if ($a_data=="") {
                $a_data=array();
                $a_data['rerun']=1;
                $a_data['no_pay']=1;
            }

            CalculateDailyLoop($t_bns_id,$rs,$a_data);


            $SQL = "UPDATE rwd_period 
                        SET calculate = 1, 
                            calculated_by = 'AUTO',
                            calculated_at = NOW(),
                            updated_by = 'AUTO".$_SESSION["t_admin_user_id"]."',
                            updated_at = NOW()
                        WHERE batch_code = '$t_bns_id'";
echo "<LI>$SQL";        
             RunSQL($SQL);

            // $t_bns_id=date("Y-m-d", strtotime($t_bns_id.'+1 days'));

        //     if ($t_bns_prev==$t_bns_id) {
        //         echo "<LI> Same BNS ID $t_bns_prev == $t_bns_id";
        //         break;
        //     }
        // }

/*
         // ExtendBonusToDate($t_bns_id);
*/  
        $SQL="UPDATE tbl_bonus_live_queue SET t_status='Done',dt_process=NOW() WHERE process_id='$mypid' AND (t_status='' OR t_status is NULL)";
        RunSQL($SQL);
      
        echo "<LI> DONE - ".date("Y-m-d H:i:s");
    }

    function DocPassupUpline($t_bns_id,$a_sales,$a_mem,$max_lvl="",$a_data=array()){            

echo "<LI>PassupUpline IN";

            $APP_ENV = $GLOBALS['APP_ENV'];
            $upline_id=$a_mem['t_upline_id'];
            $upline_lot=$a_mem['t_upline_lot'];
            $upline_leg=$a_mem['t_leg_no'];
            $chk_max_lvl=0;
            $a_data['sales_mem']=$a_mem;

            if ($max_lvl<>"") {
                $chk_max_lvl=1;
            }

            $lvl=1;
            $loop_chk=array();
            while ($upline_id<>false && $upline_id<>"0") {
                
                if ($chk_max_lvl==1 && $max_lvl>$lvl) {
                    break;
                }

                $a_upline=GetMemberData($upline_id,$upline_lot);
// echo "<pre>";
// print_r($a_upline);
// echo "</pre>";
                if (!$a_upline){
                    $upline_id=0;
                    // echo "<LI>ERROR : Upline ID not found ".$a_sales['t_member_id'];
                }else{
                    $upline_id=$a_upline['t_upline_id'];
                    $upline_lot=$a_upline['t_upline_lot'];
                    $upline_leg=$a_upline['t_leg_no'];
                }

                //Check if infinity loop
                if ($loop_chk[$upline_id][$upline_lot]==1) {
                    echo "<LI>ERROR : Upline ID Infinity Loop : $upline_id - $upline_lot";
                    break;
                }else{
                    $loop_chk[$upline_id][$upline_lot]=1;
                }

                $r_rank=GetMemberRank($a_upline['t_member_id'],1,$t_bns_id);
             
                if ($r_rank) {
                    $a_upline['t_rank_eff']=$r_rank['t_rank_eff'];
                    $a_upline['t_rank_old']=$r_rank['t_rank_old'];
                }else{
                    $a_upline['t_rank_eff']="00";
                    $a_upline['t_rank_old']="00";
                }

                CalculateBinaryPair($t_bns_id,$a_sales,$a_mem,$a_upline,$lvl,$a_data); 

                $a_mem=$a_upline;
                
                $lvl++;
            }

            return true;
    }

    function DocPassupSponsor($t_bns_id,$a_sales,$a_mem,$max_lvl="",$a_data=array()){            
            Global $DBLink;

echo "<LI>PassupSponsor IN";
            
            $sponsor_id=$a_mem['t_sponsor_id'];
            $sponsor_lot=$a_mem['t_sponsor_lot'];
            $sponsor_leg=$a_mem['t_leg_no'];
            $chk_max_lvl=0;
            $a_data['ds_perc_paid']=0;
            $a_data['i_lvl_paid']=0;
            $t_package_id=$a_sales['t_item_id'];
            $a_data['sp_paid'] = 0;


            $SQL="SELECT id, lot_rank FROM prd_master WHERE id='$t_package_id'";
            $r_package=GetSQLAssoc($SQL);
            $a_sales['t_rank_eff']=$r_package['lot_rank'];

            if ($max_lvl<>"") {
                $chk_max_lvl=1;
            }

            $lvl=1;
            $loop_chk=array();
            while ($sponsor_id<>false && $sponsor_id<>"0") {

                // echo "<LI>SponsorID1 : $sponsor_id";

                if ($chk_max_lvl==1 && $max_lvl>$lvl) {
                    break;
                }

                $a_sponsor=GetMemberData($sponsor_id,$sponsor_lot);

                if (!$a_sponsor){
                    $sponsor_id=0;
                    // echo "<LI>ERROR : sponsor ID not found ".$a_sales['t_member_id'];
                }else{
                    $sponsor_id=$a_sponsor['t_sponsor_id'];
                    $sponsor_lot=$a_sponsor['t_sponsor_lot'];
                }

                //Check if infinity loop
                if ($loop_chk[$sponsor_id][$sponsor_lot]==1) {
                    echo "<LI>ERROR : sponsor ID Infinity Loop : $sponsor_id - $sponsor_lot";
                    break;
                }else{
                    $loop_chk[$sponsor_id][$sponsor_lot]=1;
                }

                $r_rank=GetMemberRank($a_sponsor['t_member_id'],1,$t_bns_id);

                if ($r_rank) {
                    $a_sponsor['t_rank_eff']=$r_rank['t_rank_eff'];
                }else{
                    $a_sponsor['t_rank_eff']="00";
                }

                // echo "<LI>SponsorID : $sponsor_id";


                // if ($a_sales['t_plan_type']=="REG" || $a_sales['t_plan_type']=="UPG" || $a_sales['t_plan_type']=="REN" || $a_sales['t_plan_type']=="RENP" || $a_sales['t_plan_type']=="TOPUP") {    
                
                if ($a_sales['t_plan_type']=="REG" || $a_sales['t_plan_type']=="UPG"){
                    $a_data=CalculateSponsorBonus($a_sales,$lvl,$a_sponsor,$t_bns_id,$a_data);    
                }

                $a_mem=$a_sponsor;
                
                $lvl++;
            }

            return true;
    }
 
    function SummarizeBonus($t_bns_id,$a_mem,$t_bns_type,$f_bns,$a_sales="",$a_data=array(),$b_cap = 0){

        $t_bns_type_field = "f_".$t_bns_type;

        if (!$a_mem) {
            $a_mem=GetMemberData($t_member_id);
        }

        $t_member_id=$a_mem['t_member_id'];
        $t_full_name=$a_mem['t_full_name'];
        $t_status=$a_mem['t_status'];

        if (!$a_mem['t_rank_eff']) {
            $r_rank=GetMemberRank($t_member_id,"01",$t_bns_id);
            $t_rank_eff=$r_rank['t_rank_eff'];
            // $t_rank_old=$r_rank['t_rank_old'];
        }else{
            $t_rank_eff=$a_mem['t_rank_eff'];
            // $t_rank_old=$a_mem['t_rank_old'];
        }
        
        if (!$t_member_id) {
            return;
        }

        $t_user_id=$a_mem['t_user_id'];

        $SQL="SELECT * FROM tbl_bonus WHERE t_bns_id='$t_bns_id' AND t_member_id='$t_member_id'";
        $r_bns=GetSQLAssoc($SQL);

// echo "<LI>r_bns SQL : $SQL";
// echo "<LI>r_bns : "; print_r($r_bns);



        // $a_bns=CalculateBnsCap($t_bns_id,$a_mem,$t_bns_type,$f_bns,$r_bns);
        // $f_bns=$a_bns['f_bns'];
        // $b_cap = $a_bns['b_cap'];

        if ($f_bns>0) {
            if (!$r_bns) {     
                
                // $f_note_rate = GetNoteRate($t_bns_id);

                if (!$f_note_rate  || $f_note_rate == "") {
                    $f_note_rate = 1;
                }

                $SQL = "INSERT INTO tbl_bonus (t_bns_id,t_member_id,t_user_id,t_full_name,t_rank_eff,t_status,$t_bns_type_field,f_bns_gross,f_bns_tot,f_note_rate,b_cap) VALUES ('$t_bns_id','$t_member_id','$t_user_id','$t_full_name','$t_rank_eff','$t_status','$f_bns','$f_bns','$f_bns','$f_note_rate','$b_cap')";
                RunSQL($SQL);
// echo "<LI>SUMSQL : $SQL";
            }else{
                $SQL = "UPDATE tbl_bonus SET t_rank_eff='$t_rank_eff',$t_bns_type_field=$t_bns_type_field+$f_bns,f_bns_gross=f_bns_gross+$f_bns,f_bns_tot=f_bns_gross-f_bns_adj,b_cap=$b_cap WHERE t_bns_id='$t_bns_id' AND t_member_id='$t_member_id'";
                RunSQL($SQL);
// echo "<LI>SUMSQL : $SQL";
            }
            // $t_bns_ref="";
            // if ($t_bns_type=="bns_sponsor" || $t_bns_type=="bns_keyin") {
            //     $t_bns_ref=$a_sales['t_member_id'];
            // }

            // elseif ($t_bns_type=="bns_keyin") {
            //     $t_bns_ref=$a_sales['t_center_id']."-".$a_sales['t_doc_no'];
            // }

            // if (!isset($a_data['no_pay']) || $a_data['no_pay']<>1) {
                
            //     if ($t_bns_id >= "2017-02-21") {
            //         if ($t_bns_type == "bns_sponsor" || $t_bns_type == "bns_keyin") {
            //             $SQL="INSERT INTO tbl_bonus_pay (t_bns_id, t_member_id, t_doc_no, t_rank_eff, t_bns_ref, t_bns_type, f_bns) 
            //             VALUES ('$t_bns_id','$t_member_id', '".$a_sales['t_center_id']."-".$a_sales['t_doc_no']."', '$t_rank_eff', '$t_bns_ref', '$t_bns_type', '$f_bns')";
            //             RunSQL($SQL);
            //         }
            //     }
            // }
        }
    }

    function CalculateBnsCap($t_bns_id,$a_mem,$t_bns_type,$f_bns,$r_bns){
        
        // $cap_pair=array("00"=>0,10=>40,20=>250,30=>600,40=>1400,50=>4000,60=>4000);
        // $cap_acc=array("00"=>0,10=>2000,20=>9000,30=>16000,40=>28000,50=>60000,60=>60000);
        // $mem_rank=$a_mem['t_highest_rank'];


/*
        if ($t_bns_type=="bns_pair" || $t_bns_type=="bns_sponsor") { //$t_bns_type=="bns_keyin" || 
            
            $t_member_id = $a_mem['t_member_id'];    
            $cap = getIncomeCap($t_member_id,$t_bns_id);

            if (!$r_bns) {
                $f_bns_sponsor=0;
                $f_bns_pair=0;
                // echo "<LI>IN 0";
            }else{
                $f_bns_sponsor=$r_bns['f_bns_sponsor'];
                $f_bns_pair=$r_bns['f_bns_pair'];
                // echo "<LI>HV | $f_bns_sponsor | $f_bns_pair";
            }

            if ($t_bns_type == "bns_pair") {
                $f_bns_total = $f_bns_pair;
            }elseif ($t_bns_type == "bns_sponsor") {
                $f_bns_total = $f_bns_sponsor;
            }else{
                $f_bns_total = 0;
            }

            if ($f_bns_total+$f_bns > $cap) { //$f_bns_sponsor+
                $a_bns['f_bns']=$cap-$f_bns_total; //-$f_bns_sponsor
                $a_bns['b_cap']=1;
                // echo "<LI>CAP = ".($cap-$f_bns_total);
            }else{
                $a_bns['f_bns']=$f_bns;
                $a_bns['b_cap']=0;
                // echo "<LI> NO CAP = ".($f_bns_total+$f_bns);
            }
           

        }else{
            $a_bns['f_bns']=$f_bns;
            $a_bns['b_cap']=0;
        }
*/
        $a_bns['f_bns']=$f_bns;
        $a_bns['b_cap']=0;
        
        return $a_bns;

    }

    function loopInsertBonus($t_bns_id, $table,$member_field,$bns_field,$bns_type){

        Global $DBLink;

        $SQL = "SELECT $member_field AS t_member_id, SUM($bns_field) AS f_bns
        FROM $table
        WHERE t_bns_id = '$t_bns_id' AND $bns_field > 0
        GROUP BY $member_field
        ";

echo "<LI>Loop : $SQL";

        $rs =mysqli_query($DBLink, $SQL);

        while ($r = mysqli_fetch_assoc($rs)) {
            $t_member_id = $r['t_member_id'];
            $a_mem=GetMemberData($t_member_id);
            $f_bns = $r['f_bns'];

            SummarizeBonus($t_bns_id,$a_mem,$bns_type,$f_bns);
        }
    }

    function UpdateMaintainLive($t_bns_id,$a_sales){

            $t_member_id=$a_sales['t_member_id'];
            $t_member_lot=$a_sales['t_member_lot'];
            $t_bns_mth=ConvertBonusMth($t_bns_id);
            $t_sales_mth=$t_bns_mth;
            $f_pv=$a_sales['f_pv'];
            $f_bv=$a_sales['f_bv'];
            $qualify_bv=30;
            $f_bns=0;

            if ($a_sales['t_plan_type']=='REP') {

                $SQL="SELECT MAX(t_bns_mth) t_bns_mth FROM tbl_bonus_maintain WHERE t_member_id='$t_member_id' AND t_member_lot='$t_member_id' GROUP BY t_member_id,t_member_lot";
                $a_max = GetSQLAssoc($SQL);
                $a_max_mtn=$a_max['t_bns_mth'];
                $t_bns_fr=substr($a_max_mtn, 0,4)."-".substr($a_max_mtn, 4,2)."-01";
                
                if ($f_ppv < $qualify_bv) {
                    $n_month=(-1);
                }else{
                    $n_month=floor($f_ppv/$qualify_bv);
                }
                
            }elseif ($a_sales['t_plan_type']=='REG') {
                $t_bns_fr=$t_bns_id;
                $n_month=1;
                if ($f_pv < ($qualify_bv*2)) {
                    $f_pv = ($qualify_bv*2);
                }
            }else{
                $n_month=0;
            }
// echo "<LI>MAINT :  $t_member_id - $n | $n_month - ".$a_sales['t_plan_type'];
            $n=0;
            while ($n <= $n_month) {
                
                $t_bns_mth_maintain=date('Ym', strtotime($t_bns_fr.'+'.$n.' months'));

                if ($n == $n_month) {
                    $f_ppv = $f_pv - ($qualify_bv * $n);
                }else{
                    $f_ppv = $qualify_bv;
                }

                if ($n==0) {
                    $f_pbv = $f_bv;
                }else{
                    $f_pbv = 0;
                }
// echo "<LI> Code : date('Ym', strtotime($t_bns_mth.'+'.$n.' months')) --  $t_bns_mth_maintain";
// echo "<LI> MAINTIAN MTH : $n - $t_member_id - $t_bns_mth";
                $SQL="SELECT * FROM tbl_bonus_maintain WHERE t_member_id='$t_member_id' AND t_member_lot='$t_member_lot' AND t_bns_mth='$t_bns_mth_maintain'";
                $r_maintain=GetSQLAssoc($SQL);

                if ($r_maintain) {
                    $SQL = "UPDATE tbl_bonus_maintain SET f_pv=f_pv+$f_ppv, f_bv=f_bv+$f_pbv WHERE t_member_id='$t_member_id' AND t_member_lot='$t_member_lot' AND t_bns_mth='$t_bns_mth_maintain'";
                    RunSQL($SQL);
                }else{
                    $SQL="INSERT INTO tbl_bonus_maintain (t_bns_mth, t_member_id, t_member_lot, f_pv, f_bv, f_bns, t_sales_mth) VALUES ('$t_bns_mth_maintain', '$t_member_id', '$t_member_lot', '$f_ppv', '$f_pbv', '$f_bns','$t_sales_mth')";
                    RunSQL($SQL);
                }             
// echo "<LI> MAINTIAN : $SQL";
                $n++;
            }

            return true;
    }

