import React, { Component } from 'react';
import { Col, Row, Button, Input, Tooltip , Modal, ModalBody, ModalFooter, ModalHeader} from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import _, { isUndefined } from 'lodash';
import LS from 'local-storage';
import { notify } from 'react-notify-toast';
import HF, { _times, _Map, _isUndefined, _isEmpty, _cloneDeep, _isNull } from "../../helper/HelperFunction";
import Images from '../../components/images';
import { PT_TIE_BREAKER } from '../../helper/Message';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import Pagination from "react-js-pagination";
export default class OTAddMatch extends Component {
    constructor(props) {
        super(props);
        let filter = {
            CURRENT_PAGE: 1,
            items_perpage: 50,
            type: 1
        }
        this.state = {
            league_id: '',
            season_id: '',
            sports_id: '',
            season_game_uid: '',
            away_flag: '',
            away_id: '',
            home_id: '',
            home_flag: '',
            correct: '',
            // question: '',
            wrong: '',
            league_name: '',
            match: '',
            scheduled_date: '',
            modified_date: '',
            question: [],
            queFilled: 0,
            draftQuestion: false,
            tieBreakerShow: false,
            checkedTie: false,
            tieBreakerQue: {},
            startRange: '',
            endRange: '',
            isStatsValid: true,
            showQueInfo: false,
            checkSDPosting: true,
            checkPMPosting: true,
            checkedMatch: false,
            EndDate: new Date(),       
            StartDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
            rows: [{question:'', ExpireDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000)}],
            // SeasonDetails: this.props.location.state && this.props.location.state.selectedObj
            SeasonDetails: [],
            ContestStatsData: '',
            QuestionList :'',
            Total : 1,
            ActiveId: '',
            ActiveType:'',
            ParticipantModalIsOpen: false,
            MarkAnswerModal:false,
            QuestionpopItem:'',
            ParticipentQuestionDetails:'',
            CancelModalIsOpen: false,
            CancelSeasonId: "",
            CancelQuestionModalIsOpen: false,
            CancelQuestionId:'',
            cancelFixtures :'',
              TotalP: 0,
            PERPAGE: 10,
            CURRENT_PAGE: 1,
            UserData:''
            
        };
        // this.handleChange = this.handleChange.bind(this);
    }

     seasonDetail = () => {
        // let { season_id, league_id } = this.state

        // console.log(this.props.match.params,'this.state ggg')

  
 
        // let params = {           
        //     season_game_uid: this.props.match.params.season_id,
        //     league_id: this.props.match.params.league_id
        // }
        
        // WSManager.Rest(NC.baseURL + NC.TRADE_SEASON_DETAIL, params).then(Response => {
        //     if (Response.response_code == NC.successCode) {
        //         console.log(Response.data,'Response')
        //         // this.props.history.push({ pathname: '/picksfantasy/createtemplatecontest/' + league_id + '/' + season_id })

        //         console.log(Response.data,'Response.data kkkk');
        //          this.setState({
        //              SeasonDetails : Response.data
        //           })
        //     } else {
        //         notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        //     }
        //     // this.setState({
        //     //     checkPMPosting: true
        //     // })
        // }).catch(error => {
        //     notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        // })
    }

     participentDetail = (item) => {
        let {CURRENT_PAGE,PERPAGE, ActiveId } = this.state      
        let params = {           
            question_id: item.question_id,  
            limit:   PERPAGE,      
            page:   CURRENT_PAGE      
        }       
        
        WSManager.Rest(NC.baseURL + NC.TRADE_QUESTION_PARTICIPENT_DETAIL, params).then(Response => {
            
            if (Response.response_code == NC.successCode) {               
                 this.setState({
                     ParticipentQuestionDetails : Response.data.result,
                     TotalP: Response.data.total,
                     UserData: Response.data.user_data,

                  })
            } else {
               this.setState({
                     ParticipentQuestionDetails : Response.data.result
                  })
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

        handlePageChange(current_page,item) {
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            this.participentDetail(item);
        });
    }

    componentDidMount = () => {     
        let params = {           
            season_id: this.props.match.params.season_id,
            league_id: this.props.match.params.league_id
        }       
        
        WSManager.Rest(NC.baseURL + NC.TRADE_SEASON_DETAIL, params).then(Response => {
            if (Response.response_code == NC.successCode) {
               
                 this.setState({
                     SeasonDetails : Response.data
                  })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
         
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        },this.GetQuestion())

        

    }

    componentWillUnmount = () => {
    }


    GetQuestion = () => {
   
        let params = {
          
             season_id: this.props.match.params.season_id,
            
        }
        
       WSManager.Rest(NC.baseURL + NC.GET_SEASON_QUESTION, params).then(Response => {
            if (Response.response_code == NC.successCode) {
              let cancelFixtures =   Response.data.filter(rows => rows.status == 0)

               
                 this.setState({
                     QuestionList : Response.data,
                     cancelFixtures : cancelFixtures
                  })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
           
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        },[])
    }

    MarkAnswerSet =(item, type)=>{
       this.setState({
          ActiveId : item.question_id,
          ActiveType: type,
          MarkAnswerModal: true,
        })
    } 
    closeAnswerPopup = () => {
      this.setState({
         MarkAnswerModal: false
      });
   }
  

  redirectToCreateQuestion = (selectedObj) => {
    // this.props.history.push({ pathname: '/OpinionTrading/publish_match/' + selectedObj.league_id + '/' + selectedObj.season_game_uid })
    this.props.history.push({  pathname: '/opinionTrading/add_question/' + selectedObj.league_id + '/' + selectedObj.season_id + '/' +this.props.match.params.tab, state: { selectedObj: selectedObj } })
  }

   closeWinningPopup = () => {
      this.setState({
         ParticipantModalIsOpen: false,
         CURRENT_PAGE: 1
      });
      
   }
    ParticipantModalToggle = (item) => {
      if(item.participant <= 0){
         notify.show('No Participent', 'error', 5000)
        return false;
      }
      this.setState({
         ParticipantModalIsOpen: true,
         QuestionpopItem:item
        
      },this.participentDetail(item));
   }


   participantCapModal = () => {  
    let{QuestionpopItem,ParticipentQuestionDetails,UserData} = this.state     
      return (
         <div>
            <Modal
               isOpen={this.state.ParticipantModalIsOpen}               
               className="cancel-match-modal participant-modal"
                toggle={() => this.closeWinningPopup()}
            >             

               <ModalHeader>
                  <div className="modalcancel-close">
                     <div>{QuestionpopItem.question} </div>
                     <div onClick={this.closeWinningPopup}><span className="icon-close"></span></div>
                  </div>

               </ModalHeader>
               <ModalBody>         
                
                <div className="participants">
                  <div className='participant_count'><spam>Participants ({QuestionpopItem.participant})</spam></div>
                  <div className='upper_section'>
                    <div className="users_section" id="users_section">
                      {/* {ParticipentQuestionDetails && ParticipentQuestionDetails.map((row, index) => (     
                        
                        */}

                        {
                        !_isEmpty(ParticipentQuestionDetails)
                          ?
                          _Map(ParticipentQuestionDetails, (row, index) => {

                            // console.log(row,'rowfffffffffffff'); return
                            // console.log(row.matched,'rowfffffffffffff')

                                  var widthYes = row.entry_fee*10                            
                                  var widthNo = row.m_entry_fee*10 
                                  
                                  
                                  if(row.status == 1 ){
                                    var tradeStatus = 'cancelled'
                                  }
                                   if(row.status == 0 && row.matchup_id == 0 ){
                                    var tradeStatus = 'pending'
                                  }
                                  if(row.status == 0 && row.matchup_id > 0){
                                    var tradeStatus = 'matched'
                                  }  
                                    if(row.status == 2 || row.status == 3 ){
                                    var tradeStatus = 'completed'
                                  }  
                                  
                            return (                       
                                <div className="user_row">
                                  <div className="lft">
                                   
                                { row.user_id != '' ? <img src= {NC.S3 + 'upload/profile/thumb/' + UserData[row.user_id].image} /> :  <img src= {NC.S3 + 'upload/profile/thumb/no_user.png'} /> }
                                     <h5>{row.user_id != '' ? UserData[row.user_id].name: 'Trader'}</h5> 
                                  </div>
                                  <div className="mdl">                                   
                                    <div className="clear"></div>
                                    <div className="ans_bx">
                                      <div className="ans_yes" style={{width:widthYes+"%"}}>₹ {row.entry_fee}</div>
                                      <div className="ans_no" style={{width:widthNo+"%"}}>₹ {row.m_entry_fee}</div>
                                      
                                    </div>
                                    <p className={tradeStatus}>{tradeStatus}</p>
                                  </div>
                                  <div className="rgt">
                                   
                                    { row.m_user_id != '' ? <img src= {NC.S3 + 'upload/profile/thumb/' + UserData[row.m_user_id].image} /> :  <img src= {NC.S3 + 'upload/profile/thumb/no_user.png'} /> }
                                    <h5>{row.m_user_id != ''? UserData[row.m_user_id].name:'Trader'}</h5> 
                                  </div>
                              

                                 
                                </div>
                              )
                            })
                             : ''
                         }
                        {
                          _isEmpty(ParticipentQuestionDetails) &&
                          <Col md={12}>
                            <div className="no-records">No Record Found.</div>
                          </Col>
                        }                      

                       {/* <div className="user_row">
                        <div className="lft">
                          <img src="https://predev-vinfotech-org.s3.amazonaws.com/upload/profile/thumb/avatar10.png" />
                          <h5>girishp</h5>
                        </div>
                        <div className="mdl">                     
                          <div className="clear"></div>
                          <div className="ans_bx">
                            <div className="ans_yes" style={{width:"50.00%"}}>₹ 5.0</div>
                            <div className="ans_no" style={{width:"50.00%"}}>₹ 5.0</div>
                            <p>PENDING</p>
                          </div>
                        </div>
                        <div className="rgt">
                          <img src="https://predev-vinfotech-org.s3.amazonaws.com/upload/profile/thumb/no_user.png" />
                          <h5>Trader</h5>
                        </div>                       
                      </div>  */}
                    </div>
                  </div>

                    <div className="custom-pagination userlistpage-paging float-right mb-5">
                      <Pagination
                          activePage={this.state.CURRENT_PAGE}
                          itemsCountPerPage={this.state.items_perpage}
                          totalItemsCount={this.state.TotalP}
                          pageRangeDisplayed={5}
                          onChange={e => this.handlePageChange(e,this.state.QuestionpopItem)}
                      />
                    </div>




                </div>
               </ModalBody>             
            </Modal>
         </div>
      )
   }

    answerMarkpModal = () => {
      return (
         <div>
            <Modal
               isOpen={this.state.MarkAnswerModal}               
               className="cancel-match-modal markanswer_popup_content"
            >             
            <ModalBody>               
                  <div className="model_down_text">
                     <p className="trade-text">Are you sure you want to submit this answer?</p>                   
                     <p className="trade-text">(you cannot undo this action)</p>               
                  </div>
               </ModalBody>
               <ModalFooter>
                 <Button color="primary" onClick={this.closeAnswerPopup}>Cancel</Button>
                  <Button
                     color="secondary"
                     onClick={() => this.updateAnswer('yes')}
                    //  disabled={CancelWinningPosting}
                  >Yes</Button>{' '}
               </ModalFooter>
            </Modal>
         </div>
      )
   }

     updateAnswer = (type) => {
     let { ActiveId, ActiveType } = this.state
     
      if(ActiveType =="Yes"){
        var answer = 1
      }else{
         var answer = 2
      }
   
        let params = {          
             question_id: ActiveId,   
             answer :answer         
        }
        console.log(params, 'answer param');
        // console.log(params,'params question');
       WSManager.Rest(NC.baseURL + NC.UPDATE_TRADE_ANSWER, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                console.log(Response.data,'Response')
                notify.show(Response.message, "success", 3000)
               
               
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
            this.closeAnswerPopup();
            this.GetQuestion()
           
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        },[])
    }

  cancelMatchModalToggle = (SeasonDetails) => { 
    this.setState({   
      CancelModalIsOpen: !this.state.CancelModalIsOpen,
      CancelSeasonId : SeasonDetails.season_id     
    });
  }

   cancelQuestionModalToggle = (item) => {
  
    this.setState({   
      CancelQuestionModalIsOpen: !this.state.CancelQuestionModalIsOpen,
      CancelQuestionId : item.question_id
     
    });
  }

    cancelQuestionModal = () => {
    // let { CancelPosting, API_FLAG } = this.state
    return (
      <div>
        <Modal
          isOpen={this.state.CancelQuestionModalIsOpen}
          toggle={this.cancelQuestionModalToggle}
          className="cancel-match-modal"
        >
          <ModalHeader>Cancel Question</ModalHeader>
          <ModalBody>
            <div className="confirm-msg"></div>
            <div className="inputform-box">
              <label className='label-align-trade'>Are you sure you want to cancel Question ?</label>
               
            </div>
          </ModalBody>
          <ModalFooter>
            <Button
              color="secondary"
              onClick={this.cancelQuestion}
              // disabled={CancelPosting}
            >Yes</Button>{' '}
            <Button color="primary" onClick={this.cancelQuestionModalToggle}>No</Button>
          </ModalFooter>
        </Modal>
      </div>
    )
  }

   cancelMatchModal = () => {
    // let { CancelPosting, API_FLAG } = this.state
    return (
      <div>
        <Modal
          isOpen={this.state.CancelModalIsOpen}
          toggle={this.cancelMatchModalToggle}
          className="cancel-match-modal"
        >
          <ModalHeader>cancel</ModalHeader>
          <ModalBody>
            <div className="confirm-msg"></div>
            <div className="inputform-box">
              <label className='label-align-trade'>Are you sure you want to cancel fixture ?</label>
               
            </div>
          </ModalBody>
          <ModalFooter>
            <Button
              color="secondary"
              onClick={this.cancelMatch}
              // disabled={CancelPosting}
            >Yes</Button>{' '}
            <Button color="primary" onClick={this.cancelMatchModalToggle}>No</Button>
          </ModalFooter>
        </Modal>
      </div>
    )
  }

   cancelMatch = () => {
   
        let params = {          
             season_id: this.props.match.params.season_id,        
             sports_id: this.state.SeasonDetails.sports_id            
        }   
       WSManager.Rest(NC.baseURL + NC.TRADE_FIXTURE_CANCEL, params).then(Response => {
            if (Response.response_code == NC.successCode) {

              notify.show(Response.message, 'success', 5000)

               this.setState({   
                   CancelModalIsOpen: !this.state.CancelModalIsOpen,        
                 });
                 this.GetQuestion()
              
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
           
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        },[])
    }

    cancelQuestion = () => {
   
        let params = {          
          question_id: this.state.CancelQuestionId,
          sports_id: this.state.SeasonDetails.sports_id           
        }
        
       WSManager.Rest(NC.baseURL + NC.TRADE_QUESTION_CANCEL, params).then(Response => {
            if (Response.response_code == NC.successCode) {

                notify.show(Response.message, 'success', 5000)

               this.setState({   
                   CancelQuestionModalIsOpen: !this.state.CancelQuestionModalIsOpen,        
                 });
                 this.GetQuestion()

            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
          
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        },[])
    }



    render() {
        const {SeasonDetails,rows,EndDate, StartDate,home_flag, away_flag, match, scheduled_date, league_name, question, questionCount, queFilled, draftQuestion, tieBreakerShow, tieBreakerQue, showQueInfo, checkSDPosting, checkPMPosting } = this.state
        // console.log( this.state.cancelFixtures.length,' this.state.cancelFixtures.length')
        return (
            <>  
             
            <Row className='mt-30 clearfix'>  
              {this.state.ParticipantModalIsOpen && this.participantCapModal()}  
              {this.state.MarkAnswerModal && this.answerMarkpModal()}  
               {this.cancelMatchModal()}
               {this.cancelQuestionModal()}

             <Col md={5}>
                                <div className="season-data-container season-trade-sections">
                                    {/* {NC.S3 + NC.FLAG + Roster_Data.home_flag} */}
                                    <img className="flags-logo" src={NC.S3 + NC.FLAG + SeasonDetails.home_flag} alt="" />
                                    <div className="season-data">
                                        <div className="fixture-details">
                                            {SeasonDetails.home} 
                                            {' vs '}
                                            
                                             {SeasonDetails.away}
                                        </div>
                                        <div className="season-duration">

                                            {/* <MomentDateComponent data={{ date: SeasonDetails.scheduled_date, format: "D MMM - hh:mm a" }} />(IST) */}
                    {HF.getFormatedDateTime(SeasonDetails.scheduled_date, "D MMM - hh:mm a")}
                                        
                                        </div>
                                        <div className="season-duration">
                                            {/* title */}
                                            {SeasonDetails.league_name}
                                        </div>
                                    </div>
                                    
                                    <img className="flags-logo" src={NC.S3 + NC.FLAG + SeasonDetails.away_flag} alt="" />
                                </div>
                            </Col>
                             <Col sm={7}>
                                <div className='align-top-div'>
                                   { (SeasonDetails.status != "2") && 
                                   <Button className='xpull-right btn-secondary-outline cancel-match-btn' onClick={() => this.redirectToCreateQuestion(SeasonDetails)}>Add Question</Button>
                                  }     
                                   { (SeasonDetails.status != "2" && this.state.cancelFixtures.length > 0) && 
                                   
                                    
                                         <Button
                                        className='cancel-match-btn btn-secondary'
                                          onClick={() => this.cancelMatchModalToggle(SeasonDetails)}
                                        >Cancel Fixture</Button> 
                                  }

                                </div>
                    
                            </Col>
            </Row>
            <hr/>

               <Row  className="bench-dtl trade_contest-sec">
          <Col md={10}>
            <h2 className="h2-cls mb-3">Contest Stats</h2>
            <Row>
              <Col sm={3} className="pr-0">
                <div className="fxcon-total-box">
                  <div className="fxcon-title">Total Entries</div>
                  <div className="fxcon-count">
                    {
                      (!_.isEmpty(this.state.SeasonDetails) && !_.isUndefined(this.state.SeasonDetails.total_entered))
                        ?
                        this.state.SeasonDetails.total_entered
                        :
                        0
                    }
                  </div>
                </div>
              </Col>
              <Col sm={3} className="pr-0">
                <div className="fxcon-total-box">
                  <div className="fxcon-title">Matched</div>
                  <div className="fxcon-count">
                    {
                      (!_.isEmpty(this.state.SeasonDetails) && !_.isUndefined(this.state.SeasonDetails.matched))
                        ?
                        this.state.SeasonDetails.matched
                        :
                        0
                    }
                  </div>
                </div>
              </Col>
              <Col sm={3}>
                <div className="fxcon-total-box">
                  <div className="fxcon-title">Unmatched</div>
                  <div className="fxcon-count">
                    {
                      (!_.isEmpty(this.state.SeasonDetails) && !_.isUndefined(this.state.SeasonDetails.unmatched))
                        ?
                       this.state.SeasonDetails.unmatched
                        :
                        0
                    }
                  </div>
                </div>
              </Col>

              <Col sm={3}>
                <div className="fxcon-total-box">
                  <div className="fxcon-title">Total Unique joined Users</div>
                  <div className="fxcon-count">
                    {
                        (!_.isEmpty(this.state.SeasonDetails) && !_.isUndefined(this.state.SeasonDetails.unique_user_joined))
                        ?
                        this.state.SeasonDetails.unique_user_joined
                        :
                        0
                    }
                  </div>
                </div>
              </Col>
            </Row>
          </Col>
          
        </Row>

        <Row className='mt-30'>
             <Col className="heading-box">     

          <div className="fixture-back-contest">            
            <label className="backtofixtures" onClick={() => this.props.history.push('/opinionTrading/fixture?tab='+this.props.match.params.tab)}> {'<'} Back to Fixtures</label>
            
          
          </div>
        </Col>
        </Row>
        <hr/>

          <Row className="prediction-dashboard">
                                          
                                            {this.state.Total > 0 ?
                                                _.map(this.state.QuestionList, (item, preIdx) => {
                                                    if (item.status == 0) {
                                                        var status = 'open';
                                                    }if(item.status == 1){
                                                        var status = 'Cancelled';
                                                    }
                                                    if(item.status == 2 || item.status == 3){
                                                        var status = 'Completed';
                                                    }
                                                   
                                                    return (
                                                        <Col md={4} key={preIdx}>
                                                            <div className="question-box trade-question-box">
                                                                <div className="clearfix questionTitles">
                                                                    <div className="ques trade-iner-inline">                                                                       
                                                                            {item.question}
                                                                      
                                                                    </div>{ item.status == 0 &&
                                                                    <div className ="close-icon">
                                                                         <span onClick={() => this.cancelQuestionModalToggle(item)} className="icon-close close-icon-align"></span> 
                                                                    </div>
                                                               }
                                                                    </div>
                                                                 
                                                              
                                                                <div className="pool-answer">
                                                                    <ul className="pool-list trade-pool-list">
                                                                                    <li
                                                                                         onClick= {item.status != 1 ? item.answer == 0 ? () => this.MarkAnswerSet(item, 'Yes') : "":""}   
                                                                                         className={`clearfix pool-item yes-item ${item.question_id == this.state.ActiveId && item.option1 == this.state.ActiveType ? "active" : ""} ${item.answer == 1 ? "green_border" : ""}`}>
                                                                                        <div className={`answer-opts first-div `}>
                                                                                            {item.option1}               
                                                                                        </div>
                                                                                         <div className={`answer-opts sec-div`}>
                                                                                        { item.answer == 1 ? <img className="right_sign" src={Images.RIGHTSIGN} alt="" />:'' }   ₹ {item.option1_val}       
                                                                                        </div>
                                                                                    </li>
                                                                                     <li
                                                                                        onClick=  { item.status != 1 ? item.answer == 0 ? () => this.MarkAnswerSet(item, 'No') : "": ""}
                                                                                        className= {`clearfix pool-item no-item ${item.question_id == this.state.ActiveId && item.option2 == this.state.ActiveType ? "active" : ""} ${item.answer == 2 ? "green_border" : ""}`}>
                                                                                        <div className=" answer-opts first-div">
                                                                                            {item.option2}               
                                                                                        </div>
                                                                                         <div className="answer-opts sec-div">
                                                                                           { item.answer == 2 ?<img className="right_sign" src={Images.RIGHTSIGN} alt="" /> :'' } ₹ {item.option2_val}            
                                                                                        </div>
                                                                                    </li>
                                                                    </ul>
                                                                </div>
                                                                <div className='bottom-div'>
                                                                    <ul>
                                                                        <li className='trade-date'>{ HF.getFormatedDateTime(item.scheduled_date, "D MMM - hh:mm a")}</li>
                                                                        <li className='participant-trade' onClick={() => this.ParticipantModalToggle(item) }><a>{item.participant} Participant</a></li>
                                                                        <li className={`status-design ${status}`}>{status}</li>
                                                                    </ul>
                                                                </div>
                                                               
                                                            </div>

                                                        </Col>
                                                    )
                                                })
                                                :
                                                <Col md={12}>
                                                    {(this.state.Total == 0) ?
                                                        <div className="no-records">{NC.NO_RECORDS}</div>
                                                        : ""
                                                        // <Loader /> 
                                                    }
                                                </Col>
                                            }
                                           
                                        </Row>
                                        
           

                           
            </>
            
        )
    }
}
