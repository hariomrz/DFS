import React, { Component } from 'react';
import { Col, Row } from 'react-bootstrap';
import { MyContext } from '../../views/Dashboard';
import { _isUndefined, Utilities } from '../../Utilities/Utilities';
import Helmet from 'react-helmet';
import MetaData from '../../helper/MetaData';
import * as AL from "../../helper/AppLabels";
import SponserBySection from "./SponserBy";
import Images from '../../components/images';
import { getMiniLeagueDetails } from "../../WSHelper/WSCallings";
import * as Constants from "../../helper/Constants";
import { NoDataView } from '../CustomComponent';
import * as AppLabels from "../../helper/AppLabels";
import FtpPrizeComponent from './FtpPrizeComponent';

class AllPrizes extends Component {
    constructor(props) {
        super(props);
        var propsData = !_isUndefined(props.location.state) ? props.location.state : {};
        this.state = {
            LobyyData: propsData.LobyyData || [],
            MiniLeagueData: propsData.MiniLeagueData || '',
            isMiniLeaguePrize: propsData.isMiniLeaguePrize || '',
            prizeList: [],
            merchandiseList: []
        }
    }

    UNSAFE_componentWillMount = () => {
        Utilities.setScreenName('SHS')
        if (this.state.isMiniLeaguePrize && this.props.match.params.isMiniLeaguePrize) {
            this.getMiniLeagueDetails();
        }
        else {
            this.setState({
                HeaderOption: {
                    back: true,
                    title: this.state.MiniLeagueData.collection_name,
                    hideShadow: false
                },
                prizeList: this.state.MiniLeagueData.prize_distibution_detail,
                merchandiseList: this.state.MiniLeagueData.merchandise
            })
        }
    }

    getMiniLeagueDetails = async () => {
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
                MiniLeagueList: api_response_data.data.match_list, HeaderOption: {
                    back: true,
                    title: api_response_data.data.mini_league_name,
                    hideShadow: false
                },
                prizeList: api_response_data.data.prize_distibution_detail,
                merchandiseList: api_response_data.data.merchandise,
            })
        }
    }
    setCurrentMaxPrize = (minMaxValue, prizeItem) => {
        var maxMini = prizeItem.max - prizeItem.min + 1;
        var finalPrize = (minMaxValue / maxMini)
        return finalPrize;
    }

    render() {
        const { MiniLeagueData } = this.state;

        let sponserImage = MiniLeagueData && MiniLeagueData.sponsor_logo ? MiniLeagueData.sponsor_logo : 0;

        return (
            <MyContext.Provider >
                <div className="web-container Ftp-web-container padding-less Ftp-all-prizes">
                    <Helmet titleTemplate={`${MetaData.template} | %s`}>
                        <title>{MetaData.SHS.title}</title>
                        <meta name="description" content={MetaData.SHS.description} />
                        <meta name="keywords" content={MetaData.SHS.keywords}></meta>
                    </Helmet>
                    <div className="Ftp-contest">
                        <div className="Ftp-header less-height">
                            <div className='row-container'>
                                <div className='section-left'>
                                    <a href className="header-action" onClick={() => this.props.history.goBack()} >
                                        <i className="icon-left-arrow"></i>
                                    </a>
                                </div>

                                <div className='section-middle'>
                                    <div className="match-team-info"> <span>{this.state.isMiniLeaguePrize ? this.state.MiniLeagueData.mini_league_name : this.state.MiniLeagueData.collection_name}</span> </div>
                                    <div className="ftp-all-prizes-label">{AL.ALL_PRIZES}</div>
                                </div>

                                <div xs={2} className='section-right'>
                                    <img alt='' src={Images.HALL_OF_FAME_SMALL_ICON}></img>

                                </div>
                            </div>
                        </div>
                        <SponserBySection item={
                            {
                                'img': sponserImage == 0 ? Images.BRAND_LOGO_FULL_PNG : Utilities.getSponserURL(sponserImage),
                                'sponsor_link': MiniLeagueData.sponsor_link

                            }
                        } />
                        <div className="Ftp-body Ftp-all-prizes-body">
                            <Row className="Ftp-prizes">
                                {
                                    this.state.prizeList && this.state.prizeList.map((item, index) => {
                                        return (
                                            <FtpPrizeComponent from={"AllPrizes"} prizeListitem={item} merchandiseList={this.state.merchandiseList} />
                                        );
                                    })
                                }
                            </Row>
                        </div>
                        {
                            this.state.prizeList && this.state.prizeList.length === 0 &&
                            <NoDataView
                                BG_IMAGE={Images.no_data_bg_image}
                                CENTER_IMAGE={Images.NO_DATA_VIEW}
                                // CENTER_IMAGE={Images.BRAND_LOGO_FULL}
                                MESSAGE_1={AppLabels.LEAGUE_NO_PRIZE}
                                onClick_2={this.joinContest}
                            />
                        }
                    </div>
                </div>
            </MyContext.Provider>
        )
    }
}

export default AllPrizes;