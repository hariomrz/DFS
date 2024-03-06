import React from 'react';
import { MyContext } from '../../views/Dashboard';
import { OverlayTrigger, Tooltip } from 'react-bootstrap';
import Images from '../../components/images';
import { geFantasyRefLBList } from "../../WSHelper/WSCallings";
import * as AL from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";
import InfiniteScroll from 'react-infinite-scroll-component';
import { DARK_THEME_ENABLE } from '../../helper/Constants';
import { NoDataView } from '../../Component/CustomComponent';
import { Utilities, _Map, _filter, _isEmpty, _isUndefined } from '../../Utilities/Utilities';
import Moment from "react-moment";
import CustomHeader from '../../components/CustomHeader';
import LBAnimation from '../../Component/FantasyRefLeaderboard/LeaderboardAnimation';

class FantasyLeagueLeaderboard extends React.Component {
    constructor(props, context) {
        super(props, context);
        this._timeout = null;
        this.checkScrollStatus = this.checkScrollStatus.bind(this);
        this.state = {
            LName: '',
            LID: '',
            LStatus: '',
            PNO: 1,
            PSIZE: 20,
            HMORE: false,
            ISLOAD: false,
            PLIST: [],
            TOPTHREE: [],
            DETAILS: '',
            showBtmBtn: '',
            ACTLIndex: 0,
            LEADERBORD_TYPES: this.props.location && this.props.location.state ? this.props.location.state.lData || [] : [],
            overall: this.props.location && this.props.location.state ? this.props.location.state.overall : false,
        };
    }

    checkScrollStatus() {
        if (this._timeout) {
            clearTimeout(this._timeout);
        }
        this._timeout = setTimeout(() => {
            this._timeout = null;
            this.setState({
                scrollStatus: 'scroll stopped',
                showBtmBtn: ''
            });
        }, 700);
        if (this.state.scrollStatus !== 'scrolling') {
            this.setState({
                scrollStatus: 'scrolling'
            });
        }
    }
    onScrollList = (event) => {
        let scrollOffset = window.pageYOffset;
        this.checkScrollStatus();
        this.setState({
            soff: scrollOffset
        })
        if (this.state.oldScrollOffset < scrollOffset) {
            this.setState({
                showBtmBtn: 'hideBottomBtn',
                oldScrollOffset: scrollOffset
            })
        } else {
            this.setState({
                showBtmBtn: '',
                oldScrollOffset: scrollOffset
            })
        }
    }
    componentWillUnmount() {
        window.removeEventListener('scroll', this.onScrollList);
    }

    componentWillMount() {
        window.addEventListener('scroll', this.onScrollList);
    }

    componentDidMount() {
        if (this.props.match && this.props.match.params.lbid) {
            let { lbid, lname, status } = this.props.match.params;
            this.setState({
                LID: lbid,
                LName: lname,
                LStatus: status
            }, () => {
                this.getLeaderboardData()
            })
        }
        // this.setState({
        //     ACTLIndex: this.state.LEADERBORD_TYPES.length - 1
        // })
    }

    /**
    * @description - method to get leaderboard list
    */

    getLeaderboardData() {
        const { PNO, PSIZE, PLIST, LID, TOPTHREE, DETAILS } = this.state;
        let param = {
            "leaderboard_id": LID,
            "page_no": PNO,
            "page_size": PSIZE,
        }
        if (PNO === 1) {
            this.setState({ ISLOAD: true });
        }
        geFantasyRefLBList(param).then((responseJson) => {
            this.setState({ ISLOAD: false });
            if (responseJson.response_code === WSC.successCode) {
                let allList = responseJson.data.list;
                let topThree = [];
                var listOther = PNO === 1 ? [] : allList;
                if (PNO === 1 && allList) {
                    _Map(allList, (item) => {
                        if (topThree.length < 3 && (item.rank_value == 1 || item.rank_value == 2 || item.rank_value == 3)) {
                            topThree.push(item)
                        } else {
                            listOther.push(item)
                        }
                    })
                }
                this.setState({
                    PLIST: [...PLIST, ...listOther],
                    TOPTHREE: PNO === 1 ? topThree.sort((a, b) => parseInt(a.rank_value) - parseInt(b.rank_value)) : TOPTHREE,
                    DETAILS: PNO === 1 ? responseJson.data.detail : DETAILS,
                    PNO: PNO + 1,
                    HMORE: allList.length >= PSIZE,
                })
            }
        })
    }

