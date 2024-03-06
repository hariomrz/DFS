import React, { Component } from 'react';
import Select from 'react-select';
import { Badge, Card, CardBody, CardFooter, CardHeader, Col, Row, Collapse, Fade , Form,
  FormGroup,
  FormText,
  FormFeedback,
  Input,
  InputGroup,
  InputGroupAddon,
  InputGroupText,
  Button,
  Label,
  Nav, NavItem, NavLink,
  Modal, ModalBody, ModalFooter, ModalHeader,TabContent, TabPane} from 'reactstrap';
  import { notify } from 'react-notify-toast';
  import _ from 'lodash';
  import * as NC from "../../../helper/NetworkingConstants";
  import WSManager from "../../../helper/WSManager";
  import LS from 'local-storage';
  import classnames from 'classnames';
  import * as MODULE_C from "../Marketing.config";
class UserSegmentation extends Component {

  constructor(props) {
    super(props);

    this.state ={
      userSegmentParam:{
        all_user:null,
        login:null,
        signup:null,
        custom:null,
        last_7_days:null,
        to_date:"",
        from_date:"",
      },
      total_users : 0,
      templateList : [],
      notificationData:{
        email_template_id : "",
        promo_code_id:"",
        user_ids : [],
        email : false,
        message : false,
        notification: false, 
        
      },
      communication_review_modal: false,
      previewObj:{},
      activeTab:'1',
      depositPromocodes:[],
    };
  }

