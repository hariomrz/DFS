import React, { Component, Fragment } from "react";
import { Row, Col, Button, Input, Modal, ModalBody, ModalHeader, ModalFooter } from 'reactstrap';
import _ from 'lodash';
import * as NC from '../../helper/NetworkingConstants';
import WSManager from "../../helper/WSManager";
import { _times, _Map, _isEmpty } from "../../helper/HelperFunction";
import { MomentDateComponent } from "../../components/CustomComponent";
import Images from "../../components/images";
import { LF_MSG_CANCEL_REQ, LF_CANCEL_GAME_TITLE, LF_CANCEL_CONTEST_TITLE } from "../../helper/Message";
import { notify } from 'react-notify-toast';
import HF from '../../helper/HelperFunction';


class LF_OverDetails extends Component {
    constructor(props) {
        super(props)
        this.state = {
            RewardType: '1',
            fixtureDetail: [],
            league_id: (this.props.league_id) ? this.props.league_id : this.props.match.params.league_id,
            season_game_uid: (this.props.match.params.season_game_uid) ? this.props.match.params.season_game_uid : '',
            BackTab: (this.props.match.params.tab) ? this.props.match.params.tab : 2,
            overLoad: true,
            overData: [],
            currentTab:0,
            upcomingList:[],
            CancelPosting: true,
            isCheck:false,
            ScoreData:{
                'home_team_score':'',
                'home_wickets':'',
                'home_overs':'',
                'away_team_score':'',
                'away_overs':'',
                'away_wickets':''

            }

        }
    }


    componentDidMount = () => {
      

        this.GetFixtureDetail()
        this._getInningOvers()
        let status =localStorage.getItem('statusUpdate')
        if(status != undefined && status != null && status == 1)
        {
            localStorage.setItem('statusUpdate', 0)
            let item =  JSON.parse(localStorage.getItem('nextOver'))
            this.props.history.push({ pathname: '/livefantasy/update-score/' + item.league_id + '/' + item.season_game_uid + '/' + this.state.BackTab + '/' + item.inning + '/' + item.collection_id + '/' + item.overs })
            
        }
    }

