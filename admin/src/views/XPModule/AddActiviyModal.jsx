import React, { Component, Fragment } from "react";
import { Row, Col, Button, Modal, ModalBody, ModalFooter, Input } from 'reactstrap';
import HF, { _isEmpty, _Map, _isNull, _times, _isUndefined } from "../../helper/HelperFunction";
import SelectDropdown from "../../components/SelectDropdown";
import { xpGetActivityMastList, xpAddActivity, xpUpdateActivity } from '../../helper/WSCalling';
import { notify } from 'react-notify-toast';
import * as NC from '../../helper/NetworkingConstants';
import { XP_SEL_ACTIVITY, XP_SEL_COUNT, XP_EARN_POINTS, XP_LEV_ENDP } from "../../helper/Message";
export default class AddActivitiesLevelModal extends Component {
    constructor(props) {
        super(props)
        this.state = {
            // ActOptions: [],
            CountOption: [],
            ActivitySelect: '',
            CountSelect: '',
            EarnPoints: '',
            ActType: '',
            addActPosting: false,
            EditForm: true,
        }
    }

    componentDidMount() {
        // this.getActivity()
        this.getCount()
    }

    // getActivity = () => {
    //     let params = {}
    //     xpGetActivityMastList(params).then(ApiResponse => {
    //         if (ApiResponse.response_code == NC.successCode) {
    //             let res = ApiResponse.data ? ApiResponse.data : []
    //             let l_arr = []
    //             _Map(res, function (data) {
    //                 l_arr.push({
    //                     value: data.activity_master_id,
    //                     label: data.activity_title,
    //                     activity_type: data.activity_type,
    //                 });
    //             })
    //             this.setState({ ActOptions: l_arr })
    //         } else {
    //             notify.show(NC.SYSTEM_ERROR, "error", 3000)
    //         }
    //     }).catch(error => {
    //         notify.show(NC.SYSTEM_ERROR, "error", 3000)
    //     })
    // }

    getCount = () => {
        let tArr = []
        _times(101, (n) => {
            if (n !== 0)
                tArr.push({ value: n, label: n })
        })
        this.setState({ CountOption: tArr })
    }

    handleSelectChange = (value, name) => {
        if (!_isNull(value)) {
            this.setState({ [name]: value.value, EditForm: false }, () => {                
                if (name == 'ActivitySelect') {
                    this.setState({ ActType: value.activity_type })
                }
            })
        }
    }

    handleInputChange = (e) => {
        if (e) {
            let inp_name = e.target.getAttribute("data-inp");
            let value = e.target.value;
            if (HF.isFloat(value)) {
                value = this.state.EarnPoints
                notify.show(XP_LEV_ENDP, 'error', 1500)
            }
            this.setState({ EarnPoints: value, EditForm: false }, () => {
                if (Number(this.state.EarnPoints) < 1 || Number(this.state.EarnPoints) > 100000) {
                    let msg = inp_name + ' value should be between 1 to 100000'
                    notify.show(msg, 'error', 3000)
                    this.setState({ EarnPoints: '' })
                    return false
                }
            });
        }
    }

