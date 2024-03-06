import React from "react";
import { MyContext } from "../../InitialSetup/MyProvider";
import * as AppLabels from "../../helper/AppLabels";
import { WhatsappShareButton } from 'react-share';
import WSManager from "../../WSHelper/WSManager";
import * as WSC from "../../WSHelper/WSConstants";
import { CopyToClipboard } from 'react-copy-to-clipboard';
import { Utilities, checkBanState, _filter, convertToTimestamp, parseURLDate } from '../../Utilities/Utilities';
import * as Constants from "../../helper/Constants";
import { getShortURL, saveShortURL, getContestShareCode, getUserTeams, getMultigameUserTeams, joinContest, getStockInviteCode, getStockUserAllTeams, stockJoinContest, getContestShareCodeLF, getUserAadharDetail } from '../../WSHelper/WSCallings';
import ContestDetailModal from '../../Modals/ContestDetail';
import { AppSelectedSport, SELECTED_GAMET, GameType } from '../../helper/Constants';
import ls from 'local-storage';
import SkipConfirmationModal from "./SkipConfirmationModal";
import ConfirmationPopup from "../../Modals/ConfirmationPopup";
import Thankyou from "../../Modals/Thankyou";
import CustomHeader from '../../components/CustomHeader';
import Images from "../../components/images";
import { LFContestDetails } from "../../Component/LiveFantasy";

var base_url = WSC.baseURL;
var referalCode = "";
var userProfileDataFromLS = "";
var mContext = null;
export default class SharePrivateContest extends React.Component {
  constructor(props) {

    super(props);
    let LobyyData = this.props.location.state.LobyyData;
    let match_list = LobyyData.match_list.map((item, index) => {
      item.game_starts_in = convertToTimestamp(LobyyData.season_scheduled_date)
      return item
    })

    this.state = {
      LobyyData: {...LobyyData, match_list, game_starts_in: convertToTimestamp(LobyyData.season_scheduled_date)} || null,
      shareURL: '',
      contestCode: "",
      FixturedContest: {...this.props.location.state.FixturedContest, game_starts_in: convertToTimestamp(LobyyData.season_scheduled_date)},
      hostEarn: this.props.location.state.hostEarn,
      isSecIn: this.props.location.state.isSecIn,
      isStockF: this.props.location.state.isStockF,
      userTeamListSend: [],
      showSkipConfirmModal: false,
      showConfirmationPopUp: false,
      aadharData: ''
    };
  }

  componentDidMount() {
    referalCode = WSManager.getUserReferralCode();
    userProfileDataFromLS = WSManager.getProfile();
    // this.callGetShortenUrlApi();
    this.createAndSetUrls()
    this.GetInviteCodeApi()

    if (WSManager.loggedIn() && Utilities.getMasterData().a_aadhar == "1") {
      if (WSManager.getProfile().aadhar_status != 1) {
        getUserAadharDetail().then((responseJson) => {
          if (responseJson && responseJson.response_code == WSC.successCode) {
            this.setState({ aadharData: responseJson.data }, () => {
              WSManager.updateProfile(this.state.aadharData)
            });
          }
        })
      }
      else {
        let aadarData = {
          'aadhar_status': WSManager.getProfile().aadhar_status,
          "aadhar_id": WSManager.getProfile().aadhar_detail.aadhar_id
        }
        this.setState({ aadharData: aadarData });
      }
    }

  }

  onCopyLink = () => {
    this.showCopyToast(AppLabels.Link_has_been_copied);
    this.setState({ copied: true })
  }

  showCopyToast = (message) => {
    Utilities.showToast(message, 2000)
  }

