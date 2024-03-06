<!DOCTYPE html>
<html>
<head>
    <title>Match Props</title>
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css'>
<style type="text/css">
    body{background-image: url("https://predev-vinfotech-org.s3.ap-south-1.amazonaws.com/upload/front_bg.png"); background-size: 100%;}
    a,a:hover{text-decoration: none;color: #000;}
    .clear{clear: both;}
    .pg_cnt{width:100%; min-height: 1000px; padding: 20px; background-color: #f2f2f2; overflow: hidden;}
    .sp_row{width: 700px; margin: 20px;}
    .sp_row label{float: left; font-size: 19px; margin-right: 15px; margin-top: 5px;}
    .sp_row select{width: 150px; padding: 7px; border: #ccc solid 1px; font-size: 19px; background-color: #fff;}
    .fixture_cnt{width: 30%; height: 700px; overflow-y: auto;}
    .fixture{width: 25%; padding: 10px; border-radius: 10px; background-color: #fff; margin: 15px 10px; cursor: pointer;}
    .fixture .lbx{float: left; width: 20%; text-align: center;}
    .fixture .mbx{float: left; width: 60%; text-align: center;}
    .fixture .rbx{float: right; width: 20%; text-align: center;}
    .fixture .lbx img,.fixture .rbx img{width: 70px; margin-top: 10px;}
    .fixture .mbx h3{margin: 10px 0px 0px; font-weight: bold;}
    .fixture .mbx p{margin: 3px 0px;}
    .tab{overflow: hidden;background-color: #f1f1f1;}
    .tp_tabs{width: 20%; float: left;background-color: #fff; border-radius: 20px;}
    .tp_tabs .hbtn{width: 50%!important; margin-bottom: 0px; float: left; font-weight: bold; padding: 10px 15px; background-color: #fff; border-radius: 20px;border: none;outline: none;cursor: pointer;}
    .tp_tabs .hbtn.active{background-color: #5853C3; color: #fff;}
    .tab_props{width: 100%; background-color: #EBEBEB; padding: 0px; border-radius: 5px; margin-top: 15px;}
    .tab_props button{float: left;border: none;outline: none;cursor: pointer;padding: 10px 5px 8px;transition: 0.3s; background-color: transparent; font-weight: bold; margin: 0px 15px;border-bottom: #EBEBEB solid 2px;}
    .tab_props button.active{color: #5853C3; border-bottom: #5853C3 solid 2px;}
    .tabcontent,.htbcontent{width: 100%; display: none;padding: 6px 0px; padding-bottom: 130px;}
    .hide{display: none!important;}
    .show{display: block!important;}
    .pl_bx{width: 13%; float: left; margin: 10px; border:#ccc solid 1px; border-radius: 10px; padding: 10px; text-align: center; position: relative; background-color: #fff;}
    .pl_bx .ckbx{position: absolute;left: 10px; top: 5px; width: 25px; height: 25px;}
    .pl_bx .psel{width: 60px; border:#ccc solid 1px; padding: 3px 2px; position: absolute; right: 10px; top: 8px;}
    .pl_bx img{width: 120px; height: 120px; margin-top: 15px;}
    .pl_bx h3{margin: 5px 0px 0px; font-size: 16px; font-weight: bold;white-space: nowrap;text-overflow: ellipsis;overflow: hidden;}
    .pl_bx p{margin: 0px; font-size: 14px;}
    .pl_bx .bt_bx{width: 100%; margin-top: 10px;}
    .pl_bx .bt_bx .plbx{float: left; width: 50%; text-align: center; border-right: #ccc solid 1px; padding: 10px 0px;}
    .pl_bx .bt_bx .prbx{float: right; width: 50%; text-align: center; padding: 10px 0px;}
    .plbx h4{margin: 0px; font-weight: bold; font-size: 18px;}
    .prbx p{margin: 0px;}
    .fnl_box{width: 98%; position: fixed; bottom: 0px; background-color: #fff;}
    .typebox{width: 20%; float: left; padding: 10px; font-size: 16px;}
    .lrdo{margin-right: 7px!important;}
    .fnl_box .svbtn{width: 15%; padding: 15px; text-align: center; background-color: #5853C3; font-size: 18px; color: #fff; font-weight: bold;border:none; margin-top: 17px;}
    .entrybx{width: 20%; float: left; padding: 10px; font-size: 16px; border-left: #ebebeb solid 1px;}
    .entrybx .inpt{float: left; width: 110px; margin: 5px; border:#ccc solid 1px; padding: 5px;}
    .entrybx .sel{float: left; width: 110px; margin: 5px; border:#ccc solid 1px; padding: 7px; 5px;}
    .abtn{background-color: #ccc; border-radius: 5px; padding: 2px 10px; float: right; margin-right: 10px; margin-top: -17px;}
    .nrcd{margin: 10px 0px;}
    .teams{width: 100%; padding: 20px 0px; margin-top: 0px;}
    .team_ele{width: 24%; background-color: #fff; border-radius: 7px; padding: 10px; margin: 10px 8px; float: left;}
    .team_ele .top,.team_ele .mid,.team_ele .bot{width: 100%;}
    .team_ele .top h5{float: left; margin: 2px 0px; font-size: 16px; font-weight: bold; color: #5853C3;}
    .team_ele .top p{float: left; margin: 0px 0px 0px 10px; font-size: 14px; color: #333333}
    .team_ele .top label{border:#5853C3 solid 1px; color: #5853C3; text-transform: uppercase; padding: 3px 3px 1px; font-size: 10px; border-radius: 3px; float: right;margin-right: 10px;}
    .team_ele .bot p{font-size: 12px; color: #878787; margin: 0px;white-space: nowrap;text-overflow: ellipsis;overflow: hidden;}
    .team_ele .mid{margin: 15px 0px}
    .team_ele .mid .ld{float: left; width: 35%; text-align: left;}
    .team_ele .mid .md{float: left;width: 50%; text-align: center;}
    .team_ele .mid .rd{float: right; width: 15%; text-align: center;}
    .team_ele .mid h5{margin: 0px 0px 0px;font-weight: bold; color: #000; font-size: 20px;}
    .team_ele .mid i{font-size: 17px; margin-right: 5px;}
    .team_ele .mid p{margin: 0px 0px 10px 0px; color: #333; font-size: 13px;}
    .note_p{margin: 10px;}
    .gnft{color:green;}
    .rdft{color:#ff0000;}
    .pid{float: right;background-color: #ccc;padding: 2px 5px;font-size: 13px;border-radius: 0px 10px; position: absolute;right: 0px; top: 0px;}
    .pgn{background-color: #dff0d8;}
    .prd{background-color: #f2dede;}
    @media only screen and (max-width: 600px) {
        body{background-image: none;}
        .pg_cnt{width: 100%; padding: 5px;}
        .abtn{margin-top: 0px;}
        .fixture{margin-top: 30px; width: auto;}
        .fixture .mbx h3{font-size: 17px;}
        .fixture .mbx p{font-size: 13px;}
        .fixture .lbx img,.fixture .rbx img{width: 50px;}
        .pl_bx{width: 48%; margin:10px 0px; padding: 10px 5px;}
        .pl_bx:nth-child(odd){margin-right: 7px;}
        .pl_bx img{margin-top: 30px;}
        .pl_bx h3{font-size: 15px;}
        .pl_bx .psel{width: 65px;}
        .tabcontent,.htbcontent{padding: 5px 3px 150px 3px;}
        .fnl_box{width: 100%; max-width: 559px;}
        .typebox{width: 50%; font-size: 12px;}
        .entrybx{width: 50%; float: right; font-size: 12px;}
        .entrybx .sel{width: 80px; padding: 6px 5px;}
        .entrybx .inpt{width: 70px;}
        .fnl_box .svbtn{width: 100%; padding: 10px 20px; margin-top: 0px;}
        .team_ele{width: 100%; margin: 10px 0px;}
        .tp_tabs{width: 100%;}
        ::-webkit-scrollbar {width: 0px;}
    }
</style>
</head>
<body>
<?php 
$prop_id = isset($props['0']['prop_id']) ? $props['0']['prop_id'] : 0;
$tz_arr['key_value'] = "IST";
$date_arr = get_timezone(strtotime($match_info['scheduled_date']),"d M, h:i A",$tz_arr);
$players = array();
foreach($player_list as $pl){
    $players[$pl['prop_id']][] = $pl;
}
$payout_type_arr = array("1"=>"FlexPlay","2"=>"PowerPlay");
$tab_list = array("t_props"=>"ALL PLAYERS","myteams"=>"MY ENTRIES");
$p_tb_css = "show";
$t_tb_css = "";
?>
<div class="pg_cnt">
    <a class="abtn" href="<?php echo WEBSITE_URL.'props/cron/match_list?sports_id='.$sports_id.'&status=1'; ?>">Back to Home</a>
    <div class="fixture">
        <div class="lbx">
            <img src="<?php echo FEED_IMAGE_URL ?>/upload/flag/<?php echo $match_info['home_flag']; ?>">
        </div>
        <div class="mbx">
            <h3><?php echo $match_info['home']." vs ".$match_info['away']; ?></h3>
            <p><b><?php echo $date_arr['date']; ?></b></p>
            <p><?php echo $match_info['league_name']; ?></p>
        </div>
        <div class="rbx">
            <img src="<?php echo FEED_IMAGE_URL ?>/upload/flag/<?php echo $match_info['away_flag']; ?>">
        </div>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>
    <div class="tab">
        <div id="t_props" class="htbcontent show">
            <?php if(!empty($props) && !empty($player_list)){ ?>
                <div class="tab">
                    <div class="tab_props">
                    <?php foreach($props as $prop){
                        $tb_css = "";
                        if($prop['prop_id'] == $prop_id){
                            $tb_css = "active";
                        }
                    ?>
                        <button class="tablinks <?php echo $tb_css; ?>" tid="p<?php echo $prop['prop_id']; ?>"><?php echo $prop['short_name']; ?></button>
                    <?php } ?>
                    <div class="clear"></div>
                    </div>
                    <p class="note_p">Pick 2-6 players. Choose whether you think they will get MORE or LESS than their projection.</p>
                    <div class="clear"></div>
                    <?php foreach($props as $prop){
                        $p_field = $prop['fields_name'];
                        $tb_css = "";
                        if($prop['prop_id'] == $prop_id){
                            $tb_css = "show";
                        }
                        $pl_list = isset($players[$prop['prop_id']]) ? $players[$prop['prop_id']] : array();
                    ?>
                        <div id="p<?php echo $prop['prop_id']; ?>" class="tabcontent <?php echo $tb_css; ?>">
                            <?php 
                            if(!empty($pl_list)){
                            foreach($pl_list as $player){
                                $jersey = $player['jersey'];
                                $points = $player['points'] - 0.5;
                                $m_field_val = isset($player[$p_field]) ? $player[$p_field] : "DNP";
                                if($m_field_val == "DNP"){
                                    $ptc_css = "rdft";
                                    $pl_bx_css = "";
                                }else{
                                    if(in_array($p_field,["batting_fours","batting_sixes","bowling_wickets"])){
                                        $pts_dev = 2;
                                    }else if($p_field == "batting_runs"){
                                        $pts_dev = 10;
                                    }else if(in_array($p_field,["goals","assists","shots_on_goal","saves","tackles_won","clearance"])){
                                        $pts_dev = 2;
                                    }else if($p_field == "passes_completed"){
                                        $pts_dev = 10;
                                    }else{
                                        $pts_dev = ($points * 30 / 100);
                                    }
                                    $ptc_css = "gnft";
                                    $pl_bx_css = "pgn";
                                    if($m_field_val < ($points - $pts_dev) || $m_field_val > ($points + $pts_dev)){
                                        $ptc_css = "rdft";
                                        $pl_bx_css = "prd";
                                    }
                                }

                            ?>
                                <div class="pl_bx <?php echo $pl_bx_css; ?>">
                                    <label class="pid"><?php echo "P-".$player['player_id']; ?></label>
                                    <img src="<?php echo FEED_IMAGE_URL ?>/upload/jersey/<?php echo $jersey; ?>">
                                    <h3 title="<?php echo $player['display_name']; ?>"><?php echo $player['display_name']; ?></h3>
                                    <p><?php echo $player['team']." - ".$player['position']; ?></p>
                                    <div class="bt_bx">
                                        <div class="plbx">
                                            <h4><?php echo $points." - <label class='".$ptc_css."'>".$m_field_val."</label>"; ?></h4>
                                        </div>
                                        <div class="prbx">
                                            <p><?php echo ucfirst($prop['name']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php }
                            }else{
                                echo "<p class='nrcd alert alert-warning'>There is no props available.</p>";
                            } ?>
                            <div class="clear"></div>
                        </div>
                    <?php } ?>
                </div>
            <?php }else{ ?>
                <p class='nrcd alert alert-warning'>There is no props available.</p>
            <?php } ?>
            <div class="clear"></div>
        </div>
    </div>
</div>
<script type="text/javascript">
    //var ct_ht = jQuery(window).height();
    //$('.pg_cnt').css('height',ct_ht+"px");
    $(".hbtn").click(function(){
        var pid = $(this).attr("tid");
        $(".hbtn").removeClass("active");
        $(this).addClass("active");
        $(".htbcontent").removeClass("show");
        $("#"+pid).addClass("show");
    });
    $(".tablinks").click(function(){
        var pid = $(this).attr("tid");
        $(".tablinks").removeClass("active");
        $(this).addClass("active");
        $(".tabcontent").removeClass("show");
        $("#"+pid).addClass("show");
    });
    $(".svbtn").click(function(){
        var pl_ids = $("input[name='props']:checked").map(function () {
            return this.value;
        }).get();
        var payout_type = $("input[name='payout_type']:checked").val();
        var currency_type = $("#currency_type").val();
        var entry_fee = $("#entry_fee").val();
        if(pl_ids.length < 2){
            alert("Please select atleast 2 props");
        }else if(pl_ids.length > 6){
            alert("You can select max 6 props");
        }else if(entry_fee == "" || entry_fee < 0){
            alert("Entry amount can't be empty.");
        }else{
            var pl_arr = [];
            $.each(pl_ids, function(key,pid) {
                var pl_type = $("#type_"+pid).val();
                pl_arr.push({"pid":pid,"type":pl_type});
            });
            //console.log("pl_arr",pl_arr);
            $.ajax({
                url: "<?php echo WEBSITE_URL.'props/cron/save_team'; ?>", 
                type: "POST",
                dataType: "json",
                contentType: "application/json; charset=utf-8",
                data: JSON.stringify({"payout_type":payout_type,"currency_type":currency_type,"entry_fee":entry_fee,"pl":pl_arr}),
                success: function (result) {
                    console.log("result",result);
                    if(result.status == "1"){
                        alert(result.message);
                        var tmp = $.now();
                        window.location.href = "<?php echo WEBSITE_URL.'props/cron/match_props?season_id='.$season_id; ?>&tb=myteams&t="+tmp;
                    }else{
                        alert(result.message);
                    }
                },
                error: function (err) {
                    alert("Something went wrong while save team.");
                }
            });
        }
    });
</script>
</body>
</html>
