import React, { Component, Fragment } from "react";
import { Row, Tooltip, Col, Table, Input, Button, UncontrolledDropdown, DropdownToggle, DropdownMenu, DropdownItem, Modal, ModalHeader, ModalBody, ModalFooter } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import _ from 'lodash';
import Pagination from "react-js-pagination";
export default class AppBanner extends Component {
    constructor(props) {
        super(props)
        this.toggle = this.toggle.bind(this);

        this.state = {
            TotalBanner: 0,
            tooltipOpen: false,
            PERPAGE: 5,
            CURRENT_PAGE: 1,
            NewBannerToggle: false,
            title: '',
            link: '',
            fileUplode: '',
            fileName: '',
            validURL: false,
            dropdownOpen: false,
            ActionPosting: false,
            DeleteActionPosting: false,
            DeleteModalOpen: false
        }
    }
    componentDidMount() {
        this.getAppBanner()
    }
    toggle() {
        this.setState({
            tooltipOpen: !this.state.tooltipOpen
        });
    }

    getAppBanner() {
        const { PERPAGE, CURRENT_PAGE } = this.state
        const param = {
            items_perpage: PERPAGE,
            total_items: 0,
            current_page: CURRENT_PAGE,
            sort_order: "DESC",
            sort_field: "AB.created_date",
            AppBannersList: []
        }

        WSManager.Rest(NC.baseURL + NC.GET_APP_BANNERS, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({
                    AppBannersList: responseJson.data.result,
                    TotalBanner: responseJson.data.total
                });
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    NewBannerTogle(flag) {
        this.setState({ NewBannerToggle: flag })
    }

    handleNameChange = e => {
        const name = e.target.name;
        const value = e.target.value;
        this.setState({ [name]: value })
        if (name == "link" && !value.match(/^(?:http(s)?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:/?#[\]@!\$&'\(\)\*\+,;=.]+$/)) {
            this.setState({
                validURL: true
            })
        } else {
            this.setState({
                validURL: false
            }, function () {
                this.validateForm()
            })
        }
    }

    onChangeImage = (event) => {
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
        data.append("userfile", file);
        WSManager.multipartPost(NC.baseURL + NC.APP_BANNER_UPLOAD, data)
            .then(responseJson => {
                this.setState({
                    fileUplode: responseJson.data.image_url,
                    ImageName: responseJson.data.file_name
                });
            }).catch(error => {
                notify.show(NC.SYSTEM_ERROR, "error", 3000);
            });
    }
    resetFile = (event) => {
        event.preventDefault();
        this.setState({
            fileName: null,
            fileUplode: null,
        }, function () {
            this.validateForm()
        });
    }

    validateForm() {
        const { title, link, fileName } = this.state
        this.setState({ AddBannerPosting: false })
        if ((!_.isEmpty(title)) && (!_.isEmpty(link) && !_.isEmpty(fileName))) {
            this.setState({ AddBannerPosting: true })
        }
    }

    createBanner() {
        const { title, link, fileUplode, ImageName } = this.state
        this.setState({ AddBannerPosting: false })
        let param = {
            banner_title: title,
            banner_link: link,
            banner_image: fileUplode,
            is_preview: 1,
            is_remove: 1,
            uploadbtn: 0,
            image_name: ImageName,
            size_tip: ""
        }
        WSManager.Rest(NC.baseURL + NC.ADD_APP_BANNER, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                notify.show(responseJson.message, "success", 3000);
                this.setState({
                    link: null,
                    title: null,
                    fileUplode: '',
                    fileName: '',
                    AddBannerPosting: true
                }, function () {
                    this.NewBannerTogle(false)
                    this.getAppBanner()
                });
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        })
    }
    handlePageChange(current_page) {
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            this.getAppBanner();
        });
    }
    updateBannerStatus = (idx, app_banner_id, status) => {
        this.setState({ ActionPosting: true })
        let tempBannerList = this.state.AppBannersList
        const param = {
            status: status,
            app_banner_id: app_banner_id
        }
        WSManager.Rest(NC.baseURL + NC.UPDATE_STATUS, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                notify.show(responseJson.message, "success", 5000);
                tempBannerList.map((item, index) => {
                    tempBannerList[index].status = 0
                    this.setState({ tempBannerList })
                })
                tempBannerList[idx].status = status
                this.setState({
                    AppBannersList: tempBannerList,
                    ActionPosting: false
                })
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    deleteToggle = (setFalg, idx, item,) => {
        if (setFalg) {
            this.setState({
                deleteIndex: idx,
                deleteItem: item,
            })
        }
        this.setState(prevState => ({
            DeleteModalOpen: !prevState.DeleteModalOpen
        }));
    }

    deleteAppBanner = () => {
        const { deleteIndex, deleteItem, AppBannersList } = this.state
        this.setState({ DeleteActionPosting: true })
        const param = {
            app_banner_id: deleteItem.app_banner_id,
            banner_title: deleteItem.title,
            banner_image: "",
            banner_link: deleteItem.link,
            status: "0",
            image_url: ""
        }

        let tempBannerList = AppBannersList
        WSManager.Rest(NC.baseURL + NC.DELETE_APP_BANNER, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                _.remove(tempBannerList, function (item, idx) {
                    return idx == deleteIndex
                })
                this.deleteToggle(false, {}, {})
                notify.show(responseJson.message, "success", 5000);
                this.setState({
                    AppBannersList: tempBannerList,
                    DeleteActionPosting: false
                })
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }


    render() {
        const { NewBannerToggle, AddBannerPosting, validURL, title, link, AppBannersList, CURRENT_PAGE, TotalBanner, PERPAGE, ActionPosting, DeleteActionPosting } = this.state
        return (
            <Fragment>
                {
                    !NewBannerToggle &&
                    <div className="mt-4 app-banner">
                        <Row>
                            <Col md={12}>
                                <h1 className="h1-cls">App Banner</h1>
                            </Col>
                        </Row>
                        <Row className="filters-box">
                            <Col md={12}>
                                <div className="filters-area">
                                    <Button className="btn-secondary" onClick={() => this.NewBannerTogle(true)}>New App Banner</Button>
                                </div>
                                <div className='info-banner-2'>
                                    <i className="icon-info info-icon-banner" id="TooltipExample"></i>
                                    <Tooltip placement="bottom" isOpen={this.state.tooltipOpen} target="TooltipExample" toggle={this.toggle}>
                                            Will be displayed on app whenever users logged in
                                    </Tooltip>
                                </div>
                            </Col>
                        </Row>
                        <Row className="animated fadeIn">
                            <Col md={12} className="table-responsive common-table">
                                <Table>
                                    <thead>
                                        <tr>
                                            <th className="left-th pl-4">Title</th>
                                            <th>Image</th>
                                            <th>Link</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    {
                                        _.map(AppBannersList, (item, idx) => {
                                            return (
                                                <tbody key={idx}>
                                                    <tr>
                                                        <td className="pl-4">{item.banner_title}</td>
                                                        <td>
                                                            <figure className="app-banner-img">
                                                                <img src={item.image_url} className="img-cover" alt="" />
                                                            </figure>
                                                        </td>
                                                        <td>{item.banner_link}</td>
                                                        <td>
                                                            {item.status == 1 ?
                                                                <i className="icon-verified active"></i>
                                                                :
                                                                <i className="icon-inactive"></i>
                                                            }
                                                        </td>
                                                        <td>
                                                            <UncontrolledDropdown>
                                                                <DropdownToggle disabled={ActionPosting} className="icon-action" />
                                                                <DropdownMenu>
                                                                    {item.status == 1
                                                                        ?
                                                                        <DropdownItem onClick={() => this.updateBannerStatus(idx, item.app_banner_id, 0)}>Inactive</DropdownItem>
                                                                        :
                                                                        <DropdownItem onClick={() => this.updateBannerStatus(idx, item.app_banner_id, 1)}>Active</DropdownItem>
                                                                    }
                                                                    <DropdownItem onClick={() => { this.deleteToggle(true, idx, item) }}>Delete</DropdownItem>
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
                        {TotalBanner > PERPAGE &&
                            <div className="custom-pagination userlistpage-paging float-right">
                                <Pagination
                                    activePage={CURRENT_PAGE}
                                    itemsCountPerPage={PERPAGE}
                                    totalItemsCount={TotalBanner}
                                    pageRangeDisplayed={5}
                                    onChange={e => this.handlePageChange(e)}
                                />
                            </div>}
                    </div>
                }
                {
                    NewBannerToggle &&
                    <div className="mt-4">
                        <Row>
                            <Col md={12}>
                                <h1 className="h1-cls">New Banner</h1>
                            </Col>
                        </Row>
                        <div className="animated fadeIn new-banner">
                            <Col md={12} className="input-row">
                                <Row>
                                    <Col md={3} className="b-input-label">Title<span className="asterrisk">*</span></Col>
                                    <Col md={9}>
                                        <Input
                                            type="text"
                                            name='title'
                                            placeholder="Title"
                                            onChange={this.handleNameChange}
                                            value={title}
                                        />
                                    </Col>
                                </Row>
                            </Col>
                            <Col md={12} className="input-row">
                                <Row>
                                    <Col md={3} className="b-input-label">Link<span className="asterrisk">*</span></Col>
                                    <Col md={9}>
                                        <Input
                                            type="text"
                                            name='link'
                                            placeholder="Target Url"
                                            onChange={this.handleNameChange}
                                            value={link}
                                        />
                                        {validURL &&
                                            <span className="error-text">Please upload valid Link</span>
                                        }
                                    </Col>
                                </Row>
                            </Col>
                            <Col md={12} className="input-row">
                                <Row>
                                    <Col md={3} className="b-input-label">Image<span className="asterrisk">*</span> PNG</Col>
                                    <Col md={9}>
                                        <Input
                                            type="file"
                                            name='banner_image'
                                            onChange={this.onChangeImage}
                                        />
                                        {this.state.fileName && (
                                            <div>
                                                <Button className="btn-secondary mt-4 mb-3" onClick={this.resetFile}>Remove</Button>
                                                <img className="img-cover" src={this.state.fileName} />
                                            </div>
                                        )}
                                    </Col>
                                </Row>
                            </Col>
                            <Col md={12} className="banner-action">
                                <Button disabled={!AddBannerPosting} className="btn-secondary mr-3" onClick={() => this.createBanner()}>Save</Button>
                                <Button className="btn-secondary-outline" onClick={() => this.NewBannerTogle(false)}>Cancel</Button>
                            </Col>
                        </div>
                    </div>
                }
                <div>
                    <Modal isOpen={this.state.DeleteModalOpen} toggle={this.deleteToggle}>
                        <ModalHeader>Delete App Banner</ModalHeader>
                        <ModalBody>Are you sure to delete this App banner data?</ModalBody>
                        <ModalFooter>
                            <Button disabled={DeleteActionPosting} color="secondary" onClick={() => this.deleteAppBanner()}>Yes</Button>{' '}
                            <Button color="primary" onClick={this.deleteToggle}>No</Button>
                        </ModalFooter>
                    </Modal>
                </div>
            </Fragment>
        )
    }
}