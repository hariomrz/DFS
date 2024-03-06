import React, { Component, Suspense } from 'react';
import { Tab, Row, Col, Nav, NavItem } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import { getLobbyBanner, getPTTourList, getPTJoinTour, getPTMyJoinedTour, getUserAadharDetail } from "../../WSHelper/WSCallings";
import { NavLink } from "react-router-dom";
import { NoDataView, LobbyBannerSlider, LobbyShimmer } from '../CustomComponent';
import CustomHeader from '../../components/CustomHeader';
import { Utilities, _filter, _Map, BannerRedirectLink, parseURLDate, _isEmpty } from '../../Utilities/Utilities';
import MetaComponent from '../MetaComponent';
import * as Constants from "../../helper/Constants";
import WSManager from "../../WSHelper/WSManager";
import * as WSC from "../../WSHelper/WSConstants";
import PTCard from "./PTCard";
import { ConfirmationPopup, Thankyou } from '../../Modals';
import PTMyContestSlider from './PTMyContestSlider';
import * as AL from "../../helper/AppLabels";
import Images from '../../components/images';
import { PickemTournamentHTP, PTRulesScoring } from "../../Modals";
import ReactSlickSlider from '../CustomComponent/ReactSlickSlider';
var bannerData = {}
class PTLobby extends Component {
    constructor(props) {
        super(props);
        this.state = {
            BannerList: [],
            ShimmerList: [1, 2, 3, 4, 5],
            tourList: [],
            tourListFilter: [],
            isLoading: false,
            showConfirmationPopUp: false,
            joinedTourList: [],
            sports_id: Constants.AppSelectedSport,
            sportList: [],
            showptHTP: false,
            showPTRulesModal: false,
            aadharData: '',
            league_id: '',
            premierLeagueData: [],
            tournamentListFilter: [],
            filterSatats: false
        }
    }

    componentDidMount() {
        if (this.props && this.props.AvaSports) {
            this.setState({
                sportList: this.props.AvaSports
            })
        }

        if (WSManager.loggedIn() && Utilities.getMasterData().a_aadhar == "1") {
            if (WSManager.getProfile().aadhar_status != 1) {
                getUserAadharDetail().then((responseJson) => {
                    if (responseJson && responseJson.response_code == WSC.successCode) {
                        this.setState({ aadharData: responseJson.data });
                    }
                })
            }
            else {
                let aadarData = {
                    'aadhar_status': WSManager.getProfile().aadhar_status,
                    "aadhar_id": WSManager.getProfile().aadhar_detail.aadhar_id
                }
                this.setState({ aadharData: aadarData });
            }
        }
        this.getBannerList();
        this.getTourList()
    }

    UNSAFE_componentWillReceiveProps(nextProps) {
        if (this.state.sports_id != nextProps.selectedSport) {
            this.reload(nextProps);
        }
    }

    /**
     * @description method will be called when changing sports
     */
    reload = (nextProps) => {
        if (window.location.pathname.startsWith("/lobby")) {
            this.setState({
                tourList: [],
                tourListFilter: [],
                BannerList: [],
                joinedTourList: [],
                sports_id: nextProps.selectedSport,
            }, () => {
                this.getBannerList();
                this.getTourList()
            })
        }
    }

    /** 
     * @description api call to get baner listing from server
    */
    getBannerList = () => {
        let sports_id = this.state.sports_id;

        if (sports_id == null)
            return;
        if (bannerData[sports_id]) {
            this.parseBannerData(bannerData[sports_id])
        } else {
            setTimeout(async () => {
                this.setState({ isLoaderShow: true })
                let param = {
                    "sports_id": sports_id
                }
                var api_response_data = await getLobbyBanner(param);
                if (api_response_data && param.sports_id == this.state.sports_id) {
                    bannerData[sports_id] = api_response_data;
                    this.parseBannerData(api_response_data)
                }
                this.setState({ isLoaderShow: false })
            }, 1500);
        }
    }

    /** 
     * @description call to parse banner data
    */
    parseBannerData = (bdata) => {
        let refData = '';
        let temp = [];
        // _Map(this.getSelectedbanners(bdata), (item, idx) => {
        _Map(bdata, (item, idx) => {
            if (item.game_type_id == 0 || WSManager.getPickedGameTypeID() == item.game_type_id) {
                if (item.banner_type_id == 2) {
                    refData = item;
                }
                if (item.banner_type_id == 1) {
                    let dateObj = Utilities.getUtcToLocal(item.schedule_date)
                    if (Utilities.minuteDiffValue({ date: dateObj }) < 0) {
                        temp.push(item);
                    }
                }
                else {
                    temp.push(item);
                }
            }
        })
        if (refData) {
            setTimeout(() => {
                CustomHeader.showRCM(refData);
            }, 200);
        }
        this.setState({ BannerList: temp })
    }

