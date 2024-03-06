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
import { TRADE_LEAGUE_MANAGMENT_TABLE } from '../../helper/WSCalling';
export default class Report extends Component {
    constructor(props) {
        super(props)
        this.state = {
            TotalUser: 0,
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            EndDate: new Date(),       
            StartDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
            FromDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
            ToDate: new Date(moment().format('D MMM YYYY')),
            UserReportList: [],
            Keyword: '',
            sortField: '',
            isDescOrder: false,
            SelectedLeague: '',
            LeagueList: [],
            TotalDeposit: '',
            // AllSportsList: HF.getSportsData() ? HF.getSportsData() : [],
            AllSportsList: NC.sportsId,
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
            FeatureOptionsoriginal : [],
            selected_sports_id:  NC.sportsId,
            selected_league: "",
            fixtureList :"",
            selected_fixture:""
        }
          this.handleChange = this.handleChange.bind(this);
      this.handleChangeEnd = this.handleChangeEnd.bind(this);
        // this.SearchCodeReq = _.debounce(this.SearchCodeReq.bind(this), 500);
    }

     // GET ALL LEAGUE LIST
  GetAllLeagueList = () => {

    this.setState({
      posting: true
    })
    WSManager.Rest(NC.baseURL + NC.TRADES_ALL_LEAGUE_LIST_DROPDOWN, { "sports_id": this.state.selected_sport }).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        responseJson = responseJson.data.result;
        this.setState({
          posting: false
        }, () => {
          this.createLeagueList(responseJson);
        //   this.GetAllFixtureList();
        })
      } else if (responseJson.response_code == NC.sessionExpireCode) {
        WSManager.logout();
        this.props.history.push('/login');
      }

    })
  }


  createLeagueList = (list) => {
    let leagueArr = list;
    let tempArr = [{ value: "", label: "All" }];

    if (!_isEmpty(leagueArr)) {
      leagueArr.map(function (lObj, lKey) {
        tempArr.push({ value: lObj.league_id, label: lObj.league_name });
      });
    }
    this.setState({ leagueList: tempArr });
  }



  // GET ALL FIXTURE LIST
  GetAllFixtureList = () => {
    let { selected_sport, selected_league, filter, fixture_status } = this.state

  
    let param = {
    
      "league_id": selected_league, 
      "sports_id": this.state.selected_sport
    }
    this.setState({
      posting: true
    })

    WSManager.Rest(NC.baseURL + NC.GET_TRADE_ALL_FIXTURE_BY_LEAGUE, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {

        let responseJsonData = responseJson.data;
        // console.log(responseJsonData, 'nilesh fixture response'); return false;
        // this.setState({       
        //   fixtureList: responseJsonData,      
        // })
        this.setState({
          posting: false
        }, () => {
          this.createFixtureList(responseJson.data);
        //   this.GetAllFixtureList();
        this.getReportUser()
        })
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        WSManager.logout();
        this.props.history.push('/login');
      }

    })
  }


   createFixtureList = (list) => {
    let fixtureArr = list;
    let tempArr = [{ value: "", label: "All" }];

    if (!_isEmpty(fixtureArr)) {
      fixtureArr.map(function (lObj, lKey) {
        tempArr.push({ value: lObj.season_id, label: lObj.home_name + ' vs ' + lObj.away_name + ' - ' + HF.getFormatedDateTime(lObj.scheduled_date, 'YYYY-MM-DD hh:mm A') });
      });
    }
    this.setState({ fixtureList: tempArr });
  }

   clearFilter = () => {
      const { ITEMS_PERPAGE, CURRENT_PAGE, filter, selected_sports_id, IsOrder } = this.state
    //   console.log('yes');
      // filter['keyword'] = '';
      this.setState({       
          Keyword: '',
         EndDate: new Date(moment().format('D MMM YYYY')),
         StartDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
         CURRENT_PAGE: 1 ,
         selected_league: "",           
         selected_fixture:""  

      }, () => {
         this.getReportUser()
      }
      )
   }
    componentDidMount() {
        let values = queryString.parse(this.props.location.search)  
         this.getSports();
         document.body.classList.add(
      'opinion-trade-body'
     
    );
    }

     handleSports = (e,name) => {   
      const value = e.value
      const Labels = e.label

      // [name] = Labels   
      this.setState({
         sports_id: value,
         selected_sports_id: value,
         selected_sport: e.value,
         CURRENT_PAGE : 1,
         selected_league: "",           
         selected_fixture:""   
      }, () => {

      
         this.GetAllLeagueList()
          this.GetAllFixtureList();
         this.getReportUser()
      })
   }

     getSports = () => {
      let params = {}
      WSManager.Rest(NC.baseURL + NC.TRADE_ALL_SPORTS_LIST, params).then((responseJson) => {
         
         if (responseJson.response_code == NC.successCode) {

            let sportsOptions = [];
            _.map(responseJson.data, function (data) {
               sportsOptions.push({
                  value: data.sports_id,
                  label: data.sports_name,
               })
            })
            this.setState({
               sportsOptions: sportsOptions,
               selected_sport: this.state.selected_sport ? this.state.selected_sport : sportsOptions[0].value,
                
            }, () => {
               LS.set('selectedSport', this.state.selected_sport)
               this.GetAllLeagueList()
               this.getReportUser()
            })
         }
      }).catch(error => {
         notify.show(NC.SYSTEM_ERROR, "error", 3000)
      })
   }


   handleChange(date) {
   

      this.setState({
         // Validedate: this.validateDate(date)
         StartDate: date,
         CURRENT_PAGE: 1
      }, () => {

         this.getReportUser()
      })



   }

   handleChangeEnd(date) { 

      this.setState({
         // Validedate: this.validateDate(date)
         EndDate: date,
         CURRENT_PAGE: 1
      }, () => {

         this.getReportUser()
      })

   }

    handleSelect = (value) => {    
        console.log(value)   
        if (value) {
        this.setState({ 
            "selected_league": value.value,
        }, function () {
            this.GetAllFixtureList();
        });
        }
    }

    handleSelectFixture = (value) => {   
        var text = value.label
      
        if(text != 'All'){
            const myDateArray = text.split(" ")[4];
            this.setState({         
            StartDate: myDateArray,
            CURRENT_PAGE: 1      
        })
      }   

    if (value) {
      this.setState({ "selected_fixture": value.value }, function () {
        // this.handleChange(this.state.StartDate)
        this.getReportUser();
      });
    }
  }

  handlePageChange(current_page) {
      if (current_page != this.state.CURRENT_PAGE) {
         this.setState({
            CURRENT_PAGE: current_page
         }, () => {
             this.getReportUser();
         });
      }
   }

    getReportUser = () => {
        const { StartDate, EndDate, ITEMS_PERPAGE, CURRENT_PAGE, filter, selected_sports_id, IsOrder, SortField } = this.state
      this.setState({
         ListPosting: true
      })
      let params = {  
        
         "from_date": moment(StartDate).format("YYYY-MM-DD"),        
         "to_date": moment(EndDate).format("YYYY-MM-DD"),
         "keyword": this.state.Keyword,
         "sort_field": SortField,
        //  "sort_order": IsOrder ? 'ASC': "DESC" ,
         "page": CURRENT_PAGE,
         "limit": 50,
         "season_id": this.state.selected_fixture,
         "league_id": this.state.selected_league,
         "sports_id":this.state.selected_sport

      } 
      
       
        WSManager.Rest(NC.baseURL + NC.GET_TRADE_REPORT, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {                
                this.setState({
                    posting: false,
                    UserReportList: ResponseJson.data.result,
                    TotalUser: ResponseJson.data.total
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

      exportReport_Get = () => {
        let { Keyword, StartDate, EndDate, isDescOrder, sortField,selected_league,selected_fixture} = this.state
        let tempFromDate = ''
        let tempToDate = ''
        let sOrder = isDescOrder ? "ASC" : 'DESC'
        if (StartDate != '' && EndDate != '') {          
            tempFromDate =moment(StartDate).format("YYYY-MM-DD")
            tempToDate = moment(EndDate).format("YYYY-MM-DD");
        } 
      
        var query_string = '&role=2&csv=1&keyword=' + Keyword + '&sports_id=' + this.state.selected_sport +  
        '&from_date=' + tempFromDate + '&to_date=' + tempToDate + '&sort_order=' + sOrder + '&sort_field=' + sortField + '&league_id=' + selected_league + '&season_id=' + selected_fixture;
        var export_url = 'trade/admin/report/get_opinion_report?';

        HF.exportFunction(query_string, export_url)
    }
 

     searchByUser = (e) => {       
        this.setState({ Keyword: e.target.value }, this.SearchCodeReq)
        this.getReportUser()
    }

    

    render() {
        const {fixtureList,selected_fixture,leagueList,StartDate,EndDate, posting, UserReportList, CURRENT_PAGE, PERPAGE, TotalUser, Keyword, isDescOrder, SelectedLeague, LeagueList, AllSportsList, SelectedLSports, CollectionList, SelectedCollection, sumJoinRealAmount, sumJoinWinningAmount, sumJoinBonusAmount, FromDate, ToDate, groupList, SelectedGroup, TotalUserReport, SelectedFeature, FeatureOptions } = this.state
        var todaysDate = moment().format('D MMM YYYY');
        //  let currentDate = HF.getFormatedDateTime(Date.now());
        let currentDate = WSManager.getLocalToUtcFormat(Date.now(), 'YYYY-MM-DD HH:mm:ss');

        
       

        return (
            <Fragment>
                <div className="animated fadeIn mt-4 uc-report">
                    <Row>
                        <Col md={12}>
                            <h1 className="h1-cls">Opinion Report</h1>
                        </Col>
                    </Row>
                        <Row className="mt-4">
                            <Col md={2}>
                                {/* <div>
                                    <label className="filter-label">Select Sport</label>
                                    <Select
                                        isSearchable={true}
                                        class="form-control"
                                        options={AllSportsList}
                                        menuIsOpen={true}
                                        value={SelectedLSports}
                                        onChange={e => this.handleTypeChange(e, 'SelectedLSports')}
                                    />
                                </div> */}
                                
                                 <div>
                                          <label className="filter-label">Select Sports </label>
                                          <Select
                                             className="mr-15"
                                             id="selected_sport"
                                             name="selected_sport"
                                             placeholder="Select Sport"
                                             value={this.state.selected_sport}
                                             options={this.state.sportsOptions}
                                          onChange={(e) => this.handleSports(e)}
                                        //   onChange={e => this.handleTypeChange(e, 'SelectedLSports')}
                                          />
                                       </div>
                            </Col>
                            <Col md={2}>
                                <div>
                                    <label className="filter-label">Select League</label>
                                     <Select
                                    className="dfs-selector"
                                    id="selected_league"
                                    name="selected_league"
                                    placeholder="Select League"
                                    value={this.state.selected_league}
                                    options={leagueList}
                                    onChange={(e) => this.handleSelect(e, 'selected_league')}
                                    />
                                </div>
                            </Col>
                            <Col md={2}>
                                <div>
                                    <label className="filter-label">Select Fixture</label>
                                    <Select
                                        isSearchable={true}
                                        isClearable={false}
                                        class="form-control"
                                        options={fixtureList}
                                        menuIsOpen={true}
                                          name="selected_fixture"
                                        value={this.state.selected_fixture}
                                        // onChange={e => this.handleCollectionChange(e, 'SelectedCollection')}
                                       onChange={(e) => this.handleSelectFixture(e, 'selected_fixture')}
                                        placeholder= "Select"
                                    />
                                </div>
                            </Col>
                            {/* <Col md={2}>
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
                            </Col> */}
                            <Col md={4}>
                                <div className='inputFields inPutBg lge-manage'>
                                       <div className = "fix-div">

                                       <label className="filter-label" htmlFor="CandleDetails">Start date</label>
                                       <>
                                             {/* <SelectDate DateProps={StartDateProps} /> */}
                                             <DatePicker
                                                maxDate={new Date(EndDate)}
                                                className="Select-control inPut icon-calender"
                                                showYearDropdown='true'
                                                selected={new Date(StartDate)}
                                                onChange={this.handleChange}
                                                placeholderText="From"
                                                dateFormat='dd/MM/yyyy'
                                             />
                                          <i className='icon-calender Ccalender trntcalender'></i>
                                       </>
                                       </div>
                                       <div className="fix-div">

                                       <label className="filter-label" htmlFor="CandleDetails">End Date</label>
                                       <>
                                             {/* <SelectDate DateProps={EndDateProps} /> */}
                                             <DatePicker
                                                minDate={new Date(StartDate)}
                                                // maxDate={new Date()}
                                                className="Select-control inPut icon-calender"
                                                showYearDropdown='true'
                                                selected={new Date(EndDate)}
                                                onChange={this.handleChangeEnd}
                                                placeholderText="To"
                                                dateFormat='dd/MM/yyyy'
                                             />
                                          <i className='icon-calender Ccalender trntcalender'></i>
                                       </>
                                       </div>


                                    </div>  
                              
                            </Col>

                            <Col md={2} className="mt-4">
                                <Button className="btn-secondary btn-sze" onClick={() => this.clearFilter()}>Clear Filters</Button>
                            </Col>
                        </Row>
                        <Row className="filters-box mt-3">
                            {/* <Col md={2}> */}
                                {/* <div className="search-box float-left w-100">
                                    <label className="filter-label">Search Contest</label>
                                    <Input
                                        placeholder="Search Contest"
                                        name='code'
                                        value={Keyword}
                                        onChange={this.searchByUser}
                                    />
                                </div>                                 */}
                            {/* </Col> */}
                            {/* <Col md={2}>
                                <div className="search-box float-left w-100">
                                    <label className="filter-label">Select Feature type</label>
                                    <SelectDropdown SelectProps={Select_Props} />
                                </div>
                            </Col> */}
                            {/* <Col md={3} className="mt-4">
                                <Button className="btn-secondary" onClick={() => this.clearFilter()}>Clear Filters</Button>
                            </Col> */}
                            <Col md={12} className="mt-4">
                                <i className="export-list icon-export export-trade-report"
                                    onClick={() => this.exportReport_Get()}></i>
                            </Col>
                        </Row>
                        <Row>
                            <Col md={12} className="table-responsive common-table new-cr-table">
                                <Table>
                                    <thead>
                                        <tr>
                                            <th className="pointer">Match</th>
                                            <th className="pointer">Schedule Date </th>
                                            <th className="pointer">Total Option <br/> Entered</th>
                                            <th className="pointer">Total Unique <br/> user joined</th>
                                            <th className="pointer">Matched <br />Count</th>
                                            <th className="pointer">Unmatched <br />Count</th>
                                            <th className="pointer">Total Entry <br />Fee</th>
                                            <th className="pointer">Site <br />Rake %</th>
                                            <th className="pointer">Distribution</th>
                                            <th className="pointer">Status</th>
                                            
                                            <th className="pointer">Total <br />(Profit/Loss)</th>
                                        </tr>
                                    </thead>
                                    {
                                        (!_.isUndefined(UserReportList) && UserReportList.length > 0) ?
                                            <Fragment>
                                                {_.map(UserReportList, (item, idx) => {
                                                    if (item.status < 2 && item.scheduled_date > currentDate) {
                                                        var statusFixture = 'Not Started'
                                                    }
                                                    if (item.status < 2 && item.scheduled_date < currentDate) {
                                                        var statusFixture = 'Live'
                                                    }
                                                    
                                                     if (item.status == 2) {
                                                        var statusFixture = 'Completed'
                                                    }
                                                    if (item.status == 3) {
                                                        var statusFixture = 'Postponed'
                                                    }
                                                     if (item.status == 4) {
                                                        var statusFixture = 'Suspended'
                                                    }
                                                    if (item.status == 5) {
                                                        var statusFixture = 'Canceled'
                                                    }
                                                    return (
                                                        <tbody key={idx}>
                                                            <tr>
                                                                <td><a className="trade-cursor" style ={{"cursor":'pointer'}}onClick={() => this.props.history.push({ pathname: '/opinionTrading/publish_match/' + item.league_id + '/' + item.season_id + '/1'})}>{item.home_name ? item.home_name : '--'} vs {item.away_name ? item.away_name : '--'}</a></td>
                                                                {/* <td>{item.scheduled_date ? item.scheduled_date : '--'}</td> */}
                                                                <td> {HF.getFormatedDateTime(item.scheduled_date, "D/MM/YYYY hh:mm A")}</td>
                                                                <td>{item.opinion_entered ? item.opinion_entered : '--'}</td>                                                                
                                                                <td>{item.unique_user_joined ? item.unique_user_joined : '--'}</td>
                                                                <td>{item.matched ? item.matched : '--'}</td>
                                                                <td>{item.unmatched ? item.unmatched : '--'}</td>
                                                                <td>{item.total_entry_fee ? item.total_entry_fee : '--'}</td>                                                              
                                                                <td>{item.site_rake ? item.site_rake : '--'}</td>
                                                                <td>{ item.distribution ? item.distribution : '--'}</td>
                                                                <td>{ item.status ? statusFixture : '--'}</td>
                                                                <td>{item.status == 2 ? (item.total_entry_fee * item.site_rake/100) : '--'}</td>
                                                              
                                                          
                                                               
                                                            </tr>
                                                        </tbody>
                                                    )
                                                })}
                                                <tbody>
                                                
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
                        {TotalUser > 0 && (
                            <div className="custom-pagination lobby-paging">
                                <Pagination
                                    activePage={CURRENT_PAGE}
                                    itemsCountPerPage= "50"
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