import React, { Component, Fragment } from "react";
import { Row, Col, Input, Button } from 'reactstrap';
import * as NC from '../../helper/NetworkingConstants';
import WSManager from "../../helper/WSManager";
import HF, { _isEmpty, _Map, _remove } from '../../helper/HelperFunction';
import Images from "../../components/images";
import Pagination from "react-js-pagination";
import Loader from '../../components/Loader';
import { notify } from 'react-notify-toast';
import { H2H_SAVE_CMS, H2H_GET_CMS_LIST, H2H_DELETE_CMS } from '../../helper/WSCalling';
import { H2H_DELETE_LEVEL, H2H_DELETE_LEVEL_SUB } from "../../helper/Message";
import PromptModal from '../../components/Modals/PromptModal';
class H2HCms extends Component {
    constructor(props) {
        super(props)
        this.state = {
            BgImageUrl: '',
            BgImageName: '',
            BgImageLoad: false,
            LogoImageUrl: '',
            LogoImageName: '',
            LogoImageLoad: false,
            SaveBtnLoad: true,
            CmsData: [],
            EditId: '',
        }
    }

    componentDidMount = () => {
        this.getCMS()
    }

    onChangeImage = (event, type) => {
        let { EditId } = this.state
        if (type == 1) {
            this.setState({ BgImageLoad: true })
        } else {
            this.setState({ LogoImageLoad: true })
        }

        const file = event.target.files[0];
        if (!file) {
            return;
        }
        var data = new FormData();

        data.append("source", !_isEmpty(EditId) ? 'edit' : 'add');
        data.append("type", type);//0-LogoImage,1-BackgroundImage
        data.append("file", file);
        data.append("id", EditId);

        WSManager.multipartPost(NC.baseURL + NC.H2H_DO_UPLOAD, data)
            .then(responseJson => {
                let img_url = responseJson.data.image_url
                let img_name = responseJson.data.image_name
                if (type == 1) {
                    this.setState({
                        BgImageUrl: img_url,
                        BgImageName: img_name,
                        BgImageLoad: false,
                    }, this.checkValidation)
                } else {
                    this.setState({
                        LogoImageUrl: img_url,
                        LogoImageName: img_name,
                        LogoImageLoad: false,
                    }, this.checkValidation)
                }
            }).catch(error => {
                notify.show(NC.SYSTEM_ERROR, "error", 3000);
            });
    }

    resetFile = (type) => {
        if (type == 1) {
            this.setState({
                BgImageUrl: '',
                BgImageName: '',
            }, this.checkValidation)
        } else {
            this.setState({
                LogoImageUrl: '',
                LogoImageName: '',
            }, this.checkValidation)
        }
    }

    handleInputChange = (e) => {
        let name = e.target.name
        let value = e.target.value
        this.setState({ [name]: value }, this.checkValidation)
    }

    checkValidation = () => {
        let { BgImageUrl, LogoImageUrl, Title } = this.state
        let btn = false
        if (_isEmpty(BgImageUrl)) {
            notify.show('Please upload background image', 'error', 3000)
            btn = true
        }
        else if (_isEmpty(LogoImageUrl)) {
            notify.show('Please upload logo image', 'error', 3000)
            btn = true
        }
        else if (_isEmpty(Title) || Title.length < 10) {
            notify.show('Please enter title in the range of 10 to 70', 'error', 3000)
            btn = true
        }
        this.setState({ SaveBtnLoad: btn })
    }

    editCMS = (item) => {
        this.setState({
            EditId: item.id,
            BgImageUrl: HF.getImageUrl(NC.H2H_CMS, item.bg_image),
            BgImageName: item.bg_image,
            LogoImageUrl: HF.getImageUrl(NC.H2H_CMS, item.image_name),
            LogoImageName: item.image_name,
            Title: item.name,
        })
    }

