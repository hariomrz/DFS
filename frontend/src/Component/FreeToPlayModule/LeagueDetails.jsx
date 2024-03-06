import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { Row, Col } from 'react-bootstrap';
import { Utilities } from '../../Utilities/Utilities';
import Helmet from 'react-helmet';
import MetaData from '../../helper/MetaData';
import * as AL from "../../helper/AppLabels";
import * as Constants from "../../helper/Constants";
import { getMiniLeagueDetails } from "../../WSHelper/WSCallings";
import { Modal, Tabs, Tab } from 'react-bootstrap';
import Images from "../../components/images";
import { MomentDateComponent } from '../CustomComponent';
import { NoDataView } from '../CustomComponent';
import * as AppLabels from "../../helper/AppLabels";
import FtpPrizeComponent from './FtpPrizeComponent';

class LeagueDetails extends Component {
    constructor(props) {
        super(props)
        this.state = {
            prizeList: [],
            merchandiseList: [],
            MiniLeagueList: [],
            MiniLeagueSponser: '',
            prizeTabSelected: true,
            statusLeague: 0,
            HeaderOption: {
                back: false,
                title: "",
                hideShadow: false
            }
        }



    }


    getMiniLeagueDetails = async () => {
        if (Constants.AppSelectedSport == null)
            return;

        let param = {
            "sports_id": Constants.AppSelectedSport,
            "mini_league_uid": this.props.MiniLeagueData.mini_league_uid
        }

        delete param.limit;
        var api_response_data = await getMiniLeagueDetails(param);
        if (api_response_data) {
            this.setState({
                MiniLeagueList: api_response_data.data.match_list, HeaderOption: {
                    back: false,
                    title: api_response_data.data.mini_league_name,
                    hideShadow: false
                },
                prizeList: api_response_data.data.prize_distibution_detail && api_response_data.data.prize_distibution_detail != null ? api_response_data.data.prize_distibution_detail : [],
                merchandiseList: api_response_data.data.merchandise,
                MiniLeagueSponser: api_response_data.data,
                statusLeague: api_response_data.data.status == 0 && (api_response_data.data.game_starts_in * 1000 > Date.now()) ? 0 : api_response_data.data.status == 2 ? 2 : 1
            })


        }
    }

    UNSAFE_componentWillMount = () => {
        this.getMiniLeagueDetails();
        Utilities.setScreenName('SHS')
    }

