import React, { Component, Fragment } from "react";
import { TabContent, TabPane, Nav, NavItem, NavLink, Row, Col } from "reactstrap";
import Images from "../../../components/images";

import PersonalDetails from './PersonalDetails/PersonalDetails';
import Transaction from './Transaction/Transaction';
import UserTds from './UserTds/UserTds';
import Gamestats from './Gamestats/Gamestats';
import Referrals from './Referrals/Referrals';
import Dashboard from './Dashboard/Dashboard';
import Coins from './Coins/Coins';
import UserExclude from './UserExclude/UserExclude';
import PrivateContest from './PrivateContest/PrivateContest';
import SF_StockStats from './StockStats/SF_StockStats';


import * as NC from "../../../helper/NetworkingConstants";
import WSManager from "../../../helper/WSManager";
import _ from 'lodash';
import { notify } from 'react-notify-toast';
import ChangeStatus from '../ChangeStatus/ChangeStatus';
import Wallet from '../Wallet/Wallet';



import AddNotesPopup from './AddNotes/AddNotes';
import WithdrawlModal from '../../../components/WithdrawlModal/WithdrawlModal';
import HF, { _isUndefined, _isEmpty } from '../../../helper/HelperFunction';
import queryString from 'query-string';
import HelperFunction from "../../../helper/HelperFunction";
let globalthis = null;
export default class Profile extends Component {
    constructor(props) {
        super(props)
        this.state = {
            activeTab: this.props && this.props.location && this.props.location.state && this.props.location.state.activeTabId ? (this.props.location.state.activeTabId ? this.props.location.state.activeTabId : '1') : '1',
            // activeTab: '1',
            userBasic: [],
            user_unique_id: '',
            WalletmodalIsOpen: false,
            isnoteModalOpen: false,
            add_note: { note: '', create_date: '', subject: '' },
            CallNoteFlag: true,
            PageScroll: false,
            WtModalOpen: false,
            refreshBalance: true,
            ALLOW_COIN_MODULE: HF.allowCoin(),
            walletRoleAccess: !_.isNull(WSManager.getKeyValueInLocal("module_access")) ? WSManager.getKeyValueInLocal("module_access").includes("user_wallet_manage") : false,
            ALLOW_SELF_EXCLUSION: HF.allowSelfExclusion(),
            ALLOW_PRIVATE_CONTEST: HF.allowPrivateContest(),
        }
        this.closeStatusModal = this.closeStatusModal.bind(this);

    }
    static viewAllTransaction(tabIndex) {
        globalthis.toggle(tabIndex)
    }

    toggle(tab) {
        if (this.state.activeTab !== tab) {
            this.setState({
                activeTab: tab,
                PageScroll: false
            });
        }
    }
    noteModal = () => {
        this.setState({
            isnoteModalOpen: !this.state.isnoteModalOpen
        }, () => {
            if (!this.state.isnoteModalOpen)
                this.getNotes()
        });
    }

    unBlockUserLocation = () => {
        if (window.confirm("You are about to bypass location restriction for this user permanently. Make sure to verify user's kyc and location before going ahead.")) {
            this.setState({ posting: true })
            let params = {
                "user_unique_id": this.state.user_unique_id ? this.state.user_unique_id : this.state.userBasic.user_unique_id,
                "status": '3',
            };
            WSManager.Rest(NC.baseURL + NC.UPDATE_USER_LOCATION_STATUS, params).then((responseJson) => {
                if (responseJson.response_code === NC.successCode) {
                    this.getUserBasic(params.user_unique_id)
                    this.setState({ posting: false })
                }
                this.setState({ posting: false })
            })
        }
    }

    componentDidMount() {

         console.log(HF.allowCoin(),'nilesh_hf')
        let values = queryString.parse(this.props.location.search)

        if (!_isEmpty(values) && !_isUndefined(values.tab)) {
            this.setState({ RedirectTab: values.tab })
        }

        if (!_isUndefined(values.wdty)) {
            this.setState({
                WithdrawalMethod: values.wdty,
            }, () => {
                this.getUserBasic(this.props.match.params.user_unique_id)
            });
        } else {
            this.getUserBasic(this.props.match.params.user_unique_id)
        }

        this.setState({
            user_unique_id: this.props.match.params.user_unique_id
        })
    }

