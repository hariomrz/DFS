import React, { Component, Fragment, lazy } from "react";
import { Row, Col,OverlayTrigger, Tooltip, } from 'react-bootstrap';
import * as AL from "../../helper/AppLabels";
import _ from 'lodash';
import { saveBenchPlayer ,getUserLineUps, joinContest,joinContestNetworkfantasy,getLineupMasterData,getTeamDetail,getRosterList,switchTeamContest,joinContestH2H} from "../../WSHelper/WSCallings";
import WSManager from "../../WSHelper/WSManager";
import CustomHeader from '../../components/CustomHeader';
import { DARK_THEME_ENABLE,AppSelectedSport,SELECTED_GAMET,GameType,setValue } from "../../helper/Constants";
import Images from "../../components/images";
import * as WSC from "../../WSHelper/WSConstants";
import { _Map,Utilities,checkBanState, _filter, _isUndefined, _isEmpty } from "../../Utilities/Utilities";
import ls from 'local-storage';
import {createBrowserHistory} from 'history';
import FieldViewRight from "../../views/FieldViewRight";
const ConfirmationPopup = lazy(()=>import('../../Modals/ConfirmationPopup'));
const Thankyou = lazy(()=>import('../../Modals/Thankyou'));
const BenchIntroModal = lazy(()=>import('./BenchIntroModal'));
const BenchPlayerList = lazy(()=>import('./BenchPlayerList'));
const BoosterGameOnModal = lazy(()=>import('../Booster/BoosterGameOnModal'));

var globalThis = null;
const history = createBrowserHistory();
const location = history.location;
const queryString = require('query-string');
const parsed = queryString.parse(location.search);
class BenchSelection extends Component {
    constructor(props) {
        super(props)
        this.state = {
            showBtmBtn: '',
            showCheckbox: false,
            showBenchModal: false,
            showPPList: false,
            selLineupArr: [],
            position:[],
            allRosterList:[],
            isSelectPostion: 1,
            SelectedPlayerPosition: 'WK',
            SelectedPositionName: '',
            MasterData: [],
            allRosterList: [],
            position: [],
            SBPList: [],
            rosterList:[],
            LobyyData:[],
            sort_field: 'salary',//fantasy_score
            sort_order: 'DESC',//ASC
            SPFP:'1',
            BPArry: [{},{},{},{}],
            BPLength: 4,
            collection_master_id:'',
            lineupMasterdId: '',
            PSCount: 0,
            showBoosterModal: false,
            booster_id:'0',
            isBoosterEnable: false,
            showConfirmationPopUp: false,
            TotalTeam: [],
            userTeamListSend: [],
            showThankYouModal: false,
            isEditView: false,
            isClone: false,
            isPlayingAnnounced: 0,
            isBenchUC: false,
            teamName:'',
            isShare: false,
        }

    }

    componentDidMount() { 
        let url = window.location.href;
        if ((this.state.lineupMasterdId == '' || this.state.collection_master_id == '') && url.includes('bench-selection/')) {
            this.getUrlParam(url)
        }
        // if(this.state.lineupMasterdId == ''){
        //     this.setState({
        //         parsed
        //     })
        // }
        if(!ls.get('bim')){
            this.showBenchModalFn()
        }
        if(this.state.benchArr.length > 0 || ls.get('bench_data')){
            let List = this.state.benchArr.length > 0 ? this.state.benchArr : ls.get('bench_data')
            let count = 0;
            for(var item of List){
                if(item.player_team_id){
                    count= count+1
                }
            }
            let SLArry = []
            if(this.state.isFrom == "editView"){
                for(var item of this.state.selLineupArr){
                    SLArry.push(item.player_team_id)
                }
            }
            let tmpArry = this.state.BPArry
            _Map(List,(item,idx)=>{
                if(this.state.isFrom == "editView" && SLArry.includes(item.player_team_id)){
                    return(
                        <>
                            {tmpArry.splice(idx,1)}
                            {tmpArry.splice(idx,0,{})}
                        </>
                    )
                }
                else{
                    return(
                        tmpArry.splice(idx,1,item)
                    )
                }
            })
            this.setState({
                PSCount: count,
                BPArry: tmpArry
            })
        }
        window.addEventListener('scroll', this.onScrollList);
    }

