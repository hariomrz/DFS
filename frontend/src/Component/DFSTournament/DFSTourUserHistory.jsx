import React, { Component } from 'react';
import ls from 'local-storage';
import { isMobile } from 'react-device-detect';
import { MyContext } from '../../views/Dashboard';
import { MomentDateComponent, NoDataView } from '../CustomComponent';
import { getDFSTourUserHistory} from '../../WSHelper/WSCallings';
import { _times } from '../../Utilities/Utilities';
import InfiniteScroll from 'react-infinite-scroll-component';
import Skeleton,{SkeletonTheme} from 'react-loading-skeleton';
import Helmet from 'react-helmet';
import MetaData from '../../helper/MetaData';
import CustomHeader from '../../components/CustomHeader';
import Images from '../../components/images';
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import {DARK_THEME_ENABLE} from '../../helper/Constants';
import { Utilities } from '../../Utilities/Utilities';
import DFSTourFieldViewRight from "./DFSTourFieldViewRight";
import moment from 'moment';

var globalThis = null;
class DFSUserTourHistory extends Component {
    constructor(props) {
        super(props)
        this.state = {
            TourData: [],
            userPreData: [],
            userData: '',
            HOS: {
                back: this.props.history.length > 2,
                fixture: true,
                title: '',
                hideShadow: false,
                MLogo: false,
                statusBox: true,
                statusAll: '',
                isPrimary: DARK_THEME_ENABLE ? false : true,
                UserData: true
            },
            limit: 20,
            offset: 0,
            hasMore: false,
            isListLoading: false,
            touList: [],
            sideView: false,
            fieldViewRightData: [],
            rootitem: [],
            lineupArr:[],
            userTeamInfo: {}
        }
    }

    UNSAFE_componentWillMount() {
        if(Utilities.getMasterData().a_dfst == 1){
            ls.set('isDfsTourEnable',true)
        }
        if(this.props){
            let propsState = this.props && this.props.location && this.props.location.state;
            const {item,TourData,status} = propsState;
            this.setState({
                TourData: TourData,
                userPreData: item,
                HOS: {
                    back: this.props.history.length > 2,
                    fixture: true,
                    title: '',
                    hideShadow: false,
                    MLogo: false,
                    statusBox: true,
                    statusAll: status,
                    isPrimary: DARK_THEME_ENABLE ? false : true,
                    UserData: true
                },
            })
        }
    }

    componentDidMount() {
        globalThis = this;
        this.getUserHistory()
    }
    
    getUserHistory=()=>{        
        let param = {
            "tournament_id": this.state.TourData.tournament_id,
            "user_id": this.state.userPreData.user_id
        }
        this.setState({ isListLoading: true })
        getDFSTourUserHistory(param).then((responseJson) => {
            this.setState({ isListLoading: false })
            setTimeout(() => {
                this.setState({
                    isRefresh: false
                })
            }, 2000);
            if (responseJson.response_code === WSC.successCode) {
                let data = responseJson.data
                this.setState({
                    touList: data.history,
                    userData: data.user_info
                });
            }
        })
    }

