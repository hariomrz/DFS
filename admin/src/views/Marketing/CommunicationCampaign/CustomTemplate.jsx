import React, { Component, Fragment } from 'react';
import Select from 'react-select'
import { Col, Row, Button, Input, Nav, NavItem, NavLink, Modal, ModalBody, ModalFooter, ModalHeader, TabContent, TabPane, Table } from 'reactstrap';
import { notify } from 'react-notify-toast';
import Pagination from "react-js-pagination";
import * as NC from "../../../helper/NetworkingConstants";
import _ from 'lodash';
import WSManager from "../../../helper/WSManager";
import * as MODULE_C from "../Marketing.config";
import queryString from 'query-string';
import Images from '../../../components/images';
import { _times, _Map } from '../../../helper/HelperFunction';
// import { getSmsTemplate, updateSmsTemplate } from '../../../helper/WSCalling';
import Loader from '../../../components/Loader';
class CustomeTemplate extends Component {
    constructor(props) {
        super(props);
        this.state = {
            activeTab: '2',
            CURRENT_PAGE: 1,
            PERPAGE: NC.ITEMS_PERPAGE,
            Filter: 0,
            FilterList: [],
            addCateModalOpen: false,
            CreatePosting: true,
            addSmsNotiModalOpen: false,
            CreateSmsPosting: true,
            CategoryList: [],
            CategoryListModal: [],
            CategoryName: '',
            TemplateName: '',
            NotificationSub: '',
            NotificationBody: '',
            NotifyBodyLength: 0,
            NotifyTextLength: 0,
            NotifyUrlLength: 0,
            previewObj: {},
            TemplateList: [],
            SavePosting: false,
            NotiHeadImgName : '',
            NotiBodyImgName : '',
            smsTempModalOpen : false,
            DltTemplateId : '',
            TemplateStatus : '',
            DltSmsTempList : [],            
            smsTblPosting : true,            
            DltSmsTempStatus : '0',            
            DltSmsItem : [],            
            DltMessage : '',            
            dltSmsPosting : false,            
            SmsTemplateName : '',            
        }
    }

    componentDidMount() {
        this.getAllCategory()
        // this.getCustomTemplates()
        // this.getSmsDltTemplates()
        this.getCustomTemplates()
        let url = this.props.location.search;
        let urlParams = queryString.parse(url);
        if (urlParams.category) {
            this.setState({ addCateModalOpen: true })
        }
    }

