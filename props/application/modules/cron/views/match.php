<!DOCTYPE html>
<html>
<head>
    <title>Match List</title>
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
    .pg_cnt{width:600px; padding: 20px; background-color: #f2f2f2;}
    .sp_row{width: 100%; margin: 20px;}
    .sp_row label{float: left; font-size: 19px; margin-right: 15px; margin-top: 5px;}
    .sp_row select{width: 150px; padding: 7px; border: #ccc solid 1px; font-size: 19px; background-color: #fff;}
    .fixture_cnt{width: 100%; height: 700px; overflow-y: auto;}
    .fixture{width: auto; padding: 10px; border-radius: 10px; background-color: #fff; margin: 15px 10px; cursor: pointer;}
    .fixture .lbx{float: left; width: 20%; text-align: center;}
    .fixture .mbx{float: left; width: 60%; text-align: center;}
    .fixture .rbx{float: right; width: 20%; text-align: center;}
    .fixture .lbx img,.fixture .rbx img{width: 70px; margin-top: 10px;}
    .fixture .mbx h3{margin: 10px 0px 0px; font-weight: bold;}
    .fixture .mbx p{margin: 3px 0px;}
    .dis_fx{opacity: 0.5}
    .abtn{background-color: #ccc; border-radius: 5px; padding: 2px 10px; float: right; margin-right: 10px; margin-top: -17px;}
    ::-webkit-scrollbar {width: 0px;}
    @media only screen and (max-width: 600px) {
        body{background-image: none;}
        .sp_row{width: 100%;}
        .pg_cnt{width: 100%; padding: 5px;}
        .fixture .mbx h3{font-size: 17px;}
        .fixture .mbx p{font-size: 13px;}
        .fixture .lbx img,.fixture .rbx img{width: 50px;}
    }
</style>
</head>
<body>
<div class="pg_cnt">
    <div class="sp_row">
        <label>Select Sports : </label>
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
        <?php if($status == "1"){ ?>
            <a class="abtn" href="<?php echo WEBSITE_URL.'props/cron/match_list?sports_id='.$sports_id.'&status=0'; ?>">View Upcoming</a>
        <?php }else{ ?>
            <a class="abtn" href="<?php echo WEBSITE_URL.'props/cron/match_list?sports_id='.$sports_id.'&status=1'; ?>">View Completed</a>
        <?php } ?>
    </div>
    <div class="clear"></div>
    <div class="fixture_cnt">
        <?php 
        foreach($data as $row){
            $fx_css = "";
            if($status == "1"){
                $url = WEBSITE_URL."props/cron/complete_props?season_id=".$row['season_id'];
            }else{
                $url = WEBSITE_URL."props/cron/match_props?season_id=".$row['season_id'];
            }
            if($row['is_published'] == 0){
                //$fx_css = "dis_fx";
            }
            $tz_arr['key_value'] = "IST";
            $date_arr = get_timezone(strtotime($row['scheduled_date']),"d M, h:i A",$tz_arr);
        ?>
            <div class="fixture <?php echo $fx_css; ?>" url="<?php echo $url; ?>">
                <div class="lbx">
                    <img src="<?php echo FEED_IMAGE_URL ?>/upload/flag/<?php echo $row['home_flag']; ?>">
                </div>
                <div class="mbx">
                    <h3><?php echo $row['home']." vs ".$row['away']; ?></h3>
                    <p><b><?php echo $date_arr['date']; ?></b></p>
                    <p><?php echo $row['league_name']; ?></p>
                </div>
                <div class="rbx">
                    <img src="<?php echo FEED_IMAGE_URL ?>/upload/flag/<?php echo $row['away_flag']; ?>">
                </div>
                <div class="clear"></div>
            </div>
        <?php } ?>
    </div>
</div>
<script type="text/javascript">
    var ct_ht = jQuery(window).height();
    ct_ht = ct_ht-120;
    $('.fixture_cnt').css('height',ct_ht+"px");
    $('#sports_id').change(function() {
        var sports_id = $(this).val();
        var tmp = $.now();
        window.location.href = "<?php echo WEBSITE_URL.'props/cron/match_list'; ?>?sports_id="+sports_id;
    });
    $(".fixture").click(function(){
        var url = $(this).attr("url");
        var tmp = $.now();
        window.location.href = url+"&t="+tmp;
    })
</script>
</body>
</html>
