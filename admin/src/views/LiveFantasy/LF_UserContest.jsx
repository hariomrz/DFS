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
import Images from '../../components/images';
import LS from 'local-storage';
import HF, { _isUndefined, _isEmpty } from '../../helper/HelperFunction';
import SelectDate from "../../components/SelectDate";
import queryString from 'query-string';
import SelectDropdown from "../../components/SelectDropdown";
import moment from "moment-timezone";
export default class LF_UserContest extends Component {
    constructor(props) {
        super(props)
        this.state = {
            TotalUser: 0,
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            startDate: '',
            endDate: '',
            FromDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
            ToDate: new Date(moment().format('D MMM YYYY')),
            UserReportList: [],
            Keyword: '',
            sortField: 'first_name',
            isDescOrder: false,
            SelectedLeague: '',
            LeagueList: [],
            TotalDeposit: '',
            SelectedLSports: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
            sportsId: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
            contestName: '',
            CollectionList: [],
            SelectedCollection: '',
            SelectedGroup: '',
            collectionType: 1,
            TotalUserReport: [],
            posting: false,
            selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
            SelectedFeature : '',
            FeatureOptions : [],
        }
        this.SearchCodeReq = _.debounce(this.SearchCodeReq.bind(this), 500);
    }
    componentDidMount() {
        let values = queryString.parse(this.props.location.search)

        if (!_isUndefined(values.sp)) {
            this.setState({
                sportsId: values.sp,
                SelectedLSports: (values.sp != 'undefined') ? { "value": values.sp, "label": values.spn } : "",
                SelectedLeague: (values.leg != 'undefined') ? { "value": values.leg, "label": values.legn } : "",
                SelectedCollection: (values.col != 'undefined') ? { "value": values.col, "label": values.coln } : "",
                FromDate: new Date(values.frd),
                ToDate: new Date(values.tod),
            }, () => {
                this.apiCall()
            })
        } else {
            this.apiCall()
        }
    }

    apiCall = () => {
        this.getLeagueFilter()
        this.GetContestFilterData()
        this.getReportUser()        
    }

