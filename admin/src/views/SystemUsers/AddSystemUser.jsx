import React, { Component, Fragment } from "react";
import { Row, Col, Table, Button, Progress, Input } from "reactstrap";
import _ from 'lodash';
import WSManager from '../../helper/WSManager';
import HF, { _isEmpty } from '../../helper/HelperFunction';
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import Images from '../../components/images';
import Select from 'react-select';
import LS from 'local-storage';
import Loader from '../../components/Loader';
import moment from 'moment';
import { getDFSSeasonDetails, getContestDetail_SU, getContestJoined_SU, getSystemUsersForContest_SU, joinSystemUsers_SU } from "../../helper/WSCalling";
import { MomentDateComponent } from "../../components/CustomComponent";

class AddSystemUser extends Component {
    constructor(props) {
        super(props)
        this.state = {
            selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,

            league_id: (this.props.league_id) ? this.props.league_id : this.props.match.params.league_id,

            // season_game_uid: (this.props.match.params.season_game_uid) ? this.props.match.params.season_game_uid : '',

            contest_unique_id: (this.props.match.params.contest_unique_id) ? this.props.match.params.contest_unique_id : '',
            selected_cate: 1,
            isShowAutoToolTip: false,
            MaxSystemUser: 0,
            MaxSystemUserRequest: 0,
            ContestDetail: [],
            SUserList: [],
            AddPlayerList: [],
            ContestJoinedUsers: [],
            fixtureDetail: [],
            TotalLineupsToJoin: 0,
            TotalJoinedUser: 0,
            submitPosting: false,
            viewSubmitted: false,
            LineupOut: false,
            multiUsers: '',
            multiTeams: '',
            collection_master_id: (this.props.match.params.collection_master_id) ? this.props.match.params.collection_master_id : '',
            saveBtnDisable: false,
            season_id: (this.props.match.params.season_id) ? this.props.match.params.season_id : '',
        }
    }

