import React, { Component } from 'react';
import Select from 'react-select';
import { Card, Col, Row, Input, Button, Modal, ModalBody, ModalFooter, ModalHeader, TabContent, TabPane, Nav, NavItem, NavLink,Tooltip } from 'reactstrap';
import Moment from 'react-moment';
import _ from 'lodash';
import * as NC from "../../../helper/NetworkingConstants";
import WSManager from "../../../helper/WSManager";
import moment from 'moment';
import LS from 'local-storage';
import Slider from "react-slick";
import Pagination from "react-js-pagination";
import { notify } from 'react-notify-toast';
import { MomentDateComponent } from "../../../components/CustomComponent";
import { DELAY_MSG_HEAD, DELAY_TIME_MSG_HEAD } from "../../../helper/Message";
import HelperFunction from '../../../helper/HelperFunction';
class Multigame extends Component {

  constructor(props) {
    super(props);
    let filter = {
      current_page: 1,
      items_perpage: 50, type: 1
    }
    this.state = {
      filter: filter,
      total: 0,
      selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
      leagueList: [],
      collectionfixtureList: [],
      selected_league: "",
      fixture_status: (this.props && this.props.location && this.props.location.state && this.props.location.state.activeTab == 3) ? 2 : "not_complete",
      activeTab: this.props && this.props.location && this.props.location.state && this.props.location.state.activeTab ? this.props.location.state.activeTab : "1",
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
      ShowNewVisitor: false,
      matchList:[]
    };

  }

  componentDidMount() {
      LS.set('isMGEnable',1)
    this.GetAllLeagueList();
  }

