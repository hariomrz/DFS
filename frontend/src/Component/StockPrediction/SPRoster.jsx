import React, { Suspense, lazy } from 'react';
import {  Row, Col, Tab, Nav, NavItem ,FormGroup,Table } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import { getStockFixtureDetail, getSPLineupMasterData, getSPAllStock, getSPUserLineup, addRemoveStockWishlist,getStockWishlist ,getSPCollectionStatics} from "../../WSHelper/WSCallings";
import { Utilities, _isUndefined, _isEmpty, _Map } from '../../Utilities/Utilities';
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
const StockPlayerCard = lazy(() => import('../StockFantasy/StockPlayerCard'));
const SPRules = lazy(() => import('./SPFantasyRules'));
const CMSPRoster = lazy(() => import('./CMSPRoster'));

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
export default class SPRoster extends React.Component {
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
            maxStock: 0,
            minStock: 0,
            SearchVal: '',
            GData: [],
            LData: [],
            myWList: [],
            isLLoading: false,
            ShimmerList: [1, 2, 3, 4, 5, 1, 2, 3, 4, 5, 1, 2, 3, 4, 5],
            selStkIdAry: [],
            allStkId: [],
            HeaderOption: {
                back: true,
                isPrimary: DARK_THEME_ENABLE ? false : true,
                showRS: true,
                title: '',
                hideShadow: false,
                showAlertRoster: true,
                resetIndex: '',
                screenDatetitle: '' ,
                showleagueTime: true,
                showRSAction: this.openRulesModal
            },
            userLineupApiCalled: false,
            showSPCM: true,
            rosterSPCM: ls.get('stkP-roster') ? ls.get('stkP-roster') : 0
        };
        this._timeout = null;
        this.checkScrollStatus = this.checkScrollStatus.bind(this);
        this.headerRef = React.createRef();
    }

    onTabClick=(selectedTab)=>{
        
        // this.checkUrl(selectedTab)
        this.setState({ selectedTab: selectedTab },()=>{
            this.setTabData(selectedTab)
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
            this.callGetStockWishlist()
        }
        else if(selectedTab == '3'){
            this.callStaticsApi(1)
        }
        else if(selectedTab == '4'){
            this.callStaticsApi(2)
        }
        else{
            this.fetchLineupMasterData();
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
                let data = responseJson.data || [];
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
                this.setState({
                    myWList: tmpAry //data
                },()=>{
                    let tmpAry = this.addSelStk(this.state.myWList,this.state.selStkIdAry)
                    this.setState({
                        myWList: tmpAry
                    })
                })
            }
        })
    }

    callStaticsApi=(isFor)=>{ //isFor == 1 for gainer, 2 for loser
        this.setState({
            isLLoading: true
        })
        let param = {
            collection_id: this.state.LobyyData.collection_id
            // "day_filter": 3, //this.state.filterBy,
            // "type": isFor //this.state.viewMoreType
        }
        getSPCollectionStatics(param).then((responseJson) => { //getStockStatictics
            if (responseJson.response_code == WSC.successCode) {
                this.setState({
                    isLLoading: false
                })
                if(isFor == 1){
                    this.setState({
                        GData: responseJson.data.gainers 
                    },()=>{
                        let tmpAry = this.addSelStk(this.state.GData,this.state.selStkIdAry)
                        this.setState({
                            GData: tmpAry
                        })
                    })
                }
                else{
                    this.setState({
                        LData: responseJson.data.losers 
                    },()=>{
                        let tmpAry = this.addSelStk(this.state.LData,this.state.selStkIdAry)
                        this.setState({
                            LData: tmpAry
                        })
                    })
                }
            }
        })
    }
    componentDidMount() {
        // this.checkUrl()
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
                from, rootDataItem, isFromMyTeams, ifFromSwitchTeamModal, isFrom, isClone, team } = data;

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
                isClone: !_isUndefined(isClone) ? isClone : false
            }, () => {
                this.fetchLineupMasterData();
                this.getLobbyData();
                this.setState({

                    HeaderOption: {
                        back: true,
                        isPrimary: DARK_THEME_ENABLE ? false : true,
                        showRS: true,
                        title: '',
                        hideShadow: false,
                        showAlertRoster: true,
                        resetIndex: this.props.location.state.nextStepData ? this.props.location.state.nextStepData.resetIndex : this.props.location.state.resetIndex,
                        screenDatetitle: this.state.LobyyData ,
                        showleagueTime: true,
                        showRSAction: this.openRulesModal
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


    getLineupForEdit() {
        let lineupID = this.props.location.state.teamitem.lineup_master_id ? this.props.location.state.teamitem.lineup_master_id : this.props.location.state.lineup_master_id
        let param = {
            "lineup_master_id": lineupID,
            "collection_id": this.props.location.state.collection_master_id,
        }

        getSPUserLineup(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({
                    userLineupApiCalled: true
                })
                if (this.state.lineupArr.length === 0) {
                    _Map(responseJson.data.lineup, (item) => {
                        this.addStock(item,true,true)
                    })
                }
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
                        showAlertRoster: true,
                        resetIndex: this.props.location.state.nextStepData ? this.props.location.state.nextStepData.resetIndex : this.props.location.state.resetIndex,
                        screenDatetitle: this.state.LobyyData ,
                        showleagueTime: true,
                        showRSAction: this.openRulesModal
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

    fetchLineupMasterData = async () => {
        let param = {
            "collection_id": this.state.collectionMasterId,
        }
        var api_response_data = await getSPLineupMasterData(param);
        if (api_response_data.response_code === WSC.successCode) {
            this.parseMasterData(api_response_data.data);
        }
    }

    parseMasterData(api_response_data) {
        let data = api_response_data;
        this.setState({
            masterData: data || '',
            CPts: data.c_point,
            VCPts: data.vc_point,
            maxStock: data.max_stock,
            minStock: data.min_stock,
            teamName: data.team_name,
        }, () => {
            this.getAllRoster();
        })
    }
    getAllRoster = async () => {

        let param = {
            "collection_id": this.state.collectionMasterId
        }
        var api_response_data = await getSPAllStock(param);
        if (api_response_data.response_code === WSC.successCode) {
            this.setState({
                rosterList: (api_response_data.data || []),
                allRosterList: (api_response_data.data || []),
            }, () => {
               this.updateAllStockIdList()
                if (this.state.lineupArr.length > 0) {
                    _Map(this.state.lineupArr, (item) => {
                        this.addStock(item)
                    })
                }
            })
        }
        let isRStkCalled = ls.get('removeStkCalled') || false
        if (this.props.location.state.from == 'editView' && !this.state.isClone && !this.state.userLineupApiCalled && !isRStkCalled) {
            this.getLineupForEdit();
        }
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
        let urlData = _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData;
        let selectCaptainPath = '/stock-prediction/stock-bid/' + urlData.collection_id
        this.props.history.push({ pathname: selectCaptainPath.toLowerCase(), state: { teamName: this.state.teamName, SelectedLineup: this.state.lineupArr, MasterData: this.state.masterData, LobyyData: urlData, FixturedContest: this.state.FixturedContest, isFrom: this.state.isFrom, team: this.state.TeamMyContestData, rootDataItem: this.state.rootDataItem, isFromMyTeams: this.state.isFromMyTeams, ifFromSwitchTeamModal: this.state.ifFromSwitchTeamModal, isClone: this.state.isClone, lineup_master_contest_id: this.props.location.state.lineup_master_contest_id, teamitem: this.props.location.state.teamitem } })
        WSManager.googleTrack(WSC.GA_PROFILE_ID, 'stock_createteam');
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
                        LData: llist
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
                    myWList: tmpAllList
                });
            }
        })
    }

    addStock = (item,isaddRemove,isEditTeam) => {
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
                    }
                }
                else if(isaddRemove){
                    obj['is_selected'] = true;
                }
                if (isEditTeam || item.user_price) {
                    obj['user_price'] = item.user_price || 0;
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
            let { allRosterList } = this.state;
             let tmpFilterArray = [];
             tmpFilterArray = allRosterList.filter((stock) => {
                return stock.stock_name.toLowerCase().includes(value)
            });
            this.setState({ rosterList: tmpFilterArray })
        });
    }

    renderList=(item,disabled)=>{
        return(
            <tr className={`${disabled ? 'disabled' : ''}`}>
                <td className="stk-det-sec" onClick={(e)=>this.PlayerCardShow(e, item)}>
                    <img src={item.logo ? Utilities.getStockLogo(item.logo) : Images.BRAND_LOGO_FULL_PNG} alt="" />
                    <div className="stk-nm">
                        <span>{item.display_name || item.stock_name || item.name}</span>
                        <i className={`icon-wishlist ${item.is_wish == "1" ? ' active' : ''}`} onClick={(e) => { e.stopPropagation(); this.addToWatchList(item) }}></i>
                    </div>
                    <div className="stk-abt">
                        {Utilities.numberWithCommas(parseFloat(item.current_price).toFixed(2))} <span className={item.price_diff < 0 ? " danger" : ""} > {Utilities.numberWithCommas(parseFloat(item.price_diff || 0).toFixed(2))}({Math.abs(item.percent_change)}%) 
                        <i className={item.price_diff < 0 ? "icon-stock_down" : "icon-stock_up"} />
                    </span>
                    </div>
                </td>
                <td>
                    <a href className={`${item.is_selected ? 'stk-added' : 'add-stk'}`} onClick={()=>this.addStock(item,true)}><i className={`${item.is_selected ? "icon-tick" : "icon-plus-ic"}`}></i></a>
                </td>
            </tr>
        )
    }

    // function to show coachmarks
    showSPCM = () => {
        this.setState({ showSPCM: true })
    }
    // function to hide coachmarks
    hideSPCM = () => {
        this.setState({ showSPCM: false });
    }

    render() {
        var {
            LobyyData,
            showPlayerCard,
            playerDetails,
            maxStock,
            rosterList,
            lineupArr,
            showRulesModal,
            minStock,
            SearchVal,
            GData,
            LData,
            isLLoading,
            ShimmerList,
            myWList,
            selStkIdAry,
            HeaderOption,
            maxStock
        } = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"web-container stock-roster white-bg  sp-roster"}>
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.lineup.title}</title>
                            <meta name="description" content={MetaData.lineup.description} />
                            <meta name="keywords" content={MetaData.lineup.keywords}></meta>
                        </Helmet>
                        <CustomHeader ref={this.headerRef} {...this.props} HeaderOption={HeaderOption} />

                        <div className="sp-roster-body">
                            <Tab.Container id="left-tabs-example" defaultActiveKey="1">
                                <Row className="clearfix">
                                    <Col sm={12} className="navtab-wrap">
                                        <Nav bsStyle="pills" stacked>
                                            <NavItem onClick={() => this.onTabClick('1')} eventKey="1">{AL.ALL}</NavItem>
                                            <NavItem onClick={() => this.onTabClick('2')} eventKey="2">{AL.FAV}</NavItem>
                                            <NavItem onClick={() => this.onTabClick('3')} eventKey="3">{AL.TOP_GAINERS}</NavItem>
                                            <NavItem onClick={() => this.onTabClick('4')} eventKey="4">{AL.TOP_LOSERS}</NavItem>
                                        </Nav>
                                    </Col>
                                    <div className="pick-btw-sec">
                                        <div className="pbs-inn">{AL.YOU_CAN_PICK_MIN} 1 {AL.SCRIPS_SM}</div>
                                        {/* <div className="pbs-inn">{AL.YOU_CAN_PICK_BETWEEN} {minStock}-{maxStock} {AL.SCRIPS_SM}</div> */}
                                    </div>
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
                                                            <th onClick={() => {this.setState({ sort_field: 'comp', sort_order: (this.state.sort_order == 'DESC' ? 'ASC' : 'DESC') }); this.setState({ rosterList: this.state.rosterList.sort((a, b) => (this.state.sort_order == 'DESC' ? a.stock_name.localeCompare(b.stock_name) :  b.stock_name.localeCompare(a.stock_name))) })}} >{AL.COMPANY_NAME} {this.state.sort_field == 'comp' && <i className={this.state.sort_order == 'DESC' ? "icon-arrow-down" : 'icon-arrow-up'}></i>} </th>
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
                                                                let disabled = (lineupArr.length < maxStock || item.is_selected) ? false : true
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
                                                                    let disabled = (lineupArr.length < maxStock || item.is_selected) ? false : true
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
                                                                    let disabled = (lineupArr.length < maxStock || item.is_selected) ? false : true
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
                                                                    let disabled = (lineupArr.length < maxStock || item.is_selected) ? false : true
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
                                        <div className="btm-btn-sec">
                                            <a href className={`btn btn-primary btn-rounded ${(lineupArr.length < minStock) ? ' disabled' : ''}`} onClick={() => this.NextSubmit()}>{AL.NEXT}</a>
                                        </div>
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
                            <SPRules mShow={showRulesModal} mHide={this.hideRulesModal} stockSetting={this.state.stockSetting} showPtsOnly={true} />
                        } 
                        {
                            this.state.showSPCM && this.state.rosterSPCM == 0 &&  
                            <CMSPRoster {...this.props} cmData={{
                                mHide: this.hideSPCM,
                                mShow: this.showSPCM
                            }} />
                        }
                    </div>

                )}
            </MyContext.Consumer>
        )
    }
}

