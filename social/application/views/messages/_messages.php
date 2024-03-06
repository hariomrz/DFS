<script type="text/javascript">
  var MsgType = '<?php echo $Type ?>';
  var MsgGUID = '<?php echo $GUID ?>';
</script>
<div class="container wrapper" id="messageSectionCtrl" data-ng-controller="messageSectionCtrl">
  <div class="custom-modal"> 
        <div class="row">
            <aside class="col-md-12 col-sm-12 col-xs-12">
                <div class="panel panel-default messagePanel">                   
                      <div ng-cloak class="message-left" ng-init="get_threads();">
                          <div class="m-left-header">
                             <span  class="msz-label" ng-bind="(Filter=='UN_READ') ? 'UNREAD' : (Filter=='ARCHIVED') ? 'ARCHIVED' : 'INBOX' ;"></span>
                             <a class="btn btn-default pull-right m-l-5 visible-xs" id="newMessage" ng-click="compose_new_message()"> 
                                <i class="ficon-email"></i>
                              </a>

                             <div class="m-left-dropdown">
                               <a  data-toggle="dropdown" class="btn btn-default dropdown-toggle"> 
                                  <span class="text" ng-bind="::lang.msg_more"></span> <i class="caret"></i>
                               </a>
                               <ul role="menu" class="dropdown-menu">
                                    <li><a ng-if="Filter!==''" ng-click="get_filter_thread('')" href="javascript:void(0);" ng-bind="::lang.msg_inbox"></a></li>
                                    <li><a ng-if="Filter!=='UN_READ'" ng-click="get_filter_thread('UN_READ')" href="javascript:void(0);" ng-bind="::lang.msg_unread"></a></li>
                                    <li><a ng-if="Filter!=='ARCHIVED'" ng-click="get_filter_thread('ARCHIVED')" href="javascript:void(0);" ng-bind="::lang.msg_archive"></a></li>
                                </ul>
                             </div> 
                          </div>
                          <div class="clear"></div>
                          <div class="m-search">
                              <i class="icon-m-search ficon-search" ng-click="removeSearch();"></i>
                              <input type="text" class="m-search-input" ng-model="SearchKeyword" ng-keyup="get_search_thread();" value="" placeholder="Search">
                          </div>
 
                          <div class="m-left-scroll mCustomScrollbar-left" ng-cloak>
                            <ul class="m-user-listing" ng-class="(Filter=='ARCHIVED') ? 'archive' : '' ;">
  
                                
                                <!-- Thread Starts -->
                                <li ng-repeat="thread in thread_list" repeat-done="layoutDone()" id="thread-{{thread.ThreadGUID}}" ng-class="(thread.InboxNewMessageCount>0) ? 'unread' : '' ;">
                                    <div ng-click="get_new_thread_details(thread.ThreadGUID)" ng-if="thread.ThreadImageName==''" class="m-user-thmb" ng-class="(thread.Recipients.length>2) ? 'group-thumb' : 'group-thumb-two' ;">
                                        <span ng-repeat="recipients in thread.Recipients">
                                          <img ng-src="{{ImageServerPath+'upload/profile/220x220/'+recipients.ProfilePicture}}" >
                                        </span>                                          
                                    </div>

                                    <div ng-click="get_new_thread_details(thread.ThreadGUID)" ng-if="thread.ThreadImageName!==''" class="m-user-thmb">
                                        <span>
                                          <img ng-if="thread.EditableThread=='1'" width="50" ng-src="{{ImageServerPath+'upload/messages/220x220/'+thread.ThreadImageName}}" >
                                          <img ng-if="thread.EditableThread=='0'" width="50" ng-src="{{ImageServerPath+'upload/profile/220x220/'+thread.ThreadImageName}}" >
                                        </span>                                      
                                    </div>

                                    <div class="m-msz-detail-right">
                                        <span class="m-msz-time" ng-bind="date_format((thread.InboxUpdated),1)"></span>
                                        <a ng-if="thread.InboxNewMessageCount>0" ng-bind="thread.InboxNewMessageCount+' New'" class="m-new-msz"></a>
                                        <div class="m-msz-action"> 
                                           <i ng-click="change_thread_status(thread.ThreadGUID,'DELETED');" class="ficon-cross" data-toggle="tooltip" data-placement="top" title="Remove">&nbsp;</i>
                                           <i ng-if="Filter!=='ARCHIVED'" ng-click="change_thread_status(thread.ThreadGUID,'ARCHIVED');" class="ficon-archive" data-toggle="tooltip" data-placement="top" title="Archive">&nbsp;</i>
                                           <i ng-if="Filter=='ARCHIVED'" ng-click="change_thread_status(thread.ThreadGUID,'UN_ARCHIVE');" class="ficon-archive" data-toggle="tooltip" data-placement="top" title="UnArchive">&nbsp;</i>
                                           <i ng-click="change_thread_status(thread.ThreadGUID,'UN_READ');" ng-if="thread.InboxNewMessageCount==0" class="ficon-circle f-xs" data-toggle="tooltip" data-placement="top" title="Mark as unread">&nbsp;</i>
                                           <i ng-click="change_thread_status(thread.ThreadGUID,'READ');" ng-if="thread.InboxNewMessageCount>0" class="ficon-circle f-xs" data-toggle="tooltip" data-placement="top" title="Mark as read">&nbsp;</i> 
                                        </div>
                                    </div>
                                    <div class="m-msz-indetail">
                                         <span ng-click="get_new_thread_details(thread.ThreadGUID)" class="m-ellipsis" ng-bind="thread.ThreadSubject"></span>
                                         <div ng-if="thread.Body!==''" ng-click="get_new_thread_details(thread.ThreadGUID)" class="m-msz-short m-ellipsis" ng-bind-html="getMsgBodyHTML(thread.Body,1)"></div>
                                         <div ng-if="thread.Body=='' && thread.AttachmentCount==1" ng-click="get_new_thread_details(thread.ThreadGUID)" class="m-msz-short m-ellipsis" ng-bind="thread.AttachmentCount+' file attached'"></div>
                                         <div ng-if="thread.Body=='' && thread.AttachmentCount>1" ng-click="get_new_thread_details(thread.ThreadGUID)" class="m-msz-short m-ellipsis" ng-bind="thread.AttachmentCount+' files attached'"></div>
                                         
                                    </div>
                                </li>
                                <!-- Thread Ends -->
                            </ul>
                          </div>
                          <div style="display:none;" class="m-left-loader loader"></div>
                      </div>

                      <!-- Message Right Section -->

                      <div  ng-cloak  class="message-right overflow  hidden-xs">
                         <div class="m-right-header">
                            <div class="m-right-head-left">
                                <div class="m-user-name">
                                  <div class="backto-list visible-xs" id="backTolist">
                                      <i class="ficon-arrow-left"></i>
                                    </div>
                                     <span ng-if="ShowSettings=='0'" ng-bind="'Compose new message'"></span>
                                     <span ng-show="Messages.EditableThread=='1'" class="m-name-list" data-rel="group-usertip" ng-bind="Messages.ThreadSubject"></span>
                                     <span ng-show="Messages.EditableThread=='0'" class="m-name-list" ng-bind="Messages.ThreadSubject"></span>
                                     <div class="btn-group edit-group-name" ng-if="ShowSettings==1 && Messages.EditableThread=='1'">
                                          <button type="button" class="btn btn-default btn-sm" onclick="$('.m-edit-group-name').fadeIn();">
                                            <i class="ficon-pencil"></i>
                                          </button> 
                                      </div>
                                      <div class="m-edit-group-name">
                                              <div class="group-name-content">
                                                <table>                                                     
                                                  <tbody>
                                                    <tr>
                                                      <td ng-bind="::lang.msg_c_name"></td>
                                                      <td><input ng-model="EditSubject" type="text" class="form-control"></td>
                                                    </tr>
                                                    <tr>
                                                      <td ng-bind="::lang.msg_c_pic"></td>
                                                      <td> 
                                                          <div class="group-pic-view pos-relative" ng-if="( isThreadImageUploading || threadImage.ImageName )">
                                                              <div class="loader" style="display : block" ng-if="isThreadImageUploading"></div>
                                                              <i ng-click="removeThreadImage();" class="ficon-cross" ng-if="( !isThreadImageUploading && threadImage.ImageName )"></i>
                                                              <img alt="Thread Image" ng-src="{{ threadImage.ImageServerPath + '/220x220/' + threadImage.ImageName }}" ng-if="( !isThreadImageUploading && threadImage.ImageName )">
                                                          </div>
                                                        <div id="threadImageNgf" class="browse-button">
                                                          <button type="button" class="btn btn-primary" ng-if="( !threadImage.ImageName )" ngf-select="uploadThreadImage($file, $invalidFiles);" accept="image/*" ngf-validate-async-fn="validateFileSize($file, 'image');">{{::lang.msg_browse}}</button>
                                                        </div>

                                                      </td>
                                                    </tr>
                                                  </tbody>
                                                </table>
                                              </div>
                                              <div class="modal-footer">
                                                 <button type="submit" ng-disabled="isThreadImageUploading" onclick="$('.m-edit-group-name').hide();" ng-click="edit_thread(Messages.ThreadGUID);" class="btn btn-primary btn-sm pull-right">{{::lang.msg_done}}</button>
                                                <button type="submit" ng-disabled="isThreadImageUploading" onclick="$('.m-edit-group-name').hide();" class="btn btn-default pull-right">{{::lang.msg_cancel}}</button>
                                              </div>
                                          </div>
                                 </div>                                
                            </div>

                            <div class="m-right-head-right">
                                  <a class="btn btn-default pull-right visible-xs" ng-click="compose_new_message()"> 
                                   <span class="icon"><i class="ficon-plus f-mlg"></i></span>
                                 </a>
                                <button ng-if="ShowSettings==1" type="button" class="btn btn-default btn-sm pull-right hidden-xs" ng-click="compose_new_message()"><span class="icon"><i class="ficon-plus f-xlg"></i></span><span class="text" ng-bind="::lang.msg_new_msg"></span></button>
                                <div ng-if="ShowSettings==1" class="btn-group m-add-people-button">
                                  <button type="button" class="btn btn-default dropdown-toggle btn-sm" data-toggle="dropdown">
                                  <span class="icon"><i class="ficon-settings"></i></span><span class="caret"></span></button>
                                  <ul class="dropdown-menu" role="menu">
                                    <li ng-if="Messages.EditableThread=='1'"><a ng-click="resetAddPeople();" data-toggle="modal" data-target="#addPeople" ng-bind="::lang.msg_add_people"></a></li> 
                                    <li ng-if="Messages.EditableThread=='1' && Messages.CanRemoveParticipant=='1'"><a data-toggle="modal" data-target="#editParticipants" ng-bind="::lang.msg_edit_participants"></a></li>
                                    <li><a ng-click="change_thread_status(Messages.ThreadGUID,'DELETED');" ng-bind="::lang.msg_delete_conversation"></a></li> 
                                  </ul>
                                </div>                        
                            </div> 
                          </div>

                          <div class="m-conversation">
                            <div style="display:none;" class="m-conversation-loader loader"></div>
                            <aside ng cloak class="m-new-message" ng-if="ShowSettings==0">
                                <span class="m-message-to">To:  </span>
                                <div class="m-autosuggest">
                                  <div id="sendMszto">
                                    <tags-input ng-model="tags" display-property="name" add-from-autocomplete-only="true" on-tag-added="getPlaceHolder()" on-tag-removed="getPlaceHolder()" key-property="UserGUID" placeholder="Name" replace-spaces-with-dashes="false">
                                        <auto-complete source="loadFriends($query)"
                                                   min-length="0"
                                                   load-on-focus="true"
                                                   load-on-empty="true"
                                                   max-results-to-show="4"
                                                   template="userDropdownTemplate"></auto-complete>
                                      </tags-input>
                                  </div>                                            
                                  <script type="text/ng-template" id="userDropdownTemplate">
                                      <a href="javascript:void(0);" class="m-conv-list-thmb">
                                        <img ng-src="{{data.thumb}}" >
                                      </a>
                                      <a href="javascript:void(0);" class="m-u-list-name"  ng-bind-html="$highlight($getDisplayText())"></a>
                                  </script>  

                                </div>
                            </aside>

                              <div class="m-conversation-block mCustomScrollbar-right">
                                  <div class="m-conversation-content">
                                      <!-- <abbr class="conv-started">Conversation started 10 Nov 2014</abbr>  -->
                                      <ul class="m-conversation-list">
                                          <li ng-repeat="msg in MessageList" ng-class="{'m-group-activity':(msg.Type=='AUTO' && msg.ActionName!=='THREAD_CREATED' && msg.ActionName!=='CONVERSATION_DATE'),'conversation-date':(msg.NewDate!=='' && msg.ActionName!=='THREAD_CREATED' && $index>0)}" repeat-done="layoutDone()" id="msg-{{msg.MessageGUID}}">
                                              <div class="m-date-seprator"  ng-if="msg.NewDate!=='' && msg.ActionName!=='THREAD_CREATED' && $index>0">
                                                <span class="conv-date" ng-bind="msg.NewDate"></span>

                                              </div>  
                                              <a class="icon" ng-if="msg.Type=='MANUAL'" ng-click="removeMessage(msg.MessageGUID)" ><i class="ficon-cross"></i></a>


                                                
                                                <a ng-if="msg.Type=='MANUAL'" ng-href="{{'<?php echo site_url() ?>'+msg.ProfileURL}}" class="m-conv-list-thmb loadbusinesscard" entitytype="user" entityguid="{{msg.UserGUID}}">
                                                  <img ng-src="{{ImageServerPath+'upload/profile/220x220/'+msg.ProfilePicture}}" >
                                                </a>
                                                <div ng-if="msg.Type=='MANUAL'" class="overflow m-conv-msz">
                                                  <a ng-href="{{'<?php echo site_url() ?>'+msg.ProfileURL}}" class="loadbusinesscard" entitytype="user" entityguid="{{msg.UserGUID}}" ng-bind="msg.FirstName+' '+msg.LastName"></a>
                                                  <span class="m-msz-time" ng-bind="getFormattedTime(msg.CreatedDate,'h:mm A')"></span>
                                                  <div class="m-msz-text" ng-bind-html="getMsgBodyHTML(msg.Body)"></div>
                                                  <div ng-repeat="files in msg.Media" ng-cloak ng-if="files.MediaType=='Documents'" class="m-attached-file">
                                                    <span class="icon">
                                                        <i class="ficon-article"></i>
                                                    </span>
                                                    <div class="overflow">
                                                      <a class="link" ng-href="{{'<?php echo site_url() ?>home/download/'+files.MediaGUID}}" ng-bind="::lang.msg_download"></a>
                                                      <span class="text" ng-bind="files.OriginalName"></span>
                                                    </div>
                                                  </div>
                                                  <ul class="m-msz-attached" id="lg-{{msg.MessageGUID}}">
                                                    <li ng-repeat="images in msg.Media" ng-init="callLightGallery(msg.MessageGUID)" ng-data-thumb="{{ImageServerPath+'upload/messages/220x220/'+images.ImageName}}" ng-data-src="{{ImageServerPath+'upload/messages/'+images.ImageName}}" ng-if="images.MediaType=='Image'" class="attached-list">
                                                        <img  ng-src="{{ImageServerPath+'upload/messages/220x220/'+images.ImageName}}" />
                                                    </li>
                                                    <li ng-repeat="images in msg.Media" ng-init="(images.ConversionStatus=='Finished') ? callLightGallery(msg.MessageGUID) : '' ;" ng-data-html="{{'#m-'+images.MediaGUID}}" ng-if="images.MediaType=='Video'" ng-class="{'videoprocess':images.ConversionStatus!='Finished','attached-video':images.MediaType=='Video'}" ng-data-thumb="{{ImageServerPath+'upload/messages/220x220/'+images.ImageName+'jpg'}}" class="attached-list">
                                                        <img ng-if="images.ConversionStatus=='Finished'" ng-src="{{ImageServerPath+'upload/messages/220x220/'+images.ImageName+'jpg'}}" ng-cloak  ng-data-src="{{ImageServerPath+'upload/messages/220x220/'+images.ImageName+'jpg'}}" />
                                                        <i class="icon-wall-video" ng-if="images.MediaType=='Video' && images.ConversionStatus=='Finished'"></i>
                                                        <div style="display:none;" id="m-{{images.MediaGUID}}">
                                                        <img ng-cloak  ng-data-src="{{ImageServerPath+'upload/messages/220x220/'+images.ImageName+'jpg'}}" />
                                                        <video width="100%" controls="" class="object">
                                                            <source type="video/mp4" src="" dynamic-url dynamic-url-src="{{ImageServerPath+'upload/messages/'+images.ImageName+'mp4'}}"></source>
                                                            <source type="video/ogg" src="" dynamic-url dynamic-url-src="{{ImageServerPath+'upload/messages/'+images.ImageName+'ogg'}}"></source>
                                                            <source type="video/webm" src="" dynamic-url dynamic-url-src="{{ImageServerPath+'upload/messages/'+images.ImageName+'webm'}}"></source>
                                                             {{::lang.a_browser_not_support_html5}}
                                                        </video>
                                                    </div>
                                                    </li>
                                                  </ul>
                                                </div>
                                              <abbr ng-if="msg.Type=='AUTO' && msg.ActionName=='THREAD_CREATED'" class="conv-started" ng-bind-html="to_trusted(msg.Body)"></abbr>
                                              <span ng-if="msg.Type=='AUTO' && msg.ActionName!=='THREAD_CREATED' && msg.ActionName!=='CONVERSATION_DATE'" class="m-msz-time pull-right" ng-bind="getFormattedTime(msg.CreatedDate,'h:mm A')"></span>                                            
                                              <div ng-if="msg.Type=='AUTO' && msg.ActionName!=='THREAD_CREATED' && msg.ActionName!=='CONVERSATION_DATE'" ng-bind-html="to_trusted(msg.Body)"></div> 
                                          </li>
                                      </ul> 
                                   
                                  </div>
                              </div>


                          </div>
