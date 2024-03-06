import React, { Component, Fragment } from 'react';
import { Card, Col, Row, Modal, ModalBody, ModalHeader, ModalFooter, Input, Button, Tooltip } from 'reactstrap';
import _, { lastIndexOf } from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { Progress } from 'reactstrap';
import { notify } from 'react-notify-toast';
import LS from 'local-storage';
import queryString from 'query-string';
import Images from '../../components/images';

import * as MODULE_C from "../Marketing/Marketing.config";
import PromoteContestModal from '../../Modals/PromoteContest';
import PromoteNotActive from '../../Modals/PromoteNotActive';
import moment from 'moment';
import Slider from "react-slick";
import HF, { _isUndefined, _isEmpty, _Map, _filter } from '../../helper/HelperFunction';
import { MSG_CANCEL_REQ, CANCEL_GAME_TITLE, CANCEL_CONTEST_TITLE, SCRATCH_WIN_TAP, SECOND_INNING } from "../../helper/Message";
import { changeScrWinStatus, DFST_SAVE_CONTEST_TOURNAMENT, DFST_getTourFixtures } from "../../helper/WSCalling";
import PromptModal from '../../components/Modals/PromptModal';
import { CopyToClipboard } from 'react-copy-to-clipboard';
import DFSFixtureModal from './DFSFixtureModal ';
// import HF, {  } from "../../helper/HelperFunction";
export var APP_MASTER_DATA = '';

class FixtureContest extends Component {

  constructor(props) {
    super(props);

    this.state = {
      selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
      league_id: (this.props.league_id) ? this.props.league_id : this.props.match.params.league_id,
      // season_game_uid: (this.props.match.params.season_game_uid) ? this.props.match.params.season_game_uid : '',
      collection_master_id: (this.props.match.params.collection_master_id) ? this.props.match.params.collection_master_id : '',
      fixtureDetail: {},
      contestList: [],
      keyword: '',
      posting: false,
      prize_modal: false,
      contestObj: {},
      contest_promote_model: false,
      contestPromoteParam: {
        email_contest_model: false,
        message_contest_model: false,
        notification_contest_model: false
      },
      promote_model: false,
      CancelModalIsOpen: false,
      CancelPosting: true,
      CANCEL_COLLE_MASTER_ID: 0,
      CONTEST_U_ID: 0,
      SHOW_CANCEL: 0,
      BackTab: (this.props.match.params.tab) ? this.props.match.params.tab : 1,
      DeadlineTime: 0,
      isShowAutoToolTip: false,
      MaxMatchSystemUser: 0,
      ComingFrom: false,
      AllowSystemUser: (!_.isUndefined(HF.getMasterData().pl_allow) && HF.getMasterData().pl_allow == '1') ? true : false,
      ScrWinModalOpen: false,
      ScrWinPosting: false,
      SHOW_REVERT_FX: false,
      revertFxPrizeModal: false,
      season_id: (this.props.match.params.season_id) ? this.props.match.params.season_id : '',
      isDFS: LS.get('isMGEnable') && LS.get('isMGEnable') == 1 ? false : true,
      isPublished: this.props && this.props.location && this.props.location.state && this.props.location.state.isNPublished ? this.props.location.state.isNPublished : true,
      ContestStatsData: {},
      contestFixtureList: [],
      showFixtureModal: false,
      ContestData: {},
      Posting: false,
      fixtureList: [],
      selFixList: [],
      isMore: false,
      def_check:false,
      ids:'',
      tournament_data :'',
      is_multigame:0,
      tournament_enable:0,
      contest_ids :[]
      // contestId: this.state.ContestData.contest_id
    };
  }

  PromoteHide = () => {
    this.setState({
      promote_model: false
    });
  }

  componentDidMount() {
    this.GetFixtureDetail();
  }
  indexCount = (idx) => {
    return idx + idx;
  }



  /*****contest promote model functions START */

  toggleContestPromoteModal = (key, val) => {
    if (!NC.ALLOW_COMMUNICATION_DASHBOARD) {
      this.setState({
        promote_model: true
      });
      return false;
    }
    var params = {};

    let TempDate = moment(WSManager.getUtcToLocal(val.season_scheduled_date)).format("D-MMM-YYYY hh:mm A")

    params.email_template_id = 2;

    params.contest_id = val.contest_id;
    params.all_user = 1;
    params.for_str = ' for Contest ' + val.contest_name + '(' + this.state.fixtureDetail.home + ' vs ' + this.state.fixtureDetail.away + ' ' + TempDate + ')';
    const stringified = queryString.stringify(params);
    this.props.history.push(`/marketing/new_campaign?${stringified}`);
    return false;
  }

  PromoteContestHide = () => {
    this.setState({
      contest_promote_model: false
    });
  }

  /*****contest promote model functions  END*/