    componentWillReceiveProps(nextProps) {

        this.setState({
            activeTab: '1',
            user_unique_id: nextProps.match.params.user_unique_id
        }, () => {
            this.getUserBasic(nextProps.match.params.user_unique_id)
        })
    }

    getUserBasic = (user_unique_id) => {
        this.setState({ posting: true })
        let params = {
            "user_unique_id": user_unique_id,
            "withdraw_method": this.state.WithdrawalMethod,
        };
        WSManager.Rest(NC.baseURL + NC.GET_USER_BASIC, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                let userBasic = responseJson.data;
                this.setState({
                    userBasic: userBasic,
                    status: userBasic.status
                })
                this.redirectToTab()
                this.setState({ posting: false })
            }
            this.setState({ posting: false })
        })
    }
    makeActive(user) {
        this.setState({ reason: '', status: "1", user_unique_id: user.user_unique_id },
            function () {
                this.handleChangeUserStatus();
            }
        );
    }
    openStatusModal(user) {
        this.setState({ StatusmodalIsOpen: true, reason: '', status: "0", user_unique_id: user.user_unique_id });
    }

    closeStatusModal() {
        this.setState({ StatusmodalIsOpen: false });
    }
    handleChangeUserStatus = () => {
        this.setState({ posting: true })

        let params = {
            reason: this.state.inactive_reason,
            user_unique_id: this.state.user_unique_id,
            status: this.state.status
        };
        WSManager.Rest(NC.baseURL + NC.CHANGE_USER_STATUS, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                let userBasic = this.state.userBasic
                userBasic.status = this.state.status

                this.setState({ inactive_reason: '', posting: false, userBasic })

                notify.show(responseJson.message, "success", 5000);
                // this.closeStatusModal();


            }
            this.setState({ posting: false })

        });
    }

    enableOTP = (user_id) => {
        let params = {
            user_id: user_id
        };
        WSManager.Rest(NC.baseURL + NC.UPDATE_OTP_BLOCKED_USERS, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                let userBasic = this.state.userBasic
                userBasic.otp_attempt_count = 0
                this.setState({ userBasic })
                notify.show(responseJson.message, "success", 5000);
            }
        });
    }

    openWalletModal(user_unique_id) {

        this.setState({ WalletmodalIsOpen: true, user_unique_id: user_unique_id });
    }

    closeWalletModal = () => {
        this.setState({ WalletmodalIsOpen: !this.state.WalletmodalIsOpen });
    }

    WtModalOpen = (isActionPerform) => {
        this.setState({ WtModalOpen: !this.state.WtModalOpen }, () => {
            if (isActionPerform == 1 || isActionPerform == 2 || isActionPerform == 3) {
                let { userBasic } = this.state
                userBasic['is_withdraw_request'] = false
                if (isActionPerform == 2) {
                    userBasic['winning_balance'] = parseFloat(userBasic.winning_balance) + parseFloat(userBasic.withdraw_data.winning_amount)
                }
                this.setState({ userBasic: userBasic })
            }
        });

    }

    getNotes = () => {
        this.setState({ posting: true })
        let params = { 'user_unique_id': this.state.user_unique_id };
        WSManager.Rest(NC.baseURL + NC.GET_NOTES, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {

                this.setState({ notes: responseJson.data, posting: false })

            }
            this.setState({ posting: false })

        });
    }

    PersonalViewCall = () => {
        this.setState({
            activeTab: '2',
            PageScroll: true
        })
    }

    update_transaction_balance = () => {
        this.getUserBasic(this.props.match.params.user_unique_id)
        this.setState({ refreshBalance: false }, () => {
            this.setState({
                refreshBalance: true
            })
        });
    }

    redirectToTab = () => {
        if (!_isEmpty(this.state.RedirectTab) && this.state.RedirectTab === 'pers') {
            Profile.viewAllTransaction('2')
        }
        if (!_isEmpty(this.state.RedirectTab) && this.state.RedirectTab === 'trans') {
            Profile.viewAllTransaction('3')
        }
    }

    render() {
        globalthis = this
        const { refreshBalance, activeTab, user_unique_id, WalletmodalIsOpen, userBasic, CallNoteFlag, isnoteModalOpen, PageScroll, WtModalOpen, posting, ALLOW_COIN_MODULE, walletRoleAccess, ALLOW_SELF_EXCLUSION, ALLOW_PRIVATE_CONTEST } = this.state
        const Wallet_props = {
            modalIsOpen: WalletmodalIsOpen,
            modalCallback: this.closeWalletModal,
            user_unique_id: user_unique_id,
            update_method: this.update_transaction_balance
        }

        const NoteModal_props = {
            isnoteModalOpen: isnoteModalOpen,
            modalCallback: this.noteModal,
            user_unique_id: user_unique_id,
            is_flag: this.state.userBasic.is_flag
        }

        const WithdrawlModal_props = {
            WtModalOpen: WtModalOpen,
            modalCallback: this.WtModalOpen,
            userBasic: userBasic
        }

        return (
            <Fragment>
                {(userBasic.is_withdraw_request == true && this.state.userBasic.pan_verified == 1) &&
                    <Row className="pending-doc mb-2">
                        <Col md={12}>
                            <i className="icon-pending-document text-red mr-2"></i> Withdrawal Request Pending..
                            <span className="verify" onClick={this.WtModalOpen}>Verify</span>

                        </Col>
                    </Row>
                }

                {((userBasic.type == 2 && userBasic.is_bank_verified == null) || (userBasic.type == 1 && (userBasic.pan_verified == 0 || userBasic.is_bank_verified == null) && (userBasic.pan_no != null || userBasic.bank_document != null))) &&
                    <Row className="pending-doc mb-2">
                        <Col md={12}>
                            <i className="icon-pending-document text-red mr-2"></i>
                            Documents approval pending..
                            <span className="verify"
                                onClick={this.PersonalViewCall}>Verify</span>
                        </Col>
                    </Row>
                }


                <Row className="profile-header">
                    <Col md={5}>
                        <div className="profile-box">
                            <figure className="profile-container">
                                <img src={(userBasic.image) ? NC.S3 + NC.THUMB + userBasic.image : Images.no_image} className="img-cover" alt="" />
                            </figure>
                            <div className="info-box">
                                <div className="name">{(this.state.userBasic.first_name) ? this.state.userBasic.first_name : '-'} {(this.state.userBasic.last_name) ? this.state.userBasic.last_name : ''}
                                    {this.state.userBasic.is_flag == 1 ?
                                        <img className="ml-3" src={Images.FLAG_ENABLE} alt="" />
                                        :
                                        <i className="icon-flag"></i>
                                    }
                                </div>

                                <div className="address text-ellipsis">({(userBasic.user_name) ? userBasic.user_name : '--'}), {(userBasic.address) ? userBasic.address : '--'}</div>
                            </div>
                        </div>
                    </Col>
                    <Col md={2} className="profit-earned text-center">
                        <div className="rupees">{HF.getCurrencyCode()}{(this.state.userBasic.total_profit) ? this.state.userBasic.total_profit : '0'}</div>
                        <div className="font-xs">Profit earned</div>
                    </Col>
                    <Col md={5}>
                        <ul className="action-list">
                            {
                                userBasic.bs_status == "2" &&
                                <li className="action-item" onClick={() => this.unBlockUserLocation()}>
                                    <div className="action-item-box">
                                        <img style={{ height: 20, width: 20 }} src={Images.UNBLOCK_USER} ></img>
                                    </div>
                                    <div className="action-title" >Location<br /> Unblock</div>
                                </li>
                            }
                            {userBasic.otp_attempt_count == "1" &&
                                <li className="action-item">
                                    <div className="action-item-box xactive">
                                        <i
                                            className="icon-righttick"
                                            onClick={() => this.enableOTP(this.state.userBasic.user_id)}>
                                        </i>
                                    </div>
                                    <div className='action-title'>
                                        Unblock User
                                    </div>
                                </li>}
                            <li className="action-item" onClick={() => this.noteModal()}>
                                <div className="action-item-box">
                                    <i className="icon-add_note"></i>
                                </div>
                                <div className="action-title">Add Notes</div>
                            </li>



                            {
                                walletRoleAccess &&
                                <li className="action-item">
                                    <div className="action-item-box">
                                        <i className="icon-wallet" onClick={() => this.openWalletModal(user_unique_id)}></i>
                                    </div>
                                    <div className="action-title" >Manage<br /> Balance</div>
                                </li>
                            }
                            <li className="action-item">
                            <div className={`action-item-box ${userBasic.status == "0" ? 'active' : userBasic.status == "2" ? 'u-pend' : ''}`}>
                                    {
                                        userBasic.status == "2" ?
                                            <i className="icon-pending-doc"></i>
                                            :
                                            <i title="Manage status"
                                                className={`icon-inactive-border ${userBasic.status == 1 ? userBasic.wdl_status == 2 ?
                                                    'withdraw-block' : '' : userBasic.status == "0" ? 'active' : ''}`}
                                                onClick={() => (this.state.status == 1) ?
                                                    this.openStatusModal(this.state.userBasic) :
                                                    this.makeActive(this.state.userBasic)}
                                            >
                                            </i>


                                    }
                                </div>

                                <div className={`action-title ${userBasic.status == "0"
                                    ? 'active' : userBasic.status == "2" ? 'u-pend-c' : ''}`}>
                                    {
                                        userBasic.status == "0" && 'Activate'
                                    }
                                    {
                                        userBasic.status == "1" && 'Block'
                                    }
                                    {
                                        userBasic.status == "2" && 'Pending'
                                    }
                                    <br /> User</div>
                            </li>

                        </ul>
                    </Col>
                </Row>
                <Row className="user-navigation only-for-un-cls">
                    <div className="w-100">
                        <Nav tabs>
                            <NavItem className={activeTab === '1' ? "active" : ""}
                                onClick={() => { this.toggle('1'); }}>
                                <NavLink>
                                    Dashboard
                                </NavLink>
                            </NavItem>
                            {
                                ALLOW_COIN_MODULE == 1 &&
                                <NavItem className={activeTab === '9' ? "active" : ""}
                                    onClick={() => { this.toggle('9'); }}>
                                    <NavLink>
                                        Coins
                                    </NavLink>
                                </NavItem>
                            }

                            <NavItem className={activeTab === '2' ? "active" : ""}
                                onClick={() => { this.toggle('2'); }}>
                                <NavLink>
                                    Personal
                                </NavLink>
                            </NavItem>
                            {
                                ALLOW_SELF_EXCLUSION > 0 &&
                                <NavItem className={activeTab === '10' ? "active" : ""}
                                    onClick={() => { this.toggle('10'); }}>
                                    <NavLink>
                                        Exclusion
                                </NavLink>
                                </NavItem>
                            }
                            <NavItem className={activeTab === '3' ? "active" : ""}
                                onClick={() => { this.toggle('3'); }}>
                                <NavLink>
                                    Transaction
                                </NavLink>
                            </NavItem>
                            {
                                HelperFunction.allowDFS() == '1' &&
                                <NavItem className={activeTab === '4' ? "active" : ""}
                                    onClick={() => { this.toggle('4'); }}>
                                    <NavLink>
                                        Games Stats
                                    </NavLink>
                                </NavItem>

                            }




                            <NavItem className={activeTab === '7' ? "active" : ""}
                                onClick={() => { this.toggle('7'); }}>
                                <NavLink>
                                    Referrals
                                </NavLink>
                            </NavItem>

                            {
                                ALLOW_PRIVATE_CONTEST > 0 &&
                                <NavItem className={activeTab === '11' ? "active" : ""}
                                    onClick={() => { this.toggle('11'); }}>
                                    <NavLink>
                                        Private Contest
                                </NavLink>
                                </NavItem>
                            }
                            {
                                (HF.allowStockFantasy() == '1' || HF.allowLiveStockFantasy() == '1') &&
                                <NavItem className={activeTab === '12' ? "active" : ""}
                                    onClick={() => { this.toggle('12'); }}>
                                    <NavLink>
                                        Stock Stats
                                </NavLink>
                                </NavItem>
                            }
                            {
                            (HelperFunction.allowTDSReport() == '1' && HelperFunction.allowIndianTDS() == '1') &&
                            <NavItem className={activeTab === '8' ? "active" : ""}
                                onClick={() => { this.toggle('8'); }}>
                                <NavLink>
                                TDS Report
                                </NavLink>
                            </NavItem>
                           }

                            {/* <NavItem>
                                <NavLink
                                    className={activeTab === '8' ? "active" : ""}
                                    onClick={() => { this.toggle('8'); }}
                                >
                                    Login History
                                </NavLink>
                            </NavItem> */}
                        </Nav>
                        <TabContent activeTab={activeTab}>
                            <TabPane tabId="1">
                                <Row>
                                    <Col sm="12">
                                        {
                                            (!_.isEmpty(userBasic) && !posting) &&
                                            <Dashboard userBasic={userBasic} />
                                        }
                                    </Col>
                                </Row>
                            </TabPane>
                            {
                                (activeTab == '2') &&
                                <TabPane tabId="2">
                                    {

                                        (!_.isEmpty(user_unique_id) && !posting) &&
                                        <PersonalDetails scroll={PageScroll} user_unique_id={user_unique_id} userBasic={userBasic} />
                                    }
                                </TabPane>
                            }
                            {
                                activeTab == '3' &&
                                <TabPane tabId="3">
                                    <Row>
                                        <Col sm="12">
                                            {refreshBalance &&
                                                <Transaction DashboardTranProps={true} userBasic={userBasic} />
                                            }
                                        </Col>
                                    </Row>
                                </TabPane>
                            }
                            {
                                activeTab == '4' &&
                                <TabPane tabId="4">
                                    <Row>
                                        <Col sm="12">
                                            <Gamestats DashboardProps={true} userBasic={userBasic} />
                                        </Col>
                                    </Row>
                                </TabPane>
                            }
                            <TabPane tabId="5">
                                <Row>
                                    <Col sm="12">
                                        <h4>Tab 5 Contents</h4>
                                    </Col>
                                </Row>
                            </TabPane>
                            <TabPane tabId="6">
                                <Row>
                                    <Col sm="12">
                                        <h4>Tab 6 Contents</h4>
                                    </Col>
                                </Row>
                            </TabPane>
                            {
                                activeTab == '7' &&
                                <TabPane tabId="7">
                                    <Row>
                                        <Col sm="12">
                                            {userBasic.user_id != null &&
                                                <Referrals user_id={userBasic.user_id} />
                                            }
                                        </Col>
                                    </Row>
                                </TabPane>
                            }
                            {
                                activeTab == '8' &&
                            <TabPane tabId="8">
                                <Row>
                                    <Col sm="12">
                                    <UserTds DashboardProps={true} userBasic={userBasic} />
                                       
                                    </Col>
                                </Row>
                            </TabPane>
                        }
                            {
                                activeTab == '9' &&
                                <TabPane tabId="9">
                                    <Row>
                                        <Col sm="12">
                                            <Coins FromDashboard="false" user_unique_id={user_unique_id} />
                                        </Col>
                                    </Row>
                                </TabPane>
                            }
                            {
                                (HF.allowSelfExclusion() == '1' && activeTab == '10') &&
                                <TabPane tabId="10">
                                    <Row>
                                        <Col sm="12">
                                            {
                                                userBasic.user_id != null &&
                                                <UserExclude user_id={userBasic.user_id} />
                                            }
                                        </Col>
                                    </Row>
                                </TabPane>
                            }
                            {
                                activeTab == '11' &&
                                <TabPane tabId="11">
                                    <Row>
                                        <Col sm="12">
                                            {userBasic.user_id != null &&
                                                <PrivateContest user_id={userBasic.user_id} />
                                            }
                                        </Col>
                                    </Row>
                                </TabPane>
                            }
                            {
                                (activeTab == '12' && (HF.allowStockFantasy() == '1' || HF.allowLiveStockFantasy() == '1')) &&
                                <TabPane tabId="12">
                                    <Row>
                                        <Col sm="12">
                                           <SF_StockStats userBasic={userBasic} />
                                        </Col>
                                    </Row>
                                </TabPane>
                            }
                        </TabContent>
                    </div>
                </Row>
                <div className="active-modal">
                    <ChangeStatus closeStatusModal={this.closeStatusModal} item={userBasic} user_unique_id={this.state.user_unique_id} StatusmodalIsOpen={this.state.StatusmodalIsOpen} blockState="2"></ChangeStatus>
                </div>
                {WalletmodalIsOpen &&
                    <div className="wallet-modal">
                        <Wallet {...Wallet_props} />
                    </div>
                }
                {
                    isnoteModalOpen &&
                    <AddNotesPopup {...NoteModal_props} />
                }
                {!_.isEmpty(userBasic) &&
                    <WithdrawlModal {...WithdrawlModal_props} />
                }
            </Fragment>
        )
    }
}