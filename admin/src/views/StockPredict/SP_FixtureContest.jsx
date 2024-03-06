import React, { Component, Fragment } from 'react';
import { Card, Col, Row, Modal, ModalBody, ModalHeader, ModalFooter, Input, Button, Tooltip } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { Progress } from 'reactstrap';
import { notify } from 'react-notify-toast';
import queryString from 'query-string';
import Images from '../../components/images';
import PromoteContestModal from '../../Modals/PromoteContest';
import PromoteNotActive from '../../Modals/PromoteNotActive';
import moment from 'moment';
import HF, { _isUndefined } from '../../helper/HelperFunction';
import { MSG_CANCEL_REQ, CANCEL_GAME_TITLE, CANCEL_CONTEST_TITLE } from "../../helper/Message";
import { changeScrWinStatus, SP_GET_COLLECTION_DETAILS, SP_GET_FIXTURE_CONTEST } from "../../helper/WSCalling";
import PromptModal from '../../components/Modals/PromptModal';
import SP_FixtureCard from './SP_FixtureCard';
class SP_FixtureContest extends Component {

  constructor(props) {
    super(props);

    this.state = {
      collection_id: (this.props.match.params.collection_id) ? this.props.match.params.collection_id : '1',
      ActiveFxType: (this.props.match.params.category) ? this.props.match.params.category : '1',
      ActiveTab: (this.props.match.params.activeTab) ? this.props.match.params.activeTab : '1',
      FixtureValue: (this.props.match.params.fxvalue) ? this.props.match.params.fxvalue : '1',
      fixtureDetail: {},
      contestList: [],
      keyword: '',
      posting: false,
      prize_modal: false,
      contestObj: {},
      contest_promote_model: false,
      contestPromoteParam: {
        email_contest_model: false,
        message_contest_model: false,
        notification_contest_model: false
      },
      promote_model: false,
      CancelModalIsOpen: false,
      CancelPosting: true,
      CANCEL_COLLE_ID: 0,
      CONTEST_U_ID: 0,
      SHOW_CANCEL: 0,
      BackTab: (this.props.match.params.tab) ? this.props.match.params.tab : 1,
      ALLOW_FREE2PLAY: 0,

      isShowAutoToolTip: false,
      MaxMatchSystemUser: 0,

      AllowSystemUser: (!_.isUndefined(HF.getMasterData().pl_allow) && HF.getMasterData().pl_allow == '1') ? true : false,
      ScrWinModalOpen: false,
      ScrWinPosting: false,
    };
  }

  PromoteHide = () => {
    this.setState({
      promote_model: false
    });
  }

  componentDidMount() {
    if (HF.allowStockPredict() == '0') {
      notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
      this.props.history.push('/dashboard')
    }
    this.GetFixtureDetail();

    // this.GetFixtureContest();
  }

  /*****contest promote model functions START */

  toggleContestPromoteModal = (key, val) => {
    if (!NC.ALLOW_COMMUNICATION_DASHBOARD) {
      this.setState({
        promote_model: true
      });
      return false;
    }
    var params = {};

    let TempDate = moment(WSManager.getUtcToLocal(val.season_scheduled_date)).format("D-MMM-YYYY hh:mm A")

    params.email_template_id = 2;

    params.contest_id = val.contest_id;
    params.all_user = 1;
    params.for_str = ' for Contest ' + val.contest_name + '(' + this.state.fixtureDetail.home + ' vs ' + this.state.fixtureDetail.away + ' ' + TempDate + ')';
    const stringified = queryString.stringify(params);
    this.props.history.push(`/marketing/new_campaign?${stringified}`);
    return false;
  }

  PromoteContestHide = () => {
    this.setState({
      contest_promote_model: false
    });
  }

  /*****contest promote model functions  END*/

