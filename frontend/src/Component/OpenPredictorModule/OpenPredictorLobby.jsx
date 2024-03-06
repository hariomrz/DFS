import React,{lazy, Suspense} from 'react';
import { MyContext } from '../../InitialSetup/MyProvider';
import { updateDeviceToken, getLobbyOpenPrediction } from "../../WSHelper/WSCallings";
import { Utilities, parseURLDate, _debounce } from '../../Utilities/Utilities';
import { NoDataView, LobbyBannerSlider } from '../CustomComponent';
import { OpenPredictorContestList } from '.';
import Skeleton from 'react-loading-skeleton';
import ls from 'local-storage';
import OpenPredictorFixture from './OpenPredictorFixture';
import Images from '../../components/images';
import WSManager from "../../WSHelper/WSManager";
import * as AppLabels from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";
import * as Constants from "../../helper/Constants";
import { Nav, Row, Tab,Col } from 'react-bootstrap';
const ReactSlickSlider = lazy(()=>import('../CustomComponent/ReactSlickSlider'));

class OpenPredictorLobby extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            ContestList: [],
            ShimmerList: [1, 2, 3, 4, 5, 6, 7],
            isListLoading: false,
            sports_id: Constants.AppSelectedSport,
            selectedFixture: '',
            refreshList: true
        }
    }

    /**
     * @description - this is life cycle method of react
     */

    componentDidMount() {
        if (window.location.pathname === '/lobby') {
            WSManager.setFromConfirmPopupAddFunds(false);
            this.lobbyContestList();
            this.checkOldUrl();
            Utilities.handelNativeGoogleLogin(this)
            if (!ls.get('isDeviceTokenUpdated') && ls.get('isDeviceTokenUpdated')) {

                let token_data = {
                    action: 'push',
                    targetFunc: 'push',
                    type: 'deviceid',
                }
                this.sendMessageToApp(token_data)
            }
            setTimeout(() => {
                let push_data = {
                    action: 'push',
                    targetFunc: 'push',
                    type: 'receive',
                }
                this.sendMessageToApp(push_data)
            }, 300);
        }
    }

    UNSAFE_componentWillMount = () => {
        this.enableDisableBack(false)
        Utilities.scrollToTop()
    }

    enableDisableBack(flag) {
        if (window.ReactNativeWebView) {
            let data = {
                action: 'back',
                type: flag,
                targetFunc: 'handleLoginReceived'
            }
            this.sendMessageToApp(data);
        }
    }

    componentWillUnmount() {
        let data = {
            action: 'back',
            targetFunc: 'back',
            type: false,
        }
        this.sendMessageToApp(data);
    }

    UNSAFE_componentWillReceiveProps(nextProps) {
        if (this.state.sports_id != nextProps.selectedSport) {
            this.reload(nextProps);
        }
    }

    /**
     * @description method will be called when changing sports
     */
    reload = (nextProps) => {
        if (window.location.pathname.startsWith("/lobby")) {
            this.setState({
                ContestList: [],
                sports_id: nextProps.selectedSport,
            }, () => {
                WSManager.setFromConfirmPopupAddFunds(false);
                this.lobbyContestList();
            })
        }
    }

    sendMessageToApp(action) {
        if (window.ReactNativeWebView) {
            window.ReactNativeWebView.postMessage(JSON.stringify(action));
        }
    }

   

    blockMultiRedirection() {
        ls.set('canRedirect', false)
        setTimeout(() => {

            ls.set('canRedirect', true)
        }, 1000 * 5);
    }

    updateDeviceToken = () => {
        let param = {
            "device_type": Utilities.getDeviceType(),
            "device_id": WSC.DeviceToken.getDeviceId(),
        }
        if(WSManager.loggedIn() && !Constants.IS_SPORTS_HUB){
            updateDeviceToken(param).then((responseJson) => {
            })
        }
    }

    checkOldUrl() {
        let url = window.location.href;
        let sports = '#' + Utilities.getSelectedSportsForUrl();
        if (!url.includes(sports)) {
            url = url + sports
        }
        if (!url.includes('#open-predictor')) {
            url = url + "#open-predictor";
        }
        window.history.replaceState("", "", url);
    }

    /**
     * @description - method to get fixtures listing from server/s3 bucket
     */
    lobbyContestList = async () => {
        if (Constants.AppSelectedSport == null)
            return;

        let param = {
            "sports_id": Constants.AppSelectedSport
        }

        this.setState({ isListLoading: true })
        delete param.limit;
        var api_response_data = await getLobbyOpenPrediction(param);
        let alltab = [{
            // added_date: "2020-04-07 10:11:42",
            category_id: "",
            // image: "1586322157.png"
            name: "All",
            status: "1",
            // updated_date: "2020-04-08 05:02:38"
        }]
        if (api_response_data) {
            this.setState({
                ContestList: (api_response_data.category_list ? [...alltab,...api_response_data.category_list] : []),
                selectedFixture: alltab
            },()=>{
                this.onSelectFixture(alltab[0])
            })
        }
        this.setState({ isListLoading: false })
    }

    goToDFS = () => {
        WSManager.setPickedGameType(Constants.GameType.DFS);
        window.location.replace("/lobby#" + Utilities.getSelectedSportsForUrl());
    }

    goToRewards = () => {
        if (WSManager.loggedIn()) {
            this.props.history.push('/rewards')
        }
    }

    onSelectFixture = _debounce((fxtr) => {
        this.setState({
            selectedFixture: fxtr,
            refreshList: false
        }, () => {
            this.setState({
                refreshList: true
            })
        })
    }, 300)

    renderPredictionFixtures = () => {
        const {
            ContestList,
            isListLoading,
            ShimmerList,
            selectedFixture,
            refreshList
        } = this.state;

        var settings = {
            className: "slider variable-width",
            dots: false,
            infinite: false,
            centerMode: false,
            slidesToShow: 1,
            slidesToScroll: 1,
            variableWidth: true
            // touchThreshold: 10,
            // infinite: false,
            // slidesToScroll: 1,
            // slidesToShow:  ContestList.length > 2 ? 4 : 2,
            // variableWidth: false,
            // initialSlide: 0,
            // dots: false,
            // autoplay: false,
            // centerMode: false,
            // responsive: [
            //     {
            //         breakpoint: 767,
            //         settings: {
            //             slidesToShow:  ContestList.length > 2 ? 4 : 2,
            //             // className: "center",
            //             // centerMode: AvaSports.length > 2 ? true : false,
            //             // centerPadding: AvaSports.length == 3 ? '0' : '30px 0 10px',
            //             // initialSlide: AvaSports.length > 2 ? 1 : 0,
            //             // infinite: true,
            //             initialSlide: 0
            //         }
            //     },
            //     {
            //         breakpoint: 360,
            //         settings: {
            //             slidesToShow:  ContestList.length == 3 ? 3 : 2 ,
            //             className: "center",
            //             centerMode:  ContestList.length > 2 ? true : false,
            //             centerPadding:  ContestList.length == 3 ? '0' : '30px 0 10px',
            //             infinite: true,
            //             initialSlide:  ContestList.length > 2 ? 1 : 0,
            //         }

            //     }
            // ]
        };


        return (
            // <Tab.Container id='top-sports-slider' onSelect={() => console.log('onSelect')} 
                // activeKey={selectedFixture.toString()} defaultActiveKey={selectedFixture.toString()} 
                // className={Constants.SELECTED_GAMET == Constants.GameType.OpenPred ? 'hide' : ''}
                // >
                <Row>
                    <Col xs={12}>
                        <Row className="clearfix">
                            <Col className="bgc-clr sports-tab-nav sports-tab-slider open-predictor-view" xs={12}>
                                <ul >
                                    {/* <div className="bg-primary" /> */}

                                    <>
                                    {
                                        ContestList.length > 1 && <Suspense fallback={<div />} ><ReactSlickSlider settings = {settings}>
                                            {
                                                ContestList.map((item, index) => {
                                                    return (
                                                        <React.Fragment key={index} >
                                                            <OpenPredictorFixture {...this.props} item={item} onSelect={this.onSelectFixture} isActive={selectedFixture == item} />
                                                        </React.Fragment>
                                                    );
                                                })
                                            }
                                        </ReactSlickSlider></Suspense>
                                    }
                                    </>

                                </ul>
                            </Col>
                        </Row>

                        {
                            ContestList.length > 0 && refreshList && <OpenPredictorContestList {...this.props} goToDFS={this.goToDFS} goToRewards={this.goToRewards} data={{ LobbyData: selectedFixture }} />
                        }
                        <ul className="collection-list-wrapper pos-r">
                            {
                                (ContestList.length === 0 && !isListLoading) &&
                                <NoDataView
                                    BG_IMAGE={Images.no_data_bg_image}
                                    // CENTER_IMAGE={Images.BRAND_LOGO_FULL}
                                    CENTER_IMAGE={Images.NO_DATA_VIEW}
                                    MESSAGE_1={AppLabels.NO_FIXTURES_MSG1}
                                    MESSAGE_2={AppLabels.NO_FIXTURES_MSG2}
                                />
                            }
                            {
                                (ContestList.length === 0 && isListLoading) &&
                                ShimmerList.map((item, index) => {
                                    return (
                                        <React.Fragment key={index} >
                                            {
                                                index === 0 &&
                                                <div className="shimmer-fixture">
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
                                        </React.Fragment>
                                    )
                                })
                            }
                        </ul>
                    </Col>
                </Row>
        )
    }

    render() {

        const {
            BannerList
        } = this.state

        let bannerLength = BannerList ? BannerList.length : 0;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container no-tab-two-height pb-2 prediction-wrap-v open-predict-web-container open-predict-web-container-lobby">
                        <div>
                            {
                                bannerLength > 0 && 
                                <div className={bannerLength > 0 ? 'banner-v animation' : 'banner-v'}>
                                    {
                                        bannerLength > 0 && <LobbyBannerSlider BannerList={BannerList} redirectLink={this.redirectLink.bind(this)} />
                                    }
                                </div>
                            }
                            {
                                this.renderPredictionFixtures()
                            }

                        </div>
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}

export default OpenPredictorLobby
