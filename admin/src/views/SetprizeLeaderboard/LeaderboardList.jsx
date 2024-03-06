import React, { Component } from "react";
import { Row, Col, Table, Button, Modal, ModalBody, ModalFooter } from "reactstrap";
import _ from 'lodash';
import { LB_getLeaderboardList, LB_toggleLeaderboardById } from "../../helper/WSCalling";
import * as NC from '../../helper/NetworkingConstants';
import Pagination from "react-js-pagination";
import { notify } from 'react-notify-toast';
import Loader from '../../components/Loader';
import HF from "../../helper/HelperFunction";
import { MSG_DELETE_USER } from "../../helper/Message";
import PromptModal from '../../components/Modals/PromptModal';
import WSManager from "../../helper/WSManager";
class LeaderboardList extends Component {
    constructor(props) {
        super(props)
        this.state = {
            CURRENT_PAGE: 1,
            ITEMS_PERPAGE: NC.ITEMS_PERPAGE,
            addMoreModalOpen: false,
            ToggleModalOpen: false,
            LbList: [],
            ListPosting: false,
            Total: 0,
            CreatePosting: true,
            statusPosting: false,
            EditFlag: false,
            Username: '',
            ToggleModalOpen: false,
            StatusMsg: '',
            FromDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
            ToDate: new Date(),
            prize_id:''
        }
    }

    componentDidMount = () => {
        this.getLeaderboardList()
    }

