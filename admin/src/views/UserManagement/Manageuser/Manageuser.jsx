
import React, { Component, Fragment, useState } from "react";

import { Row, Col, Table, Modal, ModalBody, ModalHeader, Tooltip } from 'reactstrap';

import Images from "../../../components/images";

import _ from 'lodash';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import Select from 'react-select';


import * as NC from "../../../helper/NetworkingConstants";
import WSManager from "../../../helper/WSManager";


import Pagination from "react-js-pagination";
// import Modal from 'react-modal';
import { notify } from 'react-notify-toast';
import VerifyDocument from '../VerifyDocument/VerifyDocument';
import Wallet from '../Wallet/Wallet';
import ChangeStatus from '../ChangeStatus/ChangeStatus';
import Loader from '../../../components/Loader';
import queryString from 'query-string';
import HF from '../../../helper/HelperFunction';
import moment from "moment-timezone";

const options = [
    { value: 1, label: 'Active' },
    { value: 0, label: 'Block' },
    { value: 2, label: 'Pending' },
    { value: 3, label: 'Blocked Withdrawal' },
]

const TooltipItem = props => {
    const { item, id, icon, placement } = props;
    const [tooltipOpen, setTooltipOpen] = useState(false);
    const toggle = () => setTooltipOpen(!tooltipOpen);
    return (
        <span>

            <i className={icon} id={"Tooltip-" + id}></i>
            <Tooltip
                placement={placement}
                isOpen={tooltipOpen}
                target={"Tooltip-" + id}
                toggle={toggle}
            >
                {item}
            </Tooltip>
        </span>
    );
};


class Manageuser extends Component {
    constructor(props) {
        super(props)
        let filter = {
            // from_date: HF.getFirstDateOfMonth(),
            from_date: new Date(Date.now() - 91 * 24 * 60 * 60 * 1000),
            to_date: new Date(moment().format('D MMM YYYY')),
            current_page: 1,
            status: 1,
            pending_pan_approval: '',
            is_flag: '',
            keyword: '',
            items_perpage: NC.ITEMS_PERPAGE,
            sort_field: 'added_date',
            sort_order: 'DESC'
        }
        this.state = {
            filter: filter,
            posting: false,
            StartDate: '',
            EndDate: '',
            modalIsOpen: false,
            balanceModalIsOpen: false,
            StatusmodalIsOpen: false,
            WalletmodalIsOpen: false,
            ACverifymodalIsOpen: false,
            UserStatus: 1,
            userslist: [],
            userFullName: '',
            LoaderPosting: true,
            walletRoleAccess: !_.isNull(WSManager.getKeyValueInLocal("module_access")) ? WSManager.getKeyValueInLocal("module_access").includes("user_wallet_manage") : false,
            AUTO_KYC_ALLOW: !_.isNull(WSManager.getKeyValueInLocal('AUTO_KYC_ALLOW')) ? WSManager.getKeyValueInLocal('AUTO_KYC_ALLOW') : 0,
            total : 0,
            user_full_name: '',
            wdlStatus: '',
            total: 0,
            NetWinning:0
        };
        this.handleChange = this.handleChange.bind(this);
        this.handleChangeEnd = this.handleChangeEnd.bind(this);

        this.openStatusModal = this.openStatusModal.bind(this);
        this.openACverifyModal = this.openACverifyModal.bind(this);
        this.openWalletModal = this.openWalletModal.bind(this);

        this.closeStatusModal = this.closeStatusModal.bind(this);
        this.closeACverifyModal = this.closeACverifyModal.bind(this);
        this.closeWalletModal = this.closeWalletModal.bind(this);


        this.SearchCodeReq = _.debounce(this.SearchCodeReq.bind(this), 500);

    }

