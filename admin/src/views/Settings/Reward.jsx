import React, { Component } from "react";
import { Row, Col, Button, Input, Table, ModalBody, Modal, ModalHeader, ModalFooter } from 'reactstrap';
import _ from 'lodash';
import HF, { _isEmpty } from '../../helper/HelperFunction';
import { notify } from 'react-notify-toast';
import Loader from '../../components/Loader';
import { getScratchCardList, deleteScratchCard, addScratchCard, updateScratchCard } from '../../helper/WSCalling';
import * as NC from "../../helper/NetworkingConstants";
import { REWARD_DELETE_MSG, REWARD_DELETE_SUB_MSG, MODULE_NOT_ENABLE, REWARD_AMOUNT_MSG } from "../../helper/Message";
import PromptModal from '../../components/Modals/PromptModal';
import Pagination from "react-js-pagination";
import SelectDropdown from "../../components/SelectDropdown";
class Reward extends Component {
    constructor(props) {
        super(props)
        this.state = {
            CURRENT_PAGE: 1,
            PERPAGE: NC.ITEMS_PERPAGE,
            RewardList: [],
            ListPosting: true,
            DeletePosting: false,
            DeleteModalOpen: false,
            addEditModalOpen: false,
            SelectPrizeType: '1',
            Amount: '',
            ResultText: '',
            RewardStatus: '1',
            AmountMsg: false,
            addEditPosting: true,
            prizeOptions: [],
        }
    }

