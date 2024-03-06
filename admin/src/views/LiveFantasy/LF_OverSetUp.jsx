import React, { Component, Fragment } from "react";
import { Row, Col, Button, Modal, ModalBody, ModalHeader, ModalFooter, Tooltip, Input } from 'reactstrap';
import _ from 'lodash';
import * as NC from '../../helper/NetworkingConstants';
import WSManager from "../../helper/WSManager";
import { _times, _isEmpty } from "../../helper/HelperFunction";
import { LF_GET_SEASON_TO_PUBLISH } from "../../helper/WSCalling";
import { MomentDateComponent } from "../../components/CustomComponent";
import Images from "../../components/images";
import { _isUndefined, _Map } from "../../helper/HelperFunction";
import { LF_TITLE_PUBLISH_MATCH, LF_MSG_PUBLISH_MATCH } from "../../helper/Message";
import { notify } from 'react-notify-toast';
import HF from "../../helper/HelperFunction";
class LF_OverSetUp extends Component {
    constructor(props) {
        super(props)
        this.state = {
            SelectedInning: "0",
            SelectedOver: "1",
            league_id: (this.props.match.params.league_id) ? this.props.match.params.league_id : '',
            season_game_uid: (this.props.match.params.season_game_uid) ? this.props.match.params.season_game_uid : '',
            fxDetail: [],
            OverParam: [],
            refOver: true,
            newAddedOver: [],
            isShowToolTip: false,
            Multiplier: 0,
            Capping: 0,
            MultipliedVal: 0,
            multiConst: 3.5,

        }
    }

    componentDidMount = () => {
        this.GetFixtureDetail()
    }

    handleInputChange = (event) => {
        let name = event.target.name
        let value = event.target.value

        this.setState({ [name]: value, refOver: false, }, () => {
            /**Start code for published */
            var inning1over = []
            if (this.state.fxDetail.is_published == "1") {
                inning1over = this._filterOver()
            }
            /**End code for published */
            if (this.state.SelectedOver != "1") {
                this.setState({ refOver: true, OverParam: inning1over, })
            } else {
                this.setState({
                    refOver: true,
                    OverParam: !_isEmpty(this.state.fxDetail.overs) ? this.state.fxDetail.overs : this.state.OverParam,
                })
            }
        })
    }

    _filterOver = () => {
        let { fxDetail, SelectedInning } = this.state
        var overs = []
        if (!_isEmpty(fxDetail.collection)) {
            fxDetail.collection.filter(item => {
                if (item.inning == SelectedInning) {
                    overs.push(parseInt(item.overs))
                }
            })
        }

        console.log("overs==", overs);

        return overs
    }