    FixtureListFunction = (item) => {
        return (
            <div className="league-list">
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

    ontabSelect = (tab) => {
        if (tab == 1) {
            this.isPrizeTabSelected(true)
        }
        else {
            this.isPrizeTabSelected(false)


        }
    }

    isPrizeTabSelected = (isSelected) => {
        this.setState({ prizeTabSelected: isSelected });
    }
    setCurrentMaxPrize = (minMaxValue, prizeItem) => {
        var maxMini = prizeItem.max - prizeItem.min + 1;
        var finalPrize = (minMaxValue / maxMini)
        return finalPrize;
    }

    render() {

        const { MiniLeagueSponser } = this.state;
        const { IsContestDetailHide, LobyyData, MiniLeagueData } = this.props;

        const HeaderOption = {
            back: true,
            title: MiniLeagueData.mini_league_name,
            share: true
        }
        let sponserImage = MiniLeagueSponser.sponsor_logo && MiniLeagueSponser.sponsor_logo != null ? MiniLeagueSponser.sponsor_logo : 0

        return (
            <MyContext.Provider >
                <div className="web-container Ftp-web-container test free-to-play-info mt20">
                    <Helmet titleTemplate={`${MetaData.template} | %s`}>
                        <title>{MetaData.SHS.title}</title>
                        <meta name="description" content={MetaData.SHS.description} />
                        <meta name="keywords" content={MetaData.SHS.keywords}></meta>
                    </Helmet>

                    <Modal show="true" onHide={IsContestDetailHide} bsSize="large" dialogClassName={"contest-detail-modal contest-details-modal-white-lebel "}>
                        <Modal.Header  >
                            <Modal.Title >
                                <a onClick={IsContestDetailHide} className="modal-close">
                                    <i className="icon-close"></i>
                                </a>
                                <div className="match-heading header-content">

                                    <div className="team-header-detail">
                                        {
                                            <div className="team-header-content ">
                                                <span>{MiniLeagueData.mini_league_name} </span>
                                            </div>
                                        }
                                        <div className='match-timing'>
                                            {<span> <MomentDateComponent data={{ date: MiniLeagueData.scheduled_date, format: "D MMM" }} />
                             -  <MomentDateComponent data={{ date: MiniLeagueData.end_date, format: "D MMM" }} />
                                            </span>}
                                        </div>

                                    </div>
                                </div>


                            </Modal.Title>
                            <div className="leaderboard-rank margin-league-details">
                                <img src={Images.HALL_OF_FAME_SMALL_ICON} />

                                <div className="text_hall_of_fame">
                                    {AL.SPONSORED_BY}
                                </div>
                                {
                                    window.ReactNativeWebView ?
                                        <a
                                            href
                                            onClick={(event) => Utilities.callNativeRedirection(Utilities.getValidSponserURL(MiniLeagueSponser.sponsor_link, event))}>
                                            <img className="lobby_sponser-image sponser-card-image" style={{ resizeMode: 'contain' }} src={sponserImage == 0 ? Images.BRAND_LOGO_FULL_PNG : Utilities.getSponserURL(sponserImage)} />
                                        </a>

                                        :
                                        <a
                                            href={Utilities.getValidSponserURL(MiniLeagueSponser.sponsor_link)}
                                            onClick={(event) => event.stopPropagation()}
                                            target='_blank'>
                                            <img className="lobby_sponser-image sponser-card-image" style={{ resizeMode: 'contain' }} src={sponserImage == 0 ? Images.BRAND_LOGO_FULL_PNG : Utilities.getSponserURL(sponserImage)} />
                                        </a>

                                }

                            </div>
                        </Modal.Header>
                        <Modal.Body>

                            <Tabs id={'contest-detail-tab'} onSelect={this.ontabSelect} defaultActiveKey={this.props.activeTabIndex} className='tabs-multileage' >
                                <Tab eventKey={1} title={AL.PRIZES}>
                                    <Row className="Ftp-prizes p-t">

                                        {
                                            this.state.prizeList && this.state.prizeList.map((item, index) => {
                                                return (
                                                    <FtpPrizeComponent from={"LeagueDetails"} prizeListitem={item} merchandiseList={this.state.merchandiseList} />
                                                     );
                                            })
                                        }

                                    </Row>
                                    {

                                        this.state.prizeList && this.state.prizeList.length == 0 &&
                                        <NoDataView
                                            BG_IMAGE={Images.no_data_bg_image}
                                            // CENTER_IMAGE={Images.BRAND_LOGO_FULL}
                                            CENTER_IMAGE={Images.NO_DATA_VIEW}
                                            MESSAGE_1={AppLabels.LEAGUE_NO_PRIZE}
                                        />

                                    }
                                </Tab>
                                <Tab eventKey={2} title={AL.FIXTURE} className='table-content-height'>
                                    <div className="Ftp-prizes">
                                        {this.state.MiniLeagueList && this.state.MiniLeagueList.map((item, index) => {
                                            return (
                                                <React.Fragment key={index}>
                                                    <div className="collection-list-slider">
                                                        {this.FixtureListFunction(item)}
                                                    </div>




                                                </React.Fragment>
                                            );
                                        })

                                        }
                                    </div>

                                    {

                                        this.state.MiniLeagueList && this.state.MiniLeagueList.length == 0 &&
                                        <NoDataView
                                            BG_IMAGE={Images.no_data_bg_image}
                                            // CENTER_IMAGE={Images.BRAND_LOGO_FULL}
                                            CENTER_IMAGE={Images.NO_DATA_VIEW}
                                            MESSAGE_1={AppLabels.NO_FIXTURES_MSG1}
                                            onClick_2={this.joinContest}
                                        />

                                    }
                                </Tab>




                            </Tabs>
                        </Modal.Body>
                    </Modal>

                </div>

            </MyContext.Provider>
        )
    }
}

export default LeagueDetails;