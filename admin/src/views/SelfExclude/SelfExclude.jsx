import React, { Component, Fragment } from "react";
import { Input, Button, Modal, ModalHeader, ModalBody, ModalFooter, Row, Col, Table } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Pagination from "react-js-pagination";
import _ from 'lodash';
import Moment from 'react-moment';
import ActionRequestModal from '../../components/ActionRequestModal/ActionRequestModal';
import HF from '../../helper/HelperFunction';
class SelfExclude extends Component {
    constructor(props) {
        super(props)
        this.state = {
            PERPAGE: NC.ITEMS_PERPAGE,
            // PERPAGE: 10,
            CURRENT_PAGE: 1,
            DefaultLimit: '',
            MaximumLimit: '',
            UserList: [],
            SelfExclusion: [],
            formValid: true,
            sortField: 'user_name',
            isDescOrder: 'true',
            ActionPopupOpen: false,
            SubActionPopupOpen: false,
            setDefPost : false
        };
    }
    componentDidMount() {
        if (HF.allowSelfExclusion() != '1') {
            notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        this.getUserList();
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
        const { PERPAGE, CURRENT_PAGE, isDescOrder, sortField } = this.state
        let params = {
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
            sort_order: isDescOrder ? 'DESC' : 'ASC',
            sort_field: sortField,
        }

        WSManager.Rest(NC.baseURL + NC.SELF_EXCLUSION, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {

                if (CURRENT_PAGE == 1) {
                    this.setState({
                        SelfExclusion: ResponseJson.data ? ResponseJson.data.self_exclusion : []
                    }, () => {
                        if (!_.isEmpty(this.state.SelfExclusion)) {
                            let tempSE = this.state.SelfExclusion
                            if (tempSE[0].custom_data) {
                                let cData = JSON.parse(tempSE[0].custom_data)
                                if (!_.isUndefined(cData.max_limit)) {
                                    this.setState({ MaximumLimit: cData.max_limit })
                                }
                                if (!_.isUndefined(cData.default_limit)) {
                                    this.setState({ DefaultLimit: cData.default_limit })
                                }
                            }
                        }
                    })
                }
                this.setState({
                    UserList: ResponseJson.data ? ResponseJson.data.result : [],
                    Total: ResponseJson.data.total
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

    //function to toggle action popup
    toggleActionPopup = (user_id, idx) => {
        this.setState({
            Message: NC.MSG_SET_TO_DEF,
            idxVal: idx,
            UserID: user_id,
            ActionPopupOpen: !this.state.ActionPopupOpen
        })
    }

    setDefault = () => {
        this.setState({ setDefPost: true })
        let { UserID, idxVal } = this.state
        let params = {
            user_id: UserID
        }
        WSManager.Rest(NC.baseURL + NC.SET_DEFAULT_SELF_EXCLUSION, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({ setDefPost: false })
                this.toggleActionPopup(UserID, idxVal)
                this.getUserList();
                notify.show(Response.message, 'success', 5000)
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    handleInputChange = (e) => {
        let name = e.target.name;
        let value = e.target.value;
        this.setState({ [name]: value, formValid: false }, () => {

            if (_.isEmpty(this.state.DefaultLimit)) {
                let msg = 'Default limit can not be empty.'
                notify.show(msg, 'error', 3000)
                this.setState({ formValid: true })
                return false
            }

            if (_.isEmpty(this.state.MaximumLimit)) {
                let msg = 'Maximum limit can not be empty.'
                notify.show(msg, 'error', 3000)
                this.setState({ formValid: true })
                return false
            }

            if (parseInt(this.state.MaximumLimit) < parseInt(this.state.DefaultLimit)) {
                let msg = 'Maximum limit should be greater than equal to default limit.'
                notify.show(msg, 'error', 3000)
                this.setState({ formValid: true })
                return false
            }

        });
    }

    SaveLimit = () => {
        this.setState({ formValid: true })
        let { DefaultLimit, MaximumLimit } = this.state

        let params = {
            "default_limit": DefaultLimit,
            "max_limit": MaximumLimit,
        }

        WSManager.Rest(NC.baseURL + NC.UPDATE_SELF_EXCLUSION_LIMIT, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, 'success', 5000)
                this.toggleSubActionPopup()
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    //function to toggle action popup
    toggleSubActionPopup = () => {
        this.setState({
            SubMessage: NC.MSG_SUBMIT_LIMIT,
            SubActionPopupOpen: !this.state.SubActionPopupOpen
        })
    }

    render() {
        let { UserList, DefaultLimit, MaximumLimit, CURRENT_PAGE, PERPAGE, Total, ActionPopupOpen, Message, formValid, isDescOrder, sortField, SubMessage, SubActionPopupOpen, setDefPost } = this.state
        const ActionCallback = {
            Message: Message,
            modalCallback: this.toggleActionPopup,
            ActionPopupOpen: ActionPopupOpen,
            modalActioCallback: this.setDefault,
            posting: setDefPost
        }

        const SubmitProps = {
            Message: SubMessage,
            modalCallback: this.toggleSubActionPopup,
            ActionPopupOpen: SubActionPopupOpen,
            modalActioCallback: this.SaveLimit,
            posting: formValid
        }
        return (
            <div className="self-exclusion animated fadeIn">
                <ActionRequestModal {...ActionCallback} />
                <ActionRequestModal {...SubmitProps} />
                <Row>
                    <Col md={12}>
                        <h1 className="h1-class">Self Exclusion</h1>
                        <div className="se-sub-title">Selecting the loosing limit will be applicable to all the fantasy player. The new limit set will take immidiate effect. </div>
                    </Col>
                </Row>
                <div className="se-limit-box">
                    <Row>
                        <Col md={6}>
                            <div className="se-input-div">
                                <label className="se-label">Default Limit</label>
                                <div className="se-input-box">
                                    <Input
                                        type="number"
                                        placeholder="500"
                                        name='DefaultLimit'
                                        value={DefaultLimit}
                                        onChange={(e) => this.handleInputChange(e)}
                                    />
                                    <span>(This is the limit which is already set for the user, The Default limit is {DefaultLimit})</span>
                                </div>
                            </div>
                        </Col>
                        <Col md={6}>
                            <label className="se-label">Maximum Limit</label>
                            <div className="se-input-box">
                                <Input
                                    type="number"
                                    placeholder="1000"
                                    name='MaximumLimit'
                                    value={MaximumLimit}
                                    onChange={(e) => this.handleInputChange(e)}
                                />
                                <span>(This is the max limit that user can set on their own without approval)</span>
                            </div>
                        </Col>
                    </Row>
                    <Row className="text-center mt-5">
                        <Col md={12}>
                            <Button
                                disabled={formValid}
                                className="btn-secondary mr-3"
                                // onClick={() => this.SaveLimit()}
                                onClick={() => this.toggleSubActionPopup()}
                            >
                                Save
                            </Button>
                        </Col>
                    </Row>
                </div>
                <Row className="mt-5">
                    <Col md={12}>
                        <h4>User List</h4>
                    </Col>
                </Row>
                <Row>
                    <Col md={12} className="table-responsive common-table">
                        <Table>
                            <thead>
                                <tr>
                                    <th
                                        className="left-th text-center"
                                        onClick={() => this.sortByColumn('modified_date', isDescOrder)}
                                    >
                                        Updated Date
                                        <div className={`d-inline-block ${(sortField === 'modified_date' && isDescOrder) ? '' : 'rotate-icon'}`}>
                                            <i className="icon-Shape ml-1"></i>
                                        </div>
                                    </th>
                                    <th
                                        onClick={() => this.sortByColumn('user_name', isDescOrder)}>
                                        User Name
                                             <div className={`d-inline-block ${(sortField === 'user_name' && isDescOrder) ? '' : 'rotate-icon'}`}>
                                            <i className="icon-Shape ml-1"></i>
                                        </div>
                                    </th>
                                    <th
                                        onClick={() => this.sortByColumn('max_limit', isDescOrder)}>
                                        New limit
                                             <div className={`d-inline-block ${(sortField === 'max_limit' && isDescOrder) ? '' : 'rotate-icon'}`}>
                                            <i className="icon-Shape ml-1"></i>
                                        </div>
                                    </th>
                                    <th>Changed By</th>
                                    <th className="right-th">Default Limit</th>
                                </tr>
                            </thead>
                            {
                                Total > 0 ?
                                    _.map(UserList, (item, idx) => {
                                        return (
                                            <tbody key={idx}>
                                                <tr>
                                                    <td>
                                                        {/* <Moment
                                                            date={WSManager.getUtcToLocal(item.modified_date)}
                                                            format="D-MMM-YYYY hh:mm A" /> */}
                                                            {HF.getFormatedDateTime(item.modified_date, 'D-MMM-YYYY hh:mm A')}
                                                    </td>
                                                    <td>{item.user_name}</td>
                                                    <td>{item.max_limit}</td>
                                                    <td>
                                                        {item.set_by == '1' && 'Set by user'}
                                                        {item.set_by == '2' && 'Set by admin'}
                                                    </td>                                                    
                                                    <td>
                                                        <a
                                                            onClick={() => this.toggleActionPopup(item.user_id, 1)}
                                                            className="se-set-default">Set to default</a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        )
                                    })
                                    :
                                    <tbody>
                                        <tr>
                                            <td colSpan="12">
                                                <div className="no-records">
                                                    {NC.NO_RECORDS}
                                                </div>
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
export default SelfExclude



















