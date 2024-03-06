import React, { lazy, Suspense } from "react";
import { Row, Col } from "react-bootstrap";
import { MyContext } from "../InitialSetup/MyProvider";
import {
  updateDeviceToken,
  getLobbyBanner,
  getLobbyFixtures,
  getMyLobbyFixtures,
  getLiveMatchGameCenter,
} from "../WSHelper/WSCallings";
import { NavLink } from "react-router-dom";
import {
  Utilities,
  _filter,
  _Map,
  BannerRedirectLink,
  parseURLDate,
  _isEmpty,
} from "../Utilities/Utilities";
import {
  CollectionInfoModal,
  ContestDetailModal,
  RFHTPModal,
  DailyFantasyHTP,
  RulesScoringModal,
} from "../Modals";
import {
  NoDataView,
  LobbyBannerSlider,
  LobbyShimmer,
} from "../Component/CustomComponent";
import CustomHeader from "../components/CustomHeader";
import ls from "local-storage";
import Images from "../components/images";
import WSManager from "../WSHelper/WSManager";
import FixtureContest from "./FixtureContest";
import Filter from "../components/filter";
import * as AppLabels from "../helper/AppLabels";
import * as WSC from "../WSHelper/WSConstants";
import * as Constants from "../helper/Constants";
import MyContestSlider from "./MyContestSlider";
import MetaComponent from "../Component/MetaComponent";
import { DFSHowToPlay } from "../Component/StockPrediction";
const DFSHTPModal = lazy(() =>
  import("../Component/DFSTournament/DFSHTPModal")
);

var bannerData = {};

