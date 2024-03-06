<?php
if(!function_exists('cricket_all_rounder'))
{
    function cricket_all_rounder(&$toatlSalary, $finalTeam , $ar, $remain_pos, $maxSalaryUse,$max_ar)
    {
        if($max_ar == '0'){return $finalTeam;}
        $count  = $remain_pos['AR'];
        if($count > count($ar)){
            return $finalTeam;
        }
        $i = 0;
        do{
            $i++;

            shuffle($ar);
            $chunkBow = array_chunk($ar, $count);
            $sum = array_sum(array_values( array_column($chunkBow[0], 'salary', 'player_team_id')));
            if($i == 50){
                $maxSalaryUse = $maxSalaryUse + 1;
            }
            if($i > 75){
               return $finalTeam;
            }
        } while($sum > $maxSalaryUse);

        $toatlSalary = $toatlSalary - $sum;
        $finalTeam = array_merge($finalTeam, $chunkBow[0]);
        
        return $finalTeam;
    }
}

if(!function_exists('cricket_batsman'))
{
    function cricket_batsman(&$toatlSalary, $finalTeam, $bat, $remain_pos, $maxSalaryUse,$max_bat)
    {
        if($max_bat == '0'){return $finalTeam;}
        $i = 0;
        do{
            $i++;
            shuffle($bat);
            $chunkBat = array_chunk($bat, $remain_pos['BAT']);
            $sum = array_sum(array_values( array_column($chunkBat[0], 'salary')));
            if($i > 75){
               return $finalTeam;
            }
        } while($sum > $maxSalaryUse);

        $toatlSalary = $toatlSalary - $sum;
        $finalTeam = array_merge($finalTeam, $chunkBat[0]);
        return $finalTeam;
    }
}


if(!function_exists('cricket_wicket_kipper'))
{
    function cricket_wicket_kipper(&$toatlSalary, $finalTeam, $wk, $remain_pos, $maxSalaryUse,$max_wk)
    {
        if($max_wk == '0'){return $finalTeam;}
        $count  = $remain_pos['WK'];
        if($count > count($wk)){
            return $finalTeam;
        }
        $i = 0;
        do{
            $i++;
            shuffle($wk);
            $chunkBat = array_chunk($wk, $remain_pos['WK']);
            $sum = array_sum(array_values( array_column($chunkBat[0], 'salary')));
            if($i == 25){
                $maxSalaryUse = $maxSalaryUse + 1;
            }
            else if($i == 40){
                if(count($wk) == 1 || $count = count($wk)){
                    $maxSalaryUse = $count * 10; // salary will not be consider because team have only one WK so in case of salary is more then maxSalaryUse then we will manage with other player 
                }
            }
            if($i > 50){
               return $finalTeam;
            }
        } while($sum > $maxSalaryUse);

        $toatlSalary = $toatlSalary - $sum;
        $finalTeam = array_merge($finalTeam, $chunkBat[0]);
        return $finalTeam;
    }
}

if(!function_exists('cricket_bowling'))
{
    function cricket_bowling(&$toatlSalary, $finalTeam, $bow, $remain_pos, $maxSalaryUse,$max_bow)
    {
        if($max_bow == '0'){return $finalTeam;}
        $i = 0;
        do{
            $i++;
            shuffle($bow);
            $chunkBow = array_chunk($bow, $remain_pos['BOW']);
            $sum = array_sum(array_values( array_column($chunkBow[0], 'salary')));
            if($i == 50){
                $maxSalaryUse = $maxSalaryUse + 1;
            }
            if($i > 75){
               return $finalTeam;
            }
        } while($sum > $maxSalaryUse);

        $toatlSalary = $toatlSalary - $sum;
        $finalTeam = array_merge($finalTeam, $chunkBow[0]);
        return $finalTeam;
    }
}