    getUrlParam=(url)=>{
        let tab = url.split('bench-selection/')[1];
        tab =tab.split('/')[0]
        let CMID = tab.split('/')[1];
        this.setState({ 
            lineupMasterdId: tab,
            collection_master_id: CMID
        })
    }

    
    UNSAFE_componentWillMount() {
        if(this.props && this.props.location && this.props.location.state){
            const {from,LobyyData,sports_id,teamName,collection_master_id,players,c_id,vc_id,MasterData,selLineupArr,allRosterList,lineupMasterdId,isFrom,
                isFromMyTeams,isBoosterEnable,isReverseF,isSecIn,FixturedContest,benchArr,isEditView,isClone,isPlayingAnnounced,isBenchUC,
                ifFromSwitchTeamModal,lineup_master_contest_id,isShare} = this.props.location.state;
            this.setState({
                MasterData: MasterData || [],
                selLineupArr: selLineupArr || [],
                position: MasterData && MasterData.all_position ? MasterData.all_position : [], //MasterData && MasterData.position ? MasterData.position : [],
                allRosterList: allRosterList || [],
                LobyyData: LobyyData || [],
                lineupMasterdId: lineupMasterdId || '',
                collection_master_id:collection_master_id || '',
                isFrom:isFrom || '',
                isFromMyTeams:isFromMyTeams || '',
                isBoosterEnable: isBoosterEnable || false,
                isReverseF: isReverseF || false, 
                isSecIn: isSecIn || false,
                FixturedContest: FixturedContest || false,
                benchArr: benchArr || [],
                isEditView: isEditView || false,
                isClone: isClone || false,
                isPlayingAnnounced: isPlayingAnnounced || 0,
                isBenchUC: isBenchUC || false,
                ifFromSwitchTeamModal: ifFromSwitchTeamModal || false,
                lineup_master_contest_id: lineup_master_contest_id || false,
                SelectedPlayerPosition: MasterData && MasterData.all_position && MasterData.all_position.length > 0 ? MasterData.all_position[0].position : 'WK',
                teamName: teamName || '',
                isShare: isShare || false
            },()=>{
                let url = window.location.href;
                if ((this.state.lineupMasterdId == '' || this.state.collection_master_id == '') && url.includes('bench-selection/')) {
                    this.getUrlParam(url)
                }
                if(this.props.location.state.MasterData == undefined || this.props.location.state.MasterData == []){
                    this.getLineupMasterData()
                    this.getAllRoster()
                }
                else{
                    this.setPlayerList(selLineupArr,allRosterList)
                }
            })
        }
    }

    getLineupMasterData=async()=>{
        let param = {
            "league_id": this.state.LobyyData.league_id,
            "sports_id": AppSelectedSport,
            "collection_master_id": this.state.collection_master_id,
        }

        var api_response_data = await getLineupMasterData(param);
        if (api_response_data) {
            this.setState({
                MasterData: api_response_data,
                position: api_response_data.all_position,
                SelectedPlayerPosition: api_response_data.all_position && api_response_data.all_position.length > 0 ? api_response_data.all_position[0].position : 'WK'
            })
        }
        // setTimeout(() => {
            if(this.state.selLineupArr && this.state.selLineupArr.length == 0) {
                this.getuserLineup(this.state.lineupMasterdId,this.state.collection_master_id)
            }
        // }, 10);
    }

    getAllRoster = async () => {
        let param = {
            "league_id": this.state.LobyyData.league_id,
            "sports_id": AppSelectedSport,
            "collection_master_id": this.state.collection_master_id,
        }
        var api_response_data = await getRosterList(param);
        if (api_response_data) {
            let sortedArry = api_response_data.sort((a, b) => b.salary - a.salary);
            this.setState({
                allRosterList: sortedArry
            })
        }
    }

    getuserLineup=async(LMID,CMID)=>{
        let param = {
            "lineup_master_id": LMID,
            "sports_id": AppSelectedSport,
            "collection_master_id": CMID
        }

        var api_response_data = await getTeamDetail(param);
        if (api_response_data) {
            this.setState({
                selLineupArr: api_response_data.data.lineup,
                TmpBPArry: api_response_data.data.bench || []
            },()=>{

                let tmpArry = this.state.BPArry
                let count=this.state.PSCount
                _Map(this.state.TmpBPArry,(item,idx)=>{
                    return(
                        count = count + 1,
                        tmpArry.splice(idx,1,item)
                    )
                })
                this.setState({
                    BPArry: tmpArry,
                    PSCount: count
                })
                if(_isUndefined(this.state.benchArr) ){
                    ls.set('bench_data', this.state.benchArr)
                }
                // setTimeout(() => {
                    this.setPlayerList( this.state.selLineupArr,this.state.allRosterList)
                // }, 100);
            })
        }
    }

    setPlayerList=(selLineupArr,allRosterList)=>{  
        const { isPlayingAnnounced } = this.state
        let promises = _Map(selLineupArr, (item) => {
            return String(item.player_team_id)
        });
        Promise.all(promises).then(follow => {
            let _rosterList = _filter(allRosterList, obj => (!follow.includes(obj.player_team_id) && (isPlayingAnnounced == 0 || (isPlayingAnnounced == 1 && obj.is_playing == 1 ))))
            this.setState({
                rosterList: _rosterList,
                allRosterList: _rosterList
            },()=>{
                this.applyPositionFilter()
            })
        }).catch(err => {
            console.log(err);
        });
    }

    componentWillUnmount() {
        window.removeEventListener('scroll', this.onScrollList);
        this.applyPositionFilter()
        let CinfirmPopUpIsAddFundsClicked = WSManager.getFromConfirmPopupAddFunds()
        let tempIsAddFundsClicked = WSManager.getFromFundsOnly()
        setTimeout(() => {
            if (tempIsAddFundsClicked == 'true' && CinfirmPopUpIsAddFundsClicked == 'true' || CinfirmPopUpIsAddFundsClicked == true) {
                setTimeout(() => {
                    this.callAfterAddFundPopup()
                }, 200);
            }
        }, 500);
    }

