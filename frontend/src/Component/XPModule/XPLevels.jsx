import React from 'react';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import { _times,_Map,Utilities } from '../../Utilities/Utilities';
import {getXPRewardList } from '../../WSHelper/WSCallings';
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import Skeleton,{SkeletonTheme} from 'react-loading-skeleton';
import MetaData from "../../helper/MetaData";
import CustomHeader from '../../components/CustomHeader';
import { DARK_THEME_ENABLE } from '../../helper/Constants';
import Images from '../../components/images';
import { NoDataView } from '../../Component/CustomComponent';
import InfiniteScroll from 'react-infinite-scroll-component';
export default class XPLevels extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            xpLevelArray: [],
            userXpPoint: 0,
            isLoading: false,
            hasMore: true,
            pageNo: 1,
            page_size: 20,
            userXpDetail: ''
        }
    }

    componentDidMount() {
        this.getXpLevelList()
    }

    getXpLevelList=()=>{
        this.setState({isLoading: true})
        let param = {
            'page_no': this.state.pageNo,
            'page_size': this.state.page_size
        }
        getXPRewardList(param).then((responseJson) => {
            this.setState({isLoading: false})
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({
                    xpLevelArray: responseJson.data.reward_list,
                    userXpPoint: responseJson.data.user_xp.point,
                    userXpDetail: responseJson.data.user_xp,
                    hasMore:  responseJson.data.reward_list.length === this.state.page_size,
                    pageNo:  this.state.pageNo + 1,
                })
            }
        })
    }

    onloadMore=()=>{
        if(!this.state.isLoading && this.state.hasMore)
        this.setState({ hasMore: false })
        this.getXpLevelList()
    }

    earnMorePoints=()=>{
        this.props.history.push({ pathname: '/experience-points', state: {goBackProfile: true}  });
    }

    render() {
        const { xpLevelArray , userXpPoint, isLoading, hasMore,userXpDetail} = this.state;
        const HeaderOption = {
            back: true,
            goBackProfile: this.props && this.props.location && this.props.location.state && this.props.location.state.from && this.props.location.state.from == 'notification' ? true : false,
            notification: false,
            title: AL.XP_LEVELS,
            hideShadow: true,
            isPrimary: DARK_THEME_ENABLE ? false : true
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
                        <div className="user-xp-pot">
                            <span className="points">
                                <img src={Images.EARN_XPPOINTS} alt="" width="16px" /> {Utilities.kFormatter(userXpPoint) || 0}
                            </span>
                            <span>{AL.YOUR_XP_POINTS}</span>
                        </div>
                        
                        <div className="xp-header xs"></div>
                        <div className="xp-body top-up">
                            <div className="xp-learn-header cursor-pointer" onClick={()=>this.earnMorePoints()}>
                                <img src={Images.EARN_XPPOINTS} alt="" width="16px" />
                                <span>{AL.LEARN_EARN_MORE_XP_POINTS}</span>
                            </div>
                            <InfiniteScroll
                                dataLength={xpLevelArray.length}
                                next={()=>this.onloadMore()}
                                hasMore={!isLoading && hasMore}
                                scrollableTarget={'scrollableTarget'}
                                loader={
                                    this.state.isLoadMoreLoaderShow &&
                                    <h4 className='table-loader'>{AL.LOADING_MSG}</h4>
                                } >
                                {
                                    xpLevelArray && xpLevelArray.length > 0 && !isLoading &&
                                    <ul className="xp-list levels-list" id="scrollableTarget">
                                        {
                                            _Map(xpLevelArray,(Item,idx) =>{
                                                return(
                                                    <li key={Item.badge_id + idx} className={(Item.badge_id == 1 ? "bronze" : Item.badge_id == 2 ? "silver" : Item.badge_id == 3 ? "gold" : Item.badge_id == 4 ? "platinum" :  Item.badge_id == 5 ? "diamond" : Item.badge_id == 6 ? "elite" : "") + (userXpDetail.level_number == Item.level_number ? " highlight-li":"")}>
                                                        <div className="li-bor">
                                                            <div> 
                                                                <div className="levels-list-img">
                                                                    <img src={Item.badge_id == 1 ? Images.XP_BRONZE : Item.badge_id == 2 ? Images.XP_SILVER : Item.badge_id == 3 ? Images.XP_GOLD : Item.badge_id == 4 ? Images.XP_PLATINUM : Item.badge_id == 5 ? Images.XP_DIAMOND : Item.badge_id == 6 ? Images.XP_ELITE : Images.XP_DEFAULT_BADGE} alt="" />
                                                                    {
                                                                        Item.badge_name &&
                                                                        <span>{Item.badge_name}</span>
                                                                    }
                                                                </div>
                                                                
                                                                <div className="level-deatils">
                                                                    <h4>{AL.LEVEL} {Item.level_number}</h4>
                                                                    {
                                                                        (Item.is_coin == '1' || Item.is_cashback == '1' || Item.is_contest_discount == '1') &&
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
                                                                    }
                                                                    {/* <span>Booster Slot #1</span> */}
                                                                </div>
                                                            </div>
                                                            <div className="right-sec">
                                                                <img src={Images.EARN_XPPOINTS} alt="" width="16px" className="star-img" />
                                                                <h3>{Item.start_point}-{Item.end_point}</h3>
                                                            </div>
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
                                        // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
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