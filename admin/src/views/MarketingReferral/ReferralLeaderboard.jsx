import React, { Component, Fragment } from "react";
import { Row, Col, TabContent, TabPane, Nav, NavItem, NavLink, Table } from 'reactstrap';
import Pagination from "react-js-pagination";
import * as NC from "../../helper/NetworkingConstants";
import _ from 'lodash';
import Select from 'react-select';
import WSManager from '../../helper/WSManager';
import { notify } from 'react-notify-toast';
import Images from '../../components/images';
import HF from '../../helper/HelperFunction';
import { MODULE_NOT_ENABLE } from "../../helper/Message";
class ReferralLeaderboard extends Component {
    constructor(props) {
        super(props)
        this.state = {
            activeTab: '1',
            CURRENT_PAGE: 1,
            PERPAGE: NC.ITEMS_PERPAGE,
            Filter: 0,
            Total: 0,
            FilterList: [],
        }
    }

    componentDidMount() {
        if (HF.allowRefLeaderboard() != '1') {
            notify.show(MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        this.getLeaderboardMasterData()
        this.getOpenPredictorLeaderboard()
    }

    toggle(tab) {
        this.setState({ activeTab: tab, Filter: 0 }, this.getOpenPredictorLeaderboard)
    }

    handleFilter = (e) => {
        if (e) {
            this.setState({ Filter: e.value }, this.getOpenPredictorLeaderboard)
        }
    }

    getLeaderboardMasterData = () => {
        WSManager.Rest(NC.baseURL + NC.GET_REFERRAL_LEADERBOARD_MASTER_DATA, {}).then(Response => {
            if (Response.response_code == NC.successCode) {
                let ResponseData = Response.data

                Object.keys(ResponseData).map(function (key) {
                    _.map(ResponseData[key], (item) => {
                        item.value = item.from_date
                    })
                });

                this.setState({
                    FilterList: ResponseData
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    getOpenPredictorLeaderboard = () => {
        let { activeTab, Filter, CURRENT_PAGE, PERPAGE } = this.state
        let inputKey = ''
        if (activeTab == '1' && Filter == 0)
            inputKey = 'today'
        else if (activeTab == '2' && Filter == 0)
            inputKey = 'this_week'
        else if (activeTab == '3' && Filter == 0)
            inputKey = 'this_month'
        else if (activeTab == '1' && !_.isEmpty(Filter))
            inputKey = 'day_date'
        else if (activeTab == '2' && !_.isEmpty(Filter))
            inputKey = 'week_date'
        else if (activeTab == '3' && !_.isEmpty(Filter))
            inputKey = 'month_date'

        let params = {
            "filter": inputKey,//month_date,week_date,day_date,today,this_week,this_month
            "filter_date": Filter,//in case of week_date,day_date,this date
            current_page: CURRENT_PAGE,
            items_perpage: PERPAGE
        }

        WSManager.Rest(NC.baseURL + NC.GET_REFERRAL_LEADERBOARD, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    UsersList: Response.data.result,
                    Total: Response.data.total
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    handlePageChange(current_page) {
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            this.getOpenPredictorLeaderboard()
        });
    }

    renderUserData = () => {
        let { UsersList, PERPAGE, CURRENT_PAGE, Total } = this.state
        return (
            <Fragment>
                <Row>
                    <Col md={12}>
                        <div className="table-responsive common-table op-leaderboard">
                            <div className="tbl-min-hgt">
                                <Table>
                                    <thead>
                                        <tr>
                                            <th className="pl-4">Rank</th>
                                            <th>Username</th>
                                            <th>Prize</th>
                                            <th>Referral</th>
                                        </tr>
                                    </thead>
                                    {
                                        Total > 0 ?
                                            _.map(UsersList, (item, idx) => {
                                                return (
                                                    <tbody key={idx}>
                                                        <tr>
                                                            <td className="pl-4">{item.rank_value}</td>
                                                            <td>{item.user_name}</td>
                                                            <td>
                                                                {
                                                                    item.prize_data != null ?
                                                                        item.prize_data[0].prize_type == 0 ?
                                                                            <i className="icon-bonus1 mr-1"></i>
                                                                            :
                                                                            item.prize_data[0].prize_type == 1 ?
                                                                                <i className="icon-rupess mr-1"></i>
                                                                                :
                                                                                item.prize_data[0].prize_type == 2 ?
                                                                                    <img className="mr-1" src={Images.REWARD_ICON} alt="" />
                                                                                    :
                                                                                    ''
                                                                        :
                                                                        ''
                                                                }
                                                                {item.prize_data != null ?
                                                                    item.prize_data[0].prize_type == 3
                                                                        ?
                                                                        item.prize_data[0].name
                                                                        :
                                                                        item.prize_data[0].amount
                                                                    :
                                                                    '--'
                                                                }
                                                            </td>

                                                            <td className="pl-4 font-weight-bold">
                                                                {
                                                                    item.total_referral ? item.total_referral : 0
                                                                }
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                )
                                            })
                                            :
                                            <tbody>
                                                <tr>
                                                    <td colSpan="8">
                                                        <div className="no-records">
                                                            {NC.NO_RECORDS}</div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                    }
                                </Table>
                            </div>
                            {Total > PERPAGE && (
                                <div className="custom-pagination float-right mb-5">
                                    <Pagination
                                        activePage={CURRENT_PAGE}
                                        itemsCountPerPage={PERPAGE}
                                        totalItemsCount={Total}
                                        pageRangeDisplayed={5}
                                        onChange={e => this.handlePageChange(e)}
                                    />
                                </div>
                            )}
                        </div>
                    </Col>
                </Row>
            </Fragment>
        )
    }

    render() {
        let { Filter, activeTab, FilterData, FilterList } = this.state
        return (
            <Fragment>
                <Row>
                    <Col md={12}>
                        <div className="user-navigation">
                            <Row>
                                <Col md={12}>
                                    <Nav tabs>
                                        <NavItem
                                            className={activeTab === '1' ? "active" : ""}
                                            onClick={() => { this.toggle('1'); }}
                                        >
                                            <NavLink>
                                                Today
                                        </NavLink>
                                        </NavItem>
                                        <NavItem
                                            className={activeTab === '2' ? "active" : ""}
                                            onClick={() => { this.toggle('2'); }}
                                        >
                                            <NavLink>
                                                This Week
                                            </NavLink>
                                        </NavItem>
                                        <NavItem
                                            className={activeTab === '3' ? "active" : ""}
                                            onClick={() => { this.toggle('3'); }}
                                        >
                                            <NavLink>
                                                This Month
                                            </NavLink>
                                        </NavItem>
                                    </Nav>
                                </Col>
                            </Row>
                            <Row>
                                <Col md={3}>
                                    <div className="select-week">
                                        <label htmlFor="selectweek">
                                            Select {
                                                activeTab == '1' ? 'Day' : activeTab == '2' ? 'Week' : 'Month'
                                            }
                                        </label>
                                        <Select
                                            searchable={false}
                                            clearable={false}
                                            value={Filter}
                                            options={FilterList[activeTab == '1' ? 'day_filter' : activeTab == '2' ? 'week_filter' : 'month_filter']}
                                            onChange={(e) => this.handleFilter(e)}
                                        />
                                    </div>
                                </Col>
                            </Row>
                            <TabContent activeTab={activeTab} className="bg-white">
                                {
                                    (activeTab == '1') &&
                                    <TabPane tabId="1" className="animated fadeIn">
                                        {this.renderUserData('Day')}
                                    </TabPane>
                                }
                                {
                                    (activeTab == '2') &&
                                    <TabPane tabId="2" className="animated fadeIn">
                                        {this.renderUserData('Week')}
                                    </TabPane>
                                }
                                {
                                    (activeTab == '3') &&
                                    <TabPane tabId="3" className="animated fadeIn">
                                        {this.renderUserData('Month')}
                                    </TabPane>
                                }
                            </TabContent>
                        </div>
                    </Col>
                </Row>
            </Fragment>
        )
    }
}
export default ReferralLeaderboard
