import React, { Component } from "react";
import { Row, Col, Table } from 'reactstrap';
import HF, { _remove, _Map, _debounce, _isEmpty, _isUndefined, _isNull } from "../../helper/HelperFunction";
import Loader from '../../components/Loader';
import * as NC from "../../helper/NetworkingConstants";
import { notify } from 'react-notify-toast';
import { SF_FixtureStats } from '../../helper/WSCalling';
import Images from '../../components/images';
import Pagination from "react-js-pagination";
class SF_NseStats extends Component {
    constructor(props) {
        super(props)
        this.state = {
            collection_id: (this.props.match.params.collection_id) ? this.props.match.params.collection_id : '1',
            ActiveFxType: (this.props.match.params.category) ? this.props.match.params.category : '1',
            ActiveTab: (this.props.match.params.activeTab) ? this.props.match.params.activeTab : '1',
            StockList: [],
            ListPosting: true,
        }

    }

    componentDidMount() {
        if (HF.allowStockFantasy() == '0') {
            notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        this.getStockList();
    }

    getStockList = () => {
        this.setState({ ListPosting: true })
        let params = {
            collection_id: this.state.collection_id,
        }

        SF_FixtureStats(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    StockList: ResponseJson.data ? ResponseJson.data : [],
                    Total: ResponseJson.data ? ResponseJson.data.length : 0,
                    ListPosting: false,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    render() {
        let { StockList, ListPosting, Total, ActiveFxType, ActiveTab } = this.state
        return (
            <div className="manage-stocks sf-nse-stats">
                <Row className="mt-30">
                    <Col md={12}>
                        <div className="sf-b-btm">
                            <h2 className="h2-cls">NSE Stats</h2>
                        {/* </Col>
                        <Col md={6}> */}
                            <label className="back-to-fixtures" onClick={() => this.props.history.push('/stockfantasy/fixture?pctab=' + ActiveFxType + '&tab=' + ActiveTab)}> {'<'} Back to Fixtures</label>
                        </div>
                    </Col>
                </Row>
                <hr className="m-0" />
                <Row className="mt-30">
                    <Col md={12} className="table-responsive common-table">
                        <Table>
                            <thead className="height-40">
                                <tr>
                                    <th>Stock Name</th>
                                    <th>Trading symbol</th>
                                    <th>Display Name</th>
                                    {/* <th>Token</th> */}
                                    <th>Logo</th>
                                    <th>Closing rate</th>
                                    <th>{ActiveTab == '0' ? 'Result rate' : ActiveTab > '1' ? 'Joining rate' : ''}</th>
                                </tr>
                            </thead>
                            {
                                (Total > 0) ?
                                    _Map(StockList, (item, idx) => {
                                        return (
                                            <tbody key={idx}>
                                                <tr>
                                                    <td>{item.name}</td>
                                                    <td>{item.trading_symbol}</td>
                                                    <td>{item.display_name}</td>
                                                    {/* <td>{item.exchange_token}</td> */}
                                                    <td>
                                                        <div className="s-logo">
                                                            <img src={item.logo ? NC.S3 + NC.STOCK_PATH + item.logo : Images.no_image} className="img-cover" alt="" />
                                                        </div>
                                                    </td>
                                                    <td>{ActiveTab == '0' ? HF.convertTodecimal(item.closing_rate, 2) : ActiveTab > '1' ? HF.convertTodecimal(item.result_rate, 2) : '--'}</td>
                                                    <td>{ActiveTab == '0' ? HF.convertTodecimal(item.result_rate, 2) : ActiveTab > '1' ? HF.convertTodecimal(item.closing_rate, 2) : '--'}</td>
                                                </tr>
                                            </tbody>
                                        )
                                    })
                                    :
                                    <tbody>
                                        <tr>
                                            <td colSpan='22'>
                                                {
                                                    (Total == 0 && !ListPosting) ?
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
            </div>
        )
    }
}
export default SF_NseStats

