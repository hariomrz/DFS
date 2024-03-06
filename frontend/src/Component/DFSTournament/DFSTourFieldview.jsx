import React from 'react';
import { Row, Col } from 'react-bootstrap';
import ls from 'local-storage';
import Images from '../../components/images';
import WSManager from "../../WSHelper/WSManager";
import * as WSC from "../../WSHelper/WSConstants";
import { MyContext } from '../../InitialSetup/MyProvider';
import MyAlert from '../../Modals/MyAlert';
import { Utilities, _isUndefined, _Map, _isEmpty, _cloneDeep } from '../../Utilities/Utilities';
import { SportsIDs } from "../../JsonFiles";
import * as AppLabels from "../../helper/AppLabels";
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import { AppSelectedSport, IS_BRAND_ENABLE, SELECTED_GAMET,GameType } from '../../helper/Constants';
import { getDFSTourUserLineUpDetail, getDFSTourLineupWithScore } from '../../WSHelper/WSCallings';

var i = 0;
export default class DFSTourFieldView extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            profileDetail: ls.get('profile') || '',
            userName: '',
            isSearchable: false,
            MasterData: [],

            LobyyData: [],

            lineupArr: [],

            allPosition: [],

            maxPlayers: [],

            isFieldView: true,

            FixturedContest: [],

            isFrom: '',

            isEditLineup: false,

            TeamMyContestData: '',

            collection_master_id: '',

            rootDataItem: [],

            myContestData: '',

            isFromtab: '',

            isFromRoster: '',

            showResetAlert: false,
            tempLineupArr: [],
            homePlayerCount: 0,
            awayPlayerCount: 0,

            isFromMyTeams: false,

            ifFromSwitchTeamModal: false,

            resetIndex: -1,

            teamName: this.props.location ? this.props.location.state.team_name : '',
            current_sports_id: AppSelectedSport,
            allowCollection: Utilities.getMasterData().a_collection,
            userTeamInfo: '',
            isFromLeaderboard: false
        }

        i = 0
    }

    UNSAFE_componentWillMount() {
        if(Utilities.getMasterData().a_dfst == 1){
            ls.set('isDfsTourEnable',true)
        }
        this.setPropsVar();
        if(SELECTED_GAMET != GameType.MultiGame &&  SELECTED_GAMET != GameType.Free2Play){
            WSManager.setPickedGameType(GameType.DFS);
          }
    }

    UNSAFE_componentWillReceiveProps(nextProps) {
        if(nextProps.userName != ''){
            this.setState({
                userName: nextProps.userName
            })
        }
        if (!this.props.isFromUpcoming) {
            if (nextProps.MasterData != this.state.MasterData) {
                this.setPropsVar();
            }
        }
        else if (this.props.location.state.isFromLeaderboard) {
            this.setPropsVar();
        }
        else {
            this.callSetLineup(nextProps);
        }
    }


    setPropsVar() {
        
        let propsData = '';
        if (this.props.location && this.props.location.state) {
            propsData = this.props.location.state;
        }
        else {
            propsData = this.props;
        }


        let { current_sport, from, MasterData, LobyyData, SelectedLineup, FixturedContest, isFrom, isEdit, team, rootitem, rootDataItem, contestItem, isFromtab, isFromMyTeams, ifFromSwitchTeamModal, resetIndex, team_name,userTeamInfo ,isFromLeaderboard} = propsData;
        if(current_sport && current_sport != this.state.current_sports_id){
            this.setState({
                current_sports_id: current_sport
            })
        }
        this.setState({
            isSearchable: false,
            MasterData: from == 'MyContest' ? [] : (MasterData || []),

            LobyyData: LobyyData || [],

            lineupArr: SelectedLineup || [],

            allPosition: from == 'MyContest' ? [] : (MasterData ? (MasterData.all_position || []) : []),

            maxPlayers: from == 'MyContest' ? [] : (MasterData ? (MasterData.team_player_count || []) : []),

            isFieldView: true,

            FixturedContest: FixturedContest || [],

            isFrom: from == 'MyContest' ? from : isFrom,

            isEditLineup: from == 'MyContest' ? isEdit : false,

            TeamMyContestData: (from == 'MyContest' || isFrom == 'MyContest' ? team : isFrom && isFrom == 'editView' ? team : '') || '',

            collection_master_id: from == 'MyContest' ? rootitem.collection_master_id : '',

            rootDataItem: from == 'MyContest' ? rootitem : (isFrom && isFrom == 'editView' ? rootDataItem : (isFrom && isFrom == 'contestJoin' ? rootDataItem : '')),

            myContestData: (from && from == 'MyContest') ? contestItem : ((isFrom && isFrom == 'editView') ? FixturedContest : ''),

            isFromtab: from && from == 'MyContest' ? isFromtab : ((isFrom && isFrom == 'editView') ? FixturedContest : ((isFrom && isFrom == 'rank-view') ? 11 : '')),

            isFromRoster: isFrom && isFrom == 'editView' ? isFrom : '',

            showResetAlert: false,
            tempLineupArr: [],
            homePlayerCount: ls.get('home_player_count') ? ls.get('home_player_count') : 0,
            awayPlayerCount: ls.get('away_player_count') ? ls.get('away_player_count') : 0,

            isFromMyTeams: isFromMyTeams ? isFromMyTeams : false,

            ifFromSwitchTeamModal: ifFromSwitchTeamModal && this.props.location ? this.props.location.state.ifFromSwitchTeamModal : false,

            resetIndex: resetIndex ? resetIndex : -1,

            teamName: team_name,
            userTeamInfo: userTeamInfo ? userTeamInfo : '',
            isFromLeaderboard: isFromLeaderboard || false
        })
    }

    filterLineypArrByPosition = (player) => {
        let tmpLineupArray = this.state.lineupArr.sort((a, b) => (b.fantasy_score - a.fantasy_score))
        let arrPositionOfSelectedPlayer = tmpLineupArray.filter(function (item) {
            return item.position == player.position
        })
        return arrPositionOfSelectedPlayer
    }
    TempfilterLineypArrByPosition = (player) => {

        let arrPositionOfSelectedPlayer = this.state.tempLineupArr.filter(function (item) {
            return item.position == player.position
        })
        return arrPositionOfSelectedPlayer
    }
    checkPlayerExistInLineup(player) {
        var isExist = false

        for (var selectedPlayer of this.state.lineupArr) {
            if (selectedPlayer.player_uid == player.player_uid) {
                isExist = true
                break
            }
        }
        return isExist

    }
    toggleFields = (mode) => {
        this.setState({ isFieldView: mode })
        i = 0;
    }
    removePlayerFromLineup = (player) => {

        i = 0;
        let lineupArr = this.state.lineupArr;
        let TempArrLineup = this.state.tempLineupArr
        if (this.checkPlayerExistInLineup(player)) {
            var index = 0;
            for (var selectedPlayer of this.state.lineupArr) {
                if (selectedPlayer.player_uid == player.player_uid) {
                    TempArrLineup.push(selectedPlayer)
                    lineupArr.splice(index, 1);
                }
                index++
            }
        }
        this.setState({ tempLineupArr: TempArrLineup })
        this.setState({ lineupArr: lineupArr })
        ls.set('Lineup_data', lineupArr)

        if (player.team_abbreviation == this.state.LobyyData.home || player.team_abbr == this.state.LobyyData.home) {
            let homePlayerCount = this.state.homePlayerCount;
            homePlayerCount = homePlayerCount - 1;
            setTimeout(() => {
                this.setState({
                    homePlayerCount: homePlayerCount
                }, () => {
                    ls.set('home_player_count', homePlayerCount);
                })
            }, 100);

        } else {
            let awayPlayerCount = this.state.awayPlayerCount;
            awayPlayerCount = awayPlayerCount - 1;
            setTimeout(() => {
                this.setState({
                    awayPlayerCount: awayPlayerCount
                }, () => {
                    ls.set('away_player_count', awayPlayerCount);
                })
            }, 100);
        }

    }

    resetConfirm() {
        this.setState({ showResetAlert: true })
    }

    resetConfirmHide() {
        this.setState({ showResetAlert: false })
    }
    resetLineup = () => {
        this.setState({ showResetAlert: false })
        this.setState({ lineupArr: [] })
        this.setState({ selectedCaptain: '', salaryCap: this.state.salaryCapDefault })
        this.setState({ AvgSalaryPlayer: parseFloat(this.state.salaryCapDefault) / this.state.maxPlayers })
        WSManager.clearLineup();
    }
    EditMyLineup = () => {


        const { allowCollection, rootDataItem } = this.state;
        let urlData = this.state.rootDataItem;
        let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();

        //count home and away player count to set on local storage
        let homePlayerCount = 0;
        let awayPlayerCount = 0;

        if (typeof this.state.lineupArr != 'undefined' && this.state.lineupArr.length > 0) {
            this.state.lineupArr.map((lineupItem, lineupIndex) => {
                if (SELECTED_GAMET != GameType.MultiGame ) {
                    if (lineupItem.team_abbreviation == urlData.home || lineupItem.team_abbr == urlData.home) {
                        homePlayerCount = homePlayerCount + 1;
                    }
                    else {
                        awayPlayerCount = awayPlayerCount + 1;
                    }
                }
                else {
                    if (lineupItem.team_abbreviation == urlData.match_list[0].home || lineupItem.team_abbr == urlData.match_list[0].home) {
                        homePlayerCount = homePlayerCount + 1;
                    }
                    else {
                        awayPlayerCount = awayPlayerCount + 1;
                    }
                }


            });
        }

        ls.set('home_player_count', homePlayerCount);
        ls.set('away_player_count', awayPlayerCount);
        ls.set('Lineup_data', this.state.lineupArr);

        let lineupPath = '';
        if (SELECTED_GAMET != GameType.MultiGame ) {
            lineupPath = '/lineup/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
            this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { SelectedLineup: this.state.lineupArr, MasterData: this.state.MasterData, LobyyData: _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData, FixturedContest: this.state.myContestData, team: this.state.TeamMyContestData, from: 'editView', rootDataItem: this.state.rootDataItem, isFromMyTeams: this.state.isFromMyTeams, ifFromSwitchTeamModal: this.state.ifFromSwitchTeamModal, resetIndex: this.state.resetIndex > 0 ? (this.state.resetIndex + 1) : -1, current_sport: this.state.current_sports_id } });
        }
        else if (SELECTED_GAMET == GameType.MultiGame  && rootDataItem.match_list.length == 1) {
            lineupPath = '/lineup/' + urlData.match_list[0].home + "-vs-" + urlData.match_list[0].away + "-" + dateformaturl
            this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { SelectedLineup: this.state.lineupArr, MasterData: this.state.MasterData, LobyyData: _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData, FixturedContest: this.state.myContestData, team: this.state.TeamMyContestData, from: 'editView', rootDataItem: this.state.rootDataItem, isFromMyTeams: this.state.isFromMyTeams, ifFromSwitchTeamModal: this.state.ifFromSwitchTeamModal, resetIndex: this.state.resetIndex > 0 ? (this.state.resetIndex + 1) : -1, current_sport: this.state.current_sports_id } });
        }
        else {
            let pathurl = Utilities.replaceAll(urlData.collection_name, ' ', '_');
            lineupPath = '/lineup/' + pathurl + "-" + dateformaturl
            this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { SelectedLineup: this.state.lineupArr, MasterData: this.state.MasterData, LobyyData: _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData, FixturedContest: this.state.myContestData, team: this.state.TeamMyContestData, from: 'editView', rootDataItem: this.state.rootDataItem, isFromMyTeams: this.state.isFromMyTeams, ifFromSwitchTeamModal: this.state.ifFromSwitchTeamModal, resetIndex: this.state.resetIndex > 0 ? (this.state.resetIndex + 1) : -1, current_sport: this.state.current_sports_id } });
        }
    }
    GoToRoster = (item, groster) => {
        let urlData = _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData;
        let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();


        let lineupPath = '/lineup/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
        this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { LobyyData: _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData, FixturedContest: this.state.FixturedContest, SelectedPlayerPosition: item.position, PositionOrder: groster, from: this.state.isFrom, isFromMyTeams: this.state.isFromMyTeams, team: this.state.TeamMyContestData, ifFromSwitchTeamModal: this.state.ifFromSwitchTeamModal, resetIndex: 1, current_sport: this.state.current_sports_id } });
    }
    playerPosClass = (a) => {

        if (a == 0) {

            i = 0
        }
        i++;
        return 'pos' + i
    }

    callSetLineup = (props) => {
        if (props.isFromLeaderboard == true) {
            let param = {
                "user_tournament_season_id":props.userTeamInfo.user_tournament_season_id,
                "sports_id":this.state.current_sports_id,
            }
            getDFSTourLineupWithScore(param).then((responseJson) => {
                if (responseJson.response_code == WSC.successCode) {
                    let data = responseJson.data
                    this.setState({
                        lineupArr: data.lineup,
                        allPosition: data.all_position,
                        teamName: data.team_info.team_name
                    })
                }
            })
        }
        else {
            let param = {
                "tournament_season_id": props.TeamMyContestData.tournament_season_id,
                "tournament_team_id": props.TeamMyContestData.tournament_team_id,
                "sports_id": this.state.current_sports_id
            }

            getDFSTourUserLineUpDetail(param).then((responseJson) => {
                if (responseJson && responseJson.response_code == WSC.successCode) {
                    this.setState({
                        lineupArr: responseJson.data.lineup,
                        allPosition: responseJson.data.all_position
                    })
                }
            })
        }
    }

    componentDidMount = () => {
        i = 0

        let PData = ''
        if(this.props && this.props.location && this.props.location.state){
            PData = this.props && this.props.location && this.props.location.state
        }
        else{
            PData = this.props
        }
        if (this.props.isFromUpcoming || PData.isFromLeaderboard ) {
            this.callSetLineup(PData);
        }
        else {
            if (!_isUndefined(this.state.isFrom) && this.state.isFrom == 'MyContest' && this.state.isFromtab != 11) {
                let param = {
                    "tournament_season_id": this.state.TeamMyContestData.tournament_season_id,
                    "tournament_team_id": this.state.TeamMyContestData.tournament_team_id,
                    "sports_id": this.state.current_sports_id
                }

                getDFSTourUserLineUpDetail(param).then((responseJson) => {
                    if (responseJson && responseJson.response_code == WSC.successCode) {
                        this.setState({
                            //    home_player_team_id: responseJson.data.lineup[0].player_team_id,
                            lineupArr: responseJson.data.lineup,
                            allPosition: responseJson.data.all_position
                        })
                    }
                })
            }
            if (!_isUndefined(this.state.isFrom) && this.state.isFrom == 'MyContest' && this.state.isFromtab == 11) {

                let param = {
                    "user_tournament_season_id":this.props.TeamMyContestData.user_tournament_season_id,
                    "sports_id":this.state.current_sports_id,
                }
                getDFSTourLineupWithScore(param).then((responseJson) => {
                    if (responseJson.response_code == WSC.successCode) {

                        this.setState({
                            lineupArr: responseJson.data.lineup,
                            allPosition: responseJson.data.all_position
                        })
                    }
                })
            }
        }
    }
    NextSubmit = () => {

        let urlData = _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData;
        let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();

        let selectCaptainPath = '/select-captain/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
        this.props.history.push({ pathname: selectCaptainPath.toLowerCase(), state: { SelectedLineup: this.state.lineupArr, MasterData: this.state.MasterData, LobyyData: _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData, FixturedContest: this.state.FixturedContest, isFrom: this.state.isFromRoster ? this.state.isFromRoster : this.state.isFrom, team: this.state.TeamMyContestData, rootDataItem: this.state.rootDataItem, isFromMyTeams: this.state.isFromMyTeams, ifFromSwitchTeamModal: this.state.ifFromSwitchTeamModal } })
    }

    goBackToRoster() {
        if(this.props.showFieldV) {
            this.props.hideFieldV()
        }else{
            this.props.history.goBack()
        }
    }
    render() {
        const {
            allPosition,
            isFieldView,
            isFrom,
            isEditLineup,
            TeamMyContestData,
            isFromRoster,
            isFromtab,
            profileDetail,
            userName,
            userTeamInfo,
            isFromLeaderboard
        } = this.state;

        let reversePosition =  this.state.current_sports_id == SportsIDs.soccer ? _cloneDeep(allPosition || []).reverse() : allPosition;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"web-container pb0" + ((isFrom == 'captain' || isFrom == 'rank-view') ? ' right-fieldview' : '') + (this.props.showFieldV ? ' show-rfv' : '') }>
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.fieldview.title}</title>
                            <meta name="description" content={MetaData.fieldview.description} />
                            <meta name="keywords" content={MetaData.fieldview.keywords}></meta>
                        </Helmet>

                        <div className='field-view-cross-button-container'>
                            {
                                isFromLeaderboard ?
                                <span>
                                    {
                                        this.state.teamName 
                                        || 
                                        (TeamMyContestData.team_name ? TeamMyContestData.team_name : TeamMyContestData )
                                    }
                                    {
                                        userTeamInfo &&
                                        <span className="username-text">({userTeamInfo.user_name})</span>
                                    }
                                </span>
                                :
                                (this.props.TeamMyContestData && this.props.TeamMyContestData.team_name && this.props.TeamMyContestData.team_name != '') ?
                                    <span>
                                        {this.props.TeamMyContestData.team_name} 
                                        {
                                            userName && userName != '' ?
                                            <span className="username-text">({userName})</span>
                                            :
                                            <span className="username-text">({profileDetail.user_name})</span>
                                        }
                                    </span>
                                    :
                                    <span>
                                        {
                                            this.state.teamName 
                                            || 
                                            (TeamMyContestData.team_name ? TeamMyContestData.team_name : TeamMyContestData )
                                        }
                                        {
                                            userName && userName != '' ?
                                            <span className="username-text">({userName})</span>
                                            :
                                            <span className="username-text">({profileDetail.user_name})</span>
                                        }
                                    </span>

                            }

                            {
                                <div className="brand-logo-sec">
                                    <img className='brand-logo' alt="" src={Images.FIELD_VIEW_LOGO}></img>
                                </div>
                            }
                            {
                                <img className='developed-by-container' alt="" src={Images.DEVELOPED_BY_LOGO}></img>
                            }
                            <i onClick={() => { this.goBackToRoster() }} className='icon-close' />
                            {isEditLineup &&
                                <i onClick={() => { this.EditMyLineup() }} className='icon-edit-line edit' />
                            }
                        </div>
                        <div className={'field-view-container ' + (this.state.current_sports_id == SportsIDs.cricket ? 'cricket-ground-container' : this.state.current_sports_id == SportsIDs.soccer ? 'soccer-ground-container' : this.state.current_sports_id == SportsIDs.badminton ? 'badminton-ground-container' : this.state.current_sports_id == SportsIDs.kabaddi ? 'kabaddi-ground-container' : this.state.current_sports_id == SportsIDs.basketball ? 'basketball-ground-container' : this.state.current_sports_id == SportsIDs.football ? 'football-ground-container' : this.state.current_sports_id == SportsIDs.baseball ? ' baseball-ground-container' :'soccer-ground-container')}>
                            <div className={"player-area " + (!isFieldView && 'hide')}>
                                {(this.props.isFromUpcoming || this.props.isFromLeaderboard) &&
                                    <a href className="close-field-view-right" onClick={this.props.sideViewHide}>
                                        <i className="icon-close"></i>
                                    </a>
                                }
                                <div className='space-evenly-container'>
                                    {_Map(reversePosition, (positem, posidx) => {
                                        return (
                                            <div key={posidx} >
                                                <div className={'player-position-header'+ (this.state.current_sports_id == SportsIDs.baseball ? ' baseball-filedview': ' ')}>{positem.position_display_name}</div>
                                                <div className='player-position-row'>
                                                    {_Map(this.filterLineypArrByPosition(positem), (item, idx) => {
                                                        return (
                                                            <div key={idx} className='player-row-container'>
                                                                
                                                                {
                                                                    item.sports_id != SportsIDs.kabaddi && item.playing_announce == 1 && item.is_playing == 0 &&
                                                                    <span className="playing_indicator danger"></span>
                                                                }

                                                                {isFromRoster == "editView" ? '' :
                                                                    <React.Fragment>

                                                                        {item.player_role == 1 &&
                                                                            <span className="captain-player">C</span>
                                                                        }
                                                                        {item.player_role == 2 &&
                                                                            <span className="vcaptain-player">V</span>
                                                                        }
                                                                    </React.Fragment>
                                                                }
                                                                <img src={Utilities.playerJersyURL(item.jersey)} alt="" />
                                                                <div className="player-name"> {item.full_name}</div>
                                                                <div className="player-postion">
                                                                    {isFromtab == 1 || isFromtab == 2 || isFromtab == 11 ? '' : Utilities.getMasterData().currency_code + " "} {isFromtab == 1 || isFromtab == 2 || isFromtab == 11 ? item.score : item.salary} {isFromtab == 1 || isFromtab == 2 || isFromtab == 11 ? 'pts' : ''}
                                                                </div>

                                                            </div>
                                                        )
                                                    })
                                                    }
                                                </div>
                                            </div>
                                        )
                                    })
                                    }
                                </div>
                                {IS_BRAND_ENABLE && <div className="powered-by">
                                    <span>{AppLabels.DEVELOPED_BY} </span>
                                    <img alt='' src={Images.VINFOTECH_BRAND_WHITE} />
                                    <span>{AppLabels.VINFOTECH}</span>
                                </div>
                                }
                            </div>
                        </div>

                        <div className={"lineup-list-view " + (isFieldView ? 'hide' : '')}>
                            <div className="list-view-detail">
                                {_Map(allPosition, (positem, posidx) => {
                                    return (
                                        <div key={posidx}>
                                            <div className="list-view-header"> {positem.position_name} </div>
                                            <ul className="list-secondary" key={posidx}>
                                                {
                                                    _Map(this.filterLineypArrByPosition(positem), (item, idx) => {
                                                        return (
                                                            <li key={idx}>
                                                                <Row>
                                                                    <Col xs={8} className="text-left-ltr">
                                                                        <h4>
                                                                            {item.full_name}
                                                                            {item.player_role == 1 &&
                                                                                <span className="captain-player">C</span>
                                                                            }
                                                                            {item.player_role == 2 &&
                                                                                <span className="vcaptain-player">V</span>
                                                                            }
                                                                        </h4>
                                                                        <span>{item.team_abbreviation || item.team_abbr}</span>
                                                                    </Col>
                                                                    <Col xs={4} className="text-right-ltr">
                                                                        <p>{isFromtab == 1 || isFromtab == 2 || isFromtab == 11 ? '' : Utilities.getMasterData().currency_code + " "} {isFromtab == 1 || isFromtab == 2 || isFromtab == 11 ? item.score : item.salary} {isFromtab == 1 || isFromtab == 2 || isFromtab == 11 ? 'pts' : ''}</p>
                                                                        {!(isFrom == 'MyContest') &&
                                                                            <button className="btn-removeplayer btn" onClick={() => this.removePlayerFromLineup(item)}><i className="icon-remove"></i></button>
                                                                        }
                                                                    </Col>
                                                                </Row>

                                                            </li>
                                                        )
                                                    })
                                                }

                                            </ul>
                                        </div>
                                    )
                                })
                                }


                            </div>

                        </div>
                        {
                            this.state.showResetAlert &&
                            <MyAlert isMyAlertShow={this.state.showResetAlert} onMyAlertHide={() => this.resetLineup()} hidemodal={() => this.resetConfirmHide()} message={AppLabels.Your_lineup_will_be_reset} />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}