import React, { Component, Fragment } from "react";
import { Row, Col, Button, Table, Input } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Select from 'react-select';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import Pagination from "react-js-pagination";
import Loader from '../../components/Loader';
import moment from 'moment';
import { MomentDateComponent } from "../../components/CustomComponent";
import HF from '../../helper/HelperFunction';
export default class NetworkContestReport extends Component {
    constructor(props) {
        super(props)
        this.state = {
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            FromDate: HF.getFirstDateOfMonth(),
            ToDate: new Date(),
            UserReportList: [],
            sortField: 'season_scheduled_date',
            isDescOrder: true,
            SelectedLeague: { value: 0, label: "All" },
            LeagueList: [],
            AllSportsList: [],
            SelectedLSports: '',
            sportsId: NC.sportsId,
            CollectionList: [],
            SelectedCollection: '',
            collectionType: 1,
            posting: false,
        }
    }
    componentDidMount() {
        this.getReportUser()
        this.getAllSports()
    }

    getAllCollections = () => {
        const { collectionType, sportsId, SelectedLeague } = this.state
        let params = {
            collection_type: collectionType,
            league_id: SelectedLeague.value,
            sports_id: sportsId
        }
        WSManager.Rest(NC.baseURL + NC.GET_NW_COLLECTION_LIST, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                const Temp = []
                _.map(ResponseJson.data, (item, idx) => {
                    Temp.push({
                        value: item.collection_master_id, label: item.collection_name
                    })
                })
                this.setState({
                    CollectionList: Temp
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    // exportReport = () => {
    //     const { sportsId, SelectedCollection, SelectedLeague, FromDate, ToDate, sortField, isDescOrder } = this.state
    //     let params = {
    //         sports_id: sportsId,
    //         league_id: SelectedLeague.value,
    //         collection_master_id: SelectedCollection.value,
    //         sort_order: isDescOrder ? "ASC" : 'DESC',
    //         sort_field: sortField,
    //         from_date: FromDate ? moment(FromDate).format("DD-MM-YYYY") : '',
    //         to_date: ToDate ? moment(ToDate).format("DD-MM-YYYY") : '',
    //         report_type: "contest_report",
    //         is_from_client: 1
    //     }

    //     WSManager.Rest(NC.baseURL + NC.EXPORT_NW_CONTEST_REPORT, params).then(ResponseJson => {
    //         if (ResponseJson.response_code == NC.successCode) {
    //             notify.show(ResponseJson.message, "success", 5000);
    //         } else {
    //             notify.show(NC.SYSTEM_ERROR, "error", 3000)
    //         }
    //     }).catch(error => {
    //         notify.show(NC.SYSTEM_ERROR, "error", 3000)
    //     })
    // }

    exportReport = () => {
        let { FromDate, ToDate, isDescOrder, sortField, sportsId, SelectedCollection, SelectedLeague } = this.state
        let tempFromDate = ''
        let tempToDate = ''
        let sOrder = isDescOrder ? "ASC" : 'DESC'
        if (FromDate != '' && ToDate != '') {
            tempFromDate = FromDate ? HF.getFormatedDateTime(FromDate, 'YYYY-MM-DD') : '';
            tempToDate = ToDate ? HF.getFormatedDateTime(ToDate, 'YYYY-MM-DD') : '';
        }
        var query_string = '&is_from_client=1&report_type=contest_report&sports_id=' + sportsId + '&from_date=' + tempFromDate + '&to_date=' + tempToDate + '&sort_order=' + sOrder + '&sort_field=' + sortField + '&league_id=' + SelectedLeague.value + '&collection_master_id=' + SelectedCollection.value;

        var export_url = 'adminapi/nw_contest/export_nw_contest_report?';

        HF.exportFunction(query_string, export_url)
    }

    getAllSports = () => {
        let params = {}
        WSManager.Rest(NC.baseURL + NC.GET_ALL_SPORTS, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                const Temp = []
                _.map(ResponseJson.data, (item, idx) => {
                    Temp.push({
                        value: item.sports_id, label: item.sports_name
                    })
                })
                console.log(Temp, "TempTemp")
                this.setState({
                    AllSportsList: Temp,
                    SelectedLSports: Temp[0].label
                }, () => {
                    this.handleTypeChange(Temp[0], "SelectedLSports")
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    getLeagueFilter = () => {
        const { sportsId } = this.state
        let params = {
            sports_id: sportsId
        }
        WSManager.Rest(NC.baseURL + NC.GET_NW_CONTEST_REPORT_FILTERS, params).then(ResponseJson => {

            if (ResponseJson.response_code == NC.successCode) {
                const Temp = []
                Temp.push({
                    value: 0, label: "All"
                })
                _.map(ResponseJson.data.result, (item, idx) => {
                    Temp.push({
                        value: item.league_id, label: item.league_abbr
                    })
                })
                this.setState({
                    LeagueList: Temp
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    getReportUser = () => {
        this.setState({ posting: true })
        const { SelectedCollection, SelectedLeague, PERPAGE, CURRENT_PAGE, FromDate, ToDate, sortField, isDescOrder, sportsId } = this.state
        let params = {
            sports_id: sportsId,
            league_id: SelectedLeague.value,
            collection_master_id: SelectedCollection.value,
            items_perpage: PERPAGE,
            total_items: 0,
            current_page: CURRENT_PAGE,
            sort_order: isDescOrder ? "ASC" : 'DESC',
            sort_field: sortField,
            from_date: FromDate ? moment(FromDate).format("DD-MM-YYYY") : '',
            to_date: ToDate ? moment(ToDate).format("DD-MM-YYYY") : '',
        }
        WSManager.Rest(NC.baseURL + NC.GET_ALL_NW_CONTEST_REPORT, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    posting: false,
                    UserReportList: ResponseJson.data.result,
                    TotalUser: ResponseJson.data.total,
                }, () => {
                    console.log("TotalUser==", this.state.TotalUser);
                    console.log("posting==", this.state.posting);

                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    handleTypeChange = (value, name) => {
        if (value != null) {
            this.setState({
                [name]: value,
                sportsId: value.value,
            }, () => {
                this.getReportUser()
                this.getLeagueFilter()
            })
        }
    }

    handleLeagueChange = (value, name) => {
        if (value != null)
            this.setState({ [name]: value }, () => {
                this.getReportUser()
                this.getAllCollections()
            })
    }

    handleCollectionChange = (value, name) => {
        if (value != null)
            this.setState({ [name]: value }, this.getReportUser)
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

    clearFilter = () => {
        this.setState({
            FromDate: HF.getFirstDateOfMonth(),
            ToDate: new Date(),
            isDescOrder: true,
            SelectedCollection: '',
            SelectedLeague: ''
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

    render() {
        const { posting, UserReportList, CURRENT_PAGE, PERPAGE, TotalUser, Keyword, isDescOrder, SelectedLeague, LeagueList, AllSportsList, SelectedLSports, CollectionList, SelectedCollection } = this.state
        return (
            <Fragment>
                <div className="animated fadeIn promocode-view mt-4">
                    <Row>
                        <Col md={12}>
                            <h1 className="h1-cls">Contest Report</h1>
                        </Col>
                    </Row>
                    <div className="user-deposit-amount">

                        <Row className="xfilter-userlist mt-5">
                            <Col md={2}>
                                <div>
                                    <label className="filter-label">Select Sport</label>
                                    <Select
                                        isSearchable={true}
                                        class="form-control"
                                        options={AllSportsList}
                                        menuIsOpen={true}
                                        value={SelectedLSports}
                                        onChange={e => this.handleTypeChange(e, 'SelectedLSports')}
                                    />
                                </div>
                            </Col>
                            <Col md={2}>
                                <div className="nw-coll-filter">
                                    <label className="filter-label">Select League</label>
                                    <Select
                                        isSearchable={true}
                                        class="form-control"
                                        options={LeagueList}
                                        menuIsOpen={true}
                                        value={SelectedLeague}
                                        onChange={e => this.handleLeagueChange(e, 'SelectedLeague')}
                                    />
                                </div>
                            </Col>
                            <Col md={2}>
                                <div className="nw-coll-filter">
                                    <label className="filter-label">Select Collection</label>
                                    <Select
                                        isSearchable={true}
                                        class="form-control"
                                        options={CollectionList}
                                        menuIsOpen={true}
                                        value={SelectedCollection}
                                        onChange={e => this.handleCollectionChange(e, 'SelectedCollection')}
                                    />
                                </div>
                            </Col>
                            <Col md={2}>
                                <label className="filter-label">Select From Date</label>
                                <DatePicker
                                    maxDate={new Date()}
                                    className="form-control"
                                    showYearDropdown='true'
                                    selected={this.state.FromDate}
                                    onChange={e => this.handleDateFilter(e, "FromDate")}
                                    placeholderText="From"
                                    dateFormat='dd/MM/yyyy'
                                />
                            </Col>
                            <Col md={2}>
                                <label className="filter-label">Select To Date</label>
                                <DatePicker
                                    minDate={this.state.FromDate}
                                    maxDate={new Date()}
                                    className="form-control"
                                    showYearDropdown='true'
                                    selected={this.state.ToDate}
                                    onChange={e => this.handleDateFilter(e, "ToDate")}
                                    placeholderText="To"
                                    dateFormat='dd/MM/yyyy'
                                />
                            </Col>
                            <Col md={2}>
                                <div className="mt-4">
                                    <Button className="btn-secondary" onClick={() => this.clearFilter()}>Clear Filters</Button>
                                </div>
                            </Col>
                        </Row>
                        <Row className="filters-box mt-3">
                            <Col md={12} className="float-right">
                                <i className="export-list icon-export" onClick={e => this.exportReport()}></i>
                            </Col>
                        </Row>
                        <Row>
                            <Col md={12} className="table-responsive common-table">
                                <Table>
                                    <thead>
                                        <tr>
                                            <th className="pointer" onClick={() => this.sortContest('contest_name', isDescOrder)}>Contest Name</th>
                                            <th className="pointer" onClick={() => this.sortContest('collection_name', isDescOrder)}>Collection Name</th>
                                            <th className="pointer" onClick={() => this.sortContest('size', isDescOrder)}>Total Join</th>
                                            <th className="pointer" onClick={() => this.sortContest('minimum_size', isDescOrder)}>Min</th>
                                            <th className="pointer" onClick={() => this.sortContest('maximum_size', isDescOrder)}>Max</th>
                                            <th className="pointer" onClick={() => this.sortContest('entry_fee', isDescOrder)}>Entry Fees</th>
                                            <th className="pointer" onClick={() => this.sortContest('site_rake', isDescOrder)}>Site Rake</th>
                                            <th className="pointer" onClick={() => this.sortContest('prize_pool', isDescOrder)}>Prize Pool</th>
                                            <th>Total Entry Fee</th>
                                            <th>Entry Fees(Real Money)</th>
                                            <th>Entry Fees(Winning)</th>
                                            {/* <th>Entry Fees(Bonus)</th> */}
                                            <th>Total Win Prize</th>
                                            <th>Total (Profit/Loss)</th>
                                            <th className="pointer" onClick={() => this.sortContest('season_scheduled_date', isDescOrder)}>Start Time </th>
                                        </tr>
                                    </thead>
                                    {
                                        (!_.isUndefined(UserReportList) && UserReportList.length > 0) ?
                                            <tbody>
                                                {_.map(UserReportList, (item, idx) => {
                                                    return (
                                                        <tr key={idx}>
                                                            <td>{item.contest_name}</td>
                                                            <td>{item.collection_name}</td>
                                                            <td>{item.total_user_joined}</td>
                                                            <td>{item.minimum_size}</td>
                                                            <td>{item.size}</td>
                                                            <td>{item.entry_fee}</td>
                                                            <td>{item.site_rake}</td>
                                                            <td>{item.prize_pool}</td>
                                                            <td>{item.total_entry_fee}</td>
                                                            <td>{item.total_join_real_amount}</td>
                                                            <td>{item.site_rake}</td>
                                                            {/* <td>{item.total_join_bonus_amount}</td> */}
                                                            <td>{item.total_win_winning_amount}</td>
                                                            <td>{item.profit_loss}</td>
                                                            <td>
                                                                {/* <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                                                            {HF.getFormatedDateTime(item.season_scheduled_date, "D-MMM-YYYY hh:mm A")}

                                                            </td>
                                                        </tr>
                                                    )
                                                })}

                                            </tbody>
                                            :
                                            <tbody>
                                                <tr>
                                                    <td colSpan='22'>
                                                        {((_.isUndefined(TotalUser) || TotalUser == 0) && !posting) ?
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
