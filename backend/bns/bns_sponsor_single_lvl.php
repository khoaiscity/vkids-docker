<?php    
    
    ## Single Level Sponsor Bonus
    ## All rank same percentage
    function CalculateSponsorBonus($a_sales,$i_lvl,$a_mem="",$t_bns_id="",$a_data=array()){

// echo "<LI> CalculateSponsorBonus";

        $t_bns_type="bns_sponsor";
        $a_perc=array("00"=>0, 10=>0.1, 20=>0.1, 30=>0.1, 40=>0.1, 50=>0.1);
        $a_rank_limit=array("00"=>0, 10=>90, 20=>550, 30=>1400, 40=>2600);
        $a_package_amt=array("00"=>0, 1=>90, 2=>550, 3=>1400, 4=>2600, 5=>1000, 6=>2000);
        $max_perc = 0.1;
        // $a_perc=array("00"=>0.08, "01"=>0.06, "05"=>0.07, 10=>0.07, 20=>0.08, 30=>0.09, 40=>0.1);

        $f_bv=$a_sales['f_bv'];
        $t_member_id=$a_sales['t_member_id'];
        $t_member_lot=$a_sales['t_member_lot'];
        $t_sales_rank=$a_sales['t_rank_eff'];
        $t_sponsor_id=$a_mem['t_member_id'];
        $t_sponsor_lot=$a_mem['t_member_lot'];
        $t_center_id=$a_sales['t_center_id'];
        $t_doc_no=$a_sales['t_doc_no'];
        $t_item_id=$a_sales['t_item_id'];
        $t_rank_eff=$a_mem['t_rank_eff'];
        $t_plan_type=$a_sales['t_plan_type'];
        $i_lvl_paid=$i_lvl;


        // if (!CheckMemberMaintain($t_bns_id,$t_sponsor_id)) {
        //     return false;
        // }

        $f_sponsor = $a_package_amt[$t_item_id];
        $f_max = $a_rank_limit[$t_rank_eff];
        $f_perc = 0;

        if ($f_max > $a_data['sp_paid'] && $f_sponsor > $a_data['sp_paid']) {
            if ($f_sponsor > $f_max) {
                $f_bns=$f_max - $a_data['sp_paid'];
            }else{
                $f_bns=$f_sponsor - $a_data['sp_paid'];
            }            
        }else{
            return $a_data;
        }
// echo "<LI>t_plan_type = $t_plan_type";
        if ($t_plan_type == "UPG") {
            $SQL = "SELECT SUM(f_bns) paid FROM tbl_bonus_sponsor WHERE t_downline_id = '$t_member_id' AND t_doc_no <> '$t_doc_no'";
            $r_sponsor = GetSQLAssoc($SQL);

            $previous_paid = $r_sponsor['paid'];


            $SQL = "SELECT SUM(f_bns) paid FROM tbl_bonus_sponsor WHERE t_member_id='$t_sponsor_id' AND t_downline_id = '$t_member_id' AND t_doc_no <> '$t_doc_no'";
            $r_received = GetSQLAssoc($SQL);

            $previous_received = $r_sponsor['paid'];
            $diff = $f_sponsor - $previous_paid;

            if ($previous_received >= $f_max || $previous_received > $f_sponsor || $a_data['sp_paid'] >= $diff) {
                return $a_data;
            }
            
            $f_bns -= $previous_received;

            if ($f_bns > $diff) {
                $f_bns = $diff;
            }

// echo "<LI>== previous_paid = $previous_paid | DIFF : $diff";
// echo "<LI>== previous_received = $previous_received | PAID to : $t_sponsor_id";

        }

        
       
    
        

        // echo "<LI> Doc : $t_doc_no | LVL : $i_lvl | MAX : $f_max | Sponsor : $f_sponsor";
        // echo "<LI>$t_bns_id | Member - $t_member_id | Sponsor - $t_sponsor_id | Rank : $t_rank_eff";
        // echo "<LI> Rank Perc : $t_rank_eff -- $t_sales_rank || ".$a_rank_perc[$t_rank_eff] .">". $f_perc_paid;
        // echo "<LI>BNS : $f_bns | Paid : ".$a_data['sp_paid']." | Item : $t_item_id";
        
        if ($f_bns>0) {
            $SQL="INSERT INTO tbl_bonus_sponsor (t_bns_id, t_member_id, t_member_lot, t_downline_id, t_downline_lot, t_center_id, t_doc_no, t_item_id, i_lvl, i_lvl_paid, f_bv, f_perc, f_bns)
        VALUES ('$t_bns_id', '$t_sponsor_id', '$t_sponsor_lot', '$t_member_id', '$t_member_lot', '$t_center_id', '$t_doc_no', '$t_item_id', '$i_lvl', '$i_lvl_paid', '$f_bv', '$f_perc', '$f_bns')";
            RunSQL($SQL);
// echo "<LI>SponsorInsert : $SQL";
            $a_data['sp_paid'] += $f_bns;
 
        }elseif (!$a_mem) {
            echo "<LI> ERROR SPONSOR BONUS : SPONSOR ID NOT FOUND ".$t_sponsor_id." - ".$t_sponsor_lot;
        }

        if ($f_bns>0) {
            SummarizeBonus($t_bns_id,$a_mem,$t_bns_type,$f_bns,$a_sales,$a_data);
        }

        return $a_data;
    }

    function GetSponsorPerc($t_bns_id,$t_member_id){

        $SQL="SELECT * FROM tbl_bonus_rank_sponsor WHERE t_bns_fr <= '$t_bns_id' AND t_bns_to >= '$t_bns_id' AND t_member_id='$t_member_id'";
        $r_perc = GetSQLAssoc($SQL);

        if ($r_perc['f_perc']) {
            $f_perc = $r_perc['f_perc'];
        }else{
            $f_perc=0;
        }

        return $f_perc;
    }





