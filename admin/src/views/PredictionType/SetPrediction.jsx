import React, { Component, Fragment } from "react";
import Images from '../../components/images';
import Select from 'react-select';
import { Row, Col, TabContent, TabPane, Nav, NavItem, Button, NavLink, Input, UncontrolledDropdown, DropdownToggle, DropdownMenu, DropdownItem, Modal, ModalBody, ModalHeader, ModalFooter, Table } from 'reactstrap';
import _ from 'lodash';
import Pagination from "react-js-pagination";
import Loader from '../../components/Loader';
import WSManager from '../../helper/WSManager';
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import Moment from 'react-moment';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";

import ReadMoreAndLess from 'react-read-more-less';
import ScrollMenu from 'react-horizontal-scrolling-menu';
import ActionRequestModal from '../../components/ActionRequestModal/ActionRequestModal';
import Countdown from 'react-countdown-now';
import LS from 'local-storage';
import HelperFunction from "../../helper/HelperFunction";
import { MSG_DELETE_PREDICTION, MSG_SUBMIT_PREDICTION, MSG_SUBMIT_PREDICTION_SUB, PREDICTION_PAST_TIME_ERR } from "../../helper/Message";
import HF from '../../helper/HelperFunction';

// One item component
// selected prop will be passed
const MenuItem = ({ item, selected }) => {
    return (
        <div className="pre-fixture">
            <div className="fixture-card">
                <img src={NC.S3 + NC.FLAG + item.home_flag} alt="" className="team-img float-left" />
                <img src={NC.S3 + NC.FLAG + item.away_flag} alt="" className="team-img float-right" />
                <div className="fixture-container">
                    <div className="fixture-name">{item.home} vs {item.away}</div>
                    <div className="fixture-time">
                        {/* {WSManager.getUtcToLocalFormat(item.scheduled_date_time, 'D-MMM-YYYY hh:mm A')} */}
                        {HF.getFormatedDateTime(item.season_scheduled_date, 'D-MMM-YYYY hh:mm A')}
                    </div>
                    <div className="fixture-title">{item.subtitle ? item.subtitle : '--'}</div>
                </div>
            </div>
        </div>
    )
};

// All items component
// Important! add unique key
export const Menu = (list, selected) =>
    list.map(el => {
        return <MenuItem item={el} key={el.season_game_uid} selected={selected} />;
    });

class SetPrediction extends Component {
    constructor(props) {
        super(props)
        this.state = {
            activeTab: '1',
            ListPosting: false,
            PartiListPosting: false,
            PublishPosting: false,
            Options1Msg: true,
            Options2Msg: true,
            QuestionMsg: true,
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            LIST_CURRENT_PAGE: 1,
            COM_CURRENT_PAGE: 1,
            PredictionList: [],
            SEASON_GAME_ID: this.props.match.params.seasongameid,
            HistoryModalOpen: false,
            submitAnswerOpen: false,
            preQuestion: '',
            activeAnswer: 0,
            ActionPopupOpen: false,
            FixtureList: [],
            ExpireOnMsg: false,
            EditPredictionMasterId: '',
            YesPosting: false,
            BackTo: this.props.match.params.fixturetype,
            selFeed: 0,
            feedFilter: {value: '', label: 'All' },
            sports_id: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
            singleClick: false, 
            isEdit: false,
            showBTError: false
        }
    }

    onSelect = key => {
        this.setState({ SEASON_GAME_ID: key }, this.getPredictionList);
    }

    componentDidMount() {
        let Fixture = LS.get('selected_fixture')
        this.setState({ SelectedFixture: Fixture }, () => {
            this.getPredictionList()
            if (this.props.match.params.fixturetype) {
                this.getLiveFixtures()
            }
        })
    }

