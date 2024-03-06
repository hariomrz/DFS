import React from 'react';
import { Tab, Row, Col, Nav, NavItem } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import { _debounce } from '../../Utilities/Utilities';
import { UpcomingPredictions, LivePredictions, CompletedPredictions } from './index';
import { my_contest_config } from '../../JsonFiles';
import { getPredictionSeason } from '../../WSHelper/WSCallings';
import { NoDataView } from '../CustomComponent';
import Skeleton,{SkeletonTheme} from 'react-loading-skeleton';
import queryString from 'query-string';
import Images from '../../components/images';
import CustomHeader from '../../components/CustomHeader';
import WSManager from "../../WSHelper/WSManager";
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import * as Constants from "../../helper/Constants";

/**
  * @class MyContest
  * @description My contest listing of current loggedin user for selected sports
  * @author Vinfotech
*/
export default class MyPredictions extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            isLoaderShow: false,
            selectedTab: (this.props.location && this.props.location.state) ? (this.props.location.state.from == 'notification' ? Constants.CONTEST_COMPLETED : Constants.CONTEST_UPCOMING) : Constants.CONTEST_UPCOMING,
            lcList: [],
            ucList: [],
            ShimmerList: [1, 2, 3, 4, 5],
            ccList: [],
            sports_id: Constants.AppSelectedSport ? Constants.AppSelectedSport : ''
        }
    }

    componentDidMount() {
        let url = this.props.location.search;
        let urlParams = queryString.parse(url);

        let contest = urlParams.contest;
        if (contest in my_contest_config.contest_url) {
            let sports_id = Constants.AppSelectedSport;
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
                    let sports_id = Constants.AppSelectedSport;
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
                }
            }
        }
    }

    /**
     * @description Call this function when you want to go fo lobby screen
    */
    goToLobby = () => {
        this.props.history.push({ pathname: '/' })
    }

    /**
     * @description Event of tab click (Live, Upcoming, Completed)
     * @param selectedTab value of selected tab
     */
    onTabClick = _debounce((selectedTab) => {
        if (this.state.selectedTab !== selectedTab) {
            window.history.replaceState("", "", "/my-contests?contest=" + my_contest_config.contest[selectedTab]);
            this.setState({
                selectedTab: selectedTab, ucList: [],
                lcList: [],
                ccList: []
            }, () => {
                this.getMyCollectionsList(this.state.selectedTab)
            });
        }
    }, 300)

    /**
     * @description This function is responsible to get Live Contests response
     * @param status selected tab (Live, Upcoming, Completed)
     */
    getMyCollectionsList(status) {
        var param = {
            "sports_id": Constants.AppSelectedSport,
            "match_status": status,
        }
        this.setState({ isLoaderShow: true })
        getPredictionSeason(param).then((responseJson) => {
            this.setState({ isLoaderShow: false })

            if (responseJson && responseJson.response_code == WSC.successCode) {
                switch (this.state.selectedTab) {
                    case Constants.CONTEST_UPCOMING:
                        this.setState({ ucList: responseJson.data.match_list || [] })
                        break;
                    case Constants.CONTEST_LIVE:
                        this.setState({ lcList: responseJson.data.match_list || [] })
                        break;
                    case Constants.CONTEST_COMPLETED:
                        this.setState({ ccList: responseJson.data.match_list || [] })
                        break;
                    default:
                        this.setState({ ucList: responseJson.data.match_list || [] })
                }
            }
        })
    }

    /**
     * @description This function is responsible to remove item from list
     * @param status Selected Tab
     * @param index index of item to remove from list
     */
    removeFromList = (status, index) => {
        let list = this.state.ucList;
        list.splice(index, 1);
        this.setState({ ucList: list })
    }

    /**
     * @description This function is called when sports changed from header
     * @static A static function 
    */
    reload = (nextProps) => {
        if (window.location.pathname.startsWith("/my-contests")) {
            this.setState({ ccList: [], lcList: [], ucList: [], sports_id: nextProps.selectedSport }, () => {
                this.getMyCollectionsList(this.state.selectedTab)
            })
        }
    }

    /**
     * @description This function render all UI components. It is the React lifecycle methods that called after @see componentWillMount()
     * @return UI Components
    */
    render() {
        let MESSAGE_1 = this.state.selectedTab == Constants.CONTEST_UPCOMING ?
            AppLabels.NO_UPCOMING_CONTEST1 : this.state.selectedTab == Constants.CONTEST_LIVE ?
                AppLabels.NO_LIVE_CONTEST1 : AppLabels.NO_COMPLETED_CONTEST1

        let MESSAGE_2 = this.state.selectedTab == Constants.CONTEST_UPCOMING ?
            AppLabels.NO_UPCOMING_CONTEST2 : this.state.selectedTab == Constants.CONTEST_LIVE ?
                AppLabels.NO_LIVE_CONTEST2 : AppLabels.NO_COMPLETED_CONTEST2

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
                    <div className="web-container my-contest-style tab-two-height web-container-fixed">
                        {
                            !this.props.hideHeader &&
                            <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        }
                        <div className={"tabs-primary " + (!this.props.hideHeader ? ' mt50' : '')}>
                            <Tab.Container id='my-contest-tabs' activeKey={this.state.selectedTab} onSelect={() => console.log('clicked')} defaultActiveKey={this.state.selectedTab}>
                                <Row className="clearfix">
                                    <Col className="top-fixed my-contest-tab circular-tab circular-tab-new xnew-tab" xs={12}>
                                        <Nav>
                                            <NavItem onClick={() => this.onTabClick(Constants.CONTEST_UPCOMING)} eventKey={Constants.CONTEST_UPCOMING}>{AppLabels.UPCOMING}</NavItem>
                                            <NavItem onClick={() => this.onTabClick(Constants.CONTEST_LIVE)} eventKey={Constants.CONTEST_LIVE} className="live-contest"><span><span className="live-indicator"></span> {AppLabels.LIVE} </span></NavItem>
                                            <NavItem onClick={() => this.onTabClick(Constants.CONTEST_COMPLETED)} eventKey={Constants.CONTEST_COMPLETED}>{AppLabels.COMPLETED}</NavItem>
                                        </Nav>
                                    </Col>
                                    <Col className="top-tab-margin" xs={12}>
                                        <Tab.Content animation>
                                            <Tab.Pane eventKey={Constants.CONTEST_LIVE}>
                                                <LivePredictions {...this.props} lcList={this.state.lcList} />

                                                {
                                                    this.state.lcList.length == 0 && !this.state.isLoaderShow &&
                                                    <NoDataView
                                                        BG_IMAGE={Images.no_data_bg_image}
                                                        // CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                        CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                        MESSAGE_1={MESSAGE_1 + ' ' + MESSAGE_2}
                                                        MESSAGE_2={''}
                                                        BUTTON_TEXT={AppLabels.GO_TO_LOBBY}
                                                        onClick={this.goToLobby}
                                                    />
                                                }

                                                {
                                                    this.state.lcList.length == 0 && this.state.isLoaderShow &&
                                                    this.state.ShimmerList.map((item, index) => {
                                                        return (
                                                            <Shimmer key={index} />
                                                        )
                                                    })
                                                }
                                            </Tab.Pane>
                                            <Tab.Pane eventKey={Constants.CONTEST_UPCOMING}>

                                                <UpcomingPredictions {...this.props} ucList={this.state.ucList} removeFromList={this.removeFromList} />

                                                {
                                                    this.state.ucList.length == 0 && !this.state.isLoaderShow &&
                                                    <NoDataView
                                                        BG_IMAGE={Images.no_data_bg_image}
                                                        // CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                        CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                        MESSAGE_1={MESSAGE_1 + ' ' + MESSAGE_2}
                                                        MESSAGE_2={''}
                                                        BUTTON_TEXT={AppLabels.GO_TO_LOBBY}
                                                        onClick={this.goToLobby}
                                                    />
                                                }

                                                {
                                                    this.state.ucList.length == 0 && this.state.isLoaderShow &&
                                                    this.state.ShimmerList.map((item, index) => {
                                                        return (
                                                            <Shimmer key={index} />
                                                        )
                                                    })
                                                }

                                            </Tab.Pane>
                                            <Tab.Pane eventKey={Constants.CONTEST_COMPLETED}>
                                                <CompletedPredictions {...this.props} ccList={this.state.ccList} />

                                                {
                                                    this.state.ccList.length == 0 && !this.state.isLoaderShow &&
                                                    <NoDataView
                                                        BG_IMAGE={Images.no_data_bg_image}
                                                        // CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                        CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                        MESSAGE_1={MESSAGE_1 + ' ' + MESSAGE_2}
                                                        MESSAGE_2={''}
                                                        BUTTON_TEXT={AppLabels.GO_TO_LOBBY}
                                                        onClick={this.goToLobby}
                                                    />
                                                }

                                                {
                                                    this.state.ccList.length == 0 && this.state.isLoaderShow &&
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
                        </div>
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
        <SkeletonTheme color={Constants.DARK_THEME_ENABLE ? "#030409" : null} highlightColor={Constants.DARK_THEME_ENABLE ? "#0E2739" : null}>
            <div key={idx} className="contest-list m border shadow-none border-0">
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