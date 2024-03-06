import React, { Component, Fragment } from "react";
import { Button, Row, Col, FormGroup, Input, InputGroup, Card, CardBody, Tooltip, Table } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import Select from 'react-select';
import LS from 'local-storage';
import _ from 'lodash';
import Images from '../../components/images';
import moment from 'moment';
import { notify } from 'react-notify-toast';
import { MomentDateComponent } from "../../components/CustomComponent";
import HF from '../../helper/HelperFunction';


class EditMiniLeagueFixture extends Component {

    constructor(props) {
        super(props);

        this.state = {
            contestTemplate: { 'name':'','league_id': [], 'multiple_lineup': '1', 'entry_fee_type': '1', 'max_bonus_allowed': '0', 'prize_type': '1', 'prize_pool_type': '1', "master_contest_type_id": "1", "group_id": "1", "is_auto_recurring": false, 'site_rake': 0, 'custom_total_percentage': '100', 'custom_total_amount': '0','prize_value_type':'0','is_tie_breaker':true,'sponsor_name': '', 'sponsor_logo': '', 'sponsor_link': '', 'set_sponsor': 0 },
            mini_league_uid: (this.props.match.params.mini_league_uid) ? this.props.match.params.mini_league_uid : '',
            selected_league: "",
            league_start_date: "",
            league_end_date: "",
            leagueList: [],
            leagueListM: [],
            selectSetPrize:false,
            selectUnsetPrize:true,
            fixtureList: [],
            selectedfixtureList:[],
            fixtureFilter: [{ label: "All", id: 1 }, { label: "Selected", value: 2 }, { label: "Unselected", value: 3 }],
            fixtureFilterSelected: { label: "All", value: 1 },
            fixtureMainList: [],
            fixtureDetail: {},
            name: '',
            accordion: [],
            activeTab: 1,
            
            posting: false,
            keyword: '',
            selectAll: false,

            dropdownOpen: new Array(19).fill(false),
            selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
            };
    }

    componentDidMount() {
        this.GetAllLeagueList();
        this.GetMiniLeagueDetail();
    }
    GetMiniLeagueDetail = () => {
      this.setState({ posting: true })
      WSManager.Rest(NC.baseURL + NC.GET_MINILEAGUE_DETAIL, { "sports_id": this.state.selected_sport,"mini_league_uid":this.state.mini_league_uid}).then((responseJson) => {
          if (responseJson.response_code === NC.successCode) {
              responseJson = responseJson.data;
              let contestTemplate =this.state.contestTemplate; 
              contestTemplate['name'] = responseJson.mini_league_name;
              contestTemplate['sponsor_name'] = responseJson.sponsor_name;
              contestTemplate['sponsor_logo'] = responseJson.sponsor_logo;
              contestTemplate['sponsor_link'] = responseJson.sponsor_link;
              contestTemplate['mini_league_uid'] = responseJson.mini_league_uid;
              contestTemplate['set_sponsor'] = (responseJson.sponsor_name!="")?'1':'0';
              
              
              let season_game_uid = [];
              _.map(responseJson.season_list, (item, index) => {
                  
                      season_game_uid.push(item);
              })
              this.setState({
                  posting: false,
                  miniLeagueDetail:responseJson,
                  selected_league:responseJson.league_id,
                  contestTemplate:contestTemplate,
                  season_game_uids :season_game_uid,
                  
              },()=>{
              
                this.getLeagueSeasion();
     
              
              })
              //this.GetMiniLeagueLeaderboard();
              
          } else if (responseJson.response_code == NC.sessionExpireCode) {
              WSManager.logout();
              this.props.history.push('/login');
          }
          this.setState({ posting: false })
      }).catch((e) => {
          this.setState({ posting: false })
      })
  }
    


   
    
