import React, { Component, Fragment } from "react";
import { Row, Col, Button, Table, Input } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Select from 'react-select';
import "react-datepicker/dist/react-datepicker.css";
import Pagination from "react-js-pagination";
import Loader from '../../components/Loader';
import Images from '../../components/images';
import LS from 'local-storage';
import HF, { _isUndefined, _isEmpty } from '../../helper/HelperFunction';
import SelectDate from "../../components/SelectDate";
import Moment from 'react-moment';
export default class UserMatchReport extends Component {
    constructor(props) {
        super(props)
        this.state = {
            TotalUser: 0,
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            startDate: '',
            endDate: '',
            FromDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
            ToDate: new Date(),
            UserReportList: [],
            Keyword: '',
            sortField: 'schedule_date',
            isDescOrder: false,
            SelectedLeague: '',
            LeagueList: [],
            TotalDeposit: '',
            AllSportsList: [],
            SelectedLSports: '',
            contestName: '',
            CollectionList: [],
            SelectedCollection: '',
            collectionType: 1,
            sumJoinRealAmount: '',
            sumJoinWinningAmount: '',
            sumJoinBonusAmount: '',
            posting: false,
            sportsId: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
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
        WSManager.Rest(NC.baseURL + NC.GET_ALL_COLLECTIONS_BY_LEAGUE, params).then(ResponseJson => {
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

    exportReport_Post = () => {

        const { Keyword, FromDate, ToDate, sortField, isDescOrder, SelectedLeague, SelectedCollection, sportsId } = this.state
        let params = {
            league_id: SelectedLeague.value,
            collection_master_id: SelectedCollection.value,
            sports_id: sportsId,
            csv: true,
            sort_order: isDescOrder ? "ASC" : 'DESC',
            sort_field: sortField,
            from_date: FromDate,
            to_date: ToDate,
            keyword: Keyword,
            report_type: "match_report"
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
        let { FromDate, ToDate, isDescOrder, sortField, sportsId } = this.state
        let tempFromDate = ''
        let tempToDate = ''
        let sOrder = isDescOrder ? "ASC" : 'DESC'
        if (FromDate != '' && ToDate != '') {
            tempFromDate = FromDate ? HF.getFormatedDateTime(FromDate, 'YYYY-MM-DD') : '';
            tempToDate = ToDate ? HF.getFormatedDateTime(ToDate, 'YYYY-MM-DD') : '';
        }

        var query_string = 'csv=true&from_date=' + tempFromDate + '&to_date=' + tempToDate + '&sort_order=' + sOrder + '&sort_field=' + sortField + '&sports_id=' + sportsId;

        var export_url = 'adminapi/index.php/report/get_match_report?';

        HF.exportFunction(query_string, export_url)
    }

    getAllSports = () => {
        const Temp = HF.getSportsData() ? HF.getSportsData() : []
        this.setState({
            AllSportsList: Temp,
            SelectedLSports: Temp[0].label
        }, () => {
            this.handleTypeChange(Temp[0], "SelectedLSports")
        })
    }
    getLeagueFilter = () => {
        const { sportsId } = this.state
        let params = {
            sports_id: sportsId
        }
        WSManager.Rest(NC.baseURL + NC.GET_SPORT_LEAGUES_REPORT, params).then(ResponseJson => {

            if (ResponseJson.response_code == NC.successCode) {

                const Temp = []
                _.map(ResponseJson.data.result, (item, idx) => {
                    Temp.push({
                        value: item.league_id, label: item.league_name
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
        const { PERPAGE, CURRENT_PAGE, Keyword, FromDate, ToDate, sortField, isDescOrder, sportsId, SelectedCollection, SelectedLeague } = this.state
        let params = {
            league_id: SelectedLeague.value,
            collection_master_id: SelectedCollection.value,
            sports_id: sportsId,
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
            sort_order: isDescOrder ? "ASC" : 'DESC',
            sort_field: sortField,
            csv: false,
            from_date: FromDate ? HF.getFormatedDateTime(FromDate, 'YYYY-MM-DD') : '',
            to_date: ToDate ? HF.getFormatedDateTime(ToDate, 'YYYY-MM-DD') : '',            
        }

        WSManager.Rest(NC.baseURL + NC.GET_MATCH_REPORT, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    posting: false,
                    UserReportList: ResponseJson.data.result ? ResponseJson.data.result : [],
                    TotalUser: ResponseJson.data.total,
                    TotalDeposit: ResponseJson.data.total_deposit,
                    sumJoinRealAmount: ResponseJson.data.sum_join_real_amount,
                    sumJoinWinningAmount: ResponseJson.data.sum_join_winning_amount,
                    sumJoinBonusAmount: ResponseJson.data.sum_join_bonus_amount,

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
                SelectedLeague: '',
                SelectedCollection: '',
            }, () => {
                this.getReportUser()
                this.getLeagueFilter()
            }
            )
        }
    }
    handleLeagueChange = (value, name) => {
        if (value != null)
            this.setState({
                [name]: value,
                SelectedCollection: '',
            }, () => {
                this.getAllCollections()
                this.getReportUser()
            }
            )
    }
    handleCollectionChange = (value, name) => {
        if (value != null)
            this.setState({ [name]: value }, this.getReportUser)
    }


    handleDate = (date, dateType) => {
        this.setState({ [dateType]: date }, () => {
            if (this.state.FromDate || this.state.ToDate) {
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
            FromDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
            ToDate: new Date(),
            Keyword: '',
            isDescOrder: true,
            sortField: 'first_name',
            SelectedLSports: '7',
            sportsId: '7',
            SelectedLeague: { value: 0, label: "Select League" },
            SelectedCollection: '',
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
        const { posting, UserReportList, CURRENT_PAGE, PERPAGE, TotalUser, Keyword, isDescOrder, SelectedLeague, LeagueList, AllSportsList, SelectedLSports, CollectionList, SelectedCollection, sumJoinRealAmount, sumJoinWinningAmount, sumJoinBonusAmount, FromDate, ToDate } = this.state
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
            max_date: new Date(),
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
                            <h1 className="h1-cls">Match Report</h1>
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
                                <div>
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
                                <div>
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
                                <SelectDate DateProps={FromDateProps} />
                            </Col>
                            <Col md={2}>
                                <label className="filter-label">Select To Date</label>
                                <SelectDate DateProps={ToDateProps} />
                            </Col>
                            <Col md={2}>
                                <div className="mt-4">
                                    <Button className="btn-secondary" onClick={() => this.clearFilter()}>Clear Filters</Button>
                                </div>
                            </Col>
                        </Row>
                        <Row className="mb-20 mt-5">
                            <Col md={12}>
                                <i className="export-list icon-export" 
                                onClick={e => (TotalUser > NC.EXPORT_REPORT_LIMIT) ? this.exportReport_Post() : this.exportReport_Get()}></i>

                            </Col>
                        </Row>
                        <Row>
                            <Col md={12} className="table-responsive common-table">
                                <Table>
                                    <thead>
                                        <tr>
                                            <th className="pointer" onClick={() => this.sortContest('schedule_date', isDescOrder)}>Date</th>
                                            <th className="pointer" onClick={() => this.sortContest('collection_name', isDescOrder)}>Match</th>
                                            <th>Total teams Entered<br />(Real Users)</th>
                                            <th>Total Entry Fee Real Money</th>
                                            <th>Total Platform fee<br />(Site Rake)</th>
                                            <th>Total Prize Pool</th>
                                            <th>Total Entry Fee<br />(Bonus)</th>
                                            <th>Total Private Contest<br />(Site Rake)</th>
                                            <th>Total Distribution<br />(Real)</th>
                                            <th>Total Distribution<br />(Bonus)</th>
                                            <th>Total Distribution<br />(Coin)</th>
                                            <th>Total Promo Code Discount</th>
                                            <th>Bots User Entry<br />(Real Money)</th>
                                            <th>Bot User winnning<br />(Real Money)</th>
                                            <th>Total Revenue</th>
                                            <th>Total<br />(Profit / Loss)</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    {
                                        (!_.isUndefined(UserReportList) && UserReportList.length > 0) ?
                                            <Fragment>
                                                {_.map(UserReportList, (item, idx) => {
                                                    return (
                                                        <tbody key={idx}>
                                                            <tr>
                                                                <td>
                                                                    <Moment date={WSManager.getUtcToLocal(item.schedule_date)} format="D-MMM-YYYY hh:mm A" />
                                                                </td>
                                                                <td>{!_isUndefined(item.match_name) ? item.match_name : '--'}</td>
                                                                <td>{!_isUndefined(item.real_user) ? item.real_user : '--'}</td>
                                                                <td>{!_isUndefined(item.entry_real) ? item.entry_real : '--'}</td>
                                                                
                                                                <td>{!_isUndefined(item.site_rake) ? item.site_rake : '--'}</td>
                                                                <td>{!_isUndefined(item.prize_pool) ? item.prize_pool : '--'}</td>                                                              
                                                                
                                                                <td>{!_isUndefined(item.entry_bonus) ? item.entry_bonus : '--'}</td>                                                              
                                                                <td>{!_isUndefined(item.site_rake_private) ? item.site_rake_private : '--'}</td>                                                              
                                                                <td>{!_isUndefined(item.prize_pool_real) ? item.prize_pool_real : '--'}</td>                                                              
                                                                <td>{!_isUndefined(item.prize_pool_bonus) ? item.prize_pool_bonus : '--'}</td>                                                              
                                                                <td>{!_isUndefined(item.prize_pool_coins) ? item.prize_pool_coins : '--'}</td>                                                              
                                                                <td>{!_isUndefined(item.promo_discount) ? item.promo_discount : '--'}</td>                                                              
                                                                <td>{!_isUndefined(item.bots_entry) ? item.bots_entry : '--'}</td>                                                              
                                                                <td>{!_isUndefined(item.bots_winning) ? item.bots_winning : '--'}</td>                                                              
                                                                <td>{!_isUndefined(item.revenue) ? item.revenue : '--'}</td>                                                              
                                                                <td>{!_isUndefined(item.profit) ? parseFloat(item.profit).toFixed(2) : '--'}</td>                                                                
                                                                <td>
                                                                    <a href={"/admin/#/report/contest_report?sp=" + SelectedLSports.value + "&spn=" + SelectedLSports.label + "&leg=" + SelectedLeague.value + "&legn=" + SelectedLeague.label + "&col=" + SelectedCollection.value + "&coln=" + SelectedCollection.label + "&frd=" + FromDate + "&tod=" + ToDate}>
                                                                        View Details
                                                                    </a>                                                                    
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    )
                                                })}
                                            </Fragment>
                                            :
                                            <tbody>
                                                <tr>
                                                    <td colSpan='22'>
                                                        {!posting ?
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