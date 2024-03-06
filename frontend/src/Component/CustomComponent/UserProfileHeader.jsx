import React, { Suspense, lazy } from 'react';
import Images from '../../components/images';
import { ProfileVerifyStep } from './CustomComponents';
import { Utilities, blobToFile, isFooterTab, compressImg } from '../../Utilities/Utilities'
import WSManager from '../../WSHelper/WSManager';
import * as WSC from "../../WSHelper/WSConstants";
import ls from 'local-storage';
import * as AppLabels from "../../helper/AppLabels";
import { OnlyCoinsFlow } from '../../helper/Constants';

const ChooseYourAvatar = lazy(() => import('../../Modals/ChooseYourAvatar'));

const options = {
    maxWidthOrHeight: 300
}
let globalThis = null;
export default class UserProfileHeader extends React.Component {

    constructor(props) {
        super(props)
        this.state = {
            UserProfileDetail: (this.props.UserProfileDetail || ls.get('profile')) || '',
            profileImageFile: '',
            selectedProfileImage: '',
            imageUrl: '',
            showAvatarModal: false,
            isLoading: false,
            cameraPermisiionGranted: false,

        }
        this.onDrop = this.onDrop.bind(this);
    }

    isAccountVerified = (data) => {
        return (data.pan_verified == "1" && data.is_bank_verified == "1" && data.email_verified == "1" && data.phone_verfied == "1") ? true : false
    }

    UNSAFE_componentWillReceiveProps(nextProps) {
        this.setState({
            UserProfileDetail: nextProps.UserProfileDetail,
            imageUrl: this.props.UserProfileDetail.image
        })

    }

    onInputClick = (event) => {
        if (event && event.target) {
            event.target.value = null;
        }
    }

    componentDidMount = () => {
        this.handelCameraPermission()
    };
    actionPancard = () => {
        if (WSManager.getIsIOSApp()) {
            this.upload.click()
        }
        else if (window.ReactNativeWebView && !this.state.cameraPermisiionGranted) {

            let data = {
                action: 'profilepic',
                targetFunc: 'profilepic',
            }
            window.ReactNativeWebView.postMessage(JSON.stringify(data));
        }
        else {
            this.upload.click()
        }
    }

    handelCameraPermission() {
        window.addEventListener('message', (e) => {

            if (e.data.action == 'profilepic' && e.data.type == 'granted') {
                this.setState({ cameraPermisiionGranted: true }, () => {
                    if (this.state.cameraPermisiionGranted) {
                        this.upload.click()
                    }
                })
            }
            else if (e.data.action == 'profilepic' && e.data.type == 'denied') {
                this.setState({ cameraPermisiionGranted: false })

            }
        });
    }

    onDrop(e) {
        e.preventDefault();
        let reader = new FileReader();
        let mfile = e.target.files[0];
        reader.onloadend = () => {
            this.setState({ selectedProfileImage: reader.result })
            this.compressImage(mfile)
        }
        if (mfile && mfile.type.match('image.*')) {
            reader.readAsDataURL(mfile)
        }
    }
    compressImage = async (mfile) => {
        this.setState({ isLoading: true });
        compressImg(mfile, options).then((compressedFile) => {
            this.setState({ profileImageFile: blobToFile(compressedFile ? compressedFile : mfile, mfile.name) }, () => {
                this.uploadImage()
            })
        }).catch(function (error) {
            this.setState({ isLoading: false });
        });
    }

    uploadImage() {

        var data = new FormData();
        data.append("userfile", this.state.profileImageFile);
        data.append("update_image_record", '1');
        var xhr = new XMLHttpRequest();
        xhr.withCredentials = false;
        xhr.addEventListener("readystatechange", function () {
            if (this.readyState == 4) {
                globalThis.setState({ isLoading: false });
                if (!this.responseText) {
                    Utilities.showToast(AppLabels.SOMETHING_ERROR, 5000, Images.PAN_ICON);
                    return;
                }
                var response = JSON.parse(this.responseText);
                if (response !== '' && response.response_code == WSC.successCode) {
                    globalThis.setState({ imageUrl: response.data.file_name })
                    if (globalThis.props.UserProfileDetail) {
                        globalThis.props.UserProfileDetail['image'] = response.data.file_name;
                        let lsProfile = WSManager.getProfile();
                        let param = {
                            'image': response.data.file_name
                        }
                        WSManager.setProfile({ ...lsProfile, ...param });
                    }
                }
                else {
                    if (response.global_error && response.global_error != '') {
                        Utilities.showToast(response.global_error, 5000);
                    }
                    else {
                        var keys = Object.keys(response.error);
                        if (keys.length > 0) {
                            Utilities.showToast(response.global_error, 5000);
                        }
                    }
                }

            }
        });

        xhr.open("POST", WSC.userURL + WSC.DO_UPLOAD);
        xhr.setRequestHeader('Sessionkey', WSManager.getToken())
        xhr.send(data);
    }

    onClickCamera = () => {
        if (this.upload) {
            if (window.ReactNativeWebView) {
                setTimeout(() => { this.setState({ showAvatarModal: false }) }, 300);
                this.actionPancard()
            } else {
                this.upload.click();
            }
        }
        this.setState({
            showAvatarModal: false
        })
    }

    onSelectAvater = (imageUrl) => {
        if (this.props.UserProfileDetail) {
            this.props.UserProfileDetail['image'] = imageUrl;
        }
        this.setState({
            showAvatarModal: false,
            imageUrl: imageUrl
        })
    }

