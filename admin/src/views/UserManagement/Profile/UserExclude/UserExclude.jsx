import React, { Component } from "react";
import { Input, Button, Row, Col } from 'reactstrap';
import * as NC from "../../../../helper/NetworkingConstants";
import WSManager from "../../../../helper/WSManager";
import { notify } from 'react-notify-toast';
import _ from 'lodash';
import ActionRequestModal from '../../../../components/ActionRequestModal/ActionRequestModal';
import Loader from '../../../../components/Loader';
import HF from '../../../../helper/HelperFunction';
class UserExclude extends Component {
    constructor(props) {
        super(props)
        this.state = {
            DefaultLimit: '',
            MaximumLimit: '',
            formValid: true,
            NewBgImage: '',
            ImagePosting: true,
            ImageName: '',
            ImageURL: '',
        };
    }
    componentDidMount() {
        if (HF.allowSelfExclusion() != '1') {
            notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        this.getLimit();
    }

    getLimit = () => {
        let params = {
            'user_id': this.props.user_id
        }
        WSManager.Rest(NC.baseURL + NC.GET_USER_SELF_EXCLUSION, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    Reason: ResponseJson.data ? ResponseJson.data.reason : '',
                    DefaultLimit: ResponseJson.data ? ResponseJson.data.max_limit : '',
                    SetByType: ResponseJson.data ? ResponseJson.data.set_by : '',
                    ImageURL: ResponseJson.data ? ResponseJson.data.document : '',
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    handleInputChange = (e) => {
        let name = e.target.name;
        let value = e.target.value;
        this.setState({ [name]: value, formValid: false }, () => {

            if (_.isEmpty(this.state.DefaultLimit)) {
                let msg = 'Limit can not be empty.'
                notify.show(msg, 'error', 3000)
                this.setState({ formValid: true })
                return false
            }
        });
    }

    SubmitLimit = () => {
        this.setState({ formValid: true })
        let { DefaultLimit, ImageName, Reason } = this.state

        let params = {
            "user_id": this.props.user_id,
            "max_limit": DefaultLimit,
            "document": ImageName,
            "reason": Reason,
        }

        WSManager.Rest(NC.baseURL + NC.SET_SELF_EXCLUSION, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.toggleSubActionPopup()
                this.setState({ SetByType : '2' })
                notify.show(Response.message, 'success', 5000)
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    //function to toggle action popup
    toggleActionPopup = () => {
        this.setState({
            Message: NC.MSG_SET_TO_DEF,
            ActionPopupOpen: !this.state.ActionPopupOpen
        })
    }

    //function to toggle action popup
    toggleSubActionPopup = () => {
        this.setState({
            SubMessage: NC.MSG_SUBMIT_LIMIT,
            SubActionPopupOpen: !this.state.SubActionPopupOpen
        })
    }

    setDefault = () => {
        this.setState({ setDefPost: true })
        let params = {
            user_id: this.props.user_id
        }
        WSManager.Rest(NC.baseURL + NC.SET_DEFAULT_SELF_EXCLUSION, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({ setDefPost: false, SetByType: '' })
                this.toggleActionPopup()
                notify.show(Response.message, 'success', 5000)
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    onChangeImage = (event) => {
        this.setState({
            NewBgImage: URL.createObjectURL(event.target.files[0]),
            fileUplode: event.target.files[0].name
        });

        const file = event.target.files[0];
        if (!file) {
            return;
        }
        var data = new FormData();
        data.append("file", file);
        WSManager.multipartPost(NC.baseURL + NC.SELF_EXCLUSION_DOCUMENT_UPLOAD, data)
            .then(responseJson => {
                this.setState({
                    ImageName: !_.isEmpty(responseJson.data) ? responseJson.data.file_name : '',
                    ImageURL: !_.isEmpty(responseJson.data) ? responseJson.data.file_name : '',
                })
            }).catch(error => {
                notify.show(NC.SYSTEM_ERROR, "error", 3000);
            });
    }

    render() {
        let { DefaultLimit, ActionPopupOpen, Message, formValid, NewBgImage, ImagePosting, Reason, SubMessage, SubActionPopupOpen, setDefPost, ImageURL, SetByType } = this.state
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
            modalActioCallback: this.SubmitLimit,
            posting: formValid
        }
        return (
            <div className="self-exclusion animated fadeIn">
                <ActionRequestModal {...ActionCallback} />
                <ActionRequestModal {...SubmitProps} />
                <Row>
                    <Col md={12}>
                        <h2 className="h2-class">Self Exclusion</h2>
                    </Col>
                </Row>
                <div className="se-limit-box">
                    <Row>
                        <Col md={3}><label className="se-label">Self Exclusion Limit</label></Col>
                        <Col md={4}>
                            <div className="se-input-div">
                                <div className="se-input-box">
                                    <Input
                                        type="number"
                                        placeholder="500"
                                        name='DefaultLimit'
                                        value={DefaultLimit}
                                        onChange={(e) => this.handleInputChange(e)}
                                    />
                                    <span>
                                        {/* Set as Default */}
                                        {SetByType == '' && 'Set as Default'}
                                        {SetByType == '1' && 'Set by User'}
                                        {SetByType == '2' && 'Set by Admin'}
                                    </span>
                                </div>
                            </div>
                        </Col>
                        <Col md={5} className="mt-2">
                            <a
                                onClick={() => this.toggleActionPopup('10', 1)}
                                className="ue-set-default">Set to default</a>
                        </Col>
                    </Row>
                    <Row className="mt-5 mb-30">
                        <Col md={12}>
                            <div className="ue-sup-doc">Supporting Document</div>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={3}><label className="se-label">Reason</label></Col>
                        <Col md={4}>
                            <Input
                                type="textarea"
                                name='Reason'
                                value={Reason}
                                onChange={(e) => this.handleInputChange(e)}
                            />
                            <div className="se-img-box">
                                <span className="se-img-txt">Upload File</span>
                                <Input
                                    // accept="image/*"
                                    type="file"
                                    name='banner_image'
                                    id='banner_image'
                                    className="mt-2"
                                    onChange={this.onChangeImage}
                                />
                            </div>
                            {ImageURL && <div className="set-img">
                                <a href={NC.S3 + NC.SELF_EXCLUSION_PATH + ImageURL} target='_blank'>View File</a>
                            </div>}
                            {/* <div className="set-img">
                                {(!_.isEmpty(NewBgImage) && ImagePosting) ?
                                    <img className="img-cover" src={NewBgImage} />
                                    :
                                    !ImagePosting
                                        ?
                                        <Loader />
                                        :
                                        ''
                                }
                            </div> */}
                        </Col>
                        <Col md={5}></Col>
                    </Row>
                    <Row className="text-center mt-5">
                        <Col md={12}>
                            <Button
                                disabled={formValid}
                                className="btn-secondary mr-3"
                                // onClick={() => this.SubmitLimit()}
                                onClick={() => this.toggleSubActionPopup()}
                            >
                                Submit
                            </Button>
                        </Col>
                    </Row>
                </div>
            </div>
        )
    }
}
export default UserExclude



















