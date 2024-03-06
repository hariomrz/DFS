import React, { Component, Fragment } from "react";
import { TabContent, TabPane, Nav, NavItem, NavLink, Row, Col, Button, Table, Input, Modal, ModalHeader, ModalBody } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Select from 'react-select';
import Pagination from "react-js-pagination";
import _ from 'lodash';
import { Base64 } from 'js-base64';
import ActionRequestModal from '../../components/ActionRequestModal/ActionRequestModal';
import HF from '../../helper/HelperFunction';
import { MomentDateComponent } from "../../components/CustomComponent";
import { MSG_ACTIVE_DISTRIBUTOR, MSG_BLOCK_DISTRIBUTOR } from "../../helper/Message";

export default class Distributor extends Component {
    constructor(props) {
        super(props)
        this.state = {
            request: { amount: 0 },
            recharge: { amount: 0 },
            selectedUser: '',
            fileName: '',
            ImageName: '',
            adminList: [],
            userList: [],
            search: { keyword: '' },
            total: 0,
            filter: { current_page: 1, items_perpage: NC.ITEMS_PERPAGE },
            filter_req: { current_page: 1, items_perpage: NC.ITEMS_PERPAGE },
            filter_tran: { current_page: 1, items_perpage: NC.ITEMS_PERPAGE },
            created_by: (this.props.match.params.unique_id) ? Base64.decode(this.props.match.params.unique_id) : WSManager.getLoggedInID(),
            distributor_detail: (this.props.location.detail) ? this.props.location.detail : [],
            activeTab: WSManager.getRole() !== "4" ? "1" : (WSManager.getRole() === "4" && !this.props.match.params.unique_id) ? "3" : "2",

            ListExport: false,
            appRecPosting: false,
            backDisable: false
        }
        this.openRCModal = this.openRCModal.bind(this);
        this.closeRCModal = this.closeRCModal.bind(this);
        this.onChangeImage = this.onChangeImage.bind(this);
        this.resetFile = this.resetFile.bind(this);
        this.rechargeRequest = this.rechargeRequest.bind(this);
        this.approveRecharge = this.approveRecharge.bind(this);

        //recharge fantasy user 
        this.openRCFModal = this.openRCFModal.bind(this);
        this.closeRCFModal = this.closeRCFModal.bind(this);
        this.rechargeUser = this.rechargeUser.bind(this);
        this.searchUser = this.searchUser.bind(this);
        this.handleSearchUser = this.handleSearchUser.bind(this);
    }

    updateStorageType(value) {
        this.setState({ typeId: value.id });
    }

    componentDidMount() {
        this.getAdminList();
        if (this.props.match.params.unique_id) { this.getAdminDetail() };
        if (this.state.activeTab === "2") {
            this.getRechargeList()
        };
    }

    componentDidUpdate = () => {

        let unique_id = '';
        if (this.props.match.params.unique_id) {
            unique_id = Base64.decode(this.props.match.params.unique_id)
        };

        if (unique_id && unique_id != this.state.created_by) {
            this.setState({
                created_by: unique_id
            }, () => {
                this.getAdminDetail();
                this.getAdminList();

            })

        }
    }
    getAdminDetail = () => {
        let params = {
            "admin_id": Base64.decode(this.props.match.params.unique_id),
        }
        WSManager.Rest(NC.baseURL + NC.GET_ADMIN_DETAIL, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    distributor_detail: ResponseJson.data
                }, () => {
                    this.setState({
                        activeTab: this.state.distributor_detail.role !== "4" ? "1" : (this.state.distributor_detail.role === "4" && !this.props.match.params.unique_id) ? "3" : "2",
                    })
                })

            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    getAdminList = () => {
        let { filter, created_by, ListExport } = this.state
        let params = {
            "keyword": filter.keyword,
            "current_page": filter.current_page,
            "created_by": created_by,
            "limit": filter.items_perpage,
            "csv": ListExport

        }
        WSManager.Rest(NC.baseURL + NC.GET_ADMIN_LIST, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    adminList: ResponseJson.data.admin_list,
                    total: ResponseJson.data.total,
                })

            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    exportList = () => {
        var query_string = '';//pairs.join('&');        
        let { created_by, filter, activeTab } = this.state
        let searchKey = !_.isUndefined(filter.keyword) ? filter.keyword : ''
        query_string = 'csv=TRUE&keyword=' + searchKey;

        let URL = ''
        if (activeTab === '1') {
            query_string += "&created_by=" + created_by;
            URL = NC.baseURL + 'adminapi/distributor/get_admin_list?'
        }

        if (activeTab === '2') {
            query_string += "&admin_id=" + created_by;
            URL = NC.baseURL + 'adminapi/distributor/get_recharge_list?'
        }

        if (activeTab === '3') {
            query_string += "&from_admin_id=" + created_by;
            URL = NC.baseURL + 'adminapi/distributor/get_recharge_request_list?'
        }

        let sessionKey = WSManager.getToken();
        query_string += "&Sessionkey" + "=" + sessionKey;
        window.open(URL + query_string, '_blank');
    }

