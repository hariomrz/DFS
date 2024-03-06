import React from 'react';
import { Button, Modal, ModalHeader, ModalBody, Col, Row, Card, Input, FormGroup, Label } from 'reactstrap';
import * as NC from "../helper/NetworkingConstants";
import * as MODULE_C from "../views/Marketing/Marketing.config";
import _ from 'lodash';
import { notify } from 'react-notify-toast';
import WSManager from "../helper/WSManager";
import HF from '../helper/HelperFunction';
export default class PromoteContest extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      cd_balance: {},
      contest_promote_model: this.props.IsPromoteContestShow || false,
      ContestData: this.props.ContestData ? {
        ...this.props.ContestData.contestObj,
        email_count: 0,
        notification_count: 0,
        sms_count: 0
      } : {},
      userSegmentParam: {
        all_user: null,
        login: null,
        signup: null,
        custom: null,
        last_7_days: null,
        to_date: "",
        from_date: "",
      },
      total_users: 0,
      templateList: [],
      notificationData: {
        email_template_id: "",
        promo_code_id: "",
        user_ids: [],
        email: false,
        message: false,
        notification: false,

      },
    };
  }

  componentDidMount() {
    this.get_cd_balance();
  }

  handleFieldVal = (e) => {
    let value = e.target.value;
    let name = e.target.name;

    this.state.userSegmentParam[name] = value
    this.setState({ userSegmentParam: this.state.userSegmentParam });

  }

  handleContestNotificationType = (e) => {
    let value = e.target.value;
    let id = e.target.id;
    var notificationData = _.cloneDeep(this.state.notificationData);
    notificationData[id] = value == 'false' ? true : false;
    this.setState({ notificationData: notificationData }, () => {
      this.get_contest_cd_type_possible_counts(this.state.notificationData);
    });
  }

  get_contest_cd_type_possible_counts = (notificationData) => {
    var param = MODULE_C.extend(notificationData, this.state.userSegmentParam);//1 for all user
    param.email_template_id = 2;
    WSManager.Rest(NC.baseURL + MODULE_C.NOTIFY_BY_SELECTION_COUNT, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {

        this.setState({

          ContestData: {
            ...this.state.ContestData,
            email_count: responseJson.data.email_count,
            notification_count: responseJson.data.notification_count,
            sms_count: responseJson.data.sms_count
          }

        });
      }

    })

  }

  get_cd_balance = () => {

    WSManager.Rest(NC.baseURL + MODULE_C.GET_CD_BALANCE, {}).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {

        this.setState({
          cd_balance: responseJson.data.cd_balance
        });
      }

    })

  }

  getFilterResultTest = () => {
    let param = this.state.userSegmentParam;
    WSManager.Rest(NC.baseURL + MODULE_C.GET_FILTER_RESULT_TEST, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        this.setState({ total_users: responseJson.data.total_users });
        this.state.notificationData.user_ids = responseJson.data.user_ids;
        this.setState({ notificationData: this.state.notificationData });
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/401');
      } else {
        this.setState({ posting: false });
      }
    })
  }

  handleActivityValue = (e, option) => {
    if (e) {
      let value = e.target.value;
      let id = e.target.id;
      if (option == "activity") {
        this.state.userSegmentParam.all_user = null
        this.state.userSegmentParam.login = null
        this.state.userSegmentParam.signup = null
      } else {
        this.state.userSegmentParam.last_7_days = null
        this.state.userSegmentParam.custom = null
      }
      this.state.userSegmentParam[id] = value
      this.setState({
        userSegmentParam: this.state.userSegmentParam,
        notificationData: { ...this.state.notificationData, email: false, message: false, notification: false },
        ContestData: {
          ...this.props.ContestData.contestObj,
          email_count: 0,
          notification_count: 0,
          sms_count: 0
        },
        total_users: 0
      })
    }
  }


  contestNotifyBySelection = (contest_id) => {
    let param = { ...this.state.notificationData, contest_id: contest_id };
    WSManager.Rest(NC.baseURL + MODULE_C.NOTIFY_BY_SELECTION, param).then((responseJson) => {
      this.setState({ posting: false });
      if (responseJson.response_code === NC.successCode) {
        this.setState({ contest_promote_model: false })
        notify.show(responseJson.message, "success", 3000);
        this.props.IsPromoteContestHide();
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/401');
      }
    })
  }

  toggleContestPromoteModal = (key, val) => {

    this.setState({
      contest_promote_model: !this.state.contest_promote_model,
    });

    this.props.IsPromoteContestHide();
  }
  render() {

    const { IsContestPromoteHide } = this.props;

    const { notificationData, cd_balance, ContestData, userSegmentParam, total_users, } = this.state;
    return (
      <Modal isOpen={this.state.contest_promote_model} toggle={this.toggleContestPromoteModal} className={this.props.className}
      >
        <ModalHeader toggle={IsContestPromoteHide} className="resend">
          <Col lg={12}>
            <h5 className="resend title"> Promotion </h5></Col>
        </ModalHeader>

        <ModalBody className="">
          <div className="promotion-modal-body">
            <Row className="usertime">
              <Col lg={12}>
                <div className="h2-tag">
                  {ContestData.contest_name}
                </div>
                <div className="winning-tag">
                  {ContestData.size} Entries
                          </div>
                <div className="prize-tag">
                  Prize Pool <span className="rupees-icon">{HF.getCurrencyCode()}</span> {ContestData.prize_pool}
                </div>
              </Col>

            </Row>
            {

              <Row className="">

                <Col lg={12} className="resendvs promote-col-12">
                  <FormGroup>


                    <div className="custom-control custom-radio custom-control-inline">
                      <input type="radio" id="all_user" className="custom-control-input" value="1" checked={(userSegmentParam.all_user) ? true : false}
                        onChange={(e) => this.handleActivityValue(e, 'activity')}></input>
                      <label className="custom-control-label" for="all_user">All Users</label>
                    </div>

                    <div className="custom-control custom-radio custom-control-inline">
                      <input type="radio" id="login" className="custom-control-input" value="1" checked={(userSegmentParam.login) ? true : false}
                        onChange={(e) => this.handleActivityValue(e, 'activity')}></input>
                      <label className="custom-control-label" for="login">Login Activity</label>
                    </div>

                    <div className="custom-control custom-radio custom-control-inline">
                      <input type="radio" id="signup" className="custom-control-input" value="1" checked={(userSegmentParam.signup) ? true : false}
                        onChange={(e) => this.handleActivityValue(e, 'activity')}></input>
                      <label className="custom-control-label" for="signup">Signup Activity</label>
                    </div>

                  </FormGroup>
                </Col>
              </Row>
            }


            {
              (userSegmentParam.login || userSegmentParam.signup) &&
              <Row className="">
                <Col md={12} className="promote-col-12" >
                  <FormGroup>

                    <div className="custom-control custom-radio custom-control-inline last-7-days">
                      <input type="radio" id="last_7_days" className="custom-control-input" value="1" checked={(userSegmentParam.last_7_days) ? true : false}
                        onChange={(e) => this.handleActivityValue(e, 'duration')}></input>
                      <label className="custom-control-label" for="last_7_days">Last 7 days</label>
                    </div>


                    <div className="custom-control custom-radio custom-control-inline">
                      <input type="radio" id="custom" className="custom-control-input" value="1" checked={(userSegmentParam.custom) ? true : false}
                        onChange={(e) => this.handleActivityValue(e, 'duration')}></input>
                      <label className="custom-control-label" for="custom">Custom</label>
                    </div>

                  </FormGroup>
                </Col>
              </Row>
            }

            {
              userSegmentParam.custom && (userSegmentParam.login || userSegmentParam.signup) &&
              <Row>
                <Col className="promote-col-12">
                  <FormGroup className="promote-formgroup">
                    <Col md={6}>
                      <Label htmlFor="from_date"> From Date</Label>
                      <Input type="date" id="from_date" name="from_date" placeholder="Duration From" onChange={(e) => this.handleFieldVal(e)} required />
                    </Col>
                    <Col md={6}>
                      <Label htmlFor="to_date">To Date</Label>
                      <Input type="date" id="to_date" name="to_date" placeholder="Duration From" onChange={(e) => this.handleFieldVal(e)} required />
                    </Col>
                  </FormGroup>
                </Col>

              </Row>

            }

            <Row className="get button mB20 mT20 promote-col-12">
              <Col md={12}>
                <div className="getresultbtn get-result-btn">
                  <Button outline color="danger" onClick={() => this.getFilterResultTest()}>Get Results</Button>
                  <label className="totaluser">Total Users : {total_users}</label>
                </div>
              </Col>


            </Row>

          </div>


          <Row className="communication comm-channal">
            <Col lg={12}>
              <div className="comchannel">Communication Channel</div>

              <div className="customcontrol">

                <div className="custom-control custom-checkbox custom-control-inline">
                  <Input type="checkbox" id="email" className="custom-control-input" onChange={this.handleContestNotificationType}
                    checked={notificationData.email}
                    disabled={total_users === 0}
                    value={notificationData.email}></Input>
                  <label className="custom-control-label" for="email">E-MAIL</label>
                </div>

                <div className="custom-control custom-checkbox custom-control-inline">
                  <Input type="checkbox" id="message" className="custom-control-input" onChange={this.handleContestNotificationType}
                    checked={notificationData.message}
                    disabled={total_users === 0}
                    value={notificationData.message}></Input>
                  <label className="custom-control-label" for="message">SMS</label>
                </div>

                <div className="custom-control custom-checkbox custom-control-inline">
                  <Input type="checkbox" id="notification" className="custom-control-input" onChange={this.handleContestNotificationType}
                    checked={notificationData.notification}
                    disabled={total_users === 0}
                    value={notificationData.notification}></Input>
                  <label className="custom-control-label" for="notification">Notification</label>
                </div>

              </div>

            </Col>
          </Row>
          <Col>
            <Row className="select preview">
            </Row>

            <Row className="popcardrow">

              <Col md={4}>
                <Card className="popcard">
                  <h6 className="popcard6">E-MAIL</h6>
                  <h2 className="popcard8"> {this.state.ContestData.email_count}</h2>
                  <h6 className="popcard7">Credit remaining:{cd_balance.email_balance}</h6>
                </Card>
              </Col>

              <Col md={4}>
                <Card className="popcard">
                  <h6 className="popcard6">SMS</h6>
                  <h2 className="popcard8">{this.state.ContestData.sms_count}</h2>
                  <h6 className="popcard7">Credit remaining:{cd_balance.sms_balance}</h6>
                </Card>


              </Col>

              <Col md={4}>
                <Card className="popcard">
                  <h6 className="popcard6">Notification</h6>
                  <h2 className="popcard8">{this.state.ContestData.notification_count}</h2>
                  <h6 className="popcard7">Credit remaining:{cd_balance.notification_balance}</h6>
                </Card>


              </Col>

            </Row>
          </Col>



          <Row >
            <Col lg={12} className="resend text-center">
              <Button className="resendbtnpop" outline disabled={!notificationData.email_template_id} color="danger" onClick={() => this.contestNotifyBySelection(ContestData.contest_id)}>Send</Button>

            </Col>
          </Row>
        </ModalBody>
      </Modal>


    );
  }
}