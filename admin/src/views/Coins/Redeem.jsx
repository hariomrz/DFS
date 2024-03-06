import React, { Component, Fragment } from "react";
import { Row, Col, TabContent, TabPane, Nav, NavItem, NavLink, Input, InputGroup, InputGroupAddon, InputGroupText, Table, UncontrolledDropdown, DropdownToggle, DropdownMenu, DropdownItem, Button, Modal, ModalBody } from 'reactstrap';
import Images from "../../components/images";
import _ from 'lodash';
import WSManager from '../../helper/WSManager';
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import moment from 'moment';
import Pagination from "react-js-pagination";
import ActionRequestModal from '../../components/ActionRequestModal/ActionRequestModal';
import Loader from '../../components/Loader';
import HF from '../../helper/HelperFunction';
class Redeem extends Component {
    constructor() {
        super()
        this.state = {
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            HIS_CURRENT_PAGE: 1,
            HIS_PER_PAGE: 10,
            REP_CURRENT_PAGE: 1,
            REP_PER_PAGE: NC.ITEMS_PERPAGE,
            Total: 0,
            activeTab: '1',
            filterType: 1,
            TotalRecords: 0,
            TotalHistory: 0,
            RewardList: [],
            RewardListPosting: false,
            RewardHistoryList: [],
            RewardHistoryItem: [],
            ReportRewardList: [],
            ActionPopupOpen: false,
            HistoryModalOpen: false,
            formValid: false,
            ValueMSg: true,
            DetailMSg: true,
            CoinsMSg: true,
            RewardType: '1',
            Value: '',
            Detail: '',
            Coins: '',
            ScreenView: '',
            RewardStatus: 1,
            ActionMsgStatus: 0,
            HistoryPosting: false,
            CoinRewardId: 0,
            ReportTotal: 0,
        }
    }

