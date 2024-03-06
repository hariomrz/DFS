import React, { Component, Fragment } from 'react';
import Select from 'react-select';
import {
  Col, Row, Button, TabContent, TabPane, Nav, NavItem, NavLink, Input
} from 'reactstrap';


import * as NC from "../../../helper/NetworkingConstants";
import WSManager from "../../../helper/WSManager";
import moment from 'moment';
import LS from 'local-storage';
import Pagination from "react-js-pagination";
import { notify } from 'react-notify-toast';
import queryString from 'query-string';
import { MomentDateComponent } from "../../../components/CustomComponent";
import { STAR_CONFIRM_MSG, R_STAR_CONFIRM_MSG, SI_DATE, SI_NULL_DATE, DELAY_MSG_HEAD, DELAY_TIME_MSG_HEAD } from "../../../helper/Message";
import HF, { _times, _Map, _isUndefined, _isEmpty, _cloneDeep, _isNull } from "../../../helper/HelperFunction";
import DfsTCard from '../DFSTournament/DfsTCard';
import DfsT_ParticipantsModal from '../../../components/Modals/DfsT_ParticipantsModal';
import DfsT_LeaderBoardModal from '../../../components/Modals/DfsT_LeaderBoardModal';
import ViewWinners from '../../../components/Modals/ViewWinners';
import Dfs_MatchDelayAlertModal from '../../../components/Modals/Dfs_MatchDelayAlertModal';
import Dfs_MatchAlertMsgModal from '../../../components/Modals/Dfs_MatchAlertMsgModal';

import { DFST_getAllTournament, DFST_getTournamentParticipants, DFST_getTournamentLeaderboard, updateSIDate, DFST_cancelTournament, DFST_PIN_TOURNAMENT } from '../../../helper/WSCalling';
import Loader from '../../../components/Loader';
import PromptModal from '../../../components/Modals/PromptModal';
import ModalSecondInning from './ModalSecondInning';
import Images from '../../../components/images';
import DatePicker from "react-datepicker";
import DFSTourFixtureModal from './DFSTourFixtureModal';
import ConfirmActionModal from '../../../components/Modals/ConfirmActionModal';

