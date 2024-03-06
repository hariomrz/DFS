import React, { Component, Fragment } from "react";
import Images from '../../components/images';
import { Row, Col, TabContent, TabPane, Nav, NavItem, NavLink, UncontrolledDropdown, DropdownToggle, DropdownMenu, DropdownItem, Modal, ModalBody, ModalHeader, Table } from 'reactstrap';
import Pagination from "react-js-pagination";
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Loader from '../../components/Loader';
import ActionRequestModal from '../../components/ActionRequestModal/ActionRequestModal';
import ReadMoreAndLess from 'react-read-more-less';
import PredectionGraph from './PredictionGraph';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import { MomentDateComponent } from "../../components/CustomComponent";
import { MSG_DELETE_PREDICTION } from "../../helper/Message";
import HF from '../../helper/HelperFunction';
import moment from "moment-timezone";

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
                    {item.question_count}{' '}Questions</div>
            </div>
        </div>
    )
};

// Important! add unique key
export const Menu = (list, selected) =>
    list.map(el => {
        return <MenuItem item={el} key={el.category_id} selected={selected} />;
    });

class PredictionDashboard extends Component {
    constructor(props) {
        super(props)
        this.state = {
            activeTab: '1',
            activeTrendingTab: '1',
            ONE_BID_COUNT: 0,
            NO_BID_COUNT: 0,
            PredictionList: [],
            ListPosting: false,
            SelectedSports: "7",
            LIST_CURRENT_PAGE: 1,
            PERPAGE: NC.ITEMS_PERPAGE,
            FromDate: HF.getFirstDateOfMonth(),
            ToDate: new Date(moment().format('D MMM YYYY')),
            RECENT_CURRENT_PAGE: 1,
            ONE_CURRENT_PAGE: 1,
            POPULAR_CURRENT_PAGE: 1,
            NO_BID_CURRENT_PAGE: 1,
            DateChange: true
        }
    }

    componentDidMount() {
        this.getLiveFixtures()
        this.getTrendingPredictions()
        this.getBidCounts()
    }

    onSelect = key => {
        this.setState({ CATEGORY_ID: key }, () => {
            alert("CATEGORY_ID==", this.state.CATEGORY_ID)
        });
    }

