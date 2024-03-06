import React, { Component } from "react";
import { Input, Row, Col, Button } from "reactstrap";
import Images from "../../components/images";
import _  from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import { editLeague } from '../../helper/WSCalling';
import Select from 'react-select';
import moment from 'moment';
import ReactSummernote from 'react-summernote';
import 'react-summernote/dist/react-summernote.css';
import 'bootstrap/js/dist/modal';
import 'bootstrap/js/dist/dropdown';
import 'bootstrap/js/dist/tooltip';
class AboutUs extends Component {    
    constructor(props) {
        super(props)
        this.state = {
            page_id: (this.props.page_id) ? this.props.page_id : this.props.match.params.page_id,
            LanguageType: 'en',
            Posting : false, 
            EmailMsg : true, 
            PhoneMsg : true,             
            PhoneTwoMsg : true,             
            FacebookMsg : true, 
            TwitterMsg: true,
            InstagramMsg: true,
            LinkedInMsg: true,
            WatsappMsg: true,  
            languageOptions: [],
            fileObj : [],
            fileArray : [],    
            newPhotoArr : [],    
            deletePosting: true,      
            InpCustomData: { "email": "", "phone1": "", "phone2": "", "photos": [], "address": "", "linkdin": "", "twitter": "", "whatsapp": "", "facebook": "", "instagram": "", "Description": "" },    
        }
    }

    componentDidMount = () => {
        if (!_.isEmpty(this.state.page_id)) 
        {
            this.getLanguage()
            this.getPageDetails()
        }     
    }

