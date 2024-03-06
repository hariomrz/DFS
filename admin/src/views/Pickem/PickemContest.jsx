import React, { Component, Fragment } from 'react';
import { Row, Col, Button, TabContent, TabPane, Nav, NavItem, NavLink, UncontrolledDropdown, DropdownToggle, DropdownMenu, DropdownItem, Modal, ModalBody, ModalHeader, ModalFooter, Input } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import _, { isEmpty } from 'lodash';
import Images from "../../components/images";
import { PF_CANCEL_FIXTURE, pickemResult, deletePickem, PT_getAllTournament, getPickemTournamentList, getPickemTournamentDetail, saveTieBreakerAnswer, submitQaPickem, pickemMarkCompleted, cancelTournament } from '../../helper/WSCalling';
import { notify } from 'react-notify-toast';
import LS from 'local-storage';
import Pagination from "react-js-pagination";
import 'react-circular-progressbar/dist/styles.css';
import HF, { _times, _Map, _isUndefined } from "../../helper/HelperFunction";
import { MomentDateComponent } from "../../components/CustomComponent";
import { PKM_PUBLISH_MSG, PKM_CONFIRM_MSG, PKM_PUBLISH_SUB_MSG, PKM_DELETE_MSG } from "../../helper/Message";
import moment from 'moment';
import WSManager from '../../helper/WSManager';
import CancelTournamentModal from './CancelTournamentModal'
import PinFxModal from './PinFxModal';
import { CircularProgressbar } from 'react-circular-progressbar';
import PickemSubmitConfirmationPopup from './PickemSubmitConfirmationPopup';
import PickemMarkCompletedModal from './PickemMarkCompletedModal';
import ConfrimDeleteMatch from './ConfrimDeleteMatch';



const saveResult = []
class PickemContest extends Component {
  constructor(props) {
    super(props);
    this.state = {
      PARTI_CURRENT_PAGE_ST: 1,
      CURRENT_PAGE: 1,
      PERPAGE: NC.ITEMS_PERPAGE,
      SelectedOption: '',
      SelectedSport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
      DeleteModalOpen: false,
      ResultModalOpen: false,
      SelectLeague: '',
      activeTab: '2',
      publishModalOpen: false,
      PickemList: [],
      UnpubPickemList: [],
      UnpubPosting: false,
      LeagueOptions: [],
      ParticipantsList: [],
      SelectedLeague: "",
      publishPosting: false,
      Posting: false,
      usersModalOpen: false,
      PartiListPosting: true,
      sportName: '',
      PickemAllowDraw: '',
      // ptActiveTab: '2',
      ptChildActiveTab: '2',
      PT_PickemList: [],
      PT_Posting: false,
      MerchandiseList: [],
      PT_usersModalOpen: false,
      PT_ParticipantsList: [],
      PT_PARTI_CURRENT_PAGE_ST: 1,
      PT_ldrbrdModalOpen: false,
      PT_ldrbrdList: [],
      PT_LdrBrdPosting: true,
      PT_LDRBRD_CURRENT_PAGE: 1,
      MODAL_PERPAGE: 10,
      TotalPickemTournament: [],
      tournDetails: '',
      tournDetailsQues: 'null',
      tournMatches: [],
      answerField: '',
      home_team: 0,
      block_team: 0,
      away_team: 0,
      visibleCnclModal: false,
      cancel_reason: '',
      fxPinModalOpen: false,
      l_id: '',
      tourna_id: this.props.match.params.tournament_id,
      user_count: this.props.match.params.user_count,
      storeSaveTieBreakerAnswer: '',
      completeFixtures: [],
      teamIds: [],
      seasonIDs: [],
      activeID: '',
      liveList: [],
      upList: [],
      comList: [],
      isPin: '',
      submitPickemConfirmPop: false,
      count: 0,
      markComplete: false,
      isSubmitenable: false,
      deleteMatchConfirm: false,
      deleteItemData: '',
      preActiveTab: 2
    };
  }

  componentDidMount() {
    this.pickemDetail();
    this.setState({
      activeTab: this.props && this.props.location && this.props.location.state && this.props.location.state.pickDetailActTab ? this.props.location.state.pickDetailActTab : this.state.activeTab,
      preActiveTab: this.props && this.props.location && this.props.location.state && this.props.location.state.preActiveTab ? this.props.location.state.preActiveTab : 2
    })
  }


  pickemDetail = (activetab) => {
    let param = {
      tournament_id: this.state.tourna_id ? this.state.tourna_id : ''
    }
    getPickemTournamentDetail(param).then(Response => {
      if (Response.response_code == NC.successCode) {
        this.setState({
          tournDetails: Response.data,
          tournDetailsQues: (Response.data && Response.data.tie_breaker_question != null) ? JSON.parse(Response.data.tie_breaker_question) : 'null',
          tournMatches: (Response.data && Response.data.match.length > 0) ? Response.data.match : []
        }, () => {
          if (this.state.tournDetails.status == 3 || this.state.tournDetails.status == 2) {
            this.setState({
              activeTab: '3',
            })
          }
          this.fixByStatus(this.state.tournMatches)
        })
      } else {
        notify.show(NC.SYSTEM_ERROR, 'error', 5000)
      }
    }).catch(error => {
      notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    })
  }

  fixByStatus = (tournMatches) => {

    let liveList = tournMatches && tournMatches.length > 0 ? tournMatches.filter(
      (obj) =>
        (obj.status != '2' && obj.status != '3' && this.getFormatedDateTime(Date.now(), 'YYYY-MM-D HH:mm ')) > this.getFormatedDateTime(WSManager.getUtcToLocal(obj.scheduled_date), 'YYYY-MM-D HH:mm ')
    ) : []


    let upList = tournMatches && tournMatches.length > 0 ? (tournMatches.filter(
      (obj) => (obj.status != '2' && obj.status != '3' && this.getFormatedDateTime(Date.now(), 'YYYY-MM-D HH:mm ')) < this.getFormatedDateTime(WSManager.getUtcToLocal(obj.scheduled_date), 'YYYY-MM-D HH:mm '))
    ) : []

    let comList = tournMatches && tournMatches.length > 0 ? tournMatches.filter((obj) => obj.status == "2") : []

    this.setState({
      liveList: liveList.reverse(),
      upList: upList, //upList.reverse(),
      comList: comList.reverse()
    })

  }


  toggle(tab) {
    if (this.state.activeTab !== tab) {
      this.setState({
        activeTab: tab,
        CURRENT_PAGE: 1,
        UNPUB_CURRENT_PAGE: 1,
      })
    }
  }

