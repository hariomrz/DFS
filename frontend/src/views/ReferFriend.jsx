import React,{lazy, Suspense} from 'react';
import { MyContext } from '../InitialSetup/MyProvider';
import { Tabs, Tab, Label, Row, Col, ProgressBar } from 'react-bootstrap';
import { Helmet } from "react-helmet";
import { Utilities, _Map } from '../Utilities/Utilities';
import InfiniteScroll from 'react-infinite-scroll-component';
import Images from '../components/images';
import MetaData from "../helper/MetaData";
import CustomHeader from '../components/CustomHeader';
import WSManager from "../WSHelper/WSManager";
import * as WSC from "../WSHelper/WSConstants";
import * as AppLabels from "../helper/AppLabels";
import ls from 'local-storage';
import { getReferralMasterData, getMyReferralList, getMasterDataRef, getUserEarnMoney } from '../WSHelper/WSCallings';
import { NoDataView } from '../Component/CustomComponent';
import { ShareReferal } from "../Component/CustomComponent";
import Moment from 'react-moment';
import { ReferralSystem} from "../Modals";
import Skeleton,{SkeletonTheme} from 'react-loading-skeleton';
import { DARK_THEME_ENABLE, OnlyCoinsFlow } from "../helper/Constants";
const ReactSlickSlider = lazy(()=>import('../Component/CustomComponent/ReactSlickSlider'));

