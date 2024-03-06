import React from 'react';
import { MomentDateComponent } from '../CustomComponent';
import { Utilities, _Map } from '../../Utilities/Utilities';
import { getMyPrediction } from '../../WSHelper/WSCallings';
import Skeleton,{SkeletonTheme} from 'react-loading-skeleton';
import PredictionCard from './PredictionCard';
import CountdownTimer from '../../views/CountDownTimer';
import * as Constants from "../../helper/Constants";
import * as WSC from "../../WSHelper/WSConstants";

export default class UpcomingPredictions extends React.Component {

    constructor(props) {
        super(props)
        this.state = {
            ucList: [],
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
        //     let ucList = this.state.ucList;
        //     item['isExpanded'] = false;
        //     ucList[idx] = item;
        //     this.setState({ ucList })
        // } 
        if (item.season_game_uid == expandedItem ) {
            this.setState({ expandedItem  : ''})
        } 
        else {
            if (item.contest && item.contest.length > 0) {
                let ucList = this.state.ucList;
                // item['isExpanded'] = true;
                ucList[idx] = item;
                this.setState({ 
                    ucList,
                    expandedItem: item.season_game_uid 
                })
            } else {
                var param = {
                    "season_game_uid": item.season_game_uid,
                    "status": Constants.CONTEST_UPCOMING
                }
                this.setState({ loadingIndex: idx })
                getMyPrediction(param).then((responseJson) => {
                    this.setState({ loadingIndex: -1 })

                    if (responseJson && responseJson.response_code == WSC.successCode) {
                        let ucList = this.state.ucList;
                        item['contest'] = responseJson.data.predictions || [];
                        // item['isExpanded'] = true;
                        ucList[idx] = item;
                        this.setState({ 
                            ucList,
                            expandedItem: item.season_game_uid 
                        })
                    }
                })
            }
        }
    }

    UNSAFE_componentWillReceiveProps(nextProps) {
        if (this.state.ucList !== nextProps.ucList) {
            let fItem = nextProps.ucList && nextProps.ucList.length > 0 && nextProps.ucList[0];
            this.setState({ ucList: nextProps.ucList ,expandedItem: '' })
            if(fItem && this.state.expandedItem == '' ){
                this.getMyContestList(fItem,0)
            }
            if(fItem && this.state.expandedItem == fItem.season_game_uid ){
                var param = {
                    "season_game_uid": fItem.season_game_uid,
                    "status": Constants.CONTEST_UPCOMING
                }
                this.setState({ loadingIndex: 0 })
                getMyPrediction(param).then((responseJson) => {
                    this.setState({ loadingIndex: -1 })

                    if (responseJson && responseJson.response_code == WSC.successCode) {
                        let ucList = this.state.ucList;
                        fItem['contest'] = responseJson.data.predictions || [];
                        // item['isExpanded'] = true;
                        ucList[0] = fItem;
                        this.setState({ 
                            ucList,
                            expandedItem: fItem.season_game_uid 
                        })
                    }
                })
            }
        }
    }


    render() {
        let { removeFromList } = this.props;
        const { expandedItem } = this.state;

        return (
            <div>
                {
                    this.state.ucList.length > 0 &&
                    _Map(this.state.ucList, (item, idx) => {
                        return (
                            <div key={idx} className={"prediction-wrap-v contest-card upcoming-contest-card-new" + (item.isExpanded ? ' pb0' : '')}>
                                <div onClick={() => this.getMyContestList(item, idx)} className={"contest-card-header pointer-cursor" + (expandedItem == item.season_game_uid ? ' pb15' : '')}>
                                {/* + (item.isExpanded ? ' pb15' : '')}> */}
                                    <ul>
                                        <li className="team-left-side">
                                            <div className="team-content-img">
                                                <img src={Utilities.teamFlagURL(item.home_flag)} alt="" />
                                            </div>
                                            <div className="contest-details-action">
                                                <div className="contest-details-first-div">{item.home}</div>
                                            </div>
                                        </li>
                                        <li className="progress-middle">
                                            <div className="team-content">
                                                <p>
                                                    {item.league_name}
                                                    {Constants.AppSelectedSport === '7' &&
                                                        <React.Fragment>- {Constants.MATCH_TYPE[item.format]}</React.Fragment>
                                                    }
                                                </p>
                                                {
                                                    Utilities.showCountDown(item) ?
                                                        <span>
                                                            {item.game_starts_in && <CountdownTimer timerCallback={() => removeFromList(Constants.CONTEST_UPCOMING, idx)} deadlineTimeStamp={item.game_starts_in} />}
                                                        </span>
                                                        :
                                                        <span className="time-line-date"> <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM - hh:mm A" }} /> </span>
                                                }

                                            </div>
                                        </li>
                                        <li className="team-right-side">

                                            <div className="contest-details-action">
                                                <div className="contest-details-first-div">{item.away}</div>
                                            </div>
                                            <div className="team-content-img">
                                                <img src={Utilities.teamFlagURL(item.away_flag)} alt="" />
                                            </div>

                                        </li>
                                    </ul>
                                </div>
                                {
                                    // item.isExpanded && 
                                    expandedItem == item.season_game_uid &&
                                    <ul className="list-pred my-pre new-list-pred">
                                        {item.contest.map((childItem, indx) => {
                                            return <PredictionCard
                                                {...this.props}
                                                key={indx}
                                                data={{
                                                    itemIndex: indx,
                                                    item: childItem,
                                                    status: Constants.CONTEST_UPCOMING
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
            </div>
        )
    }
}