  createAndSetUrls() {
    let id = this.state.FixturedContest.contest_unique_id;
    let mURL = ''
    if (this.state.isStockF || Constants.SELECTED_GAMET === Constants.GameType.StockFantasy) {
      mURL = base_url + 'stock-fantasy/share-contest/' + id;
    } else if (Constants.SELECTED_GAMET === Constants.GameType.MultiGame) {
      mURL = base_url + Utilities.getSelectedSportsForUrl().toLowerCase() + "/multigame-contest/" + id;
    }
    else if (Constants.SELECTED_GAMET == Constants.GameType.LiveFantasy) {
      mURL = base_url + "live-fantasy/share-contest/" + id;
    }
    else {
      mURL = base_url + Utilities.getSelectedSportsForUrl().toLowerCase() + "/contest/" + id;
    }
    var shareURL = mURL + "?referral=" + referalCode;
    if (Constants.SELECTED_GAMET) {
      shareURL = shareURL + "&sgmty=" + btoa(Constants.SELECTED_GAMET)
    }
    this.setState({ shareURL: shareURL });
  }

  callGetShortenUrlApi() {
    let param = {
      'url_type': Constants.SELECTED_GAMET == Constants.GameType.LiveFantasy ? "7" : "2",
      'url_type_id': this.state.FixturedContest.contest_id,
    }

    getShortURL(param).then((responseJson) => {
      if (responseJson && responseJson.response_code === WSC.successCode) {
        this.setState({
          shortUrls: responseJson.data
        })

        if (responseJson.data.length > 0) {
          this.createAndSetUrls(responseJson.data);
        } else {
          this.callGetShortenUrlDataObjIsEmpty();
        }
      }
    })
  }

  callGetShortenUrlDataObjIsEmpty() {
    var urlsArray = []
    var sourcetype = ["1", "2", "3", "4", "6", "7"]
    var i;
    for (i = 0; i < 6; i++) {
      let param = {
        'url_type': Constants.SELECTED_GAMET == Constants.GameType.LiveFantasy ? "7" : "2",
        "url": "?ref=" + referalCode + "&source_type=" + sourcetype[i] + "&affiliate_type=" + 1,
        "source_type": sourcetype[i],
        'url_type_id': this.state.FixturedContest.contest_id,
      }
      urlsArray.push(param)
    }
    let param = {
      "url_data": urlsArray
    }
    saveShortURL(param).then((responseJson) => {
      if (responseJson.response_code === WSC.successCode) {
        this.setState({
          shortUrls: responseJson.data
        })
      }
    })
  }

  GetInviteCodeApi() {
    let param = {
      "contest_id": this.state.FixturedContest.contest_id
    }
    let apiAct = this.state.isStockF ? getStockInviteCode : Constants.SELECTED_GAMET == Constants.GameType.LiveFantasy ? getContestShareCodeLF : getContestShareCode
    apiAct(param).then((responseJson) => {
      if (responseJson.response_code === WSC.successCode) {
        this.setState({
          contestCode: responseJson.data
        })
      }
    })
  }

  onCopyCode = () => {
    this.showCopyToast(AppLabels.MSZ_COPY_CODE);
    this.setState({ copied: true })
  }

  joinNow() {
    let data = this.state.FixturedContest;
    data['collection_master_id'] = data.collection_master_id || data.collection_id || this.state.LobyyData.collection_id;
    data['category_id'] = data.category_id || this.state.LobyyData.category_id;
    this.setState({
      FixturedContest: data
    });
    this.ContestDetailShow(data, 2);
    if (SELECTED_GAMET != GameType.LiveFantasy) {
      this.getUserLineUpListApi();

    }
  }


  ContestDetailShow = (data, activeTab) => {
    this.setState({
      showContestDetail: true,
      contestData: data,
      activeTab: activeTab,
    });
  }

