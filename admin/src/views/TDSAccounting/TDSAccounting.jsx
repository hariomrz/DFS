import React, { Component, Fragment } from "react";
import { Row, Col, Button, Table, Input, Tooltip } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Select from 'react-select';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import Pagination from "react-js-pagination";
import moment from 'moment';
import Images from "../../components/images";
import HF, { _filter } from "../../helper/HelperFunction";


export default class TDSAccounting extends Component {
    constructor(props) {
        super(props)
        this.state = {
            TotalUser: 0,
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            startDate: '',
            endDate: '',
            FromDate: '',
            ToDate: '',
            UserReportList: [],
            Keyword: '',
            sortField: 'first_name',
            isDescOrder: true,
            SelectedFilter: { value: 1, label: "All" },
            SelectedFilterList: '',
            posting: false,
            GstStateList: [],
            GstFixture: [],
            GstContest: [],
            GstReportList: [],
            TableFields: [],
            FinancialType: [],
            SelectedFinancialYear: '',
            FilterList: [],
            SelectedeTdsType: { value: 1, label: "All" },
            TdsTypeList: [],
            maxStartDate: new Date(),
            minStartDate: new Date(),
            isShowToolTip: false,
            CurrentFY: {}
        }
       // this.SearchCodeReq = _.debounce(this.SearchCodeReq.bind(this), 500);
    }
    componentDidMount() {
        this.getTdsFilter()
    }

    ToolTipToggle = () => {
        this.setState({ isShowToolTip: !this.state.isShowToolTip });
    }

