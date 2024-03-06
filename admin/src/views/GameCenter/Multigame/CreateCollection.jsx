import React, { Component } from 'react';
import Select from 'react-select';
import { Card, CardBody, CardHeader, Col, Row, Button, Tooltip } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../../helper/NetworkingConstants";
import WSManager from "../../../helper/WSManager";
import moment from 'moment';
import LS from 'local-storage';
import MultiSelect from "@khanacademy/react-multi-select";
import Images from '../../../components/images';
import { notify } from 'react-notify-toast';
import { getAllSeasonWeek, getWeekSeason } from '../../../helper/WSCalling';
import SelectDropdown from "../../../components/SelectDropdown";
import HF, { _isUndefined, _isEmpty, _Map } from '../../../helper/HelperFunction';
class Createtemplate extends Component {
  constructor(props) {
    super(props);
    this.toggle = this.toggle.bind(this);
    this.toggle1 = this.toggle1.bind(this);
    this.toggle2 = this.toggle2.bind(this);
    this.state = {
      selectedLeague: [],
      selectedFixture: [],
      leagueList: [],
      fixtureList: [],
      selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
      contestTemplate: { league_id: 0, collection_name: '' },
      isShowBonusToolTip: false,
      isShowBonusToolTip1: false,
      isShowBonusToolTip2: false,
      multiSelectClassName: "multi-select",
      selectedWeek: '',
      WeekList: [],
      MatchList: [],
      selectWeekFixture: [],
      selectWeekDate: [],
      WeeklyLeagues: [],
      WeekTTOpen: false,
    };
  }

  handleSelect = (eleObj, dropName) => {
    if (eleObj != null) {
      let contestTemplate = _.clone(this.state.contestTemplate);
      contestTemplate[dropName] = eleObj.value;
      let legId = false
      if (dropName == "league_id") {
        this.GetFixtures(eleObj.value);
        legId = this.state.WeeklyLeagues.includes(eleObj.value);
      }
      this.setState({
        contestTemplate: contestTemplate,
        WeekStatus: legId,
        weekCheck: false,
        MatchList: [],
      });
    }
  }

  componentDidMount() {
    this.GetSportLeagueList();
    LS.set('isMGEnable', 1)
    if(this.state.selected_sport == 15) {
      this.props.history.push('/multigame/Fixtures')
    }
  }

