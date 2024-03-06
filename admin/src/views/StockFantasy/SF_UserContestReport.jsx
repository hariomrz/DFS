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
export default class SF_UserContestReport extends Component {
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
            TotalDeposit: '',


            SelectedCollection: '',
            SelectedGroup: '',
            collectionType: 1,
            TotalUserReport: [],
            posting: true,
            SelectedFeature: '',
            CategoryOptions: [],
            GameTypeOption: [],
            isShow: true
        }
        this.SearchCodeReq = _.debounce(this.SearchCodeReq.bind(this), 500);
    }
    componentDidMount() {
        if (HF.allowStockFantasy() == '0' && HF.allowEquityFantasy() == '0' && HF.allowLiveStockFantasy() == '0') {
            notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        this.GetContestFilterData()
        this.getReportUser()
    }

    GetContestFilterData = () => {
        
        let params = {};
        WSManager.Rest(NC.baseURL + NC.SF_GET_CONTEST_FILTER, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                responseJson = responseJson.data;

                let tempGroupList = [];
                if (responseJson.group_list) {
                    responseJson.group_list.map(function (lObj, lKey) {
                        tempGroupList.push({ value: lObj.group_id, label: lObj.group_name });
                    });
                }

                let tempCateList = [];
                if (responseJson.category_list) {
                    responseJson.category_list.map(function (lObj, lKey) {
                        tempCateList.push({ value: lObj.category_id, label: lObj.name });
                    });
                }

                this.setState({
                    groupList: tempGroupList,
                    CategoryOptions: tempCateList,
                    GameTypeOption: responseJson.stock_filter ? responseJson.stock_filter : [],
                });
            }
            
        })
    }

    exportReport_Post = () => {

        const { Keyword, FromDate, ToDate, sortField, isDescOrder, SelectedGroup, SelectedFeature, CURRENT_PAGE, PERPAGE } = this.state
        let params = {
            csv: true,
            sort_order: isDescOrder ? "ASC" : 'DESC',
            sort_field: sortField,
            from_date: FromDate,
            to_date: ToDate,
            keyword: Keyword,
            report_type: "contest_report",
            group_id: SelectedGroup.value,
            category_id: SelectedFeature,
            current_page: CURRENT_PAGE,
            items_perpage: PERPAGE,
        }

        WSManager.Rest(NC.baseURL + NC.SF_EXPORT_STOCK_REPORT, params).then(ResponseJson => {
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
        let { Keyword, FromDate, ToDate, isDescOrder, sortField } = this.state
        let tempFromDate = ''
        let tempToDate = ''
        let sOrder = isDescOrder ? "ASC" : 'DESC'
        if (FromDate != '' && ToDate != '') {
            // tempFromDate = FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '';
            // tempToDate = ToDate ? WSManager.getLocalToUtcFormat(ToDate, 'YYYY-MM-DD') : '';
            tempFromDate = FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '';
            tempToDate = ToDate ? moment(ToDate).format("YYYY-MM-DD") : '';
        }

        var query_string = 'report_type=stock&csv=1&keyword=' + Keyword + '&from_date=' + tempFromDate + '&to_date=' + tempToDate + '&sort_order=' + sOrder + '&sort_field=' + sortField + '&role=2';
        var export_url = 'stock/admin/contest/contest_report_csv?';

        // console.log('query_string', query_string)

        HF.exportFunction(query_string, export_url)
    }

    getReportUser = () => {
        this.setState({ posting: true })
        const { PERPAGE, CURRENT_PAGE, Keyword, FromDate, ToDate, sortField, isDescOrder, SelectedCollection, SelectedLeague, SelectedGroup, SelectedFeature, SelectedGameType } = this.state
        let params = {
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
            sort_order: isDescOrder ? "ASC" : 'DESC',
            sort_field: sortField,
            csv: false,
            // from_date: FromDate ? HF.getFormatedDateTime(FromDate, 'YYYY-MM-DD') : '',
            // to_date: ToDate ? HF.getFormatedDateTime(ToDate, 'YYYY-MM-DD') : '',
            from_date: FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '',
            to_date: ToDate ? moment(ToDate).format('YYYY-MM-DD') : '',
            keyword: Keyword,
            // group_id: SelectedGroup.value,
            category_id: SelectedFeature,
            stock_type: SelectedGameType ? SelectedGameType.value : '',
        }
        WSManager.Rest(NC.baseURL + NC.SF_GET_REPORT, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    posting: false,
                    UserReportList: ResponseJson.data.result,
                    TotalUserReport: ResponseJson.data,
                    TotalUser: ResponseJson.data.total,
                    TotalDeposit: ResponseJson.data.total_deposit,

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

                SelectedLeague: '',
                SelectedCollection: '',
            }, () => {
                this.getReportUser()
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

                this.getReportUser()
            })
    }
    handleCollectionChange = (value, name) => {
        if (value != null)
            this.setState({ [name]: value, posting: true, UserReportList: [], isShow: (value.value == 4 || value.value == 3) ? false : true }, this.getReportUser)
    }


    handleDate = (date, dateType) => {
        this.setState({ [dateType]: date, UserReportList: [] }, () => {
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
        if (this.state.Keyword.length > 2)
            this.getReportUser()
    }
    clearFilter = () => {
        this.setState({
            FromDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
            ToDate: new Date(moment().format('D MMM YYYY')),
            Keyword: '',
            isDescOrder: true,
            sortField: 'first_name',
            SelectedFeature: '',
            SelectedGameType: '',
            posting: true, 
            UserReportList: [],
            SelectedGroup: '',
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
        if (value) {
            this.setState({
                SelectedFeature: value.value,
                UserReportList: []
            }, () => {
                this.getReportUser()
            })
        }
    }

    getGameType = (value) => {
        let game_label = this.state.GameTypeOption.find(o => o.value == value);
        if(!_isUndefined(game_label))
            return game_label.label
        else
            return '--'
    }

    render() {
        const { posting, UserReportList, CURRENT_PAGE, PERPAGE, TotalUser, Keyword, isDescOrder, SelectedCollection, sumJoinRealAmount, sumJoinWinningAmount, sumJoinBonusAmount, FromDate, ToDate, groupList, SelectedGroup, TotalUserReport, SelectedFeature, CategoryOptions, GameTypeOption, SelectedGameType,isShow } = this.state
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
            sel_date: new Date(ToDate),
            date_key: 'ToDate',
            place_holder: 'To Date',
            popup_placement: "bottom-end"
        }

        const Select_Props = {
            is_disabled: false,
            is_searchable: true,
            is_clearable: false,
            menu_is_open: false,
            class_name: "",
            sel_options: CategoryOptions,
            place_holder: "Select",
            selected_value: SelectedFeature,
            modalCallback: this.handleFeatureChange
        }

        const Select_GameType_Props = {
            is_disabled: false,
            is_searchable: true,
            is_clearable: false,
            menu_is_open: false,
            class_name: "",
            sel_options: GameTypeOption,
            place_holder: "Select",
            selected_value: SelectedGameType,
            modalCallback: (e) => this.handleCollectionChange(e, 'SelectedGameType')
        }

        return (
            <Fragment>
                <div className="animated fadeIn mt-4 uc-report">
                    <Row>
                        <Col md={12}>
                            <h1 className="h1-cls">Stock Report</h1>
                        </Col>
                    </Row>
                    <Row className="mt-4">
                        {
                            // (HF.allowStockFantasy() == '1' && HF.allowEquityFantasy() == '1') &&
                            <Col md={2}>
                                <div className="search-box float-left w-100">
                                    <label className="filter-label">Select Game Type</label>
                                    <SelectDropdown SelectProps={Select_GameType_Props} />
                                </div>
                            </Col>
                        }
                        {
                            (HF.allowStockFantasy() == '1' || HF.allowEquityFantasy() == '1') && isShow &&
                            <Col md={2}>
                                <div className="search-box float-left w-100">
                                    <label className="filter-label">Select Type</label>
                                    <SelectDropdown SelectProps={Select_Props} />
                                </div>
                            </Col>
                        }
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
                                    placeholder="Select"
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
                                    // placeholder="Stock Name"
                                    name='code'
                                    value={Keyword}
                                    onChange={this.searchByUser}
                                />
                            </div>
                        </Col>

                        <Col md={2} className="mt-4 p-0">
                            <Button className="btn-secondary" onClick={() => this.clearFilter()}>Clear Filters</Button>
                            {console.log('TotalUserTotalUserTotalUserTotalUser', TotalUser)}
                            {console.log('NC.EXPORT_REPORT_LIMITNC.EXPORT_REPORT_LIMIT', NC.EXPORT_REPORT_LIMIT)}

                            <i className="export-list icon-export"
                                onClick={e => 
                                (TotalUser > NC.EXPORT_REPORT_LIMIT) ? this.exportReport_Post() : 
                                this.exportReport_Get()}></i>
                        </Col>
                    </Row>
                    <Row className="mt-30">
                        <Col md={12} className="table-responsive common-table new-cr-table">
                            { console.log(UserReportList)}
                            <Table>
                                <thead>
                                    <tr>
                                        <th className="pointer" onClick={() => this.sortContest('category_name', isDescOrder)}>Contest Type</th>
                                        {HF.allowStockFantasy() == '1' && HF.allowEquityFantasy() == '1' && <th>Game Type</th>}
                                        <th className="pointer" onClick={() => this.sortContest('group_name', isDescOrder)}>Contest <br />Category</th>
                                        <th className="pointer" onClick={() => this.sortContest('contest_name', isDescOrder)}>Contest <br />Name</th>
                                        <th className="pointer" onClick={() => this.sortContest('entry_fee', isDescOrder)}>Entry <br />Fee</th>
                                        <th className="pointer" onClick={() => this.sortContest('site_rake', isDescOrder)}>Site <br />Rake %</th>
                                        <th className="pointer" onClick={() => this.sortContest('minimum_size', isDescOrder)}>Min</th>
                                        <th className="pointer" onClick={() => this.sortContest('size', isDescOrder)}>Max</th>
                                        <th className="pointer" onClick={() => this.sortContest('total_user_joined', isDescOrder)}>Total <br /> Team <br /> Entered</th>
                                        <th className="pointer" onClick={() => this.sortContest('max_bonus_allowed', isDescOrder)}>Bonus <br />Allowed %</th>
                                        <th className="pointer" onClick={() => this.sortContest('prize_pool', isDescOrder)}>Prize <br />Pool</th>
                                        <th className="pointer" onClick={() => this.sortContest('total_entry_fee', isDescOrder)}>Total <br />Entry <br />Fee</th>
                                        <th className="pointer" onClick={() => this.sortContest('entry_fee_real_money', isDescOrder)}>Entry <br />Fee(Real Money)</th>
                                        <th className="pointer" onClick={() => this.sortContest('entry_fee_bonus', isDescOrder)}>Entry <br />Fee(Bonus)</th>
                                        <th className="pointer" onClick={() => this.sortContest('promocode_entry_fee_real', isDescOrder)}>Entry <br />Fee(Promo Code)</th>
                                        
                                        <th className="pointer" onClick={() => this.sortContest('total_win_winning_amount', isDescOrder)}>Distribution <br />(Real Money)</th>
                                        <th className="pointer" onClick={() => this.sortContest('total_win_bonus', isDescOrder)}>Distribution <br />(Bonus)</th>
                                        <th className="pointer" onClick={() => this.sortContest('total_win_coins', isDescOrder)}>Distribution <br />(Coin)</th>
                                        <th className="pointer" onClick={() => this.sortContest('total_win_prize', isDescOrder)}>Total <br />Win <br />Prize</th>
                                        <th className="pointer" onClick={() => this.sortContest('total_profit_loss', isDescOrder)}>Total <br />(Profit/Loss)</th>
                                        <th className="pointer" onClick={() => this.sortContest('start_time', isDescOrder)}>Start <br />Date Time </th>
                                        {/* <th className="pointer" onClick={() => this.sortContest('start_time', isDescOrder)}>Date</th> */}
                                    </tr>
                                </thead>
                                {
                                    (!_.isUndefined(UserReportList) && UserReportList.length > 0) ?
                                        <Fragment>
                                            {_.map(UserReportList, (item, idx) => {
                                                
                                                return (
                                                   
                                                    <tbody key={idx}>
                                                        <tr>
                                                            <td>{item.category_name ? item.category_name : '--'}</td>
                                                            <td>{
                                                                item.game_type == '2' &&
                                                                <span>Equity</span>
                                                            }
                                                                {
                                                                    item.game_type == '3' &&
                                                                    <span>Stock Predict</span>
                                                                }
                                                                {
                                                                    item.game_type == '4' &&
                                                                    <span>Live Stock Fantasy</span>
                                                                }

                                                                {
                                                                    item.game_type == '5' &&
                                                                    <span>Live Stock Fantasy</span>
                                                                }
                                                                
                                                            </td>
                                                            {/* {HF.allowStockFantasy() == '1' && HF.allowEquityFantasy() == '1' && <td>{this.getGameType(item.stock_type)}</td>} */}
                                                            <td>{item.group_name ? item.group_name : '--'}</td>
                                                            <td>{item.contest_name ? item.contest_name : '--'}</td>
                                                            <td>{
                                                                item.currency_type == '0' && item.entry_fee > 0 &&
                                                                <span><i className="icon-bonus"></i></span>
                                                            }
                                                                {
                                                                    item.currency_type == '1' && item.entry_fee > 0 &&
                                                                    <span>{HF.getCurrencyCode()}</span>
                                                                }
                                                                {
                                                                    item.currency_type == '2' && item.entry_fee > 0 &&
                                                                    <span><img src={Images.COINIMG} alt="coin-img" /></span>
                                                                }
                                                                {item.entry_fee == 0 ?
                                                                    <span>Free</span>
                                                                    :
                                                                    HF.getNumberWithCommas(HF.convertTodecimal(item.entry_fee, 2))

                                                                }
                                                            </td>
                                                            <td>{item.site_rake ? item.site_rake : '--'}</td>
                                                            <td>{item.minimum_size ? item.minimum_size : '--'}</td>
                                                            <td>{item.size ? item.size : '--'}</td>
                                                            <td>{item.total_user_joined ? item.total_user_joined : '--'}</td>
                                                            <td>{item.max_bonus_allowed ? item.max_bonus_allowed : '--'}</td>
                                                            <td>{item.prize_pool ? item.prize_pool : '--'}</td>
                                                            <td>{item.total_entry_fee ? item.total_entry_fee : '--'}</td>
                                                            <td>{item.total_join_real_amount ? HF.convertTodecimal(item.total_join_real_amount, 2) : '--'}</td>
                                                            <td>{item.total_join_bonus_amount ? item.total_join_bonus_amount : '--'}</td>
                                                            <td>{item.promocode_entry_fee_real ? HF.convertTodecimal(item.promocode_entry_fee_real, 2) : '--'}</td>
                                                          
                                                            <td>{item.total_win_winning_amount ? item.total_win_winning_amount : '--'}</td>
                                                            <td>{item.total_win_bonus ? 'B' + item.total_win_bonus : '--'}</td>
                                                            <td>{item.total_win_coins ? 'C' + item.total_win_coins : '--'}</td>
                                                            <td>{item.total_win_amount_to_real_user ? item.total_win_amount_to_real_user : '--'}</td>
                                                            <td>{item.profit_loss ? item.profit_loss : '--'}</td>
                                                            {/* <td>
                                                                {WSManager.getUtcToLocalFormat(item.scheduled_date, 'hh:mm A')}
                                                            </td> */}
                                                            <td>
                                                                {/* {WSManager.getUtcToLocalFormat(item.scheduled_date, 'D-MMM-YYYY hh:mm A')} */}
                                                                {HF.getFormatedDateTime(item.scheduled_date, 'D-MMM-YYYY hh:mm A')}
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                )
                                            })}
                                        </Fragment>
                                        :
                                        <tbody>
                                            <tr>
                                                <td colSpan='23'>
                                                    {(!posting) ?
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