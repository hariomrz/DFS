<html>
<head>
    <style>
        body{font-size:14px;}
        .invoice-box {border: 1px solid #eee;box-shadow: 0 0 10px rgba(0, 0, 0, .15);font-size: 14px;line-height: 24px;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;color: #555;}
        .invoice-box table td {padding: 5px;vertical-align: top;}
        .invoice-box table tr.top table td {padding-bottom: 20px;}
        .invoice-box table tr.top table td.title {font-size: 45px;line-height: 45px;color: #333;}
        .invoice-box table tr.information table td {padding-bottom: 40px;}
        .invoice-box table tr.heading td {background: #d1d4d5;border-bottom: 1px solid #ddd;font-weight: bold;text-align: center;}
        .invoice-box table tr.details td {padding-bottom: 20px;}
        .invoice-box table tr.item td{border-bottom: 1px solid #eee;}
        tr.heading_td td {text-align: center;}
    </style>
</head>
<body>
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0" width="100%">
            <tr class="information">
                <td colspan="2">
                    <table>
                        <tr>
                            <td style="text-align:left">
                                <img src="<?php echo IMAGE_PATH; ?>assets/img/logo.png" style="max-width: 200px;"><br/>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="text-transform: uppercase;"><b>Customer Details</b></td>
            </tr>
            <tr>
                <td colspan="2">
                    <table cellpadding="0" cellspacing="0" width="100%" border="2">
                        <tr class="heading">
                            <td>UserID</td>
                            <td>Name</td>
                            <td>Username</td>
                            <td>Mobile</td>
                            <td>Email</td>
                            <td>PAN No</td>
                            <td>Balance</td>
                            <td>Status</td>
                            <td>RegisterDate(<?php echo $data['timezone'];?>)</td>
                        </tr>
                        <tr class="heading_td">
                            <td><?php echo $data['user_id'];?></td>
                            <td><?php echo trim($data['full_name']);?></td>
                            <td><?php echo $data['user_name'];?></td>
                            <td><?php echo $data['phone_no'];?></td>
                            <td><?php echo $data['email'];?></td>
                            <td><?php echo $data['pan_no'];?></td>
                            <td><?php echo $data['balance'];?></td>
                            <td><?php echo $data['user_status'];?></td>
                            <td><?php echo $data['register_date'];?></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="text-transform: uppercase;">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2" style="text-transform: uppercase;"><b>Transaction Details</b></td>
            </tr>
            <tr>
                <td colspan="2">
                    <table cellpadding="0" cellspacing="0" width="100%" border="2">
                        <tr class="heading">
                            <td>TransactionID</td>
                            <td>Amount</td>
                            <td>GatewayName</td>
                            <td>Status</td>
                            <td>OrderDate(<?php echo $data['timezone'];?>)</td>
                            <td>PGTxnID</td>
                            <td>PGTxnAmount</td>
                            <td>PGTxnDate</td>
                            <td>PaymentMode</td>
                            <td>BankTxnId</td>
                        </tr>
                        <tr class="heading_td">
                            <td><?php echo $data['transaction_id'];?></td>
                            <td><?php echo $data['amount'];?></td>
                            <td><?php echo $data['gate_way_name'];?></td>
                            <td><?php echo $data['status'];?></td>
                            <td><?php echo $data['date_added'];?></td>
                            <td><?php echo $data['txn_id'];?></td>
                            <td><?php echo $data['txn_amount'];?></td>
                            <td><?php echo $data['txn_date'];?></td>
                            <td><?php echo $data['payment_mode'];?></td>
                            <td><?php echo $data['bank_txn_id'];?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>