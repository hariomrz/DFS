import React, { Component, Fragment } from "react";
import { Row, Col, Table, Button, Modal, ModalBody, ModalFooter, Input } from "reactstrap";
import _ from 'lodash';
import { SF_uploadGroupIcon, SF_getGroup, SF_deleteGroup, SF_updateGroup, SF_createGroup } from "../../helper/WSCalling";
import * as NC from '../../helper/NetworkingConstants';
import Pagination from "react-js-pagination";
import { notify } from 'react-notify-toast';
import Loader from '../../components/Loader';
import Images from '../../components/images';
import { MSG_DELETE_GROUP } from "../../helper/Message";
class SF_ContestCategory extends Component {
    constructor(props) {
        super(props)
        this.state = {
            CURRENT_PAGE: 1,
            ITEMS_PERPAGE: NC.ITEMS_PERPAGE_LG,
            addMoreModalOpen: false,
            deleteModalOpen: false,
            GroupList: [],
            ListPosting: false,
            Total: 0,
            CreatePosting: true,
            deletePosting: false,
            EditFlag: false,
            GroupName: '',
            Description: '',
            GroupIconName: '',
        }
    }

    componentDidMount = () => {
        this.getGroup()
    }

    addMoreToggle = (index, item) => {           
        if (!_.isUndefined(item))
        {
            this.setState({
                EditFlag: true,
                EditIndex: index,
                GroupName: item.group_name,
                GroupIconName: item.icon,
                Description: item.description,
                GroupId: item.group_id,
                GroupIcon: (!_.isNull(item.icon) && !_.isEmpty(item.icon)) ? NC.S3 + NC.GROUP_ICON + item.icon : '',
            })
        }
        this.setState({ addMoreModalOpen: !this.state.addMoreModalOpen },()=>{
            if (!this.state.addMoreModalOpen)
               { 
                   this.setState({
                        EditFlag: false,
                        EditIndex: '',
                        GroupName: '',
                        Description: '',
                        GroupId: '',
                       GroupIcon: '',
                       CreatePosting: true,
                       GroupIconName: '',
                })
            }
        })
    }

