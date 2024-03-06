import React, { Component, Fragment } from 'react';
import Select from 'react-select';
import {
  Col, Row, Button, TabContent, TabPane, Nav, NavItem, NavLink, Input, Modal, ModalBody, ModalHeader, ModalFooter,
} from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import moment from 'moment';
import _, { isEmpty } from 'lodash';
import LS from 'local-storage';
import Pagination from "react-js-pagination";
import { notify } from 'react-notify-toast';
import queryString from 'query-string';


import { STAR_CONFIRM_MSG, R_STAR_CONFIRM_MSG, SI_DATE, SI_NULL_DATE,DELAY_MSG_HEAD, DELAY_TIME_MSG_HEAD, ALREADY_TEAM_SELECTED,MSG_DELETE_FIXUTRE,MSG_CANCEL_REQ } from "../../helper/Message";
import HF, { _times, _Map, _isUndefined, _isEmpty, _cloneDeep, _isNull } from "../../helper/HelperFunction";
import DfsTCard from '../GameCenter/DFSTournament/DfsTCard';


import PFMatchDelayModal from './PFMatchDelayModal';
import Dfs_MatchAlertMsgModal from '../../components/Modals/Dfs_MatchAlertMsgModal';

import { DFST_getAllTournament, DFST_getTournamentParticipants, DFST_getTournamentLeaderboard, updateSIDate } from '../../helper/WSCalling';

import PromptModal from '../../components/Modals/PromptModal';
import ModalSecondInning from '../GameCenter/DFS/ModalSecondInning';

import Images from '../../components/images';
import SelectDate from "../../components/SelectDate";
import ConfirmActionModal from '../../components/Modals/ConfirmActionModal';
class Fixture extends Component {

  constructor(props) {
    super(props);
    let filter = {
      current_page: 1,
      items_perpage: 50,
      type: 1
    }
    this.state = {
      filter: filter,
      total: 0,
      clickedCard: '',
      // selectedSport: (LS.get('selectedSport')) ? LS.get('selectedSport') : '',
      leagueList: [],
      fixtureList: [],
      selected_league: "",
      fixture_status: "not_complete",
      activeTab: "2",
      msgModalIsOpen: false,
      DelayModalIsOpen: false,
      msgFormValid: true,
      MsgItems: {},
      fixtureObjData: {},
      Message: '',
      DelayHour: '',
      DelayMinute: '',
      DelayMessage: '',
      delayPosting: true,
      HourMsg: false,
      MinuteMsg: false,
      activeFixtureTab: "1",
      MiniLeActiveTab: "4",
      minileague_status: 'live',
      PERPAGE: NC.ITEMS_PERPAGE,
      DT_CURRENT_PAGE: 1,
      MODAL_PERPAGE: 10,
      MerchandiseList: [],
      prize_modal: false,
      StarModalOpen: false,
      StarItemIdx: '',
      StarMessage: '',
      StarCallFrom: '',
      deleteTeamModalOpen: false,

      fxPinPosting: false,
      siModalIsOpen: false,
      siPosting: false,
      isActiveAccordion: false, 
      sportsOptions:[],
      leagueOptions:[],
      selected_sport: LS.get('selectedSport') || '1',
      selected_league:'',
      league_id: '',
      team_A:'',
      team_B:'',
      team_idB:'',
      team_idA:'',
      CreatedDate: null,
      correct: '',
      question: '',
      wrong: '',
      QuestionsOptions: [],defaultQuestion:'',
      TodayDate: new Date(),
      cancel_reason: '',
      isFrom:'',
      Validedate: false,
      defaultCorrect: '',
      defaultWrong: '',
      activePinContest:''
    };
  }

  componentDidMount() {
    if(LS.get('queList')){
      LS.remove('queList')
    }
    this.getMasterData()
    this.getMerchandiseList()
    this.GetAllLeagueList();
    this.getSports()
   // this.setDateExtend()
    //this.getLeagues()
    this.isFrom()
    
    if(this.props && this.props.location && this.props.location.state){
      this.setState({
        activeTab: this.props.location.state.activeTab || '2'
      })
     
    }
  }
//   componentWillUnmount=()=>{
//     LS.remove('matchDetails')
// }