    GetContestFilterData = () => {
        this.setState({ posting: true })
        let params = {
            "sports_id": this.state.sportsId,
            "list_type": true
        };
        WSManager.Rest(NC.baseURL + NC.LF_GET_CONTEST_FILTER, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                responseJson = responseJson.data;

                let tempGroupList = [];
                if (responseJson.group_list) {
                    responseJson.group_list.map(function (lObj, lKey) {
                        tempGroupList.push({ value: lObj.group_id, label: lObj.group_name });
                    });
                }

                const TempFlist = responseJson.contest_type_list
                this.setState({ 
                    groupList: tempGroupList,
                    FeatureOptions: TempFlist
                 });
            }
            this.setState({ posting: false })
        })
    }

    getAllCollections = () => {
        const { collectionType, sportsId, SelectedLeague } = this.state
        let params = {
            collection_type: collectionType,
            league_id: SelectedLeague.value,
            sports_id: sportsId
        }
        WSManager.Rest(NC.baseURL + NC.LF_GET_ALL_COLLECTIONS_BY_LEAGUE, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {

                const Temp = []

                _.map(ResponseJson.data, (item, idx) => {
                    Temp.push({
                        value: item.collection_id, label: item.collection_name
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

        const { Keyword, FromDate, ToDate,SelectedGroup, sortField, isDescOrder, SelectedLeague, SelectedCollection, sportsId, SelectedFeature } = this.state
        let params = {
            league_id: SelectedLeague.value,
            collection_id: SelectedCollection.value,
            sports_id: sportsId,
            csv: true,
            sort_order: isDescOrder ? "ASC" : 'DESC',
            sort_field: sortField,
            from_date: WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD'),
            to_date: moment(ToDate).format("YYYY-MM-DD"),
            keyword: Keyword,
            report_type: "LF_contest_report",
            feature_type: SelectedFeature,
            group_id: SelectedGroup ? SelectedGroup.value : ''

            
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
        let { Keyword, FromDate, ToDate, isDescOrder, sortField, SelectedLeague, SelectedCollection, sportsId, SelectedFeature } = this.state
        let tempFromDate = ''
        let tempToDate = ''
        let CollId = !_isUndefined(SelectedCollection.value) ? SelectedCollection.value : ''
        let LeagueId = !_isUndefined(SelectedLeague.value) ? SelectedLeague.value : ''
        let sOrder = isDescOrder ? "ASC" : 'DESC';
        let groupId = this.state.SelectedGroup ? this.state.SelectedGroup.value : '';
        if (FromDate != '' && ToDate != '') {
            tempFromDate = WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD')
            tempToDate = moment(ToDate).format("YYYY-MM-DD");
        }

        var query_string = 'report_type=LF_contest_report&csv=1&keyword=' + Keyword + '&from_date=' + tempFromDate + '&to_date=' + tempToDate + '&sort_order=' + sOrder + '&sort_field=' + sortField + '&league_id=' + LeagueId + '&collection_id=' + CollId + '&sports_id=' + sportsId + '&feature_type=' + SelectedFeature+'&group_id='+groupId+'&role=2';
        var export_url = 'livefantasy/admin/report/contest_report_csv?';

        // console.log('query_string', query_string)

        HF.exportFunction(query_string, export_url)
    }

    // getAllSports = () => {
    //     const Temp = HF.getSportsData() ? HF.getSportsData() : []
    //     this.setState({
    //         AllSportsList: Temp,
    //         SelectedLSports: Temp[0].label,
    //         sportsId: Temp[0].value
    //     }, () => {
    //         // this.handleTypeChange(Temp[0], "SelectedLSports")
    //     })
    // }
    getLeagueFilter = () => {
        const { sportsId } = this.state
        let params = {
            sports_id: sportsId
        }
        WSManager.Rest(NC.baseURL + NC.LF_GET_SPORT_LEAGUES_REPORT, params).then(ResponseJson => {

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
        const { PERPAGE, CURRENT_PAGE, Keyword, FromDate, ToDate, sortField, isDescOrder, sportsId, SelectedCollection, SelectedLeague, SelectedGroup, SelectedFeature } = this.state
        let params = {
            contest_name: Keyword,
            league_id: SelectedLeague.value,
            collection_id: SelectedCollection.value,
            sports_id: sportsId,
            items_perpage: PERPAGE,
            total_items: 0,
            current_page: CURRENT_PAGE,
            sort_order: isDescOrder ? "ASC" : 'DESC',
            sort_field: sortField,
            csv: false,
            keyword: Keyword,
            group_id: SelectedGroup.value,
            feature_type: SelectedFeature,
            from_date: FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '',
            to_date: ToDate ? moment(ToDate).format('YYYY-MM-DD') : '',
        }
        WSManager.Rest(NC.baseURL + NC.LF_GET_ALL_CONTEST_REPORT, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    posting: false,
                    UserReportList: ResponseJson.data.result,
                    TotalUserReport: ResponseJson.data,
                    TotalUser: ResponseJson.data.total,
                    TotalDeposit: ResponseJson.data.total_deposit,

                }, () => {
                    console.log("UserReportList===", this.state.UserReportList);
                    console.log("posting===", this.state.posting);

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
                // this.getLeagueFilter()
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
            })
    }
    handleCollectionChange = (value, name) => {
        console.log("value===",value);
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
    searchByUser = (e) => {
        this.setState({ Keyword: e.target.value }, this.SearchCodeReq)
    }

    SearchCodeReq() {
        // if (this.state.Keyword.length > 2)
            this.getReportUser()
    }
    clearFilter = () => {
        this.setState({
            FromDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
            ToDate: new Date(moment().format('D MMM YYYY')),
            Keyword: '',
            isDescOrder: true,
            sortField: 'first_name',
            SelectedLSports: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
            sportsId: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
            SelectedLeague: '',
            SelectedGroup : '',
            SelectedCollection: '',
            SelectedFeature : '',
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

    handleFeatureChange = (value) => {
        if(value){
            this.setState({ 
                SelectedFeature: value.value 
            }, () => {
                this.getReportUser()
            })
        }
    }

    render() {
        const { posting, UserReportList, CURRENT_PAGE, PERPAGE, TotalUser, Keyword, isDescOrder, SelectedLeague, LeagueList, SelectedLSports, CollectionList, SelectedCollection, sumJoinRealAmount, sumJoinWinningAmount, sumJoinBonusAmount, FromDate, ToDate, groupList, SelectedGroup, TotalUserReport, SelectedFeature, FeatureOptions } = this.state
        var todaysDate = moment().format('D MMM YYYY');
        
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
            max_date: todaysDate,
            sel_date: ToDate,
            date_key: 'ToDate',
            place_holder: 'To Date',
            popup_placement: "bottom-end"
        }
        return (
            <Fragment>
                <div className="animated fadeIn mt-4 uc-report">
                    <Row>
                        <Col md={12}>
                            <h1 className="h1-cls">Contest Report</h1>
                        </Col>
                    </Row>
                        <Row className="mt-4">
                          
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
                                        placeholder= "Select"
                                    />
                                </div>
                            </Col>
                            <Col md={2}>
                                <div>
                                    <label className="filter-label">Select Fixture</label>
                                    <Select
                                        isSearchable={true}
                                        class="form-control"
                                        options={CollectionList}
                                        menuIsOpen={true}
                                        value={SelectedCollection}
                                        onChange={e => this.handleCollectionChange(e, 'SelectedCollection')}
                                        placeholder= "Select"
                                    />
                                </div>
                            </Col>
                            <Col md={2}>
                                <div>
                                    <label className="filter-label">Select Category</label>
                                    <Select
                                        isSearchable={true}
                                        class="form-control"
                                        id="group_id"
                                        name="group_id"
                                        options={groupList}
                                        menuIsOpen={true}
                                        value={SelectedGroup}
                                        onChange={e => this.handleCollectionChange(e, 'SelectedGroup')}
                                        placeholder= "Select"
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
                                <div className="search-box float-left w-100">
                                    <label className="filter-label">Search Contest</label>
                                    <Input
                                        placeholder="Search Contest"
                                        name='code'
                                        value={Keyword}
                                        onChange={this.searchByUser}
                                    />
                                </div>                                
                            </Col>
                        </Row>
                        <Row className="filters-box mt-3">
                            <Col md={6} className="mt-4">
                                <Button className="btn-secondary" onClick={() => this.clearFilter()}>Clear Filters</Button>
                            </Col>
                            <Col md={6} className="mt-4">
                                <i className="export-list icon-export"
                                    onClick={e => (TotalUser > NC.EXPORT_REPORT_LIMIT) ? this.exportReport_Post() : this.exportReport_Get()}></i>
                            </Col>
                        </Row>
                        <Row>
                            <Col md={12} className="table-responsive common-table new-cr-table">
                                <Table>
                                    <thead>
                                        <tr>
                                            <th className="pointer" onClick={() => this.sortContest('collection_name', isDescOrder)}>Match</th>
                                            {/* <th className="pointer" onClick={() => this.sortContest('feature_type', isDescOrder)}>Feature Type</th> */}
                                            <th className="pointer" onClick={() => this.sortContest('group_name', isDescOrder)}>Contest <br />Category</th>
                                            <th className="pointer" onClick={() => this.sortContest('contest_name', isDescOrder)}>Contest <br />Name</th>
                                            <th className="pointer" onClick={() => this.sortContest('entry_fee', isDescOrder)}>Entry <br />Fee</th>
                                            <th className="pointer" onClick={() => this.sortContest('site_rake', isDescOrder)}>Site <br />Rake %</th>
                                            <th className="pointer" onClick={() => this.sortContest('minimum_size', isDescOrder)}>Min</th>
                                            <th className="pointer" onClick={() => this.sortContest('size', isDescOrder)}>Max</th>
                                            {/* <th className="pointer" onClick={() => this.sortContest('total_user_joined', isDescOrder)}>Total <br /> Team <br /> Entered</th>
                                            <th className="pointer" onClick={() => this.sortContest('system_teams', isDescOrder)}>Total <br />Bot <br />User</th> */}
                                            <th className="pointer" onClick={() => this.sortContest('total_user_joined', isDescOrder)}>Total User</th>
                                            <th className="pointer" onClick={() => this.sortContest('max_bonus_allowed', isDescOrder)}>Bonus <br />Allowed %</th>
                                            <th className="pointer" onClick={() => this.sortContest('prize_pool', isDescOrder)}>Prize <br />Pool</th>
                                            <th className="pointer" onClick={() => this.sortContest('total_entry_fee', isDescOrder)}>Total <br />Entry <br />Fee</th>
                                            <th className="pointer" onClick={() => this.sortContest('entry_fee_real_money', isDescOrder)}>Entry <br />Fee(Real Money)</th>
                                            <th className="pointer" onClick={() => this.sortContest('entry_fee_bonus', isDescOrder)}>Entry <br />Fee(Bonus)</th>
                                            <th className="pointer" onClick={() => this.sortContest('promocode_entry_fee_real', isDescOrder)}>Entry <br />Fee(Promo Code)</th>
                                            {/* <th className="pointer" onClick={() => this.sortContest('botuser_total_real_entry_fee', isDescOrder)}>Bot <br />User <br />Entry (Real Money)</th> */}
                                            <th className="pointer" onClick={() => this.sortContest('total_win_winning_amount', isDescOrder)}>Distribution <br />(Real Money)</th>
                                            <th className="pointer" onClick={() => this.sortContest('total_win_bonus', isDescOrder)}>Distribution <br />(Bonus)</th>
                                            <th className="pointer" onClick={() => this.sortContest('total_win_coins', isDescOrder)}>Distribution <br />(Coin)</th>
                                            <th className="pointer" onClick={() => this.sortContest('total_win_prize', isDescOrder)}>Total <br />Win <br />Prize</th>
                                            <th className="pointer" onClick={() => this.sortContest('total_profit_loss', isDescOrder)}>Total <br />(Profit/Loss)</th>
                                            {/* <th className="pointer" onClick={() => this.sortContest('start_time', isDescOrder)}>Start <br />Time </th> */}
                                        </tr>
                                    </thead>
                                    {
                                        (!_.isUndefined(UserReportList) && UserReportList.length > 0) ?
                                            <Fragment>
                                                {_.map(UserReportList, (item, idx) => {
                                                    return (
                                                        <tbody key={idx}>
                                                            <tr>
                                                                <td>{item.collection_name ? item.collection_name : '--'}</td>
                                                                {/* <td>{item.feature_type ? item.feature_type : '--'}</td>                                                                 */}
                                                                <td>{item.group_name ? item.group_name : '--'}</td>
                                                                <td>{item.contest_name ? item.contest_name : '--'}</td>
                                                                <td>{
                                                                    item.currency_type == '0' && item.entry_fee > 0 &&
                                                                    <span><i className="icon-bonus"></i>{item.entry_fee}</span>
                                                                }
                                                                    {
                                                                        item.currency_type == '1' && item.entry_fee > 0 &&
                                                                        <span>{HF.getCurrencyCode()}{item.entry_fee}</span>
                                                                    }
                                                                    {
                                                                        item.currency_type == '2' && item.entry_fee > 0 &&
                                                                        <span><img src={Images.COINIMG} alt="coin-img" />{item.entry_fee}</span>
                                                                    }
                                                                    {item.entry_fee == 0 &&

                                                                        <span>Free</span>

                                                                    }
                                                                    </td>
                                                                <td>{item.site_rake ? item.site_rake : '--'}</td>
                                                                <td>{item.minimum_size ? item.minimum_size : '--'}</td>
                                                                <td>{item.size ? item.size : '--'}</td>
                                                                {/* <td>{item.total_user_joined ? item.total_user_joined : '--'}</td>
                                                                <td>{item.system_teams ? item.system_teams : '--'}</td> */}
                                                                <td>{item.total_user_joined ? item.total_user_joined : '--'}</td>
                                                                <td>{item.max_bonus_allowed ? item.max_bonus_allowed : '--'}</td>
                                                                <td>{item.prize_pool ? item.prize_pool : '--'}</td>
                                                                <td>
                                                                {
                                                                        item.currency_type == '0' && item.entry_fee > 0 &&
                                                                        <span><i className="icon-bonus"></i>{item.entry_fee}</span>
                                                                    }
                                                                    {
                                                                        item.currency_type == '1' && item.entry_fee > 0 &&
                                                                        <span>{HF.getCurrencyCode()}{item.entry_fee}</span>
                                                                    }
                                                                    {
                                                                        item.currency_type == '2' && item.entry_fee > 0 &&
                                                                        <span><img src={Images.COINIMG} alt="coin-img" />{item.entry_fee}</span>
                                                                    }
                                                                    {item.entry_fee == 0 &&

                                                                        <span>Free</span>

                                                                    }
                                                                </td>
                                                                <td>{item.total_join_real_amount ? parseFloat(item.total_join_real_amount).toFixed(2) : '--'}</td>
                                                                <td>{item.total_join_bonus_amount ? item.total_join_bonus_amount : '--'}</td>
                                                                <td>{item.promocode_entry_fee_real ? item.promocode_entry_fee_real : '--'}</td>
                                                                {/* <td>{item.botuser_total_real_entry_fee ? item.botuser_total_real_entry_fee : '--'}</td> */}
                                                                <td>{item.total_win_winning_amount ? item.total_win_winning_amount : '--'}</td>
                                                                <td>{item.total_win_bonus ? 'B' + item.total_win_bonus : '--'}</td>
                                                                <td>{item.total_win_coins ? 'C' + item.total_win_coins : '--'}</td>
                                                                <td>{item.total_win_amount_to_real_user ? item.total_win_amount_to_real_user : '--'}</td>
                                                                <td>{item.profit_loss ? item.profit_loss : '--'}</td>
                                                                {/* <td>
                                                                    {WSManager.getUtcToLocalFormat(item.season_scheduled_date, 'D-MMM-YYYY hh:mm A')}
                                                                </td> */}
                                                            </tr>
                                                        </tbody>
                                                    )
                                                })}
                                                <tbody>
                                                    <tr>
                                                        <td>Total</td>
                                                        <td colSpan="10"></td>
                                                   
                                                        <td colSpan="1">
                                                            {
                                                                (!_.isUndefined(TotalUserReport.sum_total_entry_fee_real)) ? parseFloat(TotalUserReport.sum_total_entry_fee_real).toFixed(2) : ''
                                                            }
                                                        </td>
                                                        <td colSpan="1">
                                                            {
                                                                (!_.isUndefined(TotalUserReport.sum_join_bonus_amount)) && TotalUserReport.sum_join_bonus_amount
                                                            }
                                                        </td>
                                                        
                                                        <td colSpan="1">
                                                            {
                                                                (!_.isUndefined(TotalUserReport.sum_promocode_entry_fee_real)) && TotalUserReport.sum_promocode_entry_fee_real
                                                            }
                                                        </td>
                                                        
                                                      
                                                        <td colSpan="1">
                                                            {
                                                                (!_.isUndefined(TotalUserReport.sum_win_amount)) && TotalUserReport.sum_win_amount
                                                            }
                                                        </td>
                                                        <td colSpan="1">
                                                            {
                                                                (!_.isUndefined(TotalUserReport.sum_total_win_bonus)) && TotalUserReport.sum_total_win_bonus
                                                            }
                                                        </td>
                                                        <td colSpan="1">
                                                            {
                                                                (!_.isUndefined(TotalUserReport.sum_total_win_coins)) && TotalUserReport.sum_total_win_coins
                                                            }
                                                        </td>
                                                        <td colSpan="3"/>
                                                
                                                    </tr>
                                                </tbody>
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
            </Fragment>
        )
    }
}