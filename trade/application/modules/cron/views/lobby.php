<!DOCTYPE html>
<html>
<head>
    <title>Opinion Trading</title>
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css'>
<style type="text/css">
    body{background-image: url("https://predev-vinfotech-org.s3.ap-south-1.amazonaws.com/upload/front_bg.png"); background-size: 100%;}
    a,a:hover{text-decoration: none;color: #000;}
    .clear{clear: both;}
    .pg_cnt{width:100%; min-height: 1000px; padding: 20px; background-color: #f2f2f2; overflow: hidden;}
    .sp_row{width: auto; margin: 20px; float: right;position: absolute;right: 0px; top: 0px;}
    .sp_row label{float: left; font-size: 19px; margin-right: 15px; margin-top: 5px;}
    .sp_row select{width: 150px; padding: 7px; border: #ccc solid 1px; font-size: 19px; background-color: #fff;}
    .fixture_cnt{width: 100%; overflow-x: auto; margin-bottom: 25px; white-space: nowrap;}
    .fixture{width: 275px; display: inline-block; padding: 10px; border-radius: 10px; background-color: #fff; margin: 5px; cursor: pointer; border:#fff solid 1px;}
    .factve{border:#5853C3 solid 1px;}
    .fixture .lbx{float: left; width: 20%; text-align: center;}
    .fixture .mbx{float: left; width: 60%; text-align: center;}
    .fixture .rbx{float: right; width: 20%; text-align: center;}
    .fixture .lbx img,.fixture .rbx img{width: 40px; margin-top: 7px;}
    .fixture .mbx h3{margin: 7px 0px 0px; font-weight: bold; font-size: 19px; color: #333333; overflow: hidden;}
    .fixture .mbx p{margin: 3px 0px; font-size: 13px; color: #706f6f;}
    .clrbtn{background-color: #cb3d3d; padding: 3px 15px; font-size: 13px; color: #fff; border-radius: 10px; margin-left: 10px;}
    .clrbtn:hover{color: #fff;}
    .tab{overflow: hidden;background-color: #f1f1f1;}
    .tp_tabs{width: 20%; float: left;background-color: #fff; border-radius: 20px;}
    .tp_tabs .hbtn{width: 50%!important; margin-bottom: 0px; float: left; font-weight: bold; padding: 10px 15px; background-color: #fff; border-radius: 20px;border: none;outline: none;cursor: pointer;}
    .tp_tabs .hbtn.active{background-color: #5853C3; color: #fff;}
    .tabcontent,.htbcontent{width: 100%; display: none;padding: 6px 0px; padding-bottom: 130px;}
    .hide{display: none!important;}
    .show{display: block!important;}
    .pl_bx{width: 23%; float: left; margin: 10px; border:#fff solid 1px; border-radius: 10px; padding: 0px; text-align: center; position: relative; background-color: #fff;}
    .pl_bx .tp_row{text-align: left; padding: 10px 10px 0px;}
    .pl_bx .nv{background-color: #f7f70c; padding: 2px 10px 0px; border-radius: 10px; font-size: 10px; text-transform: uppercase; margin-right: 10px;}
    .pl_bx .spt{border: #93A3B1 solid 1px;color: #93A3B1; padding: 1px 5px 0px; border-radius: 3px; font-size: 10px; text-transform: uppercase; margin-left: 0px;}
    .pl_bx .dt{color: #999999; padding: 1px 5px 0px; font-size: 11px; text-transform: uppercase; float: right;}
    .pl_bx .view{background-color: #5853C3; color: #fff; padding: 2px 7px 1px; border-radius: 10px; font-size: 9px; text-transform: uppercase; margin-left: 0px; float: right; cursor: pointer;}
    .mt_bx{width: 100%; margin: 5px 0px;padding: 0px 10px 0px;}
    .mt_bx img{float: left; width: 25px; height: 25px; border-radius: 50%; border:#ccc solid 1px;}
    .mt_bx h4{float: left; color: #999999; font-size: 14px!important; text-transform: uppercase; margin: 5px 5px; white-space: nowrap;}
    .pl_bx h3{margin: 5px 10px 0px 10px; color: #282E48; font-size: 16px; text-align: left; height: 40px; overflow: hidden; line-height: 20px;}
    .pl_bx p{margin: 0px; font-size: 14px;}
    .pl_bx .bt_bx{width: 100%; margin-top: 10px;}
    .pl_bx .bt_bx .bt_lbx{float: left; width: 50%; background-color: #D5F7E7; border-radius: 0px 0px 0px 10px;}
    .pl_bx .bt_bx .bt_rbx{float: left; width: 50%; background-color: #FEDDDD; border-radius: 0px 0px 10px 0px;}
    .pl_bx .bt_bx h4{margin: 5px 0px; font-weight: bold; padding: 10px 0px; cursor: pointer; font-size: 17px;}
    .sepdiv{border-right: #000 solid 2px; display: initial;margin: 0px 6px;}
    .bt_bx .bt_lbx h4{color: #108B46;}
    .bt_bx .bt_rbx h4{color: #FF2828;}
    .bt_bx .bt_lbx .sepdiv{border-color: #108B46;}
    .bt_bx .bt_rbx .sepdiv{border-color: #FF2828;}
    .nrcd{margin: 10px 0px;}

    .modal-header{background-color: #5853C3; color: #fff;}
    button.close{opacity: 0.7; color: #fff;}
    .view_modal .pl_bx{width: 100%; border:#ccc solid 1px;}
    .view_modal .pl_bx .bt_bx h4{cursor: default;}

    .entry_modal .pl_bx{width: 100%;}
    .entry_modal .pl_bx h3{min-height: auto; margin: 20px 10px;}
    .entry_modal .pl_bx .bt_lbx,.entry_modal .pl_bx .bt_rbx{padding: 0px;}
    .entry_modal .pl_bx .bt_lbx h4{background-color: #F2F2F2; color: #282E48; padding: 15px 0px!important; margin: 0px; border-radius: 0px 0px 0px 10px;}
    .entry_modal .pl_bx .bt_rbx h4{background-color: #F2F2F2; color: #282E48; padding: 15px 0px!important; margin: 0px;border-radius: 0px 0px 10px 0px;}
    .opt1_sel h4{color: #108B46; background-color: #D5F7E7!important;}
    .opt2_sel h4{color: #FEDDDD; background-color: #FEDDDD!important;}
    .price_box{width: 100%; margin: 10px; border:#ccc solid 1px; border-radius: 10px; padding: 0px; text-align: center; position: relative; background-color: #fff;}
    .price_box .row_ele{width: 100%; clear: both; padding: 5px 10px;}
    .price_box label{width: 15%; float: left; margin-right: 10px; text-align: right; margin-top: 13px;}
    .price_box .sel{float: left; width: 110px; margin: 5px; border:#ccc solid 1px; padding: 7px 5px;}
    .svbtn{width: 150px; padding: 7px 15px; text-align: center; background-color: #5853C3; font-size: 16px; color: #fff; font-weight: bold;border:none; margin-top: 5px; margin-left: 10px; border-radius: 20px;}
    input[type="button"]:disabled {background: #dddddd;}

    .participants{width: 100%; clear: both; padding: 10px; background-color: #f7f7f7; border-radius: 5px;}
    .participants h3{font-size: 18px; margin: 0px;}
    .users_section{width: 100%; margin-top: 15px; max-height: 350px; overflow-y: auto;}
    .user_row{width: 100%; clear: both;border-bottom: #f7f7f7 solid 3px; padding: 8px; background-color: #fff; border-radius: 5px;}
    .user_row:last-child{border-bottom: none;}
    .user_row .lft{width: 25%; float: left; text-align: center;}
    .user_row .mdl{width: 50%; float: left; text-align: center;}
    .user_row .rgt{width: 25%; float: right; text-align: center;}
    .user_row img{width: 30px; height: 30px;border-radius: 50%; margin-bottom: 2px;}
    .user_row h5{font-size: 14px; font-weight: normal; margin: 5px 0px;white-space: nowrap;text-overflow: ellipsis;overflow: hidden;}
    .ans_bx{width: 70%; margin: 0 auto; display: inline-block; margin-top: 0px;}
    .ans_bx .ans_yes{width: 50%; min-width: 25%; max-width: 75%; padding: 2px 5px; background-color: #D5F7E7; color: #108B46; float: left; text-align: left;}
    .ans_bx .ans_no{width: 50%; min-width: 25%; max-width: 75%; padding: 2px 5px; background-color: #FEDDDD; color: #FF2828; float: left; text-align: right;}
    .stbox_pnd{background-color: #fcf8e3; border: #faebcc solid 1px;color: #8a6d3b; padding: 2px 5px 1px; border-radius: 3px; font-size: 10px; text-transform: uppercase; margin-top: 0px;}
    .stbox_mtcd{background-color: #dff0d8; border: #d6e9c6 solid 1px;color: #3c763d; padding: 2px 5px 1px; border-radius: 3px; font-size: 10px; text-transform: uppercase; margin-top: 0px;}

    .teams{width: 100%; padding: 20px 0px; margin-top: 0px;}
    .teams .pl_bx{padding: 5px;}
    .teams .pl_bx .dt{margin-top: 5px;}
    .teams .pl_bx .bt_bx{width: 100%; padding: 10px; border-top: #ccc solid 1px; min-height: 45px;}
    .teams .tm_lft{width: 50%; float: left; text-align: left; color: #999999;} 
    .teams .tm_rgt{width: 50%; float: right; text-align: right;}
    .teams .tm_lft .usr_grn{font-size: 12px; color: #108B46; font-weight: bold; background-color: #D5F7E7; border:#108B46 solid 1px; padding: 2px 10px; border-radius: 4px; margin-left: 10px;}
    .teams .tm_lft .usr_red{font-size: 12px; color: #FF2828; font-weight: bold; background-color: #FEDDDD; border:#FF2828 solid 1px; padding: 2px 10px; border-radius: 4px; margin-left: 10px;}
    .teams .st_opn{background-color: #d9edf7; border: #bce8f1 solid 1px;color: #31708f; padding: 2px 5px 1px; border-radius: 3px; font-size: 10px; text-transform: uppercase; float: right; margin-top: 3px;}
    .teams .st_cnl{background-color: #fcf8e3; border: #faebcc solid 1px;color: #8a6d3b; padding: 2px 5px 1px; border-radius: 3px; font-size: 10px; text-transform: uppercase; float: right; margin-top: 3px;}
    .teams .st_wrng{background-color: #f2dede; border: #ebccd1 solid 1px;color: #a94442; padding: 2px 5px 1px; border-radius: 3px; font-size: 10px; text-transform: uppercase; float: right; margin-top: 3px;}
    .teams .st_win{color: #43BA6A; padding: 1px 5px 0px; font-size: 12px; float: right; margin-top: 3px; font-weight: bold;}
    .note_p{margin: 10px;}
    ::-webkit-scrollbar{height: 5px;width: 5px;}
    ::-webkit-scrollbar-track {box-shadow: inset 0 0 5px grey; border-radius: 10px;}
    ::-webkit-scrollbar-thumb {background: #5853C3;border-radius: 10px;}
    @media only screen and (max-width: 600px) {
        body{background-image: none;}
        ::-webkit-scrollbar{height: 0px;width: 0px;}
        .pg_cnt{width: 100%; padding: 5px;}
        .abtn{margin-top: 0px;}
        .tp_tabs .hbtn{padding: 7px 15px;}
        .fixture{padding: 10px 5px; margin: 10px 5px 0px; width: 250px;}
        .fixture .mbx h3{font-size: 15px;}
        .fixture .mbx p{font-size: 13px;}
        .fixture .lbx img,.fixture .rbx img{width: 40px;}
        .pl_bx{width: 100%; margin:5px 0px;}
        .pl_bx:nth-child(odd){margin-right: 7px;}
        .pl_bx .bt_bx h4{padding: 5px 0px; font-size: 15px;}
        .tabcontent,.htbcontent{padding: 5px 3px 150px 3px;}
        .tp_tabs{width: 100%;}
        .price_box{margin: 10px 0px;}
        .price_box label{width: 25%;}
        .pl_bx h3{margin: 10px 10px 10px 10px}
        .teams .tm_lft{width: 70%;}
        .teams .tm_rgt{width: 30%;}
        .sp_row{width: auto;float: left; position: relative; right:0px;top:0px; margin: 0px 5px;}
        .clrbtn{margin-left: 5px; padding: 3px 10px; font-size: 12px;}
        .ans_bx{width: 90%;}
        ::-webkit-scrollbar {width: 0px;}
    }
</style>
</head>
<body>
<?php 
$bet_price_arr = array("0.5","1.0","1.5","2.0","2.5","3.0","3.5","4.0","4.5","5.0","5.5","6.0","6.5","7.0","7.5","8.0","8.5","9.0","9.5");
$bet_quantity_arr = array("1","2","3","4","5");
$tz_arr['key_value'] = "IST";
$tab_list = array("t_all"=>"QUESTIONS","myteams"=>"MY ENTRIES");
$p_tb_css = "show";
$t_tb_css = "";
$sports_list = array_column($sports,NULL,"sports_id");
$currency_type = "₹";
?>
<div class="pg_cnt">
    <h4>
        OPINION GAME
        <?php if($season_id != ""){ ?>
            <a class="clrbtn" href="<?php echo WEBSITE_URL.'trade/cron/lobby?sports_id='.$sports_id.'user_id='.$user_id.'&t='.strtotime(format_date()); ?>">Clear</a>
        <?php } ?>
    </h4>
    <div class="clear"></div>
    <div class="sp_row">
        <select id="user_id" name="user_id">
            <?php foreach($user_list as $user){
                $selected = "";
                if($user_id == $user['user_id']){
                    $selected = "selected=selected";
                }
            ?>
                <option <?php echo $selected; ?> value="<?php echo $user['user_id']; ?>"><?php echo $user['user_name']; ?></option>
            <?php } ?>
        </select>
        <select id="sports_id" name="sports_id">
            <?php foreach($sports as $sp){
                $selected = "";
                if($sports_id == $sp['sports_id']){
                    $selected = "selected=selected";
                }
            ?>
                <option <?php echo $selected; ?> value="<?php echo $sp['sports_id']; ?>"><?php echo $sp['sports_name']; ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="clear"></div>
    <div class="fixture_cnt">
        <?php 
        foreach($match_list as $row){
            $tz_arr['key_value'] = "IST";
            $date_arr = get_timezone(strtotime($row['scheduled_date']),"d M, h:i A",$tz_arr);
            $acss = "";
            if($season_id == $row['season_id']){
                $acss = "factve";
            }
        ?>
            <div class="fixture <?php echo $acss; ?>" mid="<?php echo $row['season_id']; ?>">
                <div class="lbx">
                    <img src="<?php echo FEED_IMAGE_URL ?>/upload/flag/<?php echo $row['home_flag']; ?>">
                </div>
                <div class="mbx">
                    <h3 title="<?php echo $row['home']." vs ".$row['away']; ?>"><?php echo $row['home']." vs ".$row['away']; ?></h3>
                    <p><b><?php echo $date_arr['date']; ?></b></p>
                </div>
                <div class="rbx">
                    <img src="<?php echo FEED_IMAGE_URL ?>/upload/flag/<?php echo $row['away_flag']; ?>">
                </div>
                <div class="clear"></div>
            </div>
        <?php } ?>
    </div>
    <div class="clear"></div>
    <div class="tab">
        <div class="tp_tabs">
            <?php foreach($tab_list as $tb=>$tname){
                $a_class = "";
                if($tb == $tab){
                    $a_class = "active";
                }
                if($tab == "myteams"){
                    $p_tb_css = "";
                    $t_tb_css = "show";
                }
            ?>
                <button class="hbtn <?php echo $a_class; ?>" tid="<?php echo $tb; ?>"><?php echo $tname; ?></button>
            <?php } ?>
        </div>
        <div class="clear"></div>
        <div id="myteams" class="htbcontent <?php echo $t_tb_css; ?>">
            <div class="teams">
                <?php 
                if(!empty($user_teams)){
                    foreach($user_teams as $team){
                        $match = $team['home']." vs ".$team['away'];
                        $date_arr = get_timezone(strtotime($team['scheduled_date']),"D M d, h:i A",$tz_arr);
                        $usr_opt = "<span class='usr_grn'>YES</span>";
                        if($team['answer'] == 2){
                            $usr_opt = "<span class='usr_red'>NO</span>";
                        }
                        $usr_rslt = "<span class='st_opn'>Open</span>";
                        if($team['team_status'] == 1){
                            $usr_rslt = "<span class='st_cnl'>Cancelled</span>";
                        }else if($team['team_status'] == 2){
                            $usr_rslt = "<span class='st_win'>Winning : ".$currency_type." ".$team['winning']."</span>";
                        }else if($team['team_status'] == 3){
                            $usr_rslt = "<span class='st_wrng'>Wrong</span>";
                        }
                ?>
                    <div class="pl_bx">
                        <div class="mt_bx">
                            <img src="<?php echo FEED_IMAGE_URL ?>/upload/flag/<?php echo $team['home_flag']; ?>">
                            <h4><?php echo $match; ?></h4>
                            <img src="<?php echo FEED_IMAGE_URL ?>/upload/flag/<?php echo $team['away_flag']; ?>">
                            <label class="dt"><?php echo $date_arr['date']; ?></label>
                            <div class="clear"></div>
                        </div>
                        <div class="clear"></div>
                        <h3 title="<?php echo $team['question']; ?>"><?php echo $team['question']; ?></h3>
                        <div class="bt_bx">
                            <div class="tm_lft">
                                Investment : <?php echo $currency_type." ".$team['entry_fee']." | ".$usr_opt; ?>
                            </div>
                            <div class="tm_rgt">
                                <?php echo $usr_rslt; ?>
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                <?php 
                    }
                }else {
                    echo "<p class='nrcd alert alert-warning'>There is no entries available.</p>";
                } ?>
                <div class="clear"></div>
            </div>
        </div>
        <div id="t_all" class="htbcontent <?php echo $p_tb_css; ?>">
            <?php if(!empty($question)){ ?>
                <div class="tab">
                    <div class="clear"></div>
                    <?php foreach($question as $row){
                        $time_diff = round(abs(strtotime(format_date())-strtotime($row['added_date']))/60/60);
                        $row['match'] = $row['home']." vs ".$row['away'];
                        $row['sports_name'] = $sports_list[$row['sports_id']]['sports_name'];
                        $date_arr = get_timezone(strtotime($row['scheduled_date']),"D M d, h:i A",$tz_arr);
                        $row['d_date'] = $date_arr['date'];
                    ?>
                        <div class="pl_bx" id="plbx_<?php echo $row['question_id']; ?>">
                            <div class="tp_row">
                                <?php if($time_diff <= 1){ ?>
                                    <label class="nv">New</label>
                                <?php } ?>
                                <label class="spt"><?php echo $row['sports_name']; ?></label>
                                <label class="view" onclick='view_question(<?php echo json_encode($row); ?>)'>View</label>
                                <label class="dt"><?php echo $date_arr['date']; ?></label>
                            </div>
                            <div class="mt_bx">
                                <img src="<?php echo FEED_IMAGE_URL ?>/upload/flag/<?php echo $row['home_flag']; ?>">
                                <h4><?php echo $row['match']; ?></h4>
                                <img src="<?php echo FEED_IMAGE_URL ?>/upload/flag/<?php echo $row['away_flag']; ?>">
                                <div class="clear"></div>
                            </div>
                            <div class="clear"></div>
                            <h3 title="<?php echo $row['question']; ?>"><?php echo $row['question']; ?></h3>
                            <div class="bt_bx">
                                <div class="bt_lbx" onclick='place_bet("1",<?php echo json_encode($row); ?>)'>
                                    <h4><?php echo strtoupper($row['option1'])." <div class='sepdiv'></div> ".$currency_type." ".$row['option1_val']; ?></h4>
                                </div>
                                <div class="bt_rbx" onclick='place_bet("2",<?php echo json_encode($row); ?>)'>
                                    <h4><?php echo strtoupper($row['option2'])." <div class='sepdiv'></div> ".$currency_type." ".$row['option2_val']; ?></h4>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php }else{ ?>
                <p class='nrcd alert alert-warning'>There is no questions available.</p>
            <?php } ?>
            <div class="clear"></div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade entry_modal" id="myModal" role="dialog">
        <div class="modal-dialog">
          <!-- Modal content-->
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title">Confirmation</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" name="question_id" id="question_id" value="">
                <input type="hidden" name="option_id" id="option_id" value="">
                <div class="pl_bx" id="active_question">
                    <h3 id="title"></h3>
                    <div class="bt_bx">
                        <div class="bt_lbx" id="opt1">
                            <h4></h4>
                        </div>
                        <div class="bt_rbx" id="opt2">
                            <h4></h4>
                        </div>
                    </div>
                </div>
                <div class="clear"></div>
                <div class="price_box" id="price_box">
                    <div class="row_ele">
                        <label>Price : </label>
                        <select id="bet_price" name="bet_price" class="sel">
                            <?php foreach($bet_price_arr as $prc){ ?>
                                <option><?php echo $prc; ?></option>
                            <?php } ?>
                        </select>
                        <div class="clear"></div>
                    </div>
                    <div class="row_ele">
                        <label>Quantity : </label>
                        <select id="bet_quantity" name="bet_quantity" class="sel">
                            <?php foreach($bet_quantity_arr as $qty){ ?>
                                <option><?php echo $qty; ?></option>
                            <?php } ?>
                        </select>
                        <div class="clear"></div>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="clear"></div>
                <input class="svbtn" id="submit_btn" type="button" name="save" value="SUBMIT">
                <div class="clear"></div>
            </div>
          </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade view_modal" id="viewModal" role="dialog">
        <div class="modal-dialog">
          <!-- Modal content-->
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title">Question Detail</h4>
            </div>
            <div class="modal-body">
                <div class="pl_bx" id="view_question">
                    <div class="tp_row">
                        <label class="spt" id="v_sports"></label>
                        <label class="dt" id="v_date"></label>
                    </div>
                    <div class="mt_bx">
                        <img id="v_home" src="">
                        <h4 id="v_match"></h4>
                        <img id="v_away" src="">
                        <div class="clear"></div>
                    </div>
                    <div class="clear"></div>
                    <h3 id="v_title"></h3>
                    <div class="bt_bx">
                        <div class="bt_lbx" id="v_opt1">
                            <h4></h4>
                        </div>
                        <div class="bt_rbx" id="v_opt2">
                            <h4></h4>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="participants">
                    <h3>Participants</h3>
                    <div class="users_section" id="users_section"></div>
                </div>
                <div class="clear"></div>
            </div>
          </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('#sports_id').change(function() {
        var tmp = $.now();
        var sports_id = $(this).val();
        var user_id = $("#user_id").val();
        window.location.href = "<?php echo WEBSITE_URL.'trade/cron/lobby'; ?>?sports_id="+sports_id+"&user_id="+user_id+"&t="+tmp;
    });
    $('#user_id').change(function() {
        var tmp = $.now();
        var sports_id = $("#sports_id").val();
        var user_id = $(this).val();
        window.location.href = "<?php echo WEBSITE_URL.'trade/cron/lobby'; ?>?sports_id="+sports_id+"&user_id="+user_id+"&t="+tmp;
    });

    $(".fixture").click(function(){
        var tmp = $.now();
        var sports_id = $("#sports_id").val();
        var user_id = $("#user_id").val();
        var season_id = $(this).attr("mid");

        window.location.href = "<?php echo WEBSITE_URL.'trade/cron/lobby'; ?>?sports_id="+sports_id+"&user_id="+user_id+"&season_id="+season_id+"&t="+tmp;
    })
    //var ct_ht = jQuery(window).height();
    //$('.pg_cnt').css('height',ct_ht+"px");
    $(".hbtn").click(function(){
        var pid = $(this).attr("tid");
        $(".hbtn").removeClass("active");
        $(this).addClass("active");
        $(".htbcontent").removeClass("show");
        $("#"+pid).addClass("show");
    });

    function view_question(rowObj){
        //console.log("rowObj",rowObj);
        if(rowObj.question_id > 0){
            $("#users_section").html("");
            //console.log("rowObj",rowObj);
            var home_flag = '<?php echo FEED_IMAGE_URL."/upload/flag/"; ?>'+rowObj.home_flag;
            var away_flag = '<?php echo FEED_IMAGE_URL."/upload/flag/"; ?>'+rowObj.away_flag;
            $("#v_sports").html(rowObj.sports_name);
            $("#v_date").html(rowObj.d_date);
            $("#v_match").html(rowObj.match);
            $("#v_home").attr("src",home_flag);
            $("#v_away").attr("src",away_flag);
            $("#v_title").html(rowObj.question);
            $("#v_opt1 h4").html(rowObj.option1);
            $("#v_opt2 h4").html(rowObj.option2);
            $("#viewModal").modal("show");
            $("#users_section").html('<p><img style="width:25px;" src="<?php echo IMAGE_PATH."upload/loader.gif"; ?>"/></p>');
            var data_arr = {question_id:rowObj.question_id}
            //console.log("data_arr",data_arr);
            $.ajax({
                url: "<?php echo WEBSITE_URL.'trade/cron/get_question_detail'; ?>", 
                type: "POST",
                dataType: "json",
                contentType: "application/json; charset=utf-8",
                data: JSON.stringify(data_arr),
                success: function (result) {
                    console.log("result",result);
                    if(result.status == "1"){
                        var user_img = '<?php echo IMAGE_PATH; ?>upload/profile/thumb/';
                        var user_html = '';
                        if(result.data.length > 0){
                            $.each(result.data, function(key,val) {
                                var status_txt = "Pending";
                                var st_css = "stbox_pnd";
                                if(val.status == 1){
                                    status_txt = "Matched";
                                    st_css = "stbox_mtcd";
                                }
                                var yes_wd = ((val.yes.entry_fee * 100)/10).toFixed(2);
                                var no_wd = (100-yes_wd).toFixed(2);
                                user_html+= '<div class="user_row"><div class="lft"><img src="'+user_img+val.yes.image+'"><h5>'+val.yes.user_name+'</h5></div>';
                                user_html+= '<div class="mdl"><label class="'+st_css+'">'+status_txt+'</label><div class="clear"></div><div class="ans_bx"><div class="ans_yes" style="width:'+yes_wd+'%"><?php echo $currency_type; ?> '+val.yes.entry_fee+'</div><div class="ans_no" style="width:'+no_wd+'%"><?php echo $currency_type; ?> '+val.no.entry_fee+'</div></div></div>';
                                user_html+= '<div class="rgt"><img src="'+user_img+val.no.image+'"><h5>'+val.no.user_name+'</h5></div>';
                                user_html+= '<div class="clear"></div></div>';
                            });
                        }else{
                            user_html = '<p class="nrcd alert alert-warning">There is no participants.</p>';
                        }
                        $("#users_section").html(user_html);
                    }
                },
                error: function (err) {
                    user_html = '<p class="nrcd alert alert-warning">There is no participants.</p>';
                }
            });
        }else{
            alert("Invalid data. please select valid option.");
        }
    }

    function place_bet(type,rowObj){
        $('#submit_btn').removeAttr('disabled');
        $("#opt1").removeClass("opt1_sel");
        $("#opt2").removeClass("opt2_sel");
        $("#bet_quantity").val(1);
        $("#question_id").val("");
        $("#option_id").val("");
        if(rowObj.question_id > 0){
            console.log("rowObj",rowObj);
            $("#question_id").val(rowObj.question_id);
            $("#option_id").val(type);
            $("#active_question #title").html(rowObj.question);
            $("#active_question #opt1 h4").html(rowObj.option1);
            $("#active_question #opt2 h4").html(rowObj.option2);

            if(type == 1){
                $("#bet_price").val(rowObj.option1_val);
                $("#opt1").addClass("opt1_sel");
            }else{
                $("#bet_price").val(rowObj.option2_val);
                $("#opt2").addClass("opt2_sel");
            }
            $("#myModal").modal("show");
        }else{
            alert("Invalid data. please select valid option.");
        }
    }

    $(".svbtn").click(function(){
        var sports_id = $("#sports_id").val();
        var user_id = $("#user_id").val();
        var question_id = $("#question_id").val();
        var option_id = $("#option_id").val();
        var bet_price = $("#bet_price").val();
        var bet_quantity = $("#bet_quantity").val();
        if(question_id <= 0){
            alert("Invalid question id. please select valid question.");
        }else if(option_id <= 0){
            alert("Invalid option id. please select valid option.");
        }else if(bet_price == ""){
            alert("Please select valid bet price.");
        }else if(bet_quantity == ""){
            alert("Please select valid bet quantity.");
        }else{
            $('#submit_btn').attr('disabled',true);
            var data_arr = {sports_id:sports_id,user_id:user_id,question_id:question_id,option_id:option_id,entry_fee:bet_price,quantity:bet_quantity}
            //console.log("data_arr",data_arr);
            $.ajax({
                url: "<?php echo WEBSITE_URL.'trade/cron/save_team'; ?>", 
                type: "POST",
                dataType: "json",
                contentType: "application/json; charset=utf-8",
                data: JSON.stringify(data_arr),
                success: function (result) {
                    console.log("result",result);
                    if(result.status == "1"){
                        alert(result.message);
                        var tmp = $.now();
                        window.location.href = "<?php echo WEBSITE_URL.'trade/cron/lobby?sports_id='.$sports_id.'&user_id='.$user_id.'&season_id='.$season_id; ?>&t="+tmp;
                    }else{
                        $('#submit_btn').removeAttr('disabled');
                        alert(result.message);
                    }
                },
                error: function (err) {
                    $('#submit_btn').removeAttr('disabled');
                    alert("Something went wrong while save team.");
                }
            });
        }
    });
</script>
</body>
</html>