    getLiveFixtures = () => {
        let { SelectedFixture } = this.state
        let params = {
            sports_id: this.props.match.params.sportsid,
            items_perpage: 1000,
            current_page: 1,
            match_type: this.props.match.params ? this.props.match.params.fixturetype : 1//live 

        }
        let fixture = SelectedFixture
        WSManager.Rest(NC.baseURL + NC.GET_SEASON_LIST, params).then(Response => {
            if (Response.response_code == NC.successCode) {

                let tempResArr = Response.data.fixtures.result

                _.remove(tempResArr, function (item, idx) {
                    return fixture.season_id == item.season_id
                })

                tempResArr.unshift(fixture);
                this.setState({
                    FixtureList: tempResArr,
                    season_id: Response.data.fixtures.result[0].season_id,
                }, () => {
                    this.menuItems = Menu(this.state.FixtureList, this.state.season_game_uid);
                    this.resetChanges()
                })

            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    getPredictionList = () => {
        this.setState({ ListPosting: true })
        let { PERPAGE, CURRENT_PAGE, SEASON_GAME_ID, activeTab, COM_CURRENT_PAGE, feedFilter  } = this.state
        let params = {
            season_game_uid: SEASON_GAME_ID,
            items_perpage: PERPAGE,
            current_page: activeTab == "2" ? COM_CURRENT_PAGE : CURRENT_PAGE,
            status: activeTab == "2" ? activeTab : "0",
            is_prediction_feed : feedFilter.value,
        }
        WSManager.Rest(NC.baseURL + NC.GET_ALL_PREDICTION, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    PredictionList: Response.data.predictions.result,
                    Total: Response.data.predictions.total,
                    ListPosting: false
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    toggle(tab) {
        this.setState({ activeTab: tab, CURRENT_PAGE: 1, COM_CURRENT_PAGE: 1 }, () => {
            this.getPredictionList()
        })
    }

    handlePageChange(current_page) {
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            this.getPredictionList()
        });
    }

    handleParticipantsPageChange(current_page) {
        this.setState({
            LIST_CURRENT_PAGE: current_page
        }, () => {
            this.getPredictionUserList()
        });
    }

    handleCompletedChange(current_page) {
        this.setState({
            COM_CURRENT_PAGE: current_page
        }, () => {
            this.getPredictionList()
        });
    }
    diff_minutes(compareDate) {
        let today = new Date();
        let Christmas = new Date(compareDate);
        let diffMs = (Christmas - today);
        let diffDays = Math.floor(diffMs / 86400000); // days
        let diffHrs = Math.floor((diffMs % 86400000) / 3600000); // hours
        let diffMins = Math.round(((diffMs % 86400000) % 3600000) / 60000);//Min 


        return diffMins
    }

    createPrediction = () => {
        this.setState({ PublishPosting: true, ExpireOnMsg: false, singleClick: true })
        let { EditPredictionMasterId, Question, option1, option2, option3, option4, ExpireOn, SEASON_GAME_ID } = this.state
        if (!ExpireOn || this.diff_minutes(ExpireOn) < 0) {

            this.setState({
                ExpireOnMsg: true,
                PublishPosting: false
            })
            return false
        }
        let params = {
            question: Question,
            options: [
                {
                    "text": option1
                },
                {
                    "text": option2
                },
                {
                    "text": option3
                },
                {
                    "text": option4
                },
            ],
            deadline_date: ExpireOn,
            season_game_uid: SEASON_GAME_ID,
            site_rake: "0",
            sports_id: this.props.match.params.sportsid,
            is_prediction_feed: this.state.selFeed ? this.state.selFeed : '',
        }

        let CallUrl = NC.CREATE_PREDICTION
        if (EditPredictionMasterId) {
            params.prediction_master_id = EditPredictionMasterId
            CallUrl = NC.UPDATE_PREDICTION
        }

        
        WSManager.Rest(NC.baseURL + CallUrl, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, 'success', 5000)
                this.setState({
                    PublishPosting: false,
                    formValid: false,
                    Question: '',
                    option1: '',
                    Options1Msg: true,
                    option2: '',
                    Options2Msg: true,
                    option3: '',
                    option4: '',
                    ExpireOn: '',
                    ExpireOnMsg: false,
                    EditPredictionMasterId: '',
                    QuestionMsg: true,
                    singleClick: false,
                    isEdit: false,
                }, this.getPredictionList)
            } else {
                this.setState({
                    EditPredictionMasterId: '',
                    PublishPosting: false,
                    singleClick: false,
                    isEdit: false,
                    ExpireOnMsg: false,
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            this.setState({
                PublishPosting: false,
            })
        })
    }

    handleInputChange = (event) => {
        let name = event.target.name
        let value = event.target.value

        this.setState({ [name]: value },
            () => this.validateForm(name, value)
        )
    }
   
    handleFeedChange = (e) => {
        let value = e.target.value
        this.setState({ selFeed: value }
        )
    }
   
    handleSelect = (e) => {
        this.setState({ feedFilter: e,
            activeAnswer: 0},
        () =>{
            this.getPredictionList()}    )
    }

    handleDateChange = (date, dateType) => {
        this.setState({ [dateType]: date }, () => this.validateForm(dateType, date))
    }

    validateForm = (name, value) => {
        let QuestionValid = this.state.Question
        let Option1Valid = this.state.option1
        let Option2Valid = this.state.option2
        let ExpireOnValid = this.state.ExpireOnMsg
        switch (name) {
            case 'Question':
                QuestionValid = (value.trim().length > 0 && value.length <= 201) ? true : false;
                this.setState({ QuestionMsg: QuestionValid })
                break;
            case 'option1':
                Option1Valid = (value.trim().length > 0 && value.length <= 51) ? true : false;
                this.setState({ Options1Msg: Option1Valid })
                break;
            case 'option2':
                Option2Valid = (value.trim().length > 0 && value.length <= 51) ? true : false;
                this.setState({ Options2Msg: Option2Valid })
                break;
            case 'ExpireOn':
                ExpireOnValid = ((value != '' && this.diff_minutes(value) > 0)) ? false : true;
                // ExpireOnValid = ((value != '' && this.diff_minutes(value) > 0)) ? true : false;
                this.setState({ ExpireOnMsg: ExpireOnValid})
                break;
            default:
                break;
        }

        this.setState({
            
            formValid: (QuestionValid && Option1Valid && Option2Valid && !ExpireOnValid && this.state.ExpireOn != '')
        })
    }

    resetChanges = (flag) => {
        this.setState({
            formValid: false,
            Question: '',
            option1: '',
            option2: '',
            option3: '',
            option4: '',
            ExpireOn: '',
            OptionsMsg: true,
            QuestionMsg: true,
            CURRENT_PAGE: 1,
            ExpireOnMsg: false
        }, () => {
            if (flag == 1) {
                this.getPredictionList()
            }
        })
    }

    pausePlayPrediction(item, idxVal) {
        let params = {
            prediction_master_id: item.prediction_master_id,
            pause: item.status == 3 ? 0 : 1
        }
        let TempPredictionList = this.state.PredictionList
        WSManager.Rest(NC.baseURL + NC.PAUSE_PLAY_PREDICTION, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                TempPredictionList[idxVal].status = item.status == 3 ? 0 : 3
                this.setState({ PredictionList: TempPredictionList })
                notify.show(Response.message, 'success', 5000)
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    updatePinPrediction(item, idxVal) {
        let params = {
            prediction_master_id: item.prediction_master_id,
            is_pin: item.is_pin == "1" ? "0" : "1"
        }
        let TempPredictionList = this.state.PredictionList
        WSManager.Rest(NC.baseURL + NC.UPDATE_PIN_PREDICTION, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                TempPredictionList[idxVal].is_pin = item.is_pin == "1" ? "0" : "1"
                this.setState({ PredictionList: TempPredictionList })
                notify.show(Response.global_error, 'success', 5000)
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    toggleUserListModal = (prediction_master_id, pre_question) => {
        this.setState({
            HistoryModalOpen: !this.state.HistoryModalOpen,
            preQuestion: pre_question,
            predictionMasterId: prediction_master_id,
        }, () => {
            if (prediction_master_id)
                this.getPredictionUserList()
        }
        )
    }

    getPredictionUserList = () => {
        this.setState({ PartiListPosting: true })
        let { PERPAGE, LIST_CURRENT_PAGE, predictionMasterId } = this.state
        let params = {
            prediction_master_id: predictionMasterId,
            items_perpage: PERPAGE,
            current_page: LIST_CURRENT_PAGE
        }

        WSManager.Rest(NC.baseURL + NC.GET_PREDICTION_PARTICIPANTS, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    Participants: Response.data.prediction_participants,
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

    predictUserListModal() {
        let { PartiListPosting, LIST_CURRENT_PAGE, PERPAGE, Participants, TotalParticipants, preQuestion, activeTab } = this.state
        return (
            <Modal
                isOpen={this.state.HistoryModalOpen}
                className="modal-md coupon-history prediction-popup"
                toggle={() => this.toggleUserListModal('', preQuestion)}
            >
                <ModalHeader>{preQuestion ? preQuestion : '--'}</ModalHeader>
                <ModalBody>
                    <Row>
                        <Col md={12}>
                            <div className="participant-count">Participant List ({TotalParticipants ? TotalParticipants : '0'})</div>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <div className="table-responsive common-table">
                                <div className="tbl-min-hgt">
                                    <Table>
                                        <thead>
                                            <tr>
                                                <th className="pl-4">Name</th>
                                                <th>Pick</th>
                                                <th>Bid</th>
                                                <th>{activeTab == "1" ? 'Estimated ' : ''}Winnings</th>
                                            </tr>
                                        </thead>
                                        {
                                            TotalParticipants > 0 ?
                                                _.map(Participants, (item, idx) => {
                                                    return (
                                                        <tbody key={idx}>
                                                            <tr>
                                                                <td className="pl-4">{item.user_name ? item.user_name : '--'}</td>
                                                                <td className="text-ellipsis">{item.option ? item.option : '--'}</td>
                                                                <td>{item.bet_coins ? item.bet_coins : '--'}</td>
                                                                {/* <td>{item.estimated_winning ? item.estimated_winning : '--'}</td> */}
                                                                <td>
                                                                    {
                                                                        activeTab == "1" &&
                                                                        <Fragment>
                                                                            {item.estimated_winning ? item.estimated_winning : '--'}
                                                                        </Fragment>
                                                                    }
                                                                    {
                                                                        activeTab == "2" &&
                                                                        <Fragment>
                                                                            {item.win_coins ? item.win_coins : '--'}
                                                                        </Fragment>
                                                                    }
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    )
                                                })
                                                :
                                                <tbody>
                                                    <tr>
                                                        <td colSpan="8">
                                                            {(TotalParticipants == 0 && !PartiListPosting) ?
                                                                <div className="no-records">
                                                                    {NC.NO_RECORDS}</div>
                                                                :
                                                                <Loader />
                                                            }
                                                        </td>
                                                    </tr>
                                                </tbody>
                                        }
                                    </Table>
                                </div>
                                {TotalParticipants > PERPAGE && (
                                    <div className="custom-pagination">
                                        <Pagination
                                            activePage={LIST_CURRENT_PAGE}
                                            itemsCountPerPage={PERPAGE}
                                            totalItemsCount={TotalParticipants}
                                            pageRangeDisplayed={5}
                                            onChange={e => this.handleParticipantsPageChange(e)}
                                        />
                                    </div>
                                )}
                            </div>
                        </Col>
                    </Row>
                </ModalBody>
            </Modal>
        )
    }

    toggleSubmitAnswerModal = () => {
        this.setState({
            submitAnswerOpen: !this.state.submitAnswerOpen
        })
    }

    submitAnswerModal() {
        return (
            <Modal
                isOpen={this.state.submitAnswerOpen}
                className="modal-sm coupon-history prediction-popup"
                toggle={() => this.toggleSubmitAnswerModal()}
            >

                <ModalBody>
                    <Row>
                        <Col md={12}>
                            <div className="ask-text">{MSG_SUBMIT_PREDICTION}<br />
                                {MSG_SUBMIT_PREDICTION_SUB}</div>
                        </Col>
                    </Row>
                </ModalBody>
                <ModalFooter className="request-footer">
                    <Button className="btn-secondary-outline ripple no-btn" onClick={this.toggleSubmitAnswerModal}>No</Button>
                    <Button
                        disabled={this.state.YesPosting}
                        onClick={this.submitAnswer
                        }
                        className="btn-secondary-outline ripple">Yes</Button>
                </ModalFooter>
            </Modal>
        )
    }

    selectPreAnswer = (preMasterId, preOptionId, idx) => {
        this.setState({
            activeAnswer: preOptionId,
            preMasterId: preMasterId,
            preOptionId: preOptionId,
            activeIndex: idx,
        })

    }

    submitAnswer = () => {
        this.setState({ YesPosting: true })
        let { preMasterId, preOptionId, PredictionList } = this.state
        let params = {
            prediction_master_id: preMasterId,
            prediction_option_id: preOptionId
        }
        let TempPredictionList = PredictionList
        WSManager.Rest(NC.baseURL + NC.SUBMIT_PREDICTION_ANSWER, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                _.remove(TempPredictionList, function (item, idx) {
                    return item.prediction_master_id == preMasterId
                })
                this.setState({
                    PredictionList: TempPredictionList,
                    YesPosting: false
                })
                notify.show(Response.message, 'success', 5000)
                this.toggleSubmitAnswerModal()
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    //function to toggle action popup
    toggleActionPopup = (prediction_master_id, idx) => {
        this.setState({
            Message: MSG_DELETE_PREDICTION,
            idxVal: idx,
            PredictionID: prediction_master_id,
            ActionPopupOpen: !this.state.ActionPopupOpen,
            ExpireOnMsg: false,
        })
    }

    deletePrediction = () => {
        let { PredictionID, idxVal } = this.state
        let params = {
            prediction_master_id: PredictionID
        }
        let TempPredictionList = this.state.PredictionList
        WSManager.Rest(NC.baseURL + NC.DELETE_PREDICTION, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                _.remove(TempPredictionList, function (item, idx) {
                    return idx == idxVal
                })
                this.setState({ PredictionList: TempPredictionList })
                this.toggleActionPopup(PredictionID, idxVal)
                notify.show(Response.message, 'success', 5000)
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    copyPredictionUrl = (item) => {
        const el = document.createElement('textarea');
        el.value = NC.baseURL + NC.PredictionShareUrl + item.season_game_uid + '/' + btoa(item.prediction_master_id);
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        notify.show("Copied to clipboard", "success", 2000)
    }

    getBgColor = (prediction_count, total_user_joined) => {
        return (prediction_count != "0" && total_user_joined != "0") ? ((prediction_count / total_user_joined) * 100) : "0"
    }

    editPrediction = (item, isEdit) => {
        let optLength = item.options.length
        this.setState({
            EditPredictionMasterId: item.prediction_master_id,
            Question: item.desc,
            Description: item.source_desc,
            CategoryImageName: item.image,
            option1: optLength > 0 ? item.options[0].option : '',
            option2: optLength > 1 ? item.options[1].option : '',
            option3: optLength > 2 ? item.options[2].option : '',
            option4: optLength > 3 ? item.options[3].option : '',
            ExpireOn: new Date(WSManager.getUtcToLocal(item.deadline_date)),
            SourceUrl: item.source_url,
            formValid: true,
            selFeed: item.is_prediction_feed,
            isEdit: isEdit,
            ExpireOnMsg: false,
        }, () => {
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            })
        })
    }

    onComplete = (idx) => {
        let TempPredictionList = this.state.PredictionList
        TempPredictionList[idx].onCompTimer = true
        this.setState({ PredictionList: TempPredictionList })
    }
    acctiveInFeed = (item)=>{
       let temArr = []
       item.options.map((opt, idx)=>{
        temArr.push({
            'id': opt.prediction_option_id,
            'value': opt.option,
        })
       })
       let params ={
        question: item.desc,
        options: temArr,
        deadline_date: new Date(WSManager.getUtcToLocal(item.deadline_date)),
        season_game_uid: item.season_game_uid,
        sports_id: this.state.sports_id,
        prediction_master_id: item.prediction_master_id
       }
       WSManager.Rest(NC.baseURL + NC.UPDATE_PREDICTION_FEED, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.data.message, 'success', 5000)
                this.getPredictionList()
            }   
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
       
    }


    render() {
        let {selFeed, feedFilter,  ExpireOnMsg, Message, ActionPopupOpen, PublishPosting, activeIndex, activeAnswer, SEASON_GAME_ID, CURRENT_PAGE, COM_CURRENT_PAGE, PERPAGE, PredictionList, Total, activeTab, Question, ExpireOn, formValid, Options1Msg, Options2Msg, QuestionMsg, option1, option2, option3, option4, ListPosting, BackTo ,showBTError} = this.state
        const menu = this.menuItems;
        const feedOptions = [
            {label: 'All', value: ''},
            {label: 'App Only', value: '0'},
            {label: 'Feed Questions', value: '1'}
        ]
        const ActionCallback = {
            Message: Message,
            modalCallback: this.toggleActionPopup,
            ActionPopupOpen: ActionPopupOpen,
            modalActioCallback: this.deletePrediction,
        }
        let prediction_feed = HelperFunction.getMasterData().prediction_feed;
        return (
            <div className="set-prediction">
                <ActionRequestModal {...ActionCallback} />
                {this.submitAnswerModal()}
                <Row className="mt-4">
                    <Col md={12}>
                        <ScrollMenu
                            wheel={0}
                            data={menu}
                            selected={SEASON_GAME_ID}
                            onSelect={this.onSelect}
                            alignCenter={0}
                        />
                    </Col>
                </Row>

                <Row className="mt-1">
                    <Col md={12}>
                        <div className="pre-heading float-left">Prediction test</div>
                        <div onClick={() => this.props.history.push('/prediction/fixture?tab=' + BackTo)} className="go-back float-right">{'<'} Back to fixture</div>
                    </Col>
                </Row>
                <Row>
                    <Col md={12}>
                        <div className="user-navigation">
                            <Row>
                                <Col md={6}>
                                    <Nav tabs>
                                        <NavItem
                                            className={activeTab === '1' ? "active" : ""}
                                            onClick={() => { this.toggle('1'); }}
                                        >
                                            <NavLink>
                                                Active
</NavLink>
                                        </NavItem>
                                        <NavItem
                                            className={activeTab === '2' ? "active" : ""}
                                            onClick={() => { this.toggle('2'); }}
                                        >
                                            <NavLink>
                                                Completed/ Deleted
</NavLink>
                                        </NavItem>
                                    </Nav>
                                </Col>
                                <Col md={6}>
                                    <div className="refresh-page" onClick={() => this.resetChanges(1)}>
                                        <i className="icon-refresh"></i>
                                        <span>Refresh</span>
                                    </div>
                                </Col>
                            </Row>
                            <TabContent activeTab={activeTab}>
                                <TabPane tabId="1" className="animated fadeIn">
                                    <div className="active-items promotion-feedback animated fadeIn">
                                        {
                                            this.props.match.params.fixturetype != 3 && (
                                                <div className="card-box-wrapper card-wrapper">
                                                    <Row>
                                                        <Col md={7}>
                                                            <div className="mb-20">
                                                                <label htmlFor="Question">Question</label>
                                                                <Input
                                                                    maxLength={200}
                                                                    className="question-input"
                                                                    type="textarea"
                                                                    name="Question"
                                                                    value={Question}
                                                                    onChange={this.handleInputChange}
                                                                />
                                                                {!QuestionMsg &&
                                                                    <span className="color-red">
                                                                        Please enter valid question.
</span>
                                                                }
                                                            </div>
                                                            <div className="redeem-box mb-20">
                                                                <Row>
                                                                    <Col md={3}>
                                                                        <label htmlFor="Redeem">Option 1</label>
                                                                        <Input
                                                                            maxLength={30}
                                                                            name='option1'
                                                                            value={option1}
                                                                            onChange={this.handleInputChange}
                                                                        />
                                                                        {!Options1Msg &&
                                                                            <span className="color-red">
                                                                                Please enter alphanumeric option.
</span>
                                                                        }
                                                                    </Col>
                                                                    <Col md={3}>
                                                                        <label htmlFor="Redeem">Option 2</label>
                                                                        <Input
                                                                            maxLength={30}
                                                                            name='option2'
                                                                            value={option2}
                                                                            onChange={this.handleInputChange}
                                                                        />
                                                                        {!Options2Msg &&
                                                                            <span className="color-red">
                                                                                Please enter alphanumeric option.
</span>
                                                                        }
                                                                    </Col>
                                                                    <Col md={3}>
                                                                        <label htmlFor="Redeem">Option 3<sub className="ml-1">optional</sub></label>
                                                                        <Input
                                                                            maxLength={30}
                                                                            name='option3'
                                                                            value={option3}
                                                                            onChange={this.handleInputChange}
                                                                        />
                                                                    </Col>
                                                                    <Col md={3}>
                                                                        <label htmlFor="Redeem">Option 4<sub className="ml-1">optional</sub></label>
                                                                        <Input
                                                                            maxLength={30}
                                                                            name='option4'
                                                                            value={option4}
                                                                            onChange={this.handleInputChange}
                                                                        />
                                                                    </Col>
                                                                </Row>
                                                            </div>
                                                            <div className="redeem-box clearfix">
                                                                <div className="redeem-box-items">
                                                                    <div>
                                                                        <label htmlFor="Redeem">Expires On</label>
                                                                        <div className="expire-input float-left">
                                                                            <label htmlFor="">
                                                                                <i className="icon-calender"></i>
                                                                                <DatePicker
                                                                                    minDate={new Date}
                                                                                    onChange={e => this.handleDateChange(e, "ExpireOn")}
                                                                                    selected={ExpireOn}
                                                                                    className="form-control"
                                                                                    showTimeSelect
                                                                                    timeFormat="HH:mm"
                                                                                    timeIntervals={10}
                                                                                    timeCaption="time"
                                                                                    dateFormat="dd/MM/yyyy h:mm aa"
                                                                                />
                                                                                {ExpireOnMsg &&
                                                                                    <p className="text-red" style={{fontSize: 11}}>{PREDICTION_PAST_TIME_ERR}</p>
                                                                                }
                                                                            </label>
                                                                        </div>
                                                                    </div>   
                                                                    { (prediction_feed && prediction_feed.feed_server == 1) &&
                                                                    <div className="feed-buttons">
                                                                        <div className="custom-radio">
                                                                            <input
                                                                                type="radio"
                                                                                className="custom-control-input"
                                                                                name="selFeed"
                                                                                value="0"
                                                                                id='activateApp'
                                                                                checked={selFeed == '0'}
                                                                                onChange={(e)=>(!this.state.isEdit && this.handleFeedChange(e))}
                                                                            />
                                                                            <label className="custom-control-label" htmlFor="activateApp">
                                                                                <span className="input-text">Activate in App Only</span>
                                                                            </label>
                                                                        </div>
                                                                        <div className="custom-radio">
                                                                            <input
                                                                                type="radio"
                                                                                className="custom-control-input"
                                                                                name="selFeed"
                                                                                value="1"
                                                                                id="activateFeed"
                                                                                checked={selFeed == '1'}
                                                                                onChange={(e)=>(!this.state.isEdit && this.handleFeedChange(e))}
                                                                            />
                                                                            <label className="custom-control-label" htmlFor="activateFeed">
                                                                                <span className="input-text">Activate in Feed</span>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    }
                                                                    <div className="publish-box">
                                                                        <div onClick={() => this.resetChanges(2)} className="refresh icon-reset"></div>
                                                                        <Button
                                                                            
                                                                            disabled={formValid == false || PublishPosting}
                                                                            className="btn-secondary-outline publish-btn"
                                                                            onClick={() =>( !this.state.singleClick && this.createPrediction())}
                                                                        >Publish</Button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </Col>
                                                        <Col md={5}>
                                                            <div className={`img-preview-box prediction-dashboard ${!_.isEmpty(Question) || !_.isEmpty(option1) || ExpireOn ? ' dot-border' : ''}`}>
                                                                <Fragment>
                                                                    {!_.isEmpty(Question) || !_.isEmpty(option1) || ExpireOn ?
                                                                        (Question.trim().length > 0 || option1.trim().length > 0) ?
                                                                            <div className="question-box">
                                                                                <div className="clearfix">
                                                                                    <div className="ques">{Question}</div>
                                                                                    <div className="ques-action">
                                                                                        <i className="icon-share-fill"></i>      </div>
                                                                                </div>
                                                                                <div className="pool-answer">
                                                                                    <ul className="pool-list">
                                                                                        {option1.trim().length > 0 && (<li className="clearfix pool-item">
                                                                                            <div className="float-left">{option1}</div>
                                                                                            <div className="float-right">0%</div>
                                                                                        </li>)}
                                                                                        {option2.trim().length > 0 && (<li className="clearfix pool-item">
                                                                                            <div className="float-left">{option2}</div>
                                                                                            <div className="float-right">0%</div>
                                                                                        </li>)}
                                                                                        {option3.trim().length > 0 && (<li className="clearfix pool-item">
                                                                                            <div className="float-left">{option3}</div>
                                                                                            <div className="float-right">0%</div>
                                                                                        </li>)}
                                                                                        {option4.trim().length > 0 && (<li className="clearfix pool-item">
                                                                                            <div className="float-left">{option4}</div>
                                                                                            <div className="float-right">0%</div>
                                                                                        </li>)}
                                                                                    </ul>
                                                                                </div>
                                                                                <div className="pool-box clearfix">
                                                                                    <div className="poll-info">
                                                                                        Pool
<img src={Images.REWARD_ICON} alt="" />
                                                                                        0
</div>
                                                                                    <div className="pre-timer">
                                                                                        <ul className="prediction-list">
                                                                                            <li className="time">
                                                                                                {
                                                                                                    ExpireOn ?
                                                                                                        HelperFunction.showCountDown(ExpireOn) ?
                                                                                                            <Countdown date={ExpireOn} />
                                                                                                            : <Moment className="date-style" date={ExpireOn} format="D MMM -  hh:mm A" />
                                                                                                        :
                                                                                                        ''
                                                                                                }

                                                                                            </li>
                                                                                            <li className="predicted">0 Predicted</li>
                                                                                        </ul>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            :
                                                                            ''
                                                                        :
                                                                        <span className="preview-text">Your Preview will<br /> appear here</span>
                                                                    }
                                                                </Fragment>
                                                            </div>
                                                        </Col>
                                                    </Row>

                                                </div>
                                            )
                                        } 
                                        <Row>
                                            <Col md={12}>
                                                <div className="feedFilter">
                                                    <h2>Queston List</h2>
                                                    {
                                                        (prediction_feed.feed_server == 1 || prediction_feed.feed_client == 1) && 
                                                        <>
                                                            <ul className="list-unstyled CoditionalList">
                                                                <li>
                                                                    <Select
                                                                        className="dfs-selector"
                                                                        id=""
                                                                        name=""
                                                                        placeholder="All"
                                                                        value={feedFilter}
                                                                        options={feedOptions}
                                                                        onChange={(e) => this.handleSelect(e)}
                                                                        />
                                                                </li>
                                                                <li className="feedInfo">
                                                                    <span className="grrenA">A</span>
                                                                    <span className="feedLable">App</span>
                                                                </li>
                                                                <li className="feedInfo">
                                                                    <span className="yallowF">F</span>
                                                                    <span className="feedLable">Feed</span>
                                                                </li>
                                                            </ul>
                                                        </>  
                                                    }
                                                </div>
                                                
                                            </Col>
                                        </Row>

                                        <Row className={`prediction-dashboard ${prediction_feed.feed_server != 1 ? 'mt-5' : ''}`}>
                                            {this.predictUserListModal()}
                                            {Total > 0 ?
                                                _.map(PredictionList, (item, preIdx) => {
                                                    return (
                                                        <Col md={4} key={preIdx}>
                                                            <div className="question-box">
                                                                {
                                                                    item.is_pin == "1" && <i className="icon-pinned-fill pinned-prediction"></i>
                                                                }
                                                                {
                                                                (prediction_feed && prediction_feed.feed_server == 1 && item.is_prediction_feed == 0) &&
                                                                    
                                                                    <p className={"activeFeed" } onClick={()=>this.acctiveInFeed(item)}><i className="icon-Path"></i><span className="underline-none">Activate in Feeds</span> </p>
                                                                }
                                                                { (prediction_feed && prediction_feed.feed_server == 1 && item.is_prediction_feed == 1) &&
                                                                  <p className="activeFeed visibiliy-none">Activate in Feeds</p>
                                                                }


                                                                <div className="clearfix questionTitle">
                                                                    <div className="ques">
                                                                        <ReadMoreAndLess
                                                                            ref={this.ReadMore}
                                                                            charLimit={90}
                                                                            readMoreText="Read more"
                                                                            readLessText="Read less"
                                                                        >
                                                                            {item.desc}
                                                                        </ReadMoreAndLess>
                                                                    </div>
                                                                    <div className="queOpt">
                                                                        {
                                                                            prediction_feed && (prediction_feed.feed_server == 1 || prediction_feed.feed_client == 1) &&
                                                                            <>
                                                                                {
                                                                                    item.is_prediction_feed == 0 &&  
                                                                                    <div><span className="itemA">A</span></div>
                                                                                }
                                                                                {
                                                                                    item.is_prediction_feed == 1 &&  
                                                                                    <div><span className="itemF">F</span> </div>
                                                                                }
                                                                            </>
                                                                        }
                                                                    
                                                                   
                                                                        <div className="ques-action">
                                                                            { (item.is_prediction_feed == 0 ||  (item.is_prediction_feed == 1 && prediction_feed.feed_server == 1)) &&
                                                                            <UncontrolledDropdown direction="left">
                                                                                <DropdownToggle tag="i" caret={false} className="icon-more">
                                                                                </DropdownToggle>
                                                                                <DropdownMenu>
                                                                                    <DropdownItem onClick={() => this.updatePinPrediction(item, preIdx)}>
                                                                                        <i className="icon-pinned-fill"></i>
                                                                                        {item.is_pin == "1" ? "Unpin" : "Pin"}
                                                                                    </DropdownItem>
                                                                                    
                                                                                    {(!HelperFunction.getTimeDiff(item.deadline_date) && !item.onCompTimer) &&
                                                                                    <DropdownItem onClick={() => this.copyPredictionUrl(item)}><i className="icon-share-fill"></i>Share</DropdownItem>
                                                                                    }
                                                                                    
                                                                                    <DropdownItem
                                                                                        onClick={() => this.toggleActionPopup(item.prediction_master_id, preIdx)}
                                                                                    >
                                                                                        <i className="icon-delete1"></i>Delete
                                                                                    </DropdownItem>
                                                                                    <DropdownItem
                                                                                        onClick={() => this.editPrediction(item, true)}
                                                                                    >
                                                                                        <i className="icon-edit"></i>Edit
                                                                                    </DropdownItem>
                                                                                </DropdownMenu>                 
                                                                            </UncontrolledDropdown>
                                                                            }

                                                                            <i onClick={() => this.pausePlayPrediction(item, preIdx)}
                                                                                className={item.status == 3 ? "icon-ic-play" : "icon-pause"}
                                                                            ></i>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div className="pool-answer">
                                                                    <ul className="pool-list">
                                                                        {
                                                                            _.map(item.options, (pre_options, idx) => {
                                                                                let pColor = this.getBgColor(pre_options.prediction_count, item.total_user_joined)
                                                                                return (
                                                                                    <li
                                                                                        style={{
                                                                                            backgroundImage:
                                                                                                "linear-gradient(to right, #E4F9FE " +
                                                                                                pColor + "%, #F2F2F2 0%)"
                                                                                        }}
                                                                                        onClick={() =>( (item.is_prediction_feed == 0 ||  (item.is_prediction_feed == 1 && prediction_feed.feed_server == 1)) &&
                                                                                             this.selectPreAnswer(item.prediction_master_id, pre_options.prediction_option_id, preIdx)) }
                                                                                        key={idx}
                                                                                        className={`clearfix pool-item ${pre_options.prediction_option_id == activeAnswer ? "active" : ""}`}>
                                                                                        <div className="float-left answer-opt">
                                                                                            {pre_options.option}
                                                                                        </div>
                                                                                        <div className="float-right">
                                                                                            {pre_options.prediction_count != "0" || item.total_user_joined != "0" ?
                                                                                                ((pre_options.prediction_count / item.total_user_joined) * 100).toFixed(2)
                                                                                                :
                                                                                                "0"
                                                                                            }%
</div>
                                                                                    </li>
                                                                                )
                                                                            })
                                                                        }
                                                                    </ul>
                                                                </div>
                                                                <div className="pool-box clearfix">
                                                                    <div className="float-left">
                                                                        <div className="poll-info">
                                                                            Pool
                                                                            <img src={Images.REWARD_ICON} alt="" />
                                                                            {item.total_pool}
                                                                        </div>
                                                                        <div className="pre-timer">
                                                                            <ul className="prediction-list">
                                                                                <li className="time">
                                                                                    {
                                                                                        (HelperFunction.showCountDown(item.deadline_date) && !item.onCompTimer) ?

                                                                                            <Countdown
                                                                                                onComplete={() => this.onComplete(preIdx)}
                                                                                                date={WSManager.getUtcToLocal(item.deadline_date)} />
                                                                                            :
                                                                                            // <Moment className="date-style" date={WSManager.getUtcToLocalFormat(item.deadline_date, 'D-MMM-YYYY hh:mm A')} format="D MMM - hh:mm A" />
                          <>{HelperFunction.getFormatedDateTime(item.deadline_date, 'D MMM - hh:mm A')}</>

                                                                                    }
                                                                                </li>
                                                                                <li className="predicted"
                                                                                    onClick={() => item.total_user_joined > 0 ? this.toggleUserListModal(item.prediction_master_id, item.desc) : ''}>{item.total_user_joined} Predicted</li>
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                    <div className="float-right">
                                                                        {(activeAnswer != 0 && activeIndex == preIdx) && 
                                                                            <Button onClick={() => this.toggleSubmitAnswerModal()} className="ques-action action-status">ANSWER</Button>
                                                                        }
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </Col>
                                                    )
                                                })
                                                :
                                                <Col md={12}>
                                                    {(Total == 0 && !ListPosting) ?
                                                        <div className="no-records">{NC.NO_RECORDS}</div>
                                                        :
                                                        <Loader />
                                                    }
                                                </Col>
                                            }
                                        </Row>
                                        {Total > PERPAGE && (
                                            <div className="custom-pagination float-right">
                                                <Pagination
                                                    activePage={CURRENT_PAGE}
                                                    itemsCountPerPage={PERPAGE}
                                                    totalItemsCount={Total}
                                                    pageRangeDisplayed={5}
                                                    onChange={e => this.handlePageChange(e)}
                                                />
                                            </div>
                                        )
                                        }
                                    </div>
                                </TabPane>
                                {
                                    (activeTab == '2') &&
                                    <TabPane tabId="2" className="animated fadeIn completed-tab">
                                        <div className="trending-navigation">
                                            <Row className="prediction-dashboard mt-5">
                                                {Total > 0 ?
                                                    _.map(PredictionList, (item, idx) => {
                                                        return (
                                                            <Col md={4} key={idx}>
                                                                <div className="question-box">
                                                                    <div className="clearfix">
                                                                        <div className="ques">
                                                                            <ReadMoreAndLess
                                                                                ref={this.ReadMore}
                                                                                charLimit={90}
                                                                                readMoreText="Read more"
                                                                                readLessText="Read less"
                                                                            >
                                                                                {item.desc}
                                                                            </ReadMoreAndLess>
                                                                        </div>
                                                                      
                                                                        <div
                                                                            className={`ques-action action-status ${item.status == "4" ? 'deleted' : ''}`}>
                                                                            {item.status == "4" ? 'Deleted' : item.status == "2" ? 'Completed' : ''}
                                                                        </div>
                                                                    </div>
                                                                    <div className="pool-answer">
                                                                        <ul className="pool-list">
                                                                            {
                                                                                _.map(item.options, (pre_options, idx) => {
                                                                                    return (
                                                                                        <li

                                                                                            style={{
                                                                                                backgroundImage:
                                                                                                    "linear-gradient(to right, #E4F9FE " + ((pre_options.prediction_count / item.total_user_joined) * 100) + "%, #F2F2F2 0%)"
                                                                                            }}

                                                                                            key={idx}
                                                                                            className={`clearfix pool-item ${pre_options.is_correct == '1' ? 'active' : ''}`}
                                                                                        >
                                                                                            <div className="float-left">{pre_options.option}</div>
                                                                                            <div className="float-right">
                                                                                                {pre_options.prediction_count != "0" || item.total_user_joined != "0" ?
                                                                                                    ((pre_options.prediction_count / item.total_user_joined) * 100).toFixed(2)
                                                                                                    :
                                                                                                    "0"
                                                                                                }%
</div>
                                                                                        </li>
                                                                                    )
                                                                                })
                                                                            }
                                                                        </ul>
                                                                    </div>
                                                                    <div className="pool-box clearfix">
                                                                        <div className="poll-info">
                                                                            Pool
<img src={Images.REWARD_ICON} alt="" />
                                                                            {item.total_pool}
                                                                        </div>
                                                                        <div className="pre-timer">
                                                                            <ul className="prediction-list">
                                                                                <li className="time">
                                                                                    {/* <Moment date={WSManager.getUtcToLocalFormat(item.deadline_date, 'D-MMM-YYYY hh:mm A')} format="D MMM - hh:mm A" /> */}
                         {HelperFunction.getFormatedDateTime(item.deadline_date, 'D MMM - hh:mm A')}

                                                                                </li>


                                                                                <li className="predicted" onClick={() => item.total_user_joined > 0 ? this.toggleUserListModal(item.prediction_master_id, item.desc) : ''}>{item.total_user_joined} Predicted</li>
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </Col>
                                                        )
                                                    })
                                                    :
                                                    <Col md={12}>
                                                        {(Total == 0 && !ListPosting) ?
                                                            <div className="no-records">{NC.NO_RECORDS}</div>
                                                            :
                                                            <Loader />
                                                        }
                                                    </Col>
                                                }
                                            </Row>
                                            {Total > PERPAGE && (
                                                <div className="custom-pagination float-right">
                                                    <Pagination
                                                        activePage={COM_CURRENT_PAGE}
                                                        itemsCountPerPage={PERPAGE}
                                                        totalItemsCount={Total}
                                                        pageRangeDisplayed={5}
                                                        onChange={e => this.handleCompletedChange(e)}
                                                    />
                                                </div>
                                            )}
                                        </div>
                                    </TabPane>
                                }
                            </TabContent>
                        </div>
                    </Col>
                </Row>
            </div>
        )
    }
}
export default SetPrediction