    getGroup = () => {
        this.setState({ ListPosting: true })
        let { CURRENT_PAGE, ITEMS_PERPAGE } = this.state
        let params = {
            "current_page": CURRENT_PAGE,
            "items_perpage": ITEMS_PERPAGE,
            "sort_field": "added_date",
            "sort_order": "DESC"
        }

        SF_getGroup(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                if (CURRENT_PAGE == 1)
                    this.setState({
                        Total: ResponseJson.data.total
                    })
                this.setState({
                    GroupList: ResponseJson.data.result,
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
        
        if(name === 'GroupName' || name === 'Description')
        {
            value = value.replace(/  +/g, ' ')
        }

        this.setState({ [name]: value },()=>{
            let valid = true                 
            if ((this.state.GroupName.length > 2) && (this.state.Description.length > 9) && !_.isEmpty(this.state.GroupIconName)){
                valid = false
            }
            this.setState({ CreatePosting: valid })
        })
    }

    addMoreModal = () => {
        let { GroupIcon, CreatePosting, GroupName, EditFlag, Description } = this.state
        return (
            <div>
                <Modal className="addmore-sf-cate sf-addcon-cat-mod" isOpen={this.state.addMoreModalOpen}
                    toggle={this.addMoreToggle}>
                    <ModalBody>
                        <Row>
                            <Col md={12}>
                                <h3 className="h3-cls">
                                    {EditFlag ? 'Update ' : 'Add '} Group</h3>
                            </Col>
                        </Row>
                        <Row>
                            <Col md={12}>
        <label htmlFor="Redeem">Group Icon<span className="i-size">{' '}(Max 100x100)</span></label>
                                     <div className="su-image">
                                            {!_.isEmpty(GroupIcon) ?
                                                <Fragment>
                                                    <i onClick={this.resetFile} className="icon-close"></i>
                                                    <img className="img-cover" src={GroupIcon} />
                                                </Fragment>
                                                :
                                                <Fragment>
                                                    <Input
                                                        accept="image/x-png,
                                                        image/jpeg,image/jpg"
                                                        type="file"
                                                        name='GroupIcon'
                                                        id="GroupIcon"
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
                                <label>Group Name</label>
                                <Input
                                maxLength="50"
                                    type="text"
                                    name="GroupName"
                                    value={GroupName}
                                onChange={(e) => this.handleInputChange(e)}
                                />
                            </Col>
                        </Row>
                        <Row className="mt-2">
                            <Col md={12}>
                                <label>Group Description</label>
                                <Input
                                    maxLength="300"
                                    type="text"
                                    name="Description"
                                    value={Description}
                                    onChange={(e) => this.handleInputChange(e)}
                                />
                            </Col>
                        </Row>
                    </ModalBody>
                    <ModalFooter>
                        <Button className="btn-default-gray" onClick={this.addMoreToggle}>Cancel</Button>
                        <Button className="btn-secondary-outline"
                            disabled={CreatePosting}
                            onClick={EditFlag ? this.updateGroupFn : this.createGroupFn}>{EditFlag ? 'Update' :'Add'}</Button>{' '}
                    </ModalFooter>
                </Modal>
            </div>
        )
    }


    deleteGroupToggle = (dUserIdx, GroupId) => {
        this.setState({ 
            dUserIdx: dUserIdx,
            GroupId: GroupId,
            deleteModalOpen: !this.state.deleteModalOpen 
        })
    }

    deleteGroupModal = () => {
        let { deletePosting } = this.state
        return (
            <div>
                <Modal className="sf-addmore-su-modal" isOpen={this.state.deleteModalOpen}
                    toggle={this.deleteGroupToggle}>
                    <ModalBody className="text-center">
                        <h5>{MSG_DELETE_GROUP}</h5>
                    </ModalBody>
                    <ModalFooter className="justify-content-center">
                        <Button className="btn-default-gray" onClick={this.deleteGroupToggle}>No</Button>
                        <Button className="btn-secondary-outline"
                            disabled={deletePosting}
                            onClick={this.deleteGroup}>Yes</Button>{' '}
                    </ModalFooter>
                </Modal>
            </div>
        )
    }

    deleteGroup = () => {
        let { GroupId, GroupList } = this.state        
        this.setState({ deletePosting: true })
        let params = {
            "group_id": GroupId
        }
        SF_deleteGroup(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.deleteGroupToggle()
                notify.show(ResponseJson.message, "success", 3000)
                _.remove(GroupList,(item)=>{
                    return item.group_id == GroupId
                })
                this.setState({ 
                    GroupList: GroupList,
                    GroupName: '',
                    deletePosting: false 
                })
            } else {
                this.deleteGroupToggle()
                this.setState({ deletePosting: false })
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            this.setState({ deletePosting: false })
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    createGroupFn = () => {
        this.setState({ CreatePosting: true })        
        let { GroupName, Description, GroupIconName, Total } = this.state
        let params = {
            "group_name": GroupName,
            "description": Description,
            "icon": GroupIconName,
            "sort_order": Total + 1
        }

        SF_createGroup(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.addMoreToggle()
                this.getGroup()
                notify.show(ResponseJson.message, "success", 3000)
                this.setState({ 
                    GroupName: '',
                    GroupIcon:'', 
                    GroupIconName:'', 
                })
            } else {                
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            this.setState({ CreatePosting: false })
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    updateGroupFn = () => {
        this.setState({ CreatePosting: true })
        let { GroupIconName, GroupName, GroupId, GroupList, EditIndex, Description } = this.state
        let params = {
            "icon": GroupIconName,
            "group_name": GroupName,
            "description": Description,
            "group_id": GroupId,
        }
        let TempUsersList = GroupList        
        SF_updateGroup(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {

                TempUsersList[EditIndex].group_name = GroupName                
                TempUsersList[EditIndex].description = Description                
                if (GroupIconName)
                TempUsersList[EditIndex].icon = GroupIconName

                this.addMoreToggle()
                notify.show(ResponseJson.message, "success", 3000)
                this.setState({
                    TempUsersList: TempUsersList,
                    GroupName: '',
                    Description: '',
                    GroupId: '',
                    CreatePosting: true,
                    GroupIcon: '',
                    GroupIconName: '',
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
    
    handlePageChange(current_page) {
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            this.getGroup()
        });
    }

    onChangeImage = (event) => {
        this.setState({
            GroupIcon: URL.createObjectURL(event.target.files[0]),
            CreatePosting: true
        });
        const file = event.target.files[0];
        if (!file) {
            return;
        }
        var data = new FormData();
        data.append("userfile", file);
        SF_uploadGroupIcon(data).then(Response => {
                if (Response.response_code == NC.successCode) {
                    this.setState({
                        GroupIconName: Response.data.file_name,
                    });

                    if (this.state.GroupName.length > 2)
                        this.setState({ CreatePosting: false })
                } else {
                    this.setState({
                        GroupIcon: null,
                    });
                }
            }).catch(error => {
                notify.show(NC.SYSTEM_ERROR, "error", 3000);
            });
    }

    resetFile = () => {
        this.setState({
            GroupIcon: null,
            GroupIconName: '',
            CreatePosting: true,
        });
    }

    render() {
        let { GroupList, Total, ListPosting, CURRENT_PAGE, ITEMS_PERPAGE } = this.state
        return (
            <React.Fragment>
                <div className="sf-category">
                    {this.addMoreModal()}
                    {this.deleteGroupModal()}
                    <Row>
                        <Col md={6}>
                            <div className="float-left">
                                <h2 className="h2-cls mt-2">Contest Group</h2>
                            </div>
                        </Col>
                        <Col md={6}>
                            <Button onClick={this.addMoreToggle} className="add-more-su"><i className="icon-plus"></i> Add Group</Button>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12} className="table-responsive common-table">
                            <Table className="mb-0">
                                <thead>
                                    <tr>
                                        <th className="left-th pl-3">Group Icon</th>
                                        <th>Group Name</th>
                                        <th>Description</th>
                                        <th className="right-th pl-20">Action  </th>
                                    </tr>
                                </thead>
                                {
                                    Total > 0 ?
                                        _.map(GroupList, (item, idx) => {
                                            return (
                                            <tbody key={idx}>
                                                <tr>
                                                    <td className="pl-3">
                                                            <div className="sf-cate-img">
                                                                <img className="img-cover" src={item.icon ? NC.S3 + NC.GROUP_ICON + item.icon : Images.DEF_ADDPHOTO } />
                                                        </div>
                                                    </td>                                
                                                    <td className="pl-3">{item.group_name ? item.group_name : "--"}</td>
                                                        <td className="pl-3">{item.description ? item.description : "--"}</td>
                                                    <td>
                                                        <i
                                                        onClick={() => this.deleteGroupToggle(idx, item.group_id)}
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
                                                            {NC.NO_RECORDS}</div>
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
                </div>
            </React.Fragment>
        )
    }
}
export default SF_ContestCategory