export class Lobby extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      OriginalContestList: [],
      ContestList: [],
      BannerList: [],
      ShimmerList: [1, 2, 3, 4, 5],
      showContestDetail: false,
      FixtureData: "",
      isLoaderShow: false,
      isListLoading: false,
      offset: 0,
      MCOffset: 0,
      showLobbyFitlers: false,
      league_id: "",
      filterArray: [],
      sports_id: Constants.AppSelectedSport,
      showCollectionInfo: false,
      canRedirect: true,
      myContestData: [],
      hasMore: false,
      showCM: true,
      CoachMarkStatus: ls.get("coachmark-dfs") ? ls.get("coachmark-dfs") : 0,
      showModalSequence:
        ls.get("seqNo") && ls.get("seqNo") == "" ? true : false,
      filterLeagueList: [],
      showHTP: false,
      showShadow: false,
      DFSTourEnable: Utilities.getMasterData().a_dfst == 1 ? true : false,
      MerchandiseList: [],
      ismodeListLoad: false,
      SecondInningFixtures: [],
      onLoadCls: false,
      updatedLiveMatch: {},
      liveMatchCount: 0,
      dfsHTP: false,
      showDFSRulesModal: false,

    };
  }

  getGcLiveMatchList = () => {
    let param = {
      sports_id: Constants.AppSelectedSport,
    };
    getLiveMatchGameCenter(param).then((responseJson) => {
      this.setState({ isLoading: false });
      if (responseJson.response_code === WSC.successCode) {
        // let finalArrayUserP = responseJson.data.predictions && responseJson.data.predictions.sort((a, b) => (this.state.sort_order == 'ASC' ? a.prediction_master_id - b.prediction_master_id : b.prediction_master_id - a.prediction_master_id))
        //    let LV = [
        //        {
        //         away: "OV",
        //         away_flag: "OV_1639726833.png",
        //         collection_master_id: 1184,
        //         home: "WF",
        //         home_flag: "WF_1639981031.png",
        //         season_game_uid: "52078",
        //         season_scheduled_date: "2021-12-20T22:00:00.000Z"
        //        },
        //        {
        //         away: "ADB",
        //         away_flag: "OV_1639726833.png",
        //         collection_master_id: 1184,
        //         home: "ADB",
        //         home_flag: "WF_1639981031.png",
        //         season_game_uid: "52078",
        //         season_scheduled_date: "2021-12-20T22:00:00.000Z"
        //        }
        //    ]
        if (
          responseJson.data.live_match &&
          responseJson.data.live_match.length > 0
        ) {
          // this.updatedLiveMatchList(LV)
          this.setState({
            liveMatchCount: responseJson.data.live_match.length || 0,
          });
          this.updatedLiveMatchList(responseJson.data.live_match);
        } else {
          this.setState({ updatedLiveMatch: "" });
        }
      }
    });
  };
  updatedLiveMatchList = (obj) => {
    let data = {};
    //this.setState({ updateMatchRank: data })
    clearInterval(this.intervalLive);

    this.setState({ isLoading: false });
    if (obj != null && obj != undefined && obj.length > 0) {
      //this.setState({ updatedLiveMatchList: obj[0] })
      let intrvalCount = 0;
      this.intervalLive = setInterval(
        () =>
          this.setState({ time: Date.now() }, () => {
            intrvalCount = intrvalCount + 1;
            data = obj[intrvalCount - 1];
            this.setState({ updatedLiveMatch: {} }, () => {
              this.setState({
                updatedLiveMatch: data,
              });
            });

            if (intrvalCount >= obj.length) {
              intrvalCount = 0;
            }
          }),
        3500
      );
    }
  };

  ContestDetailShow = (data) => {
    this.setState({
      showContestDetail: true,
      FixtureData: data,
    });
  };
  /**
   * @description this method to hide contest detail model,
   */
  ContestDetailHide = () => {
    this.setState({
      showContestDetail: false,
    });
  };
  /**
   *
   * @description method to display collection info model.
   */
  CollectionInfoShow = (event) => {
    event.stopPropagation();
    this.setState(
      {
        showCollectionInfo: true,
      },
      () => { }
    );
  };
  /**
   *
   * @description method to hide collection info model.
   */
  CollectionInfoHide = () => {
    this.setState({
      showCollectionInfo: false,
    });
  };
  /**
   * @description this method to to open create contest screen
   */
  createContest = () => {
    this.props.history.push("/create-contest");
  };

  /**
   * @description this method to to open Have a league code screen
   */
  joinContest = () => {
    if (WSManager.loggedIn()) {
      this.props.history.push({ pathname: "/private-contest" });
    } else {
      this.props.history.push({ pathname: "/signup" });
    }
  };
  /**
   * @description this method will be call when user click join buttonn from contestt detail model screen,
   * in case user in not logged in then signup/login screen will display
   * @param data - contest model
   */
  onSubmitBtnClick = (data) => {
    if (!WSManager.loggedIn()) {
      setTimeout(() => {
        this.props.history.push({ pathname: "/signup" });
        Utilities.showToast(AppLabels.Please_Login_Signup_First, 3000);
      }, 10);
    } else {
      let dateformaturl = parseURLDate(data.season_scheduled_date);
      WSManager.clearLineup();
      let lineupPath =
        "/lineup/" + data.home + "-vs-" + data.away + "-" + dateformaturl;
      this.props.history.push({
        pathname: lineupPath.toLowerCase(),
        state: {
          FixturedContest: this.state.FixtureData,
          LobyyData: data,
          current_sport: Constants.AppSelectedSport,
        },
      });
    }
  };

  /**
   * @description - this method is to display contest of a fixture on click event
   * @param data - fixture model
   */
  gotoDetails = (data, event) => {
    ls.remove("guru_lineup_data");
    event.preventDefault();

    if (data.status == 2 || data.is_live == 1) {
      this.props.history.push({
        pathname: "/my-contests",
        state: { from: data.is_live == 1 ? "lobby-live" : "lobby-completed" },
      });
    } else {
      if (Constants.SELECTED_GAMET == Constants.GameType.MultiGame) {
        if (data.match_list.length == 1) {
          data.home = data.match_list[0].home;
          data.home_flag = data.match_list[0].home_flag;
          data.away = data.match_list[0].away;
          data.away_flag = data.match_list[0].away_flag;
        }
      }

      let dateformaturl = parseURLDate(data.season_scheduled_date);
      this.setState({ LobyyData: data });

      let contestListingPath =
        Utilities.getSelectedSportsForUrl().toLowerCase() +
        "/contest-listing/" +
        data.collection_master_id +
        "/" +
        data.league_name +
        "-" +
        data.home +
        "-vs-" +
        data.away +
        "-" +
        dateformaturl;
      let CLPath =
        contestListingPath.toLowerCase() +
        "?sgmty=" +
        btoa(Constants.SELECTED_GAMET);
      this.props.history.push({
        pathname: CLPath,
        state: {
          FixturedContest: this.state.FixtureData,
          LobyyData: data,
          lineupPath: CLPath,
        },
      });
    }

    // let dateformaturl = parseURLDate(data.season_scheduled_date);
    // this.setState({ LobyyData: data })
    // let contestListingPath = Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest-listing/' + data.collection_master_id + '/'+ data.league_name +'-' + data.home + "-vs-" + data.away + "-" + dateformaturl + "?sgmty=" +  btoa(Constants.SELECTED_GAMET);
    // this.props.history.push({ pathname: contestListingPath.toLowerCase(), state: { FixturedContest: this.state.FixtureData, LobyyData: data, lineupPath: contestListingPath } })
  };
  gotoGameCenter = (data, event) => {
    event.stopPropagation();
    let gameCenter = "/game-center/" + data.collection_master_id;
    this.props.history.push({
      pathname: gameCenter,
      state: { LobyyData: data },
    });
  };

  gotoSecondInningDetails = (data, event) => {
    event.preventDefault();
    let dateformaturl = parseURLDate(data.season_scheduled_date);
    this.setState({ LobyyData: data });
    let contestListingPath =
      Utilities.getSelectedSportsForUrl().toLowerCase() +
      "/contest-listing/" +
      data.collection_master_id +
      "/" +
      data.league_name +
      "-" +
      data.home +
      "-vs-" +
      data.away +
      "-" +
      dateformaturl;
    let CLPath =
      contestListingPath.toLowerCase() +
      "?sgmty=" +
      btoa(Constants.SELECTED_GAMET) +
      "&sit=" +
      btoa(true);
    this.props.history.push({
      pathname: CLPath,
      state: {
        FixturedContest: this.state.FixtureData,
        LobyyData: data,
        lineupPath: CLPath,
        is_2nd_inning: true,
      },
    });
  };

  /**
   * @description - this is life cycle method of react
   */
  componentDidMount() {



    // let param = {
    // }
    // getBannedStats(param).then((responseJson) => {
    //   if (responseJson.response_code == WSC.successCode) {
    //     let Data = Utilities.getMasterData();
    //     Data['banned_state'] = responseJson.data;
    //     let banStates = Object.keys(responseJson.data || {});
    //     Constants.setValue.setBanStateEnabled(banStates.length > 0);
    //     Utilities.setMasterData(Data);
    //     ls.set('bslist', responseJson.data);
    //     ls.set('bslistTime', { date: Date.now() });
    //     this.setState({
    //       isLoading: false
    //     })
    //   }
    // })



    if (window.location.pathname.startsWith("/lobby") && Utilities.getMasterData().allow_gc == 1) {
      this.getGcLiveMatchList();
    }
    if (ls.get("showMyTeam")) {
      ls.remove("showMyTeam");
    }
    ls.set("h2hTab", false);
    ls.remove("guru_lineup_data");
    window.addEventListener("scroll", this.onScrollList);
    setTimeout(() => {
      this.setState({
        onLoadCls: true,
      });
    }, 10);
    Utilities.gtmEventFire("landing_screen");

    if (window.ReactNativeWebView) {
      let data = {
        action: "SessionKey",
        targetFunc: "SessionKey",
        page: "lobby",
        SessionKey: WSManager.getToken()
          ? WSManager.getToken()
          : WSManager.getTempToken()
            ? WSManager.getTempToken()
            : "",
      };
      window.ReactNativeWebView.postMessage(JSON.stringify(data));
    }
    if (this.props.location.pathname == "/lobby") {
      let { sports_id } = this.state;
      WSManager.setFromConfirmPopupAddFunds(false);
      let league_id = this.getSportsLeagueId(
        sports_id,
        Constants.LOBBY_FILTER_ARRAY
      );
      this.setState(
        {
          isLoaderShow: true,
          sports_id,
          league_id,
          filterArray: Constants.LOBBY_FILTER_ARRAY,
        },
        () => {
          // this.lobbyContestList(0);

          this.lobbyContestList(0);
          this.getBannerList();
        }
      );

      //Analytics Calling
      WSManager.googleTrack(WSC.GA_PROFILE_ID, "fixture");
      if (WSManager.loggedIn()) {
        WSManager.googleTrackDaily(WSC.GA_PROFILE_ID, "loggedInusers");
      }
      this.checkOldUrl();
    }
    Utilities.handelNativeGoogleLogin(this);
    if (!ls.get("isDeviceTokenUpdated")) {
      let token_data = {
        action: "push",
        targetFunc: "push",
        type: "deviceid",
      };
      this.sendMessageToApp(token_data);
    }
    setTimeout(() => {
      let push_data = {
        action: "push",
        targetFunc: "push",
        type: "receive",
      };
      this.sendMessageToApp(push_data);
    }, 300);
  }

  onScrollList = (event) => {
    let scrollOffset = window.pageYOffset;
    if (scrollOffset > 0) {
      this.setState({
        showShadow: true,
      });
    } else {
      this.setState({
        showShadow: false,
      });
    }
  };

  UNSAFE_componentWillMount = () => {
    this.enableDisableBack(false);
    Utilities.setScreenName("lobby");
  };

  enableDisableBack(flag) {
    if (window.ReactNativeWebView) {
      let data = {
        action: "back",
        type: flag,
        targetFunc: "back",
      };
      this.sendMessageToApp(data);
    }
  }

  componentWillUnmount() {
    clearInterval(this.intervalLive);
    this.enableDisableBack(false);
  }

  /**
   * @description method will be called when changing sports
   */
  reload = (nextProps) => {
    if (window.location.pathname.startsWith("/lobby")) {
      if (Utilities.getMasterData().allow_gc == 1) {
        clearInterval(this.intervalLive);
        this.setState({ updatedLiveMatch: "" }, () => {
          this.getGcLiveMatchList();
        });
      }

      let league_id = this.getSportsLeagueId(
        nextProps.selectedSport,
        this.state.filterArray
      );
      this.setState(
        {
          ContestList: [],
          league_id: league_id,
          offset: 0,
          MCOffset: 0,
          sports_id: nextProps.selectedSport,
        },
        () => {
          WSManager.setFromConfirmPopupAddFunds(false);
          // this.lobbyContestList(0);

          this.lobbyContestList(0);
          if (WSManager.loggedIn()) {
            this.getMyLobbyFixturesList(0);
          }
          this.getBannerList();
          Filter.reloadLobbyFilter();
        }
      );
    }
  };

  sendMessageToApp(action) {
    if (window.ReactNativeWebView) {
      window.ReactNativeWebView.postMessage(JSON.stringify(action));
    }
  }

  blockMultiRedirection() {
    ls.set("canRedirect", false);
    setTimeout(() => {
      ls.set("canRedirect", true);
    }, 1000 * 5);
  }

  updateDeviceToken = () => {
    let param = {
      device_type: Utilities.getDeviceType(),
      device_id: WSC.DeviceToken.getDeviceId(),
    };
    if (WSManager.loggedIn()) {
      // if(WSManager.loggedIn() && !Constants.IS_SPORTS_HUB){
      updateDeviceToken(param).then((responseJson) => { });
    }
  };

  checkOldUrl() {
    let url = window.location.href;
    if (!url.includes("#") && window.location.pathname === "/lobby") {
      if (Utilities.getSelectedSportsForUrl())
        window.history.replaceState(
          "",
          "",
          window.location.pathname + "#" + Utilities.getSelectedSportsForUrl()
        );
    }
  }

  /**
   * @description - method to get fixtures listing from server/s3 bucket
   */
  lobbyContestList = async (offset) => {
    if (Constants.AppSelectedSport == null) return;

    let param = {
      sports_id: Constants.AppSelectedSport,
    };

    this.setState({ isLoaderShow: true, isListLoading: true });
    delete param.limit;
    var api_response_data = await getLobbyFixtures(param);
    if (api_response_data && param.sports_id == Constants.AppSelectedSport) {


      this.setState({ isLoaderShow: false });
      let fixture_list = api_response_data.fixture_list;
      let fixture_live = api_response_data.fixture_live || [];
      let merchandise_list = api_response_data.merchandise_list
        ? api_response_data.merchandise_list
        : [];
      if (offset == 0) {
        let tmpArray = [];
        let tmpLeagues = [];
        _Map(fixture_list, (obj) => {
          if (Utilities.minuteDiffValue({ date: obj.game_starts_in }) < 0) {
            tmpArray.push(obj);
            let objLeague = {
              league_id: obj.league_id,
              league_name: obj.league_name,
            };
            if (
              tmpLeagues.filter((e) => e.league_id === objLeague.league_id)
                .length === 0
            ) {
              tmpLeagues.push(objLeague);
            }
          }
        });
        let sortList = tmpArray.sort(
          (a, b) =>
            new Date(a.season_scheduled_date) -
            new Date(b.season_scheduled_date)
        );

        let pinFixtures = [];
        let normalFixture = [];
        _Map(sortList, (obj) => {
          if (obj.is_pin_fixture == 1) {
            pinFixtures.push(obj);
          } else {
            normalFixture.push(obj);
          }
        });

        this.setState(
          {
            ContestList: [...pinFixtures, ...normalFixture], //sortList,
            OriginalContestList: [...pinFixtures, ...normalFixture], //sortList,
            filterLeagueList: tmpLeagues,
            MerchandiseList: merchandise_list,
            SecondInningFixtures: _filter(fixture_live, (obj) => {
              return (
                Utilities.minuteDiffValue({ date: obj.game_starts_in }) < 0
              );
            }),
          },
          () => {
            if (Constants.LOBBY_FILTER_ARRAY.length > 0) {
              _Map(Constants.LOBBY_FILTER_ARRAY, (obj) => {
                if (obj.sports_id == this.state.sports_id) {
                  this.filterLobbyResults({ league_id: obj.league_id });
                }
              });
            }
          }
        );
      }
      this.setState({ offset: api_response_data.offset });
    }
    this.setState({ isListLoading: false });
    if (WSManager.loggedIn()) {
      this.getMyLobbyFixturesList(0);
    }
  };

  /**
   * @description - method to get fixtures listing from server/s3 bucket
   */
  getMyLobbyFixturesList = async (MCOffset) => {
    if (Constants.AppSelectedSport == null) return;

    let param = {
      sports_id: Constants.AppSelectedSport,
      limit: this.state.limit,
      offset: this.state.MCOffset,
    };

    this.setState({ isLoaderShow: true, isListLoading: true });
    // delete param.limit;
    var api_response_data = await getMyLobbyFixtures(param);
    if (api_response_data) {
      this.setState({ isLoaderShow: false });
      let data = api_response_data.data || [];
      let tmpArray = [];
      _Map(data, (obj) => {
        if (obj.dfs_count == 0 && obj["2nd_inning_count"] > 0) {
        } else {
          tmpArray.push(obj);
        }
      });
      let haseMore = data.length >= param.limit;
      if (param.offset == 0) {
        this.setState({
          myContestData: tmpArray || [],
          hasMore: false,
          MCOffset: 0,
        });
      } else {
        this.setState({
          myContestData: [...this.state.myContestData, ...tmpArray],
          MCOffset: data.offset,
          hasMore: haseMore,
        });
      }
      //     let tmpArray = []
      //     _Map(api_response_data,(obj)=>{
      //         if (Utilities.minuteDiffValue({ date: obj.game_starts_in }) < 0) {
      //             tmpArray.push(obj);
      //         }
      //     })
      //     this.setState({ ContestList: tmpArray, OriginalContestList: tmpArray }, () => {
      //         if (Constants.LOBBY_FILTER_ARRAY.length > 0) {
      //             this.filterLobbyResults({ league_id: Constants.LOBBY_FILTER_ARRAY[0].league_id })
      //         }
      //     })
      // }
      // else {
      //     let tmpArray = []
      //     _Map(api_response_data,(obj)=>{
      //         if (Utilities.minuteDiffValue({ date: obj.game_starts_in }) < 0) {
      //             tmpArray.push(obj);
      //         }
      //     })
      //     this.setState({ ContestList: [...this.state.ContestList, ...tmpArray], OriginalContestList: [...this.state.ContestList, ...tmpArray] });
      // }
      // this.setState({ offset: api_response_data.offset })
    }
    this.setState({ isListLoading: false });
  };


  getSportsLeagueId(sports_id, filterArray) {
    let league_id = "";
    for (let i = 0; i < filterArray.length; i++) {
      if (filterArray[i].sports_id == sports_id) {
        league_id = filterArray[i].league_id;
      }
    }
    return league_id;
  }

  /** 
    @description hide lobby filters 
    */
  hideFilter = () => {
    this.setState({ showLobbyFitlers: false });
    this.props.hideFilterData();
  };

  /** 
    @description show lobby filters 
    */
  showFilter = () => {
    this.setState({ showLobbyFitlers: true });
  };

  /** 
    @description Apply filters and load data accordingly
    */
  filterLobbyResults = (filterObj) => {
    let league_id = filterObj.league_id ? filterObj.league_id : "";
    this.setState({ league_id: league_id }, function () {
      this.filterFixturesLocally(league_id);
    });

    let filterArray = this.setFilterArray(league_id);
    Constants.setValue.setFilter(filterArray);
    this.setState({
      league_id: league_id,
      showLobbyFitlers: false,
      offset: 0,
      filterArray: filterArray,
    });
    this.props.hideFilterData();
  };

  filterFixturesLocally(leagueIds) {
    let allFixtures = this.state.OriginalContestList;
    if (leagueIds == "") {
      this.setState({ ContestList: allFixtures });
    } else {
      let filteredList = [];
      for (var i = 0; i < allFixtures.length; i++) {
        if (leagueIds.includes(allFixtures[i].league_id)) {
          filteredList.push(allFixtures[i]);
        }
      }
      this.setState({ ContestList: filteredList });
    }
  }

  setFilterArray(league_id) {
    let { filterArray } = this.state;

    let hasFilter = false;
    if (filterArray.length > 0) {
      for (let i = 0; i < filterArray.length; i++) {
        if (filterArray[i].sports_id == this.state.sports_id) {
          hasFilter = true;
          filterArray[i].league_id = league_id;
        }
      }
    }

    if (!hasFilter && league_id != "") {
      let filterObj = {
        sports_id: this.state.sports_id,
        league_id: league_id,
      };
      filterArray.push(filterObj);
    }

    return filterArray;
  }

  /**
   * @description api call to get baner listing from server
   */
  getBannerList = () => {
    let sports_id = Constants.AppSelectedSport;

    if (sports_id == null) return;
    if (bannerData[sports_id]) {
      this.parseBannerData(bannerData[sports_id]);
    } else {
      setTimeout(async () => {
        this.setState({ isLoaderShow: true });
        let param = {
          sports_id: sports_id,
        };
        var api_response_data = await getLobbyBanner(param);
        if (
          api_response_data &&
          param.sports_id == Constants.AppSelectedSport
        ) {
          bannerData[sports_id] = api_response_data;
          this.parseBannerData(api_response_data);
        }
        this.setState({ isLoaderShow: false });
      }, 1500);
    }
  };

  /**
   * @description call to parse banner data
   */
  parseBannerData = (bdata) => {
    let refData = "";
    let temp = [];
    // _Map(this.getSelectedbanners(bdata), (item, idx) => {
    _Map(bdata, (item, idx) => {
      if (item.game_type_id == 0 || WSManager.getPickedGameTypeID() == item.game_type_id) {
        if (item.banner_type_id == 2) {
          refData = item;
        }
        if (item.banner_type_id == 1) {
          let dateObj = Utilities.getUtcToLocal(item.schedule_date);
          if (Utilities.minuteDiffValue({ date: dateObj }) < 0) {
            temp.push(item);
          }
        } else {
          temp.push(item);
        }
      }
    });
    if (refData) {
      setTimeout(() => {
        CustomHeader.showRCM(refData);
      }, 200);
    }
    this.setState({ BannerList: temp });
  };

  /**
   * @description call to get selected banner data
   */
  getSelectedbanners(api_response_data) {
    let tempBannerList = [];
    for (let i = 0; i < api_response_data.length; i++) {
      let banner = api_response_data[i];
      if (WSManager.getToken() && WSManager.getToken() != "") {
        if (
          banner.banner_type_id == Constants.BANNER_TYPE_REFER_FRIEND ||
          banner.banner_type_id == Constants.BANNER_TYPE_DEPOSITE
        ) {
          if (banner.amount > 0) tempBannerList.push(api_response_data[i]);
        } else if (banner.banner_type_id == "6") {
          //TODO for banner type-6 add data
        } else {
          tempBannerList.push(api_response_data[i]);
        }
      }

      return tempBannerList;
    }
  }


  /**
   * @description method to redirect user on appopriate screen when user click on banner
   * @param {*} banner_type_id - id of banner on which clicked
   */
  redirectLink = (result, isRFBanner) => {
    if (isRFBanner) {
      this.showRFHTPModalFn();
    } else {
      if (WSManager.loggedIn()) {
        BannerRedirectLink(result, this.props);
      } else {
        this.props.history.push({ pathname: "/signup" });
      }
    }
  };
  showRFHTPModalFn = () => {
    this.setState({ showRFHTPModal: true });
  };
  hideRFHTPModalFn = () => {
    this.setState({ showRFHTPModal: false });
  };

  UNSAFE_componentWillReceiveProps(nextProps) {
    if (this.state.sports_id != nextProps.selectedSport) {
      this.reload(nextProps);
    }
    if (this.state.showLobbyFitlers != nextProps.showLobbyFitlers) {
      this.setState({ showLobbyFitlers: nextProps.showLobbyFitlers });
    }
  }

  timerCompletionCall = (item) => {
    let fArray = _filter(this.state.ContestList, (obj) => {
      return item.collection_master_id != obj.collection_master_id;
    });
    this.setState({
      ContestList: fArray,
    });
  };
  timerSecInngCompletionCall = (item) => {
    let fArray = _filter(this.state.SecondInningFixtures, (obj) => {
      return item.collection_master_id != obj.collection_master_id;
    });
    this.setState({
      SecondInningFixtures: fArray,
    });
  };

  goToPREDICTION = () => {
    WSManager.setPickedGameType(Constants.GameType.Pred);
    // window.location.replace("/lobby#" + Utilities.getSelectedSportsForUrl() + "#prediction");

    let gameType = Utilities.getMasterData().sports_hub;
    let HGLIST = _filter(gameType, (obj) => {
      return obj.game_key == Constants.GameType.Pred;
    });
    let lsSport = ls.get("selectedSports");
    if (HGLIST[0].allowed_sports.includes(lsSport)) {
      window.location.replace(
        "/lobby#" + Utilities.getSelectedSportsForUrl() + "#prediction"
      );
    } else {
      let sport = HGLIST[0].allowed_sports[0];
      ls.set("selectedSports", sport);
      Constants.setValue.setAppSelectedSport(sport);
      window.location.replace(
        "/lobby#" + Utilities.getSelectedSportsForUrl() + "#prediction"
      );
    }
  };

  renderGameCenterLiveGameCard = () => {
    let updatedLiveMatchList = this.state.updatedLiveMatch;
    return (

      // <div
      //   onClick={(event) =>
      //     !_isEmpty(updatedLiveMatchList) &&
      //     this.gotoGameCenter(updatedLiveMatchList, event)
      //   }
      //   className="bg-game-center"
      // >
      //   <div className="bg-image">
      //     <div></div>
      //     <div
      //       className={`go-to-game-center-of  ${
      //         this.state.liveMatchCount == 1 ? " no-ani" : ""
      //       }`}
      //     >
      //       <div className="goto-text">{AppLabels.GO_TO_GAME_CENTER_FOR}</div>
      //       {!_isEmpty(updatedLiveMatchList) && (
      //         <div className={`h-a-container `}>
      //           <img
      //             className="flag-home"
      //             src={
      //               updatedLiveMatchList.home_flag
      //                 ? Utilities.teamFlagURL(updatedLiveMatchList.home_flag)
      //                 : Images.NODATA
      //             }
      //             alt=""
      //           />
      //           <div className="verses-h-a">
      //             {updatedLiveMatchList.home}
      //             {" " + AppLabels.VS + " "}
      //             {updatedLiveMatchList.away}
      //           </div>
      //           <img
      //             className="flag-away"
      //             src={
      //               updatedLiveMatchList.away_flag
      //                 ? Utilities.teamFlagURL(updatedLiveMatchList.away_flag)
      //                 : Images.NODATA
      //             }
      //             alt=""
      //           />
      //         </div>
      //       )}
      //     </div>

      //     <div className="live-container">
      //       <img src={Images.LIVE_GC} alt="" className="image"></img>
      //       <div className="live-text">{AppLabels.LIVE}</div>
      //     </div>
      //   </div>
      // </div>
      <div onClick={(event) => !_isEmpty(updatedLiveMatchList) && this.gotoGameCenter(updatedLiveMatchList, event)} className='bg-game-center-container'>
        <div className='inner-view-live'>
          <div className={`  ${this.state.liveMatchCount == 1 ? ' no-ani' : ''}`}>

            {
              !_isEmpty(updatedLiveMatchList) &&
              <div className="game-center-view">
                <div className='image-game-center'><img className='' src={updatedLiveMatchList.home_flag ? Utilities.teamFlagURL(updatedLiveMatchList.home_flag) : Images.NODATA} alt="" />
                  <img className='away-img' src={updatedLiveMatchList.away_flag ? Utilities.teamFlagURL(updatedLiveMatchList.away_flag) : Images.NODATA} alt="" /></div>
                <div className='responsive-view-cotainer'>
                  <span className="go-to-game-center-text">{AppLabels.GO_TO_GAME_CENTER_FOR}</span>
                  <span className="team-name">
                    {updatedLiveMatchList.home}{" " + AppLabels.VS + " "}{updatedLiveMatchList.away}</span>
                </div>
              </div>
            }

          </div>
          <div className='arrow-icon-container'>
            <i className="icon-arrow-right iocn-first"></i>
            <i className="icon-arrow-right iocn-second"></i>
            <i className="icon-arrow-right iocn-third"></i>

          </div>

        </div>

      </div>
    );
  };

  renderPREDCard = () => {
    if (Utilities.getMasterData().a_sports_prediction_bnr != 1) {
      return "";
    }
    let bannerImg = Utilities.getMasterData().sports_prediction_bnr;
    if (Constants.IS_PREDICTION) {
      return bannerImg ? (
        <li onClick={this.goToPREDICTION} className="prd-card-img-only">
          <img
            className="img-shape"
            src={Utilities.getSettingURL(bannerImg)}
            alt=""
          />
        </li>
      ) : (
        <li
          onClick={this.goToPREDICTION}
          className="dfs-card prd-card dfs-card-new"
        >
          <div className="dfs-c-new">
            <div className="dfs-c-inner dfs-c-inner-left">
              <img
                className="img-dfs"
                src={Images.PLAY_PRED_BANNER_IMG}
                alt=""
              />
            </div>
            <div className="dfs-c-inner  dfs-c-inner-right">
              <p>
                Play prediction & win coins to redeem for exciting offers &
                prizes
              </p>
            </div>
          </div>
        </li>
      );
    }
    return "";
  };

  goToMyContest = () => {
    this.props.history.push({ pathname: "/my-contests" });
  };

  showCM = () => {
    this.setState({ showCM: true });
  };

  hidePropCM = () => {
    this.setState({ showCM: false });
  };

  showHTPModal = () => {
    this.setState({
      showHTP: true,
    });
  };

  hideHTPModal = () => {
    this.setState({
      showHTP: false,
    });
  };

  clickEarnCoins = () => {
    if (WSManager.loggedIn()) {
      this.props.history.push("/earn-coins");
    } else {
      this.props.history.push({ pathname: "/signup" });
    }
  };

  showDFSHTPModal = () => {
    this.setState({
      dfsHTP: true,
    });
  };

  hideDFSHTPModal = () => {
    this.setState({
      dfsHTP: false,
    });
  };

  showDFSRulesModal = () => {
    this.setState({
      dfsHTP: false,
      showDFSRulesModal: true,
    });
  };

  hideDFSRulesModal = () => {
    this.setState({
      showDFSRulesModal: false
    });
  };

  render() {
    const {
      showContestDetail,
      FixtureData,
      isLoaderShow,
      showCollectionInfo,
      BannerList,
      league_id,
      showLobbyFitlers,
      ShimmerList,
      ContestList,
      isListLoading,
      myContestData,
      showModalSequence,
      showRFHTPModal,
      showHTP,
      showShadow,
      DFSTourEnable,
      ismodeListLoad,
      SecondInningFixtures,
      updatedLiveMatch,
      dfsHTP,
      showDFSRulesModal,
    } = this.state;

    let FitlerOptions = {
      showLobbyFitler: showLobbyFitlers,
      filtered_league_id: league_id,
    };

    let bannerLength = BannerList.length;
    let showToggleSec = DFSTourEnable;
    var showLobbySportsTab =
      process.env.REACT_APP_LOBBY_SPORTS_ENABLE == 1 ? true : false;

    return (
      <MyContext.Consumer>
        {(context) => (
          <div className="transparent-header web-container tab-two-height pb0 DFS-tour-lobby">
            <MetaComponent page="lobby" />
            {!ismodeListLoad && (
              <Filter
                customLeagues={this.state.filterLeagueList}
                leagueList={league_id}
                {...this.props}
                FitlerOptions={FitlerOptions}
                hideFilter={this.hideFilter}
                filterLobbyResults={this.filterLobbyResults}
              ></Filter>
            )}

            <div
              className={
                "header-fixed-strip" +
                (showLobbySportsTab ? " header-fixed-strip-2" : "")
              }
            >
              <div
                className={
                  "strip-content" + (showShadow ? " strip-content-shadow" : "")
                }
              >
                <span>{AppLabels.DAILY_FANTASY}</span>
                <a
                  href
                  onClick={(e) => { this.showDFSHTPModal(e) }}
                >
                  {AppLabels.HOW_TO_PLAY_FREE}
                </a>
              </div>
            </div>


            <div className={bannerLength > 0 ? "" : " m-t-60"}>
              {bannerLength > 0 && (
                <div
                  className={
                    bannerLength > 0 ? "banner-v animation" : "banner-v"
                  }
                >
                  {bannerLength > 0 && (
                    <LobbyBannerSlider
                      BannerList={BannerList}
                      redirectLink={this.redirectLink.bind(this)}
                    />
                  )}
                </div>
              )}

              {/* {
                  showToggleSec &&
                  <div className="my-lobby-dfs-tabs">
                      <Button> {AppLabels.TOURNAMENT}</Button>
                  </div>
              } */}
              <>
                {WSManager.loggedIn() && ContestList.length > 0 && (
                  <div
                    className={
                      "contest-action single-btn-contest-action" +
                      (bannerLength == 0 ? " mt15" : " pt5")
                    }
                  >
                    {Constants.SELECTED_GAMET != Constants.GameType.DFS &&
                      Utilities.getMasterData().private_contest == "1" && (
                        <NavLink
                          exact
                          to={"/create-contest"}
                          className="btn btnStyle btn-rounded small"
                        >
                          <span className="text-uppercase">
                            {AppLabels.Create_a_Contest}
                          </span>
                        </NavLink>
                      )}
                    <NavLink
                      exact
                      to="/private-contest"
                      className="btn btnStyle btn-rounded small"
                    >
                      <span className="league-code-btn text-uppercase">
                        {AppLabels.JOIN_CONTEST}
                      </span>
                    </NavLink>
                  </div>
                )}
                {WSManager.loggedIn() &&
                  myContestData &&
                  myContestData.length > 0 && (
                    <div className="my-lobby-fixture-wrap">
                      <div className="top-section-heading">
                        {AppLabels.MY_CONTEST}
                        <a href onClick={() => this.goToMyContest()}>
                          {AppLabels.VIEW} {AppLabels.All}
                        </a>
                      </div>
                      <MyContestSlider
                        FixtureData={myContestData}
                        gotoDetails={this.gotoDetails}
                        getMyLobbyFixturesList={this.getMyLobbyFixturesList}
                        timerCallback={() =>
                          this.timerCompletionCall(myContestData)
                        }
                      />
                    </div>
                  )}
                {SecondInningFixtures.length > 0 && (
                  <div className="my-lobby-fixture-wrap second-inning">
                    <div className="top-section-heading">
                      <span className="live-text-sec">{AppLabels.LIVE}</span>{" "}
                      {AppLabels.MATCHES}
                    </div>
                    <MyContestSlider
                      FixtureData={SecondInningFixtures}
                      gotoDetails={this.gotoSecondInningDetails}
                      timerCallback={(matchobj) =>
                        this.timerSecInngCompletionCall(matchobj)
                      }
                      isSecondInning={true}
                      getMyLobbyFixturesList={() =>
                        console.log("SecondInning")
                      }
                    />
                  </div>
                )}
                <div className="upcoming-lobby-contest">
                  <div className="top-section-heading">
                    {AppLabels.UPCOMING} {AppLabels.MATCHES}
                  </div>
                  <Row className={bannerLength > 0 ? "" : "mt15"}>
                    <Col sm={12}>
                      <Row>
                        <Col sm={12}>
                          <ul className="collection-list-wrapper lobby-anim">
                            {ContestList.length == 0 &&
                              isListLoading &&
                              ShimmerList.map((item, index) => {
                                return <LobbyShimmer key={index} />;
                              })}

                            {ContestList.length > 0 &&
                              ContestList.map((item, index) => {
                                return (
                                  <React.Fragment
                                    key={item.collection_master_id}
                                  >
                                    {(
                                      <FixtureContest
                                        {...this.props}
                                        onLBClick={(e) => {
                                          e.stopPropagation();
                                          CustomHeader.LBModalShow();
                                        }}
                                        indexKey={item.collection_master_id}
                                        ContestListItem={item}
                                        gotoDetails={this.gotoDetails}
                                        gotoGameCenter={this.gotoGameCenter}
                                        CollectionInfoShow={
                                          this.CollectionInfoShow
                                        }
                                        IsCollectionInfoHide={
                                          this.CollectionInfoHide
                                        }
                                        timerCallback={() =>
                                          this.timerCompletionCall(item)
                                        }
                                      />
                                    )}
                                    {index === 1 && this.renderPREDCard()}
                                    {Utilities.getMasterData().allow_gc ==
                                      1 &&
                                      // !_isEmpty(updatedLiveMatch) &&
                                      updatedLiveMatch != "" &&
                                      index === 1 &&
                                      this.renderGameCenterLiveGameCard()}
                                  </React.Fragment>
                                );
                              })}
                            {ContestList.length < 2 &&
                              !isListLoading &&
                              this.renderPREDCard()}
                            {Utilities.getMasterData().allow_gc == 1 &&
                              ContestList.length < 2 && !isListLoading &&
                              // !_isEmpty(updatedLiveMatch)
                              updatedLiveMatch != "" &&
                              this.renderGameCenterLiveGameCard()}

                            {ContestList.length == 0 && !isListLoading && (
                              <NoDataView
                                BG_IMAGE={Images.no_data_bg_image}
                                // CENTER_IMAGE={
                                //   Constants.DARK_THEME_ENABLE
                                //     ? Images.DT_BRAND_LOGO_FULL
                                //     : Images.BRAND_LOGO_FULL
                                // }
                                CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                MESSAGE_1={AppLabels.NO_FIXTURES_MSG1}
                                MESSAGE_2={AppLabels.NO_FIXTURES_MSG2}
                                onClick_2={this.joinContest}
                              />
                            )}
                          </ul>
                        </Col>
                      </Row>
                    </Col>
                  </Row>
                </div>
              </>
            </div>

            {showContestDetail && (
              <ContestDetailModal
                IsContestDetailShow={showContestDetail}
                onJoinBtnClick={this.onSubmitBtnClick}
                IsContestDetailHide={this.ContestDetailHide}
                OpenContestDetailFor={FixtureData}
                {...this.props}
              />
            )}
            {showCollectionInfo && (
              <CollectionInfoModal
                IsCollectionInfoShow={showCollectionInfo}
                IsCollectionInfoHide={this.CollectionInfoHide}
              />
            )}
            {showRFHTPModal && (
              <RFHTPModal
                isShow={showRFHTPModal}
                isHide={this.hideRFHTPModalFn}
              />
            )}
            {showHTP && (
              <Suspense fallback={<div />}>
                <DFSHTPModal
                  ModalData={{
                    show: showHTP,
                    hide: this.hideHTPModal,
                  }}
                />
              </Suspense>
            )}
            {dfsHTP && (
              <Suspense fallback={<div />}>
                <DailyFantasyHTP
                  mShow={dfsHTP}
                  mHide={this.hideDFSHTPModal}
                  rulesModal={this.showDFSRulesModal}
                />
              </Suspense>
            )}
            {showDFSRulesModal && (
              <RulesScoringModal
                MShow={showDFSRulesModal}
                MHide={this.hideDFSRulesModal}
              />
            )}
          </div>
        )}
      </MyContext.Consumer>
    );
  }
}

export default Lobby;
