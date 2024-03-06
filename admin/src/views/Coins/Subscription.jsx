import React, { Fragment } from "react";
import { Row, Col, TabContent, TabPane, Nav, NavItem, NavLink, Input, Button } from 'reactstrap';
import CardSubscription from './CardSubscription.jsx';

import _ from 'lodash';
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import WSManager from '../../helper/WSManager';
import ActionRequestModal from '../../components/ActionRequestModal/ActionRequestModal';
import Pagination from "react-js-pagination";
import queryString from 'query-string';
import Loader from '../../components/Loader';
import { MODULE_NOT_ENABLE, SC_PKG_DLT, SC_PKG_SUB_DLT } from "../../helper/Message";
import HF, { _isEmpty, _Map, _isUndefined } from '../../helper/HelperFunction';
import SubscriptionReport from './SubscriptionReport.jsx';
import PromptModal from '../../components/Modals/PromptModal';
var globalThis = null;

var createReactClass = require('create-react-class');
var Subscription = createReactClass({
    getInitialState: function () {
        return {
            CURRENT_PAGE: 1,
            PERPAGE: NC.ITEMS_PERPAGE,
            activeTab: '1',
            PackageList: [],
            formValid: false,
            PriceMSg: true,
            PackageNameMSg: true,
            AndIosMSg: true,
            CoinsMSg: true,
            Price: '',
            PackageName: '',
            Coins: '',
            ActionPopupOpen: false,
            ListPosting: false,
            filter: '',
            sortField: 'subscription_id',
            isDescOrder: 'true',
            AndroidID: '',
            IosID: '',
            PackageDdOpt: [],
        }
    },

    componentDidMount: function () {
        if (HF.allowSubscription() != '1') {
            notify.show(MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }

        globalThis = this;
        let qString = queryString.parse(this.props.location.search)
        this.setState({
            activeTab: !_.isUndefined(qString.report) ? "2" : "1",
        }, () => {
            this.getPackageList()
        })
    },

    getPackageList: function () {
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

        WSManager.Rest(NC.baseURL + NC.SC_GET_PACKAGES, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                var pack_dd = [{
                    value: '',
                    label: 'All',
                }]
                _Map(Response.data.result, function (d) {
                    pack_dd.push({
                        value: d.subscription_id,
                        label: d.name
                    });
                })

                this.setState({
                    ListPosting: false,
                    PackageList: Response.data ? Response.data.result : [],
                    PackageDdOpt: pack_dd,
                    Total: Response.data ? Response.data.total : '',
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    },

    toggle: function (tab) {
        if (this.state.activeTab !== tab) {
            this.setState({ CURRENT_PAGE: 1, PackageList: [], activeTab: tab }, () => {
                this.getPackageList()
            });
        }
    },

    handleInputChange: function (event) {
        let name = event.target.name
        let value = event.target.value
        if (name === 'PackageName') {
            value = HF.allowOneSpace(value)
        }
        this.setState({ [name]: value },
            () => this.validateForm(name, value)
        )
    },

    validateForm: function (name, value) {
        let { Price, PackageName, Coins, AndroidID, IosID } = this.state

        let PriceValid = false
        let PackageNameValid = false
        let CoinsValid = false
        let AndIosValid = false
        if (((HF.getIntVersion() == 1 && Price > 0) || (HF.getIntVersion() != 1 && Price > 9)) && (Price.length > 0 && Price.length <= 7 && Price.match(/^([1-9][0-9]{0,7})$/))) {
            PriceValid = true
        }
        if (Coins.length > 0 && Coins.length <= 7 && Coins.match(/^([1-9][0-9]{0,7})$/)) {
            CoinsValid = true
        }
        if (PackageName.length > 2 && PackageName.length <= 10) {
            PackageNameValid = true
        }
        if (!_isEmpty(AndroidID) || !_isEmpty(IosID)) {
            AndIosValid = true
        }
        if (name === 'Price') {
            this.setState({ PriceMSg: PriceValid })
        }
        if (name === 'Coins') {
            this.setState({ CoinsMSg: CoinsValid })
        }
        if (name === 'PackageName') {
            this.setState({ PackageNameMSg: PackageNameValid })
        }
        if (name === 'AndroidID' || name === 'IosID') {
            this.setState({ AndIosMSg: AndIosValid })
        }

        this.setState({
            formValid: (PriceValid && CoinsValid && PackageNameValid && AndIosValid)
        })
    },

    addPackage: function () {
        this.setState({ formValid: false })
        let { Price, PackageName, Coins, AndroidID, IosID } = this.state
        if (PackageName.length < 3 || PackageName.length > 10) {
            this.setState({ TitleMSg: false })
            return false
        }

        let params = {
            "android_id": AndroidID,
            "ios_id": IosID,
            "name": PackageName,
            "amount": Price,
            "coins": Coins
        }
        WSManager.Rest(NC.baseURL + NC.SC_ADD_PACKAGE, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.getPackageList()
                notify.show(Response.message, 'success', 5000)
                this.setState({
                    formValid: true,
                    PriceMSg: true,
                    PackageName: true,
                    CoinsMSg: true,
                    Price: '',
                    PackageName: '',
                    Coins: '',
                    AndroidID: '',
                    IosID: '',
                    AndIosMSg: true,
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    },

    //function to toggle reward modal component
    toggleActionPopup: function (id, idx) {
        this.setState({
            indexVal: idx,
            SubcriptID: id,
            ActionPopupOpen: !this.state.ActionPopupOpen
        })
    },

    //function to active inactive reward request
    modalActioCallback: function () {
        this.setState({ YesPosting: true })
        let { indexVal, PackageList, SubcriptID } = this.state
        let params = {
            'subscription_id': SubcriptID
        }
        WSManager.Rest(NC.baseURL + NC.SC_REMOVE_PACKAGE, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, 'success', 5000)
                this.setState({ ActionPopupOpen: false })
                this.getPackageList()
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
                this.getPackageList()
            });
        }
    },

    getPagination() {
        let { Total, PERPAGE, CURRENT_PAGE } = this.state
        if (Total > PERPAGE) {
            return (
                <Row>
                    <Col md={12}>
                        <div className="custom-pagination mb-30">
                            <Pagination
                                activePage={CURRENT_PAGE}
                                itemsCountPerPage={PERPAGE}
                                totalItemsCount={Total}
                                pageRangeDisplayed={5}
                                onChange={e => this.handlePageChange(e)}
                            />
                        </div>
                    </Col>
                </Row>
            )
        }
    },

    render() {
        let { activeTab, PackageList, Price, PackageName, Coins, CoinsMSg, PackageNameMSg, formValid, PriceMSg, YesPosting, ScreenView, ActionPopupOpen, Total, ListPosting, AndroidID, IosID, AndIosMSg, PackageDdOpt } = this.state
        // const ActionCallback = {
        //     posting: YesPosting,
        //     Message: SC_PKG_DLT,
        //     Screen: ScreenView,
        //     modalCallback: this.toggleActionPopup,
        //     modalActioCallback: this.modalActioCallback,
        //     ActionPopupOpen: ActionPopupOpen,
        // }

        let ActionCallback = {
            publishModalOpen: ActionPopupOpen,
            publishPosting: YesPosting,
            modalActionNo: this.toggleActionPopup,
            modalActionYes: this.modalActioCallback,
            MainMessage: SC_PKG_DLT,
            SubMessage: SC_PKG_SUB_DLT,
        }

        return (
            <Fragment>
                <Row><h2 className="h2-cls mt-4">Subscription</h2></Row>
                <Row className="user-navigation subscription">
                    <div className="w-100">
                        <Nav tabs>
                            <NavItem
                                className={activeTab === '1' ? "active" : ""}
                                onClick={() => { this.toggle('1'); }}
                            >
                                <NavLink>
                                    Add package
                                </NavLink>
                            </NavItem>
                            <NavItem
                                className={activeTab === '2' ? "active" : ""}
                                onClick={() => { this.toggle('2'); }}
                            >
                                <NavLink>
                                    Reports
                                </NavLink>
                            </NavItem>
                        </Nav>
                        <TabContent activeTab={activeTab}>
                            <TabPane tabId="1" className="animated fadeIn">
                                <div className="add-rewards input-form">
                                    <Row>
                                        <Col md="4" className="mb-3">
                                            <label htmlFor="Title">Package Name <span className="asterrisk">*</span></label>
                                            <Input
                                                placeholder="Enter Package Name"
                                                maxLength={10}
                                                name='PackageName'
                                                value={PackageName}
                                                onChange={this.handleInputChange}
                                            />
                                            {!PackageNameMSg &&
                                                <span className="color-red">
                                                    Package name should be between 3 to 10 character
                                                </span>
                                            }
                                        </Col>
                                        <Col md="4" className="mb-3">
                                            <label htmlFor="Title">Android ID</label>
                                            <Input
                                                placeholder="Enter Android ID"
                                                name='AndroidID'
                                                value={AndroidID}
                                                onChange={this.handleInputChange}
                                            />
                                            {!AndIosMSg &&
                                                <span className="color-red">
                                                    Android or Ios ID can not be empty
                                                </span>
                                            }
                                        </Col>
                                        <Col md="4" className="mb-3">
                                            <label htmlFor="Title">IOS ID</label>
                                            <Input
                                                placeholder="Enter IOS ID"
                                                name='IosID'
                                                value={IosID}
                                                onChange={this.handleInputChange}
                                            />
                                            {!AndIosMSg &&
                                                <span className="color-red">
                                                    Android or Ios ID can not be empty
                                                </span>
                                            }
                                        </Col>

                                        <Col md="4" className=" mb-3">
                                            <label htmlFor="Redeem">Price in {HF.getCurrencyCode()}<span className="asterrisk">*</span></label>
                                            <div className="">
                                                <Input
                                                    placeholder={'Enter Price in ' + HF.getCurrencyCode()}
                                                    maxLength={7}
                                                    name='Price'
                                                    value={Price}
                                                    onChange={this.handleInputChange}
                                                />
                                                {
                                                    (!PriceMSg && HF.getIntVersion() == 1) &&
                                                    <span className="color-red">
                                                        Please enter number greater than equal to 1.
                                                    </span>
                                                }
                                                {
                                                    (!PriceMSg && HF.getIntVersion() != 1) &&
                                                    <span className="color-red">
                                                        Please enter number greater than equal to 10.
                                                    </span>
                                                }
                                            </div>
                                        </Col>

                                        <Col md="4" className=" mb-3">
                                            <label htmlFor="Redeem">Coins<span className="asterrisk">*</span></label>
                                            <div className="">
                                                <Input
                                                    placeholder="Enter Coins"
                                                    maxLength={7}
                                                    name='Coins'
                                                    value={Coins}
                                                    onChange={this.handleInputChange}
                                                />
                                                {!CoinsMSg &&
                                                    <span className="color-red">
                                                        Please enter valid number only.
                                                            </span>
                                                }
                                            </div>
                                        </Col>
                                    </Row>
                                    <Row>
                                        <Col md={12}>
                                            <div className="">
                                                <div className="subs-btn">
                                                    <Button
                                                        disabled={!formValid}
                                                        className=""
                                                        onClick={this.addPackage}
                                                    >Create Package</Button>
                                                </div>
                                            </div>
                                        </Col>
                                    </Row>
                                </div>
                                <Row className="mb-5">
                                    <Col md={12}>
                                        <div className="mt-30">
                                            {
                                                Total > 0 ?
                                                    _.map(PackageList, function (item, idx) {
                                                        let props = {
                                                            item: item,
                                                            idx: idx,
                                                            actionPopupCall: globalThis.toggleActionPopup
                                                        }
                                                        return (
                                                            <div key={idx} className="card-align clearfix">
                                                                <CardSubscription {...props} />
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
                                    </Col>
                                </Row>
                                {this.getPagination()}
                            </TabPane>
                            {
                                activeTab == '2' &&
                                <TabPane tabId="2" className="animated fadeIn">
                                    {
                                        !_isUndefined(PackageDdOpt) &&
                                        <SubscriptionReport package_opt={PackageDdOpt} />
                                    }
                                </TabPane>
                            }
                            {/* <ActionRequestModal {...ActionCallback} /> */}
                            {ActionPopupOpen && <PromptModal {...ActionCallback} />}
                        </TabContent>
                    </div>
                </Row>
            </Fragment>
        )
    }
})

export default Subscription
