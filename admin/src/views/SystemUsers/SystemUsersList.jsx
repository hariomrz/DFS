import React, { Component, Fragment } from "react";
import { Row, Col, Table, Button, Modal, ModalBody, ModalFooter, Input } from "reactstrap";
import _ from 'lodash';
import { remove_image_SU, do_upload_SU, getUsers_SU, deleteUsers_SU, updateUsers_SU, createUsers_SU } from "../../helper/WSCalling";

import * as NC from '../../helper/NetworkingConstants';
import Pagination from "react-js-pagination";
import { notify } from 'react-notify-toast';
import Loader from '../../components/Loader';
import Images from '../../components/images';
import WSManager from '../../helper/WSManager';
import ActionRequestModal from '../../components/ActionRequestModal/ActionRequestModal';
import { NO_RECORDS, SU_ERROR_MSG, MSG_DELETE_USER, SYSTEM_ERROR, UPLOAD_CSV, CSV_USERS_ERROR } from "../../helper/Message";
class SystemUsersList extends Component {
    constructor(props) {
        super(props)
        this.state = {
            CURRENT_PAGE: 1,
            ITEMS_PERPAGE: NC.ITEMS_PERPAGE_LG,
            addMoreModalOpen: false,
            deleteModalOpen: false,
            UsersList: [],
            ListPosting: false,
            Total: 0,
            CreatePosting: true,
            deletePosting: false,
            EditFlag: false,
            Username: '',
            upCsvModalOpen : false,
            CsvName : '',
            CsvFile : '',
            csvPosting : true,
        }
    }

    componentDidMount = () => {
        this.getUsers()
    }

    addMoreToggle = (index, item) => {         
        if (!_.isUndefined(item))
        {
            this.setState({
                CreatePosting: false,
                EditFlag: true,
                EditIndex: index,
                Username: item.user_name,
                UserId: item.user_id,
                ProfileImage: (!_.isNull(item.image) && !_.isEmpty(item.image)) ? NC.S3 + NC.THUMB + item.image : '',
            })
        }
        this.setState({ addMoreModalOpen: !this.state.addMoreModalOpen },()=>{
            if (!this.state.addMoreModalOpen)
               { 
                   this.setState({
                       EditFlag: false,
                    EditIndex: '',
                    Username: '',
                    UserId: '',
                })
            }
        })
    }

