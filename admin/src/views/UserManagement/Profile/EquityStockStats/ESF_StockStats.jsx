import React, { Component, Fragment } from 'react';
import { Row, Col, Table, Button, Input } from 'reactstrap';
import DatePicker from "react-datepicker";
import _ from 'lodash';
import { Modal, ModalBody } from 'reactstrap';
import * as NC from "../../../../helper/NetworkingConstants";
import { notify } from 'react-notify-toast';
import WSManager from "../../../../helper/WSManager";
import ScrollMenu from 'react-horizontal-scrolling-menu';
import Pagination from "react-js-pagination";
import GamestatsGraph from '../GamestatsGraph';
import Moment from 'react-moment';
import moment from 'moment';
import LS from 'local-storage';
import Images from '../../../../components/images';
import HF, { _isEmpty } from '../../../../helper/HelperFunction';
import { MomentDateComponent } from "../../../../components/CustomComponent";
import Loader from '../../../../components/Loader';
import SelectDropdown from "../../../../components/SelectDropdown";
import SelectDate from "../../../../components/SelectDate";
import Select from 'react-select';
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

export default class SF_StockStats extends Component {
    constructor(props) {
        super(props)
        this.state = {
            Total: 0,
            PERPAGE: NC.ITEMS_PERPAGE,
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
            ColorArr: ["#F77084", "#48BF21", "#2B2E47", "#EB5E5E"],
            GameLinupDetails: [],
            list: [],
            indexVal: '',
            CategoryOptions: '',
            SelectedCategory: '',
            groupList: [],
            SelectedGroup: '',
            Keyword: '',
        }
        this.SearchCodeReq = _.debounce(this.SearchCodeReq.bind(this), 500);
    }

    lineupDetailModal(game, index) {

        console.log("game==", game);

        if (game.user_id && game.contest_id)
            this.getGameLinupDetails(game.contest_id, game.user_id)
        this.setState({
            league_id: game.league_id,
            game_contest_name: game.contest_name,
            game_entry_fee: game.total_entry_fee,
            game_prize_pool: game.prize_pool,
            game_fixture_title: game.title,
            game_added_date: game.season_schedule_date,
            indexVal: index,
            game_prize_type: game.currency_type,
            game_status: game.status,
        })
        this.setState(prevState => ({
            isLineupModalOpen: !prevState.isLineupModalOpen
        }));
    }

    onSelect = (key) => {
        this.setState({
            lineup_master_contest_id: key,
        }, () => {
            this.getLinupDetails()
        });
    }

    componentDidMount() {
        if (this.props.userBasic.user_id) {
            this.GetContestFilterData()
            this.getGameHistory()
        }
    }

