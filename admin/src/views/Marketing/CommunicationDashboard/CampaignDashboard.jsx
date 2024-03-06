import React, { Component, Fragment } from 'react';
import Select from 'react-select';
import {
  Card, CardBody, Col, Row, Input, Button, Label, Table, TabContent, TabPane, Nav, NavItem, NavLink, Modal, ModalBody, ModalHeader, PaginationItem, PaginationLink
} from 'reactstrap';
import Moment from 'react-moment';
import _ from 'lodash';
import * as NC from "../../../helper/NetworkingConstants";
import * as MODULE_C from "../Marketing.config";
import WSManager from "../../../helper/WSManager";
import moment from 'moment';
import LS from 'local-storage';
import Images from '../../../components/images';
import { notify } from 'react-notify-toast';
import classnames from 'classnames';
import queryString from 'query-string';
import { connect } from 'react-redux';
import * as  actionTypes from '../../../appConstants/ActionTypes';
import PromoteFixtureModal from '../../../Modals/PromoteFixture';
import HF, { _isUndefined } from '../../../helper/HelperFunction';
import { MomentDateComponent } from "../../../components/CustomComponent";
import { Base64 } from 'js-base64';
import Pagination from "react-js-pagination";
import Loader from '../../../components/Loader';
class CampaignDashboard extends Component {
  constructor(props) {
    super(props);
    this.state = {
      selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
      modal: false,
      recent_communication_list: [],
      preview_modal: false,
      cd_balance: {},
      communication_review_modal: false,
      communication_resend_modal: false,
      RC_obj_detail: {},
      fixtureObj: { email_count: 0, sms_count: 0, notification_count: 0 },
      Resend_obj_detail: {},
      notificationData: {},
      fixturePromoteParam: {
        email_fixture_model: false,
        message_fixture_model: false,
        notification_fixture_model: false
      },
      templateList: [],
      userbase: null,
      recent_communication_id: null,
      buy_communication_entity_modal: false,
      buy_sms_modal: false,
      buy_current_entity: null,
      notify_entity_value: null,
      notify_sms_value: null,
      notify_sms_price: 0.2,
      notify_sms_amount: 0,
      buy_notification_modal: false,
      notify_notification_value: null,
      notify_notification_price: 0,
      notify_notification_amount: 0,
      isLoading: false,
      activeTab: '1',
      length: 9,
      userBaseType: MODULE_C.userBaseType,
      fixtures: [],
      fixture_promote_model: false,
      recentCommunicationParams: {
        currentPage: 0,
        pageSize: 6,
        pagesCount: 1,
        sort_order: 'DESC',
        sort_field: 'recent_communication_id'
      },
      depositPromocodes: [],
      notify_email_price: 0.2,
      notify_email_amount: 0,
      minPage: 1,
      maxPage: 5,
      fixture_image_path: "",
      SentCount: [],
      SEND_CURRENT_PAGE: 1,
      SCHE_CURRENT_PAGE: 1,
      PERPAGE: 10,
      SendPosting: false,
      SchPosting: false,
      callFlag: '',
      ScheList: [],
      ModalCallFrom: '',
    };

    this.toggle = this.toggle.bind(this);
    this.toggle_ = this.toggle_.bind(this);

    this.togglePrimary = this.togglePrimary.bind(this);


    this.dataSet = [...Array(Math.ceil(500 + Math.random() * 500))].map(
      (a, i) => "Record " + (i + 1)
    );

    this.pageSize = 50;
    this.pagesCount = Math.ceil(this.dataSet.length / this.pageSize);

  }

