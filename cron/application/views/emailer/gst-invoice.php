<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<!--Start middle section-->
<tr>
    <td colspan="3" class="info-td">
        <h4>
            Hey <?php echo $user_name; ?>,
        </h4>

        
        <table class="info-td" width="100%">
        	<tr>
        		<td>
        			 <table>
                        <tr>
                            <td>
                                <b><?php echo $data['company_name']; ?></b><br>
                                PAN :- <?php echo $this->app_config['allow_gst']['custom_data']['pan']; ?><br>
                                CIN :- <?php echo $this->app_config['allow_gst']['custom_data']['cin']; ?><br>
                                GSTIN :- <?php echo $this->app_config['allow_gst']['custom_data']['gstin']; ?><br>
                                TAN :- <?php echo $this->app_config['allow_gst']['custom_data']['tan']; ?><br>
                                <?php echo $data['company_address']; ?><br>
                                HSN Code :- <?php echo $this->app_config['allow_gst']['custom_data']['hsn_code']; ?><br>
                                <?php echo $data['company_contact']; ?><br>
                                Description of Service :- Supply of online content service
                            </td>
                            <td style="text-align:center">
                            <a href="<?php echo WEBSITE_URL; ?>"><img src="<?php echo WEBSITE_URL; ?>cron/assets/img/logo.png"  width="50"></a>
                                 <!-- <img src="/var/www/html/cron/assets/img/logo.png" width="50"> -->
                            </td>

                            <td style="text-align:right">
                                <b>Invoice Date :_ <?php echo @$content['date']; ?></b><br>
                                <!-- Invoice #: <?php echo $content['invoice_no']; ?><br> -->
                                <b>Billed to / Customer Details -</b> <br>
                                <?php echo $content['email']; ?><br>
                                Address :- <?php 
                                if($content['address']){
                                    echo $content['address']; 
                                }
                                else{
                                    echo " No Address exists on records."; 
                                }
                                ?><br>
                                <?php echo $content['city']; ?><br>
                                <?php echo $content['zip_code']; ?><br>
                                PLACE OF SUPPLY - <?php echo $content['state']; ?><br>
                            </td>
                            
                           
                        </tr>
                    </table>	

        		</td>
        		
        		
        	</tr>
        </table>

        <center><?php echo $content['collection_name'];?></center>

        <center><h3>Tax Invoice</h3></center> <br>

        <table width="100%" border="1">
        	<tr style="background-color:#2BBFEB;">
        		<th style="background-color:#2BBFEB;">Contest Name</th>
                <th style="background-color:#2BBFEB;">Entry Amount (INR)</th>
        		<th style="background-color:#2BBFEB;">Taxable Value (Platform Fee) (INR)*</th>
        		<th style="background-color:#2BBFEB;">SGST (INR)</th>
        		<th style="background-color:#2BBFEB;">CGST (INR)</th>
        		<th style="background-color:#2BBFEB;">IGST (INR)</th>
        		<th style="background-color:#2BBFEB;">Total</th>
        	</tr>

        	<tr>
        		<th><?php echo $content['contest_name'];?></th>
                <th><?php echo $content['entry_fee'];?></th>
        		<th><?php echo $content['taxable_value'];?></th>
        		<th><?php echo $content['sgst'];?></th>
        		<th><?php echo $content['cgst'];?></th>
        		<th><?php echo $content['igst'];?></th>

        		<th><?php echo ($content['sgst']+$content['cgst']+$content['igst']+$content['taxable_value']); ?></th>
        	</tr>

            <tr>
        		<th>Total</th>
                <th><?php echo number_format($content['entry_fee'],2);?></th>
        		<th><?php echo number_format($content['taxable_value'],2);?></th>
        		<th><?php echo number_format($content['sgst'],2);?></th>
        		<th><?php echo number_format($content['cgst'],2);?></th>
        		<th><?php echo number_format($content['igst'],2);?></th>

        		<th><?php echo number_format(($content['sgst']+$content['cgst']+$content['igst']+$content['taxable_value']),2); ?></th>
        	</tr>

        </table>

    </td>
</tr>

            <tr class="total">
                <td></td>
                <td>
                   Taxable Amount: <?php echo $content['taxable_value']; ?>
                </td>
            </tr>

            <tr class="total">
                <td></td>
                <td>
                   Total Tax: <?php echo ($content['sgst']+$content['cgst']+$content['igst']); ?>
                </td>
            </tr>

            <tr class="total">
                <td></td>
                <td>
                   Invoice Total: <?php echo ($content['sgst']+$content['cgst']+$content['igst']+$content['taxable_value']); ?>
                </td>
            </tr>


<tr>
    <td colspan="3" style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
    	
    	<p style="margin-left:3px;">For <?php echo strtoupper($content['company_name']); ?></p>

        <p style="text-align: center">Tax payable under Reverse Charge : No</p>
        <p style="text-align: center">Office Address : <?php echo $this->app_config['allow_gst']['custom_data']['firm_name'].' '.$this->app_config['allow_gst']['custom_data']['firm_address']; ?></p>
    </td>
</tr>


<tr>
    <td colspan="3" style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
        Cheers,<br>
        <?php echo SITE_TITLE; ?> Team
    </td>
</tr>
<tr>
    <td colspan="3" style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td  colspan="3" style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td colspan="3"></td>
</tr>



<!--End middle section-->
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>