    getUsers = () => {
        this.setState({ ListPosting: true })
        let { CURRENT_PAGE, ITEMS_PERPAGE } = this.state
        let params = {
            "current_page": CURRENT_PAGE,
            "keyword": "",
            "items_perpage": ITEMS_PERPAGE,
            "sort_field": "added_date",
            "sort_order": "DESC"
        }

        getUsers_SU(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                if (CURRENT_PAGE == 1)
                    this.setState({
                        Total: ResponseJson.data.total
                    })
                this.setState({
                    UsersList: ResponseJson.data.result,
                    ListPosting: false
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    handleInputChange = (event) =>{
        let name = event.target.name
        let value = event.target.value
        
        if(value.length > 2)
            this.setState({ CreatePosting: false })
        else
            this.setState({ CreatePosting: true })        

        if (value.match(/^[A-Za-z0-9_@.]*$/)) {
            
            this.setState({ [name]: value })
        } else {
            notify.show(SU_ERROR_MSG, "error", 3000)
        }           
        
    }

    addMoreModal = () => {
        let { ProfileImage, CreatePosting, Username, EditFlag } = this.state
        return (
            <div>
                <Modal className="addmore-su-modal" isOpen={this.state.addMoreModalOpen}
                    toggle={this.addMoreToggle}>
                    <ModalBody>
                        <Row>
                            <Col md={12}>
                                <h3 className="h3-cls">
                                    {EditFlag ? 'Update ' : 'Add '} System User</h3>
                            </Col>
                        </Row>
                        <Row>
                            <Col md={12}>
                                <label htmlFor="Redeem">System User Image</label>
                                     <div className="su-image">
                                            {!_.isEmpty(ProfileImage) ?
                                                <Fragment>
                                                    <i onClick={this.resetFile} className="icon-close"></i>
                                                    <img className="img-cover" src={ProfileImage} />
                                                </Fragment>
                                                :
                                                <Fragment>
                                                    <Input
                                                        accept="image/x-png,
                                                        image/jpeg,image/jpg"
                                                        type="file"
                                                        name='ProfileImage'
                                                        id="ProfileImage"
                                                        onChange={this.onChangeImage}
                                                    />
                                                    <img className="def-addphoto" src={Images.DEF_ADDPHOTO} alt="" />
                                                </Fragment>
                                            }
                                        </div>
                                </Col>
                        </Row>
                        <Row>
                            <Col md={12}>
                                <label>System User Name</label>
                                <Input
                                maxLength="25"
                                    type="text"
                                    name="Username"
                                    value={Username}
                                onChange={(e) => this.handleInputChange(e)}
                                />
                            </Col>
                        </Row>
                    </ModalBody>
                    <ModalFooter>
                        <Button className="btn-default-gray" onClick={this.addMoreToggle}>Cancel</Button>
                        <Button className="btn-secondary-outline"
                            disabled={CreatePosting}
                            onClick={EditFlag ? this.updateSystemUser : this.createSystemUser}>{EditFlag ? 'Update' :'Add'}</Button>{' '}
                    </ModalFooter>
                </Modal>
            </div>
        )
    }

    deleteUserToggle = (dUserIdx, UserId) => {
        this.setState({ 
            dUserIdx: dUserIdx,
            UserId: UserId,
            deleteModalOpen: !this.state.deleteModalOpen 
        })
    }

    deleteUserModal = () => {
        let { deletePosting } = this.state
        return (
            <div>
                <Modal className="addmore-su-modal" isOpen={this.state.deleteModalOpen}
                    toggle={this.deleteUserToggle}>
                    <ModalBody className="text-center">
                        <h5>{MSG_DELETE_USER}</h5>
                    </ModalBody>
                    <ModalFooter className="justify-content-center">
                        <Button className="btn-default-gray" onClick={this.deleteUserToggle}>No</Button>
                        <Button className="btn-secondary-outline"
                            disabled={deletePosting}
                            onClick={this.deleteSystemUser}>Yes</Button>{' '}
                    </ModalFooter>
                </Modal>
            </div>
        )
    }

    deleteSystemUser = () => {
        let { dUserIdx, UserId, UsersList } = this.state        
        this.setState({ deletePosting: true })
        let params = {
            "user_id": UserId
        }
        
        deleteUsers_SU(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.deleteUserToggle()
                notify.show(ResponseJson.message, "success", 3000)
                _.remove(UsersList,(item)=>{
                    return item.user_id == UserId
                })
                this.setState({ 
                    UsersList: UsersList,
                    Username: '',
                    deletePosting: false 
                })
            } else {
                this.deleteUserToggle()
                this.setState({ deletePosting: false })
                notify.show(SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            this.setState({ deletePosting: false })
            notify.show(SYSTEM_ERROR, "error", 3000)
        })
    }

    createSystemUser = () => {
        this.setState({ CreatePosting: true })
        let params = {
            "image": this.state.ProfileImageName,
            "user_name": this.state.Username,
            "balance": "0"
        }

        createUsers_SU(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.addMoreToggle()
                this.getUsers()
                notify.show(ResponseJson.message, "success", 3000)
                this.setState({ 
                    Username: '',
                    ProfileImage:'', 
                    ProfileImageName:'', 
                })
            } else {                
                notify.show(SYSTEM_ERROR, "error", 3000)
            }
            
        }).catch(error => {
            this.setState({ CreatePosting: false })
            notify.show(SYSTEM_ERROR, "error", 3000)
        })
    }

    updateSystemUser = () => {
        this.setState({ CreatePosting: true })
        let { ProfileImageName, Username, UserId, UsersList, EditIndex } = this.state
        let params = {
            "image": ProfileImageName,
            "user_name": Username,
            "user_id": UserId,
            "balance": "0"
        }
        let TempUsersList = UsersList        
        updateUsers_SU(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                TempUsersList[EditIndex].user_name = Username
                
                if (ProfileImageName)
                TempUsersList[EditIndex].image = ProfileImageName

                this.addMoreToggle()
                notify.show(ResponseJson.message, "success", 3000)
                this.setState({
                    TempUsersList: TempUsersList,
                    Username: '',
                    UserId: '',
                    CreatePosting: false,
                    ProfileImage: '',
                    ProfileImageName: '',
                })
            } else {
                this.setState({ CreatePosting: false })
                notify.show(SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            this.setState({ CreatePosting: false })
            notify.show(SYSTEM_ERROR, "error", 3000)
        })
    }
    
    handlePageChange(current_page) {
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            this.getUsers()
        });
    }

