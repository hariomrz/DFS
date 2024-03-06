import React, { Suspense, lazy } from 'react';
import {  Row, Col, Tab, Nav, NavItem ,FormGroup,Table } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import { getStockFixtureDetail, getLSFAllStock, getLSFUserLineup, addRemoveStockWishlist,getStockWishlist ,getLSFCollectionStatics,LSF_USER_TRADE,getLSFHOLIDAYLIST} from "../../WSHelper/WSCallings";
import { Utilities, _isUndefined, _isEmpty, _Map,isDateTimePast } from '../../Utilities/Utilities';
import { Helmet } from "react-helmet";
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import ls from 'local-storage';
import Images from '../../components/images';
import WSManager from "../../WSHelper/WSManager";
import InfiniteScroll from 'react-infinite-scroll-component';
import Skeleton,{SkeletonTheme} from 'react-loading-skeleton';
import MetaData from "../../helper/MetaData";
import CustomHeader from '../../components/CustomHeader';
import { DARK_THEME_ENABLE,setValue, StockSetting } from '../../helper/Constants';
import { inputStyle } from '../../helper/input-style';
import { NoDataView } from '../CustomComponent';
import { MomentDateComponent } from "../../Component/CustomComponent";
import moment from 'moment';
const StockPlayerCard = lazy(() => import('../StockFantasy/StockPlayerCard'));
const LSFRules = lazy(() => import('./LSFRules'));
const LSFStockActionModal = lazy(() => import('./LSFStockActionModal'));
const LSFStockBuyModal = lazy(() => import('./LSFStockBuyModal'));
const LSFExitStockModal = lazy(() => import('./LSFExitStockModal'));
const LSFTeamPreview = lazy(() => import('./LSFTeamPreview'));

