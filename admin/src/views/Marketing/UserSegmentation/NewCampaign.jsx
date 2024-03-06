import React, { Component } from 'react';
import Select from 'react-select';
import {
           
Card, CardBody, Col, Row,CardHeader,Button,
  FormGroup,
  Input,
  Label,
  Nav, NavItem, NavLink,
  Modal, ModalBody, ModalHeader, TabContent, TabPane
} from 'reactstrap';
import { notify } from 'react-notify-toast';
import _ from 'lodash';
import validator from 'validator';
import 'spinkit/css/spinkit.css';
import { CopyToClipboard } from 'react-copy-to-clipboard';
import * as NC from "../../../helper/NetworkingConstants";
import WSManager from "../../../helper/WSManager";
import LS from 'local-storage';
import classnames from 'classnames';
import queryString from 'query-string';
import * as MODULE_C from "../Marketing.config";
import moment from 'moment';
class NewCampaign extends Component {

  constructor(props) {
    super(props);

    this.state = {
      selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
      userSegmentParam: {
        all_user: null,
        login: null,
        signup: null,
        fixture_participation: null,
        custom: null,
        last_7_days: null,
        to_date: "",
        from_date: "",
        season_game_uid: null,
        contest_id: null,
        home: 'IND',
        away: 'PAK',
        home_flag: 'https://communication-dashboard.s3.amazonaws.com/upload/flag/flag_default.jpg',
        away_flag: 'https://communication-dashboard.s3.amazonaws.com/upload/flag/flag_default.jpg',
        season_scheduled_date: '2019-12-29',
        collection_name: ''
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
        custom_sms:'',
        campaign_url:'',
        campaign_name:'',
        campaign_generated_url:'',
        custom_notification_subject:'',
        custom_notification_text:'',
        custom_notification_landing_page:'',

      },
      communication_review_modal: false,
      previewObj: {},
      activeTab: '1',
      depositPromocodes: [],
      matchList: [],
      templateParam: { type: 1 },
      cd_email_template_id: null,
      for_str: null,
      promo_code_id: '',
      cd_balance: {},
      balance_will_used: {
        email_count: 0,
        sms_count: 0,
        notification_count: 0
      },
      isLoading: false,
      copySuccess: '',
      campaign_url_error:''
    };



  }

  componentDidMount() {

    var parsed = queryString.parse(this.props.location.search);
    console.log('parsed.param:', parsed); // replace param with you

    var templateType = {};
    var contestObj = {};
    var promo_code_id = '';
    if (parsed.email_template_id) {

      if (parsed.contest_id) {
        console.log('parsed.contest_id:', parsed.contest_id);
        templateType.type = 0;
        contestObj.contest_id = parsed.contest_id;
      }

      if (parsed.promo_code_id) {
        promo_code_id = parsed.promo_code_id;
      }

      if (parsed.season_game_uid) {
        this.getUpcomingLiveMatchs(parsed.season_game_uid);
        templateType.type = 2;
      }
      this.setState({
        cd_email_template_id: parsed.email_template_id,
        promo_code_id: promo_code_id,
        templateParam: { ...this.state.templateParam, cd_email_template_id: parsed.email_template_id, type: templateType.type }
      }, () => {
        this.getSegmentationTemplate();
      });
    }

    if (parsed.fixture_participation) {
      this.setState({
        userSegmentParam: { ...this.state.userSegmentParam, fixture_participation: parsed.fixture_participation }
      })
    }

    if (parsed.email) {
      this.setState({ notificationData: { ...this.state.notificationData, email: true } });
    }

    if (parsed.for_str) {
      this.setState({
        for_str: parsed.for_str
      });
    }

    if (parsed.message) {
      this.setState({ notificationData: { ...this.state.notificationData, message: true } });
    }
    if (parsed.notification) {
      this.setState({ notificationData: { ...this.state.notificationData, notification: true } });
    }

    if (parsed.all_user) {
      this.setState({ userSegmentParam: { ...this.state.userSegmentParam, all_user: true, ...contestObj } },
        () => {
          this.getFilterResultTest();
        });

    }

    if (parsed.login) {
      this.setState({ userSegmentParam: { ...this.state.userSegmentParam, login: true, ...contestObj } },
        () => {
          this.getFilterResultTest();
        });

    }

    if (parsed.signup) {
      this.setState({ userSegmentParam: { ...this.state.userSegmentParam, signup: true, ...contestObj } },
        () => {
          this.getFilterResultTest();
        });

    }

    this.get_cd_balance();

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


  get_cd_type_possible_counts = (notificationData) => {

    var param = { ...this.state.userSegmentParam, ...notificationData };//1 for all user   
    WSManager.Rest(NC.baseURL + MODULE_C.NOTIFY_BY_SELECTION_COUNT, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {

        this.setState({

          balance_will_used: {
            email_count: responseJson.data.email_count,
            notification_count: responseJson.data.notification_count,
            sms_count: responseJson.data.sms_count
          }

        });
      }

    })

  }