    componentDidMount = () => {
        this.getContestDetail()
        this.GetFixtureDetail()
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

        getContestDetail_SU(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                let ResponseData = ResponseJson.data
                var dt = new Date(ResponseData.contest_detail.season_scheduled_date);
                dt.setMinutes(dt.getMinutes() - 20);

                let contestDetails = ResponseData.contest_detail;
                this.createMultiLineupDropDowm(ResponseData.contest_detail.multiple_lineup);

                this.setState({
                    MaxSystemUser: ResponseData.max_system_user,
                    MaxSystemUserRequest: ResponseData.system_user_request_limit,
                    ContestDetail: contestDetails,
                    TotalJoinedUser: contestDetails.total_system_user,
                }, () => {
                    // this.getSystemUsersForContest()
                    // this.getContestJoinedSysUser()

                    if (this.getTimeDiff(dt) || contestDetails.total_system_user === ResponseData.max_system_user || contestDetails.total_user_joined === contestDetails.size) {

                        this.setState({
                            viewSubmitted: true,
                            LineupOut: true
                        }, () => {
                            this.getContestJoinedSysUser()
                        })
                    }
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    /****
     * Multi line up drop down value 
     */

    createMultiLineupDropDowm = (count) => {
        let spCate = [];
        for (let i = 1; i <= count; i++) {
            console.log('i == ', i)
            spCate.push({
                value: i,
                label: i
            });
        }
        this.setState({ MultiLineUpDropValue: spCate })
    }



    GetFixtureDetail = () => {
        let { selected_sport, season_id,collection_master_id } = this.state
        let param = {
            // "league_id": league_id,
            // "sports_id": selected_sport,
            "collection_master_id": collection_master_id
        }
        this.setState({ posting: true });

        getDFSSeasonDetails(param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({
                    posting: false,
                    fixtureDetail: responseJson.data
                },()=>{
                    console.log('fixtureDetail',this.state.fixtureDetail)
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
            "contest_id": this.state.ContestDetail.contest_id,
            "collection_master_id": this.state.collection_master_id,
        }

        getContestJoined_SU(params).then(ResponseJson => {
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
            // "collection_master_id": this.state.collection_master_id,
        }

        getSystemUsersForContest_SU(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                let ResponseData = ResponseJson.data
                let TempConUser = []
                let TempConDict = {}
                _.map(ResponseData, (item) => {
                    if (item.user_name != "" && item.user_name != null) {
                        TempConDict = {
                            "label": item.user_name,
                            "value": item.user_id,
                            "team_count": item.available_team_count,
                        }
                        TempConUser.push(TempConDict)
                    }
                })

                this.setState({
                    SUserList: TempConUser,
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
        }, () => {
            if (this.state.AddPlayerList?.length == 1) {
                this.getSystemUsersForContest()
            }
        })
    }

    managePlayerLineup = (index, value) => {
        let { TotalLineupsToJoin, AddPlayerList } = this.state
        let tempPlaList = AddPlayerList
        let PlaTeamCount = tempPlaList[index].team_count ? tempPlaList[index].team_count : 0
        let TempTotLineToJoin = TotalLineupsToJoin

        tempPlaList[index].team_count = value.value
        TempTotLineToJoin = value.value

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

        joinSystemUsers_SU(params).then(ResponseJson => {
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
                notify.show(ResponseJson.message || ResponseJson.global_error, "error", 3000)
            }
            this.setState({ submitPosting: false })
        }).catch(error => {
            this.setState({ submitPosting: false })
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    showSubmittedSU = () => {
        this.setState({ viewSubmitted: !this.state.viewSubmitted }, () => {
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

    handleChangeValue = (e) => {
        this.setState({ [e.target.name]: e.target.value })
    }

    validateJoinTeam = () => {
        let { multiUsers, multiTeams } = this.state
        let msg = ''

        if (_isEmpty(multiTeams)) {
            msg = 'Please enter Teams'
        }
        else if (HF.isFloat(multiTeams) || isNaN(parseInt(multiTeams))) {
            msg = "Please enter Teams in number"
        }
        else {
            this.setState({ saveBtnDisable: true })
            this.joinTeam()
            return false;
        }
        notify.show(msg, "error", 2000)
    }

    joinTeam = () => {
        let { ContestDetail, multiTeams } = this.state
        let param = {
            "contest_id": ContestDetail.contest_id,
            "no_of_teams": multiTeams
        }

        WSManager.Rest(NC.baseURL + NC.JOIN_MULTIPLE_USERS, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({
                    multiTeams: '',
                    TeamsPerUser: '',
                }, this._refreshJoinedSU);
                notify.show(responseJson.message, "success", 2000)
            }
            else if (responseJson.response_code == NC.sessionExpireCode) {
                WSManager.logout();
                this.props.history.push('/login');
            }
            else{
                notify.show(responseJson.message, "error", 2000)
            }
            this.setState({ saveBtnDisable: false })
        })
    }

    _refreshJoinedSU = () => {
        this.setState({ viewSubmitted: true }, () => {
            this.getContestDetail()
            this.getContestJoinedSysUser()
        })
    }

    render() {
        let { MaxSystemUserRequest, MaxSystemUser, ContestDetail, MultiLineUpDropValue, selected_cate, SUserList, AddPlayerList, TotalLineupsToJoin, submitPosting, viewSubmitted, ContestJoinedUsers, fixtureDetail, TotalJoinedUser, season_id, LineupOut, multiUsers, multiTeams, saveBtnDisable,collection_master_id } = this.state

        return (
            <React.Fragment>
                <div className="add-system-user">
                    <Row>
                        <div
                            onClick={() => this.props.history.push({ pathname: '/contest/fixturecontest/' + collection_master_id + '/' + season_id + '/2' })}
                            className="go-back float-right text-body w-100">{'<'} Back </div>
                    </Row>
                    <Row>
                        <Col md={9}>
                            {
                                !_isEmpty(fixtureDetail) &&
                                <div className="common-fixture float-left">
                                    <img src={NC.S3 + NC.FLAG + (fixtureDetail.home_flag ? fixtureDetail.home_flag: fixtureDetail.match[0].home_flag)} className="com-fixture-flag float-left" alt="" />
                                    <img src={NC.S3 + NC.FLAG + (fixtureDetail.away_flag ? fixtureDetail.away_flag : fixtureDetail.match[0].away_flag)} className="com-fixture-flag float-right" alt="" />
                                    <div className="com-fixture-container">
                                        <div className="com-fixture-name">{(fixtureDetail.home) ? fixtureDetail.home : fixtureDetail.match[0].home} VS {(fixtureDetail.away) ? fixtureDetail.away : fixtureDetail.match[0].away}</div>

                                        <div className="com-fixture-time">
                                            {/* <MomentDateComponent data={{ date: fixtureDetail.fixture_date_time, format: "D-MMM-YYYY hh:mm A" }} /> */}
                                            {HF.getFormatedDateTime(fixtureDetail.season_scheduled_date, "D-MMM-YYYY hh:mm A")}

                                        </div>
                                        <div className="com-fixture-title">{fixtureDetail.league_name}</div>
                                    </div>
                                </div>
                            }
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
                        <Col md={3}>
                            <div className="msu-team-form asu">
                                {/* <div className="msu-team-input">
                                    <label>User</label>
                                    <Input
                                        rows="3"
                                        type="text"
                                        name="multiUsers"
                                        value={multiUsers}
                                        onChange={this.handleChangeValue}
                                        placeholder="10"
                                    />
                                </div> */}
                                <div className="msu-team-input w-100">
                                    <label>Teams</label>
                                    <Input
                                        rows="3"
                                        type="text"
                                        name="multiTeams"
                                        value={multiTeams}
                                        onChange={this.handleChangeValue}
                                        placeholder="10"
                                    />
                                </div>
                                <div className="xmsu-team-input mt-4 msu-btn">
                                    {
                                        !saveBtnDisable ?
                                            <Button
                                                disabled={saveBtnDisable}
                                                className="btn-secondary mr-0"
                                                onClick={this.validateJoinTeam}
                                            >Join</Button>
                                            :
                                            <div className="msu-load">
                                                <Loader hide />
                                            </div>
                                    }
                                </div>
                            </div>
                        </Col>
                    </Row>
                    <Row>
                        <div>
                            {
                                !viewSubmitted ? (
                                    <div className="linup-info-text xmx-text-wd">
                                        You are allowed to create {MaxSystemUser} teams per contest and {MaxSystemUserRequest} teams per request.
                                    </div>
                                ) : fixtureDetail.playing_announce == "1" ? (
                                    <div className="linup-info-text p11-text-wd"><i className="icon-info"></i>Playing 11 Settled</div>
                                )
                                    :
                                    ""
                            }

                        </div>
                    </Row>
                    <Row className="mt-3">
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
                                                className="view-submitted-su">View Lineup(s)</a>
                                        )
                                        :
                                        ''
                                    : !LineupOut ? (
                                        <Button
                                            onClick={this.showSubmittedSU}
                                            className="add-more-su"><i className="icon-plus"></i> Add More</Button>
                                    )
                                        :
                                        ""
                            }

                        </Col>
                    </Row>
                    {!viewSubmitted ? (
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
                                                                        <div className="filters-area mr-3 center-data-2">
                                                                            {/* <SelectDropdown SelectProps={Type_Props} /> */}
                                                                            <Select
                                                                                menuIsOpen={true}
                                                                                isSearchable={false}
                                                                                options={MultiLineUpDropValue}
                                                                                value={item.team_count}
                                                                                placeholder="Select Lineup"
                                                                                onChange={e => this.managePlayerLineup(idx, e)}
                                                                            // isOptionDisabled={(SUserList) => SUserList.disabled === true}
                                                                            />
                                                                        </div>
                                                                        {/* <span
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
                                                                        </span> */}
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
                    )}
                </div>
            </React.Fragment>
        )
    }
}
export default AddSystemUser
//Just comment Code reflection check