export default class ReferFriend extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            masterData: [],
            achvmentData: [],
            userList: [],
            UserReferralList: [],
            copied: false,
            hasMore: false,
            isLoaderShow: false,
            userRefOffset: 1,
            shareURL: WSC.baseURL + "signup/?referral=" + WSManager.getUserReferralCode(),
            selectedTab: 1,
            loadingData: false,
            showReferalSystem: false,
           
            isShowDetails: false,
            activeIndex: 0,
            profileDetail: WSManager.getProfile(),
            isEnableRef: true,
            isDisplayLowerBanner: false,
            isReferBy: false,
            refName: '',
            refAmount: '',
            refType: '',
            totalJoined: '',
            totalRefRealCase: '',
            totalRefBonusCase: '',
            totalRefCoins: '',
            refMasterDataFromService: '',
            totalBonus: 0,
            totalCoins: 0,
            totalRealCase: 0,
            bannerValue: 0,
            bannerValueType: '',
            bannerCountNo: '',
            isDataLoading: true,
            userEarnDetail: [],
            displayAchvments: true,
            BannerList: ['1', '2', '3'],
            showBG: false,
        }
    }

    ShowReferralSystemModal = () => {
        this.setState({
            showReferalSystem: true
        })
    }
    HideReferralSystemModal = (showcheck) => {
        if(showcheck == true){
            ls.set('isShowPopup', '1');            
            // let tempProfile = this.state.profileDetail;
            // tempProfile.referral_code = this.state.newRefCode;
            // tempProfile.is_rc_edit = '1';
            // tempProfile.isShowPopup='1'
            // WSManager.setProfile(tempProfile);
        }
        this.setState({
            showReferalSystem: false,
        })

        // this.setState({
        //     showReferalSystem: false,
        // })
        // if (this.state.profileDetail.is_rc_edit == 1) {
        //     return;
        // }

        // let passingData = this.state.masterData[16]
        // this.props.history.push('/edit-referral-code', passingData);
    }
    HideEditCodeModal = () => {
        this.setState({
            showEditCodeModal: false
        })
    }

    componentDidMount() {
        window.addEventListener('scroll', this.onScrollList);
        if (ls.get("isShowPopup") != 1 ) {
           this.ShowReferralSystemModal();
        }
    }

    onScrollList = (event) => {
        let scrollOffset = window.pageYOffset;
        if (scrollOffset > 0) {
            this.setState({
                showBG: true
            })
        }
        else {
            this.setState({
                showBG: false
            })
        }
    }

    UNSAFE_componentWillMount() {
        Utilities.setScreenName('referfriend')
        
        this.getRefMasterData();
    }

    showEditReferralPage=()=>{
        let passingData = this.state.masterData[16]
        this.props.history.push('/edit-referral-code', passingData);
    }


    openRefSystem = (e) => {
        this.setState({
            showReferalSystem : true
        })
    }
    onCopyCode = () => {
        Utilities.showToast(AppLabels.MSZ_COPY_CODE, 1000);
        this.setState({ copied: true })
    }

    onCopyLink = () => {
        Utilities.showToast(AppLabels.Link_has_been_copied, 1000);
        this.setState({ copied: true })
    }

    goToShare = () => {
        this.setState({ selectedTab: 1 })
    }

    // *****************************************CALL MASTER REF DATA*****************************************
    callRFMasterDataApi = () => {
        let param = {}
        getReferralMasterData(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({
                    masterData: responseJson.data,
                })
                let achvment = [];
                let fifthJoin = {
                    'amount': parseInt(responseJson.data[17].bonus_amount) >= parseInt(responseJson.data[17].coin_amount) && parseInt(responseJson.data[17].bonus_amount) >= parseInt(responseJson.data[17].real_amount) ?
                        responseJson.data[17].bonus_amount : parseInt(responseJson.data[17].coin_amount) >= parseInt(responseJson.data[17].bonus_amount) && parseInt(responseJson.data[17].coin_amount) >= parseInt(responseJson.data[17].real_amount) ?
                            responseJson.data[17].coin_amount : responseJson.data[17].real_amount,
                    'type': parseInt(responseJson.data[17].bonus_amount) >= parseInt(responseJson.data[17].coin_amount) && parseInt(responseJson.data[17].bonus_amount) >= parseInt(responseJson.data[17].real_amount) ?
                        0 : parseInt(responseJson.data[17].coin_amount) >= parseInt(responseJson.data[17].bonus_amount) && parseInt(responseJson.data[17].coin_amount) >= parseInt(responseJson.data[17].real_amount) ?
                            1 : 2,

                }


                let tenthJoin = {
                    'amount': parseInt(responseJson.data[18].bonus_amount) >= parseInt(responseJson.data[18].coin_amount) && parseInt(responseJson.data[18].bonus_amount) >= parseInt(responseJson.data[18].real_amount) ?
                        responseJson.data[18].bonus_amount : parseInt(responseJson.data[18].coin_amount) >= parseInt(responseJson.data[18].bonus_amount) && parseInt(responseJson.data[18].coin_amount) >= parseInt(responseJson.data[18].real_amount) ?
                            responseJson.data[18].coin_amount : responseJson.data[18].real_amount,
                    'type': parseInt(responseJson.data[18].bonus_amount) >= parseInt(responseJson.data[18].coin_amount) && parseInt(responseJson.data[18].bonus_amount) >= parseInt(responseJson.data[18].real_amount) ?
                        0 : parseInt(responseJson.data[18].coin_amount) >= parseInt(responseJson.data[18].bonus_amount) && parseInt(responseJson.data[18].coin_amount) >= parseInt(responseJson.data[18].real_amount) ?
                            1 : 2,
                }

                let fiftheenJoin = {
                    'amount': parseInt(responseJson.data[19].bonus_amount) >= parseInt(responseJson.data[19].coin_amount) && parseInt(responseJson.data[19].bonus_amount) >= parseInt(responseJson.data[19].real_amount) ?
                        responseJson.data[19].bonus_amount : parseInt(responseJson.data[19].coin_amount) >= parseInt(responseJson.data[19].bonus_amount) && parseInt(responseJson.data[19].coin_amount) >= parseInt(responseJson.data[19].real_amount) ?
                            responseJson.data[19].coin_amount : responseJson.data[19].real_amount,
                    'type': parseInt(responseJson.data[19].bonus_amount) >= parseInt(responseJson.data[19].coin_amount) && parseInt(responseJson.data[19].bonus_amount) >= parseInt(responseJson.data[19].real_amount) ?
                        0 : parseInt(responseJson.data[19].coin_amount) >= parseInt(responseJson.data[19].bonus_amount) && parseInt(responseJson.data[19].coin_amount) >= parseInt(responseJson.data[19].real_amount) ?
                            1 : 2,
                }
                achvment.push(fifthJoin);
                achvment.push(tenthJoin);
                achvment.push(fiftheenJoin);
                if (fifthJoin.amount == 0 && tenthJoin.amount == 0 && fiftheenJoin.amount == 0) {
                    this.setState({
                        displayAchvments: false
                    })
                }

                this.setState({
                    achvmentData: achvment
                })
                if (this.state.totalJoined == 0 && this.state.isEnableRef == true) {
                    var tempBannerValue = responseJson.data[0];
                    if (parseInt(tempBannerValue.real_amount) >= parseInt(tempBannerValue.coin_amount) && parseInt(tempBannerValue.real_amount) >= parseInt(tempBannerValue.bonus_amount)) {
                        this.setState({
                            bannerValue: tempBannerValue.real_amount,
                            bannerValueType: 2,
                        })
                    }
                    if (parseInt(tempBannerValue.coin_amount) >= parseInt(tempBannerValue.real_amount) && parseInt(tempBannerValue.coin_amount) >= parseInt(tempBannerValue.bonus_amount)) {
                        this.setState({
                            bannerValue: tempBannerValue.coin_amount,
                            bannerValueType: 1,
                        })
                    }
                    if (parseInt(tempBannerValue.bonus_amount) >= parseInt(tempBannerValue.coin_amount) && parseInt(tempBannerValue.bonus_amount) >= parseInt(tempBannerValue.real_amount)) {
                        this.setState({
                            bannerValue: tempBannerValue.bonus_amount,
                            bannerValueType: 0,
                        })
                    }
                } else {
                    if (this.state.totalJoined < 5) {
                        this.setState({
                            bannerValue: fifthJoin.amount,
                            bannerValueType: fifthJoin.type
                        })
                    } else if (this.state.totalJoined >= 5 && this.state.totalJoined < 10) {
                        this.setState({
                            bannerValue: tenthJoin.amount,
                            bannerValueType: tenthJoin.type
                        })
                    } else if (this.state.totalJoined >= 10 && this.state.totalJoined < 15) {
                        this.setState({
                            bannerValue: fiftheenJoin.amount,
                            bannerValueType: fiftheenJoin.type
                        })
                    }

                }
                this.setState({
                    isDataLoading: false,
                })
            }
        })

    }

    // *****************************************CALL MASTER DATA*****************************************

    getRefMasterData = () => {
        let param = {}
        getMasterDataRef(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                try {
                    let tempResponse = responseJson.data;
                    this.setState({
                        isEnableRef: tempResponse.total_joined > 0 ? false : true,
                        isDisplayLowerBanner: tempResponse.total_joined != null && tempResponse.total_joined != undefined && tempResponse.total_joined != '' && tempResponse.total_joined == 0 ? true : false,
                        isReferBy: tempResponse.refer_by != null && tempResponse.refer_by != undefined && tempResponse.refer_by != '' ? true : false,
                        refMasterDataFromService: tempResponse,
                        totalBonus: tempResponse.total_bonus_cash,
                        totalCoins: tempResponse.total_coin_earned,
                        totalRealCase: tempResponse.total_real_cash,
                        totalJoined: parseInt(tempResponse.total_joined),

                    }, () => {
                        if (this.state.isEnableRef) {
                            let realCase = parseInt(tempResponse.refer_by.user_real_cash);
                            let bonus = parseInt(tempResponse.refer_by.user_bonus_cash);
                            let coins = parseInt(tempResponse.refer_by.user_coin);
                            if (realCase >= bonus && realCase >= coins) {
                                this.setState({
                                    refName: tempResponse.refer_by.user_name,
                                    refAmount: realCase,
                                    refImage: tempResponse.refer_by.image,
                                    refType: 2,
                                })
                            } else if (bonus > realCase && bonus >= coins) {
                                this.setState({
                                    refName: tempResponse.refer_by.user_name,
                                    refAmount: bonus,
                                    refImage: tempResponse.refer_by.image,
                                    refType: 0,
                                })
                            } else if (coins > bonus && coins > realCase) {
                                this.setState({
                                    refName: tempResponse.refer_by.user_name,
                                    refAmount: coins,
                                    refImage: tempResponse.refer_by.image,
                                    refType: 1,
                                })
                            }
                        } else {

                            let bannerCountNo = tempResponse.total_joined < 5 ? (5 - tempResponse.total_joined) :
                                tempResponse.total_joined >= 5 && tempResponse.total_joined < 10 ? (10 - tempResponse.total_joined) :
                                    tempResponse.total_joined >= 10 && tempResponse.total_joined < 15 ? (15 - tempResponse.total_joined) : ''

                            this.setState({
                                totalJoined: parseInt(tempResponse.total_joined),
                                totalRefRealCase: tempResponse.total_real_cash,
                                totalRefBonusCase: tempResponse.total_bonus_cash,
                                totalRefCoins: tempResponse.total_coin_earned,
                                bannerCountNo: bannerCountNo,
                            })
                        }
                    })
                    this.callRFMasterDataApi();
                } catch (e) {
                }
            }
        })
    }

    // *****************************************LOAD MORE FUNCTION CALLING*****************************************

    fetchMoreData = () => {
        if (!this.state.loadingData && this.state.hasMore) {
            this.callGetMyReferralListApi()
        }
    }

    callGetMyReferralListApi() {
        let param = {
            'page_no': this.state.userRefOffset,
            'page_size': 10
        }
        this.setState({ loadingData: true })
        getMyReferralList(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {

                if (this.state.userRefOffset === 1) {
                    this.setState({ UserReferralList: responseJson.data })
                } else {
                    this.setState({ UserReferralList: [...this.state.UserReferralList, ...responseJson.data] });
                }
                this.setState({
                    hasMore: responseJson.data.length === 10,
                    userRefOffset: this.state.userRefOffset + 1,
                    loadingData: false
                })
            }
        })
    }

    // *****************************************SHOW HIDE TABLE ROW ON CLICK *****************************************
    showDetail = (item, index) => {
        this.setState({
            isShowDetails: this.state.activeIndex == index ? !this.state.isShowDetails : true,
            activeIndex: index
        }, () => {
            // setTimeout(() => {
            //     this.setState({
                   
            //     })
            // }, 0);
        })
    }

    userEarnDetail = (item, index) => {
        let param = {
            'user_id': item.friend_id,
        }
        this.setState({ loadingData: true })
        getUserEarnMoney(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({
                    friendDeposit: responseJson.data.friends_deposit,
                    userEarnDetail: responseJson.data.referral,
                })
                this.showDetail(item, index);
            }
        })

    }

    callNativeShare(type, url, detail) {
        let data = {
            action: 'social_sharing',
            targetFunc: 'social_sharing',
            type: type,
            url: url,
            detail: detail
        }
        window.ReactNativeWebView.postMessage(JSON.stringify(data));
    }

    // ***************************************** DYNAMIC EARN BONUS AND COINS VIEW *****************************************

    UserRefferalList = (userItem, index) => {
        return <div className="list-card-wrapper" >
            <div className='data-holder'>
                <Row className={this.state.isShowDetails && this.state.activeIndex == index ? "no-wrap" : 'no-wrap'} onClick={() => { this.userEarnDetail(userItem, index) }}>
                    <Col sm={6} className='p15'>
                        <div className="left-view-holder">
                            <Label className='row-text'>{userItem.user_name} </Label>
                            <Label className='row-text-sub-title'><Moment format="MMM DD,ddd">
                                {userItem.added_date}
                            </Moment></Label>
                        </div>

                    </Col>
                    <Col sm={6} className='p15'>
                        <div className='right-view-holder'>
                            <div className='span-label icon-label-margin d-f align-t-baseline'>
                                {userItem.total_cash_earned > 0 && <><Label className={!userItem.total_cash_earned ? "vhide" : "icon-color-blue vshow" }>{Utilities.getMasterData().currency_code}</Label><Label className='amt-text-14'>{Utilities.numberWithCommas(Number(parseFloat(userItem.total_cash_earned || 0).toFixed(2)))}</Label><i className={userItem.total_bonus_earned > 0 || userItem.total_coin_earned > 0 ? "pl3 pr3" : "d-none"}>/</i></>}
                                {userItem.total_bonus_earned > 0 && <><Label className={!userItem.total_bonus_earned ? "vhide" : "icon-bonus icon-color-blue f-s-12 vshow"}></Label><Label className='amt-text-14'>{Utilities.numberWithCommas(Number(parseFloat(userItem.total_bonus_earned || 0).toFixed(2)))}</Label><i className={userItem.total_coin_earned > 0 ? "pl3 pr3" : "d-none"}>/</i></>}
                                {userItem.total_coin_earned > 0 && <><img alt='' src={Images.IC_COIN} className={userItem.total_coin_earned > 0 ? 'icon-height-is-12 mt2' : "d-none"} /><Label className={userItem.total_coin_earned > 0 ? 'amt-text-14 ml2' : "d-none"}>{Utilities.numberWithCommas(userItem.total_coin_earned)}</Label></>}

                            </div>
                            {/* <span className='pr15'><i className={this.state.isShowDetails && this.state.activeIndex == index ? "icon-arrow-up icon-color-gray f-right cursor-pointer" : "icon-arrow-down icon-color-gray f-right cursor-pointer"}></i></span> */}
                            <img alt='' className={"f-right cursor-pointer"} src={this.state.isShowDetails && this.state.activeIndex == index ? Images.THIN_ARROW_UP : Images.THIN_ARROW}/>
                        </div>

                    </Col>
                </Row>
                <Row >
                    <Col sm={12} className={this.state.isShowDetails && this.state.activeIndex == index ? 'no-wrap-show' : 'no-wrap-hide'}>
                        { OnlyCoinsFlow != 1 && <div className='pt20'>
                            <div className='ref-detail-amount d-f'>
                                <div className='view-xs center-alingment mb5 mt10'>
                                    <Label className="row-text-sub-title">{AppLabels.REFERRAL}</Label>
                                    <br></br>
                                    <span>
                                        <Label className=" f-s-12 primary-icon ">{Utilities.getMasterData().currency_code}</Label>
                                    </span>
                                    <Label className='cc'>{Utilities.numberWithCommas(Number(parseFloat(userItem.total_cash_earned || 0).toFixed(2)))}</Label>
                                </div>
                                <div className='line-light-v'></div>
                                <div className='view-l d-f'>
                                    <div className='view-s center-alingment'>
                                        <Label className="row-text-sub-title">{AppLabels.FRIEND_DEPOSIT}</Label><br></br>
                                        <span> <Label className=" f-s-12 primary-icon">{Utilities.getMasterData().currency_code}</Label></span><Label className='amt-text'>{this.state.friendDeposit != undefined ? Utilities.numberWithCommas(Number(parseFloat(this.state.friendDeposit.total_cash_earned || 0).toFixed(2))) : 0}</Label>
                                    </div>
                                    <div className='view-l d-b'>

                                        <ProgressBar now={this.state.friendDeposit != undefined ? this.state.friendDeposit.total_cash_earned : 0} className='progress-indicator' max={this.state.friendDeposit != undefined ? this.state.friendDeposit.max_earning_amount : ''} />
                                        <div>
                                            <span className='direction-l'><i className=" f-s-10 f-gray">{Utilities.getMasterData().currency_code} </i><Label className='pro-text'>0</Label></span>
                                            <span className='direction-r'> <i className=" f-s-10 f-gray">{Utilities.getMasterData().currency_code} </i><Label className='pro-text'>{this.state.friendDeposit != undefined ? Utilities.numberWithCommas(this.state.friendDeposit.max_earning_amount) : ''}</Label></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div className='mt5 mb5'>
                                <Label className='hint-text'>*{AppLabels.GET} {this.state.friendDeposit != undefined ? this.state.friendDeposit.real_amount : '0'}
                                    {this.state.friendDeposit != undefined ? this.state.friendDeposit.amount_type == 2 ? '%' : '' : ''} {AppLabels.OF_YOUR_FRIEND_DEPOSIT_MAXIMUM} {this.state.friendDeposit != undefined ? this.state.friendDeposit.amount_type == 2 ? <i className=" f-s-10 font-style-normal">{Utilities.getMasterData().currency_code}</i> : '' : ''}{this.state.friendDeposit != undefined ? Utilities.numberWithCommas(this.state.friendDeposit.max_earning_amount) : ''}</Label>
                            </div>
                        </div>}



                        {/* Email View  */}

                        {
                            this.state.userEarnDetail.map((item, index) => {
                                return (
                                    <div className={item.total_cash_earned == 0 && item.total_bonus_earned == 0 && item.total_coin_earned == 0 ? 'd-none' : ''}>
                                        <div className={item.affiliate_type == 13 || item.affiliate_type == 17 || item.affiliate_type == 5 || item.affiliate_type == 4 || item.affiliate_type == 10 || item.affiliate_type == 11 || item.affiliate_type == 12 ? 'ref-detail-amount d-f mt10 mb5' : 'd-none'}>
                                            <div className='earn-other-holder'>
                                                <div className='f-left'>
                                                    <Label className="row-text-sub-title">
                                                        {item.affiliate_type == 13 ? AppLabels.EMAIL_VERIFICATION : 
                                                            item.affiliate_type == 17 ? AppLabels.BankVerification : 
                                                                item.affiliate_type == 5 ? AppLabels.replace_PANTOID(AppLabels.PANCARD_VERIFICATION) : 
                                                                    item.affiliate_type == 4 ? AppLabels.PHONE_VERIFICATION : 
                                                                        item.affiliate_type == 10 ? AppLabels.FIRST_CONTEST : 
                                                                            item.affiliate_type == 11 ? AppLabels.FIFTH_CONTEST : 
                                                                                item.affiliate_type == 12 ? AppLabels.THENTH_CONTEST : 
                                                                                ''
                                                                                }
                                                    </Label>
                                                </div>
                                                <div className='f-right pr10'>
                                                    <span>
                                                        {
                                                            parseInt(item.total_cash_earned) >= parseInt(item.total_bonus_earned) && parseInt(item.total_cash_earned) >= parseInt(item.total_coin_earned) ? 
                                                                <i className=" icon-color-blue f-s-10 font-style-normal" >{Utilities.getMasterData().currency_code}</i> :
                                                                item.total_bonus_earned >= parseInt(item.total_cash_earned) && parseInt(item.total_bonus_earned) >= parseInt(item.total_coin_earned) ? 
                                                                    <i className="icon-bonus icon-color-blue f-s-10" /> :
                                                                        <img src={Images.IC_COIN} className='icon-height-is' />
                                                        }
                                                    </span>

                                                    <Label className='amt-text pl5'>
                                                        {parseInt(item.total_cash_earned) >= parseInt(item.total_bonus_earned) && parseInt(item.total_cash_earned) >= item.total_coin_earned ? 
                                                            Utilities.numberWithCommas(item.total_cash_earned) : 
                                                                item.total_bonus_earned >= parseInt(item.total_cash_earned) && parseInt(item.total_bonus_earned) >= parseInt(item.total_coin_earned) ? 
                                                                    Utilities.numberWithCommas(item.total_bonus_earned) : 
                                                                        Utilities.numberWithCommas(item.total_coin_earned)}
                                                    </Label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                )
                            })
                        }


                    </Col>
                </Row>
                <div className="line-light">

                </div>

            </div>
        </div>
    }


    render() {

        var settings = {
            infinite: false,
            slidesToShow: 1,
            slidesToScroll: 1,
            variableWidth: true,
            centerPadding: '20px',
            initialSlide: 0,
            responsive: [
                {
                    breakpoint: 450,
                    settings: {
                        slidesToShow: 1,
                    }
                },
                {
                    breakpoint: 414,
                    settings: {
                        slidesToShow: 1,
                    }
                }
            ]
        };
        const HeaderOption = {
            back: this.props.history.length > 1,
            notification: true,
            title: AppLabels.REFER_A_FRIEND_C,
            hideShadow: true,
            isPrimary: DARK_THEME_ENABLE ? false : true
        }

        const {
            masterData,
            hasMore,
            isLoaderShow,
            showReferalSystem,
            isDataLoading,
            showEditCodeModal,
            IsModalHide,
            IsModalShow,
            isModalHideEdit,
            isModalShowEdit,
            showBG
        } = this.state


        return (
            <MyContext.Consumer>
                {(context) => (
                    //   **********************************REFFER FRIEND NEW CODE**************************************
                    <div className={"web-container profile-section refer-friend transparent-header web-container-fixed"+ (showBG ? ' with-bg' : '')}>
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.referfriend.title}</title>
                            <meta name="description" content={MetaData.referfriend.description} />
                            <meta name="keywords" content={MetaData.referfriend.keywords}></meta>
                        </Helmet>
                        {!this.props.hideHeader &&
                            <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        }
                        {!isDataLoading && <React.Fragment>
                            <div>
                                <div className="webcontainer-inner">
                                    <div className="page-header">
                                        <div className='overlay-background'>

                                        </div>
                                        {this.state.totalJoined > 0 && this.state.totalJoined < 15 ?
                                            <div>
                                                <div className="banner">
                                                    <div className={this.state.bannerValue == 0 ? 'd-none' : ''}>
                                                        <div>
                                                            <img src={Images.SPECIAL_OFFER} className='special-text-spot'/>
                                                        </div>
                                                        <div className='d-f j-c-c'>
                                                            <Label className='banner-cap-text mt2'>{AppLabels.GET}</Label>&nbsp;&nbsp;
                                                            <i className={this.state.bannerValueType == 0 ? "icon-bonus color-dark index-top bonus-icon-style bonus-icon-style-b" : this.state.bannerValueType == 2 ? "color-dark index-top bonus-icon-style-rupee" : ''}>{this.state.bannerValueType == 2 ? Utilities.getMasterData().currency_code : ''}</i> {this.state.bannerValueType == 1 ? <img src={Images.IC_COIN} className='icon-height-is-l' /> : ''}<Label className='banner-value-label index-top pl2'>{this.state.bannerValue}</Label>
                                                            &nbsp;
                                                        <Label className='banner-cap-text  mt2'>{this.state.bannerValueType == 0 ? AppLabels.BONUS_CASH_LOWER : this.state.bannerValueType == 1 ? AppLabels.COINS : this.state.bannerValueType == 2 ? AppLabels.REAL_CASH_LOWER : ''}</Label>
                                                        </div>
                                                        <Label className='banner-cap-text-s'>{AppLabels.ON}&nbsp;{this.state.bannerCountNo}&nbsp;{AppLabels.MORE_REFERRALS}</Label>
                                                        {/* <Label className='info-label extra-s-text' onClick={() => { this.openRefSystem() }}><i>{AppLabels.HOW_IT_WORKS}</i></Label> */}
                                                        <img src={Images.INFO_ICON} alt="" className='info-label' onClick={() => { this.openRefSystem() }} />
                                                    </div>

                                                </div>
                                            </div>
                                            :
                                            <div>
                                                <div className="banner">

                                                    <div className={this.state.bannerValue == 0 ? 'mt15' : 'd-none'}>
                                                        <div className='d-f j-c-c'>
                                                            <Label className='banner-cap-text  mt2'>{AppLabels.IT_PAY_TO_HAVE}</Label>
                                                        </div> <Label className='banner-cap-text-s'>{AppLabels.YOU_WILL_EARN_ON_EACH_NEW_SIGN_UP}</Label>
                                                        {/* <Label className='info-label extra-s-text' onClick={() => { this.openRefSystem() }}><i>{AppLabels.HOW_IT_WORKS}</i></Label> */}
                                                        <img src={Images.INFO_ICON} alt="" className='info-label' onClick={() => { this.openRefSystem() }} />
                                                    </div>
                                                </div>
                                            </div>


                                        }



                                    </div>
                                    <div className={!this.state.isDisplayLowerBanner && !this.state.isReferBy ? 'enable-true' : "referring-you-section xhide"}>
                                        <img src={this.state.refImage == null ? Images.DEFAULT_USER : Utilities.getThumbURL(this.state.refImage)} alt="" />
                                        <h2>
                                            <span className="referby-name to-upper-case">{this.state.refName != "" ? this.state.refName : AppLabels.Your_Friend}</span>&nbsp;
                                        {AppLabels.REFER_YOU_AND_EARNED}
                                        </h2>
                                        <div className='ml2 margin-t-2-'>
                                            <Label className={this.state.refType == 0 ? "icon-bonus lower-banner-text-" : 'd-none'} />
                                            {
                                                this.state.refType == 1 ? <img alt='' src={Images.IC_COIN} className='icon-height-is icon-refer-lower-banner m-r-xs' /> : ''
                                            }
                                            {
                                                this.state.refType == 2 ? <Label className='lower-banner-text-'>{Utilities.getMasterData().currency_code}</Label> : ''
                                            }
                                            <Label className='lower-banner-text-'>{this.state.refAmount}</Label>
                                        </div>



                                        {/* <span> <i className=></i>{this.state.refType == 1 ?  : ''}&nbsp; </span> */}
                                    </div>
                                    <div className={this.state.isEnableRef ? "" : 'enable-true'}>
                                        <ShareReferal {...this.props} ShowReferralModal={this.openRefSystem} showEditReferralPage={this.showEditReferralPage} />
                                    </div>


                                </div>
                            </div>
                            {/* ************************************WHEN USER EARN BONUS AND CASH *************************** */}

                            <div className={this.state.isEnableRef ? 'enable-true' : ""}>
                                <div className="my-referral-section">
                                    <div className="my-referal-head score-board-shadow-m">
                                        <Label className='my-refer-text'>{AppLabels.MY_REFERRALS}</Label>
                                        <Label className='my-refer-text pull-right pt5'>{this.state.totalJoined} {AppLabels.JOINED}</Label>

                                    </div>
                                    <div className="display-table display-inline-f">
                                        <div className={this.state.totalRealCase > 0 ? 'score-board score-border-l mr5 score-board-shadow' : "d-none"}>
                                            <div className='d-f j-c-c mt5'>
                                                <div>
                                                    <i className="icon-color-blue font-w-900">{Utilities.getMasterData().currency_code}</i>
                                                </div>

                                                <Label className='score-amount-text mt5'>{Utilities.numberWithCommas(Number(parseFloat(this.state.totalRealCase || 0).toFixed(2)))}</Label>
                                            </div>
                                            <div>
                                                <Label className='score-amount-text-sub-title m-l-5-m'>{AppLabels.REAL_CASH}</Label>
                                            </div>
                                        </div>
                                        <div className={this.state.totalBonus > 0 ? 'score-board score-border-m ml5 mr5 score-board-shadow-m' : 'd-none'}>
                                            <div className='d-f j-c-c mt5'>
                                                <div>
                                                    <i className="icon-bonus icon-color-blue font-w-900"></i>
                                                </div>

                                                <Label className='score-amount-text mt5'>{Utilities.numberWithCommas(Number(parseFloat(this.state.totalBonus || 0).toFixed(2)))}</Label>
                                            </div>
                                            <div>
                                                <Label className='score-amount-text-sub-title m-l-5-m'> {AppLabels.BONUS_CASH}</Label>
                                            </div>
                                        </div>
                                        <div className={this.state.totalCoins > 0 ? "score-board score-border-r ml5 score-board-shadow-r" : 'd-none'}>
                                            <div className='d-f j-c-c mt5'>
                                                <div>
                                                    {/* <i className="icon-coins icon-color-blue"></i>
                                                 */}
                                                    <img src={Images.IC_COIN} className='icon-height-is' />
                                                </div>

                                                <Label className='score-amount-text mt5'>{this.state.totalCoins}</Label>
                                            </div>
                                            <div>
                                                <Label className='score-amount-text-sub-title m-l-5-m'> {AppLabels.COINS}</Label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div className="pt10">

                                    <Tabs defaultActiveKey={1}
                                        id="uncontrolled-tab-example"
                                        className="custom-nav-tabs-profile tabs-two m-t-10 white-back referal-tab-menu"
                                        activeKey={this.state.selectedTab}
                                        onSelect={key => this.setState({ selectedTab: key }, () => {
                                            if (key === 2 && this.state.UserReferralList.length === 0) {
                                                this.callGetMyReferralListApi()
                                            }
                                        })}>

                                        <Tab eventKey={1} title={AppLabels.SHARE}>
                                            <div className="inner-tab-content">
                                                <ShareReferal from={1} {...this.props} ShowReferralModal={this.openRefSystem} showEditReferralPage={this.showEditReferralPage} />
                                            </div>
                                        </Tab>
                                        <Tab eventKey={2} title={AppLabels.REFERRAL} className='pt0'>
                                            <InfiniteScroll
                                                dataLength={this.state.UserReferralList.length}
                                                className='mb-120'
                                                hasMore={false}
                                                scrollableTarget='test'
                                                loader={
                                                    isLoaderShow == true &&
                                                    <h4 className='table-loader'>{AppLabels.LOADING_MSG}</h4>
                                                }>
                                                <ul className="p-0" id="test">
                                                    <div className='heading-bar'>
                                                        <Row className='no-wrap'>
                                                            <Col sm={6} className="pt10 pb10 pl15">
                                                                <div className="d-f">
                                                                    <Label className='pro-text-t'>{AppLabels.USER_NAME}</Label>
                                                                </div>
                                                            </Col>
                                                            <Col sm={6} className="pt10 pb10">
                                                                <div className="d-f j-c-c t-a-c">
                                                                    <Label className='pro-text-t'>{AppLabels.TOTAL_EARNING}</Label>
                                                                </div>
                                                            </Col>
                                                        </Row>

                                                    </div>
                                                    {
                                                        this.state.UserReferralList.length > 0 && this.state.UserReferralList.map((item, index) => {
                                                            return (this.UserRefferalList(item, index));
                                                        })
                                                    }
                                                    {
                                                        this.state.hasMore && <Row>
                                                            <Col sm={12} className='d-f j-c-c mt10'>
                                                                <div className='load-more-view' onClick={this.fetchMoreData}>
                                                                    <Label className='load-more-text'>{AppLabels.LOAD_MORE_RESULTS}</Label>
                                                                </div>
                                                            </Col>
                                                        </Row>
                                                    }
                                                </ul>
                                            </InfiniteScroll>
                                            {
                                                this.state.UserReferralList.length == 0 && !this.state.loadingData &&
                                                <NoDataView
                                                    BG_IMAGE={Images.no_data_bg_image}
                                                    // CENTER_IMAGE={Images.BRAND_LOGO_FULL}
                                                    CENTER_IMAGE={Images.NO_DATA_VIEW}
                                                    MESSAGE_1={AppLabels.NOT_REFER}
                                                    MESSAGE_2={''}
                                                    BUTTON_TEXT={AppLabels.SHARE_NOW}
                                                    onClick={this.goToShare}
                                                />
                                            }
                                            <div className={this.state.displayAchvments ? 'achvment-slider' : 'd-none'}>
                                                <Label className='achievment-text'>{AppLabels.ACHIEVEMENTS}</Label>
                                                <Suspense fallback={<div />} ><ReactSlickSlider settings = {settings}>

                                                    {
                                                        _Map(this.state.achvmentData, (item, index) => {
                                                            return (

                                                                <div className={item.amount > 0 ? 'inner-view' : 'd-none'}>
                                                                    <div className='index-top'>
                                                                        {
                                                                            index == 0 && this.state.totalJoined < 5 ?
                                                                            <span className="lock-sec">
                                                                                <i className="icon-lock-ic"></i>
                                                                            </span>
                                                                            :
                                                                            index == 1 && this.state.totalJoined < 10 && this.state.totalJoined < 15 ?
                                                                            <span className="lock-sec">
                                                                                <i className="icon-lock-ic"></i>
                                                                            </span>
                                                                            :
                                                                            index == 2 && this.state.totalJoined <= 15 ?
                                                                            <span className="lock-sec">
                                                                                <i className="icon-lock-ic"></i>
                                                                            </span>
                                                                            :
                                                                            <img src={Images.TICK_IC} className='img-height' />
                                                                        }
                                                                        {/* <img src={index == 0 && this.state.totalJoined < 5 ? Images.REFER_LOCK : index == 1 && this.state.totalJoined < 10 && this.state.totalJoined < 15 ? Images.REFER_LOCK : index == 2 && this.state.totalJoined <= 15 ? Images.REFER_LOCK : Images.TICK_IC} className='img-height' /> */}
                                                                    </div>

                                                                    <div className='ml10 mt5'>
                                                                        {item.type == 0 ? <i className="icon-bonus is-blue font-s-15 line-h-14"></i> : item.type == 2 ? <i className=" is-blue font-s-12 line-h-16 font-style-normal" >{Utilities.getMasterData().currency_code}</i> : item.type == 1 ? <img src={Images.IC_COIN} className='icon-height-is-18 d-inline' /> : ''}<Label className='amt-text-blue-slider'> {item.amount}
                                                                        </Label> &nbsp;
                                                                         <Label className='amt-text-slider'>{item.type == 0 ? AppLabels.BONUS_CASH : item.type == 1 ? AppLabels.COINS : AppLabels.REAL_CASH}</Label><br></br>
                                                                        <Label className='row-text-sub-title'>{index == 0 ? AppLabels.ERNED_FIFITH_REF : index == 1 ? AppLabels.ERNED_TEHTH_REF : AppLabels.ERNED_FIFITHEEN_REF}</Label>
                                                                    </div>
                                                                    <div className={index == 0 && this.state.totalJoined < 5 ? "disable-bg" : index == 1 && this.state.totalJoined < 10 && this.state.totalJoined < 15 ? "disable-bg" : index == 2 && this.state.totalJoined <= 15 ? "disable-bg" : Images.TICK_IC}>
                                                                    </div>
                                                                </div>
                                                            )
                                                        })
                                                    }

                                                </ReactSlickSlider></Suspense>
                                            </div>
                                        </Tab>
                                    </Tabs>
                                </div>
                                {showReferalSystem &&
                                    <ReferralSystem IsModalShow={showReferalSystem} IsModalHide={this.HideReferralSystemModal} />
                                }
                            </div>
                        </React.Fragment>}
                        {
                            isDataLoading && 
                            <SkeletonTheme color={DARK_THEME_ENABLE ? "#161920" : null} highlightColor={DARK_THEME_ENABLE ? "#0E2739" : null}>
                                <div className="contest-list shadow-none shimmer-border">
                                    <Skeleton height={160} width={"100%"} />
                                    <div className="top-next-sec">
                                        <Skeleton height={40} width={100} className='skt-right' />
                                        <Skeleton height={40} width={100} className='skt-left' />
                                    </div>
                                    <div className='p'  >

                                        <Skeleton height={8} />
                                        <Skeleton height={6} />

                                    </div>
                                    <div className='j-c-c d-f'>
                                        <div className='mr20'>
                                            <Skeleton height={50} width={50} />
                                        </div>
                                        <div className='mr20'>
                                            <Skeleton height={50} width={50} />
                                        </div>
                                        <div>
                                            <Skeleton height={50} width={50} />
                                        </div>
                                    </div>
                                    <div className='p-t-20-p j-c-c d-f'>
                                        <div >
                                            <Skeleton height={40} width={100} />
                                        </div>

                                    </div>
                                </div>
                            </SkeletonTheme>
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}