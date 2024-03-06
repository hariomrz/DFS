<?php
$post_data = $_REQUEST;
$key_pass = md5("FwVadMiN15");
$domain = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['SERVER_NAME'];
$passkey = isset($post_data['passkey']) ? $post_data['passkey'] : "";
if($key_pass != $passkey){
	echo "<h3 style='color:#ff0000;text-align:center;font-size:48px; padding:20px'>Unauthorized Access</h3>";die;
}
$selected_module = isset($post_data['type']) ? strtolower($post_data['type']) : "other";
$file_name = isset($post_data['file']) ? $post_data['file'] : "";
$folders = array();
$folders['other'] = array("name"=>"Other","path"=>"logs");
$folders['user'] = array("name"=>"User","path"=>"user/application/logs");
$folders['fantasy'] = array("name"=>"Fantasy","path"=>"fantasy/application/logs");
$folders['admin'] = array("name"=>"Admin","path"=>"adminapi/application/logs");
$folders['cron'] = array("name"=>"Cron","path"=>"cron/application/logs");
if(!isset($folders[$selected_module])){
	echo "<h3 style='color:#ff0000;text-align:center;font-size:48px; padding:20px'>Invalid module type.</h3>";die;
}

$dir_path = $_SERVER['DOCUMENT_ROOT']."/".$folders[$selected_module]['path'];
$files_list = scandir($dir_path, SCANDIR_SORT_DESCENDING);
$files_list = array_diff($files_list, array('..', '.','index.html'));

$file_data = "";
if($file_name != ""){
	$log_file = $dir_path."/".base64_decode($file_name);
	$file_data = htmlentities(file_get_contents($log_file,true));
}
//echo "<pre>";print_r($files_list);die;
?>
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
    .pg_cnt{width:100%; margin:0px auto; border:#ccc solid 1px; padding: 20px;}
    .chdiv{width: 100%; margin: 5px;}
    .sp_row{width: 100%; padding: 20px; background:linear-gradient(86.95deg, #114872 0%, #19315D 100%);}
    .sp_row label{float: left; font-size: 19px; margin-right: 15px; margin-top: 5px; color: #fff;}
    .sp_row select{width: 150px; padding: 7px; border: #ccc solid 1px; font-size: 19px; background-color: #fff;}
    table td,table th{text-align: center;}
    table td:nth-child(1),table th:nth-child(1),table td:nth-child(2),table th:nth-child(2){text-align: left;}
    .chdiv table{margin-bottom: 0px;}
    .chdiv table td{border: #ccc solid 1px; text-align: center;}
    .chdiv table tr.pertr td{border: none; padding: 2px 8px; font-size: 12px; font-style: italic;}
    .flsel{width: 250px!important;}
</style>
</head>
<body>
<div class="sp_row">
    <label>Select Module : </label>
    <select id="type" name="type">
        <?php foreach($folders as $key=>$row){
            $selected = "";
            if($selected_module == $key){
                $selected = "selected=selected";
            }
        ?>
            <option <?php echo $selected; ?> value="<?php echo $key; ?>"><?php echo $row['name']; ?></option>
        <?php } ?>
    </select>
    <select class="flsel" id="file" name="file">
    	<option value="">Select File</option>
        <?php foreach($files_list as $fname){
        	$file_key = base64_encode($fname);
            $file_selected = "";
            if($file_name == $file_key){
                $file_selected = "selected=selected";
            }
        ?>
            <option <?php echo $file_selected; ?> value="<?php echo $file_key; ?>"><?php echo $fname; ?></option>
        <?php } ?>
    </select>
</div>
<div class="pg_cnt">
	<?php 
		if($file_name != "" && $file_data != ""){
			echo "<pre>";
			echo $file_data;
		}else if($file_name != ""){
			echo "<h3 style='color:#ff0000;text-align:left;font-size:24px; padding:20px'>There is no log data.</h3>";
		}else{
			echo "<h3 style='color:#ff0000;text-align:left;font-size:24px; padding:20px'>Please select log file.</h3>";
		}
	?>
</div>
<script type="text/javascript">
    $('#type').change(function() {
        var type = $(this).val();
        var file = "";
        window.location.href = "<?php echo $domain.'/logs.php'; ?>?type="+type+"&file="+file+"&passkey=<?php echo $passkey ?>";
    });
    $('#file').change(function() {
        var type = $('#type').val();
        var file = $('#file').val();
        window.location.href = "<?php echo $domain.'/logs.php'; ?>?type="+type+"&file="+file+"&passkey=<?php echo $passkey ?>";
    });
</script>
</body>
</html>
