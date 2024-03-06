import React, { Component, Fragment } from "react";
import { Row, Col, Table, Input } from 'reactstrap';
import HF, { _isUndefined, _isEmpty, _times, _Map, _isNull } from "../../helper/HelperFunction";
import Images from "../../components/images";
import { QZ_get_leaderboard_by_category } from "../../helper/WSCalling";
import * as NC from "../../helper/NetworkingConstants";
import { notify } from 'react-notify-toast';
import Loader from '../../components/Loader';
import CommonPagination from '../../components/CommonPagination';
class QuizSpinWheelLrdBrd extends Component {
    constructor(props) {
        super(props)
        this.state = {
            CURRENT_PAGE: 1,
            PERPAGE: !_isUndefined(this.props.per_page) ? this.props.per_page : NC.ITEMS_PERPAGE,
            FromDate: !_isUndefined(this.props.from_date) ? this.props.from_date : this.props.match ? this.props.match.params.fdate : HF.getFirstDateOfMonth(),
            ToDate: !_isUndefined(this.props.to_date) ? this.props.to_date : this.props.match ? this.props.match.params.tdate : new Date(),
            sortField: this.props.match ? this.props.match.params.filter : 'coins',
            isDescOrder: 'true',
            ListPosting: false,
            Keyword: '',
            CallFlag: _isUndefined(this.props.per_page) ? true : false,
            SelectedGainer: this.props.match ? this.props.match.params.filter : this.props.selected_gainer,
        }
    }

    componentDidMount = () => {
        this.getUserList()
    }

    getUserList = () => {
        this.setState({ ListPosting: true })
        let { PERPAGE, CURRENT_PAGE, Keyword, SelectedGainer, FromDate, ToDate, sortField, isDescOrder } = this.state
        let params = {
            "from_date": HF.getDateFormat(FromDate, 'YYYY-MM-DD'),
            "to_date": HF.getDateFormat(ToDate, 'YYYY-MM-DD'),
            "filter_by": SelectedGainer,
            "items_perpage": PERPAGE,
            "current_page": CURRENT_PAGE,
            "keyword": Keyword,
            // "sort_field": sortField,
            // "sort_order": isDescOrder ? 'DESC' : 'ASC',
        }

        QZ_get_leaderboard_by_category(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    UserList: ResponseJson.data ? ResponseJson.data.result : [],
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

    sortByColumn(sortfiled, isDescOrder) {
        let Order = isDescOrder ? false : true
        this.setState({
            sortField: sortfiled,
            isDescOrder: Order,
            CURRENT_PAGE: 1,

        }, this.getUserList)
    }

    handleSearch(e) {
        if (!_isNull(e)) {
            this.setState({
                CURRENT_PAGE: 1,
                Keyword: e.target.value,
                Total: 0,
            }, () => {
                this.getUserList()
            })
        }
    }

    handlePageChange(current_page) {
        if (this.state.CURRENT_PAGE != current_page) {
            this.setState({
                CURRENT_PAGE: current_page,
                Total: 0,
            }, () => {
                this.getUserList();
            });
        }
    }

    render() {
        let { sortField, isDescOrder, UserList, CallFlag, Total, ListPosting, CURRENT_PAGE, PERPAGE, SelectedGainer } = this.state
        const pagination_props = {
            current_page: CURRENT_PAGE,
            per_page: PERPAGE,
            total: Total,
            page_range_displayed: 5,
            handle_page_change: (cpage) => this.handlePageChange(cpage),
        }
        return (
            <Fragment>
                <div className={`qz-u-pagination ${CallFlag ? ' qzAllLrdBrd' : ''}`}>
                    {
                        CallFlag &&
                        <Row className="mt-0 mb-20">
                            <Col md={12}>
                                <label className="back-btn" onClick={() => this.props.history.push('/coins/reports?tab=1')}>
                                    {'<'} Back to Reports
                            </label>
                            </Col>
                        </Row>
                    }
                    <Row>
                        <Col md={12}>
                            <div className="pre-heading mt-0 float-left">
                                Leaderboard
                            </div>
                            <div className="search-input float-right">
                                <Input
                                    name="search-user"
                                    id="search-user"
                                    className="search-input"
                                    placeholder="Search"
                                    onChange={(e) => this.handleSearch(e)}
                                />
                            </div>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}
                            className={`table-responsive common-table ${CallFlag ? ' mt-30' : ''}`} >
                            <Table>
                                <thead className="height-40">
                                    <tr>
                                        <th className="text-left pl-5">Rank</th>
                                        <th>User Name</th>
                                        <th>Spins</th>
                                        <th
                                            className="cursor-pointer"
                                            //onClick={() => this.sortByColumn('coins', isDescOrder)}
                                        >
                                            Coins
                                            {/* <div className={`d-inline-block ${(sortField === 'coins' && isDescOrder) ? '' : 'rotate-icon'}`}>
                                                <i className="icon-Shape ml-1"></i>
                                            </div> */}
                                        </th>
                                        <th
                                            className="cursor-pointer"
                                            //onClick={() => this.sortByColumn('bonus', isDescOrder)}
                                        >
                                            Bonus
                                            {/* <div className={`d-inline-block ${(sortField === 'bonus' && isDescOrder) ? '' : 'rotate-icon'}`}>
                                                <i className="icon-Shape ml-1"></i>
                                            </div> */}
                                        </th>
                                        <th
                                            className="cursor-pointer"
                                            //onClick={() => this.sortByColumn('cash', isDescOrder)}
                                        >
                                            Cash
                                            {/* <div className={`d-inline-block ${(sortField === 'cash' && isDescOrder) ? '' : 'rotate-icon'}`}>
                                                <i className="icon-Shape ml-1"></i>
                                            </div> */}
                                        </th>
                                    </tr>
                                </thead>
                                {
                                    Total > 0 ?
                                        _Map(UserList, (item, idx) => {
                                            return (
                                                <tbody key={idx}>
                                                    <tr>
                                                        <td className="text-left pl-5">
                                                            {item.rank_value ? item.rank_value : '--'}
                                                        </td>
                                                        <td className="font-weight-bold text-click">
                                                            <a href={"/admin/#/profile/" + item.user_unique_id + '?tab=pers'}>
                                                                {item.user_name ? item.user_name : '--'}
                                                            </a>
                                                        </td>
                                                        <td className="font-weight-bold">
                                                            {item.spins ? item.spins : '--'}
                                                        </td>
                                                        <td><img src={Images.REWARD_ICON} alt="" className="mr-2" />
                                                            {item.coins ? item.coins : '--'}
                                                        </td>
                                                        <td><i className="icon-bonus"></i>
                                                            {item.bonus ? item.bonus : '--'}
                                                        </td>
                                                        <td className="font-weight-bold">
                                                            <span className="qz-rs">{HF.getCurrencyCode()}</span>
                                                            {item.cash ? item.cash : '--'}
                                                        </td>
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
                        !CallFlag &&
                        <Row>
                            <Col md={12}>
                                <a
                                    href={"/admin/#/quiz/spinwheel-user/" + SelectedGainer + '/' + this.state.FromDate + '/' + this.state.ToDate}
                                    className="view-all float-right"
                                >View More</a>
                            </Col>
                        </Row>
                    }
                    {
                        CallFlag &&
                        <CommonPagination {...pagination_props} />
                    }
                </div>
            </Fragment>
        )
    }
}
export default QuizSpinWheelLrdBrd

