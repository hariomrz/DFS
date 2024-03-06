import React, { Component } from "react";
import { Row, Col, Table } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import { notify } from 'react-notify-toast';
import Pagination from "react-js-pagination";
import SelectDropdown from "../../components/SelectDropdown";
import HF from "../../helper/HelperFunction";
import { xpGetLevelList, xpLevelLeaderboard } from '../../helper/WSCalling';
import { _Map, _isNull } from "../../helper/HelperFunction";
import Loader from '../../components/Loader';
import { Base64 } from 'js-base64';
class LevelLeaderboard extends Component {
    constructor(props) {
        super(props)
        this.state = {
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            UserList: [],
            formValid: true,
            sortField: 'level_number',
            isDescOrder: 'true',
            ActionPopupOpen: false,
            SubActionPopupOpen: false,
            setDefPost: false,
            LevelSelected: '',
            ListPosting: false,
        };
    }
    componentDidMount() {
        if (HF.allowXpPoints() == '0') {
            notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        this.getLevel();
        this.getUserList();
    }

    getLevel = () => {
        const { CURRENT_PAGE } = this.state
        let params = {
            items_perpage: 1000,
            current_page: CURRENT_PAGE
        }
        xpGetLevelList(params).then(ApiResponse => {
            if (ApiResponse.response_code == NC.successCode) {
                let res = ApiResponse.data ? ApiResponse.data.level_list : []
                let l_arr = [{
                    value: '',
                    label: 'All'
                }]
                _Map(res, function (data) {
                    l_arr.push({
                        value: data.level_number,
                        label: data.level_str
                    });
                })
                this.setState({ LevelOptions: l_arr })
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

        }, this.getUserList)
    }

    getUserList = () => {
        this.setState({ ListPosting: true })
        const { PERPAGE, CURRENT_PAGE, isDescOrder, sortField, LevelSelected } = this.state
        let params = {
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
            sort_order: isDescOrder ? 'DESC' : 'ASC',
            sort_field: sortField,
            level_number: LevelSelected
        }


        xpLevelLeaderboard(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    UserList: ResponseJson.data ? ResponseJson.data.user_list : [],
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
                this.getUserList();
            });
        }
    }

    handleSelectChange = (value) => {
        if (!_isNull(value)) {
            this.setState({ CURRENT_PAGE: 1, ListPosting: true, LevelSelected: value.value }, () => {
                this.getUserList()
            })
        }
    }

    openHistory = (itm) => {
        console.log("itm==",itm);
        Base64.encode(itm.user_id)
        let n_url = '/admin/#/xp/userpointhistory/'+Base64.encode(itm.user_id)
        window.open(n_url, "_blank")
    }

    render() {
        let { UserList, CURRENT_PAGE, PERPAGE, Total, isDescOrder, sortField, LevelSelected, LevelOptions, ListPosting } = this.state

        const Select_Props = {
            is_disabled: false,
            is_searchable: false,
            is_clearable: false,
            menu_is_open: false,
            class_name: "custom-form-control",
            sel_options: LevelOptions,
            place_holder: "Select Level",
            selected_value: LevelSelected,
            modalCallback: this.handleSelectChange
        }

        return (
            <div className="leaderboard-level animated fadeIn">
                <div className="header-primary">Leaderboard</div>
                <div className="form-body">
                    <Row>
                        <Col md={4}>
                            <div className="input-box">
                                <label>Leaderboard</label>
                                <SelectDropdown SelectProps={Select_Props} />


                            </div>
                        </Col>
                    </Row>
                </div>

                <Row>
                    <Col md={12} className="table-responsive common-table mt-5">
                        <Table>
                            <thead>
                                <tr className="height-40">
                                    <th onClick={() => this.sortByColumn('modified_date', isDescOrder)}
                                    >
                                        Username
                                        <div className={`d-inline-block ${(sortField === 'modified_date' && isDescOrder) ? '' : 'rotate-icon'}`}>
                                            <i className="icon-Shape ml-1"></i>
                                        </div>
                                    </th>
                                    <th>Level</th>
                                    <th> Points</th>
                                </tr>
                            </thead>
                            {
                                Total > 0 ?
                                    _Map(UserList, (item, idx) => {
                                        return (
                                            <tbody key={idx}>
                                                <tr>
                                                    <td onClick={() => this.openHistory(item)} className="text-click">{item.user_name}</td>
                                                    <td>{item.level_str}</td>
                                                    <td>{item.points}</td>
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
export default LevelLeaderboard