    getLiveFixtures = () => {
        let { SelectedFixture } = this.state
        let params = {
            items_perpage: 1000,
            current_page: 1,
            status: "1" //live, Upcoming          
        }
        let fixture = SelectedFixture
        WSManager.Rest(NC.baseURL + NC.OP_GET_CATEGORY_LIST_BY_STATUS, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    FixtureList: Response.data.category_list,
                }, () => {
                    this.menuItems = Menu(this.state.FixtureList, "1");
                })

            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    getBidCounts = () => {
        let param = {
            sports_id: this.state.SelectedSports
        }
        WSManager.Rest(NC.baseURL + NC.OP_GET_PREDICTION_COUNTS, param).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    ONE_BID_COUNT: ResponseJson.data.one_bid_count,
                    NO_BID_COUNT: ResponseJson.data.no_bid_count,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    getTrendingPredictions = () => {
        this.setState({ ListPosting: true })
        let { activeTrendingTab, SelectedSports, RECENT_CURRENT_PAGE, POPULAR_CURRENT_PAGE, ONE_CURRENT_PAGE, NO_BID_CURRENT_PAGE, PERPAGE } = this.state
        let param = {
            tab_no: activeTrendingTab,
            items_perpage: PERPAGE
        }
        if (activeTrendingTab == 1)
            param.current_page = RECENT_CURRENT_PAGE
        if (activeTrendingTab == 2)
            param.current_page = POPULAR_CURRENT_PAGE
        if (activeTrendingTab == 3)
            param.current_page = ONE_CURRENT_PAGE
        if (activeTrendingTab == 4)
            param.current_page = NO_BID_CURRENT_PAGE

        WSManager.Rest(NC.baseURL + NC.OP_GET_TRENDING_PREDICTIONS, param).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    PredictionList: ResponseJson.data.result,
                    TotalPrediction: ResponseJson.data.total,
                    ListPosting: false
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    toggle(tab) {
        this.setState({ activeTab: tab })
    }

    toggleTrending(tab) {
        this.setState({ activeTrendingTab: tab }, this.getTrendingPredictions)
    }

    resetChanges = () => {
        this.setState({ activeTrendingTab: "1" }, this.getTrendingPredictions)
    }

    handleTypeChange = (value) => {
        this.setState({
            SelectedSports: value.value
        }, () => {
            this.getTrendingPredictions()
            this.getBidCounts()
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

    handleParticipantsPageChange(current_page) {
        this.setState({
            LIST_CURRENT_PAGE: current_page
        }, () => {
            this.getPredictionUserList()
        });
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
        let { NO_BID_COUNT, ONE_BID_COUNT, activeTrendingTab, PredictionID, idxVal } = this.state
        let params = {
            prediction_master_id: PredictionID
        }
        let TempPredictionList = this.state.PredictionList
        WSManager.Rest(NC.baseURL + NC.OP_DELETE_PREDICTION, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                _.remove(TempPredictionList, function (item, idx) {
                    return idx == idxVal
                })
                if (activeTrendingTab === '3' && ONE_BID_COUNT >= 1) {
                    let oneBidCount = parseInt(ONE_BID_COUNT) - 1
                    this.setState({ ONE_BID_COUNT: oneBidCount })
                }
                if (activeTrendingTab === '4' && NO_BID_COUNT >= 1) {
                    let noBidCount = parseInt(NO_BID_COUNT) - 1
                    this.setState({ NO_BID_COUNT: noBidCount })
                }
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

    handleDateFilter = (date, dateType) => {
        this.setState({ DateChange: false })
        this.setState({ [dateType]: date }, () => {
            this.setState({ DateChange: true })
        })
    }

    getBgColor = (prediction_count, total_user_joined) => {
        return (prediction_count != "0" && total_user_joined != "0") ? ((prediction_count / total_user_joined) * 100) : "0"
    }

    handlePageChange(current_page, tab_no) {
        if (tab_no == 1) {
            this.setState({
                RECENT_CURRENT_PAGE: current_page
            }, this.getTrendingPredictions);
        }
        if (tab_no == 2) {
            this.setState({
                POPULAR_CURRENT_PAGE: current_page
            }, this.getTrendingPredictions);
        }
        if (tab_no == 3) {
            this.setState({
                ONE_CURRENT_PAGE: current_page
            }, this.getTrendingPredictions);
        }
        if (tab_no == 4) {
            this.setState({
                NO_BID_CURRENT_PAGE: current_page
            }, this.getTrendingPredictions);
        }

    }

    render() {
        let { DateChange, FromDate, ToDate, Message, ActionPopupOpen, ListPosting, TotalPrediction, activeTab, activeTrendingTab, ONE_BID_COUNT, NO_BID_COUNT, PredictionList, PERPAGE, RECENT_CURRENT_PAGE, ONE_CURRENT_PAGE, POPULAR_CURRENT_PAGE, NO_BID_CURRENT_PAGE } = this.state
        const ActionCallback = {
            Message: Message,
            modalCallback: this.toggleActionPopup,
            ActionPopupOpen: ActionPopupOpen,
            modalActioCallback: this.deletePrediction,
        }
        var todaysDate = moment().format('D MMM YYYY');
        const menu = this.menuItems;
        return (
            <Fragment>
                <div className="prediction-dashboard">
                    <ActionRequestModal {...ActionCallback} />
                    <Row>
                        <Col md={12} className="mt-4">
                            <div className="coins-setting-box float-right">
                                <i onClick={() => this.props.history.push('/open-predictor/module')} className="icon-setting pointer"></i>
                            </div>
                        </Col>
                    </Row>
                    <Row className="mt-3">
                        <Col md={6}>
                            <div className="pre-heading">Dashboard</div>
                        </Col>
                        <Col md={6}>
                            {
                                !this.props.FromDashboard &&
                                (<div className="float-right">
                                    <div className="member-box float-left">
                                        <label className="filter-label">Date</label>
                                        <Row>
                                            <Col md={6} className="pr-0">
                                                <DatePicker
                                                    maxDate={new Date()}
                                                    className="filter-date mr-2"
                                                    showYearDropdown='true'
                                                    selected={FromDate}
                                                    onChange={e => this.handleDateFilter(e, "FromDate")}
                                                    placeholderText="From"
                                                    dateFormat='dd/MM/yyyy'
                                                />
                                            </Col>
                                            <Col md={6} className="pl-2">
                                                <DatePicker
                                                    popperPlacement="top-end"
                                                    minDate={FromDate}
                                                    maxDate={new Date(todaysDate)}
                                                    className="filter-date"
                                                    showYearDropdown='true'
                                                    selected={ToDate}
                                                    onChange={e => this.handleDateFilter(e, "ToDate")}
                                                    placeholderText="To"
                                                    dateFormat='dd/MM/yyyy'
                                                />
                                            </Col>
                                        </Row>
                                    </div>
                                </div>)
                            }
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
                                                    Overall
</NavLink>
                                            </NavItem>
                                            <NavItem
                                                className={activeTab === '2' ? "active" : ""}
                                                onClick={() => { this.toggle('2'); }}
                                            >
                                                <NavLink>
                                                    Trending
</NavLink>
                                            </NavItem>
                                        </Nav>
                                    </Col>
                                    <Col md={6}>
                                        <div className="refresh-page" onClick={this.resetChanges}>
                                            <i className="icon-refresh"></i>
                                            <span>Refresh</span>
                                        </div>
                                    </Col>
                                </Row>
                                <TabContent activeTab={activeTab}>
                                    {
                                        (activeTab == '1') &&
                                        <TabPane tabId="1" className="animated fadeIn">

                                            {
                                                DateChange &&
                                                <PredectionGraph FromDate={FromDate} ToDate={ToDate} />
                                            }

                                        </TabPane>
                                    }
                                    {
                                        (activeTab == '2') &&
                                        <TabPane tabId="2" className="animated fadeIn">
                                            <div className="trending-navigation">
                                                <Nav tabs>
                                                    <NavItem
                                                        className={activeTrendingTab === '1' ? "active" : ""}
                                                        onClick={() => { this.toggleTrending('1'); }}
                                                    >
                                                        <NavLink>
                                                            Recently Added
</NavLink>
                                                    </NavItem>
                                                    <NavItem
                                                        className={activeTrendingTab === '2' ? "active" : ""}
                                                        onClick={() => { this.toggleTrending('2'); }}
                                                    >
                                                        <NavLink>
                                                            Popular
</NavLink>
                                                    </NavItem>
                                                    <NavItem
                                                        className={activeTrendingTab === '3' ? "active" : ""}
                                                        onClick={() => { this.toggleTrending('3'); }}
                                                    >
                                                        <NavLink>
                                                            Only 1 Bid ({ONE_BID_COUNT})
</NavLink>
                                                    </NavItem>
                                                    <NavItem
                                                        className={activeTrendingTab === '4' ? "active" : ""}
                                                        onClick={() => { this.toggleTrending('4'); }}
                                                    >
                                                        <NavLink>
                                                            No Bids({NO_BID_COUNT})
</NavLink>
                                                    </NavItem>
                                                </Nav>
                                                <TabContent activeTab={activeTrendingTab}>
                                                    {this.predictUserListModal()}
                                                    {
                                                        (activeTrendingTab == '1') &&
                                                        <TabPane tabId="1" className="animated fadeIn">
                                                            <Row>
                                                                {
                                                                    TotalPrediction > 0 ?
                                                                        _.map(PredictionList, (item, idx) => {
                                                                            return (
                                                                                <Col md={4} key={idx}>
                                                                                    <div className="recently-added-content">
                                                                                        <div className="question-box">
                                                                                            {
                                                                                                item.is_pin == "1" && <i className="icon-pinned-fill pinned-prediction"></i>
                                                                                            }
                                                                                            <div className="clearfix">
                                                                                                <div className="ques"><ReadMoreAndLess
                                                                                                    ref={this.ReadMore}
                                                                                                    charLimit={70}
                                                                                                    readMoreText="Read more"
                                                                                                    readLessText="Read less"
                                                                                                >
                                                                                                    {item.desc}
                                                                                                </ReadMoreAndLess></div>
                                                                                                <div className="ques-action">
                                                                                                    <UncontrolledDropdown direction="left">
                                                                                                        <DropdownToggle tag="i" className="icon-more">
                                                                                                        </DropdownToggle>
                                                                                                        <DropdownMenu>
                                                                                                            <DropdownItem onClick={() => this.updatePinPrediction(item, idx)}>
                                                                                                                <i className="icon-pinned-fill"></i>
                                                                                                                {item.is_pin == "1" ? "Unpin" : "Pin"}
                                                                                                            </DropdownItem>
                                                                                                            <DropdownItem
                                                                                                                onClick={() => this.copyPredictionUrl(item)}
                                                                                                            ><i className="icon-share-fill"></i>Share</DropdownItem>
                                                                                                            <DropdownItem
                                                                                                                onClick={() => this.toggleActionPopup(item.prediction_master_id, idx)}
                                                                                                            ><i className="icon-delete1"></i>Delete</DropdownItem>
                                                                                                        </DropdownMenu>
                                                                                                    </UncontrolledDropdown>
                                                                                                    <i onClick={() => this.pausePlayPrediction(item, idx)}
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
                                                                                                                            "linear-gradient(to right, #E4F9FE " + pColor + "%, #F2F2F2 0%)"
                                                                                                                    }}
                                                                                                                    key={idx} className="clearfix pool-item">
                                                                                                                    <div className="float-left answer-opt">{pre_options.option}</div>
                                                                                                                    <div className="float-right">
                                                                                                                        {pre_options.option_total_coins != "0" || item.total_pool != "0" ?
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
                                                                                                        {item.entry_type === '0' ? 'Pool' : 'Win'}
                                                                                                        <img src={Images.REWARD_ICON} alt="" />
                                                                                                        {item.entry_type === '0' ? item.total_pool : item.win_prize}
                                                                                                    </div>
                                                                                                    <div className="pre-timer">
                                                                                                        <ul className="prediction-list">
                                                                                                            <li className="time">
                                                                                                                {/* <MomentDateComponent data={{ date: item.deadline_date, format: "D MMM - hh:mm A" }} /> */}
                                        {HF.getFormatedDateTime( item.deadline_date, "D MMM - hh:mm A")}

                                                                                                            </li>
                                                                                                            <li
                                                                                                                className="predicted"
                                                                                                                onClick={() => item.total_user_joined > 0 ? this.toggleUserListModal(item.prediction_master_id, item.desc, item.entry_type) : ''}
                                                                                                            >{item.total_user_joined} Predicted</li>
                                                                                                        </ul>
                                                                                                    </div>
                                                                                                </div>
                                                                                                {item.entry_type === '1' && (
                                                                                                    <div className="entry-fee-view float-left">
                                                                                                        <div className="poll-info">
                                                                                                            Entry<img src={Images.REWARD_ICON} alt="" />{item.entry_fee}
                                                                                                        </div>
                                                                                                    </div>
                                                                                                )
                                                                                                }
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </Col>
                                                                            )
                                                                        })
                                                                        :
                                                                        <Col md={12}>
                                                                            {(TotalPrediction == 0 && !ListPosting) ?
                                                                                <div className="no-records">{NC.NO_RECORDS}</div>
                                                                                :
                                                                                <Loader />
                                                                            }
                                                                        </Col>
                                                                }
                                                            </Row>
                                                            {TotalPrediction > PERPAGE && (
                                                                <div className="custom-pagination float-right">
                                                                    <Pagination
                                                                        activePage={RECENT_CURRENT_PAGE}
                                                                        itemsCountPerPage={PERPAGE}
                                                                        totalItemsCount={TotalPrediction}
                                                                        pageRangeDisplayed={5}
                                                                        onChange={e => this.handlePageChange(e, 1)}
                                                                    />
                                                                </div>
                                                            )}
                                                        </TabPane>
                                                    }
                                                    {
                                                        (activeTrendingTab == '2') &&
                                                        <TabPane tabId="2" className="animated fadeIn">
                                                            <Row>
                                                                {
                                                                    TotalPrediction > 0 ?
                                                                        _.map(PredictionList, (item, idx) => {
                                                                            return (
                                                                                <Col md={4} key={idx}>
                                                                                    <div className="recently-added-content">
                                                                                        <div className="question-box">
                                                                                            {
                                                                                                item.is_pin == "1" && <i className="icon-pinned-fill pinned-prediction"></i>
                                                                                            }
                                                                                            <div className="clearfix">
                                                                                                <div className="ques"><ReadMoreAndLess
                                                                                                    ref={this.ReadMore}
                                                                                                    charLimit={90}
                                                                                                    readMoreText="Read more"
                                                                                                    readLessText="Read less"
                                                                                                >
                                                                                                    {item.desc}
                                                                                                </ReadMoreAndLess></div>
                                                                                                <div className="ques-action">
                                                                                                    <UncontrolledDropdown direction="left">
                                                                                                        <DropdownToggle tag="i" className="icon-more">
                                                                                                        </DropdownToggle>
                                                                                                        <DropdownMenu>
                                                                                                            <DropdownItem onClick={() => this.updatePinPrediction(item, idx)}>
                                                                                                                <i className="icon-pinned-fill"></i>
                                                                                                                {item.is_pin == "1" ? "Unpin" : "Pin"}
                                                                                                            </DropdownItem>
                                                                                                            <DropdownItem
                                                                                                                onClick={() => this.copyPredictionUrl(item)}
                                                                                                            ><i className="icon-share-fill"></i>Share</DropdownItem>
                                                                                                            <DropdownItem
                                                                                                                onClick={() => this.toggleActionPopup(item.prediction_master_id, idx)}
                                                                                                            ><i className="icon-delete1"></i>Delete</DropdownItem>
                                                                                                        </DropdownMenu>
                                                                                                    </UncontrolledDropdown>
                                                                                                    <i onClick={() => this.pausePlayPrediction(item, idx)}
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
                                                                                                                            "linear-gradient(to right, #E4F9FE " + pColor + "%, #F2F2F2 0%)"
                                                                                                                    }}
                                                                                                                    key={idx} className="clearfix pool-item">
                                                                                                                    <div className="float-left answer-opt">{pre_options.option}</div>
                                                                                                                    <div className="float-right">
                                                                                                                        {pre_options.option_total_coins != "0" || item.total_pool != "0" ?
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
                                                                                                        {item.entry_type === '0' ? item.total_pool : item.win_prize}
                                                                                                    </div>
                                                                                                    <div className="pre-timer">
                                                                                                        <ul className="prediction-list">
                                                                                                            <li className="time">
                                                                                                                {/* <MomentDateComponent data={{ date: item.deadline_date, format: "D MMM - hh:mm A" }} /> */}
                                                                                                                {HF.getFormatedDateTime( item.deadline_date, "D MMM - hh:mm A")}

                                                                                                            </li>
                                                                                                            <li
                                                                                                                className="predicted"
                                                                                                                onClick={() => item.total_user_joined > 0 ? this.toggleUserListModal(item.prediction_master_id, item.desc, item.entry_type) : ''}
                                                                                                            >{item.total_user_joined} Predicted</li>
                                                                                                        </ul>
                                                                                                    </div>
                                                                                                </div>
                                                                                                {item.entry_type === '1' && (
                                                                                                    <div className="entry-fee-view float-left">
                                                                                                        <div className="poll-info">
                                                                                                            Entry<img src={Images.REWARD_ICON} alt="" />{item.entry_fee}
                                                                                                        </div>
                                                                                                    </div>
                                                                                                )
                                                                                                }
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </Col>
                                                                            )
                                                                        })
                                                                        :
                                                                        <Col md={12}>
                                                                            {(TotalPrediction == 0 && !ListPosting) ?
                                                                                <div className="no-records">{NC.NO_RECORDS}</div>
                                                                                :
                                                                                <Loader />
                                                                            }
                                                                        </Col>
                                                                }

                                                            </Row>
                                                            {TotalPrediction > PERPAGE && (
                                                                <div className="custom-pagination float-right">
                                                                    <Pagination
                                                                        activePage={POPULAR_CURRENT_PAGE}
                                                                        itemsCountPerPage={PERPAGE}
                                                                        totalItemsCount={TotalPrediction}
                                                                        pageRangeDisplayed={5}
                                                                        onChange={e => this.handlePageChange(e, 2)}
                                                                    />
                                                                </div>
                                                            )}
                                                        </TabPane>
                                                    }
                                                    {
                                                        (activeTrendingTab == '3') &&
                                                        <TabPane tabId="3" className="animated fadeIn">
                                                            <Row>
                                                                {
                                                                    TotalPrediction > 0 ?
                                                                        _.map(PredictionList, (item, idx) => {
                                                                            return (
                                                                                <Col md={4} key={idx}>
                                                                                    <div className="recently-added-content">
                                                                                        <div className="question-box">
                                                                                            {
                                                                                                item.is_pin == "1" && <i className="icon-pinned-fill pinned-prediction"></i>
                                                                                            }
                                                                                            <div className="clearfix">
                                                                                                <div className="ques"><ReadMoreAndLess
                                                                                                    ref={this.ReadMore}
                                                                                                    charLimit={90}
                                                                                                    readMoreText="Read more"
                                                                                                    readLessText="Read less"
                                                                                                >
                                                                                                    {item.desc}
                                                                                                </ReadMoreAndLess></div>
                                                                                                <div className="ques-action">
                                                                                                    <UncontrolledDropdown direction="left">
                                                                                                        <DropdownToggle tag="i" className="icon-more">
                                                                                                        </DropdownToggle>
                                                                                                        <DropdownMenu>
                                                                                                            <DropdownItem onClick={() => this.updatePinPrediction(item, idx)}>
                                                                                                                <i className="icon-pinned-fill"></i>
                                                                                                                {item.is_pin == "1" ? "Unpin" : "Pin"}
                                                                                                            </DropdownItem>
                                                                                                            <DropdownItem onClick={() => this.copyPredictionUrl(item)}><i className="icon-share-fill"></i>Share</DropdownItem>
                                                                                                            <DropdownItem
                                                                                                                onClick={() => this.toggleActionPopup(item.prediction_master_id, idx)}
                                                                                                            ><i className="icon-delete1"></i>Delete</DropdownItem>
                                                                                                        </DropdownMenu>
                                                                                                    </UncontrolledDropdown>
                                                                                                    <i onClick={() => this.pausePlayPrediction(item, idx)}
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
                                                                                                                            "linear-gradient(to right, #E4F9FE " + pColor + "%, #F2F2F2 0%)"
                                                                                                                    }}
                                                                                                                    key={idx} className="clearfix pool-item">
                                                                                                                    <div className="float-left answer-opt">{pre_options.option}</div>
                                                                                                                    <div className="float-right">
                                                                                                                        {pre_options.option_total_coins != "0" || item.total_pool != "0" ?
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
                                                                                                        {item.entry_type === '0' ? 'Pool' : 'Win'}
                                                                                                        <img src={Images.REWARD_ICON} alt="" />
                                                                                                        {item.entry_type === '0' ? item.total_pool : item.win_prize}
                                                                                                    </div>
                                                                                                    <div className="pre-timer">
                                                                                                        <ul className="prediction-list">
                                                                                                            <li className="time">
                                                                                                                {/* <MomentDateComponent data={{ date: item.deadline_date, format: "D MMM - hh:mm A" }} /> */}
                                        {HF.getFormatedDateTime( item.deadline_date, "D MMM - hh:mm A")}

                                                                                                            </li>
                                                                                                            <li
                                                                                                                className="predicted"
                                                                                                                onClick={() => item.total_user_joined > 0 ? this.toggleUserListModal(item.prediction_master_id, item.desc, item.entry_type) : ''}
                                                                                                            >{item.total_user_joined} Predicted</li>
                                                                                                        </ul>
                                                                                                    </div>
                                                                                                </div>
                                                                                                {item.entry_type === '1' && (
                                                                                                    <div className="entry-fee-view float-left">
                                                                                                        <div className="poll-info">
                                                                                                            Entry<img src={Images.REWARD_ICON} alt="" />{item.entry_fee}
                                                                                                        </div>
                                                                                                    </div>
                                                                                                )
                                                                                                }
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </Col>
                                                                            )
                                                                        })
                                                                        :
                                                                        <Col md={12}>
                                                                            {(TotalPrediction == 0 && !ListPosting) ?
                                                                                <div className="no-records">{NC.NO_RECORDS}</div>
                                                                                :
                                                                                <Loader />
                                                                            }
                                                                        </Col>
                                                                }

                                                            </Row>
                                                            {TotalPrediction > PERPAGE && (
                                                                <div className="custom-pagination float-right">
                                                                    <Pagination
                                                                        activePage={ONE_CURRENT_PAGE}
                                                                        itemsCountPerPage={PERPAGE}
                                                                        totalItemsCount={TotalPrediction}
                                                                        pageRangeDisplayed={5}
                                                                        onChange={e => this.handlePageChange(e, 3)}
                                                                    />
                                                                </div>
                                                            )}
                                                        </TabPane>
                                                    }
                                                    {
                                                        (activeTrendingTab == '4') &&
                                                        <TabPane tabId="4" className="animated fadeIn">
                                                            <Row>
                                                                {
                                                                    TotalPrediction > 0 ?
                                                                        _.map(PredictionList, (item, idx) => {
                                                                            return (
                                                                                <Col md={4} key={idx}>
                                                                                    <div className="recently-added-content">
                                                                                        <div className="question-box">
                                                                                            {
                                                                                                item.is_pin == "1" && <i className="icon-pinned-fill pinned-prediction"></i>
                                                                                            }
                                                                                            <div className="clearfix">
                                                                                                <div className="ques"><ReadMoreAndLess
                                                                                                    ref={this.ReadMore}
                                                                                                    charLimit={90}
                                                                                                    readMoreText="Read more"
                                                                                                    readLessText="Read less"
                                                                                                >
                                                                                                    {item.desc}
                                                                                                </ReadMoreAndLess></div>
                                                                                                <div className="ques-action">
                                                                                                    <UncontrolledDropdown direction="left">
                                                                                                        <DropdownToggle tag="i" className="icon-more">
                                                                                                        </DropdownToggle>
                                                                                                        <DropdownMenu>
                                                                                                            <DropdownItem onClick={() => this.updatePinPrediction(item, idx)}>
                                                                                                                <i className="icon-pinned-fill"></i>
                                                                                                                {item.is_pin == "1" ? "Unpin" : "Pin"}
                                                                                                            </DropdownItem>
                                                                                                            <DropdownItem onClick={() => this.copyPredictionUrl(item)}><i className="icon-share-fill"></i>Share</DropdownItem>
                                                                                                            <DropdownItem
                                                                                                                onClick={() => this.toggleActionPopup(item.prediction_master_id, idx)}
                                                                                                            ><i className="icon-delete1"></i>Delete</DropdownItem>
                                                                                                        </DropdownMenu>
                                                                                                    </UncontrolledDropdown>
                                                                                                    <i onClick={() => this.pausePlayPrediction(item, idx)}
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
                                                                                                                            "linear-gradient(to right, #E4F9FE " + pColor + "%, #F2F2F2 0%)"
                                                                                                                    }}
                                                                                                                    key={idx} className="clearfix pool-item">
                                                                                                                    <div className="float-left answer-opt">{pre_options.option}</div>
                                                                                                                    <div className="float-right">
                                                                                                                        {pre_options.option_total_coins != "0" || item.total_pool != "0" ?
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
                                                                                                        {item.entry_type === '0' ? 'Pool' : 'Win'}
                                                                                                        <img src={Images.REWARD_ICON} alt="" />
                                                                                                        {item.entry_type === '0' ? item.total_pool : item.win_prize}
                                                                                                    </div>
                                                                                                    <div className="pre-timer">
                                                                                                        <ul className="prediction-list">
                                                                                                            <li className="time">
                                                                                                                {/* <MomentDateComponent data={{ date: item.deadline_date, format: "D MMM - hh:mm A" }} /> */}
                                        {HF.getFormatedDateTime( item.deadline_date, "D MMM - hh:mm A")}

                                                                                                            </li>
                                                                                                            <li
                                                                                                                className="predicted"
                                                                                                                onClick={() => item.total_user_joined > 0 ? this.toggleUserListModal(item.prediction_master_id, item.desc, item.entry_type) : ''}
                                                                                                            >{item.total_user_joined} Predicted</li>
                                                                                                        </ul>
                                                                                                    </div>
                                                                                                </div>
                                                                                                {item.entry_type === '1' && (
                                                                                                    <div className="entry-fee-view float-left">
                                                                                                        <div className="poll-info">
                                                                                                            Entry<img src={Images.REWARD_ICON} alt="" />{item.entry_fee}
                                                                                                        </div>
                                                                                                    </div>
                                                                                                )
                                                                                                }
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </Col>
                                                                            )
                                                                        })
                                                                        :
                                                                        <Col md={12}>
                                                                            {(TotalPrediction == 0 && !ListPosting) ?
                                                                                <div className="no-records">{NC.NO_RECORDS}</div>
                                                                                :
                                                                                <Loader />
                                                                            }
                                                                        </Col>
                                                                }

                                                            </Row>
                                                            {TotalPrediction > PERPAGE && (
                                                                <div className="custom-pagination float-right">
                                                                    <Pagination
                                                                        activePage={NO_BID_CURRENT_PAGE}
                                                                        itemsCountPerPage={PERPAGE}
                                                                        totalItemsCount={TotalPrediction}
                                                                        pageRangeDisplayed={5}
                                                                        onChange={e => this.handlePageChange(e, 4)}
                                                                    />
                                                                </div>
                                                            )}
                                                        </TabPane>
                                                    }
                                                </TabContent>
                                            </div>
                                        </TabPane>
                                    }
                                </TabContent>
                            </div>
                        </Col>
                    </Row>
                </div>
            </Fragment>
        )
    }
}
export default PredictionDashboard