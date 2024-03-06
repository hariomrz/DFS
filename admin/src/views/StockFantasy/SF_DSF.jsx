import React, { Component } from 'react';
import { Col, Row, Button, TabContent, TabPane, Nav, NavItem, NavLink } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import moment from 'moment';
import LS from 'local-storage';
import Pagination from "react-js-pagination";
import { notify } from 'react-notify-toast';
import queryString from 'query-string';
import { MomentDateComponent } from "../../components/CustomComponent";
import { STAR_CONFIRM_MSG, R_STAR_CONFIRM_MSG } from "../../helper/Message";
import HF, { _times, _Map, _isUndefined, _isEmpty, _cloneDeep } from "../../helper/HelperFunction";
import Loader from '../../components/Loader';
import PromptModal from '../../components/Modals/PromptModal';
import Images from '../../components/images';
import SF_MatchAlertMsgModal from './SF_MatchAlertMsgModal';
import SF_FixtureCard from './SF_FixtureCard';
import SF_AddFixtureModal from './SF_AddFixtureModal';
import { SF_getHoliday } from '../../helper/WSCalling';
class SF_DSF extends Component {
  constructor(props) {
    super(props);
    let filter = {
      current_page: 1,
      items_perpage: 50,
      type: 1
    }
    this.state = {
      filter: filter,
      current_page: 1,
      total: 0,
      TotalCount  :0,
      clickedCard: '',
      selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
      leagueList: [],
      fixtureList: [],
      activeTab: "0",
      msgModalIsOpen: false,
      DelayModalIsOpen: false,
      msgFormValid: true,
      MsgItems: {},
      fixtureObjData: {},
      Message: '',
      delay_hour: '',
      delay_minute: '',
      delay_message: '',
      delayPosting: true,
      HourMsg: false,
      MinuteMsg: false,
      activeFxType: "1",
      PERPAGE: NC.ITEMS_PERPAGE,
      addFxModalOpen: false,
      DateArray: [],
      WeekArray: [],
      MonthArray: [],
      NextIndex: 0,
      PrevIndex: 0,
      DateLength: '',
      WeekLength: '',
      MonthLength: '',
      FixtureDate: '',
      FixtureName: '',
      Holidays: [],
    };
  }

  componentDidMount() {

    if (HF.allowStockFantasy() == '0') {
      notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
      this.props.history.push('/dashboard')
    }
    
    // this.getNextDate()
    this.GetHolidayList()
    this.getMonth()
    this.getWeekDay()   

    this.GetCategoryList();
    let values = queryString.parse(this.props.location.search)
    this.setState({
      activeTab: !_isEmpty(values) ? (values.tab) ? values.tab : '1' : '0',
      activeFxType: !_isEmpty(values) ? !_isUndefined(values.pctab) ? values.pctab : (values.fixtab) ? values.fixtab : '1' : '1',
    }, () => {
      this.GetAllFixtureList();
    })
  }