    showLivePrizeData = (data) => {
        let traverse = true;
        let showData = ''
        let gameRank = data.rank_value ? data.rank_value : data;
        _Map(this.state.DETAILS.prize_detail, (item, idx) => {
            let max = parseInt(item.max)
            let min = parseInt(item.min)
            if (traverse && (gameRank == max || gameRank == min || (gameRank < max && gameRank > min))) {
                showData = item;
                traverse = false
            }
        })

        return (
            <>
                {
                    (showData.prize_type == 0) ?
                        <span className="contest-prizes">
                            {<i style={{ display: 'inlineBlock' }} className="icon-bonus"></i>}
                            {Utilities.kFormatter(Number(parseFloat(showData.amount || 0).toFixed(2)))}
                        </span>
                        :
                        (showData.prize_type == 1) ?
                            <span className="contest-prizes">
                                {
                                    <span style={{ display: 'inlineBlock' }}>
                                        {Utilities.getMasterData().currency_code}</span>
                                }
                                {Utilities.kFormatter(Number(parseFloat(showData.amount || 0).toFixed(2)))}
                            </span>
                            :
                            (showData.prize_type == 2) ?
                                <span className="contest-prizes">
                                    {
                                        <span style={{ display: 'inlineBlock' }}>
                                            <img alt='' style={{ marginBottom: '2px', marginRight: 2, width: 15 }} src={Images.IC_COIN} />
                                            {Utilities.kFormatter(showData.amount)}
                                        </span>
                                    }
                                </span>
                                :
                                (showData.prize_type == 3) ?
                                    <span style={ (gameRank === 1 || gameRank === 2 || gameRank === 3) ? {
                                        maxWidth: 115,
                                        overflow: 'hidden',
                                        textOverflow:'ellipsis',
                                        whiteSpace: "nowrap",
                                        width: 'calc(100vw - 70vw)',
                                    } : {}} className="contest-prizes p-0" onClick={(e) => e.stopPropagation()}>
                                        <OverlayTrigger rootClose trigger={['click']} placement={(gameRank === 1 || gameRank === 2 || gameRank === 3) ? "bottom" : "left"} overlay={
                                            <Tooltip id="tooltip" className={"tooltip-featured" + ((gameRank === 1 || gameRank === 2 || gameRank === 3) ? ' lbd' : '')}>
                                                <strong>{showData.amount}</strong>
                                            </Tooltip>
                                        }>
                                            {
                                                <span className="merch-prize-sec" style={{ display: 'inlineBlock' }}>
                                                    {showData.amount}
                                                </span>
                                            }
                                        </OverlayTrigger>
                                    </span>
                                    : <span className="contest-prizes">0</span>
                }
            </>
        )
    }

    renderItem = (item, idx) => {
        let isown = item.is_current == '1'
        let u_name = item.user_name != null && !_isEmpty(item.user_name) && !_isUndefined(item.user_name) ? Utilities.replaceAll(item.user_name, ' ', '_').toLowerCase() : 'username'
        return (
            <div onClick={() => this.props.history.push(`/leaderboard-details/${item.history_id}/${u_name}`)} key={item.history_id + idx} id={item.history_id + idx} className={"list-item cursor-pointer " + (isown ? ' own-v' : '')}>
                <span className="u-rank">{item.rank_value}</span>
                <span className="usernm">
                    <div className="img-sec">
                        <img src={item.image ? Utilities.getThumbURL(item.image) : Images.DEFAULT_USER} alt="" />
                    </div>
                    <div>
                        <div>
                            {
                                isown ? AL.YOU : (item.user_name != null && !_isEmpty(item.user_name) && !_isUndefined(item.user_name) ? Utilities.replaceAll(item.user_name, ' ', '_').toLowerCase() : 'username')
                            }
                        </div>
                        <div className='ref'>
                            {
                                item.total_value + ' ' + AL.Pts
                            }
                        </div>
                    </div>
                </span>
                <span className="amount">
                    {
                        this.showLivePrizeData(item)
                    }
                </span>
            </div>
        )
    }