const Shimmer = () => {
    return (
        <SkeletonTheme color={DARK_THEME_ENABLE ? "#161920" : null} highlightColor={DARK_THEME_ENABLE ? "#0E2739" : null}>
            <div className="ranking-list shimmer margin-2p">
                <div className="display-table-cell text-center">
                    <div className="rank">--</div>
                    <div className="rank-heading">{AL.RANK}</div>
                </div>
                <div className="display-table-cell pl-1 pointer-cursor">
                    <figure className="user-img shimmer">
                        <Skeleton circle={true} width={40} height={40} />
                    </figure>
                    <div className="user-name-container shimmer">
                        <Skeleton width={'80%'} height={8} />
                        <Skeleton width={'40%'} height={5} />
                    </div>
                </div>
                <div className="display-table-cell">
                    <div className="points">--</div>
                </div>
            </div>
        </SkeletonTheme>
    )
}
export default class LSFRoster extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            teamName: '',
            sort_field: 'salary',
            sort_order: 'DESC',
            showPlayerCard: false,
            playerDetails: {},
            collectionMasterId: '',
            masterData: '',
            lineupArr: ls.get('Lineup_data') ? ls.get('Lineup_data') : [],
            teamList: [],
            rosterList: [],
            allRosterList: [],
            isTableLoaderShow: false,
            selectedTeamOption: { value: { team_league_id: 0 }, label: AL.ALL_STOCK },
            contestListData: '',
            LobyyData: '',
            FixturedContest: '',
            isFrom: '',
            teamData: '',
            rootDataItem: '',
            isFromMyTeams: false,
            TeamMyContestData: '',
            isClone: false,
            showFilterByTeam: false,
            showBtmBtn: '',
            oldScrollOffset: 0,
            soff: 0,
            scrollStatus: '',
            showRulesModal: false,
            selectedTab: '1',
            CPts: 0,
            VCPts: 0,
            SearchVal: '',
            GData: [],
            LData: [],
            myWList: [],
            AllGData: [],
            AllLData: [],
            AllmyWList: [],
            isLLoading: false,
            ShimmerList: [1, 2, 3, 4, 5, 1, 2, 3, 4, 5, 1, 2, 3, 4, 5],
            selStkIdAry: [],
            allStkId: [],
            salaryCap: 500000,
            HeaderOption: {
                back: true,
                isPrimary: DARK_THEME_ENABLE ? false : true,
                showRS: true,
                title: '',
                hideShadow: false,
                showAlertRoster: false,
                resetIndex: '',
                // screenDatetitle: '' ,
                // showleagueTime: true,
                showRSAction: this.openRulesModal,
                reminingBudget: 500000 ,
                brokerage: 0
            },
            userLineupApiCalled: false,
            showStkModal:false,
            showExitStock:false,
            action: 0,
            showTeamPreview: false,
            holidayList: [],
            isMarketOpen: true,
            lineupArLength:0,
            userRank: '',
            lineupMCID: '',
            WLApiCall: false,
            allGLData: [],
            GLApiCall: false,
            ASApiCall: false
        };
        this._timeout = null;
        this.checkScrollStatus = this.checkScrollStatus.bind(this);
        this.headerRef = React.createRef();
    }

    onTabClick=(selectedTab)=>{
        
        // this.checkUrl(selectedTab)
        this.setState({ selectedTab: selectedTab },()=>{
            this.setTabData(selectedTab)
            this.scrollWindowToTop()
        });
    }

    checkUrl=(selectedTab)=>{
        let url = window.location.href;
        // selectedTab = selectedTab || 1
        // window.history.replaceState("", "", "/my-contests?tab=" + my_contest_config.contest[selectedTab]);

        if (url.includes('tab')) {
            // let nurl = url.split('tab')[1]; 
            let ourl = url.split('tab')[0];
            window.history.replaceState("", "", ourl + "tab=" + selectedTab);
        }
    }

    setTabData=(selectedTab)=>{
        if(selectedTab == '2'){
            if(!this.state.WLApiCall){
                this.callGetStockWishlist()
            }
        }
        else if(selectedTab == '3' || selectedTab == '4'){
            if(this.state.GLApiCall){
                this.setData(1,this.state.allGLData)
                this.setData(2,this.state.allGLData)
            }
            else{
                this.callStaticsApi()
            }
        }
        else{
            if(_isUndefined(this.state.allRosterList) || this.state.allRosterList == []){
                this.getAllRoster();
            }
            // this.fetchLineupMasterData();
        }
    }

    callGetStockWishlist() {
        let param = {
            "day_filter": 1
        }
        this.setState({ isLoading: true })
        getStockWishlist(param).then((responseJson) => {
            this.setState({ isLoading: false })
            if (responseJson && responseJson.response_code == WSC.successCode) {
                let data = responseJson.data || []
                
                let tmpAry = []
                _Map(data, (item) => {
                    item['is_wish'] = '1'
                    for(var stk of this.state.allStkId){
                        if(item.stock_id == stk){
                            tmpAry.push(item)
                            break;
                        }
                    }
                })

                let FTmpArray = tmpAry && tmpAry.length > 0 && this.state.SearchVal != '' ? tmpAry .filter((stock) => {
                    return stock.stock_name.toLowerCase().includes(this.state.SearchVal)
                }) : tmpAry

                this.setState({
                    myWList: FTmpArray,
                    AllmyWList: tmpAry,
                    WLApiCall: true
                },()=>{
                    let tmpAry = this.addSelStk(this.state.myWList,this.state.selStkIdAry)
                    this.setState({
                        myWList: tmpAry
                    })
                })
            }
        })
    }

    callStaticsApi=()=>{
        this.setState({
            isLLoading: true
        })
        let param = {
            collection_id: this.state.LobyyData.collection_id
            // "day_filter": 3, //this.state.filterBy,
            // "type": isFor //this.state.viewMoreType
        }
        getLSFCollectionStatics(param).then((responseJson) => { //getStockStatictics
            if (responseJson.response_code == WSC.successCode) {
                this.setState({
                    isLLoading: false,
                    allGLData: responseJson.data,
                    GLApiCall: true
                })
                this.setData(1,responseJson.data) //isFor == 1 for gainer, 2 for loser
                this.setData(2,responseJson.data) //isFor == 1 for gainer, 2 for loser
            }
        })
    }
    setData=(isFor, data)=>{
        if(isFor == 1){
            let tmpFilterGArray = [];
            tmpFilterGArray = this.state.SearchVal != '' ? data.gainers .filter((stock) => {
                return stock.name.toLowerCase().includes(this.state.SearchVal)
            }) : data.gainers 
            this.setState({
                GData: tmpFilterGArray ,
                AllGData: data.gainers ,
            },()=>{
                let tmpAry = this.addSelStk(this.state.GData,this.state.selStkIdAry)
                this.setState({
                    GData: tmpAry
                })
            })
        }
        else{
            let tmpFilterLArray = [];
            tmpFilterLArray = this.state.SearchVal != '' ? data.losers .filter((stock) => {
                return stock.name.toLowerCase().includes(this.state.SearchVal)
            }) : data.losers 
            this.setState({
                LData: tmpFilterLArray,
                AllLData: data.losers,
            },()=>{
                let tmpAry = this.addSelStk(this.state.LData,this.state.selStkIdAry)
                this.setState({
                    LData: tmpAry
                })
            })
        }
    }
    componentDidMount() {
        // this.checkUrl()
        // if(){
        //     this.getHolidayList();
        // }
    }

    scrollWindowToTop=()=>{
        window.scrollTo({
            top: 0, 
            behavior: 'smooth'
            /* you can also use 'auto' behaviour
               in place of 'smooth' */
          });
    }

    UNSAFE_componentWillMount = () => {
        this.setLocationStateData();
        window.addEventListener('scroll', this.onScrollList);
    }

    componentWillUnmount() {
        let isRStkCalled = ls.get('removeStkCalled') || false
        if(isRStkCalled){
            ls.remove('removeStkCalled')
        }
        window.removeEventListener('scroll', this.onScrollList);
    }
    /**
     * 
     * @description method to display rules scoring modal, when user join contest.
     */
    openRulesModal = () => {
        this.setState({
            showRulesModal: true,
        });
    }
    /**
     * 
     * @description method to hide rules scoring modal
     */
    hideRulesModal = () => {
        this.setState({
            showRulesModal: false,
        });
    }

    setLocationStateData() {
        if (this.props.location && this.props.location.state) {
            let data = this.props.location.state.nextStepData ? this.props.location.state.nextStepData : this.props.location.state
            const { FixturedContest, LobyyData, collection_master_id,
                from, rootDataItem, isFromMyTeams, ifFromSwitchTeamModal, isFrom, isClone, team, teamitem, isMyContest } = data;
            this.setState({
                collectionMasterId: FixturedContest ? FixturedContest.collection_id : collection_master_id,
                contestListData: FixturedContest,
                LobyyData: LobyyData ? LobyyData : this.getFixtureDetails(collection_master_id),
                FixturedContest: FixturedContest,
                isFrom: !_isUndefined(from) && from == 'editView' || from == 'MyTeams' || from == 'MyContestSwitchModal' || from == 'MyContest' ? from : !_isUndefined(from) && from == 'contestJoin' ? from : '',
                teamData: !_isUndefined(from) && from == 'editView' ? team : '',
                rootDataItem: !_isUndefined(from) && from == 'editView' ? rootDataItem : !_isUndefined(from) && from == 'contestJoin' ? rootDataItem : '',
                isFromMyTeams: !_isUndefined(isFromMyTeams) ? isFromMyTeams : false,
                ifFromSwitchTeamModal: !_isUndefined(ifFromSwitchTeamModal) ? ifFromSwitchTeamModal : false,
                TeamMyContestData: !_isUndefined(from) || !_isUndefined(isFrom) && from == 'MyContest' || isFrom == 'MyContest' ? team : !_isUndefined(isFrom) && isFrom == 'editView' ? team : '',
                isClone: !_isUndefined(isClone) ? isClone : false,
                // userRank: isMyContest ? teamitem.game_rank : LobyyData.game_rank,
                lineupMCID: isMyContest ? teamitem.lineup_master_contest_id : LobyyData.lineup_master_contest_id
            }, () => {
                // this.fetchLineupMasterData();
                this.getAllRoster();
                this.getLobbyData();
                if(!isDateTimePast(this.state.LobyyData.end_date)){
                    this.getHolidayList();
                }

                this.setState({

                    HeaderOption: {
                        back: true,
                        isPrimary: DARK_THEME_ENABLE ? false : true,
                        showRS: true,
                        title: '',
                        hideShadow: false,
                        showAlertRoster: false,
                        resetIndex: this.props.location.state.nextStepData ? this.props.location.state.nextStepData.resetIndex : this.props.location.state.resetIndex,
                        // screenDatetitle: this.state.LobyyData ,
                        // showleagueTime: true,
                        showRSAction: this.openRulesModal,
                        reminingBudget: this.state.salaryCap ,
                        brokerage: this.state.LobyyData.brokerage
               
                    }
                })
            })
        }
    }


    getLobbyData() {

        if (this.state.LobyyData) {
            if (this.state.isFrom != 'editView' || this.state.isClone) {
                this.getTeamName();
            }
            else if (this.state.isFrom == 'editView' && !this.state.isClone) {
                this.setState({ teamName: this.props.location.state.teamitem.team_name })
            }
        }
        else {
            setTimeout(() => {
                this.getLobbyData()
            }, 500);
        }
    }


    getLineupForEdit(action) {
        let lineupID = this.props.location.state.teamitem.lineup_master_id ? this.props.location.state.teamitem.lineup_master_id : this.props.location.state.lineup_master_id
        let CMsterId = this.state.collectionMasterId ? this.state.collectionMasterId : this.props.location.state.collection_master_id
        let param = {
            "lineup_master_id": lineupID,
            "collection_id": CMsterId,
        }

        getLSFUserLineup(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                let lineupResLength = responseJson.data && responseJson.data.lineup ? responseJson.data.lineup.length : 0
                this.setState({
                    userLineupApiCalled: true,
                    salaryCap: responseJson.data && responseJson.data.remaining_amount ? responseJson.data.remaining_amount : 0,
                    lineupArLength: lineupResLength || 0
                },()=>{
                    this.setState({
                        HeaderOption: {
                            back: true,
                            isPrimary: DARK_THEME_ENABLE ? false : true,
                            showRS: true,
                            title: '',
                            hideShadow: false,
                            showAlertRoster: false,
                            resetIndex: this.props.location.state.nextStepData ? this.props.location.state.nextStepData.resetIndex : this.props.location.state.resetIndex,
                            // screenDatetitle: this.state.LobyyData ,
                            // showleagueTime: true,
                            showRSAction: this.openRulesModal,
                            reminingBudget: this.state.salaryCap ,
                            brokerage: this.state.LobyyData.brokerage
                        }
                    })
                })
                // if (this.state.lineupArr.length === 0 && lineupResLength > 0) {
                    _Map(responseJson.data.lineup, (item) => {
                        this.manageAddedStock(item,action == 3 ? false : true,true)
                    })
                // }
            }
        })
    }

    getFixtureDetails = async (collectionMasterId) => {
        let param = {
            "collection_id": collectionMasterId,
        }
        var api_response_data = await getStockFixtureDetail(param);
        if (api_response_data) {
            this.setState({
                LobyyData: api_response_data
            },()=>{
                this.setState({
                    HeaderOption: {
                        back: true,
                        isPrimary: DARK_THEME_ENABLE ? false : true,
                        showRS: true,
                        title: '',
                        hideShadow: false,
                        showAlertRoster: false,
                        resetIndex: this.props.location.state.nextStepData ? this.props.location.state.nextStepData.resetIndex : this.props.location.state.resetIndex,
                        // screenDatetitle: this.state.LobyyData ,
                        // showleagueTime: true,
                        showRSAction: this.openRulesModal,
                        reminingBudget: this.state.salaryCap ,
                        brokerage: this.state.LobyyData.brokerage
                    }
                })
            });
        }
    }
    PlayerCardShow = (e, item) => {
        e.stopPropagation();
        item.collection_master_id = this.state.collectionMasterId;
        this.setState({
            playerDetails: item,
            showPlayerCard: true
        });
    }

    PlayerCardHide = () => {
        this.setState({
            showPlayerCard: false,
            playerDetails: {}
        });
    }

    // fetchLineupMasterData = async () => {
    //     let param = {
    //         "collection_id": this.state.collectionMasterId,
    //     }
    //     var api_response_data = await getSPLineupMasterData(param);
    //     if (api_response_data.response_code === WSC.successCode) {
    //         this.parseMasterData(api_response_data.data);
    //     }
    // }

    // parseMasterData(api_response_data) {
    //     let data = api_response_data;
    //     this.setState({
    //         masterData: data || '',
    //         CPts: data.c_point,
    //         VCPts: data.vc_point,
    //         teamName: data.team_name,
    //     }, () => {
    //         this.getAllRoster();
    //     })
    // }
    getAllRoster = async () => {
        let param = {
            "collection_id": this.state.collectionMasterId,
            "lineup_master_contest_id": this.state.lineupMCID
        }
        var api_response_data = await getLSFAllStock(param);
        if (api_response_data.response_code === WSC.successCode) {
            let ApiData = api_response_data.data

            let tmpFilterArray = [];
            tmpFilterArray = this.state.SearchVal != '' ? ApiData.stock_list.filter((stock) => {
                return stock.stock_name.toLowerCase().includes(this.state.SearchVal)
            }) : ApiData.stock_list
            this.setState({
                rosterList: (tmpFilterArray || []),
                allRosterList: (ApiData.stock_list || []),
                salaryCap: (ApiData.salary_cap || []),
                userRank: api_response_data.data.game_rank,
                ASApiCall: true
            }, () => {
               this.updateAllStockIdList()
                if (this.state.lineupArr.length > 0) {
                    _Map(this.state.lineupArr, (item) => {
                        this.manageAddedStock(item)
                    })
                }
            })
        }
        let isRStkCalled = ls.get('removeStkCalled') || false
        // if (this.props.location.state.from == 'editView' && !this.state.isClone && !this.state.userLineupApiCalled && !isRStkCalled) {
            this.getLineupForEdit();
        // }
    }

    updateAllStockIdList=()=>{
        let tmpAry = []
        for(var stk of this.state.allRosterList){
            tmpAry.push(stk.stock_id)
        }
        this.setState({
            allStkId:tmpAry
        })
    }

    NextSubmit = () => {
        // let urlData = _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData;
        // let selectCaptainPath = '/stock-prediction/stock-bid/' + urlData.collection_id
        // this.props.history.push({ pathname: selectCaptainPath.toLowerCase(), state: { teamName: this.state.teamName, SelectedLineup: this.state.lineupArr, MasterData: this.state.masterData, LobyyData: urlData, FixturedContest: this.state.FixturedContest, isFrom: this.state.isFrom, team: this.state.TeamMyContestData, rootDataItem: this.state.rootDataItem, isFromMyTeams: this.state.isFromMyTeams, ifFromSwitchTeamModal: this.state.ifFromSwitchTeamModal, isClone: this.state.isClone, lineup_master_contest_id: this.props.location.state.lineup_master_contest_id, teamitem: this.props.location.state.teamitem } })
        // WSManager.googleTrack(WSC.GA_PROFILE_ID, 'stock_createteam');
        this.props.history.push('/')
    }

    checkScrollStatus() {
        if (this._timeout) {
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

    getTeamName() {
        this.setState({ teamName: this.state.teamName ? this.state.teamName : this.state.masterData.team_name })
    }

    updateList=(item,list)=>{
        for (var obj of list) {
            if (obj.stock_id === item.stock_id) {
                let Msg = ''
                if(obj['is_wish'] == '1'){
                    Msg = AL.STK_REMOVE_MSG
                }
                else{
                    Msg = AL.STK_ADDED_MSG
                }
                obj['is_wish'] = obj['is_wish'] == '1' ? '0' : '1';
                Utilities.showToast(Msg, 5000);
                break;
            }
        }
        return list;
    }

    addToWatchList = (item) => {
        if(this.state.selectedTab == '2'){
            this.addStkToWatchList(item)
        }
        else{
            this.setState({
                ASApiCall: false,
                GLApiCall: false
            })
            let param = {
                "stock_id": item.stock_id,
            }
            addRemoveStockWishlist(param).then((responseJson) => {
                if (responseJson.response_code == WSC.successCode) {
                    let tmpAllList = this.state.allRosterList;
                    let glist = this.state.GData;
                    let llist = this.state.LData;
                    tmpAllList = this.updateList(item,tmpAllList)
                    glist = this.updateList(item,glist)
                    llist = this.updateList(item,llist)
                   
                    let tmpArry = tmpAllList.filter((stock) => {
                        return stock.is_selected
                    })
                    if (tmpArry.length > 0) {
                        ls.set('Lineup_data', tmpArry)
                    }
                    this.setState({
                        allRosterList: tmpAllList,
                        lineupArr: tmpArry,
                        GData: glist,
                        LData: llist,
                        WLApiCall: false,
                        ASApiCall: true,
                        GLApiCall: true
                    }, () => {
                        this.updateAllStockIdList()
                    })
                }
            })
        }
    }

    addStkToWatchList = (item) => {
        let idx = this.state.myWList.indexOf(item)
        let param = {
            "stock_id": item.stock_id,
        }
        addRemoveStockWishlist(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                let tmpAllList = this.state.myWList;
                tmpAllList.splice(idx, 1)
                let Msg = AL.STK_REMOVE_MSG
                Utilities.showToast(Msg, 5000);
                this.setState({
                    myWList: tmpAllList,
                    WLApiCall: false
                });
            }
        })
    }

    manageAddedStock = (item,isaddRemove,isEditTeam) => {
        let tmpAllList = this.state.allRosterList;
        let stkSA = []
        let gainers = this.state.GData;
        let loser = this.state.LData;
        let wishList = this.state.myWList;

        for (var obj of tmpAllList) {
            if (obj.stock_id === item.stock_id) {
                if (item.is_selected ) {
                    if(obj.is_selected){
                        obj['is_selected'] = false;
                    }
                    else{
                        obj['is_selected'] = true;
                        obj['lot_size'] = item.lot_size;
                    }
                }
                 if(isaddRemove){
                    obj['is_selected'] = true;
                    obj['lot_size'] = item.lot_size;
                }
                if (isEditTeam || item.user_price) {
                    obj['user_price'] = item.user_price || 0;
                }
                if(item.status){
                    obj['status'] = item.status
                    obj['total_trade_value'] = item.total_trade_value
                }
                break;
            }
        }
        let tmpArry = tmpAllList.filter((stock) => {
            return stock.is_selected == true
        })
        for (var stk of tmpArry) {
            if (stk.is_selected) {
                stkSA.push(stk.stock_id);
            }
        }       
        ls.set('Lineup_data', tmpArry)
        this.setState({
            allRosterList: tmpAllList,
            lineupArr: tmpArry,
            selStkIdAry: stkSA
        }, () => {
            let gstk = gainers.length > 0 ? this.addSelStk(gainers,this.state.selStkIdAry) : []
            let lstk = loser.length > 0 ? this.addSelStk(loser,this.state.selStkIdAry) : []
            let wlist = wishList.length > 0 ? this.addSelStk(wishList,this.state.selStkIdAry) : []
            this.setState({
                GData: gstk,
                LData: lstk,
                myWList: wlist
            })
            this.updateAllStockIdList()
            if (this.headerRef && this.headerRef.current) {
                this.headerRef.current.GetHeaderProps("lineup", this.state.lineupArr, this.state.masterData, _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData, this.state.FixturedContest, this.state.isFrom, this.state.rootDataItem, this.state.teamData ? this.state.teamData : this.state.teamName);
            }
        })
    }

    addStock = (item) => {
        if(item.is_selected){
            this.showStockActionModal(item)
        }
        else{
            this.showBuyStockModal(item,true)
        }
    }

    showItemFromSelLineup=(id)=>{
        let data = this.state.lineupArr.filter(item => item.stock_id == id)
        return data[0]
    }

    showStockActionModal= (item) => {
        this.setState({
            selectedItem: this.showItemFromSelLineup(item.stock_id),
            // selectedAction: action,
            // pDiff: pDiff,
            // isUpdate: isUpdate,
            showStkActModal: true,
        });
    }

    hideStockActionModal=()=>{
        this.setState({
            showStkActModal:false
        })
    }

    showExitStockModal= (action,item) => {
        this.setState({
            selectedItem: item,
            showExitStock: true,
            action: action
        });
    }

    hideExitStockModal=()=>{
        this.setState({
            showExitStock:false
        })
    }

    // ########## function to handle action on trade stock ########### action 1 for buy, 2 for eit partial, 3 for exit all
    handleStkAction=(item,action)=>{
        this.hideStockActionModal()
        if(action == 2 || action == 3){
            this.showExitStockModal(action,item)
        }
        else{
            this.showBuyStockModal(item,true)
        }
    }

    showBuyStockModal= (item, pDiff) => {
        this.setState({
            selectedItem: item,
            // selectedAction: action,
            pDiff: pDiff,
            // isUpdate: isUpdate,
            showBuyStkModal: true,
        });
    }

    hideBuyStockModal=()=>{
        this.setState({
            showBuyStkModal:false
        })
    }

    addSelStk=(MainAry,stkSA)=>{
        for(var stk of MainAry){
            if(stkSA.length > 0){
                for(var obj of stkSA){
                    if(obj == stk.stock_id ){
                        stk['is_selected'] = true;
                        break;
                    }
                    else if(stk.is_selected){
                        stk['is_selected'] = false;
                    }
                }
            }
            else if(stk.is_selected){
                stk['is_selected'] = false;
            }
        }
        return MainAry;
    }

    SearchHandler = (e) => {
        const name = e.target.id;
        const value = e.target.value.toLowerCase();
        this.setState({ [name]: value },()=>{
            let { allRosterList ,AllmyWList,AllGData,AllLData} = this.state; 
             let tmpFilterArray = [];
             tmpFilterArray = allRosterList.filter((stock) => {
                return stock.stock_name.toLowerCase().includes(value)
            });
            let tmpWLFilter = [];
            tmpWLFilter = AllmyWList.filter((stock) => {
                return stock.stock_name.toLowerCase().includes(value)
            });
            let tmpGDataFilter = [];
            tmpGDataFilter = AllGData.filter((stock) => {
                return stock.name.toLowerCase().includes(value)
            });
            let tmpLDataFilter = [];
            tmpLDataFilter = AllLData.filter((stock) => {
                return stock.name.toLowerCase().includes(value)
            });
            this.setState({ 
                rosterList: tmpFilterArray,
                myWList: tmpWLFilter,
                GData: tmpGDataFilter,
                LData: tmpLDataFilter,
             })
        });
    }

    addDataFromBytSell = (item, action, pDiff, remaingBudget, stockPrize, shareValue, brkVal) => {
        this.setState({ 
            salaryCap: remaingBudget, 
            stockPrize: stockPrize 
        })
        if(action == 1){
            this.callBuySellApi(item, action, stockPrize, shareValue, brkVal)
        }
        // this.buySellAction(action, item, shareValue, isUpdate)

    }

    // ########### function to call api for exit stock ############
    submitExitAction=(item, action,stockPrize, stockCount,remaingBudget)=>{
        this.setState({ 
            salaryCap: remaingBudget
        })
        this.callBuySellApi(item, action,stockPrize, stockCount, 0)
    }

    // ########### funtion to call trade api  #############
    callBuySellApi=(item, action, stockPrize, shareValue, brkVal)=>{
        
        let idx = this.state.myWList.indexOf(item)
        let StkID = item.stock_id
        let tmpArry = {}
        tmpArry[StkID] = shareValue
        if(action == 3){
            let updateArry = this.state.lineupArr.filter((ldata) => {
                return ldata.stock_id != item.stock_id
            })
            ls.set('Lineup_data', updateArry)

            let tmpAllList = this.state.allRosterList
            for (var obj of tmpAllList) {
                if (obj.stock_id === item.stock_id) {
                    if (item.is_selected ) {
                        if(obj.is_selected){
                            obj['is_selected'] = false;
                        }
                    }
                    break;
                }
            }
            this.setState({
                allRosterList: tmpAllList
            })
        }
        let lineupID = this.state.LobyyData.lineup_master_id ? this.state.LobyyData.lineup_master_id : this.props.location.state.teamitem.lineup_master_id ? 
        this.props.location.state.teamitem.lineup_master_id : 
        this.props.location.state.lineup_master_id
        let param = {
            "lineup_master_id": lineupID,
            "contest_id": this.state.LobyyData.contest_id,
            "trade_value": stockPrize ,
            "price": item.current_price,
            "lot_size": shareValue,
            "brokerage": brkVal,
            "type": action ,//buy=1,exit_partial=2,exit_all=3
            "stock_id": StkID,
            "stocks": tmpArry
        }
        LSF_USER_TRADE(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.hideExitStockModal()
                this.getLineupForEdit(action)
                // this.manageAddedStock()
            }
            else{
                Utilities.showToast(responseJson.global_error != "" ? responseJson.global_error : responseJson.message, 2000);
            }
        })
        this.hideBuyStockModal()
    }

    // ########### funtion to render list view #############
    renderList=(item,disabled)=>{
        return(
            <tr className={`${disabled ? 'disabled' : ''}`}>
                <td className="stk-det-sec" onClick={(e)=>this.PlayerCardShow(e, item)}>
                    <div className="stk-img"> <img src={item.logo ? Utilities.getStockLogo(item.logo) : Images.BRAND_LOGO_FULL_PNG} alt="" /></div>
                    <div className="stk-nm">
                        <span>{item.display_name || item.stock_name || item.name}</span>
                        {/* <i className={`icon-wishlist ${item.is_wish == "1" ? ' active' : ''}`} onClick={(e) => { e.stopPropagation(); this.addToWatchList(item) }}></i> */}
                    </div>
                    <div className="stk-abt">
                        {Utilities.getMasterData().currency_code}
                        {Utilities.numberWithCommas(parseFloat(item.current_price).toFixed(2))} 
                        <span className={item.price_diff < 0 ? " danger" : ""} > 
                            {item.price_diff > 0 ? ' +' : ' '}{Utilities.numberWithCommas(parseFloat(item.price_diff || 0).toFixed(2))}({Math.abs(item.percent_change)}%) 
                            <i className={item.price_diff < 0 ? "icon-stock_down" : "icon-stock_up"} />
                        </span>
                    </div>
                </td>
                <td>
                    {/* <a href className={`${item.is_selected ? 'stk-added' : 'add-stk'}`} onClick={()=>this.addStock(item,true)}><i className={`${item.is_selected ? "icon-tick" : "icon-plus-ic"}`}></i></a> */}
                    <a href className={`${item.is_selected ? 'stk-added' : 'add-stk'} ${!isDateTimePast(this.state.LobyyData.end_date) && ' disabled'}`} onClick={()=>this.addStock(item,true)}>
                      {
                        item.is_selected ? <>{AL.MANAGE}</> : <>{AL.TRADE}</>
                      }
                    </a>
                </td>
            </tr>
        )
    }

    // ########### funtion to redirect on transaction screen #############
    viewTransanction=()=>{
        const {LobyyData,salaryCap} = this.state;
        let dateformaturl = Utilities.getUtcToLocal(LobyyData.scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
        let transactionPath = ''
        transactionPath = '/live-stock-fantasy/' + LobyyData.contest_id + '/' + LobyyData.lineup_master_id + '-' + dateformaturl +'/transaction'
        this.props.history.push({
            pathname: transactionPath.toLowerCase(), state: {
                // FixturedContest: ContestItem,
                LobbyData: LobyyData,
                salaryCap: salaryCap,
                LMID: this.props.location.state.teamitem.lineup_master_id ? this.props.location.state.teamitem.lineup_master_id : this.props.location.state.lineup_master_id
            }
        })
    }

    // let lineupID = this.props.location.state.teamitem.lineup_master_id ? this.props.location.state.teamitem.lineup_master_id : this.props.location.state.lineup_master_id
    // let CMsterId = this.state.collectionMasterId ? this.state.collectionMasterId : this.props.location.state.collection_master_id

    // ########### funtion to set contet winning and name #############
    getPrizeAmount = (prize_data) => {
        let prizeAmount = this.getWinCalculation(prize_data.prize_distibution_detail);
        return (
        <React.Fragment>
            {
            prizeAmount.is_tie_breaker == 0 && prizeAmount.real > 0 ?
                <span>
                {Utilities.getMasterData().currency_code}
                {Utilities.getPrizeInWordFormat(prizeAmount.real)}
                </span>
                : prizeAmount.is_tie_breaker == 0 && prizeAmount.bonus > 0 ? <span> <i className="icon-bonus" />{Utilities.numberWithCommas(parseFloat(prizeAmount.bonus).toFixed(0))}</span>
                : prizeAmount.is_tie_breaker == 0 && prizeAmount.point > 0 ? <span> <img className="img-coin" alt='' src={Images.IC_COIN} />{Utilities.numberWithCommas(parseFloat(prizeAmount.point).toFixed(0))}</span>
                    : AL.PRIZES
            }
        </React.Fragment>
        )
    }
    getWinCalculation = (prize_data) => {
        let prizeAmount = { 'real': 0, 'bonus': 0, 'point': 0, 'is_tie_breaker': 0 };
        prize_data && prize_data.map(function (lObj, lKey) {
        var amount = 0;
        if (lObj.max_value) {
            amount = parseFloat(lObj.max_value);
        } else {
            amount = parseFloat(lObj.amount);
        }
        if (lObj.prize_type == 3) {
            prizeAmount['is_tie_breaker'] = 1;
        }
        if (lObj.prize_type == 0) {
            prizeAmount['bonus'] = parseFloat(prizeAmount['bonus']) + amount;
        } else if (lObj.prize_type == 2) {
            prizeAmount['point'] = parseFloat(prizeAmount['point']) + amount;
        } else {
            prizeAmount['real'] = parseFloat(prizeAmount['real']) + amount;
        }
        })
        return prizeAmount;
    }

    getHolidayList=()=>{
        getLSFHOLIDAYLIST().then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({
                    holidayList: responseJson.data
                },()=>{
                    this.checkIsMarketOpen()
                })
            }
        })
    }

    // ########### function to check is today market close or not ############
    checkIsMarketOpen=()=>{
        var TodayDate = new Date()
        var TodayDay = TodayDate.getDay();
        var TodayTime = TodayDate.getTime();

        var MarketOpenTime = '09:15:00'
        var MOT = new Date((TodayDate.getMonth() + 1) + "/" + TodayDate.getDate() + "/" + TodayDate.getFullYear() + " " + MarketOpenTime);
        var CloseOpenTime = '15:30:00'
        var COT = new Date((TodayDate.getMonth() + 1) + "/" + TodayDate.getDate() + "/" + TodayDate.getFullYear() + " " + CloseOpenTime);
        if(this.isHoliday(TodayDate) || TodayDay == 0 || TodayDay == 6){
            this.setState({
                isMarketOpen: false
            })
        }
        if(TodayTime == MOT.getTime() || TodayTime == COT.getTime() || TodayTime < MOT.getTime() || TodayTime > COT.getTime()){
            this.setState({
                isMarketOpen: false
            })
        }
    }

    isHoliday = (data) => {
        var userDate = new Date((data.getMonth() + 1) + "/" + data.getDate() + "/" + data.getFullYear() );
        if (this.state.holidayList.includes(moment(userDate).format('YYYY-MM-DD'))) {
            return true    
        } 
        return false
    }

    // ######### function to show team preview #########
    showTeamPreview=()=>{
        this.setState({
            showTeamPreview: true
        })
    }

    // ######### function to hide team preview #########
    hideTeamPreview=()=>{
        this.setState({
            showTeamPreview: false
        })
    }

    render() {
        var {
            LobyyData,
            showPlayerCard,
            playerDetails,
            rosterList,
            lineupArr,
            showRulesModal,
            SearchVal,
            GData,
            LData,
            isLLoading,
            ShimmerList,
            myWList,
            selStkIdAry,
            HeaderOption,
            showStkActModal,
            showBuyStkModal,
            showExitStock,
            action,
            showTeamPreview,
            isMarketOpen,
            lineupArLength,
            userRank
        } = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"web-container stock-roster white-bg lsf-roster"}>
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.lineup.title}</title>
                            <meta name="description" content={MetaData.lineup.description} />
                            <meta name="keywords" content={MetaData.lineup.keywords}></meta>
                        </Helmet>
                        <CustomHeader ref={this.headerRef} {...this.props} HeaderOption={HeaderOption} />
                        <div className="cndl-desc-sec">
                          <div className="cndl-desc-inn">
                            <div className="bg-clr-sec"></div>
                            <div className="roster-cr-info-sec">
                                <div className="cdl-nm">  
                                    <span className="win-amt">{AL.WIN} {this.getPrizeAmount(LobyyData)}</span>
                                    <span className="candel-nm">{LobyyData.contest_title ? " - " + LobyyData.contest_title : ""}</span>
                                    <span className="rank"> <i className="icon-sheild"></i> {AL.RANK} #{userRank || '--'}</span>
                                </div>
                                <div className="cdl-dt-info">
                                    <MomentDateComponent data={{ date: LobyyData.scheduled_date, format: "D MMM hh:mm A " }} /> - 
                                    <MomentDateComponent data={{ date: LobyyData.end_date, format: "D MMM hh:mm A " }} />
                                </div>
                                <div className="info-tab-sec">
                                    <div className="info-sec">
                                    <i className="icon-check"></i>
                                    <div className="tbl"><span className="val">{lineupArLength ? lineupArLength : '--'}</span> {AL.SCRIPS}</div>
                                    </div>
                                    <div className="info-sec">
                                        <i className="icon-transaction-circle"></i>
                                        {/* <div className="val">{Utilities.getMasterData().currency_code}{Utilities.numberWithCommas(parseFloat(Utilities.getExactValue(this.state.salaryCap)))}</div> */}
                                        <div className="view-trans" >
                                            <span className='cursor-pointer' onClick={()=>this.viewTransanction()}>{AL.VIEW_TRANSACTIONS}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                          </div>
                        </div>
                        <div className="sp-roster-body">
                            <Tab.Container id="left-tabs-example" defaultActiveKey="1">
                                <Row className="clearfix">
                                    <Col sm={12} className="navtab-wrap">
                                        <Nav bsStyle="pills" stacked>
                                            <NavItem onClick={() => this.onTabClick('1')} eventKey="1">{AL.ALL}</NavItem>
                                            <NavItem onClick={() => this.onTabClick('2')} eventKey="2">{AL.WATCHLISTED}</NavItem>
                                            <NavItem onClick={() => this.onTabClick('3')} eventKey="3">{AL.TOP_GAINERS}</NavItem>
                                            <NavItem onClick={() => this.onTabClick('4')} eventKey="4">{AL.TOP_LOSERS}</NavItem>
                                        </Nav>
                                    </Col>
                                    <div className="search-sec">
                                        <i className="icon-search-bg"></i>
                                        <FormGroup
                                            className={`input-label-center input-transparent`}
                                            controlId="formBasicText">

                                            <input
                                                autoComplete='off'
                                                styles={inputStyle}
                                                id='SearchVal'
                                                name='SearchVal'
                                                value={SearchVal} 
                                                placeholder={AL.SEARCH_SCRIPS}
                                                type='text'
                                                onChange={this.SearchHandler}
                                            />
                                        </FormGroup>
                                    </div>
                                    <Col sm={12}>
                                        <Tab.Content animation>
                                            <Tab.Pane eventKey="1">
                                                <Table className="ros-tb-header">
                                                    <thead>
                                                        <tr>
                                                            <th onClick={() => {this.setState({ sort_field: 'comp', sort_order: (this.state.sort_order == 'DESC' ? 'ASC' : 'DESC') }); this.setState({ rosterList: this.state.rosterList.sort((a, b) => (this.state.sort_order == 'DESC' ? a.stock_name.localeCompare(b.stock_name) :  b.stock_name.localeCompare(a.stock_name))) })}} >
                                                                {AL.SCRIPS} {this.state.sort_field == 'comp' && <i className={this.state.sort_order == 'DESC' ? "icon-arrow-down" : 'icon-arrow-up'}></i>} 
                                                            </th>
                                                            <th>{AL.ADD_STOCK}</th>
                                                        </tr>
                                                    </thead>
                                                </Table>
                                                <InfiniteScroll
                                                    dataLength={rosterList.length}
                                                    >
                                                    <Table>
                                                        <tbody>
                                                        {
                                                            _Map(rosterList, (item, idx) => {
                                                                let disabled = false;// (lineupArr.length < maxStock || item.is_selected) ? false : true
                                                                return (
                                                                    <>
                                                                    {this.renderList(item,disabled)}
                                                                    </>
                                                                )
                                                            })
                                                        }
                                                        </tbody>
                                                    </Table>
                                                </InfiniteScroll>
                                                {
                                                    rosterList && rosterList.length == 0 && !isLLoading &&
                                                    <NoDataView 
                                                        BG_IMAGE={Images.no_data_bg_image}
                                                        // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                        CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                        MESSAGE_1={AL.NO_WISHLIST_STOCK}
                                                        MESSAGE_2={''}
                                                    />
                                                }
                                                {
                                                    rosterList && rosterList.length == 0 && isLLoading && this.state.ShimmerList.map((item, index) => {
                                                        return (
                                                            <Shimmer key={index} />
                                                        )
                                                    })
                                                }
                                            </Tab.Pane>
                                            <Tab.Pane eventKey="2">
                                                <Table className="ros-tb-header">
                                                    <thead>
                                                        <tr>
                                                            <th onClick={() => {this.setState({ sort_field: 'comp', sort_order: (this.state.sort_order == 'DESC' ? 'ASC' : 'DESC') }); this.setState({ rosterList: this.state.rosterList.sort((a, b) => (this.state.sort_order == 'DESC' ? a.stock_name.localeCompare(b.stock_name) :  b.stock_name.localeCompare(a.stock_name))) })}} >{AL.COMPANY_NAME} {this.state.sort_field == 'comp' && <i className={this.state.sort_order == 'DESC' ? "icon-arrow-down" : 'icon-arrow-up'}></i>} </th>
                                                            <th>{AL.ADD_STOCK}</th>
                                                        </tr>
                                                    </thead>
                                                </Table>
                                                {
                                                    myWList && myWList.length > 0 && !isLLoading &&
                                                    <InfiniteScroll
                                                    dataLength={myWList.length}
                                                    >
                                                        <Table>
                                                            <tbody>
                                                            {
                                                                _Map(myWList, (item, idx) => {
                                                                    let disabled = false;//(lineupArr.length < maxStock || item.is_selected) ? false : true
                                                                    return (
                                                                        <>
                                                                        {this.renderList(item,disabled)}
                                                                        </>
                                                                    )
                                                                })
                                                            }
                                                            </tbody>
                                                        </Table>
                                                    </InfiniteScroll>
                                                }
                                                {
                                                    myWList && myWList.length == 0 && !isLLoading &&
                                                    <NoDataView 
                                                        BG_IMAGE={Images.no_data_bg_image}
                                                        // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                        CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                        MESSAGE_1={AL.NO_WISHLIST_STOCK}
                                                        MESSAGE_2={''}
                                                    />
                                                }
                                                {
                                                    myWList && myWList.length == 0 && isLLoading && this.state.ShimmerList.map((item, index) => {
                                                        return (
                                                            <Shimmer key={index} />
                                                        )
                                                    })
                                                }
                                            
                                            </Tab.Pane>
                                            <Tab.Pane eventKey="3">
                                                <Table className="ros-tb-header">
                                                    <thead>
                                                        <tr>
                                                            <th onClick={() => {this.setState({ sort_field: 'comp', sort_order: (this.state.sort_order == 'DESC' ? 'ASC' : 'DESC') }); this.setState({ rosterList: this.state.rosterList.sort((a, b) => (this.state.sort_order == 'DESC' ? a.stock_name.localeCompare(b.stock_name) :  b.stock_name.localeCompare(a.stock_name))) })}} >{AL.COMPANY_NAME} {this.state.sort_field == 'comp' && <i className={this.state.sort_order == 'DESC' ? "icon-arrow-down" : 'icon-arrow-up'}></i>} </th>
                                                            <th>{AL.ADD_STOCK}</th>
                                                        </tr>
                                                    </thead>
                                                </Table>
                                                {
                                                    GData && GData.length > 0 && !isLLoading &&
                                                    <InfiniteScroll
                                                    dataLength={GData.length}
                                                    >
                                                        <Table>
                                                            <tbody>
                                                            {
                                                                _Map(GData, (item, idx) => {
                                                                    let disabled = false;//(lineupArr.length < maxStock || item.is_selected) ? false : true
                                                                    return (
                                                                        <>
                                                                        {this.renderList(item,disabled)}
                                                                        </>
                                                                    )
                                                                })
                                                            }
                                                            </tbody>
                                                        </Table>
                                                    </InfiniteScroll>
                                                }
                                                {
                                                    GData && GData.length == 0 && !isLLoading &&
                                                    <NoDataView 
                                                        BG_IMAGE={Images.no_data_bg_image}
                                                        // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                        CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                        MESSAGE_1={AL.INFO_NOT_AVAILABLE}
                                                        MESSAGE_2={''}
                                                    />
                                                }
                                                {
                                                    GData && GData.length == 0 && isLLoading && this.state.ShimmerList.map((item, index) => {
                                                        return (
                                                            <Shimmer key={index} />
                                                        )
                                                    })
                                                }
                                            </Tab.Pane>
                                            <Tab.Pane eventKey="4">
                                                <Table className="ros-tb-header">
                                                    <thead>
                                                        <tr>
                                                            <th onClick={() => {this.setState({ sort_field: 'comp', sort_order: (this.state.sort_order == 'DESC' ? 'ASC' : 'DESC') }); this.setState({ rosterList: this.state.rosterList.sort((a, b) => (this.state.sort_order == 'DESC' ? a.stock_name.localeCompare(b.stock_name) :  b.stock_name.localeCompare(a.stock_name))) })}} >{AL.COMPANY_NAME} {this.state.sort_field == 'comp' && <i className={this.state.sort_order == 'DESC' ? "icon-arrow-down" : 'icon-arrow-up'}></i>} </th>
                                                            <th>{AL.ADD_STOCK}</th>
                                                        </tr>
                                                    </thead>
                                                </Table>
                                                {
                                                    LData && LData.length > 0 && !isLLoading &&
                                                    <InfiniteScroll
                                                    dataLength={LData.length}
                                                    >
                                                        <Table>
                                                            <tbody>
                                                            {
                                                                _Map(LData, (item, idx) => {
                                                                    let disabled = false;//(lineupArr.length < maxStock || item.is_selected) ? false : true
                                                                    return (
                                                                        <>
                                                                        {this.renderList(item,disabled)}
                                                                        </>
                                                                    )
                                                                })
                                                            }
                                                            </tbody>
                                                        </Table>
                                                    </InfiniteScroll>
                                                }
                                                {
                                                    LData && LData.length == 0 && !isLLoading &&
                                                    <NoDataView 
                                                        BG_IMAGE={Images.no_data_bg_image}
                                                        // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                        CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                        MESSAGE_1={AL.INFO_NOT_AVAILABLE}
                                                        MESSAGE_2={''}
                                                    />
                                                }
                                                {
                                                    LData && LData.length == 0 && isLLoading && this.state.ShimmerList.map((item, index) => {
                                                        return (
                                                            <Shimmer key={index} />
                                                        )
                                                    })
                                                }
                                            </Tab.Pane>
                                        </Tab.Content>
                                        <div className={"roster-footer " + this.state.showBtmBtn}>
                                            <div className="btn-wrap">
                                                <button disabled={!(lineupArr.length > 0)} onClick={() => this.showTeamPreview()} className="btn btn-primary btm-fix-btn stk-preview">{AL.PREVIEW}</button>
                                                <button disabled={!(lineupArr.length > 0)} onClick={() => this.NextSubmit()} className="btn btn-primary btm-fix-btn">{AL.CONFIRM}</button>
                                            </div>
                                        </div>
                                        {/* <div className="btm-btn-sec">
                                            <a href className={`btn btn-primary btn-rounded `} onClick={() => this.NextSubmit()}>{AL.NEXT}</a>
                                            <a href className={`btn btn-primary btn-rounded ${(lineupArr.length < minStock) ? ' disabled' : ''}`} onClick={() => this.NextSubmit()}>{AL.NEXT}</a>
                                        </div> */}
                                    </Col>
                                </Row>
                            </Tab.Container>
                        </div>

                        {
                            showPlayerCard &&
                            <Suspense fallback={<div />} >
                                <StockPlayerCard
                                    mShow={showPlayerCard}
                                    mHide={this.PlayerCardHide}
                                    playerData={playerDetails}
                                    buySellAction={this.buySellAction}
                                    addToWatchList={this.addToWatchList} 
                                />
                            </Suspense>

                        }
                        {showRulesModal &&
                            <LSFRules mShow={showRulesModal} mHide={this.hideRulesModal} stockSetting={this.state.stockSetting} showPtsOnly={true} />
                        } 
                        {
                            showStkActModal &&
                            <Suspense fallback={<div />} >
                                <LSFStockActionModal mShow={showStkActModal} mHide={this.hideStockActionModal} item={this.state.selectedItem} handleStkAction={this.handleStkAction} />
                            </Suspense>
                        }
                        {
                            showBuyStkModal &&
                            <Suspense fallback={<div />} >
                                <LSFStockBuyModal mShow={showBuyStkModal} mHide={this.hideBuyStockModal} item={this.state.selectedItem} pDiff={this.state.pDiff} salaryCap={this.state.salaryCap} brokerage={LobyyData.brokerage} addDataFromBytSell={this.addDataFromBytSell} isMarketOpen={isMarketOpen} />
                            </Suspense>
                        }
                        {
                            showExitStock &&
                            <Suspense fallback={<div />} >
                                <LSFExitStockModal mShow={showExitStock} mHide={this.hideExitStockModal} item={this.state.selectedItem} pDiff={this.state.pDiff} salaryCap={this.state.salaryCap} brokerage={LobyyData.brokerage} addDataFromBytSell={this.addDataFromBytSell} action={action} submitExitAction={this.submitExitAction} isMarketOpen={isMarketOpen} />
                            </Suspense>
                        }
                        {
                            showTeamPreview &&
                            <Suspense fallback={<div />} >
                                <LSFTeamPreview 
                                salary_cap={this.state.salaryCap} isFrom={'roster'} preTeam={lineupArr} 
                                CollectionData={LobyyData} isViewAllShown={showTeamPreview}
                                 onViewAllHide={this.hideTeamPreview} isTeamPrv={'true'} />
                            </Suspense>
                        }
                    </div>

                )}
            </MyContext.Consumer>
        )
    }
}