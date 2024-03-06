import React from 'react';
import { MyContext } from '../../views/Dashboard';
import { NoDataView } from '../CustomComponent';
import Images from '../../components/images';
import { _times, _Map } from '../../Utilities/Utilities';
import { getFixedPredictionCategory, getFixedPredictionLeaderboard } from "../../WSHelper/WSCallings";
import * as AL from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";
import Skeleton from 'react-loading-skeleton';
import InfiniteScroll from 'react-infinite-scroll-component';
import WSManager from '../../WSHelper/WSManager';
import {GameType} from '../../helper/Constants';
import Filter from "../../components/filter";
import LBAnimation from '../../Component/FantasyRefLeaderboard/LeaderboardAnimation';
class OpenPredictionLeaderboard extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            PLIST: [],
            OWNDATA: '',
            PNO: 1,
            PSIZE: 20,
            categoryList: [],
            HMORE: false,
            ISLOAD: false,
            refreshList: true,
            showLFitlers: false,
            filterDataBy: 'today',
            CFilter : '',
            filerByTime: [
                {
                    value: 'today',
                    label: AL.TODAY
                },
                {
                    value: 'this_week',
                    label: AL.THIS_WEEK
                },
                {
                    value: 'this_month',
                    label: AL.THIS_MONTH
                },
            ]
        };

    }

    UNSAFE_componentWillMount() {
        WSManager.setPickedGameType(GameType.OpenPred);
        let url = window.location.href;
        if (!url.includes('#open-predictor')) {
            url = url + "#open-predictor";
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
        getFixedPredictionCategory().then((responseJson) => {
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
        const { PNO, PSIZE, PLIST, CFilter, OWNDATA, filterDataBy } = this.state;
        let param = {
            "category_id": CFilter.category_id,
            "page_no": PNO,
            "page_size": PSIZE,
            "filter": filterDataBy
        }
        this.setState({ ISLOAD: true });
        getFixedPredictionLeaderboard(param).then((responseJson) => {
            this.setState({ ISLOAD: false });
            if (responseJson.response_code === WSC.successCode) {
                let ownData = responseJson.data.own || '';
                let listOther = responseJson.data.other_list || [];
                this.setState({
                    PLIST: [...PLIST, ...listOther],
                    OWNDATA: PNO === 1 ? ownData : OWNDATA,
                    HMORE: listOther.length >= (PSIZE - (ownData || OWNDATA ? 1 : 0)),
                    PNO: PNO + 1
                })
            }
        })
    }


    getMoreLData() {
        const { PNO, PSIZE, PLIST, CFilter, OWNDATA, filterDataBy } = this.state;
        let param = {
            "category_id": CFilter.category_id,
            "page_no": PNO,
            "page_size": PSIZE,
            "filter": filterDataBy
        }
        this.setState({ ISLOAD: true });
        getFixedPredictionLeaderboard(param).then((responseJson) => {
            this.setState({ ISLOAD: false });
            if (responseJson.response_code === WSC.successCode) {
                let ownData = responseJson.data.own || '';
                let listOther = responseJson.data.other_list || [];
                this.setState({
                    PLIST: [...PLIST, ...listOther],
                    OWNDATA: PNO === 1 ? ownData : OWNDATA,
                    HMORE: listOther.length >= (PSIZE - (ownData || OWNDATA ? 1 : 0)),
                    PNO: PNO + 1
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
        return (
            <div key={item.user_id + idx} id={item.user_id + idx} className={"list-item" + (isown ? ' own-v' : '')}>
                <span className="u-rank">{item.user_rank}</span>
                <span>{item.user_name}</span>
                <span>{item.total_wins}</span>
                <span className="amount"><img src={Images.IC_COIN} alt="" /><div className="val">{item.win_coins || 0}</div></span>
            </div>
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

    handleTimeFilter=(filterBy) =>{
        this.setState({
            filterDataBy: filterBy,
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
            filterDataBy
        } = this.state;

        let FitlerOptions = {
            showLFitler: showLFitlers
        }

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="prediction-wrap-v prediction-part-v open-predict-leaderboard is-leaderboard">
                        <div>
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
                                    {
                                        _Map(filerByTime,(item,idx)=>{
                                            return(
                                                <a 
                                                    href 
                                                    className={"filter-time-btn" + (item.value == filterDataBy ? ' active' : '')}
                                                    onClick={()=>this.handleTimeFilter(item.value)}>
                                                    {item.label}
                                                </a>
                                            )
                                        })
                                    }
                                </div>
                                <div className="header-v m-t-sm">
                                    <span className="u-rank">{AL.RANK}</span>
                                    <span className="usernm">{AL.USER_NAME}</span>
                                    <span className="usernm text-capitalize ellipsis-text">{AL.CORRECT_PREDICTIONS}</span>
                                    <span className="amount">{AL.COINS_WON}</span>
                                </div>
                            </div>
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
                                PLIST.length === 0 && !OWNDATA && !ISLOAD &&
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

export default OpenPredictionLeaderboard;
