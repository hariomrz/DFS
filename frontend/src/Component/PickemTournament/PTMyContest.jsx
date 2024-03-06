import React from 'react';
import { Tab, Row, Col, Nav, NavItem } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities, _isUndefined, _isEmpty, _debounce, _filter, _Map } from '../../Utilities/Utilities';
import { my_contest_config } from '../../JsonFiles';
import { getPTMyContest } from '../../WSHelper/WSCallings';
import Images from '../../components/images';
import WSManager from "../../WSHelper/WSManager";
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import * as Constants from "../../helper/Constants";
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import queryString from 'query-string';
import CustomHeader from '../../components/CustomHeader';
import { NoDataView } from '../CustomComponent';
import PTLiveContest from './PTLiveContest';
import PTUpcomingContest from './PTUpcomingContest';
import PTCompleted from './PTCompleted';
import PTCardTournament from './PTCardTournament';


/**
  * @class MyContest
  * @description My contest listing of current loggedin user for selected sports
  * @author Vinfotech
*/
export default class PTMyContest extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            isLoaderShow: false,
            liveContestList: [],
            upcomingContestList: [],
            ShimmerList: [1, 2, 3, 4, 5],
            completedContestList: [],
            sports_id: Constants.AppSelectedSport ? Constants.AppSelectedSport : '',
            selectedTab: (this.props.location && this.props.location.state) ? (this.props.location.state.from == 'notification' || this.props.location.state.from == 'lobby-completed' ? Constants.CONTEST_COMPLETED : this.props.location.state.from == 'lobby-live' ? Constants.CONTEST_LIVE : Constants.CONTEST_UPCOMING) : Constants.CONTEST_UPCOMING,
            tournamentCard: []

        }
    }

    componentDidMount() {
        Utilities.setScreenName('contests')
        Utilities.handleAppBackManage('my-contest')
        WSManager.setH2hMessage(false)
        let url = this.props.location.search;
        let urlParams = queryString.parse(url);
        let contest = urlParams.contest;
        if (contest in my_contest_config.contest_url) {
            let { sports_id } = this.state;
            sports_id = Constants.AppSelectedSport;

            this.setState({ selectedTab: my_contest_config.contest_url[contest], sports_id }, () => {
                this.getMyCollectionsList(this.state.selectedTab)

            })
        }
        else {
            if (contest in my_contest_config.contest) {
                this.props.history.replace("/my-contests?contest=" + my_contest_config.contest[contest])
            }
            else {
                this.props.history.replace("/my-contests?contest=" + my_contest_config.contest[this.state.selectedTab])
            }
            this.setState({ sports_id: Constants.AppSelectedSport }, () => {
                this.getMyCollectionsList(this.state.selectedTab)

            })
        }
    }

    UNSAFE_componentWillReceiveProps(nextProps) {
        if (WSManager.loggedIn() && this.props.history.location.pathname == '/my-contests') {

            if (this.state.sports_id != nextProps.selectedSport) {
                this.reload(nextProps);
            }
            else {
                let url = this.props.location.search;
                let urlParams = queryString.parse(url);

                let contest = urlParams.contest;
                if (contest in my_contest_config.contest_url) {
                    let { sports_id } = this.state;
                    sports_id = Constants.AppSelectedSport;
                    let tmpSelectedTab = my_contest_config.contest_url[contest];
                    if (this.state.selectedTab != tmpSelectedTab || this.state.sports_id != Constants.AppSelectedSport) {

                        this.setState({ selectedTab: my_contest_config.contest_url[contest], sports_id }, () => {
                            this.getMyCollectionsList(this.state.selectedTab)

                        })
                    }
                }
                else {
                    if (contest in my_contest_config.contest) {
                        this.props.history.replace("/my-contests?contest=" + my_contest_config.contest[contest])
                    }
                    else {
                        this.props.history.replace("/my-contests?contest=" + my_contest_config.contest[this.state.selectedTab])
                    }
                }
            }
        }
    }

    goLobby = () => {
        this.props.history.push({ pathname: '/' })
    }

    /**
     * @description Event of tab click (Live, Upcoming, Completed)
     * @param selectedTab value of selected tab
     */
    onTabClick = _debounce((selectedTab) => {
        window.history.replaceState("", "", "/my-contests?contest=" + my_contest_config.contest[selectedTab]);
        this.setState({ selectedTab: selectedTab }, () => {
            this.getMyCollectionsList(this.state.selectedTab)

        });
    }, 300)

    /**
     * @description This function is responsible to get Live Contests response
     * @param status selected tab (Live, Upcoming, Completed)
     */
    getMyCollectionsList = async (status) => {
        var param = {
            "sports_id": Constants.AppSelectedSport,
            is_previous: 1

            // "status": status,
        }
        this.setState({ isLoaderShow: true })

        let apiStatus = getPTMyContest
        var responseJson = await apiStatus(param);
        // this.setState({ isLoaderShow: false })
        this.setState({ tournamentCard: responseJson.data, isLoaderShow: false })


        // if (responseJson && responseJson.response_code == WSC.successCode) {
        //     switch (this.state.selectedTab) {
        //         case Constants.CONTEST_UPCOMING:
        //             this.setState({ upcomingContestList: responseJson.data })
        //             break;
        //         case Constants.CONTEST_LIVE:
        //             this.setState({ liveContestList: responseJson.data })
        //             break;
        //         case Constants.CONTEST_COMPLETED:
        //             this.setState({ completedContestList: responseJson.data })
        //             break;
        //         default:
        //             this.setState({ upcomingContestList: responseJson.data })
        //     }
        // }

    }

    /**
     * @description This function is called when sports changed from header
     * @static A static function 
    */
    reload = (nextProps) => {
        if (window.location.pathname.startsWith("/my-contests")) {
            this.setState({ completedContestList: [], liveContestList: [], upcomingContestList: [], sports_id: nextProps.selectedSport }, () => {
                this.getMyCollectionsList(this.state.selectedTab)

            })
        }
    }

    gotoDetails = (item) => {
        this.props.history.push({
            pathname: '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/pickem/detail/' + item.tournament_id,
            state: {
                tourId: item.tournament_id
            }
        })
    }
    showCompltedList = () => {
        if(WSManager.loggedIn()){
            this.props.history.push({
                pathname: '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/pickem-tournament-completed-list' 
            })
        }
        else{
            this.props.history.push({ pathname: '/signup' })
        }
    }

    /**
     * @description This function render all UI components. It is the React lifecycle methods that called after @see componentWillMount()
     * @return UI Components
    */
    render() {
        const {
            liveContestList, tournamentCard
        } = this.state;

        let MESSAGE_1 = this.state.selectedTab == Constants.CONTEST_UPCOMING ?
            AppLabels.NO_UPCOMING_CONTEST1
            :
            this.state.selectedTab == Constants.CONTEST_LIVE ?
                AppLabels.PICK_NO_LIVE_CONTEST1
                :
                AppLabels.PICK_NO_COMPLETED_CONTEST1

        let MESSAGE_2 = this.state.selectedTab == Constants.CONTEST_UPCOMING ?
            AppLabels.PICK_UPCOMING_CONTEST2
            :
            this.state.selectedTab == Constants.CONTEST_LIVE ?
                AppLabels.NO_LIVE_CONTEST2
                :
                AppLabels.NO_COMPLETED_CONTEST2

        let HeaderOption = {
            title: AppLabels.MY_CONTEST,
            notification: true,
            hideShadow: true,
            back: true,
            isPrimary: Constants.DARK_THEME_ENABLE ? false : true

        };
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"web-container my-contest-style tab-two-height web-container-fixed pickem-tour-mycontest"}>
                        {!this.props.hideHeader &&
                            <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        }
                        {
                            tournamentCard && tournamentCard.length > 0 &&
                            <div className="tour-list tour-list-new">
                                {
                                    _Map(tournamentCard, (item, idx) => {
                                        return (
                                            <PTCardTournament
                                                item={item}
                                                KEY={idx}
                                                gotoDetails={() => this.gotoDetails(item)}
                                            // joinTournament={(e)=>this.joinTournament(e,item)}
                                            />
                                        )
                                    })
                                }
                            </div>
                        }
                        {
                            tournamentCard && tournamentCard.length == 0 &&
                            <NoDataView
                                BG_IMAGE={Images.no_data_bg_image}
                                // CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                MESSAGE_1={AppLabels.NO_FIXTURES_MSG1}
                            />
                        }
                        <div className='all-completed-view all-completed-view-new' onClick={() => this.showCompltedList()}>
                            <div className="completed-text">{AppLabels.VIEW_ALL} {" "}{AppLabels.COMPLETED}</div>
                        </div>

                        {/* <div className={"tabs-primary " + (!this.props.hideHeader ? ' mt50' : '')}>
                            <Tab.Container id='my-contest-tabs' activeKey={this.state.selectedTab}  defaultActiveKey={this.state.selectedTab}>
                                <Row className="clearfix">
                                    <Col className="top-fixed my-contest-tab circular-tab circular-tab-new xnew-tab" xs={12}>
                                        <Nav>
                                            <NavItem onClick={() => this.onTabClick(Constants.CONTEST_UPCOMING)} eventKey={Constants.CONTEST_UPCOMING}>{AppLabels.UPCOMING}</NavItem>
                                            <NavItem onClick={() => this.onTabClick(Constants.CONTEST_LIVE)} eventKey={Constants.CONTEST_LIVE} className="live-contest"><span><span className="live-indicator"></span> {AppLabels.LIVE} </span></NavItem>
                                            <NavItem onClick={() => this.onTabClick(Constants.CONTEST_COMPLETED)} eventKey={Constants.CONTEST_COMPLETED}>{AppLabels.COMPLETED}</NavItem>
                                        </Nav>
                                    </Col>

                                    <h3 className='tour-head'>{AppLabels.TOURNAMENT}</h3>
                                    <Col className="top-tab-margin" xs={12}>
                                        <Tab.Content animation>
                                            <Tab.Pane eventKey={Constants.CONTEST_LIVE}>
                                                <PTLiveContest {...this.props} liveContestList={this.state.liveContestList}
                                                    isLoaderShow={this.state.isLoaderShow} 
                                                    gotoDetails={this.gotoDetails} />

                                                {
                                                    this.state.liveContestList.length == 0 && !this.state.isLoaderShow &&
                                                    <NoDataView
                                                        BG_IMAGE={Images.no_data_bg_image}
                                                        // CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                        CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                        MESSAGE_1={MESSAGE_1 + ' ' + MESSAGE_2}
                                                        MESSAGE_2={''}
                                                        BUTTON_TEXT={AppLabels.GO_TO_LOBBY}
                                                        onClick={this.goLobby}
                                                    />
                                                }

                                                {
                                                    this.state.liveContestList.length == 0 && this.state.isLoaderShow &&
                                                    this.state.ShimmerList.map((item, index) => {
                                                        return (
                                                            <Shimmer key={index} />
                                                        )
                                                    })
                                                }
                                            </Tab.Pane>
                                            <Tab.Pane eventKey={Constants.CONTEST_UPCOMING}>
                                                <PTUpcomingContest {...this.props} 
                                                upcomingContestList={this.state.upcomingContestList} 
                                                gotoDetails={this.gotoDetails} />


                                                {
                                                    this.state.upcomingContestList.length == 0 && !this.state.isLoaderShow &&
                                                    <NoDataView
                                                        BG_IMAGE={Images.no_data_bg_image}
                                                        // CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                        CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                        MESSAGE_1={MESSAGE_1 + ' ' + MESSAGE_2}
                                                        MESSAGE_2={''}
                                                        BUTTON_TEXT={AppLabels.GO_TO_LOBBY}
                                                        onClick={this.goLobby}
                                                    />
                                                }
                                                {
                                                    this.state.upcomingContestList.length == 0 && this.state.isLoaderShow &&
                                                    this.state.ShimmerList.map((item, index) => {
                                                        return (
                                                            <Shimmer key={index} />
                                                        )
                                                    })
                                                }

                                            </Tab.Pane>
                                            <Tab.Pane eventKey={Constants.CONTEST_COMPLETED}>
                                                <PTCompleted {...this.props} completedContestList={this.state.completedContestList} 
                                                gotoDetails={this.gotoDetails} />
                                                {
                                                    this.state.completedContestList.length == 0 && !this.state.isLoaderShow &&
                                                    <NoDataView
                                                        BG_IMAGE={Images.no_data_bg_image}
                                                        // CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                        CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                        MESSAGE_1={AppLabels.NO_TOURNAMENTS_COMPLETED}
                                                        MESSAGE_2={''}
                                                        BUTTON_TEXT={AppLabels.GO_TO_LOBBY}
                                                        onClick={this.goLobby}
                                                    />
                                                }
                                                {
                                                    this.state.completedContestList.length == 0 && this.state.isLoaderShow &&
                                                    this.state.ShimmerList.map((item, index) => {
                                                        return (
                                                            <Shimmer key={index} />
                                                        )
                                                    })
                                                }

                                            </Tab.Pane>
                                        </Tab.Content>
                                    </Col>
                                </Row>
                            </Tab.Container>
                        </div> */}
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}

/**
  * @description Display shimmer effects while loading list
  * @return UI components
*/
const Shimmer = ({ idx }) => {
    return (
        <SkeletonTheme color={Constants.DARK_THEME_ENABLE ? "#161920" : null} highlightColor={Constants.DARK_THEME_ENABLE ? "#0E2739" : null}>
            <div key={idx} className="contest-list m border shadow-none shimmer-border">
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
    )
}