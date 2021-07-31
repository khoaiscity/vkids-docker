<?php

    function CalculateBinaryPair($t_bns_id,$a_sales,$a_mem,$a_upline,$lvl,$a_data=""){

// echo "<LI>CalculateBinaryPair - ".$lvl;

        $pair_bv=1;
        $a_pair_perc=array("00"=>0, 10=>0.09, 20=>0.1, 30=>0.11, 40=>0.12, 50=>0.15);
        $a_pair_lot_perc=array("00"=>0, 1=>1, 2=>0.8, 3=>0.8, 4=>0.6, 5=>0.6, 6=>0.6, 7=>0.6, 8=>0.6, 9=>0.6, 10=>0.6, 11=>0.6);
        $a_lot_rank_cap=array("00"=>0, 10=>25, 20=>140, 30=>400, 40=>700, 50=>0, 60=>0);
        $max_pair_acc=0;
        $max_pair=0;
        $min_lvl=0;
        $max_lvl=0;

        if ($max_lvl <> 0 || $min_lvl <> 0) {

            if ($max_lvl<>0 && $lvl > $max_lvl) {
                return true;
            }

            if ($lvl < $min_lvl) {
                return true;
            }
        }

// echo "<LI>================================";
// echo "<LI>DOC : ".$a_sales['t_doc_no'];
// echo "<LI>MEMBER : ";print_r($a_mem);
// echo "<LI>UPLINE : ";print_r($a_upline);


        $t_bns_type="bns_pair";
        $t_member_id=$a_mem['t_member_id'];
        $t_upline_id=$a_upline['t_member_id'];
        $t_upline_lot=$a_upline['t_member_lot'];
        $t_upline_leg=$a_mem['t_leg_no'];
        $i_lft=0;//$a_upline['i_upline_lft'];
        $i_rgt=0;//$a_upline['i_upline_rgt'];
        $i_lvl=0;//$a_upline['i_upline_lvl'];
        $t_rank_eff=$a_upline['t_rank_eff'];
        $t_doc_no=$a_sales['t_doc_no'];
        $t_plan_type=$a_sales['t_plan_type'];
        $sales_time=$a_sales['dt_user_create'];
        

        $tbl_detail="tbl_bonus_pair";
        $where="WHERE t_member_id='$t_upline_id' AND t_member_lot='$t_upline_lot' AND t_bns_fr<='$t_bns_id' AND t_bns_to>='$t_bns_id'";

        $i_b_pair_acc=0;
        $f_b_bf_1=0;
        $f_b_bf_2=0;
        $f_b_cur_1=0;
        $f_b_cur_2=0;
        $i_pair=0;
        $i_b_pair=0;
        $i_b_pair2=0;
        $i_pair_bal=0;
        $f_bns_today=0;
        $f_bns_diff=0;
        $f_b_flush_1=0;
        $f_b_flush_2=0;
        $f_b_match=0;
        $f_b_pair_perc=0;
        $i_b_pair=0;
        $i_b_pair1=0; $i_b_pair2=0; $i_b_pair_acc=0; $f_b_pair_perc=0; $f_b_pair1=0; $f_bns_pair=0; $i_b_pair2=0; $f_b_pair2_perc=0; $f_bns_pair2=0; $f_bns=0; $b_cap=0;

        if (!$t_upline_id || $t_rank_eff == "00" || $t_rank_eff == 0) {
// echo "<LI>PAIR STOP : $t_upline_id | $t_rank_eff";            
            return true;
        }



        $SQL = "SELECT * FROM ent_member_tree_sponsor WHERE member_id='$t_upline_id' AND member_lot='$t_upline_lot'";
        $r_sales = GetSQLAssoc($SQL);

        $lot_create = $r_sales['created_at'];

        $SQL = "SELECT * FROM sls_master WHERE doc_no='$t_doc_no'";
        $r_sales = GetSQLAssoc($SQL); 

        $sales_create = $r_sales['placed_at'];    

// echo "<LI> LOT CREATE : $lot_create < $sales_create";

        if ($t_plan_type=='REG' && $lot_create > $sales_create) {
            
// echo "<LI>LOT LATER : $t_upline_id - $t_upline_lot : $lot_create < $sales_create";
            return true;
        }

        if ($t_plan_type=='REG') {
            $chk_time = $sales_create;
        }else{
            $chk_time = $sales_time;
        }

        if(!CheckMemberMaintain($t_bns_id,$t_upline_id,$chk_time)){
// echo "<LI>Pair Skip : $t_upline_id";
            return true;
        }
        
        $pair_perc = $a_pair_lot_perc[$t_upline_lot];
            

        // $custom_perc = getCustomSetting($t_bns_id, $t_upline_id, "f_perc_pair");
        
        // if ($custom_perc <> "no") {

        //     if ($custom_perc == 0 || $custom_perc > $pair_perc) {
        //         $pair_perc=$custom_perc;
        //     }
        
        // }

// echo "<LI>PAIR LOT : $t_upline_id - $t_upline_lot| PERC : $pair_perc";

        // echo "<LI> Pair Custom : $t_upline_id = $pair_perc";

        $f_bv=$a_sales['f_bv'];
        $f_bv_cap=$a_lot_rank_cap[$t_rank_eff];

// echo "<LI>BV : $f_bv | $t_doc_no";

        $previous_bv=getPairPerviousPassup($t_bns_id,$a_sales['t_member_id'],$a_sales['t_doc_no']);
        
        if ($previous_bv>0 && $previous_bv >= $f_bv_cap) {
            return;
        }elseif ($previous_bv>0 && $previous_bv + $f_bv >= $f_bv_cap) {
            $f_bv = $f_bv_cap - $previous_bv;
        }elseif ($f_bv>$f_bv_cap) {
            $f_bv = $f_bv_cap;
        }
// echo "<LI>BV2 : $f_bv | $t_doc_no";
        $SQL="SELECT * FROM $tbl_detail $where";
        $r_today=GetSQLAssoc($SQL);
// echo "<LI>TODAY:";print_r($r_today);
        if (!$r_today || $r_today['t_bns_fr']<$t_bns_id) {

            if ($r_today && $r_today['t_bns_fr']<$t_bns_id) {
                $SQL="UPDATE $tbl_detail SET t_bns_to=DATE_SUB('$t_bns_id',INTERVAL 1 DAY),b_latest=0 $where";
                RunSQL($SQL);
       
                $r_yest=$r_today;
                $r_today=false;
            }else{
                // echo "<LI>no data";
                $SQL="SELECT * FROM $tbl_detail WHERE t_member_id='$t_upline_id' AND t_member_lot='$t_upline_lot' AND t_bns_fr<=DATE_SUB('$t_bns_id',INTERVAL 1 DAY) AND t_bns_to>=DATE_SUB('$t_bns_id',INTERVAL 1 DAY)";
                $r_yest=GetSQLAssoc($SQL);  

            }

            if ($r_yest) {
                $f_b_bf_1=$r_yest['f_b_cf_1'];
                $f_b_bf_2=$r_yest['f_b_cf_2'];
                $i_b_pair_acc=$r_yest['i_b_pair_acc'];
            }

        }else{
            $f_b_bf_1=$r_today['f_b_bf_1'];
            $f_b_bf_2=$r_today['f_b_bf_2'];
            $f_b_cur_1=$r_today['f_b_cur_1'];
            $f_b_cur_2=$r_today['f_b_cur_2'];
            $i_b_pair_acc=$r_today['i_b_pair_acc']-$r_today['i_b_pair'];
            $f_bns_today=$r_today['f_bns'];
        }

        if ($t_upline_leg==1) {
            $f_b_cur_1 += $f_bv;
        }elseif ($t_upline_leg==2) {
            $f_b_cur_2 += $f_bv;
        }

        $pair2_perc=$a_pair2_perc[$t_rank_eff];
        $f_b_tot_1 = $f_b_bf_1 + $f_b_cur_1; 
        $f_b_tot_2 = $f_b_bf_2 + $f_b_cur_2; 
// echo "<LI> Tot1 : $f_b_tot_1 = $f_b_bf_1 + $f_b_cur_1;";
// echo "<LI> Tot2 : $f_b_tot_2 = $f_b_bf_2 + $f_b_cur_2; ";
        if ($f_b_tot_1 > $f_b_tot_2) {
            // $i_pair = floor($f_b_tot_2 / $pair_bv);
            $i_pair = $f_b_tot_2 / $pair_bv;
        }else{
            // $i_pair = floor($f_b_tot_1 / $pair_bv);
            $i_pair = $f_b_tot_1 / $pair_bv;
        }

        if ($i_pair>=1) {

            if ($max_pair_acc<>0 && $i_b_pair_acc+$i_pair > $max_pair_acc) {
                $i_b_pair = $max_pair_acc - $i_b_pair_acc;
            }else{
                $i_b_pair=$i_pair;
            }
                
            $i_b_pair1 = $i_pair;
            

            $f_b_pair_perc=$pair_perc;
   
            $f_bns_pair = $i_b_pair1 * ($pair_bv*$pair_perc);

            $f_bns = $f_bns_pair; //+$f_bns_pair2

            $a_cap = getPairCap($t_bns_id,$t_upline_id, $t_upline_lot, $t_rank_eff);

            $cap = $a_cap['remain'];
            $cap_flag = $a_cap['cap'];

            if ($cap < $f_bns) {
                $f_bns = $cap;
                $b_cap = 1;
            }         

            // $f_b_match = $i_b_pair*$pair_bv;
            $f_b_match = ceil($f_bns/$pair_perc);

            if ($i_pair_bal > 0) {

                $f_b_flush_1=$i_pair*$pair_bv;
                $f_b_flush_2=$i_pair*$pair_bv;
                $f_b_cf_1=0;
                $f_b_cf_2=0;

               
            }else{

                if ($b_cap==1 && $cap_flag==0) {

// echo "<LI>CAP2 : $b_cap | $f_b_match";    
                    
                    if ($f_b_tot_1 >= $f_b_tot_2) {
                        $f_b_flush_1=floor(($f_b_tot_1-$f_b_match)/2) + $f_b_match;

// echo "<LI>FLUSH1 : $f_b_flush_1=floor(($f_b_tot_1-$f_b_match)/2);";

                        $f_b_flush_2=$f_b_tot_2;
                        $f_b_cf_1=$f_b_tot_1-$f_b_flush_1;

// echo "<LI>CF1 = $f_b_cf_1=$f_b_tot_1-$f_b_flush_1;";

                        $f_b_cf_2=0; 
                    }else{
                        $f_b_flush_1=$f_b_tot_1;
                        $f_b_flush_2=floor(($f_b_tot_2-$f_b_match)/2) + $f_b_match;

// echo "<LI>FLUSH2 : $f_b_flush_2=floor(($f_b_tot_2-$f_b_match)/2);";

                        $f_b_cf_1=0;
                        $f_b_cf_2=$f_b_tot_2-$f_b_flush_2;

// echo "<LI>CF2 : $f_b_cf_2=$f_b_tot_1-$f_b_flush_1;";

                    }

                }elseif ($b_cap==1 && $cap_flag==1) {

                    if ($f_b_tot_1 >= $f_b_tot_2) {
                        $f_b_flush_1=0;
                        $f_b_flush_2=$f_b_tot_2;
                    }else{
                        $f_b_flush_1=$f_b_tot_1;
                        $f_b_flush_2=0;
                    }
                    
                    $f_b_cf_1=$f_b_tot_1-$f_b_flush_1;
                    $f_b_cf_2=$f_b_tot_2-$f_b_flush_2; 
                    
                }else{
                    $f_b_flush_1=$i_pair*$pair_bv;
                    $f_b_flush_2=$i_pair*$pair_bv;
                    $f_b_cf_1=$f_b_tot_1-$f_b_flush_1;
                    $f_b_cf_2=$f_b_tot_2-$f_b_flush_2; 
                }                
            }

            $f_bns_diff=$f_bns-$f_bns_today;

        }else{
            $f_b_cf_1=$f_b_tot_1;
            $f_b_cf_2=$f_b_tot_2;
        }
        
// echo "<LI>BNS : $f_bns_pair = $i_b_pair1 * ($pair_bv*$pair_perc)";
// echo "<LI>CAP : $cap | BNS : $f_bns";

        $t_bns_to = "2099-12-31";

        $i_b_pair_acc += $i_b_pair;
        
// echo "<LI>PAIR TODAY : ";print_r($r_today);
        if (!$r_today || ($r_today['t_bns_fr']<$t_bns_id && $r_today['t_bns_to']>=$t_bns_id)) {

            if ($r_today['t_bns_to']>=$t_bns_id) {
                $SQL="UPDATE $tbl_detail SET t_bns_to=DATE_SUB('$t_bns_id',INTERVAL 1 DAY),b_latest=0 $where";
                RunSQL($SQL);
            }

            $SQL="INSERT INTO $tbl_detail (t_bns_fr, t_bns_to, t_member_id, t_member_lot, t_rank_eff, t_status, i_lft, i_rgt, i_lvl, f_b_bf_1, f_b_bf_2, f_b_cur_1, f_b_cur_2, f_b_tot_1, f_b_tot_2, f_b_flush_1, f_b_flush_2, f_b_cf_1, f_b_cf_2, f_b_match, i_b_pair, i_b_pair_acc, f_b_pair_perc, i_b_pair1, f_bns_pair, i_b_pair2, f_b_pair2_perc, f_bns_pair2, f_bns, b_cap, b_latest)
            VALUES('$t_bns_id', '$t_bns_to', '$t_upline_id', '$t_upline_lot', '$t_rank_eff', '$t_status', '$i_lft', '$i_rgt', '$i_lvl', '$f_b_bf_1', '$f_b_bf_2', '$f_b_cur_1', '$f_b_cur_2', '$f_b_tot_1', '$f_b_tot_2', '$f_b_flush_1', '$f_b_flush_2', '$f_b_cf_1', '$f_b_cf_2', '$f_b_match', '$i_b_pair', '$i_b_pair_acc', '$f_b_pair_perc', '$f_b_pair1', '$f_bns_pair', '$i_b_pair2', '$f_b_pair2_perc', '$f_bns_pair2', '$f_bns', $b_cap ,1)";
            RunSQL($SQL);

// echo "<LI>PAIR INSERT : $SQL";

            $SQL="UPDATE $tbl_detail SET b_latest=0 WHERE t_member_id='$t_upline_id' AND t_member_lot='$t_upline_lot' AND t_bns_to=DATE_SUB('$t_bns_id',INTERVAL 1 DAY)";
            RunSQL($SQL);

        }else{
            $SQL="UPDATE $tbl_detail SET i_lft='$i_lft', i_rgt='$i_rgt', i_lvl='$i_lvl',t_rank_eff='$t_rank_eff', f_b_bf_1='$f_b_bf_1', f_b_bf_2='$f_b_bf_2', f_b_cur_1='$f_b_cur_1', f_b_cur_2='$f_b_cur_2', f_b_tot_1='$f_b_tot_1', f_b_tot_2='$f_b_tot_2', f_b_flush_1='$f_b_flush_1', f_b_flush_2='$f_b_flush_2', f_b_cf_1='$f_b_cf_1', f_b_cf_2='$f_b_cf_2', f_b_match='$f_b_match', i_b_pair='$i_b_pair', i_b_pair_acc='$i_b_pair_acc', f_b_pair_perc='$f_b_pair_perc', i_b_pair1='$i_b_pair1', f_bns_pair='$f_bns_pair', i_b_pair2='$i_b_pair2', f_b_pair2_perc='$f_b_pair2_perc', f_bns_pair2='$f_bns_pair2', f_bns='$f_bns', b_cap=$b_cap $where";
            RunSQL($SQL);
// echo "<LI>PAIR UPDATE : $SQL";            
        }

        if ($f_bns_diff>0) {
            SummarizeBonus($t_bns_id,$a_upline,$t_bns_type,$f_bns_diff,$a_sales,$a_data,$b_cap);          
        }
    }

    function getPairCap($t_bns_id,$t_member_id,$t_member_lot,$t_rank_eff){


        // 1-7,8-14,15-21,22-30

        $a_pair_cap=array("00"=>0, 10=>2000, 20=>8000, 30=>12000, 40=>15000, 50=>0, 60=>0);
        $remain=0;
        $cap = $a_pair_cap[$t_rank_eff];       

        $day = date("d", strtotime($t_bns_id));

        if ($day >= 1 && $day <= 7) {
            $last_monday = date("Y-m-01", strtotime($t_bns_id));
        }elseif ($day >= 8 && $day <= 14) {
            $last_monday = date("Y-m-08", strtotime($t_bns_id));
        }elseif ($day >= 15 && $day <= 21) {
            $last_monday = date("Y-m-15", strtotime($t_bns_id));
        }elseif ($day >= 22 && $day <= 31) {
            $last_monday = date("Y-m-22", strtotime($t_bns_id));
        }

        $today = date("N", strtotime($t_bns_id));
        
        if ($day == 1 || $day == 8 || $day == 15 || $day == 22) {
            $last_monday = $t_bns_id;
        
            $return['remain'] = $cap;
            return $return;
        }

        // else{
        //     $last_monday = date("Y-m-d", strtotime($t_bns_id.'last monday'));
        //     $yesterday = date("Y-m-d", strtotime($t_bns_id.'last monday'));
        // }

        $SQL = "SELECT SUM(f_bns) pair_cap, SUM(b_cap) b_cap FROM tbl_bonus_pair WHERE t_member_id='$t_member_id' AND t_member_lot='$t_member_lot' AND t_bns_fr >= '$last_monday' AND t_bns_fr <= DATE_SUB('$t_bns_id',INTERVAL 1 DAY)";
        $r_bonus = GetSQLAssoc($SQL);

// echo "<LI>$SQL";

        $return = array();
        $return['cap'] = $r_bonus['b_cap'];

        if ($r_bonus['pair_cap'] && $r_bonus['pair_cap']>0) {

            if ($cap >= $r_bonus['pair_cap']) {
                $remain = $cap - $r_bonus['pair_cap'];
            }else{
                $remain = 0;
            }

            $return['remain'] = $remain;
            return $return;
        }else{
            
            $return['remain'] = $cap;
            return $return;
            
        }
    }

    function getPairPerviousPassup($t_bns_id, $t_member_id, $t_doc_no){

        $SQL = "SELECT SUM(f_bv) bv FROM tbl_bonus_sales WHERE t_member_id='$t_member_id' AND t_bns_id<='$t_bns_id' AND t_doc_no<'$t_doc_no'";
        $r_member = GetSQLAssoc($SQL);
        
        if ($r_member['bv']) {
            $bv=$r_member['bv'];
        }else{
            $bv=0;
        }

        return $bv;

    }