    renderTopUser = (item) => {
        let itemLength = item ? item.length : 0;
        let FirstUser = itemLength > 0 ? item[0] : '';
        let SecondUser = itemLength > 1 ? item[1] : '';
        let ThirdUser = itemLength > 2 ? item[2] : '';
        return (
            <React.Fragment>
                <div className={"rank-section second-rank" + (itemLength > 1 ? '' : ' disabled')}>
                    <div className="section-data">
                        <div onClick={() => this.props.history.push(`/leaderboard-details/${SecondUser.history_id}/${SecondUser.user_name}`)} className="circle-wrap cursor-pointer">
                            <span className="rank-pos second">
                                2
                            </span>
                            <div className="win-img-sec">
                                <img src={SecondUser.image ? Utilities.getThumbURL(SecondUser.image) : Images.DEFAULT_USER} alt="" />
                            </div>
                        </div>
                        <div className="winner-name user-name-new">{SecondUser.is_current == '1' ? AL.You : (SecondUser.user_name || '--')}</div>
                        {
                            <div className="prize-amt prize-amt-new">
                                {
                                    SecondUser ? this.showLivePrizeData(2) : '--'
                                }
                            </div>
                        }
                        <div className="won-amt">
                            {
                                <span className="contest-prizes t-value">{SecondUser ? (SecondUser.total_value + ' ' + AL.Pts) : '--'}</span>
                            }
                        </div>
                    </div>
                </div>
                <div className={"rank-section first-rank" + (itemLength > 0 ? '' : ' disabled')}>
                    <div className="section-data">
                        <div onClick={() => this.props.history.push(`/leaderboard-details/${FirstUser.history_id}/${FirstUser.user_name}`)} className="circle-wrap cursor-pointer">
                            <span className="rank-pos first">
                                1
                            </span>
                            <div className="win-img-sec">
                                <img src={FirstUser.image ? Utilities.getThumbURL(FirstUser.image) : Images.DEFAULT_USER} alt="" />
                            </div>
                        </div>
                        <div className="winner-name user-name-new">{FirstUser.is_current == '1' ? AL.You : (FirstUser.user_name || '--')}</div>
                        {
                            <div className="prize-amt prize-amt-new">
                                {

                                    FirstUser ? this.showLivePrizeData(1) : '--'
                                }
                            </div>
                        }
                        <div className="won-amt">
                            {
                                <span className="contest-prizes t-value">{FirstUser ? (FirstUser.total_value + ' ' + AL.Pts) : '--'}</span>
                            }
                        </div>
                    </div>
                </div>
                <div className={"rank-section third-rank" + (itemLength > 2 ? '' : ' disabled')}>
                    <div className="section-data">
                        <div onClick={() => this.props.history.push(`/leaderboard-details/${ThirdUser.history_id}/${ThirdUser.user_name}`)} className="circle-wrap cursor-pointer">
                            <span className="rank-pos third">
                                3
                            </span>
                            <div className="win-img-sec">
                                <img src={ThirdUser.image ? Utilities.getThumbURL(ThirdUser.image) : Images.DEFAULT_USER} alt="" />
                            </div>
                        </div>
                        <div className="winner-name user-name-new">{ThirdUser.is_current == '1' ? AL.You : (ThirdUser.user_name || '--')}</div>
                        {
                            <div className="prize-amt prize-amt-new">
                                {
                                    ThirdUser ? this.showLivePrizeData(3) : '--'
                                }
                            </div>
                        }
                        <div className="won-amt">
                            {
                                <span className="contest-prizes t-value">{ThirdUser ? (ThirdUser.total_value + ' ' + AL.Pts) : '--'}</span>
                            }
                        </div>
                    </div>
                </div>

            </React.Fragment>
        )
    }

    onArrowClick = (idx) => {
        this.setState({ PNO: 1, HMORE: false, PLIST: [], TOPTHREE: [], DETAILS: '', LID: this.state.LEADERBORD_TYPES[idx].leaderboard_id, LStatus: this.state.LEADERBORD_TYPES[idx].status, ACTLIndex: idx }, () => {
            this.getLeaderboardData()
        });
    }

    renderMonthView = (L_TYPES, AIndex) => {
        let item = L_TYPES[AIndex || 0]
        return (
            <div style={{ top: 56 }} className="date-l-view date-l-view-new">
                <a onClick={() => this.onArrowClick(AIndex + 1)} href className={'arrow-v' + (AIndex < L_TYPES.length - 1 ? ' active' : '')}>
                    <i className="icon-arrow-up l" />
                </a>
                <div className='date-view date-view-new-leaderboard'>
                    <span><Moment date={item.start_date} format={"DD MMM"} /> - <Moment date={item.end_date} format={"DD MMM"} /></span>
                    {
                        item.status == 0 && <div className="leader-status">
                            <span />{AL.LIVE}
                        </div>
                    }
                    {
                        (item.status == 3 || item.status == 2) &&
                        <div className="leader-status comp">
                            {AL.COMPLETED}
                        </div>
                    }
                </div>
                <a onClick={() => this.onArrowClick(AIndex - 1)} href className={'arrow-v' + (AIndex > 0 ? ' active' : '')}>
                    <i className="icon-arrow-up r" />
                </a>
            </div>
        )
    }

