import React, { Component, Fragment } from "react";
import { Row, Col, Button, Table } from 'reactstrap';
import Select from 'react-select';
import _ from 'lodash';
import Pagination from "react-js-pagination";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import * as NC from "../../helper/NetworkingConstants";
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import Loader from '../../components/Loader';
import moment from 'moment';
import { MomentDateComponent } from "../../components/CustomComponent";
import HF from '../../helper/HelperFunction';
const TrTypeOptions = [
    { value: '', label: 'All' },
    { value: '1', label: 'Debit' },
    { value: '0', label: 'Credit' }
]

const TrStatusOptions = [
    { value: '', label: 'All' },
    { value: '0', label: 'Pending' },
    { value: '1', label: 'Success' },
    { value: '2', label: 'Failed' }
]

const TrDescriptionOptions = [
    { value: '', label: 'All' },
    { value: '0', label: 'Admin' },
    { value: '400', label: 'Contest Commission' },
]

export default class NetworkCommission extends Component {
    constructor(props) {
        super(props)
        this.state = {
            Total: 0,
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            TransactionData: [],
            TrTypeChange: '',
            TrStatusChange: '',
            TrDescChange: '',
            posting: false,
            FromDate: HF.getFirstDateOfMonth(),
            ToDate: new Date(),
        }
    }
    componentDidMount() {
        this.getAllTransaction()
    }

