import React from 'react';
import { Clearfix, Modal, ProgressBar } from 'react-bootstrap';
import { MyContext } from '../InitialSetup/MyProvider';
import { Images } from 'OpinionTrade/Lib';
import * as AL from "../helper/AppLabels";
import { participantsDetail } from "../WSHelper/WSCallings";
import * as WSC from "../WSHelper/WSConstants";
import { Utilities } from '../Utilities/Utilities';
export default class ParticipantsModal extends React.Component {
   constructor(props) {
      super(props)

      this.state = {
         homeTeamPer: "",
         awayTeamPer: "",
         drawTeamPer : "",
         participants_detail: []
      }
   }
   ShowProgressBar = (join, total) => {
      return join * 100 / 100;
   }
   componentDidMount() {
      this.PickedPercentageHome()
      this.PickedPercentageAway()
      this.PickedPercentageDraw() 
      this.participantsDetail()
   }
   PickedPercentageHome = () => {
      const { participantsData } = this.props
      let picked = parseFloat(participantsData.home_count || 0)
      let total = participantsData.total_season_count ? participantsData.total_season_count : 100
      let pickedPer = picked == 0 ? 0 : ((picked / total) * 100).toFixed(2);
      let checkpickedPer = (pickedPer % 1) == 0 ? Math.floor(pickedPer) : pickedPer;
      pickedPer = Math.round(checkpickedPer);
      this.setState({ homeTeamPer: pickedPer })
   }
   PickedPercentageAway = () => {
      const { participantsData } = this.props;
      let picked = parseFloat(participantsData.away_count || 0)
      let total = participantsData.total_season_count ? participantsData.total_season_count : 100
      let pickedPer = picked == 0 ? 0 : ((picked / total) * 100).toFixed(2);
      let checkpickedPer = (pickedPer % 1) == 0 ? Math.floor(pickedPer) : pickedPer;
      pickedPer = Math.round(checkpickedPer);
      this.setState({ awayTeamPer: pickedPer })
   }
   PickedPercentageDraw = () => {
      const { participantsData } = this.props;
      let picked = parseFloat(participantsData.draw_count || 0)
      let total = participantsData.total_season_count ? participantsData.total_season_count : 100
      let pickedPer = picked == 0 ? 0 : ((picked / total) * 100).toFixed(2);
      let checkpickedPer = (pickedPer % 1) == 0 ? Math.floor(pickedPer) : pickedPer;
      pickedPer = Math.round(checkpickedPer);
      this.setState({ drawTeamPer: pickedPer })
   }


   

   participantsDetail = () => {
      const { details, item } = this.props;
      let param = {
         "tournament_id": details.tournament_id,
         "season_id": item.season_id
      }
      participantsDetail(param).then((responseJson) => {
         if (responseJson.response_code === WSC.successCode) {

            this.setState({
               participants_detail: responseJson.data
            })
         }
      })
   }





   render() {
      const { homeTeamPer, awayTeamPer, drawTeamPer, participants_detail } = this.state;
      const { mShow, mHide, participantsData } = this.props;

      return (
         <MyContext.Consumer>
            {(context) => (
               <Modal
                  show={mShow}
                  onHide={mHide}
                  className="participants-modal-backdrop"

               >
                  <Modal.Body>
                     <div className="participants-view">
                        <div className="heading-view">
                           <div className="participants-number">{participantsData.total_season_count} {AL.PARTICIPANTS}</div>
                           <div className="team-name">{participantsData.home} vs {participantsData.away} </div>
                        </div>
                        <div className="slider-view">
                        <ProgressBar >
                           <ProgressBar now={homeTeamPer} key={1}  className='primary-progress-bar'/>
                           <ProgressBar now={drawTeamPer} key={2}  className='draw-progress-bar' />
                           <ProgressBar now={awayTeamPer} key={3} className='secondary-progress-bar'/>
                        </ProgressBar>
                           {/* <ProgressBar now={this.ShowProgressBar(homeTeamPer)} /> */}
                           <div className='team-fullname-percentage'>
                              <span>{participantsData.home_name} {homeTeamPer}%</span>
                              <span>{participantsData.away_name} {awayTeamPer}%</span>
                           </div>
                        </div>

                        {/* <ProgressBar>
                           <ProgressBar striped variant="success" now={38} key={1} />
                           <ProgressBar variant="warning" now={25} key={2} />
                           <ProgressBar striped variant="danger" now={38} key={3} />
                        </ProgressBar> */}

                        <div className="participants-name-container">
                           {participants_detail && participants_detail.length > 0 &&
                              participants_detail.map((item, idx) => {
                                 return (
                                    <div className="participants-name-view" key={idx}>
                                       <div className='images-name-view'>
                                          <img src={item.image ? Utilities.getThumbURL(item.image) : Images.DEFAULT_AVATAR} alt="" />
                                          <span>{item.user_name}</span>
                                       </div>
                                       <div className='Team-name-view'>{item.team_id == participantsData.home_id ? participantsData.home : item.team_id == participantsData.away_id ? participantsData.away : 'Draw'}</div>
                                    </div>
                                 )
                              })
                           }

                        </div>

                     </div>
                  </Modal.Body>
               </Modal>
            )}
         </MyContext.Consumer>
      );
   }
}
