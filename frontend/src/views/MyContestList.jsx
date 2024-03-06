import React from 'react';
import { Row, Col, Button, ProgressBar, OverlayTrigger, Tooltip } from 'react-bootstrap';
import { MyContext } from '../InitialSetup/MyProvider';
import { Utilities, _Map, _filter } from '../Utilities/Utilities';
import { AppSelectedSport, DARK_THEME_ENABLE, SELECTED_GAMET, GameType, OnlyCoinsFlow, RFContestId } from '../helper/Constants';
import { getMyContest, getMultigameMyContest } from "../WSHelper/WSCallings";
import InfiniteScroll from 'react-infinite-scroll-component';
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import Images from '../components/images';
import * as WSC from "../WSHelper/WSConstants";
import * as AppLabels from "../helper/AppLabels";
import { NoDataView } from '../Component/CustomComponent';
import ls from 'local-storage';
import firebase from "firebase";
import WSManager from "../WSHelper/WSManager";

/**
  * @description Display shimmer effects while loading list
  * @return UI components
*/
const Shimmer = ({ index }) => {
    return (
        <SkeletonTheme color={DARK_THEME_ENABLE ? "#161920" : null} highlightColor={DARK_THEME_ENABLE ? "#0E2739" : null}>
            <div key={index} className="contest-list m">
                <div className="shimmer-container">
                    <div className="shimmer-top-view">
                        <div className="shimmer-line">
                            <Skeleton height={9} />
                            <Skeleton height={6} />
                            <Skeleton height={4} width={100} />
                        </div>
                        <div className="shimmer-image">
                            <Skeleton width={30} height={30} />
                        </div>
                    </div>
                    <div className="shimmer-bottom-view">
                        <div className="progress-bar-default">
                            <Skeleton height={6} />
                            <div className="d-flex justify-content-between">
                                <Skeleton height={4} width={60} />
                                <Skeleton height={4} width={60} />
                            </div>
                        </div>
                        <div className="shimmer-buttin">
                            <Skeleton height={30} />
                        </div>
                    </div>
                </div>
            </div>
        </SkeletonTheme>
    )
}

