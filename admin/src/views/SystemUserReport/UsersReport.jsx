import React, { Component, Fragment } from "react";
import { Row, Col, Table, Button } from 'reactstrap';
import _ from 'lodash';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import Select from 'react-select';
import Pagination from "react-js-pagination";
import moment from 'moment';
import queryString from 'query-string';
import Loader from "../../components/Loader";
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import HighGraph from "../../components/HighGraph";
import LS from 'local-storage';
import HF from "../../helper/HelperFunction";
class UsersReport extends Component {
    constructor(props) {
        super(props)
        let filter = {
            sports_id: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
            league_id: '',
            from_date: HF.getFirstDateOfMonth(),
            to_date: new Date(),
            current_page: 1,
            items_perpage: 10,
            csv: false,
        }
        let leageListFilter = {
            sports_id: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
            from_date: HF.getFirstDateOfMonth(),
            to_date: new Date(),
        }
        this.state = {
            filter: filter,
            options: [],
            leageListFilter: leageListFilter,
            posting: false,
            StartDate: '',
            EndDate: '',
            UserStatus: 1,
            userslist: [],
            userFullName: '',
            totalBalance: '',
            total: 0,
        };
        this.handleChange = this.handleChange.bind(this);
        this.handleChangeEnd = this.handleChangeEnd.bind(this);
    }

    componentDidMount() {
        let filter = this.state.filter;
        let url = this.props.location.search;
        let urlParams = queryString.parse(url);
        if (urlParams.pending == 1) {
            filter['pending_pan_approval'] = urlParams.pending;
            this.setState({ filter })
        }
        this.getUserList();
        this.getSystemUserLeagueList();
    }

    handlePageChange(current_page) {

        let filter = this.state.filter;

        filter['current_page'] = current_page;

        this.setState(
            { filter: filter },
            function () {
                this.getUserList();
            });

    }

    handleSelect(status) {
        if (status != null) {
            let filter = this.state.filter;
            filter['league_id'] = status.value;
            filter['current_page'] = 1;
            this.setState({
                filter: filter,
                UserStatus: status
            },
                function () {
                    this.getUserList();
                });
        }
    }

    handleChange(date) {
        let filter = this.state.filter;
        let leageListFilter = this.state.leageListFilter;


        filter['from_date'] = date;
        leageListFilter['from_date'] = date;
        filter['current_page'] = 1;
        this.setState(
            {
                filter: filter,
                userslist: [],
                posting: true,
            },
            function () {
                this.getUserList();
                this.getSystemUserLeagueList();
            });


    }

    handleChangeEnd(date) {
        let filter = this.state.filter;
        let leageListFilter = this.state.leageListFilter;
        filter['to_date'] = date;
        leageListFilter['to_date'] = date;
        filter['current_page'] = 1;
        this.setState({
            filter: filter,
            userslist: [],
            posting: true,
        },
            function () {
                this.getUserList();
                this.getSystemUserLeagueList();
            });
    }

