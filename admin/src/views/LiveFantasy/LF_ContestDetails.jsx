import React, { Component, Fragment } from "react";
import { Row, Col, Table, Modal, ModalBody } from 'reactstrap';
import _ from 'lodash';
import Pagination from "react-js-pagination";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import * as NC from "../../helper/NetworkingConstants";
import "react-datepicker/dist/react-datepicker.css";
import Images from '../../components/images';
import HF, { _isEmpty, _isNull } from '../../helper/HelperFunction';
import { MomentDateComponent } from "../../components/CustomComponent";
import BenchPlayer from "../BenchPlayer/BenchPlayer";
import LS from 'local-storage';
import Loader from '../../components/Loader';
export default class LF_ContestDetails extends Component {
    constructor(props) {
        super(props)
        this.state = {
            Total: 0,
            PERPAGE: 10,
            CURRENT_PAGE: 1,
            isLineupModalOpen: false,
            gameDetail: [],
            LeagueDetail: [],
            MatchDetail: [],
            MatchList: [],
            SportDetail: [],
            UserData: [],
            GameLinupDetail: [],
            LinupUpData: [],
            slectedLineup:{},
            UserType: '',
            MerchandiseList: [],
            TotalMerchadiseDistri: 0,
            AllowSystemUser: (!_.isUndefined(HF.getMasterData().pl_allow) && HF.getMasterData().pl_allow == '1') ? true : false,
            contest_template_id: this.props.match.params.contest_template_id ? this.props.match.params.contest_template_id : false,
            selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
            BenchPly: [],
            BenchLoad: true,
        }
    }
    componentDidMount() {
        // this.getGameDetail()
        if (this.state.contest_template_id) {
            this.GetContestTemplateDetails()

        }
        else {
            this.getGameDetail()

        }
    }
    GetContestTemplateDetails = () => {
        this.setState({ posting: true })
        WSManager.Rest(NC.baseURL + NC.LF_GET_COPPIED_CONTEST_TEMPLATE_DETAILS, { "contest_template_id": this.state.contest_template_id }).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({ gameDetail: responseJson.data, prize_distibution_detail: responseJson.data.prize_distibution_detail }, () => {
                })
            }
            this.setState({ posting: false })
        })

    }

    lineupDetailModal(ApiCall, lineup) {
        this.setState(prevState => ({
            isLineupModalOpen: !prevState.isLineupModalOpen
        }), () => {
            if (ApiCall) { this.getLinupDetails(lineup) }
            else
                this.setState({ 
                    LinupUpData: [],
                    BenchPly: [],
                    BenchLoad: true,
                 })
        });
    }

    getGameDetail = () => {
        let params = {
            contest_unique_id: this.props.match.params.id
        }
        WSManager.Rest(NC.baseURL + NC.LF_GET_GAME_DETAIL, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    gameDetail: ResponseJson.data.contest_detail,
                    LeagueDetail: ResponseJson.data.league_detail,
                    MatchDetail: ResponseJson.data.match_detail,
                    MatchList: ResponseJson.data.match_list,
                    SportDetail: ResponseJson.data.sport_detail,
                    UserData: ResponseJson.data.user_data,
                    prize_distibution_detail: (!_.isEmpty(ResponseJson.data.contest_detail) && !_.isUndefined(ResponseJson.data.contest_detail.prize_distibution_detail)) ? ResponseJson.data.contest_detail.prize_distibution_detail : [],
                    MerchandiseList: ResponseJson.data.merchandise_list ? ResponseJson.data.merchandise_list : [],
                }, () => {
                    this.getTotMerchandiseDist()
                    this.getGameLinupDetail(this.state.gameDetail)
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }
    getGameLinupDetail = (gameDetail) => {
        const { PERPAGE, CURRENT_PAGE, UserType } = this.state
        let params = {
            "items_perpage": PERPAGE,
            "total_items": 0,
            "current_page": CURRENT_PAGE,
            "contest_id": gameDetail.contest_id,
            "sort_field": "game_rank",
            "sort_order": "ASC"
        }
        if (UserType !== '') {
            params.is_systemuser = UserType
        }

        WSManager.Rest(NC.baseURL + NC.LF_GET_GAME_LINEUP_DETAIL, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    GameLinupDetail: ResponseJson.data.result,
                    Total: ResponseJson.data.total
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }
    getLinupDetails = (lineup) => {
        let params = {
            'user_team_id': lineup.user_team_id,
            'user_contest_id': lineup.user_contest_id
        }
        WSManager.Rest(NC.baseURL + NC.LF_GET_USER_PRIDICTION, params).then((ResponseJson) => {
            if (ResponseJson.response_code === NC.successCode) {
                this.setState({
                    LinupUpData: ResponseJson.data,
                    BenchPly: ResponseJson.data ? ResponseJson.data.bench : [],
                    BenchLoad: false,
                    slectedLineup:lineup

                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        })
    }
    handlePageChange(current_page) {
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            this.getGameLinupDetail(this.state.gameDetail);
        });
    }

    handleUserClick(utype) {
        if (utype === this.state.UserType) {
            utype = ''
        }
        this.setState({
            UserType: utype
        }, () => {
            this.getGameLinupDetail(this.state.gameDetail);
        });
    }

    getTotMerchandiseDist = () => {
        let { MerchandiseList } = this.state
        if (!_.isEmpty(MerchandiseList)) {
            let mArr = _.keyBy(MerchandiseList, 'merchandise_id')
            let TotMerDis = 0
            if (!_.isEmpty(this.state.prize_distibution_detail)) {
                this.state.prize_distibution_detail.map((pData) => {
                    if (parseInt(pData.prize_type) === 3) {
                        let mid = parseInt(pData.amount)
                        TotMerDis += (mArr[mid].price * ((parseInt(pData.max) - parseInt(pData.min)) + 1))
                    }
                });

                this.setState({ TotalMerchadiseDistri: TotMerDis })
            }
        }
    }

    getOver = (over_ball,status) => {
        var exStr = ''
        if(status){
            exStr = this._decimalCount(over_ball, status);
            if (_isEmpty(exStr)) {
                exStr = over_ball;
            }
        }
        else{
            if (!_isEmpty(over_ball) && !_isEmpty(this._decimalCount(over_ball, status))) {
                exStr = '(Ex' + this._decimalCount(over_ball, status) + ')';
            }
        }
       
        return exStr!= '' ? exStr== "(Ex0)"? '': exStr :over_ball;
    }
    _decimalCount = (num, return_flg) => {
        let newStr = num.toString()
        let res = '';
        if (newStr.includes('.')) {
            var count = newStr.split('.')[1].length
            if (count > 1) {
                if (return_flg) {
                    res = newStr.substring(0, newStr.length - 1);
                } else {
                    res = newStr.charAt(newStr.length - 1);
                }
            }
        }
        return res;
    }
    render() {
        const { gameDetail, LeagueDetail, MatchDetail, MatchList, SportDetail, UserData, prize_distibution_detail, GameLinupDetail, LinupUpData, CURRENT_PAGE, PERPAGE, Total, UserType, TotalMerchadiseDistri, AllowSystemUser, contest_template_id, selected_sport, BenchPly, BenchLoad ,slectedLineup} = this.state
        var isFromContest = contest_template_id ? false : true
        return (
            <Fragment>
                <div className="contest-d-main">
                    <Row className="mt-3 mb-3">
                        <Col md={12}>
                            <h1 className="h1-cls">{isFromContest ? 'Contest Detail Informations' : 'Contest Template Detail Informations'}</h1>
                        </Col>
                    </Row>
                    <div className="details-box">
                        <Row className="box-items mt-3">
                            <Col md={3}>
                                <label>Contest Name</label>
                                <div className="user-value">
                                    {isFromContest ? gameDetail.contest_name : gameDetail.template_name}
                                    {/* {
                                        gameDetail.is_reverse == "1" &&
                                        <img className="reverse-contest" title="Reverse contest" src={Images.REVERSE_FANTASY} />
                                    } */}
                                </div>
                            </Col>
                            <Col md={3}>
                                <label>Contest Title</label>
                                <div className="user-value">
                                    {isFromContest ? gameDetail.contest_title : gameDetail.template_title ? gameDetail.template_title : '--'}
                                </div>
                            </Col>
                            {
                                isFromContest &&
                                <Col md={3}>
                                    <label>League</label>
                                    <div className="user-value">
                                        {isFromContest ? LeagueDetail.league_name ? LeagueDetail.league_name : '--' : '--'}
                                    </div>
                                </Col>
                            }
                            {
                                (isFromContest && gameDetail.sports_id != 9) && (
                                    <Col md={3}>
                                        <label>Match</label>
                                        <div className="user-value">
                                            {isFromContest ? gameDetail.collection_name + ' ' + MatchDetail.format : gameDetail.template_description ? gameDetail.template_description : '--'}
                                        </div>
                                    </Col>
                                )
                            }

                            <Col md={3} className={` ${isFromContest ? 'mt-3' : ''}`}>
                                <label>{isFromContest ? 'Entrants / Participants' : 'Min / Total Participants'}</label>
                                <div className="user-value">{isFromContest ? gameDetail.total_user_joined : gameDetail.minimum_size}
                                    /
                                {gameDetail.size != -1 && gameDetail.size}
                                </div>
                            </Col>
                            <Col md={3} className={` ${isFromContest ? 'mt-3' : ''}`}>
                                <label>Entry Fee</label>
                                <div className="user-value">
                                    {
                                        gameDetail.currency_type == '0' && gameDetail.entry_fee > 0 &&
                                        <span><i className="icon-bonus"></i>{HF.getNumberWithCommas(gameDetail.entry_fee)}</span>
                                    }
                                    {
                                        gameDetail.currency_type == '1' && gameDetail.entry_fee > 0 &&
                                        <span><i className="icon-rupess"></i>{HF.getNumberWithCommas(gameDetail.entry_fee)}</span>
                                    }
                                    {
                                        gameDetail.currency_type == '2' && gameDetail.entry_fee > 0 &&
                                        <span><img src={Images.COINIMG} alt="coin-img" />{HF.getNumberWithCommas(gameDetail.entry_fee)}</span>
                                    }
                                    {gameDetail.entry_fee == 0 &&
                                        <span>Free</span>
                                    }
                                </div>
                            </Col>
                            <Col md={3} className="mt-3">
                                <label>Site Rake</label>
                                <div className="user-value">{HF.getNumberWithCommas(gameDetail.site_rake)}</div>
                            </Col>
                            {/* <Col md={3} className="mt-3">
                                <label>Multiple lineup / Lineup Count</label>
                                {
                                    gameDetail.multiple_lineup != '0' && (
                                        <div className="user-value">Yes / {gameDetail.multiple_lineup}
                                        </div>
                                    )
                                }

                            </Col> */}

                            <Col md={3} className="mt-3">
                                <label>Auto Recurrent  </label>
                                {gameDetail.is_auto_recurring == '0' ?
                                    <div className="user-value">No</div>
                                    :
                                    <div className="user-value">Yes</div>
                                }
                            </Col>
                            {
                                isFromContest &&
                                <Col md={3} className="mt-3">
                                    <label>Scheduled Date</label>
                                    <div className="user-value">
                                        {/* <MomentDateComponent data={{ date: gameDetail.season_scheduled_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                                        {HF.getFormatedDateTime(gameDetail.season_scheduled_date, "D-MMM-YYYY hh:mm A")}
                                    
                                    </div>
                                </Col>
                            }
                            <Col md={3} className="mt-3">
                                <label>Created Date </label>
                                <div className="user-value">
                                    {/* <MomentDateComponent data={{ date: gameDetail.added_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                                    {HF.getFormatedDateTime(gameDetail.added_date, "D-MMM-YYYY hh:mm A")}

                                </div>
                            </Col>
                            {
                                isFromContest &&
                                <Col md={3} className="mt-3">
                                    <label>Completed Date </label>
                                    {gameDetail.completed_date &&
                                        <div className="user-value">
                                            {/* <MomentDateComponent data={{ date: gameDetail.completed_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                                    {HF.getFormatedDateTime(gameDetail.completed_date, "D-MMM-YYYY hh:mm A")}

                                        </div>}
                                </Col>
                            }
                            {
                                isFromContest &&
                                <Col md={3} className="mt-3">
                                    <label>Created By</label>
                                    <div className="user-value">

                                        {UserData.user_name === null || UserData.user_name == ''
                                            ?
                                            'ADMIN'
                                            :
                                            UserData.user_name
                                        }
                                    </div>
                                </Col>
                            }
                            {
                                isFromContest &&
                                <Col md={3} className="mt-3">
                                    <label>Status</label>
                                    <div className="user-value">
                                        {gameDetail.status == '0' && 'Open'}
                                        {gameDetail.status == '1' && 'Cancel'}
                                        {gameDetail.status == '2' && 'Coming'}
                                        {gameDetail.status == '3' && 'Prize Distributed'}
                                    </div>
                                </Col>
                            }
                            <Col md={3} className="mt-3">
                                <label>Prize Pool</label>
                                <div className="user-value">
                                    {
                                        gameDetail.currency_type == '0' && gameDetail.entry_fee > 0 &&
                                        <i className="icon-bonus"></i>
                                    }
                                    {
                                        gameDetail.currency_type == '1' && gameDetail.entry_fee > 0 &&
                                        <i className="icon-rupess"></i>
                                    }
                                    {
                                        gameDetail.currency_type == '2' && gameDetail.entry_fee > 0 &&
                                        <img src={Images.COINIMG} alt="coin-img" />
                                    }
                                    {HF.getNumberWithCommas(gameDetail.prize_pool)}
                                </div>
                            </Col>
                            <Col md={3} className="mt-3">
                                <label>Max Bonus Allowed (%)</label>
                                <div className="user-value">{gameDetail.max_bonus_allowed}</div>
                            </Col>
                            {/* <Col md={3} className="mt-3">
                                <label>Salary Cap </label>
                                {!isFromContest && <div className="user-value">{gameDetail.salary_cap}</div>}
                                {isFromContest && <div className="user-value">{gameDetail.collection_salary_cap}</div>}
                            </Col> */}
                            <Col md={3} className="mt-3">
                                <label>Contest Type</label>
                                <div className="user-value">{
                                    gameDetail.prize_pool_type == '1' ? 'Auto' : gameDetail.prize_pool_type == '2' ? 'Guaranteed' : '--'
                                }</div>
                            </Col>
                            {isFromContest && <Col md={3} className="mt-3">
                                <label>Contest Over</label>
                                <div className="user-value">Inn {gameDetail.inning} Over {gameDetail.overs}</div>
                            </Col>}
                            <Col md={3} className="mt-3">
                                <label>Tie-breaker</label>
                                <div className="user-value">{
                                    gameDetail.is_tie_breaker == '1' ? 'Yes' : gameDetail.is_tie_breaker == '0' ? 'No' : '--'
                                }</div>
                            </Col>
                        </Row>
                    </div>
                    {
                        (TotalMerchadiseDistri > 0) &&
                        <Row className="mb-1 sponser-section">
                            <Col xs="12" lg="12">
                                <div className="mer-dis-note">
                                    <span className="font-weight-bold mr-2">Note:</span>
                                    Merchandise value {HF.getCurrencyCode()}{TotalMerchadiseDistri} will be extra apart from total real cash distribution in this contest.
                                </div>
                            </Col>
                        </Row>
                    }
                    <Row>
                        <Col md={12} className="table-responsive common-table">
                            <Table>
                                <thead>
                                    <tr className="text-center">
                                        <th>Min</th>
                                        <th>Max</th>
                                        <th>%</th>
                                        <th>Amount (Per Person)</th>
                                    </tr>
                                </thead>
                                {
                                    _.map(prize_distibution_detail, (item, idx) => {
                                        return (
                                            <tbody key={idx}>
                                                <tr>
                                                    <td className="text-center">{item.min}</td>
                                                    <td className="text-center">{item.max}</td>
                                                    <td className="text-center">
                                                        {(item.per != 'Infinity' && item.prize_type != '3') && item.per}
                                                        {(item.per != 'Infinity' && item.prize_type == '3') && '--'}
                                                        {_isNull(item.per) && '0'}
                                                        {(item.per == 'Infinity') && '0'}
                                                    </td>
                                                    <td className="text-center">

                                                        {
                                                            item.prize_type == "0" &&
                                                            <span className="mr-1"><i className="icon-bonus1 mr-1"></i></span>
                                                        }
                                                        {
                                                            item.prize_type == "1" &&
                                                            <span className="mr-1">{HF.getCurrencyCode()}</span>
                                                        }
                                                        {
                                                            item.prize_type == "2" &&
                                                            <span>
                                                                <img className="mr-1" src={Images.REWARD_ICON} alt="" />
                                                            </span>
                                                        }

                                                        {item.prize_type != '3' && item.amount}
                                                        {item.prize_type == '3' && item.min_value}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        )
                                    })
                                }
                            </Table>
                        </Col>
                    </Row>
                    {
                        isFromContest &&
                        <Fragment>
                            <Row className="mt-3 mb-3">
                                <Col md={12}>
                                    <h3 className="h3-cls">Match List</h3>
                                </Col>
                            </Row>
                            <Row>
                                <Col md={12} className="table-responsive common-table">
                                    <Table>
                                        <thead>
                                            <tr className="text-center">
                                                <th>Match</th>
                                                <th>Schedule Date</th>
                                                {
                                                    (HF.allowSecondInni() == "1" && gameDetail.is_2nd_inning == "1" && selected_sport == '7') &&
                                                    <th>Second Inning Date</th>
                                                }
                                            </tr>
                                        </thead>
                                        {
                                            _.map(MatchList, (lineup, idx) => {
                                                return (
                                                    <tbody>
                                                        <tr>
                                                            <td className="text-center">{lineup.home + ' - ' + lineup.away}</td>
                                                            <td className="text-center">
                                                                {/* {WSManager.getUtcToLocalFormat(lineup.season_scheduled_date, 'D-MMM-YYYY hh:mm A')} */}
                                                                {HF.getFormatedDateTime(lineup.season_scheduled_date, 'D-MMM-YYYY hh:mm A')}
                                                            </td>
                                                            {
                                                                (HF.allowSecondInni() == "1" && gameDetail.is_2nd_inning == "1" && selected_sport == '7') &&
                                                                <td className="text-center">
                                                                    {/* {WSManager.getUtcToLocalFormat(lineup['2nd_inning_date'], 'D-MMM-YYYY hh:mm A')} */}
                                                                    {HF.getFormatedDateTime(lineup['2nd_inning_date'], 'D-MMM-YYYY hh:mm A')}
                                                                </td>
                                                            }
                                                        </tr>
                                                    </tbody>
                                                )
                                            })
                                        }
                                    </Table>
                                </Col>
                            </Row>
                            <Row className="mt-3 mb-3">
                                <Col md={4}>
                                    <h3 className="h3-cls">Participants</h3>
                                </Col>
                            </Row>
                            <Row>
                                <Col md={12} className="table-responsive common-table">
                                    <Table>
                                        <thead>
                                            <tr>
                                                <th>User Name</th>
                                                <th></th>
                                                <th>Rank</th>
                                                <th>Score</th>
                                                <th>Winning Amount</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        {
                                            _.map(GameLinupDetail, (lineup, idx) => {
                                                return (
                                                    <tbody key={idx}>
                                                        <tr>
                                                            <td>{lineup.user_name}
                                                                {lineup.is_systemuser == "1" &&
                                                                    <span className="cont-su-flag">S</span>}

                                                            </td>
                                                            <td> {
                                                                lineup.is_pl_team && lineup.is_pl_team == '1' &&
                                                                <img style={{ marginLeft: -70 }} src={Images.PL_LOGO} alt=''></img>
                                                            }</td>
                                                            <td>{lineup.game_rank}</td>
                                                            <td>{lineup.total_score}</td>
                                                            <td>
                                                                {
                                                                    lineup.is_winner == "1" ?
                                                                        lineup.prize_data != null ?
                                                                            _.map(lineup.prize_data, (item, idx) => {
                                                                                return (
                                                                                    <Fragment>
                                                                                        {
                                                                                            item.prize_type == "0" &&
                                                                                            <span className="mr-1"><i className="icon-bonus1 mr-1"></i>{item.amount}</span>
                                                                                        }
                                                                                        {
                                                                                            item.prize_type == "1" &&
                                                                                            <span className="mr-1">{HF.getCurrencyCode()}{item.amount}</span>
                                                                                        }
                                                                                        {
                                                                                            item.prize_type == "2" &&
                                                                                            <span>
                                                                                                <img className="mr-1" src={Images.REWARD_ICON} alt="" />{item.amount}
                                                                                            </span>
                                                                                        }
                                                                                        {
                                                                                            item.prize_type == "3" &&
                                                                                            <span className="mr-1">{item.name}</span>
                                                                                        }
                                                                                    </Fragment>
                                                                                )
                                                                            })
                                                                            :
                                                                            <span className="mr-1">
                                                                                {HF.getCurrencyCode()}{lineup.winning_amount}
                                                                            </span>
                                                                        :
                                                                        ''
                                                                }
                                                            </td>
                                                            <td onClick={() => this.lineupDetailModal(true,lineup)}>
                                                                <span className="linup-details">Prediction Details</span></td>
                                                        </tr>
                                                    </tbody>
                                                )
                                            })
                                        }
                                    </Table>
                                </Col>
                            </Row>
                            <div className="custom-pagination userlistpage-paging float-right mb-5">
                                <Pagination
                                    activePage={CURRENT_PAGE}
                                    itemsCountPerPage={PERPAGE}
                                    totalItemsCount={Total}
                                    pageRangeDisplayed={5}
                                    onChange={e => this.handlePageChange(e)}
                                />
                            </div>
                            <div>
                                <Modal isOpen={this.state.isLineupModalOpen} toggle={() => this.lineupDetailModal(false)} className="lineup-details modal-md">
                                    <ModalBody className="p-0">

                                        <div className="lineup-teams theme-color">
                                            <Row>
                                                <Col xs={12}>
                                                    <ul className="lineup-feelist">
                                                        <li className="lineup-feeitem">
                                                            <label>Over</label>
                                                            <div className="font-weight-bold">{"Innings " + slectedLineup.inning + " Over" + slectedLineup.overs}</div>
                                                        </li>
                                                        <li className="lineup-feeitem">
                                                            <label>Username</label>
                                                            <div className="font-weight-bold">{slectedLineup.user_name}</div>
                                                        </li>
                                                        <li className="lineup-feeitem">
                                                            <label>Total Points </label>
                                                            <div className="font-weight-bold">{slectedLineup.score ? slectedLineup.score : '0' }</div>
                                                        </li>
                                                    </ul>
                                                </Col>
                                            </Row>
                                        </div>
                                        <Col xs={12}>
                                            <Row className="rank-box">
                                                <Col xs={3}>
                                                    <h3 className="h3-cls">Rank {' '} {slectedLineup.game_rank}</h3>
                                                </Col>
                                                <Col xs={9}>
                                                    <h3 className="h3-cls">Winnings {' '}
                                                        {
                                                            slectedLineup.is_winner == "1" ?
                                                            slectedLineup.prize_data != null ?
                                                                    _.map(slectedLineup.prize_data, (item, idx) => {
                                                                        return (
                                                                            <Fragment>
                                                                                {
                                                                                    item.prize_type == "0" &&
                                                                                    <span className="mr-1"><i className="icon-bonus1 mr-1"></i>{item.amount}</span>
                                                                                }
                                                                                {
                                                                                    item.prize_type == "1" &&
                                                                                    <span className="mr-1">{HF.getCurrencyCode()}{item.amount}</span>
                                                                                }
                                                                                {
                                                                                    item.prize_type == "2" &&
                                                                                    <span>
                                                                                        <img className="mr-1" src={Images.REWARD_ICON} alt="" />{item.amount}
                                                                                    </span>
                                                                                }
                                                                                {
                                                                                    item.prize_type == "3" &&
                                                                                    <span className="mr-1">{item.name}</span>
                                                                                }
                                                                            </Fragment>
                                                                        )
                                                                    })
                                                                    :
                                                                    slectedLineup.won_amount > "0" &&
                                                                    <span className="mr-1">
                                                                        {HF.getCurrencyCode()}{slectedLineup.won_amount}
                                                                    </span>

                                                                :
                                                                ''
                                                        }

                                                    </h3>
                                                </Col>
                                            </Row>
                                        </Col>
                                        <Row className="mb-5">
                                            <Col md={12}>
                                                <div className="table-responsive common-table">
                                                    <Table>
                                                        <thead>
                                                            <tr>
                                                                <th className="pl-4">Over</th>
                                                                <th>Prediction</th>
                                                                <th>Second Prediction</th>
                                                                <th>Result</th>
                                                                <th>Points</th>
                                                            </tr>
                                                        </thead>
                                                        {
                                                            _.map(LinupUpData, (lineup, idx) => {
                                                                return (
                                                                    <tbody key={idx}>
                                                                        <tr>
                                                                            <td className="pl-4">
                                                                            {this.getOver(((parseFloat(lineup.over_ball)-1).toFixed(2)),true)} {this.getOver(lineup.over_ball,false)}
                                                                                     </td>
                                                                            <td>{lineup.prediction ? lineup.prediction : '--'}</td>
                                                                            <td>{lineup.second_prediction && lineup.second_prediction!='' ? lineup.second_prediction : '--'}</td>

                                                                            <td className="pl-4">{lineup.is_correct == 0 ? '--' :lineup.is_correct == 1 ? 'Right': 'Wrong'}</td>
                                                                            {/* <td>{lineup.team_abbr}</td> */}
                                                                            <td>{lineup.points}</td>
                                                                        </tr>
                                                                    </tbody>
                                                                )
                                                            })
                                                        }
                                                    </Table>
                                                </div>
                                            </Col>
                                        </Row>
                                        {
                                    (HF.allowBenchPlyer() == '1') &&
                                            <div className="bench">
                                                {
                                                    (BenchLoad) ?
                                                        <Loader hide />
                                                        :
                                                        <BenchPlayer data={BenchPly ? BenchPly : []} />
                                                }
                                            </div>
                                        }
                                        
                                    </ModalBody>
                                </Modal>
                            </div>
                        </Fragment>
                    }
                </div>
            </Fragment>
        )
    }
}