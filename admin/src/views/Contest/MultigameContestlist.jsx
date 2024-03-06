import React, { Component } from 'react';
import { Card, CardBody, Col, Row, Modal, ModalBody, ModalHeader, ModalFooter, FormGroup, Input, InputGroup, Button, Table} from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Select from 'react-select';
import LS from 'local-storage';
import queryString from 'query-string';
import Images from '../../components/images';
import * as MODULE_C from "../Marketing/Marketing.config";
import PromoteContestModal from '../../Modals/PromoteContest';
import PromoteNotActive from '../../Modals/PromoteNotActive';
import moment from 'moment';
import Pagination from "react-js-pagination";
import HF from '../../helper/HelperFunction';
class MultigameContestlist extends Component {

  constructor(props) {
    super(props);
    let selected_sports_id = (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId;
    this.state = {
      selected_sport: selected_sports_id,
      contestParams: { 'sports_id': selected_sports_id, 'league_id': '','season_game_uid': '', 'collection_master_id': '', 'group_id': '', 'status': '', 'keyword': '', 'sort_field': 'season_scheduled_date', 'sort_order': 'DESC', currentPage: 1, pageSize: 10, pagesCount: 1 },
      leagueList: [],
      groupList: [],
      statusList: [],
      contestList: [],
      contestObj: {},
      keyword: '',
      posting: false,
      contest_promote_model: false,
      contestPromoteParam: {
        email_contest_model: false,
        message_contest_model: false,
        notification_contest_model: false
      },
      promote_model: false,
      minPage: 1,
      maxPage: 5,
      fixtureList:[],
      matchList: []
    };
  }

  componentDidMount() {
    this.GetContestFilterData();
  }

  handleFieldVal = (e, tindex) => {
    if (e) {
      let value = e.target.value;
      let contestParams = this.state.contestParams;
      contestParams['keyword'] = value;
      this.setState({ 'contestParams': contestParams }, function () { });
    }
  }

  handleSelect = (eleObj, dropName) => { 
    
    let contestParams = this.state.contestParams;
    contestParams[dropName] = (eleObj!=null) ? eleObj.value : '';
    this.setState({ 'contestParams': contestParams,'selected_league':(eleObj!=null) ? eleObj.value : '' }, function () {
      if(dropName=='league_id') {
        if(contestParams[dropName]) {
          this.GetAllFixtureList();
        } else {
          this.setState({ fixtureList: []});
        }
      }
      
      this.SearchContest();
    });
  }

  handleSelectFixture = (eleObj, dropName) => {
    let contestParams = this.state.contestParams;
    contestParams[dropName] = (eleObj!=null) ? eleObj.value : '';
    this.setState({'contestParams': contestParams }, function () {
      this.SearchContest();
    });
  }

  GetContestFilterData = () => { 
    this.setState({ posting: true })
    let params = { "sports_id": this.state.selected_sport};
    WSManager.Rest(NC.baseURL + NC.GET_MG_CONTEST_FILTER, params).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        responseJson = responseJson.data;

        let tempLeagueList = [{ 'value': '', 'label': 'Select League' }];
        if (responseJson.league_list) {
          responseJson.league_list.map(function (lObj, lKey) {
            tempLeagueList.push({ value: lObj.league_id, label: lObj.league_name });
          });
        }
        let tempGroupList = [{ 'value': '', 'label': 'Select Group' }];
        if (responseJson.group_list) {
          responseJson.group_list.map(function (lObj, lKey) {
            tempGroupList.push({ value: lObj.group_id, label: lObj.group_name });
          });
        }
        this.setState({ leagueList: tempLeagueList, groupList: tempGroupList, statusList: responseJson.status_list });
       
        this.GetContestList(); 
      }
      this.setState({ posting: false })
    })
  }

  SearchContest = () => {
    let contestParams = this.state.contestParams;
    contestParams["currentPage"] = 1;
    contestParams["pagesCount"] = 1;
    this.setState({ 'contestParams': contestParams }, function () {
      this.GetContestList();
    });
  }
  // GET ALL FIXTURE LIST
  GetAllFixtureList = () => {
    let {int_version} = HF.getMasterData()
    let param = {
      "sports_id": this.state.selected_sport,
      "league_id": this.state.selected_league,
      "items_perpage": 500,
      "current_page": 1,
      "sort_order": "ASC",
      "sort_field": "season_scheduled_date",
    }
    this.setState({
      posting: true
    })

    WSManager.Rest(NC.baseURL + NC.GET_MG_LEAGUE_FIXTURES, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {

        let responseJsonData = responseJson.data;
        let fixtureList =  responseJsonData;
        let tempFixtureList = [{ 'value': '', 'label': int_version == "1" ? "Select Game" : "Select Fixture" }];
        this.setState({
          posting: false,
          fixtureList: tempFixtureList,
        })
        if (fixtureList) {
          let tempFixtureList = [];
          console.log('fixtureList',fixtureList)
          fixtureList.map(function (lObj, lKey) {
            let d = moment(new Date(WSManager.getUtcToLocal(lObj.season_scheduled_date)));
            tempFixtureList.push({ value: lObj.collection_master_id, label: lObj.collection_name+' ('+d.format("YYYY-DD-MM h:mm A")+')',season_scheduled_date:lObj.season_scheduled_date });
            // tempFixtureList.push({ value: lObj.season_game_uid, label: lObj.home+' VS '+lObj.away+' ('+d.format("YYYY-DD-MM h:mm A")+')',season_scheduled_date:lObj.season_scheduled_date });
          });
          this.setState({
            posting: false,
            fixtureList: tempFixtureList,
          })  
        }

       
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        WSManager.logout();
        this.props.history.push('/login');
      }
    })
  }

  GetContestList = () => { 
    this.setState({ posting: true })
    let params = this.state.contestParams;
    WSManager.Rest(NC.baseURL + NC.GET_MULTIGAME_CONTEST_LIST, params).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        var responseJsonData = responseJson.data.result;
        console.log('responseJson.data',responseJson.data)
        this.setState({
          contestList: responseJsonData,
          matchList: responseJson.data.match_list,
          contestParams: { ...this.state.contestParams, pagesCount: Math.ceil(responseJson.data.total / this.state.contestParams.pageSize),totalRecords:responseJson.data.total },
        })
      }
      this.setState({ posting: false })
    })
  }

  getWinnerCount(ContestItem) {  
    let PDD = this.handleJsonParser(ContestItem.prize_distibution_detail)
    if(PDD!=''){
      if ((PDD[PDD.length - 1].max) > 1) {
        return PDD[PDD.length - 1].max + " Winners"
      } else {
        return PDD[PDD.length - 1].max + " Winner"
      }
    } else{
      return '0 Winner';
    }
  }

  viewWinners = (e, contestObj) => {
    e.stopPropagation();
    let ConObj = contestObj
    ConObj['prize_distibution_detail']= this.handleJsonParser(contestObj.prize_distibution_detail)
    this.setState({ 'prize_modal': true, 'contestObj': ConObj });
  }

  closePrizeModel = () => {
    this.setState({ 'prize_modal': false, 'contestObj': {} });
  }

  markPinContest = (e, contest, contest_index) => { 
   
    e.stopPropagation();
    if (window.confirm("Are you sure want to mark pin ?")) {
      this.setState({ posting: true })
      let params = { "contest_id": contest.contest_id,collection_master_id:contest.collection_master_id };
      WSManager.Rest(NC.baseURL + NC.MARK_PIN_CONTEST, params).then((responseJson) => {
        if (responseJson.response_code === NC.successCode) {
          let contestList = _.cloneDeep(this.state.contestList);
          contestList[contest_index]['is_pin_contest'] = '1';
          this.setState({ 'contestList': contestList });

          notify.show(responseJson.message, "success", 5000);
        } else {
          notify.show(responseJson.message, "error", 3000);
        }
        this.setState({ posting: false })
      })
    } else {
      return false;
    }
  }

  removePinContest = (e, contest, contest_index) => {
    e.stopPropagation();
    if (window.confirm("Are you sure want to remove pin ?")) {
      this.setState({ posting: true })
      let params = { "contest_id": contest.contest_id, 'is_pin_contest': '0',collection_master_id:contest.collection_master_id };
      WSManager.Rest(NC.baseURL + NC.MARK_PIN_CONTEST, params).then((responseJson) => {
        if (responseJson.response_code === NC.successCode) {
          let contestList = _.cloneDeep(this.state.contestList);
          contestList[contest_index]['is_pin_contest'] = '0';
          this.setState({ 'contestList': contestList });

          notify.show(responseJson.message, "success", 5000);
        } else {
          notify.show(responseJson.message, "error", 3000);
        }
        this.setState({ posting: false })
      })
    } else {
      return false;
    }
  }

  sortContestList = (e, sort_field) => {
    let contestParams = _.cloneDeep(this.state.contestParams);
    let sort_order = contestParams.sort_order;
    if (contestParams.sort_field == sort_field) {
      if (sort_order == "DESC") {
        sort_order = "ASC";
      } else {
        sort_order = "DESC";
      }
    } else {
      sort_order = "DESC";
    }

    contestParams['sort_field'] = sort_field;
    contestParams['sort_order'] = sort_order;
    this.setState({ 'contestParams': contestParams }, function () {
      this.GetContestList();
    });
  }

  toggleContestPromoteModal = (key, val) => {
    if (!NC.ALLOW_COMMUNICATION_DASHBOARD) {
      this.setState({
        promote_model: true
      });
      return false;
    }


    var params = {};
    params.email_template_id = 2;

    params.contest_id = val.contest_id;
    params.all_user = 1;
    params.for_str = 'for Contest ' + val.contest_name + '(' + val.home + ' vs ' + val.away + ' ' + moment(val.season_scheduled_date).format("YYYY-MM-DD hh:mm A") + ')';
    const stringified = queryString.stringify(params);

    this.props.history.push(`/marketing/user_segmentation?${stringified}`);
    return false;
  }

  PromoteContestHide = () => {
    this.setState({
      contest_promote_model: false
    });
  }

  PromoteHide = () => {
    this.setState({
      promote_model: false
    });
  }
  
  handlePageChange(current_page) {
    let contestParams = this.state.contestParams;
    contestParams['currentPage'] = current_page;    
    this.setState({ contestParams: contestParams},
        function () {
            this.GetContestList(); 
        });
  }

  setMatchData=(data)=>{
    let leagueId = JSON.parse(JSON.stringify(data.season_ids))
    leagueId = leagueId.split(',')
    let list =  this.state.matchList.filter(obj => leagueId.includes(obj.season_id))
    return list
  }

  handleJsonParser(data){
    try{
      return JSON.parse(data)
    }
    catch{
      return data
    }
  }

  render() {
    let {
      leagueList,
      groupList,
      statusList,
      contestList,
      contestObj,
      fixtureList,
    } = this.state
    let {int_version} = HF.getMasterData()

    return (
      <div className="animated fadeIn contestlist-dashboard">
        <Col lg={12}>
          <Row className="dfsrow">
            <h2 className="h2-cls">Contests Dashboard</h2>
          </Row>
        </Col>
        <Row>
          <Col xs="12" sm="12" md="12" className="contest-dashboard-dropdown">
            <label className="float-left form-group filter-label">Filter By - </label>
            <FormGroup className="league-filter select-wrapper">
              <Select
                className=""
                id="league_id"
                name="league_id"
                placeholder="Select League"
                value={this.state.contestParams.league_id}
                options={leagueList}
                onChange={(e) => this.handleSelect(e, 'league_id')}
              />
            </FormGroup>
            <FormGroup className="league-filter fixture-filter select-wrapper">
              <Select
                className=""
                id="league_id"
                name="league_id"
                placeholder={int_version == "1" ? "Select Game" : "Select Fixture"}
                value={this.state.contestParams.season_game_uid}
                options={fixtureList}
                onChange={(e) => this.handleSelectFixture(e, 'season_game_uid')}
              />
            </FormGroup>
            <FormGroup className="league-filter select-wrapper">
              <Select
                className=""
                id="group_id"
                name="group_id"
                placeholder="Select Group"
                value={this.state.contestParams.group_id}
                options={groupList}
                onChange={(e) => this.handleSelect(e, 'group_id')}
              />
            </FormGroup>
            <FormGroup className="league-filter select-wrapper">
              <Select
                className=""
                id="status"
                name="status"
                placeholder="Select Status"
                value={this.state.contestParams.status}
                options={statusList}
                onChange={(e) => this.handleSelect(e, 'status')}
              />
            </FormGroup>
            <FormGroup className="float-right">
              <InputGroup className="search-wrapper">
                <i className="icon-search" onClick={() => this.SearchContest()}></i>
                <Input type="text" id="keyword" name="keyword" value={this.state.contestParams.keyword} onChange={(e) => this.handleFieldVal(e, 'keyword')} onKeyPress={event => { if (event.key === 'Enter') { this.SearchContest() } }} placeholder="Enter Contest name" />
              </InputGroup>
            </FormGroup>
          </Col>
        </Row>
        <Row>
          <Col xs="12" lg="12" >
            <div className="contestcard">
              <CardBody>

                <Table className="communication-table">
                  <thead>
                    <tr>
                      <th className="contest-column">
                        <div className="dropdown" onClick={(e) => this.sortContestList(e, 'contest_name')}>
                          <button className="contests dropdown-toggle contest-dashboard-btn" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Contests
                              </button>
                          {
                            this.state.contestParams.sort_field == 'contest_name' && this.state.contestParams.sort_order == 'DESC' &&
                            <i className="fa fa-sort-desc"></i>
                          }
                          {
                            this.state.contestParams.sort_field == 'contest_name' && this.state.contestParams.sort_order == 'ASC' &&
                            <i className="fa fa-sort-asc"></i>
                          }
                        </div>
                      </th>
                      <th onClick={(e) => this.sortContestList(e, 'entry_fee')}>
                        <div className="dropdown">
                          <button className="dropdown-toggle contest-dashboard-btn" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Entry Fee
                              </button>
                          {
                            this.state.contestParams.sort_field == 'entry_fee' && this.state.contestParams.sort_order == 'DESC' &&
                            <i className="fa fa-sort-desc"></i>
                          }
                          {
                            this.state.contestParams.sort_field == 'entry_fee' && this.state.contestParams.sort_order == 'ASC' &&
                            <i className="fa fa-sort-asc"></i>
                          }
                        </div>
                      </th>

                      <th onClick={(e) => this.sortContestList(e, 'minimum_size')}>
                        <div className="dropdown">
                          <button className="dropdown-toggle contest-dashboard-btn" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Participants
                              </button>
                          {
                            this.state.contestParams.sort_field == 'minimum_size' && this.state.contestParams.sort_order == 'DESC' &&
                            <i className="fa fa-sort-desc"></i>
                          }
                          {
                            this.state.contestParams.sort_field == 'minimum_size' && this.state.contestParams.sort_order == 'ASC' &&
                            <i className="fa fa-sort-asc"></i>
                          }
                        </div>
                      </th>

                      <th onClick={(e) => this.sortContestList(e, 'total_user_joined')}>
                        <div className="dropdown">
                          <button className="dropdown-toggle contest-dashboard-btn" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Entries
                              </button>
                          {
                            this.state.contestParams.sort_field == 'total_user_joined' && this.state.contestParams.sort_order == 'DESC' &&
                            <i className="fa fa-sort-desc"></i>
                          }
                          {
                            this.state.contestParams.sort_field == 'total_user_joined' && this.state.contestParams.sort_order == 'ASC' &&
                            <i className="fa fa-sort-asc"></i>
                          }
                        </div>
                      </th>

                      <th onClick={(e) => this.sortContestList(e, 'prize_pool')}>
                        <div className="dropdown">
                          <button className="dropdown-toggle contest-dashboard-btn" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Winnings
                              </button>
                          {
                            this.state.contestParams.sort_field == 'prize_pool' && this.state.contestParams.sort_order == 'DESC' &&
                            <i className="fa fa-sort-desc"></i>
                          }
                          {
                            this.state.contestParams.sort_field == 'prize_pool' && this.state.contestParams.sort_order == 'ASC' &&
                            <i className="fa fa-sort-asc"></i>
                          }
                        </div>
                      </th>

                      <th>
                        <div className="dropdown">
                          <button className="dropdown-toggle contest-dashboard-btn" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Winners
                              </button>
                        </div>
                      </th>

                      <th onClick={(e) => this.sortContestList(e, 'is_pin_contest')}>
                        <div className="dropdown">
                          <button className="dropdown-toggle contest-dashboard-btn" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Pin Contest
                              </button>
                          {
                            this.state.contestParams.sort_field == 'is_pin_contest' && this.state.contestParams.sort_order == 'DESC' &&
                            <i className="fa fa-sort-desc"></i>
                          }
                          {
                            this.state.contestParams.sort_field == 'is_pin_contest' && this.state.contestParams.sort_order == 'ASC' &&
                            <i className="fa fa-sort-asc"></i>
                          }
                        </div>
                      </th>
                      {/* <th></th> */}
                    </tr>
                  </thead>

                </Table>

              </CardBody>
            </div>
          </Col>
        </Row>
        {contestList.map((item, contest_index) => {
          let matchData = this.setMatchData(item)
          item['fixture_list']=matchData
          console.log('matchData',matchData)
          console.log('item',item)
          return (
            <Row>
              <Col xs="12" lg="12" className="collection-vd">
                {item.fixture_list.length > 1 &&
                  <div className="collection_vertically h91">Match club <i className="icon-info"></i></div>
                }
                <Card className="recentcom">
                  <CardBody>
                    <Table responsive ClassName="tablecontest">
                      <tr >
                        <td className="contest-column">
                          <p className="contest-table-p">
                            <span className="line-text-ellipsis" style={{ WebkitBoxOrient: 'vertical' }}> {item.contest_name}</span>

                            <span className="alphabets-icon">
                              {
                                item.guaranteed_prize == '2' &&
                                <i className="icon-g contest-type"></i>
                              }
                              {
                                item.multiple_lineup > 1 &&
                                <i className="icon-m contest-type"></i>
                              }
                              {
                                item.is_auto_recurring == "1" &&
                                <i className="icon-r contest-type"></i>
                              }
                            </span>
                          </p>
                          <div className="carddiv contest-listtable">
                            {item.home_flag &&
                              <div>
                                <img className="cardimgdfs mr-3" src={NC.S3 + NC.FLAG + item.home_flag}></img>
                                <span className="livcardh3dfs">{item.home + ' vs ' + item.away}</span>
                                <img className="cardimgdfs xfloat-right" src={NC.S3 + NC.FLAG + item.away_flag}></img>
                              </div>
                            }
                            {console.log('first',item.fixture_list)}
                            {!item.home_flag &&
                              <div className={item.fixture_list.length > 1 ? "showFixture" : "showFixture1"}>
                                <img className="cardimgdfs mr-3" src={NC.S3 + NC.FLAG + item.fixture_list[0].home_flag}></img>
                                <span className="livcardh3dfs">{item.fixture_list[0].home + ' vs ' + item.fixture_list[0].away}</span>
                                <img className="cardimgdfs xfloat-right" src={NC.S3 + NC.FLAG + item.fixture_list[0].away_flag}></img>

                                {item.fixture_list.length > 1 &&
                                  <span> +{item.fixture_list.length - 1}</span>
                                }
                                <div id="showMoreFixture">
                                  {console.log('item.fixture_list',item.fixture_list)}
                                  {item.fixture_list.map((fix_item, fix__index) => (
                                    <div>
                                      <img className="cardimgdfs mr-3" src={NC.S3 + NC.FLAG + fix_item.home_flag}></img>
                                      <span className="livcardh3dfs">{fix_item.home + ' vs ' + fix_item.away}</span>
                                      <img className="cardimgdfs xfloat-right" src={NC.S3 + NC.FLAG + fix_item.away_flag}></img>
                                    </div>
                                  )
                                  )}
                                </div>

                              </div>
                            }

                          </div>
                        </td>
                        <td>
                          {
                            item.currency_type == '0' &&
                            <i className="icon-bonus"></i>
                          }
                          {
                            item.currency_type == '1' &&
                            HF.getCurrencyCode()
                          }
                          {
                            item.currency_type == '2' &&
                            <img src={Images.COINIMG} alt="coin-img" />
                          }
                          {item.entry_fee}
                        </td>
                        <td>{item.minimum_size + '-' + item.size}</td>
                        <td>{item.total_user_joined}</td>
                        <td>
                          {
                            item.prize_type == '0' &&
                            <i className="icon-bonus"></i>
                          }
                          {
                            item.prize_type == '1' &&
                            HF.getCurrencyCode()
                          }
                          {
                            item.prize_type == '2' &&
                            <img src={Images.COINIMG} alt="coin-img" />
                          }
                          {item.prize_pool}
                        </td>
                        <td>
                          <span onClick={(e) => this.viewWinners(e, item)}>{this.getWinnerCount(item)}</span>
                        </td>
                        <td>
                          {
                            item.is_pin_contest == 1 &&
                            <img style={{ marginleft: "35px" }}
                              onClick={(e) => this.removePinContest(e, item, contest_index)} src={Images.Pinpink} />
                          }
                          {
                            item.is_pin_contest != 1 &&
                            <img onClick={(e) => this.markPinContest(e, item, contest_index)} Style="width 25px;height:25px; opacity:0.5;margin-left:35px" src={Images.PIN} />
                          }
                        </td>
                        {/* <td>
                          <Button onClick={() => this.toggleContestPromoteModal(contest_index, item)} className='promote' outline disabled={item.status >= 1} color="danger">Promote</Button>
                        </td> */}
                      </tr>
                    </Table>
                  </CardBody>
                </Card>
              </Col>
            </Row>
          )
        })}
        {contestList.length <= 0 &&
          <div className="no-records">No Record Found.</div>
        }
        {contestList.length >0 &&
        <Col>
          <div className="custom-pagination lobby-paging">
            <Pagination
              activePage={this.state.contestParams.currentPage}
              itemsCountPerPage={this.state.contestParams.pageSize}
              totalItemsCount={this.state.contestParams.totalRecords}
              pageRangeDisplayed={5}
              onChange={e => this.handlePageChange(e)}
            />
          </div>
          </Col>
      }

        <div className="winners-modal-container">
          <Modal isOpen={this.state.prize_modal} toggle={() => this.closePrizeModel()} className="winning-modal">
            <ModalHeader toggle={this.toggle}>Winnings Distribution</ModalHeader>
            <ModalBody>
              <div className="distribution-container">
                {console.log('ConObjcontestObj.prize_distibution_detail',contestObj.prize_distibution_detail)}
                {
                  contestObj && contestObj.prize_distibution_detail &&
                  <table>
                    <tbody>
                    {console.log('ConObjcontestObj.prize_distibution_detail',contestObj.prize_distibution_detail.length)}
                      {contestObj.prize_distibution_detail.map((prize, idx) => (
                        <tr>
                          <td className="text-left">
                            {prize.min}
                            {
                              prize.min != prize.max &&
                              <span>-{prize.max}</span>
                            }
                          </td>
                          <td className="text-right">
                            {
                              prize.prize_type == '0' &&
                              <i className="icon-bonus"></i>
                            }
                            {
                              prize.prize_type == '1' &&
                              HF.getCurrencyCode()
                            }
                            {
                              prize.prize_type == '2' &&
                              <img src={Images.COINIMG} alt="coin-img" />
                            }
                            { prize.prize_type == '3' ? prize.min_value : prize.amount}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                }
              </div>
            </ModalBody>
            <ModalFooter>
              <Button className="close-btn" color="secondary" onClick={() => this.closePrizeModel()}>Close</Button>
            </ModalFooter>
          </Modal>
        </div>
        {
          this.state.contest_promote_model &&
          <PromoteContestModal IsPromoteContestShow={this.state.contest_promote_model} IsPromoteContestHide={this.PromoteContestHide} ContestData={{ contestObj: contestObj }} />}

        {
          this.state.promote_model &&
          <PromoteNotActive IsPromoteShow={this.state.promote_model} IsPromoteHide={this.PromoteHide} />}
      </div>
    );
  }
}

export default MultigameContestlist;