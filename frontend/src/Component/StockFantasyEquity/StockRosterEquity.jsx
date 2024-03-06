import React, { Suspense, lazy } from 'react';
import {  Row, Col, Tab, Nav, NavItem ,FormGroup, Table } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import { getStockFixtureDetail, getStockLineupMasterData, getStockRoster, getStockLineupTeamName, getStockUserLineupEquity, addRemoveStockWishlist, getStockLobbySetting,getStockContestStaticsEquity } from "../../WSHelper/WSCallings";
import { Utilities, _isUndefined, _isEmpty, _Map } from '../../Utilities/Utilities';
import { Helmet } from "react-helmet";
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import Skeleton,{SkeletonTheme} from 'react-loading-skeleton';
import ls from 'local-storage';
import WSManager from "../../WSHelper/WSManager";
import InfiniteScroll from 'react-infinite-scroll-component';
import MetaData from "../../helper/MetaData";
import CustomHeader from '../../components/CustomHeader';
import FilterByTeam from '../../components/filterByteam';
import { DARK_THEME_ENABLE, setValue, StockSetting, GameType } from '../../helper/Constants';
import { CircularProgressBar } from '../CustomComponent';
import StockEquityFRules from './StockEquityFRules';
import StockItem from '../StockFantasy/StockItem';
import StockTeamPreview from '../StockFantasy/StockTeamPreview';
// import BuySellStockModal from './BuySellStockModal';
import BuySellStockAmountModal from './BuySellStockAmountModal';
import Images from '../../components/images';
import FloatingLabel from 'floating-label-react';
import { inputStyleLeft, inputStyle } from '../../helper/input-style';
import CMStkRosterEqModal from "./CMStkRosterEq";
import { NoDataView } from '../CustomComponent';
import StockRosterFilterEq from './StockRosterFilterEq';

