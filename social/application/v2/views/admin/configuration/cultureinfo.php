
<div class="clearfix">&nbsp;</div>
<div ng-controller="CultureInfoCtrl" id="CultureInfoCtrl">
    <!--Info row-->
    <div class="info-row row-flued">
        <h2><span id="spnh2"><?php echo lang('CultureInfo'); ?></span> ({{totalRecord}})</h2>
        <div class="info-row-right"></div>
    </div>
    <!--/Info row-->

    <div class="row-flued">
        <div class="table-scrollable">
            <table class="table table-hover table-bordered culture_table">
                <tr>                    
                    <th>Language Name</th>
                    <th>Culture Name</th>
                </tr>
                <tr class="rowtr" ng-repeat="culturelist in listData[0].ObjLanguage">
                    <td>{{culturelist.language_name}}</td>
                    <td>{{culturelist.culture_name}}</td>
                </tr>                  
            </table>
        </div>
        <span id="result_message" class="result_message">There is no record to show.</span>
    </div>    
</div>