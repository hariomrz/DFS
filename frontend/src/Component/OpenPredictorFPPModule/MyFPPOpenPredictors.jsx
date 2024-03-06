import React from 'react';
import { Tab, Row, Col, Nav, NavItem } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities, _debounce } from '../../Utilities/Utilities';
import { UpcomingFPPOpenPredictors, LiveFPPOpenPredictors, CompletedFPPOpenPredictors } from './index';
import { my_contest_config } from '../../JsonFiles';
import { getMyFPPOpenPredictionCategory } from '../../WSHelper/WSCallings';
import { NoDataView } from '../CustomComponent';
import Skeleton from 'react-loading-skeleton';
import queryString from 'query-string';
import Images from '../../components/images';
import CustomHeader from '../../components/CustomHeader';
import WSManager from "../../WSHelper/WSManager";
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import * as Constants from "../../helper/Constants";
import Filter from "../../components/filter";
/**
  * @class MyContest
  * @description My contest listing of current loggedin user for selected sports
  * @author Vinfotech
*/
class MyFPPOpenPredictors extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            isLoaderShow: false,
            selectedTab: (this.props.location && this.props.location.state) ? (this.props.location.state.from == 'notification' ? Constants.CONTEST_COMPLETED : Constants.CONTEST_UPCOMING) : Constants.CONTEST_UPCOMING,
            lcList: [],
            ucList: [],
            ccList: [],
            ShimmerList: [0, 1, 2, 3, 4, 5],
            sports_id: Constants.AppSelectedSport ? Constants.AppSelectedSport : '',
            selectedFixture: '',
            refreshList: true,
            showMPFitler: false
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
        if (this.state.showMPFitler != nextProps.showLobbyFitlers) {
            this.setState({ showMPFitler: nextProps.showLobbyFitlers })
        }
        if (WSManager.loggedIn() && this.props.history.location.pathname == '/my-contests') {

            if (this.state.sports_id != nextProps.selectedSport) {
                this.reload(nextProps);
            }
            else {
                var url = this.props.location.search;
                if(window.location.search != url){
                    url = window.location.search;
                }
                let urlParams = queryString.parse(url);
                let contest = urlParams.contest;
                if (contest in my_contest_config.contest_url) {
                    let sports_id = Constants.AppSelectedSport;
                    if(my_contest_config.contest_url[contest] != this.state.selectedTab){
                        this.setState({ selectedTab: my_contest_config.contest_url[contest], sports_id }, () => {
                            this.getMyCollectionsList(this.state.selectedTab)
                        })
                    }else{
                    this.setState({ sports_id })
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

    /** 
    @description hide filters 
    */
    hideFilter = () => {
        this.setState({ showMPFitler: false })
        this.props.hideFilter()
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
                selectedTab: selectedTab, 
                ucList: [],
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
            "prediction_status": status,
        }
        this.setState({ isLoaderShow: true })
        getMyFPPOpenPredictionCategory(param).then((responseJson) => {
            this.setState({ isLoaderShow: false })

            if (responseJson && responseJson.response_code == WSC.successCode) {
                let list = responseJson.data.category_list || [];
                this.setState({
                    selectedFixture: list.length > 0 ? list[0] : ''
                },()=>{
                    setTimeout(() => {
                        CustomHeader.changeFilter(true, this.state.selectedFixture.category_name )                        
                    }, 100);                    
                })
                switch (this.state.selectedTab) {
                    case Constants.CONTEST_UPCOMING:
                        this.setState({ ucList: list })
                        break;
                    case Constants.CONTEST_LIVE:
                        this.setState({ lcList: list })
                        break;
                    case Constants.CONTEST_COMPLETED:
                        this.setState({ ccList: list })
                        break;
                    default:
                        this.setState({ ucList: list })
                }
            }
        })
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

    MPFilterSelect = (filterBy) => {
        this.setState({
            showMPFitler: false,
            selectedFixture : filterBy,
            refreshList: false
        }, () => {
            this.setState({
                refreshList: true
            })
        })
    }

    /**
     * @description This function render all UI components. It is the React lifecycle methods that called after @see componentWillMount()
     * @return UI Components
    */
    render() {
        let MESSAGE_1 = this.state.selectedTab == Constants.CONTEST_UPCOMING ? AppLabels.NO_UPCOMING_CONTEST1 : this.state.selectedTab == Constants.CONTEST_LIVE ?
            AppLabels.NO_LIVE_CONTEST1 : AppLabels.NO_COMPLETED_CONTEST1

        let MESSAGE_2 = this.state.selectedTab == Constants.CONTEST_UPCOMING ? AppLabels.NO_UPCOMING_CONTEST2 : this.state.selectedTab == Constants.CONTEST_LIVE ?
            AppLabels.NO_LIVE_CONTEST2 : AppLabels.NO_COMPLETED_CONTEST2

        let HeaderOption = {
            title: AppLabels.MY_CONTEST,
            notification: true,
            hideShadow: true,
            back: true,
            isPrimary: Constants.DARK_THEME_ENABLE ? false : true
        };

        let sliderList = this.state.selectedTab == Constants.CONTEST_UPCOMING ? this.state.ucList : this.state.selectedTab == Constants.CONTEST_LIVE ?
            this.state.lcList : this.state.ccList
        let FitlerOptions = {
            showMPFitler: this.state.showMPFitler
        }

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container open-predict-web-container my-contest-style tab-two-height web-container-fixed prediction-wrap-v">
                        {
                            !this.props.hideHeader &&
                            <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        }
                        {
                            this.state.refreshList &&
                            <Filter
                                {...this.props}
                                FitlerOptions={FitlerOptions}
                                hideFilter={this.hideFilter}
                                filerObj={sliderList}
                                MPFilterSelect={this.MPFilterSelect}
                                filterByCat={this.state.selectedFixture}
                            />
                        }
                        <div className={"tabs-primary " + (!this.props.hideHeader ? ' mt50' : '')}>
                            <Tab.Container id='my-contest-tabs' activeKey={this.state.selectedTab} onSelect={() => console.log('clicked')} defaultActiveKey={this.state.selectedTab}>
                                <Row className="clearfix">
                                    <Col className="top-fixed my-contest-tab circular-tab circular-tab-new" xs={12}>
                                        <Nav>
                                            <NavItem onClick={() => this.onTabClick(Constants.CONTEST_UPCOMING)} eventKey={Constants.CONTEST_UPCOMING}>{AppLabels.UPCOMING}</NavItem>
                                            <NavItem onClick={() => this.onTabClick(Constants.CONTEST_LIVE)} eventKey={Constants.CONTEST_LIVE} className="live-contest"><span className="live-indicator"></span> {AppLabels.LIVE} </NavItem>
                                            <NavItem onClick={() => this.onTabClick(Constants.CONTEST_COMPLETED)} eventKey={Constants.CONTEST_COMPLETED}>{AppLabels.COMPLETED}</NavItem>
                                        </Nav>
                                    </Col>
                                    <Col className="top-tab-margin" xs={12}>
                                        
                                        <Tab.Content animation>
                                            <Tab.Pane eventKey={Constants.CONTEST_LIVE}>
                                                {
                                                    this.state.lcList.length > 0 && this.state.refreshList && <LiveFPPOpenPredictors {...this.props} selectedFixture={this.state.selectedFixture} />
                                                }
                                                {
                                                    this.state.lcList.length == 0 && !this.state.isLoaderShow &&
                                                    <NoDataView
                                                        BG_IMAGE={Images.no_data_bg_image}
                                                        // CENTER_IMAGE={Images.BRAND_LOGO_FULL}
                                                        CENTER_IMAGE={Images.NO_DATA_VIEW}
                                                        MESSAGE_1={MESSAGE_1 + ' ' + MESSAGE_2}
                                                        MESSAGE_2={''}
                                                        BUTTON_TEXT={AppLabels.GO_TO_LOBBY}
                                                        onClick={this.goToLobby}
                                                    />
                                                }

                                                {
                                                    this.state.lcList.length == 0 && this.state.isLoaderShow &&
                                                    this.state.ShimmerList.map((item) => {
                                                        return (
                                                            <Shimmer key={item} idx={item} />
                                                        )
                                                    })
                                                }
                                            </Tab.Pane>
                                            <Tab.Pane eventKey={Constants.CONTEST_UPCOMING}>
                                                {
                                                    this.state.ucList.length > 0 && this.state.refreshList && <UpcomingFPPOpenPredictors {...this.props} selectedFixture={this.state.selectedFixture} />
                                                }
                                                {
                                                    this.state.ucList.length == 0 && !this.state.isLoaderShow &&
                                                    <NoDataView
                                                        BG_IMAGE={Images.no_data_bg_image}
                                                        // CENTER_IMAGE={Images.BRAND_LOGO_FULL}
                                                        CENTER_IMAGE={Images.NO_DATA_VIEW}
                                                        MESSAGE_1={MESSAGE_1 + ' ' + MESSAGE_2}
                                                        MESSAGE_2={''}
                                                        BUTTON_TEXT={AppLabels.GO_TO_LOBBY}
                                                        onClick={this.goToLobby}
                                                    />
                                                }

                                                {
                                                    this.state.ucList.length == 0 && this.state.isLoaderShow &&
                                                    this.state.ShimmerList.map((item) => {
                                                        return (
                                                            <Shimmer key={item} idx={item} />
                                                        )
                                                    })
                                                }

                                            </Tab.Pane>
                                            <Tab.Pane eventKey={Constants.CONTEST_COMPLETED}>
                                                {
                                                    this.state.ccList.length > 0 && this.state.refreshList && <CompletedFPPOpenPredictors {...this.props} selectedFixture={this.state.selectedFixture} />
                                                }
                                                {
                                                    this.state.ccList.length == 0 && !this.state.isLoaderShow &&
                                                    <NoDataView
                                                        BG_IMAGE={Images.no_data_bg_image}
                                                        // CENTER_IMAGE={Images.BRAND_LOGO_FULL}
                                                        CENTER_IMAGE={Images.NO_DATA_VIEW}
                                                        MESSAGE_1={MESSAGE_1 + ' ' + MESSAGE_2}
                                                        MESSAGE_2={''}
                                                        BUTTON_TEXT={AppLabels.GO_TO_LOBBY}
                                                        onClick={this.goToLobby}
                                                    />
                                                }

                                                {
                                                    this.state.ccList.length == 0 && this.state.isLoaderShow &&
                                                    this.state.ShimmerList.map((item) => {
                                                        return (
                                                            <Shimmer key={item} idx={item} />
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
        <ul className="collection-list-wrapper pos-r pb-0">
            {
                idx === 0 &&
                <div className="shimmer-fixture m-t">
                    <Skeleton width={'95%'} height={72} />
                    <Skeleton width={'95%'} height={72} />
                </div>
            }
            <div className="contest-list">
                <div className="shimmer-container">
                    <div className="shimmer-top-view">
                        <div className="shimmer-image predict">
                            <Skeleton width={24} height={24} />
                        </div>
                        <div className="shimmer-line predict">
                            <div className="m-v-xs">
                                <Skeleton height={8} width={'70%'} />
                            </div>
                            <Skeleton height={34} />
                            <Skeleton height={34} />
                        </div>
                    </div>
                    <div className="shimmer-bottom-view m-0 pt-3">
                        <div className="progress-bar-default">
                            <Skeleton height={8} width={'70%'} />
                            <div className="d-flex justify-content-between">
                                <Skeleton height={4} width={110} />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </ul>
    )
}

export default MyFPPOpenPredictors