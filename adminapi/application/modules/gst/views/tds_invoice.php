<html>
<head>
    <style>
   
     body{
    font-size:14px;
    }
    .invoice-box {
        border: 1px solid #eee;
        box-shadow: 0 0 10px rgba(0, 0, 0, .15);
        font-size: 14px;
        line-height: 24px;
        font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        color: #555;
    }
    
    
    .invoice-box table td {
        padding: 5px;
        vertical-align: top;
    }
    
    .invoice-box table tr td:nth-child(2) {
        text-align: right;
    }
    
    .invoice-box table tr.top table td {
        padding-bottom: 20px;
    }
    
    .invoice-box table tr.top table td.title {
        font-size: 45px;
        line-height: 45px;
        color: #333;
    }
    
    .invoice-box table tr.information table td {
        padding-bottom: 40px;
    }
    
    .invoice-box table tr.heading td {
        background: #eee;
        border-bottom: 1px solid #ddd;
        font-weight: bold;
        text-align: center;
    }
    
    .invoice-box table tr.details td {
        padding-bottom: 20px;
    }
    
    .invoice-box table tr.item td{
        border-bottom: 1px solid #eee;
    }
    
    .invoice-box table tr.item.last td {
        border-bottom: none;
    }
    
    .invoice-box table tr.total td:nth-child(2) {
        border-top: 2px solid #eee;
        font-weight: bold;
    }
    tr.heading_td td {
        text-align: center;
    }
    
    
    </style>
</head>
<body>
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0" width="100%">
            
            <tr class="information">
                <td colspan="2">
                    <table>
                        <tr>
                            <td>
                                <b><?php echo $data['company_name']; ?></b><br>
                                GSTIN : -<?php echo $this->app_config['allow_gst']['custom_data']['gstin']; ?><br>
                                <?php echo $data['company_address']; ?><br>
                                HSN Code : -<?php echo $this->app_config['allow_gst']['custom_data']['hsn_code']; ?><br>
                                <?php echo $data['company_contact']; ?><br>
                                Description of Service : - Supply of online content service
                            </td>
                            <td style="text-align:center">
                                 <img src="/var/www/html/cron/assets/img/logo.png" width="50">
                            </td>

                            <td style="text-align:right">
                                <b>Invoice Date: <?php echo $data['date']; ?></b><br>
                                Invoice #: <?php echo $data['invoice_no']; ?><br>
                                Pan Number #: <?php echo $data['pan_no']; ?><br>

                                <?php echo $data['full_name']; ?><br>
                                <?php echo $data['email']; ?><br>
                                <?php echo $data['address']; ?><br>
                                <?php echo $data['city']; ?><br>
                                <?php echo $data['zip_code']; ?><br>
                                PLACE OF SUPPLY - <?php echo $data['state']; ?><br>
                            </td>
                            
                           
                        </tr>
                    </table>
                </td>
            </tr>
           
            <tr>
                <td colspan="2"><center><?php echo $data['collection_name'];?></center></td>
            </tr>

            <tr>
                <td colspan="2"><center>TDS Invoice</center></td>
            </tr>
            
            <tr>
                <td colspan="2">
                    <table cellpadding="0" cellspacing="0" width="100%" border="2">
                        <tr  class="heading">
                            <td>
                                ENTRY AMOUNT
                            </td>
                            
                            <td>
                               WINNING AMOUNT
                            </td>

                            <td>
                               TDS
                            </td>

                            <td>
                               NET AMOUNT PAYABLE
                            </td>
                            <td>
                               TOTAL
                            </td>
                        </tr>

                        <tr class="heading_td">
                            <td>
                              <?php echo $data['entry_fee'];?>
                            </td>
                            
                            <td>
                              <?php echo $data['win_amount'];?>
                            </td>

                            <td>
                              <?php echo $data['tds'];?>
                            </td>

                            <td>
                              <?php echo ($data['win_amount']-$data['tds']);?>
                            </td>

                            <td>
                               <?php echo ($data['tds']); ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>


            <tr class="total">
                <td></td>
                <td>
                   TDS Amount: <?php echo $data['tds']; ?>
                </td>
            </tr>

            <tr class="total">
                <td></td>
                <td>
                   Total Tax: <?php echo ($data['tds']); ?>
                </td>
            </tr>

        </table>
        <p style="margin-left:3px;">For <?php echo strtoupper($data['company_name']); ?></p>
        <!--<p style="margin-left:3px;"><?php //echo COMPANY_DIRECTOR_NAME ?></p> <br><br><br>-->

        <p style="text-align: center">Tax payable under Reverse Charge : No</p>
        <p style="text-align: center">Office Address : <?php echo $this->app_config['allow_gst']['custom_data']['firm_name'].' '.$this->app_config['allow_gst']['custom_data']['firm_address']; ?></p>

    </div>
</body>
</html>