    goToHome=()=>{
        this.props.history.push({ pathname: '/signup'})
    }
    feedAction =(e)=>{
        e.stopPropagation()
        Utilities.sendProfileDataToApp(WSManager.getProfile(),'viewpostprofile')  
    }
    render() {
        globalThis = this;

        let { IsImgEditable, EditUserNameModalShow, IsProfileVerifyShow, goToVerifyAccount, StepList, accVerified, isXPEnable, userXPDetail, publicProfile } = this.props;
        let { imageUrl, selectedProfileImage, UserProfileDetail, showAvatarModal } = this.state;
        let BadgeId = isXPEnable && userXPDetail && userXPDetail.badge_id ? userXPDetail.badge_id : '';
        return (
            <div className={"user-profile-section xp-profile-img " + (!isFooterTab('my-profile') ? ' pt-0' : '')}>
                {
                    publicProfile &&
                    <a href className="goToHome" onClick={() => this.goToHome()}>
                        <i className="icon-home"></i>
                    </a>
                }
                <div className="overlay-white-circle"></div>
                <div className="profile-cont">
                    <div className="text-center profile-img-section" onClick={(e) => IsImgEditable ? this.setState({ showAvatarModal: true })  :null  }>
                        <figure>
                            <input id="myInput"
                                type="file"
                                accept="image/*"
                                ref={(ref) => this.upload = ref}
                                style={{ display: 'none' }}
                                onChange={this.onDrop.bind(this)}
                                onClick={this.onInputClick}
                            />
                            <span className={isXPEnable ? ('xp-img-wrap bronze') : ''}>
                                <img src={imageUrl !== '' ? Utilities.getThumbURL(imageUrl) :
                                    (selectedProfileImage !== '' ? selectedProfileImage : Images.DEFAULT_AVATAR)} alt="" />
                            </span>
                            {
                                isXPEnable && userXPDetail && userXPDetail.level_number > 0 &&
                                <span className="xplevel-view bronze">
                                     {AppLabels.LEVEL} {userXPDetail.level_number} {userXPDetail.max_end_point && parseInt(userXPDetail.point) > parseInt(userXPDetail.max_end_point) && <>+</>}
                                     <span>{userXPDetail.badge_name}</span>
                                </span>
                            }
                            {this.state.isLoading && <div className="upload-loader"><div className="loader" /></div>}
                        </figure>
                        {IsImgEditable &&
                            <span id="uploadImage" className="change-img">
                                <span className="icon-wrapper"><i className="icon-camera-fill"></i></span>
                            </span>
                        }
                        {
                            isXPEnable && publicProfile && BadgeId &&
                            <span className="user-badge">
                                <img className="xp-level-with-name" src={BadgeId == 1 ? Images.XP_BRONZE : BadgeId == 2 ? Images.XP_SILVER : BadgeId == 3 ? Images.XP_GOLD : BadgeId == 4 ? Images.XP_PLATINUM : BadgeId == 5 ? Images.XP_DIAMOND : BadgeId == 6 ? Images.XP_ELITE : ""} alt="" />
                            </span>
                        }
                    </div>
                    <div className="user-name">
                        {UserProfileDetail.first_name &&
                            (UserProfileDetail.first_name + ' ' + (UserProfileDetail.last_name || ''))
                        }
                        {
                            isXPEnable && !publicProfile && BadgeId &&
                            <img className="xp-level-with-name" src={BadgeId == 1 ? Images.XP_BRONZE : BadgeId == 2 ? Images.XP_SILVER : BadgeId == 3 ? Images.XP_GOLD : BadgeId == 4 ? Images.XP_PLATINUM : BadgeId == 5 ? Images.XP_DIAMOND : BadgeId == 6 ? Images.XP_ELITE : ""} alt="" />
                        }
                    </div>
                    <div className="user-profile-name">
                        {UserProfileDetail.user_name}
                        {
                            !publicProfile &&
                            <a href id="changeUserName" onClick={EditUserNameModalShow} className="editUserName" >
                                <i className="icon-edit-line"></i>
                            </a>
                        }
                    </div>
                    {
                        isXPEnable && publicProfile &&
                        <div className="user-xp-pts">
                            <span className={'text-uppercase ' + (userXPDetail.point && userXPDetail.point.length > 5 ? 'font-sm' : '')}><img src={Images.EARN_XPPOINTS} alt="" width="16px" /> {userXPDetail.point} {AppLabels.XP}</span>
                        </div>
                    }
                </div>
                {
                  window.ReactNativeWebView &&  Utilities.getMasterData().allow_social == 1 &&
                    <div onClick={(e)=>this.feedAction(e)} className="mask">
                        <div className="view-posts">View Posts</div>
                    </div>
                }
                
                {IsProfileVerifyShow &&
                    <div>
                        <ProfileVerifyStep OnlyCoinsFlow={OnlyCoinsFlow} goToVerifyAccount={goToVerifyAccount} StepList={StepList} accVerified={accVerified} userProfileDetail={UserProfileDetail.aadhar_detail} />
                    </div>
                }
                <Suspense fallback={<div />}>
                    {
                        showAvatarModal && <ChooseYourAvatar
                            data={{
                                onHide: () => { this.setState({ showAvatarModal: false }) },
                                showModal: showAvatarModal,
                                onClickCamera: this.onClickCamera,
                                onSelectAvater: this.onSelectAvater
                            }}
                        />
                    }
                </Suspense>
            </div>
        )
    }
}