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
    <div class="container" ng-controller="AdvertiseCtrl" id="ArticleListCtrl" ng-init="getDefaultBannerDetails();" ng-cloak>
        <aside class="content-wrapper">
            <!--Info row-->
            <div class="info-row row-flued">
                <h2>Set Default Ad Image</h2>
                <div class="info-row-right">
                    <a class="btn-link" href="<?php echo site_url('admin/advertise/banner'); ?>"><span>Back</span></a>
                </div>
            </div>
            <div class="panel">
                <form method="post" name="formBanner" id="formBanner" ng-submit="SaveDefaultBanner();" autocomplete="off">
                    <div class="panel-body"> 
                            <div class="row">
                                <div class="col-sm-4">
                                    <h2>300 X 250 Ad </h2>
                                    <div class="error-holder"><span></span></div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group" ng-init="initializeCropper();">
                                        <label class="label" for="severName">Upload Image </label>
                                        <div class="p-v-sm"><small>Note : For best result please upload image size 300 X 250</small></div>
                                        <div class="browse-image row" data-type="focus">
                                            <div class="col-sm-1">
                                                <div class="support-search-new button relative">
                                                    <input type="file" id="fileInputBanner" value="Browse">
                                                    <label class="label-white">Browse</label>
                                                </div>
                                            </div>
                                            <div class="col-sm-2"><input type="text" class="form-control"></div>    
                                        </div>
                                        <div style="display: none" class="error-holder" id="ErrorValideImage">
                                            <label class="errorbox">Please upload only jpg or png file</label>
                                        </div> 

                                        <div class="form-group" style="margin: 10px 0px;">
                                            <input type="hidden" id="BannerSize" ng-init="DefaultBannerData.BannerSize = '300x250'" ng-model="DefaultBannerData.BannerSize">
                                            <img ng-hide="myImageBanner" ng-if="DefaultBannerData.BlogImage != ''" ng-src="<?php echo IMAGE_SERVER_PATH . 'upload/banner/' ?>{{DefaultBannerData.BlogImage}}" style="max-width: 100%;" />
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
                                </div> 
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="label" for="URL">Url</label>
                                        <input type="text" class="form-control" ng-model='DefaultBannerData.URL' name="URL" id="URL" data-msglocation="errorURL" data-mandatory="" data-controltype="validurl" data-requiredmessage="Please enter url">
                                        <div class="error-holder" id="errorURL"><span></span></div>
                                    </div> 
                               </div> 
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                <div class="form-group">
                                    <label class="label" for="SourceScript">Source Script</label>
                                    <div class="text-field" data-type="focus">
                                        <textarea class="message-sm textarea" id="SourceScript" name="SourceScript" placeholder="" class="message textarea" rows="4" data-ng-model="DefaultBannerData.SourceScript"></textarea>
                                    </div>
                                    <div class="error-holder"><span>Error</span></div>
                                </div> 
                                </div>

                            </div> 
                            <div class="row">
                                <div class="col-sm-6">
                                <div class="form-group">
                                    <h2>300 X 600 Ad</h2>
                                    <div class="error-holder"><span></span></div>
                                </div> 
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label class="label" for="severName">Upload Image </label>
                                        <div class="p-v-sm"><small>Note : For best result please upload image size 300 X 600</small></div>
                                        <div class="browse-image row" data-type="focus">
                                            <div class="col-sm-1">
                                                <div class="support-search-new button relative">
                                                    <input type="file" id="fileInputBanner2" value="Browse">
                                                    <label class="label-white">Browse</label>
                                                </div>
                                            </div>
                                            <div class="col-sm-2"><input type="text" class="form-control"></div>
                                        </div>
                                        <div style="display: none" class="error-holder" id="ErrorValideImage2">
                                            <label class="errorbox">Please upload only jpg or png file</label>
                                        </div> 
                                        <div class="form-group" style="margin: 10px 0px;">
                                            <input type="hidden" id="BannerSize2" ng-init="DefaultBannerData2.BannerSize = '300x600'" ng-model="DefaultBannerData2.BannerSize">
                                            <img ng-hide="myImageBanner2" ng-if="DefaultBannerData2.BlogImage != ''" ng-src="<?php echo IMAGE_SERVER_PATH . 'upload/banner/' ?>{{DefaultBannerData2.BlogImage}}" style="max-width: 100%;" />
                                            <div class="cropArea" style="width: 400px;height: 400px; margin:10px 0px;" ng-show="myImageBanner2">
                                                <img-crop image="myImageBanner2" area-type="rectangle" aspect-ratio="0.5" result-image="myCroppedImageBanner2" result-image-size='{w: 300,h: 600}' area-min-size='{w: 100, h: 200}'></img-crop>
                                            </div>
                                            <div style="display:none">Cropped Image:</div>
                                            <div>
                                                <img id="CroppedImgData2" ng-src="{{myCroppedImageBanner2}}" style="max-width: 100%;" />
                                                <div class="error-holder"><span></span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div> 
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="label" for="URL1">Url</label>
                                        <div class="text-field" data-type="focus">
                                            <input type="text" ng-model='DefaultBannerData2.URL' name="URL2" id="URL2" data-msglocation="errorURL2" data-mandatory="" data-controltype="validurl" data-requiredmessage="Please enter url">
                                        </div>
                                        <div class="error-holder" id="errorURL2"><span></span></div>
                                    </div> 
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label class="label" for="SourceScript">Source Script</label>
                                        <div class="text-field" data-type="focus">
                                            <textarea class="message-sm textarea" id="SourceScript2" name="SourceScript" placeholder="" class="message textarea" rows="4" data-ng-model="DefaultBannerData2.SourceScript"></textarea>
                                        </div>
                                        <div class="error-holder"><span>Error</span></div>
                                    </div> 
                                </div>
                            </div> 
                    </div>
                    <div class="panel-footer">
                        <div class="btn-toolbar btn-toolbar-right">
                            <button type="button" class="btn btn-default" onclick="window.location = '<?php echo site_url('admin/advertise/banner'); ?>';">CANCEL</button>
                            <button type="submit" class="btn btn-primay">SAVE</button>
                        </div>
                    </div>
                </form>
            </div>
        </aside>
    </div>
</section>