    getLanguage() {
        WSManager.Rest(NC.baseURL + NC.GET_LANGUAGE_LIST, {}).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                let ResponseData = responseJson.data.language_list
                let TempLang = []
                let TempLangDict = {}
                _.map(ResponseData, (language, idx) => {
                    TempLangDict = {
                        "label": language,
                        "value": idx,
                    }
                    TempLang.push(TempLangDict)
                })
                this.setState({
                    languageOptions: TempLang
                })
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    getPageDetails() {
        let { LanguageType, page_id, InpCustomData } = this.state
        let param = {
            page_id: page_id,
            language: LanguageType
        }
        WSManager.Rest(NC.baseURL + NC.GET_PAGE_DETAIL, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                let data = responseJson.data
                this.setState({
                    PageTitle: data.page_title ? data.page_title : '',
                    Description: data.page_content ? data.page_content : '',
                    MetaKeyword: data.meta_keyword ? data.meta_keyword : '',
                    MetaDesc: data.meta_desc ? data.meta_desc : '',
                    CustomData: (!_.isNull(data.custom_data) && !_.isUndefined(data.custom_data)) ? data.custom_data : InpCustomData
                },()=>{
                        
                    
                });
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }
    
    handleLangChange = (value) => {
        this.setState({ LanguageType: value.value }, () => {
            this.getPageDetails()
        })
    }
    
    handleInput = (e) => {
        let tempCustomData = this.state.CustomData
        let name = e.target.name
        let value = e.target.value
        tempCustomData[name] = value        
        this.setState({ 
            Posting: false,
            [name]: value, 
            CustomData: tempCustomData, 
        }, 
            // () => this.validateForm(name, value)
            )
    } 
    
    uploadMultipleFiles = (e) => {
        this.setState({ Posting: true, deletePosting: false, newPhotoArr : [] })
        let fileObj = []
        let fileArray = []
        fileObj.push(e.target.files)

        var data = new FormData();

        for (let i = 0; i < fileObj[0].length; i++) {
            fileArray.push(URL.createObjectURL(fileObj[0][i]))
            data.append('file[' + i + ']', fileObj[0][i]);
        }        
        
        this.setState({ 
            // file: fileArray 
            fileArray: fileArray
        },()=>{            
            WSManager.multipartPost(NC.baseURL + NC.UPLOAD_ABOUT_US, data)
                .then(Response => {
                    if (Response.response_code == NC.successCode) {
                        if (_.isEmpty(Response.data))
                        {
                            notify.show("Something went wrong due to more images uploaded.Please try again", "error", 5000);
                            this.setState({ fileArray : [] })
                        }else{
                            this.setState({
                                newPhotoArr : Response.data,
                                PrfImgPosting: false,
                            });
                        }
                        this.setState({ PrfImgPosting: false });
                    }
                    this.setState({ Posting: false, deletePosting: true, fileArray : [] });
                }).catch(error => {
                    notify.show(NC.SYSTEM_ERROR, "error", 3000);
                });          
        })        
    }

    removePhoto = (temData, photo_name) => {
        var index = temData.indexOf(photo_name);
        temData.splice(index, 1);
    }

    deletePhoto = (photo_name, flag) => {
        this.setState({ deletePosting: false })
        let { CustomData, newPhotoArr } = this.state              

        let params = { 
            "remove_image_post": photo_name 
        }    

        WSManager.Rest(NC.baseURL + NC.REMOVE_IMAGE, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                if (flag === 1) {
                    this.removePhoto(CustomData.photos, photo_name)
                } else {
                    this.removePhoto(newPhotoArr, photo_name)
                }
            }

        this.setState({ deletePosting: true })

        }).catch(error => {
            this.setState({ deletePosting: true })
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    addAboutUs = () => {
        let { fileSave, page_id, Description, PageTitle, MetaKeyword, MetaDesc, LanguageType, CustomData } = this.state
        this.setState({
            EmailMsg: true,
            PhoneMsg: true,
            PhoneTwoMsg: true,
            FacebookMsg: true,
            TwitterMsg: true,
            InstagramMsg: true,
            LinkedInMsg: true,
            WatsappMsg: true,
        })
        if (!_.isEmpty(CustomData.email) && !CustomData.email.match(/^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/)) {
            this.setState({
                EmailMsg: false,
            })
            return false
        }

        if (!_.isEmpty(CustomData.phone1) && CustomData.phone1.length < 8 && !CustomData.phone1.match(/^[0-9]$/)) {
            this.setState({
                PhoneMsg: false
            })
            return false
        }

        if (!_.isEmpty(CustomData.phone2) && CustomData.phone2.length < 8 && !CustomData.phone2.match(/^[0-9]$/)) {
            this.setState({
                PhoneTwoMsg: false
            })
            return false
        }

        if (!_.isEmpty(CustomData.facebook) && !CustomData.facebook.match(/^^https?:\/\/(.*)?$/gm)) {
            this.setState({
                FacebookMsg: false
            })
            return false;
        }

        if (!_.isEmpty(CustomData.twitter) && !CustomData.twitter.match(/^^https?:\/\/(.*)?$/gm)) {
            this.setState({
                TwitterMsg: false
            })
            return false;
        }

        if (!_.isEmpty(CustomData.instagram) && !CustomData.instagram.match(/^^https?:\/\/(.*)?$/gm)) {
            this.setState({
                InstagramMsg: false
            })
            return false;
        }

        if (!_.isEmpty(CustomData.linkedin) && !CustomData.linkedin.match(/^^https?:\/\/(.*)?$/gm)) {
            this.setState({
                LinkedInMsg: false
            })
            return false;
        }

        if (!_.isEmpty(CustomData.whatsapp) && CustomData.whatsapp.length < 8 && !CustomData.whatsapp.match(/^[0-9]$/)) {
            this.setState({ WatsappMsg: false })
            return false;
        }


        let tData = CustomData
        if (_.isUndefined(CustomData.photos)) {
            tData.photos = this.state.newPhotoArr
        }
        else if (_.isUndefined(this.state.newPhotoArr)) {
            tData.photos = CustomData.photos
        }
        else {
            tData.photos = [...CustomData.photos, ...this.state.newPhotoArr]
            this.setState({ newPhotoArr : [] })
        }

        let param = {
            page_id: page_id,
            page_title: PageTitle,
            page_alias: PageTitle.replace(/ /g, "_"),
            meta_keyword: MetaKeyword,
            meta_desc: MetaDesc,
            page_url: PageTitle.replace(/ /g, "_"),
            page_content: Description,
            status: "1",
            modified_by: "0",
            added_date: moment().format("YYYY-MM-DD h:mm:ss"),
            modified_date: moment().format("YYYY-MM-DD h:mm:ss"),
            language: LanguageType,
            custom_data: CustomData
        }

        this.setState({ Posting: true })
        WSManager.Rest(NC.baseURL + NC.UPDATE_PAGE, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                notify.show(responseJson.message, "success", 5000);
                this.props.history.push('/cms/cms/')
            }
            this.setState({ posting: false })
        });
    }
    
    onContentChange = (value) => {
        this.setState({
            Description: value
        })
    }

    render() {
        let { newPhotoArr, deletePosting, file, languageOptions, LanguageType, Description, Posting, EmailMsg, PhoneMsg, PhoneTwoMsg, FacebookMsg, TwitterMsg, InstagramMsg, LinkedInMsg, WatsappMsg, ProofImage, CustomData } = this.state
        return (
            <div className="about-us-box">
                <div className="about-heading">About Us</div>
                <div className="au-bg-box">
                    <div className="au-desc w-25">
                        <div className="au-title">Language</div>
                        <Select
                            isSearchable={true}
                            class="form-control"
                            options={languageOptions}
                            placeholder="Select Language"
                            value={LanguageType}
                            onChange={e => this.handleLangChange(e)}
                        />
                    </div>
                    <div className="au-desc mt-3">
                        <div className="au-title">Description</div>
                        <ReactSummernote
                            // value={SummernoteView ? PageContent : ''}
                            value={Description}
                            onChange={this.onContentChange}
                            onImageUpload={this.onImageUpload}
                            options={{
                                height: 250,
                                toolbar: [
                                    ['color', ['color']],
                                    ['style', ['style']],
                                    ['font', ['bold', 'underline', 'clear']],
                                    ['fontname', ['fontname']],
                                    ['para', ['ul', 'ol', 'paragraph']],
                                    ['table', ['table']],
                                    ['insert', ['link', 'picture']],
                                    ['view', ['codeview']]
                                ]
                            }}

                        />
                        {/* <Input
                            type="textarea"
                            placeholder="Write something here"
                            name="Description"
                            value={Description}
                            onChange={this.handleInput}
                        /> */}
                    </div>
                    <div className="au-avatar mt-56">
                        <div className="au-title">Upload Photos <span className="help-text">(You can upload upto 10 images at once, Max 2 MB Each)</span></div>
                        <Row>
                            <Col md={12}>
                                <div className="photos-view-box">
                                    <div className="photos-view add-photo-box">
                                        <div className="add-photo-content">
                                            <i className="icon-plus"></i><br />
                                            <span className="au-add-photos">Add Photos</span>
                                            <Input
                                                accept="image/x-png,
                                                image/jpeg,image/jpg"
                                                type="file"
                                                name='Photos[]'
                                                id="Photos"
                                                className="add-photo-in"
                                                // onChange={this.onChangeImage}
                                                onChange={this.uploadMultipleFiles} 
                                                multiple
                                            />
                                        </div>
                                    </div>
            {
                ((!_.isEmpty(CustomData) && !_.isUndefined(CustomData.photos))) &&
                        CustomData.photos.map((item, idx) => { return(
                            <div key={idx} className="photos-view">
                            {deletePosting && <i 
                                    onClick={() => this.deletePhoto(item,1)}
                            className="icon-delete"></i>}
                            <img
                                    src={NC.S3 + NC.ABOUT_US_IMG + item} className="img-cover"
                                alt=""
                            />
                        </div>
                    )
                }
                )
            }
            {
                // ((!_.isEmpty(newPhotoArr) && !_.isUndefined(newPhotoArr))) &&
                ((!_.isEmpty(newPhotoArr) && !_.isUndefined(newPhotoArr))) ?
                        newPhotoArr.map((item, idx) => { 
                            return(
                                <div key={idx} className="photos-view">
                                        {deletePosting && 
                                            <i 
                                                onClick={() => this.deletePhoto(item, 2)}
                                                className="icon-delete"></i>}
                                            <img
                                                src={NC.S3 + NC.ABOUT_US_IMG + item} className="img-cover"
                                            alt=""
                                        />
                                </div>
                             )
                    }
                    )
                    :
                    this.state.fileArray.map((item, idx) => { 
                            return(
                                <div key={idx} className="photos-view">
                                    <span className="spinner-border"></span>
                                    <span className="image-loading">Loading...</span>
                                </div>
                    )
                    }
                )
                   
            }
            {/* {
                ((!_.isEmpty(this.state.fileArray) && !_.isUndefined(this.state.fileArray))) &&
                        this.state.fileArray.map((item, idx) => { 
                            return(
                            <div key={idx} className="photos-view">
                            {
                                deletePosting && 
                                <i 
                                    onClick={() => this.deletePhoto(item, 2)}
                                    className="icon-delete"></i>
                            }
                                <img
                                    src={item} className="img-cover"
                                alt="" />
                        </div>
                    )
                }
                )
            } */}
                                </div>
                            </Col>
                        </Row>
                    </div>
                    <div className="au-contact-d mt-56">
                        <div className="au-title">Contact Details</div>
                        <Row>
                            <Col md={4}>
                                <label htmlFor="email">Email Address</label>
                                <Input
                                    type="text"
                                    name="email"
                                    value={!_.isEmpty(CustomData) ? CustomData.email : ''}
                                    onChange={this.handleInput}
                                />
                                {!EmailMsg &&
                                    <span className="color-red">Please enter valid email</span>
                                }
                            </Col>
                            <Col md={4}>
                                <label htmlFor="phone1">Phone Number</label>
                                <Input
                                    type="text"
                                    name="phone1"
                                    value={!_.isEmpty(CustomData) ? CustomData.phone1 : ''}
                                    onChange={this.handleInput}
                                />
                                {!PhoneMsg &&
                                    <span className="color-red">Please enter valid phone number</span>
                                }
                            </Col>
                            <Col md={4}>
                                <label htmlFor="email">Phone Number 2</label>
                                <Input
                                    type="text"
                                    name="phone2"
                                    value={!_.isEmpty(CustomData) ? CustomData.phone2 : ''}
                                    onChange={this.handleInput}
                                />
                                {!PhoneTwoMsg &&
                                    <span className="color-red">Please enter valid phone number</span>
                                }
                            </Col>
                        </Row>
                        <Row className="mt-5">
                            <Col md={4}>
                                <label htmlFor="email">Address</label>
                                <Input
                                    type="textarea"
                                    name="address"
                                    value={!_.isEmpty(CustomData) ? CustomData.address : ''}
                                    onChange={this.handleInput}
                                />
                            </Col>
                        </Row>
                    </div>
                    <div className="au-contact-d mt-4">
                        <div className="au-title">Social Media Platform</div>
                        <Row>
                            <Col md={4}>
                                <label htmlFor="Facebook">Facebook</label>
                                <Input
                                    type="url"
                                    name="facebook"
                                    placeholder="share link here"
                                    value={!_.isEmpty(CustomData) ? CustomData.facebook : ''}
                                    onChange={this.handleInput}
                                />
                                {!FacebookMsg &&
                                    <span className="color-red">Please enter valid facebook URL</span>
                                }
                            </Col>
                            <Col md={4}>
                                <label htmlFor="Twitter">Twitter</label>
                                <Input
                                    type="url"
                                    name="twitter"
                                    placeholder="share link here"
                                    value={!_.isEmpty(CustomData) ? CustomData.twitter : ''}
                                    onChange={this.handleInput}
                                />
                                {!TwitterMsg &&
                                    <span className="color-red">Please enter valid twitter URL</span>
                                }
                            </Col>
                            <Col md={4}>
                                <label htmlFor="Instagram">Instagram</label>
                                <Input
                                    type="url"
                                    name="instagram"
                                    placeholder="share link here"
                                    value={!_.isEmpty(CustomData) ? CustomData.instagram : ''}
                                    onChange={this.handleInput}
                                />
                                {!InstagramMsg &&
                                    <span className="color-red">Please enter valid instagram URL</span>
                                }
                            </Col>
                        </Row>
                        <Row className="mt-4">
                            <Col md={4}>
                                <label htmlFor="LinkedIn">LinkedIn</label>
                                <Input
                                    type="text"
                                    name="linkdin"
                                    placeholder="share link here"
                                    value={!_.isEmpty(CustomData) ? CustomData.linkdin : ''}
                                    onChange={this.handleInput}
                                />
                                {!LinkedInMsg &&
                                    <span className="color-red">Please enter valid linkedin URL</span>
                                }
                            </Col>
                            <Col md={4}>
                                <label htmlFor="Watsapp">Whatsapp</label>
                                <Input
                                    type="text"
                                    name="whatsapp"
                                    // placeholder="share link here"
                                    value={!_.isEmpty(CustomData) ? CustomData.whatsapp : ''}
                                    onChange={this.handleInput}
                                />
                                {!WatsappMsg &&
                                    <span className="color-red">Please enter valid whatsapp number</span>
                                }
                            </Col>
                        </Row>
                    </div>
                    <div className="submit-form">
                        <Button
                            className="btn-secondary-outline"
                            disabled={Posting}
                            onClick={this.addAboutUs}
                        >
                            Submit
                        </Button>
                    </div>
                </div>
            </div>
        )
    }
}
export default AboutUs