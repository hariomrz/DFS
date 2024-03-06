<tbody>
  <tr>
    <td colspan="2" class="content-padding" style="padding:40px 40px 20px; border-bottom:1px solid #E5E5E5;background:#FFFFFF;" ><p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;">Hi <?php echo $data['FirstLastName']; ?>,</p>
      
      	<?php if($this->DeviceTypeID!='' && $this->DeviceTypeID!=1){ /*For Mobile */?>
      		<p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;">Please verify your email address by using below code.</p>
      		<p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;"> <?php echo $data['Link']; ?> </p>
      	<?php }else{ ?>
      
      		<p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;">Please verify your email address by clicking the link below.</p>
      		<p style="font-family: 'Roboto', sans-serif; font-size:16px; color:#444444; font-weight:500; margin-bottom:30px;"> <a href="<?php echo $data['Link']; ?>" style="color:#00529F;cursor:pointer; display:block; text-decoration:none;">Click to get started</a> </p>
      	<?php }?>	