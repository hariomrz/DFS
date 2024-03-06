import React from 'react';
import { Row, Col, FormGroup } from 'react-bootstrap';
import { inputStyleLeft, darkInputStyleLeft } from '../../helper/input-style';
import FloatingLabel from 'floating-label-react';
import Validation from '../../helper/Validation';
import WSManager from "../../WSHelper/WSManager";
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import ConfirmationPopup from '../../Modals/ConfirmationPopup';
import Thankyou from '../../Modals/Thankyou';
import CustomHeader from '../../components/CustomHeader';
import UnableJoinContest from '../../Modals/UnableJoinContest';
import { Utilities, _isUndefined, _Map, checkBanState ,_indexOf} from '../../Utilities/Utilities';
import { getStockLineupTeamName, stockLineupProcessEquity, stockJoinContest, getStockUserAllTeams, stockSwitchTeam ,getStockLobbySetting} from '../../WSHelper/WSCallings';
import { DARK_THEME_ENABLE, GameType,StockSetting,BanStateEnabled,setValue ,SELECTED_GAMET} from "../../helper/Constants";
import StockItem from '../StockFantasy/StockItem';
import Highcharts from 'highcharts'
import HighchartsReact from 'highcharts-react-official'
import highcharts3d from "highcharts/highcharts-3d";
highcharts3d(Highcharts);