    getSystemUserLeagueList = () => {
        this.setState({ posting: true })
        let { leageListFilter } = this.state

        let tempLF = leageListFilter
        tempLF.from_date = moment(tempLF.from_date).format("YYYY-MM-DD")
        tempLF.to_date = moment(tempLF.to_date).format("YYYY-MM-DD")
        let params = tempLF;

        WSManager.Rest(NC.baseURL + NC.GET_SYSTEM_USER_LEAGUE_LIST, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                let leagueArr = responseJson.data.result;
                let tempArr = [{ value: "", label: "All" }];

                leagueArr.map(function (lObj, lKey) {
                    tempArr.push({ value: lObj.league_id, label: lObj.league_display_name });
                });
                this.setState({ options: tempArr });
            }
            this.setState({ posting: false })
        })
    }

    getUserList = () => {
        this.setState({ posting: true })
        let { filter } = this.state

        let tempFilter = filter
        tempFilter.from_date = moment(tempFilter.from_date).format("YYYY-MM-DD")
        tempFilter.to_date = moment(tempFilter.to_date).format("YYYY-MM-DD")
        let params = tempFilter;

        WSManager.Rest(NC.baseURL + NC.GET_SYSTEM_USER_REPORTS, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                let result = responseJson.data.result;
                let total = responseJson.data.total;
                if (responseJson != null && responseJson.data != null && result != null) {
                    this.renderGraph(result)
                }
                this.setState({
                    posting: false,
                    userslist: result
                }, () => {
                    this.setState({
                        totalBalance: responseJson.data.balance
                    })
                })
                if (total > 0) {
                    this.setState({
                        total: total
                    })
                }
            }
            this.setState({ posting: false })
        })
    }

    renderGraph = (result) => {
        let xaxisValue = [];

        let graphValue = [];

        result.map(function (item, lKey) {
            let temp_val = item.collection_name + ' <br /> ' + moment(item.season_scheduled_date).format("DD MMM YYYY")
            xaxisValue.push(temp_val)

            if (Number.parseInt(item.net_profit) >= 0) {
                graphValue.push({ y: Number.parseInt(item.net_profit), color: '#5dbe7d' });
            }
            else {
                graphValue.push({ y: Number.parseInt(item.net_profit), color: '#d07f7f' });

            }
        });


        this.setState({
            HighGraphProgressConfigOption: {
                chart: {
                    backgroundColor: '#FFFFFF',
                    type: 'column',
                    height: 350
                },

                navigator: {
                    enabled: true
                },
                title: {
                    text: ''
                },
                xAxis: {
                    labels: {
                        style: {
                            fontWeight: 'bold',
                        }
                    },
                    title: {
                        text: ''
                    },
                    categories: xaxisValue
                },
                yAxis: {
                    lineWidth: 1,
                    title: {
                        text: ''
                    }
                },
                tooltip: {
                    backgroundColor: '#FFFFFF',
                    borderColor: 'tranparent',
                    borderRadius: 4,
                    borderWidth: 0,
                    textAlign: 'center'
                },
                plotOptions: {
                    series: {
                        pointWidth: 20,
                        stacking: 'normal',
                        borderRadius: 3,
                        showInLegend: false,
                    }
                },
                credits: {
                    enabled: false,
                },
                series: [{
                    // name: 'System User Reports',
                    name: '',
                    type: 'column',
                    data: graphValue,
                }]

            },

        })
    }

    clearFilter = () => {
        let { filter } = this.state
        let { leageListFilter } = this.state
        filter.from_date = new Date(Date.now() - 15 * 24 * 60 * 60 * 1000)
        filter.to_date = new Date()

        leageListFilter.from_date = new Date(Date.now() - 15 * 24 * 60 * 60 * 1000)
        leageListFilter.to_date = new Date()

        filter.league_id = ""
        this.setState({
            StartDate: '',
            EndDate: '',
            UserStatus: '',
            filter,
            leageListFilter,
            userslist: [],
            posting: true,
        }, () => {
            this.getUserList();
            this.getSystemUserLeagueList();
        })
    }

    exportReport_Get = () => {
        let { filter } = this.state
        let tempFromDate = ''
        let tempToDate = ''
        if (filter.from_date != '' && filter.to_date != '') {
            tempFromDate = filter.from_date ? HF.getFormatedDateTime(filter.from_date, 'YYYY-MM-DD') : '';
            tempToDate = filter.to_date ? HF.getFormatedDateTime(filter.to_date, 'YYYY-MM-DD') : '';
        }

        var query_string = '&action=1&csv=true&from_date=' + tempFromDate + '&to_date=' + tempToDate;

        var export_url = 'adminapi/systemuser/get_system_user_reports?';

        HF.exportFunction(query_string, export_url)
    }

    render() {
        let { filter, UserStatus, userslist, total, posting } = this.state
        return (
            <Fragment>
                <div className="bot-user-reports">
                    <div className="manage-user-heading clearfix">
                        <h2 className="h2-cls">
                            System User Reports</h2>
                        <div className="search-user">

                            <Button
                                className="add-bots btn-secondary-outline"
                                onClick={() => {
                                    this.props.history.push({ pathname: '/system-users/userslist/' })
                                }}>Add system user</Button>
                        </div>
                    </div>
                    <Row className="filter-userlist">
                        <Col md={9}>
                            <div className="member-box float-left">
                                <div className="float-left">
                                    <label className="filter-label">Start Date</label>
                                    <DatePicker
                                        maxDate={new Date(filter.to_date)}
                                        className="filter-date"
                                        showYearDropdown='true'
                                        selected={new Date(filter.from_date)}
                                        onChange={this.handleChange}
                                        placeholderText="From"
                                        dateFormat='dd/MM/yyyy'
                                    />
                                </div>
                                <div className="float-left">
                                    <label className="filter-label">End Date</label>
                                    <DatePicker
                                        minDate={new Date(filter.from_date)}
                                        maxDate={new Date()}
                                        className="filter-date"
                                        showYearDropdown='true'
                                        selected={new Date(filter.to_date)}
                                        onChange={this.handleChangeEnd}
                                        placeholderText="To"
                                        dateFormat='dd/MM/yyyy'
                                    />
                                </div>
                            </div>
                        </Col>
                        <Col md={3}>
                            <Button
                                className="add-bots btn-secondary-outline float-right mt-4"
                                onClick={this.clearFilter}
                            >Clear Filter</Button>
                        </Col>
                    </Row>
                    <div>

                        <div className="graph-container-user-report">
                            {
                                (this.state.HighGraphProgressConfigOption && !posting) ?
                                    <Fragment>
                                        <div>
                                            <span className="balance">Balance</span>
                                            <div className="league-dropdown">
                                                <Select
                                                    searchable={false}
                                                    clearable={false}
                                                    options={this.state.options}
                                                    value={UserStatus}
                                                    onChange={e => this.handleSelect(e)}
                                                />
                                            </div>
                                        </div>

                                        <div className={Number.parseFloat(this.state.totalBalance) >= 0 ? ' positive-balance' : ' negative-balance'} >{this.state.totalBalance}</div>


                                        <HighGraph
                                            {...this.props} HighGraphConfigOption={this.state.HighGraphProgressConfigOption}
                                        > </HighGraph>
                                    </Fragment>
                                    :
                                    <Loader />
                            }
                        </div>

                    </div>
                    <Row>
                        <Col md={12}>
                            <div className="float-left" style={{ margin: '10px', fontWeight: 'bold' }}>
                                Joined system user
                            </div>
                            <div className="float-right mt-4" style={{ margin: '10px' }}>
                                <i className="export-list icon-export"
                                    onClick={e => this.exportReport_Get()}></i>
                            </div>
                        </Col>
                    </Row>
                    <Row className="user-list">
                        <Col className="md-12 table-responsive">
                            <Table>
                                <thead>
                                    <tr>
                                        <th className="left-th pl-4 align-left">Fixture</th>
                                        <th className="align-left">Date & Time</th>
                                        <th>Total Users</th>
                                        <th>System Users</th>
                                        <th>Real Users</th>
                                        <th>Bonus loss</th>
                                        <th>Real amount collected</th>
                                        <th>Real User Winnings</th>
                                        <th>Site Rake Remaining</th>
                                        <th>Net profit/Loss</th>
                                        <th className="right-th">System user winnings</th>
                                    </tr>
                                </thead>
                                {
                                    userslist.length > 0 ?
                                        _.map(userslist, (item, idx) => {
                                            return (
                                                <tbody key={idx}>
                                                    <tr>
                                                        <td className="left-th align-left">
                                                            {item.collection_name ? item.collection_name : '--'}
                                                        </td>
                                                        <td className="align-left">
                                                            {
                                                                <div>
                                                                    <span className="mr-2">{
                                                                        moment(new Date(WSManager.getUtcToLocal(item.season_scheduled_date))).format("DD MMM YYYY, h:mm A")}
                                                                    </span>
                                                                </div>
                                                            }
                                                        </td>
                                                        <td>
                                                            {item.total_user_joined ? item.total_user_joined : '--'}
                                                        </td>
                                                        <td>
                                                            {item.total_system_user ? item.total_system_user : '--'}
                                                        </td>
                                                        <td>
                                                            {parseInt(item.total_user_joined) - parseInt(item.total_system_user)}
                                                        </td>
                                                        <td>{item.bonus_loss ? item.bonus_loss : '--'}</td>
                                                        <td>{item.real_amount ? item.real_amount : '--'}</td>
                                                        <td>{item.realuser_winnings ? item.realuser_winnings : '--'}</td>
                                                        <td>{item.site_rake}</td>
                                                        <td>
                                                            <div
                                                                className={Number.parseFloat(item.net_profit) >= 0 ? ' positive-value-color' : ' negative-value-color'} >
                                                                {Number.parseFloat(item.net_profit) >= 0 && '+'} {item.net_profit}
                                                            </div>
                                                        </td>
                                                        <td className="right-th">{item.systemuser_winnings}</td>
                                                    </tr>
                                                </tbody>
                                            )
                                        })
                                        :
                                        <tbody>
                                            <tr>
                                                <td colSpan="11">
                                                    {(userslist.length == 0 && !posting) ?
                                                        <div className="no-records">
                                                            No Record Found.</div>
                                                        :
                                                        <Loader />
                                                    }
                                                </td>
                                            </tr>
                                        </tbody>
                                }
                            </Table>
                            {
                                userslist.length != 0 &&
                                <div className="custom-pagination lobby-paging">
                                    <Pagination
                                        activePage={filter.current_page}
                                        itemsCountPerPage={filter.items_perpage}
                                        totalItemsCount={total}
                                        pageRangeDisplayed={5}
                                        onChange={e => this.handlePageChange(e)}
                                    />
                                </div>
                            }
                        </Col>
                    </Row>
                </div>
            </Fragment >
        )
    }
}
export default UsersReport