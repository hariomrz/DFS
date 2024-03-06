import React, { Suspense, lazy } from 'react';
import { MyContext } from '../../InitialSetup/MyProvider';
import { getStockFixtureDetail, getStockLineupMasterData, getStockRoster, getStockLineupTeamName, getStockUserLineup, addRemoveStockWishlist } from "../../WSHelper/WSCallings";
import { Utilities, _isUndefined, _isEmpty, _Map } from '../../Utilities/Utilities';
import { Helmet } from "react-helmet";
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import ls from 'local-storage';
import WSManager from "../../WSHelper/WSManager";
import InfiniteScroll from 'react-infinite-scroll-component';
import MetaData from "../../helper/MetaData";
import CustomHeader from '../../components/CustomHeader';
import FilterByTeam from '../../components/filterByteam';
import { DARK_THEME_ENABLE,setValue, StockSetting } from '../../helper/Constants';
import { StockItem, StockTeamPreview } from '.';
import { CircularProgressBar } from '../CustomComponent';
import StockFantasyRules from './StockFantasyRules';
const StockPlayerCard = lazy(() => import('./StockPlayerCard'));

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
            selectedTeamOption: { value: { team_league_id: 0 }, label: AL.ALL_STOCK },
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
        };
        this._timeout = null;
        this.checkScrollStatus = this.checkScrollStatus.bind(this);
        this.headerRef = React.createRef();
    }

    UNSAFE_componentWillMount = () => {
        this.setLocationStateData();
        window.addEventListener('scroll', this.onScrollList);
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

        getStockUserLineup(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                if (this.state.lineupArr.length === 0) {
                    _Map(responseJson.data.lineup, (item) => {
                        this.buySellAction(item.action, item)
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
        this.setState({ showRosterFilter: false, selectedTeamOption: selectedOption }, () => {
            this.applyTeamFilter(selectedOption.value)
        });
    }

    applyTeamFilter(team) {
        let { allRosterList } = this.state;
        let tmpFilterArray = [];
        if (team.team_league_id === 0) {
            tmpFilterArray = allRosterList;
        }
        if (team.team_league_id === 1) {
            tmpFilterArray = allRosterList.filter((stock) => {
                return stock.is_wish == 1
            });
        }
        if (team.team_league_id === 2) {
            tmpFilterArray = allRosterList.filter((stock) => {
                return stock.is_selected
            });
        }
        this.setState({ rosterList: tmpFilterArray })
    }

    fetchLineupMasterData = async () => {
        let param = {
            "collection_id": this.state.collectionMasterId,
        }
        var api_response_data = await getStockLineupMasterData(param);
        if (api_response_data.response_code === WSC.successCode) {
            this.parseMasterData(api_response_data.data);
        }
    }

    parseMasterData(api_response_data) {
        let data = api_response_data.length > 0 ? api_response_data[0] : ''
        this.setState({
            masterData: data || '',
            maxPlayers: data.config_data ? data.config_data.tc || 0 : 0,
            maxPlayerBuy: data.config_data ? data.config_data.b || 0 : 0,
            maxPlayerSell: data.config_data ? data.config_data.s || 0 : 0,
            StockSettingValue: data || ''
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
                if (this.state.lineupArr.length > 0) {
                    _Map(this.state.lineupArr, (item) => {
                        this.buySellAction(item.action, item)
                    })
                }
            })
        }
        if (this.props.location.state.from == 'editView' && !this.state.isClone) {
            this.getLineupForEdit();
        }
    }

    NextSubmit = () => {
        let urlData = _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData;
        let name = urlData.category_id.toString() === "1" ? 'Daily' : urlData.category_id.toString() === "2" ? 'Weekly' : 'Monthly';
        let selectCaptainPath = '/stock-fantasy/select-captain/' + name
        this.props.history.push({ 
            pathname: selectCaptainPath.toLowerCase(), 
            state: { teamName: this.state.teamName, SelectedLineup: this.state.lineupArr, MasterData: this.state.masterData, 
                LobyyData: urlData, FixturedContest: this.state.FixturedContest, isFrom: this.state.isFrom, team: this.state.TeamMyContestData, 
                rootDataItem: this.state.rootDataItem, isFromMyTeams: this.state.isFromMyTeams, ifFromSwitchTeamModal: this.state.ifFromSwitchTeamModal, 
                isClone: this.state.isClone, lineup_master_contest_id: this.props.location.state.lineup_master_contest_id, 
                teamitem: this.props.location.state.teamitem,StockSettingValue:this.state.StockSettingValue } })
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
                    this.applyTeamFilter(this.state.selectedTeamOption.value)
                })
            }
        })
    }
    buySellAction = (action, item) => {
        console.log('action')
        if (item.action != action && action == 1 && !(this.state.selBuyC < this.state.maxPlayerBuy)) {
            let msg = AL.STOCK_MAX_BUY.replace('##', this.state.maxPlayerBuy)
            Utilities.showToast(msg, 3000);
        } else if (item.action != action && action == 2 && !(this.state.selSellC < this.state.maxPlayerSell)) {
            let msg = AL.STOCK_MAX_SELL.replace('##', this.state.maxPlayerSell)
            Utilities.showToast(msg, 3000);
        } else {
            let tmpAllList = this.state.allRosterList;
            for (var obj of tmpAllList) {
                if (obj.stock_id === item.stock_id) {
                    let oldAct = obj['action'];
                    obj['action'] = oldAct == action ? '' : action;
                    obj['is_selected'] = oldAct == action ? false : true;
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
            this.setState({
                allRosterList: tmpAllList,
                lineupArr: tmpArry,
                selSellC,
                selBuyC
            }, () => {
                this.applyTeamFilter(this.state.selectedTeamOption.value)
            })
            if (this.headerRef && this.headerRef.current) {
                this.headerRef.current.GetHeaderProps("lineup", this.state.lineupArr, this.state.masterData, _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData, this.state.FixturedContest, this.state.isFrom, this.state.rootDataItem, this.state.teamData ? this.state.teamData : this.state.teamName);
            }
        }
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
            showRulesModal
        } = this.state;
        const HeaderOption =
        {
            back: true,
            isPrimary: DARK_THEME_ENABLE ? false : true,
            filter: true,
            title: '',
            hideShadow: false,
            showAlertRoster: true,
            resetIndex: this.props.location.state.nextStepData ? this.props.location.state.nextStepData.resetIndex : this.props.location.state.resetIndex,
            showRosterFilter: this.showRosterFilter,
            showFilterByTeam: true,
            screentitle: LobyyData ? (LobyyData.collection_name && LobyyData.collection_name != '' ? LobyyData.collection_name : LobyyData.category_id.toString() === "1" ? AL.DAILY : LobyyData.category_id.toString() === "2" ? AL.WEEKLY : AL.MONTHLY) + ' ' + AL.STOCK_FANTASY : '',
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
                    <div className={"web-container stock-roster white-bg "}>
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.lineup.title}</title>
                            <meta name="description" content={MetaData.lineup.description} />
                            <meta name="keywords" content={MetaData.lineup.keywords}></meta>
                        </Helmet>
                        <CustomHeader ref={this.headerRef} {...this.props} HeaderOption={HeaderOption} />
                        <div className="stock-roster-header">
                            <div className="count-header">
                                <div>
                                    <div className="sel-stk">{AL.SELECTED_STOCK}</div>
                                    <div className="stk-left">{lineupArr.length}/{maxPlayers} {AL.LEFT}</div>
                                    <div className="stk-b-s">{AL.BUY_SELL_STOCK.replace('##', maxPlayers)}</div>
                                    <div className="stk-b-s-link">
                                        <a href
                                            onClick={() => this.openRulesModal()}
                                        >
                                            <i className="icon-file"></i>
                                            <span>{AL.SCORING_RULES}</span>
                                        </a>
                                    </div>
                                </div>
                                <div className='prog-v'>
                                    <CircularProgressBar
                                        isSF={true}
                                        progressPer={lineupArr.length}
                                        maxPlayers={maxPlayers}
                                    />
                                </div>
                            </div>
                            <div className="item-header watchlist">
                                <span onClick={() => {
                                    this.setState({ sort_field: 'comp', sort_order: (this.state.sort_order == 'DESC' ? 'ASC' : 'DESC') });
                                    this.setState({ rosterList: this.state.rosterList.sort((a, b) => (this.state.sort_order == 'DESC' ? a.stock_name.localeCompare(b.stock_name) :  b.stock_name.localeCompare(a.stock_name))) })
                                }}>{AL.COMPANY_NAME}{this.state.sort_field == 'comp' && <i className={this.state.sort_order == 'DESC' ? "icon-arrow-down" : 'icon-arrow-up'}></i>}</span>
                                <span onClick={() => {
                                    this.setState({ sort_field: 'per', sort_order: (this.state.sort_order == 'DESC' ? 'ASC' : 'DESC') });
                                    this.setState({ rosterList: this.state.rosterList.sort((a, b) => (this.state.sort_order == 'DESC' ? a.percent_change - b.percent_change : b.percent_change - a.percent_change)) })
                                }}>% Chg  {this.state.sort_field == 'per' && <i className={this.state.sort_order == 'DESC' ? "icon-arrow-down" : 'icon-arrow-up'}></i>}</span>


                                <span>{AL.PICK_STOCK}</span>
                            </div>
                        </div>
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
                                                btnAction={this.buySellAction}
                                                disabled={disabled}
                                                openPlayerCard={this.PlayerCardShow}
                                                StockSettingValue={StockSettingValue} 
                                            />
                                        )
                                    })
                                }
                            </InfiniteScroll>
                        </div>

                        <div className={"roster-footer " + this.state.showBtmBtn}>
                            <div className="btn-wrap">
                                <button disabled={!(lineupArr.length > 0)} onClick={() => this.GoToFieldView()} className="btn btn-primary btm-fix-btn stk-preview">{AL.STOCK_PREVIEW}</button>
                                <button disabled={!(lineupArr.length === maxPlayers)} onClick={() => this.NextSubmit()} className="btn btn-primary btm-fix-btn">{AL.NEXT}</button>
                            </div>
                        </div>
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
                            <StockTeamPreview isFrom={'roster'} preTeam={lineupArr} CollectionData={LobyyData} isViewAllShown={isViewAll} onViewAllHide={this.onViewAllHide}  StockSettingValue={this.state.StockSettingValue} isTeamPrv={'true'} />
                        }
                        {this.state.showRosterFilter &&
                            <FilterByTeam teamName={[
                                { value: { team_league_id: 0 }, label: AL.ALL_STOCK },
                                { value: { team_league_id: 1 }, label: AL.WATCHLIST_STOCK },
                                { value: { team_league_id: 2 }, label: AL.SELECTED_STOCK }
                            ]} selectedTeamOption={this.state.selectedTeamOption} onSelected={this.handleChange} />
                        }
                        {showRulesModal &&
                            <StockFantasyRules mShow={showRulesModal} mHide={this.hideRulesModal} stockSetting={this.state.stockSetting} showPtsOnly={true} />
                        }
                    </div>

                )}
            </MyContext.Consumer>
        )
    }
}