    renderShimmer = (idx) => {
        return (
            <SkeletonTheme key={idx} color={DARK_THEME_ENABLE ? "#030409" : null} highlightColor={DARK_THEME_ENABLE ? "#0E2739" : null}>  
                <div key={idx} className="list-item shimmer-list-item">
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

    renderItem = (item,idx) => {

        let sDate = new Date(Utilities.getUtcToLocal(item.season_scheduled_date))
        let game_starts_in = Date.parse(sDate)
        item['game_starts_in'] = game_starts_in;



        return (
            <div className="display-table" key={item.user_id + idx} id={item.user_id + idx} 
                onClick={
                    (Utilities.minuteDiffValue({ date: item.game_starts_in }) < 0) ? 
                        (() => this.state.userPreData.user_id == ls.get('profile').user_id && globalThis.showFieldView(item)) 
                        : 
                        (() => globalThis.showFieldView(item))
                    }>
                
                <div className="dtr dtb cursor-pointer">
                    <div className="dtc part-name">
                        <span className="nm">
                            {item.home} vs {item.away}
                        </span>
                        <span className="date">
                            <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM " }} />
                        </span>
                    </div>
                    <div className="dtc part-est-win">
                        {item.score}
                    </div>
                </div>
            </div>
        )
    }

    showFieldView=(item)=>{
        let sideView = true;
        if(window.ReactNativeWebView || isMobile ){
            sideView = false
        }
        this.openLineup(item,this.state.TourData,this.state.TourData,sideView)
    }    

    sideViewHide = () => {
        this.setState({
            sideView: false,
        })
    }

    openLineup=(userInfo,rootitem, isFromtab,sideView)=>{
        userInfo.user_name = this.state.userPreData.user_name
        this.setState({
            sideView: sideView,
            fieldViewRightData: rootitem,
            rootitem: rootitem,
            userTeamInfo: userInfo
        })
        let dateformaturl = Utilities.getUtcToLocal(userInfo.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
        if (sideView == false) {
            let fieldViewPath = '/tournament/field-view/' + userInfo.home + "-vs-" + userInfo.away + "-" + dateformaturl
            this.props.history.push({ pathname: fieldViewPath.toLowerCase(), state: { contestItem: rootitem, rootitem: rootitem, isEdit: false, from: 'MyContest', isFromtab: 11, isFromMyTeams: true, resetIndex: 1 ,isFromLeaderboard: true,userTeamInfo: userInfo,isFrom:'rank-view'} });
        }
    }


    render() {
        const { userData,HOS ,touList,isListLoading} = this.state;
        let status = this.props && this.props.location && this.props.location.state && this.props.location.state.status ? this.props.location.state.status : 1

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container prediction-detail-wrap pickem-participants-wrap pickem-tour-user-history">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.DFSTUH.title}</title>
                            <meta name="description" content={MetaData.DFSTUH.description} />
                            <meta name="keywords" content={MetaData.DFSTUH.keywords}></meta>
                        </Helmet>
                        <CustomHeader 
                            {...this.props} 
                            LobyyData={userData} 
                            HeaderOption={HOS}  
                        />
                        <div className="participants-list">
                            <div className="header-table">
                                <div className="dtr dth">
                                    <div className="dtc part-name">{AL.MATCHES}</div>
                                    <div className="dtc part-pick">{AL.POINTS}</div>
                                </div>
                            </div>
                            <div className={"all-part-table" + (status == 2 ? ' all-part-table-cp' : '')}>
                                {
                                    touList.length > 0 && !isListLoading && 
                                    <>
                                    {
                                        touList.map((item, idx) => {
                                            return this.renderItem(item, false, idx);
                                        })
                                    }
                                    <div className="total-fixed-btn">
                                        <div className="tot-pts">{AL.TOTAL_POINTS} </div>
                                        <div className="tot-scr">{userData.total_score || 0}</div>
                                    </div>
                                    </>
                                }
                                {
                                    touList.length === 0 && !isListLoading &&
                                    <NoDataView
                                        BG_IMAGE={Images.no_data_bg_image}
                                        // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                        CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                        MESSAGE_1={AL.MORE_COMING_SOON}
                                    />
                                }
                                {
                                    touList.length === 0 && isListLoading &&
                                    _times(16, (idx) => {
                                        return this.renderShimmer(idx)
                                    })
                                }
                            </div>
                        </div>
                        {this.state.sideView &&
                            <DFSTourFieldViewRight
                                SelectedLineup={this.state.lineupArr.length ? this.state.lineupArr : []}
                                isFrom={'rank-view'}
                                isFromUpcoming={true}
                                team={this.state.team}
                                team_name={this.state.teamName}
                                resetIndex={1}
                                TeamMyContestData={this.state.fieldViewRightData}
                                isFromMyTeams={true}
                                rootitem={this.state.rootitem}
                                sideViewHide={this.sideViewHide}
                                isFromLeaderboard={true}
                                userTeamInfo={this.state.userTeamInfo}
                            />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}

export default DFSUserTourHistory;