<!--  Write a Reply  -->   <div class="clear"></div>
                          <div class="m-write-reply">
                            <div class="m-write-reply-inner">
                              <div class="m-write-msz">
                                <textarea ng-cloak ng-if="ShowSettings=='1'" class="msgbody" ng-model="MsgBody" name="" placeholder="Write a reply"></textarea>
                                <textarea ng-cloak ng-if="ShowSettings=='0'" class="msgbody" ng-model="MsgBody" name="" placeholder="Write a message"></textarea>
                              </div>
                              <div class="m-attachment-view" ng-show="( ( mediaCount > 0 ) || ( fileCount > 0 ) )">
                                  <ul class="m-file-attached-list" ng-cloak ng-show="(fileCount > 0)">
                                    <li ng-repeat="(fileKey, file) in files">
<!--                                        <div ng-if="!file.progress" class="loader loader-attach-file" style="display:block; font-size:0.8em;"></div>-->
                                        <div ng-if="file.progressPercentage && file.progressPercentage < 101" data-percentage="{{file.progressPercentage}}" upload-progress-bar-cs></div>
                                        <div >
                                            <i class="ficon-article"></i> {{ file.data.OriginalName || file.name }}
                                            <i class="ficon-cross pull-right" ng-click="removeMsgAttachement('file', fileKey, file.data.MediaGUID)"></i>
                                        </div>
                                    </li>
                                  </ul>
                                  <div class="m-media-attached-list m-file-attached-wrapper" ng-cloak ng-show="(mediaCount > 0)" style="display: block;">
                                      <ul class="attachedList">
                                          <li ng-repeat="(mediaKey, media) in medias" make-ul-width>