export default class MyContestList extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            contestList: [],
            publicContestList: [],
            privateContestList: [],
            ShimmerList: [1, 2, 3, 4, 5],
            isLoaderShow: false,
            showTeamModal: false,
            showFor: '',
            isRefCalled: false,
            isSecIn: this.props.isSecondInning || false,
            // myContestCount: 0,
            myContestCount: this.props.myContestCount || 0,
            allContestList: [],
            bn_state: localStorage.getItem('banned_on'),
            geoPlayFree: localStorage.getItem('geoPlayFree')
        }
    }

    showTeam = (e, data) => {
        e.stopPropagation()
        this.setState({
            showTeamModal: true,
            showFor: data
        })
    }
    hideTeam = () => {
        this.setState({
            showTeamModal: false
        })
    }

    /**
     * @description lifecycle method of react,
     * method to load data of contest listing and user lineup list
     */
    componentDidMount() {
        if (this.props && this.props.LobyyData) {

            // if(this.props.myContestCount != this.state.myContestCount && this.state.myContestCount > 0 && this.state.allContestList.length != this.state.myContestCount){
            //     this.setState({
            //         myContestCount: this.props.myContestCount
            //     })
            //     this.getMyContest()
            // }
            if (this.props.myContestListData && this.props.myContestListData.length == this.state.myContestCount) {
                console.log('did mpunt api call 222222')
                this.setData(this.props.myContestListData)
            }
            if (!this.props.myContestListData && this.props && this.props.LobyyData && this.props.LobyyData.collection_master_id && this.state.myContestCount > 0) {
                this.getMyContest()
            }
            // else if((this.state.myContestCount > 0 && this.props.myContestListData && this.props.myContestListData.length != this.state.myContestCount) || (this.props.myContestCount != this.state.myContestCount && this.props && this.props.LobyyData && this.props.LobyyData.collection_master_id)){
            // // if(((this.state.myContestCount > 0 && this.state.allContestList.length != this.state.myContestCount) || (this.props.myContestCount != this.state.myContestCount)) && this.props && this.props.LobyyData && this.props.LobyyData.collection_master_id){
            //     console.log('did mpunt api call')
            //     this.getMyContest()
            // }
            // else{}
        }
    }


    componentWillReceiveProps(nextProps) {
        if (nextProps && nextProps.LobyyData) {
            this.setState({
                nextProps: nextProps
            })
            console.log('first componentWillReceiveProps nextProps', nextProps)
            console.log('first componentWillReceiveProps', this.state.myContestCount)
            if (nextProps.myContestCount > 0 && nextProps.myContestCount != this.state.myContestCount) {
                this.setState({
                    myContestCount: nextProps.myContestCount
                })
                if (nextProps.myContestListData) {
                    console.log('receive props api call if 222222')
                    this.setData(nextProps.myContestListData)
                }
                else {
                    console.log('receive props api call if 1')
                    this.getMyContest()
                }
            }
            else {
                if (nextProps.myContestListData) {
                    console.log('receive props api call else 222222', nextProps)
                    console.log('receive props api call else 222222 nextProps.myContestCount', this.state.myContestCount)
                    this.setData(nextProps.myContestListData)
                }
            }
        }
    }

    componentWillMount = (e) => {
        try {
            //update last read
            this.lastReadStatusRef = firebase
                .database()
                .ref()
                .child("user_last_msg_read")
                .child(WSManager.getProfile().user_id);
            this.messageRef = firebase
                .database()
                .ref()
                .child("group_message");
        } catch (e) {

        }
    }

    checkUnseen = (contestList) => {
        contestList.map((itemContest, indexContest) => {
            contestList[indexContest].has_unseen = 0;
            this.lastReadStatusRef && this.lastReadStatusRef.child(itemContest.contest_unique_id).limitToLast(1).on("value", message => {
                var lastReadStatus = null;
                if (message.val() != null) {
                    let msgList = Object.values(message.val());
                    lastReadStatus = msgList[0].last_read;
                }
                else {
                    this.setState({ isRefCalled: true })
                    this.setState({ privateContestList: contestList })
                }
                this.messageRef.child(itemContest.contest_unique_id).limitToLast(1).on("value", message => {
                    var lastMsgTime = null;
                    if (message.val() != null) {
                        let msgList1 = Object.values(message.val());
                        lastMsgTime = msgList1[0].messageDate;
                        if (lastReadStatus == null) {
                            contestList[indexContest].has_unseen = 1;
                        }
                        else if (lastReadStatus == lastMsgTime) {
                            contestList[indexContest].has_unseen = 0;
                        }
                        else {
                            contestList[indexContest].has_unseen = 1;
                        }
                    }
                    else {
                        contestList[indexContest].has_unseen = 0;
                    }
                    this.setState({ isRefCalled: true })
                });
            });
        });
        if (this.state.isRefCalled) {
            this.setState({ privateContestList: contestList })
        }
    }

    getMyContest = () => {
        console.log('my contest -- api call')
        let collection_master_id = '';
        if (this.props && this.props.LobyyData && this.props.LobyyData.collection_master_id) {
            collection_master_id = this.props.LobyyData.collection_master_id;
        }
        else {
            let url = window.location.href;
            if (url.includes('#')) {
                url = url.split('contest-listing')[1];
                collection_master_id = url.split('/')[1];
            }
        }
        var param = {
            "sports_id": AppSelectedSport,
            "status": 0,
            "collection_master_id": collection_master_id
        }
        this.setState({ isLoaderShow: true })
        if (this.state.isSecIn && SELECTED_GAMET != GameType.MultiGame) {
            param['is_2nd_inning'] = 1
        }
        let apiStatus = SELECTED_GAMET == GameType.MultiGame ? getMultigameMyContest : getMyContest
        apiStatus(param).then((responseJson) => {
            this.setState({ isLoaderShow: false })

            if (responseJson && responseJson.response_code == WSC.successCode) {
                let data = responseJson.data;
                this.setData(data)
            }
        })
    }

    setData = (data) => {
        let publicContest = data.filter(function (item) {
            return (item.is_private != 1);
        });
        let privateContest = data.filter(function (item) {
            return (item.is_private == 1);
        });
        this.checkUnseen(privateContest)
        let PCL = publicContest;
        let CL = data;
        this.setState({
            contestList: !this.state.isSecIn ? _filter(CL, (obj) => obj.is_2nd_inning != 1) : CL,
            publicContestList: !this.state.isSecIn ? _filter(PCL, (obj) => obj.is_2nd_inning != 1) : PCL,
            allContestList: data
            // privateContestList : privateContest,
        }, () => {
            // if(this.state.allContestList.length != this.state.myContestCount){
            //     console.log('receive props api call else ')
            //     this.getMyContest()
            // }
        })
    }
    
    handleJson=(data)=>{
        try {
           return JSON.parse(data)
        } catch {
            return data
        }
    }

    getPrizeAmount = (pdata) => {
        let prize_data = this.handleJson(pdata)
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
                        <span className="contest-prizes">{Utilities.getMasterData().currency_code}{Utilities.numberWithCommas(parseFloat(prizeAmount.real).toFixed(0))}</span>
                        : is_tie_breaker == 0 && prizeAmount.bonus > 0 ? <div className="contest-listing-prizes" ><i className="icon-bonus" />{Utilities.numberWithCommas(parseFloat(prizeAmount.bonus).toFixed(0))}</div>
                            : is_tie_breaker == 0 && prizeAmount.point > 0 ? <span style={{ display: 'inlineBlock' }}> <img className="img-coin" alt='' src={Images.IC_COIN} />{Utilities.numberWithCommas(parseFloat(prizeAmount.point).toFixed(0))}</span>
                                : AppLabels.PRIZES
                }
            </React.Fragment>
        )
    }
    /**
     * @description Method to show progress bar
     * @param {*} join - number of user joined
     * @param {*} total - total (max size) of team
     */
    ShowProgressBar = (join, total) => {
        return join * 100 / total;
    }

    /**
     * @description Method to open chat screen for contest members
     */
    goToChat = (contest_unique_id, childItem) => {
        this.props.history.push({ pathname: '/group-chat/'+contest_unique_id, state: { contest_unique_id: contest_unique_id, childItem: childItem }})
    }

    getCSSforChatIcon(contest) {
        let isMulti = false;
        let isGurantied = false;
        if (contest.multiple_lineup > 1) {
            isMulti = true;
        }
        if (parseInt(contest.total_user_joined) >= parseInt(contest.minimum_size)) {
            isGurantied = true;
        }
        if (isGurantied && isMulti) {
            return ' isMultiGuranteed';
        }
        if (isGurantied) {
            return ' isGuranteed';
        }
        if (isMulti) {
            return ' isMulti';
        }
        return '';
    }
    showEntriesLeft = (size, totalJoined) => {
        let entrieLeft = parseFloat(size) - parseFloat(totalJoined)
        return entrieLeft ? Utilities.numberWithCommas(entrieLeft) : 0
    }
    

    geoValidate = (event, contest, bn_state) => {
        // event.preventDefault();
        event.stopPropagation();
        this.setState({ showTeamModal: false })

        let { geoPlayFree } = this.state;
        if (WSManager.loggedIn()) {
            if (bn_state == 1 || bn_state == 2) {
                if (contest.entry_fee == '0') {
                    this.props.check(event, contest, bn_state)
                }
                else {
                    Utilities.bannedStateToast(bn_state)
                }
            }
            if (bn_state == 0) {
                this.props.check(event, contest, bn_state)
            }

        }
        // else {
        //     setTimeout(() => {
        //         this.props.history.push({ pathname: '/signup' })
        //         Utilities.showToast(AppLabels.Please_Login_Signup_First, 3000);
        //     }, 10);
        // }

        // contest.entry_fee == '0' ?
        // ((event) =>
        //     (ContestDisabled)
        //         ?
        //         null
        //         :
        //         globalThis.check(event, contest)
        // )
        // :
        // (e) => .bannedStateToast(e)}
    }


    RenderContestCard = (data, isPrivate) => {
        let contest = data;
        let user_data = ls.get('profile');
        let rookie_setting = Utilities.getMasterData().rookie_setting || '';
        let isRookie = data.group_id == rookie_setting.group_id;
        let h2hID = Utilities.getMasterData().h2h_challenge == '1' ? Utilities.getMasterData().h2h_data && Utilities.getMasterData().h2h_data.group_id : ''
        let { bn_state } = this.state;

        return (
            <div className="contest-list">
                <div className="contest-list-header" onClick={(event) => (this.setState({ showTeamModal: false }), this.props.ContestDetailShow(contest, 2, event))}>
                    <div className="contest-heading">
                        <h3 className="win-type position-relative pr-5">
                            {
                                contest.contest_title ?
                                    <span className="rev-con-title">
                                        <span>
                                            {contest.contest_title}{contest.is_2nd_inning == 1 &&
                                                <OverlayTrigger trigger={['hover']} placement="right" overlay={
                                                    <Tooltip id="tooltip" >
                                                        <strong>{AppLabels.SEC_INNING_CHANCES}</strong>
                                                    </Tooltip>
                                                }><span onClick={(e) => e.stopPropagation()} className='sec-in-tool'>{AppLabels.SEC_INNING}</span></OverlayTrigger>}
                                        </span>
                                    </span>
                                    :
                                    <span onClick={(event) => (this.setState({ showTeamModal: false }), this.props.ContestDetailShow(contest, 1, event))}>
                                        <span className="prize-pool-text text-capitalize" >{AppLabels.WIN} </span>

                                        <span>
                                            {this.getPrizeAmount(contest.prize_distibution_detail)}
                                        </span>
                                        {contest.is_2nd_inning == 1 &&
                                            <OverlayTrigger trigger={['hover']} placement="right" overlay={
                                                <Tooltip id="tooltip" >
                                                    <strong>{AppLabels.SEC_INNING_CHANCES}</strong>
                                                </Tooltip>
                                            }><span onClick={(e) => e.stopPropagation()} className='sec-in-tool'>{AppLabels.SEC_INNING}</span></OverlayTrigger>}
                                        {/* {contest.prize_type == 2 &&
                                        <span className='prize-pool-value'>
                                            {contest.prize_pool != "0" && <img src={Images.COINS} alt="" />}
                                            {contest.prize_pool == "0" ? ' ' + AppLabels.PRACTICE : contest.prize_pool}</span>
                                    } */}
                                    </span>
                            }

                            {
                                h2hID != contest.group_id &&
                                parseInt(contest.size) > parseInt(contest.total_user_joined) &&
                                <i onClick={(shareContestEvent) => (this.setState({ showTeamModal: false }), this.props.shareContest(shareContestEvent, contest))} className="icon-share"></i>
                            }
                            <div className="featured-icon-wrap">
                                {
                                    (contest.is_private == 1 || contest.is_private_contest == 1) &&
                                    <React.Fragment>
                                        {(contest.has_unseen != undefined && contest.has_unseen == 1) ?
                                            <div onClick={(e) => this.goToChat(contest.contest_unique_id)} className={'chat-icon-mycontestlist ' + (this.getCSSforChatIcon(contest))}>
                                                <div style={{ flexDirection: 'row', flex: 1, display: 'flex', justifyContent: 'end' }} >
                                                    <i className='icon-ic-chat'></i>
                                                    <span className='unread-tick'>.</span>
                                                </div>

                                                {/* <img onClick={(e) => this.goToChat(contest.contest_unique_id)} className='unread_status' src={Images.ic_chat_unread} alt=''></img> */}
                                            </div>
                                            :
                                            <div onClick={(e) => this.goToChat(contest.contest_unique_id, contest)} className={'chat-icon-mycontestlist ' + (this.getCSSforChatIcon(contest))}>
                                                {/* <img onClick={(e) => this.goToChat(contest.contest_unique_id, contest)} className='unread_status' src={Images.ic_chat} alt=''></img> */}
                                                <i className='icon-ic-chat'></i>
                                            </div>
                                        }
                                    </React.Fragment>
                                }
                                {contest.multiple_lineup > 1 &&
                                    <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                        <Tooltip id="tooltip" className="tooltip-featured">
                                            <strong>{AppLabels.MAX_TEAM_FOR_MULTI_ENTRY} {contest.multiple_lineup} {AppLabels.MAX_MULTI_ENTRY_TEAM}</strong>
                                        </Tooltip>
                                    }>
                                        <span className="featured-icon new-featured-icon multi-feat" onClick={(e) => e.stopPropagation()}>{AppLabels.MULTI}</span>
                                    </OverlayTrigger>

                                }
                                {
                                    contest.guaranteed_prize == 2 && parseInt(contest.total_user_joined) >= parseInt(contest.minimum_size) &&
                                    <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                        <Tooltip id="tooltip" className="tooltip-featured">
                                            <strong>{AppLabels.GUARANTEED_DESCRIPTION}</strong>
                                        </Tooltip>
                                    }>
                                        <span className="featured-icon new-featured-icon gau-feat" onClick={(e) => e.stopPropagation()}>{AppLabels.GUARANTEED}</span>
                                    </OverlayTrigger>
                                }
                                {
                                    h2hID == contest.group_id &&
                                    <span className="featured-icon new-featured-icon h2h-feat" onClick={(e) => e.stopPropagation()}>{AppLabels.H2H_CHALLENGE}</span>

                                    // <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                    //     <Tooltip id="tooltip" className="tooltip-featured">
                                    //         <strong>{AppLabels.CONFIRM_DESCRIPTION}</strong>
                                    //     </Tooltip>
                                    // }>
                                    //     <span className="featured-icon new-featured-icon h2h-feat" onClick={(e) => e.stopPropagation()}>H2H Challenge</span>
                                    // </OverlayTrigger>
                                }
                                {
                                    h2hID == contest.group_id &&
                                    <span className={"featured-icon new-featured-icon " + (parseInt(contest.total_user_joined) >= 2 ? ' conf-feat' : ' h2h-waiting-feat')} onClick={(e) => e.stopPropagation()}>{parseInt(contest.total_user_joined) >= 2 ? AppLabels.CONFIRM_CONTEST : AppLabels.WAITING_H2H}</span>

                                    // <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                    //     <Tooltip id="tooltip" className="tooltip-featured">
                                    //         <strong>{AppLabels.CONFIRM_DESCRIPTION}</strong>
                                    //     </Tooltip>
                                    // }>
                                    //     <span className={"featured-icon new-featured-icon " + (parseInt(contest.total_user_joined) >= 2 ? ' conf-feat' : ' h2h-waiting-feat')} onClick={(e) => e.stopPropagation()}>{parseInt(contest.total_user_joined) >= 2 ? 'Confirmed' : 'Waiting'}</span>
                                    // </OverlayTrigger>
                                }
                            </div>
                        </h3>
                        <div className="text-small-italic mt3x">
                            {OnlyCoinsFlow != 1 && (contest.max_bonus_allowed != '0') && <span onClick={(event) => (this.setState({ showTeamModal: false }), this.props.ContestDetailShow(contest, 1, event))}>
                                {AppLabels.Use} {contest.max_bonus_allowed}{'% '}{AppLabels.BONUS_CASH_CONTEST_LISTING} {(parseInt(contest.user_joined_count) > 0) ? '|' : ''}
                            </span>}
                            <span className="joined-with">
                                {AppLabels.JOINED_WITH}
                                <span>{contest.teams[0].team_name}</span>
                                {contest.teams.length > 1 && <span onClick={(e) => this.showTeam(e, contest.contest_id)}>+{contest.teams.length - 1} {AppLabels.MORE}</span>}
                                {this.state.showTeamModal && contest.contest_id == this.state.showFor &&
                                    <span className="all-team-section">
                                        {
                                            _Map(contest.teams, (item, idx) => {
                                                return (
                                                    <span>{item.team_name}</span>
                                                )
                                            })
                                        }
                                    </span>
                                }
                            </span>
                        </div>
                    </div>
                    <div className="display-table">
                        <div className="progress-bar-default display-table-cell v-mid" onClick={(event) => this.props.ContestDetailShow(contest, 3, event)}>
                            <ProgressBar now={this.ShowProgressBar(contest.total_user_joined, contest.minimum_size)} className={parseInt(contest.total_user_joined) >= parseInt(contest.minimum_size) ? '' : 'danger-area'} />
                            <div className="progress-bar-value" >
                                {/* <span className="user-joined">
                                {parseFloat(contest.size) -  parseFloat(contest.total_user_joined)}
                                    {SELECTED_GAMET == GameType.DFS && ' ' + AppLabels.SPOTS_LEFT}
                                </span> */}
                                {

                                    <>
                                        <span className="total-entries">
                                            {/* {Utilities.numberWithCommas(parseFloat(contest.size) - parseFloat(contest.total_user_joined))}  */}
                                            {this.showEntriesLeft(contest.size, contest.total_user_joined)}
                                            {" " + AppLabels.SPOTS_LEFT}
                                        </span>
                                        <span className="min-entries">{Utilities.numberWithCommas(contest.size)}{" " + AppLabels.SPOTS} </span></>
                                }
                            </div>
                        </div>

                        <div className="display-table-cell v-mid position-relative entry-criteria">
                            <Button className={"white-base btnStyle btn-rounded" + (isRookie ? ' btn-rookie' : '') + ((bn_state == 1 || bn_state == 2) ?
                                (contest.entry_fee != '0') ? ' geo-disabled' : ' ' : '')} bsStyle="primary" onClick={(event) => this.geoValidate(event, contest, bn_state)}
                                disabled={!((parseInt(contest.user_joined_count) < parseInt(contest.multiple_lineup)) && (parseInt(contest.size) > parseInt(contest.total_user_joined)))}
                            >
                                {
                                    contest.entry_fee > 0 ? ((contest.prize_type == 1 || contest.prize_type == 0 || contest.prize_type == 2) ?
                                        <React.Fragment>
                                            {
                                                contest.currency_type == 2 ?
                                                    <img className="img-coin" alt='' src={Images.IC_COIN} />
                                                    :
                                                    <span>
                                                        {Utilities.getMasterData().currency_code}
                                                        {/* {AppLabels.JOIN + "  " + Utilities.getMasterData().currency_code + contest.entry_fee} */}
                                                    </span>
                                            }
                                            {Utilities.numberWithCommas(contest.entry_fee)}
                                        </React.Fragment>
                                        :
                                        <React.Fragment>
                                            <span >
                                                {/* {AppLabels.JOIN}&nbsp; */}
                                                <i className="icon-bean"></i>
                                            </span>
                                            {Utilities.numberWithCommas(contest.entry_fee)}
                                        </React.Fragment>
                                    ) : AppLabels.FREE
                                }
                                {isRookie && <img style={{ top: '-23px' }} src={Images.ROOKIE_LOGO} alt='' className={'rookie-img' + (!((parseInt(contest.user_joined_count) < parseInt(contest.multiple_lineup)) && (parseInt(contest.size) > parseInt(contest.total_user_joined))) ? ' img-dis' : '')} />}
                            </Button>
                        </div>
                        {/* } */}

                    </div>
                </div>
                {
                    isPrivate &&
                    <div className='private-contest-box live-box'>
                        <div className='left-content'>
                            <span className='private-logo'>P</span>
                            <span className="box-text">{AppLabels.PRIVATE_CONTEST}</span>
                        </div>
                        <div className='creator-info'>
                            {console.log('user_data',user_data)}
                            {/* {AppLabels.YOU}
                            <img src={user_data.image? Utilities.getThumbURL(user_data.image):Images.DEFAULT_AVATAR} alt=""/> */}
                            <span className="name box-text">{user_data.user_id === contest.creator.user_id ? 'YOU' : (contest.creator.name =='' ? contest.creator.user_name : contest.creator.name)}</span>
                            <span className="img-wrp">
                                <img src={contest.image !== '' ? Utilities.getThumbURL(contest.creator.image) : Images.DEFAULT_AVATAR} alt="" />
                            </span>
                        </div>
                    </div>
                }
            </div>
        );
    }

    render() {
        const {
            contestList,
            publicContestList,
            privateContestList,
            isLoaderShow,
            ShimmerList,
            hasMore,
            showTeamModal
        } = this.state;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container contest-listing-web-conatiner header-margin minus-header-height bg-white my-contest-list-wrap">

                        <div className="webcontainer-inner">
                            {/* {
                            isLoading &&  */}
                            <Row>
                                <Col sm={12}>
                                    <InfiniteScroll
                                        style={{ overflow: 'hidden !important' }}
                                        dataLength={contestList.length}
                                        pullDownToRefresh={false}
                                        hasMore={false}
                                        scrollableTarget='test'
                                        loader={
                                            isLoaderShow == true &&
                                            <h4 className='table-loader'>{AppLabels.LOADING_MSG}</h4>
                                        }>
                                        {
                                            privateContestList && privateContestList.length > 0 &&
                                            <React.Fragment>
                                                <div className="contest-head-sec">
                                                    {AppLabels.PRIVATE_CONTESTS}
                                                </div>
                                                {
                                                    _Map(privateContestList, (item, idx) => {
                                                        return <React.Fragment>
                                                            {this.RenderContestCard(item, true)}
                                                        </React.Fragment>
                                                    })
                                                }
                                            </React.Fragment>
                                        }
                                        {
                                            publicContestList && publicContestList.length > 0 &&
                                            <React.Fragment>
                                                <div className="contest-head-sec">
                                                    {AppLabels.PUBLIC_CONTESTS}
                                                </div>
                                                {
                                                    _Map(publicContestList, (item, idx) => {
                                                        return <React.Fragment key={idx}>
                                                            {this.RenderContestCard(item, false)}
                                                        </React.Fragment>
                                                    })
                                                }
                                            </React.Fragment>
                                        }
                                        {
                                            publicContestList.length == 0 && privateContestList.length == 0 && isLoaderShow &&
                                            ShimmerList.map((item, index) => {
                                                return (
                                                    <Shimmer key={index} index={index} />
                                                )
                                            })
                                        }

                                            {
                                                publicContestList.length == 0 && (privateContestList.length == 0) && !isLoaderShow &&
                                                <NoDataView
                                                    BG_IMAGE={Images.no_data_bg_image}
                                                    // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                    CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                    MESSAGE_1={AppLabels.NO_UPCOMING_CONTEST1 + " " + AppLabels.NO_UPCOMING_CONTEST2}
                                                    // MESSAGE_2={AppLabels.NO_UPCOMING_CONTEST2}
                                                    BUTTON_TEXT={AppLabels.JOIN_CONTEST}
                                                    onClick={() => this.props.handleTab ? this.props.handleTab(0) : null}
                                                />
                                            }
                                        </InfiniteScroll>
                                    </Col>
                                </Row>
                            {/* } */}
                        </div>
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}