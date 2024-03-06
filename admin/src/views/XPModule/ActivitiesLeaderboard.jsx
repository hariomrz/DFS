import React, { Component } from "react";
import { Row, Col, Table } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import { notify } from 'react-notify-toast';
import Pagination from "react-js-pagination";
import SelectDropdown from "../../components/SelectDropdown";
import HF from "../../helper/HelperFunction";
import { xpGetActivitiesList, xpActivitiesLeaderboard } from '../../helper/WSCalling';
import { _Map, _isNull } from "../../helper/HelperFunction";
import Loader from '../../components/Loader';
class ActivitiesLeaderboard extends Component {
    constructor(props) {
        super(props)
        this.state = {
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            UserList: [],
            formValid: true,
            ActionPopupOpen: false,
            SubActionPopupOpen: false,
            setDefPost: false,
            ActSelected: '',
            ListPosting: false,
        };
    }
    componentDidMount() {
        if (HF.allowXpPoints() == '0') {
            notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        this.getActivity();
    }

    getActivity = () => {
        const { CURRENT_PAGE } = this.state
        let params = {
            items_perpage: 1000,
            current_page: CURRENT_PAGE
        }
        xpGetActivitiesList(params).then(ApiResponse => {
            if (ApiResponse.response_code == NC.successCode) {
                let res = ApiResponse.data ? ApiResponse.data.activities_list : []
                let l_arr = []
                _Map(res, function (data) {
                    let msg = data.activity_title
                    
                    if(data.activity_type == '2')
                    msg = data.activity_title + ' (Recurrent count: ' +data.recurrent_count + ')'
                    l_arr.push({
                        value: data.activity_id,
                        label: msg
                    });
                })
                this.setState({ ActiOptions: l_arr })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    getUserList = () => {
        this.setState({ ListPosting: true })
        const { PERPAGE, CURRENT_PAGE, isDescOrder, sortField, ActSelected } = this.state
        let params = {
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
            sort_order: isDescOrder ? 'DESC' : 'ASC',
            sort_field: sortField,
            activity_id: ActSelected
        }


        xpActivitiesLeaderboard(params).then(ResponseJson => {
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
        if (!_isNull(value) && this.state.ActSelected != value.value) {
            this.setState({ CURRENT_PAGE: 1, ListPosting: true, ActSelected: value.value }, () => {
                this.getUserList()
            })
        }
    }

    render() {
        let { UserList, CURRENT_PAGE, PERPAGE, Total, isDescOrder, sortField, ActSelected, ActiOptions, ListPosting } = this.state

        const Select_Props = {
            is_disabled: false,
            is_searchable: false,
            is_clearable: false,
            menu_is_open: false,
            class_name: "custom-form-control",
            sel_options: ActiOptions,
            place_holder: "Select Activity",
            selected_value: ActSelected,
            modalCallback: this.handleSelectChange
        }

        return (
            <div className="leaderboard-level animated fadeIn">
                <div className="header-primary">Leaderboard</div>
                <div className="form-body">
                    <Row>
                        <Col md={4}>
                            <div className="input-box">
                                <label>Activities</label>
                                <SelectDropdown SelectProps={Select_Props} />
                            </div>
                        </Col>
                    </Row>
                </div>

                <Row className={`${ActSelected ? 'opc-1' : 'opc-0'}`}>
                    <Col md={12} className="table-responsive common-table mt-5">
                        <Table>
                            <thead>
                                <tr className="height-40">
                                    <th className="cursor-default">Username</th>
                                    <th className="cursor-default"> Points</th>
                                </tr>
                            </thead>
                            {
                                Total > 0 ?
                                    _Map(UserList, (item, idx) => {
                                        return (
                                            <tbody key={idx}>
                                                <tr>
                                                    <td>{item.user_name}</td>
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
export default ActivitiesLeaderboard