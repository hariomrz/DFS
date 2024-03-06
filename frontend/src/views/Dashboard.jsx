import React, { lazy, Suspense } from 'react';
import { Tabs, Tab, Nav, NavItem, Row, Col } from 'react-bootstrap';
import { MyContest } from '../Component/MyContest';
import { Utilities, _Map, _filter, _debounce, _isEmpty,IsGameTypeEnabled, _cloneDeep } from '../Utilities/Utilities';
import { Helmet } from "react-helmet";
import queryString from 'query-string';
import ls from 'local-storage';
import Loadable from 'react-loadable';
import MetaData from "../helper/MetaData";
import WSManager from '../WSHelper/WSManager';
import { GetPickFantasySports, otGetSportsList } from '../WSHelper/WSCallings';
import * as NC from "../WSHelper/WSConstants";
import Profile from '../Component/Profile/MyProfile';
import Lobby from './Lobby';
import CustomHeader from '../components/CustomHeader';
import More from './More';
import Notification from '../views/Notification';
import Wallet from '../Component/Finance/MyWallet';
import ReferFriend from './ReferFriend';
import ReferFriendStandard from '../views/ReferFriendStandard';
import Images from '../components/images';
import * as AppLabels from "../helper/AppLabels";
import * as Constants from "../helper/Constants";
import { Sports } from "../JsonFiles";
import { StockLobby, StockMyContest } from '../Component/StockFantasy';
import { StockLobbyEquity } from '../Component/StockFantasyEquity';
import { LiveFantasyLobby } from '../Component/LiveFantasy/LiveFantasyLobby';
import { LFMycontest } from '../Component/LiveFantasy';
import { SPLobby, SPMyContest } from "../Component/StockPrediction";
import Feed from '../Component/Feed/Feed';
import { LSFLobby, LSFMyContest } from "../Component/LiveStockFantasy";
import { DMLobby, DMMyContest } from '../Component/DFSWithMultigame';
import { PFLobby } from '../Component/PickFantasy';
import PFMyContest from '../Component/PickFantasy/PFMyContest';
import { PTLobby, PTMyContest } from '../Component/PickemTournament';
import ReactSlickSlider, { SportsNavigation } from '../Component/CustomComponent/ReactSlickSlider';
import { TLeaderboard } from '../Component/TourLeaderboard';

import { Lobby as OTLobby } from '../OpinionTrade/View';

export const MyContext = React.createContext()
var mContext = null;
function LoadingComponent() {
    return <div className="web-container" />;
}
const PredictionLobby = Loadable({
    loader: () => {
        return import('../Component/PredictionModule/PredictionLobby')
    },
    delay: 0,
    loading: LoadingComponent
});
const MyPrediction = Loadable({
    loader: () => {
        return import('../Component/PredictionModule/MyPredictions')
    },
    delay: 0,
    loading: LoadingComponent
});
const OpenPredictorLobby = Loadable({
    loader: () => {
        return import('../Component/OpenPredictorModule/OpenPredictorLobby')
    },
    delay: 0,
    loading: LoadingComponent
});
const MyOpenPredictors = Loadable({
    loader: () => {
        return import('../Component/OpenPredictorModule/MyOpenPredictors')
    },
    delay: 0,
    loading: LoadingComponent
});
const LeagueLeaderBoard = Loadable({
    loader: () => {
        return import('../Component/FreeToPlayModule/LeaguaNavLeaderBoard')
    },
    delay: 0,
    loading: LoadingComponent
});
const OpenPredictorFPPLobby = Loadable({
    loader: () => {
        return import('../Component/OpenPredictorFPPModule/OpenPredictorFPPLobby')
    },
    delay: 0,
    loading: LoadingComponent
});
const MyFPPOpenPredictors = Loadable({
    loader: () => {
        return import('../Component/OpenPredictorFPPModule/MyFPPOpenPredictors')
    },
    delay: 0,
    loading: LoadingComponent
});
const FPPOpenPredictionLeaderboard = Loadable({
    loader: () => {
        return import('../Component/OpenPredictorFPPModule/OpenPredictionFPPLeaderboard')
    },
    delay: 0,
    loading: LoadingComponent
});
const FantasyRefLeaderboard = Loadable({
    loader: () => {
        return import('../Component/FantasyRefLeaderboard/FantasyRefLeaderboard')
    },
    delay: 0,
    loading: LoadingComponent
});

const PropsLobby = Loadable({
    loader: () => {
        return import('../Component/PropsFantasy/PropsLobby')
    },
    delay: 0,
    loading: LoadingComponent
});
const OpinionLobby = Loadable({
    loader: () => {
        return import('../OpinionTrade/View/Lobby')
    },
    delay: 0,
    loading: LoadingComponent
});

