import React from 'react';
import { Button, Modal, ModalHeader, ModalBody, Col, Row, Card, Input } from 'reactstrap';
import * as NC from "../helper/NetworkingConstants";
import * as MODULE_C from "../views/Marketing/Marketing.config";
import _ from 'lodash';
import { notify } from 'react-notify-toast';
import WSManager from "../helper/WSManager";
import HF from '../helper/HelperFunction';

export default class PromoteFixure extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      fixturePromoteParam: {
        email_fixture_model: false,
        message_fixture_model: false,
        notification_fixture_model: false
      },
      cd_balance: {},
      fixture_promote_model: this.props.IsPromoteFixtureShow || false,
      FixtureData: this.props.FixtureData ? { ...this.props.FixtureData.fixtureObj, email_count: 0, sms_count: 0, notification_count: 0 } : {}
    };
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

  get_fixture_cd_type_possible_counts = (notificationData) => {

    var param = MODULE_C.extend(notificationData, MODULE_C.userBaseType[1]);//1 for all user
    param.email_template_id = 4;
    WSManager.Rest(NC.baseURL + MODULE_C.NOTIFY_BY_SELECTION_COUNT, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {

        this.setState({

          FixtureData: {
            ...this.state.FixtureData,
            email_count: responseJson.data.email_count,
            notification_count: responseJson.data.notification_count,
            sms_count: responseJson.data.sms_count
          }

        });
      }

    })

  }

  fixtureNotifyBySelection = (seasonGameUid) => {
    let param = { ...this.state.fixturePromoteParam, season_game_uid: seasonGameUid };

    WSManager.Rest(NC.baseURL + MODULE_C.NOTIFY_BY_SELECTION, param).then((responseJson) => {

      this.setState({ posting: false });
      if (responseJson.response_code === NC.successCode) {
        this.setState({ fixture_promote_model: false })
        notify.show(responseJson.message, "success", 3000);
        this.props.IsPromoteFixtureHide();
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/401');
      }
    })
  }

  toggleFixturePromoteModal = (key, val) => {

    this.setState({
      fixture_promote_model: !this.state.fixture_promote_model,
    });

    this.props.IsPromoteFixtureHide();
  }
  render() {

    const { IsFixturePromoteHide } = this.props;

    const { fixturePromoteParam, FixtureData } = this.state;
    return (
      <Modal isOpen={this.state.fixture_promote_model} toggle={this.toggleFixturePromoteModal} className={this.props.className}
      >
        <ModalHeader toggle={IsFixturePromoteHide} className="resend">
          <Col lg={12}>
            <h5 className="resend title"> PROMOTE</h5></Col>
        </ModalHeader>
        <ModalBody>
          {
            <Row className="usertime">
              <Col lg={12} className="resendvs">
                <Col className="img1"><img Style='width:58px; height:58px;' src={FixtureData.home_flag} /></Col>
                <Col className="resendvsmatch">
                  <h3 Style="font-size:18px;font-weight:bold;color: #000000;">{FixtureData.home} vs {FixtureData.away} </h3>
                  <h6 Style="font-size:12px;font-weight:bold;color: #000000;">
                    {/* {FixtureData.season_scheduled_date} */}
                    {HF.getFormatedDateTime(FixtureData.season_scheduled_date, "D-MMM-YYYY hh:mm A")}
                  </h6>
                </Col>
                <Col className="img2"> <img Style='width:58px; height:58px; ' src={FixtureData.away_flag} /></Col>
              </Col>
            </Row>
          }
          <Row className="communication">
            <Col lg={12}>
              <div className="comchannel">Communication Channel</div>
              <div className="customcontrol">
                <div className="custom-control custom-checkbox custom-control-inline">
                  <Input type="checkbox" id="email_fixture_model" className="custom-control-input" onChange={this.handleFixtureNotificationType}
                    checked={fixturePromoteParam.email_fixture_model}
                    value={fixturePromoteParam.email_fixture_model}></Input>
                  <label className="custom-control-label" for="email_fixture_model">E-MAIL</label>
                </div>
                <div className="custom-control custom-checkbox custom-control-inline">
                  <Input type="checkbox" id="message_fixture_model" className="custom-control-input" onChange={this.handleFixtureNotificationType}
                    checked={fixturePromoteParam.message_fixture_model}
                    value={fixturePromoteParam.message_fixture_model}></Input>
                  <label className="custom-control-label" for="message_fixture_model">SMS</label>
                </div>
                <div className="custom-control custom-checkbox custom-control-inline">
                  <Input type="checkbox" id="notification_fixture_model" className="custom-control-input" onChange={this.handleFixtureNotificationType}
                    checked={fixturePromoteParam.notification_fixture_model}
                    value={fixturePromoteParam.notification_fixture_model}></Input>
                  <label className="custom-control-label" for="notification_fixture_model">Notification</label>
                </div>
              </div>
            </Col>
          </Row>
          <Col>
            <Row className="select preview"></Row>
            <Row className="popcardrow">
              <Col md={4}>
                <Card className="popcard">
                  <h6 className="popcard6">E-MAIL</h6>
                  <h2 className="popcard8"> {this.state.FixtureData.email_count}</h2>
                </Card>
              </Col>
              <Col md={4}>
                <Card className="popcard">
                  <h6 className="popcard6">SMS</h6>
                  <h2 className="popcard8">{this.state.FixtureData.sms_count}</h2>
                </Card>
              </Col>
              <Col md={4}>
                <Card className="popcard">
                  <h6 className="popcard6">Notification</h6>
                  <h2 className="popcard8">{this.state.FixtureData.notification_count}</h2>
                </Card>
              </Col>
            </Row>
          </Col>
          <Row>
            <Col lg={12} className="resend">
              <Button className="resendbtnpop" outline disabled={!fixturePromoteParam.email_template_id} color="danger" onClick={() => this.fixtureNotifyBySelection(FixtureData.season_game_uids)}>Send</Button>
            </Col>
          </Row>
        </ModalBody>
      </Modal>
    );
  }
}