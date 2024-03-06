import React, { Component } from 'react';
import { Card, CardBody, Col, Row, Modal, ModalBody, ModalHeader, ModalFooter, FormGroup, Input, InputGroup, Button, Table } from 'reactstrap';
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
class FixtureContest extends Component {
  constructor(props) {
    super(props);
    let selected_sports_id = (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId;

    this.state = {
      selected_sport: selected_sports_id,
      contestParams: { 'sports_id': selected_sports_id, 'league_id': '', 'season_game_uid': '', 'collection_master_id': '', 'group_id': '', 'status': '', 'keyword': '', 'sort_field': 'season_scheduled_date', 'sort_order': 'DESC', currentPage: 1, pageSize: 100, pagesCount: 1 },
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
      fixtureList: [],
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
    contestParams[dropName] = (eleObj != null) ? eleObj.value : '';
    this.setState({ 'contestParams': contestParams, 'selected_league': (eleObj != null) ? eleObj.value : '' }, function () {
      if (dropName == 'league_id') {
        if (contestParams[dropName]) {
          this.GetAllFixtureList();
        } else {
          this.setState({ fixtureList: [] });
        }
      }
      this.SearchContest();
    });
  }

  handleSelectFixture = (eleObj, dropName) => {
    let contestParams = this.state.contestParams;
    contestParams[dropName] = (eleObj != null) ? eleObj.value : '';
    this.setState({ 'contestParams': contestParams }, function () {
      this.SearchContest();
    });
  }

  GetContestFilterData = () => {
    this.setState({ posting: true })
    let params = { "sports_id": this.state.selected_sport };
    WSManager.Rest(NC.baseURL + NC.GET_CONTEST_FILTER, params).then((responseJson) => {
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
    let param = {
      // "sports_id": this.state.selected_sport,
      "league_id": this.state.selected_league,
      // "items_perpage": 500,
      // "current_page": 1,
      // "sort_order": "ASC",
      // "sort_field": "season_scheduled_date",
    }
    let { int_version } = HF.getMasterData()
    this.setState({
      posting: true
    })

    // WSManager.Rest(NC.baseURL + NC.GET_ALL_FIXTURE, param).then((responseJson) => {
    WSManager.Rest(NC.baseURL + NC.GET_LEAGUE_FIXTURES, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {

        let responseJsonData = responseJson.data;
        // let fixtureList = [...responseJsonData.result.upcoming_fixture, ...responseJsonData.result.live_fixture];
        let fixtureList = [...responseJsonData];
        let tempFixtureList = [{ 'value': '', 'label': int_version == "1" ? "Select Game" : "Select Fixture"}];
        this.setState({
          posting: false,
          fixtureList: tempFixtureList,
        })
        if (fixtureList) {
          let tempFixtureList = [];
          fixtureList.map(function (lObj, lKey) {
            let d = moment(new Date(WSManager.getUtcToLocal(lObj.season_scheduled_date)));
            tempFixtureList.push({ value: lObj.collection_master_id, label: lObj.collection_name + ' (' + d.format("YYYY-DD-MM h:mm A") + ')', season_scheduled_date: lObj.season_scheduled_date });
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
    WSManager.Rest(NC.baseURL + NC.GET_CONTEST_LIST, params).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        var responseJsonData = responseJson.data.result;
        this.setState({
          contestList: responseJsonData,
          matchList: responseJson.data.match_list,

          contestParams: { ...this.state.contestParams, pagesCount: Math.ceil(responseJson.data.total / this.state.contestParams.pageSize), totalRecords: responseJson.data.total },
        })
      }
      this.setState({ posting: false })
    })
  }

  getWinnerCount(ContestItem) {

    if (ContestItem.prize_distibution_detail != '') {
      if ((ContestItem.prize_distibution_detail[ContestItem.prize_distibution_detail.length - 1].max) > 1) {
        return ContestItem.prize_distibution_detail[ContestItem.prize_distibution_detail.length - 1].max + " Winners"
      } else {
        return ContestItem.prize_distibution_detail[ContestItem.prize_distibution_detail.length - 1].max + " Winner"
      }
    } else {
      return '0 Winner';
    }
  }

  viewWinners = (e, contestObj) => {
    e.stopPropagation();
    this.setState({ 'prize_modal': true, 'contestObj': contestObj });
  }

  closePrizeModel = () => {
    this.setState({ 'prize_modal': false, 'contestObj': {} });
  }

  markPinContest = (e, contest, contest_index) => {

    e.stopPropagation();
    if (window.confirm("Are you sure want to mark pin ?")) {
      this.setState({ posting: true })
      let params = {
        "contest_id": contest.contest_id,
        collection_master_id: contest.collection_master_id
      };
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
      let params = {
        "contest_id": contest.contest_id,
        'is_pin_contest': '0',
        collection_master_id: contest.collection_master_id
      };
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
    let TempDate = moment(WSManager.getUtcToLocal(val.season_scheduled_date)).format("D-MMM-YYYY hh:mm A")
    params.email_template_id = 2;
    params.contest_id = val.contest_id;
    params.all_user = 1;

    params.for_str = 'for Contest ' + val.contest_name + '(' + val.home + ' vs ' + val.away + ' ' + TempDate + ')';
    const stringified = queryString.stringify(params);
    this.props.history.push(`/marketing/new_campaign?${stringified}`);
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
    this.setState({
      contestParams: contestParams,
    },
      function () {
        this.GetContestList();
      });

  }

  exportUser = (contestId) => {
    var query_string = 'contest_id=' + contestId;
    let sessionKey = WSManager.getToken();
    query_string += "&Sessionkey" + "=" + sessionKey;
    window.open(NC.baseURL + 'adminapi/contest/export_contest_winners?' + query_string, '_blank');
  }

  setmatchData=(data)=>{
    return this.state.matchList.filter(obj => obj.season_id == data.season_id)[0] || {}
  }

  render() {
    let {
      leagueList,
      groupList,
      statusList,
      contestList,
      contestObj,
      fixtureList,
      selected_sport,
    } = this.state
    let { int_version } = HF.getMasterData()


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
            <FormGroup className="league-filter fixture-filter  select-wrapper">
              <Select
                className=""
                id="league_id"
                name="league_id"
                placeholder= {int_version == "1" ? "Select Game" : "Select Fixture"}
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
            <div className="table-responsive common-table">


              <Table className="xcommunication-table">
                <thead>
                  <tr>
                    <th rowSpan="2" className="contest-column">
                      <div onClick={(e) => this.sortContestList(e, 'contest_name')}>
                        <div id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          Contests
                              </div>
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

                    <th rowSpan="2" onClick={(e) => this.sortContestList(e, 'entry_fee')}>
                      <div>
                        <div id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          Entry Fee
                              </div>
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

                    <th rowSpan="2" onClick={(e) => this.sortContestList(e, 'minimum_size')}>
                      <div>
                        <div id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          Participants <br />
                          (min-max)
                          </div>
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

                    <th rowSpan="2" onClick={(e) => this.sortContestList(e, 'max_bonus_allowed')}>
                      <div>
                        <div id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          Allowed <br />Bonus %
                          </div>
                        {
                          this.state.contestParams.sort_field == 'max_bonus_allowed' && this.state.contestParams.sort_order == 'DESC' &&
                          <i className="fa fa-sort-desc"></i>
                        }
                        {
                          this.state.contestParams.sort_field == 'max_bonus_allowed' && this.state.contestParams.sort_order == 'ASC' &&
                          <i className="fa fa-sort-asc"></i>
                        }
                      </div>
                    </th>

                    <th colSpan="2">
                      <div>
                        <div id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          Teams joined
                          </div>
                      </div>
                    </th>

                    <th rowSpan="2" onClick={(e) => this.sortContestList(e, 'spot_left')}>
                      <div>
                        <div id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          Spots Left
                          </div>
                        {
                          this.state.contestParams.sort_field == 'spot_left' && this.state.contestParams.sort_order == 'DESC' &&
                          <i className="fa fa-sort-desc"></i>
                        }
                        {
                          this.state.contestParams.sort_field == 'spot_left' && this.state.contestParams.sort_order == 'ASC' &&
                          <i className="fa fa-sort-asc"></i>
                        }
                      </div>
                    </th>

                    <th rowSpan="2" onClick={(e) => this.sortContestList(e, 'current_earning')}>
                      <div>
                        <div id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          Current <br /> Earning
                          </div>
                        {
                          this.state.contestParams.sort_field == 'current_earning' && this.state.contestParams.sort_order == 'DESC' &&
                          <i className="fa fa-sort-desc"></i>
                        }
                        {
                          this.state.contestParams.sort_field == 'current_earning' && this.state.contestParams.sort_order == 'ASC' &&
                          <i className="fa fa-sort-asc"></i>
                        }
                      </div>
                    </th>

                    <th rowSpan="2" onClick={(e) => this.sortContestList(e, 'potential_earning')}>
                      <div>
                        <div id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          Potential <br /> Earning
                          </div>
                        {
                          this.state.contestParams.sort_field == 'potential_earning' && this.state.contestParams.sort_order == 'DESC' &&
                          <i className="fa fa-sort-desc"></i>
                        }
                        {
                          this.state.contestParams.sort_field == 'potential_earning' && this.state.contestParams.sort_order == 'ASC' &&
                          <i className="fa fa-sort-asc"></i>
                        }
                      </div>
                    </th>
                    <th rowSpan="2"></th>
                  </tr>
                  <tr className="balance-type">
                    <th onClick={(e) => this.sortContestList(e, 'real_teams')}>
                      <div>
                        <div id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          Real
                          </div>
                        {
                          this.state.contestParams.sort_field == 'real_teams' && this.state.contestParams.sort_order == 'DESC' &&
                          <i className="fa fa-sort-desc"></i>
                        }
                        {
                          this.state.contestParams.sort_field == 'real_teams' && this.state.contestParams.sort_order == 'ASC' &&
                          <i className="fa fa-sort-asc"></i>
                        }
                      </div>
                    </th>
                    <th onClick={(e) => this.sortContestList(e, 'system_teams')}>
                      <div>
                        <div id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          System
                          </div>
                        {
                          this.state.contestParams.sort_field == 'system_teams' && this.state.contestParams.sort_order == 'DESC' &&
                          <i className="fa fa-sort-desc"></i>
                        }
                        {
                          this.state.contestParams.sort_field == 'system_teams' && this.state.contestParams.sort_order == 'ASC' &&
                          <i className="fa fa-sort-asc"></i>
                        }
                      </div>
                    </th>
                  </tr>
                </thead>
                {
                  _.map(contestList, (item, contest_index) => {
                    var mEndDate = new Date(WSManager.getUtcToLocal(item.season_scheduled_date));
                    var curDate = new Date();
                    let matchData = this.setmatchData(item)
                    let compDate = false;
                    if (curDate >= mEndDate) {
                      compDate = true;
                    }
                    return (
                      <tbody key={contest_index}>
                        <tr >
                          <td className="contest-column">
                            <div className="float-left">
                              {
                                item.is_pin_contest == 1 &&
                                <img style={{ marginLeft: "10px", cursor: "pointer" }}
                                  onClick={(e) => this.removePinContest(e, item, contest_index)} src={Images.Pinpink} />
                              }
                              {
                                item.is_pin_contest != 1 &&
                                <img
                                  onClick={(e) => this.markPinContest(e, item, contest_index)}
                                  style={{ width: "20px", height: "20px", opacity: 0.5, marginLeft: "14px", marginTop: "14px", cursor: "pointer" }}
                                  src={Images.PIN} />
                              }
                            </div>
                            <div className="float-right">
                              {/* <p className="contest-table-p"> */}
                              <div className="xline-text-ellipsis" style={{ WebkitBoxOrient: 'vertical' }}> {item.contest_name}</div>
                              <div className="contest-table-p">
                                <div className="alphabets-icon">
                                { HF.getMasterData().allow_dfs_tournament =='1' &&
                                item.tournament_count > 0 &&
                                <i style ={{color:'#20a8d8'}} title="Tournament" class="icon-tournament-jn"></i>
                                }
                             
                                  {
                                    item.guaranteed_prize == '2' &&
                                    <i className="icon-icon-g contest-type"></i>
                                  }
                                  {
                                    item.multiple_lineup > 1 &&
                                    <i className="icon-icon-m contest-type"></i>
                                  }
                                  {
                                    item.is_auto_recurring == "1" &&
                                    <i className="icon-icon-r contest-type"></i>
                                  }
                                  {
                                    item.is_reverse == "1" &&
                                    <img className="reverse-contest contest-type" title="Reverse contest" src={Images.REVERSE_FANTASY} />
                                  }                              
                                  {
                                    (HF.allowScratchWin() == '1') &&
                                    <i className="icon-SW contest-type"></i>
                                  }                                  
                                  {
                                    (HF.allowSecondInni() == '1' && item.is_2nd_inning == "1" && selected_sport == '7') &&
                                    <i className="icon-snd contest-type"></i>
                                  }
                                </div>
                              </div>
                              {/* </p> */}
                              <div className="carddiv contest-listtable">
                                <div>
                                  <img className="cardimgdfs mr-3" src={NC.S3 + NC.FLAG + matchData.home_flag}></img>
                                  <span className="livcardh3dfs">{matchData.home + ' vs ' + matchData.away}</span>
                                  <img className="cardimgdfs xfloat-right" src={NC.S3 + NC.FLAG + matchData.away_flag}></img>
                                </div>

                              </div>
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
                          <td>{item.max_bonus_allowed}%</td>
                          <td>{item.real_teams}</td>
                          <td>{item.system_teams}</td>
                          <td>{item.spot_left}</td>
                          <td>{item.current_earning}</td>
                          <td>{item.potential_earning}</td>
                          <td>
                            {
                              (item.status == "0" && !compDate) &&
                              <Button onClick={() => this.toggleContestPromoteModal(contest_index, item)} className='cd-act-btns' outline disabled={item.status >= 1} color="danger">Promote</Button>
                            }
                            {
                              item.status == "3" &&
                              <Button
                                onClick={e => this.exportUser(item.contest_id)}
                                className='cd-act-btns'
                                outline
                                color="danger"
                              >
                                Export Winners
                        </Button>
                            }
                          </td>
                        </tr>
                      </tbody>
                    )
                  })
                }
              </Table>
            </div>
          </Col>
        </Row>
        {contestList.length <= 0 &&
          <div className="no-records">No Record Found.</div>
        }
        {contestList.length > 0 &&
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
                {
                  contestObj.prize_distibution_detail &&
                  <table>
                    <tbody>
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
                              contestObj.prize_type == '0' &&
                              <i className="icon-bonus"></i>
                            }
                            {
                              contestObj.prize_type == '1' &&
                              HF.getCurrencyCode()
                            }
                            {
                              contestObj.prize_type == '2' &&
                              <img src={Images.COINIMG} alt="coin-img" />
                            }
                            {prize.amount}
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

export default FixtureContest;