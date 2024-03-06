import React, { Component, Fragment } from 'react'
import { Row, Col, Button, Table } from 'reactstrap'
import * as NC from "../../helper/NetworkingConstants";
import Images from "../../components/images";
import { MomentDateComponent } from "../../components/CustomComponent";
import HF, { _isEmpty, _isNull, _isUndefined, _Map } from '../../helper/HelperFunction'
import SelectDropdown from "../../components/SelectDropdown";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import moment from 'moment';
import { error } from 'jquery';

class LF_UpdateScore extends Component {
    constructor(props) {
        super(props)
        this.state = {
            league_id: (this.props.league_id) ? this.props.league_id : this.props.match.params.league_id,
            season_game_uid: (this.props.match.params.season_game_uid) ? this.props.match.params.season_game_uid : '',
            P_INNING: (this.props.match.params.inning) ? this.props.match.params.inning : '',
            P_OVERS: (this.props.match.params.over) ? this.props.match.params.over : '',
            tab: (this.props.match.params.tab) ? this.props.match.params.tab : '',
            c_id:(this.props.match.params.c_id) ? this.props.match.params.c_id : '',
            fxDetail: [],
            SelectedScore: "",
            UpdateBtnLoad: false,
            saveLoad: false,
            masterOdds: [],
            scoreDD: [],
            undoLoad: true,
            MarketOdds: [],
            ExtraDD: [],
            PlayersBat: [],
            PlayersBall: [],
            playBtnEnable : false,
            showButtonStatus : false,
            collDetails : false,
            BatList: [{ value: "", label: "" }
                , { value: "", label: "" }
            ],
            BallList: [{ value: "", label: "" },

            ],
            batsManStatus: 0,
            ballsManStatus:0,
            btnStartOverTimer:true,
            selectedTime:'',
        }
    }

    componentDidMount() {
        let blank={}
        localStorage.setItem('statusUpdate', 0)
        localStorage.setItem('nextOver', JSON.stringify(blank))

        this.getMatchOverDetails()
        this.GetFixtureDetail();
        this._getMarketOdds();
        this._getScoringMaster();
    }

