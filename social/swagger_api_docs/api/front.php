<!DOCTYPE html>
<html lang="en">
<head>
  <title>API Listing</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>

<?php

$whitelist = array(
    '127.0.0.1',
    '::1',
    'localhost'
);

$base_path = '';
if(in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
    $base_path = '/social';
} 

$api_data = include './api_data_arr/front_arr.php';
?>    
    
    
    
<div class="container">
  <h2> Front End API Listing </h2>
  <p> List of APIs with their url </p>            
  <table class="table">
    <thead>
      <tr>
        <th>API Name</th>
        <th>URL</th>
        <th>Create Command</th>
      </tr>
    </thead>
    <tbody>
        <?php foreach($api_data as $item): ?>
      <tr>
        <td><?php echo $item['api_name']; ?></td>
        <td><a href="<?php echo $item['api_url']; ?>"><?php echo $item['api_url']; ?></a></td>
        <td><?php echo $item['JsonCreateCMD']; ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

</body>
</html>
