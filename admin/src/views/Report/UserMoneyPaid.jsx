import React, { Component, Fragment } from "react";
import { Row, Col, Button, Table, Input } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Pagination from "react-js-pagination";
import Loader from '../../components/Loader';
import HF from '../../helper/HelperFunction';
import SelectDate from "../../components/SelectDate";
import moment from "moment-timezone";
export default class UserMoneyPaid extends Component {
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
            sortField: 'U.added_date',
            isDescOrder: true,
            posting: false,
            FromDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
            ToDate: new Date(moment().format('D MMM YYYY')),
        }
        this.SearchCodeReq = _.debounce(this.SearchCodeReq.bind(this), 500);
    }
    componentDidMount() {
        this.getReportUser()
    }

    getReportUser = () => {
        this.setState({ posting: true })
        const { PERPAGE, CURRENT_PAGE, Keyword, FromDate, ToDate, sortField, isDescOrder } = this.state
        let params = {
            items_perpage: PERPAGE,
            total_items: 0,
            current_page: CURRENT_PAGE,
            sort_order: 'ASC',
            sort_field: sortField,
            csv: false,
            // from_date: FromDate,
            // to_date: ToDate,
            from_date: FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '',
            to_date:  ToDate ? moment(ToDate).format('YYYY-MM-DD') : '',
            keyword: Keyword,
        }
        WSManager.Rest(NC.baseURL + NC.GET_REPORT_MONEY_PAID_BY_USER, params).then(ResponseJson => {
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
            sort_order: isDescOrder ? 'DESC' : "ASC",
            sort_field: sortField,
            from_date: FromDate,
            to_date: moment(ToDate).format("YYYY-MM-DD"),
            // tempFromDate: FromDate,
            // tempToDate = moment(ToDate).format("YYYY-MM-DD"),
            keyword: Keyword,
            report_type: "user_money_paid"
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
        let sOrder = isDescOrder ? 'DESC' : "ASC"
        if (FromDate != '' && ToDate != '') {
            // tempFromDate = FromDate ? HF.getFormatedDateTime(FromDate, 'YYYY-MM-DD') : '';
            // tempToDate = ToDate ? HF.getFormatedDateTime(ToDate, 'YYYY-MM-DD') : '';
            tempFromDate = WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD')
            tempToDate = moment(ToDate).format("YYYY-MM-DD");
        }

        var query_string = '&report_type=user_money_paid&csv=1&keyword=' + Keyword + '&from_date=' + tempFromDate + '&to_date=' + tempToDate + '&sort_order=' + sOrder + '&sort_field=' + sortField;
        var export_url = 'adminapi/index.php/report/get_report_money_paid_by_user?';


        HF.exportFunction(query_string, export_url)
    }

    handleDateFilter = (date, dateType) => {
        this.setState({ [dateType]: date }, () => {
            if (this.state.FromDate && this.state.ToDate) {
                this.getReportUser()
            }
        })
    }



    handlePageChange(current_page) {
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            this.getReportUser();
        });
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
            // FromDate: '',
            // ToDate: '',
            Keyword: '',
            // isDescOrder: true,
            // sortField: 'first_name'
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

    handleDate = (date, dateType) => {
        this.setState({ [dateType]: date }, () => {
            console.log('state', [dateType])
            if (this.state.ToDate) {
                this.getReportUser()
            }
        })
    }

    render() {
        const { FromDate, ToDate, posting, UserReportList, CURRENT_PAGE, PERPAGE, TotalUser, Keyword, isDescOrder } = this.state
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
            date_key: 'ToDate',
            place_holder: 'To Date',
            popup_placement: "bottom-end"
        }
        return (
            <Fragment>
                <div className="animated fadeIn promocode-view mt-4">
                    <Row>
                        <Col md={12}>
                            <h1 className="h1-cls">User Money Paid</h1>
                        </Col>
                    </Row>
                    <div className="promocode-list-view">

                        <Row className="filters-box">
                            <Col md={2}>
                                <label className="filter-label">Select From Date</label>
                                <SelectDate DateProps={FromDateProps} />
                            </Col>
                            <Col md={2}>
                                <label className="filter-label">Select To Date</label>
                                <SelectDate DateProps={ToDateProps} />
                            </Col>
                            <Col md={2}>
                                <div className="">
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
                                    <Button className="btn-secondary" onClick={() => this.clearFilter()}>Clear Username</Button>
                                </div>
                            </Col>

                            <Col md={2} className="">
                                {/* <i className="export-list icon-export"
                                    onClick={e => (TotalUser > NC.EXPORT_REPORT_LIMIT) ? this.exportReport_Post() :
                                     this.exportReport_Get()}></i> */}

                                <div className="export-list" onClick={e => (TotalUser > NC.EXPORT_REPORT_LIMIT) ? this.exportReport_Post() : this.exportReport_Get()}>
                                    <span>Export</span>
                                    <i className="icon-export"
                                    ></i>
                                </div>

                            </Col>


                            <Col md={2}>
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
                                            <th>Unique Id</th>
                                            <th className="pointer" onClick={() => this.sortContest('user_name', isDescOrder)}>UserName</th>
                                            <th className="pointer" onClick={() => this.sortContest('first_name', isDescOrder)}>Name</th>
                                            <th className="pointer" onClick={() => this.sortContest('phone_no', isDescOrder)}>Phone</th>
                                            <th className="pointer" onClick={() => this.sortContest('email', isDescOrder)}>Email</th>
                                            <th className="pointer" onClick={() => this.sortContest('coins_paid', isDescOrder)}>Coins spent</th>
                                            <th className="pointer" onClick={() => this.sortContest('point_balance', isDescOrder)}>Coin Balance</th>
                                            <th className="pointer" onClick={() => this.sortContest('point_balance', isDescOrder)}>Refund Coin</th>
                                            <th className="pointer" onClick={() => this.sortContest('real_money_paid', isDescOrder)}>Real Money Paid</th>
                                            <th className="pointer" onClick={() => this.sortContest('balance', isDescOrder)}>Real Balance</th>
                                            <th className="pointer" onClick={() => this.sortContest('balance', isDescOrder)}>Refund Amount</th>
                                            <th className="pointer" onClick={() => this.sortContest('bouns_money_paid', isDescOrder)}>Bonus Money Paid</th>
                                            <th className="pointer" onClick={() => this.sortContest('bonus_balance', isDescOrder)}>Bonus Balance</th>
                                            <th className="pointer" onClick={() => this.sortContest('bonus_balance', isDescOrder)}>Refund Bonus</th>
                                            <th className="pointer" onClick={() => this.sortContest('added_date', isDescOrder)}>Member Since </th>
                                            <th className="pointer" onClick={() => this.sortContest('status', isDescOrder)}>Status </th>
                                        </tr>
                                    </thead>
                                    {
                                        UserReportList.length > 0 ?
                                            _.map(UserReportList, (item, idx) => {
                                                return (
                                                    <tbody key={idx}>
                                                        <tr>
                                                            <td>{item.user_unique_id}</td>
                                                            <td><a onClick={() => this.props.history.push({ pathname: "/profile/" + item.user_unique_id, state: { activeTabId: '3' } })} className="text-click">{item.user_name}</a></td>
                                                            <td>{item.name}</td>
                                                            <td>{item.phone_no}</td>
                                                            <td>{item.email}</td>
                                                            <td><a onClick={() => this.props.history.push({ pathname: "/profile/" + item.user_unique_id, state: { activeTabId: '3' } })} className="text-click">{item.coins_paid}</a></td>
                                                            <td><a onClick={() => this.props.history.push({ pathname: "/profile/" + item.user_unique_id, state: { activeTabId: '3' } })} className="text-click">{item.point_balance}</a></td>
                                                            <td><a onClick={() => this.props.history.push({ pathname: "/profile/" + item.user_unique_id, state: { activeTabId: '3' } })} className="text-click">{item.refund_coin}</a></td>
                                                            <td><a onClick={() => this.props.history.push({ pathname: "/profile/" + item.user_unique_id, state: { activeTabId: '3' } })} className="text-click">{item.real_money_paid}</a></td>
                                                            <td><a onClick={() => this.props.history.push({ pathname: "/profile/" + item.user_unique_id, state: { activeTabId: '3' } })} className="text-click">{item.balance}</a></td>
                                                            <td><a onClick={() => this.props.history.push({ pathname: "/profile/" + item.user_unique_id, state: { activeTabId: '3' } })} className="text-click">{item.refund_amount}</a></td>
                                                            <td><a onClick={() => this.props.history.push({ pathname: "/profile/" + item.user_unique_id, state: { activeTabId: '3' } })} className="text-click">{item.bouns_money_paid}</a></td>
                                                            <td><a onClick={() => this.props.history.push({ pathname: "/profile/" + item.user_unique_id, state: { activeTabId: '3' } })} className="text-click">{item.bonus_balance}</a></td>
                                                            <td><a onClick={() => this.props.history.push({ pathname: "/profile/" + item.user_unique_id, state: { activeTabId: '3' } })} className="text-click">{item.refund_bonus}</a></td>
                                                            <td>
                                                                {/* {WSManager.getUtcToLocalFormat(item.member_since, 'D-MMM-YYYY')} */}
                                                                {HF.getFormatedDateTime(item.added_date, "D-MMM-YYYY")}
                                                            </td>
                                                            <td>{item.status == 1 ? <i className="icon-inactive text-red"></i> : <i className="icon-verified text-green"></i>}</td>
                                                        </tr>
                                                    </tbody>
                                                )
                                            })
                                            :
                                            <tbody>
                                                <tr>
                                                    <td colSpan='15'>
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


                </div>
            </Fragment>
        )
    }
}