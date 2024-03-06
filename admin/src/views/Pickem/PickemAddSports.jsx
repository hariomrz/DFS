import React, { Component, Fragment } from 'react';
import { Row, Col, Table, UncontrolledDropdown, DropdownToggle, DropdownMenu, DropdownItem, Button, Input } from 'reactstrap';
// import _ from 'lodash';
import WSManager from '../../helper/WSManager';
import Loader from '../../components/Loader';
import { MomentDateComponent } from "../../components/CustomComponent";
import * as NC from '../../helper/NetworkingConstants';
import SelectDropdown from "../../components/SelectDropdown";
import { _times, _Map, _isEmpty, _remove } from '../../helper/HelperFunction'
import { PT_SPORTS_MSG, PT_PRIORITY_MSG, PT_MSG_DELETE_SPORT } from '../../helper/Message'
import { saveSports, updateSports, rolesList, deletePTSports, enableSports } from '../../helper/WSCalling'
import { notify } from 'react-notify-toast';
import ActionRequestModal from '../../components/ActionRequestModal/ActionRequestModal';
class PickemAddSports extends Component {
    constructor(props) {
        super(props);
        this.state = {
            ListPosting: false,
            Total: 10,
            SportsSetting: "0",
            SPriorityOptions: [],
            formValid: false,
            PriorityType: 0,
            SportName: '',
            SportsList: [],
            Edit_Id: 0,
            disableBtn: false,
        };
    }

    componentDidMount() {
        this.getPriority()
        this.getSports()
    }

    getSports = () => {
        this.setState({ ListPosting: true })

        let params = {
            "current_page": 1,
            "keyword": "",
            "items_perpage": 100,
            "sort_field": "added_date",
            "sort_order": "DESC"
        }

        rolesList(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                ResponseJson = { "service_name": "roles\/roles_list", "message": "", "global_error": "", "error": [], "data": { "result": [{ "setting": "0", "admin_id": "100161", "firstname": "umm", "lastname": "management", "email": "um@mailinator.com", "status": "1", "access_list": ["user_management"] }, { "setting": "1", "admin_id": "100160", "firstname": "Sunil", "lastname": "Bhawsar", "email": "sunil@vinfotech.com", "status": "1", "access_list": ["report", "settings", "manage_finance"] }, { "setting": "0", "admin_id": "100174", "firstname": "Shreya", "lastname": "Gupta", "email": "shreya.gupta@vinfotech.com", "status": "1", "access_list": ["accounting", "manage_finance"] }, { "setting": "1", "admin_id": "100162", "firstname": "Akhielsh", "lastname": "Rathore", "email": "aff@mailinator.com", "status": "1", "access_list": ["dfs", "dashboard", "affiliate"] }], "total": 4 }, "response_code": 200 }
                this.setState({
                    SportsList: ResponseJson.data.result,
                    ListPosting: false
                }, () => {


                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    getPriority = () => {
        let tempPrio = []
        for (var i = 1; i < 11; ++i) {
            tempPrio.push({ value: i, label: i });
        }
        this.setState({ SPriorityOptions: tempPrio })
    }

    handleSportsEnable = (indx) => {
        let SportsList = this.state.SportsList

        SportsList[indx].setting = SportsList[indx].setting == "1" ? "0" : "1"
        this.setState({
            // SportsSetting: SportsSetting == "1" ? "0" : "1",
            SportsList,
            SportIdx: indx,
        }, () => {
            this.enableDisSports()
        })
    }

    enableDisSports = () => {
        let { SportsList, SportIdx } = this.state;

        this.setState({ disableBtn: true })
        let params = {
            'sports_id': SportsList[SportIdx].sport_id,
            'setting': SportsList[SportIdx].setting,
        }

        enableSports(params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({
                    disableBtn: false
                })
                notify.show(responseJson.message, "success", 5000);
            }
        });
    }

    handleChange = (e) => {
        let name = e.target.name;
        let value = e.target.value;
        this.setState({ SportName: value });
    }

    handlePrioChange = (value) => {
        this.setState({ PriorityType: value.value })
    }

    addSports = () => {
        let { SportName, PriorityType, Edit_Id } = this.state;
        if (_isEmpty(SportName) || SportName.length < 4 || SportName.length > 15) {
            notify.show(PT_SPORTS_MSG, "error", 3000);
            return false
        }

        if (PriorityType === 0) {
            notify.show(PT_PRIORITY_MSG, "error", 3000);
            return false
        }

        this.setState({ formValid: true })
        let params = {
            'sport_name': SportName,
            'priority': PriorityType,
        }

        let apiCall = saveSports
        if (Edit_Id > 0) {
            params.sport_id = Edit_Id
            apiCall = updateSports
        }

        apiCall(params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.getSports()
                this.setState({
                    SportName: '',
                    PriorityType: 0,
                    Edit_Id: 0,
                    formValid: false
                })
                notify.show(responseJson.message, "success", 5000);
            }
        });
    }


