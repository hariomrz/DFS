import React from 'react';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import { _times, Utilities, _Map } from '../../Utilities/Utilities';
import { getUserXPHistory } from '../../WSHelper/WSCallings';
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import MetaData from "../../helper/MetaData";
import CustomHeader from '../../components/CustomHeader';
import { DARK_THEME_ENABLE } from '../../helper/Constants';
import Images from '../../components/images';
import { MomentDateComponent, NoDataView } from '../../Component/CustomComponent';
import InfiniteScroll from 'react-infinite-scroll-component';

export default class XPPointsHistory extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            userXPHistory: [],
            userXpPoint: 0,
            isLoading: false,
            hasMore: false,
            pageNo: 1,
            page_size: 20,
        }
    }

    componentDidMount() {
        this.getUserXPHistory()
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
                    userXPHistory: this.state.pageNo == 1 ? responseJson.data.history : [...this.state.userXPHistory,...responseJson.data.history] ,
                    userXpPoint: responseJson.data.user_xp.point,
                    hasMore: responseJson.data.history.length === this.state.page_size,
                    pageNo: this.state.pageNo + 1,
                })
            }
        })
    }

    onLoadMore() {
        if (!this.state.isLoading && this.state.hasMore) {
            this.getUserXPHistory()            
        }
    }


    render() {
        const HeaderOption = {
            back: true,
            notification: false,
            title: AL.EARNING_POINTS_HISTORY,
            hideShadow: true,
            isPrimary: DARK_THEME_ENABLE ? false : true
        }

        const { userXPHistory, isLoading, hasMore, userXpPoint } = this.state;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container transparent-header web-container-fixed xp-wrapper wallet-wrapper xp-history">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.mywallet.title}</title>
                            <meta name="description" content={MetaData.mywallet.description} />
                            <meta name="keywords" content={MetaData.mywallet.keywords}></meta>
                        </Helmet>
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        <div className="user-xp-pot">
                            <span className="points">
                                <img src={Images.EARN_XPPOINTS} alt="" width="16px" /> {Utilities.kFormatter(userXpPoint) || 0}
                            </span>
                            <span>{AL.YOUR_XP_POINTS}</span>
                        </div>
                        <div className="xp-trans-wrap">
                            <div className="xp-trans-header-wrap">
                                <div>{AL.TRANSACTION}</div>
                                <div>{AL.XP}</div>
                            </div>
                            <div className="trans-body-wrap">
                                <InfiniteScroll
                                    dataLength={userXPHistory.length}
                                    next={this.onLoadMore.bind(this)}
                                    hasMore={!isLoading && hasMore}
                                    >
                                    {
                                        userXPHistory && userXPHistory.length > 0 && _Map((userXPHistory),(item,idx)=>{
                                            return(
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
                                <Skeleton width={40} height={10} />
                            </div>
                        </div>
                        <div className="shimmer-inner">
                            <Skeleton width={24} height={4} />
                        </div>
                    </div>
                </div>
            </SkeletonTheme>
        )
    }
}