import React, { lazy, Suspense } from "react";
import { Row, Col } from "react-bootstrap";
import { MyContext } from "../../InitialSetup/MyProvider";
import {
  updateDeviceToken,
  getLobbyBanner,
  getSPLbyContestLst,
  getSPMyLobbyContest,
  getSPLbyFilter,
  getSPUserLineupList,
  stockJoinContest,
  getUserProfile,
} from "../../WSHelper/WSCallings";
import { NavLink } from "react-router-dom";
import {
  Utilities,
  _Map,
  BannerRedirectLink,
  parseURLDate,
  _isUndefined,
  checkBanState,
  _filter,
} from "../../Utilities/Utilities";
import {
  NoDataView,
  LobbyBannerSlider,
  LobbyShimmer,
} from "../../Component/CustomComponent";
import CustomHeader from "../../components/CustomHeader";
import ls from "local-storage";
import Images from "../../components/images";
import WSManager from "../../WSHelper/WSManager";
import * as AL from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";
import * as Constants from "../../helper/Constants";
import MetaComponent from "../../Component/MetaComponent";
import SPFixtureCard from "./SPFixtureCard";
import SPMyContestSlider from "./SPMyContestSlider";
import MyAlert from "../../Modals/MyAlert";
import {
  Thankyou,
  ContestDetailModal,
  ConfirmationPopup,
  UnableJoinContest,
  ShareContestModal,
  ShowMyAllTeams,
} from "../../Modals";
import moment from "moment";
import { HowPlay } from "../StockPrediction";
const SPFHTP = lazy(() => import("./SPHTP"));
const SPLobbyFilter = lazy(() => import("./SPLobbyFilter"));
const SPRules = lazy(() => import("./SPFantasyRules"));

var bannerData = {};

