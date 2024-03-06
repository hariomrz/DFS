import React, { lazy, Suspense } from "react";
import { Row, Col } from "react-bootstrap";
import { MyContext } from "../../InitialSetup/MyProvider";
import {updateDeviceToken, getLobbyBanner, GetPFFixtureLobby, GetPFMyLobbyFixtures} from "../../WSHelper/WSCallings";
import { NavLink } from "react-router-dom";
import { Utilities, _filter, _Map, BannerRedirectLink, parseURLDate, _isEmpty, isDateTimePast,} from "../../Utilities/Utilities";
// import {
//   CollectionInfoModal,
//   ContestDetailModal,
//   RFHTPModal,
//   DailyFantasyHTP,
//   RulesScoringModal,
// } from "../Modals";
import {  NoDataView, LobbyBannerSlider, LobbyShimmer} from "../CustomComponent";
import CustomHeader from "../../components/CustomHeader";
import ls from "local-storage";
import Images from "../../components/images";
import WSManager from "../../WSHelper/WSManager";
import PFFixtureContest from "./PFFixtureContest";
import Filter from "../../components/filter";
import * as AppLabels from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";
import * as Constants from "../../helper/Constants";
import PFMyContestSlider from "./PFMyContestSlider";
import MetaComponent from "../MetaComponent";
import PFHTP from "./PFHTP";

var bannerData = {};

