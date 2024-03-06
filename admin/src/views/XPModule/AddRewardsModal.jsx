import React, { Component } from "react";
import { Row, Col, Button, Modal, ModalBody, ModalFooter, Input } from 'reactstrap';
import HF, { _isEmpty, _isNull, _times, _Map, _isUndefined } from "../../helper/HelperFunction";
import SelectDropdown from "../../components/SelectDropdown";
import { notify } from 'react-notify-toast';
import { xpGetLevelList, xpGetBadgeList, xpAddRewards, xpUpdateReward } from '../../helper/WSCalling';
import * as NC from '../../helper/NetworkingConstants';
import { XP_LEVEL, XP_BADGE, XP_REWARD, XP_COIN_DTL, XP_CASHBACK_DTL, XP_CONTEST_DTL } from '../../helper/Message';
const YesNoOption = [
    { value: 1, label: 'Yes' },
    { value: 0, label: 'No' },
]

const PrizeOption = [
    { value: 0, label: 'Real Money' },
    { value: 1, label: 'Bonus' },
]
export default class AddRewardsModal extends Component {
    constructor(props) {
        super(props)
        this.state = {
            CURRENT_PAGE: 1,
            PERPAGE: 1000,
            LevelOptions: [],
            BadgeOptions: [],
            PercentOption: [],
            addRewdPosting: false,
            EditForm: true,
            RewardArr: {
                'LevelSelect': '',
                'BadgeSelect': '',
                'CoinsSelect': 0,
                'CoinPoints': '',
                'CashbackSelect': 0,
                'CashbackPrizeSelect': '',
                'CashbackPercentSelect': '',
                'CashbackMaxCap': '',
                'ContestSelect': 0,
                'ContestPrizeSelect': '',
                'ContestPercentSelect': '',
                'ContestMaxCap': '',
            },
        }
    }

    componentDidMount() {
        this.getLevel()
        this.getBadge()
        this.getPercent()
    }

