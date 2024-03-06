import React, { Suspense, lazy } from 'react';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import { AppSelectedSport, SELECTED_GAMET, GameType, DARK_THEME_ENABLE, setValue } from '../../helper/Constants';
import MetaData from "../../helper/MetaData";
import CustomHeader from '../../components/CustomHeader';
import { SportsIDs } from "../../JsonFiles";
import { Utilities, _isUndefined, _isEmpty, _Map, } from '../../Utilities/Utilities';
import { getFixtureDetail, getCollectionBooster, getUserLineUps, joinContest,applyBooster,getBoosterList,switchTeamContest,joinContestNetworkfantasy,switchTeamContestNF,joinContestH2H } from "../../WSHelper/WSCallings";
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import ConfirmationPopup from '../../Modals/ConfirmationPopup';
import Thankyou from '../../Modals/Thankyou';
import WSManager from '../../WSHelper/WSManager';
import Images from '../../components/images';
import BoosterConfirmationModal from './BoosterConfirmationModal';
import WhatIsBooster from './WhatIsBooster';


export default class ApplyBooster extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            teamName: '',
            allPosition: [],
            isSelectPostion: 1,
            LobyyData: this.props.location.state.LobyyData ? this.props.location.state.LobyyData :[] ,
            FixturedContest: this.props.location.state.FixturedContest,
            team_name: this.props.location.state.team_name,
            isFrom: this.props.location.state.isFromFlow,
            isFromMyTeams: this.props.location.state.isFromMyTeams,
            direct: this.props.location.state.direct,
            lineup_master_contest_id:this.props.location.state.lineup_master_contest_id ? this.props.location.state.lineup_master_contest_id:0,
            ifFromSwitchTeamModal:this.props.location.state.ifFromSwitchTeamModal ? this.props.location.state.ifFromSwitchTeamModal:false,
            boosterList: [],
            allBoosterList: [],
            allAvilableBoosterList: [],
            current_sports_id: AppSelectedSport,
            SelectedPlayerPosition: 'All',
            SelectedPositionName: '',
            isTableLoaderShow: false,
            showConfirmationPopUp: false,
            userTeamListSend: [],
            TotalTeam: [],
            showThankYouModal: false,
            isSelectPostion: 1,
            selectedBoosterId: this.props.location.state.booster_id ? this.props.location.state.booster_id:'0',
            showBoosterConfirmationModal:false,
            showWhatISBoosterModal:false,
            isBenchEnable: Utilities.getMasterData().bench_player == '1',
            isFromBench: this.props.location.state.isFromBench || false

        };

    }

    componentDidMount() {
        //lineupId
        const matchParam = this.props.match.params;
        if (this.state.LobyyData == undefined || this.state.LobyyData == []) {
            this.FixtureDetail(matchParam)
        }
        this.getBoosterCollection(matchParam)
    }

    filterLineypArrByPosition = (player) => {
        let arrPositionOfSelectedPlayer = this.state.boosterList.filter(function (item) {
            return item.position == player.position_name
        })
        return arrPositionOfSelectedPlayer
    }

    /**
   * @description method to get fixture detail
   */
    FixtureDetail = async (CollectionData) => {
        let param = {
            "sports_id": SportsIDs[CollectionData.sportsId],
            "collection_master_id": CollectionData.c_id,
        }
        var api_response_data = await getFixtureDetail(param);
        if (api_response_data) {
            this.setState({
                LobyyData: api_response_data
            })


        }

    }

    /**
     * @description Booster Details
     */
    getBoosterCollection = async (CollectionData) => {
        let param = {
            "sports_id": SportsIDs[CollectionData.sportsId],
            "collection_master_id": CollectionData.c_id,
        }
        var api_response_data = await getCollectionBooster(param);
        if (api_response_data) {
            if (api_response_data && api_response_data.response_code == WSC.successCode) {
                let tempList = [];
                api_response_data && api_response_data.data.position.map((data, key) => {
                    tempList.push({ position_name: data, position_order: key + 1 })
                    return '';
                })
                this.setState({allPosition: tempList,
                    boosterList:api_response_data.data.booster,
                    allBoosterList:api_response_data.data.booster})
            }
           }

    }
    /**
     * @description Booster Details
     */
    getBoosterListApiCall = async (CollectionData) => {
        let param = {
            "sports_id": SportsIDs[CollectionData.sportsId],
        }
        var api_response_data = await getBoosterList(param);
        if (api_response_data) {
            if (api_response_data && api_response_data.response_code == WSC.successCode) {               
                this.setState({ allAvilableBoosterList:api_response_data.data},()=>{
                    this.setState({
                        showWhatISBoosterModal: true,
                    });
                })
            }
           }

    }
     /**
     * @description Apply Booster 
     */
    applyBoosterApiCall = async (CollectionData,booster_id) => {
        let param = {
            "sports_id": SportsIDs[CollectionData.sportsId],
            "collection_master_id": CollectionData.c_id,
            "lineup_master_id":CollectionData.lineupId,
            "booster_id":booster_id
        }
        var api_response_data = await applyBooster(param);
        if (api_response_data) {
            if (api_response_data && api_response_data.response_code == WSC.successCode) {
                Utilities.showToast(api_response_data.message, 1000);
                this.hideBoosterConfirmationModal();
                this.getFlowEditAndJoin();
            }
           }

    }
    ConfirmEvent = (dataFromConfirmPopUp, context) => {
        // Constants.setValue.SetRFContestId(FixturedContestItem.collection_master_id);
        if (dataFromConfirmPopUp.selectedTeam.value.lineup_master_id && dataFromConfirmPopUp.selectedTeam.value.lineup_master_id != null && dataFromConfirmPopUp.selectedTeam.lineup_master_id == "" || dataFromConfirmPopUp.selectedTeam == "") {
            Utilities.showToast(AppLabels.SELECT_NAME_FIRST, 1000);
        } else {
            var currentEntryFee = 0;
            currentEntryFee = dataFromConfirmPopUp.entryFeeOfContest;
            if (SELECTED_GAMET == GameType.Free2Play) {
                this.CallJoinGameApi(dataFromConfirmPopUp);
            }
            else if (
                (dataFromConfirmPopUp.FixturedContestItem.currency_type == 2 && (parseInt(currentEntryFee) <= parseInt(dataFromConfirmPopUp.balanceAccToMaxPercent))) ||
                (dataFromConfirmPopUp.FixturedContestItem.currency_type != 2 && (parseFloat(currentEntryFee) <= parseFloat(dataFromConfirmPopUp.balanceAccToMaxPercent)))
            ) {
                this.CallJoinGameApi(dataFromConfirmPopUp);
            }
            else {
                if (this.state.isReverseF && SELECTED_GAMET == GameType.DFS && this.state.allowRevFantasy) {
                    let collectionMatserId = this.state.LobyyData.collection_master_id ? this.state.LobyyData.collection_master_id : this.state.FixturedContest.collection_master_id;
                    setValue.SetRFContestId(collectionMatserId)
                }
                if (dataFromConfirmPopUp.FixturedContestItem.currency_type == 2) {
                    if (Utilities.getMasterData().allow_buy_coin == 1) {
                        WSManager.setFromConfirmPopupAddFunds(true);
                        WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp)
                        WSManager.setPaymentCalledFrom("SelectCaptainList")
                        this.props.history.push({ pathname: '/buy-coins', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, state: { isFrom: 'SelectCaptainList', isReverseF: this.state.isReverseF } });

                    }
                    else {
                        // Utilities.showToast('Not enough coins', 1000);
                        this.props.history.push({ pathname: '/earn-coins', state: { isFrom: 'lineup-flow', isReverseF: this.state.isReverseF } })
                    }
                }
                else {
                    WSManager.setFromConfirmPopupAddFunds(true);
                    WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp)
                    WSManager.setPaymentCalledFrom("SelectCaptainList")
                    this.props.history.push({ pathname: '/add-funds', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, isReverseF: this.state.isReverseF });
                }

                //Analytics Calling 
                WSManager.googleTrack(WSC.GA_PROFILE_ID, 'paymentgateway');
            }
        }
    }

    CallJoinGameApi(dataFromConfirmPopUp) {
        var contestId = dataFromConfirmPopUp.FixturedContestItem.contest_id
        let isH2h = dataFromConfirmPopUp.FixturedContestItem.contest_template_id ? true :false;

        let param = {
            "contest_id": isH2h ?dataFromConfirmPopUp.FixturedContestItem.contest_template_id :contestId,
            "lineup_master_id": dataFromConfirmPopUp.selectedTeam.lineup_master_id ? dataFromConfirmPopUp.selectedTeam.lineup_master_id : dataFromConfirmPopUp.selectedTeam.value.lineup_master_id,
            "promo_code": dataFromConfirmPopUp.promoCode,
            "device_type": window.ReactNativeWebView ? WSC.deviceTypeAndroid : WSC.deviceType
        }

        let contestUid = dataFromConfirmPopUp.FixturedContestItem.contest_unique_id
        let contestAccessType = dataFromConfirmPopUp.FixturedContestItem.contest_access_type;
        let isPrivate = dataFromConfirmPopUp.FixturedContestItem.is_private;

        this.setState({ isLoaderShow: true })
        let apiCall = isH2h ? joinContestH2H : joinContest;

        apiCall(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                if (isH2h) {
                    Utilities.setH2hData(dataFromConfirmPopUp, responseJson.data.contest_id)
                }
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
        WSManager.googleTrack(WSC.GA_PROFILE_ID, 'joingame');


    }
    createTeamAndJoin = (dataFromConfirmFixture, dataFromConfirmLobby) => {
        this.props.history.push({ pathname: '/lineup', state: { FixturedContest: dataFromConfirmFixture, LobyyData: dataFromConfirmLobby, current_sport: AppSelectedSport, isReverseF: 0 } })
    }
    switchTeam(CollectionData) {
        let param = {
            "sports_id": SportsIDs[CollectionData.sportsId],
            "contest_id": this.state.FixturedContest.contest_id,
            "lineup_master_id":this.props.match.params.lineupId,
            "lineup_master_contest_id": this.state.lineup_master_contest_id,
        }
        switchTeamContest(param).then((responseJson) => {
            this.setState({ isLoaderShow: false })
            if (responseJson.response_code == WSC.successCode) {
                Utilities.showToast(responseJson.message, 5000);
                this.props.history.push({ pathname: '/my-contests', state: { from: 'SelectCaptain'} });
                WSManager.clearLineup();
            }
        })

    }


    SendRosterPosition = (item) => {
        this.setState({
            isSelectPostion: item.position_order,
            SelectedPlayerPosition: item.position_name,
            SelectedPositionName: item.position_name
        }, () => {
            this.applyTeamFilter()
        })
    }
    applyTeamFilter() {
        let { boosterList, allBoosterList } = this.state;
        let tempRosterList = allBoosterList;
        if (this.state.SelectedPlayerPosition != 'All') {
            tempRosterList = allBoosterList.filter((player, index, array) => {
                return (player.position == this.state.SelectedPlayerPosition || player.position == 'All' );
            });
            this.setState({ boosterList: tempRosterList }, () => {
            })
        }
        else {
            this.setState({ boosterList: allBoosterList }, () => {
            })
        }

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

     /**
     * 
     * @description method to display Booster Confirmation modal, when user save booster.
     */
    openBoosterConfirmationModal = () => {
        this.setState({
            showBoosterConfirmationModal: true,
        });
    }
    /**
     * 
     * @description method to hide Booster Confirmation modal
     */
    hideBoosterConfirmationModal = () => {
        this.setState({
            showBoosterConfirmationModal: false,
        });
    }

    /**
     * 
     * @description method to display What is booster modal.
     */
    openWhatIsBoosterModal = () => {
        this.getBoosterListApiCall(this.props.match.params)
       
    }
    /**
     * 
     * @description method to hide What is booster modal
     */
    hideWhatIsBoosterModal = () => {
        this.setState({
            showWhatISBoosterModal: false,
        });
    }

    confirmSaveBooster= () => {
       this.applyBoosterApiCall(this.props.match.params,this.state.selectedBoosterId)
    }

    goToLobby = () => {
        this.props.history.push({ pathname: '/' });
    }

    seeMyContest = () => {
        this.props.history.push({ pathname: '/my-contests', state: { from: 'SelectCaptain' } });
    }
    getFlowEditAndJoin() {
        if (this.state.ifFromSwitchTeamModal) {
            this.switchTeam(this.props.match.params)
        }
        else if ((this.state.isFrom == "MyTeams" || this.state.isFrom == "MyContest" || this.state.isFrom == "editView") && this.state.isFromMyTeams) {
            var go_index;
            if (this.state.direct) {
                if(this.state.isBenchEnable && this.state.isFromBench){
                    go_index = -2
                }
                else{
                    go_index = -1
                }
            }
            else {
                if(this.state.isBenchEnable && this.state.isFromBench){
                    go_index = -4
                }
                else{
                    go_index = -3;
                }
            }
            if (this.state.isFrom == "editView" && !this.state.isClone && !this.state.isFromMyTeams) {
                if(this.state.isBenchEnable && this.state.isFromBench){
                    go_index = -4
                }
                else{
                    go_index = -3;
                }
            }
            WSManager.clearLineup();
            this.props.history.go(go_index);
        }
        else {
            let param = {
                "sports_id": SportsIDs[this.props.match.params.sportsId],
                "collection_master_id": this.props.match.params.c_id,
                "league_id": this.state.LobyyData.league_id
            }
            this.setState({ isLoaderShow: true })
            getUserLineUps(param).then((responseJson) => {
                if (responseJson.response_code == WSC.successCode) {
                    this.setState({
                        showConfirmationPopUp: true,
                        TotalTeam: responseJson.data,
                        userTeamListSend: (this.state.allowRevFantasy && SELECTED_GAMET == GameType.DFS) ? responseJson.data.filter((obj, idx) => {
                            return (this.state.isReverseF ? obj.is_reverse == "1" : obj.is_reverse != "1");
                        }) : responseJson.data
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

    }

    selectBooster=(item)=>{
        var{ selectedBoosterId } = this.state
        if (selectedBoosterId == item.booster_id) {
            this.setState({ selectedBoosterId: '0' })

        }
        else {
            this.setState({ selectedBoosterId: item.booster_id })

        }
        // boosterList && boosterList.map((item, key) => {

        // })
        
    }


    render() {
        var {
            allPosition,
            isSelectPostion,
            boosterList,
            isTableLoaderShow,
            LobyyData,
            showConfirmationPopUp,
            userTeamListSend,
            TotalTeam, FixturedContest,
            showThankYouModal,
            selectedBoosterId,
            showBoosterConfirmationModal,
            showWhatISBoosterModal
        } = this.state;
        const HeaderOption = {
            back: true,
            fixture: true,
            hideShadow: true,
            title: '',
            isPrimary: DARK_THEME_ENABLE ? false : true
        }
        var activeSTIDx = 0;

        return (
            <MyContext.Consumer>
                {(context) => (


                    <div className={"web-container booster-listing-web-conatiner fixed-sub-header web-container-fixed"}>

                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.BOOSTER.title}</title>
                            <meta name="description" content={MetaData.BOOSTER.description} />
                            <meta name="keywords" content={MetaData.BOOSTER.keywords}></meta>
                        </Helmet>
                        <CustomHeader   {...this.props} LobyyData={LobyyData} ref={this.headerRef} HeaderOption={HeaderOption} />
                        <div style={{marginTop:64}} className={"webcontainer-inner"}>

                            <div className="primary-overlay"></div>
                            <div className="fantasy-rules-sec">
                                <span className="text-uppercase">
                                    {AppLabels.YOUR_TEAM_NAME}
                                </span>

                                <a href >{this.state.team_name}</a>
                                
                            </div>
                            <img style={{ height: 60, width: '100%', top: -30, position: "relative" }} src={Images.BOOSTER_STRAIGHT} alt='' onClick={(e) => e.stopPropagation()}></img>

                        </div>
                        <div className= "boosters-text">{AppLabels.BOOSTERS}</div>
                        <div className= "you-can-only-apply-one">{AppLabels.ONLY_ONE_BOOSTER}</div>
                        <div onClick={() => this.openWhatIsBoosterModal()} className= "what-are-boosters">{AppLabels.WHAT_ARE_BOOSTERS}</div>
                        <div className={"booster-header "}>
                            <div className="roster-top-header">
                                <div className={"booster-postion-header" + (AppSelectedSport == SportsIDs.football ? ' roster-position-football' : AppSelectedSport == SportsIDs.basketball ? ' roster-position-basketball' : AppSelectedSport == SportsIDs.ncaaf ? ' roster-postion-ncss' : '')}>
                                    <ul>
                                        {
                                            _Map(allPosition, (item, idx) => {
                                                if (isSelectPostion == item.position_order) {
                                                    activeSTIDx = idx;
                                                }
                                                return (
                                                    <li key={idx} className={(this.state.current_sports_id == SportsIDs.kabaddi ? 'three-position ' : '') + (isSelectPostion == item.position_order ? 'active' : '')} onClick={() => this.SendRosterPosition(item)}>
                                                        <a>
                                                            <h4>{item.position_name}</h4>
                                                        </a>
                                                    </li>
                                                )
                                            })
                                        }
                                        <span style={{ width: 'calc(100% / ' + allPosition.length + ')', left: 'calc(' + (100 / allPosition.length * activeSTIDx) + '%)' }} className="active-nav-indicator"></span>
                                    </ul>
                                </div>

                            </div>
                        </div>


                        <div class='row'>

                            {
                                boosterList && boosterList.map((item, key) => {
                                    return (
                                        <div style={{display:'flex',justifyContent:'center',marginTop:10}} class='col-xs-6'>
                                            <div className={"booster-card"}>
                                                <div className="booster-inner-container">
                                                    <div className="inner-layout">
                                                        <div className="position-name"> {item.position}</div>
                                                    
                                                        <img src={ item.image_name != ''  && item.image_name!= undefined ? Utilities.getBoosterLogo(item.image_name) :Images.BOOSTER_STRAIGHT} className="bitmap" onClick={(e) => e.stopPropagation()}/>
                                                        <div className="booster-name"> {item.name}</div>
                                                        <div className="get-points-extra"> {AppLabels.GET + " "+parseFloat(item.points).toFixed(1) +"x" + " "+ AppLabels.POINTS_EXTRA }</div>
                                                        <div style={{pointerEvents:selectedBoosterId==item.booster_id || selectedBoosterId =='0' ? '' : 'none'}} onClick={() => this.selectBooster(item)} className={"apply-container"+ (selectedBoosterId==item.booster_id || selectedBoosterId =='0' ? '' : ' disable')}>
                                                            <div className={"apply"}>{ selectedBoosterId==item.booster_id ? AppLabels.REMOVE  : AppLabels.APPLY}</div>
                                                        </div>

                                                        
                                                    </div>

                                                    <img src={selectedBoosterId != '0' ?selectedBoosterId == item.booster_id ? Images.BOOSTER_ACTIVE: Images.BOOSTER_DISABLE  :Images.BOOSTER_NORMAL} className={"img"} onClick={(e) => e.stopPropagation()}>

                                                    </img>
                                                </div>
                                            
                                            </div>

                                        </div>
                                    )

                                })

                            }

                        </div>


                        <div className={"booster-footer"}>
                            <div className="btn-wrap">
                            <div onClick={() =>this.getFlowEditAndJoin()} className= "skip-apply-later">{AppLabels.SKIP_APPLY_LATER}</div>

                                <button disabled={selectedBoosterId == '0'} onClick={() =>this.openBoosterConfirmationModal()} className="btn btn-primary btn-block btm-fix-btn">{AppLabels.APPLY}</button>

                            </div>
                        </div>
                        {showConfirmationPopUp &&
                            <ConfirmationPopup lobbyDataToPopup={this.state.LobyyData} IsConfirmationPopupShow={this.ConfirmatioPopUpShow} IsConfirmationPopupHide={this.ConfirmatioPopUpHide} TeamListData={userTeamListSend} TotalTeam={TotalTeam} FixturedContest={FixturedContest} ConfirmationClickEvent={this.ConfirmEvent} CreateTeamClickEvent={this.createTeamAndJoin} fromContestListingScreen={false} createdLineUp={this.props.match.params.lineupId} />
                        }
                        {showThankYouModal &&
                            <Thankyou ThankyouModalShow={this.ThankYouModalShow} ThankYouModalHide={this.ThankYouModalHide} goToLobbyClickEvent={this.goToLobby} seeMyContestEvent={this.seeMyContest} />
                        }
                        {showBoosterConfirmationModal &&
                            <BoosterConfirmationModal boosterList={boosterList} selectedBoosterId={selectedBoosterId} confirmBooster={this.confirmSaveBooster} MShow={this.openBoosterConfirmationModal} MHide={this.hideBoosterConfirmationModal} />
                        }
                        {
                            showWhatISBoosterModal &&
                            <WhatIsBooster boosterList={this.state.allAvilableBoosterList} MShow={this.openWhatIsBoosterModal} MHide={this.hideWhatIsBoosterModal} />

                        }

                    </div>

                )}
            </MyContext.Consumer>
        )
    }
}

