import React from 'react';
import { Row, Col } from "react-bootstrap";
import { MyContext } from '../../InitialSetup/MyProvider';
import { getUserProfile, playingExperience, getUserXPCard, getStockPlayingExperience } from '../../WSHelper/WSCallings';
import { _Map, Utilities, isFooterTab } from '../../Utilities/Utilities';
import { UserProfileHeader, MomentDateComponent, DataCountBlock } from '../CustomComponent';
import { XPProfileCard } from '../CustomComponent/../XPModule';
import { EditUserNameModal, EditMobileModal, EditEmailModal } from "../../Modals";
import WSManager from "../../WSHelper/WSManager";
import Images from '../../components/images';
import CustomHeader from '../../components/CustomHeader';
import { DARK_THEME_ENABLE } from "../../helper/Constants";
import ls from 'local-storage';
import * as WSC from "../../WSHelper/WSConstants";
import * as Constants from "../../helper/Constants";
import * as AppLabels from "../../helper/AppLabels";
import ShareProfileModal from "../XPModule/ShareProfileModal";

var expData = null;

export default class Profile extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            showEditUserNameModal: false,
            showEditMobileModal: false,
            showEditEmailModal: false,
            code: Constants.DEFAULT_COUNTRY_CODE,
            profileDetail: ls.get('profile') || '',
            verificationSteps: '',
            playingExpdata: '',
            accVerified: this.isAccountVerified(WSManager.getProfile()),
            isXPEnable: Utilities.getMasterData().a_xp_point == 1 ? true : false,
            userXPDetail: '',
            profileUrl: '',
            showShareProfileModal: false,
            sportsList: Utilities.getMasterData().sports_hub
        }
    }

    isAccountVerified = (data) => {
        var isVerified = data.email_verified == "1" && data.phone_verfied == "1" ? true : false
        if (Utilities.getMasterData().a_bank_flow == 1) {
            isVerified = isVerified && data.is_bank_verified == "1"
        }
        if (Utilities.getMasterData().a_pan_flow == 1) {
            isVerified = isVerified && data.pan_verified == "1"
        }
        if (Utilities.getMasterData().a_aadhar == "1" && WSManager.loggedIn()) {
            isVerified = isVerified && data.aadhar_status == "1"
        }


        return isVerified;
    }
    gtmEventFire = (data) => {
        Utilities.gtmEventFire('account_verified', {
            pan_verified: data.pan_verified == "1" ? 'Yes' : 'No',
            bank_verified: data.is_bank_verified == "1" ? 'Yes' : 'No',
            email_verified: data.email_verified == "1" ? 'Yes' : 'No',
            phone_verified: data.phone_verfied == "1" ? 'Yes' : 'No',
        })
    }

    componentDidMount() {
        Utilities.setScreenName('myprofile')
        Utilities.handleAppBackManage('my-profile')
        if (expData && Utilities.minuteDiffValue(expData) < 1) {
            this.parseExpData(expData.data)
        }
        if (this.state.profileDetail) {
            this.initVerificationSteps()
        }
        this.callProfileDetail();
        if (this.state.isXPEnable) {
            this.callUserXPDetail();
        }
    }
    /**
    * @description method to display profile detail of user
    */
    callProfileDetail() {
        getUserProfile().then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.parseProfileData(responseJson.data);
            }
        })
    }
    parseProfileData(data) {
        ls.set('profile', data)
        this.gtmEventFire(data)
        this.setState({
            profileDetail: data,
            accVerified: this.isAccountVerified(data),
            profileUrl: WSC.baseURL + 'my-profile/' + data.user_id
        }, () => {
            this.initVerificationSteps();
            this.showPlayingExperience();
        })
    }

    showPlayingExperience() {
        if (expData && Utilities.minuteDiffValue(expData) < 1) {
            this.parseExpData(expData.data)
        } else {
            let tmpAry = []
            for(var item of this.state.sportsList){
                tmpAry.push(item.game_key)
            }
            let StockPE = tmpAry && (
                    (tmpAry.length == 1 && tmpAry.includes(Constants.GameType.StockFantasyEquity)) || 
                    (tmpAry.length == 1 && tmpAry.includes(Constants.GameType.StockFantasy)) || 
                    (tmpAry.length == 1 && tmpAry.includes('allow_stock_predict')) || 
                    (tmpAry.length == 2 && tmpAry.includes(Constants.GameType.StockFantasy && Constants.GameType.StockFantasyEquity))  
                    || (tmpAry.length == 2 && tmpAry.includes(Constants.GameType.StockFantasy && 'allow_stock_predict')) || 
                    (tmpAry.length == 2 && tmpAry.includes('allow_stock_predict' && Constants.GameType.StockFantasyEquity)) || 
                    (tmpAry.length == 3 && tmpAry.includes(Constants.GameType.StockFantasy && Constants.GameType.StockFantasyEquity && 'allow_stock_predict'))
                    ) ? true : false;
            let apiCall = StockPE ? getStockPlayingExperience : playingExperience
            apiCall().then((responseJson) => {
                if (responseJson && responseJson.response_code == WSC.successCode) {
                    expData = { data: responseJson.data, date: Date.now() };
                    this.parseExpData(responseJson.data, StockPE)
                }
            })
        }
    }
    parseExpData(playingExpdata, StockPE) {
        let {int_version} = Utilities.getMasterData()
        if(StockPE){
            this.setState({
                playingExpdata: [
                    {
                        'icon': 'icon-badge',
                        'count': playingExpdata.won_contest,
                        'count_for': AppLabels.CONTEST_WON
                    },
                    {
                        'icon': 'icon-tickets',
                        'count': playingExpdata.total_contest,
                        'count_for': AppLabels.TOTAL_CONTESTS
                    },
                    {
                        'icon': 'icon-trophy2-ic',
                        'count': playingExpdata.winning_amount,
                        'count_for': AppLabels.TOTAL_EARNING
                    },
                    {
                        'icon': 'icon-vs-ic',
                        'count': playingExpdata.total_referral,
                        'count_for': AppLabels.TOTAL_REFERAL
                    }
                ],
            })
        }
        else{
            this.setState({
                playingExpdata: [
                    {
                        'icon': 'icon-badge',
                        'count': playingExpdata.won_contest,
                        'count_for': AppLabels.CONTEST_WON
                    },
                    {
                        'icon': 'icon-tickets',
                        'count': playingExpdata.total_contest,
                        'count_for': AppLabels.TOTAL_CONTESTS
                    },
                    {
                        'icon': 'icon-vs-ic',
                        'count': playingExpdata.match_counts,
                        'count_for': int_version == "1" ? AppLabels.GAMES :  AppLabels.MATCHES
                    },
                    {
                        'icon': 'icon-trophy2-ic',
                        'count': playingExpdata.league_counts,
                        'count_for': AppLabels.SERIES
                    }
                ],
            })
        }
    }

    initVerificationSteps() {



        let mVerificationSteps = [];
        let AadharStatus = this.state.profileDetail ? this.state.profileDetail : '';
        let AadharNo = (this.state.profileDetail && this.state.profileDetail.aadhar_detail) ? this.state.profileDetail.aadhar_detail : '';

        // console.log((AadharStatus.aadhar_status == "0" && !AadharStatus.aadhar_detail.aadhar_number) ? 8 : (AadharStatus.aadhar_status == "1" && AadharStatus.aadhar_detail.aadhar_number) ? 1 : 0)

        if (Utilities.getMasterData().login_flow == 0) {
            mVerificationSteps.push(
                {
                    'name': AppLabels.MOBILE,
                    'status': 1,
                    'icon': DARK_THEME_ENABLE ? Images.DT_MOBILE_ICON : Images.MOBILE_ICON,
                    'image': '',
                })
            mVerificationSteps.push(
                {
                    'name': AppLabels.EMAIL,
                    'status': this.state.profileDetail.email_verified,
                    'icon': DARK_THEME_ENABLE ? Images.DT_EMAIL_ICON : Images.EMAIL_ICON,
                    'image': '',

                })
        }
        else {
            mVerificationSteps.push(
                {
                    'name': AppLabels.EMAIL,
                    'status': 1,
                    'icon': DARK_THEME_ENABLE ? Images.DT_EMAIL_ICON : Images.EMAIL_ICON,
                    'image': '',
                })
            if (Utilities.getMasterData().a_mbl != 0) {
                mVerificationSteps.push(
                    {
                        'name': AppLabels.MOBILE,
                        'status': this.state.profileDetail.phone_verfied,
                        'icon': Images.MOBILE_ICON,
                        'image': '',
                    })
            }

        }
        if (Utilities.getMasterData().a_pan_flow == 1) {
            mVerificationSteps.push(
                {
                    'name': AppLabels.replace_PANTOID(AppLabels.PAN),
                    'status': this.state.profileDetail.pan_verified,
                    'icon': DARK_THEME_ENABLE ? Images.DT_PAN_ICON : Images.PAN_ICON,
                    'image': this.state.profileDetail.pan_image,

                })
        }
        if (Utilities.getMasterData() && WSManager.loggedIn() && Utilities.getMasterData().a_aadhar == "1") {
            mVerificationSteps.push(
                {
                    'name': AppLabels.AADHAR,
                    'icon': Images.PAN_ICON,
                    'image': this.state.profileDetail.pan_image,
                    'status': ((AadharNo.length == 0 && AadharStatus.aadhar_status == "0") ? 8 : (AadharStatus.aadhar_status == "0" && AadharNo.aadhar_number) ? 0 : (AadharNo.aadhar_number && AadharStatus.aadhar_status == "2") ? 8 : 1)
                })
        }
        if (Utilities.getMasterData().a_crypto == 1) {
            mVerificationSteps.push(
                {
                    'name': AppLabels.BANK,
                    'crypto': '1',
                    'status': this.state.profileDetail.is_bank_verified,
                    'icon': Images.BANK_ICON,
                    'c_status': this.state.profileDetail.user_bank_detail.upi_id ? 'verified' : 'no',
                })
        }
        else {
            if (Utilities.getMasterData().a_bank_flow == 1) {
                mVerificationSteps.push(
                    {
                        'name': AppLabels.BANK,
                        'status': this.state.profileDetail.is_bank_verified,
                        'icon': Images.BANK_ICON,
                        'image': this.state.profileDetail.user_bank_detail ? this.state.profileDetail.user_bank_detail.bank_document : '',
                    })
            }
        }

        this.setState({ verificationSteps: mVerificationSteps }, () => {
        })
    }

    /**
     * @description method to display username edit modal
     */
    EditUserNameModalShow = () => {
        this.setState({
            showEditUserNameModal: true,
        });
    }
    /**
     * @description method to hide username edit modal
     */
    EditUserNameModalHide = () => {
        this.setState({
            showEditUserNameModal: false,
            profileDetail: ls.get('profile'),
        })
    }

    /**
     * @description method to display mobile no edit modal
     */
    EditMobileModalShow = () => {
        this.setState({
            showEditMobileModal: true,
        });
    }
    /**
     * @description method to hide mobile no edit modal
     */
    EditMobileModalHide = () => {
        this.setState({
            showEditMobileModal: false,
        });
        this.callProfileDetail();
    }

    /**
     * @description method to display email edit modal
     */
    EditEmailModalShow = () => {
        this.setState({
            showEditEmailModal: true,
        });
    }
    /**
     * @description method to hide email edit modal
     */
    EditEmailModalHide = () => {
        this.setState({
            showEditEmailModal: false,
        });
        this.callProfileDetail();
    }
    /**
     * @description method to open edit profile page
     */
    openEditProfile() {
        this.props.history.push({ pathname: '/edit-profile' })
    }
    /**
     * @description method to open edit profile page
     */
    goToVerifyAccount() {
        this.props.history.push({
            pathname: '/verify-account',
            state: {
                email_verified: this.state.profileDetail.email_verified,
                phone_verfied: this.state.profileDetail.phone_verfied,
                pan_verified: this.state.profileDetail.pan_verified,
                is_bank_verified: this.state.profileDetail.is_bank_verified,
                isFromProfile: true,
                a_aadhar: this.state.profileDetail.aadhar_status

            }
        })
    }

    callUserXPDetail() {
        getUserXPCard().then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({
                    userXPDetail: responseJson.data.user_xp_card
                })
            }
        })
    }
    
    onCopyCode = () => {
        Utilities.showToast(AppLabels.MSZ_COPY_CODE, 1000);
        this.setState({ copied: true })
    }


    /**
     * 
     * @description method to display share contest popup model.
     */
     shareProfileModalShow = (data) => {
        this.setState({
            showShareProfileModal: true,
        });
    }
    /**
     * 
     * @description method to hide share contest popup model.
     */
     shareProfileModalHide = () => {
        this.setState({
            showShareProfileModal: false,
        });
    }

    handleBack=()=>{
        if(!isFooterTab('my-profile') && this.state.isXPEnable){
            this.props.history.push("/more");
        }
        else{
            this.props.history.goBack();
        }
    }

    render() {
        const {
            showEditUserNameModal,
            showEditMobileModal,
            showEditEmailModal,
            profileDetail,
            isXPEnable,
            userXPDetail,
            profileUrl,
            showShareProfileModal
        } = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="profile-section profile-view-section">
                        {
                            // !isFooterTab('my-profile') &&
                            <CustomHeader {...this.props} HeaderOption={{
                                back: true,// !isFooterTab('my-profile') ? true : false,
                                goBackMore: !isFooterTab('my-profile') && isXPEnable ? true :false,
                                isPrimary: DARK_THEME_ENABLE ? false : true,
                                notification: isFooterTab('my-profile') ? true : false,
                                // NoLogo: ''
                            }} />
                        }
                        {
                            !isFooterTab('my-profile') &&
                            <div className="profile-back" onClick={()=>this.handleBack()}>
                                <i className="icon-left-arrow"></i>
                            </div>
                        }
                        <UserProfileHeader {...this.props}
                            UserProfileDetail={profileDetail}
                            IsProfileVerifyShow={true}
                            IsImgEditable={true}
                            EditUserNameModalShow={() => this.EditUserNameModalShow()}
                            goToVerifyAccount={() => this.goToVerifyAccount()}
                            StepList={this.state.verificationSteps}
                            accVerified={this.state.accVerified}
                            isXPEnable={isXPEnable}
                            userXPDetail={userXPDetail}
                        />

                        {profileDetail !== '' &&
                            <div className="profile-body">
                                {
                                    isXPEnable && userXPDetail &&
                                    <XPProfileCard userXPDetail={userXPDetail} {...this.props} shareProfile={this.shareProfileModalShow} />
                                }
                                {this.state.playingExpdata &&
                                    <div className="section-header">{AppLabels.PLAYING_EXPERIENCE}</div>
                                }
                                <div className="playing-exp-block">
                                    <div className="playing-exp-content">
                                        <Row>
                                            {
                                                _Map(this.state.playingExpdata, (item, index) => {
                                                    return (
                                                        <Col key={index} sm={6} xs={6}>
                                                            <DataCountBlock item={item} key={index} onClick={() => ''} countInt={true} />
                                                        </Col>
                                                    )
                                                })
                                            }
                                        </Row>
                                    </div>
                                </div>
                                <div className="section-header">{AppLabels.PRIMARY_INFO}</div>
                                <div className="primary-info-section">
                                    <div className="editable-info">
                                        <div className="info-label">{AppLabels.MOBILE}</div>
                                        <div className="info-value">
                                            {
                                                !profileDetail.phone_no ? '--' :
                                                    '+' + profileDetail.phone_code + ' ' + profileDetail.phone_no
                                            }
                                            <a href id="mobileEdit" onClick={() => this.EditMobileModalShow()}><i className="icon-edit-line"></i></a>
                                        </div>
                                    </div>
                                    <div className="editable-info">
                                        <div className="info-label">{AppLabels.EMAIL}</div>
                                        <div className="info-value">
                                            {profileDetail.email}
                                            <a href id="emailEdit" onClick={() => this.EditEmailModalShow()}><i className="icon-edit-line"></i></a>
                                        </div>
                                    </div>
                                </div>
                                <div className="section-header">{AppLabels.BASIC_INFO}</div>
                                <div className="user-basic-info-section">
                                    <a href id="basicInfoEdit" className="basic-info-edit" onClick={() => this.openEditProfile()}>
                                        <i className="icon-edit-line"></i>
                                    </a>
                                    <div className="display-table">
                                        <div className="editable-info">
                                            <div className="info-label">{AppLabels.DOB}</div>
                                            <div className="info-value">
                                                {profileDetail.dob ?
                                                    <MomentDateComponent data={{ date: profileDetail.dob, format: "MMM DD, YYYY" }} /> :
                                                    <span>--</span>
                                                }
                                            </div>
                                        </div>
                                        <div className="editable-info">
                                            <div className="info-label">{AppLabels.GENDER}</div>
                                            <div className="info-value text-capitalize">
                                                {profileDetail.gender || <span>--</span>}
                                            </div>
                                        </div>
                                    </div>
                                    <div className="display-table">
                                        <div className="editable-info">
                                            <div className="info-label">{AppLabels.SETREET_ADDRESS}</div>
                                            <div className="info-value">
                                                {profileDetail.address || <span>--</span>}
                                            </div>
                                        </div>
                                        <div className="editable-info">
                                            <div className="info-label">{AppLabels.COUNTRY}</div>
                                            <div className="info-value">
                                                {profileDetail.country_name || <span>--</span>}
                                            </div>
                                        </div>
                                    </div>
                                    <div className="display-table">
                                        <div className="editable-info">
                                            <div className="info-label">{AppLabels.STATE}</div>
                                            <div className="info-value">
                                                {profileDetail.state_name || <span>--</span>}
                                            </div>
                                        </div>
                                        <div className="editable-info">
                                            <div className="info-label">{AppLabels.CITY}</div>
                                            <div className="info-value">
                                                {profileDetail.city || <span>--</span>}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        }
                        {
                            showEditUserNameModal &&
                            <EditUserNameModal
                                IsEditUserNameShow={showEditUserNameModal}
                                IsEditUserNameHide={this.EditUserNameModalHide}
                            />
                        }
                        {
                            showEditMobileModal &&
                            <EditMobileModal
                                IsEditMobileShow={showEditMobileModal}
                                IsEditMobileHide={this.EditMobileModalHide}
                                onHide={() => this.setState({
                                    showEditMobileModal: false,
                                })}
                            />
                        }
                        {
                            showEditEmailModal &&
                            <EditEmailModal
                                {...this.props}
                                IsEditEmailShow={showEditEmailModal}
                                IsEditEmailHide={this.EditEmailModalHide}
                                email={profileDetail.email}
                                isVerifyMode={false}
                            />
                        }


                        {
                            isXPEnable && showShareProfileModal &&
                            <ShareProfileModal
                                IsModalShow={this.shareProfileModalShow}
                                IsModalHide={this.shareProfileModalHide}
                                profileDetail={profileDetail} />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}