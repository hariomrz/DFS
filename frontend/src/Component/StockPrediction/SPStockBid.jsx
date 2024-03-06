import React, { Suspense, lazy } from 'react';
import { Row, Col, FormGroup, Table, FormControl, OverlayTrigger,Tooltip } from 'react-bootstrap';
import { inputStyle } from '../../helper/input-style';
import ls from 'local-storage';
import FloatingLabel from 'floating-label-react';
import Validation from '../../helper/Validation';
import WSManager from "../../WSHelper/WSManager";
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import ConfirmationPopup from '../../Modals/ConfirmationPopup';
import Thankyou from '../../Modals/Thankyou';
import CustomHeader from '../../components/CustomHeader';
import UnableJoinContest from '../../Modals/UnableJoinContest';
import { Utilities, _isUndefined, _Map, checkBanState ,_indexOf} from '../../Utilities/Utilities';
import { getSPLineupMasterData, getSPLineupProcess, stockJoinContest, getSPUserLineupList, switchTeamContest} from '../../WSHelper/WSCallings';
import { DARK_THEME_ENABLE, GameType,StockSetting,BanStateEnabled,setValue } from "../../helper/Constants";
import Images from '../../components/images';
import { floatingStyles,focusStyles, inputStyles,labelStyles} from 'floating-label-react';
import '../../assets/fonts/primary_font_Regular.ttf';
import '../../assets/fonts/primary_font_Regular.woff';
import '../../assets/fonts/primary_font_Regular.woff2';
const StockPlayerCard = lazy(() => import('../StockFantasy/StockPlayerCard'));
const SPRules = lazy(() => import('./SPFantasyRules'));
const MyAlert = lazy(() => import('../../Modals/MyAlert'));


const stkTeamNmInput = {
    floating: {
        ...floatingStyles,
        color: '#999',
        fontSize: '12px',
        borderBottomColor: '#EAEAEA',
        fontFamily: 'PrimaryF-Regular',
        textAlign:'left',
        top: '8px'
    },
    focus: {
        ...focusStyles,
        borderColor: '#e1e1e1',
    },
    input: {
        ...inputStyles,
        borderBottomWidth: 1,
        borderBottomColor: '#EAEAEA',
        width: '100%',
        fontSize: '16px',
        color: '#212121',
        fontFamily: 'PrimaryF-Regular',
        padding: '10px 0px 10px',
        marginTop: '5px',
        textAlign:'left',

    },
    label: {
        ...labelStyles,
        paddingBottom: '0px',
        marginBottom: '0px',
        top: '-1px',
        flot:'left',
        width: '100%',
        fontSize: '14px',
        color: '#999',
        fontWeight:400,
        fontFamily: 'PrimaryF-Regular',
    }
}
const darkstkTeamNmInput = {
    floating: {
        ...floatingStyles,
        color: '#999',
        fontSize: '12px',
        borderBottomColor: 'rgba(153,153,153,0.4)',
        fontFamily: 'PrimaryF-Regular',
        textAlign:'left',
        top: '8px'
    },
    focus: {
        ...focusStyles,
        borderColor: '#e1e1e1',
    },
    input: {
        ...inputStyles,
        borderBottomWidth: 1,
        borderBottomColor: 'rgba(153,153,153,0.4)',
        width: '100%',
        fontSize: '16px',
        color: '#FFF',
        fontFamily: 'PrimaryF-Regular',
        padding: '10px 0px 10px',
        marginTop: '5px',
        textAlign:'left',
        background: '#030409'
    },
    label: {
        ...labelStyles,
        paddingBottom: '0px',
        marginBottom: '0px',
        top: '-1px',
        flot:'left',
        width: '100%',
        fontSize: '14px',
        color: '#999',
        fontWeight:400,
        fontFamily: 'PrimaryF-Regular',
    }
}


