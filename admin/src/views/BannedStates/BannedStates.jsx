import React, { Component } from "react";
import { Col, Row, FormGroup, Button, Table } from 'reactstrap';
import _ from 'lodash';
import { getcountryList } from "../../helper/WSCalling";
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import HF, { _isEmpty } from "../../helper/HelperFunction";
import LS from 'local-storage';
import WSManager from "../../helper/WSManager";
import Select from 'react-select';
import { STATE_DELETE_MSG } from "../../helper/Message";
import PromptModal from '../../components/Modals/PromptModal';
import Loader from '../../components/Loader';

class BannedStates extends Component {
    constructor(props) {
        super(props)
        this.state = {
            SelectedSport: LS.get('selected_sport') ? LS.get('selected_sport') : NC.sportsId,
            countryList: [],
            stateList: [],
            bannedList: [],
            selected_country: '',
            selected_state: '',
            search_text: '',
            DeleteModalOpen: false,
            DeletePosting: false,
        }
    }

    componentDidMount = () => {
        this.getcountryList()
        this.getBannedStates()
    }

    // GET COUNTRY LIST
    getcountryList = () => {
        let params = { }
        getcountryList(params).then(ApiResponse => {
            if (ApiResponse.response_code == NC.successCode) {
               
                this.createSelectList(ApiResponse.data, 'country');
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    createSelectList = (list, type) => {
    
        let listArr = list;
        if (type == 'country')
        {
            var tempArr = [{ value: "", label: "All" }];
        }

        if (type == 'country')
        {
            listArr.map(function (cObj, cKey) {
                tempArr.push({ value: cObj.master_country_id, label: cObj.name });
            });
            this.setState({ countryList: tempArr });
        }
        else if (type == 'state')
        {
            var tempArr = [];
            listArr.map(function (cObj, cKey) {
                tempArr.push({ value: cObj.master_state_id, label: cObj.name });
            });
            this.setState({ stateList: tempArr });
        }
    }

    handleSelect = (value, dropName) => {
        if (value)
        {
            if (dropName == "selected_country")
            {
                this.setState({ selected_country: value.value }, function () {
                    if (value.value != '')
                    {
                        this.getStateList(value.value);
                    }
                    else
                    {
                        this.setState({ stateList: [] });
                    }
                });
            }
            else
            {
                this.setState({ selected_state: value.value });
            }
        }
    }

    // GET STATE LIST
    getStateList = (selected_country_id) => {
        let param = {
            "master_country_id": selected_country_id,
        }
        WSManager.Rest(NC.baseURL + NC.GET_STATES_LIST, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.createSelectList(responseJson.data, 'state');
            }
        })
    }

    // GET STATE LIST
    getBannedStates = () => {
        let param = {
            "items_perpage": NC.ITEMS_PERPAGE_LG,
            "current_page": 1,
            "sort_order": "ASC",
            "sort_field": "id"
        }
        WSManager.Rest(NC.baseURL + NC.GET_BANNED_STATE_LIST, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                responseJson = responseJson.data;
                this.setState({
                    bannedList: responseJson.result
                })
            }
        })
    }

    addBannedState = (state_id) => {
        let param = {
            "master_state_id": state_id,
        }
        WSManager.Rest(NC.baseURL + NC.SAVE_BANNED_STATE, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode)
            {
                this.getBannedStates();
                this.setState({ selected_state: '' });
                notify.show(responseJson.message, 'success', 5000);
            }
            else
            {
                notify.show(NC.SYSTEM_ERROR, "error", 5000);
            }
        })
    }

    deleteToggle = (r_id) => {
        this.setState({
            stateId: r_id,
            DeleteModalOpen: !this.state.DeleteModalOpen
        })
    }

    removeBannedState = (state_id) => {
        const { stateId } = this.state
        this.setState({ DeletePosting: true })
        let param = {
            "master_state_id": stateId,
        }
        WSManager.Rest(NC.baseURL + NC.REMOVE_BANNED_STATE, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({ 
                    selected_state: '',
                    DeleteModalOpen: false,
                });
                this.getBannedStates();
            }
            this.setState({ DeletePosting: false })
        })
    }

    render() {
        let { countryList, bannedList, stateList, selected_state, DeleteModalOpen, DeletePosting } = this.state
        let DeleteModalProps = {
            publishModalOpen: DeleteModalOpen,
            publishPosting: DeletePosting,
            modalActionNo: this.deleteToggle,
            modalActionYes: this.removeBannedState,
            MainMessage: STATE_DELETE_MSG,
            SubMessage: '',
        }
        return (
            <div className="animated fadeIn team-list">
                {DeleteModalOpen && <PromptModal {...DeleteModalProps} />}
                <Col lg={12}>
                    <Row className="dfsrow">
                        <h2 className="h2-cls">Banned States</h2>
                    </Row>
                </Col>
                <Row>
                    <Col xs="12" sm="12" md="12">
                        <FormGroup className="float-right mr-3">
                            <div className="filters-area">
                                <Button
                                    disabled={!selected_state}
                                    onClick={() => this.addBannedState(selected_state)}
                                    className="rules-up-btn"
                                >Add to banned</Button>
                            </div>
                        </FormGroup>
                        <FormGroup className="float-right mr-3">
                            <Select
                                className="dfs-selector"
                                id="selected_state"
                                name="selected_state"
                                placeholder="Select State"
                                value={this.state.selected_state}
                                options={stateList}
                                onChange={(e) => this.handleSelect(e, 'selected_state')}
                            />
                        </FormGroup>
                        <FormGroup className="float-right mr-3">
                            <Select
                                className="dfs-selector"
                                id="selected_country"
                                name="selected_country"
                                placeholder="Select Country"
                                value={this.state.selected_country}
                                options={countryList}
                                onChange={(e) => this.handleSelect(e, 'selected_country')}
                            />
                        </FormGroup>
                    </Col>
                </Row>
                <Row>
                    <Col md={12} className="table-responsive common-table">
                    <Table>
                        <thead>
                            <tr>
                                <th>State - (Country)</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        {
                            !_.isEmpty(bannedList) ?
                                _.map(bannedList, (item, idx) => {
                                    return (
                                        <tbody key={idx}>
                                            <tr>
                                                <td>{item.state_name} - ({item.country_name})</td>
                                                {/* <td className="w-100-px"></td> */}
                                                <td >
                                                    <span style={{ cursor: 'pointer' }} class="icon-delete" onClick={() => this.deleteToggle(item.master_state_id)} ></span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    )
                                })
                                :
                                <tbody>
                                    <tr>
                                        <td colSpan="8">
                                            {(_.isEmpty(bannedList)) ?
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
        )
    }
}
export default BannedStates