    getLeaderboardList = () => {
        this.setState({ ListPosting: true })
        let {CURRENT_PAGE,ITEMS_PERPAGE} = this.state
        const param = { 
            // from_date: FromDate ? HF.getFormatedDateTime(FromDate, 'YYYY-MM-DD') : '',
            // to_date: ToDate ? HF.getFormatedDateTime(ToDate, 'YYYY-MM-DD') : '',
            items_perpage: ITEMS_PERPAGE,
            current_page: CURRENT_PAGE,
            sort_field: "prize_id",
            sort_order: "DESC" 
    }

        LB_getLeaderboardList(param).then(ApiResponse => {
        
            if (ApiResponse.response_code == NC.successCode) {
                this.setState({
                    LbList: ApiResponse.data.result ? ApiResponse.data.result : [],
                    Total: ApiResponse.data.total ? ApiResponse.data.total : 0,
                    ListPosting: false
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    deleteUserToggle = (d_admin_idx, admin_id) => {
        this.setState({
            AdminId: admin_id,
            ToggleModalOpen: !this.state.ToggleModalOpen
        })
    }

    handlePageChange(current_page) {
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            this.getLeaderboardList()
        });
    }

    changeStatusToggle = (item, idx) => {
        let msg = 'Are you sure you want to ' + (item.status == '1' ? 'in':'') + 'active this?'
        this.setState({
            CatIndex: idx,
            CATE_ID: item.category_id,
            StatusMsg: msg,
            prize_id:item.prize_id,
            status:item.status == '1' ? 0 : 1
        })

        this.setState(prevState => ({
            ToggleModalOpen: !prevState.ToggleModalOpen
        }));
    }

    changeCateStatus = () => {
        this.setState({ statusPosting: true })
        const { CatIndex, CATE_ID, LbList,prize_id,status } = this.state
        const param = { 'prize_id': prize_id,status:status }
        let tLbList = LbList

        LB_toggleLeaderboardById(param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                tLbList[CatIndex]['status'] = tLbList[CatIndex]['status'] == '0' ? '1' : '0'
                notify.show(responseJson.message, "success", 5000);
                this.setState({
                    LbList: tLbList,
                    statusPosting: false,
                    ToggleModalOpen: false,
                })
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    markCompleted = (e,item) => {
        e.stopPropagation();
        if (window.confirm("Are you sure want to mark complete ?")) {
          this.setState({ posting: true })
          let params = { "prize_id": item.prize_id};
          WSManager.Rest(NC.baseURL + NC.MARK_LEAGUE_COMPLETE, params).then((responseJson) => {
              if (responseJson.response_code === NC.successCode) {
                  notify.show(responseJson.message, "success", 5000);
                  this.getLeaderboardList()
              } else {
                  notify.show(responseJson.message, "error", 3000);
              }
            this.setState({ posting: false })
          })
        } else {
          return false;
        }
      }
    
    markCancel = (e,item) => {
        e.stopPropagation();
        if (window.confirm("Are you sure want to mark cancel ?")) {
          this.setState({ posting: true })
          let params = { "prize_id": item.prize_id};
          WSManager.Rest(NC.baseURL + NC.MARK_LEAGUE_CANCEL, params).then((responseJson) => {
              if (responseJson.response_code === NC.successCode) {
                  notify.show(responseJson.message, "success", 5000);
                  this.getLeaderboardList()
              } else {
                  notify.show(responseJson.message, "error", 3000);
              }
            this.setState({ posting: false })
          })
        } else {
          return false;
        }
      }

    render() {
        let { LbList, Total, ListPosting, CURRENT_PAGE, ITEMS_PERPAGE, ToggleModalOpen, statusPosting, StatusMsg } = this.state
        let DeleteModalProps = {
            publishModalOpen: ToggleModalOpen,
            publishPosting: statusPosting,
            modalActionNo: this.changeStatusToggle,
            modalActionYes: this.changeCateStatus,
            MainMessage: StatusMsg,
            SubMessage: '',
        }
        return (
            <React.Fragment>
                <div className="sp-ldrbrd-list">
                    {ToggleModalOpen && <PromptModal {...DeleteModalProps} />}
                    <Row className="prz-lb-head">
                        <Col md={12}>
                            <div className="float-left">
                                <h2 className="h2-cls">Leaderboard</h2>
                            </div>
                            <div className="float-right">
                                <Button onClick={() => { this.props.history.push({pathname:'/marketing/marketingleaderboard_setprize/' + 0})}} className="btn-secondary-outline">Add New</Button>
                            </div>
                        </Col>
                    </Row>

                    <Row>
                        <Col md={12} className="table-responsive common-table">
                            <Table className="mb-1">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        {/* <th>Type</th> */}
                                        <th>Name</th>
                                        <th>Status</th>
                                        {/* <th>Allow Prize</th> */}
                                        {/* <th>Allow Sponser</th> */}
                                        <th>Action</th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </thead>
                                {
                                    Total > 0 ?
                                        LbList.map((item, idx) => {
                                            var isShow = item.type == 4 && item.l_status && item.l_status !=0 ? false :true;
                                            return (
                                                <tbody key={idx}>
                                                    <tr>
                                                        <td>{item.category_name}</td>
                                                        {/* <td>{item.type}</td> */}
                                                        <td>{item.name}</td>
                                                        <td>
                                                            {item.status == '1' && 'Active'}
                                                            {item.status == '0' && 'Inactive'}
                                                            {item.status == '2' && 'Cancelled'}
                                                            {item.type==4 && item.is_complete== 0 && item.l_status == 0 ? ' | Live' : item.type==4 && item.is_complete == 0 && item.l_status == 1 ? ' | Cancelled' : item.type==4 && item.is_complete== 1 && item.l_status > 1 ? ' | Completed' : '' }

                                                        </td>
                                                        {/* <td>
                                                            {item.allow_prize == '1' && 'Yes'}
                                                            {item.allow_prize == '0' && 'No'}
                                                        </td> */}
                                                        {/* <td>
                                                            {item.allow_sponsor == '1' && 'Yes'}
                                                            {item.allow_sponsor == '0' && 'No'}
                                                        </td> */}
                                                        <td className="colFlex">
                                                       
                                                            {
                                                              isShow &&  
                                                              <i style={{ cursor: 'pointer' }}
                                                                onClick={() => { this.props.history.push({ pathname: '/marketing/marketingleaderboard_setprize/' + item.prize_id }) }}
                                                                className="icon-edit ml-4"></i>
                                                            }
                                                            {
                                                                isShow &&
                                                                <div className="activate-module">
                                                                    <label className="global-switch">
                                                                        <input
                                                                            type="checkbox"
                                                                            checked={item.status == "1" ? false : true}
                                                                            onChange={() => this.changeStatusToggle(item, idx)}
                                                                        />
                                                                        <span className="switch-slide round">
                                                                            <span className={`switch-on ${item.status == "1" ? 'active' : ''}`}>On</span>
                                                                            <span className={`switch-off ${item.status == "0" ? 'active' : ''}`}>Off</span>
                                                                        </span>
                                                                    </label>
                                                                </div>
                                                            }
                                                            
                                                        </td>
                                                        <td>
                                                            <span
                                                                className="btn-v-dtl"
                                                                onClick={() => { this.props.history.push({pathname:'/marketing/marketingleaderboard-details/' + item.prize_id})}}

                                                            >View Details</span>
                                                           
                                                        </td>
                                                        <td>
                                                        { item.type == 4 && item.is_complete == 0 && item.l_status == 0 ?
                                                                <Button style={{padding:'7px 15px'}}
                                                                className="btn-secondary-outline rebuplish-btn"
                                                                onClick={(e) => this.markCompleted(e, item)}
                                                            >
                                                                {'Mark Complete'}
                                                            </Button>
                                                            :
                                                            ''
                                                            }&nbsp;&nbsp;
                                                        { item.type == 4 && item.is_complete == 0 && item.l_status == 0 ?
                                                                <Button style={{padding:'7px 15px'}}
                                                                className="btn-secondary-outline rebuplish-btn"
                                                                onClick={(e) => this.markCancel(e, item)}
                                                            >
                                                                {'Mark Cancel'}
                                                            </Button>
                                                            :
                                                            ''
                                                            }
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            )
                                        })
                                        :
                                        <tbody>
                                            <tr>
                                                <td colSpan="8">
                                                    {(Total == 0 && !ListPosting) ?
                                                        <div className="no-records">
                                                            {NC.NO_RECORDS}</div>
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
                    <Row>
                        <Col md={12}>
                            {
                                Total > 5 &&
                                (<div className="custom-pagination float-right mt-5">
                                    <Pagination
                                        activePage={CURRENT_PAGE}
                                        itemsCountPerPage={ITEMS_PERPAGE}
                                        totalItemsCount={Total}
                                        pageRangeDisplayed={5}
                                        onChange={e => this.handlePageChange(e)}
                                    />
                                </div>)
                            }
                        </Col>
                    </Row>
                    {

                    }
                </div>
            </React.Fragment>
        )
    }
}
export default LeaderboardList