  GetFixtureDetail = () => {
    this.setState({ posting: true });
    let param = {
      "collection_id": this.state.collection_id,
    }

    SP_GET_COLLECTION_DETAILS(param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        let fixtureDetail = responseJson.data;
        var dt = new Date(fixtureDetail.season_scheduled_date);

        var mEndDate = new Date(WSManager.getUtcToLocal(fixtureDetail.scheduled_date));
        var curDate = new Date();

        let compDate = false;
        if (curDate >= mEndDate) {
          compDate = true;
        }

        this.setState({
          posting: false,
          fixtureDetail: fixtureDetail,
          BtnCreateConTest: compDate,

        }, function () {
          this.GetFixtureContest();
        });
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        WSManager.logout();
        this.props.history.push('/login');
      }
    })
  }

  GetFixtureContest = () => {
    this.setState({ posting: true })
    let { collection_id } = this.state
    let params = { "collection_id": collection_id };
    SP_GET_FIXTURE_CONTEST(params)
      .then((responseJson) => {
        if (responseJson.response_code === NC.successCode) {
          let responseJsonData = responseJson.data;
          this.setState({
            contestList: responseJsonData,
            MaxMatchSystemUser: responseJson.max_match_system_user,
            CANCEL_COLLE_ID: responseJson.collection_id,
            SHOW_CANCEL: responseJson.show_cancel,
            ALLOW_FREE2PLAY: responseJson.allow_freetoplay
          })
        }
        this.setState({ posting: false })
      })
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

  ShowProgressBar = (join, total) => {
    return join * 100 / total;
  }

  redirectToCreateContest = () => {
    let { ActiveFxType, ActiveTab, collection_id, fixtureDetail } = this.state
    this.props.history.push({ pathname: '/stockpredict/createcontest/' + ActiveTab + '/' + collection_id })
  }

  markPinContest = (e, contest, group_index, contest_index, pin_val) => {
    e.stopPropagation();
    let m = pin_val == 1 ? 'mark' : 'remove'
    if (window.confirm("Are you sure want to " + m + " pin ?")) {
      this.setState({ posting: true })
      let params = {
        "contest_id": contest.contest_id,
        "collection_master_id": contest.collection_master_id,
        "is_pin_contest": pin_val,
      };
      WSManager.Rest(NC.baseURL + NC.SP_MARK_PIN_CONTEST, params).then((responseJson) => {
        if (responseJson.response_code === NC.successCode) {
          let contestList = _.cloneDeep(this.state.contestList);
          contestList[group_index]['contest_list'][contest_index]['is_pin_contest'] = pin_val;
          this.setState({ 'contestList': contestList });

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

  deleteContest = (e, contestObj, index) => {
    e.stopPropagation();
    if (window.confirm("Are you sure want to delete this contest ?")) {
      this.setState({ posting: true })
      let params = { "contest_id": contestObj.contest_id, "collection_master_id": contestObj.collection_master_id };
      WSManager.Rest(NC.baseURL + NC.SF_DELETE_CONTEST, params).then((responseJson) => {
        if (responseJson.response_code === NC.successCode) {
          this.GetFixtureContest();
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

  cancelMatchModalToggle = (contest_u_id, flag, group_index, idx) => {
    if (flag == 2) {
      this.setState({
        CONTEST_U_ID: contest_u_id
      });
    }
    this.setState({
      API_FLAG: flag,
      CancelModalIsOpen: !this.state.CancelModalIsOpen,
      GroupIndex: group_index,
      DeleteIndex: idx,
    });
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
    let { API_FLAG, CANCEL_COLLE_ID, CONTEST_U_ID, CancelReason, GroupIndex, DeleteIndex, contestList, ActiveFxType, ActiveTab } = this.state
    this.setState({ CancelPosting: false });

    let param = {
      cancel_reason: CancelReason
    };

    let API_URL = ""
    if (API_FLAG == 1) {
      param.collection_id = CANCEL_COLLE_ID
      API_URL = NC.SF_CANCEL_COLLECTION
    } else {
      param.contest_unique_id = CONTEST_U_ID
      API_URL = NC.SF_CANCEL_CONTEST
    }

    WSManager.Rest(NC.baseURL + API_URL, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        this.GetFixtureContest();
        this.setState({
          CancelPosting: false,
          CancelReason: ''
        })

        if (GroupIndex >= "0" && DeleteIndex >= "0") {
          contestList[GroupIndex].contest_list[DeleteIndex].status = "1"
        } else {
          // this.props.history.push({ pathname: '/stockpredict/fixture' })
          this.props.history.push('/stockpredict/fixture?pctab=' + ActiveFxType + '&tab=' + ActiveTab)
        }
        notify.show(responseJson.message, "success", 5000);
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        notify.show(responseJson.message, "error", 5000);
      }
      this.cancelMatchModalToggle('', '')
    })
  }

  cancelMatchModal = () => {
    let { CancelPosting, API_FLAG } = this.state
    return (
      <div>
        <Modal
          isOpen={this.state.CancelModalIsOpen}
          toggle={this.cancelMatchModalToggle}
          className="SpCancelMatchModal"
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

  SWinToggle = (g_idx, idx) => {
    if (!_isUndefined(g_idx) && !_isUndefined(idx)) {
      let contestList = this.state.contestList
      let flag = contestList[g_idx]['contest_list'][idx]['swin_tt']
      contestList[g_idx]['contest_list'][idx]['swin_tt'] = !flag
      this.setState({ contestList });
    }
  }

  addRemoveScWinModal = (c_id, g_idx, c_idx, flag) => {
    let msg_status = (flag == '1') ? 'in' : '';
    let msg = 'Are you sure you want to ' + msg_status + 'active scratch and win for this contest ?'
    this.setState({
      ScrWinModalOpen: !this.state.ScrWinModalOpen,
      ContestId: c_id,
      ScWinMsg: msg,
      GrpIdx: g_idx,
      ConIdx: c_idx,
      ScFlag: flag,
    })
  }

  addRemoveScWin = () => {
    this.setState({ ScrWinPosting: true })
    let { contestList, ContestId, GrpIdx, ConIdx, ScFlag } = this.state
    let new_val = ScFlag == '1' ? '0' : '1';
    let params = {
      "contest_id": ContestId,
      "status": new_val,
    };

    changeScrWinStatus(params).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        let temp_c_list = contestList;
        temp_c_list[GrpIdx]['contest_list'][ConIdx]['is_scratchwin'] = new_val;
        this.setState({ contestList: temp_c_list, ScrWinModalOpen: false });
        notify.show(responseJson.message, "success", 5000);
      } else {
        notify.show(responseJson.message, "error", 3000);
      }
      this.setState({ ScrWinPosting: false })
    })
  }

  render() {
    let {
      fixtureDetail,
      contestList,
      contestObj,
      CANCEL_COLLE_ID,
      SHOW_CANCEL,
      selected_sport,
      AllowSystemUser,
      ScrWinModalOpen,
      ScrWinPosting,
      ScWinMsg,
      ActiveFxType,
      ActiveTab,
      BtnCreateConTest,
    } = this.state

    let ScrWinModalProps = {
      publishModalOpen: ScrWinModalOpen,
      publishPosting: ScrWinPosting,
      modalActionNo: this.addRemoveScWinModal,
      modalActionYes: this.addRemoveScWin,
      MainMessage: ScWinMsg,
      SubMessage: '',
    }
    let fx_item = {
      scheduled_date: fixtureDetail.scheduled_date,
      end_date: fixtureDetail.end_date,
    }
    return (
      <div className="animated fadeIn contest-group-wrapper SpFixtureContest">
        {ScrWinModalOpen && <PromptModal {...ScrWinModalProps} />}
        {this.cancelMatchModal()}
        {
          !_.isEmpty(fixtureDetail) &&
          <Row>
            <Col lg={12}>
              <div className="sf-wdt pull-left">
                <SP_FixtureCard
                  callfrom={'2'}
                  activeFxTab={ActiveFxType}
                  activeTab={ActiveTab}
                  edit={false}
                  item={fx_item}
                  redirectToTemplate={null}
                  redirectToStockReview={null}
                  redirectToUpdateStock={null}
                  openMsgModal={null}
                  openDelayModal={null}
                  show_flag={true}
                />
              </div>
              {
                !BtnCreateConTest &&
                <Button className='pull-right btn-secondary-outline' onClick={() => this.redirectToCreateContest()}>Create New Contest</Button>
              }
              {
                (CANCEL_COLLE_ID > "0" && SHOW_CANCEL > "0") &&
                <Button
                  className='cancel-match-btn btn-secondary'
                  onClick={() => this.cancelMatchModalToggle('', 1, '-1', '-1')}
                >Cancel Contest/Candle</Button>
              }
            </Col>
          </Row>
        }
        <Row className="xanimate-right">
          <Col sm={3}>
            <div className="fxcon-total-box">
              <div className="fxcon-title">Total Joined Users</div>
              <div className="fxcon-count">
                {
                  (!_.isEmpty(fixtureDetail) && !_.isUndefined(fixtureDetail.total_users))
                    ?
                    fixtureDetail.total_users
                    :
                    0
                }
              </div>
            </div>
          </Col>
          <Col sm={3}>
            <div className="fxcon-total-box">
              <div className="fxcon-title">Paid Users</div>
              <div className="fxcon-count">
                {
                  (!_.isEmpty(fixtureDetail) && !_.isUndefined(fixtureDetail.paid_users))
                    ?
                    fixtureDetail.paid_users
                    :
                    0
                }
              </div>
            </div>
          </Col>
          <Col sm={3}>
            <div className="fxcon-total-box">
              <div className="fxcon-title">Free Users</div>
              <div className="fxcon-count">
                {
                  (!_.isEmpty(fixtureDetail) && !_.isUndefined(fixtureDetail.free_users))
                    ?
                    fixtureDetail.free_users
                    :
                    0
                }
              </div>
            </div>
          </Col>
        </Row>

        <Col className="heading-box">
          <div className="contest-tempalte-wrapper">
            <h2 className="h2-cls">
              {
                ActiveTab == '0' ? 'Live' : ActiveTab == '1' ? 'Upcoming' : 'Completed'
              } Contest
            </h2>
          </div>

          <div className="fixture-contest">
            {
              // !this.state.collection_master_id &&
              <label className="backtofixtures" onClick={() => this.props.history.push('/stockpredict/fixture?tab=' + ActiveTab)}> {'<'} Back to candle</label>
            }
          </div>
        </Col>
        <div className="border-bottom mb-4"></div>
        <Row>
          {contestList.map((item, group_index) => (
            // <div className="contest-group-container" key={group_index}>
            <Fragment>
              {/* <Col md="12" className="xanimate-left">
                <h4>{item.group_name}</h4>
              </Col> */}
              {item.contest_list.map((contest, idx) => (
                <Col key={idx} md="4">
                  <div className="contest-group">
                    <div className="contest-list-wrapper">
                      {/* <div className="contest-card more-contest-card sponsor-cls"> */}
                      <div className={"contest-card more-contest-card xsponsor-cls" + (contest.sponsor_logo ? ' sponsor-cls' : '')}>
                        <div className="contest-list contest-card-body">
                          <div className="pinned-area">
                            {
                              contest.is_pin_contest == '1' &&
                              <img onClick={(e) => this.markPinContest(e, contest, group_index, idx, 0)} src={Images.PIN_ACTIVE} alt="" className="pinned-active" />
                            }
                            {
                              contest.is_pin_contest == '0' &&
                              <i onClick={(e) => this.markPinContest(e, contest, group_index, idx, 1)} className="icon-pinned"></i>
                            }
                          </div>
                          <div className="contest-list-header clearfix">
                            <div className="contest-heading">
                              <div className="action-head clearfix">
                                <div onClick={() => this.props.history.push('/stockpredict/contest_detail/' + contest.contest_id + '?tab=' + ActiveTab)} className="contest-name text-ellipsis">{contest.contest_name}</div>
                              </div>
                              <div className="clearfix">
                                <ul className="ul-action con-action-list">
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
                                          <Tooltip placement="right" isOpen={contest.swin_tt} target={"swin_" + group_index + '_' + idx} toggle={() => this.SWinToggle(group_index, idx)}>{SCRATCH_WIN_TAP}</Tooltip>
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
                              </div>
                            </div>
                            <div className="display-table">
                              <div className="progress-bar-default display-table-cell v-mid">
                                <div className="danger-area progress">
                                  <div className="text-center"></div>
                                  {(selected_sport == "7" && fixtureDetail.playing_eleven_confirm == "1" && AllowSystemUser) ? <Progress className="com-contest-mul-progress" multi>

                                    <Progress bar className="su-progress" value={this.ShowProgressBar(contest.total_system_user, contest.size)} >
                                      <span className="su-count">System user {contest.total_system_user}</span>
                                    </Progress>
                                    <Progress bar className="com-contest-progress all-u-progress" value={this.ShowProgressBar(parseInt(contest.total_user_joined) - parseInt(contest.total_system_user), contest.size)} >
                                      <span className="total-u-count">Total user {contest.total_user_joined}</span>
                                    </Progress>
                                  </Progress> : <Progress value={this.ShowProgressBar(contest.total_user_joined, contest.size)} />}


                                </div>
                                <div className="progress-bar-value"><span className="user-joined">{contest.total_user_joined}</span><span className="total-entries"> / {contest.size} Entries</span><span className="min-entries">min {contest.minimum_size}</span></div>
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
                                  {contest.entry_fee == 0 &&

                                    <span>Free</span>

                                  }
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    {
                      contest.sponsor_logo &&
                      <div className="league-listing-container xmar-10">
                        {/* <div className="sponsor-name float-left">
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
                    }

                    <div className="promote-container mt-2">

                      {
                        fixtureDetail.match_started == 0 &&
                        <p onClick={() => this.toggleContestPromoteModal(idx, contest)}><img src={Images.PROMOTE} alt="" className="promote-img" /><span>Promote</span> </p>
                      }
                    </div>
                  </div>
                </Col>
              ))}
            </Fragment>
            // </div>
          ))}
        </Row>
        {contestList.length <= 0 &&
          <div className="no-records">No Record Found.</div>
        }

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
                        <tr>
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
                              prize.min_value
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
                              prize.max_value
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

        {
          this.state.contest_promote_model &&
          <PromoteContestModal IsPromoteContestShow={this.state.contest_promote_model} IsPromoteContestHide={this.PromoteContestHide} ContestData={{ contestObj: contestObj }} />}

        {
          this.state.promote_model &&
          <PromoteNotActive IsPromoteShow={this.state.promote_model} IsPromoteHide={this.PromoteHide} />}
      </div>
    );
  }
}

export default SP_FixtureContest;