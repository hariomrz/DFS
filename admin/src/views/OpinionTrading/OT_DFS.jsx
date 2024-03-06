import React, { Component, Fragment } from 'react';
import Select from 'react-select';
import {
  Col, Row, TabContent, TabPane, Nav, NavItem, NavLink,
} from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import moment from 'moment';
import LS from 'local-storage';
import Pagination from "react-js-pagination";
import { notify } from 'react-notify-toast';
import queryString from 'query-string';
import { MomentDateComponent } from "../../components/CustomComponent";
import HF, { _times, _Map, _isUndefined, _isEmpty, _cloneDeep, _isNull } from "../../helper/HelperFunction";
import Loader from '../../components/Loader';
import PromptModal from '../../components/Modals/PromptModal';
import Images from '../../components/images';
import Dfs_MatchAlertMsgModal from '../../components/Modals/Dfs_MatchAlertMsgModal';
import Dfs_MatchDelayAlertModal from '../../components/Modals/Dfs_MatchDelayAlertModal';
import _ from 'lodash';
class OT_DFS extends Component {

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
      selected_sport: LS.get('selectedSport'),
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
      PERPAGE: NC.ITEMS_PERPAGE,
      MODAL_PERPAGE: 10,
      MerchandiseList: [],
      prize_modal: false,
      fxPinPosting: false,
      fxSeasonid: ''
    };
  }

  componentDidMount() {
    this.getSports();   
    // this.GetAllLeagueList();
    let values = queryString.parse(this.props.location.search)
    this.setState({
      activeTab: !_isEmpty(values) ? (values.tab) ? values.tab : '1' : '1',
      fixture_status: (values.tab == 3) ? 2 : 'not_complete',
      filter: {
        type: !_isEmpty(values) ? values.tab : '1',
        current_page: 1,
        items_perpage: 50,
      },
    }, () => {
      // this.GetAllFixtureList();
    })
  }

  // GET ALL LEAGUE LIST
  GetAllLeagueList = () => {

    this.setState({
      posting: true
    })
    WSManager.Rest(NC.baseURL + NC.TRADES_ALL_LEAGUE_LIST_DROPDOWN, { "sports_id": this.state.selected_sport }).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        responseJson = responseJson.data.result;
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

  createLeagueList = (list) => {
    let leagueArr = list;
    let tempArr = [{ value: "", label: "All" }];

    if (!_isEmpty(leagueArr)) {
      leagueArr.map(function (lObj, lKey) {
        tempArr.push({ value: lObj.league_id, label: lObj.league_name });
      });
    }
    this.setState({ leagueList: tempArr });
  }

  // GET ALL FIXTURE LIST
  GetAllFixtureList = () => {
    let { selected_sport, selected_league, filter, fixture_status } = this.state
    let param = {
      "sports_id": selected_sport,
      "league_id": selected_league,
      "limit": filter.items_perpage,
      "page": filter.current_page,
      "sort_order": (filter.type != 2) ? "DESC" : "ASC",
      "sort_field": "scheduled_date",
      "status": filter.type == 3 ? 'completed' : filter.type == 2 ? 'upcoming' : 'live',//fixture_status,,
      "type": filter.type,
    }
    this.setState({
      posting: true
    })

    WSManager.Rest(NC.baseURL + NC.GET_TRADE_ALL_FIXTURE, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {

        let responseJsonData = responseJson.data;
        // console.log(responseJsonData, 'nilesh response'); return false;
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

    getSports = () => {
      let params = {}
      WSManager.Rest(NC.baseURL + NC.TRADE_ALL_SPORTS_LIST, params).then((responseJson) => {
         
         if (responseJson.response_code == NC.successCode) {

            let sportsOptions = [];
            _.map(responseJson.data, function (data) {
               sportsOptions.push({
                  value: data.sports_id,
                  label: data.sports_name,
               })
            })
            this.setState({
               sportsOptions: sportsOptions,
               selected_sport: this.state.selected_sport ? this.state.selected_sport : sportsOptions[0].value,
            }, () => {
               LS.set('selectedSport', this.state.selected_sport)
               this.GetAllLeagueList()
            })
         }
      }).catch(error => {
         notify.show(NC.SYSTEM_ERROR, "error", 3000)
      })
   }

  handleSelect = (value) => {
    if (value) {
      this.setState({ "selected_league": value.value }, function () {
        this.GetAllFixtureList();
      });
    }
  }

  redirectToSalaryReview = (selectedObj, event_type) => {

    // this.props.history.push({ pathname: '/livefantasy/overdetails/' + selectedObj.league_id + '/' + selectedObj.season_game_uid + '/2', state: { isCollection: true } })
    // return false

    if (selectedObj.is_published > 0 || event_type == 'live' || event_type == 'completed') {
      let tab = 1
      if (event_type == 'upcoming')
        tab = 2
      if (event_type == 'completed')
        tab = 3

      this.props.history.push({ pathname: '/opinionTrading/publish_match/' + selectedObj.league_id + '/' + selectedObj.season_id + '/' + tab, state: { selectedObj: selectedObj }})


    } else {
       let tab = 1
      if (event_type == 'upcoming')
        tab = 2
      if (event_type == 'completed')
        tab = 3
      this.props.history.push({ pathname: '/opinionTrading/publish_match/' + selectedObj.league_id + '/' + selectedObj.season_id + '/' + tab, state: { selectedObj: selectedObj }})
    }
  }

   redirectToPublish = (selectedObj, event_type) => {  

    

    if (selectedObj.is_published > 0 || event_type == 'live' || event_type == 'completed') {
      let tab = 1
      if (event_type == 'upcoming')
        tab = 2
      if (event_type == 'completed')
        tab = 3
     
         this.props.history.push({ pathname: '/opinionTrading/publish_match/' + selectedObj.league_id + '/' + selectedObj.season_id + '/' + tab})

        } else {
      let tab = 1
      if (event_type == 'upcoming')
        tab = 2
      if (event_type == 'completed')
        tab = 3
     
      this.props.history.push({ pathname: '/opinionTrading/publish_match/' + selectedObj.league_id + '/' + selectedObj.season_id + '/' + tab})
    }
  }

  // redirectToNewSalaryReview = (selectedObj) => {
  //   // this.props.history.push({ pathname: '/OpinionTrading/publish_match/' + selectedObj.league_id + '/' + selectedObj.season_ga+ '/' + tabme_uid })
  //   this.props.history.push({  pathname: '/opinionTrading/add_question/' + selectedObj.league_id + '/' + selectedObj.season_id + '/' + tab, state: { selectedObj: selectedObj } })
  // }


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
        fixture_status: (tab == 3) ? 2 : 'upcoming',
        fixtureList: [],

      }, function () {
        this.GetAllFixtureList();
      });
    }
  }

  toggleFixtureTab(tab) {
    if (this.state.activeFixtureTab !== tab) {
      this.setState({
        activeTab: '1',
        activeFixtureTab: tab,
        fixture_status: "not_complete",
        filter: {
          current_page: 1,
          items_perpage: 50,
          type: tab
        },
      }, () => {
        this.GetAllFixtureList();
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

    var old = moment(fixtureObjData.scheduled_date).subtract(fixtureObjData.delay_hour, 'hours').subtract(fixtureObjData.delay_minute, 'minutes').format('DD-MMM-YYYY hh:mm A');

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
      "delay_message": delay_message,
      "league_id": fixtureObjData.league_id,
    };

    // console.log(params);return false;

    this.setState({ delayPosting: false })
    WSManager.Rest(NC.baseURL + NC.UPDATE_TRADE_FIXTURE_DELAY, params).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        console.log('success')
        this.setState({ delayPosting: true })
        this.GetAllLeagueList();
        this.openDelayModal(fixtureObjData)
        notify.show(responseJson.message, "success", 5000);
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
          console.log('sec')
        WSManager.logout();
        this.props.history.push('/login');
      } else {
           notify.show(responseJson.message, "error", 5000);
        this.setState({ delayPosting: true })
      }
    })
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

    // console.log(param);return false;

    WSManager.Rest(NC.baseURL + NC.UPDATE_TRADE_FIXTURE_CUSTOM_MESSAGE, param).then((responseJson) => {
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

  viewWinners = (e, templateObj) => {
    this.setState({
      prize_modal: !this.state.prize_modal,
      templateObj: templateObj
    });
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

  markPinFixture = () => {
    let { fxSeasonid, fxSeasonGameUid, fxIdx, fxPinVal, selected_sport } = this.state
    this.setState({ fxPinPosting: true })
    let params = {
      "season_id": fxSeasonid,
      // "season_game_uid": fxSeasonGameUid,
      "sports_id": selected_sport,
    };
    if (fxPinVal == '1') {
      params.is_pin_season = '0'
    }  

    WSManager.Rest(NC.baseURL + NC.OT_PIN_FIXTURE, params).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        let fixtureList = _cloneDeep(this.state.GetAllFixtureList);
        // fixtureList.upcoming_fixture[fxIdx]['is_pin_season'] = (fxPinVal == '1') ? '0' : '1'
        this.setState({ fxPinModalOpen: false });

        notify.show(responseJson.message, "success", 5000);
        this.GetAllFixtureList();
      } else {
        notify.show(responseJson.message, "error", 3000);
      }
      this.setState({ fxPinPosting: false })
    })
  }

  // fxPinModal = (item, idx) => {
  //   let msg_status = (item.is_pin_fixture == '1') ? 'remove' : 'mark';
  //   let msg = 'Are you sure you want to ' + msg_status + ' pin ?'
  //   this.setState({
  //     fxPinModalOpen: !this.state.fxPinModalOpen,
  //     fxLeagueId: item.league_id,
  //     fxSeasonGameUid: item.season_game_uid,
  //     fxPinMsg: msg,
  //     fxIdx: idx,
  //     fxPinVal: item.is_pin_fixture,
  //   })
  // }

  promoteFx = (val) => {
    let qStr = HF.promoteFixture(val)
    if (val.delay_minute > 0) {
      qStr.email_template_id = 7
    }
    const stringified = queryString.stringify(qStr);
    this.props.history.push(`/marketing/new_campaign?${stringified}`);
    return false;
  }

  fxPinModal = (item, idx) => {    
    let msg_status = (item.is_pin_season == '1') ? 'remove' : 'mark';
    let msg = 'Are you sure you want to ' + msg_status + ' pin ?'
    this.setState({
      fxPinModalOpen: !this.state.fxPinModalOpen,      
      fxSeasonid: item.season_id,
      fxPinMsg: msg,
      fxIdx: idx,
      fxPinVal: item.is_pin_season,
    })
  }

   handleSports = (e,name) => {   
      const value = e.value
      const Labels = e.label

      // [name] = Labels   
      this.setState({
         sports_id: value,
         selected_sports_id: value,
         selected_sport: e.value,
         CURRENT_PAGE : 1
      }, () => {

         LS.set('selectedSport', this.state.selected_sport)
        this.GetAllLeagueList()
      })
   }

  render() {
    const { activeFixtureTab, activeTab, total, leagueList, fixtureList, filter, fixture_status, fxPinModalOpen, fxPinPosting, fxPinMsg, selected_sport, msgModalIsOpen, msgFormValid, MsgItems, Message, DelayModalIsOpen, delayPosting, fixtureObjData, delay_hour, delay_minute, delay_message, HourMsg, MinuteMsg } = this.state;
      
    let fxPinModalProps = {
      publishModalOpen: fxPinModalOpen,
      publishPosting: fxPinPosting,
      modalActionNo: this.fxPinModal,
      modalActionYes: this.markPinFixture,
      MainMessage: fxPinMsg,
      SubMessage: '',
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

    return (

      <div className="animated fadeIn fk-dfs-main">
        {fxPinModalOpen && <PromptModal {...fxPinModalProps} />}
        {msgModalIsOpen && <Dfs_MatchAlertMsgModal {...MatchAlertMsgProps} />}
        {DelayModalIsOpen && <Dfs_MatchDelayAlertModal {...MatchDelayAlertMsgProps} />}

        <Row>
          <Col md ={9}>
            <div className='selector-dropdown'>
            <div className='in-div'>
              <label className="filter-label">Select Sports </label>
              <Select
                  className="dfs-selector"
                  id="selected_sport"
                  name="selected_sport"
                  placeholder="Select Sport"
                  value={this.state.selected_sport}
                  options={this.state.sportsOptions}
              onChange={(e) => this.handleSports(e)}
              />
            </div>

             <div className='in-div'>
               <label className="filter-label">Select League </label>
            <Select
              className="dfs-selector"
              id="selected_league"
              name="selected_league"
              placeholder="Select League"
              value={this.state.selected_league}
              options={leagueList}
              onChange={(e) => this.handleSelect(e, 'selected_league')}
            />
            </div>
            </div>
          </Col>
          {/* <Col md={8}>
             <div>
               <label className="filter-label">Select League </label>
            <Select
              className="dfs-selector"
              id="selected_league"
              name="selected_league"
              placeholder="Select League"
              value={this.state.selected_league}
              options={leagueList}
              onChange={(e) => this.handleSelect(e, 'selected_league')}
            />
            </div>
          </Col> */}
        </Row>
        <Row>
          <Col md={12}>
            <div className="user-navigation mb-30">
              {/* <Nav tabs>
                <NavItem className={activeFixtureTab === '1' ? "active" : ""}
                  onClick={() => { this.toggleFixtureTab('1'); }}>
                  <NavLink>
                    Fixture
                </NavLink>
                </NavItem>
              </Nav> */}
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
                                            // var mEndDate = new Date(WSManager.getUtcToLocal(item['2nd_inning_date']));
                                            // var curDate = new Date();
                                            // let compDate = false;
                                            // if (curDate >= mEndDate) {
                                            //   compDate = true;
                                            // }
                                            return (
                                              <div className={`flip-animation common-fixture${(item.highlight == '1') ? ' mth-star' : ''}`} key={"live-fixtures-" + index}>
                                                <div className="bg-card">
                                                  <div className="dfs-mn-hgt">
                                                    <img className="com-fixture-flag float-left" src={NC.S3 + NC.FLAG + item.home_flag}></img>
                                                    <img className="com-fixture-flag float-right" src={NC.S3 + NC.FLAG + item.away_flag}></img>
                                                    <div className="com-fixture-container">
                                                      <div onClick={() => this.redirectToSalaryReview(item, 'live')} className="com-fixture-name">{(item.home) ? item.home : 'TBA'} VS {(item.away) ? item.away : 'TBA'}</div>
                                                      {
                                                        (_isEmpty(item['2nd_inning_date'])) &&
                                                        <div className="com-fixture-time">
                                                          {/* {WSManager.getUtcToLocalFormat(item.season_scheduled_date, 'D-MMM-YYYY hh:mm A')} */}
                                                          {HF.getFormatedDateTime(item.scheduled_date, 'D-MMM-YYYY hh:mm A')}
                                                        </div>
                                                      }
                                                      <div className="com-fixture-title">{item.league_name}</div>
                                                    </div>
                                                  </div>
                                                  {
                                                    ((item.status == "0" || item.status == "1" || item.status == "2") && (item.status_overview == "0" || item.status_overview == "4")) ?
                                                      <ul className="fx-action-list">
                                                        {/* <li
                                                          className="fx-action-item"
                                                          onClick={() => this.openMsgModal(item)}
                                                        >
                                                          <i className="icon-email_verified"
                                                            title="Add alert message"></i>
                                                        </li> */}
                                                        {/* <li className="fx-action-item">
                                                          <i
                                                            title="Match stats"
                                                            className="icon-stats"
                                                            onClick={() => this.props.history.push({ pathname: '/livefantasy/season_schedule/' + item.league_id + '/' + item.season_game_uid + '/' + this.state.selected_sport + '/' + activeTab })}
                                                          ></i>
                                                        </li> */}
                                                      </ul>
                                                      : ""
                                                      // <div className="fx-match-cancel">{this.getMatchMsg(item.status, item.status_overview)}</div>
                                                  }
                                                </div>
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
                                              // item.is_published == '1' &&
                                              <div style={{ zIndex: 1, cursor: 'pointer', position: 'absolute', marginLeft: item.is_pin_season == '1' ? '0px' : '10px' }}>
                                                {
                                                  item.is_pin_season == '1' &&
                                                  <img onClick={(e) => this.fxPinModal(item, index)} src={Images.PIN_ACTIVE} alt="" className="pinned-active" />
                                                }
                                                {
                                                  item.is_pin_season == '0' &&
                                                  <i onClick={(e) => this.fxPinModal(item, index)} className="icon-pinned"></i>
                                                }
                                              </div>
                                            }
                                            <div className="bg-card">
                                              <div className="dfs-mn-hgt">
                                                <img className="com-fixture-flag float-left" src={NC.S3 + NC.FLAG + item.home_flag}></img>
                                                <img className="com-fixture-flag float-right" src={NC.S3 + NC.FLAG + item.away_flag}></img>
                                                <div className="com-fixture-container">
                                                  <div className="com-fixture-name" onClick={() => this.redirectToSalaryReview(item, 'upcoming')}>{(item.home) ? item.home : 'TBA'} VS {(item.away) ? item.away : 'TBA'}</div>
                                                  <div className="com-fixture-time xlivcardh6">
                                                    {/* {WSManager.getUtcToLocalFormat(item.season_scheduled_date, 'D-MMM-YYYY hh:mm A')} */}
                                                    {HF.getFormatedDateTime(item.scheduled_date, 'D-MMM-YYYY hh:mm A')}
                                                  </div>
                                                  <div className="com-fixture-title xlivcardh6">{item.league_name}</div>
                                                </div>
                                              </div>
                                              {
                                                ((item.status == "0" || item.status == "1" || item.status == "2")) ?
                                                  <ul className="fx-action-list">
                                                   {/* {
                                                      item.is_salary_changed == 1 && item.is_published == 0 &&
                                                      <li className="fx-action-item">
                                                        <i
                                                          title="Verify and Publish"
                                                          className="icon-fixture_published"
                                                          onClick={() => this.redirectToSalaryReview(item)}
                                                        ></i>
                                                      </li>
                                                    }  */}
                                                    {/* {
                                                      item.is_published == 0 ?
                                                      item.is_published == 0 &&
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
                                                            title="Update Over"
                                                            className="icon-Salary-update"
                                                            onClick={() => this.redirectToNewSalaryReview(item)}
                                                          ></i></li>
                                                    } */}
                                                    <li className="fx-action-item">
                                                      <i
                                                        title="Mark match delay"
                                                        className="icon-delay"
                                                        onClick={() => this.openDelayModal(item)}
                                                      ></i>
                                                    </li>

                                                    {/* <li
                                                      title="Add alert message"
                                                      className="fx-action-item"
                                                      onClick={() => this.openMsgModal(item)}
                                                    >
                                                      <i className="icon-email_verified" title="Add alert message"></i>
                                                    </li> */}
                                                    {
                                                     
                                                      <li className="fx-action-item">
                                                        <i
                                                          title="Published"
                                                          className="icon-fixture-contest"
                                                          onClick={() => this.redirectToPublish(item, 'upcoming')}
                                                        ></i>
                                                      </li>
                                                    }
                                                    {/* {
                                                      item.is_salary_changed == 1 && item.is_published == 1 &&
                                                      <Fragment>
                                                        <li className="fx-action-item">
                                                          <i
                                                            title="Contest Template"
                                                            className="icon-template"
                                                            onClick={() => this.redirectToContestTemplate(item, 0)}
                                                          ></i>
                                                        </li>
                                                      </Fragment>

                                                    } */}
                                                  </ul>
                                                  :
                                                  <div className="fx-match-cancel">{this.getMatchMsg(item.status, item.status_overview)}</div>
                                              }
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
                                                <div className="bg-card">
                                                  <div className="dfs-mn-hgt" onClick={() => this.redirectToSalaryReview(item, 'completed')}>

                                                    <img className="com-fixture-flag float-left xcardimg" src={NC.S3 + NC.FLAG + item.home_flag}></img>
                                                    <img className="com-fixture-flag float-right xcardimg" src={NC.S3 + NC.FLAG + item.away_flag}></img>
                                                    <div className="com-fixture-container">
                                                      <div className="com-fixture-name xlivcardh3">{(item.home) ? item.home : 'TBA'} VS {(item.away) ? item.away : 'TBA'}</div>
                                                      <div className="com-fixture-time xlivcardh6">
                                                        {/* {WSManager.getUtcToLocalFormat(item.season_scheduled_date, 'D-MMM-YYYY hh:mm A')} */}
                                                        {HF.getFormatedDateTime(item.scheduled_date, 'D-MMM-YYYY hh:mm A')}
                                                      </div>
                                                      <div className="com-fixture-title xlivcardh6">{item.league_name}</div>
                                                    </div>
                                                  </div>
                                                  {
                                                    ((item.status == "0" || item.status == "1" || item.status == "2") && (item.status_overview == "0" || item.status_overview == "4")) ?
                                                      <ul className="fx-action-list">
                                                        {/* <li
                                                          className="fx-action-item"
                                                          onClick={() => this.props.history.push({ pathname: '/livefantasy/season_schedule/' + item.league_id + '/' + item.season_game_uid + '/' + this.state.selected_sport + '/' + activeTab })}
                                                        >
                                                          <i
                                                            className="icon-stats"
                                                            title="Match stats"></i>
                                                        </li> */}
                                                      </ul>
                                                      : ""
                                                      // <div className="fx-match-cancel">{this.getMatchMsg(item.status, item.status_overview)}</div>
                                                  }
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
                              }
                            </Col>
                          </Row>
                        </TabPane>
                      </TabContent>
                    </Col>
                  </Row>
                  {
                    ( total > filter.items_perpage) && (
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
              </TabContent>
            </div>
          </Col>
        </Row>

      </div>
    );
  }
}
export default OT_DFS;

