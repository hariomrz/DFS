import React, { Fragment } from "react";
import { Row, Col, TabContent, TabPane, Nav, NavItem, NavLink, Input, InputGroup, InputGroupAddon, InputGroupText, Button } from 'reactstrap';
import CoinCard from './CoinCard.jsx';
import BuyCoinReport from './BuyCoinReport.jsx';
import _ from 'lodash';
import Images from "../../components/images";
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import WSManager from '../../helper/WSManager';
import ActionRequestModal from '../../components/ActionRequestModal/ActionRequestModal';
import Pagination from "react-js-pagination";
import queryString from 'query-string';
import Loader from '../../components/Loader';
import { MODULE_NOT_ENABLE } from "../../helper/Message";
import HF from '../../helper/HelperFunction';
var globalThis = null;

var createReactClass = require('create-react-class');
var BuyCoin = createReactClass({
    getInitialState: function () {
        return {
            CURRENT_PAGE: 1,
            PERPAGE: NC.ITEMS_PERPAGE,
            activeTab: '1',
            RewardList: [],
            formValid: false,
            ValueMSg: true,
            TitleMSg: true,
            CoinsMSg: true,
            Value: '',
            Title: '',
            Coins: '',
            ActionPopupOpen: false,
            ListPosting: false,
            filter: '',
            sortField: 'coins',
            isDescOrder: 'true',
        }
    },

    componentDidMount: function () {
        if (HF.allowBuyCoin() != '1') {
            notify.show(MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }

        globalThis = this;
        let qString = queryString.parse(this.props.location.search)
        this.setState({
            activeTab: !_.isUndefined(qString.report) ? "3" : "1",
        }, () => {
            this.getCoinsList()
        })
    },

    getCoinsList: function () {
        this.setState({ ListPosting: true })
        let { PERPAGE, CURRENT_PAGE, activeTab, keyword, isDescOrder, sortField } = this.state
        let params =
        {
            "keyword": keyword,
            "current_page": CURRENT_PAGE,
            "items_perpage": PERPAGE,
            "sort_field": sortField,
            "sort_order": isDescOrder ? 'DESC' : 'ASC',
            "status": activeTab
        }

        WSManager.Rest(NC.baseURL + NC.PACKAGE_LIST, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    ListPosting: false,
                    RewardList: Response.data.result,
                    Total: Response.data.total,
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    },

    toggle: function (tab) {
        if (this.state.activeTab !== tab) {
            this.setState({ CURRENT_PAGE: 1, RewardList: [], activeTab: tab }, () => {
                this.getCoinsList()
            });
        }
    },

    handleInputChange: function (event) {
        let name = event.target.name
        let value = event.target.value

        this.setState({ [name]: value },
            () => this.validateForm(name, value)
        )
    },

    validateForm: function (name, value) {
        let { Value, Title, Coins } = this.state

        let ValueValid = false
        let TitleValid = false
        let CoinsValid = false
        if (Value.length > 0 && Value.length <= 7 && Value.match(/^([1-9][0-9]{0,7})$/)) {
            ValueValid = true
        }
        if (Coins.length > 0 && Coins.length <= 7 && Coins.match(/^([1-9][0-9]{0,7})$/)) {
            CoinsValid = true
        }
        if (Title.length > 2 && Title.length <= 25) {
            TitleValid = true
        }
        if (name === 'Value') {
            this.setState({ ValueMSg: ValueValid })
        }
        if (name === 'Coins') {
            this.setState({ CoinsMSg: CoinsValid })
        }
        if (name === 'Title') {
            this.setState({ TitleMSg: TitleValid })
        }

        this.setState({
            formValid: (ValueValid && CoinsValid && TitleValid)
        })
    },

    resetChanges: function () {
        this.setState({
            formValid: false,
            ValueMSg: true,
            TitleMSg: true,
            CoinsMSg: true,
            RewardType: '1',
            Value: '',
            Title: '',
            Coins: '',
        })
    },

    addPackage: function () {
        this.setState({ formValid: false })
        let { Value, Title, Coins } = this.state
        if (Title.length < 3 || Title.length > 25) {
            this.setState({ TitleMSg: false })
            return false
        }

        let params = {
            amount: Value,
            package_name: Title,
            coins: Coins,
        }
        WSManager.Rest(NC.baseURL + NC.ADD_PACKAGE, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.getCoinsList()
                notify.show(Response.message, 'success', 5000)
                this.setState({
                    formValid: true,
                    ValueMSg: true,
                    DetailMSg: true,
                    CoinsMSg: true,
                    Value: '',
                    Title: '',
                    Coins: '',
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    },

    //function to toggle reward modal component
    toggleActionPopup: function (id, idx, ActionStaus) {
        let msg = ActionStaus === "1" ? 'inactive' : 'active'
        this.setState({
            Message: 'Are you sure you want to ' + msg + ' this package ?',
            ScreenView: 'Coupon',
            ActionMsgStatus: ActionStaus,
            indexVal: idx,
            RewardID: id,
            ActionPopupOpen: !this.state.ActionPopupOpen
        })
    },

    //function to active inactive reward request
    modalActioCallback: function () {
        this.setState({ YesPosting: true })
        let { indexVal, RewardList, ActionMsgStatus, RewardID } = this.state
        let params = {
            status: ActionMsgStatus === "1" ? 0 : 1,
            package_id: RewardID
        }
        WSManager.Rest(NC.baseURL + NC.PACKAGE_UPDATE, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, 'success', 5000)
                _.remove(RewardList, (item, idx) => {
                    return idx == indexVal
                })
                this.setState({
                    RewardList,
                    ActionPopupOpen: false
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
            this.setState({ YesPosting: false })
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    },

    handlePageChange(current_page) {
        if (this.state.CURRENT_PAGE !== current_page) {
            this.setState({
                CURRENT_PAGE: current_page
            }, () => {
                this.getCoinsList()
            });
        }
    },

    getPagination() {
        let { Total, PERPAGE, CURRENT_PAGE } = this.state
        if (Total > PERPAGE) {
            return (
                <div className="custom-pagination mb-30">
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
    },

    handleSearch(keyword) {
        if (!_.isNull(keyword)) {
            this.setState({ CURRENT_PAGE: 1, keyword: keyword.target.value }, () => {
                this.SearchCodeReq()
            })
        }
    },

    SearchCodeReq() {
        this.getCoinsList()
    },

    sortByColumn(sortfiled, isDescOrder) {
        let Order = isDescOrder ? false : true
        this.setState({
            sortField: sortfiled,
            isDescOrder: Order,
            CURRENT_PAGE: 1,

        }, this.getCoinsList)
    },

    render() {
        let { activeTab, RewardList, Value, Title, Coins, CoinsMSg, TitleMSg, formValid, ValueMSg, YesPosting, Message, ScreenView, RewardID, ActionPopupOpen, Total, PERPAGE, CURRENT_PAGE, ListPosting, sortField, isDescOrder } = this.state
        const ActionCallback = {
            posting: YesPosting,
            Message: Message,
            Screen: ScreenView,
            RewardID: RewardID,
            modalCallback: this.toggleActionPopup,
            modalActioCallback: this.modalActioCallback,
            ActionPopupOpen: ActionPopupOpen,
        }

        const BCReportProps = {
            CURRENT_PAGE: CURRENT_PAGE,
            PERPAGE: PERPAGE,
            RewardList: RewardList,
            Total: Total,
            ListPosting: ListPosting,
            handlePageChange: this.handlePageChange,
            handleSearch: this.handleSearch,
            sortField: sortField,
            isDescOrder: isDescOrder,
            sortByColumn: this.sortByColumn,
        }

        return (
            <Fragment>
                <Row><h2 className="h2-cls mt-4">Buy Coins</h2></Row>
                <Row className="user-navigation xredeem-screen buy-coin-sc">
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
                                        <Col md="6">
                                            <div className="input-box">
                                                <div className="redeem-box clearfix mb-3">
                                                    <label htmlFor="Redeem">Coin Value</label>
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
                                                </div>
                                                <div className="redeem-box clearfix mb-3">
                                                    <label htmlFor="Redeem">Buy with</label>
                                                    <div className="redeem float-left">
                                                        <InputGroup>
                                                            <InputGroupAddon addonType="prepend">
                                                                <InputGroupText>
                                                                    <i className="icon-rupess"></i>
                                                                </InputGroupText>
                                                            </InputGroupAddon>
                                                            <Input
                                                                placeholder="Enter Value"
                                                                maxLength={7}
                                                                name='Value'
                                                                value={Value}
                                                                onChange={this.handleInputChange}
                                                            />
                                                        </InputGroup>
                                                        {!ValueMSg &&
                                                            <span className="color-red">
                                                                Please enter valid number only.
                                                        </span>
                                                        }
                                                    </div>
                                                </div>
                                                <div className="mb-3">
                                                    <label htmlFor="Title">Title</label>
                                                    <Input
                                                        placeholder="Enter Title"
                                                        maxLength={25}
                                                        name='Title'
                                                        value={Title}
                                                        onChange={this.handleInputChange}
                                                    />
                                                    {!TitleMSg &&
                                                        <span className="color-red">
                                                            Title should be between 3 to 25 character
                                                        </span>
                                                    }
                                                </div>
                                                <div className="redeem-box clearfix">
                                                    <div className="publish-box">
                                                        <div onClick={this.resetChanges} className="refresh icon-reset"></div>
                                                        <Button
                                                            disabled={!formValid}
                                                            className="btn-secondary-outline publish-btn"
                                                            onClick={this.addPackage}
                                                        >Publish</Button>
                                                    </div>
                                                </div>
                                            </div>
                                        </Col>
                                        <Col md="4">
                                            <div className="img-preview-box">
                                                <Fragment>
                                                    {!_.isEmpty(Value) || !_.isEmpty(Coins) ?
                                                        <div className="">
                                                            <CoinCard
                                                                Reward_Id={null}
                                                                Value={Coins}
                                                                Coins={Value}
                                                                Status={null}
                                                                PackageName={Title}
                                                                Preview={false}
                                                                indx={0}
                                                                actionPopupCall={globalThis.toggleActionPopup}
                                                            />
                                                        </div>
                                                        :
                                                        <span className="preview-text">Your Preview will<br /> appear here</span>
                                                    }
                                                </Fragment>
                                            </div>
                                        </Col>
                                        <Col md="2"></Col>
                                    </Row>
                                </div>
                                <div className="mb-5 clearfix">
                                    {
                                        Total > 0 ?
                                            _.map(RewardList, function (item, idx) {
                                                let props = {
                                                    Reward_Id: item.coin_package_id,
                                                    Value: item.coins,
                                                    Coins: item.amount,
                                                    Status: item.status,
                                                    PackageName: item.package_name,
                                                    Preview: true,
                                                    indx: idx,
                                                    actionPopupCall: globalThis.toggleActionPopup
                                                }
                                                return (
                                                    <div key={idx} className="card-align clearfix">
                                                        <CoinCard {...props} />
                                                    </div>
                                                )
                                            })
                                            :
                                            (Total == 0 && !ListPosting) ?
                                                <div className="no-records mt-30">
                                                    {NC.NO_RECORDS}</div>
                                                :
                                                <Loader />
                                    }
                                </div>
                                {this.getPagination()}
                            </TabPane>
                            {
                                (activeTab == '0') &&
                                <TabPane tabId="0" className="animated fadeIn">
                                    <div className="mb-5 clearfix">
                                        {
                                            Total > 0 ?
                                                _.map(RewardList, function (item, idx) {
                                                    let props = {
                                                        Reward_Id: item.coin_package_id,
                                                        Value: item.coins,
                                                        Coins: item.amount,
                                                        Status: item.status,
                                                        PackageName: item.package_name,
                                                        Preview: true,
                                                        indx: idx,
                                                        actionPopupCall: globalThis.toggleActionPopup
                                                    }
                                                    return (
                                                        <div key={idx} className="card-align">
                                                            <CoinCard {...props} />
                                                        </div>
                                                    )
                                                })
                                                :
                                                (Total == 0 && !ListPosting) ?
                                                    <div className="no-records mt-30">
                                                        {NC.NO_RECORDS}</div>
                                                    :
                                                    <Loader />

                                        }
                                    </div>
                                    {this.getPagination()}
                                </TabPane>
                            }
                            {
                                activeTab == '3' &&
                                <TabPane tabId="3" className="animated fadeIn">
                                    <BuyCoinReport {...BCReportProps} />
                                    {this.getPagination()}
                                </TabPane>
                            }
                            <ActionRequestModal {...ActionCallback} />
                        </TabContent>
                    </div>
                </Row>
            </Fragment>
        )
    }
})

export default BuyCoin
