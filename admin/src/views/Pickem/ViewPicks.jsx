import React, { Component, Fragment } from 'react';
import { Row, Col, Button, TabContent, TabPane, Nav, NavItem, NavLink, UncontrolledDropdown, DropdownToggle, DropdownMenu, DropdownItem, Modal, ModalBody, ModalHeader, ModalFooter } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import _ from 'lodash';
import Images from "../../components/images";
import { getPickemParticipants, publishMatchPickem, getUnpubMatches, getPickem, getLeagues, pickemResult, deletePickem, PT_getAllTournament, PT_getTournamentParticipants, PT_getTournamentLeaderboard, getPickemTournamentList, pickemGetAllParticipantsList, getPickemAllLeagues, getPickemFixtureList, getPickemGetTournamentFixtures } from '../../helper/WSCalling';
import { notify } from 'react-notify-toast';
import LS from 'local-storage';
import moment from 'moment';
import Pagination from "react-js-pagination";
import Loader from '../../components/Loader';
import { CircularProgressbar } from 'react-circular-progressbar';
import 'react-circular-progressbar/dist/styles.css';
import PromptModal from '../../components/Modals/PromptModal';
import ParticipantsModal from '../../components/Modals/ParticipantsModal';
import WSManager from '../../helper/WSManager';
import Countdown from 'react-countdown-now';
import HF, { _times, _Map, _isUndefined } from "../../helper/HelperFunction";
import Select from 'react-select';
import { MomentDateComponent } from "../../components/CustomComponent";
import { PKM_PUBLISH_MSG, PKM_CONFIRM_MSG, PKM_PUBLISH_SUB_MSG, PKM_DELETE_MSG } from "../../helper/Message";
import PTCard from './PTCard';
import queryString from 'query-string';
import PT_ParticipantsModal from '../../components/Modals/PT_ParticipantsModal';
import PT_LeaderBoardModal from '../../components/Modals/PT_LeaderBoardModal';
import ViewParticipants from './ViewParticipants'
import ViewFixtureModal from './ViewFixtureModal';
import PickemTourFixtureModal from './PickemTourFixtureModal';
import CancelTournamentModal from './CancelTournamentModal';
import PinFxModal from './PinFxModal';