    /** 
     * @description call to get selected banner data
    */
    getSelectedbanners(api_response_data) {
        let tempBannerList = [];
        for (let i = 0; i < api_response_data.length; i++) {
            let banner = api_response_data[i];
            if (WSManager.getToken() && WSManager.getToken() != '') {
                if (banner.banner_type_id == Constants.BANNER_TYPE_REFER_FRIEND
                    || banner.banner_type_id == Constants.BANNER_TYPE_DEPOSITE) {
                    if (banner.amount > 0)
                        tempBannerList.push(api_response_data[i]);
                }
                else if (banner.banner_type_id == '6') {
                    //TODO for banner type-6 add data
                }
                else {
                    tempBannerList.push(api_response_data[i]);
                }
            }
            else {
                if (banner.banner_type_id == '6') {
                    tempBannerList.push(api_response_data[i]);
                }
            }
        }

        return tempBannerList;
    }

    /**
     * @description method to redirect user on appopriate screen when user click on banner
     * @param {*} banner_type_id - id of banner on which clicked
     */
    redirectLink = (result, isRFBanner) => {
        if (isRFBanner) {
            this.showRFHTPModalFn()
        }
        else {
            if (WSManager.loggedIn()) {
                BannerRedirectLink(result, this.props)
            }
            else {
                this.props.history.push({ pathname: '/signup' })
            }
        }
    }

