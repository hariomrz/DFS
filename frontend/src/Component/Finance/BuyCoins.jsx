import React from 'react';
import Images from '../../components/images';
import WSManager from "../../WSHelper/WSManager";
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import { getUserBalance, getCoinPackageList, callBuyCoins, joinContest, joinContestWithMultiTeam,joinDFSTour,joinStockContestWithMultiTeam, stockJoinContest, joinContestLF, LSFJoinContest, GetPFJoinGame,getPTJoinTour,joinContestH2H, propsSaveTeam } from '../../WSHelper/WSCallings';
import { Utilities, _Map } from '../../Utilities/Utilities';
import CustomHeader from '../../components/CustomHeader';
import BuyConfirmModal from "./BuyConfirmModal";
import Thankyou from '../../Modals/Thankyou';
import { DARK_THEME_ENABLE, SELECTED_GAMET, OnlyCoinsFlow ,GameType} from "../../helper/Constants";
import InfiniteScroll from 'react-infinite-scroll-component';
import { lstat } from 'fs';
import ls from 'local-storage'

var globalThis = null;


export default class BuyCoins extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            showCheckbox: false,
            masterData: '',
            profileDetail: WSManager.getProfile(),
            userBalance: this.props.location.state && this.props.location.state.userBalance ? (parseFloat(this.props.location.state.userBalance.winning_amount) + parseFloat(this.props.location.state.userBalance.real_amount)) : '0',
            showConfirmM: false,
            amt: 0,
            packageId: '',
            packageList: [],
            pageNo: 1,
            page_size: 20,
            isLoaderShow: false,
            hasMore: true,
            isDFSTour: this.props && this.props.location && this.props.location.state && this.props.location.state.isDFSTour ? this.props.location.state.isDFSTour : false,
            isStockPF: this.props && this.props.location && this.props.location.state && this.props.location.state.isStockPF ? this.props.location.state.isStockPF : false,
            callcoinPkgList: false
        };
    }

    componentDidMount() {
        globalThis = this;
        this.callUserBalanceApi()
        if(!this.state.callcoinPkgList){
            this.setState({
                callcoinPkgList: true
            })
            this.callCoinPckgLis()
        }
    }

    goBack = (e) => {
        this.props.history.goBack();
    }

    goToAddFunds(amt) {
        if (this.props.location.state.isFrom && this.props.location.state.isFrom == 'contestList') {
            let ID = this.state.packageId;
            WSManager.setContestFromAddCoinAndJoin(ID)
            WSManager.setPaymentCalledFrom("ContestJoinBuyCoins")
            this.props.history.push({ pathname: '/add-funds', state: { amountToAdd: amt, fromBuyCoin: true,isDFSTour: this.state.isDFSTour ,isStockPF: this.state.isStockPF} })
        }
        else {
            let ID = this.state.packageId;
            WSManager.setContestFromAddCoinAndJoin(ID)
            WSManager.setPaymentCalledFrom("BuyCoins")
            if(this.state.isDFSTour){
                WSManager.setDFSTourEnabel(true)
            }
            this.props.history.push({ pathname: '/add-funds', state: { amountToAdd: amt, fromBuyCoin: true,isDFSTour: this.state.isDFSTour ,isStockPF: this.state.isStockPF} })
        }

    }

    submitAction = (amt) => {
        let Bal = parseFloat(this.state.amt);
        let TBal = parseFloat(this.state.userBalance);
        let Id = this.state.packageId;
        if (TBal >= Bal) {
            this.callBuyCoinsApi(Id)
        }
        else {
            this.goToAddFunds(Bal)
        }
    }

    callUserBalanceApi() {
        getUserBalance().then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.setState({
                    userBalance: (parseFloat(responseJson.data.user_balance.real_amount) + parseFloat(responseJson.data.user_balance.winning_amount)),
                })
                WSManager.setBalance(responseJson.data.user_balance);
            }
        })
    }

    callCoinPckgLis() {
        let param = {
            "page_no": this.state.pageNo,
            "page_size": this.state.page_size
        }
        getCoinPackageList(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                let data = responseJson.data;
                this.setState({
                    packageList: this.state.pageNo == 1 ? data : [...this.state.packageList, ...data],
                    hasMore: responseJson.data.length === this.state.page_size,
                    pageNo: this.state.pageNo + 1,
                    callcoinPkgList: false
                })
            }
        })
    }

    callBuyCoinsApi(id) {
        let param = {
            "package_id": id
        }
        callBuyCoins(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.hideConfirmBuyCoin()
                if (this.props.location.state.isFrom && (this.props.location.state.isFrom == 'contestList' || this.props.location.state.isFrom == 'SelectCaptainList' || this.props.location.state.isFrom == 'mycontest')) {
                    this.CallJoinGameApi(this.props.location.contestDataForFunds)
                }
                else {
                    this.goBack()
                }

            }
            else {
                Utilities.showToast(responseJson.global_error != "" ? responseJson.global_error : responseJson.message, 2000);
            }
        })
    }


    CallJoinGameApi(dataFromConfirmPopUp) {
        let isLF = SELECTED_GAMET == GameType.LiveFantasy ? true:false
        let isH2h = dataFromConfirmPopUp.FixturedContestItem.contest_template_id ? true : false;
        let param = {}
        if(isLF){
           param = {
                "contest_id": dataFromConfirmPopUp.FixturedContestItem.contest_id,
                "promo_code": dataFromConfirmPopUp.promoCode,
                "device_type":window.ReactNativeWebView ? WSC.deviceTypeAndroid : WSC.deviceType
            }
        }
        else if(SELECTED_GAMET == GameType.PickFantasy){
           param = {
                "contest_id": dataFromConfirmPopUp.FixturedContestItem.contest_id,
                'user_team_id': dataFromConfirmPopUp.selectedTeam.value.user_team_id
            }
        }
        else if(SELECTED_GAMET == GameType.PickemTournament){
            param = {
                "tournament_id": dataFromConfirmPopUp.FixturedContestItem.tournament_id,
            }
        }
        else if(dataFromConfirmPopUp && dataFromConfirmPopUp.isDFSTour){
            param = {
                "tournament_season_id": dataFromConfirmPopUp.lobbyDataItem.tournament_season_id,
                "tournament_id": dataFromConfirmPopUp.FixturedContestItem.tournament_id,
                "tournament_team_id": dataFromConfirmPopUp.selectedTeam.tournament_team_id ? dataFromConfirmPopUp.selectedTeam.tournament_team_id : dataFromConfirmPopUp.selectedTeam.value.tournament_team_id,
                "device_type":window.ReactNativeWebView ? WSC.deviceTypeAndroid : WSC.deviceType
            }
            contestUid = dataFromConfirmPopUp.FixturedContestItem.tournament_id
            contestAccessType = dataFromConfirmPopUp.FixturedContestItem.contest_access_type;
            isPrivate = dataFromConfirmPopUp.FixturedContestItem.is_private;
        }
        else{
            param = {
                "contest_id": isH2h ? dataFromConfirmPopUp.FixturedContestItem.contest_template_id : dataFromConfirmPopUp.FixturedContestItem.contest_id,
                "promo_code": dataFromConfirmPopUp.promoCode,
                "device_type":window.ReactNativeWebView ? WSC.deviceTypeAndroid : WSC.deviceType
            }
        }

        let contestUid = dataFromConfirmPopUp.FixturedContestItem.contest_unique_id
        let contestAccessType = dataFromConfirmPopUp.FixturedContestItem.contest_access_type;
        let isPrivate = dataFromConfirmPopUp.FixturedContestItem.is_private;

        let apiCall = isH2h ? joinContestH2H : joinContest;
        if(isLF){
            apiCall = joinContestLF;
        }
        else if(dataFromConfirmPopUp.isStockLF){
            apiCall = LSFJoinContest
        }
        else if(SELECTED_GAMET == GameType.StockFantasy || SELECTED_GAMET == GameType.StockFantasyEquity){
            apiCall = stockJoinContest
            param['lineup_master_id'] = dataFromConfirmPopUp.selectedTeam.lineup_master_id ? dataFromConfirmPopUp.selectedTeam.lineup_master_id : dataFromConfirmPopUp.selectedTeam.value.lineup_master_id
        }
        else if(dataFromConfirmPopUp.isStockPF){
            apiCall = dataFromConfirmPopUp.lineUpMasterIdArray && dataFromConfirmPopUp.lineUpMasterIdArray.length > 1 ? joinStockContestWithMultiTeam : stockJoinContest;
            if(dataFromConfirmPopUp.lineUpMasterIdArray && dataFromConfirmPopUp.lineUpMasterIdArray.length > 1){
                let resultLineup = dataFromConfirmPopUp.lineUpMasterIdArray.map(a => a.lineup_master_id);
                param['lineup_master_id'] = resultLineup
            }
            else{
                let lineupMID = dataFromConfirmPopUp.selectedTeam.lineup_master_id ? dataFromConfirmPopUp.selectedTeam.lineup_master_id : dataFromConfirmPopUp.selectedTeam.value.lineup_master_id
                param['lineup_master_id'] = lineupMID
            }
        }
        else if(dataFromConfirmPopUp.lineUpMasterIdArray && dataFromConfirmPopUp.lineUpMasterIdArray.length > 1){
            apiCall = joinContestWithMultiTeam;
            let resultLineup = dataFromConfirmPopUp.lineUpMasterIdArray.map(a => a.lineup_master_id);
            param['lineup_master_id'] = resultLineup
        }
        else if(dataFromConfirmPopUp.isDFSTour){
            apiCall = joinDFSTour;
        }
        else if(SELECTED_GAMET == GameType.PickFantasy){
            apiCall = GetPFJoinGame
        }
        else if(SELECTED_GAMET == GameType.PickemTournament){
            apiCall = getPTJoinTour;
        }
        else if(SELECTED_GAMET == GameType.PropsFantasy){  
            apiCall = propsSaveTeam;
            param= ls.get('in_params') ? ls.get('in_params') : this.props.location.params
        }
        else{
            let lineupMID = dataFromConfirmPopUp.selectedTeam.lineup_master_id ? dataFromConfirmPopUp.selectedTeam.lineup_master_id : dataFromConfirmPopUp.selectedTeam.value.lineup_master_id
            param['lineup_master_id'] = lineupMID
        }
        this.setState({ isLoaderShow: true })
        apiCall(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                setTimeout(() => {
                    WSManager.googleTrack(WSC.GA_PROFILE_ID, dataFromConfirmPopUp.isDFSTour ? 'tournamentcontestjoined' : 'contestjoin');
                    WSManager.googleTrackDaily(WSC.GA_PROFILE_ID, 'contestjoindaily');
                    this.ThankYouModalShow()
                }, 300);
                // if (contestAccessType == '1' || isPrivate == '1') {
                //     WSManager.updateFirebaseUsers(contestUid);
                // }
                if (contestAccessType == '1' || isPrivate == '1') {
                    let deviceIds = [];
                    deviceIds = responseJson.data.user_device_ids;
                    WSManager.updateFirebaseUsers(contestUid,deviceIds);
                }
                WSManager.setFromConfirmPopupAddFunds(false);
            } else {
                var errorMsg = responseJson.message != '' ? responseJson.message : responseJson.global_error

                if (errorMsg == '') {
                    for (var key in responseJson.error) {
                        errorMsg = responseJson.error[key];
                    }
                }
                if (responseJson.response_code == WSC.sessionExpireCode) {
                    this.logout();
                }
                Utilities.showToast(errorMsg, 3000);
                this.goBack()
            }
        })
    }


    showConfirmBuyCoin = (amt, pckgId) => {
        this.setState({
            showConfirmM: true,
            amt: amt,
            packageId: pckgId
        })
    }

    hideConfirmBuyCoin = () => {
        this.setState({
            showConfirmM: false
        })
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

    goToLobby = () => {
        if(SELECTED_GAMET == GameType.LiveFantasy || SELECTED_GAMET == GameType.PickFantasy || SELECTED_GAMET == GameType.PickemTournament || SELECTED_GAMET == GameType.PropsFantasy){
            this.props.history.push({pathname : '/lobby'})
            return;
        }
        let contestData = WSManager.getContestFromAddFundsAndJoin()
        let calledFrom = WSManager.getPaymentCalledFrom();

        setTimeout(() => {
            // if (calledFrom == 'mycontest') {
            //     this.props.history.push({ pathname: '/' });
            // } else {
                this.gotoContestListingClass(contestData.FixturedContestItem, contestData.lobbyDataItem)
            // }
        }, 500);
    }

    gotoContestListingClass(data, lobbyItem) {
        if((SELECTED_GAMET == GameType.DFS && this.state.isDFSTour) || (SELECTED_GAMET == GameType.StockPredict && this.state.isStockPF) || (SELECTED_GAMET == GameType.LiveStockFantasy)){            
            this.props.history.push({ pathname: '/' });
        }
        else{
            if(SELECTED_GAMET == GameType.StockFantasy || SELECTED_GAMET == GameType.StockFantasyEquity){
                data['collection_master_id'] = data.collection_id;
                let name = data.category_id.toString() === "1" ? 'Daily' : data.category_id.toString() === "2" ? 'Weekly' : 'Monthly';

                let mpath = SELECTED_GAMET == GameType.StockFantasy ? "/stock-fantasy/contest/" : '/stock-fantasy-equity/contest/';
                let contestListingPath =  mpath + data.collection_id + "/" + name;
                let CLPath = contestListingPath.toLowerCase() + "?sgmty=" + btoa(SELECTED_GAMET)
                
                this.props.history.push({
                  pathname: CLPath,
                  state: { LobyyData: data, lineupPath: CLPath },
                });

            }
            else{
                let dateformaturl = Utilities.getUtcToLocal(data.season_scheduled_date);
                dateformaturl = new Date(dateformaturl);
        
                let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
                let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        
                let home = data.home || lobbyItem.home;
                let away = data.away || lobbyItem.away;
        
                dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
                let contestListingPath = Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest-listing/' + data.collection_master_id + '/' + home + "-vs-" + away + "-" + dateformaturl + "?sgmty=" + btoa(SELECTED_GAMET)
                this.setState({ LobyyData: data });
                this.props.history.push({ pathname: contestListingPath.toLowerCase(), state: { FixturedContest: this.state.FixtureData, LobyyData: lobbyItem, isFromPM: true } })
            }
        }
    }

    seeMyContest = () => {
        this.props.history.push({ pathname: '/my-contests', state: { from: 'SelectCaptain' } });
    }
    onLoadMore() {
        if (!this.state.isLoaderShow && this.state.hasMore) {
            this.setState({ hasMore: false })
            if(!this.state.callcoinPkgList){
                this.setState({
                    callcoinPkgList: true
                })
                this.callCoinPckgLis()
            }
        }
    }


    render() {
        const HeaderOption = {
            back: true,
            isPrimary: DARK_THEME_ENABLE ? false : true,
            title: OnlyCoinsFlow == 0 ? '' : AppLabels.BUY_COINS
        }

        const { userBalance, showConfirmM, amt, packageList, showThankYouModal } = this.state;
        let is_props = this.props.location.state.isProps ? true : false
        ls.set('in_params', this.props.location.params)
        ls.set('isProps', is_props)


        return (
            <div className="web-container buy-coins-wrap esport-wrap">
                <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                {OnlyCoinsFlow == 0 && <div className="buy-coin-header">
                    <div className="buy-sec">
                        {AppLabels.BUY_COINS}
                        <span className="userbal">{Utilities.getMasterData().currency_code} {parseFloat(userBalance).toFixed(2)}</span>
                    </div>
                    <div className="ava-sec">
                        {AppLabels.AVAIL_BAL}
                    </div>
                    <div className="ava-sec">
                        ({AppLabels.DEPOSIT} + {AppLabels.WINNINGS})
                    </div>
                </div>}
                <InfiniteScroll
                    dataLength={this.state.packageList.length}
                    next={() => this.onLoadMore()}
                    hasMore={!this.state.isLoaderShow && this.state.hasMore}
                    scrollableTarget={'scrollableTarget'}
                    loader={
                        this.state.isLoadMoreLoaderShow &&
                        <h4 className='table-loader'>{AppLabels.LOADING_MSG}</h4>
                    }>
                    <div className="coins-opt-sec">
                        {
                            packageList && packageList.length > 0 && _Map(packageList, (item, idx) => {
                                return (
                                    <div className="coins-card">
                                        <img src={Images.COINIMG} alt="" />
                                        <div className="coin-cnt">{item.coins}</div>
                                        <div className="text-center">
                                            <a href className="btn btn-rounded" onClick={() => this.showConfirmBuyCoin(item.amount, item.coin_package_id)}>{Utilities.getMasterData().currency_code} {Utilities.kLowerFormatter(item.amount)}</a>
                                        </div>
                                    </div>
                                )
                            })
                        }
                    </div>

                </InfiniteScroll>
                {OnlyCoinsFlow == 0 && <div className="btm-bxt">
                    {AppLabels.AMOUNT_DEDUCTION_MSG}
                </div>}
                {
                    showConfirmM &&
                    <BuyConfirmModal hide={this.hideConfirmBuyCoin} show={showConfirmM} submitAction={this.submitAction} amt={amt} userBalance={userBalance} />
                }
                {showThankYouModal &&
                    <Thankyou ThankyouModalShow={this.ThankYouModalShow} ThankYouModalHide={this.ThankYouModalHide} goToLobbyClickEvent={this.goToLobby} seeMyContestEvent={this.seeMyContest} isProps={this.props.location.state.isProps} />
                }

            </div>

        );
    }
}