const saveResult = []
class ViewPicks extends Component {
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
      participantsListPickem: [],
      viewparticipantModal: false,
      openViewModal: false,
      viewModalData: [],
      editFixture: false,
      l_id: '',
      visibleCnclModal: false,
      canclTourId: '',
      fxPinModalOpen: false,
      participantDetail: '',
      // fixture_match_count: ''
      isPin: ''
    };
  }

  componentDidMount() {
    if (this.props && this.props.location && this.props.location.state) {
      this.setState({
        activeTab: this.props.location.state.activePicktab ? this.props.location.state.activePicktab : '2'
      }, () => {
        // console.log('first activeTab',this.state.activeTab)
      })
    }
    let { SelectedSport } = this.state
    let spNm = HF.getSportsData() ? HF.getSportsData() : []

    if (!_.isEmpty(spNm)) {
      var getSportName = spNm.filter(function (item) {
        return item.value === SelectedSport ? true : false;
      });
      let sName = 'cricket'
      if (!_.isEmpty(getSportName)) {
        sName = getSportName[0].label
        this.setState({ sportName: sName })
      } else {
        notify.show("Please select sport", 'error', 5000)
      }
    }


    //Check active tab
    let values = queryString.parse(this.props.location.search)
    this.setState({
      // ptActiveTab: !_isUndefined(values.pctab) ? '2' : '1',
      ptChildActiveTab: !_isUndefined(values.pctab) ? values.pctab : '1',
    }, () => {
      this.getAllLeagues()
      this.getMerchandiseList()
      // if (this.state.ptActiveTab == '1') {
      //   this.getAllPickem()
      //   this.getUnpublishedMatches()
      // }
      // if (this.state.ptActiveTab == '2') {
      //   this.getAllTornament()
      // }
    })
    setTimeout(() => {

      this.pickemList();
    }, 10);
  }


  pickemList = () => {
    getPickemTournamentList({
      sports_id: this.state.SelectedSport,
      status: this.state.activeTab == '1' ? 'live' : this.state.activeTab == '2' ? 'upcoming' : 'completed',
      league_id: this.state.SelectLeague,
      limit: this.state.PERPAGE,
      page: this.state.CURRENT_PAGE,
    }).then(Response => {
      if (Response.response_code == NC.successCode) {
        this.setState({
          TotalPickemTournament: Response.data.result,
          TotalPickem: Response.data.total,
        })
      } else {
        notify.show(NC.SYSTEM_ERROR, 'error', 5000)
      }
    }).catch(error => {
      notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    })
  }

  getMerchandiseList = () => {
    let { PERPAGE, CURRENT_PAGE } = this.state
    let params = {
      sort_field: "added_date",
      sort_order: "DESC",
      items_perpage: PERPAGE,
      current_page: CURRENT_PAGE,
    }
    // WSManager.Rest(NC.baseURL + NC.PT_GET_MERCHANDISE_LIST, params).then(Response => {
    //   if (Response.response_code == NC.successCode) {
    //     this.setState({
    //       MerchandiseList: Response.data.merchandise_list
    //     })
    //   }
    // }).catch(error => {
    //   notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    // })
  }

  toggle(tab) {
    if (this.state.activeTab !== tab) {
      this.setState({
        activeTab: tab,
        CURRENT_PAGE: 1,
        UNPUB_CURRENT_PAGE: 1,
      }, () => {
        this.getAllPickem()
        // if (this.state.activeTab == '2') {
        //   this.getUnpublishedMatches()
        // }
      })
    }
  }

  getAllPickem = () => {
    let { SelectedLeague, activeTab, SelectedSport, SelectLeague, CURRENT_PAGE, PERPAGE, StartDate, EndDate } = this.state
    this.setState({ Posting: true })
    let params = {
      sports_id: SelectedSport,
      status: activeTab == 1 ? "live" : activeTab == 2 ? "upcoming" : "completed",
      league_id: SelectLeague,
      limit: this.state.PERPAGE,
      page: this.state.CURRENT_PAGE,
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
          TotalPickem: Response.data.total,
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
    getPickemAllLeagues(params).then(Response => {
      if (Response.response_code == NC.successCode) {
        this.setState({
          LeagueList: Response.data,
        }, () => {
          _.map(this.state.LeagueList, (item, idx) => {
            obj = { value: item.league_id, label: item.league_name }
            leagueOptions.push(obj)
          })
          this.setState({
            LeagueOptions: leagueOptions
          })
        })
      } else {
        notify.show(NC.SYSTEM_ERROR, 'error', 5000)
      }
    }).catch(error => {
      notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    })
  }

  handleTypeChange = (value) => {
    let { activeTab, ptActiveTab } = this.state
    if (value != null) {
      this.setState({ SelectedLeague: value.value }, function () {

        this.pickemList();
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

  redirectToContest = (item, event, pin) => {
    event.stopPropagation();
    this.props.history.push({
      pathname: '/pickem/view-contest/' + item.tournament_id + '/' + item.user_count, state: {
        tourID: item.tournament_id,
        pin: pin,
        user_count: item.user_count,
        isCancelTour: item.status,
        preActiveTab: this.state.activeTab
      }
    })
  }

  getAllParticipantList = (item, e) => {
    this.setState({
      participantDetail: item
      // fixture_match_count: item.match_count
    })
    e.stopPropagation();
    let param = {
      tournament_id: item.tournament_id,
    }
    pickemGetAllParticipantsList(param).then(Response => {
      if (Response.response_code == NC.successCode) {
        this.setState({
          participantsListPickem: Response.data.result,
          viewparticipantModal: true
        })
      }
      else {
        notify.show(NC.SYSTEM_ERROR, 'error', 5000)
      }
    }).catch(error => {
      notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    })
  }

  openViewModalReq = (l_id, e) => {
    e.stopPropagation();

    let param = {
      league_id: l_id
    }
    getPickemFixtureList(param).then(Response => {
      if (Response.response_code == NC.successCode) {
        this.setState({
          viewModalData: Response.data,
          openViewModal: true
        })
        // this.getAllPickem();
      }
      else {
        notify.show(NC.SYSTEM_ERROR, 'error', 5000)
      }
    }).catch(error => {
      notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    })
  }

  editFixtureTour = (l_id, e) => {
    e.stopPropagation();
    this.setState({
      editFixture: true,
      l_id: l_id
    })
  }

  cancelTournamentFunc = (id, e) => {
    e.stopPropagation();
    this.setState({
      visibleCnclModal: true,
      canclTourId: id
    })
  }

  closeViewModalReq = () => {
    this.setState({
      openViewModal: false
    })
  }
  S
  closeParticipantsModal = () => {
    this.setState({
      viewparticipantModal: false
    })
  }

  openDetailingPage = (event, item) => {
    event.stopPropagation();
    this.props.history.push({
      pathname: '/pickem/pickem-detail/' + item.tournament_id,
      state: { tournament_id: item.tournament_id, activeTab: this.state.activeTab, isFromFixture: true }
    })
  }

  pTPinModal = (item, e) => {
    // console.log(item)
    e.stopPropagation();

    this.setState({
      fxPinModalOpen: true,
      l_id: item.tournament_id,
      isPin: item.is_pin
    })
  }



  renderCricketCard = (flag, tab) => {
    let { TotalPickemTournament, activeTab } = this.state
    return (
      _.map(TotalPickemTournament, (item, idx) => {

        return (
          // <Col md={4} key={idx}>
          <div className="cricket-fixture-card mr-3" key={idx}>
            {/* {
                activeTab == '1' ?
                  <div className="live-comp-date">
                    {
                      item.feed_type == '0' ?
                        'score'
                        :
                        <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM - hh:mm A" }} />
                    }
                  </div>
                  :
                  (HF.showCountDown(item.season_scheduled_date) && !item.onCompTimer) ?
                    <div className="pickem-matchtype">
                      <Countdown
                        daysInHours={true}
                        onComplete={() => this.onComplete(idx)}
                        date={WSManager.getUtcToLocal(item.season_scheduled_date)}
                      />
                    </div>
                    :
                    <div className="live-comp-date">
                      <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM - hh:mm A" }} />
                    </div>
              } */}


            <div className="pickem-card-set"
              // onClick={() => this.openDetailingPage(item)}
              onClick={(event) => this.redirectToContest(item, event, item.is_pin)}
            >
              <div className='pin-wrap'>
                {tab != '3' && <div className='pin-modal-left'>
                  {
                    item.is_pin == '1' &&
                    <img onClick={(e) => this.pTPinModal(item, e)} src={Images.PIN_ACTIVE} alt="" className="pinned-active" />
                  }
                  {
                    item.is_pin == '0' &&
                    <i onClick={(e) => this.pTPinModal(item, e)} className="icon-pinned ml-2"></i>
                  }
                </div>}
                <div className='icon-section'>
                  <img src={Images.VIEW_ICON} className="cursor-point" onClick={(e) => this.openDetailingPage(e, item)} />
                  {(activeTab != '3' && item.status != "1") && <img src={Images.EDIT_ICON} className="cursor-point" onClick={(e) => this.editFixtureTour(item.tournament_id, e)} />}
                  {(item.status != "1" && activeTab != '3')
                    &&
                    <img src={Images.CANCEL} className="cursor-point" onClick={(e) => this.cancelTournamentFunc(item.tournament_id, e)} width="10" />
                  }
                  {item.status == "1" &&
                    <span className='cancel-text'>Cancelled</span>
                  }
                </div>
              </div>
              <Row className='logo-details'>
                <div className='image-block'>
                  {item.image != '' ? <img src={item.image} alt="" /> : <img src={Images.TROPHY} alt="" />}
                </div>
                <div className='image-detailing'>
                  <a href className='tour-name'>{item.name}</a>
                  <p className='tour-date'>
                    {/* <MomentDateComponent data={{ date: item.start_date, format: "D MMM - " }} />
                    <MomentDateComponent data={{ date: item.end_date, format: "D MMM" }} /> */}

                    {HF.getFormatedDateTime(item.start_date, "D MMM - ")}
                    {HF.getFormatedDateTime(item.end_date, "D MMM")}
                    |
                    {item.match_count}
                    {item.match_count > 1 ? 'Fixtures' : 'Fixture'} | {Math.round(item.max_bonus)}
                    % Bonus Allowed
                  </p>

                  <p className='count' onClick={(e) => this.getAllParticipantList(item, e)}>{item.user_count} Participant</p>
                  <button className='entry-btn'>{item.entry_fee != "0" ? item.currency_type == "1" ? <div className="icon-rupess"></div> : <img src={Images.COIN} width="15" /> : ''}<div>{item.entry_fee == "0" ? 'Free' : item.entry_fee}</div></button>
                </div>
              </Row>
              <div className='league-name-block'>
                {item.league_name}
              </div>
            </div>
          </div>
          // </Col>
        )
      })
    )
  }

  renderCommonView = (flag, tab) => {
    let { TotalPickemTournament, activeTab, UnpubPosting, Posting, CURRENT_PAGE, UNPUB_CURRENT_PAGE, PERPAGE, TotalUnpubPickem, TotalPickem, SelectedSport } = this.state
    return (
      <Fragment>
        <Row className="mt-30">
          {
            this.renderCricketCard(flag, tab)
          }
        </Row>
        {
          TotalPickem > PERPAGE && (
            <Row className="mb-20">
              <Col md={12}>
                <div className="custom-pagination float-right">
                  <Pagination
                    activePage={flag ? CURRENT_PAGE : UNPUB_CURRENT_PAGE}
                    itemsCountPerPage={PERPAGE}
                    totalItemsCount={flag ? TotalPickem : TotalUnpubPickem}
                    pageRangeDisplayed={5}
                    onChange={e => this.handlePageChange(e, flag)}
                  />
                </div>
              </Col>
            </Row>
          )
        }
      </Fragment>
    )
  }

  publishModal = (season_game_uid) => {
    this.setState({
      publishModalOpen: !this.state.publishModalOpen,
      SeasonGameUid: season_game_uid
    })
  }

  publishMatch = () => {
    let { SelectedSport, SeasonGameUid, UnpubPickemList } = this.state

    this.setState({ publishPosting: true })
    let params = {
      sports_id: SelectedSport,
      season_game_uid: SeasonGameUid
    }

    let tempUnPlist = UnpubPickemList
    publishMatchPickem(params).then(Response => {
      if (Response.response_code == NC.successCode) {
        this.publishModal()
        notify.show(Response.message, 'success', 5000)
        _.remove(tempUnPlist, (item) => {
          return item.season_game_uid == SeasonGameUid
        })
        this.setState({
          publishPosting: false,
          UnpubPickemList: tempUnPlist
        }, this.getAllPickem)
      } else {
        this.publishModal()
        this.setState({ publishPosting: false })
        notify.show(NC.SYSTEM_ERROR, 'error', 5000)
      }
    }).catch(error => {
      notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    })
  }

  handleUsersPageChange = (current_page) => {
    if (this.state.PARTI_CURRENT_PAGE_ST !== current_page) {
      this.setState({
        PARTI_CURRENT_PAGE_ST: current_page
      }, () => {
        this.getParticipantList()
      });
    }
  }

  usersModal = (item, call_from) => {
    this.setState({
      callFrom: call_from,
      PICKEM_ID: item.pickem_id ? item.pickem_id : '',
      PICKEM_ITEM: item,
      PARTI_CURRENT_PAGE_ST: 1,
      usersModalOpen: !this.state.usersModalOpen
    }, () => {
      if (this.state.usersModalOpen) {
        this.getParticipantList()
      }
      else {
        this.setState({
          ParticipantsList: [],
          TotalParticipants: 0,
        })
      }
    })
  }

  PT_handleUsersPageChange = (current_page) => {
    if (this.state.PT_PARTI_CURRENT_PAGE_ST !== current_page) {
      this.setState({
        PT_PARTI_CURRENT_PAGE_ST: current_page
      }, () => {
        this.PT_getParticipantList()
      });
    }
  }

  PT_handleLdrBrdPageChange = (current_page) => {
    if (this.state.PT_LDRBRD_CURRENT_PAGE !== current_page) {
      this.setState({
        PT_LDRBRD_CURRENT_PAGE: current_page
      }, () => {
        this.PT_getLeaderboardList()
      });
    }
  }

  PT_usersModal = (item) => {
    this.setState({
      PT_PICKEM_ID: item.pickem_id ? item.pickem_id : '',
      PT_PICKEM_ITEM: item,
      PT_PARTI_CURRENT_PAGE_ST: 1,
      PT_usersModalOpen: !this.state.PT_usersModalOpen
    }, () => {
      if (this.state.PT_usersModalOpen) {
        this.PT_getParticipantList()
      }
      else {
        this.setState({
          PT_ParticipantsList: [],
          PT_TotalParticipants: 0,
        })
      }
    })
  }

  PT_ldrbrdModal = (item) => {
    this.setState({
      PT_PICKEM_ID: item.pickem_id ? item.pickem_id : '',
      PT_PICKEM_ITEM: item,
      PT_LDRBRD_CURRENT_PAGE: 1,
      PT_ldrbrdModalOpen: !this.state.PT_ldrbrdModalOpen
    }, () => {
      if (this.state.PT_ldrbrdModalOpen) {
        this.PT_getLeaderboardList()
      }
      else {
        this.setState({
          PT_ldrbrdList: [],
          PT_TotalLdrbrd: 0,
        })
      }
    })
  }

  getParticipantList = () => {
    this.setState({ PartiListPosting: true })
    let { PARTI_CURRENT_PAGE_ST, MODAL_PERPAGE, PICKEM_ID } = this.state
    let params = {
      pickem_id: PICKEM_ID,
      items_perpage: MODAL_PERPAGE,
      current_page: PARTI_CURRENT_PAGE_ST,
    }
    getPickemParticipants(params).then(Response => {
      if (Response.response_code == NC.successCode) {
        this.setState({
          ParticipantsList: Response.data.pickem_participants,
          TotalParticipants: Response.data.total,
          PartiListPosting: false
        })
      } else {
        notify.show(NC.SYSTEM_ERROR, 'error', 5000)
      }
    }).catch(error => {
      notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    })
  }

  PT_getParticipantList = () => {
    this.setState({ PT_PartiListPosting: true })
    let { PT_PARTI_CURRENT_PAGE_ST, MODAL_PERPAGE, PT_PICKEM_ID } = this.state
    let params = {
      pickem_id: PT_PICKEM_ID,
      items_perpage: MODAL_PERPAGE,
      current_page: PT_PARTI_CURRENT_PAGE_ST,
    }
    PT_getTournamentParticipants(params).then(Response => {
      if (Response.response_code == NC.successCode) {
        this.setState({
          PT_ParticipantsList: Response.data.pickem_participants,
          PT_TotalParticipants: Response.data.total,
          PT_PartiListPosting: false
        })
      } else {
        notify.show(NC.SYSTEM_ERROR, 'error', 5000)
      }
    }).catch(error => {
      notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    })
  }

  PT_getLeaderboardList = () => {
    this.setState({ PT_LdrBrdPosting: true })
    let { PT_LDRBRD_CURRENT_PAGE, MODAL_PERPAGE, PT_PICKEM_ID } = this.state
    let params = {
      pickem_id: PT_PICKEM_ID,
      items_perpage: MODAL_PERPAGE,
      current_page: PT_LDRBRD_CURRENT_PAGE,
    }
    PT_getTournamentLeaderboard(params).then(Response => {
      if (Response.response_code == NC.successCode) {
        this.setState({
          PT_ldrbrdList: Response.data.leaderboard_data,
          PT_TotalLdrbrd: Response.data.total,
          PT_LdrBrdPosting: false
        })
      } else {
        notify.show(NC.SYSTEM_ERROR, 'error', 5000)
      }
    }).catch(error => {
      notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    })
  }

  // togglePt(tab) {
  //   if (this.state.ptActiveTab !== tab) {
  //     this.setState({
  //       ptActiveTab: tab,
  //       SelectedLeague: '',
  //       CURRENT_PAGE: 1,
  //       PT_CURRENT_PAGE: 1,
  //       UNPUB_CURRENT_PAGE: 1,
  //     }, () => {
  //       // if (this.state.ptActiveTab == '1') {
  //       //   this.getAllPickem()
  //       // }
  //       // if (this.state.ptActiveTab == '2') {
  //         this.getAllTornament()
  //       // }
  //     })
  //   }
  // }

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
  getPtCard = (list, tab_flag) => {
    return (
      _Map(list, (item, idx) => {
        return (
          <Col md={4} key={idx}>
          </Col>
        )
      })
    )
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

  hidePickemTourFixture = () => {
    this.setState({
      editFixture: false
    }, () => {

      // this.pickemList();
      this.getAllPickem();
    })
  }


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

  render() {
    let { PICKEM_ITEM, isPin, DeleteModalOpen, participantDetail, visibleCnclModal, fxPinModalOpen, viewparticipantModal, participantsListPickem, DeletePosting, ResultModalOpen, ResultPosting, PERPAGE, PARTI_CURRENT_PAGE_ST, ParticipantsList, TotalParticipants, PartiListPosting, usersModalOpen, publishPosting, LeagueOptions, SelectedLeague, activeTab, publishModalOpen, ptActiveTab, ptChildActiveTab, PT_PickemList, PT_Posting, PT_TotalPickem, prize_modal, PT_usersModalOpen, PT_PICKEM_ITEM, PT_ParticipantsList, PT_PartiListPosting, PT_PARTI_CURRENT_PAGE_ST, PT_TotalParticipants, PT_ldrbrdModalOpen, PT_ldrbrdList, PT_LdrBrdPosting, PT_LDRBRD_CURRENT_PAGE, PT_TotalLdrbrd, MODAL_PERPAGE, viewModalData, openViewModal, editFixture, l_id } = this.state
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
        <div className="view-picks" id="scrolldiv">
          {fxPinModalOpen && <PinFxModal fxPinModalOpen={fxPinModalOpen} tournID={l_id} pickemList={this.pickemList} closePinModal={this.closePinModal} isPin={isPin} />}
          {DeleteModalOpen && <PromptModal {...DeleteModalProps} />}
          {publishModalOpen && <PromptModal {...prompModalProps} />}
          {ResultModalOpen && <PromptModal {...ConfirmModalProps} />}
          {usersModalOpen && <ParticipantsModal {...usersProps} />}
          {PT_usersModalOpen && <PT_ParticipantsModal {...PT_usersProps} />}
          {prize_modal && this.renderPrizeModal()}
          {PT_ldrbrdModalOpen && <PT_LeaderBoardModal {...PT_ldrbrdProps} />}
          {viewparticipantModal && <ViewParticipants viewparticipantModal={viewparticipantModal} participantDetail={participantDetail} participantsListPickem={participantsListPickem} closeParticipantsModal={this.closeParticipantsModal} />}
          {openViewModal && <ViewFixtureModal openViewModal={openViewModal} viewModalData={viewModalData} closeViewModalReq={this.closeViewModalReq} />}
          {editFixture && <PickemTourFixtureModal editFixture={editFixture} getPickemGetTournamentFixtures={this.getPickemGetTournamentFixtures} l_id={l_id} hidePickemTourFixture={this.hidePickemTourFixture} pickemList={this.getAllPickem} />}
          {visibleCnclModal && <CancelTournamentModal visibleCnclModal={visibleCnclModal} tournID={this.state.canclTourId} closeCancelTour={this.closeCancelTour} pickemList={this.pickemList} />}

          <Row>
            <Col md={4}>
              <div className="pre-sports-select float-left">
                <Select
                  isSearchable={true}
                  className="xform-control"
                  options={LeagueOptions}
                  menuIsOpen={true}
                  value={SelectedLeague}
                  onChange={e => this.handleTypeChange(e, 'SelectedLeague')}
                />
              </div>
            </Col>
            {/* <Col md={8}>
              <ul className="pickem-filter-list">
                <li className="pickem-filter-item">
                  <Button onClick={() => this.props.history.push({ pathname: '/pickem/create-pick' })} className="btn-secondary-outline">Create New Match</Button>
                </li>
                <li className="pickem-filter-item">
                  <Button onClick={() => this.props.history.push({ pathname: '/pickem/leagues' })} className="btn-secondary-outline">Create Leagues/Players</Button>
                </li>
              </ul>
            </Col> */}
          </Row>
          {/* <Row>
            <Col md={12}>
              <div className="fixtures-title">Fixtures</div>
            </Col>
          </Row> */}
          {/* <div className="user-navigation">
            <Col md={12}>
              <div className="w-100">
                <Nav tabs>
                  <NavItem className={ptActiveTab === '1' ? "active" : ""}
                    onClick={() => { this.togglePt('1'); }}>
                    <NavLink>
                      Fixtures
                  </NavLink>
                  </NavItem>
                  {

                    <NavItem className={ptActiveTab === '2' ? "active" : ""}
                      onClick={() => { this.togglePt('2'); }}>
                      <NavLink>
                        Pick’em Tournament
                    </NavLink>
                    </NavItem>
                  }
                </Nav>
              </div>
              
            </Col>
          </div> */}
          <h3 className='pt-3 mt-3 pb-3'>Pick'em Tournament</h3>
          <Row className="user-navigation">
            <Col md={12}>
              <div className="w-100">
                <TabContent>
                  <TabPane className="p-0">
                    <Nav tabs>
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

                      <NavItem className={activeTab === '3' ? "active" : ""}
                        onClick={() => { this.toggle('3'); }}>
                        <NavLink>
                          Completed
                        </NavLink>
                      </NavItem>
                    </Nav>
                    <TabContent activeTab={activeTab}>
                      <TabPane tabId="1">
                        {this.renderCommonView(true, '1')}
                      </TabPane>
                      {
                        (activeTab == '2') &&
                        <TabPane tabId="2">
                          <Fragment>
                            {this.renderCommonView(true, '2')}
                            {/* <Row>
                              <Col md={12}>
                                <div className="pre-border-bottom">
                                  <div className="fixture-type">Unpublished Games</div>
                                </div>
                              </Col>
                            </Row>
                            {this.renderCommonView(false)} */}
                          </Fragment>
                        </TabPane>
                      }
                      {
                        activeTab == '3' &&
                        <TabPane tabId="3">
                          {this.renderCommonView(true, '3')}
                        </TabPane>
                      }
                    </TabContent>
                  </TabPane>
                  <TabPane className="p-0" tabId="2">
                    {/* <Nav tabs>
                      <NavItem className={ptChildActiveTab === '1' ? "active" : ""}
                        onClick={() => { this.ptChildToggleTab('1'); }}>
                        <NavLink>
                          <label className="live">Live</label>
                        </NavLink>
                      </NavItem>
                      {

                        <NavItem className={ptChildActiveTab === '2' ? "active" : ""}
                          onClick={() => { this.ptChildToggleTab('2'); }}>
                          <NavLink>
                            <label className="live">Upcoming</label>
                          </NavLink>
                        </NavItem>
                      }

                      <NavItem className={ptChildActiveTab === '3' ? "active" : ""}
                        onClick={() => { this.ptChildToggleTab('3'); }}>
                        <NavLink>
                          <label className="live">Completed</label>
                        </NavLink>
                      </NavItem>
                    </Nav> */}
                    <TabContent activeTab={ptChildActiveTab}>
                      {
                        (ptChildActiveTab == '1' && PT_TotalPickem != 0) &&
                        <TabPane tabId="1">
                          <Row className="mt-30">
                            {this.getPtCard(PT_PickemList, ptChildActiveTab)}
                          </Row>
                        </TabPane>
                      }
                      {
                        (ptChildActiveTab == '2' && PT_TotalPickem != 0) &&
                        <TabPane tabId="2">
                          <Row className="mt-30">
                            {this.getPtCard(PT_PickemList, ptChildActiveTab)}
                          </Row>
                        </TabPane>
                      }
                      {
                        (ptChildActiveTab == '3' && PT_TotalPickem != 0) &&
                        <TabPane tabId="3">
                          <Row className="mt-30">
                            {this.getPtCard(PT_PickemList, ptChildActiveTab)}
                          </Row>
                        </TabPane>
                      }
                      <Row>
                        <Col md={12}>
                          {(PT_TotalPickem == 0 && !PT_Posting) &&
                            <div className="no-records mt-30">{NC.NO_RECORDS}</div>
                          }
                          {
                            PT_TotalPickem != 0 && PT_Posting &&
                            <Loader />
                          }
                        </Col>
                      </Row>
                      {this.getTouramentPagination()}
                    </TabContent>
                  </TabPane>
                </TabContent>
              </div>
            </Col>
          </Row>
        </div>
      </Fragment>
    );
  }
}

export default ViewPicks;
