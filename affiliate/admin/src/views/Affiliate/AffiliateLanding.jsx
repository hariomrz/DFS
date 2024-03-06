import React, { Component, Fragment } from "react";
import { Row, Col, Button, Table, Input } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import DatePicker from "react-datepicker";
import SelectDate from "../../components/SelectDate";
import Moment from 'react-moment';
import "react-datepicker/dist/react-datepicker.css";
import Pagination from "react-js-pagination";
import Loader from '../../components/Loader';
import HF, { _isEmpty } from '../../helper/HelperFunction';
class AffiliateLanding extends Component {
    constructor(props) {
        super(props)
        this.state = {
            TotalUser: 0,
            startDate: '',
            endDate: '',
            FromDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
            ToDate: new Date().toISOString().split('T')[0],
            posting: false,
            items_perpage: 10,
            current_page: 1,
            sort_field: 'date_created',
            sort_order: 'DESC',
            searchText: '',
            affiliateList: [],
            AffiliateTotalCount: 0,
        }
        this.SearchCodeReq = _.debounce(this.SearchCodeReq.bind(this), 500);
    }

    componentDidMount = () => {
        this._getAffiliate();
    }


    /***
     * SEARCH USER BY FILLTER LIST 
     */


    searchByUser = (e) => {
        this.setState({ searchText: e.target.value }, this.SearchCodeReq)
    }

    SearchCodeReq() {
        this._getAffiliate()
    }


