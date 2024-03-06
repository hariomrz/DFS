import React from 'react';
import { MyContext } from '../../InitialSetup/MyProvider';
import { _Map, Utilities, isFooterTab, _isEmpty, _isUndefined } from '../../Utilities/Utilities';
import CustomHeader from '../../components/CustomHeader';
import { DARK_THEME_ENABLE } from "../../helper/Constants";
import Images from '../../components/images';
import WSManager from '../../WSHelper/WSManager';
import { DownloadAppECModal } from '../UserEngagement';
import Utils from 'Local/Helper/Utils';

export default class Feed extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            DAECModal: false,

        }
    }

    componentDidMount() {
        let post_id = this.props.match.params.post_id
        if (!_isEmpty(post_id)) {

            var deeplinking_url = process.env.REACT_APP_DEEPLINK_SCHEMA + '://' + window.location
            const iOS = !!navigator.platform && /iPad|iPhone|iPod/.test(navigator.platform);
            if (iOS) {
                window.location = deeplinking_url;
                setTimeout(function () {        
                    window.location = Utils.getMasterData().app_version.ios.app_url;
                }, 500);
            }
        }
        else if (_isEmpty(post_id) || _isUndefined(post_id)) {
            this.props.history.push('/feed')
        }
        this.handelNativeEvent()

        if (window.ReactNativeWebView) {
            Utilities.sendProfileDataToApp(WSManager.getProfile(), 'profileData')

        }

    }
    handelNativeEvent() {
        window.addEventListener('message', (e) => {
            if (e.data.action == 'backfs') {
                this.props.history.push("/lobby#" + Utilities.getSelectedSportsForUrl())

            }


        });
    }

    showDAECSModal = () => {
        if (!WSManager.loggedIn()) {
            // this.goToSignUp()
            this.props.history.push("/signup")
        }
        else {
            this.setState({
                DAECModal: true
            })
        }
    }

    hideDAECModal = () => {
        this.setState({
            DAECModal: false
        })
    }

    render() {
        const {
            DAECModal
        } = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="feed-section feed-view-section">
                        {
                            <CustomHeader {...this.props} HeaderOption={{
                                back: true,
                                goBackMore: false,
                                isPrimary: DARK_THEME_ENABLE ? false : true,
                                notification: true,
                                title: "FEEDS",

                            }} />
                        }
                        {
                            !window.ReactNativeWebView &&
                            <div className='feed-view-center'>
                                <div className='youll-find-this-sec'>{"You’ll find this section in our app."}</div>
                                <div className='youll-find-this-sec-d'>{"Download our app for added fun"}</div>

                                <div className='smily-container'>
                                    <img src={Images.SMILY_SOCIAL} className='smiley'></img>

                                </div>
                                <div onClick={() => this.showDAECSModal()} className='let-go-container'>
                                    <div className='let-go-text'>Let's go!</div>

                                </div>

                            </div>
                        }


                        {
                            DAECModal &&
                            <DownloadAppECModal
                                isShow={DAECModal}
                                isHide={this.hideDAECModal}
                            />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}