export default class StockSelectCaptainListEquity extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            lineupArr: this.props.location.state.SelectedLineup,
            MasterData: this.props.location.state.MasterData,
            LobyyData: this.props.location.state.LobyyData,
            IsChanged: false,
            showConfirmationPopUp: false,
            TotalTeam: [],
            userTeamListSend: [],
            FixturedContest: this.props.location.state.FixturedContest,
            isFrom: !_isUndefined(this.props.location.state.isFrom) && this.props.location.state.isFrom == 'editView' ? this.props.location.state.isFrom : !_isUndefined(this.props.location.state.isFrom) && this.props.location.state.isFrom == 'contestJoin' ? this.props.location.state.isFrom : this.props.location.state.isFrom == 'MyTeams' ? this.props.location.state.isFrom : "",
            teamData: !_isUndefined(this.props.location.state.isFrom) && this.props.location.state.isFrom == 'editView' ? this.props.location.state.team : '',
            teamName: (this.props.location.state.teamitem && this.props.location.state.teamitem.team_name != '') ? this.props.location.state.teamitem.team_name : (this.props.location.state.isClone ? '' : (!_isUndefined(this.props.location.state.isFrom) && this.props.location.state.isFrom == 'editView' || this.props.location.state.isFrom == 'MyContest' ? (this.props.location.state.team && this.props.location.state.team.team_name) : this.props.location.state.teamName)),
            lineupMasterdId: '',
            showThankYouModal: false,
            rootDataItem: !_isUndefined(this.props.location.state.isFrom) && this.props.location.state.isFrom == 'editView' ? this.props.location.state.rootDataItem : !_isUndefined(this.props.location.state.FixturedContest) ? this.props.location.state.FixturedContest : "",
            isFromMyTeams: this.props.location.state.isFromMyTeams ? this.props.location.state.isFromMyTeams : false,
            ifFromSwitchTeamModal: !_isUndefined(this.props.location.state.ifFromSwitchTeamModal) ? this.props.location.state.ifFromSwitchTeamModal : false,
            salary_cap: !_isUndefined(this.props.location.state.salary_cap) ? this.props.location.state.salary_cap : 0,
            isLoading: false,
            clickOnce: false,
            isCategory: true,
            isClone: !_isUndefined(this.props.location.state.isClone) ? this.props.location.state.isClone : false,
            isTeamNameChanged: true,
            showUJC: false,
            StockSettingValue: !_isUndefined(this.props.location.state.StockSettingValue) ? this.props.location.state.StockSettingValue : [],
            viewType:0,
            sourcePieData:{},
            showCVstock:false,
            setPlayerRole:0,
        }
        this.headerRef = React.createRef();

    }
    showUJC = (data) => {
        this.setState({
            showUJC: true,
        });
    }

    showCVstockPopup=(status,e)=>{
        e.stopPropagation();
        this.setState({
            showCVstock: true,
            setPlayerRole:status
        })
    }
    hideCVstockPopup=()=>{
        this.setState({
            showCVstock: false
        })
    }

    hideUJC = () => {
        this.setState({
            showUJC: false,
        }, () => {
            this.props.history.push({ pathname: '/' });
        });
    }
    getValidationState(type, value) {
        return Validation.validate(type, value)
    }
    ChangePlayerRole = (role,player) => {
       this.hideCVstockPopup()
        let lineupArr = this.state.lineupArr;
        _Map(lineupArr, (item) => {
            if (item.player_role == role || item.player_role == 0) {
                item.player_role = 0;
            }
            return item;
        })
        let index = _indexOf(lineupArr, player);
        lineupArr[index].player_role = (role === 1) ? "1" : "2";
        this.setState({ lineupArr: lineupArr })
        if((role === 1 && this.returnPlayerRole(2, lineupArr)) || (role === 2 && this.returnPlayerRole(1, lineupArr))){
            this.setState({ IsChanged: true })
        }
        else if ((this.state.StockSettingValue.c_point > 0 && this.state.StockSettingValue.vc_point <= 0) || (this.state.StockSettingValue.vc_point > 0 && this.state.StockSettingValue.c_point <= 0)) {            
            this.setState({ IsChanged: true })
        }
        else {
            this.setState({ IsChanged: false })
        }

        //Analytics Calling 
        WSManager.googleTrack(WSC.GA_PROFILE_ID, 'selectcaptain');
    }


    returnPlayerRole = (role, lineupArr) => {
        for (var player of lineupArr) {
            if (player.player_role == role) {
                return true
            }
        }
        return false
    }

    SubmitLineup = () => {
        if (this.checkButtonEnable()) {
            this.setState({ clickOnce: true })
            if (this.isLoading) {
                return true;
            }

            let tmpBuyArray = {};
            let tmpSellArray = {};
            let cap_ptID = '';
            let vcap_ptID = '';

            _Map(this.state.lineupArr, (item) => {
                let ptID = item.stock_id;
                if (item.player_role == 1) {
                    cap_ptID = ptID
                }
                if(item.player_role == 2){
                    vcap_ptID = ptID
                }
                if (item.action == 1) {
                    //tmpBuyArray.push(ptID)
                    tmpBuyArray[ptID] = item.shareValue;

                } else {
                    //tmpSellArray.push(ptID)
                    tmpSellArray[ptID] = item.shareValue;

                }
            });
            let param = {
                "team_name": this.state.teamName,
                "collection_id": this.state.LobyyData.collection_master_id ? this.state.LobyyData.collection_master_id : this.state.FixturedContest.collection_master_id,
                "stocks": {
                    b: tmpBuyArray,
                    s: tmpSellArray
                },
                "c_id": parseInt(cap_ptID),
                "vc_id":parseInt(vcap_ptID),
                "lineup_master_id": this.state.isClone ? '' : (this.props.location.state.teamitem ? this.props.location.state.teamitem.lineup_master_id ? this.props.location.state.teamitem.lineup_master_id : this.props.location.state.lineup_master_id : (this.state.teamData.lineup_master_id ? this.state.teamData.lineup_master_id : this.state.lineupMasterdId))
            }

            this.setState({
                isLoading: true
            });
            stockLineupProcessEquity(param).then((responseJson) => {
                this.setState({
                    isLoading: false
                });
                if (responseJson.response_code == WSC.successCode) {
                    if (responseJson.data.lineup_master_id) {
                        this.setState({ lineupMasterdId: responseJson.data.lineup_master_id })
                    }
                    if (this.state.isFrom == 'editView' && !this.state.isFromMyTeams) {
                        Utilities.showToast(responseJson.message, 1000);
                        this.props.history.push({ pathname: '/my-contests', state: { from: 'SelectCaptain' } });
                    }
                    else if ((this.state.isFrom == "MyTeams" || this.state.isFrom == "MyContest" || this.state.isFrom == "editView") && this.state.isFromMyTeams) {
                        Utilities.showToast(responseJson.message, 1000);
                        var go_index = -2;
                        if (this.state.isFrom == "editView" && !this.state.isClone && !this.state.isFromMyTeams) {
                            go_index = -3;
                        }
                        WSManager.clearLineup();
                        this.props.history.go(go_index);
                    }
                    else if (this.state.ifFromSwitchTeamModal) {
                        Utilities.showToast(responseJson.message, 1000);
                        this.switchTeam(this.state.FixturedContest, responseJson.data.lineup_master_id, this.props.location.state.lineup_master_contest_id);
                    }
                    else {
                        if (checkBanState(this.state.FixturedContest, CustomHeader, 'CAP')) {
                            this.getUserLineUpListApi();
                        }
                    }
                    //Analytics Calling 
                    WSManager.googleTrack(WSC.GA_PROFILE_ID, 'stock_confirmteam');

                }else{
                    Utilities.showToast(responseJson.message, 3000);
                }
                this.setState({ clickOnce: false })
            })
        }
    }

    switchTeam(FixturedContest, lineup_master_id, lineup_master_contest_id) {
        let param = {
            "contest_id": FixturedContest.contest_id,
            "lineup_master_id": lineup_master_id,
            "lineup_master_contest_id": lineup_master_contest_id,
        }

        let apiCall = stockSwitchTeam
        this.setState({ isLoaderShow: true })
        apiCall(param).then((responseJson) => {
            this.setState({ isLoaderShow: false })
            if (responseJson.response_code == WSC.successCode) {
                Utilities.showToast(responseJson.message, 5000);
                this.props.history.push({ pathname: '/my-contests', state: { from: 'SelectCaptain' } });
                WSManager.clearLineup();
            }
        })

    }

    getTeamName() {
        if (!this.state.teamName) {
            let param = {
                "collection_id": this.state.LobyyData.collection_master_id ? this.state.LobyyData.collection_master_id : this.state.FixturedContest.collection_master_id,
            }
            getStockLineupTeamName(param).then((responseJson) => {
                if (responseJson.response_code == WSC.successCode) {
                    this.setState({ teamName: responseJson.data.team_name, isTeamNameChanged: false }, () => {
                        this.setState({ isTeamNameChanged: true })
                    })
                }
            })
        }
    }

    getUserLineUpListApi() {
        let param = {
            "collection_id": this.state.LobyyData.collection_master_id ? this.state.LobyyData.collection_master_id : this.state.FixturedContest.collection_master_id
        }
        this.setState({ isLoaderShow: true })
        getStockUserAllTeams(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.setState({
                    showConfirmationPopUp: true,
                    TotalTeam: responseJson.data,
                    userTeamListSend: responseJson.data
                })
                if (this.state.userTeamListSend) {
                    let tempList = [];
                    this.state.userTeamListSend.map((data, key) => {
                        tempList.push({ value: data, label: data.team_name })
                        return '';
                    })
                    this.setState({ userTeamListSend: tempList });
                }
            }
        })
    }

    ConfirmatioPopUpShow = (data) => {
        this.setState({
            showConfirmationPopUp: true,
        });
    }


    ConfirmatioPopUpHide = () => {
        this.setState({
            showConfirmationPopUp: false,
        });
    }

    ThankYouModalShow = (data) => {
        this.setState({
            showThankYouModal: true,
        });
    }

    ThankYouModalHide = () => {
        this.setState({
            showThankYouModal: false,
        });
    }


    ConfirmEvent = (dataFromConfirmPopUp, context) => {
        if (dataFromConfirmPopUp.selectedTeam.value.lineup_master_id && dataFromConfirmPopUp.selectedTeam.value.lineup_master_id != null && dataFromConfirmPopUp.selectedTeam.lineup_master_id == "" || dataFromConfirmPopUp.selectedTeam == "") {
            Utilities.showToast(AppLabels.SELECT_NAME_FIRST, 1000);
        } else {
            var currentEntryFee = 0;
            currentEntryFee = dataFromConfirmPopUp.entryFeeOfContest;
            if (
                (dataFromConfirmPopUp.FixturedContestItem.currency_type == 2 && (parseInt(currentEntryFee) <= parseInt(dataFromConfirmPopUp.balanceAccToMaxPercent))) ||
                (dataFromConfirmPopUp.FixturedContestItem.currency_type != 2 && (parseFloat(currentEntryFee) <= parseFloat(dataFromConfirmPopUp.balanceAccToMaxPercent)))
            ) {
                this.CallJoinGameApi(dataFromConfirmPopUp);
            }
            else {
                if (dataFromConfirmPopUp.FixturedContestItem.currency_type == 2) {
                    if (Utilities.getMasterData().allow_buy_coin == 1) {
                        WSManager.setFromConfirmPopupAddFunds(true);
                        WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp)
                        WSManager.setPaymentCalledFrom("SelectCaptainList")
                        this.props.history.push({ pathname: '/buy-coins', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, state: { isFrom: 'SelectCaptainList', isStockF: true } });

                    }
                    else {
                        this.props.history.push({ pathname: '/earn-coins', state: { isFrom: 'lineup-flow', isStockF: true } })
                    }
                }
                else {
                    WSManager.setFromConfirmPopupAddFunds(true);
                    WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp)
                    WSManager.setPaymentCalledFrom("SelectCaptainList")
                    this.props.history.push({ pathname: '/add-funds', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, isStockF: true });
                }

                //Analytics Calling 
                WSManager.googleTrack(WSC.GA_PROFILE_ID, 'paymentgateway');
            }
        }
    }

    createTeamAndJoin = (dataFromConfirmFixture, dataFromConfirmLobby) => {
        // this.props.history.push({ pathname: '/lineup', state: { FixturedContest: dataFromConfirmFixture, LobyyData: dataFromConfirmLobby, current_sport: AppSelectedSport, isReverseF: this.state.isReverseF, isSecIn: this.state.isSecIn } })
    }

    CallJoinGameApi(dataFromConfirmPopUp) {
        var contestId = dataFromConfirmPopUp.FixturedContestItem.contest_id
        let param = {
            "contest_id": contestId,
            "lineup_master_id": dataFromConfirmPopUp.selectedTeam.lineup_master_id ? dataFromConfirmPopUp.selectedTeam.lineup_master_id : dataFromConfirmPopUp.selectedTeam.value.lineup_master_id,
            "promo_code": dataFromConfirmPopUp.promoCode,
            "device_type": window.ReactNativeWebView ? WSC.deviceTypeAndroid : WSC.deviceType
        }

        let contestUid = dataFromConfirmPopUp.FixturedContestItem.contest_unique_id
        let contestAccessType = dataFromConfirmPopUp.FixturedContestItem.contest_access_type;
        let isPrivate = dataFromConfirmPopUp.FixturedContestItem.is_private;
        this.setState({ isLoaderShow: true })
        let apiCall = stockJoinContest;
        apiCall(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                if (contestAccessType == '1' || isPrivate == '1') {
                    let deviceIds = [];
                    deviceIds = responseJson.data.user_device_ids;
                    WSManager.updateFirebaseUsers(contestUid, deviceIds);
                }
                this.ConfirmatioPopUpHide();
                setTimeout(() => {
                    this.ThankYouModalShow()
                }, 300);
                WSManager.clearLineup();
            } else {
                if (Utilities.getMasterData().allow_self_exclusion == 1 && responseJson.data.self_exclusion_limit == 1) {
                    this.ConfirmatioPopUpHide();
                    this.showUJC();
                }
                else {
                    Utilities.showToast(responseJson.global_error != "" ? responseJson.global_error : responseJson.message, 2000);
                }
            }
        })

        //Analytics Calling 
        WSManager.googleTrack(WSC.GA_PROFILE_ID, 'stock_joingame');


    }
    handleChange = (e) => {
        const name = e.target.name;
        const value = e.target.value;
        this.setState({ [name]: value }, this.validateForm);
    }

    componentDidMount = () => {
        this.setPieChartData();
        if (this.state.isFrom == 'editView') {
            if (this.state.isClone) {
                this.getTeamName()
            }
            this.setState({ IsChanged: true })
        }
        else {
            this.getTeamName()
        }
        if (BanStateEnabled && !WSManager.getProfile().master_state_id) {
            checkBanState(this.state.FixturedContest, CustomHeader, 'CAP')
        }
        // if(StockSetting &&StockSetting.length > 0){
        //     this.setState({
        //         StockSettingValue: StockSetting
        //     })
        // }
        // else{
        //     getStockLobbySetting().then((responseJson) => {
        //         setValue.setStockSettings(responseJson.data);
        //         this.setState({ StockSettingValue: responseJson.data })
        //     })
        // }
    }
    setPieChartData=()=>{
        let tempList = [];
        let otherObject = {};
        this.state.lineupArr && this.state.lineupArr.length >0 && this.state.lineupArr.map((data, key) => {
            tempList.push({name: data.stock_name, y: parseFloat(data.stockPrize), action:data.action,color: data.action == 1 ? '#5DBE7D' : '#EB4A3C' })

            
        })
        otherObject["name"]='Unused'
        otherObject["y"]=parseFloat(this.state.salary_cap)
        otherObject["action"]=3
        otherObject["color"]='#D8D8D8'
        tempList.push(otherObject)
        let tmpLineupArray =tempList.sort((a, b) => (b.action - a.action))

        this.setState({sourcePieData:{
            chart: {
              type: 'pie',
              options3d: {
                enabled: true,
                alpha: 45,
                beta: 0
              }
            },
            title: {
              text: ''
            },
            accessibility: {
              point: {
                valueSuffix: ''
              }
            },
            tooltip: {
              pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            plotOptions: {
              pie: {
                size:200,
                innerSize: 100,
                allowPointSelect: true,
                cursor: 'pointer',
                depth: 35,
                dataLabels: {
                  enabled: true,
                  format: '{point.name}'
                }
              }
            },
            credits: {
              enabled: false,
            },
            series: [{
              type: 'pie',
              name: 'Stocks',
              data: tmpLineupArray
            }]
          }
        })
    }

    goToLobby = () => {
        // this.props.history.push({ pathname: '/' });
        let data = this.state.LobyyData
        data['collection_master_id'] = this.state.LobyyData.collection_id;
        let name = data.category_id.toString() === "1" ? 'Daily' : data.category_id.toString() === "2" ? 'Weekly' : 'Monthly';
        let contestListingPath = '/stock-fantasy-equity/contest/' + data.collection_id + '/' + name;
        let CLPath = contestListingPath.toLowerCase() + "?sgmty=" + btoa(SELECTED_GAMET)
        this.props.history.push({ pathname: CLPath, state: { LobyyData: this.state.LobyyData, lineupPath: CLPath,isFromPM: true } })

    }

    seeMyContest = () => {
        this.props.history.push({ pathname: '/my-contests', state: { from: 'SelectCaptain' } });
    }

    callAfterAddFundPopup() {
        if (WSManager.getFromConfirmPopupAddFunds()) {
            WSManager.setFromConfirmPopupAddFunds(false);
            var contestData = WSManager.getContestFromAddFundsAndJoin();
            this.ConfirmEvent(contestData)
        }
    }

    UNSAFE_componentWillMount() {
        WSManager.setPickedGameType(GameType.StockFantasyEquity);
        let CinfirmPopUpIsAddFundsClicked = WSManager.getFromConfirmPopupAddFunds()
        let tempIsAddFundsClicked = WSManager.getFromFundsOnly()
        setTimeout(() => {
            if (tempIsAddFundsClicked == 'true' && CinfirmPopUpIsAddFundsClicked == 'true' || CinfirmPopUpIsAddFundsClicked == true) {
                setTimeout(() => {
                    this.callAfterAddFundPopup()
                }, 200);
            }
        }, 500);
    }

    onPlayers = () => {
        this.setState({
            isCategory: true
        })
    }
    onPoints = () => {
        this.setState({
            isCategory: false,
            lineupArr: this.state.lineupArr.sort((a, b) => (b.fantasy_score - a.fantasy_score))
        })
    }

    checkButtonEnable() {
        var isValid = true;
        var teamname = this.state.teamName ? this.state.teamName : this.props.location.state.team_name
        if (!teamname || teamname.length < 4 || !this.state.IsChanged) {
            isValid = false;
        }
        else if (this.state.isLoading || this.state.clickOnce) {
            isValid = false;
        }
        return isValid;
    }
    viewTypeSelected = (viewTypeValue) => {
        this.setState({viewType:viewTypeValue})
    }
    getSelectedCapAndVC =(status)=>{
        let name;
        _Map(this.state.lineupArr, (item) => {
            if (item.player_role == 1 && status == 1) {
                name = item.stock_name
            }
            if(item.player_role == 2 && status == 2){
                name = item.stock_name
            }
        });
        return name

       
    }
    render() {
        const {
            teamName,
            showConfirmationPopUp,
            userTeamListSend,
            FixturedContest,
            showThankYouModal,
            lineupMasterdId,
            showUJC,
            TotalTeam,
            LobyyData,
            StockSettingValue,
            showCVstock
        } = this.state;
        const HeaderOption = {
            back: true,
            hideShadow: true,
            title: '',
            isPrimary: DARK_THEME_ENABLE ? false : true,
            screentitle: AppLabels.CHOOSE_CAPTAIN.replace(AppLabels.CAPTAIN, AppLabels.CORE),
            minileague: true,
            leagueDate: {
                scheduled_date: LobyyData.scheduled_date || '',
                end_date: LobyyData ? (LobyyData.category_id.toString() === "1" ? '' : LobyyData.end_date) : '',
                game_starts_in: LobyyData.game_starts_in || '',
                lbl: LobyyData ? (LobyyData.category_id.toString() === "1" ? AppLabels.DAILY : LobyyData.category_id.toString() === "2" ? AppLabels.WEEKLY : AppLabels.MONTHLY) : ''
            },
            rightSection:true,
            listPieOption:this.viewTypeSelected
        }
        let buyStock = (this.state.lineupArr || []).filter((item) => item.action == 1)
        let sellStock = (this.state.lineupArr || []).filter((item) => item.action == 2)
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container web-container-fixed white-bg">
                        <CustomHeader {...this.props} ref={this.headerRef} HeaderOption={HeaderOption} />
                        <div onClick={() => this.hideCVstockPopup()}  className="select-captian-wrap stock-f">
                            <div className="filed-with-icon">
                                <Row >
                                    {
                                        <Col xs={12}>
                                            <FormGroup
                                                className='xinput-label-center'
                                                controlId="formBasicText"
                                                validationState={teamName ? "success" : this.props.location.state.team_name && this.getValidationState('teamName', teamName ? teamName : this.props.location.state.team_name)}
                                            >
                                                {
                                                    this.state.isTeamNameChanged &&
                                                    <FloatingLabel
                                                        autoComplete='off'
                                                        styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                        id='teamName'
                                                        name='teamName'
                                                        placeholder={AppLabels.ENTER_TAM_NAME.replace(AppLabels.Team, AppLabels.PORTFOLIO)}
                                                        type='text'
                                                        value={teamName ? teamName : this.props.location.state.team_name || ''}
                                                        onChange={this.handleChange}
                                                    />
                                                }
                                            </FormGroup>
                                        </Col>
                                    }
                                </Row>
                            </div>
                            {
                                this.state.viewType == 0 &&
                                <div className="selection-help-txt">
                                <h4>
                                    {/* <span>{AppLabels.CHOOSE_CAPTAIN.replace(AppLabels.CAPTAIN, AppLabels.CORE)}</span> */}
                                    <span>
                                        {StockSettingValue.c_point > 0 && StockSettingValue.vc_point <= 0 &&
                                            AppLabels.CHOOSE_CAPTAIN.replace(AppLabels.CAPTAIN, AppLabels.CORE_STOCK)
                                        }
                                        {StockSettingValue.c_point > 0 && StockSettingValue.vc_point > 0 &&
                                            AppLabels.CHOOSE_CORE_STOCK_SATELLITE_STOCK
                                        }
                                        {StockSettingValue.c_point <= 0 && StockSettingValue.vc_point > 0 && 
                                            AppLabels.CHOOSE_SATELLITE_STOCK
                                        }
                                    </span>
                                </h4>
                                <p>
                                    <span>
                                        <span className='captain_cirlce'>{AppLabels.A}</span>
                                        {AppLabels.GETS} {StockSettingValue.c_point + 'x'} {AppLabels.RETURN}
                                    </span>
                                    {StockSettingValue.vc_point > 0 && 
                                        <span>
                                            <span className='captain_cirlce'>{AppLabels.B}</span>
                                            {AppLabels.GETS} {StockSettingValue.vc_point + 'x'} {AppLabels.RETURN}
                                        </span>
                                    }
                                </p>

                            </div>
                            }
                            {
                                this.state.viewType == 0 ?
                                    <div className="lineup-list-view">
                                {buyStock.length > 0 && <div className="player-list-container">
                                    <div className="item-header">
                                        <span>{AppLabels.BUY_STOCK} <i className="icon-stock_up" /></span>
                                    </div>
                                    {
                                        _Map(buyStock, (item, idx) => {
                                            return (
                                                <StockItem key={item.stock_id + idx} item={item} isFrom={'cap'} ChangePlayerRole={this.ChangePlayerRole} StockSettingValue={StockSettingValue} />
                                            )
                                        })

                                    }
                                </div>}
                                {sellStock.length > 0 && <div className="player-list-container down">
                                    <div className="item-header">
                                        <span>{AppLabels.SELL_STOCK} <i className="icon-stock_down" /></span>
                                    </div>
                                    {
                                        _Map(sellStock, (item, idx) => {
                                            return (
                                                <StockItem key={item.stock_id + idx} item={item} isFrom={'cap'} ChangePlayerRole={this.ChangePlayerRole} StockSettingValue={StockSettingValue} />
                                            )
                                        })

                                    }
                                </div>}
                            </div>
                            :
                                    <div style={{marginTop:20}} onClick={() => this.hideCVstockPopup()} className='pie-chart'>
                                        <HighchartsReact
                                            highcharts={Highcharts}
                                            options={this.state.sourcePieData}
                                        />
                                        <div className="choose-your-core-stock">{AppLabels.CHOOSE_CORE_STOCK_AND}{AppLabels.GETS} {StockSettingValue.c_point + 'x'} {AppLabels.RETURN}</div>
                                        <div onClick={(e) => this.showCVstockPopup(1,e)} className="select-captain-stock-conatiner">
                                            <div className='combined'>
                                                <div className="oval">
                                                    <div className='text-lable'>{AppLabels.A}</div>
                                                    </div>
                                                <div className={"stockName" +(this.getSelectedCapAndVC(1) ? " selected": '')}>{this.getSelectedCapAndVC(1) ? this.getSelectedCapAndVC(1) :AppLabels.SELECT}</div>
                                            </div>

                                            <i className="icon-arrow-down down-arrow"></i>

                                        </div>
                                        <div className="path-3"></div>
                                        <div className="choose-your-core-stock vc-stock">{AppLabels.CHOOSE_SATELLITE_STOCK} {' & '} {AppLabels.GETS} {StockSettingValue.vc_point + 'x'} {AppLabels.RETURN}</div>
                                        <div onClick={(e) => this.showCVstockPopup(2,e)} className="select-captain-stock-conatiner">
                                            <div className='combined'>
                                                <div className="oval">
                                                <div className='text-lable'>{AppLabels.B}</div>
                                                  </div>
                                                <div className={"stockName" +(this.getSelectedCapAndVC(2) ? " selected": '')}>{this.getSelectedCapAndVC(2) ? this.getSelectedCapAndVC(2) :AppLabels.SELECT}</div>
                                            </div>

                                            <i className="icon-arrow-down down-arrow"></i>

                                        </div>
                                        <div className="path-3"></div>
                                    </div>
                            }
                            
                        </div>


                        <button disabled={!this.checkButtonEnable()} onClick={() => this.SubmitLineup()} className="btn btn-primary  btn-block btm-fix-btn">{AppLabels.SUBMIT}</button>

                        {showConfirmationPopUp &&
                            <ConfirmationPopup lobbyDataToPopup={this.state.LobyyData} IsConfirmationPopupShow={this.ConfirmatioPopUpShow} IsConfirmationPopupHide={this.ConfirmatioPopUpHide} TeamListData={userTeamListSend} TotalTeam={TotalTeam} FixturedContest={FixturedContest} ConfirmationClickEvent={this.ConfirmEvent} CreateTeamClickEvent={this.createTeamAndJoin} fromContestListingScreen={false} createdLineUp={lineupMasterdId} isStockF={true} />
                        }

                        {showThankYouModal &&
                            <Thankyou ThankyouModalShow={this.ThankYouModalShow} ThankYouModalHide={this.ThankYouModalHide} goToLobbyClickEvent={this.goToLobby} seeMyContestEvent={this.seeMyContest} isStock={true} />
                        }
                        {
                            showUJC &&
                            <UnableJoinContest
                                showM={showUJC}
                                hideM={this.hideUJC}
                            />
                        }
                        {
                            showCVstock &&

                            <span className={"select-cap-vc-option"}>
                                {
                                    _Map(this.state.lineupArr, (item, idx) => {
                                        return (
                                            <span className={(item.player_role && item.player_role == 1 || item.player_role == 2 ? ' disable':'')} onClick={() => this.ChangePlayerRole(this.state.setPlayerRole, item)} >
                                                {item.stock_name}
                                                </span>
                                        )
                                    })
                                }
                            </span>

                        }

                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}