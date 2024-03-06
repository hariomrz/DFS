import React, { Component, Fragment } from 'react';
import { Col, Row, Input, Button } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';

class SF_Merchandise extends Component {

    constructor(props) {
        super(props);
        this.state = {
            reload: true,
            showCancelBtn: false,
            MName: '',
            MValue: '',
            fileName: '',
            formValid: false,
            MNameMSg: true,
            MValueMSg: true,
            MerchandiseList: [],
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            Previous_Img: '',
            EditItemData: [],
            editCase: false,
        };
    }

    componentDidMount() {
        this.getMerchandiseList();
    }

    handleInputChange = (event) => {
        this.setState({ MValueMSg: true })
        let name = event.target.name;
        let value = event.target.value;
        if(name === 'MName')
        {
            value = value.replace(/  +/g, ' ')
        }
        if(name === 'MValue' && (Number(value) < 1 || Number(value) > 99999))
        {
            value = ''
            this.setState({ MValueMSg: false })
        }
        this.setState({ [name]: value },
            () => this.validateForm(name, value)
        )
    }

    validateForm = (name, value) => {
        let NameValid = this.state.MName
        let ValueValid = this.state.MValue
        let FileNameValid = this.state.fileName

        console.log("FileNameValid==", FileNameValid);
        
        switch (name) {
            case 'MName':
                NameValid = (value.length > 2 && value.length <= 50) ? true : false;
                this.setState({ MNameMSg: NameValid })
                break;
            // case 'MValue':
            //     // ValueValid = (value.length > 10 && !value.match(/^[0-9]*$/)) ? false : true;
            //     ValueValid = (value == '0' && !value.match(/^[0-9]*$/)) ? false : true;
            //     this.setState({ MValueMSg: ValueValid }, () => {
            //         console.log("MValueMSg==", this.state.MValueMSg);

            //     })
            //     break;

            default:
                break;
        }

        this.setState({
            formValid: (NameValid && ValueValid && !_.isEmpty(FileNameValid)&& (!_.isUndefined(FileNameValid) && !_.isNull(FileNameValid)))
        })
    }

    onChangeImage = (event, uploadFor, merchandiseId) => {
        this.setState({
            fileName: URL.createObjectURL(event.target.files[0]),
        }, function () {
            this.validateForm()
        });
        const file = event.target.files[0];
        if (!file) {
            return;
        }
        var data = new FormData();
        if (this.state.editCase) {
            data.append("merchandise_id", merchandiseId)
        }
        data.append("file", file);
        data.append("source", uploadFor);
        data.append("previous_img", this.state.Previous_Img);

        WSManager.multipartPost(NC.baseURL + NC.SF_UPLOAD_MERCHANDISE_IMG, data)
            .then(Response => {
                if (Response.response_code == NC.successCode) {
                    this.setState({
                        IMAGE_NAME: Response.data.image_name
                    });
                } else {
                    this.setState({
                        fileName: null
                    }, this.validateForm);
                }
            }).catch(error => {
                
                notify.show(NC.SYSTEM_ERROR, "error", 3000);
            });
    }

    resetFile = () => {
        this.setState({
            fileName: null
        }, function () {
            this.validateForm()
        });
    }

