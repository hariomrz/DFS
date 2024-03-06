import React from 'react';
import { MomentDateComponent } from '../CustomComponent';
import { Utilities, _Map } from '../../Utilities/Utilities';
import { AppSelectedSport, MATCH_TYPE } from '../../helper/Constants';
import { getMyPrediction } from '../../WSHelper/WSCallings';
import { SportsIDs } from "../../JsonFiles";
import Skeleton,{SkeletonTheme} from 'react-loading-skeleton';
import PredictionCard from './PredictionCard';
import * as Constants from "../../helper/Constants";
import * as WSC from "../../WSHelper/WSConstants";

export default class CompletedPredictions extends React.Component {

    constructor(props) {
        super(props)
        this.state = {
            ccList: [],
            loadingIndex: -1,
            expandedItem: ''
        };
    };

    /**
     * @description This function is responsible to get Live Contests response
     * @param status selected tab (Live, Upcoming, Completed)
     */
    getMyContestList(item, idx) {
        const {expandedItem} =  this.state;
        // if (item.isExpanded) {
        //     let ccList = this.state.ccList;
        //     item['isExpanded'] = false;
        //     ccList[idx] = item;
        //     this.setState({ ccList })
        // } 

        if (item.season_game_uid == expandedItem ) {
            this.setState({ expandedItem  : ''})
        } 
        else {
            if (item.contest && item.contest.length > 0) {
                let ccList = this.state.ccList;
                // item['isExpanded'] = true;
                ccList[idx] = item;
                this.setState({ 
                    ccList,
                    expandedItem: item.season_game_uid
                })
            } else {
                var param = {
                    "season_game_uid": item.season_game_uid,
                    "status": Constants.CONTEST_COMPLETED
                }
                this.setState({ loadingIndex: idx })
                getMyPrediction(param).then((responseJson) => {
                    this.setState({ loadingIndex: -1 })

                    if (responseJson && responseJson.response_code == WSC.successCode) {
                        let ccList = this.state.ccList;
                        item['contest'] = responseJson.data.predictions || [];
                        // item['isExpanded'] = true;
                        ccList[idx] = item;
                        this.setState({ 
                            ccList,
                            expandedItem: item.season_game_uid
                        })
                    }
                })
            }
        }
    }

    UNSAFE_componentWillReceiveProps(nextProps) {
        if (this.state.ccList !== nextProps.ccList) {
            let fItem = nextProps.ccList && nextProps.ccList.length > 0 && nextProps.ccList[0];
            this.setState({ ccList: nextProps.ccList ,expandedItem: '' })
            if(fItem && this.state.expandedItem == '' ){
                this.getMyContestList(fItem,0)
            }
            if(fItem && this.state.expandedItem == fItem.season_game_uid ){
                var param = {
                    "season_game_uid": fItem.season_game_uid,
                    "status": Constants.CONTEST_COMPLETED
                }
                this.setState({ loadingIndex: 0 })
                getMyPrediction(param).then((responseJson) => {
                    this.setState({ loadingIndex: -1 })

                    if (responseJson && responseJson.response_code == WSC.successCode) {
                        let ccList = this.state.ccList;
                        fItem['contest'] = responseJson.data.predictions || [];
                        // item['isExpanded'] = true;
                        ccList[0] = fItem;
                        this.setState({ 
                            ccList,
                            expandedItem: fItem.season_game_uid
                        })
                    }
                })
            }
        }
    }

