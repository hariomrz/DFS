import React, { Component, Fragment } from "react";
import { Row, Col, Button, Table, Input } from 'reactstrap';
import _, { isEmpty } from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Select from 'react-select';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import Pagination from "react-js-pagination";
import Loader from '../../components/Loader';
import moment from 'moment';
const ReportType = [
    { "label": "TDS Report", "value": "tds_invoice" }
]
const StateType = [
    { "label": "Intra State", "value": "intra_state" },
    { "label": "Inter State", "value": "inter_state" }
]

export default class GSTReports extends Component {
    constructor(props) {
        super(props)
        this.state = {
            TotalUser: 0,
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            startDate: '',
            endDate: '',
            FromDate: new Date(Date.now() - 15 * 24 * 60 * 60 * 1000),
            ToDate: new Date(),
            UserReportList: [],
            Keyword: '',
            sortField: 'first_name',
            isDescOrder: true,
            SelectedPaymentType: { value: 0, label: "Select Type" },
            SelectedState: { value: 0, label: "Select State" },
            SelectedFixture: { value: 0, label: "Select Fixture" },
            SelectedContest: { value: 0, label: "Select Contest" },
            SelectedReportType: { label: "TDS Report", value: "tds_invoice" },
            SeletedStateType: { label: "StateType Select", value: " " },
            PaymentType: [],
            TotalDeposit: '',
            posting: false,
            GstStateList: [],
            GstFixture: [],
            GstContest: [],
            GstReportList: [],
            TableFields: [],
            TotalCount: [],



        }
        this.SearchCodeReq = _.debounce(this.SearchCodeReq.bind(this), 500);
    }
    componentDidMount() {
        // let { FromDate, ToDate } = this.state
        // // if (FromDate && ToDate){
        // //     this.getReportUser()
        // // }
        this.getGstReport()
        this.getStateList()
        this.getFixture()
        //this.getContest()

    }

    // getReportUser = () => {
    //     this.setState({ posting: true })
    //     const { PERPAGE, CURRENT_PAGE, Keyword, FromDate, ToDate, sortField, isDescOrder, SelectedPaymentType } = this.state

