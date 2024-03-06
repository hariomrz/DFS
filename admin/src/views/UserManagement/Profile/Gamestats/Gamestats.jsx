import React, { Component, Fragment } from 'react';
import { Row, Col, Table } from 'reactstrap';
import DatePicker from "react-datepicker";
import _ from 'lodash';
import { Modal, ModalBody } from 'reactstrap';
import * as NC from "../../../../helper/NetworkingConstants";
import { notify } from 'react-notify-toast';
import WSManager from "../../../../helper/WSManager";
import ScrollMenu from 'react-horizontal-scrolling-menu';
import Pagination from "react-js-pagination";
import GamestatsGraph from '../GamestatsGraph';
import moment from 'moment';
import LS from 'local-storage';
import Images from '../../../../components/images';
import HF, { _isEmpty } from '../../../../helper/HelperFunction';
import { MomentDateComponent } from "../../../../components/CustomComponent";
import BoosterShow from "../../../Booster/BoosterShow";
import Loader from '../../../../components/Loader';
import BenchPlayer from "../../../BenchPlayer/BenchPlayer";
// selected prop will be passed
const MenuItem = ({ text, SelectedOpt }) => {
    return <div
        className={`menu-item ${SelectedOpt ? 'active' : ''}`}
    >{text}</div>;
};

// All items component
// Important! add unique key
export const Menu = (list, selected) =>
    list.map(el => {
        const { team_name, lineup_master_contest_id } = el;
        return <MenuItem text={team_name} key={lineup_master_contest_id} SelectedOpt={selected} />;
    });

export default class Gamestats extends Component {
    constructor(props) {
        super(props)
        this.state = {
            Total: 0,
            PERPAGE: !this.props.DashboardProps ? 5 : 50,
            CURRENT_PAGE: 1,
            FromDate: HF.getFirstDateOfMonth(),
            ToDate: new Date(),
            isLineupModalOpen: false,
            selected: '',
            totalGames: 0,
            GameHistoryData: [],
            LinupUpData: [],
            LinupUpRankWinning: [],
            sportoption: [],
            ContestWon: 0,
            TotalContestGraph: {
                title: {
                    text: ''
                },
                credits: {
                    enabled: false,
                }
            },
            FreeGraphOption: {
                title: {
                    text: ''
                },
                credits: {
                    enabled: false,
                }
            },
            SportPreferencesGraph: {
                title: {
                    text: ''
                },
                credits: {
                    enabled: false,
                }
            },
            ColorArr: ["#F77084", "#48BF21", "#2B2E47", "#EB5E5E"],
            GameLinupDetails: [],
            list: [],
            indexVal: '',
            Booster: [],
            BoosterLoad: true,
            BenchPly: [],
            BenchLoad: true,
            sports_id_state: []
        }
    }

    lineupDetailModal(game, index) {
        console.log("game", game)
        if (game.league_id && game.contest_id)
            this.getGameLinupDetails(game.league_id, game.contest_id, game.user_id)

        if (!this.state.isLineupModalOpen) {
            this.setState({
                Booster: [],
                BenchPly: [],
            })
        }
        this.setState({
            league_id: game.league_id,
            game_contest_name: game.contest_name,
            game_entry_fee: game.total_entry_fee,
            game_prize_pool: game.prize_pool,
            game_fixture_title: game.title,
            game_added_date: game.season_schedule_date,
            indexVal: index,
            sports_id_state: game.sports_id
        })
        this.setState(prevState => ({
            isLineupModalOpen: !prevState.isLineupModalOpen
        }));
    }