  toggleRecentCModal = () => {

    this.setState({
      communication_review_modal: !this.state.communication_review_modal,
    });

    console.log('email body:', this.state.userSegmentParam);
    var email_body = this.state.previewObj.email_body;
    //email_body = email_body.replace("{{offer_percentage}}", 10);
   // email_body = email_body.replace("{{promo_code}}", "FIRSTDEPOSIT");
    //email_body = email_body.replace("{{amount}}", 10);
    email_body = email_body.replace("{{year}}", (new Date()).getFullYear());
    email_body = email_body.replace("{{SITE_TITLE}}", 'Fantasy Sports');
    email_body = email_body.replace("{{home}}", this.state.userSegmentParam.home);
    email_body = email_body.replace("{{away}}", this.state.userSegmentParam.away);
    email_body = email_body.replace("{{home_flag}}", this.state.userSegmentParam.home_flag);
    email_body = email_body.replace("{{away_flag}}", this.state.userSegmentParam.away_flag);
    email_body = email_body.replace("{{season_scheduled_date}}", this.state.userSegmentParam.season_scheduled_date);
    email_body = email_body.replace("{{collection_name}}", this.state.userSegmentParam.collection_name);

    var sms_message = this.state.previewObj.message_body;
    sms_message = sms_message.replace("{{home}}", this.state.userSegmentParam.home);
    sms_message = sms_message.replace("{{away}}", this.state.userSegmentParam.away);
    sms_message = sms_message.replace("{{season_scheduled_date}}", this.state.userSegmentParam.season_scheduled_date);
    sms_message = sms_message.replace("{{collection_name}}", this.state.userSegmentParam.collection_name);

    var notification_message = this.state.previewObj.notification_message;
    notification_message = notification_message.replace("{{home}}", this.state.userSegmentParam.home);
    notification_message = notification_message.replace("{{away}}", this.state.userSegmentParam.away);
    notification_message = notification_message.replace("{{season_scheduled_date}}", this.state.userSegmentParam.season_scheduled_date);
    notification_message = notification_message.replace("{{collection_name}}", this.state.userSegmentParam.collection_name);


    this.setState({
      previewObj: {
        ...this.state.previewObj, email_body: email_body,
        message_body: sms_message,
        notification_message: notification_message

      }
    });
  }
  getSegmentationTemplate() {

    this.setState({ isLoading: true });
    WSManager.Rest(NC.baseURL + MODULE_C.GET_SEGEMENTATION_TEMPLATE_LIST, this.state.templateParam).then((responseJson) => {
      this.setState({ isLoading: false });
      if (responseJson.response_code === NC.successCode) {
        const templates = [];
        responseJson.data.result.map((data, key) => {
          templates.push({
            value: data.cd_email_template_id, label: data.display_label,
            detail: data
          })
          return '';
        })
        this.setState({ templateList: templates },
          () => {

            if (this.state.cd_email_template_id) {
              this.setState({
                notificationData: { ...this.state.notificationData, email_template_id: this.state.cd_email_template_id, }
              }, () => {

                if (templates.length) {
                  this.setState({ previewObj: templates[0].detail });
                }

              });



              if (this.state.promo_code_id) {
                this.getDepositPromotionsPromocodes()
              }
            }
          });

      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/login');
      } else {
        this.setState({ posting: false });
      }
    })
  }