  isFrom = () =>{
    
    if(this.props && this.props.location && this.props.location.state){
      const {isFrom} = this.props.location.state;
      this.setState({
        activeTab: isFrom
      },()=>{})
    }
  }
  // GET ALL LEAGUE LIST
  GetAllLeagueList = () => {
    this.setState({
      posting: true
    })
    WSManager.Rest(NC.baseURL + NC.GET_ALL_LEAGUE_LIST, { "sports_id": this.state.selected_sport }).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        responseJson = responseJson.data;
        this.setState({
          posting: false
        }, () => {
          this.createLeagueList(responseJson);
          this.GetAllFixtureList();
        })
      } else if (responseJson.response_code == NC.sessionExpireCode) {
        WSManager.logout();
        this.props.history.push('/login');
      }

    })
  }
  // GET ALL LEAGUE LISTPF_GET_SPORTS
  getSports = () =>{
    let params = {}
    WSManager.Rest(NC.baseURL + NC.PF_GET_SPORTS, params).then((responseJson) => {
        if (responseJson.response_code == NC.successCode) {

            let sportsOptions= [];
            _.map(responseJson.data, function (data){
                        sportsOptions.push({
                            value: data.sports_id,
                            label: data.name,
                        })
               })
            this.setState({
                sportsOptions: sportsOptions,
                selected_sport: this.state.selected_sport ? this.state.selected_sport : sportsOptions[0],
                selected_league: '',
                leagueOptions: []
            },()=>{ 
               LS.set('selectedSport', this.state.selected_sport)
            this.getLeagues()})   
        }
    }).catch(error => {
        notify.show(NC.SYSTEM_ERROR, "error", 3000)
    })
}
getMasterData = () =>{
    let params = {}
    WSManager.Rest(NC.baseURL + NC.PF_MASTER_DATA, params).then((responseJson) => {
        if (responseJson.response_code == NC.successCode) {
            let allow_pick_data = responseJson.data.allow_pick_data
            let wrong = allow_pick_data.wrong.replace('-', '')
            this.setState({ 
              correct: allow_pick_data.correct,
              question: allow_pick_data.question,
              defaultQuestion: allow_pick_data.question,
              wrong: wrong,
              defaultCorrect: allow_pick_data.correct,
              defaultWrong: wrong,
            },()=>{this.QuestionsOptions()})   
        }
    }).catch(error => {
        notify.show(NC.SYSTEM_ERROR, "error", 3000)
    })
}
QuestionsOptions = () =>{
    let temArry = [];
      for(let i = 1; i < this.state.question; i++){
        temArry.push({
          value: i+1,
          label: i+1,
          });
      }
    this.setState({
      QuestionsOptions: temArry,
    })  
}
getLeagues = () =>{
  let sportsId =  LS.get('selectedSport') || '1'
    let params = {"sports_id": this.state.selected_sport ? this.state.selected_sport : sportsId}
    WSManager.Rest(NC.baseURL + NC.PF_GET_LEAGUE_LIST, params).then((responseJson) => {
        if (responseJson.response_code == NC.successCode) {
            let leagueOptions= [];
            _.map(responseJson.data, function (data){
                        leagueOptions.push({
                            value: data.league_id,
                            label: data.league_name
                        })
               })

            this.setState({
                leagueOptions: leagueOptions,
                selected_league: leagueOptions[0].value,
                league_id: leagueOptions[0].value,
            },()=>{
              this.getTeams()
            })   
        }
    })
}
getTeams = () =>{
  let params = {
    "league_id": this.state.league_id
  }
  WSManager.Rest(NC.baseURL + NC.GET_TEAM_BY_LEAGUE_ID_LIST, params).then((responseJson) => {
      if (responseJson.response_code == NC.successCode) {
          let teamsOptions= [];
          _.map(responseJson.data, function (data){
            teamsOptions.push({
                          value: data.team_id,
                          label: data.team_name,
                      })
             })
          this.setState({
            teamsOptions: teamsOptions,
          })   
      }
  }).catch(error => {
      notify.show(NC.SYSTEM_ERROR, "error", 3000)
  })
}
createFixture = () =>{
  let params = {
    "league_id":this.state.league_id,
    "home_id": this.state.team_idA,
    "away_id": this.state.team_idB,
    // "scheduled_date": "2022-10-15 09:55:29",
    "scheduled_date": moment.utc(this.state.CreatedDate).format("YYYY-MM-DD HH:mm:ss"),
    "question": this.state.question,
    "correct": this.state.correct,
    "wrong":this.state.wrong,
  }
  WSManager.Rest(NC.baseURL + NC.PF_ADD_FIXTURE, params).then((responseJson) => {
      if (responseJson.response_code == NC.successCode) {
        notify.show(responseJson.message, "success", 3000)
        this.setState({
          team_A:'',
          team_B:'',
          CreatedDate: null,
          question:this.state.defaultQuestion,
          team_idA:'',
          team_idB:'',
          correct: this.state.defaultCorrect,
          wrong: this.state.defaultWrong
        },()=>{
          setTimeout(() => {this.GetAllFixtureList()})
        })
      }
  }).catch(error => {
      notify.show(NC.SYSTEM_ERROR, "error", 3000)
  })
}


  createLeagueList = (list) => {
    let leagueArr = list;
    let tempArr = [{ value: "", label: "All" }];

    leagueArr.map(function (lObj, lKey) {
      tempArr.push({ value: lObj.league_id, label: lObj.league_abbr });
    });
    this.setState({ leagueList: tempArr });
  }

  // GET ALL FIXTURE LIST
  GetAllFixtureList = () => {
    let { selected_sport, selected_league, filter, fixture_status, activeTab} = this.state
    let selectedSport = LS.get("selectedSport") || '1'
    let param = {
      "sports_id": selected_sport ? selected_sport : selectedSport,
      "items_perpage": filter.items_perpage,
      "current_page": filter.current_page,
      "sort_order": (activeTab == '2') ? "ASC" : "DESC",
      "sort_field": "scheduled_date",
      "status": (activeTab == '1') ? '1' : (activeTab == '2') ? '0' : (activeTab == '3' ) ? '2' : '1',
      "type": activeTab,
    }
    this.setState({
      posting: true
    })

    WSManager.Rest(NC.baseURL + NC.PF_GET_ALL_FIXUTRE, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        let responseJsonData = responseJson.data;
        this.setState({
          posting: false,
          fixtureList: responseJsonData.fixture,
          total: responseJsonData.total
        })
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        WSManager.logout();
        this.props.history.push('/login');
      }

    })
  }

  // GET ALL MINILEAGUE LIST
  GetMiniLeagueList = () => {
    let param = { 
      "sports_id": this.state.selected_sport,
      "status": this.state.minileague_status,
    }
    this.setState({
      posting: true,
      miniLeagueList: []
    })

    WSManager.Rest(NC.baseURL + NC.GET_MINILEAGUE_LIST, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        let responseJsonData = responseJson.data;
        this.setState({
          posting: false,
          miniLeagueList: responseJsonData.fixtures,
          miniLeaguetotal: responseJsonData.total
        })
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        WSManager.logout();
        this.props.history.push('/login');
      }
    })
  }

  handleSelect = (value, dropName) => {
    if (value) {
      this.setState({ "selected_league": value.value }, function () {
        if (this.state.activeFixtureTab === '1')
          this.GetAllFixtureList();

        if (this.state.activeFixtureTab === '3')
          this.getAllTornament();
      });
    }
  }
  handleSports = (e) => {
    this.setState({
      sports_id: e.value,
      selected_sport: e.value,
      wrong: this.state.defaultWrong,
      correct: this.state.defaultCorrect,
      team_A:'',
      team_B:'',
      CreatedDate: null,
      question:this.state.defaultQuestion,
      team_idA:'',
      team_idB:'',
      selected_league: '',
        leagueOptions: []
    }, ()=>{ 
      LS.set('selectedSport', this.state.selected_sport)
      this.GetAllFixtureList();
      this.getLeagues()})
  }
  handleLeague = (e) => {
    this.setState({
      league_id: e.value,
      selected_league: e.value,
      team_A:'',
      team_B:'',
      CreatedDate: null,
      question:this.state.defaultQuestion,
      team_idA:'',
      team_idB:'',
    }, ()=>{ this.getTeams()})
  }

  handleTeam_A = (e) => {
    if(this.state.team_idB == e.value){
      notify.show(ALREADY_TEAM_SELECTED, "error", 3000)
      return
    }
    this.setState({
      team_idA: e.value,
      team_A: e.value,
    })
  }

  handleTeam_B = (e) => {
    if(this.state.team_idA == e.value){
      notify.show(ALREADY_TEAM_SELECTED, "error", 3000)
      return
    }
    this.setState({
      team_idB: e.value,
      team_B: e.value,
    })
  }
  handleQuestion = (e) => {
    this.setState({
      question: e.value,
    })
  }
  handleAnswer = (e) => {
    this.setState({
      correct: e.target.value,
      wrong: '' 
    })
  }
  handleWrong = (e) => {
    let correct = this.state.correct
    let value = e.target.value
    // value = value
    // if(correct >= value && value >= 0){
      // value = e.target.value
      this.setState({
        wrong: value
      })
    // }
  }
 

  redirectToSalaryReview = (selectedObj, event_type) => {
    if (selectedObj.is_published > 0 || event_type == 'live' || event_type == 'completed') {
      let tab = 1
      if (event_type == 'upcoming')
        tab = 2
      if (event_type == 'completed')
        tab = 3
      this.props.history.push({ pathname: '/contest/fixturecontest/' + selectedObj.league_id + '/' + selectedObj.season_game_uid + '/' + tab, state: { isCollection: true } })
    } else {
      this.props.history.push({ pathname: '/game_center/update-salary/' + selectedObj.league_id + '/' + selectedObj.season_game_uid })
    }
  }

  redirectToSelectPlayer = (selectedObj, event_type) => {
    if (selectedObj.is_published > 1 && event_type == 'live') {
      this.props.history.push({ pathname: '/contest/fixturecontest/' + selectedObj.league_id + '/' + selectedObj.season_game_uid + '/1' })
    } else {
      this.props.history.push({ pathname: '/game_center/Playing11/' + selectedObj.league_id + '/' + selectedObj.season_id })
    }
  }

  redirectToNewSalaryReview = (selectedObj, event_type) => {
    this.props.history.push({ pathname: '/game_center/update-salary/' + selectedObj.league_id + '/' + selectedObj.season_game_uid })
  }

  redirectToContestTemplate = (selectedObj, rdrt_to) => {    
    this.props.history.push({ 
      pathname: '/contest/createtemplatecontest/' + selectedObj.collection_master_id + '/' + selectedObj.season_id + '/2/1',
      state: { h2h_template: rdrt_to } 
    })
  }

  handlePageChange(current_page) {
    let filter = this.state.filter;
    filter['current_page'] = current_page;
    this.setState({
      filter: filter,
      fixtureList: []
    },
      function () {
        this.GetAllFixtureList();
      });

  }

  toggleTab(tab) {
    if (this.state.activeTab !== tab) {
      this.setState({
        activeTab: tab,
        PageScroll: false,
        filter: {
          current_page: 1,
          items_perpage: 50,
          type: tab
        },
        // fixture_status: (tab == 3) ? 2 : 'not_complete',
        fixtureList: [],
        DT_CURRENT_PAGE: 1,
      }, function () {
        if (this.state.activeFixtureTab === '1')
          this.GetAllFixtureList();
        if (this.state.activeFixtureTab === '3')
          this.getAllTornament();
      });
    }
  }

  toggleFixtureTab(tab) {
    if (this.state.activeFixtureTab !== tab) {
      this.setState({
        activeTab: '1',
        activeFixtureTab: tab,
        DfsT_List: [],
        // fixture_status: "not_complete",
        MiniLeActiveTab: '4',
        filter: {
          current_page: 1,
          items_perpage: 50,
          type: tab
        },
      }, () => {
        if (this.state.activeFixtureTab === '1')
          this.GetAllFixtureList();
        if (this.state.activeFixtureTab === '3') {
          this.getAllTornament()
          // this.getMerchandiseList()
        }
      });
    }
  }

  LeagueToggleTab(tab) {
    if (this.state.activeFixtureTab !== tab) {
      this.setState({
        MiniLeActiveTab: tab,
        minileague_status: (tab == 4) ? 'live' : (tab == 5) ? 'upcoming' : 'completed',
        minileagueList: []
      }, function () { this.GetMiniLeagueList(); });
    }
  }

  handleInputChange = (e) => {
    let name = e.target.name
    let value = e.target.value
    this.setState({ [name]: value })
    if (value.length > 0)
      this.setState({ msgFormValid: false })
    else
      this.setState({ msgFormValid: true })
  }

  handleFieldVal = (e) => {
    if (e) {
      let name = e.target.name;
      let value = e.target.value;
      let fixtureObjData = _cloneDeep(this.state.fixtureObjData);

      if (name == "delay_hour") {
        if (value.length < 3 && value >= 0 && value < 48) {
          this.setState({
            [name]: value
          })
          this.setState({ HourMsg: false })
        }
        else {
          fixtureObjData[name] = '';
          this.setState({ HourMsg: true })
        }
      }
      else if (name == "delay_minute") {
        if (value.length < 3 && value >= 0 && value < 60) {
          this.setState({
            [name]: value
          })
          this.setState({ MinuteMsg: false })
        }
        else {
          fixtureObjData[name] = '';
          this.setState({ MinuteMsg: true })
        }
      } else {
        if (name == "delay_message")
          this.setState({ [name]: value })
      }

      this.setState({
        fixtureObjData: fixtureObjData
      }, function () {
        if (name != 'custom_message') {
          this.calculateDeadline();
        }
      })
    }
  }

  calculateDeadline = () => {
    let { delay_hour, delay_minute } = this.state
    let fixtureObjData = _cloneDeep(this.state.fixtureObjData);
    var returned_endate = "";

    // if (delay_hour > 0 || delay_minute > 0) {

    var old = moment(fixtureObjData.scheduled_date).subtract(fixtureObjData.delay_hour, 'hours').subtract(fixtureObjData.delay_minute, 'minutes').format('DD-MMM-YYYY hh:mm A');

    var old_1 = moment(old).format("DD-MMM-YYYY hh:m A")
    // var old_1 = moment(WSManager.getUtcToLocal(old)).format("DD-MMM-YYYY hh:m A")

    var returned_endate = moment(old_1).add(delay_hour, 'hours').add(delay_minute, 'minutes').format('DD-MMM-YYYY hh:mm A');

    fixtureObjData['new_deadline'] = returned_endate;
    // }

    this.setState({ fixtureObjData: fixtureObjData });
  }

  openDelayModal = (item) => {
    this.setState({
      DelayModalIsOpen: !this.state.DelayModalIsOpen,
      fixtureObjData: item,
      delay_hour: item.delay_hour,
      delay_minute: item.delay_minute,
      delay_message: item.delay_message,
    });
  }

  saveDelayTime = () => {
    let { delay_hour, delay_minute, delay_message } = this.state

    let fixtureObjData = _cloneDeep(this.state.fixtureObjData);
    if (parseInt(delay_hour) < 0 || parseInt(delay_hour) > 48) {
      notify.show("Delay hours should be 0 to 48", "error", 5000);
      return false;
    }
    if (delay_minute == "" || parseInt(delay_minute) < 0 || parseInt(delay_minute) > 59) {
      notify.show("Delay minute should be 0 to 59", "error", 5000);
      return false;
    }
    if (delay_message == "" || (delay_message && delay_message.length > 160)) {
      notify.show("Delay message field required and max 160 characters", "error", 5000);
      return false;
    }
    if (delay_hour == null || delay_hour == '') {
      delay_hour = 0;
    }
    var params = {
      "season_game_uid": fixtureObjData.season_game_uid,
      "delay_hour": parseInt(delay_hour),
      "delay_minute": parseInt(delay_minute),
      "delay_message": delay_message,
      "league_id": fixtureObjData.league_id,
    };

    this.setState({ delayPosting: false })
    WSManager.Rest(NC.baseURL + NC.PF_UPDATE_FIXTURE_DELAY, params).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        this.setState({ delayPosting: true })
        this.GetAllLeagueList();
        this.openDelayModal(fixtureObjData)
        notify.show(responseJson.message, "success", 5000);
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        WSManager.logout();
        this.props.history.push('/login');
      } else {
        this.setState({ delayPosting: true })
      }
    })
  }

  getFormatedDate = (date) => {
    return moment(date).format('LLLL');
  }

  openMsgModal = (item) => {
    this.setState({
      msgModalIsOpen: !this.state.msgModalIsOpen,
      MsgItems: item,
      Message: item.custom_message,
    });
  }

  updateMatchMsg = (flag) => {
    let { MsgItems, Message } = this.state
    let param = {}
    if (flag == 1) {
      param = {
        "season_game_uid": MsgItems.season_game_uid,
        "custom_message": Message,
        "league_id": MsgItems.league_id,
      }
    } else {
      param = {
        "season_game_uid": MsgItems.season_game_uid,
        "custom_message": "",
        "is_remove": "1",
        "league_id": MsgItems.league_id,
      }
    }

    WSManager.Rest(NC.baseURL + NC.UPDATE_FIXTURE_CUSTOM_MESSAGE, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        notify.show(responseJson.message, "success", 5000);
        this.openMsgModal(MsgItems)
        this.GetAllLeagueList();
        this.setState({ Message: '' })
      } else if (responseJson.response_code == NC.sessionExpireCode) {
        notify.show(responseJson.message, "error", 5000);
      }

    })
  }

  leagueDisplayList = () => {
    return (
      <div className="ml-grid-item">
        <i className="icon-edit"></i>
        <div className="ml-grid-item-pd">
          <h3 className="h3-cls">Sydney Matches</h3>
          <a className="total-matches" href="javascript:void(0)">14 Matches</a>
        </div>
      </div>
    )
  }

  getMatchMsg = (status, status_overview) => {
    let msg = ''
    if (status == '0' || '1' || '2' || '3' || '4' || '5') {
      if (status_overview == '1') {
        msg = 'Rain Delay/Suspended';
      }
      else if (status_overview == '2') {
        msg = 'Abandoned';
      }
      else if (status_overview == '3') {
        msg = 'Canceled';
      }
    }
    return msg
  }

  redirectTornamentDtl = (tournament_id) => {
    this.props.history.push('/game_center/tournament-detail/' + tournament_id + '/' + this.state.activeTab)
  }

  redirectTornament = () => {

  }

  viewWinners = (e, templateObj) => {
    this.setState({
      prize_modal: !this.state.prize_modal,
      templateObj: templateObj
    });
  }

  DfsT_ldrbrdModal = (item) => {
    this.setState({
      DfsT_ID: item.tournament_id ? item.tournament_id : '',
      DfsT_LDRBRD_ITEM: item,
      DfsT_LDRBRD_CURRENT_PAGE: 1,
      DfsT_ldrbrdModalOpen: !this.state.DfsT_ldrbrdModalOpen
    }, () => {
      if (this.state.DfsT_ldrbrdModalOpen) {
        this.getTrnLeaderboardList()
      }
      else {
        this.setState({
          DfsT_LDRBRD_ITEM: [],
          DfsT_TotalLdrbrd: 0,
        })
      }
    })
  }

  getTrnParticipantList = () => {
    this.setState({ DfsT_PartiListPosting: true })
    let { DfsT_PARTI_CURRENT_PAGE, MODAL_PERPAGE, DfsT_ID } = this.state
    let params = {
      tournament_id: DfsT_ID,
      items_perpage: MODAL_PERPAGE,
      current_page: DfsT_PARTI_CURRENT_PAGE,
    }
    DFST_getTournamentParticipants(params).then(Response => {
      if (Response.response_code == NC.successCode) {
        this.setState({
          DfsT_ParticipantsList: Response.data.tournament_participants,
          DfsT_TotalParticipants: Response.data.total,
          DfsT_PartiListPosting: false
        })
      } else {
        notify.show(NC.SYSTEM_ERROR, 'error', 5000)
      }
    }).catch(error => {
      notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    })
  }

  getTrnLeaderboardList = () => {
    this.setState({ DfsT_LdrBrdPosting: true })
    let { DfsT_LDRBRD_CURRENT_PAGE, MODAL_PERPAGE, DfsT_ID } = this.state
    let params = {
      tournament_id: DfsT_ID,
      items_perpage: MODAL_PERPAGE,
      current_page: DfsT_LDRBRD_CURRENT_PAGE,
    }
    DFST_getTournamentLeaderboard(params).then(Response => {
      if (Response.response_code == NC.successCode) {
        this.setState({
          DfsT_ldrbrdList: Response.data.leaderboard_data,
          DfsT_TotalLdrbrd: Response.data.total,
          DfsT_LdrBrdPosting: false
        })
      } else {
        notify.show(NC.SYSTEM_ERROR, 'error', 5000)
      }
    }).catch(error => {
      notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    })
  }

  DfsT_usersModal = (item) => {
    this.setState({
      DfsT_ID: item.tournament_id ? item.tournament_id : '',
      DfsT_ITEM: item,
      DfsT_PARTI_CURRENT_PAGE: 1,
      DfsT_usersModalOpen: !this.state.DfsT_usersModalOpen
    }, () => {
      if (this.state.DfsT_usersModalOpen) {
        this.getTrnParticipantList()
      }
      else {
        this.setState({
          DfsT_ParticipantsList: [],
          DfsT_TotalParticipants: 0,
        })
      }
    })
  }

  getDfstCard = (list, tab_flag) => {
    return (
      _Map(list, (item, idx) => {
        return (
          <Col md={4} key={idx}>
            <DfsTCard
              activeTab={tab_flag}
              edit={false}
              listItem={item}
              redirectCallback={(pem_id) => this.redirectTornamentDtl(pem_id)}
              getPrizeCallback={(data) => HF.getPrizeAmount(data)}
              editCallback={() => this.redirectTornament()}
              viewWinnersCallback={(e, itemObj) => this.viewWinners(e, itemObj)}
              partiModalCallback={(data) => this.DfsT_usersModal(data)}
              ldrbrdModalCallback={(data) => this.DfsT_ldrbrdModal(data)}
            />
          </Col>
        )
      })
    )
  }

  getAllTornament = () => {
    let { selected_league, activeTab, selected_sport, DT_CURRENT_PAGE, PERPAGE } = this.state
    this.setState({ DfsT_Posting: true })
    let params = {
      sports_id: selected_sport,
      league_id: selected_league,
      match_type: activeTab,
      items_perpage: PERPAGE,
      current_page: DT_CURRENT_PAGE,
      sort_field: "scheduled_date",
      sort_order: "ASC"
    }

    DFST_getAllTournament(params).then(Response => {
      if (Response.response_code == NC.successCode) {
        this.setState({
          DfsT_List: (Response.data.result) ? Response.data.result : [],
          DfsT_Total: (Response.data.total) ? Response.data.total : 0,
          DfsT_Posting: false,
        })
      } else {
        notify.show(NC.SYSTEM_ERROR, 'error', 5000)
      }
    }).catch(error => {
      notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    })
  }

  DfsThandlePageChange(current_page) {
    if (current_page != this.state.DT_CURRENT_PAGE) {
      this.setState({
        DfsT_List: [],
        DT_CURRENT_PAGE: current_page,
      }, this.getAllTornament);
    }
  }

  getTouramentPagination = () => {
    let { DT_CURRENT_PAGE, PERPAGE, DfsT_Total } = this.state
    return (
      DfsT_Total > PERPAGE &&
      <Row className="mb-20">
        <Col md={12}>
          <div className="custom-pagination float-right">
            <Pagination
              activePage={DT_CURRENT_PAGE}
              itemsCountPerPage={PERPAGE}
              totalItemsCount={DfsT_Total}
              pageRangeDisplayed={5}
              onChange={e => this.DfsThandlePageChange(e)}
            />
          </div>
        </Col>
      </Row>
    )
  }

  getMerchandiseList = () => {
    let { PERPAGE, CURRENT_PAGE } = this.state
    let params = {
      sort_field: "added_date",
      sort_order: "DESC",
      items_perpage: PERPAGE,
      current_page: CURRENT_PAGE,
    }
    WSManager.Rest(NC.baseURL + NC.GET_MERCHANDISE_LIST, params).then(Response => {
      if (Response.response_code == NC.successCode) {
        this.setState({
          MerchandiseList: Response.data.merchandise_list
        })
      }
    }).catch(error => {
      notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    })
  }

  changeUserPagination = (current_page) => {
    if (this.state.DfsT_PARTI_CURRENT_PAGE !== current_page) {
      this.setState({
        DfsT_PARTI_CURRENT_PAGE: current_page
      }, () => {
        this.getTrnParticipantList()
      });
    }
  }

  handleTrnLdrBrdPageChange = (current_page) => {
    if (this.state.DfsT_LDRBRD_CURRENT_PAGE !== current_page) {
      this.setState({
        DfsT_LDRBRD_CURRENT_PAGE: current_page
      }, () => {
        this.getTrnLeaderboardList()
      });
    }
  }

  StarModalToggle = (idx, star_flg, call_from) => {
    let msg = (star_flg == '1') ? R_STAR_CONFIRM_MSG : STAR_CONFIRM_MSG
    this.setState({
      StarModalOpen: !this.state.StarModalOpen,
      StarItemIdx: idx,
      StarMessage: msg,
      StarCallFrom: call_from,
    });
  }

  updateStar = () => {
    const { StarItemIdx, fixtureList, StarCallFrom } = this.state
    let tempVar = fixtureList[StarCallFrom][StarItemIdx]
    const param = {
      season_game_uid: tempVar['season_game_uid'],
      highlight: tempVar['highlight'] == '1' ? '0' : '1',
    }

    let tempFixtureList = fixtureList
    // PT_deletePickem(param).then((responseJson) => {
    //   if (responseJson.response_code === NC.successCode) {

    tempFixtureList[StarCallFrom][StarItemIdx]['highlight'] = tempVar['highlight'] == '1' ? '0' : '1'

    this.StarModalToggle(StarItemIdx, '', StarCallFrom)
    notify.show('responseJson.message', "success", 5000);
    this.setState({
      fixtureList: tempFixtureList
    })
    //   }
    // }).catch((error) => {
    //   notify.show(NC.SYSTEM_ERROR, "error", 5000);
    // })
  }

  markPinFixture = () => {
    let { fxLeagueId, fxSeasonGameUid, fxIdx, fxPinVal, selected_sport } = this.state
    this.setState({ fxPinPosting: true })
    let params = {
      "league_id": fxLeagueId,
      "season_game_uid": fxSeasonGameUid,
      "sports_id": selected_sport,
    };
    if (fxPinVal == '1') {
      params.is_pin_fixture = '0'
    }

    WSManager.Rest(NC.baseURL + NC.MARK_PIN_FIXTURE, params).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        let fixtureList = _cloneDeep(this.state.fixtureList);
        fixtureList.upcoming_fixture[fxIdx]['is_pin_fixture'] = (fxPinVal == '1') ? '0' : '1'
        this.setState({ 'fixtureList': fixtureList, fxPinModalOpen: false });

        notify.show(responseJson.message, "success", 5000);
      } else {
        notify.show(responseJson.message, "error", 3000);
      }
      this.setState({ fxPinPosting: false })
    })
  }

  fxPinModal = (item, idx) => {
    let msg_status = (item.is_pin_fixture == '1') ? 'remove' : 'mark';
    let msg = 'Are you sure you want to ' + msg_status + ' pin ?'
    this.setState({
      fxPinModalOpen: !this.state.fxPinModalOpen,
      fxLeagueId: item.league_id,
      fxSeasonGameUid: item.season_game_uid,
      fxPinMsg: msg,
      fxIdx: idx,
      fxPinVal: item.is_pin_fixture,
    })
  }

  openSecInnModal = (item, idx) => {
    this.setState({
      siModalIsOpen: !this.state.siModalIsOpen,
      fixtureObjData: item,
      fxIdx: idx,
    });
  }

  saveSecInnTime = (si_date) => {
    if (_isNull(si_date)) {
      notify.show(SI_NULL_DATE, "error", 2000)
      return false;
    }
    else if (si_date <= this.state.todayDate) {
      notify.show(SI_DATE, "error", 2000)
      return false;
    }
    let tempData = this.state.fixtureList['live_fixture'][this.state.fxIdx]
    const utc_d = moment.utc(si_date).format("YYYY-MM-DD HH:mm:ss")
    let params = {
      "scheduled_date": utc_d,
      "season_game_uid": tempData.season_game_uid,
      "league_id": tempData.league_id,
    }

    this.setState({ siPosting: true })
    updateSIDate(params).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        notify.show(responseJson.message, "success", 5000);
        this.state.fixtureList['live_fixture'][this.state.fxIdx]['2nd_inning_date'] = utc_d
        this.setState({
          siPosting: false,
          siModalIsOpen: false,
        })
      }
      this.setState({
        siPosting: false,
        siModalIsOpen: false,
      })
    })
  }

  promoteFx = (val) => {
    let qStr = HF.promoteFixture(val)
    if (val.delay_minute > 0) {
      qStr.email_template_id = 7
    }
    const stringified = queryString.stringify(qStr);
    this.props.history.push(`/marketing/new_campaign?${stringified}`);
    return false;
  }

  redirectToBooster = (selectedObj, active_tab) => {
    // this.props.history.push({ pathname: '/contest/booster/' + selectedObj.league_id + '/' + selectedObj.season_game_uid + '/2/1' })
    this.props.history.push({ pathname: '/contest/booster/' + selectedObj.collection_master_id + '/' + selectedObj.season_id + '/' + active_tab + '/1' })
  }

  

  fxPinModal = (item, idx) => {
    let msg_status = (item.is_pin_fixture == '1') ? 'remove' : 'mark';
    let msg = 'Are you sure you want to ' + msg_status + ' pin ?'
    this.setState({
      fxPinModalOpen: !this.state.fxPinModalOpen,
      fxLeagueId: item.league_id,
      fxSeasonGameUid: item.season_game_uid,
      fxPinMsg: msg,
      fxIdx: idx,
      fxPinVal: item.is_pin_fixture,
    })
  }
  isActiveAccordion =()=>{
    this.setState({
      isActiveAccordion: !this.state.isActiveAccordion
    })
  }

  handleDate = (date, dateType) => {
        this.validateDate(date)
        this.setState({
          Validedate:  this.validateDate(date)
        })
        this.setState({ [dateType]: date }, () => {
            let time = this.state.TimeOption
            _Map(time, (item, idx) => {
                let disable = this.getTimeDisable(item.value)
                time[idx]['disabled'] = disable
            })
            this.setState({ TimeOption: time })

        })
    }
  validateDate=(date)=>{
    let TodayDate = new Date()
    TodayDate.setMinutes(TodayDate.getMinutes() + 5);
      if (date <= TodayDate) {
        notify.show('Please select time more than 5 min from current time', "error", 5000)
        return false;
      }
      else return true
  } 

    redirectToContestList= (item)=>{
      let fixtureDetails = [{
        league_id:item.league_id,
        season_id:item.season_id,
        sports_id:item.sports_id,
        season_game_uid:item.season_game_uid,
        away_flag:item.away_flag, 
        away_id: item.away_id, 
        home_id: item.home_id, 
        home_flag: item.home_flag,
        correct: item.correct,
        question: item.question,
        wrong: item.wrong,
        league_name: item.league_name,
        match: item.match,
        scheduled_date: item.scheduled_date,
        activeTab: this.state.activeTab,
        showtab: item.published == 1 ? 1 : 2
        }];
        localStorage.setItem("matchDetails", JSON.stringify(fixtureDetails));

        setTimeout(() => {
          this.props.history.push({
            pathname: '/picksfantasy/contest-list/'+ item.league_id + '/' + item.season_id,
            state: {
              league_id:item.league_id,
              season_id:item.season_id,
              sports_id:item.sports_id,
              season_game_uid:item.season_game_uid,
              away_flag:item.away_flag, 
              away_id: item.away_id, 
              home_id: item.home_id, 
              home_flag: item.home_flag,
              correct: item.correct,
              question: item.question,
              wrong: item.wrong,
              league_name: item.league_name,
              match: item.match,
              scheduled_date: item.scheduled_date,
              activeTab: this.state.activeTab,
            }
        })
        }, 10);
 
    }
    redirectToContestTemplate= (item)=>{
      let fixtureDetails = [{
        league_id:item.league_id,
        season_id:item.season_id,
        sports_id:item.sports_id,
        season_game_uid:item.season_game_uid,
        away_flag:item.away_flag, 
        away_id: item.away_id, 
        home_id: item.home_id, 
        home_flag: item.home_flag,
        correct: item.correct,
        question: item.question,
        wrong: item.wrong,
        league_name: item.league_name,
        match: item.match,
        scheduled_date: item.scheduled_date,
        }];
        localStorage.setItem("matchDetails", JSON.stringify(fixtureDetails));

        setTimeout(() => {
          this.props.history.push({
            pathname: '/picksfantasy/createtemplatecontest/'+ item.league_id + '/' + item.season_id,
            state: {
              league_id:item.league_id,
              season_id:item.season_id,
              sports_id:item.sports_id,
              season_game_uid:item.season_game_uid,
              away_flag:item.away_flag, 
              away_id: item.away_id, 
              home_id: item.home_id, 
              home_flag: item.home_flag,
              correct: item.correct,
              question: item.question,
              wrong: item.wrong,
              league_name: item.league_name,
              match: item.match,
              scheduled_date: item.scheduled_date,
            }
        })
        }, 10);
    }

    goToQuestions = (item) =>{
      let fixtureDetails = [{
        league_id:item.league_id,
        season_id:item.season_id,
        sports_id:item.sports_id,
        season_game_uid:item.season_game_uid,
        away_flag:item.away_flag, 
        away_id: item.away_id, 
        home_id: item.home_id, 
        home_flag: item.home_flag,
        correct: item.correct,
        question: item.question,
        wrong: item.wrong,
        league_name: item.league_name,
        match: item.match,
        scheduled_date: item.scheduled_date,
        }];
        localStorage.setItem("matchDetails", JSON.stringify(fixtureDetails));


      let params = {
        "season_id":item.season_id
      }
      WSManager.Rest(NC.baseURL + NC.PF_GET_QUESTION_LIST_BY_ID, params).then((responseJson) => {
        if (responseJson.response_code === NC.successCode) {
          let saveDraftsData = responseJson.data
         

          if(!_isEmpty(saveDraftsData)){
            LS.set('queList', saveDraftsData)
          }

          setTimeout(() => {
            this.props.history.push({
              pathname: '/picksfantasy/publish_match/'+ item.league_id + '/' + item.season_id,
              state: {
                league_id:item.league_id,
                season_id:item.season_id,
                sports_id:item.sports_id,
                season_game_uid:item.season_game_uid,
                away_flag:item.away_flag, 
                away_id: item.away_id, 
                home_id: item.home_id, 
                home_flag: item.home_flag,
                correct: item.correct,
                question: item.question,
                wrong: item.wrong,
                league_name: item.league_name,
                match: item.match,
                scheduled_date: item.scheduled_date,
              }
          })
          }, 10);
        }
      })

      // Set data into local storage PF_GET_QUESTION_LIST_BY_ID
   
  }  
  deleteTeam = () => {
    let { season_id, league_id } = this.state 
    this.setState({ deletePosting: true })
    let params = {
        "season_id": season_id,
        "league_id": league_id,
    }
    // deleteTeam(params).then(ResponseJson => {
    //     if (ResponseJson.response_code == NC.successCode) {
    //         this.setState({ 
    //             deleteTeamModalOpen: !this.state.deleteTeamModalOpen,
    //             deletePosting: false ,
    //         })
    //         this.getLeagueList()
    //         notify.show(ResponseJson.message, "success", 3000)
    //     }else{
    //         this.setState({ 
    //             deleteTeamModalOpen: !this.state.deleteTeamModalOpen,
    //             deletePosting: false ,
    //         })
    //     }
    // }).catch(error => {
    //     this.deleteTeamToggle()
    //     this.setState({ deletePosting: false })
    //     notify.show(NC.SYSTEM_ERROR, "error", 3000)
    // })
}

  deleteTeamToggle=(item)=>{
    
    this.setState({ 
        season_id: item.season_id,
        league_id: item.league_id,
        deleteTeamModalOpen: !this.state.deleteTeamModalOpen 
    })
    
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

  cancelMatch = () => {
    let {CancelReason} = this.state
    this.setState({ CancelPosting: false });
    let param = {
      "season_id": this.state.season_id,
      // cancel_reason: CancelReason,
      // "outside_season" :"1"
      // collection_id:this.state.collection_id
    };

    WSManager.Rest(NC.baseURL + NC.PF_DELETE_FIXTURE, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        notify.show(responseJson.message, "success", 5000);
        this.setState({
          deleteTeamModalOpen: !this.state.deleteTeamModalOpen
        },()=>{
          setTimeout(() => {this.GetAllFixtureList()},10)
          })
      }
    })
  }


  deleteTeamModal = () => {
    let { deletePosting, deleteTeamModalOpen } = this.state
    return (
        <div className='canceLMod'>
           <Modal isOpen={this.state.deleteTeamModalOpen}
              toggle={this.cancelMatchModalToggle}
              className="cancel-match-modal"
            >
          <ModalHeader className="simpleflex">
                        <span className="h3-cls" style={{width: '100%'}}>Confirmation</span>
                        <span style={{float:'right'}} onClick={()=>{this.setState({deleteTeamModalOpen: !this.state.deleteTeamModalOpen})}}><i className="icon-close"></i></span>
                    </ModalHeader>
          <ModalBody>
            <div className="confirm-msg">{MSG_DELETE_FIXUTRE}</div>
            {/* <div className="inputform-box">
              <label>Cancel Reason</label>
              <Input
                minLength="3"
                maxLength="160"
                rows={3}
                type="textarea"
                name="CancelReason"
                onChange={(e) => this.handleInputChange(e)}
              />
            </div> */}
          </ModalBody>
          <ModalFooter>
            <Button
              color="secondary"
              onClick={this.cancelMatch}
            >Yes</Button>{' '}
            <Button color="primary" onClick={()=>{this.setState({deleteTeamModalOpen: !this.state.deleteTeamModalOpen})}}>No</Button>
          </ModalFooter>
        </Modal>
    </div>
    )
}
setDateExtend(){
  var d;
d = new Date();
d.setMinutes(d.getMinutes() + 5);
this.setState({
  CreatedDate: d
},()=>{})
}

