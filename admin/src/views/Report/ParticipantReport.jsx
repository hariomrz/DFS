import React, { Component, Fragment } from "react";
import { Row, Col, Button, Table, Input } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Select from 'react-select';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import Pagination from "react-js-pagination";
import Loader from '../../components/Loader';
import Moment from 'react-moment';
import LS from 'local-storage';
export default class ParticipantReport extends Component {
    constructor(props) {
        super(props)
        this.state = {
            TotalUser: 0,
            // PERPAGE: NC.ITEMS_PERPAGE,
            PERPAGE: 10,
            CURRENT_PAGE: 1,
            startDate: '',
            endDate: '',
            FromDate: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000),
            ToDate: new Date(),
            UserReportList: [],
            Keyword: '',
            sortField: 'first_name',
            isDescOrder: true,
            SelectedLeague: '',
            LeagueList: [],
            TotalDeposit: '',
            AllSportsList: [],
            SelectedLSports: '',
            contestName: '',
            contestName: '',
            CollectionList: [],
            SelectedCollection: '',
            SelectedGroup: '',
            collectionType: 1,
            posting: false,
            teamPosting: false,
            TeamList: [],
            contestList: [],
            selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
            RealTeams: 0,
            SystemTeams: 0,
            TotalUsers: 0,
            activeUser: null,
            SelectedGroup: { value: '', label: "All" }
        }
        this.SearchCodeReq = _.debounce(this.SearchCodeReq.bind(this), 500);
    }
    componentDidMount() {
        this.GetContestFilterData()
    }

    GetContestFilterData = () => {
        this.setState({ posting: true })
        let params = { "sports_id": this.state.selected_sport };
        WSManager.Rest(NC.baseURL + NC.GET_CONTEST_FILTER, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                responseJson = responseJson.data;

                let tempGroupList = [{ value: '', label: 'All' }];
                if (responseJson.group_list) {
                    responseJson.group_list.map(function (lObj, lKey) {
                        tempGroupList.push({ value: lObj.group_id, label: lObj.group_name });
                    });
                }

                let tempLeagList = [];
                if (responseJson.league_list) {
                    responseJson.league_list.map(function (lObj, lKey) {
                        tempLeagList.push({ value: lObj.league_id, label: lObj.league_name });
                    });
                }

                this.setState({ groupList: tempGroupList, LeagueList: tempLeagList });
            }
            this.setState({ posting: false })
        })
    }

    getContest = () => {
        let { selected_sport, SelectedCollection, SelectedGroup } = this.state
        if (_.isEmpty(SelectedCollection))
        {
            notify.show("Pleae select league and match first", "error", 3000)
            return false
        }
        let params = {
            "group_id": SelectedGroup.value,
            "sports_id": selected_sport,
            "collection_master_id": SelectedCollection.value,
        };
        WSManager.Rest(NC.baseURL + NC.NR_GET_COLLECTION_CONTEST, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                responseJson = responseJson.data;

                let tempContestList = [];
                if (responseJson.result) {
                    responseJson.result.map(function (lObj, lKey) {
                        tempContestList.push({ value: lObj.contest_id, label: lObj.contest_name });
                    });
                }
                this.setState({ contestList: tempContestList });
            }
        })
    }

    getTotalCounts = () => {
        let { selected_sport, SelectedContest } = this.state
        let params = {
            "sports_id": selected_sport,
            "contest_id": SelectedContest.value,
        };
        WSManager.Rest(NC.baseURL + NC.NR_GET_CONTEST_PARTICIPANT_REPORT, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                responseJson = responseJson.data;
                this.setState({
                    RealTeams: responseJson.real_teams,
                    SystemTeams: responseJson.system_teams,
                    TotalUsers: responseJson.users,
                })

            }
        })
    }

    getAllCollections = () => {
        const { collectionType, selected_sport, SelectedLeague } = this.state
        let params = {
            league_id: SelectedLeague.value,
            sports_id: selected_sport
        }
        WSManager.Rest(NC.baseURL + NC.NR_GET_ALL_COLLECTIONS, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {

                const Temp = []
                _.map(ResponseJson.data, (item) => {
                    Temp.push({
                        value: item.collection_master_id, label: item.collection_name
                    })
                })
                this.setState({
                    CollectionList: Temp
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    exportReport = () => {
        const { SelectedContest } = this.state
        if (!_.isEmpty(SelectedContest))
        {
            var query_string = SelectedContest.value;
            // let sessionKey = WSManager.getToken();
            // query_string += "&Sessionkey" + "=" + sessionKey;
    
            console.log("Ex_url :", NC.S3 + 'lineup/' + query_string + '.pdf');                            

            window.open(NC.S3 + 'lineup/' + query_string + '.pdf', '_blank');
        }else{
            notify.show("Please select contest.", "error", 3000)
        }
    }

    getReportUser = () => {
        this.setState({ posting: true })
        const { PERPAGE, CURRENT_PAGE, SelectedContest } = this.state
        let params = {
            "items_perpage": PERPAGE,
            "total_items": 0,
            "current_page": CURRENT_PAGE,
            // "contest_id": "733",
            // "game_id": "733"
            "contest_id": SelectedContest.value,
            "game_id": SelectedContest.value
        }
        WSManager.Rest(NC.baseURL + NC.GET_DFS_GAME_LINEUP_DETAIL, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    posting: false,
                    UserReportList: ResponseJson.data.result,
                    TotalUser: ResponseJson.data.total,
                },()=>{
                        if (!_.isEmpty(this.state.UserReportList))
                        this.loadTeam(this.state.UserReportList[0].lineup_master_contest_id)
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    loadTeam = (l_m_contest_id) => {
        this.setState({ 
            teamPosting: true, 
            activeUser: l_m_contest_id, 
            TeamList: [], 
        })
        let { SelectedLeague } = this.state
        let params = {
            "lineup_master_contest_id": l_m_contest_id,
            "league_id": SelectedLeague.value
        }
        WSManager.Rest(NC.baseURL + NC.DFS_GET_USER_CONTEST_TEAM, params).then(ResponseJson => {

            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    teamPosting: false,
                    TeamList: ResponseJson.data.lineup,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    handleLeagueChange = (value, name) => {
        if (value != null)
            this.setState({
                [name]: value,
                CURRENT_PAGE: 1,
                SelectedCollection: '',
                SelectedContest: '',
                UserReportList : [],
                TeamList : [],
            }, this.getAllCollections)
    }

    handleCollectionChange = (value, name) => {
        if (value != null)
            this.setState({
                [name]: value,
                CURRENT_PAGE: 1,
                SelectedContest: '',
                UserReportList: [],
                TeamList: [],
            }, this.getContest)
    }

    handleCategoryChange = (value, name) => {
        if (value != null)
            this.setState({
                [name]: value,
                CURRENT_PAGE: 1,
                UserReportList: [],
                TeamList: [],
            }, this.getContest)
    }

    handleContestChange = (value, name) => {
        if (value != null)
            this.setState({
                [name]: value,
                CURRENT_PAGE: 1,
                UserReportList : [],
                TeamList : []
            }, () => {
                this.getReportUser()
                this.getTotalCounts()
            })
    }

    handlePageChange(current_page) {
        if (this.state.CURRENT_PAGE != current_page) {
            this.setState({
                CURRENT_PAGE: current_page
            }, () => {
                this.getReportUser();
            });
        }
    }

    searchByUser = (e) => {
        this.setState({ Keyword: e.target.value }, this.SearchCodeReq)
    }

    SearchCodeReq() {
        if (this.state.Keyword.length > 2)
            this.getReportUser()
    }


    render() {
        const { posting, UserReportList, CURRENT_PAGE, PERPAGE, TotalUser, Keyword, isDescOrder, SelectedLeague, LeagueList, CollectionList, SelectedCollection, groupList, SelectedGroup, TeamList, teamPosting, contestList, SelectedContest, RealTeams, SystemTeams, TotalUsers, activeUser } = this.state
        return (
            <Fragment>
                <div className="animated fadeIn participants-rp">
                    <Row>
                        <Col md={12}>
                            <h1 className="h1-cls">Participant Report</h1>
                        </Col>
                    </Row>
                    <div className="user-deposit-amount">
                        <Row className="mt-4">
                            <Col md={2}>
                                <div>
                                    <label className="filter-label">Select League</label>
                                    <Select
                                        isSearchable={true}
                                        class="form-control"
                                        options={LeagueList}
                                        menuIsOpen={true}
                                        value={SelectedLeague}
                                        onChange={e => this.handleLeagueChange(e, 'SelectedLeague')}
                                    />
                                </div>
                            </Col>
                            <Col md={2}>
                                <div>
                                    <label className="filter-label">Select Match</label>
                                    <Select
                                        isSearchable={true}
                                        class="form-control"
                                        options={CollectionList}
                                        menuIsOpen={true}
                                        value={SelectedCollection}
                                        onChange={e => this.handleCollectionChange(e, 'SelectedCollection')}
                                    />
                                </div>
                            </Col>
                            <Col md={2}>
                                <div>
                                    <label className="filter-label">Select Category</label>
                                    <Select
                                        isSearchable={true}
                                        class="form-control"
                                        id="group_id"
                                        name="group_id"
                                        options={groupList}
                                        menuIsOpen={true}
                                        value={SelectedGroup}
                                        onChange={e => this.handleCategoryChange(e, 'SelectedGroup')}
                                    />
                                </div>
                            </Col>
                            <Col md={2}>
                                <div>
                                    <label className="filter-label">Contest Name</label>
                                    <Select
                                        isSearchable={true}
                                        class="form-control"
                                        id="contest_id"
                                        name="contest_id"
                                        options={contestList}
                                        menuIsOpen={true}
                                        value={SelectedContest}
                                        onChange={e => this.handleContestChange(e, 'SelectedContest')}
                                    />
                                </div>
                            </Col>
                        </Row>
                        <Row className="prp-head-wrap">
                            <Col md={9}>
                                <div className="prp-head-info">
                                    <div className="prp-title">Number of User</div>
                                    <div className="prp-count">{TotalUsers}</div>
                                </div>
                                <div className="prp-head-info">
                                    <div className="prp-title">User Teams</div>
                                    <div className="prp-count">{RealTeams}</div>
                                </div>
                                <div className="prp-head-info">
                                    <div className="prp-title">System Teams</div>
                                    <div className="prp-count">{SystemTeams}</div>
                                </div>
                            </Col>
                            <Col md={3} className="mt-4">
                                <i className="export-list icon-export" onClick={e => this.exportReport()}></i>
                            </Col>
                        </Row>
                        <Row>
                            <Col md={12}>
                                <div className="prp-header">Participant Details</div>
                            </Col>
                        </Row>
                        <Row>
                            <Col md={7} className="table-responsive common-table">
                                <Table>
                                    <thead>
                                        <tr>
                                            <th className="pointer">Rank</th>
                                            <th className="pointer">User Name</th>
                                            <th className="pointer">Pts</th>
                                        </tr>
                                    </thead>
                                    {
                                        (UserReportList.length > 0) ?
                                            _.map(UserReportList, (item, idx) => {
                                                return (
                                                    <tbody key={idx}>
                                                        <tr className={`cursor-pointer ${activeUser == item.lineup_master_contest_id ? 'activeUser' : ''}`} onClick={() => this.loadTeam(item.lineup_master_contest_id)}>
                                                            <td>{item.game_rank}</td>
                                                            <td className="contest-d-main">
                                                                {item.user_name}
                                                                {
                                                                    item.is_systemuser === '1' &&
                                                                    <span className="cont-su-flag">S</span>
                                                                }
                                                            </td>
                                                            <td>{item.total_score}</td>
                                                        </tr>
                                                    </tbody>
                                                )
                                            })
                                            :
                                            <tbody>
                                                <tr>
                                                    <td colSpan='22'>
                                                        {!posting ?
                                                            <div className="no-records">
                                                                {NC.NO_RECORDS}
                                                                <br />
                                                                <i>{NC.PRP_REPORT_MSG}</i>
                                                            </div>
                                                            :
                                                            <Loader />
                                                        }
                                                    </td>
                                                </tr>
                                            </tbody>
                                    }
                                </Table>
                                {
                                    TotalUser > PERPAGE && (
                                        <div className="custom-pagination lobby-paging">
                                            <Pagination
                                                activePage={CURRENT_PAGE}
                                                itemsCountPerPage={PERPAGE}
                                                totalItemsCount={TotalUser}
                                                pageRangeDisplayed={5}
                                                onChange={e => this.handlePageChange(e)}
                                            />
                                        </div>
                                    )
                                }
                            </Col>
                            <Col md={5} className="table-responsive common-table">
                                <Table>
                                    <thead>
                                        <tr>
                                            <th className="pointer">Player Name</th>
                                            <th></th>
                                            <th className="pointer">Pts</th>
                                        </tr>
                                    </thead>
                                    {
                                        (TeamList.length > 0) ?
                                            _.map(TeamList, (lineup, idx) => {
                                                return (
                                                    <tbody key={idx}> 
                                                        <tr>
                                                            <td>
                                                                <div>
                                                                    {lineup.full_name}
                                                                    <span className={`ply-status ${(lineup.is_playing == 1) ? 'playing' : (lineup.is_playing == 0) ? 'not-playing' : ''}`}></span>
                                                                </div>
                                                                <div className="prp-ply-detail">
                                                                    <span>{lineup.position}</span>
                                                                    <span className="prp-sepr"></span>
                                                                    <span>{lineup.team_abbr}</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                {
                                                                    lineup.captain == 0 && ''
                                                                }
                                                                {
                                                                    lineup.captain == 1 && 
                                                                    <div className="prp-c-vc">C</div>
                                                                }
                                                                {
                                                                    lineup.captain == 2 && 
                                                                    <div className="prp-c-vc">V</div>
                                                                }
                                                            </td>
                                                            <td>{lineup.score}</td>
                                                        </tr>
                                                    </tbody>
                                )
                            })
                            :
                                            <tbody>
                                    <tr>
                                        <td colSpan='22'>
                                            {TeamList.length == 0 && !teamPosting ?
                                                            <div className="no-records">
                                                                {NC.NO_RECORDS}
                                                            </div>
                                                :
                                                <Loader />
                                            }
                                        </td>
                                    </tr>
                                </tbody>
                                }
                                </Table>
                            </Col>
                        </Row>
                </div>


                </div>
            </Fragment >
        )
    }
}