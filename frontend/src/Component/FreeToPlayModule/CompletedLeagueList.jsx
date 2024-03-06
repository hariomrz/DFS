import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import Helmet from 'react-helmet';
import MetaData from '../../helper/MetaData';
import CustomHeader from '../../components/CustomHeader';
import * as AL from "../../helper/AppLabels";
import * as Constants from "../../helper/Constants";
import {getMiniLeagueByStatus } from "../../WSHelper/WSCallings";
import { NoDataView, MomentDateComponent } from '../CustomComponent';
import Images from '../../components/images';
import * as AppLabels from "../../helper/AppLabels";
import LeagueDetails from './LeagueDetails';
import { Utilities } from '../../Utilities/Utilities';


class CompletedLeagueList extends Component {
    constructor(props) {
        super(props)
        this.state = {
            leagueList: [],
            LobyyData: '',
            MiniLeagueList: [],
            showContestDetail: false,
             HeaderOption: {
                back: true,
                title: AL.COMPLETED_LEAGUE,
                hideShadow: false
            }
        }
    }

    getMiniLeagueDetails = (item,LobyyData) => {
        this.props.history.push({
            pathname: '/league-details',
            state: { LobyyData: LobyyData, MiniLeagueData:item }
        })

    }
    ContestDetailShow = (item) => {
          this.setState({
            showContestDetail: true,
            MiniLeagueData:item,
        });
    }
    /**
     * @description method to hide contest detail model
     */
    ContestDetailHide = () => {
        this.setState({
            showContestDetail: false,
        });
    }
    openLeaderBoardLeague=(item)=>{

        this.props.history.push({ pathname: '/mini-league-leader-board', state: { 
            LobyyData:this.state.LobyyData,
            MiniLeagueSponser:item,
            MiniLeagueListItem:item
        } }) 
    }



    getMiniLeagueByStatus = async () => {
        if (Constants.AppSelectedSport == null)
            return;

        let param = {
            "sports_id": Constants.AppSelectedSport,
            "status": "completed",
            "page_no": "1",
            "page_size": "500"
        }

        delete param.limit;
        var api_response_data = await getMiniLeagueByStatus(param);
        if (api_response_data) {
            this.setState({ MiniLeagueList: api_response_data })
        }
    }

    componentDidMount() {
        this.getMiniLeagueByStatus();
        Utilities.setScreenName('SHS')
    }

    render() {

        const HeaderOption = {
            back: true,
            title: AL.COMPLETED_LEAGUE,
            share: true,
            isPrimary: Constants.DARK_THEME_ENABLE ? false : true
        }
        const { LobyyData, MiniLeagueList,showContestDetail } = this.state;

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

                    {
                        MiniLeagueList.data && MiniLeagueList.data.map((item, index) => {
                            return (
                                <div className="league-list-all">

                                <div className="sort-contest-wrapper mt15">
                                <div className="league-section no-border" onClick={() => this.openLeaderBoardLeague(item)}>
                                        <div className="league-name">
                                            {item.mini_league_name}

                                        </div>
                                        <p> <MomentDateComponent data={{date:item.scheduled_date,format:"D MMM"}}/> - <MomentDateComponent data={{date:item.end_date,format:"D MMM"}}/> </p>
                                    </div>

                                </div>
                                </div>
                            );


                            
                        })
                    }

                {
                    
                        MiniLeagueList.data && MiniLeagueList.data.length == 0 &&
                                                        <NoDataView
                                                            BG_IMAGE={Images.no_data_bg_image}
                                                            // CENTER_IMAGE={Images.BRAND_LOGO_FULL}
                                                            CENTER_IMAGE={Images.NO_DATA_VIEW}
                                                            MESSAGE_1={AppLabels.NO_FIXTURES_MSG1}
                                                            MESSAGE_2={AppLabels.NO_DATA_VIEW_MESSAGE_COMPLETED_LEAGUE}
                                                            onClick_2={this.joinContest}
                                                        />
                                                    
                }
                
                {
                            showContestDetail &&
                            <LeagueDetails
                               {...this.props}
                                IsContestDetailShow={showContestDetail}
                                IsContestDetailHide={this.ContestDetailHide}
                                LobyyData={this.state.LobyyData}
                                MiniLeagueData={this.state.MiniLeagueData} />
                        }


                </div>
            </MyContext.Provider>
        )
    }
}

export default CompletedLeagueList;