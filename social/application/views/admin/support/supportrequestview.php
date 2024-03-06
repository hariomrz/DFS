<?php
$errorLogId = $_GET['logId'];
$errorStatus = $_GET['errorStatus'];
?>
<aside class="content-wrapper" ng-controller="SupportViewCtrl" id="SupportViewCtrl" ng-cloak>     
    <!--Bread crumb-->
    <div class="bread-crumb">
      <ul>
          <li> <a href="<?php echo base_url('admin/support'); ?>"><?php echo lang('SupportRequest_View'); ?></a></li>
      </ul>
    </div>
    <!--/Bread crumb-->
    <div class="clearfix"></div>
    <!--Info row-->
    <div class="info-row row-flued">
      <h2><?php echo lang('SupportRequest_View'); ?></h2>
      <div class="float-right support-feature"><a href="<?php echo base_url(); ?>admin/support?errorStatus=<?php echo $errorStatus; ?>" class="btn-link"><span><?php echo lang('Back'); ?></span></a> </div>
    </div>
    <!--/Info row-->
    
    <div class="row-flued">
        <table id="errorlogdetaildiv" class="users-table support-feature-view">
            <tbody>
                <tr>
                    <th class="ui-sort"><?php echo lang('SupportRequestView_Type'); ?></th>
                    <th class="ui-sort"><?php echo lang('SupportRequestView_Details'); ?></th>
                </tr>
                <tr>
                    <td><?php echo lang('Title'); ?></td>
                    <td>{{errorLogDetail.Title}}</td>
                </tr>
                <tr>
                    <td><?php echo lang('BrowserDetail'); ?></td>
                    <td>{{errorLogDetail.BrowserDetail}}</td>
                </tr>
                <tr>
                    <td><?php echo lang('Reporter'); ?></td>
                    <td>{{errorLogDetail.Reporter}}</td>
                </tr>
                <tr>
                    <td><?php echo lang('ReporterEmail'); ?></td>
                    <td>{{errorLogDetail.ReporterEmail}}</td>
                </tr>
                <tr>
                    <td><?php echo lang('IPAddress'); ?></td>
                    <td>{{errorLogDetail.IPAddress}}</td>
                </tr>
                <tr>
                    <td><?php echo lang('Description'); ?></td>
                    <td>{{errorLogDetail.ErrorDescription}}</td>
                </tr>
                <tr>
                    <td><?php echo lang('OperatingSystem'); ?></td>
                    <td>{{errorLogDetail.OperatingSystem}}</td>
                </tr>
                <tr>
                    <td><?php echo lang('CreatedDate'); ?></td>
                    <td>{{errorLogDetail.CreatedDate}}</td>
                </tr>
                <tr ng-if="errorLogDetail.ErrorTypeID == '5'">
                    <td><?php echo lang('QueryExecutionTime'); ?></td>
                    <td>{{errorLogDetail.QueryTime}} Seconds</td>
                </tr>
                <tr>
                    <td><?php echo lang('Files'); ?></td>
                    <td>
                        <ul id="supportimg" class="upload-category-image">
                            <li ng-repeat="file in errorLogDetail.errorLogAttachments" repeat-done="layoutDone();"> 
                                <img ng-src="{{file}}" > 
                                <div class="logimg">
                                    <a href="{{file}}" class="icon-zoomlist viewicon">&nbsp;</a>                                   
                                </div>
                            </li>
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table>
        <div id="accessdenieddiv"></div>
    </div>
    <div class="clearfix"></div>
  </aside>
<input type="hidden"  name="ErrorLogID" id="ErrorLogID" value="<?php echo $errorLogId; ?>"/>