    getRechargeRequestList = () => {
        let { filter, created_by, ListExport } = this.state
        let params = {
            "current_page": filter.current_page,
            "from_admin_id": created_by,
            "limit": filter.items_perpage,
            "status": "0",
            "csv": ListExport,
        }
        WSManager.Rest(NC.baseURL + NC.GET_RECHARGE_REQUEST_LIST, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    rechargeReqList: ResponseJson.data.list,
                    reqtotal: ResponseJson.data.total,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }
    handlePageChange(current_page) {

        let filter = this.state.filter;
        filter['current_page'] = current_page;
        this.setState(
            { filter: filter },
            () => {
                this.getAdminList();
            });

    }
    handleReqPageChange(current_page) {

        let filter = this.state.filter_req;
        filter['current_page'] = current_page;
        this.setState(
            { filter_req: filter },
            () => {
                this.getRechargeRequestList();
            });

    }
    handleTranPageChange(current_page) {

        let filter = this.state.filter_tran;
        filter['current_page'] = current_page;
        this.setState(
            { filter_tran: filter },
            () => {
                this.getRechargeList();
            });

    }
    handleSearch(keyword) {
        let filter = this.state.filter;
        filter['keyword'] = keyword.target.value;
        this.setState({
            filter: filter,
        },
            () => { this.getAdminList(); }
        );
    }
    toggle(tab) {
        if (this.state.activeTab !== tab) {
            this.setState({
                activeTab: tab,
                PageScroll: false
            }, () => {
                if (this.state.activeTab === '1') {
                    this.getAdminList()
                }

                if (this.state.activeTab === '2') {
                    this.getRechargeList()
                }

                if (this.state.activeTab === '3') {
                    this.getRechargeRequestList()
                }
            });
        }
    }

    openRCModal(request, call_frorm) {
        this.setState({
            rcmCallFrorm: call_frorm,
            RCmodalIsOpen: true,
            request: request,
            fileName: (request.upload_reciept) ? NC.RECHARGE_SLIP + request.upload_reciept : ''
        });
    }
    closeRCModal() {
        let tempReq = this.state.request
        if (this.state.rcmCallFrorm === 1) {
            tempReq.amount = ""
            tempReq.reference_id = ""
        }
        this.setState({
            request: tempReq,
            RCmodalIsOpen: false
        });
    }

    //recharge fantasy user popup
    openRCFModal(recharge) {
        this.setState({
            RCFmodalIsOpen: true,
            recharge: recharge,
        });
    }
    closeRCFModal() {
        let tempRecharge = this.state.recharge
        tempRecharge.amount = ""
        tempRecharge.user_unique_id = ""
        this.setState({
            selectedUser: '',
            recharge: tempRecharge,
            RCFmodalIsOpen: false
        });
    }
    onChangeImage = (event) => {
        this.setState({
            TempFileName: URL.createObjectURL(event.target.files[0]),
        }, function () {
            this.validateForm()
        });
        const file = event.target.files[0];
        if (!file) {
            return;
        }
        var data = new FormData();
        data.append("userfile", file);
        WSManager.multipartPost(NC.baseURL + NC.DISTRIBUTOR_IMAGE_UPLOAD, data)
            .then(responseJson => {
                this.setState({
                    fileName: responseJson.data.image_url,
                    fileUplode: responseJson.data.image_url,
                    ImageName: responseJson.data.file_name
                });
            }).catch(error => {
                notify.show(NC.SYSTEM_ERROR, "error", 3000);
            });
    }

    resetFile(event) {
        event.preventDefault();
        document.getElementById("banner_image").value = "";
        this.setState({
            fileName: null,
            fileUplode: null,
        }, function () {
            this.validateForm()
        });
    }
    validateForm() {
        const { target_url, fileName, TempFileName } = this.state
        this.setState({ AddBannerPosting: false })
    }

    rechargeRequest() {
        this.setState({ posting: true })
        let params = {
            to_admin_id: WSManager.getCreatedBy(),
            from_admin_id: WSManager.getLoggedInID(),
            amount: this.state.request.amount,
            reference_id: this.state.request.reference_id,
            upload_reciept: this.state.ImageName


        };
        WSManager.Rest(NC.baseURL + NC.RECHARGE_REQUEST, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {

                this.setState({
                    posting: false, RCmodalIsOpen: false
                }, () => this.getRechargeRequestList())
                notify.show(responseJson.message, "success", 5000);
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
            this.setState({ posting: false })
        });

    }
    //Recharge fantasy user 
    rechargeUser() {
        let { distributor_detail, recharge } = this.state
        this.setState({ posting: true })
        let params = {
            admin_id: WSManager.getLoggedInID(),
            user_unique_id: recharge.user_unique_id,
            amount: recharge.amount
        };
        WSManager.Rest(NC.baseURL + NC.DISTRIBUTOR_RECHARGE_USER, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                let tempDisDtl = distributor_detail
                tempDisDtl['balance'] = parseInt(tempDisDtl.balance) - parseInt(recharge.amount)

                this.closeRCFModal()
                this.setState({
                    distributor_detail: tempDisDtl,
                    posting: false,
                }, () => this.getRechargeList())
                notify.show(responseJson.message, "success", 5000);
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
            this.setState({ posting: false })
        });

    }