  GetFixtureDetail = () => {
    let param = {
      // "league_id": this.state.league_id,
      // "sports_id": this.state.selected_sport,
      // "season_game_uid": this.state.season_game_uid,
      "collection_master_id": this.state.collection_master_id,
      "is_trnt":1,
      // season_id: this.state.season_id,
      // ...(this.state.isPublished && {"collection_master_id": this.state.collection_master_id})
      ...((!this.state.isPublished || !this.state.isDFS) && { season_id: this.state.season_id })
    }
    this.setState({ posting: true });
    let Api = this.state.isDFS ? (!this.state.isPublished ? NC.GET_SEASON_DETAILS : NC.DFS_GET_FIXTURE_DETAILS) : NC.DFS_GET_FIXTURE_DETAILS
    WSManager.Rest(NC.baseURL + Api, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        let fixtureDetail = responseJson.data;
        var dt = new Date(fixtureDetail.season_scheduled_date);
        var is_multigame = 0;
        if(fixtureDetail.season_game_count > 1){
          is_multigame = 1;
        }
        dt.setMinutes(dt.getMinutes() - 20);

        this.setState({
          ComingFrom: HF.getTimeDiff(dt),
          posting: false,
          fixtureDetail: fixtureDetail,
          is_multigame:is_multigame,
          tournament_enable:fixtureDetail.tournament_enable,
          DeadlineTime: moment(WSManager.getUtcToLocal(dt)).format("hh:mm A"),
        }, function () {
          this.GetFixtureContest();
          this.GetFixtureTournamentList();
        });
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        WSManager.logout();
        this.props.history.push('/login');
      }
    })
  }

  GetFixtureContest = () => {
    this.setState({ posting: true })
    let params = {
      "collection_master_id": this.state.collection_master_id,
      // "sports_id": this.state.selected_sport, 
      // 'league_id': this.state.league_id, 
      // "season_game_uid": this.state.season_game_uid, 
      // 'keyword': this.state.keyword ,
      ...(!this.state.isDFS && { "season_id": this.state.season_id })

    };
    // WSManager.Rest((this.state.isDFS) ? NC.baseURL + NC.GET_FIXTURE_CONTEST : NC.baseURL + NC.GET_COLLECTION_FIXTURE_CONTEST, params).then((responseJson) => {
    WSManager.Rest(NC.baseURL + NC.GET_FIXTURE_CONTEST, params).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        let responseJsonData = responseJson.data;
        this.setState({
          contestList: responseJsonData.contest_list,
          MaxMatchSystemUser: responseJsonData.stats.system_user_limit,
          CANCEL_COLLE_MASTER_ID: this.state.collection_master_id,
          SHOW_CANCEL: responseJsonData.stats.open,
          SHOW_REVERT_FX: responseJsonData.stats.completed,
          ContestStatsData: responseJsonData.stats
        })
      }
      this.setState({ posting: false })
    })
  }


  GetFixtureTournamentList = () => {
    //disable api call for multigame
    if(this.state.is_multigame == 1 || this.state.tournament_enable == 0){
      return true;
    }
    this.setState({ posting: true })
    let params = {
      "collection_master_id": this.state.collection_master_id,
      // "sports_id": this.state.selected_sport, 
      // 'league_id': this.state.league_id, 
      // "season_game_uid": this.state.season_game_uid, 
      // 'keyword': this.state.keyword ,
      ...(!this.state.isDFS && { "season_id": this.state.season_id })

    };
    // WSManager.Rest((this.state.isDFS) ? NC.baseURL + NC.GET_FIXTURE_CONTEST : NC.baseURL + NC.GET_COLLECTION_FIXTURE_CONTEST, params).then((responseJson) => {
    WSManager.Rest(NC.baseURL + NC.GET_FIXTURE_TOURNAMENT_LIST, params).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        console.log(responseJson,'response')
        let ids = responseJson.data && responseJson.data.map(obj => {return obj.contest_id })

        this.setState({ contest_ids: ids })      
        

        // console.log('nilesh object')
        // return false
        let tmpList = []
        _Map(responseJson.data, (fix, indx) => {


           
          // console.log(fix,'get tour')
          if (fix.contest_id > '0') {
            tmpList.push(fix.tournament_id)
          }
        })
        this.setState({
          contestFixturelist: Response.data,
          // Posting: false,
          // selFixList: tmpList
          isMore: false,
          selFixList :[],

        }, () => {
          this.setState({
            isMore: this.state.contestFixtureList.length == this.state.selFixList.length ? false : true
          })
        })
        let responseJsonData = responseJson.data;
        this.setState({
          contestFixtureList: responseJsonData,
      
        })
      }
      this.setState({ posting: false })
    })
  }



  saveTourFixture = (cm_id, t_id, c_id) => {
    let {fixtureList, selFixList, isMore } = this.state
    
    this.setState({ Posting: true })
    let params = {
      collection_master_id: cm_id,
      contest_id: c_id,
      tournament_ids: t_id
    }
    DFST_SAVE_CONTEST_TOURNAMENT(params).then(Response => {
      if (Response.response_code == NC.successCode) {
         console.log(Response,'nilesh response')
        this.setState({
           contestFixtureList: Response.data,
           isMore: false,
           selFixList :[],


          
          },()=>{
         
        })

        notify.show(Response.message, "success", 3000);
        // this.GetFixtureTournamentList()
        this.hideFixtureModal()
        
      } else {
        notify.show(NC.SYSTEM_ERROR, 'error', 5000)
      }
    }).catch(error => {
      notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    })
  }


  getWinnerCount(ContestItem) {
    if (!_.isEmpty(ContestItem) && !_.isEmpty(ContestItem.prize_distibution_detail) && !_.isNull(ContestItem.prize_distibution_detail)) {
      if (ContestItem.prize_distibution_detail.length > 0) {
        if ((ContestItem.prize_distibution_detail[ContestItem.prize_distibution_detail.length - 1].max) > 1) {
          return ContestItem.prize_distibution_detail[ContestItem.prize_distibution_detail.length - 1].max + " Winners"
        } else {
          return ContestItem.prize_distibution_detail[ContestItem.prize_distibution_detail.length - 1].max + " Winner"
        }
      }
    }
  }

  viewWinners = (e, contestObj) => {
    e.stopPropagation();
    this.setState({ 'prize_modal': true, 'contestObj': contestObj });
  }

  closePrizeModel = () => {
    this.setState({ 'prize_modal': false, 'contestObj': {} });
  }

  ShowProgressBar = (join, total) => {
    return join * 100 / total;
  }

  redirectToCreateContest = () => {
    if (!this.state.isDFS) {
      this.props.history.push({ pathname: '/contest/createcollectioncontest/' + this.state.league_id + '/' + this.state.collection_master_id })
    } else {
      this.props.history.push({ pathname: '/contest/createcontest/' + this.state.collection_master_id + '/' + this.state.season_id })
    }
  }
  redirectToCreateF2PContest = () => {

    this.props.history.push({ pathname: '/contest/createf2pcontest/' + this.state.league_id + '/' + this.state.season_id })
  }

  markPinContest = (e, contest, group_index, contest_index) => {
    e.stopPropagation();
    if (window.confirm("Are you sure want to mark pin ?")) {
      this.setState({ posting: true })
      let params = { "contest_id": contest.contest_id, "collection_master_id": contest.collection_master_id };
      WSManager.Rest(NC.baseURL + NC.MARK_PIN_CONTEST, params).then((responseJson) => {
        if (responseJson.response_code === NC.successCode) {
          let contestList = _.cloneDeep(this.state.contestList);
          contestList[group_index]['contest_list'][contest_index]['is_pin_contest'] = '1';
          this.setState({ 'contestList': contestList });

          notify.show(responseJson.message, "success", 5000);
        } else {
          notify.show(responseJson.message, "error", 3000);
        }
        this.setState({ posting: false })
      })
    } else {
      return false;
    }
  }

  removePinContest = (e, contest, group_index, contest_index) => {
    e.stopPropagation();
    if (window.confirm("Are you sure want to remove pin ?")) {
      this.setState({ posting: true })
      let params = { "contest_id": contest.contest_id, "collection_master_id": contest.collection_master_id, 'is_pin_contest': '0' };
      WSManager.Rest(NC.baseURL + NC.MARK_PIN_CONTEST, params).then((responseJson) => {
        if (responseJson.response_code === NC.successCode) {
          let contestList = _.cloneDeep(this.state.contestList);
          contestList[group_index]['contest_list'][contest_index]['is_pin_contest'] = '0';
          this.setState({ 'contestList': contestList });

          notify.show(responseJson.message, "success", 5000);
        } else {
          notify.show(responseJson.message, "error", 3000);
        }
        this.setState({ posting: false })
      })
    } else {
      return false;
    }
  }

  deleteContest = (e, contestObj, index) => {
    e.stopPropagation();
    if (window.confirm("Are you sure want to delete this contest ?")) {
      this.setState({ posting: true })
      let params = {
        "contest_id": contestObj.contest_id,
        // "collection_master_id": contestObj.collection_master_id 
      };
      WSManager.Rest(NC.baseURL + NC.DELETE_CONTEST, params).then((responseJson) => {
        if (responseJson.response_code === NC.successCode) {
          this.GetFixtureContest();
          notify.show(responseJson.message, "success", 5000);
        } else {
          notify.show(responseJson.message, "error", 3000);
        }
        this.setState({ posting: false })
      })
    } else {
      return false;
    }
  }

  cancelMatchModalToggle = (contest_u_id, flag, group_index, idx) => {
    if (flag == 2) {
      this.setState({
        CONTEST_U_ID: contest_u_id
      });
    }
    this.setState({
      API_FLAG: flag,
      CancelModalIsOpen: !this.state.CancelModalIsOpen,
      GroupIndex: group_index,
      DeleteIndex: idx,
    });
  }

  handleInputChange = (e) => {
    let name = e.target.name
    let value = e.target.value
    let btnAction = false
    if (value.length < 3 || value.length > 160)
      btnAction = true

    this.setState({
      [name]: value,
      CancelPosting: btnAction
    })
  }

  cancelMatch = () => {
    let { API_FLAG, CANCEL_COLLE_MASTER_ID, CONTEST_U_ID, CancelReason, GroupIndex, DeleteIndex, contestList } = this.state
    this.setState({ CancelPosting: false });

    let param = {
      cancel_reason: CancelReason
    };

    let API_URL = ""
    if (API_FLAG == 1) {
      param.collection_master_id = CANCEL_COLLE_MASTER_ID
      API_URL = NC.CANCEL_COLLECTION
    } else {
      param.contest_unique_id = CONTEST_U_ID
      API_URL = NC.CANCEL_CONTEST
    }

    WSManager.Rest(NC.baseURL + API_URL, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        this.GetFixtureContest();
        // this.GetFixtureTournamentList();
        this.setState({
          CancelPosting: false,
          CancelReason: ''
        })

        if (GroupIndex >= "0" && DeleteIndex >= "0") {
          contestList[GroupIndex].contest_list[DeleteIndex].status = "1"
        } else {
          if (!this.state.isDFS) {
            this.props.history.push({ pathname: '/multigame/Fixtures' })
          }
          else {
            this.props.history.push({ pathname: '/game_center/DFS' })
          }
        }
        notify.show(responseJson.message, "success", 5000);
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        notify.show(responseJson.message, "error", 5000);
      }
      this.cancelMatchModalToggle('', '')
    })
  }

  cancelMatchModal = () => {
    let { CancelPosting, API_FLAG } = this.state
    return (
      <div>
        <Modal
          isOpen={this.state.CancelModalIsOpen}
          toggle={this.cancelMatchModalToggle}
          className="cancel-match-modal"
        >
          <ModalHeader>{API_FLAG == 1 ? CANCEL_GAME_TITLE : CANCEL_CONTEST_TITLE}</ModalHeader>
          <ModalBody>
            <div className="confirm-msg">{MSG_CANCEL_REQ}</div>
            <div className="inputform-box">
              <label>Cancel Reason</label>
              <Input
                minLength="3"
                maxLength="160"
                rows={3}
                type="textarea"
                name="CancelReason"
                onChange={(e) => this.handleInputChange(e)}
              />
            </div>
          </ModalBody>
          <ModalFooter>
            <Button
              color="secondary"
              onClick={this.cancelMatch}
              disabled={CancelPosting}
            >Yes</Button>{' '}
            <Button color="primary" onClick={this.cancelMatchModalToggle}>No</Button>
          </ModalFooter>
        </Modal>
      </div>
    )
  }
  getPrizeAmount = (prize_data) => {
    let prize_text = "Prizes";
    let is_tie_breaker = 0;
    let prizeAmount = { 'real': 0, 'bonus': 0, 'point': 0 };
    if (!_.isNull(prize_data)) {
      prize_data.map(function (lObj, lKey) {
        var amount = 0;
        if (lObj.max_value) {
          amount = parseFloat(lObj.max_value);
        } else {
          amount = parseFloat(lObj.amount);
        }
        if (lObj.prize_type == 3) {
          is_tie_breaker = 1;
        }
        if (lObj.prize_type == 0) {
          prizeAmount['bonus'] = parseFloat(prizeAmount['bonus']) + amount;
        } else if (lObj.prize_type == 2) {
          prizeAmount['point'] = parseFloat(prizeAmount['point']) + amount;
        } else {
          prizeAmount['real'] = parseFloat(prizeAmount['real']) + amount;
        }
      });
    }
    if (is_tie_breaker == 0 && prizeAmount.real > 0) {
      // prize_text = HF.getCurrencyCode() + parseFloat(prizeAmount.real).toFixed(2);
      prize_text = HF.getCurrencyCode() + HF.getPrizeInWordFormat(prizeAmount.real);
    } else if (is_tie_breaker == 0 && prizeAmount.bonus > 0) {
      // prize_text = '<i class="icon-bonus"></i>' + parseFloat(prizeAmount.bonus).toFixed(2);
      prize_text = '<i class="icon-bonus"></i>' + HF.getPrizeInWordFormat(prizeAmount.bonus);
    } else if (is_tie_breaker == 0 && prizeAmount.point > 0) {
      // prize_text = '<img src="' + Images.COINIMG + '" alt="coin-img" />' + parseFloat(prizeAmount.point).toFixed(2);
      prize_text = '<img src="' + Images.COINIMG + '" alt="coin-img" />' + HF.getPrizeInWordFormat(prizeAmount.point)
    }
    return { __html: prize_text };
  }

  SWinToggle = (g_idx, idx, name) => {
    if (!_isUndefined(g_idx) && !_isUndefined(idx)) {
      let contestList = this.state.contestList
      let flag = contestList[g_idx]['contest_list'][idx][name]
      contestList[g_idx]['contest_list'][idx][name] = !flag
      this.setState({ contestList });
    }
  }

  addRemoveScWinModal = (c_id, g_idx, c_idx, flag) => {
    let msg_status = (flag == '1') ? 'in' : '';
    let msg = 'Are you sure you want to ' + msg_status + 'active scratch and win for this contest ?'
    this.setState({
      ScrWinModalOpen: !this.state.ScrWinModalOpen,
      ContestId: c_id,
      ScWinMsg: msg,
      GrpIdx: g_idx,
      ConIdx: c_idx,
      ScFlag: flag,
    })
  }

  addRemoveScWin = () => {
    this.setState({ ScrWinPosting: true })
    let { contestList, ContestId, GrpIdx, ConIdx, ScFlag } = this.state
    let new_val = ScFlag == '1' ? '0' : '1';
    let params = {
      "contest_id": ContestId,
      "status": new_val,
    };

    changeScrWinStatus(params).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        let temp_c_list = contestList;
        temp_c_list[GrpIdx]['contest_list'][ConIdx]['is_scratchwin'] = new_val;
        this.setState({ contestList: temp_c_list, ScrWinModalOpen: false });
        notify.show(responseJson.message, "success", 5000);
      } else {
        notify.show(responseJson.message, "error", 3000);
      }
      this.setState({ ScrWinPosting: false })
    })
  }

  revertFxPrizeModal = (contest_id, flag, group_index, idx) => {
    let str = 'match'
    if (flag == 2) {
      str = 'contest'
    }
    let msg = 'Are you sure you want to revert prize for this ' + str + '?'
    if (flag == 2) {
      this.setState({
        CONTEST_ID: contest_id
      });
    }
    this.setState({
      FXR_API_FLAG: flag,
      RevertFxModalOpen: !this.state.RevertFxModalOpen,
      FXR_GroupIndex: group_index,
      FXR_Index: idx,
      FXR_MSG: msg,
    });
  }

  revertFxPrize = () => {
    let { FXR_API_FLAG, CANCEL_COLLE_MASTER_ID, CONTEST_ID, FXR_GroupIndex, FXR_Index, contestList } = this.state
    this.setState({ RevertFxPosting: true });

    let param = {};

    let API_URL = ""
    if (FXR_API_FLAG == 1) {
      param.collection_master_id = CANCEL_COLLE_MASTER_ID
      API_URL = NC.REVERT_COLLECTION_PRIZE
    } else {
      param.contest_id = CONTEST_ID
      API_URL = NC.REVERT_CONTEST_PRIZE
    }

    WSManager.Rest(NC.baseURL + API_URL, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        this.GetFixtureContest();
        this.setState({ RevertFxPosting: false })

        if (FXR_GroupIndex >= "0" && FXR_Index >= "0") {
          contestList[FXR_GroupIndex].contest_list[FXR_Index].status = "0"
        } else {
          this.props.history.push({ pathname: '/game_center/DFS' })
        }
        notify.show(responseJson.message, "success", 5000);
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        notify.show(responseJson.message, "error", 5000);
      }
      this.revertFxPrizeModal('', '')
    })
  }
  onCopyCode = () => {
    notify.show("Contest id copied", "success", 5000);

  }

  gotoDetails = (contest, item) => {
    let isTourGame = item.is_tour_game
    this.props.history.push({ pathname: '/finance/contest_detail/' + contest.contest_unique_id + '/' + isTourGame, state: { isTourGame } })
  }

  showMatchFormat = (format) => {
    return format == 1 ? 'ODI' : format == 2 ? 'TESt' : format == 3 ? 'T20' : 'T10'
  }

  hideFixtureModal = () => {   
    let {tournament_data } = this.state
    if(tournament_data){
    tournament_data['is_added'] = 0;
   }
    this.setState({
      showFixtureModal: false,
      activeTournament: '',
      isMore: false,
      selFixList :[],
      
    }, () => {
      // this.getAllTornament()
    })
  }
  statsPopup = (e) => {
    let ContestData = e
    this.setState({
      showFixtureModal: true,
      ContestData: ContestData
    })
  }

  getFixtureList = (listItem) => {
const {contestFixtureList} = this.state
    // console.log(listItem); 
    this.setState({ Posting: true })
    let params = {
      collection_master_id: listItem
    }
    DFST_getTourFixtures(params).then(Response => {
      // console.log('nilj')
      if (Response.response_code == NC.successCode) {
        
        let tmpList = []
        _Map(Response.data, (fix, indx) => {

         let ids = Response.data.map(obj => obj.contest_id)

          console.log(fix,'get tour')
          if (fix.is_added == '1') {
            tmpList.push(fix.season_id)
          }
        })
        this.setState({
          contestFixturelist: Response.data,
          Posting: false,
          selFixList: tmpList
        }, () => {
          this.setState({
            isMore: this.state.contestFixtureList.length == this.state.selFixList.length ? false : true
          })
        })
      } else {
        notify.show(NC.SYSTEM_ERROR, 'error', 5000)
      }
    }).catch(error => {
      notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    })
  }

  

  onSelect = (e, item, idx) => {
    // console.log(HF.getMasterData().allow_dfs_tournament,'nileshint')
    // HF.getMasterData().allow_dfs_tournament
    
    let tmp = this.state.contestFixtureList
    let tmpSelList = this.state.selFixList

  
    
    tmp[idx].is_added = item.is_added == '1' ? '0' : '1'
    tmp[idx].new_added = item.is_added == '1' ? true : false

    console.log(item,'nilesh');

    if (item.is_added == '0') {
      // console.log('kkkk')
      let index = tmpSelList.indexOf(item.tournament_id);
      if (index > -1) {
        tmpSelList.splice(index, 1);
      }
    }
    else {
      tmpSelList.push(item.tournament_id)
    }

    // this.setState({
    //   contestFixturelist: tmp,
    //   isMore: false
    // })
    if(_isEmpty(tmpSelList)){
      this.setState({
        contestFixturelist: tmp,
        isMore: false
      })
    }else{
      this.setState({
        contestFixturelist: tmp,
        isMore: true
      })

    }

    this.setState({
       tournament_data :item
    })

    // console.log(tmpSelList,'templist')
  }

  backToList=()=>{
    this.props.history.push({ 
      pathname: '/multigame/Fixtures' ,
      state: {
        activeTab: this.state.fixtureDetail.status != 0 ? '3' : (this.state.fixtureDetail.match_started == 0 ? '2' : '1')
      }
    })
  }

  renderTourList=(contestFixtureList,ContestData)=>{
    let isTourExsist = contestFixtureList.length > 0 ? contestFixtureList.filter(obj => (obj.contest_id == 0 || obj.contest_id == ContestData.contest_id)) : []
    // console.log('isTourExsist',isTourExsist)
    return (
    
      <>
        {
          contestFixtureList && contestFixtureList.length > 0 && isTourExsist.length > 0 ?
          <div className="list-view">
          <div className="sel-fix-lbl">Select Tournament </div>
          <ul className="list-wrap">
            { 
            
              _Map(contestFixtureList, (match, idx) => {
                console.log(match,'match')
                return (
                  (match.contest_id == 0 || ContestData.contest_id == match.contest_id) &&
                  <>
                  <li className={`list-item`}>
                    <Input
                      disabled={match.contest_id > '0'}
                      className="select-all-in"
                      type="checkbox"
                      onChange={(e) => this.onSelect(e, match, idx)}
                      checked={(match.contest_id == ContestData.contest_id || match.is_added == 1) ? true : false}
                    />
                    {
                      <div className="team-abr">{match.name}</div>

                    }

                    {match.contest_id > '0' ? <div className="overlay"></div> : ''}
                  </li> 
                  {/* {
                    (match.contest_id != 0 || ContestData.contest_id != match.contest_id) &&
                    <li className={`list-item`}>No Tournament </li>} */}
                  </>
                )
              })
            }
          </ul> </div> :  <div className="list-view"><ul className="list-wrap"><li className={`list-item`}>Tournament Not available </li></ul></div>
        }
      </>
     
    )
  }

  render() {
    const {
      BackTab,
      ComingFrom,
      MaxMatchSystemUser,
      DeadlineTime,
      fixtureDetail,
      contestList,
      contestObj,
      CANCEL_COLLE_MASTER_ID,
      SHOW_CANCEL,
      selected_sport,
      AllowSystemUser,
      ScrWinModalOpen,
      ScrWinPosting,
      ScWinMsg,
      collection_master_id,
      SHOW_REVERT_FX,
      RevertFxModalOpen,
      RevertFxPosting,
      FXR_MSG,
      league_id,
      season_id,
      ContestStatsData,
      contestFixtureList,
      showFixtureModal,
      ContestData,
      is_multigame,
      tournament_enable
    } = this.state
    const settings = {
      dots: false,
      infinite: false,
      speed: 500,
      slidesToShow: 4,
      slidesToScroll: 1,
      arrows: false,
      responsive: [
        {
          breakpoint: 1400,
          settings: {
            slidesToShow: 3
          }
        }
      ]
    };

    let ScrWinModalProps = {
      publishModalOpen: ScrWinModalOpen,
      publishPosting: ScrWinPosting,
      modalActionNo: this.addRemoveScWinModal,
      modalActionYes: this.addRemoveScWin,
      MainMessage: ScWinMsg,
      SubMessage: '',
    }

    let RevertFxProps = {
      publishModalOpen: RevertFxModalOpen,
      publishPosting: RevertFxPosting,
      modalActionNo: this.revertFxPrizeModal,
      modalActionYes: this.revertFxPrize,
      MainMessage: FXR_MSG,
      SubMessage: '',
    }
    let { int_version } = HF.getMasterData()



    return (
      <div className="animated fadeIn contest-group-wrapper fixture-contest-main">
        {ScrWinModalOpen && <PromptModal {...ScrWinModalProps} />}
        {RevertFxModalOpen && <PromptModal {...RevertFxProps} />}
        {this.cancelMatchModal()}
        {!_.isEmpty(fixtureDetail) &&
          <Row className="xanimate-left">
            {this.state.isDFS && !_.isEmpty(fixtureDetail) && this.state.season_id &&
              <Fragment>

                {
                  fixtureDetail.is_tour_game == 1 ?
                    <Col lg={4}>
                      <div className="bg-card-motor-sports">
                        <div className="motor-sports-container">
                          <div className="top-view-motor-sports">
                            <div className={`car-type-view ${fixtureDetail.league_name == "Formula 1" ? " formula-one" : fixtureDetail.league_name == "Moto GP" ? " moto-gp" : fixtureDetail.league_name == "Desert racing" ? " desert-racing" : " other-league-abbr"}`}>{fixtureDetail.league_name}</div>
                          </div>
                          <div className="motor-sports-view">
                            <img className="img-colum-view" src={NC.S3 + NC.MOTOR_SPORTS_IMG + fixtureDetail.league_image} alt=""
                            ></img>
                            <div className="inner-view-motor-sports">
                              <div className="tournament-name-view">{fixtureDetail.tournament_name}</div>
                              <div className="events-view">{fixtureDetail.match_event} events</div>
                              <div className="date-view">{HF.getFormatedDateTime(fixtureDetail.season_scheduled_date, 'D MMM YYYY hh:mm A')} to {HF.getFormatedDateTime(fixtureDetail.end_scheduled_date, 'D MMM YYYY hh:mm A')} </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </Col>
                    :
                    <Col lg={4}>

                      <div className="carddiv pull-left fxrcard">

                        <Col>
                          <img className="cardimgdfs" src={NC.S3 + NC.FLAG + (fixtureDetail.home_flag ? fixtureDetail.home_flag : fixtureDetail.match[0].home_flag)}></img>
                        </Col>
                        <Col >
                          <h3 className="livcardh3dfs">{(fixtureDetail.home) ? fixtureDetail.home : fixtureDetail.match[0].home} VS {(fixtureDetail.away) ? fixtureDetail.away : fixtureDetail.match[0].away}</h3>

                          <h6 className="livcardh6dfs">
                            {/* {WSManager.getUtcToLocalFormat(fixtureDetail.fixture_date_time, 'D-MMM-YYYY hh:mm A')} */}
                            {HF.getFormatedDateTime(fixtureDetail.season_scheduled_date, 'D-MMM-YYYY hh:mm A')}

                          </h6>

                          <h6 className="livcardh6dfs">{fixtureDetail.league_name}</h6>
                        </Col>
                        <Col>
                          <img className="cardimgdfs" src={NC.S3 + NC.FLAG + (fixtureDetail.away_flag ? fixtureDetail.away_flag : fixtureDetail.match[0].away_flag)}></img>
                        </Col>
                        {
                          this.state.selected_sport == "7" && fixtureDetail.format_str != '' &&
                          <small className="format_bx">{this.showMatchFormat(fixtureDetail.format_str)}</small>
                        }
                      </div>
                    </Col>
                }


                <Col lg={8}>
                  {
                    (
                      // CANCEL_COLLE_MASTER_ID > "0" && 
                      SHOW_CANCEL > "0") &&
                    <Button
                      className='cancel-match-btn btn-secondary'
                      onClick={() => this.cancelMatchModalToggle('', 1, '-1', '-1')}
                    >{int_version == "1" ? "Cancel Game" : "Cancel Fixture"}</Button>
                  }
                  {
                    (SHOW_REVERT_FX > 0) &&
                    <Button
                      className='cancel-match-btn btn-secondary'
                      onClick={() => this.revertFxPrizeModal('', 1, '-1', '-1')}
                    >Revert Game/Fixture Prize</Button>
                  }
                  {
                    fixtureDetail.match_started == 0 &&
                    // !ComingFrom &&
                    this.state.isDFS &&
                    // NC.ALLOW_DFS == 1 &&
                    <Button className='xpull-right btn-secondary-outline cancel-match-btn' onClick={() => this.redirectToCreateContest()}>Create New Contest</Button>
                  }
                  {
                    (!ComingFrom && (selected_sport == 7 || selected_sport == 5) && ContestStatsData.system_user_limit > 0 && fixtureDetail.allow_bots == "1" && AllowSystemUser) &&
                    <Button
                      className={`btn-secondary float-right ${(SHOW_REVERT_FX > 0 || SHOW_CANCEL > "0") ? 'msu-btn-style' : ''}`}
                      onClick={() => this.props.history.push({ pathname: '/system-users/manage-system-users/' + CANCEL_COLLE_MASTER_ID + '/' + '0' + '/' + this.state.season_id })}
                    >Manage System Users</Button>
                  }
                </Col>
              </Fragment>

            }
            {
              !this.state.isDFS &&

              <Col lg={12} className='mb-20'>

                {
                  fixtureDetail && fixtureDetail.collection_name &&
                  <div><h2 class="h2-cls mb-20">{fixtureDetail.collection_name}</h2></div>
                }
                <Slider {...settings}>

                  {
                    !_.isEmpty(fixtureDetail) && !_.isEmpty(fixtureDetail.match)
                      ?
                      _.map(fixtureDetail.match, (fixtureitem, fixtureindex) => {
                        if (typeof fixtureitem.season_id == 'undefined') return false;
                        return (
                          <Card className="livecard p-l-r-5">
                            <div className="carddiv" >
                              <Col>
                                <img className="cardimg" src={NC.S3 + NC.FLAG + fixtureitem.home_flag}></img>
                              </Col>
                              <Col>
                                <h4 className="livcardh3">{(fixtureitem.home) ? fixtureitem.home : 'TBA'} VS {(fixtureitem.away) ? fixtureitem.away : 'TBA'}</h4>
                                <h6 className="livcardh6dfs">
                                  {/* {WSManager.getUtcToLocalFormat(fixtureitem.fixture_date_time, 'D-MMM-YYYY hh:mm A')} */}
                                  {HF.getFormatedDateTime(fixtureitem.season_scheduled_date, 'D-MMM-YYYY hh:mm A')}

                                </h6>
                                {<h6 className="livcardh6dfs">{fixtureDetail.league_name} -{this.showMatchFormat(fixtureitem.format_str)} </h6>}
                              </Col>
                              <Col>
                                <img className="cardimg" src={NC.S3 + NC.FLAG + fixtureitem.away_flag}></img>
                              </Col>
                            </div>
                          </Card>
                        )
                      }) : ''
                  }
                </Slider>

                {
                  (
                    // CANCEL_COLLE_MASTER_ID > "0" && 
                    SHOW_CANCEL > "0") &&
                  <Button
                    className='cancel-match-btn btn-secondary'
                    onClick={() => this.cancelMatchModalToggle('', 1, '-1', '-1')}
                  >{int_version == "1" ? "Cancel Game" : "Cancel Fixture"}</Button>
                }
                {
                  fixtureDetail.match_started == 0 &&
                  <Button className='btn btn-secondary btn-secondary-outline cancel-match-btn pull-right' outline color="danger" onClick={() => this.redirectToCreateContest()}>Create New Contest</Button>
                }
              </Col>


            }

          </Row>
        }

        <Row className="bench-dtl">
          <Col md={6}>
            <h2 className="h2-cls mb-3">Contest Stats</h2>
            <Row>
              <Col sm={4} className="pr-0">
                <div className="fxcon-total-box">
                  <div className="fxcon-title">Total Joined Users</div>
                  <div className="fxcon-count">
                    {
                      (!_.isEmpty(ContestStatsData) && !_.isUndefined(ContestStatsData.total_entries))
                        ?
                        ContestStatsData.total_entries
                        :
                        0
                    }
                  </div>
                </div>
              </Col>
              <Col sm={4} className="pr-0">
                <div className="fxcon-total-box">
                  <div className="fxcon-title">Paid Users</div>
                  <div className="fxcon-count">
                    {
                      (!_.isEmpty(ContestStatsData) && !_.isUndefined(ContestStatsData.paid_entries))
                        ?
                        ContestStatsData.paid_entries
                        :
                        0
                    }
                  </div>
                </div>
              </Col>
              <Col sm={4}>
                <div className="fxcon-total-box">
                  <div className="fxcon-title">Free Users</div>
                  <div className="fxcon-count">
                    {
                      (!_.isEmpty(ContestStatsData) && !_.isUndefined(ContestStatsData.paid_entries) && !_.isUndefined(ContestStatsData.total_entries))
                        ?
                        parseInt(ContestStatsData.total_entries) - parseInt(ContestStatsData.paid_entries)
                        :
                        0
                    }
                  </div>
                </div>
              </Col>
            </Row>
          </Col>
          {
            (HF.allowBenchPlyer() == '1' && this.state.isDFS) &&
            <Col md={6}>
              <h2 className="h2-cls mb-3">Bench Stats</h2>
              <Row>
                <Col sm={4} className="pr-0">
                  <div className="fxcon-total-box">
                    <div className="fxcon-title">Total Teams</div>
                    <div className="fxcon-count">
                      {
                        (!_.isEmpty(ContestStatsData) && !_.isUndefined(ContestStatsData.bench))
                          ?
                          ContestStatsData.bench.total_teams
                          :
                          0
                      }
                    </div>
                  </div>
                </Col>
                <Col sm={4} className="pr-0">
                  <div className="fxcon-total-box">
                    <div className="fxcon-title">Bench Applied</div>
                    <div className="fxcon-count">
                      {
                        (!_.isEmpty(ContestStatsData) && !_.isUndefined(ContestStatsData.bench))
                          ?
                          ContestStatsData.bench.bench_applied
                          :
                          0
                      }
                    </div>
                  </div>
                </Col>
                <Col sm={4}>
                  <div className="fxcon-total-box">
                    <div className="fxcon-title">Bench Used</div>
                    <div className="fxcon-count">
                      {
                        (!_.isEmpty(ContestStatsData) && !_.isUndefined(ContestStatsData.bench))
                          ?
                          ContestStatsData.bench.bench_used
                          :
                          0
                      }
                    </div>
                  </div>
                </Col>
              </Row>
            </Col>
          }
        </Row>

        <Col className="heading-box">
          <div className="contest-tempalte-wrapper">
            <h2 className="h2-cls">Daily Fantasy Sports</h2>
          </div>

          <div className="fixture-contest">
            {
              this.state.isDFS &&
              <label className="backtofixtures" onClick={() => this.props.history.push('/game_center/DFS?tab=' + BackTab)}> {'<'} {int_version == "1" ? "Back to Games" : "Back to Fixtures"}</label>
            }
            {
              !this.state.isDFS &&
              <label className="backtofixtures" onClick={()=>this.backToList()}> {'<'} Back to list</label>
            }
          </div>
        </Col>
        <div className="border-bottom mb-4"></div>
        {
          (!ComingFrom && (selected_sport == 7 || selected_sport == 5) && fixtureDetail.playing_eleven_confirm == "1" && fixtureDetail.allow_bots == "1" && AllowSystemUser) &&
          (<Row className="martop-15 ml-1">
            <div className="linup-info-text mt-0">
              You can add maximum {MaxMatchSystemUser} system user Lineup in total, in this {int_version == "1" ? "game" : "fixture"}. Deadline for the same for this {int_version == "1" ? "game" : "fixture"} is: <span>{DeadlineTime}</span></div>
          </Row>)
        }
        {
          (!ComingFrom && (selected_sport == 7 || selected_sport == 5) && fixtureDetail.playing_eleven_confirm == "0" && fixtureDetail.allow_bots == "1" && AllowSystemUser) &&
          (<Row className="martop-15 ml-1">
            <div className="linup-info-text war-text mt-0">
              <span>Warning!</span> Lineup out is not guaranteed in this match, you may lose the contest as bots will not update the team.</div>
          </Row>)
        }



        {contestList.map((item, group_index) => (

          <div className="contest-group-container" key={group_index}>
            <Row>
              <Col md="12" className="xanimate-left">
                <h4>{item.group_name}</h4>
              </Col>
              {item.contest_list.map((contest, idx) => (
                <Col key={idx} md="4" className="xanimate-right">
                  <div className="contest-group">
                    <div className="contest-list-wrapper">
                      {/* <div className="contest-card more-contest-card sponsor-cls"> */}
                      <div className={"contest-card more-contest-card xsponsor-cls" + (contest.sponsor_logo ? ' sponsor-cls' : '')}>
                        <div className="contest-list contest-card-body">
                          <div className="pinned-area">
                            {
                              contest.is_pin_contest == '1' &&
                              <img onClick={(e) => this.removePinContest(e, contest, group_index, idx)} src={Images.PIN_ACTIVE} alt="" className="pinned-active" />
                            }
                            {
                              contest.is_pin_contest == '0' &&
                              <i onClick={(e) => this.markPinContest(e, contest, group_index, idx)} className="icon-pinned"></i>
                            }
                          </div>
                          <div className="contest-list-header clearfix">
                            <div className="contest-heading">
                              <div className="action-head clearfix">
                                <div
                                  // onClick={() => this.props.history.push('/finance/contest_detail/' + contest.contest_unique_id )}
                                  onClick={() => this.gotoDetails(contest, fixtureDetail)}

                                  className="contest-name text-ellipsis">{contest.contest_name}</div>
                                {HF.getMasterData().allow_dfs_tournament =='1' &&
                                <div className='tournament-align'>
                                  {
                                    (contest['is_2nd_inning'] == undefined || contest['is_2nd_inning'] == '0') && contest['is_rookie'] == 0 && contest['is_h2h'] == 0 && is_multigame == 0 && tournament_enable == 1 && 
                                    <div className="action-item" onClick={() => this.statsPopup(contest)}>
                                      Tournaments
                                    </div>

                                  }
                                </div>
                                }
                              </div>
                              <div className="clearfix">
                                <ul className="ul-action con-action-list nil">
                                  {
                                    <CopyToClipboard onCopy={this.onCopyCode} text={contest.contest_unique_id} className="cursor-pointer ">
                                      <img onClick={() => { this.onCopyCode() }} style={{ height: "22px", width: "22px" }} alt='' src={Images.COPY_CONTEST_ID} />

                                    </CopyToClipboard>

                                  }

                                  {
                                    (HF.allowSecondInni() == '1' && contest.is_2nd_inning == "1" && selected_sport == '7') &&
                                    <li className="action-item">
                                      <i
                                        className="icon-snd"
                                        id={"sinni_tt_" + group_index + '_' + idx}>
                                        <span>
                                          <Tooltip
                                            placement="right"
                                            isOpen={contest.sinni_tt}
                                            target={"sinni_tt_" + group_index + '_' + idx}
                                            toggle={() => this.SWinToggle(group_index, idx, 'sinni_tt')}>
                                            {SECOND_INNING}
                                          </Tooltip>
                                        </span>
                                      </i>
                                    </li>
                                  }

                                  {
                                    contest.status == '3' &&
                                    <li className="action-item">
                                      <i
                                        className="icon-cancel-key icon-reset"
                                        title="Revert Contest Prize"
                                        onClick={() => this.revertFxPrizeModal(contest.contest_id, 2, group_index, idx)}
                                      ></i>
                                    </li>
                                  }
                                  {
                                    contest.status == '0' &&
                                    <li className="action-item">
                                      <i
                                        className="icon-cross icon-cancel-key"
                                        title="Cancel Contest"
                                        onClick={() => this.cancelMatchModalToggle(contest.contest_unique_id, 2, group_index, idx)}
                                      ></i>
                                    </li>
                                  }
                                  {
                                    contest.guaranteed_prize == '2' &&
                                    <li className="action-item">
                                      <i className="icon-icon-g"></i>
                                    </li>
                                  }
                                  {
                                    contest.multiple_lineup > 1 &&
                                    <li className="action-item">
                                      <i className="icon-icon-m"></i>
                                    </li>
                                  }
                                  {
                                    contest.is_auto_recurring == "1" &&
                                    <li className="action-item">
                                      <i className="icon-icon-r contest-type"></i>
                                    </li>
                                  }
                                  {
                                    contest.is_reverse == "1" &&
                                    <li className="action-item">
                                      <img className="reverse-contest ml-0" title="Reverse contest" src={Images.REVERSE_FANTASY} />
                                    </li>
                                  }
                                  {
                                    contest.total_user_joined == 0 &&
                                    <li className="action-item">
                                      <i title="Delete Contest" className="icon-delete contest-type" onClick={(e) => this.deleteContest(e, contest, idx)}></i>
                                    </li>
                                  }

                                  {
                                    (!ComingFrom && (selected_sport == 7 || selected_sport == 5) && fixtureDetail.allow_bots == "1" && AllowSystemUser && (contest['is_2nd_inning'] == undefined || contest['is_2nd_inning'] == '0') && this.state.isDFS)
                                    &&
                                    <li className="action-item">
                                      <i
                                        title="Add system user"
                                        className="icon-select_player"
                                        onClick={() => this.props.history.push({ pathname: '/system-users/add-system-users/' + this.state.collection_master_id + '/' + this.state.season_id + '/' + contest.contest_unique_id })}
                                      ></i>
                                    </li>
                                  }
                                  {
                                    (HF.allowScratchWin() == '1') &&
                                    <li className={`action-item ${fixtureDetail.match_started == 0 ? '' : 'cursor-dis'}`}>
                                      <i
                                        onClick={() => fixtureDetail.match_started == 0 ? this.addRemoveScWinModal(contest.contest_id, group_index, idx, contest.is_scratchwin) : null}
                                        id={"swin_" + group_index + '_' + idx} className={`icon-SW contest-type ${(contest.is_scratchwin == "1") ? '' : 'not-active'}`}>
                                        <span className="btn-information">
                                          <Tooltip
                                            placement="right"
                                            isOpen={contest.swin_tt}
                                            target={"swin_" + group_index + '_' + idx}
                                            toggle={() => this.SWinToggle(group_index, idx, 'swin_tt')}>
                                            {SCRATCH_WIN_TAP}
                                          </Tooltip>
                                        </span>
                                      </i>
                                    </li>
                                  }
                                </ul>
                                {/* </div>
                              <div className="clearfix"> */}
                                <h3 className="win-type yyy">
                                  {
                                    (!_.isUndefined(contest.contest_title) && !_.isEmpty(contest.contest_title) && !_.isNull(contest.contest_title)) ?
                                      <span className="prize-pool-value">{contest.contest_title}</span>
                                      :
                                      <span>
                                        <span className="prize-pool-text">WIN </span>
                                        <span className="prize-pool-value" dangerouslySetInnerHTML={this.getPrizeAmount(contest.prize_distibution_detail)}>
                                        </span>
                                      </span>
                                  }
                                </h3>
                              </div>
                              <div className="text-small-italic">
                                <span onClick={(e) => this.viewWinners(e, contest)}>{this.getWinnerCount(contest)}</span>
                                <span className="b-allow">{contest.max_bonus_allowed ? contest.max_bonus_allowed : '0'}% Bonus allowed</span>
                              </div>
                            </div>
                            <div className="display-table">
                              <div className="progress-bar-default display-table-cell v-mid">
                                <div className="danger-area progress">
                                  <div className="text-center"></div>
                                  {((selected_sport == "7" || selected_sport == "5") && fixtureDetail.playing_eleven_confirm == "1" && fixtureDetail.allow_bots == "1" && AllowSystemUser) ? <Progress className="com-contest-mul-progress" multi>

                                    <Progress bar className="su-progress" value={this.ShowProgressBar(contest.total_system_user, contest.size)} >
                                      <span className="su-count">System user {contest.total_system_user}</span>
                                    </Progress>
                                    <Progress bar className="com-contest-progress all-u-progress" value={this.ShowProgressBar(parseInt(contest.total_user_joined) - parseInt(contest.total_system_user), contest.size)} >
                                      <span className="total-u-count">Total user {contest.total_user_joined}</span>
                                    </Progress>
                                  </Progress> : <Progress value={this.ShowProgressBar(contest.total_user_joined, contest.size)} />}


                                </div>
                                <div className="progress-bar-value"><span className="user-joined">{contest.total_user_joined}</span><span className="total-entries"> / {contest.size} Entries</span><span className="min-entries">min {contest.minimum_size}</span></div>

                                <GetContestTournament contestFixtureList={this.state.contestFixtureList} contest_id={contest.contest_id}/>
                              </div>
                              <div className="display-table-cell v-mid entry-criteria">
                                <button type="button" className="white-base btnStyle btn-rounded btn btn-primary">
                                  {
                                    contest.currency_type == '0' && contest.entry_fee > 0 &&
                                    <span>
                                      <i className="icon-bonus"></i>
                                      {HF.getPrizeInWordFormat(parseInt(contest.entry_fee))}
                                      {/* {contest.entry_fee} */}
                                    </span>
                                  }
                                  {
                                    contest.currency_type == '1' && contest.entry_fee > 0 &&
                                    // <span><i className="icon-rupess"></i>{contest.entry_fee}</span>
                                    <span>
                                      {HF.getCurrencyCode()}
                                      {HF.getPrizeInWordFormat(parseInt(contest.entry_fee))}
                                      {/* {contest.entry_fee} */}
                                    </span>
                                  }
                                  {
                                    contest.currency_type == '2' && contest.entry_fee > 0 &&
                                    <span>
                                      <img src={Images.COINIMG} alt="coin-img" />
                                      {HF.getPrizeInWordFormat(parseInt(contest.entry_fee))}
                                      {/* {contest.entry_fee} */}
                                    </span>
                                  }
                                  {contest.entry_fee == 0 &&

                                    <span>Free</span>

                                  }
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    {
                      contest.sponsor_logo &&
                      <div className="league-listing-container xmar-10">
                        {/* <div className="sponsor-name float-left">
                        <div>{contest.sponsor_logo ? "Sponsored by :" : 'Sponsor not assigned'}</div>
                      </div> */}
                        <div className="spr-card-img">
                          {
                            (contest.sponsor_logo && contest.sponsor_link) &&
                            <a target="_blank" href={contest.sponsor_link}>
                              <img src={NC.S3 + NC.SPONSER_IMG_PATH + contest.sponsor_logo} alt="" />
                            </a>
                          }
                          {
                            (contest.sponsor_logo && contest.sponsor_link == null) &&
                            <img src={NC.S3 + NC.SPONSER_IMG_PATH + contest.sponsor_logo} alt="" />
                          }
                        </div>
                      </div>
                    }
                    {
                      fixtureDetail.is_tour_game == 0 && this.state.isDFS
                      &&
                      <div className="promote-container mt-2">

                        {
                          fixtureDetail.match_started == 0 &&
                          <p onClick={() => this.toggleContestPromoteModal(idx, contest)}><img src={Images.PROMOTE} alt="" className="promote-img" /><span>Promote</span> </p>
                        }
                      </div>
                    }

                  </div>
                </Col>
              ))}
            </Row>
          </div>
        ))}
        {contestList.length <= 0 &&
          <div className="no-records">No Record Found.</div>
        }

        <div className="winners-modal-container">
          <Modal isOpen={this.state.prize_modal} toggle={() => this.closePrizeModel()} className="winning-modal">
            <ModalHeader toggle={this.toggle}>Winnings Distribution</ModalHeader>
            <ModalBody>
              <div className="distribution-container">
                {
                  contestObj.prize_distibution_detail &&
                  <table>
                    <tbody>
                      <tr>
                        <th>Rank</th>
                        <th style={{ width: "100px", textAlign: "center" }}>Min</th>
                        <th style={{ width: "100px", textAlign: "center" }}>Max</th>
                      </tr>
                      {contestObj.prize_distibution_detail.map((prize, idx) => (
                        <tr>
                          <td className="text-left">
                            {prize.min}
                            {
                              prize.min != prize.max &&
                              <span>-{prize.max}</span>
                            }
                          </td>
                          <td className="text-center">
                            {
                              prize.prize_type == '0' &&
                              <i className="icon-bonus"></i>
                            }
                            {
                              (!prize.prize_type || prize.prize_type == '1') &&
                              HF.getCurrencyCode()
                            }
                            {
                              prize.prize_type == '2' &&
                              <img src={Images.COINIMG} alt="coin-img" />
                            }
                            {
                              prize.prize_type == '3' &&
                              (prize.min_value + ' (' + parseInt(prize.mer_price * prize.min) + ')')
                            }
                            {
                              prize.prize_type != '3' &&
                              HF.getNumberWithCommas(prize.min_value)
                              // parseFloat(prize.min_value).toFixed(2)
                            }
                          </td>
                          <td className="text-center">
                            {
                              prize.prize_type == '0' &&
                              <i className="icon-bonus"></i>
                            }
                            {
                              (!prize.prize_type || prize.prize_type == '1') &&
                              HF.getCurrencyCode()
                            }
                            {
                              prize.prize_type == '2' &&
                              <img src={Images.COINIMG} alt="coin-img" />
                            }
                            {
                              prize.prize_type == '3' &&
                              (prize.max_value + ' (' + parseInt(prize.mer_price * prize.min) + ')')
                            }
                            {
                              prize.prize_type != '3' &&
                              HF.getNumberWithCommas(prize.max_value)
                              // parseFloat(prize.max_value).toFixed(2)
                            }
                          </td>
                        </tr>

                      ))}
                    </tbody>
                  </table>
                }
              </div>
            </ModalBody>
            <ModalFooter>
              <Button className="close-btn" color="secondary" onClick={() => this.closePrizeModel()}>Close</Button>
            </ModalFooter>
          </Modal>
        </div>

        {
          this.state.contest_promote_model &&
          <PromoteContestModal IsPromoteContestShow={this.state.contest_promote_model} IsPromoteContestHide={this.PromoteContestHide} ContestData={{ contestObj: contestObj }} />}

        {
          this.state.promote_model &&
          <PromoteNotActive IsPromoteShow={this.state.promote_model} IsPromoteHide={this.PromoteHide} />}

        {/* {
          showFixtureModal && <DFSFixtureModal show={showFixtureModal} hide={this.hideFixtureModal} data={collection_master_id} ContestData={ContestData} saveFn={this.saveTourFixture()}/>} */}
        {showFixtureModal &&
        
          <Modal
            isOpen={showFixtureModal}
            className="match-msg-modal fix-sel-mdl"
            toggle={!showFixtureModal}
          >
            <ModalHeader className="">
              {int_version == "1" ? "Add Tournament" : "Add Tournament"}
             
            </ModalHeader>
            <ModalBody>

              {
                // console.log(this.state.contestFixtureList,'sdadas')


              }
              <h2>This contest is available for the following tournaments </h2>
              {/* <p style={{fontSize:"20px",textAlign:"center"}}> Tournaments </p> */}
              {/* <p  style={{fontSize:"15px",textAlign:"center"}}> This contest is avilable for the following tournaments </p> */}
              {/* <div className="list-view">
                <div className="sel-fix-lbl">{int_version == "1" ? 'Select Games' : 'Select Tournament'} </div> */}

                           <>{this.renderTourList(this.state.contestFixtureList, this.state.ContestData)}</>

                
              {/* </div> */}
            </ModalBody>
            <ModalFooter className="border-0 justify-content-center">
              <Button
                disabled={!this.state.isMore}
                onClick={() => this.saveTourFixture(this.state.collection_master_id, this.state.selFixList, this.state.ContestData.contest_id)}
                className="btn-secondary-outline"
              >Save</Button>
              <Button
                onClick={() => this.hideFixtureModal()}
                className="btn-secondary-outline"
              >Close</Button>
            </ModalFooter>
          </Modal>}
      </div>
    );
  }
}

export default FixtureContest;
const GetContestTournament = ({contestFixtureList,contest_id}) => {
  let leaderboard = _filter(contestFixtureList,(item, idx) => item.contest_id == contest_id)
  // console.log("leaderboard",leaderboard);
  let count_html = ""
  if(leaderboard.length > 1){
    count_html = " + "+(leaderboard.length - 1)
  }
  return (
    leaderboard.length > 0  ? <div className="progress-bar-value leaderborad-ss"><span className="user-joined">Leaderboard - </span><span className="user-joined">{leaderboard[0].name+" "+count_html}</span></div> : ""
  )
}