    GetFixtureDetail = () => {
        let { league_id, season_game_uid, multiConst } = this.state
        let param = {
            "league_id": league_id,
            "season_game_uid": season_game_uid
        }

        this.setState({ posting: true });

        LF_GET_SEASON_TO_PUBLISH(param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                var rd = responseJson.data ? responseJson.data : []

                this.setState({
                    fxDetail: responseJson.data,
                    OverParam: !_isEmpty(rd.overs) ? rd.overs : this.state.OverParam,
                    SelectedInning: rd.is_published == "1" ? "1" : "0",
                    Multiplier: rd.multiplier ? rd.multiplier : 10,
                    Capping: rd.capping ? rd.capping : 100,
                    MultipliedVal: this._getMultiplier(rd.multiplier),
                });
            }
            else if (responseJson.response_code == NC.sessionExpireCode) {
                WSManager.logout();
                this.props.history.push('/login');
            }
        })
    }

    _checkOverSelected = (over_arr, over) => {
        let { OverParam } = this.state
        var rval = true
        // if (OverParam.indexOf(over) == -1)
        if (over_arr.indexOf(over) == -1)
            rval = false

        return rval
    }

    _getSelctAllOpt = () => {
        let { fxDetail, SelectedOver, OverParam, newAddedOver } = this.state
        return (
            !_isEmpty(fxDetail.overs) &&
            _times(fxDetail.overs.length, (over) => {
                let newover = over + 1
                return (
                    <Col md={3} key={newover}>
                        <div className="common-cus-checkbox">
                            <label className="com-chekbox-container">
                                <span className="opt-text">Over {newover}</span>
                                <input
                                    defaultChecked={this._checkOverSelected(OverParam, newover)}
                                    disabled={((this._checkOverSelected(OverParam, newover) && SelectedOver == "1") || (this._checkOverSelected(OverParam, newover) && (SelectedOver == "3") && (fxDetail.is_published == "1") && (!this._checkOverSelected(newAddedOver, newover))))}
                                    type="checkbox"
                                    onChange={(e) => this.handleOverSelect(e, newover)}
                                />
                                <span className="com-chekbox-checkmark"></span>
                            </label>
                        </div>
                    </Col>
                )
            })
        )
    }

    _getRange = () => {
        let { fxDetail } = this.state
        let min = 1;
        let max = 5;
        let totalOver = !_isEmpty(fxDetail.overs) ? fxDetail.overs.length : 50;
        let looplength = totalOver / max
        let arr = []
        for (let i = 1; i <= looplength; i++) {
            let str = 'Over ' + (i!=1? min+1: min) + ' to ' + (max * i)
            min = max * i
            arr.push({
                'value': min,
                'label': str,
            })
        }
        return arr
    }

    _getRangeOpt = () => {
        const arr = this._getRange()
        let { SelectedOver, OverParam } = this.state
        return (
            _Map(arr, (range, idx) => {
                return (
                    <Col md={3} key={idx}>
                        <div className="common-cus-checkbox">
                            <label className="com-chekbox-container">
                                <span className="opt-text">{range.label}</span>
                                <input
                                    defaultChecked={this._checkOverSelected(OverParam, range.value)}
                                    // disabled={(this._checkOverSelected(range.value))}
                                    type="checkbox"
                                    onChange={(e) => this.handleOverSelect(e, range.value)}
                                />
                                <span className="com-chekbox-checkmark"></span>
                            </label>
                        </div>
                    </Col>
                )
            })
        )
    }

    handleOverSelect = (e, item) => {
        let { OverParam, newAddedOver, fxDetail } = this.state
        if (e) {
            let tempOver = !_.isUndefined(OverParam) ? OverParam : [];

            let tempnewOver = !_.isUndefined(newAddedOver) ? newAddedOver : [];

            if (tempOver.indexOf(item) !== -1) {
                let indx = tempOver.indexOf(item)
                tempOver.splice(indx, 1);

                if (fxDetail.is_published == "1") {
                    let new_indx = tempnewOver.indexOf(item)
                    tempnewOver.splice(new_indx, 1);
                }
            } else {
                tempOver.push(item)
                if (fxDetail.is_published == "1") {
                    tempnewOver.push(item)
                }
            }
            this.setState({
                OverParam: tempOver,
                newAddedOver: tempnewOver
            })
        }
    }

    PublishMatchModalToggle = () => {
        let { OverParam, Multiplier, Capping } = this.state
        if (_isEmpty(OverParam)) {
            notify.show('Please select over', "error", 3000);
            return false
        }
        else if (Multiplier < 1 || Multiplier > 9999) {
            notify.show('Multiplier should be in the rage of 1 to 9999', "error", 3000);
            return false
        }
        else if (Capping < 1 || Capping > 99999) {
            notify.show('Capping should be in the rage of 1 to 99999', "error", 3000);
            return false
        }
        this.setState({
            PublishModalIsOpen: !this.state.PublishModalIsOpen,
        });
    }

    publishMatchModal = () => {
        let { FixturePosting } = this.state
        return (
            <div>
                <Modal
                    isOpen={this.state.PublishModalIsOpen}
                    toggle={this.PublishMatchModalToggle}
                    className="cancel-match-modal"
                >
                    <ModalHeader>{LF_TITLE_PUBLISH_MATCH}</ModalHeader>
                    <ModalBody>
                        <div className="confirm-msg">{LF_MSG_PUBLISH_MATCH}</div>
                    </ModalBody>
                    <ModalFooter>
                        <Button
                            color="secondary"
                            onClick={this.publishFixture}
                            disabled={FixturePosting}
                        >Yes</Button>{' '}
                        <Button color="primary" onClick={this.PublishMatchModalToggle}>No</Button>
                    </ModalFooter>
                </Modal>
            </div>
        )
    }

    publishFixture = () => {
        let { fxDetail, league_id, season_game_uid, SelectedOver, SelectedInning, OverParam, Multiplier, Capping } = this.state

        if (SelectedOver == "2") {
            let new_op = []
            for (let i = 0; i < OverParam.length; i++) {
                for (let j = ((OverParam[i] - 5) || 1); j <= OverParam[i]; j++) {
                    new_op.push(j)
                }
            }
            OverParam = new_op
        }
        const obj = {
            "innings": SelectedInning,
            "overs": OverParam,
            "multiplier": Multiplier,
            "capping": Capping,
        }

        console.log('obj==', obj);

        localStorage.setItem('over_data', JSON.stringify(obj))

        this.props.history.push({
            pathname: '/livefantasy/createtemplatecontest/' + league_id + '/' + season_game_uid + '/0' + '/2',
        });
    }

    ToolTipToggle = () => {
        this.setState({ isShowToolTip: !this.state.isShowToolTip });
    }

    _inputChange = (e) => {
        let name = e.target.name;
        let value = e.target.value;
        if (name == 'Multiplier') {
            this._getMultiplier(value)
            this.setState({ MultipliedVal: this._getMultiplier(value) })
        }
        this.setState({
            [name]: value
        });
    }

    _getMultiplier = (multiplier) => {
        let ret = 0        
        if (!_isEmpty(multiplier))
            ret = parseFloat(multiplier) * this.state.multiConst

        return ret
    }

    render() {
        let { SelectedInning, SelectedOver, fxDetail, refOver, PublishModalIsOpen, isShowToolTip, Multiplier, Capping, MultipliedVal } = this.state
        var globalThis = this;
        return (
            <div className="fkOverSetUp">
                {PublishModalIsOpen && this.publishMatchModal()}
                <Row>
                    <Col md={12}>
                        <div className="common-fixture float-left">
                            <img src={fxDetail.home_flag ? NC.S3 + NC.FLAG + fxDetail.home_flag : Images.DEFAULT_CIRCLE} className="com-fixture-flag float-left" alt="" />
                            <img src={fxDetail.away_flag ? NC.S3 + NC.FLAG + fxDetail.away_flag : Images.DEFAULT_CIRCLE} className="com-fixture-flag float-right" alt="" />
                            <div className="com-fixture-container">
                                <div className="com-fixture-name">{(fxDetail.home) ? fxDetail.home : 'TBA'} VS {(fxDetail.away) ? fxDetail.away : 'TBA'}</div>

                                <div className="com-fixture-time">
                                    {/* <MomentDateComponent data={{ date: fxDetail.season_scheduled_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                    {HF.getFormatedDateTime(fxDetail.season_scheduled_date, "D-MMM-YYYY hh:mm A")}

                                </div>
                                <div className="com-fixture-title">{fxDetail.league_abbr}</div>
                            </div>
                        </div>

                    </Col>
                </Row>
                <Row>
                    <Col md={12}>
                        <div className="float-left">
                            <h2 className="h2-cls mt-2">Over Set Up</h2>
                        </div>
                    </Col>
                </Row>
                <hr />
                <Row>
                    <Col md={12}>
                        <div className="osInnings osBoxShadow">
                            <div className="osSlectInn">Select inning</div>
                            <ul className="coupons-option-list">
                                {
                                    !_isUndefined(fxDetail.innings) &&
                                    Object.keys(fxDetail.innings).map(function (keyIdx) {
                                        return <li className="coupons-option-item" key={keyIdx}>
                                            <div className="custom-radio">
                                                <input
                                                    disabled={fxDetail.is_published == "1" && keyIdx == 0}
                                                    type="radio"
                                                    className="custom-control-input"
                                                    name="SelectedInning"
                                                    value={keyIdx}
                                                    checked={SelectedInning == keyIdx}
                                                    onChange={(e) => globalThis.handleInputChange(e)}
                                                />
                                                <label className="custom-control-label">
                                                    <span className="input-text">{fxDetail.innings[keyIdx]}</span>
                                                </label>
                                            </div>
                                        </li>
                                    })
                                }
                            </ul>
                        </div>
                    </Col>
                </Row>
                <div className="osSelectOver">
                    <Row>
                        <Col md={12}>
                            <div className="osInnings pb-0">
                                <div className="osSlectInn">Select Overs</div>
                                <ul className="coupons-option-list">
                                    <li className="coupons-option-item">
                                        <div className="custom-radio">
                                            <input
                                                type="radio"
                                                className="custom-control-input"
                                                name="SelectedOver"
                                                value="1"
                                                checked={SelectedOver === '1'}
                                                onChange={this.handleInputChange}
                                            />
                                            <label className="custom-control-label">
                                                <span className="input-text">Select All</span>
                                            </label>
                                        </div>
                                    </li>
                                    <li className="coupons-option-item">
                                        <div className="custom-radio">
                                            <input
                                                disabled={fxDetail.is_published == "1"}
                                                type="radio"
                                                className="custom-control-input"
                                                name="SelectedOver"
                                                value="2"
                                                checked={SelectedOver === '2'}
                                                onChange={this.handleInputChange}
                                            />
                                            <label className="custom-control-label">
                                                <span className="input-text">Select Range</span>
                                            </label>
                                        </div>

                                    </li>
                                    <li className="coupons-option-item">
                                        <div className="custom-radio">
                                            <input
                                                type="radio"
                                                className="custom-control-input"
                                                name="SelectedOver"
                                                value="3"
                                                checked={SelectedOver === '3'}
                                                onChange={this.handleInputChange}
                                            />
                                            <label className="custom-control-label">
                                                <span className="input-text">Select Set</span>
                                            </label>
                                        </div>

                                    </li>
                                </ul>
                                <hr className="mt-30 mb-30" />
                            </div>
                        </Col>
                    </Row>
                    <Row className="osSelectOpt">
                        {(SelectedOver != "2" && refOver) && this._getSelctAllOpt()}
                        {(SelectedOver == "2" && refOver) && this._getRangeOpt()}
                    </Row>
                </div>
                <Row className="mt-4">
                    <Col md={12}>
                        <div className="osInnings osBoxShadow">
                            <div className="osBallpoint">
                                Ball Points
                                <span>
                                    <i className="ml-2 icon-info-border cursor-pointer" id="bptt"></i>
                                    <Tooltip
                                        placement="right"
                                        isOpen={isShowToolTip}
                                        target="bptt"
                                        toggle={() => this.ToolTipToggle()}
                                    >Multiplier will be multiplied with odds to form ball points up to a maximum of cap value given.</Tooltip>
                                </span>
                            </div>
                            <div className="osMultiBox">
                                <div>
                                    <label>Multiplier</label>
                                    <Input
                                        disabled={fxDetail.is_published == "1"}
                                        type="number"
                                        name="Multiplier"
                                        value={Multiplier}
                                        onChange={(e) => this._inputChange(e)}
                                    />
                                </div>
                                <div>
                                    <label>Capping</label>
                                    <Input
                                         disabled={fxDetail.is_published == "1"}
                                        type="number"
                                        name="Capping"
                                        value={Capping}
                                        onChange={(e) => this._inputChange(e)}
                                    />
                                </div>
                            </div>
                            <div className="osMutiTxt">
                                For 3.5 odds, the user gets <span className="osMutiNo">{MultipliedVal}</span> points
                            </div>
                        </div>
                    </Col>
                </Row>
                <Row className="osConfirmBtn">
                    <Col md={12}>
                        <Button
                            className="btn-secondary-outline"
                            onClick={this.PublishMatchModalToggle}
                        >Confirm</Button>
                    </Col>
                </Row>
            </div>
        )
    }
}
export default LF_OverSetUp

