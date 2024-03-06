import React, { Component, Fragment } from 'react';
import { Row, Col, Button, Input } from 'reactstrap';
import _ from 'lodash';
import * as NC from '../../helper/NetworkingConstants';
import Images from '../../components/images';
import { notify } from 'react-notify-toast';
import WSManager from '../../helper/WSManager';
import ActionRequestModal from '../../components/ActionRequestModal/ActionRequestModal';
import Pagination from "react-js-pagination";
import { MSG_DELETE_CATEGORY } from "../../helper/Message";
class PredictionCreateCategory extends Component {
    constructor(props) {
        super(props)
        this.state = {
            ListPosting: false,
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            CategoryList: [],
            ActionPopupOpen: false,
            CategoryNameMsg: true,
            CategoryImageNameMsg: true,
            CategoryName: ''
        }
    }

    componentDidMount() {
        this.getCategoryList()
    }

    handleInputChange = (event) => {
        let name = event.target.name
        let value = event.target.value
        this.setState({ [name]: value })
    }

    onChangeImage = (event) => {
        this.setState({
            CategoryImage: URL.createObjectURL(event.target.files[0]),
        });
        const file = event.target.files[0];
        if (!file) {
            return;
        }
        var data = new FormData();
        data.append("userfile", file);
        WSManager.multipartPost(NC.baseURL + NC.FIXED_OP_DO_UPLOAD, data)
            .then(Response => {
                if (Response.response_code == NC.successCode) {
                    this.setState({
                        CategoryImageName: Response.data.file_name
                    });
                } else {
                    this.setState({
                        CategoryImage: null
                    });
                }
            }).catch(error => {
                console.log("486");
                notify.show(NC.SYSTEM_ERROR, "error", 3000);
            });
    }

    editCategory = (item) => {
        this.setState({
            EditCategoryId: item.category_id,
            CategoryName: item.name,
            CategoryImageName: item.image,
            CategoryImage: NC.S3 + NC.OP_CATEGORY + item.image,
        },()=>{
            window.scrollTo({
                top:0,
                behavior:"smooth"
            })
        })
    }

