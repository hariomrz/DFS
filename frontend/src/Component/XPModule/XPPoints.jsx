import React from 'react';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import { _times, _Map, _isNull, _debounce, Utilities } from '../../Utilities/Utilities';
import { getXPActivityList, getXPRewardList, getUserXPHistory, getUserXPCard } from '../../WSHelper/WSCallings';
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import ls from 'local-storage';
import MetaData from "../../helper/MetaData";
import CustomHeader from '../../components/CustomHeader';
import { DARK_THEME_ENABLE } from '../../helper/Constants';
import Images from '../../components/images';
import ExperiencePointsIntroModal from './ExperiencePointsIntroModal';
import { NoDataView, MomentDateComponent } from '../../Component/CustomComponent';
import InfiniteScroll from 'react-infinite-scroll-component';
import { Tab, Row, Col, Nav, NavItem } from 'react-bootstrap';
import XPModulesRules from './XPModulesRules';

export default class XPPoints extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            XpIntroModal: false,
            xpList: [],
            isLoading: false,
            hasMore: true,
            pageNo: 1,
            page_size: 20,
            goBackProfile: false,
            selectedTab: "earn-xp",
            xpLevelArray: [],
            userXpPoint: 0,
            userXpDetail: '',
            userXPHistory: [],
            showXPRules: false,
            userXPDetail: ''
        }
    }

    XpIntroModalHide = () => {
        this.setState({
            XpIntroModal: false
        }, () => {
            ls.set('xpModal', 1)
        })
    }

    XpIntroModalShow = () => {
        this.setState({
            XpIntroModal: true
        })
    }

    componentDidMount() {
        if (ls.get('xpModal') != 1) {
            this.XpIntroModalShow()
        }
        // Utilities.handleAppBackManage('my-wallet')
        this.callUserXPDetail();
        this.getXPPointList()
        this.getXpLevelList()
        this.getUserXPHistory()
    }


    componentWillMount() {
        if (this.props && this.props.location && this.props.location.state) {
            this.setState({
                goBackProfile: this.props.location.state.goBackProfile || false
            })
        }
    }


    getXPPointList = () => {
        this.setState({
            isLoading: true
        })
        let param = {
            "page_size": this.state.page_size,
            "page_no": this.state.pageNo
        }
        getXPActivityList(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({
                    isLoading: false,
                    xpList: responseJson.data.activity_list,
                    hasMore: responseJson.data.activity_list.length === this.state.page_size,
                    pageNo: this.state.pageNo + 1,
                })
            }
        })
    }
    callUserXPDetail() {
        getUserXPCard().then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({
                    userXPDetail: responseJson.data.user_xp_card
                })
            }
        })
    }


    onLoadMore() {
        if (!this.state.isLoading && this.state.hasMore) {
            this.setState({ hasMore: false })
            this.getXPActivityList()
        }
    }

    goToXpLevel = () => {
        this.props.history.push({ pathname: '/experience-points-levels' });
    }
    calcPer = (point, total) => {
        point = parseInt(point);
        total = parseInt(total);
        let per = ((point / total) * 100).toFixed(2) + '%';
        return per;
    }
    onTabClick = _debounce((selectedTab) => {
        if (this.state.selectedTab == selectedTab) return
        this.setState({
            selectedTab: selectedTab,
        }, () => {

        });
    }, 300)
    getXpLevelList = () => {
        this.setState({ isLoading: true })
        let param = {
            'page_no': this.state.pageNo,
            'page_size': this.state.page_size
        }
        getXPRewardList(param).then((responseJson) => {
            this.setState({ isLoading: false })
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({
                    xpLevelArray: responseJson.data.reward_list,
                    userXpPoint: responseJson.data.user_xp.point,
                    userXpDetail: responseJson.data.user_xp,
                    hasMore: responseJson.data.reward_list.length === this.state.page_size,
                    pageNo: this.state.pageNo + 1,
                    nextLevelValue: responseJson.data.next_level
                })
            }
        })
    }
    getUserXPHistory = () => {
        let param = {
            "page_size": this.state.page_size,
            "page_no": this.state.pageNo
        }
        this.setState({
            isLoading: true
        })
        getUserXPHistory(param).then((responseJson) => {
            this.setState({
                isLoading: false
            })
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({
                    userXPHistory: this.state.pageNo == 1 ? responseJson.data.history : [...this.state.userXPHistory, ...responseJson.data.history],
                    userXpPoint: responseJson.data.user_xp.point,
                    hasMore: responseJson.data.history.length === this.state.page_size,
                    pageNo: this.state.pageNo + 1,
                })
            }
        })
    }
    onLoadMoreHistory() {
        if (!this.state.isLoading && this.state.hasMore) {
            this.getUserXPHistory()
        }
    }
    onLoadMoreSeeLevels() {
        if (!this.state.isLoading && this.state.hasMore) {
            this.getXpLevelList()
        }
    }
    goBack() {
        this.props.history.goBack();
    }
    kFormatter = (num) => {
        return Math.abs(num) > 999 ? Math.sign(num) * ((Math.abs(num) / 1000).toFixed(1)) + 'k' : Math.sign(num) * Math.abs(num)
    }

    render() {

        const { XpIntroModal, xpList, isLoading, hasMore, goBackProfile, xpLevelArray, userXpPoint, userXpDetail, userXPHistory, showXPRules, userXPDetail } = this.state;
        // const { userXPDetail } = this.props.location.state;
        let isMaxPt = userXPDetail.max_level == userXPDetail.level_number;
        let total = isMaxPt ? parseInt(userXPDetail.max_end_point) - parseInt(userXPDetail.start_point) : parseInt(userXPDetail.next_level_start_point) - parseInt(userXPDetail.start_point);
        let point = parseInt(userXPDetail.point) - parseInt(userXPDetail.start_point);
        let maxExc = (userXPDetail.max_end_point && parseInt(userXPDetail.point) > parseInt(userXPDetail.max_end_point)) ? true : false;

        const HeaderOption = {
            back: true,
            goBackProfile: goBackProfile ? true : false,
            notification: false,
            // title: AL.XP_POINTS,
            hideShadow: true,
            isPrimary: DARK_THEME_ENABLE ? false : true
        }
        const ExperiencePointsIntroModalProps = {
            showM: XpIntroModal,
            hideM: this.XpIntroModalHide
        }


        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container transparent-header web-container-fixed xp-wrapper wallet-wrapper pb-4">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.mywallet.title}</title>
                            <meta name="description" content={MetaData.mywallet.description} />
                            <meta name="keywords" content={MetaData.mywallet.keywords}></meta>
                        </Helmet>
                        {!this.props.hideHeader &&
                            <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        }
                        <div className="view-xp-level">
                            {/* <a href onClick={() => this.goToXpLevel()}>{AL.VIEW_ALL_LEVELS}</a> */}
                            <i className='icon-ic-info'
                                onClick={(e) => this.setState({ showXPRules: true })}
                            />
                        </div>
                        <div className="view-back-icon">
                            {/* <a href onClick={() => this.goToXpLevel()}>{AL.VIEW_ALL_LEVELS}</a> */}
                            <i className='icon-left-arrow'
                                onClick={this.goBack.bind(this)}
                            />
                        </div>
                        <div className="experience-points-view">
                            <div className="heading">
                                {AL.EXPERIENCE_POINTS_TEXT}
                            </div>
                            <div className="value-view">
                                <img src={Images.EARN_XPPOINTS} alt="" width="25px" /> <span>{Utilities.kFormatter(userXpPoint) || 0}</span>
                                {/* <img className='xp-level-with-name' src={userXPDetail.level_number == 1 ? Images.XP_BRONZE : userXPDetail.level_number == 2 ? Images.XP_SILVER : userXPDetail.level_number == 3 ? Images.XP_GOLD : userXPDetail.level_number == 4 ? Images.XP_PLATINUM : userXPDetail.level_number == 5 ? Images.XP_DIAMOND : userXPDetail.level_number == 6 ? Images.XP_ELITE : Images.XP_DEFAULT_BADGE} alt="" width="16px" /> */}
                                {/* <img src={Images.EARN_XPPOINTS} alt="" width="25px" /> */}
                            </div>
                            {_isNull(this.state.nextLevelValue) ?
                                <div className="xp-point-text">{AL.MAXIMUM_LEVEL_REACHED} </div>
                                :
                                <div className="xp-point-text"> {AL.REACH_TEXT}
                                    <span className='xp-point-text-green'>{userXPDetail.next_level_start_point} XP</span> {AL.POINTS_TO_UNLOCK}  <span className='xp-point-text-green'>Level {userXPDetail.next_level} </span>


                                    {_Map(xpLevelArray, (Item, idx) => {
                                        return (
                                            <>
                                                {
                                                    (Item.level_number == userXPDetail.next_level) &&
                                                    (Item.is_coin == '1' || Item.is_cashback == '1' || Item.is_contest_discount == '1') &&

                                                    <span>
                                                        {AL.AND_RECEIVE}
                                                        {
                                                            Item.is_coin == '1' &&
                                                            <span><img src={Images.IC_COIN} alt="" width="10px" className="mR2 ml-1 mr-1" />{Item.coin_amt}</span>
                                                        }
                                                        {
                                                            Item.is_cashback == '1' &&
                                                            <span>
                                                                {Item.is_coin == '1' && <> + </>} {Item.cashback_amt}% {AL.DEPOSIT_CASHBACK} {AL.UPTO} {Item.cashback_type == 1 ? <i className="icon-bonus" /> : Utilities.getMasterData().currency_code}{Item.cashback_amt_cap}
                                                            </span>
                                                        }
                                                        {
                                                            Item.is_contest_discount == '1' &&
                                                            <span>
                                                                {(Item.is_coin == '1' || Item.is_cashback == '1') && <> + </>} {Item.discount_percent}% {AL.CONTEST_JOINING_CASHBACK} {AL.UPTO} {Item.discount_type == 1 ? <i className="icon-bonus" /> : Utilities.getMasterData().currency_code}{Item.discount_amt_cap}
                                                            </span>
                                                        }
                                                    </span>
                                                }

                                            </>
                                        )
                                    })}

                                </div>
                            }

                            <div className="xpprofile-card-slider xpprofile-card-slider-new">
                                <div className="progress-bar progress-bar-new" style={{ width: (maxExc ? '100%' : this.calcPer(point, total)) }}></div>
                                {!maxExc && <span>{userXPDetail.level_number}</span>}
                                {
                                    maxExc ?
                                        <span className="next-lvl">{userXPDetail.level_number}{maxExc && <>+</>}</span>
                                        :
                                        <span className="next-lvl">{userXPDetail.next_level}</span>
                                }
                            </div>
                        </div>
                        <div className="tabs-primary ">
                            <Tab.Container id='my-contest-tabs' activeKey={this.state.selectedTab} onSelect={() => { }} defaultActiveKey={this.state.selectedTab}>
                                <Row className="clearfix">
                                    <Col className="top-fixed my-contest-tab circular-tab circular-tab-new xnew-tab tabs-primary-xp-level" xs={12}>
                                        <Nav>
                                            <NavItem onClick={() => this.onTabClick("earn-xp")} eventKey="earn-xp">{AL.EARN_XP}</NavItem>
                                            <NavItem onClick={() => this.onTabClick("see-levels")} eventKey="see-levels">{AL.SEE_LEVELS} </NavItem>
                                            <NavItem onClick={() => this.onTabClick("history")} eventKey="history">{AL.HISTORY_TEXT}</NavItem>
                                        </Nav>
                                    </Col>
                                    <Col className="mt15" xs={12} >
                                        <Tab.Content animation className='tab-content-view'>
                                            <Tab.Pane eventKey="earn-xp">
                                                <div className="xp-body">
                                                    {/* <div className="section-header lg">{AL.HOW_EARN_XP_PTS}</div> */}
                                                    <InfiniteScroll
                                                        dataLength={xpList.length}
                                                        next={() => this.onLoadMoreSeeLevels()}
                                                        hasMore={!isLoading && hasMore}
                                                        scrollableTarget={'scrollableTarget'}
                                                        loader={
                                                            this.state.isLoadMoreLoaderShow &&
                                                            <h4 className='table-loader'>{AL.LOADING_MSG}</h4>
                                                        } >
                                                        {
                                                            xpList && xpList.length > 0 && !isLoading &&
                                                            <ul className="xp-list" id="scrollableTarget">
                                                                {
                                                                    _Map(xpList, (item, idx) => {
                                                                        return (
                                                                            <li className="border" key={item.activity_id + idx}>
                                                                                <div>
                                                                                    <h4>{item.activity_title}</h4>
                                                                                    {
                                                                                        item.recurrent_count > 0 ?
                                                                                            <span className="daily">{AL.RECURRENT}</span>
                                                                                            :
                                                                                            <span className="one-time">{AL.ONE_TIME}</span>
                                                                                    }
                                                                                    {
                                                                                        item.recurrent_count > 0 &&
                                                                                        <>
                                                                                            {
                                                                                                item.activity_master_id == 8 ?
                                                                                                    <span>({item.recurrent_count == 1 ? AL.ON_EACH : AL.ON_EVERY} {item.recurrent_count != 1 && item.recurrent_count} {AL.DEPOSITS})</span>
                                                                                                    :
                                                                                                    item.activity_master_id == 4 ?
                                                                                                        <span>({item.recurrent_count == 1 ? AL.ON_EACH : AL.ON_EVERY} {item.recurrent_count != 1 && item.recurrent_count} {AL.FREE_CONTEST_JOINING})</span>
                                                                                                        :
                                                                                                        item.activity_master_id == 2 ?
                                                                                                            <span>({item.recurrent_count == 1 ? AL.ON_EACH : AL.ON_EVERY} {item.recurrent_count != 1 && item.recurrent_count} {AL.CASH_CONTEST_JOINING})</span>
                                                                                                            :
                                                                                                            item.activity_master_id == 3 ?
                                                                                                                <span>({item.recurrent_count == 1 ? AL.ON_EACH : AL.ON_EVERY} {item.recurrent_count != 1 && item.recurrent_count} {AL.COIN_CONTEST_JOINING})</span>
                                                                                                                :
                                                                                                                item.activity_master_id == 9 ?
                                                                                                                    <span>({item.recurrent_count == 1 ? AL.ON_EACH : AL.ON_EVERY} {item.recurrent_count != 1 && item.recurrent_count} {AL.WINNINGS})</span>
                                                                                                                    :
                                                                                                                    item.activity_master_id == 5 ?
                                                                                                                        <span>({item.recurrent_count == 1 ? AL.ON_EACH : AL.ON_EVERY} {item.recurrent_count != 1 && item.recurrent_count} {AL.FRIEND_INVITES})</span>
                                                                                                                        :
                                                                                                                        <span></span>
                                                                                            }
                                                                                        </>
                                                                                        // <span>(Every 24 hrs ) Min ₹25</span>    
                                                                                    }
                                                                                </div>
                                                                                <div>
                                                                                    <img src={Images.EARN_XPPOINTS} alt="" className="star-img" />
                                                                                    <h3>{item.xp_point}</h3>
                                                                                </div>
                                                                            </li>
                                                                        )
                                                                    })
                                                                }
                                                            </ul>
                                                        }
                                                        {
                                                            xpList && xpList.length == 0 && !isLoading &&
                                                            <NoDataView
                                                                BG_IMAGE={Images.no_data_bg_image}
                                                                // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                                CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                                MESSAGE_1={AL.XP_POINTS_NOT_DEFINED_YET}
                                                                MESSAGE_2={AL.NO_FIXTURES_MSG3}
                                                                BUTTON_TEXT={AL.PLEASE_COME_BACK_LATER}
                                                                onClick={this.goBack}
                                                            />
                                                        }
                                                        {
                                                            xpList && xpList.length == 0 && isLoading &&
                                                            <div className="mycontest-shimmer-wrap">
                                                                {
                                                                    _times(7, (idx) => {
                                                                        return (
                                                                            this.Shimmer(idx)
                                                                        )
                                                                    })
                                                                }
                                                            </div>
                                                        }
                                                    </InfiniteScroll>
                                                </div>
                                            </Tab.Pane>
                                            <Tab.Pane eventKey="see-levels">

                                                <InfiniteScroll
                                                    dataLength={xpLevelArray.length}
                                                    next={() => this.onloadMore()}
                                                    hasMore={!isLoading && hasMore}
                                                    scrollableTarget={'scrollableTarget'}
                                                    loader={
                                                        this.state.isLoadMoreLoaderShow &&
                                                        <h4 className='table-loader'>{AL.LOADING_MSG}</h4>
                                                    } >
                                                    {
                                                        // xpLevelArray && xpLevelArray.length > 0 && !isLoading &&
                                                        xpLevelArray && xpLevelArray != '' &&
                                                        <ul className="xp-list levels-list" id="scrollableTarget">
                                                            {
                                                                _Map(xpLevelArray, (Item, idx) => {
                                                                    return (
                                                                        <li key={Item.badge_id + idx}
                                                                        //  className={(Item.badge_id == 1 ? "bronze" : Item.badge_id == 2 ? "silver" : Item.badge_id == 3 ? "gold" : Item.badge_id == 4 ? "platinum" : Item.badge_id == 5 ? "diamond" : Item.badge_id == 6 ? "elite" : "") + (userXpDetail.level_number == Item.level_number ? " highlight-li" : "")}
                                                                        >
                                                                            <div className={`li-bor ${parseInt(userXpDetail.level_number) >= parseInt(Item.level_number) ? " " : "li-bor-new"}`}>
                                                                                <div className='li-bor-inner-view'>
                                                                                <div className='d-flex align-items-center'>
                                                                                    <div className="levels-list-img">
                                                                                        <div className={`img-view-container-xp ${Item.badge_id == 1 ? "bronze-bg" : Item.badge_id == 2 ? "silver-bg" : Item.badge_id == 3 ? "gold-bg" : Item.badge_id == 4 ? "platinum-bg" : Item.badge_id == 5 ? "diamond-bg" : Item.badge_id == 6 ? "elite-bg" : "thumb-bg"}`}>
                                                                                            <i className={Item.badge_id == 1 ? "icon-bronze-ic" : Item.badge_id == 2 ? "icon-silver-ic" : Item.badge_id == 3 ? "icon-gold-ic" : Item.badge_id == 4 ? "icon-platinum-ic" : Item.badge_id == 5 ? "icon-diamond-ic" : Item.badge_id == 6 ? "icon-elite-ic" : "icon-thumb-ic"}/>
                                                                                        </div>
                                                                                        {/* <img src={Item.badge_id == 1 ? Images.XP_BRONZE : Item.badge_id == 2 ? Images.XP_SILVER : Item.badge_id == 3 ? Images.XP_GOLD : Item.badge_id == 4 ? Images.XP_PLATINUM : Item.badge_id == 5 ? Images.XP_DIAMOND : Item.badge_id == 6 ? Images.XP_ELITE : Images.XP_DEFAULT_BADGE} alt="" /> */}
                                                                                        {/* {
                                                                                            Item.badge_name &&
                                                                                            <span>{Item.badge_name}</span>
                                                                                        } */}
                                                                                    </div>

                                                                                    <div className="level-deatils">
                                                                                        {
                                                                                            // Item.badge_name &&
                                                                                            <div className='badge-name-text'>{Item.badge_name ?Item.badge_name : AL.OTHERS }</div>
                                                                                        }
                                                                                        <div className='level-text-view'>{AL.LEVEL} {Item.level_number}</div>


                                                                                        {/* {parseInt(userXpDetail.level_number) <= parseInt(Item.level_number) && (Item.is_coin == '1' || Item.is_cashback == '1' || Item.is_contest_discount == '1') &&
                                                                                            <span>
                                                                                                {
                                                                                                    Item.is_coin == '1' &&
                                                                                                    <span><img src={Images.IC_COIN} alt="" width="13px" className="mR2" />{Item.coin_amt}</span>
                                                                                                }
                                                                                                {
                                                                                                    Item.is_cashback == '1' &&
                                                                                                    <span>
                                                                                                        {Item.is_coin == '1' && <> + </>} {Item.cashback_amt}% {AL.DEPOSIT_CASHBACK} {AL.UPTO} {Item.cashback_type == 1 ? <i className="icon-bonus" /> : Utilities.getMasterData().currency_code}{Item.cashback_amt_cap}
                                                                                                    </span>
                                                                                                }
                                                                                                {
                                                                                                    Item.is_contest_discount == '1' &&
                                                                                                    <span>
                                                                                                        {(Item.is_coin == '1' || Item.is_cashback == '1') && <> + </>} {Item.discount_percent}% {AL.CONTEST_JOINING_CASHBACK} {AL.UPTO} {Item.discount_type == 1 ? <i className="icon-bonus" /> : Utilities.getMasterData().currency_code}{Item.discount_amt_cap}
                                                                                                    </span>
                                                                                                }
                                                                                            </span>
                                                                                        } */}
                                                                                        {/* <span>Booster Slot #1</span> */}
                                                                                    </div>
                                                                                </div>
                                                                                {parseInt(userXpDetail.level_number) >= parseInt(Item.level_number) &&
                                                                                <div className="icon-tick-view">
                                                                                    <i className='icon-tick-circular'/>
                                                                                </div>
                                                                }
                                                                                <div className="right-sec right-sec-new">
                                                                                    <img src={Images.EARN_XPPOINTS} alt="" width="16px" className="" />
                                                                                    <h3>
                                                                                        {this.kFormatter(Item.start_point)} - {this.kFormatter(Item.end_point)}

                                                                                        {/* {Utilities.kFormatter(Item.start_point)} - 
                                                                                    {Utilities.kFormatter(Item.end_point)} */}
                                                                                        {/* {Item.start_point} */}
                                                                                        {/* -{Item.end_point} */}
                                                                                    </h3>
                                                                                </div>
                                                                                </div>
                                                                            
                                                                                {parseInt(userXpDetail.level_number) < parseInt(Item.level_number) && (Item.is_coin == '1' || Item.is_cashback == '1' || Item.is_contest_discount == '1') &&

                                                                                            <span>
                                                                                                {
                                                                                                    Item.is_coin == '1' &&
                                                                                                    <div className='view-see-level-text'>
                                                                                                        <div className='prize-line-view'/>
                                                                                                    <div className='inner-span-text'><img src={Images.IC_COIN} alt="" width="13px" className="mR2" />{Item.coin_amt}</div>
                                                                                                    </div>
                                                                                                }
                                                                                                {
                                                                                                    Item.is_cashback == '1' &&
                                                                                                    <div className='view-see-level-text'>
                                                                                                         <div className='prize-line-view'/>
                                                                                                         <div className='inner-span-text'>
                                                                                                        {Item.is_coin == '1' && <> + </>} {Item.cashback_amt}% {AL.DEPOSIT_CASHBACK} {AL.UPTO} {Item.cashback_type == 1 ? <i className="icon-bonus" /> : Utilities.getMasterData().currency_code}{Item.cashback_amt_cap}
                                                                                                        </div>
                                                                                                    </div>
                                                                                                }
                                                                                                {
                                                                                                    Item.is_contest_discount == '1' &&
                                                                                                    <div className='view-see-level-text'>
                                                                                                    <div className='prize-line-view'/>
                                                                                                    <div className='inner-span-text'>
                                                                                                        {(Item.is_coin == '1' || Item.is_cashback == '1') && <> + </>} {Item.discount_percent}% {AL.CONTEST_JOINING_CASHBACK} {AL.UPTO} {Item.discount_type == 1 ? <i className="icon-bonus" /> : Utilities.getMasterData().currency_code}{Item.discount_amt_cap}
                                                                                                    </div>
                                                                                                    </div>
                                                                                                }
                                                                                            </span>
                                                                                        }
                                                                            </div>
                                                                        </li>
                                                                    )
                                                                })
                                                            }
                                                        </ul>
                                                    }
                                                    {
                                                        xpLevelArray && xpLevelArray.length == 0 && !isLoading &&
                                                        <NoDataView
                                                            BG_IMAGE={Images.no_data_bg_image}
                                                            CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                            MESSAGE_1={AL.LEVELS_POINTS_NOT_DEFINED_YET}
                                                            MESSAGE_2={AL.PLEASE_COME_BACK_LATER}
                                                            BUTTON_TEXT={AL.GO_BACK_TO_LOBBY}
                                                            onClick={this.goBack}
                                                        />
                                                    }
                                                    {
                                                        xpLevelArray && xpLevelArray.length == 0 && isLoading &&
                                                        <div className="mycontest-shimmer-wrap">
                                                            {
                                                                _times(7, (idx) => {
                                                                    return (
                                                                        this.Shimmer(idx)
                                                                    )
                                                                })
                                                            }
                                                        </div>
                                                    }
                                                </InfiniteScroll>

                                            </Tab.Pane>
                                            <Tab.Pane eventKey="history">
                                                <div className="xp-trans-wrap">
                                                    <div className="xp-trans-header-wrap">
                                                        <div>{AL.TRANSACTION}</div>
                                                        <div>{AL.XP}</div>
                                                    </div>
                                                    <div className="trans-body-wrap">
                                                        <InfiniteScroll
                                                            dataLength={userXPHistory.length}
                                                            next={this.onLoadMoreHistory.bind(this)}
                                                            hasMore={!isLoading && hasMore}
                                                        >
                                                            {
                                                                userXPHistory && userXPHistory.length > 0 && _Map((userXPHistory), (item, idx) => {
                                                                    return (
                                                                        <div className="xp-trans-list" key={idx + item.activity_id}>
                                                                            <div>
                                                                                <div className="xp-trans-header">{item.activity_title}</div>
                                                                                <div className="xp-trans-time">
                                                                                    <MomentDateComponent data={{ date: item.added_date, format: "MMM D - hh:mm a" }} />
                                                                                </div>
                                                                            </div>
                                                                            <div>
                                                                                <img src={Images.EARN_XPPOINTS} alt="" className="star-img" width="10px" />
                                                                                <span className="xp-trans-price">{item.point}</span>
                                                                            </div>
                                                                        </div>
                                                                    )
                                                                })
                                                            }
                                                        </InfiniteScroll>
                                                    </div>
                                                    {
                                                        userXPHistory && userXPHistory.length == 0 && !isLoading &&
                                                        <NoDataView
                                                            BG_IMAGE={Images.no_data_bg_image}
                                                            // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                            CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                            MESSAGE_1={AL.NOTHING_EARNED_YET}
                                                            MESSAGE_2={AL.START_EARNING_SEE_HISTORY}
                                                        />
                                                    }
                                                    {
                                                        userXPHistory && userXPHistory.length == 0 && isLoading &&
                                                        <div className="mycontest-shimmer-wrap">
                                                            {
                                                                _times(7, (idx) => {
                                                                    return (
                                                                        this.Shimmer(idx)
                                                                    )
                                                                })
                                                            }
                                                        </div>
                                                    }
                                                </div>


                                            </Tab.Pane>
                                        </Tab.Content>
                                    </Col>
                                </Row>
                            </Tab.Container>

                        </div>

                        {/* <div className="xp-body">
                            <div className="section-header lg">{AL.HOW_EARN_XP_PTS}</div>
                            <InfiniteScroll
                                dataLength={xpList.length}
                                next={() => this.onLoadMore()}
                                hasMore={!isLoading && hasMore}
                                scrollableTarget={'scrollableTarget'}
                                loader={
                                    this.state.isLoadMoreLoaderShow &&
                                    <h4 className='table-loader'>{AL.LOADING_MSG}</h4>
                                } >
                                {
                                    xpList && xpList.length > 0 && !isLoading &&
                                    <ul className="xp-list" id="scrollableTarget">
                                        {
                                            _Map(xpList, (item, idx) => {
                                                return (
                                                    <li className="border" key={item.activity_id + idx}>
                                                        <div>
                                                            <h4>{item.activity_title}</h4>
                                                            {
                                                                item.recurrent_count > 0 ?
                                                                    <span className="daily">{AL.RECURRENT}</span>
                                                                    :
                                                                    <span className="one-time">{AL.ONE_TIME}</span>
                                                            }
                                                            {
                                                                item.recurrent_count > 0 &&
                                                                <>
                                                                    {
                                                                        item.activity_master_id == 8 ?
                                                                            <span>({item.recurrent_count == 1 ? AL.ON_EACH : AL.ON_EVERY} {item.recurrent_count != 1 && item.recurrent_count} {AL.DEPOSITS})</span>
                                                                            :
                                                                            item.activity_master_id == 4 ?
                                                                                <span>({item.recurrent_count == 1 ? AL.ON_EACH : AL.ON_EVERY} {item.recurrent_count != 1 && item.recurrent_count} {AL.FREE_CONTEST_JOINING})</span>
                                                                                :
                                                                                item.activity_master_id == 2 ?
                                                                                    <span>({item.recurrent_count == 1 ? AL.ON_EACH : AL.ON_EVERY} {item.recurrent_count != 1 && item.recurrent_count} {AL.CASH_CONTEST_JOINING})</span>
                                                                                    :
                                                                                    item.activity_master_id == 3 ?
                                                                                        <span>({item.recurrent_count == 1 ? AL.ON_EACH : AL.ON_EVERY} {item.recurrent_count != 1 && item.recurrent_count} {AL.COIN_CONTEST_JOINING})</span>
                                                                                        :
                                                                                        item.activity_master_id == 9 ?
                                                                                            <span>({item.recurrent_count == 1 ? AL.ON_EACH : AL.ON_EVERY} {item.recurrent_count != 1 && item.recurrent_count} {AL.WINNINGS})</span>
                                                                                            :
                                                                                            item.activity_master_id == 5 ?
                                                                                                <span>({item.recurrent_count == 1 ? AL.ON_EACH : AL.ON_EVERY} {item.recurrent_count != 1 && item.recurrent_count} {AL.FRIEND_INVITES})</span>
                                                                                                :
                                                                                                <span></span>
                                                                    }
                                                                </>
                                                                // <span>(Every 24 hrs ) Min ₹25</span>    
                                                            }
                                                        </div>
                                                        <div>
                                                            <img src={Images.EARN_XPPOINTS} alt="" className="star-img" />
                                                            <h3>{item.xp_point}</h3>
                                                        </div>
                                                    </li>
                                                )
                                            })
                                        }
                                    </ul>
                                }
                                {
                                    xpList && xpList.length == 0 && !isLoading &&
                                    <NoDataView
                                        BG_IMAGE={Images.no_data_bg_image}
                                        // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                        CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                        MESSAGE_1={AL.XP_POINTS_NOT_DEFINED_YET}
                                        MESSAGE_2={AL.NO_FIXTURES_MSG3}
                                        BUTTON_TEXT={AL.PLEASE_COME_BACK_LATER}
                                        onClick={this.goBack}
                                    />
                                }
                                {
                                    xpList && xpList.length == 0 && isLoading &&
                                    <div className="mycontest-shimmer-wrap">
                                        {
                                            _times(7, (idx) => {
                                                return (
                                                    this.Shimmer(idx)
                                                )
                                            })
                                        }
                                    </div>
                                }
                            </InfiniteScroll>
                        </div> */}
                        {
                            showXPRules &&
                            <XPModulesRules
                                {...this.props}
                                mShow={showXPRules}
                                mHide={() => this.setState({ showXPRules: false })}
                            />
                        }
                        {
                            <ExperiencePointsIntroModal {...ExperiencePointsIntroModalProps} />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }

    Shimmer = (index) => {
        return (
            <SkeletonTheme key={index} color={DARK_THEME_ENABLE ? "#030409" : null} highlightColor={DARK_THEME_ENABLE ? "#0E2739" : null}>
                <div className="shimmer-list xp-point-list">
                    <div className="shimmer-container">
                        <div>
                            <div className="shimmer-inner shimmer-image">
                                <Skeleton width={36} height={45} />
                                <Skeleton width={40} height={10} />
                            </div>
                        </div>
                        <div className="shimmer-inner">
                            <Skeleton width={24} height={4} />
                            <Skeleton width={24} height={4} />
                        </div>
                    </div>
                </div>
            </SkeletonTheme>
        )
    }
}