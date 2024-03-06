import React, { Component, Fragment } from 'react';
import { Col, Row, Nav, NavItem, NavLink, TabContent, TabPane, Button, Modal, ModalBody, ModalHeader, ModalFooter, Input } from 'reactstrap';
import Images from '../../components/images';
import _ from 'lodash';
import WSManager from '../../helper/WSManager';
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import { get_all_avatars, change_avatar_status } from '../../helper/WSCalling';
import Loader from '../../components/Loader';
import Pagination from "react-js-pagination";
import HF from '../../helper/HelperFunction';
class ManageAvatars extends Component {
    constructor(props) {
        super(props);
        this.state = {
            activeTab: '1',
            ListPosting: false,
            hidePosting: false,
            submitAvatarOpen: false,
            PrfImgPosting: true,
            SubmitFlag: false,
            AvatarsList: [],
            PERPAGE: NC.ITEMS_PERPAGE,
            // PERPAGE: 3,
            CURRENT_PAGE: 1,
        };
    }

    componentDidMount() {
        this.getAvatars()
    }

    toggle(tab) {
        if (tab !== this.state.activeTab)
            this.setState({ AvatarsList: [], activeTab: tab, CURRENT_PAGE: 1 }, this.getAvatars)
    }

    handlePageChange(current_page) {
        if (current_page !== this.state.CURRENT_PAGE) {
            this.setState({
                CURRENT_PAGE: current_page
            }, () => {
                this.getAvatars()
            });
        }
    }

