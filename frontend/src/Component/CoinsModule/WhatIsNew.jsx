import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { Helmet } from "react-helmet";
import { _Map, Utilities } from '../../Utilities/Utilities';
import { Swipeable } from 'react-swipeable'
import { SELECTED_GAMET, OnlyCoinsFlow } from '../../helper/Constants';
import { updateUserSettings,getWhatIsNew } from '../../WSHelper/WSCallings';
import CustomHeader from '../../components/CustomHeader';
import Images from '../../components/images';
import MD from "../../helper/MetaData";
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import WSManager from '../../WSHelper/WSManager';
import {DARK_THEME_ENABLE} from "../../helper/Constants";

class WhatIsNew extends Component {
    constructor(props) {
        super(props);
        this.isFirst = (props.location && props.location.state) ? props.location.state.isFirst : false;
        this.state = {
            HOS: {
                back: !this.isFirst,
                title: AL.WHATSNEW,
                skip: true,
                skipAction: this.skipBtnClicked,
                isPrimary: DARK_THEME_ENABLE ? false : true,
            },
            SIN: 'slide-in-r',
            CSI: 0,
            FLOAD: 0,
            gameType: SELECTED_GAMET,
            SLIDRD:[]
            // SLIDRD: [
            //     {
            //         title: AL.HOW_TO_EARN,
            //         decription: AL.HOW_TO_EARN_DESC,
            //         image: Utilities.getMasterData().a_spin == 1 ? Images.SPIN_SW1 : Utilities.getMasterData().coin_only == 1 ? Images.W_IMG_COIN1 : Images.COIN_SW1
            //     },
            //     {
            //         title: Utilities.getMasterData().a_spin == 1 ? AL.SPIN_THE_WHEEL : AL.DAILY_CHECKIN,
            //         decription: AL.VISIT + ' ' + WSC.AppName + ' ' + AL.EVERYDAY + '. ' + AL.DAILY_CHECKIN_DESC,
            //         image: Utilities.getMasterData().a_spin == 1 ? Images.SPIN_SW2 : Utilities.getMasterData().coin_only == 1 ? Images.W_IMG_COIN2 : Images.COIN_SW2
            //     },
            //     {
            //         title: AL.GET_REWARDS,
            //         decription: OnlyCoinsFlow == 1 ? AL.GET_REWARDS_DESC_COIN : AL.GET_REWARDS_DESC,
            //         image: Utilities.getMasterData().coin_only == 1 ? Images.W_IMG_COIN3 : Images.COIN_SW3
            //     }
            // ]
        }
    }

    UNSAFE_componentWillMount() {
        Utilities.setScreenName('WSN')
        if (this.state.gameType && this.isFirst) {
            WSManager.removeLSItem('SHGT');
        }
    }
   

    whatIsNewRecordDetail = async (offset) => {
        getWhatIsNew().then(ResponseJson => {
            this.setState({SLIDRD:ResponseJson.data})
       })
     };

    componentDidMount() {
        this.whatIsNewRecordDetail();
        if(Utilities.getMasterData().a_rookie == 1){
            let tmpAry = this.state.SLIDRD;
            tmpAry[0] = {
                title: AL.WHAT_IS_ROOKIE_CONTEST,
                decription: AL.WHAT_NEW_MSG,
                image: Images.SW1_ROOKIE
            }
            this.setState({
                SLIDRD: tmpAry
            });
        }
    }

    completedWhatsNew = () => {
        let profile = WSManager.getProfile();
        let param = profile.user_setting || {};
        if (param.earn_coin == "1") {
            this.props.history.push({ pathname: '/' });
        } else {
            param["earn_coin"] = "1";
            param["user_id"] = undefined;
            param["_id"] = undefined;

            profile['user_setting'] = param;
            WSManager.setProfile(profile);
            if (this.state.gameType) {
                setTimeout(() => {
                    CustomHeader.showSHSCM();
                }, 100);
                WSManager.setPickedGameType(this.state.gameType);
            }
            setTimeout(() => {
                this.props.history.push({ pathname: '/' });
            }, 50);

            updateUserSettings(param).then((responseJson) => {
                if (responseJson.response_code == WSC.successCode) {
                    CustomHeader.showCoinCM();
                }
            })
        }
    }

