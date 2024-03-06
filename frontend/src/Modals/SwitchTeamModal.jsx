
import React, { Suspense, lazy } from 'react';
import { Modal, FormGroup } from 'react-bootstrap';
import { MyContext } from '../InitialSetup/MyProvider';
import WSManager from "../WSHelper/WSManager";
import * as WSC from "../WSHelper/WSConstants";
import * as AppLabels from "../helper/AppLabels";
import { inputStyle } from '../helper/input-style';
import {Utilities, _isEmpty} from '../Utilities/Utilities';
import { AppSelectedSport,SELECTED_GAMET,GameType, PFSelectedSport } from '../helper/Constants';
import { getSwitchTeamList, switchTeamContest, getSwitchTeamListNF,switchTeamContestNF, stockSwitchTeam, stockSwitchTeamList,getPFSwitchTeamList ,PFSwitchTeamContest} from '../WSHelper/WSCallings';
const ReactSelectDD = lazy(()=>import('../Component/CustomComponent/ReactSelectDD'));


// var hasMore = false;
export default class SwitchTeam extends React.Component {
    constructor(props, context) {
        super(props, context);

        this.handleShow = this.handleShow.bind(this);
        this.handleClose = this.handleClose.bind(this);

        this.state = {
            show: false,
            isCMounted: false,
            sportsSelected: AppSelectedSport,
            teamData: '',
            fixtureData: '',
            contestData: '',
            teamList: [],
            selectedTeam: '',
            selectedTeamItem: '',
            isRF: false,
            allowRevFantasy : Utilities.getMasterData().a_reverse == '1',
        };
    }

    componentDidMount() {
        
        this.setState({
            isCMounted: true
        });
    }
    

    setData(fixtureData, contestData, teamData) {
        this.setState({ fixtureData: fixtureData, contestData: contestData, teamData: teamData })

        this.getUserLineUpListApi(fixtureData, contestData, teamData);
    }

    getUserLineUpListApi(fixtureData, contestData, teamData) {
        let PTeams = contestData.teams.length;
        let param = {
            ...(SELECTED_GAMET == 'allow_dfs' ? {} : { "sports_id": SELECTED_GAMET == GameType.PickFantasy ? (contestData.sports_id ? contestData.sports_id : PFSelectedSport.sports_id) : AppSelectedSport }),
            "contest_id": contestData.contest_id,
        }

        this.setState({ isLoaderShow: true, isRF : contestData.is_reverse, isSecIn: contestData.is_2nd_inning == "1" })
        let apicall = contestData.is_network_contest == 1 ? getSwitchTeamListNF : SELECTED_GAMET == GameType.PickFantasy ? getPFSwitchTeamList : getSwitchTeamList;
        if(this.props.isStockF){
            apicall = stockSwitchTeamList;
        }
        apicall(param).then((responseJson) => {
            this.setState({ isLoaderShow: false })
            if (responseJson.response_code == WSC.successCode) {
                this.processTeamList(responseJson.data, teamData, contestData.is_reverse,PTeams)
            }
        })
    }

    submitSwitch() {
        if(SELECTED_GAMET == GameType.PickFantasy){
            let apicall = PFSwitchTeamContest
            console.log('contestData',this.state.contestData)
            console.log('selectedTeam',this.state.selectedTeam)
            console.log('teamData',this.state.teamData)
            if (this.state.selectedTeam != '') {
                let param = {
                    "sports_id": this.state.contestData.sports_id || PFSelectedSport.sports_id,
                    "contest_id": this.state.contestData.contest_id,
                    "user_team_id": this.state.selectedTeam.user_team_id,
                    "user_contest_id": this.state.teamData.user_contest_id,
                }
                this.setState({ isLoaderShow: true })
                apicall(param).then((responseJson) => {
                    this.setState({ isLoaderShow: false })
                    if (responseJson.response_code == WSC.successCode) {
    
                        Utilities.showToast(responseJson.message, 5000);
                        this.props.IsSwitchTeamModalHide(true);
    
                    }
                })
            }
        }
        else{
            let apicall = this.state.contestData.is_network_contest == 1 ? switchTeamContestNF : SELECTED_GAMET == GameType.PickFantasy ? PFSwitchTeamContest : switchTeamContest
            if (this.state.selectedTeam != '') {
                let param = {
                    ...(SELECTED_GAMET == 'allow_dfs' ? {} : {"sports_id": AppSelectedSport}),
                    "contest_id": this.state.contestData.contest_id,
                    "lineup_master_id": this.state.selectedTeam.lineup_master_id,
                    "lineup_master_contest_id": this.state.teamData.lineup_master_contest_id,
                }
                if(this.props.isStockF){
                    apicall = stockSwitchTeam;
                }
                this.setState({ isLoaderShow: true })
                apicall(param).then((responseJson) => {
                    this.setState({ isLoaderShow: false })
                    if (responseJson.response_code == WSC.successCode) {
    
                        Utilities.showToast(responseJson.message, 5000);
                        this.props.IsSwitchTeamModalHide(true);
    
                    }
                })
            }
        }
    }

