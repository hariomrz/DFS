import React, { Component, Fragment } from 'react'
import { Row, Col, Table, Button, FormGroup, Input, InputGroup, Label, Modal, ModalBody, ModalFooter, ModalHeader } from "reactstrap";
import WSManager from "../../helper/WSManager";
import * as NC from "../../helper/NetworkingConstants";
import { notify } from 'react-notify-toast';
import _ from 'lodash'
import Images from "../../components/images";
import { SAVE_RECORD_WHATSNEW, DO_UPLOAD_WHATSNEW_IMG } from '../../helper/WSCalling';
import HF, { _remove, _Map, _debounce, _isEmpty, _isUndefined, _isNull } from "../../helper/HelperFunction";
export class AddWhatsNew extends Component {
    constructor(props) {
        super(props)

        this.state = {
            title: '',
            decription: '',
            fileUplode: '',
            isValid: false,
            posting: false,
            fileUplode: '',
            fileName: '',
            imagePosting: false,
            isEdit:false,
            ImageName:''
        }
    }

    componentDidMount=()=>{
        if(this.props.isEdit && this.props.editData){
            this.setState({
                title:  this.props.editData.name,
                decription:  this.props.editData.description,
                ImageName: this.props.editData.image,
                isEdit: this.props.isEdit
            },() => this.validateFields())
        }
    }

    handleInputChange = (e) => {
        this.setState({
            [e.target.name]: e.target.value
        }
            , () => {
                this.validateFields()
            }
        )
    }
    validateFields = () => {
        const { title, decription, fileUplode,ImageName,isEdit } = this.state
        this.setState({
            isValid: (title != '' && title.length <= 30 != '' && title.length >= 4 != '' ) && 
            (decription != '' && decription.length <= 150 != '' && decription.length >= 15 != '' )
             && (isEdit? ImageName : fileUplode != '')
        })
    }

    saveWhatsNewData = (e) => {
        const { title, decription, ImageName } = this.state
        this.setState({ posting: true })
        let params = {
            "name": title,
            "description": decription,
            "image": ImageName
        }
        WSManager.Rest(NC.baseURL + NC.SAVE_RECORD_WHATSNEW, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                notify.show(responseJson.message, "success", 3000)
                this.setState({ posting: false }, () => {
                    this.props.addModalhHide()
                })
            } else {
                notify.show(responseJson.message, "error", 3000)
                this.setState({ posting: false })
            }

        })
            .catch(error => {
                notify.show(NC.SYSTEM_ERROR, "error", 3000);
            });
    }
    editWhatsNewData = (e) => {
        const { title, decription, ImageName } = this.state
        this.setState({ posting: true })
        let params = {
            "id":this.props.editData.id,
            "name": title,
            "description": decription,
            "image": ImageName 
        }
        WSManager.Rest(NC.baseURL + NC.EDIT_REOCRD_SAVED, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                notify.show(responseJson.message, "success", 3000)
                this.setState({ posting: false }, () => {
                    this.props.addModalhHide()
                })
            } else {
                notify.show(responseJson.message, "error", 3000)
                this.setState({ posting: false })
            }

        })
            .catch(error => {
                notify.show(NC.SYSTEM_ERROR, "error", 3000);
            });
    }

    onChangeImage = (event) => {
        this.setState({
            fileUplode: event.target.files[0].name,
        }, function () {
            this.validateFields()
        });

        const file = event.target.files[0];
        if (!file) {
            return;
        }

        var data = new FormData();
        data.append("file_name", file);
        data.append("type", "whatsnew");
        WSManager.multipartPost(NC.baseURL + NC.DO_UPLOAD_WHATSNEW_IMG, data)
            .then(responseJson => {
                notify.show(responseJson.message, "success", 3000)
                this.setState({
                    fileUplode: responseJson.data.image_url,
                    ImageName: responseJson.data.image_name,
                });
            }).catch(error => {
                notify.show(NC.SYSTEM_ERROR, "error", 3000);
            });
    }

    render() {
        let { addModalShow, addModalhHide,isEdit } = this.props;
        const { title, decription, isValid,imagePosting,ImageName } = this.state;
        return (
            <Fragment>

                <Modal
                    isOpen={addModalShow}
                    toggle={() => this.props.addModalhHide()}
                    className="modal-edit-sm add-whats-new-con"
                >

                    <ModalHeader className="add-whats-new">
                        What’s New
                        <i className='icon-close' onClick={() => this.props.addModalhHide()} />
                    </ModalHeader>
                    <ModalBody>


                        <Row>
                            <Col md={12}>
                                <label className='title-lable'>Title</label>
                                <Input
                                    maxLength="50"
                                    type="text"
                                    name="title"
                                    value={ title }
                                    placeholder='Enter Title'
                                    onChange={(e) => this.handleInputChange(e)}
                                />
                                 {
                                            (title != "") && !( title.length <= 30) ? <span className="error-message-whatsnew">The Name field cannot exceed 30 characters in length.</span> : (title != "") && !(title.length >= 4)? <span className="error-message-whatsnew">The Name field must be at least 4 characters in length.</span>:null
                                        }
                            </Col>
                        </Row>



                        <Row>
                            <Col md={12}>
                                <label className='title-lable'>Description</label>
                                <Input
                                className='textarea-description'
                                     type="textarea"
                                     maxLength={500}
                                    name="decription"
                                    value={decription}
                                    placeholder='Enter Description'
                                    onChange={(e) => this.handleInputChange(e)}
                                />
                                  {
                                            (decription != "") && !( decription.length <= 150) ? <span className="error-message-whatsnew">The Description field cannot exceed 150 characters in length.</span> : (decription != "") && !(decription.length >= 15)? <span className="error-message-whatsnew">The Description field must be at least 15 characters in length.</span>:null
                                        }
                            </Col>
                        </Row>


                        <Row>
                            <Col md={12}>
                                <label className='title-lable'>Image</label>
                                <div className="sf-image">
                                <Input
                                    type="file"
                                    name='banner_image'
                                    onChange={this.onChangeImage}
                                />
                                    {this.state.fileUplode 
                                     ?
                                        <div>
                                            <img className="img-cover preview-view-img"
                                            src={(this.state.fileUplode ) ?  this.state.fileUplode : Images.no_image} />
                                        </div> :
                                        <div className="sf-icon-txt" >
                                            {ImageName ? 
                                              <img className="def-addphoto  preview-view-img"  
                                              src={(ImageName) ? NC.S3 + NC.WHATSNEW_IMG_PATH + ImageName : Images.IMAGE_GALLARY}
                                               alt="" />
                                            :
                                           <>  <img className="def-addphoto" src={Images.IMAGE_GALLARY} alt="" />Browse Image (400*476 px)</>
                                            }
                                           

                                        </div>
                                    }
                                </div>
                               


                            </Col></Row>



                    </ModalBody>
                    <ModalFooter className="request-footer request-footer-view">
                        <Row className="text-center mt-30">
                            <Col md={12}>
                                <Button className='btn-secondary'
                                    disabled={!isValid}
                                    onClick={() => isEdit ? this.editWhatsNewData() : this.saveWhatsNewData()}
                                     >
                                    Save
                                </Button>
                            </Col>
                        </Row>
                    </ModalFooter>
                </Modal>
            </Fragment>
        )
    }
}

export default AddWhatsNew;


// The Name field must be at least 4 characters in length.
// The Name field cannot exceed 30 characters in length.
// The Description field must be at least 15 characters in length.
// The Description field cannot exceed 150 characters in length.