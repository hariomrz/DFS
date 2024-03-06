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
import { ReferralSystem,HowThisWorkModal} from "../Modals";
import Skeleton,{SkeletonTheme} from 'react-loading-skeleton';
import { DARK_THEME_ENABLE, OnlyCoinsFlow } from "../helper/Constants";
import {BecomeAffiliateNew, ThankuAffiliateModal } from "../Component/BecomeAffiliate";
const ReactSlickSlider = lazy(()=>import('../Component/CustomComponent/ReactSlickSlider'));

export default class ReferFriendStandard extends React.Component {
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
            isShowDetails: false,
            activeIndex: 0,
            profileDetail: WSManager.getProfile(),
            isEnableRef: true,
            isDisplayLowerBanner: false,
            isReferBy: false,
            refName: '',
            refAmount: '',
            refType: '',
            totalJoined: 0,
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
            isDataLoading: false,
            userEarnDetail: [],
            displayAchvments: true,
            BannerList: ['1', '2', '3'],
            showBG: false,
            showHtwModal:false,
            showBecomeAM: false,
            showThanku: false,
            is_affiliate: WSManager.getProfile().is_affiliate

        }
    }
    onScrollList = (event) => {
        let scrollOffset = window.pageYOffset;
        if(scrollOffset>0){
            this.setState({
                showBG: true
            })
        }
        else{
            this.setState({
                showBG: false
            })
        }
    }

    HideEditCodeModal = () => {
        this.setState({
            showEditCodeModal: false
        })
    }

    componentDidMount() {
        window.addEventListener('scroll', this.onScrollList);
        let fromHTWM = this.props && this.props.location && this.props.location.state && this.props.location.state.fromHTWM ? this.props.location.state.fromHTWM : false;
        if (ls.get("isShowPopup") != 1 && !fromHTWM) {
           this.htwModalShow();
        }
    }

    UNSAFE_componentWillMount() {
        this.getRefMasterData();
    }

    showEditReferralPage=()=>{
        let passingData = this.state.masterData[16]
        this.props.history.push('/edit-referral-code', passingData);
    }


    openRefSystem = (e) => {
        this.setState({
            showHtwModal : true
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
    callRFMasterDataApi = (showHTPModal) => {
        let param = {}
        this.setState({
            isDataLoading: true
        })
        getReferralMasterData(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({
                    masterData: responseJson.data,
                },()=>{
                    if(showHTPModal){
                        this.setState({
                            showHtwModal: true
                        })
                    }
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
                    let totUsrJnd = tempResponse.total_joined || '0'
                    this.setState({
                        isEnableRef: tempResponse.total_joined > 0 ? false : true,
                        isDisplayLowerBanner: tempResponse.total_joined != null && tempResponse.total_joined != undefined && tempResponse.total_joined != '' && tempResponse.total_joined == 0 ? true : false,
                        isReferBy: tempResponse.refer_by != null && tempResponse.refer_by != undefined && tempResponse.refer_by != '' ? true : false,
                        refMasterDataFromService: tempResponse,
                        totalBonus: tempResponse.total_bonus_cash,
                        totalCoins: tempResponse.total_coin_earned,
                        totalRealCase: tempResponse.total_real_cash,
                        totalJoined: parseInt(totUsrJnd),

                    }, () => {
                        // if (this.state.isEnableRef) {
                        //     let realCase = parseInt(tempResponse.refer_by.user_real_cash);
                        //     let bonus = parseInt(tempResponse.refer_by.user_bonus_cash);
                        //     let coins = parseInt(tempResponse.refer_by.user_coin);
                        //     if (realCase >= bonus && realCase >= coins) {
                        //         this.setState({
                        //             refName: tempResponse.refer_by.user_name,
                        //             refAmount: realCase,
                        //             refImage: tempResponse.refer_by.image,
                        //             refType: 2,
                        //         })
                        //     } else if (bonus > realCase && bonus >= coins) {
                        //         this.setState({
                        //             refName: tempResponse.refer_by.user_name,
                        //             refAmount: bonus,
                        //             refImage: tempResponse.refer_by.image,
                        //             refType: 0,
                        //         })
                        //     } else if (coins > bonus && coins > realCase) {
                        //         this.setState({
                        //             refName: tempResponse.refer_by.user_name,
                        //             refAmount: coins,
                        //             refImage: tempResponse.refer_by.image,
                        //             refType: 1,
                        //         })
                        //     }
                        // } else {

                            let bannerCountNo = totUsrJnd < 5 ? (5 - totUsrJnd) :
                                totUsrJnd >= 5 && totUsrJnd < 10 ? (10 - totUsrJnd) :
                                    totUsrJnd >= 10 && totUsrJnd < 15 ? (15 - totUsrJnd) : ''

                            this.setState({
                                totalJoined: parseInt(totUsrJnd),
                                totalRefRealCase: tempResponse.total_real_cash,
                                totalRefBonusCase: tempResponse.total_bonus_cash,
                                totalRefCoins: tempResponse.total_coin_earned,
                                bannerCountNo: bannerCountNo,
                            })
                        // }
                    })
                    if(this.state.masterData == []){
                        this.callRFMasterDataApi();
                    }
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
    htwModalHide = () => {
        this.setState({
            showHtwModal: false
        });
    }
    htwModalShow = () => {
        this.callRFMasterDataApi(true)
        // this.setState({
        //     showHtwModal: true
        // })
    }

    becomeAffiliate = () => {
        if (this.state.is_affiliate == 0 || this.state.is_affiliate == 4) {
            this.setState({
                showBecomeAM: true
            })
        } 
        else if(this.state.is_affiliate == 2) {
            this.showThanku()
        }
        else {
            this.props.history.push('/affiliate-program');
        }
    }

    hideBecomeAM = (value) => {
        this.setState({
            showBecomeAM: false, is_affiliate: value ? value : this.state.is_affiliate
        })
        if(value && value == 2){
            this.showThanku()
        }
    }
    showThanku=()=>{
        this.setState({
            showThanku: true,
        })
        
    }
    hideThanku=()=>{
        this.setState({
            showThanku: false,
        })
        
    }

    goToEarnMoreScr=()=>{
        this.props.history.push('/earn-coins')
        this.hideThanku()
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
            isPrimary: DARK_THEME_ENABLE ? false : true,
            info: true,
            infoAction: this.htwModalShow
        }

        const {
            masterData,
            hasMore,
            isLoaderShow,
            isDataLoading,
            showEditCodeModal,
            IsModalHide,
            IsModalShow,
            isModalHideEdit,
            isModalShowEdit,
            showBG,
            showHtwModal
        } = this.state


        return (
            <MyContext.Consumer>
                {(context) => (
                    //   **********************************REFFER FRIEND NEW CODE**************************************
                    <div className={"web-container profile-section refer-friend transparent-header web-container-fixed refer-new" + (showBG ? ' with-bg' : '')}>
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
                                <div style={{marginTop:0}} className="webcontainer-inner">
                                    {/* <div className={"page-header" + (!this.state.isEnableRef ? ' new-page-refer' :'') }> */}
                                    <div className="page-header new-page-refer">
                                        <div className='overlay-background'>

                                        </div>
                                        {/* {this.state.totalJoined > 0 && this.state.totalJoined < 15 ? */}
                                            <div>
                                                <div className="banner new-ref">
                                                    <div className={this.state.bannerValue == 0 ? 'xd-none' : ''}>
                                                        <div>
                                                            <img src={Images.REFER_LOGO} className='img-height' />
                                                        </div>
                                                        {/* <div>
                                                            <img src={Images.SPECIAL_OFFER} className='special-text-spot'/>
                                                        </div> */}
                                                        <div style={{marginTop:30}} className='d-f j-c-c'>
                                                            <Label className='banner-cap-text mt2'>{AppLabels.GET}</Label>&nbsp;&nbsp;
                                                            <i className={this.state.bannerValueType == 0 ? "icon-bonus color-dark-new-ref index-top bonus-icon-style bonus-icon-style-b  " : this.state.bannerValueType == 2 ? "color-dark-new-ref index-top bonus-icon-style-rupee" : ''}
                                                            style={{fontSize: "20px",marginTop:"6px"}}
                                                            >{this.state.bannerValueType == 2 ? Utilities.getMasterData().currency_code : ''}</i> {this.state.bannerValueType == 1 ? <img src={Images.IC_COIN} className='icon-height-is-l' /> : ''}<Label className='banner-value-label new-ref index-top pl2'>{this.state.bannerValue}</Label>
                                                            &nbsp;
                                                        <Label className='banner-cap-text  mt2'>{this.state.bannerValueType == 0 ? AppLabels.BONUS_CASH_LOWER : this.state.bannerValueType == 1 ? AppLabels.COINS : this.state.bannerValueType == 2 ? AppLabels.REAL_CASH_LOWER : ''}</Label>
                                                        </div>
                                                        <Label className='banner-cap-text-s'>{AppLabels.ON}&nbsp;{this.state.bannerCountNo}&nbsp;{AppLabels.MORE_REFERRALS}</Label>
                                                        {/* <img src={Images.INFO_ICON} alt="" className='info-label' onClick={() => { this.openRefSystem() }} /> */}
                                                        
                                                    </div>

                                                </div>
                                            </div>
                                        {/* //     :
                                        //     <div>
                                        //         <div className="banner">

                                        //             <div className={this.state.bannerValue == 0 ? 'mt15' : 'd-none'}>
                                        //                 <div className='d-f j-c-c'>
                                        //                     <Label className='banner-cap-text  mt2'>{AppLabels.IT_PAY_TO_HAVE}</Label>
                                        //                 </div> <Label className='banner-cap-text-s'>{AppLabels.YOU_WILL_EARN_ON_EACH_NEW_SIGN_UP}</Label>
                                        //             </div>
                                        //         </div>
                                        //     </div>


                                        // } */}



                                    </div>
                                    {/* when your referral is 0 and your friend earned because you joined with your friend's referral code */}
                                    <div className={"hide " + (!this.state.isDisplayLowerBanner && !this.state.isReferBy ? 'enable-true' : "referring-you-section xhide")}>
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
                                    {/* <div className={this.state.isEnableRef ? "" : 'enable-true'}>
                                        <ShareReferal isEnableRef={this.state.isEnableRef} {...this.props} ShowReferralModal={this.openRefSystem} showEditReferralPage={this.showEditReferralPage} becomeAffiliate={this.becomeAffiliate} />
                                    </div> */}


                                </div>
                            </div>
                            {/* ************************************WHEN USER EARN BONUS AND CASH *************************** */}

                            {/* <div className={this.state.isEnableRef ? 'enable-true' : ""}> */}
                            <div className="">
                                <div className="my-referral-section new-ref">
                                    <div className="my-referal-head new-ref-head">
                                        <Label className='my-refer-text'>{" Friends " +AppLabels.JOINED} {'- '}{this.state.totalJoined || 0}</Label>
                                    </div>
                                    <div style={{marginTop:10}} className="display-table display-inline-f">
                                        <div className='score-board score-border-l mr5 score-board-shadow'>
                                            <div className='d-f j-c-c mt5'>
                                                <div className='main-cont'>
                                                    <div className='oval'>
                                                        <i className="icon-color-blue font-w-900 prize-tyype">{Utilities.getMasterData().currency_code}</i>

                                                    </div>
                                                    <div className='score-amount-text mt10'>{Utilities.numberWithCommas(Number(parseFloat(this.state.totalRealCase || 0).toFixed(2)))}</div>

                                                </div>


                                            </div>

                                        </div>
                                        <div className="score-board score-border-r ml5 score-board-shadow-r">
                                            <div className='d-f j-c-c mt5'>
                                            <div className='main-cont'>
                                                    <div className='oval'>
                                                        <img src={Images.IC_COIN} className='icon-height-is prize-tyype' />
                                                    </div>

                                                    <div className='score-amount-text mt10'>{this.state.totalCoins}</div>
                                                </div>
                                            </div>
                                          
                                        </div>
                                        <div className='score-board score-border-m ml5 mr5 score-board-shadow-m'>
                                            <div className='d-f j-c-c mt5'>
                                            <div className='main-cont'>
                                                    <div className='oval '>
                                                        <i className="icon-bonus icon-color-blue font-w-900 prize-tyype"></i>

                                                    </div>
                                                    <div className='score-amount-text mt10'>{Utilities.numberWithCommas(Number(parseFloat(this.state.totalBonus || 0).toFixed(2)))}</div>

                                                </div>


                                            </div>

                                        </div>
                                       
                                    </div>
                                </div>
                                <div className="pt5">

                                    <Tabs style={{marginLeft:25,marginRight:25}} defaultActiveKey={1}
                                        id="uncontrolled-tab-example"
                                        className="custom-nav-tabs-profile tabs-two m-t-10 white-back referal-tab-menu"
                                        activeKey={this.state.selectedTab}
                                        onSelect={key => this.setState({ selectedTab: key }, () => {
                                            if (key === 2 && this.state.UserReferralList.length === 0 && this.state.totalJoined > 0) {
                                                console.log('first')
                                                this.callGetMyReferralListApi()
                                            }
                                        })}>

                                        <Tab eventKey={1} title={<div className={"referal-tab-div share-tab-contesnt" + (this.state.selectedTab == 1 ? ' active': '')}> <div><i className={"icon-share icon-home-c" +  (this.state.selectedTab == 1 ? ' active': '')} /></div>
                                            <div className={"tab-label"+ (this.state.selectedTab == 1 ? ' active': '')}>{AppLabels.SHARE}</div>
                                        </div>}>
                                            <div className="inner-tab-content">
                                                <ShareReferal isEnableRef={this.state.isEnableRef} from={1} {...this.props} ShowReferralModal={this.openRefSystem} showEditReferralPage={this.showEditReferralPage} becomeAffiliate={this.becomeAffiliate} />
                                            </div>
                                        </Tab>
                                        <Tab eventKey={2} title={<div className={"referal-tab-div referal-tab-contesnt" + (this.state.selectedTab == 2 ? ' active': '')}> <div><i className={"icon-admin icon-home-c"  + (this.state.selectedTab == 2 ? ' active': '')} /></div>
                                            <div className={"tab-label" + (this.state.selectedTab == 2 ? ' active': '')}>{AppLabels.REFERRAL}</div>
                                        </div>} className='pt0'>
                                            <InfiniteScroll
                                                dataLength={this.state.UserReferralList.length}
                                                className={this.state.isEnableRef ? '' : 'mb-120'}
                                                hasMore={false}
                                                scrollableTarget='test'
                                                loader={
                                                    isLoaderShow == true &&
                                                    <h4 className='table-loader'>{AppLabels.LOADING_MSG}</h4>
                                                }>
                                                <ul className="p-0" id="test">
                                                    {
                                                        !this.state.isEnableRef &&
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
                                                    }
                                                    {
                                                        this.state.UserReferralList.length > 0 && this.state.UserReferralList.map((item, index) => {
                                                            return (this.UserRefferalList(item, index));
                                                        })
                                                    }
                                                    {
                                                        this.state.hasMore && <Row>
                                                            <Col sm={12} className='d-f j-c-c mt10'>
                                                                <div className='load-more-view' onClick={this.fetchMoreData}>
                                                                    <Label className='load-more-text'>{AppLabels.SHOW_MORE}</Label>
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
                            
                            </div>
                        </React.Fragment>}
                        {
                            showHtwModal &&
                            <Suspense fallback={<div />} >
                                <HowThisWorkModal
                                    {...this.props}
                                    isFromRefer={true}
                                    mShow={this.htwModalShow}
                                    mHide={this.htwModalHide}
                                    masterData={this.state.masterData}
                                />
                            </Suspense>
                        }
                        {
                            this.state.showBecomeAM && <BecomeAffiliateNew {...this.props} preData={{
                                mShow: this.state.showBecomeAM,
                                mHide: this.hideBecomeAM
                            }} />
                        }
                        {
                            this.state.showThanku && <ThankuAffiliateModal {...this.props} preData={{
                                mShow: this.showThanku,
                                mHide: this.hideThanku,
                                goToEarnMoreScr: this.goToEarnMoreScr
                            }} />
                        }
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