    createTeamAndJoin = () => {
        WSManager.clearLineup()
        let urlData = this.state.fixtureData;
        try {

            let isPlayingAnnounced = _isEmpty(urlData.match_list) ? 0 : urlData.match_list[0].playing_announce;
            if (this.props.isStockF) {
                if (!urlData.collection_master_id) {
                    urlData['collection_master_id'] = urlData.collection_id || this.state.contestData.collection_id;
                }
                let name = urlData.category_id.toString() === "1" ? 'Daily' : urlData.category_id.toString() === "2" ? 'Weekly' : 'Monthly';
                let lineupPath;
                if (SELECTED_GAMET == GameType.StockFantasyEquity) {
                    lineupPath = '/stock-fantasy-equity/lineup/' + name;
                }
                else {
                    lineupPath = '/stock-fantasy/lineup/' + name;
    
                } 
                this.props.mHistory.push({
                    pathname: lineupPath.toLowerCase(), state: {
                        FixturedContest: this.state.contestData,
                        LobyyData: this.state.fixtureData,
                        rootDataItem: this.state.fixtureData,
                        ifFromSwitchTeamModal: true,
                        resetIndex: 1,
                        lineup_master_contest_id: this.state.teamData.lineup_master_contest_id,
                        isPlayingAnnounced
                    }
                })
            } else {
                let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
                dateformaturl = new Date(dateformaturl);
                dateformaturl = dateformaturl.getDate() + '-' + (dateformaturl.getMonth() + 1) + '-' + dateformaturl.getFullYear();
                if(SELECTED_GAMET == GameType.PickFantasy){
                    this.props.mHistory.push({ 
                        pathname: '/pick-fantasy/lineup/' + urlData.home.toLowerCase() + "-vs-" + urlData.away.toLowerCase() + "-" + dateformaturl, 
                        state: { 
                            FixturedContest: this.state.contestData, 
                            LobyyData: this.state.fixtureData, 
                            rootDataItem: this.state.fixtureData, 
                            ifFromSwitchTeamModal: true, resetIndex: 1, 
                            user_team_id: this.state.teamData.user_team_id, 
                            user_team_contest_id: this.state.teamData.user_contest_id,
                            current_sport: PFSelectedSport.sports_id, 
                            isPlayingAnnounced
                        } 
                    })
                }
                else{
                    if (urlData.home) {
                        this.props.mHistory.push({ pathname: '/lineup/' + urlData.home.toLowerCase() + "-vs-" + urlData.away.toLowerCase() + "-" + dateformaturl, state: { FixturedContest: this.state.contestData, LobyyData: this.state.fixtureData, rootDataItem: this.state.fixtureData, ifFromSwitchTeamModal: true, resetIndex: 1, lineup_master_contest_id: this.state.teamData.lineup_master_contest_id, current_sport: AppSelectedSport, isReverseF: this.state.isRF, isSecIn: this.state.isSecIn, isPlayingAnnounced } })
                    }
                    else {
                        let pathurl = Utilities.replaceAll(urlData.collection_name, ' ', '_');
                        this.props.mHistory.push({ pathname: '/lineup/' + pathurl.toLowerCase() + "-" + dateformaturl, state: {
                             FixturedContest: this.state.contestData, LobyyData: this.state.fixtureData,
                              rootDataItem: this.state.fixtureData, ifFromSwitchTeamModal: true, resetIndex: 1, 
                              lineup_master_contest_id: this.state.teamData.lineup_master_contest_id, current_sport: AppSelectedSport, 
                              isReverseF: this.state.isRF, isSecIn: this.state.isSecIn, isPlayingAnnounced } })
                    }
                }
            }
        } catch (error) {
            
        }
    }

