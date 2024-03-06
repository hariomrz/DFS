<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css'>
<style type="text/css">
    .rdft{color:#ff0000;}
    .pg_h3{margin-left:20px;}
    .pg_cnt{width:900px; margin:20px;}
    .chdiv{width: 100%; margin: 5px;}
    .pgfrm{border: #ccc solid 1px;}
    .sp_row{width: 700px; margin: 20px;}
    .sp_row label{float: left; font-size: 19px; margin-right: 15px; margin-top: 5px;}
    .sp_row select{width: 150px; padding: 7px; border: #ccc solid 1px; font-size: 19px; background-color: #fff;}
    table td,table th{text-align: center;}
    table td:nth-child(1),table th:nth-child(1),table td:nth-child(2),table th:nth-child(2){text-align: left;}
    .chdiv table{margin-bottom: 0px;}
    .chdiv table td{border: #ccc solid 1px; text-align: center;}
    .chdiv table tr.pertr td{border: none; padding: 2px 8px; font-size: 12px; font-style: italic;}
    .rowElem { clear: both; border-top: 1px solid #e7e7e7; padding: 10px 16px; position: relative; }

    .formRight { float: right; width: 76%; margin: 5px 0; display: block; position: relative; }
    .formRight label, .loginRow label { cursor: pointer; }
    .formBottom { /*width: 688px;*/ margin: 12px 12px 12px 0; }
    .rowElem > label { padding: 5px 0; width: 14%; }
    .rowElem .topLabel { padding: 5px 12px 12px 0; width: 100%; }
    .frm_ch{margin: 10px 0px 10px 20px;}
    input[type=text],select{border: #ccc solid 1px; padding: 5px 10px; border-radius: 5px;}
    .frm_ch input[type=text],.frm_ch select{width: 40%; margin: 5px 10px 0px 0px; font-size: 15px; clear: both; background-color: #fff;}
    input[type="radio"] {background-color: initial;cursor: default;appearance: auto;box-sizing: border-box;margin: 3px 3px 0px 5px;padding: initial;border: initial;}
    .frm_ch input[readOnly]{background: #dddddd;}
    .prdno{margin-left: 15px!important;}
    .chlbl{width: 100px; font-size: 15px;}
    .lstdv{margin-top: 150px;}
    .cfgdv{width: 100%; clear: both;}
    .chhr{clear: both; height: 5px;}
    .btn,.btn:hover{margin: 10px 0px 10px 20px; background-color: #5853c3; color: #fff; font-size: 16px; padding: 5px 20px; border-radius: 20px;}
    .s_st{color: #155724; background-color: #d4edda; border: #c3e6cb solid 1px; padding: 7px 10px;}
    .s_ft{color: #721c24; background-color: #f8d7da; border: #f5c6cb solid 1px; padding: 7px 10px;}
</style>
</head>
<body>
<div class="pg_cnt">
    <?php if(isset($status) && $status == "1"){
        echo '<p class="s_st">Configuration setting updated successfully.</p>';
    }else if(isset($status) && $status == "0"){
        echo '<p class="s_ft">Please update setting properly.</p>';
    } ?>
    <form class="pgfrm" name="sports_config" id="sports_config" method="post" action="<?php echo $action; ?>">
        <input type="hidden" name="key" name="key" value="<?php echo $key; ?>">
        <table class='table table-striped'>
            <tbody>
                    <tr>
                        <td>Sports:</td>
                        <td class="pform">
                            <select id="sports_id" name="sports_id">
                                <?php foreach($sports_list as $row){
                                    $selected = "";
                                    if($sports_id == $row['sports_id']){
                                        $selected = "selected=selected";
                                    }
                                 ?>
                                    <option <?php echo $selected; ?> value="<?php echo $row['sports_id']; ?>"><?php echo $row['sports_name']; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Team Player Limit:</td>
                        <td class="pform">
                            <input type="number" name="max_player_per_team" id="max_player_per_team" value="<?php echo $max_player_per_team; ?>" required="1">
                        </td>
                    </tr>
                    <?php foreach($position_list as $pos){ ?>
                        <tr>
                            <td><?php echo $pos['position']; ?>:</td>
                            <td class="pform">
                                <input type="number" name="position_min[<?php echo $pos['mpl_id']; ?>]" id="min_player_<?php echo $pos['mpl_id']; ?>" value="<?php echo $pos['min_player']; ?>" required="1">
                                <input type="number" name="position_max[<?php echo $pos['mpl_id']; ?>]" id="max_player_<?php echo $pos['mpl_id']; ?>" value="<?php echo $pos['max_player']; ?>" required="1">
                            </td>
                        </tr>
                    <?php } ?>
            </tbody>
        </table>
        <input class="btn" type="submit" name="submit" value="Save">
    </form>
</div>
</body>
</html>
<script type="text/javascript">
    $('#sports_id').change(function() {
        var sports_id = $(this).val();
        var year = $("#year").val();
        window.location.href = "<?php echo WEBSITE_URL.'adminapi/sports_config'; ?>?key=<?php echo $key; ?>&Sessionkey=<?php echo $Sessionkey; ?>&sports_id="+sports_id;
    });
</script>