    /***
     * GET AFFILIATE API INTEGRATION 
     */
    _getAffiliate = () => {
        this.setState({ posting: true })
        let params = {
            "items_perpage": this.state.items_perpage,
            "current_page": this.state.current_page,
            "sort_field": this.state.sort_field,
            "sort_order": "DESC",
            "keyword": this.state.searchText,
            "from_date": this.state.FromDate,
            "to_date": this.state.ToDate,
            "csv": false,
        }
        WSManager.Rest(NC.baseURL + NC.GET_AFFILIATE, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                if (ResponseJson.data.length != 0) {
                    this.setState({
                        affiliateList: ResponseJson.data.result,
                        AffiliateTotalCount: ResponseJson.data.total
                    })
                } else {
                    this.setState({
                        affiliateList: [],
                        AffiliateTotalCount: 0
                    })
                }
                this.setState({ posting: false })
            } else {
                notify.show(ResponseJson.message, "error", 3000)
                this.setState({ posting: false })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    /***
     * CLEAR ALL FILTER BUTTON INTEGRATION  
     */

    clearFilter = () => {
        this.setState({
            FromDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
            ToDate: new Date(),
            searchText: '',
            sort_field: "date_created",
            sortField: 'email'
        }, () => {
            this._getAffiliate()
        }
        )
    }

    /***
     * HANDLE DATE CHANGE EVENT 
     */

    handleDate = (e, date_key) => {
        this.setState({
            [date_key]: e.toISOString().split('T')[0]
        }, () => {
            this._getAffiliate();
        })
    }

    /***
     * HANDLE PAGINATION CHANGE EVENT
     */

    handlePageChange = (e) => {
        this.setState({
            current_page: e
        }, () => {
            this._getAffiliate();
        })
    }


    /**
     * OPEN VIEW AFFILLIATE ON CLICK
    */

    _viewAffiliateDetail = (item) => {
        this.props.history.push({
            pathname: '/view-affilate/' + item.affiliate_id,
        })
    }

    /***
     * OPEN AFFILLIATE SCREEN
    */

    _openAddAffiliate = () => {
        this.props.history.push({
            pathname: '/create-affilate',
        })
    }

    /*****
     * EXPORT CSV FUNCRTION 
     */

    exportReport_Get = () => {
        let { searchText, FromDate, ToDate, sort_order, sort_field } = this.state
        let fromDate_ = FromDate;
        let toDate_ = ToDate;
        // let fromDate_ = FromDate.toISOString().split('T')[0];
        // let toDate_ = ToDate.toISOString().split('T')[0];
        var query_string = 'csv=1&keyword=' + searchText + '&from_date=' + fromDate_ + '&to_date=' + toDate_ + '&sort_order=' + sort_order + '&sort_field=' + sort_field + '&status=' + '';
        var export_url = 'affiliate/admin/affiliate/get_affiliates?&';
        HF.exportFunction(query_string, export_url)
    }

    _openEditAffiliate = (item) => {
        this.props.history.push({
            pathname: '/create-affilate',
            item: item
        })
    }

    render() {
        const { searchText, AffiliateTotalCount, affiliateList, sort_field, current_page, items_perpage, TotalUser, posting, FromDate, ToDate } = this.state
        const FromDateProps = {
            min_date: false,
            max_date: new Date(ToDate),
            sel_date: new Date(FromDate),
            date_key: 'FromDate',
            place_holder: 'From Date',
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
        const ToDateProps = {

            min_date: new Date(FromDate),
            max_date: new Date(),
            sel_date: new Date(ToDate),
            date_key: 'ToDate',
            place_holder: 'To Date',
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
        return (
            <Fragment>
                <div className="animated fadeIn promocode-view mt-4">
                    <Row>
                        <Col md={12}>
                            <h1 className="h1-cls">Affiliate List</h1>
                        </Col>
                    </Row>

                    <div className="promocode-list-view">

                        <Row className="filter-userlist mt-4">
                            <Col md={12}>
                                <div className="float-left">
                                    <label className="filter-label">Start Date</label>
                                    <SelectDate DateProps={FromDateProps} />
                                </div>
                                <div className="float-left">
                                    <label className="filter-label">End Date</label>
                                    <SelectDate DateProps={ToDateProps} />
                                </div>
                                <div className="float-left">
                                    <label className="filter-label">Search User</label>
                                    <Input
                                        placeholder="Search User"
                                        name='code'
                                        value={searchText}
                                        onChange={this.searchByUser}
                                    />
                                </div>
                            </Col>
                        </Row>
                        <Row className="filters-box">
                            <Col md={12}>
                                <div className="filters-area">
                                    <Button className="btn-secondary m-r-20" onClick={() => this._openAddAffiliate()}>Add</Button>

                                    <Button className="btn-secondary" onClick={() => this.clearFilter()}>Clear Filters</Button>
                                    <i className="export-list-2 icon-export"
                                    onClick={e => (this.exportReport_Get())}></i>
                                </div>
                            </Col>
                        </Row>
                        <Row className="filters-box">
                            <Col md={12}>
                                <div className="filters-area">
                                    <h4>Total Record Count:{this.state.AffiliateTotalCount}</h4>
                                </div>
                            </Col>
                        </Row>
                        <Row>
                            <Col md={12} className="table-responsive common-table">
                                <Table>
                                    <thead>
                                        <tr>
                                          
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Created Date</th>
                                            <th>Modified Date</th>
                                            <th>Action</th>

                                        </tr>
                                    </thead>
                                    {
                                        affiliateList.length > 0 ?
                                            _.map(affiliateList, (item, idx) => {
                                                return (
                                                    <tbody key={idx}>
                                                        <tr>
                                                           
                                                            <td className='cursor-p' onClick={() => { this._viewAffiliateDetail(item) }}>
                                                               <u>{item.name}</u> 
                                                            </td>
                                                            <td>
                                                                {item.email}
                                                            </td>
                                                            <td><Moment format="D MMM YYYY" withTitle>
                                                                {item.date_created}
                                                            </Moment></td>
                                                            <td><Moment format="D MMM YYYY" withTitle>
                                                                {item.date_modified}
                                                            </Moment></td>
                                                            <td onClick={() => { this._openEditAffiliate(item) }}>
                                                                <span className='cursor-p'><u>Edit</u></span>
                                                            </td>

                                                        </tr>
                                                    </tbody>
                                                )
                                            })
                                            :
                                            <tbody>
                                                <tr>
                                                    <td colSpan='22'>
                                                        {(affiliateList.length == 0 && !posting) ?
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
                        {AffiliateTotalCount > items_perpage && (
                            <div className="custom-pagination lobby-paging float-right">
                                <Pagination
                                    activePage={current_page}
                                    itemsCountPerPage={items_perpage}
                                    totalItemsCount={AffiliateTotalCount}
                                    pageRangeDisplayed={10}
                                    onChange={e => this.handlePageChange(e)}
                                />
                            </div>
                        )
                        }
                    </div>


                </div>
            </Fragment>
        );
    }
}

export default AffiliateLanding;