<div class="modal fade" tabindex="-1" role="dialog" id="addNotes">
    <div class="modal-dialog" role="document">
        <div class="modal-content">  
            <div class="modal-header">
                <button type="button" class="close" onclick="$('#addNotes').modal('hide');" aria-label="Close">
                    <i class="ficon-cross"></i>
                </button>
                <h4 ng-if="userPersonaDetail" class="modal-title">Add Note for <span ng-bind="userPersonaDetail.FirstName+' '+userPersonaDetail.LastName"></span></h4>
                <h4 ng-if="!userPersonaDetail" class="modal-title">Add Note for <span ng-bind="Name"></span></h4>
            </div>           
            <div class="modal-body">
                <div class="stiky-overlay" data-type="post-overlay" ng-click="confirmCloseEditor()" ng-class="{active : overlayShow}"></div>
                <div ng-cloak>
                    <div class="post-editor" id="postEditor" ng-cloak style="position: relative; border:1px solid #ddd;">
                        <div class="loader postEditorLoader" style="top:30%;">&nbsp;</div>                                    
                        <div class="post-ws-editor">
                            <summernote ng-model="PostContentNotes" data-posttype="Post" on-change="change(contents);" on-paste="parseLinkDataWithDelay(evt,1)" on-focus="parseLinkData(evt,0)" on-keyup="parseLinkData(evt,0)" id="PostContentNotes" config="summernote_options"></summernote>
                            <input type="hidden" id="PostTitleInput"></input>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="button-group">
                    <button class="btn btn-default" onclick="$('#addNotes').modal('hide');" aria-label="Close" type="button">Cancel </button>
                    <button ng-if="IsEdit==0" ng-disabled="(!PostContentNotes)" class="btn btn-primary" id="ShareButton" ng-click="SubmitNote();" type="button">Add </button>
                    <button ng-if="IsEdit==1" ng-disabled="(!PostContentNotes)" class="btn btn-primary" id="ShareButton" ng-click="UpdateNote();" type="button">Edit </button>
                </div>
            </div> 
        </div>
    </div>
</div>