    getMatchOverDetails = () => {
        //this.setState({ undoLoad: false })
        let { c_id } = this.state
        let param = {
            "collection_id": c_id,
        }
            WSManager.Rest(NC.baseURL + NC.LF_GET_MATCH_OVER_STATUS, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({collDetails:responseJson.data,selectedTime:responseJson.data.over_time
                })
            }
        })
    }
    startOverTimer = () => {
        //this.setState({ undoLoad: false })
        let { c_id } = this.state
        let param = {
            "collection_id": c_id,
        }
            WSManager.Rest(NC.baseURL + NC.LF_START_OVER_TIMER, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({btnStartOverTimer:false})
            }
        })
    }
    updateOverStatus = (status) => {
        //this.setState({ undoLoad: false })
        let { c_id } = this.state
        let param = {
            "collection_id": c_id,
            "status":status,
            "over_time":this.state.selectedTime
        }
            WSManager.Rest(NC.baseURL + NC.LF_UPDATE_OVER_STATUS, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({collDetails:responseJson.data},()=>{
                    if(status == 2){
                        let item = responseJson.data.next_over
                        if(item != null && item != undefined && !_isEmpty(item)){
                     
                        localStorage.setItem('statusUpdate', 1)
                        localStorage.setItem('nextOver', JSON.stringify(item))
                        this._redirectToOver()
                        }

                    }
                })
            }
        })
    }


    // nextOver =()=>{
    //     let url = window.location.href;
    //     url = url.split("/" + this.props.match.params.inning)[0] + '/' + this.props.match.params.c_id[0] + '/' + this.props.match.params.over[0];
    //     window.history.replaceState("", "", url + "/" + 1 +"/" +1776 + "/" + 4 );
     
    //     window.location.reload()
    // }

    GetFixtureDetail = () => {
        let { league_id, season_game_uid } = this.state
        let param = {
            "league_id": league_id,
            "season_game_uid": season_game_uid,
        }

        WSManager.Rest(NC.baseURL + NC.LF_GET_SEASON_TO_PUBLISH, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                let d = responseJson.data ? responseJson.data : [];
                this.setState({
                    fxDetail: d,
                });
            }
            else if (responseJson.response_code == NC.sessionExpireCode) {
                WSManager.logout();
                this.props.history.push('/login');
            }
        })
    }

    _getMarketOdds = () => {
        let { season_game_uid, league_id, P_INNING, P_OVERS } = this.state
        let param = {
            "season_game_uid": season_game_uid,
            "league_id": league_id,
            "inn_over": P_INNING + '_' + P_OVERS //"1_8"
        }

        WSManager.Rest(NC.baseURL + NC.LF_GET_MARKETS_ODDS, param).then((responseJson) => {

            if (responseJson.response_code === NC.successCode) {
                let m_odd = responseJson.data ? responseJson.data.market_odds : [];
                this.setState({
                    MarketOdds: m_odd,
                });
            }
            else if (responseJson.response_code == NC.sessionExpireCode) {
                WSManager.logout();
                this.props.history.push('/login');
            }
        })
    }

    _getScoringMaster = () => {
        let { season_game_uid } = this.state
        let param = {
            "season_game_uid": season_game_uid,
        }

        WSManager.Rest(NC.baseURL + NC.LF_MANUAL_SCORING_MASTER, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                let m_odd = responseJson.data ? responseJson.data.master_odds : [];
                let extra = responseJson.data ? responseJson.data.extra : [];
                let others = responseJson.data ? responseJson.data.others : [];
                let players = responseJson.data ? responseJson.data.players : [];

                let sdd = []
                _Map(m_odd, function (itm) {
                    sdd.push({
                        value: itm.odds_id,
                        label: itm.name,
                        extra_score_value: itm.value,
                    });
                })

                let bat_ply = []
                _Map(players, function (itm) {
                    // if (itm.team_uid == itm.batting_team_uid) {
                    //     bat_ply.push({
                    //         value: itm.player_team_id,
                    //         label: itm.name
                    //     });
                    // }
                    bat_ply.push({
                        value: itm.player_team_id,
                        label: itm.name
                    });
                })

                let ball_ply = []
                _Map(players, function (itm) {
                    // if (itm.team_uid != itm.batting_team_uid) {
                    //     ball_ply.push({
                    //         value: itm.player_team_id,
                    //         label: itm.name
                    //     });
                    // }
                    ball_ply.push({
                        value: itm.player_team_id,
                        label: itm.name
                    });
                })

                let ex_dd = [{
                    value: '',
                    label: 'Select',
                    extra_score_id: '',
                }]
               
                
                let ot_dd = [{
                    value: '',
                    label: 'Select',
                    extra_score_id: '',
                }]
                _Map(others, function (itm) {
                    ot_dd.push({
                        value: itm.id.toString(),
                        extra_score_id: itm.value.toString(),
                        label: itm.name,
                    });
                })
                _Map(extra, function (itm) {
                    ex_dd.push({
                        // value: itm.value.toString(),
                        // extra_score_id: itm.id.toString(),
                        value: itm.id.toString(),
                        extra_score_id: itm.value.toString(),
                        scores:itm.value.toString(),
                        label: itm.name,
                    });
                })
                let timer =[]
                const n = 30;

                [...Array(n)].map((elementInArray, index) => (
                    timer.push({value:index+1,label:index+1})

                )
                )
                this.setState({
                    masterOdds: m_odd,
                    scoreDD: sdd,
                    ExtraDD: ex_dd,
                    OtherDD: ot_dd,
                    PlayersBat: bat_ply,
                    PlayersBall: ball_ply,
                    timerOption:timer
                });
            }
            else if (responseJson.response_code == NC.sessionExpireCode) {
                WSManager.logout();
                this.props.history.push('/login');
            }
        })
    }
    handleTimerChange =(value, name)=>{
       
        this.setState({ selectedTime: value.value })

    }
    handleSelectChangeUpper = (value, name) => {  
        let bat_ply = []
        let ball_ply = []
        let batList = this.state.BatList;
        let ballList = this.state.BallList;

        if(name == 'bat_player_id1'){
          
            if( batList[1].value != '' && batList[1].value == value.value || ballList[0].value == value.value ){
                notify.show("This Player is Already Selected",'error',1000)
                return;
            }
           
            batList[0]['value'] = value.value
            batList[0]['label'] = value.label
            this.setState({BatList:batList,batsManStatus:1},()=>{
                //alert(JSON.stringify(this.state.BatList))   
            })
        }
        else if(name == 'bat_player_id2'){
            if(batList[0].value != '' &&  batList[0].value == value.value || ballList[0].value == value.value ){
                notify.show("This Player is Already Selected",'error',1000)
                return;
            }
            
            batList[1]['value'] = value.value
            batList[1]['label'] = value.label
            this.setState({BatList:batList,batsManStatus:2},()=>{
                // if(batList[0].value == ''){
                //     this.setState({batsManStatus:2})
                // }
                // else{
                //     this.setState({batsManStatus:3})
  
                // }
                //alert(JSON.stringify(this.state.BatList))   
    
            })  
        }
        else if(name == 'bow_player_id'){
            if(batList[0].value != '' &&  batList[0].value == value.value || batList[1].value != '' && batList[1].value == value.value ){
                notify.show("This Player is Already Selected",'error',1000)
                return;
            }
            
            ballList[0]['value'] = value.value
            ballList[0]['label'] = value.label
            this.setState({BallList:ballList,ballsManStatus:1},()=>{
    
            })
            
        }
      

    } 


    handleSelectChange = (value, name, m_idx) => {    
       
  
        let tempObj = this.state.MarketOdds
        if (name == 'result') {
            tempObj[m_idx]['market_name'] = this.state.scoreDD[parseInt(value.value) - 1]['label']
            tempObj[m_idx]['extra_score_value'] = value.extra_score_value
            tempObj[m_idx]['extra_score_id'] = ''
        }
        if (name == 'score') {
            console.log("==value===",value);
            console.log("==name===",name);
            console.log("==m_idx===",m_idx);
            tempObj[m_idx]['extra_score_id'] = value.value
        }
        if(name == 'bat_player_id'){
            if(this.state.batsManStatus == 1) {
                this.setState({ batsManStatus: 2 })
    
            }
            else{
                this.setState({ batsManStatus: 1 })
     
            } 
        }
        if(name == 'bow_player_id'){
            this.setState({ ballsManStatus: 1 })

        }
        tempObj[m_idx][name] = tempObj[m_idx].result == '7' || tempObj[m_idx].result == '8' ? value.extra_score_id ? value.extra_score_id : value.value :value.value

        console.log("tempObj",JSON.stringify(tempObj[m_idx]));

        if (!_isNull(value)) {
            this.setState({ MarketOdds: tempObj })
        }
    }

    handleInputChange = (e, m_idx, c_idx) => {
        let name = e.target.name;
        let value = e.target.value;
        console.log("e.value.name",name)

        
        let reg = /^[0-9]*(\.[0-9]{0,2})?$/
        console.log("e.value.name",name)
        console.log("e.value.name",value)
        let tempObj = this.state.MarketOdds

        if(name && name == 'Dot'){
            if(value && value.match(reg)){
                tempObj[m_idx]['market_odds'][c_idx] = value
                this.setState({ MarketOdds: tempObj })
            }
            else if(name == 'Dot' && value == ''){
                tempObj[m_idx]['market_odds'][c_idx] = ''
                this.setState({ MarketOdds: tempObj })

            }
        }
        console.log("tempObjOdd",JSON.stringify(tempObj[m_idx]))
       
    }

    _saveBallScore = (m_idx) => {
        let { league_id, season_game_uid, MarketOdds } = this.state
        let pdata = !_isUndefined(MarketOdds[m_idx]) ? MarketOdds[m_idx] : []
        var error_flag = false;
        var error_msg = "";

        if (_isEmpty(pdata.result) || pdata.result == '0') {
            error_flag = true;
            error_msg = "Please select score"
        }
        else if (pdata.result == '7' && (_isEmpty(pdata.score))) {
            error_flag = true;
            error_msg = "Please select value"
        }

        if (error_flag) {
            notify.show(error_msg, "error", 3000);
            return false;
        }       
        
        let new_result = pdata.result;
        let new_score = pdata.score;
        let new_extra_score_id = pdata.extra_score_id;
        // if(pdata.result == '7' || pdata.result == '8'){
        if(pdata.result == '7'){
            //For extra or others
            new_result = pdata.result;
            // new_score = pdata.extra_score_id;
            // new_extra_score_id = pdata.score;
            new_score = pdata.score;
            new_extra_score_id = pdata.extra_score_id;
            if(pdata.extra_score_id == ""){
                notify.show('Please select value', "error", 3000);
                return false;
            }
        }
        else if (pdata.result == '8') {
            //For extra or others
            new_result = pdata.result;
            new_score = pdata.score;
            new_extra_score_id = pdata.extra_score_id;
        }
        else{
             new_result = pdata.result;
             new_score = pdata.extra_score_value;
             new_extra_score_id = 0;
        }
        let param = {
            "season_game_uid": season_game_uid,
            "league_id": league_id,
            "inn_over": pdata.inn_over,
            "market_id": pdata.market_id,
            "over_ball": pdata.over_ball,
            "market_name": pdata.market_name,
            // "result": pdata.result,
            // "score": pdata.score,
            // "extra_score_id": pdata.extra_score_id,
            "result": new_result,
            "score": new_score!="" && new_score!=undefined ? new_score : pdata.score,
            "extra_score_id": new_extra_score_id,
        }
        console.log("pdata",JSON.stringify(pdata))
        console.log("param",JSON.stringify(param))

        this.setState({ saveLoad: true })
        WSManager.Rest(NC.baseURL + NC.LF_UPDATE_BALL_RESULT, param).then((responseJson) => {

            if (responseJson.response_code === NC.successCode) {
                notify.show(responseJson.message, "success", 3000);

                this._getMarketOdds();
                this.setState({ saveLoad: false })
                localStorage.setItem('lastP', param);

            }
            else{
                this.setState({ saveLoad: false })

            }
        })
    }

    _undoBallScore = (status, m_idx) => {
        this.setState({ undoLoad: false })
        let { league_id, season_game_uid, MarketOdds } = this.state
        let pdata = !_isUndefined(MarketOdds[m_idx]) ? MarketOdds[m_idx] : []
        let param = {
            "season_game_uid": season_game_uid,
            "league_id": league_id,
            "inn_over": pdata.inn_over,
            "market_id": pdata.market_id,
            "over_ball": pdata.over_ball,
            "status": status
        }

        WSManager.Rest(NC.baseURL + NC.LF_CHANGE_BALL_STATUS, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this._getMarketOdds();
                notify.show(responseJson.message, "success", 3000);
                this.setState({ undoLoad: true, })
            }
        })
    }

    _redirectToOver = () => {
        let { league_id, season_game_uid, tab } = this.state
        this.props.history.push({ pathname: '/livefantasy/overdetails/' + league_id + '/' + season_game_uid + '/' + tab })
    }

    _updateScore = (m_idx) => {
        let { league_id, season_game_uid, MarketOdds } = this.state
        let pdata = !_isUndefined(MarketOdds[m_idx]) ? MarketOdds[m_idx] : []
        let bowler= this.state.BallList[0].value;
        let batsman = this.state.batsManStatus == 1 ? this.state.BatList[0].value : this.state.batsManStatus == 2 ? this.state.BatList[1].value : ''
        var error_flag = false;
        var error_msg = "";
        if (_isEmpty(batsman) || batsman == "") {
            error_flag = true;
            error_msg = "Please select Batsman"
        }
        else if (_isEmpty(bowler) || bowler == "") {
            error_flag = true;
            error_msg = "Please select Bowler"
        }
        else if (!_isEmpty(pdata.market_odds)) {
            for (const property in pdata.market_odds) {
                if (pdata.market_odds[property] === '' || pdata.market_odds[property] > 99999) {
                    error_flag = true;
                    error_msg = "Point scoring should be in the range of 0 to 99999"
                }
            }
        }

        if (error_flag) {
            notify.show(error_msg, "error", 3000);
            return false;
        }


        let param = {
            "league_id": league_id,
            "season_game_uid": season_game_uid,
            "market_id": pdata.market_id,
            "inn_over": pdata.inn_over,
            "over_ball": pdata.over_ball,
            "market_odds": pdata.market_odds,
            "bat_player_id": batsman,
            "bow_player_id": bowler,
        }
        console.log("param",JSON.stringify(param))

        this.setState({ UpdateBtnLoad: true })

        WSManager.Rest(NC.baseURL + NC.LF_UPDATE_SCORING_POINTS, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                var rd = responseJson.data ? responseJson.data : []
                notify.show(responseJson.message, "success", 3000);
                this.setState({ 
                    UpdateBtnLoad: false, 
                    playBtnEnable: true 
                })
            }
        })
    }

    _playOver = (m_idx) => {
        let { MarketOdds } = this.state
        let pdata = !_isUndefined(MarketOdds[m_idx]) ? MarketOdds[m_idx] : []
        let bowler= this.state.BallList[0].value;
        let batsman = this.state.batsManStatus == 1 ? this.state.BatList[0].value : this.state.batsManStatus == 2 ? this.state.BatList[1].value : ''

        var error_flag = false;
        var error_msg = "";
        if (_isEmpty(batsman) || batsman == "") {
            error_flag = true;
            error_msg = "Please select Batsman"
        }
        else if (_isEmpty(bowler) || bowler == "") {
            error_flag = true;
            error_msg = "Please select Bowler"
        }

        if (error_flag) {
            notify.show(error_msg, "error", 3000);
            return false;
        }else{
            this._undoBallScore("1", m_idx)
        }
    }
  getExactValue=(pPer)=>{
        //console.log("pPer",pPer)
        let num = pPer && pPer.toString(); //If it's not already a String
        if(num && num.includes('.'))
        {
            num = num.slice(0, (num.indexOf("."))+2); //With 3 exposing the hundredths place

        }
        //Number(num); //If you need it back as a Number
        if(num != undefined){
            //console.log("num",num)

            return num

        }
    }

    getMinutes = (dateTime) => {
        let scheduleDate = WSManager.getUtcToLocal(dateTime);
        let currentDate = HF.getFormatedDateTime(Date.now());
        var now = moment(currentDate); //todays date
        var end = moment(scheduleDate); // another date
        var duration = moment.duration(end.diff(now));
        var minutes = duration.asMinutes();
        var newstr = minutes.toString().replace('-', '');

        var flg = false
        // if (newstr > 3)
        if (newstr > 53)
            flg = true

        return flg
    }

    _decimalCount = (num, return_flg) => {
        let newStr = num.toString()
        let res = '';
        if (newStr.includes('.')) {
            var count = newStr.split('.')[1].length
            if (count > 1) {
                if (return_flg) {
                    res = newStr.substring(0, newStr.length - 1);
                } else {
                    res = newStr.charAt(newStr.length - 1);
                }
            }
        }
        return res;
    }

    render() {
        const globalthis = this
        const { ballsManStatus,batsManStatus,BatList,BallList,fxDetail, UpdateBtnLoad, masterOdds, scoreDD, ExtraDD, OtherDD, MarketOdds, P_INNING, P_OVERS, PlayersBat, PlayersBall, saveLoad, playBtnEnable ,timerOption,selectedTime} = this.state

        const Comm_Select_Props = {
            is_searchable: true,
            is_clearable: false,
            menu_is_open: false,
            place_holder: "Select",
        }
        const Bat_Props_one = {
            ...Comm_Select_Props,
            is_disabled: false,
            class_name: "us-ply-select",
            sel_options: PlayersBat,
            select_name: 'bat_player_id1',
            selected_value: BatList[0].value,
           modalCallback: (e, name) => this.handleSelectChangeUpper(e, name)
        }
        const Timer_Prop = {
            ...Comm_Select_Props,
            is_disabled:this.state.collDetails!=undefined && this.state.collDetails.status == 1 ? true : false,
            class_name: "us-ply-select",
            sel_options: timerOption,
            select_name: 'timer',
            selected_value: selectedTime,
           modalCallback: (e, name) => this.handleTimerChange(e,name)
        }
        const Bat_Props_two = {
            ...Comm_Select_Props,
            is_disabled: false,
            class_name: "us-ply-select",
            sel_options: PlayersBat,
            select_name: 'bat_player_id2',
            selected_value: BatList[1].value,
           modalCallback: (e, name) => this.handleSelectChangeUpper(e, name)
        }
        const Bowl_Props_top = {
            ...Comm_Select_Props,
            is_disabled: false,
            class_name: "us-ply-select",
            sel_options: PlayersBall,
            select_name: 'bow_player_id',
            selected_value: BallList[0].value,
           modalCallback: (e, name) => this.handleSelectChangeUpper(e, name)
        }

        return (
            <div className="lf-up-sc">
                <Row>
                    <Col md={12}>
                        <div>
                            <h2 className="h2-cls">Update Score</h2>
                        </div>
                    </Col>
                </Row>
                <hr />
                <Row>
                    {!_isEmpty(fxDetail) &&
                        <Col md={5}>
                            <div className="common-fixture float-left">
                                <img src={!_isUndefined(fxDetail.home_flag) ? NC.S3 + NC.FLAG + fxDetail.home_flag : Images.DEFAULT_CIRCLE} className="com-fixture-flag float-left" alt="" />
                                <img src={!_isUndefined(fxDetail.away_flag) ? NC.S3 + NC.FLAG + fxDetail.away_flag : Images.DEFAULT_CIRCLE} className="com-fixture-flag float-right" alt="" />
                                <div className="com-fixture-container">
                                    <div className="com-fixture-name">{(fxDetail.home) ? fxDetail.home : 'TBA'} VS {(fxDetail.away) ? fxDetail.away : 'TBA'}</div>

                                    <div className="com-fixture-time">
                                        {/* <MomentDateComponent data={{ date: fxDetail.season_scheduled_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                                        {HF.getFormatedDateTime(fxDetail.season_scheduled_date, "D-MMM-YYYY hh:mm A ")}
                                    </div>
                                    <div className="com-fixture-title">{fxDetail.league_abbr}</div>
                                </div>
                            </div>
                        </Col>
                        
                    }
                    {
                        <Col md={2}>
                            <div className="us-ply-sel-box">
                                <div className="us-sel-ply mr-4">
                                    <label htmlFor="">Select Batsman 1</label>
                                    <SelectDropdown SelectProps={Bat_Props_one} />
                                </div>
                           
                            </div>

                           
                        </Col>
                    }
                     {
                        <Col md={2}>
                            <div className="us-ply-sel-box">
                                <div className="us-sel-ply mr-4">
                                    <label htmlFor="">Select Batsman 2</label>
                                    <SelectDropdown SelectProps={Bat_Props_two} />
                                </div>
                           
                            </div>

                           
                        </Col>
                    }
                     {
                        <Col md={2}>
                            <div className="us-ply-sel-box">
                              
                                <div className="us-sel-ply">
                                    <label htmlFor="">Select Bowler</label>
                                    <SelectDropdown SelectProps={Bowl_Props_top  } />
                                </div>
                            </div>

                           
                        </Col>
                    }
                </Row>
                <Row className="mt-3">
                    <Col md={12}>
                        <Row>
                        <Col md={4}>
                        <h2 className="h2-cls">Innings {P_INNING} Over {P_OVERS}</h2>

                        </Col>
                        <Col md={4}>
                                {
                                    this.state.btnStartOverTimer && this.state.collDetails.status == 0  && this.state.collDetails.timer_date =="" &&
                                    <span  className="us-btn">
                                        <Button
                                            className="btn-secondary-outline"
                                            onClick={() => this.startOverTimer()}
                                        >
                                            {"Start Timer"}
                                        </Button>
                                    </span>
                                }
                                {
                                    (this.state.collDetails && this.state.collDetails.status!=undefined && this.state.collDetails.status == 0 || this.state.collDetails.status == 1) &&
                                    <span style={{marginLeft:10}} className="us-btn">
                                        <Button
                                            className="btn-secondary-outline"
                                            onClick={() => this.updateOverStatus(this.state.collDetails.status == 0 ? 1 : this.state.collDetails.status == 1 ? 2 : '' )}
                                        >
                                            {this.state.collDetails.status == 0 ? "Move To Live" : "Move To Completed"}
                                        </Button>
                                    </span>
                                }
                       
                        </Col>
                        {
                        <Col md={3}>
                            <div style={{marginTop:-15}}>
                                <div >
                                    <label htmlFor="">Select Timer</label>
                                    <SelectDropdown SelectProps={Timer_Prop} />
                                </div>
                           
                            </div>

                           
                        </Col>
                    }
                    
                       

                    
                        </Row>
                       
                    </Col>
                    {/* <Col md={3}>
                        <span className="us-btn">
                            <Button
                                disabled={saveLoad}
                                className="btn-secondary-outline"
                                onClick={() => alert("cxz")}
                            >
                                Save
                            </Button>
                        </span>
                    </Col> */}
                    
                </Row>
                <hr />
                <Row className="mt-3">
                    <Col md={12}>
                        <div className="us-setup">
                            {
                                _Map(MarketOdds, (m_item, m_idx) => {
                                    var midxdata = MarketOdds[m_idx] ? MarketOdds[m_idx] : {}
                                    var prevIdxMrStatus = ''
                                    if (m_idx != 0 && !_isUndefined(MarketOdds[m_idx - 1])) {
                                        prevIdxMrStatus = MarketOdds[m_idx - 1]['market_status']
                                    }
                                    var nextIdxMrStatus = ''
                                    if (!_isUndefined(MarketOdds[m_idx + 1])) {
                                        nextIdxMrStatus = MarketOdds[m_idx + 1]['market_status']
                                    }

                                    var lasBall = ''
                                    if (!_isEmpty(m_item.over_ball)) {
                                        lasBall = m_item.over_ball.split('.')[1]
                                    }

                                    const Score_Props = {
                                        ...Comm_Select_Props,
                                        is_disabled: midxdata['market_status'] == 'cls' ? true : false,
                                        class_name: "us-select",
                                        sel_options: scoreDD,
                                        select_name: 'result',
                                        selected_value: midxdata['result'],
                                        modalCallback: (e, name) => this.handleSelectChange(e, name, m_idx)
                                    }

                                    const Value_Props = {
                                        ...Comm_Select_Props,
                                        is_disabled: midxdata['market_status'] == 'cls' ? true : false,
                                        class_name: "us-select",
                                        sel_options: midxdata['result'] == '7' ? ExtraDD : midxdata['result'] == '8' ? OtherDD : [],
                                        select_name: 'score',
                                        selected_value: midxdata['extra_score_id'],
                                        modalCallback: (e, name) => this.handleSelectChange(e, name, m_idx)
                                    }
                                 

                                    const Bat_Props = {
                                        ...Comm_Select_Props,
                                        is_disabled: false,
                                        class_name: "us-ply-select",
                                        sel_options: BatList,
                                        select_name: 'bat_player_id',
                                        selected_value: batsManStatus == 1 ? BatList[0].value :  batsManStatus == 2 ? BatList[1].value:'' ,
                                        modalCallback: (e, name) => this.handleSelectChange(e, name, m_idx)
                                    }
                                    const Bowl_Props = {
                                        ...Comm_Select_Props,
                                        is_disabled: false,
                                        class_name: "us-ply-select",
                                        sel_options: BallList,
                                        select_name: 'bow_player_id',
                                        selected_value: BallList[0].value,
                                        modalCallback: (e, name) => this.handleSelectChange(e, name, m_idx)
                                    }

                                    var extraBall = this._decimalCount(m_item.over_ball, true);
                                    if (_isEmpty(extraBall)) {
                                        extraBall = m_item.over_ball;
                                    }
                                    var exStr = ''
                                    if (!_isEmpty(m_item.over_ball) && !_isEmpty(this._decimalCount(m_item.over_ball, false))) {
                                        exStr = '(Ex' + this._decimalCount(m_item.over_ball, false) + ')';
                                    }
                                    return (
                                        <Fragment key={m_idx}>
                                            <Fragment>
                                                <div className="us-play-box">
                                                    {
                                                        // (m_idx != 0 && midxdata['market_status'] != 'ctd') &&
                                                        <span className="us-over">Over {Math.max((parseFloat(extraBall)-1).toFixed(1))}<small>{exStr}</small></span>
                                                    }
                                                    {
                                                        (midxdata['market_status'] == 'stm' || midxdata['market_status'] == 'cls') &&
                                                        <Fragment>
                                                            <span className="us-sel-run">
                                                                <label htmlFor="">Select Score</label>
                                                                <SelectDropdown SelectProps={Score_Props} />
                                                            </span>
                                                            {
                                                                (midxdata['result'] == '7' || midxdata['result'] == '8') &&
                                                                <span className="us-sel-run">
                                                                    <label htmlFor="">Select Value</label>
                                                                    <SelectDropdown SelectProps={Value_Props} />
                                                                </span>
                                                            }
                                                            {
                                                                midxdata['market_status'] == 'stm' &&
                                                                <span className="us-btn">
                                                                    <Button
                                                                        disabled={saveLoad}
                                                                        className="btn-secondary-outline"
                                                                        onClick={() => this._saveBallScore(m_idx)}
                                                                    >
                                                                        Save
                                                                </Button>
                                                                </span>
                                                            }
                                                        </Fragment>
                                                    }

                                                    {
                                                        midxdata['market_status'] == 'cls' &&
                                                        <span
                                                            // className={`us-act ur-reset ${(nextIdxMrStatus == 'ctd' && !this.getMinutes(midxdata['updated_date'])) ? '' : 'r-disable'}`}
                                                            // onClick={() => (nextIdxMrStatus == 'ctd' && !this.getMinutes(midxdata['updated_date'])) ? this._undoBallScore("2", m_idx) : null}
                                                            className={`us-act ur-reset ${((lasBall == '6' || nextIdxMrStatus == 'ctd') && !this.getMinutes(midxdata['updated_date'])) ? '' : 'r-disable'}`}
                                                            onClick={() => ((lasBall == '6' || nextIdxMrStatus == 'ctd') && !this.getMinutes(midxdata['updated_date'])) ? this._undoBallScore("2", m_idx) : null}
                                                        >
                                                            <i className="icon-reset"></i>
                                                        </span>
                                                    }

                                                    {
                                                        ((m_idx == 0 && midxdata['market_status'] == 'ctd') || ((midxdata['market_status'] == 'ctd' && prevIdxMrStatus == 'cls'))) &&
                                                        <span  className="us-act custom">
                                                            <div onClick={() => this._playOver(m_idx)} style={{cursor:'pointer', borderRadius:30,alignItems:'center', color:'#ffffff', background:'#F8436E',width:'auto',display:'flex', flexDirection:'row', justifyContent:'space-around',padding:"0px 18px",}}>
                                                            <i className={`icon-ic-play`} style={{color:'#ffffff' ,fontSize:25}} ></i> 
                                                            <span style={{color:'#ffffff',fontSize:25,marginLeft:10}} >Play</span> 

                                                        </div>
                                                        </span>

                                                        // <div onClick={() => this._playOver(m_idx)} style={{cursor:'pointer', borderRadius:30,alignItems:'center', color:'#ffffff', background:'#F8436E',width:'auto',borderRadius:5,display:'flex', flexDirection:'row', justifyContent:'space-around',padding:"5px 20px 5px 20px",}}>
                                                        //     <i className={`icon-ic-play`} style={{color:'#ffffff' ,fontSize:35}} ></i> 
                                                        //     <span style={{color:'#ffffff',fontSize:25,marginLeft:10}} >Play</span> 

                                                        // </div>
                                                    }
                                                </div>
                                                {/* {
                                                    (midxdata['market_status'] != 'ctd') &&
                                                    <hr />
                                                } */}
                                                {
                                                    midxdata['market_status'] == 'ctd' &&
                                                    <div className="us-box">
                                                        <div className="us-score-head">
                                                            <div className="us-point-score">
                                                                Point Scoring for { Math.max((parseFloat(midxdata['over_ball'])-1).toFixed(1))}
                                                            </div>
                                                            <div className="us-ply-sel-box">
                                                                {
                                                                      batsManStatus !=0  &&
                                                                      <div className="us-sel-ply not-applied mr-4">
                                                                      <label htmlFor="">Select Batsman</label>
                                                                      
                                                                        
                                                                          <SelectDropdown SelectProps={Bat_Props} />
                                                                      
                                                                      
                                                                  </div>
                                                                }
                                                                {
                                                                    ballsManStatus != 0 &&
                                                                    <div className="us-sel-ply">
                                                                        <label htmlFor="">Select Bowler</label>
                                                                        <SelectDropdown SelectProps={Bowl_Props} />
                                                                    </div>
                                                                }
                                                               
                                                                
                                                            </div>
                                                        </div>
                                                        <div className="us-score-tbl">

                                                            <Table className="mb-0">
                                                                <thead>
                                                                    <tr>
                                                                        <th></th>
                                                                        {
                                                                            masterOdds.map((item, idx) => {
                                                                                return <th key={idx}>{item.name}</th>
                                                                            })
                                                                        }
                                                                        <th></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td className="us-point-score">Odds</td>
                                                                        {
                                                                            Object.keys(MarketOdds[m_idx]['market_odds']).map(function (index) {
                                                                                return (
                                                                                    <td key={index}>
                                                                                        <input
                                                                                            type="text"
                                                                                            name="Dot"
                                                                                            placeholder="Points"
                                                                                            className="form-control"
                                                                                            // value={MarketOdds[m_idx]['market_odds'][index] || ''}
                                                                                            value={MarketOdds[m_idx]['market_odds'][index]}
                                                                                            onChange={(e) => globalthis.handleInputChange(e, m_idx, index)}
                                                                                        />
                                                                                    </td>
                                                                                )
                                                                            })
                                                                        }
                                                                        <td className="us-btn">
                                                                            <Button
                                                                                disabled={UpdateBtnLoad}
                                                                                className="btn-secondary-outline"
                                                                                onClick={() => this._updateScore(m_idx)}
                                                                            >
                                                                                Update
                                                                        </Button>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </Table>

                                                        </div>
                                                    </div>
                                                }
                                                {
                                                    // (midxdata['market_status'] != 'ctd') &&
                                                    <hr />
                                                }
                                            </Fragment>
                                        </Fragment>
                                    )
                                })
                            }
                        </div>
                        <div className="us-close-btn">
                            <Button
                                className="btn-secondary-outline"
                                onClick={this._redirectToOver}
                            >
                                Close
                        </Button>
                        </div>
                    </Col>
                </Row>
            </div>
        )
    }

}
export default LF_UpdateScore