    onSelect = (key) => {
        this.setState({
            lineup_master_contest_id: key,
            Booster: [],
            BoosterLoad: true,
            BenchPly: [],
            BenchLoad: true,
        }, () => {
            this.getLinupDetails()
        });
    }
    componentDidMount() {
        if (this.props.userBasic.user_id) {
            this.getGameHistory()
            if (this.props.DashboardProps)
                this.getGameState()
        }
    }
    getGameLinupDetails = (league_id, contest_id, user_id) => {
        let params = {
            contest_id: contest_id, user_id: user_id
        }
        WSManager.Rest(NC.baseURL + NC.GET_DFS_GAME_LINEUP_DETAIL, params).then((ResponseJson) => {
            if (ResponseJson.response_code === NC.successCode) {
                this.setState({
                    ScrollList: ResponseJson.data.result,

                    lineup_master_contest_id: ResponseJson.data.result[0].lineup_master_contest_id,
                    league_id: league_id
                }, () => {
                    this.menuItems = Menu(this.state.ScrollList, this.state.lineup_master_contest_id);
                    this.getLinupDetails()
                })

            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        })
    }
    getLinupDetails = () => {
        let { lineup_master_contest_id, league_id } = this.state
        let params = {
            lineup_master_contest_id: lineup_master_contest_id,
            // league_id: league_id
        }
        WSManager.Rest(NC.baseURL + NC.DFS_GET_USER_CONTEST_TEAM, params).then((ResponseJson) => {
            if (ResponseJson.response_code === NC.successCode) {
                this.setState({
                    LinupUpData: ResponseJson.data.lineup,
                    LinupUpRankWinning: ResponseJson.data,
                    Booster: ResponseJson.data ? ResponseJson.data.booster : [],
                    BoosterLoad: false,
                    BenchPly: ResponseJson.data ? ResponseJson.data.bench : [],
                    BenchLoad: false,
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        })
    }
    getGameHistory() {
        const { PERPAGE, CURRENT_PAGE, FromDate, ToDate } = this.state
        let params = {
            items_perpage: PERPAGE,
            total_items: 0,
            current_page: CURRENT_PAGE,
            sort_order: "DESC",
            sort_field: "season_scheduled_date",
            user_id: this.props.userBasic.user_id,
            from_date: moment(FromDate).format('YYYY-MM-DD'),
            to_date: moment(ToDate).format('YYYY-MM-DD'),
        }

        WSManager.Rest(NC.baseURL + NC.GET_USER_GAME_HISTORY, params).then((ResponseJson) => {
            if (ResponseJson.response_code === NC.successCode) {
                this.setState({
                    GameHistoryData: ResponseJson.data.result,
                    Total: ResponseJson.data.total
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        })
    }
    getGameState = () => {
        const { FromDate, ToDate } = this.state
        let param = {
            user_id: this.props.userBasic.user_id,
            from_date: moment(FromDate).format("YYYY-MM-DD"),
            to_date: moment(ToDate).format("YYYY-MM-DD"),
        }
        WSManager.Rest(NC.baseURL + NC.GET_GAME_STATS, param).then((ResponseJson) => {
            if (ResponseJson.response_code === NC.successCode) {
                let FreeGraphData = []
                let PaidGraphData = []
                let DateAddedData = []
                let tempDate = new Date()
                let today = moment(tempDate).format("DD<br/>MMM");
                this.setState({
                    SportsGraph: ResponseJson.data.sport_pref,
                    FreeGraph: ResponseJson.data.freee_paid
                })

                if (!_.isEmpty(this.state.SportsGraph)) {
                    _.map(this.state.SportsGraph, (sports, idxSp) => {
                        sports.y = parseInt(sports.sport_count)
                        sports.color = this.state.ColorArr[idxSp]
                        this.state.SportsGraph[idxSp] = sports
                    })
                    this.setState({
                        sportoption: this.state.SportsGraph
                    })
                }

                if (!_.isEmpty(this.state.FreeGraph)) {
                    _.map(this.state.FreeGraph, (free, idx) => {
                        FreeGraphData.push(parseInt(free.free))
                        PaidGraphData.push(parseInt(free.paid))

                        let formatedDate = moment(free.date_added).format("MMM DD");
                        DateAddedData.push(formatedDate)
                    })
                }
                //Start Free Graph

                this.setState({
                    FreeGraphOption: {
                        title: {
                            text: ''
                        },
                        chart: {

                            height: !_.isEmpty(this.state.FreeGraph) ? '265px' : '280px',
                        },
                        plotOptions: {
                            series: {
                                marker: {
                                    enabled: false
                                }
                            }
                        },
                        xAxis: {

                            categories: !_.isEmpty(this.state.FreeGraph) ? DateAddedData : [today],
                            min: 0,
                            tickWidth: 0,
                            crosshair: false,
                            lineWidth: 3,
                            gridLineWidth: 0,
                            title: '',
                            lineColor: '#D8D8D8',
                            title: {
                                text: ''
                            }
                        },
                        yAxis: {
                            title: {
                                text: ''
                            },
                            min: 0,
                            tickWidth: 0,
                            crosshair: false,
                            lineWidth: 3,
                            gridLineWidth: 3,
                            title: '',
                            lineColor: '#D8D8D8'
                        },
                        series: [
                            {
                                type: 'line',
                                name: 'Free',

                                data: !_.isEmpty(this.state.FreeGraph) ? FreeGraphData : [0],
                                color: '#F77084'
                            },
                            {
                                type: 'line',
                                name: 'Paid',

                                data: !_.isEmpty(this.state.FreeGraph) ? PaidGraphData : [0],
                                color: '#2A2E49'
                            },
                        ],
                        credits: {
                            enabled: false,
                        },
                        legend: {
                            enabled: true
                        },
                    }
                })
                //}
                //End Free Graph
                //Start Total contest Graph
                this.setState({
                    TotalContestGraph: {
                        chart: {
                            type: 'bar', height: '220px'
                        },
                        tooltip: false,
                        plotOptions: {
                            bar: {
                                dataLabels: {
                                    enabled: true,

                                },
                                borderRadius: 10,
                                minPointLength: 10,
                                pointHeight: 12,
                                pointWidth: 16,
                            }
                        },
                        legend: {
                            enabled: false
                        },
                        credits: {
                            enabled: false,
                        },
                        xAxis: {
                            categories: ['Contest Played', 'Contest Won'],
                            min: 0,
                            tickWidth: 0,
                            crosshair: false,
                            lineWidth: 3,
                            gridLineWidth: 0,
                            title: '',
                            lineColor: '#D8D8D8'
                        },
                        yAxis: {
                            min: 0,
                            tickWidth: 0,
                            crosshair: false,
                            lineWidth: 3,
                            gridLineWidth: 0,
                            title: '',
                            lineColor: '#D8D8D8'
                        },

                        series: [{
                            name: '',

                            data: [{ y: parseInt(ResponseJson.data.contest_joined), color: '#F77084' }, { y: parseInt(ResponseJson.data.contest_won), color: '#2A2E49' }],
                        }
                        ],
                    }
                })
                //End Total contest Graph

                //Start Sports Preference Graph

                this.setState({
                    SportPreferencesGraph: {
                        title: {

                            text: ''
                        },
                        chart: {
                            type: 'pie',
                            height: '220px',
                        },
                        plotOptions: {
                            pie: {
                                dataLabels: false,
                                innerSize: '80%',
                                allowPointSelect: true,
                                cursor: 'pointer',
                                dataLabels: {
                                    enabled: true,
                                    format: '<b>{point.sports_name}</b>: {point.percentage:.1f} %',

                                }
                            }
                        },
                        series: [{

                            data: this.state.sportoption

                        }],
                        LineData: [],
                        GraphHeaderTitle: [],
                        credits: {
                            enabled: false,
                        }
                    }
                })

                //End Sports Preference Graph
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        })
    }
    handlePageChange(current_page) {
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            this.getGameHistory();
        });
    }
    handleDateFilter = (date, dateType) => {
        this.setState({ [dateType]: date }, () => {
            // if (this.state.FromDate && this.state.ToDate) {
            this.getGameHistory()
            this.getGameState()
            // }
        })
    }
    exportGameStat = () => {
        let { PERPAGE, CURRENT_PAGE, FromDate, ToDate } = this.state
        let uid = !_.isUndefined(this.props.userBasic.user_id) ? this.props.userBasic.user_id : ''
        let tempFromDate = moment(FromDate).format("YYYY-MM-DD");
        let tempToDate = moment(ToDate).format("YYYY-MM-DD");

        var query_string = 'user_id=' + uid + '&items_perpage=' + PERPAGE + '&total_items=0&current_page=' + CURRENT_PAGE + '&sort_order=DESC&sort_field=added_date&country=&state=&keyword=&from_date=' + tempFromDate + '&to_date=' + tempToDate + '&role=2';
        let sessionKey = WSManager.getToken();
        query_string += "&Sessionkey" + "=" + sessionKey;

        window.open(NC.baseURL + 'adminapi/user/user_game_history_export?' + query_string, '_blank');
    }

    getSelectedSport = (SPORT_ID) => {
        let spNm = HF.getSportsData() ? HF.getSportsData() : []
        if (!_.isEmpty(spNm)) {
            var sportName = spNm.filter(function (item) {
                return item.value === SPORT_ID ? true : false;
            });
            if (!_.isEmpty(sportName))
                return sportName[0].label
            else
                return '--'
        } else {
            return '--'
        }
    }

    render() {
        const { lineup_master_contest_id, CURRENT_PAGE, PERPAGE, Total, LinupUpData, LinupUpRankWinning, GameHistoryData, FreeGraphOption, TotalContestGraph, SportPreferencesGraph, game_contest_name, game_entry_fee, game_prize_pool, game_fixture_title, game_added_date, indexVal, Booster, BoosterLoad, BenchPly, BenchLoad, sports_id_state } = this.state;
        const { DashboardProps } = this.props;
        // Create menu from items
        const menu = this.menuItems;
        return (
            <Fragment>
                <div className="gamestats">
                    {
                        DashboardProps && (
                            <Row>
                                <Col md={8}>
                                    <div className="float-left mr-2">
                                        <label className="filter-label">From Date</label>
                                        <DatePicker
                                            maxDate={new Date(this.state.ToDate)}
                                            className="filter-date mr-1"
                                            showYearDropdown='true'
                                            selected={this.state.FromDate}
                                            onChange={e => this.handleDateFilter(e, "FromDate")}
                                            placeholderText="From"
                                            dateFormat='dd/MM/yyyy'
                                        />
                                    </div>
                                    <div className="float-left">
                                        <label className="filter-label">To Date</label>
                                        <DatePicker
                                            minDate={new Date(this.state.FromDate)}
                                            maxDate={new Date()}
                                            className="filter-date"
                                            showYearDropdown='true'
                                            selected={this.state.ToDate}
                                            onChange={e => this.handleDateFilter(e, "ToDate")}
                                            placeholderText="To"
                                            dateFormat='dd/MM/yyyy'
                                        />
                                    </div>
                                </Col>
                                <Col md={4}>
                                    <div className="filter-right-box clearfix">
                                        <div className="filter-export">
                                            <i className="icon-export" onClick={e => this.exportGameStat()}></i>
                                        </div>
                                    </div>
                                </Col>
                            </Row>
                        )
                    }

                    <div className="game-status-component">
                        {
                            DashboardProps && TotalContestGraph && FreeGraphOption && SportPreferencesGraph && (
                                <GamestatsGraph
                                    TotalContestGraph={TotalContestGraph}
                                    FreeGraphOption={FreeGraphOption}
                                    SportPreferencesGraph={SportPreferencesGraph}
                                />
                            )
                        }
                    </div>


                    <Row>
                        <Col md={12} className="table-responsive common-table">
                            <Table>
                                <thead>
                                    <tr>
                                        <th className="left-th pl-4">Date</th>
                                        <th>Sports</th>
                                        {
                                            HF.allowSecondInni() == "1" &&
                                            <th>Feature Type</th>
                                        }
                                        <th>Contest</th>
                                        <th>Fixture</th>
                                        <th>Entry Fee</th>
                                        <th>Winning</th>
                                        <th>Bonus Winning</th>
                                        <th>Coins Winning</th>
                                        <th>Merchandise Winning</th>
                                        <th>Game</th>
                                        <th className={!DashboardProps ? "right-th" : ""}>Contest Type</th>
                                        {
                                            DashboardProps && (
                                                <th className="right-th">Action</th>
                                            )
                                        }
                                    </tr>
                                </thead>
                                {
                                    _.map(GameHistoryData, (game, idx) => {
                                        return (
                                            <tbody key={idx}>
                                                <tr>
                                                    <td className="pl-4">
                                                        {/* <MomentDateComponent data={{ date: game.season_schedule_date, format: "D MMMM YY" }} /> */}
                                                        {HF.getFormatedDateTime(game.season_schedule_date, "D MMMM YY")}
                                                    </td>
                                                    <td>
                                                        {
                                                            this.getSelectedSport(game.sports_id)
                                                        }
                                                    </td>
                                                    {
                                                        HF.allowSecondInni() == "1" &&
                                                        <td>{game.feature_type}</td>
                                                    }
                                                    <td className="text-ellipsis">{game.contest_name}</td>
                                                    <td>{game.title} {(game.season_game_count > 1) ? " + " + (game.season_game_count - 1) : ''}</td>
                                                    <td>


                                                        {
                                                            game.currency_type == '0' && game.entry_fee > 0 &&
                                                            <span>
                                                                <i className="icon-bonus"></i>
                                                                {HF.getNumberWithCommas(game.entry_fee)}
                                                                {/* {game.entry_fee} */}
                                                            </span>
                                                        }
                                                        {
                                                            game.currency_type == '1' && game.entry_fee > 0 &&
                                                            <span>
                                                                {HF.getCurrencyCode()}
                                                                {HF.getNumberWithCommas(game.entry_fee)}
                                                                {/* {game.entry_fee} */}
                                                            </span>
                                                        }
                                                        {
                                                            game.currency_type == '2' && game.entry_fee > 0 &&
                                                            <span>
                                                                <img src={Images.COINIMG} alt="coin-img" />
                                                                {HF.getNumberWithCommas(game.entry_fee)}
                                                                {/* {game.entry_fee} */}
                                                            </span>
                                                        }
                                                        {game.entry_fee == 0 &&

                                                            <span>Free</span>

                                                        }

                                                    </td>
                                                    <td>
                                                        {
                                                            game.winning_amount > 0 ?
                                                                <span>
                                                                    {HF.getCurrencyCode()}&nbsp;
                                                                    {HF.getNumberWithCommas(Number(game.winning_amount))}
                                                                </span>
                                                                :
                                                                '--'
                                                        }
                                                    </td>
                                                    <td>

                                                        {
                                                            game.winning_bonus && (Number(game.winning_bonus) > 0) ?
                                                                <span>
                                                                    <i className="icon-bonus"></i>&nbsp;
                                                                    {HF.getNumberWithCommas(Number(game.winning_bonus))}
                                                                </span>
                                                                : '--'
                                                        }
                                                        {/* {(!_.isNull(game.prize_data) && game.prize_data.length > 0) &&
                                                            <div className="merchandise-label">


                                                                Already commnetd : Start >> {game.prize_data[0].prize_type && game.prize_data[0].prize_type == 0 &&
                                                                    <i className="icon-bonus"></i>
                                                                } <<< Already commnetd : End

                                                                {game.prize_data[0].prize_type && game.prize_data[0].prize_type == 0 &&
                                                                    <i className="icon-bonus"></i>
                                                                }


                                                                {(game.prize_data[0].prize_type && game.prize_data[0].prize_type == 0) ? game.prize_data[0].amount : ''}

                                                            </div>
                                                        } */}
                                                    </td>
                                                    <td>
                                                        {/* {(!_.isNull(game.prize_data) && game.prize_data.length > 0) &&
                                                            <div className="merchandise-label">



                                                                {game.prize_data[0].prize_type && game.prize_data[0].prize_type == 2 &&
                                                                    <img src={Images.REWARD_ICON} />
                                                                }

                                                                {(game.prize_data[0].prize_type && game.prize_data[0].prize_type == 2) ? game.prize_data[0].amount : ''}

                                                            </div>
                                                        } */}
                                                        {game.winning_coins}
                                                    </td>
                                                    <td>
                                                        {(!_.isNull(game.prize_data) && game.prize_data.length > 0) &&
                                                            <div className="merchandise-label">
                                                                {game.prize_data.map((item, idx) => {
                                                                    return (<>
                                                                        {item.prize_type && item.prize_type == 3 ? item.prize_type.length === idx + 1 ? item.name : item.name + "/" : ""}
                                                                    </>)
                                                                })}
                                                                {/* {(game.prize_data[0].prize_type && game.prize_data[0].prize_type == 3) ? game.prize_data[0].name : ''} */}

                                                            </div>
                                                        }
                                                        {/* {(!_.isNull(game.prize_data) && game.prize_data.length > 0) &&
                                                            <div className="merchandise-label">



                                                                {(game.prize_data[0].prize_type && game.prize_data[0].prize_type == 3) ? game.prize_data[0].name : ''}

                                                            </div>
                                                        } */}
                                                    </td>
                                                    <td>Fantasy</td>
                                                    <td>{game.group_name}</td>
                                                    {
                                                        DashboardProps && (
                                                            <td className="btn-linup" onClick={() => this.lineupDetailModal(game, idx)}>
                                                                <span className={`linup-details ${idx == indexVal ? 'active' : ''}`}>
                                                                    Lineup Details</span>
                                                            </td>

                                                        )
                                                    }
                                                </tr>
                                            </tbody>
                                        )
                                    })
                                }
                            </Table>
                        </Col>
                    </Row>
                    {
                        (DashboardProps && Total > PERPAGE) && (
                            <div className="custom-pagination userlistpage-paging float-right mb-5">
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
                    <div>
                        <Modal isOpen={this.state.isLineupModalOpen} toggle={() => this.lineupDetailModal('', indexVal)} className="lineup-details modal-md">
                            <ModalBody className="p-0">
                                <div className="lineup-teams theme-color">
                                    <Row>
                                        <Col xs={4}>

                                            <h2 className="h2-cls mb-0 text-ellipsis">{game_contest_name}</h2>
                                            <div className="team-vs">{game_fixture_title}</div>
                                            <div className="font-xs">
                                                { }
                                                {HF.getFormatedDateTime(game_added_date, "D-MMM-YYYY hh:mm A ")}
                                            </div>
                                        </Col>
                                        <Col xs={8}>
                                            <ul className="lineup-feelist">
                                                <li className="lineup-feeitem">
                                                    <label>Total Entry Fee</label>
                                                    <div className="font-weight-bold">{HF.getCurrencyCode()}{game_entry_fee}</div>
                                                </li>
                                                <li className="lineup-feeitem">
                                                    <label> Price Pool </label>
                                                    <div className="font-weight-bold">{HF.getCurrencyCode()}{game_prize_pool}</div>
                                                </li>
                                            </ul>
                                        </Col>
                                    </Row>
                                </div>
                                <Row>
                                    <Col xs={12}>
                                        <div>
                                            <ScrollMenu
                                                data={menu}

                                                selected={lineup_master_contest_id}
                                                onSelect={(e) => this.onSelect(e)}
                                                alignCenter={0}
                                            />
                                        </div>
                                    </Col>
                                </Row>
                                <Col xs={12}>
                                    <Row className="rank-box">
                                        <Col xs={3}>
                                            <h3 className="h3-cls">Rank {' '} {(LinupUpRankWinning.game_rank) ? LinupUpRankWinning.game_rank : '0'}</h3>
                                        </Col>
                                        <Col xs={9}>
                                            <h3 className="h3-cls">Winnings {' '}
                                                {
                                                    LinupUpRankWinning.is_winner == "1" ? 
                                                    <>
                                                    
                                                        {
                                                        parseFloat(LinupUpRankWinning.amount) > 0 && 
                                                            <span className="mr-1">{HF.getCurrencyCode() + ' ' + LinupUpRankWinning.amount}</span> 
                                                        }
                                                        {parseFloat(LinupUpRankWinning.coin) > 0 && 
                                                            <>{parseFloat(LinupUpRankWinning.amount) > 0 && '/'}<span><img className="mr-1" src={Images.REWARD_ICON} alt="" />{LinupUpRankWinning.coin}</span></> 
                                                        }
                                                        {parseFloat(LinupUpRankWinning.bonus) > 0 &&
                                                            <>{(parseFloat(LinupUpRankWinning.coin) > 0 || parseFloat(LinupUpRankWinning.amount) > 0) && '/'}<span className="mr-1"><i className="icon-bonus1 mr-1"></i>{LinupUpRankWinning.bonus}</span></> 
                                                        }
                                                        {
                                                            LinupUpRankWinning.merchandise != '' && 
                                                            <>{(parseFloat(LinupUpRankWinning.coin) > 0 || parseFloat(LinupUpRankWinning.amount) > 0 &&parseFloat(LinupUpRankWinning.bonus) > 0) && '/'}{LinupUpRankWinning.merchandise}</> 
                                                        }
                                                    </>
                                                    :
                                                    ''
                                                }
                                                {/* {
                                                    !_isEmpty(LinupUpRankWinning.prize_data) && LinupUpRankWinning.prize_data.length > 0 &&
                                                    <>
                                                        {LinupUpRankWinning.prize_data[0].prize_type == 1 &&
                                                            HF.getCurrencyCode()
                                                        }
                                                        {LinupUpRankWinning.prize_data[0].prize_type == 0 &&
                                                            <i className="icon-bonus"></i>
                                                        }
                                                        {LinupUpRankWinning.prize_data[0].prize_type == 2 &&
                                                            <img src={Images.REWARD_ICON} />
                                                        }

                                                        {(LinupUpRankWinning.prize_data[0].prize_type == 3) ? ' ' + LinupUpRankWinning.prize_data[0].name : LinupUpRankWinning.prize_data[0].amount}

                                                    </>
                                                } */}

                                                {/* {HF.getCurrencyCode()}{(LinupUpRankWinning.won_amount) ? LinupUpRankWinning.won_amount : '0'} */}
                                            </h3>
                                        </Col>
                                    </Row>
                                </Col>
                                <Row className="mb-5">
                                    <Col md={12}>
                                        <div className="table-responsive common-table">
                                            {
                                                sports_id_state && sports_id_state == 15 ?
                                                    <Table>
                                                        <thead>
                                                            <tr>
                                                                <th className="pl-4">Driver/Constructor</th>
                                                                <th>Display Name</th>
                                                                <th>Role</th>
                                                                <th>Car</th>
                                                                <th>Score</th>
                                                            </tr>
                                                        </thead>
                                                        {
                                                            _.map(LinupUpData, (lineup, idx) => {
                                                                return (
                                                                    <tbody key={idx}>
                                                                        <tr>

                                                                            <td>
                                                                                {lineup.full_name}
                                                                                {lineup.captain == 1 ?
                                                                                    <span>(T)</span>
                                                                                    :
                                                                                    lineup.captain == 2
                                                                                        ?
                                                                                        <span>(VC)</span>
                                                                                        :
                                                                                        ''
                                                                                }
                                                                                {(HF.allowBenchPlyer() == '1' && lineup.sub_in == 1) && <span className="bench-in">Sub In</span>}
                                                                            </td>
                                                                            <td> {lineup.full_name}</td>
                                                                            <td >{lineup.position}</td>
                                                                            <td>{lineup.team_abbr}</td>
                                                                            <td>{lineup.score}</td>
                                                                        </tr>
                                                                    </tbody>
                                                                )
                                                            })
                                                        }
                                                    </Table>
                                                    :
                                                    <Table>
                                                        <thead>
                                                            <tr>
                                                                <th className="pl-4">Position</th>
                                                                <th>Player Name</th>
                                                                <th>Team Name</th>
                                                                <th>Score</th>
                                                            </tr>
                                                        </thead>
                                                        {
                                                            _.map(LinupUpData, (lineup, idx) => {
                                                                return (
                                                                    <tbody key={idx}>
                                                                        <tr>
                                                                            <td className="pl-4">{lineup.position}</td>
                                                                            <td>
                                                                                {lineup.full_name}
                                                                                {lineup.captain == 1 ?
                                                                                    <span>(C)</span>
                                                                                    :
                                                                                    lineup.captain == 2
                                                                                        ?
                                                                                        <span>(VC)</span>
                                                                                        :
                                                                                        ''
                                                                                }
                                                                                {(HF.allowBenchPlyer() == '1' && lineup.sub_in == 1) && <span className="bench-in">Sub In</span>}
                                                                            </td>
                                                                            <td>{lineup.team_abbr}</td>
                                                                            <td>{lineup.score}</td>
                                                                        </tr>
                                                                    </tbody>
                                                                )
                                                            })
                                                        }
                                                    </Table>
                                            }

                                        </div>
                                        {(HF.allowBenchPlyer() == '1') &&
                                            <div className="bench">
                                                {
                                                    (BenchLoad) ?
                                                        <Loader hide />
                                                        :
                                                        <BenchPlayer data={BenchPly ? BenchPly : []} />
                                                }
                                            </div>}

                                        {(HF.allowBooster() == '1') && <div className="bstr">
                                            {
                                                (BoosterLoad) ?
                                                    <Loader hide />
                                                    :
                                                    <BoosterShow data={Booster ? Booster : []} />
                                            }
                                        </div>}
                                    </Col>
                                </Row>
                            </ModalBody>
                        </Modal>
                    </div>
                </div>
            </Fragment>
        )
    }
}