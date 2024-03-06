import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { _Map, _isUndefined, Utilities } from '../../Utilities/Utilities';
import Helmet from 'react-helmet';
import MetaData from '../../helper/MetaData';
import CustomHeader from '../../components/CustomHeader';
import WSManager from '../../WSHelper/WSManager';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import { getUserMiniLeagueLeaderBoardMatches } from "../../WSHelper/WSCallings";
import * as Constants from "../../helper/Constants";
import { NoDataView } from '../CustomComponent';
import * as AppLabels from "../../helper/AppLabels";
import { MomentDateComponent } from '../CustomComponent';

const ListHeader = ({ context }) => {
    return (
        <div className="ranking-list-user user-list-header mini-leage-leaderbord" style={context.state.userData ? { marginTop: 0 } : {}}>
            <div className="display-table-cell text-center">
                <div className="list-header-text list-heder-mini-league">{AppLabels.MATCHES}</div>
            </div>
            
            <div className="display-table-cell">
                <div className="list-header-text list-heder-mini-league text-right mr10">{AppLabels.POINTS}</div>
            </div>
        </div>
    )
}
class UserLeaguePoints extends Component {
    constructor(props) {
        super(props)
        this.state = {
            LobyyData: !_isUndefined(props.location.state) ? props.location.state.LobyyData : [],
            userData: !_isUndefined(props.location.state) ? props.location.state.userData : '',
            status: !_isUndefined(props.location.state) ? props.location.state.status : '',
            isYou:!_isUndefined(props.location.state) ? props.location.state.isYou : false,
            UserLeaguePointsList: [],
            MiniLeagueData: '',
            HeaderOption: {
                back: true,
                title: AL.F2P_LEAGUES,
                hideShadow: false
            }
        }
    }

    UNSAFE_componentWillMount = () => {
        this.getMiniLeagueByStatusApi();
        Utilities.setScreenName('SHS')
    }
    getMiniLeagueDetails = (item, LobyyData) => {
        this.props.history.push({
            pathname: '/league-details',
            state: { LobyyData: LobyyData, MiniLeagueData: item }
        })

    }
    

    getMiniLeagueByStatusApi = async () => {
        if (Constants.AppSelectedSport == null)
            return;

        let param = {
            "sports_id": Constants.AppSelectedSport,
            "mini_league_leaderboard_id": this.state.userData.mini_league_leaderboard_id,

        }
        delete param.limit;
        var api_response_data = await getUserMiniLeagueLeaderBoardMatches(param);
        if (api_response_data) {
            this.setState({ UserLeaguePointsList: api_response_data.data })
        }




    }
    render() {
        let userRank =  AppLabels.RANK + "#" + "   "+ this.state.userData.game_rank

        const HeaderOption = {
            back: true,
            screentitle:this.state.isYou ? AppLabels.You : this.state.userData.user_name,
            rank:userRank,
            statusLeaderBoard:this.state.status,
            share: true,
            isPrimary: Constants.DARK_THEME_ENABLE ? false : true
        }
        const { LobyyData, MiniLeagueList } = this.state;
        var totalScore = 0
        return (
            <MyContext.Provider >
                <div className="web-container Ftp-web-container ">
                    <Helmet titleTemplate={`${MetaData.template} | %s`}>
                        <title>{MetaData.SHS.title}</title>
                        <meta name="description" content={MetaData.SHS.description} />
                        <meta name="keywords" content={MetaData.SHS.keywords}></meta>
                    </Helmet>
                    <CustomHeader
                        {...this.props}
                        HeaderOption={HeaderOption}
                    />
                    
                    <ListHeader context={this} />
                    {

                       this.state.UserLeaguePointsList && this.state.UserLeaguePointsList.map((item, index) => {
                        totalScore = totalScore + parseFloat(item.total_score)
                            return (
                                <div className="league-list-all no-margin">
                                <div className="sort-contest-wrapper  p-t">
                                    <div className="league-section no-border-leageue-point">
                                        <div className="league-name leaderboard">
                                            {item.home}{" "+AppLabels.VS+" "}{item.away}

                                        </div>
                                        {
                                            <div className="pull-right total-points user-points-top-margin">
                                                {item.total_score}
                                            </div>
                                        }


                                        <p className="leaderboard"> 
                                        
                                        {<span> <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM" }}/></span>}
                                        
                                        </p>
                                    </div>

                                </div>
                                </div>
                            );
                        })
                    
                    }

                    {
                        this.state.UserLeaguePointsList && this.state.UserLeaguePointsList.length == 0 &&
                        <NoDataView
                            BG_IMAGE={Images.no_data_bg_image}
                            // CENTER_IMAGE={Images.BRAND_LOGO_FULL}
                            CENTER_IMAGE={Images.NO_DATA_VIEW}
                            MESSAGE_1={AppLabels.NO_FIXTURES_MSG1}
                            onClick_2={this.joinContest}
                        />

                    }
                    {
                        WSManager.loggedIn() &&
                        <div className="btn-block bottom bottom-view-leader-board">
                        <div className="pull-left">
                          {"Total Score"}
                         </div>
                         <div className="pull-right">
                          {totalScore}
                         </div>
                            

                        </div>
                    }



                </div>
            </MyContext.Provider>
        )
    }
}

export default UserLeaguePoints;