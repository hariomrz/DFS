import React, { Component,Fragment } from "react";
import { Row, Col, Table } from 'reactstrap';
import _ from 'lodash';
import Pagination from "react-js-pagination";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import * as NC from "../../helper/NetworkingConstants";
import HF, { _isNull, _isEmpty } from '../../helper/HelperFunction';
import {LB_leaderboardByDetails,LB_leaderboardUserList } from "../../helper/WSCalling";

import SelectDropdown from "../../components/SelectDropdown";
import Images from "../../components/images";
// const FilterOption = [
//     { value: 0, label: 'Pending' },
//     { value: 1, label: 'Success' },
//     { value: 2, label: 'Failed' },
// ]
class LeaderboardDetails extends Component {
    constructor(props) {
        super(props)
        this.state = {
            SelectedFilter: '',
            prize_id: (this.props.match.params.prize_id) ? this.props.match.params.prize_id : false,
            FilterOption:[],
            prize_distibution_detail:[],
            prize_distibution_detail_master:[],
            leaderboards:[],
            Total: 0,
            PERPAGE: 10,
            CURRENT_PAGE: 1,
            GameLinupDetail: [],
            LeaderBoardDetails:{},
            category_id:''


        }
    }
    componentDidMount(){
        this.getLeaderboardDetails();

    }