  getAllPickem = () => {
    let { SelectedLeague, activeTab, SelectedSport, SelectLeague, CURRENT_PAGE, PERPAGE, StartDate, EndDate } = this.state
    this.setState({ Posting: true })
    let params = {
      sports_id: SelectedSport,
      status: activeTab == 1 ? "live" : activeTab == 2 ? "upcoming" : "completed"
      // league_id: SelectedLeague,
      // fromdate: StartDate ? moment(StartDate).format("YYYY-MM-DD") : '',
      // todate: EndDate ? moment(EndDate).format("YYYY-MM-DD") : '',
      // match_type: activeTab,
      // items_perpage: PERPAGE,
      // current_page: CURRENT_PAGE,
      // sort_field: "season_scheduled_date",
      // sort_order: "ASC"
    }

    getPickemTournamentList(params).then(Response => {
      if (Response.response_code == NC.successCode) {
        this.setState({
          // PickemAllowDraw: Response.data.pickem_allow_draw[SelectedSport],
          // PickemList: Response.data.result,
          // TotalPickem: Response.data.total,
          // Posting: false,
          TotalPickemTournament: Response.data.result
        })
        // HF.scrollView('scrolldiv')
      } else {
        notify.show(NC.SYSTEM_ERROR, 'error', 5000)
      }
    }).catch(error => {
      notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    })
  }

  // getUnpublishedMatches = () => {
  //   let { SelectedSport, UNPUB_CURRENT_PAGE, PERPAGE } = this.state
  //   let params = {
  //     sports_id: SelectedSport,
  //     items_perpage: PERPAGE,
  //     current_page: UNPUB_CURRENT_PAGE,
  //   }

  //   getUnpubMatches(params).then(Response => {
  //     if (Response.response_code == NC.successCode) {
  //       this.setState({ UnpubPosting: true })
  //       this.setState({
  //         UnpubPickemList: Response.data.result,
  //         TotalUnpubPickem: Response.data.total,
  //         UnpubPosting: false,
  //       })
  //     } else {
  //       notify.show(NC.SYSTEM_ERROR, 'error', 5000)
  //     }
  //   }).catch(error => {
  //     notify.show(NC.SYSTEM_ERROR, 'error', 5000)
  //   })
  // }

  getAllLeagues = () => {
    this.setState({ PagePosting: true })
    let { SelectedSport } = this.state
    let params = {
      sports_id: SelectedSport
    }
    let leagueOptions = [{ value: '', label: 'All' }]
    let obj = {}
    // getLeagues(params).then(Response => {
    //   if (Response.response_code == NC.successCode) {
    //     this.setState({
    //       LeagueList: Response.data.league_list,
    //     }, () => {
    //       _.map(this.state.LeagueList, (item, idx) => {
    //         obj = { value: item.league_id, label: item.league_name }
    //         leagueOptions.push(obj)
    //       })
    //       this.setState({
    //         LeagueOptions: leagueOptions
    //       })
    //     })
    //   } else {
    //     notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    //   }
    // }).catch(error => {
    //   notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    // })
  }

  handleTypeChange = (value) => {
    let { activeTab, ptActiveTab } = this.state
    if (value != null) {
      this.setState({ SelectedLeague: value.value }, function () {

        // if (ptActiveTab == '1') {
        //   this.getAllPickem()
        //   if (activeTab == '2') {
        //     this.getUnpublishedMatches()
        //   }
        // }
        // if (ptActiveTab == '2') {
        //   this.getAllTornament()
        // }
      })
    }
  }

  deleteToggle = (setFalg, PickemId, idx) => {
    if (setFalg) {
      this.setState({
        deleteIndex: idx,
        PICKEM_Id: PickemId,
      })
    }
    this.setState(prevState => ({
      DeleteModalOpen: !prevState.DeleteModalOpen
    }));
  }