    getAllCategory = () => {
        WSManager.Rest(NC.baseURL + MODULE_C.GET_TEMPLATE_CATEGORY, {}).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                var category_list = [{
                    value: "",
                    label: "All Templates"
                }];
                var category_list_modal = [];
                _.map(responseJson.data, (item) => {
                    if (item.category_id != "9" && item.category_id != "10") {
                        category_list.push({
                            value: item.category_id,
                            label: item.category_name
                        });
                        category_list_modal.push({
                            value: item.category_id,
                            label: item.category_name
                        });
                    }
                });
                this.setState({
                    CategoryList: category_list,
                    CategoryListModal: category_list_modal,
                });
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    getCustomTemplates = () => {
        let { activeTab, SelectedFilterCategory, PERPAGE, CURRENT_PAGE } = this.state
        let param = {
            "template_id": '',
            "category_id": SelectedFilterCategory,
            "message_type": activeTab == '3' ? '' : activeTab,
            "item_perpage": PERPAGE,
            "current_page": CURRENT_PAGE,
        }
        WSManager.Rest(NC.baseURL + MODULE_C.GET_CUSTOME_TEMPLATE, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({
                    TemplateList: responseJson.data,
                    Total: responseJson.data.length,
                });
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    toggle(tab) {
        if (tab != this.state.activeTab)
            this.setState({ activeTab: tab, Filter: 0 },()=>{
                // if(this.state.activeTab == "1")
                //     this.getSmsDltTemplates()
                // else
                    this.getCustomTemplates()
            })
    }

    handlePageChange(current_page) {
        if (this.state.CURRENT_PAGE != current_page) {
            this.setState({
                CURRENT_PAGE: current_page
            }, this.getCustomTemplates
            );
        }
    }

    handleFilter = (e, name) => {
        if (e) {
            this.setState({ [name]: e.value }, this.getCustomTemplates)
        }
    }

    renderCommonBodyView = (template_data) => {
        let { PERPAGE, CURRENT_PAGE, Total, activeTab } = this.state
        return (
            <Fragment>
                <Row>
                    {
                        !_.isEmpty(template_data) ?
                            _.map(template_data, (item, idx) => {
                                return (
                                    !_.isEmpty(item.message_body) &&
                                    <Col md={4} key={idx}>
                                        <div className={`cus-temp-wrapper ${activeTab == '2' ? ' temp-noti-sty' : ''}`}>
                                            {activeTab == '2' &&
                                                <div className="noti-headear clearfix">
                                                <div className={`temp-subject ${activeTab == '2' ? ' noti-sty' : ''}`}>
                                                    {item.subject}
                                                </div>
                                                {
                                                    (!_.isUndefined(item.header_image) && !_.isNull(item.header_image)) &&
                                                    <div className="img-head-view">
                                                    <img 
                                                        className="img-cover" 
                                                            src={item.header_image ? NC.S3 + NC.PUSH_HEADER + item.header_image : Images.NO_IMAGE} />
                                                    </div>
                                                } 
                                            </div>
                                            }
                                            <div className="temp-desc">
                                                <div dangerouslySetInnerHTML={{ __html: item.message_body }}></div>
                                            </div>
                                            {
                                                (!_.isUndefined(item.header_image) && !_.isNull(item.body_image)) &&
                                                <div className="img-body-view">
                                                    <img
                                                        className="img-cover"
                                                        src={item.body_image ? NC.S3 + NC.PUSH_BODY + item.body_image : Images.NO_IMAGE} />
                                                </div>
                                            } 
                                            {/* <div className="temp-url">
                                                    <a href={item.message_url} target="_blank">
                                                        {item.message_url}
                                                    </a>
                                                </div> */}
                                        </div>
                                    </Col>
                                )
                            })
                            :
                            <div className="no-records mt-4">{NC.NO_RECORDS}</div>
                    }
                </Row>
                {Total > PERPAGE && (
                    <Row>
                        <Col md={12}>
                            <div className="custom-pagination float-right mb-5">
                                <Pagination
                                    activePage={CURRENT_PAGE}
                                    itemsCountPerPage={PERPAGE}
                                    totalItemsCount={Total}
                                    pageRangeDisplayed={5}
                                    onChange={e => this.handlePageChange(e)}
                                />
                            </div>
                        </Col>
                    </Row>
                )}
            </Fragment>
        )
    }

    toggleRecentCModal = (email_item) => {

        this.setState({
            communication_review_modal: !this.state.communication_review_modal,
            email_item: email_item
        }, () => {
            var email_body = this.state.email_item.email_body;
            email_body = email_body.replace("{{year}}", (new Date()).getFullYear());
            email_body = email_body.replace("{{SITE_TITLE}}", 'Fantasy Sports');
            this.setState({
                EmailBody: email_body,
                EmailSubject: this.state.email_item.subject,
            });
        });
    }

    emailTemplateModal = () => {
        let { EmailBody, EmailSubject, email_item } = this.state
        return (
            <Modal
                isOpen={this.state.communication_review_modal}
                toggle={() => this.toggleRecentCModal(email_item)}
                className={this.props.className}
                className="modal-md">
                <ModalHeader toggle={() => this.toggleRecentCModal(email_item)} className="promotion">
                    <h5 className="promotion title"> Preview</h5>
                </ModalHeader>
                <ModalBody>
                    <div className="popuppreviewtab">
                        <Row>
                            <Col sm="12" className="temptab">
                                <div className="subjecttemp mb-20">
                                    <text className="subject">Subject - {EmailSubject}</text>
                                </div>


                                <div dangerouslySetInnerHTML={{ __html: EmailBody }}>

                                </div>
                            </Col>
                        </Row>
                    </div>
                    <div className="templatepreview">

                    </div>
                </ModalBody>
            </Modal>
        )
    }

    renderEmailView = (template_data) => {
        return (
            <Fragment>
                <Row>
                    {
                        !_.isEmpty(template_data) ?
                            _.map(template_data, (item, idx) => {
                                return (
                                    !_.isEmpty(item.email_body) &&
                                    <Col md={4}>
                                        <div className="cus-temp-wrapper">
                                            <div className="temp-subject">Template subject : {item.subject}</div>
                                            <div className="text-center">
                                                <Button className="btn-secondary-outline" onClick={() => this.toggleRecentCModal(item)} >Preview</Button>
                                            </div>
                                        </div>
                                    </Col>
                                )
                            })
                            :
                            <div className="no-records mt-4">{NC.NO_RECORDS}</div>
                    }
                </Row>
            </Fragment>
        )
    }

    handleInputChange = (event) => {
        let name = event.target.name
        let value = event.target.value

        if (value.length > 2)
            this.setState({ CreatePosting: false })
        else
            this.setState({ CreatePosting: true })

        this.setState({ [name]: value }, () => {
            if (name === 'NotificationBody') {
                let bodyLength = value.length
                this.setState({ NotifyBodyLength: bodyLength })                
            }
        })
    }

    addCategoryToggle = () => {
        this.setState({ CategoryName: '' })
        this.setState({ addCateModalOpen: !this.state.addCateModalOpen })
    }

    addCategoryModal = () => {
        let { CreatePosting, CategoryName } = this.state
        return (
            <div>
                <Modal className="addmore-su-modal" isOpen={this.state.addCateModalOpen}
                    toggle={this.addCategoryToggle}>
                    <ModalBody>
                        <Row>
                            <Col md={12}>
                                <h3 className="h3-cls">Add Category</h3>
                            </Col>
                        </Row>
                        <Row>
                            <Col md={12}>
                                <label>Enter Category</label>
                                <Input
                                    maxLength="30"
                                    type="text"
                                    name="CategoryName"
                                    value={CategoryName}
                                    onChange={(e) => this.handleInputChange(e)}
                                />
                            </Col>
                        </Row>
                    </ModalBody>
                    <ModalFooter>
                        <Button className="btn-default-gray" onClick={this.addCategoryToggle}>Cancel</Button>
                        <Button className="btn-secondary-outline"
                            disabled={CreatePosting}
                            onClick={this.createCategory}>Add</Button>{' '}
                    </ModalFooter>
                </Modal>
            </div>
        )
    }

    createCategory = () => {
        let param = {
            "category_name": this.state.CategoryName
        }

        WSManager.Rest(NC.baseURL + MODULE_C.CREATE_NEW_CATEGORY, param).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.getAllCategory()
                this.addCategoryToggle()
                notify.show(ResponseJson.message, "success", 3000)
                this.setState({
                    CategoryName: '',
                    CreatePosting: false
                })
            } else {
                this.setState({ CreatePosting: false })
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            this.setState({ CreatePosting: false })
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    addSmsNotiToggle = () => {
        this.setState({
            SelectedSmsCategory: '',
            TemplateName: '',
            NotificationSub: '',
            NotificationBody: '',
            SelectedReditectTo: '',
            SmsUrl: ''
        })
        this.setState({ addSmsNotiModalOpen: !this.state.addSmsNotiModalOpen })
    }

    addSmsNotiModal = () => {
        let { CategoryListModal, NotifyTextLength, NotifyUrlLength, SmsUrl, SelectedReditectTo, NotifyBodyLength, NotificationBody, NotificationSub, TemplateName, SelectedSmsCategory, CategoryList, SavePosting, activeTab, NotiHeadImg, NotiBodyImg } = this.state
        return (
            <Modal
                isOpen={this.state.addSmsNotiModalOpen}
                className="modal-md cus-noti-popup"
                toggle={() => this.addSmsNotiToggle()}
            >
                <ModalHeader>CREATE NEW TEMPLATE</ModalHeader>
                <ModalBody>
                    <Row>
                        <Col md={12}>
                            <div className="h2-cls">
                                {/* {activeTab == "1" && "SMS"} */}
                                {activeTab == "2" && "Notification"}
                            </div>
                        </Col>
                    </Row>
                    <Row className="mt-1">
                        <Col md={6}>
                            <label htmlFor="ProofDesc">Select Category</label>
                            <Select
                                value={SelectedSmsCategory}
                                options={CategoryListModal}
                                onChange={(e) => this.handleFilter(e, 'SelectedSmsCategory')}
                            />
                        </Col>
                        <Col md={6}>
                            <label htmlFor="ProofDesc">Enter Template Name</label>
                            <Input
                                maxLength="40"
                                type="text"
                                name="TemplateName"
                                placeholder="Template Name"
                                value={TemplateName}
                                onChange={this.handleInputChange}
                            />
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <a className="pull-right mt-2" href="https://coolsymbol.com/emojis/emoji-for-copy-and-paste.html" target="_blank">Emoji Keyboard</a>
                            <div className="noti-input-box">
                                {
                                    activeTab == "2" &&
                                    <div className="noti-area">
                                        <Row>
                                            <Col md={10}>
                                                <Input
                                                    maxLength="50"
                                                    type="text"
                                                    name="NotificationSub"
                                                    value={NotificationSub}
                                                    className="subject-input"
                                                    placeholder="Header"
                                                    onChange={this.handleInputChange}
                                                />
                                            </Col>
                                            <Col md={2}>
                                                <div className="head-img-dashbox float-right">
                                                        {!_.isEmpty(NotiHeadImg) ?
                                                            <Fragment>
                                                            <i onClick={() => this.resetFile('NotiHeadImg')} className="icon-close"></i>
                                                                <img className="img-cover" src={NotiHeadImg} />
                                                            </Fragment>
                                                            :
                                                            <Fragment>
                                                                <input
                                                                    accept="image/x-png,
                                                                    image/jpeg,image/jpg"
                                                                    type="file"
                                                                    name='NotiHeadImg'
                                                                    id="NotiHeadImgName"
                                                                    className="head-img-inpt"
                                                                    onChange={this.onChangeImage}
                                                                />
                                                            <span className="head-img-txt">
                                                                Upload 
                                                                Image 
                                                                192*192
                                                            </span>
                                                            </Fragment>
                                                        }
                                                    </div>
                                            </Col>
                                        </Row>
                                    </div>
                                }
                                <div className={`temp-body-box ${activeTab === '2' ? ' temp-noti-sty' : ''}`}>
                                    <Input
                                        type="textarea"
                                        // maxLength={160 - NotifyUrlLength}
                                        maxLength={160}
                                        className="noti-body"
                                        name="NotificationBody"
                                        value={NotificationBody}
                                        placeholder="Body"
                                        onChange={this.handleInputChange}
                                    />
                                    {
                                        activeTab === '2' &&
                                        <div className="head-img-dashbox">
                                            {!_.isEmpty(NotiBodyImg) ?
                                                <Fragment>
                                                    <i onClick={() => this.resetFile('NotiBodyImg')} className="icon-close"></i>
                                                    <img className="img-cover" src={NotiBodyImg} />
                                                </Fragment>
                                                :
                                                <Fragment>
                                                    <input
                                                        accept="image/x-png,
                                                        image/jpeg,image/jpg"
                                                        type="file"
                                                        name='NotiBodyImg'
                                                        id="NotiBodyImgName"
                                                        className="head-img-inpt temp-body-box"
                                                        onChange={this.onChangeImage}
                                                    />
                                                <span className="head-img-txt font-lg">
                                                    Upload Image<br /> 720x240
                                                </span>
                                                </Fragment>
                                            }
                                        </div>
                                    }
                                </div>
                                
                                <span className="char-limit float-right">({NotifyBodyLength}/160 Characters)</span>
                            </div>
                        </Col>
                    </Row>
                    <Row>
                        {activeTab == "2" && <Col md={6}>
                            <label htmlFor="ProofDesc">Redirect To</label>
                            <Select
                                value={SelectedReditectTo}
                                options={MODULE_C.notification_landing_pages}
                                onChange={(e) => this.handleFilter(e, 'SelectedReditectTo')}
                            />
                        </Col>}
                    </Row>
                </ModalBody>
                <ModalFooter className="request-footer">
                    <Button
                        className="btn-secondary-outline ripple no-btn"
                        onClick={() => this.addSmsNotiToggle()}>Cancel</Button>
                    <Button
                        disabled={SavePosting}
                        onClick={this.saveTemplate}
                        className="btn-secondary-outline">Save</Button>
                </ModalFooter>
            </Modal>
        )
    }

    saveTemplate = () => {
        let { activeTab, SelectedSmsCategory, TemplateName, NotificationSub, NotificationBody, SelectedReditectTo, SmsUrl, NotiHeadImgName, NotiBodyImgName } = this.state
        if (_.isEmpty(SelectedSmsCategory) || _.isEmpty(TemplateName) || _.isEmpty(NotificationBody)) {
            notify.show("Pleae complete the template form", 'error', 5000)
            return false
        }

        if (activeTab == "2" && _.isEmpty(NotificationSub)) {
            notify.show("Pleae enetr notification subject", 'error', 5000)
            return false
        }
        this.setState({ SavePosting: true })
        let params = {
            "message_type": activeTab,
            "category_id": SelectedSmsCategory,
            "template_name": TemplateName,
            "message_body": NotificationBody,
            "message_url": SmsUrl,
        }

        if (activeTab == '2') {
            params.subject = NotificationSub
            params.redirect_to = SelectedReditectTo
            params.header_image = NotiHeadImgName
            params.body_image = NotiBodyImgName
        }
        
        WSManager.Rest(NC.baseURL + MODULE_C.CREATE_NEW_TEMPLATE, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    SelectedSmsCategory: '',
                    TemplateName: '',
                    NotificationSub: '',
                    NotificationBody: '',
                    SelectedReditectTo: '',
                    SmsUrl: '',
                    NotiHeadImgName: null,
                    NotiBodyImgName: null,
                    NotiHeadImg: null,
                    NotiBodyImg: null,
                })
                notify.show(Response.message, 'success', 5000)
                this.addSmsNotiToggle()
                this.getCustomTemplates()
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
            this.setState({ SavePosting: false })
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    onChangeImage = (event) => {

        if (event)
        {        
            let imgUrl = event.target.name
            let imgName = event.target.id        

            this.setState({
                [imgUrl]: URL.createObjectURL(event.target.files[0]),
                SavePosting: true
            });
            const file = event.target.files[0];
            if (!file) {
                return;
            }
            var data = new FormData();
            data.append("file", file);

            let apiURL = NC.HEADER_IMAGE
            if (imgUrl === 'NotiBodyImg')
            {
                apiURL = NC.BODY_IMAGE
            }

            WSManager.multipartPost(NC.baseURL + apiURL, data)
                .then(Response => {
                    if (Response.response_code == NC.successCode) {
                        this.setState({
                            [imgName]: Response.data.image_name,
                            SavePosting: false,
                        });
                    } else {
                        this.setState({
                            [imgUrl]: null,
                            SavePosting: false,
                        });
                    }
                }).catch(error => {
                    notify.show(NC.SYSTEM_ERROR, "error", 3000);
                });
        } else {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        }
    }

    resetFile = (n_flag) => {
        this.setState({
            [n_flag]: null,
            [n_flag + 'Name']: '',
        });
    }

    // getSmsDltTemplates = () => { 
    //     this.setState({ smsTblPosting : true })       
    //     getSmsTemplate({}).then((responseJson) => {
    //         if (responseJson.response_code === NC.successCode) {
    //             this.setState({
    //                 DltSmsTempList: responseJson.data,
    //                 DltTotalTemp: responseJson.data.length,
    //                 smsTblPosting: false
    //             });
    //         }
    //     }).catch((error) => {
    //         notify.show(NC.SYSTEM_ERROR, "error", 5000);
    //     })
    // }

    smsTempToggle = (item) => {
        if(!this.state.smsTempModalOpen)
        {
            this.setState({
                DltSmsItem: item,
                SmsTemplateName: item.name,
                DltTemplateId: item.dlt_template_id,
                DltMessage: item.message,
                DltSmsTempStatus: item.status,                
            })
        }      
        this.setState({
            smsTempModalOpen: !this.state.smsTempModalOpen
        })        
    }

    addDltSmsModal = () => {
        let { NotifyBodyLength, DltMessage, dltSmsPosting, activeTab, DltTemplateId, DltSmsTempStatus, SmsTemplateName } = this.state
        return (
            <Modal
                isOpen={this.state.smsTempModalOpen}
                className="modal-md cus-noti-popup"
                toggle={() => this.smsTempToggle()}
            >
                
                <ModalBody>
                    <Row className="mt-4">
                        <Col md={12}>
                            <label htmlFor="ProofDesc">Enter Template Name</label>
                            <Input
                                disabled={true}
                                maxLength="40"
                                type="text"
                                name="SmsTemplateName"
                                placeholder="Template Name"
                                value={SmsTemplateName}
                                onChange={this.handleInputChange}
                            />
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12} className="mt-3">
                            <label htmlFor="ProofDesc">DLT Template Id</label>
                            <Input
                                maxLength="50"
                                type="text"
                                name="DltTemplateId"
                                placeholder="DLT Template Id"
                                value={DltTemplateId}
                                onChange={this.handleDLTInput}
                            />
                        </Col>
                        <Col md={12}>
                            <a className="pull-right mt-2" href="https://coolsymbol.com/emojis/emoji-for-copy-and-paste.html" target="_blank">Emoji Keyboard</a>
                            <div className="noti-input-box">
                                <div className={`temp-body-box ${activeTab === '2' ? ' temp-noti-sty' : ''}`}>
                                    <Input
                                        type="textarea"
                                        // maxLength={160 - NotifyUrlLength}
                                        maxLength={160}
                                        className="noti-body"
                                        name="DltMessage"
                                        value={DltMessage}
                                        placeholder="Body"
                                        onChange={this.handleInputChange}
                                    />
                                </div>                                
                                <span className="char-limit float-right">({NotifyBodyLength}/160 Characters)</span>
                            </div>
                        </Col>
                        <Col md={12}>
                            <label htmlFor="ProofDesc">Template Status</label>
                            <ul className="radio-option-list">
                                <li className="radio-option-item">
                                    <div className="custom-radio">
                                        <input
                                            type="radio"
                                            className="custom-control-input"
                                            name="DltSmsTempStatus"
                                            value="1"
                                            checked={DltSmsTempStatus === '1'}
                                            onChange={this.handleDLTInput}
                                        />
                                        <label className="custom-control-label">
                                            <span className="input-text">Active</span>
                                        </label>
                                    </div>
                                </li>
                                <li className="radio-option-item">
                                    <div className="custom-radio">
                                        <input
                                            type="radio"
                                            className="custom-control-input"
                                            name="DltSmsTempStatus"
                                            value="0"
                                            checked={DltSmsTempStatus === '0'}
                                            onChange={this.handleDLTInput}
                                        />
                                        <label className="custom-control-label">
                                            <span className="input-text">Inactive</span>
                                        </label>
                                    </div>
                                </li>
                            </ul>
                        </Col>
                    </Row>
                </ModalBody>
                <ModalFooter className="request-footer">
                    <Button
                        className="btn-secondary-outline ripple no-btn"
                        onClick={() => this.smsTempToggle()}>Cancel</Button>
                    <Button
                        disabled={dltSmsPosting}
                        onClick={this.saveDltSmsTemplate}
                        className="btn-secondary-outline">Save</Button>
                </ModalFooter>
            </Modal>
        )
    }

    handleDLTInput = (event) => {
        if(event){
            let name = event.target.name
            let value = event.target.value    
            this.setState({ [name]: value })
        }else{
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        }
    }

    // saveDltSmsTemplate = () => {
    //     let { DltSmsItem, DltMessage, DltTemplateId, SmsTemplateName, DltSmsTempStatus } = this.state
    //     if (_.isEmpty(DltMessage)) {
    //         notify.show("Pleae enter messgae", 'error', 5000)
    //         return false
    //     }
        
    //     if (_.isEmpty(DltTemplateId)) {
    //         notify.show("Pleae enter dlt template id", 'error', 5000)
    //         return false
    //     }

    //     this.setState({ dltSmsPosting: true })
    //     let params = {
    //         "sms_template_id": DltSmsItem.sms_template_id,
    //         "name": SmsTemplateName,
    //         "dlt_template_id": DltTemplateId,
    //         "message": DltMessage,
    //         "reference_id": DltSmsItem.reference_id,
    //         "status": DltSmsTempStatus
    //     }

    //     updateSmsTemplate(params).then(Response => {
    //         if (Response.response_code == NC.successCode) {
    //             this.setState({
    //                 SmsTemplateName: '',
    //                 DltTemplateId: '',
    //                 DltMessage: '',
    //                 DltSmsTempStatus: '0',
    //                 smsTempModalOpen: false,
    //             })
    //             notify.show(Response.message, 'success', 5000)
    //             this.getSmsDltTemplates()
    //         } else {
    //             notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    //         }
    //         this.setState({ dltSmsPosting: false })
    //     }).catch(error => {
    //         notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    //     })
    // }

    render() {
        let { TemplateList, CategoryList, SelectedFilterCategory, activeTab, smsTempModalOpen, DltSmsTempList, DltTotalTemp, smsTblPosting } = this.state
        return (
            <Fragment>
                {this.addCategoryModal()}
                {this.addSmsNotiModal()}
                {this.emailTemplateModal()}
                {smsTempModalOpen && this.addDltSmsModal()}
                <div className="custom-template-main">
                    <Row className="cus-tem-header">
                        <Col lg={12}>
                            <h2 className="h2-cls float-left">Custom Templates</h2>
                            {(activeTab != '3' && activeTab != '1') && <div className="float-right">
                                <Button
                                    className='btn-secondary-outline float-right'
                                    onClick={() => { this.addSmsNotiToggle() }}>+ Create Template</Button>
                                <Button
                                    className='btn-secondary-outline float-right mr-4'
                                    onClick={() => { this.addCategoryToggle() }}>+ Create Category</Button>
                            </div>}
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <div className="user-navigation">
                                <Row>
                                    <Col md={12}>
                                        <Nav tabs>
                                            {/* <NavItem
                                                className={activeTab === '1' ? "active" : ""}
                                                onClick={() => { this.toggle('1'); }}
                                            >
                                                <NavLink>
                                                    SMS
                                            </NavLink>
                                            </NavItem> */}
                                            <NavItem
                                                className={activeTab === '2' ? "active" : ""}
                                                onClick={() => { this.toggle('2'); }}
                                            >
                                                <NavLink>
                                                    Notification
                                            </NavLink>
                                            </NavItem>
                                            <NavItem
                                                className={activeTab === '3' ? "active" : ""}
                                                onClick={() => { this.toggle('3'); }}
                                            >
                                                <NavLink>
                                                    Email
                                            </NavLink>
                                            </NavItem>
                                        </Nav>
                                    </Col>
                                </Row>
                                <div className="custem-bg-box">
                                    {
                                        activeTab != "1" &&
                                        <Row>
                                            <Col md={3}>
                                                <div className="select-categories">
                                                    <label htmlFor="categories">
                                                        Select Categories
                                                    </label>
                                                    <Select
                                                        value={SelectedFilterCategory}
                                                        options={CategoryList}
                                                        onChange={(e) => this.handleFilter(e, 'SelectedFilterCategory')}
                                                    />
                                                </div>
                                            </Col>
                                        </Row>
                                    }
                                    <TabContent activeTab={activeTab}>
                                        {
                                            // (activeTab == '1') &&
                                            // <TabPane tabId="1" className="animated fadeIn">
                                            //     <Row>
                                            //         <Col md={12} className="table-responsive common-table">
                                            //             <Table>
                                            //                 <thead>
                                            //                     <tr>
                                            //                         <th className="left-th">Template Name</th>
                                            //                         <th>Dlt Template ID</th>
                                            //                         <th>Body</th>
                                            //                         <th>Status</th>
                                            //                         <th className="right-th"></th>
                                            //                     </tr>
                                            //                 </thead>
                                            //                 {
                                            //                     DltTotalTemp > 0 ?
                                            //                     _Map(DltSmsTempList, (item, idx) => {
                                            //                             return (
                                            //                                 <tbody key={idx}>
                                            //                                     <tr>
                                            //                                         <td>{item.name ? item.name : '--'}</td>
                                            //                                         <td>{item.dlt_template_id ? item.dlt_template_id : '--'}</td>
                                            //                                         <td>
                                            //                                             <div className="sms-t-desc">
                                            //                                                 {item.message ? item.message : '--'}
                                            //                                             </div>
                                            //                                         </td>
                                            //                                         <td className={item.status == "1" ? 'text-green' : 'text-red'}>
                                            //                                             {item.status == '1' && 'Active'}
                                            //                                             {item.status == '0' && 'Inactive'}                                                                                        
                                            //                                         </td>
                                            //                                         <td>
                                            //                                             <Button 
                                            //                                                 className="btn-secondary dlt-e-btn"
                                            //                                                 onClick={() => this.smsTempToggle(item)}
                                            //                                             >Edit</Button>
                                            //                                         </td>
                                            //                                     </tr>
                                            //                                 </tbody>
                                            //                             )
                                            //                         })
                                            //                     :
                                            //                     <tbody>
                                            //                         <tr>
                                            //                             <td colSpan='22'>
                                            //                                 {(DltTotalTemp == 0 && !smsTblPosting) ?
                                            //                                     <div className="no-records">{NC.NO_RECORDS}</div>
                                            //                                     :
                                            //                                     <Loader />
                                            //                                 }
                                            //                             </td>
                                            //                         </tr>
                                            //                 </tbody>
                                            //                 }
                                            //             </Table>
                                            //         </Col>
                                            //     </Row>
                                            // </TabPane>
                                        }
                                        {
                                            (activeTab == '2') &&
                                            <TabPane tabId="2" className="animated fadeIn">
                                                {this.renderCommonBodyView(TemplateList)}
                                            </TabPane>
                                        }
                                        {
                                            (activeTab == '3') &&
                                            <TabPane tabId="3" className="animated fadeIn">
                                                {this.renderEmailView(TemplateList)}
                                            </TabPane>
                                        }
                                    </TabContent>
                                </div>
                            </div>
                        </Col>
                    </Row>
                </div>
            </Fragment>
        )
    }
}
export default CustomeTemplate