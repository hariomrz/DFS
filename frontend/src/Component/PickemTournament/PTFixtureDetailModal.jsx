import React from 'react';
import { Modal, Panel, OverlayTrigger, Tooltip } from 'react-bootstrap';
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities, _Map, addOrdinalSuffix, _isEmpty, _isNull, _isUndefined } from '../../Utilities/Utilities';
import { pickemTeamHistory } from '../../WSHelper/WSCallings';
import * as WSC from "../../WSHelper/WSConstants";
import {DARK_THEME_ENABLE} from '../../helper/Constants';
import { MomentDateComponent, NoDataView } from '../CustomComponent';
import FieldView from "../../views/FieldView";
import * as AppLabels from "../../helper/AppLabels";
import Images from '../../components/images';
import { isDateTimePast } from '../../Utilities/Utilities';
import InfiniteScroll from 'react-infinite-scroll-component';
import FieldViewRight from '../../views/FieldViewRight';
import { SportsIDs } from "../../JsonFiles";
import util from 'util';
import { CommonLabels } from '../../helper/AppLabels';
import ls from "local-storage";
// import HelperFunction from '../../../../admin/src/helper/HelperFunction';

// import {HF} from '../../helper/HelperFunction';




export default class PTFixtureDetailModal extends React.Component {
   constructor(props, context) {
      super(props, context);
      this.state = {
         ptTeamHistory: [],
         matchDetails: [],
         user_data: ''
      };

   }

   componentDidMount() {
      this.callPTTeamHistory()
   }

   callPTTeamHistory = async () => {
      const { activeUserDetail } = this.props
      this.setState({ isLoaderShow: true })
      let param = {
         "user_tournament_id": activeUserDetail.user_tournament_id
      }
      let apiResponse = await pickemTeamHistory(param)
      if (apiResponse) {
         let data = apiResponse.data
         this.setState({
            ptTeamHistory: data,
            matchDetails: data.match,
            user_data: data.user_data
         })
      }
   }