class SPLobby extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      FixtureList: [],
      BannerList: [],
      ShimmerList: [1, 2, 3, 4, 5],
      isListLoading: false,
      showHTP: false,
      showShadow: false,
      stockSetting: [],
      stockStatistic: !_isUndefined(props.location.state)
        ? props.location.state.stockStatistic
        : false,
      contestListing: !_isUndefined(props.location.state)
        ? props.location.state.contestListing
        : false,
      pushListing: !_isUndefined(props.location.state)
        ? props.location.state.pushListing
        : [],
      showFilter: false,
      filterList: {},
      minCT: "",
      maxCT: "",
      minFee: "",
      maxFee: "",
      minEnt: "",
      maxEnt: "",
      minWin: "",
      maxWin: "",
      dayFilter: "",
      fromDate: "",
      toDate: "",
      showRules: false,
      showAlert: false,
      showContestDetail: false,
      activeTab: "",
      TeamList: [],
      TotalTeam: [],
      userTeamListSend: [],
      showConfirmationPopUp: false,
      showThankYouModal: false,
      lineup_master_idArray: [],
      MyStockList: [],
      isFilerApplied: false,
      showTimeOutAlert: false,
      profileData: ''
    };
  }


  /**
   * @description - this is life cycle method of react
   */
  componentDidMount() {
    setTimeout(() => {
      if (this.state.stockStatistic) {
        this.props.history.push("/stock-fantasy/statistics");
      }
      if (this.state.contestListing) {
        this.gotoDetails(this.state.pushListing);
      }
    }, 300);
    if (this.props.location.pathname == "/lobby") {
      this.checkOldUrl();
      this.getLobbyFilters();
      this.getLobbyFixture();
      setTimeout(() => {
        this.getBannerList();
      }, 1500);
      WSManager.googleTrack(WSC.GA_PROFILE_ID, "stock_fixture");
      if (WSManager.loggedIn()) {
        // this.callLobbySettingApi()
        this.getMyFixtures();
        WSManager.googleTrackDaily(WSC.GA_PROFILE_ID, "loggedInusers");
      }
      window.addEventListener("scroll", this.onScrollList);
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
      WSManager.clearLineup();
    }
    if(WSManager.loggedIn()){
      getUserProfile().then((responseJson) => {
          if (responseJson && responseJson.response_code == WSC.successCode) {
              this.setState({ profileData: responseJson.data });
          }
      })
    }
  }

  onScrollList = () => {
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
  };

  checkOldUrl() {
    let url = window.location.href;
    if (!url.includes("#stock-prediction")) {
      url = url + "#stock-prediction";
    }
    window.history.replaceState("", "", url);
  }

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
    window.removeEventListener("scroll", this.onScrollList);
  }

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
      device_type: WSC.deviceTypeAndroid,
      device_id: WSC.DeviceToken.getDeviceId(),
    };
    if (WSManager.loggedIn()) {
      updateDeviceToken(param).then((responseJson) => {});
    }
  };

  /**
   * @description api call to get stock filter list from server
   */
  getLobbyFilters = () => {
    getSPLbyFilter().then((responseJson) => {
      if (responseJson && responseJson.response_code == WSC.successCode) {
        this.setState({
          filterList: responseJson.data,
        });
      }
    });
  };

  /**
   * @description api call to get stock fixture listing from server
   */
  getLocalToUTCTime = (value) => {
    var date = new Date();
    var IndHR = value.split(":")[0];
    var IndMin = value.split(":")[1];

    date.setHours(IndHR);
    date.setMinutes(IndMin);

    var now_utc = new Date(date.getTime() + date.getTimezoneOffset() * 60000);
    const formatted = moment(now_utc).format("hh:mm");
    return formatted;
  };

  /**
   * @description api call to get stock fixture listing from server
   */
  getLobbyFixture = async () => {
    this.setState({ isListLoading: true });

    let MinCTUTC =
      this.state.minCT == "" ? "" : this.getLocalToUTCTime(this.state.minCT);
    let MaxCTUTC =
      this.state.maxCT == "" ? "" : this.getLocalToUTCTime(this.state.maxCT);

    let param = {
      min_time: MinCTUTC,
      max_time: MaxCTUTC,
      min_fee: this.state.minFee,
      max_fee: this.state.maxFee,
      min_entries: this.state.minEnt,
      max_entries: this.state.maxEnt,
      min_winning: this.state.minWin,
      max_min_winning: this.state.maxWin,
      from_date: this.state.fromDate,
      to_date: this.state.toDate,
    };
    var res = await getSPLbyContestLst(param);
    if (res.data && res.data) {
      let fArray = _filter(res.data.contest, (obj) => {
        let dateObj = Utilities.getUtcToLocal(obj.schedule_date);
        return Utilities.minuteDiffValue({ date: dateObj }) < 0;
      });
      this.setState({ isListLoading: false, FixtureList: res.data.contest });
    } else {
      this.setState({ isListLoading: false });
    }
  };

  /**
   * @description api call to get joined stock fixture listing from server
   */
  getMyFixtures = async () => {
    let param = {
      // "page_no": "1",
      // "page_size": "20",
      // "stock_type":"3"
    };
    var res = await getSPMyLobbyContest(param);
    if (res.data && res.data) {
      this.setState({ MyStockList: res.data });
    }
  };

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
          param.sports_id.toString() === Constants.AppSelectedSport.toString()
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
        if(banner.game_type_id == 0 || 
            banner.game_type_id == 10 ||
            banner.game_type_id == 13 ||
            banner.game_type_id == 27 ||
            banner.game_type_id == 39
          ){
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
        if (banner.banner_type_id === "6" && (banner.game_type_id == 0 || 
          banner.game_type_id == 10 ||
          banner.game_type_id == 13 ||
          banner.game_type_id == 27 ||
          banner.game_type_id == 39
        )) {
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
  redirectLink = (result) => {
    BannerRedirectLink(result, this.props);
  };

  goToMyContest = () => {
    this.props.history.push({ pathname: "/my-contests" });
  };

  showHTPModal = (e) => {
    e.stopPropagation();
    this.setState({
      showHTP: true,
    });
  };

  hideHTPModal = () => {
    this.setState({
      showHTP: false,
    });
  };

  showRulesModal = (e) => {
    e.stopPropagation();
    this.setState({
      showRules: true,
    });
  };

  hideRulesModal = () => {
    this.setState({
      showRules: false,
    });
  };

  /**
   *
   * @description method to display confirmation popup model, when user join contest.
   */
  ConfirmatioPopUpShow = () => {
    this.setState({
      showConfirmationPopUp: true,
    });
  };
  /**
   *
   * @description method to hide confirmation popup model
   */
  ConfirmatioPopUpHide = () => {
    this.setState({
      showConfirmationPopUp: false,
    });
  };

  playNow = (item) => {
    if (WSManager.loggedIn()) {
      this.gotoDetails(item);
    } else {
      this.goToSignup();
    }
  };
  btnAction = (item) => {
    if (WSManager.loggedIn()) {
      item["collection_master_id"] = item.collection_id;
      if (
        parseInt(item.status || "0") > 1 ||
        parseInt(item.is_live || "0") === 1
      ) {
        this.props.history.push({
          pathname: "/my-contests",
          state: {
            from:
              parseInt(item.is_live || "0") === 1
                ? "lobby-live"
                : "lobby-completed",
          },
        });
      } else {
        this.gotoDetails(item);
      }
    } else {
      this.goToSignup();
    }
  };

  goToSignup = () => {
    this.props.history.push("/signup");
  };

  gotoDetails = (data) => {
    data["collection_master_id"] = data.collection_id;
    let name =
      data.category_id.toString() === "1"
        ? "Daily"
        : data.category_id.toString() === "2"
        ? "Weekly"
        : "Monthly";
    let contestListingPath =
      "/stock-fantasy/contest/" + data.collection_id + "/" + name;
    let CLPath =
      contestListingPath.toLowerCase() +
      "?sgmty=" +
      btoa(Constants.SELECTED_GAMET);
    this.props.history.push({
      pathname: CLPath,
      state: { LobyyData: data, lineupPath: CLPath },
    });
  };

  showLFilter = () => {
    this.setState({
      showFilter: true,
    });
  };

  hideLFilter = () => {
    this.setState({
      showFilter: false,
    });
  };

  setFilter = (
    minCT,
    maxCT,
    minFee,
    maxFee,
    minEnt,
    maxEnt,
    minWin,
    maxWin,
    isFilerApplied
  ) => {
    // const {minCT, maxCT, minFee, maxFee, minEnt, maxEnt, minWin, maxWin} = this.state;
    // if(isfor == 1){ // 1 is for contest time
    //     this.setState({
    //         minCT: minCT == minVal ? "" : minVal,
    //         maxCT: maxCT == maxVal ? "" : maxVal,
    //         isFilerApplied: true
    //     })
    // }
    // else if(isfor == 2){ // 2 is for entry fee
    //     this.setState({
    //         minFee: minFee == minVal ? "" : minVal,
    //         maxFee: maxFee == maxVal ? "" : maxVal,
    //         isFilerApplied: true
    //     })
    // }
    // else if(isfor == 3){ //3 is for entries
    //     this.setState({
    //         minEnt: minEnt == minVal ? "" : minVal,
    //         maxEnt: maxEnt == maxVal ? "" : maxVal,
    //         isFilerApplied: true
    //     })
    // }
    // else if(isfor == 4){ //3 is for winning
    //     this.setState({
    //         minWin: minWin == minVal ? "" : minVal,
    //         maxWin: maxWin == maxVal ? "" : maxVal,
    //         isFilerApplied: true
    //     })
    // }
    // else if(isfor == 0){ //0 to clear filter
    this.setState(
      {
        minCT: minCT,
        maxCT: maxCT,
        minFee: minFee,
        maxFee: maxFee,
        minEnt: minEnt,
        maxEnt: maxEnt,
        minWin: minWin,
        maxWin: maxWin,
        isFilerApplied: isFilerApplied,
        dayFilter:
          isFilerApplied && this.state.dayFilter == ""
            ? "1"
            : this.state.dayFilter,
      },
      () => {
        this.hideLFilter();
        this.getLobbyFixture();
      }
    );
    // }
  };

  ApplyFilter = () => {
    this.hideLFilter();
    this.getLobbyFixture();
  };

  addDayFilter = (val) => {
    if (val == 1) {
      let today = Utilities.getFormatedDate({ date: new Date(), format: "" });
      today = today.split("T")[0];
      this.setState(
        {
          dayFilter: val,
          fromDate: today,
          toDate: today,
        },
        () => {
          this.getLobbyFixture();
        }
      );
    } else if (val == 2) {
      const TD = new Date();
      let tomorrow = new Date();
      tomorrow.setDate(TD.getDate() + 1);
      tomorrow = Utilities.getFormatedDate({ date: tomorrow, format: "" });
      tomorrow = tomorrow.split("T")[0];
      this.setState(
        {
          dayFilter: val,
          fromDate: tomorrow,
          toDate: tomorrow,
          // minCT: "",
          // maxCT: "",
          // minFee: "",
          // maxFee: "",
          // minEnt: "",
          // maxEnt: "",
          // minWin: "",
          // maxWin: "",
          // isFilerApplied: false
        },
        () => {
          this.getLobbyFixture();
        }
      );
    } else {
      this.setState(
        {
          dayFilter: val,
          fromDate: "",
          toDate: "",
          minCT: "",
          maxCT: "",
          minFee: "",
          maxFee: "",
          minEnt: "",
          maxEnt: "",
          minWin: "",
          maxWin: "",
          isFilerApplied: false,
        },
        () => {
          this.getLobbyFixture();
        }
      );
    }
  };

  goToLineup = (ContestItem, isFromMyTeam) => {
    // let urlData = this.state.LobyyData;
    let dateformaturl = Utilities.getUtcToLocal(ContestItem.scheduled_date);
    dateformaturl = new Date(dateformaturl);
    let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2);
    let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2);
    dateformaturl =
      dateformaturlDate +
      "-" +
      dateformaturlMonth +
      "-" +
      dateformaturl.getFullYear();
    let lineupPath = "";
    lineupPath =
      "/stock-prediction/lineup/" +
      ContestItem.contest_id +
      "-" +
      dateformaturl +
      "?tab=1";

    let myTeam = {};
    if (isFromMyTeam) {
      myTeam = { from: "MyTeams", isFromMyTeams: true, isFrom: "MyTeams" };
    }
    this.props.history.push({
      pathname: lineupPath.toLowerCase(),
      state: {
        FixturedContest: ContestItem,
        LobyyData: ContestItem,
        resetIndex: 1,
        ...myTeam,
      },
    });
  };

  check(event, FixturedContestItem) {
    // if( WSManager.loggedIn()){
    //     this.getUserLineUpListApi(FixturedContestItem)
    // }
    // WSManager.loggedIn() ? this.joinGame(event, FixturedContestItem) : this.goToSignup()
    WSManager.loggedIn()
      ? this.getUserLineUpListApi(event, FixturedContestItem)
      : this.goToSignup();
  }

  /**
   * @description Method called when user loggedin  and click on join game
   * @param {*} event - click event
   * @param {*} FixturedContestItem - contest model on which user click
   * @param {*} teamListData - user created team list of same collection
   */
  joinGame(event, FixturedContestItem, teamListData) {
    if (event) {
      event.stopPropagation();
    }

    if (checkBanState(FixturedContestItem, CustomHeader)) {
      WSManager.clearLineup();
      if (
        this.state.TeamList.length > 0 ||
        (teamListData && teamListData != null && teamListData.length > 0)
      ) {
        this.setState({
          showConfirmationPopUp: true,
          FixtureData: FixturedContestItem,
        });
      } else {
        if (
          this.state.TotalTeam.length ===
          parseInt(Utilities.getMasterData().a_teams)
        ) {
          this.openAlert();
        } else {
          this.goToLineup(FixturedContestItem);
        }
      }
      WSManager.setFromConfirmPopupAddFunds(false);
    }
  }

  getUserLineUpListApi = async (event, item, isFromCD) => {
    if (event) {
      event.stopPropagation();
    }
    if (
      !isFromCD &&
      !Utilities.minuteDiffValueStock({ date: item.game_starts_in }, -5)
    ) {
      this.setState({
        showTimeOutAlert: true,
      });
      return;
    } else {
      let param = {
        collection_id: item.collection_id,
      };
      var api_response_data = await getSPUserLineupList(param);
      if (api_response_data.response_code === WSC.successCode) {
        this.setState(
          {
            TotalTeam: api_response_data.data,
            TeamList: api_response_data.data,
            userTeamListSend: api_response_data.data,
          },
          () => {
            if (!isFromCD) {
              this.joinGame(event, item);
            }
            if (this.state.userTeamListSend) {
              let tempList = [];
              this.state.userTeamListSend.map((data, key) => {
                tempList.push({ value: data, label: data.team_name });
                return "";
              });
              this.setState({ userTeamListSend: tempList });
            }
          }
        );
      }
    }
  };

  openAlert = () => {
    this.setState({
      showAlert: true,
    });
  };

  hideAlert = () => {
    this.setState({
      showAlert: false,
    });
  };

  /**
   * @description method to display contest detail model
   * @param data - contest model data for which contest detail to be shown
   * @param activeTab -  tab to be open on detail, screen
   * @param event -  click event
   */
  ContestDetailShow = (data, activeTab, event) => {
    event.stopPropagation();
    event.preventDefault();
    this.getUserLineUpListApi(event, data, true);
    this.setState({
      showContestDetail: true,
      FixtureData: data,
      activeTab: activeTab,
    });
  };
  /**
   * @description method to hide contest detail model
   */
  ContestDetailHide = () => {
    this.setState({
      showContestDetail: false,
    });
  };

  /**
   * @description method to submit user entry to join contest
   * if user is guest then loggin screen will display else go to roster to select play to create new team
   */
  onSubmitBtnClick = () => {
    if (!WSManager.loggedIn()) {
      setTimeout(() => {
        this.props.history.push({ pathname: "/signup" });
        Utilities.showToast(AL.Please_Login_Signup_First, 3000);
      }, 10);
    } else {
      if (
        Constants.SELECTED_GAMET == Constants.GameType.StockPredict &&
        !Utilities.minuteDiffValueStock(
          { date: this.state.FixtureData.game_starts_in },
          -5
        )
      ) {
        this.ContestDetailHide();
        this.showTimeOutModal();
      } else {
        if (checkBanState(this.state.FixtureData, CustomHeader)) {
          if (
            this.state.TeamList != null &&
            !_isUndefined(this.state.TeamList) &&
            this.state.TeamList.length > 0
          ) {
            this.ContestDetailHide();
            setTimeout(() => {
              this.setState({
                showConfirmationPopUp: true,
                FixtureData: this.state.FixtureData,
              });
            }, 200);
          } else {
            this.goToLineup(this.state.FixtureData);
          }
        } else {
          this.ContestDetailHide();
        }
      }
    }
  };

  callAfterAddFundPopup() {
    if (WSManager.getFromConfirmPopupAddFunds()) {
      WSManager.setFromConfirmPopupAddFunds(false);
      setTimeout(() => {
        var contestData = WSManager.getContestFromAddFundsAndJoin();
        this.joinGame(
          null,
          contestData.FixturedContestItem,
          contestData.TeamsSortedArray
        );
      }, 100);
    }
  }

  ConfirmEvent = (dataFromConfirmPopUp) => {
    if (
      !Utilities.minuteDiffValueStock(
        { date: dataFromConfirmPopUp.FixturedContestItem.game_starts_in },
        -5
      )
    ) {
      this.ConfirmatioPopUpHide();
      this.showTimeOutModal();
    } else if (
      dataFromConfirmPopUp.lineUpMasterIdArray &&
      dataFromConfirmPopUp.lineUpMasterIdArray.length > 1
    ) {
      this.JoinGameApiCall(dataFromConfirmPopUp);
    } else if (
      (dataFromConfirmPopUp.selectedTeam.lineup_master_id != null &&
        dataFromConfirmPopUp.selectedTeam.lineup_master_id == "") ||
      dataFromConfirmPopUp.selectedTeam == ""
    ) {
      Utilities.showToast(AL.SELECT_NAME_FIRST, 1000);
    } else {
      this.JoinGameApiCall(dataFromConfirmPopUp);
    }
  };
  JoinGameApiCall = (dataFromConfirmPopUp) => {
    var currentEntryFee = 0;
    currentEntryFee = dataFromConfirmPopUp.entryFeeOfContest;
    if (
      (dataFromConfirmPopUp.FixturedContestItem.currency_type == 2 &&
        parseInt(currentEntryFee) <=
          parseInt(dataFromConfirmPopUp.balanceAccToMaxPercent)) ||
      (dataFromConfirmPopUp.FixturedContestItem.currency_type != 2 &&
        parseFloat(currentEntryFee) <=
          parseFloat(dataFromConfirmPopUp.balanceAccToMaxPercent))
    ) {
      this.CallJoinGameApi(dataFromConfirmPopUp);
    } else {
      if (dataFromConfirmPopUp.FixturedContestItem.currency_type == 2) {
        if (Utilities.getMasterData().allow_buy_coin == 1) {
          WSManager.setFromConfirmPopupAddFunds(true);
          WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp);
          WSManager.setPaymentCalledFrom("ContestListing");
          this.props.history.push({
            pathname: "/buy-coins",
            contestDataForFunds: dataFromConfirmPopUp,
            fromConfirmPopupAddFunds: true,
            state: { isFrom: "contestList", isStockF: true, isStockPF: true },
          });
        } else {
          this.props.history.push({
            pathname: "/earn-coins",
            state: { isFrom: "lineup-flow", isStockF: true, isStockPF: true },
          });
        }
      } else {
        WSManager.setFromConfirmPopupAddFunds(true);
        WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp);
        WSManager.setPaymentCalledFrom("ContestListing");
        this.props.history.push({
          pathname: "/add-funds",
          contestDataForFunds: dataFromConfirmPopUp,
          fromConfirmPopupAddFunds: true,
          state: {
            amountToAdd: dataFromConfirmPopUp.AmountToAdd,
            isStockF: true,
            isStockPF: true,
          },
          isReverseF: this.state.showRF,
        });
      }
    }
  };

  CallJoinGameApi(dataFromConfirmPopUp) {
    let ApiAction = stockJoinContest;
    let param = {
      contest_id: dataFromConfirmPopUp.FixturedContestItem.contest_id,
      promo_code: dataFromConfirmPopUp.promoCode,
      device_type: window.ReactNativeWebView
        ? WSC.deviceTypeAndroid
        : WSC.deviceType,
      lineup_master_id:
        dataFromConfirmPopUp.selectedTeam.value.lineup_master_id,
    };
    // if (dataFromConfirmPopUp.lineUpMasterIdArray && dataFromConfirmPopUp.lineUpMasterIdArray.length > 1) {
    //     ApiAction = joinStockContestWithMultiTeam;
    //     let resultLineup = dataFromConfirmPopUp.lineUpMasterIdArray.map(a => a.lineup_master_id);
    //     param['lineup_master_id'] = resultLineup
    // } else {
    // param['lineup_master_id'] = dataFromConfirmPopUp.selectedTeam.value.lineup_master_id
    // }

    let contestUid = dataFromConfirmPopUp.FixturedContestItem.contest_unique_id;
    let contestAccessType =
      dataFromConfirmPopUp.FixturedContestItem.contest_access_type;
    let isPrivate = dataFromConfirmPopUp.FixturedContestItem.is_private;

    ApiAction(param).then((responseJson) => {
      if (responseJson.response_code == WSC.successCode) {
        if (contestAccessType == "1" || isPrivate == "1") {
          let deviceIds = [];
          deviceIds = responseJson.data.user_device_ids;
          WSManager.updateFirebaseUsers(contestUid, deviceIds);
        }

        this.ConfirmatioPopUpHide();
        this.setState({
          lineup_master_idArray: [],
          lineup_master_id: "",
        });
        setTimeout(() => {
          WSManager.googleTrackDaily(
            WSC.GA_PROFILE_ID,
            "stock_contestjoindaily"
          );

          WSManager.googleTrackDaily(
            WSC.GA_PROFILE_ID,
            "stock_contestjoindaily"
          );
          this.ThankYouModalShow();
        }, 300);
        WSManager.clearLineup();
      } else {
        if (
          Utilities.getMasterData().allow_self_exclusion == 1 &&
          responseJson.data &&
          responseJson.data.self_exclusion_limit == 1
        ) {
          this.ConfirmatioPopUpHide();
          this.showUJC();
        } else {
          Utilities.showToast(
            responseJson.global_error != ""
              ? responseJson.global_error
              : responseJson.message,
            2000
          );
        }
      }
    });
  }

  createTeamAndJoin = (dataFromConfirmFixture, dataFromConfirmLobby) => {
    if (checkBanState(dataFromConfirmFixture, CustomHeader)) {
      WSManager.clearLineup();
      this.goToLineup(dataFromConfirmFixture);
    }
  };

  ThankYouModalShow = (data) => {
    this.setState({
      showThankYouModal: true,
    });
  };

  ThankYouModalHide = () => {
    this.setState({
      showThankYouModal: false,
    });
  };

  joinMore = () => {
    this.ThankYouModalHide();
    this.getLobbyFixture();
  };

  editJoinedContest = (e, item) => {
    if (item.is_upcoming != 1 || item.is_live == 1) {
      this.props.history.push({
        pathname: "/my-contests",
        state: { from: item.is_live == 1 ? "lobby-live" : "lobby-completed" },
      });
    } else {
      let dateformaturl = Utilities.getUtcToLocal(item.scheduled_date);
      dateformaturl = new Date(dateformaturl);
      let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2);
      let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2);
      dateformaturl =
        dateformaturlDate +
        "-" +
        dateformaturlMonth +
        "-" +
        dateformaturl.getFullYear();
      let lineupPath = "";
      lineupPath =
        "/stock-prediction/lineup/" +
        item.contest_id +
        "-" +
        dateformaturl +
        "?tab=1";

      this.props.history.push({
        pathname: lineupPath.toLowerCase(),
        state: {
          SelectedLineup: item.lineup_master_id,
          FixturedContest: item,
          team: item.team_name,
          LobyyData: item,
          resetIndex: 1,
          teamitem: item,
          rootDataItem: item,
          from: "editView",
          isFromMyTeams: true,
          collection_master_id: item.collection_id,
        },
      });
    }
  };

  seeMyContest = () => {
    this.props.history.push({
      pathname: "/my-contests",
      state: { from: "SelectCaptain" },
    });
  };

  showTimeOutModal = () => {
    this.setState({
      showTimeOutAlert: true,
    });
  };

  hideTimeOutModal = () => {
    this.setState({
      showTimeOutAlert: false,
    });
  };

  timerCallback = (item) => {
    // let fArray = _filter(this.state.FixtureList, (obj) => {
    //     return item.contest_unique_id != obj.contest_unique_id
    // })
    // console.log('fArray',fArray);
    // this.setState({
    //     FixtureList: fArray
    // })
    this.getLobbyFixture();
  };

  render() {
    const {
      BannerList,
      ShimmerList,
      FixtureList,
      isListLoading,
      showHTP,
      showShadow,
      showFilter,
      filterList,
      dayFilter,
      showRules,
      showAlert,
      showContestDetail,
      activeTab,
      TotalTeam,
      userTeamListSend,
      showConfirmationPopUp,
      FixtureData,
      showThankYouModal,
      MyStockList,
      isFilerApplied,
      showTimeOutAlert,
      profileData
    } = this.state;
    let bannerLength = BannerList.length;
    var showLobbySportsTab = process.env.REACT_APP_LOBBY_SPORTS_ENABLE == 1 ? true : false
    return (
      <MyContext.Consumer>
        {(context) => (
          <div className="web-container sp-container">
            <MetaComponent page="lobby" />
            {/* <div className="hdr-strp">
                            <a
                                href
                                onClick={(e) => { this.showHTPModal(e) }}
                            >
                                <i className="icon-question"></i>
                                {AL.How_to_Play}
                            </a>
                        </div> */}

            {/* <div className="header-fixed-strip">
              <div className="strip-content">
                <span>{AL.STOCK_PREDICT}</span>
                <a
                  href
                   onClick={(e) => { this.showHTPModal(e) }}
                >
                  {AL.How_to_Play}?
                </a>
              </div>
            </div> */}
            <div>
              {bannerLength > 0 && (
                <div
                  className={
                    bannerLength > 0 ? " animation" : "banner-v"
                  }
                >
                  {bannerLength > 0 && (
                    <LobbyBannerSlider
                      BannerList={BannerList}
                      redirectLink={this.redirectLink.bind(this)}
                      isStock
                    />
                  )}
                </div>
              )}
              <div className={"header-fixed-strip mt-0" + (showLobbySportsTab ? " header-fixed-strip-2" : '')}>
                                <div className={"strip-content" + (showShadow ? ' strip-content-shadow' : '')}>
                                    <span className='head-bg-strip'>{AL.STOCK_PREDICT}</span>
                                    <a className='decoration-under'
                                        href
                                        onClick={(e) => { this.showDFSHTPModal(e) }}
                                    >
                                        {AL.HOW_TO_PLAY_FREE}
                                    </a>
                                </div>
                            </div>

              {WSManager.loggedIn() && MyStockList && MyStockList.length > 0 && (
                <div className="my-lobby-fixture-wrap">
                  <div className="top-section-heading">
                    <span className="txt-sc">{AL.MY_CONTEST} </span>
                    <a href onClick={() => this.goToMyContest()}>
                      {AL.VIEW} {AL.All}
                    </a>
                  </div>
                  <SPMyContestSlider
                    MyContestList={MyStockList}
                    showRulesModal={this.showRulesModal}
                    onEdit={this.editJoinedContest.bind(this)}
                  />
                </div>
              )}

              <div className="upcoming-lobby-contest">
                <div className="top-section-heading">
                  <span className="txt-sc">{AL.UPCOMING}</span>
                  <div className="act-sec">
                    <a
                      href
                      className={`btn ${dayFilter == "" ? " active" : ""}`}
                      onClick={() => this.addDayFilter("")}
                    >
                      <span>{AL.All}</span>
                    </a>
                    <a
                      href
                      className={`btn ${dayFilter == "1" ? " active" : ""}`}
                      onClick={() => this.addDayFilter(1)}
                    >
                      <span>{AL.TODAY}</span>
                    </a>
                    <a
                      href
                      className={`btn ${dayFilter == "2" ? " active" : ""}`}
                      onClick={() => this.addDayFilter(2)}
                    >
                      <span>{AL.TOMORROW}</span>
                    </a>
                    <a href className="btn" onClick={() => this.showLFilter()}>
                      <i className="icon-filter"></i>
                    </a>
                  </div>
                </div>
                <Row className={`sp-up-fx ${bannerLength > 0 ? "" : " xmt15"}`}>
                  <Col sm={12}>
                    <Row>
                      <Col sm={12}>
                        {FixtureList &&
                          FixtureList.length > 0 &&
                          FixtureList.map((item) => {
                            return (
                              <SPFixtureCard
                              {...this.props}
                              profileData= {profileData}
                                key={item.contest_id}
                                data={{
                                  isFrom: "SPLobby",
                                  item,
                                }}
                                goToLineup={this.goToLineup}
                                showRulesModal={this.showRulesModal}
                                check={this.check.bind(this)}
                                ContestDetailShow={this.ContestDetailShow.bind(
                                  this
                                )}
                                timerCallback={(item) =>
                                  this.timerCallback(item)
                                }
                              />
                            );
                          })}
                        {FixtureList.length === 0 &&
                          isListLoading &&
                          ShimmerList.map((item, index) => {
                            return <LobbyShimmer key={index} />;
                          })}
                        {FixtureList.length === 0 &&
                          !isListLoading &&
                          !isFilerApplied &&
                          dayFilter == "" && (
                            <div className="stay-tuned-card d-flx-fx">
                              {/* <img
                                className="bg-graph"
                                src={Images.daily_g}
                                alt=""
                              /> */}
                              <div className="label">{AL.STAY_TUNED}</div>
                              <div className="open-at">
                                {AL.STOCK_OPEN_SHORTLY}
                              </div>
                              <img src={Images.daily_g} alt="" />
                              <div className="link-sec">
                                {AL.SEE}{" "}
                                <a href onClick={(e) => this.showHTPModal(e)}>
                                  {AL.STOCK_HOW_TO_PLAY}
                                </a>{" "}
                                {AL.STOCK_FANTASY}
                              </div>
                            </div>
                          )}
                        {FixtureList.length === 0 &&
                          !isListLoading &&
                          (isFilerApplied || dayFilter != "") && (
                            <NoDataView
                              BG_IMAGE={Images.no_data_bg_image}
                              // CENTER_IMAGE={
                              //   Constants.DARK_THEME_ENABLE
                              //     ? Images.DT_BRAND_LOGO_FULL
                              //     : Images.BRAND_LOGO_FULL
                              // }
                              CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                              MESSAGE_1={AL.NO_DATA_AVAILABLE}
                              BUTTON_TEXT={AL.RESET + " " + AL.FILTERS}
                              onClick={this.showLFilter}
                            />
                          )}
                      </Col>
                    </Row>
                  </Col>
                </Row>
              </div>
            </div>
            <div
              className="stats-fixed-btn"
              onClick={() =>
                this.props.history.push("/stock-fantasy/statistics")
              }
            >
              <i className="icon-statistics" />
              <span>{AL.STATS}</span>
            </div>
            {showHTP && (
              <Suspense fallback={<div />}>
                <SPFHTP
                  mShow={showHTP}
                  mHide={this.hideHTPModal}
                  stockSetting={this.state.stockSetting}
                />
              </Suspense>
            )}
            {showRules && (
              <Suspense fallback={<div />}>
                <SPRules mShow={showRules} mHide={this.hideRulesModal} />
              </Suspense>
            )}
            {showFilter && (
              <Suspense fallback={<div />}>
                <SPLobbyFilter
                  isShow={showFilter}
                  isHide={this.hideLFilter}
                  filterList={filterList}
                  setFilter={this.setFilter}
                  selFilVal={{
                    minCT: this.state.minCT,
                    maxCT: this.state.maxCT,
                    minFee: this.state.minFee,
                    maxFee: this.state.maxFee,
                    minEnt: this.state.minEnt,
                    maxEnt: this.state.maxEnt,
                    minWin: this.state.minWin,
                    maxWin: this.state.maxWin,
                  }}
                  ApplyFilter={this.ApplyFilter}
                />
              </Suspense>
            )}
            {showAlert && (
              <MyAlert
                isMyAlertShow={showAlert}
                hidemodal={() => this.hideAlert()}
                isFrom={"contest-listing"}
                message={(AL.YOU_CAN_CREATE_ONLY_10TEAMS || "").replace(
                  "10",
                  Utilities.getMasterData().a_teams
                )}
              />
            )}
            {showContestDetail && (
              <ContestDetailModal
                IsContestDetailShow={showContestDetail}
                onJoinBtnClick={this.onSubmitBtnClick}
                IsContestDetailHide={this.ContestDetailHide}
                OpenContestDetailFor={FixtureData}
                activeTabIndex={activeTab}
                isStockF={true}
                isStockPF={true}
                LobyyData={FixtureData}
                {...this.props}
              />
            )}
            {showConfirmationPopUp && (
              <ConfirmationPopup
                IsConfirmationPopupShow={showConfirmationPopUp}
                IsConfirmationPopupHide={this.ConfirmatioPopUpHide}
                TeamListData={userTeamListSend}
                TotalTeam={TotalTeam}
                FixturedContest={FixtureData}
                ConfirmationClickEvent={this.ConfirmEvent}
                CreateTeamClickEvent={this.createTeamAndJoin}
                lobbyDataToPopup={this.state.LobyyData}
                fromContestListingScreen={true}
                createdLineUp={""}
                selectedLineUps={this.state.lineup_master_idArray}
                showDownloadApp={this.showDownloadApp}
                isStockF={true}
                isStockPF={true}
              />
            )}

            {showThankYouModal && (
              <Thankyou
                ThankyouModalShow={this.ThankYouModalShow}
                ThankYouModalHide={this.ThankYouModalHide}
                goToLobbyClickEvent={this.joinMore}
                seeMyContestEvent={this.seeMyContest}
                isStock={true}
              />
            )}
            {showTimeOutAlert && (
              <MyAlert
                isMyAlertShow={showTimeOutAlert}
                hidemodal={() => this.hideTimeOutModal()}
                isFrom={"TimeOutAlert"}
                message={AL.JOIN_BEFORE_5MIN}
              />
            )}
          </div>
        )}
      </MyContext.Consumer>
    );
  }
}

export default SPLobby;