    getLeaderboardDetails = () => {
         const param = { 'prize_id': this.state.prize_id}

        LB_leaderboardByDetails(param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                const Temp = []
                this.setState({category_id:responseJson.data.category_id, LeaderBoardDetails: responseJson.data,prize_distibution_detail_master:responseJson.data.prize_data_master ? responseJson.data.prize_data_master:[] })
                if(responseJson.data.leaderboard && !_isEmpty(responseJson.data.leaderboard) ){
                    _.map(responseJson.data.leaderboard, (item, idx) => {
                        Temp.push({
                            value: item.leaderboard_id, label: item.name,prize_detail:item.prize_detail ? item.prize_detail:[]
                        })
                    })
                    this.setState({
                        FilterOption: Temp,
                        SelectedFilter:Temp[0].value,
                        prize_distibution_detail:Temp[0].prize_detail ? Temp[0].prize_detail:[]
                    },()=>{
                        this.getLeaderboardUserList()
                    })
                }
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }
    getLeaderboardUserList = () => {
        const { PERPAGE, CURRENT_PAGE ,SelectedFilter} = this.state
        let param = {
            "items_perpage": PERPAGE,
            "total_items": 0,
            "current_page": CURRENT_PAGE,
            "leaderboard_id": SelectedFilter,
        }
        LB_leaderboardUserList(param).then((responseJson) => {
           if (responseJson.response_code === NC.successCode) {
            this.setState({
                GameLinupDetail: responseJson.data.result,
                Total: responseJson.data.total
            })           
           }
       }).catch((error) => {
           notify.show(NC.SYSTEM_ERROR, "error", 5000);
       })
   }
    handlePageChange(current_page) {
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            this.getLeaderboardUserList();
        });
    }

    handleFilterChange = (value) => {
        this.setState({ SelectedFilter: value.value, prize_distibution_detail: value.prize_detail }, () => {
            this.getLeaderboardUserList();
        })
    }

    render() {
        let { SelectedFilter ,FilterOption,prize_distibution_detail,CURRENT_PAGE, PERPAGE, Total,GameLinupDetail,LeaderBoardDetails,prize_distibution_detail_master} = this.state
        const Select_Props = {
            is_disabled: false,
            is_searchable: true,
            is_clearable: false,
            menu_is_open: false,
            class_name: "",
            sel_options: FilterOption,
            place_holder: "Select",
            selected_value: SelectedFilter,
            modalCallback: this.handleFilterChange
        }
        let type = LeaderBoardDetails.type == 4 ? "League" : LeaderBoardDetails.type == 3 ? "Monthly" :LeaderBoardDetails.type == 2 ? "Weekly" :LeaderBoardDetails.type == 1 ? "Daily" : '--'
        return (
            <div className="contest-d-main">
                <Row className="mt-3 mb-3">
                    <Col md={12}>
                        <h1 className="h1-cls">Leaderboard Detail</h1>
                    </Col>
                </Row>
                <div className="details-box">
                    <Row className="box-items mt-3">
                        <Col md={3}>
                            <label>Leaderboard Type</label>
                            <div className="user-value">{LeaderBoardDetails.leaderboard_type}</div>
                        </Col>
                        <Col md={3}>
                            <label>Type</label>
                            <div className="user-value">{type}</div>
                        </Col>


                        {/* <Col md={3}>
                            <label>Allow Prize</label>
                            <div className="user-value">{LeaderBoardDetails.allow_prize == "1" ? "Allowed": "Not Allowed"}</div>
                        </Col> */}
                       
                        <Col md={3} >
                            <label>Name</label>
                            <div className="user-value">{LeaderBoardDetails.name}</div>
                        </Col>
                        <Col md={3}>
                            <label>Status</label>
                            <div className="user-value">{LeaderBoardDetails.status == "1" ? "Active": "InActive"}</div>
                        </Col>
                       
                        {/* <Col md={3} className="mt-3">
                            <label>Leaderboard Name</label>
                            <div className="user-value">Referral</div>
                        </Col>
                        <Col md={3} className="mt-3">
                            <label>Leaderboard Name</label>
                            <div className="user-value">Referral</div>
                        </Col> */}
                    </Row>
                </div>
                {
                    prize_distibution_detail_master && !_isEmpty(prize_distibution_detail_master) &&
                    <Row className="mt-3 mb-3">
                    <Col md={4}>
                        <h3 className="h3-cls">Prize Detail</h3>
                    </Col>

                </Row>
                }
                { prize_distibution_detail_master && !_isEmpty(prize_distibution_detail_master) ?
                    <Row>
                        <Col md={12} className="table-responsive common-table">
                            <Table>
                                <thead>
                                    <tr className="text-center">
                                        <th>Min</th>
                                        <th>Max</th>
                                        <th></th>
                                        <th>Amount (Per Person)</th>
                                    </tr>
                                </thead>
                                {  
                                    _.map(prize_distibution_detail_master, (item, idx) => {
                                        return (
                                            <tbody key={idx}>
                                                <tr>
                                                    <td className="text-center">{item.min}</td>
                                                    <td className="text-center">{item.max}</td>
                                                    <td className="text-center">
                                                        {(item.per != 'Infinity' && item.prize_type != '3') && item.per}
                                                        {(item.per != 'Infinity' && item.prize_type == '3') && ''}
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

                                                        {item.amount}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        )
                                    })
                                }
                            </Table>
                        </Col>
                    </Row>
                    :
                    <Row className="mt-3 mb-3">
                        <Col md={12}>
                            <div className="no-records">No Records Found.</div>
                        </Col>

                    </Row>
                }
                    {
                       LeaderBoardDetails.type !=4 && LeaderBoardDetails.leaderboard && LeaderBoardDetails.leaderboard != undefined && LeaderBoardDetails.leaderboard.length > 0 &&
                        <Row className="ld-filters">
                        <Col md={3}>
                            <SelectDropdown SelectProps={Select_Props} />
                        </Col>
                    </Row>
                    }
               

    
                {LeaderBoardDetails.type !=4 && prize_distibution_detail && !_isEmpty(prize_distibution_detail) ?
                    <Row>
                        <Col md={12} className="table-responsive common-table">
                            <Table>
                                <thead>
                                    <tr className="text-center">
                                        <th>Min</th>
                                        <th>Max</th>
                                        <th></th>
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
                                                        {(item.per != 'Infinity' && item.prize_type == '3') && ''}
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
                                                        

                                                        {item.amount}

                                                    </td>
                                                </tr>
                                            </tbody>
                                        )
                                    })
                                }
                            </Table>
                        </Col>
                    </Row>
                    :
                    LeaderBoardDetails.type !=4 && 
                    <Row className="mt-3 mb-3">
                        <Col md={12}>
                            <div className="no-records">No Records Found.</div>
                        </Col>

                    </Row>
                }
                {
                   
                    <Row className="mt-3 mb-3">
                    <Col md={4}>
                        <h3 className="h3-cls">Participants</h3>
                    </Col>

                </Row>
                }
                {
                    GameLinupDetail && !_isEmpty(GameLinupDetail) ?
                    <Row>
                    <Col md={12} className="table-responsive common-table">
                        <Table>
                            <thead>
                                <tr>
                                    <th>User Name</th>
                                    <th>Rank</th>
                                    <th>{this.state.category_id == '1'? 'Referral Count':'Score'}</th>
                                    <th>Winning Amount</th>
                                    {/* <th>Action</th> */}
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
                                                <td>{lineup.rank_value}</td>
                                                <td>{this.state.category_id == '1' ? Math.round(lineup.total_value) :lineup.total_value}</td>
                                                <td>
                                                    {
                                                        lineup.is_winner == "1" ?
                                                        lineup.prize_data!=undefined &&  lineup.prize_data != null ?
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
                                                            '--'
                                                    }
                                                </td>
                                                {/* <td onClick={() => this.lineupDetailModal(true, lineup.lineup_master_contest_id, LeagueDetail.league_id)}>
                                                    <span className="linup-details">Lineup Details</span></td> */}
                                            </tr>
                                        </tbody>
                                    )
                                })
                            }
                        </Table>
                    </Col>
                </Row>
                        :

                        <Row className="mt-3 mb-3">
                            <Col md={12}>
                                <div className="no-records">No Records Found.</div>
                            </Col>

                        </Row>
                }
                {  GameLinupDetail && !_isEmpty(GameLinupDetail) &&
                    <div className="custom-pagination userlistpage-paging float-right mb-5">
                        <Pagination
                            activePage={CURRENT_PAGE}
                            itemsCountPerPage={PERPAGE}
                            totalItemsCount={Total}
                            pageRangeDisplayed={5}
                            onChange={e => this.handlePageChange(e)}
                        />
                    </div>
                }

            </div>
        )
    }
}
export default LeaderboardDetails