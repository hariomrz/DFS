import React, { Component, Fragment } from 'react';
import { Tooltip, Row, Col, Input, Button } from 'reactstrap';
import _ from 'lodash';
import Select from 'react-select';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import LS from 'local-storage';
import { getNewLeagues, getPlayers, createPickem, getLeagues } from '../../helper/WSCalling';
import moment from 'moment';
import Loader from '../../components/Loader';
import { PKM_SAME_TEAM, SYSTEM_ERROR, PKM_MATCH_LINK, PT_PUBLISH_M_TT, PT_MATCHD_MSG, PT_MAKE_PK_MSG, MATCH_START_D } from "../../helper/Message";
import { _isEmpty } from '../../helper/HelperFunction';
class CreateNewPick extends Component {
    constructor(props) {
        super(props);
        this.state = {
            PERPAGE: NC.ITEMS_PERPAGE_LG,
            CURRENT_PAGE: 1,
            SelectedLeague: '',
            isShowAutoToolTip: false,
            PlayersOptions: [],
            SelectedSport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
            CreatePosting: false,
            FormValid: false,
            isShowLineupTT: false,
            selectDraw: false,
            isShowPublishTT: false,
            PublishPickem: false,
            TodayDate: new Date()
        };
    }

    componentDidMount() {
        this.getAllLeagues()
    }

    getAllLeagues = () => {
        this.setState({ PagePosting: true })
        let { SelectedSport, PERPAGE, CURRENT_PAGE } = this.state
        let params = {
            sports_id: SelectedSport,
            search_text: '',
            limit: PERPAGE,
            current_page: CURRENT_PAGE,
            type: '2',
        }
        let leagueOptions = []
        let obj = {}
        getLeagues(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    LeagueList: Response.data.league_list,
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
                notify.show(SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(SYSTEM_ERROR, 'error', 5000)
        })
    }

