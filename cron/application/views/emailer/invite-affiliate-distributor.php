<?php $this->load->view("emailer/header"); ?>
                  <tr>
                    <td style="background-color:#ffffff;"> </td>
                  </tr>
                  <tr>
                    <td style="background-color:#ffffff;"> </td>
                  </tr>
                  <tr>
                    <td style="padding:0 30px 0 30px;background-color:#ffffff;">
                      <p style="color:#3FAFEF;font-size:18px;font-family:Calibri;margin:0px;padding:0;font-weight:bold;line-height:50px;">Dear *|firstname|* *|lastname|*,</p>
                    </td>
                  </tr>
                  <tr>
                    <td style="background-color:#ffffff;"> </td>
                  </tr>
                  <tr>
                    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
                      Welcome to Golden Jeeto. We're happy to have you as our newest Distributor/Sub-Distributor on our website.</td>
                    </tr>
                    <tr>
                      <td style="background-color:#ffffff;"> </td>
                    </tr>
                    <tr>
                      <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">Please use email, password and <a href="*|login_path|*">click here</a> to login your account.</td>
                    </tr>
                    <tr>
                      <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
                        <b>Email :</b> *|email|*
                      </td>
                    </tr>
                    <tr>
                      <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
                        <b>Password :</b> *|raw_password|*
                      </td>
                    </tr>
                    <tr>
                      <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
                        we wish you great games.</td>
                      </tr>
                      <tr>
                        <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
                          <b>Note :</b> After login do not forgot to change your password.
                        </td>
                      </tr>
                      <tr>
                        <td style="background-color:#ffffff;"> </td>
                      </tr>
                      <tr>
                        <td style="background-color:#ffffff;"> </td>
                      </tr>
                      <tr>
                        <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
                          Cheers,<br>
                          Golden Jeeto Team
                        </td>
                      </tr>
                      <tr>
                        <td style="background-color:#ffffff;"> </td>
                      </tr>
                      <tr>
                        <td style="background-color:#ffffff;"> </td>
                      </tr>
                      <tr>
                        <td> </td>
                      </tr>
                     <?php $this->load->view("emailer/footer"); ?>