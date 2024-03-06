import React, { Component, Fragment } from "react";
import { Row, Col, Table, Modal, ModalBody } from 'reactstrap';
import _ from 'lodash';
import Pagination from "react-js-pagination";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import * as NC from "../../helper/NetworkingConstants";
import "react-datepicker/dist/react-datepicker.css";
import Images from '../../components/images';
import HF, { _isNull } from '../../helper/HelperFunction';
import { MomentDateComponent } from "../../components/CustomComponent";
import LS from 'local-storage';
export default class PFTempalteDetail extends Component {
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
            selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
            BenchPly: [],
            BenchLoad: true,
            contest_template_id: '',
            activeUserInfo: []
        }
    }
    componentDidMount() {
        // this.getGameDetail()
        let url = window.location.href;
         if (url.includes('#')) {
           url = url.split('#')[1];
           const contest_template_id = url.split('/')[3];
           this.setState({
            contest_template_id: contest_template_id
            }, ()=>this.getGameDetail(contest_template_id)
           )
        }

        // if (this.state.contest_template_id) {
        //     this.GetContestTemplateDetails()

        // }
        // else {
        //     this.getGameDetail()

        // }
    }
    GetContestTemplateDetails = () => {
        this.setState({ posting: true })
        WSManager.Rest(NC.baseURL + NC.GET_CONTEST_TEMPLATE_DETAILS, { "contest_template_id": this.state.contest_template_id }).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({ gameDetail: responseJson.data, prize_distibution_detail: responseJson.data.prize_distibution_detail }, () => {
                })
            }
            this.setState({ posting: false })
        })

    }

    lineupDetailModal(lineup, lineup_master_contest_ids) {
        
        this.setState(prevState => ({
            isLineupModalOpen: !prevState.isLineupModalOpen
        }), () => {
            if (lineup) { this.getLinupDetails(lineup, lineup_master_contest_ids) }
            else
                this.setState({ 
                    LinupUpData: [],
                    BenchPly: [],
                    BenchLoad: true,
                 })
        });
    }

    getGameDetail = (contest_template_id) => {
        let params = {
            "contest_template_id": contest_template_id
        }
        WSManager.Rest(NC.baseURL + NC.PF_COPY_CONTEST_TEMPLATE, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    gameDetail: ResponseJson.data,
                },()=>{
                  console.log('gameDetail', this.state.gameDetail)
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
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

    render() {
        const { gameDetail, prize_distibution_detail, TotalMerchadiseDistri, selected_sport, } = this.state
      
        return (
            <Fragment>
                <div className="contest-d-main">
                    <Row className="mt-3 mb-3">
                        <Col md={12}>
                            <h1 className="h1-cls">Template Detail Informations</h1>
                        </Col>
                    </Row>
                    <div className="details-box">
                        <Row className="box-items mt-3">
                            <Col md={3}>
                                <label>Contest Name</label>
                                <div className="user-value">
                                    {gameDetail.template_name && gameDetail.template_name}
                                    {/* {
                                        gameDetail.is_reverse == "1" &&
                                        <img className="reverse-contest" title="Reverse contest" src={Images.REVERSE_FANTASY} />
                                    } */}
                                </div>
                            </Col>
                            <Col md={3}>
                                <label>Contest Title</label>
                                <div className="user-value">
                                    {gameDetail.template_title}
                                </div>
                            </Col>
                            <Col md={3}>
                                <label>Max Bonus Allowed (%)</label>
                                <div className="user-value">
                                    {gameDetail.max_bonus_allowed}
                                </div>
                            </Col>
                            <Col md={3} >
                                <label>{'Min / Total Participants'}</label>
                                <div className="user-value">{ gameDetail.minimum_size}/{ gameDetail.size}
                                
                                </div>
                            </Col>
                            {/* {
                                isFromContest &&
                                <Col md={3}>
                                    <label>League</label>
                                    <div className="user-value">
                                        {isFromContest ? LeagueDetail.league_id ? LeagueDetail.league_id : '--' : '--'}
                                    </div>
                                </Col>
                            } */}
                            {/* {
                                (isFromContest && gameDetail.sports_id != 9) && (
                                    <Col md={3}>
                                        <label>Match</label>
                                        <div className="user-value">
                                            {isFromContest ? gameDetail.collection_name + ' ' + MatchDetail.format : gameDetail.template_description ? gameDetail.template_description : '--'}
                                        </div>
                                    </Col>
                                )
                            } */}

                            {/* <Col md={3} >
                                <label>{isFromContest ? 'Entrants / Participants' : 'Min / Total Participants'}</label>
                                <div className="user-value">{isFromContest ? gameDetail.total_user_joined : gameDetail.minimum_size}
                                    /
                                    {gameDetail.size != -1 && gameDetail.size}
                                </div>
                            </Col> */}
                            <Col md={3} className="mt-3">
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
                            <Col md={3} className="mt-3">
                                <label>Multiple lineup / Lineup Count</label>
                                {
                                    gameDetail.multiple_lineup != '0' && (
                                        <div className="user-value">Yes / {gameDetail.multiple_lineup}
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
                            {/* {
                                isFromContest &&
                                <Col md={3} className="mt-3">
                                    <label>Scheduled Date</label>
                                    <div className="user-value">
                                        <MomentDateComponent data={{ date: gameDetail.scheduled_date, format: "D-MMM-YYYY hh:mm A" }} />
                                    </div>
                                </Col>
                            } */}
                            <Col md={3} className="mt-3">
                                <label>Created Date </label>
                                <div className="user-value">
                                    {/* <MomentDateComponent data={{ date: gameDetail.added_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                                    {/* { gameDetail.added_date} */}
                                    {HF.getFormatedDateTime(gameDetail.added_date, "D-MMM-YYYY hh:mm A")}
                                </div>
                            </Col>
                            {/* {
                                isFromContest &&
                                <Col md={3} className="mt-3">
                                    <label>Completed Date </label>
                                    {gameDetail.completed_date &&
                                        <div className="user-value">
                                            <MomentDateComponent data={{ date: gameDetail.completed_date, format: "D-MMM-YYYY hh:mm A" }} />
                                        </div>}
                                </Col>
                            } */}
                            {/* {
                                isFromContest &&
                                <Col md={3} className="mt-3">
                                    <label>Created By</label>
                                    <div className="user-value">
                                        {gameDetail.user_id == '0'
                                            ?
                                            'ADMIN'
                                            : '--'
                                            // gameDetail.user_name
                                        }
                                    </div>
                                </Col>
                            } */}
                            {/* {
                                
                                <Col md={3} className="mt-3">
                                    <label>Contest Mode</label>
                                    <div className="user-value">
                                        {gameDetail.contest_mode_id == '1' && 'TOP 3'}
                                        {gameDetail.contest_mode_id == '2' && 'FAB5'}
                                        {gameDetail.contest_mode_id == '3' && 'SUPER7'}
                                    </div>
                                </Col>
                            } */}
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
                            {/* <Col md={3} className="mt-3">
                                <label>Max Bonus Allowed (%)</label>
                                <div className="user-value">{gameDetail.max_bonus_allowed}</div>
                            </Col> */}
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
                            <Col md={3} className="mt-3">
                                <label>Tie-breaker</label>
                                <div className="user-value">{
                                    gameDetail.is_tie_breaker == '1' ? 'Yes' : gameDetail.is_tie_breaker == '0' ? 'No' : '--'
                                }</div>
                            </Col>
                            {/* <Col md={3} className="mt-3">
                                <label>Total Matches</label>
                                <div className="user-value">{
                                    gameDetail.total_matches ? gameDetail.total_matches : '--'
                                }</div>
                            </Col> */}
                        </Row>
                    </div>
                  
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
                                    _.map(gameDetail.prize_distibution_detail, (item, idx) => {
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
                   
                </div>
            </Fragment>
        )
    }
}