    onScrollList = (event) => {
        let scrollOffset = window.pageYOffset;
        this.checkScrollStatus();
        this.setState({
            soff: scrollOffset
        })
        if (this.state.oldScrollOffset < scrollOffset) {
            this.setState({
                showBtmBtn: 'hideBottomBtn',
                oldScrollOffset: scrollOffset
            })
        } else {
            this.setState({
                showBtmBtn: '',
                oldScrollOffset: scrollOffset
            })
        }
    }

    checkScrollStatus() {
        if (this._timeout) { //if there is already a timeout in process cancel it
            clearTimeout(this._timeout);
        }
        this._timeout = setTimeout(() => {
            this._timeout = null;
            this.setState({
                scrollStatus: 'scroll stopped',
                showBtmBtn: ''
            });
        }, 700);
        if (this.state.scrollStatus !== 'scrolling') {
            this.setState({
                scrollStatus: 'scrolling'
            });
        }
    }

    showBenchModalFn=()=>{
        this.setState({
            showBenchModal: true
        })
    }

    hideBenchModalFn=()=>{
        this.setState({
            showBenchModal: false
        })
    }

    dontShowAgain=()=>{
        this.setState({
            showCheckbox: !this.state.showCheckbox
        },()=>{
            if(this.state.showCheckbox){
                ls.set('bim', 1)
            }
        })
    }

    showPList=(forPos)=>{
        this.setState({
            showPPList: true,
            SPFP: forPos
        })
    }
    
    hidePList=()=>{
        this.setState({
            showPPList: false
        })
    }

    SendRosterPosition = (item) => {
        this.setState({ rosterOffset: 0 })
        let tempRosterList = this.state.allRosterList;
        if (this.state.sort_field == 'salary') {
            this.setState({ rosterList: tempRosterList.sort((a, b) => (this.state.sort_order == 'ASC' ? a.salary - b.salary : b.salary - a.salary)) })
        }
        else {
            this.setState({ rosterList: tempRosterList.sort((a, b) => (this.state.sort_order == 'ASC' ? a.fantasy_score - b.fantasy_score : b.fantasy_score - a.fantasy_score)) })
        }
        this.setState({
            isSelectPostion: item.position_order,
            SelectedPlayerPosition: item.position,
            SelectedPositionName: item.position_display_name
        }, () => {            
            this.applyPositionFilter(); //this.applyTeamFilter();
        })
        // this.setState({ PlayerSearch: '' })
    }

    applyPositionFilter() {
        let { rosterList, SelectedPlayerPosition,allRosterList} = this.state;
        let tempRosterList = allRosterList;  
        tempRosterList = allRosterList.filter((player, index, array) => {
            return player.position == SelectedPlayerPosition;
        });  
        this.setState({ rosterList: tempRosterList }, () => {
        })
    }

    addBenchPlayer=(player)=>{
        let tmpArry = this.state.BPArry;
        let idx=this.state.SPFP
        tmpArry.splice(idx,1,player)
        this.setState({
            BPArry: tmpArry,
            PSCount: this.state.PSCount + 1
        },()=>{
            ls.set('bench_data',this.state.BPArry)
            this.hidePList()
        })
    }

    removePlayer=(player)=>{
        let tmpArry = this.state.BPArry;
        var index = 0;
        for(var item of this.state.BPArry){
            if(item.player_team_id == player.player_team_id){
                tmpArry.splice(index,1)
                tmpArry.splice(index,0,{})
            }
            else{
                index++
            }
        }
        this.setState({
            BPArry: tmpArry,
            PSCount: this.state.PSCount - 1
        },()=>{
            if(this.state.PSCount == 0){
                ls.remove('bench_data')
            }
            else{
                ls.set('bench_data',tmpArry)
            }
        })
        
    }

    // filterLineypArrByPosition = (player) => {
    //     let arrPositionOfSelectedPlayer = this.state.SBPList.filter(function (item) {
    //         return item.position == player.position
    //     })
    //     return arrPositionOfSelectedPlayer
    // }

    sortField=(sortFor)=>{
        let SField = sortFor == 'FS' ? 'fantasy_score' : 'salary';
        let SOrder = (this.state.sort_order == 'DESC' ? 'ASC' : 'DESC');

        // if(sortFor == 'FS'){
            this.setState({ 
                sort_field: SField, 
                sort_order: SOrder,
                rosterList: sortFor == 'FS' ? (this.state.rosterList.sort((a, b) => (this.state.sort_order == 'DESC' ? a.fantasy_score - b.fantasy_score : b.fantasy_score - a.fantasy_score))) : (this.state.rosterList.sort((a, b) => (this.state.sort_order == 'DESC' ? a.salary - b.salary : b.salary - a.salary)))
            })
        // }
        // else{
        //     this.setState({ 
        //         sort_field: 'salary', 
        //         sort_order: (this.state.sort_order == 'DESC' ? 'ASC' : 'DESC'),
        //         rosterList: this.state.rosterList.sort((a, b) => (this.state.sort_order == 'DESC' ? a.salary - b.salary : b.salary - a.salary))
        //     })
        // }
    }