    saveCMS = () => {
        let { BgImageName, LogoImageName, Title, EditId } = this.state
        this.setState({ SaveBtnLoad: true })

        let params = {
            "name": Title,
            "image_name": LogoImageName,
            "bg_image": BgImageName,
        };
        if (!_isEmpty(EditId)) {
            params.id = EditId
        }

        H2H_SAVE_CMS(params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({
                    BgImageUrl: '',
                    BgImageName: '',
                    LogoImageUrl: '',
                    LogoImageName: '',
                    Title: '',
                }, this.getCMS)
                notify.show(responseJson.message, "success", 5000);
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        });
    }

    getCMS = () => {
        this.setState({ ListPosting: true })
        H2H_GET_CMS_LIST({}).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    CmsData: ResponseJson.data,
                    Total: ResponseJson.data ? ResponseJson.data.length : 0,
                    ListPosting: false,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    deleteToggle = (did, idx) => {
        this.setState(prevState => ({
            delIdx: idx,
            Del_ID: did,
            DeleteModalOpen: !prevState.DeleteModalOpen
        }));
    }

    deleteCms = () => {
        this.setState({ DeletePosting: true })
        const { delIdx, Del_ID, CmsData } = this.state
        const param = { id: Del_ID }
        let d_list = CmsData

        _remove(d_list, function (item, idx) {
            return idx == delIdx
        })

        H2H_DELETE_CMS(param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                _remove(d_list, function (item, idx) {
                    return idx == delIdx
                })

                notify.show(responseJson.message, "success", 5000);
                this.setState({
                    CmsData: d_list,
                    Total: d_list.length,
                })
            }
            this.setState({
                DeletePosting: false,
                DeleteModalOpen: false,
            })
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    render() {
        let { SaveBtnLoad, Title, BgImageLoad, BgImageUrl, LogoImageLoad, LogoImageUrl, CmsData, Total, ListPosting, EditId, DeleteModalOpen, DeletePosting } = this.state

        let DeleteModalProps = {
            publishModalOpen: DeleteModalOpen,
            publishPosting: DeletePosting,
            modalActionNo: this.deleteToggle,
            modalActionYes: this.deleteCms,
            MainMessage: H2H_DELETE_LEVEL,
            SubMessage: H2H_DELETE_LEVEL_SUB,
        }

        return (
            <div className="h2h-cms">
                {DeleteModalOpen && <PromptModal {...DeleteModalProps} />}
                <Row className="h2h-head">
                    <Col md={6}>
                        <h2 className="h2-cls">CMS</h2>
                    </Col>
                    {/* <Col md={6}>
                        <label className="back-to-fixtures"
                            onClick={() => this.props.history.push('/game_center/h2h')}>
                            {'<< '} Back</label>
                    </Col> */}
                </Row>
                <Row>
                    <Col md={12}>
                        <div className="header-primary">Banner</div>
                    </Col>
                </Row>
                <div className="form-box">
                    <Row>
                        <Col md="8" className="h2h-br-rgt pr-0">
                            <div>
                                <Row>
                                    <Col md="12" className="pr-0">
                                        <figure className="h2h-upload-img xmr-4">
                                            {!_isEmpty(BgImageUrl) ?
                                                BgImageLoad ?
                                                    <Loader />
                                                    :
                                                    <Fragment>
                                                        <i onClick={() => this.resetFile(1)} className="icon-close"></i>
                                                        <img className="img-cover" src={BgImageUrl} />
                                                    </Fragment>
                                                :
                                                <Fragment>
                                                    {
                                                        <Fragment>
                                                            <Input
                                                                accept="image/x-png,image/gif,image/jpeg,image/bmp,image/jpg"
                                                                type="file"
                                                                name='gift_image'
                                                                id="gift_image"
                                                                className="h2h_image"
                                                                onChange={(e) => this.onChangeImage(e, 1)}
                                                            />
                                                            <div className="h2h-upload-btn">
                                                                <i onChange={(e) => this.onChangeImage(e, 1)} className="icon-camera"></i>
                                                                <span className="h2h-up-title">Upload Background Image</span>
                                                                <span>400X400</span>
                                                            </div>
                                                        </Fragment>
                                                    }
                                                </Fragment>
                                            }
                                        </figure>
                                        <figure className="h2h-upload-img">
                                            {!_isEmpty(LogoImageUrl) ?
                                                LogoImageLoad ?
                                                    <Loader />
                                                    :
                                                    <Fragment>
                                                        <i onClick={() => this.resetFile(0)} className="icon-close"></i>
                                                        <img className="img-cover" src={LogoImageUrl} />
                                                    </Fragment>
                                                :
                                                <Fragment>
                                                    {
                                                        <Fragment>
                                                            <Input
                                                                accept="image/x-png,image/gif,image/jpeg,image/bmp,image/jpg"
                                                                type="file"
                                                                name='gift_image'
                                                                id="gift_image"
                                                                className="h2h_image"
                                                                onChange={(e) => this.onChangeImage(e, 0)}
                                                            />
                                                            <div className="h2h-upload-btn">
                                                                <i onChange={(e) => this.onChangeImage(e, 0)} className="icon-camera"></i>
                                                                <span className="h2h-up-title">Upload Logo Image</span>
                                                                <span>200X200</span>
                                                            </div>
                                                        </Fragment>
                                                    }
                                                </Fragment>
                                            }
                                        </figure>
                                    </Col>
                                </Row>
                                <Row className="mt-4">
                                    <Col md="12">
                                        <div className="h2h-input-box clearfix">
                                            <div className="mb-3">
                                                <label htmlFor="Title">Title</label>
                                                <div className="h2h-inp-box">
                                                    <Input
                                                        maxLength={70}
                                                        name='Title'
                                                        value={Title}
                                                        onChange={this.handleInputChange}
                                                    />
                                                    {/* {!DetailMSg &&
                                                    <span className="color-red">
                                                        Please enter valid details.
                                                    </span>
                                                } */}
                                                </div>
                                                <Button
                                                    disabled={SaveBtnLoad}
                                                    className="btn-secondary-outline publish-btn"
                                                    onClick={this.saveCMS}
                                                >
                                                    {
                                                        !_isEmpty(EditId) ? 'Update' : 'Save'
                                                    }
                                                </Button>
                                            </div>
                                        </div>
                                    </Col>
                                </Row>
                            </div>
                        </Col>
                        <Col md="4">
                            <div className="h2h-img-preview-box">
                                <Fragment>
                                    {!_isEmpty(BgImageUrl) || !_isEmpty(LogoImageUrl) || !_isEmpty(Title) ?
                                        <div className="h2h-div-dis">
                                            <div className="h2h-cms-card">
                                                {
                                                    LogoImageUrl &&
                                                    <figure className="h2h-logo">
                                                        <img className="img-cover" src={LogoImageUrl} alt="" />
                                                    </figure>
                                                }
                                                {
                                                    BgImageUrl &&
                                                    <figure>
                                                        <img className="img-cover img-lay" src={BgImageUrl} alt="" />
                                                    </figure>
                                                }
                                                <div className="h2h-img-title">
                                                    {Title}
                                                </div>
                                            </div>
                                        </div>
                                        :
                                        <span className="preview-text">Your Preview will<br /> appear here</span>
                                    }

                                </Fragment>
                            </div>
                        </Col>
                    </Row>
                </div>
                <div className="h2h-rewards-list">
                    <Row>
                        {
                            Total > 0 ?
                                _Map(CmsData, (item, idx) => {
                                    return (
                                        <div key={idx} className="h2h-div-dis">
                                            <div className="h2h-cms-card">
                                                <div className="h2h-act">
                                                    <i
                                                        className="icon-edit"
                                                        onClick={() => this.editCMS(item)}
                                                    ></i>
                                                    <i
                                                        className="icon-delete"
                                                        onClick={() => this.deleteToggle(item.id, idx)}
                                                    ></i>
                                                </div>
                                                <figure className="h2h-logo">
                                                    <img className="img-cover" src={item.image_name ? HF.getImageUrl(NC.H2H_CMS, item.image_name) : Images.no_image} alt="" />
                                                </figure>
                                                <figure>
                                                    <img className="img-cover img-lay" src={item.bg_image ? HF.getImageUrl(NC.H2H_CMS, item.bg_image) : Images.no_image} alt="" />
                                                </figure>
                                                <div className="h2h-img-title">
                                                    {item.name}
                                                </div>
                                            </div>
                                        </div>
                                    )
                                })
                                :
                                <Col md={12}>
                                    {(Total == 0 && !ListPosting) ?
                                        <div className="no-records">{NC.NO_RECORDS}</div>
                                        :
                                        <Loader />
                                    }
                                </Col>
                        }
                    </Row>
                </div>
            </div>
        )
    }
}
export default H2HCms

