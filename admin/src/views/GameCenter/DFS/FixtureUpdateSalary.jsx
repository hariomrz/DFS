import React, { Component, Fragment, useEffect, useState } from 'react'
import { TabContent, TabPane, Nav, NavItem, NavLink, Row, Col, Button, Modal, ModalBody, ModalHeader, ModalFooter, FormGroup, Input } from 'reactstrap'
import Images from '../../../components/images';
import _ from 'lodash'
import { notify } from 'react-notify-toast';
import * as NC from "../../../helper/NetworkingConstants";
import WSManager from "../../../helper/WSManager";
import LS from 'local-storage';
import UpdateSalaryView from './updateSalaryView';
import { MomentDateComponent } from "../../../components/CustomComponent";
import { TITLE_PUBLISH_MATCH, MSG_PUBLISH_MATCH } from "../../../helper/Message";
import HF, { _isEmpty } from '../../../helper/HelperFunction';


class FixtureUpdateSalary extends Component {
    constructor(props) {
        super(props)
        this.state = {
            selected_sports_id: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
            activeTab: '',
            selectedPosition: 'BAT',
            selectAll: false,
            PublishModalIsOpen: false,
            saveUpdatePosting: false,

            FinalRosterList: [],
            HomeTeamCount: 0,
            AwayTeamCount: 0,
            MasterPlayerByPostition: [],
            filteredListData: [],
            PlayersPositionLength: [],
            CountryPlayersCount: [],
            All_Postion: [],
            RowPostion: [],
            Roster_Data: [],
            select_allplayer: 0,
            FixturePosting: false,
            isDynamicSetting: "0",
            DynamicSetting: {},
            invalidSetting: false,
            seasonPlayerInit: false
        }
    }

    componentDidMount() {
        this.getAllPosotions()
    }

