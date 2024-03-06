import React, { Component } from 'react';


import { Row, Col, Table } from 'reactstrap';
import * as NC from "../../../../helper/NetworkingConstants";
import WSManager from "../../../../helper/WSManager";
import _ from 'lodash';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import Pagination from "react-js-pagination";
import { notify } from 'react-notify-toast';

import Highcharts from 'highcharts';
import HighchartsReact from 'highcharts-react-official';
import moment from 'moment';
import HF from '../../../../helper/HelperFunction';


class Referrals extends Component {
    constructor(props) {
        super(props)
        this.state = {
            Total: 0,
            PERPAGE: 50,
            CURRENT_PAGE: 1,
            from_date: HF.getFirstDateOfMonth(),
            to_date: new Date(),
            Total: 0,
            ReferralData: [],
            ReferralList: [],
            ReferralTrendData: [],
            RefArr: [],
            ReferralTrendGraph: {
                title: {
                    text: ''
                },
                credits: {
                    enabled: false,
                }
            }
        }
    }
    componentDidMount() {
        this.getReferralData()
    }
    getReferralData = () => {
        let { from_date, to_date, PERPAGE, CURRENT_PAGE } = this.state
        let params = {
            "user_id": this.props.user_id,
            "from_date": moment(from_date).format("YYYY-MM-DD"),
            "to_date": moment(to_date).format("YYYY-MM-DD"),
            "items_perpage": PERPAGE,
            "current_page": CURRENT_PAGE,
        }

        WSManager.Rest(NC.baseURL + NC.GET_USER_REFERRAL_DATA, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                
                this.setState({
                    RefArr : ResponseJson.data.referral_trend
                })
                if (!_.isEmpty(this.state.RefArr)) {
                    let dateArr = []
                    let refCountArr = []
                    _.map(this.state.RefArr, (refItem, idx) => {
                        dateArr.push(moment(refItem.created_date).format('MMM DD'))
                        refCountArr.push(parseInt(refItem.referral_count))
                    })

                    //Start referral trend
                    this.setState({
                        ReferralTrendGraph: {
                            title: {
                                text: '',
                            },
                            chart: {
                                height: '300px',
                            },
                            xAxis: {
                                categories: dateArr,
                                min: 0,
                                tickWidth: 0,
                                crosshair: false,
                                lineWidth: 3,
                                gridLineWidth: 0,
                                title: '',
                                lineColor: '#D8D8D8',
                                title: {
                                    text: ''
                                }
                            },
                            yAxis: {
                                title: {
                                    text: ''
                                },
                                min: 0,
                                tickWidth: 0,
                                crosshair: false,
                                lineWidth: 3,
                                gridLineWidth: 3,
                                title: '',
                                lineColor: '#D8D8D8'
                            },
                            series: [
                                {
                                    type: 'line',
                                    name: 'Date',
                                    data: refCountArr,
                                    // data: [1, 4, 7],
                                    color: '#F77084'
                                }
                            ],
                            credits: {
                                enabled: false,
                            },
                            legend: {
                                enabled: true
                            }
                        }
                    })
                    //End referral trend
                }

                this.setState({
                    ReferralData: ResponseJson.data,
                    Total: ResponseJson.data.referral_list.total,
                    ReferralList: ResponseJson.data.referral_list.result
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }
    handleDateFilter = (date, dateType) => {
        this.setState({ [dateType]: date }, () => {
            if (this.state.from_date && this.state.to_date) {
                this.getReferralData()
            }
        })
    }
    handlePageChange(current_page) {
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            this.getReferralData();
        });
    }

    exportReferrals = () => {
        let { from_date, to_date } = this.state
        let tempFromDate = from_date ? moment(from_date).format('YYYY-MM-DD') : '';
        let tempToDate = to_date ? moment(to_date).format('YYYY-MM-DD') : '';
        var query_string = 'user_id=' + this.props.user_id + '&from_date=' + tempFromDate + '&to_date=' + tempToDate;
        let sessionKey = WSManager.getToken();
        query_string += "&Sessionkey" + "=" + sessionKey;

        
        
        window.open(NC.baseURL + 'adminapi/user/export_referral_data?' + query_string, '_blank');
    }

    render() {
        let { ReferralData, ReferralList, CURRENT_PAGE, PERPAGE, Total, from_date, to_date, ReferralTrendGraph } = this.state
        return (
            <div className="referrals referral-box-view">
                <Row>
                    <Col md={8}>
                        <div className="float-left mr-2">
                            <label className="filter-label">From Date</label>
                            <DatePicker
                                maxDate={new Date(to_date)}
                                className="filter-date mr-1"
                                showYearDropdown='true'
                                selected={from_date}
                                onChange={e => this.handleDateFilter(e, "from_date")}
                                placeholderText="From"
                                dateFormat='dd/MM/yyyy'
                            />
                            </div>
                        <div className="float-left">
                            <label className="filter-label">To Date</label>
                            <DatePicker
                                minDate={new Date(from_date)}
                                maxDate={new Date()}
                                className="filter-date"
                                showYearDropdown='true'
                                selected={to_date}
                                onChange={e => this.handleDateFilter(e, "to_date")}
                                placeholderText="To"
                                dateFormat='dd/MM/yyyy'
                            />
                        </div>
                    </Col>
                    <Col md={4}>
                        <div className="filter-right-box clearfix">
                            <div className="filter-export">
                                <i className="icon-export" onClick={e => this.exportReferrals()}></i>
                            </div>
                        </div>
                    </Col>
                </Row>
                <Row className="mt-4">
                    <Col md={6}>
                        <h3 className="h3-cls">Referral Details</h3>
                        <div className="ref-box">
                            <div className="ref-align clearfix">
                                <div className="main-div">
                                    <div className="inner-div xpt-0">
                                        <div className="ref-title">Direct Referrals</div>
                                        <div className="ref-count">{ReferralData.direct_referral ? ReferralData.direct_referral : '0'}
                                        </div>
                                    </div>
                                    <div className="inner-div border-right-0 xpt-0">
                                        <div className="ref-title">Secondary Referrals</div>
                                        <div className="ref-count">{ReferralData.secondary_referrals ? ReferralData.secondary_referrals : '0'}</div>
                                    </div>
                                </div>
                                <div className="main-div">
                                    <div className="inner-div">
                                        <div className="ref-title">Referral Cash Earned</div>
        <div className="ref-count">{HF.getCurrencyCode()}{' '}{ReferralData.referral_earned_cash ? ReferralData.referral_earned_cash : '0'}</div>
                                    </div>
                                    <div className="inner-div border-right-0">
                                        <div className="ref-title">Referral Bonus earned</div>
                                        <div className="ref-count">B {' '}{ReferralData.referral_bonus_earned ? ReferralData.referral_bonus_earned : '0'}</div>
                                    </div>
                                </div>
                                <div className="main-div">
                                    <div className="inner-div border-bottom-0">
                                        <div className="ref-title">Deposited by Referred Users</div>
        <div className="ref-count">{HF.getCurrencyCode()}{' '}
                                            {parseFloat(ReferralData.deposited_by_referred_users ? ReferralData.deposited_by_referred_users : '0').toFixed(2)}
                                        </div>
                                    </div>
                                    <div className="inner-div border-right-0 border-bottom-0">
                                        <div className="ref-title">Deposited by Secondary Referrals</div>
        <div className="ref-count">{HF.getCurrencyCode()} {' '}{ReferralData.deposited_by_secondary_referrals ? ReferralData.deposited_by_secondary_referrals : '0'}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Col>
                    <Col md={6}>
                        <h3 className="h3-cls">Referral Trend</h3>
                        <div className="trend-box trd-hgt">
                            
                            <HighchartsReact
                                highcharts={Highcharts}
                                options={ReferralTrendGraph}
                            />
                            
                        </div>
                    </Col>
                </Row>
                <Row>
                    <Col md={12} className="table-responsive common-table mt-4">
                        <Table>
                            <thead>
                                <tr>
                                    <th className="pl-5">Join Date</th>
                                    <th>Username</th>
                                    <th>Total Earning</th>
                                    <th>Deposit</th>
                                    <th>Secondary Referrals</th>
                                </tr>
                            </thead>
                            {Total > 0 ?
                                _.map(ReferralList, (item, idx) => {
                                    return (
                                        <tbody key={idx}>
                                            <tr>
                                                <td className="pl-5">
                                                    {/* {moment(WSManager.getUtcToLocal(item.created_date)).format('DD MMM YY')} */}
                                                    {HF.getFormatedDateTime(item.created_date, 'DD MMM YY')}
                                                </td>

                                                <td className="xtext-ellipsis xtext-click">{item.user_name}</td>
                                                
                                    <td>{HF.getCurrencyCode()}{' '} {item.total_user_real_cash}
                                                </td>
                                    <td>{HF.getCurrencyCode()}{' '}{item.deposit}</td>
                                                <td>{item.secondary_referral.direct_referral}</td>
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
                        {Total > PERPAGE && (
                            <div className="custom-pagination">
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

                    </Col>
                </Row>
            </div>
        )
    }
}



export default Referrals;