    processTeamList(contestData, teamData,isRF,PTeams) {
        let totalTeams = parseInt(PTeams) + parseInt(contestData.length)
        let tempTeamList = [];
        if(totalTeams<parseInt(Utilities.getMasterData().a_teams)){
            tempTeamList = [{
                label: this.props.isStockF ? AppLabels.CREATE_NEW_PORTFOLIO : AppLabels.CREATE_NEW_TEAM,
                value: '',
            }];
        }
       

        // let tempTeamList = [{
        //     label: 'Create New Team',
        //     value: '',
        // }];

        if(this.state.isSecIn){
            contestData.forEach(function (team) {
                if (team.is_2nd_inning == "1") {
                    let tempItem = {};
                    tempItem['label'] = team.team_name;
                    tempItem['value'] = team;
                    tempTeamList.push(tempItem);
                }
            });
        }else if(SELECTED_GAMET == GameType.DFS && isRF == "1" && this.state.allowRevFantasy){
            contestData.forEach(function (team) {
                if (team.is_reverse == "1" && team.lineup_master_id != teamData.lineup_master_id) {
                    let tempItem = {};
                    tempItem['label'] = team.team_name;
                    tempItem['value'] = team;
                    tempTeamList.push(tempItem);
                }
            });
        }
        else if(SELECTED_GAMET == GameType.DFS && isRF != "1" && this.state.allowRevFantasy){
            contestData.forEach(function (team) {
                if (team.is_reverse != "1" && team.is_2nd_inning != "1" && team.lineup_master_id != teamData.lineup_master_id) {
                    let tempItem = {};
                    tempItem['label'] = team.team_name;
                    tempItem['value'] = team;
                    tempTeamList.push(tempItem);
                }
            });
        }
        else if(SELECTED_GAMET == GameType.PickFantasy){
            // console.log('team',team)
            console.log('teamData',teamData)
            console.log('contestData',contestData)
            contestData.forEach(function (team) {
                if (team.user_team_id != teamData.user_team_id) {
                    let tempItem = {};
                    tempItem['label'] = team.team_name;
                    tempItem['value'] = team;
                    tempTeamList.push(tempItem);
                }
            });
        }
        else{
            console.log('first789956',contestData)
            contestData.forEach(function (team) {
                if (team.lineup_master_id != teamData.lineup_master_id && team.is_2nd_inning != "1") {
                    let tempItem = {};
                    tempItem['label'] = team.team_name;
                    tempItem['value'] = team;
                    tempTeamList.push(tempItem);
                }
            });
        }

        this.setState({ teamList: tempTeamList })
    }

    handleClose() {
        this.setState({ show: false });
    }

    handleShow() {
        this.setState({ show: true });
    }

    handleTeamChange = (team) => {
        console.log('team',team)
        if (team.value == "") // Empty value for Create New team
        {
            this.createTeamAndJoin();
        }
        else {
            this.setState({ selectedTeam: team.value, selectedTeamItem: team })
        }
    }

    render() {
        const { IsSwitchTeamModalShow, IsSwitchTeamModalHide } = this.props;
        const { isCMounted, selectedTeamItem } = this.state
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div>
                        <Modal show={IsSwitchTeamModalShow} onHide={IsSwitchTeamModalHide} bsSize="sm" className="switch-team-modal center-modal">
                            <Modal.Header>
                                <Modal.Title>
                                    <div className="match-heading display-table">
                                        <div className="display-table-cell v-mid text-center switch-team-title">
                                            {AppLabels.SWITCH_TEAM}
                                        </div>
                                    </div>
                                </Modal.Title>
                            </Modal.Header>
                            <Modal.Body>
                                <div className="current-team-wrapper">
                                    <h2>{this.props.isStockF ? AppLabels.YOUR_PORTFOLIO_TEAM : AppLabels.YOUR_CURRENT_TEAM}</h2>
                                    <p>{this.state.teamData.team_name}</p>
                                </div>
                                <div className="">
                                    <FormGroup className='input-label-center input-transparent select-state-field switch-team-select m-t-20 m-b-20'>
                                        <div className="select-state ">
                                            <label style={inputStyle.label} className="text-center">{AppLabels.SWITCH_WITH}</label>
                                            <div className="stateStyle switch-sport-style">
                                                {isCMounted && <Suspense fallback={<div />} ><ReactSelectDD
                                                    onChange={this.handleTeamChange}
                                                    classNamePrefix="secondary"
                                                    options={this.state.teamList}
                                                    value={selectedTeamItem}
                                                    arrowRenderer={this.arrowRenderer}
                                                    placeholder="--"
                                                    isSearchable={true}
                                                    isClearable={false}
                                                    theme={(theme) => ({
                                                        ...theme,
                                                        borderRadius: 0,
                                                        colors: {
                                                            ...theme.colors,
                                                            primary: process.env.REACT_APP_PRIMARY_COLOR,
                                                        },
                                                    })}
                                                /></Suspense>}
                                            </div>
                                            <i className="icon-switch-team"></i>
                                            <span className="select-arr"><i className="icon-arrow-down"></i></span>
                                            <div className="state-border col-sm-12"></div>
                                        </div>
                                    </FormGroup>
                                </div>
                            </Modal.Body>
                            <Modal.Footer onClick={() => this.submitSwitch()} className={'custom-modal-footer ' + (this.state.selectedTeam == '' ? 'disabled' : '')}>
                                <a className={"btn btn-primary " + (this.state.selectedTeam == '' ? 'disabled' : '')}>{AppLabels.SUBMIT}</a>
                            </Modal.Footer>
                        </Modal>
                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}