  componentDidMount(){
    this.getSegmentationTemplate();
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


  toggleRecentCModal=()=> {

    this.setState({
      communication_review_modal: !this.state.communication_review_modal,
    });

     console.log('email body:',this.state.previewObj.email_body);
     var email_body = this.state.previewObj.email_body;
      email_body = email_body.replace("{{offer_percentage}}", 10);
      email_body = email_body.replace("{{promo_code}}", "FIRSTDEPOSIT");
      email_body = email_body.replace("{{amount}}", 10);
      email_body = email_body.replace("{{year}}", (new Date()).getFullYear());
      email_body = email_body.replace("{{SITE_TITLE}}", 'Fantasy Sports');

      this.setState({
        previewObj:{...this.state.previewObj,email_body:email_body}
      });
  }
  getSegmentationTemplate(){
    WSManager.Rest(NC.baseURL + MODULE_C.GET_SEGEMENTATION_TEMPLATE_LIST,{}).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
          const templates = [];
           responseJson.data.result.map((data, key) => {
            templates.push({ value: data.cd_email_template_id, label: data.display_label,
            detail:data})
            return '';
        })
        this.setState({templateList : templates});
         
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/login');
      } else {
          this.setState({ posting: false });
      }
    })
  }

  notifyBySelection(){
    let param = {...this.state.notificationData,...this.state.userSegmentParam};
    WSManager.Rest(NC.baseURL + MODULE_C.NOTIFY_BY_SELECTION,param).then((responseJson) => {
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

  exportUser = () => {
	
    var pairs = [];
    
    _.map(this.state.userSegmentParam,(val,key)=>{
       pairs.push(encodeURIComponent(key)+'='+encodeURIComponent(val));
    });
    
		var query_string =  pairs.join('&');

	
		window.open(NC.baseURL+MODULE_C.EXPORT_FILTER_DATA+'?'+query_string, '_blank');
	};

  handleActivityValue = (e,option) => {
    if(e){
      let value = e.target.value;
      let id = e.target.id;
      if(option == "activity"){
        this.state.userSegmentParam.all_user = null
        this.state.userSegmentParam.login= null
        this.state.userSegmentParam.signup = null
      }else{
        this.state.userSegmentParam.last_7_days = null
        this.state.userSegmentParam.custom= null
      }
      this.state.userSegmentParam[id] = value
      this.setState({
        userSegmentParam : this.state.userSegmentParam,
        notificationData:{...this.state.notificationData,email:false,message:false,notification:false},
        total_users:0
      },function(){console.log('selected_player:', this.state.userSegmentParam)})
    }
  }

  getFilterResultTest = () => {
    let param=this.state.userSegmentParam; 
    WSManager.Rest(NC.baseURL + MODULE_C.GET_FILTER_RESULT_TEST,param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
           this.setState({total_users : responseJson.data.total_users});
           this.state.notificationData.user_ids =  responseJson.data.user_ids;
           this.setState({notificationData : this.state.notificationData});    
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/login');
      } else {
          this.setState({ posting: false });
      }
    })
  }

  getDepositPromotionsPromocodes =() =>{
    WSManager.Rest(NC.baseURL + MODULE_C.GET_DEPOSIT_PROMOCODES,{}).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
           this.setState({depositPromocodes : responseJson.data.promocodes});
            
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        this.props.history.push('/login');
      } else {
          this.setState({ posting: false });
      }
    })
  }

  handleFieldVal = (e) =>{
    let value = e.target.value;
    let name = e.target.name;

    this.state.userSegmentParam[name] = value
    this.setState({userSegmentParam : this.state.userSegmentParam});

  }

  handleChange = (selectedOption) =>{

    if(!selectedOption)
    {
      this.setState({
        notificationData:{}
      });
      return false;
    }

    //check for deposit template
    if(selectedOption && selectedOption.detail && selectedOption.detail.template_name==='promotion-for-deposit')
    {
      //get percentage promocodes for deposit
      this.getDepositPromotionsPromocodes();
    }
    else{
      this.setState({
        depositPromocodes:[]
      });
    }
    console.log('tempalte::::',selectedOption);

    let value = selectedOption.value;
    console.log("selectedOption:",selectedOption);
    //this.state.notificationData.email_template_id = value;
    this.setState({ notificationData : {...this.state.notificationData,email_template_id:value,promo_code_id:''},
            previewObj: selectedOption.detail,
           
      
    });
  }
  handlePromocodeChange = (selectedOption) =>{


 
    console.log('tempalte::::',selectedOption);

    let value = selectedOption.value;
    console.log("selectedOption:",selectedOption);
    //this.state.notificationData.email_template_id = value;
    this.setState({ notificationData : {...this.state.notificationData,promo_code_id:value},
           
      
    });
  }

  handleNotificationType = (e) =>{
    
    let value = e.target.value;
    let id = e.target.id;
    console.log(e.target.id,e.target.value,typeof e.target.value);

    var notificationData = _.cloneDeep(this.state.notificationData);
    notificationData[id] = value=='false'?true:false;
    this.setState({notificationData : notificationData},()=>
    {
      console.log(this.state.notificationData);
    });
  }

  render() {

    const {
      userSegmentParam,
      total_users,
      templateList,
      notificationData
    } = this.state;

    return (
      <div className="animated fadeIn">

<div className="new campaign">
                  <Row Style = 'flex-wrap:nowrap;align-items: center;'>
                      <Col sm={6} >
                          <h3 className="newcamp">New Campaign</h3>
                      </Col>
                     <Col sm={6} >
                       
                      </Col>
                    </Row>
                  </div>
      
        <Row>
        {/* <Row Style = 'flex-wrap:nowrap;align-items: center;'>
          <Col lg={12}>
          <Col sm={6} >
              <h3>New Campaign</h3>
            </Col>
            <Col sm={6}>
            <h3> {"< Back to Fixtures"}</h3>
            </Col>
          </Col>
          </Row> */}

          <Col lg="12">
            <Card className="card userbase">
                <CardHeader className="userbasebar">
                    Select Userbase
                </CardHeader>
              
              <CardBody>
                <Col md={12}>
                <FormGroup>
                  
                  
                    <div className="custom-control custom-radio custom-control-inline">
                      <input type="radio" id="all_user"  className="custom-control-input" value="1" checked={(userSegmentParam.all_user) ? true : false} 
                            onChange={(e) => this.handleActivityValue(e,'activity')}></input>                    
                      <label className="custom-control-label" for="all_user">All Users</label>
                    </div> 

                    <div className="custom-control custom-radio custom-control-inline">
                      <input type="radio" id="login"  className="custom-control-input" value="1" checked={(userSegmentParam.login) ? true : false} 
                            onChange={(e) => this.handleActivityValue(e,'activity')}></input>
                      <label className="custom-control-label" for="login">Login Activity</label>
                    </div> 

                    <div className="custom-control custom-radio custom-control-inline">
                      <input type="radio" id="signup" className="custom-control-input" value="1" checked={(userSegmentParam.signup) ? true : false} 
                            onChange={(e) => this.handleActivityValue(e,'activity')}></input>
                      <label className="custom-control-label"for="signup">Signup Activity</label>
                    </div> 
                    
                </FormGroup>
                </Col>

                {
                  (userSegmentParam.login || userSegmentParam.signup) &&
                  <Col md={12} >
                  <FormGroup>
                  
                      <div className="custom-control custom-radio custom-control-inline last-7-days">
                        <input type="radio" id="last_7_days" className="custom-control-input" value="1" checked={(userSegmentParam.last_7_days) ? true : false} 
                              onChange={(e) => this.handleActivityValue(e,'duration')}></input>
                        <label className="custom-control-label" for="last_7_days">Last 7 days</label>
                      </div>


                        <div className="custom-control custom-radio custom-control-inline">
                          <input type="radio" id="custom" className="custom-control-input" value="1" checked={(userSegmentParam.custom) ? true : false} 
                                onChange={(e) => this.handleActivityValue(e,'duration')}></input>
                          <label className="custom-control-label" for="custom">Custom</label>
                      </div>
                    
                </FormGroup>
                </Col>
                }
            

                {
                  userSegmentParam.custom && (userSegmentParam.login || userSegmentParam.signup)  &&
                 
                            <FormGroup>
                              <Col md={4}> 
                                  <Label htmlFor="from_date"> From Date</Label>
                                  <Input type="date" id="from_date" name="from_date" placeholder="Duration From" onChange={(e) => this.handleFieldVal(e)} required />
                                  </Col>
                              <Col md={4}>
                                  <Label htmlFor="to_date">To Date</Label>
                                  <Input type="date" id="to_date" name="to_date" placeholder="Duration From"  onChange={(e) => this.handleFieldVal(e)} required />
                                  </Col> 
                            </FormGroup>
                                
                }
                <Col lg={12}>
                <Row className="get button mB20 mT20">
                <div className="getresultbtn">
                <Button outline color="danger" onClick ={()=>this.getFilterResultTest()}>Get Results</Button>
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
                <Col lg={12}>
                <Row className="select preview promocode-select">  
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

                  <label  onClick={()=>this.toggleRecentCModal()} className="preview">Preview</label>
                  </Row>
                  }
                 
                  </Row>
                  </Col>
                  <Col lg={6}></Col>

                  <Col md={12} className="emailcheckrow">
                <FormGroup>
                  
                        <div className="custom-control custom-checkbox custom-control-inline">
                          <Input type="checkbox" id="email" className="custom-control-input" onChange={this.handleNotificationType} checked={notificationData.email} value={notificationData.email}></Input>
                          <label className="custom-control-label" for="email">Email</label>
                        </div>
                    
                        <div className="custom-control custom-checkbox custom-control-inline">
                          <Input type="checkbox" id="message" className="custom-control-input" onChange={this.handleNotificationType}  
                          checked={notificationData.message}
                          value={notificationData.message}></Input>
                          <label className="custom-control-label" for="message">Message</label>
                        </div>
                    
                        <div className="custom-control custom-checkbox custom-control-inline">
                          <Input type="checkbox" id="notification" className="custom-control-input" onChange={this.handleNotificationType} 
                          checked={notificationData.notification}  
                          value={notificationData.notification}></Input>
                          <label className="custom-control-label" for="notification">Notification</label>
                        </div> 
               
                </FormGroup>
                </Col>

                  <Row className="align-items-left">
                  <Col lg={12}>
                    <Col md={12} className="sendbtn">
                      <Button outline color="danger" disabled={!((notificationData.notification==true || notificationData.email==true || notificationData.message==true)&& notificationData.email_template_id) } onClick ={()=>this.notifyBySelection()}>Send</Button>
                    </Col>
                    </Col>
                  </Row>
              </CardBody>
            </Card>
            }
            
          </Col>
        </Row>
        <Modal isOpen={this.state.communication_review_modal} toggle={this.toggleRecentCModal} className={this.props.className}>
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

                                                         
                                                               <div dangerouslySetInnerHTML={{__html: this.state.previewObj.email_body}}>
                                                               
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
                                      <ModalFooter>
                                       
                                      </ModalFooter>
                                     
                                  </Modal>
      </div>
    );
  }
}
export default UserSegmentation;