openConfirmModal=(listItem)=>{
  this.setState({
    showConfirmModal: true,
    activePinContest: listItem
  })
}

hideConfirmModal=()=>{
  this.setState({
    showConfirmModal: false,
    activePinContest: ''
  })
}


pinContest=(item)=>{
  let params = {
    "season_id": item.season_id,
  }
 
  WSManager.Rest(NC.baseURL + NC.PF_MARK_PIN_FIXTURE, params).then((responseJson) => {
    if (responseJson.response_code === NC.successCode) {
      notify.show(responseJson.message, "success", 5000);
      this.hideConfirmModal()
      this.GetAllFixtureList();
    }
  })
}

  render() {
    const {  TodayDate, QuestionsOptions, team_A, team_B, CreatedDate, teamsOptions, selected_Sport, selected_league, leagueOptions, sportsOptions, isActiveAccordion, MiniLeActiveTab, activeFixtureTab, activeTab, total, leagueList, fixtureList, filter, DfsT_List, DfsT_Total, DfsT_Posting, DfsT_usersModalOpen, DfsT_ParticipantsList, DfsT_PartiListPosting, DfsT_PARTI_CURRENT_PAGE, MODAL_PERPAGE, DfsT_TotalParticipants, DfsT_ldrbrdModalOpen, DfsT_ldrbrdList, DfsT_LdrBrdPosting, DfsT_LDRBRD_CURRENT_PAGE, DfsT_TotalLdrbrd, DfsT_ITEM, prize_modal, MerchandiseList, fixture_status, templateObj, StarModalOpen, StarPosting, StarMessage, DelayModalIsOpen, delayPosting, fixtureObjData, delay_hour, delay_minute, delay_message, HourMsg, MinuteMsg, msgModalIsOpen, msgFormValid, MsgItems, Message, DfsT_LDRBRD_ITEM, fxPinModalOpen, fxPinPosting, fxPinMsg, siModalIsOpen, siPosting, selected_sport,correct,showConfirmModal } = this.state;
    const sameDateProp = { 
    show_time_select: true, 
    time_format: "HH:mm",
    time_intervals: 5,
    time_caption: "time",
    date_format: 'dd/MM/yyyy h:mm aa',
    handleCallbackFn: this.handleDate,
    class_name: 'Select-control inPut icon-calender',
    year_dropdown: true,
    month_dropdown: true,
    className: ''
  }

  const DateProps = {
      ...sameDateProp,
      min_date: new Date(TodayDate),
      max_date: null,
      sel_date: CreatedDate,
      date_key: 'CreatedDate',
      place_holder: 'Select Date',
      // className: 'icon-calender Ccalender'
  }
    


 
    let DfsT_usersProps = {
      usersModalOpen: DfsT_usersModalOpen,
      closeUserListModal: this.DfsT_usersModal,
      ListItem: DfsT_ITEM,
      ParticipantsList: DfsT_ParticipantsList,
      PartiListPosting: DfsT_PartiListPosting,
      PARTI_CURRENT_PAGE: DfsT_PARTI_CURRENT_PAGE,
      PERPAGE: MODAL_PERPAGE,
      TotalParticipants: DfsT_TotalParticipants,
      handleUsersPageChange: this.changeUserPagination,
      activeTab: activeTab
    }

    let DfsT_ldrbrdProps = {
      usersModalOpen: DfsT_ldrbrdModalOpen,
      closeUserListModal: this.DfsT_ldrbrdModal,
      ListItem: DfsT_LDRBRD_ITEM,
      ParticipantsList: DfsT_ldrbrdList,
      PartiListPosting: DfsT_LdrBrdPosting,
      PARTI_CURRENT_PAGE: DfsT_LDRBRD_CURRENT_PAGE,
      PERPAGE: MODAL_PERPAGE,
      TotalParticipants: DfsT_TotalLdrbrd,
      handleUsersPageChange: this.handleTrnLdrBrdPageChange,
      activeTab: activeTab,
    }

    let DfsT_ViewWinnersProps = {
      modalisOpen: prize_modal,
      templateObj: templateObj,
      merchandise_list: MerchandiseList,
      PrizeModelCallback: this.viewWinners,
    }

    let ConfirmStarProps = {
      publishModalOpen: StarModalOpen,
      publishPosting: StarPosting,
      modalActionNo: this.StarModalToggle,
      modalActionYes: this.updateStar,
      MainMessage: StarMessage,
      SubMessage: '',
    }

    let MatchDelayAlertMsgProps = {
      DelayModalIsOpen: DelayModalIsOpen,
      delayPosting: delayPosting,
      modalActionNo: this.openDelayModal,
      modalActionYes: this.saveDelayTime,
      fixtureObjData: fixtureObjData,
      delay_hour: delay_hour,
      delay_minute: delay_minute,
      delay_message: delay_message,
      HourMsg: HourMsg,
      MinuteMsg: MinuteMsg,
      handleFieldVal: this.handleFieldVal,
    }

    let MatchAlertMsgProps = {
      msgModalIsOpen: msgModalIsOpen,
      msgFormValid: msgFormValid,
      openMsgModal: this.openMsgModal,
      handleInputChange: this.handleInputChange,
      updateMatchMsg: this.updateMatchMsg,
      MsgItems: MsgItems,
      Message: Message,
    }

    let fxPinModalProps = {
      publishModalOpen: fxPinModalOpen,
      publishPosting: fxPinPosting,
      modalActionNo: this.fxPinModal,
      modalActionYes: this.markPinFixture,
      MainMessage: fxPinMsg,
      SubMessage: '',
    }

    let second_inni_mdl_props = {
      ModalIsOpen: siModalIsOpen,
      Posting: siPosting,
      openModal: this.openSecInnModal,
      modalActionYes: (si_date) => this.saveSecInnTime(si_date, fixtureObjData.season_game_uid, fixtureObjData.league_id),
      MsgItems: fixtureObjData,
      handleFieldVal: this.handleSecInnFieldVal,
      // msgFormValid: msgFormValid,
      msgFormValid: false,
    }
    return (
      
      <div className="animated fadeIn dfs-main">
        {/* {DfsT_usersModalOpen && <DfsT_ParticipantsModal {...DfsT_usersProps} />}
        {DfsT_ldrbrdModalOpen && <DfsT_LeaderBoardModal {...DfsT_ldrbrdProps} />}
        {prize_modal && <ViewWinners {...DfsT_ViewWinnersProps} />} */}
        {StarModalOpen && <PromptModal {...ConfirmStarProps} />}
        {DelayModalIsOpen && <PFMatchDelayModal {...MatchDelayAlertMsgProps} />}
        {msgModalIsOpen && <Dfs_MatchAlertMsgModal {...MatchAlertMsgProps} />}
        {fxPinModalOpen && <PromptModal {...fxPinModalProps} />}
        {siModalIsOpen && <ModalSecondInning {...second_inni_mdl_props} />}
        {this.deleteTeamModal()}
        <Row>
          <Col md={12}>
            <div className='fixtureDrop'>
              <div className='fixtureSlections'>
                <div className='fixtureDropItem'>
                <label className="filter-label">Select Sports </label>
                    <Select
                    className="mr-15"
                    id="selected_sport"
                    name="selected_sport"
                    placeholder="Select Sport"
                    value={this.state.selected_sport}
                    options={sportsOptions}
                    onChange={(e) => this.handleSports(e)}
                  />
                </div> 
                
                <div className='fixtureDropItem'>
                <label className="filter-label">Select League </label>

                  <Select
                    className=""
                    id="selected_league"
                    name="selected_league"
                    placeholder="Select League"
                    value={this.state.selected_league}
                    options={leagueOptions}
                    onChange={(e) => this.handleLeague(e)}
                  />
                </div>
              </div>  
              <div className='fixtureBtn'>
                  <Button onClick={() => this.props.history.push('/picksfantasy/leagues')} className="add-button-pick ml-3">Add League</Button>
                  <Button onClick={() => this.props.history.push({pathname:'/picksfantasy/leagues',state: {showLeaguePopup: true}})} className="add-button-pick ml-3"> Add / View Sports</Button>
              </div>
            </div>
          </Col>
        </Row>
        <Row>
          <Col md={12}>
              <div className='AddFixtureBox'>
                <div className='AddFixtureBoxItem'>
                  <span className='AddFixtureTxt' onClick={()=>this.isActiveAccordion()}>Add Fixtures</span>
                  <span className='CursorPointer' onClick={()=>this.isActiveAccordion()}><i className={!isActiveAccordion ? 'icon-addmore' : 'icon-minus'}></i> </span>
                </div> 
                
              {
                isActiveAccordion && 
                  <>
                  <hr />
                  <div className='addFixturesOptions'>
                    <div className='addFixturesOptionsItem'>
                      <div className='inputFields'>
                        <label className="filter-label">Select Team / Players A  </label>
                          <Select
                            className="inPut"
                            id="team_A"
                            name="team_A"
                            placeholder="Select Team A"
                            value={this.state.team_A}
                            options={teamsOptions}
                            onChange={(e) => this.handleTeam_A(e)}
                          />
                        </div>  
                    </div>
                    <div className='addFixturesOptionsItem'>
                     <div className='inputFields'>
                        <label className="filter-label">Select Team / Players B</label>
                          <Select
                            className="inPut"
                            id="team_B"
                            name="team_B"
                            placeholder="Select Team B"
                            value={this.state.team_B}
                            options={teamsOptions}
                            onChange={(e) => this.handleTeam_B(e)}
                          />
                      </div>    
                    </div>
                    <div className='addFixturesOptionsItem'>
                      <div className='inputFields inPutBg'>

                        <label className="filter-label" htmlFor="CandleDetails">Select Date / Time</label>
                        <>
                          <SelectDate DateProps={DateProps} />
                          <i className='icon-calender Ccalender'></i>
                       </>
                        
                        
                      </div>  
                    </div>
                    <div className='addFixturesOptionsItem'>
                      <div className='inputFields'>
                        <label className="filter-label">Questions </label>
                        
                        <Select
                          className="inPut"
                          id="selected_league"
                          name="selected_league"
                          placeholder="Select League"
                          value={this.state.question}
                          options={QuestionsOptions}
                          onChange={(e) => this.handleQuestion(e)}
                         
                        />
                      </div>  
                    </div>
                    <div className='addFixturesOptionsItem'>
                      <div className='inputFields'>
                        <label className="filter-label">Right Answer </label>
                          <Input
                              type="text"
                              name="correct"
                              value={this.state.correct}
                              className='Select-control inPut'
                              onChange={(e) => this.handleAnswer(e)}
                              // onChange={(e) => this.handleInputChange(e)}
                          />
                      </div>    
                    </div>
                    <div className='addFixturesOptionsItem'>
                      <div className='inputFields' style={{position: 'relative'}}>
                        <label className="filter-label">Wrong Answer </label>
                          <Input
                              type="text"
                              name="wrong"
                              className='Select-control inPut'
                              value={this.state.wrong}
                              onChange={(e) => this.handleWrong(e)}
                              
                          />
                          <span className='negative-symbol'>-</span>
                          {
                            (parseInt(correct) < parseInt(this.state.wrong)) && 
                            <div className="error-msg" style={{color: 'red'}}> 
                          Wrong Ans should be in between 0 to -{correct}</div>
                          }
                      </div>    
                    </div>
                    <div className='addFixturesOptionsItem'>
                      <div className='inputFields addButton'>
                        <Button disabled={isEmpty(team_A) || isEmpty(team_B) || !this.state.Validedate} className="btn-secondary-outline"
                              // disabled={!leagueLenght}
                              onClick={(e)=>this.createFixture()}>Save</Button>
                      </div>        
                    </div>
                   
                  </div>
                </>  
                }
                </div>
          </Col>
        </Row>
        <Row>
          <Col md={12}>
            <div className="user-navigation mb-30">
                  <Row className="dfs-tabs">
                    <Col md={12}>
                      <Nav tabs>
                        <NavItem>
                          <NavLink
                            className={activeTab === '1' ? "active" : ""}
                            onClick={() => { this.toggleTab('1'); }}
                          >
                            <label className="live">Live</label>
                          </NavLink>
                        </NavItem>
                        <NavItem>
                          <NavLink
                            className={activeTab === '2' ? "active" : ""}
                            onClick={() => { this.toggleTab('2'); }}
                          >
                            <label className="live">Upcoming</label>
                          </NavLink>
                        </NavItem>
                        <NavItem>
                          <NavLink
                            className={activeTab === '3' ? "active" : ""}
                            onClick={() => { this.toggleTab('3'); }}
                          >
                            <label className="live">Completed</label>
                          </NavLink>
                        </NavItem>
                      </Nav>
                      <TabContent activeTab={activeTab}>
                        <TabPane tabId="1">
                          <Row>
                            <Col sm="12">
                             
                                <div>

                                  {/* LIVE FIXTURE LIST START */}
                                  <Row>
                                    <Col lg={12} className="cardlivcol">
                                      {
                                        !_isEmpty(fixtureList) 
                                          ?
                                          _Map(fixtureList, (item, index) => {
                                            var mEndDate = new Date(WSManager.getUtcToLocal(item['2nd_inning_date']));
                                            var curDate = new Date();
                                            let compDate = false;
                                            if (curDate >= mEndDate) {
                                              compDate = true;
                                            }
                                            return (
                                              <div className={`flip-animation common-fixture${(item.highlight == '1') ? ' mth-star' : ''}`} key={"live-fixtures-" + index}>
                                                <div className="bg-card pf-bg-card">
                                                {
                                                   item.published == 1 && 
                                                   <>
{
  (item.is_pin_fixture == '1') ?
  <img onClick={() => this.openConfirmModal(item)} src={Images.PIN_ACTIVE} alt="" className="pinned-active" />
  : <span className="pin-tour" onClick={()=>this.openConfirmModal(item)}>
      <i className="icon-pinned"></i>
  </span>
}
                                                   </>
                                    
                                }
                                                  <div className="dfs-mn-hgt">
                                                    <img className="com-fixture-flag float-left" src={NC.S3 + NC.FLAG + item.home_flag}></img>
                                                    <img className="com-fixture-flag float-right" src={NC.S3 + NC.FLAG + item.away_flag}></img>
                                                    <div className="com-fixture-container">
                                                      <div className="com-fixture-name" onClick={() => this.redirectToContestList(item)}>{(item.home) ? item.home : 'TBA'} VS {(item.away) ? item.away : 'TBA'}</div>
                                                      {
                                                        <div className="com-fixture-time">
                                                          {/* {WSManager.getUtcToLocalFormat(item.scheduled_date, 'D-MMM-YYYY hh:mm A')} */}
                                                          {HF.getFormatedDateTime(item.scheduled_date, 'D-MMM-YYYY hh:mm A')}
                                                        </div>
                                                      }
                                                      
                                                      <div className="com-fixture-title">{item.league_name}</div>
                                                    </div>
                                                  </div>
                                                </div>
                                              </div>
                                            )
                                          })
                                          :  <Col md={12}>
                                          <div className="no-records">No Record Found.</div>
                                        </Col>
                                      }
                                    
                                    </Col>
                                  </Row>

                                </div>
                          
                            </Col>
                          </Row>
                        </TabPane>
                        <TabPane tabId="2">
                          <Row>
                            <Col sm="12">
                              {/* UPCOMING FIXTURE LIST START */}
                              <Row className="cardupcomingrow">
                                <Col lg={12} className="cardupcomingcol">
                                  {
                                    !_isEmpty(fixtureList)
                                      ?
                                      _Map(fixtureList, (item, index) => {
                                        return (
                                          <div className={`flip-animation common-fixture${(item.highlight == '1') ? ' mth-star' : ''}`} key={"upcoming-fixtures-" + index}>
                                            {
                                              item.is_published == '1' &&
                                              <div style={{ zIndex: 1, cursor: 'pointer', position: 'absolute', marginLeft: item.is_pin_fixture == '1' ? '0px' : '10px' }}>
                                                {
                                                  item.is_pin_fixture == '1' &&
                                                  <img onClick={(e) => this.fxPinModal(item, index)} src={Images.PIN_ACTIVE} alt="" className="pinned-active" />
                                                }
                                                {
                                                  item.is_pin_fixture == '0' &&
                                                  <i onClick={(e) => this.fxPinModal(item, index)} className="icon-pinned"></i>
                                                }
                                              </div>
                                            }
                                            <div className="bg-card pf-bg-card">
                                            {
                                              item.published == 1 && 
                                              <>
                                              {
                                                 (item.is_pin_fixture == '1') ?
                                                 <img onClick={() => this.openConfirmModal(item)} src={Images.PIN_ACTIVE} alt="" className="pinned-active" />
                                                 : <span className="pin-tour" onClick={()=>this.openConfirmModal(item)}>
                                                     <i className="icon-pinned"></i>
                                                 </span>
                                              }
                                              </>
                                   
                                }
                                              <div className="dfs-mn-hgt">
                                                <img className="com-fixture-flag float-left" src={NC.S3 + NC.FLAG + item.home_flag}></img>
                                                <img className="com-fixture-flag float-right" src={NC.S3 + NC.FLAG + item.away_flag}></img>
                                                <div className="com-fixture-container">
                                                  <div className="com-fixture-name" onClick={() =>(item.published == 0 ?  this.goToQuestions(item) : this.redirectToContestList(item))}>{(item.home) ? item.home : 'TBA'} VS {(item.away) ? item.away : 'TBA'}</div>
                                                  <div className="com-fixture-time xlivcardh6">
                                                    {/* {WSManager.getUtcToLocalFormat(item.scheduled_date, 'D-MMM-YYYY hh:mm A')} */}
                                                    {HF.getFormatedDateTime(item.scheduled_date, 'D-MMM-YYYY hh:mm A')}
                                                  </div>
                                                  <div className="com-fixture-title xlivcardh6">{item.league_name}</div>
                                                </div>
                                              </div>
                                                <ul className="fx-action-list">
                                                  {
                                                    item.published =='0' &&
                                                    <li className="fx-action-item">
                                                      <i
                                                        title="Publish Match"
                                                        className="icon-Salary-update"
                                                        onClick={() => this.goToQuestions(item)}
                                                      ></i>
                                                    </li>
                                                  }
                                                  {
                                                    item.published =='1' &&
                                                    <li className="fx-action-item">
                                                      <i
                                                        title="Published"
                                                        className="icon-fixture-contest"
                                                        onClick={() => this.redirectToContestList(item)}
                                                      ></i>
                                                    </li>
                                                  }
                                                  {
                                                    item.published =='1' &&
                                                    <li className="fx-action-item">
                                                      <i
                                                        title="Contest Template"
                                                        className="icon-template"
                                                        onClick={() => this.redirectToContestTemplate(item)}
                                                      ></i>
                                                  </li>
                                                  }
                                                    
                                                   
                                                      <li className="fx-action-item">
                                                        <i
                                                          title="Mark match delay"
                                                          className="icon-delay"
                                                          onClick={() => this.openDelayModal(item)}
                                                        ></i>
                                                      </li>
                                                      {
                                                        item.is_contest == '0' && 
                                                        <li
                                                          className="fx-action-item"
                                                          onClick={() => this.deleteTeamToggle(item)}
                                                          >
                                                          <i className="icon-delete"
                                                            title="Delete Fixture"></i>
                                                        </li>
                                                      }
                                                      {/* <li
                                                      className="fx-action-item"
                                                      onClick={() => this.deleteTeamToggle(item)}
                                                      >
                                                      <i className="icon-delete"
                                                        title="Delete Fixture"></i>
                                                    </li> */}
                                                </ul>
                                            </div>
                                          </div>
                                        )
                                      })
                                      :
                                      ''
                                  }
                                  {
                                    _isEmpty(fixtureList) &&
                                    <Col md={12}>
                                      <div className="no-records">No Record Found.</div>
                                    </Col>
                                  }
                                </Col>
                              </Row>


                            </Col>
                          </Row>
                        </TabPane>

                        <TabPane tabId="3">
                          <Row>
                            <Col sm="12">
                              {/** COMPLETED START */}
                              {/* {fixture_status === 2 && */}
                                <div>
                                  {/* LIVE FIXTURE LIST START */}
                                  <Row>
                                    <Col lg={12} className="cardlivcol">
                                      {
                                        !_isEmpty(fixtureList)
                                          ?
                                          _Map(fixtureList, (item, index) => {
                                            return (
                                              <div className="flip-animation common-fixture" key={"live-fixtures-" + index}>
                                                <div className="bg-card pf-bg-card">
                                                {
                                                   item.published == 1 && 
                                                   <>
{
  (item.is_pin_fixture == '1') &&
  <img src={Images.PIN_ACTIVE} alt="" className="pinned-active" />
  // : <span className="pin-tour" onClick={()=>this.openConfirmModal(item)}>
  //     <i className="icon-pinned"></i>
  // </span>
}
                                                   </>
                                    
                                }
                                                  <div className="dfs-mn-hgt">

                                                    <img className="com-fixture-flag float-left xcardimg" src={NC.S3 + NC.FLAG + item.home_flag}></img>
                                                    <img className="com-fixture-flag float-right xcardimg" src={NC.S3 + NC.FLAG + item.away_flag}></img>
                                                    <div className="com-fixture-container">
                                                      <div className="com-fixture-name xlivcardh3" onClick={() => this.redirectToContestList(item)}>{(item.home) ? item.home : 'TBA'} VS {(item.away) ? item.away : 'TBA'}</div>
                                                      <div className="com-fixture-time xlivcardh6">
                                                        {/* {WSManager.getUtcToLocalFormat(item.scheduled_date, 'D-MMM-YYYY hh:mm A')} */}
                                                        {HF.getFormatedDateTime(item.scheduled_date, 'D-MMM-YYYY hh:mm A')}
                                                      </div>
                                                      <div className="com-fixture-title xlivcardh6">{item.league_name}</div>
                                                    </div>
                                                  </div>
                                                </div>
                                              </div>
                                            )
                                          })
                                          :
                                          ''

                                      }
                                      {
                                        _isEmpty(fixtureList) &&
                                        <Col md={12}>
                                          <div className="no-records">No Record Found.</div>
                                        </Col>
                                      }
                                    </Col>
                                  </Row>
                                </div>
                              {/* } */}
                            </Col>
                          </Row>
                        </TabPane>
                      </TabContent>
                    </Col>
                  </Row>
                  {
                    (fixture_status === 2 && total > filter.items_perpage) && (
                      <Row>
                        <Col md={12}>
                          <div className="custom-pagination lobby-paging">
                            <Pagination
                              activePage={filter.current_page}
                              itemsCountPerPage={filter.items_perpage}
                              totalItemsCount={total}
                              pageRangeDisplayed={5}
                              onChange={e => this.handlePageChange(e)}
                            />
                          </div>
                        </Col>
                      </Row>
                    )
                  }
            
            </div>
          </Col>
        </Row>
        {showConfirmModal &&
          <ConfirmActionModal 
            show={showConfirmModal} 
            hide={this.hideConfirmModal} 
            data={{
              item: this.state.activePinContest,
              action: this.pinContest,
              msg: (this.state.activePinContest.is_pin_fixture == '0' ? 'Are you sure you want to mark pin ?' : 'Are you sure you want to remove pin ?'),
              cancelReason: false
            }} 
          />
        }
      </div>
    );
  }
}
export default Fixture;