    GetContestFilterData = () => {
        this.setState({ posting: true })
        let params = {};
        WSManager.Rest(NC.baseURL + NC.SF_GET_CONTEST_FILTER, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                responseJson = responseJson.data;

                let tempGroupList = [];
                if (responseJson.group_list) {
                    responseJson.group_list.map(function (lObj, lKey) {
                        tempGroupList.push({ value: lObj.group_id, label: lObj.group_name });
                    });
                }

                let tempCateList = [];
                if (responseJson.category_list) {
                    responseJson.category_list.map(function (lObj, lKey) {
                        tempCateList.push({ value: lObj.category_id, label: lObj.name });
                    });
                }

                this.setState({
                    groupList: tempGroupList,
                    CategoryOptions: tempCateList
                });
            }
            this.setState({ posting: false })
        })
    }

    getGameLinupDetails = (contest_id, user_id) => {
        let params = {
            game_id: contest_id,
            user_id: user_id
        }
        WSManager.Rest(NC.baseURL + NC.SF_GET_GAME_LINEUP_DETAIL, params).then((ResponseJson) => {
            if (ResponseJson.response_code === NC.successCode) {
                this.setState({
                    ScrollList: ResponseJson.data.result,
                    lineup_master_contest_id: ResponseJson.data.result[0].lineup_master_contest_id,
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
        let { lineup_master_contest_id } = this.state
        let params = {
            lineup_master_contest_id: lineup_master_contest_id,
        }
        WSManager.Rest(NC.baseURL + NC.SF_GET_LINEUP_DETAIL, params).then((ResponseJson) => {
            if (ResponseJson.response_code === NC.successCode) {
                this.setState({
                    LinupUpData: ResponseJson.data.lineup,
                    LinupUpRankWinning: ResponseJson.data,
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        })
    }
    getGameHistory() {
        const { PERPAGE, CURRENT_PAGE, FromDate, ToDate, SelectedGroup, SelectedCategory, Keyword } = this.state
        let params = {
            "keyword": Keyword,
            "category_id": SelectedCategory,
            "group_id": SelectedGroup.value,
            "current_page": CURRENT_PAGE,
            "items_perpage": PERPAGE,
            "sort_order": "DESC",
            "sort_field": "scheduled_date",
            "user_id": this.props.userBasic.user_id,
            'from_date': moment(FromDate).format('YYYY-MM-DD'),
            'to_date': moment(ToDate).format('YYYY-MM-DD'),
        }

        WSManager.Rest(NC.baseURL + NC.SF_USER_GAME_HISTORY, params).then((ResponseJson) => {
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

    handlePageChange(current_page) {
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            this.getGameHistory();
        });
    }
    handleDateFilter = (date, dateType) => {
        this.setState({ [dateType]: date }, () => {
            this.getGameHistory()
        })
    }

    handleCateChange = (value) => {
        if (value) {
            this.setState({
                SelectedCategory: value.value
            }, () => {
                this.getGameHistory()
            })
        }
    }

    handleCollectionChange = (value, name) => {
        if (value != null)
            this.setState({ [name]: value }, this.getGameHistory)
    }

    searchByUser = (e) => {
        this.setState({ Keyword: e.target.value }, this.SearchCodeReq)
    }

    SearchCodeReq() {
        if (this.state.Keyword.length > 2)
            this.getGameHistory()
    }

    clearFilter = () => {
        this.setState({
            FromDate: HF.getFirstDateOfMonth(),
            ToDate: new Date(),
            Keyword: '',
            isDescOrder: true,
            sortField: 'scheduled_date',
            SelectedCategory: '',
            SelectedGroup: '',
        }, () => {
            this.getGameHistory()
        })
    }

    handleDate = (date, dateType) => {
        this.setState({ [dateType]: date }, () => {
            if (this.state.FromDate || this.state.ToDate) {
                this.getGameHistory()
            }
        })
    }

    exportStockStat = () => {
        let { FromDate, ToDate, SelectedGroup, SelectedCategory, Keyword } = this.state
        let group = !_.isUndefined(SelectedGroup.value) ? SelectedGroup.value : ''
        let uid = !_.isUndefined(this.props.userBasic.user_id) ? this.props.userBasic.user_id : ''
        let tempFromDate = moment(FromDate).format("YYYY-MM-DD");
        let tempToDate = moment(ToDate).format("YYYY-MM-DD");

        var query_string = 'keyword=' + Keyword + '&from_date=' + tempFromDate + '&to_date=' + tempToDate + '&sort_order=DESC&sort_field=scheduled_date' + '&group_id=' + group + '&category_id=' + SelectedCategory + '&user_id=' + uid;
        var export_url = 'stock/admin/user/user_game_history_export?';
        HF.exportFunction(query_string, export_url)
    }

    render() {
        const { lineup_master_contest_id, CURRENT_PAGE, PERPAGE, Total, LinupUpData, LinupUpRankWinning, GameHistoryData, FreeGraphOption, TotalContestGraph, SportPreferencesGraph, game_contest_name, game_entry_fee, game_prize_pool, game_fixture_title, game_added_date, indexVal, TotalUser, Keyword, CategoryOptions, SelectedCategory, FromDate, ToDate, groupList, SelectedGroup, game_prize_type, game_status } = this.state;

        // Create menu from items
        const menu = this.menuItems;

        const sameDateProp = {
            disabled_date: false,
            show_time_select: false,
            time_format: false,
            time_intervals: false,
            time_caption: false,
            date_format: 'dd/MM/yyyy',
            handleCallbackFn: this.handleDate,
            class_name: 'form-control mr-3',
            year_dropdown: true,
            month_dropdown: true,
        }
        const FromDateProps = {
            ...sameDateProp,
            min_date: false,
            max_date: new Date(ToDate),
            sel_date: new Date(FromDate),
            date_key: 'FromDate',
            place_holder: 'From Date',
        }
        const ToDateProps = {
            ...sameDateProp,
            min_date: new Date(FromDate),
            max_date: new Date(),
            sel_date: new Date(ToDate),
            date_key: 'ToDate',
            place_holder: 'To Date',
            popup_placement: "bottom-end"
        }

        const Select_Props = {
            is_disabled: false,
            is_searchable: true,
            is_clearable: false,
            menu_is_open: false,
            class_name: "",
            sel_options: CategoryOptions,
            place_holder: "Select",
            selected_value: SelectedCategory,
            modalCallback: this.handleCateChange
        }
        return (
            <Fragment>
                <div className="stockstats">
                    <Row className="mt-4">
                        <Col md={2}>
                            <div className="search-box float-left w-100">
                                <label className="filter-label">Select Type</label>
                                <SelectDropdown SelectProps={Select_Props} />
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
                                    onChange={e => this.handleCollectionChange(e, 'SelectedGroup')}
                                    placeholder="Select"
                                />
                            </div>
                        </Col>
                        <Col md={2}>
                            <label className="filter-label">Select From Date</label>
                            <SelectDate DateProps={FromDateProps} />
                        </Col>
                        <Col md={2}>
                            <label className="filter-label">Select To Date</label>
                            <SelectDate DateProps={ToDateProps} />
                        </Col>
                        <Col md={2}>
                            <div className="search-box float-left w-100">
                                <label className="filter-label">Search Contest</label>
                                <Input
                                    name='code'
                                    value={Keyword}
                                    onChange={this.searchByUser}
                                />
                            </div>
                        </Col>

                        <Col md={2} className="mt-4 p-0">
                            <Button className="btn-secondary" onClick={() => this.clearFilter()}>Clear Filters</Button>
                            <i className="export-list icon-export" onClick={e => this.exportStockStat()}></i>
                        </Col>
                    </Row>
                    <Row className="mt-30">
                        <Col md={12}>
                            <div className="manage-stocks">
                                <h2 className="h2-cls">NSE Stats</h2>
                            </div>
                        </Col>
                    </Row>

                    <Row className="mt-4">
                        <Col md={12} className="table-responsive common-table">
                            <Table>
                                <thead>
                                    <tr>
                                        <th className="left-th pl-4">Date</th>
                                        <th>Contest Type</th>
                                        <th>Contest Category</th>
                                        <th>Contest Name</th>
                                        <th>Entry Fee</th>
                                        <th>Site Rake%</th>
                                        <th>Real Winning</th>
                                        <th>Bonus Winning</th>
                                        <th>Coins Winning</th>
                                        <th>Merchandise Winning</th>
                                        <th>Game</th>
                                        <th className="right-th">Action</th>
                                    </tr>
                                </thead>
                                {
                                    _.map(GameHistoryData, (game, idx) => {
                                        /**Start For stock fantsy */
                                        // if (item.source == '462') {
                                        _.map(game.prize_data, (stkdata, idx) => {
                                            if (stkdata.prize_type == "0") {
                                                game.win_bonus = stkdata.amount
                                            }
                                            if (stkdata.prize_type == "1") {
                                                game.win_real = stkdata.amount
                                            }
                                            if (stkdata.prize_type == "2") {
                                                game.win_coin = stkdata.amount
                                            }
                                            if (stkdata.prize_type == "3") {
                                                game.mer_name = stkdata.name
                                            }
                                        })
                                        // }
                                        /**End For stock fantsy */
                                        return (
                                            <tbody key={idx}>
                                                <tr>
                                                    <td className="pl-4">
                                                        {/* <MomentDateComponent data={{ date: game.scheduled_date, format: "D/MMM/YYYY" }} /> */}
                                                        {HF.getFormatedDateTime(game.scheduled_date, "D/MMM/YYYY")}
                                                    </td>
                                                    <td>{game.category_name}</td>
                                                    <td>{game.group_name}</td>
                                                    <td>{game.contest_name}</td>
                                                    <td>
                                                        {
                                                            game.currency_type == '0' && game.entry_fee > 0 &&
                                                            <span>
                                                                <i className="icon-bonus"></i>
                                                            </span>
                                                        }
                                                        {
                                                            game.currency_type == '1' && game.entry_fee > 0 &&
                                                            <span>
                                                                {HF.getCurrencyCode()}
                                                            </span>
                                                        }
                                                        {
                                                            game.currency_type == '2' && game.entry_fee > 0 &&
                                                            <span>
                                                                <img src={Images.COINIMG} alt="coin-img" />
                                                            </span>
                                                        }
                                                        {game.entry_fee == 0 ?
                                                            <span>Free</span>
                                                            :
                                                            HF.getNumberWithCommas(HF.convertTodecimal(game.entry_fee, 2))
                                                        }
                                                    </td>
                                                    <td>{game.site_rake}</td>
                                                    <td>
                                                        {HF.getCurrencyCode()}{HF.convertTodecimal(game.winning_amount, 2)}
                                                        {/* {(!_.isNull(game.prize_data) && game.prize_data.length > 0) &&
                                                            <div className="merchandise-label">
                                                                {game.prize_data[0].prize_type && game.prize_data[0].prize_type == 1 &&
                                                                    HF.getCurrencyCode()
                                                                }
                                                                {(game.prize_data[0].prize_type && game.prize_data[0].prize_type == 1) ? HF.convertTodecimal(game.prize_data[0].amount, 2) : ''}
                                                            </div>
                                                        } */}
                                                        {/* {
                                                            game.win_real ?
                                                                <span>
                                                                    {HF.getCurrencyCode()}{game.win_real}
                                                                </span>
                                                                : '--'
                                                        } */}
                                                    </td>
                                                    <td>
                                                        <i className="icon-bonus"></i>{HF.convertTodecimal(game.winning_bonus, 2)}
                                                        {/* {(!_.isNull(game.prize_data) && game.prize_data.length > 0) &&
                                                            <div className="merchandise-label">
                                                                {game.prize_data[0].prize_type && game.prize_data[0].prize_type == 0 &&
                                                                    <i className="icon-bonus"></i>
                                                                }
                                                                {(game.prize_data[0].prize_type && game.prize_data[0].prize_type == 0) ? game.prize_data[0].amount : ''}
                                                            </div>
                                                        } */}
                                                        {/* {
                                                            game.win_bonus ?
                                                                <span>
                                                                    <i className="icon-bonus"></i>{game.win_bonus}
                                                                </span>
                                                                : '--'
                                                        } */}
                                                    </td>
                                                    <td>
                                                        <img src={Images.REWARD_ICON} />{game.winning_coin}
                                                        {/* {(!_.isNull(game.prize_data) && game.prize_data.length > 0) &&
                                                            <div className="merchandise-label">
                                                                {game.prize_data[0].prize_type && game.prize_data[0].prize_type == 2 &&
                                                                    <img src={Images.REWARD_ICON} />
                                                                }
                                                                {(game.prize_data[0].prize_type && game.prize_data[0].prize_type == 2) ? game.prize_data[0].amount : ''}
                                                            </div>
                                                        } */}
                                                        {/* {
                                                            game.win_coin ?
                                                                <span>
                                                                    <img src={Images.REWARD_ICON} />{game.win_coin}
                                                                </span>
                                                                : '--'
                                                        } */}
                                                    </td>
                                                    <td>
                                                        {(!_.isNull(game.prize_data) && game.prize_data.length > 0) &&
                                                            <div className="merchandise-label">
                                                                {(game.prize_data[0].prize_type && game.prize_data[0].prize_type == 3) ? game.prize_data[0].name : ''}
                                                            </div>
                                                        }
                                                        {/* {
                                                            game.mer_name ?
                                                                <span>{game.mer_name}</span>
                                                                : '--'
                                                        } */}
                                                    </td>
                                                    <td>Stock</td>
                                                    {
                                                        <td className="btn-linup" onClick={() => this.lineupDetailModal(game, idx)}>
                                                            <span className={`linup-details ${idx == indexVal ? 'active' : ''}`}>
                                                                Lineup Details</span>
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
                    {
                        (Total > PERPAGE) && (
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
                                            <div className="font-xs">{game_added_date}</div>
                                        </Col>
                                        <Col xs={8}>
                                            <ul className="lineup-feelist">
                                                <li className="lineup-feeitem">
                                                    <label>Total Entry Fee</label>
                                                    <div className="font-weight-bold">
                                                        <Fragment>
                                                            {
                                                                (game_prize_type == "0" && game_entry_fee != 0) &&
                                                                <i className="icon-bonus1 mr-1" />
                                                            }
                                                            {
                                                                (game_prize_type == "1" && game_entry_fee != 0) &&
                                                                HF.getCurrencyCode()
                                                            }
                                                            {
                                                                (game_prize_type == "2" && game_entry_fee != 0) &&
                                                                <img className="mr-1" src={Images.REWARD_ICON} alt="" />
                                                            }
                                                        </Fragment>
                                                        {(game_entry_fee == 0) ? 'Free' : game_entry_fee}
                                                    </div>
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
                                                        LinupUpRankWinning.prize_data != null ?
                                                            _.map(LinupUpRankWinning.prize_data, (item, idx) => {
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
                                                            LinupUpRankWinning.won_amount > "0" &&
                                                            <span className="mr-1">
                                                                {HF.getCurrencyCode()}{LinupUpRankWinning.won_amount}
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
                                                        <th className="pl-4">Stock Name</th>
                                                        <th>Stock Display Name</th>
                                                        <th>No. of Shares</th>
                                                        <th>Buy/Sell</th>
                                                        <th>Opning Price</th>
                                                        <th>
                                                            {game_status == '0' && 'Result Price'}
                                                            {(game_status == '2' || game_status == '3') && 'Closing Price'}
                                                        </th>
                                                        <th>Holding</th>
                                                        <th>Gain/Loss</th>
                                                    </tr>
                                                </thead>
                                                {
                                                    _.map(LinupUpData, (lineup, idx) => {
                                                        return (
                                                            <tbody key={idx}>
                                                                <tr>
                                                                    <td className="pl-4">
                                                                        {lineup.stock_name}
                                                                        {lineup.captain == 1 ?
                                                                            <span>(C)</span>
                                                                            :
                                                                            lineup.captain == 2
                                                                                ?
                                                                                <span>(VC)</span>
                                                                                :
                                                                                ''
                                                                        }
                                                                        <span className={`player-sty ${(LinupUpData.playing_announce == 1 && lineup.is_playing == 1) ? 'playing' : (LinupUpData.playing_announce == 1 && lineup.is_playing == 0) ? 'not-playing' : ''}`}></span>
                                                                    </td>
                                                                    <td>{lineup.display_name ? lineup.display_name : '--'}</td>
                                                                    <td className="pl-4">{lineup.lot_size}</td>
                                                                    <td>
                                                                        {lineup.type == '1' ? 'Buy' : lineup.type == '2' ? 'Sell' : '--'}
                                                                    </td>
                                                                    <td>{lineup.joining_rate}</td>
                                                                    <td>
                                                                        {game_status != '1' && HF.convertTodecimal(lineup.result_rate, 2)}
                                                                    </td>
                                                                    <td>{lineup.score}</td>
                                                                    <td>Gain/Loss</td>
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
                </div>
            </Fragment>
        )
    }
}