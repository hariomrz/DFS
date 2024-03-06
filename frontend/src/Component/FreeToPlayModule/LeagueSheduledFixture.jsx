import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { Utilities, _Map, _isUndefined } from '../../Utilities/Utilities';
import Helmet from 'react-helmet';
import MetaData from '../../helper/MetaData';
import CustomHeader from '../../components/CustomHeader';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import { getMiniLeagueDetails } from "../../WSHelper/WSCallings";
import * as Constants from "../../helper/Constants";
import { NoDataView, MomentDateComponent } from '../CustomComponent';
import * as AppLabels from "../../helper/AppLabels";

class LeagueSheduledFixture extends Component {
    constructor(props) {
        super(props)
        this.state = {
            MiniLeagueFixtureList: [],
            HeaderOption: {
                back: true,
                title: this.props.match.params.mini_league_name,
                hideShadow: false
            }
        }
    }

    UNSAFE_componentWillMount = () => {
        this.getMiniLeagueDetails();
        Utilities.setScreenName('SHS')
    }
    getMiniLeagueDetails = async (mini_league_uid) => {
        if (Constants.AppSelectedSport == null)
            return;

        let param = {
            "sports_id": Constants.AppSelectedSport,
            "mini_league_uid": this.props.match.params.mini_league_uid
        }

        delete param.limit;
        var api_response_data = await getMiniLeagueDetails(param);
        if (api_response_data) {
            this.setState({
                MiniLeagueFixtureList: api_response_data.data.match_list,
            })


        }
    }

    FixtureListFunction = (item) => {
        return (
            <div className="mini_league_sheduled-fixture">
                <div className="display-table">
                    <div className="display-table-cell text-center v-mid w20">
                        <img src={Utilities.teamFlagURL(item.home_flag)} alt="" className="team-img" />
                    </div>
                    <div className="display-table-cell text-center v-mid w-lobby-40">
                        <div className="team-block">
                            <span className="team-name text-uppercase">{item.home}</span>
                            <span className="verses">{AL.VS}</span>
                            <span className="team-name text-uppercase">{item.away}</span>
                        </div>
                        <div className="match-timing">
                            {

                                <span> <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM - hh:mm A " }} /></span>
                            }
                        </div>
                    </div>
                    <div className="display-table-cell text-center v-mid w20">
                        <img src={Utilities.teamFlagURL(item.away_flag)} alt="" className="team-img" />
                    </div>
                </div>
            </div>
        );
    }



    render() {

        const HeaderOption = {
            back: true,
            title: this.props.match.params.mini_league_name,
            share: true,
            isPrimary: Constants.DARK_THEME_ENABLE ? false : true
        }
        const { LobyyData, MiniLeagueFixtureList } = this.state;
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
                    {this.state.MiniLeagueFixtureList && this.state.MiniLeagueFixtureList.map((item, index) => {
                        return (
                            <React.Fragment key={index}>
                                <div className="collection-list-slider">
                                    {this.FixtureListFunction(item)}
                                </div>




                            </React.Fragment>
                        );
                    })

                    }

                    {
                        MiniLeagueFixtureList.data && MiniLeagueFixtureList.data.length == 0 &&
                        <NoDataView
                            BG_IMAGE={Images.no_data_bg_image}
                            CENTER_IMAGE={Images.NO_DATA_VIEW}
                            // CENTER_IMAGE={Images.BRAND_LOGO_FULL}
                            MESSAGE_1={AppLabels.NO_FIXTURES_MSG1}
                            MESSAGE_2={AppLabels.NO_DATA_VIEW_MESSAGE_ALL_LEAGUE}
                            onClick_2={this.joinContest}
                        />

                    }
                    




                </div>
            </MyContext.Provider>
        )
    }
}

export default LeagueSheduledFixture;