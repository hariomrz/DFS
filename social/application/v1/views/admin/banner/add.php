<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><a>Banner</a></li>
                    <li>/</li>
                    <li><span>Add</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<section class="main-container">
    <div class="container" ng-controller="AdvertiseCtrl" id="ArticleListCtrl"  ng-cloak>
        <aside class="content-wrapper">
            <!--Info row-->
            <div class="info-row row-flued">
                <h2>
                    <?php
                    echo 'ADD Banner';
                    ?>
                </h2>
                <div class="info-row-right">
                    <a class="btn-link" href="<?php echo site_url('admin/banner'); ?>"><span>Back</span></a>
                </div>
            </div>
            <div class="panel">
                <form method="post" name="form_banner" id="form_banner" ng-submit="save_banner();" autocomplete="off">
                    <div class="panel-body"> 
                        <div class="row">
                            <div class="col-sm-12">
                                <div >
                                    <div class="form-group" ng-init="initializeCropper();">
                                        <label class="label" for="severName">Upload Image </label>
                                        <div class="p-v-sm"><small>Note : For best result please upload image size 600 X 112</small></div>
                                        <div class="browse-image row" data-type="focus">
                                            <div class="col-sm-3">
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
                                            <input type="hidden" id="BannerSize" ng-init="BannerData.BannerSize = '600x112'" ng-model="BannerData.BannerSize">
                                            <div class="cropArea" style="width: 600px;height: 90px; margin:10px 0px;" ng-show="myImageBanner">
                                                <img-crop image="myImageBanner" area-type="rectangle" aspect-ratio="5.4" result-image="myCroppedImageBanner" result-image-size='{w: 600,h: 112}' area-min-size='{w: 240,h: 45}'></img-crop>
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
                        </div>

                    </div>
                    <div class="panel-footer">
                        <div class="btn-toolbar btn-toolbar-right">
                            <button type="button" class="btn btn-default" onclick="window.location = '<?php echo site_url('admin/banner'); ?>';">CANCEL</button>
                            <button type="submit" class="btn btn-primay">SAVE<span></span></button>
                        </div>
                    </div>
                </form>
            </div>
        </aside>
    </div>
</section>
