import React, { Component, Fragment } from "react";
import { Row, Col, Button, Table, Input } from 'reactstrap';
import { _isEmpty, _Map, _debounce, _isUndefined } from '../../helper/HelperFunction';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Select from 'react-select';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import Pagination from "react-js-pagination";
import moment from 'moment';
import Loader from '../../components/Loader';
import HF from "../../helper/HelperFunction";
import { MODULE_NOT_ENABLE } from "../../helper/Message";
export default class GSTReports extends Component {
    constructor(props) {
        super(props)
        this.state = {
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            startDate: '',
            endDate: '',
            FromDate: HF.getFirstDateOfMonth(),
            ToDate: new Date(moment().format('D MMM YYYY')),
            UserReportList: [],
            Keyword: '',
            sortField: 'date',
            isDescOrder: false,
            SelectedState: { value: '', label: "All" },
            SelectedFixture: { value: 0, label: "Select Fixture" },
            SelectedContest: { value: 0, label: "Select Contest" },
            SelectedModuleType: {},
            SelectedReportType: {},
            // SelectedReportType: { label: "Gst Report", value: "1" },
            SeletedStateType: { label: "All", value: "" },
            PaymentType: [],
            TotalDeposit: '',
            posting: false,
            GstStateList: [],
            GetAllGstStateList: [],
            ReportType: [],
            StateType: [],
            GstFixture: [],
            GstContest: [],
            GstReportList: [],
            TableFields: [],
            TotalCount: [],
            fillterStateId: '',
            gstRegime: [{ value: "0", label: "New GST" }, { value: "1", label: "Old GST" }],
            selectedGSTRegime: { label: "New GST", value: "0" },
            gstApply: 1

        }
        this.SearchCodeReq = _debounce(this.SearchCodeReq.bind(this), 500);
    }
    componentDidMount() {
        if (HF.allowGst() != '1' && HF.allowTds() != '1') {
            notify.show(MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        //this.getStateList()
        // this.getGSTCompletedMatch()
        this.getGstFillterData()


    }
    getGstFillterData = () => {
        this.setState({ posting: true })
        let params = {

        }
        WSManager.Rest(NC.baseURL + NC.GET_GST_FILLTER_DATA, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                //const Temp = [{ value: '', label: "All" }]
                const StateTemp = []
                const ReportTypeTemp = []
                const ModuleTypeTemp = []
                const StateTypeTemp = []
                _Map(ResponseJson.data.state_list, (item, idx) => {
                    StateTemp.push({
                        value: item.master_state_id, label: item.state_name
                    })
                })
                _Map(ResponseJson.data.state_type, (item, idx) => {
                    StateTypeTemp.push({
                        value: item.id, label: item.name
                    })
                })
                _Map(ResponseJson.data.report_type, (item, idx) => {
                    ReportTypeTemp.push({
                        value: item.id, label: item.name
                    })
                })
                _Map(ResponseJson.data.module_type, (item, idx) => {
                    if (HF.allowDFS() == 1 && item.id == 1) {
                        ModuleTypeTemp.push({
                            value: item.id, label: item.name
                        })
                    }
                    if (HF.allowLiveFantsy() == 1 && item.id == 2) {
                        ModuleTypeTemp.push({
                            value: item.id, label: item.name
                        })
                    }
                })
                this.setState({
                    GstStateList: StateTemp,
                    GetAllGstStateList: StateTemp,
                    fillterStateId: ResponseJson.data.state_id,
                    ReportType: ReportTypeTemp,
                    ModuleType: ModuleTypeTemp,
                    StateType: StateTypeTemp,
                    SelectedReportType: ReportTypeTemp[0]
                }, () => {
                    const { ModuleType } = this.state
                    this.setState({
                        SelectedModuleType: !_isEmpty(ModuleType) ? ModuleType[0] : {}
                    }, this.getGstReport)
                })
            }
            else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    getGstReport = () => {
        this.setState({ posting: true })

        let { PERPAGE, CURRENT_PAGE, Keyword, FromDate, ToDate, sortField, isDescOrder, SelectedState, SelectedContest, SelectedFixture, SelectedReportType, TableFields, SeletedStateType, SelectedModuleType, gstApply } = this.state

        // for (var key in TableFields) {
        //     if (TableFields[key] == '') {
        //         notify.show(key.replace(/_/g, ' ').toUpperCase() + ' field can not be empty.', 'error', 3000)
        //         return false;
        //     }
        //     else {
        //         this.setState({
        //             updatePosting: false,
        //             TableFields: TableFields,
        //         })
        //     }
        // }

        let params = {
            items_perpage: PERPAGE,
            total_items: 0,
            current_page: CURRENT_PAGE,
            sort_order: isDescOrder ? "ASC" : 'DESC',
            sort_field: sortField,
            csv: false,
            // from_date: FromDate ? moment(FromDate).format("YYYY-MM-DD") : '',
            // to_date: ToDate ? moment(ToDate).format("YYYY-MM-DD") : '',
            from_date: FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '',
            to_date: ToDate ? moment(ToDate).format('YYYY-MM-DD') : '',
            keyword: Keyword,
            report_type: '1',
            module_type: SelectedModuleType.value,
            state_id: SelectedReportType.value == 2 ? '' : SelectedState.value,
            contest_id: SelectedContest.value != 0 ? SelectedContest.value : '',
            match_id: SelectedFixture.value != 0 ? SelectedFixture.value : '',
            state_type: SelectedReportType.value == 2 ? '' : SeletedStateType.value,
            invoice_type: gstApply
        }

        WSManager.Rest(NC.baseURL + NC.GET_GST_REPORT, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                // if ((ResponseJson.data != "No Record")) {
                this.setState({
                    posting: false,
                    GstReportList: !_isUndefined(ResponseJson.data.result) ? ResponseJson.data.result : [],
                    TableFields: ResponseJson.data.table_field,
                    TotalCount: ResponseJson.data.total_count,
                    TotalUser: ResponseJson.data.total,
                })
                if (this.state.SelectedReportType.value == 2) {
                    this.getTdsCompletedContest()
                }
                else {
                    this.setState({ GstContest: [] })
                    this.getGSTCompletedMatch()

                }
                // }

            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    // getStateList = () => {
    //     this.setState({ posting: true })
    //     let params = {
    //         'master_country_id': 101,
    //         state_type: this.state.SeletedStateType.value,
    //     }
    //     WSManager.Rest(NC.baseURL + NC.GET_STATE_LIST, params).then(ResponseJson => {
    //         if (ResponseJson.response_code == NC.successCode) {
    //             const Temp = [{ value: '', label: "All" }]
    //             _Map(ResponseJson.data.state_list, (item, idx) => {
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

    getGSTCompletedMatch = () => {
        const { PERPAGE, CURRENT_PAGE, FromDate, ToDate, SelectedModuleType } = this.state

        let params = {
            // from_date: FromDate ? moment(FromDate).format("YYYY-MM-DD") : '',
            // to_date: ToDate ? moment(ToDate).format("YYYY-MM-DD") : '',
            from_date: FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '',
            to_date: ToDate ? moment(ToDate).format('YYYY-MM-DD') : '',
            module_type: SelectedModuleType.value,
        }
        WSManager.Rest(NC.baseURL + NC.GET_COMPLETED_FIXTURE, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                let TempGstFixture = [];
                _Map(ResponseJson.data, (lObj, lKey) => {
                    let d = moment(new Date(WSManager.getUtcToLocal(lObj.scheduled_date)));
                    TempGstFixture.push({ value: lObj.match_id, label: lObj.match_name + ' (' + d.format("DD-MM-YYYY h:mm A") + ')', season_scheduled_date: lObj.scheduled_date });
                })
                this.setState({
                    //SelectedFixture:SelectedFixtureApi,
                    GstFixture: TempGstFixture

                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    getGstCompltedContest = () => {
        this.setState({ posting: true })
        let { SelectedFixture, SelectedModuleType } = this.state
        let params = {
            match_id: SelectedFixture.value,
            module_type: SelectedModuleType.value,
        }

        WSManager.Rest(NC.baseURL + NC.GET_COMPLETED_CONTEST, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                const TempGstContest = []
                _Map(ResponseJson.data, (item, idx) => {
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
    getTdsCompletedContest = () => {
        this.setState({ posting: true })
        const { FromDate, ToDate, SelectedModuleType } = this.state

        let params = {
            // from_date: FromDate ? moment(FromDate).format("YYYY-MM-DD") : '',
            // to_date: ToDate ? moment(ToDate).format("YYYY-MM-DD") : '',
            from_date: FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '',
            to_date: ToDate ? moment(ToDate).format('YYYY-MM-DD') : '',
            module_type: SelectedModuleType.value,
        }

        WSManager.Rest(NC.baseURL + NC.GET_TDS_COMPLETED_CONTEST, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                const TempGstContest = []
                _Map(ResponseJson.data, (lObj, idx) => {

                    let d = moment(new Date(WSManager.getUtcToLocal(lObj.scheduled_date)));
                    TempGstContest.push({ value: lObj.contest_id, label: lObj.match_name + '-' + lObj.contest_name + ' (' + d.format("DD-MM-YYYY h:mm A") + ')', season_scheduled_date: lObj.scheduled_date });

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
        let { Keyword, FromDate, ToDate, sortField, isDescOrder, SelectedState, SelectedContest, SelectedFixture, SelectedReportType, SeletedStateType, SelectedModuleType, gstApply } = this.state

        let tempFromDate = ''
        let tempToDate = ''
        if (FromDate != '' && ToDate != '') {
            // tempFromDate = moment(FromDate).format("YYYY-MM-DD");
            // tempToDate = moment(ToDate).format("YYYY-MM-DD");
            tempFromDate = WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD')
            tempToDate = moment(ToDate).format("YYYY-MM-DD");
        }

        let ord = isDescOrder ? "ASC" : 'DESC'
        var query_string = 'csv=true&keyword=' + Keyword + '&from_date=' + tempFromDate + '&to_date=' + tempToDate + '&report_type=' + SelectedReportType.value + '&module_type=' + SelectedModuleType.value + '&sort_field=' + sortField + '&sort_order=' + ord + '&state_type=' + (SelectedReportType.value == 2 ? '' : SeletedStateType.value) + '&match_id=' + (SelectedFixture.value != 0 ? SelectedFixture.value : '') + '&contest_id=' + (SelectedContest.value != 0 ? SelectedContest.value : '') + '&state_id=' + (SelectedReportType.value == 2 ? '' : SelectedState.value) + '&invoice_type=' + gstApply;
        ;

        var export_url = 'adminapi/gst/gst_report?';

        HF.exportFunction(query_string, export_url)
    }

    downloadGstReport = (item) => {
        var query_string = '';

        query_string = 'invoice_id=' + item.invoice_id;

        let sessionKey = WSManager.getToken();
        query_string += "&Sessionkey" + "=" + sessionKey;

        window.open(NC.baseURL + 'adminapi/gst/gst_invoice_download?' + query_string, '_blank');
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

            this.setState({
                [name]: value,
                FromDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
                ToDate: new Date(moment().format('D MMM YYYY')),
                Keyword: '',
                isDescOrder: false,
                sortField: 'date',
                SeletedStateType: { label: "StateType Select", value: "" },
                SelectedState: { value: 0, label: "Select State" },
                SelectedFixture: { value: 0, label: "Select Fixture" },
                SelectedContest: { value: 0, label: "Select Contest" },
            }, () => {
                this.clearVariables()
                this.getGstReport()
            })
    }

    handleTypeState = (value, name) => {


        if (value != null) {
            let { GstStateList, fillterStateId, GetAllGstStateList } = this.state;

            if (value.value == 3) {
                let tempStateList = GetAllGstStateList;
                tempStateList = GetAllGstStateList.filter((state, index, array) => {

                    if (state.value != 0 && state.value != fillterStateId) {
                        return true
                    }
                });

                this.setState({ GstStateList: tempStateList }, () => {
                })
            }
            else if (value.value == 2) {
                let tempStateList = GetAllGstStateList;
                tempStateList = GetAllGstStateList.filter((state, index, array) => {
                    return (state.value == fillterStateId);
                });
                this.setState({ GstStateList: tempStateList }, () => {
                })
            }
            else {
                this.setState({ GstStateList: GetAllGstStateList }, () => {
                })
            }
            this.setState({
                [name]: value,
                SelectedState: '',

            }, () => {
                this.clearVariables()
                // this.getStateList()

            })
        }


    }

    handleStateChange = (value, name) => {
        if (value != null)
            this.setState({
                [name]: value,
            }, this.clearVariables)
    }

    handleFixtureChange = (value, name) => {
        this.setState({ GstContest: [] })
        if (value != null) {
            this.setState({
                [name]: value,
                SelectedContest: '',
            }, () => {
                this.getGstCompltedContest();
                this.clearVariables();
            })
        }
        else {
            this.setState({ SelectedFixture: { value: 0, label: "Select Fixture" } })
        }

    }

    handleContestChange = (value, name) => {
        if (value != null) {
            this.setState({ [name]: value }, this.clearVariables)

        }
        else {
            this.setState({ SelectedContest: { value: 0, label: "Select Contest" } })

        }
    }


    handleDateFilter = (date, dateType) => {
        this.setState({ [dateType]: date }, () => {
            this.clearVariables()
            this.getGstReport()
            // this.getGSTCompletedMatch()
        })
    }

    handlePageChange(current_page) {
        if (current_page != this.state.CURRENT_PAGE) {
            this.setState({
                CURRENT_PAGE: current_page
            }, () => {
                this.getGstReport();
            });
        }
    }

    searchByUser = (e) => {
        this.setState({ Keyword: e.target.value, CURRENT_PAGE: 1 }, this.SearchCodeReq)
    }

    SearchCodeReq() {
        if (this.state.Keyword.length > 2 || this.state.Keyword.length == 0)
            this.getGstReport()
    }

    clearFilter = () => {
        this.setState({
            FromDate: HF.getFirstDateOfMonth(),
            ToDate: new Date(moment().format('D MMM YYYY')),
            Keyword: '',
            isDescOrder: false,
            sortField: 'date',
            // SelectedReportType: { label: "Gst Report", value: "tax_invoice" },
            SeletedStateType: { value: 1, label: "All" },
            SelectedState: { value: 0, label: "All" },
            SelectedFixture: { value: 0, label: "Select Fixture" },
            SelectedContest: { value: 0, label: "Select Contest" },
        }, () => {
            this.getGstFillterData()
            this.getGstReport()
        }
        )
    }

    clearVariables = () => {
        this.setState({
            CURRENT_PAGE: 1,
        })
    }

    getSortCond = (skey) => {
        let keyArr = ['date', 'contest_name', 'entry_fee', 'platform_fee']
        if (keyArr.indexOf(skey) >= 0) {
            return true
        } else {
            return false
        }
    }

    sortContest(sortfiled, isDescOrder) {
        let Order = (sortfiled == this.state.sortField) ? !isDescOrder : isDescOrder

        this.setState({
            sortField: sortfiled,
            isDescOrder: Order,
            CURRENT_PAGE: 1,
        }, () => {
            this.getGstReport()
        })
    }
    handleGSTRegime = (value, name) => {
        this.setState({ selectedGSTRegime: value })
        if (value.value == 1) {
            this.setState({ gstApply: 0 }, () => this.getGstReport())
        } else {
            this.setState({ gstApply: 1 }, () => this.getGstReport())
        }
    }




    render() {
        const { CURRENT_PAGE, PERPAGE, TotalUser, Keyword, GstStateList, GstFixture, GstContest, GstReportList, TableFields, TotalCount, SelectedFixture, SelectedContest, SelectedState, SelectedReportType, SeletedStateType, posting, isDescOrder, SelectedModuleType, gstRegime, selectedGSTRegime, gstApply } = this.state
        return (
            <Fragment>
                <div className="animated fadeIn mt-4">
                    <Row>
                        <Col md={12}>
                            <h1 className="h1-cls">
                                {SelectedReportType.label}
                            </h1>
                        </Col>
                    </Row>
                    <div className="user-deposit-amount">
                        <Row className="xfilter-userlist mt-5">
                            {/* <Col md={2}>
                                <div>
                                    <label className="filter-label">Module Type</label>
                                    <Select
                                        isSearchable={true}
                                        class="form-control"
                                        options={this.state.ModuleType}
                                        menuIsOpen={true}
                                        value={SelectedModuleType}
                                        onChange={e => this.handleTypeChange(e, 'SelectedModuleType')}
                                    />
                                </div>
                            </Col>
                            <Col md={2}>
                                <div>
                                    <label className="filter-label">Report Type</label>
                                    <Select
                                        isSearchable={true}
                                        class="form-control"
                                        options={this.state.ReportType}
                                        menuIsOpen={true}
                                        value={SelectedReportType}
                                        onChange={e => this.handleTypeChange(e, 'SelectedReportType')}
                                    />
                                </div>
                            </Col> */}
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
                                        dateFormat={NC.DATE_FORMAT}
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
                                        dateFormat={NC.DATE_FORMAT}
                                    />
                                </div>
                            </Col>

                            <Col md={2}>
                                <div className="search-box gst-regime-view">
                                    <label className="filter-label">GST Regime</label>
                                    <Select
                                        isSearchable={true}
                                        class="form-control"
                                        options={gstRegime}
                                        menuIsOpen={true}
                                        value={selectedGSTRegime}
                                        onChange={e => this.handleGSTRegime(e, 'selectedGSTRegime')}
                                    />
                                </div>
                            </Col>

                            {/* <Col md={4} className="mb-5 mt-4">
                                <div className="filters-area">
                                    <Button className="btn-secondary" onClick={() => this.clearFilter()}>Clear Filters</Button>
                                </div>
                            </Col> */}
                            <Col md={2} className="mt-2">
                                <div className="">
                                    <i className={`export-list icon-export ${(!_isEmpty(GstReportList) && (GstReportList.length > 0)) ? '' : 'cls-dis'}`}
                                        onClick={e => (!_isEmpty(GstReportList) && (GstReportList.length > 0)) ? this.exportGstReport() : null}></i>

                                </div>
                            </Col>
                        </Row>
                        <Row className="xfilter-userlist mt-4">
                            {
                                SelectedReportType.value === '1' &&
                                <Fragment>
                                    <Col md={2}>
                                        <div className="search-box">
                                            <label className="filter-label">State Type</label>
                                            <Select
                                                isSearchable={true}
                                                class="form-control"
                                                options={this.state.StateType}
                                                menuIsOpen={true}
                                                value={SeletedStateType}
                                                onChange={e => this.handleTypeState(e, 'SeletedStateType')}
                                            />
                                        </div>
                                    </Col>
                                    <Col md={2}>
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
                                                isOptionDisabled={true}

                                            />
                                        </div>
                                    </Col>
                                </Fragment>
                            }
                            {
                                SelectedReportType.value === '1' &&
                                <Col md={2}>
                                    <div className={gstApply == 0 ? "" : "d-none"}>
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
                            }

                            <Col md={2}>
                                <div className={gstApply == 0 ? "" : "d-none"}>
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
                            </Col>

                            <Col md={4} className="mb-5 mt-4">
                                <div className="filters-area">
                                    <Button className="btn-secondary" onClick={() => this.getGstReport()}>Apply</Button>
                                </div>
                                <div style={{ marginRight: 20 }} className="filters-area">
                                    <Button className="btn-secondary" onClick={() => this.clearFilter()}>Clear Filters</Button>
                                </div>
                            </Col>
                            {/* <Col md={2} className="mb-5 mt-4">
                                <div className="filters-area">
                                    <Button className="btn-secondary" onClick={() => this.clearFilter()}>Clear Filters</Button>
                                </div>
                            </Col> */}

                        </Row>
                        <Row>
                            <Col md={12} className="table-responsive common-table gst-table">
                                <Table>
                                    <thead>
                                        <tr>
                                            {
                                                _Map(TableFields, (item, idx) => {
                                                    let callback = this.getSortCond(item)
                                                    return (
                                                        <th
                                                            key={idx}
                                                            className={`${callback ? 'pointer' : ''}`}
                                                            onClick={() => callback ? this.sortContest(item, isDescOrder) : null}
                                                        >
                                                            {item === 'hsn_code' && 'HSN/SAC CODE'}
                                                            {item !== 'hsn_code' && item.replace(/_/g, ' ').toUpperCase()}
                                                        </th>
                                                    )
                                                })
                                            }
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {
                                            (!_isEmpty(GstReportList) && (GstReportList.length > 0)) ?
                                                <React.Fragment>
                                                    {
                                                        _Map(GstReportList, (item, ind) => {
                                                            return (
                                                                <tr key={ind}>
                                                                    {
                                                                        _Map(TableFields, (fieldname, idx) => {
                                                                            let callback = this.getSortCond(fieldname)
                                                                            return (

                                                                                <td key={idx}>
                                                                                    {
                                                                                        fieldname === 'scheduled_date' &&
                                                                                        HF.getFormatedDateTime(item[fieldname], 'DD-MM-YYYY hh:mm:ss')
                                                                                    }
                                                                                    {
                                                                                        fieldname === 'txn_date' &&
                                                                                        HF.getFormatedDateTime(item[fieldname], 'DD-MM-YYYY hh:mm:ss')
                                                                                    }

                                                                                    {
                                                                                        fieldname !== 'scheduled_date' && fieldname !== 'txn_date' &&
                                                                                        item[fieldname]
                                                                                    }
                                                                                    <div className="edit-stats-btn">
                                                                                        {
                                                                                            (fieldname == 'download_report') &&
                                                                                            <a onClick={e => this.downloadGstReport(item)} className="pointer">Invoice <i className="icon-download-ic" /></a>
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
                                                    {gstApply == 0 && <tr>
                                                        <td colSpan={`${SelectedReportType.value === '1' ? "10" : ""}`}>
                                                        </td>
                                                        {
                                                            _Map(TotalCount, (item, idx) => {
                                                                return (
                                                                    <td key={idx}>
                                                                        {item}
                                                                    </td>
                                                                )
                                                            })
                                                        }
                                                        <td colSpan={`${SelectedReportType.value === '1' ? "1" : "12"}`}>
                                                        </td>
                                                    </tr>}
                                                    {gstApply == 1 && <tr>
                                                            <td colSpan={`${SelectedReportType.value === '1' ? "6" : ""}`}>
                                                            </td>
                                                            {
                                                                _Map(TotalCount, (item, idx) => {
                                                                    return (
                                                                        <td key={idx}>
                                                                            {item}
                                                                        </td>
                                                                    )
                                                                })
                                                            }
                                                            <td colSpan={`${SelectedReportType.value === '1' ? "1" : "12"}`}>
                                                            </td>
                                                        </tr>
                                                        }

                                                </React.Fragment>

                                                :
                                                <tr>
                                                    <td colSpan='22'>
                                                        {((GstReportList.length == 0) && !posting) ?
                                                            <div className="no-records">No Record Found.</div>
                                                            :
                                                            <Loader />
                                                        }
                                                    </td>
                                                </tr>
                                        }

                                    </tbody>

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


                </div>
            </Fragment>
        )
    }
}