    handlePageChange(current_page) {

    }
    componentDidMount() {
        let filter = this.state.filter;
        let url = this.props.location.search;
        let urlParams = queryString.parse(url);
        if (urlParams.pending == 1) {
            filter['pending_pan_approval'] = urlParams.pending;
            this.setState({ filter })
        }
        this.getUserList();

    }
    handlePageChange(current_page) {

        let filter = this.state.filter;

        filter['current_page'] = current_page;

        this.setState(
            { filter: filter },
            function () {
                this.getUserList();
            });

    }
    handleSearch(keyword) {

        let filter = this.state.filter;
        filter['keyword'] = keyword.target.value;
        this.setState({
            filter: filter,
        },
            this.SearchCodeReq
        );


    }
    SearchCodeReq() {

        this.getUserList()
    }

    handleSelect(status) {
        if (status != null) {
            let filter = this.state.filter;
            filter['status'] = status.value;
            filter['current_page'] = 1;
            this.setState({
                filter: filter,
                UserStatus: status
            },
                function () {
                    this.getUserList();
                });
        }
    }






    handleClick(pending_pan_approval, flag) {
        let filter = this.state.filter;

        filter[flag] = pending_pan_approval;
        filter['current_page'] = 1;
        this.setState(
            { filter: filter },
            function () {
                this.getUserList();
            });

    }
    handleChange(date) {
        let filter = this.state.filter;
        filter['from_date'] = moment(date).format("YYYY-MM-DD");
        filter['current_page'] = 1;
        this.setState(
            {
                filter: filter,
                StartDate: date
            },
            function () {
                this.getUserList();
            });


    }
    handleChangeEnd(date) {
        let filter = this.state.filter;
        filter['to_date'] = moment(date).format("YYYY-MM-DD");
        filter['current_page'] = 1;
        this.setState(
            {
                filter: filter,
                EndDate: date
            },
            function () {
                this.getUserList();
            });
    }
    getUserList = () => {
        this.setState({ posting: true })
        let { filter } = this.state
      

        let tempFilter = filter
        let FilterToDate = WSManager.getLocalToUtcFormat(tempFilter.from_date , 'YYYY-MM-DD') ;
        let FilterFromDate = WSManager.getLocalToUtcFormat(tempFilter.to_date , 'YYYY-MM-DD');
        tempFilter.from_date = FilterToDate
        tempFilter.to_date = FilterFromDate
        let params = tempFilter;

        WSManager.Rest(NC.baseURL + NC.GET_USERLIST, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                let result = responseJson.data.result;
                let total = responseJson.data.total;
                this.setState({
                    posting: false,
                    userslist: result
                })

                if (total > 0) {
                    this.setState({
                        total: total
                    })
                }
            }
            this.setState({ posting: false })
        })
    }
    exportUser = () => {
        let { filter, StartDate, EndDate } = this.state
        let tempFromDate = ''
        let tempToDate = ''
        if (StartDate != '' && EndDate != '') {
            tempFromDate = moment(StartDate).format("YYYY-MM-DD");
            tempToDate = moment(EndDate).format("YYYY-MM-DD");
        }

        var query_string = 'status=' + filter.status + '&is_flag=' + filter.is_flag + '&is_pending_pan_approval=' + filter.pending_pan_approval + '&items_perpage=' + filter.items_perpage + '&total_items=0&current_page=' + filter.current_page + '&sort_order=' + filter.sort_order + '&sort_field=' + filter.sort_field + '&country=&state=&keyword=' + filter.keyword + '&from_date=' + tempFromDate + '&to_date=' + tempToDate;

        // console.log('query_stringquery_string', query_string)


        let sessionKey = WSManager.getToken();
        query_string += "&Sessionkey" + "=" + sessionKey;

        window.open(NC.baseURL + 'adminapi/user/export_users?' + query_string, '_blank');
    }
    openStatusModal(user) {
        let firstname = user.first_name ? user.first_name : '-'
        let lastname = user.last_name ? user.last_name : ''
        let fullname = firstname + ' ' + lastname
        this.setState({
            StatusmodalIsOpen: true,
            reason: '',
            status: "0",
            user_unique_id: user.user_unique_id,
            user_full_name: fullname,
            wdlStatus: user,
        });
    }

    closeStatusModal() {
        this.setState({ StatusmodalIsOpen: false }, function () {
            this.getUserList();
        });
    }

    handleChangeUserStatus = () => {
        this.setState({ posting: true })

        let params = { reason: this.state.inactive_reason, user_unique_id: this.state.user_unique_id, status: this.state.status };
        WSManager.Rest(NC.baseURL + NC.CHANGE_USER_STATUS, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {

                this.setState({ inactive_reason: '', posting: false })
                notify.show(responseJson.message, "success", 5000);
                this.closeStatusModal();
                this.getUserList();

            }
            this.setState({ posting: false })

        });
    }
    makeActive(user) {
        this.setState({ reason: '', status: "1", user_unique_id: user.user_unique_id },
            function () {
                this.handleChangeUserStatus();
            }
        );
    }

    openWalletModal(item) {        
        let firstname = item.first_name ? item.first_name : '-'
        let lastname = item.last_name ? item.last_name : ''
        let fullname = firstname + ' ' + lastname
        this.setState({
            WalletmodalIsOpen: true,
            user_unique_id: item.user_unique_id,
            userFullName: fullname,
            NetWinning: item.net_winning,
        });
    }
    closeWalletModal = () => {
        this.setState({ WalletmodalIsOpen: !this.state.WalletmodalIsOpen });
    }

    openACverifyModal(user) {

        this.setState({ ACverifymodalIsOpen: true, userDetail: user });
    }
    closeACverifyModal() {
        this.setState({ ACverifymodalIsOpen: false });
    }
    AutoToolTipToggle = (flag) => {
        this.setState({ isShowAutoToolTip: !this.state.isShowAutoToolTip });
    }

    update_transaction_balance = () => {
        return null;
    }

    reloadPanModalData = (user) => {
        let tempUdtl = this.state.userDetail
        tempUdtl.first_name = user.first_name
        tempUdtl.last_name = ''
        tempUdtl.pan_no = user.pan_no
        tempUdtl.pan_image = user.pan_image
        tempUdtl.dob = user.dob
        this.setState({ userDetail: tempUdtl });
    }

    reloadBankModalData = (user) => {
        let tempUdtl = this.state.userDetail
        tempUdtl.bank_document = user.bank_document
        this.setState({ userDetail: tempUdtl });
    }

    render() {
        const { NetWinning,WalletmodalIsOpen, user_unique_id, userFullName, filter, UserStatus, userslist, StartDate, EndDate, total, posting, walletRoleAccess, AUTO_KYC_ALLOW } = this.state
        var todaysDate = moment().format('D MMM YYYY');
        const Wallet_props = {
            modalIsOpen: WalletmodalIsOpen,
            modalCallback: this.closeWalletModal,
            user_unique_id: user_unique_id,
            userFullName: userFullName,
             NetWinning: NetWinning,
            update_method: this.update_transaction_balance
        }
        return (
            <Fragment>
                <div className="manage-user-heading clearfix">
                    <h1 className="page-title">
                        Manage User</h1>
                    <div className="search-user">

                        <input name="search-user" id="search-user" className="search-input" value={this.state.filter.keyword} placeholder="Search" onChange={e => this.handleSearch(e)} />
                        <i className="icon-search"></i>
                    </div>
                </div>
                <Row className="filter-userlist">
                    <Col md={9}>
                        <div className="member-box float-left">
                            <div className="float-left">
                                <label className="filter-label">Start Date</label>
                                <DatePicker
                                    maxDate={new Date(filter.to_date)}
                                    className="filter-date"
                                    showYearDropdown='true'
                                    selected={new Date(filter.from_date)}
                                    onChange={this.handleChange}
                                    placeholderText="From"
                                    dateFormat='dd/MM/yyyy'
                                />
                            </div>
                            <div className="float-left">
                                <label className="filter-label">End Date</label>
                                <DatePicker
                                    minDate={new Date(filter.from_date)}
                                    maxDate={new Date()}
                                    className="filter-date"
                                    showYearDropdown='true'
                                    selected={new Date(filter.to_date)}
                                    onChange={this.handleChangeEnd}
                                    placeholderText="To"
                                    dateFormat='dd/MM/yyyy'
                                />
                            </div>
                        </div>
                        <div>
                            <label className="filter-label">User</label>
                            <Select
                                searchable={false}
                                clearable={false}
                                class="form-control"
                                options={options}
                                placeholder="Active"
                                value={UserStatus}
                                onChange={e => this.handleSelect(e)}
                            />
                            {
                                //AUTO_KYC_ALLOW == 0 && 
                                <div className={(this.state.filter.pending_pan_approval == '') ? 'pending-docs' : 'pending-docs1'} onClick={e => this.handleClick((this.state.filter.pending_pan_approval == '') ? 1 : '', 'pending_pan_approval')}>
                                    Pending Docs
                                </div>
                            }
                            <div className={(this.state.filter.is_flag == '') ? 'pending-docs' : 'pending-docs1'} onClick={e => this.handleClick((this.state.filter.is_flag == '') ? 1 : '', 'is_flag')}>
                                Flagged
                            </div>
                        </div>
                    </Col>
                    <Col md={3} className="mt-4">
                        <i className="export-list icon-export" onClick={e => this.exportUser()}></i>
                    </Col>
                </Row>
                <Row className="user-list">
                    <Col className="md-12 table-responsive">
                        <Table>
                            <thead>
                                <tr>
                                    <th className="left-th pl-4">Document</th>
                                    <th>Uniqe ID</th>
                                    <th>User name</th>
                                    <th>City</th>
                                    <th>Phone</th>
                                    <th>Total Net Winning</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    {walletRoleAccess && <th>Wallet</th>}
                                    <th className="right-th">Flag</th>
                                </tr>
                            </thead>
                            {
                                userslist.length > 0 ?
                                    _.map(userslist, (item, idx) => {
                                        return (
                                            <tbody key={idx}>
                                                <tr>
                                                    <td className="kyc-status left-th">

                                                        {/* {
                                                            ((item.type == 2 && item.is_bank_verified == "2") || (item.type == 1 && (item.pan_no == null && item.bank_document == null) || (item.pan_verified == "2" && item.is_bank_verified == "2")) || item.aadhar_status == "2") &&
                                                            <span className="ml-2">--</span>
                                                        }

                                                        {
                                                            ((item.type == 2 && item.is_bank_verified != "2") || (item.type == 1 && (
                                                                (item.pan_verified != "2" || item.is_bank_verified != "2") && (item.pan_no != null || item.bank_document != null)
                                                            )) || (item.aadhar_number != null && item.aadhar_number != ''))
                                                            &&
                                                            <div>

                                                                <span className="mr-2">
                                                                    {
                                                                        AUTO_KYC_ALLOW == 0 && (item.pan_verified == "1" && item.is_bank_verified == "1")
                                                                            ?
                                                                            <i className="icon-verified active"></i>
                                                                            :
                                                                            <i className="icon-pending-doc active"></i>

                                                                    }
                                                                    {
                                                                        AUTO_KYC_ALLOW == 1 && (item.pan_verified == "1" && item.is_bank_verified == "1")
                                                                        &&
                                                                        <i className="icon-verified active"></i>

                                                                    }
                                                                    KYC
                                                                </span>

                                                                {
                                                                    AUTO_KYC_ALLOW == 0 &&
                                                                    <span className="mr-2">
                                                                        {
                                                                            (item.pan_verified == "1" && item.is_bank_verified == "1")
                                                                                ?
                                                                                <i className="icon-verified active"></i>
                                                                                :
                                                                                <i className="icon-pending-doc active"></i>
                                                                        }

                                                                        KYC
                                                                    </span>
                                                                }
                                                                {
                                                                    AUTO_KYC_ALLOW == 1 &&
                                                                    <span className="mr-2">
                                                                        {
                                                                            (item.pan_verified == "1" && item.is_bank_verified == "1") && <i className="icon-verified active"></i>
                                                                        }
                                                                        KYC
                                                                    </span>
                                                                }
                                                                <span className="verify" onClick={() => this.openACverifyModal(item)}>
                                                                    {
                                                                        (
                                                                            (item.type == 2 && item.is_bank_verified == "1")
                                                                            &&
                                                                            (item.type == 1 &&
                                                                                (item.pan_verified == "1" && item.is_bank_verified == "1")
                                                                            )
                                                                            &&
                                                                            (item.aadhar_status == 1)
                                                                        )
                                                                            ?
                                                                            <i className="icon-verified active"></i>
                                                                            :

                                                                            <span className="verify" onClick={() => this.openACverifyModal(item)}>view</span>
                                                                    }
                                                                </span>

                                                            </div>
                                                        } */}
                                                          {
                                                            HF.getMasterData().allow_aadhar == "1" ?
                                                                <div>
                                                                    <span className="mr-2">
                                                                        {
                                                                            (item.aadhar_status == "1" && item.pan_verified == "1" && item.is_bank_verified == "1")
                                                                                ?
                                                                                <>
                                                                                    <i className="icon-verified active"></i>
                                                                                    <span className="verify" onClick={() => this.openACverifyModal(item)}>KYC view</span>
                                                                                </>
                                                                                :
                                                                                ((item.aadhar_status == "0" && item.aadhar_name != null) || (item.pan_verified == "0" && item.pan_no != null) || (item.is_bank_verified == "0" && item.bank_document != null))
                                                                                    ?
                                                                                    <React.Fragment>
                                                                                        <i className="icon-pending-doc active"></i>
                                                                                        <span className="verify" onClick={() => this.openACverifyModal(item)}>KYC view</span>
                                                                                    </React.Fragment>
                                                                                    :
                                                                                    (item.aadhar_status == "1" || item.pan_verified == "1" || item.is_bank_verified == "1")
                                                                                    &&
                                                                                    <React.Fragment>
                                                                                        <i className="icon-pending-doc active"></i>
                                                                                        <span className="verify" onClick={() => this.openACverifyModal(item)}>KYC view</span>
                                                                                    </React.Fragment>

                                                                        }

                                                                    </span>

                                                                </div>

                                                                :

                                                                <div>
                                                                    <span className="mr-2">
                                                                        {
                                                                            (item.pan_verified == "1" && item.is_bank_verified == "1")
                                                                                ?
                                                                                <>
                                                                                    <i className="icon-verified active"></i>
                                                                                    <span className="verify" onClick={() => this.openACverifyModal(item)}>KYC view</span>
                                                                                </>
                                                                                :
                                                                                ((item.pan_verified == "0" && item.pan_no != null) || (item.is_bank_verified == "0" && item.bank_document != null))
                                                                                    ?
                                                                                    <React.Fragment>
                                                                                        <i className="icon-pending-doc active"></i>
                                                                                        <span className="verify" onClick={() => this.openACverifyModal(item)}>KYC view</span>
                                                                                    </React.Fragment>
                                                                                    :
                                                                                    (item.pan_verified == "1" || item.is_bank_verified == "1")
                                                                                    &&
                                                                                    <React.Fragment>
                                                                                        <i className="icon-pending-doc active"></i>
                                                                                        <span className="verify" onClick={() => this.openACverifyModal(item)}>KYC view</span>
                                                                                    </React.Fragment>

                                                                        }


                                                                    </span>
                                                                </div>


                                                        }


                                                    </td>
                                                    <td>{item.user_unique_id}</td>

                                                    <td className="user-name text-ellipsis"><a onClick={() => this.props.history.push({ pathname: "/profile/" + item.user_unique_id, state: { activeTabId: '3' } })} className="text-click">{item.user_name ? item.user_name : '--'}</a></td>

                                                    <td className="font-weight-normal xtext-ellipsis">{item.city ? item.city : '--'}</td>

                                                    <td onClick={() => this.props.history.push("/profile/" + item.user_unique_id)} className="font-weight-normal user-name"><i className="icon-verified active"></i>{item.phone_no}</td>
                                                    <td>{item.net_winning != null ? item.net_winning:'0.00'}</td>
                                                    <td>
                                                        <TooltipItem placement="top" icon={`icon-email_verified mr-1 ${item.email_verified == 1 ? 'active' : ''}`} key={idx} item={item.email ? item.email : 'No email'} id={idx} />

                                                    </td>


                                                    <td><i
                                                        title="Manage status"
                                                        className={`icon-inactive-border ${item.status == 1 ? item.wdl_status == 2 ? 'withdraw-block' : '' : item.status == "0" ? 'active' : ''}`}
                                                        onClick={() => (item.status != 0) ? this.openStatusModal(item) : this.makeActive(item)} ></i></td>


                                                    {
                                                        walletRoleAccess &&
                                                        <td><i title="Manage wallet" className="icon-wallet" onClick={() => this.openWalletModal(item)}></i></td>
                                                    }

                                                    <td className="right-th">
                                                        {item.is_flag == 1 &&
                                                            <img src={Images.FLAG_ENABLE} alt="" />
                                                        }
                                                        {item.is_flag == 0 &&
                                                            <i className="icon-flag"></i>
                                                        }
                                                    </td>
                                                </tr>
                                            </tbody>
                                        )
                                    })

                                    :
                                    <tbody>
                                        <tr>
                                            <td colSpan="8">
                                                {(userslist.length == 0 && !posting) ?
                                                    <div className="no-records">
                                                        No Record Found.</div>
                                                    :
                                                    <Loader />
                                                }
                                            </td>
                                        </tr>
                                    </tbody>
                            }
                        </Table>
                        {
                             
                            userslist.length != 0 &&
                            <div className="custom-pagination lobby-paging">
                                <Pagination
                                    activePage={filter.current_page}
                                    itemsCountPerPage={filter.items_perpage}
                                    totalItemsCount={parseInt(total)}
                                    pageRangeDisplayed={5}
                                    onChange={e => this.handlePageChange(e)}
                                />
                            </div>
                        }



                        <div className="active-modal">
                            <ChangeStatus closeStatusModal={this.closeStatusModal} user_unique_id={this.state.user_unique_id} StatusmodalIsOpen={this.state.StatusmodalIsOpen} user_full_name={this.state.user_full_name} item={this.state.wdlStatus} ></ChangeStatus>
                        </div>

                        <div className="verify-card-modal">
                            {/* {console.log('userUniqueID', this.state.user_unique_id)} */}
                            <Modal
                                isOpen={this.state.ACverifymodalIsOpen}
                                className="modal-md"
                                toggle={this.closeACverifyModal}
                            >
                                <ModalHeader toggle={this.toggle}>Account Verify</ModalHeader>
                                <ModalBody>
                                    <VerifyDocument
                                        user_unique_id={this.state.user_unique_id}
                                        nameflag="1"
                                        closeACverifyModal={this.closeACverifyModal}
                                        userDetail={this.state.userDetail}
                                        updatePanDtl={this.reloadPanModalData}
                                        updateBankDtl={this.reloadBankModalData}
                                    />
                                </ModalBody>

                            </Modal>
                        </div>
                        {WalletmodalIsOpen &&
                            <div className="wallet-modal">
                                <Wallet {...Wallet_props} />
                            </div>
                        }
                    </Col>
                </Row>
            </Fragment >
        )
    }
}
export default Manageuser 