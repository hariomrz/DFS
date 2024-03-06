import React, { Component } from "react";
import { Row, Col, Table } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Pagination from "react-js-pagination";
import _ from 'lodash';
import HF, { _remove, _isEmpty } from "../../helper/HelperFunction";
import { XP_LEV_ENDP_EMPTY, XP_LEV_ENDP, XP_LEV_ENDP_G } from "../../helper/Message"
import Loader from '../../components/Loader';
import moment from 'moment';
import { ROOK_getRookieUserList } from "../../helper/WSCalling"
class AllRookieUser extends Component {
    constructor(props) {
        super(props)
        this.state = {
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            RookieList: [],
            ListPosting: false,            
        };
    }
    componentDidMount() {
        if (HF.allowRookieContest() == '0') {
            notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        this.getRookieList();
    }

    getRookieList = () => {
        this.setState({ ListPosting: true })
        const { PERPAGE, CURRENT_PAGE, isDescOrder, sortField } = this.state
        let params = {
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
        }

        ROOK_getRookieUserList(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    RookieList: ResponseJson.data ? ResponseJson.data.result : [],
                    Total: ResponseJson.data.total ? ResponseJson.data.total : 0,
                    ListPosting: false,
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
                CURRENT_PAGE: current_page,
                ListPosting: true,
                Total: 0,
            }, () => {
                this.getRookieList();
            });
        }
    }

    render() {
        let { RookieList, CURRENT_PAGE, PERPAGE, Total, ListPosting } = this.state
        return (
            <div className="view-rookie animate-left">
                <Row>
                    <Col md={12}>
                        <h2 className="h2-cls">Rookies</h2>
                        <label className="back-btn" onClick={() => this.props.history.push('/user_management/viewrookie')}> {'<'} Back to Rookie Dashboard</label>
                    </Col>
                </Row>
                <Row>
                    <Col md={12} className="table-responsive common-table">
                        <Table>
                            <thead className="height-40">
                                <tr>
                                    <th className="text-left pl-5">Username</th>
                                    <th>Member Since</th>
                                    <th>Winnings ({HF.getCurrencyCode()})</th>
                                    <th>Paid Contest</th>
                                    <th>Free Contest</th>
                                </tr>
                            </thead>
                            {
                                Total > 0 ?
                                    _.map(RookieList, (item, idx) => {
                                        return (
                                            <tbody key={idx}>
                                                <tr>
                                                    <td className="text-click text-left pl-5">
                                                        <a href={"/admin/#/profile/" + item.user_unique_id + '?tab=pers'}>
                                                            {item.user_name ? item.user_name : '--'}
                                                        </a>
                                                    </td>
                                                    <td>{HF.getFormatedDateTime(item.added_date, 'DD MMM YYYY')}</td>
                                                    <td>{item.winnings}</td>
                                                    <td>{item.paid_contests}</td>
                                                    <td>{item.free_contests}</td>
                                                </tr>
                                            </tbody>
                                        )
                                    })
                                    :
                                    <tbody>
                                        <tr>
                                            <td colSpan="8">
                                                {(Total == 0 && !ListPosting) ?
                                                    <div className="no-records">
                                                        {NC.NO_RECORDS}</div>
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
                {
                    Total > PERPAGE &&
                    <div className="custom-pagination lobby-paging">
                        <Pagination
                            activePage={CURRENT_PAGE}
                            itemsCountPerPage={PERPAGE}
                            totalItemsCount={Total}
                            pageRangeDisplayed={5}
                            onChange={e => this.handlePageChange(e)}
                        />
                    </div>
                }
            </div>
        )
    }
}
export default AllRookieUser







