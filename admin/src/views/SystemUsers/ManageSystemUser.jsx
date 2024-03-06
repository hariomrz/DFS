import React, { Component, Fragment } from "react";
import { Row, Col, Table, Button, Input } from "reactstrap";
import _, { isEmpty } from 'lodash';
import WSManager from '../../helper/WSManager';
import HF from '../../helper/HelperFunction';
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import LS from 'local-storage';
import Loader from '../../components/Loader';
import Pagination from "react-js-pagination";
import { getSeasonDetails } from "../../helper/WSCalling";
import { MomentDateComponent } from "../../components/CustomComponent";
class ManageSystemUser extends Component {
    constructor(props) {
        super(props)
        this.state = {
            selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
            league_id: (this.props.league_id) ? this.props.league_id : (this.props.match.params.league_id || 0),
            // season_game_uid: (this.props.match.params.season_game_uid) ? this.props.match.params.season_game_uid : '',
            collection_master_id: (this.props.match.params.collection_master_id) ? this.props.match.params.collection_master_id : '',
            ContestDetail: [],
            SystemUserList: [],
            addTeamShow: false,
            NoOfTeams: '',
            TeamsPerUser: '',
            saveBtnDisable: false,
            BackTab: (this.props.match.params.tab) ? this.props.match.params.tab : 1,
            TotalData: [],
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            Total: 1,
            fixtureDetail: [],
            season_id: (this.props.match.params.season_id) ? this.props.match.params.season_id : '',
        }
    }

    componentDidMount = () => {
        this.GetFixtureDetail()
        this._getTotalData()
        this._getSystemUser()
    }