    addActivity = () => {
        let { ActivitySelect, CountSelect, EarnPoints, ActType } = this.state
        if (_isEmpty(ActivitySelect) && _isEmpty(this.props.EditRItem)) {
            notify.show(XP_SEL_ACTIVITY, 'error', 1500)
            return false
        }

        if (ActType == '2' && _isEmpty(CountSelect.toString())) {
            notify.show(XP_SEL_COUNT, 'error', 1500)
            return false
        }

        if (_isEmpty(EarnPoints)) {
            notify.show(XP_EARN_POINTS, 'error', 1500)
            return false
        }

        let params = {
            "activity_master_id": ActivitySelect,
            "xp_point": EarnPoints,
            "recurrent_count": CountSelect,
        }
        this.setState({ addActPosting: true })
        let api_call = xpAddActivity
        if (!_isUndefined(this.props.EditRItem.activity_id)) {
            params.activity_id = this.props.EditRItem.activity_id
            delete params.activity_master_id;
            api_call = xpUpdateActivity
        }

        api_call(params).then(ApiResponse => {
            if (ApiResponse.response_code == NC.successCode) {
                this.setState({
                    ActivitySelect: '',
                    CountSelect: '',
                    EarnPoints: '',
                    EditForm: true,
                })
                notify.show(ApiResponse.message, 'success', 3000)
                this.props.modalActioYesCallback(ActivitySelect)
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
            this.setState({ addActPosting: false })
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }
    
    changeEditState = () => {
        if(this.state.EarnPoints != this.props.EditRItem.xp_point)
        {
            this.setState({
                EarnPoints: this.props.EditRItem.xp_point,
                CountSelect: this.props.EditRItem.recurrent_count,
                ActType: this.props.EditRItem.activity_type,
            })
        }
    }

    render() {
        let { modalCallback, AddActiModalOpen, EditRItem, EditFlag, ActOptions } = this.props
        const { ActType, ActivitySelect, CountSelect, CountOption, EarnPoints, addActPosting, EditForm } = this.state


        if (EditForm && !_isEmpty(EditRItem)) {
            this.changeEditState()
        }

        const comm_select_props = {
            is_disabled: false,
            is_searchable: false,
            is_clearable: false,
            menu_is_open: false,
            class_name: "custom-form-control",
            place_holder: "Select",
            modalCallback: (e, name) => this.handleSelectChange(e, name)
        }

        const activity_select = {
            ...comm_select_props,
            sel_options: ActOptions,
            selected_value: ActivitySelect,
            select_name: 'ActivitySelect',
        }

        const act_count_select = {
            ...comm_select_props,
            sel_options: CountOption,
            // selected_value: CountSelect || (EditForm && !_isEmpty(EditRItem)) && EditRItem.recurrent_count,
            selected_value: CountSelect,
            select_name: 'CountSelect',
            is_searchable: true,
        }

        return (
            <Modal
                isOpen={AddActiModalOpen}
                toggle={modalCallback}
                className="addrewards-modal modal-md addact-modal animate-modal-top"
            >
                <ModalBody>
                    <Row>
                        <Col md={12}>
                            <h3 className="h3-cls">{EditRItem['activity_id'] ? 'Update' : 'Add'} Activities</h3>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={6}>
                            <div className="input-box">
                                <label>Select Activity</label>
                                {
                                    EditRItem['activity_id'] ?
                                        <div className="xp-sel-title">
                                            {EditRItem ? EditRItem.activity_title : ''}
                                        </div>
                                        :
                                        <SelectDropdown SelectProps={activity_select} />
                                }
                                <span className="act-type">
                                    {ActType == '1' && 'One time'}
                                    {ActType == '2' && 'Recurrent'}
                                </span>
                            </div>
                        </Col>
                        <Col md={6}>
                            <div className={`input-box ${(ActType == '2') ? 'opc-1' : 'opc-0'}`}>
                                <label>Count</label>
                                <SelectDropdown SelectProps={act_count_select} />
                            </div>
                        </Col>
                    </Row>

                    <Row>
                        <Col md={6}>
                            <div className="input-box">
                                <label>Earn Points</label>
                                <Input
                                    className="form-control"
                                    type="number"
                                    placeholder="Points"
                                    name='EarnPoints'
                                    // value={(EditForm && !_isEmpty(EditRItem)) ? EditRItem.xp_point : EarnPoints}
                                    value={EarnPoints}
                                    data-inp='Earn points'
                                    onChange={(e) => this.handleInputChange(e)}
                                />
                            </div>
                        </Col>
                    </Row>
                </ModalBody>
                <ModalFooter>
                    <Button
                        disabled={addActPosting}
                        className="btn-secondary-outline"
                        onClick={this.addActivity}
                    >{EditRItem['activity_id'] ? 'Update' : 'Add'} Activity</Button>
                </ModalFooter>
            </Modal>
        )
    }
}