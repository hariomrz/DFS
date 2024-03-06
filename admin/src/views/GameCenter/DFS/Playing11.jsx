import React, { Component } from 'react';
import { Card, CardHeader, Col, Row, Collapse, Button, Table } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../../helper/NetworkingConstants";
import WSManager from "../../../helper/WSManager";
import LS from 'local-storage';
import { notify } from 'react-notify-toast';
import HF from '../../../helper/HelperFunction';

class Playing11 extends Component {

  constructor(props) {
    super(props);
    this.toggleAccordion = this.toggleAccordion.bind(this);
    this.state = {
      selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
      league_id: (this.props.league_id) ? this.props.league_id : this.props.match.params.league_id,
      // season_game_uid: (this.props.match.params.season_game_uid) ? this.props.match.params.season_game_uid : '',
      fixtureDetail: {},
      accordion: [],
      posting: false,
      activeTab: (this.props.active_tab) ? this.props.active_tab : '',
      playing11: [],
      substitutePlayer: [],
      sub_list: [],
      playing11_list: [],
      season_id: (this.props.match.params.season_id) ? this.props.match.params.season_id : '',
    };
  }

  toggleAccordion(tab) {
    const prevState = this.state.accordion;
    const state = prevState.map((x, index) => tab === index ? !x : false);
    this.setState({
      accordion: state,
    });
  }

  componentDidMount() {
    this.GetFixtureTeamDetail();
    this.getSportsList();
  }