  getSelectedSport = () => {
    var sportName = LS.get('sports_list').filter(function (item) {
      return item.value === LS.get('selected_sport') ? true : false;
    });

    console.log('sportName:', sportName);
    return {
      sports_id: sportName[0].value,
      sports_name: sportName[0].label.toLowerCase()
    };
  }

  notifyBySelection() {

    var sportName = this.getSelectedSport();

    let param = { ...this.state.notificationData, ...this.state.userSegmentParam, ...sportName };
    console.log('notification params:', param);
    this.setState({ isLoading: true });
    WSManager.Rest(NC.baseURL + MODULE_C.NOTIFY_BY_SELECTION, param).then((responseJson) => {
      this.setState({ isLoading: false });
      if (responseJson.response_code === NC.successCode) {

        notify.show(responseJson.message, "success", 3000);
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/login');
      } else {
        this.setState({ posting: false });
      }
    })
  }

  getUpcomingLiveMatchs = (sid) => {
    this.setState({ isLoading: true });
    WSManager.Rest(NC.baseURL + MODULE_C.GET_LIVE_UPCOMING_MATCHS, { sports_id: this.state.selected_sport }).then((responseJson) => {
      this.setState({ isLoading: false });
      if (responseJson.response_code === NC.successCode) {

        var matchList = [];
        responseJson.data.collection.map((data, key) => {
          matchList.push({
            value: data.season_game_uid, label: data.collection_name + ' ' + moment(data.season_scheduled_date).format("YYYY-MM-DD hh:mm A"),
            detail: data
          })
          return '';
        })

        this.setState({
          matchList: matchList,

        },
          () => {

            if (sid) {
              this.setState({
                userSegmentParam: { ...this.state.userSegmentParam, season_game_uid: sid }
              },
                () => {
                  this.getFilterResultTest();
                });
              console.log('sid:', sid);
            }
          });


        //notify.show(responseJson.message, "success", 3000);
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/login');
      } else {
        this.setState({ posting: false });
      }
    })

  }


  exportUser = () => {

    var pairs = [];

    _.map(this.state.userSegmentParam, (val, key) => {
      pairs.push(encodeURIComponent(key) + '=' + encodeURIComponent(val));
    });

    var query_string = pairs.join('&');


    window.open(NC.baseURL + MODULE_C.EXPORT_FILTER_DATA + '?' + query_string, '_blank');
  };

  checkFromOtherSource = () => {
    return this.state.for_str === null
  }

  handleActivityValue = (e, option, flag) => {
    if (e) {
      let value = e.target.value;
      let id = e.target.id;
      if (option === "activity") {
        this.state.userSegmentParam.all_user = null
        this.state.userSegmentParam.login = null
        this.state.userSegmentParam.signup = null
        this.state.userSegmentParam.fixture_participation = null
        this.state.userSegmentParam.last_7_days = null
        this.state.userSegmentParam.custom = null
        if (this.checkFromOtherSource()) {
          this.state.userSegmentParam.season_game_uid = null
        }
      } else {
        this.state.userSegmentParam.last_7_days = null
        this.state.userSegmentParam.custom = null
      }
      this.state.userSegmentParam[id] = value

      if (this.checkFromOtherSource()) {
        this.setState({
          templateParam: { type: 1 }
        });
      }
      this.setState({
        userSegmentParam: this.state.userSegmentParam,
        notificationData: { ...this.state.notificationData, email: false, message: false, notification: false, promo_code_id: '' },
        total_users: 0,
        depositPromocodes: []

      }, function () {
        console.log('selected_player:', this.state.userSegmentParam)

        if (flag) {

          this.getSegmentationTemplate();
        }
      })
    }
  }

  getFilterResultTest = () => {
    let param = this.state.userSegmentParam;
    this.setState({ isLoading: true });
    WSManager.Rest(NC.baseURL + MODULE_C.GET_FILTER_RESULT_TEST, param).then((responseJson) => {
      this.setState({ isLoading: false });
      if (responseJson.response_code === NC.successCode) {
        this.setState({
          total_users: responseJson.data.total_users,
          balance_will_used: {
            email_count: 0,
            sms_count: 0,
            notification_count: 0,

          }
        });
        this.state.notificationData.user_ids = responseJson.data.user_ids;
        this.setState({ notificationData: this.state.notificationData });
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/login');
      } else {
        this.setState({ posting: false });
      }
    })
  }

