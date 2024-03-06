<div class="clearfix"></div>
    <div class="row-flued" ng-controller="SongSettingCtrl" id="SongSettingCtrl">
     <h2>Set Song of the day</h2>   
            <form method="post" name="frmsmtp" id="smtp_form" autocomplete="off">
                <div class="info-row row-flued">
                
                    <div class="form_analytic_tools">
                    <table class="addcategory-table rolestable">
                    <tr><td class="valign" ng-init="get_sources();init_uploader();">
                        <label class="label">Source</label>  
                        </td><td>
                        <select 
                        class="width100" 
                        chosen data-disable-search="true" 
                        name="analyticProviders" 
                        id="Source"  
                        data-placeholder="Scholly me"
                        data-ng-model="Source"
                        ng-options="source.SourceGUID as source.SourceName for source in sources"
                        >
                        <option value=""></option>
                        </select>
                        <div class="errordiv">
                            <div class="error-holder">{{errorMessage}}</div>
                        </div></td></tr></table>
                        <div class="Schollyme box">
                           <table class="addcategory-table rolestable">
                            <tr>
                                <td class="valign"><label class="label">Song Name</label></td>
                                <td>
                                    <div>
                                        <div class="text-field large" data-type="focus">
                                            <input data-ng-model="Schollyme.Title" type="text" name="SongName" id="Schollyme_SongName" value="">
                                        </div>
                                        <div class="clearfix">&nbsp;</div>
                                        <div class="error-holder usrerror" ng-bind="Error.error_Schollyme_SongName"></div>
                                        <div class="clearfix">&nbsp;</div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="valign"><label class="label">Artist Name</label></td>
                                <td>
                                    <div>
                                        <div class="text-field large" data-type="focus">
                                            <input data-ng-model="Schollyme.ArtistName" type="text" name="ArtistName" id="Schollyme_ArtistName" value="">
                                        </div>
                                        <div class="clearfix">&nbsp;</div>
                                        <div class="error-holder usrerror" ng-bind="Error.error_Schollyme_ArtistName"></div>
                                        <div class="clearfix">&nbsp;</div>
                                    </div>
                                </td>
                            </tr> 
                            <tr>
                                <td class="valign"><label class="label">Album Name</label></td>
                                <td>
                                    <div>
                                        <div class="text-field large" data-type="focus">
                                            <input data-ng-model="Schollyme.AlbumName" type="text" name="AlbumName" id="Schollyme_AlbumName" value="">
                                        </div>
                                        <div class="clearfix">&nbsp;</div>
                                        <div class="error-holder usrerror" ng-bind="Error.error_Schollyme_AlbumName"></div>
                                        <div class="clearfix">&nbsp;</div>
                                    </div>
                                </td>
                            </tr>	
                            <tr>
                              <td class="valign">
                                <label class="label">Thumbnail</label>
                                
                              </td>
                              <td>
                                 <div>
                                   <div><span class="button pull-left w55">
                                   <div class="uplaodLoader hide" id="ImageThumbLoader"><img id="spinner" src="<?php echo base_url();?>assets/admin/img/loading22.gif"></div>
                                     <div data-ng-model="Schollyme.ImageURL" id="Schollyme_Thumbnail">Upload</div>
                                     <!-- <span class="max-size">Max size allowed</span></span> -->
                                   </div>
                                   <figure class="set-thumbnail hide" id="scholly_song_thumb_container"><img id="scholly_song_img" src="" ></figure>
                                        <div class="clearfix">&nbsp;</div>
                                        <div class="error-holder usrerror" id='scholly_song_thumb_err' ng-bind="Error.error_Schollyme_ImageURL"></div>
                                        <div class="clearfix">&nbsp;</div>
                                 </div>
                              </td>
                            </tr>
                            <tr>
                              <td class="valign">
                               
                                <label class="label">Upload Song</label>
                              </td>
                              <td>
                                 <div>
                                   <div><span class="button pull-left w55">
                                   <div class="uplaodLoader hide" id="songUploadLoader"><img id="spinner" src="<?php echo base_url();?>assets/admin/img/loading22.gif"></div>
                                     <div data-ng-model="Schollyme.SongURL"  id="Schollyme_Song">Upload</div>
                                     <!-- <span class="max-size">Max size allowed</span></span> -->
                                   </div>
                                   <div id='song_url' class="hide"></div>

                                        <div class="clearfix">&nbsp;</div>
                                        <div class="error-holder usrerror" id="scholly_song_err" ng-bind="Error.error_Schollyme_SongURL"></div>
                                        <div class="clearfix">&nbsp;</div>
                                 </div>
                              </td>
                            </tr>           
         			   </table>
                        </div>
                        <div class="Spotify box">
                           <table class="addcategory-table rolestable">
                            <tr>
                                <td class="valign"><label class="label">Song Name</label></td>
                                <td>
                                    <div>
                                        <div class="large" data-type="focus">
                                            <input type="text" class="form-control" data-ng-model="Spotify.Title" name="SongName" id="Spotify_SongName" value="">
                                        </div>
                                        <div class="clearfix">&nbsp;</div>
                                        <div class="error-holder usrerror" ng-bind="Error.error_Spotify_SongName"></div>
                                        <div class="clearfix">&nbsp;</div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="valign"><label class="label">Artist Name</label></td>
                                <td>
                                    <div>
                                        <div class="text-field large" data-type="focus">
                                            <input  data-ng-model="Spotify.ArtistName" type="text" name="ArtistName" id="Spotify_ArtistName" value="">
                                        </div>
                                        <div class="clearfix">&nbsp;</div>
                                        <div class="error-holder usrerror" ng-bind="Error.error_Spotify_ArtistName"></div>
                                        <div class="clearfix">&nbsp;</div>
                                    </div>
                                </td>
                            </tr> 
                            <tr>
                                <td class="valign"><label class="label">Album Name</label></td>
                                <td>
                                    <div>
                                        <div class="text-field large" data-type="focus">
                                            <input data-ng-model="Spotify.AlbumName" type="text" name="AlbumName" id="Spotify_AlbumName" value="">
                                        </div>
                                        <div class="clearfix">&nbsp;</div>
                                        <div class="error-holder usrerror" ng-bind="Error.error_Spotify_AlbumName"></div>
                                        <div class="clearfix">&nbsp;</div>
                                    </div>
                                </td>
                            </tr>	
                            <tr>
                              <td class="valign"><label class="label">Image URL</label></td>
                              <td>
                                 <div>
                                   <div class="large">
                                     <input type="text" class="form-control" data-ng-model="Spotify.ImageURL" name="thunbnail" id="Spotify_Thumbnail">
                                   </div>
                                        <div class="clearfix">&nbsp;</div>
                                        <div class="error-holder usrerror" ng-bind="Error.error_Spotify_Thumbnail"></div>
                                        <div class="clearfix">&nbsp;</div>
                                 </div>
                              </td>
                            </tr>
                            <tr>
                                <td class="valign"><label class="label">Song Unique Id</label></td>
                                <td>
                                    <div>
                                        <div class="large" data-type="focus">
                                            <input type="text" class="form-control" data-ng-model="Spotify.SourceSongID" name="UniqueId" id="Spotify_UniqueId" value="">
                                        </div>
                                        <div class="clearfix">&nbsp;</div>
                                        <div class="error-holder usrerror" ng-bind="Error.error_Spotify_UniqueId"></div>
                                        <div class="clearfix">&nbsp;</div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="valign"><label class="label">Song Preview URL</label></td>
                                <td>
                                    <div>
                                        <div class="large" data-type="focus">
                                            <input type="text" class="form-control" data-ng-model="Spotify.SongPreviewURL" name="SongURL" id="Spotify_SongPreviewURL" value="">
                                        </div>
                                        <div class="clearfix">&nbsp;</div>
                                        <div class="error-holder usrerror" ng-bind="Error.error_Spotify_SongPreviewURL"></div>
                                        <div class="clearfix">&nbsp;</div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="valign"><label class="label">Song URL</label></td>
                                <td>
                                    <div>
                                        <div class="large" data-type="focus">
                                            <input type="text" class="form-control" data-ng-model="Spotify.SongURL" name="SongURL" id="Spotify_SongURL" value="">
                                        </div>
                                        <div class="clearfix">&nbsp;</div>
                                        <div class="error-holder usrerror" ng-bind="Error.error_Spotify_SongURL"></div>
                                        <div class="clearfix">&nbsp;</div>
                                    </div>
                                </td>
                            </tr>  
                                     
         			   </table>
                      </div>
                        <div class="itunes box">
                           <table class="addcategory-table rolestable">
                            <tr>
                                <td class="valign"><label class="label">Song Name</label></td>
                                <td>
                                    <div>
                                        <div class="large" data-type="focus">
                                            <input type="text" class="form-control" data-ng-model="iTunes.Title" name="SongName" id="iTunes_SongName" value="">
                                        </div>
                                        <div class="clearfix">&nbsp;</div>
                                        <div class="error-holder usrerror" ng-bind="Error.error_iTunes_SongName"></div>
                                        <div class="clearfix">&nbsp;</div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="valign"><label class="label">Artist Name</label></td>
                                <td>
                                    <div>
                                        <div class="large" data-type="focus">
                                            <input type="text" class="form-control" data-ng-model="iTunes.ArtistName" name="ArtistName" id="iTunes_ArtistName" value="">
                                        </div>
                                        <div class="clearfix">&nbsp;</div>
                                        <div class="error-holder usrerror" ng-bind="Error.error_iTunes_ArtistName"></div>
                                        <div class="clearfix">&nbsp;</div>
                                    </div>
                                </td>
                            </tr> 
                            <tr>
                                <td class="valign"><label class="label">Album Name</label></td>
                                <td>
                                    <div>
                                        <div class="large" data-type="focus">
                                            <input type="text" class="form-control" data-ng-model="iTunes.AlbumName" name="AlbumName" id="iTunes_AlbumName" value="">
                                        </div>
                                        <div class="clearfix">&nbsp;</div>
                                        <div class="error-holder usrerror" ng-bind="Error.error_iTunes_AlbumName"></div>
                                        <div class="clearfix">&nbsp;</div>
                                    </div>
                                </td>
                            </tr>	
                            <tr>
                              <td class="valign"><label class="label">Image URL</label></td>
                              <td>
                                 <div>
                                   <div class="large">
                                     <input class="form-control" data-ng-model="iTunes.ImageURL" type="text" name="thunbnail" id="iTunes_Thumbnail">
                                   </div>
                                        <div class="clearfix">&nbsp;</div>
                                        <div class="error-holder usrerror" ng-bind="Error.error_iTunes_Thumbnail"></div>
                                        <div class="clearfix">&nbsp;</div>
                                 </div>
                              </td>
                            </tr>
                            <tr>
                                <td class="valign"><label class="label">Song Unique Id</label></td>
                                <td>
                                    <div>
                                        <div class="large" data-type="focus">
                                            <input type="text" class="form-control" name="UniqueId" data-ng-model="iTunes.SourceSongID" id="iTunes_UniqueId" value="">
                                        </div>
                                        <div class="clearfix">&nbsp;</div>
                                        <div class="error-holder usrerror" ng-bind="Error.error_iTunes_UniqueId"></div>
                                        <div class="clearfix">&nbsp;</div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="valign"><label class="label">Song Preview URL</label></td>
                                <td>
                                    <div>
                                        <div class="large" data-type="focus">
                                            <input type="text" class="form-control" name="SongURL" data-ng-model="iTunes.SongPreviewURL"  id="iTunes_SongPreviewURL" value="">
                                        </div>
                                        <div class="clearfix">&nbsp;</div>
                                        <div class="error-holder usrerror" ng-bind="Error.error_iTunes_SongPreviewURL"></div>
                                        <div class="clearfix">&nbsp;</div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="valign"><label class="label">Song URL</label></td>
                                <td>
                                    <div>
                                        <div class="large" data-type="focus">
                                            <input type="text" class="form-control" name="SongURL" data-ng-model="iTunes.SongURL" id="iTunes_SongURL" value="">
                                        </div>
                                        <div class="clearfix">&nbsp;</div>
                                        <div class="error-holder usrerror" ng-bind="Error.error_iTunes_SongURL"></div>
                                        <div class="clearfix">&nbsp;</div>
                                    </div>
                                </td>
                            </tr>  
                                     
         			   </table>
                      </div>
                        <button id="btnSave" type="submit" class="button float-right" onclick="openPopDiv('Setsong_popup', 'bounceInDown');">Set Song Of The Day</button>
                  </div>
                </div>
            </form>
            <div class="popup confirme-popup animated" id="Setsong_popup">
                <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onClick="closePopDiv('Setsong_popup', 'bounceOutUp');">&nbsp;</i></div>
                <div class="popup-content">
                    <p>Are you sure you want to set this on all users profile.</p>
                    <div class="communicate-footer text-center">
                        <button class="button wht" onClick="closePopDiv('Setsong_popup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                        <button class="button" ng-click="save_song();" id="button_on_delete" name="button_on_delete">
                            <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
  

    