  ContestDetailHide = () => {
    this.setState({
      showContestDetail: false,
    });
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

  gotoStockLineup = (FixturedContestItem) => {
    if (!FixturedContestItem.collection_master_id && FixturedContestItem.collection_id) {
      FixturedContestItem['collection_master_id'] = FixturedContestItem.collection_id;
    } else if (!FixturedContestItem.collection_master_id) {
      FixturedContestItem['collection_master_id'] = this.state.LobyyData.collection_master_id;
    }
    let cat_id = FixturedContestItem.category_id || this.state.LobyyData.category_id || ''
    let name = cat_id.toString() === "1" ? 'Daily' : cat_id.toString() === "2" ? 'Weekly' : 'Monthly';
    let lineupPath;
    if (SELECTED_GAMET == Constants.GameType.StockFantasyEquity) {
      lineupPath = '/stock-fantasy-equity/lineup/' + name;
    }
    else {
      lineupPath = '/stock-fantasy/lineup/' + name;

    }
    this.props.history.push({
      pathname: lineupPath.toLowerCase(), state: {
        FixturedContest: FixturedContestItem,
        LobyyData: this.state.LobyyData || FixturedContestItem,
        resetIndex: 1,
        collection_master_id: FixturedContestItem.collection_master_id
      }
    })
  }

  onSubmitBtnClick = (data) => {
    WSManager.clearLineup();
    if (this.state.isStockF || Constants.SELECTED_GAMET === Constants.GameType.StockFantasy) {
      if (this.state.userTeamListSend.length > 0) {
        this.setState({ showContestDetail: false, showConfirmationPopUp: true })
      } else {
        this.gotoStockLineup(data)
      }
    } else {
      
      data['2nd_total'] = this.state.LobyyData['2nd_total'] || 0
      data['2nd_inning_date'] = this.state.LobyyData['2nd_inning_date'] || ''

      this.setState({ LobyyData: data })
      if (!WSManager.loggedIn()) {
        setTimeout(() => {
          this.props.history.push({ pathname: '/signup' })
          Utilities.showToast(AppLabels.Please_Login_Signup_First, 3000);
        }, 10);
      }
      else {
        if (this.state.userTeamListSend.length > 0) {
          this.setState({ showContestDetail: false, showConfirmationPopUp: true })
        }
        else {
          let urlData = data;
          let mdata = data.match_list[0]
          delete mdata['is_tournament'];
  
          urlData = {...data, ...mdata, playing_announce: urlData.match_list[0].playing_announce};

          // let urlData = data;
          // urlData = {...urlData, playing_announce: urlData.match_list[0].playing_announce}

          let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
          dateformaturl = new Date(dateformaturl);
          let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
          let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
          dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();

          let lineupPath = '';
          if (urlData.home) {
            lineupPath = '/lineup/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
            this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { LobyyData: data, FixturedContest: this.state.contestData, resetIndex: 1, collection_master_id: urlData.collection_master_id, current_sport: AppSelectedSport, isSecIn: this.state.isSecIn, isPlayingAnnounced: urlData.playing_announce} })
          }
          else {
            let pathurl = Utilities.replaceAll(urlData.collection_name, ' ', '_');
            lineupPath = '/lineup/' + pathurl + "-" + dateformaturl
            this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { LobyyData: data, FixturedContest: this.state.contestData, resetIndex: 1, collection_master_id: urlData.collection_master_id, current_sport: AppSelectedSport, isSecIn: this.state.isSecIn, isPlayingAnnounced: urlData.playing_announce } })
          }
        }
      }
    }
  }

  getStockUserLineUpListApi = async (CollectionData) => {
    let param = {
      "collection_id": CollectionData.collection_master_id || CollectionData.collection_id,
    }
    var api_response_data = await getStockUserAllTeams(param)
    if (api_response_data.response_code === WSC.successCode) {
      this.setState({
        userTeamListSend: api_response_data.data,
      }, () => {
        if (this.state.userTeamListSend) {
          let tempList = [];
          this.state.userTeamListSend.map((data, key) => {
            tempList.push({ value: data, label: data.team_name })
            return '';
          })
          this.setState({ userTeamListSend: tempList });
        }
      })

    }
  }

  getUserLineUpListApi = async () => {
    if (this.state.isStockF) {
      this.getStockUserLineUpListApi(this.state.FixturedContest)
    } else {
      let param = {
        "sports_id": AppSelectedSport,
        "collection_master_id": this.state.FixturedContest.collection_master_id,
      }
      this.setState({ isLoaderShow: true })
      let user_data = ls.get('profile');
      var user_unique_id = 0;
      if (user_data && user_data.user_unique_id) {
        user_unique_id = user_data.user_unique_id;
      }
      var api_response_data = SELECTED_GAMET === GameType.DFS ? await getUserTeams(param, user_unique_id) : await getMultigameUserTeams(param, user_unique_id);;
      if (api_response_data) {
        let tList = this.state.isSecIn ? _filter(api_response_data, (obj, idx) => {
          return obj.is_2nd_inning == "1";
        }) : _filter(api_response_data, (obj, idx) => {
          return (obj.is_reverse != "1" && obj.is_2nd_inning != "1")
        })
        this.setState({
          userTeamListSend: tList
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
    }
  }

  ConfirmEvent = (dataFromConfirmPopUp) => {

    if (dataFromConfirmPopUp.selectedTeam.lineup_master_id != null && dataFromConfirmPopUp.selectedTeam.lineup_master_id == "" || dataFromConfirmPopUp.selectedTeam == "") {
      Utilities.showToast(AppLabels.SELECT_NAME_FIRST, 1000);
    } else {
      var currentEntryFee = 0;
      currentEntryFee = dataFromConfirmPopUp.entryFeeOfContest;

      if (currentEntryFee <= dataFromConfirmPopUp.balanceAccToMaxPercent) {
        this.CallJoinGameApi(dataFromConfirmPopUp);
      } else {
        if (dataFromConfirmPopUp.FixturedContestItem.currency_type == 2) {
          if (Utilities.getMasterData().allow_buy_coin == 1) {
              WSManager.setFromConfirmPopupAddFunds(true);
              WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp)
              WSManager.setPaymentCalledFrom("ContestListing")
              this.props.history.push({ pathname: '/buy-coins', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, state: { isFrom: 'contestList' } });

          }
          else {
              this.props.history.push({ pathname: '/earn-coins', state: { isFrom: 'lineup-flow' } })
          }
        }
        else{
          WSManager.setFromConfirmPopupAddFunds(true);
          WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp)
          WSManager.setPaymentCalledFrom("SelectCaptainList")
          this.props.history.push({ pathname: '/add-funds', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, isSecIn: this.state.isSecIn, isStockF: true, state: { isStockF: true } });
        }
      }
    }
  }

  createTeamAndJoin = (dataFromConfirmFixture, dataFromConfirmLobby) => {
   
    if (checkBanState(dataFromConfirmFixture, CustomHeader)) {
      WSManager.clearLineup();
      if (this.state.isStockF || Constants.SELECTED_GAMET === Constants.GameType.StockFantasy) {
        this.gotoStockLineup(dataFromConfirmFixture)
      } else {
        let mdata = this.state.LobyyData.match_list[0]
        delete mdata['is_tournament'];

        let urlData = {...this.state.LobyyData, ...mdata};
        // let urlData = this.state.LobyyData;
        urlData = {...urlData, playing_announce: urlData.match_list[0].playing_announce}
        let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        dateformaturl = dateformaturl.getDate() + '-' + (dateformaturl.getMonth() + 1) + '-' + dateformaturl.getFullYear();

        if (urlData.home) {
          this.props.history.push({ pathname: '/lineup/' + urlData.home.toLowerCase() + "-vs-" + urlData.away.toLowerCase() + "-" + dateformaturl, state: { FixturedContest: dataFromConfirmFixture, LobyyData: this.state.LobyyData, resetIndex: 1, current_sport: Constants.AppSelectedSport, isSecIn: this.state.isSecIn, isPlayingAnnounced: urlData.playing_announce } })
        }
        else {
          
          let collectionName = Utilities.replaceAll(urlData.collection_name, ' ', '_');
          this.props.history.push({ pathname: '/lineup/' + collectionName.toLowerCase() + "-" + dateformaturl, state: { FixturedContest: dataFromConfirmFixture, LobyyData: this.state.LobyyData, resetIndex: 1, current_sport: Constants.AppSelectedSport, isSecIn: this.state.isSecIn, isPlayingAnnounced: urlData.playing_announce } })
        }
      }
    }
  }

  CallJoinGameApi(dataFromConfirmPopUp) {
    let param = {
      "contest_id": dataFromConfirmPopUp.FixturedContestItem.contest_id,
      "lineup_master_id": dataFromConfirmPopUp.selectedTeam.value.lineup_master_id,
      "promo_code": dataFromConfirmPopUp.promoCode,
      "device_type": window.ReactNativeWebView ? WSC.deviceTypeAndroid : WSC.deviceType
    }
    this.setState({ isLoaderShow: true })
    let contestUid = dataFromConfirmPopUp.FixturedContestItem.contest_unique_id
    let contestAccessType = dataFromConfirmPopUp.FixturedContestItem.contest_access_type;
    let isPrivate = dataFromConfirmPopUp.FixturedContestItem.is_private;
    let apiAct = joinContest;
    if (this.state.isStockF || Constants.SELECTED_GAMET === Constants.GameType.StockFantasy) {
      apiAct = stockJoinContest
    }
    apiAct(param).then((responseJson) => {
      if (responseJson.response_code == WSC.successCode) {
        if (process.env.REACT_APP_SINGULAR_ENABLE > 0) {
          let singular_data = {};
          singular_data.user_unique_id = WSManager.getProfile().user_unique_id;
          singular_data.contest_id = dataFromConfirmPopUp.FixturedContestItem.contest_id;
          singular_data.contest_date = dataFromConfirmPopUp.lobbyDataItem.season_scheduled_date;
          singular_data.fixture_name = dataFromConfirmPopUp.lobbyDataItem.collection_name;
          singular_data.entry_fee = dataFromConfirmPopUp.FixturedContestItem.entryFeeOfContest;

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
        this.ConfirmatioPopUpHide();
        if (contestAccessType == '1' || isPrivate == '1') {
          let deviceIds = [];
          deviceIds = responseJson.data.user_device_ids;
          WSManager.updateFirebaseUsers(contestUid, deviceIds);
        }
        setTimeout(() => {
          this.ThankYouModalShow()
        }, 300);
        WSManager.clearLineup();

      } else {
        Utilities.showToast(responseJson.global_error != "" ? responseJson.global_error : responseJson.message, 2000);
      }
    })
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

  onJoinClick = () => {
    mContext.setState({ showSkipConfirmModal: false })
    this.joinNow()
  }

  onJoinLaterClick = () => {
    mContext.setState({ showSkipConfirmModal: false })
    this.props.history.goBack(-1);
  }

  goToLobby = () => {
    if(SELECTED_GAMET == GameType.DFS){
      let data = this.state.FixturedContest
      if (data.match_list.length >= 1 && data.is_tour_game != 1) {
        data.home = data.match_list[0].home;
        data.home_flag = data.match_list[0].home_flag;
        data.away = data.match_list[0].away;
        data.away_flag = data.match_list[0].away_flag;
        data.league_name = data.league_name || data.match_list[0].league_name;
      } else if (data.is_tour_game == 1) {
          data.tournament_name_url = data.tournament_name.replaceAll(' ', '-')
          data.league_name_url = data.league_name.replaceAll(' ', '-')
      }

      let dateformaturl = parseURLDate(data.season_scheduled_date);
      this.setState({ LobyyData: data })
      let contestListingPath = Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest-listing/' + data.collection_master_id + '/' + (data.is_tour_game == 1 ? data.league_name_url : data.league_name) + '-' + (data.is_tour_game == 1 ? data.tournament_name_url : (data.home + "-vs-" + data.away)) + "-" + dateformaturl;
      let CLPath = contestListingPath.toLowerCase() + "?sgmty=" + btoa(Constants.SELECTED_GAMET)
      this.props.history.push({ pathname: CLPath, state: { FixturedContest: this.state.FixturedContest, LobyyData: this.state.LobyyData, lineupPath: CLPath } })
    }
    else{
      this.props.history.push({ pathname: '/' });
    }
  }

  seeMyContest = () => {
    this.props.history.push({ pathname: '/my-contests', state: { from: 'SelectCaptain'  } });
  }

  callNativeShare(type, url, detail) {
    let data = {
      action: 'social_sharing',
      targetFunc: 'social_sharing',
      type: type,
      url: url,
      detail: detail
    }
    window.ReactNativeWebView.postMessage(JSON.stringify(data));
  }

  render() {
    mContext = this;
    let { contestCode, showContestDetail, activeTab, FixturedContest, showSkipConfirmModal, hostEarn, showConfirmationPopUp, userTeamListSend,
      showThankYouModal, aadharData } = this.state;

    return (
      <MyContext.Consumer>
        {(context) => (
          <div className="web-container private-contest-share-contener ">
            {
              <div className='private-contest-parent'>
                <div onClick={() => this.setState({ showSkipConfirmModal: true })} className='skip_option'>{AppLabels.SKIP_STEP}</div>
                <div className='private-msg-label'>{AppLabels.SHARE_TITLE}</div>
                <div className='private-contest-name'>{FixturedContest && FixturedContest.contest_name} </div>
                {hostEarn && FixturedContest.currency_type != 2 && <div style={{ display: 'flex', flexDirection: 'row', justifyContent: 'center' }} className='private-contest-prize'>{AppLabels.WIN}{' '}{FixturedContest.currency_type == 2 ? <div style={{ display: 'flex', flexDirection: 'row', justifyContent: 'center', marginLeft: 5 }} > <img style={{ width: 15, height: 15, marginTop: 10 }} src={Images.IC_COIN} alt="" /> {hostEarn} </div> : hostEarn}</div>}
                <div className='private-contest-bottom-container'>
                  <div className='private-inner-container'>
                    <div className='share-msg-label'>{AppLabels.SHARE_MESSAGE1}<br />{AppLabels.SHARE_MESSAGE2}</div>
                    <div className='share-btn-container'>
                      <div className='share-btn-child-container'>
                        <div className='invite-opt-label'>{AppLabels.INVITE_VIA}</div>
                        {window.ReactNativeWebView ?
                          <div className='socil-btn-bg whatsapp'>
                            <span className="social-circle icon-whatsapp" onClick={() => this.callNativeShare('whatsapp', this.state.shareURL, AppLabels.YOUR_FRIEND_CONTEST + ' ' + userProfileDataFromLS.user_name
                              + ' ' + AppLabels.has_referred_you_on_contest +
                              " " + AppLabels.please_join_and_earn_prizes_text_contest + " : \n"
                              + (WSManager.getIsIOSApp() ? this.getIOSWhatsappURL() : this.state.shareURL) + " \n " + AppLabels.OR_CONTEST + " \n" + AppLabels.Join_through_the_following_text_contest + " " +
                              WSManager.getUserReferralCode() + " " + AppLabels.and_contest_code_contest + " " + contestCode + " " + AppLabels.MEDIUM_ADD + '\n\n' + AppLabels.Cheers + ",\n" + AppLabels.Team + " " + WSC.AppName)}>
                              <label className='invite-opt-name add-top-margin'>{AppLabels.INVITE_WHATSAPP}</label>
                            </span>
                          </div>
                          :
                          <React.Fragment>
                            <div className='socil-btn-bg whatsapp'>
                              <WhatsappShareButton className="social-circle icon-whatsapp"
                                url={
                                  AppLabels.YOUR_FRIEND_CONTEST + ' ' + userProfileDataFromLS.user_name
                                  + ' ' + AppLabels.has_referred_you_on_contest + ' ' +
                                  " " + AppLabels.please_join_and_earn_prizes_text_contest + " : \n"
                                  + this.state.shareURL + " \n " + AppLabels.OR_CONTEST + " \n" + AppLabels.Join_through_the_following_text_contest + " " +
                                  WSManager.getUserReferralCode() + " " + AppLabels.and_contest_code_contest + " " + contestCode + " " + AppLabels.MEDIUM_ADD + '\n\n' + AppLabels.Cheers + "," + '\n' + AppLabels.Team + " " + WSC.AppName
                                } />
                            </div>
                            <label className='invite-opt-name'>{AppLabels.INVITE_WHATSAPP}</label>
                          </React.Fragment>
                        }
                      </div>
                      <div className='middle-container'>
                        <div className='line'></div>
                        <div className='middle-circle'>{'or'}</div>
                        <div className='line bottom'></div>
                      </div>
                      <div className='share-btn-child-container'>
                        <div className='invite-opt-label'>{AppLabels.SHARE_LINK}</div>
                        <div className='socil-btn-bg link'>
                          <CopyToClipboard onCopy={this.onCopyLink} text={this.state.shareURL} className="social-circle icon-link">
                            <i className="icon-link"></i>
                          </CopyToClipboard>
                        </div>
                        <div className='invite-opt-name'>{AppLabels.INVITE_LINK}</div>
                      </div>

                    </div>
                    <div className='share-code-container'>
                      <div className='share-code-label'>{AppLabels.SHARE_CONTEST_CODE}</div>
                      <CopyToClipboard onCopy={this.onCopyCode} text={contestCode}>
                        <div className='contest-code-container'><span>{contestCode}</span><i className='icon-copy-ic'></i></div>
                      </CopyToClipboard>
                    </div>
                    <div onClick={() => this.joinNow()} className='join-contest-btn'>
                      <span>{AppLabels.JOIN_THIS_CONTEST}</span>
                    </div>

                  </div>
                </div>
              </div>
            }
            {showContestDetail &&
              <ContestDetailModal
                showPCError={true}
                LobyyData={FixturedContest}
                IsContestDetailShow={showContestDetail}
                onJoinBtnClick={this.onSubmitBtnClick}
                IsContestDetailHide={this.ContestDetailHide}
                OpenContestDetailFor={FixturedContest}
                isSecIn={this.state.isSecIn}
                activeTabIndex={activeTab}
                isStockF={this.state.isStockF || Constants.SELECTED_GAMET === Constants.GameType.StockFantasy}
                profileShow={this.state.aadharData}
               
                {...this.props} />
            }
            {
              showSkipConfirmModal &&
              <SkipConfirmationModal
                isShow={showSkipConfirmModal}
                isHide={() => mContext.setState({ showSkipConfirmModal: false })}
                onJoinClick={this.onJoinClick}
                onJoinLaterClick={this.onJoinLaterClick}
              />
            }
            {showConfirmationPopUp &&
              <ConfirmationPopup
                IsConfirmationPopupShow={this.ConfirmatioPopUpShow}
                IsConfirmationPopupHide={this.ConfirmatioPopUpHide}
                TeamListData={userTeamListSend}
                FixturedContest={FixturedContest}
                ConfirmationClickEvent={this.ConfirmEvent}
                CreateTeamClickEvent={this.createTeamAndJoin}
                lobbyDataToPopup={FixturedContest}
                fromContestListingScreen={true}
                TotalTeam={[]}
                isStockF={this.state.isStockF || Constants.SELECTED_GAMET === Constants.GameType.StockFantasy}
                createdLineUp={''} />
               
            }
            {showThankYouModal &&
              <Thankyou
                ThankyouModalShow={this.ThankYouModalShow}
                ThankYouModalHide={this.ThankYouModalHide}
                goToLobbyClickEvent={this.goToLobby}
                seeMyContestEvent={this.seeMyContest}
                isStock={this.state.isStockF || Constants.SELECTED_GAMET === Constants.GameType}
               
              />
            }
          </div>
        )}
      </MyContext.Consumer>
    );
  }
}