    getAllPosotions = () => {
        const { selected_sports_id } = this.state
        let params = {
            sports_id: selected_sports_id
        }
        WSManager.Rest(NC.baseURL + NC.GET_ALL_POSITION, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                let TempPosition = []
                let defaultActPosition = ''
                _.map(ResponseJson.data, (item, idx) => {
                    if (idx === 0) {
                        defaultActPosition = item.position;
                    }

                    let PositionDict = { value: item.position, label: item.position, position_name: item.position_display_name }
                    TempPosition.push(PositionDict)
                })

                this.setState({ All_Postion: TempPosition, activeTab: defaultActPosition, RowPostion: ResponseJson.data }, () => {
                    this.getSeasonPlayers()
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }


    toggle(value) {
        this.setState({
            activeTab: value
        });
    }

    returnPositionCount = (arrayOfPosition, team_uid) => {
        var count = 0
        for (let dictPlayer of arrayOfPosition) {
            if (dictPlayer.team_uid == team_uid) {
                count++
            }
        }
        return count
    }

    getSeasonPlayers = () => {
        let params = {
            // league_id: this.props.match.params.league_id,
            // sports_id: this.state.selected_sports_id,
            season_id: this.props.match.params.season_id
        }
        WSManager.Rest(NC.baseURL + NC.GET_SEASON_PLAYERS, params).then(ResponseJson => {

            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    seasonPlayerInit: true
                })

                let ResponseData = ResponseJson.data;

                _.map(ResponseData.roster_list, (item, idx) => {
                    if (ResponseData.is_published == "0" && item.feed_verified == "1") {
                        ResponseData.roster_list[idx]["new_position"] = item.position;
                        ResponseData.roster_list[idx]["new_salary"] = item.salary;
                        ResponseData.roster_list[idx]["is_selected"] = '1';
                    }
                    else if (ResponseData.is_published == "0" && item.is_published == "1" && item.feed_verified == "0") {
                        ResponseData.roster_list[idx]["new_position"] = item.position;
                        ResponseData.roster_list[idx]["new_salary"] = item.salary;
                        ResponseData.roster_list[idx]["is_selected"] = '1';
                    }
                })
                this.setState({ Roster_Data: ResponseData }, () => {
                    const { Roster_Data } = this.state;
                    if(Roster_Data.is_published == "1") {
                        this.setState({
                            isDynamicSetting: _isEmpty(Roster_Data.setting) ? '0' : '1'
                        });
                    }
                })
            } else {
                this.setState({ Roster_Data: [] })
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    PublishMatchModalToggle = () => {
        this.setState({
            PublishModalIsOpen: !this.state.PublishModalIsOpen,
        });
    }

    publishMatchModal = () => {
        let { FixturePosting } = this.state
        return (
            <div>
                <Modal
                    isOpen={this.state.PublishModalIsOpen}
                    toggle={this.PublishMatchModalToggle}
                    className="cancel-match-modal"
                >
                    <ModalHeader>{TITLE_PUBLISH_MATCH}</ModalHeader>
                    <ModalBody>
                        <div className="confirm-msg">{MSG_PUBLISH_MATCH}</div>
                    </ModalBody>
                    <ModalFooter>
                        <Button
                            color="secondary"
                            onClick={this.publishFixture}
                            disabled={FixturePosting}
                        >Yes</Button>{' '}
                        <Button color="primary" onClick={this.PublishMatchModalToggle}>No</Button>
                    </ModalFooter>
                </Modal>
            </div>
        )
    }

    publishFixture = () => {
        this.setState({ FixturePosting: true })
        let { Roster_Data } = this.state
        let ArrOfTempNewPlayer = []

        _.map(Roster_Data.roster_list, (item, idx) => {
            if (Roster_Data.is_published == "0" && (item.is_selected == "0" || item.is_selected == "1")) {
                let tempNewPlayer = {
                    "full_name": item.full_name,
                    "display_name": item.display_name,
                    "player_team_id": item.player_team_id,
                    "player_id": item.player_id,
                    "team_league_id": item.team_league_id,
                    "salary": item.new_salary || item.salary,
                    "position": item.new_position || item.position,
                    "is_published": item.is_selected,
                }

                ArrOfTempNewPlayer.push(tempNewPlayer)
            }

            if (Roster_Data.is_published == "1" && item.is_selected == "1") {
                let tempNewPlayer = {
                    "full_name": item.full_name,
                    "display_name": item.display_name,
                    "player_team_id": item.player_team_id,
                    "player_id": item.player_id,
                    "team_league_id": item.team_league_id,
                    "salary": item.new_salary || item.salary,
                    "position": item.new_position || item.position,
                    "is_published": "1",
                }
                ArrOfTempNewPlayer.push(tempNewPlayer)
            }
        })
        //Start validation for players to be save        
        let isValid = true;
        let min_sal = 1;
        let max_sal = 20;
        if (this.state.selected_sports_id == 4) {
            min_sal = 1;
            max_sal = 50;
        }
        else if (this.state.selected_sports_id == 8) {
            min_sal = 1;
            max_sal = 20;
        }else if (this.state.selected_sports_id == 11) {
            min_sal = 1;
            max_sal = 30;
        }else if (this.state.selected_sports_id == 15) {
            min_sal = 1;
            max_sal = 30;
        }
        let errorMessage = "Please enter salary between " + min_sal + " to " + max_sal + " for player(s): ";

        _.map(ArrOfTempNewPlayer, (rosterObj, rosterIndex) => {
            if (rosterObj.is_published == "1") {
                if (rosterObj.salary < min_sal || rosterObj.salary > max_sal) {
                    isValid = false;
                    errorMessage += rosterObj.full_name + ", ";
                }
            }
        });

        if (!isValid) {
            this.setState({ FixturePosting: false })
            errorMessage = errorMessage.replace(/,\s*$/, "");
            notify.show(errorMessage, "error", 5000);
            this.PublishMatchModalToggle()
            return false;
        }
        //End validation for players to be save
        //Start code to remove player having salary less then 0
        let tempFinalPostArr = _.filter(ArrOfTempNewPlayer, (item) => {
            return (
                (item.salary > 0 || item.salary == '')
            )
        })

        let dsPos = []
        _.map(this.state.DynamicSetting.pos, obj => {
            dsPos.push(obj)
            return obj
        })
        //End code to remove player having salary less then 0
        let params = {
            // league_id: this.props.match.params.league_id,
            // sports_id: this.state.selected_sports_id,
            season_id: this.props.match.params.season_id,
            roster_list: tempFinalPostArr,
            setting: this.state.isDynamicSetting == 1 ? {...this.state.DynamicSetting, pos: _.assign.apply(_, dsPos)} : {}
        }
        WSManager.Rest(NC.baseURL + NC.PUBLISH_FIXTURE, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    PublishModalIsOpen: false,
                    FixturePosting: false,
                })
                notify.show(ResponseJson.message, "success", 3000)
                if (Roster_Data.is_published == "0") {
                    if (NC.ALLOW_DFS == 1) {
                        this.props.history.push({
                            pathname: '/contest/createtemplatecontest/' + ResponseJson.data.collection_master_id  + '/' + this.props.match.params.season_id + '/2/0',
                            state: { h2h_template: 0, isDFS: true }
                        });
                    } else {
                        this.props.history.push({ 
                            pathname: '/contest/fixturecontest/' + ResponseJson.data.collection_master_id  + '/' + this.props.match.params.season_id ,
                            state: { isDFS: true,isNPublished: false}
                        });
                    }
                } else {
                    this.props.history.push({ pathname: '/game_center/DFS/', search: '?tab=2',state: { isDFS: true } });
                }
            } else {
                this.setState({ PublishModalIsOpen: false, FixturePosting: false, })
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            this.setState({ PublishModalIsOpen: false, FixturePosting: false, })
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    isUnPublished = (item) => {
        let { Roster_Data } = this.state;
        if (Roster_Data.is_published == "0") {
            return item.feed_verified == "0" && item.is_new != "1"
        } else if (Roster_Data.is_published == "1") {
            return item.is_new != "1" && item.is_published != "1"
        }

    }
    isNewPublished = (item) => {
        return item.is_new == "1" && item.is_published == "0"
    }
    isPublished = (item) => {
        let { Roster_Data } = this.state;
        if (Roster_Data.is_published == "0") {
            return item.feed_verified == "1" && item.is_new != "1"
        } else if (Roster_Data.is_published == "1") {
            return item.is_published == "1"
        }
        return false
    }

    filterRosterByPosition_published = (type) => {
        let { Roster_Data, activeTab } = this.state;
        if (!Roster_Data.roster_list)
            return []

        let temparr = [];
        if (type == 'new_published') {
            temparr = _.filter(Roster_Data.roster_list, (item) => {
                return (
                    this.isNewPublished(item) && item.position == activeTab
                )
            })
        }
        if (type == 'published') {
            temparr = _.filter(Roster_Data.roster_list, (item) => {
                return (
                    this.isPublished(item) && item.position == activeTab
                )
            })
        }
        if (type == 'un_published') {
            temparr = _.filter(Roster_Data.roster_list, (item) => {
                return (
                    this.isUnPublished(item) && item.position == activeTab
                )
            })
        }

        return temparr
    }

    filterPubRosterByPos = (List, position) => {
        const { Roster_Data} = this.state 
        let temparr = _.filter(List.roster_list, (item) => {
            return  Roster_Data.is_tour_game == 1 ?   item.position == position :  this.isPublished(item) && item.position == position 
         })
        return temparr || []
    }

    getTeamWiseCount = (team_home_away_uid, List) => {
        let temparr = _.filter(List, (item) => {
            return (
                team_home_away_uid == item.team_uid
            )
        })
        return temparr.length || 0
    }

    updatePositionList = (updated_item, new_position) => {
        let { Roster_Data } = this.state
        let index = _.indexOf(Roster_Data.roster_list, updated_item)
        let tempRosData = Roster_Data;
        if (index >= 0) {
            tempRosData.roster_list[index]["new_position"] = new_position
            this.setState({ Roster_Data: tempRosData })
        }
    }

    updateDisNameList = (updated_item, display_name) => {
        let { Roster_Data } = this.state
        let index = _.indexOf(Roster_Data.roster_list, updated_item)
        let tempRosData = Roster_Data;
        if (index >= 0) {
            tempRosData.roster_list[index]["display_name"] = display_name
            this.setState({ Roster_Data: tempRosData })
        }
    }

    updateSalaryList = (updated_item, new_salary) => {
        let { Roster_Data } = this.state
        let index = _.indexOf(Roster_Data.roster_list, updated_item)
        let tempRosData = Roster_Data;

        let min_sal = 1;
        let max_sal = 20;
        if (this.state.selected_sports_id == 4) {
            min_sal = 1;
            max_sal = 50;
        }
        else if (this.state.selected_sports_id == 8) {
            min_sal = 1;
            max_sal = 20;
        }
        else if (this.state.selected_sports_id == 15) {
            min_sal = 1;
            max_sal = 30;
        }
        if (index >= 0) {
            if (new_salary == '') {
                tempRosData.roster_list[index]["salary"] = new_salary
                tempRosData.roster_list[index]["new_salary"] = new_salary
            }
            else if ((new_salary != 1 && new_salary != 2) && (new_salary < min_sal || new_salary > max_sal)) {

                notify.show(updated_item.full_name + ' salary should be between ' + min_sal + ' to ' + max_sal, "error", 5000);

                tempRosData.roster_list[index]["salary"] = ""
                tempRosData.roster_list[index]["new_salary"] = ""
            }
            else {
                tempRosData.roster_list[index]["salary"] = new_salary
                tempRosData.roster_list[index]["new_salary"] = new_salary
            }

        }
        this.setState({ Roster_Data: tempRosData })
    }

    updateSelectList = (updated_item, is_selected, new_published_all, type, removeKey) => {
        let { Roster_Data } = this.state
        let index = _.indexOf(Roster_Data.roster_list, updated_item)
        let tempRosData = Roster_Data;

        if (index >= 0) {
            tempRosData.roster_list[index]["is_selected"] = is_selected
            this.setState({ Roster_Data: tempRosData })
        }
    }

    updateAllSelectList = (selectedAllV) => {
        let { Roster_Data } = this.state

        let tempRosData = Roster_Data;
        let NewPublishedList = this.filterRosterByPosition_published('new_published')
        _.map(NewPublishedList, (item, idx) => {
            let index = _.indexOf(Roster_Data.roster_list, item)
            if (index >= 0) {
                tempRosData.roster_list[index]["is_selected"] = selectedAllV
            }
        })
        this.setState({ Roster_Data: tempRosData })
    }

    handleDynamicChange = () => {
        let { isDynamicSetting } = this.state
        this.setState({
            isDynamicSetting: isDynamicSetting == "1" ? "0" : "1"
        })
    }

    callbackHandler = (data, invalidSetting) => {
        this.setState({
            DynamicSetting: data,
            invalidSetting: invalidSetting
        });
    }

    

   
    render() {
        let { All_Postion, activeTab, Roster_Data, isDynamicSetting, selected_sports_id, RowPostion, invalidSetting, seasonPlayerInit} = this.state

        let NewPublishedList = this.filterRosterByPosition_published('new_published')
        let PublishedList = this.filterRosterByPosition_published('published')
        let UnPublishedList = this.filterRosterByPosition_published('un_published')

        let UpdateSalaryViewProps = {
            NewPublishedList: NewPublishedList,
            PublishedList: PublishedList,
            UnPublishedList: UnPublishedList,
            All_Postion: All_Postion,
            updatePositionList: this.updatePositionList.bind(this),
            updateDisNameList: this.updateDisNameList.bind(this),
            updateSalaryList: this.updateSalaryList.bind(this),
            updateSelectList: this.updateSelectList.bind(this),
            updateAllSelectList: this.updateAllSelectList.bind(this),
            Roster_Data: Roster_Data,
            activeTab: activeTab,
        }
        let { int_version } = HF.getMasterData()

        return (
            <Fragment>
                <div className="sc-update-salary">
                    {this.publishMatchModal()}
                    <Row>
                        {Roster_Data.is_tour_game == 1 ?
                            <>
                                <Col>
                                    <div className="bg-card-motor-sports">
                                        <div className="motor-sports-container">
                                            <div className="top-view-motor-sports">
                                                <div className={`car-type-view ${Roster_Data.league_abbr == "Formula 1" ? " formula-one" : Roster_Data.league_abbr == "Moto GP" ? " moto-gp" : Roster_Data.league_abbr == "Desert racing" ? " desert-racing" : " other-league-abbr"}`}>{Roster_Data.league_abbr}</div>
                                            </div>
                                            <div className="motor-sports-view">
                                                <img className="img-colum-view" src={NC.S3 + NC.MOTOR_SPORTS_IMG + Roster_Data.league_image} alt=""
                                                ></img>
                                                <div className="inner-view-motor-sports">
                                                    <div className="tournament-name-view">{Roster_Data.tournament_name}</div>
                                                    <div className="events-view">{Roster_Data.match_event} events</div>
                                                    <div className="date-view">
                                                        {/* {WSManager.getUtcToLocalFormat(Roster_Data.season_scheduled_date, 'D MMM YYYY hh:mm A')}  */}
                                                        {HF.getFormatedDateTime(Roster_Data.season_scheduled_date, 'D MMM YYYY hh:mm A')}

                                                        to
                                                        {/* {WSManager.getUtcToLocalFormat(Roster_Data.end_scheduled_date, 'D MMM YYYY hh:mm A')}  */}
                                                        {HF.getFormatedDateTime(Roster_Data.season_scheduled_date, 'D MMM YYYY hh:mm A')}

                                                        </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </Col>
                                <Col md={6}>
                                    {
                                        Roster_Data.is_published == "0" &&
                                        <DynamicSwitchRender isDynamicSetting={isDynamicSetting} isDisabled={Roster_Data.is_published == "1"} handler={this.handleDynamicChange}/>
                                    }
                                </Col>
                            </>
                            :
                            <>
                                <Col>
                                    <div className="season-data-container">
                                        <img className="flags-logo" src={NC.S3 + NC.FLAG + Roster_Data.home_flag} alt="" />
                                        <div className="season-data">
                                            <div className="fixture-details">{Roster_Data.home}
                                                {' vs '} {Roster_Data.away}
                                            </div>
                                            <div className="season-duration">

                                                {/* <MomentDateComponent data={{ date: Roster_Data.season_scheduled_date, format: "D MMM - hh:mm a" }} />(IST) */}
                        {HF.getFormatedDateTime(Roster_Data.season_scheduled_date, "D MMM - hh:mm a")}
                                            
                                            </div>
                                            <div className="season-duration">{Roster_Data.subtitle}</div>
                                        </div>
                                        <img className="flags-logo" src={NC.S3 + NC.FLAG + Roster_Data.away_flag} alt="" />
                                    </div>
                                </Col>
                                
                                <Col md={6}>
                                    {
                                        Roster_Data.is_published == "0" &&
                                        <DynamicSwitchRender isDynamicSetting={isDynamicSetting} isDisabled={Roster_Data.is_published == "1"} handler={this.handleDynamicChange}/>
                                    }
                                </Col>
                            </>
                        }
                        <DynamicSettings {...{
                            Roster_Data,
                            RowPostion,
                            isDynamicSetting,
                            selected_sports_id,
                            callbackHandler: this.callbackHandler,
                            isDisabled:Roster_Data.is_published == "1",
                            seasonPlayerInit
                        }} />
                    </Row>
                    <Row className="fantasy-title">
                        <Col md={6}>
                            <h2 className="h2-cls">Daily Fantasy Sports</h2>
                        </Col>
                        <Col md={6}>
                            <label className="back-to-fixtures" onClick={() => this.props.history.push({ pathname: '/game_center/DFS', search: '?tab=2' })}> {'< '}  {int_version == "1" ? "Back to Games" : "Back to Fixtures"}</label>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}><div className="players">Players</div></Col>
                    </Row>
                    {
                        Roster_Data.is_tour_game == 1 ?
                            <Col md={12}>
                                <div className="players-container">
                                    <ul>
                                        <li className="players-view">
                                            <div className="total-view">Total</div>
                                            <div className="player-number-view">{Roster_Data.roster_list ? Roster_Data.roster_list.length : 0} <span>Players</span></div>
                                        </li>
                                            {
                                                    _.map(All_Postion, (item, idx) => {
                                                       
                                                        let PubRosterByPos = this.filterPubRosterByPos((Roster_Data || []), item.value)
                                                    
                                                        return (
                                                            <li className="players-view">
                                                            <div className="total-view">{item.position_name}</div>
                                                            <div className="player-number-view">
                                                                {PubRosterByPos.length} <span>Players</span></div>
                                                            </li>
                                                        )
                                                    })
                                                }
                                        

                                    </ul>
                                </div>
                            </Col>
                            :
                            <Row>
                                <Col md={8}>
                                    <ul className="player-total-list">
                                        <li className="player-total-item">
                                            <img className="pteam-logo" src={NC.S3 + NC.FLAG + Roster_Data.home_flag} alt="" />
                                            <img className="pteam-logo" src={NC.S3 + NC.FLAG + Roster_Data.away_flag} alt="" />
                                            <div className="total-players">Total <br />{Roster_Data.roster_list ? Roster_Data.roster_list.length : 0} Players
                                            </div>
                                        </li>
                                        <li className="player-total-item">
                                            <img className="pteam-logo" src={NC.S3 + NC.FLAG + Roster_Data.home_flag} alt="" />
                                            <div className="total-players">{Roster_Data.home} <br />
                                                {this.getTeamWiseCount(Roster_Data.home_uid, (Roster_Data.roster_list || []))}{' Players'}
                                            </div>
                                        </li>
                                        <li className="player-total-item">
                                            <img className="pteam-logo" src={NC.S3 + NC.FLAG + Roster_Data.away_flag} alt="" />
                                            <div className="total-players">
                                                {Roster_Data.away} <br />
                                                {this.getTeamWiseCount(Roster_Data.away_uid, (Roster_Data.roster_list || []))}{' Players'}
                                            </div>
                                        </li>
                                    </ul>
                                </Col>
                                <Col md={4}>
                                    {
                                        Roster_Data.is_published == "0" &&
                                        <div className="progress-view-wrapper">
                                            <img src={Images.SALARY_REVIEW_BAR} alt="" />
                                            <div className="progress-text">
                                                <span>Salary Review</span> <span>Choose Template</span>
                                            </div>
                                        </div>
                                    }
                                </Col>
                            </Row>

                    }
                    {
                        Roster_Data.is_tour_game == 1 ? 

                        <Row>
                        <Col md={12}>
                            <div className="xuser-navigation p-selection-navigation">
                                <div className="driver-const-header-view">


                                    <Row>
                                        <Col md={10}>
                                            <Nav tabs>
                                                {
                                                    _.map(All_Postion, (item, idx) => {
                                                        let PubRosterByPos = this.filterPubRosterByPos((Roster_Data || []), item.value)
                                                    
                                                        return (
                                                            <NavItem key={idx}>
                                                                <NavLink
                                                                    className={activeTab === item.value ? "active" : ""}
                                                                    onClick={() => { this.toggle(item.value); }}
                                                                >
                                                                    {item.position_name}{' '}({PubRosterByPos.length} Players)<br />
                                                                    

                                                                </NavLink>
                                                            </NavItem>
                                                        )
                                                    })
                                                }
                                            </Nav>
                                        </Col>
                                    </Row>
                                </div>
                                <TabContent activeTab={activeTab}>
                                    {
                                        _.map(All_Postion, (item, idx) => {
                                            return (
                                                <TabPane key={item.value} tabId={item.value}>
                                                    <UpdateSalaryView {...UpdateSalaryViewProps} />
                                                </TabPane>
                                            )
                                        }
                                        )
                                    }

                                </TabContent>
                            </div>
                        </Col>
                    </Row>
                            :
                            <Row>
                                <Col md={12}>
                                    <div className="xuser-navigation p-selection-navigation">
                                        <div className="header-color">


                                            <Row>
                                                <Col md={10}>
                                                    <Nav tabs>
                                                        {
                                                            _.map(All_Postion, (item, idx) => {
                                                                let PubRosterByPos = this.filterPubRosterByPos((Roster_Data || []), item.value)
                                                               
                                                                return (
                                                                    <NavItem key={idx}>
                                                                        <NavLink
                                                                            className={activeTab === item.value ? "active" : ""}
                                                                            onClick={() => { this.toggle(item.value); }}
                                                                        >
                                                                            {item.value}{' '}({PubRosterByPos.length})<br />
                                                                            {Roster_Data.home} ({this.getTeamWiseCount(Roster_Data.home_uid, (PubRosterByPos || []))}){' '}
                                                                            {Roster_Data.away} ({this.getTeamWiseCount(Roster_Data.away_uid, (PubRosterByPos || []))})

                                                                        </NavLink>
                                                                    </NavItem>
                                                                )
                                                            })
                                                        }
                                                    </Nav>
                                                </Col>
                                            </Row>
                                        </div>
                                        <TabContent activeTab={activeTab}>
                                            {
                                                _.map(All_Postion, (item, idx) => {
                                                    return (
                                                        <TabPane key={item.value} tabId={item.value}>
                                                            <UpdateSalaryView {...UpdateSalaryViewProps} />
                                                        </TabPane>
                                                    )
                                                }
                                                )
                                            }

                                        </TabContent>
                                    </div>
                                </Col>
                            </Row>
                    }





                    <Row className="text-center">
                        <Col md={12}>
                            {console.log("Roster_Data",Roster_Data.roster_list)}
                            {!_.isEmpty(Roster_Data) && <Button
                                className={Roster_Data.roster_list != '' ? "btn-secondary-outline rebuplish-btn" : " rebuplish-btn-disable" }
                                onClick={this.PublishMatchModalToggle}
                                disabled={_isEmpty(Roster_Data.setting) ? (isDynamicSetting == 0 ? false : invalidSetting) : (Roster_Data.is_published == "1" || invalidSetting)}
                            >
                                {Roster_Data.is_published == "0" ?
                                    'Verify and Publish'
                                    :
                                    'Republish'
                                }
                            </Button>}
                        </Col>
                    </Row>
                </div>

            </Fragment>
        )
    }
}
export default FixtureUpdateSalary


const DynamicSwitchRender = ({isDynamicSetting, handler, isDisabled}) => {
    return (
        <div className="dynamic-settings-switch">
            <span className="module-text">Enable Dynamic Settings</span>
            <label className="global-switch default">
                <input
                    type="checkbox"
                    checked={isDynamicSetting == "0" ? false : true}
                    onChange={handler}
                    disabled={isDisabled}
                />
                <span className="switch-slide round" />
            </label>
        </div>
    )
}


const DynamicSettings = ({ Roster_Data, RowPostion, isDynamicSetting, selected_sports_id, callbackHandler, isDisabled, seasonPlayerInit }) => {
    let msData = HF.getMasterData()
    const [init, setInit] = useState(false)
    const [invalidSetting, setInvalidSetting] = useState(false)
    const [SportObj, setSportObj] = useState({})
    const [setting, setSetting] = useState({
        "max_player_per_team": 0,
        "team_player_count": 0,
        "c": 1,
        "vc": 1,
        "pos": {
            "wk_min": 0,
            "wk_max": 0,
            "bat_min": 0,
            "bat_max": 0,
            "ar_min": 0,
            "ar_max": 0,
            "bow_min": 0,
            "bow_max": 0
        }
    })

    useEffect(() => {
        const _sportObj = _.filter(msData.sports_list, obj => obj.sports_id == selected_sports_id)[0] || {}
        setSportObj(_sportObj)
        if(_isEmpty(Roster_Data) || Roster_Data.is_published == "1") {
            if(!_isEmpty(Roster_Data) && isDynamicSetting != 0) {
                let position = {}
                _.map(RowPostion, item => {
                    let pos = {
                        [item.position.toLowerCase() + '_min']: Roster_Data.setting.pos[item.position.toLowerCase() + '_min'],
                        [item.position.toLowerCase() + '_max']: Roster_Data.setting.pos[item.position.toLowerCase() + '_max'],
                    }
                    position[item.position.toLowerCase()] = pos
                    return item
                })
                setSetting(prevData => ({
                    ...prevData, 
                    ...Roster_Data.setting,
                    pos: position
                }))
            }
            return
        };
        
        setSetting(prevData => ({
            ...prevData, 
            max_player_per_team: _sportObj.max_player_per_team,
            team_player_count: _sportObj.team_player_count,
        }))
        return () => {}
    }, [msData, Roster_Data, isDynamicSetting])

    useEffect(() => {
        let position = {}
        _.map(RowPostion, item => {
            let pos = {
                [item.position.toLowerCase() + '_min']: item.number_of_players,
                [item.position.toLowerCase() + '_max']: SportObj.max_player_per_team
            }
            position[item.position.toLowerCase()] = pos
            return item
        })
        setSetting(prevData => ({
            ...prevData, 
            pos: position
        }))
        setInit(true)
        return () => {}
    }, [SportObj, RowPostion])

    useEffect(() => {
        if(init) {
            callbackHandler(setting, isInvalid())
        }
    }, [init, setting])

    // const handleCaptainSetting = (event) => {
    //     let name = event.target.name
    //     let value = event.target.value
    //     setSetting(prevData => ({
    //         ...prevData, 
    //         [name]: value
    //     }))

    // }
    const handleTeamChange = (e) => {
        let name = e.target.name
        let value = e.target.value
        setSetting(prevData => ({
            ...prevData, 
            [name]: value
        }))
    }

    const handleChange = (e) => {
        let name = e.target.name
        let id = e.target.id
        let value = e.target.value
        setSetting(prevData => ({
            ...prevData, 
            pos: { ...prevData.pos, [name]: { ...prevData.pos[name], [name + id]: value } }
        }))
    }

    const isInvalid = () => {
        const data = setting.pos
        const team_player_count = setting.team_player_count
        let sumOfMinValues = 0;
        let sumOfMaxValues = 0;

        for (const playerType in data) {
            if (data.hasOwnProperty(playerType) && data[playerType].hasOwnProperty(playerType + '_min')) {
                sumOfMinValues += parseInt(data[playerType][playerType + '_min']);
            }
            if (data.hasOwnProperty(playerType) && data[playerType].hasOwnProperty(playerType + '_max')) {
                sumOfMaxValues += parseInt(data[playerType][playerType + '_max']);
            }
        }
        const _is = sumOfMinValues > team_player_count || (setting.max_player_per_team == '' || setting.max_player_per_team == 0)
        setInvalidSetting(_is)
        return _is;
    }

    return isDynamicSetting == '1' ? (
        <Col md={12} style={isDisabled ? { pointerEvents: 'none' } : {}}>
            <div className="dynamic-settings-wrap">
                <div className="dsw-title">Dynamic Settings</div>
                <Row className="dws-content">
                    <Col>
                        <label>Team player limit</label>
                        <FormGroup>
                            <Input type="number" value={setting.team_player_count} name='team_player_count' onChange={handleTeamChange} />
                        </FormGroup>
                    </Col>
                    {
                        !(Roster_Data.is_tour_game == 1 && selected_sports_id == '11') &&
                        <Col>
                            <label>Max player per team</label>
                            <FormGroup>
                                <Input type="number" value={setting.max_player_per_team} name='max_player_per_team' onChange={handleTeamChange}/>
                            </FormGroup>
                        </Col>
                    }
                    <Col>
                        {
                            (Roster_Data.is_tour_game == 1 && selected_sports_id == '15') ?
                            <label>Turbo</label>
                            :
                            <label>Captain</label>
                        }
                        <div className='captain-setting'>
                            <div className="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="CaptainYes" className="custom-control-input" name="c" value="1" onChange={handleTeamChange} checked={setting.c == '1'} />
                                {/* onChange={(e) => this.handleActivityValue(e, 'activity')} */}
                                <label className="custom-control-label" for="CaptainYes">Yes</label>
                            </div>
                            <div className="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="CaptainNo" className="custom-control-input" name="c" value="0" onChange={handleTeamChange} checked={setting.c == '0'} />
                                <label className="custom-control-label" for="CaptainNo">No</label>
                            </div>
                        </div>
                    </Col>
                    {
                        !(Roster_Data.is_tour_game == 1) &&
                        <Col>
                            <label>Vice Captain</label>
                            <div className='captain-setting'>
                                <div className="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="VCaptainYes" className="custom-control-input" name="vc" value="1" onChange={handleTeamChange} checked={setting.vc == '1'}/>
                                    <label className="custom-control-label" for="VCaptainYes">Yes</label>
                                </div>
                                <div className="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="VCaptainNo" className="custom-control-input" name="vc" value="0" onChange={handleTeamChange} checked={setting.vc == '0'}/>
                                    <label className="custom-control-label" for="VCaptainNo">No</label>
                                </div>
                            </div>
                        </Col>
                    }
                </Row>
                {
                    !(Roster_Data.is_tour_game == 1 && selected_sports_id == '11') &&
                    <Row className="dws-content">
                        {
                            _.map(setting.pos, (item, idx) => {
                                let min = item[idx + '_min']
                                let max = item[idx + '_max']
                                return (
                                    <Col md={3} key={idx}>
                                        <label className='dark-color text-uppercase'>{idx}</label>
                                        <FormGroup className='form-with-range'>
                                            <Input type="number" placeholder='Min' value={min} id="_min" name={`${idx}`} onChange={handleChange} />
                                            <Input type="number" placeholder='Max' value={max} id="_max" name={`${idx}`} onChange={handleChange}/>
                                        </FormGroup>
                                    </Col>
                                )
                            })
                        }
                    </Row>
                }
                <Row>
                    <Col>
                        <div className="error-text">
                            {invalidSetting && 'Invalid dynamic settings'}
                        </div>
                    </Col>
                </Row>
            </div>
        </Col>
    ) : ''
}