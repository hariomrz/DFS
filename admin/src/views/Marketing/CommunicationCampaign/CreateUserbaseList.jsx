import React, { Component, Fragment } from 'react';
import { Row, Col, Button, Input, Modal, ModalHeader, ModalBody, ModalFooter } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../../helper/NetworkingConstants";
import WSManager from "../../../helper/WSManager";
import { notify } from 'react-notify-toast';
import * as MODULE_C from "../Marketing.config";
import { Multiselect } from 'multiselect-react-dropdown';
import { SPORT_PREF_HEADTITLE, SYSTEM_ERROR, MIN_MAX_ERROR, MAX_GREATER_ERROR } from "../../../helper/Message";
class CreatUserbaseList extends Component {
    constructor(props) {
        super(props)
        this.state = {
            SportsPrefModalOpen: false,
            SportsPref: [],
            Locations: [],
            SavedSportsPref: [],
            SaveSPPosting: true,
            UserbaseData: {
                "age_group":
                {
                    "status": 0,
                    "min_value": "",
                    "max_value": ""
                }
                ,
                "admin_created_contest_lost":
                {
                    "status": 0,
                    "min_value": "",
                    "max_value": ""
                }
                ,
                "admin_created_contest_join":
                {
                    "status": 0,
                    "min_value": "",
                    "max_value": ""
                }
                ,
                "admin_created_contest_won":
                {
                    "status": 0,
                    "min_value": "",
                    "max_value": ""
                }
                ,
                "private_contest_won":
                {
                    "status": 0,
                    "min_value": "",
                    "max_value": ""
                }
                ,
                "private_contest_lost":
                {
                    "status": 0,
                    "min_value": "",
                    "max_value": ""
                }
                ,
                "private_contest_join":
                {
                    "status": 0,
                    "min_value": "",
                    "max_value": ""
                }
                ,
                "money_deposit":
                {
                    "status": 0,
                    "min_value": "",
                    "max_value": ""
                }
                ,
                "money_won":
                {
                    "status": 0,
                    "min_value": "",
                    "max_value": ""
                }
                ,
                "money_lost":
                {
                    "status": 0,
                    "min_value": "",
                    "max_value": ""
                }
                ,
                "coin_earn":
                {
                    "status": 0,
                    "min_value": "",
                    "max_value": ""
                }
                ,
                "coin_lost":
                {
                    "status": 0,
                    "min_value": "",
                    "max_value": ""
                }
                ,
                "coin_redeem":
                {
                    "status": 0,
                    "min_value": "",
                    "max_value": ""
                }
                ,
                "referral":
                {
                    "status": 0,
                    "min_value": "",
                    "max_value": ""
                }
                ,
                "sport_preference":
                {
                    "status": 0,
                    "sport_preference": [

                    ]
                }
                ,
                "location":
                {
                    "status": 0,
                    "location": [

                    ]
                }
                ,
                "gender":
                {
                    "status": 0,
                    "gender": [

                    ]
                }
                ,
                "profile_status":
                {
                    "status": 0,
                    "verified": 0,
                    "not_verified": 0
                }

            },
            selectedSportsPref: [],
            saveUbListPosting: true,
            ub_list_id: (this.props.match) ? this.props.match.params.ub_list_id : 0,
            getCountPosting: false,
            TotalUsers: 0,
            UserIds: [],
            DefineCheck: [],
        }
    }

    componentDidMount = () => {
        this.getSavedSportsPref()
        this.getLocations()
        if (this.state.ub_list_id > 0)
            this.getUserbaseList(this.state.ub_list_id)
    }