class DFS extends Component {

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
      selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
      leagueList: [],
      fixtureList: [],
      selected_league: "",
      fixture_status: "not_complete",
      activeTab: "1",
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
      DfsT_List: [],
      DfsT_Posting: false,
      MODAL_PERPAGE: 10,
      DfsT_ID: '',
      DfsT_ITEM: [],
      DfsT_PARTI_CURRENT_PAGE: 1,
      DfsT_usersModalOpen: false,
      DfsT_ParticipantsList: [],
      DfsT_TotalParticipants: 0,
      DfsT_LDRBRD_ITEM: [],
      DfsT_LDRBRD_CURRENT_PAGE: 1,
      DfsT_ldrbrdModalOpen: false,
      MerchandiseList: [],
      prize_modal: false,
      StarModalOpen: false,
      StarItemIdx: '',
      StarMessage: '',
      StarCallFrom: '',
      DfsT_LdrBrdPosting: false,
      fxPinPosting: false,
      siModalIsOpen: false,
      siPosting: false,
      FromDate: new Date(),
      ToDate: new Date(),
      keyword: '',
      todayDate: new Date(),
      activeTournament: '',
      showConfirmModal: false,
    };
  }

  componentDidMount() {
    if(LS.get('isMGEnable')){
      LS.remove('isMGEnable')
    }
    if (HF.allowDFSTournament() != '1') {
      this.setState({ activeFixtureTab: '1', activeTab: '1' })
    }
    // this.getMerchandiseList()
    this.GetAllLeagueList();
    let isTour = this.props && this.props.location && this.props.location.state && this.props.location.state.isTour
    let values = queryString.parse(this.props.location.search)
    this.setState({
      activeTab: isTour ? (this.props.location.state.DfstId ? this.props.location.state.DfstId : '1') : (!_isEmpty(values) ? (values.tab) ? values.tab : '1' : '1'),

      fixture_status: (values.tab == 3) ? 2 : 'not_complete',
      activeFixtureTab: isTour ? '3' : (!_isEmpty(values) ? !_isUndefined(values.pctab) ? values.pctab : (values.fixtab) ? values.fixtab : '1' : '1'),
      MiniLeActiveTab: !_isEmpty(values) ? (values.fixtab) ? '5' : '4' : '4',
      minileague_status: !_isEmpty(values) ? (values.fixtab) ? 'upcoming' : 'live' : "live",
      filter: {
        type: !_isEmpty(values) ? values.tab : '1',
        current_page: 1,
        items_perpage: 50,
      },
    }, () => {
      if (this.state.activeFixtureTab === '1')
        this.GetAllFixtureList();

      if (this.state.activeFixtureTab === '3')
        this.getAllTornament();

      // if(this.props && this.props.location && this.props.location.state && this.props.location.state.isTour){
      //   this.setState({ activeFixtureTab: '3', activeTab: this.props.location.state.DfstId ? this.props.location.state.DfstId : '1' })
      // }
    })
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
          if (NC.ALLOW_FREETOPLAY == 1) {
            this.GetMiniLeagueList();
          }
        })
      } else if (responseJson.response_code == NC.sessionExpireCode) {
        WSManager.logout();
        this.props.history.push('/login');
      }

    })
  }

  createLeagueList = (list) => {
    let leagueArr = list;
    let tempArr = [{ value: "", label: "All" }];
    leagueArr.map(function (lObj, lKey) {
      tempArr.push({ value: lObj.league_id, label: lObj.league_name });
    });
    this.setState({ leagueList: tempArr });
  }

  // GET ALL FIXTURE LIST
  GetAllFixtureList = () => {
    let { keyword, selected_sport, selected_league, filter, fixture_status, FromDate, ToDate } = this.state

    let setDate = (date, hour, minute, second, millisecond, addition = 0) => {
      return moment(date).add(addition, 'days')
        .set('hour', hour)
        .set('minute', minute)
        .set('second', second)
        .set('millisecond', millisecond)
        .format('YYYY-MM-DD HH:mm:ss')
    }

    let FDate = setDate(FromDate, 0, 0, 0, 0)
    let TDate = setDate(ToDate, 23, 59, 59, 0)

    let param = {
      "sports_id": selected_sport,
      "league_id": selected_league,
      "limit": filter.items_perpage,
      "page": filter.current_page,
      "sort_order": (filter.type != 2) ? "DESC" : "ASC",
      "sort_field": "season_scheduled_date",
      "status": filter.type == 3 ? 'completed' : filter.type == 2 ? 'upcoming' : 'live',//fixture_status,
      "type": filter.type,
      // "fromdate": FromDate ? WSManager.getLocalToUtcFormat(FDate, 'YYYY-MM-DD HH:mm') : '',
      // "todate": ToDate ? WSManager.getLocalToUtcFormat(TDate, 'YYYY-MM-DD HH:mm') : '',
      "keyword": keyword,
    }
    this.setState({
      posting: true
    })

    WSManager.Rest(NC.baseURL + NC.GET_ALL_FIXTURE, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {

        let responseJsonData = responseJson.data;
        this.setState({
          posting: false,
          fixtureList: responseJsonData.result,
          total: responseJsonData.total
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

  redirectToSalaryReview = (selectedObj, event_type) => {
    if (selectedObj.is_published > 0 || event_type == 'live' || event_type == 'completed') {
      let tab = 1
      if (event_type == 'upcoming')
        tab = 2
      if (event_type == 'completed')
        tab = 3
      this.props.history.push({ 
        pathname: '/contest/fixturecontest/' + selectedObj.collection_master_id + '/' + selectedObj.season_id + '/' + tab, 
        state: { isCollection: true, isNPublished : selectedObj.is_published == 0 ? false : true } 
      })
    } else {
      this.props.history.push({ pathname: '/game_center/update-salary/' + selectedObj.league_id + '/' + selectedObj.season_id })
    }
  }

  redirectToSelectPlayer = (selectedObj, event_type) => {
    if (selectedObj.is_published > 1 && event_type == 'live') {
      this.props.history.push({ 
        pathname: '/contest/fixturecontest/' + selectedObj.collection_master_id + '/' + selectedObj.season_id + '/1' ,
        state : {isNPublished : selectedObj.is_published == 0 ? false : true}
    })
    } else {
      this.props.history.push({ pathname: '/game_center/Playing11/' + selectedObj.league_id + '/' + selectedObj.season_id })
    }
  }

  redirectToNewSalaryReview = (selectedObj, event_type) => {
    this.props.history.push({ pathname: '/game_center/update-salary/' + selectedObj.league_id + '/' + selectedObj.season_id })
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
        fixture_status: (tab == 3) ? 2 : 'not_complete',
        fixtureList: [],
        DT_CURRENT_PAGE: 1,

        FromDate: new Date(),
        ToDate: new Date(),
        keyword: '',
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
        fixture_status: "not_complete",
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

    var old = moment(fixtureObjData.season_scheduled_date).subtract(fixtureObjData.delay_hour, 'hours').subtract(fixtureObjData.delay_minute, 'minutes').format('DD-MMM-YYYY hh:mm A');

    var old_1 = moment(WSManager.getUtcToLocal(old)).format("DD-MMM-YYYY hh:m A")

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
      "season_id": fixtureObjData.season_id,
      "delay_hour": parseInt(delay_hour),
      "delay_minute": parseInt(delay_minute),
      "delay_message": delay_message
    };
    this.setState({ delayPosting: false })
    WSManager.Rest(NC.baseURL + NC.UPDATE_FIXTURE_DELAY, params).then((responseJson) => {
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
    let param = {
      "season_id":MsgItems.season_id,
      "custom_message":Message,
      ...(flag != 1 && {"is_remove":"1"})
    }
    // if (flag == 1) {
    //   param = {
    //     "season_game_uid": MsgItems.season_game_uid,
    //     "custom_message": Message,
    //     "league_id": MsgItems.league_id,
    //   }
    // } else {
    //   param = {
    //     "season_game_uid": MsgItems.season_game_uid,
    //     "custom_message": "",
    //     "is_remove": "1",
    //     "league_id": MsgItems.league_id,
    //   }
    // }

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
    console.log('kkkk')
    return (
      _Map(list, (item, idx) => {
        return (
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
            manageFixture={() => this.manageFixture(item)}
            cancleTournament={() => this.cancleTournament(item)}
            pinContest={() => this.showConfirmModal(item)}
          />
        )
      })
    )
  }

  getAllTornament = () => {
    let { selected_league, activeTab, selected_sport, DT_CURRENT_PAGE, PERPAGE, FromDate, ToDate, keyword } = this.state
    this.setState({ DfsT_Posting: true })

    //   let setDate = (date, hour, minute, second, millisecond, addition = 0) => {
    //     return moment(date).add(addition, 'days')
    //                 .set('hour', hour)
    //                 .set('minute', minute)
    //                 .set('second', second)
    //                 .set('millisecond', millisecond)
    //                 .format('YYYY-MM-DD HH:mm:ss')
    //   }

    // let FDate= setDate(FromDate, 0, 0, 0, 0)
    // let TDate= setDate(ToDate, 23, 59, 59, 0)
    let params = {
      "sports_id": selected_sport,
      "league_id": selected_league,
      "status": activeTab == '1' ? 'live' : (activeTab == '3' ? 'completed' : 'upcoming'),
      "keyword": keyword,
      // "start_date":FromDate ? WSManager.getLocalToUtcFormat(FDate, 'YYYY-MM-DD HH:mm') : '',
      // "end_date": ToDate ? WSManager.getLocalToUtcFormat(TDate, 'YYYY-MM-DD HH:mm') : '',
      "limit": PERPAGE,
      "page": DT_CURRENT_PAGE
    }

    DFST_getAllTournament(params).then(Response => {
      if (Response.response_code == NC.successCode) {
        this.setState({
          DfsT_List: Response.data && Response.data.result ? Response.data.result : [],
          DfsT_Total: Response.data && Response.data.total ? Response.data.total : 0,
          DfsT_Posting: false
        })
      } else {
        // notify.show(NC.SYSTEM_ERROR, 'error', 5000)
      }
    }).catch(error => {
      // notify.show(NC.SYSTEM_ERROR, 'error', 5000)
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
    let { fxLeagueId, fxSeasonGameUid, fxIdx, fxPinVal, selected_sport,fxCMID } = this.state
    this.setState({ fxPinPosting: true })
    let params = {
      // "league_id": fxLeagueId,
      // "season_game_uid": fxSeasonGameUid,
      "sports_id": selected_sport,
      "collection_master_id":fxCMID
    };
    if (fxPinVal == '1') {
      // params.is_pin = '0'
      params.is_pin_fixture = '0'
    }

    WSManager.Rest(NC.baseURL + NC.MARK_PIN_FIXTURE, params).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        let fixtureList = _cloneDeep(this.state.fixtureList);
        fixtureList[fxIdx]['is_pin'] = (fxPinVal == '1') ? '0' : '1'
        this.setState({ 'fixtureList': fixtureList, fxPinModalOpen: false });

        notify.show(responseJson.message, "success", 5000);
      } else {
        notify.show(responseJson.message, "error", 3000);
      }
      this.setState({ fxPinPosting: false })
    })
  }

  fxPinModal = (item, idx) => {
    let msg_status = (item.is_pin == '1') ? 'remove' : 'mark';
    let msg = 'Are you sure you want to ' + msg_status + ' pin ?'
    this.setState({
      fxPinModalOpen: !this.state.fxPinModalOpen,
      fxLeagueId: item.league_id,
      fxSeasonGameUid: item.season_game_uid,
      fxPinMsg: msg,
      fxIdx: idx,
      fxPinVal: item.is_pin,
      fxCMID: item.collection_master_id
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
    // let tempData = this.state.fixtureList['live_fixture'][this.state.fxIdx]
    let tempData = this.state.fixtureList[this.state.fxIdx]
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
        // this.state.fixtureList['live_fixture'][this.state.fxIdx]['2nd_inning_date'] = utc_d
        this.state.fixtureList[this.state.fxIdx]['2nd_inning_date'] = utc_d
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

  handleDate = (date, dateType) => {
    this.setState({ [dateType]: date }, () => {
      if (this.state.FromDate || this.state.ToDate) {
        // if(isTour){
        //   this.getAllTornament()
        // }
        // else{
        this.GetAllFixtureList()
        // }
      }
    })
  }

  searchByUser = (e) => {
    this.setState({ keyword: e.target.value }, this.SearchCodeReq())
  }

  fxPinModal = (item, idx) => {
    let msg_status = (item.is_pin == '1') ? 'remove' : 'mark';
    let msg = 'Are you sure you want to ' + msg_status + ' pin ?'
    this.setState({
      fxPinModalOpen: !this.state.fxPinModalOpen,
      fxLeagueId: item.league_id,
      fxSeasonGameUid: item.season_game_uid,
      fxPinMsg: msg,
      fxIdx: idx,
      fxPinVal: item.is_pin,
      fxCMID: item.collection_master_id,
    })
  }

  searchByUser = (e, isTour) => {
    this.setState({
      keyword: e.target.value
    }, () => {
      if (isTour) {
        this.getAllTornament()
      }
      else {
        this.GetAllFixtureList()
      }
    })
  }

  manageFixture = (item) => {
    this.setState({
      showFixtureModal: true,
      activeTournament: item
    })
  }

  hideFixtureModal = () => {
    this.setState({
      showFixtureModal: false,
      activeTournament: ''
    }, () => {
      this.getAllTornament()
    })
  }

  showConfirmModal = (item) => {
    this.setState({
      showConfirmModal: true,
      pinTour: true,
      activeTournament: item
    })
  }

  cancleTournament = (item) => {
    this.setState({
      showConfirmModal: true,
      activeTournament: item
    })
  }

  hideConfirmModal = () => {
    this.setState({
      showConfirmModal: false,
      activeTournament: ''
    })
  }

  deleteTournament = (reason) => {
    let params = {
      "cancel_reason": reason,
      "tournament_id": this.state.activeTournament.tournament_id,
    }

    DFST_cancelTournament(params).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        notify.show(responseJson.message, "success", 5000);
        this.setState({
          showConfirmModal: false,
        }, () => {
          this.getAllTornament()
        })
      }
    })
  }

  clearAll = (isTour) => {
    if (isTour) {
      this.setState({
        keyword: ''
      }, () => {
        this.getAllTornament()
      })
    }
    else {
      this.setState({
        // keyword: '',
        FromDate: new Date(),
        ToDate: new Date(),
      }, () => {
        this.GetAllFixtureList()
      })
    }
  }

  pinContest = (item) => {
    let params = {
      "tournament_id": item.tournament_id,
    }

    DFST_PIN_TOURNAMENT(params).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        notify.show(responseJson.message, "success", 5000);
        this.hideConfirmModal()
        this.getAllTornament()
      }
    })
  }

  showMatchFormat=(format)=>{
    return format == 1 ? 'ODI' : format == 2 ? 'TEST' : format == 3 ? 'T20' : 'T10'
  }

  render() {
    const { MiniLeActiveTab, activeFixtureTab, activeTab, total, leagueList, fixtureList, filter, DfsT_List, DfsT_Total, DfsT_Posting, DfsT_usersModalOpen, DfsT_ParticipantsList, DfsT_PartiListPosting, DfsT_PARTI_CURRENT_PAGE, MODAL_PERPAGE, DfsT_TotalParticipants, DfsT_ldrbrdModalOpen, DfsT_ldrbrdList, DfsT_LdrBrdPosting, DfsT_LDRBRD_CURRENT_PAGE, DfsT_TotalLdrbrd, DfsT_ITEM, prize_modal, MerchandiseList, fixture_status, templateObj, StarModalOpen, StarPosting, StarMessage, DelayModalIsOpen, delayPosting, fixtureObjData, delay_hour, delay_minute, delay_message, HourMsg, MinuteMsg, msgModalIsOpen, msgFormValid, MsgItems, Message, DfsT_LDRBRD_ITEM, fxPinModalOpen, fxPinPosting, fxPinMsg, siModalIsOpen, siPosting, selected_sport, FromDate, ToDate, keyword, showFixtureModal, activeTournament, showConfirmModal } = this.state;

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
        {DfsT_usersModalOpen && <DfsT_ParticipantsModal {...DfsT_usersProps} />}
        {DfsT_ldrbrdModalOpen && <DfsT_LeaderBoardModal {...DfsT_ldrbrdProps} />}
        {prize_modal && <ViewWinners {...DfsT_ViewWinnersProps} />}
        {StarModalOpen && <PromptModal {...ConfirmStarProps} />}
        {DelayModalIsOpen && <Dfs_MatchDelayAlertModal {...MatchDelayAlertMsgProps} />}
        {msgModalIsOpen && <Dfs_MatchAlertMsgModal {...MatchAlertMsgProps} />}
        {fxPinModalOpen && <PromptModal {...fxPinModalProps} />}
        {siModalIsOpen && <ModalSecondInning {...second_inni_mdl_props} />}
        <Row>
          <Col md={9}>
            <Select
              className="dfs-selector"
              id="selected_league"
              name="selected_league"
              placeholder="Select League"
              value={this.state.selected_league}
              options={leagueList}
              onChange={(e) => this.handleSelect(e, 'selected_league')}
            />
          </Col>
        </Row>
        <Row>
          <Col md={12}>
            <div className="user-navigation mb-30">
              <Nav tabs>
                <NavItem className={activeFixtureTab === '1' ? "active" : ""}
                  onClick={() => { this.toggleFixtureTab('1'); }}>
                  <NavLink>
                    {HF.getMasterData().int_version == "1" ? "Games" : "Fixture"}
                  </NavLink>
                </NavItem>
                {
                  (HF.allowDFSTournament() == "1") &&
                  <NavItem className={activeFixtureTab === '3' ? "active" : ""}
                    onClick={() => { this.toggleFixtureTab('3'); }}>
                    <NavLink>
                      DFS Tournament
                    </NavLink>
                  </NavItem>
                }
                {NC.ALLOW_FREETOPLAY == 1 &&
                  <NavItem className={activeFixtureTab === '2' ? "active" : ""}
                    onClick={() => { this.toggleFixtureTab('2'); }}>
                    <NavLink>
                      Mini leagues
                    </NavLink>
                  </NavItem>
                }
              </Nav>
              <TabContent activeTab={activeFixtureTab}>
                <TabPane tabId="1">
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
                              {fixture_status !== 2 &&
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
                                                {
                                                  item.is_tour_game == "1" ?
                                                  <div className="bg-card-motor-sports">
                                                    <div className="motor-sports-container">
                                                <div className="top-view-motor-sports">
                                               
                                                {
                                              item.is_published == '1' && 
                                              <div className="pinned-view">
                                                {
                                                  item.is_pin == '1' && 
                                                  <img onClick={(e) => this.fxPinModal(item, index)} src={Images.PIN_ACTIVE} alt="" className="pinned-active" />
                                                }
                                                {
                                                  item.is_pin == '0' && 
                                                  <i onClick={(e) => this.fxPinModal(item, index)} className="icon-pinned"/>
                                                }
                                              </div>
                                            }
                                                <div className={`car-type-view ${item.league_name == "Formula 1" ? " formula-one" : item.league_name == "Moto GP" ? " moto-gp" : item.league_name == "Desert racing" ? " desert-racing" : " other-league-abbr" }`}>{item.league_name}</div>
                                                </div>
                                                  <div className="motor-sports-view" onClick={() => this.redirectToSalaryReview(item, 'live')}>
                                                  <img className="img-colum-view" 
                                                  src={NC.S3 + NC.MOTOR_SPORTS_IMG + item.league_image} 
                                                  alt=""
                                                  
                                                  ></img>
                                                  <div className="inner-view-motor-sports">
                                                    <div className="tournament-name-view cursor-pointer" onClick={() => this.redirectToSalaryReview(item, 'live')}>{item.tournament_name}</div>
                                                    <div className="events-view">{item.match_event} events</div>
                                                    <div className="date-view">{HF.getFormatedDateTime(item.season_scheduled_date, 'D MMM YYYY hh:mm A')} to {HF.getFormatedDateTime(item.end_scheduled_date, 'D MMM YYYY hh:mm A')} </div>
                                                  </div>
                                                  </div>
                                                  </div>
                                                  {/* <div className="dfs-mn-hgt">
                                                    {
                                                      this.state.selected_sport == "7" && item.format != '' &&
                                                      <small className="format_bx">{item.format}</small>
                                                    }
                                                    <img className="com-fixture-flag float-left" src={NC.S3 + NC.FLAG + item.home_flag}></img>
                                                    <img className="com-fixture-flag float-right" src={NC.S3 + NC.FLAG + item.away_flag}></img>
                                                    <div className="com-fixture-container">
                                                      <div onClick={() => this.redirectToSalaryReview(item, 'live')} className="com-fixture-name">{(item.home) ? item.home : 'TBA'} VS {(item.away) ? item.away : 'TBA'}</div>
                                                      {
                                                        (_isEmpty(item['2nd_inning_date'])) &&
                                                        <div className="com-fixture-time">
                                                          {WSManager.getUtcToLocalFormat(item.scheduled_date_time, 'D-MMM-YYYY hh:mm A')}
                                                        </div>
                                                      }
                                                      Start for second inning // commented
                                                      {
                                                        (HF.allowSecondInni() == '1' && !_isEmpty(item['2nd_inning_date']) && selected_sport == '7' && (item.format == 1 || item.format == 3)) &&
                                                        <Fragment>
                                                          <div className="com-fixture-time">
                                                            Second inning starts in
                                                          </div>
                                                          <div className="si-date">
                                                            <MomentDateComponent data={{ date: item['2nd_inning_date'], format: "D-MMM-YYYY hh:mm A" }} />
                                                            {
                                                              !compDate &&
                                                              <i
                                                                className="icon-edit ml-1 pointer"
                                                                onClick={() => this.openSecInnModal(item, index)}></i>
                                                            }
                                                          </div>
                                                        </Fragment>
                                                      }
                                                      End for second inning  // commented
                                                      <div className="com-fixture-title">{item.league_abbr}</div>
                                                    </div>
                                                  </div> */}
                                                  {
                                                    ((item.status == "0" || item.status == "1" || item.status == "2") && (item.status_overview == "0" || item.status_overview == "4")) ?
                                                      <ul className="fx-action-list">
                                                        <li
                                                          className="fx-action-item"
                                                          onClick={() => this.openMsgModal(item)}
                                                        >
                                                          <i className="icon-email_verified"
                                                            title="Add alert message"></i>
                                                        </li>
                                                        <li className="fx-action-item">
                                                          <i
                                                            title="Match stats"
                                                            className="icon-stats"
                                                            onClick={() => this.props.history.push({ pathname: '/DFS/season_schedule/' + item.league_id + '/' + item.season_id + '/' + this.state.selected_sport + '/' + activeTab })}
                                                          ></i>
                                                        </li>
                                                        {
                                                          (HF.allowBooster() == '1' && (HF.allowBoosterInSports(selected_sport)) && item.is_booster == '1') &&
                                                          <li className="fx-action-item">
                                                            <i
                                                              title="Booster"
                                                              className="icon-booster"
                                                              onClick={() => this.redirectToBooster(item, '1')}
                                                            ></i>
                                                          </li>
                                                        }
                                                      </ul>
                                                      :
                                                      <div className="fx-match-cancel">{this.getMatchMsg(item.status, item.status_overview)}</div>
                                                  }
                                                </div>
                                                : 
                                                <div className="bg-card fxrcard">
                                                <div className="dfs-mn-hgt">
                                                  {
                                                    this.state.selected_sport == "7" && item.format != '' &&
                                                    <small className="format_bx">{this.showMatchFormat(item.format)}</small>
                                                  }
                                                  <img className="com-fixture-flag float-left" src={NC.S3 + NC.FLAG + item.home_flag}></img>
                                                  <img className="com-fixture-flag float-right" src={NC.S3 + NC.FLAG + item.away_flag}></img>
                                                  <div className="com-fixture-container">
                                                    <div onClick={() => this.redirectToSalaryReview(item, 'live')} className="com-fixture-name a2">{(item.home) ? item.home : 'TBA'} VS {(item.away) ? item.away : 'TBA'}</div>
                                                    {
                                                      (_isEmpty(item['2nd_inning_date']) || _isUndefined(item['2nd_inning_date'])) &&
                                                      <div className="com-fixture-time">
                                                        {HF.getFormatedDateTime(item.season_scheduled_date, 'D-MMM-YYYY hh:mm A')}
                                                      </div>
                                                    }
                                                    {/* Start for second inning */}
                                                    {
                                                      (HF.allowSecondInni() == '1' && !_isEmpty(item['2nd_inning_date']) && selected_sport == '7' && (item.format == 1 || item.format == 3)) &&
                                                      <Fragment>
                                                        <div className="com-fixture-time">
                                                          Second inning starts in
                                                        </div>
                                                        <div className="si-date">
                                                          {/* <MomentDateComponent data={{ date: item['2nd_inning_date'], format: "D-MMM-YYYY hh:mm A" }} /> */}
                    {HF.getFormatedDateTime(item['2nd_inning_date'], "D-MMM-YYYY hh:mm A")}
                                                          
                                                          {
                                                            !compDate &&
                                                            <i
                                                              className="icon-edit ml-1 pointer"
                                                              onClick={() => this.openSecInnModal(item, index)}></i>
                                                          }
                                                        </div>
                                                      </Fragment>
                                                    }
                                                    {/* End for second inning */}
                                                    <div className="com-fixture-title">{item.league_name}</div>
                                                  </div>
                                                </div>
                                                {
                                                  ((item.status == "0" || item.status == "1" || item.status == "2") && (item.status_overview == "0" || item.status_overview == "4")) ?
                                                    <ul className="fx-action-list">
                                                      <li
                                                        className="fx-action-item"
                                                        onClick={() => this.openMsgModal(item)}
                                                      >
                                                        <i className="icon-email_verified"
                                                          title="Add alert message"></i>
                                                      </li>
                                                      <li className="fx-action-item">
                                                        <i
                                                          title="Match stats"
                                                          className="icon-stats"
                                                          onClick={() => this.props.history.push({ pathname: '/DFS/season_schedule/' + item.league_id + '/' + item.season_id + '/' + this.state.selected_sport + '/' + activeTab })}
                                                        ></i>
                                                      </li>
                                                      {
                                                        (HF.allowBooster() == '1' && (HF.allowBoosterInSports(selected_sport)) && item.is_booster == '1') &&
                                                        <li className="fx-action-item">
                                                          <i
                                                            title="Booster"
                                                            className="icon-booster"
                                                            onClick={() => this.redirectToBooster(item, '1')}
                                                          ></i>
                                                        </li>
                                                      }
                                                    </ul>
                                                    :
                                                    <div className="fx-match-cancel">{this.getMatchMsg(item.status, item.status_overview)}</div>
                                                }
                                              </div>
                                                }
                                               
                                              </div>
                                            )
                                          })
                                          : ''
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
                              }
                            </Col>
                          </Row>
                        </TabPane>
                        <TabPane tabId="2">
                          <Row className='TopBot fixtureFilter'>
                            {/* <div className='filterItems'>
                                    <label className="filter-label">From Date</label>
                                    <DatePicker
                                      // maxDate={this.state.ToDate}
                                      selected={FromDate}
                                      className="filter-date"
                                      showYearDropdown='true'
                                      minDate={moment().toDate()}
                                      onChange={e => this.handleDate(e, "FromDate")}
                                      placeholderText="From Date"
                                      dateFormat='dd/MM/yyyy'
                                  />
                                </div>
                                <div className='filterItems'>
                                    <label className="filter-label">To Date</label>
                                    <DatePicker
                                      // maxDate={this.state.ToDate}
                                      className="filter-date"
                                      showYearDropdown='true'
                                      minDate={moment().toDate()}
                                      selected={ToDate}
                                      onChange={e => this.handleDate(e, "ToDate")}
                                      placeholderText="To Date"
                                      dateFormat='dd/MM/yyyy'
                                  />
                                </div> */}
                            <div className='filterItems'>
                              <div className="search-box">
                                <label className="filter-label">Search</label>
                                <Input
                                  placeholder="Search League"
                                  name='code'
                                  value={keyword}
                                  onChange={(e) => this.searchByUser(e)}
                                />
                              </div>
                            </div>
                            <div className='filterItems'>
                              <div className="filters-area" style={{ marginTop: '24px' }}>
                                <Button className="btn-secondary" onClick={() => this.clearAll()}>Clear</Button>
                              </div>
                            </div>
                          </Row>
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
                                              item.is_published == '1' && item.is_tour_game != 1 &&
                                              <div style={{ zIndex: 1, cursor: 'pointer', position: 'absolute', marginLeft: item.is_pin == '1' ? '0px' : '10px' }}>
                                                {
                                                  item.is_pin == '1' && item.is_tour_game != 1 &&
                                                  <img onClick={(e) => this.fxPinModal(item, index)} src={Images.PIN_ACTIVE} alt="" className="pinned-active" />
                                                }
                                                {
                                                  item.is_pin == '0' && item.is_tour_game != 1 &&
                                                  <i onClick={(e) => this.fxPinModal(item, index)} className="icon-pinned"></i>
                                                }
                                              </div>
                                            }
                                            
                                            {
                                              item.is_tour_game == 1 ?
                                                <div className="bg-card-motor-sports">
                                                  <div className="motor-sports-container">
                                                <div className="top-view-motor-sports">
                                               
                                                {
                                              item.is_published == '1' && 
                                              <div className="pinned-view">
                                                {
                                                  item.is_pin == '1' && 
                                                  <img onClick={(e) => this.fxPinModal(item, index)} src={Images.PIN_ACTIVE} alt="" className="pinned-active" />
                                                }
                                                {
                                                  item.is_pin == '0' && 
                                                  <i onClick={(e) => this.fxPinModal(item, index)} className="icon-pinned"/>
                                                }
                                              </div>
                                            }
                                                <div className={`car-type-view ${item.league_name == "Formula 1" ? " formula-one" : item.league_name == "Moto GP" ? " moto-gp" : item.league_name == "Desert racing" ? " desert-racing" : " other-league-abbr" }`}>{item.league_name}</div>
                                                </div>
                                                  <div className="motor-sports-view"  onClick={() => this.redirectToSalaryReview(item, 'upcoming')}>
                                                  <img className="img-colum-view" 
                                                  src={NC.S3 + NC.MOTOR_SPORTS_IMG + item.league_image} 
                                                  alt=""
                                                  
                                                  ></img>
                                                  <div className="inner-view-motor-sports">
                                                    <div className="tournament-name-view" onClick={() => this.redirectToSalaryReview(item, 'upcoming')}>{item.tournament_name}</div>
                                                    <div className="events-view">{item.match_event} events</div>
                                                    <div className="date-view">{HF.getFormatedDateTime(item.season_scheduled_date, 'D MMM YYYY hh:mm A')} to {HF.getFormatedDateTime(item.end_scheduled_date, 'D MMM YYYY hh:mm A')} </div>
                                                  </div>
                                                  </div>
                                                  </div>
                                                  
                                                  {/* <div className="dfs-mn-hgt">
                                                   
                                                    <img className="com-fixture-flag float-left" src={NC.S3 + NC.FLAG + item.home_flag}></img>
                                                    <div className="com-fixture-container">
                                                      <div className="com-fixture-name" onClick={() => this.redirectToSalaryReview(item, 'upcoming')}>{(item.home) ? item.home : 'TBA'} VS {(item.away) ? item.away : 'TBA'}</div>
                                                      <div className="com-fixture-time xlivcardh6">
                                                        {WSManager.getUtcToLocalFormat(item.scheduled_date_time, 'D-MMM-YYYY hh:mm A')}
                                                      </div>
                                                      <div className="com-fixture-title xlivcardh6">{item.league_abbr}</div>
                                                    </div>
                                                  </div> */}
                                                  {
                                                    ((item.status == "0" || item.status == "1" || item.status == "2") && (item.status_overview == "0" || item.status_overview == "4")) ?
                                                      <ul className="fx-action-list">
                                                        {
                                                          item.is_salary_changed == 1 && item.is_published == 0 &&
                                                          <li className="fx-action-item">
                                                            <i
                                                              title="Verify and Publish"
                                                              className="icon-fixture_published"
                                                              onClick={() => this.redirectToSalaryReview(item)}
                                                            ></i>
                                                          </li>
                                                        }
                                                        {
                                                          item.is_published == 0 ?
                                                            <li className="fx-action-item">
                                                              <i
                                                                title="Publish Match"
                                                                className="icon-salary-Review"
                                                                onClick={() => this.redirectToNewSalaryReview(item)}
                                                              ></i>
                                                            </li>
                                                            :
                                                            <li className="fx-action-item">
                                                              <i
                                                                title="Update Player"
                                                                className="icon-Salary-update"
                                                                onClick={() => this.redirectToNewSalaryReview(item)}
                                                              ></i></li>
                                                        }
                                                        {
                                                          item.is_salary_changed == 1 && item.is_published == 1 && item.is_tour_game == 0 &&
                                                          <li className="fx-action-item">
                                                            <i
                                                              title="Select Playing 11"
                                                              className="icon-select_player"
                                                              onClick={() => this.redirectToSelectPlayer(item)}
                                                            ></i>
                                                          </li>
                                                        }
                                                        {
                                                          this.state.selected_sport != 11 && 
                                                        <li className="fx-action-item">
                                                          <i
                                                            title="Mark match delay"
                                                            className="icon-delay"
                                                            onClick={() => this.openDelayModal(item)}
                                                          ></i>
                                                        </li>
                                                        }

                                                        <li
                                                          title="Add alert message"
                                                          className="fx-action-item"
                                                          onClick={() => this.openMsgModal(item)}
                                                        >
                                                          <i className="icon-email_verified" title="Add alert message"></i>
                                                        </li>
                                                        {
                                                          item.is_salary_changed == 1 && item.is_published == 1 &&
                                                          <li className="fx-action-item">
                                                            <i
                                                              title="Published"
                                                              className="icon-fixture-contest"
                                                              onClick={() => this.redirectToSalaryReview(item, 'upcoming')}
                                                            ></i>
                                                          </li>
                                                        }
                                                        {
                                                          item.is_salary_changed == 1 && item.is_published == 1 &&
                                                          <Fragment>
                                                            <li className="fx-action-item">
                                                              <i
                                                                title="Contest Template"
                                                                className="icon-template"
                                                                onClick={() => this.redirectToContestTemplate(item, 0)}
                                                              ></i>
                                                            </li>
                                                            {
                                                              (HF.allowBooster() == '1' && HF.allowBoosterInSports(selected_sport)) &&
                                                              <li className="fx-action-item">
                                                                <i
                                                                  title="Booster"
                                                                  className="icon-booster"
                                                                  onClick={() => this.redirectToBooster(item, '2')}
                                                                ></i>
                                                              </li>
                                                            }
                                                            { (HF.allowH2H() == '1') &&
                                                              <li className="fx-action-item">
                                                                <i
                                                                  title="H2H Template"
                                                                  onClick={() => this.redirectToContestTemplate(item, 1)}
                                                                >
                                                                  <img src={Images.H2TEMP} className='h2-icon' />
                                                                </i>
                                                              </li>
                                                            }
                                                          </Fragment>

                                                        }
                                                       
                                                      </ul>
                                                      :
                                                      <div className="fx-match-cancel">{this.getMatchMsg(item.status, item.status_overview)}</div>
                                                  }
                                                </div> :
                                                <div className="bg-card fxrcard">
                                                  <div className="dfs-mn-hgt">
                                                    {
                                                      this.state.selected_sport == "7" && item.format != '' &&
                                                      <small className="format_bx">{this.showMatchFormat(item.format)}</small>
                                                    }
                                                    <img className="com-fixture-flag float-left" src={NC.S3 + NC.FLAG + item.home_flag}></img>
                                                    <img className="com-fixture-flag float-right" src={NC.S3 + NC.FLAG + item.away_flag}></img>
                                                    <div className="com-fixture-container">
                                                      <div className="com-fixture-name a3" onClick={() => this.redirectToSalaryReview(item, 'upcoming')}>{(item.home) ? item.home : 'TBA'} VS {(item.away) ? item.away : 'TBA'}</div>
                                                      <div className="com-fixture-time xlivcardh6">
                                                        {HF.getFormatedDateTime(item.season_scheduled_date, 'D-MMM-YYYY hh:mm A')}
                                                        {/* {moment(item.scheduled_date_time).format('D-MMM-YYYY hh:mm A')} */}
                                                      </div>
                                                      <div className="com-fixture-title xlivcardh6">{item.league_name}</div>
                                                    </div>
                                                  </div>
                                                  {
                                                    ((item.status == "0" || item.status == "1" || item.status == "2") && (item.status_overview == "0" || item.status_overview == "4")) ?
                                                      <ul className="fx-action-list">
                                                        {
                                                          item.is_salary_changed == 1 && item.is_published == 0 &&
                                                          <li className="fx-action-item">
                                                            <i
                                                              title="Verify and Publish"
                                                              className="icon-fixture_published"
                                                              onClick={() => this.redirectToSalaryReview(item)}
                                                            ></i>
                                                          </li>
                                                        }
                                                        {
                                                          item.is_published == 0 ?
                                                            <li className="fx-action-item">
                                                              <i
                                                                title="Publish Match"
                                                                className="icon-salary-Review"
                                                                onClick={() => this.redirectToNewSalaryReview(item)}
                                                              ></i>
                                                            </li>
                                                            :
                                                            <li className="fx-action-item">
                                                              <i
                                                                title="Update Player"
                                                                className="icon-Salary-update"
                                                                onClick={() => this.redirectToNewSalaryReview(item)}
                                                              ></i></li>
                                                        }
                                                        {
                                                          item.is_salary_changed == 1 && item.is_published == 1 &&
                                                          <li className="fx-action-item">
                                                            <i
                                                              title="Select Playing 11"
                                                              className="icon-select_player"
                                                              onClick={() => this.redirectToSelectPlayer(item)}
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

                                                        <li
                                                          title="Add alert message"
                                                          className="fx-action-item"
                                                          onClick={() => this.openMsgModal(item)}
                                                        >
                                                          <i className="icon-email_verified" title="Add alert message"></i>
                                                        </li>
                                                        {
                                                          item.is_salary_changed == 1 && item.is_published == 1 &&
                                                          <li className="fx-action-item">
                                                            <i
                                                              title="Published"
                                                              className="icon-fixture-contest"
                                                              onClick={() => this.redirectToSalaryReview(item, 'upcoming')}
                                                            ></i>
                                                          </li>
                                                        }
                                                        {
                                                          item.is_salary_changed == 1 && item.is_published == 1 &&
                                                          <Fragment>
                                                            <li className="fx-action-item">
                                                              <i
                                                                title="Contest Template"
                                                                className="icon-template"
                                                                onClick={() => this.redirectToContestTemplate(item, 0)}
                                                              ></i>
                                                            </li>
                                                            {
                                                              (HF.allowBooster() == '1' && HF.allowBoosterInSports(selected_sport)) &&
                                                              <li className="fx-action-item">
                                                                <i
                                                                  title="Booster"
                                                                  className="icon-booster"
                                                                  onClick={() => this.redirectToBooster(item, '2')}
                                                                ></i>
                                                              </li>
                                                            }
                                                            { (HF.allowH2H() == '1') &&
                                                              <li className="fx-action-item">
                                                                <i
                                                                  title="H2H Template"
                                                                  // className="icon-H2H"
                                                                  onClick={() => this.redirectToContestTemplate(item, 1)}
                                                                >
                                                                  <img src={Images.H2TEMP} className='h2-icon' />
                                                                </i>
                                                              </li>
                                                            }
                                                          </Fragment>

                                                        }
                                                        {/* <li className="fx-action-item">
         <i
           title={item.highlight == "1" ? "Remove " : "" + "Highlight"}
           className="icon-star"
           onClick={() => this.StarModalToggle(index, item.highlight, 'upcoming_fixture')}
         ></i>
       </li> */}
                                                      </ul>
                                                      :
                                                      <div className="fx-match-cancel">{this.getMatchMsg(item.status, item.status_overview)}</div>
                                                  }
                                                </div>
                                            }

                                            {
                                              (item.is_tour_game == "0" &&  item.is_published == 1 && item.contest_count > 0) &&
                                              <div
                                                className="fx-promote"
                                                onClick={() => this.promoteFx(item)}
                                              >
                                                <span>Promote</span>
                                              </div>
                                            }
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
                          <Row className='TopBot fixtureFilter'>
                            {/* <div className='filterItems'>
                                    <label className="filter-label">From Date</label>
                                    <DatePicker
                                      // maxDate={this.state.ToDate}
                                      selected={FromDate}
                                      className="filter-date"
                                      showYearDropdown='true'
                                      maxDate={moment().toDate()}
                                      onChange={e => this.handleDate(e, "FromDate")}
                                      placeholderText="From Date"
                                      dateFormat='dd/MM/yyyy'
                                  />
                                </div>
                                <div className='filterItems'>
                                    <label className="filter-label">To Date</label>
                                    <DatePicker
                                      // maxDate={this.state.ToDate}
                                      className="filter-date"
                                      showYearDropdown='true'
                                      maxDate={moment().toDate()}
                                      selected={ToDate}
                                      onChange={e => this.handleDate(e, "ToDate")}
                                      placeholderText="From Date"
                                      dateFormat='dd/MM/yyyy'
                                  />
                                </div> */}
                            <div className='filterItems'>
                              <div className="search-box">
                                <label className="filter-label">Search</label>
                                <Input
                                  placeholder="Search League"
                                  name='code'
                                  value={keyword}
                                  onChange={(e) => this.searchByUser(e)}
                                />
                              </div>
                            </div>
                            <div className='filterItems'>
                              <div className="filters-area" style={{ marginTop: '24px' }}>
                                <Button className="btn-secondary" onClick={() => this.clearAll()}>Clear</Button>
                              </div>
                            </div>
                          </Row>
                          <Row>
                            <Col sm="12">
                              {/** COMPLETED START */}
                              {fixture_status === 2 &&
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
                                               {
                                                item.is_tour_game == "1" ? 
                                                <div className="bg-card-motor-sports">
                                                <div className="motor-sports-container">
                                              <div className="top-view-motor-sports">
                                             
                                              {
                                            item.is_published == '1' && 
                                            <div className="pinned-view">
                                              {
                                                item.is_pin == '1' && 
                                                <img onClick={(e) => this.fxPinModal(item, index)} src={Images.PIN_ACTIVE} alt="" className="pinned-active" />
                                              }
                                              {
                                                item.is_pin == '0' && 
                                                <i onClick={(e) => this.fxPinModal(item, index)} className="icon-pinned"/>
                                              }
                                            </div>
                                          }
                                              <div className={`car-type-view ${item.league_name == "Formula 1" ? " formula-one" : item.league_name == "Moto GP" ? " moto-gp" : item.league_name == "Desert racing" ? " desert-racing" : " other-league-abbr" }`}>{item.league_name}</div>
                                              </div>
                                                <div className="motor-sports-view" onClick={() => this.redirectToSalaryReview(item, 'completed')}>
                                                <img className="img-colum-view" 
                                                src={NC.S3 + NC.MOTOR_SPORTS_IMG + item.league_image} 
                                                alt=""
                                                
                                                ></img>
                                                <div className="inner-view-motor-sports" >
                                                  <div className="tournament-name-view"  onClick={() => this.redirectToSalaryReview(item, 'completed')}>{item.tournament_name}</div>
                                                  <div className="events-view">{item.match_event} events</div>
                                                  <div className="date-view">{HF.getFormatedDateTime(item.season_scheduled_date, 'D MMM YYYY hh:mm A')} to {HF.getFormatedDateTime(item.end_scheduled_date, 'D MMM YYYY hh:mm A')} </div>
                                                </div>
                                                </div>
                                                </div>
                                                {/* <div className="dfs-mn-hgt" onClick={() => this.redirectToSalaryReview(item, 'completed')}>
                                                  {
                                                    this.state.selected_sport == "7" && item.format != '' &&
                                                    <small className="format_bx">{item.format}</small>
                                                  }
                                                  <img className="com-fixture-flag float-left xcardimg" src={NC.S3 + NC.FLAG + item.home_flag}></img>
                                                  <img className="com-fixture-flag float-right xcardimg" src={NC.S3 + NC.FLAG + item.away_flag}></img>
                                                  <div className="com-fixture-container">
                                                    <div className="com-fixture-name xlivcardh3">{(item.home) ? item.home : 'TBA'} VS {(item.away) ? item.away : 'TBA'}</div>
                                                    <div className="com-fixture-time xlivcardh6">
                                                      {WSManager.getUtcToLocalFormat(item.scheduled_date_time, 'D-MMM-YYYY hh:mm A')}
                                                    </div>
                                                    <div className="com-fixture-title xlivcardh6">{item.league_abbr}</div>
                                                  </div>
                                                </div> */}
                                                {
                                                  ((item.status == "0" || item.status == "1" || item.status == "2") && (item.status_overview == "0" || item.status_overview == "4")) ?
                                                    <ul className="fx-action-list">
                                                      <li
                                                        className="fx-action-item"
                                                        onClick={() => this.props.history.push({ pathname: '/DFS/season_schedule/' + item.league_id + '/' + item.season_id + '/' + this.state.selected_sport + '/' + activeTab })}
                                                      >
                                                        <i
                                                          className="icon-stats"
                                                          title="Match stats"></i>
                                                      </li>
                                                      {
                                                        (HF.allowBooster() == '1' && HF.allowBoosterInSports(selected_sport) && item.is_booster == '1') &&
                                                        <li className="fx-action-item">
                                                          <i
                                                            title="Booster"
                                                            className="icon-booster"
                                                            onClick={() => this.redirectToBooster(item, '3')}
                                                          ></i>
                                                        </li>
                                                      }
                                                    </ul>
                                                    :
                                                    <div className="fx-match-cancel">{this.getMatchMsg(item.status, item.status_overview)}</div>
                                                }
                                              </div>
                                              :
                                              <div className="bg-card fxrcard">
                                              <div className="dfs-mn-hgt" onClick={() => this.redirectToSalaryReview(item, 'completed')}>
                                                {
                                                  this.state.selected_sport == "7" && item.format != '' &&
                                                  <small className="format_bx">{this.showMatchFormat(item.format)}</small>
                                                }
                                                <img className="com-fixture-flag float-left xcardimg" src={NC.S3 + NC.FLAG + item.home_flag}></img>
                                                <img className="com-fixture-flag float-right xcardimg" src={NC.S3 + NC.FLAG + item.away_flag}></img>
                                                <div className="com-fixture-container">
                                                  <div className="com-fixture-name xlivcardh3 a1">{(item.home) ? item.home : 'TBA'} VS {(item.away) ? item.away : 'TBA'}</div>
                                                  <div className="com-fixture-time xlivcardh6">
                                                    {HF.getFormatedDateTime(item.season_scheduled_date, 'D-MMM-YYYY hh:mm A')}
                                                    {/* {moment(item.scheduled_date_time).format('D-MMM-YYYY hh:mm A')} */}
                                                  </div>
                                                  <div className="com-fixture-title xlivcardh6">{item.league_name}</div>
                                                </div>
                                              </div>
                                              {
                                                ((item.status == "0" || item.status == "1" || item.status == "2") && (item.status_overview == "0" || item.status_overview == "4")) ?
                                                  <ul className="fx-action-list">
                                                    <li
                                                      className="fx-action-item"
                                                      onClick={() => this.props.history.push({ pathname: '/DFS/season_schedule/' + item.league_id + '/' + item.season_id + '/' + this.state.selected_sport + '/' + activeTab })}
                                                    >
                                                      <i
                                                        className="icon-stats"
                                                        title="Match stats"></i>
                                                    </li>
                                                    {
                                                      (HF.allowBooster() == '1' && HF.allowBoosterInSports(selected_sport) && item.is_booster == '1') &&
                                                      <li className="fx-action-item">
                                                        <i
                                                          title="Booster"
                                                          className="icon-booster"
                                                          onClick={() => this.redirectToBooster(item, '3')}
                                                        ></i>
                                                      </li>
                                                    }
                                                  </ul>
                                                  :
                                                  <div className="fx-match-cancel">{this.getMatchMsg(item.status, item.status_overview)}</div>
                                              }
                                            </div>
                                               }

                                               
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
                              }
                            </Col>
                          </Row>
                        </TabPane>
                      </TabContent>
                    </Col>
                  </Row>
                  {
                    // fixture_status === 2 && 
                    (total > filter.items_perpage) && (
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
                </TabPane>
                <TabPane tabId="2">
                  {
                    (activeFixtureTab === '2' && total > filter.items_perpage) && (
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
                </TabPane>

                <TabPane tabId="3">
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
                        {
                          (activeTab == '1') &&
                          <TabPane tabId="1">
                            <Row className='TopBot fixtureFilter'>
                              <div className='filterItems'>
                                <div className="search-box">
                                  <label className="filter-label">Search By Tournament Name</label>
                                  <Input
                                    placeholder="Search "
                                    name='code'
                                    value={keyword}
                                    onChange={(e) => this.searchByUser(e, true)}
                                  />
                                </div>
                              </div>
                              <div className='filterItems'>
                                <div className="filters-area" style={{ marginTop: '24px' }}>
                                  <Button className="btn-secondary" onClick={() => this.clearAll(true)}>Clear</Button>
                                </div>
                              </div>
                            </Row>
                            <Row>
                              <Col lg={12} className="cardlivcol">
                                {!DfsT_Posting && DfsT_List && DfsT_List.length > 0 && this.getDfstCard(DfsT_List, activeTab)}

                                {(DfsT_Total == 0 && !DfsT_Posting) &&
                                  <div className="no-records mt-30">{NC.NO_RECORDS}</div>
                                }
                                {
                                  DfsT_Total != 0 && DfsT_Posting &&
                                  <Loader />
                                }
                              </Col>
                            </Row>
                          </TabPane>
                        }

                        {
                          (activeTab == '2') &&
                          <TabPane tabId="2">
                            <Row className='TopBot fixtureFilter'>
                              <div className='filterItems'>
                                <div className="search-box">
                                  <label className="filter-label">Search By Tournament Name</label>
                                  <Input
                                    placeholder="Search "
                                    name='code'
                                    value={keyword}
                                    onChange={(e) => this.searchByUser(e, true)}
                                  />
                                </div>
                              </div>
                              <div className='filterItems'>
                                <div className="filters-area" style={{ marginTop: '24px' }}>
                                  <Button className="btn-secondary" onClick={() => this.clearAll(true)}>Clear</Button>
                                </div>
                              </div>
                            </Row>
                            <Row>
                              <Col lg={12} className="cardlivcol">
                                {!DfsT_Posting && DfsT_List && DfsT_List.length > 0 && this.getDfstCard(DfsT_List, activeTab)}
                                {(DfsT_Total == 0 && !DfsT_Posting) &&
                                  <div className="no-records mt-30">{NC.NO_RECORDS}</div>
                                }
                                {
                                  DfsT_Total != 0 && DfsT_Posting &&
                                  <Loader />
                                }
                              </Col>
                            </Row>
                          </TabPane>
                        }

                        {
                          (activeTab == '3') &&
                          <TabPane tabId="3">
                            <Row className='TopBot fixtureFilter'>
                              <div className='filterItems'>
                                <div className="search-box">
                                  <label className="filter-label">Search By Tournament Name</label>
                                  <Input
                                    placeholder="Search"
                                    name='code'
                                    value={keyword}
                                    onChange={(e) => this.searchByUser(e, true)}
                                  />
                                </div>
                              </div>
                              <div className='filterItems'>
                                <div className="filters-area" style={{ marginTop: '24px' }}>
                                  <Button className="btn-secondary" onClick={() => this.clearAll(true)}>Clear</Button>
                                </div>
                              </div>
                            </Row>
                            <Row>
                              <Col lg={12} className="cardlivcol">
                                {!DfsT_Posting && DfsT_List && DfsT_List.length > 0 && this.getDfstCard(DfsT_List, activeTab)}
                                {(DfsT_Total == 0 && !DfsT_Posting) &&
                                  <div className="no-records mt-30">{NC.NO_RECORDS}</div>
                                }
                                {
                                  DfsT_Total != 0 && DfsT_Posting &&
                                  <Loader />
                                }
                              </Col>
                            </Row>
                          </TabPane>
                        }
                        {/* <Row>
                          <Col md={12}>
                            {(DfsT_Total == 0 && !DfsT_Posting) &&
                              <div className="no-records mt-30">{NC.NO_RECORDS}</div>
                            }
                            {
                              DfsT_Total != 0 && DfsT_Posting &&
                              <Loader />
                            }
                          </Col>
                        </Row> */}
                        {this.getTouramentPagination()}
                      </TabContent>
                    </Col>
                  </Row>
                </TabPane>
              </TabContent>
            </div>
          </Col>
        </Row>
        {
          showFixtureModal &&
          <DFSTourFixtureModal show={showFixtureModal} hide={this.hideFixtureModal} data={activeTournament} />
        }
        {showConfirmModal &&
          <ConfirmActionModal
            show={showConfirmModal}
            hide={this.hideConfirmModal}
            data={{
              item: this.state.activeTournament,
              action: this.state.pinTour ? this.pinContest : this.deleteTournament,
              msg: this.state.pinTour ? (this.state.activeTournament.is_pin == '0' ? 'Are you sure you want to mark pin ?' : 'Are you sure you want to remove pin ?') : 'Are you sure you want to cancel this tournament ?',
              cancelReason: this.state.pinTour ? false : true
            }}
          />
        }
      </div>
    );
  }
}
export default DFS;