  // GET ALL LEAGUE LIST
  GetAllLeagueList = () => {

    this.setState({
      posting: true
    })
    WSManager.Rest(NC.baseURL + NC.GET_MG_FIXTURE_LEAGUE_LIST, { "sports_id": this.state.selected_sport }).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        responseJson = responseJson.data;
        this.setState({
          posting: false
        }, () => {
          this.createLeagueList(responseJson);
          this.GetAllCollectionFixtureList();

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
  // GET ALL COLLECTIONS LIST
  GetAllCollectionFixtureList = () => {
    let { selected_sport, selected_league, filter, fixture_status } = this.state
    let param = {
      "sports_id": selected_sport,
      "league_id": selected_league,
      "limit": filter.items_perpage,
      "page": filter.current_page,
      "status": this.state.activeTab == 2 ? 'upcoming' : this.state.activeTab == 3 ? 'completed' : 'live',
      // "status": filter.type == 2 ? 'upcoming' : filter.type == 3 ? 'completed' : 'live',
      "keyword":""
      // "type": filter.type,
    }
    // {"sports_id":"7","league_id":"","status":"completed","keyword":"","limit":20,"page":1}
    this.setState({
      postingcol: true, collectionfixtureList: []
    })

    WSManager.Rest(NC.baseURL + NC.GET_ALL_COLLECTION_FIXTURE_LIST, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {

        let responseJsonData = responseJson.data;
        this.setState({
          postingcol: false,
          total: responseJsonData.total,
          collectionfixtureList: responseJsonData.result,
          matchList: responseJsonData.match_list
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
      this.setState({ "selected_league": value.value, collectionfixtureList: [] }, function () {
        this.GetAllCollectionFixtureList();
      });
    }
  }
  CreateCollection = () => {
    if(this.state.selected_sport == 15) {
      notify.show("Multigame is not available for this sports.", "error", 5000)
    }else{ 
    this.props.history.push({ pathname: '/game_center/CreateCollection' })
    }
  }

  redirectToCollectionContest = (selectedObj, event_type) => {
    this.props.history.push({ 
      pathname: '/contest/collectioncontest/' + selectedObj.league_id + '/' + selectedObj.collection_master_id
    })
  }

  handlePageChange(current_page) {
    let filter = this.state.filter;
    filter['current_page'] = current_page;
    this.setState({
      filter: filter,
      collectionfixtureList: []
    },
      function () {
        this.GetAllCollectionFixtureList();
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
        collectionfixtureList: []
      }, function () { this.GetAllCollectionFixtureList(); });
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
      let fixtureObjData = _.cloneDeep(this.state.fixtureObjData);

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
    let fixtureObjData = _.cloneDeep(this.state.fixtureObjData);
    var returned_endate = "";
    if (delay_hour > 0 || delay_minute > 0) {

      var old = moment(fixtureObjData.season_scheduled_date).subtract(fixtureObjData.delay_hour, 'hours').subtract(fixtureObjData.delay_minute, 'minutes').format('DD-MMM-YYYY hh:mm A');

      var old_1 = moment(WSManager.getUtcToLocal(old)).format("DD-MMM-YYYY hh:m A")

      var returned_endate = moment(old_1).add(delay_hour, 'hours').add(delay_minute, 'minutes').format('DD-MMM-YYYY hh:mm A');

      fixtureObjData['new_deadline'] = returned_endate;
    }

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

  delayModal = () => {
    let { fixtureObjData, delayPosting, HourMsg, MinuteMsg, delay_hour, delay_minute, delay_message } = this.state
    return (
      <div>
        <Modal
          isOpen={this.state.DelayModalIsOpen}
          className="match-msg-modal"
          toggle={this.openDelayModal}
        >
          <ModalHeader>{DELAY_TIME_MSG_HEAD}</ModalHeader>
          <ModalBody>

            <Row className="msg-matchinfo">
              <img className="cardimg" src={NC.S3 + NC.FLAG + fixtureObjData.home_flag}></img>
              <div className="matchinfo-box">
                <div className="match-title">{fixtureObjData.league_abbr}</div>
                <div className="match-date">
                  <MomentDateComponent data={{ date: fixtureObjData.season_scheduled_date, format: "D-MMM-YYYY hh:mm A" }} />
                </div>
                <div className="match-vs"><b>{fixtureObjData.home}{' VS '}{fixtureObjData.away}</b></div>
              </div>
              <img className="cardimg" src={NC.S3 + NC.FLAG + fixtureObjData.away_flag}></img>
            </Row>

            <Row>
              <Col xs="6">
                <Input type="number" min="0" max="48" maxLength="2" id="delay_hour" name="delay_hour" placeholder="hh"
                  value={delay_hour}
                  onChange={(e) => this.handleFieldVal(e)} required />
                {
                  HourMsg &&
                  <p className="warning-msg">Hour should be 1 to 47 delay</p>
                }
              </Col>
              <Col xs="6">
                <Input type="number" min="0" max="59" maxLength="2" id="delay_minute" name="delay_minute" placeholder="mm"
                  value={delay_minute}
                  onChange={(e) => this.handleFieldVal(e)} required />
                {
                  MinuteMsg &&
                  <p className="warning-msg">Minute should be 1 to 59 delay</p>
                }
              </Col>
              <br /><br />
            </Row>
            <Row>
              <Col md={12}>
                <Input
                  type="textarea"
                  maxLength="160"
                  className="match-msg mt-3"
                  id="delay_message"
                  name="delay_message"
                  placeholder="Enter Delay Message"
                  value={delay_message}
                  onChange={(e) => this.handleFieldVal(e)}
                  required resize="0"
                />

              </Col>
            </Row>
            <Row>
              <Col md={12}>
                <p className="warning-msg">Warning: After your intervention if any update come from feed will not be considered as priority. Your update will be considered as final. For any change you need to update this section again.</p>
              </Col>
            </Row>
            {
              fixtureObjData.new_deadline && fixtureObjData.new_deadline != "" &&
              <Row className="mt-3">
                <Col xs="12" className="new-deadline">
                  New Deadline : {fixtureObjData.new_deadline}
                </Col>
              </Row>
            }

          </ModalBody>
          <ModalFooter className="border-0 justify-content-center">
            <Row>
              <Button
                disabled={!delayPosting}
                className="btn-secondary-outline"
                onClick={() => this.saveDelayTime()}
              >Send</Button>
            </Row>
          </ModalFooter>
        </Modal>
      </div>
    )
  }

  saveDelayTime = () => {
    let { delay_hour, delay_minute, delay_message } = this.state

    let fixtureObjData = _.cloneDeep(this.state.fixtureObjData);
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

  openMsgModal = (item) => {
    this.setState({
      msgModalIsOpen: !this.state.msgModalIsOpen,
      MsgItems: item,
      Message: item.custom_message,
    });
  }

  msgModal = () => {
    let { MsgItems, msgFormValid, Message,selected_sport } = this.state

    return (
      <div>
        <Modal
          isOpen={this.state.msgModalIsOpen}
          className="match-msg-modal"
          toggle={this.openMsgModal}
        >
          <ModalHeader>{DELAY_MSG_HEAD}</ModalHeader>
          <ModalBody>

            <Row className="msg-matchinfo">
              <img className="cardimg" src={NC.S3 + NC.FLAG + MsgItems.home_flag}></img>
              <div className="matchinfo-box">
                <div className="match-title">{MsgItems.league_abbr}</div>
                <div className="match-date">
                  <MomentDateComponent data={{ date: MsgItems.season_scheduled_date, format: "D-MMM-YYYY hh:mm A" }} />
                </div>
                <div className="match-vs"><b>{MsgItems.home}{' VS '}{MsgItems.away}</b></div>
              </div>
              <img className="cardimg" src={NC.S3 + NC.FLAG + MsgItems.away_flag}></img>
            </Row>
            <Row>
              <Col md={12}>
                <textarea
                  rows="3"
                  name="Message"
                  className="match-msg"
                  value={Message}
                  onChange={e => this.handleInputChange(e)} ></textarea>
              </Col>
            </Row>
            <Row>
              <Col md={12}>
                <p className="warning-msg">Warning: After your intervention if any update come from feed will not be considered as priority. Your update will be considered as final. For any change you need to update this section again.</p>
              </Col>
            </Row>
          </ModalBody>
          <ModalFooter className="border-0 justify-content-center">
            <Button
              disabled={msgFormValid}
              className="btn-secondary-outline"
              onClick={() => this.updateMatchMsg(1)}
            >Send</Button>
            {
              (MsgItems.custom_message != null) ?
                MsgItems.custom_message != "" ?
                  <Button
                    className="btn-secondary-outline"
                    onClick={() => this.updateMatchMsg(2)}
                  >Remove</Button>
                  :
                  ''
                :
                ''
            }
          </ModalFooter>
        </Modal>
      </div>
    )
  }


  updateMatchMsg = (flag) => {
    let { MsgItems, Message } = this.state
    let param = {}
    if (flag == 1) {
      param = {
        "season_game_uid": MsgItems.season_game_uid,
        "custom_message": Message
      }
    } else {
      param = {
        "season_game_uid": MsgItems.season_game_uid,
        "custom_message": "",
        "is_remove": "1"
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

  redirectToContestTemplate = (e,selectedObj) => {
    e.stopPropagation()
    this.props.history.push({ 
      pathname: '/contest/createtemplatecollectioncontest/' + selectedObj.league_id + '/' + selectedObj.collection_master_id ,
      state: { ismultigame: true } 
    })
  }
  NewVisitorToggle = () => {
    this.setState({
      ShowNewVisitor: !this.state.ShowNewVisitor
    });
  }
 
  setMatchData=(data)=>{
    let leagueId = JSON.parse(JSON.stringify(data.season_ids))
    leagueId = leagueId.split(',')
    let list =  this.state.matchList.filter(obj => leagueId.includes(obj.season_id))
    return list
  }
  
  render() {
    let { activeTab, total, filter, leagueList, selected_league, collectionfixtureList, fixture_status, postingcol,ShowNewVisitor } = this.state;
    const settings = {
      dots: false,
      infinite: false,
      speed: 500,
      slidesToShow: 1,
      slidesToScroll: 1,
      arrows: false
    };

    return (
      <div className="animated fadeIn dfs-main multigame-fix-wrap">
        {this.delayModal()}
        {this.msgModal()}
        <Row>
          <Col md={9}>
            <Select
              className="dfs-selector mg-dfs-selector"
              id="selected_league"
              name="selected_league"
              placeholder="Select League"
              value={selected_league}
              options={leagueList}
              onChange={(e) => this.handleSelect(e, 'selected_league')}
            />
          </Col>
          {/* <Col md={3} className={this.state.selected_sport == '15' ? 'd-flex justify-content-end' : ''}>
            {this.state.selected_sport == '15' && 
             <i className="icon-info" id="NewVisitorTooltip" >
             <Tooltip
               placement="top"
               isOpen={ShowNewVisitor}
               target="NewVisitorTooltip"
               toggle={this.NewVisitorToggle}>
               <p>Multigame is not available for this sports.</p>
             </Tooltip>
           </i>
            }
            <Button
              className={`pull-right btn-secondary-outline mr-4 ${this.state.selected_sport == '15' ? " disable-match-club-btn" :""}`}
              onClick={() => this.CreateCollection()}>Create match club</Button>
          </Col> */}
           <Col md={3} >
            <Button
              className="pull-right btn-secondary-outline mr-4" 
              onClick={() => this.CreateCollection()}>Create match club</Button>
          </Col>
        </Row>
        <Col>
          <Nav tabs>
            <NavItem>
              <NavLink
                className={activeTab == '1' ? "active" : ""}
                onClick={() => { this.toggleTab('1'); }}
              >
                <label className="live">Live</label>
              </NavLink>
            </NavItem>
            <NavItem>
              <NavLink
                className={activeTab == '2' ? "active" : ""}
                onClick={() => { this.toggleTab('2'); }}
              >
                <label className="live">Upcoming</label>
              </NavLink>
            </NavItem>
            <NavItem>
              <NavLink
                className={activeTab == '3' ? "active" : ""}
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
                            !_.isEmpty(collectionfixtureList) && filter.type == 1
                              ?
                              _.map(collectionfixtureList, (item, index) => {
                                if (item.status != 0 || postingcol) return false;
                                item['fixtures'] = this.setMatchData(item)
                                return (
                                  <Col md={4} className="collection-v" key={"live-fixtures-" + index}>
                                    {<div className="collection_vertically">Match club <i className="icon-info"></i></div>}
                                    <Card className="collectionfixtureList livecard" onClick={() => this.redirectToCollectionContest(item, 'live')}>
                                      <div className="carddiv">
                                        <Col>
                                          <div className="collection-fixture-slider  collection-fixture-slider-new">
                                            <div className="collection_name">{item.collection_name}</div>
                                            <div className="match_count" >{item.season_game_count} matches</div>
                                            <div className="league_name" ><span class="doticon"></span> {item.league_name}</div>
                                            <Slider {...settings}>
                                              {
                                                !_.isEmpty(item.fixtures)
                                                  ?
                                                  _.map(item.fixtures, (fixtureitem, fixtureindex) => {
                                                    return (
                                                      <Card className="livecard" index={fixtureindex}>
                                                        <div className="carddiv" >
                                                          <Col>
                                                            <img className="cardimg" src={NC.S3 + NC.FLAG + fixtureitem.home_flag}></img>
                                                          </Col>
                                                          <Col>
                                                            <h4 className="livcardh3">{(fixtureitem.home) ? fixtureitem.home : 'TBA'} VS {(fixtureitem.away) ? fixtureitem.away : 'TBA'}</h4>
                                                            <h6 className="livcardh6">
                                                              {/* <Moment date={WSManager.getUtcToLocalFormat(fixtureitem.season_scheduled_date, 'D-MMM-YYYY hh:mm A')} format="D MMM hh:mm A" /> */}
                                                              {HelperFunction.getFormatedDateTime(fixtureitem.season_scheduled_date, 'D-MMM-YYYY hh:mm A')}
                                                            </h6>
                                                          </Col>
                                                          <Col>
                                                            <img className="cardimg" src={NC.S3 + NC.FLAG + fixtureitem.away_flag}></img>
                                                          </Col>
                                                        </div>
                                                      </Card>
                                                    )
                                                  }) : ''
                                              }
                                            </Slider>
                                          </div>
                                        </Col>
                                      </div>
                                    </Card>
                                  </Col>
                                )
                              })
                              :
                              ''
                          }
                          {
                            _.isEmpty(collectionfixtureList) &&
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
                    <Col lg={12} className="cardupcomingcol multigame-upc-card">
                      {
                        !_.isEmpty(collectionfixtureList)
                          ?
                          _.map(collectionfixtureList, (item, index) => {
                            item['fixtures'] = this.setMatchData(item)
                            return (

                              <Col md={4} className="collection-v" key={"live-fixtures-" + index}>
                                {<div className="collection_vertically">Match club <i className="icon-info"></i></div>}
                                <Card className="collectionfixtureList livecard" onClick={() => this.redirectToCollectionContest(item, 'live')}>
                                  <div className="carddiv">
                                    <Col>

                                      <div class="collection-fixture-slider  collection-fixture-slider-new">

                                        <div className="collection_name">{item.collection_name}</div>
                                        <div className="match_count" >{item.season_game_count} matches</div>
                                        <div className="league_name" ><span class="doticon"></span> {item.league_name}</div>
                                        <Slider {...settings}>

                                          {
                                            !_.isEmpty(item.fixtures)
                                              ?
                                              _.map(item.fixtures, (fixtureitemU, fixtureindex) => {
                                                return (
                                                  <Card className="livecard">
                                                    <div className="carddiv" >
                                                      <Col>
                                                        <img className="cardimg" src={NC.S3 + NC.FLAG + fixtureitemU.home_flag}></img>
                                                      </Col>
                                                      <Col>
                                                        <h4 className="livcardh3"> {(fixtureitemU.home) ? fixtureitemU.home : 'TBA'} VS {(fixtureitemU.away) ? fixtureitemU.away : 'TBA'} </h4>
                                                        <h6 className="livcardh6">
                                                          {/* <Moment date={WSManager.getUtcToLocalFormat(fixtureitemU.season_scheduled_date, 'D-MMM-YYYY hh:mm A')} format="D MMM hh:mm A" /> */}
                                                          {HelperFunction.getFormatedDateTime(fixtureitemU.season_scheduled_date, 'D-MMM-YYYY hh:mm A')}
                                                        </h6>
                                                      </Col>
                                                      <Col>
                                                        <img className="cardimg" src={NC.S3 + NC.FLAG + fixtureitemU.away_flag}></img>
                                                      </Col>
                                                    </div>
                                                  </Card>
                                                )
                                              }) : ''
                                          }
                                        </Slider>
                                        <div class="action-card-footer"><i title="Contest Template" class="icon-template"  onClick={(e) => this.redirectToContestTemplate(e,item, 'upcoming')}></i></div>
                                      </div>
                                    </Col>
                                  </div>
                                </Card>
                              </Col>
                            )
                          })
                          :
                          ''
                      }
                      {
                        _.isEmpty(collectionfixtureList) &&
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
                  {fixture_status == 2 &&
                    <div>
                      {/* COMPLETED FIXTURE LIST START */}
                      <Row>
                        <Col lg={12} className="cardlivcol">
                          {
                            !_.isEmpty(collectionfixtureList) //&& filter.type == 3
                              ?
                              _.map(collectionfixtureList, (item, index) => {
                                if (item.status != 1 || postingcol) return false;
                                item['fixtures'] = this.setMatchData(item)
                                return (

                                  <Col className="collection-v" md={4} key={"live-fixtures-" + index}>
                                    {<div className="collection_vertically">Match club <i className="icon-info"></i></div>}
                                    <Card className="collectionfixtureList livecard" onClick={() => this.redirectToCollectionContest(item, 'live')}>
                                      <div className="carddiv">
                                        <Col>
                                          <div class="collection-fixture-slider  collection-fixture-slider-new">
                                            <div className="collection_name">{item.collection_name}</div>
                                            <div className="match_count" >{item.season_game_count} matches</div>
                                            <div className="league_name" ><span class="doticon"></span> {item.league_name}</div>
                                            <Slider {...settings}>

                                              {
                                                !_.isEmpty(item.fixtures)
                                                  ?
                                                  _.map(item.fixtures, (fixtureitem, fixtureindex) => {
                                                    return (
                                                      <Card className="livecard">
                                                        <div className="carddiv" >
                                                          <Col>
                                                            <img className="cardimg" src={NC.S3 + NC.FLAG + fixtureitem.home_flag}></img>
                                                          </Col>
                                                          <Col>
                                                            <h4 className="livcardh3">{(fixtureitem.home) ? fixtureitem.home : 'TBA'} VS {(fixtureitem.away) ? fixtureitem.away : 'TBA'}</h4>
                                                            <h6 className="livcardh6">
                                                              {/* <Moment date={WSManager.getUtcToLocalFormat(fixtureitem.season_scheduled_date, 'D-MMM-YYYY hh:mm A')} format="D MMM hh:mm A" /> */}
                                                              {HelperFunction.getFormatedDateTime(fixtureitem.season_scheduled_date, 'D-MMM-YYYY hh:mm A')}
                                                            </h6>
                                                          </Col>
                                                          <Col>
                                                            <img className="cardimg" src={NC.S3 + NC.FLAG + fixtureitem.away_flag}></img>
                                                          </Col>
                                                        </div>
                                                      </Card>
                                                    )
                                                  }) : ''
                                              }
                                            </Slider>
                                          </div>


                                        </Col>
                                      </div>
                                    </Card>
                                  </Col>
                                )
                              })
                              :
                              ''
                          }
                          {
                            _.isEmpty(collectionfixtureList) &&
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
        <Col>
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

      </div>
    );
  }
}

export default Multigame;