if(!function_exists('systemuser_make_team')){
    function systemuser_make_team($playingPlayer,$maxPlayerperteam,$playerFormation,$sports_id=7){
        if(empty($playingPlayer)){
            return [];
        }
        //echo "<pre>";print_r($playerFormation);die;
        $maxLimitForOneTeamPlayer = $maxPlayerperteam;
        $wk = $bat = $ar = $bow = [];
        $formation = $pos_arr = [];
        if($sports_id == 5){
            $dummy_pos_arr = [ "GK","DF","MF","FW" ]; // its for reffernce
            $pos_arr = array("WK","BOW","AR","BAT");
            $formation = ["1-4-4-2" => "9-35-37-19", "1-5-3-2" =>  "9-44-28-19", "1-3-4-3" =>  "9-26-37-28"];
            $wk = $playingPlayer['GK'];
            $ar = $playingPlayer['MF'];
            $bat = $playingPlayer['FW'];
            $bow = $playingPlayer['DF'];

            $playerFormation['min_wk'] = $playerFormation['min_gk'];
            $playerFormation['max_wk'] = $playerFormation['max_gk'];
            $playerFormation['min_ar'] = $playerFormation['min_mf'];
            $playerFormation['max_ar'] = $playerFormation['max_mf'];
            $playerFormation['min_bat'] = $playerFormation['min_fw'];
            $playerFormation['max_bat'] = $playerFormation['max_fw'];
            $playerFormation['min_bow'] = $playerFormation['min_df'];
            $playerFormation['max_bow'] = $playerFormation['max_df'];
        }else{
            $pos_arr = array("WK","BAT","AR","BOW");
            $wk = $playingPlayer['WK'];
            $bat = $playingPlayer['BAT'];
            $ar = $playingPlayer['AR'];
            $bow = $playingPlayer['BOW'];
            //echo "<pre>";print_r($wk);die;
            if($playerFormation['max_wk'] > '0' && $playerFormation['max_bat'] > '0'
                && $playerFormation['max_ar'] > '0' && $playerFormation['max_bow'] > '0' )
            {
                $formation = array("1-4-1-5" => "10-33-10-46","1-5-1-4" => "10-42-10-37");
                if(count($ar) > 1){
                    $formation["1-4-2-4"] = "10-37-20-37";
                }
                if(count($ar) > 2){
                    $formation["1-4-3-3"] = "10-34-28-28";
                }

                if((count($wk) >= 2) && count($bat) >= 3 &&  count($ar) >= 3 &&  count($bow) >= 3 ){
                    $formation["2-3-3-3"] = "18-26-28-28";
                }
                if((count($wk) >= 3) && count($bat) >= 3 &&  count($ar) >= 2 &&  count($bow) >= 3 ){
                    $formation["3-3-2-3"] = "28-26-19-27";
                }
                if((count($wk) >= 1) && count($bat) >= 3 &&  count($ar) >= 2 &&  count($bow) >= 5 ){
                     $formation["1-3-2-5"] = "9.5-28-18-44.5";
                }
                if((count($wk) >= 1) && count($bat) >= 3 &&  count($ar) >= 4 &&  count($bow) >= 3 ){
                     $formation["1-3-4-3"] = "9.5-27.5-35.5-27.5";
                }

                if( empty($wk) ||  empty($ar) ||  empty($bat) ||  empty($bow) ){
                    return [];
                }
            }else{
                 //Wicket keeper 0
                 if($playerFormation['max_wk'] == '0' && $playerFormation['max_ar'] == '1')
                 {   
                    $formation = array("0-5-1-5" => "0-45-10-44");
                 }elseif($playerFormation['max_wk'] == '0' && $playerFormation['max_ar'] == '2')
                 {   
                    $formation = array("0-5-2-4" => "0-45-18-36","0-4-2-5" => "0-36-18-45");
                 }elseif($playerFormation['max_wk'] == '0' && $playerFormation['max_ar'] == '2' && $playerFormation['max_bat'] >= '6' && $playerFormation['max_bow'] >= '6')
                 {   
                    $formation = array("0-5-2-4" => "0-45-18-36","0-4-2-5" => "0-36-18-45","0-6-2-3" => "0-54-18-27","0-3-2-6" => "0-27-18-54");
                 }elseif($playerFormation['max_wk'] == '0' && $playerFormation['max_ar'] >= '3' )
                 {   
                    $formation = array("0-4-3-4" => "0-36-27-36","0-5-3-3" => "0-47-26-26","0-3-3-5" => "0-26-26-47");
                 } 

                 //All rounder and Wk 0
                 if($playerFormation['max_wk'] == '0' && $playerFormation['max_ar'] == '0' && $playerFormation['max_bat'] >= '6' && $playerFormation['max_bow'] >= '6')
                 {   
                    $formation = array("0-6-0-5" => "0-55-0-44","0-5-0-6" => "0-44-0-55");
                 }

                 // All rounder 0

                 if($playerFormation['max_wk'] == '1' && $playerFormation['max_ar'] == '0' && $playerFormation['max_bat'] >= '6' && $playerFormation['max_bow'] >= '6')
                 {   
                    $formation = array("1-5-0-5" => "10-45-0-44","1-6-0-4" => "10-52-0-37","1-4-0-6" => "10-37-0-52");
                 }elseif($playerFormation['max_wk'] == '2' && $playerFormation['max_ar'] == '0' && $playerFormation['max_bat'] >= '6' && $playerFormation['max_bow'] >= '6')
                 {   
                    $formation = array("2-5-0-4" => "18-45-0-36","2-4-0-5" => "18-36-0-45","2-3-0-6" => "18-27-0-54");
                 }elseif($playerFormation['max_wk'] >= '3' && $playerFormation['max_ar'] == '0' && $playerFormation['max_bat'] >= '6' && $playerFormation['max_bow'] >= '6')
                 {   
                    $formation = array("3-5-0-3" => "27-43-0-29","3-4-0-4" => "27-36-0-36","3-3-0-5" => "27-29-0-43");
                 }

                 //Bow Zero
                 if($playerFormation['max_bow'] == '0' && $playerFormation['max_ar'] >= '6' && $playerFormation['max_bat'] >= '6' && $playerFormation['max_wk'] >= '6')
                 {   
                    $formation = array("1-6-4-0" => "9-54-36-0","2-5-4-0" => "19-44-36-0","2-4-5-0" => "19-36-44-0","3-5-3-0" => "27-45-27-0","2-3-6-0" => "18-28-53-0");
                 }
                 //Bat Zero
                 if($playerFormation['max_bat'] == '0' && $playerFormation['max_ar'] >= '6' && $playerFormation['max_bow'] >= '6' && $playerFormation['max_wk'] >= '6')
                 {   
                    $formation = array("1-0-4-6" => "9-0-36-54","2-0-4-5" => "19-0-36-44","2-0-5-4" => "19-0-44-36","3-0-3-5" => "27-0-27-45","2-0-6-3" => "18-0-53-28");
                 }
            }
        }

        if(empty($formation)){exit();}
        //echo "<pre>";print_r($formation);die;            
        $fkey = array_rand($formation);
        $remain_pos = array_combine($pos_arr, explode("-", $fkey));
        $format_pos_salary = array_combine($pos_arr, explode("-", $formation[$fkey])); 
        
        $maxDoWhileLimit = 3;
        $processAgain = true;
        do{
            $maxDoWhileLimit--;
            $toatlSalary = 100;
            $finalTeam = $teamPlayerCount = []; 
            $maxSalaryUse = (count($wk) > 1) ? $format_pos_salary['WK'] : 11;  // decide position team group by fixed salary cap. [in soccer its G.K]
            $finalTeam = cricket_wicket_kipper($toatlSalary, $finalTeam, $wk, $remain_pos, $maxSalaryUse,$playerFormation['max_wk']);
            $maxSalaryUse = (count($ar) > $remain_pos['AR']) ? $format_pos_salary['AR'] : $format_pos_salary['AR']+1;  // [in soccer its M.F]
            $finalTeam = cricket_all_rounder($toatlSalary, $finalTeam, $ar, $remain_pos, $maxSalaryUse,$playerFormation['max_ar']);
            $maxSalaryUse = $format_pos_salary['BOW'];  // decide position team group by fixed salary cap.  // [in soccer its D.F]
            $finalTeam = cricket_bowling($toatlSalary, $finalTeam, $bow, $remain_pos, $maxSalaryUse,$playerFormation['max_bow']);
            //$maxSalaryUse = (($remain_pos['BAT'] == 4) ? 37 : 46) + 1;
            $maxSalaryUse = $toatlSalary;                            // rest salary is use for BAT team group   // [in soccer its F.W]
            $finalTeam = cricket_batsman($toatlSalary, $finalTeam, $bat, $remain_pos, $maxSalaryUse,$playerFormation['max_bat']);

            // ----- check here any of one team have not more then 7 player in team.
            $processAgain = false;
            foreach($finalTeam as $arr){
                if(isset($teamPlayerCount[$arr['team']])){
                    $teamPlayerCount[$arr['team']]++;
                }else{
                    $teamPlayerCount[$arr['team']] = 1;
                }

                if($teamPlayerCount[$arr['team']] > $maxLimitForOneTeamPlayer){
                    $processAgain = true;
                    break;
                }
            }

        } while ($processAgain && $maxDoWhileLimit >= 0);
        
        return $finalTeam;
    }
}
?>