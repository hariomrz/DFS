<div class="container wrapper" id="MyAccountCtrl" ng-controller="teachManProfCtrl">
 <div class="custom-modal" ng-init="initGoogleLocation();" ng-cloak>
 
  <div class="panel panel-default fadeInDown">
   <div class="modal-content">
    <div class="panel-body">
     <div class="pages-wrapper">
      <h4 class="label-title secondary-title text-center mart25">SETUP PROFILE</h4>
      <div class="info-text">Fill in these details to help your friends look for you and recognize you to connect. We wont bother you next time if these are all filled. </div>
      <div class="row">
       <div class="col-lg-7 col-md-7 col-sm-10 col-xs-12 pages-inner-block">
        <div class="page-top-block">
         <aside class="profile-pic set-profile-pic">
           <figure class="user-wall-thumb" ng-cloak>
              <img ng-src="{{ProfileImage}}" alt="User" title="User" class="img-circle">
            </figure>
            <div class="dropdown thumb-dropdown">  
              <a class="edit-profilepic dropdown-toggle" href="javascript:void(0);" data-toggle="dropdown">
                <i class="ficon-pencil"></i>
              </a>
              <ul class="dropdown-menu">
                <li>
                  <a data-target="#uploadModal" data-toggle="modal" ng-click="getPreviousProfilePictures();" href="javascript:void(0);">
                    <span class="space-icon"><i class="ficon-upload"></i></span>Upload New
                  </a>
                  <div class="hiddendiv">
                    <input type="file" name="" id="changeThumb">
                  </div>
                </li>
                <li><a href="javascript:void(0);" ng-if="ProfilePictureExists==1" ng-click="removeProfilePicture()"><span class="space-icon"><i class="ficon-cross"></i></span>Remove</a></li>
              </ul>
            </div>
          </aside>
        </div>
        <div class="page-bottom-block row">
          <form id="setupProfile" ng-submit="submitAboutMe();">
            <aside class="row">
              <aside class="col-xs-12 col-sm-6">
                <div class="form-group">
                  <label>First Name</label>
                    <div data-error="hasError" class="text-field">

                      <input type="text" ng-model="FirstName" data-req-minlen="2" maxlength="25" data-req-maxlen="25" data-requiredmessage="Required" data-msglocation="errorFirstname" data-mandatory="true" data-controltype="namefield" value="" id="firsttnamefieldCtrlID" placeholder="First name" uix-input="" />
                      <label id="errorFirstname" class="error-block-overlay"></label>
                    </div>
                </div>
              </aside>
              <aside class="col-xs-12 col-sm-6">
                <div class="form-group">
                  <label>Last Name</label>
                    <div data-error="hasError" class="text-field">

                      <input type="text" ng-model="LastName" data-req-minlen="2" maxlength="25" data-req-maxlen="25" data-requiredmessage="Required" data-msglocation="errorLastname" data-mandatory="true" data-controltype="namefield" value="" id="lastnamefieldCtrlID" placeholder="Last name" uix-input="" />
                      <label id="errorLastname" class="error-block-overlay"></label>
                    </div>
                </div>
              </aside>
              <aside class="col-xs-12 col-sm-12">
                <div class="form-group">
                  <label>Username</label>
                    <div data-error="hasError" class="text-field">

                      <input type="text" ng-model="Username" data-req-minlen="4" maxlength="25" data-req-maxlen="25" data-requiredmessage="Required" data-msglocation="errorUsername" data-mandatory="true" data-controltype="username" value="" id="usernameCtrlID" placeholder="Username" uix-input="">
                      <input type="hidden" ng-model="Email" />
                      <label id="errorUsername" class="error-block-overlay"></label>
                    </div>
                </div>
              </aside>
              <aside class="col-xs-12 col-sm-6 col-md-6">
                <div class="form-group">
                  <label class="control-label">Gender</label>
                    <div class="dradio form-group">
                      <div class="radio-btn" ng-class="(Gender==1) ? 'selected' : '' ;">
                        <i class="icon-male">&nbsp;</i>
                        Male
                        <input ng-model="Gender" ng-value="1" type="radio" name="Gender" id="male">
                      </div>
                      <div class="radio-btn" ng-class="(Gender==2) ? 'selected' : '' ;">
                        <i class="icon-female">&nbsp;</i>
                        Female
                        <input ng-model="Gender" ng-value="2" type="radio" name="Gender" id="fmale">
                      </div>
                    </div>
                </div>
              </aside>
              <aside class="col-xs-12 col-sm-6 col-md-6">
                <div class="form-group">
                   <label class="control-label">Date of Birth</label>
                     <div data-error="hasError" class="text-field">
                      <input ng-model="DOB" type="text" data-requiredmessage="Required" readonly id="Datepicker3" name="DOB" placeholder="__/__/____" />
                      <label id="errorDateofbirth" class="error-block-overlay"></label>
                    </div>
                </div>
              </aside>
              <aside class="col-xs-12 col-sm-12">
                <div class="form-group">
                  <label>Location</label>
                   <div data-error="hasError" class="text-field">
                      <input type="text" id="address" ng-model="Location"  data-requiredmessage="Required" data-msglocation="errorLocation" data-mandatory="true" data-controltype="" value="" placeholder="Location" uix-input="" />
                      <input type="hidden" ng-model="City" />
                      <input type="hidden" ng-model="State" />
                      <input type="hidden" ng-model="Country" />
                      <label id="errorLocation" class="error-block-overlay"></label>
                    </div>
                </div>
              </aside>
              <aside class="col-xs-12 col-sm-12">
                <div class="form-group">
                  <label>Hometown</label>
                    <div data-error="hasError" class="text-field">
                      <input ng-model="HLocationEdit" type="text" id="hometown" data-requiredmessage="Required" data-msglocation="errorHometown" data-mandatory="true" data-controltype="" value="" placeholder="Hometown" uix-input="">
                      <input type="hidden" ng-model="HCity" />
                      <input type="hidden" ng-model="HState" />
                      <input type="hidden" ng-model="HCountry" />
                      <label id="errorHometown" class="error-block-overlay"></label>
                    </div>
                </div>
              </aside>
              <aside class="col-xs-12 col-sm-6">
                <div class="form-group">
                  <label>Relationship</label>
                  <div class="text-field-select" ng-init="RelationshipOptions=[{val:'1',Relation:'Single'},{val:'2',Relation:'In a relationship'},{val:'3',Relation:'Engaged'},{val:'4',Relation:'Married'},{val:'5',Relation:'Its complicated'},{val:'6',Relation:'Separated'},{val:'7',Relation:'Divorced'}]">
                      <select  
                          ng-model="MartialStatus" 
                          ng-value="MartialStatus" 
                          id="MStatus" 
                          name="MaritalStatus" 
                          data-chosen="" 
                          data-disable-search="true" 
                          data-ng-change="showRelationWith();" 
                          data-placeholder="Choose Marital Status"
                          ng-options="Relationship.val as Relationship.Relation for Relationship in RelationshipOptions">
                          <option value=""></option>
                      </select>
                  </div>
                </div>
              </aside>
              <aside class="col-xs-12 col-sm-6" ng-if="MartialStatus==2 || MartialStatus==3 || MartialStatus==4" ng-init="InitRelationTo();">
                <div class="form-group">
                  <label>To</label>
                    <div data-error="hasError" class="text-field">
                      <input type="text" ng-model="RelationWith"  data-msglocation="errorTo" data-mandatory="false" data-controltype="relationfield" value="" id="RelationTo" class="form-control ui-autocomplete-input" placeholder="Start typing" uix-input="" />
                      <label id="errorTo" class="error-block-overlay"></label>
                    </div>
                </div>
                <span class="relate hidden-xs">-</span>
              </aside>
              <aside class="col-xs-12 col-sm-12">
                <div class="form-group">
                  <label>About</label>
                  <div data-error="hasError" class="textarea-field">
                    <textarea data-msglocation="erroraboutText" data-req-minlen="2" maxlength="200" data-mandatory="true" data-controltype="general" data-requiredmessage="Required" data-req-maxlen="200" maxcount="200" ng-model="aboutme" id="aboutText" name="aboutText" maxcount="200" placeholder="Write something about yourself..." uix-textarea></textarea>
                    <span id="spn2textareaID" style="cursor: pointer; color: Red; position: inherit;"></span><br>
                    <label id="erroraboutText" class="error-block-overlay"></label>
                    <span id="noOfCharaboutText" ng-bind="parseInt(200-aboutme.length)"></span>
 <input type="hidden" id="hasIntro" ng-model="Introduction" ng-bind="Introduction"  />
                    </div>
                </div>
              </aside>
              <aside class="col-xs-12 col-sm-12">
                <div class="pull-right">
                  <!-- <a href="javascript:void(0)" class="btn-link">Cancel</a> -->
                  <input type="hidden" id="ProfileSetup" value="1" />
                 
                  <input type="submit" value="SAVE" class="btn btn-primary" onclick="return checkstatus('setupProfile');" />
                </div>
              </aside>
            </aside>
         </form>
        </div>
       </div>
      </div>
     </div>
    </div>
   </div>
   <!-- /.modal-content --> 
  </div>
  <!-- /.modal-dialog --> 
 </div>
 <!-- /.modal --> 
</div>
 <div class="hiddendiv" id="profile-picture"></div> 
<!--//Container-->
<input type="hidden" id="isuserprofile" value="1" />