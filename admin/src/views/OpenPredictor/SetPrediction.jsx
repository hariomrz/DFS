import React, { Component, Fragment } from "react";
import Images from '../../components/images';
import { Row, Col, TabContent, TabPane, Nav, NavItem, Button, NavLink, Input, UncontrolledDropdown, DropdownToggle, DropdownMenu, DropdownItem, Modal, ModalBody, ModalHeader, ModalFooter, Table, Progress } from 'reactstrap';
import _ from 'lodash';
import Pagination from "react-js-pagination";
import Loader from '../../components/Loader';
import WSManager from '../../helper/WSManager';
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import Moment from 'react-moment';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import HF from '../../helper/HelperFunction';
import ReadMoreAndLess from 'react-read-more-less';
import ScrollMenu from 'react-horizontal-scrolling-menu';
import ActionRequestModal from '../../components/ActionRequestModal/ActionRequestModal';


import Countdown from 'react-countdown-now';
import LS from 'local-storage';
import HelperFunction from "../../helper/HelperFunction";
import { MomentDateComponent } from "../../components/CustomComponent";
import { MSG_DELETE_PREDICTION, MSG_SUBMIT_PREDICTION, MSG_SUBMIT_PREDICTION_SUB, PREDICTION_PAST_TIME_ERR, SOURCE_URL_MSG, VALID_QUESTION, VALID_OPTION, VALID_DESCRIPTION, QUES_LIMIT_TEXT, DESC_LIMIT_TEXT, NO_RECORDS } from "../../helper/Message";

// One item component
// selected prop will be passed
const MenuItem = ({ item, selected }) => {
    return (
        <div className="category-card cursor-pointer cursor-pointer">
            <img src={NC.S3 + NC.OP_CATEGORY + item.image} alt="" className="cat-img" />
            <div className="cat-info-box">
                <div className="cat-title">
                    {item.name}
                </div>
                <div className="cat-questions">
                    {
                        item.completed_count == "0"
                            ?
                            item.question_count
                            :
                            item.completed_count
                    }{' '}Questions</div>
            </div>
        </div>
    )
};

// All items component
// Important! add unique key
export const Menu = (list, selected) =>
    list.map(el => {
        return <MenuItem item={el} key={el.category_id} selected={selected} />;
    });

class SetPrediction extends Component {
    constructor(props) {
        super(props)
        this.state = {
            activeTab: this.props.match.params.type == 2 ? '2' : '1',
            ListPosting: false,
            PartiListPosting: false,
            PublishPosting: false,
            Options1Msg: true,
            Options2Msg: true,
            QuestionMsg: true,
            categoryView: true,
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            LIST_CURRENT_PAGE: 1,
            COM_CURRENT_PAGE: 1,
            PredictionList: [],
            CATEGORY_ID: this.props.match.params.category_id,
            HistoryModalOpen: false,
            submitAnswerOpen: false,
            preQuestion: '',
            activeAnswer: 0,
            ActionPopupOpen: false,
            FixtureList: [],
            ExpireOnMsg: false,
            option1: '',
            option2: '',
            option3: '',
            option4: '',
            Description: '',
            EntryType: NC.ALLOW_OP_WITH_POOL != "2" ? '0' : "1",
            Winning: '',
            EntryFee: '',
            EntryFeeMsg: true,
            WinningMsg: true,
            onCompTimer: true,
            Description: '',
            SourceUrl: '',
            SourceUrlMsg: true,
            DescriptionMsg: true,
            ProofDesc : '', 
            ProofImage : '',
            ProofImageName : '',
            PrfImgPosting : false,
            EditPredictionMasterId: '',
            BackTo: this.props.match.params.type,
        }
    }

    onSelect = key => {
        this.setState({
            CATEGORY_ID: key,
            CURRENT_PAGE: 1,
            LIST_CURRENT_PAGE: 1,
            COM_CURRENT_PAGE: 1,
        }, this.getPredictionList);
    }

    componentDidMount() {
        let Fixture = LS.get('selected_fixture')
        this.setState({ SelectedFixture: Fixture }, () => {
            this.getPredictionList()
            if (this.props.match.params.type) {
                this.getLiveFixtures()
            }
        })
    }

