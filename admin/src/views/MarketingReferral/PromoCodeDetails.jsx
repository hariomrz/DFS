import React, { Component, Fragment } from "react";
import { Row, Col, Table, Input } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Pagination from "react-js-pagination";
import { MomentDateComponent } from "../../components/CustomComponent";
import { PC_getPromoCodeAnalytics } from '../../helper/WSCalling';
import AnalyticsBarChart from './AnalyticsBarChart';
import HF, { _isEmpty } from '../../helper/HelperFunction';
export default class PromoCodeDetails extends Component {
    constructor(props) {
        super(props)
        this.state = {
            TotalPromo: 0,
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            promo_type: this.props.match.params.promo_type,
            activeTab: this.props.match.params.tab,
            Keyword: '',
            PcData: [],
            GraphData: {}
        }
        this.SearchCodeReq = _.debounce(this.SearchCodeReq.bind(this), 500);
    }
    componentDidMount() {
        this.getPromoCodeAnalytics()
        this.getPromoCodeDetail()
    }
    getPromoCodeDetail() {
        let { CURRENT_PAGE, PERPAGE, Keyword } = this.state
        let params = {
            items_perpage: PERPAGE,
            total_items: 0,
            current_page: CURRENT_PAGE,
            sort_order: "DESC",
            sort_field: "PCE.added_date",
            promo_code: this.props.match.params.promo_code,
            keyword: Keyword,
        }

        let API = NC.GET_PROMO_CODE_DETAIL
        if(Number(this.state.promo_type) == 5)
        {
            API = NC.SF_GET_PROMO_CODE_DETAIL
        }

        WSManager.Rest(NC.baseURL + API, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    PromoCodeList: ResponseJson.data ? ResponseJson.data.result : [],
                    TotalPromo: ResponseJson.data ? ResponseJson.data.total : 0,
                    TotalApplied:ResponseJson.data.total_applied
                })

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
                this.getPromoCodeDetail();
            });
        }
    }

    getPrcodeType = (val) => {
        let pct = ''
        if (val == 0)
            pct = 'First Deposit'
        else if (val == 1)
            pct = 'Deposit Range'
        else if (val == 2)
            pct = 'Promo Code'
        else if (val == 3)
            pct = 'Contest Join'
        else if (val == 5)
            pct = 'Stock Contest Code'

        return pct
    }
    searchByCode = (e) => {
        this.setState({ Keyword: e.target.value }, this.SearchCodeReq)
    }

    SearchCodeReq() {
        this.getPromoCodeDetail()
    }

    getPromoCodeAnalytics() {
        let params = {
            promo_code: this.props.match.params.promo_code,
        }

        PC_getPromoCodeAnalytics(params).then(ResponseJson => {
            let gdata = ResponseJson.data ? ResponseJson.data.graph_data : []
            if (ResponseJson.response_code == NC.successCode) {
                let res_data = ResponseJson.data ? ResponseJson.data : []
                if (!_isEmpty(res_data)) {
                    res_data.description = this.replaceStrVar(res_data)
                }

                const grp_categories = gdata ? gdata.categories : [];
                const grp_amt_dep = gdata ? gdata.ad : [];
                const grp_real_cash = gdata ? gdata.rcd : [];

                this.setState({
                    PcData: res_data,
                    // TotalPromo: ResponseJson.data ? ResponseJson.data.total : 0,
                })

                this.setState({
                    GraphData: {
                        credits: {
                            enabled: false,
                        },
                        legend: {
                            // enabled: false,
                            enabled: true,
                            layout: 'vertical',
                            align: 'right',
                            verticalAlign: 'bottom',
                            itemMarginTop: 16,
                            itemMarginBottom: 0,
                            itemMarginLeft: 0,
                            symbolHeight: 20,
                            symbolWidth: 20,
                            symbolRadius: 6,
                            color: 'red',
                            itemStyle: {
                                fontSize: '14px',
                                fontFamily: 'MuliBold',
                                color: '#81898D',
                            },
                        },
                        chart: {
                            type: 'bar',
                            type: 'column'
                        },

                        title: {
                            text: ''
                        },

                        xAxis: {
                            tickWidth: 0,
                            crosshair: false,
                            lineWidth: 1,
                            gridLineWidth: 0,
                            title: '',
                            // categories: ['23/07/2021', '23/07/2021', '23/07/2021', '23/07/2021', '23/07/2021']
                            categories: grp_categories
                        },

                        yAxis: {
                            tickWidth: 0,
                            crosshair: false,
                            lineWidth: 1,
                            gridLineWidth: 1,
                            title: '',
                            allowDecimals: false,
                            min: 1,
                            title: {
                                text: '<span style="font-size: 14px;font-weight: bold;color: #81898D;opacity: 1;">Amount Deposited</span>',
                            },
                            labels: {
                                style: {

                                }
                            }
                        },

                        tooltip: {
                            formatter: function () {
                                return '<b>' + this.x + '</b><br/>' +
                                    this.series.name + ': ' + this.y + '<br/>' +
                                    'Total: ' + this.point.stackTotal;
                            }
                        },

                        plotOptions: {
                            column: {
                                stacking: 'normal'
                            },
                            series: {
                                pointWidth: 32,
                                borderRadius: 4
                            }
                        },

                        series: [{
                            name: this.state.PcData.cash_type == '0' ? 'Bonus Cash Distributed' : this.state.PcData.cash_type == '1' ? 'Real Cash Distributed' : '',
                            // data: [15.20, 3.30, 4.20, 7, 2],
                            data: grp_real_cash,
                            // stack: 'male',
                            color: '#EBCC84',
                        }, {
                            name: 'Amount Deposited',
                            // data: [3.30, 4.20, 4, 2, 5],
                            data: grp_amt_dep,
                            // stack: 'male',
                            color: '#FC9696',
                        }]
                    }
                })

            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    replaceStrVar = (item) => {
        const mapObj = {
            promo_code: item.promo_code,
            discount: item.discount,
            cash_type: (item.cash_type == 0 ? 'Bonus Cash' : item.cash_type == 1 ? 'Real Cash' : '')
        };
        let newstr = item.description.replace(/\b(?:promo_code|discount|cash_type)\b/gi, matched => mapObj[matched]);
        return newstr.replace(/[\])}[{(]/g, '')
    }

    render() {
        let { promo_type, PromoCodeList, CURRENT_PAGE, PERPAGE, TotalPromo, activeTab, Keyword, GraphData, PcData, TotalApplied } = this.state;
        const GraphProps = {
            graph_data: GraphData,
            graph_height: "220px",
            graph_width: "900px",
        }
        return (
            <div className="animated fadeIn promocode-view mt-4 pc-dtl-view">
                {
                    (promo_type == 3 || promo_type == 5 || promo_type == 6) &&
                    <Row className="mb-5">
                        <Col md={6}>
                            <h1 className="h1-cls float-left">Promo Code Detail List</h1>
                            <label
                                className="back-btn"
                                onClick={() => this.props.history.push('/marketing/promo_code?tab=' + activeTab)}>
                                {'<'} Back to Promocode
                        </label>
                        </Col>
                        <Col md={6}>
                            <h2 style={{marginTop:6,textAlign:'right'}} className="h2-cls m-t-5">{'Total Applied :- '}{TotalApplied && TotalApplied!=null ? TotalApplied:'--' }</h2>
                        </Col>
                    </Row>
                }

                {
                    (promo_type != 3 && promo_type != 5 &&  promo_type != 6 ) &&
                    <Fragment>
                        <Row>
                            <Col md={9}>
                                <h2 className="h2-cls">{PcData.promo_code}</h2>
                                <div className="pc-dtl">
                                    {PcData.type_value}
                                    {PcData.type_value ? ' | ' : ''}
                                    {/* {HF.getDateFormat(PcData.start_date, 'DD MMM YYYY')} */}
                                    {HF.getFormatedDateTime(PcData.start_date, 'DD MMM YYYY')}

                                    {PcData.start_date ? ' - ' : ''}
                                    {/* {HF.getDateFormat(PcData.expiry_date, 'DD MMM YYYY')} */}
                                    {HF.getFormatedDateTime(PcData.expiry_date, 'DD MMM YYYY')}

                                </div>
                                {
                                    PcData.description &&
                                    <div className="pc-desc">
                                        {/* {this.replaceStrVar(PcData)} */}
                                        {PcData.description}
                                    </div>
                                }
                            </Col>
                            <Col md={3}>
                                <label
                                    className="back-btn"
                                    onClick={() => this.props.history.push('/marketing/promo_code?tab=' + activeTab)}>
                                    {'<'} Back to Promocode
                                </label>
                            </Col>
                        </Row>

                        <Row>
                            <Col>
                                <ul className="pc-dtl-list">
                                    <li className="pc-dtl-itm">
                                        <div>{PcData.u_total ? PcData.u_total : 0}</div>
                                        <div>Total Users</div>
                                    </li>
                                    <li className="pc-dtl-itm">
                                        <div>{PcData.r_amt ? PcData.r_amt : 0}</div>
                                        <div>Total{PcData.cash_type == '0' ? ' Bonus ' : PcData.cash_type == '1' ? ' Real ' : ''}Cash Distributed</div>
                                    </li>
                                    <li className="pc-dtl-itm">
                                        <div>{PcData.d_amt ? PcData.d_amt : 0}</div>
                                        <div>Amount Deposited</div>
                                    </li>
                                </ul>
                            </Col>
                        </Row>

                        <Row>
                            <Col md={12}>
                                <div className="graph-box">
                                    {/* <HighchartsReact
                                containerProps={{ style: { height: "220px", width: "900px" } }}
                                highcharts={Highcharts}
                                options={this.state.GraphData}
                            /> */}
                                    <AnalyticsBarChart {...GraphProps} />
                                </div>
                            </Col>
                        </Row>
                    </Fragment>
                }
                <Row>
                    <Col md={12}>
                        <div className="pcd-search">
                            <label className="filter-label">Search</label>
                            <Input
                                placeholder="Name"
                                name='Keyword'
                                value={Keyword}
                                onChange={this.searchByCode}
                            />
                            <i className="icon-search"></i>
                        </div>
                    </Col>
                </Row>

                <Row className="mt-4">
                    <Col md={12} className="table-responsive common-table">
                        <Table>
                            <thead>
                                <tr>
                                    {
                                        (promo_type == 3 || promo_type == 5 || promo_type == 6) &&
                                        <Fragment>
                                            <th className="left-th text-center">Type</th>
                                            <th>Promo code</th>
                                        </Fragment>
                                    }

                                    <th>Name</th>
                                    <th>Benefits Received</th>
                                    {(promo_type == 3 || promo_type == 5 || promo_type == 6) ?
                                        <Fragment>
                                            <th>Game Name</th>
                                            <th>Entry Fee</th>
                                            <th>Contest Scheduled Date</th>
                                        </Fragment>
                                        :
                                        <th>Deposit amount</th>
                                    }
                                    <th>Promocode Used Date</th>
                                    <th>Max Usage Limit</th>
                                    {
                                      promo_type == 3 &&<th>Contest Id</th>
                                    }
                              
                                    <th className="right-th">Status</th>
                                </tr>
                            </thead>
                            {
                                TotalPromo > 0 ?
                                    _.map(PromoCodeList, (item, idx) => {
                                        let cur = item.cash_type == '0' ? 'B' : item.cash_type == '1' ? HF.getCurrencyCode() : ''
                                        return (
                                            <tbody key={idx}>
                                                <tr>
                                                    {
                                                        (promo_type == 3 || promo_type == 5 || promo_type == 6) &&
                                                        <Fragment>
                                                            <td>
                                                                {
                                                                    item.type == 0 &&
                                                                    <td>First Deposit</td>
                                                                }
                                                                {
                                                                    item.type == 1 &&
                                                                    <td>Deposit Range</td>
                                                                }
                                                                {
                                                                    item.type == 2 &&
                                                                    <td>Promo Code</td>
                                                                }
                                                                {
                                                                    item.type == 3 &&
                                                                    <td>Contest Join</td>
                                                                }
                                                                {
                                                                    item.type == 5 &&
                                                                    <td>Stock Contest Join</td>
                                                                }
                                                                 {
                                                                    item.type == 6 &&
                                                                    <td>Live Fantasy Contest Join</td>
                                                                }
                                                            </td>
                                                            <td>{item.promo_code}</td>
                                                        </Fragment>
                                                    }
                                                    <td
                                                        onClick={() => this.props.history.push("/profile/" + item.user_unique_id + '?tab=trans')}
                                                        className="user-name text-click">
                                                        {item.user_full_name}
                                                    </td>
                                                    {(promo_type == 3 || promo_type == 5 || promo_type == 6) ?
                                                        <Fragment>
                                                            <td>{cur}{item.amount_received}</td>
                                                            <td>{item.contest_name}</td>
                                                            <td>{item.entry_fee}</td>
                                                        </Fragment>
                                                        :
                                                        <td>{cur}{item.amount_received}</td>
                                                    }
                                                    <td>

                                                        {(promo_type == 3 || promo_type == 5 || promo_type == 6) ?
                                                            // <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D-MMM-YYYY hh:mm A" }} />
                                                            <>{HF.getFormatedDateTime(item.season_scheduled_date, "D-MMM-YYYY hh:mm A ")}</>

                                                            :
                                                            <span>{HF.getCurrencyCode()}{item.deposit_amount}</span>
                                                        }
                                                    </td>
                                                    <td>
                                                        {/* <MomentDateComponent data={{ date: item.added_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                                                        {HF.getFormatedDateTime(item.added_date, "D-MMM-YYYY hh:mm A")}
                                                    </td>
                                                    <td>{item.max_usage_limit && item.max_usage_limit != null ? item.max_usage_limit: '--' }</td>
                                                    {
                                                       item.type == 3 &&
                                                       <td>{item.contest_unique_id && item.contest_unique_id != null && item.contest_unique_id != '0'  ? item.contest_unique_id : '--'}</td>

                                                    }

                                                    <td>
                                                        {item.status == "0" && <span className="pending">Pending</span>}
                                                        {item.status == "1" && <span className="success">Success</span>}
                                                        {item.status == "2" && <span className="failed">Failed</span>}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        )
                                    })
                                    :
                                    <tbody>
                                        <tr>
                                            <td colSpan="12">
                                                <div className="no-records">No Records Found.</div>
                                            </td>
                                        </tr>
                                    </tbody>
                            }
                        </Table>
                    </Col>
                </Row>
                {TotalPromo > PERPAGE &&
                    <div className="custom-pagination lobby-paging">
                        <Pagination
                            activePage={CURRENT_PAGE}
                            itemsCountPerPage={PERPAGE}
                            totalItemsCount={TotalPromo}
                            pageRangeDisplayed={5}
                            onChange={e => this.handlePageChange(e)}
                        />
                    </div>
                }
            </div>
        )
    }
} 