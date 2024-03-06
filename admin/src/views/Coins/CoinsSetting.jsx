import React, { Component } from "react";
import { Row, Col, InputGroup, InputGroupAddon, InputGroupText, Input, Button } from 'reactstrap';
import Images from "../../components/images";
import _ from 'lodash';
import WSManager from '../../helper/WSManager';
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import AnimateHeight from 'react-animate-height';
import HeaderNotification from '../../components/HeaderNotification';
import { COIN_SETTING_TITLE_1, COIN_SETTING_TITLE_2 } from "../../helper/Message";

class CoinsSetting extends Component {
    constructor() {
        super()
        this.state = {
            value: true,
            activeDay: 3,
            ModuleSetting: "",
            VideoSetting: "",
            SurveySetting: "",
            FeedbackSetting: "",
            StreakSetting: "",
            CoinModules: [],
            DailyCheckinDays: [],
            dailyCoinsData: [],
            SubmitPosting: true,
        }

    }
    componentDidMount() {
        this.getCoinConfiguration()
    }

    changeDays = (day) => {
        if (this.state.activeDay !== day)
            this.setState({ activeDay: day })
    }

    getCoinConfiguration() {
        let params = {}
        WSManager.Rest(NC.baseURL + NC.GET_COIN_CONFIGURATION_DETAILS, params).then(Response => {
            if (Response.response_code == NC.successCode) {

                let ResponseData = Response.data.coins_module_setting
                let StreakArr = []
                let PromotionArr = []
                _.map(ResponseData.coin_modules, (item) => {
                    if (item.submodule_key == 'daily_streak_bonus') {
                        StreakArr.push(item)
                    } else {
                        PromotionArr.push(item)
                    }
                })
                this.setState({
                    ModuleSetting: ResponseData.allow_coin,
                    coinHeight: ResponseData.allow_coin == "1" ? 'auto' : 0,
                    CoinModules: ResponseData.coin_modules,
                    DailyCheckinDays: ResponseData.daily_checkin_days,
                    StreakArr: StreakArr,
                    PromotionArr: PromotionArr,
                    dailyCoinsData: StreakArr[0].daily_coins_data,
                    activeDay: StreakArr[0].daily_coins_data.length,
                    streakHeight: StreakArr[0].status == "1" ? 'auto' : 0,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    handleModuleChange = () => {
        let { ModuleSetting } = this.state
        this.setState({
            ModuleSetting: ModuleSetting == "1" ? "0" : "1",
            coinHeight: ModuleSetting == "1" ? 0 : 'auto'
        }, () => {
            WSManager.setKeyValueInLocal('ALLOW_COIN_MODULE', ModuleSetting == "1" ? "0" : "1")
            this.updateCoinStatus()
            HeaderNotification.reloadData(ModuleSetting)
        })
    }
    handlePromotionChange = (idx, event) => {
        let name = event.target.name
        let value = event.target.value
        if (name == 'daily_streak_bonus') {
            let tempArr = this.state.StreakArr
            tempArr[idx].status = (value == "1" ? "0" : "1")
            this.setState({ StreakArr: tempArr, streakHeight: value == "1" ? 0 : 'auto', })
        }
        else {
            let tempArr = this.state.PromotionArr
            tempArr[idx].status = (value == "1" ? "0" : "1")
            this.setState({ PromotionArr: tempArr })
        }
    }

    updateCoinStatus = () => {
        let params = {
            status: this.state.ModuleSetting
        }
        WSManager.Rest(NC.baseURL + NC.UPDATE_COINS_STATUS, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.global_error, 'success', 5000)
                window.location.reload();
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }


    inputChange = (idx, val) => {
        let { dailyCoinsData } = this.state
        let tempCoinData = dailyCoinsData
        tempCoinData[idx] = val

        if ((val >= 0 && val <= 9999 && val.length <= 4)) {
            tempCoinData[idx] = val
            this.setState({ dailyCoinsData: tempCoinData })
        } else {
            if (val.length > 4) {
                notify.show("Coin value should be maximum 4 digit.", "error", 5000)
            } else {
                notify.show("Coin value should be number only", "error", 5000)
            }
        }
    }

    saveCoinsConfiguration = () => {
        let { activeDay, dailyCoinsData } = this.state
        if ((activeDay <= dailyCoinsData.length)) {
            if (dailyCoinsData.indexOf('0') > 0) {
                notify.show("Streak value can not be 0", 'error', 5000)
            } else {
                this.saveCoinsConfigurationDB()
            }
        } else {
            notify.show("Please fill all daily streak value", 'error', 5000)
        }
    }

    saveCoinsConfigurationDB = () => {
        let { activeDay, StreakArr, PromotionArr, dailyCoinsData } = this.state
        _.remove(dailyCoinsData, function (item, idx) {
            return idx >= activeDay
        })
        this.setState({ dailyCoinsData })
        let params = {
            "coin_module_details":
                [...StreakArr, ...PromotionArr]
        }
        WSManager.Rest(NC.baseURL + NC.SAVE_COINS_CONFIGURATION, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.global_error, 'success', 5000)
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    render() {
        const { dailyCoinsData, activeDay, ModuleSetting, DailyCheckinDays, StreakArr, PromotionArr } = this.state
        return (
            <React.Fragment>
                <div className="coins-setting animated fadeIn">
                    <div className={`introducing-box ${!ModuleSetting ? " introbox-border" : ""}`}>
                        <Row className="p-rgt-30">
                            <Col md={6}>
                                <div className="introducing-coins">
                                    {
                                        ModuleSetting == "1" ? 'Starts with Coins' : 'Introducing Coins'
                                    }
                                </div>
                            </Col>
                            <Col md={6}>
                                <div className="activate-module">
                                    <span className="module-text">Activate Module</span>
                                    <label className="global-switch">
                                        <input
                                            type="checkbox"
                                            checked={ModuleSetting == "1" ? false : true}
                                            onChange={this.handleModuleChange}
                                        />
                                        <span className="switch-slide round">
                                            <span className={`switch-on ${ModuleSetting == "1" ? 'active' : ''}`}>Yes</span>
                                            <span className={`switch-off ${ModuleSetting == "0" ? 'active' : ''}`}>No</span>
                                        </span>
                                    </label>
                                </div>
                            </Col>
                        </Row>
                        <Row className="mt-2 p-rgt-30">
                            <Col md={12}>
                                <div className="setting-info">
                                    {
                                        ModuleSetting == "1" ? COIN_SETTING_TITLE_1 : COIN_SETTING_TITLE_2
                                    }

                                </div>
                            </Col>
                        </Row>
                        <Row className="setting-alignment p-rgt-30">
                            <Col md={4}>
                                <div className="display-table">
                                    <figure className="img-container">
                                        <img src={Images.REFER_FRD} className="img-cover" />
                                    </figure>
                                    <div className="sub-info">
                                        <div className="title">Refer a Friend</div>
                                        <div className="sub-title">User can earn coins on every<br /> friend’s signup</div>
                                    </div>
                                </div>
                            </Col>
                            <Col md={4}>
                                <div className="display-table">
                                    <figure className="img-container">
                                        <img src={Images.DAILY_STREAK} className="img-cover" />
                                    </figure>
                                    <div className="sub-info">
                                        <div className="title">Daily Streak Bonus</div>
                                        <div className="sub-title">Coins can earn by logging in<br />
                                            daily</div>
                                    </div>
                                </div>
                            </Col>
                            <Col md={4}>
                                <div className="display-table">
                                    <figure className="img-container">
                                        <img src={Images.PROMOTION} className="img-cover" />
                                    </figure>
                                    <div className="sub-info">
                                        <div className="title">Promotions</div>
                                        <div className="sub-title">User will earn coins by participating <br />in promotion activities.</div>
                                    </div>
                                </div>
                            </Col>
                        </Row>
                    </div>

                    <AnimateHeight
                        duration={900}
                        height={this.state.coinHeight}
                    >
                        <div className="promotion-box animated fadeIn">
                            <Row className="mt-4 p-rgt-30">
                                <Col md={12}>
                                    <div className="promotions">Promotions</div>
                                    <div className="pro-sub-text">Various other ways to keep your users stick to your app. </div>
                                </Col>
                            </Row>
                            <Row className="setting-alignment p-rgt-30">
                                {
                                    _.map(PromotionArr, (item, idx) => {
                                        return (
                                            <React.Fragment key={idx}>
                                                <Col md={4} className="mb-5">
                                                    <div className="display-table">
                                                        <div className="pro-sub-info">
                                                            <div className="pro-title">{item.name}</div>
                                                            <div className="pro-sub-title">{item.description}</div>
                                                        </div>
                                                        <label className="global-switch">
                                                            <input
                                                                type="checkbox"
                                                                name={item.submodule_key}
                                                                value={item.status}
                                                                checked={item.status == "1" ? false : true}
                                                                onChange={(e) => this.handlePromotionChange(idx, e)}
                                                            />
                                                            <span className="switch-slide round">
                                                                <span className={`switch-on ${item.status == "1" ? 'active' : ''}`}>Yes</span>
                                                                <span className={`switch-off ${item.status == "0" ? 'active' : ''}`}>No</span>
                                                            </span>
                                                        </label>
                                                    </div>
                                                </Col>
                                            </React.Fragment>
                                        )
                                    })
                                }
                            </Row>
                        </div>
                        <div className="daily-streak-box animated fadeIn">
                            {
                                _.map(StreakArr, (item, idx) => {
                                    return (
                                        <Row className="mt-4 p-rgt-30" key={idx}>
                                            <Col md={6}>
                                                <div className="promotions">{item.name}</div>
                                                <div className="pro-sub-text">{item.description}</div>
                                            </Col>
                                            <Col md={6}>
                                                <label className="global-switch">
                                                    <input
                                                        type="checkbox"
                                                        name={item.submodule_key}
                                                        value={item.status}
                                                        checked={item.status == "1" ? false : true}
                                                        onChange={(e) => this.handlePromotionChange(idx, e)}
                                                    />
                                                    <span
                                                        className="switch-slide round">
                                                        <span className={`switch-on ${item.status == "1" ? 'active' : ''}`}>Yes</span>
                                                        <span className={`switch-off ${item.status == "0" ? 'active' : ''}`}>No</span>
                                                    </span>
                                                </label>
                                            </Col>
                                        </Row>
                                    )
                                })
                            }
                            <AnimateHeight
                                duration={600}
                                height={this.state.streakHeight}
                            >
                                <div clas="strek-input-box animated fadeIn">
                                    <Row className="streak-top">
                                        {
                                            _.map(DailyCheckinDays, (item, idx) => {
                                                return (
                                                    <Col md={3} key={idx}>
                                                        <div className={`days ${activeDay == item ? 'active' : ''}`} onClick={() => this.changeDays(item)}>{item} Days</div>
                                                    </Col>
                                                )
                                            })
                                        }
                                    </Row>
                                    <Row className="streak-top st-align">
                                        {
                                            _.times(activeDay, (idx) => {
                                                return (
                                                    <Col md={3} key={idx} className="mb-30">
                                                        <div>
                                                            <label className="days-label">Day {idx + 1}</label>
                                                            <InputGroup>
                                                                <InputGroupAddon addonType="prepend">
                                                                    <InputGroupText>
                                                                        <span className="icon-coins">
                                                                            <i className="path1"></i>
                                                                            <i className="path2"></i>
                                                                        </span>
                                                                    </InputGroupText>
                                                                </InputGroupAddon>
                                                                <Input
                                                                    id={idx}
                                                                    // maxLength={5}
                                                                    type="number"
                                                                    value={dailyCoinsData[idx]}
                                                                    placeholder="100"
                                                                    onChange={(e) => this.inputChange(idx, e.target.value)}
                                                                />
                                                            </InputGroup>
                                                        </div>
                                                    </Col>
                                                )
                                            })
                                        }
                                    </Row>
                                </div>
                            </AnimateHeight>
                        </div>
                    </AnimateHeight>

                </div>
                {(ModuleSetting == "1") &&
                    <Row className="btn-submit-box">
                        <Col md={12}>
                            <Button onClick={this.saveCoinsConfiguration} className="btn-secondary-outline">
                                Submit
                            </Button>
                        </Col>
                    </Row>
                }

            </React.Fragment>
        )
    }
}
export default CoinsSetting