    getLevel = () => {
        const { PERPAGE, CURRENT_PAGE } = this.state
        let params = {
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE
        }
        xpGetLevelList(params).then(ApiResponse => {
            if (ApiResponse.response_code == NC.successCode) {
                let res = ApiResponse.data ? ApiResponse.data.level_list : []
                let l_arr = []
                _Map(res, function (data) {
                    l_arr.push({
                        value: data.level_number,
                        label: data.level_str
                    });
                })
                this.setState({ LevelOptions: l_arr })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    getBadge = () => {
        let params = {}
        xpGetBadgeList(params).then(ApiResponse => {
            if (ApiResponse.response_code == NC.successCode) {
                let res = ApiResponse.data ? ApiResponse.data : []
                let b_arr = []
                _Map(res, function (data) {
                    b_arr.push({
                        value: data.badge_id,
                        label: data.badge_name
                    });
                })
                this.setState({ BadgeOptions: b_arr })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    getPercent = () => {
        let tArr = []
        _times(101, (n) => {
            if (n !== 0)
                tArr.push({ value: n, label: n })
        })
        this.setState({ PercentOption: tArr })
    }

    handleSelectChange = (value, name) => {
        let tempArr = this.state.RewardArr
        if (name === 'CoinsSelect' && value.value === 0) {
            tempArr['CoinPoints'] = ''
        }
        if (name === 'CashbackSelect' && value.value === 0) {
            tempArr['CashbackPrizeSelect'] = ''
            tempArr['CashbackPercentSelect'] = ''
            tempArr['CashbackMaxCap'] = ''
        }
        if (name === 'ContestSelect' && value.value === 0) {
            tempArr['ContestPrizeSelect'] = ''
            tempArr['ContestPercentSelect'] = ''
            tempArr['ContestMaxCap'] = ''
        }

        if (!_isNull(value)) {
            tempArr[name] = value.value
            this.setState({ RewardArr: tempArr, EditForm: false })
        }
    }

    handleInputChange = (e) => {
        let tempArr = this.state.RewardArr

        let inp_name = e.target.getAttribute("data-inp");
        let name = e.target.name;
        let value = e.target.value;

        if (HF.isFloat(value)) {
            value = this.state.RewardArr[name]
            let msg = inp_name + ' can not be decimal'
            notify.show(msg, 'error', 1500)
            return false
        }

        tempArr[name] = value
        this.setState({ RewardArr: tempArr, EditForm: false }, () => {

            if (Number(this.state.RewardArr[name]) < 1 || Number(this.state.RewardArr[name]) > 99999) {
                tempArr[name] = ''
                let msg = inp_name + ' value should be in the range of 1 to 99999'
                notify.show(msg, 'error', 2000)
                this.setState({ RewardArr: tempArr })
                return false
            }

        });
    }

    checkFormValid = () => {
        let { RewardArr } = this.state

        let ret_flg = true
        if (_isEmpty(RewardArr['LevelSelect'])) {
            notify.show(XP_LEVEL, 'error', 1500)
            ret_flg = false
        }

        if (_isEmpty(RewardArr['BadgeSelect'])) {
            notify.show(XP_BADGE, 'error', 1500)
            ret_flg = false
        }

        if (!RewardArr['CoinsSelect'] && !RewardArr['CashbackSelect'] && !RewardArr['ContestSelect']) {
            notify.show(XP_REWARD, 'error', 1500)
            ret_flg = false
        }

        if (RewardArr['CoinsSelect'] && _isEmpty(RewardArr['CoinPoints'])) {
            notify.show(XP_COIN_DTL, 'error', 1500)
            ret_flg = false
        }

        if (RewardArr['CashbackSelect'] && _isEmpty(RewardArr['CashbackPercentSelect']) && _isEmpty(RewardArr['CashbackMaxCap']) && (RewardArr['CashbackPrizeSelect'] !== 0 || RewardArr['CashbackPrizeSelect'] !== 1)) {
            notify.show(XP_CASHBACK_DTL, 'error', 1500)
            ret_flg = false
        }

        if (RewardArr['ContestSelect'] && _isEmpty(RewardArr['ContestPercentSelect']) && _isEmpty(RewardArr['ContestMaxCap']) && (RewardArr['ContestPrizeSelect'] !== 0 || RewardArr['ContestPrizeSelect'] !== 1)) {
            notify.show(XP_CONTEST_DTL, 'error', 1500)
            ret_flg = false
        }
        return ret_flg
    }

    emptyForm = () => {
        for (var key in this.state.RewardArr) {
            this.state.RewardArr[key] = ""
        }
        this.state.RewardArr['CoinsSelect'] = 0
        this.state.RewardArr['CashbackSelect'] = 0
        this.state.RewardArr['ContestSelect'] = 0
    }

    addRewards = () => {
        if (!this.checkFormValid())
            return false

        let r_arr = this.state.RewardArr
        let params = {
            "level_number": r_arr.LevelSelect,
            "badge_id": r_arr.BadgeSelect,
            "coins": {
                "allow": r_arr.CoinsSelect,
                "amt": r_arr.CoinPoints
            },
            "deposit_cashback": {
                "allow": r_arr.CashbackSelect,
                "amt": r_arr.CashbackPercentSelect,
                "type": r_arr.CashbackPrizeSelect,
                "cap": r_arr.CashbackMaxCap
            },
            "joining_cashback": {
                "allow": r_arr.ContestSelect,
                "amt": r_arr.ContestPercentSelect,
                "type": r_arr.ContestPrizeSelect,
                "cap": r_arr.ContestMaxCap
            }
        }
        this.setState({ addRewdPosting: true })
        let api_call = xpAddRewards

        if (r_arr.reward_id) {
            params.reward_id = r_arr.reward_id
            api_call = xpUpdateReward
        }

        api_call(params).then(ApiResponse => {
            if (ApiResponse.response_code == NC.successCode) {
                this.props.modalActioCallback()
                notify.show(ApiResponse.message, 'success', 5000)
                this.emptyForm()
                this.setState({ EditForm: true })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
            this.setState({ addRewdPosting: false })
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    handleChildToggle = (e) => {
        e.stopPropagation();
        this.emptyForm()
        this.props.modalCallback()
        this.setState({ EditForm: true })
    }

    render() {
        let { AddRewardsModalOpen, EditRItem, EditFlag } = this.props
        const { PercentOption, RewardArr, LevelOptions, BadgeOptions, addRewdPosting, EditForm } = this.state
        if (EditFlag && EditForm && !_isEmpty(EditRItem)) {

            RewardArr['level_str'] = EditRItem.level_str
            RewardArr['reward_id'] = EditRItem.reward_id

            RewardArr['LevelSelect'] = EditRItem.level_number
            RewardArr['BadgeSelect'] = EditRItem.badge_id

            RewardArr['CoinsSelect'] = EditRItem.coins.allow ? Number(EditRItem.coins.allow) : ''
            RewardArr['CoinPoints'] = RewardArr['CoinsSelect'] ? EditRItem.coins.amt : ''

            RewardArr['CashbackSelect'] = Number(EditRItem.deposit_cashback.allow)
            RewardArr['CashbackPrizeSelect'] = RewardArr['CashbackSelect'] ? EditRItem.deposit_cashback.type : ''

            RewardArr['CashbackPercentSelect'] = Number(EditRItem.deposit_cashback.amt)
            RewardArr['CashbackMaxCap'] = RewardArr['CashbackSelect'] ? EditRItem.deposit_cashback.cap : ''

            RewardArr['ContestSelect'] = Number(EditRItem.joining_cashback.allow)
            RewardArr['ContestPrizeSelect'] = RewardArr['ContestSelect'] ? EditRItem.joining_cashback.type : ''
            RewardArr['ContestPercentSelect'] = Number(EditRItem.joining_cashback.amt)
            RewardArr['ContestMaxCap'] = RewardArr['ContestSelect'] ? EditRItem.joining_cashback.cap : ''

        }

        const comm_select_props = {
            is_disabled: false,
            is_searchable: false,
            is_clearable: false,
            menu_is_open: false,
            class_name: "custom-form-control",
            place_holder: "Select",
            modalCallback: (e, name) => this.handleSelectChange(e, name)
        }

        const level_select = {
            ...comm_select_props,
            is_searchable: true,
            sel_options: LevelOptions,
            selected_value: RewardArr['LevelSelect'],
            select_name: 'LevelSelect',
        }

        const badge_select = {
            ...comm_select_props,
            is_searchable: true,
            sel_options: BadgeOptions,
            selected_value: RewardArr['BadgeSelect'],
            select_name: 'BadgeSelect',
        }

        const coin_select = {
            ...comm_select_props,
            sel_options: YesNoOption,
            selected_value: RewardArr['CoinsSelect'],
            select_name: 'CoinsSelect',
        }

        const cashback_select = {
            ...comm_select_props,
            sel_options: YesNoOption,
            selected_value: RewardArr['CashbackSelect'],
            select_name: 'CashbackSelect',
        }

        const cashback_prize_select = {
            ...comm_select_props,
            class_name: "custom-form-control",
            is_disabled: !RewardArr.CashbackSelect,
            sel_options: PrizeOption,
            selected_value: RewardArr['CashbackPrizeSelect'],
            select_name: 'CashbackPrizeSelect',
        }

        const cashback_percent_select = {
            ...comm_select_props,
            is_searchable: true,
            is_disabled: !RewardArr.CashbackSelect,
            place_holder: "Select %",
            sel_options: PercentOption,
            selected_value: RewardArr['CashbackPercentSelect'],
            select_name: 'CashbackPercentSelect',
        }

        const contest_select = {
            ...comm_select_props,
            sel_options: YesNoOption,
            selected_value: RewardArr['ContestSelect'],
            select_name: 'ContestSelect',
        }

        const contest_prize_select = {
            ...comm_select_props,
            is_disabled: !RewardArr.ContestSelect,
            sel_options: PrizeOption,
            selected_value: RewardArr['ContestPrizeSelect'],
            select_name: 'ContestPrizeSelect',
        }

        const contest_percent_select = {
            ...comm_select_props,
            is_searchable: true,
            is_disabled: !RewardArr.ContestSelect,
            place_holder: "Select %",
            class_name: "custom-form-control r-prct-dd",
            sel_options: PercentOption,
            selected_value: RewardArr['ContestPercentSelect'],
            select_name: 'ContestPercentSelect',
        }

        return (
            <Modal
                isOpen={AddRewardsModalOpen}
                toggle={this.handleChildToggle}
                className="addrewards-modal modal-lg xaddrewards-cat-mod"
            >
                <ModalBody>
                    <Row>
                        <Col md={12}>
                            <h3 className="h3-cls">{RewardArr['reward_id'] ? 'Update' : 'Add'} Rewards</h3>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={3}>
                            <div className="input-box">
                                <label>Select Level</label>
                                {
                                    RewardArr['reward_id'] ?
                                        <div className="xp-sel-title">
                                            {RewardArr ? RewardArr.level_str : ''}
                                        </div>
                                    :
                                    <SelectDropdown SelectProps={level_select} />

                                }
                            </div>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={3}>
                            <div className="input-box">
                                <label>Badge</label>
                                <SelectDropdown SelectProps={badge_select} />
                            </div>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={3}>
                            <div className="input-box">
                                <label>Coins</label>
                                <SelectDropdown SelectProps={coin_select} />
                            </div>
                        </Col>
                        <Col md={3}>
                            <div className="input-box">
                                <label className="blank-lbl"></label>
                                <Input
                                    disabled={!RewardArr.CoinsSelect}
                                    className={!RewardArr.CoinsSelect ? "disable" : ''}
                                    type="number"
                                    placeholder="Points"
                                    name='CoinPoints'
                                    data-inp='Coins points'
                                    value={RewardArr.CoinPoints || ''}
                                    onChange={(e) => this.handleInputChange(e)}
                                />
                            </div>
                        </Col>
                    </Row>

                    <Row>
                        <Col md={3}>
                            <div className="input-box">
                                <label>Cashback</label>
                                <SelectDropdown SelectProps={cashback_select} />
                            </div>
                        </Col>
                        <Col md={3}>
                            <div className="input-box">
                                <label className="blank-lbl">Currency Value</label>
                                <SelectDropdown SelectProps={cashback_prize_select} />
                            </div>
                        </Col>
                        <Col md={3}>
                            <div className="input-box">
                                <label className="blank-lbl">Select Percentage</label>
                                <SelectDropdown SelectProps={cashback_percent_select} />
                            </div>
                        </Col>
                        <Col md={3}>
                            <div className="input-box">
                                <label>Maximum Cap</label>
                                <Input
                                    disabled={!RewardArr.CashbackSelect}
                                    className={!RewardArr.CashbackSelect ? "disable" : ''}
                                    type="number"
                                    placeholder="Points"
                                    name='CashbackMaxCap'
                                    data-inp='Cashback maximum cap'
                                    value={RewardArr.CashbackMaxCap}
                                    onChange={(e) => this.handleInputChange(e)}
                                />
                            </div>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={3}>
                            <div className="input-box">
                                <label>Contest Joining Discount</label>
                                <SelectDropdown SelectProps={contest_select} />
                            </div>
                        </Col>
                        <Col md={3}>
                            <div className="input-box">
                                <label className="blank-lbl">Currency Value</label>
                                <SelectDropdown SelectProps={contest_prize_select} />
                            </div>
                        </Col>
                        <Col md={3}>
                            <div className="input-box">
                                <label className="blank-lbl">Select Percentage</label>
                                <SelectDropdown SelectProps={contest_percent_select} />
                            </div>
                        </Col>
                        <Col md={3}>
                            <div className="input-box">
                                <label>Maximum Cap</label>
                                <Input
                                    disabled={!RewardArr.ContestSelect}
                                    className={!RewardArr.ContestSelect ? "disable" : ''}
                                    type="number"
                                    placeholder="Points"
                                    name='ContestMaxCap'
                                    data-inp='Contest maximum cap'
                                    value={RewardArr.ContestMaxCap}
                                    onChange={(e) => this.handleInputChange(e)}
                                />
                            </div>
                        </Col>
                    </Row>
                </ModalBody>
                <ModalFooter>
                    <Button
                        disabled={addRewdPosting || EditForm}
                        className="btn-secondary-outline"
                        onClick={this.addRewards}
                    >{RewardArr['reward_id'] ? 'Update' : 'Add'} Rewards</Button>
                </ModalFooter>
            </Modal>
        )
    }
}