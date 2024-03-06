import React from 'react';
import { MyContext } from '../../views/Dashboard';
import { OverlayTrigger, Tooltip } from 'react-bootstrap';
import { NoDataView } from '../CustomComponent';
import Images from '../../components/images';
import { _times, _Map, _filter } from '../../Utilities/Utilities';
import { getFPPFixedPredictionCategory, getFPPFixedPredictionLeaderboard } from "../../WSHelper/WSCallings";
import * as AL from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";
import Skeleton from 'react-loading-skeleton';
import InfiniteScroll from 'react-infinite-scroll-component';
import WSManager from '../../WSHelper/WSManager';
import { Utilities } from '../../Utilities/Utilities';
import ls from 'local-storage';
import Moment from "react-moment";
import { GameType } from '../../helper/Constants';
import Filter from "../../components/filter";
import LBAnimation from '../../Component/FantasyRefLeaderboard/LeaderboardAnimation';

class OpenPredictionFPPLeaderboard extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            PLIST: [],
            OWNDATA: '',
            TOPTHREE: [],
            SPONSORDATA: [],
            PNO: 1,
            PSIZE: 20,
            categoryList: [],
            HMORE: false,
            ISLOAD: false,
            refreshList: true,
            showLFitlers: false,
            filterDataBy: 'today',
            CFilter : '',
            filterById: '1',
            OwnUserName: ls.get('profile'),
            showSponsorData: '',
            STARTDATE: '',
            ENDDATE: '',
            leadStatus: '',
            filerByTime: [
                {
                    value: 'today',
                    label: AL.TODAY,
                    prize_cat_id: '1'
                },
                {
                    value: 'this_week',
                    label: AL.THIS_WEEK,
                    prize_cat_id: '2'
                },
                {
                    value: 'this_month',
                    label: AL.THIS_MONTH,
                    prize_cat_id: '3'
                },
            ],
            filerByPreTime: [
                {
                    value: 'yesterday',
                    label: AL.YESTERDAY,
                    prize_cat_id: '1'
                },
                {
                    value: 'last_week',
                    label: AL.LAST_WEEK,
                    prize_cat_id: '2'
                },
                {
                    value: 'last_month',
                    label: AL.LAST_MONTH,
                    prize_cat_id: '3'
                },
            ]
        };

    }

    UNSAFE_componentWillMount() {
        WSManager.setPickedGameType(GameType.OpenPredLead);
        let url = window.location.href;
        if (!url.includes('#open-predictor-leaderboard')) {
            url = url + "#open-predictor-leaderboard";
        }
        window.history.replaceState("", "", url);
    }

    componentDidMount() {
        this.getCategory();
        this.getLeaderboardData();
    }

    UNSAFE_componentWillReceiveProps(nextProps) {
        if (this.state.showLFitlers != nextProps.showLobbyFitlers) {
            this.setState({ showLFitlers: nextProps.showLobbyFitlers })
        }
    }

    /** 
    @description hide filters 
    */
    hideFilter = () => {
        this.setState({ showLFitlers: false })
    }

    getCategory = () => {
        getFPPFixedPredictionCategory().then((responseJson) => {
            this.setState({ ISLOAD: false });
            if (responseJson.response_code === WSC.successCode) {
                this.setState({
                    categoryList: responseJson.data
                })
            }
        })
    }

    /**
    * @description - method to get leaderboard list
    */

    getLeaderboardData() {
        const { PNO, PSIZE, PLIST, CFilter, OWNDATA, filterDataBy , TOPTHREE,SPONSORDATA} = this.state;
        let param = {
            "category_id": CFilter.category_id,
            "page_no": PNO,
            "page_size": PSIZE,
            "filter": filterDataBy
        }
        this.setState({ ISLOAD: true });
        getFPPFixedPredictionLeaderboard(param).then((responseJson) => {
            this.setState({ ISLOAD: false });
            if (responseJson.response_code === WSC.successCode) {
                let ownData = responseJson.data.own || '';
                let listOther = responseJson.data.other_list || [];
                let topThree = responseJson.data.top_three || [];
                let sponserData = responseJson.data.sponsors || [];
                let startDate = responseJson.data.start_date || '';
                let endDate = responseJson.data.end_date || '';
                let status = responseJson.data.status;
                this.setState({
                    PLIST: [...PLIST, ...listOther],
                    OWNDATA: PNO === 1 ? ownData : OWNDATA,
                    TOPTHREE: PNO === 1 ? topThree : TOPTHREE,
                    SPONSORDATA: PNO === 1 ? sponserData : SPONSORDATA ,
                    HMORE: listOther.length >= (PSIZE - (ownData || OWNDATA ? 1 : 0)),
                    PNO: PNO + 1,
                    STARTDATE: startDate,
                    ENDDATE: endDate,
                    leadStatus: status
                },()=>{
                    this.showSponser()
                })
            }
        })
    }


    getMoreLData() {
        const { PNO, PSIZE, PLIST, CFilter, OWNDATA, filterDataBy , TOPTHREE,SPONSORDATA} = this.state;
        let param = {
            "category_id": CFilter.category_id,
            "page_no": PNO,
            "page_size": PSIZE,
            "filter": filterDataBy
        }
        this.setState({ ISLOAD: true });
        getFPPFixedPredictionLeaderboard(param).then((responseJson) => {
            this.setState({ ISLOAD: false });
            if (responseJson.response_code === WSC.successCode) {
                let ownData = responseJson.data.own || '';
                let listOther = responseJson.data.other_list || [];
                let topThree = responseJson.data.top_three || [];
                let sponserData = responseJson.data.sponsors || [];
                this.setState({
                    PLIST: [...PLIST, ...listOther],
                    OWNDATA: PNO === 1 ? ownData : OWNDATA,
                    TOPTHREE: PNO === 1 ? topThree : TOPTHREE,
                    SPONSORDATA: PNO === 1 ? sponserData : SPONSORDATA ,
                    HMORE: listOther.length >= (PSIZE - (ownData || OWNDATA ? 1 : 0)),
                    PNO: PNO + 1
                },()=>{
                    this.showSponser()
                })
            }
        })
    }

    renderShimmer = (idx) => {
        return (
            <div key={idx} className="list-item">
                <span className="shimmer">
                    <Skeleton height={6} width={'90%'} />
                    <Skeleton height={4} width={'50%'} />
                </span>
                <span className="amount">
                    <Skeleton height={6} width={'30%'} />
                </span>
                <span className="amount">
                    <Skeleton height={6} width={'40%'} />
                </span>
            </div>
        )
    }

    renderItem = (item, isown, idx) => {
        const {filterDataBy } = this.state;
        return (
            <div key={item.user_id + idx} id={item.user_id + idx} className={"list-item" + (isown ? ' own-v' : '')}>
                <span className="u-rank">{item.rank_value}</span>
                <span className="usernm">
                    {
                        isown ?
                            <React.Fragment>
                                <div className="usrnm-text">{this.state.OwnUserName.user_name}</div>
                                <div className="you-text">[{AL.YOU}]</div>
                            </React.Fragment>
                            :
                            item.user_name
                    }
                </span>
                <span className="amount">
                    <div className="val val-section">
                        {item.prize_data && item.prize_data.length > 0 ?
                            <React.Fragment>
                                {
                                    item.prize_data[0].prize_type != 3 &&
                                        <React.Fragment>
                                            <span>
                                                {
                                                    item.prize_data[0].prize_type == 0
                                                        ?
                                                        <i className="icon-bonus"></i>
                                                        :
                                                        item.prize_data[0].prize_type == 1 ?
                                                            Utilities.getMasterData().currency_code
                                                            :
                                                            <img src={Images.IC_COIN} alt="" />
                                                }
                                            </span>
                                            <React.Fragment>
                                                {Utilities.kFormatter(item.prize_data[0].amount)}
                                            </React.Fragment>
                                        </React.Fragment>
                                }
                                {item.prize_data[0].prize_type == 3 &&
                                    <React.Fragment>
                                        <OverlayTrigger rootClose trigger={['click']} placement="bottom" overlay={
                                            <Tooltip id="tooltip" >
                                                <strong>{item.prize_data[0].name}</strong>
                                            </Tooltip>
                                        }>
                                            <div className="win"> 
                                                {item.prize_data[0].name}
                                            </div>
                                        </OverlayTrigger>
                                    </React.Fragment>
                                }
                            </React.Fragment>
                            :
                            <React.Fragment>
                                {
                                    (filterDataBy == "last_week" || filterDataBy == "last_month" || filterDataBy == "yesterday") ?
                                    <div className="win">--</div>
                                    :
                                    this.showPrize(item.rank_value)
                                }
                            </React.Fragment>
                        }
                    </div>
                </span>
                <span className="corrected">{item.correct_answer}/{item.attempts}</span>
            </div>
        )
    }

    showSponser=()=>{
        const {SPONSORDATA,filterById } = this.state;
        let sponsor = _filter(SPONSORDATA, (item) => {
            return item.prize_category == filterById
        });
        this.setState({
            showSponsorData : sponsor
        })     
    }

    showPrize=(data)=>{
        const {showSponsorData,CFilter} = this.state;
        let rank = parseInt(data);
        let tmpSData = showSponsorData && showSponsorData.length > 0 ? showSponsorData[0] : [];
        let traverse = true;
        let prize = [];
        _Map(tmpSData.prize_distribution_detail,(item,idx)=>{
            let max = parseInt(item.max);
            let min = parseInt(item.min);
            if(traverse && ((max > rank && min < rank) || (max == rank) || (min == rank))){
                prize.push(item);
                traverse = false;
            }
        })

        let item = prize && prize.length > 0 ? prize[0] : '';

        return <React.Fragment>
            {
                CFilter == '' && item && item.amount ?
                    <div className={"win" + (item.prize_type == 2 ? ' win-pL3' : '')}>
                        {
                            item.prize_type != 3 &&
                            <React.Fragment>
                                {
                                    item.prize_type == 0
                                        ?
                                        <span className="bns-span">
                                            <i className="icon-bonus"></i>
                                        </span>
                                        :
                                        item.prize_type == 1 ?
                                            <span className="rupee-span">{Utilities.getMasterData().currency_code}</span>
                                            :
                                            <span className="coin-span">
                                                <img src={Images.IC_COIN} alt="" />
                                            </span>
                                }
                                <React.Fragment>
                                    {Utilities.kFormatter(item.amount)}
                                </React.Fragment>
                            </React.Fragment>
                        }
                        {item.prize_type == 3 &&
                            <React.Fragment>
                                <OverlayTrigger rootClose trigger={['click']} placement="bottom" overlay={
                                    <Tooltip id="tooltip" >
                                        <strong>{item.amount}</strong>
                                    </Tooltip>
                                }>
                                    <div className="win"> 
                                        {item.amount}
                                    </div>
                                </OverlayTrigger>
                            </React.Fragment>
                        }
                    </div>
                    :
                <div className="win">
                    --
                </div>
            }
        </React.Fragment>
           
    }

    renderTopUser = (item) =>{
        const {filterDataBy,CFilter} = this.state;
        
        let itemLength = item ? item.length : 0;
        let FirstUser = itemLength > 0 ? item[0] : '';
        let SecondUser = itemLength > 1 ? item[1] : '';
        let ThirdUser = itemLength > 2 ? item[2] : '';

        return (
            <React.Fragment>
                <div className={"rank-section second-rank" + (itemLength > 1 ? '' : ' disabled')}>
                    <div className="section-data">
                        <div className="circle-wrap">
                            <span className="rank-pos second">
                                <span className="img-section"></span>
                                <span className="pos-text">2</span>
                            </span>
                            <div>{item.rank_value}</div>
                            {
                                CFilter == '' && SecondUser && SecondUser.prize_data && SecondUser.prize_data.length > 0 ?
                                    <div className={"win" + (SecondUser.prize_type == 2 ? ' win-pL3' : '')}>
                                        {
                                            SecondUser.prize_data[0].prize_type != 3 &&
                                            <React.Fragment>
                                                {
                                                    SecondUser.prize_data[0].prize_type == 0
                                                        ?
                                                        <span className="bns-span">
                                                            <i className="icon-bonus"></i>
                                                        </span>
                                                        :
                                                        SecondUser.prize_data[0].prize_type == 1 ?
                                                            <span className="rupee-span">{Utilities.getMasterData().currency_code}</span>
                                                            :
                                                            <span className="coin-span">
                                                                <img src={Images.IC_COIN} alt="" />
                                                            </span>
                                                }
                                                <React.Fragment>
                                                    {Utilities.kFormatter(SecondUser.prize_data[0].amount)}
                                                </React.Fragment>
                                            </React.Fragment>
                                        }
                                        { SecondUser.prize_data[0].prize_type == 3 &&
                                            <React.Fragment>
                                                <OverlayTrigger rootClose trigger={['click']} placement="bottom" overlay={
                                                    <Tooltip id="tooltip" >
                                                        <strong>{SecondUser.prize_data[0].name}</strong>
                                                    </Tooltip>
                                                }>
                                                    <div className="win"> 
                                                        {SecondUser.prize_data[0].name}
                                                    </div>
                                                </OverlayTrigger>
                                            </React.Fragment>
                                        }
                                    </div>
                                    :
                                    <React.Fragment>
                                        {
                                            (filterDataBy == "last_week" || filterDataBy == "last_month" || filterDataBy == "yesterday") ?
                                            <div className="win">--</div>
                                            :
                                            <React.Fragment>{this.showPrize(2)}</React.Fragment>
                                        }
                                    </React.Fragment>
                            }
                            <div className="corrected">{SecondUser.correct_answer || 0}/{SecondUser.attempts || 0}</div>
                        </div>
                        <div className="winner-name">{SecondUser.user_name || 'User Name'}</div>
                    </div>
                </div>
                <div className={"rank-section first-rank" + (itemLength > 0 ? '' : ' disabled')}>
                    <div className="section-data">
                        <div className="circle-wrap">
                            <span className="rank-pos first">
                                <span className="img-section"></span>
                            <span className="pos-text">1</span>
                            </span>
                            {
                                CFilter == '' && FirstUser && FirstUser.prize_data && FirstUser.prize_data.length > 0 ?
                                    <div className={"win" + (FirstUser.prize_type == 2 ? ' win-pL3' : '')}>
                                        {
                                            FirstUser.prize_data[0].prize_type != 3 &&
                                                <React.Fragment>
                                                    {
                                                        FirstUser.prize_data[0].prize_type == 0
                                                            ?
                                                            <span className="bns-span">
                                                                <i className="icon-bonus"></i>
                                                            </span>
                                                            :
                                                            FirstUser.prize_data[0].prize_type == 1 ?
                                                                <span className="rupee-span">{Utilities.getMasterData().currency_code}</span>
                                                                :
                                                                <span className="coin-span">
                                                                    <img src={Images.IC_COIN} alt="" />
                                                                </span>
                                                    }
                                                    <React.Fragment>
                                                        {Utilities.kFormatter(FirstUser.prize_data[0].amount)}
                                                    </React.Fragment>
                                                </React.Fragment>
                                        }
                                        { 
                                            FirstUser.prize_data[0].prize_type == 3 &&
                                            <React.Fragment>
                                                <OverlayTrigger rootClose trigger={['click']} placement="bottom" overlay={
                                                    <Tooltip id="tooltip" >
                                                        <strong>{FirstUser.prize_data[0].name}</strong>
                                                    </Tooltip>
                                                }>
                                                    <div className="win"> 
                                                        {FirstUser.prize_data[0].name}
                                                    </div>
                                                </OverlayTrigger>
                                            </React.Fragment>
                                        }
                                    </div>
                                    :
                                    <React.Fragment>
                                        {
                                            (filterDataBy == "last_week" || filterDataBy == "last_month" || filterDataBy == "yesterday") ?
                                            <div className="win">--</div>
                                            :
                                            <React.Fragment>{this.showPrize(1)}</React.Fragment>
                                        }
                                    </React.Fragment>
                            }
                            <div className="corrected">{FirstUser.correct_answer || 0}/{FirstUser.attempts || 0}</div>
                        </div>
                        <div className="winner-name">{FirstUser.user_name || 'User Name'}</div>
                    </div>
                </div>
                <div className={"rank-section third-rank" + (itemLength > 2 ? '' : ' disabled')}>
                    <div className="section-data">
                        <div className="circle-wrap">
                            <span className="rank-pos third">
                                <span className="img-section"></span>
                                <span className="pos-text">3</span>
                            </span>
                            {
                                CFilter == '' && ThirdUser && ThirdUser.prize_data && ThirdUser.prize_data.length > 0 ?
                                    <div className={"win" + (ThirdUser.prize_type == 2 ? ' win-pL3' : '')}>
                                        {
                                            ThirdUser.prize_data[0].prize_type != 3 &&
                                                <React.Fragment>
                                                    {
                                                        ThirdUser.prize_data[0].prize_type == 0
                                                            ?
                                                            <span className="bns-span">
                                                                <i className="icon-bonus"></i>
                                                            </span>
                                                            :
                                                            ThirdUser.prize_data[0].prize_type == 1 ?
                                                                <span className="rupee-span">{Utilities.getMasterData().currency_code}</span>
                                                                :
                                                                <span className="coin-span">
                                                                    <img src={Images.IC_COIN} alt="" />
                                                                </span>
                                                    }
                                                    <React.Fragment>
                                                        {Utilities.kFormatter(ThirdUser.prize_data[0].amount)}
                                                    </React.Fragment>
                                                </React.Fragment>
                                        }
                                        {
                                            ThirdUser.prize_data[0].prize_type == 3 &&
                                                <React.Fragment>
                                                    <OverlayTrigger rootClose trigger={['click']} placement="bottom" overlay={
                                                        <Tooltip id="tooltip" >
                                                            <strong>{ThirdUser.prize_data[0].name}</strong>
                                                        </Tooltip>
                                                    }>
                                                        <div className="win"> 
                                                            {ThirdUser.prize_data[0].name}
                                                        </div>
                                                    </OverlayTrigger>
                                                </React.Fragment>
                                        }
                                    </div>
                                    :
                                    <React.Fragment>
                                        {
                                            (filterDataBy == "last_week" || filterDataBy == "last_month" || filterDataBy == "yesterday") ?
                                            <div className="win">--</div>
                                            :
                                            <React.Fragment>{this.showPrize(3)}</React.Fragment>
                                        }
                                    </React.Fragment>
                            }
                            <div className="corrected">{ThirdUser.correct_answer || 0}/{ThirdUser.attempts || 0}</div>
                        </div>
                        <div className="winner-name">{ThirdUser.user_name || 'User Name'}</div>
                    </div>
                </div>
                
            </React.Fragment>
        )
    } 

    showSponsor = (item,idx) =>{
        const {filterById} = this.state;
        let data = filterById == item.prize_category ? item : '';
        return (
            <React.Fragment>
                {
                    data != '' && data.sponsor_name &&
                        <div className="sponsored-section">
                            <span className="sponsored-text">{AL.SPONSOR_BY}</span>
                            <img src={Utilities.getOpenPredFPPURL(item.sponsor_logo)} alt=""/>
                        </div>
                }
            </React.Fragment>
        )
    }

    filterLeaderboard = (filterBy) => {
        this.setState({
            showLFitlers: false,
            CFilter : filterBy,
            PLIST: [],
            PNO: 1,
            PSIZE: 20,
            OWNDATA: ''
        }, () => {
            this.getLeaderboardData();
        })
    }

    handleTimeFilter=(filterBy, id) =>{
        this.setState({
            filterDataBy: filterBy,
            filterById: id,
            PLIST: [],
            PNO: 1,
            PSIZE: 20,
            OWNDATA: ''
        }, () => {
            this.getLeaderboardData();
        })
    }

    render() {
        const {
            categoryList,
            PLIST,
            OWNDATA,
            ISLOAD,
            HMORE,
            refreshList,
            CFilter,
            showLFitlers,
            filerByTime,
            filterDataBy,
            TOPTHREE,
            SPONSORDATA,
            filerByPreTime,
            filterById,
            STARTDATE,
            ENDDATE,
            leadStatus
        } = this.state;

        let FitlerOptions = {
            showLFitler: showLFitlers
        }

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="prediction-wrap-v prediction-part-v open-predict-leaderboard is-leaderboard fpp-leaderboard">
                        <Filter
                            {...this.props}
                            FitlerOptions={FitlerOptions}
                            hideFilter={this.hideFilter}
                            filerObj={categoryList}
                            filterLeaderboard={this.filterLeaderboard}
                            filterDataBy={CFilter}
                        />
                        <div className="fixed-ch-view">                              
                            <div className="filter-time-section">
                                <ul className="filter-time-wrap">
                                    {
                                        _Map(filerByTime,(item,idx)=>{
                                            return(
                                                <li
                                                    href 
                                                    className={"filter-time-btn" + 
                                                        (item.value == filterDataBy ? ' active' : '') + 
                                                        (item.prize_cat_id == 2 && filterById == 2 && STARTDATE ? ' with-date' : '')
                                                        } 
                                                        onClick={()=>this.handleTimeFilter(item.value, item.prize_cat_id)}
                                                >
                                                    {item.label} 
                                                    {
                                                        (item.prize_cat_id == 2 && filterById == 2) && STARTDATE &&
                                                        <span>
                                                            <Moment date={STARTDATE} format={"D MMM "} /> 
                                                            -
                                                            <Moment date={ENDDATE} format={" D MMM "} /> 
                                                        </span>
                                                    }
                                                </li>
                                            )
                                        })
                                    }
                                </ul>
                            </div>

                            <div className="previous-data">
                                {
                                    _Map(filerByPreTime,(item,idx)=>{
                                        return(
                                            <React.Fragment>
                                                {
                                                    filterById === item.prize_cat_id &&
                                                    <a 
                                                        href 
                                                        className={"previous-time-btn" + ((filterDataBy === 'last_week' || filterDataBy === 'last_month' || filterDataBy === 'yesterday') ? ' active' : '')}
                                                        onClick={()=>this.handleTimeFilter(item.value, item.prize_cat_id)}
                                                        >
                                                            <i className="icon-arrow-up"></i>
                                                            <i className="icon-arrow-up"></i>
                                                            {item.label}
                                                    </a>
                                                }
                                            </React.Fragment>
                                        )
                                    })
                                }
                                {
                                    leadStatus == 0 &&
                                    <div className="leader-status">
                                        <span></span>{AL.LIVE}
                                    </div>
                                }
                                {
                                    leadStatus == 3 &&
                                    <div className="leader-status comp">
                                        {AL.COMPLETED}
                                    </div>
                                }
                            </div>
                        </div>
                        <div className="table-view">
                            <div className="top-three-users">                                
                                {
                                    TOPTHREE && TOPTHREE.length > 0 && this.renderTopUser(TOPTHREE)
                                }
                                <div className="white-section"></div>
                            </div>
                            {
                                TOPTHREE && TOPTHREE.length > 0 && SPONSORDATA && SPONSORDATA.length > 0 &&
                                    SPONSORDATA.map((item,idx)=>{
                                        return this.showSponsor(item)
                                    })
                            }
                            {
                                ((PLIST && PLIST.length > 0) || (OWNDATA && OWNDATA.length > 0) || (TOPTHREE && TOPTHREE.length > 0) ) &&
                                <div className="header-v">
                                    <span className="u-rank">{AL.RANK}</span>
                                    <span className="usernm">{AL.USER_NAME}</span>
                                    <span className="amount">{AL.PRIZE}</span>
                                    <span className="corrected text-capitalize ellipsis-text">{AL.CORRECTED}</span>
                                </div>
                            }
                        
                            {
                                refreshList && OWNDATA && this.renderItem(OWNDATA, true, -1)
                            }
                            {
                                PLIST.length > 0 && <InfiniteScroll
                                    dataLength={PLIST.length}
                                    hasMore={!ISLOAD && HMORE}
                                    next={() => this.getMoreLData()}
                                >
                                    <div className="list-view">
                                        {
                                            PLIST.map((item, idx) => {
                                                return this.renderItem(item, false, idx);
                                            })
                                        }
                                    </div>
                                </InfiniteScroll>
                            }
                            {
                                PLIST.length === 0 && OWNDATA.length === 0 && TOPTHREE.length === 0 && !ISLOAD &&
                                // <div className="no-data-leaderboard">
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
                                PLIST.length === 0 && !OWNDATA && !ISLOAD && TOPTHREE.length != 0 &&
                                // <NoDataView
                                //     BG_IMAGE={Images.no_data_bg_image}
                                //     CENTER_IMAGE={Images.BRAND_LOGO_FULL}
                                //     MESSAGE_1={AL.NO_DATA_FOUND}
                                //     MESSAGE_2={AL.NO_DATA_FOR_FILTER}
                                // />
                                <div className="leaderbrd-ani-wrapper">
                                    <LBAnimation />
                                </div>
                            }
                            {
                                PLIST.length === 0 && ISLOAD &&
                                _times(16, (idx) => {
                                    return this.renderShimmer(idx)
                                })
                            }
                        </div>
                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}

export default OpenPredictionFPPLeaderboard;
