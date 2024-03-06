import React, { Component } from "react";
import { Button, Row, Col, Table } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Pagination from "react-js-pagination";
import _ from 'lodash';
import AddActiviyModal from './AddActiviyModal';
import { xpGetActivitiesList, xpDelActivity, xpGetActivityMastList } from '../../helper/WSCalling';
import Loader from '../../components/Loader';
import HF, { _remove, _Map, _isEmpty } from "../../helper/HelperFunction";
import { XP_DELETE_LEVEL, XP_DELETE_LEVEL_SUB } from "../../helper/Message";
import PromptModal from '../../components/Modals/PromptModal';
class RewardsLevel extends Component {
    constructor(props) {
        super(props)
        this.state = {
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            sortField: 'activity_id',
            isDescOrder: 'true',
            AddActiModalOpen: false,
            listPosting: false,
            DeleteModalOpen: false,
            ActOptions: [],
        };
    }
    componentDidMount() {
        if (HF.allowXpPoints() == '0') {
            notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        this.getActivityList();
        this.getActivity()
    }

    getActivity = () => {
        let params = {}
        xpGetActivityMastList(params).then(ApiResponse => {
            if (ApiResponse.response_code == NC.successCode) {
                let res = ApiResponse.data ? ApiResponse.data : []
                let l_arr = []
                _Map(res, function (data) {
                    l_arr.push({
                        value: data.activity_master_id,
                        label: data.activity_title,
                        activity_type: data.activity_type,
                    });
                })
                this.setState({ ActOptions: l_arr })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    sortByColumn(sortfiled, isDescOrder) {
        let Order = isDescOrder ? false : true
        this.setState({
            sortField: sortfiled,
            isDescOrder: Order,
            CURRENT_PAGE: 1,

        }, this.getActivityList)
    }

    getActivityList = () => {
        this.setState({ listPosting: true })
        const { PERPAGE, CURRENT_PAGE, isDescOrder, sortField } = this.state
        let params = {
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
            sort_order: isDescOrder ? 'DESC' : 'ASC',
            sort_field: sortField,
        }

        xpGetActivitiesList(params).then(ApiResponse => {
            if (ApiResponse.response_code == NC.successCode) {
                this.setState({
                    ActivityList: ApiResponse.data ? ApiResponse.data.activities_list : [],
                    Total: ApiResponse.data ? ApiResponse.data.total : 0,
                    listPosting: false,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    handlePageChange(current_page) {
        if (current_page !== this.state.CURRENT_PAGE) {
            this.setState({
                CURRENT_PAGE: current_page
            }, () => {
                this.getActivityList();
            });
        }
    }

    //function to toggle action popup
    toggleActionPopup = (flg, edit_itm) => {
        if (!flg) {
            edit_itm = {}
        }
        this.setState({
            AddActiModalOpen: !this.state.AddActiModalOpen,
            EditRItem: edit_itm,
            EditFlag: flg,
        })
    }

    toggleYesActionPopup = (id) => {

        console.log("id==", id);

        this.setState({
            AddActiModalOpen: false,
        }, () => {
            this.getActivityList();
            // let t_act_list = this.state.ActOptions
            // _remove(t_act_list, function (item, idx) {
            //     return id == item.value
            // })
            // this.setState({ ActOptions: t_act_list })
        })
    }

    deleteToggle = (ACT_id, idx) => {
        this.setState(prevState => ({
            delIdx: idx,
            ACT_ID: ACT_id,
            DeleteModalOpen: !prevState.DeleteModalOpen
        }));
    }

    deleteReward = () => {
        this.setState({ DeletePosting: true })
        const { delIdx, ACT_ID, ActivityList } = this.state
        const param = { activity_id: ACT_ID }
        let t_act_list = ActivityList

        xpDelActivity(param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.getActivity();
                _remove(t_act_list, function (item, idx) {
                    return idx == delIdx
                })

                notify.show(responseJson.message, "success", 5000);
                this.setState({
                    ActivityList: t_act_list,
                    Total: t_act_list.length,
                })
            }
            this.setState({
                DeletePosting: false,
                DeleteModalOpen: false,
            })
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    render() {
        let { ActivityList, CURRENT_PAGE, PERPAGE, Total, AddActiModalOpen, Message, isDescOrder, sortField, listPosting, DeleteModalOpen, DeletePosting, EditRItem, ActOptions } = this.state
        const AddActivityModalProps = {
            Message: Message,
            modalCallback: this.toggleActionPopup,
            AddActiModalOpen: AddActiModalOpen,
            modalActioYesCallback: (id) => this.toggleYesActionPopup(id),
            EditRItem: EditRItem,
            ActOptions: ActOptions,
        }

        let DeleteModalProps = {
            publishModalOpen: DeleteModalOpen,
            publishPosting: DeletePosting,
            modalActionNo: this.deleteToggle,
            modalActionYes: this.deleteReward,
            MainMessage: XP_DELETE_LEVEL,
            SubMessage: XP_DELETE_LEVEL_SUB,
        }

        return (
            <div className="rewards-level animated fadeIn">
                {AddActiModalOpen && <AddActiviyModal {...AddActivityModalProps} />}
                {DeleteModalOpen && <PromptModal {...DeleteModalProps} />}

                <Row className="level-sub-header mb-20">
                    <Col xs={6}>
                        <h2>Activities:</h2>
                    </Col>
                    <Col xs={6} className="text-right">
                        <Button className="btn-secondary-outline" onClick={() => this.toggleActionPopup()}>Add Activities</Button>
                    </Col>
                </Row>
                <div className="white-container">
                    <Row>
                        <Col md={12} className="table-responsive common-table">
                            <Table>
                                <thead className="height-40">
                                    <tr>
                                        <th onClick={() => this.sortByColumn('activity_id', isDescOrder)}>
                                            Activity
                                        <div className={`d-inline-block ${(sortField === 'activity_id' && isDescOrder) ? '' : 'rotate-icon'}`}>
                                                <i className="icon-Shape ml-1"></i>
                                            </div>
                                        </th>
                                        <th className="cursor-default">Points</th>
                                        <th className="cursor-default">Type</th>
                                        <th className="text-center cursor-default">Action</th>
                                    </tr>
                                </thead>
                                {
                                    Total > 0 ?
                                        _.map(ActivityList, (item, idx) => {
                                            return (
                                                <tbody key={idx}>
                                                    <tr>
                                                        <td>{item.activity_title}</td>
                                                        <td>{item.xp_point}</td>
                                                        <td>
                                                            {item.activity_type == '1' && 'One time'}
                                                            {item.activity_type == '2' && 'Recurrent'}
                                                        </td>
                                                        <td className="text-center">
                                                            <a
                                                                className="action-icn"
                                                                onClick={() => this.toggleActionPopup(true, item)}
                                                            >
                                                                <i className="icon-edit"></i>
                                                            </a>
                                                            <a
                                                                className="action-icn"
                                                                onClick={() => this.deleteToggle(item.activity_id, idx)}
                                                            >
                                                                <i className="icon-delete"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            )
                                        })
                                        :
                                        <tbody>
                                            <tr>
                                                <td colSpan='22'>
                                                    {(Total == 0 && !listPosting) ?
                                                        <div className="no-records">{NC.NO_RECORDS}</div>
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
                {
                    Total > PERPAGE
                    && (
                        <div className="custom-pagination lobby-paging">
                            <Pagination
                                activePage={CURRENT_PAGE}
                                itemsCountPerPage={PERPAGE}
                                totalItemsCount={Total}
                                pageRangeDisplayed={5}
                                onChange={e => this.handlePageChange(e)}
                            />
                        </div>
                    )}
            </div>
        )
    }
}
export default RewardsLevel



















