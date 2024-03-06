import React, { Component, Fragment } from "react";
import { Row, Col, Table, Input, Button,UncontrolledDropdown, DropdownToggle, DropdownMenu, DropdownItem } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import moment from 'moment';
import _ from 'lodash';
import ReactSummernote from 'react-summernote';
import 'react-summernote/dist/react-summernote.css';
import 'bootstrap/js/dist/modal';
import 'bootstrap/js/dist/dropdown';
import 'bootstrap/js/dist/tooltip';
import HF from '../../helper/HelperFunction';
import SelectDropdown from "../../components/SelectDropdown";
class CMS extends Component {
    constructor(props) {
        super(props)
        this.state = {
            PagesList: [],
            TotalRecord: 0,
            editView: false,
            EditPagePosting: false,
            TitleError: false,
            ContentError: false,
            PageTitle: '',
            PageContent: '',
            MetaKeyword: '',
            MetaDesc: '',
            LanguageType: 'en',
            PageId: '',
            languageOptions: HF.getLanguageData() ? HF.getLanguageData() : [],
            SummernoteView: true,
            ActionPosting: false,
        }
    }
    componentDidMount() {
        this.getPages()
    }

    getPages() {
        const { PERPAGE, CURRENT_PAGE } = this.state
        const param = {
            items_perpage: PERPAGE,
            total_items: 0,
            current_page: CURRENT_PAGE,
            sort_order: "ASC",
            // sort_field: "sort_order"
            sort_field: "page_title"
        }

        WSManager.Rest(NC.baseURL + NC.GET_PAGES, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({ PagesList: responseJson.data.result, TotalRecord: responseJson.data.total });
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    editContent(editViewFlag, PageId, page_alias) {
        if (page_alias === "contact_us")
        {
            this.props.history.push('/cms/about-us/' + PageId)
        }
        if (page_alias === "faq")
        {
            this.props.history.push('/cms/faq/' + PageId)
        }
        else{
            this.setState({
                editView: editViewFlag,
                PageId: PageId,
                SummernoteView: false
            }, () => {
                if (PageId)
                    this.getPageDetails()
            })
        }
    }

    getPageDetails() {
        const { LanguageType, PageId } = this.state
        const param = {
            page_id: PageId,
            language: LanguageType
        }
        WSManager.Rest(NC.baseURL + NC.GET_PAGE_DETAIL, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                let data = responseJson.data
                this.setState({
                    PageTitle: data.page_title ? data.page_title : '',
                    PageContent: data.page_content ? data.page_content : '',
                    MetaKeyword: data.meta_keyword ? data.meta_keyword : '',
                    MetaDesc: data.meta_desc ? data.meta_desc : '',
                    SummernoteView: true
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
    handleNameChange = (e) => {
        const name = e.target.name
        const value = e.target.value
        this.setState({
            [name]: value
        }, () => {
            this.validateForm()
        })
    }
    onContentChange = (value) => {
        this.setState({
            PageContent: value
        }, () => {
            this.validateForm()
        })
    }

    onImageUpload = (fileList) => {
        const reader = new FileReader();
        reader.onloadend = () => {
            ReactSummernote.insertImage(reader.result);
        }
        reader.readAsDataURL(fileList[0]);
    }

    validateForm = () => {
        let { PageTitle, PageContent } = this.state
        if (!PageTitle)
            this.setState({ TitleError: true })
        else
            this.setState({ TitleError: false })
        if (!PageContent)
            this.setState({ ContentError: true })
        else
            this.setState({ ContentError: false })

        if (PageTitle && PageContent)
            this.setState({ EditPagePosting: false })
        else
            this.setState({ EditPagePosting: true })
    }
    updatePage = () => {
        const { PageId, PageTitle, PageContent, MetaKeyword, MetaDesc, LanguageType } = this.state
        this.setState({ EditPagePosting: true })
        let param = {
            page_id: PageId,
            page_title: PageTitle,
            page_alias: PageTitle.replace(/ /g, "_"),
            meta_keyword: MetaKeyword,
            meta_desc: MetaDesc,
            page_url: PageTitle.replace(/ /g, "_"),
            page_content: PageContent,
            status: "1",
            modified_by: "0",
            added_date: moment().format("YYYY-MM-DD h:mm:ss"),
            modified_date: moment().format("YYYY-MM-DD h:mm:ss"),
            language: LanguageType
        }

        WSManager.Rest(NC.baseURL + NC.UPDATE_PAGE, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                notify.show(responseJson.message, "success", 5000);
                this.editContent(false)
                this.setState({ EditPagePosting: false }, function () {
                    this.getPages()
                })
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    markPinContest = (e, item) => {
        const { LanguageType } = this.state
        e.stopPropagation();
        if (window.confirm("Are you sure want to change status ?")) {
          this.setState({ posting: true,ActionPosting: true })
          if(item.status == 0){
            var status = 1;

          }else{
            var status = 0;

          }

          let params = { "status": status, page_id :item.page_id,language: LanguageType };
        //   console.log(params);
          WSManager.Rest(NC.baseURL + NC.UPDATE_PAGE_STATUS, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {         
    
              notify.show(responseJson.message, "success", 5000);
              this.setState({ posting: false,ActionPosting: false, }, function () {
                this.getPages()
            })
            } else {
              notify.show(responseJson.message, "error", 3000);
            }
            this.setState({ posting: false })
          })
        } else {
          return false;
        }
    }

    render() {
        const { languageOptions, PagesList, TotalRecord, editView, EditPagePosting, TitleError, ContentError, PageTitle, PageContent, LanguageType, SummernoteView,ActionPosting } = this.state
        const Select_Props = {
            is_disabled: false,
            is_searchable: true,
            is_clearable: false,
            menu_is_open: false,
            class_name: "",
            sel_options: languageOptions,
            place_holder: "Select Language",
            selected_value: LanguageType,
            modalCallback: this.handleLangChange
        }

        return (
            <Fragment>
                <div className="mt-4 app-banner">
                    <Row>
                        <Col md={12}>
                            <h1 className="h1-cls">Manage Pages</h1>
                        </Col>
                    </Row>
                    {!editView && (
                        <div className="cms-list-view">
                            <Row className="filters-box">
                                <Col md={12}>
                                    <div className="filters-area">
                                        <h4 className="m-0">Total Record Count : {TotalRecord}</h4>
                                    </div>
                                </Col>
                            </Row>
                            <Row className="animated fadeIn mt-4">
                                <Col md={12} className="table-responsive common-table">
                                    <Table>
                                        <thead>
                                            <tr>
                                                <th className="left-th pl-4">Title</th>
                                                <th>Alias</th>
                                                <th>Updated Date</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        {
                                            _.map(PagesList, (item, idx) => {
                                                return (
                                                    <tbody key={idx}>
                                                        <tr>
                                                            <td className="left-th pl-4">{item.page_title}</td>
                                                            <td>{item.page_alias}</td>
                                                            <td>
                                                                {HF.getFormatedDateTime(item.modified_date, 'D-MMM-YYYY hh:mm A')}
                                                                {/* {WSManager.getUtcToLocalFormat(item.modified_date, 'D-MMM-YYYY hh:mm A')} */}
                                                            </td>
                                                            {item.status == 1 ?<td className="width-200">Active</td>: <td className="width-200">Inactive</td> }

                                                            {/* <td><i onClick={() => this.editContent(true, item.page_id)} className="icon-edit cursor-pointer"></i></td> */}
                                                            <td>
                                                                <UncontrolledDropdown>
                                                                <DropdownToggle disabled={ActionPosting} className="icon-action" />
                                                                <DropdownMenu>
                                                                    {item.status == 1
                                                                        ?
                                                                        <DropdownItem onClick={(e) => this.markPinContest(e, item)}>Inactive</DropdownItem>
                                                                        :
                                                                        <DropdownItem onClick={(e) => this.markPinContest(e, item)}>Active</DropdownItem>
                                                                    }
                                                                    <DropdownItem onClick={() => this.editContent(true, item.page_id, item.page_alias)}>Edit</DropdownItem>
                                                                </DropdownMenu>
                                                                </UncontrolledDropdown>
                                                            </td>

                                                        </tr>
                                                    </tbody>
                                                )
                                            })
                                        }
                                    </Table>
                                </Col>
                            </Row>
                        </div>
                    )
                    }
                    {editView && (
                        <div className="cms-page-view">
                            <Row>
                                <Col md={12}>
                                    <div className="edit-title">Edit Page</div>
                                </Col>
                            </Row>
                            <div className="animated fadeIn new-banner mt-0">
                                <Col md={12} className="input-row">
                                    <Row>
                                        <Col md={3} className="b-input-label">Select Language<span className="asterrisk">*</span></Col>
                                        <Col md={9}>
                                            <SelectDropdown SelectProps={Select_Props} />
                                        </Col>
                                    </Row>
                                </Col>
                                <Col md={12} className="input-row">
                                    <Row>
                                        <Col md={3} className="b-input-label">Title<span className="asterrisk">*</span></Col>
                                        <Col md={9}>
                                            <Input
                                                type="text"
                                                name='PageTitle'
                                                placeholder="Title"
                                                onChange={this.handleNameChange}
                                                value={PageTitle}
                                            />
                                            {TitleError &&
                                                <span className="error-text">Title field can not be empty</span>
                                            }
                                        </Col>
                                    </Row>
                                </Col>
                                <Col md={12} className="input-row">
                                    <Row>
                                        <Col md={3} className="b-input-label">Content<span className="asterrisk">*</span></Col>
                                        <Col md={9}>
                                            <ReactSummernote
                                                value={SummernoteView ? PageContent : ''}
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
                                            {ContentError &&
                                                <span className="error-text">Content field can not be empty</span>
                                            }
                                        </Col>
                                    </Row>
                                </Col>
                                <Col md={12} className="banner-action">
                                    <Button disabled={EditPagePosting} className="btn-secondary mr-3" onClick={() => this.updatePage()}>Save Page</Button>
                                    <Button className="btn-secondary-outline" onClick={() => this.editContent(false)}>Cancel</Button>
                                </Col>
                            </div>
                        </div>
                    )
                    }
                </div>
            </Fragment >
        )
    }
}
export default CMS