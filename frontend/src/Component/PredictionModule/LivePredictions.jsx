import React from 'react';
import { SportsIDs } from "../../JsonFiles";
import { _Map, Utilities } from '../../Utilities/Utilities';
import { AppSelectedSport, CONTEST_LIVE, MATCH_TYPE, CONTEST_UPCOMING } from '../../helper/Constants';
import { getMyPrediction } from '../../WSHelper/WSCallings';
import Skeleton,{SkeletonTheme} from 'react-loading-skeleton';
import PredictionCard from './PredictionCard';
import * as AppLabels from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";
import * as Constants from "../../helper/Constants";


export default class LivePredictions extends React.Component {

    constructor(props) {
        super(props)
        this.state = {
            lcList: [],
            loadingIndex: -1,
            expandedItem: ''
        };
    }    

    /**
     * @description This function is responsible to get Live Contests response
     * @param status selected tab (Live, Upcoming, Completed)
     */
    getMyContestList(item, idx) {
        const {expandedItem} =  this.state;
        // if (item.isExpanded) {
        //     let lcList = this.state.lcList;
        //     item['isExpanded'] = false;
        //     lcList[idx] = item;
        //     this.setState({ lcList })
        // } 
        if (item.season_game_uid == expandedItem ) {
            this.setState({ expandedItem  : ''})
        } 
        else {
            if (item.contest && item.contest.length > 0) {
                let lcList = this.state.lcList;
                // item['isExpanded'] = true;
                lcList[idx] = item;
                this.setState({ 
                    lcList,
                    expandedItem: item.season_game_uid
                })
            } else {
                var param = {
                    "season_game_uid": item.season_game_uid,
                    "status": CONTEST_LIVE
                }
                this.setState({ loadingIndex: idx })
                getMyPrediction(param).then((responseJson) => {
                    this.setState({ loadingIndex: -1 })

                    if (responseJson && responseJson.response_code == WSC.successCode) {
                        let lcList = this.state.lcList;
                        item['contest'] = responseJson.data.predictions || [];
                        // item['isExpanded'] = true;
                        lcList[idx] = item;
                        this.setState({ 
                            lcList,
                            expandedItem: item.season_game_uid
                        })
                    }
                })
            }
        }
    }

    UNSAFE_componentWillReceiveProps(nextProps) {
        if (this.state.lcList !== nextProps.lcList) {
            let fItem = nextProps.lcList && nextProps.lcList.length > 0 && nextProps.lcList[0];
            this.setState({ lcList: nextProps.lcList ,expandedItem: ''})
            if(fItem && this.state.expandedItem == '' ){
                this.getMyContestList(fItem,0)
            }
            if(fItem && this.state.expandedItem == fItem.season_game_uid ){
                var param = {
                    "season_game_uid": fItem.season_game_uid,
                    "status": CONTEST_LIVE
                }
                this.setState({ loadingIndex: 0 })
                getMyPrediction(param).then((responseJson) => {
                    this.setState({ loadingIndex: -1 })

                    if (responseJson && responseJson.response_code == WSC.successCode) {
                        let lcList = this.state.lcList;
                        fItem['contest'] = responseJson.data.predictions || [];
                        // item['isExpanded'] = true;
                        lcList[0] = fItem;
                        this.setState({ 
                            lcList,
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
                    this.state.lcList.length > 0 &&
                    _Map(this.state.lcList, (item, idx) => {
                        return (
                            <div key={idx} className={"prediction-wrap-v contest-card live-contest-card live-contest-card-new prediction-live-view" + (expandedItem == item.season_game_uid ? ' pb-1' : '')}>
                                <div onClick={() => this.getMyContestList(item, idx)} className={"contest-card-header pointer-cursor" + (expandedItem == item.season_game_uid ? ' pb15' : '')}>
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
                                                <p>
                                                    {item.league_name}
                                                    {
                                                        AppSelectedSport === '7' &&
                                                        <React.Fragment>- {MATCH_TYPE[item.format]}</React.Fragment>
                                                    }
                                                </p>
                                                <span className="progress-span">
                                                    {AppLabels.IN_PROGRESS}
                                                </span>
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
                                    expandedItem == item.season_game_uid && 
                                    <ul className="list-pred my-pre new-list-pred">
                                        {item && item.contest && item.contest.map((childItem, indx) => {
                                            return <PredictionCard
                                                {...this.props}
                                                key={indx}
                                                data={{
                                                    itemIndex: indx,
                                                    item: childItem,
                                                    status: CONTEST_LIVE
                                                }} />;
                                        })}
                                    </ul>
                                }
                                {

                                    (this.state.loadingIndex === idx) && 
                                    <SkeletonTheme color={Constants.DARK_THEME_ENABLE ? "#030409" : null} highlightColor={Constants.DARK_THEME_ENABLE ? "#0E2739" : null}>
                                        <div className="contest-list m border shadow-none border-0">
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
            </div >
        )
    }

}