  getDepositPromotionsPromocodes = () => {
    WSManager.Rest(NC.baseURL + MODULE_C.GET_DEPOSIT_PROMOCODES, {}).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        this.setState({ depositPromocodes: responseJson.data.promocodes });

      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/login');
      } else {
        this.setState({ posting: false });
      }
    })
  }

  handleRecentCommunicationPagination(e, index) {

    e.preventDefault();

    var minPage = 1;
    var maxPage = 5;
    if (index >= 1 && index < 5) {
      maxPage = 5;
      minPage = 1;
    }

    if (index >= 5) {
      minPage = index - 2;
      maxPage = index + 2;
    }

    this.setState({
      recentCommunicationParams: { ...this.state.recentCommunicationParams, currentPage: index },
      minPage: minPage,
      maxPage: maxPage
    },
      () => {
        this.getRecentCommunication();
      }
    );

  }


  buySms = () => {

    if (this.state.isLoading) {
      return false;
    }

    const param = {
      type: this.state.buy_current_entity,
      value: this.state.notify_sms_value,
      amount: this.state.notify_sms_amount,
      entity_name: 'SMS'
    }

    this.setState({ isLoading: true });
    WSManager.Rest(NC.baseURL + MODULE_C.ADD_NOTIFICATION_ENTITY, param).then((responseJson) => {
      this.setState({ isLoading: false });
      if (responseJson.response_code === NC.successCode) {
        this.setState({
          buy_sms_modal: false,
          cd_balance: responseJson.data.cd_balance,
          notify_entity_value: null
        });

        this.props.updateSmsBalance(param.value);

        notify.show(responseJson.message, "success", 5000);
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/login');
      }
    });
  }

  buyNotification = () => {

    if (this.state.isLoading) {
      return false;
    }

    const param = {
      type: this.state.buy_current_entity,
      value: this.state.notify_notification_value,
      amount: this.state.notify_notification_amount,
      entity_name: 'Notification'
    }

    this.setState({ isLoading: true });
    WSManager.Rest(NC.baseURL + MODULE_C.ADD_NOTIFICATION_ENTITY, param).then((responseJson) => {
      this.setState({ isLoading: false });
      if (responseJson.response_code === NC.successCode) {
        this.setState({
          buy_notification_modal: false,
          cd_balance: responseJson.data.cd_balance,
          notify_entity_value: null
        });

        this.props.updateNotificationBalance(param.value);

        notify.show(responseJson.message, "success", 5000);
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/login');
      }
    });
  }

  buyNotifyEntity = () => {

    if (this.state.isLoading) {
      return false;
    }

    const param = {
      type: this.state.buy_current_entity,
      value: this.state.notify_entity_value,
      amount: this.state.notify_email_amount,
      entity_name: 'Email'
    }

    this.setState({ isLoading: true });
    WSManager.Rest(NC.baseURL + MODULE_C.ADD_NOTIFICATION_ENTITY, param).then((responseJson) => {
      this.setState({ isLoading: false });
      if (responseJson.response_code === NC.successCode) {
        this.setState({
          buy_communication_entity_modal: false,
          cd_balance: responseJson.data.cd_balance,
          notify_entity_value: null
        });
        this.props.updateEmailBalance(param.value);
        notify.show(responseJson.message, "success", 5000);
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/login');
      }
    });


  }
  notifyEntityValueChange = (event) => {

    this.setState({
      notify_entity_value: event.target.value,
      notify_email_amount: this.state.notify_email_price * event.target.value
    });
  }

  notifySMSValueChange = (event) => {

    this.setState({
      notify_sms_value: event.target.value,
      notify_sms_amount: this.state.notify_sms_price * event.target.value
    });
  }

  notifyNotificationValueChange = (event) => {

    this.setState({
      notify_notification_value: event.target.value,
      notify_notification_amount: this.state.notify_notification_price * event.target.value
    });
  }

  handleNotificationType = (e) => {

    let value = e.target.value;
    let id = e.target.id;


    var notificationData = _.cloneDeep(this.state.notificationData);
    notificationData[id] = value == 'false' ? true : false;

    this.setState({ notificationData: notificationData }, () => {

      this.get_cd_type_possible_counts(this.state.notificationData);
    });
  }

  handleFixtureNotificationType = (e) => {

    let value = e.target.value;
    let id = e.target.id;


    var fixturePromoteParam = _.cloneDeep(this.state.fixturePromoteParam);
    fixturePromoteParam[MODULE_C.fixtureChannelMap[id]] = value == 'false' ? true : false;
    fixturePromoteParam[id] = value == 'false' ? true : false;

    this.setState({ fixturePromoteParam: fixturePromoteParam }, () => {

      this.get_fixture_cd_type_possible_counts(this.state.fixturePromoteParam);
    });
  }

  sortRecentComminications = (sortField) => {

    var sortOrder = 'DESC';
    if (sortField == this.state.recentCommunicationParams.sort_field) {
      sortOrder = (this.state.recentCommunicationParams.sort_order == 'DESC') ? 'ASC' : 'DESC';
    }

    this.setState({
      recentCommunicationParams: { ...this.state.recentCommunicationParams, sort_order: sortOrder, sort_field: sortField }
    }, () => {
      this.getRecentCommunication();
    });
  }

  getRecentCommunication() {
    let { callFlag } = this.state
    let p_name = ''
    if (callFlag === 1) { p_name = 'SendPosting' }
    else if (callFlag === 2) { p_name = 'SchPosting' }

    this.setState({ [p_name]: true })

    let param = {
      ...this.state.recentCommunicationParams,
      sports_id: this.state.selected_sport
    };

    param.currentPage = this.state.SEND_CURRENT_PAGE
    param.schedule_page = this.state.SCHE_CURRENT_PAGE


    WSManager.Rest(NC.baseURL + MODULE_C.GET_RECENT_COMMUNICATION_LIST, param).then((responseJson) => {

      if (responseJson.response_code === NC.successCode) {
        this.setState({
          recent_communication_list: responseJson.data.recent_communication_list.result,
          TotalSend: responseJson.data.recent_communication_list.total ? responseJson.data.recent_communication_list.total : 0,
          cd_balance: responseJson.data.cd_balance,
          fixtures: (responseJson.data.fixtures && responseJson.data.fixtures.data && responseJson.data.fixtures.data.length > 0) ? responseJson.data.fixtures : [],
          recentCommunicationParams: { ...this.state.recentCommunicationParams, pagesCount: Math.ceil(responseJson.data.recent_communication_list.total / 10) },

          notify_email_price: responseJson.data.CD_ONE_EMAIL_RATE,
          notify_sms_price: responseJson.data.CD_ONE_SMS_RATE,
          notify_notification_price: responseJson.data.CD_ONE_NOTIFICATION_RATE,
          fixture_image_path: responseJson.data.fixture_image_path,
          SentCount: !_isUndefined(responseJson.data.cd_sent_count) ? responseJson.data.cd_sent_count : [],
          [p_name]: false,
          ScheList: responseJson.data.sch_comm_list ? responseJson.data.sch_comm_list.result : [],
          ScheTotal: responseJson.data.sch_comm_list ? responseJson.data.sch_comm_list.total : [],
        });



        this.props.setCdBalance(responseJson.data.cd_balance);

      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/login');
      }
    });
  }

  getUtcToLocal = (date) => {
    return moment.utc(date).local().format();
  }

  getFormatedDate = (date) => {
    return moment(date).format('LLLL');
  }


  toggle_(tab) {
    if (this.state.activeTab !== tab) {
      this.setState({
        activeTab: tab
      });
    }
  }

  toggle() {

    this.setState({
      modal: !this.state.modal,
    });
  }

  toggleRecentCModal = (key, val, eye_flag, call_from) => {
    let tab = '1'
    if (!_.isEmpty(val) && !_.isUndefined(val.template_name)) {
      if (val.template_name == 'custom-sms') {
        tab = '2'
      }
      if (val.template_name == 'custom-notification') {
        tab = '3'
      }

    }

    if (call_from == 'schedule') {
      tab = '3'
    }

    this.setState({
      activeTab: tab,
      communication_review_modal: !this.state.communication_review_modal,
      ModalCallFrom: call_from,
    });

    if (!val) {
      return false;
    }

    let params = {
      recent_communication_id: val.recent_communication_id,
      noti_schedule: eye_flag,
    }
    //get data
    WSManager.Rest(NC.baseURL + MODULE_C.GET_RECENT_COMMUNICATION_DETAIL, params).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        var email_body = responseJson.data.recent_communication_detail.email_body;
        email_body = email_body.replace("{{offer_percentage}}", 10);
        email_body = email_body.replace("{{promo_code}}", "FIRSTDEPOSIT");
        email_body = email_body.replace("{{amount}}", 10);
        email_body = email_body.replace("{{year}}", (new Date()).getFullYear());
        email_body = email_body.replace("{{SITE_TITLE}}", 'Fantasy Sports');
        email_body = email_body.replace("{{contest_name}}", 'Demo');

        this.setState({
          RC_obj_detail: { ...responseJson.data.recent_communication_detail, email_body: email_body }
        });
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/login');
      }
    });
  }

  toggleFixturePromoteModal = (key, val) => {
    let TempDate = moment(WSManager.getUtcToLocal(val.season_scheduled_date)).format("D-MMM-YYYY hh:mm A")
    var params = {};
    params.email_template_id = 4;

    params.season_game_uid = val.season_game_uid;
    params.all_user = 1;
    params.for_str = 'for ' + val.home + ' vs ' + val.away + ' (' + TempDate + ')';
    const stringified = queryString.stringify(params);
    this.props.history.push(`/marketing/new_campaign?${stringified}`);
    return false;
  }

  PromoteFixtureHide = () => {
    this.setState({
      fixture_promote_model: false
    });
  }

  PromoteFixtureShow = () => {
    this.setState({
      fixture_promote_model: true
    });
  }

  get_fixture_cd_type_possible_counts = (notificationData) => {

    var param = MODULE_C.extend(notificationData, MODULE_C.userBaseType[1]);//1 for all user

    param.email_template_id = 4;
    WSManager.Rest(NC.baseURL + MODULE_C.NOTIFY_BY_SELECTION_COUNT, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {

        this.setState({

          fixtureObj: {
            ...this.state.fixtureObj,
            email_count: responseJson.data.email_count,
            notification_count: responseJson.data.notification_count,
            sms_count: responseJson.data.sms_count
          },

        }, () => {

        });
      }

    })

  }


  toggleCResendCModal = (key, val) => {
    var params = {};
    if (val.email_count && val.email_count > 0) {
      params.email = true;
    }

    if (val.sms_count && val.sms_count > 0) {
      params.message = true;
    }

    if (val.notification_count && val.notification_count > 0) {
      params.notification = true;
    }

    if (val.cd_email_template_id) {
      params.email_template_id = val.cd_email_template_id;
    }

    if (MODULE_C.sourceByTemplate[val.notification_type]) {
      params[MODULE_C.sourceByTemplate[val.notification_type]] = val.source_id;
    }
    params = { ...params, ...MODULE_C.userBases[val.userbase] };
    const stringified = queryString.stringify(params);

    this.props.history.push(`/marketing/new_campaign?${stringified}`);
    return false;
  }

  togglePrimary() {
    this.setState({
      primary: !this.state.primary,
    });
  }

  componentDidMount() {
    this.getRecentCommunication();
  }


  getSegmentationTemplate = (val) => {
    var param = {};

    if (val.cd_email_template_id) {
      param.cd_email_template_id = val.cd_email_template_id;
    }

    WSManager.Rest(NC.baseURL + MODULE_C.GET_SEGEMENTATION_TEMPLATE_LIST, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        const templates = [];
        responseJson.data.result.map((data, key) => {
          templates.push({ value: data.cd_email_template_id, label: data.display_label, detail: data })
          return '';
        })
        this.setState({ templateList: templates });
        this.setState({ notificationData: { ...this.state.notificationData, email_template_id: val.cd_email_template_id } });

      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/login');
      } else {
        this.setState({ posting: false });
      }
    })
  }



  notifyBySelection = () => {

    this.setState({ communication_resend_modal: true })

    let notificationData = MODULE_C.extend(this.state.notificationData, MODULE_C.userBaseType[this.state.notificationData.userbase])

    let param = { ...notificationData };
    WSManager.Rest(NC.baseURL + MODULE_C.NOTIFY_BY_SELECTION, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        notify.show(responseJson.message, "success", 5000);
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/login');
      } else {
        this.setState({ posting: false });
      }
    })
  }

  fixtureNotifyBySelection = (seasonGameUid) => {
    let param = { ...this.state.fixturePromoteParam, season_game_uid: seasonGameUid };

    WSManager.Rest(NC.baseURL + MODULE_C.NOTIFY_BY_SELECTION, param).then((responseJson) => {

      this.setState({ posting: false });
      if (responseJson.response_code === NC.successCode) {
        this.setState({ fixture_promote_model: false })
        notify.show(responseJson.message, "success", 5000);
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/login');
      }
    })
  }

  get_cd_type_possible_counts = (notificationData) => {

    var param = MODULE_C.extend(notificationData, MODULE_C.userBaseType[this.state.Resend_obj_detail.userbase]);
    param.from_date = this.state.Resend_obj_detail.from_date;
    param.to_date = this.state.Resend_obj_detail.to_date;

    WSManager.Rest(NC.baseURL + MODULE_C.NOTIFY_BY_SELECTION_COUNT, notificationData).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {

        this.setState({

          Resend_obj_detail: {
            ...this.state.Resend_obj_detail,
            email_count: responseJson.data.email_count,
            notification_count: responseJson.data.notification_count,
            sms_count: responseJson.data.sms_count
          },

        });
      }
    })
  }

  handlePromocodeChange = (selectedOption) => {
    let value = selectedOption.value;
    this.setState({
      notificationData: { ...this.state.notificationData, promo_code_id: value },
    });
  }

  handleChange = (selectedOption) => {

    if (!selectedOption) {
      this.setState({
        notificationData: {}
      });
      return false;
    }

    let value = selectedOption.value;

    //check for deposit template
    if (selectedOption && selectedOption.detail && selectedOption.detail.template_name === 'promotion-for-deposit') {

      this.setState({
        notificationData: { ...this.state.notificationData, email_template_id: value, promo_code_id: '' },
        depositPromocodes: [],
      }, () => {
        this.getDepositPromotionsPromocodes();
      });
    }
    else {
      this.setState({
        depositPromocodes: [],
        notificationData: { ...this.state.notificationData, email_template_id: value, promo_code_id: '' }
      });
    }
  }

  // editSchedule = (val) => {

  //   console.log("val===", val);

  //   var params = {};
  //   params.edit = true;
  //   params.email_template_id = val.category_id;
  //   params.all_user = !val.user_base_list_id ? val.user_base_list_id : 0;

  //   if (val.season_game_uid)
  //     params.season_game_uid = val.season_game_uid;
  //   if (val.contest_id)
  //     params.contest_id = val.contest_id;
  //   if (val.league_id)
  //     params.league_id = val.league_id;

  //   if (val.deal_id) {
  //     params.deal_id = Base64.encode(val.deal_id);
  //     params.amt = Base64.encode(val.amount);
  //     params.for_str = ' For Deal '
  //   }

  //   if (val.promo_code_id) {
  //     params.pc_id = Base64.encode(val.promo_code_id);
  //     params.pct = Base64.encode(this.getPrcodeType(val.type));
  //     params.for_str = ' For Promo code'
  //   }

  //   if (val.fixture_participation) {
  //     params.fixture_participation = val.fixture_participation
  //   }

  //   // if(val.login){
  //   params.login = true;
  //   // }

  //   if (val.signup) {
  //     params.signup = val.signup;
  //   }

  //   if (val.notification) {
  //     params.notification = val.notification;
  //     params.noti_schedule = val.noti_schedule;
  //     params.schedule_date = val.schedule_date
  //   }

  //   const stringified = queryString.stringify(params);
  //   this.props.history.push(`/marketing/new_campaign?${stringified}`);
  //   return false;
  // }

  editSchedule = (obj) => {
    var params = {};
    params.editid = obj.recent_communication_id;
    params.email_template_id = obj.category_id;
    const stringified = queryString.stringify(params);
    this.props.history.push(`/marketing/new_campaign?${stringified}`);
  }

  handlePageChange(current_page, name, posting, listname) {
    if ((name === 'SEND_CURRENT_PAGE' && current_page !== this.state.SEND_CURRENT_PAGE) || (name === 'SCHE_CURRENT_PAGE' && current_page !== this.state.SCHE_CURRENT_PAGE)) {

      let call_val = ''
      if (name === 'SEND_CURRENT_PAGE') {
        call_val = 1
      }
      else if (name === 'SCHE_CURRENT_PAGE') {
        call_val = 2
      }
      this.setState({ callFlag: call_val })
      this.setState({
        [name]: current_page,
        [posting]: true,
        [listname]: [],
      }, () => {
        this.getRecentCommunication();
      });
    }
  }

  getPrcodeType = (val) => {
    let pct = ''
    if (val == 0)
      pct = 'First Deposit'
    else if (val == 1)
      pct = 'Deposit Range'
    else if (val == 2)
      pct = 'Promo Code'
    else if (val == 3)
      pct = 'Contest Join'

    return pct
  }

  render() {
    let {
      recent_communication_list,
      cd_balance,
      notificationData,
      templateList,
      fixtures,
      fixtureObj,
      recentCommunicationParams,
      minPage,
      maxPage,
      SentCount,
      SEND_CURRENT_PAGE,
      TotalSend,
      PERPAGE,
      SCHE_CURRENT_PAGE,
      SendPosting,
      ScheList,
      ScheTotal,
      SchPosting,
    } = this.state;

    return (
      <div className="animated fadeIn campaign-dashboard">
        <div className="new campaign">
          <Row className="commrow" >
            <Col sm={6} >
              <h2 className="h2-cls">Communication Dashboard</h2>
            </Col>

            <Col sm={6}>
              <Button className='btn-secondary-outline btn-new-campaign' onClick={() => { this.props.history.push('new_campaign') }}>New Campaign</Button>
            </Col>
          </Row>
        </div>



        <Row className="remainingsrow">
          <Col md={12}>
            <Card className="remainings mb-0">

              <Col className="remainingscol">
                <div className="col-sm-6">
                  {
                    <h2 className="balanceremaning">
                      {!_isUndefined(SentCount.email_sent) ? SentCount.email_sent : 0}
                    </h2>
                  }
                  <label className="labelremaining" text-align="center"><text className="textcls">Total emails sent</text>
                  </label>
                </div>

                {/* <div className="col-md-4">
                  <h2 className="balanceremaning">
                    {!_isUndefined(SentCount.sms_sent) ? SentCount.sms_sent : 0}
                  </h2>
                  <label className="labelremaining"><text className="textcls" >
                    Total SMS sent
                    </text>
                  </label>
                </div> */}

                <div className="col-sm-6">
                  <h2 className="balanceremaning">
                    {!_isUndefined(SentCount.notification_sent) ? SentCount.notification_sent : 0}
                  </h2>
                  <label className="labelremaining" align-text="center"><text className="textcls">Total notifications sent</text>
                  </label>
                </div>
              </Col>
            </Card>
          </Col>
        </Row>
        {/* Start schedule communication */}
        <Row>
          <Col lg="12">
            <h5 className="recom">Scheduled Communication</h5>
          </Col>
        </Row>
        <Row>
          <Col xs="12" lg="12">
            <Card className="recentcom">
              <CardBody>
                <Table responsive className="communication-table">
                  <thead>
                    <tr>
                      <th scope="col">
                        <div className="dropdownuserdetail" onClick={() => this.sortRecentComminications('user_details')}>
                          <button className="btn tbl-btn dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Userbase
                           </button>
                          <i className={(this.state.recentCommunicationParams.sort_field == 'user_details' && this.state.recentCommunicationParams.sort_order == 'DESC') ? 'fa fa-sort-desc' : ((this.state.recentCommunicationParams.sort_field == 'user_details' && this.state.recentCommunicationParams.sort_order == 'ASC') ? 'fa fa-sort-asc' : '')} ></i>
                        </div>
                      </th>


                      <th scope="col">
                        <div className="dropdown" onClick={() => this.sortRecentComminications('template_name')}>
                          <button className="btn tbl-btn dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Templates
                           </button>
                          <i className={(this.state.recentCommunicationParams.sort_field == 'template_name' && this.state.recentCommunicationParams.sort_order == 'DESC') ? 'fa fa-sort-desc' : ((this.state.recentCommunicationParams.sort_field == 'template_name' && this.state.recentCommunicationParams.sort_order == 'ASC') ? 'fa fa-sort-asc' : '')} ></i>
                          <div className="dropdown-menu" aria-labelledby="dropdownMenu"></div>
                        </div>
                      </th>

                      <th scope="col">
                        <div className="dropdown" onClick={() => this.sortRecentComminications('notification_count')}>
                          <button className="btn tbl-btn dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Notification
                           </button>
                          <i className={(this.state.recentCommunicationParams.sort_field == 'notification_count' && this.state.recentCommunicationParams.sort_order == 'DESC') ? 'fa fa-sort-desc' : ((this.state.recentCommunicationParams.sort_field == 'notification_count' && this.state.recentCommunicationParams.sort_order == 'ASC') ? 'fa fa-sort-asc' : '')} ></i>
                          <div className="dropdown-menu" aria-labelledby="dropdownMenu"></div>
                        </div>
                      </th>
                      <th scope="col">
                        <div className="dropdown" onClick={() => this.sortRecentComminications('added_date')}>
                          <button className="btn tbl-btn dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Scheduled Date And Time
                           </button>
                          <i className={(this.state.recentCommunicationParams.sort_field == 'added_date' && this.state.recentCommunicationParams.sort_order == 'DESC') ? 'fa fa-sort-desc' : ((this.state.recentCommunicationParams.sort_field == 'added_date' && this.state.recentCommunicationParams.sort_order == 'ASC') ? 'fa fa-sort-asc' : '')} ></i>
                          <div className="dropdown-menu" aria-labelledby="dropdownMenu"></div>
                        </div>
                      </th>

                      <th scope="col">
                        <div className="dropdown">
                          <button className="btn tbl-btn dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Action
                           </button>
                          <div className="dropdown-menu" aria-labelledby="dropdownMenu"></div>
                        </div>
                      </th>
                    </tr>
                  </thead>



                  {
                    ScheList && ScheList.length > 0 ?
                      _.map(ScheList, (val, key) => {
                        var dt = new Date(val.schedule_date);
                        dt.setMinutes(dt.getMinutes() - 10);
                        let btn_show_time = HF.getTimeDiff(dt);                      
                          
                        return (
                          <tbody>
                            <tr key={key}>
                              <td>{val.sch_user_detail}
                              </td>
                              <td>{val.template_name}</td>
                              <td>{val.notification_count}</td>
                              

                              <td>
                                {/* <MomentDateComponent data={{ date: val.schedule_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                                {HF.getFormatedDateTime(val.schedule_date, "D-MMM-YYYY hh:mm A")}
                              </td>
                              <td Style="display:flex;">
                                <span onClick={() => this.toggleRecentCModal(key, val, 2, 'schedule')} className="shape magnifying-glass" >
                                  <img src={Images.EYE} />
                                </span>
                                {
                                  // (val.is_processed == '0' || !btn_show_time) &&
                                  // <span onClick={() => (val.is_processed == '0' || !btn_show_time) ? this.editSchedule(val) : null} 
                                  // className={`shape ${(val.is_processed == '0' || !btn_show_time) ? '' : 'disable d-bdr'}`} >
                                  <span onClick={() => (!btn_show_time) ? this.editSchedule(val) : null} 
                                  className={`shape ${(!btn_show_time) ? '' : 'disable d-bdr'}`} >
                                    <i className="icon-edit font-xl"></i>
                                  </span>
                                }
                              </td>
                            </tr>
                          </tbody>
                        )
                      })
                      :
                      <tbody>
                        <tr>
                          <td colSpan='7'>
                            {(ScheList.length == 0 && !SchPosting) ?
                              <div className="no-records">No Record Found.</div>
                              :
                              <Loader />
                            }
                          </td>
                        </tr>
                      </tbody>
                  }


                </Table>
                <Row className="viewrow">
                  <Col lg={12}>
                    {ScheTotal > PERPAGE && (
                      <div className="custom-pagination lobby-paging">
                        <Pagination
                          activePage={SCHE_CURRENT_PAGE}
                          itemsCountPerPage={PERPAGE}
                          totalItemsCount={ScheTotal}
                          pageRangeDisplayed={5}
                          onChange={e => this.handlePageChange(e, 'SCHE_CURRENT_PAGE', 'SchPosting', 'ScheList')}
                        />
                      </div>
                    )
                    }
                    {/* {
                      this.state.length == 3 ?
                        <text className="viewallcd" onClick={() => this.setState({ length: recent_communication_list.length })}>View All</text>
                        :
                        <text className="viewallcd" onClick={() => this.setState({ length: 3 })}>View Less</text>
                    }
                    {
                      this.state.length > 3 &&
                      <div className="pull-right">
                        <Pagination aria-label="Page navigation example" className="custom-pagination">
                          <PaginationItem disabled={recentCommunicationParams.currentPage <= 0}>

                            <PaginationLink
                              onClick={e => this.handleRecentCommunicationPagination(e, 0)}
                              first
                              href="#"
                            />
                          </PaginationItem>

                          <PaginationItem disabled={recentCommunicationParams.currentPage <= 0}>
                            <PaginationLink
                              onClick={e => this.handleRecentCommunicationPagination(e, recentCommunicationParams.currentPage - 1)}
                              previous
                              href="#"
                            />
                          </PaginationItem>
                          {[...Array(recentCommunicationParams.pagesCount)].map((page, i) =>
                            ((i + 1) >= minPage && (i + 1) <= maxPage) &&
                            <PaginationItem active={i === recentCommunicationParams.currentPage} key={i}>
                              <PaginationLink onClick={e => this.handleRecentCommunicationPagination(e, i)} href="#">
                                {i + 1}
                              </PaginationLink>
                            </PaginationItem>
                          )}
                          <PaginationItem disabled={recentCommunicationParams.currentPage >= recentCommunicationParams.pagesCount - 1}>
                            <PaginationLink
                              onClick={e => this.handleRecentCommunicationPagination(e, recentCommunicationParams.currentPage + 1)}
                              next
                              href="#"
                            />
                          </PaginationItem>
                          <PaginationItem disabled={recentCommunicationParams.currentPage >= recentCommunicationParams.pagesCount}>
                            <PaginationLink
                              onClick={e => this.handleRecentCommunicationPagination(e, recentCommunicationParams.pagesCount - 1)}
                              last
                              href="#"
                            />
                          </PaginationItem>
                        </Pagination>
                      </div>
                    } */}
                  </Col>
                </Row>
              </CardBody>
            </Card>
          </Col>
        </Row>
        {/* End schedule communication */}
        <Row>
          <Col lg="12">
            <h5 className="recom">Recent Communication</h5>
          </Col>
        </Row>
        <Row>
          <Col xs="12" lg="12">
            <Card className="recentcom">
              <CardBody>
                <Table responsive className="communication-table">
                  <thead>
                    <tr>
                      <th scope="col">
                        <div className="dropdownuserdetail" onClick={() => this.sortRecentComminications('user_details')}>
                          <button className="btn tbl-btn dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Userbase
                           </button>
                          <i className={(this.state.recentCommunicationParams.sort_field == 'user_details' && this.state.recentCommunicationParams.sort_order == 'DESC') ? 'fa fa-sort-desc' : ((this.state.recentCommunicationParams.sort_field == 'user_details' && this.state.recentCommunicationParams.sort_order == 'ASC') ? 'fa fa-sort-asc' : '')} ></i>
                        </div>
                      </th>


                      <th scope="col">
                        <div className="dropdown" onClick={() => this.sortRecentComminications('template_name')}>
                          <button className="btn tbl-btn dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Templates
                           </button>
                          <i className={(this.state.recentCommunicationParams.sort_field == 'template_name' && this.state.recentCommunicationParams.sort_order == 'DESC') ? 'fa fa-sort-desc' : ((this.state.recentCommunicationParams.sort_field == 'template_name' && this.state.recentCommunicationParams.sort_order == 'ASC') ? 'fa fa-sort-asc' : '')} ></i>
                          <div className="dropdown-menu" aria-labelledby="dropdownMenu"></div>
                        </div>
                      </th>

                      <th scope="col">
                        <div className="dropdown" onClick={() => this.sortRecentComminications('email_count')}>
                          <button className="btn tbl-btn dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Emails
                           </button>
                          <i className={(this.state.recentCommunicationParams.sort_field == 'email_count' && this.state.recentCommunicationParams.sort_order == 'DESC') ? 'fa fa-sort-desc' : ((this.state.recentCommunicationParams.sort_field == 'email_count' && this.state.recentCommunicationParams.sort_order == 'ASC') ? 'fa fa-sort-asc' : '')} ></i>
                          <div className="dropdown-menu" aria-labelledby="dropdownMenu"></div>
                        </div>
                      </th>

                      {/* <th scope="col">
                        <div className="dropdown" onClick={() => this.sortRecentComminications('sms_count')}>
                          <button className="btn tbl-btn dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            SMS
                           </button>
                          <i className={(this.state.recentCommunicationParams.sort_field == 'sms_count' && this.state.recentCommunicationParams.sort_order == 'DESC') ? 'fa fa-sort-desc' : ((this.state.recentCommunicationParams.sort_field == 'sms_count' && this.state.recentCommunicationParams.sort_order == 'ASC') ? 'fa fa-sort-asc' : '')} ></i>
                          <div className="dropdown-menu" aria-labelledby="dropdownMenu"></div>
                        </div>
                      </th> */}

                      <th scope="col">
                        <div className="dropdown" onClick={() => this.sortRecentComminications('notification_count')}>
                          <button className="btn tbl-btn dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Notification
                           </button>
                          <i className={(this.state.recentCommunicationParams.sort_field == 'notification_count' && this.state.recentCommunicationParams.sort_order == 'DESC') ? 'fa fa-sort-desc' : ((this.state.recentCommunicationParams.sort_field == 'notification_count' && this.state.recentCommunicationParams.sort_order == 'ASC') ? 'fa fa-sort-asc' : '')} ></i>
                          <div className="dropdown-menu" aria-labelledby="dropdownMenu"></div>
                        </div>
                      </th>
                      <th scope="col">
                        <div className="dropdown">
                          Delivered
                          <div className="dropdown-menu" aria-labelledby="dropdownMenu"></div>
                        </div>
                      </th>
                      <th scope="col">
                        <div className="dropdown">
                          Viewed
                          <div className="dropdown-menu" aria-labelledby="dropdownMenu"></div>
                        </div>
                      </th>
                      <th scope="col">
                        <div className="dropdown" onClick={() => this.sortRecentComminications('added_date')}>
                          <button className="btn tbl-btn dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            DateTime
                           </button>
                          <i className={(this.state.recentCommunicationParams.sort_field == 'added_date' && this.state.recentCommunicationParams.sort_order == 'DESC') ? 'fa fa-sort-desc' : ((this.state.recentCommunicationParams.sort_field == 'added_date' && this.state.recentCommunicationParams.sort_order == 'ASC') ? 'fa fa-sort-asc' : '')} ></i>
                          <div className="dropdown-menu" aria-labelledby="dropdownMenu"></div>
                        </div>
                      </th>

                      <th scope="col">
                        <div className="dropdown">
                          <button className="btn tbl-btn dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Action
                           </button>
                          <div className="dropdown-menu" aria-labelledby="dropdownMenu"></div>
                        </div>
                      </th>
                    </tr>
                  </thead>



                  {
                    recent_communication_list && recent_communication_list.length > 0 ?
                      _.map(recent_communication_list, (val, key) => {
                        if (key < this.state.length) {
                          return (
                            <tbody>
                              <tr key={key}>
                                <td>{val.user_details}
                                </td>
                                <td>{val.template_name}</td>
                                <td className="sent-count">{val.email_count}</td>
                                {/* <td className="sent-count">{val.sms_count}</td> */}
                                <td>{val.notification_count}</td>
                                <td>{val.notification_delivered_count}</td>
                                <td>{val.notification_viewed_count}</td>
                                <td>
                                  {/* <MomentDateComponent data={{ date: val.added_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                                  {HF.getFormatedDateTime(val.added_date, "D-MMM-YYYY hh:mm A")}
                                </td>
                                <td Style="display:flex;">
                                  <span onClick={() => this.toggleRecentCModal(key, val, 1, 'recent')} className="shape magnifying-glass" >
                                    <img src={Images.EYE} />
                                  </span>
                                  {/* <span onClick={() => this.editSchedule(val)} className="shape" >
                                  <i className="icon-edit font-xl"></i>
                                </span> */}
                                </td>
                              </tr>
                            </tbody>
                          )
                        }

                      })
                      :
                      <tbody>
                        <tr>
                          <td colSpan='22'>
                            {(recent_communication_list && recent_communication_list.length == 0 && !SendPosting) ?
                              <div className="no-records">No Record Found.</div>
                              :
                              <Loader />
                            }
                          </td>
                        </tr>
                      </tbody>
                  }



                </Table>
                <Row className="viewrow">
                  <Col lg={12}>
                    {TotalSend > PERPAGE && (
                      <div className="custom-pagination lobby-paging">
                        <Pagination
                          activePage={SEND_CURRENT_PAGE}
                          itemsCountPerPage={PERPAGE}
                          totalItemsCount={TotalSend}
                          pageRangeDisplayed={5}
                          onChange={e => this.handlePageChange(e, 'SEND_CURRENT_PAGE', 'SendPosting', 'recent_communication_list')}
                        />
                      </div>
                    )
                    }
                    {/* {
                      this.state.length == 3 ?
                        <text className="viewallcd" onClick={() => this.setState({ length: recent_communication_list.length })}>View All</text>
                        :
                        <text className="viewallcd" onClick={() => this.setState({ length: 3 })}>View Less</text>
                    }
                    {
                      this.state.length > 3 &&
                      <div className="pull-right">
                        <Pagination aria-label="Page navigation example" className="custom-pagination">
                          <PaginationItem disabled={recentCommunicationParams.currentPage <= 0}>

                            <PaginationLink
                              onClick={e => this.handleRecentCommunicationPagination(e, 0)}
                              first
                              href="#"
                            />
                          </PaginationItem>

                          <PaginationItem disabled={recentCommunicationParams.currentPage <= 0}>
                            <PaginationLink
                              onClick={e => this.handleRecentCommunicationPagination(e, recentCommunicationParams.currentPage - 1)}
                              previous
                              href="#"
                            />
                          </PaginationItem>
                          {[...Array(recentCommunicationParams.pagesCount)].map((page, i) =>
                            ((i + 1) >= minPage && (i + 1) <= maxPage) &&
                            <PaginationItem active={i === recentCommunicationParams.currentPage} key={i}>
                              <PaginationLink onClick={e => this.handleRecentCommunicationPagination(e, i)} href="#">
                                {i + 1}
                              </PaginationLink>
                            </PaginationItem>
                          )}
                          <PaginationItem disabled={recentCommunicationParams.currentPage >= recentCommunicationParams.pagesCount - 1}>
                            <PaginationLink
                              onClick={e => this.handleRecentCommunicationPagination(e, recentCommunicationParams.currentPage + 1)}
                              next
                              href="#"
                            />
                          </PaginationItem>
                          <PaginationItem disabled={recentCommunicationParams.currentPage >= recentCommunicationParams.pagesCount}>
                            <PaginationLink
                              onClick={e => this.handleRecentCommunicationPagination(e, recentCommunicationParams.pagesCount - 1)}
                              last
                              href="#"
                            />
                          </PaginationItem>
                        </Pagination>
                      </div>
                    } */}
                  </Col>
                </Row>
              </CardBody>
            </Card>
          </Col>
        </Row>

        {/* ///Email Modal\\\ */}
        <Modal
          isOpen={this.state.communication_review_modal}
          toggle={this.toggleRecentCModal}
          className="promtn-modal"
        >
          <ModalHeader toggle={this.toggleRecentCModal} className="promotion">
            Promotion
            </ModalHeader>
          <ModalBody>

            <Row >
              <Col lg={6} className="allusercol">
                <h3 className="pr-ub-name">
                  {this.state.RC_obj_detail.list_name}
                </h3>
                <div className="pr-ub-date">
                  {/* <MomentDateComponent data={{ date: this.state.ModalCallFrom == 'recent' ? this.state.RC_obj_detail.added_date : this.state.RC_obj_detail.schedule_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                  {(this.state.ModalCallFrom == 'recent' ?
                    HF.getFormatedDateTime(this.state.RC_obj_detail.added_date, "D-MMM-YYYY hh:mm A") :
                    HF.getFormatedDateTime(this.state.RC_obj_detail.schedule_date, "D-MMM-YYYY hh:mm A"))
                  }

                </div>
              </Col>

              <Col lg={6} className="allusercol">
                {
                  (this.state.activeTab === '1' && this.state.ModalCallFrom == 'recent') &&
                  <div className="tabpreview">
                    <div className="pr-sent-title"> Email sent</div>
                    <div className="pr-ub-name float-right">{this.state.RC_obj_detail.email_count}</div>
                  </div>
                }
                {
                  // (this.state.activeTab === '2' && this.state.ModalCallFrom == 'recent') &&
                  // <div className="tabpreview">
                  //   <div className="pr-sent-title"> SMS sent</div>
                  //   <div className="pr-ub-name float-right">{this.state.RC_obj_detail.sms_count}</div>
                  // </div>
                }
                {
                  (this.state.activeTab === '3') &&
                  <div className="tabpreview">
                    <div className="pr-sent-title"> Notification {this.state.ModalCallFrom == 'recent' ? ' sent' : ' to be'}</div>
                    <div className="pr-ub-name float-right">{this.state.RC_obj_detail.notification_count}</div>
                  </div>
                }
              </Col>
            </Row>

            <div className="popuppreviewtab">
              <Nav tabs>
                {
                  this.state.ModalCallFrom == 'recent' &&
                  <Fragment>
                    <NavItem>
                      <NavLink
                        className={classnames({ active: this.state.activeTab === '1' })}
                        onClick={() => { this.toggle_('1'); }}
                      >
                        Email
                  </NavLink>
                    </NavItem>

                    {/* <NavItem>
                      <NavLink
                        className={classnames({ active: this.state.activeTab === '2' })}
                        onClick={() => { this.toggle_('2'); }}
                      >
                        SMS
                    </NavLink>
                    </NavItem> */}
                  </Fragment>
                }
                <NavItem>
                  <NavLink
                    className={classnames({ active: this.state.activeTab === '3' })}
                    onClick={() => { this.toggle_('3'); }}
                  >
                    Notification
                  </NavLink>
                </NavItem>

              </Nav>
              <TabContent activeTab={this.state.activeTab}>
                <TabPane tabId="1">
                  <Row>
                    <Col sm="12" className="temptab p-0">
                      <div className="subjecttemp mb-2">
                        <text className="subject">Subject -
                          <text className="subject1">   {this.state.RC_obj_detail.subject}
                          </text>
                        </text>
                      </div>
                      <div className="emailbody" dangerouslySetInnerHTML={{ __html: this.state.RC_obj_detail.email_body }}>
                      </div>
                    </Col>
                  </Row>
                </TabPane>

                <TabPane tabId="2">
                  <Row>
                    <Col sm="12" className="temptab p-0">
                      <div className="pr-body-text">
                        {this.state.RC_obj_detail.message_body}
                      </div>
                    </Col>
                  </Row>
                </TabPane>

                <TabPane tabId="3">
                  <Row>
                    <Col sm="12" className="temptab p-0">
                      <div className="pr-body-text">
                        {this.state.RC_obj_detail.notification_message}
                      </div>
                    </Col>
                  </Row>
                </TabPane>
              </TabContent>
            </div>
            <div className="templatepreview">
            </div>
          </ModalBody>
        </Modal>



        {/* ///Resend Modal\\\ */}
        <Modal isOpen={this.state.communication_resend_modal} toggle={this.toggleCResendCModal} className={this.props.className}>
          <ModalHeader toggle={this.toggleCResendCModal} className="resend">
            <Col lg={12}>
              <h5 className="resend title"> RESEND</h5></Col>
          </ModalHeader>

          <ModalBody>
            {
              <Row className="usertime">
                <Col lg={12}>
                  <div>Campaign Type</div>
                  <div>{this.state.Resend_obj_detail.user_details} </div>
                  <span className="allusers">
                    {
                      this.state.Resend_obj_detail.from_date && this.state.Resend_obj_detail.to_date &&
                      <span>
                        {/* (<MomentDateComponent data={{ date: this.state.Resend_obj_detail.from_date, format: "D-MMM-YYYY hh:mm A" }} /> -
                        <MomentDateComponent data={{ date: this.state.Resend_obj_detail.to_date, format: "D-MMM-YYYY hh:mm A" }} />) */}

                        (
                          {HF.getFormatedDateTime(this.state.Resend_obj_detail.from_date, "D-MMM-YYYY hh:mm A")} - {HF.getFormatedDateTime(this.state.Resend_obj_detail.to_date, "D-MMM-YYYY hh:mm A")}
                        )
                        </span>
                    }
                    {
                      !this.state.Resend_obj_detail.from_date && !this.state.Resend_obj_detail.to_date &&
                      <span>
                        {/* (<MomentDateComponent data={{ date: this.state.Resend_obj_detail.added_date, format: "DD/MM/YYYY" }} />) */}
                       {HF.getFormatedDateTime(this.state.Resend_obj_detail.added_date, "DD/MM/YYYY")}

                      </span>
                    }

                  </span>
                </Col>
              </Row>
            }
            <Row className="communication">
              <Col lg={12}>
                <div className="comchannel">Communication Channel</div>

                <div className="customcontrol">

                  <div className="custom-control custom-checkbox custom-control-inline">
                    <Input type="checkbox" id="email" className="custom-control-input" onChange={this.handleNotificationType}
                      checked={notificationData.email}
                      value={notificationData.email}></Input>
                    <label className="custom-control-label" for="email">E-MAIL</label>
                  </div>

                  <div className="custom-control custom-checkbox custom-control-inline">
                    <Input type="checkbox" id="message" className="custom-control-input" onChange={this.handleNotificationType}
                      checked={notificationData.message}
                      value={notificationData.message}></Input>
                    <label className="custom-control-label" for="message">SMS</label>
                  </div>

                  <div className="custom-control custom-checkbox custom-control-inline">
                    <Input type="checkbox" id="notification" className="custom-control-input" onChange={this.handleNotificationType}
                      checked={notificationData.notification}
                      value={notificationData.notification}></Input>
                    <label className="custom-control-label" for="notification">Notification</label>
                  </div>
                </div>
              </Col>
            </Row>
            <Col>
              <Row className="select preview">
                <Col md={5} className="tempselect">
                  <div className="templates1">Templates</div>
                  <Select class="form-control"
                    value={notificationData.email_template_id}
                    onChange={this.handleChange}
                    options={templateList}>
                  </Select>
                  {
                    this.state.depositPromocodes.length > 0 &&
                    <Select class="form-control"
                      value={notificationData.promo_code_id}
                      onChange={this.handlePromocodeChange}
                      options={this.state.depositPromocodes}>
                      <div className="Select-placeholder">Select Promocode</div>
                    </Select>
                  }
                </Col>
              </Row>
              <Row className="popcardrow">
                <Col md={4}>
                  <Card className="popcard">
                    <h6 className="popcard6">E-MAIL</h6>
                    <h2 className="popcard8"> {this.state.Resend_obj_detail.email_count}</h2>
                    {/* <h6 className="popcard7">Credit remaining:{cd_balance.email_balance}</h6> */}
                  </Card>
                </Col>
                <Col md={4}>
                  <Card className="popcard">
                    <h6 className="popcard6">SMS</h6>
                    <h2 className="popcard8">{this.state.Resend_obj_detail.sms_count}</h2>
                    {/* <h6 className="popcard7">Credit remaining:{cd_balance.sms_balance}</h6> */}
                  </Card>
                </Col>
                <Col md={4}>
                  <Card className="popcard">
                    <h6 className="popcard6">Notification</h6>
                    <h2 className="popcard8">{this.state.Resend_obj_detail.notification_count}</h2>
                    {/* <h6 className="popcard7">Credit remaining:{cd_balance.notification_balance}</h6> */}
                  </Card>
                </Col>
              </Row>
            </Col>
            <Row>
              <Col lg={12} className="resend">
                <Button className="resendbtnpop" outline disabled={!notificationData.email_template_id} color="danger" onClick={() => this.notifyBySelection()}>Resend</Button>
              </Col>
            </Row>
          </ModalBody>
        </Modal>
        {/* /////Resend Modal (Promote btnmodal)\\\\ */}
        {
          this.state.fixture_promote_model &&
          <PromoteFixtureModal IsPromoteFixtureShow={this.state.fixture_promote_model} IsPromoteFixtureHide={this.PromoteFixtureHide} FixtureData={{ fixtureObj: fixtureObj }} />}
      </div>
    );
  }
}

const mapDispatchToProps = dispatch => {
  return {
    setCdBalance: (cd_balance) => dispatch({ type: actionTypes.SET_CD_BALANCE, cd_balance: cd_balance }),
    updateEmailBalance: (val) => dispatch({ type: actionTypes.UPDATE_EMAIL_BALANCE, value: val }),
    updateSmsBalance: (val) => dispatch({ type: actionTypes.UPDATE_SMS_BALANCE, value: val }),
    updateNotificationBalance: (val) => dispatch({ type: actionTypes.UPDATE_NOTIFICATION_BALANCE, value: val })
  }
}

const mapStateToProps = state => {
  return state;
}
export default connect(mapStateToProps, mapDispatchToProps)(CampaignDashboard);