  GetCategoryList = () => {
    WSManager.Rest(NC.baseURL + NC.SF_GET_ALL_CATEGORY_LIST, {}).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        responseJson = responseJson.data;
        this.setState({ CategoryList: responseJson });
      }
    })
  }

  // GET ALL FIXTURE LIST
  GetAllFixtureList = () => {
    let { activeFxType, filter, activeTab } = this.state
    let param = {
      // "items_perpage": filter.items_perpage,
      // "current_page": filter.current_page,
      // "sort_order": (filter.type != 2) ? "DESC" : "ASC",
      // "sort_field": "season_scheduled_date",
      "status": activeTab,
      "category_id": activeFxType,
      "items_perpage": "50"
    }

    this.setState({ posting: true })

    WSManager.Rest(NC.baseURL + NC.SF_GET_FIXTURES, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        let responseJsonData = responseJson.data;
        this.setState({
          posting: false,
          fixtureList: responseJsonData.result,
          total: responseJsonData.total,
          TotalCount : responseJsonData.total,
          fixtureListLength: responseJsonData.result.live_fixture.length
        })
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        WSManager.logout();
        this.props.history.push('/login');
      }
    })
  }

  redirectToStock = (selectedObj) => {
    let { activeFxType, activeTab } = this.state    
    this.props.history.push({ pathname: '/stockfantasy/fixturecontest/' + activeFxType + '/' + activeTab + '/' + selectedObj.collection_id });
  }

  redirectToNewStockReview = (itemObj) => {
    let send_val = this.getUrlFxInput(itemObj)
    this.props.history.push({ pathname: '/stockfantasy/verify-stocks/' + this.state.activeFxType + '/' + this.state.activeTab + '/' + send_val + '/' + itemObj.collection_id + '/' + itemObj.name })
  }

  redirectToContestTemplate = (selectedObj) => {
    let { activeFxType, activeTab } = this.state
    let send_val = this.getUrlFxInput(selectedObj)
    this.props.history.push({ pathname: '/stockfantasy/createtemplatecontest/' + activeFxType + '/' + activeTab + '/' + send_val + '/' + selectedObj.collection_id });
  }

  handlePageChange(current_page) {
    // let filter = this.state.filter;
    // filter['current_page'] = current_page;
    // this.setState({
    //   filter: filter,
    //   fixtureList: []
    // },
    //   function () {
    //     this.GetAllFixtureList();
    //   });
    this.setState({
      current_page: current_page
    }, () => {
      this.GetAllFixtureList();
    })

  }

  toggleTab(tab) {
    if (this.state.activeTab !== tab) {
      this.setState({
        activeTab: tab,
        PageScroll: false,
        fixtureList: [],
        DT_CURRENT_PAGE: 1,
      }, function () {
        this.GetAllFixtureList();
      });
    }
  }

  toggleFixtureTab(tab) {
    if (this.state.activeFxType !== tab) {
      this.setState({
        activeTab: '0',
        activeFxType: tab,
        fixtureList: [],
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

    var old = moment(fixtureObjData.season_scheduled_date).subtract(fixtureObjData.delay_hour, 'hours').subtract(fixtureObjData.delay_minute, 'minutes').format('DD-MMM-YYYY hh:mm A');

    var old_1 = moment(WSManager.getUtcToLocal(old)).format("DD-MMM-YYYY hh:m A")

    var returned_endate = moment(old_1).add(delay_hour, 'hours').add(delay_minute, 'minutes').format('DD-MMM-YYYY hh:mm A');

    fixtureObjData['new_deadline'] = returned_endate;
    // }

    this.setState({ fixtureObjData: fixtureObjData });
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
        "collection_id": MsgItems.collection_id,
        "custom_message": Message,  
        "is_remove": "0",      
      }
    } else {
      param = {
        "collection_id": MsgItems.collection_id,
        "custom_message": "",
        "is_remove": "1",        
      }
    }

    WSManager.Rest(NC.baseURL + NC.SF_UPDATE_FIXTURE_CUSTOM_MESSAGE, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        notify.show(responseJson.message, "success", 5000);
        this.GetAllFixtureList()
        this.openMsgModal(MsgItems)
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

  StarModalToggle = (idx, star_flg, call_from) => {
    let msg = (star_flg == '1') ? R_STAR_CONFIRM_MSG : STAR_CONFIRM_MSG
    this.setState({
      StarModalOpen: !this.state.StarModalOpen,
      StarItemIdx: idx,
      StarMessage: msg,
      StarCallFrom: call_from,
    });
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

  promoteFx = (val) => {
    let qStr = HF.promoteFixture(val)
    if (val.delay_minute > 0) {
      qStr.email_template_id = 7
    }
    const stringified = queryString.stringify(qStr);
    this.props.history.push(`/marketing/new_campaign?${stringified}`);
    return false;
  }

  getSFCard = (list) => {
    return (
      _Map(list, (item, idx) => {
        return (
          <SF_FixtureCard
            key={idx}
            callfrom={'1'}
            activeFxTab={this.state.activeFxType}
            activeTab={this.state.activeTab}
            edit={false}
            item={item}
            redirectToTemplate={(itemObj) => this.redirectToContestTemplate(itemObj)}
            redirectToStockReview={(itemObj) => this.redirectToNewStockReview(itemObj)}
            redirectToUpdateStock={(itemObj) => this.redirectToStock(itemObj)}
            openMsgModal={(itemObj) => this.openMsgModal(itemObj)}
            openDelayModal={null}
            show_flag={true}
          />
        )
      })
    )
  }

  addFxModalToggle = (fx_type, fx_status) => {
    let { DateArray, WeekArray, MonthArray } = this.state

    let fx_date = '';
    if (fx_type == '1') {
      fx_date = DateArray[0]
    }
    else if (fx_type == '2') {
      fx_date = WeekArray[0]
    }
    else if (fx_type == '3') {
      fx_date = MonthArray[0]
    }

    this.setState({
      FixtureDate: fx_date,
      NextIndex: 0,
      PrevIndex: 0,
      addFxModalOpen: !this.state.addFxModalOpen
    })
  }

  addFixtureNext = () => {
    let { activeFxType, activeTab, FixtureDate, FixtureName } = this.state
    let fx_val = FixtureDate
    if (!_isEmpty(FixtureName) && (FixtureName.length < 3 || FixtureName.length > 20)) {
      notify.show('Fixture Name should be in the range of 3 to 20', "error", 3000);
      return false
    }
    if (activeFxType == '1') {
      fx_val = HF.getDateFormat(FixtureDate, 'YYYY-MM-DD')      
    }
    else if (activeFxType == '3') {
      fx_val = HF.getMonthNumFromString(FixtureDate)      
    }
    let param = {
      "category_id": parseInt(activeFxType),
      "value": fx_val,
      "name": FixtureName,
    }

    WSManager.Rest(NC.baseURL + NC.SF_VALIDATE_FIXTURE, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        
        notify.show(responseJson.message, "success", 5000);
        let new_name = FixtureName ? FixtureName : 0;
        this.props.history.push({ pathname: '/stockfantasy/verify-stocks/' + activeFxType + '/' + activeTab + '/' + FixtureDate + '/0/' + new_name })
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        WSManager.logout();
        this.props.history.push('/login');
      }
    })
  }

  getUrlFxInput = (selectedObj) => {
    let send_val = ''
    if (this.state.activeFxType == '1') {
      send_val = selectedObj.scheduled_date
    }
    else if (this.state.activeFxType == '2') {
      send_val = selectedObj.week
    }
    else if (this.state.activeFxType == '3') {
      send_val = selectedObj.month
    }
    return send_val
  }

  addMonths = (d, n) => {
    var dt = new Date(d.getTime());
    dt.setMonth(dt.getMonth() + n);
    return dt;
  }

  getMonth = () => {
    var d = new Date();
    var monthNames = ["January", "February", "March", "April", "May", "June",
      "July", "August", "September", "October", "November", "December"
    ];
    // var cur_m = this.addMonths(d, 0);
    var d1 = this.addMonths(d, 1);
    var d2 = this.addMonths(d, 2);

    // var m_arr = [monthNames[cur_m.getMonth()], monthNames[d1.getMonth()], monthNames[d2.getMonth()]]
    var m_arr = [monthNames[d1.getMonth()], monthNames[d2.getMonth()]]
    this.setState({ MonthArray: m_arr, MonthLength: (m_arr.length - 1) })
  }

  getWeekNumber = (weekdate) => {
    var date1 = new Date(weekdate);
    var oneJan = new Date(date1.getFullYear(), 0, 1);
    var numberOfDays = Math.floor((date1 - oneJan) / (24 * 60 * 60 * 1000));
    var result = Math.floor((date1.getDay() + 1 + numberOfDays) / 7);
    return result;
  }

  getWeekDay = () => {
    var firstDay = new Date();
    var wk_1_date = new Date(firstDay.getTime() + 7 * 24 * 60 * 60 * 1000);
    var wk_2_date = new Date(firstDay.getTime() + 14 * 24 * 60 * 60 * 1000);
    var wk_3_date = new Date(firstDay.getTime() + 21 * 24 * 60 * 60 * 1000);
    var wk_4_date = new Date(firstDay.getTime() + 28 * 24 * 60 * 60 * 1000);

    var week_arr = [this.getWeekNumber(wk_1_date), this.getWeekNumber(wk_2_date), this.getWeekNumber(wk_3_date), this.getWeekNumber(wk_4_date)]

    this.setState({ WeekArray: week_arr, WeekLength: (week_arr.length - 1) })
  }

  getNextDate = () => {
    let d_arr = []
    // var today = new Date('2021-08-03');
    var today = new Date();
    var year = today.getFullYear();
    var month = today.getMonth();
    var date = today.getDate();
    for (var i = 1; i < 41; i++) {
      var day = new Date(year, month, date + i);

      if (day.getDay() == 6 || day.getDay() == 0 || this.state.Holidays.includes(moment(day).format('YYYY-MM-DD'))) {
        //If week days needed        
      } else {        
        // d_arr.push(moment(day).format('DD/MM/YYYY'))
        d_arr.push(day)
      }
    }


    this.setState({ DateArray: d_arr, DateLength: (d_arr.length - 1) })
  }

  nextPrevFxValue = (next_prev) => {
    let { activeFxType, DateArray, WeekArray, MonthArray, NextIndex } = this.state
    let new_idx = '';
    if (next_prev) {
      new_idx = NextIndex + 1;
    }
    else {
      new_idx = NextIndex - 1;
    }

    let fx_val = '';
    if (activeFxType == '1') {
      fx_val = DateArray[new_idx]
    }
    else if (activeFxType == '2') {
      fx_val = WeekArray[new_idx]
    }
    else if (activeFxType == '3') {
      fx_val = MonthArray[new_idx]
    }

    this.setState({ FixtureDate: fx_val, NextIndex: new_idx })
  }

  handleFxInputChange = (e) => {
    let name = e.target.name
    let value = e.target.value
    value = HF.allowOneSpace(value)
    this.setState({ [name]: value })
  }

  GetHolidayList = () => {
    var today = new Date();
    var year = today.getFullYear();
    let params = { "year": year }

    SF_getHoliday(params).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        responseJson = responseJson.data ? responseJson.data : [];
        this.setState({ Holidays: responseJson }, function () {
          this.getNextDate()
        });
      }
    })
  }

  render() {
    const { MiniLeActiveTab, activeFxType, activeTab, total, fixtureList, filter, DfsT_List, DfsT_Total, DfsT_Posting, DelayModalIsOpen, delayPosting, fixtureObjData, delay_hour, delay_minute, delay_message, HourMsg, MinuteMsg, msgModalIsOpen, msgFormValid, MsgItems, Message, fxPinModalOpen, fxPinPosting, fxPinMsg, addFxModalOpen, addFxPosting, CategoryList, FixtureDate, DateLength, WeekLength, MonthLength, NextIndex, PrevIndex, FixtureName } = this.state;

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
      activeFxType: activeFxType,
      activeTab: activeTab,
    }

    let MatchAlertMsgProps = {
      msgModalIsOpen: msgModalIsOpen,
      msgFormValid: msgFormValid,
      openMsgModal: this.openMsgModal,
      handleInputChange: this.handleInputChange,
      updateMatchMsg: this.updateMatchMsg,
      MsgItems: MsgItems,
      Message: Message,
      activeFxType: activeFxType,
      activeTab: activeTab,
    }

    let fxPinModalProps = {
      publishModalOpen: fxPinModalOpen,
      publishPosting: fxPinPosting,
      modalActionNo: this.fxPinModal,
      modalActionYes: this.markPinFixture,
      MainMessage: fxPinMsg,
      SubMessage: '',
    }

    let addFixtureProps = {
      modal_open: addFxModalOpen,
      posting: addFxPosting,
      modal_action_no: this.addFxModalToggle,
      modal_action_yes: this.addFixtureNext,
      active_fx_type: activeFxType,
      fixture_date: FixtureDate,
      date_length: DateLength,
      week_length: WeekLength,
      month_length: MonthLength,
      next_index: NextIndex,
      prev_index: PrevIndex,
      prev_fx_value: () => this.nextPrevFxValue(false),
      next_fx_value: () => this.nextPrevFxValue(true),
      fixture_name: FixtureName,
      handle_input_change: (e) => this.handleFxInputChange(e),
    }

    return (
      <div className="animated fadeIn sf-dsf-main">
        {msgModalIsOpen && <SF_MatchAlertMsgModal {...MatchAlertMsgProps} />}
        {fxPinModalOpen && <PromptModal {...fxPinModalProps} />}
        {addFxModalOpen && <SF_AddFixtureModal {...addFixtureProps} />}
        <Row>
          <Col md={12}>
            <h2 className="h2-cls">Fixture</h2>
          </Col>
        </Row>
        <Row>
          <Col md={12}>
            <div className="user-navigation mb-30">
              <Nav tabs>
                {
                  _Map(CategoryList, (item, idx) => {
                    return (
                      <NavItem key={idx} className={activeFxType === item.category_id ? "active" : ""}
                        onClick={() => { this.toggleFixtureTab(item.category_id); }}>
                        <NavLink>
                          {item.name}
                        </NavLink>
                      </NavItem>
                    )
                  })
                }

              </Nav>
              <TabContent>
                <TabPane>
                  <Row className="sf-dfs-tabs">
                    <Col md={12}>
                      <Row>
                        <Col md={9}>
                          <Nav tabs>
                            <NavItem>
                              <NavLink
                                className={activeTab === '0' ? "active" : ""}
                                onClick={() => { this.toggleTab('0'); }}
                              >
                                <label className="live">Live</label>
                              </NavLink>
                            </NavItem>
                            <NavItem>
                              <NavLink
                                className={activeTab === '1' ? "active" : ""}
                                onClick={() => { this.toggleTab('1'); }}
                              >
                                <label className="live">Upcoming</label>
                              </NavLink>
                            </NavItem>
                            <NavItem>
                              <NavLink
                                className={activeTab === '2' ? "active" : ""}
                                onClick={() => { this.toggleTab('2'); }}
                              >
                                <label className="live">Completed</label>
                              </NavLink>
                            </NavItem>
                          </Nav>
                        </Col>
                        <Col md={3}>
                          {
                            activeTab == '1' &&
                            <div className="sf-add-btn">
                              <Button onClick={() => this.addFxModalToggle(activeFxType, activeTab)} className="btn-secondary">Add Fixture</Button>
                            </div>
                          }
                        </Col>
                      </Row>

                      <TabContent activeTab={activeTab}>
                        <TabPane tabId="0">
                          <Row className="sf-row">
                            <Col lg={12} className="sf-content">
                              {this.getSFCard(fixtureList.live_fixture)}
                              {
                                _isEmpty(fixtureList.live_fixture) &&
                                <Col md={12}>
                                  <div className="no-records">{NC.NO_RECORDS}</div>
                                </Col>
                              }
                            </Col>
                          </Row>
                        </TabPane>
                        <TabPane tabId="1">
                          <Row className="sf-row">
                            <Col lg={12} className="sf-content">
                              {this.getSFCard(fixtureList.upcoming_fixture)}
                            </Col>
                          </Row>
                          {
                            _isEmpty(fixtureList.upcoming_fixture) &&
                            <Col md={12}>
                              <div className="no-records">{NC.NO_RECORDS}</div>
                            </Col>
                          }
                          
                        </TabPane>

                        <TabPane tabId="2">
                          <Row className="sf-row">
                            <Col lg={12} className="sf-content">
                              {this.getSFCard(fixtureList.live_fixture)}
                              {
                                _isEmpty(fixtureList.live_fixture) &&
                                <Col md={12}>
                                  <div className="no-records">{NC.NO_RECORDS}</div>
                                </Col>
                              }
                            </Col>
                          </Row>
                          
                        }
                        </TabPane>
                      </TabContent>
                    </Col>
                  </Row>
                  
                </TabPane>
              </TabContent>
              {this.state.TotalCount > 0 && this.state.fixtureListLength != 0 ? (<Row>
                          <Col md={12}>
                            <div className="custom-pagination lobby-paging">
                              <Pagination
                                activePage={this.state.current_page}
                                itemsCountPerPage={50}
                                totalItemsCount={this.state.TotalCount}
                                pageRangeDisplayed={5}
                                onChange={e => this.handlePageChange(e)}
                              />
                            </div>

                          </Col>
                        </Row>) : ''}
              
            </div>
          </Col>
        </Row>

      </div>
    );
  }
}
export default SF_DSF;

