import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { NoDataView } from '../CustomComponent';
import { getPredictionParticipants } from '../../WSHelper/WSCallings';
import { Utilities, _times } from '../../Utilities/Utilities';
import InfiniteScroll from 'react-infinite-scroll-component';
import Skeleton,{SkeletonTheme} from 'react-loading-skeleton';
import Helmet from 'react-helmet';
import MetaData from '../../helper/MetaData';
import CustomHeader from '../../components/CustomHeader';
import Images from '../../components/images';
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import * as Constants from "../../helper/Constants";

class PredictionParticipants extends Component {
    constructor(props) {
        super(props)
        this.state = {
            PLIST: [],
            OWNDATA: '',
            PNO: 1,
            PSIZE: 20,
            HMORE: false,
            ISLOAD: false,
            PMID: '',
            isLeader: false,
            HOS: {
                back: true,
                title: 'Participants',
                isPrimary: Constants.DARK_THEME_ENABLE ? false : true
            }
        }
    }

    UNSAFE_componentWillMount() {
        Utilities.setScreenName('PRDPLIST')
        
        if (this.props.match && this.props.match.params) {
            const matchParam = this.props.match.params;
            let pmid = atob(matchParam.prediction_master_id)
            this.setState({
                PMID: pmid,
                isLeader: (this.props.location && this.props.location.state) ? this.props.location.state.isLeader : false
            }, () => {
                this.getDetail();
            });
        }
    }

    getDetail() {
        const { PNO, PSIZE, PLIST, PMID, OWNDATA, isLeader } = this.state;
        let param = {
            "prediction_master_id": PMID,
            "page_no": PNO,
            "page_size": PSIZE,
            "isLeader": isLeader
        }
        this.setState({ ISLOAD: true });
        getPredictionParticipants(param).then((responseJson) => {
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
            <SkeletonTheme color={Constants.DARK_THEME_ENABLE ? "#030409" : null} highlightColor={Constants.DARK_THEME_ENABLE ? "#0E2739" : null}>
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
            </SkeletonTheme>
        )
    }

    renderItem = (item, isown, idx) => {
        return (
            <div key={item.user_id + idx} id={item.user_id + idx} className={"list-item" + (isown ? ' own-v' : '')}>
                {this.state.isLeader && <span className="u-rank">{item.user_rank}</span>}
                <span>{item.user_name}</span>
                <span className="amount"><img src={Images.IC_COIN} alt="" /><div className="val">{item.bet_coins}</div></span>
                <span className="amount"><img src={Images.IC_COIN} alt="" /><div className="val">{(this.state.isLeader ? item.win_coins : item.estimated_winning) || 0}</div></span>
            </div>
        )
    }

    render() {
        const { PLIST, HOS, ISLOAD, OWNDATA, HMORE, isLeader } = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"web-container sport-pred prediction-part-v" + (isLeader ? ' is-leaderboard' : '')}>
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.PRDPLIST.title}</title>
                            <meta name="description" content={MetaData.PRDPLIST.description} />
                            <meta name="keywords" content={MetaData.PRDPLIST.keywords}></meta>
                        </Helmet>
                        <CustomHeader {...this.props} HeaderOption={HOS} />
                        <div className="header-v">
                            {isLeader && <span className="u-rank">{AL.RANK}</span>}
                            <span>{AL.USER_NAME}</span>
                            <span className="amount">{AL.BID}</span>
                            <span className="amount">{isLeader ? AL.WINNINGS : AL.EST_WIN}</span>
                        </div>
                        {
                            OWNDATA && this.renderItem(OWNDATA, true, -1)
                        }
                        {
                            PLIST.length > 0 && <InfiniteScroll
                                dataLength={PLIST.length}
                                hasMore={!ISLOAD && HMORE}
                                next={() => this.getDetail()}
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
                            <NoDataView
                                BG_IMAGE={Images.no_data_bg_image}
                                // CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                MESSAGE_1={AL.NO_DATA_FOUND}
                            />
                        }
                        {
                            PLIST.length === 0 && ISLOAD &&
                            _times(16, (idx) => {
                                return this.renderShimmer(idx)
                            })
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}
export default PredictionParticipants;