  getDepositPromotionsPromocodes = () => {
    WSManager.Rest(NC.baseURL + MODULE_C.GET_DEPOSIT_PROMOCODES, {}).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        this.setState({ depositPromocodes: responseJson.data.promocodes },
          () => {
            if (this.state.promo_code_id) {
              this.setState({
                notificationData: { ...this.state.notificationData, promo_code_id: this.state.promo_code_id },
                promo_code_id: ''
              });
            }
          });

      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/login');
      } else {
        this.setState({ posting: false });
      }
    })
  }

  handleFieldVal = (e) => {
    let value = e.target.value;
    let name = e.target.name;

    this.state.userSegmentParam[name] = value
    this.setState({ userSegmentParam: this.state.userSegmentParam });

  }

  handleFixture = (selectedOption) => {

    console.log('select:', selectedOption);

    this.setState({
      userSegmentParam: {
        ...this.state.userSegmentParam, season_game_uid: selectedOption.value,
        home: selectedOption.detail.home,
        away: selectedOption.detail.away,
        home_flag: selectedOption.detail.home_flag,
        away_flag: selectedOption.detail.away_flag,
        season_scheduled_date: selectedOption.detail.season_scheduled_date,

      },
      templateParam: { type: 2 }
    }, () => {
      this.getSegmentationTemplate();
    });

  }

  handleChange = (selectedOption) => {

    if (!selectedOption) {
      return false;
    }

    this.setState({
      notificationData: {...this.state.notificationData,
        email:false,
        message:false,
        notification:false,
        custom_sms:'',
        campaign_url:'',
        campaign_name:'',
        campaign_generated_url:'',
        custom_notification_subject:'',
        custom_notification_text:'',
        custom_notification_landing_page:'',
      }
    },
    ()=>
    {
      if (selectedOption && selectedOption.detail && selectedOption.detail.template_name === 'admin-refer-a-friend') {
        //get percentage promocodes for deposit
        this.getRenderedTempate(selectedOption.detail);
      }
      //check for deposit template
      if (selectedOption && selectedOption.detail && selectedOption.detail.template_name === 'promotion-for-deposit') {
        //get percentage promocodes for deposit
        this.getDepositPromotionsPromocodes();
      }
      else {
        this.setState({
          depositPromocodes: []
        });
      }

      let value = selectedOption.value;
      console.log("selectedOption:", selectedOption);
      //this.state.notificationData.email_template_id = value;
      this.setState({
        notificationData: { ...this.state.notificationData, email_template_id: value, promo_code_id: '' },
        previewObj: selectedOption.detail,
  
      });
    });
   
  }

  getRenderedTempate = (detail) => {
    console.log('pre:',detail);
    WSManager.Rest(NC.baseURL + MODULE_C.RENDER_EMAIL_BODY, detail).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        this.setState({ previewObj: {...this.state.previewObj,email_body:responseJson.data.email_body,message_body:responseJson.data.message_body} });

      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/login');
      } else {
        this.setState({ posting: false });
      }
    })
  }

  handlePromocodeChange = (selectedOption) => {



    console.log('tempalte::::', this.state.previewObj);

    let value = selectedOption.value;
    console.log("selectedOption:", selectedOption);
    //this.state.notificationData.email_template_id = value;
    this.setState({
      notificationData: { ...this.state.notificationData, promo_code_id: value },
    },()=>{

      this.getRenderedTempate({...this.state.previewObj,promo_code_id:value});
    });
  }

  handleNotificationType = (e) => {

    let value = e.target.value;
    let id = e.target.id;
    console.log(e.target.id, e.target.value, typeof e.target.value);

    var notificationData = _.cloneDeep(this.state.notificationData);
    notificationData[id] = value == 'false' ? true : false;
    this.setState({ notificationData: notificationData }, () => {
      console.log(this.state.notificationData);
      this.get_cd_type_possible_counts(notificationData);
    });
  }

  handleCustomSMS = (e) => {

    let value = e.target.value;
    let id = e.target.id;
    console.log(e.target.id, e.target.value, typeof e.target.value);

    var notificationData = _.cloneDeep(this.state.notificationData);
    notificationData.custom_sms = value;
    this.setState({ notificationData: notificationData }, () => {
      console.log(this.state.notificationData);
     
    });
  }

  handleCustomCampaign = (e) => {

    let value = e.target.value;
    let id = e.target.id;
    console.log(e.target.id, e.target.value, typeof e.target.value);

    var notificationData = _.cloneDeep(this.state.notificationData);
    notificationData[id] = value;
    this.setState({ notificationData: notificationData }, () => {
      console.log(this.state.notificationData);
     
    });
  }

  generate_campaign_url =()=>{

    setTimeout(()=>{
      var url = this.state.notificationData.campaign_url+'/?utm_campaign='+encodeURIComponent(this.state.notificationData.campaign_name)+'&utm_source='+MODULE_C.UTM_SOURCE_SMS+'&utm_medium='+MODULE_C.UTM_MEDIUM;
      var notificationData = _.cloneDeep(this.state.notificationData);
      notificationData.campaign_generated_url = url;
      this.setState({notificationData: notificationData});
    },500);
   
  }

  handleLandingPage = (selectedOption) => {

    if (!selectedOption) {
      return false;
    }

    let value = selectedOption.value;
    console.log("selectedOption:", selectedOption);
    //this.state.notificationData.email_template_id = value;
    this.setState({
      notificationData: { ...this.state.notificationData, custom_notification_landing_page: value },
    });
   
  }
  handleCustomNotification = (e) => {

    let value = e.target.value;
    let id = e.target.id;
    console.log(e.target.id, e.target.value, typeof e.target.value);

    var notificationData = _.cloneDeep(this.state.notificationData);
    notificationData[id] = value;
    this.setState({ notificationData: notificationData }, () => {
      console.log(this.state.notificationData);
     
    });
  }
  checkUrl = () =>{

    console.log('enter');
    if(!validator.isURL(this.state.notificationData.campaign_url))
    {
      this.setState({
        campaign_url_error:"Please enter valid url"
      });  
    }
    else{
      this.setState({
        campaign_url_error:""
      });  
    }
  
  }
  


  render() {

    const {
      userSegmentParam,
      total_users,
      templateList,
      notificationData,
      matchList,
      cd_balance,
      previewObj
    } = this.state;

    return (
      <div className="animated fadeIn new-campaign">
        {
          this.state.isLoading &&
          <Row>
            <div className="loader-body">
              <div className="sk-spinner sk-spinner-pulse"></div>

            </div>
          </Row>

        }

        <div className="new campaign mb-4">
          <Row Style='flex-wrap:nowrap;align-items: center;'>
            <Col sm={6} >
              <h2 className="h2-cls">New Campaign {this.state.for_str}</h2>

            </Col>
            <Col sm={6} >
              <div onClick={() => this.props.history.goBack()} className='pull-right back-cursor'> {"< Back to Fixtures"}</div>
            </Col>
          </Row>
        </div>

        <Row>
          <Col lg="12">
            <Card className="card userbase">
              <CardHeader className="userbasebar">
                Select Userbase
              </CardHeader>
              <CardBody>
                {
                  <Row>
                    <Col md={12}>
                      <FormGroup>
                        <div className="custom-control custom-radio custom-control-inline">
                          <input type="radio" id="all_user" className="custom-control-input" value="1" checked={(userSegmentParam.all_user) ? true : false}
                            onChange={(e) => this.handleActivityValue(e, 'activity', 1)}></input>
                          <label className="custom-control-label" for="all_user">All Users</label>
                        </div>

                        <div className="custom-control custom-radio custom-control-inline">
                          <input type="radio" id="login" className="custom-control-input" value="1" checked={(userSegmentParam.login) ? true : false}
                            onChange={(e) => this.handleActivityValue(e, 'activity', 1)}></input>
                          <label className="custom-control-label" for="login">Login Activity</label>
                        </div>

                        <div className="custom-control custom-radio custom-control-inline">
                          <input type="radio" id="signup" className="custom-control-input" value="1" checked={(userSegmentParam.signup) ? true : false}
                            onChange={(e) => this.handleActivityValue(e, 'activity', 1)}></input>
                          <label className="custom-control-label" for="signup">Signup Activity</label>
                        </div>
                        {
                          this.state.for_str === null &&
                          <React.Fragment>
                            <div className="custom-control custom-radio custom-control-inline">
                              <input type="radio" id="fixture_participation" className="custom-control-input" value="1" checked={(userSegmentParam.fixture_participation) ? true : false}
                                onChange={(e) => { this.handleActivityValue(e, 'activity', 0); this.getUpcomingLiveMatchs() }}></input>
                              <label className="custom-control-label" for="fixture_participation">By Fixture Participation</label>
                            </div>
                          </React.Fragment>
                        }


                      </FormGroup>
                    </Col>
                  </Row>
                }


                {
                  userSegmentParam.fixture_participation &&
                  <Row>
                    <Col lg={12}>
                      <div className="select preview promocode-select">
                        {/* <Col md={4}> */}
                        <Select class="form-control"
                          value={userSegmentParam.season_game_uid}
                          onChange={this.handleFixture}
                          options={matchList}>
                          <div className="Select-placeholder">Select Match</div>
                        </Select>
                      </div>
                    </Col>
                  </Row>
                }


                {
                  (userSegmentParam.login || userSegmentParam.signup) &&
                  <Row>
                    <Col md={12} >
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

                  <FormGroup>
                    <Row>
                      <Col md={4}>
                        <Label htmlFor="from_date"> From Date</Label>
                        <Input type="date" id="from_date" name="from_date" placeholder="" onChange={(e) => this.handleFieldVal(e)} required />
                      </Col>
                      <Col md={4}>
                        <Label htmlFor="to_date">To Date</Label>
                        <Input type="date" id="to_date" name="to_date" placeholder="Duration From" onChange={(e) => this.handleFieldVal(e)} required />
                      </Col>
                    </Row>
                  </FormGroup>

                }
                <Col lg={12}>
                  <Row className="get button mB20 mT20">
                    <div className="getresultbtn">
                      {
                        //this.state.for_str===null &&
                        <Button outline color="danger" onClick={() => this.getFilterResultTest()}>Get Results</Button>
                      }
                      <label className="totaluser">Total Users : {total_users}</label>
                    </div>
                    {/* <Col md={2}>
                <label>Total users : {total_users}</label>
                </Col>                 */}
                  </Row>
                </Col>


                {/* <Row className="align-items-center mB20 mT20" >
                    <Col col="3" sm="4" md="2" xl className="mb-3 mb-xl-0">
                      <Button outline color="danger" onClick ={()=>this.getFilterResultTest()}>Get Results</Button>
                    </Col>
                    <Col col="3" sm="4" md="2" xl className="mb-3 mb-xl-0">
                      <label>Total users : {total_users}</label>
                    </Col>
                      <Col col="3" sm="4" md="2" xl className="mb-3 mb-xl-0">
                      {
                        total_users > 0 &&
                        <Button outline color="danger" onClick={()=>this.exportUser()}>Export</Button>
                      }
                    </Col>
                </Row> */}

              </CardBody>
            </Card>
            {
              total_users > 0 &&
              <Card className="card templates">
                <CardHeader className="userbasebar">
                  Select Templates
                </CardHeader>

                <CardBody>
                  <Row>
                    <Col lg={12}>
                      <div className="select preview promocode-select">
                        {/* <Col md={4}> */}
                        <Select class="form-control"
                          value={notificationData.email_template_id}
                          onChange={this.handleChange}
                          options={templateList}>
                          <div className="Select-placeholder">Promo Code for Deposit</div>
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
                        {

                          notificationData.email_template_id &&
                          <Row className="previewrow">

                            <label onClick={() => this.toggleRecentCModal()} className="preview">Preview</label>
                          </Row>
                        }

                      </div>
                    </Col>
                  </Row>
                  <Col lg={6}></Col>
                  <Row className="mt-4">
                    <Col md={12} className="emailcheckrow">
                      <FormGroup>

                        <div className="custom-control custom-checkbox custom-control-inline">
                          <Input disabled={previewObj.template_name == 'custom-sms' || previewObj.template_name == 'custom-notification'} type="checkbox" id="email" className="custom-control-input" onChange={this.handleNotificationType} checked={notificationData.email} value={notificationData.email}></Input>
                          <label className="custom-control-label" for="email">Email</label>
                        </div>

                        <div className="custom-control custom-checkbox custom-control-inline">
                          <Input disabled={previewObj.template_name == 'custom-notification'} type="checkbox" id="message" className="custom-control-input" onChange={this.handleNotificationType}
                            checked={notificationData.message}
                            value={notificationData.message}></Input>
                          <label className="custom-control-label" for="message">Message</label>
                        </div>

                        <div className="custom-control custom-checkbox custom-control-inline">
                          <Input disabled={previewObj.template_name == 'custom-sms'} type="checkbox" id="notification" className="custom-control-input" onChange={this.handleNotificationType}
                            checked={notificationData.notification}
                            value={notificationData.notification}></Input>
                          <label className="custom-control-label" for="notification">Notification</label>
                        </div>

                      </FormGroup>
                    </Col>
                  </Row>
                  {
                    previewObj.template_name == 'custom-sms' &&
                    <div>
                       <FormGroup>        
                          <Row>
                              
                                  <Col md={6} className="emailcheckrow">
                                    
                                      <Label for="exampleText">Enter Body (160 Characters)</Label>
                                      <a className="pull-right" href="https://coolsymbol.com/emojis/emoji-for-copy-and-paste.html" target="_blank">Emoji Keyboard</a>
                                      <Input bsSize='lg' type="textarea" name="text" id="smsText"
                                        onChange={this.handleCustomSMS}
                                      />
                                      
                                  </Col>
                                    
                          </Row>
                        </FormGroup>  
                        <h3>Campaign URL Builder</h3>      
                        <FormGroup>
                        <Row>
                          <Col md={6} className="emailcheckrow">
                                  
                                  <Label for="exampleText">Website URL</Label>
                                  <Input type="text" name="text" id="campaign_url"
                                    onChange={this.handleCustomCampaign}
                                    onBlur={this.checkUrl}
                                  />
                                  <div>
                                  <Label>{this.state.campaign_url_error}</Label>
                                  </div>
                                  
                              </Col>
                        </Row>
                        </FormGroup>
                        <FormGroup>
                        <Row>
                          <Col md={6} className="emailcheckrow">
                                  
                                  <Label for="exampleText">Campaign Name</Label>
                                  <Input type="text" name="campaign_name" id="campaign_name"
                                    onChange={(e)=>{this.handleCustomCampaign(e);this.generate_campaign_url();}}
                                  />
                                  
                              </Col>
                        </Row>
                        </FormGroup>
                        <FormGroup>
                        <Row>
                          <Col md={6} className="emailcheckrow">
                                  
                                  <Label for="exampleText">Generated Campaign URL</Label>
                                  <Input type="text" name="campaign_generated_url"
                                  id="campaign_generated_url"
                                  readOnly={true}
                                    onChange={(e)=>{this.handleCustomCampaign(e);this.generate_campaign_url(); }}
                                    value={this.state.notificationData.campaign_generated_url}
                                  
                                  />
                                
                              </Col>
                              <Col>
                              <CopyToClipboard onCopy={this.onCopyLink} text={this.state.notificationData.campaign_generated_url} className="social-circle icon-link">
                              <Button outline color="danger" >Copy</Button>
                                        </CopyToClipboard>
                              </Col> 
                        </Row>
                        <div>
                        *Use this URL in any promotional channels you want to be associated with this custom campaign ** campaign medium will be SMS. campaign source will be Communication dashboard
                        </div>
                        </FormGroup>
                    </div>
                  }
                  {
                    previewObj.template_name == 'custom-notification' &&
                    <div>
                       <FormGroup>
                        <Row>
                          <Col md={6} className="emailcheckrow">
                                  
                                  <Label for="exampleText">Subject</Label>
                                  <Input type="text" name="custom_notification_subject" id="custom_notification_subject"
                                    onChange={(e)=>{this.handleCustomNotification(e);}}
                                  />
                                  
                              </Col>
                        </Row>
                        </FormGroup>
                      <FormGroup>        
                          <Row>
                               <Col md={6} className="emailcheckrow">
                                    
                                      <Label for="exampleText">Enter Body </Label>
                                      <a className="pull-right" href="https://coolsymbol.com/emojis/emoji-for-copy-and-paste.html" target="_blank">Emoji Keyboard</a>
                                      <Input bsSize='lg' type="textarea" name="custom_notification_text" id="custom_notification_text"
                                        onChange={this.handleCustomNotification}
                                      />
                                      
                                  </Col>
                                    
                          </Row>
                        </FormGroup>  
                      <FormGroup>        
                          <Row>
                               <Col md={6} className="emailcheckrow">
                                    
                               <Select class="form-control"
                                value={notificationData.custom_notification_landing_page}
                                onChange={this.handleLandingPage}
                                options={MODULE_C.notification_landing_pages}>
                                <div className="Select-placeholder">Select Landing Page</div>
                              </Select>
                                      
                                  </Col>
                                    
                          </Row>
                        </FormGroup>  
                    </div>
                  }

                  <Row>
                    <Col>
                      <Row className="select preview">
                      </Row>

                      <Row className="popcardrow popcardrow-copy">

                        <Col md={4}>
                          <Card className="popcard">
                            <h6 className="popcard6">E-MAIL</h6>
                            <h2 className="popcard8"> {this.state.balance_will_used.email_count}</h2>
                            <h6 className="popcard7">Credit remaining:{cd_balance.email_balance}</h6>
                          </Card>
                        </Col>

                        <Col md={4}>
                          <Card className="popcard">
                            <h6 className="popcard6">SMS</h6>
                            <h2 className="popcard8">{this.state.balance_will_used.sms_count}</h2>
                            <h6 className="popcard7">Credit remaining:{cd_balance.sms_balance}</h6>
                          </Card>


                        </Col>

                        <Col md={4}>
                          <Card className="popcard">
                            <h6 className="popcard6">Notification</h6>
                            <h2 className="popcard8">{this.state.balance_will_used.notification_count}</h2>
                            <h6 className="popcard7">Credit remaining:{cd_balance.notification_balance}</h6>
                          </Card>


                        </Col>

                      </Row>
                    </Col>
                  </Row>
                  <Row className="align-items-left">
                    <Col lg={12}>
                      <Col md={12} className="sendbtn text-center">
                        <Button className="btn-sm" outline color="danger" disabled={!((notificationData.notification == true || notificationData.email == true || notificationData.message == true) && notificationData.email_template_id)} onClick={() => this.notifyBySelection()}>Send</Button>
                      </Col>
                    </Col>
                  </Row>
                </CardBody>
              </Card>
            }

          </Col>
        </Row>
        <Modal isOpen={this.state.communication_review_modal} toggle={this.toggleRecentCModal} className={this.props.className} className="modal-md">
          {/* <div className="modal-dialog modal-dialog-centered"> */}
          <ModalHeader toggle={this.toggleRecentCModal} className="promotion">
            <h5 className="promotion title"> Preview</h5>
          </ModalHeader>
          <ModalBody>


            <div className="popuppreviewtab">
              <Nav tabs>
                <NavItem>
                  <NavLink
                    className={classnames({ active: this.state.activeTab === '1' })}
                    onClick={() => { this.toggle_('1'); }}
                  >
                    Email
                  </NavLink>
                </NavItem>

                <NavItem>
                  <NavLink
                    className={classnames({ active: this.state.activeTab === '2' })}
                    onClick={() => { this.toggle_('2'); }}
                  >
                    SMS
                  </NavLink>
                </NavItem>

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
                    <Col sm="12" className="temptab">
                      <div className="subjecttemp">
                        <text className="subject">Subject - {this.state.previewObj.subject}</text>
                        {/* <text className="promotionalmsg" dangerouslySetInnerHTML={{__html: this.state.RC_obj_detail.email_body}}>  {this.state.RC_obj_detail.email_body}</text> */}
                      </div>


                      <div dangerouslySetInnerHTML={{ __html: this.state.previewObj.email_body }}>

                      </div>
                    </Col>
                  </Row>
                </TabPane>

                <TabPane tabId="2">
                  <Row>
                    <Col sm="12" className="temptab">
                      <div>
                        {this.state.previewObj.message_body}
                      </div>
                    </Col>
                  </Row>
                </TabPane>

                <TabPane tabId="3">
                  <Row>
                    <Col sm="12" className="temptab">
                      <div>
                        {this.state.previewObj.message_body}
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

      </div >
    );
  }
}
export default NewCampaign;