  // GET LEAGUE TEAM DETAIL
  GetFixtureTeamDetail = () => {
    let { league_id, selected_sport, season_id } = this.state
    let param = {
      "league_id": league_id,
      "sports_id": selected_sport,
      "season_id": season_id
    }
    this.setState({ posting: true })
    WSManager.Rest(NC.baseURL + NC.GET_SEASON_TEAMS_AND_ROSTERS, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        let teams_length = responseJson.data.team_list.length;
        let accordion = [];
        for (let j = 0; j < teams_length; j++) {
          accordion.push(false);
        }
        this.setState({
          posting: false,
          accordion: accordion,
          fixtureDetail: responseJson.data,
          playing11_list: this.getPlayerList(responseJson.data),
          // sub_list: responseJson.data.season_data.playing_list1

        },()=>{
          console.log('playing11_list',this.state.playing11_list)
        })
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        WSManager.logout();
        this.props.history.push('/login');
      }
    })
  }

  getPlayerList=(data)=>{
    let tmpdata = []
    console.log('data.team_list[0]',data.team_list)
    console.log('data.team_list[0]',data.team_list[0])
    _.map(data.team_list[0].roster_list, (obj,idx)=>{
      if(obj.is_playing == 1){
        tmpdata.push(obj.player_id)
      }
    })
    _.map(data.team_list[1].roster_list, (obj,idx)=>{
      if(obj.is_playing == 1){
        tmpdata.push(obj.player_id)
      }
    })
    console.log('tmpdata',tmpdata)
    return tmpdata
  }

  handleFieldVal = (e, tindex, rindex, field, _roster) => {
    const fixtureDetailClone = _.cloneDeep(this.state.fixtureDetail)
    const { team_list } = fixtureDetailClone

    const team_list_clone = _.map(team_list, (_player, idx) => {
      if (idx === tindex) {
        _.map(_player.roster_list, (roster, i) => {
          if (i === rindex) {
            roster.is_playing = false;
            roster.is_sub = false;

            if (field == 'playing11') {
              let playing11_list = this.state.playing11_list //_player.roster_list
              roster.is_playing = _roster.is_playing ? false : true;
              _.map(_player.roster_list, (ply, i) => {
                if (ply.is_playing && playing11_list.indexOf(ply.player_id) == -1) {
                  playing11_list.push(ply.player_id)
                }
                if (!ply.is_playing) {
                  let index = playing11_list.indexOf(ply.player_id);
                  if (index > -1) {
                    playing11_list.splice(index, 1);
                  }
                }
              })
              this.setState({ playing11_list: playing11_list })
            }

            if (field == 'substitute') {
              let sub_list = this.state.sub_list //_player.roster_list
              roster.is_sub = _roster.is_sub ? false : true;

              _.map(_player.roster_list, (ply, i) => {
                if (ply.is_sub && sub_list.indexOf(ply.player_id) == -1) {
                  sub_list.push(ply.player_id)
                }
                if (!ply.is_sub) {
                  let index = sub_list.indexOf(ply.player_id);
                  if (index > -1) {
                    sub_list.splice(index, 1);
                  }
                }
              })
              this.setState({ sub_list: sub_list })
            }

          }
          return roster
        })
      }
      return _player
    })

    this.setState({
      fixtureDetail: { ...fixtureDetailClone, team_list: team_list_clone }
    })
  }


  getSportsList() {
    WSManager.Rest(NC.baseURL + NC.GET_ALL_SPORTS, {}).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        this.setState({ 'sportslist': responseJson.data })
      }
    })
  }


  // PUBLISH FIXTURE
  PublishFixture(season_data) {
    if (window.confirm("Are you sure?")) {
      let { selected_sport, sportslist, playing11, fixtureDetail, sub_list, playing11_list } = this.state
      let spt = selected_sport;
      let team_player_count = null;

      _.map(sportslist, function (item) {
        if (item.sports_id == spt) {
          team_player_count = 2 * item.team_player_count;
        }
      });


      let param = {
        // season_game_uid: fixtureDetail.season_data.season_game_uid,
        season_id: fixtureDetail.season_id,
        league_id: fixtureDetail.league_id,
        playing_list: playing11_list,
        substitute_list: sub_list,
        sports_id: selected_sport
      }
      this.setState({ posting: true })
      WSManager.Rest(NC.baseURL + NC.PLAYING11, param).then((responseJson) => {
        if (responseJson.response_code === NC.successCode) {
          notify.show(responseJson.message, "success", 5000);
          this.GetFixtureTeamDetail();
        }
        else if (responseJson.response_code == NC.sessionExpireCode) {
          WSManager.logout();
          this.props.history.push('/login');
        } else {
          notify.show(responseJson.message, "error", 5000);
        }
      })
    } else {
      return false;
    }
  }

  render() {
    const { fixtureDetail, accordion} = this.state;
    let { int_version } = HF.getMasterData()
    return (
      <div className="animated fadeIn salary-review">
        {!_.isEmpty(fixtureDetail) &&
          <Row>
            <Col lg={5}>

              {/* <div className="carddiv"> */}
                <Col className="match-with">
                  <img
                    className="cardimgdfs"
                    src={NC.S3 + NC.FLAG + fixtureDetail.home_flag}>
                  </img>
                  <Col className="pl-1 pr-1">
                    <h3 className="livcardh3dfs">{(fixtureDetail.home) ? fixtureDetail.home : 'TBA'} VS {(fixtureDetail.away) ? fixtureDetail.away : 'TBA'}</h3>
                    <h6 className="livcardh6dfs">
                      {/* {WSManager.getUtcToLocalFormat(fixtureDetail.season_data.fixture_date_time, 'D-MMM-YYYY hh:mm A')} */}
                      {HF.getFormatedDateTime(fixtureDetail.fixture_date_time, 'D-MMM-YYYY hh:mm A'
                      )}
                    </h6>
                    <h6 className="livcardh6dfs">{fixtureDetail.league_name}</h6>
                  </Col>
                  <img className="cardimgdfs" src={NC.S3 + NC.FLAG + fixtureDetail.away_flag}></img>
                </Col>
              {/* </div> */}
            </Col>
          </Row>
        }

        <Col lg={12}>
          <Row className="dfsrow">
            <label className="dfssports">Daily Fantasy Sports</label>
            <label className="backtofixtures" onClick={() => this.props.history.push('/game_center/DFS?tab=2')}> {'<'} {int_version == "1" ? "Back to Games" : "Back to Fixtures"}</label>
          </Row>
        </Col>
        <div className="border-bottom">
        </div>
        <Row>
          <Col lg={4}>
            <label className="salaryreview">Select Playing 11</label>
            <p className="salaryreview-para">Finalize your playing11 </p>
          </Col>
          <Col lg={8}>
            {!_.isEmpty(fixtureDetail.team_list) && !_.isEmpty(fixtureDetail) &&

              <Row className="verifyrow verifyrow-top pull-right">
                <Col lg={12}>
                  {
                    fixtureDetail.is_published != 1 &&
                    <Button className='verify btn-secondary-outline' onClick={() => this.PublishFixture(fixtureDetail)}
                    >Save</Button>
                  }
                </Col>
              </Row>
            }
          </Col>
        </Row>
        {
          !_.isEmpty(fixtureDetail.team_list)
            ?
            _.map(fixtureDetail.team_list, (item, index) => {
              return (
                <React.Fragment key={"team-" + index}>
                  <div id={"accordion" + index} className="salary-card">
                    <Card onClick={() => this.toggleAccordion(index)} aria-expanded={accordion[index]} aria-controls={"collapse-" + index}>
                      <CardHeader id={"heading-" + index} >
                        <div className="salary-cardheader">
                          <a className="text-left pt-3 pb-3" onClick={() => this.toggleAccordion(index)} aria-expanded={accordion[index]} aria-controls={"collapse-" + index}>
                            <img src={item.flag_url} className="sal-team-img" />
                            <label className="team-label">{item.team_name}</label>
                          </a>
                          <div className="salary-btn">
                            <div className={(accordion[index] ? 'rotateShape' : '')}>
                              <i className="icon-Shape"></i>
                            </div>
                          </div>
                        </div>

                      </CardHeader>
                    </Card>
                    <Collapse isOpen={accordion[index]} data-parent={"#accordion" + index} id={"collapse-" + index} aria-labelledby={"heaging-" + index}>
                      <Table responsive className="salaryreview-table">
                        <thead>
                          <tr>
                            <th width="20%" >Playing 11</th>
                            <th width="20%" >Substitute Player</th>
                            <th width="30%" >Player Name</th>
                            <th width="30%" >Player Display Name</th>
                            <th width="10%">Position</th>
                            <th width="10%" className="">Salary</th>
                          </tr>
                        </thead>
                        <tbody>
                          {
                            !_.isEmpty(item.roster_list)
                              ?
                              _.map(item.roster_list, (roster, rindex) => {
                                return (

                                  <tr key={rindex}>
                                    <td className="text-left">
                                      <input checked={roster.is_playing} type="checkbox" onChange={(e) => this.handleFieldVal(e, index, rindex, 'playing11', roster)} name={roster.player_id} />
                                    </td>
                                    <td className="text-left">
                                      <input checked={roster.is_sub} type="checkbox" onChange={(e) => this.handleFieldVal(e, index, rindex, 'substitute', roster)} name={roster.player_id} />
                                    </td>
                                    <td className="text-left">{roster.full_name}</td>
                                    <td className="text-left">{roster.display_name ? roster.display_name : '--'}</td>
                                    <td className="text-left">
                                      {roster.position}
                                    </td>
                                    <td className="text-left">
                                      {roster.salary}
                                    </td>
                                  </tr>
                                )
                              })
                              :
                              <tr>
                                <td colSpan="4" className="text-center">No Record Found</td>
                              </tr>
                          }
                        </tbody>
                      </Table>
                    </Collapse>
                  </div>
                </React.Fragment>
              )
            })
            :
            <Col md={12}>
              <div className="no-records">No Record Found.</div>
            </Col>
        }
        {!_.isEmpty(fixtureDetail.team_list) && !_.isEmpty(fixtureDetail) &&
          <Row className="verifyrow mt-5 pull-right">
            <Col lg={12}>

              <Button className='verify btn-secondary-outline' onClick={() => this.PublishFixture(fixtureDetail)}
              >Save</Button>
            </Col>
          </Row>
        }
      </div>
    );
  }
}

export default Playing11;
