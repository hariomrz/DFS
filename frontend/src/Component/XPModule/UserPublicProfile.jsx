import React from 'react';
import { Row, Col } from "react-bootstrap";
import { MyContext } from '../../InitialSetup/MyProvider';
import { getMyPublicProfile,getUserXPCard } from '../../WSHelper/WSCallings';
import { _Map, Utilities, isFooterTab } from '../../Utilities/Utilities';
import { UserProfileHeader, DataCountBlock } from '../CustomComponent';
import { XPProfileCard } from '../CustomComponent/../XPModule';
import CustomHeader from '../../components/CustomHeader';
import {DARK_THEME_ENABLE} from "../../helper/Constants";
import ls from 'local-storage';
import * as WSC from "../../WSHelper/WSConstants";
import * as Constants from "../../helper/Constants";
import * as AppLabels from "../../helper/AppLabels";
import {createBrowserHistory} from 'history';

var expData = null;
var globalThis = null;
const history = createBrowserHistory();
const location = history.location;
const queryString = require('query-string');
const parsed = queryString.parse(location.search);

export default class UserPublicProfile extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            code: Constants.DEFAULT_COUNTRY_CODE,
            profileDetail: ls.get('profile') || '',
            verificationSteps: '',
            playingExpdata: '',            
            isXPEnable: Utilities.getMasterData().a_coin == '1' && Utilities.getMasterData().a_xp_point == '1' ? true : false,
            userXPDetail: '',
            profileId: '',
            isLoading: false
        }
    }

    componentDidMount() {        
        globalThis = this; 
        const matchParam = this.props.match.params;
        this.setState({
            profileId : matchParam.user_id
        },()=>{
            Utilities.handleAppBackManage('my-profile')
            if (expData && Utilities.minuteDiffValue(expData) < 1) {
                this.parseExpData(expData.data)
            }
            this.callProfileDetail();
            if(this.state.isXPEnable){
                this.callUserXPDetail();
            }
        },10)
    }
    /**
    * @description method to display profile detail of user
    */
    callProfileDetail() {
        this.setState({
            isLoading: true
        })
        let param = {
            "user_id": this.state.profileId
        }
        getMyPublicProfile(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({
                    isLoading: false
                })
                this.parseProfileData(responseJson.data);
                if (expData && Utilities.minuteDiffValue(expData) < 1) {
                    this.parseExpData(expData.data)
                } else {
                    expData = { data: responseJson.data, date: Date.now() };
                    this.parseExpData(responseJson.data.pe)
                }
            }
        })
    }
    parseProfileData(data) {
        ls.set('profile', data)
        this.setState({
            profileDetail: data
        })
    }

    parseExpData(playingExpdata) {
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
                    'count_for': AppLabels.MATCHES
                },
                {
                    'icon': 'icon-trophy2-ic',
                    'count': playingExpdata.league_counts,
                    'count_for': AppLabels.SERIES
                }
            ],
        })
    }
    onCopyCode = () => {
        Utilities.showToast(AppLabels.MSZ_COPY_CODE, 1000);
        this.setState({ copied: true })
    }
    callUserXPDetail() {
        let param={
            'user_id': this.state.profileId
        }
        getUserXPCard(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({
                    userXPDetail: responseJson.data.user_xp_card
                })
            }
        })
    }
    render() {
        const {
            profileDetail,
            isXPEnable,
            userXPDetail
        } = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="profile-section profile-view-section public-profile-sec">
                        <UserProfileHeader {...this.props}
                            UserProfileDetail={profileDetail}
                            IsProfileVerifyShow={true}
                            IsImgEditable={false}
                            EditUserNameModalShow={() => this.EditUserNameModalShow()}
                            goToVerifyAccount={() => this.goToVerifyAccount()}
                            StepList={this.state.verificationSteps}
                            accVerified={this.state.accVerified}
                            isXPEnable={isXPEnable}
                            userXPDetail={userXPDetail}
                            publicProfile={true}
                        />

                        {profileDetail !== '' &&
                            <div className="profile-body">
                                {
                                    isXPEnable && userXPDetail &&
                                    <XPProfileCard userXPDetail={userXPDetail} {...this.props} publicProfile={true} />
                                }
                                {this.state.playingExpdata &&
                                    <div className="section-header">{AppLabels.PLAYING_EXPERIENCE}</div>
                                }
                                <div className="playing-exp-block">
                                    <div className="playing-exp-content">
                                        <Row>
                                            {this.state.playingExpdata && this.state.playingExpdata.length > 0 &&
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
                            </div>
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}