const PropsMyContest = Loadable({
    loader: () => {
        return import('../Component/PropsFantasy/PropsMyContest')
    },
    delay: 0,
    loading: LoadingComponent
});
const OpinionMyContest = Loadable({
    loader: () => {
        return import('../OpinionTrade/View/ViewCompletedEntries')
    },
    delay: 0,
    loading: LoadingComponent
});
export default class Dashboard extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            activeSportsTab: 0,
            ShimmerList: [1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1],
            activeTab: 'lobby',
            urlParams: '',
            sportsList: [],
            showLobbyFitlers: false,
            EarnCoins: LoadingComponent,
            PredictionLobby: LoadingComponent,
            MyPrediction: LoadingComponent,
            selectedGameType: WSManager.getPickedGameType(),
            getGameTypeSport: Utilities.getGameTypeSports(),
            MultiGame: LoadingComponent,
            FreeToPlay: LoadingComponent,
            LiveFantasy: LoadingComponent,
            CurrentGameType: Constants.SELECTED_GAMET,
            headerReload: true,
            AvaSports: [],
            windowWidth: window.innerWidth,
            HeaderOption: {
                menu: true,
                title: "",
                notification: true,
                filter: true,
                edit: false,
                hideShadow: true,
                hideHeader: false,
                close: false
            },
            footerTabs: Constants.DASHBOARD_FOOTER.tabs,
            showNaviStrip: false,
            showNaviBtnSec: false,
            switchPosting: false,
            isSportIdListener: null 
        }
        this.MenuRef = React.createRef();

    }
    componentDidMount() {
        window.addEventListener('resize', (event) => {
            this.setState({
                windowWidth: window.innerWidth
            })
        });
        document.addEventListener("mousedown", this.handleClickOutside);
        // let url =window.location.href;
        // if(Constants.SELECTED_GAMET == Constants.GameType.PickFantasy && Constants.PFSelectedSport && !url.includes(Constants.PFSelectedSport.name)){
        //     this.props.history.replace("/lobby#" + Constants.PFSelectedSport.name)
        // }
    }

    handleClickOutside = e => {
        if (this.state.showNaviBtnSec && this.state.showNaviStrip && Utilities.getMasterData().a_coin == "1" && Constants.IS_SPORTS_HUB && WSManager.loggedIn() && (!this.MenuRef && !this.MenuRef.current && !this.MenuRef.current.contains(e.target))) {
            this.setState({ showNaviStrip: false });
        }
    };


    componentWillUnmount() {
        window.removeEventListener('resize', () => { });
        document.removeEventListener("mousedown", this.handleClickOutside);
        this.setState = () => {
            return;
        };
    }

    willmountCalling = () => {
        let isPF = Constants.SELECTED_GAMET == Constants.GameType.PickFantasy ? true : false
        let sports_id = isPF ? '' : Utilities.getUrlSports();
        if (!this.checkPickedGame()) {
            if(isPF){
                if(ls.get('PFSportList')){
                    let tempArray = ls.get('PFSportList');
                    if (!Constants.PFSelectedSport) {
                        Constants.setValue.setPFSelectedSport(tempArray[0]);
                    }
                    this.setState({
                        sportsList: tempArray
                    }, () => {
                        var SID = Utilities.getPFUrlSports();
                        let url = this.props.location.search;
                        let urlParams = queryString.parse(url);

                        let pathData = Constants.DASHBOARD_FOOTER.config.tab_path;
                        let event = pathData[window.location.pathname];

                        this.onSelect(event, true);
                        this.setState({ activeTab: event, urlParams, activeSportsTab: SID.sports_id })
                        if (Constants.PFSelectedSport == null) {
                            this.checkSportId();
                        }
                    })
                }
                else{
                    GetPickFantasySports().then((responseJson)=>{
                        if (responseJson && responseJson.response_code == NC.successCode) {
                            let data = [{
                                is_default: "0",
                                name: "featured",
                                sports_id: "0"
                            }]
                            data= [...data,...responseJson.data];
                            ls.set('PFSportList',data)
                            ls.set('PFSSport',data[0]);
                            this.setState({
                                sportsList: data,
                                AvaSports: data,
                            }, () => {
                                if (!Constants.PFSelectedSport) {
                                    Constants.setValue.setPFSelectedSport(data[0]);
                                }
                                let pathurl =window.location.href;
                                if(window.location.pathname === "/lobby" && Constants.SELECTED_GAMET == Constants.GameType.PickFantasy && Constants.PFSelectedSport && !pathurl.includes(Constants.PFSelectedSport.name)){
                                    this.props.history.replace(window.location.pathname + "#" + Utilities.getPFSelectedSportsForUrl() + Utilities.getGameTypeHash())
                                }
                                let SID = Utilities.getPFUrlSports()
                                let url = this.props.location.search;
                                let urlParams = queryString.parse(url);

                                let pathData = Constants.DASHBOARD_FOOTER.config.tab_path;
                                let event = pathData[window.location.pathname];

                                this.onSelect(event, true);
                                this.setState({ activeTab: event, urlParams, activeSportsTab: SID.sports_id })

                                if (Constants.PFSelectedSport == null) {
                                    this.checkSportId();
                                }
                            })
                        }
                    })
                }
            }
            else {
                this.setState({ sportsList: Utilities.getMasterData().fantasy_list ? Utilities.getMasterData().fantasy_list : [] }, () => {
                    Constants.setValue.setAppSelectedSport(sports_id);

                    let url = this.props.location.search;
                    let urlParams = queryString.parse(url);

                    let pathData = Constants.DASHBOARD_FOOTER.config.tab_path;
                    let event = pathData[window.location.pathname];

                    this.onSelect(event, true);
                    this.setState({ activeTab: event, urlParams, activeSportsTab: sports_id })
                    if (Constants.AppSelectedSport == null) {
                        this.checkSportId();
                    }
                })
            }

            if (Utilities.getMasterData().a_coin == 0 || !WSManager.loggedIn()) {
                var filterArray = _filter(Constants.DASHBOARD_FOOTER.tabs, (item) => {
                    return item.tab_key !== 'earn-coins'
                })
                this.setState({ footerTabs: filterArray })
            }
            if(Constants.SELECTED_GAMET == Constants.GameType.OpinionTradeFantasy) {
                var filterArray = _Map(_cloneDeep(Constants.DASHBOARD_FOOTER.tabs), (item, idx) => {
                    
                    if (item.tab_key === "leaderboard") {
                        item.tab_key = 'my-profile'
                    }
                    return item
                })
                this.setState({ footerTabs: filterArray })
            }
            if (Utilities.getMasterData().a_coin == '1') {
                import('../Component/CoinsModule').then(CM => {
                    if (CM.EarnCoins) {
                        this.setState({
                            EarnCoins: CM.EarnCoins
                        });
                    }
                });
            }
            if (Constants.SELECTED_GAMET == Constants.GameType.MultiGame) {
                import('../Component/MultiGameModule').then(MM => {
                    if (MM.MultiGameLobby) {
                        this.setState({
                            MultiGame: MM.MultiGameLobby,
                        });
                    }
                });
            }
            if (Constants.SELECTED_GAMET == Constants.GameType.Free2Play) {
                import('../Component/FreeToPlayModule').then(FF => {
                    if (FF.LandingFreeToPlay) {
                        this.setState({
                            FreeToPlay: FF.LandingFreeToPlay,
                        });
                    }
                });
            }
            if (Constants.SELECTED_GAMET == Constants.GameType.LiveFantasy) {
                import('../Component/LiveFantasy').then(LF => {
                    if (LF.LiveFantasyLobby) {
                        this.setState({
                            LiveFantasy: LF.LiveFantasyLobby,
                        });
                    }
                });
            }
            if (Constants.SELECTED_GAMET == Constants.GameType.LiveStockFantasy) {
                import('../Component/LiveStockFantasy').then(LSF => {
                    if (LSF.LSFLobby) {
                        this.setState({
                            LiveFantasy: LSF.LSFLobby,
                        });
                    }
                });
            }
            WSManager.setShareContestJoin(false);
        }
        if (this.state.getGameTypeSport) {
            let activeSport = ls.get('PFSSport') || ''
            if (Constants.SELECTED_GAMET == Constants.GameType.PickFantasy) {
                let tempArray = [];
                if(ls.get('PFSportList')){
                    tempArray = ls.get('PFSportList');
                    if (!Constants.PFSelectedSport) {
                        Constants.setValue.setPFSelectedSport(tempArray[0]);
                    }
                    this.setState({
                        AvaSports: tempArray,
                        // activeSportsTab: activeSport != '' ? activeSport.sports_id : tempArray[0].sports_id
                    })
                }
                // else{
                //     GetPickFantasySports().then((responseJson)=>{
                //         if (responseJson && responseJson.response_code == NC.successCode) {
                //             let data = responseJson.data;
                //             ls.set('PFSportList',data)
                //             if(!Constants.PFSelectedSport){
                //                 Constants.setValue.setPFSelectedSport(data[0]);
                //             }
                //             let url =window.location.href;
                //             if(window.location.pathname === "/lobby" && Constants.SELECTED_GAMET == Constants.GameType.PickFantasy && Constants.PFSelectedSport && !url.includes(Constants.PFSelectedSport.name)){
                //                 // this.props.history.replace("/lobby#" + Constants.PFSelectedSport.name + Utilities.getGameTypeHash())
                //                 this.props.history.replace(window.location.pathname + "#" + Utilities.getPFSelectedSportsForUrl() + Utilities.getGameTypeHash())
                //             }
                //             this.setState({
                //                 AvaSports: data,
                //                 // activeSportsTab: activeSport != '' ? activeSport.sports_id : data[0].sports_id
                //             })
                //         }
                //     })
                // }

            }
            else {
                var gametypeArray = this.state.getGameTypeSport;
                let tempArray = [];
                // let tempArray = Constants.SELECTED_GAMET == Constants.GameType.PickemTournament ? [{'label': 'featured','value': 0}] : [];
                // let option = Constants.SELECTED_GAMET == Constants.GameType.PickemTournament ? [{'label': 'featured','value': '0'}] : [];
                let option = [];
                for (var item of gametypeArray) {
                    if (item.game_key == this.state.selectedGameType) {
                        tempArray = item.allowed_sports || ''
                    }
                }
                if (tempArray != '') {
                    for (var obj of tempArray) {
                        var sportsId = '';
                        if (obj in Sports.url) {
                            sportsId = Sports.url[obj] + "";
                        }
                        option.push({
                            'label': sportsId,
                            'value': obj
                        })
                    }
                }
                this.setState({
                    AvaSports: option
                })
            }
        }

        if(Constants.SELECTED_GAMET == Constants.GameType.OpinionTradeFantasy){
            const sportsList = ls.get('OTSportList')
            const convertLV = (List) => {
                return _Map(List, o => {
                    return {
                        ...o,
                        label: o.sports_name,
                        value: o.sports_id
                    }
                })
            }
            if(sportsList && !_isEmpty(sportsList)) {
                this.setState({
                    AvaSports: sportsList,
                },  () => {
                    const { AvaSports } = this.state
                    Constants.setValue.setAppSelectedSport(AvaSports[0].value);
                })
            } else {

                otGetSportsList().then(({response_code, data}) => {
                    if (response_code == NC.successCode) {
                        ls.set('OTSportList', convertLV(data))
                        Constants.setValue.setOTSportList(convertLV(data) || [])
                        this.setState({
                            AvaSports: convertLV(data),
                        },  () => {
                            const { AvaSports } = this.state
                            Constants.setValue.setAppSelectedSport(AvaSports[0].value);
                        })
                    }
                })
            }
        }

        this.setState({ switchPosting: false });
    }

    UNSAFE_componentWillMount() {
        this.willmountCalling()
    }

    checkSportId = () => {
        let interval = setInterval(() => {
            if (Constants.SELECTED_GAMET == Constants.GameType.PickFantasy && Constants.PFSelectedSport != null) {
                clearInterval(interval)
                this.setState({ activeSportsTab: Constants.PFSelectedSport.sports_id })
            }
            if (Constants.SELECTED_GAMET != Constants.GameType.PickFantasy && Constants.AppSelectedSport != null) {
                clearInterval(interval)
                this.setState({ activeSportsTab: Constants.AppSelectedSport })
            }
        }, 100)
    }

    /**
     * @description will be called when user tap on filter icon on header
     * @return UI components
    */
    showFilterData = () => {
        this.setState({ showLobbyFitlers: true });
    }
    /**
     * @description will be called when user tap on filter  done icon 
     * @return UI components
    */
    hideFilterData = () => {
        this.setState({
            showLobbyFitlers: false
        });
    }

    onSelect = (tab, fromMount) => {
        ls.set("isULF", false)
        if (!fromMount) {
            this.setState({ urlParams: ''})
            if (tab === "lobby"){
                let url = ls.get('SHActive')  ? 
                            this.props.history.push('/sports-hub') : 
                            (
                                Constants.SELECTED_GAMET == Constants.GameType.StockFantasy || 
                                Constants.SELECTED_GAMET == Constants.GameType.StockFantasyEquity
                            ) ? 
                                ('/' + tab + Utilities.getGameTypeHash()) : 
                                (Constants.SELECTED_GAMET == Constants.GameType.PickFantasy ? 
                                ('/' + tab + "#" + Utilities.getPFSelectedSportsForUrl() + Utilities.getGameTypeHash()) :
                                ('/' + tab + "#" + Utilities.getSelectedSportsForUrl() + Utilities.getGameTypeHash()))
                this.props.history.push(url)
            }
            else if (tab === "my-contests") {
                ls.remove('SHActive')
                this.props.history.push('/' + tab + "?contest=upcoming")
            }
            // else if (tab === "home")
            //     this.props.history.push('/sports-hub')
            else
                this.props.history.push('/' + tab)
        }

        this.setHeaderOptions(tab);
    }

    setHeaderOptions(tab) {

        let { HeaderOption, sportsList, selectedGameType } = mContext.state;
        HeaderOption.title = '';
        HeaderOption.edit = false;
        HeaderOption.filter = false;
        HeaderOption.notification = false;
        HeaderOption.hideHeader = false;
        HeaderOption.close = false;
        HeaderOption.showGTTitle = '';
        HeaderOption.loginOpt = '';
        if (tab === 'my-contests') {
            HeaderOption.title = Constants.SELECTED_GAMET == Constants.GameType.DFS || Constants.SELECTED_GAMET == Constants.GameType.OpenPred || Constants.SELECTED_GAMET == Constants.GameType.OpenPredLead || Constants.SELECTED_GAMET == Constants.GameType.Pickem || Constants.SELECTED_GAMET == Constants.GameType.MultiGame || Constants.SELECTED_GAMET == Constants.GameType.StockFantasy || Constants.SELECTED_GAMET == Constants.GameType.PropsFantasy ? AppLabels.MY_CONTEST : '';
            HeaderOption.showGTTitle = Constants.SELECTED_GAMET == Constants.GameType.MultiGame ? AppLabels.MULTIGAME : Constants.SELECTED_GAMET == Constants.GameType.Pickem ? AppLabels.SPORTS_PICKEM : '';
            HeaderOption.notification = true;
            HeaderOption.hideShadow = true;
            HeaderOption.DFSPrimary = true;
            HeaderOption.filter = Constants.SELECTED_GAMET == Constants.GameType.OpenPred || Constants.SELECTED_GAMET == Constants.GameType.OpenPredLead ? true : false;
            HeaderOption.isPrimary = Constants.DARK_THEME_ENABLE ? false : true;

            HeaderOption.back = Constants.SELECTED_GAMET == Constants.GameType.PropsFantasy;
            HeaderOption.goBackLobby = Constants.SELECTED_GAMET == Constants.GameType.PropsFantasy
        }
        else if (tab === 'my-profile') {
            HeaderOption.edit = true;
            HeaderOption.hideShadow = true;
            HeaderOption.isPrimary = Constants.DARK_THEME_ENABLE ? false : true;
        }
        else if (tab === 'more') {
            HeaderOption.title = AppLabels.MORE;
            HeaderOption.hideShadow = true;
            HeaderOption.close = false;
            HeaderOption.isPrimary = Constants.DARK_THEME_ENABLE ? false : true;
        }
        else if (tab === 'lobby') {
          

            HeaderOption.filter = (Constants.SELECTED_GAMET == Constants.GameType.Pred || Constants.SELECTED_GAMET == Constants.GameType.OpenPred || Constants.SELECTED_GAMET == Constants.GameType.OpenPredLead || Constants.SELECTED_GAMET == Constants.GameType.StockFantasy || Constants.SELECTED_GAMET == Constants.GameType.StockFantasyEquity || Constants.SELECTED_GAMET == Constants.GameType.LiveFantasy || Constants.SELECTED_GAMET == Constants.GameType.PickemTournament || Constants.SELECTED_GAMET == Constants.GameType.PropsFantasy)
            ? false : true;
            HeaderOption.isPrimary = Constants.DARK_THEME_ENABLE ? false : true;
            // HeaderOption.isPrimary = (Constants.SELECTED_GAMET == Constants.GameType.Pred || Constants.SELECTED_GAMET == '1' || Constants.SELECTED_GAMET == Constants.GameType.OpenPred || Constants.SELECTED_GAMET == Constants.GameType.OpenPredLead || selectedGameType == Constants.GameType.Pickem || Constants.SELECTED_GAMET == Constants.GameType.Free2Play) ? true : false;
            HeaderOption.notification = true;
            HeaderOption.DFSPrimary = true;
            HeaderOption.hideShadow = sportsList.length > 1 ? true : false;
            HeaderOption.loginOpt = WSManager.loggedIn() ? false : true;
            if (Constants.SELECTED_GAMET == Constants.GameType.Pred) {
                HeaderOption.title = AppLabels.PREDICT_WIN_ONLY;
                HeaderOption.infoPredL = true;
            }
        }
        else if (tab === 'notification') {
            HeaderOption.title = AppLabels.NOTIFICATIONS;
            HeaderOption.filter = false;
            HeaderOption.notification = false;
            HeaderOption.hideShadow = false;
            HeaderOption.isPrimary = Constants.DARK_THEME_ENABLE ? false : true;
        }
        else if (tab === 'my-wallet') {
            HeaderOption.title = AppLabels.MY_WALLET;
            HeaderOption.filter = false;
            HeaderOption.notification = false;
            HeaderOption.hideShadow = false;
            HeaderOption.isPrimary = Constants.DARK_THEME_ENABLE ? false : true;
        }
        else if (tab === 'refer-friend') {
            HeaderOption.title = AppLabels.REFER_A_FRIEND;
            HeaderOption.filter = false;
            HeaderOption.notification = false;
            HeaderOption.hideShadow = false;
            HeaderOption.isPrimary = Constants.DARK_THEME_ENABLE ? false : true;
        }
        else if (tab === 'earn-coins') {
            HeaderOption.title = AppLabels.HOW_TO_EARN;
            HeaderOption.filter = false;
            HeaderOption.notification = true;
            HeaderOption.hideShadow = true;
            HeaderOption.isPrimary = Constants.DARK_THEME_ENABLE ? false : true;
        }
        else if (tab === 'leaderboard') {
            HeaderOption.title = Constants.SELECTED_GAMET == Constants.GameType.Free2Play ? AppLabels.F2P_LEAGUES : Constants.SELECTED_GAMET == Constants.GameType.Pickem ? AppLabels.PICKEM + ' ' + AppLabels.LEADERBOARD : AppLabels.LEADERBOARD;
            HeaderOption.filter = Constants.SELECTED_GAMET == Constants.GameType.Free2Play || Constants.SELECTED_GAMET == Constants.GameType.Pickem || Constants.SELECTED_GAMET == Constants.GameType.DFS ? false : true;
            HeaderOption.notification = false;
            HeaderOption.hideShadow = true;
            HeaderOption.isPrimary = Constants.DARK_THEME_ENABLE ? false : true;
            HeaderOption.FPPLeaderboard = Constants.SELECTED_GAMET == Constants.GameType.OpenPredLead || Constants.SELECTED_GAMET == Constants.GameType.Pickem ? true : false;
            HeaderOption.showLBal = Constants.SELECTED_GAMET == Constants.GameType.OpenPredLead ? true : Constants.SELECTED_GAMET == Constants.GameType.Pickem ? true : false;

        }
        this.checkPickedGame();
        mContext.setState({ HeaderOption, activeTab: tab })
    }

    componentDidUpdate() {
        let tempTab = Constants.DASHBOARD_FOOTER.config.tab_path[window.location.pathname];
        if (this.state.activeTab !== tempTab || this.state.CurrentGameType != Constants.SELECTED_GAMET)
            this.setState({ activeTab: tempTab, CurrentGameType: Constants.SELECTED_GAMET }, () => {
                this.setHeaderOptions(tempTab)
            })
    }
    onTabClick = _debounce((item, index) => {
        this.setState({
            isSportIdListener: new Date().valueOf()
        });
        if (Constants.SELECTED_GAMET == Constants.GameType.PickFantasy) {
            ls.set('PFSSport', item);
            CustomHeader.changeFilter(false, '')
            Constants.setValue.setPFSelectedSport(item);
            this.setState({ activeSportsTab: item.sports_id }, () => {
                if (window.location.pathname === "/lobby") {
                    this.props.history.replace(window.location.pathname + "#" + Utilities.getPFSelectedSportsForUrl() + Utilities.getGameTypeHash())
                    // this.props.history.replace(window.location.pathname + "#" + item.name.toLowerCase() + Utilities.getGameTypeHash())
                }
            });
        }
        else {
            ls.set('selectedSports', item.value);
            CustomHeader.changeFilter(false, '')
            Constants.setValue.setAppSelectedSport(item.value);
            this.setState({ activeSportsTab: item.value }, () => {
                if (window.location.pathname === "/lobby") {
                    this.props.history.replace(window.location.pathname + "#" + Utilities.getSelectedSportsForUrl() + Utilities.getGameTypeHash())
                }
            });
        }
    }, 200)
    
    returnLobbyType = () => {
        var LobbyType = Lobby;
        if (Constants.SELECTED_GAMET == Constants.GameType.DFS) {
            LobbyType = DMLobby; //Utilities.getMasterData().dfs_multi == 1 ? DMLobby : Lobby;
        }
        if (Constants.SELECTED_GAMET == Constants.GameType.Pred) {
            LobbyType = PredictionLobby;
        }
        if (Constants.SELECTED_GAMET == Constants.GameType.MultiGame) {
            LobbyType = this.state.MultiGame;
        }
        if (Constants.SELECTED_GAMET == Constants.GameType.OpenPred) {
            LobbyType = OpenPredictorLobby;
        }
        if (Constants.SELECTED_GAMET == Constants.GameType.Free2Play) {
            LobbyType = this.state.FreeToPlay;
        }
        if (Constants.SELECTED_GAMET == Constants.GameType.OpenPredLead) {
            LobbyType = OpenPredictorFPPLobby;
        }
        if (Constants.SELECTED_GAMET == Constants.GameType.StockFantasy) {
            LobbyType = StockLobby;
        }
        if (Constants.SELECTED_GAMET == Constants.GameType.StockFantasyEquity) {
            LobbyType = StockLobbyEquity;
        }
        if (Constants.SELECTED_GAMET == Constants.GameType.LiveFantasy) {
            LobbyType = LiveFantasyLobby;
        }

        if (Constants.SELECTED_GAMET == Constants.GameType.StockPredict) {
            LobbyType = SPLobby;
        }
        if (Constants.SELECTED_GAMET == Constants.GameType.LiveStockFantasy) {
            LobbyType = LSFLobby;
        }
        if (Constants.SELECTED_GAMET == Constants.GameType.PickFantasy) {
            LobbyType = PFLobby;
        }
        if (Constants.SELECTED_GAMET == Constants.GameType.PickemTournament) {
            LobbyType = PTLobby;
        }
        if (Constants.SELECTED_GAMET == Constants.GameType.PropsFantasy) {
            LobbyType = PropsLobby;
        }
        if (Constants.SELECTED_GAMET == Constants.GameType.OpinionTradeFantasy) {
            LobbyType = OpinionLobby;
        }
        return LobbyType;
    }
    returnMyContestType = () => {
        var MyContestType = DMMyContest;
        // var MyContestType = Utilities.getMasterData().dfs_multi == 1 ? DMMyContest : MyContest;

        if (Constants.SELECTED_GAMET == Constants.GameType.Pred) {
            MyContestType = MyPrediction;
        }
        if (Constants.SELECTED_GAMET == Constants.GameType.OpenPred) {
            MyContestType = MyOpenPredictors;
        }
        if (Constants.SELECTED_GAMET == Constants.GameType.OpenPredLead) {
            MyContestType = MyFPPOpenPredictors;
        }
        if (Constants.SELECTED_GAMET == Constants.GameType.StockFantasy) {
            MyContestType = StockMyContest;
        }
        if (Constants.SELECTED_GAMET == Constants.GameType.StockFantasyEquity) {
            MyContestType = StockMyContest;
        }
        if (Constants.SELECTED_GAMET == Constants.GameType.LiveFantasy) {
            MyContestType = LFMycontest;
        }
        if (Constants.SELECTED_GAMET == Constants.GameType.StockPredict) {
            MyContestType = SPMyContest;
        }
        if (Constants.SELECTED_GAMET == Constants.GameType.LiveStockFantasy) {
            MyContestType = LSFMyContest;
        }
        if (Constants.SELECTED_GAMET == Constants.GameType.PickFantasy) {
            MyContestType = PFMyContest;
        }
        if (Constants.SELECTED_GAMET == Constants.GameType.PickemTournament) {
            MyContestType = PTMyContest;
        }
        if (Constants.SELECTED_GAMET == Constants.GameType.PropsFantasy) {
            MyContestType = PropsMyContest;
        }
        if (Constants.SELECTED_GAMET == Constants.GameType.OpinionTradeFantasy) {
            MyContestType = OpinionMyContest;
         }
        return MyContestType;
    }
    returnLeaderBoardType = () => {
        var LeaderBoardType = FantasyRefLeaderboard;
        // if (Constants.SELECTED_GAMET == Constants.GameType.DFS) {
        //     LeaderBoardType = FantasyRefLeaderboard;
        // }
        if (Constants.SELECTED_GAMET == Constants.GameType.Free2Play) {
            LeaderBoardType = LeagueLeaderBoard;
        }
        // if (Constants.SELECTED_GAMET == Constants.GameType.Pickem) {
        //     LeaderBoardType = PickemLeaderboard;
        // }
        if (Constants.SELECTED_GAMET == Constants.GameType.OpenPredLead) {
            LeaderBoardType = FPPOpenPredictionLeaderboard;
        }
        if (Constants.SELECTED_GAMET == Constants.GameType.StockFantasy || Constants.GameType.StockFantasyEquity) {
            LeaderBoardType = FantasyRefLeaderboard;
        }

        return LeaderBoardType;
    }

    renderTabById(tabItem, activeTab) {
        let LobbyType = this.returnLobbyType();
        let MyContestType = this.returnMyContestType();
        let LeaderBoardType = this.returnLeaderBoardType();
        if (tabItem.tab_key === 'lobby') {
            return <Tab key={tabItem.tab_key} eventKey={tabItem.tab_key} title={<div className="animated-tab-div"> <div><i className="icon-home" /></div>
                <div className="tab-label">{AppLabels.HOME}</div>
            </div>}>
                {activeTab === tabItem.tab_key &&
                    <LobbyType showLobbyFitlers={this.state.showLobbyFitlers} hideFilterData={this.hideFilterData} {...this.props} selectedSport={this.state.activeSportsTab} AvaSports={this.state.AvaSports} isSportIdListener={this.state.isSportIdListener}/>
                }
            </Tab>
        }
        else if (tabItem.tab_key === 'feed') {
            return <Tab key={tabItem.tab_key} eventKey={tabItem.tab_key} title={<div className="animated-tab-div"><div><i className="icon-fs-social" /></div><div className="tab-label">{"Feeds"}</div> </div>}>
                {activeTab === tabItem.tab_key && <Feed {...this.props} showLobbyFitlers={this.state.showLobbyFitlers} hideFilter={this.hideFilterData} selectedSport={this.state.activeSportsTab} />}
            </Tab>
        }
        else if (tabItem.tab_key === 'my-contests') {
            return <Tab key={tabItem.tab_key} eventKey={tabItem.tab_key} disabled={ls.get('SHActive') ? true : false} title={<div className="animated-tab-div"><div><i className="icon-my-contests" /></div><div className="tab-label">{AppLabels.MY_CONTEST}</div> </div>}>
                {activeTab === tabItem.tab_key && <MyContestType {...this.props} hideHeader={true} urlParams={this.state.urlParams} selectedSport={this.state.activeSportsTab} showLobbyFitlers={this.state.showLobbyFitlers} hideFilter={this.hideFilterData} />}
            </Tab>
        }
        else if (tabItem.tab_key === 'my-profile') {
            return <Tab key={tabItem.tab_key} eventKey={tabItem.tab_key} title={<div className="animated-tab-div"><div><i className="icon-profile" /></div><div className="tab-label">{AppLabels.PROFILE}</div> </div>}>
                {activeTab === tabItem.tab_key && <Profile hideHeader={true} urlParams={this.state.urlParams} {...this.props} selectedSport={this.state.activeSportsTab} />}
            </Tab>
        }
        else if (tabItem.tab_key === 'leaderboard') {
            // if((Utilities.getMasterData().leaderboard && Utilities.getMasterData().leaderboard.length > 0) || (Utilities.getMasterData().a_dfst == 1) || Utilities.getMasterData().a_pickem_tournament == 1) {
            if((Utilities.getMasterData().leaderboard && Utilities.getMasterData().leaderboard.length > 0) || (Constants.SELECTED_GAMET == Constants.GameType.DFS && Utilities.getMasterData().a_dfst == 1) || Constants.SELECTED_GAMET == Constants.GameType.PickemTournament) {
                return <Tab key={tabItem.tab_key} eventKey={tabItem.tab_key} title={<div className="animated-tab-div"><div><i className="icon-leaderboard" /></div><div className="tab-label">{AppLabels.LEADERBOARD}</div> </div>}>
                    {activeTab === tabItem.tab_key && 
                    <>
                        {
                            // ((IsGameTypeEnabled(Constants.GameType.DFS) && Utilities.getMasterData().a_dfst == 1) ||  Utilities.getMasterData().a_pickem_tournament == 1)
                            ((Constants.SELECTED_GAMET == Constants.GameType.DFS && Utilities.getMasterData().a_dfst == 1) || Constants.SELECTED_GAMET == Constants.GameType.PickemTournament)
                            ?
                            <TLeaderboard {...this.props} showLobbyFitlers={this.state.showLobbyFitlers} hideFilter={this.hideFilterData} selectedSport={this.state.activeSportsTab} />
                            :
                            <LeaderBoardType {...this.props} showLobbyFitlers={this.state.showLobbyFitlers} hideFilter={this.hideFilterData} selectedSport={this.state.activeSportsTab} />
                        }
                    </>
                    }
                </Tab>
            }
            else{
                return <Tab key={tabItem.tab_key} eventKey={tabItem.tab_key} title={<div className="animated-tab-div"><div><i className="icon-profile" /></div><div className="tab-label">{AppLabels.PROFILE}</div> </div>}>
                    {activeTab === tabItem.tab_key && <Profile hideHeader={true} urlParams={this.state.urlParams} {...this.props} selectedSport={this.state.activeSportsTab} />}
                </Tab>
            }
        }
        else if (tabItem.tab_key === 'tour-leaderboard') {
            return <Tab key={tabItem.tab_key} eventKey={tabItem.tab_key} title={<div className="animated-tab-div"><div><i className="icon-leaderboard" /></div><div className="tab-label">{AppLabels.LEADERBOARD}</div> </div>}>
                {activeTab === tabItem.tab_key && <TLeaderboard {...this.props} showLobbyFitlers={this.state.showLobbyFitlers} hideFilter={this.hideFilterData} selectedSport={this.state.activeSportsTab} />}
            </Tab>
        }
        else if (tabItem.tab_key === 'more') {
            return <Tab key={tabItem.tab_key} eventKey={tabItem.tab_key} title={<div className="animated-tab-div"><div><i className="icon-more-large" /></div><div className="tab-label">{AppLabels.MORE} </div></div>}>
                {activeTab === tabItem.tab_key && <More {...this.props} onLanguageChange={this.setHeaderOptions} />}
            </Tab>
        }
        else if (tabItem.tab_key === 'notification') {
            return <Tab key={tabItem.tab_key} eventKey={tabItem.tab_key} title={<div className="animated-tab-div"><div><i className="icon-alarm-new" /></div><div className="tab-label">{AppLabels.NOTIFICATIONS} </div></div>}>
                {activeTab === tabItem.tab_key && <Notification {...this.props} hideHeader={true} />}
            </Tab>
        }
        else if (tabItem.tab_key === 'my-wallet') {
            return <Tab key={tabItem.tab_key} eventKey={tabItem.tab_key} title={<div className="animated-tab-div"><div><i className="ic icon-wallet-ic" /></div><div className="tab-label">{AppLabels.MY_WALLET}</div> </div>}>
                {activeTab === tabItem.tab_key && <Wallet {...this.props} hideHeader={true} />}
            </Tab>
        }
        // else if (tabItem.tab_key === 'refer-friend') {
        //     return <Tab key={tabItem.tab_key} eventKey={tabItem.tab_key} title={<div className="animated-tab-div"><div><i className="ic icon-add-user" /></div><div className="tab-label">{AppLabels.REFER_A_FRIEND}</div> </div>}>
        //         {activeTab === tabItem.tab_key && <ReferFriend {...this.props} hideHeader={true} />}
        //     </Tab>
        // }
        else if (tabItem.tab_key === 'refer-friend') {
            return <Tab key={tabItem.tab_key} eventKey={tabItem.tab_key} title={<div className="animated-tab-div"><div><i className="ic icon-add-user" /></div><div className="tab-label">{AppLabels.REFER_A_FRIEND}</div> </div>}>
                {activeTab === tabItem.tab_key && <ReferFriendStandard {...this.props} hideHeader={true} />}
            </Tab>
        }
        else if ((Utilities.getMasterData().sports_hub) && tabItem.tab_key === 'earn-coins') {
            return <Tab key={tabItem.tab_key} eventKey={tabItem.tab_key} title={
                <div className="isCoin coin-shine">
                    <div className="shadow-v" />
                    <span className="coins-tab-label">{AppLabels.EARN_COINS}</span>
                    <span className={activeTab === tabItem.tab_key ? "fcoin" : 'position-relative'}>
                        {/* <img src={Images.EARN_COINS} alt="" /> */}
                        <img src={
                            // activeTab === tabItem.tab_key ? 
                            Images.EARN_COINS 
                            // : Images.SILVR_EARN_COINS
                            } alt="" />
                        {
                            activeTab === tabItem.tab_key && <React.Fragment>
                                <div className="spark1">✦</div>
                                <div className="spark2">✦</div>
                                <div className="spark3">✦</div>
                            </React.Fragment>
                        }
                    </span>
                </div>
            } >
                {activeTab === tabItem.tab_key && <this.state.EarnCoins {...this.props} hideHeader={true} showDailyStreak={CustomHeader.showDailyStreak} />}
            </Tab>
        }
        else if (tabItem.tab_key === 'sports-hub') {
            // if (!this.state.showNaviBtnSec) {
            //     this.setState({
            //         showNaviBtnSec: true
            //     })
            // }
            let spImg = Utilities.getMasterData().hub_icon;
            return <Tab key={tabItem.tab_key} eventKey={tabItem.tab_key} title={
                <>
                    {
                        Utilities.getMasterData().sports_hub.length > 2 &&
                        <div className="isCoin coin-shine">
                            <div className="shadow-v logo-hub" />
                            {
                                // ((Utilities.getMasterData().a_coin != "1") || Constants.IS_SPORTS_HUB && !WSManager.loggedIn()) &&
                                <span className={"position-relative fcoin" + (spImg ? " upload-img" : "")}>
                                    <img src={spImg ? Utilities.getSettingURL(spImg) : Images.DT_SPORTS_HUB} alt="" />
                                    <React.Fragment>
                                        <div className="spark1">✦</div>
                                        <div className="spark2">✦</div>
                                        <div className="spark3">✦</div>
                                    </React.Fragment>
                                </span>
                            }
                        </div>
                    }
                </>
            } />
        }
    }

    renderHelmetData = (ATab) => {
        let isLobby = ATab === 'lobby';
        let isMyContast = ATab === 'my-contests';
        let isCoins = ATab === 'earn-coins';
        let isProfile = ATab === 'my-profile';
        let isLeaderboard = ATab === 'leaderboard';
        let isMore = ATab === 'more';
        var data = '';

        if (isLobby) {
            data = MetaData.lobby;
        } else if (isMyContast) {
            data = MetaData.contests;
        } else if (isCoins) {
            data = MetaData.ERNC;
        } else if (isProfile) {
            data = MetaData.myprofile;
        } else if (isMore) {
            data = MetaData.more;
        } else if (isLeaderboard) {
            data = MetaData.leaderboard;
        }


        return (
            <Helmet titleTemplate={`${MetaData.template} | %s`}>
                <title>{data.title}</title>
                <meta name="description" content={data.description} />
                <meta name="keywords" content={data.keywords}></meta>
            </Helmet>
        )
    }

    renderTopSportsTab = () => {
        let { sportsList, AvaSports, activeSportsTab, activeTab } = this.state;
        let isSPrediction = Constants.SELECTED_GAMET == Constants.GameType.Pred;
        let isLobby = activeTab === 'lobby';
        var activeSTIDx = 0;
        var settings = {
            touchThreshold: 10,
            infinite: false,
            slidesToScroll: 1,
            // slidesToShow: AvaSports.length > 3 ? 4 : 3,
            slidesToShow: AvaSports.length > 4 ? 5 : AvaSports.length >3 ? 4:AvaSports.length >2 ?3 :2,
            variableWidth: false,
            initialSlide: 0,
            dots: false,
            autoplay: false,
            centerMode: false,
            responsive: [
                {
                    breakpoint: 767,
                    settings: {
                        slidesToShow: AvaSports.length > 4 ? 5 : AvaSports.length >3 ? 4:AvaSports.length >2 ?3 :2,
                        variableWidth: true,
                        // className: "center",
                        // centerMode: AvaSports.length > 2 ? true : false,
                        // centerPadding: AvaSports.length == 3 ? '0' : '30px 0 10px',
                        // initialSlide: AvaSports.length > 2 ? 1 : 0,
                        // infinite: true,
                        initialSlide: 0
                    }
                },
                {
                    breakpoint: 500,
                    settings: {
                        variableWidth: true,
                        slidesToShow: AvaSports.length > 4? 5.5 : AvaSports.length >3 ? 4:AvaSports.length >2 ?3 :2,
                        // className: "center",
                        className: "left",
                        // centerMode: AvaSports.length > 2 ? true : false,
                        // centerPadding: AvaSports.length == 3 ? '0' : '30px 0 10px',
                        // initialSlide: AvaSports.length > 2 ? 1 : 0,
                        // infinite: true,
                        initialSlide: 0
                    }
                },
                {
                    breakpoint: 360,
                    settings: {
                        variableWidth: true,
                        slidesToShow: AvaSports.length == 5 ? 5 : AvaSports.length == 4 ? 4 :AvaSports.length == 3 ?3 :2 ,
                        className: "left",
                        // centerMode: AvaSports.length > 2 ? true : false,
                        // centerPadding: AvaSports.length == 3 ? '0' : '30px 0 10px',
                        // infinite: true,
                        initialSlide:  0,
                    }

                }
            ]
        };

        return (
            <Tab.Container id='top-sports-slider' onSelect={() => console.log('')}
                activeKey={activeSportsTab.toString()} defaultActiveKey={activeSportsTab.toString()}
                className={((Constants.SELECTED_GAMET == Constants.GameType.OpenPred) || (!isLobby && Constants.SELECTED_GAMET == Constants.GameType.OpinionTradeFantasy)) ? 'hide' : ''}>
                <Row className="clearfix">
                    <Col className={Constants.SELECTED_GAMET == Constants.GameType.PickFantasy? 'bgc-clr sports-tab-nav sports-tab-slider' : 'bgc-clr sports-tab-nav sports-tab-slider' }  xs={12}>

                        {
                            Constants.SELECTED_GAMET == Constants.GameType.PickFantasy ?
                                <Nav>
                                    <Suspense fallback={<div />} >
                                        {/* <ReactSlickSlider settings={settings}>
                                        {
                                            _Map(AvaSports, (item, idx) => {
                                                if (item.sports_id == activeSportsTab) {
                                                    activeSTIDx = idx;
                                                }
                                                return (
                                                    <NavItem key={item.sports_id} style={{ width: 'calc(100% / ' + AvaSports.length + ')' }}
                                                        onClick={() => this.onTabClick(item, idx)} eventKey={item.sports_id}
                                                        className={(item.sports_id == activeSportsTab ? ' active ' : '')}>
                                                        <span>
                                                            {item.name}
                                                        </span>
                                                    </NavItem>

                                                )
                                            })
                                        }
                                    </ReactSlickSlider> */}
                                    <SportsNavigation
                                            tabsContainerClassName="sports-tab-container"
                                            list={AvaSports}
                                            selected={0}
                                        >
                                            {({ Tab }) => {
                                                return (
                                                    _Map(AvaSports, (item, idx) => {
                                                        if (item.sports_id == activeSportsTab) {
                                                            activeSTIDx = idx;
                                                        }
                                                        return (
                                                            <Tab {...{
                                                                className: `sports-tab-item`,
                                                                key: idx,
                                                                onClick: () => {}
                                                            }}>
                                                            <NavItem key={item.sports_id} style={{ width: 'calc(100% / ' + AvaSports.length + ')' }}
                                                                onClick={() => this.onTabClick(item, idx)} eventKey={item.sports_id}
                                                                className={(item.sports_id == activeSportsTab ? ' active ' : '')}>
                                                                <span>
                                                                    {item.name}
                                                                </span>
                                                            </NavItem>
                                                            </Tab>
        
                                                        )
                                                    })

                                                )
                                            }}
                                        </SportsNavigation>
                                    </Suspense>
                                </Nav>
                                :
                                <Nav className='p-0'>
                                    <Suspense fallback={<div />} >

                                        <SportsNavigation
                                            tabsContainerClassName="sports-tab-container"
                                            list={AvaSports}
                                            selected={0}
                                        >
                                            {({ Tab }) => {
                                                return (
                                                    _Map(AvaSports, (item, idx) => {
                                                        return (
                                                            <Tab {...{
                                                                className: `sports-tab-item`,
                                                                key: idx,
                                                                onClick: (e) => console.log('')
                                                            }}>
                                                                <NavItem key={item.value} style={{ width: 'calc(100% / ' + AvaSports.length + ')' }} onClick={() => this.onTabClick(item, idx)} eventKey={item.value.toString()} className={(item.value == activeSportsTab ? ' active ' : '') + (idx === 0 && isSPrediction && isLobby ? ' first-sport' : '') + ((idx === AvaSports.length - 1) && isSPrediction && isLobby ? ' last-sport' : '')}>
                                                                    <span>{Sports.url[item.value + (WSManager.getAppLang() || '')]}</span>
                                                                </NavItem>
                                                            </Tab>
                                                        )
                                                    })
                                                )
                                            }}
                                        </SportsNavigation>
                                    </Suspense>
                                </Nav>
                        }
                    </Col>
                </Row>
            </Tab.Container>
        )
    }

    checkPickedGame = () => {
        let returnValue = false;
        if (!Constants.SELECTED_GAMET && Constants.IS_SPORTS_HUB) {
            returnValue = true;
            if (Utilities.getMasterData().sports_hub.length == 2) {
                let SGtype = Utilities.getMasterData().sports_hub[0]
                this.selectGameType(SGtype)
            }
            else {
                this.props.history.push("/sports-hub#" + Utilities.getSelectedSportsForUrl())
            }
        }
        return returnValue;
    }

    renderIconValue = (gk) => {
        let { sports_hub } = Utilities.getMasterData();
        let sb = sports_hub.filter((obj) => obj.game_key != gk)
        return sb[0]
    }



    selectGameType = (item) => {
        let { showNaviStrip } = this.state;
        this.setState({ showNaviStrip: !showNaviStrip, switchPosting: true })
        Utilities.gtmEventFire('button_click', {
            button_name: item.en_t
        })
        ls.set('SHActive', false)
        Utilities.handleAppBackManage('game-type')
        let sport = ls.get('selectedSports');
        let allowedSport = item.allowed_sports || '';
        if (item.game_key == Constants.GameType.StockFantasy) {
            Utilities.scrollToTop()
            WSManager.setPickedGameType(item.game_key);
            this.props.history.push("/lobby" + Utilities.getGameTypeHash())
            this.willmountCalling()
        }
        else if (item.game_key == Constants.GameType.StockPredict) {
            Utilities.scrollToTop()
            WSManager.setPickedGameType(item.game_key);
            this.props.history.push("/lobby" + Utilities.getGameTypeHash())
            this.willmountCalling()
        }
        else if (item.game_key == Constants.GameType.PickFantasy) {
            let SelSport = ls.get('PFSSport');
            let SportsList = ls.get('PFSportList')
            // if(SelSport && SportsList.includes(SelSport.sports_id)){
            if (SelSport && (SportsList && SportsList.some(SL => SL.sports_id === SelSport.sports_id))) {
                
                Utilities.scrollToTop()
                if (!Constants.SELECTED_GAMET) {
                    setTimeout(() => {
                        CustomHeader.showSHSCM();
                    }, 100);
                }
                WSManager.setPickedGameType(item.game_key);
                WSManager.setPickedGameTypeID(item.sports_hub_id);
                this.props.history.push("/lobby#")
                this.willmountCalling()
                // this.props.history.push("/lobby#" + Utilities.getPFSelectedSportsForUrl(SelSport.sports_id))
            }
            else {
                // ls.set('PFSSport', SportsList[0]);
                Utilities.scrollToTop()
                if (!Constants.SELECTED_GAMET) {
                    setTimeout(() => {
                        CustomHeader.showSHSCM();
                    }, 100);
                }
                WSManager.setPickedGameType(item.game_key);
                WSManager.setPickedGameTypeID(item.sports_hub_id);
                this.props.history.push("/lobby#")
                this.willmountCalling()
            }
        }
        else if ((allowedSport == '') || (allowedSport.length > 0 && allowedSport.includes(sport))) {
            Utilities.scrollToTop()
            if (!Constants.SELECTED_GAMET) {
                setTimeout(() => {
                    CustomHeader.showSHSCM();
                }, 100);
            }
            WSManager.setPickedGameType(item.game_key);
            WSManager.setPickedGameTypeID(item.sports_hub_id);
            if (item.game_key == Constants.GameType.PickemTournament || item.game_key == Constants.GameType.OpinionTradeFantasy) {
                this.props.history.push("/lobby#" + Utilities.getSelectedSportsForUrl() + Utilities.getGameTypeHash())
                this.willmountCalling()
            }
            else {
                this.props.history.push("/lobby#" + Utilities.getSelectedSportsForUrl())
                this.willmountCalling()
            }
        }
        else {
            let FSport = allowedSport[0];
            ls.set('selectedSports', FSport);
            Constants.setValue.setAppSelectedSport(FSport);
            this.setState({ ACSPORTTAB: FSport });
            Utilities.scrollToTop()
            if (!Constants.SELECTED_GAMET) {
                setTimeout(() => {
                    CustomHeader.showSHSCM();
                }, 100);
            }

            WSManager.setPickedGameType(item.game_key);
            WSManager.setPickedGameTypeID(item.sports_hub_id);
            if (item.game_key == Constants.GameType.PickemTournament || item.game_key == Constants.GameType.OpinionTradeFantasy) {
                this.props.history.push("/lobby#" + Utilities.getSelectedSportsForUrl() + Utilities.getGameTypeHash())
                this.willmountCalling()
            }
            else {
                this.props.history.push("/lobby#" + Utilities.getSelectedSportsForUrl())
                this.willmountCalling()
            }
        }
    }

    render() {
        let { sportsList, activeTab, getGameTypeSport, AvaSports, showNaviBtnSec, showNaviStrip, switchPosting } = this.state;
        mContext = this;

        let isLobby = activeTab === 'lobby';
        let isMyContast = activeTab === 'my-contests';
        let isCoins = activeTab === 'earn-coins';
        let isProfile = activeTab === 'my-profile';
        let isMore = activeTab === 'more';
        let isLeaderboard = activeTab === 'leaderboard';

        var showLobbySportsTab = process.env.REACT_APP_LOBBY_SPORTS_ENABLE == 1 ? true : (Constants.IS_SPORTS_HUB ? false : true)
        let spImg = Utilities.getMasterData().hub_icon;
        return (

            <MyContext.Provider value={this}>
                <div className={"web-container transparent-header lobby-web-container web-container-fixed " +
                    (
                        (Constants.SELECTED_GAMET == Constants.GameType.Pred && isLobby) ? " sport-pred-lobby " : '') + 
                        (sportsList.length > 1 ? ' ' : 'web-container-no-fixed') + 
                        (isProfile ? 'white-bg profile-web-container ' : '') + 
                        (isMore ? ' more-web-container' : '') + 
                        (isLeaderboard && 
                            (Constants.SELECTED_GAMET == Constants.GameType.OpenPredLead || Constants.SELECTED_GAMET == Constants.GameType.Free2Play ? 
                                ' header-with-img' : 
                                ( ((Constants.SELECTED_GAMET == Constants.GameType.DFS && Utilities.getMasterData().a_dfst == 1) || 
                                (Constants.SELECTED_GAMET == Constants.GameType.PickemTournament && Utilities.getMasterData().a_pickem_tournament == 1))  ? ' with-tour-lead' :' new-leaderboard')) + 
                        (Constants.SELECTED_GAMET == Constants.GameType.Pickem ? ' pickem-leaderboard-wrap' : '')
                    )
                }>

                    {this.renderHelmetData(activeTab)}

                    {!isProfile &&
                        <CustomHeader {...this.props} HeaderOption={this.state.HeaderOption} showLobbyFitlers={this.showFilterData} getGameTypeSport={getGameTypeSport} />
                    }
                    {(showNaviBtnSec && Utilities.getMasterData().a_coin == "1" && Constants.IS_SPORTS_HUB && WSManager.loggedIn() && Utilities.getMasterData().sports_hub.length > 2) &&
                        <ul ref={this.MenuRef} className="nav-btn-sec">
                            <a className={`menu-button ${showNaviStrip ? ' menu-btn-open' : ''}`} href title="Show navigation" onClick={() => this.setState({ showNaviStrip: !showNaviStrip })}>
                                {
                                    !showNaviStrip ?
                                        <>
                                            <span className="front">  <img src={spImg ? Utilities.getSettingURL(spImg) : Images.DT_SPORTS_HUB} alt="" /></span>
                                            <span className="back">
                                                <img src={Images.EARN_COINS} alt="" />
                                                <span className="position-relative">
                                                    <span className="coins-tab-label">{AppLabels.EARN_COINS}</span>
                                                </span>
                                            </span>
                                        </>
                                        :
                                        <i className="icon-cross-circular"></i>
                                }
                            </a>

                            <li className={`menu-item ${showNaviStrip ? ' shw-ani1' : ''}`} >
                                <a href onClick={() => this.props.history.replace('/sports-hub')}>
                                    <img src={spImg ? Utilities.getSettingURL(spImg) : Images.DT_SPORTS_HUB} alt="" />
                                </a>
                            </li>
                            <li className={`menu-item ${showNaviStrip ? ' shw-ani2' : ''}`} >
                                <a href onClick={() => this.props.history.replace('/earn-coins')}>
                                    <img src={Images.EARN_COINS} alt="" />
                                    <span className="position-relative">
                                        <span className="coins-tab-label">{AppLabels.EARN_COINS}</span>
                                    </span>
                                </a>
                            </li>
                        </ul>
                    }
                    {Utilities.getMasterData().sports_hub.length == 2 &&
                        <>
                            {
                                // Utilities.getMasterData().a_coin == "1" ?
                                //     <ul ref={this.MenuRef} className="nav-btn-sec switch-nav-wrap">
                                //         <a className={`menu-button ${showNaviStrip ? ' menu-btn-open' : ''}`} href title="Show navigation" onClick={() => this.setState({ showNaviStrip: !showNaviStrip })}>
                                //             {
                                //                 !showNaviStrip ?
                                //                     <>
                                //                         <span className="front">
                                //                             <img src={Images[this.renderIconValue(Constants.SELECTED_GAMET).game_key]} alt="" className='single-sport-icon' />
                                //                         </span>
                                //                         <span className="back">
                                //                             <img src={Images.earn_coin} alt="" className='single-sport-icon' />
                                //                         </span>
                                //                     </>
                                //                     :
                                //                     <i className="icon-cross-circular"></i>
                                //             }
                                //         </a>

                                //         <li className={`menu-item switch-menu-item ${showNaviStrip ? ' shw-ani1' : ''}`} >
                                //             <a href onClick={() => this.selectGameType(this.renderIconValue(Constants.SELECTED_GAMET))}>
                                //                 <img src={Images[this.renderIconValue(Constants.SELECTED_GAMET).game_key]} alt="" className='single-sport-icon' />
                                //             </a>
                                //         </li>
                                //         <li className={`menu-item switch-menu-item ${showNaviStrip ? ' shw-ani2' : ''}`} >
                                //             <a href onClick={() => this.props.history.replace('/earn-coins')}>
                                //                 <img src={Images.earn_coin} alt="" className='single-sport-icon' />
                                //                 {/* <span className="position-relative">
                                //                     <span className="coins-tab-label">{AppLabels.EARN_COINS}</span>
                                //                 </span> */}
                                //             </a>
                                //         </li>
                                //     </ul>
                                //     :
                                <ul className="nav-btn-sec switch-nav-wrap">
                                    <a className={`menu-button `} href title="Show navigation"
                                        onClick={() => this.selectGameType(this.renderIconValue(Constants.SELECTED_GAMET))}
                                    >
                                        {
                                            !switchPosting &&
                                            <img src={Images[this.renderIconValue(Constants.SELECTED_GAMET).game_key]} alt="" className='single-sport-icon ani' />
                                        }
                                    </a>
                                </ul>
                            }
                        </>
                    }





                    <div className={
                        "dashboard-container" + (isProfile ? ' without-header ' : (
                            isMore || isCoins || sportsList.length < 2 ||
                            (!showLobbySportsTab || AvaSports.length == 0 || AvaSports.length <= 1) || (Constants.SELECTED_GAMET == Constants.GameType.OpenPred) || (Constants.SELECTED_GAMET == Constants.GameType.LiveFantasy)
                            // (!showLobbySportsTab || Constants.SELECTED_GAMET != Constants.GameType.DFS)
                        ) ? (Constants.SELECTED_GAMET != Constants.GameType.DFS && this.state.activeTab == 'leaderboard' && Utilities.getMasterData().allow_social != 1) ? '' : ' without-sports-tab' : '')
                        // + 
                        // ((Constants.SELECTED_GAMET == Constants.GameType.Pickem && isLeaderboard) ? ' without-sports-tab' : '') 
                        + (isMyContast ? ' dashboard-my-contest' : '') + (showLobbySportsTab && this.state.HeaderOption.isPrimary ? ' sports-tab-primary' : '') + ((Constants.SELECTED_GAMET == Constants.GameType.DFS || Constants.SELECTED_GAMET == Constants.GameType.MultiGame || Constants.SELECTED_GAMET == Constants.GameType.Pred || Constants.SELECTED_GAMET == Constants.GameType.Pickem) ? ' dashboard-new' : '')}>
                        {
                            (
                                // (sportsList.length > 1 && 
                                (AvaSports.length > 1 &&
                                    (isLobby || isMyContast || (Constants.SELECTED_GAMET == Constants.GameType.Pickem && isLeaderboard))) &&
                                (showLobbySportsTab)
                                // (showLobbySportsTab && Constants.SELECTED_GAMET == Constants.GameType.DFS)
                            ) ?
                                this.renderTopSportsTab()
                                :
                                ''
                        }
                        <Tabs id='bottom-tabs' animation={false} onSelect={(tab) => this.onSelect(tab, false)} activeKey={this.state.activeTab} defaultActiveKey={'lobby'} className="dasboard-footer-tabs">
                          
                            {this.state.footerTabs !== undefined &&
                                _Map(this.state.footerTabs, (item, idx) => {

                                    return this.renderTabById(item, activeTab)
                                })
                            }
                        </Tabs>
                    </div>
                </div>
            </MyContext.Provider>
        )
    }
}