    getLiveFixtures = () => {
        let { SelectedFixture } = this.state
        let params = {
            items_perpage: 1000,
            current_page: 1,
            status: this.props.match.params.type //live, Upcoming          
        }
        let fixture = SelectedFixture
        WSManager.Rest(NC.baseURL + NC.OP_GET_CATEGORY_LIST_BY_STATUS, params).then(Response => {
            if (Response.response_code == NC.successCode) {

                let tempResArr = Response.data.category_list
                _.remove(tempResArr, function (item, idx) {
                    return fixture.category_id == item.category_id
                })
                tempResArr.unshift(fixture);
                this.setState({
                    FixtureList: tempResArr,
                }, () => {this.menuItems = Menu(this.state.FixtureList, this.state.CATEGORY_ID);
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
        let { COM_CURRENT_PAGE, PERPAGE, CURRENT_PAGE, CATEGORY_ID, activeTab } = this.state
        let params = {
            "category_id": CATEGORY_ID,
            "items_perpage": PERPAGE,
            "current_page": activeTab == "2" ? COM_CURRENT_PAGE : CURRENT_PAGE,
            status: activeTab == "2" ? activeTab : "0"
        }

        WSManager.Rest(NC.baseURL + NC.OP_GET_ALL_PREDICTION, params).then(Response => {
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
        this.setState({
            activeTab: tab,
            CURRENT_PAGE: 1,
            LIST_CURRENT_PAGE: 1,
            COM_CURRENT_PAGE: 1,
        }, () => {
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
        this.setState({ 
            PublishPosting: true,
            ExpireOnMsg: false,
            EntryFeeMsg: true,
            WinningMsg: true,
        })
        let { EditPredictionMasterId, Description, SourceUrl, CATEGORY_ID, Question, option1, option2, option3, option4, ExpireOn, FixtureList, EntryFee, Winning, EntryType } = this.state
        if (!ExpireOn || this.diff_minutes(ExpireOn) < 0) {
            this.setState({
                ExpireOnMsg: true,
                PublishPosting: false
            })
            return false
        }

        this.setState({ categoryView: false })

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
            category_id: CATEGORY_ID,
            source_desc: Description,
            source_url: SourceUrl
        }

        let CallUrl = NC.OP_CREATE_PREDICTION
        if (EditPredictionMasterId) {
            params.prediction_master_id = EditPredictionMasterId
            CallUrl = NC.OP_UPDATE_PREDICTION
        }

        let tempFixList = _.clone(FixtureList);
        let CatIndex = _.findIndex(tempFixList, function (fx) { return fx.category_id == CATEGORY_ID; });
        WSManager.Rest(NC.baseURL + CallUrl, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, 'success', 5000)
                if (!EditPredictionMasterId) {
                tempFixList[CatIndex].question_count = parseInt(tempFixList[CatIndex].question_count) + 1
                }
                this.setState({
                    FixtureList: tempFixList,
                    categoryView: true,
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
                    QuestionMsg: true,
                    EntryFee: '',
                    EntryFeeMsg: true,
                    Winning: '',
                    WinningMsg: true,
                    EntryType: '0',
                    Description: '',
                    SourceUrl: '',
                    EditPredictionMasterId: '',
                }, this.getPredictionList)
            } else {
                this.setState({
                    PublishPosting: false,
                    Question: '',
                    option1: '',
                    Options1Msg: true,
                    option2: '',
                    Options2Msg: true,
                    option3: '',
                    option4: '',
                    ExpireOn: '',
                    QuestionMsg: true,
                    Description: '',
                    SourceUrl: '',
                    EditPredictionMasterId: '',
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    handleInputChange = (event) => {
        let name = event.target.name
        let value = event.target.value
        if (name != 'EntryType' || name != 'EntryFee' || name != 'Winning') {
            this.setState({ [name]: value },
                () => this.validateForm(name, value)
            )
        } else {
            this.setState({ [name]: value })
        }
    }

    handleDateChange = (date, dateType) => {
        this.setState({ [dateType]: date })
    }

    validateForm = (name, value) => {
        let QuestionValid = this.state.Question
        let Option1Valid = this.state.option1
        let Option2Valid = this.state.option2

        switch (name) {
            case 'Question':
                QuestionValid = (value.trim().length > 2 && value.length <= 201) ? true : false;
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

            default:
                break;
        }
        this.setState({
            formValid: (QuestionValid && Option1Valid && Option2Valid)
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
            Description: '',
            SourceUrl: '',
            OptionsMsg: true,
            QuestionMsg: true,
            CURRENT_PAGE: 1
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
        WSManager.Rest(NC.baseURL + NC.OP_PAUSE_PLAY_PREDICTION, params).then(Response => {
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
        WSManager.Rest(NC.baseURL + NC.OP_UPDATE_PIN_PREDICTION, params).then(Response => {
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

    toggleUserListModal = (prediction_master_id, pre_question, entrytype) => {
        this.setState({
            ULEntryType: entrytype,
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

        WSManager.Rest(NC.baseURL + NC.OP_GET_PREDICTION_PARTICIPANTS, params).then(Response => {
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
        let { ULEntryType, PartiListPosting, LIST_CURRENT_PAGE, PERPAGE, Participants, TotalParticipants, ListPosting, preQuestion } = this.state
        return (
            <Modal
                isOpen={this.state.HistoryModalOpen}
                className="modal-md coupon-history prediction-popup"
                toggle={() => this.toggleUserListModal('', preQuestion)}
            >
                <ModalHeader className="ques-head-hgt">{preQuestion ? preQuestion : '--'}</ModalHeader>
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
                                                {
                                                    ULEntryType == "0" &&
                                                    <th>Estimated Winnings</th>
                                                }
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
                                                                {
                                                                    ULEntryType == "0" &&
                                                                    <td>{item.estimated_winning ? item.estimated_winning : '--'}</td>
                                                                }
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

    toggleSubmitAnswerModal = (AlertMSg, edit_item, idx) => {
        this.setState({
            ListIdex: idx,
            preMasterId: edit_item.prediction_master_id,
            AlertMSg: AlertMSg,
            EditItem: edit_item,
            ProofDesc: edit_item.proof_desc,
            ProofImage: edit_item.proof_image ? NC.S3 + NC.OP_PROOF + edit_item.proof_image : '',
            ProofImageName: edit_item.proof_image ? edit_item.proof_image : '',
            submitAnswerOpen: !this.state.submitAnswerOpen
        })
    }

    submitAnswerModal() {
        let { PrfImgPosting,ListIdex, EditItem, ProofDesc, ProofImage, AlertMSg } = this.state
        return (
            <Modal
                isOpen={this.state.submitAnswerOpen}
                className="modal-sm coupon-history prediction-popup"
                toggle={() => this.toggleSubmitAnswerModal(AlertMSg, EditItem, ListIdex)}
            >

                <ModalBody>
                    {AlertMSg && (<Row>
                        <Col md={12}>
                            <div className="ask-text">{MSG_SUBMIT_PREDICTION}<br />
                                {MSG_SUBMIT_PREDICTION_SUB}</div>
                        </Col>
                    </Row>)}
                    <Row>
                        <Col md={12}>
                            <div className={`op-create-category proof-box ${!AlertMSg ? 'pt-4' : ''}`}>
                                <div className="mb-20">
                                    <label htmlFor="ProofDesc">Proof Description</label>
                                    <Input
                                        type="textarea"
                                        maxLength={140}
                                        className="question-input pf-desc-wdt"
                                        name="ProofDesc"
                                        value={ProofDesc}
                                        onChange={this.handleInputChange}
                                    />
                                </div>
                                <div className="redeem-box position-relative clearfix">
                                    <label htmlFor="Redeem">Proof Image</label>
                                    <div className="select-image-box float-left">
                                        <div className="dashed-box">
                                            {!_.isEmpty(ProofImage) ?
                                                <Fragment>
                                                    <i onClick={this.resetFile} className="icon-close"></i>
                                                    <img className="img-cover" src={ProofImage} />
                                                </Fragment>
                                                :
                                                <Fragment>
                                                    <Input
                                                        accept="image/x-png,
                                                        image/jpeg,image/jpg"
                                                        type="file"
                                                        name='ProofImage'
                                                        id="ProofImage"
                                                        onChange={this.onChangeImage}
                                                    />
                                                    <img className="def-addphoto" src={Images.DEF_ADDPHOTO} alt="" />
                                                </Fragment>
                                            }
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Col>
                    </Row>
                </ModalBody>
                <ModalFooter className="request-footer">
                    <Button className="btn-secondary-outline ripple no-btn" onClick={() => this.toggleSubmitAnswerModal(AlertMSg, EditItem, ListIdex)}>No</Button>
                    <Button 
                        disabled={PrfImgPosting}
                        onClick={this.submitAnswer}
                        className={`btn-secondary-outline ${AlertMSg ? 'yes-wd-cls' : ''}`}>{AlertMSg ? 'Yes' : 'Update'}</Button>
                </ModalFooter>
            </Modal>
        )
    }

    selectPreAnswer = (preMasterId, preOptionId, idx) => {
        this.setState({
            activeAnswer: preOptionId,
            preOptionId: preOptionId,
            activeIndex: idx,
        })

    }

    submitAnswer = () => {
        this.setState({ PrfImgPosting: true })
        let { ListIdex, EditItem, AlertMSg, ProofImageName, ProofDesc, preMasterId, preOptionId, PredictionList } = this.state
        let params = {
            prediction_master_id: preMasterId,
            "proof_desc": ProofDesc,
            "proof_image": ProofImageName
        }
        let URL = NC.OP_UPDATE_PREDICTION_PROOF
        if (AlertMSg){
            params.prediction_option_id = preOptionId
            URL = NC.OP_SUBMIT_PREDICTION_ANSWER
        }

        let TempPredictionList = PredictionList
        WSManager.Rest(NC.baseURL + URL, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                if (AlertMSg) {
                    _.remove(TempPredictionList, function (item, idx) {
                        return item.prediction_master_id == preMasterId
                    })
                }else{
                    TempPredictionList[ListIdex].proof_desc = ProofDesc
                    TempPredictionList[ListIdex].proof_image = ProofImageName
                }
                this.setState({ 
                    PredictionList: TempPredictionList,
                    ProofDesc: '',
                    ProofImage: '',
                    ProofImageName: '',
                    activeAnswer: 0,
                    PrfImgPosting: false
                 })
                notify.show(Response.message, 'success', 5000)
                this.toggleSubmitAnswerModal(AlertMSg,EditItem)
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
            ActionPopupOpen: !this.state.ActionPopupOpen
        })
    }

    deletePrediction = () => {
        let { PredictionID, idxVal } = this.state
        let params = {
            prediction_master_id: PredictionID
        }
        let TempPredictionList = this.state.PredictionList
        WSManager.Rest(NC.baseURL + NC.OP_DELETE_PREDICTION, params).then(Response => {
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
        el.value = NC.baseURL + NC.PredictionShareUrl + item.category_id + '/' + btoa(item.prediction_master_id);

        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        notify.show("Copied to clipboard", "success", 2000)
    }

    getBgColor = (prediction_count, total_user_joined) => {
        return (prediction_count != "0" && total_user_joined != "0") ? ((prediction_count / total_user_joined) * 100) : "0"
    }

    onComplete = (idx) => {
        let TempPredictionList = this.state.PredictionList
        TempPredictionList[idx].onCompTimer = true
        this.setState({ PredictionList: TempPredictionList })
    }

    onChangeImage = (event) => {
        this.setState({
            ProofImage: URL.createObjectURL(event.target.files[0]),
            PrfImgPosting : true
        });
        const file = event.target.files[0];
        if (!file) {
            return;
        }
        var data = new FormData();
        data.append("userfile", file);
        WSManager.multipartPost(NC.baseURL + NC.OP_DO_UPLOAD_PROOF_IMAGE, data)
            .then(Response => {
                if (Response.response_code == NC.successCode) {
                    this.setState({
                        ProofImageName: Response.data.file_name,
                        PrfImgPosting: false,
                    });
                } else {
                    this.setState({
                        ProofImage: null,
                        PrfImgPosting: false,
                    });
                }
            }).catch(error => {
                notify.show(NC.SYSTEM_ERROR, "error", 3000);
            });
    }

    resetFile = () => {
        this.setState({
            ProofImage: null,
            ProofImageName: '',
        });
    }

    editPrediction = (item) => {
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
            ExpireOn: new Date(HF.getFormatedDateTime(item.deadline_date)),
            SourceUrl: item.source_url,
            formValid: true,
        }, () => {
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            })
        })
    }

    render() {
        let { SourceUrlMsg, SourceUrl, DescriptionMsg, Description, categoryView, WinningMsg, EntryFeeMsg, Winning, EntryFee, EntryType, CATEGORY_ID, ExpireOnMsg, Message, ActionPopupOpen, PublishPosting, activeIndex, activeAnswer, CURRENT_PAGE, COM_CURRENT_PAGE, PERPAGE, PredictionList, Total, activeTab, Question, ExpireOn, formValid, Options1Msg, Options2Msg, QuestionMsg, option1, option2, option3, option4, ListPosting, BackTo } = this.state
        const menu = this.menuItems;

        const ActionCallback = {
            Message: Message,
            modalCallback: this.toggleActionPopup,
            ActionPopupOpen: ActionPopupOpen,
            modalActioCallback: this.deletePrediction,
        }

        return (
            <div className="set-prediction">
                <ActionRequestModal {...ActionCallback} />
                {this.submitAnswerModal()}
                {
                    categoryView && (
                        <Row className="mt-4">
                            <Col md={12}>
                                <ScrollMenu
                                    wheel={0}
                                    data={menu}
                                    selected={CATEGORY_ID}
                                    onSelect={this.onSelect}
                                    alignCenter={0}
                                    scrollToSelected={true}
                                />
                            </Col>
                        </Row>
                    )
                }

                <Row className="mt-4">
                    <Col md={12}>
                        <div className="pre-heading float-left">Prediction</div>
                        <div onClick={() => this.props.history.push('/open-predictor/category?tab=' + BackTo)} className="go-back float-right">{'<'} Back to category</div>
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
                                                            <div className="input-form-parent">
                                                                {
                                                                    NC.ALLOW_OP_WITH_POOL == '3' &&
                                                                    <div className="entry-fee-box input-box">
                                                                        <ul className="coupons-option-list">
                                                                            <li className="coupons-option-item">
                                                                                <div className="custom-radio">
                                                                                    <input
                                                                                        type="radio"
                                                                                        className="custom-control-input"
                                                                                        name="EntryType"
                                                                                        value="0"
                                                                                        checked={EntryType === '0'}
                                                                                        onChange={this.handleInputChange}
                                                                                    />
                                                                                    <label className="custom-control-label">
                                                                                        <span className="input-text">Pool</span>
                                                                                    </label>
                                                                                </div>
                                                                            </li>
                                                                            <li className="coupons-option-item">
                                                                                <div className="custom-radio">
                                                                                    <input
                                                                                        type="radio"
                                                                                        className="custom-control-input"
                                                                                        name="EntryType"
                                                                                        value="1"
                                                                                        checked={EntryType === '1'}
                                                                                        onChange={this.handleInputChange}
                                                                                    />
                                                                                    <label className="custom-control-label">
                                                                                        <span className="input-text">Fixed</span>
                                                                                    </label>
                                                                                </div>

                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                }

                                                                {
                                                                    EntryType === '1' && (
                                                                        <Row>
                                                                            <Col md={6}>
                                                                                <div className="mb-20">
                                                                                    <label htmlFor="Question">Entry</label>
                                                                                    <Input
                                                                                        maxLength={4}
                                                                                        minLength={1}
                                                                                        type="text"
                                                                                        name="EntryFee"
                                                                                        value={EntryFee}
                                                                                        onChange={this.handleInputChange}
                                                                                    />
                                                                                    {!EntryFeeMsg &&
                                                                                        <span className="color-red">
                                                                                            Entry should be between 0 to 9999.
</span>
                                                                                    }
                                                                                </div>
                                                                            </Col>
                                                                            <Col md={6}>
                                                                                <div className="mb-20">
                                                                                    <label htmlFor="Question">Win Prize</label>
                                                                                    <Input
                                                                                        maxLength={4}
                                                                                        minLength={1}
                                                                                        type="text"
                                                                                        name="Winning"
                                                                                        value={Winning}
                                                                                        onChange={this.handleInputChange}
                                                                                    />
                                                                                    {!WinningMsg &&
                                                                                        <span className="color-red">
                                                                                            Win prize should be between 10 to 9999.
</span>
                                                                                    }
                                                                                </div>
                                                                            </Col>
                                                                        </Row>
                                                                    )
                                                                }

                                                                <div className="mb-20">
                                                                    <label htmlFor="Question">Question<sub className="ml-1">({QUES_LIMIT_TEXT})</sub></label>
                                                                    <Input
                                                                        maxLength={200}
                                                                        className="question-input"
                                                                        type="textarea"
                                                                        name="Question"
                                                                        value={Question}
                                                                        onChange={this.handleInputChange}
                                                                    />
                                                                    {!QuestionMsg &&
                                                                        <span className="color-red">{VALID_QUESTION}</span>
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
                                                                                <span className="color-red">{VALID_OPTION}</span>
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
                                                                            {!Options2Msg && <span className="color-red">{VALID_OPTION}</span>
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
                                                                <div className="mb-20">
                                                                    <label htmlFor="Question">Description<sub className="ml-1">({DESC_LIMIT_TEXT})</sub></label>
                                                                    <Input
                                                                        maxLength={140}
                                                                        className="question-input"
                                                                        type="textarea"
                                                                        name="Description"
                                                                        value={Description}
                                                                        onChange={this.handleInputChange}
                                                                    />
                                                                    {!DescriptionMsg && <span className="color-red">{VALID_DESCRIPTION}</span>}
                                                                </div>
                                                                <div className="redeem-box clearfix">
                                                                    <Row className="mb-20">
                                                                        <Col md={12}>
                                                                            <label htmlFor="Redeem">Expires On</label>
                                                                            <div className="expire-input float-left datepicker-wdt">
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
                                                                                </label>
                                                                                {ExpireOnMsg &&
                                                                                    <span className="color-red">{PREDICTION_PAST_TIME_ERR}</span>
                                                                                }
                                                                            </div>
                                                                        </Col>
                                                                    </Row>
                                                                    <Row>
                                                                        <Col md={12}>
                                                                            <label htmlFor="Redeem">Source URL</label>
                                                                            <div className="expire-input float-left">
                                                                                <Input
                                                                                    type="url"
                                                                                    name="SourceUrl"
                                                                                    value={SourceUrl}
                                                                                    onChange={this.handleInputChange}
                                                                                />
                                                                                {!SourceUrlMsg &&
                                                                                    <span className="color-red">{SOURCE_URL_MSG}</span>
                                                                                }
                                                                            </div>
                                                                        </Col>
                                                                    </Row>
                                                                    <Row className="mt-4">
                                                                        <Col md={12}>
                                                                            <div className="publish-box float-right">
                                                                                <div onClick={() => this.resetChanges(2)} className="refresh icon-reset"></div>
                                                                                <Button
                                                                                    disabled={formValid == false || PublishPosting}
                                                                                    className="btn-secondary-outline publish-btn"
                                                                                    onClick={this.createPrediction}
                                                                                >Publish</Button>
                                                                            </div>
                                                                        </Col>
                                                                    </Row>
                                                                </div>
                                                            </div>
                                                        </Col>
                                                        <Col md={5}>
                                                            <div className={`img-preview-box prediction-dashboard ${!_.isEmpty(Question) || !_.isEmpty(option1) || ExpireOn ? ' dot-border' : ''}`}>
                                                                <Fragment>
                                                                    {!_.isEmpty(EntryFee) || !_.isEmpty(Winning) || !_.isEmpty(Question) || !_.isEmpty(option1) || ExpireOn ?
                                                                        (Winning.trim().length > 0 || EntryFee.trim().length > 0 || Question.trim().length > 0 || option1.trim().length > 0) ?
                                                                            <div className="question-box">
                                                                                <div className="clearfix">
                                                                                    <div className="ques">{Question}</div>
                                                                                    <div className="ques-action">
                                                                                        <i className="icon-share-fill"></i>
                                                                                    </div>
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
                                                                                    <div className="float-left mr-4">
                                                                                        <div className="poll-info">
                                                                                            {EntryType === '0' ? 'Pool' : 'Win'}
                                                                                            <img src={Images.REWARD_ICON} alt="" />
                                                                                            {EntryType === '0' ? '0' : Winning}
                                                                                        </div>
                                                                                        <div className="pre-timer">
                                                                                            <ul className="prediction-list">
                                                                                                <li className="time">
                                                                                                    {
                                                                                                        ExpireOn ?
                                                                                                            HelperFunction.showCountDown(ExpireOn) ?
                                                                                                                <Countdown date={ExpireOn} />
                                                                                                                : 
                                                                                                                // <Moment className="date-style" date={ExpireOn} format="D MMM -  hh:mm A" />
                                                                                                                <>{HF.getFormatedDateTime(ExpireOn, 'D MMM -  hh:mm A')}</>
                                                                                                            :
                                                                                                                ''
                                                                                                    }

                                                                                                </li>
                                                                                                <li className="predicted">0 Predicted</li>
                                                                                            </ul>
                                                                                        </div>
                                                                                    </div>
                                                                                    {EntryType === '1' && (
                                                                                        <div className="entry-fee-view xfloat-left">
                                                                                            <div className="poll-info">
                                                                                                Entry<img src={Images.REWARD_ICON} alt="" />{EntryFee}
                                                                                            </div>
                                                                                        </div>
                                                                                    )
                                                                                    }
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

                                        <Row className="prediction-dashboard mt-5">
                                            {this.predictUserListModal()}
                                            {Total > 0 ?
                                                _.map(PredictionList, (item, preIdx) => {
                                                    return (
                                                        <Col md={4} key={preIdx}>
                                                            <div className="question-box">
                                                                {
                                                                    item.is_pin == "1" && <i className="icon-pinned-fill pinned-prediction"></i>
                                                                }
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
                                                                    <div className="ques-action">
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
                                                                                    onClick={() => this.editPrediction(item, preIdx)}
                                                                                >
                                                                                    <i className="icon-edit"></i>Edit
                                                                                </DropdownItem>
                                                                            </DropdownMenu>
                                                                        </UncontrolledDropdown>
                                                                        <i onClick={() => this.pausePlayPrediction(item, preIdx)}
                                                                            className={item.status == 3 ? "icon-ic-play" : "icon-pause"}
                                                                        ></i>
                                                                    </div>
                                                                </div>
                                                                <div className="pool-answer">
                                                                    <ul className="pool-list">
                                                                        {
                                                                            _.map(item.options, (pre_options, idx) => {
                                                                                let pColor = this.getBgColor(pre_options.option_total_coins, item.total_pool)
                                                                                return (
                                                                                    <li
                                                                                        style={{
                                                                                            backgroundImage:
                                                                                                "linear-gradient(to right, #E4F9FE " +
                                                                                                pColor + "%, #F2F2F2 0%)"
                                                                                        }}
                                                                                        onClick={() => this.selectPreAnswer(item.prediction_master_id, pre_options.prediction_option_id, preIdx)}
                                                                                        key={idx}
                                                                                        className={`clearfix pool-item ${pre_options.prediction_option_id == activeAnswer ? "active" : ""}`}>
                                                                                        <div className="float-left answer-opt">
                                                                                            {pre_options.option}
                                                                                        </div>
                                                                                        <div className="float-right">
                                                                                            {item.total_pool > "0" ?
                                                                                                ((pre_options.option_total_coins / item.total_pool) * 100).toFixed(2)
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
                                                                    <div className="float-left mr-4">
                                                                        <div className="poll-info">
                                                                            {item.entry_type == '0' ? 'Pool' : 'Win'}
                                                                            <img src={Images.REWARD_ICON} alt="" />
                                                                            {item.entry_type == '0' ? item.total_pool : item.win_prize}
                                                                        </div>

<div className="pre-timer">
<ul className="prediction-list">
<li className="time">
    {
        (HelperFunction.showCountDown(item.deadline_date) && !item.onCompTimer) ?

            <Countdown
                onComplete={() => this.onComplete(preIdx)}
                date={HF.getFormatedDateTime(item.deadline_date)} />
            :
            <Moment className="date-style" date={HF.getFormatedDateTime(item.deadline_date, 'D-MMM-YYYY hh:mm A')} format="D MMM - hh:mm A" />      
    }
</li>
<li className="predicted"
    onClick={() => item.total_user_joined > 0 ? this.toggleUserListModal(item.prediction_master_id, item.desc, item.entry_type) : ''}>{item.total_user_joined} Predicted</li>
</ul>
</div>
                                                                    </div>
                                                                    {item.entry_type == '1' && (
                                                                        <div className="entry-fee-view float-left">
                                                                            <div className="poll-info">
                                                                                Entry<img src={Images.REWARD_ICON} alt="" />{item.entry_fee}
                                                                            </div>
                                                                        </div>
                                                                    )
                                                                    }
                                                                    <div className="float-right">
                                                                        {(activeAnswer != 0 && activeIndex == preIdx) &&
                                                                            <Button onClick={() => this.toggleSubmitAnswerModal(true, item, preIdx)} className="ques-action action-status">ANSWER</Button>
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
                                                        <div className="no-records">{NO_RECORDS}</div>
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
                                                                                                    "linear-gradient(to right, #E4F9FE " + ((pre_options.option_total_coins / item.total_pool) * 100) + "%, #F2F2F2 0%)"
                                                                                            }}

                                                                                            key={idx}
                                                                                            className={`clearfix pool-item ${pre_options.is_correct == "1" ? "right-answer" : ""}`}
                                                                                        >
                                                                                            <div className="float-left">{pre_options.option}</div>
                                                                                            <div className="float-right">
                                                                                                {item.total_pool > "0" ?
                                                                                                    ((pre_options.option_total_coins / item.total_pool) * 100).toFixed(2)
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
                                                                        <div className="float-left xmr-4">
                                                                            <div className="poll-info">
                                                                                {item.entry_type == '0' ? 'Pool' : 'Win'}
                                                                                <img src={Images.REWARD_ICON} alt="" />
                                                                                {item.entry_type == '0' ? item.total_pool : item.win_prize}
                                                                            </div>
                                                                            <div className="pre-timer">
                                                                                <ul className="prediction-list">
                                                                                    <li className="time">
                                                                                        
                                                                                        <Moment className="date-style" date={HF.getFormatedDateTime(item.deadline_date, 'D-MMM-YYYY hh:mm A')} format="D MMM - hh:mm A" /> 
                                                                                    </li>


                                                                                    <li className="predicted" onClick={() => item.total_user_joined > 0 ? this.toggleUserListModal(item.prediction_master_id, item.desc, item.entry_type) : ''}>{item.total_user_joined} Predicted</li>
                                                                                </ul>
                                                                            </div>
                                                                        </div>
                                                                        {item.entry_type == '1' && (
                                                                            <div className="entry-fee-view float-left">
                                                                                <div className="poll-info">
                                                                                    Entry<img src={Images.REWARD_ICON} alt="" />{item.entry_fee}
                                                                                </div>
                                                                            </div>
                                                                        )
                                                                        }
                                                                        {item.status == "2" && <div className="float-right">
                                                                            <a onClick={() => this.toggleSubmitAnswerModal(false, item, idx)} className="a-view-proof">Edit Proof</a>
                                                                        </div>}
                                                                    </div>
                                                                </div>

                                                            </Col>
                                                        )
                                                    })
                                                    :
                                                    <Col md={12}>
                                                        {(Total == 0 && !ListPosting) ?
                                                            <div className="no-records">{NO_RECORDS}</div>
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
