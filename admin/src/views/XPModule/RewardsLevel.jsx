import React, { Component, Fragment } from "react";
import { Input, Button, Modal, ModalHeader, ModalBody, ModalFooter, Row, Col, Table } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Pagination from "react-js-pagination";
import AddRewardsModal from './AddRewardsModal';
import HF, { _times, _Map, _isEmpty, _isNull, _isUndefined, _remove, _find } from "../../helper/HelperFunction";
import PromptModal from '../../components/Modals/PromptModal';
import { xpDeleteReward } from '../../helper/WSCalling';
import { XP_DELETE_LEVEL, XP_DELETE_LEVEL_SUB } from "../../helper/Message";
import Loader from '../../components/Loader';

class RewardsLevel extends Component {
    constructor(props) {
        super(props)
        this.state = {
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            StartingPoints: '',
            RewardsList: [],
            formValid: true,
            sortField: 'level_number',
            isDescOrder: false,
            AddRewardsModalOpen: false,
            SubAddRewardsModalOpen: false,
            LevelSelected: '',
            DeleteModalOpen: false,
            DeletePosting: false,
            listPosting: false,
        };
    }
    componentDidMount() {
        if (HF.allowXpPoints() == '0') {
            notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        this.getRewardList();
    }

    sortByColumn(sortfiled, isDescOrder) {
        let Order = isDescOrder ? false : true
        this.setState({
            sortField: sortfiled,
            isDescOrder: Order,
            CURRENT_PAGE: 1,

        }, this.getRewardList)
    }

    getRewardList = () => {
        this.setState({ listPosting: true })
        const { PERPAGE, CURRENT_PAGE, isDescOrder, sortField } = this.state
        let params = {
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
            sort_order: isDescOrder ? 'DESC' : 'ASC',
            sort_field: sortField,
        }

        WSManager.Rest(NC.baseURL + NC.XP_GET_XP_REWARD_LIST, params).then(ApiResponse => {
            if (ApiResponse.response_code == NC.successCode) {
                this.setState({
                    RewardsList: ApiResponse.data ? ApiResponse.data.reward_list : [],
                    Total: ApiResponse.data ? ApiResponse.data.total : '',
                    listPosting: false
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
                this.getRewardList();
            });
        }
    }

    //function to toggle action popup
    toggleActionPopup = (flg, edit_itm) => {
        if (!flg) {
            edit_itm = {}
        }

        this.setState({
            AddRewardsModalOpen: !this.state.AddRewardsModalOpen,
            EditRItem: edit_itm,
            EditFlag: flg,
        })
    }

    deleteToggle = (rew_id, idx) => {
        this.setState(prevState => ({
            deleteIndex: idx,
            REW_ID: rew_id,
            DeleteModalOpen: !prevState.DeleteModalOpen
        }));
    }

    deleteReward = () => {
        this.setState({ DeletePosting: true })
        const { deleteIndex, REW_ID, RewardsList } = this.state
        const param = { reward_id: REW_ID }
        let tempRewardsList = RewardsList

        xpDeleteReward(param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                _remove(tempRewardsList, function (item, idx) {
                    return idx == deleteIndex
                })

                notify.show(responseJson.message, "success", 5000);
                this.setState({
                    RewardsList: tempRewardsList,
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

    toggleYesActionPopup = () => {        
        this.setState({
            AddRewardsModalOpen: false,
            EditRItem : {}
        }, () => {
            this.getRewardList();
        })
    }

    render() {
        let { RewardsList, CURRENT_PAGE, PERPAGE, Total, AddRewardsModalOpen, Message, formValid, isDescOrder, sortField, DeleteModalOpen, DeletePosting, listPosting, EditRItem, EditFlag } = this.state
        const AddRewardsModalProps = {
            Message: Message,
            modalCallback: () => this.toggleActionPopup(false, {}),
            AddRewardsModalOpen: AddRewardsModalOpen,
            modalActioCallback: this.toggleYesActionPopup,
            EditRItem: EditRItem,
            EditFlag: EditFlag,
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
                <AddRewardsModal {...AddRewardsModalProps} />
                {DeleteModalOpen && <PromptModal {...DeleteModalProps} />}

                <Row className="level-sub-header mb-20">
                    <Col xs={6}>
                        <h2 className="animate-left">Rewards:</h2>
                    </Col>
                    <Col xs={6} className="text-right">
                        <Button className="btn-secondary-outline animate-right" onClick={() => this.toggleActionPopup(false, {})}>Add Rewards</Button>
                    </Col>
                </Row>
                <div className="xwhite-container">
                    <Row>
                        <Col md={12} className="table-responsive common-table">
                            <Table className="animate-top">
                                <thead className="height-40">
                                    <tr>
                                        <th
                                            onClick={() => this.sortByColumn('level_number', isDescOrder)}
                                        >
                                            Level
                                            <div className={`d-inline-block ${(sortField === 'level_number' && isDescOrder) ? '' : 'rotate-icon'}`}>
                                                <i className="icon-Shape ml-1"></i>
                                            </div>
                                        </th>
                                        <th>Rewards</th>
                                        <th className="text-center">Action</th>
                                    </tr>
                                </thead>
                                {
                                    Total > 0 ?
                                        _Map(RewardsList, (item, idx) => {

                                            let rew_str = item.badge_name + ','
                                            if (Number(item.coins.allow))
                                                rew_str += ' Coins-' + item.coins.amt + ', '
                                            if (Number(item.deposit_cashback.allow))
                                                rew_str += ' Cashback(' + (Number(item.deposit_cashback.type == 1) ? 'B' : HF.getCurrencyCode()) + ') -' + item.deposit_cashback.amt + '%' + ' (' + item.deposit_cashback.cap + '), '
                                            if (Number(item.joining_cashback.allow))
                                                rew_str += ' Contest Discount(' + (Number(item.joining_cashback.type == 1) ? 'B' : HF.getCurrencyCode()) + ') -' + item.joining_cashback.amt + '%' + ' (' + item.joining_cashback.cap + ') '

                                            rew_str = rew_str.replace(/,\s*$/, "");

                                            return (
                                                <tbody key={idx}>
                                                    <tr>
                                                        <td>
                                                            {item.level_str}
                                                        </td>
                                                        <td>
                                                            {rew_str}
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
                                                                onClick={() => this.deleteToggle(item.reward_id, idx)}
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



