    getAllTransaction = () => {
        this.setState({ posting: true })
        const { PERPAGE, CURRENT_PAGE, TrTypeChange, TrStatusChange, TrDescChange, FromDate, ToDate } = this.state

        let params = {
            items_perpage: PERPAGE,
            total_items: 0,
            current_page: CURRENT_PAGE,
            sort_order: "DESC",
            sort_field: "created_date",
            from_date: FromDate ? moment(FromDate).format("DD-MM-YYYY") : '',
            to_date: ToDate ? moment(ToDate).format("DD-MM-YYYY") : '',
            status: TrStatusChange,
            type: TrTypeChange,
            source: TrDescChange,
        }

        WSManager.Rest(NC.baseURL + NC.GET_CONTEST_COMMISSION_HISTORY, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {

                this.setState({
                    posting: false,
                    TransactionData: ResponseJson.data.result,
                })
                if (ResponseJson.data.total > 0) {
                    this.setState({
                        Total: ResponseJson.data.total
                    })
                }

            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    handlePageChange(current_page) {
        if (current_page !== this.state.CURRENT_PAGE) {
            this.setState({
                CURRENT_PAGE: current_page
            }, () => {
                this.getAllTransaction()
            });
        }
    }

    handleTypeChange = (value, name) => {
        if (value != null)
            this.setState({ [name]: value.value, CURRENT_PAGE: 1 }, this.getAllTransaction)
    }

    handleDateFilter = (date, dateType) => {
        this.setState({ [dateType]: date, CURRENT_PAGE: 1 }, () => {
            if (this.state.FromDate && this.state.ToDate) {
                this.getAllTransaction()
            }
        })
    }

    clearFilter = () => {
        this.setState({
            CURRENT_PAGE: 1,
            TrTypeChange: '',
            TrStatusChange: '',
            TrDescChange: '',
            FromDate: '',
            ToDate: '',
        }, this.getAllTransaction
        )
    }

    exportUser = () => {
        var query_string = '';//pairs.join('&');
        let sessionKey = WSManager.getToken();
        query_string += "Sessionkey" + "=" + sessionKey;

        window.open(NC.baseURL + 'adminapi/nw_contest/get_contest_commission_history_export?' + query_string, '_blank');
    }

    render() {
        let { posting, CURRENT_PAGE, PERPAGE, Total, TransactionData, TrTypeChange, TrStatusChange, TrDescChange, FromDate, ToDate } = this.state

        return (
            <Fragment>
                <div className="animated fadeIn transaction-list">
                    <Row>
                        <Col md={12}>
                            <h1 className="h1-cls">Commission History</h1>
                        </Col>
                    </Row>
                    <Row className="mt-5">
                        <Col md={12}>
                            <h3 className="h3-cls">Filters</h3>
                        </Col>
                    </Row>
                    <Row className="mt-2 mb-5">
                        <Col md={2}>
                            <div>
                                <label className="filter-label">Transaction Type</label>
                                <Select
                                    isSearchable={true}
                                    class="form-control"
                                    options={TrTypeOptions}
                                    placeholder="Withdrawal Type"
                                    menuIsOpen={true}
                                    value={TrTypeChange}
                                    onChange={e => this.handleTypeChange(e, 'TrTypeChange')}
                                />
                            </div>
                        </Col>
                        <Col md={2}>
                            <div>
                                <label className="filter-label">Status</label>
                                <Select
                                    isSearchable={true}
                                    class="form-control"
                                    options={TrStatusOptions}
                                    placeholder="Transaction Status"
                                    menuIsOpen={true}
                                    value={TrStatusChange}
                                    onChange={e => this.handleTypeChange(e, 'TrStatusChange')}
                                />
                            </div>
                        </Col>
                        <Col md={2}>
                            <label className="filter-label">From Date</label>
                            <DatePicker
                                maxDate={new Date(ToDate)}
                                className="form-control"
                                showYearDropdown='true'
                                selected={FromDate}
                                onChange={e => this.handleDateFilter(e, "FromDate")}
                                placeholderText="From"
                                dateFormat='dd/MM/yyyy'
                            />
                        </Col>
                        <Col md={2}>
                            <label className="filter-label">To Date</label>
                            <DatePicker
                                minDate={new Date(FromDate)}
                                maxDate={new Date()}
                                className="form-control"
                                showYearDropdown='true'
                                selected={ToDate}
                                onChange={e => this.handleDateFilter(e, "ToDate")}
                                placeholderText="To"
                                dateFormat='dd/MM/yyyy'
                            />
                        </Col>
                        <Col md={2}>
                            <div>
                                <label className="filter-label">Description</label>
                                <Select
                                    isSearchable={true}
                                    class="form-control"
                                    options={TrDescriptionOptions}
                                    placeholder="Description"
                                    menuIsOpen={true}
                                    value={TrDescChange}
                                    onChange={e => this.handleTypeChange(e, 'TrDescChange')}
                                />
                            </div>
                        </Col>
                        <Col md={2}>
                            <div className="mt-4">
                                <Button className="btn-secondary" onClick={() => this.clearFilter()}>Clear Filters</Button>
                            </div>
                        </Col>
                    </Row>
                    <Row className="mb-2">
                        <Col md={12}>
                            <h3 className="h3-cls pull-left">Transactions</h3>
                            <div className="cursor-pointer">
                                <i className="export-list icon-export" onClick={e => this.exportUser()}></i>
                            </div>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12} className="table-responsive common-table nw-table">
                            <Table>
                                <thead>
                                    <tr>
                                        <th>Description</th>
                                        <th>Contest</th>
                                        <th>Commission Amount</th>
                                        <th>Payment Type</th>
                                        <th>Transaction Date</th>
                                        <th className="right-th">Status</th>
                                    </tr>
                                </thead>

                                {
                                    TransactionData.length > 0 ?
                                        _.map(TransactionData, (transaction, idx) => {

                                            let item_prize_data = (transaction.custom_data) ? transaction.custom_data : [];
                                            let row_prize_data = (item_prize_data.length > 0) ? item_prize_data : [];
                                            return (
                                                <tbody key={idx}>
                                                    <tr>
                                                        <td>{transaction.trans_desc}</td>
                                                        {transaction.contest_name ? (
                                                            <td
                                                                onClick={() => this.props.history.push('/network-game/details/' + transaction.contest_unique_id)}
                                                                className="user-name">{transaction.contest_name}
                                                            </td>
                                                        )
                                                            :
                                                            <td>--</td>
                                                        }

                                                        <td>{transaction.commission_amount ? transaction.commission_amount : '--'
                                                        }</td>

                                                        <td>{transaction.type == 0 ? 'CREDIT' : 'DEBIT'
                                                        }</td>

                                                        
                                                        <td>
                                                            {/* <MomentDateComponent data={{ date: transaction.order_date_added, format: "D-MMM-YYYY hh:mm A" }} /> */}
                                                            {HF.getFormatedDateTime(transaction.order_date_added, "D-MMM-YYYY hh:mm A")}

                                                        </td>
                                                        <td>
                                                            {
                                                                transaction.status == 0
                                                                    ?
                                                                    <i className="icon-verified" title='Not yet' />
                                                                    :
                                                                    transaction.status == 1 || transaction.status == 3
                                                                        ?
                                                                        <i className="icon-verified text-green" title='Payment Processed Done' />
                                                                        :
                                                                        <i className="icon-inactive text-red" title={(transaction.source == 8) ? 'Rejected' : 'Failed'} />
                                                            }
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            )
                                        })
                                        :
                                        <tbody>
                                            <tr>
                                                <td colSpan='22'>
                                                    {(TransactionData.length == 0 && !posting) ?
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
                    {Total > PERPAGE && (
                        <div className="custom-pagination lobby-paging">
                            <Pagination
                                activePage={CURRENT_PAGE}
                                itemsCountPerPage={PERPAGE}
                                totalItemsCount={Total}
                                pageRangeDisplayed={5}
                                onChange={e => this.handlePageChange(e)}
                            />
                        </div>
                    )
                    }
                </div>
            </Fragment>
        )
    }
}