    componentDidMount() {
        if (HF.allowScratchWin() != '1') {
            notify.show(MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        this.getReward()
    }

    getReward = () => {
        this.setState({ ListPosting: true })
        const { PERPAGE, CURRENT_PAGE } = this.state
        let params = {
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
        }
        getScratchCardList(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                let res = ResponseJson.data ? ResponseJson.data : []
                this.setState({
                    ListPosting: false,
                    RewardList: res.result ? res.result : [],
                    Total: res.total ? res.total : 0,
                    prizeOptions: res.prize_type ? res.prize_type : [],
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    deleteToggle = (r_id) => {
        this.setState({
            RewardId: r_id,
            DeleteModalOpen: !this.state.DeleteModalOpen
        })
    }

    deleteReward = () => {
        const { RewardId, RewardList, Total } = this.state
        this.setState({ DeletePosting: true })
        const param = { scratch_card_id: RewardId }
        let tempRewardList = RewardList
        deleteScratchCard(param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                _.remove(tempRewardList, function (item) {
                    return item.scratch_card_id == RewardId
                })
                
                this.setState({
                    RewardList: tempRewardList,
                    DeleteModalOpen: false,
                    Total: (tempRewardList.length == 0) ? 0 : Total,
                })
                notify.show(responseJson.message, "success", 5000);
            }
            this.setState({ DeletePosting: false })
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    handlePageChange(current_page) {
        if (current_page != this.state.CURRENT_PAGE) {
            this.setState({
                CURRENT_PAGE: current_page
            }, this.getReward);
        }
    }

    addEditRewadModalToggle = (flag, item) => {
        this.setState({
            SCRATCH_CARD_ID: item.scratch_card_id ? item.scratch_card_id : '1',
            SelectPrizeType: item.prize_type ? item.prize_type : '1',
            Amount: item.amount ? item.amount : '',
            ResultText: item.result_text ? item.result_text : '',
            RewardStatus: item.status ? item.status : '1',
            addEditRewFlag: flag,
            addEditPosting: true,
            addEditModalOpen: !this.state.addEditModalOpen,
        })
    }

    handlePrizeChange = (value) => {
        if (!_isEmpty(this.state.Amount)) {
            this.setState({ addEditPosting: false })
        }
        this.setState({ SelectPrizeType: value.value }, this.createResultMsg)
    }

    addEditRewadModal() {
        let { addEditRewFlag, addEditPosting, Amount, addEditModalOpen, prizeOptions, SelectPrizeType, ResultText, RewardStatus, AmountMsg } = this.state
        const Select_Props = {
            is_disabled: (Amount === '0') ? true : false,
            is_searchable: true,
            is_clearable: false,
            menu_is_open: false,
            class_name: "form-control",
            sel_options: (Amount === '0') ? [] : prizeOptions,
            place_holder: "Select Prize",
            selected_value: SelectPrizeType,
            modalCallback: this.handlePrizeChange
        }
        return (
            <Modal isOpen={addEditModalOpen} toggle={() => this.addEditRewadModalToggle('', '')} className={`add-league-modal modal-xs reward-mod ${addEditRewFlag === 1 ? 'animate-modal-top' : ''}`}>
                <ModalHeader>{addEditRewFlag === 1 ? 'Add' : 'Edit'} Reward</ModalHeader>
                <ModalBody>
                    <Row>
                        <Col md={12}>
                            <label>Select Prize</label>
                            <SelectDropdown SelectProps={Select_Props} />
                        </Col>
                    </Row>
                    <Row className="mt-3">
                        <Col md={12}>
                            <label>Amount</label>
                            <Input
                                maxLength={7}
                                type="number"
                                name="Amount"
                                placeholder="Amount"
                                value={Amount}
                                onChange={(e) => this.handleInputChange(e)}
                            />
                            {
                                AmountMsg &&
                                <span className="color-red">{REWARD_AMOUNT_MSG}</span>
                            }
                        </Col>
                    </Row>
                    <Row className="mt-3">
                        <Col md={12}>
                            <label>Result Text</label>
                            <Input
                                disabled={true}
                                type="text"
                                name="ResultText"
                                placeholder="ResultText"
                                value={ResultText}
                                onChange={(e) => this.handleInputChange(e)}
                            />
                        </Col>
                    </Row>
                    <Row className="mt-3">
                        <Col md={12}>
                            <label htmlFor="ProofDesc">Reward Status</label>
                            <ul className="radio-option-list">
                                <li className="radio-option-item">
                                    <div className="custom-radio">
                                        <input
                                            type="radio"
                                            className="custom-control-input"
                                            name="RewardStatus"
                                            value="1"
                                            checked={RewardStatus === '1'}
                                            onChange={this.handleInputChange}
                                        />
                                        <label className="custom-control-label">
                                            <span className="input-text">Active</span>
                                        </label>
                                    </div>
                                </li>
                                <li className="radio-option-item">
                                    <div className="custom-radio">
                                        <input
                                            type="radio"
                                            className="custom-control-input"
                                            name="RewardStatus"
                                            value="0"
                                            checked={RewardStatus === '0'}
                                            onChange={this.handleInputChange}
                                        />
                                        <label className="custom-control-label">
                                            <span className="input-text">Inactive</span>
                                        </label>
                                    </div>
                                </li>
                            </ul>
                        </Col>
                    </Row>
                </ModalBody>
                <ModalFooter>
                    <Button
                        disabled={addEditPosting}
                        className="btn-secondary-outline"
                        onClick={this.addEditReward}>{addEditRewFlag === 2 ? 'Update' : 'Save'}</Button>
                </ModalFooter>
            </Modal>
        )
    }

    handleInputChange = (e) => {
        let name = e.target.name
        let value = e.target.value
        this.setState({ AmountMsg: false })
        if (name === 'Amount' && HF.isFloat(value)) {
            value = this.state.Amount
            this.setState({ AmountMsg: true })
        }

        this.setState({ [name]: value }, () => {
            this.createResultMsg()
            if (name === 'Amount' && (value.length <= 0 || value.length > 7)) {
                this.setState({
                    Amount: '',
                    AmountMsg: true,
                    addEditPosting: true,
                    ResultText: '',
                })
            } else {
                this.setState({ addEditPosting: false })
            }
        })
    }

    addEditReward = () => {
        this.setState({ addEditPosting: true })
        let { addEditRewFlag, SelectPrizeType, Amount, ResultText, RewardStatus, SCRATCH_CARD_ID } = this.state
        let params = {
            prize_type: (Amount === '0') ? '' : SelectPrizeType, 
            amount: Amount,
            result_text: ResultText,
            status: RewardStatus
        }

        let URL = ""
        if (addEditRewFlag == 1) {
            URL = addScratchCard(params)
        } else {
            params.scratch_card_id = SCRATCH_CARD_ID
            URL = updateScratchCard(params)
        }

        URL.then(Response => {
            if (Response.response_code == NC.successCode) {
                this.getReward()
                this.setState({
                    SelectPrizeType: '1',
                    Amount: '',
                    ResultText: '',
                    RewardStatus: '1',
                    addEditModalOpen: false,
                })
                notify.show(Response.message, 'success', 5000)
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    createResultMsg = () => {
        let { SelectPrizeType, Amount } = this.state
        let ptype = ''
        if (SelectPrizeType == '0') {
            ptype = 'bonus cash'
        }
        else if (SelectPrizeType == '1') {
            ptype = 'real cash'
        }
        else if (SelectPrizeType == '2') {
            let s = (Amount > 1) ? 's' : ''
            ptype = 'coin' + s
        }

        let msg = 'Better luck next time'
        if (Amount > 0)
            msg = Amount ? 'You won ' + Amount + ' ' + ptype : '';
        this.setState({ ResultText: msg })
    }

    render() {
        let { RewardList, ListPosting, Total, DeleteModalOpen, DeletePosting, CURRENT_PAGE, PERPAGE, addEditModalOpen } = this.state
        let DeleteModalProps = {
            publishModalOpen: DeleteModalOpen,
            publishPosting: DeletePosting,
            modalActionNo: this.deleteToggle,
            modalActionYes: this.deleteReward,
            MainMessage: REWARD_DELETE_MSG,
            SubMessage: REWARD_DELETE_SUB_MSG,
        }
        return (
            <div className="sw_reward">
                {DeleteModalOpen && <PromptModal {...DeleteModalProps} />}
                {addEditModalOpen && this.addEditRewadModal()}
                <Row>
                    <Col md={12}>
                        <h2 className="h2-cls float-left animate-left">Manage Reward</h2>
                        <Button
                            onClick={() => this.addEditRewadModalToggle(1, '')}
                            className="btn-secondary-outline float-right animate-right">
                            Add Reward
                        </Button>
                    </Col>
                </Row>
                <Row className="mt-30">
                    <Col md={12} className="table-responsive common-table">
                        <Table className="animate-top">
                            <thead>
                                <tr>
                                    <th>Prize</th>
                                    <th>Amount</th>
                                    <th>Result text</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            {
                                Total > 0 ?
                                    _.map(RewardList, (item, idx) => {
                                        return (
                                            <tbody key={idx}>
                                                <tr>
                                                    <td>
                                                        {item.prize_type == '' && '--'}
                                                        {item.prize_type == '0' && 'Bonus'}
                                                        {item.prize_type == '1' && 'Real Cash'}
                                                        {item.prize_type == '2' && 'Coin'}
                                                    </td>
                                                    <td>{item.amount == '0' ? '--' : item.amount}</td>
                                                    <td>{item.result_text}</td>
                                                    <td>
                                                        <span className={`${item.status == '1' ? 'text-green' : 'text-red'}`}>
                                                            {item.status == '1' && 'Active'}
                                                            {item.status == '0' && 'Inactive'}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <i
                                                            onClick={() => this.deleteToggle(item.scratch_card_id)}
                                                            className="icon-delete"></i>
                                                        <i
                                                            onClick={() => this.addEditRewadModalToggle(2, item)}
                                                            className="icon-edit ml-4"></i>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        )
                                    })
                                    :
                                    <tbody>
                                        <tr>
                                            <td colSpan='22'>
                                                {(Total == 0 && !ListPosting) ?
                                                    <div className="no-records">No Record Found.</div>
                                                    :
                                                    <Loader />
                                                }
                                            </td>
                                        </tr>
                                    </tbody>
                            }
                        </Table>
                    </Col>
                </Row>
                <Row>
                    <Col md={12}>
                        {
                            Total > PERPAGE &&
                            (<div className="custom-pagination float-right mt-5">
                                <Pagination
                                    activePage={CURRENT_PAGE}
                                    itemsCountPerPage={PERPAGE}
                                    totalItemsCount={Total}
                                    pageRangeDisplayed={5}
                                    onChange={e => this.handlePageChange(e)}
                                />
                            </div>)
                        }
                    </Col>
                </Row>
            </div>
        )
    }
}
export default Reward