<!--                                              <div ng-if="!media.progress" class="m-laoder"><div class="loader" style="display:block;"></div></div>-->
                                <div ng-if="media.progressPercentage && media.progressPercentage < 101" data-percentage="{{media.progressPercentage}}" upload-progress-bar-cs></div>
                                              <div ng-show="media.progress">
                                                  <img ng-src="{{ media.data.ImageServerPath + '/' + media.data.ImageName }}" >
                                                  <div class="m-hoveraction"><i class="icon-removemedia" ng-click="removeMsgAttachement('media', mediaKey, media.data.MediaGUID)"></i></div>
                                              </div>
                                          </li>
                                      </ul>
                                  </div>
                              </div>
                              <div class="m-attachment-block">
                                 <ul class="m-attachment-button">
                                    <!--<li id="addFile" onclick="checkAttachmentView();">-->
                                    <li ngf-select="uploadMsgFiles($files, $invalidFiles)" multiple ngf-validate-async-fn="validateFileSize($file, 'file');">
                                      <i class="ficon-attachment"></i>
                                      <span class="text" ng-bind="::lang.msg_add_file"></span>
                                    </li>
                                    <!--<li id="addMessageMedia" onclick="checkAttachmentView();">-->
                                    <li ngf-select="uploadMsgFiles($files, $invalidFiles)" multiple ngf-validate-async-fn="validateFileSize($file, 'image');">
                                      <i class="ficon-imageicn"></i> 
                                      <span class="text" ng-bind="::lang.msg_add_photos"></span>                                      
                                    </li>
                                  </ul> 
                                  <button ng-if="ShowSettings=='1'" type="button" ng-disabled="isMsgAttachementUploading" class="send-btn-msg btn btn-primary btn-small pull-right" ng-click="reply();"> {{::lang.msg_send}} </button>
                                  <button ng-if="ShowSettings=='0'" type="button" ng-disabled="isMsgAttachementUploading" class="send-btn-msg btn btn-primary btn-small pull-right" ng-click="compose();"> {{::lang.msg_send}} </button>
                              </div>
                               
                            </div>    
                          </div> 
                      </div> 

                     <div class="clearboth"></div>
                     
                </div>
             </aside>
          </div>
    </div>

    <!-- Custom Tooltip -->
 
    <div class="customTooltip animated" data-rel="custom-tooltip">
        <div class="tooltip-content mCustomScrollbar">
          <!--tooltip content goes heer-->
            <ul class="autosuggest-list">
              <li ng-repeat="recipients in RecipientsList">
                <figure> <img ng-src="{{'<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/'+recipients.ProfilePicture}}" > </figure>
                <a ng-href="{{'<?php echo site_url() ?>'+recipients.ProfileURL}}" ng-bind="recipients.FirstName+' '+recipients.LastName"></a>
              </li>
            </ul>
        </div>
    </div>

    <!--/Custom tooltip-->

    <!-- Add People -->

  <div class="modal fade" id="addPeople" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true">
      
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="icon-close"></i></span></button>
              <h4 class="modal-title" id="myModalLabel2" ng-bind="::lang.msg_add_people"></h4>
            </div>
            <div class="modal-body">
              <p ng-bind="::lang.msg_add_people_text"></p>
              <div class="form-group">
                <div data-error="" class="text-field m-inputtag">
                  <div class="tag-view">
                    <tags-input ng-model="tags" id="addPeopleTags" display-property="name" add-from-autocomplete-only="true" key-property="UserGUID" placeholder="Name" replace-spaces-with-dashes="false">
                      <auto-complete source="loadFriends($query)"
                                 min-length="0"
                                 load-on-focus="true"
                                 load-on-empty="true"
                                 max-results-to-show="4">
                      </auto-complete>
                    </tags-input>
                  </div> 
                </div>
              </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary pull-right marleft10" ng-click="add_people(Messages.ThreadGUID)" ng-bind="::lang.msg_add"></button>
                <button type="submit" class="btn btn-default pull-right" data-dismiss="modal" ng-bind="lang.msg_cancel"></button>
            </div>
          </div>
        </div>
      
    </div>


