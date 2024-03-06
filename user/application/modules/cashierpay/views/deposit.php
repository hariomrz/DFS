<?php
$attr = [
    'name'=>'cashierpayform',
    'id'=>'cashierpayform',
    'method'=>'post'
    ];
echo form_open($url,$attr);
foreach($request as $name=>$value){
    echo form_hidden($name,$value);
    }
    //echo form_submit("submit","submit");
echo form_close();
?>