    updateMatchStatus =()=>{
        let { league_id, season_game_uid } = this.state
        let param = {
            "league_id": league_id,
            "season_game_uid": season_game_uid,
            "is_live_score":this.state.isCheck ? 1 : 0
        }
        this.setState({ posting: true });

        WSManager.Rest(NC.baseURL + NC.LF_UPDATE_MATCH_SCORE_STATUS, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                notify.show(responseJson.message, "success", 2000);
                this.GetFixtureDetail()
               
            }
            else if (responseJson.response_code == NC.sessionExpireCode) {
                WSManager.logout();
                this.props.history.push('/login');
            }
        })  
    }
    updateMatchScore =()=>{
        let { league_id, season_game_uid } = this.state
        let param = {
            "league_id": league_id,
            "season_game_uid": season_game_uid,
            "score_data":this.state.ScoreData
        }
        this.setState({ posting: true });

        WSManager.Rest(NC.baseURL + NC.LF_UPDATE_MATCH_SCORE, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.GetFixtureDetail()
                notify.show(responseJson.message, "success", 2000);

            }
            else if (responseJson.response_code == NC.sessionExpireCode) {
                WSManager.logout();
                this.props.history.push('/login');
            }
        })  
    }

    GetFixtureDetail = () => {
        let { league_id, season_game_uid } = this.state
        let param = {
            "league_id": league_id,
            "season_game_uid": season_game_uid,
        }
        this.setState({ posting: true });

        WSManager.Rest(NC.baseURL + NC.LF_GET_SEASON_TO_PUBLISH, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({
                    posting: false,
                    fixtureDetail: responseJson.data,
                    isCheck :responseJson.data.is_live_score == 1 ?  true: false
                });
                if(responseJson.data.score_data != null && responseJson.data.score_data != undefined ){
                    let score_data = JSON.parse(responseJson.data.score_data)
                    this.setState({
                        ScoreData:score_data
                    });
                }
            }
            else if (responseJson.response_code == NC.sessionExpireCode) {
                WSManager.logout();
                this.props.history.push('/login');
            }
        })
    }

    _getInningOvers = () => {
        this.setState({ overLoad: true });
        let { league_id, season_game_uid } = this.state
        let param = {
            "league_id": league_id,
            "season_game_uid": season_game_uid,
        }
        WSManager.Rest(NC.baseURL + NC.LF_GET_FIXTURE_OVERS, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({
                    overLoad: false,
                    overData: responseJson.data
                },()=>{
                    let fillterList=[];
                    let upcoming=[];

                    fillterList = !_isEmpty(this.state.overData) && this.state.overData.filter((items, index, array) => {
                        //return items.status == ( || 1);
                        if(items.status == this.state.currentTab){
                            return items.status
                        }
                        else if(items.status == 1 || items.status == 0 ){
                            return items.status;
           
                        }
                    }); 
                    upcoming = !_isEmpty(this.state.overData) && this.state.overData.filter((items, index, array) => {
                        //return items.status == ( || 1);
                         if(items.status == 1 || items.status == 0 ){
                            return items.status;
           
                        }
                    }); 
                    this.setState({fillterListData:fillterList,upcomingList:upcoming})
                });
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    overDetailCard = (item, idx) => {
    
        return (
            <div className="odCard" key={idx}>
                <div className="od-inn-d-box">
                    {
                        item.status == "0" &&
                        <div className="od-upcmng">Upcoming</div>
                    }
                    {
                        item.status == "1" &&
                        <div className="od-live">Live</div>
                    }
                    {
                        item.status == "2" &&
                        <div className="od-comp">Completed</div>
                    }
                    {
                        item.status == "3" &&
                        <div className="od-cancel">Cancelled</div>
                    }
                    <div
                        className="od-inn-over"
                        onClick={() => this.redirectToFxContest(item)}
                    >Inning {item.inning} Over {item.overs}</div>
                    <div className="od-con-usr">{item.total_contest} Contests | {item.total_user_joined} Users</div>
                </div>
                <ul className="fx-action-list">
                    {
                       <li className="fx-action-item">
                       <i
                           title="Published"
                           className="icon-fixture-contest"
                           onClick={() => this.redirectToFxContest(item)}
                       ></i>
                   </li>
                    }
                   
                    {
                        this.state.BackTab == '2' &&
                        <li className="fx-action-item">
                            <i
                                title="Contest Template"
                                className="icon-template"
                                onClick={() => this.redirectToContestTemplate(item)}
                            ></i>
                        </li>
                    }
                    {
                        (item.status != '2' && item.status != '3') &&
                        <li className="fx-action-item">
                            <i
                                title="Cancel"
                                className="icon-cancel"
                                onClick={() => this.cancelMatchModalToggle(item.collection_id, 2, idx, idx)}
                            ></i>
                        </li>
                    }
                    { <li className="fx-action-item">
                        <i
                            title="Update Score"
                            className={`icon-content`}
                            onClick={() => this.props.history.push({ pathname: '/livefantasy/update-score/' + item.league_id + '/' + item.season_game_uid + '/' + this.state.BackTab + '/' + item.inning + '/' + item.collection_id + '/' + item.overs })}
                            // onClick={() => this.props.history.push({ pathname: '/livefantasy/update-score/' + item.league_id + '/' + item.season_game_uid + '/' + this.state.BackTab + '/' + item.inning + '/' + item.collection_id + '/' + item.overs })}
                        ></i>
                    </li>}
                </ul>
            </div>
        )
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
            DeleteIndex: idx,
        });
    }

    cancelMatchModal = () => {
        let { CancelPosting, API_FLAG } = this.state
        return (
            <div>
                <Modal
                    isOpen={this.state.CancelModalIsOpen}
                    toggle={this.cancelMatchModalToggle}
                    className="cancel-match-modal"
                >
                    <ModalHeader>{API_FLAG == 1 ? LF_CANCEL_GAME_TITLE : LF_CANCEL_CONTEST_TITLE}</ModalHeader>
                    <ModalBody>
                        <div className="confirm-msg">{LF_MSG_CANCEL_REQ}</div>
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
        let { API_FLAG, season_game_uid, CONTEST_U_ID, CancelReason, DeleteIndex, overData, league_id } = this.state
        this.setState({ CancelPosting: false });
        if(CancelReason== '' || CancelReason == undefined){
            notify.show("Please enter description first");
            return
        }
        let param = {
            cancel_reason: CancelReason,
            "league_id": league_id
        };

        let API_URL = ""
        if (API_FLAG == 1) {
            param.season_game_uid = season_game_uid
            API_URL = NC.LF_CANCEL_FIXTURE
        } else {
            param.collection_id = CONTEST_U_ID
            //param.cancel_reason = CONTEST_U_ID

            API_URL = NC.LF_CANCEL_FIXTURE_OVER
        }

        WSManager.Rest(NC.baseURL + API_URL, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {



                this.setState({
                    CancelPosting: false,
                    CancelReason: ''
                })
                    if(API_FLAG != 1){
                    this._getInningOvers();

                    }
                if (DeleteIndex >= "0") {
                    // this._getInningOvers();
                    let tvd = overData
                    tvd[DeleteIndex].status = "3"
                    this.setState({ overData: tvd })
                } else {
                    this.props.history.push({ pathname: '/livefantasy/fixture' })
                }
                notify.show(responseJson.message, "success", 5000);

            }
            else if (responseJson.response_code == NC.sessionExpireCode) {
                notify.show(responseJson.message, "error", 5000);
            }
            this.cancelMatchModalToggle('', '')
        })
    }

    redirectToFxContest = (item) => {
        let { league_id, season_game_uid, BackTab, collection_id } = this.state
        this.props.history.push(
            {
                pathname: '/livefantasy/fixturecontest/' + league_id + '/' + season_game_uid + '/' + BackTab + '/' + item.collection_id,
                // state: { overitem: item }
            }
        )
    }

    redirectToOverSetup = () => {
        localStorage.setItem('new_over_setup', 1);
        let { league_id, season_game_uid } = this.state
        this.props.history.push({ pathname: '/livefantasy/oversetup/' + league_id + '/' + season_game_uid })
    }

    redirectToContestTemplate = (item) => {
        let { league_id, season_game_uid } = this.state
        this.props.history.push({
            pathname: '/livefantasy/createtemplatecontest/' + league_id + '/' + season_game_uid + '/' + item.collection_id + '/2',
        })
        // this.props.history.push({
        //     pathname: '/livefantasy/createtemplatecontest/' + league_id + '/' + season_game_uid + '/2',
        // })
    }
    selectTab =(status)=>{
        let overDataNew = this.state.overData;
        let fillterList=[];
        if(status != this.state.currentTab){
            this.setState({currentTab:status})
            fillterList = overDataNew.filter((items, index, array) => {
                return (status== 2 ? items.status == 2 || items.status == 3 : status== 0 && items.status == 0 || items.status == 1);
              
            }); 
            this.setState({fillterListData:fillterList})
        }

    }
    onHandleChangeCheck = (e)=>{
        if(!this.state.isCheck){
            this.setState({isCheck:true},()=>{
                this.updateMatchStatus()
            })

        }
        else{
            this.setState({isCheck:false},()=>{
                this.updateMatchStatus()
            })
 
        }

    }
    handleInputChangeScore =(e)=>{
        let name = e.target.name
        let value = e.target.value
        let data = this.state.ScoreData;
        let reg = /^[0-9]+$/
        var regexp = /^[0-9]*(\.[0-9]{0,1})?$/;


        if(name == 'home_wickets' && value && value.match(reg) && value <=10 ){
             data['home_wickets']= value
             this.setState({ ScoreData: data }) 
        }
        else if(name == 'home_wickets' && value == ''){
             data['home_wickets']= ''
             this.setState({ ScoreData: data }) 
        }
     
        if(name == 'home_team_score' &&  value && value.match(reg)){
            data['home_team_score']= value
            this.setState({ ScoreData: data }) 
        }
        else if(name == 'home_team_score' && value == ''){
            data['home_team_score']= ''
            this.setState({ ScoreData: data }) 
        }


         if(name == 'home_overs' &&  value && value.match(regexp)){
            data['home_overs']= value
            this.setState({ ScoreData: data }) 
         }
         else if(name == 'home_overs' &&  value == ''){
            data['home_overs']= ''
            this.setState({ ScoreData: data }) 
         }
       
         if(name == 'away_team_score' &&  value && value.match(reg)){
            data['away_team_score']= value
            this.setState({ ScoreData: data }) 
        }
        else if(name == 'away_team_score' && value == ''){
            data['away_team_score']= ''
            this.setState({ ScoreData: data }) 
        }
        if(name == 'away_overs' &&  value && value.match(regexp)){
            data['away_overs']= value
            this.setState({ ScoreData: data }) 
         }
         else if(name == 'away_overs' &&  value == ''){
            data['away_overs']= ''
            this.setState({ ScoreData: data }) 
         }

        if(name == 'away_wickets' && value && value.match(reg) && value <=10 ){
            data['away_wickets']= value
            this.setState({ ScoreData: data }) 
       }
       else if(name == 'away_wickets' && value == ''){
            data['away_wickets']= ''
            this.setState({ ScoreData: data }) 
       }

    }
    
    saveScore = (e)=>{
        this.updateMatchScore()
      

    }
    render() {
        let {ScoreData, upcomingList, fillterListData,currentTab,fixtureDetail, BackTab, CancelModalIsOpen, overData } = this.state
        let isUPC = currentTab == 0 ? true: false
        let filteredListItems = fillterListData;
        console.log('fixtureDetailfixtureDetailfixtureDetail', fixtureDetail)
        return (
            <div className="fkOverDetails">
                {CancelModalIsOpen && this.cancelMatchModal()}
                <Row className="animate-left">
                    {!_.isEmpty(fixtureDetail) &&
                        <Col lg={12}>
                            <div className='ds-f'>
                                <div className="common-fixture float-left max-h-100">
                                    <img src={fixtureDetail.home_flag ? NC.S3 + NC.FLAG + fixtureDetail.home_flag : Images.DEFAULT_CIRCLE} className="com-fixture-flag float-left" alt="" />
                                    <img src={fixtureDetail.home_flag ? NC.S3 + NC.FLAG + fixtureDetail.away_flag : Images.DEFAULT_CIRCLE} className="com-fixture-flag float-right" alt="" />
                                    <div className="com-fixture-container">
                                        <div className="com-fixture-name">{(fixtureDetail.home) ? fixtureDetail.home : 'TBA'} VS {(fixtureDetail.away) ? fixtureDetail.away : 'TBA'}</div>

                                        <div className="com-fixture-time">
                                            {/* <MomentDateComponent data={{ date: fixtureDetail.season_scheduled_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                                            {HF.getFormatedDateTime(fixtureDetail.season_scheduled_date, "D-MMM-YYYY hh:mm A ")}
                                        </div>
                                        <div className="com-fixture-title">{fixtureDetail.league_abbr}</div>
                                    </div>
                                </div>
                                {
                                    (this.state.BackTab !=2 && this.state.currentTab != 2 && !_isEmpty(this.state.overData)) &&
                                    <div className='dash-view'>
                                    <div>
                                        <div className="common-cus-checkbox">
                                            <label className="com-chekbox-container check-holder">
                                                <span className="opt-text">Enable Live Score

                                                </span>
                                                <input
                                                    type="checkbox"
                                                    name="SetSponsor"
                                                    checked={this.state.isCheck}
                                                    onChange={(e) => this.onHandleChangeCheck(e)}
                                                />
                                                <span className="com-chekbox-checkmark"></span>
                                            </label>
                                        </div>
                                        <div className='hr-line' />
                                        <div>
                                            <Row>
                                                <Col>
                                                    <div>
                                                        <div>
                                                            <div className="com-fixture-name text-3">{(fixtureDetail.home) ? fixtureDetail.home : 'TBA'}</div>
                                                        </div>
                                                        <div className='ds-f mt10'>
                                                            <div className='ds-g'>
                                                                <span className='text-2'>
                                                                    Run
                                                                </span>
                                                                <Input type='text'
                                                                    maxLength={3}
                                                                    className='score-text'
                                                                    placeholder="Score"
                                                                    name='home_team_score'
                                                                    value={ScoreData.home_team_score}
                                                                    onChange={(e) => this.handleInputChangeScore(e)}
                                                                />
                                                            </div>
                                                            <div className='ds-g ml10'>
                                                                <span className='text-2'>
                                                                    Wickets
                                                                </span>
                                                                <Input 
                                                                    type='text'
                                                                    maxLength={2}
                                                                    className='score-text'
                                                                    placeholder="Wicket"
                                                                    name='home_wickets'
                                                                    value={ScoreData.home_wickets}
                                                                    onChange={(e) => this.handleInputChangeScore(e)}
                                                                />
                                                            </div>
                                                            <div className='ds-g ml10'>
                                                                <span className='text-2'>
                                                                    Over
                                                                </span>
                                                                <Input type='text'
                                                                    className='score-text'
                                                                    placeholder="Over"
                                                                    name='home_overs'
                                                                    maxLength={5}
                                                                    value={ScoreData.home_overs}
                                                                    onChange={(e) => this.handleInputChangeScore(e)}
                                                                />
                                                            </div>
                                                        </div>

                                                    </div>
                                                </Col>
                                                <Col>
                                                    <div>
                                                        <div>
                                                            <div className="com-fixture-name text-3">{(fixtureDetail.away) ? fixtureDetail.away : 'TBA'}</div>
                                                        </div>
                                                        <div className='ds-f mt10'>
                                                            <div className='ds-g'>
                                                                <span className='text-2'>
                                                                    Run
                                                                </span>
                                                                <Input 
                                                                    type='text'
                                                                    maxLength={3}
                                                                    className='score-text'
                                                                    placeholder="Score"
                                                                    name='away_team_score'
                                                                    value={ScoreData.away_team_score}
                                                                    onChange={(e) => this.handleInputChangeScore(e)}
                                                                />
                                                            </div>
                                                            <div className='ds-g ml10'>
                                                                <span className='text-2'>
                                                                    Wickets
                                                                </span>
                                                                <Input type='text'
                                                                    maxLength={2}
                                                                    className='score-text'
                                                                    placeholder="Wickets"
                                                                    name='away_wickets'
                                                                    value={ScoreData.away_wickets}
                                                                    onChange={(e) => this.handleInputChangeScore(e)}
                                                                />
                                                            </div>
                                                            <div className='ds-g ml10'>
                                                                <span className='text-2'>
                                                                    Over
                                                                </span>
                                                                <Input type='text'
                                                                    className='score-text'
                                                                    placeholder="Overs"
                                                                    name='away_overs'
                                                                    maxLength={5}
                                                                    value={ScoreData.away_overs}
                                                                    onChange={(e) => this.handleInputChangeScore(e)}
                                                                />
                                                            </div>
                                                        </div>

                                                    </div>
                                                </Col>
                                                <Col>
                                                    <Button onClick={()=>this.saveScore()}
                                                        className="btn-secondary-outline mr-4 mt52"
                                                    >Save</Button>
                                                </Col>
                                            </Row>

                                        </div>
                                    </div>
                                </div>
                                }
                                
                            </div>
                            

                                                      
                        </Col>
                    }
                    {
                        <Col lg={12}>
                            {
                                 
                                 <div style={{ float: 'left', marginTop: 15 }} className="odHeadBrn">
                                {
                                    // BackTab == "2" &&
                                    !_isEmpty(upcomingList) &&
                                    <Button
                                        className="btn-secondary-outline mr-4"
                                        onClick={() => this.cancelMatchModalToggle('', 1, '-1', '-1')}
                                    >Cancel Fixture</Button>
                                }
                                <Button
                                    className="btn-secondary"
                                    onClick={() => this.redirectToOverSetup()}>
                                    New Over Setup
                                </Button>
                            </div>
                            }
                            

                        </Col>

                    }
                </Row>
                <Row>
                    <Col style={{ marginTop:20, background:'#ffffff',height:40,borderRadius:5}} >
                        <div onClick={()=>this.selectTab(0)} style={{ height:40, display:"flex",justifyContent:'flex-start',flexDirection:"row"}}  >
                            <div style={{cursor:"pointer", height:40, display: "flex", justifyContent: 'space-between', flexDirection: 'column' }} >
                                <div style={{fontSize:15, marginTop:10,fontWeight:'bold', color: isUPC ? '#F8436E': '#9398A0',textAlign:"center" }} >Upcoming</div>
                                <div style={{width: 100, height:2,background: isUPC ? '#F8436E' :'#fff' }} ></div>

                            </div>
                            {(this.state.BackTab == 3 || this.state.BackTab == 1) &&
                                <div onClick={() => this.selectTab(2)} style={{ cursor: "pointer", marginLeft: 60, height: 40, display: "flex", justifyContent: 'space-between', flexDirection: 'column' }} >
                                    <div style={{ fontSize: 15, marginTop: 10, fontWeight: 'bold', color: !isUPC ? '#F8436E' : '#9398A0', textAlign: "center" }} >Completed</div>
                                    <div style={{ width: 100, height: 2, background: !isUPC ? '#F8436E' : '#fff' }} ></div>

                                </div>
                            }
                           
                        </div>

                       
                    </Col>
                </Row>
                <Row>
                    <Col className="heading-box">
                        <div className="contest-tempalte-wrapper">
                            <h2 className="h2-cls">Overs</h2>
                        </div>

                        <div className="fixture-contest">
                            <label className="back-to-fixtures" onClick={() => this.props.history.push('/livefantasy/fixture?tab=' + BackTab)}> {'<<'} Back to Fixtures</label>
                        </div>
                    </Col>
                </Row>
                <div className="border-bottom mb-4"></div>
                <Row>
                    {
                        _Map(filteredListItems, (item, idx) => {
                            return (
                                <Col md={3} key={idx}>
                                    {this.overDetailCard(item, idx)}
                                </Col>
                            )
                        })
                    }
                </Row>
            </div>
        )
    }
}
export default LF_OverDetails

