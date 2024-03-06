import React, { Component, Fragment } from "react";
import { Input, Button, Row, Col, Table } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Pagination from "react-js-pagination";
import { XP_DELETE_LEVEL, XP_DELETE_LEVEL_SUB } from "../../helper/Message";
import PromptModal from '../../components/Modals/PromptModal';
import { xpDeleteLevel, xpUpdateLevel } from '../../helper/WSCalling';
import HF, { _times, _Map, _isEmpty, _isNull, _isUndefined, _remove, _find } from "../../helper/HelperFunction";
import Loader from '../../components/Loader';
class AddLevel extends Component {
    constructor(props) {
        super(props)
        this.state = {
            PERPAGE: NC.ITEMS_PERPAGE,
            // PERPAGE: 10,
            CURRENT_PAGE: 1,
            start_point: '',
            end_point: '',
            LevelList: [],            
            formValid: true,
            sortField: 'level_number',
            isDescOrder: 'false',
            setDefPost: false,
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
        this.getLevelList();
    }


    getLevelList = () => {
        this.setState({ listPosting: true })
        const { PERPAGE, CURRENT_PAGE, isDescOrder, sortField } = this.state
        let params = {
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
            // sort_order: isDescOrder ? 'DESC' : 'ASC',
            sort_order: 'ASC',
            sort_field: sortField,
        }

        WSManager.Rest(NC.baseURL + NC.XP_GET_LEVEL_LIST, params).then(ApiResponse => {
            if (ApiResponse.response_code == NC.successCode) {
                this.setState({
                    LevelList: ApiResponse.data ? ApiResponse.data.level_list : [],
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
                this.getLevelList();
            });
        }
    }

    handleInputChange = (e, idx, name) => {
        let { LevelList } = this.state
        let value = e.target.value;
        let upIdx = idx + 1;
        
        if (_isEmpty(value)) {
            let msg = LevelList[idx].level_str + ' ending points can not be empty.'
            notify.show(msg, 'error', 3000)
        }
        if (HF.isFloat(value)) {
            let msg = LevelList[idx].level_str + ' ending points can not be decimal.'
            notify.show(msg, 'error', 3000)
        }
        if (value == LevelList[idx].start_point) {
            let msg = LevelList[idx].level_str + ' starting and ending points can not be same.'
            notify.show(msg, 'error', 3000)
        }

        _Map(LevelList, (itm, indx) => {
            LevelList[idx].end_point = value;
            if (!_isUndefined(LevelList[upIdx])) {
                LevelList[upIdx].start_point = Number(value) + 1;
            }
        })
        this.setState({ LevelList, formValid: false })
    }

    SaveLimit = () => {
        this.setState({ formValid: true })
        let { LevelList } = this.state
        let flag = true
        let params = []
        _Map(LevelList, (itm) => {
            if (_isEmpty(itm.end_point)) {
                let msg = itm.level_str + ' ending points can not be empty.'
                notify.show(msg, 'error', 3000)
                flag = false
            }
            else if (HF.isFloat(itm.end_point)) {
                let msg = itm.level_str + ' ending points can not be decimal.'
                notify.show(msg, 'error', 3000)
            }
            else if (itm.end_point == itm.start_point) {
                let msg = itm.level_str + ' starting and ending points can not be same.'
                notify.show(msg, 'error', 3000)
                flag = false
            }
            if (Number(itm.start_point) > Number(itm.end_point)) {
                let msg = itm.level_str + ' ending points should be greater than starting points.'
                notify.show(msg, 'error', 3000)
                flag = false
            }

            params.push({
                "level_number": itm.level_number,
                "start_point": itm.start_point,
                "end_point": itm.end_point
            })
        })


        if (!flag) {
            this.setState({ formValid: true })
            return false;
        }

        xpUpdateLevel(params).then(ApiResponse => {
            if (ApiResponse.response_code == NC.successCode) {
                this.setState({ formValid: true })
                notify.show(ApiResponse.message, 'success', 5000)
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    GoToLevels = () => {
        this.props.history.push({ pathname: 'add-level', state: '' })
    }

    deleteToggle = (lev_id, idx) => {
        this.setState(prevState => ({
            deleteIndex: idx,
            LEV_ID: lev_id,
            DeleteModalOpen: !prevState.DeleteModalOpen
        }));
    }

    deleteLevel = () => {
        this.setState({ DeletePosting: true })
        const { deleteIndex, LEV_ID, LevelList } = this.state
        const param = { level_pt_id: LEV_ID }
        let tempLevelList = LevelList

        xpDeleteLevel(param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                _remove(tempLevelList, function (item, idx) {
                    return idx == deleteIndex
                })

                notify.show(responseJson.message, "success", 5000);
                this.setState({
                    LevelList: tempLevelList,
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
        let { LevelList, CURRENT_PAGE, PERPAGE, Total, formValid, DeleteModalOpen, DeletePosting, listPosting } = this.state

        let DeleteModalProps = {
            publishModalOpen: DeleteModalOpen,
            publishPosting: DeletePosting,
            modalActionNo: this.deleteToggle,
            modalActionYes: this.deleteLevel,
            MainMessage: XP_DELETE_LEVEL,
            SubMessage: XP_DELETE_LEVEL_SUB,
        }

        return (
            <div className="edit-level animated fadeIn">
                {DeleteModalOpen && <PromptModal {...DeleteModalProps} />}
                <Row className="level-sub-header mb-20">
                    <Col xs={6}>
                        <h2 className="animate-left">Edit Levels:</h2>
                    </Col>
                    <Col xs={6} >
                        <span onClick={() => this.GoToLevels()} className="animate-right"> <i className="icon-Shape"></i> Back to Levels</span>
                    </Col>
                </Row>
                <div className="white-container xanimate-left">
                    <Row>
                        <Col md={12} className="table-responsive common-table">
                            <Table>
                                <thead className="height-40">
                                    <tr>
                                        <th className="cursor-default">Level's (Not Editable)</th>
                                        <th className="text-center cursor-default">Starting Points</th>
                                        <th className="text-center cursor-default">Ending Points</th>
                                        <th className="text-center cursor-default"></th>
                                    </tr>
                                </thead>
                                {
                                    Total > 0 ?
                                        _Map(LevelList, (item, idx) => {
                                            return (
                                                <tbody key={idx}>
                                                    <tr>
                                                        <td>
                                                            {item.level_str}
                                                        </td>
                                                        <td>
                                                            <div className="input-box p-0">

                                                                <Input
                                                                    className="form-control disable"
                                                                    type="number"
                                                                    name='start_point'
                                                                    value={item.start_point}
                                                                    //onChange={(e) => this.handleInputChange(e,idx,'start_point')}
                                                                    disabled={true}
                                                                />

                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div className="input-box p-0">
                                                                <Input
                                                                    className="form-control"
                                                                    type="number"
                                                                    name='end_point'
                                                                    value={item.end_point}
                                                                    onChange={(e) => this.handleInputChange(e, idx, 'end_point')}
                                                                />

                                                            </div>
                                                        </td>
                                                        <td>
                                                            {idx == LevelList.length - 1 &&
                                                                <a
                                                                    className="pointer"
                                                                    onClick={() => this.deleteToggle(item.level_pt_id, idx)}>
                                                                    <i className="icon-delete"></i>
                                                                </a>}
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
                    <Row className="text-center mt-5 pb-4">
                        <Col md={12}>
                            <Button
                                disabled={formValid}
                                className="btn-secondary-outline mr-3"
                                onClick={() => this.SaveLimit()}

                            >
                                Update Level
                            </Button>
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
export default AddLevel



