   render() {

      const { show, hide, details } = this.props;
      const { ptTeamHistory, matchDetails, user_data } = this.state;
      let winGoal = Utilities.getMasterData().pickem_win_goal;
      let winGoalDiff = Utilities.getMasterData().pickem_win_goal_diff;
      let winOnly = Utilities.getMasterData().pickem_win_only;

      let AppSelectedSport = ls.get("selectedSports");

      return (
         <MyContext.Consumer>
            {(context) => (
               <Modal
                  show={show}
                  dialogClassName="custom-modal tour-fix-detail-modal"
                  className="center-modal"
                  backdropClassName='tour-fix-detail-modal-backdrop'
               >
                  <Modal.Header >
                     <div className='usr-nm'> {ptTeamHistory.tournament_name} </div>
                     <div className="match-count">
                        <MomentDateComponent data={{ date: ptTeamHistory.start_date, format: "DD MMM" }} />
                        -
                        <MomentDateComponent data={{ date: ptTeamHistory.end_date, format: "DD MMM" }} />
                     </div>

                     <span onClick={hide} className="mdl-close new-tour-close">
                        <i className="icon-left-arrow"></i>
                     </span>





                     <div className="center-container tour-hdr mdl-close">
                        <div className="cont-date tour-new-pp">
                           {
                              ptTeamHistory.status == 3 || ptTeamHistory.status == 2 ?
                                 <span className="comp-sec">{AppLabels.COMPLETED}</span>
                                 :
                                 <>
                                    {
                                       // Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') < Utilities.getFormatedDateTime(Utilities.getUtcToLocal(ptTeamHistory.start_date), 'YYYY-MM-DD HH:mm ')
                                       isDateTimePast(ptTeamHistory.start_date)
                                       &&
                                       <span className="live-sec"><span></span> {AppLabels.LIVE}</span>

                                    }
                                 </>
                           }
                        </div>
                     </div>



                  </Modal.Header>
                  <Modal.Body className='newLB-body pickLB-body'>
                     <div className="fix-list-wrap newLB">
                        <div className='TourL-profile'>
                           <div className='img-block'>
                              <img src={user_data.image ? Utilities.getThumbURL(user_data.image) : Images.DEFAULT_AVATAR} className='user-profile-image' alt="" />
                           </div>
                           <div className='detail-block'>
                              <h5 className='name'>{user_data.user_name}</h5>
                              <h6 className='match-joined'>{AppLabels.MATCH_JOINED} <span className='number'>{ptTeamHistory.match && ptTeamHistory.match.length >= 0 ? ptTeamHistory.match.length : ''}</span></h6>
                           </div>
                        </div>
                     </div>

                     <div className='pick-fix-card-outer'>
                        {matchDetails && matchDetails.length > 0 && matchDetails.map((item, idx) => {
                           let score_data = JSON.parse(item.score_data)
                           return (
                              <>
                                 {item.status == 4 ? <div className="countdown-timer-section text-uppercase">
                                    {AL.MATCH_CANCELLED}
                                 </div> :

                                    <div className='pick-countdown-tview' key={idx}>
                                       <div className='pick-com-timer'>
                                          <div className='pick-dt'><MomentDateComponent data={{ date: item.scheduled_date, format: "MMM D - hh:mm A " }} /></div>
                                       </div>

                                       {
                                          // details.is_score_predict && details.is_score_predict == 1 ?
                                          //    <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                          //       <Tooltip id="tooltip" className="tooltip-featured">
                                          //          {details.is_score_predict && details.is_score_predict == 1 && parseInt(item.score) != 0 ? "+" : ""}
                                          //          {
                                          //             parseInt(item.score) == parseInt(winGoal) ?
                                          //                parseInt(item.score) + ' ' + AL.COMPLTED_TOOLTIP_TEXT1 :
                                          //                parseInt(item.score) == parseInt(winGoalDiff) ?
                                          //                   parseInt(item.score) + ' ' + AL.COMPLTED_TOOLTIP_TEXT2 :
                                          //                   parseInt(item.score) == parseInt(winOnly) ?
                                          //                      parseInt(item.score) + ' ' + AL.COMPLTED_TOOLTIP_TEXT3 :
                                          //                      parseInt(item.score) == 0 ?
                                          //                         AL.COMPLTED_TOOLTIP_TEXT4 : ""
                                          //          }
                                          //       </Tooltip>
                                          //    }>
                                          //       <div className={`pick-pts-score pts-score-view txt-underline ${item.is_correct == 1 ? ' succ ' : item.is_correct == 0 ? ' zero-color-change ' : ' ps-negtive '} ${!item.team_id ? 'zero-point' : ''}`}> {item.is_correct == 1 && "+"}{parseInt(item.score) || 0} {AL.PTS}</div>
                                          //    </OverlayTrigger>
                                          // :
                                          <div className={`pick-pts-score pts-score-view ${item.is_correct == 1 ? ' ps-plus ' : ' ps-negtive'} ${!item.team_id ? ' zero-point' : ''}`}> {item.is_correct == 1 && "+"}{parseInt(item.score) || 0} {AL.PTS}</div>
                                       }



                                    </div>
                                 }
                                 {details.is_score_predict == 1 ?
                                    <div className='pick-option-row pick-option-row-vs'>
                                       <div className='pick-option-col col-4'>
                                          <div className='pick-option-info'>
                                             <div className='pick-option-img'>
                                                <img className='img-fluid' src={Utilities.teamFlagURL(item.home_flag)} alt='' />
                                             </div>
                                             <div className='pick-option-name'>
                                                {item.home}
                                             </div>
                                          </div>
                                       </div>
                                       <div className='pick-option-col col-4'>
                                          
                                             <div className="input-score-sec">
                                                {(score_data && score_data.home_score && score_data.away_score && score_data.away_score == item.away_predict && score_data.home_score == item.home_predict) ?
                                                   <i className="icon-tick-circular"></i>
                                                   :
                                                   <i className="icon-cross-circular"></i>}
                                                <div class="hm-score"><p class="blk-txt">{item.home_predict ? item.home_predict : '-'}</p></div>
                                                <div class="ay-score"> <p class="blk-txt">{item.away_predict ? item.away_predict : "-" }</p></div>
                                             </div>
                                          
                                          <div className='pick-team-vs'>
                                             {AppLabels.CORRECT_SCORE} {score_data.home_score} - {score_data.away_score}
                                          </div>
                                       </div>
                                       <div className='pick-option-col col-4'>
                                          <div className='pick-option-info'>
                                             <div className='pick-option-img'>
                                                <img className='img-fluid' src={Utilities.teamFlagURL(item.away_flag)} alt='' />
                                             </div>
                                             <div className='pick-option-name'>
                                                {item.away}
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                    :
                                    <div className='pick-option-row'>
                                       <div className={`pick-option-col ${AppSelectedSport != "7"  ? " col-4" : " col-6"}`}>
                                          <div className={`pick-option-info ${item.team_id == item.winning_team_id && item.team_id == item.home_id ? " correct" : item.team_id == item.home_id && item.team_id != item.winning_team_id ? " wrong" : " no-bg"}`}>

                                             <i className={item.winning_team_id == item.home_id ? 'icon-tick' : item.team_id == item.home_id && item.team_id != item.winning_team_id ? " icon-close" : ""} />
                                             <div className='pick-option-img'>
                                                <img className='img-fluid' src={Utilities.teamFlagURL(item.home_flag)} alt='' />
                                             </div>
                                             <div className='pick-option-name'>
                                                {item.home}
                                             </div>

                                          </div>
                                       </div>

                                       <div className={`pick-option-col ${AppSelectedSport != "7"  ? " col-4" : " d-none"}`}>
                                          <div className={`pick-option-info ${score_data && score_data.home_score == score_data.away_score && item.team_id == item.winning_team_id ? " correct" : item.team_id == "0" ? " wrong" : " no-bg"}`}>
                                             <i className={score_data && score_data.home_score == score_data.away_score && item.team_id == item.winning_team_id ? 'icon-tick' : item.team_id == "0" ? " icon-close" : ""} />
                                             <div className='pick-option-img'>
                                                <img className='img-fluid' src={Images.DRAW_IMG} alt="" />
                                             </div>
                                             <div className='pick-option-name'>
                                                {"Draw"}
                                             </div>
                                          </div>
                                       </div>

                                       <div className={`pick-option-col ${AppSelectedSport != "7" ? " col-4" : " col-6"} `}>
                                          <div className={`pick-option-info ${item.team_id == item.winning_team_id && item.team_id == item.away_id ? " correct" : item.team_id == item.away_id && item.team_id != item.winning_team_id ? " wrong" : " no-bg"}`}>
                                             <i className={item.winning_team_id == item.away_id ? 'icon-tick' : item.team_id == item.away_id && item.team_id != item.winning_team_id ? " icon-close" : ""} />
                                             <div className='pick-option-img'>
                                                <img className='img-fluid' src={Utilities.teamFlagURL(item.away_flag)} alt='' />
                                             </div>
                                             <div className='pick-option-name'>
                                                {item.away}
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 }
                              </>
                           )
                        })}
                        {

                           matchDetails && matchDetails.length == 0 &&
                           <NoDataView
                              BG_IMAGE={Images.no_data_bg_image}
                              // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                              CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                              MESSAGE_1={AL.NO_DATA_AVAILABLE}
                           />
                        }





                        {/* <div className='pick-countdown-tview'>
                           <div className='pick-com-timer'>
                              <div className='pick-dt'>Mar 20 5:30 AM</div>
                           </div>
                           <div className='pick-pts-score ps-plus'>
                              +10 Pts 
                           </div>
                        </div>
                        <div className='pick-option-row'>
                           <div className='pick-option-col col-4'>
                              <div className='pick-option-info correct'>
                                 <i className='icon-tick'></i>
                                 <div className='pick-option-img'>
                                 <img className='img-fluid' src={Images.DEFAULT_AVATAR} alt=''/>
                                 </div>
                                 <div className='pick-option-name'>
                                    Fulham
                                 </div>
                                 <div className='pick-percentage'> 
                                    75
                                    <span>%</span>
                                 </div>                           
                              </div>
                           </div>
                           <div className='pick-option-col col-4'>   
                              <div className='pick-option-info'>
                                 <div className='pick-option-img'>
                                    <img className='img-fluid' src={Images.DEFAULT_AVATAR} alt=''/>
                                 </div>
                                 <div className='pick-option-name'>
                                    Fulham
                                 </div>
                                 <div className='pick-percentage'> 
                                    5
                                    <span>%</span>
                                 </div>                           
                              </div>
                           </div>
                           <div className='pick-option-col col-4'>   
                              <div className='pick-option-info'>
                                 <div className='pick-option-img'>
                                 <img className='img-fluid' src={Images.DEFAULT_AVATAR} alt=''/>
                                 </div>
                                 <div className='pick-option-name'>
                                    Fulham
                                 </div>
                                 <div className='pick-percentage'> 
                                    20
                                    <span>%</span>
                                 </div>                           
                              </div>
                           </div>
                        </div>
                        <div className='pick-countdown-tview'>
                           <div className='pick-com-timer'>
                              <div className='pick-dt'>Mar 20 5:30 AM</div>
                           </div>
                           <div className='pick-pts-score ps-plus'>
                              +10 Pts 
                           </div>
                        </div>
                        <div className='pick-option-row'>
                           <div className='pick-option-col col-4'>
                              <div className='pick-option-info'>
                                 <div className='pick-option-img'>
                                 <img className='img-fluid' src={Images.DEFAULT_AVATAR} alt=''/>
                                 </div>
                                 <div className='pick-option-name'>
                                    Fulham
                                 </div>
                                 <div className='pick-percentage'> 
                                    75
                                    <span>%</span>
                                 </div>                           
                              </div>
                           </div>
                           <div className='pick-option-col col-4'>   
                              <div className='pick-option-info'>
                                 <div className='pick-option-img'>
                                    <img className='img-fluid' src={Images.DEFAULT_AVATAR} alt=''/>
                                 </div>
                                 <div className='pick-option-name'>
                                    Fulham
                                 </div>
                                 <div className='pick-percentage'> 
                                    5
                                    <span>%</span>
                                 </div>                           
                              </div>
                           </div>
                           <div className='pick-option-col col-4'>   
                              <div className='pick-option-info correct'>
                                 <i className='icon-tick'></i>
                                 <div className='pick-option-img'>
                                 <img className='img-fluid' src={Images.DEFAULT_AVATAR} alt=''/>
                                 </div>
                                 <div className='pick-option-name'>
                                    Fulham
                                 </div>
                                 <div className='pick-percentage'> 
                                    20
                                    <span>%</span>
                                 </div>                           
                              </div>
                           </div>
                        </div>
                        <div className='pick-countdown-tview'>
                           <div className='pick-com-timer'>
                              <div className='pick-dt'>Mar 20 5:30 AM</div>
                           </div>
                           <div className='pick-pts-score ps-negtive'>
                              -4 Pts 
                           </div>
                        </div>
                        <div className='pick-option-row'>
                           <div className='pick-option-col col-4'>
                              <div className='pick-option-info'>                                 
                                 <div className='pick-option-img'>
                                 <img className='img-fluid' src={Images.DEFAULT_AVATAR} alt=''/>
                                 </div>
                                 <div className='pick-option-name'>
                                    Fulham
                                 </div>
                                 <div className='pick-percentage'> 
                                    75
                                    <span>%</span>
                                 </div>                           
                              </div>
                           </div>
                           <div className='pick-option-col col-4'>   
                              <div className='pick-option-info wrong'>
                              <i className='icon-close'></i>
                                 <div className='pick-option-img'>
                                    <img className='img-fluid' src={Images.DEFAULT_AVATAR} alt=''/>
                                 </div>
                                 <div className='pick-option-name'>
                                    Fulham
                                 </div>
                                 <div className='pick-percentage'> 
                                    5
                                    <span>%</span>
                                 </div>                           
                              </div>
                           </div>
                           <div className='pick-option-col col-4'>   
                              <div className='pick-option-info'>
                                 <div className='pick-option-img'>
                                 <img className='img-fluid' src={Images.DEFAULT_AVATAR} alt=''/>
                                 </div>
                                 <div className='pick-option-name'>
                                    Fulham
                                 </div>
                                 <div className='pick-percentage'> 
                                    20
                                    <span>%</span>
                                 </div>                           
                              </div>
                           </div>
                        </div> */}

                        {/* two Columns view */}



                     </div>



                  </Modal.Body>
                  <Modal.Footer className='tourLBF'>
                     <div className='footer-tour'>
                        <div>{AL.TOTAL} {' '} {AL.SCORE}
                        </div>
                        <div className='t-score'>{ptTeamHistory.total_score ?

                           Number(parseFloat(ptTeamHistory.total_score || 0).toFixed(2))
                           : 0} {AL.POINTS}</div>
                     </div>
                  </Modal.Footer>
               </Modal>

            )}
         </MyContext.Consumer>
      );
   }
}