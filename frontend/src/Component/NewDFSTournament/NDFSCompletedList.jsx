import React, { Component, lazy, Suspense } from 'react';
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import Images from '../../components/images';
import { Tab, Row, Col, Nav, NavItem } from 'react-bootstrap';
import { getDFSTTournamentList, getLobbyBanner, getDFSTTournamentLeaderboard, getLineupWithScore } from '../../WSHelper/WSCallings';
import WSManager from "../../WSHelper/WSManager";
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import { _times, Utilities, _Map, BannerRedirectLink } from '../../Utilities/Utilities';
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import CustomHeader from '../../components/CustomHeader';
import { AppSelectedSport, DARK_THEME_ENABLE, BANNER_TYPE_REFER_FRIEND, BANNER_TYPE_DEPOSITE } from '../../helper/Constants';
import NDFSTourCard from './NDFSTourCard';
import { NoDataView, LobbyBannerSlider } from '../CustomComponent';
import DFSTRulesModal from './DFSTRulesModal';
import { RFHTPModal } from '../../Modals';
import NDFSFixtureDetailModal from './NDFSFixtureDetailModal';
import InfiniteScroll from 'react-infinite-scroll-component';
import FieldView from "../../views/FieldView";
const DFSTourHTPModal = lazy(() => import('./DFSTourHTPModal'));




class NDFSCompletedList extends Component {
    constructor(props) {
        super(props)
        this.state = {
            tournamentList: [],
            isListLoading: false,
            showHTP: false,
            showRulesModal: false,
            BannerList: [],
            leaderboardData: [],
            isLoaderShow: false,
            ownList: [],
            pageNo: 1,
            hasMore: true,
            page_size: 20,
            // selectedOption: null,
            selectvalue: [],
            selectedOption: [],
            activeUserDetail: [],
            showFieldView: false,
            showFixDetail: false,
            activeFix: '',
            AllLineUPData: ''
        }
    }

    componentDidMount = () => {
        this.getTourList()
        //   this.getBannerList();
        // this.callLeaderboardApi()
    }

    getTourList = async () => {
        if (AppSelectedSport == null)
            return;
        this.setState({
            isListLoading: true
        })
        let param = {
            "sports_id": AppSelectedSport,
            "status": 2
        }
        let apiResponse = await getDFSTTournamentList(param)
        let data = apiResponse.data;
        let tournamentFilterData = data.filter((item, idx) => {
            return (item.status == 3 ? item :
                Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') > Utilities.getFormatedDateTime(Utilities.getUtcToLocal(item.start_date), 'YYYY-MM-DD HH:mm ')
            )
        })
        let dataTournament = tournamentFilterData.map((item, idx) => {
            return { "label": item.name, "value": item.tournament_id, "statusItem": item.status, "startedDate": item.start_date }
        })

        if (apiResponse) {
            this.setState({
                tournamentList: apiResponse.data,
                isListLoading: false,
                selectvalue: dataTournament,
                selectedOption: dataTournament[0]
            })
        }
    }

    goToDetail = (item) => {
        this.props.history.push({
            pathname: '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/dfs-tournament-detail/' + item.tournament_id,
            state: {
                tourId: item.tournament_id,
                completedItem: true
            }
        })
    }




    render() {
        const { leaderboardData, isLoaderShow, ownList, hasMore, tournamentList, isListLoading, showHTP, showRulesModal, BannerList, showRFHTPModal, selectedTab, selectvalue, selectedOption, activeUserDetail, showFixDetail, showFieldView, activeFix, AllLineUPData } = this.state;
        const HeaderOption = {
            back: true,
            MLogo: false,
            hideShadow: true,
            isPrimary: DARK_THEME_ENABLE ? false : true,
            notification: false,
            title: AL.COMPLETED_DFS_TOUR,
            title_text_view :true,
            isFrom: 'ndfs-tour'
        }
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container web-container-fixed dfs-tour-container DFS-tour-lobby">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.DFSTourList.title}</title>
                            <meta name="description" content={MetaData.DFSTourList.description} />
                            <meta name="keywords" content={MetaData.DFSTourList.keywords}></meta>
                        </Helmet>
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />


                        <div className="tour-listing pt15">
                            {
                                !isListLoading && tournamentList && tournamentList.length > 0 &&
                                _Map(tournamentList, (item, idx) => {
                                    return (
                                        <NDFSTourCard
                                            item={item}
                                            goToDetail={() => this.goToDetail(item)}
                                        />
                                    )
                                })
                            }
                            {
                                !isListLoading && tournamentList && tournamentList.length == 0 &&
                                <NoDataView
                                    BG_IMAGE={Images.no_data_bg_image}
                                    // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                    CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                    MESSAGE_1={AL.NO_DATA_AVAILABLE}
                                />
                            }
                        </div>
                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}

export default NDFSCompletedList;

