<div ng-controller="messageSectionCtrl" class="modal ng-scope" id="newMsg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" ng-cloak>

    <div class="modal-dialog" ng-init="getUserDetails();">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="icon-close"></i></span>
                </button>
                <h4 class="modal-title" id="myModalLabel" ng-bind="lang.w_new_message"></h4>
            </div>
            <form id="newmsgform" ng-submit="submitMessage();" class="ng-pristine ng-valid ng-valid-maxlength">
                <div class="modal-body">

                    <div class="no-scrollbar">
                        <div class="form-group">
                            <label ng-bind="lang.w_to_f_caps"></label>
                            <div class="text-field">
                                <div data-error="hasError" class="text-field">
                                    <input type="text" ng-value="FirstName+' '+LastName" id="toAddress" readonly="readonly" uix-input="" value="Suresh Patidar">
                                    <label id="errorTofield" class="error-block-overlay"></label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label ng-bind="lang.w_message"></label>
                            <div data-error="hasError" class="textarea-field">
                                <textarea maxcount="200" maxlength="200" data-req-maxlen="200" data-req-minlen="200" ng-model="MessageTxt" id="textareaID" uix-textarea="" placeholder="Write something..." class="msg-textarea ng-valid ng-valid-maxlength"></textarea>
                            <span style="cursor: pointer; color: Red; position: inherit;" id="spn2textareaID"></span><br><span id="noOfChartextareaID"></span>  </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary pull-right" onclick="return checkstatus('newmsgform');" ng-bind="lang.w_send_caps"></button>
                </div>
            </form>
        </div>
    </div>

</div>