    getAllPlayers = (ID) => {
        let params = {
            league_id: ID
        }
        let playersOptions = []
        let obj = {}
        getPlayers(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    PlayersList: Response.data.team_list
                }, () => {
                    _.map(this.state.PlayersList, (item, idx) => {
                        obj = { value: item.team_uid, label: item.team_name }
                        playersOptions.push(obj)
                    })
                    this.setState({
                        PlayersOptions: playersOptions
                    })
                })
            } else {
                notify.show(SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(SYSTEM_ERROR, 'error', 5000)
        })
    }

    AutoToolTipToggle = (flag) => {
        this.setState({ isShowAutoToolTip: !this.state.isShowAutoToolTip });
    }

    handleTypeChange = (value, FieldName) => {
        if (FieldName == "SelectedPlayerA") {
            this.setState({ SelectedLabelA: value.label })
        }
        if (FieldName == "SelectedPlayerB") {
            this.setState({ SelectedLabelB: value.label })
        }

        this.setState({ [FieldName]: value.value }, () => {
            if (FieldName == "SelectedLeague") { this.getAllPlayers(value.value) }
            this.validateLeagueForm(FieldName, value)
        })
    }

    dateCheck = (dateType = '') => {
        let { ExpireOn, LineUpDate, TodayDate  } = this.state        
        if (dateType == 'ExpireOn' && ExpireOn <= TodayDate) {
            notify.show(MATCH_START_D, "error", 5000)
            return false;
        }
        else if (dateType == 'LineUpDate' && LineUpDate <= TodayDate) {
            notify.show(PT_MAKE_PK_MSG, "error", 5000)
            return false;
        } 
        else if (dateType == 'LineUpDate' && ExpireOn <= LineUpDate) {
            notify.show(PT_MATCHD_MSG, "error", 5000)
            return false;
        } 
        else 
        {
            return true;
        }
    }

    handleDateChange = (date, dateType) => {
        this.setState({ [dateType]: date }, () => {
            this.dateCheck(dateType)
            this.validateLeagueForm(dateType, date)
        })
    }

    handleInputChange = (e) => {
        let name = e.target.name
        let value = e.target.value
        this.setState({ [name]: value })
    }

    validateLeagueForm(name, value) {
        let { SelectedLeague, SelectedPlayerA, SelectedPlayerB, ExpireOn } = this.state
        let ValidLeague = SelectedLeague
        let ValidPlayerA = SelectedPlayerA
        let ValidPlayerB = SelectedPlayerB
        let ValidDate = ExpireOn ? moment(ExpireOn).format('YYYY-MM-DD') : ''

        switch (name) {
            case 'LeagueName':
                ValidLeague = (!_.isEmpty(value)) ? true : false;
                this.setState({ LeagueMsg: ValidLeague })
                break;
            case 'SelectedPlayerA':
                ValidPlayerA = (!_.isEmpty(value)) ? true : false;
                this.setState({ PlayerAMsg: ValidLeague })
                break;
            case 'SelectedPlayerB':
                ValidPlayerB = (!_.isEmpty(value)) ? true : false;
                this.setState({ PlayerBMsg: ValidLeague })
                break;
            case 'ExpireOn':
                ValidDate = (!_.isUndefined(value) && !_.isNull(value)) ? true : false;
                this.setState({ DateMsg: ValidLeague })
                break;
            default:
                break;
        }
        this.setState({
            FormValid: ValidLeague && ValidPlayerA && ValidPlayerB && ValidDate
        })
    }

    createNewPickem = () => {
        let { SelectedLabelA, SelectedLabelB, MatchLink, SelectedLeague, SelectedPlayerA, SelectedPlayerB, ExpireOn, LineUpDate, selectDraw, PublishPickem } = this.state
        if (SelectedPlayerA === SelectedPlayerB) {
            notify.show(PKM_SAME_TEAM, 'error', 5000)
            return false;
        }
        if (!_.isEmpty(MatchLink)) {
            if (!MatchLink.match(/^^https?:\/\/(.*)?$/gm)) {
                notify.show(PKM_MATCH_LINK, 'error', 5000)
                return false;
            }
        }

        if (!_isEmpty(LineUpDate) && !this.dateCheck('LineUpDate')) {
            return false;
        }


        this.setState({ CreatePosting: true })
        let params = {
            league_id: SelectedLeague,
            feed_type: 1,
            pickem_name: SelectedLabelA + " vs " + SelectedLabelB,
            // season_scheduled_date: moment.utc(ExpireOn).format(),
            season_scheduled_date: moment.utc(ExpireOn).format("YYYY-MM-DD HH:mm:ss"),
            home_uid: SelectedPlayerA,
            away_uid: SelectedPlayerB,
            entry_fee: 0,
            winning: 2,
            match_link: MatchLink ? MatchLink : '',
            // pick_enable_time: moment.utc(LineUpDate).format(),
            pick_enable_time: moment.utc(LineUpDate).format("YYYY-MM-DD HH:mm:ss"),
            allow_draw: selectDraw ? "1" : "0",
            pickem_publish: PublishPickem ? "1" : "0",
        }

        createPickem(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, 'success', 5000)
                this.setState({
                    CreatePosting: false,
                    SelectedLeague: '',
                    SelectedPlayerA: '',
                    SelectedPlayerB: '',
                    MatchLink: '',
                    ExpireOn: '',
                    LineUpDate: '',
                    TodayDate: new Date(),
                    PlayersOptions: [],
                    selectDraw: false,
                    PublishPickem: false,
                })
            } else {
                notify.show(SYSTEM_ERROR, 'error', 5000)
                this.setState({ CreatePosting: false })
            }
        }).catch(error => {
            notify.show(SYSTEM_ERROR, 'error', 5000)
            this.setState({ CreatePosting: false })
        })
    }

    LineupToolTipToggle = (flag) => {
        this.setState({ isShowLineupTT: !this.state.isShowLineupTT });
    }

    handleDrawCheck = (e) => {
        if (e) {
            this.setState({
                selectDraw: this.state.selectDraw ? false : true
            })
        }
    }

    pubttToggle = () => {
        this.setState({ isShowPublishTT: !this.state.isShowPublishTT });
    }

    handlePublishCheck = (e) => {
        if (e) {
            this.setState({
                PublishPickem: this.state.PublishPickem ? false : true
            })
        }
    }

    render() {
        let { FormValid, MatchLink, CreatePosting, SelectedPlayerA, SelectedPlayerB, PlayersOptions, SelectedLeague, isShowAutoToolTip, LeagueOptions, isShowLineupTT, selectDraw, PublishPickem, isShowPublishTT } = this.state
        return (
            <Fragment>
                <div className="create-pick">
                    <Row className="mb-30">
                        <Col md={12}>
                            <div onClick={() => this.props.history.goBack()} className='back-btn'> {"< Back"}</div>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <div className="heading-row">
                                Create Match
                            <i className="ml-2 icon-info-border cursor-pointer" id="AutoTooltip"></i>
                                <Tooltip
                                    placement="right"
                                    isOpen={isShowAutoToolTip}
                                    target="AutoTooltip"
                                    toggle={() => this.AutoToolTipToggle(1)}
                                >Create Match</Tooltip>

                            </div>
                            <div className="bg-design-box clearfix">
                                <div>
                                    <label>League</label>
                                    <Select
                                        isSearchable={true}
                                        className="xform-control"
                                        options={LeagueOptions}
                                        placeholder="Select your Leauge/s"
                                        menuIsOpen={true}
                                        value={SelectedLeague}
                                        onChange={e => this.handleTypeChange(e, 'SelectedLeague')}
                                    />
                                </div>
                            </div>
                        </Col>
                    </Row>

                    <div className="bg-design-box clearfix">
                        <Row>
                            <Col md={12}>
                                <div className="input-container">
                                    <div className="mb-30 mr-5 float-left">
                                        <label>Team/Player (A)</label>
                                        <Select
                                            isSearchable={true}
                                            className="xform-control"
                                            options={PlayersOptions}
                                            placeholder="Select your Team/Player"
                                            menuIsOpen={true}
                                            value={SelectedPlayerA}
                                            onChange={e => this.handleTypeChange(e, 'SelectedPlayerA')}
                                        />
                                    </div>
                                    <div className="float-left mb-30 mr-5">
                                        <label>Team/Player (B)</label>
                                        <Select
                                            isSearchable={true}
                                            className="xform-control"
                                            options={PlayersOptions}
                                            placeholder="Select your Team/Player"
                                            value={SelectedPlayerB}
                                            onChange={e => this.handleTypeChange(e, 'SelectedPlayerB')}
                                        />
                                    </div>
                                </div>
                            </Col>
                        </Row>
                        <Row>
                            <Col md={12}>
                                <div className="input-container">
                                    <div className="mr-box">
                                        <div className="datePicker-box">
                                            <label>Match Start Date & Time</label>
                                            <label className="mb-0">
                                                <DatePicker
                                                    minDate={new Date()}
                                                    className="form-control"
                                                    showYearDropdown='true'
                                                    onChange={e => this.handleDateChange(e, "ExpireOn")}
                                                    selected={this.state.ExpireOn}
                                                    placeholderText="Start Date"
                                                    showTimeSelect
                                                    timeFormat="HH:mm"
                                                    timeIntervals={10}
                                                    timeCaption="time"
                                                    dateFormat="dd/MM/yyyy h:mm aa"
                                                />
                                                <i className="icon-calender"></i>
                                            </label>
                                        </div>
                                    </div>
                                    <div className="mr-box">
                                        <div className="datePicker-box">
                                            <label>
                                                Make picks Date & Time
                                                <i className="ml-2 icon-info-border cursor-pointer"
                                                    id="LineupTT"></i>
                                                <Tooltip
                                                    placement="right"
                                                    isOpen={isShowLineupTT}
                                                    target="LineupTT"
                                                    toggle={() => this.LineupToolTipToggle(1)}
                                                >This will be date and time to allow users make picks on the match.</Tooltip>
                                            </label>
                                            <label className="mb-0">
                                                <DatePicker
                                                    minDate={new Date()}
                                                    className="form-control"
                                                    showYearDropdown='true'
                                                    onChange={e => this.handleDateChange(e, "LineUpDate")}
                                                    selected={this.state.LineUpDate}
                                                    placeholderText="Lineup creation date"
                                                    showTimeSelect
                                                    timeFormat="HH:mm"
                                                    timeIntervals={10}
                                                    timeCaption="time"
                                                    dateFormat="dd/MM/yyyy h:mm aa"
                                                />
                                                <i className="icon-calender"></i>
                                            </label>
                                        </div>
                                    </div>
                                    <div className="mr-box">
                                        <label>Enter link for match details
                                            <sub className="optional">
                                                Optional
                                            </sub></label>
                                        <Input
                                            type="url"
                                            name="MatchLink"
                                            value={MatchLink}
                                            onChange={(e) => this.handleInputChange(e)}
                                        />
                                    </div>

                                    <div className="float-left pt-sel-draw">
                                        <div className="common-cus-checkbox">
                                            <label className="com-chekbox-container">
                                                <span className="opt-text">Draw Option</span>
                                                <input
                                                    type="checkbox"
                                                    name="selectDraw"

                                                    // defaultChecked={false}
                                                    checked={selectDraw}

                                                    onChange={(e) => this.handleDrawCheck(e)}
                                                />
                                                <span className="com-chekbox-checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div className="float-left pt-sel-draw">
                                        <div className="common-cus-checkbox">
                                            <label className="com-chekbox-container">
                                                <span className="opt-text">Publish Pickem</span>
                                                <input
                                                    type="checkbox"
                                                    name="PublishPickem"
                                                    checked={PublishPickem}
                                                    onChange={(e) => this.handlePublishCheck(e)}
                                                />
                                                <span className="com-chekbox-checkmark"></span>
                                                <span className="pem-tt">
                                                    <i className="ml-2 icon-info-border cursor-pointer" id="pem-pub-tt"></i>
                                                    <Tooltip
                                                        placement="right"
                                                        isOpen={isShowPublishTT}
                                                        target="pem-pub-tt"
                                                        toggle={() => this.pubttToggle(1)}
                                                    >{PT_PUBLISH_M_TT}</Tooltip>
                                                </span>
                                            </label>
                                        </div>
                                    </div>

                                </div>
                            </Col>
                        </Row>
                    </div>

                    <div md={12} className="createpick-btn-box">
                        {!CreatePosting ?
                            <Button
                                disabled={!FormValid}
                                className="btn-secondary-outline"
                                onClick={this.createNewPickem}
                            >Create Match</Button>
                            :
                            <Loader hide />}
                    </div>
                </div >
            </Fragment >
        )
    }
}
export default CreateNewPick;