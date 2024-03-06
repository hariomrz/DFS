import React from 'react';
import ls from 'local-storage';
import { Modal, Tabs, Tab, Table, ProgressBar, Panel, Row, OverlayTrigger, Tooltip ,Alert } from 'react-bootstrap';
import Images from '../../components/images';
import { MyContext } from '../../InitialSetup/MyProvider';
import WSManager from "../../WSHelper/WSManager";
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import InfiniteScroll from 'react-infinite-scroll-component';
import CountdownTimer from '../../views/CountDownTimer';
import { Utilities, _Map, _filter } from '../../Utilities/Utilities';
import * as Constants from "../../helper/Constants";
import CollectionInfoModal from "../../Modals/CollectionInfo";
import {getContestDetailsLF,getContestUserListLF } from '../../WSHelper/WSCallings';
import { MomentDateComponent } from '../../Component/CustomComponent';
import { FtpPrizeComponent } from '../../Component/FreeToPlayModule';
//import { ALL } from 'dns';


var masterDataResponse = null;
var fantasyListArray = null;
var selectedSportsVar = null;
var isTimerOver = false;

var hasMore = false;

export default class LFContestDetailsModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            prizeList: [],
            ContestDetail: "",
            merchandiseList: [],
            MiniLeagueList: [],
            miniLeagueprizeList: [],
            miniLeagueMerchandiseList: [],
            bonus_scoring_rules: [],
            normal_scoring_rules: [],
            strike_scoring_rules: [],
            economy_scoring_rules: [],
            pitching_scoring_rules: [],
            hitting_scoring_rules: [],
            prizeDistributionDetail: [],
            isMiniLeaguePrize: '',
            MiniLeagueData: '',
            userList: [],
            playerCount: 0,
            joinBtnVisibility: false,
            isLoading: false,
            limit: 20,
            sportsSelected: Constants.AppSelectedSport,
            userJoinCount: WSManager.loggedIn() ? -1 : 0,
            contestStatus: this.props.contestStatus,
            showCollectionInfo: false,
            total_user_joined: this.props.OpenContestDetailFor ? this.props.OpenContestDetailFor.total_user_joined : 0,
            page_no: 1,
            maxcurrentStatus: true,
            season_game_uid: '',
            showError: false,
            isPrivateContest: 0,
            showMaxToggle: true,
            activeSTIDx: this.props.activeTabIndex,
            isRFEnable: Utilities.getMasterData().a_reverse == '1',
            isXPEnable: Utilities.getMasterData().a_coin == '1' && Utilities.getMasterData().a_xp_point == '1' ? true : false,
            stockFRules: {
                daily: [
                    AppLabels.STOCK_DR1,
                    AppLabels.STOCK_DR2,
                    AppLabels.STOCK_DR3,
                    AppLabels.STOCK_DR4,
                    AppLabels.STOCK_DR5
                ],
                
            },
            fixtureBoosterList:[]
        };
    }

    convertIntoWhole = (x) => {
        var no = Math.round(x)
        return no;
    }

    ShowProgressBar = (join, total) => {
        return join * 100 / total;
    }

    isMaximumSelected = (isSelected) => {
        this.setState({ maxcurrentStatus: isSelected });
    }

    /**
    * 
    * @description method to display collection info model.
    */
    CollectionInfoShow = (event) => {
        event.stopPropagation();
        this.setState({
            showCollectionInfo: true
        }, () => {
        });
    }
    /**
     * 
     * @description method to hide collection info model.
     */
    CollectionInfoHide = () => {
        this.setState({
            showCollectionInfo: false,
        });
    }

    componentDidMount() {
        this.ContestDetail(this.props.OpenContestDetailFor);
        this.getMasterDataFromLS();
    }

    getMasterDataFromLS() {
        selectedSportsVar = Constants.AppSelectedSport;
        masterDataResponse = Utilities.getMasterData()
        if (masterDataResponse && masterDataResponse != null) {
            fantasyListArray = masterDataResponse.fantasy_list;
            for (var obj of fantasyListArray) {
                if (selectedSportsVar == obj.sports_id) {
                    this.setState({
                        playerCount: obj.team_player_count
                    })
                    break;
                }
            }
        }
    }

    ContestDetail = async (data) => {
        var param = {
            "contest_id": data.contest_id,
        }
        this.setState({ isLoading: true })
        //var api_response_data = await getContestDetailsLF(param);
        getContestDetailsLF(param).then((responseJson) => {  
            if (responseJson.response_code == WSC.successCode) {
                let api_response_data = responseJson.data;
                this.setState({ isLoading: false })
          
            // let normal_scoring_rules = [];
            // let bonus_scoring_rules = [];
            // let strike_scoring_rules = [];
            // let economy_scoring_rules = [];
            // let pitching_scoring_rules = [];
            // let hitting_scoring_rules = [];
            // _Map(api_response_data.scoring_rules, (o) => {
            //     let masterSID = o.master_scoring_category_id;
            //     if(masterSID === '14' || masterSID === '18' ||
            //     masterSID === '19' || masterSID === '20' ||
            //     masterSID === '23' || masterSID === '24' ||
            //     masterSID === '25' || masterSID === '27'){
            //         normal_scoring_rules.push(o)
            //     }else if(masterSID === '15' || masterSID === '26'){
            //         bonus_scoring_rules.push(o)
            //     }else if(masterSID === '17'){
            //         strike_scoring_rules.push(o)
            //     }else if(masterSID === '16'){
            //         economy_scoring_rules.push(o)
            //     }else if(masterSID === '21'){
            //         pitching_scoring_rules.push(o)
            //     }else if(masterSID === '22'){
            //         hitting_scoring_rules.push(o)
            //     }
                
            // })

            if (this.props.activeTabIndex == 2) {
                this.getUserList(this.props.OpenContestDetailFor, 1);
            }


            this.setState({
                season_game_uid: api_response_data.season_game_uid,
                ContestDetail: api_response_data,
                // normal_scoring_rules: normal_scoring_rules,
                // bonus_scoring_rules: bonus_scoring_rules,
                // strike_scoring_rules: strike_scoring_rules,
                // economy_scoring_rules: economy_scoring_rules,
                // pitching_scoring_rules: pitching_scoring_rules,
                // hitting_scoring_rules: hitting_scoring_rules,
                // prizeDistributionDetail: api_response_data.prize_detail,
                // prizeList: api_response_data.prize_detail,
                merchandiseList: api_response_data.merchandise,
                isPrivateContest: api_response_data.is_private || 0,
            }, () => {
                if(this.state.ContestDetail.is_joined != '0' && this.props.isFromLFShareContest){
                    this.showPrivateContestError()

                }

                let prizeListVar = _filter(api_response_data.prize_detail, (o) => {
                    return o.max_value != o.min_value;
                })
                // let showMaxToggle = prizeListVar && prizeListVar.length > 0;
                let minDiff = Utilities.minuteDiffValue({ date: api_response_data.game_starts_in }) > 0;
                let showMaxToggle = (api_response_data.is_prize_reset == 1 && minDiff) ? false : (prizeListVar && prizeListVar.length > 0);
                this.setState({
                    showMaxToggle: showMaxToggle,
                    maxcurrentStatus: (api_response_data.is_prize_reset == 1 && minDiff ) ? false : true
                })
            })
            if(api_response_data.sports_id){
                ls.set('selectedSports', api_response_data.sports_id);
                Constants.setValue.setAppSelectedSport(api_response_data.sports_id);
            }
           
            else {
                this.setState({
                    joinBtnVisibility: (this.state.contestStatus !== 1 && this.state.contestStatus !== 2)
                })
            }
            }
            
        })
        
    }

    

    

    

    getUserList(data = {}, page_no = 1) {
        var param = {
            "contest_id": data.contest_id,
            "page_no": page_no,
            "page_size": this.state.limit,
        }
        this.setState({ isLoadMoreLoaderShow: page_no > 1, isLoading: true })
          getContestUserListLF(param).then((responseJson) => {    
            setTimeout(() => {
                this.setState({ isLoading: false })
            }, 100);
            if (responseJson.response_code == WSC.successCode) {
                let mergeList = [];
                if (page_no == 1) {
                    mergeList = responseJson.data.users;
                    this.setState({ total_user_joined: responseJson.data.total_user_joined })
                }
                else {
                    mergeList = [...this.state.userList, ...responseJson.data.users]
                }
                hasMore = responseJson.data.users.length === this.state.limit;
                this.setState({ userList: mergeList, page_no: this.state.page_no + 1 })
            }
        })
    }

    getWinnerCount(prizeDistributionDetail) {
        if (prizeDistributionDetail.length > 0) {
            if ((prizeDistributionDetail[prizeDistributionDetail.length - 1].max) > 1) {
                return prizeDistributionDetail[prizeDistributionDetail.length - 1].max + " " + AppLabels.WINNERS
            } else {
                return prizeDistributionDetail[prizeDistributionDetail.length - 1].max + " " + AppLabels.WINNER
            }
        }
    }

    joinGame() {
        this.props.history.push({ pathname: '/lineup' })
    }

    contestDetailBtnVisibility(contestDetailsState) {
        let totalUserJoined = parseInt(this.state.total_user_joined)
        let maxContestSize = parseInt(contestDetailsState.size)
        let userJoinedCount = this.state.userJoinCount;
        let multiLineupCount = parseInt(contestDetailsState.multiple_lineup)

        if (isTimerOver) {
            this.setState({
                joinBtnVisibility: false,
                showError: this.props.showPCError
            })
        } else {
            if (totalUserJoined >= maxContestSize) {
                this.setState({
                    joinBtnVisibility: false,
                    showError: this.props.showPCError
                })
            } else {
                if ((this.state.contestStatus && this.state.contestStatus == Constants.CONTEST_UPCOMING) || (this.state.ContestDetail.status == Constants.CONTEST_UPCOMING)) {
                    if ((multiLineupCount == 0 || multiLineupCount == 1) && userJoinedCount == 0) {
                        this.setState({
                            joinBtnVisibility: (this.state.contestStatus !== 1 && this.state.contestStatus !== 2),
                            showError: this.props.showPCError
                        })
                    } else if (multiLineupCount > 1 && (userJoinedCount < multiLineupCount)) {
                        this.setState({
                            joinBtnVisibility: (this.state.contestStatus !== 1 && this.state.contestStatus !== 2),
                            showError: this.props.showPCError
                        })
                    } else {   //New scenerio can be added here....
                        this.setState({
                            joinBtnVisibility: false,
                            showError: this.props.showPCError
                        })
                    }
                }
                else {
                    this.setState({
                        joinBtnVisibility: false,
                        showError: this.props.showPCError
                    })
                }
            }
        }
    }
    getIsTimerOver(contestDetailsState) {
        if (contestDetailsState.current_timestamp > contestDetailsState.game_starts_in) {
            isTimerOver = true;
        } else {
            isTimerOver = false;
        }
        this.contestDetailBtnVisibility(contestDetailsState)
    }

    onLoadMore = () => {
        if (!this.state.isLoading && hasMore)
            this.getUserList(this.props.OpenContestDetailFor, this.state.page_no);
    }

    ontabSelect = (tab) => {
        this.setState({
            activeSTIDx: tab
        })
        if (tab == 2) {
            if (this.state.userList.length == 0)
                this.getUserList(this.props.OpenContestDetailFor, 1);
        }
    }
    getContestPrizeDetails = (ContestDetail) => {
        this.setState({
            isMiniLeaguePrize: false,
        }, () => {
            this.props.history.push({
                pathname: '/all-prizes/' + "contestPrize" + "/" + false, state: {
                    LobyyData: this.state.LobyyData,
                    MiniLeagueData: ContestDetail,
                    isMiniLeaguePrize: this.state.isMiniLeaguePrize

                }
            })
        })
    }
    getPrizeDetail = (item, LobyyData) => {
        this.setState({
            isMiniLeaguePrize: true,
        }, () => {
            this.props.history.push({
                pathname: '/all-prizes/' + item.mini_league_uid + "/" + true, state: {
                    LobyyData: this.state.LobyyData,
                    MiniLeagueData: item,
                    isMiniLeaguePrize: this.state.isMiniLeaguePrize

                }
            })
        })
    }

    showPrivateContestError = () => {
        this.setState({
            showError: false
        }, () => {
            Utilities.showToast(AppLabels.ERROR_MSG, 5000);
        })
    }
    setCurrentMaxPrize = (minMaxValue, prizeItem) => {
        var finalPrize;
        var maxMini;
        if (prizeItem.prize_type == 2) {
            maxMini = prizeItem.max - prizeItem.min + 1;
            finalPrize = (Math.ceil(minMaxValue) / maxMini)
        } else {
            maxMini = prizeItem.max - prizeItem.min + 1;
            finalPrize = (parseFloat(minMaxValue).toFixed(2) / maxMini)
        }
        finalPrize = finalPrize.toFixed(0);
        finalPrize = Utilities.numberWithCommas(finalPrize);
        return finalPrize;
    }
    getWinnerCounts(prizeList) {

        if (prizeList != '') {
            if ((prizeList[prizeList.length - 1].max) > 1) {
                return prizeList[prizeList.length - 1].max + " " + AppLabels.WINNERS
            } else {
                return prizeList[prizeList.length - 1].max + " " + AppLabels.WINNER
            }
        } else {
            return '0 Winner';
        }
    }

    getPrizeAmount = (prize_data) => {
        let is_tie_breaker = 0;
        let prizeAmount = { 'real': 0, 'bonus': 0, 'point': 0 };
        return (
            <React.Fragment>
                {
                    prize_data && prize_data.map(function (lObj, lKey) {
                        var amount = 0;
                        if (lObj.max_value) {
                            amount = parseFloat(lObj.max_value);
                        } else {
                            amount = parseFloat(lObj.amount);
                        }
                        if (lObj.prize_type == 3) {
                            is_tie_breaker = 1;
                        }
                        if (lObj.prize_type == 0) {
                            prizeAmount['bonus'] = parseFloat(prizeAmount['bonus']) + amount;
                        } else if (lObj.prize_type == 2) {
                            prizeAmount['point'] = parseFloat(prizeAmount['point']) + amount;
                        } else {
                            prizeAmount['real'] = parseFloat(prizeAmount['real']) + amount;
                        }
                    })
                }

                {
                    is_tie_breaker == 0 && prizeAmount.real > 0 ?
                        <span className="contest-prizes">{Utilities.getMasterData().currency_code}{parseFloat(prizeAmount.real).toFixed(0)}</span>
                        : is_tie_breaker == 0 && prizeAmount.bonus > 0 ? <span className="contest-prizes margin-contest"><i className="icon-bonus" />{parseFloat(prizeAmount.bonus).toFixed(0)}</span>
                            : is_tie_breaker == 0 && prizeAmount.point > 0 ? <span style={{ marginLeft: '13px', display: 'inlineBlock' }}> <img alt='' className="img-coin" src={Images.IC_COIN} />{parseFloat(prizeAmount.point).toFixed(0)}</span>
                                : AppLabels.PRIZES
                }

            </React.Fragment>
        )


    }

    showNumberOfEntries=(size)=>{
        size = Utilities.numberWithCommas(parseInt(size || '0'))
        return size
    }

    showUserProfile=()=>{
        this.props.history.push({ pathname: '/my-profile' })
    }

    aadharConfirmation = () => {
        Utilities.showToast(AppLabels.YOU_CANNOT_JOIN_CONTEST_VERIFICATION, 3000);
        this.props.history.push('/aadhar-verification')
    }
    

    render() {
        const { IsContestDetailShow, IsContestDetailHide, onJoinBtnClick, LobyyData, OpenContestDetailFor } = this.props;
        const { ContestDetail, normal_scoring_rules, bonus_scoring_rules, economy_scoring_rules, pitching_scoring_rules, hitting_scoring_rules, strike_scoring_rules, prizeDistributionDetail, playerCount, joinBtnVisibility, userList, sportsSelected, allowCollection, showCollectionInfo, showError, isPrivateContest ,showMaxToggle, activeSTIDx, isRFEnable, isXPEnable } = this.state;

        let lengthFixture = LobyyData.match_list ? LobyyData.match_list.length : 0
        let match_item = lengthFixture >= 1 ? LobyyData.match_list[0] : LobyyData
        let sponserImage = ContestDetail.sponsor_logo && ContestDetail.sponsor_logo != null ? ContestDetail.sponsor_logo : 0
        let miniLeagueListLengthStatus = this.state.MiniLeagueList && this.state.MiniLeagueList.length > 1 ? 2 : this.state.MiniLeagueList && this.state.MiniLeagueList.length == 1 ? 1 : 0

        var isPrivateEnable = process.env.REACT_APP_PRIVATE_CONTEST_WINNING_DISABLE == 1 ? 1 : 0;
        var showtab = isPrivateContest == 1 ? (process.env.REACT_APP_PRIVATE_CONTEST_WINNING_DISABLE == 1 ? false : true) : true;

        let user_data = ls.get('profile');

        if(this.props.isSecIn){
            LobyyData['game_starts_in'] = ContestDetail.game_starts_in
        }
        let sfCat = LobyyData.category_id ? (LobyyData.category_id.toString() === "1" ? AppLabels.DAILY : LobyyData.category_id.toString() === "2" ? AppLabels.WEEKLY : AppLabels.MONTHLY) : '';

        let StockEndDate = this.props.isStockF ? (LobyyData && LobyyData.end_date ? LobyyData.end_date : ContestDetail && ContestDetail.end_date ? ContestDetail.end_date : '') : '';
        let StockSSDate = this.props.isStockF ? (LobyyData && LobyyData.season_scheduled_date ? LobyyData.season_scheduled_date : ContestDetail && ContestDetail.season_scheduled_date ? ContestDetail.season_scheduled_date : '') : '';

        let SCID = this.props.isStockF ? (LobyyData && LobyyData.category_id ? LobyyData.category_id : ContestDetail && ContestDetail.category_id ? ContestDetail.category_id : '') : ''
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div>
                        <Modal show={IsContestDetailShow}
                            className={"contest-detail-dialog" + (showCollectionInfo ? ' contest-detail-hide' : ' ')}
                            onHide={IsContestDetailHide} bsSize="large"
                            dialogClassName={"contest-detail-modal lf-contest-detail bg-white primary-h contest-details-modal-white-lebel " + (!joinBtnVisibility ? 'contest-detail-with-btn ' : '') + (isPrivateEnable == 1 && isPrivateContest == 1 ? ' contest-with-two-tabs' : '') +
                                (
                                    LobyyData ? (LobyyData.match_list && LobyyData.match_list.length > 1 ? 'contest-detail-with-collection' : '') :
                                        (ContestDetail.match_list && ContestDetail.match_list.length > 1 ? 'contest-detail-with-collection' : '')
                                )
                            }>
                            <Modal.Header className={LobyyData ? (LobyyData.match_list && LobyyData.match_list.length > 1 ? 'header-with-collection' : '') : (ContestDetail.match_list && ContestDetail.match_list.length > 1 ? 'header-with-collection' : '')}>
                                <Modal.Title >
                                    <a href onClick={IsContestDetailHide} className="modal-close">
                                        <i className="icon-close"></i>
                                    </a>
                                    <div style={{maxWidth:270,height:30}} className="match-heading header-content">
                                       
                                        <div className="team-header-detail">
                                            {
                                             
                                                <div className="team-header-content text-uppercase">
                                                    <span>{ContestDetail.home} <span className='text-lowercase'> {AppLabels.VS} </span>{ContestDetail.away}
                                                    <span style={{textTransform:'none'}}>{ContestDetail.overs ? ' Over ' + ContestDetail.overs : '' }</span>
                                                    
                                                        </span>
                                                </div>
                                            
                                            }
                                            
                                        </div>
                                      

                                    </div>

                                </Modal.Title>
                                 {(!WSManager.loggedIn() || ContestDetail.is_joined == '0') &&
                                    <div className="header-section-contest-entry">
                                         <div className="center-alignment">
                                            <span className="entry-fee-btn" onClick={Utilities.getMasterData().a_aadhar == 1 ? (this.props.profileShow && this.props.profileShow.aadhar_status == "1") ? () => onJoinBtnClick(ContestDetail) :  () => this.aadharConfirmation() : () => onJoinBtnClick(ContestDetail)}>      
                                                {
                                                    ContestDetail.entry_fee > 0 ?
                                                        <>
                                                            {
                                                                ContestDetail.currency_type == 2 ?
                                                                    <img className="img-coin" alt='' src={Images.IC_COIN} />
                                                                    :
                                                                    Utilities.getMasterData().currency_code
                                                            }
                                                            {Utilities.numberWithCommas(ContestDetail.entry_fee)} {AppLabels.JOIN }
                                                        </>
                                                        : 
                                                            AppLabels.JOIN_FOR_FREE
                                                }
                                            </span>

                                        </div>
                                    </div>
                                }

                            </Modal.Header>
                            {
                                this.state.ContestDetail.custom_message != '' && this.state.ContestDetail.custom_message != null && <div style={{marginTop:5,marginBottom:5,paddingLeft:15,paddingRight:15}} className="m-b-15 padding-strip">
                                    <Alert variant="warning" className="alert-warning msg-alert-container">
                                        <div className="msg-alert-wrapper">
                                            <span className=""><i className="icon-megaphone"></i></span>
                                            <span>{this.state.ContestDetail.custom_message}</span>
                                        </div>
                                    </Alert>
                                </div>
                            }
                            <Modal.Body>
                                <Tabs id={'contest-detail-tab'} onSelect={this.ontabSelect} defaultActiveKey={this.props.activeTabIndex} >
                                
                                    {
                                        showtab &&
                                        <Tab eventKey={1} title={AppLabels.WINNINGS}>
                                            {
                                             <div className="winning-section">
                                                    <div className="winning-tab-header">
                                                        <div className="table total-entries-table">
                                                            <div className="table-cell">
                                                                <div className="label">{AppLabels.MIN} {AppLabels.ENTRIES}</div>
                                                                <div className="value">{this.showNumberOfEntries(ContestDetail.minimum_size)}</div>
                                                            </div>
                                                            <div className="table-cell">
                                                                <div className="label">{AppLabels.MAX} {AppLabels.ENTRIES}</div>
                                                                <div className="value">{this.showNumberOfEntries(ContestDetail.size)}</div>
                                                            </div>
                                                        </div>

                                                        </div>
                                                        <div className="center-alignment">
                                            {
                                                ContestDetail.sponsor_contest_dtl_image &&
                                                <div className="sponser-section-strip-header sponser-img-sec">
                                                    {
                                                        <div className="sponser-logo-view">
                                                            {
                                                                window.ReactNativeWebView ?
                                                                    <a
                                                                        href
                                                                        onClick={(event) => Utilities.callNativeRedirection(Utilities.getValidSponserURL(ContestDetail.sponsor_link, event))}>
                                                                        <img alt='' className="lobby_sponser-image sponser-card-image" style={{ resizeMode: 'contain' }} src={Utilities.getSponserURL(ContestDetail.sponsor_contest_dtl_image)} />
                                                                    </a>

                                                                    :
                                                                    <a
                                                                        href={Utilities.getValidSponserURL(ContestDetail.sponsor_link)}
                                                                        onClick={(event) => event.stopPropagation()}
                                                                        target='__blank'>
                                                                        <img alt='' className="lobby_sponser-image sponser-card-image" style={{ resizeMode: 'contain' }} src={Utilities.getSponserURL(ContestDetail.sponsor_contest_dtl_image)} />
                                                                    </a>

                                                            }

                                                        </div>
                                                    }
                                                </div>
                                            }

                                        </div>
                                                        {
                                                            ContestDetail.is_tie_breaker == 0 && showMaxToggle ?
                                                            <div className="max-current-sec">
                                                                <div className="switch-container">
                                                                    <div className="switch" >
                                                                        <input type="radio" className="switch-input" name="view" value="week" id="week" defaultChecked />
                                                                        <label for="week" className="switch-label switch-label-off" onClick={() => this.isMaximumSelected(true)}>{AppLabels.MAXIMUM}</label>
                                                                        <input type="radio" className="switch-input" name="view" value="month" id="month" />
                                                                        <label for="month" className="switch-label switch-label-on" onClick={() => this.isMaximumSelected(false)}>{AppLabels.CURRENT}</label>
                                                                        <span className="switch-selection"></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        :
                                                            <div className="table-heading">
                                                                <div className="text-center" style={{ width: '100%' }}>
                                                                    {AppLabels.DISTRIBUTION}
                                                                </div>
                                                            </div>
                                                        }
                                                        {/* <div className={"table-heading" + (ContestDetail.is_tie_breaker == 0 ? '' : ' switch-text-align')}>
                                                            {
                                                                ContestDetail.is_tie_breaker == 0 ?
                                                                    <div style={{ float: 'left', width: '80%' }}>
                                                                        {AppLabels.DISTRIBUTION}
                                                                    </div>
                                                                    :
                                                                    <div style={{ width: '100%' }}>
                                                                        {AppLabels.DISTRIBUTION}
                                                                    </div>

                                                            }

                                                            {
                                                                ContestDetail.is_tie_breaker == 0 && showMaxToggle && 
                                                                ContestDetail.is_prize_reset != 1 && (this.state.contestStatus === Constants.CONTEST_LIVE || this.state.contestStatus === Constants.CONTEST_COMPLETED) &&
                                                                // Utilities.minuteDiffValue({ date: ContestDetail.game_starts_in }) < 0 &&
                                                                <div className="switch-container">
                                                                    <div className="switch" >
                                                                        <input type="radio" className="switch-input" name="view" value="week" id="week" defaultChecked />
                                                                        <label for="week" className="switch-label switch-label-off" onClick={() => this.isMaximumSelected(true)}>{AppLabels.MAXIMUM}</label>
                                                                        <input type="radio" className="switch-input" name="view" value="month" id="month" />
                                                                        <label for="month" className="switch-label switch-label-on" onClick={() => this.isMaximumSelected(false)}>{AppLabels.CURRENT}</label>
                                                                        <span className="switch-selection"></span>
                                                                    </div>
                                                                </div>
                                                            }
                                                        </div> */}
                                                        <Table responsive>
                                                            <tbody>

                                                                {
                                                                    ContestDetail.is_tie_breaker == 0 ?
                                                                        _Map(ContestDetail.prize_detail, (prizeItem, idx) => {
                                                                            if(showMaxToggle && !this.state.maxcurrentStatus && ContestDetail.total_user_joined){
                                                                                if(ContestDetail.total_user_joined == 0 && idx > 0){
                                                                                    return ''
                                                                                }else if(prizeItem.min > ContestDetail.total_user_joined && ContestDetail.total_user_joined > 0){
                                                                                    return ''
                                                                                }
                                                                            }
                                                                            return (
                                                                                <tr key={idx} className='winning-tbl'>
                                                                                    <td className='rank-fz'>{prizeItem.min == prizeItem.max ? prizeItem.min : prizeItem.min + ' - ' + prizeItem.max}</td>
                                                                                    
                                                                                    <React.Fragment>

                                                                                        <div>
                                                                                            {
                                                                                                prizeItem.prize_type ?
                                                                                                    (prizeItem.prize_type == 0) ?
                                                                                                        <div className='winning'>
                                                                                                            <span className="contest-prizes">
                                                                                                                {<i style={{ display: 'inlineBlock' }} className="icon-bonus"></i>}

                                                                                                            {(this.state.maxcurrentStatus ? this.setCurrentMaxPrize(prizeItem.max_value, prizeItem) : this.setCurrentMaxPrize(prizeItem.min_value, prizeItem))}
                                                                                                            </span>
                                                                                                        </div>
                                                                                                    :
                                                                                                    (prizeItem.prize_type == 1) ?
                                                                                                        <div className='winning'>

                                                                                                            <span className="contest-prizes" style={{ display: 'inlineBlock' }}>{Utilities.getMasterData().currency_code}
                                                                                                            {(this.state.maxcurrentStatus ? this.setCurrentMaxPrize(prizeItem.max_value, prizeItem) : this.setCurrentMaxPrize(prizeItem.min_value, prizeItem))}
                                                                                                            </span>
                                                                                                        </div>
                                                                                                    :
                                                                                                    (prizeItem.prize_type == 2) ?
                                                                                                        <div className='winning'>
                                                                                                            {
                                                                                                                <span className="contest-prizes">
                                                                                                                    <img style={{ marginTop: "0px" }} src={Images.IC_COIN} width="10px" height="10px" />
                                                                                                                    {(this.state.maxcurrentStatus ?  this.setCurrentMaxPrize(prizeItem.max_value, prizeItem) : this.setCurrentMaxPrize(prizeItem.min_value, prizeItem))}
                                                                                                                </span>
                                                                                                            }

                                                                                                        </div>
                                                                                                    :
                                                                                                    (prizeItem.prize_type == 3) ?
                                                                                                        <div className='winning'>
                                                                                                            {<span className="contest-prizes" style={{ display: 'inlineBlock' }}>{this.state.maxcurrentStatus ? prizeItem.max_value : prizeItem.min_value}</span>}

                                                                                                        </div>
                                                                                                    :
                                                                                                    (prizeItem.prize_type == 4) ?
                                                                                                        <div className='winning'>
                                                                                                                        {<span className="contest-prizes" style={{ display: 'inlineBlock' }}>{ Utilities.getMasterData().currency_code +prizeItem.amount}</span>}

                                                                                                    </div>
                                                                                                    : ''
                                                                                                    :
                                                                                                    (ContestDetail.prize_type == 0) ?
                                                                                                        <React.Fragment>
                                                                                                            {(prizeItem.amount === "0" || prizeItem.amount === "0.00") ?
                                                                                                                <td className="text-right">{AppLabels.PRACTICE}</td>
                                                                                                                :
                                                                                                                <td className="text-right">
                                                                                                                    <span className="amt-type">
                                                                                                                        <i style={{ display: 'inlineBlock' }} className="icon-bonus"></i>
                                                                                                                    </span>
                                                                                                                    {prizeItem.amount}
                                                                                                                </td>
                                                                                                            }
                                                                                                        </React.Fragment>
                                                                                                        :
                                                                                                        (ContestDetail.prize_type == 1) &&
                                                                                                        <React.Fragment>
                                                                                                            {
                                                                                                                (prizeItem.amount === "0" || prizeItem.amount === "0.00") ?
                                                                                                                    <td className="text-right">{AppLabels.PRACTICE}</td>
                                                                                                                    :
                                                                                                                    <td className="text-right">
                                                                                                                        <span className="amt-type">
                                                                                                                            {Utilities.getMasterData().currency_code}
                                                                                                                        </span>
                                                                                                                        {this.convertIntoWhole(prizeItem.amount)}
                                                                                                                    </td>
                                                                                                            }
                                                                                                        </React.Fragment>
                                                                                            }

                                                                                        </div>
                                                                                    </React.Fragment>


                                                                                </tr>
                                                                            )
                                                                        })

                                                                        :
                                                                        ContestDetail.prize_detail
                                                                        &&
                                                                        <React.Fragment>
                                                                            {
                                                                                (Constants.SELECTED_GAMET == Constants.GameType.DFS || Constants.SELECTED_GAMET == Constants.GameType.MultiGame || Constants.SELECTED_GAMET == Constants.GameType.StockFantasy || Constants.SELECTED_GAMET == Constants.GameType.StockFantasyEquity || Constants.SELECTED_GAMET == Constants.GameType.LiveFantasy) && ContestDetail.is_tie_breaker == 1 &&
                                                                                    <>
                                                                                        {
                                                                                            <Row className="Ftp-prizes no-margin p-v-ms">
                                                                                            {
                                                                                                ContestDetail.prize_detail && ContestDetail.prize_detail.length > 0 && ContestDetail.prize_detail.map((item, index) => {
                                                                                                    return (
                                                                                                        <FtpPrizeComponent from={"ContestDetail"} prizeListitem={item} merchandiseList={this.state.merchandiseList} />
    
                                                                                                    );
                                                                                                })
                                                                                            }
    
                                                                                        </Row>
                                                                                        }
                                                                                    </>
                                                                                       }
                                                                        </React.Fragment>

                                                                }

                                                                {
                                                                    (ContestDetail.consolation_prize && prizeDistributionDetail.length > 0) && <tr>
                                                                        <td>{(prizeDistributionDetail[prizeDistributionDetail.length - 1].max + 1) + ' - ' + ContestDetail.size}</td>
                                                                        <td className="text-right">
                                                                            <span className="amt-type">
                                                                                {
                                                                                    ContestDetail.consolation_prize.prize_type == 0
                                                                                        ?
                                                                                        <i className="icon-bonus" />
                                                                                        :
                                                                                        <img className="coin-img" src={Images.IC_COIN} alt="" />
                                                                                }
                                                                            </span>
                                                                            {ContestDetail.consolation_prize.value}
                                                                        </td>
                                                                    </tr>
                                                                }
                                                            </tbody>
                                                        </Table>
                                                        {ContestDetail.guaranteed_prize != 2 && ContestDetail.minimum_size != ContestDetail.size && ContestDetail.entry_fee > 0 &&
                                                            <div className="tab-description">

                                                                <span className='star'>
                                                                    <sup>*</sup>
                                                                </span>
                                                                {AppLabels.PRIZE_MSG1} {ContestDetail.minimum_size} {AppLabels.PRIZE_MSG2} {ContestDetail.max_prize_pool}.<br />
                                                                {AppLabels.PRIZE_MSG3} {ContestDetail.minimum_size} {AppLabels.PRIZE_MSG4}
                                                            </div>
                                                        }
                                                        {
                                                            ContestDetail.guaranteed_prize == 2 && ContestDetail.total_user_joined >= ContestDetail.minimum_size &&
                                                            <div className="tab-description">
                                                                {AppLabels.GUARANTEED_PRIZE_MSG4}
                                                            </div>
                                                        }
                                                        {((ContestDetail.guaranteed_prize == 2 && (ContestDetail.total_user_joined < ContestDetail.minimum_size)) || (ContestDetail.guaranteed_prize != 2 && ContestDetail.minimum_size == ContestDetail.size)) &&
                                                            <div className="tab-description">
                                                                {AppLabels.GUARANTEED_PRIZE_MSG1} {ContestDetail.minimum_size} {AppLabels.GUARANTEED_PRIZE_MSG2} {ContestDetail.minimum_size} {AppLabels.GUARANTEED_PRIZE_MSG3}
                                                            </div>
                                                        }
                                                        {
                                                            !this.state.maxcurrentStatus && ContestDetail.minimum_size > this.state.total_user_joined &&
                                                            <div className="tab-description p-0">
                                                                {AppLabels.THIS_WILL_BE_UPDATED} {ContestDetail.minimum_size} {AppLabels.PEOPLE_JOINED_THIS_CONTEST}
                                                            </div>
                                                        }
                                                        {/* {
                                                            ((ContestDetail.guaranteed_prize == 2 && parseFloat(ContestDetail.prize_pool) >= 10000) || ContestDetail.guaranteed_prize != 2) &&
                                                            <div className="tab-description p-0">
                                                                {AppLabels.TDS_TEXT}
                                                            </div>
                                                        } */}
                                                    </div>
                                            }

                                        </Tab>
                                    }
                                    <Tab eventKey={2} title={AppLabels.ENTRIES}>
                                        <div className="entries-section">
                                            <div className="table total-entries-table">
                                                <div className="table-cell">
                                                    <div className="label">{AppLabels.MIN} {AppLabels.ENTRIES}</div>
                                                    <div className="value">{this.showNumberOfEntries(ContestDetail.minimum_size)}</div>
                                                </div>
                                                <div className="table-cell">
                                                    <div className="label">{AppLabels.MAX} {AppLabels.ENTRIES}</div>
                                                    <div className="value">{this.showNumberOfEntries(ContestDetail.size)}</div>
                                                </div>
                                            </div>
                                              <div className="progress-bar-default">
                                               
                                                <ProgressBar className={(parseInt(this.state.total_user_joined) < parseInt(ContestDetail.minimum_size) ) ? 'danger-area' : ''} now={this.ShowProgressBar(this.state.total_user_joined, ContestDetail.minimum_size)} />
                                                <div className="progress-bar-value">
                                                    <span className="total-output">
                                                        {this.state.total_user_joined == 0 ? '0': Utilities.numberWithCommas(parseInt(this.state.total_user_joined))}
                                                        {ContestDetail.is_tie_breaker == 1 && this.state.contestStatus !== Constants.CONTEST_LIVE && this.state.contestStatus !== Constants.CONTEST_COMPLETED  && ' ' }
                                                    </span> 
                                                    {
                                                        (((ContestDetail.is_tie_breaker == 0 || this.state.contestStatus == Constants.CONTEST_LIVE || this.state.contestStatus == Constants.CONTEST_COMPLETED) ) || Constants.SELECTED_GAMET != Constants.GameType.DFS)  && 
                                                        <>
                                                            / <span className="total-entries">{Utilities.numberWithCommas(parseInt(ContestDetail.size))} {AppLabels.ENTRIES}</span>
                                                            <span className="min-entries">{AppLabels.MIN} {Utilities.numberWithCommas(parseInt(ContestDetail.minimum_size))}</span>
                                                        </>
                                                    }
                                                </div>
                                            </div>

                                            <InfiniteScroll
                                                dataLength={userList.length}
                                                next={this.onLoadMore}
                                                hasMore={!this.state.isLoading && hasMore}
                                                scrollableTarget='users-scroll-list'
                                            >
                                                <div className='user-table-container' id="users-scroll-list" >
                                                    <Table responsive>
                                                        <tbody className="table-body">
                                                            {
                                                                _Map(userList, (item, idx) => {
                                                                    return (
                                                                        idx < parseInt(ContestDetail.size) ?
                                                                            <tr key={idx}>
                                                                                <td className={"user-entry" + ( isXPEnable ? ' with-user-xp-det' : '')}>
                                                                                    {
                                                                                        isXPEnable && item.user_id && 
                                                                                        <span className="user-xp-detail">
                                                                                            <img className="xp-bdg" src={ item.badge_id == 1 ? Images.XP_BRONZE : item.badge_id == 2 ? Images.XP_SILVER : item.badge_id == 3 ? Images.XP_GOLD : item.badge_id == 4 ? Images.XP_PLATINUM : item.badge_id == 5 ? Images.XP_DIAMOND : item.badge_id == 6 ? Images.XP_ELITE : Images.XP_DEFAULT_BADGE} alt=""/>
                                                                                            {
                                                                                                item.level_number &&
                                                                                                <span className="level-no">Level {item.level_number}</span>
                                                                                            }
                                                                                        </span>
                                                                                    }
                                                                                    {item.image === '' &&
                                                                                        <img src={Images.DEFAULT_USER} alt="" className="user-img" />
                                                                                    }
                                                                                    {item.image !== '' &&
                                                                                        <img src={Utilities.getThumbURL(item.image)} alt="" className="user-img" />
                                                                                    }
                                                                                    {
                                                                                        isXPEnable ?
                                                                                        <>
                                                                                        {
                                                                                            (user_data ? user_data.user_id : '') != item.user_id ?
                                                                                            <div className="user-name">
                                                                                                <a href={WSC.baseURL + "my-profile/" + item.user_id} target='_blank'>{item.name} </a>
                                                                                            </div>
                                                                                            :
                                                                                            <div className="user-name cursor-pointer" onClick={()=>this.showUserProfile(item.user_id)}>{item.name} </div>
                                                                                        }
                                                                                        </>
                                                                                        :
                                                                                        <div className="user-name">{item.name}</div>
                                                                                    }
                                                                                    
                                                                                </td>
                                                                                {ContestDetail.multiple_lineup > 1 &&
                                                                                    <td className="text-right team-joined">{item.user_join_count != -1 && item.user_join_count}
                                                                                        <span>{item.user_join_count != -1 && (item.user_join_count > 1 ? ' ' + AppLabels.TEAMS : ' ' + AppLabels.TEAM)}</span>
                                                                                    </td>
                                                                                }
                                                                            </tr>
                                                                            :
                                                                            ''
                                                                    )
                                                                })
                                                            }
                                                        </tbody>
                                                    </Table>

                                                </div>
                                            </InfiniteScroll>

                                        </div>
                                    </Tab>

                                    
                                    <span style={{ width: 'calc(100% / ' + (2) + ')', left: 'calc(' + (100/(2) * (activeSTIDx - 1)) + '%)' }} className="active-nav-indicator con-detail"></span>
                                </Tabs>
                                {
                                    !joinBtnVisibility && showError && this.showPrivateContestError()
                                }
                            </Modal.Body>
                        </Modal>
                        {showCollectionInfo &&
                            <CollectionInfoModal IsCollectionInfoShow={showCollectionInfo} IsCollectionInfoHide={this.CollectionInfoHide} />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}
