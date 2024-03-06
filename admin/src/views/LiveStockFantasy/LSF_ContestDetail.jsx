import React, { Component, Fragment } from "react";
import { Row, Col, Table, Modal, ModalBody } from 'reactstrap';
import _ from 'lodash';
import Pagination from "react-js-pagination";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import * as NC from "../../helper/NetworkingConstants";
import "react-datepicker/dist/react-datepicker.css";
import Images from '../../components/images';
import HF, { _isNull, _isEmpty, _isUndefined, _Map } from '../../helper/HelperFunction';
import { MomentDateComponent } from "../../components/CustomComponent";
import { SP_GET_GAME_DETAIL, SP_GET_GAME_LINEUP_DETAIL } from "../../helper/WSCalling";
import queryString from 'query-string';
export default class LSF_ContestDetails extends Component {
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
            UserType: '',
            MerchandiseList: [],
            TotalMerchadiseDistri: 0,
            AllowSystemUser: (!_.isUndefined(HF.getMasterData().pl_allow) && HF.getMasterData().pl_allow == '1') ? true : false,
            contest_template_id: this.props.match.params.contest_template_id ? this.props.match.params.contest_template_id : false,
            activeTab: '',
            TemplateCategory: '',
            LineupDetail: []
        }
    }
    componentDidMount() {
        if (HF.allowLiveStockFantasy() == '0') {
            notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        // this.getGameDetail()

        let values = queryString.parse(this.props.location.search)
        this.setState({
            activeTab: !_isEmpty(values) ? values.tab : '',
        })
        if (this.state.contest_template_id) {
            this.GetContestTemplateDetails()

        }
        else {
            this.getGameDetail()

        }
    }
    GetContestTemplateDetails = () => {
        this.setState({ posting: true })
        WSManager.Rest(NC.baseURL + NC.SF_GET_CONTEST_TEMPLATE_DETAILS, { "contest_template_id": this.state.contest_template_id }).then((responseJson) => {
            let rdata = responseJson.data ? responseJson.data : []
            let cat_name = ''

            _Map(rdata.template_categories, (itm) => {
                cat_name += rdata.categories[itm] + ','
            })


            if (responseJson.response_code === NC.successCode) {
                this.setState({
                    gameDetail: responseJson.data,
                    prize_distibution_detail: responseJson.data.prize_distibution_detail,
                    TemplateCategory: HF.removeLastComma(cat_name),
                }, () => {
                })
            }
            this.setState({ posting: false })
        })

    }

    lineupDetailModal(ApiCall, lineupdata) {
        this.setState(prevState => ({
            isLineupModalOpen: !prevState.isLineupModalOpen,
            LinupUpData: lineupdata ? lineupdata : []
        }), () => {
            if (ApiCall) { this.getLinupDetails(lineupdata) }
            else
                this.setState({ LinupUpData: [] })
        });
    }

    getGameDetail = () => {
        let params = {
            contest_id: this.props.match.params.id,
        }
        SP_GET_GAME_DETAIL(params).then(ResponseJson => {
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
            "game_id": gameDetail.contest_id,
            "sort_field": "game_rank",
            "sort_order": "ASC"
        }
        if (UserType !== '') {
            params.is_systemuser = UserType
        }

        SP_GET_GAME_LINEUP_DETAIL(params).then(ResponseJson => {
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

    getLinupDetails = (lineupdata) => {
        let params = {
            'lineup_master_id': lineupdata.lineup_master_id,
            'contest_id': lineupdata.contest_id
        }
        WSManager.Rest(NC.baseURL + NC.LSF_GET_LINEUP_DETAIL, params).then((ResponseJson) => {
            if (ResponseJson.response_code === NC.successCode) {
                this.setState({
                    LineupDetail: ResponseJson.data,
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

    getPrizeType = (pt) => {
        let prize_text = "";
        if (pt == "0")
            prize_text = '<i class="icon-bonus1 mr-1"></i>'

        else if (pt == "1")
            prize_text = HF.getCurrencyCode()

        else if (pt == "2")
            prize_text = '<img src="' + Images.COINIMG + '" alt="coin-img" />'

        return { __html: prize_text };

    }

    render() {
        const { gameDetail, LeagueDetail,LineupDetail, MatchDetail, MatchList, SportDetail, UserData, prize_distibution_detail, GameLinupDetail, LinupUpData, CURRENT_PAGE, PERPAGE, Total, UserType, TotalMerchadiseDistri, AllowSystemUser, contest_template_id, activeTab, TemplateCategory } = this.state
        var isFromContest = contest_template_id ? false : true
        return (
            <Fragment>
                <div className="spContestDtl">
                    <Row className="mt-3 mb-3">
                        <Col md={12}>
                            <h2 className="h2-cls">{isFromContest ? 'Contest Detail Informations' : 'Contest Template Detail Informations'}</h2>
                        </Col>
                    </Row>
                    <div className="details-box">
                        <Row className="box-items mt-3">
                            <Col md={3}>
                                <label>Contest Name</label>
                                <div className="user-value">
                                    {isFromContest ? gameDetail.contest_name : gameDetail.contest_name}
                                </div>
                            </Col>
                            <Col md={3}>
                                <label>Contest Title</label>
                                <div className="user-value">
                                    {isFromContest ? gameDetail.contest_title : gameDetail.template_title ? gameDetail.contest_title : '--'}
                                </div>
                            </Col>
                            <Col md={3}>
                                <label>Candle Details</label>
                                <div className="user-value">
                                    {HF.getFormatedDateTime(gameDetail.scheduled_date, 'D-MMM-YYYY hh:mm A')} - <br/> {HF.getFormatedDateTime(gameDetail.end_date, 'D-MMM-YYYY hh:mm A')}
                                </div>
                            </Col>
                            <Col md={3}>
                                <label>Entry Fee</label>
                                <div className="user-value">
                                    {
                                        gameDetail.currency_type == '0' && gameDetail.entry_fee > 0 &&
                                        <span>{HF.getCurrencyCode()}{HF.getNumberWithCommas(gameDetail.entry_fee)}</span>
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
                            <Col md={3} className="mt-3">
                                <label>Multiple Portfolios / Portfolio Count</label>
                                {
                                    gameDetail.multiple_lineup != '0' && (
                                        <div className="user-value">No / 0
                                        </div>
                                    )
                                }

                            </Col>

                            <Col md={3} className="mt-3">
                                <label>Auto Recurrent  </label>
                                {gameDetail.is_auto_recurring == '0' ?
                                    <div className="user-value">No</div>
                                    :
                                    <div className="user-value">Yes</div>
                                }
                            </Col>
                            <Col md={3} className={` ${isFromContest ? 'mt-3' : ''}`}>
                                <label>{isFromContest ? 'Entrants / Participants' : 'Min / Total Participants'}</label>
                                <div className="user-value">{isFromContest ? gameDetail.total_user_joined : gameDetail.minimum_size}
                                    /
                                {gameDetail.size != -1 && gameDetail.size}
                                </div>
                            </Col>
                            <Col md={3} className="mt-3">
                                <label>Contest Type</label>
                                <div className="user-value">{
                                    gameDetail.prize_pool_type == '1' ? 'Auto' : gameDetail.prize_pool_type == '2' ? 'Guaranteed' : '--'
                                }</div>
                            </Col>
                            <Col md={3} className="mt-3">
                                <label>Tie-breaker</label>
                                <div className="user-value">{
                                    gameDetail.is_tie_breaker == '1' ? 'Yes' : gameDetail.is_tie_breaker == '0' ? 'No' : '--'
                                }</div>
                            </Col>
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
                                        HF.getCurrencyCode()
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
                                <label>Created Date </label>
                                <div className="user-value">
                                    {/* <MomentDateComponent data={{ date: gameDetail.added_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                    {HF.getFormatedDateTime(gameDetail.added_date, "D-MMM-YYYY hh:mm A")}

                                </div>
                            </Col>

                            <Col md={3} className="mt-3">
                                <label>Site Rake</label>
                                <div className="user-value">{HF.getNumberWithCommas(gameDetail.site_rake)}</div>
                            </Col>
                            <Col md={3} className="mt-3">
                                <label>Brokerage</label>
                                <div className="user-value">{HF.getNumberWithCommas(gameDetail.brokerage) || '0'}%</div>
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
                                        <th>No.of Users</th>
                                        <th>Accumulated Amount</th>
                                        <th>%</th>
                                        <th>Amount (Per Person)</th>
                                    </tr>
                                </thead>
                                {
                                    _.map(prize_distibution_detail, (item, idx) => {
                                        let n_user = (parseInt(item.max) - parseInt(item.min) + 1)
                                        let accu_amount = parseFloat(n_user * item.amount).toFixed(2)

                                        if (item.prize_type == '3')
                                            accu_amount = item.min_value

                                        return (
                                            <tbody key={idx}>
                                                <tr>
                                                    <td className="text-center">{item.min}</td>
                                                    <td className="text-center">{item.max}</td>
                                                    <td className="text-center">{n_user}</td>
                                                    <td className="text-center">
                                                        <span dangerouslySetInnerHTML={this.getPrizeType(item.prize_type)}>
                                                        </span>
                                                        {accu_amount}
                                                    </td>
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
                                                            <td>{lineup.user_name}</td>
                                                            <td>{lineup.game_rank}</td>
                                                            <td>{lineup.total_score}</td>
                                                            <td>
                                                                {
                                                                    lineup.is_winner == "1" ?
                                                                        lineup.prize_data != null ?
                                                                            _.map(lineup.prize_data, (item, idx) => {
                                                                                return (
                                                                                    item != null &&
                                                                                    <Fragment key={idx}>
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
                                                                        '--'
                                                                }
                                                            </td>
                                                            <td onClick={() => this.lineupDetailModal(true, lineup)}>
                                                                <span className="linup-details">Portfolio Details</span></td>
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
                                                            <label>Portfolio Name</label>
                                                            <div className="font-weight-bold">{LinupUpData.team_name}</div>
                                                        </li>
                                                        <li className="lineup-feeitem">
                                                            <label>Username</label>
                                                            <div className="font-weight-bold">{LinupUpData.user_name}</div>
                                                        </li>
                                                        <li className="lineup-feeitem">
                                                            <label>Total Amount </label>
                                                            <div className="font-weight-bold">
                                                                {LinupUpData.total_score || '--'}
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </Col>
                                            </Row>
                                        </div>
                                        <Col xs={12}>
                                            <Row className="rank-box lsf-rank-box">
                                                <Col xs={3}>
                                                    <div className="h3-cls"> 
                                                        <div className="val">{LinupUpData.game_rank}</div>
                                                        <div className="lbl">Rank  </div>
                                                    </div>
                                                    {/* <h3 className="h3-cls bg">Rank {' '} {LinupUpData.game_rank}</h3> */}
                                                </Col>
                                                <Col xs={3}>
                                                    {/* <h3 className="h3-cls bg">Winnings {' '} <br /> */}
                                                    <div className="h3-cls"><div className="val">{
                                                            LinupUpData.is_winner == "1" ?
                                                                LinupUpData.prize_data != null ?
                                                                    _.map(LinupUpData.prize_data, (item, idx) => {
                                                                        return (
                                                                            <Fragment key={idx}>
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
                                                                    LinupUpData.won_amount > "0" &&
                                                                    <span className="mr-1">
                                                                        {HF.getCurrencyCode()}{LinupUpData.won_amount}
                                                                    </span>

                                                                :
                                                                '--'
                                                        }</div>
                                                        <div className="lbl">Winnings</div>
                                                    </div>
                                                    {/* </h3> */}
                                                </Col>
                                                <Col xs={3}>
                                                    <div className="h3-cls">
                                                        <div className="val">
                                                        {HF.getFormatedDateTime(gameDetail.scheduled_date, 'D MMM YYYY hh:mm A')} IST
                                                        </div>
                                                        <div className="lbl">Order Detail</div>
                                                    </div>
                                                </Col>
                                                <Col xs={3}>
                                                    <div className="h3-cls">
                                                        <div className="val">
                                                            {HF.getFormatedDateTime(gameDetail.end_date, 'D MMM YYYY hh:mm A')} IST
                                                        </div>
                                                        <div className="lbl">Closure Detail</div>
                                                    </div>
                                                </Col>
                                            </Row>
                                        </Col>
                                        <Row className="mb-5">
                                            <Col md={12}>
                                                <div className="table-responsive common-table">
                                                    <Table>
                                                        <thead>
                                                            <tr>
                                                                <th className="pl-4">Stock Display Name</th>
                                                                <th>Shares Traded</th>
                                                                <th>Trade</th>
                                                                <th>Stock Price</th>
                                                                <th>Invested Amount</th>
                                                                <th>Brokerage Amount</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </thead>
                                                        {
                                                            _.map(LineupDetail, (lineup, idx) => {
                                                                return (
                                                                    <tbody key={idx}>
                                                                        <tr>
                                                                            <td>{lineup.display_name ? lineup.display_name : '--'}</td>
                                                                            <td>{lineup.lot_size || '0'}</td>
                                                                            <td>{lineup.type == '2' ? 'Exit' : lineup.type == '3' ? 'Exit All' : 'Buy'}</td>
                                                                            <td>{lineup.price || '0'}</td>
                                                                            <td>{lineup.trade_value || '0'}</td>
                                                                            <td>{lineup.brokerage || '--'}</td>
                                                                            <td>{lineup.status == 1 ? 'Completed' : lineup.status == 2 ? 'Cancelled' : 'Pending'}</td>
                                                                        </tr>
                                                                    </tbody>
                                                                )
                                                            })
                                                        }
                                                    </Table>
                                                </div>
                                            </Col>
                                        </Row>
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