    createCategory = () => {
        let { CategoryName, CategoryImageName, EditCategoryId } = this.state

        if (CategoryName.length <= 0 || CategoryName.trim().length <= 0) {
            this.setState({ CategoryNameMsg: false })
            return false
        }

        if (_.isUndefined(CategoryImageName) || _.isEmpty(CategoryImageName)) {
            this.setState({ CategoryImageNameMsg: false })
            return false
        }

        let params = {
            name: CategoryName,
            image: CategoryImageName
        }

        let CallUrl = NC.FIXED_OP_ADD_CATEGORY
        if (EditCategoryId) {
            params.category_id = EditCategoryId
            CallUrl = NC.FIXED_OP_UPDATE_CATEGORY
        }

        WSManager.Rest(NC.baseURL + CallUrl, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, 'success', 5000)
                this.setState({
                    CategoryName: '',
                    CategoryImage: '',
                    CategoryImageName: '',
                    CategoryNameMsg: true,
                    CategoryImageNameMsg: true,
                    EditCategoryId: ''
                },
                    this.getCategoryList
                )
            } else {
                
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    getCategoryList = () => {
        this.setState({ ListPosting: true })
        let { PERPAGE, CURRENT_PAGE } = this.state
        let params = {
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE
        }

        WSManager.Rest(NC.baseURL + NC.FIXED_OP_GET_ALL_CATEGORY, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    CategoryList: Response.data.category_list.result,
                    Total: Response.data.category_list.total,
                    ListPosting: false
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    resetFile = () => {
        this.setState({
            CategoryImage: null
        });
    }

    //function to toggle action popup
    toggleActionPopup = (deleteId, idx) => {
        this.setState({
            Message: MSG_DELETE_CATEGORY,
            idxVal: idx,
            CategoryID: deleteId,
            ActionPopupOpen: !this.state.ActionPopupOpen
        })
    }

    deleteCategory = () => {
        let { CategoryID, idxVal } = this.state
        let params = {
            category_id: CategoryID
        }
        let TempCategoryList = this.state.CategoryList
        WSManager.Rest(NC.baseURL + NC.FIXED_OP_DELETE_CATEGORY, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                _.remove(TempCategoryList, function (item, idx) {
                    return idx == idxVal
                })
                this.setState({ CategoryList: TempCategoryList })
                this.toggleActionPopup(CategoryID, idxVal)
                notify.show(Response.message, 'success', 5000)
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    resetChanges = () => {
        this.setState({
            CategoryName: '',
            CategoryImage: '',
            CategoryImageName: '',
            CategoryNameMsg: true,
            CategoryImageNameMsg: true,
            EditCategoryId: ''
        })
    }

    handlePageChange(current_page) {
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            this.getCategoryList()
        });
    }

    render() {
        let { Total, PERPAGE, CURRENT_PAGE, ActionPopupOpen, Message, CategoryList, CategoryImageNameMsg, CategoryImage, CategoryName, formValid, CategoryNameMsg, ListPosting } = this.state
        const ActionCallback = {
            Message: Message,
            modalCallback: this.toggleActionPopup,
            ActionPopupOpen: ActionPopupOpen,
            modalActioCallback: this.deleteCategory,
        }
        return (
            <Fragment>
                <div className="promotion-feedback op-create-category animated fadeIn">
                    <ActionRequestModal {...ActionCallback} />
                    <Row className="mt-4 op-heading">
                        <Col md={12}>
                            <div className="pre-heading float-left">Create Category</div>
                            <div onClick={() => this.props.history.push('/prize-open-predictor/category')} className="go-back float-right">{'<'} Back to Category</div>
                        </Col>
                    </Row>

                    
                    <div className="card-box-wrapper card-wrapper">
                        <Row>
                            <Col md={7}>
                                <div className="after-div">
                                    <div className="mb-20">
                                        <label htmlFor="CategoryName">Category Name</label>
                                        <Input
                                            maxLength={25}
                                            className="question-input"
                                            name="CategoryName"
                                            value={CategoryName}
                                            onChange={this.handleInputChange}
                                        />
                                        {!CategoryNameMsg &&
                                            <span className="color-red">
                                                Please enter valid category name.
                                            </span>
                                        }
                                    </div>
                                    <div className="redeem-box position-relative clearfix">
                                        <label htmlFor="Redeem">Select Image</label>
                                        <div className="select-image-box float-left">
                                            <div className="dashed-box">
                                                {!_.isEmpty(CategoryImage) ?
                                                    <Fragment>
                                                        <i onClick={this.resetFile} className="icon-close"></i>
                                                        <img className="img-cover" src={CategoryImage} />
                                                    </Fragment>
                                                    :
                                                    <Fragment>
                                                        <Input
                                                            accept="image/x-png,image/gif,image/jpeg,image/bmp,image/jpg"
                                                            type="file"
                                                            name='CategoryImage'
                                                            id="CategoryImage"
                                                            onChange={this.onChangeImage}
                                                        />
                                                        <img className="def-addphoto" src={Images.DEF_ADDPHOTO} alt="" />
                                                    </Fragment>
                                                }
                                            </div>
                                        </div>
                                        {!CategoryImageNameMsg &&
                                            <span className="color-red ml-3">
                                                Please select category image.
    </span>
                                        }

                                        <div className="publish-box float-right">
                                            <div onClick={() => this.resetChanges()} className="refresh icon-reset"></div>
                                            <Button
                                                className="btn-secondary-outline publish-btn"
                                                onClick={this.createCategory}
                                            >Submit</Button>
                                        </div>
                                    </div>
                                </div>
                            </Col>
                            <Col md={5}>
                                <div className={`img-preview-box prediction-dashboard ${(!_.isEmpty(CategoryImage) || !_.isEmpty(CategoryName)) ? ' dot-border' : ''}`}>
                                    <Fragment>
                                        {(!_.isEmpty(CategoryImage) || !_.isEmpty(CategoryName)) ?
                                            (!_.isEmpty(CategoryImage) || CategoryName.trim().length) > 0 ?
                                            <div className="category-card">
                                                <img src={CategoryImage} alt="" className="cat-img" />
                                                <div className="cat-info-box">
                                                    <div className="cat-title">{CategoryName}</div>
                                                </div>
                                            </div>
                                            :
                                            ''
                                            :
                                            <span className="preview-text">Your Preview will<br /> appear here</span>
                                        }
                                    </Fragment>
                                </div>
                            </Col>
                        </Row>
                    </div>
                    
                    <div className="category-list-box">
                        <Row>
                            {
                                _.map(CategoryList, (item, idx) => {
                                    return (
                                        <Col md={3} key={idx} className="pr-0 mb-3">
                                            <div className="category-card">
                                                <img src={NC.S3 + NC.OP_CATEGORY + item.image} alt="" className="cat-img" />
                                                <div className="icons-box">
                                                    <i onClick={() => this.editCategory(item)} className="icon-edit"></i>
                                                    <i onClick={() => this.toggleActionPopup(item.category_id, idx)}
                                                        className="icon-delete"></i>
                                                </div>
                                                <div className="cat-info-box">
                                                    <div className="cat-title">{item.name}</div>
                                                </div>
                                            </div>
                                        </Col>
                                    )
                                })
                            }
                        </Row>
                    </div>
                    <Row>
                        <Col md={12}>
                            {Total > PERPAGE && (
                                <div className="custom-pagination float-right">
                                    <Pagination
                                        activePage={CURRENT_PAGE}
                                        itemsCountPerPage={PERPAGE}
                                        totalItemsCount={Total}
                                        pageRangeDisplayed={5}
                                        onChange={e => this.handlePageChange(e)}
                                    />
                                </div>
                            )
                            }
                        </Col>
                    </Row>
                </div>
            </Fragment>
        )
    }
}

export default PredictionCreateCategory