    addMerchandise = () => {
        this.setState({ formValid: false })
        let { MName, MValue, IMAGE_NAME } = this.state
        let params = {
            name: MName,
            price: MValue,
            image_name: IMAGE_NAME
        }
        WSManager.Rest(NC.baseURL + NC.SF_ADD_MERCHANDISE, params).then(Response => {
            this.getMerchandiseList();
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, 'success', 5000)
                this.setState({
                    MName: '',
                    MValue: '0',
                    fileName: '',
                    IMAGE_NAME: ''
                })
            }
            this.setState({ formValid: true })
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    updateMerchandise = () => {
        let { MName, MValue, IMAGE_NAME, EditItemData } = this.state
        let params = {
            name: MName,
            price: MValue,
            image_name: IMAGE_NAME,
            merchandise_id: EditItemData.merchandise_id
        }
        WSManager.Rest(NC.baseURL + NC.SF_UPDATE_MERCHANDISE, params).then(Response => {
            this.getMerchandiseList();
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, 'success', 5000)
                this.setState({
                    MName: '',
                    MValue: '',
                    IMAGE_NAME: '',
                    fileName: '',
                    showCancelBtn: false
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
        this.setState({
            editCase: false
        })

    }

    getMerchandiseList = () => {
        let { PERPAGE, CURRENT_PAGE } = this.state
        let params = {
            sort_field: "added_date",
            sort_order: "DESC",
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
        }
        WSManager.Rest(NC.baseURL + NC.SF_GET_MERCHANDISE_LIST, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    MerchandiseList: Response.data.merchandise_list,
                    NextOffset: Response.data.next_offset,
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    editMerchandise = (item) => {
        let editFor = item.merchandise_id
        this.setState({
            showCancelBtn: true,
            EditItemData: item,
            editCase: true
        })
        let params = {
            merchandise_id: editFor
        }
        WSManager.Rest(NC.baseURL + NC.SF_GET_MERCHANDISE_BY_ID, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    MName: Response.data.name,
                    MValue: Response.data.price,
                    IMAGE_NAME: Response.data.image_name,
                    fileName: NC.S3 + NC.MERCHANDISEIMG + Response.data.image_name,
                    Previous_Img: Response.data.image_name,
                    reload: false
                }, () => {
                    window.scrollTo({
                        top: 0,
                        behavior: "smooth"
                    })

                    this.setState({
                        reload: true
                    })
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    resetValue = () => {
        this.setState({
            reload: false,
            MName: '',
            MValue: '0',
            fileName: '',
            IMAGE_NAME: ''
        }, () => {
            this.setState({
                reload: true
            })
        })
    }

    render() {
        const {
            fileName,
            MNameMSg,
            MValueMSg,
            MName,
            MValue,
            formValid,
            MerchandiseList,
            reload,
            showCancelBtn,
            EditItemData
        } = this.state

        return (
            <div className="animated fadeIn sf-add-merchandise">

                <div className="form-container">
                    <div className="header-primary">
                        Add Merchandise
                    </div>
                    {reload &&
                        <div className="form-body add-rewards">
                            <Row className="pb-3">
                                <Col xs={8} className="border-right">
                                    <figure className="upload-img">
                                        {!_.isEmpty(fileName) ?
                                            <Fragment>
                                                <a
                                                    onClick={() => this.resetFile()}
                                                >
                                                    <i className={showCancelBtn ? "icon-delete" : "icon-close"}></i>
                                                </a>
                                                <img className="img-cover" src={fileName} />
                                            </Fragment>
                                            :
                                            <Fragment>
                                                {this.state.editCase ?
                                                    <Fragment>
                                                        <Input
                                                            accept="image/x-png,image/gif,image/jpeg,image/bmp,image/jpg"
                                                            type="file"
                                                            name='merchandise_image'
                                                            id="merchandise_image"
                                                            className="gift_image"
                                                            onChange={(e) => this.onChangeImage(e, 'edit', EditItemData.merchandise_id)}
                                                        />
                                                        <i onChange={(e) => this.onChangeImage(e, 'edit', EditItemData.merchandise_id)} className="icon-camera"></i>
                                                    </Fragment>
                                                    :
                                                    <Fragment>
                                                        <Input
                                                            accept="image/x-png,image/gif,image/jpeg,image/bmp,image/jpg"
                                                            type="file"
                                                            name='merchandise_image'
                                                            id="merchandise_image"
                                                            className="gift_image"
                                                            onChange={(e) => this.onChangeImage(e, 'add', '')}
                                                        />
                                                        <i onChange={(e) => this.onChangeImage(e, 'add', '')} className="icon-camera"></i>
                                                    </Fragment>

                                                }
                                            </Fragment>
                                        }
                                    </figure>
                                    <div className="figure-help-text">Please upload image with maximum size of 150 by 150.</div>
                                    <div className="input-box">
                                        <div className="mb-3">
                                            <label htmlFor="MName">Name</label>
                                            <Input
                                                type='text'
                                                maxLength={50}
                                                name='MName'
                                                value={MName}
                                                onChange={this.handleInputChange}
                                            />

                                            {!MNameMSg &&
                                                <span className="color-red">
                                                    Name should be in the range of 3 to 50
                                                </span>
                                            }
                                        </div>
                                        <div className="mb-3">
                                            <label htmlFor="MValue">Value</label>
                                            <Input
                                                type='Number'
                                                maxLength={10}
                                                name='MValue'
                                                value={MValue}
                                                onChange={this.handleInputChange}
                                            />
                                            {
                                                !MValueMSg &&
                                                <div className="color-red">Value should be in the range of 1 to 99999</div>
                                            }
                                        </div>
                                        {showCancelBtn ?
                                            <Button
                                                disabled={!formValid}
                                                className="btn-secondary-outline publish-btn float-right"
                                                onClick={() => this.updateMerchandise()}
                                            >Update</Button>
                                            :
                                            <Button
                                                disabled={!formValid}
                                                className="btn-secondary-outline publish-btn float-right"
                                                onClick={this.addMerchandise}
                                            >Save</Button>
                                        }
                                    </div>
                                </Col>
                                <Col xs={4} className="text-center">
                                    <div className="sf-uploaded-logo-view">
                                        {fileName &&
                                            <img className="img-cover" src={fileName} />
                                        }
                                    </div>
                                    <div className="uploaded-label">
                                        {MName}
                                    </div>
                                </Col>
                            </Row>
                        </div>
                    }
                    <Row className="sf-add-m-list-wrap">
                        <Col xs={12}>
                            <div className="sf-add-m-list">
                                {_.map(MerchandiseList, (item, idx) => {
                                    return (
                                        <div key={idx} className="merchandise-info-wrap" id={'name' + idx}>
                                            <div className="merchandise-img-wrap">
                                                <a
                                                    onClick={() => this.editMerchandise(item)}
                                                >
                                                    <i className="icon-edit"></i>
                                                </a>
                                                <img src={NC.S3 + NC.MERCHANDISEIMG + item.image_name} alt="" />
                                            </div>
                                            <div className="merchandise-related-data">
                                                <div className="merchandise-label">{item.name}</div>
                                                <div className="amt">{item.price}</div>
                                            </div>
                                        </div>
                                    )
                                })

                                }
                            </div>
                        </Col>
                    </Row>
                </div>
            </div>
        );
    }
}

export default SF_Merchandise;