export class PFLobby extends React.Component {
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
      sports_id: Constants.PFSelectedSport ? Constants.PFSelectedSport.sports_id : '',
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
      onLoadCls: false,
      dfsHTP: false,
      showDFSRulesModal: false,
     
    };
  }

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
      () => {}
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
          current_sport:Constants.PFSelectedSport.sports_id
        },
      });
    }
  };

  /**
   * @description - this method is to display contest of a fixture on click event
   * @param data - fixture model
   */
  gotoDetails = (data, event) => {
    event.preventDefault();
    if (data.status == 2 || data.is_live == 1) {
      this.props.history.push({
        pathname: "/my-contests",
        state: { from: data.is_live == 1 ? "lobby-live" : "lobby-completed" },
      });
    } 
    else {
      let dateformaturl = parseURLDate(data.season_scheduled_date);
      this.setState({ LobyyData: data });
      let contestListingPath =
        Utilities.getPFSelectedSportsForUrl().toLowerCase() + "/pick-fantasy/contest-listing/" + data.season_id +'/' + data.league_name + "-" + data.home +
        "-vs-" + data.away + "-" + dateformaturl;
      let CLPath = contestListingPath.toLowerCase() + "?sgmty=" + btoa(Constants.SELECTED_GAMET);
      console.log('this.state.FixtureData lobby',data)
      this.props.history.push({
        pathname: CLPath,
        state: {
          FixturedContest: this.state.FixtureData,
          LobyyData: data,
          lineupPath: CLPath,
        },
      });
    }

};

 
  /**
   * @description - this is life cycle method of react
   */
  componentDidMount() {
    if (ls.get("showMyTeam")) {
      ls.remove("showMyTeam");
    }
    if (ls.get("selOptArray")) {
      ls.remove('selOptArray')
    }
    if (ls.get("pickQueList")) {
      ls.remove('pickQueList')
    }
    if (ls.get("isPickEdit")) {
      ls.remove('isPickEdit')
    }
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
          this.lobbyContestList(0);
          setTimeout(() => {
            this.getBannerList();
          }, 1000);
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
    this.enableDisableBack(false);
  }

  /**
   * @description method will be called when changing sports
   */
  reload = (nextProps) => {
    if (window.location.pathname.startsWith("/lobby")) {
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
          this.lobbyContestList(0);
          if (WSManager.loggedIn()) {
              this.getMyLobbyFixturesList(0);
          }
          setTimeout(() => {
            this.getBannerList();
          }, 1000);
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
      updateDeviceToken(param).then((responseJson) => {});
    }
  };

  checkOldUrl() {
    // let url = window.location.href;
    // if (!url.includes("#") && window.location.pathname === "/lobby") {
    //   if (Utilities.getSelectedSportsForUrl())
    //     window.history.replaceState(
    //       "",
    //       "",
    //       window.location.pathname + "#" + Utilities.getSelectedSportsForUrl()
    //     );
    // }
    let url = window.location.href;
    let sports = '#' + Utilities.getPFSelectedSportsForUrl();
    if (!url.includes(sports)) {
        url = url + sports
    }
    if (!url.includes('#pick-fantasy')) {
        url = url + "#pick-fantasy";
    }
    window.history.replaceState("", "", url);
  }

  

  /**
   * @description - method to get fixtures listing from server/s3 bucket
   */
  lobbyContestList = async (offset) => {
    if (Constants.PFSelectedSport == null) return;

    let param = {
      sports_id: Constants.PFSelectedSport.sports_id
    };

    this.setState({ isLoaderShow: true, isListLoading: true });
    delete param.limit;
    var api_response_data = await GetPFFixtureLobby(param);
    // if (api_response_data && param.sports_id == Constants.AppSelectedSport) {
      this.setState({ isLoaderShow: false });
      let fixture_list = api_response_data.data;
      // if (offset == 0) {
        let tmpArray = [];
        // let tmpArray = fixture_list;
        let tmpLeagues = [];
        _Map(fixture_list, (obj) => {
          if (Utilities.minuteDiffValue({ date: parseInt(obj.game_starts_in) }) < 0) {
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
            new Date(a.scheduled_date) -
            new Date(b.scheduled_date)
        );
        let pinFixtures = []
        let normalFixture = []
        _Map(sortList, (obj) => {
          console.log(obj);
          if (!isDateTimePast(obj.scheduled_date)) {
            if (obj.is_pin_fixture == 1) {
              pinFixtures.push(obj)
            } else {
              normalFixture.push(obj)
            }
          }
        })
        this.setState(
          {
            ContestList: [...pinFixtures, ...normalFixture], //sortList, 
            OriginalContestList: sortList,
            filterLeagueList: tmpLeagues
           
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
      // }
      this.setState({ offset: api_response_data.offset });
    // }
    this.setState({ isListLoading: false });
    if (WSManager.loggedIn()) {
      this.getMyLobbyFixturesList(0);
    }
  };

  /**
   * @description - method to get fixtures listing from server/s3 bucket
   */
  getMyLobbyFixturesList = async (MCOffset) => {
    if (Constants.PFSelectedSport == null) return;

    let param = {
      sports_id: Constants.PFSelectedSport.sports_id,
      limit: this.state.limit,
      offset: this.state.MCOffset,
    };

    this.setState({ isLoaderShow: true, isListLoading: true });
    // delete param.limit;
    var api_response_data = await GetPFMyLobbyFixtures(param);
    if (api_response_data) {
      this.setState({ isLoaderShow: false });
      let data = api_response_data.data || [];
      let haseMore = data.length >= param.limit;
      if (param.offset == 0) {
        this.setState({
          myContestData: data || [],
          hasMore: false,
          MCOffset: 0,
        });
      } else {
        this.setState({
          myContestData: [...this.state.myContestData, ...data],
          MCOffset: data.offset,
          hasMore: haseMore,
        });
      }
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
    this.setState({ league_id: league_id }, function() {
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
    console.log('PFSelectedSport',Constants.PFSelectedSport)
    let sports_id = Constants.PFSelectedSport.sports_id;

    if (sports_id == null) return;
    if (bannerData[sports_id]) {
      console.log('api_response_data==')
      this.parseBannerData(bannerData[sports_id]);
    } else {
      console.log('api_response_data')
      setTimeout(async () => {
        this.setState({ isLoaderShow: true });
        let param = {
          sports_id: sports_id,
        };
        var api_response_data = await getLobbyBanner(param);
        let LSSportId = ls.get("PFSSport") ? ls.get("PFSSport").sports_id : Constants.PFSelectedSport.sports_id
        if (
          api_response_data &&
          param.sports_id == LSSportId
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
    let temp = [];
    _Map(this.getSelectedbanners(bdata), (item, idx) => {
      if (item.banner_type_id == 1) {
        let dateObj = Utilities.getUtcToLocal(item.schedule_date);
        if (Utilities.minuteDiffValue({ date: dateObj }) < 0) {
          temp.push(item);
        }
      } else {
        temp.push(item);
      }
    });
    this.setState({ BannerList: temp });
  };

  /**
   * @description call to get selected banner data
   */
  getSelectedbanners(api_response_data) {
    let tempBannerList = [];
    for (let i = 0; i < api_response_data.length; i++) {
      let banner = api_response_data[i];
      if (WSManager.getToken()) {
        if(banner.game_type_id == 0 ){
          if (
            parseInt(banner.banner_type_id) ===
              Constants.BANNER_TYPE_REFER_FRIEND ||
            parseInt(banner.banner_type_id) === Constants.BANNER_TYPE_DEPOSITE
          ) {
            if (banner.amount > 0) tempBannerList.push(api_response_data[i]);
          } else if (banner.banner_type_id === "6") {
            //TODO for banner type-6 add data
          } else {
            tempBannerList.push(api_response_data[i]);
          }
        }
      } else {
        if (banner.banner_type_id === "6") {
          tempBannerList.push(api_response_data[i]);
        }
      }
    }

    return tempBannerList;
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
      dfsHTP,
      showDFSRulesModal,
    } = this.state;

    let FitlerOptions = {
      showLobbyFitler: showLobbyFitlers,
      filtered_league_id: league_id,
    };

    let bannerLength = BannerList.length;
    var showLobbySportsTab =
      process.env.REACT_APP_LOBBY_SPORTS_ENABLE == 1 ? true : false;
    return (
      <MyContext.Consumer>
        {(context) => (
          <div className="transparent-header web-container tab-two-height pb0 pick-fantasy-lby">
            <MetaComponent page="lobby" />
              <Filter
                customLeagues={this.state.filterLeagueList}
                leagueList={league_id}
                {...this.props}
                FitlerOptions={FitlerOptions}
                hideFilter={this.hideFilter}
                filterLobbyResults={this.filterLobbyResults}
              ></Filter>

            {/* <div
              className="header-strip"
            >
              <div
                className={
                  "strip-content" + (showShadow ? " strip-content-shadow" : "")
                }
              >
                <span>{AppLabels.PICKS_FANTASY}</span>
                <a
                  href
                   onClick={(e) => { this.showDFSHTPModal(e) }}
                >
                  {AppLabels.HOW_TO_PLAY_FREE}
                </a>
              </div>
            </div> */}
            {/* <div className={`module-header-strip ${bannerLength == 0 ? '' : ' pb-0'}`}>
                <span>{AppLabels.PICKS_FANTASY}</span>
                <a
                    href
                    onClick={(e) => { this.showDFSHTPModal(e) }}
                >
                    {AppLabels.HOW_TO_PLAY_FREE}
                </a>
            </div> */}

            <div className={bannerLength > 0 ? "" : " "}>
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
              <div className={`module-header-strip ${bannerLength == 0 ? '' : ''}`}>
                <span>{AppLabels.PICKS_FANTASY}</span>
                <a
                    href
                    onClick={(e) => { this.showDFSHTPModal(e) }}
                >
                    {AppLabels.HOW_TO_PLAY_FREE}
                </a>
            </div>
                <>
                  {/* {WSManager.loggedIn() && ContestList.length > 0 && (
                    <div
                      className={
                        "contest-action single-btn-contest-action" +
                        (bannerLength == 0 ? " mt15" : " pt5")
                      }
                    >
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
                  )} */}
                  {WSManager.loggedIn() &&
                    myContestData &&
                    myContestData.length > 0 && (
                      <div className={`my-lobby-fixture-wrap ${bannerLength > 0 ? '' : 'mt0'}`}>
                        <div className="top-section-heading">
                          {AppLabels.MY_CONTEST}
                          <a href onClick={() => this.goToMyContest()}>
                            {AppLabels.VIEW} {AppLabels.All}
                          </a>
                        </div>
                        <PFMyContestSlider
                          FixtureData={myContestData}
                          gotoDetails={this.gotoDetails}
                          getMyLobbyFixturesList={this.getMyLobbyFixturesList}
                          timerCallback={() =>
                            this.timerCompletionCall(myContestData)
                          }
                        />
                      </div>
                    )}
                
                  <div className="upcoming-lobby-contest pt0">
                    <div className="top-section-heading">
                      {AppLabels.UPCOMING} {AppLabels.MATCHES}
                    </div>
                    <Row className={bannerLength > 0 ? "" : "mt15"}>
                      <Col sm={12}>
                        <Row>
                          <Col sm={12}>
                            <ul className={`collection-list-wrapper lobby-anim ${this.state.sports_id == 0 ? ' featured-enable' : ''}`}>
                              {ContestList.length == 0 &&
                                isListLoading &&
                                ShimmerList.map((item, index) => {
                                  return <LobbyShimmer key={index} />;
                                })}
                              {ContestList.length > 0 &&
                                ContestList.map((item, index) => {
                                  item['game_starts_in'] = parseInt(item.game_starts_in)
                                  return (
                                    <React.Fragment
                                      key={item.collection_master_id}
                                    >
                                        <PFFixtureContest
                                          {...this.props}
                                          sports_id={this.state.sports_id}
                                          onLBClick={(e) => {
                                            e.stopPropagation();
                                            CustomHeader.LBModalShow();
                                          }}
                                          indexKey={item.collection_master_id}
                                          ContestListItem={item}
                                          gotoDetails={this.gotoDetails}
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
                                      {/* {index === 1 && this.renderPREDCard()} */}
                                     
                                    </React.Fragment>
                                  );
                                })}
                              {/* {ContestList.length < 2 &&
                                !isListLoading &&
                                this.renderPREDCard()} */}
                             

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

            {/* {showContestDetail && (
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
            )} */}
            {/* {showRFHTPModal && (
              <RFHTPModal
                isShow={showRFHTPModal}
                isHide={this.hideRFHTPModalFn}
              />
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
            )} */}
            {dfsHTP && (
              <Suspense fallback={<div />}>
                <PFHTP
                  mShow={dfsHTP}
                  mHide={this.hideDFSHTPModal}
                  // rulesModal={this.showDFSRulesModal}
                />
              </Suspense>
            )}
          </div>
        )}
      </MyContext.Consumer>
    );
  }
}

export default PFLobby;