    handleChange = (e) => {
        let name = e.target.name;
        let value = e.target.value;
        let request = this.state.request;

        if (name === 'amount') {
            request[name] = HF.decimalValidate(value, 3);
        } else {
            request[name] = value;
        }

        this.setState({
            request
        }, () => {
            this.validateFormReq(name, value)
        });
    }
    handleChangeRCF = (e) => {

        let name = e.target.name;
        let value = e.target.value;
        let recharge = this.state.recharge;
        if (name === 'amount') {
            recharge[name] = HF.decimalValidate(value, 3);
        }

        this.setState({
            recharge
        }, () => {
            this.validateFormReq(name, value)
        });
    }
    handleChangeRCF2 = (e) => {


        let name = 'user_unique_id';
        let value = e.value;

        let recharge = this.state.recharge;
        recharge[name] = value;
        this.setState({
            recharge,
            selectedUser: e.value
        }, () => {
            this.validateFormReq(name, value)
        });
    }

    validateFormReq = (name, value) => {
        let SubjectValid = this.state.request.amount

        switch (name) {
            case "amount":
                SubjectValid = (value.length > 0) ? true : false;
                break;

            default:
                break;
        }
        this.setState({
            formValid: (SubjectValid)
        })
    }

    approveRecharge() {
        this.setState({ appRecPosting: true })
        let params = {
            to_admin_id: WSManager.getLoggedInID(),
            recharge_id: this.state.request.recharge_id

        };
        WSManager.Rest(NC.baseURL + NC.APPROVE_RECHARGE_REQUEST, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                let tempDisDtl = this.state.distributor_detail
                tempDisDtl['balance'] = parseInt(tempDisDtl.balance) - parseInt(this.state.request.amount)
                this.setState({
                    distributor_detail: tempDisDtl,
                    RCmodalIsOpen: false
                }, () => this.getRechargeRequestList())
                notify.show(responseJson.message, "success", 5000);
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
            this.setState({ appRecPosting: false })
        });

    }

    searchUser = (e) => {
        let params = {
            "keyword": e
        }
        WSManager.Rest(NC.baseURL + NC.DISTRIBUTOR_SEARCH_USER, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    userList: ResponseJson.data
                })
            } else {
                return [];
            }
        }).catch(error => {
            return [];
        })
    }
    handleSearchUser(keyword) {
        let search = this.state.search;
        search['keyword'] = keyword.target.value;
        this.setState({
            search: search,
        });
    }
    getRechargeList = () => {
        let { filter_tran, created_by, ListExport } = this.state
        let params = {
            "current_page": filter_tran.current_page,
            "admin_id": created_by,
            "limit": filter_tran.items_perpage,
            "csv": ListExport,
        }
        WSManager.Rest(NC.baseURL + NC.DISTRIBUTOR_RECHARGE_LIST, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    rechargeList: ResponseJson.data.transactionList,
                    trantotal: ResponseJson.data.total,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }
    handleChangeStatus = (status) => {

        this.setState({ posting: true })

        let params = { admin_id: this.state.distributor_detail.admin_id, status: this.state.actStatus };
        WSManager.Rest(NC.baseURL + NC.CHANGE_DISTRIBUTOR_STATUS, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.getAdminDetail();
                this.toggleActionPopup(this.state.actStatus)
                this.setState({ posting: false })
                notify.show(responseJson.message, "success", 5000);
            }
            this.setState({ posting: false })

        });
    }

    //function to toggle action popup
    toggleActionPopup = (status) => {
        this.setState({
            Message: (status === "1") ? MSG_ACTIVE_DISTRIBUTOR : MSG_BLOCK_DISTRIBUTOR,
            actStatus: status,
            ActionPopupOpen: !this.state.ActionPopupOpen
        })
    }

    goBack = () => {
        this.setState({
            backDisable: true
        }, () => {
            this.props.history.goBack()
            setTimeout(() => {
                this.setState({
                    backDisable: false
                })
            }, 1000);
        })
    }

    render() {
        const { appRecPosting, fileName, adminList, total, filter, activeTab, userList, Message, ActionPopupOpen, posting, distributor_detail, backDisable } = this.state
        const ActionCallback = {
            Message: Message,
            modalCallback: this.toggleActionPopup,
            ActionPopupOpen: ActionPopupOpen,
            modalActioCallback: this.handleChangeStatus,
            posting: posting
        }
        return (
            <Fragment>
                <div className="animated fadeIn distributor mt-4">
                    <ActionRequestModal {...ActionCallback} />
                    {
                        this.props.match.params.unique_id &&
                        <Fragment>
                            <Row>
                                <Col md={6}>
                                    <h2 className="h2-cls mb-30">
                                        {
                                            WSManager.getRole() === "2" && "Master Distributor"
                                        }
                                        {
                                            WSManager.getRole() === "3" && "Distributor"
                                        }
                                        {
                                            WSManager.getRole() === "4" && "Agent"
                                        }
                                    </h2>
                                </Col>
                                <Col md={6}>
                                    {
                                        (WSManager.getRole() != distributor_detail.role) &&
                                        <button
                                            disabled={backDisable}
                                            className="dis-back-btn"
                                            id="backbtn"
                                            onClick={() => this.goBack()}
                                        >{'<'} Back to Distributors
                                        </button>
                                    }
                                </Col>
                            </Row>
                            <Row>
                                <Col md={12}>
                                    <div className="disprofile-box">
                                        <Row>
                                            <Col md={3}>
                                                <h4 className="username">{distributor_detail.fullname}</h4>
                                                <p className="location">{distributor_detail.city}</p>
                                            </Col>
                                            <Col md={2}>
                                                <h2 className="username">₹{(distributor_detail.balance) ? distributor_detail.balance : "0"}</h2>
                                                <p className="location des-text">Total Balance</p>

                                            </Col>
                                            <Col md={2}>
                                                <h2 className="username">{(distributor_detail.commission_percent) ? HF.isFloat(distributor_detail.commission_percent) ? parseInt(distributor_detail.commission_percent).toFixed(2) : distributor_detail.commission_percent : "0"}%</h2>
                                                <p className="location des-text">Commission %</p>
                                            </Col>
                                            <Col md={5}>
                                                {WSManager.getLoggedInID() == distributor_detail.created_by &&
                                                    <h2 className="username btn-act">
                                                        {WSManager.getLoggedInID() == distributor_detail.created_by &&
                                                            <i title="Edit" className="icon-edit" onClick={() => this.props.history.push({ pathname: "/distributors/add", state: { distributor_detail: distributor_detail } })}></i>

                                                        }

                                                        {WSManager.getLoggedInID() == distributor_detail.created_by && distributor_detail.status == "1" &&

                                                            <i title="Block" className="icon-inactive mt-4" onClick={() => this.toggleActionPopup("0")}></i>

                                                        }
                                                        {WSManager.getLoggedInID() == distributor_detail.created_by && distributor_detail.status == "0" &&
                                                            <i title="Active" className="icon-inactive icon-active ml-4 mt-4" onClick={() => this.toggleActionPopup("1")}></i>

                                                        } </h2>
                                                }
                                                {(WSManager.getRole() == distributor_detail.role && WSManager.getRole() > 1) &&
                                                    <Button
                                                        className="btn-secondary-outline request-btn float-right"
                                                        onClick={() => this.openRCModal(distributor_detail, 1)}>
                                                        Request Balance
                                                    </Button>
                                                }
                                            </Col>
                                        </Row>
                                    </div>
                                </Col>
                            </Row>
                        </Fragment>
                    }
                    {
                        !this.props.match.params.unique_id &&
                        <Row>
                            <Col md={6}>
                                <h1 className="h1-cls">Distributor</h1>
                            </Col>

                            <Col md={6}>
                                {WSManager.getRole() !== "4" && <Button className="btn-secondary-outline float-right" onClick={() => this.props.history.push("/distributors/add")}>Add New</Button>}
                            </Col>
                        </Row>
                    }
                    {
                        this.props.match.params.unique_id &&
                        //for sub role 
                        <Row>
                            <Col md={6}>
                                <div className="search-box float-left">
                                    <Input
                                        placeholder="Search by name, mobile"
                                        value={this.state.filter.keyword}
                                        onChange={e => this.handleSearch(e)} />
                                </div>
                            </Col>
                            <Col md={6}>
                                {
                                    WSManager.getRole() == distributor_detail.role &&
                                    <Fragment>
                                        {WSManager.getRole() != 4 && <Button className="btn-secondary-outline float-right Recharge" onClick={() => this.props.history.push("/distributors/add")}>Add New</Button>}
                                        {WSManager.getRole() > 1 &&
                                            <Button className="btn-secondary-outline float-right mr-2" onClick={() => this.openRCFModal(this.state.distributor_detail)}>Recharge</Button>
                                        }
                                    </Fragment>
                                }
                            </Col>
                        </Row>

                    }
                    <div className="promocode-list-view">
                        <Row className="filter-userlist">
                            <Col md={6}>

                            </Col>
                            <Col md={6}>
                                {
                                    !this.props.match.params.unique_id &&
                                    <div className="search-box">
                                        <Input
                                            placeholder="Search by name, mobile"
                                            value={this.state.filter.keyword}
                                            onChange={e => this.handleSearch(e)}
                                        />
                                    </div>
                                }
                            </Col>
                        </Row>
                        <Row className="mb-2">
                            <Col md={12}>
                                <div className="cursor-pointer">
                                    <i className="export-list icon-export" onClick={e => this.exportList()}></i>
                                </div>
                            </Col>
                        </Row>
                        <Row className="user-navigation">
                            <div className="w-100">
                                <Nav tabs>
                                    {distributor_detail.role !== "4" && <NavItem className={activeTab === '1' ? "active" : ""}
                                        onClick={() => { this.toggle('1'); }}>
                                        <NavLink>
                                            Distributors
                                        </NavLink>
                                    </NavItem>}
                                    {this.props.match.params.unique_id && <NavItem className={activeTab === '2' ? "active" : ""}
                                        onClick={() => { this.toggle('2'); }}>
                                        <NavLink>
                                            Transaction List
                                        </NavLink>
                                    </NavItem>}
                                    <NavItem className={activeTab === '3' ? "active" : ""}
                                        onClick={() => { this.toggle('3'); }}>
                                        <NavLink>
                                            Requested Balance
                                        </NavLink>
                                    </NavItem>
                                </Nav>
                                <TabContent activeTab={activeTab}>
                                    <TabPane tabId="1">
                                        <Row>
                                            <Col md={12} className="table-responsive common-table">
                                                <Table>
                                                    <thead>
                                                        <tr>
                                                            <th className="left-th">Distributor Name</th>
                                                            <th>User Name</th>
                                                            <th>Type</th>
                                                            <th>Mobile</th>
                                                            <th>City</th>
                                                            <th>Commission %</th>
                                                            <th>Balance</th>
                                                        </tr>
                                                    </thead>

                                                    {total > 0 ?

                                                        _.map(adminList, (item, idx) => {
                                                            return (
                                                                <tbody key={idx}>
                                                                    <tr>
                                                                        <td className="role-name" onClick={() => this.props.history.push({ pathname: "/distributors/detail/" + Base64.encode(item.admin_id), detail: item })}>{item.fullname}</td>
                                                                        <td>{item.username}</td>
                                                                        <td>{(item.role == 2) ? "Master Distributor" : (item.role == 3) ? "Distributor" : "Agent"}</td>
                                                                        <td>{item.mobile}</td>
                                                                        <td>{item.city}</td>
                                                                        <td>{item.commission_percent}</td>
                                                                        <td>{item.balance}</td>
                                                                    </tr>
                                                                </tbody>
                                                            )
                                                        })

                                                        :

                                                        <tbody>
                                                            <tr>
                                                                <td colSpan="12">
                                                                    <div className="no-records">No Record Found.</div>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    }
                                                </Table>
                                                {
                                                    total > filter.items_perpage &&
                                                    <div className="custom-pagination float-right">
                                                        <Pagination
                                                            activePage={filter.current_page}
                                                            itemsCountPerPage={filter.items_perpage}
                                                            totalItemsCount={total}
                                                            pageRangeDisplayed={5}
                                                            onChange={e => this.handlePageChange(e)}
                                                        />
                                                    </div>
                                                }

                                            </Col>
                                        </Row>
                                    </TabPane>
                                    {
                                        (activeTab == '2') &&
                                        <TabPane tabId="2">
                                            <Row>
                                                <Col sm="12">
                                                    <Row>
                                                        <Col md={12} className="table-responsive common-table">
                                                            <Table>
                                                                <thead>
                                                                    <tr>
                                                                        <th className="left-th">Username</th>
                                                                        <th>Amount</th>
                                                                        <th>Created Date & Time</th>
                                                                    </tr>
                                                                </thead>

                                                                {this.state.trantotal > 0 ?

                                                                    _.map(this.state.rechargeList, (item, idx) => {
                                                                        return (
                                                                            <tbody key={idx}>
                                                                                <tr>
                                                                                    <td className="role-name">{item.user_name}</td>
                                                                                    <td>{item.amount}</td>
                                                                                    <td>
                                                                                        {/* <MomentDateComponent data={{ date: item.created_date, format: "D MMM YYYY- hh:mm a" }} /> */}
                                {HF.getFormatedDateTime(item.created_date, "D MMM YYYY- hh:mm a")}
                                                                                    
                                                                                    </td>

                                                                                </tr>
                                                                            </tbody>
                                                                        )
                                                                    })

                                                                    :

                                                                    <tbody>
                                                                        <tr>
                                                                            <td colSpan="12">
                                                                                <div className="no-records">No Record Found.</div>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                }
                                                            </Table>
                                                            {
                                                                this.state.trantotal > this.state.filter_tran.items_perpage &&
                                                                <div className="custom-pagination">
                                                                    <Pagination
                                                                        activePage={this.state.filter_tran.current_page}
                                                                        itemsCountPerPage={this.state.filter_tran.items_perpage}
                                                                        totalItemsCount={this.state.trantotal}
                                                                        pageRangeDisplayed={5}
                                                                        onChange={e => this.handleTranPageChange(e)}
                                                                    />
                                                                </div>
                                                            }

                                                        </Col>
                                                    </Row>
                                                </Col>
                                            </Row>

                                        </TabPane>
                                    }
                                    {
                                        activeTab == '3' &&
                                        <TabPane tabId="3">
                                            <Row>
                                                <Col sm="12">
                                                    <Row>
                                                        <Col md={12} className="table-responsive common-table">
                                                            <Table>
                                                                <thead>
                                                                    <tr>
                                                                        <th className="left-th">Username</th>
                                                                        <th>Amount</th>
                                                                        <th>Reference id </th>
                                                                        <th>Created Date & Time</th>
                                                                        <th>Action</th>
                                                                    </tr>
                                                                </thead>

                                                                {this.state.reqtotal > 0 ?

                                                                    _.map(this.state.rechargeReqList, (item, idx) => {
                                                                        return (
                                                                            <tbody key={idx}>
                                                                                <tr>
                                                                                    <td
                                                                                        className="role-name"
                                                                                        onClick={() => this.props.history.push({ pathname: "/distributors/detail/" + Base64.encode(item.from_admin_id), detail: item })}>
                                                                                        {((item.from_admin_id == this.state.created_by)) ? item.fullname : item.fullname}
                                                                                    </td>
                                                                                    <td>{item.amount}</td>
                                                                                    <td>{item.reference_id}</td>
                                                                                    <td>
                                                                                        {/* <MomentDateComponent data={{ date: item.created_date, format: "D MMM YYYY- hh:mm a" }} /> */}
                                {HF.getFormatedDateTime(item.created_date, "D MMM YYYY- hh:mm a")}
                                                                                    
                                                                                    </td>
                                                                                    <td>
                                                                                        {item.status == 0 && item.from_admin_id != this.state.created_by &&
                                                                                            <Button
                                                                                                className="app-btn btn-secondary-outline"
                                                                                                onClick={() => this.openRCModal(item, 2)}>
                                                                                                Approve
                                                                                            </Button>}
                                                                                        {item.status == 0 && item.from_admin_id == this.state.created_by && "Pending"}
                                                                                        {item.status == 1 && "Approved"}
                                                                                    </td>
                                                                                </tr>
                                                                            </tbody>
                                                                        )
                                                                    })

                                                                    :

                                                                    <tbody>
                                                                        <tr>
                                                                            <td colSpan="12">
                                                                                <div className="no-records">No Record Found.</div>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                }
                                                            </Table>
                                                            {
                                                                this.state.reqtotal > this.state.filter_req.items_perpage &&
                                                                <div className="custom-pagination">
                                                                    <Pagination
                                                                        activePage={this.state.filter_req.current_page}
                                                                        itemsCountPerPage={this.state.filter_req.items_perpage}
                                                                        totalItemsCount={this.state.reqtotal}
                                                                        pageRangeDisplayed={5}
                                                                        onChange={e => this.handleReqPageChange(e)}
                                                                    />
                                                                </div>
                                                            }

                                                        </Col>
                                                    </Row>
                                                </Col>
                                            </Row>
                                        </TabPane>
                                    }
                                </TabContent>
                            </div>
                        </Row>
                    </div>
                </div>

                <div>
                    <Modal
                        isOpen={this.state.RCFmodalIsOpen}
                        className="modal-md distributor"
                        toggle={this.closeRCModal}
                    >
                        <ModalHeader toggle={this.closeRCFModal}>Recharge User</ModalHeader>
                        <ModalBody>
                            <Row>
                                <Col md={12}>
                                    <div className="mt-2 input-row" >
                                        <label className="inputlabel">Amount</label>
                                        <input
                                            className="inputpop"
                                            type="number"
                                            name="amount"
                                            value={this.state.recharge.amount}
                                            onChange={this.handleChangeRCF}
                                        />
                                    </div>
                                </Col>

                            </Row>
                            <Row>
                                <Col md={12}>
                                    <div className="mt-2 input-row s-user-modal" >
                                        <label className="inputlabel">Select User {this.state.request.recharge_id}</label>
                                        <Select
                                            name="user_unique_id"
                                            isSearchable={true}
                                            className="inputpop"
                                            options={this.state.userList}
                                            placeholder="Search User"
                                            menuIsOpen={true}
                                            onInputChange={e => this.searchUser.bind(this)(e)}
                                            onChange={e => this.handleChangeRCF2.bind(this)(e)}
                                            value={this.state.selectedUser}
                                        />

                                    </div>

                                </Col>
                            </Row>
                            <Button className="btn-secondary mt-4 mb-3" onClick={this.rechargeUser}>Recharge</Button>
                        </ModalBody>
                    </Modal>

                    <Modal
                        isOpen={this.state.RCmodalIsOpen}
                        className="modal-md distributor"
                        toggle={this.closeRCModal}
                    >
                        <ModalHeader toggle={this.closeRCModal}>Request Balance</ModalHeader>
                        <ModalBody>

                            <Row>
                                <Col md={6}>
                                    <div className="mt-2 input-row" >
                                        <label className="inputlabel">Amount</label>
                                        <input readOnly={this.state.request.recharge_id}
                                            className="inputpop"
                                            type="number"
                                            name="amount"
                                            value={this.state.request.amount}
                                            onChange={this.handleChange}
                                        />
                                    </div>
                                </Col>
                                <Col md={6}>
                                    <div className="mt-2 input-row">
                                        <label className="inputlabel">Reference id</label>
                                        <input readOnly={this.state.request.recharge_id}
                                            className="inputpop"
                                            type="text"
                                            name="reference_id"
                                            value={this.state.request.reference_id}
                                            onChange={this.handleChange}
                                        />
                                    </div>

                                </Col>
                            </Row>
                            <Row>
                                <Col md={6} className="b-input-label inputlabel mt-4">{(!this.state.request.recharge_id) ? "Upload" : ""}  Reciept</Col>
                                <Col md={6} className="mt-4">
                                    {!this.state.request.recharge_id &&
                                        <Input
                                            type="file"
                                            name='banner_image'
                                            id="banner_image"
                                            onChange={this.onChangeImage}
                                        />
                                    }
                                    {this.state.fileName && this.state.fileName != 'undefined' && (
                                        <div className="banner-img">
                                            {!this.state.request.recharge_id && this.state.fileName_ &&
                                                <Button className="btn-secondary mt-4 mb-3" onClick={this.resetFile}>Remove</Button>
                                            }
                                            <img className="img-cover" src={this.state.fileName} />
                                        </div>
                                    )}
                                </Col>
                            </Row>
                            {this.state.request.recharge_id &&
                                <Button
                                    disabled={appRecPosting}
                                    className="btn-secondary mt-4 mb-3"
                                    onClick={this.approveRecharge}>
                                    Approve
                                    </Button>
                            }
                            {!this.state.request.recharge_id &&
                                <Button className="btn-secondary mt-4 mb-3" onClick={this.rechargeRequest}>Request</Button>
                            }
                        </ModalBody>

                    </Modal>
                </div>
            </Fragment>
        )
    }
}