    submitBench=()=>{
        const {isEditView,isClone,isBenchUC} = this.state;
        let playerArry = [];
        for(var pl of this.state.BPArry){
            if(pl.player_team_id){
                playerArry.push(pl.player_team_id)
            }
        }
        let param = {
            "sports_id": AppSelectedSport,
            "lineup_master_id": this.state.lineupMasterdId,
            "players": playerArry,
        }
        if((isEditView || isBenchUC) && !isClone){
            param['edit_bench'] = 1
        }
        this.setState({ isLoaderShow: true })
        saveBenchPlayer(param).then((responseJson) => {
            console.log(responseJson);
            if (responseJson.response_code == WSC.successCode) {
                Utilities.showToast(responseJson.message, 1000);
                if(this.state.isFrom == 'MyContest'){
                    this.props.history.goBack();
                }
                else{
                    this.SubmitLineup()
                }
            } else {
                Utilities.showToast(responseJson.message, 5000);
            }
        })
    }

    skipBench=(isSkip)=>{
        let skip = isSkip || false;
        const {isEditView , isClone} = this.state;
        if(skip){
            if((isClone || isEditView && !isClone && this.isBenchEmpty()) || (!isEditView && !isClone)){
                this.setState({
                    BPArry: [{},{},{},{}],
                })
                ls.remove('bench_data')
                if(this.state.isFrom == 'MyContest'){
                    this.props.history.goBack();
                }
                else{
                    this.SubmitLineup()
                }
            } else {
                this.submitBench()
            }
        }
    }

    SubmitLineup=()=>{
        const {isBoosterEnable,lineupMasterdId} = this.state;
        if (this.state.isFrom == 'editView' && !this.state.isFromMyTeams) {
            // Utilities.showToast(responseJson.message, 1000);
                this.props.history.push({ pathname: '/my-contests', state: { from: 'SelectCaptain' } });
        }
        else if ((this.state.isFrom == "MyTeams" || this.state.isFrom == "MyContest" || this.state.isFrom == "editView") && this.state.isFromMyTeams) {
            // Utilities.showToast(responseJson.message, 1000);
            if(isBoosterEnable && this.state.isFrom != "editView"){
                this.BoosterModalShow()
            }else if(isBoosterEnable && this.state.isClone){
                this.BoosterModalShow()
            }
            else{
                this.skipBoosterAndContinue()
            }
          }
        else if (this.state.ifFromSwitchTeamModal) {

            if (isBoosterEnable) {
                this.BoosterModalShow()

            } else {
                // Utilities.showToast(responseJson.message, 1000);
                this.switchTeam(this.state.FixturedContest, 
                    lineupMasterdId,
                    // this.state.FixturedContest.is_network_contest == 1 ? responseJson.data.network_lineup_master_id : responseJson.data.lineup_master_id,
                     this.props.location.state.lineup_master_contest_id);

            }
        }
        else {
            // if(checkBanState(this.state.FixturedContest,CustomHeader, 'CAP') || this.state.isShare){
            if(checkBanState(this.state.FixturedContest,CustomHeader, 'CAP', this.state.isShare)){
                    if (isBoosterEnable) {
                        this.BoosterModalShow()
                    } else {
                        this.getUserLineUpListApi();

                    }

            }
        }

    }

    switchTeam(FixturedContest, lineup_master_id, lineup_master_contest_id) {
        let param = {
            "sports_id": AppSelectedSport,
            "contest_id": FixturedContest.contest_id,
            "lineup_master_id":lineup_master_id,
            "lineup_master_contest_id": lineup_master_contest_id,
        }

        let apiCall = switchTeamContest
        this.setState({ isLoaderShow: true })
        apiCall(param).then((responseJson) => {
            this.setState({ isLoaderShow: false })
            if (responseJson.response_code == WSC.successCode) {
                Utilities.showToast(responseJson.message, 5000);
                this.props.history.push({ pathname: '/my-contests', state: { from: 'SelectCaptain',isReverseF: this.state.isReverseF } });
                WSManager.clearLineup();
            }
        })

    }

      /**
     * @description method to Bosster model
     */
    BossterModalHide = () => {
        this.setState({
            showBoosterModal: false,
        });
    }
     /**
     * @description method to Bosster model
     */
    BoosterModalShow = () => {
        this.setState({
            showBoosterModal: true,
        });
    }

    skipBoosterAndContinue = () => {
        this.BossterModalHide()
         if ((this.state.isFrom == "MyTeams" || this.state.isFrom == "MyContest" || this.state.isFrom == "editView") && this.state.isFromMyTeams) {

            var go_index = -3;
            if (this.state.isFrom == "editView" && !this.state.isClone && !this.state.isFromMyTeams) {
                go_index = -4;
            }
            WSManager.clearLineup();
            this.props.history.go(go_index);
        }
        else if (this.state.ifFromSwitchTeamModal) {
            this.switchTeam(this.state.FixturedContest, this.state.lineupMasterdId, this.props.location.state.lineup_master_contest_id);

        }
        else {
            if (checkBanState(this.state.FixturedContest, CustomHeader, 'CAP')) {
                this.getUserLineUpListApi();
            }
        }
 
    }