    GetAllLeagueList = () => {
        this.setState({
            posting: true
        })
        WSManager.Rest(NC.baseURL + NC.GET_LEAGUE_LIST_MINILEAGUE, { "sports_id": this.state.selected_sport }).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                responseJson = responseJson.data;
                this.setState({
                    posting: false
                }, () => {
                    this.createLeagueList(responseJson);
                })
            } else if (responseJson.response_code == NC.sessionExpireCode) {
                WSManager.logout();
                this.props.history.push('/login');
            }
            this.setState({
                posting: false
            })
        }).catch((e) => {
            this.setState({
                posting: false
            })
        })
    }

    createLeagueList = (list) => {
        let leagueArr = list;
        let tempArr = [];

        leagueArr.map(function (lObj, lKey) {
            tempArr.push({ value: lObj.league_id, label: lObj.league_name });
        });
        this.setState({ leagueListM: list, leagueList: tempArr });
    }

    Create = () => {
        
        if (this.getSelectedLeague().length <= 0) {
            notify.show("Please select atleast one fixture.", "error", 3000);
            return false;
        }
        
        this.setState({ posting: true}) 
        
        WSManager.Rest(NC.baseURL + NC.UPDATE_MINILEAGUE_FIXTURE, { 
            "mini_league_uid": this.state.contestTemplate.mini_league_uid,
            "seasons":this.getSelectedLeague(),
        }).then((responseJson) => {
            this.setState({ posting: false })
            if (responseJson.response_code === NC.successCode) {
                notify.show(responseJson.message, "success", 5000);
                responseJson = responseJson.data;
                this.props.history.push({ pathname: '/game_center/DFS/', search: '?fixtab=2' });
            } else if (responseJson.response_code == NC.sessionExpireCode) {
                WSManager.logout();
                this.props.history.push('/login');
            }

            }).catch((e)=>{
                this.setState({ posting: false })
            })
    }


    handleFieldSearch = (e) => { 
    
        if (e) {
            
            let name = '';
            let value = '';
            name = e.target.name;
            value = e.target.value;
            
            this.setState({
                'keyword': value
              }, function () {   
                this.search();

              })
        }
    }

    handleLeague = (value, dropName) => {
        if (value) {
            if (dropName == "selected_league") {
                this.setState({ selected_league: value.value, fixtureList: [], fixtureMainList: [] }, function () {
                    this.getLeagueSeasion();
                    this.setState({
                        league_start_date: this.getSeasionDate('start'),
                        league_end_date: this.getSeasionDate('end')
                    });
                });
            }
        }
    }


    getLeagueSeasion = () => {


        this.setState({
            posting: true
        })
        WSManager.Rest(NC.baseURL + NC.GET_LEAGUE_SEASIONS_MINILEAGUE, {
            "league_id": this.state.selected_league,team_uid:this.state.team_uid
        }).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                responseJson = responseJson.data;
                let fixtureList =[];
                let selectedfixtureList = [];
                
                _.map(responseJson.season_list, (item, index) => {
                  item['is_selected']=false;
                  _.map(this.state.season_game_uids, (item2, index) => {
                    
                    if (item.season_game_uid==item2.season_game_uid)
                    item['is_selected']=true;
                    //return false;
                  })
                  if(item['is_selected']==false){

                  fixtureList.push(item);
                  }

                })
                _.map(this.state.season_game_uids, (item2, index) => {
                    
                    item2['is_selected']=true;
                    selectedfixtureList.push(item2);
                  })

                
                this.setState({
                    posting: false,
                    fixtureList: fixtureList,
                    fixtureMainList: fixtureList,
                    selectedfixtureList:selectedfixtureList
                }, () => {

                })
            } else if (responseJson.response_code == NC.sessionExpireCode) {
                WSManager.logout();
                this.props.history.push('/login');
            }
            this.setState({
                posting: false
            })
        }).catch((e) => {
            this.setState({
                posting: false
            })
        })
    }

    getSeasionDate(type) {
        let selecteditem;
        this.state.leagueListM.map((item, index) => {
            if (item.league_id == this.state.selected_league) {
                selecteditem = item;
            }
        })

        if (type == 'start') {
            return selecteditem.league_schedule_date;
        } else {
            

            return selecteditem.league_last_date;
        }

    }

    handleFilter = (value, dropName) => {
        
        if (value) {
            let filteredList = [];
            let tempList = this.state.fixtureMainList;
            this.setState({ fixtureFilterSelected: value }, function () {
                if (value.value == 2) {
                    filteredList = tempList.filter(function (item) {
                        return item.is_selected
                    });
                } else if (value.value == 3) {
                    filteredList = tempList.filter(function (item) {
                        return !item.is_selected
                    });
                } else {
                    filteredList = tempList;
                }

                this.setState({ fixtureList: filteredList })

            });


        }
    }
    selectAll() {
        _.map(this.state.fixtureList, (item, index) => {
            item.is_selected = this.state.selectAll;
        })

        this.setState({
            fixtureList: this.state.fixtureList
        })

    }
    handleChkVal = (e) => {

        if (e) {
            let value = e.target.checked;

            this.setState({
                selectAll: value,
            }, function () {
                this.selectAll();
            })
        }
    }
   
    search() {
        if (this.state.keyword.length > 1) {
            var fixtureLists = this.state.fixtureMainList.filter((item) => {
              let reshome = item.home.toLowerCase();
              let resaway = item.away.toLowerCase();
              return reshome.includes(this.state.keyword.toLowerCase()) || resaway.includes(this.state.keyword.toLowerCase());
                //return item.home.toLowerCase() == this.state.keyword.toLowerCase() || item.away.toLowerCase() == this.state.keyword.toLowerCase();

            });
            this.setState({ fixtureList: fixtureLists })

        } else {
            this.setState({ fixtureList: this.state.fixtureMainList })

        }
    }

   
   
    render() {
        const {
              leagueList,
              contestTemplate,
              selectAll,
              name
              } = this.state

        return (
            <div className="create-ml-parent">
                <Row>
                    <Col md={12}>
                        <div className="screen-header">
                            <div className="sc-title">Edit Mini League</div>
                            <div
                                onClick={() => {
                                    this.props.history.goBack()
                                }}
                                className="sc-back-arrow">{'< Go Back'}
                            </div>
                        </div>
                    </Col>
                </Row>
                <div className="create-ml-parent white-box">
                    <div className="d-flex">
                        <div className="ml-first-row">
                            <label>Select League</label>
                            <Select disabled={true}
                                className="league-selector-create-tournament"
                                id="selected_league"
                                name="selected_league"
                                placeholder="Select League"
                                value={this.state.selected_league}
                                options={leagueList}
                                onChange={(e) => this.handleLeague(e, 'selected_league')}
                            />
                        </div>
                        <div className="ml-first-row">
                            <label className="select-league-label" >Mini League Name</label>
                            <input disabled={true} maxlength="30" className="tournament-name required" id="name" name="name"
                                value={contestTemplate.name} onChange={(e) => this.handleFieldVal(e, 'name', 'name')}
                                placeholder="Mini League Name"></input>

                        </div>
                    </div>

                    <div className="fixture-view">
                        {this.getSelectedLeague().length > 0 ?
                            <label className="fixture-label">Selected Fixtures
                            </label>
                            : <label className="fixture-label">Selected Fixtures</label>
                        }
                       
                        <div className="line" />
                        <div>
                            <Row>{
                                _.map(this.state.selectedfixtureList, (item, idx) => {
                                  
                                    return (

                                        <Col md={3} key={idx} >
                                            <div className="fixture-data">
                                                <img src={NC.S3 + NC.FLAG + item.home_flag} className="team-image" />
                                                <div className="center-view">
                                                    <label className="team-name">{item.home + ' vs ' + item.away}</label>
                                                    <label className="time">
                                                        {/* <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                            {HF.getFormatedDateTime(item.season_scheduled_date, "D-MMM-YYYY hh:mm A")}
                                                    
                                                    </label>
                                                </div>
                                                <img src={NC.S3 + NC.FLAG + item.away_flag} className="team-image" />

                                                {item.is_selected &&
                                                    <div className="right-selection">
                                                        <i className="icon-righttick"></i>
                                                    </div>
                                                }
                                            </div>
                                        </Col>

                                    )
                                })
                            }
                            </Row>
                        </div>

                    </div>
                    
                    <div className="fixture-view">
                        <label className="fixture-label">Select Fixtures</label>
                        <div className="fixture-view-header">
                            <div className="select-all-parent">
                                <label className="select-all">Select All</label>
                                <input type="checkbox"
                                    defaultChecked={selectAll}
                                    checked={selectAll}
                                    onChange={(e) => this.handleChkVal(e)}
                                />
                                <label className="select-all">Yes</label>
                            </div>
                            <div className="right-item">
                                <Select
                                    className="fixture-filter-selector"
                                    id="selected_league"
                                    name="selected_league"
                                    placeholder="All"
                                    value={this.state.fixtureFilterSelected}
                                    options={this.state.fixtureFilter}
                                    onChange={(e) => this.handleFilter(e, 'fixtureFilter')}
                                />
                                <FormGroup className="float-right">
                                    <InputGroup className="search-wrapper">
                                        <i className="icon-search" onClick={() => { this.search(); }}></i>
                                        <Input type="text" id="keyword" name="keyword" value={this.state.keyword} onChange={(e) => this.handleFieldSearch(e)} onKeyPress={event => { if (event.key === 'Enter') { this.search() } }} placeholder="Search" />
                                    </InputGroup>
                                </FormGroup>
                            </div>
                        </div>
                        <div className="line" />
                        <div>
                            <Row>{
                                _.map(this.state.fixtureList, (item, idx) => {
                                 
                                    return (

                                        <Col md={3} key={idx} >
                                            <div className="fixture-data" onClick={() => {
                                                item.is_selected = !item.is_selected;
                                                let isAllSelected = this.isAllSelected();


                                                this.setState({ selectAll: isAllSelected }, () => {
                                                    this.setState({ fixtureList: this.state.fixtureList }, () => { })

                                                })



                                            }}>
                                                <img src={NC.S3 + NC.FLAG + item.home_flag} className="team-image" />
                                                <div className="center-view">
                                                    <label className="team-name">{item.home + ' vs ' + item.away}</label>
                                                    <label className="time">
                                                        {/* <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                            {HF.getFormatedDateTime(item.season_scheduled_date, "D-MMM-YYYY hh:mm A")}

                                                    </label>
                                                </div>
                                                <img src={NC.S3 + NC.FLAG + item.away_flag} className="team-image" />

                                                {item.is_selected &&
                                                    <div className="right-selection">
                                                        <i className="icon-righttick"></i>
                                                    </div>
                                                }
                                            </div>
                                        </Col>

                                    )
                                })
                            }
                            </Row>
                        </div>

                    </div>
                    
                    
                </div>
                
                    <Row>
                        <Col md={12}>
                            <div className="cr-trmnt-slr">
                                <Button disabled={this.state.posting}
                                    onClick={() => {
                                        this.Create();
                                    }}
                                    className='btn-secondary-outline'>
                                    Update</Button>
                            </div>

                        </Col>

                    </Row>

            </div>

        )
    }

    getFormatedDate = (date) => {
        date = WSManager.getUtcToLocal(date);
        return moment(date).format('LLLL');
    }
    getSelectedLeague() {
        let season_game_uid = [];
        _.map(this.state.fixtureList, (item, index) => {
            if (item.is_selected)
                season_game_uid.push(item.season_game_uid);
        })
        return season_game_uid;
    }
    isAllSelected() {
        let isAllSelected = true;
        for (let i = 0; i < this.state.fixtureList.length; i++) {
            if (!this.state.fixtureList[i].is_selected) {
                isAllSelected = false;
                break;
            }
        }
        return isAllSelected;
    }
}
export default EditMiniLeagueFixture;
