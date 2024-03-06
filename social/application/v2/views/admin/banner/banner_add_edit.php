<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><a>Tools</a></li>
                    <li>/</li>
                    <li><span>Ad</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<section class="main-container">
    <div class="container" ng-controller="AdvertiseCtrl" id="ArticleListCtrl" ng-init="getBannerDetails('<?php echo $BlogID; ?>');
<?php
if (empty($BlogID))
{
    echo 'getBannerImageList();';
}
?>" ng-cloak>
        <aside class="content-wrapper">
            <!--Info row-->
            <div class="info-row row-flued">
                <h2>
                <?php
                if (!empty($BlogID))
                {
                    echo 'EDIT Ad';
                }
                else
                {
                    echo 'ADD Ad';
                }
                ?>

            </h2>
                <div class="info-row-right">
                    <a class="btn-link" href="<?php echo site_url('admin/advertise/banner'); ?>"><span>Back</span></a>
                </div>
            </div>
            <div class="panel">
                <form method="post" name="formBanner" id="formBanner" ng-submit="SaveBanner();" autocomplete="off">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="label" for="BlogUniqueID">Module Name <span>*</span></label>
                                    <div class="large">
                                        <select name="BlogUniqueID" id="BlogUniqueID" data-controltype="general" data-mandatory="true" data-msglocation="errorBlogUniqueID" data-requiredmessage="Please select module" data-ng-model="BannerData.BlogUniqueID" data-ng-change="getBannerImageList(); setSelectedBannerSize();" ng-options="k as v for (k, v) in BannerModule" chosen autocomplete="off">
                                            <option value="">Select Module</option>
                                        </select>
                                    </div>
                                    <div class="error-holder" id="errorBlogUniqueID"><span>Error</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="label" for="Title">Title <span>*</span></label>
                                    <input type="text" ng-model='BannerData.BlogTitle' name="Title" id="Title" class="form-control" data-req-maxlen="50" maxlength="50" data-msglocation="errorTitle" data-mandatory="true" data-controltype="" data-requiredmessage="Please enter title">
                                    <div class="error-holder" id="errorTitle"><span>Error</span></div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="label" for="Advertiser">Advertiser <span>*</span></label>
                                    <input type="text" ng-model='BannerData.Advertiser' name="Advertiser" id="Advertiser" class="form-control" ng-keyup="SearchAdvertiser()" data-req-maxlen="100" maxlength="100" data-msglocation="errorAdvertiser" data-mandatory="true" data-controltype="namefield" data-requiredmessage="Please enter advertiser">
                                    <div class="error-holder" id="errorAdvertiser"><span>Error</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="label" for="severName">Banner Source <span>*</span></label>
                                    <div class="radio-list">
                                        <label class="radio radio-inline">
                                            <input id="NewSource" type="radio" ng-model="BannerData.BannerSource" value="1">
                                            <span class="label">New</span>
                                        </label>
                                        <label class="radio radio-inline">
                                            <input id="ExistingSource" type="radio" ng-model="BannerData.BannerSource" value="2">
                                            <span class="label">Existing</span>
                                        </label>
                                    </div>
                                    <div class="error-holder"><span>Error</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="row" ng-if="BannerData.BannerSource == 2">
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <label class="label" for="port">Select Image <span>*</span></label>
                                    <div ng-if="BannerImageList.length > 0" class="jcarousel-panel thumb-carousel">
                                        <div id="existing-jcarousel" class="jcarousel">
                                            <ul>
                                                <li style="width: 100px;" ng-class="{'active': BannerData.SelectedBlogImage == BImage.ImageName}" ng-repeat="BImage in BannerImageList" ng-click="BannerData.SelectedBlogImage = BImage.ImageName;" repeat-done="existingCarousel()">
                                                    <img ng-src="<?php echo IMAGE_SERVER_PATH . 'upload/banner/' ?>{{BImage.ImageName}}"  />
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="jcarousel-control"> <a class="prev">&lsaquo;</a> <a class="next">&rsaquo;</a> </div>
                                    </div>
                                    <div ng-if="BannerImageList.length == 0" class="" style="color: #DC2626;">
                                        No existing images found.
                                    </div>
                                </div>
                                <!--div class="error-holder errorbox" ng-if="isValidate == true && BannerData.SelectedBlogImage == ''" >Please select ad image</div-->
                                <div class="col-sm-12">
                                    <div class="banner-preview">
                                        <img ng-if="BannerData.SelectedBlogImage != '' && BannerData.SelectedBlogImage != undefined" ng-src="<?php echo IMAGE_SERVER_PATH . 'upload/banner/' ?>{{BannerData.SelectedBlogImage}}" style="max-width: 100%;" />
                                        <div ng-if="BannerData.SelectedBlogImage != '' && BannerData.SelectedBlogImage != undefined && (BannerData.BlogUniqueID == 'home_page_carousel' || BannerData.BlogUniqueID == 'race_event_carousel')" class="banner-caption">
                                            <table class="banner-container">
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            <h2 ng-bind-html='toTrustedHTML(BannerData.BlogDescription)'></h2></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="error-holder"></div>
                                        <input type="hidden" ng-model="BannerData.SelectedBannerSize">
                                    </div>
                                </div>
                            </div>
                        </div>
 
                        <div class="row">
                         <div class="col-sm-12">
                          <div ng-if="BannerData.BannerSource == 1">
                                <div class="form-group" ng-if="BannerData.BlogUniqueID != 'home_page_carousel' && BannerData.BlogUniqueID != 'race_event_carousel' && BannerData.BlogUniqueID != 'home_page_sidebar1'" ng-init="initializeCropper();">
                                    <label class="label" for="severName">Upload Image </label>
                                    <div class="p-v-sm"><small>Note : For best result please upload image size 300 X 250</small></div>
                                    <div class="browse-image row" data-type="focus">
                                        <div class="col-sm-1">
                                            <div class="support-search-new btn btn-primary relative">
                                                <input type="file" id="fileInputBanner" value="Browse">
                                                <label class="label-white">Browse</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-2">
                                            <input type="text" class="form-control">
                                        </div>
                                    </div>
                                    <div style="display: none" class="error-holder" id="ErrorValideImage">
                                        <label class="errorbox">Please upload only jpg or png file</label>
                                    </div>
                                    <div class="form-group" style="margin: 10px 0px;">
                                        <input type="hidden" id="BannerSize" ng-init="BannerData.BannerSize = '300x250'" ng-model="BannerData.BannerSize">
                                        <img ng-if="BannerData.BlogImage != ''" ng-src="<?php echo IMAGE_SERVER_PATH . 'upload/banner/' ?>{{BannerData.BlogImage}}" style="max-width: 100%;" />
                                        <div class="cropArea" style="width: 350px;height: 200px; margin:10px 0px;" ng-show="myImageBanner">
                                            <img-crop image="myImageBanner" area-type="rectangle" aspect-ratio="1.2" result-image="myCroppedImageBanner" result-image-size='{w: 300,h: 250}' area-min-size='{w: 120,h: 100}'></img-crop>
                                        </div>
                                        <div style="display:none">Cropped Image:</div>
                                        <div>
                                            <img id="CroppedImgData" ng-src="{{myCroppedImageBanner}}" style="max-width: 100%;" />
                                            <div class="error-holder"><span></span></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group" ng-if="BannerData.BlogUniqueID == 'home_page_carousel' || BannerData.BlogUniqueID == 'race_event_carousel'" ng-init="initializeCropper();">
                                    <label class="label" for="severName">Upload Image </label>
                                    <div class="p-v-sm"><small>Note : For best result please upload image size 1600 X 400</small></div>
                                    <div class="browse-image row" data-type="focus">
                                        <div class="col-sm-2">
                                            <input type="text" class="form-control">
                                        </div>
                                        <div class="col-sm-1">
                                            <div class="support-search-new btn btn-primary relative">
                                                <input type="file" id="fileInputBanner" value="Browse">
                                                <label class="label-white">Browse</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="display: none" class="error-holder" id="ErrorValideImage">
                                        <label class="errorbox">Please upload only jpg or png file</label>
                                    </div>
                                    <div class="form-group" style="margin: 10px 0px;">
                                        <input type="hidden" id="BannerSize" ng-init="BannerData.BannerSize = '1600x400'" ng-model="BannerData.BannerSize">
                                        <div class="banner-preview">
                                            <img ng-if="BannerData.BlogImage != ''" ng-src="<?php echo IMAGE_SERVER_PATH . 'upload/banner/' ?>{{BannerData.BlogImage}}" style="max-width: 100%;" />
                                            <div ng-if="BannerData.BlogImage != ''" class="banner-caption">
                                                <table class="banner-container">
                                                    <tbody>
                                                        <tr>
                                                            <td>
                                                                <h2 ng-bind-html='toTrustedHTML(BannerData.BlogDescription)'></h2></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="cropArea" style="width: 800px;height: 400px; margin:10px 0px;" ng-show="myImageBanner">
                                            <img-crop image="myImageBanner" area-type="rectangle" aspect-ratio="4" result-image="myCroppedImageBanner" result-image-size='{w: 1600,h: 400}' area-min-size='{w: 400,h: 100}'></img-crop>
                                        </div>
                                        <div style="display:none">Cropped Image:</div>
                                        <div class="banner-preview">
                                            <img id="CroppedImgData" ng-src="{{myCroppedImageBanner}}" style="max-width:100%;" />
                                            <div class="banner-caption">
                                                <table class="banner-container">
                                                    <tbody>
                                                        <tr>
                                                            <td>
                                                                <h2 ng-bind-html='toTrustedHTML(BannerData.BlogDescription)'></h2></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="error-holder"><span></span></div>
                                    </div>
                                </div>
                                <div class="form-group" ng-if="BannerData.BlogUniqueID == 'home_page_sidebar1'" ng-init="initializeCropper();">
                                    <label class="label" for="severName">Upload Image </label>
                                    <div class="p-v-sm"><small>Note : For best result please upload image size 300 X 600</small></div>
                                    <div class="browse-image row" data-type="focus">
                                        <div class="col-sm-1">
                                            <div class="support-search-new btn btn-primary relative">
                                                <input type="file" id="fileInputBanner" value="Browse">
                                                <label class="label-white">Browse</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-2">
                                            <input type="text" class="form-control">
                                        </div>
                                    </div>
                                    <div style="display: none" class="error-holder" id="ErrorValideImage">
                                        <label class="errorbox">Please upload only jpg or png file</label>
                                    </div>
                                    <div class="form-group" style="margin: 10px 0px;">
                                        <input type="hidden" id="BannerSize" ng-init="BannerData.BannerSize = '300x600'" ng-model="BannerData.BannerSize">
                                        <img ng-hide="myImageBanner" ng-if="BannerData.BlogImage != ''" ng-src="<?php echo IMAGE_SERVER_PATH . 'upload/banner/' ?>{{BannerData.BlogImage}}" style="max-width: 100%;" />
                                        <div class="cropArea" style="width: 350px;height: 700px; margin:10px 0px;" ng-show="myImageBanner">
                                            <img-crop image="myImageBanner" area-type="rectangle" aspect-ratio="0.5" result-image="myCroppedImageBanner" result-image-size='{w: 300,h: 600}' area-min-size='{w: 100, h: 200}'></img-crop>
                                        </div>
                                        <div style="display:none">Cropped Image:</div>
                                        <div class="banner-preview">
                                            <img id="CroppedImgData" ng-src="{{myCroppedImageBanner}}" style="max-width:100%;" />
                                            <div class="banner-caption">
                                                <table class="banner-container">
                                                    <tbody>
                                                        <tr>
                                                            <td>
                                                                <h2 ng-bind-html='toTrustedHTML(BannerData.BlogDescription)'></h2></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="error-holder"><span></span></div>
                                    </div>
                                </div>
                            </div>
                          </div>
                        </div>


                        <div class="row" ng-if="BannerData.BlogUniqueID == 'home_page_carousel' || BannerData.BlogUniqueID == 'race_event_carousel'">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label class="label" for="BlogDescription">Overlay Text</label>
                                    <span class="help-text"><small>Note : Use Italic to have display overlay text in lowercase.</small></span>
                                    <textarea data-summernote="" id="PostContent0" name="PostContent0" data-ng-model="BannerData.BlogDescription"></textarea> 
                                    <div class="error-holder"><span>Error</span></div>
                                </div> 
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="label" for="URL">Url</label>
                                    <input type="text" class="form-control" ng-model='BannerData.URL' name="URL" id="URL" data-msglocation="errorURL" data-mandatory="" data-controltype="validurl" data-requiredmessage="Please enter url">
                                    <div class="error-holder" id="errorURL"><span></span></div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" data-ng-model="BannerData.Duration">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="label" for="BannerStartDate">Start Date <span>*</span></label>
                                    <input type="text" class="form-control" id="BannerStartDate" ng-model="BannerData.StartDate" value="{{BannerData.StartDate}}" data-msglocation="errorBannerStartDate" data-mandatory="true" data-controltype="" data-requiredmessage="Please enter start date">
                                    <div class="error-holder" id="errorBannerStartDate"><span>Error</span></div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="label" for="BannerEndDate">End Date <span>*</span></label>
                                    <input type="text" class="form-control" id="BannerEndDate" ng-model="BannerData.EndDate" value="{{BannerData.ValidTill}}" data-msglocation="errorBannerEndDate" data-mandatory="true" data-controltype="" data-requiredmessage="Please enter end date">
                                    <div class="error-holder" id="errorBannerEndDate"><span>Error</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="label" for="SourceScript">Source Script</label>
                                    <textarea class="message-sm textarea form-control" id="SourceScript" name="SourceScript" placeholder="" class="message textarea" rows="4" data-ng-model="BannerData.SourceScript"></textarea>
                                    <div class="error-holder"><span>Error</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="label" for="AdvertiserContact">Advertiser Contact</label>
                                    <input data-req-maxlen="15" maxlength="15" data-msglocation="errorContact" class="form-control" data-controltype="phonenumber" data-requiredmessage="Invalid contact no." type="text" ng-model='BannerData.AdvertiserContact' name="AdvertiserContact" id="AdvertiserContact">
                                    <div class="error-holder" id="errorContact"><span>Error</span></div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="label" for="title">No. of Hits</label>
                                    <div class="" data-type="focus">
                                        <label class="label">{{BannerData.NoOfHits}}</label>
                                    </div>
                                    <div class="error-holder"><span>Error</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="row">
                                    <div class="col-sm-8">
                                        <table class="add-multiple-location">
                                            <tr data-ng-repeat="location in BannerData.Locations">
                                                <td class="valign">
                                                    <label class="label" data-ng-show="$index == 0">Location </label>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control" data-ng-model='location.address' name="RedirectLink" id="RedirectLink" value="" ng-autocomplete="" placeholder="">
                                                    <div class="error-holder usrerror" data-ng-bind="ErrorLocation[$index]"></div>
                                                </td>
                                                <td class="valign">
                                                    <button data-ng-click="add_location();" data-ng-show="$index == (BannerData.Locations.length - 1)" type="button" class="btn btn-primay btn-md">+</button>
                                                    <button data-ng-click="remove_location($index);" data-ng-show="$index < (BannerData.Locations.length - 1)" type="button" class="btn btn-primay btn-md">x</button>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <div class="btn-toolbar btn-toolbar-right">
                            <button type="button" class="btn btn-default" onclick="window.location = '<?php echo site_url('admin/advertise/banner'); ?>';">CANCEL</button>
                            <button type="submit" class="btn btn-primay">SAVE<span></span></button>
                        </div>
                    </div>
                </form>
            </div>
        </aside>
    </div>
</section>