    getTourList = () => {
        this.setState({
            isLoading: true
        })
        let param = {
            sports_id: this.state.sports_id
        }
        getPTTourList(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                let tmpArray = responseJson.data.filter((item, idx) => {
                    return item.is_joined == "0"
                })
                // Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') > Utilities.getFormatedDateTime(Utilities.getUtcToLocal(item.start_date), 'YYYY-MM-DD HH:mm ')
                let tmpArrayUpc = responseJson.data.filter((item, idx) => {
                    return item.is_joined == "1" &&  Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') < Utilities.getFormatedDateTime(Utilities.getUtcToLocal(item.start_date), 'YYYY-MM-DD HH:mm ')
                })
                let tmpArrayLive = responseJson.data.filter((item, idx) => {
                    return item.is_joined == "1" &&  Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') > Utilities.getFormatedDateTime(Utilities.getUtcToLocal(item.start_date), 'YYYY-MM-DD HH:mm ')
                })
                let data = responseJson.data;

                if (data.length > 0) {
                    // let featuredList = data.filter((obj) => obj.is_featured == "1")
                    this.setState({
                        // premierLeagueData: data.filter((obj) => obj.is_featured == "1")
                        premierLeagueData: data.filter((value, index, self) =>
                            index === self.findIndex((t) => (
                                t.league === value.league && t.is_featured == "1"
                            ))
                        )
                    })
                }


                this.setState({
                    tourList: responseJson.data,
                    isLoading: false,
                    // tourListFilter: tmpArray 
                    tourListFilter: tmpArray && tmpArray.length > 0 ? tmpArray : tmpArrayUpc && tmpArrayUpc.length > 0 ? tmpArrayUpc :  tmpArrayLive
                })
            } else {
            }
            if (WSManager.loggedIn()) {
                this.getMyJoinedTourList()
            }
        })
    }

    gotoDetails = (item) => {
        if (WSManager.loggedIn()) {
            this.props.history.push({
                // /pickem/detail/:pickemId
                pathname: '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/pickem/detail/' + item.tournament_id,
                state: {
                    itemContest: item,
                    tourId: item.tournament_id,
                    aadharData: this.state.aadharData
                }
            })
        } else {
            this.props.history.push({ pathname: '/signup' })
        }
    }

    joinTournament = (event, item) => {
        event.stopPropagation();
        if (WSManager.loggedIn()) {
            if (Utilities.getMasterData().a_aadhar == "1" && item.entry_fee != '0') {
                if (this.state.aadharData && this.state.aadharData.aadhar_status == "1") {
                    this.setState({
                        LobyyData: item,
                        FixtureData: item,
                        showConfirmationPopUp: true,
                    })
                }
                else {
                    this.aadharConfirmation()
                }
            }
            else {
                this.setState({
                    LobyyData: item,
                    FixtureData: item,
                    showConfirmationPopUp: true,
                })
            }
        }
        else {
            this.props.history.push({ pathname: '/signup' })
        }
    }

    /**
     * 
     * @description method to display confirmation popup model, when user join contest.
     */
    ConfirmatioPopUpShow = () => {
        this.setState({
            showConfirmationPopUp: true,
        });
    }
    /**
     * 
     * @description method to hide confirmation popup model
     */
    ConfirmatioPopUpHide = () => {
        this.setState({
            showConfirmationPopUp: false,
        });
    }

    ConfirmEvent = (dataFromConfirmPopUp) => {
        this.JoinGameApiCall(dataFromConfirmPopUp)
    }

    JoinGameApiCall = (dataFromConfirmPopUp) => {
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
                    WSManager.setPaymentCalledFrom("ContestListing")
                    this.props.history.push({ pathname: '/buy-coins', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, state: { isFrom: 'contestList' } });

                }
                else {
                    this.props.history.push({ pathname: '/earn-coins', state: { isFrom: 'lineup-flow' } })
                }
            }

            else {
                WSManager.setFromConfirmPopupAddFunds(true);
                WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp)
                WSManager.setPaymentCalledFrom("ContestListing")
                this.props.history.push({ pathname: '/add-funds', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, state: { amountToAdd: dataFromConfirmPopUp.AmountToAdd } });
            }
        }
    }

    CallJoinGameApi(dataFromConfirmPopUp) {
        let ApiAction = getPTJoinTour;
        let param = {
            "tournament_id": dataFromConfirmPopUp.FixturedContestItem.tournament_id,
            "device_type": window.ReactNativeWebView ? WSC.deviceTypeAndroid : WSC.deviceType,
        }
        ApiAction(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {

                this.ConfirmatioPopUpHide();
                this.setState({
                    lineup_master_idArray: [],
                    lineup_master_id: ''
                })
                setTimeout(() => {
                    WSManager.googleTrackDaily(WSC.GA_PROFILE_ID, 'pickem_contestjoin');

                    WSManager.googleTrackDaily(WSC.GA_PROFILE_ID, 'pickem_contestjoin');
                    this.ThankYouModalShow()
                }, 300);
                WSManager.clearLineup();
            } else {
                if (Utilities.getMasterData().allow_self_exclusion == 1 && responseJson.data && responseJson.data.self_exclusion_limit == 1) {
                    this.ConfirmatioPopUpHide();
                    this.showUJC();
                }
                else {
                    Utilities.showToast(responseJson.global_error != "" ? responseJson.global_error : responseJson.message, 2000);
                }
            }
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

    joinMore = () => {
        this.ThankYouModalHide()
        this.getTourList()
    }

    seeMyContest = () => {
        this.props.history.push({ pathname: '/my-contests', state: { from: 'Lobby' } });
    }

    goToMyContest = () => {
        this.props.history.push({ pathname: "/my-contests" });
    };

    getMyJoinedTourList = () => {
        let param = {
            sports_id: this.state.sports_id
        }
        getPTMyJoinedTour(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.setState({
                    joinedTourList: responseJson.data.tournament
                })
            }
        })
    }

    showPTHTPModal = () => {
        this.setState({
            showptHTP: true
        })
    }

    hidePTHTPModal = () => {
        this.setState({
            showptHTP: false
        })
    }

    showPTRulesModal = () => {
        this.setState({
            showptHTP: false,
            showPTRulesModal: true
        })
    }
    hideDFSRulesModal = () => {
        this.setState({
            showPTRulesModal: false
        })
    }

    aadharConfirmation = () => {
        if (this.state.aadharData.aadhar_status == "0" && this.state.aadharData.aadhar_id) {
            Utilities.showToast(AL.VERIFICATION_PENDING_MSG, 3000);
            this.props.history.push({ pathname: '/aadhar-verification' })
        }
        else {
            Utilities.showToast(AL.AADHAAR_NOT_UPDATED, 3000);
            this.props.history.push({ pathname: '/aadhar-verification' })
        }
    }
    // activeTabsPremium = (item) => {
    //     this.setState({
    //         league_id: item.league_id
    //     })
    //     let { filteredTour } = this.state;
    //     let data = filteredTour.filter((obj) => obj.league_id == item.league_id)
    //     setTimeout(() => {
    //         this.setState({
    //             tournamentList: data
    //         })
    //     }, 1000);

    // }
    activeTabsPremium = (item) => {
        const { league_id, tourListFilter,tourList } = this.state;
        if (league_id == item.league_id) {
            this.setState({
                league_id: ''
            })
            // setTimeout(() => {
            this.setState({
                tourListFilter: tourListFilter,
                filterSatats: false
            })
            //  }, 1000);

        } else {
            this.setState({
                league_id: item.league_id
            })
            if(WSManager.loggedIn()) {
            let data = tourListFilter.filter((obj) => obj.league_id == item.league_id)
            this.setState({
                tournamentListFilter: data,
                filterSatats: true
            })
            }else{
                if(!WSManager.loggedIn()){
                    let data = tourList.filter((obj) => obj.league_id == item.league_id)
                    this.setState({
                        tournamentListFilter: data,
                        filterSatats: true
                    })
                }
            }
            // setTimeout(() => {
            // this.setState({
            //     tournamentListFilter: data,
            //     filterSatats: true
            // })
            //  }, 1000);
        }


    }

    render() {
        const { BannerList, tourList, isLoading, showConfirmationPopUp, showThankYouModal, joinedTourList, sports_id, sportList, showptHTP, showPTRulesModal, aadharData, tourListFilter, premierLeagueData, league_id, filterSatats, tournamentListFilter } = this.state;
        let bannerLength = BannerList.length;
        var showLobbySportsTab = process.env.REACT_APP_LOBBY_SPORTS_ENABLE == 1 ? true : false
        const settings = {
            className: "slider variable-width",
            dots: false,
            infinite: false,
            centerMode: false,
            slidesToShow: 1,
            slidesToScroll: 1,
            variableWidth: true
        };

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="transparent-header web-container tab-two-height pb0 pick-tour-lobby">
                        <MetaComponent page="lobby" />
                        {/* <div className={"header-fixed-strip" + (showLobbySportsTab ? " header-fixed-strip-2" : '')}>
                            <div className={"strip-content"}>
                                <span>{AL.PICKEM} {AL.TOURNAMENT}</span>
                                <a
                                    href
                                    onClick={(e) => { this.showPTHTPModal(e) }}
                                >
                                    {AL.HOW_TO_PLAY_FREE}
                                </a>
                            </div>
                        </div> */}
                        {
                            bannerLength > 0 &&
                            <div className={bannerLength > 0 ? 'banner-v animation' : 'banner-v'}>
                                {
                                    bannerLength > 0 && <LobbyBannerSlider BannerList={BannerList} redirectLink={this.redirectLink.bind(this)} />
                                }
                            </div>
                        }
                        <div className={"header-fixed-strip" + (showLobbySportsTab ? " header-fixed-strip-2" : '')}>
                            <div className={"strip-content"}>
                                <span>{AL.PICKEM} {AL.TOURNAMENT}</span>
                                <a
                                    href
                                    onClick={(e) => { this.showPTHTPModal(e) }}
                                >
                                    {AL.HOW_TO_PLAY_FREE}
                                </a>
                            </div>
                        </div>

                        {premierLeagueData && premierLeagueData.length > 0 &&
                            <div className='dashboard-container dashboard-container-ptLobby mt0'>
                                <div className="premium-league-container ">
                                    <Tab.Container id='top-sports-slider'>
                                        <div className="sports-tab-nav custom-scrollbar ">
                                            <i className='icon-stock_up' />
                                            <Nav>
                                                <Suspense fallback={<div />} > <ReactSlickSlider settings={settings}>
                                                    {
                                                        _Map(premierLeagueData, (item, idx) => {

                                                            return (
                                                                <NavItem className="premium-league-view" onClick={() => this.activeTabsPremium(item)} >
                                                                    <span className={`premium-league-tabs  ${league_id == item.league_id ? ' active ' : ' inactive '}`}
                                                                        style={{ width: 100 }}
                                                                    >
                                                                        {item.league}
                                                                    </span>
                                                                </NavItem>
                                                            )
                                                        })
                                                    }
                                                </ReactSlickSlider>
                                                </Suspense>
                                            </Nav>
                                        </div>

                                    </Tab.Container>
                                </div>
                            </div>
                        }

                        {WSManager.loggedIn() &&
                            joinedTourList &&
                            joinedTourList.length > 0 && (
                                <div className="my-lobby-fixture-wrap">
                                    <div className="top-section-heading ml-0">
                                        {AL.MY_CONTEST}
                                        <a href onClick={() => this.goToMyContest()}>
                                            {AL.VIEW} {AL.All}
                                        </a>
                                    </div>
                                    <PTMyContestSlider
                                        FixtureData={joinedTourList}
                                        gotoDetails={this.gotoDetails}
                                    // getMyLobbyFixturesList={this.getMyLobbyFixturesList}
                                    // timerCallback={() =>
                                    // this.timerCompletionCall(myContestData)
                                    // }
                                    />
                                </div>
                            )
                        }
                        {
                            WSManager.loggedIn() && !filterSatats && tourListFilter && tourListFilter.length > 0 && !isLoading &&
                            <div className={`tour-list  ${(joinedTourList.length == 0) ? 'mt10' : ''}`}>
                                {
                                    _Map(tourListFilter, (item, idx) => {
                                        return (
                                            <PTCard
                                                item={item}
                                                gotoDetails={() => this.gotoDetails(item)}
                                                joinTournament={(e) => this.joinTournament(e, item)}
                                                isFeatured={sports_id == 0 ? true : false}
                                                sportList={sportList}
                                            />
                                        )
                                    })
                                }
                            </div>
                        }
                        {
                            (WSManager.loggedIn() || !WSManager.loggedIn()) && filterSatats && tournamentListFilter && tournamentListFilter.length > 0 && !isLoading &&
                            <div className={`tour-list  ${(joinedTourList.length == 0) ? 'mt10' : ''}`}>
                                {
                                    _Map(tournamentListFilter, (item, idx) => {
                                        return (
                                            <PTCard
                                                item={item}
                                                gotoDetails={() => this.gotoDetails(item)}
                                                joinTournament={(e) => this.joinTournament(e, item)}
                                                isFeatured={sports_id == 0 ? true : false}
                                                sportList={sportList}
                                            />
                                        )
                                    })
                                }
                            </div>
                        }

                        {
                            !WSManager.loggedIn() && !filterSatats && tourList && tourList.length > 0 && !isLoading &&
                            <div className={`tour-list ${(joinedTourList.length == 0) ? 'mt10' : ''}`}>
                                {
                                    _Map(tourList, (item, idx) => {
                                        return (
                                            <PTCard
                                                item={item}
                                                gotoDetails={() => this.gotoDetails(item)}
                                                joinTournament={(e) => this.joinTournament(e, item)}
                                                isFeatured={sports_id == 0 ? true : false}
                                                sportList={sportList}
                                            />
                                        )
                                    })
                                }
                            </div>
                        }
                        {
                            WSManager.loggedIn() && tourListFilter && tourListFilter.length == 0 && !isLoading &&
                            <NoDataView
                                BG_IMAGE={Images.no_data_bg_image}
                                CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                MESSAGE_1={AL.NO_FIXTURES_MSG1}
                            />
                        }

                        {
                            !WSManager.loggedIn() && !filterSatats && tourList && tourList.length == 0 && !isLoading &&
                            <NoDataView
                                BG_IMAGE={Images.no_data_bg_image}
                                CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                MESSAGE_1={AL.NO_FIXTURES_MSG1}
                            />
                        }

                        {
                            filterSatats && tournamentListFilter && tournamentListFilter.length == 0 && !isLoading &&
                            <NoDataView
                                BG_IMAGE={Images.no_data_bg_image}
                                CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                MESSAGE_1={AL.NO_FIXTURES_MSG1}
                            />
                        }
                        

                        {
                            showConfirmationPopUp &&
                            <ConfirmationPopup
                                IsConfirmationPopupShow={showConfirmationPopUp}
                                IsConfirmationPopupHide={this.ConfirmatioPopUpHide}
                                TeamListData={''}//{userTeamListSend}
                                TotalTeam={0} //TotalTeam}
                                FixturedContest={this.state.LobyyData}
                                ConfirmationClickEvent={this.ConfirmEvent}
                                // CreateTeamClickEvent={this.createTeamAndJoin}
                                lobbyDataToPopup={this.state.LobyyData}
                                fromContestListingScreen={true}
                                createdLineUp={''}
                                selectedLineUps={[]}
                                hideExtraSec={true}
                                isPickemTournament={true}
                            // showDownloadApp={this.showDownloadApp}
                            // isStockF={true}
                            // isStockLF={true}
                            />
                        }
                        {
                            showThankYouModal &&
                            <Thankyou ThankyouModalShow={this.ThankYouModalShow}
                                ThankYouModalHide={this.ThankYouModalHide}
                                goToLobbyClickEvent={this.joinMore}
                                seeMyContestEvent={this.seeMyContest}
                                isPickemTournament={true}
                            />
                        }

                        {
                            showptHTP &&
                            <Suspense fallback={<div />} >
                                <PickemTournamentHTP
                                    mShow={showptHTP}
                                    mHide={this.hidePTHTPModal}
                                    rulesModal={this.showPTRulesModal}
                                />
                            </Suspense>
                        }
                        {showPTRulesModal &&
                            <PTRulesScoring MShow={showPTRulesModal} MHide={this.hideDFSRulesModal} />
                        }

                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}

export default PTLobby;