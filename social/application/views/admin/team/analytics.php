<div class="clearfix"></div>
    
    <div class="row-flued" ng-controller="SongAnalyticCtrl" id="SongAnalyticCtrl">
     <div ng-init="list_song_analytic();get_sources();get_song_summery();">
     <h2>Song of the day analytics</h2>   
     <div class="info-row-right rightdivbox">
     <a ng-click="Summary();" onclick="$('#Summary').slideDown();" id="SummaryOpen" class="button float-right marl10" href="javascript:void(0);">Summary</a>
       <div class="right-filter">
       	 <label class="label">Source</label>
         <select 
         chosen 
         data-disable-search="true" 
         name="analyticProviders" 
         id="" 
         data-ng-model="Source"
         data-placeholder="Scholly me"
         ng-options="source.SourceGUID as source.SourceName for source in sources"
         ng-change= "search_source();"
         >
          <option value=""></option>
      </select>
        
      </div>
     </div>
     <div class="clearfix"></div>       
       <div style="display:none" id="Summary" class="row-flued">
      <div class="add-category">
        <div class="communicate-region">
            <h3 class="song-title">Song "Buy" summary</h3>
            <section style="clear: both;">
                <ul>
                  <li class="ng-scope">
                     <div class="communicate-content">
                       <p class="ng-binding pull-left">SchollyMe</p>
                       <span class="pull-right" ng-if="song_summery.BUY[0].SchollyMe.ActionCount!=undefined" ng-bind="song_summery.BUY[0].SchollyMe.ActionCount">0</span>
                       <span class="pull-right" ng-if="song_summery.BUY[0].SchollyMe.ActionCount==undefined">0</span>
                     </div>
                  </li>
                  <li class="ng-scope">
                     <div class="communicate-content">
                       <p class="ng-binding pull-left">Spotify</p>
                       <span class="pull-right" ng-if="song_summery.BUY[0].Spotify.ActionCount!=undefined" ng-bind="song_summery.BUY[0].Spotify.ActionCount">0</span>
                       <span class="pull-right" ng-if="song_summery.BUY[0].Spotify.ActionCount==undefined">0</span>
                     </div>
                 </li>
                 <li class="ng-scope">
                   <div class="communicate-content">
                     <p class="ng-binding pull-left">iTunes</p>
                     <span class="pull-right" ng-if="song_summery.BUY[0].iTunes.ActionCount!=undefined"  ng-bind="song_summery.BUY[0].iTunes.ActionCount">0</span>
                     <span class="pull-right" ng-if="song_summery.BUY[0].iTunes.ActionCount==undefined">0</span>
                  </div>
                </li>
              </ul>
            </section>
             <h3 class="song-title">Song "Play" summary</h3>
            <section style="clear: both;">
                <ul>
                  <li class="ng-scope">
                     <div class="communicate-content">
                       <p class="ng-binding pull-left">SchollyMe</p>
                       <span class="pull-right" ng-if="song_summery.PLAY[0].SchollyMe.ActionCount!=undefined" ng-bind="song_summery.PLAY[0].SchollyMe.ActionCount">0</span>
                       <span class="pull-right" ng-if="song_summery.PLAY[0].SchollyMe.ActionCount==undefined">0</span>
                     </div>
                  </li>
                  <li class="ng-scope">
                     <div class="communicate-content">
                       <p class="ng-binding pull-left">Spotify</p>
                       <span class="pull-right" ng-if="song_summery.PLAY[0].Spotify.ActionCount!=undefined" ng-bind="song_summery.PLAY[0].Spotify.ActionCount">0</span>
                       <span class="pull-right" ng-if="song_summery.PLAY[0].Spotify.ActionCount==undefined" >0</span>
                     </div>
                 </li>
                 <li class="ng-scope">
                   <div class="communicate-content">
                     <p class="ng-binding pull-left">iTunes</p>
                     <span class="pull-right" ng-if="song_summery.PLAY[0].iTunes.ActionCount!=undefined" ng-bind="song_summery.PLAY[0].iTunes.ActionCount">0</span>
                     <span class="pull-right" ng-if="song_summery.PLAY[0].iTunes.ActionCount==undefined">0</span>
                  </div>
                </li>
              </ul>
            </section>
            <a onclick="$('#Summary').slideUp();" id="summaryClose" class="button float-right m-r10" href="javascript:void(0);">Close</a>
         </div>
        <div class="clearfix"></div>
      </div>
    </div>
    <div >
        <div data-pagination="" data-total-items="totalRecord" data-num-per-page="numPerPage" data-num-pages="numPages()" data-current-page="currentPage" data-max-size="maxSize" data-boundary-links="true" class="simple-pagination"></div>
        <div class="box"><table class="users-table registered-user" id="userlist_table">
            <tbody>
            <tr>
                <th id="UName" class="ui-sort selected" ng-click="orderByField = 'SourceName'; reverseSort = !reverseSort; sortBY('UName')">                           
                    <div class="shortdiv sortedDown">Source<span class="icon-arrowshort">&nbsp;</span></div>
                </th>
                <th id="UEmail" class="ui-sort" ng-click="orderByField = 'FullName'; reverseSort = !reverseSort; sortBY('UEmail')">                           
                    <div class="shortdiv sortedDown">User<span class="icon-arrowshort hide">&nbsp;</span></div>
                </th>
                <th id="ContactName" class="ui-sort" ng-click="orderByField = 'Title'; reverseSort = !reverseSort; sortBY('ContactName')">                           
                    <div class="shortdiv sortedDown">Song<span class="icon-arrowshort hide">&nbsp;</span></div>
                </th>
                <th id="PhoneNumber" class="ui-sort" >                           
                    <div class="shortdiv sortedDown">Activity<span >&nbsp;</span></div>
                </th>
                 <th id="Position" class="ui-sort" ng-click="orderByField = 'Count'; reverseSort = !reverseSort; sortBY('Position')">                           
                    <div class="shortdiv sortedDown">Count<span class="icon-arrowshort hide">&nbsp;</span></div>
                </th>
                <th id="CreatedDate" class="ui-sort" ng-click="orderByField = 'ModifiedDate'; reverseSort = !reverseSort; sortBY('CreatedDate')">
                    <div class="shortdiv">Date<span class="icon-arrowshort hide">&nbsp;</span></div>
                </th>

            </tr>
			<tr class="rowtr" ng-repeat="list in listData" ng-if="listData.length>0">
                <td ng-bind="list.SourceName"></td>
                <td ng-bind="list.FullName"></td>
                <td ng-bind="list.Title"></td>
                <td ng-bind="list.Action"></td>
                <td ng-bind="list.Count"></td>
                <td ng-bind="list.ModifiedDate"></td>
            </tr>
            </tbody>
        </table></div>
        <!--Actions Dropdown menu-->
        <ul class="action-dropdown userActiondropdown" style="left: 1191.5px; top: 297px; display: none;">
            <li><a href="javascript:void(0);">Edit</a></li>   
            <li><a href="javascript:void(0);">Delete</a></li>
            <li><a href="javascript:void(0);">Set song of the day</a></li>
        </ul>
        <!--/Actions Dropdown menu-->
    </div>

    <span id="result_message" class="result_message"><?php echo lang("no_record"); ?></span>
    </div>
    </div>
    <div class="clearfix"></div>