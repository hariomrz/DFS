import React, { Component, Fragment } from "react";
import { Row, Col, Table, Button, Progress, Input } from "reactstrap";
import _ from 'lodash';
import WSManager from '../../helper/WSManager';
import HF from '../../helper/HelperFunction';
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import Images from '../../components/images';
import Select from 'react-select';
import LS from 'local-storage';

import moment from 'moment';
import { getSeasonDetails, getNGContestDetail_SU, getNGContestJoined_SU, getNGSystemUsersForContest_SU, joinNGSystemUsers_SU, getNetworkContestDetails, joinMultiSystemUsers_SU } from "../../helper/WSCalling";
import { MomentDateComponent } from "../../components/CustomComponent";
import { MULTIPLE_BOT } from "../../helper/Message";
import Loader from '../../components/Loader';
class AddNetwGameSysUser extends Component {
    constructor(props) {
        super(props)
        this.state = {
            selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,

            league_id: (this.props.league_id) ? this.props.league_id : this.props.match.params.league_id,

            season_game_uid: (this.props.match.params.season_game_uid) ? this.props.match.params.season_game_uid : '',

            contest_unique_id: (this.props.match.params.contest_unique_id) ? this.props.match.params.contest_unique_id : '',

            isShowAutoToolTip: false,
            MaxSystemUser: 0,
            MaxSystemUserRequest: 0,
            ContestDetail: [],
            SUserList: [],
            AddPlayerList: [],
            ContestJoinedUsers: [],
            fixtureDetail: [],
            PlayerName: '',
            TotalLineupsToJoin: 0,
            TotalJoinedUser: 0,
            submitPosting: false,
            viewSubmitted: false,
            LineupOut: false,
            matchDetail: [],
            multiLineup: false,
            multiLineupCount: 1,
            multiMsg: false,
            multiSubPosting: true,
            BotNumber: '',
            addBot: false,
        }
    }

    componentDidMount = () => {
        this.getContestDetail()
        this.GetFixtureDetail()

        var arr = ['nclient1', 'cricjam', 'predev'];

        if (!_.isUndefined(NC.baseURL)) {
            let baseUrl = NC.baseURL
            // let baseUrl = 'https://scores11.com/'
            let botFlag = HF.containsString(baseUrl, arr)
            if (!botFlag) {
                this.props.history.push('/dashboard');
            }
        }
    }

    AutoToolTipToggle = () => {
        this.setState({ isShowAutoToolTip: !this.state.isShowAutoToolTip });
    }

    getTimeDiff = (dateTime) => {
        let scheduleDate = WSManager.getUtcToLocal(dateTime);
        let currentDate = HF.getFormatedDateTime(Date.now());
        var now = moment(currentDate); //todays date
        var end = moment(scheduleDate); // another date
        var duration = moment.duration(end.diff(now));
        var hours = duration.asHours();
        var minutes = duration.asMinutes();
        // return true;   
        return (minutes <= 0);
    }

