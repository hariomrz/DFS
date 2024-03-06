import React from 'react';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Button, Table } from 'react-bootstrap';
import { getStockUserAllTeams, getStockUserLineup,getStockUserLineupEquity, getStockLobbySetting } from "../../WSHelper/WSCallings";
import { Utilities, _isEmpty, _Map, _isUndefined } from '../../Utilities/Utilities';
import { DARK_THEME_ENABLE ,setValue, StockSetting, SELECTED_GAMET, GameType} from '../../helper/Constants';
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import ls from 'local-storage';
import Images from '../../components/images';
import WSManager from "../../WSHelper/WSManager";
import InfiniteScroll from 'react-infinite-scroll-component';
import { NoDataView } from '../../Component/CustomComponent';
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import StockTeamPreview from './StockTeamPreview';
/**
  * @description Display shimmer effects while loading list
  * @return UI components
*/
const Shimmer = ({ index }) => {
    return (
        <SkeletonTheme color={DARK_THEME_ENABLE ? "#161920" : null} highlightColor={DARK_THEME_ENABLE ? "#0E2739" : null}>
            <div key={index} className="contest-list m">
                <div className="shimmer-container">
                    <div className="shimmer-top-view">
                        <div className="shimmer-line">
                            <Skeleton height={9} />
                            <Skeleton height={6} />
                            <Skeleton height={4} width={100} />
                        </div>
                        <div className="shimmer-image">
                            <Skeleton width={30} height={30} />
                        </div>
                    </div>
                    <div className="shimmer-bottom-view">
                        <div className="progress-bar-default">
                            <Skeleton height={6} />
                            <div className="d-flex justify-content-between">
                                <Skeleton height={4} width={60} />
                                <Skeleton height={4} width={60} />
                            </div>
                        </div>
                        <div className="shimmer-buttin">
                            <Skeleton height={30} />
                        </div>
                    </div>
                </div>
            </div>
        </SkeletonTheme>
    )
}

