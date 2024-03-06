import React, { Suspense, lazy } from 'react';
import { Row, Col } from 'react-bootstrap';
import ls from 'local-storage';
import Images from '../../components/images';
import WSManager from "../../WSHelper/WSManager";
import * as WSC from "../../WSHelper/WSConstants";
import { MyContext } from '../../InitialSetup/MyProvider';
import MyAlert from '../../Modals/MyAlert';
import { Utilities, _isUndefined, _Map, _isEmpty, _cloneDeep,_sumBy, _filter, _includes } from '../../Utilities/Utilities';
import { SportsIDs } from "../../JsonFiles";
import * as AppLabels from "../../helper/AppLabels";
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import { AppSelectedSport, IS_BRAND_ENABLE, SELECTED_GAMET,GameType } from '../../helper/Constants';
import { getUserLineUpDetail, getLineupWithScore,genrateLineup,processLineup } from '../../WSHelper/WSCallings';
import CustomLoader from '../../helper/CustomLoader';
import GuruFieldviewDetailModal from './GuruFieldviewDetailModal';
import SliderPerfectLineupModal from './SliderPerfectLineupModal';

const BreakDownPlayerCard = lazy(() => import('../../Modals/BreakDownPlayerCard'));

var i = 0;
class GuruFiledView extends React.Component {
    constructor(props) {
        super(props);
        const { GenerateGuruData, location } = props;
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

            teamName: location ? location.state.team_name : GenerateGuruData || '',
            allowCollection: Utilities.getMasterData().a_collection,
            playerCard: {},
            showPlayeBreakDown: false,
            selectedGame: '',
            league_id: location ? location.state.league_id : GenerateGuruData.league_id || '',
            avilableBudget:'',
            isExludeToggle:true,
            isGenrateApi:true,
            guruFieldViewModalShow:ls.get('guruFiledViewCheck') ? ls.get('guruFiledViewCheck') : 0,
            howToPlay:false,
            perfectLineupSlider:false,
            invalidLineup: false



        }