export default class SPStockBid extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            lineupArr: ls.get('Lineup_data') ? ls.get('Lineup_data') : this.props.location.state.SelectedLineup,
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
            isLoading: false,
            clickOnce: false,
            isCategory: true,
            isClone: !_isUndefined(this.props.location.state.isClone) ? this.props.location.state.isClone : false,
            isTeamNameChanged: true,
            showUJC: false,
            showPlayerCard: false,
            playerDetails: {},
            showRulesModal: false,
            showTimeOutAlert: false
        }
        this.headerRef = React.createRef();

    }
    showUJC = (data) => {
        this.setState({
            showUJC: true,
        });
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

            let FinalSelArry = {};
          
            _Map(this.state.lineupArr, (item) => {
                let stkID = item.stock_id;
                let stkPrVal = item.user_price;

                FinalSelArry = {...FinalSelArry, [stkID]: stkPrVal}
            });
            let param = {
                "collection_id": this.state.LobyyData.collection_id ? this.state.LobyyData.collection_id : this.state.FixturedContest.collection_id,
                "stocks": FinalSelArry,
                "lineup_master_id": this.state.isClone ? '' : (this.props.location.state.teamitem ? this.props.location.state.teamitem.lineup_master_id ? this.props.location.state.teamitem.lineup_master_id : this.props.location.state.lineup_master_id : (this.state.teamData.lineup_master_id ? this.state.teamData.lineup_master_id : this.state.lineupMasterdId))
            }

            this.setState({
                isLoading: true
            });
            getSPLineupProcess(param).then((responseJson) => {
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

        let apiCall = switchTeamContest
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

    getTeamName = async () =>{
        if (!this.state.teamName) {
            let param = {
                "collection_id": this.state.LobyyData && this.state.LobyyData.collection_id ? this.state.LobyyData.collection_id : this.state.FixturedContest.collection_id,
            }
            var api_response_data = await getSPLineupMasterData(param);
            if (api_response_data.response_code === WSC.successCode) {
                this.parseMasterData(api_response_data.data);
            }
        }
    }

    getUserLineUpListApi() {
        let param = {
            "collection_id": this.state.LobyyData.collection_id ? this.state.LobyyData.collection_id : this.state.FixturedContest.collection_id
        }
        this.setState({ isLoaderShow: true })
        getSPUserLineupList(param).then((responseJson) => {
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
        if(!Utilities.minuteDiffValueStock({ date: dataFromConfirmPopUp.FixturedContestItem.game_starts_in },-5)){
            this.ConfirmatioPopUpHide();
            this.showTimeOutModal();
        }
        else{
            if (dataFromConfirmPopUp.selectedTeam.value.lineup_master_id && dataFromConfirmPopUp.selectedTeam.value.lineup_master_id != null && dataFromConfirmPopUp.selectedTeam.lineup_master_id == "" || dataFromConfirmPopUp.selectedTeam == "") {
                Utilities.showToast(AL.SELECT_NAME_FIRST, 1000);
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
                            this.props.history.push({ pathname: '/buy-coins', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, state: { isFrom: 'SelectCaptainList', isStockF: true ,isStockPF: true} });
    
                        }
                        else {
                            this.props.history.push({ pathname: '/earn-coins', state: { isFrom: 'lineup-flow', isStockF: true,isStockPF: true } })
                        }
                    }
                    else {
                        WSManager.setFromConfirmPopupAddFunds(true);
                        WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp)
                        WSManager.setPaymentCalledFrom("SelectCaptainList")
                        this.props.history.push({ pathname: '/add-funds', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, isStockF: true,isStockPF: true });
                    }
    
                    //Analytics Calling 
                    WSManager.googleTrack(WSC.GA_PROFILE_ID, 'paymentgateway');
                }
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
    }

    goToLobby = () => {
        this.props.history.push({ pathname: '/' });
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
        if( ls.get('Lineup_data') ? ls.get('Lineup_data') : this.props.location.state.SelectedLineup){
            let tmpAllList = ls.get('Lineup_data') ? ls.get('Lineup_data') : this.props.location.state.SelectedLineup;
            for (var obj of tmpAllList) {
                if (obj.user_price) {
                    obj['user_price'] = obj.user_price;
                }
                // else{
                //     obj['user_price'] = 0;
                // }
                break;
            }
            this.setState({
                lineupArr:tmpAllList
            })
        }
        WSManager.setPickedGameType(GameType.StockPredict);
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
        const {lineupArr} = this.state;
        var isValid = true;
        if (lineupArr.length == 0 || this.state.isLoading || this.state.clickOnce) {
            isValid = false;
        }
        else if(lineupArr.length != 0){
            for (var item of lineupArr) {
                if(!item.user_price || parseInt(item.user_price) == 0){
                    isValid = false;
                    break;
                }
            }
        }
        return isValid;
    }
    viewStatusListPie = (data) => {
       alert(data)
    }


    PlayerCardShow = (e, item) => {
        e.stopPropagation();
        item.collection_master_id = this.state.collectionMasterId;
        this.setState({
            playerDetails: item,
            showPlayerCard: true
        });
    }

    PlayerCardHide = () => {
        this.setState({
            showPlayerCard: false,
            playerDetails: {}
        });
    }
    /**
     * 
     * @description method to display rules scoring modal, when user join contest.
     */
    openRulesModal = () => {
        this.setState({
            showRulesModal: true,
        });
    }
    /**
     * 
     * @description method to hide rules scoring modal
     */
    hideRulesModal = () => {
        this.setState({
            showRulesModal: false,
        });
    }
    
    removeStock = (item) => {
        let tmpAllList = this.state.lineupArr;
        for (var obj of tmpAllList) {
            if (obj.stock_id === item.stock_id) {
                obj['is_selected'] = false;
                break;
            }
        }
        let tmpArry = tmpAllList.filter((stock) => {
            return stock.is_selected == true
        })
        ls.set('removeStkCalled', true)
        ls.set('Lineup_data', tmpArry)
        if(tmpArry.length == 0){
            this.props.history.goBack(); 
        }
        this.setState({
            lineupArr: tmpArry
        })
    }

    handlePVChange=(e,item)=>{
        const value = e.target.value;
        let dec = ''
        if(value.includes('.')){
            dec = value.split('.')[1]
        }
        let tmpAllList = this.state.lineupArr;
        if(value == 0){
            Utilities.showToast('Prediction Amount should be greater than 0', 1000);
        }
        for (var obj of tmpAllList) {
            if (obj.stock_id === item.stock_id) {
                if(dec.length <= 2){
                    obj['user_price'] = value;
                }
                break;
            }
        }        
        this.setState({
            lineupArr:tmpAllList
        })
    }

    showTimeOutModal=()=>{
        this.setState({
            showTimeOutAlert: true
        })
    }

    hideTimeOutModal=()=>{
        this.goToLobby()
        this.setState({
            showTimeOutAlert: false
        })
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
            lineupArr,
            showPlayerCard,
            showRulesModal,
            playerDetails,
            showTimeOutAlert
        } = this.state;
        const HeaderOption = {
            back: true,
            hideShadow: true,
            title: 'Your Quote',
            isPrimary: DARK_THEME_ENABLE ? false : true,
            screenDatetitle: LobyyData ,
            showleagueTime: true,
            isBid: true,
            showRSAction: this.openRulesModal,
            showRS: true
        }
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container web-container-fixed  sp-stock-bid">
                        <CustomHeader {...this.props} ref={this.headerRef} HeaderOption={HeaderOption} />
                        <div className="sp-stock-bid-body">
                            <div className="team-nm-sec">
                                <div className="pri-sec"></div>
                                <Row >
                                    {
                                        <Col xs={12} className="position-relative">
                                            <FormGroup
                                                className=''
                                                controlId="formBasicText"
                                            >
                                                <FloatingLabel
                                                    autoComplete='off'
                                                    styles={DARK_THEME_ENABLE ? darkstkTeamNmInput : stkTeamNmInput}
                                                    id='teamName'
                                                    name='teamName'
                                                    placeholder={AL.PORTFOLIO_NAME}
                                                    type='text'
                                                    value={teamName}
                                                    disabled={true}
                                                />
                                            </FormGroup>
                                            <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                                <Tooltip id="tooltip" className="tooltip-featured">
                                                    <strong>{AL.PORTFOLIO_NAME_DESC}</strong>
                                                </Tooltip>
                                            }>
                                                <a href className="tem-info">
                                                    <i className="icon-info"></i>
                                                </a>
                                            </OverlayTrigger>
                                        </Col>
                                    }
                                </Row>
                            </div>
                            <div className="stock-pred-info">
                                {AL.PREDICT_STOCK_MSG}
                            </div>
                        </div>
                        <div className="stock-f">
                            
                            <div className="lineup-list-view">
                                {lineupArr.length > 0 && 
                                    _Map(lineupArr, (item, idx) => {
                                        return (
                                            <Table>
                                            <tbody>
                                                <tr>
                                                    <td className="stk-det-sec" onClick={(e)=>this.PlayerCardShow(e, item)}>
                                                        <img src={item.logo ? Utilities.getStockLogo(item.logo) : Images.BRAND_LOGO_FULL_PNG} alt="" />
                                                        <div className="stk-nm">
                                                            <span>{item.display_name || item.stock_name || item.name}</span>
                                                            <i className={`icon-wishlist ${item.is_wish == "1" ? ' active' : ''}`} onClick={(e) => { e.stopPropagation(); this.addToWatchList(item) }}></i>
                                                        </div>
                                                        <div className="stk-abt">
                                                            {Utilities.numberWithCommas(parseFloat(item.current_price).toFixed(2))} <span className={item.price_diff < 0 ? " danger" : ""} > {Utilities.numberWithCommas(parseFloat(item.price_diff).toFixed(2))}({item.percent_change}%) 
                                                            <i className={item.price_diff < 0 ? "icon-stock_down" : "icon-stock_up"} />
                                                        </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <FormGroup
                                                            className={`input-label-center input-transparent set-bid`}
                                                            controlId="formBasicText">
                                                            <i className="icon-ruppee"></i>
                                                            <FormControl
                                                                autoComplete='off'
                                                                id={'predictInput' + item.stock_id}
                                                                styles={inputStyle}
                                                                value={item.user_price != undefined && item.user_price}  //value={SearchVal}
                                                                placeholder={0}
                                                                type='number'
                                                                maxLength='7'
                                                                onChange={(e)=>this.handlePVChange(e,item)}
                                                            />
                                                        </FormGroup>
                                                        <a href className="remove-stk" onClick={()=>this.removeStock(item)}><i className="icon-close"></i></a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </Table>
                                        )
                                    })
                                }
                            </div>
                        </div>


                        <button disabled={!this.checkButtonEnable()} onClick={() => this.SubmitLineup()} className="btn btn-primary  btn-block btm-fix-btn">{AL.SUBMIT}</button>

                        {showConfirmationPopUp &&
                            <ConfirmationPopup lobbyDataToPopup={this.state.LobyyData} IsConfirmationPopupShow={this.ConfirmatioPopUpShow} IsConfirmationPopupHide={this.ConfirmatioPopUpHide} TeamListData={userTeamListSend} TotalTeam={TotalTeam} FixturedContest={FixturedContest} ConfirmationClickEvent={this.ConfirmEvent} CreateTeamClickEvent={this.createTeamAndJoin} fromContestListingScreen={false} createdLineUp={lineupMasterdId} isStockF={true} isStockPF={true} />
                        }

                        {showThankYouModal &&
                            <Thankyou ThankyouModalShow={this.ThankYouModalShow} ThankYouModalHide={this.ThankYouModalHide} goToLobbyClickEvent={this.goToLobby} seeMyContestEvent={this.seeMyContest} isStock={true}  />
                        }
                        {
                            showUJC &&
                            <UnableJoinContest
                                showM={showUJC}
                                hideM={this.hideUJC}
                            />
                        }
                        {
                            showPlayerCard &&
                            <Suspense fallback={<div />} >
                                <StockPlayerCard
                                    mShow={showPlayerCard}
                                    mHide={this.PlayerCardHide}
                                    playerData={playerDetails}
                                    addToWatchList={this.addToWatchList} 
                                />
                            </Suspense>

                        }
                        {showRulesModal &&
                            <SPRules mShow={showRulesModal} mHide={this.hideRulesModal} stockSetting={this.state.stockSetting} showPtsOnly={true} />
                        } 

{
                            showTimeOutAlert &&
                            <MyAlert
                                isMyAlertShow={showTimeOutAlert}
                                hidemodal={() => this.hideTimeOutModal()}
                                isFrom={'TimeOutAlert'}
                                message={AL.JOIN_BEFORE_5MIN}
                            />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}