  deletePickemItem = () => {
    const { deleteIndex, PICKEM_Id, PickemList } = this.state
    const param = { pickem_id: PICKEM_Id }
    let tempPickemList = PickemList
    deletePickem(param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        _.remove(tempPickemList, function (item, idx) {
          return idx == deleteIndex
        })
        this.deleteToggle('', deleteIndex, PICKEM_Id)
        notify.show(responseJson.message, "success", 5000);
        this.setState({
          PickemList: tempPickemList
        })
      }
    }).catch((error) => {
      notify.show(NC.SYSTEM_ERROR, "error", 5000);
    })
  }

  DeclareResultToggle = () => {
    this.setState({ ResultModalOpen: !this.state.ResultModalOpen });
  }

  updatePickemResult = () => {
    this.setState({ ResultPosting: true })
    let params = {
      data: saveResult
    }
    pickemResult(params).then(Response => {
      if (Response.response_code == NC.successCode) {
        notify.show(Response.message, "success", 5000)
        this.getAllPickem()
        this.DeclareResultToggle()
        saveResult.splice(0, saveResult.length)
      } else {
        notify.show(NC.SYSTEM_ERROR, 'error', 5000)
      }
      this.setState({ ResultPosting: false })
    }).catch(error => {
      notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    })
  }

  selectOption = (pickemId, teamUID, index, flag) => {
    let tempLiveList = this.state.PickemList

    if (tempLiveList[index].selectedTeamId === teamUID && tempLiveList[index].pickem_id === pickemId) {
      tempLiveList[index].indedexValue = ''
      tempLiveList[index].selectedTeamId = ''
    } else {
      tempLiveList[index].indedexValue = index
      tempLiveList[index].selectedTeamId = teamUID
    }
    let inpObj = {
      selectedIndex: index,
      pickem_id: pickemId,
      team_uid: teamUID
    }
    let isExist = false
    _.remove(saveResult, function (item, idx) {
      if (teamUID == item.team_uid && pickemId == item.pickem_id) {
        isExist = true
        return teamUID == item.team_uid && pickemId == item.pickem_id
      }
    })
    _.map(saveResult, (item, idx) => {
      if (pickemId == item.pickem_id) {
        isExist = true
        saveResult[idx] = inpObj
      }
    })

    if (!isExist) {
      saveResult.push(inpObj)
    }

    this.setState({
      SelectedOption: teamUID,
      SelectedIndex: index,
      OptionType: flag,
      saveResult: inpObj
    })
  }

  handlePageChange(current_page, flag) {
    let { CURRENT_PAGE, UNPUB_CURRENT_PAGE } = this.state
    if (flag && current_page != CURRENT_PAGE) {
      this.setState({
        CURRENT_PAGE: current_page
      }, this.getAllPickem);
    }
    if (!flag && current_page != UNPUB_CURRENT_PAGE) {
      this.setState({
        UNPUB_CURRENT_PAGE: current_page
      }, this.getUnpublishedMatches);
    }
  }

  onComplete = (idx) => {
    let TempPickemList = this.state.PickemList
    TempPickemList[idx].onCompTimer = true
    this.setState({ PickemList: TempPickemList })
  }

  copyPickemUrl = (item) => {
    let { sportName } = this.state
    const el = document.createElement('textarea');
    el.value = NC.baseURL + sportName.toLowerCase() + NC.PickemShareUrl + item.league_id + '/' + btoa(item.pickem_id);

    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
    notify.show("Copied to clipboard", "success", 2000)
  }

  renderActionDropdown = (item, activeTab, idx, flag) => {
    return (
      <UncontrolledDropdown direction="right">

        <DropdownToggle tag="i" caret={false} className="icon-more"></DropdownToggle>
        <DropdownMenu>
          <DropdownItem
            onClick={() => this.copyPickemUrl(item)}>
            <i className="icon-share-fill"></i>Share
          </DropdownItem>
          {activeTab == "2" && <DropdownItem
            onClick={() => this.deleteToggle(true, item.pickem_id, idx)}
          >
            <i className="icon-delete1"></i>Delete
          </DropdownItem>}
        </DropdownMenu>
      </UncontrolledDropdown>
    )
  }

  //   redirectToContest = () => {
  //     this.props.history.push('/pickem/view-contest')
  //   }

  saveTieBreaker = (e) => {
    let param = { answer: this.state.answerField, tournament_id: this.state.tourna_id ? this.state.tourna_id : '' }
    saveTieBreakerAnswer(param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        this.setState({
          storeSaveTieBreakerAnswer: responseJson.data
        })
      }
    }).catch((error) => {
      notify.show(NC.SYSTEM_ERROR, "error", 5000);
    })
  }

  pTPinModal = (item, e, isPin) => {
    e.stopPropagation();

    this.setState({
      fxPinModalOpen: true,
      l_id: item,
      isPin: isPin
    })
  }

  openDetailingPage = (item) => {
    this.props.history.push({
      pathname: '/pickem/pickem-detail/' + item.tournament_id,
      state: {
        tournament_id: item.tournament_id,
        pickDetailActTab: this.state.activeTab
      }
    })
  }

  renderCricketCard = (flag) => {
    let { tournDetails, tournDetailsQues, storeSaveTieBreakerAnswer } = this.state
    let is_pin = (this.props && this.props.history.location.state) ? this.props.history.location.state.pin : '';
    let participant_count = (this.props && this.props.history.location.state) ? this.props.history.location.state.participant_count : '';
    let t_id = tournDetails && tournDetails.tournament_id;

    return (

      <>
        <div className="cricket-fixture-card mr-3">
          <div className="pickem-card-set cursor-pointer" onClick={() => this.openDetailingPage(tournDetails)}>

            <div className='pin-wrap'>
              <div className='pin-modal-left'>
                {
                  tournDetails && tournDetails.is_pin == '1' &&
                  <img onClick={(e) => this.pTPinModal(t_id, e, tournDetails.is_pin)} src={Images.PIN_ACTIVE} alt="" className="pinned-active" />
                }
                {
                  tournDetails.is_pin == '0' &&
                  <i onClick={(e) => this.pTPinModal(t_id, e, tournDetails.is_pin)} className="icon-pinned ml-2"></i>
                }
              </div>
            </div>



            <Row className='logo-details'>
              <div className='image-block'>
                {tournDetails.image != '' ? <img src={tournDetails.image} alt="" /> : <img src={Images.TROPHY} alt="" />}
              </div>
              <div className='image-detailing'>

                <p className='tour-name'>{tournDetails.name}</p>


                <p className='tour-date'>
                  {/* <MomentDateComponent data={{ date: tournDetails.start_date, format: "D MMM - " }} />
                  <MomentDateComponent data={{ date: tournDetails.end_date, format: "D MMM" }} />  */}
                  {HF.getFormatedDateTime(tournDetails.start_date, "D MMM - ")}
                  {HF.getFormatedDateTime(tournDetails.end_date, "D MMM")}
                  |  {tournDetails.match_count} {tournDetails.match_count > 1 ? 'Fixtures' : 'Fixture'}  | {Math.round(tournDetails.max_bonus)}% Bonus Allowed
                </p>
                <p className='count' onClick={(e) => this.getAllParticipantList(tournDetails.tournament_id, e)}>{this.state.user_count} Participant</p>
                <button className='entry-btn'>{tournDetails.entry_fee != "0" ? tournDetails.currency_type == "1" ? <div className="icon-rupess"></div> : <img src={Images.COIN} width="15" /> : ''}<div>{tournDetails.entry_fee == "0" ? 'Free' : tournDetails.entry_fee}</div></button>

              </div>
            </Row>
            <div className='league-name-block'>
              {tournDetails.league_name}
            </div>
          </div>
        </div>
        <div className='w-100'>
          {tournDetailsQues != "null" && <div className="pickem-card-set qa-card">
            <div className='questionary'>
              <p className='que'>Question</p>
              <p className='ans'>{tournDetailsQues.question == "" ? "-" : tournDetailsQues.question}</p>
              <p className='que'>Range</p>
              <p className='ans'>{tournDetailsQues.start}-{tournDetailsQues.end}</p>
            </div>
            <div className='pl-3 pr-3 w-100 correct-answer-section'>
              <p>Correct Answer</p>
              {((storeSaveTieBreakerAnswer != '' || tournDetails.tie_breaker_answer != "0") && this.state.preActiveTab == 2) ?
                <div className='flex-input'>
                  <Input type="number" name='answer' className='answer-input' value={storeSaveTieBreakerAnswer ? storeSaveTieBreakerAnswer : tournDetails.tie_breaker_answer} disabled />
                  <Button className="btn-secondary-outline disabled">Save</Button>
                </div>
                :
                <div className='flex-input'>
                  <Input type="number" name='answer' className='answer-input' onChange={(e) => this.setState({ answerField: e.target.value })} />
                  <Button className="btn-secondary-outline" onClick={this.saveTieBreaker}>Save</Button>
                </div>}
            </div>

          </div>}
        </div>
      </>


    )
  }



  renderCommonView = (flag) => {
    return (
      <div className="mt-30 d-flex">
        {
          this.renderCricketCard(flag)
        }
      </div>
    )
  }


  submitPickemId = (item, op) => {
    let seasonID = []
    let tmpArray = []
    // let count = parseInt(this.state.count)
    _Map(this.state.liveList, (data, idx) => {
      if (data.season_id == item.season_id) {
        if (data.user_sel_team_id && data.user_sel_team_id == op) {
          delete data.user_sel_team_id;
        }
        else {
          data['user_sel_team_id'] = op
        }
        seasonID.push({
          "season_id": data.season_id, "team_id": op
        })
      }
      tmpArray.push(data)
    })
    this.setState({
      listLive: tmpArray,
      seasonIDs: seasonID
    }, () => {
      let count = this.state.listLive.filter(obj => obj.user_sel_team_id) || []
      count = count.length || 0
      this.setState({
        count: count
      })
    })
  }

  submitPickemConfirmationpop = () => {
    this.setState({
      submitPickemConfirmPop: true
    })
  }

  deleteMatchConfirm = (item) => {
    this.setState({
      deleteMatchConfirm: true,
      deleteItemData: item.season_id
    })
  }


  deleteMatchConfirmClose = () => {
    this.setState({
      deleteMatchConfirm: false,
      deleteItemData: ''
    })
  }

  deleteMatchFitures = () => {
    let param = {
      "tournament_id": this.state.tourna_id ? this.state.tourna_id : '',
      "season_id": this.state.deleteItemData
    }
    PF_CANCEL_FIXTURE(param).then(Response => {
      if (Response.response_code == NC.successCode) {
        this.setState({
          upList: []
        }, () => {
          this.deleteMatchConfirmClose()
          this.pickemDetail()
        })
      } else {
        this.deleteMatchConfirmClose()
        notify.show(Response.message, 'error', 5000)
      }
    }).catch(error => {
      notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    })
  }

  getFormatedDateTime = (date, format) => {
    if (format) {
      // return moment.utc(date).local().format(format);
      return moment.utc(date).local().format();
    }
    return moment(date).utc().local().format();
  }

  handleScore = (e, teamkey, idx) => {
    let value = e.target.value
    if ((parseInt(value) >= 0 && parseInt(value) <= 99) || value == '') {
      let tmpList = _.cloneDeep(this.state.liveList)

      // home score and away score value assign into liveList index
      tmpList[idx][teamkey] = value


      if (teamkey == 'away_score' && !tmpList[idx]['home_score']) {
        tmpList[idx]['home_score'] = ''
      }
      if (teamkey == 'home_score' && !tmpList[idx]['away_score']) {
        tmpList[idx]['away_score'] = ''
      }

      this.setState({
        liveList: tmpList
      }, () => {
        this.valideSubmit()
      })
    }
    else {
      notify.show('The Score Should 0 to 99', "error", 5000);
      this.valideSubmit()

    }
  }

  valideSubmit = () => {
    const { liveList } = this.state;
    let count = 0
    liveList.map((item, idx) => {
      if ((item.away_score && item.away_score != '' && item.home_score && item.home_score != '') || (!item.away_score && !item.home_score)) {
        count = count + 1
      }
    })
    this.setState({
      isSubmitenable: liveList.length == count ? true : false,
    })
  }

  getJsonScoreData = (data) => {
    try {
      return JSON.parse(data)
    }
    catch {
      return data
    }
  }
  renderOptionFieldLive = () => {
    const { liveList, count, tournDetails } = this.state;

    return (
      <React.Fragment>
        <Row>
          {liveList.map((item, idx) => {
            let isWinning = item.winning_team_id ? item.winning_team_id : '';
            // let homeJoined = (parseInt(item.home_count) / parseInt(item.totoal_season_count)) * 100;
            // let drawJoined = (parseInt(item.draw_count) / parseInt(item.totoal_season_count)) * 100;
            // let awayJoined = (parseInt(item.away_count) / parseInt(item.totoal_season_count)) * 100;

            return (
              <Col md="4" key={idx}>
                <div className="live-comp-date pt-c-gray">
                  {/* <MomentDateComponent data={{ date: item.scheduled_date, format: "D MMM - hh:mm A" }} /> */}
                  {HF.getFormatedDateTime(item.scheduled_date, "D MMM - hh:mm A")}
                  {item.status == 4 && <div className="cancel-text-view">cancel</div>}
                  {item.is_selected &&
                    <div className="right-selection">
                      <img src={Images.tick} className="rght-img" />
                    </div>

                  }
                </div>
                <div className={`pickem-card-set ${tournDetails.is_score_predict != 1 ? 'option-set' : 'option-set-cond'} `} >
                  <Col xs={tournDetails.is_score_predict == 1 ? '6' : '6'} className={`home ${(item.user_sel_team_id && item.user_sel_team_id == item.home_id) ? 'background-home' : ''}`}>
                    <span className='pick-perc-value'>
                      <CircularProgressbar value={this.PickedPercentage(
                        parseFloat(item.home_count || 0),
                        item.total_season_count ? item.total_season_count : 100
                      )} text={`${this.PickedPercentage(
                        parseFloat(item.home_count || 0),
                        item.total_season_count ? item.total_season_count : 100
                      )}%`} />
                    </span>
                    <div onClick={() => { tournDetails.is_score_predict != 1 && this.submitPickemId(item, item.home_id) }} >
                      {(item.user_sel_team_id && item.user_sel_team_id == item.home_id) && <img src={Images.APPROVAL} className="approval" />}

                      <div className="right-approval">
                        {/* <p>{homeJoined}</p> */}
                      </div>
                      <img src={item.home_flag ? NC.S3 + NC.FLAG + item.home_flag : Images.dummy_user} width="60" height="60" />
                      <p className='draw-home'>{item.home}</p>
                    </div>
                    {
                      tournDetails.is_score_predict == 1 &&
                      <Input
                        maxLength="2"
                        placeholder="Enter Score"
                        type="text"
                        name=""
                        className={item.home_score == '' ? 'scoreTextboxRed' : 'scoreTextbox'}
                        onChange={(e) => this.handleScore(e, 'home_score', idx)}
                      />
                    }
                  </Col>
                  {/* i have removed {} from this code below */}
                  {/* tournDetails.is_score_predict == 0 &&  */}

                  {/* <Col xs={tournDetails.is_score_predict == 1 ? '6' : '4'} className={`draw ${(item.user_sel_team_id && item.user_sel_team_id == "0") ? 'background-draw' : ''}`}>
                    <span className='pick-perc-value'>
                      <CircularProgressbar value={this.PickedPercentage(
                        parseFloat(item.draw_count || 0),
                        item.total_season_count ? item.total_season_count : 100
                      )} text={`${this.PickedPercentage(
                        parseFloat(item.draw_count || 0),
                        item.total_season_count ? item.total_season_count : 100
                      )}%`} />
                    </span>
                    <div onClick={() => {tournDetails.is_score_predict != 1 && this.submitPickemId(item, '0')}}>
                      {(item.user_sel_team_id && item.user_sel_team_id == "0") && <img src={Images.APPROVAL} className="approval" />}

                      <div className="right-approval">
                        {<p>{drawJoined}</p> }this was already commented
                      </div>
                      <img src={Images.BLOCK} width="60" height="60" />
                      <p className='draw-home'>draw</p>
                    </div>
                  </Col> */}

                  <Col xs={tournDetails.is_score_predict == 1 ? '6' : '6'} className={`away ${(item.user_sel_team_id && item.user_sel_team_id == item.away_id) ? 'background-away' : ''}`}>
                    <span className='pick-perc-value'>
                      <CircularProgressbar value={this.PickedPercentage(
                        parseFloat(item.away_count || 0),
                        item.total_season_count ? item.total_season_count : 100
                      )} text={`${this.PickedPercentage(
                        parseFloat(item.away_count || 0),
                        item.total_season_count ? item.total_season_count : 100
                      )}%`} />
                    </span>
                    <div onClick={() => { this.state.tournDetails.is_score_predict != 1 && this.submitPickemId(item, item.away_id) }}
                    >
                      {(item.user_sel_team_id && item.user_sel_team_id == item.away_id) && <img src={Images.APPROVAL} className="approval" />}

                      <div className="right-approval">
                        {/* <p>{awayJoined}</p> */}
                      </div>
                      <img src={item.away_flag ? NC.S3 + NC.FLAG + item.away_flag : Images.dummy_user} width="60" height="60" />
                      <p className='draw-home'>{item.away}</p>
                      {
                        tournDetails.is_score_predict == 1 &&

                        <Input
                          maxLength="2"
                          placeholder="Enter Score"
                          type="text"
                          name=""
                          className={item.away_score == '' ? 'scoreTextboxRed' : 'scoreTextbox'}
                          onChange={(e) => this.handleScore(e, 'away_score', idx)}
                        />
                      }
                    </div>
                  </Col>
                </div>
              </Col>
            )
          })}
        </Row>
        {liveList.length > 0 && <div className='d-flex justify-content-center pt-4 pb-4'>
          <Button
            onClick={() => this.submitPickemConfirmationpop()}
            disabled={tournDetails.is_score_predict == 1 ? !this.state.isSubmitenable : count == 0}
          >Submit</Button>
        </div>}
      </React.Fragment>
    )
  }


  renderOptionFieldUpcoming = (listUpcoming) => {
    const { tournDetails } = this.state
    return (
      <React.Fragment>
        <Row>
          {listUpcoming.map((item, idx) => {
            let isWinning = item.winning_team_id ? item.winning_team_id : ''
            // let homeJoined = (parseInt(item.home_count) / parseInt(item.total_season_count)) * 100;
            // let drawJoined = (parseInt(item.draw_count) / parseInt(item.total_season_count)) * 100;
            // let awayJoined = (parseInt(item.away_count) / parseInt(item.total_season_count)) * 100;
            return (
              <Col md="4" key={idx}>
                <div className="live-comp-date pt-c-gray">
                  {/* <MomentDateComponent data={{ date: item.scheduled_date, format: "D MMM - hh:mm A" }} /> */}
                  {HF.getFormatedDateTime(item.scheduled_date, "D MMM - hh:mm A")}
                  {item.status == 4 && <div className="cancel-text-view">cancel</div>}
                  {item.is_selected &&
                    <div className="right-selection">
                      <img src={Images.tick} className="rght-img" />
                    </div>
                  }
                  {
                    listUpcoming.length > 1 && tournDetails.status != '2' && tournDetails.status != '3' &&
                    // (tournDetails.status != '2' && tournDetails.status != '3' && this.getFormatedDateTime(Date.now(), 'YYYY-MM-D HH:mm ')) < this.getFormatedDateTime(WSManager.getUtcToLocal(tournDetails.start_date), 'YYYY-MM-D HH:mm ')  &&
                    <i className='icon-delete deletePickMatch' onClick={() => this.deleteMatchConfirm(item)}></i>
                  }
                </div>
                <div className="pickem-card-set d-flex option-set">
                  <Col xs={this.state.tournDetails.is_score_predict == 1 ? '6' : '6'} className={`home`}>
                    <span className='pick-perc-value'>
                      <CircularProgressbar value={this.PickedPercentage(
                        parseFloat(item.home_count || 0),
                        item.total_season_count ? item.total_season_count : 100
                      )} text={`${this.PickedPercentage(
                        parseFloat(item.home_count || 0),
                        item.total_season_count ? item.total_season_count : 100
                      )}%`} />
                    </span>
                    <div>
                      <div className="right-approval">
                        {/* <p>{homeJoined}</p> */}
                      </div>
                      <img src={item.home_flag ? NC.S3 + NC.FLAG + item.home_flag : Images.dummy_user} width="60" height="60" />
                      <p className='draw-home'>{item.home}</p>
                    </div>
                  </Col>
                  {/* {
                    this.state.tournDetails.is_score_predict == 0 && 
                  <Col xs="4" className={`draw`}>
                    <span className='pick-perc-value'>
                      <CircularProgressbar value={this.PickedPercentage(
                        parseFloat(item.draw_count || 0),
                        item.total_season_count ? item.total_season_count : 100
                      )} text={`${this.PickedPercentage(
                        parseFloat(item.draw_count || 0),
                        item.total_season_count ? item.total_season_count : 100
                      )}%`} />
                    </span>
                    <div>
                      <div className="right-approval">
                        <p>{drawJoined}</p>
                      </div>
                      <img src={Images.BLOCK} width="60" height="60" />
                      <p className='draw-home'>draw</p>
                    </div>
                  </Col>
                  } */}
                  <Col className={`away`} xs={this.state.tournDetails.is_score_predict == 1 ? '6' : '6'}>
                    <span className='pick-perc-value'>
                      <CircularProgressbar value={this.PickedPercentage(
                        parseFloat(item.away_count || 0),
                        item.total_season_count ? item.total_season_count : 100
                      )} text={`${this.PickedPercentage(
                        parseFloat(item.away_count || 0),
                        item.total_season_count ? item.total_season_count : 100
                      )}%`} />
                    </span>
                    <div>
                      <div className="right-approval">
                        {/* <p>{awayJoined}</p> */}
                      </div>
                      <img src={item.away_flag ? NC.S3 + NC.FLAG + item.away_flag : Images.dummy_user} width="60" height="60" />
                      <p className='draw-home'>{item.away}</p>
                    </div>
                  </Col>
                </div>
              </Col>
            )
          })}
        </Row>
      </React.Fragment>
    )
  }

  renderOptionFieldCompleted = (list) => {

    return (
      <React.Fragment>
        <Row>
          {list.map((item, idx) => {
            let isWinning = item.winning_team_id ? item.winning_team_id : ''
            let ScoreData = this.getJsonScoreData(item.score_data)
            // let homeJoined = (parseInt(item.home_count) / parseInt(item.totoal_season_count)) * 100;
            // let drawJoined = (parseInt(item.draw_count) / parseInt(item.totoal_season_count)) * 100;
            // let awayJoined = (parseInt(item.away_count) / parseInt(item.totoal_season_count)) * 100;
            return (

              <Col md="4">
                <div className="live-comp-date pt-c-gray">
                  {/* <MomentDateComponent data={{ date: item.scheduled_date, format: "D MMM - hh:mm A" }} /> */}
                  {HF.getFormatedDateTime(item.scheduled_date, "D MMM - hh:mm A")}
                  {item.status == 4 && <div className="cancel-text-view">cancel</div>}
                  {item.is_selected &&
                    <div className="right-selection">
                      <img src={Images.tick} className="rght-img" />
                    </div>
                  }
                </div>
                <div className={`pickem-card-set d-flex ${this.state.tournDetails.is_score_predict == 1 ? 'option-set-completed' : 'option-set'}`}>
                  <Col xs={this.state.SelectedSport == "7" ? "6" : "4"} className={`home ${(item.winning_team_id && item.winning_team_id == item.home_id) ? 'background-home' : ''}  ${this.state.tournDetails.is_score_predict == 1 ? 'pl-0 pr-0' : ''}`}>
                    <span className='pick-perc-value'>
                      <CircularProgressbar value={this.PickedPercentage(
                        parseFloat(item.home_count || 0),
                        item.total_season_count ? item.total_season_count : 100
                      )} text={`${this.PickedPercentage(
                        parseFloat(item.home_count || 0),
                        item.total_season_count ? item.total_season_count : 100
                      )}%`} />
                    </span>
                    <div>
                      {(item.winning_team_id && item.winning_team_id == item.home_id) && <img src={Images.APPROVAL} className="approval" />}
                      <img src={item.home_flag ? NC.S3 + NC.FLAG + item.home_flag : Images.dummy_user} width="60" height="60" />
                      <p className='draw-home'>{item.home}</p>
                    </div>
                    {
                      this.state.tournDetails.is_score_predict == 1 &&
                      <p className='scoring'>Score <strong>{ScoreData ? ScoreData.home_score : ''}</strong></p>
                    }
                  </Col>
                  {this.state.SelectedSport != "7" && <Col xs="4" className={`draw ${(item.winning_team_id && item.winning_team_id == "0") ? 'background-draw' : ''}`}>
                    <span className='pick-perc-value'>
                      <CircularProgressbar value={this.PickedPercentage(
                        parseFloat(item.draw_count || 0),
                        item.total_season_count ? item.total_season_count : 100
                      )} text={`${this.PickedPercentage(
                        parseFloat(item.draw_count || 0),
                        item.total_season_count ? item.total_season_count : 100
                      )}%`} />
                    </span>
                    <div>
                      {(item.winning_team_id && item.winning_team_id == "0" && this.state.tournDetails.is_score_predict != 1) && <img src={Images.APPROVAL} className="approval" />}
                      <img src={Images.BLOCK} width="60" height="60" />
                      <p className='draw-home'>draw</p>
                    </div>
                  </Col>}
                  <Col className={`away ${(item.winning_team_id && item.winning_team_id == item.away_id) ? 'background-away' : ''} ${this.state.tournDetails.is_score_predict == 1 ? 'pl-0 pr-0' : ''}`} xs={this.state.SelectedSport == "7" ? "6" : "4"}>
                    <span className='pick-perc-value'>
                      <CircularProgressbar value={this.PickedPercentage(
                        parseFloat(item.away_count || 0),
                        item.total_season_count ? item.total_season_count : 100
                      )} text={`${this.PickedPercentage(
                        parseFloat(item.away_count || 0),
                        item.total_season_count ? item.total_season_count : 100
                      )}%`} />
                    </span>
                    <div>
                      {(item.winning_team_id && item.winning_team_id == item.away_id) && <img src={Images.APPROVAL} className="approval" />}
                      <img src={item.away_flag ? NC.S3 + NC.FLAG + item.away_flag : Images.dummy_user} width="60" height="60" />
                      <p className='draw-home'>{item.away}</p>
                    </div>
                    {
                      this.state.tournDetails.is_score_predict == 1 &&
                      <p className='scoring'>Score <strong>{ScoreData ? ScoreData.away_score : ''}</strong></p>
                    }
                  </Col>
                </div>
              </Col>
            )
          })}
        </Row>
      </React.Fragment>
    )
  }






  publishModal = (season_game_uid) => {
    this.setState({
      publishModalOpen: !this.state.publishModalOpen,
      SeasonGameUid: season_game_uid
    })
  }

  ptChildToggleTab(tab) {
    if (this.state.ptChildActiveTab !== tab) {
      this.setState({
        PT_PickemList: [],
        ptChildActiveTab: tab,
        PT_CURRENT_PAGE: 1,
      }, () => {
        this.getAllTornament()
      })
    }
  }

  redirectTornamentDtl = (pickem_id) => {
    this.props.history.push('/pickem/tournament-detail/' + pickem_id + '/' + this.state.ptChildActiveTab)
  }

  redirectTornament = () => {

  }

  PT_handlePageChange(current_page) {
    if (current_page != this.state.PT_CURRENT_PAGE) {
      this.setState({
        PT_CURRENT_PAGE: current_page
      }, this.getAllTornament);
    }
  }

  getTouramentPagination = () => {
    let { PT_CURRENT_PAGE, PERPAGE, PT_TotalPickem } = this.state
    return (
      PT_TotalPickem > PERPAGE &&
      <Row className="mb-20">
        <Col md={12}>
          <div className="custom-pagination float-right">
            <Pagination
              activePage={PT_CURRENT_PAGE}
              itemsCountPerPage={PERPAGE}
              totalItemsCount={PT_TotalPickem}
              pageRangeDisplayed={5}
              onChange={e => this.PT_handlePageChange(e)}
            />
          </div>
        </Col>
      </Row>
    )
  }

  getAllTornament = () => {
    let { SelectedLeague, ptChildActiveTab, SelectedSport, PT_CURRENT_PAGE, PERPAGE } = this.state
    this.setState({ PT_Posting: true })
    let params = {
      sports_id: SelectedSport,
      league_id: SelectedLeague,
      match_type: ptChildActiveTab,
      items_perpage: PERPAGE,
      current_page: PT_CURRENT_PAGE,
      sort_field: "season_scheduled_date",
      sort_order: "ASC"
    }

    PT_getAllTournament(params).then(Response => {
      if (Response.response_code == NC.successCode) {
        this.setState({
          PT_PickemList: (Response.data.result) ? Response.data.result : [],
          PT_TotalPickem: (Response.data.total) ? Response.data.total : 0,
          PT_Posting: false,
        })
        // HF.scrollView('scrolldiv')
      } else {
        notify.show(NC.SYSTEM_ERROR, 'error', 5000)
      }
    }).catch(error => {
      notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    })
  }

  renderPrizeModal = () => {
    let { templateObj, prize_modal, MerchandiseList } = this.state
    return (
      <div className="winners-modal-container">
        <Modal isOpen={prize_modal} toggle={() => this.closePrizeModel()} className="winning-modal">
          <ModalHeader>Winnings Distribution</ModalHeader>
          <ModalBody>
            <div className="distribution-container">
              {
                templateObj.prize_detail &&
                <table>
                  <thead>
                    <tr>
                      <th>Rank</th>
                      <th style={{ width: "100px", textAlign: "center" }}>Min</th>
                      <th style={{ width: "100px", textAlign: "center" }}>Max</th>
                    </tr>
                  </thead>
                  <tbody>
                    {templateObj.prize_detail.map((prize, idx) => (
                      <tr key={idx}>
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
                            HF.getMerchandiseName(MerchandiseList, prize.amount)
                          }
                          {
                            prize.prize_type != '3' &&
                            HF.getNumberWithCommas(prize.amount)
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
                            HF.getMerchandiseName(MerchandiseList, prize.amount)
                          }
                          {
                            prize.prize_type != '3' &&
                            HF.getNumberWithCommas(prize.amount)
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
    )
  }

  viewWinners = (e, templateObj) => {
    e.stopPropagation();
    this.setState({ 'prize_modal': true, 'templateObj': templateObj });
  }

  closePrizeModel = () => {
    this.setState({ 'prize_modal': false, 'templateObj': {} });
  }

  cnclTournament = () => {
    // const {cnclTournament} = this.state
    this.setState({
      visibleCnclModal: true
    })
  }




  // cancelTournamentFunc = () => {
  //   let param = {
  //     tournament_id: (this.props && this.props.history.location.state) ? this.props.history.location.state.tourID : '',
  //     cancel_reason: this.state.cancel_reason
  //   }
  //   cancelTournament(param).then(Response => {
  //     if (Response.response_code == NC.successCode) {
  //       notify.show(Response.message)
  //     }
  //     else {
  //       notify.show(NC.SYSTEM_ERROR, 'error', 5000)
  //     }
  //   }).catch(error => {
  //     notify.show(NC.SYSTEM_ERROR, 'error', 5000)

  //   })
  // }


  closeCancelTour = () => {
    this.setState({
      visibleCnclModal: false
    })
  }

  closePinModal = () => {
    this.setState({
      fxPinModalOpen: false
    })
  }


  markAsCompletedPop = () => {
    this.setState({
      markComplete: true
    })
  }

  goToTourList = () => {
    let tourStatus = (this.state.tournDetails.status == 3 || this.state.tournDetails.status == 2) ? '3' : (
      this.getFormatedDateTime(Date.now(), 'YYYY-MM-D HH:mm ')) > this.getFormatedDateTime(WSManager.getUtcToLocal(this.state.tournDetails.start_date), 'YYYY-MM-D HH:mm '
      ) ? '1' : '2'
    this.props.history.push({
      pathname: '/pickem/picks',
      state: {
        activePicktab: tourStatus
      }
    })
  }

  PickedPercentage = (picked, total) => {
    let pickedPer = picked == 0 ? 0 : ((picked / total) * 100).toFixed(2);
    let checkpickedPer = (pickedPer % 1) == 0 ? Math.floor(pickedPer) : pickedPer;
    pickedPer = Math.round(checkpickedPer);
    return pickedPer;
  }
  closePickemSubmitModal = () => {
    this.setState({
      submitPickemConfirmPop: false, liveList: []
    }, () => {
      this.pickemDetail()
    })
  }

  closePickemMarkCompletedModal = () => {
    this.setState({
      markComplete: false
    })
  }


  render() {
    let { PICKEM_ITEM, submitPickemConfirmPop, fxPinModalOpen, isPin, liveList, markComplete, completeFixtures, tournMatches, l_id, DeleteModalOpen, visibleCnclModal, tournDetails, DeletePosting, ResultModalOpen, ResultPosting, PERPAGE, PARTI_CURRENT_PAGE_ST, ParticipantsList, TotalParticipants, PartiListPosting, usersModalOpen, publishPosting, LeagueOptions, SelectedLeague, activeTab, publishModalOpen, ptActiveTab, ptChildActiveTab, PT_PickemList, PT_Posting, PT_TotalPickem, prize_modal, PT_usersModalOpen, PT_PICKEM_ITEM, PT_ParticipantsList, PT_PartiListPosting, PT_PARTI_CURRENT_PAGE_ST, PT_TotalParticipants, PT_ldrbrdModalOpen, PT_ldrbrdList, PT_LdrBrdPosting, PT_LDRBRD_CURRENT_PAGE, PT_TotalLdrbrd, MODAL_PERPAGE, count, upList, deleteMatchConfirm, deleteItemData } = this.state
    let isCancelTour = (this.props && this.props.history.location.state) ? this.props.history.location.state.isCancelTour : '';
    let prompModalProps = {
      publishModalOpen: publishModalOpen,
      publishPosting: publishPosting,
      modalActionNo: this.publishModal,
      modalActionYes: this.publishMatch,
      MainMessage: PKM_PUBLISH_MSG,
      SubMessage: PKM_PUBLISH_SUB_MSG,
    }

    let ConfirmModalProps = {
      publishModalOpen: ResultModalOpen,
      publishPosting: ResultPosting,
      modalActionNo: this.DeclareResultToggle,
      modalActionYes: this.updatePickemResult,
      MainMessage: PKM_CONFIRM_MSG,
      SubMessage: PKM_PUBLISH_SUB_MSG,
    }

    let DeleteModalProps = {
      publishModalOpen: DeleteModalOpen,
      publishPosting: DeletePosting,
      modalActionNo: this.deleteToggle,
      modalActionYes: this.deletePickemItem,
      MainMessage: PKM_DELETE_MSG,
      SubMessage: PKM_PUBLISH_SUB_MSG,
    }

    let usersProps = {
      usersModalOpen: usersModalOpen,
      closeUserListModal: this.usersModal,
      PickItem: PICKEM_ITEM,
      ParticipantsList: ParticipantsList,
      PartiListPosting: PartiListPosting,
      PARTI_CURRENT_PAGE: PARTI_CURRENT_PAGE_ST,
      PERPAGE: MODAL_PERPAGE,
      TotalParticipants: TotalParticipants,
      handleUsersPageChange: this.handleUsersPageChange,
      activeTab: activeTab
    }

    let PT_usersProps = {
      usersModalOpen: PT_usersModalOpen,
      closeUserListModal: this.PT_usersModal,
      PickItem: PT_PICKEM_ITEM,
      ParticipantsList: PT_ParticipantsList,
      PartiListPosting: PT_PartiListPosting,
      PARTI_CURRENT_PAGE: PT_PARTI_CURRENT_PAGE_ST,
      PERPAGE: MODAL_PERPAGE,
      TotalParticipants: PT_TotalParticipants,
      handleUsersPageChange: this.PT_handleUsersPageChange,
      activeTab: activeTab
    }

    let PT_ldrbrdProps = {
      usersModalOpen: PT_ldrbrdModalOpen,
      closeUserListModal: this.PT_ldrbrdModal,
      PickItem: PT_PICKEM_ITEM,
      ParticipantsList: PT_ldrbrdList,
      PartiListPosting: PT_LdrBrdPosting,
      PARTI_CURRENT_PAGE: PT_LDRBRD_CURRENT_PAGE,
      PERPAGE: MODAL_PERPAGE,
      TotalParticipants: PT_TotalLdrbrd,
      handleUsersPageChange: this.PT_handleLdrBrdPageChange,
      activeTab: activeTab,
    }

    return (
      <Fragment>
        {fxPinModalOpen && <PinFxModal fxPinModalOpen={fxPinModalOpen} tournID={l_id} pickemList={this.pickemDetail} closePinModal={this.closePinModal} isPin={isPin} />}
        {visibleCnclModal
          &&
          <CancelTournamentModal
            visibleCnclModal={visibleCnclModal}
            tournID={this.state.tourna_id}
            closeCancelTour={this.closeCancelTour}
            pickemList={this.getAllPickem}
            {...this.props}
          />
        }
        {submitPickemConfirmPop &&
          <PickemSubmitConfirmationPopup liveList={liveList} submitPickemConfirmPop={submitPickemConfirmPop} closePickemSubmitModal={this.closePickemSubmitModal} pickemList={this.pickemDetail} tournDetails={tournDetails} />
        }
        {deleteMatchConfirm &&
          <ConfrimDeleteMatch upList={upList} deleteMatchConfirm={deleteMatchConfirm} deleteMatchConfirmClose={this.deleteMatchConfirmClose} deleteMatchFitures={this.deleteMatchFitures} />
        }
        {
          markComplete &&
          <PickemMarkCompletedModal markComplete={markComplete} tourna_id={this.state.tourna_id} closePickemMarkCompletedModal={this.closePickemMarkCompletedModal} />
        }


        <div className="view-picks pickem-contest" id="scrolldiv">
          <div className="text-right pb-3 cursor-pointer" onClick={() => this.goToTourList()}> {'< '}Back</div>
          <Row>
            <Col md={4}>
              <h3>Contest</h3>
            </Col>
            <Col md={8}>

              <div>
                <ul className="pickem-filter-list">
                  {(activeTab != 3 && isCancelTour != '1') && <li className="pickem-filter-item">
                    <Button className="btn-secondary-outline" onClick={() => this.cnclTournament()}>Cancel Tournament</Button>
                  </li>}
                  {activeTab == 3 && tournDetails.status != 3 && <li className="pickem-filter-item">
                    <Button
                      className="btn-secondary-outline"
                      onClick={() => this.markAsCompletedPop()}
                    // disabled={tournMatches.length == completeFixtures.length}
                    >Mark as Complete</Button>
                  </li>}
                </ul>
              </div>
            </Col>
          </Row>
          <div>
            {this.renderCommonView(true)}
          </div>
          <Row className="user-navigation mt-4">
            {/* <Col md={12}> */}
            <div className="w-100">
              <TabContent>
                <TabPane className="p-0">
                  <Nav tabs>
                    {
                      tournDetails.status != 3 &&
                      <>
                        <NavItem className={activeTab === '1' ? "active" : ""}
                          onClick={() => { this.toggle('1'); }}>
                          <NavLink>
                            Live
                          </NavLink>
                        </NavItem>
                        {

                          <NavItem className={activeTab === '2' ? "active" : ""}
                            onClick={() => { this.toggle('2'); }}>
                            <NavLink>
                              Upcoming
                            </NavLink>
                          </NavItem>
                        }
                      </>
                    }

                    <NavItem className={activeTab === '3' ? "active" : ""}
                      onClick={() => { this.toggle('3'); }}>
                      <NavLink>
                        Completed
                      </NavLink>
                    </NavItem>
                  </Nav>
                  <TabContent activeTab={activeTab}>
                    <TabPane tabId="1">
                      {this.renderOptionFieldLive()}
                    </TabPane>
                    {
                      (activeTab == '2') &&
                      <TabPane tabId="2">
                        <Fragment>
                          {this.renderOptionFieldUpcoming(this.state.upList)}
                        </Fragment>
                      </TabPane>
                    }
                    {
                      activeTab == '3' &&
                      <TabPane tabId="3">
                        {this.renderOptionFieldCompleted(this.state.comList)}
                      </TabPane>
                    }
                  </TabContent>
                </TabPane>
              </TabContent>
            </div>
            {/* </Col> */}
          </Row>
        </div>
      </Fragment >
    );
  }
}

export default PickemContest;