        i = 0
    }


    UNSAFE_componentWillMount() {
        Utilities.setScreenName('fieldview')
        this.setPropsVar();
        
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
        else {
            this.callSetLineup(nextProps);
        }
    }


    setPropsVar() {
        const { GenerateGuruData, location } = this.props;
        let propsData = '';
        if (location && location.state) {
            propsData = location.state;
        } else if (GenerateGuruData) {
            propsData = GenerateGuruData;
        } else {
            propsData = this.props;
        }


        let { from, MasterData, LobyyData, SelectedLineup, FixturedContest, isFrom, isEdit, team, rootitem, rootDataItem, contestItem, isFromtab, isFromMyTeams, ifFromSwitchTeamModal, resetIndex, team_name, league_id,TotalSalary,collection_master_id, generate_lineup } = propsData;

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
            league_id: league_id,
            TotalSalary:TotalSalary,
            collection_master_id_guru: collection_master_id
            
        },()=>{
            this.avilableBudgetCall()
            console.log(generate_lineup, 'generate_lineup');
            this.lineupCheck(generate_lineup)
        })
    }
    avilableBudgetCall =() => {
        let Budget = _sumBy(this.state.lineupArr, function (o) { return o.in_lineup && parseFloat(o.salary, 10); })
            let BudgetFinal = this.state.TotalSalary - Budget;
            this.setState({avilableBudget:BudgetFinal})
    }

    filterLineypArrByPosition = (player) => {
        let tmpLineupArray = this.state.lineupArr.sort((a, b) => (b.fantasy_score - a.fantasy_score))
        let arrPositionOfSelectedPlayer = tmpLineupArray.filter(function (item) {
            return item.position == player.position && item.in_lineup
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

    callSetLineup = (props) => {
        if (props.isFromLeaderboard == true) {
            let param = {
                'lineup_master_contest_id': props.TeamMyContestData.lineup_master_contest_id,
                "sports_id": AppSelectedSport,
            }
            getLineupWithScore(param).then((responseJson) => {
                if (responseJson.response_code == WSC.successCode) {

                    this.setState({
                        lineupArr: responseJson.data.lineup,
                        allPosition: responseJson.data.all_position
                    })
                }
            })
        }
        else {
            let param = {
                "lineup_master_id": props.TeamMyContestData.lineup_master_id,
                "collection_master_id": props.rootitem.collection_master_id,
                "sports_id": AppSelectedSport,
            }

            getUserLineUpDetail(param).then((responseJson) => {
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
        ls.set("toRosterTab",false)

        i = 0

        if (this.props.isFromUpcoming || this.props.isFromLeaderboard) {
            this.callSetLineup(this.props);
        }
        else {
            if (!_isUndefined(this.state.isFrom) && this.state.isFrom == 'MyContest' && this.state.isFromtab != 11) {
                let param = {
                    "lineup_master_id": this.state.TeamMyContestData.lineup_master_id,
                    "collection_master_id": this.state.collection_master_id,
                    "sports_id": AppSelectedSport,
                }

                getUserLineUpDetail(param).then((responseJson) => {
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
                    'lineup_master_contest_id': this.state.TeamMyContestData.lineup_master_contest_id,
                    "sports_id": AppSelectedSport,
                }
                getLineupWithScore(param).then((responseJson) => {
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

    goBackToRoster() {
        console.log(this.props);
        if(this.props.showFieldV) {
            this.props.hideFieldV()
            this.props.HideFieldView()
        }else{
            ls.set("toRosterTab",true)
            this.props.history.goBack()
        }
    }

    PlayerCardHide = () => {
        this.setState({
            showPlayeBreakDown: false
        });
    }

    showBreakeDown = (obj) => {
        if (this.state.league_id) {
            obj['league_id'] = this.props.IsNetworkGameContest ? obj.league_id: this.state.league_id;
            this.setState({
                selectedGame: { player_team_id: obj.player_team_id },
                playerCard: obj,
                showPlayeBreakDown: true
            })
        }
    }
    exculedLock = (isExlude) => {
        if(isExlude){
            this.setState({isExludeToggle: true})
        }
        else{
            this.setState({isExludeToggle: false})
 
        }
        
    }
    lockUnlockPlayer = (item) => {
        let TempArr = this.state.lineupArr
        _Map(TempArr, (it, ix) => {
            if (it.player_uid == item.player_uid) {
                if (_isUndefined(it['is_locked']) || it['is_locked'] == 0) {
                    it['is_locked'] = 1
                } else {
                    it['is_locked'] = 0
                }
            }
        })
        this.setState({
            lineupArr: TempArr
        }, () => {
            ls.set('guru_lineup_data', TempArr)

            //this.checkExcluded()
        })
        
    }
    exludePlayer = (item) => {
        let TempArr = this.state.lineupArr;

        _Map(TempArr, (it, ix) => {
            if (it.player_uid == item.player_uid) {
                if (_isUndefined(it['is_excluded']) || it['is_excluded'] == 0) {
                    it['is_excluded'] = 1;
                    it['processLineup'] = false;

                }
                else{
                    it['is_excluded'] = 0;
                    it['processLineup'] = true;

                }
            }
        })
        let salary = parseFloat(item.salary);
        let budget= parseFloat(this.state.avilableBudget) + salary
        
        this.setState({ lineupArr: TempArr, avilableBudget:budget }, () => {
            ls.set('guru_lineup_data', TempArr)
            //this.checkExcluded()
        })
        
    }


    refresh = async() => {
        this.setState({isGenrateApi: false})
        let locked=[];
        let excluded=[];

        _Map(this.state.lineupArr, (item) => {
            if(item.is_locked == 1) {
                locked.push(item.player_team_id)
            }
            if(item.is_excluded == 1) {
                excluded.push(item.player_team_id)
            }
        });

        let param = {
            "collection_master_id": this.state.collection_master_id_guru,
            "locked":locked,
            "excluded":excluded
        }
        var api_response_data = await genrateLineup(param);
        if (api_response_data) {
 
            if(api_response_data.data){
                this.setState({isGenrateApi:true},()=>{
                    let initialArray = this.state.lineupArr
                    for (var pos of initialArray) {
                        let isInLinup=false;
                        let is_locked=0;
                        let captain=0;
                        let is_excluded=0;

                        for (var lineup of api_response_data.data.lineup) {
                            if (lineup.player_uid == pos.player_uid) {
                                is_locked =lineup.is_locked;
                                isInLinup = true;
                                captain = lineup.captain;
                                //pos.is_locked = lineup.is_locked
                            }
                           
                        }
                        if(isInLinup)
                        {   pos.processLineup = true;
                            pos.is_locked = is_locked;
                            pos.in_lineup = true;
                            pos.captain = captain
                        }
                        else{
                            pos.processLineup = false;
                            pos.in_lineup = false;
                            pos.is_locked = is_locked;
                            pos.captain = captain

                        }
                    }
                    this.setState({lineupArr:initialArray},()=>{
                        ls.set('guru_lineup_data', initialArray)
                        this.avilableBudgetCall();
                        //this.GoToFieldView()

                        this.lineupCheck(api_response_data.data.lineup)
                    })
                })
            }
           
            //this.parseMasterData(api_response_data);
        }
        else{
            this.setState({isGenrateApi:true}) 
        }
    }

    lineupCheck = (lineup) => {
        const { lineupArr, MasterData } = this.state
        let excluded = _Map(lineupArr, item => {
            if(item.is_excluded == 1) {
                return item.player_uid
            }
        } )
        let generate = _Map(lineup, item => {
            return _includes(excluded, item.player_uid)
        }).filter(obj => !obj)
        this.setState({
            invalidLineup: generate.length < MasterData.team_player_count
        }, () => {
            if(generate.length < MasterData.team_player_count) {
                Utilities.showToast('Check if any players were mistakenly excluded or if there are specific criteria for eligibility.', 3000);
            }
        })
    }


    SubmitLineup = () => {
            this.setState({ isGenrateApi: false })
            let tmpLineupArray = [];
            let cap_ptID = '';
            let vcap_ptID = '';
    
            _Map(this.state.lineupArr, (item) => {
                if(item.processLineup){
                let ptID = item.player_team_id;

                    if(item.captain == 1){
                        cap_ptID = ptID
                    }
                    if(item.captain == 2){
                        vcap_ptID = ptID
                    }
                    tmpLineupArray.push(ptID)
                }
                
            });

            if(tmpLineupArray.length < 11){
                this.setState({ isGenrateApi: true })

                Utilities.showToast("Please Select 11 players");
                return;
            }
    
            let param = {
                "league_id": this.state.league_id,
                "sports_id": AppSelectedSport,
                "team_name": this.state.teamName,
                "collection_master_id": this.state.collection_master_id_guru,
                "players": tmpLineupArray,
                "c_id":cap_ptID,
                "vc_id":vcap_ptID,
                "lineup_master_id": '',
                "is_pl_team":1
            }
            processLineup(param).then((responseJson) => {
                this.setState({ isGenrateApi: true});
                if (responseJson.response_code == WSC.successCode) {
                    Utilities.showToast(responseJson.message)
                    ls.remove('guru_lineup_data')
                    ls.set('guruTab',3)
                    ls.set('toRosterTab',false)
                    this.props.history.go(0);

                }
            })
        
    }
    
      /**
     * 
     * @description method to display GuruRosterModal popup model.
     */
    GuruFieldViewModalShow = (data) => {
        this.setState({
            guruFieldViewModalShow: 0,
            howToPlay:true
        });
    }
    /**
     * 
     * @description method to hide GuruRosterModal popup model
     */
    GuruFieldViewModalHide = () => {
        this.setState({
            guruFieldViewModalShow: 1,
            howToPlay:false

        });
    }

      /**
     * 
     * @description method to display Perfect lineup slider popup model.
     */
    perfectLineupSliderShow = (data) => {
        this.setState({
            perfectLineupSlider: true
        });
    }
    /**
     * 
     * @description method to hide Perfect lineup slider popup model
     */
    perfectLineupSliderHide = () => {
        this.setState({
            perfectLineupSlider: false,

        });
    }
    goToPerFectLineup = () => {
        this.perfectLineupSliderHide()
        if(window.ReactNativeWebView){
            let data = {
                action: 'sponserLink',
                targetFunc: 'sponserLink',
                type: 'link',
                url:   WSManager.getIsIOSApp() ? 'https://apps.apple.com/in/app/the-perfect-lineup/id1501149666' : 'https://play.google.com/store/apps/details?id=com.vinfotech.perfectlineup&hl=en_IN&gl=US',
                detail: ""
            }
            window.ReactNativeWebView.postMessage(JSON.stringify(data))

        }
        else{
            window.open('https://www.perfectlineup.in/lineup-players-pool?sports:Cricket', "_blank")

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
            playerCard,
            showPlayeBreakDown,
            selectedGame,
            guruFieldViewModalShow,
            perfectLineupSlider,
            howToPlay,
            invalidLineup
        } = this.state;

        let reversePosition =  AppSelectedSport == SportsIDs.soccer ? _cloneDeep(allPosition || []).reverse() : allPosition;
        let isGuru = this.state.isFrom== "Guru" ? true:false;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"web-container pb0" + ((isFrom == 'captain' || isFrom == 'rank-view') ? ' right-fieldview' : '') + (this.props.showFieldV ? ' show-rfv' : '') }>
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.fieldview.title}</title>
                            <meta name="description" content={MetaData.fieldview.description} />
                            <meta name="keywords" content={MetaData.fieldview.keywords}></meta>
                        </Helmet>
                        {
                            !this.state.isGenrateApi && <CustomLoader isFrom={this.state.isFrom} />
                        }
                        <div className='field-view-cross-button-container'>
                            {
                                <img className='developed-by-container' alt="" src={Images.DEVELOPED_BY_LOGO}></img>
                            }
                            <i style={{top:10,right:10}} onClick={() => { this.goBackToRoster() }} className='icon-close' />
                            
                        </div>
                        <div className={'field-view-container ' + (AppSelectedSport == SportsIDs.cricket ? 'cricket-ground-container' : AppSelectedSport == SportsIDs.soccer ? 'soccer-ground-container' : AppSelectedSport == SportsIDs.badminton ? 'badminton-ground-container' : AppSelectedSport == SportsIDs.kabaddi ? 'kabaddi-ground-container' : AppSelectedSport == SportsIDs.basketball || AppSelectedSport == SportsIDs.NCAA_BASKETBALL ? 'basketball-ground-container' : AppSelectedSport == SportsIDs.football ? 'football-ground-container' : AppSelectedSport == SportsIDs.baseball ? ' baseball-ground-container' :'soccer-ground-container')}>
                            <div className={"player-area " + (!isFieldView && 'hide')}>
                                {(this.props.isFromUpcoming || this.props.isFromLeaderboard) &&
                                    <a href className="close-field-view-right" onClick={this.props.sideViewHide}>
                                        <i className="icon-close"></i>
                                    </a>
                                }
                                <div className={'space-evenly-container' + (isGuru ? ' guru-container': '')}>
                                    {
                                        isGuru &&
                                        <div className="guru-asset-container">
                                            <div className="first-conatiner">
                                                <div onClick={() => { this.goBackToRoster() }}  className="start-over-layout">
                                                    <img src={Images.UNDO} className="icon-image-back"></img>
                                                    <div className="start-over">{AppLabels.START_OVER}</div>

                                                </div>
                                                <div className="salary-remaing-conatiner">
                                                    <div className="salary-budget">{Utilities.getMasterData().currency_code}{this.state.avilableBudget}</div>

                                                    <div className="salary-remaining">{AppLabels.SALARY_REMAINING}</div>

                                                </div>

                                            </div>
                                            <div className="second-conatiner">
                                                <div onClick={() => { this.GuruFieldViewModalShow() }} className="start-over-layout">
                                                    <i className="icon-question icon-image"></i>
                                                    <div  className="start-over">{AppLabels.HOW_TO_PLAY}</div>

                                                </div>
                                                <div className="exclude-lock-conatiner">
                                                    <div className="lock-exlude-layout">
                                                        <div onClick={() => { this.exculedLock(true) }}  className={"exclude-minus"+(this.state.isExludeToggle ? ' enable': '')}>
                                                            <img className="img" src={Images.MINUS_IMG}></img>
                                                        </div>
                                                        <div onClick={() => { this.exculedLock(false) }} className={"lock"+(!this.state.isExludeToggle ? ' enable': '')}>
                                                            <i className="icon-lock-ic ic-lock">
                                                            </i>                                                      
                                                        </div>
                                                        
                                                    </div>
                                                    <div className="salary-remaining">{this.state.isExludeToggle ? AppLabels.REMOVE_PLAYER : AppLabels.LOCK_PLAYER}</div>

                                                </div>

                                            </div>

                                        </div>

                                    }
                                    {
                                        <div onClick={() => { this.perfectLineupSliderShow() }} className='more-team-container'>
                                            <div className='logo-conatiner'>
                                                <img src={Images.PL_LOGO_SMALL} className='icon-img'></img>

                                            </div>
                                            <div className='more-team'>
                                            <div className='want-more-than'>{AppLabels.WANT_MORE_THEN_ONE_TEAM}</div>
                                            <div  className='click-here'>{AppLabels.CLICK_HERE}</div>
                                            </div>
                                           

                                        </div>
                                      }
                                    {_Map(reversePosition, (positem, posidx) => {
                                        return (
                                            
                                            <div key={posidx} >
                                                <div className={'player-position-header guru-line-height'+ (AppSelectedSport == SportsIDs.baseball ? ' baseball-filedview': ' ')}>{positem.position_display_name}</div>
                                                <div className='player-position-row'>
                                                    {_Map(this.filterLineypArrByPosition(positem), (item, idx) => {
                                                        return (
                                                            <div onClick={()=>this.showBreakeDown(item)} key={idx} className={'player-row-container' + (this.state.league_id ? ' cursor-pointer' : '')}>
                                                                
                                                                {
                                                                    item.sports_id != SportsIDs.kabaddi && item.playing_announce == 1 && item.is_playing == 0 &&
                                                                    <span className="playing_indicator danger"></span>
                                                                }

                                                                {isFromRoster == "editView" ? '' :
                                                                    <React.Fragment>

                                                                        {item.captain == 1 && item.is_excluded !=1 &&
                                                                            <span className="captain-player">C</span>
                                                                        }
                                                                        {item.captain == 2 && item.is_excluded !=1 &&
                                                                            <span className="vcaptain-player">V</span>
                                                                        }
                                                                    </React.Fragment>
                                                                }
                                                                {
                                                                    this.state.isExludeToggle ?
                                                                        item.is_locked && item.is_locked != 1 && item.is_excluded && item.is_excluded != 1 ?
                                                                            <img onClick={() => this.exludePlayer(item)} className="remove-icon" src={Images.MINUS_IMG} alt=''></img>
                                                                            :
                                                                            item.is_locked && item.is_locked == 1 ?
                                                                            <div onClick={() => this.lockUnlockPlayer(item)} className={"lock" + (item.is_locked && item.is_locked == 1 ? ' enable' : '')}>
                                                                                <i className="icon-lock-ic ic-lock">
                                                                                </i>
                                                                            </div>
                                                                            :
                                                                            item.is_excluded && item.is_excluded == 1   ? '':
                                                                            <img onClick={() => this.exludePlayer(item)} className="remove-icon" src={Images.MINUS_IMG} alt=''></img>

                                                                        :
                                                                        item.is_excluded && item.is_excluded == 1 ? '':
                                                                        <div onClick={() => this.lockUnlockPlayer(item)} className={"lock" + (item.is_locked && item.is_locked == 1 ? ' enable' : '')}>
                                                                            <i className="icon-lock-ic ic-lock">
                                                                            </i>
                                                                        </div>

                                                                }
                                                                {
                                                                    item.is_excluded && item.is_excluded == 1 ?
                                                                        <img src={Images.BLANK_JERSY} alt="" />

                                                                        :
                                                                        <img src={Utilities.playerJersyURL(item.jersey)} alt="" />

                                                                }
                                                                {
                                                                    item.is_excluded && item.is_excluded == 1 ?
                                                                        <div className="player-name"> {"-"}</div>

                                                                        :
                                                                        <div className="player-name"> {item.full_name}</div>

                                                                }
                                                                {
                                                                    item.is_excluded && item.is_excluded == 1 ?
                                                                        <div className="player-postion">
                                                                            {'-'}
                                                                        </div>
                                                                        :
                                                                        <div className="player-postion">
                                                                            {isFromtab == 1 || isFromtab == 2 || isFromtab == 11 ? '' : Utilities.getMasterData().currency_code + " "} {isFromtab == 1 || isFromtab == 2 || isFromtab == 11 ? item.score : item.salary} {isFromtab == 1 || isFromtab == 2 || isFromtab == 11 ? 'pts' : ''}
                                                                        </div>
                                                                }
                                                                

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
                                {<div className="refresh-save-layout ">
                                    <div onClick={()=>this.refresh()} className='refresh-layout save-team cursor-pointer'>
                                        <div className='refresh-team '>
                                            {AppLabels.REFRESH_TEAM}

                                        </div>
                                        <img src={Images.REFRESH_ICON} className='icon-refresh'></img>
                                    </div>
                                    {/* invalidLineup */}
                                    <div onClick={()=>this.SubmitLineup()} {...{className: `save-team cursor-pointer ${invalidLineup ? 'disabled' : ''}`}}>
                                        {AppLabels.SAVE_THIS_LINEUP}
                                    </div>

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
                                                                            {item.captain == 1 &&
                                                                                <span className="captain-player">C</span>
                                                                            }
                                                                            {item.captain == 2 &&
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
                        {/* {
                        showPlayeBreakDown &&
                        <Suspense fallback={<div />} >
                            <BreakDownPlayerCard IsNetworkGameContest={this.props.IsNetworkGameContest} IsPlayerCardShow={showPlayeBreakDown} playerDetails={playerCard} team_abbr ={playerCard.team_abbr || ''} IsPlayerCardHide={this.PlayerCardHide} selectedGame={selectedGame} />
                        </Suspense>
                    } */}
                        {
                            (guruFieldViewModalShow == 0 || howToPlay) &&
                            <GuruFieldviewDetailModal
                                IsGuruFieldViewModalShow={this.GuruFieldViewModalShow}
                                IsGuruFieldViewModalHide={this.GuruFieldViewModalHide}
                            />
                        }
                         {
                           perfectLineupSlider &&
                            <SliderPerfectLineupModal
                                IsPerfectLineupSliderShow={this.perfectLineupSliderShow}
                                IsperfectLineupSliderHide={this.perfectLineupSliderHide}
                                goToPerFectLineup={this.goToPerFectLineup}
                            />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}
GuruFiledView.defaultProps = {
    hideFieldV: () => {},
    HideFieldView: () => {}
}
export default GuruFiledView