    onChangeImage = (event) => {
        this.setState({
            ProfileImage: URL.createObjectURL(event.target.files[0]),
            CreatePosting: true
        });
        const file = event.target.files[0];
        if (!file) {
            return;
        }
        var data = new FormData();
        data.append("userfile", file);
        do_upload_SU(data).then(Response => {
                if (Response.response_code == NC.successCode) {
                    this.setState({
                        ProfileImageName: Response.data.file_name,
                        CreatePosting: false,
                    });
                } else {
                    this.setState({
                        ProfileImage: null,
                        CreatePosting: false,
                    });
                }
            }).catch(error => {
                notify.show(SYSTEM_ERROR, "error", 3000);
            });
    }

    resetFile = () => {
        this.setState({ CreatePosting: true }); 
        let { UserId, UsersList, EditIndex} = this.state      
        let params = { user_id: UserId }
        let TempUsersList = UsersList
        remove_image_SU(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, "success", 3000)
                TempUsersList[EditIndex].image = null
                this.setState({
                    UsersList: TempUsersList,
                    ProfileImage: null,
                    ProfileImageName: '',
                });
            }
            this.setState({ CreatePosting: false });
        }).catch(error => {
            notify.show(SYSTEM_ERROR, "error", 3000);
        });
    }

    uploadCsvModal = () => {
       this.setState({ 
           CsvName: '',
           CsvFile: '',
           csvPosting: true,
           upCsvModalOpen: !this.state.upCsvModalOpen 
        })
    }

    addCsvModal = () => {
        let { CsvName, upCsvModalOpen, csvPosting } = this.state
        return (
            <div>
                <Modal className="addmore-su-modal addcsv-modal" isOpen={upCsvModalOpen}
                    toggle={this.uploadCsvModal}>
                    <ModalBody>
                        <Row>
                            <Col md={12}>
                                <h3 className="h3-cls">Upload CSV</h3>
                            </Col>
                        </Row>
                        <Row>
                            <Col md={12}>
                                <div className="redeem-box op-create-category">
                                    <div className="select-image-box w-100">
                                        <div className="dashed-box w-100">
                                            {!_.isEmpty(this.state.CsvName) ?
                                                <Fragment>
                                                    <i onClick={this.resetCsv} className="icon-close"></i>
                                                    <div className="csv-name">{CsvName}</div>
                                                </Fragment>
                                                :
                                                <Fragment>
                                                    <Input
                                                        accept=".csv"
                                                        type="file"
                                                        name='ProofImage'
                                                        id="ProofImage"
                                                        onChange={this.selectCsv}
                                                    />
                                                    <span className="csv-help-text">Choose a file or drag it here</span>
                                                </Fragment>
                                            }
                                        </div>
                                    </div>
                                </div>
                                </Col>
                        </Row>
                    </ModalBody>
                    <ModalFooter className="justify-content-center">
                        <Button className="btn-default-gray" onClick={this.uploadCsvModal}>Cancel</Button>
                        <Button 
                            className="btn-secondary-outline"
                            disabled={csvPosting}
                            onClick={this.toggleActionPopup}>Upload</Button>{' '}
                    </ModalFooter>
                </Modal>
            </div>
        )
    }

    selectCsv = (event) => {
        if (event.target.files)
        {
            const file = event.target.files[0];
            if (!file) {
                return;
            }
            this.setState({ 
                CsvFile : file,
                CsvName: event.target.files[0].name,
                csvPosting: false
            })
        }
    }
    
    saveCsvOnServer = () => {
        this.setState({ csvPosting: true })
        var data = new FormData();        
        data.append("file", this.state.CsvFile);
        WSManager.multipartPost(NC.baseURL + NC.UPLOAD_SYSTEMUSER, data)
            .then(Response => {
                if (Response.response_code == NC.successCode) {
                    if (Response.data.skipped && Response.data.skipped !== 0)
                    {
                        notify.show(Response.data.skipped + ' ' + CSV_USERS_ERROR, "success", 3000);
                    }
                    this.getUsers()
                    this.setState({
                        ActionPopupOpen : false,
                        upCsvModalOpen : false,
                        CsvFile: '',
                        CsvName: '',
                    });
                }
            }).catch(error => {
                notify.show(SYSTEM_ERROR, "error", 3000);
            });
    }

    resetCsv = () => {
        this.setState({
            CsvFile: '',
            CsvName: '',
            csvPosting: true,
        });
    }

    //function to toggle action popup
    toggleActionPopup = () => {
        this.setState({
            ActionPopupOpen: !this.state.ActionPopupOpen
        })
    }

    sampleCSV = () => {
        let sessionKey = WSManager.getToken();
        let query_string = "&Sessionkey" + "=" + sessionKey;
        window.open(NC.baseURL + 'adminapi/systemuser/get_sample_csv?' + query_string, '_blank');
    }

    render() {
        let { UsersList, Total, ListPosting, CURRENT_PAGE, ITEMS_PERPAGE, ActionPopupOpen } = this.state
        const ActionCallback = {
            Message: UPLOAD_CSV,
            modalCallback: this.toggleActionPopup,
            ActionPopupOpen: ActionPopupOpen,
            modalActioCallback: this.saveCsvOnServer,
        }
        return (
            <React.Fragment>
                <div className="system-userlist">
                    <ActionRequestModal {...ActionCallback} />
                    {this.addMoreModal()}
                    {this.addCsvModal()}
                    {this.deleteUserModal()}
                    <Row>
                        <Col md={4}>
                            <div className="float-left">
                                <h2 className="h2-cls mt-2">System User Managment</h2>
                            </div>
                        </Col>
                        <Col md={8}>                            
                            <Button onClick={this.addMoreToggle} className="add-more-su"><i className="icon-plus"></i> Add More</Button>
                            <Button onClick={e => this.sampleCSV()} className="add-more-su mr-3"><i className="icon-export"></i> 
                                <a className="csv-anc">Sample CSV</a>
                            </Button>
                            <Button onClick={this.uploadCsvModal} className="add-more-su mr-3"><i className="icon-plus"></i> Upload CSV</Button>
                        </Col>
                    </Row>
                    <Row className="text-right">
                        <Col md={12}>
                            <div className="total-bot-user">
                                Total system user : {Total}
                            </div>
                        </Col>
                    </Row>

<Row>
    <Col md={12} className="table-responsive common-table">
        <Table className="mb-0">
            <thead>
                <tr>
                    <th className="left-th pl-3">System User Image</th>
                    <th>System User Name</th>
                    <th className="right-th pl-20">Action  </th>
                </tr>
            </thead>
            {
                Total > 0 ?
                    _.map(UsersList, (item, idx) => {
                        return (
                        <tbody key={idx}>
                            <tr>
                                <td className="pl-3">
                                    <div className="su-profile-img">
                                            <img className="img-cover" src={item.image ? NC.S3 + NC.THUMB + item.image : Images.DEF_ADDPHOTO } />
                                    </div>
                                </td>
                                
                                <td className="pl-3">{item.user_name ? item.user_name : "--"}</td>

                                <td>
                                    <i
                                    onClick={() => this.deleteUserToggle(idx, item.user_id)}
                                    className="icon-delete"></i>
                                        <i
                                            onClick={() => this.addMoreToggle(idx, item)}
                                            className="icon-edit ml-4"></i>
                                    </td>
                            </tr>
                        </tbody>
                        )
                    })
                    :
                    <tbody>
                        <tr>
                            <td colSpan="8">
                                {(Total == 0 && !ListPosting) ?
                                    <div className="no-records">
                                        {NO_RECORDS}</div>
                                    :
                                    <Loader />
                                }
                            </td>
                        </tr>
                    </tbody>
            }
        </Table>
    </Col>
</Row>
                    <Row>
                        <Col md={12}>
                            {
                                Total > NC.ITEMS_PERPAGE &&
                                (<div className="custom-pagination float-right mt-5">
                                    <Pagination
                                        activePage={CURRENT_PAGE}
                                        itemsCountPerPage={ITEMS_PERPAGE}
                                        totalItemsCount={Total}
                                        pageRangeDisplayed={5}
                                        onChange={e => this.handlePageChange(e)}
                                    />
                                </div>)
                            }
                        </Col>
                    </Row>
                    {

                    }
                </div>
            </React.Fragment>
        )
    }
}
export default SystemUsersList