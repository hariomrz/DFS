import React, { Component, Fragment } from "react";
import { Row, Col, Button, Table, Input, Modal, ModalBody, ModalHeader } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import Pagination from "react-js-pagination";
import Loader from '../../components/Loader';
import HF from '../../helper/HelperFunction';
import SelectDate from "../../components/SelectDate";
import { MomentDateComponent } from "../../components/CustomComponent";
import moment from "moment-timezone";
export default class UserReferralReport extends Component {
    constructor(props) {
        super(props)
        this.state = {
            TotalUser: 0,
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            startDate: '',
            endDate: '',
            FromDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
            ToDate: new Date(moment().format('D MMM YYYY')),
            UserReportList: [],
            Keyword: '',
            sortField: 'first_name',
            isDescOrder: true,
            isLineupModalOpen: false,
            detialData: [],
            posting: false,
            UserId: '',
            refReportData: ''
        }
        this.SearchCodeReq = _.debounce(this.SearchCodeReq.bind(this), 500);
    }
    componentDidMount() {
        this.getReportUser()
    }
    lineupDetailModal(data, user_id) {

        let {FromDate, ToDate} = this.state;

        let params = {
            "user_id":user_id,
            "from_date": FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '',
            "to_date":  ToDate ? moment(ToDate).format('YYYY-MM-DD') : ''
        }

        WSManager.Rest(NC.baseURL + NC.REFERRAL_FRIENDS_REPORT, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                   refReportData: ResponseJson.data
                })
            }
        })
        
        this.setState({ detialData: data, UserId: user_id })
        this.setState(prevState => ({
            isLineupModalOpen: !prevState.isLineupModalOpen
        }));
    }

    getReportUser = () => {
        this.setState({ posting: true })
        const { PERPAGE, CURRENT_PAGE, Keyword, FromDate, ToDate, sortField, isDescOrder } = this.state
        let params = {
            items_perpage: PERPAGE,
            total_items: 0,
            current_page: CURRENT_PAGE,
            sort_order: isDescOrder ? "ASC" : 'DESC',
            sort_field: sortField,
            csv: false,
            from_date: FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '',
            to_date: ToDate ? moment(ToDate).format('YYYY-MM-DD') : '',
            keyword: Keyword,
        }
        WSManager.Rest(NC.baseURL + NC.REFERAL_REPORT, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    posting: false,
                    UserReportList: ResponseJson.data.result,
                    TotalUser: ResponseJson.data.total
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    exportReport_Post = () => {
        const { Keyword, FromDate, ToDate, sortField, isDescOrder } = this.state
        let params = {
            sort_order: isDescOrder ? "ASC" : 'DESC',
            sort_field: sortField,
            from_date: FromDate,
            to_date: ToDate,
            keyword: Keyword,
            report_type: "referral"
        }

        WSManager.Rest(NC.baseURL + NC.EXPORT_REPORT, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                notify.show(ResponseJson.message, "success", 5000);
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    exportReport_Get = () => {
        let { Keyword, FromDate, ToDate, isDescOrder, sortField } = this.state
        let tempFromDate = ''
        let tempToDate = ''
        let sOrder = isDescOrder ? "ASC" : 'DESC'
        if (FromDate != '' && ToDate != '') {
            tempFromDate = WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD')
            tempToDate = moment(ToDate).format("YYYY-MM-DD");
        }

        var query_string = 'report_type=referral&csv=1&keyword=' + Keyword + '&from_date=' + tempFromDate + '&to_date=' + tempToDate + '&sort_order=' + sOrder + '&sort_field=' + sortField;
        var export_url = 'adminapi/index.php/report/referal_report?';


        HF.exportFunction(query_string, export_url)
    }

    exportReffered = () => {
        let { FromDate, ToDate, isDescOrder, UserId } = this.state
        let tempFromDate = ''
        let tempToDate = ''
        let sOrder = isDescOrder ? "ASC" : 'DESC'
        if (FromDate != '' && ToDate != '') {
            // tempFromDate = FromDate ? HF.getFormatedDateTime(FromDate, 'YYYY-MM-DD') : '';
            // tempToDate = ToDate ? HF.getFormatedDateTime(ToDate, 'YYYY-MM-DD') : '';
            tempFromDate = WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD')
            tempToDate = moment(ToDate).format("YYYY-MM-DD");
        }

        var query_string = 'csv=1&user_id=' + UserId + '&from_date=' + tempFromDate + '&to_date=' + tempToDate;

        var export_url = 'adminapi/index.php/report/export_referral_list_by_user?';

        HF.exportFunction(query_string, export_url)
    }

    handleTypeChange = (value, name) => {
        if (value != null)
            this.setState({ [name]: value }, this.getReportUser)
    }

    handleDate = (date, dateType) => {
        this.setState({ [dateType]: date }, () => {
            if (this.state.FromDate || this.state.ToDate) {
                this.getReportUser()
            }
        })
    }



    handlePageChange(current_page) {
        if (current_page !== this.state.CURRENT_PAGE) {
            this.setState({
                CURRENT_PAGE: current_page
            }, () => {
                this.getReportUser();
            });
        }
    }
    searchByUser = (e) => {
        this.setState({ Keyword: e.target.value }, this.SearchCodeReq)
    }

    SearchCodeReq() {
        if (this.state.Keyword.length > 2)
            this.getReportUser()
    }
    clearFilter = () => {
        this.setState({
            FromDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
            ToDate: new Date(moment().format('D MMM YYYY')),
            Keyword: '',
            isDescOrder: true,
            sortField: 'first_name'
        }, () => {
            this.getReportUser()
        }
        )
    }
    sortContest(sortfiled, isDescOrder) {
        let Order = sortfiled == this.state.sortField ? !isDescOrder : isDescOrder
        this.setState({
            sortField: sortfiled,
            isDescOrder: Order,
            CURRENT_PAGE: 1,

        }, this.getReportUser)
    }
    render() {
        const { UserReportList, refReportData, CURRENT_PAGE, PERPAGE, TotalUser, Keyword, isDescOrder, detialData, posting, FromDate, ToDate } = this.state
        var todaysDate = moment().format('D MMM YYYY');

        const sameDateProp = {
            disabled_date: false,
            show_time_select: false,
            time_format: false,
            time_intervals: false,
            time_caption: false,
            date_format: 'dd/MM/yyyy',
            handleCallbackFn: this.handleDate,
            class_name: 'form-control mr-3',
            year_dropdown: true,
            month_dropdown: true,
        }
        const FromDateProps = {
            ...sameDateProp,
            min_date: false,
            max_date: new Date(ToDate),
            sel_date: new Date(FromDate),
            date_key: 'FromDate',
            place_holder: 'From Date',
        }
        const ToDateProps = {
            ...sameDateProp,
            min_date: new Date(FromDate),
            max_date: todaysDate,
            sel_date: new Date(ToDate),
            // sel_date: new Date(ToDate),
            date_key: 'ToDate',
            place_holder: 'To Date',
        }
        return (
            <Fragment>
                <div className="animated fadeIn promocode-view mt-4">
                    <Row>
                        <Col md={12}>
                            <h1 className="h1-cls">Referral Report</h1>
                        </Col>
                    </Row>
                    <div className="promocode-list-view">

                        <Row className="filter-userlist mt-5">
                            <Col md={4}>
                                <div className="float-left">
                                    <label className="filter-label">Start Date</label>
                                    <SelectDate 
                                    DateProps={FromDateProps} />
                                </div>
                                <div className="float-left">
                                    <label className="filter-label">End Date</label>
                                    <SelectDate DateProps={ToDateProps} />
                                </div>
                            </Col>
                            <Col md={2}>
                                <div className="search-box">
                                    <label className="filter-label">Search User</label>
                                    <Input
                                        placeholder="Search User"
                                        name='code'
                                        value={Keyword}
                                        onChange={this.searchByUser}
                                    />
                                </div>
                            </Col>
                        
                            <Col md={2}>
                                <div className="filters-area TopBot">
                                    <Button className="btn-secondary" onClick={() => this.clearFilter()}>Clear Filters</Button>
                                </div>
                            </Col>
                            <Col md={2} className="TopBot">
                                {/* <i className="export-list icon-export"
                                    onClick={e => (TotalUser > NC.EXPORT_REPORT_LIMIT) ? this.exportReport_Post() : this.exportReport_Get()}></i> */}
                                <div className="export-list" onClick={e => (TotalUser > NC.EXPORT_REPORT_LIMIT) ? this.exportReport_Post() : this.exportReport_Get()}>	
                                    <span>Export</span>	
                                    <i className="icon-export"	
                                    ></i>	
                                </div>
                            </Col>
                        
                            <Col md={2} className="TopBot">
                                <div className="filters-area">
                                    <h4>Total Record Count:{TotalUser}</h4>
                                </div>
                            </Col>
                        </Row>
                        <Row>
                            <Col md={12} className="table-responsive common-table">
                                <Table>
                                    <thead>
                                        <tr>
                                            <th>Uniqe ID</th>
                                            <th className="pointer" onClick={() => this.sortContest('user_name', isDescOrder)}>UserName</th>
                                            <th className="pointer" onClick={() => this.sortContest('phone_no', isDescOrder)}>Phone</th>
                                            <th className="pointer" onClick={() => this.sortContest('email', isDescOrder)}>Email</th>
                                            <th>Registered</th>
                                            <th>Referral Cash</th>
                                            <th>Referral Bonus</th>
                                            <th>Referral Coin</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    {
                                        UserReportList.length > 0 ?
                                            _.map(UserReportList, (item, idx) => {
                                                return (
                                                    <tbody key={idx}>
                                                        <tr>
                                                            <td>{item.user_unique_id}</td>
                                                            <td><a onClick={() => this.props.history.push("/profile/" + item.user_unique_id)} className="text-click">{item.user_name}</a></td>
                                                            <td>{item.phone_no}</td>
                                                            <td>{item.email}</td>
                                                            <td>{item.registered}</td>
                                                            <td>{item.user_real_cash}</td>
                                                            <td>{item.user_bonus_cash}</td>
                                                            <td>{item.user_coin}</td>
                                                            <td><a className="pointer" onClick={() => this.lineupDetailModal(item, item.user_id)}><i className="icon-info-border"></i></a></td>
                                                        </tr>
                                                    </tbody>
                                                )
                                            })
                                            :
                                            <tbody>
                                                <tr>
                                                    <td colSpan='22'>
                                                        {(UserReportList.length == 0 && !posting) ?
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
                        {TotalUser > PERPAGE && (
                            <div className="custom-pagination lobby-paging">
                                <Pagination
                                    activePage={CURRENT_PAGE}
                                    itemsCountPerPage={PERPAGE}
                                    totalItemsCount={TotalUser}
                                    pageRangeDisplayed={5}
                                    onChange={e => this.handlePageChange(e)}
                                />
                            </div>
                        )
                        }
                    </div>
                    <div>
                        <Modal isOpen={this.state.isLineupModalOpen} toggle={() => this.lineupDetailModal()} className="ref-modal lineup-details modal-lg">
                            <ModalHeader>
                                <a onClick={e => this.exportReffered()}>Export list</a>
                            </ModalHeader>
                            <ModalBody className="p-0">

                                <Row className="mb-5">
                                    <Col md={12}>
                                        <div className="table-responsive common-table">
                                            <Table>
                                                <thead>
                                                    <tr>
                                                        <th className="pl-4">S.No</th>
                                                        <th>Referral Sent To</th>
                                                        <th>Date</th>
                                                        <th>Earned Bonus</th>
                                                        <th>Earned Real Cash</th>
                                                        <th>Earned Coins</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                {
                                                    _.map((refReportData && refReportData.referral_data), (ditem, idx) => {
                                                        return (
                                                            <tbody key={idx}>
                                                                <tr>
                                                                    <td className="pl-4">{idx + 1}</td>
                                                                    <td>
                                                                        {ditem.friend_email}
                                                                    </td>
                                                                    <td>
                                                                        {/* <MomentDateComponent data={{ date: ditem.added_date, format: "D-MMM-YYYY hh:mm A " }} /> */}
                                                                        {HF.getFormatedDateTime(ditem.added_date, "D-MMM-YYYY hh:mm A ")}
                                                                    </td>
                                                                    <td>{ditem.earned_bonus}</td>
                                                                    <td>{ditem.earned_real}</td>
                                                                    <td>{ditem.earned_coin}</td>
                                                                    <td>{ditem.status == 1 ? 'Joined' : ''}</td>

                                                                </tr>
                                                            </tbody>
                                                        )
                                                    })
                                                }
                                            </Table>
                                        </div>
                                    </Col>
                                </Row>
                            </ModalBody>
                        </Modal>
                    </div>

                </div>
            </Fragment>
        )
    }
}