  GetSportLeagueList = () => {
    this.setState({ posting: true })
    let params = {
      "sports_id": this.state.selected_sport,
      // 'for_collection': "1"
    }
    WSManager.Rest(NC.baseURL + NC.GET_MG_LEAGUE_LIST, params).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        responseJson = responseJson.data;

        let tempArr = [];
        if (!_isUndefined(responseJson)) {
          responseJson.map(function (lObj, lKey) {
            tempArr.push({ value: lObj.league_id, label: lObj.league_name });
          });
        }

        this.setState({
          leagueList: tempArr,
          WeeklyLeagues: !_isUndefined(responseJson.soccer_weekly_leagues) ? responseJson.soccer_weekly_leagues : [],
        })
      }
      this.setState({ posting: false })
    })
  }

  GetFixtures = (leagueId) => {
    this.setState({ posting: true })
    let params = {
      // "sports_id": this.state.selected_sport,
      'league_id': leagueId
    }
    WSManager.Rest(NC.baseURL + NC.GET_ALL_FIXTURE_LIST, params).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        responseJson = responseJson.data;
        let tempArr = [];
        responseJson.map(function (lObj, lKey) {
          let d = moment(new Date(WSManager.getUtcToLocal(lObj.season_scheduled_date)));
          tempArr.push({ value: lObj.season_id, label: lObj.home + ' VS ' + lObj.away + ' (' + d.format("YYYY-DD-MM h:mm A") + ')', season_scheduled_date: lObj.season_scheduled_date });
        });
        this.setState({ fixtureList: tempArr, selectedFixture: [] })
      }
      this.setState({ posting: false })
    })
  }

  handleFieldVal = (e, tindex, element_id) => {
    if (e) {
      WSManager.removeErrorClass("contest_template_form", element_id);
      let name = '';
      let value = '';
      name = e.target.name;
      value = e.target.value;
      let contestTemplate = _.cloneDeep(this.state.contestTemplate)
      contestTemplate[tindex] = value
      this.setState({
        contestTemplate: contestTemplate
      })
    }
  }

  CreateCollection = () => {
    let { selectedWeek, selectWeekFixture, selectWeekDate } = this.state
    let contestTemplate = _.cloneDeep(this.state.contestTemplate);
    if (this.state.contestTemplate.league_id == 0) {
      notify.show("Select atleast one league.", "error", 3000);
      return false
    }
    if (this.state.contestTemplate.collection_name == "") {
      notify.show("Please enter match club name.", "error", 3000);
      return false
    } else if (this.state.contestTemplate.collection_name.length < 3) {
      notify.show("Please enter atleast 3 character.", "error", 3000);
      return false
    }

    var season_schedule_dates = [];
    if (selectedWeek) {
      contestTemplate['fixture_ids'] = selectWeekFixture;
      season_schedule_dates = selectWeekDate
    } else {
      contestTemplate['fixture_ids'] = this.state.selectedFixture;
      for (var i = 0; i < this.state.fixtureList.length; i++) {
        for (var j = 0; j < this.state.selectedFixture.length; j++) {
          if (this.state.selectedFixture[j] == this.state.fixtureList[i].value) {
            season_schedule_dates.push(this.state.fixtureList[i].season_scheduled_date)
          }
        }
      }
    }

    if (contestTemplate['fixture_ids'] <= 0) {
      notify.show("Select atleast one fixture.", "error", 3000);
      return false
    }

    // contestTemplate['season_scheduled_date'] = season_schedule_dates;
    // contestTemplate['sports_id'] = this.state.selected_sport;
    if (WSManager.validateFormFields("contest_template_form")) {
      this.setState({ posting: true })
      let params = contestTemplate;
      WSManager.Rest(NC.baseURL + NC.CREATE_COLLECTION, params).then((responseJson) => {
        if (responseJson.response_code === NC.successCode) {
          notify.show(responseJson.message, "success", 5000);
          this.props.history.push({
            pathname: '/contest/createtemplatecollectioncontest/' + this.state.contestTemplate.league_id + '/' + responseJson.data.collection_master_id,
            state: { h2h_template: 0, ismultigame: true }
          })
        }
        this.setState({ posting: false })
        if(responseJson.response_code == 500){
          notify.show( responseJson.message, "error", 3000);
        }
      })

    } else {
      notify.show("Please fill required fields.", "error", 3000);
      return false;
    }

  }
  toggle() {
    this.setState({
      isShowBonusToolTip: !this.state.isShowBonusToolTip
    });
  }

  toggle1 = () => {
    this.setState({
      isShowBonusToolTip1: !this.state.isShowBonusToolTip1
    });
  }

  toggle2 = () => {
    this.setState({
      isShowBonusToolTip2: !this.state.isShowBonusToolTip2
    });
  }
  toggleWeek = () => {
    this.setState({
      WeekTTOpen: !this.state.WeekTTOpen
    });
  }

  handleWeekCheck = (e) => {
    if (e) {
      this.setState({
        weekCheck: !this.state.weekCheck,
        selectedFixture: [],
        selectedWeek: '',
      }, () => {
        this.GetWeek()
      })
    }
  }

  handleWeekChange = (value) => {
    this.setState({ selectedWeek: value.value }, () => {
      this.GetSeasonWeek()
    })
  }

  GetWeek = () => {
    let { contestTemplate, selected_sport } = this.state
    let params = {
      "sports_id": selected_sport,
      "league_id": contestTemplate.league_id
    }
    getAllSeasonWeek(params).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        let tempWeek = [];
        if (!_isUndefined(responseJson.data) && !_isEmpty(responseJson.data)) {
          responseJson.data.map(function (item) {
            let ws = moment(new Date(item.week_start)).format('DD-MM-YYYY')
            let we = moment(new Date(item.week_end)).format('DD-MM-YYYY')
            tempWeek.push({
              value: item.week,
              label: 'Week ' + item.week + ' (' + ws + '-' + we + ')'
            });
          })
          this.setState({ WeekList: tempWeek })
        }

      }
    }).catch(error => {
      notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    })
  }

  GetSeasonWeek = () => {
    let { contestTemplate, selected_sport, selectedWeek } = this.state
    let params = {
      "sports_id": selected_sport,
      "league_id": contestTemplate.league_id,
      "week": selectedWeek,
    }

    getWeekSeason(params).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        let fx_ids = []
        let fx_date = []
        if (!_isEmpty(responseJson.data.result) && !_isUndefined(responseJson.data.result)) {
          fx_ids = _.map(responseJson.data.result, 'season_game_uid')
          fx_date = _.map(responseJson.data.result, 'season_scheduled_date')
        }

        this.setState({
          MatchList: responseJson.data.result ? responseJson.data.result : [],
          selectWeekFixture: fx_ids,
          selectWeekDate: fx_date,
        })
      }
    }).catch(error => {
      notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    })
  }

  render() {
    let {
      leagueList,
      fixtureList,
      contestTemplate,
      selectedFixture,
      multiSelectClassName,
      weekCheck,
      selectedWeek,
      selected_sport,
      WeekList,
      posting,
      MatchList,
      WeekStatus,
      WeekTTOpen,
    } = this.state
    let {int_version} = HF  .getMasterData()

    const Select_Props = {
      is_disabled: false,
      is_searchable: true,
      is_clearable: false,
      menu_is_open: false,
      class_name: "week-select",
      sel_options: WeekList,
      place_holder: "Select Week",
      selected_value: selectedWeek,
      modalCallback: this.handleWeekChange
    }

    return (
      <div className="animated fadeIn create-template create-mtl-game">
        <form id="contest_template_form">
          <Row className="mb-1">
            <Col xs="12" lg="12">
              <Card className="recentcom">
                <CardHeader className="contestcreate">
                  <h5 className="DFScontest">Create Match Club</h5>
                </CardHeader>

                <CardBody className="contestcard">
                  <Row>
                    <Col md={4}>
                      <div className="form-group">
                        <label className="fixturevs">Select League
                          <span className="btn-information"><img id="TooltipExample1" className="infobtn" src={Images.INFO} />
                            <Tooltip placement="right" isOpen={this.state.isShowBonusToolTip} target="TooltipExample1" toggle={this.toggle}>
                              Select League
                            </Tooltip>
                          </span>
                        </label>
                        <Select
                          className=""
                          id="league_id"
                          name="league_id"
                          placeholder="Select League"
                          value={contestTemplate.league_id}
                          options={leagueList}
                          onChange={(e) => this.handleSelect(e, 'league_id')}
                        />
                      </div>
                    </Col>
                  </Row>
                  <Row className="mt-3">
                    <Col md={4}>
                      <div className="form-group">
                        <label className="fixturevs">Enter match club name
                          <span className="btn-information"><img id="TooltipExample2" className="infobtn" src={Images.INFO} />
                            <Tooltip placement="right" isOpen={this.state.isShowBonusToolTip1} target="TooltipExample2" toggle={this.toggle1}>
                              Enter match club name
                            </Tooltip>
                          </span>
                        </label>
                        <input minLength={30} maxLength={30} type="text" className="contestname required" id="collection_name" name="collection_name" onChange={(e) => this.handleFieldVal(e, 'collection_name', 'collection_name')} placeholder="Match club name"></input>
                      </div>
                    </Col>
                  </Row>
                  <Row className="mt-3">
                    <Col md={4}>
                      <div className="form-group multiselect-wrapper">
                        <label className="fixturevs ">Select {int_version == "1" ? "Games" : "Fixtures"}
                          <span className="btn-information"><img id="TooltipExample3" className="infobtn" src={Images.INFO} />
                            <Tooltip placement="right" isOpen={this.state.isShowBonusToolTip2} target="TooltipExample3" toggle={this.toggle2}>
                              It does not show {int_version == "1" ? "games" : "fixtures"} with pending salary review
                            </Tooltip>
                          </span>
                        </label>
                        <MultiSelect
                          disabled={weekCheck}
                          className={multiSelectClassName}
                          id="selectedFixture"
                          name="selectedFixture"
                          placeholder= {int_version == "1" ? "All Game(s)" : "All Fixture(s)"}
                          overrideStrings={{
                            selectSomeItems: int_version == "1" ? "All Game(s)" : "All Fixture(s)"
                          }}
                          options={fixtureList}
                          selected={selectedFixture}
                          onSelectedChanged={selectedFixture => this.setState({ selectedFixture })}
                        />
                      </div>
                    </Col>
                  </Row>
                  {
                    parseInt(selected_sport) === 5 &&
                    <Row className="mt-3">
                      <Col md={4}>
                        <div className="common-cus-checkbox week-match-club">
                          {/* <label className="com-chekbox-container"> */}
                          <label className={`com-chekbox-container ${WeekStatus ? '' : 'check-disable'}`}>
                            <span className="week-text">
                              Weekly match club
                            </span>
                            <input
                              disabled={!WeekStatus}
                              type="checkbox"
                              name='WeeklMmatch'
                              id='WeeklyMatch'
                              // defaultChecked={weekCheck}
                              checked={weekCheck}
                              onChange={(e) => this.handleWeekCheck(e)}
                            />
                            <span className="com-chekbox-checkmark"></span>
                          </label>
                          <span className="btn-information">
                            <img id="WeekTT" className="infobtn" src={Images.INFO} />
                            <Tooltip
                              placement="right"
                              isOpen={WeekTTOpen}
                              target="WeekTT"
                              toggle={this.toggleWeek}>Only available for English league, Spanish league , Champion league and Italian league.</Tooltip>
                          </span>
                        </div>
                      </Col>
                    </Row>
                  }
                  {
                    weekCheck &&
                    <Row className="mt-3">
                      <Col md={4}>
                        <SelectDropdown SelectProps={Select_Props} />
                      </Col>
                    </Row>
                  }
                </CardBody>
              </Card>
            </Col>
          </Row>
          {
            !_.isEmpty(selectedWeek) &&
            <Row className="mb-20">
              <Col lg={12}>
                {
                  !_isEmpty(MatchList) &&
                  _Map(MatchList, (item, index) => {
                    return (
                      <div className="common-fixture float-left" key={index}>
                        <div className="bg-card">
                          <div>
                            <img className="com-fixture-flag float-left" src={NC.S3 + NC.FLAG + item.home_flag}></img>
                            <img className="com-fixture-flag float-right" src={NC.S3 + NC.FLAG + item.away_flag}></img>
                            <div className="com-fixture-container">
                              <div className="com-fixture-name">{(item.home) ? item.home : 'TBA'} VS {(item.away) ? item.away : 'TBA'}</div>
                              <div className="com-fixture-time">
                                {/* {WSManager.getUtcToLocalFormat(item.scheduled_date_time, 'D-MMM-YYYY hh:mm A')} */}
                                {HF.getFormatedDateTime(item.scheduled_date_time, 'D-MMM-YYYY hh:mm A')}
                              </div>
                              <div className="com-fixture-title">{item.league_abbr}</div>
                            </div>
                          </div>
                        </div>
                      </div>
                    )
                  })
                }
              </Col>
            </Row>
          }
          <Card className="">
            <CardBody className="">
              <Row>
                <Col lg={12} className="">
                  <Button
                    disabled={posting}
                    onClick={() => { this.CreateCollection() }} className=' btn-secondary-outline'>Create match club</Button>
                </Col>
              </Row>
            </CardBody>
          </Card>
        </form>
      </div>
    );
  }
}

export default Createtemplate;