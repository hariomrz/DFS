<!DOCTYPE html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo SITE_TITLE;?></title>
<script>
  var base_url='<?php echo base_url(); ?>'; 
</script>
<style type="text/css">
body {
	margin: 0;
	padding: 0;
	background-color:#E9E9E9;
	font-size:14px;
	color:#5F5F63;
	font-family:Verdana, Geneva, sans-serif;
}
table, tr, td, th {
	margin: 0;
	padding: 0;
}
img {
	border: none;
}
a {
	text-decoration: none;
	color:#000 !important;
}
table[class="outer-tbl"] {
	max-width:600px !important;
	margin:45px auto !important;
	
}
p {
	margin-top:20px;
	padding:0;
}
 @media only screen and (max-width: 640px) {
table[class="margtable"] {margin:0 20px !important;}
table[class="bottompadding10"] { padding-top:10px;}  
table[class="outer-tbl"] {width:auto !important; margin-top:0 !important; margin-bottom:0 !important;}
td[class="logo"] {padding:40px 0 !important;}
table[class="bottom-row"] {padding-bottom:10px !important;}
td[class="text"] {padding:10px 0 20px 0 !important;}
td[class="footer"] {padding:15px 0 !important;}
td[class="toppadding10"] {padding-top:10px !important;}
td[class="bottompadding10"] {padding-bottom:10px !important;}
p {margin-top:10px !important;}
}
</style>
</head>
<body>
<table border="0" cellspacing="0" cellpadding="0" align="center" width="620" class="outer-tbl">
  <tr>
    <td>
        <table cellpadding="0" cellspacing="0"  style="width:100%; border:1px solid #ccc; background:#fff; border-radius:5px; -webkit-border-radius:5px; -moz-border-radius:5px;">
    		<tr>
            	<td>
                	<table border="0" width="100%" style="text-align:center; background:#F4F4F4; border-top-left-radius:5px; border-top-right-radius:5px; ">
                	<tr>
                    	<td class="logo" style="padding:65px 0; background-color:#000;">
                        	<?php echo $this->load->view('emailer/header','',TRUE); ?>
                        </td>
                   </tr>
                </table>
                </td>
            </tr>
            <tr>
              <td>
                  <table border="0" class="margtable" style="margin-left:50px; margin-right:50px;">                  
                      <tr>
                        <td style="font-family:Verdana, Geneva, sans-serif; font-size:14px; padding-top:60px;" class="toppadding10"><?php echo $this->lang->line('dear');?> <span style="color:#9E2822; font-weight:bold;"><?php echo $username;?></span>,</td>
                      </tr>
                      <tr>
                        <td style="padding-top:10px;font-family:Verdana, Geneva, sans-serif; font-size:14px">&nbsp;</td>
                    </tr>
                      <tr>
                        <td style="padding-top:10px;">
                          <p style="font-family:Verdana, Geneva, sans-serif; font-size:14px"><?php echo $this->lang->line('email_paragraph1');?></p>
                       
                          <p style="font-family:Verdana, Geneva, sans-serif; font-size:14px"><?php echo sprintf($this->lang->line('email_paragraph3'), '<a href="'.$link.'">'.$link.'</a>');?></p>
                          <p style="font-family:Verdana, Geneva, sans-serif; font-size:14px"><?php echo $this->lang->line('email_paragraph5');?></p>
                        </td>
                      </tr>
                      <tr>
                        <td style="padding:10px 0 35px 0; font-family:Verdana, Geneva, sans-serif; font-size:14px" class="text"></td>
                      </tr>
                      <tr>
                        <td style="font-family:Verdana, Geneva, sans-serif; font-size:14px"><?php echo $this->lang->line('cheers');?>,</td>
                      </tr>
                      <tr>
                        <td style="color:#AC1C14; font-family:Verdana, Geneva, sans-serif; font-size:14px; padding-bottom:75px" class="bottompadding10"><?php echo $this->lang->line('team');?></td>
                      </tr>
                  </table>
                </td>
            </tr>
        </table>
    </td>
  </tr>  
  <tr>
      <td width="100%" align="left" style="padding:15px 0 10px 0; font-size:13px; margin:0;">
      	<?php echo $this->lang->line('footer_text');?>
     </td>
  </tr>
  <tr>
    <td width="100%" align="center" style="padding:0 0 15px 0;">
    	<?php echo $this->load->view('emailer/footer','',TRUE); ?>
    </td>
  </tr>
</table>
</body>
</html>