    getSavedSportsPref = () => {
        WSManager.Rest(NC.baseURL + MODULE_C.GET_PREFERENCE_LIST, {}).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                var sports_pref = [];
                _.map(responseJson.data, (item) => {
                    if (item.status == "1") {
                        sports_pref.push({
                            id: item.sports_id,
                            name: item.sports_name
                        });
                    }
                });

                this.setState({
                    SavedSportsPref: responseJson.data,
                    SportsPref: sports_pref
                });
            }
        }).catch((error) => {
            notify.show(SYSTEM_ERROR, "error", 5000);
        })
    }

    getLocations = () => {
        WSManager.Rest(NC.baseURL + MODULE_C.GET_CITY_NAMES, {}).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                var locations_city = [];
                _.map(responseJson.data, (item) => {
                    locations_city.push({
                        id: item.city,
                        name: item.city
                    });
                });
                this.setState({ Locations: locations_city });
            }
        }).catch((error) => {
            notify.show(SYSTEM_ERROR, "error", 5000);
        })
    }

    addSportsPrefToggle = () => {
        this.setState({ SportsPrefModalOpen: !this.state.SportsPrefModalOpen })
    }

    handleSpoPrefCheckbox = (e, indexV, save_sportPref) => {
        let { SavedSportsPref } = this.state

        let temp_saved_sp = SavedSportsPref
        temp_saved_sp[indexV].status = save_sportPref == 1 ? 0 : 1
        temp_saved_sp[indexV].min_value = save_sportPref == 0 ? "" : ""
        temp_saved_sp[indexV].max_value = save_sportPref == 1 ? "" : ""

        this.setState({ SavedSportsPref: temp_saved_sp, SaveSPPosting: false })
    }

    SpoPrefInputChange = (e, indexV) => {
        let { SavedSportsPref } = this.state
        let name = e.target.name;
        let value = e.target.value;
        let temp_saved_sp = SavedSportsPref

        temp_saved_sp[indexV][name] = value
        this.setState({ SavedSportsPref: temp_saved_sp, SaveSPPosting: false })
    }

    checkSpoPrefAndSave = () => {
        let { SavedSportsPref } = this.state
        let saveFlag = true
        _.map(SavedSportsPref, (item) => {
            if (item.status == 1) {
                if ((_.isEmpty(item.min_value) || _.isEmpty(item.max_value))) {
                    saveFlag = false
                    notify.show(MIN_MAX_ERROR + " for " + item.sports_name.toLowerCase(), "error", 3000);
                    return false;
                }
                else if (!item.min_value.match(/^[0-9-]*$/) || !item.max_value.match(/^[0-9-]*$/)) {
                    saveFlag = false
                    notify.show(MIN_MAX_ERROR + " for " + item.sports_name.toLowerCase(), "error", 3000);
                    return false;
                }
                else if (parseInt(item.max_value) < parseInt(item.min_value)) {
                    saveFlag = false
                    notify.show(MAX_GREATER_ERROR + " for " + item.sports_name.toLowerCase(), "error", 3000);
                    return false;
                }
            }
        })
        if (saveFlag)
            this.SaveSportPreInDB()
    }

    SaveSportPreInDB = () => {
        let params = this.state.SavedSportsPref;
        params.forEach(function (v) { delete v.sports_name });
        WSManager.Rest(NC.baseURL + MODULE_C.UPDATE_PREFERENCE_LIST, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                notify.show(responseJson.message, "success", 5000);
                this.setState({ SaveSPPosting: true })
                this.getSavedSportsPref()
                this.addSportsPrefToggle()
            }
        }).catch((error) => {
            notify.show(SYSTEM_ERROR, "error", 5000);
        })
    }

    onSelectDropdown = (e, array_name) => {
        let { UserbaseData } = this.state
        let temp_ub_data = UserbaseData
        temp_ub_data[array_name][array_name] = e
        this.setState({ UserbaseData: temp_ub_data, DefineCheck: e.length })
    }

    onRemoveDropdown = (e, array_name) => {
        let { UserbaseData } = this.state
        let temp_ub_data = UserbaseData
        temp_ub_data[array_name][array_name] = e
        this.setState({ UserbaseData: temp_ub_data, DefineCheck: e.length })
    }

    checkUbListAndSave = (callFrom) => {
        let { UserbaseData } = this.state
        let saveFlag = true
        _.map(UserbaseData, (item, key) => {
            if (key != 'user_base_list_id' && key != 'status' && key != 'list_name' && key != 'count' && key != 'added_date' && key != 'sport_id' && key != 'user_ids') {
                if (item.status == 1) {
                    let splitKey = key ? key.replace(/_/g, " ") : ''
                    if (key != 'sport_id') {
                        if (key === 'sport_preference' || key === 'location' || key === 'gender') {
                            if (key === 'sport_preference' && _.isEmpty(item.sport_preference)) {
                                saveFlag = false
                                notify.show("Please select " + splitKey, "error", 3000);
                                return false;
                            }
                            else if (key === 'location' && _.isEmpty(item.location)) {
                                saveFlag = false
                                notify.show("Please select " + splitKey, "error", 3000);
                                return false;
                            }
                            else if (key === 'gender' && _.isEmpty(item.gender)) {
                                saveFlag = false
                                notify.show("Please select " + splitKey, "error", 3000);
                                return false;
                            }
                        }
                        else if (key === 'profile_status') {
                            if (item.verified == 0 && item.not_verified == 0) {
                                saveFlag = false
                                notify.show("Please select " + splitKey, "error", 3000);
                                return false;
                            }
                        }
                        else {

                            if (key === 'age_group' && parseInt(item.min_value) < 18) {
                                saveFlag = false
                                notify.show("Age group min value should be greater than equal to 18 years", "error", 3000);
                                return false;
                            }
                            if (key === 'age_group' && parseInt(item.max_value) > 150) {
                                saveFlag = false
                                notify.show("Age group max value should be less than 150 years ", "error", 3000);
                                return false;
                            }

                            if ((_.isEmpty(item.min_value) || _.isEmpty(item.max_value))) 
                            {
                                saveFlag = false
                                notify.show(MIN_MAX_ERROR + " for " + splitKey, "error", 3000);
                                return false;
                            }
                            else if (!item.min_value.trim().match(/^[0-9-]*$/) || !item.max_value.trim().match(/^[0-9-]*$/)) 
                            {
                                saveFlag = false
                                notify.show(MIN_MAX_ERROR + " for " + splitKey, "error", 3000);
                                return false;
                            }
                            else if (parseInt(item.max_value) < parseInt(item.min_value)) 
                            {
                                saveFlag = false
                                notify.show(MAX_GREATER_ERROR + " for " + splitKey, "error", 3000);
                                return false;
                            }
                        }
                    }
                }
            }
        })
        if (saveFlag && callFrom === 1)
            this.getUserCount()
        if (saveFlag && callFrom === 2)
            this.SaveUbListInDB()
    }

    SaveUbListInDB = () => {
        this.setState({ saveUbListPosting: true })
        let { UserbaseData, ub_list_id, TotalUsers, UserIds } = this.state

        if (TotalUsers == 0) {
            notify.show("User base list can not be created with 0 user.", "error", 5000);
            return false
        }

        UserbaseData.count = TotalUsers
        UserbaseData.user_ids = UserIds
        let params = UserbaseData
        let URL = MODULE_C.CREATE_USER_BASE_LIST
        if (ub_list_id > 0)
            URL = MODULE_C.UPDATE_USER_BASE_LIST

        WSManager.Rest(NC.baseURL + URL, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({ saveUbListPosting: false })
                notify.show(responseJson.message, "success", 5000);
                this.props.history.push('/marketing/new_campaign')
            }
        }).catch((error) => {
            notify.show(SYSTEM_ERROR, "error", 5000);
        })
    }

    getUserCount = () => {
        let { UserbaseData } = this.state
        this.setState({ getCountPosting: true })
        let params = UserbaseData
        WSManager.Rest(NC.baseURL + MODULE_C.GET_USER_COUNT, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({
                    TotalUsers: responseJson.data ? responseJson.data.user_count : 0,
                    UserIds: responseJson.data ? responseJson.data.user_ids : [],
                }, () => {
                    this.setState({ saveUbListPosting: this.state.TotalUsers > 0 ? false : true })
                    notify.show("Total " + this.state.TotalUsers + " users matches to your filters", "success", 5000);
                })
            }
            this.setState({ getCountPosting: false })
        }).catch((error) => {
            notify.show(SYSTEM_ERROR, "error", 5000);
        })
    }

    addSportsPrefModal = () => {
        let { SaveSPPosting, SavedSportsPref } = this.state
        return (
            <div>
                <Modal className="sportpref-modal modal-xl" isOpen={this.state.SportsPrefModalOpen}
                    toggle={this.addSportsPrefToggle}>
                    <ModalHeader>{SPORT_PREF_HEADTITLE}</ModalHeader>
                    <ModalBody>
                        <Row>
                            {
                                _.map(SavedSportsPref, (item, idx) => {
                                    return (
                                        <Col md={4} key={idx} className="mb-5">
                                            <div className="common-cus-checkbox">
                                                <label class="com-chekbox-container mb-0">
                                                    <span className="noti-title">{item.sports_name}</span>
                                                    <input
                                                        type="checkbox"
                                                        name="SaveSportPref"
                                                        checked={item.status == '1' ? true : false}
                                                        onChange={(e) => this.handleSpoPrefCheckbox(e, idx, item.status)}
                                                    />
                                                    <span class="com-chekbox-checkmark"></span>
                                                </label>
                                            </div>
                                            <Input
                                                maxLength="10"
                                                type="text"
                                                name="min_value"
                                                value={item.min_value}
                                                placeholder="Min"
                                                disabled={item.status == '1' ? false : true}
                                                onChange={(e) => this.SpoPrefInputChange(e, idx)}
                                            />
                                            <Input
                                                maxLength="10"
                                                type="text"
                                                name="max_value"
                                                value={item.max_value}
                                                placeholder="Max"
                                                disabled={item.status == '1' ? false : true}
                                                onChange={(e) => this.SpoPrefInputChange(e, idx)}
                                            />
                                        </Col>
                                    )
                                })
                            }
                        </Row>
                    </ModalBody>
                    <ModalFooter>
                        <Button className="btn-default-gray" onClick={this.addSportsPrefToggle}>Cancel</Button>
                        <Button className="btn-secondary-outline"
                            disabled={SaveSPPosting}
                            onClick={this.checkSpoPrefAndSave}>Save</Button>{' '}
                    </ModalFooter>
                </Modal>
            </div>
        )
    }

    handleProfileCheckbox = (ub_ceckbox_flag, array_name, key_name) => {
        let { UserbaseData } = this.state
        let temp_ub_data = UserbaseData
        if (key_name === 'verified')
            temp_ub_data[array_name].verified = ub_ceckbox_flag == 1 ? 0 : 1
        if (key_name === 'not_verified')
            temp_ub_data[array_name].not_verified = ub_ceckbox_flag == 1 ? 0 : 1

        this.setState({ UserbaseData: temp_ub_data })
    }

    handleUBListCheckbox = (ub_ceckbox_flag, array_name) => {
        let { UserbaseData } = this.state
        let temp_ub_data = UserbaseData
        console.log("array_name==", array_name);

        if (ub_ceckbox_flag == 1) {
            this.setState({ TotalUsers: 0, UserIds: [] })
        }

        if (array_name === 'sport_preference' || array_name === 'location' || array_name === 'gender') {
            temp_ub_data[array_name].status = ub_ceckbox_flag == 1 ? 0 : 1
            temp_ub_data[array_name][array_name] = []
        }
        else if (array_name === 'profile_status') {
            temp_ub_data[array_name].status = ub_ceckbox_flag == 1 ? 0 : 1
            temp_ub_data[array_name].verified = ub_ceckbox_flag == 0 ? 0 : 0
            temp_ub_data[array_name].not_verified = ub_ceckbox_flag == 0 ? 0 : ""
        }
        else {
            temp_ub_data[array_name].status = ub_ceckbox_flag == 1 ? 0 : 1
            temp_ub_data[array_name].min_value = ub_ceckbox_flag == 0 ? "" : ""
            temp_ub_data[array_name].max_value = ub_ceckbox_flag == 1 ? "" : ""
        }
        this.setState({
            UserbaseData: temp_ub_data,
            getCountPosting: false
        })
    }

    handleUBListInputChange = (e, array_name, key_name) => {
        let { UserbaseData } = this.state
        let value = e.target.value;
        let temp_ubinput_data = UserbaseData

        temp_ubinput_data[array_name][key_name] = value

        this.setState({ UserbaseData: temp_ubinput_data })
    }

    getUserbaseList = (ub_id) => {
        let param = {
            user_base_list_id: ub_id,
        }
        WSManager.Rest(NC.baseURL + MODULE_C.GET_SINGLE_USER_BASE_LIST, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({
                    UserbaseData: responseJson.data,
                    TotalUsers: responseJson.data.count,
                    saveUbListPosting: false,
                });
            }
        }).catch((error) => {
            notify.show(SYSTEM_ERROR, "error", 5000);
        })
    }

    handleListNameChange = (e, indexV) => {
        let name = e.target.name;
        let value = e.target.value;
        let temp_userbase_data = this.state.UserbaseData
        if (value.length > 3) {
            this.setState({ saveUbListPosting: false })
        } else {
            this.setState({ saveUbListPosting: true })
        }

        temp_userbase_data.list_name = value
        this.setState({ UserbaseData: temp_userbase_data })
    }

    render() {
        let { DefineCheck, TotalUsers, getCountPosting, UserbaseData, Locations, SportsPref, saveUbListPosting } = this.state
        return (
            <Fragment>
                {this.addSportsPrefModal()}
                <div className="add-userbase-list">
                    <div className="userbase-header">Edit List</div>
                    <div className="userbase-bg rm-hdr-radius">
                        <Row>
                            <Col md={12}>
                                <label>Enter List name</label>
                                <Input
                                    maxLength="30"
                                    type="text"
                                    name="ListName"
                                    value={UserbaseData.list_name}
                                    placeholder="Enter"
                                    onChange={(e) => this.handleListNameChange(e)}
                                />
                            </Col>
                        </Row>
                    </div>
                    <div className="userbase-bg">
                        <div className="ub-heading">User Persona</div>
                        <Row>
                            <Col md={4} className="ub-sports-pref">
                                <div className="common-cus-checkbox clearfix">
                                    <label class="com-chekbox-container">
                                        <span className="noti-title">Sports Preference</span>
                                        <input
                                            type="checkbox"
                                            name="selectSetPrize"
                                            checked={
                                                UserbaseData.sport_preference != null ?
                                                    UserbaseData.sport_preference.status == 1
                                                        ? true : false
                                                    :
                                                    ''
                                            }
                                            onChange={(e) => this.handleUBListCheckbox(UserbaseData.sport_preference.status, 'sport_preference')}
                                        />
                                        <span class="com-chekbox-checkmark"></span>
                                    </label>

                                    {
                                        DefineCheck <= 0 && <div
                                            className="ub-define"
                                            onClick={() => this.addSportsPrefToggle()
                                            }
                                        >Define</div>
                                    }
                                </div>
                                <div
                                    className={
                                        UserbaseData.sport_preference != null ?
                                            UserbaseData.sport_preference.status == 1
                                                ? '' : 'select-disabled'
                                            :
                                            ''
                                    }
                                >
                                    <Multiselect
                                        options={SportsPref}
                                        selectedValues={UserbaseData.sport_preference != null ?
                                            UserbaseData.sport_preference.sport_preference : []}
                                        onSelect={(e) => this.onSelectDropdown(e, 'sport_preference')}
                                        onRemove={(e) => this.onRemoveDropdown(e, 'sport_preference')}
                                        displayValue="name"
                                    />
                                </div>
                            </Col>
                            <Col md={7}>
                                <div className="common-cus-checkbox">
                                    <label class="com-chekbox-container">
                                        <span className="noti-title">Location</span>
                                        <input
                                            type="checkbox"
                                            name="selectSetPrize"
                                            checked={
                                                UserbaseData.location != null ?
                                                    UserbaseData.location.status == 1
                                                        ? true : false
                                                    :
                                                    ''
                                            }
                                            onChange={(e) => this.handleUBListCheckbox(UserbaseData.location.status, 'location')}
                                        />
                                        <span class="com-chekbox-checkmark"></span>
                                    </label>
                                </div>
                                <div
                                    className={
                                        UserbaseData.location != null ?
                                            UserbaseData.location.status == 1
                                                ? '' : 'select-disabled'
                                            :
                                            ''
                                    }
                                >
                                    <Multiselect
                                        options={Locations}
                                        selectedValues={UserbaseData.location != null ?
                                            UserbaseData.location.location : []}
                                        onSelect={(e) => this.onSelectDropdown(e, 'location')}
                                        onRemove={(e) => this.onRemoveDropdown(e, 'location')}
                                        displayValue="name"
                                    />
                                </div>
                            </Col>
                        </Row>
                        <Row className="mt-5">
                            <Col md={4}>
                                <div className="common-cus-checkbox">
                                    <label class="com-chekbox-container">
                                        <span className="noti-title">Age Group</span>
                                        <input
                                            type="checkbox"
                                            name="selectSetPrize"
                                            checked={
                                                UserbaseData.age_group != null ?
                                                    UserbaseData.age_group.status == 1
                                                        ? true : false
                                                    :
                                                    ''
                                            }
                                            onChange={(e) => this.handleUBListCheckbox(UserbaseData.age_group.status, 'age_group')}
                                        />
                                        <span class="com-chekbox-checkmark"></span>
                                    </label>
                                </div>
                                <Input
                                    maxLength="10"
                                    type="text"
                                    name="AgeGroup_Min"
                                    disabled={UserbaseData.age_group != null ? UserbaseData.age_group.status == 1 ? false : true : false}
                                    value={UserbaseData.age_group != null ? UserbaseData.age_group.min_value : ''}
                                    onChange={(e) => this.handleUBListInputChange(e, 'age_group', 'min_value')}
                                    placeholder="Min"
                                />
                                <Input
                                    maxLength="10"
                                    type="text"
                                    name="AgeGroup_Max"
                                    disabled={UserbaseData.age_group != null ? UserbaseData.age_group.status == 1 ? false : true : false}
                                    value={UserbaseData.age_group != null ? UserbaseData.age_group.max_value : ''}
                                    onChange={(e) => this.handleUBListInputChange(e, 'age_group', 'max_value')}
                                    placeholder="Max"
                                />
                            </Col>
                            <Col md={4}>
                                <div className="common-cus-checkbox">
                                    <label class="com-chekbox-container">
                                        <span className="noti-title">Profile status</span>
                                        <input
                                            type="checkbox"
                                            name="selectSetPrize"
                                            checked={
                                                UserbaseData.profile_status != null ?
                                                    UserbaseData.profile_status.status == 1
                                                        ? true : false
                                                    :
                                                    ''
                                            }
                                            onChange={(e) => this.handleUBListCheckbox(UserbaseData.profile_status.status, 'profile_status')}
                                        />
                                        <span class="com-chekbox-checkmark"></span>
                                    </label>
                                </div>
                                <div className="common-cus-checkbox">
                                    <label class="com-chekbox-container float-left mr-5">
                                        <span className="noti-title">Verified</span>
                                        <input
                                            type="checkbox"
                                            name="selectSetPrize"
                                            disabled={UserbaseData.profile_status != null ? UserbaseData.profile_status.status == 1 ? false : true : false}
                                            checked={
                                                UserbaseData.profile_status != null ?
                                                    UserbaseData.profile_status.verified == 1
                                                        ? true : false
                                                    :
                                                    ''
                                            }
                                            onChange={(e) => this.handleProfileCheckbox(UserbaseData.profile_status.verified, 'profile_status', 'verified')}
                                        />
                                        <span class="com-chekbox-checkmark"></span>
                                    </label>
                                </div>
                                <div className="common-cus-checkbox">
                                    <label class="com-chekbox-container float-left">
                                        <span className="noti-title">Non verified</span>
                                        <input
                                            type="checkbox"
                                            name="selectSetPrize"
                                            disabled={UserbaseData.profile_status != null ? UserbaseData.profile_status.status == 1 ? false : true : false}
                                            checked={
                                                UserbaseData.profile_status != null ?
                                                    UserbaseData.profile_status.not_verified == 1
                                                        ? true : false
                                                    :
                                                    ''
                                            }
                                            onChange={(e) => this.handleProfileCheckbox(UserbaseData.profile_status.not_verified, 'profile_status', 'not_verified')}
                                        />
                                        <span class="com-chekbox-checkmark"></span>
                                    </label>
                                </div>
                            </Col>
                            <Col md={3}>
                                <div className="common-cus-checkbox">
                                    <label class="com-chekbox-container">
                                        <span className="noti-title">Gender</span>
                                        <input
                                            type="checkbox"
                                            name="selectSetPrize"
                                            checked={
                                                UserbaseData.gender != null ?
                                                    UserbaseData.gender.status == 1
                                                        ? true : false
                                                    :
                                                    ''
                                            }
                                            onChange={(e) => this.handleUBListCheckbox(UserbaseData.gender.status, 'gender')}
                                        />
                                        <span class="com-chekbox-checkmark"></span>
                                    </label>
                                </div>
                                <div
                                    className={
                                        UserbaseData.gender != null ?
                                            UserbaseData.gender.status == 1
                                                ? '' : 'select-disabled'
                                            :
                                            ''
                                    }
                                >
                                    <Multiselect
                                        options={MODULE_C.GenderOptions}
                                        selectedValues={UserbaseData.gender != null ?
                                            UserbaseData.gender.gender : []}
                                        onSelect={(e) => this.onSelectDropdown(e, 'gender')}
                                        onRemove={(e) => this.onRemoveDropdown(e, 'gender')}
                                        displayValue="name"
                                    />
                                </div>
                            </Col>
                        </Row>
                    </div>
                    <div className="userbase-bg">
                        <div className="ub-heading">Contest (Admin created)</div>
                        <Row>
                            <Col md={4}>
                                <div className="common-cus-checkbox">
                                    <label class="com-chekbox-container">
                                        <span className="noti-title">Contest Joined</span>
                                        <input
                                            type="checkbox"
                                            name="selectSetPrize"
                                            checked={
                                                UserbaseData.admin_created_contest_join != null ?
                                                    UserbaseData.admin_created_contest_join.status == 1
                                                        ? true : false
                                                    :
                                                    ''
                                            }
                                            onChange={(e) => this.handleUBListCheckbox(UserbaseData.admin_created_contest_join.status, 'admin_created_contest_join')}
                                        />
                                        <span class="com-chekbox-checkmark"></span>
                                    </label>
                                </div>
                                <Input
                                    maxLength="10"
                                    type="text"
                                    name="ContestJoined_Min"
                                    disabled={UserbaseData.admin_created_contest_join != null ? UserbaseData.admin_created_contest_join.status == 1 ? false : true : false}
                                    value={UserbaseData.admin_created_contest_join != null ? UserbaseData.admin_created_contest_join.min_value : ''}
                                    placeholder="Min"
                                    onChange={(e) => this.handleUBListInputChange(e, 'admin_created_contest_join', 'min_value')}
                                />
                                <Input
                                    maxLength="10"
                                    type="text"
                                    name="ContestJoined_Max"
                                    disabled={UserbaseData.admin_created_contest_join != null ? UserbaseData.admin_created_contest_join.status == 1 ? false : true : false}
                                    value={UserbaseData.admin_created_contest_join != null ? UserbaseData.admin_created_contest_join.max_value : ''}
                                    placeholder="Max"
                                    onChange={(e) => this.handleUBListInputChange(e, 'admin_created_contest_join', 'max_value')}
                                />
                            </Col>
                            <Col md={4}>
                                <div className="common-cus-checkbox">
                                    <label class="com-chekbox-container">
                                        <span className="noti-title">Contest Won</span>
                                        <input
                                            type="checkbox"
                                            name="selectSetPrize"
                                            checked={
                                                UserbaseData.admin_created_contest_won != null ?
                                                    UserbaseData.admin_created_contest_won.status == 1
                                                        ? true : false
                                                    :
                                                    ''
                                            }
                                            onChange={(e) => this.handleUBListCheckbox(UserbaseData.admin_created_contest_won.status, 'admin_created_contest_won')}
                                        />
                                        <span class="com-chekbox-checkmark"></span>
                                    </label>
                                </div>
                                <Input
                                    maxLength="10"
                                    type="text"
                                    name="ContestWon_Min"
                                    disabled={UserbaseData.admin_created_contest_won != null ? UserbaseData.admin_created_contest_won.status == 1 ? false : true : false}
                                    value={UserbaseData.admin_created_contest_won != null ? UserbaseData.admin_created_contest_won.min_value : ''}
                                    placeholder="Min"
                                    onChange={(e) => this.handleUBListInputChange(e, 'admin_created_contest_won', 'min_value')}
                                />
                                <Input
                                    maxLength="10"
                                    type="text"
                                    name="ContestWon_Max"
                                    disabled={UserbaseData.admin_created_contest_won != null ? UserbaseData.admin_created_contest_won.status == 1 ? false : true : false}
                                    value={UserbaseData.admin_created_contest_won != null ? UserbaseData.admin_created_contest_won.max_value : ''}
                                    placeholder="Max"
                                    onChange={(e) => this.handleUBListInputChange(e, 'admin_created_contest_won', 'max_value')}
                                />
                            </Col>
                            <Col md={4}>
                                <div className="common-cus-checkbox">
                                    <label class="com-chekbox-container">
                                        <span className="noti-title">Contest Lost</span>
                                        <input
                                            type="checkbox"
                                            name="selectSetPrize"
                                            checked={
                                                UserbaseData.admin_created_contest_lost != null ?
                                                    UserbaseData.admin_created_contest_lost.status == 1
                                                        ? true : false
                                                    :
                                                    ''
                                            }
                                            onChange={(e) => this.handleUBListCheckbox(UserbaseData.admin_created_contest_lost.status, 'admin_created_contest_lost')}
                                        />
                                        <span class="com-chekbox-checkmark"></span>
                                    </label>
                                </div>
                                <Input
                                    maxLength="10"
                                    type="text"
                                    name="ContestLost_Min"
                                    disabled={UserbaseData.admin_created_contest_lost != null ? UserbaseData.admin_created_contest_lost.status == 1 ? false : true : false}
                                    value={UserbaseData.admin_created_contest_lost != null ? UserbaseData.admin_created_contest_lost.min_value : ''}
                                    placeholder="Min"
                                    onChange={(e) => this.handleUBListInputChange(e, 'admin_created_contest_lost', 'min_value')}
                                />
                                <Input
                                    maxLength="10"
                                    type="text"
                                    name="ContestLost_Max"
                                    disabled={UserbaseData.admin_created_contest_lost != null ? UserbaseData.admin_created_contest_lost.status == 1 ? false : true : false}
                                    value={UserbaseData.admin_created_contest_lost != null ? UserbaseData.admin_created_contest_lost.max_value : ''}
                                    placeholder="Max"
                                    onChange={(e) => this.handleUBListInputChange(e, 'admin_created_contest_lost', 'max_value')}
                                />
                            </Col>
                        </Row>
                        <div className="ub-heading mt-4">Contest (Private)</div>
                        <Row>
                            <Col md={4}>
                                <div className="common-cus-checkbox">
                                    <label class="com-chekbox-container">
                                        <span className="noti-title">Contest Joined</span>
                                        <input
                                            type="checkbox"
                                            name="selectSetPrize"
                                            checked={
                                                UserbaseData.private_contest_join != null ?
                                                    UserbaseData.private_contest_join.status == 1
                                                        ? true : false
                                                    :
                                                    ''
                                            }
                                            onChange={(e) => this.handleUBListCheckbox(UserbaseData.private_contest_join.status, 'private_contest_join')}
                                        />
                                        <span class="com-chekbox-checkmark"></span>
                                    </label>
                                </div>
                                <Input
                                    maxLength="10"
                                    type="text"
                                    name="ContestJoined_Min"
                                    disabled={UserbaseData.private_contest_join != null ? UserbaseData.private_contest_join.status == 1 ? false : true : false}
                                    value={UserbaseData.private_contest_join != null ? UserbaseData.private_contest_join.min_value : ''}
                                    placeholder="Min"
                                    onChange={(e) => this.handleUBListInputChange(e, 'private_contest_join', 'min_value')}
                                />
                                <Input
                                    maxLength="10"
                                    type="text"
                                    name="ContestJoined_Max"
                                    disabled={UserbaseData.private_contest_join != null ? UserbaseData.private_contest_join.status == 1 ? false : true : false}
                                    value={UserbaseData.private_contest_join != null ? UserbaseData.private_contest_join.max_value : ''}
                                    placeholder="Max"
                                    onChange={(e) => this.handleUBListInputChange(e, 'private_contest_join', 'max_value')}
                                />
                            </Col>
                            <Col md={4}>
                                <div className="common-cus-checkbox">
                                    <label class="com-chekbox-container">
                                        <span className="noti-title">Contest Won</span>
                                        <input
                                            type="checkbox"
                                            name="selectSetPrize"
                                            checked={
                                                UserbaseData.private_contest_won != null ?
                                                    UserbaseData.private_contest_won.status == 1
                                                        ? true : false
                                                    :
                                                    ''
                                            }
                                            onChange={(e) => this.handleUBListCheckbox(UserbaseData.private_contest_won.status, 'private_contest_won')}
                                        />
                                        <span class="com-chekbox-checkmark"></span>
                                    </label>
                                </div>
                                <Input
                                    maxLength="10"
                                    type="text"
                                    name="ContestWon_Min"
                                    disabled={UserbaseData.private_contest_won != null ? UserbaseData.private_contest_won.status == 1 ? false : true : false}
                                    value={UserbaseData.private_contest_won != null ? UserbaseData.private_contest_won.min_value : ''}
                                    placeholder="Min"
                                    onChange={(e) => this.handleUBListInputChange(e, 'private_contest_won', 'min_value')}
                                />
                                <Input
                                    maxLength="10"
                                    type="text"
                                    name="ContestWon_Max"
                                    disabled={UserbaseData.private_contest_won != null ? UserbaseData.private_contest_won.status == 1 ? false : true : false}
                                    value={UserbaseData.private_contest_won != null ? UserbaseData.private_contest_won.max_value : ''}
                                    placeholder="Max"
                                    onChange={(e) => this.handleUBListInputChange(e, 'private_contest_won', 'max_value')}
                                />
                            </Col>
                            <Col md={4}>
                                <div className="common-cus-checkbox">
                                    <label class="com-chekbox-container">
                                        <span className="noti-title">Contest Lost</span>
                                        <input
                                            type="checkbox"
                                            name="selectSetPrize"
                                            checked={
                                                UserbaseData.private_contest_lost != null ?
                                                    UserbaseData.private_contest_lost.status == 1
                                                        ? true : false
                                                    :
                                                    ''
                                            }
                                            onChange={(e) => this.handleUBListCheckbox(UserbaseData.private_contest_lost.status, 'private_contest_lost')}
                                        />
                                        <span class="com-chekbox-checkmark"></span>
                                    </label>
                                </div>
                                <Input
                                    maxLength="10"
                                    type="text"
                                    name="ContestLost_Min"
                                    disabled={UserbaseData.private_contest_lost != null ? UserbaseData.private_contest_lost.status == 1 ? false : true : false}
                                    value={UserbaseData.private_contest_lost != null ? UserbaseData.private_contest_lost.min_value : ''}
                                    placeholder="Min"
                                    onChange={(e) => this.handleUBListInputChange(e, 'private_contest_lost', 'min_value')}
                                />
                                <Input
                                    maxLength="10"
                                    type="text"
                                    name="ContestLost_Max"
                                    disabled={UserbaseData.private_contest_lost != null ? UserbaseData.private_contest_lost.status == 1 ? false : true : false}
                                    value={UserbaseData.private_contest_lost != null ? UserbaseData.private_contest_lost.max_value : ''}
                                    placeholder="Max"
                                    onChange={(e) => this.handleUBListInputChange(e, 'private_contest_lost', 'max_value')}
                                />
                            </Col>
                        </Row>
                    </div>
                    <div className="userbase-bg">
                        <div className="ub-heading">Money</div>
                        <Row>
                            <Col md={4}>
                                <div className="common-cus-checkbox">
                                    <label class="com-chekbox-container">
                                        <span className="noti-title">Deposited</span>
                                        <input
                                            type="checkbox"
                                            name="selectSetPrize"
                                            checked={
                                                UserbaseData.money_deposit != null ?
                                                    UserbaseData.money_deposit.status == 1
                                                        ? true : false
                                                    :
                                                    ''
                                            }
                                            onChange={(e) => this.handleUBListCheckbox(UserbaseData.money_deposit.status, 'money_deposit')}
                                        />
                                        <span class="com-chekbox-checkmark"></span>
                                    </label>
                                </div>
                                <Input
                                    maxLength="10"
                                    type="text"
                                    name="MoneyDeposited_Min"
                                    disabled={UserbaseData.money_deposit != null ? UserbaseData.money_deposit.status == 1 ? false : true : false}
                                    value={UserbaseData.money_deposit != null ? UserbaseData.money_deposit.min_value : ''}
                                    placeholder="Min"
                                    onChange={(e) => this.handleUBListInputChange(e, 'money_deposit', 'min_value')}
                                />
                                <Input
                                    maxLength="10"
                                    type="text"
                                    name="MoneyDeposited_Max"
                                    disabled={UserbaseData.money_deposit != null ? UserbaseData.money_deposit.status == 1 ? false : true : false}
                                    value={UserbaseData.money_deposit != null ? UserbaseData.money_deposit.max_value : ''}
                                    placeholder="Max"
                                    onChange={(e) => this.handleUBListInputChange(e, 'money_deposit', 'max_value')}
                                />
                            </Col>
                            <Col md={4}>
                                <div className="common-cus-checkbox">
                                    <label class="com-chekbox-container">
                                        <span className="noti-title">Won</span>
                                        <input
                                            type="checkbox"
                                            name="selectSetPrize"
                                            checked={
                                                UserbaseData.money_won != null ?
                                                    UserbaseData.money_won.status == 1
                                                        ? true : false
                                                    :
                                                    ''
                                            }
                                            onChange={(e) => this.handleUBListCheckbox(UserbaseData.money_won.status, 'money_won')}
                                        />
                                        <span class="com-chekbox-checkmark"></span>
                                    </label>
                                </div>
                                <Input
                                    maxLength="10"
                                    type="text"
                                    name="ContestWon_Min"
                                    disabled={UserbaseData.age_group != null ? UserbaseData.money_won.status == 1 ? false : true : false}
                                    value={UserbaseData.money_won != null ? UserbaseData.money_won.min_value : ''}
                                    placeholder="Min"
                                    onChange={(e) => this.handleUBListInputChange(e, 'money_won', 'min_value')}
                                />
                                <Input
                                    maxLength="10"
                                    type="text"
                                    name="ContestWon_Max"
                                    disabled={UserbaseData.money_won != null ? UserbaseData.money_won.status == 1 ? false : true : false}
                                    value={UserbaseData.money_won != null ? UserbaseData.money_won.max_value : ''}
                                    placeholder="Max"
                                    onChange={(e) => this.handleUBListInputChange(e, 'money_won', 'max_value')}
                                />
                            </Col>                            
                        </Row>
                    </div>
                    <div className="userbase-bg">
                        <div className="ub-heading">Coin</div>
                        <Row>
                            <Col md={4}>
                                <div className="common-cus-checkbox">
                                    <label class="com-chekbox-container">
                                        <span className="noti-title">Earned</span>
                                        <input
                                            type="checkbox"
                                            name="selectSetPrize"
                                            checked={
                                                UserbaseData.coin_earn != null ?
                                                    UserbaseData.coin_earn.status == 1
                                                        ? true : false
                                                    :
                                                    ''
                                            }
                                            onChange={(e) => this.handleUBListCheckbox(UserbaseData.coin_earn.status, 'coin_earn')}
                                        />
                                        <span class="com-chekbox-checkmark"></span>
                                    </label>
                                </div>
                                <Input
                                    maxLength="10"
                                    type="text"
                                    name="CoinEarned_Min"
                                    disabled={UserbaseData.coin_earn != null ? UserbaseData.coin_earn.status == 1 ? false : true : false}
                                    value={UserbaseData.coin_earn != null ? UserbaseData.coin_earn.min_value : ''}
                                    placeholder="Min"
                                    onChange={(e) => this.handleUBListInputChange(e, 'coin_earn', 'min_value')}
                                />
                                <Input
                                    maxLength="10"
                                    type="text"
                                    name="CoinEarned_Max"
                                    disabled={UserbaseData.coin_earn != null ? UserbaseData.coin_earn.status == 1 ? false : true : false}
                                    value={UserbaseData.coin_earn != null ? UserbaseData.coin_earn.max_value : ''}
                                    placeholder="Max"
                                    onChange={(e) => this.handleUBListInputChange(e, 'coin_earn', 'max_value')}
                                />
                            </Col>
                            <Col md={4}>
                                <div className="common-cus-checkbox">
                                    <label class="com-chekbox-container">
                                        <span className="noti-title">Redeem</span>
                                        <input
                                            type="checkbox"
                                            name="selectSetPrize"
                                            checked={
                                                UserbaseData.coin_redeem != null ?
                                                    UserbaseData.coin_redeem.status == 1
                                                        ? true : false
                                                    :
                                                    ''
                                            }
                                            onChange={(e) => this.handleUBListCheckbox(UserbaseData.coin_redeem.status, 'coin_redeem')}
                                        />
                                        <span class="com-chekbox-checkmark"></span>
                                    </label>
                                </div>
                                <Input
                                    maxLength="10"
                                    type="text"
                                    name="CoinRedeem_Min"
                                    disabled={UserbaseData.coin_redeem != null ? UserbaseData.coin_redeem.status == 1 ? false : true : false}
                                    value={UserbaseData.coin_redeem != null ? UserbaseData.coin_redeem.min_value : ''}
                                    placeholder="Min"
                                    placeholder="Min"
                                    onChange={(e) => this.handleUBListInputChange(e, 'coin_redeem', 'min_value')}
                                />
                                <Input
                                    maxLength="10"
                                    type="text"
                                    name="CoinRedeem_Max"
                                    disabled={UserbaseData.coin_redeem != null ? UserbaseData.coin_redeem.status == 1 ? false : true : false}
                                    value={UserbaseData.coin_redeem != null ? UserbaseData.coin_redeem.max_value : ''}
                                    placeholder="Min"
                                    placeholder="Max"
                                    onChange={(e) => this.handleUBListInputChange(e, 'coin_redeem', 'max_value')}
                                />
                            </Col>
                        </Row>
                    </div>
                    <div className="userbase-bg">
                        <div className="ub-heading">Referral</div>
                        <Row>
                            <Col md={4}>
                                <div className="common-cus-checkbox">
                                    <label class="com-chekbox-container">
                                        <span className="noti-title">Users Referred</span>
                                        <input
                                            type="checkbox"
                                            name="selectSetPrize"
                                            checked={
                                                UserbaseData.referral != null ?
                                                    UserbaseData.referral.status == 1
                                                        ? true : false
                                                    :
                                                    ''
                                            }
                                            onChange={(e) => this.handleUBListCheckbox(UserbaseData.referral.status, 'referral')}
                                        />
                                        <span class="com-chekbox-checkmark"></span>
                                    </label>
                                </div>
                                <Input
                                    maxLength="10"
                                    type="text"
                                    name="Referral_Min"
                                    disabled={UserbaseData.referral != null ? UserbaseData.referral.status == 1 ? false : true : false}
                                    value={UserbaseData.referral != null ? UserbaseData.referral.min_value : ''}
                                    placeholder="Min"
                                    onChange={(e) => this.handleUBListInputChange(e, 'referral', 'min_value')}
                                />
                                <Input
                                    maxLength="10"
                                    type="text"
                                    name="Referral_Max"
                                    disabled={UserbaseData.referral != null ? UserbaseData.referral.status == 1 ? false : true : false}
                                    value={UserbaseData.referral != null ? UserbaseData.referral.max_value : ''}
                                    placeholder="Max"
                                    onChange={(e) => this.handleUBListInputChange(e, 'referral', 'max_value')}
                                />
                            </Col>
                        </Row>
                    </div>
                    <div className="userbase-bg total-box">
                        <Row>
                            <Col md={6}>
                                <Button
                                    className="btn-secondary-outline float-right"
                                    onClick={() => this.checkUbListAndSave(1)}
                                    disabled={getCountPosting}
                                >
                                    Get Total User
                                </Button>
                            </Col>
                            <Col md={6}>
                                <span className="ub-t-users">Total Users</span>
                                <span className="ub-t-users-count">{TotalUsers ? TotalUsers : '0'}</span>
                            </Col>
                        </Row>
                    </div>
                    <Row>
                        <Col md={12}>
                            <div className="bottom-action-box">
                                <Button
                                    className="btn-secondary-outline mr-3"
                                    onClick={() => this.checkUbListAndSave(2)}
                                    disabled={saveUbListPosting}
                                >
                                    Update
                                </Button>
                                <Button
                                    className="btn-secondary-outline gray-btn"
                                    onClick={() => this.props.history.push('/marketing/new_campaign')}
                                >
                                    Cancel
                                </Button>
                            </div>
                        </Col>
                    </Row>
                </div>
            </Fragment>
        )
    }
}
export default CreatUserbaseList


