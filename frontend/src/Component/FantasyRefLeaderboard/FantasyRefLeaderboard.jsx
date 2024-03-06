import React, { Suspense, lazy } from 'react';
import { MyContext } from '../../views/Dashboard';
import { Tab, Nav, NavItem, Row, Col } from 'react-bootstrap';
import { OverlayTrigger, Tooltip } from 'react-bootstrap';
import Images from '../../components/images';
import { geFantasyRefLBMasterData, geFantasyRefLBList } from "../../WSHelper/WSCallings";
import * as AL from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";
import InfiniteScroll from 'react-infinite-scroll-component';
import Moment from "react-moment";
import { DARK_THEME_ENABLE, GameType, SELECTED_GAMET } from '../../helper/Constants';
import { NoDataView } from '../../Component/CustomComponent';
import { Utilities, _debounce, _Map, _filter } from '../../Utilities/Utilities';
import queryString from 'query-string';
import CustomHeader from '../../components/CustomHeader';
import { RefFantasyLeaderboardModal } from '../../Modals';
import WSManager from '../../WSHelper/WSManager';
import LBAnimation from './LeaderboardAnimation';
import { _isUndefined } from '../../Utilities/Utilities';
const ReactSlickSlider = lazy(() => import('../CustomComponent/ReactSlickSlider'));