    getTdsFilter = () => {

        WSManager.Rest(NC.baseURL + NC.GET_FILTER_LIST_TDS, {}).then(ResponseJson => {

            if (ResponseJson.response_code == NC.successCode) {
                const Temp = []

                _.map(ResponseJson.data.fy, (it, idx) => {
                    Temp.push({
                        value: it, label: idx
                    })
                })
                const Temptype = []

                _.map(ResponseJson.data.type, (it, idx) => {
                    Temptype.push({
                        value: it.id, label: it.name
                    })
                })
                const Temptds = []

                _.map(ResponseJson.data.tds_type, (it, idx) => {
                    Temptds.push({
                        value: it.id, label: it.name
                    })
                })
                const _date = new Date();
                let _year = _date.getFullYear().toString().slice(-2);
                let currentFy = _filter(Temp, o => o.label.includes(_year))[0] || {}
                this.setState({
                    FinancialType: Temp,
                    SelectedFinancialYear:currentFy,
                    minStartDate: new Date(currentFy.value.start),
                    FromDate: new Date(currentFy.value.start),
                    ToDate: new Date(currentFy.value.end),
                    maxStartDate: new Date(currentFy.value.end),
                    FilterList: Temptype,
                    TdsTypeList: Temptds,
                    CurrentFY: currentFy
                },()=>{
                    this.getGstReport()
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    getGstReport = () => {

        this.setState({ posting: true })
        const { PERPAGE, CURRENT_PAGE, Keyword, FromDate, ToDate, SelectedeTdsType, SelectedFilter, SelectedFinancialYear } = this.state




        let params = {
            limit: PERPAGE,
            total_items: 0,
            page: CURRENT_PAGE,
            csv: false,
            from_date: FromDate ? moment(FromDate).format("YYYY-MM-DD") : '',
            to_date: ToDate ? moment(ToDate).format("YYYY-MM-DD") : '',
            keyword: Keyword,
            fy: SelectedFinancialYear.label,
            type: SelectedFilter.value,
            tds_type: SelectedeTdsType.value
        }
        WSManager.Rest(NC.baseURL + NC.GET_REPORT_TDS, params).then(ResponseJson => {

            if (ResponseJson.response_code == NC.successCode) {
                if ((ResponseJson.data != "No Record")) {

                    this.setState({
                        posting: false,
                        GstReportList: ResponseJson.data.result,
                        TableFields: ResponseJson.data.table_field,
                        TotalUser: ResponseJson.data.total
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




    exportGstReport = () => {
        let { Keyword, FromDate, ToDate, SelectedeTdsType, SelectedFilter, SelectedFinancialYear } = this.state

        let tempFromDate = ''
        let tempToDate = ''
        if (FromDate != '' && ToDate != '') {
            tempFromDate = moment(FromDate).format("YYYY-MM-DD");
            tempToDate = moment(ToDate).format("YYYY-MM-DD");
        }


        var query_string = 'csv=true&keyword=' + Keyword + '&from_date=' + tempFromDate + '&to_date=' + tempToDate + '&fy=' + (SelectedFinancialYear=='' ? '' : SelectedFinancialYear.label) + '&type=' + SelectedFilter.value + '&tds_type=' + SelectedeTdsType.value;

        var export_url = 'adminapi/tds/get_report?';
 
        HF.exportFunction(query_string, export_url)
    }



    handleTypeChange = (value, name) => {
        if (value != null)
            this.setState({ [name]: value }, () => {
                const { SelectedFinancialYear } = this.state
                if (!_.isEmpty(SelectedFinancialYear)) {
                    this.setState({
                        minStartDate: new Date(SelectedFinancialYear.value.start),
                        FromDate: new Date(SelectedFinancialYear.value.start),
                        ToDate: new Date(SelectedFinancialYear.value.end),
                        maxStartDate: new Date(SelectedFinancialYear.value.end),
                    })
                }
            })
    }



    handleDateFilter = (date, dateType) => {
        this.setState({ [dateType]: date })
    }

    handlePageChange(current_page) {
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            this.getGstReport();
        });
    }

    searchByUser = (e) => {
        this.setState({ Keyword: e.target.value }, 
            //this.SearchCodeReq
            )
    }

    // SearchCodeReq() {
    //     // if (this.state.Keyword.length > 2 || this.state.Keyword.length == 0)
    //     //     this.getGstReport()
    // }

    clearFilter = () => {
        const { CurrentFY } = this.state
        this.setState({
            FromDate: '',
            ToDate: '',
            Keyword: '',
            SelectedFinancialYear: CurrentFY,
            minStartDate: new Date(CurrentFY.value.start),
            FromDate: new Date(CurrentFY.value.start),
            ToDate: new Date(CurrentFY.value.end),
            maxStartDate: new Date(CurrentFY.value.end),
            SelectedFilter: { value: 1, label: "All" },
            SelectedeTdsType: { value: 1, label: "All" },
        }, () => {
            this.getGstReport()
        }
        )
    }



    render() {
        const { CURRENT_PAGE, PERPAGE, TotalUser, Keyword, GstReportList, TableFields, FinancialType, SelectedFinancialYear, FilterList, SelectedFilter, TdsTypeList, SelectedeTdsType, isShowToolTip } = this.state
        return (
            <Fragment>
                <div className="animated fadeIn mt-4 contest-template">
                    <Row className="tds-head">
                    <Col md={6}>
                        <h2 className="h2-cls">TDS Report</h2>
                    </Col>
                    <Col md={6} className="jc-flex-end">
                            <Button className='btn-secondary-outline' onClick={() => { this.props.history.push('/accounting/tds-document') }}>TDS Documents</Button>
                            <i
                                className={`export-list icon-export tds-export ${(!_.isEmpty(GstReportList) && (GstReportList.length > 0)) ? '' : 'cls-dis'}`}
                                onClick={e => this.exportGstReport()}></i>
                    </Col>
                    </Row>
                    <div className="user-deposit-amount">
                        <Row className="mt-2">
                            <Col md={2}>
                                <div>
                                    <label className="filter-label">Financial Year</label>
                                    <Select
                                        searchable={false}
                                        clearable={false}
                                        class="form-control"
                                        options={FinancialType}
                                        value={SelectedFinancialYear}
                                        onChange={e => this.handleTypeChange(e, 'SelectedFinancialYear')}
                                    />
                                </div>
                            </Col>
                            <Col md={2}>
                                <div>
                                    <label className="filter-label">Filter</label>
                                    <Select
                                        class="form-control"
                                        options={FilterList}
                                        searchable={false}
                                        clearable={false}
                                        value={SelectedFilter}
                                        placeholder={"Select"}
                                        onChange={e => this.handleTypeChange(e, 'SelectedFilter')}
                                    />
                                </div>
                            </Col>
                            <Col md={2}>
                                <div>
                                    <label className="filter-label">TDS type <i id={'itemTip'} className="icon-info"></i>
                                        <Tooltip
                                            placement='top'
                                            isOpen={isShowToolTip}
                                            target={'itemTip'}
                                            toggle={() => this.ToolTipToggle()}
                                        >The FY Settlements will be done on net winnings for the given year and the amount after TDS deduction is credited to the user wallet. This amount is not liable for any further TDS deduction thereafter.</Tooltip>
                                    </label>
                                    <Select
                                        class="form-control"
                                        options={TdsTypeList}
                                        searchable={false}
                                        clearable={false}
                                        value={SelectedeTdsType}
                                        placeholder={"Select"}
                                        onChange={e => this.handleTypeChange(e, 'SelectedeTdsType')}
                                    />
                                </div>
                            </Col>

                            <Col md={2}>
                                <div>
                                    <label className="filter-label">From Date</label>
                                    <DatePicker
                                        minDate={this.state.minStartDate}
                                        maxDate={this.state.maxStartDate}
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
                                        minDate={this.state.FromDate}
                                        maxDate={this.state.maxStartDate}
                                        className="form-control"
                                        showYearDropdown='true'
                                        selected={this.state.ToDate}
                                        onChange={e => this.handleDateFilter(e, "ToDate")}
                                        placeholderText="To"
                                    />
                                </div>
                            </Col>
                        </Row>
                        <Row className="align-items-flex-end mb-30">
                            <Col md={3}>
                                <div className="search-box">
                                    <label className="filter-label">Search User</label>
                                    <Input
                                        placeholder={HF.getIntVersion() != 1 ? "PAN, Name, Email, Mobile" : "ID, Name, Email, Mobile"}
                                        name='code'
                                        value={Keyword}
                                        onChange={this.searchByUser}
                                    />
                                </div>
                            </Col>
                            <Col md={9}>
                                <div className="filters-area tds-filter-btns">
                                    <Button className="btn-secondary" onClick={() => this.getGstReport()}>Apply</Button>
                                    <Button className="btn-secondary btn-secondary-outline" onClick={() => this.clearFilter()}>Clear</Button>
                                </div>
                            </Col>

                        </Row>

                        <Row>
                            <Col md={12} className="table-responsive common-table gst-table">
                                {
                                    !_.isEmpty(GstReportList) && (GstReportList.length > 0) ?
                                    <Table>
                                        <thead>
                                            <tr>
                                                {
                                                    _.map(TableFields, (item, idx) => {
                                                        return (
                                                            <th key={idx}>{item}</th>
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
                                                                                        {
                                                                                            idx == "date_added" ?
                                                                                                <>{HF.getFormatedDateTime(item[idx], 'DD-MMM-YYYY | hh:mm A')}</>
                                                                                                :
                                                                                                <>
                                                                                                    {
                                                                                                        idx == "type" ?
                                                                                                            <>{_.replace(item[idx], '{{fy}}', item.fy)}</>
                                                                                                            :
                                                                                                            <>{item[idx]}</>
                                                                                                    }
                                                                                                </>
                                                                                        }
                                                                                    </td>
                                                                                )
                                                                            })
                                                                        }
                                                                    </tr>
                                                                )
                                                            }
                                                            )
                                                        }
                                                    </React.Fragment>

                                                    :
                                                    <tr>
                                                        <td colSpan='22'>
                                                            {/* {!_.isEmpty(GstReportList)  && (GstReportList.length == 0 && !posting) ?
                                                        <div className="no-records">No Record Found.</div>
                                                        :
                                                        <Loader />
                                                    } */}

                                                        </td>
                                                    </tr>
                                            }

                                        </tbody>

                                    </Table>
                                    :
                                    <div className="tds-no-data">
                                        <div><img src={Images.NO_DATA_SHADE} alt="" /></div>
                                        <div className="no-records">No Records Found</div>
                                    </div>
                                }


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