    skipBtnClicked = () => {
        if (this.isFirst) {
            this.completedWhatsNew();
        } else {
            this.props.history.goBack();
        }
    }

    nextBtnAction = () => {
        const { CSI, SLIDRD } = this.state;
        const length = (SLIDRD.length - 1);
        this.changleSlider(CSI < length ? (CSI + 1) : CSI)
        if (CSI === length) {
            if (!this.isFirst) {
                this.props.history.goBack();
            } else {
                this.completedWhatsNew();
            }
        }
    }

    preBtnAction = () => {
        const { CSI } = this.state;
        this.changleSlider(CSI > 0 ? (CSI - 1) : CSI)
    }

    onSwiped = (eventData) => {
        const { CSI, SLIDRD } = this.state;
        if (eventData && eventData.dir === "Left" && CSI < (SLIDRD.length - 1)) {
            this.nextBtnAction();
        }
        if (eventData && eventData.dir === "Right") {
            this.preBtnAction();
        }
    }

    changleSlider = (value) => {
        const length = (this.state.SLIDRD.length - 1)
        if (this.state.CSI != value) {
            this.setState({
                CSI: value,
                FLOAD: this.state.FLOAD < value ? value : this.state.FLOAD,
                SIN: value >= this.state.CSI ? 'slide-in-r' : 'slide-in-l',
                HOS: {
                    back: !this.isFirst,
                    title: AL.WHATSNEW,
                    skip: value < length,
                    skipAction: this.skipBtnClicked,
                    isPrimary: DARK_THEME_ENABLE ? false : true,
                }
            })
        }
    }

    styleCss(CSI, index) {
        const { SIN } = this.state;
        if (CSI === index) {
            return ('active ' + SIN)
        } else if (CSI > index && index < 2) {
            return ('slide-out-r')
        }
        else if (CSI < index && index > 0) {
            return 'slide-out-l' + (this.state.FLOAD < index ? ' no-anim' : '');
        }

    }

    renderSPage = () => {
        const { SLIDRD, CSI } = this.state;
        return (
            <React.Fragment>
                {
                    _Map(SLIDRD, (item, index) => {
                        return (
                            <div name={"slide-" + item.name} key={index} className={"c-slide " + this.styleCss(CSI, index)}>
                                <div className="slider-title slider-title-view">
                                    {item.name}
                                </div>
                                <div className="slider-desc">
                                    {item.description}
                                </div>
                                <img alt="" 
                                // NC.S3 + NC.WHATSNEW_IMG_PATH + item.image
                                src={Utilities.getWhatsNew(item.image)}
                                 />
                            </div>
                        )
                    })
                }
            </React.Fragment>
        )
    }
    renderSDots = () => {
        const { SLIDRD, CSI } = this.state;
        return (
            <ul className="slider-dots-ul">
                {
                    _Map(SLIDRD, (item, index) => {
                        return <li name={item.title} key={index} value={index} onClick={(e) => this.changleSlider(e.target.value)} className={CSI === index ? "active" : ""} />
                    })
                }
            </ul>
        )
    }



    renderFooterBtns = () => {
        const { CSI, SLIDRD } = this.state;
        const length = (SLIDRD.length - 1)
        return (
            <div className="footer-btns" >
                <a href onClick={this.preBtnAction} name={CSI + 'prev'} className={"header-action pre skip-step" + (CSI > 0 ? '' : ' btn-hide')}>
                    {AL.PREV}
                </a>
                <a href onClick={this.nextBtnAction} name={CSI + 'next'} className="header-action skip-step active">
                    {CSI === length ? AL.GOTIT : AL.NEXT}
                </a>
            </div>
        )
    }

    render() {
        const { HOS } = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container what-is-new">
                        <Helmet titleTemplate={`${MD.template} | %s`}>
                            <title>{MD.WSN.title}</title>
                            <meta name="description" content={MD.WSN.description} />
                            <meta name="keywords" content={MD.WSN.keywords}></meta>
                        </Helmet>
                        <CustomHeader {...this.props} HeaderOption={HOS} />
                        <Swipeable className="swipe-view" onSwiped={this.onSwiped} >
                            <div className="slider-container">
                                <div className="slides">
                                    {this.renderSPage()}
                                </div>
                                {this.renderSDots()}
                                {this.renderFooterBtns()}
                            </div>
                        </Swipeable>

                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}

export default WhatIsNew;