    getUserLineUpListApi() {
        let param = {
            "sports_id": AppSelectedSport,
            "collection_master_id": this.state.LobyyData.collection_master_id ? this.state.LobyyData.collection_master_id : this.state.FixturedContest.collection_master_id,
            "league_id": this.state.LobyyData.league_id
        }
        this.setState({ isLoaderShow: true })
        getUserLineUps(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                let tList = this.state.isSecIn ? _filter(responseJson.data,(obj,idx) => {
                    return obj.is_2nd_inning == "1";
                }) : this.state.isReverseF ? _filter(responseJson.data,(obj,idx) => {
                    return obj.is_reverse == "1";
                }) : _filter(responseJson.data,(obj,idx) => {
                    return (obj.is_reverse != "1" && obj.is_2nd_inning != "1")
                })
                this.setState({
                    showConfirmationPopUp: true,
                    TotalTeam: tList,//responseJson.data,
                    userTeamListSend: tList /*(this.state.allowRevFantasy && SELECTED_GAMET == GameType.DFS) ? responseJson.data.filter((obj,idx) => {
                        return (this.state.isReverseF ? obj.is_reverse == "1": obj.is_reverse != "1");
                    }) : responseJson.data*/
                })
                if (this.state.userTeamListSend) {
                    let tempList = [];
                    this.state.userTeamListSend.map((data, key) => {

                        tempList.push({ value: data, label: data.team_name })
                        return '';
                    })
                    this.setState({ userTeamListSend: tempList });
                }
            }
        })
    }



    ConfirmatioPopUpShow = (data) => {
        this.setState({
            showConfirmationPopUp: true,
        });
    }


    ConfirmatioPopUpHide = () => {
        this.setState({
            showConfirmationPopUp: false,
        });
    }

    ThankYouModalShow = (data) => {
        this.setState({
            showThankYouModal: true,
        });
    }

    ThankYouModalHide = () => {
        this.setState({
            showThankYouModal: false,
        });
    }

    GoToFieldView = () => {
        const { LobyyData , BPArry} = this.state;

        let urlParams = '';
        if (LobyyData && LobyyData.match_list && LobyyData.match_list.length == 1) {
            urlParams = Utilities.setUrlParams(LobyyData);
        }
        else {
            urlParams = Utilities.replaceAll(LobyyData.collection_name, ' ', '_')
        }
        let fieldViewPath = '/field-view/' + urlParams;
        this.props.history.push({ pathname: fieldViewPath.toLowerCase(), state: { SelectedLineup: this.state.selLineupArr, MasterData: this.state.MasterData, LobyyData: this.state.LobyyData, FixturedContest: this.state.FixturedContest, isFrom: this.state.isFrom, rootDataItem: this.state.LobyyData, team: this.state.team, team_name: this.state.teamName, resetIndex: 1 ,benchPlayer: BPArry, isFromBS: true} })
    }

    createTeamAndJoin = (dataFromConfirmFixture, dataFromConfirmLobby) => {
        this.props.history.push({ pathname: '/lineup', state: { FixturedContest: dataFromConfirmFixture, LobyyData: dataFromConfirmLobby, current_sport: AppSelectedSport,isReverseF: this.state.isReverseF, isSecIn: this.state.isSecIn } })
    }

    ConfirmEvent = (dataFromConfirmPopUp, context) => {
        // Constants.setValue.SetRFContestId(FixturedContestItem.collection_master_id);
        if (dataFromConfirmPopUp.selectedTeam.value.lineup_master_id && dataFromConfirmPopUp.selectedTeam.value.lineup_master_id != null && dataFromConfirmPopUp.selectedTeam.lineup_master_id == "" || dataFromConfirmPopUp.selectedTeam == "") {
            Utilities.showToast(AL.SELECT_NAME_FIRST, 1000);
        } else {
            var currentEntryFee = 0;
            currentEntryFee = dataFromConfirmPopUp.entryFeeOfContest;
            if (
                (dataFromConfirmPopUp.FixturedContestItem.currency_type == 2 && (parseInt(currentEntryFee) <= parseInt(dataFromConfirmPopUp.balanceAccToMaxPercent))) || 
                (dataFromConfirmPopUp.FixturedContestItem.currency_type != 2 && (parseFloat(currentEntryFee) <= parseFloat(dataFromConfirmPopUp.balanceAccToMaxPercent)))
                ) 
            {
                this.CallJoinGameApi(dataFromConfirmPopUp);
            } 
            else {
                if(this.state.isReverseF && SELECTED_GAMET == GameType.DFS && this.state.allowRevFantasy){
                    let collectionMatserId = this.state.LobyyData.collection_master_id ? this.state.LobyyData.collection_master_id : this.state.FixturedContest.collection_master_id;
                    setValue.SetRFContestId(collectionMatserId)
                }
                if(dataFromConfirmPopUp.FixturedContestItem.currency_type == 2){
                    if(Utilities.getMasterData().allow_buy_coin == 1){     
                        WSManager.setFromConfirmPopupAddFunds(true);
                        WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp)
                        WSManager.setPaymentCalledFrom("SelectCaptainList")
                        this.props.history.push({ pathname: '/buy-coins', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true , state: {isFrom : 'SelectCaptainList',isReverseF: this.state.isReverseF}});

                    }
                    else{
                        // Utilities.showToast('Not enough coins', 1000);
                        this.props.history.push({ pathname:'/earn-coins', state: {isFrom : 'lineup-flow',isReverseF: this.state.isReverseF}})
                    }
                }
                else{
                    WSManager.setFromConfirmPopupAddFunds(true);
                    WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp)
                    WSManager.setPaymentCalledFrom("SelectCaptainList")
                    this.props.history.push({ pathname: '/add-funds', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true,isReverseF: this.state.isReverseF, isSecIn: this.state.isSecIn });
                }

                //Analytics Calling 
                WSManager.googleTrack(WSC.GA_PROFILE_ID, 'paymentgateway');
            }
        }
    }


    CallJoinGameApi(dataFromConfirmPopUp) {
        var contestId = SELECTED_GAMET == GameType.Free2Play ? this.state.LobyyData.contest_id : dataFromConfirmPopUp.FixturedContestItem.contest_id
        let isH2h = dataFromConfirmPopUp.FixturedContestItem.contest_template_id ? true :false;

        let param = {
            "contest_id": isH2h ?dataFromConfirmPopUp.FixturedContestItem.contest_template_id :contestId,
            "lineup_master_id": dataFromConfirmPopUp.selectedTeam.lineup_master_id ? dataFromConfirmPopUp.selectedTeam.lineup_master_id : dataFromConfirmPopUp.selectedTeam.value.lineup_master_id,
            "promo_code": dataFromConfirmPopUp.promoCode,
            "device_type":window.ReactNativeWebView ? WSC.deviceTypeAndroid : WSC.deviceType
        }

        let contestUid = dataFromConfirmPopUp.FixturedContestItem.contest_unique_id
        let contestAccessType = dataFromConfirmPopUp.FixturedContestItem.contest_access_type;
        let isPrivate = dataFromConfirmPopUp.FixturedContestItem.is_private;

        this.setState({ isLoaderShow: true })
        let IsNetworkContest = this.props && this.props.location && this.props.location.state && this.props.location.state.FixturedContest && this.props.location.state.FixturedContest.is_network_contest == 1 ? true : this.state.LobyyData.is_network_contest!= undefined && this.state.LobyyData.is_network_contest == 1 ? true: false ;
        if(this.state.isSecIn){
            param['is_2nd_inning'] = 1
        }
        let apiCall = IsNetworkContest ? joinContestNetworkfantasy : isH2h ? joinContestH2H : joinContest;
        apiCall(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                if(isH2h){
                    Utilities.setH2hData(dataFromConfirmPopUp,responseJson.data.contest_id)
                }
                if(process.env.REACT_APP_SINGULAR_ENABLE > 0)
                {
                    let singular_data = {};
                    singular_data.user_unique_id    = WSManager.getProfile().user_unique_id;
                    singular_data.contest_id        = dataFromConfirmPopUp.FixturedContestItem.contest_id;
                    singular_data.contest_date      = dataFromConfirmPopUp.lobbyDataItem.season_scheduled_date;
                    singular_data.fixture_name      = dataFromConfirmPopUp.lobbyDataItem.collection_name;
                    singular_data.entry_fee         = dataFromConfirmPopUp.FixturedContestItem.entryFeeOfContest;

                    if (window.ReactNativeWebView) {
                        let event_data = {
                            action: 'singular_event',
                            targetFunc: 'onSingularEventTrack',
                            type: 'Contest_joined',
                            args: singular_data,
                        }
                        window.ReactNativeWebView.postMessage(JSON.stringify(event_data));
                    }
                    else {
                        window.SingularEvent("Contest_joined", singular_data);
                    }
                }
                Utilities.gtmEventFire('join_contest', {
                    fixture_name: dataFromConfirmPopUp.lobbyDataItem.collection_name,
                    contest_name: dataFromConfirmPopUp.FixturedContestItem.contest_title,
                    league_name: dataFromConfirmPopUp.lobbyDataItem.league_name,
                    entry_fee: dataFromConfirmPopUp.FixturedContestItem.entry_fee,
                    fixture_scheduled_date: Utilities.getFormatedDateTime(dataFromConfirmPopUp.lobbyDataItem.season_scheduled_date, 'YYYY-MM-DD HH:mm:ss'),
                    contest_joining_date: Utilities.getFormatedDateTime(new Date(), 'YYYY-MM-DD HH:mm:ss'),
                })

                // if (contestAccessType == '1' || isPrivate == '1') {
                //     WSManager.updateFirebaseUsers(contestUid);
                // }
                if (contestAccessType == '1' || isPrivate == '1') {
                    let deviceIds = [];
                    deviceIds = responseJson.data.user_device_ids;
                    WSManager.updateFirebaseUsers(contestUid,deviceIds);
                }
                this.ConfirmatioPopUpHide();
                setTimeout(() => {
                    this.ThankYouModalShow()
                }, 300);
                WSManager.clearLineup();
            } else {
                if(Utilities.getMasterData().allow_self_exclusion == 1 && responseJson.data.self_exclusion_limit == 1 ){
                    this.ConfirmatioPopUpHide();
                    this.showUJC(); 
                }
                else{
                    Utilities.showToast(responseJson.global_error != "" ? responseJson.global_error : responseJson.message, 2000);
                }
            }
        })

        //Analytics Calling 
        WSManager.googleTrack(WSC.GA_PROFILE_ID, 'joingame');


    }

    callAfterAddFundPopup() {
        if (WSManager.getFromConfirmPopupAddFunds()) {
            WSManager.setFromConfirmPopupAddFunds(false);
            var contestData = WSManager.getContestFromAddFundsAndJoin();
            this.ConfirmEvent(contestData)
        }
    }

    openRosterCollection =()=>{
        this.props.history.push({
            pathname: `/booster-collection/${this.state.LobyyData.collection_master_id ? this.state.LobyyData.collection_master_id : this.state.FixturedContest.collection_master_id}/${Utilities.getSelectedSportsForUrl().toLowerCase()}/${(this.state.lineupMasterdId)}`
            ,state: {LobyyData:this.state.LobyyData,FixturedContest: this.state.FixturedContest,team_name:this.state.teamName,isFromFlow:this.state.isFrom,isFromMyTeams:this.state.isFromMyTeams,booster_id: this.state.isClone ? this.state.booster_id :this.props.location.state.teamitem &&  this.props.location.state.teamitem.booster_id && this.props.location.state.teamitem.booster_id ? this.props.location.state.teamitem.booster_id:this.state.booster_id,direct:false,ifFromSwitchTeamModal:this.state.ifFromSwitchTeamModal,lineup_master_contest_id:this.state.ifFromSwitchTeamModal ? this.props.location.state.lineup_master_contest_id:0,isPlayingAnnounced: this.state.isPlayingAnnounced,isFromBench: true}
        })
    }

    goToLobby = () => {
        ls.remove('bench_data')
        // this.props.history.push({ pathname: '/' });
        const { LobyyData, FixturedContest } = this.state;
        console.log('SCL LobyyData',LobyyData)
        console.log('SCL FixturedContest',FixturedContest)
        let dateformaturl = Utilities.getUtcToLocal(FixturedContest.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);

        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)

        let home = FixturedContest.home || LobyyData.home;
        let away = FixturedContest.away || LobyyData.away;


        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
            let contestListingPath = this.state.isSecIn ?
                '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest-listing/' + FixturedContest.collection_master_id + '/' + home + "-vs-" + away + "-" + dateformaturl + "?sgmty=" + btoa(SELECTED_GAMET) + '&sit=' + btoa(true)
                : '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest-listing/' + FixturedContest.collection_master_id + '/' + home + "-vs-" + away + "-" + dateformaturl + "?sgmty=" + btoa(SELECTED_GAMET)
            this.setState({ LobyyData: FixturedContest });
            contestListingPath = contestListingPath.toLowerCase()

            console.log('SCL contestListingPath',contestListingPath)
            this.props.history.push({ pathname: contestListingPath, state: { FixturedContest: this.state.FixtureData, LobyyData: LobyyData, isFromPM: true, isJoinContestFlow: true } })
    }

    seeMyContest = () => {
        ls.remove('bench_data')
        this.props.history.push({ pathname: '/my-contests', state: { from: 'SelectCaptain' } });
    }
    isBenchEmpty = () => {
        return _filter(this.state.BPArry, o => !_isEmpty(o)).length == 0;
    }

    render() {
        let {
            showBenchModal, 
            showPPList, 
            position, 
            MasterData, 
            selLineupArr,
            allRosterList,
            rosterList,
            isSelectPostion,
            sort_order,
            sort_field,
            SPFP,
            BPArry,
            LobyyData,
            PSCount,
            showBoosterModal,
            showConfirmationPopUp,
            userTeamListSend,
            showThankYouModal,
            isSecIn,
            TotalTeam,
            lineupMasterdId,
            isEditView,
            isClone,
            isBenchUC
        } = this.state;
        const HeaderOption = {
            back: true,
            fixture: true,
            title: '',
            isPrimary: DARK_THEME_ENABLE ? false : true
        }
        return (
            <div className="web-container bench-selction-wrap ">
                <CustomHeader {...this.props} LobyyData={LobyyData} HeaderOption={HeaderOption} />
                
                <a href className={"header-skip-sec" + ( (isClone || isEditView && !isClone && this.isBenchEmpty()) ? '' : (!isEditView && !isClone ? '' : ' disabled'))} onClick={()=> this.skipBench(true)}>{AL.SKIP_STEP}</a>

                
                <div className="ben-intro-main-sec">
                    <div className="ben-intro-sec">
                        <div className="heading">
                            {AL.PICK_YOUR_BENCH}
                            <OverlayTrigger trigger={['click']} placement="bottom" overlay={
                                <Tooltip id="tooltip" className="bench-tooltip">
                                    <ul>
                                        <li>{AL.BENCH_RULE1}</li>
                                        <li>{AL.BENCH_RULE2}</li>
                                        <li>{AL.BENCH_RULE3}</li>
                                    </ul>
                                </Tooltip>
                            }>
                                <a href className="WIB">
                                    {AL.WHAT_IS_BENCH}
                                </a>
                            </OverlayTrigger>
                        </div>
                        <div className="sub-heading">{AL.NOT_APPLIED_BENCH_TEXT}</div>
                    </div>
                    <div className="primary-overlap"></div>
                </div>
                <div className="bench-plr-list">
                    <Row>
                        {
                            // BPArry.length > 0 && 
                            _Map(BPArry,(item,idx)=>{
                                return(
                                    <>
                                        {
                                            item.player_team_id ?
                                            <Col sm={6} xs={6} className="text-center info-card">
                                                <div className="info-card-inn">
                                                    <a href  onClick={()=>this.removePlayer(item)}>
                                                        <i className="icon-close"></i>
                                                    </a>
                                                    <div className='plyr-img'>
                                                        <img src={Utilities.playerJersyURL(item.jersey)} alt=""/>
                                                    </div>
                                                    <div className="player-nm">{item.display_name || item.full_name}</div>
                                                    <div className="plr-desc">{item.team_abbreviation || item.team_abbr}  <span>|</span>  {item.position}  <span>|</span>  {item.salary}</div>
                                                    <div className="pos-sec">
                                                        <span>{idx+1} {AL.POSITION}</span>
                                                    </div>
                                                </div>
                                            </Col>
                                            :
                                            <Col sm={6} xs={6} className="text-center info-card">
                                                <div className="info-card-inn sel-play" onClick={()=>this.showPList(idx)}>
                                                    <div className='plyr-img'>
                                                        <img src={Images.TSHIT_WPLUS} alt="" className="cursor-pointer" />
                                                    </div>
                                                    <div className="player-nm">{AL.ADD_PLAYER}</div>
                                                    <div className="pos-sec">
                                                        <span>{idx+1} {AL.POSITION}</span>
                                                    </div>
                                                </div>
                                            </Col>
                                        }
                                    </>
                                )
                            })
                        }
                    </Row>
                </div>
                <div className={"footer-act " +  this.state.showBtmBtn}>
                    <a href className="btn btn-primary btn-rounded team-preview" onClick={() => this.GoToFieldView()}>{AL.TEAM_PREVIEW}</a>
                    <a href className={"btn btn-primary btn-rounded" + (PSCount == 0 && (!isEditView || (isEditView && isClone)) && !isBenchUC ? '  disabled' : '')} onClick={()=>this.submitBench()}>{AL.SUBMIT}</a>
                </div>
                {
                    showBenchModal &&
                    <BenchIntroModal
                        dontShowAgain={this.dontShowAgain}
                        MShow={showBenchModal}
                        MHide={this.hideBenchModalFn}
                    />
                }
                {
                    showPPList &&
                    <BenchPlayerList 
                        SPFP={SPFP}
                        MasterData={MasterData}
                        allRosterList={allRosterList}
                        rosterList={rosterList}
                        isSelectPostion={isSelectPostion}
                        position={position}
                        sort_field={sort_field}
                        sort_order={sort_order}
                        sortField={this.sortField}
                        hidePList={this.hidePList}
                        showPList={showPPList}
                        SendRosterPosition={this.SendRosterPosition}
                        applyPositionFilter={this.applyPositionFilter}
                        addBenchPlayer={this.addBenchPlayer}
                        addedPList={BPArry}
                        // filterLineypArrByPosition={this.filterLineypArrByPosition}
                    />
                }
                <FieldViewRight
                    SelectedLineup={this.state.selLineupArr.length ? this.state.selLineupArr : []}
                    MasterData={MasterData}
                    LobyyData={this.state.LobyyData}
                    FixturedContest={this.state.FixturedContest}
                    isFrom={this.state.isFrom}
                    rootDataItem={this.state.LobyyData}
                    team={this.state.team}
                    team_name={this.state.teamName}
                    resetIndex={1}
                    TeamMyContestData={this.state.TeamMyContestData ? this.state.TeamMyContestData : this.props.location.state.TeamMyContestData}
                    isFromMyTeams={this.state.isFromMyTeams}
                    ifFromSwitchTeamModal={this.state.ifFromSwitchTeamModal}
                    current_sports_id={this.state.current_sports_id}
                    benchPlayer= {BPArry}
                    isBenchUC= {this.state.isBenchUC}
                    updateTeamDetails={new Date().valueOf()}
                    // isFrom={'roster'}

                />
                 
                {showConfirmationPopUp &&
                    <ConfirmationPopup lobbyDataToPopup={this.state.LobyyData} IsConfirmationPopupShow={this.ConfirmatioPopUpShow} IsConfirmationPopupHide={this.ConfirmatioPopUpHide} TeamListData={userTeamListSend} TotalTeam={TotalTeam} FixturedContest={this.state.FixturedContest} ConfirmationClickEvent={this.ConfirmEvent} CreateTeamClickEvent={this.createTeamAndJoin} fromContestListingScreen={false} createdLineUp={lineupMasterdId} isSecIn = {isSecIn}/>
                }

                {showThankYouModal &&
                    <Thankyou ThankyouModalShow={this.ThankYouModalShow} ThankYouModalHide={this.ThankYouModalHide} goToLobbyClickEvent={this.goToLobby} seeMyContestEvent={this.seeMyContest} />
                }
                {
                    showBoosterModal &&
                    <BoosterGameOnModal team_name={this.state.teamName} gotoBooster={this.openRosterCollection} skipToMyTeam ={this.skipBoosterAndContinue} IsBoosterModalShow={this.BoosterModalShow}
                        IsBoosterModalHide={this.BossterModalHide} />
                }
            </div>
        )
    }
}
export default BenchSelection

