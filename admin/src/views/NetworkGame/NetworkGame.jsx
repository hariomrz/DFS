import React, { Component, Fragment } from 'react';
import { Card, CardBody, Col, Row, Modal, ModalBody, ModalHeader, ModalFooter, FormGroup, Button, Table } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Select from 'react-select';
import LS from 'local-storage';
import Images from '../../components/images';
import Pagination from "react-js-pagination";
import { getAllNetworkContest, publishNetworkContest } from "../../helper/WSCalling";
import { MomentDateComponent } from "../../components/CustomComponent";
import HF, { _Map } from '../../helper/HelperFunction';

var globalThis = null;
class NetworkGame extends Component {

    constructor(props) {
        super(props);
        let selected_sports_id = (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId;

        this.state = {
            selected_sport: selected_sports_id,
            contestParams: { 'sports_id': selected_sports_id, 'league_id': '', 'season_game_uid': '', 'collection_master_id': '', 'group_id': '', 'status': '', 'keyword': '', 'sort_field': 'season_scheduled_date', 'sort_order': 'DESC', currentPage: 1, pageSize: 10, pagesCount: 1 },
            leagueList: [],
            groupList: [],
            statusList: [],
            contestList: [],
            contestObj: {},
            keyword: '',
            posting: false,
            contest_promote_model: false,
            contestPromoteParam: {
                email_contest_model: false,
                message_contest_model: false,
                notification_contest_model: false
            },
            promote_model: false,
            minPage: 1,
            maxPage: 5,
            fixtureList: [],
            PubPosting: false,
            showUserbtn: false,
        };

    }

    componentDidMount() {
        globalThis = this;
        this.GetContestFilterData();
        
        var arr = ['nclient1', 'cricjam', 'predev'];       

        if (!_.isUndefined(NC.baseURL)) {
            let baseUrl = NC.baseURL
            // let baseUrl = 'https://scores11.com/'
            let botFlag = HF.containsString(baseUrl, arr)
            if (botFlag) {
                this.setState({ showUserbtn: true })
            }
        }

    }

    handleSelect = (eleObj, dropName) => {
        let contestParams = this.state.contestParams;
        contestParams[dropName] = (eleObj != null) ? eleObj.value : '';
        this.setState({ 'contestParams': contestParams, 'selected_league': (eleObj != null) ? eleObj.value : '' }, function () {
            if (dropName == 'league_id' || dropName == 'status') {
                this.GetContestList();
            }
        });
    }

    GetContestFilterData = () => {
        this.setState({ posting: true })
        let params = { "sports_id": this.state.selected_sport };
        WSManager.Rest(NC.baseURL + NC.GET_CONTEST_FILTER, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                responseJson = responseJson.data;
                let tempLeagueList = [{ 'value': '', 'label': 'All' }];
                if (responseJson.league_list) {
                    responseJson.league_list.map(function (lObj, lKey) {
                        tempLeagueList.push({ value: lObj.league_id, label: lObj.league_abbr });
                    });
                }
                let tempGroupList = [{ 'value': '', 'label': 'Select Group' }];
                if (responseJson.group_list) {
                    responseJson.group_list.map(function (lObj, lKey) {
                        tempGroupList.push({ value: lObj.group_id, label: lObj.group_name });
                    });
                }
                this.setState({ leagueList: tempLeagueList, groupList: tempGroupList, statusList: responseJson.status_list });

                this.GetContestList();
            }
            this.setState({ posting: false })
        })
    }

    GetContestList = () => {
        this.setState({ posting: true })
        let params = this.state.contestParams;
        getAllNetworkContest(params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                var responseJsonData = responseJson.data.result;
                this.setState({
                    contestList: responseJsonData,

                    contestParams: { ...this.state.contestParams, pagesCount: Math.ceil(responseJson.data.total / this.state.contestParams.pageSize), totalRecords: responseJson.data.total },
                })
            }
            this.setState({ posting: false })
        })
    }

    getWinnerCount(ContestItem) {
        if (!_.isUndefined(ContestItem.prize_distibution_detail)) {
            let pddetail = ContestItem.prize_distibution_detail
            if (pddetail != '') {
                if ((pddetail[pddetail.length - 1].max) > 1) {
                    return pddetail[pddetail.length - 1].max + " Winners"
                } else {
                    return pddetail[pddetail.length - 1].max + " Winner"
                }
            } else {
                return '0 Winner';
            }
        }
        else {
            return '0 Winner';
        }
    }

    viewWinners = (e, contestObj) => {
        e.stopPropagation();
        this.setState({ 'prize_modal': true, 'contestObj': contestObj });
    }

    closePrizeModel = () => {
        this.setState({ 'prize_modal': false, 'contestObj': {} });
    }

    sortContestList = (e, sort_field) => {
        let contestParams = _.cloneDeep(this.state.contestParams);
        let sort_order = contestParams.sort_order;
        if (contestParams.sort_field == sort_field) {
            if (sort_order == "DESC") {
                sort_order = "ASC";
            } else {
                sort_order = "DESC";
            }
        } else {
            sort_order = "DESC";
        }

        contestParams['sort_field'] = sort_field;
        contestParams['sort_order'] = sort_order;
        this.setState({ 'contestParams': contestParams }, function () {
            this.GetContestList();
        });
    }

    handlePagination(e, index) {
        e.preventDefault();
        var minPage = 1;
        var maxPage = 5;
        if (index >= 1 && index < 5) {
            maxPage = 5;
            minPage = 1;
        }

        if (index >= 5) {
            minPage = index - 2;
            maxPage = index + 2;
        }

        this.setState({
            contestParams: { ...this.state.contestParams, currentPage: index },
            minPage: minPage,
            maxPage: maxPage
        },
            () => {
                this.GetContestList();
            });
    }

    handlePageChange(current_page) {
        let contestParams = this.state.contestParams;
        contestParams['currentPage'] = current_page;
        this.setState(
            {
                contestParams: contestParams,
            },
            function () {
                this.GetContestList();
            });
    }

    changeStatusTemplate = (e, templateObj, indx) => {
        let { selected_sport, contestList } = this.state
        this.setState({ PubPosting: true })
        let TempList = contestList
        e.stopPropagation();
        if (window.confirm("Are you sure you want to publish?")) {
            let params = {
                sports_id: selected_sport,
                league_id: templateObj.league_id,
                id: templateObj.id,
                network_contest_id: templateObj.network_contest_id
            };
            publishNetworkContest(params).then((responseJson) => {
                if (responseJson.response_code === NC.successCode) {
                    TempList[indx].active = templateObj.active == "0" ? "1" : "0"
                    this.setState({
                        TemplateList: TempList,
                        PubPosting: false
                    })
                    notify.show(responseJson.message, "success", 5000);
                } else {
                    notify.show(responseJson.message, "error", 3000);
                }
                this.setState({
                    PubPosting: false
                })
            })
        } else {
            return false;
        }
    }

    render() {
        const { PubPosting, leagueList, statusList, contestList, contestObj, showUserbtn } = this.state

        return (
            <div className="animated fadeIn contestlist-dashboard">
                <Col lg={12}>
                    <Row className="dfsrow">
                        <h2 className="h2-cls">Network Contest</h2>
                    </Row>
                </Col>
                <Row>
                    <Col xs="12" sm="12" md="12" className="contest-dashboard-dropdown">
                        <label className="float-left form-group filter-label">Filter By - </label>
                        <FormGroup className="league-filter select-wrapper">
                            <Select
                                className=""
                                id="league_id"
                                name="league_id"
                                placeholder="Select League"
                                value={this.state.contestParams.league_id}
                                options={leagueList}
                                onChange={(e) => this.handleSelect(e, 'league_id')}
                            />
                        </FormGroup>
                        <FormGroup className="league-filter select-wrapper">
                            <Select
                                className=""
                                id="status"
                                name="status"
                                placeholder="Select Status"
                                value={this.state.contestParams.status}
                                options={statusList}
                                onChange={(e) => this.handleSelect(e, 'status')}
                            />
                        </FormGroup>
                    </Col>
                </Row>
                <Row>
                    <Col xs="12" lg="12" >
                        <div className="contestcard">
                            <CardBody>

                                <Table className="communication-table">
                                    <thead>
                                        <tr>
                                            <th className="contest-column">
                                                <div className="dropdown" onClick={(e) => this.sortContestList(e, 'contest_name')}>
                                                    <button className="contests dropdown-toggle contest-dashboard-btn" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        Contests
                                                    </button>
                                                    {
                                                        this.state.contestParams.sort_field == 'contest_name' && this.state.contestParams.sort_order == 'DESC' &&
                                                        <i className="fa fa-sort-desc"></i>
                                                    }
                                                    {
                                                        this.state.contestParams.sort_field == 'contest_name' && this.state.contestParams.sort_order == 'ASC' &&
                                                        <i className="fa fa-sort-asc"></i>
                                                    }
                                                </div>
                                            </th>
                                            <th onClick={(e) => this.sortContestList(e, 'entry_fee')}>
                                                <div className="dropdown">
                                                    <button className="dropdown-toggle contest-dashboard-btn" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        Entry Fee
                                                    </button>
                                                    {
                                                        this.state.contestParams.sort_field == 'entry_fee' && this.state.contestParams.sort_order == 'DESC' &&
                                                        <i className="fa fa-sort-desc"></i>
                                                    }
                                                    {
                                                        this.state.contestParams.sort_field == 'entry_fee' && this.state.contestParams.sort_order == 'ASC' &&
                                                        <i className="fa fa-sort-asc"></i>
                                                    }
                                                </div>
                                            </th>

                                            <th onClick={(e) => this.sortContestList(e, 'minimum_size')}>
                                                <div className="dropdown">
                                                    <button className="dropdown-toggle contest-dashboard-btn" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        Participants
                                                    </button>
                                                    {
                                                        this.state.contestParams.sort_field == 'minimum_size' && this.state.contestParams.sort_order == 'DESC' &&
                                                        <i className="fa fa-sort-desc"></i>
                                                    }
                                                    {
                                                        this.state.contestParams.sort_field == 'minimum_size' && this.state.contestParams.sort_order == 'ASC' &&
                                                        <i className="fa fa-sort-asc"></i>
                                                    }
                                                </div>
                                            </th>

                                            <th onClick={(e) => this.sortContestList(e, 'total_user_joined')}>
                                                <div className="dropdown">
                                                    <button className="dropdown-toggle contest-dashboard-btn" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        Entries
                                                    </button>
                                                    {
                                                        this.state.contestParams.sort_field == 'total_user_joined' && this.state.contestParams.sort_order == 'DESC' &&
                                                        <i className="fa fa-sort-desc"></i>
                                                    }
                                                    {
                                                        this.state.contestParams.sort_field == 'total_user_joined' && this.state.contestParams.sort_order == 'ASC' &&
                                                        <i className="fa fa-sort-asc"></i>
                                                    }
                                                </div>
                                            </th>

                                            <th onClick={(e) => this.sortContestList(e, 'prize_pool')}>
                                                <div className="dropdown">
                                                    <button className="dropdown-toggle contest-dashboard-btn" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        Winnings
                                                    </button>
                                                    {
                                                        this.state.contestParams.sort_field == 'prize_pool' && this.state.contestParams.sort_order == 'DESC' &&
                                                        <i className="fa fa-sort-desc"></i>
                                                    }
                                                    {
                                                        this.state.contestParams.sort_field == 'prize_pool' && this.state.contestParams.sort_order == 'ASC' &&
                                                        <i className="fa fa-sort-asc"></i>
                                                    }
                                                </div>
                                            </th>

                                            <th>
                                                <div className="dropdown">
                                                    <button className="dropdown-toggle contest-dashboard-btn" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        Winners
                                                    </button>
                                                </div>
                                            </th>
                                            <th></th>
                                        </tr>
                                    </thead>

                                </Table>

                            </CardBody>
                        </div>
                    </Col>
                </Row>
                {
                    _Map(contestList, (item, contest_index) => {
                        var dt = new Date(item.season_scheduled_date);
                        dt.setMinutes(dt.getMinutes() - 20);
                        let s_u_date = HF.getTimeDiff(dt);
                        return (
                            <Row id={contest_index}>
                                <Col xs="12" lg="12" className="collection-vd">

                                    <Card className="recentcom">
                                        <CardBody>
                                            <Table responsive ClassName="tablecontest">
                                                <tr>
                                                    <td className="contest-column">
                                                        <p className="contest-table-p">
                                                            <span className="line-text-ellipsis" style={{ WebkitBoxOrient: 'vertical' }}> {item.contest_details.contest_name}</span>

                                                            <span className="alphabets-icon">
                                                                {
                                                                    item.guaranteed_prize == '2' &&
                                                                    <i className="icon-icon-g contest-type"></i>
                                                                }
                                                                {
                                                                    item.multiple_lineup > 1 &&
                                                                    <i className="icon-icon-m contest-type"></i>
                                                                }
                                                                {
                                                                    item.is_auto_recurring == "1" &&
                                                                    <i className="icon-icon-r contest-type"></i>
                                                                }
                                                            </span>
                                                        </p>
                                                        <p className="mb-0">
                                                            {/* <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                                                            {HF.getFormatedDateTime(item.season_scheduled_date, "D-MMM-YYYY hh:mm A")}

                                                        </p>
                                                        <div className="carddiv contest-listtable">
                                                            <div>
                                                                {
                                                                    item.home_flag &&
                                                                    <img className="cardimgdfs mr-3" src={NC.S3 + NC.FLAG + item.home_flag} />
                                                                }
                                                                <span className="livcardh3dfs">{item.contest_details.collection_name}</span>
                                                                {
                                                                    item.away_flag &&
                                                                    <img className="cardimgdfs xfloat-right" src={NC.S3 + NC.FLAG + item.away_flag} />
                                                                }
                                                            </div>

                                                        </div>
                                                    </td>
                                                    <td>
                                                        {
                                                            item.contest_details.currency_type == '0' &&
                                                            <i className="icon-bonus"></i>
                                                        }
                                                        {
                                                            item.contest_details.currency_type == '1' &&
                                                            <i className="icon-rupess"></i>
                                                        }
                                                        {
                                                            item.contest_details.currency_type == '2' &&
                                                            <img src={Images.COINIMG} alt="coin-img" />
                                                        }
                                                        {item.contest_details.entry_fee}
                                                    </td>
                                                    <td>{item.contest_details.minimum_size + '-' + item.contest_details.size}</td>
                                                    <td>{item.contest_details.total_user_joined}</td>
                                                    <td>
                                                        {
                                                            item.contest_details.prize_type == '0' &&
                                                            <i className="icon-bonus"></i>
                                                        }
                                                        {
                                                            item.contest_details.prize_type == '1' &&
                                                            <i className="icon-rupess"></i>
                                                        }
                                                        {
                                                            item.contest_details.prize_type == '2' &&
                                                            <img src={Images.COINIMG} alt="coin-img" />
                                                        }
                                                        {item.contest_details.prize_pool}
                                                    </td>
                                                    <td>
                                                        <span
                                                            className="cursor-pointer"
                                                            onClick={(e) => (parseInt(this.getWinnerCount(item.contest_details)) > 0) ? this.viewWinners(e, item.contest_details) : null}>{this.getWinnerCount(item.contest_details)}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div className="edit-box sys-usr-box">
                                                            {item.active == 0 ? <Button
                                                                disabled={PubPosting}
                                                                className="btn-secondary btn-p"
                                                                onClick={(e) => this.changeStatusTemplate(e, item, contest_index)}
                                                            >Publish</Button>
                                                                :
                                                                <Fragment>
                                                                    <Button
                                                                        onClick={() => this.props.history.push('/network-game/details/' + item.contest_details.contest_unique_id)}
                                                                        className="btn-secondary btn-p"
                                                                    >
                                                                        Contest Details
                                                                    </Button>

                                                                    {(showUserbtn && !s_u_date) && <Button
                                                                        onClick={() => this.props.history.push({ pathname: '/system-users/add-ntwk-system-users/' + item.contest_details.league_id + '/' + item.contest_details.season_game_uid + '/' + item.contest_details.contest_unique_id })}
                                                                        className="btn-secondary btn-p"
                                                                    >
                                                                        Add system user
                                                                    </Button>}
                                                                </Fragment>
                                                            }
                                                        </div>
                                                    </td>
                                                </tr>
                                            </Table>
                                        </CardBody>
                                    </Card>
                                </Col>
                            </Row>
                        )
                    })
                }
                {contestList.length <= 0 &&
                    <div className="no-records">No Record Found.</div>
                }
                {contestList.length > 0 &&
                    <Col>
                        <div className="custom-pagination lobby-paging">
                            <Pagination
                                activePage={this.state.contestParams.currentPage}
                                itemsCountPerPage={this.state.contestParams.pageSize}
                                totalItemsCount={this.state.contestParams.totalRecords}
                                pageRangeDisplayed={5}
                                onChange={e => this.handlePageChange(e)}
                            />
                        </div>
                    </Col>
                }

                <div className="winners-modal-container">
                    <Modal isOpen={this.state.prize_modal} toggle={() => this.closePrizeModel()} className="winning-modal">
                        <ModalHeader toggle={this.toggle}>Winnings Distribution</ModalHeader>
                        <ModalBody>
                            <div className="distribution-container">
                                {
                                    contestObj.prize_distibution_detail &&
                                    <table>
                                        <tbody>
                                            {contestObj.prize_distibution_detail.map((prize, idx) => (
                                                <tr>
                                                    <td className="text-left pr-20">
                                                        {prize.min}
                                                        {
                                                            prize.min != prize.max &&
                                                            <span>-{prize.max}</span>
                                                        }
                                                    </td>
                                                    <td className="text-right">
                                                        {
                                                            contestObj.prize_type == '0' &&
                                                            <i className="icon-bonus"></i>
                                                        }
                                                        {
                                                            contestObj.prize_type == '1' &&
                                                            <i className="icon-rupess"></i>
                                                        }
                                                        {
                                                            contestObj.prize_type == '2' &&
                                                            <img src={Images.COINIMG} alt="coin-img" />
                                                        }
                                                        {prize.amount}
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
            </div>
        );
    }
}

export default NetworkGame;