class StockMyTeams extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            TeamsList: [],
            CollectionData: undefined,
            isFromCreateTeam: false,
            hasMore: false,
            isLoaderShow: false,
            offset: 0,
            selectedTeams: [],
            rootitem: [],
            ShimmerList: [1, 2, 3, 4, 5],
            isViewAll: false,
            openTeam: '',
            StockSettingValue: []
        }
    }

    UNSAFE_componentWillMount() {
        this.setLocationStateData();
    }

    componentDidMount() {
        let MatchProps = this.props.LobyyData || this.props.match.params;
        let CollectionData = this.state.CollectionData || MatchProps
        this.setState({
            CollectionData
        })
        // if (CollectionData.collection_master_id || CollectionData.collection_id) {
        //     this.getUserLineUpListApi(CollectionData)
        // }
        // if(StockSetting.length > 0){
        //     this.setState({
        //         StockSettingValue: StockSetting
        //     })
        // }
        // else{
        //     getStockLobbySetting().then((responseJson) => {
        //         setValue.setStockSettings(responseJson.data);
        //         this.setState({ StockSettingValue: responseJson.data })
        //     })
        // }
    }

    componentWillReceiveProps(nextProps) {
        // if (nextProps && nextProps.LobyyData !== this.props.LobyyData) {
        //     this.getUserLineUpListApi(nextProps.LobyyData);
        // }
        console.log('nextProps',nextProps)
        if(nextProps && nextProps.LobyyData){
            this.setState({
                CollectionData: nextProps.LobyyData
            })
        }
        if(nextProps && nextProps.myTeamCount && nextProps.myTeamCount != this.state.myTeamCount && nextProps.TotalTeam && nextProps.TotalTeam.length != this.state.TeamsList.length){
            console.log('UNSAFE_componentWillReceiveProps')
            this.setState({
                TeamsList: nextProps.TotalTeam,
            })
        }
    }

    openAllPlayer(item) {
        this.setState({ openTeam: item, isViewAll: true })
    }

    onViewAllHide = () => {
        this.setState({ isViewAll: false })
    }

    setLocationStateData() {
        if (this.props.location && this.props.location.state) {
            const { LobyyData, isFromCreateTeam, TotalTeam } = this.props.location.state;
            this.setState({
                CollectionData: LobyyData ? LobyyData : undefined,
                isFromCreateTeam: isFromCreateTeam ? isFromCreateTeam : false,
                TeamsList: TotalTeam ? TotalTeam : []
            })
            setTimeout(() => {
                if (this.headerRef) {
                    this.headerRef.GetHeaderProps("lobbyheader", '', '', LobyyData ? LobyyData : '');
                }
            }, 100);
        }
    }

    getUserLineUpListApi = async (CollectionData) => {
        if (!this.state.isLoaderShow) {
            let param = {
                "collection_id": CollectionData.collection_master_id || CollectionData.collection_id,
            }
            this.setState({ isLoaderShow: true })
            var api_response_data = await getStockUserAllTeams(param)
            if (api_response_data.response_code === WSC.successCode) {
                this.setState({
                    TeamsList: api_response_data.data,
                })
            }
            this.setState({ isLoaderShow: false })
        }
    }

    fetchMoreData = () => {
        this.getUserLineUpListApi()
    }

    createLineup = (CollectionData) => {
        if (CollectionData) {
            WSManager.clearLineup();
            let name = CollectionData.category_id.toString() === "1" ? 'Daily' : CollectionData.category_id.toString() === "2" ? 'Weekly' : 'Monthly';
            let lineupPath = SELECTED_GAMET == GameType.StockFantasy ?  '/stock-fantasy/lineup/' : '/stock-fantasy-equity/lineup/' ;

            this.props.history.push({
                pathname: lineupPath + name.toLowerCase(),
                state: {
                    FixturedContest: CollectionData,
                    LobyyData: CollectionData,
                    from: 'MyTeams',
                    isFromMyTeams: true,
                    isFrom: "MyTeams",
                    resetIndex: 1
                }
            })
        }
    }

    openContestListing() {
        let url = window.location.href;
        if (url.includes('#')) {
            url = url.split('#')[0];
        }
        if (this.props.handleTab) {
            this.props.handleTab(0, { from: 'MyTeams', lineupObj: this.state.selectedTeams });
        }
    }

    cloneLineup(rootitem, teamItem) {
        this.getUserLineup(rootitem, teamItem).then((lineupData) => {

            if (lineupData) {

                let MasterData = lineupData;
                let lineupArr = lineupData.lineup;

                ls.set('Lineup_data', lineupArr);
                ls.set('showMyTeam',1)

                teamItem['team_name'] = '';

                let name = rootitem.category_id.toString() === "1" ? 'Daily' : rootitem.category_id.toString() === "2" ? 'Weekly' : 'Monthly';
                let lineupPath = SELECTED_GAMET == GameType.StockFantasy ?  '/stock-fantasy/lineup/' : '/stock-fantasy-equity/lineup/' ;
                 this.props.history.push({
                    pathname: lineupPath + name.toLowerCase(),
                    state: {
                        SelectedLineup: lineupArr,
                        MasterData: MasterData,
                        LobyyData: rootitem,
                        FixturedContest: rootitem,
                        team: teamItem,
                        from: 'editView',
                        rootDataItem: rootitem,
                        isFromMyTeams: true,
                        ifFromSwitchTeamModal: false,
                        resetIndex: 1,
                        isClone: true,
                        teamitem: teamItem,
                        collection_master_id: teamItem.collection_master_id
                    }
                });
            }
        });

    }

    async getUserLineup(rootitem, teamItem) {

        let param = {
            "lineup_master_id": teamItem.lineup_master_id,
            "collection_id": rootitem.collection_master_id,
        }
        let apiCall =  SELECTED_GAMET ==  GameType.StockFantasyEquity ? getStockUserLineupEquity : getStockUserLineup

        let responseJson = await apiCall(param);
        let lineupData = '';
        if (responseJson.response_code === WSC.successCode) {
            lineupData = responseJson.data;
        }

        return lineupData;
    }


    openLineup(rootitem, contestItem, teamitem, isEdit) {
        ls.set('showMyTeam',1)
        this.setState({
            rootitem: rootitem
        })
        if (isEdit === true) {
            let name = rootitem.category_id && rootitem.category_id.toString() === "1" ? 'Daily' : rootitem.category_id.toString() === "2" ? 'Weekly' : 'Monthly';
            let lineupPath = SELECTED_GAMET == GameType.StockFantasy ?  '/stock-fantasy/lineup/' : '/stock-fantasy-equity/lineup/' ;
            this.props.history.push({
                pathname: lineupPath + name.toLowerCase(),
                state: {
                    SelectedLineup: this.state.lineupArr,
                    MasterData: this.state.MasterData,
                    LobyyData: _isEmpty(this.state.LobyyData) ? rootitem : this.state.LobyyData,
                    FixturedContest: rootitem,
                    team: teamitem,
                    from: 'editView',
                    rootDataItem: rootitem,
                    isFromMyTeams: true,
                    ifFromSwitchTeamModal: false,
                    resetIndex: 1,
                    teamitem: teamitem,
                    collection_master_id: contestItem.collection_master_id,
                    league_id: contestItem.league_id
                }
            });
        } else {
        }
    }

    onSelectTeam = (item) => {
        const tmpArray = this.state.selectedTeams;
        if (tmpArray.includes(item)) {
            const idx = tmpArray.indexOf(item);
            if (idx > -1) {
                tmpArray.splice(idx, 1);
            }
        } else {
            tmpArray.push(item)
        }
        this.setState({ selectedTeams: tmpArray })
    }

    onSelectTeam = (item) => {
        const tmpArray = this.state.selectedTeams;
        if (tmpArray.includes(item)) {
            const idx = tmpArray.indexOf(item);
            if (idx > -1) {
                tmpArray.splice(idx, 1);
            }
        } else {
            tmpArray.push(item)
        }
        this.setState({ selectedTeams: tmpArray })
    }
    onAllSelect = () => {
        if (this.state.selectedTeams.length === this.state.TeamsList.length) {
            this.setState({
                selectedTeams: []
            })
        } else {
            const tmpArray = [];
            _Map(this.state.TeamsList, (item) => {
                tmpArray.push(item)
            })
            this.setState({
                selectedTeams: tmpArray
            })
        }
    }

    render() {

        const {
            hasMore,
            isLoaderShow,
            CollectionData,
            ShimmerList,
            isViewAll,
            openTeam,
            StockSettingValue
        } = this.state;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container account-wrapper my-team-root web-container-fixed bg-white stock-f" >
                        <div className="webcontainer-inner mt-0">
                            {
                                (!this.state.isLoaderShow && this.state.TeamsList.length < parseInt(Utilities.getMasterData().a_teams) && this.state.TeamsList.length > 0) &&
                                <div className="text-center">
                                    <Button className="btn create-team-button mt15" onClick={() => this.createLineup(CollectionData)}>
                                        <span className="text-uppercase" >{AppLabels.CREATE_NEW_TEAM.replace(AppLabels.Team, AppLabels.PORTFOLIO)}</span>
                                    </Button>
                                </div>
                            }

                            <div className={"reverse-heading justify-content-end " + (this.state.TeamsList.length > 0 ? ' select-all' : '')}>
                                {
                                    this.state.TeamsList.length > 0 && <div className="cursor-pointer d-flex" onClick={() => this.onAllSelect()}>
                                        <div>
                                            {(AppLabels.SELECT + " " + AppLabels.ALL).toLowerCase()}
                                        </div>
                                        <div className={"select-team-checkbox m-l-sm " + (this.state.selectedTeams.length === this.state.TeamsList.length ? 'selected' : '')} />
                                    </div>
                                }
                            </div>


                            <InfiniteScroll
                                dataLength={this.state.TeamsList.length}
                                next={this.fetchMoreData.bind(this)}
                                hasMore={hasMore}
                                scrollableTarget='test'
                                loader={
                                    isLoaderShow === true &&
                                    <h4 className='table-loader'>{AppLabels.LOADING_MSG}</h4>
                                }>
                                <ul className="transaction-list transaction-class-scroll no-height" id="test">
                                    {
                                        this.state.TeamsList && this.state.TeamsList.map((item, index) => {
                                            if (item.collection_id) {
                                                item['collection_master_id'] = item.collection_id
                                            }
                                            let price_diff = parseFloat(item.price_diff || "0").toFixed(2);
                                            price_diff = price_diff == 0 ? '0.00' : price_diff;
                                            let vc_price_diff = parseFloat(item.vc_price_diff || "0").toFixed(2);
                                            vc_price_diff = vc_price_diff == 0 ? '0.00' : vc_price_diff;
                                            return (
                                                <li className="my-team-list-item stk" key={item.lineup_master_id + index}>
                                                    <div className={"my-teams-item " + (this.state.selectedTeams.includes(item) ? 'selected' : '')}>

                                                        <div className="row-header">
                                                            <div className="name-container">
                                                                <div className="team-name">{item.team_name}</div>
                                                                <div className="contests-joined">{item.total_joined} {AppLabels.CONTEST_JOINED}</div>
                                                            </div>


                                                            <div onClick={() => this.onSelectTeam(item)}
                                                                className={"select-team-checkbox " + (this.state.selectedTeams.includes(item) ? 'selected' : '')}>
                                                                <i className="icon-tick-ic"></i>
                                                            </div>
                                                        </div>
                                                        <Table>
                                                            <tbody>
                                                                <tr className={"captain-vice-captain "+ ((!_isUndefined(item.c_name) && !_isUndefined(item.vc_name) && item.c_name != null && item.vc_name != null) ? " full-width-content" : "")}>
                                                                    <td>
                                                                        <div className="image-container">
                                                                            <img className="player-image" alt="" src={item.logo ? Utilities.getStockLogo(item.logo) : Images.BRAND_LOGO_FULL_PNG} />
                                                                            <span className="player-post captain">{AppLabels.A}</span>
                                                                        </div>
                                                                        <div className="player-name-container">
                                                                            <div className="player-name">{item.c_name}</div>
                                                                            <div className={"team-vs-team" + (price_diff < 0 ? ' down' : '')}>
                                                                                <i className={price_diff < 0 ? "icon-stock_down" : "icon-stock_up"} />{Utilities.numberWithCommas(item.current_price)}<span>{Utilities.numberWithCommas(price_diff)}</span>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    {!_isUndefined(item.vc_name) &&
                                                                        <React.Fragment>
                                                                            {
                                                                                item.vc_name != null &&
                                                                                <td>
                                                                                    
                                                                                    <div className="image-container">
                                                                                        <img className="player-image" alt="" src={item.vc_logo ? Utilities.getStockLogo(item.vc_logo) : Images.BRAND_LOGO_FULL_PNG} />
                                                                                        <span className="player-post vice-captain">{AppLabels.B}</span>
                                                                                    </div>
                                                                                    <div className="player-name-container">
                                                                                        <div className="player-name">{item.vc_name}</div>
                                                                                        <div className={"team-vs-team" + (vc_price_diff < 0 ? ' down' : '')}>
                                                                                            <i className={vc_price_diff < 0 ? "icon-stock_down" : "icon-stock_up"} />{Utilities.numberWithCommas(item.vc_current_price)}<span>{Utilities.numberWithCommas(vc_price_diff)}</span>
                                                                                        </div>
                                                                                    </div>
                                                                                </td>
                                                                            }
                                                                        </React.Fragment>
                                                                    }
                                                                </tr>
                                                            </tbody>
                                                        </Table>
                                                        {(this.state.TeamsList && this.state.TeamsList.length < parseInt(Utilities.getMasterData().a_teams)) &&
                                                            <a href id='clone-button' title="Clone this team"
                                                                className="clone-team"
                                                                onClick={() => this.cloneLineup(CollectionData, item)}>
                                                                <i className="icon-copy-ic"></i>
                                                            </a>
                                                        }
                                                        <a href title="Edit this team"
                                                            className="edit-team"
                                                            onClick={() => this.openLineup(CollectionData, CollectionData, item, true)}>
                                                            <i className="icon-edit-line"></i>
                                                        </a>
                                                        <div className="bottom-row"
                                                            onClick={() => this.openAllPlayer(item)}
                                                        >
                                                            <span>{AppLabels.VIEW_ALL_SCRIPS}</span>
                                                            <i className='icon-next-arrow'></i>
                                                        </div>
                                                    </div>

                                                </li>
                                            )
                                        })
                                    }

                                    {
                                        this.state.TeamsList.length === 0 && !this.state.isLoaderShow &&
                                        <NoDataView
                                            BG_IMAGE={Images.no_data_bg_image}
                                            // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                            CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                            MESSAGE_1={AppLabels.NO_TEAM_MSG.replace(AppLabels.Team.toLowerCase(), AppLabels.PORTFOLIO.toLowerCase()) + ' ' + AppLabels.THIS_CONTEST}
                                            BUTTON_TEXT={AppLabels.CREATE_NEW_TEAM.replace(AppLabels.Team, AppLabels.PORTFOLIO)}
                                            onClick={() => this.createLineup(this.state.CollectionData)}
                                        />
                                    }

                                    {
                                        this.state.TeamsList.length === 0 && this.state.isLoaderShow &&
                                        ShimmerList.map((item, index) => {
                                            return (
                                                <Shimmer key={index} index={index} />
                                            )
                                        })
                                    }
                                </ul>
                            </InfiniteScroll>

                        </div>
                        {
                            this.state.selectedTeams && this.state.selectedTeams.length > 0 &&
                            <Button
                                onClick={() => this.openContestListing()}
                                className="bottom">
                                {AppLabels.JOIN_CONTEST}
                                {
                                    this.state.selectedTeams.length > 1 &&
                                    <span className="my-t-j-width">({AppLabels.WITH1 + " " + this.state.selectedTeams.length + " " + AppLabels.WITH2})</span>
                                }
                            </Button>
                        }
                        {
                            isViewAll &&
                            <StockTeamPreview isFrom={'preview'} CollectionData={CollectionData} openTeam={openTeam} isViewAllShown={isViewAll} onViewAllHide={this.onViewAllHide}  
                            // StockSettingValue={this.state.StockSettingValue} 
                            isTeamPrv={'true'} />
                            
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}
export default StockMyTeams;