class FantasyRefLeaderboard extends React.Component {
    constructor(props, context) {
        super(props, context);
        this._timeout = null;
        this.checkScrollStatus = this.checkScrollStatus.bind(this);
        this.state = {
            masterData: '',
            ATAB: '',
            ATABITEM: '',
            ACTL: '',
            ACTLIndex: '',
            PNO: 1,
            PSIZE: 20,
            HMORE: false,
            ISLOAD: false,
            PLIST: [],
            TOPTHREE: [],
            LEADERBORD_TYPES: [],
            DETAILS: '',
            showBtmBtn: '',
            showLBModal: false,
            type:'',
            stockLeaderboardData:{},
            activeType:1,
            isGL: false
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
       // WSManager.setPickedGameType(GameType.DFS);
        window.addEventListener('scroll', this.onScrollList);
    }

    componentDidMount() {
        let type = ''
        if (this.props.location.search) {
            let url = this.props.location.search;
            let urlParams = queryString.parse(url) || '';
            type = urlParams.type
        }
        this.setState({type:type},()=>{
        })
        this.getLeaderboardMasterData(type);
    }

    getLeaderboardMasterData(type) {
        let param = {
        }
        this.setState({ ISLOAD: true });
        geFantasyRefLBMasterData(param).then((responseJson) => {
            this.setState({ ISLOAD: false });
            if (responseJson.response_code === WSC.successCode) {
                this.setState({
                    masterData: responseJson.data
                }, () => {
                    let tmpArray = _filter(responseJson.data, (item) => {
                        return item.category_id == type
                    })
                    this.onTabClick(tmpArray.length > 0 ? tmpArray[0] : responseJson.data[0]);
                })
            }
        })
    }

    /**
    * @description - method to get leaderboard list
    */

    getLeaderboardData() {
        const { PNO, PSIZE, PLIST, ACTL, TOPTHREE, DETAILS } = this.state;
        let param = {
            "leaderboard_id": ACTL.leaderboard_id,
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
                                    <span style={(gameRank === 1 || gameRank === 2 || gameRank === 3) ? {
                                        maxWidth: 115,
                                        overflow: 'hidden',
                                        textOverflow: 'ellipsis',
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
                                    : 0
                }
            </>
        )
    }

    onTabClick = _debounce((item) => {
        if(_isUndefined(item)) return ''
        this.setState({type:item.category_id},()=>{})
        if (!item.isRev) {
            item['isRev'] = true;
            item['league'] = (item.league || []).reverse();
        }
        let url = window.location.href;
        let path = ''
        if(url.includes('global')){
            path = "/global-leaderboard?type="
            this.setState({
                isGL: true
            })
        }
        else{
            path = "/leaderboard?type="
        }
        
        // let path = Utilities.getMasterData().allow_social == 1 ? "/leaderboards?type=" : '/leaderboard?type=';
        window.history.replaceState("", "", path + item.category_id);
        this.setState({stockLeaderboardData: item,activeType:1})
        let leaderboardData =  (item.category_id == '3' || item.category_id == '4' || item.category_id == '5' ) ?  this.state.activeType == 1 ? item.leaderboard.weekly : item.leaderboard.monthly : item.leaderboard
        this.setState({ATABITEM: item, PNO: 1, HMORE: false, PLIST: [], TOPTHREE: [], DETAILS: '', ATAB: item.category_id, LEADERBORD_TYPES: leaderboardData || [], ACTL: (leaderboardData || []).length > 0 ? leaderboardData[0] : '', ACTLIndex: 0 }, () => {
            if (this.state.ACTL && item.category_id != '2') {
                this.getLeaderboardData()
            }
        });
    }, 200)

    onArrowClick = (idx) => {
        this.setState({ PNO: 1, HMORE: false, PLIST: [], TOPTHREE: [], DETAILS: '', ACTL: this.state.LEADERBORD_TYPES[idx], ACTLIndex: idx }, () => {
            this.getLeaderboardData()
        });
    }

    renderMonthView = (L_TYPES, AIndex) => {
        let item = L_TYPES[AIndex]
        return (
            <div className={"date-l-view date-l-view-new" + (SELECTED_GAMET == GameType.StockFantasy || GameType.StockFantasyEquity ?  ' stock-padding' : '')  + (this.state.type == 3 || this.state.type== 4 ? "" : " mt-0") + (this.state.type =='1'? ' type-one': '')}>
                <a onClick={() => this.onArrowClick(AIndex + 1)} href className={'arrow-v' + (AIndex < L_TYPES.length - 1 ? ' active' : '')}>
                    <i className="icon-arrow-up l" />
                </a>
                <div className='date-view date-view-new-leaderboard'>
                    <span><Moment date={item.start_date} format={"DD MMM"} /> - <Moment date={item.end_date} format={"DD MMM"} /></span>
                    {
                        item.status == 0 && <div className="leader-status leader-status-new">
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

    renderItem = (item, idx) => {
        let isown = item.is_current == '1'
        let u_name = Utilities.replaceAll(item.user_name, ' ', '_').toLowerCase()
        let isStock = this.state.type == '3' || this.state.type == '4' || this.state.type == '5' ? true : false;
        return (
            <div onClick={() => isStock ? this.props.history.push(`/leaderboard-details-stock/${item.history_id}/${this.state.type}/${u_name}`) : ''} key={item.history_id + idx} id={item.history_id + idx} className={"list-item" + (isown ? ' own-v' : '') }>
                <span className="u-rank">{item.rank_value}</span>
                <span className="usernm">
                    <div className="img-sec">
                        <img src={item.image ? Utilities.getThumbURL(item.image) : Images.DEFAULT_USER} alt="" />
                    </div>
                    <div>
                        <div>
                            {
                                isown ? AL.YOU : item.user_name
                            }
                        </div>
                        <div className='ref'>
                            { this.state.type ==5 ?
                                Utilities.getExactValueSP(item.total_value)+ ' %' :
                                // isStock ?
                                // Utilities.kFormatter(Number(parseFloat(item.average_accuracy || 0).toFixed(2))) + ' %'

                                // :
                                Utilities.kFormatter(Number(parseFloat(item.total_value || 0).toFixed(2))) + ' ' + (isStock ? AL.PTS : AL.REFERRED)
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
    gotoLeagueLeaderboard = (item, lName, isoverall) => {
        this.props.history.push({
            pathname: `/league-leaderboard/${item.leaderboard_id}/${item.status}/${Utilities.replaceAll(lName, ' ', '_').toLowerCase()}`,
            state: { lData: this.state.ATABITEM.leaderboard || [], overall: isoverall }
        })
    }
    renderLeagueItem = (item, idx, own) => {
        // let lName = own ? 'Overall Leaderboard' : item.name
        let lName = item.name
        return (
            <div onClick={() => this.gotoLeagueLeaderboard(item, lName, own)} key={item.leaderboard_id + idx} id={item.leaderboard_id + idx} className={"list-item league" + (own ? ' own-v' : '')}>
                <span className="usernm">
                    <div className="img-sec">
                        <img src={idx % 2 === 0 ? Images.tb3 : idx % 3 === 0 ? Images.tb2 : Images.tb1} alt="" />
                    </div>
                    <div>
                        <div>
                            {
                                lName
                            }
                        </div>
                        <div className='ref'>
                            <span><Moment date={item.start_date} format={"DD MMM"} /> - <Moment date={item.end_date} format={"DD MMM"} /></span>
                        </div>
                    </div>
                </span>
                {(item.is_joined == 1 || item.status == 3) && <div className='joined'>{item.status == 3 ? AL.COMPLETED : AL.JOINED}</div>}
            </div>
        )
    }

    renderTopUser = (item) => {
        let itemLength = item ? item.length : 0;
        let FirstUser = itemLength > 0 ? item[0] : '';
        let SecondUser = itemLength > 1 ? item[1] : '';
        let ThirdUser = itemLength > 2 ? item[2] : '';
        let u_name_FirstUser;
        let u_name_SecondUser;
        let u_name_ThirdUser;
        let isStock = this.state.type == '3' || this.state.type == '4' || this.state.type == '5' ? true : false;

        if(isStock){
             u_name_FirstUser =FirstUser && Utilities.replaceAll(FirstUser.user_name, ' ', '_').toLowerCase()
             u_name_SecondUser = SecondUser && Utilities.replaceAll(SecondUser.user_name, ' ', '_').toLowerCase()
             u_name_ThirdUser = ThirdUser && Utilities.replaceAll(ThirdUser.user_name, ' ', '_').toLowerCase()
        }

          return (
            <React.Fragment>
                <div onClick={() => isStock ? this.props.history.push(`/leaderboard-details-stock/${SecondUser.history_id}/${this.state.type}/${u_name_SecondUser}`):''} className={"rank-section second-rank" + (itemLength > 1 ? '' : ' disabled')}>
                    <div className="section-data">
                        <div className="circle-wrap">
                            <span className="rank-pos second">
                                2
                            </span>
                            <div className="win-img-sec  win-img-sec-new">
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
                            {this.state.type ==5 ?
                                <span className="contest-prizes t-value">{SecondUser ? Utilities.getExactValueSP(SecondUser.total_value) + '%' : '--'}</span> :
                                // isStock ?
                                // <span className="contest-prizes t-value">{SecondUser ? Utilities.kFormatter(Number(parseFloat(SecondUser.average_accuracy || 0).toFixed(2))) + ' ' + (isStock ?  '%' : AL.REFERRED) : '--'}</span> 

                                // :
                                <span className="contest-prizes t-value">{SecondUser ? Utilities.kFormatter(Number(parseFloat(SecondUser.total_value || 0).toFixed(2))) + ' ' + (isStock ? this.state.type ==5 ? '%' : AL.PTS : AL.REFERRED) : '--'}</span>
                            }
                        </div>
                    </div>
                </div>
                <div onClick={() => isStock ?  this.props.history.push(`/leaderboard-details-stock/${FirstUser.history_id}/${this.state.type}/${u_name_FirstUser}`):''} className={"rank-section first-rank" + (itemLength > 0 ? '' : ' disabled')}>
                    <div className="section-data">
                        <div className="circle-wrap">
                            <span className="rank-pos first">
                                1
                            </span>
                            <div className="win-img-sec win-img-sec-new">
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
                            {this.state.type ==5 ? 
                                <span className="contest-prizes t-value">{FirstUser ?  Utilities.getExactValueSP(FirstUser.total_value) + '%'  : '--'}</span>:
                                // isStock ?
                                // <span className="contest-prizes t-value">{FirstUser ? Utilities.kFormatter(Number(parseFloat(FirstUser.average_accuracy || 0).toFixed(2))) + ' ' + (isStock ?  '%' : AL.REFERRED) : '--'}</span>

                                // :
                                <span className="contest-prizes t-value">{FirstUser ? Utilities.kFormatter(Number(parseFloat(FirstUser.total_value || 0).toFixed(2))) + ' ' + (isStock ?  this.state.type ==5 ? '%' : AL.PTS : AL.REFERRED) : '--'}</span>
                            }
                        </div>
                    </div>
                </div>
                <div onClick={() => isStock ? this.props.history.push(`/leaderboard-details-stock/${ThirdUser.history_id}/${this.state.type}/${u_name_ThirdUser}`) : ''} className={"rank-section third-rank" + (itemLength > 2 ? '' : ' disabled')}>
                    <div className="section-data">
                        <div className="circle-wrap">
                            <span className="rank-pos third">
                                3
                            </span>
                            <div className="win-img-sec win-img-sec-new">
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
                            {this.state.type ==5 ?
                                <span className="contest-prizes t-value">{ThirdUser ? Utilities.getExactValueSP(ThirdUser.total_value) + '%'  : '--'}</span> :
                                // isStock ?
                                // <span className="contest-prizes t-value">{ThirdUser ? Utilities.kFormatter(Number(parseFloat(ThirdUser.average_accuracy || 0).toFixed(2))) + ' ' + (isStock ? '%' : AL.REFERRED) : '--'}</span>

                                // :
                                <span className="contest-prizes t-value">{ThirdUser ? Utilities.kFormatter(Number(parseFloat(ThirdUser.total_value || 0).toFixed(2))) + ' ' + (isStock ?  this.state.type ==5 ? '%' : AL.PTS : AL.REFERRED) : '--'}</span>
                            }
                        </div>
                    </div>
                </div>

            </React.Fragment>
        )
    }

    renderTopTab = () => {
        let { masterData, ATAB } = this.state;
        var settings = {
            touchThreshold: 10,
            infinite: false,
            slidesToScroll: 1,
            slidesToShow: masterData.length > 2 ? 4 : 2,
            variableWidth: false,
            initialSlide: 0,
            dots: false,
            autoplay: false,
            centerMode: false,
            // responsive: [
            //     {
            //         breakpoint: 767,
            //         settings: {
            //             slidesToShow: masterData.length > 3 ? 3 :2,
            //             className: "center",
            //             centerMode: masterData.length > 3 ? false : true,
            //             centerPadding: '30px 0 10px',
            //             infinite: false,
            //             initialSlide: 1,
            //             variableWidth: false,
            //         }

            //     }
            // ]
        };
        return (
            <Tab.Container id='top-sports-slider' onSelect={() => console.log('onSelect')} activeKey={ATAB} defaultActiveKey={ATAB}>
                <Row className="clearfix">
                    <Col className="sports-tab-nav sports-tab-rules xoverall-leadbrd-slider p-0" xs={12}>
                        <Nav>
                            <Suspense fallback={<div />} ><ReactSlickSlider settings={settings}>
                                {
                                    _Map(masterData, (item, idx) => {
                                        return (
                                            <NavItem
                                                style={{ width: 'calc(100% / ' + masterData.length + ')' }}
                                                key={item.category_id}
                                                onClick={() => this.onTabClick(item, idx)}
                                                eventKey={item.category_id}
                                                className={item.category_id == ATAB ? 'active' : ''}
                                            >
                                                <span>
                                                    {/* {
                                                        item.category_id == '1' ? AL.REFERRAL : item.category_id == '2' ? AL.FANTASY_POINTS : item.category_id == '3' ? AL.STOCK_POINTS: item.category_id == '4' ? AL.STOCK_EQUITY_POINTS :item.name
                                                    } */}
                                                    {
                                                        item.name
                                                    }
                                                </span>
                                            </NavItem>
                                        )
                                    })
                                }
                            </ReactSlickSlider></Suspense>
                        </Nav>
                    </Col>
                </Row>
            </Tab.Container>
        )
    }

    LBModalHide = () => {
        this.setState({
            showLBModal: false
        });
    }
    LBModalShow = () => {
        this.setState({
            showLBModal: true
        })
    }
    setCurrentStockStatus =(type)=>{
        let item = this.state.stockLeaderboardData;
        this.setState({activeType:type},()=>{
            if(item && item != undefined){
                //this.onTabClick(item);
                let leaderboardData =  item.category_id == 3 || item.category_id ==4 || item.category_id ==5 ?  this.state.activeType == 1 ? item.leaderboard.weekly : item.leaderboard.monthly : item.leaderboard
                this.setState({ ATABITEM: item, PNO: 1, HMORE: false, PLIST: [], TOPTHREE: [], DETAILS: '', ATAB: item.category_id, LEADERBORD_TYPES: leaderboardData || [], ACTL: (leaderboardData || []).length > 0 ? leaderboardData[0] : '', ACTLIndex: 0 }, () => {
                    if (this.state.ACTL && item.category_id != '2') {
                        this.getLeaderboardData()
                    }
                });

            }

        })

    }

    render() {
        const {
            PLIST,
            ISLOAD,
            HMORE,
            TOPTHREE,
            LEADERBORD_TYPES,
            ACTLIndex,
            ATAB,
            DETAILS,
            showBtmBtn,
            ATABITEM,
            showLBModal,
            masterData
        } = this.state;
        const HeaderOption = {
            title: AL.LEADERBOARD,
            isPrimary: DARK_THEME_ENABLE ? false : true,
            info: true,
            infoAction: this.LBModalShow,
            back: this.state.isGL ? true :false
            // back: Utilities.getMasterData().allow_social == 1 ? true :false
        }
        let isTypeStock = this.state.type == 3 || this.state.type == 4 || this.state.type == 5  ? true :false;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"global-leaderboard-desktop pickem-leaderboard prediction-part-v prediction-wrap-v open-predict-leaderboard fantasy-ref-l leaderboard-frm-more"}>
                    <CustomHeader
                            {...this.props}
                            HeaderOption={HeaderOption}
                        />
                        {
                            this.renderTopTab()
                        }
                      
                        {
                            (this.state.type == 3 || this.state.type== 4 || this.state.type == 5) &&
                            <div className ={'week-month-container'}>
                                 <div onClick={()=>this.setCurrentStockStatus(1)} className ={'week-text' + (this.state.activeType == 1 ? ' active':'')}>{AL.WEEKLY}</div>
                                 <div onClick={()=>this.setCurrentStockStatus(0)}  className ={'month-text' + (this.state.activeType == 0 ? ' active':'')}>{AL.MONTHLY}</div>

                            </div>
                        }
                        {
                            LEADERBORD_TYPES.length > 1 && ATAB != '2' && this.renderMonthView(LEADERBORD_TYPES, ACTLIndex)
                        }
                        {
                            ATAB != '2' && <div className={"table-view"}>
                                {
                                    TOPTHREE && TOPTHREE.length > 0 && <div style={{ marginTop: LEADERBORD_TYPES.length > 1 ? SELECTED_GAMET == GameType.StockFantasy || GameType.StockFantasyEquity ? ((this.state.type == 3 || this.state.type== 4) ? 30  : 30):80 : 20, padding: '0 5px 15px' }} className="top-three-users top-three-users-new">
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
                                        <span className="amount">{AL.PRIZES}</span>
                                    </div>
                                }

                                {
                                    PLIST.length > 0 &&
                                    <InfiniteScroll
                                        dataLength={PLIST.length}
                                        hasMore={!ISLOAD && HMORE}
                                        next={() => this.getLeaderboardData()}
                                    >
                                        <div className="list-view" style={{ paddingBottom: 100 }}>
                                            {
                                                PLIST.map((item, idx) => {
                                                    return this.renderItem(item, idx)
                                                })
                                            }
                                        </div>
                                    </InfiniteScroll>
                                }
                                {/* {
                                    PLIST.length === 0 && TOPTHREE.length === 0 && !ISLOAD &&
                                    <div style={{ marginTop: '15%' }} className="no-data-leaderboard">
                                        <NoDataView
                                            BG_IMAGE={Images.no_data_bg_image}
                                            CENTER_IMAGE={Images.BRAND_LOGO_FULL}
                                            MESSAGE_1={AL.NOT_ENOUGH_DATA_ON_LEADERBOARD}
                                        /> 
                                    </div>
                                } */}
                                { PLIST.length === 0 && TOPTHREE.length === 0 && !ISLOAD &&
                                    <div className="nda-text" style={{marginTop : (LEADERBORD_TYPES.length > 1 && ATAB != '2' || (this.state.type == 3 || this.state.type== 4)) ? 0 : 30}}>There is not enough data to generate the leaderboard for the selected dates.</div>
                                }
                                { PLIST.length === 0 && TOPTHREE.length === 0 && !ISLOAD &&
                                    <div className="leaderbrd-ani-wrapper">
                                        <LBAnimation />
                                    </div>
                                }
                                {
                                    (ATAB != '2' && DETAILS.prize_detail) && (PLIST.length > 0 || TOPTHREE.length 
                                        > 0) && <div className={"roster-footer mb80 pl15 pr15 " + showBtmBtn}>
                                    {/* (ATAB != '2' && DETAILS.prize_detail) && <div className={"roster-footer pl15 pr15 "+(Utilities.getMasterData().allow_social== 1 ? ' mb50': ' mb80') + showBtmBtn}> */}
                                        <div className="btn-wrap">
                                            <button onClick={() => this.props.history.push({
                                                pathname: '/all-prizes',
                                                state: { prizeDetail: DETAILS.prize_detail }
                                            })} className="btn btn-block btm-fix-btn completed-league-preview">{AL.VIEW_ALL_PRIZES}</button>
                                        </div>
                                    </div>
                                }
                            </div>
                        }
                        {
                            ATAB == '2' && <div className={"table-view"}>
                                {
                                    <div className="list-view" style={{ paddingBottom: 100 }}>
                                        {ATABITEM.leaderboard && ATABITEM.leaderboard.length > 0 && this.renderLeagueItem(ATABITEM.leaderboard[0], 0, true)}
                                        {
                                            (ATABITEM.league || []).map((item, idx) => {
                                                return this.renderLeagueItem(item, idx + 1)
                                            })
                                        }
                                    </div>
                                }
                                {
                                !ATABITEM.league || ATABITEM.league.length === 0 &&
                                <div className="nda-text">There is not enough data to generate the leaderboard for the selected dates.</div>
                                }
                                {
                                    !ATABITEM.league || ATABITEM.league.length === 0 &&
                                    // <div className="no-data-leaderboard">
                                    //     <NoDataView
                                    //         BG_IMAGE={Images.no_data_bg_image}
                                    //         CENTER_IMAGE={Images.BRAND_LOGO_FULL}
                                    //         MESSAGE_1={AL.NO_DATA_AVAILABLE}
                                    //     />
                                    // </div>
                                    <div className="leaderbrd-ani-wrapper">
                                        <LBAnimation />
                                    </div>
                                }
                                {/* {
                                    (ATABITEM.league && ATABITEM.league.length > 0) && <div className={"roster-footer mb80 pl15 pr15 " + showBtmBtn}>
                                        <div className="btn-wrap">
                                            <button onClick={() => } className="btn btn-primary btn-block btm-fix-btn completed-league-preview">{AL.VIEW} {AL.COMPLETED}</button>
                                        </div>
                                    </div>
                                } */}
                            </div>
                        }
                        {
                            showLBModal &&
                            <Suspense fallback={<div />} >
                                <RefFantasyLeaderboardModal
                                    {...this.props}
                                    mShow={showLBModal}
                                    mHide={this.LBModalHide}
                                    lData={masterData}
                                />
                            </Suspense>
                        }
                    </div>

                )}
            </MyContext.Consumer>
        );
    }
}

export default FantasyRefLeaderboard;