    getAvatars = () => {
        this.setState({ ListPosting: true })
        let { activeTab, CURRENT_PAGE, PERPAGE } = this.state
        let params = {
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,            
            status: activeTab == '1' ? activeTab : '0'
        }
        get_all_avatars(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    AvatarsList: Response.data.result,
                    TotalAvatars: !_.isUndefined(Response.data.total) ? Response.data.total : 0,
                    ListPosting: false
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    changeStatus = (idx, a_id, a_status) => {
        this.setState({ hidePosting: true })
        let { TotalAvatars, PERPAGE, CURRENT_PAGE} = this.state
        let params = {
            id: a_id,
            status: a_status,
        }

        change_avatar_status(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show("Status updated successfully", 'success', 5000)
                this.setState({
                hidePosting: false,
                    CURRENT_PAGE: HF.getCurrentPage(TotalAvatars, PERPAGE, CURRENT_PAGE),
                },()=>{
                        this.getAvatars()
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    viewAvatars = () => {
        let { AvatarsList, hidePosting, TotalAvatars, ListPosting } = this.state
        return (
            (TotalAvatars > 0) ?
                _.map(AvatarsList, (item, idx) => {
                    let fImg = NC.S3 + NC.UPLOAD_AVATAR + item.name
                    return (
                        <div key={idx} className="avatars-list">
                            <div className="avatars-item xactive">
                                <div className="avatar-img">
                                    {
                                        fImg !== '' ?
                                            <img src={item.name ? fImg : Images.no_image} className="img-cover" alt="" />
                                            :
                                            <div className="spinner-border text-dark"></div>
                                    }
                                </div>
                            </div>
                            <div
                                onClick={() => !hidePosting && this.changeStatus(idx, item.id, item.status)}
                                className="avatars-action">
                                {
                                    !hidePosting && item.status == '1' && 'Hide'
                                }
                                {
                                    !hidePosting && item.status == '0' && 'Unhide'
                                }
                            </div>
                        </div>
                    )
                })
                :
                (TotalAvatars == 0 && !ListPosting) ?
                    <div className="no-records">
                        {NC.NO_RECORDS}</div>
                    :
                    <Loader />

        )
    }

    toggleAvatarModal = () => {
        this.setState({
            submitAvatarOpen: !this.state.submitAvatarOpen
        })
    }

    submitAvatarModal() {
        let { PrfImgPosting, ProofImage } = this.state
        return (
            <Modal
                isOpen={this.state.submitAvatarOpen}
                className="modal-sm coupon-history prediction-popup avatar-box"
                toggle={this.toggleAvatarModal}
            >
                <ModalHeader>Add Avatar</ModalHeader>
                <ModalBody>
                    <Row>
                        <Col md={12}>
                            <div className="op-create-category proof-box clearfix">
                                <div className="select-image-box float-left">
                                    <label className="dashed-box">
                                        {!_.isEmpty(ProofImage) ?
                                            <Fragment>
                                                <img className="img-cover" src={ProofImage} />
                                            </Fragment>
                                            :
                                            <Fragment>
                                                <Input
                                                    accept="image/x-png"
                                                    type="file"
                                                    name='ProofImage'
                                                    id="ProofImage"
                                                    onChange={this.onChangeImage}
                                                />
                                                <img className="def-addphoto" src={Images.DEF_ADDPHOTO} alt="" />
                                                <div className="up-text">Upload Image</div>
                                            </Fragment>
                                        }
                                    </label>
                                </div>
                            </div>
                            <div className="help-text">
                                Please Upload a PNG image with 86x86 pixel dimension
                                </div>
                        </Col>
                    </Row>
                </ModalBody>
                <ModalFooter className="request-footer">
                    <div
                        onClick={this.resetFile}
                        className={`mr-3 refresh-btn icon-reset ${PrfImgPosting ? 'ref-disable' : ''}`}></div>
                    <Button
                        disabled={PrfImgPosting}
                        onClick={() => this.submitAvatar()}
                        className='btn-secondary-outline'>Submit</Button>
                </ModalFooter>
            </Modal>
        )
    }

    onChangeImage = (event) => {
        if (!this.state.SubmitFlag) {
            this.setState({
                ProofImage: URL.createObjectURL(event.target.files[0]),
                PrfImgPosting: true
            });
            const file = event.target.files[0];
            if (!file) {
                return;
            }

            this.setState({
                PrfImgPosting: false,
                file: file
            });
        }
        if (this.state.SubmitFlag) {
            var data = new FormData();
            data.append("file", this.state.file);
            WSManager.multipartPost(NC.baseURL + NC.AVATAR_DO_UPLOAD, data)
                .then(Response => {
                    if (Response.response_code == NC.successCode) {
                        notify.show(Response.message, "success", 3000);
                        this.setState({ submitAvatarOpen: false, ProofImage: '', SubmitFlag: false })
                        this.getAvatars()
                    } else {
                        this.setState({ PrfImgPosting: false })
                    }
                }).catch(error => {
                    notify.show(NC.SYSTEM_ERROR, "error", 3000);
                });
        }
    }

    resetFile = () => {
        this.setState({
            PrfImgPosting: true,
            SubmitFlag: false,
            ProofImage: null,
            ProofImageName: '',
        });
    }

    submitAvatar = () => {
        this.setState({ PrfImgPosting: true, SubmitFlag: true }, this.onChangeImage)
    }

    render() {
        let { activeTab, CURRENT_PAGE, PERPAGE, TotalAvatars } = this.state
        return (
            <div className="manage-avatars mt-30">
                {this.submitAvatarModal()}
                <Row className="mb-2">
                    <Col md={6}>
                        <div className="pre-heading">Manage Avatars</div>
                    </Col>
                    <Col md={6}>
                        <Button
                            className="btn-secondary-outline float-right"
                            onClick={this.toggleAvatarModal}
                        >Add Avatar
                        </Button>
                    </Col>
                </Row>
                <Row>
                    <Col md={12}>
                        <div className="user-navigation">
                            <Row>
                                <Col md={12}>
                                    <Nav tabs>
                                        <NavItem
                                            className={activeTab === '1' ? "active" : ""}
                                            onClick={() => { this.toggle('1'); }}
                                        >
                                            <NavLink>
                                                Active
                                            </NavLink>
                                        </NavItem>
                                        <NavItem
                                            className={activeTab === '2' ? "active" : ""}
                                            onClick={() => { this.toggle('2'); }}
                                        >
                                            <NavLink>
                                                Hidden
                                            </NavLink>
                                        </NavItem>
                                    </Nav>
                                </Col>
                            </Row>
                            <TabContent activeTab={activeTab}>
                                {
                                    (activeTab == '1') &&
                                    <TabPane tabId="1" className="animated fadeIn mt-30">
                                        <div className="avatars-style clearfix">
                                            {this.viewAvatars(18)}
                                        </div>
                                    </TabPane>
                                }
                                {
                                    (activeTab == '2') &&
                                    <TabPane tabId="2" className="animated fadeIn mt-30">
                                        <div className="avatars-style clearfix">
                                            {this.viewAvatars(25)}
                                        </div>
                                    </TabPane>
                                }
                                {
                                    // TotalAvatars >= PERPAGE &&
                                    TotalAvatars > PERPAGE &&
                                    <div className="custom-pagination float-right">
                                        <Pagination
                                            activePage={CURRENT_PAGE}
                                            itemsCountPerPage={PERPAGE}
                                            totalItemsCount={TotalAvatars}
                                            pageRangeDisplayed={5}
                                            onChange={e => this.handlePageChange(e)}
                                        />
                                    </div>
                                }
                            </TabContent>
                        </div>
                    </Col>
                </Row>
            </div>
        )
    }
}
export default ManageAvatars