    componentDidMount() {
        this.getRewardList()
    }
    addReward = () => {
        this.setState({ formValid: false })
        let { Value, Detail, Coins, RewardType, image_name } = this.state
        let params = {
            value: Value,
            detail: Detail,
            redeem_coins: Coins,
            type: RewardType,
            image: image_name,
        }
        WSManager.Rest(NC.baseURL + NC.ADD_REWARD, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.getRewardList()
                notify.show(Response.message, 'success', 5000)
                this.setState({
                    formValid: true,
                    ValueMSg: true,
                    DetailMSg: true,
                    CoinsMSg: true,
                    RewardType: '1',
                    Value: '',
                    Detail: '',
                    Coins: '',
                    fileName: null
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    getRewardList() {
        this.setState({ RewardListPosting: true })
        let { PERPAGE, CURRENT_PAGE } = this.state
        let params = {
            status: parseInt(this.state.activeTab),
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
        }
        WSManager.Rest(NC.baseURL + NC.GET_REWARD_LIST, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    RewardListPosting: false,
                    RewardList: Response.data.reward_list,
                    Total: Response.data.total,
                    NextOffset: Response.data.next_offset,
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    toggle(tab) {
        if (this.state.activeTab !== tab) {
            this.setState({ activeTab: tab }, () => {
                if (this.state.activeTab == '0' || this.state.activeTab == '1') {
                    this.setState({
                        PERPAGE: NC.ITEMS_PERPAGE,
                        CURRENT_PAGE: 1,
                    }, this.getRewardList)
                }
                if (this.state.activeTab == '3')
                    this.getReportsByStatus()
            });
        }
    }
    filterReport = (flag) => {
        this.setState({ filterType: flag, REP_CURRENT_PAGE: 1 }, () => {
            this.getReportsByStatus()
        })
    }

    exportRewardListByStatus = () => {
        var query_string = '?status=' + this.state.filterType;
        let sessionKey = WSManager.getToken();
        query_string += "&Sessionkey" + "=" + sessionKey;

        window.open(NC.baseURL + NC.EXPORT_REWARD_LIST_BY_STATUS + query_string, '_blank');
    }

    getReportsByStatus() {
        let { filterType, REP_CURRENT_PAGE, REP_PER_PAGE } = this.state
        let params = {
            status: filterType,
            items_perpage: REP_PER_PAGE,
            current_page: REP_CURRENT_PAGE,
        }
        WSManager.Rest(NC.baseURL + NC.GET_REWARD_LIST_BY_STATUS, params).then(ResponseJson => {

            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    ReportRewardList: ResponseJson.data.list,
                    ReportTotal: ResponseJson.data.total,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000)
        })
    }

    handlePageChange(current_page) {
        let { activeTab } = this.state
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            if (activeTab == "1" || activeTab == "0")
                this.getRewardList()
            if (activeTab == "3")
                this.getReportsByStatus()
        });
    }

    handleHistoryPageChange(current_page) {
        this.setState({
            HIS_CURRENT_PAGE: current_page
        }, () => {
            this.getRewardHistory()
        });
    }

    handleReportPageChange(current_page) {
        this.setState({
            REP_CURRENT_PAGE: current_page
        }, () => {
            this.getReportsByStatus()
        });
    }

    //function to toggle reward modal component
    toggleActionPopup = (id, idx, ActionStaus) => {
        let msg = ActionStaus ? 'Inactive' : 'Active'
        this.setState({
            Message: 'Are you sure you want to ' + msg + ' this coupon ?',
            ScreenView: 'Coupon',
            ActionMsgStatus: ActionStaus,
            indexVal: idx,
            RewardID: id,
            ActionPopupOpen: !this.state.ActionPopupOpen
        })
    }

    //function to active inactive reward request
    modalActioCallback = () => {
        this.setState({ YesPosting: true })
        let { indexVal, RewardList, ActionMsgStatus, RewardID } = this.state
        let params = {
            status: ActionMsgStatus ? 0 : 1,
            coin_reward_id: RewardID
        }
        WSManager.Rest(NC.baseURL + NC.UPDATE_REWARD_STATUS, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, 'success', 5000)
                _.remove(RewardList, (item, idx) => {
                    return idx == indexVal
                })
                this.setState({
                    RewardList,
                    ActionPopupOpen: !this.state.ActionPopupOpen
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
            this.setState({ YesPosting: false })
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    //function to toggle report modal component
    toggleReportActionPopup = (coin_reward_history_id, RepIdx) => {
        this.setState({
            Message: 'Are you sure you want to Active this coupon ?',
            ScreenView: 'Report',
            ReportIndex: RepIdx,
            CoinRewardHistoryId: coin_reward_history_id,
            ActionPopupOpen: !this.state.ActionPopupOpen
        })
    }

    //function to active inactive reward request
    modalReportActionCallback = () => {
        let { ReportIndex, ReportRewardList, CoinRewardHistoryId } = this.state
        let params = {
            status: 1,
            coin_reward_history_id: CoinRewardHistoryId
        }
        WSManager.Rest(NC.baseURL + NC.APPROVE_REWARD_REQUEST, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, 'success', 5000)
                _.remove(ReportRewardList, (item, idx) => {
                    return idx == ReportIndex
                })
                this.setState({
                    ReportRewardList,
                    ActionPopupOpen: !this.state.ActionPopupOpen
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }


    toggleHistoryModal = (coin_reward_id, status) => {
        this.setState({
            HistoryModalOpen: !this.state.HistoryModalOpen,
            RewardStatus: status,
            CoinRewardId: coin_reward_id,
        }, () => {
            if (this.state.HistoryModalOpen)
                this.getRewardHistory()
        })
    }

    getRewardHistory = () => {
        this.setState({ HistoryPosting: true })
        let { HIS_CURRENT_PAGE, HIS_PER_PAGE, CoinRewardId } = this.state
        let params = {
            coin_reward_id: CoinRewardId,
            items_perpage: HIS_PER_PAGE,
            current_page: HIS_CURRENT_PAGE,
        }
        WSManager.Rest(NC.baseURL + NC.GET_REWARD_HISTORY, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    HistoryPosting: false,
                    RewardHistoryItem: ResponseJson.data,
                    RewardHistoryList: ResponseJson.data.history,
                    TotalHistory: ResponseJson.data.total
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000)
        })
    }

    couponHistoryModal() {
        let { HIS_CURRENT_PAGE, PERPAGE, RewardHistoryList, RewardHistoryItem, TotalHistory, RewardStatus, HistoryPosting } = this.state
        return (
            <Modal
                isOpen={this.state.HistoryModalOpen}
                className="modal-md coupon-history"
                toggle={() => this.toggleHistoryModal('', RewardStatus)}
            >
                <ModalBody>
                    <Row>
                        <Col md={12}>
                            <div className="header-wrapper">
                                <figure className="history-img">
                                    {
                                        !_.isEmpty(RewardHistoryItem.reward_detail) && (
                                            RewardHistoryItem.reward_detail.type == "1" ?
                                                <i className="icon-color icon-bonus1"></i>
                                                :
                                                RewardHistoryItem.reward_detail.type == "2" ?
                                                    <i className="icon-color icon-currency">{HF.getCurrencyCode()}</i>
                                                    :
                                                    <img src={!_.isEmpty(RewardHistoryItem.reward_detail) ? NC.S3 + NC.COINS + RewardHistoryItem.reward_detail.image : Images.no_image} className="img-cover" alt="" />
                                        )
                                    }

                                </figure>
                                <div className="history-info">
                                    <div>
                                        <div className="worth">
                                            {
                                                !_.isEmpty(RewardHistoryItem.reward_detail)
                                                    ?
                                                    <Fragment>
                                                        {(RewardHistoryItem.reward_detail.type == '1' && RewardHistoryItem.reward_detail.type != '3') &&
                                                            <Fragment>
                                                                Worth{' '}<span className="icon-bonus1"></span>
                                                                {RewardHistoryItem.reward_detail.value}
                                                            </Fragment>}

                                                        {(RewardHistoryItem.reward_detail.type == '2' && RewardHistoryItem.reward_detail.type != '3') &&
                                                            <Fragment>
                                                                Worth{' '}<span className="icon-color icon-currency">{HF.getCurrencyCode()}</span>
                                                                {RewardHistoryItem.reward_detail.value}
                                                            </Fragment>
                                                        }
                                                    </Fragment>
                                                    :
                                                    '0'
                                            }
                                        </div>
                                        <div className="card-text">{!_.isEmpty(RewardHistoryItem.reward_detail) ? RewardHistoryItem.reward_detail.detail : '0'}</div>
                                    </div>
                                    <ul className="coupon-avail-list">
                                        <li className="coupon-avail-item">
                                            <label htmlFor="Redeemedby">Redeemed by</label>
                                            <div className="numbers">{RewardHistoryItem.redeem_by}</div>
                                        </li>
                                        <li className="coupon-avail-item">
                                            <label htmlFor="Redeemedby">Coins Redeem per user</label>
                                            <div className="numbers">{!_.isEmpty(RewardHistoryItem.reward_detail) ? RewardHistoryItem.reward_detail.redeem_coins : '0'}</div>
                                        </li>
                                        <li className="coupon-avail-item">
                                            <label htmlFor="Redeemedby">Total Coins Redeem</label>
                                            <div className="numbers">{RewardHistoryItem.total_coin_redeem}</div>
                                        </li>
                                    </ul>
                                </div>
                                <div className={`reward-status ${RewardStatus ? '' : 'inactive'}`}>
                                    {RewardStatus ? 'Active' : 'Inactive'}
                                </div>
                            </div>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <div className="table-responsive common-table">
                                <div className="tbl-min-hgt">
                                    <Table>
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Username</th>
                                            </tr>
                                        </thead>
                                        {
                                            TotalHistory > 0 ?
                                                _.map(RewardHistoryList, (item, idx) => {
                                                    console.log('idssss', item.added_date)
                                                    return (
                                                        <tbody key={idx}>
                                                            <tr>
                                                                <td>
                                                                    {/* {moment(item.added_date).format("DD MMM YYYY")} */}
                                                                    {HF.getFormatedDateTime(item.added_date, "DD MMM YYYY")}
                                                                </td>
                                                                <td className="text-ellipsis">{item.username}</td>
                                                            </tr>
                                                        </tbody>
                                                    )
                                                })
                                                :
                                                <tbody>
                                                    <tr>
                                                        <td colSpan="8">
                                                            {(TotalHistory == 0 && !HistoryPosting) ?
                                                                <div className="no-records">
                                                                    {NC.NO_RECORDS}</div>
                                                                :
                                                                <Loader />
                                                            }
                                                        </td>
                                                    </tr>
                                                </tbody>
                                        }
                                    </Table>
                                </div>
                                {TotalHistory > 0 && (
                                    <div className="custom-pagination">
                                        <Pagination
                                            activePage={HIS_CURRENT_PAGE}
                                            itemsCountPerPage={PERPAGE}
                                            totalItemsCount={TotalHistory}
                                            pageRangeDisplayed={5}
                                            onChange={e => this.handleHistoryPageChange(e)}
                                        />
                                    </div>
                                )
                                }
                            </div>
                        </Col>
                    </Row>
                </ModalBody>
            </Modal>
        )
    }
    handleInputChange = (event) => {
        let name = event.target.name
        let value = event.target.value

        this.setState({ [name]: value },
            () => this.validateForm(name, value)
        )
    }

    validateForm = (name, value) => {
        let ValueValid = this.state.Value
        let DetailValid = this.state.Detail
        let CoinsValid = this.state.Coins
        let FileNameValid = this.state.fileName

        switch (name) {
            case 'Value':
                ValueValid = (value.length > 0 && value.length <= 7 && value.match(/^([1-9][0-9]{0,7})$/)) ? true : false;
                break;
            case 'Detail':
                DetailValid = (value.length > 0 && value.length <= 40) ? true : false;
                this.setState({ DetailMSg: DetailValid })
                break;
            case 'Coins':
                CoinsValid = (value.length > 0 && value.length <= 7 && value.match(/^([1-9][0-9]{0,7})$/)) ? true : false;
                this.setState({ CoinsMSg: CoinsValid })
                break;

            default:
                break;
        }

        if (this.state.RewardType == '3') {
            this.setState({
                formValid: (ValueValid && DetailValid && CoinsValid && !_.isUndefined(FileNameValid) && !_.isNull(FileNameValid))
            })
        } else {
            this.setState({
                formValid: (ValueValid && DetailValid && CoinsValid)
            })
        }
    }

    resetChanges = () => {
        this.setState({
            formValid: false,
            ValueMSg: true,
            DetailMSg: true,
            CoinsMSg: true,
            RewardType: '1',
            Value: '',
            Detail: '',
            Coins: '',
            fileName: null
        })
    }

    onChangeImage = (event) => {
        this.setState({
            fileName: URL.createObjectURL(event.target.files[0]),
        }, function () {
            this.validateForm()
        });
        const file = event.target.files[0];
        if (!file) {
            return;
        }
        var data = new FormData();
        data.append("file", file);
        WSManager.multipartPost(NC.baseURL + NC.DO_UPLOAD_REWARD_IMAGE, data)
            .then(Response => {
                if (Response.response_code == NC.successCode) {
                    this.setState({
                        image_name: Response.data.image_name
                    });
                } else {
                    this.setState({
                        fileName: null
                    }, this.validateForm);
                }
            }).catch(error => {
                notify.show(NC.SYSTEM_ERROR, "error", 3000);
            });
    }

    resetFile = () => {
        this.setState({
            fileName: null
        }, function () {
            this.validateForm()
        });
    }

    render() {
        let { Message, ScreenView, REP_CURRENT_PAGE, REP_PER_PAGE, ReportTotal, ReportRewardList, RewardID, Total, fileName, RewardType, Coins, ValueMSg, DetailMSg, CoinsMSg, formValid, activeTab, filterType, RewardList, CURRENT_PAGE, PERPAGE, ActionPopupOpen, Value, Detail, RewardListPosting, YesPosting } = this.state
        const ActionCallback = {
            Posting: YesPosting,
            Message: Message,
            Screen: ScreenView,
            RewardID: RewardID,
            modalCallback: this.toggleActionPopup,
            modalActioCallback: this.modalActioCallback,
            modalReportActionCallback: this.modalReportActionCallback,
            ActionPopupOpen: ActionPopupOpen,
        }
        return (
            <React.Fragment>
                <Row><h2 className="h2-cls mt-4">Redeem / Rewards</h2></Row>
                <Row className="user-navigation redeem-screen">
                    <div className="w-100">
                        <Nav tabs>
                            <NavItem
                                className={activeTab === '1' ? "active" : ""}
                                onClick={() => { this.toggle('1'); }}
                            >
                                <NavLink>
                                    Active
                                </NavLink>
                            </NavItem>
                            <NavItem
                                className={activeTab === '0' ? "active" : ""}
                                onClick={() => { this.toggle('0'); }}
                            >
                                <NavLink>
                                    Inactive
                                </NavLink>
                            </NavItem>
                            <NavItem
                                className={activeTab === '3' ? "active" : ""}
                                onClick={() => { this.toggle('3'); }}
                            >
                                <NavLink>
                                    Reports
                                </NavLink>
                            </NavItem>

                        </Nav>
                        <TabContent activeTab={activeTab}>
                            <TabPane tabId="1" className="animated fadeIn">
                                <div className="add-rewards">
                                    <Row>
                                        <Col md="8">
                                            <figure className="upload-img">
                                                {!_.isEmpty(fileName) ?
                                                    <Fragment>
                                                        <i onClick={this.resetFile} className="icon-close"></i>
                                                        <img className="img-cover" src={fileName} />
                                                    </Fragment>
                                                    :
                                                    <Fragment>
                                                        {
                                                            RewardType === '1' ?
                                                                <i className="icon-color icon-bonus1"></i>
                                                                :
                                                                RewardType === '2' ?
                                                                    <i className="icon-color icon-currency">{HF.getCurrencyCode()}</i>
                                                                    :
                                                                    <Fragment>
                                                                        <Input
                                                                            accept="image/x-png,image/gif,image/jpeg,image/bmp,image/jpg"
                                                                            type="file"
                                                                            name='gift_image'
                                                                            id="gift_image"
                                                                            className="gift_image"
                                                                            onChange={this.onChangeImage}
                                                                        />
                                                                        <i onChange={this.onChangeImage} className="icon-camera"></i>
                                                                    </Fragment>
                                                        }
                                                    </Fragment>
                                                }
                                            </figure>

                                            <div className="input-box">
                                                <ul className="coupons-option-list">
                                                    <li className="coupons-option-item">
                                                        <div className="custom-radio">
                                                            <input
                                                                type="radio"
                                                                className="custom-control-input"
                                                                name="RewardType"
                                                                value="1"
                                                                checked={RewardType === '1'}
                                                                onChange={this.handleInputChange}
                                                            />
                                                            <label className="custom-control-label">
                                                                <span className="input-text">Bonus Cash</span>
                                                            </label>
                                                        </div>
                                                    </li>
                                                    <li className="coupons-option-item">
                                                        <div className="custom-radio">
                                                            <input
                                                                type="radio"
                                                                className="custom-control-input"
                                                                name="RewardType"
                                                                value="2"
                                                                checked={RewardType === '2'}
                                                                onChange={this.handleInputChange}
                                                            />
                                                            <label className="custom-control-label">
                                                                <span className="input-text">Real Cash</span>
                                                            </label>
                                                        </div>

                                                    </li>
                                                    <li className="coupons-option-item">
                                                        <div className="custom-radio">
                                                            <input
                                                                type="radio"
                                                                className="custom-control-input"
                                                                name="RewardType"
                                                                value="3"
                                                                checked={RewardType === '3'}
                                                                onChange={this.handleInputChange}
                                                            />
                                                            <label className="custom-control-label">
                                                                <span className="input-text">Gift Coupon</span>
                                                            </label>
                                                        </div>

                                                    </li>
                                                </ul>
                                                <div className="mt-3 mb-3">
                                                    <label htmlFor="Value">Value</label>
                                                    <Input
                                                        maxLength={20}
                                                        name='Value'
                                                        value={Value}
                                                        onChange={this.handleInputChange}
                                                    />
                                                    {!ValueMSg &&
                                                        <span className="color-red">
                                                            Value can not be empty.
                                                        </span>
                                                    }
                                                </div>
                                                <div className="mb-3">
                                                    <label htmlFor="Detail">Detail</label>
                                                    <Input
                                                        maxLength={40}
                                                        name='Detail'
                                                        value={Detail}
                                                        onChange={this.handleInputChange}
                                                    />
                                                    {!DetailMSg &&
                                                        <span className="color-red">
                                                            Please enter valid details.
                                                        </span>
                                                    }
                                                </div>
                                                <div className="redeem-box clearfix">
                                                    <label htmlFor="Redeem">Redeem with</label>
                                                    <div className="redeem float-left">
                                                        <InputGroup>
                                                            <InputGroupAddon addonType="prepend">
                                                                <InputGroupText>
                                                                    <img src={Images.REWARD_ICON} alt="" />
                                                                </InputGroupText>
                                                            </InputGroupAddon>
                                                            <Input
                                                                placeholder="Enter Coins"
                                                                maxLength={7}
                                                                name='Coins'
                                                                value={Coins}
                                                                onChange={this.handleInputChange}
                                                            />
                                                        </InputGroup>
                                                        {!CoinsMSg &&
                                                            <span className="color-red">
                                                                Please enter valid number only.
                                                            </span>
                                                        }
                                                    </div>
                                                    <div className="publish-box float-right">
                                                        <div onClick={this.resetChanges} className="refresh icon-reset"></div>
                                                        <Button
                                                            disabled={!formValid}
                                                            className="btn-secondary-outline publish-btn"
                                                            onClick={this.addReward}
                                                        >Publish</Button>
                                                    </div>
                                                </div>
                                            </div>
                                        </Col>
                                        <Col md="4">
                                            <div className="img-preview-box">
                                                <Fragment>
                                                    {!_.isEmpty(fileName) || !_.isEmpty(Value) || !_.isEmpty(Coins) ?
                                                        <div className="reward-card">
                                                            <div className="left-div">
                                                                <Fragment>
                                                                    <div className="card-img-wrapper">
                                                                        {RewardType == '1' ?
                                                                            <span className="icon-color icon-bonus1"></span>
                                                                            :
                                                                            RewardType == '2' ?
                                                                                <span className="icon-color icon-currency">{HF.getCurrencyCode()}</span>
                                                                                :
                                                                                !_.isEmpty(fileName) &&
                                                                                <img src={fileName} className="img-cover" alt="" />
                                                                        }
                                                                    </div>
                                                                    <div className="reward-info">
                                                                        <div className="worth">
                                                                            {(RewardType == '1' && RewardType != '3') &&
                                                                                <Fragment>
                                                                                    <span className="icon-bonus1"></span>
                                                                                    {Value}
                                                                                </Fragment>
                                                                            }
                                                                            {(RewardType == '2' && RewardType != '3') &&
                                                                                <Fragment>
                                                                                    <span className="icon-color icon-currency">{HF.getCurrencyCode()}</span>
                                                                                    {Value}
                                                                                </Fragment>
                                                                            }
                                                                        </div>
                                                                        <div className="card-text xtext-ellipsis">{Detail}</div>
                                                                    </div>
                                                                </Fragment>


                                                            </div>
                                                            <div className="card-status">
                                                                <div className="text-right">
                                                                    <i className="icon-inactive"></i>
                                                                </div>
                                                                <div className="redeem-with">Redeem with </div>
                                                                {
                                                                    Coins && (
                                                                        <div className="redeem-btn">
                                                                            <img src={Images.REWARD_ICON} alt="" />
                                                                            &nbsp;
                                                                            {HF.getNumberWithCommas(Coins)}
                                                                        </div>
                                                                    )
                                                                }
                                                            </div>
                                                        </div>
                                                        :
                                                        <span className="preview-text">Your Preview will<br /> appear here</span>
                                                    }

                                                </Fragment>
                                            </div>
                                        </Col>
                                    </Row>
                                </div>
                                <div className="rewards-list">
                                    <Row>
                                        {
                                            Total > 0 ?
                                                _.map(RewardList, (item, idx) => {
                                                    return (
                                                        <Col md={4} key={idx}>
                                                            <div className="reward-card">
                                                                <div className="left-div">

                                                                    <div>
                                                                        <div className="card-img-wrapper">
                                                                            {item.type == '1' ?
                                                                                <span className="icon-color icon-bonus1"></span>
                                                                                :
                                                                                item.type == '2' ?
                                                                                    <i className="icon-color icon-currency">{HF.getCurrencyCode()}</i>
                                                                                    :
                                                                                    <img src={NC.S3 + NC.COINS + item.image} className="img-cover" alt="" />
                                                                            }

                                                                        </div>
                                                                        <div className="reward-info">
                                                                            {this.couponHistoryModal()}
                                                                            <div
                                                                                onClick={() => this.toggleHistoryModal(item.coin_reward_id.$oid, item.status)}
                                                                                className="worth">
                                                                                {(item.type == '1' && item.type != '3') &&
                                                                                    <Fragment>
                                                                                        Worth {' '}<span className="icon-bonus1"></span>
                                                                                        {item.value}
                                                                                    </Fragment>
                                                                                }
                                                                                {(item.type == '2' && item.type != '3') &&
                                                                                    <Fragment>
                                                                                        Worth {' '}<span className="icon-currency">{HF.getCurrencyCode()}</span>
                                                                                        {item.value}
                                                                                    </Fragment>
                                                                                }
                                                                            </div>
                                                                            <div onClick={() => this.toggleHistoryModal(item.coin_reward_id.$oid, item.status)} className="card-text xtext-ellipsis">{item.detail}</div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div className="card-status">
                                                                    <div className="text-right">
                                                                        <i className="icon-inactive icon-style"
                                                                            onClick={() => this.toggleActionPopup(item.coin_reward_id.$oid, idx, item.status)}
                                                                        ></i>
                                                                    </div>
                                                                    <div className="redeem-with">Redeem with</div>
                                                                    <div className="redeem-btn">
                                                                        <img src={Images.REWARD_ICON} alt="" />
                                                                        &nbsp;
                                                                        {HF.getNumberWithCommas(item.redeem_coins)}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </Col>
                                                    )
                                                })
                                                :
                                                <Col md={12}>
                                                    {(Total == 0 && !RewardListPosting) ?
                                                        <div className="no-records">No Rewards added.</div>
                                                        :
                                                        <Loader />
                                                    }
                                                </Col>
                                        }
                                    </Row>
                                    {
                                        Total > PERPAGE && (
                                            <div className="custom-pagination lobby-paging">
                                                <Pagination
                                                    activePage={CURRENT_PAGE}
                                                    itemsCountPerPage={PERPAGE}
                                                    totalItemsCount={Total}
                                                    pageRangeDisplayed={5}
                                                    onChange={e => this.handlePageChange(e)}
                                                />
                                            </div>
                                        )
                                    }
                                </div>
                                <ActionRequestModal {...ActionCallback} />
                            </TabPane>
                            {
                                (activeTab == '0') &&
                                <TabPane tabId="0" className="animated fadeIn">
                                    <div className="rewards-list">
                                        <Row>
                                            {
                                                Total > 0 ?
                                                    _.map(RewardList, (item, idx) => {
                                                        return (
                                                            <Col md={4} key={idx}>
                                                                <div className="reward-card">
                                                                    <div className="left-div">

                                                                        <div>
                                                                            <div className="card-img-wrapper">
                                                                                {item.type == '1' ?
                                                                                    <span className="icon-color icon-bonus1"></span>
                                                                                    :
                                                                                    item.type == '2' ?
                                                                                        <span className="icon-color icon-currency">{HF.getCurrencyCode()}</span>
                                                                                        :
                                                                                        <img src={NC.S3 + NC.COINS + item.image} className="img-cover" alt="" />
                                                                                }
                                                                            </div>
                                                                            <div className="reward-info">
                                                                                {this.couponHistoryModal()}
                                                                                <div
                                                                                    onClick={() => this.toggleHistoryModal(item.coin_reward_id.$oid, item.status)}
                                                                                    className="worth">
                                                                                    {(item.type == '1' && item.type != '3') &&
                                                                                        <Fragment>
                                                                                            Worth {' '}<span className="icon-bonus1"></span>
                                                                                            {item.value}
                                                                                        </Fragment>
                                                                                    }
                                                                                    {(item.type == '2' && item.type != '3') &&
                                                                                        <Fragment>
                                                                                            Worth {' '}<span className="icon-currency">{HF.getCurrencyCode()}</span>
                                                                                            {item.value}
                                                                                        </Fragment>
                                                                                    }
                                                                                </div>
                                                                                <div onClick={() => this.toggleHistoryModal(item.coin_reward_id.$oid, item.status)} className="card-text">{item.detail}</div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div className="card-status">
                                                                        <div className="text-right">
                                                                            <i className="icon-inactive icon-style"
                                                                                onClick={() => this.toggleActionPopup(item.coin_reward_id.$oid, idx, item.status)}
                                                                            ></i>
                                                                        </div>
                                                                        <div className="redeem-with">Redeem with</div>
                                                                        <div className="redeem-btn">
                                                                            <img src={Images.REWARD_ICON} alt="" />
                                                                            &nbsp;
                                                                            {HF.getNumberWithCommas(item.redeem_coins)}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </Col>
                                                        )
                                                    })
                                                    :
                                                    <Col md={12}>
                                                        {(Total == 0 && !RewardListPosting) ?
                                                            <div className="no-records">{NC.NO_RECORDS}</div>
                                                            :
                                                            <Loader />
                                                        }
                                                    </Col>
                                            }
                                        </Row>
                                    </div>
                                    {
                                        Total > PERPAGE && (
                                            <div className="custom-pagination lobby-paging">
                                                <Pagination
                                                    activePage={CURRENT_PAGE}
                                                    itemsCountPerPage={PERPAGE}
                                                    totalItemsCount={Total}
                                                    pageRangeDisplayed={5}
                                                    onChange={e => this.handlePageChange(e)}
                                                />
                                            </div>
                                        )}
                                </TabPane>
                            }
                            {
                                activeTab == '3' &&
                                <TabPane tabId="3" className="animated fadeIn">
                                    <div className="reports-box">
                                        <Row>
                                            <Col md={12}>
                                                <div>
                                                    <ul className="reports-filter-list">
                                                        <li className="reports-filter-item">
                                                            <div className={`filter-status ${filterType == 1 ? 'active' : ''}`} onClick={() => this.filterReport('1')}>
                                                                Completed</div>
                                                        </li>
                                                        <li className="reports-filter-item">
                                                            <div className={`filter-status ${filterType == 0 ? 'active' : ''}`} onClick={() => this.filterReport('0')}>
                                                                Pending</div>
                                                        </li>
                                                    </ul>
                                                    <div>
                                                        <i className="export-list icon-export" onClick={e => this.exportRewardListByStatus()}></i>
                                                    </div>
                                                </div>
                                            </Col>
                                        </Row>
                                        <Row>
                                            <Col md={12} className="table-responsive common-table">
                                                <Table>
                                                    <thead>
                                                        <tr>
                                                            <th className="left-th pl-5">Date</th>
                                                            <th>Unique ID</th>
                                                            <th>Username</th>                                                            
                                                            <th>Event</th>
                                                            <th className={`${filterType == 1 ? 'right-th' : ''}`}>Value</th>
                                                            {
                                                                filterType != 1 &&
                                                                <th className="right-th">
                                                                    Status
                                                                </th>
                                                            }
                                                        </tr>
                                                    </thead>
                                                    {
                                                        ReportTotal > 0 ?
                                                            _.map(ReportRewardList, (item, idx) => {
                                                                console.log('itemitem', item)
                                                                return (
                                                                    <tbody key={idx}>
                                                                        <tr>
                                                                            <td>
                                                                                {/* {moment(item.added_date).format("DD MMM YYYY")} */}
                                                                                {HF.getFormatedDateTime(item.added_date, "DD MMM YYYY")}
                                                                            </td>
                                                                            <td>{item.user_id}</td>
                                                                            <td className="cursor-p" onClick={() => this.props.history.push("/profile/" + item.user_unique_id)}>{item.username}</td>
                                                                            <td>
                                                                                {
                                                                                    item.reward_detail[0].status === '1' ?
                                                                                        'Bonus Cash'
                                                                                        :
                                                                                        item.reward_detail[0].status === '2' ?
                                                                                            'Real Cash'
                                                                                            :
                                                                                            item.reward_detail[0].detail
                                                                                }
                                                                            </td>
                                                                            <td><div>
                                                                                <span className="icon-color">{HF.getCurrencyCode()}</span>
                                                                                <span className="price">{' '}
                                                                                    {/* {item.reward_detail[0].value} */}

                                                                                    {HF.getNumberWithCommas(item.reward_detail[0].value)}
                                                                                </span>
                                                                            </div>
                                                                            </td>
                                                                            {
                                                                                filterType != 1 &&
                                                                                <td>
                                                                                    <div>
                                                                                        <UncontrolledDropdown>
                                                                                            <DropdownToggle tag="span" caret={1}>
                                                                                                Not Sent
                                                                                            </DropdownToggle>
                                                                                            <DropdownMenu>
                                                                                                <DropdownItem onClick={() => this.toggleReportActionPopup(item.coin_reward_history_id.$oid, idx)}>Acknowledge and sent</DropdownItem>
                                                                                            </DropdownMenu>
                                                                                        </UncontrolledDropdown>
                                                                                    </div>
                                                                                </td>
                                                                            }
                                                                        </tr>
                                                                    </tbody>
                                                                )
                                                            })
                                                            :
                                                            <tbody>
                                                                <tr>
                                                                    <td colSpan="12">
                                                                        <div className="no-records">{NC.NO_RECORDS}</div>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                    }
                                                </Table>
                                            </Col>
                                        </Row>
                                    </div>
                                    {ReportTotal > 0 && (
                                        <div className="custom-pagination lobby-paging">
                                            <Pagination
                                                activePage={REP_CURRENT_PAGE}
                                                itemsCountPerPage={REP_PER_PAGE}
                                                totalItemsCount={ReportTotal}
                                                pageRangeDisplayed={5}
                                                onChange={e => this.handleReportPageChange(e)}
                                            />
                                        </div>
                                    )}
                                </TabPane>
                            }
                        </TabContent>
                    </div>
                </Row>
            </React.Fragment>
        )
    }
}
export default Redeem
