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
export default class PFContestReport extends Component {
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
            sportsOptions:[],
            leagueOptions:[],
            TotalDeposit: '',
            AllSportsList: HF.getSportsData() ? HF.getSportsData() : [],
            sports_id: (LS.get('selectedSport')) ? LS.get('selectedSport') : '1',
            contestName: '',
            CollectionList: [],
            SelectedCollection: '',
            SelectedFixure: '',
            collectionType: 1,
            TotalUserReport: [],
            posting: false,
            SelectedFeature : '',
            FeatureOptions : [],
            teamsOptions: [],
            group_id:'',
            selectedGroup: '',
            season_id: '',
            fixtureListoptions: []
        }
        this.SearchCodeReq = _.debounce(this.SearchCodeReq.bind(this), 500);
    }
    componentDidMount() {
        this.apiCall();
    }

    apiCall = () => {
        //this.getLeagueFilter()
        //this.GetContestFilterData()
        this.getReportUser()  
        this.getSports() 
        this.GetGroupList()  
       
    }

    GetContestFilterData = () => {
        this.setState({ posting: true })
        let params = {
            "sports_id": this.state.sportsId,
            "list_type": true
        };
        WSManager.Rest(NC.baseURL + NC.GET_CONTEST_FILTER, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                responseJson = responseJson.data;

                let tempGroupList = [{ 'value': '', 'label': 'Select Category' }];
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

        const { Keyword, FromDate, ToDate, sortField, isDescOrder, SelectedLeague, SelectedCollection, sports_id, SelectedFeature } = this.state
        let params = {
            league_id: SelectedLeague.value,
            collection_master_id: SelectedCollection,
            sports_id: sports_id,
            csv: true,
            sort_order: isDescOrder ? "ASC" : 'DESC',
            sort_field: sortField,
            from_date: WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD'),
            to_date: moment(ToDate).format("YYYY-MM-DD"),
            keyword: Keyword,
            report_type: "contest_report",
            feature_type: SelectedFeature,
        }
        WSManager.Rest(NC.baseURL + NC.EXPORT_CONTEST_WINNERS, params).then(ResponseJson => {
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
        let { Keyword, FromDate, ToDate, isDescOrder, sortField, SelectedLeague, SelectedCollection, sports_id, SelectedFeature } = this.state
        let tempFromDate = ''
        let tempToDate = ''
        let CollId = !_isUndefined(SelectedCollection) ? SelectedCollection : ''
        let LeagueId = !_isUndefined(SelectedLeague.value) ? SelectedLeague.value : ''
        let sOrder = isDescOrder ? "ASC" : 'DES'
        if (FromDate != '' && ToDate != '') {
            tempFromDate = FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : ''
            tempToDate = ToDate ? moment(ToDate).format("YYYY-MM-DD") : '';
        }
       //debugger;
        var query_string = 'report_type=contest_report&csv=1&keyword=' + Keyword + '&from_date=' + tempFromDate + '&to_date=' + tempToDate + '&sort_order=' + sOrder + '&sort_field=' + sortField + '&league_id=' + LeagueId + '&collection_master_id=' + CollId + '&sports_id=' + sports_id + '&feature_type=' + SelectedFeature + '&role=2';
        var export_url = 'picks/admin/report/contest_report_csv?';
console.log('query_string', query_string)
        // HF.exportFunction(query_string, export_url)
    }



    getReportUser = () => {
        this.setState({ posting: true })
        const { selectedGroup ,PERPAGE, CURRENT_PAGE, Keyword, FromDate, ToDate, sortField, isDescOrder, sportsId, SelectedCollection, SelectedLeague, SelectedFixure, SelectedFeature } = this.state
      
        let params = {
            sports_id: this.state.sports_id ? this.state.sports_id : '1',
            league_id : this.state.league_id ? this.state.league_id : '' ,
            season_id : SelectedCollection || '',
            group_id : selectedGroup,
            collection_id: SelectedCollection,
            contest_name : Keyword,
            item_perpage : "50",
            current_page : "1",
            page_size : "",
            Keyword : Keyword,
            from_date: FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '',
            to_date: ToDate ? moment(ToDate).format('YYYY-MM-DD') : '',
        }

        WSManager.Rest(NC.baseURL + NC.PF_GET_ALL_CONTEST_REPORT, params).then(ResponseJson => {
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



   
    handleCollectionChange = (e, name) => {
        if (e != null)
        console.log('season_id',e.value, name)
            this.setState({ [name]: e.value }, () => {
                this.getReportUser()
                this.getAllCollections()
            })
    }


    handleDate = (date, dateType) => {
        this.setState({ [dateType]: date }, () => {
            if (this.state.FromDate || this.state.ToDate) {
                this.getReportUser()
            }
        })
    }

    //handle change

    handleSports = (e) => {
        this.setState({
          sports_id: e.value,
          selected_sport: e.value,
          SelectedCollection: '',
          selected_league:'',
          league_id: '',
          fixtureListoptions: []
        }, ()=>{ 
          LS.set('selectedSport', this.state.sports_id)
          this.getLeagues()
          this.getReportUser()
        })
      }

      handleLeague = (e) => {
        console.log('handleLeague',e)
        this.setState({
          league_id: e.value,
          selected_league: e.value,
          SelectedCollection: ''
        }, ()=>{ 

            this.GetAllFixtureList();
            // this.getTeams()
            this.getReportUser()
        })
      }
  
      getTeams = () =>{
        let params = {
          "league_id": this.state.league_id
        }
        WSManager.Rest(NC.baseURL + NC.GET_TEAM_BY_LEAGUE_ID_LIST, params).then((responseJson) => {
            if (responseJson.response_code == NC.successCode) {
                let teamsOptions= [];
                _.map(responseJson.data, function (data){
                  teamsOptions.push({
                                value: data.team_id,
                                label: data.team_name,
                            })
                   })
                this.setState({
                  teamsOptions: teamsOptions,
                })   
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
      }    

      // GET ALL LEAGUE LISTPF_GET_SPORTS
      //new
  getSports = () =>{
    let params = {}
    WSManager.Rest(NC.baseURL + NC.PF_GET_SPORTS, params).then((responseJson) => {
        if (responseJson.response_code == NC.successCode) {

            let sportsOptions= [];
            _.map(responseJson.data, function (data){
                        sportsOptions.push({
                            value: data.sports_id,
                            label: data.name,
                        })
               })
            this.setState({
                sportsOptions: sportsOptions,
                selected_sport: this.state.selectedSport ? this.state.selectedSport : sportsOptions[0],
            },()=>{ LS.set('selectedSport', this.state.sportsOptions[0].value)
            this.getLeagues()})   
        }
    }).catch(error => {
        notify.show(NC.SYSTEM_ERROR, "error", 3000)
    })
}
//new
QuestionsOptions = () =>{
    let temArry = [];
      for(let i = 0; i < this.state.question; i++){
        temArry.push({
          value: i+1,
          label: i+1,
          });
      }
    this.setState({
      QuestionsOptions: temArry,
    })  
}
//new
getLeagues = () =>{
    let params = {"sports_id": this.state.sports_id ? this.state.sports_id : LS.get('selectedSport')}
    WSManager.Rest(NC.baseURL + NC.PF_GET_LEAGUE_LIST, params).then((responseJson) => {
        if (responseJson.response_code == NC.successCode) {
            console.log('getLeagues',responseJson.data)
            let leagueOptions = [{ 'value': '', 'label': 'Select League' }];
            _.map(responseJson.data, function (data){
                        leagueOptions.push({
                            value: data.league_id,
                            label: data.league_name
                        })
               })

               console.log('getLeagues leagueOptions',leagueOptions)
            this.setState({
                leagueOptions: leagueOptions,
            })   
        }
    }).catch(error => {
        notify.show(NC.SYSTEM_ERROR, "error", 3000)
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
        //if (this.state.Keyword.length > 2)
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
            SelectedCollection: '',
            SelectedFeature : '',
            selectedGroup: '',
            selected_league: '',
            fixtureListoptions: [],

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

    handleSelect = (e) => {
       this.setState({
        selectedGroup: e.value,
       },()=>{this.getReportUser()})
      }


      GetGroupList = () =>{
        this.setState({ posting: true })
        let param = {}
        WSManager.Rest(NC.baseURL + NC.PF_GET_GROUP_LIST, { "sports_id": this.state.sports_id }).then((responseJson) => {
          if (responseJson.response_code === NC.successCode) {
            responseJson = responseJson.data;
            let groupList = [];
            responseJson.map(function (lObj, lKey) {
              groupList.push({ value: lObj.group_id, label: lObj.group_name })
            })
            this.setState({
              groupList: groupList,
            })
          }
        })
      }
   // GET ALL FIXTURE LIST
  GetAllFixtureList = () => {
    let param = {
      "sports_id": this.state.sports_id,
      "league_id": this.state.league_id,
      "items_perpage": 500,
      "current_page": 1,
      "sort_order": "ASC",
      "sort_field": "season_scheduled_date",
      
    }
    console.log("league_id", this.state.league_id)
    this.setState({
      posting: true
    })
    

    WSManager.Rest(NC.baseURL + NC.PF_GET_FIXTURE_BY_LEAGUE_ID, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
       
        let responseJsonData = responseJson.data;
       
        let tempFixtureList = [{ 'value': '', 'label': 'Select Fixture' }];
      
          responseJsonData.map(function (lObj, lKey) {
            tempFixtureList.push({ value: lObj.season_id, label: lObj.match });
          });
      
          this.setState({
            posting: false,
            fixtureListoptions: tempFixtureList,
          })
        }

      })
   
  }
      
    render() {
        const { selectedGroup, teamsOptions, posting, UserReportList, CURRENT_PAGE, PERPAGE, TotalUser, Keyword, isDescOrder, SelectedLeague, LeagueList, AllSportsList, sportsOptions, leagueOptions, SelectedLSports, CollectionList, SelectedCollection, sumJoinRealAmount, sumJoinWinningAmount, sumJoinBonusAmount, FromDate, ToDate, groupList, SelectedFixure, TotalUserReport, SelectedFeature, FeatureOptions ,fixtureListoptions} = this.state
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

        const Select_Props = {
            is_disabled: false,
            is_searchable: true,
            is_clearable: false,
            menu_is_open: false,
            class_name: "",
            sel_options: FeatureOptions,
            place_holder: "Select",
            selected_value: SelectedFeature,
            modalCallback: this.handleFeatureChange
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
                                 <label className="filter-label">Select Sports </label>
                                    <Select
                                        className="mr-15"
                                        id="selected_sport"
                                        name="selected_sport"
                                        placeholder="Select Sport"
                                        value={this.state.selected_sport}
                                        options={sportsOptions}
                                        onChange={(e) => this.handleSports(e)}
                                />
                                </div>
                            </Col>
                            <Col md={2}>
                                <div>
                                    <label className="filter-label">Select League</label>
                                    
                                    <Select
                                          className=""
                                          id="selected_league"
                                          name="selected_league"
                                           placeholder="Select League"
                                           value={this.state.selected_league}
                                           options={leagueOptions}
                                           onChange={(e) => this.handleLeague(e)}
                                     />
                                </div>
                            </Col>
                            <Col md={2}>
                                <div>
                                    <label className="filter-label">Select Fixture</label>
                                    <Select
                                        isSearchable={true}
                                        id="SelectedCollection"
                                        name="SelectedCollection"
                                        className=""
                                        options={fixtureListoptions}
                                        // menuIsOpen={true}
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
                                        className=""//"gray-select-field"
                                        id="selectedGroup"
                                        name="selectedGroup"
                                        placeholder="Select Group"
                                        value={selectedGroup}
                                        options={groupList}
                                        onChange={(e) => this.handleSelect(e)}
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
                        </Row>
                        <Row className="filters-box mt-3">
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
                            {/* <Col md={2}>
                                <div className="search-box float-left w-100">
                                    <label className="filter-label">Select Feature type</label>
                                    <SelectDropdown SelectProps={Select_Props} />
                                </div>
                            </Col> */}
                            <Col md={2} className="mt-4">
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
                                            <th className="pointer" onClick={() => this.sortContest('total_user_joined', isDescOrder)}>Total <br /> Team <br /> Entered</th>
                                            {/* <th className="pointer" onClick={() => this.sortContest('system_teams', isDescOrder)}>Total <br />Bot <br />User</th> */}
                                            <th className="pointer" onClick={() => this.sortContest('real_teams', isDescOrder)}>Total <br />Real <br />User</th>
                                            <th className="pointer" onClick={() => this.sortContest('max_bonus_allowed', isDescOrder)}>Bonus <br />Allowed %</th>
                                            <th className="pointer" onClick={() => this.sortContest('prize_pool', isDescOrder)}>Prize <br />Pool</th>
                                            <th className="pointer" onClick={() => this.sortContest('total_entry_fee', isDescOrder)}>Total <br />Entry <br />Fee</th>
                                            <th className="pointer" onClick={() => this.sortContest('entry_fee_real_money', isDescOrder)}>Entry <br />Fee(Real Money)</th>
                                            <th className="pointer" onClick={() => this.sortContest('entry_fee_bonus', isDescOrder)}>Entry <br />Fee(Bonus)</th>
                                            {/* <th className="pointer" onClick={() => this.sortContest('promocode_entry_fee_real', isDescOrder)}>Entry <br />Fee(Promo Code)</th> */}
                                            {/* <th className="pointer" onClick={() => this.sortContest('botuser_total_real_entry_fee', isDescOrder)}>Bot <br />User <br />Entry (Real Money)</th> */}
                                            <th className="pointer" onClick={() => this.sortContest('total_win_winning_amount', isDescOrder)}>Distribution <br />(Real Money)</th>
                                            <th className="pointer" onClick={() => this.sortContest('total_win_bonus', isDescOrder)}>Distribution <br />(Bonus)</th>
                                            <th className="pointer" onClick={() => this.sortContest('total_win_coins', isDescOrder)}>Distribution <br />(Coin)</th>
                                            <th className="pointer" onClick={() => this.sortContest('total_win_prize', isDescOrder)}>Total <br />Win <br />Prize</th>
                                            <th className="pointer" onClick={() => this.sortContest('total_profit_loss', isDescOrder)}>Total <br />(Profit/Loss)</th>
                                            <th className="pointer" onClick={() => this.sortContest('start_time', isDescOrder)}>Start <br />Time </th>
                                        </tr>
                                    </thead>
                                    {
                                        (!_.isUndefined(UserReportList) && UserReportList.length > 0) ?
                                            <Fragment>
                                                {_.map(UserReportList, (item, idx) => {
                                                    //console.log("UserReportList", UserReportList)
                                                    return (
                                                        <tbody key={idx}>
                                                            <tr>
                                                                <td>{item.match ? item.match : '--'}</td>
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
                                                                <td>{item.total_user_joined ? item.total_user_joined : '--'}</td>
                                                                {/* <td>{item.system_teams ? item.system_teams : '--'}</td> */}
                                                                <td>{item.real_teams ? item.real_teams : '--'}</td>
                                                                <td>{item.max_bonus_allowed ? item.max_bonus_allowed : '--'}</td>
                                                                <td>{item.prize_pool ? item.prize_pool : '--'}</td>
                                                                <td>
                                                                {
                                                                        item.currency_type == '0' && item.total_entry_fee > 0 &&
                                                                        <span><i className="icon-bonus"></i>{item.total_entry_fee}</span>
                                                                    }
                                                                    {
                                                                        item.currency_type == '1' && item.total_entry_fee > 0 &&
                                                                        <span>{HF.getCurrencyCode()}{item.total_entry_fee}</span>
                                                                    }
                                                                    {
                                                                        item.currency_type == '2' && item.total_entry_fee > 0 &&
                                                                        <span><img src={Images.COINIMG} alt="coin-img" />{item.total_entry_fee}</span>
                                                                    }
                                                                    {item.total_entry_fee == 0 &&

                                                                        <span>Free</span>

                                                                    }
                                                                </td>
                                                                <td>{item.total_join_real_amount ? parseFloat(item.total_join_real_amount).toFixed(2) : '--'}</td>
                                                                <td>{item.total_join_bonus_amount ? item.total_join_bonus_amount : '--'}</td>
                                                                {/* <td>{item.promocode_entry_fee_real ? item.promocode_entry_fee_real : '--'}</td> */}
                                                                {/* <td>{item.botuser_total_real_entry_fee ? item.botuser_total_real_entry_fee : '--'}</td> */}
                                                                <td>{item.total_win_winning_amount ? item.total_win_winning_amount : '--'}</td>
                                                                <td>{item.total_win_bonus ? 'B' + item.total_win_bonus : '--'}</td>
                                                                <td>{item.total_win_coins ? 'C' + item.total_win_coins : '--'}</td>
                                                                <td>{item.total_win_amount_to_real_user ? item.total_win_amount_to_real_user : '--'}</td>
                                                                <td>{item.profit_loss ? item.profit_loss : '--'}</td>
                                                                <td>
                                                                    {/* {WSManager.getUtcToLocalFormat(item.scheduled_date, 'D-MMM-YYYY hh:mm A')} */}
                                                                    {HF.getFormatedDateTime(item.scheduled_date, 'D-MMM-YYYY hh:mm A')}
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    )
                                                })}
                                                <tbody>
                                                    <tr>
                                                        <td>Total</td>
                                                        <td colSpan="2"></td>
                                                        <td colSpan="1">
                                                            {   
                                                                (!_.isUndefined(TotalUserReport.sum_entry_fee)) && TotalUserReport.sum_entry_fee
                                                               
                                                            }
                                                        </td>
                                                        <td colSpan="1">
                                                            {   
                                                                (!_.isUndefined(TotalUserReport.sum_site_rake)) && TotalUserReport.sum_site_rake
                                                               
                                                            }
                                                        </td>
                                                        <td colSpan="2"></td>
                                                        <td colSpan="1">
                                                            {
                                                                (!_.isUndefined(TotalUserReport.sum_total_user_joined)) && TotalUserReport.sum_total_user_joined
                                                            }
                                                        </td>
                                                        <td colSpan="1">
                                                            {
                                                                (!_.isUndefined(TotalUserReport.sum_real_teams)) && TotalUserReport.sum_real_teams
                                                            }
                                                        </td>
                                                        <td colSpan="1">
                                                            {
                                                                (!_.isUndefined(TotalUserReport.sum_max_bonus_allowed)) && TotalUserReport.sum_max_bonus_allowed
                                                            }
                                                        </td>
                                                        <td colSpan="1"></td>

                                                        <td colSpan="1">
                                                            {
                                                                (!_.isUndefined(TotalUserReport.sum_total_entry_fee)) && TotalUserReport.sum_total_entry_fee
                                                            }
                                                        </td>
                                                        <td colSpan="1">
                                                            {
                                                                (!_.isUndefined(TotalUserReport.sum_total_entry_fee_real)) ? parseFloat(TotalUserReport.sum_total_entry_fee_real).toFixed(2) : ''
                                                            }
                                                        </td>
                                                        { <td colSpan="1">
                                                            {
                                                                (!_.isUndefined(TotalUserReport.sum_join_bonus_amount)) && TotalUserReport.sum_join_bonus_amount
                                                            } 
                                                        </td>
                                                         }
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
                                                        <td colSpan="1">
                                                            {
                                                                (!_.isUndefined(TotalUserReport.sum_total_win_amount_to_real_user)) && TotalUserReport.sum_total_win_amount_to_real_user
                                                            }
                                                        </td>
                                                        <td colSpan="1">
                                                            {
                                                                (!_.isUndefined(TotalUserReport.sum_profit_loss)) && TotalUserReport.sum_profit_loss
                                                            }
                                                        </td>
                                                        <td colSpan="3"></td>
                                                       
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
