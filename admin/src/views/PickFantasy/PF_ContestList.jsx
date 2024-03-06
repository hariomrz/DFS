import React, { Component, Fragment } from 'react';
import {
  Col, Row, Button, TabContent, TabPane, Nav, NavItem, NavLink, Input, Modal, ModalBody, ModalHeader, ModalFooter,
} from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager"
import { Progress } from 'reactstrap';
import moment from 'moment';
import _, { isEmpty, isUndefined } from 'lodash';
import Images from '../../components/images';
import LS from 'local-storage';
import { notify } from 'react-notify-toast';
import { STAR_CONFIRM_MSG, CANCEL_GAME_TITLE, CANCEL_CONTEST_TITLE, MSG_CANCEL_REQ } from "../../helper/Message";
import HF, { _times, _Map, _isUndefined, _isEmpty, _cloneDeep, _isNull } from "../../helper/HelperFunction";
import Loader from '../../components/Loader';

import PromptModal from '../../components/Modals/PromptModal';
import { PF_SAVE_TIE_BREAKER } from '../../helper/WSCalling';

export default class ContestList extends Component {
    constructor(props) {
        super(props);
        this.state = {
            league_id:'',
            season_id:'',
            sports_id:'',
            season_game_uid:'',
            away_flag:'', 
            away_id: '', 
            home_id: '', 
            home_flag: '',
            correct: '',
            // question: '',
            wrong:'',
            league_name:'',
            match: '',
            scheduled_date: '',
            modified_date: '',
            question: [],
            queFilled: 0,
            activeTab: '1',
            selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
            questionList:[],
            pick_id :'',
            answer : '',
            answerBtn: false,
            editPickId: '',
            CancelModalIsOpen: false,
            CONTEST_U_ID: 0,
            prize_modal: false, 
            contestObj: {},
            right_wrong: {},
            queCount:0,
            status: 1,
            prevActiveTab:1,
            showExpModal: false,
            activeQue: '',
            uploadedImgName: '',
            uploadedImgUrl: '',
            storeSaveTieBreakerAnswer: '',
            answerField: '',
            removeImg: false,
            removeImgName:'',
            tiebreakerAnsMark: false
        };
      }
    componentDidMount() {
        this.getLocalProps();
        
        // this.GetContestTemplateMasterData();
        
    }
    getLocalProps = () =>{
        let matchDetails = LS.get('matchDetails')
        this.setState({
            league_id: matchDetails[0].league_id,
            season_id: matchDetails[0].season_id,
            season_game_uid: matchDetails[0].season_game_uid,
            away_flag: matchDetails[0].away_flag, 
            away_id: matchDetails[0].away_id, 
            home_id: matchDetails[0].home_id, 
            home_flag: matchDetails[0].home_flag,
            correct: matchDetails[0].correct,
            questionCount: matchDetails[0].question,
            wrong: matchDetails[0].wrong,
            league_name: matchDetails[0].league_name,
            match: matchDetails[0].match,
            scheduled_date: matchDetails[0].scheduled_date,
            modified_date: matchDetails[0].modified_date,
            // activeTab: matchDetails[0].activeTab,
            prevActiveTab: matchDetails[0].activeTab,
            activeTab: matchDetails[0].activeTab == '3' ? '1' : (matchDetails[0].showtab && matchDetails[0].showtab == 1 ? '1' : matchDetails[0].activeTab ? matchDetails[0].activeTab : '1'),
            status: matchDetails[0].activeTab // 1 for live, 2 for upcoming, 3 for completed
        },()=>{
            this.getFixtureContest()
            this.getFixtureQuestions()
        })
      }
      getFixtureContest = ()=>{
        let params = {
            "items_perpage":"",
            "current_page":"",
            "page_size":"",
            "sort_field":"",
            "sort_order":"DESC",
            "keyword":"",
            "season_id" :this.state.season_id
        }
        WSManager.Rest(NC.baseURL + NC.PF_GET_FIXTURE_CONTEST, params).then(Response => {
            if (Response.response_code == NC.successCode) {
               this.setState({
                contestList: Response.data.contest_template,
                right_wrong: Response.data.right_wrong[0],
               },()=>{})
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }
    parseTieBreakerData=(data)=>{
      // let tmpData = data.tie_breaker_question ? data.tie_breaker_question : {}

      let tmpQue = {}
      if(data.tie_breaker_question){
        try {
          tmpQue = JSON.parse(data.tie_breaker_question)
        } catch {
          tmpQue = {}
        }
      }
      let tmpData = {
        'tie_breaker_answer': data.tie_breaker_answer,
        'tie_breaker_question':tmpQue
      }
      return tmpData
    }
    getFixtureQuestions = ()=>{
        let params = {
            "season_id" :this.state.season_id
        }
        WSManager.Rest(NC.baseURL + NC.PF_GET_QUE_BY_ID, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                let queData = Response.data.question_info
                // let tieBreakerData = Response.data.tie_breaker
                let tieBreakerData = this.parseTieBreakerData(Response.data.tie_breaker)
                let tmpArray = []
                _Map(queData,(item,idx)=>{
                  if(item.answer != '0'){
                    item['answerPicked'] = 1
                  }
                  tmpArray.push(item)
                })
                this.setState({
                    questionList: tmpArray,
                    queCount: tmpArray.length,
                    tieBreakerData:tieBreakerData
                })
               
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    getPrizeAmount = (prize_data) => {
        let prize_text = "Prizes";
        let is_tie_breaker = 0;
        let prizeAmount = { 'real': 0, 'bonus': 0, 'point': 0 };
        if (!_.isNull(prize_data)) {
          prize_data.map(function (lObj, lKey) {
            var amount = 0;
            if (lObj.max_value) {
              amount = parseFloat(lObj.max_value);
            } else {
              amount = parseFloat(lObj.amount);
            }
            if (lObj.prize_type == 3) {
              is_tie_breaker = 1;
            }
            if (lObj.prize_type == 0) {
              prizeAmount['bonus'] = parseFloat(prizeAmount['bonus']) + amount;
            } else if (lObj.prize_type == 2) {
              prizeAmount['point'] = parseFloat(prizeAmount['point']) + amount;
            } else {
              prizeAmount['real'] = parseFloat(prizeAmount['real']) + amount;
            }
          });
        }
        if (is_tie_breaker == 0 && prizeAmount.real > 0) {
          // prize_text = HF.getCurrencyCode() + parseFloat(prizeAmount.real).toFixed(2);
          prize_text = HF.getCurrencyCode() + HF.getPrizeInWordFormat(prizeAmount.real);
        } else if (is_tie_breaker == 0 && prizeAmount.bonus > 0) {
          // prize_text = '<i class="icon-bonus"></i>' + parseFloat(prizeAmount.bonus).toFixed(2);
          prize_text = '<i class="icon-bonus"></i>' + HF.getPrizeInWordFormat(prizeAmount.bonus);
        } else if (is_tie_breaker == 0 && prizeAmount.point > 0) {
          // prize_text = '<img src="' + Images.COINIMG + '" alt="coin-img" />' + parseFloat(prizeAmount.point).toFixed(2);
          prize_text = '<img src="' + Images.COINIMG + '" alt="coin-img" />' + HF.getPrizeInWordFormat(prizeAmount.point)
        }
        return { __html: prize_text };
      }
      
   
  getWinnerCount(ContestItem) {
    if (!_.isEmpty(ContestItem) && !_.isEmpty(ContestItem.prize_distibution_detail) && !_.isNull(ContestItem.prize_distibution_detail)) {
      if (ContestItem.prize_distibution_detail.length > 0) {
        if ((ContestItem.prize_distibution_detail[ContestItem.prize_distibution_detail.length - 1].max) > 1) {
          return ContestItem.prize_distibution_detail[ContestItem.prize_distibution_detail.length - 1].max + " Winners"
        } else {
          return ContestItem.prize_distibution_detail[ContestItem.prize_distibution_detail.length - 1].max + " Winner"
        }
      }
    }
  }

  viewWinners = (e, contestObj) => {
    e.stopPropagation();
    this.setState({ 'prize_modal': true, 'contestObj': contestObj });
  }   

  closePrizeModel = () => {
    this.setState({ 'prize_modal': false, 'contestObj': {} });
  }
    
  toggleTab(tab) {
    if (this.state.activeTab !== tab) {
      this.setState({
        activeTab: tab,
      });
    }
  }  
  ShowProgressBar = (join, total) => {
    return join * 100 / total;
  }

  answerSubmit = (pick_id,answer,isUpdate)=>{
    let params = {
        'pick_id' : pick_id,
        'answer' : answer,
        'update_ans': isUpdate ? 1 : 0
    }
    WSManager.Rest(NC.baseURL + NC.PF_UPDATE_ANSWER, params).then(Response => {
        if (Response.response_code == NC.successCode) {
        notify.show(Response.message, 'success')
          let tmp=[]
            _Map(this.state.questionList, (obj, idx)=>{
                if(obj.pick_id == pick_id){
                    obj['answerPicked']= 1
                }
                tmp.push(obj)
            })
            this.setState({
                questionList: tmp   
            },()=>{
              if(isUpdate){
                this.setState({
                  editPickId: ''
                })
              }
            })
        } else {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        }
    }).catch(error => {
        notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    })
}

  handleAnwer =(e, item, value) =>{
    let tmp=[]
    _Map(this.state.questionList, (obj, idx)=>{
            if(obj.pick_id == item.pick_id){
                // obj['answerPicked']= 1
                obj['answer']= value
                if(item.answerPicked == 1 && this.state.editPickId == obj.pick_id){
                  this.setState({
                    pick_id :item.pick_id,
                    answer : value,
                  })
                }
            }
            tmp.push(obj)
        })
        this.setState({
            questionList: tmp   
        },()=>{})
  }

  editAnswer=(id)=>{
    this.setState({
        editPickId: id
    })
  }

  viewWinners = (e, contestObj) => {
    e.stopPropagation();
    this.setState({ 'prize_modal': true, 'contestObj': contestObj });
  }

  deleteContest = (e, contestObj, index) => {
    e.stopPropagation();
    if (window.confirm("Are you sure want to delete this contest ?")) {
      this.setState({ posting: true })
      let params = {
        "cancel_reason": `${contestObj.collection_id} cancel`,
        "contest_id": contestObj.contest_id
      };

      WSManager.Rest(NC.baseURL + NC.PF_DELETE_CONTEST, params).then((responseJson) => {
        if (responseJson.response_code === NC.successCode) {
          this.getFixtureContest();
          notify.show(responseJson.message, "success", 5000);
        } else {
          notify.show(responseJson.message, "error", 3000);
        }
        this.setState({ posting: false })
      })
    } else {
      return false;
    }
  }


  handleInputChange = (e) => {
    let name = e.target.name
    let value = e.target.value
    let btnAction = false
    if (value.length < 3 || value.length > 160)
      btnAction = true

    this.setState({
      [name]: value,
      CancelPosting: btnAction
    })
  }
  cancelMatchModalToggle = (contest_u_id, flag, group_index, idx) => {
    
    this.setState({
      API_FLAG: flag,
      CancelModalIsOpen: !this.state.CancelModalIsOpen,
      GroupIndex: group_index,
      DeleteIndex: idx,
      CONTEST_U_ID:contest_u_id
    });
  }

  cancelMatchModal = () => {
    let { CancelPosting, API_FLAG } = this.state
    return (
      <div>
        <Modal
          isOpen={this.state.CancelModalIsOpen}
          toggle={this.cancelMatchModalToggle}
          className="cancel-match-modal"
        >
          <ModalHeader>{API_FLAG == 1 ? CANCEL_GAME_TITLE : CANCEL_CONTEST_TITLE}</ModalHeader>
          <ModalBody>
            <div className="confirm-msg">{MSG_CANCEL_REQ}</div>
            <div className="inputform-box">
              <label>Cancel Reason</label>
              <Input
                minLength="3"
                maxLength="160"
                rows={3}
                type="textarea"
                name="CancelReason"
                onChange={(e) => this.handleInputChange(e)}
              />
            </div>
          </ModalBody>
          <ModalFooter>
            <Button
              color="secondary"
              onClick={this.cancelMatch}
              disabled={CancelPosting}
            >Yes</Button>{' '}
            <Button color="primary" onClick={this.cancelMatchModalToggle}>No</Button>
          </ModalFooter>
        </Modal>
      </div>
    )
  }
  cancelMatch = () => {
    let { API_FLAG, CANCEL_COLLE_MASTER_ID, CONTEST_U_ID, CancelReason, GroupIndex, DeleteIndex, contestList } = this.state
    this.setState({ CancelPosting: false });
    let param = {
      cancel_reason: CancelReason,
      // season_id: this.state.season_id
    };

    let API_URL = ""
    if (API_FLAG == 1) {
      param.season_id= this.state.season_id
      API_URL = NC.PF_CANCEL_SEASON
    } else {
      param.contest_unique_id = CONTEST_U_ID
      // param.collection_id = this.state.collection_id
      API_URL = NC.PF_CANCEL_CONTEST
    }
    WSManager.Rest(NC.baseURL + API_URL, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        notify.show(responseJson.message, "success", 5000);
        this.getFixtureContest();
        this.props.history.push({
          pathname: '/picksfantasy/fixture',
          state:{
            activeTab: this.state.prevActiveTab
          }
        })
      }
      this.cancelMatchModalToggle('', '')
    })
  }

  isValidForComplete=()=>{
    let count = 0
    _Map(this.state.questionList,(item,idx)=>{
      if(item.answerPicked == 1){
        count = count + 1
      }
    })
    return count == this.state.questionList.length ? true : false
  }

  markComplete=()=>{
    let params = {
        "season_id" :this.state.season_id
    }
    WSManager.Rest(NC.baseURL + NC.PF_MARK_COMPLETE, params).then(Response => {
        if (Response.response_code == NC.successCode) {
            let queData = Response.data
            let tmpArray = []
            _Map(queData,(item,idx)=>{
              if(item.answer != '0'){
                item['answerPicked'] = 1
              }
              tmpArray.push(item)
            })
            this.setState({
                questionList: tmpArray
            })
            this.props.history.push({
              pathname: '/picksfantasy/fixture',
              state:{
                activeTab: '3'
              }
            })
        } else {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        }
    }).catch(error => {
        notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    })
  }

  updateAnswer=(pick_id,answer)=>{
    this.answerSubmit(pick_id,answer,true)
  }

 
  redirectToCreateContest=()=>{
    this.props.history.push({
      pathname: '/picksfantasy/create-contest/'+ this.state.season_id
    })
  }

  explanationModal=(que)=>{
    this.setState({
      showExpModal: true,
      activeQue:que
    })
  }

  toggleExplantionModal=()=>{
    this.setState({
      showExpModal: !this.state.showExpModal,
      activeQue: ''
    })
  }

  callJsonParser=(data)=>{
    try {
        return JSON.parse(data)
    }
    catch {
        return data
    }
  }

  handleExpInput=(e)=>{
    let tmpQueItem = this.state.activeQue
    let value = e.target.value 
    let name = e.target.name 
    tmpQueItem[name]=value
    this.setState({
      activeQue:tmpQueItem
    })
  }

  renderQueList=(question,idx)=>{
    let { answer,editPickId,status,prevActiveTab} = this.state
    let optImg = !_isUndefined(question.option_images) ? this.callJsonParser(question.option_images) : {}
   
    return (
        <div className='QuestionListItem' key={idx}>
          <p className='questionName'>
            {`${question.name}`} 
            {/* { prevActiveTab == 3 &&
              <Button className="btn btn-ans btn-comp">Completed </Button>
            }       */}
          </p>
          {
            optImg && optImg.option_1 ?
            <ul className='que-list-with-img'>
                <li className={`${(question.answer == 1 &&  editPickId != question.pick_id) || (question.answerPicked == 1 && answer == 1 && this.state.editPickId == question.pick_id) ? 'selected' : ''}`} >
                  <div className='cursor-pointer' onClick={(e)=>(status == 1 && (question.answer == 0 || question.answerPicked != 1 || editPickId == question.pick_id) && this.handleAnwer(e, question, 1))}>
                    <div className="img-sec">
                      <img src={NC.S3 + NC.PICK_FANTASY + optImg.option_1} alt="" />
                    </div>
                    <p className="opt-label">{question.option_1}</p>
                  </div>
                    {/* <input label={question.option_1} type="radio" id="other" name={question.pick_id} onClick={(e)=>(status == 1 && (question.answer == 0 || question.answerPicked != 1 || editPickId == question.pick_id) && this.handleAnwer(e, question, 1))} value={question.option_1} 
                    checked={(question.answer == 1 &&  editPickId != question.pick_id) || (question.answerPicked == 1 && answer == 1 && this.state.editPickId == question.pick_id)}/> */}
                </li>
                <li className={`${(question.answer == 2 &&  editPickId != question.pick_id) || (question.answerPicked == 1 && answer == 2 && this.state.editPickId == question.pick_id) ? 'selected' : ''}`} >
                  <div className='cursor-pointer' onClick={(e)=>(status == 1 && (question.answer == 0 || question.answerPicked != 1 || editPickId == question.pick_id) && this.handleAnwer(e, question, 2))}>
                    <div className="img-sec">
                      <img src={NC.S3 + NC.PICK_FANTASY + optImg.option_2} alt="" />
                    </div>
                    <p className="opt-label">{question.option_2}</p>
                  </div>
                    {/* <input label={question.option_2} type="radio" id="other" name={question.pick_id} onClick={(e)=>(status == 1 && (question.answer == 0 || question.answerPicked != 1 || editPickId == question.pick_id) && this.handleAnwer(e, question, 2))}  value={question.option_2} 
                    checked={(question.answer == 2 &&  editPickId != question.pick_id) || (question.answerPicked == 1 && answer == 2 && this.state.editPickId == question.pick_id)}/> */}
                </li>
                <li className={`${(question.answer == 3 &&  editPickId != question.pick_id) || (question.answerPicked == 1 && answer == 3 && this.state.editPickId == question.pick_id) ? 'selected' : ''}`}>
                  {
                    optImg.option_3 &&
                    <div className='cursor-pointer' onClick={(e)=>(status == 1 && (question.answer == 0 || question.answerPicked != 1 || editPickId == question.pick_id) && this.handleAnwer(e, question, 3))}>
                      <div className="img-sec">
                        <img src={NC.S3 + NC.PICK_FANTASY + optImg.option_3} alt="" />
                      </div>
                      <p className="opt-label">{question.option_3}</p>
                    </div>
                    // <input label={question.option_3} type="radio" id="other" name={question.pick_id} onClick={(e)=>(status == 1 && (question.answer == 0 || question.answerPicked != 1 || editPickId == question.pick_id) && this.handleAnwer(e, question, 3))} value={question.option_3}  
                    // checked={(question.answer == 3 &&  editPickId != question.pick_id) || (question.answerPicked == 1 && answer == 3 && this.state.editPickId == question.pick_id)}/>
                  }
                </li>                
            </ul>
            :
            <ul>
                <li className='' style={{height: 55}}>
                    <input label={question.option_1} type="radio" id="other" name={question.pick_id} onClick={(e)=>(status == 1 && (question.answer == 0 || question.answerPicked != 1 || editPickId == question.pick_id) && this.handleAnwer(e, question, 1))} value={question.option_1} 
                    checked={(question.answer == 1 &&  editPickId != question.pick_id) || (question.answerPicked == 1 && answer == 1 && this.state.editPickId == question.pick_id)}/>
                </li>
                <li className='' style={{height: 55}}>
                    <input label={question.option_2} type="radio" id="other" name={question.pick_id} onClick={(e)=>(status == 1 && (question.answer == 0 || question.answerPicked != 1 || editPickId == question.pick_id) && this.handleAnwer(e, question, 2))}  value={question.option_2} 
                    checked={(question.answer == 2 &&  editPickId != question.pick_id) || (question.answerPicked == 1 && answer == 2 && this.state.editPickId == question.pick_id)}/>
                </li>
                <li className='' style={{height: 55}}>
                  {
                    question.option_3 &&
                    <input label={question.option_3} type="radio" id="other" name={question.pick_id} onClick={(e)=>(status == 1 && (question.answer == 0 || question.answerPicked != 1 || editPickId == question.pick_id) && this.handleAnwer(e, question, 3))} value={question.option_3}  
                    checked={(question.answer == 3 &&  editPickId != question.pick_id) || (question.answerPicked == 1 && answer == 3 && this.state.editPickId == question.pick_id)}/>
                  }
                </li>
                <li className='' style={{height: 55}}>
                  {
                      question.option_4 &&
                      <input label={question.option_4} type="radio" id="other" name={question.pick_id} onClick={(e)=>(status == 1 && (question.answer == 0 || question.answerPicked != 1 || editPickId == question.pick_id) && this.handleAnwer(e, question, 4))} value={question.option_4} 
                      checked={(question.answer == 4 &&  editPickId != question.pick_id) || (question.answerPicked == 1 && answer == 4 && this.state.editPickId == question.pick_id)}/>
                  }
                </li>
                
            </ul>
          }
          {
          status == 1 &&
            <div className='que-action'>
                {
                  question.answer != 0 ?
                  <>
                  {
                    ((question.explaination && question.explaination != '') || this.state.editPickId == question.pick_id || question.answerPicked != 1)  ?
                    // (this.state.editPickId == question.pick_id || (this.state.editPickId != question.pick_id && question.explaination && question.explaination != '')) ?
                    <a href className={`add-expl cursor-pointer ${question.explaination && question.explaination != '' ? ' link-view' : ''}`} onClick={()=>this.explanationModal(question)}>{question.explaination && question.explaination != '' ? <>Explanation</> : <><span><i className="icon-add"></i></span> Add explanation</>}</a>
                    :
                  <div className="visibility-hidden">add explanation</div>
                  }
                  </>
                  :
                  <div className="visibility-hidden">add explanation</div>
                }
                {
                    question.answerPicked != 1 ?
                    <Button disabled={question.answer == 0} onClick={()=>this.answerSubmit(question.pick_id,question.answer)} className="btn btn-ans">ANSWER </Button>
                    :
                    this.state.editPickId == question.pick_id ?
                    <Button onClick={()=>this.updateAnswer(question.pick_id,question.answer)} className="btn btn-ans">Update</Button> :
                    <Button onClick={()=>this.editAnswer(question.pick_id)} className="btn btn-ans">EDIT</Button>
                }
            </div>    
          }    
          { prevActiveTab == 3 &&
          <div className='que-action'>
              {
                question.explaination && question.explaination != '' ?
                  <a href className={`add-expl cursor-pointer link-view`} onClick={()=>this.explanationModal(question)}>Explanation</a>
                :
                <div className="visibility-hidden">add explanation</div>
              }
              <Button className="btn btn-ans btn-comp">Completed </Button>
              </div>
          }   
      </div>
    )
  }


  onChangeImage = (event) => {
    // this.setState({ UpLogoPosting: true });
    const file = event.target.files[0];
    if (!file) {
        return;
    }
    var data = new FormData();
    data.append("file_name", file);
    data.append("type", "explaination_image");
    WSManager.multipartPost(NC.baseURL + NC.PF_DO_UPLOAD_LOGO, data)
    .then(Response => {
        if (Response.response_code == NC.successCode) {
                this.setState({
                    uploadedImgName: Response.data.image_name,
                    uploadedImgUrl: Response.data.image_url,
                },()=>{
                  let activeQue = this.state.activeQue
                  activeQue['explaination_image']= this.state.uploadedImgName
                  this.setState({
                    activeQue: activeQue
                  })
                })
            }
            // this.setState({ UpLogoPosting: false })
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        });
}

    submitExplanation=()=>{
        let{activeQue} = this.state
        if(this.state.removeImg){
          this.removeApiCall()
        }
        let params = {
            explaination: activeQue.explaination,
            explaination_image: activeQue.explaination_image,         
            pick_id: activeQue.pick_id    
        }
        WSManager.Rest(NC.baseURL + NC.PF_UPDATE_EXPLANATION, params).then(Response => {
            if (Response.response_code == NC.successCode) {
              this.setState({
                removeImg: false,
                removeImgName: ''
              })
                this.toggleExplantionModal()
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }
    removeApiCall=()=>{
        let{removeImgName} = this.state
        let params = {
          type: 'explaination_image',
          file_name: removeImgName
        }
        WSManager.Rest(NC.baseURL + NC.PF_REMOVE_MEDIA, params).then(Response => {
            if (Response.response_code == NC.successCode) {
              this.setState({
                removeImg: false
              })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    saveTieBreaker = (e) => {
      let param = { answer: this.state.answerField, season_id: this.state.season_id ? this.state.season_id : '' }
      PF_SAVE_TIE_BREAKER(param).then((responseJson) => {
        if (responseJson.response_code === NC.successCode) {
          this.setState({
            storeSaveTieBreakerAnswer: responseJson.data,
              tiebreakerAnsMark: true
          })
          notify.show(responseJson.message, 'success')
        }
      }).catch((error) => {
        notify.show(NC.SYSTEM_ERROR, "error", 5000);
      })
    }

    removeFile=()=>{
      let tmpObj = this.state.activeQue
      let removeImgName = this.state.activeQue.explaination_image
      tmpObj['explaination_image']=''
      this.setState({
        activeQue:tmpObj,
        removeImgName: removeImgName,
        removeImg: true
      })
    }

    render() {
        let { contestObj, home_flag, away_flag, match, scheduled_date, league_name, activeTab, contestList, selected_sport,questionList, answerBtn, right_wrong,answer,editPickId,queCount,status,prevActiveTab,showExpModal,activeQue,storeSaveTieBreakerAnswer,tieBreakerData,tiebreakerAnsMark} = this.state

        return (
            
        <>
        <Row className='mt-56'>
          <Col sm={6}>
          <h3>Contest</h3>
          </Col>
          <Col sm={6}>
              {
                prevActiveTab != 1 && prevActiveTab != 3 &&
                <>
                  <Button className="btn-secondary mt-4 float-right"
                        onClick={() => this.redirectToCreateContest()}>
                        Create New Contest
                  </Button>
                  <Button className="btn-secondary mt-4 float-right mr-15"
                        onClick={() => this.cancelMatchModalToggle('',1,'-1','-1')}>
                        Cancel All Contest
                  </Button>
                </>
              }
          </Col>
        </Row>
        <Row>
          <Col sm={12}>
          <div className='contestListCutomHeading'>
            <div className='singleFixture contestListCutomHeadingLeft'>
                <div>
                    <img className="matchLogo" src={NC.S3 + NC.FLAG + home_flag}></img>
                </div>
                <div className='matchDetails'>
                    <span className='fixture-name'>{match ? match : 'TBA VS TBA'}</span>
                    <span className='fixture-time'>
                      {scheduled_date && 
                      // WSManager.getUtcToLocalFormat(scheduled_date, 'D-MMM-YYYY hh:mm A')
                      HF.getFormatedDateTime(scheduled_date, 'D-MMM-YYYY hh:mm A')

                      
                      }</span>                        
                    <span className='fixture-title'>{league_name && league_name}</span>
                </div>
                <div>
                    <img className="matchLogo" src={NC.S3 + NC.FLAG + away_flag}></img>
                </div>
            </div>
            
            <div className='w-100 pickem-contest'>
            {!_.isUndefined(tieBreakerData) && !_.isEmpty(tieBreakerData) && !_.isUndefined(tieBreakerData.tie_breaker_question.question) && <div className="pickem-card-set qa-card">
            <div className='questionary '>
              <p className='que'>Question</p>
              <p className='ans'>{tieBreakerData.tie_breaker_question.question == "" ? "-" : tieBreakerData.tie_breaker_question.question}</p>
              <p className='que'>Range</p>
              <p className='ans'>{tieBreakerData.tie_breaker_question.start}-{tieBreakerData.tie_breaker_question.end}</p>
            </div>
            <div className='pl-3 pr-3 correct-answer-section'>
              <p>Correct Answer</p>
              {(storeSaveTieBreakerAnswer != '' || tieBreakerData.tie_breaker_answer != "0" || (this.state.prevActiveTab == 2 || isUndefined(this.state.prevActiveTab))) ?
                <div className='flex-input'>
                  <Input type="number" name='answer' className='answer-input' value={storeSaveTieBreakerAnswer ? storeSaveTieBreakerAnswer : tieBreakerData.tie_breaker_answer} disabled />
                  <Button className="btn-secondary-outline disabled">Save</Button>
                </div>
                :
                <div className='flex-input'>
                  <Input type="number" name='answer' className='answer-input' onChange={(e) => this.setState({ answerField: e.target.value })} />
                  <Button className="btn-secondary-outline" onClick={this.saveTieBreaker} disabled={this.state.answerField=='' || tiebreakerAnsMark}>Save</Button>
                </div>}
            </div>

          </div>}
        </div>
        </div>  
            </Col>
        </Row>


        <Row>
          <Col sm={12}>
            <div className='questionAnswerBox'>
              <div className='questionAnswerBoxItem'>
                <p className='questionAnswerTitle'>Right Answer</p>
                <h4 className='questionAnswerValue'>{right_wrong.correct}</h4>
              </div>
              <div className='questionAnswerBoxItem'>
                <p className='questionAnswerTitle'>Wrong Answer</p>
                <h4 className='questionAnswerValue'>{parseInt(right_wrong.wrong) > 0 && '-'}{right_wrong.wrong}</h4>
              </div>
              <div className='questionAnswerBoxItem'>
                <p className='questionAnswerTitle'>No. of Question:</p>
                <h4 className='questionAnswerValue'>{queCount}</h4>
              </div>
            </div>
          </Col>
        </Row>
        <Row>
            <Col sm={12}>
                <div className='heading-flex'>
                  <span className='set-picks'>Set Pick</span>
                  <span className='back-to-fixture' onClick={() => {
                        this.props.history.push({
                          pathname: '/picksfantasy/fixture',
                          state:{
                            activeTab: this.state.prevActiveTab
                          }
                        })
                      }}>{'<'} Back to fixture</span>
                </div>
            </Col>
        </Row>
        <hr />
        {this.cancelMatchModal()}
        <Row>
            <Col sm={12}>
                <div className='mb-30'>
                    <Nav tabs>
                        <NavItem>
                            <NavLink
                            className={activeTab === '1' ? "active" : ""}
                            onClick={() => { this.toggleTab('1'); }}
                            >
                            <label className="live">Contest</label>
                            </NavLink>
                        </NavItem>
                        <NavItem>
                            <NavLink
                            className={activeTab === '2' ? "active" : ""}
                            onClick={() => { this.toggleTab('2'); }}
                            >
                            <label className="live">Questions</label>
                            </NavLink>
                        </NavItem>
                    </Nav>
                        <TabContent activeTab={activeTab}>
                            <TabPane tabId="1">
                            {contestList && contestList.map((item, group_index) => (
                            <div className="contest-group-container" key={group_index}>
                                <Row>
                                    <Col md="12" className="xanimate-left">
                                        <h4>{item.group_name}</h4>
                                    </Col>
                                    

                                    {item.template_list.map((contest, idx) => (
                                        <Col key={idx} md="4" className="xanimate-right">
                                            <div className="contest-group">
                                                <div className="contest-list-wrapper">
                                                  {/* <div className="contest-card more-contest-card sponsor-cls"> */}
                                                  <div className={"contest-card more-contest-card xsponsor-cls" + (contest.sponsor_logo ? ' sponsor-cls' : '')}>
                                                      <div className="contest-list contest-card-body">
                                                          <div className="pinned-area">
                                                          </div>
                                                          <div className="contest-list-header clearfix">
                                                                  <div className="contest-heading">
                                                                      <div className="action-head clearfix">
                                                                          <div onClick={() => this.props.history.push('/picksfantasy/contest_detail/' + contest.contest_unique_id)} className="contest-name text-ellipsis">{contest.contest_name}</div>
                                                                      </div>
                                                                  </div>
                                                              
                                                              <div className="clearfix">
                                                                  <ul className="ul-action con-action-list">
                                                                  {
                                                                      contest.status == '3' &&
                                                                      <li className="action-item">
                                                                      <i
                                                                          className="icon-cancel-key icon-reset"
                                                                          title="Revert Contest Prize"
                                                                          onClick={() => this.revertFxPrizeModal(contest.contest_id, 2, group_index, idx)}
                                                                      ></i>
                                                                      </li>
                                                                  }
                                                                  {
                                                                      contest.status == '0' &&
                                                                      <li className="action-item">
                                                                      <i
                                                                          className="icon-cross icon-cancel-key"
                                                                          title="Cancel Contest"
                                                                          onClick={() => this.cancelMatchModalToggle(contest.contest_unique_id, 2, group_index, idx)}
                                                                      ></i>
                                                                      </li>
                                                                  }
                                                                  {
                                                                          contest.guaranteed_prize == '2' &&
                                                                          <li className="action-item">
                                                                          <i className="icon-icon-g"></i>
                                                                          </li>
                                                                      }
                                                                      {
                                                                          contest.multiple_lineup > 1 &&
                                                                          <li className="action-item">
                                                                          <i className="icon-icon-m"></i>
                                                                          </li>
                                                                      }
                                                                      {
                                                                          contest.is_auto_recurring == "1" &&
                                                                          <li className="action-item">
                                                                          <i className="icon-icon-r contest-type"></i>
                                                                          </li>
                                                                      }
                                                                      {
                                                                          contest.is_reverse == "1" &&
                                                                          <li className="action-item">
                                                                          <img className="reverse-contest ml-0" title="Reverse contest" src={Images.REVERSE_FANTASY} />
                                                                          </li>
                                                                      }
                                                                      {
                                                                          contest.total_user_joined == 0 &&
                                                                          <li className="action-item">
                                                                          <i title="Delete Contest" className="icon-delete contest-type" onClick={(e) => this.deleteContest(e, contest, idx)}></i>
                                                                          </li>
                                                                      }

                                                                      {/* {
                                                                          
                                                                          (HF.allowScratchWin() == '1') &&
                                                                          <li className={`action-item ${fixtureDetail.match_started == 0 ? '' : 'cursor-dis'}`}>
                                                                          <i
                                                                              onClick={() => fixtureDetail.match_started == 0 ? this.addRemoveScWinModal(contest.contest_id, group_index, idx, contest.is_scratchwin) : null}
                                                                              id={"swin_" + group_index + '_' + idx} className={`icon-SW contest-type ${(contest.is_scratchwin == "1") ? '' : 'not-active'}`}>
                                                                              <span className="btn-information">
                                                                              <Tooltip
                                                                                  placement="right"
                                                                                  isOpen={contest.swin_tt}
                                                                                  target={"swin_" + group_index + '_' + idx}
                                                                                  toggle={() => this.SWinToggle(group_index, idx, 'swin_tt')}>
                                                                                  {SCRATCH_WIN_TAP}
                                                                              </Tooltip>
                                                                              </span>
                                                                          </i>
                                                                          </li>
                                                                      } */}
                                                                  </ul>
                                                              </div> 
                                                              <div className="clearfix">
                                                                  <h3 className="win-type">
                                                                  {
                                                                      (!_.isUndefined(contest.contest_title) && !_.isEmpty(contest.contest_title) && !_.isNull(contest.contest_title)) ?
                                                                      <span className="prize-pool-value">{contest.contest_title}</span>
                                                                      :
                                                                      <span>
                                                                          <span className="prize-pool-text">WIN </span>
                                                                          <span className="prize-pool-value" dangerouslySetInnerHTML={this.getPrizeAmount(contest.prize_distibution_detail)}>
                                                                          </span>
                                                                      </span>
                                                                  }
                                                                  </h3>
                                                              </div> 
                                                              <div className="text-small-italic">
                                                                  <span onClick={(e) => this.viewWinners(e, contest)}>{this.getWinnerCount(contest)}</span>
                                                                  <span className="b-allow">{contest.max_bonus_allowed ? contest.max_bonus_allowed : '0'}% Bonus allowed</span>
                                                              </div>

                                                              <div className="display-table">
                                                                  <div className="progress-bar-default display-table-cell v-mid">
                                                                      <div className="danger-area progress">
                                                                      <div className="text-center"></div>
                                                                      { <Progress value={this.ShowProgressBar(contest.total_user_joined, contest.size)} />}


                                                                      </div>
                                                                      <div className="progress-bar-value custom"><span className="user-joined">{contest.total_user_joined}</span><span className="total-entries"> / {contest.size} Entries</span><span className="min-entries">min {contest.minimum_size}</span></div>
                                                                  </div>
                                                                  <div className="display-table-cell v-mid entry-criteria">
                                                                      <button type="button" className="white-base btnStyle btn-rounded btn btn-primary">
                                                                      {
                                                                          contest.currency_type == '0' && contest.entry_fee > 0 &&
                                                                          <span>
                                                                          <i className="icon-bonus"></i>
                                                                          {HF.getPrizeInWordFormat(parseInt(contest.entry_fee))}
                                                                          {/* {contest.entry_fee} */}
                                                                          </span>
                                                                      }
                                                                      {
                                                                          contest.currency_type == '1' && contest.entry_fee > 0 &&
                                                                          // <span><i className="icon-rupess"></i>{contest.entry_fee}</span>
                                                                          <span>
                                                                          {HF.getCurrencyCode()}
                                                                          {HF.getPrizeInWordFormat(parseInt(contest.entry_fee))}
                                                                          {/* {contest.entry_fee} */}
                                                                          </span>
                                                                      }
                                                                      {
                                                                          contest.currency_type == '2' && contest.entry_fee > 0 &&
                                                                          <span>
                                                                          <img src={Images.COINIMG} alt="coin-img" />
                                                                          {HF.getPrizeInWordFormat(parseInt(contest.entry_fee))}
                                                                          {/* {contest.entry_fee} */}
                                                                          </span>
                                                                      }
                                                                      {
                                                                          contest.currency_type == '3' && contest.entry_fee > 0 &&
                                                                          <span>
                                                                          <img src={Images.COINIMG} alt="coin-img" />
                                                                          {HF.getPrizeInWordFormat(parseInt(contest.entry_fee))}
                                                                          {/* {contest.entry_fee} */}
                                                                          </span>
                                                                      }
                                                                      {contest.entry_fee == 0 &&

                                                                          <span>Free</span>

                                                                      }
                                                                      </button>
                                                                  </div>
                                                              </div>    

                                                          </div>  
                                                      </div>
                                                      {
                                                        contest.sponsor_logo &&
                                                        <div className="sponsor-box league-listing-wrapper pf-sponser-sec">
                                                          <div className="league-listing-container">
                                                            {/* <div className="sponsor-name">
                                                            <div>{contest.sponsor_logo ? "Sponsored by :" : 'Sponsor not assigned'}</div>
                                                          </div> */}
                                                            <div className="spr-card-img">
                                                              {
                                                                (contest.sponsor_logo && contest.sponsor_link) &&
                                                                <a target="_blank" href={contest.sponsor_link}>
                                                                  <img src={NC.S3 + NC.SPONSER_IMG_PATH + contest.sponsor_logo} alt="" />
                                                                </a>
                                                              }
                                                              {
                                                                (contest.sponsor_logo && contest.sponsor_link == null) &&
                                                                <img src={NC.S3 + NC.SPONSER_IMG_PATH + contest.sponsor_logo} alt="" />
                                                              }
                                                            </div>
                                                          </div>
                                                        </div>
                                                      }
                                                  </div>   
                                                </div> 
                                            </div> 
                                        </Col>
                                    ))}     

                                </Row>
                            </div>   
                            ))
                            }   
                            </TabPane>
                            <TabPane tabId="2">
                                <Row>
                                    <Col sm={12}>
                                    <div className='QuestionList'>
                                        { questionList &&
                                        
                                        questionList.map((question, idx) => ( 
                                            
                                             this.renderQueList(question,idx)
                                         
                                        ))}
                                          </div>
                                       
                                    </Col>
                                </Row>
                                {
                                  status == 1 &&
                                  <Row>
                                    <Col sm={12} className="text-center">
                                      <Button disabled={!this.isValidForComplete()} onClick={()=>this.markComplete()} className="btn btn-ans">Mark as Complete</Button>
                                    </Col>
                                  </Row>
                                }
                            </TabPane>
                        </TabContent>  
                </div>
            </Col>
        </Row>  

        <div className="winners-modal-container">
          <Modal isOpen={this.state.prize_modal} toggle={() => this.closePrizeModel()} className="winning-modal">
            <ModalHeader toggle={this.toggle}>Winnings Distribution</ModalHeader>
            <ModalBody>
              <div className="distribution-container">
                {
                  contestObj.prize_distibution_detail &&
                  <table>
                    <tbody>
                      <tr>
                        <th>Rank</th>
                        <th style={{ width: "100px", textAlign: "center" }}>Min</th>
                        <th style={{ width: "100px", textAlign: "center" }}>Max</th>
                      </tr>
                      {contestObj.prize_distibution_detail.map((prize, idx) => (
                        <tr key={idx}>
                          <td className="text-left">
                            {prize.min}
                            {
                              prize.min != prize.max &&
                              <span>-{prize.max}</span>
                            }
                          </td>
                          <td className="text-center">
                            {
                              prize.prize_type == '0' &&
                              <i className="icon-bonus"></i>
                            }
                            {
                              (!prize.prize_type || prize.prize_type == '1') &&
                              HF.getCurrencyCode()
                            }
                            {
                              prize.prize_type == '2' &&
                              <img src={Images.COINIMG} alt="coin-img" />
                            }
                            {
                              prize.prize_type == '3' &&
                              (prize.min_value + ' (' + parseInt(prize.mer_price * prize.min) + ')')
                            }
                            {
                              prize.prize_type != '3' &&
                              HF.getNumberWithCommas(prize.min_value)
                              // parseFloat(prize.min_value).toFixed(2)
                            }
                          </td>
                          <td className="text-center">
                            {
                              prize.prize_type == '0' &&
                              <i className="icon-bonus"></i>
                            }
                            {
                              (!prize.prize_type || prize.prize_type == '1') &&
                              HF.getCurrencyCode()
                            }
                            {
                              prize.prize_type == '2' &&
                              <img src={Images.COINIMG} alt="coin-img" />
                            }
                            {
                              prize.prize_type == '3' &&
                              (prize.max_value + ' (' + parseInt(prize.mer_price * prize.min) + ')')
                            }
                            {
                              prize.prize_type != '3' &&
                              HF.getNumberWithCommas(prize.max_value)
                              // parseFloat(prize.max_value).toFixed(2)
                            }
                          </td>
                        </tr>

                      ))}
                    </tbody>
                  </table>
                }
              </div>
            </ModalBody>
            <ModalFooter>
              <Button className="close-btn" color="secondary" onClick={() => this.closePrizeModel()}>Close</Button>
            </ModalFooter>
          </Modal>
        </div>
        <div className="explanation-modal-container">
          <Modal isOpen={showExpModal} toggle={() => this.toggleExplantionModal()} className="winning-modal explanation-modal">
            <ModalHeader toggle={this.toggleExplantionModal}>Add explanation</ModalHeader>
            <ModalBody>
              <div className="que-lbl-txt">Question</div>
              <div className="que-val-txt txt-primary">{activeQue.name}</div>
              <div className="que-lbl-txt">Answer Selected</div>
              <div className="que-val-txt">
                {
                  activeQue.answer == 1 ? activeQue.option_1 :
                    activeQue.answer == 2 ? activeQue.option_2 : 
                      activeQue.answer == 3 ? activeQue.option_3 : 
                      activeQue.option_4 
                      }
              </div>
              <div className="expl-sec">
                <label htmlFor="explaination">Explanation (maximum 150 characters)</label>
                <Input
                    type="textarea"
                    maxLength="150"
                    minLength="4"
                    className="match-msg"
                    id="explaination"
                    name="explaination"
                    placeholder="Write here..."
                    value={activeQue.explaination}
                    onChange={(e) => this.handleExpInput(e)}
                    required 
                    resize="3"
                    disabled={activeQue.answerPicked != 1 ? false : (this.state.editPickId == activeQue.pick_id ? false : true)}
                />
              </div>
              <div className="expl-sec mt-3">
                <label htmlFor="explaination_image">Explanation Image (520 X 200)</label>
                <div className="img-upld-sec">
                  {
                    (activeQue.answerPicked == 1 && (this.state.editPickId == '' || this.state.editPickId != activeQue.pick_id)) &&
                      <div className="disabled-overlay-block"></div>
                  }
                  {!_.isEmpty(activeQue.explaination_image) ?
                      <div className='upload-opt-img'>
                          <img className="img-cover" src={NC.S3 + NC.PICK_FANTASY + activeQue.explaination_image} />
                          {
                            (activeQue.answerPicked != 1 || 
                              (activeQue.answerPicked == 1 && this.state.editPickId == activeQue.pick_id )) &&
                            <div
                                onClick={this.removeFile}
                                className="dfs-remove-img close-btn">
                                Remove
                                </div>
                          }
                      </div>
                      :
                      
                      <div className='img-up'>
                          <Input
                              type="file"
                              name='explaination_image'
                              id="explaination_image"
                              className="img-up-ip"
                              onChange={(e)=>this.onChangeImage(e)}
                          />  
                                                          
                          <div className="dfs-upload" onChange={(e)=>this.onChangeImage(e)}>
                              <img className="def-addphoto" src={Images.DEF_ADDPHOTO} alt="" />
                              <div className="dfs-banner-sz">
                              Select image or drop here
                              </div>
                          </div>
                      </div>
                  }
                </div>
              </div>
            </ModalBody>
            <ModalFooter>
              {
                prevActiveTab != 3 &&
                // <Button className="close-btn" color="secondary" disabled={true}>Add</Button>
                // :activeQue.answerPicked != 1 ? false : (this.state.editPickId == activeQue.pick_id ? false : true)}
                <Button className="close-btn" color="secondary" onClick={() =>this.submitExplanation()} 
                disabled={(this.state.editPickId == activeQue.pick_id || activeQue.answerPicked != 1) ? ((activeQue.explaination && activeQue.explaination.length > 3) ? false : true) : true}>Add</Button>
              }
            </ModalFooter>
          </Modal>
        </div>

        </>
        )
    }
}
