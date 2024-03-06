<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" />
</head>
<body>
<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css'>
<style type="text/css">
    .clear{clear: both;}
    .rdft{color:#ff0000;}
    .pg_h3{margin-left:20px;}
    .pg_cnt{margin:20px;}
    .chdiv{width: 100%; margin: 5px;}
    table td,table th{text-align: center;vertical-align: middle!important;}
    table td:nth-child(1),table th:nth-child(1),table td:nth-child(2),table th:nth-child(2){text-align: left;}
    .chdiv table{margin-bottom: 0px;}
    .chdiv table td{border: #ccc solid 1px; text-align: center;}
    .chdiv table tr.pertr td{border: none; padding: 2px 8px; font-size: 12px; font-style: italic;}
    .table{margin-bottom: 0px;}
    .pl_info{width: 100%; margin: 10px 0px 25px 0px; padding-bottom: 10px; border: #ccc solid 1px; overflow: auto;}
    .httbl{border:#ccc solid 1px;}
    .frmdiv{width: 100%;}
    .frmdiv .frm_ele{float: left; width: 30%; padding: 5px;}
    .frmdiv .frm_ele input,.frmdiv .frm_ele select{width: 100%; padding: 7px; border: #ccc solid 1px;}
    .res_td h4{float: left; margin-right: 15px; margin-top: 55px;}
    .res_td .chdiv{width: 150px; float: left; border: #ccc solid 1px;}
    .res_td .chdiv table tr.pertr td{padding: 8px; font-size: 14px;}
    .usrpts{border-left: none!important;}
    .sttd:nth-child(even){background: #f1f1f1;}
    .sttd .chdiv{margin: 5px 0px;width: 175px;}
    .sttd label{font-size: 12px;}
    .chdiv table tr.mwtr td{border: none; padding: 2px 8px; font-size: 12px; font-style: italic; background-color: #ccc;}
</style>
<?php 
$type_arr = array();
$player_info = $data['player_list'];
$column_list = $data['column_list'];
foreach($column_list as $column){
    $type_arr[$column] = ucfirst(str_replace("_"," ",$column));
}
$colspan = count($column_list) - 2;
$user_point = "";
$user_type = $column_list['0'];
$user_prob = 0;
if(isset($data['user_input']) && $data['user_input'] != ""){
    //echo "<pre>";print_r($data['user_input']);die;
    if(isset($data['user_input']['frmsubmit']) && $data['user_input']['frmsubmit'] == "Submit"){
        $user_point = number_format($data['user_input']['user_value'],"0",".","");
        $user_point_val = $user_point + 0.5;
        $user_type = $data['user_input']['type'];
        $usr_stat_list = array();
        if($player_info[$user_type.'_list'] != ""){
            $usr_stat_list = explode(",",$player_info[$user_type.'_list']);
        }
        $user_prob = get_custom_probability($usr_stat_list,$player_info[$user_type],$user_point_val);
    }
}
?>
<div class="pg_cnt">
    <h3 class="pg_h3"><?php echo $data['season_info']['home']." vs ".$data['season_info']['away']." - ".$data['season_info']['season_scheduled_date']; ?>(UTC)</h3>
    <div class="pl_info">
        <?php if(!empty($player_info)){ ?>
            <table class='table table-striped'>
                <thead>
                    <tr>
                        <th>PlayerName</th>
                        <th>Team</th>
                        <?php foreach($column_list as $column){ ?>
                            <th class="sttd"><?php echo ucfirst(str_replace("_"," ",$column)); ?></th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                           <?php echo $player_info['player_name']; ?> 
                        </td>
                        <td>
                           <?php echo $player_info['team_name']; ?> 
                        </td>
                        <?php foreach($column_list as $column){
                            $stat_list = array();
                            if($player_info[$column.'_list'] != ""){
                                $stat_list = explode(",",$player_info[$column.'_list']);
                            }
                            $pts_lmt = round(get_stand_deviation($stat_list));
                            $pts_lower = $pts_upper = 0;
                            $pts_mid = $player_info[$column] + 0.5;
                            if($pts_lmt > 0){
                                $pts_lower = round($player_info[$column] - $pts_lmt) + 0.5;
                                $pts_upper = round($player_info[$column] + $pts_lmt) + 0.5;
                                if($pts_lower < 0){
                                    $pts_lower = 0;
                                }
                            }
                            $pts_lower_per = get_points_probability($stat_list,$pts_lower);
                            $pts_mid_per = get_points_probability($stat_list,$pts_mid);
                            $pts_upper_per = get_points_probability($stat_list,$pts_upper);
                            $minmax = get_min_max_stats($stat_list);

                            $ptc_css = "";
                            if(is_numeric($player_info['match_'.$column]) && $player_info['match_'.$column] >= 0 && $player_info[$column] >= 0){
                                $pts_dev = ($player_info['match_'.$column] * 30 / 100);
                                if($player_info[$column] < ($player_info['match_'.$column] - $pts_dev) || $player_info[$column] > ($player_info['match_'.$column] + $pts_dev)){
                                    $ptc_css = "rdft";
                                }
                            }
                        ?>
                            <td class="sttd">
                                <label class="<?php echo $ptc_css; ?>">Predicted : <?php echo $player_info[$column]; ?></label> | 
                                <label class="<?php echo $ptc_css; ?>">Match : <?php echo $player_info['match_'.$column]; ?></label><br/>
                                <div class="chdiv">
                                    <table class='table table-striped' border="0">
                                        <tbody>
                                            <tr class="pertr">
                                                <td><?php echo $pts_lower_per; ?> pts</td>
                                                <td><?php echo $pts_mid_per; ?> pts</td>
                                                <td><?php echo $pts_upper_per; ?> pts</td>
                                            </tr>
                                            <tr>
                                                <td><?php echo $pts_lower; ?></td>
                                                <td><?php echo $pts_mid; ?></td>
                                                <td><?php echo $pts_upper; ?></td>
                                            </tr>
                                            <tr class="pertr">
                                                <td><?php echo 100-$pts_lower_per; ?> pts</td>
                                                <td><?php echo 100-$pts_mid_per; ?> pts</td>
                                                <td><?php echo 100-$pts_upper_per; ?> pts</td>
                                            </tr class="pertr">
                                            <tr class="mwtr">
                                                <td colspan="3">Min : <?php echo $minmax['min']; ?> | Max: <?php echo $minmax['max']; ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <h4>Check Prediction : </h4>
                            <form name="frm" id="frm" method="post">
                                <div class="frmdiv">
                                    <div class="frm_ele">
                                        <select name="type" id="type">
                                            <?php foreach($type_arr as $key=>$val){
                                                $selected = "";
                                                if($key == $user_type){
                                                    $selected = "selected=selected";
                                                }
                                            ?>
                                                <option <?php echo $selected; ?> value="<?php echo $key; ?>"><?php echo $val; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="frm_ele">
                                        <input type="number" name="user_value" id="user_value" placeholder="Please enter value" required="1" value="<?php echo $user_point; ?>">
                                    </div>
                                    <div class="frm_ele">
                                        <input type="submit" name="frmsubmit" id="frmsubmit">
                                    </div>
                                </div>
                            </form>
                        </td>
                        <td colspan="<?php echo $colspan; ?>" class="res_td">
                            <?php if($user_point != ""){ ?>
                                <h4>Result : </h4>
                                <div class="chdiv">
                                    <table class='table table-striped'>
                                        <tbody>
                                            <tr class="pertr">
                                                <td><b>Over</b> - <?php echo number_format($user_prob,2)." pts"; ?></td>
                                            </tr>
                                            <tr>
                                                <td class="usrpts"><?php echo $user_point_val; ?></td>
                                            </tr>
                                            <tr class="pertr">
                                                <td><b>Under</b> - <?php echo number_format(100-$user_prob,2)." pts"; ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            <?php } ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php } ?>
    </div>
    <div class="clear"></div>
    <h4>Match History</h4>
    <table class='table table-striped httbl'>
        <thead>
            <tr>
                <th>SeasonID</th>
                <th>Match</th>
                <th>ScheduleDate(UTC)</th>
                <?php foreach($column_list as $column){ ?>
                    <th><?php echo ucfirst(str_replace("_"," ",$column)); ?></th>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
            <?php 
            if(count($data['match_list']) > 0){
            foreach($data['match_list'] as $row){
            ?>
                <tr>
                    <td><?php echo $row['season_id']; ?></td>
                    <td><?php echo $row['home']." vs ".$row['away']; ?></td>
                    <td><?php echo $row['scheduled_date']; ?></td>
                    <?php foreach($column_list as $column){ ?>
                        <td><?php echo $row[$column]; ?></td>
                    <?php } ?>
                </tr>
            <?php }
            }else{ ?>
                <tr>
                    <td colspan="7">
                        <div class="alert alert-danger">There is no stats available</div>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
</body>
</html>
