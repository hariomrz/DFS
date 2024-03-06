import React from 'react';
import * as AL from "../../helper/AppLabels";
import { _times,Utilities ,_Map} from '../../Utilities/Utilities';
import { AppSelectedSport, DARK_THEME_ENABLE, CONTEST_UPCOMING } from '../../helper/Constants';
import CountdownTimer from '../../views/CountDownTimer';
import { MomentDateComponent } from '../CustomComponent';
import { Sports } from '../../JsonFiles';


export default class DFSTourFixtureCard extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            status: 0,
            limit: 20,
            offset: 0,
            hasMore: false,
            isLoading: false,
            TourList:[],
            MerchandiseList:[]
        }
    }

    render() {
        const { status,isLoading, hasMore,TourList,MerchandiseList } = this.state;
        const {data,isFrom} = this.props;
        const HeaderOption = {
            back: true,
            title: '',
            hideShadow: true,
            isPrimary: DARK_THEME_ENABLE ? false : true
        }
        let scoredata = data.score_data && data.score_data.length > 0 ? data.score_data[0] : ''
        return (
            <li className={"tour-fixture-card-wrap" + (data.is_joined == 0 && isFrom == 'CTLobby' ? ' unjoined-comp' : '') + (data.is_joined == 0 && isFrom == 'LTLobby' ? ' not-joined' : '')} onClick={()=>(isFrom != 'UTLobby' && this.props.goToFixtureleaderboard(data))}>
                {/* + ( ? ' disabled' : '') */}
                <div className="tour-fixture-card-body">
                    {
                        data.playing_announce == "1" && isFrom == 'UTLobby' &&
                        <span className="match-delay-info">{AL.LINEUP_OUT}</span>
                    }
                    <div className="left-sec">
                        <img src={Utilities.teamFlagURL(data.home_flag)} alt=""/>
                        <div className={"team-abbr" + ((data.home.length == 5 || data.away.length == 5) ? ' dec-FS5' : (data.home.length == 6 || data.away.length == 6) ? ' dec-FS6' : (data.home.length == 7 || data.away.length == 7) ? ' dec-FS7' : '')}>{data.home}</div>
                        {
                            (isFrom == 'CTLobby' || isFrom == 'LTLobby') && scoredata != '' &&
                            <div className="team-stats">
                                {
                                    AppSelectedSport == Sports.cricket ?
                                    <>
                                        {scoredata.home_team_score}/{scoredata.home_wickets}
                                        <span>{scoredata.home_overs}</span>
                                    </>
                                    :
                                    <> {scoredata.home_score}</>
                                }
                            </div>
                        }
                    </div>
                    <div className="middle-sec">
                        {
                            isFrom == 'LTLobby' ?
                            <span className="in-progress">{AL.IN_PROGRESS}</span>
                            :
                            <>
                                {
                                    Utilities.showCountDown(data) ?
                                        <div className="countdown time-line">
                                            {data.game_starts_in && <CountdownTimer
                                                deadlineTimeStamp={data.game_starts_in}
                                                timerCallback={this.props.timerCallback}
                                            />}
                                        </div> :
                                        <>
                                        {
                                            isFrom == 'CTLobby' ?
                                            <>
                                                <div className="fixture-date"> 
                                                    <MomentDateComponent data={{ date: data.season_scheduled_date, format: "D MMM" }} />
                                                </div>
                                                <div className="fixture-time">
                                                    <MomentDateComponent data={{ date: data.season_scheduled_date, format: "hh:mm a" }} />
                                                </div>
                                            </>
                                            :
                                            <>
                                                <div className="fixture-date mb-0"> 
                                                    <MomentDateComponent data={{ date: data.season_scheduled_date, format: "D MMM" }} /> 
                                                    <span><MomentDateComponent data={{ date: data.season_scheduled_date, format: "hh:mm a" }} /></span>
                                                </div>
                                            </>
                                        }
                                        </>
                                }
                                {
                                    data.entries && data.entries > 0 && isFrom != 'CTLobby' &&
                                    <div className="entries">{data.entries} {data.entries == 1 ? AL.ENTRY : AL.ENTRIES}</div>
                                }
                            </>
                        }
                    </div>
                    <div className="right-sec">
                        <img src={Utilities.teamFlagURL(data.away_flag)} alt=""/>
                        <div className={"team-abbr" + ((data.home.length == 5 || data.away.length == 5) ? ' dec-FS5' : (data.home.length == 6 || data.away.length == 6) ? ' dec-FS6' : (data.home.length == 7 || data.away.length == 7) ? ' dec-FS7' : '')}>{data.away}</div>
                        {
                            (isFrom == 'CTLobby' || isFrom == 'LTLobby') && scoredata != '' &&
                                <div className="team-stats">
                                    {
                                        AppSelectedSport == Sports.cricket ?
                                        <>
                                            {scoredata.away_team_score}/{scoredata.away_wickets}
                                            <span>{scoredata.away_overs}</span>
                                        </>
                                        :
                                        <> {scoredata.away_score}</>
                                    }
                                </div>
                        }
                    </div>
                </div>
                <div className={"tour-fixture-card-footer " + (data.is_joined == 0 && isFrom == 'UTLobby' ? "not-joined" : "")}>
                    {
                        data.is_joined == 0 && isFrom == 'UTLobby' ?
                        <a href className="btn btn-rounded enter-team" onClick={(e)=>this.props.joinFixture(e,data)}>{AL.ENTER_YOUR_TEAM}</a>
                        :
                        <>
                            {
                                data.is_joined == 1 &&
                                <div className="team-info" onClick={(e)=>e.stopPropagation}>
                                    <div className="team-nm">{data.team_name || 'Team 1'}</div>
                                    {
                                        isFrom== 'LTLobby' || isFrom == 'CTLobby' ?
                                            <div className="other-info">
                                                <span>{data.match_rank}</span>
                                                <span className="text-span">{AL.RANK}</span>
                                                <span>{data.team_score || 0}</span>                                       
                                                <span className="text-span">{AL.POINTS}</span>  
                                            </div>
                                            :
                                            <div className="other-info cursor-pointer">
                                                <a href 
                                                    className="visible-for-mobile"
                                                    onClick={(e)=>this.props.openLineup(e,data, data, data, false, CONTEST_UPCOMING, false)}
                                                >
                                                    <span><i className="icon-ground"></i></span>
                                                    {AL.VIEW}
                                                </a>
                                                <a href 
                                                    className="visible-for-desktop"
                                                    onClick={(e)=>this.props.openLineup(e,data, data, data, false, CONTEST_UPCOMING, true)}
                                                    >
                                                   <span><i className="icon-ground"></i></span>
                                                    {AL.VIEW}
                                                </a>
                                                <a href 
                                                    onClick={(e)=>this.props.openLineup(e,data, data, data, true, CONTEST_UPCOMING)}
                                                >
                                                    <span><i className="icon-edit-line"></i></span>
                                                    {AL.EDIT}
                                                </a>
                                            </div>
                                    }
                                </div>
                            }   
                        </>                     
                    }
                </div>
            </li>
        )
    }
}