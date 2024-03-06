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
    .pg_cnt{width:1000px; margin:20px; border:#ccc solid 1px;}
    .chdiv{width: 100%; margin: 5px;}
    .sp_row{width: 700px; margin: 20px;}
    .sp_row label{float: left; font-size: 19px; margin-right: 15px; margin-top: 5px;}
    .sp_row select{width: 150px; padding: 7px; border: #ccc solid 1px; font-size: 19px; background-color: #fff;}
    table td,table th{text-align: center;}
    table td:nth-child(1),table th:nth-child(1),table td:nth-child(2),table th:nth-child(2){text-align: left;}
    .chdiv table{margin-bottom: 0px;}
    .chdiv table td{border: #ccc solid 1px; text-align: center;}
    .chdiv table tr.pertr td{border: none; padding: 2px 8px; font-size: 12px; font-style: italic;}
</style>
</head>
<body>
<div class="sp_row">
    <label>Select Filter : </label>
    <select id="sports_id" name="sports_id">
        <?php foreach($sports_list as $sp){
            $selected = "";
            if($sports_id == $sp['sports_id']){
                $selected = "selected=selected";
            }
        ?>
            <option <?php echo $selected; ?> value="<?php echo $sp['sports_id']; ?>"><?php echo $sp['sports_name']; ?></option>
        <?php } ?>
    </select>
    <select id="status_id" name="status_id">
        <?php foreach($status_list as $st_key=>$st){
            $selected = "";
            if($status_id == $st_key){
                $selected = "selected=selected";
            }
        ?>
            <option <?php echo $selected; ?> value="<?php echo $st_key; ?>"><?php echo $st; ?></option>
        <?php } ?>
    </select>
</div>
<div class="pg_cnt">
    <table class='table table-striped'>
        <thead>
            <tr>
                <th>CollectionID</th>
                <th>Collection</th>
                <th>ScheduleDate(UTC)</th>
                <th>ScheduleDate(IST)</th>
                <th>Status</th>
                <th>LineupProcess</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            foreach($match_list as $row){
                $scheduled_date = convert_to_client_timezone($row['season_scheduled_date'],"d M Y, h:i A");
            ?>
                <tr>
                    <td><?php echo $row['collection_master_id']; ?></td>
                    <td><?php echo $row['collection_name']; ?></td>
                    <td><?php echo $row['season_scheduled_date']; ?></td>
                    <td><?php echo $scheduled_date; ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td><?php echo $row['is_lineup_processed']; ?></td>
                    <td>
                        <a target='_blank' href="<?php echo WEBSITE_URL.'cron/stats/match_detail/'.$row['collection_master_id']; ?>">View</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
<script type="text/javascript">
    $('#sports_id,#status_id').change(function() {
        var sports_id = $("#sports_id").val();
        var status_id = $("#status_id").val();
        window.location.href = "<?php echo WEBSITE_URL.'cron/stats/match_list'; ?>?sports_id="+sports_id+"&status_id="+status_id;
    });
</script>
</body>
</html>