const StockPlayerCard = lazy(() => import('../StockFantasy/StockPlayerCard'));

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
export default class StockRoster extends React.Component {
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
            maxPlayers: '',
            lineupArr: ls.get('Lineup_data') ? ls.get('Lineup_data') : [],
            teamList: [],
            rosterList: [],
            allRosterList: [],
            isTableLoaderShow: false,
            selectedIndustry: [],
            contestListData: '',
            LobyyData: '',
            FixturedContest: '',
            maxPlayerBuy: 0,
            maxPlayerSell: 0,
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
            selSellC: 0,
            selBuyC: 0,
            StockSettingValue: [],
            showRulesModal: false,
            showBuySellModal: false,
            stockPrize: 0,
            SearchVal: '',
            showCM: true,
            RosterCoachMarkStatus: ls.get('stkeq-roster') ? ls.get('stkeq-roster') : 0,
            GData: [],
            LData: [],
            AllGData: [],
            AllLData: [],
            selSTKList: ls.get('Lineup_data') ? ls.get('Lineup_data') : [],
            AllSelSTKList: ls.get('Lineup_data') ? ls.get('Lineup_data') : [],
            isLLoading: false,
            ShimmerList: [1, 2, 3, 4, 5, 1, 2, 3, 4, 5, 1, 2, 3, 4, 5],
            filterArry: [],
            allGLData: '',
            GLApiCalled: false
        };
        this._timeout = null;
        this.checkScrollStatus = this.checkScrollStatus.bind(this);
        this.headerRef = React.createRef();
    }

    SearchHandler = (e) => {
        const name = e.target.id;
        const value = e.target.value.toLowerCase();
        this.setState({ [name]: value },()=>{
            let { allRosterList,AllGData,AllLData,AllSelSTKList } = this.state;
            let tmpFilterArray = [];
            let tmpGFilter = [];
            let tmpLFilter = [];
            let tmpSSFilter = [];
            tmpFilterArray = allRosterList.filter((stock) => {
                return stock.stock_name.toLowerCase().includes(value)
            });
            tmpGFilter = AllGData.filter((stock) => {
                return stock.stock_name.toLowerCase().includes(value)
            });
            tmpLFilter = AllLData.filter((stock) => {
                return stock.stock_name.toLowerCase().includes(value)
            });
            tmpSSFilter = AllSelSTKList.filter((stock) => {
                return stock.stock_name.toLowerCase().includes(value)
            });
            this.setState({ 
                rosterList: tmpFilterArray,
                GData: tmpGFilter,
                LData: tmpLFilter,
                selSTKList: tmpSSFilter,
            })
        });
    }

    componentDidMount=()=>{        
        if (this.headerRef && this.headerRef.current) {
            this.headerRef.current.GetRosterEqHeaderProps(this.state.lineupArr);
        }
        this.setSelectedStockList(this.state.lineupArr)
    }

    UNSAFE_componentWillMount = () => {
        WSManager.setPickedGameType(GameType.StockFantasyEquity)
        this.setLocationStateData();
        window.addEventListener('scroll', this.onScrollList);

        // if (StockSetting.length > 0) {
        //     this.setState({
        //         StockSettingValue: StockSetting
        //     })
        // }
        // else {
        //     let param = {
        //         "stock_type": 2,
        //     }
        //     getStockLobbySetting(param).then((responseJson) => {
        //         setValue.setStockSettings(responseJson.data);
        //         this.setState({ StockSettingValue: responseJson.data })
        //     })
        // }
    }
    componentWillUnmount() {
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
    /**
    * 
    * @description method to display rules BuySellModal modal.
    */
    openBuySellModal = (action, item, pDiff, isUpdate) => {
        this.setState({
            selectedItem: item,
            selectedAction: action,
            pDiff: pDiff,
            isUpdate: isUpdate,
            showBuySellModal: true,

        });
    }
    /**
     * 
     * @description method to hide rules BuySellModal modal
     */
    hideBuySellModal = () => {
        this.setState({
            showBuySellModal: false,
        });
    }
    addDataFromBytSell = (item, action, pDiff, remaingBudget, stockPrize, shareValue, isUpdate) => {
        this.setState({ salary_cap: remaingBudget, stockPrize: stockPrize })
        this.buySellAction(action, item, shareValue, isUpdate)
        this.hideBuySellModal()

    }
    removeDataFromList = (item, stockPrize) => {
        // let SC= (parseFloat(this.state.salary_cap) + parseFloat(stockPrize));
        // let exact = parseFloat(SC) > 500000 ? 500000.0 : SC
        this.setState({ salary_cap: stockPrize })
        let lineupArr = this.state.lineupArr;

        if (this.checkPlayerExistInLineup(item)) {

            var index = 0;
            for (var selectedPlayer of this.state.lineupArr) {
                if (selectedPlayer.stock_id == item.stock_id) {
                    lineupArr.splice(index, 1);

                }
                index++
            }

        }
        this.setSelectedStockList(lineupArr)
        ls.set('Lineup_data', lineupArr)
        this.hideBuySellModal()
        this.buySellAction('', item, 0, false)


    }
    checkPlayerExistInLineup(player) {
        var isExist = false;
        for (var selectedPlayer of this.state.lineupArr) {
            if (selectedPlayer.stock_id == player.stock_id) {
                isExist = true
                break
            }
        }
        return isExist

    }

    setLocationStateData() {
        if (this.props.location && this.props.location.state) {
            let data = this.props.location.state.nextStepData ? this.props.location.state.nextStepData : this.props.location.state
            const { FixturedContest, LobyyData, collection_master_id,
                from, rootDataItem, isFromMyTeams, ifFromSwitchTeamModal, isFrom, isClone, team } = data;

            this.setState({
                collectionMasterId: FixturedContest ? FixturedContest.collection_master_id : collection_master_id,
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
                // this.fetchLineupMasterData();
                this.setSelectedStockList()
                // this.callStaticsApi()
            })
        }
    }


    getLobbyData() {

        if (this.state.LobyyData) {
            // if (this.state.isFrom != 'editView' || this.state.isClone) {
            //     this.getTeamName();
            // }
            // else 
            if (this.state.isFrom == 'editView' && !this.state.isClone) {
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
        getStockUserLineupEquity(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                if (this.state.lineupArr.length === 0) {
                    let totalAmount = 0.0
                    //alert("hgfghfghf")

                    let user_lot_size;
                    _Map(responseJson.data.lineup, (item) => {
                        user_lot_size = parseFloat(item.user_lot_size) * parseFloat(item.current_price)
                        let c = parseFloat(Utilities.getExactValue(totalAmount));
                        let d = parseFloat(Utilities.getExactValue(user_lot_size));
                        totalAmount = Utilities.addNumber(c, d)
                        this.buySellAction(item.action, item, item.user_lot_size, true)

                    })

                    let a = parseFloat(Utilities.getExactValue(this.state.salary_cap));
                    let b = parseFloat(Utilities.getExactValue(totalAmount));
                    let updatedSalaryCap = Utilities.subNumber(a, b);
                    this.setState({ salary_cap: parseFloat(Utilities.getExactValue(updatedSalaryCap)) })
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

    handleChange = (selectedOption) => {
        this.setState({ showRosterFilter: false, selectedIndustry: selectedOption }, () => {
            if(selectedOption && selectedOption.industryID){
                this.applyTeamFilter(selectedOption.industryID)
            }
            else{
                this.setState({ 
                    selectedIndustry: [],
                    rosterList: this.state.allRosterList,
                    GData: this.state.AllGData,
                    LData: this.state.AllLData,
                    selSTKList: this.state.AllSelSTKList
                })
            }
        });
    }

    applyTeamFilter(id) {
        let { allRosterList,AllGData,AllLData,AllSelSTKList } = this.state;
        let tmpFilterArray = [];
        let tmpGainer = [];
        let tmpLoser = [];
        let tmpStkSel = [];
        if (id != '') {
            tmpFilterArray = allRosterList.filter((stock) => {
                return id == stock.industry_id
            });
            tmpGainer = AllGData.filter((stock) => {
                return id == stock.industry_id
            });
            tmpLoser = AllLData.filter((stock) => {
                return id == stock.industry_id
            });
            tmpStkSel = AllSelSTKList.filter((stock) => {
                return id == stock.industry_id
            });
            this.setState({ 
                rosterList: tmpFilterArray,
                GData: tmpGainer,
                LData: tmpLoser,
                selSTKList: tmpStkSel
            })
        }
    }

    fetchLineupMasterData = async () => {
        let param = {
            "collection_id": this.state.collectionMasterId,
            "stock_type": 2
        }
        var api_response_data = await getStockLineupMasterData(param);
        if (api_response_data.response_code === WSC.successCode) {
            this.parseMasterData(api_response_data.data);
        }
    }

    parseMasterData(api_response_data) {
        let data = api_response_data.length > 0 ? api_response_data[0] : ''
        let settingData = {
            "stock_limit": data.stock_limit,
            "config_data": data.config_data,
            "c_point": data.c_point,
            "vc_point": data.vc_point
        }
        this.setState({
            masterData: data || '',
            maxPlayers: data.config_data ? data.config_data.max || 0 : 0,
            maxPlayerBuy: data.config_data ? data.config_data.b || 0 : 0,
            maxPlayerSell: data.config_data ? data.config_data.s || 0 : 0,
            salary_cap: data ? parseFloat(Utilities.getExactValue(data.salary_cap)) || 500000 : 500000,
            max_cap_per_stock: data ? data.max_cap_per_stock || 0 : 0,
            min_cap_per_stock: data ? data.min_cap_per_stock || 0 : 0,
            StockSettingValue: settingData
        }, () => {
            this.getAllRoster();
        })
    }
    getAllRoster = async () => {

        let param = {
            "collection_id": this.state.collectionMasterId
        }
        var api_response_data = await getStockRoster(param);
        if (api_response_data.response_code === WSC.successCode) {
            this.setState({
                rosterList: (api_response_data.data || []),
                allRosterList: (api_response_data.data || []),
            }, () => {
                this.setFilterData(api_response_data.data)
                if (this.state.lineupArr.length > 0) {
                    let totalAmount = 0.0
                    _Map(this.state.lineupArr, (item) => {
                        //totalAmount = parseFloat(totalAmount) + parseFloat(item.stockPrize)
                        let c = parseFloat(Utilities.getExactValue(totalAmount));
                        let d = parseFloat(Utilities.getExactValue(item.stockPrize));
                        totalAmount = Utilities.addNumber(c, d)
                        this.buySellAction(item.action, item, item.shareValue ? item.shareValue : item.user_lot_size, item.isUpdate)
                    })
                    if (!this.state.isClone) {
                        //let updatedSalaryCap = (parseFloat(Utilities.getExactValue(this.state.salary_cap)) - parseFloat(Utilities.getExactValue(totalAmount)))
                        let a = parseFloat(Utilities.getExactValue(this.state.salary_cap));
                        let b = parseFloat(Utilities.getExactValue(totalAmount));
                        let updatedSalaryCap = Utilities.subNumber(a, b);
                        this.setState({ salary_cap: updatedSalaryCap })
                    }

                }
            })
        }
        if (this.props.location.state.from == 'editView' && !this.state.isClone) {
            this.getLineupForEdit();
        }
        if (this.state.isClone) {
            let totalAmount = 0.0
            let user_lot_size;
            _Map(ls.get('Lineup_data'), (item) => {
                user_lot_size = parseFloat(item.shareValue) * parseFloat(item.current_price)
                let c = parseFloat(Utilities.getExactValue(totalAmount));
                let d = parseFloat(Utilities.getExactValue(user_lot_size));
                totalAmount = Utilities.addNumber(c, d)
                this.buySellAction(item.action, item, item.shareValue, true)


            })
            let a = parseFloat(Utilities.getExactValue(this.state.salary_cap));
            let b = parseFloat(Utilities.getExactValue(totalAmount));
            let updatedSalaryCap = Utilities.subNumber(a, b);
            this.setState({ salary_cap: parseFloat(Utilities.getExactValue(updatedSalaryCap)) })
        }

    }

    setFilterData=(data)=>{
        let filterArry = this.state.filterArry
        for(let i = 0;i < data.length ;i++){
            let item = data[i]
            if(filterArry.length > 0){
                if(item.industry_id && !this.valueExists((item.industry_id),filterArry)){
                    let obj = {
                        'industryID' : item.industry_id,
                        'industryName' : item.industry_name
                    }
                    // { value: { team_league_id: 0 }, label: AL.ALL_STOCK },
                    filterArry.push(obj)
                }
            }
            else if(item.industry_id){
                let obj = {
                   'industryID' : item.industry_id,
                   'industryName' : item.industry_name
                }
                filterArry.push(obj)
            }
        }
        this.setState({
            filterArry: filterArry
        })
    }

    valueExists(value,filterArry) {
        return filterArry.some(function(el) {
          return el.industryID == value;
        }); 
    }

    NextSubmit = () => {
        let urlData = _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData;
        let name = urlData.category_id.toString() === "1" ? 'Daily' : urlData.category_id.toString() === "2" ? 'Weekly' : 'Monthly';
        let selectCaptainPath = '/stock-fantasy-equity/select-captain/' + name
        this.props.history.push({ pathname: selectCaptainPath.toLowerCase(), state: { teamName: this.state.teamName, SelectedLineup: this.state.lineupArr, MasterData: this.state.masterData, LobyyData: urlData, FixturedContest: this.state.FixturedContest, isFrom: this.state.isFrom, team: this.state.TeamMyContestData, rootDataItem: this.state.rootDataItem, isFromMyTeams: this.state.isFromMyTeams, ifFromSwitchTeamModal: this.state.ifFromSwitchTeamModal, isClone: this.state.isClone, lineup_master_contest_id: this.props.location.state.lineup_master_contest_id, teamitem: this.props.location.state.teamitem, salary_cap: this.state.salary_cap ,StockSettingValue: this.state.StockSettingValue} })
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
        let param = {
            "collection_id": this.state.LobyyData.collection_master_id ? this.state.LobyyData.collection_master_id : this.state.FixturedContest.collection_master_id,
        }
        getStockLineupTeamName(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.setState({ teamName: responseJson.data.team_name })
            }
        })
    }

    showRosterFilter = () => {
        this.setState({
            showRosterFilter: true
        })
    }

    GoToFieldView = () => {
        this.setState({
            isViewAll: true
        })
    }

    onViewAllHide = () => {
        this.setState({
            isViewAll: false
        })
    }

    addToWatchList = (item) => {

        let param = {
            "stock_id": item.stock_id,
        }
        addRemoveStockWishlist(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                let tmpAllList = this.state.allRosterList;
                for (var obj of tmpAllList) {
                    if (obj.stock_id === item.stock_id) {
                        obj['is_wish'] = obj['is_wish'] == '1' ? '0' : '1';
                        break;
                    }
                }
                let tmpArry = tmpAllList.filter((stock) => {
                    return stock.is_selected
                })
                if (tmpArry.length > 0) {
                    ls.set('Lineup_data', tmpArry)
                }
                this.setState({
                    allRosterList: tmpAllList,
                    lineupArr: tmpArry
                }, () => {                  
                    if(this.state.selectedIndustry && this.state.selectedIndustry.industryID){
                        this.applyTeamFilter(this.state.selectedIndustry.industryID)
                    }
                })
            }
        })
    }
    openBuySellPopup = (action, item, pDiff, isUpdate) => {
        if (item.action != action && action == 1 && !(this.state.selBuyC < this.state.maxPlayerBuy)) {
            let msg = AL.STOCK_MAX_BUY.replace('##', this.state.maxPlayerBuy)
            Utilities.showToast(msg, 3000);
        } else if (item.action != action && action == 2 && !(this.state.selSellC < this.state.maxPlayerSell)) {
            let msg = AL.STOCK_MAX_SELL.replace('##', this.state.maxPlayerSell)
            Utilities.showToast(msg, 3000);
        }
        else{
            this.openBuySellModal(action, item, pDiff, isUpdate)
        }
    }
    buySellAction = (action, item, shareValue, isUpdate) => {
        if (item.action != action && action == 1 && !(this.state.selBuyC < this.state.maxPlayerBuy)) {
            let msg = AL.STOCK_MAX_BUY.replace('##', this.state.maxPlayerBuy)
            Utilities.showToast(msg, 3000);
        } else if (item.action != action && action == 2 && !(this.state.selSellC < this.state.maxPlayerSell)) {
            let msg = AL.STOCK_MAX_SELL.replace('##', this.state.maxPlayerSell)
            Utilities.showToast(msg, 3000);
        } else {
            let tmpAllList = this.state.allRosterList;
            let gainers = this.state.AllGData;
            let loser = this.state.AllLData;
            for (var obj of tmpAllList) {
                if (obj.stock_id === item.stock_id) {
                    //let oldAct = obj['action'];
                    obj['action'] = action;
                    obj['shareValue'] = shareValue ? shareValue : 0
                    obj['isUpdate'] = isUpdate
                    obj['stockPrize'] = shareValue ? parseFloat(shareValue) * parseFloat(item.current_price) : 0
                    obj['is_selected'] = action == 1 || action == 2 ? true : false;
                    if (item.player_role) {
                        obj['player_role'] = item.player_role;
                    }
                    break;
                }

            }
            let selBuyC = 0;
            let selSellC = 0;
            let tmpArry = tmpAllList.filter((stock) => {
                if (stock.action == 1) {
                    selBuyC = selBuyC + 1
                }
                if (stock.action == 2) {
                    selSellC = selSellC + 1
                }
                return stock.is_selected
            })
            ls.set('Lineup_data', tmpArry)

            this.setSelectedStockList(tmpArry)
            this.setState({
                // allRosterList: tmpAllList,
                rosterList: tmpAllList,
                lineupArr: tmpArry,
                selSellC,
                selBuyC
            }, () => {
                let gstk = gainers.length > 0 ? this.addSelStk(gainers,this.state.lineupArr) : []
                let lstk = loser.length > 0 ? this.addSelStk(loser,this.state.lineupArr) : []

                this.setState({
                    GData: gstk,
                    LData: lstk,
                    AllGData: gstk,
                    AllLData: lstk,
                    // selSTKList: selStk,
                    // AllSelSTKList: selStk
                })
                if(this.state.selectedIndustry && this.state.selectedIndustry.industryID){
                    this.applyTeamFilter(this.state.selectedIndustry.industryID)
                }
                if (this.headerRef && this.headerRef.current) {
                    this.headerRef.current.GetHeaderProps("lineup", this.state.lineupArr, this.state.masterData, _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData, this.state.FixturedContest, this.state.isFrom, this.state.rootDataItem, this.state.teamData ? this.state.teamData : this.state.teamName);
                }
            })
        }
    }
    showSlider = (maxPlayers, fillTab, lineupArr) => {
        let i = 0;
        let tempArry = [];
        let divStyle = { width: `calc(100%/${maxPlayers})` };
        for (i; i < maxPlayers; i++) {
            tempArry.push(
                <div key={i}
                    className={
                        (i < lineupArr.length ? lineupArr[i].action == 1 ? "active" : 'active-sell' : '') +
                        (fillTab == (i + 1) ? " show-number" : '')
                    }
                    style={divStyle}
                >
                    <span>{i + 1}</span>
                </div>
            )
        }
        return tempArry;
    }

    // function to show coachmarks
    showCM = () => {
        this.setState({ showCM: true })
    }
    // function to hide coachmarks
    hideCM = () => {
        this.setState({ showCM: false });
    }

    onTabClick=(selectedTab)=>{        
        // this.checkUrl(selectedTab)
        this.setState({ selectedTab: selectedTab },()=>{
            this.setTabData(selectedTab)
        });
    }

    setTabData=(selectedTab)=>{
        if((selectedTab == '3' || selectedTab == '4') && !this.state.GLApiCalled){
            this.callStaticsApi(2)
        }
        // else{
            // this.fetchLineupMasterData();
            // this.setSelectedStockList()
            // this.callStaticsApi(1)
            // this.callStaticsApi(2)
        // }
    }

    setSelectedStockList=(list)=>{
        let LAlist = list || this.state.lineupArr
       let selSTKList = LAlist.length > 1 ? LAlist.sort((a, b) => (this.state.sort_order == 'DESC' ? a.stock_name.localeCompare(b.stock_name) : b.stock_name.localeCompare(a.stock_name))) : LAlist
        
        // return selSTKList
       this.setState({
        selSTKList: selSTKList,
        AllSelSTKList: selSTKList
       })
    }
    
    callStaticsApi=()=>{ 
        this.setState({
            isLLoading: true
        })
        let param = {
            collection_id: this.state.LobyyData.collection_id
        }
        getStockContestStaticsEquity(param).then((responseJson) => { //getStockStatictics
            if (responseJson.response_code == WSC.successCode) {
                this.setState({
                    isLLoading: false
                })
                this.setState({
                    allGLData: responseJson.data,
                    GLApiCalled: true
                })
                this.setData(1,responseJson.data)//isFor == 1 for gainer, 2 for loser
                this.setData(2,responseJson.data)//isFor == 1 for gainer, 2 for loser
            }
        })
    }

    setData=(isFor,data)=>{
        if(isFor == 1){
            this.setState({
                GData: data.gainers ,
                AllGData: data.gainers 
            },()=>{
                let tmpAry = this.addSelStk(this.state.AllGData,this.state.lineupArr)
                this.setState({
                    GData: tmpAry,
                    AllGData: tmpAry
                })
            })
        }
        else{
            this.setState({
                LData: data.losers,
                AllLData: data.losers
            },()=>{
                let tmpAry = this.addSelStk(this.state.AllLData,this.state.lineupArr)
                this.setState({
                    LData: tmpAry,
                    AllLData: tmpAry,
                })
            })
        }
    }

    addSelStk=(MainAry,stkSA)=>{
        for(var stk of MainAry){
            if(stkSA.length > 0){
                for(var obj of stkSA){
                    if(obj.stock_id == stk.stock_id ){
                        stk['is_selected'] = true;
                        stk['action'] = obj.action;
                        stk['stockPrize'] = obj.stockPrize;
                        stk['shareValue'] = obj.shareValue;
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

    render() {
        var {
            LobyyData,
            showPlayerCard,
            playerDetails,
            maxPlayers,
            rosterList,
            lineupArr,
            isViewAll,
            StockSettingValue,
            showRulesModal,
            showBuySellModal,
            stockPrize,
            SearchVal,
            selSTKList,
            ShimmerList,
            GData,
            LData,
            isLLoading,
            filterArry
        } = this.state;
        const HeaderOption =
        {
            back: true,
            isPrimary: DARK_THEME_ENABLE ? false : true,
            filter: false,
            title: '',
            hideShadow: false,
            showAlertRoster: true,
            resetIndex: this.props.location.state.nextStepData ? this.props.location.state.nextStepData.resetIndex : this.props.location.state.resetIndex,
            showRosterFilter: this.showRosterFilter,
            showFilterByTeam: true,
            screentitle: LobyyData ? (LobyyData.collection_name && LobyyData.collection_name != '' ? LobyyData.collection_name : LobyyData.category_id.toString() === "1" ? AL.DAILY : LobyyData.category_id.toString() === "2" ? AL.WEEKLY : AL.MONTHLY) + ' ': '',
            minileague: true,
            leagueDate: {
                scheduled_date: LobyyData.scheduled_date || '',
                end_date: LobyyData.end_date || '', //LobyyData ? (LobyyData.category_id.toString() === "1" ? '' : LobyyData.end_date) : '',
                game_starts_in: LobyyData.game_starts_in || '',
                catID: LobyyData.category_id || ''
            },
            showleagueTime: true
        }
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"web-container stock-roster white-bg steq-stock-roster "}>
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.lineup.title}</title>
                            <meta name="description" content={MetaData.lineup.description} />
                            <meta name="keywords" content={MetaData.lineup.keywords}></meta>
                        </Helmet>
                        <CustomHeader ref={this.headerRef} {...this.props} HeaderOption={HeaderOption} />
                        <Tab.Container id="left-tabs-example" defaultActiveKey="1">
                            <Row className="clearfix">
                                <Col sm={12} className="navtab-wrap">
                                    <div className="stock-roster-header">
                                        <div className="count-header equity-padding">
                                            <div>

                                                <div className="sel-stk text-capitalize">
                                                    {AL.PICK} {" " + maxPlayers + " "} {AL.STOCKS}    {/* {AL.PICK_STOCK_EQUITY} */}
                                                </div>

                                                <div className='stock-equity'>
                                                    <div className='sel-stk-equity'>
                                                        <div className="stk-left-equity">{lineupArr.length}/{maxPlayers} </div>

                                                        <div className="scoring-rule">

                                                            <i className="icon-file"></i>
                                                            <div onClick={() => this.openRulesModal()} className="scoring-rules-txt">{AL.SCORING_RULES}</div>
                                                        </div>

                                                    </div>

                                                </div>


                                            </div>


                                            <div className='remaining-budget-layout'>
                                                <div className="remaining-budget">{AL.REMAINING_BUDGET}</div>
                                                <div className="remaining-salary-count">{Utilities.getMasterData().currency_code}{Utilities.numberWithCommas(parseFloat(Utilities.getExactValue(this.state.salary_cap)))}</div>

                                            </div>

                                        </div>
                                        <div className="player-count-slider">
                                            {this.showSlider(maxPlayers, lineupArr.length, lineupArr)}
                                        </div>
                                        <Nav bsStyle="pills" stacked>
                                            <NavItem onClick={() => this.onTabClick('1')} eventKey="1">{AL.ALL}</NavItem>
                                            <NavItem onClick={() => this.onTabClick('2')} eventKey="2">{AL.SELECTED}</NavItem>
                                            <NavItem onClick={() => this.onTabClick('3')} eventKey="3">{AL.TOP_GAINERS}</NavItem>
                                            <NavItem onClick={() => this.onTabClick('4')} eventKey="4">{AL.TOP_LOSERS}</NavItem>
                                        </Nav>
                                        <div className="search-stock-container">
                                            <img  alt='' src={Images.search_dark} className='search-icon'></img>
                                            <FormGroup
                                                style={{ marginLeft: 6, width: '100%', marginTop: 18 }}
                                                className={`input-label-center input-transparent`}
                                                controlId="formBasicText">

                                                <input

                                                    autoComplete='off'
                                                    styles={inputStyle}
                                                    id='SearchVal'
                                                    name='SearchVal'
                                                    value={SearchVal}
                                                    placeholder={AL.SEARCH_STOCK}
                                                    type='text'
                                                    onChange={this.SearchHandler}
                                                />
                                            </FormGroup>
                                        </div>
                                        <div className="item-header watchlist">
                                            <span onClick={() => {
                                                this.setState({ sort_field: 'comp', sort_order: (this.state.sort_order == 'DESC' ? 'ASC' : 'DESC') });
                                                this.setState({ rosterList: this.state.rosterList.sort((a, b) => (this.state.sort_order == 'DESC' ? a.stock_name.localeCompare(b.stock_name) : b.stock_name.localeCompare(a.stock_name))) })
                                            }}>{AL.SCRIP_NAME}{this.state.sort_field == 'comp' && <i className={this.state.sort_order == 'DESC' ? "icon-arrow-down" : 'icon-arrow-up'}></i>}</span>
                                            <span onClick={() => {
                                                this.setState({ sort_field: 'per', sort_order: (this.state.sort_order == 'DESC' ? 'ASC' : 'DESC') });
                                                this.setState({ rosterList: this.state.rosterList.sort((a, b) => (this.state.sort_order == 'DESC' ? a.percent_change - b.percent_change : b.percent_change - a.percent_change)) })
                                            }}>  {this.state.sort_field == 'per' && <i className={this.state.sort_order == 'DESC' ? "icon-arrow-down" : 'icon-arrow-up'}></i>}</span>

                                            {/* <div onClick={() => {
                                                this.setState({ sort_field: 'fantasy_score', sort_order: (this.state.sort_order == 'DESC' ? 'ASC' : 'DESC') });
                                                this.setState({ rosterList: this.state.rosterList.sort((a, b) => (this.state.sort_order == 'DESC' ? a.fantasy_score - b.fantasy_score : b.fantasy_score - a.fantasy_score)) })
                                            }}>{AppLabels.POINTS}  {this.state.sort_field == 'fantasy_score' && <i className={this.state.sort_order == 'DESC' ? "icon-arrow-down" : 'icon-arrow-up'}></i>}</div> */}
                                            <span>{}</span>
                                        </div>
                                    </div>
                                </Col>
                                <Col sm={12}>
                                    <Tab.Content animation>
                                        <Tab.Pane eventKey="1">
                                            <div id="tableLineupPlayer" >
                                                    <InfiniteScroll 
                                                        dataLength={rosterList.length}
                                                    >
                                                        {
                                                            _Map(rosterList, (item, idx) => {
                                                                let disabled = (lineupArr.length < maxPlayers || item.is_selected) ? false : true
                                                                return (
                                                                    <StockItem
                                                                        key={item.stock_id + idx}
                                                                        item={item}
                                                                        isFrom="roster"
                                                                        down={item.price_diff < 0}
                                                                        openBuySellPopup={this.openBuySellPopup}
                                                                        disabled={disabled}
                                                                        openPlayerCard={this.PlayerCardShow}
                                                                        StockSettingValue={StockSettingValue}
                                                                    />
                                                                )
                                                            })
                                                        }
                                                    </InfiniteScroll>
                                                </div>
                                        </Tab.Pane>
                                        <Tab.Pane eventKey="2">
                                            {
                                                selSTKList && selSTKList.length > 0 ?
                                                <>
                                                {
                                                    _Map(selSTKList,(item,idx)=>{
                                                        let disabled = (lineupArr.length < maxPlayers || item.is_selected) ? false : true
                                                        return (
                                                            <StockItem
                                                                key={item.stock_id + idx}
                                                                item={item}
                                                                isFrom="roster"
                                                                down={item.price_diff < 0}
                                                                openBuySellPopup={this.openBuySellPopup}
                                                                disabled={disabled}
                                                                openPlayerCard={this.PlayerCardShow}
                                                                StockSettingValue={StockSettingValue}
                                                            />
                                                        )
                                                    })
                                                }
                                                </>
                                                :
                                                <NoDataView 
                                                    BG_IMAGE={Images.no_data_bg_image}
                                                    // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                    CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                    MESSAGE_1={AL.STK_NO_STK_SEL}
                                                    MESSAGE_2={''}
                                                />
                                            }
                                        </Tab.Pane>
                                        <Tab.Pane eventKey="3">
                                            {
                                                GData && GData.length > 0 && !isLLoading &&
                                                <InfiniteScroll
                                                dataLength={GData.length}
                                                >
                                                    {
                                                        _Map(GData, (item, idx) => {
                                                        let disabled = (lineupArr.length < maxPlayers || item.is_selected) ? false : true
                                                            return (
                                                                <StockItem
                                                                    key={item.stock_id + idx}
                                                                    item={item}
                                                                    isFrom="roster"
                                                                    down={item.price_diff < 0}
                                                                    openBuySellPopup={this.openBuySellPopup}
                                                                    disabled={disabled}
                                                                    openPlayerCard={this.PlayerCardShow}
                                                                    StockSettingValue={StockSettingValue}
                                                                />
                                                            )
                                                        })
                                                    }
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
                                                GData && GData.length == 0 && isLLoading && ShimmerList.map((item, index) => {
                                                    return (
                                                        <Shimmer key={index} />
                                                    )
                                                })
                                            }
                                        </Tab.Pane>
                                        <Tab.Pane eventKey="4">
                                            {
                                                LData && LData.length > 0 && !isLLoading &&
                                                <InfiniteScroll
                                                dataLength={LData.length}
                                                >
                                                    {
                                                        _Map(LData, (item, idx) => {
                                                        let disabled = (lineupArr.length < maxPlayers || item.is_selected) ? false : true
                                                            return (
                                                                <StockItem
                                                                    key={item.stock_id + idx}
                                                                    item={item}
                                                                    isFrom="roster"
                                                                    down={item.price_diff < 0}
                                                                    openBuySellPopup={this.openBuySellPopup}
                                                                    disabled={disabled}
                                                                    openPlayerCard={this.PlayerCardShow}
                                                                    StockSettingValue={StockSettingValue}
                                                                />
                                                            )
                                                        })
                                                    }
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
                                                LData && LData.length == 0 && isLLoading && ShimmerList.map((item, index) => {
                                                    return (
                                                        <Shimmer key={index} />
                                                    )
                                                })
                                            }
                                        </Tab.Pane>
                                    </Tab.Content>
                                    <div className={"roster-footer " + this.state.showBtmBtn}>
                                        <div className="btn-wrap">
                                            <button disabled={!(lineupArr.length > 0)} onClick={() => this.GoToFieldView()} className="btn btn-primary btm-fix-btn stk-preview">{AL.STOCK_PREVIEW}</button>
                                            <button disabled={!(lineupArr.length === maxPlayers)} onClick={() => this.NextSubmit()} className="btn btn-primary btm-fix-btn">{AL.NEXT}</button>
                                        </div>
                                    </div>
                                </Col>
                            </Row>
                        </Tab.Container>
                        {/* <div className={"roster-footer " + this.state.showBtmBtn}>
                            <div className="btn-wrap">
                                <button disabled={!(lineupArr.length > 0)} onClick={() => this.GoToFieldView()} className="btn btn-primary btm-fix-btn stk-preview">{AL.STOCK_PREVIEW}</button>
                                <button disabled={!(lineupArr.length === maxPlayers)} onClick={() => this.NextSubmit()} className="btn btn-primary btm-fix-btn">{AL.NEXT}</button>
                            </div>
                        </div> */}
                        {
                            showPlayerCard &&
                            <Suspense fallback={<div />} >
                                <StockPlayerCard
                                    mShow={showPlayerCard}
                                    mHide={this.PlayerCardHide}
                                    playerData={playerDetails}
                                    buySellAction={this.buySellAction}
                                    addToWatchList={this.addToWatchList} />
                            </Suspense>

                        }
                        {
                            isViewAll &&
                            <StockTeamPreview salary_cap={this.state.salary_cap} isFrom={'roster'} preTeam={lineupArr} CollectionData={LobyyData} isViewAllShown={isViewAll} onViewAllHide={this.onViewAllHide} isTeamPrv={'true'} />
                        }
                        {/* {this.state.showRosterFilter &&
                            <FilterByTeam teamName={[
                                { value: { team_league_id: 0 }, label: AL.ALL_STOCK },
                                { value: { team_league_id: 1 }, label: AL.WATCHLIST_STOCK },
                                { value: { team_league_id: 2 }, label: AL.SELECTED_STOCK }
                            ]} selectedTeamOption={this.state.selectedTeamOption} onSelected={this.handleChange} />
                        } */}
                        {showRulesModal &&
                            <StockEquityFRules mShow={showRulesModal} mHide={this.hideRulesModal} stockSetting={this.state.stockSetting} showPtsOnly={true} />
                        }
                        {showBuySellModal &&
                            <BuySellStockAmountModal mShow={showBuySellModal} mHide={this.hideBuySellModal} item={this.state.selectedItem} action={this.state.selectedAction} pDiff={this.state.pDiff} minCapPerStock={this.state.min_cap_per_stock} maxCapPerStock={this.state.max_cap_per_stock} salaryCap={this.state.salary_cap} addDataFromBytSell={this.addDataFromBytSell} isUpdate={this.state.isUpdate} removeDataFromList={this.removeDataFromList} />
                        }
                        {
                            this.state.showCM && this.state.RosterCoachMarkStatus == 0 &&
                            <CMStkRosterEqModal {...this.props} cmData={{
                                mHide: this.hideCM,
                                mShow: this.showCM
                            }} />
                        }
                        {this.state.showRosterFilter &&
                            <StockRosterFilterEq 
                                filterArry={this.state.filterArry} 
                                selectedIndustry={this.state.selectedIndustry} 
                                onSelected={this.handleChange} 
                            />
                        }
                    </div>

                )}
            </MyContext.Consumer>
        )
    }
}