    GetFixtureDetail = () => {
        let { league_id, selected_sport, season_id,collection_master_id } = this.state
        let param = {
            // "collection_master_id": collection_master_id,
            // "sports_id": selected_sport,
            "season_id": season_id
        }
        this.setState({ posting: true });

        getSeasonDetails(param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({
                    posting: false,
                    fixtureDetail: responseJson.data
                });
            }
            else if (responseJson.response_code == NC.sessionExpireCode) {
                WSManager.logout();
                this.props.history.push('/login');
            }
        })
    }

    _getTotalData = () => {
        let { selected_sport, collection_master_id, NoOfTeams, TeamsPerUser } = this.state
        let params = {
            "sports_id": selected_sport,
            "collection_master_id": collection_master_id,
        }

        WSManager.Rest(NC.baseURL + NC.DFS_MANAGE_SYSTEM_USER_MASTER_DATA, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({
                    TotalData: responseJson.data,
                });
            }
            else if (responseJson.response_code == NC.sessionExpireCode) {
                WSManager.logout();
                this.props.history.push('/login');
            }
            this.setState({ saveBtnDisable: false })
        })
    }

    _getSystemUser = () => {
        let { selected_sport, collection_master_id, PERPAGE, CURRENT_PAGE } = this.state

        let params = {
            "sports_id": selected_sport,
            "collection_master_id": collection_master_id,
            "items_perpage": PERPAGE,
            "current_page": CURRENT_PAGE,
        }

        WSManager.Rest(NC.baseURL + NC.DFS_MANAGE_SYSTEM_USER_LIST, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                if (CURRENT_PAGE == 1) {
                    this.setState({
                        Total: responseJson.data ? responseJson.data.total : 0,
                    });
                }
                this.setState({
                    SystemUserList: responseJson.data.result,
                });
            }
            else if (responseJson.response_code == NC.sessionExpireCode) {
                WSManager.logout();
                this.props.history.push('/login');
            }
            this.setState({ saveBtnDisable: false })
        })
    }

    handleChangeValue = (e) => {
        this.setState({ [e.target.name]: e.target.value })
    }

    showAddTeam = (status) => {
        this.setState({
            addTeamShow: status,
            NoOfTeams: '',
            TeamsPerUser: ''
        })
    }

    validateSaveTeam = () => {
        let { NoOfTeams, TeamsPerUser } = this.state
        let msg = ''
        if (isEmpty(NoOfTeams)) {
            msg = 'Please enter No. of Teams'
        }
        else if (HF.isFloat(NoOfTeams) || isNaN(parseInt(NoOfTeams))) {
            msg = "Please enter No. of Teams in number"
        }
        else if (isEmpty(TeamsPerUser)) {
            msg = 'Please enter Teams per User'
        }
        else if (HF.isFloat(TeamsPerUser) || isNaN(parseInt(TeamsPerUser))) {
            msg = "Please enter Teams per User in number"
        }
        else {
            this.setState({ saveBtnDisable: true })
            this.saveTeam()
            return false;
        }
        notify.show(msg, "error", 2000)
    }

    saveTeam = () => {
        let { selected_sport, collection_master_id, NoOfTeams, TeamsPerUser, repeat_user } = this.state
        let params = {
            // "sports_id": selected_sport,
            "collection_master_id": collection_master_id,
            "no_of_teams": NoOfTeams,
            "teams_per_user": TeamsPerUser,
            "repeat_user": repeat_user ? "1" : "0",
        }

        WSManager.Rest(NC.baseURL + NC.ADD_SYSTEM_USER_TEAMS, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({
                    saveBtnDisable: false,
                    NoOfTeams: '',
                    TeamsPerUser: '',
                });
                this._getTotalData()
                this._getSystemUser()
                notify.show(responseJson.message, "success", 2000)
            }
            else if (responseJson.response_code == NC.sessionExpireCode) {
                WSManager.logout();
                this.props.history.push('/login');
            }else {
                notify.show(responseJson.global_error || responseJson.message, "error", 3000)
            }
            this.setState({ saveBtnDisable: false })
        })
    }

    handlePageChange(current_page) {
        if (this.state.CURRENT_PAGE != current_page) {
            this.setState({
                CURRENT_PAGE: current_page
            }, () => {
                this._getSystemUser();
            });
        }
    }

    handleRoles = (e) => {
        if (e) {
            this.setState({
                repeat_user: !this.state.repeat_user
            })
        }
    }

    render() {
        let { CURRENT_PAGE, PERPAGE, SystemUserList, league_id, season_id, NoOfTeams, TeamsPerUser, addTeamShow, saveBtnDisable, BackTab, TotalData, Total, fixtureDetail } = this.state
console.log('fixtureDetailfixtureDetail',fixtureDetail)
        return (
            <React.Fragment>
                <div className="add-system-user multi-su">
                    <Row>
                        <Col md={10}>
                            <div className="common-fixture float-left">
                                <img src={NC.S3 + NC.FLAG + fixtureDetail.home_flag} className="com-fixture-flag float-left" alt="" />
                                <img src={NC.S3 + NC.FLAG + fixtureDetail.away_flag} className="com-fixture-flag float-right" alt="" />
                                <div className="com-fixture-container">
                                    <div className="com-fixture-name">{(fixtureDetail.home) ? fixtureDetail.home : 'TBA'} VS {(fixtureDetail.away) ? fixtureDetail.away : 'TBA'}</div>

                                    <div className="com-fixture-time">
                                        {/* <MomentDateComponent data={{ date: fixtureDetail.season_scheduled_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                                        {HF.getFormatedDateTime(fixtureDetail.season_scheduled_date, "D-MMM-YYYY hh:mm A")}
                                    </div>
                                    <div className="com-fixture-title">{fixtureDetail.league_name}</div>
                                </div>
                            </div>
                        </Col>
                        <Col md={2}>
                            <div
                                onClick={() => this.props.history.push({ pathname: '/contest/fixturecontest/' + this.state.collection_master_id + '/' + season_id + '/2' })}
                                className="go-back float-right text-body mt-3 mb-0">{'<<'} Back </div>
                        </Col>
                    </Row>
                    <Row className="mt-3">
                        <Col md={10}>
                            <h2 className="h2-cls mt-2">Manage System Users</h2>
                        </Col>
                    </Row>
                    <hr className="mt-2" />
                    <Row className="manage-su-count-box">
                        <Col sm={5}>
                            <div className="fxcon-total-box">
                                <div className="fxcon-title">Total Teams</div>
                                <div className="fxcon-count">
                                    {TotalData ? TotalData.total_teams : 0}
                                </div>
                            </div>
                            <div className="fxcon-total-box">
                                <div className="fxcon-title">System Users</div>
                                <div className="fxcon-count">
                                    {TotalData ? TotalData.total_system_users : 0}
                                </div>
                            </div>
                        </Col>
                        <Col sm={7} className="pl-0 pr-0">
                            {
                                !addTeamShow && (
                                    <Button
                                        className={`btn-secondary float-right at-btn`}
                                        onClick={() => this.showAddTeam(true)}
                                    >Add Teams</Button>
                                )
                            }
                            {
                                addTeamShow && (
                                    <div className="msu-team-form">
                                        <div className="msu-team-input common-cus-checkbox">
                                            <label className="com-chekbox-container">
                                                <span className="opt-text">Repeat User</span>
                                                <input
                                                    type="checkbox"
                                                    name={'repeat_user'}
                                                    id={'repeat_user'}
                                                    // defaultChecked={dCheck}
                                                    onChange={(e) => this.handleRoles(e)}
                                                />
                                                <span className="com-chekbox-checkmark"></span>
                                            </label>
                                        </div>
                                        <div className="msu-team-input">
                                            <label>No. of Teams</label>
                                            <Input
                                                rows="3"
                                                type="text"
                                                name="NoOfTeams"
                                                value={NoOfTeams}
                                                onChange={this.handleChangeValue}
                                                placeholder="10"
                                            />
                                        </div>
                                        <div className="msu-team-input">
                                            <label>Teams per User</label>
                                            <Input
                                                rows="3"
                                                type="text"
                                                name="TeamsPerUser"
                                                value={TeamsPerUser}
                                                onChange={this.handleChangeValue}
                                                placeholder="10"
                                            />
                                        </div>
                                        <div className="xmsu-team-input mt-4 msu-btn">
                                            {
                                                !saveBtnDisable ?
                                                    <Fragment>
                                                        <Button
                                                            disabled={saveBtnDisable}
                                                            className="btn-secondary"
                                                            onClick={() => this.validateSaveTeam()}
                                                        >Save</Button>
                                                        <Button
                                                            className="btn-secondary-outline"
                                                            onClick={() => this.showAddTeam(false)}
                                                        >Cancel</Button>
                                                    </Fragment>
                                                    :
                                                    <Loader hide />
                                            }
                                        </div>
                                    </div>
                                )
                            }
                        </Col>
                    </Row>

                    <Row>
                        <Col sm={12}>
                            <div className="linup-info-text xmx-text-wd">
                                You are allowed to create {TotalData.max_teams_per_fixture} teams per fixture
                            </div>
                        </Col>
                    </Row>

                    <Row className="mt-30">
                        <Col sm={12}>
                            <h2 className="hc-cls">System User Details</h2>
                        </Col>
                    </Row>
                    <hr className="m-0" />
                    <Fragment>
                        <Row>
                            <Col md={12} className="table-responsive common-table mt-3">
                                <Table>
                                    <thead>
                                        <tr>
                                            <th className="pl-3">S No.</th>
                                            <th className="text-center pr-28">System User ID</th>
                                            <th className="text-center pr-28">System User</th>
                                            <th className="">Total Teams</th>
                                        </tr>
                                    </thead>
                                    {
                                        SystemUserList.length > 0 ?
                                            _.map(SystemUserList, (item, idx) => {
                                                return (
                                                    <tbody key={idx}>
                                                        <tr>
                                                            <td className="pl-3 select-wtd">{idx + 1}</td>
                                                            <td className="text-center">{item.user_id}</td>
                                                            <td className="text-center">{item.user_name}</td>
                                                            <td className="text-center">{item.total_teams}</td>
                                                        </tr>
                                                    </tbody>
                                                )
                                            })
                                            :
                                            <tbody>
                                                <tr>
                                                    <td colSpan="8">
                                                        <div className="no-records">{NC.NO_RECORDS}
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                    }
                                </Table>
                            </Col>
                        </Row>
                        <Row className="float-right mb-50">
                            <Col md={12}>
                                {
                                    Total > PERPAGE && (
                                        <div className="custom-pagination">
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
                            </Col>
                        </Row>
                    </Fragment>
                </div>
            </React.Fragment>
        )
    }
}
export default ManageSystemUser
