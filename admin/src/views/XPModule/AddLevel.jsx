import React, { Component, Fragment } from "react";
import { Input, Button, Row, Col, Table } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Pagination from "react-js-pagination";
import _ from 'lodash';
import HF, { _remove } from "../../helper/HelperFunction";
import { XP_LEV_ENDP_EMPTY, XP_LEV_ENDP, XP_LEV_ENDP_G  } from "../../helper/Message"
import Loader from '../../components/Loader';
class AddLevel extends Component {
    constructor(props) {
        super(props)
        this.state = {
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            start_point: '',
            end_point: '',
            UserList: [],
            LevelList: [],
            formValid: true,
            sortField: 'level_number',
            isDescOrder: 'true',
            LevelSelected: '',
            next_level: '',
            ListPosting: false,
        };
    }
    componentDidMount() {
        if (HF.allowXpPoints() == '0') {
            notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        this.getLevelList();
        this.getLevelMasterData();


        console.log('Eslint Test');
    }


    sortByColumn(sortfiled, isDescOrder) {
        let Order = isDescOrder ? false : true
        this.setState({
            sortField: sortfiled,
            isDescOrder: Order,
            CURRENT_PAGE: 1,

        }, this.getLevelList)
    }


    getLevelMasterData = () => {
        const { } = this.state
        let params = {

        }

        WSManager.Rest(NC.baseURL + NC.XP_GET_ADD_MASTER_DATA, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {

                this.setState({
                    next_level: ResponseJson.data.next_level,
                    start_point: ResponseJson.data.start_point
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    getLevelList = () => {
        this.setState({ ListPosting: true })
        const { PERPAGE, CURRENT_PAGE, isDescOrder, sortField } = this.state
        let params = {
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
            sort_order: isDescOrder ? 'DESC' : 'ASC',
            sort_field: sortField,
        }

        WSManager.Rest(NC.baseURL + NC.XP_GET_LEVEL_LIST, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    LevelList: ResponseJson.data ? ResponseJson.data.level_list : [],
                    Total: ResponseJson.data.total ? ResponseJson.data.total : 0,
                    ListPosting: false,
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




    handleInputChange = (e) => {
        let name = e.target.name;
        let value = e.target.value;
        if (HF.isFloat(value)) {
            value = this.state.end_point
            notify.show(XP_LEV_ENDP, 'error', 1500)
            this.setState({ formValid: true })
            return false
        }
        this.setState({ [name]: value, formValid: false }, () => {

            if (_.isEmpty(this.state.end_point)) {
                notify.show(XP_LEV_ENDP_EMPTY, 'error', 1500)
                this.setState({ formValid: true })
                return false
            }

            if (parseInt(this.state.end_point) <= parseInt(this.state.start_point)) {
                notify.show(XP_LEV_ENDP_G, 'error', 1500)
                this.setState({ formValid: true })
                return false
            }

        });
    }

    SaveLevel = () => {
        this.setState({ formValid: true })
        let { start_point, end_point, next_level } = this.state

        let params = {
            "level_number": next_level,
            "start_point": start_point,
            "end_point": end_point
        }

        WSManager.Rest(NC.baseURL + NC.XP_ADD_LEVEL, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, 'success', 5000)
                this.setState({
                    end_point: '',
                    start_point: ''
                }, () => {
                    this.getLevelMasterData()
                    this.getLevelList()
                })

            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }


    GoToEdit = () => {
        this.props.history.push({ pathname: 'edit-level', state: '' })
    }

    render() {
        let { LevelList, start_point, end_point, CURRENT_PAGE, PERPAGE, Total, formValid, isDescOrder, sortField, next_level, ListPosting } = this.state
        return (
            <div className="add-level animated fadeIn">
                <div className="header-primary">Levels</div>
                <div className="form-body">
                    <Row>
                        <Col md={4}>
                            <div className="input-box">
                                <label>Next Level</label>
                                <Input
                                    className="form-control disable"
                                    type="text"
                                    placeholder="Points"
                                    name='next_level'
                                    value={next_level}
                                    disabled={true}
                                />


                            </div>
                        </Col>
                        <Col md={4}>
                            <div className="input-box">
                                <label>Starting Points</label>
                                <Input
                                    className="form-control disable"
                                    type="number"
                                    placeholder="Points"
                                    name='start_point'
                                    value={start_point}
                                    disabled={true}
                                />


                            </div>
                        </Col>
                        <Col md={4}>
                            <div className="input-box">
                                <label>Ending Points</label>
                                <Input
                                    className="form-control"
                                    type="number"
                                    placeholder="Points"
                                    name='end_point'
                                    value={end_point}
                                    onChange={(e) => this.handleInputChange(e)}
                                />

                            </div>
                        </Col>
                    </Row>
                    <Row className="text-center mt-5 mb-4">
                        <Col md={12}>
                            <Button
                                disabled={formValid}
                                className="btn-secondary-outline mr-3"
                                onClick={() => this.SaveLevel()}
                            >
                                Add Level
                            </Button>
                        </Col>
                    </Row>
                </div>
                <Row className="mt-5 text-right mb-3">
                    <Col md={12}>
                        <a className="edit-level-btn" onClick={() => this.GoToEdit()}><i className="icon-edit mr-1"></i> Edit Levels</a>
                    </Col>
                </Row>
                <Row>
                    <Col md={12} className="table-responsive common-table">
                        <Table>
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
                                    <th
                                        className="text-center"
                                        onClick={() => this.sortByColumn('start_point', isDescOrder)}
                                    >
                                        Starting Points
                                        <div className={`d-inline-block ${(sortField === 'start_point' && isDescOrder) ? '' : 'rotate-icon'}`}>
                                            <i className="icon-Shape ml-1"></i>
                                        </div>
                                    </th>
                                    <th
                                        className="text-center"
                                        onClick={() => this.sortByColumn('end_point', isDescOrder)}
                                    >
                                        Ending Points
                                        <div className={`d-inline-block ${(sortField === 'end_point' && isDescOrder) ? '' : 'rotate-icon'}`}>
                                            <i className="icon-Shape ml-1"></i>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            {
                                Total > 0 ?
                                    _.map(LevelList, (item, idx) => {
                                        return (
                                            <tbody key={idx}>
                                                <tr>
                                                    <td>{item.level_str}</td>
                                                    <td className="text-center">{item.start_point}</td>
                                                    <td className="text-center">{item.end_point}</td>
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
                {
                    Total > PERPAGE &&
                    <div className="custom-pagination lobby-paging">
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
export default AddLevel



