    //     let params = {
    //         items_perpage: PERPAGE,
    //         total_items: 0,
    //         current_page: CURRENT_PAGE,
    //         sort_order: isDescOrder ? "ASC" : 'DESC',
    //         sort_field: sortField,
    //         csv: false,
    //         from_date: FromDate ? moment(FromDate).format("YYYY-MM-DD") : '',
    //         to_date: ToDate ? moment(ToDate).format("YYYY-MM-DD") : '',
    //         keyword: Keyword,
    //         payment_method: SelectedPaymentType.value
    //     }
    //     WSManager.Rest(NC.baseURL + NC.GET_REPORT_USER_DEPOSIT_AMOUNT, params).then(ResponseJson => {
    //         if (ResponseJson.response_code == NC.successCode) {
    //             this.setState({
    //                 posting: false,
    //                 UserReportList: ResponseJson.data.result,
    //                 TotalUser: ResponseJson.data.total,
    //                 TotalDeposit: ResponseJson.data.total_deposit
    //             })
    //         } else {
    //             notify.show(NC.SYSTEM_ERROR, "error", 3000)
    //         }
    //     }).catch(error => {
    //         notify.show(NC.SYSTEM_ERROR, "error", 3000)
    //     })
    // }
    getGstReport = () => {

        this.setState({ posting: true })
        const { PERPAGE, CURRENT_PAGE, Keyword, FromDate, ToDate, sortField, isDescOrder, SelectedPaymentType, SelectedState, SelectedContest, SelectedFixture, SelectedReportType, TableFields, SeletedStateType } = this.state
     

        for (var key in TableFields) {
            if (TableFields[key] == '') {
                notify.show(key.replace(/_/g, ' ').toUpperCase() + ' field can not be empty.', 'error', 3000)
                return false;
            }
            else {
                this.setState({
                    updatePosting: false,
                    TableFields: TableFields,
                })
            }
        }

        let params = {
            items_perpage: PERPAGE,
            total_items: 0,
            current_page: CURRENT_PAGE,
            sort_order: isDescOrder ? "ASC" : 'DESC',
            sort_field: sortField,
            csv: false,
            from_date: FromDate ? moment(FromDate).format("YYYY-MM-DD") : '',
            to_date: ToDate ? moment(ToDate).format("YYYY-MM-DD") : '',
            keyword: Keyword,
            payment_method: SelectedPaymentType.value,
            report_type: SelectedReportType.value,
            state: SelectedState.value,
            contest_id: SelectedContest.value,
            season_game_uid: SelectedFixture.value,
            // inter_state:'',
            // intra_state:'',
            state_type: SeletedStateType.value,



        }
        WSManager.Rest(NC.baseURL + NC.GET_GST_REPORT, params).then(ResponseJson => {

       

            if (ResponseJson.response_code == NC.successCode) {
                if ((ResponseJson.data != "No Record")) {

                    this.setState({
                        posting: false,
                        GstReportList: ResponseJson.data.result,
                        TableFields: ResponseJson.data.table_field,
                        TotalCount: ResponseJson.data.total_count

                    }

                    )
                }

            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    // getSearchUser = () => {
    //     this.setState({ posting: true })
    //     let params = {
    //         keyword: "ankit"
    //     }
    //     WSManager.Rest(NC.baseURL + NC.GET_SEARCH_USER, params).then(ResponseJson => {
    //         if (ResponseJson.response_code == NC.successCode) {
    //             const Temp = []

    //             _.map(ResponseJson.data.state_list, (item, idx) => {
    //                 Temp.push({
    //                     value: item.master_state_id, label: item.state_name
    //                 })
    //             })
    //             this.setState({
    //                 GstStateList: Temp,


    //             })
    //         }
    //         else {
    //             notify.show(NC.SYSTEM_ERROR, "error", 3000)
    //         }
    //     }).catch(error => {
    //         notify.show(NC.SYSTEM_ERROR, "error", 3000)
    //     })
    // }
    getStateList = () => {
        this.setState({ posting: true })
        let params = {
            'master_country_id': 101
        }
        WSManager.Rest(NC.baseURL + NC.GET_STATE_LIST, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                const Temp = []

                _.map(ResponseJson.data.state_list, (item, idx) => {
                    Temp.push({
                        value: item.master_state_id, label: item.state_name
                    })
                })
                this.setState({
                    GstStateList: Temp,

                })
            }
            else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    getFixture = () => {
        const { PERPAGE, CURRENT_PAGE, FromDate, ToDate } = this.state

        let params = {
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
            from_date: FromDate ? moment(FromDate).format("YYYY-MM-DD") : '',
            to_date: ToDate ? moment(ToDate).format("YYYY-MM-DD") : '',

        }
        WSManager.Rest(NC.baseURL + NC.GET_COMPLETED_FIXTURE, params).then(ResponseJson => {
            
            if (ResponseJson.response_code == NC.successCode) {
                let TempGstFixture = [{ 'value': '', 'label': 'Select Fixture' }];

                _.map(ResponseJson.data.result, (lObj, lKey) => {
                    let d = moment(new Date(WSManager.getUtcToLocal(lObj.season_scheduled_date)));
                    TempGstFixture.push({ value: lObj.season_game_uid, label: lObj.home + ' VS ' + lObj.away + ' (' + d.format("YYYY-DD-MM h:mm A") + ')', season_scheduled_date: lObj.season_scheduled_date });

                })
                this.setState({
                    GstFixture: TempGstFixture

                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    getContest = () => {
        this.setState({ posting: true })
        let { SelectedFixture } = this.state
       
        let params = {
            // 'season_game_uid': SelectedFixture.value,
            season_game_uid: SelectedFixture.value,

        }

        WSManager.Rest(NC.baseURL + NC.GET_COMPLETED_CONTEST, params).then(ResponseJson => {
          
            if (ResponseJson.response_code == NC.successCode) {

                const TempGstContest = []

                _.map(ResponseJson.data, (item, idx) => {
                    TempGstContest.push({
                        value: item.contest_id, label: item.contest_name
                    })
                })
                this.setState({
                    GstContest: TempGstContest,

                })
            }
            else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }
    exportGstReport = () => {
        const { Keyword, FromDate, ToDate, sortField, isDescOrder } = this.state
        let params = {
            sort_order: isDescOrder ? "ASC" : 'DESC',
            sort_field: sortField,
            from_date: FromDate,
            to_date: ToDate,
            keyword: Keyword,
            report_type: "user_deposit"
        }

        WSManager.Rest(NC.baseURL + NC.GET_EXPORT_GST_REPORT, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                notify.show(ResponseJson.message, "success", 5000);
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }
    downloadGstReport = (item) => {
        var query_string = '';//pairs.join('&');
        let { FromDate, ToDate, Keyword, TrStatusChange, TrTypeChange, TrDescChange } = this.state
        let tempFromDate = FromDate ? moment(FromDate).format('YYYY-MM-DD') : '';
        let tempToDate = ToDate ? moment(ToDate).format('YYYY-MM-DD') : '';

        query_string = 'user_id=' + item.user_id + '&lineup_master_contest_id=' + item.lineup_master_contest_id;

        // query_string = 'keyword=' + Keyword + '&status=' + TrStatusChange + '&type=' + TrTypeChange + '&source=' + TrDescChange + '&csv=true' + '&from_date=' + tempFromDate + '&to_date=' + tempToDate;

        let sessionKey = WSManager.getToken();
        query_string += "&Sessionkey" + "=" + sessionKey;

        window.open(NC.baseURL + 'adminapi/index.php/gst/gst_invoice_download?' + query_string, '_blank');
    }
    exportReport = () => {
        const { Keyword, FromDate, ToDate, sortField, isDescOrder } = this.state
        let params = {
            sort_order: isDescOrder ? "ASC" : 'DESC',
            sort_field: sortField,
            from_date: FromDate,
            to_date: ToDate,
            keyword: Keyword,
            report_type: "user_deposit"
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

    handleTypeChange = (value, name) => {
        if (value != null)
            this.setState({ [name]: value }, this.getGstReport)
    }
    handleTypeState = (value, name) => {
       
        //this.setState({GstReportList : [] })

        if (value != null)
            this.setState({
                [name]: value,
                SelectedState: '',

            }, this.getGstReport)
    }
    handleStateChange = (value, name) => {
        this.setState({ GstReportList: [] })

        if (value != null)
            this.setState({
                [name]: value,
            }, this.getGstReport)
    }
    handleFixtureChange = (value, name) => {
        this.setState({ GstContest: [] })
       

        if (value != null)
            this.setState({
                [name]: value,
                SelectedContest: '',
            }, () => {
                this.getContest();
                this.getGstReport();
            })
    }

    handleContestChange = (value, name) => {

        if (value != null)
            this.setState({ [name]: value }, this.getGstReport)
    }


    handleDateFilter = (date, dateType) => {
        this.setState({ [dateType]: date }, () => {
            if (this.state.FromDate && this.state.ToDate) {
                this.getGstReport()
                this.getFixture()
            }
        })
    }

    handlePageChange(current_page) {
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            this.getGstReport();
        });
    }

    searchByUser = (e) => {
        this.setState({ Keyword: e.target.value }, this.SearchCodeReq)
    }

    SearchCodeReq() {
        if (this.state.Keyword.length > 2 || this.state.Keyword.length == 0)
            this.getGstReport()
    }

    clearFilter = () => {
        this.setState({
            FromDate: '',
            ToDate: '',
            Keyword: '',
            isDescOrder: true,
            sortField: 'first_name'
        }, () => {
            this.getGstReport()
        }
        )
    }

    sortContest(sortfiled, isDescOrder) {
        let Order = sortfiled == this.state.sortField ? !isDescOrder : isDescOrder
        this.setState({
            sortField: sortfiled,
            isDescOrder: Order,
            CURRENT_PAGE: 1,

        }, this.getGstReport)
    }


    render() {
        const { UserReportList, CURRENT_PAGE, PERPAGE, TotalUser, Keyword, isDescOrder, SelectedPaymentType, PaymentType, TotalDeposit, posting, GstStateList, GstFixture, GstContest, GstReportList, TableFields, TotalCount, SelectedFixture, SelectedContest, SelectedState, SelectedReportType, SeletedStateType } = this.state
        return (
            <Fragment>
                <div className="animated fadeIn mt-4">
                    <Row>
                        <Col md={12}>
                            <h1 className="h1-cls"> TDS Report</h1>
                        </Col>
                    </Row>
                    <div className="user-deposit-amount">
                        <Row className="xfilter-userlist mt-5">
                            <Col md={2}>
                                <div>
                                    <label className="filter-label">Report Type</label>
                                    <Select
                                        isSearchable={true}
                                        class="form-control"
                                        options={ReportType}
                                        menuIsOpen={true}
                                        value={SelectedReportType}
                                        onChange={e => this.handleTypeChange(e, 'SelectedReportType')}
                                    />
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
                                <div>
                                    <label className="filter-label">From Date</label>
                                    <DatePicker
                                        maxDate={new Date(this.state.ToDate)}
                                        className="form-control"
                                        showYearDropdown='true'
                                        selected={this.state.FromDate}
                                        onChange={e => this.handleDateFilter(e, "FromDate")}
                                        placeholderText="From"
                                    />
                                </div>
                            </Col>
                            <Col md={2}>
                                <div>
                                    <label className="filter-label">To Date</label>
                                    <DatePicker
                                        minDate={new Date(this.state.FromDate)}
                                        maxDate={new Date()}
                                        className="form-control"
                                        showYearDropdown='true'
                                        selected={this.state.ToDate}
                                        onChange={e => this.handleDateFilter(e, "ToDate")}
                                        placeholderText="To"
                                    />
                                </div>
                            </Col>
                            <Col md={3} className="mb-5 mt-4">
                                <div className="filters-area">
                                    <Button className="btn-secondary" onClick={() => this.clearFilter()}>Clear Filters</Button>
                                </div>
                            </Col>
                            <Col md={1} className="mt-4">
                                <div className="">
                                    <i className="export-list icon-export" onClick={e => this.exportGstReport()}></i>

                                </div>
                            </Col>
                        </Row>
                        <Row className="xfilter-userlist mt-5">
                            {/* <Col md={2}>
                                <div>
                                    <label className="filter-label">User</label>
                                    <Select
                                        isSearchable={true}
                                        class="form-control"
                                        options={ReportType}
                                        menuIsOpen={true}
                                        value={SelectedPaymentType}
                                        onChange={e => this.handleTypeChange(e, 'SelectedPaymentType')}
                                    />
                                </div>
                            </Col> */}
                            {/* <Col md={2}>
                                <div className="search-box">
                                    <label className="filter-label">State Type</label>
                                    <Select
                                        isSearchable={true}
                                        class="form-control"
                                        options={StateType}
                                        menuIsOpen={true}
                                        value={SeletedStateType}
                                        onChange={e => this.handleTypeState(e, 'SeletedStateType')}
                                    />
                                </div>
                            </Col> */}
                            {/* <Col md={2}>
                                
                               {
                                   SeletedStateType.value == 'intra_state' &&
                                   
                                   <div className="overlap-state">

                                   </div>


                               } 
                            
                                <div className="search-box">
                                    <label className="filter-label">State Filter</label>
                                    <Select
                                        isSearchable={true}
                                        class="form-control"
                                        options={GstStateList}
                                        menuIsOpen={true}
                                        value={SelectedState}
                                        onChange={e => this.handleStateChange(e, 'SelectedState')}
                                    />
                                </div>
                               

                            

                            </Col> */}
                            {/* <Col md={2}>
                                <div>
                                    <label className="filter-label">Fixture</label>
                                    <Select
                                        isSearchable={true}
                                        class="form-control"
                                        options={GstFixture}
                                        menuIsOpen={true}
                                        value={SelectedFixture}
                                        onChange={e => this.handleFixtureChange(e, 'SelectedFixture')}
                                    />

                                </div>
                            </Col>
                            <Col md={2}>
                                <div>
                                    <label className="filter-label">Contest</label>
                                    <Select
                                        isSearchable={true}
                                        class="form-control"
                                        options={GstContest}
                                        menuIsOpen={true}
                                        value={SelectedContest}
                                        onChange={e => this.handleContestChange(e, 'SelectedContest')}
                                    />
                                </div>
                            </Col> */}




                        </Row>
                        {/* <Row className="filters-box">
                            <Col md={11}>
                                <div className="filters-area">
                                    <Button className="btn-secondary" onClick={() => this.clearFilter()}>Clear Filters</Button>
                                </div>
                            </Col>

                            <Col md={1} className="">
                                <i className="export-list icon-export" onClick={e => this.exportReport()}></i>
                            </Col>
                        </Row> */}
                        {/* <Row className="mb-5 filters-box">
                             <Col md={11} className="mt-4">
                                <div className="filters-area">
                                    <Button className="btn-secondary" onClick={() => this.clearFilter()}>Clear Filters</Button>
                                </div>
                            </Col>
                            <Col md={1}>
                                <div className="">
                                    <i className="export-list icon-export" onClick={e => this.exportGstReport()}></i>

                                </div>
                            </Col>
                        </Row> */}
                        <Row>
                            <Col md={12} className="table-responsive common-table gst-table">
                                <Table>
                                    <thead>
                                        <tr>
                                            {
                                                _.map(TableFields, (item, idx) => {
                                                    return (
                                                        <th key={idx}>{item.replace(/_/g, ' ').toUpperCase()}</th>


                                                    )
                                                })
                                            }
                                        </tr>
                                    </thead>


                                    <tbody>
                                        {
                                            !_.isEmpty(GstReportList) && (GstReportList.length > 0) ?
                                                <React.Fragment>
                                                    {
                                                        _.map(GstReportList, (item, ind) => {
                                                            return (
                                                                <tr key={ind}>
                                                                    {
                                                                        _.map(TableFields, (fieldname, idx) => {
                                                                            return (

                                                                                <td key={idx}>
                                                                                    {item[fieldname]}
                                                                                    <div className="edit-stats-btn">
                                                                                        {
                                                                                            (fieldname == 'download_report') &&
                                                                                            <a onClick={e => this.downloadGstReport(item)} className="pointer">Invoice</a>
                                                                                        }
                                                                                    </div>
                                                                                </td>
                                                                            )
                                                                        })
                                                                    }


                                                                </tr>


                                                            )
                                                        }
                                                        )
                                                    }

                                                    <tr>
                                                        <td colSpan="4">
                                                        </td>
                                                        {
                                                            _.map(TotalCount, (item, idx) => {
                                                                return (

                                                                    <td key={idx}>
                                                                        {item}
                                                                    </td>
                                                                )
                                                            })
                                                        }
                                                        <td colSpan="3">
                                                        </td>
                                                    </tr>
                                                </React.Fragment>

                                                :
                                                <tr>
                                                    <td colSpan='22'>
                                                        {/* {!_.isEmpty(GstReportList)  && (GstReportList.length == 0 && !posting) ?
                                                    <div className="no-records">No Record Found.</div>
                                                    :
                                                    <Loader />
                                                } */}

                                                        <div className="no-records">No Record Found.</div>
                                                    </td>
                                                </tr>
                                        }

                                    </tbody>

                                </Table>
                            </Col>
                        </Row>
                        {TotalUser > 0 && (
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


                </div>
            </Fragment>
        )
    }
}