    getContestDetail = () => {
        let { contest_unique_id } = this.state
        let params = {
            "contest_unique_id": contest_unique_id
        }

        getNGContestDetail_SU(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                let ResponseData = ResponseJson.data
                var dt = new Date(ResponseData.contest_detail.season_scheduled_date);
                dt.setMinutes(dt.getMinutes() - 20);

                let contestDetails = ResponseData.contest_detail

                // if (contestDetails.playing_eleven_confirm == "0") {
                //     notify.show(NC.PLA_ELE_NOT_CONFIRM, "error", 3000)
                //     this.props.history.push({ pathname: '/contest/fixturecontest/' + league_id + '/' + season_game_uid })
                //     return false
                // }

                if (this.getTimeDiff(dt) || contestDetails.total_system_user === ResponseData.max_system_user || contestDetails.total_user_joined === contestDetails.size) {

                    this.setState({
                        viewSubmitted: true,
                        LineupOut: true
                    })
                }

                this.setState({
                    MaxSystemUser: ResponseData.max_system_user,
                    MaxSystemUserRequest: ResponseData.system_user_request_limit,
                    ContestDetail: contestDetails,
                }, () => {
                    this.getSystemUsersForContest()
                    this.getContestJoinedSysUser()
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }


    GetFixtureDetail = () => {
        let { league_id, selected_sport, season_game_uid, contest_unique_id } = this.state
        let param = {
            "sports_id": selected_sport,
            "contest_unique_id": contest_unique_id,
        }
        this.setState({ posting: true });

        getNetworkContestDetails(param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({
                    posting: false,
                    fixtureDetail: responseJson.data,
                    matchDetail: !_.isUndefined(responseJson.data.match_detail) ? responseJson.data.match_detail : [],
                });
            }
            else if (responseJson.response_code == NC.sessionExpireCode) {
                WSManager.logout();
                this.props.history.push('/login');
            }
        })
    }

    getContestJoinedSysUser = () => {
        let params = {
            "contest_id": this.state.ContestDetail.contest_id
        }

        getNGContestJoined_SU(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                var tJoinedUSer = 0
                _.map(ResponseJson.data, (item) => {
                    tJoinedUSer += parseInt(item.team_count);
                })
                this.setState({
                    ContestJoinedUsers: ResponseJson.data,
                    TotalJoinedUser: tJoinedUSer,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    getSystemUsersForContest = () => {
        this.setState({ ListPosting: true })
        let params = {
            "contest_id": this.state.ContestDetail.contest_id,
            "multiple_lineup": this.state.ContestDetail.multiple_lineup
        }

        getNGSystemUsersForContest_SU(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                let ResponseData = ResponseJson.data
                let TempConUser = []
                let TempConDict = {}
                _.map(ResponseData, (item) => {
                    if (item.user_name != "" && item.user_name != null) {
                        TempConDict = {
                            "label": item.user_name,
                            "value": item.user_id,
                            "team_count": item.team_count,
                        }
                        TempConUser.push(TempConDict)
                    }
                })

                this.setState({
                    SUserList: TempConUser,
                    PlayerName: TempConUser[0].value,
                })

            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    handlePlayerChange = (value, index, userId) => {
        let tempSUserList = this.state.SUserList
        let tempPlaList = this.state.AddPlayerList
        tempPlaList[index].slot_flag = value.team_count
        tempPlaList[index].available_slot = parseInt(this.state.ContestDetail.multiple_lineup) - parseInt(value.team_count ? value.team_count : 0)

        tempPlaList[index].value = value.value
        tempPlaList[index].user_id = value.value
        tempPlaList[index].user_name = value.label
        _.map(tempSUserList, (tempItem, idx) => {
            if (tempItem.value == value.value) {
                tempSUserList[idx].disabled = true
            } else if (tempItem.value == userId) {
                tempSUserList[idx].disabled = false
            }
        })

        this.setState({
            AddPlayerList: tempPlaList,
            SUserList: tempSUserList,
        })
    }

    deletePlayerRow = (removeUserId, removeIndex) => {
        let { TotalLineupsToJoin, AddPlayerList, SUserList } = this.state

        let tempSUserList = SUserList
        _.map(tempSUserList, (item, idx) => {
            if (!_.isEmpty(removeUserId) && (item.value === removeUserId)) {
                tempSUserList[idx].disabled = false
                this.setState({ SUserList: tempSUserList })
            }
        })

        let tempPlaList = AddPlayerList
        let tempTotalToJoin = TotalLineupsToJoin
        _.remove(tempPlaList, (item) => {
            if (((!_.isEmpty(removeUserId) && (item.user_id === removeUserId)) || item.row_id == removeIndex)) {
                let finalTotal = parseInt(tempTotalToJoin) - parseInt(item.team_count)
                this.setState({ TotalLineupsToJoin: finalTotal })
                return true;
            }
        })
        this.setState({ AddPlayerList: tempPlaList })
    }

    addNewPlayerRow = () => {
        let tempPlaArr = this.state.AddPlayerList
        let { MaxSystemUserRequest, TotalLineupsToJoin, MaxSystemUser, TotalJoinedUser } = this.state
        if (TotalLineupsToJoin >= (parseInt(MaxSystemUser) - parseInt(TotalJoinedUser)) || TotalLineupsToJoin >= MaxSystemUserRequest) {
            notify.show("You are allowed to create " + MaxSystemUser + " teams per contest and " + MaxSystemUserRequest + " teams per request.", "error", 3000)
            return false
        }

        let lastRowId = 0

        if (!_.isEmpty(tempPlaArr)) {
            let arrId = parseInt(tempPlaArr.length) - 1
            lastRowId = parseInt(tempPlaArr[arrId].row_id) + 1
        }
        let tempAddPlaRow = {
            "row_id": lastRowId,
            "user_id": "",
            "team_count": 1,
            "user_name": ""
        }
        tempPlaArr.push(tempAddPlaRow)
        this.setState({
            AddPlayerList: tempPlaArr,
            TotalLineupsToJoin: TotalLineupsToJoin + 1
        })
    }

    managePlayerLineup = (index, flag) => {
        let { TotalLineupsToJoin, AddPlayerList } = this.state
        let tempPlaList = AddPlayerList
        let PlaTeamCount = tempPlaList[index].team_count ? tempPlaList[index].team_count : 0
        let TempTotLineToJoin = TotalLineupsToJoin
        if (flag) {
            tempPlaList[index].team_count = PlaTeamCount + 1
            TempTotLineToJoin = TempTotLineToJoin + 1
        }
        else {
            if (PlaTeamCount > 1) {
                tempPlaList[index].team_count = PlaTeamCount - 1
                TempTotLineToJoin = TempTotLineToJoin - 1
            }
        }
        this.setState({
            AddPlayerList: tempPlaList,
            TotalLineupsToJoin: TempTotLineToJoin,
        })
    }

    joinLineupSysUsers = () => {
        this.setState({ submitPosting: true })
        let { AddPlayerList, ContestDetail, SUserList, TotalLineupsToJoin } = this.state
        let inputValid = false

        let joinedPlayer = 0
        _.map(AddPlayerList, (item) => {
            joinedPlayer += parseInt(item.team_count);
            if (item.user_id == "" || item.user_name == "") {
                inputValid = true
            }
        })
        if (inputValid) {
            this.setState({ submitPosting: false })
            notify.show("Please select user name for all lineup", "error", 3000)
            return false
        }

        let params = {
            "contest_id": this.state.ContestDetail.contest_id,
            "user_list": AddPlayerList
        }

        joinNGSystemUsers_SU(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                ContestDetail.total_user_joined = parseInt(ContestDetail.total_user_joined) + parseInt(joinedPlayer)
                ContestDetail.total_system_user = parseInt(ContestDetail.total_system_user) + parseInt(joinedPlayer)
                notify.show(ResponseJson.message, "success", 3000)

                if (TotalLineupsToJoin == 100) {
                    this.setState({ LineupOut: true })
                }

                this.setState({ AddPlayerList: [], ContestDetail: ContestDetail, TotalLineupsToJoin: 0 })
                this.showSubmittedSU()
                if (ContestDetail.size == this.state.ContestDetail.total_user_joined) {
                    this.setState({ LineupOut: true })
                }
                let tempSUserList = SUserList
                _.map(tempSUserList, (item, idx) => {
                    if (tempSUserList[idx].disabled = true) {
                        tempSUserList[idx].disabled = false
                        this.setState({ SUserList: tempSUserList })
                    }
                })

            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
            this.setState({ submitPosting: false })
        }).catch(error => {
            this.setState({ submitPosting: false })
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    showSubmittedSU = () => {
        this.setState({
            viewSubmitted: !this.state.viewSubmitted,
            multiLineup: false,
            multiLineupCount: 1,
            BotNumber: '',
            multiSubPosting: true,
        }, () => {
            this.getContestJoinedSysUser()
        })
    }

    ShowProgressBar = (join, total) => {
        return join * 100 / total;
    }

    getPrizeAmount = (prize_data) => {
        let prize_text = "Prizes";
        let is_tie_breaker = 0;
        let prizeAmount = { 'real': 0, 'bonus': 0, 'point': 0 };
        if (!_.isNull(prize_data) && !_.isUndefined(prize_data)) {
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
            prize_text = '<i class="icon-rupess"></i>' + parseFloat(prizeAmount.real).toFixed(2);
        } else if (is_tie_breaker == 0 && prizeAmount.bonus > 0) {
            prize_text = '<i class="icon-bonus"></i>' + parseFloat(prizeAmount.bonus).toFixed(2);
        } else if (is_tie_breaker == 0 && prizeAmount.point > 0) {
            prize_text = '<img src="' + Images.COINIMG + '" alt="coin-img" />' + parseFloat(prizeAmount.point).toFixed(2);
        }
        return { __html: prize_text };
    }

    addMultipleLineup = () => {
        this.setState({ multiLineup: !this.state.multiLineup }, () => {
            if (this.state.multiLineup) {
                this.setState({ AddPlayerList: [], TotalLineupsToJoin: 0, multiLineupCount: 1, multiMsg: false })
            }
            else if (!this.state.multiLineup) {
                this.setState({
                    multiLineupCount: 1,
                    BotNumber: '',
                    multiSubPosting: true,
                    TotalLineupsToJoin: 0,
                }, this.getSystemUsersForContest)
            }
        })
    }
    handleInputChange = (e) => {
        let name = e.target.name;
        let value = e.target.value;
        if (HF.isFloat(value)) {
            value = this.state.BotNumber
        }
        // let t_lineup = this.getMultipleTeamCount(value, this.state.multiLineupCount)
        this.setState({
            [name]: value,
            multiMsg: false,
            multiSubPosting: false,
            // TotalLineupsToJoin: t_lineup,
            TotalLineupsToJoin: value,
        }, () => {
            if (this.state.BotNumber <= 0 || this.state.BotNumber > 100) {
                this.setState({
                    BotNumber: '',
                    multiSubPosting: true,
                    multiMsg: true,
                    TotalLineupsToJoin: 0,
                })
            } else {
                this.setState({ multiSubmit: false })
            }
        });
    }
    multiPlayerLineup = (flag) => {
        let { TotalLineupsToJoin, multiLineupCount, BotNumber } = this.state
        let mt_count = multiLineupCount
        let PlaTeamCount = multiLineupCount ? multiLineupCount : 0
        let TempTotLineToJoin = TotalLineupsToJoin
        if (flag) {
            mt_count = PlaTeamCount + 1
            TempTotLineToJoin = this.getMultipleTeamCount(BotNumber, mt_count)
        }
        else {
            if (PlaTeamCount > 1) {
                mt_count = PlaTeamCount - 1
                TempTotLineToJoin = this.getMultipleTeamCount(BotNumber, mt_count)
            }
        }

        this.setState({
            multiLineupCount: mt_count,
            // TotalLineupsToJoin: TempTotLineToJoin,
        })

    }
    joinMultiLineup = () => {
        this.setState({ multiSubPosting: true, addBot: true })
        let { ContestDetail, SUserList, TotalLineupsToJoin, BotNumber, multiLineupCount } = this.state
        let params = {
            "contest_id": this.state.ContestDetail.contest_id,
            "no_of_bots": BotNumber,
            "team_count": multiLineupCount,
        }
        joinMultiSystemUsers_SU(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                ContestDetail.total_user_joined = parseInt(ContestDetail.total_user_joined) + parseInt(TotalLineupsToJoin)
                ContestDetail.total_system_user = parseInt(ContestDetail.total_system_user) + parseInt(TotalLineupsToJoin)
                notify.show(ResponseJson.message, "success", 3000)
                if (TotalLineupsToJoin == 100) {
                    this.setState({ LineupOut: true })
                }
                this.setState({ AddPlayerList: [], ContestDetail: ContestDetail, TotalLineupsToJoin: 0, BotNumber: '', multiLineupCount: 1 })
                this.showSubmittedSU()
                if (ContestDetail.size == this.state.ContestDetail.total_user_joined) {
                    this.setState({ LineupOut: true })
                }
                let tempSUserList = SUserList
                _.map(tempSUserList, (item, idx) => {
                    if (tempSUserList[idx].disabled = true) {
                        tempSUserList[idx].disabled = false
                        this.setState({ SUserList: tempSUserList })
                    }
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
            this.setState({ 
                multiSubPosting: false, 
                addBot: false 
            })
        }).catch(error => {
            this.setState({ multiSubPosting: false })
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }
    getMultipleTeamCount = (bot_num, lineup_count) => {
        if (bot_num && lineup_count)
            return bot_num * lineup_count
        else
            return lineup_count
    }

    render() {
        let { MaxSystemUserRequest, MaxSystemUser, ContestDetail, PlayerName, SUserList, AddPlayerList, TotalLineupsToJoin, submitPosting, viewSubmitted, ContestJoinedUsers, fixtureDetail, TotalJoinedUser, league_id, season_game_uid, LineupOut, matchDetail, multiLineup, BotNumber, multiLineupCount, multiMsg, multiSubPosting, addBot } = this.state
        return (
            <React.Fragment>
                <div className="add-system-user">
                    <Row>
                        <Col md={10}>
                            <div className="common-fixture float-left">
                                {/* <img src={NC.S3 + NC.FLAG + matchDetail.home_flag} className="com-fixture-flag float-left" alt="" />
                                <img src={NC.S3 + NC.FLAG + matchDetail.away_flag} className="com-fixture-flag float-right" alt="" /> */}
                                <div className="com-fixture-container">
                                    <div className="com-fixture-name">{(matchDetail.home) ? matchDetail.home : 'TBA'} VS {(matchDetail.away) ? matchDetail.away : 'TBA'}</div>

                                    <div className="com-fixture-time">
                                        {/* <MomentDateComponent data={{ date: matchDetail.season_scheduled_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                                        {HF.getFormatedDateTime(matchDetail.season_scheduled_date, "D-MMM-YYYY hh:mm A")}

                                    </div> 
                                    {/* <div className="com-fixture-title">{fixtureDetail.league_abbr}</div> */}
                                </div>
                            </div>
                            <div className="common-contest float-left">
                                <div className="action-head clearfix">
                                    <div
                                        className="contest-name text-ellipsis"
                                        onClick={() => this.props.history.push('/finance/contest_detail/' + ContestDetail.contest_unique_id)}
                                    >{ContestDetail.contest_name}</div>
                                    <ul className="con-action-list">
                                        {
                                            ContestDetail.guaranteed_prize == '2' &&
                                            <li className="action-item">
                                                <i className="icon-icon-g"></i>
                                            </li>
                                        }
                                        {
                                            ContestDetail.multiple_lineup > 1 &&
                                            <li className="action-item">
                                                <i className="icon-icon-m"></i>
                                            </li>
                                        }
                                        {
                                            ContestDetail.is_auto_recurring == "1" &&
                                            <li className="action-item">
                                                <i title="Recurrence" className="icon-icon-r" ></i>
                                            </li>
                                        }
                                    </ul>
                                </div>
                                <div className="com-contest-name">Win {' '}
                                    <span className="prize-pool-value" dangerouslySetInnerHTML={this.getPrizeAmount(ContestDetail.prize_distibution_detail)}>
                                    </span>
                                </div>
                                <div className="com-contest-subtitle">{ContestDetail.max_bonus_allowed}% bonus</div>
                                <Row>
                                    <Col md={8} className="pr-0">

                                        <Progress className="com-contest-mul-progress" multi>

                                            <Progress bar className="su-progress" value={this.ShowProgressBar(ContestDetail.total_system_user, ContestDetail.size)} >
                                                <span className="su-count">System user {ContestDetail.total_system_user}</span>
                                            </Progress>

                                            <Progress bar className="com-contest-progress all-u-progress" value={this.ShowProgressBar(parseInt(ContestDetail.total_user_joined) - parseInt(ContestDetail.total_system_user), ContestDetail.size)} >
                                                <span className="total-u-count">Total user {ContestDetail.total_user_joined}</span>
                                            </Progress>
                                        </Progress>
                                        <div className="com-contest-entries"><span>{ContestDetail.total_user_joined}</span> / {ContestDetail.size} Entries <abbr className="min-entry float-right">min {ContestDetail.minimum_size}</abbr></div>
                                    </Col>
                                    <Col md={4}>
                                        <div className="con-contest-fee">
                                            {
                                                ContestDetail.currency_type == '0' &&
                                                <span><i className="icon-bonus"></i>{ContestDetail.entry_fee}</span>
                                            }
                                            {
                                                ContestDetail.currency_type == '1' &&
                                                <span>{HF.getCurrencyCode()}{ContestDetail.entry_fee}</span>
                                            }
                                            {
                                                ContestDetail.currency_type == '2' &&
                                                <span><img src={Images.COINIMG} alt="coin-img" />{ContestDetail.entry_fee}</span>
                                            }
                                        </div>
                                    </Col>
                                </Row>
                            </div>
                        </Col>
                        <Col md={2}>
                            <div
                                onClick={() => this.props.history.push({ pathname: '/network-game/' })}
                                className="go-back float-right text-body">{'<'} Back </div>
                        </Col>
                    </Row>
                    <Row>
                        <div>
                            {
                                !viewSubmitted ? (
                                    <div className="linup-info-text xmx-text-wd">
                                        {!multiLineup && 'You are allowed to create '+ MaxSystemUser + ' teams per contest and ' + MaxSystemUserRequest + ' teams per request.'}
                                        {multiLineup && 'You are allowed to create 100 system users at a time.'}
                                    </div>
                                ) : fixtureDetail.playing_announce == "1" ? (
                                    <div className="linup-info-text p11-text-wd"><i className="icon-info"></i>Playing 11 Settled</div>
                                )
                                        :
                                        ""
                            }

                        </div>
                    </Row>
                    <Row>
                        <Col md={6}>
                            <div className="float-left">
                                <h3 className="h3-cls">
                                    {
                                        !viewSubmitted ?
                                            'Total lineups to join : ' + TotalLineupsToJoin
                                            :
                                            'Total lineups joined : ' + TotalJoinedUser
                                    }
                                </h3>


                            </div>
                        </Col>
                        <Col md={6}>
                            {
                                !viewSubmitted ?
                                    (TotalJoinedUser > 0) ?
                                        (
                                            <a onClick={this.showSubmittedSU}
                                                className="view-submitted-su ml-4">View Lineup(s)</a>
                                        )
                                        :
                                        ''
                                    : !LineupOut ? (
                                        <Button
                                            onClick={this.showSubmittedSU}
                                            className="add-more-su ml-4"><i className="icon-plus"></i> Add More</Button>
                                    )
                                        :
                                        ""
                            }
                            {
                                !viewSubmitted &&
                                <a onClick={this.addMultipleLineup}
                                    className={`view-submitted-su font-weight-bold ${(viewSubmitted && !LineupOut) ? 'mt-2' : ''}`}>Add {multiLineup ? 'Individual' : 'Multiple'} bots</a>
                            }

                        </Col>
                    </Row>
                    {
                        !multiLineup &&
                        <Fragment>
                            {
                                !viewSubmitted ? (
                                    <Fragment>
                                        <Row>
                                            <Col md={12} className="table-responsive common-table mt-3">
                                                <Table>
                                                    <thead>
                                                        <tr>
                                                            <th className="left-th pl-3">System User Name</th>
                                                            <th className="text-center pr-28">Lineup</th>
                                                            <th className="text-center pr-28">Slots available</th>
                                                            {
                                                                !viewSubmitted &&
                                                                <th className="right-th">Action</th>
                                                            }
                                                        </tr>
                                                    </thead>
                                                    {
                                                        AddPlayerList.length > 0 ?
                                                            _.map(AddPlayerList, (item, idx) => {
                                                                let avSlot = parseInt(ContestDetail.multiple_lineup) - parseInt(item.slot_flag ? item.slot_flag : 0)
                                                                return (
                                                                    <tbody key={idx}>
                                                                        <tr>
                                                                            <td className="pl-3 select-wtd">
                                                                                <Select
                                                                                    menuIsOpen={true}
                                                                                    isSearchable={true}
                                                                                    options={SUserList}
                                                                                    value={item.value}
                                                                                    placeholder="Select user name"
                                                                                    onChange={e => this.handlePlayerChange(e, idx, item.value)}
                                                                                    isOptionDisabled={(SUserList) => SUserList.disabled === true}
                                                                                />
                                                                            </td>
                                                                            <td className="text-center">
                                                                                <div className="lineup-control">
                                                                                    <span
                                                                                        onClick={() => this.managePlayerLineup(idx, false)}
                                                                                        className="linup-action">
                                                                                        <i className="icon-minus"></i>
                                                                                    </span>
                                                                                    <span className="linup-count">{item.team_count}</span>
                                                                                    <span
                                                                                        onClick={() =>
                                                                                            (item.team_count >= avSlot)
                                                                                                ?
                                                                                                notify.show("Maximum " + ContestDetail.multiple_lineup + " lineup allowed per player", "error", 3000)
                                                                                                :
                                                                                                (TotalLineupsToJoin >= (parseInt(MaxSystemUser) - parseInt(TotalJoinedUser)) || TotalLineupsToJoin >= MaxSystemUserRequest)
                                                                                                    ?
                                                                                                    notify.show("You are allowed to create " + MaxSystemUser + " teams per contest and " + MaxSystemUserRequest + " teams per request.", "error", 3000)
                                                                                                    :
                                                                                                    this.managePlayerLineup(idx, true)}
                                                                                        className="linup-action">
                                                                                        <i className="icon-plus"></i>
                                                                                    </span>
                                                                                </div>
                                                                            </td>
                                                                            <td className="text-center">{parseInt(item.available_slot ? item.available_slot : ContestDetail.multiple_lineup)}</td>
                                                                            <td><i
                                                                                onClick={() => this.deletePlayerRow(item.user_id, item.row_id)}
                                                                                className="icon-delete"></i></td>
                                                                        </tr>
                                                                    </tbody>
                                                                )
                                                            })
                                                            :
                                                            <tbody>
                                                                <tr>
                                                                    <td colSpan="8">
                                                                        <div className="no-records">
                                                                            {
                                                                                ContestDetail.size == ContestDetail.total_user_joined
                                                                                    ?
                                                                                    "Contest already full"
                                                                                    :
                                                                                    NC.NO_RECORDS
                                                                            }

                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                    }
                                                </Table>
                                            </Col>
                                        </Row>
                                        {
                                            ContestDetail.size != ContestDetail.total_user_joined &&
                                            <Row className="addmore-container">
                                                <Col>
                                                    <Button
                                                        onClick={this.addNewPlayerRow}
                                                        className="add-more-su"><i className="icon-plus"></i> Add More</Button>
                                                </Col>
                                            </Row>
                                        }
                                        {
                                            AddPlayerList.length > 0 &&
                                            (<Row className="submit-container">
                                                <Col>
                                                    <Button
                                                        onClick={this.joinLineupSysUsers}
                                                        disabled={submitPosting}
                                                        className="btn-secondary-outline">Submit</Button>
                                                </Col>
                                            </Row>)
                                        }
                                    </Fragment>
                                ) : (
                                        <Row>
                                            <Col md={12} className="table-responsive common-table mt-3">
                                                <Table>
                                                    <thead>
                                                        <tr>
                                                            <th className="left-th pl-3">System User Name</th>
                                                            <th>Lineup</th>
                                                        </tr>
                                                    </thead>
                                                    {
                                                        ContestJoinedUsers.length > 0 ?
                                                            _.map(ContestJoinedUsers, (item, idx) => {
                                                                return (
                                                                    <tbody key={idx}>
                                                                        <tr>
                                                                            <td className="pl-3">
                                                                                {item.user_name ? item.user_name : '--'}
                                                                            </td>
                                                                            <td className="pl-20">{item.team_count ? item.team_count : '0'}</td>
                                                                        </tr>
                                                                    </tbody>
                                                                )
                                                            })
                                                            :
                                                            <tbody>
                                                                <tr>
                                                                    <td colSpan="8">
                                                                        <div className="no-records">
                                                                            {NC.NO_RECORDS}</div>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                    }
                                                </Table>
                                            </Col>
                                        </Row>
                                    )
                            }
                        </Fragment>
                    }
                    {
                        multiLineup &&
                        <Fragment>
                            <Row>
                                <Col md={12} className="table-responsive common-table mt-3">
                                    <Table>
                                        <thead>
                                            <tr>
                                                <th className="left-th pl-3">Add number of bots</th>
                                                <th className="text-center pr-28">Lineup</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td className="pl-3 select-wtd">
                                                    <Input
                                                        type="number"
                                                        name='BotNumber'
                                                        value={BotNumber}
                                                        placeholder='Enter number of bots'
                                                        onChange={(e) => this.handleInputChange(e)}
                                                    />
                                                    {
                                                        multiMsg &&
                                                        <span className="color-red">{MULTIPLE_BOT}</span>
                                                    }
                                                </td>
                                                <td className="text-center">
                                                    <div className="lineup-control">
                                                        <span
                                                            onClick={() => this.multiPlayerLineup(false)}
                                                            className="linup-action">
                                                            <i className="icon-minus"></i>
                                                        </span>
                                                        <span className="linup-count">{multiLineupCount}</span>
                                                        <span
                                                            onClick={() =>
                                                                (multiLineupCount >= ContestDetail.multiple_lineup)
                                                                    ?
                                                                    notify.show("Maximum " + ContestDetail.multiple_lineup + " lineup allowed per player", "error", 3000)
                                                                    // :
                                                                    // (TotalLineupsToJoin >= (parseInt(MaxSystemUser) - parseInt(TotalJoinedUser)) || TotalLineupsToJoin >= MaxSystemUserRequest)
                                                                    //     ?
                                                                    //     notify.show("You are allowed to create " + MaxSystemUser + " teams per contest and " + MaxSystemUserRequest + " teams per request.", "error", 3000)
                                                                        :
                                                                        this.multiPlayerLineup(true)
                                                            }
                                                            className="linup-action">
                                                            <i className="icon-plus"></i>
                                                        </span>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </Table>
                                </Col>
                            </Row>
                            {
                                <Row className="submit-container">
                                    <Col>
                                        {
                                            addBot ?
                                            <Fragment>
                                                <Loader hide className="min-hgt-40"/>
                                                <div className="font-16 text-red">Adding Bots...</div>
                                            </Fragment>
                                            :
                                            <Button
                                                onClick={this.joinMultiLineup}
                                                disabled={multiSubPosting}
                                                className="btn-secondary-outline">Submit</Button>
                                            }
                                    </Col>
                                </Row>
                            }
                        </Fragment>
                    }
                </div>
            </React.Fragment>
        )
    }
}
export default AddNetwGameSysUser