<!-- Edit Participants -->

<!-- All Model Box -->

<!-- Delete This Entire Conversation? --> 

  <div class="modal fade" id="editParticipants" tabindex="-1" role="dialog" aria-labelledby="myModalLabel3" aria-hidden="true">
    
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="icon-close"></i></span></button>
            <h4 class="modal-title" id="myModalLabel3" ng-bind="::lang.msg_edit_participants"></h4>
          </div>
          <div class="modal-body listing-view">
              <div class="default-scroll mCustomScrollbar">
                <ul class="list-group">
                     <li ng-repeat="recipients in RecipientsList" class="list-group-item">
                        <figure><a href="javascript:void(0);">
                            <img   class="img-circle" ng-src="{{ImageServerPath+'upload/profile/220x220/'+recipients.ProfilePicture}}"></a>
                        </figure>
                        <div class="description"> 
                            <a ng-href="{{'<?php site_url() ?>'+recipients.ProfileURL}}" class="name" ng-bind="recipients.FirstName+' '+recipients.LastName"></a> 
                            <!-- <span class="location">Indore, India</span>  -->                               
                        </div>
                        <a ng-if="recipients.UserGUID!==LoggedInUserGUID" class="remove-link" ng-click="remove_recipient(recipients.UserGUID)" href="javascript:void(0);" ng-bind="::lang.msg_remove"></a> 
                      </li>
                  </ul>
              </div>
          </div>
          <div class="modal-footer">
              <button type="submit" class="btn btn-default pull-right" data-dismiss="modal" ng-bind="::lang.msg_done"></button>
          </div>
        </div>
      </div>
     
  </div>

</div>
<input type="hidden" value="1" id="LeftPageNo" />
<input type="hidden" value="2" id="RightPageNo" />
<input type="hidden" value="uploadimage" id="endpoint" />
<!--//Container-->