    render() {
        const {expandedItem} = this.state;
        return (
            <div>
                {
                    this.state.ccList.length > 0 &&

                    _Map(this.state.ccList, (item, idx) => {

                        return (
                            <div key={idx} className={"prediction-wrap-v contest-card completed-contest-card-new" + (expandedItem == item.season_game_uid? ' pb0' : '')}>
                                <div onClick={() => this.getMyContestList(item, idx)} className={"contest-card-header pointer-cursor" + (expandedItem == item.season_game_uid? ' pb15' : '')}>
                                    <ul>
                                        <li className="team-left-side">

                                            <div className="team-content-img">
                                                <img src={Utilities.teamFlagURL(item.home_flag)} alt="" />
                                            </div>
                                            <div className="contest-details-action">

                                                <div className="contest-details-first-div">{item.home}</div>
                                                {
                                                    AppSelectedSport == SportsIDs.cricket ?
                                                        item.score_data && item.score_data[1] ?
                                                        <div className="contest-details-sec-div">
                                                        {item.score_data[1].home_team_score}-{(item.score_data[1].home_wickets) ? item.score_data[1].home_wickets : 0}
                                                        <span className="gray-color-class"> {(item.score_data[1].home_overs) ? item.score_data[1].home_overs : 0} {item.score_data[2] ? ' & ' : ''} </span>
                                                        {
                                                            item.score_data[2] && <div className="contest-details-sec-div second-inning">
                                                            {item.score_data[2].home_team_score}-{(item.score_data[2].home_wickets) ? item.score_data[2].home_wickets : 0}
                                                            <span className="gray-color-class"> {(item.score_data[2].home_overs) ? item.score_data[2].home_overs : 0} </span>
                                                            </div>
                                                        }
                                                        </div>
                                                            :
                                                            <div className="contest-details-sec-div">{0}-{0}<span className="gray-color-class"> 0 </span></div>
                                                        :
                                                        (item.score_data) ?
                                                            <div className="contest-details-sec-div">{item.score_data.home_score}</div>
                                                            :
                                                            <div className="contest-details-sec-div">0</div>
                                                }
                                            </div>
                                        </li>
                                        <li className="progress-middle">

                                            <div className="progress-middle-div">
                                                <div className="team-content">
                                                    <p>
                                                        {item.league_name}
                                                        {
                                                            AppSelectedSport === '7' &&
                                                            <React.Fragment>- {MATCH_TYPE[item.format]}</React.Fragment>
                                                        }
                                                    </p>
                                                    <span className="time-line primary-color"> <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM - hh:mm A" }} /> </span>

                                                </div>
                                            </div>
                                        </li>
                                        <li className="team-right-side">

                                            <div className="contest-details-action">
                                                <div className="contest-details-first-div">{item.away}</div>
                                                {
                                                    AppSelectedSport == SportsIDs.cricket ?
                                                        item.score_data && item.score_data[1] ?
                                                        <div className="contest-details-sec-div">
                                                        {item.score_data[1].away_team_score}-{(item.score_data[1].away_wickets) ? item.score_data[1].away_wickets : 0}
                                                        <span className="gray-color-class"> {(item.score_data[1].away_overs) ? item.score_data[1].away_overs : 0} {item.score_data[2] ? ' & ' : ''} </span>
                                                        {
                                                            item.score_data[2] && <div className="contest-details-sec-div second-inning">
                                                            {item.score_data[2].away_team_score}-{(item.score_data[2].away_wickets) ? item.score_data[2].away_wickets : 0}
                                                            <span className="gray-color-class"> {(item.score_data[2].away_overs) ? item.score_data[2].away_overs : 0} </span>
                                                            </div>
                                                        }
                                                        </div>
                                                            :
                                                            <div className="contest-details-sec-div">{0}-{0}<span className="gray-color-class"> 0 </span></div>
                                                        :
                                                        (item.score_data) ?
                                                            <div className="contest-details-sec-div">{item.score_data.away_score}</div>
                                                            :
                                                            <div className="contest-details-sec-div">0</div>
                                                }

                                            </div>
                                            <div className="team-content-img">
                                                <img src={Utilities.teamFlagURL(item.away_flag)} alt="" />
                                            </div>
                                        </li>
                                    </ul>
                                </div>


                                {
                                    expandedItem == item.season_game_uid && <ul className="list-pred my-pre new-list-pred">
                                        {item.contest.map((childItem, indx) => {
                                            return <PredictionCard
                                                {...this.props}
                                                key={indx}
                                                data={{
                                                    itemIndex: indx,
                                                    item: childItem,
                                                    status: Constants.CONTEST_COMPLETED
                                                }} />;
                                        })}
                                    </ul>
                                }
                                {

                                    (this.state.loadingIndex === idx) && 
                                    <SkeletonTheme color={Constants.DARK_THEME_ENABLE ? "#030409" : null} highlightColor={Constants.DARK_THEME_ENABLE ? "#0E2739" : null}>
                                        <div className="contest-list m border shadow-none">
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
                                                    <div className="progress-bar-default w-100">
                                                        <Skeleton height={6} />
                                                        <div className="d-flex justify-content-between">
                                                            <Skeleton height={4} width={60} />
                                                            <Skeleton height={4} width={60} />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </SkeletonTheme>
                                }
                            </div>
                        )
                    })
                }
            </div>
        )
    }
}