import React, { Component, Fragment } from "react";
import { Row, Col, Input, Button, Tooltip } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import _ from 'lodash';
import Loader from '../../components/Loader';
export default class BackgroundImage extends Component {
    constructor(props) {
        super(props)
        this.toggle = this.toggle.bind(this);

        this.state = {
            fileUplode: '',
            tooltipOpen: false,

            fileName: '',
            BgImage: '',
            ImagePosting: true,
        }
    }
    componentDidMount() {
        this.getBgImage()
    }
    toggle() {
        this.setState({
            tooltipOpen: !this.state.tooltipOpen
        });
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
        data.append("userfile", file);

        if ((file.size / 1024000) > 4) {
            notify.show('File size must be less than 4 mb.', "error", 5000);
        } else {
            WSManager.multipartPost(NC.baseURL + NC.FRONT_BG_UPLOAD, data)
                .then(responseJson => {
                    document.getElementById("banner_image").value = "";
                    notify.show("Image uploaded successfully", "success", 3000)
                }).catch(error => {
                    notify.show(NC.SYSTEM_ERROR, "error", 3000);
                });
        }
    }

    getBgImage = () => {
        this.setState({ ImagePosting: false })
        WSManager.Rest(NC.baseURL + NC.GET_FRONT_BG_IMAGE, {}).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({
                    NewBgImage: responseJson.data.image_url,
                    ImagePosting: true
                })
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        })
    }

    resetBgImage = () => {
        this.setState({ ImagePosting: false })
        WSManager.Rest(NC.baseURL + NC.RESET_FRONT_BG_IMAGE, {}).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                notify.show(responseJson.message, "success", 3000)
                setTimeout(() => {
                    this.setState({
                        NewBgImage: responseJson.data.image_url,
                        ImagePosting: true
                    })
                }, 200);
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        })
    }

    render() {
        const { ImagePosting, NewBgImage } = this.state
        return (
            <Fragment>
                <div className="mt-4">
                    <Row>
                        <Col md={12}>
                            <h1 className="h1-cls">Front BG Image</h1>
                        </Col>
                    </Row>
                    <div className="animated fadeIn new-banner">
                        <Col md={12} className="input-row">
                            <Row>
                                <Col md={3} className='d-f'>
                                    <div className="b-input-label">Select Image (1780*1200)<span className="asterrisk">*</span> </div>
                                    <div className='info-banner-2 ml10'>
                                        <i className="icon-info" id="TooltipExample"></i>
                                        <Tooltip placement="bottom" isOpen={this.state.tooltipOpen} target="TooltipExample" toggle={this.toggle}>
                                            Static image displayed on web only.
                                            This image covers the right section of the microsite.
                                        </Tooltip>
                                    </div>
                                </Col>
                                <Col md={9}>
                                    <Input
                                        accept="image/*"
                                        type="file"
                                        name='banner_image'
                                        id='banner_image'
                                        onChange={this.onChangeImage}
                                    />
                                    <div className="set-img">
                                        {(!_.isEmpty(NewBgImage) && ImagePosting) ?
                                            <img className="img-cover" src={NewBgImage} />
                                            :
                                            <Loader />
                                        }
                                    </div>
                                </Col>
                            </Row>
                        </Col>
                        <Col md={12} className="banner-action">
                            <Button className="btn-secondary mr-3" onClick={() => this.resetBgImage()}>Reset BG Image</Button>
                        </Col>
                    </div>
                </div>
            </Fragment>
        )
    }
}