    render() {
        const {
            PLIST,
            ISLOAD,
            HMORE,
            TOPTHREE,
            DETAILS,
            showBtmBtn,
            LName,
            LStatus,
            LEADERBORD_TYPES,
            ACTLIndex,
            overall
        } = this.state;
        let lnm = DETAILS.name ? DETAILS.name : Utilities.replaceAll(LName, '_', ' ')
        let subT = DETAILS ? Utilities.getFormatedDate({ date: DETAILS.start_date, format: 'DD MMM' }) + ' - ' + Utilities.getFormatedDate({ date: DETAILS.end_date, format: 'DD MMM' }) : ''
        const HeaderOption = {
            referalLeaderboradTitle: lnm,
            referalLeaderboradSubTitle: subT,
            isPrimary: DARK_THEME_ENABLE ? false : true,
            back: true,
            // statusLeaderBoard: (LStatus == 2 || LStatus == 3) ? 2 : 1,
            newLBD: true
        }

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container Ftp-web-container new-leaderboard">
                        <div className="global-leaderboard-desktop pickem-leaderboard prediction-part-v prediction-wrap-v open-predict-leaderboard fantasy-ref-l ">
                            <CustomHeader
                                {...this.props}
                                HeaderOption={HeaderOption}
                            />
                            {
                                LEADERBORD_TYPES.length > 1 && overall && this.renderMonthView(LEADERBORD_TYPES, ACTLIndex)
                            }
                            <div className="table-view">
                                {
                                    TOPTHREE && TOPTHREE.length > 0 && <div style={{ marginTop: (LEADERBORD_TYPES.length > 1 && overall) ? 80 : 30, padding: '0 5px 15px' }} className="top-three-users top-three-users-new">
                                        {
                                            this.renderTopUser(TOPTHREE)
                                        }
                                    </div>
                                }

                                {
                                    PLIST && PLIST.length > 0 &&
                                    <div className="header-v">
                                        <span className="u-rank">{AL.RANK}</span>
                                        <span className="usernm">{AL.USER}</span>
                                        <span className="amount">{AL.PRIZE}</span>
                                    </div>
                                }

                                {
                                    PLIST.length > 0 &&
                                    <InfiniteScroll
                                        dataLength={PLIST.length}
                                        hasMore={!ISLOAD && HMORE}
                                        next={() => this.getLeaderboardData()}
                                    >
                                        <div className="list-view" style={{ paddingBottom: 55 }}>
                                            {console.log('PLIST',PLIST)}
                                            {
                                                PLIST.map((item, idx) => {
                                                    return this.renderItem(item, idx)
                                                })
                                            }
                                        </div>
                                    </InfiniteScroll>
                                }
                                {
                                    PLIST.length === 0 && TOPTHREE.length === 0 && !ISLOAD &&
                                    // <div style={{ marginTop: '15%' }} className="no-data-leaderboard">
                                    //     <NoDataView
                                    //         BG_IMAGE={Images.no_data_bg_image}
                                    //         CENTER_IMAGE={Images.BRAND_LOGO_FULL}
                                    //         MESSAGE_1={AL.NOT_ENOUGH_DATA_ON_LEADERBOARD}
                                    //     />
                                    // </div>
                                    <div className="leaderbrd-ani-wrapper">
                                        <LBAnimation />
                                    </div>
                                }
                                {
                                    DETAILS.prize_detail && (PLIST.length > 0 || TOPTHREE.length 
                                    > 0) && <div className={"roster-footer mb-3 pl15 pr15 " + showBtmBtn}>
                                        <div className="btn-wrap">
                                            <button onClick={() => this.props.history.push({
                                                pathname: '/all-prizes',
                                                state: { prizeDetail: DETAILS.prize_detail, isLeague: overall ? false : true, }
                                            })} className="btn btn-block btm-fix-btn completed-league-preview">{AL.VIEW_ALL_PRIZES}</button>
                                        </div>
                                    </div>
                                }
                            </div>
                        </div>
                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}

export default FantasyLeagueLeaderboard;