    toggleActionPopup = (sport_id, idx) => {
        this.setState({
            idxVal: idx,
            SportID: sport_id,
            ActionPopupOpen: !this.state.ActionPopupOpen
        })
    }

    deleteTrans = () => {
        let { SportID } = this.state
        this.setState({ delPosting: true })
        let params = {
            sport_id: SportID,
        }
        let TempQList = this.state.SportsList
        deletePTSports(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                _remove(TempQList, (item) => {
                    return item.sport_id == SportID
                })
                this.setState({
                    SportsList: TempQList,
                    delPosting: false,
                    ActionPopupOpen: false
                })

                notify.show(Response.message, 'success', 5000)
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    editSport = (item) => {
        this.setState({
            // Edit_Id: item.sport_id,
            // SportName : item.sport_name
            Edit_Id: 5,
            SportName: 'Cricket',
            PriorityType: 4,
        })
    }


    render() {
        let { ListPosting, Total, SportsSetting, SportName, PriorityType, SPriorityOptions, Edit_Id, formValid, SportsList, delPosting, ActionPopupOpen } = this.state
        const Select_Props = {
            is_disabled: false,
            is_searchable: true,
            is_clearable: false,
            menu_is_open: false,
            class_name: "pt-add-fields",
            sel_options: SPriorityOptions,
            place_holder: "Select Priority",
            selected_value: PriorityType,
            modalCallback: this.handlePrioChange
        }
        const ActionCallback = {
            posting: delPosting,
            Message: PT_MSG_DELETE_SPORT,
            modalCallback: this.toggleActionPopup,
            ActionPopupOpen: ActionPopupOpen,
            modalActioCallback: this.deleteTrans,
        }

        return (
            <Fragment>
                <div className="pt-addsports">
                    <ActionRequestModal {...ActionCallback} />
                    <Row>
                        <Col md={12}>
                            <h2 className="h2-cls mb-20">Add Sport</h2>
                        </Col>
                    </Row>
                    <div className="pt-bg-white">
                        <div className="pt-sports-name">
                            <Input
                                minLength="3"
                                maxLength="15"
                                type="text"
                                className="pt-add-fields"
                                name='SportName'
                                placeholder="Enter Sport Name"
                                onChange={this.handleChange}
                                value={SportName}
                            />
                        </div>
                        <div className="pt-sports-prio">
                            <SelectDropdown SelectProps={Select_Props} />
                        </div>
                        <div className="pt-sports-add">
                            <Button
                                disabled={formValid}
                                className="btn-secondary-outline mr-3"
                                onClick={() => this.addSports()}>
                                {Edit_Id > 0 ? 'Update' : 'Add'}
                            </Button>
                        </div>
                    </div>
                    <Row>
                        <Col md={12} className="table-responsive common-table">
                            <Table className="mb-0">
                                <thead>
                                    <tr>
                                        <th>Sport Name</th>
                                        <th>Created On</th>
                                        <th>Priority</th>
                                        <th>Enable / Disable</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                {
                                    Total > 0 ?
                                        _Map(SportsList, (item, idx) => {
                                            return (
                                                <tbody key={idx}>
                                                    <tr>
                                                        <td>Cricket</td>
                                                        <td>11/04/2020</td>
                                                        <td>1</td>
                                                        <td>
                                                            <div className="activate-module">
                                                                <label className="global-switch">
                                                                    <input
                                                                        type="checkbox"
                                                                        checked={item.setting == "1" ? false : true}
                                                                        onChange={() => this.handleSportsEnable(idx)}
                                                                    />
                                                                    <span className="switch-slide round">
                                                                        <span className={`switch-on ${item.setting == "1" ? 'active' : ''}`}></span>
                                                                        <span className={`switch-off ${item.setting == "0" ? 'active' : ''}`}></span>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div className="erp-t-action text-center">
                                                                <UncontrolledDropdown direction="left">
                                                                    <DropdownToggle tag="i" caret={false} className="icon-more cursor-pointer">
                                                                    </DropdownToggle>
                                                                    <DropdownMenu>
                                                                        {
                                                                            <DropdownItem
                                                                                onClick={() => this.editSport(item)}
                                                                            >Edit
                                                                    </DropdownItem>
                                                                        }
                                                                        <DropdownItem
                                                                            onClick={() => this.toggleActionPopup(item.sport_id, idx)}
                                                                        >Delete
                                                                    </DropdownItem>
                                                                    </DropdownMenu>
                                                                </UncontrolledDropdown>
                                                            </div>
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
                </div>
            </Fragment>
        )
    }
}
export default PickemAddSports