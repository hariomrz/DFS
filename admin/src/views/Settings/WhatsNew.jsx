import React, { Component, Fragment } from "react";
import { Row, Col, Table, Button, UncontrolledDropdown, DropdownToggle, DropdownMenu, DropdownItem, FormGroup, Input, InputGroup, Label, Modal, ModalBody, ModalFooter, ModalHeader } from "reactstrap";
import _ from 'lodash';
import Images from "../../components/images";
import HF, { _Map } from "../../helper/HelperFunction";
import { GET_RECORD_LIST_WHATSNEW,DELETE_RECORD_WHATSNEW } from '../../helper/WSCalling';
import { notify } from 'react-notify-toast';
import * as NC from "../../helper/NetworkingConstants";
import Select from 'react-select';
import AddWhatsNew from "./AddWhatsNew";
import WSManager from "../../helper/WSManager";
import Pagination from "react-js-pagination";
class WhatsNew extends Component {
   constructor(props) {
      super(props)
      this.toggle = this.toggle.bind(this);
      this.state = {
         WhatsNewData: [],
         Total: 0,
         ITEMS_PERPAGE: 20,
         ListPosting: false,
         noRecord: false,
         IsAddModalOpen: false,
         ActionPosting: false,
         DeleteModalOpen: false,
         DeleteActionPosting: false,
         editData:{},
         isEdit: false,
         Total: 0
      }
   }
   toggle() {
      this.setState({
         dropdownOpen: !this.state.dropdownOpen
      });
   }

   getrecordDetails = async (pageStatus, offset) => {
      const { ITEMS_PERPAGE, CURRENT_PAGE, filter } = this.state
      this.setState({
         ListPosting: true
      })
      let params = {
         "limit": ITEMS_PERPAGE,
         "page": pageStatus == 1 ? 1 : CURRENT_PAGE,
      }

      GET_RECORD_LIST_WHATSNEW(params).then(ResponseJson => {
         if (ResponseJson.response_code == NC.successCode) {
            this.setState({
               WhatsNewData: ResponseJson.data.result,
               Total: ResponseJson.data.total ? ResponseJson.data.total : 0
            }, () => {
               this.setState({
                  ListPosting: false
               })
            })
         } else {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
         }
      }).catch(error => {
         notify.show(NC.SYSTEM_ERROR, "error", 3000)
      })
   };
   componentDidMount() {
      this.getrecordDetails()
   }

   updateActivateStatus = (idx, id, status) => {
      this.setState({ ActionPosting: true })
      let tempBannerList = this.state.WhatsNewData
      const param = {
         id:id,
         status: status,
      }
      WSManager.Rest(NC.baseURL + NC.UPDATE_STATUS_WHATSNEW, param).then((responseJson) => {
          if (responseJson.response_code === NC.successCode) {
              notify.show(responseJson.message, "success", 5000);
              tempBannerList.map((item, index) => {
                  tempBannerList[index].status = 0
                  this.setState({ tempBannerList })
              })
              tempBannerList[idx].status = status
              this.setState({
               WhatsNewData: tempBannerList,
                  ActionPosting: false
              },() =>{  this.getrecordDetails()})
          }
      }).catch((error) => {
          notify.show(NC.SYSTEM_ERROR, "error", 5000);
      })
  }
 
   addModalOpen = (item,isEdit) => {
      this.setState({
         IsAddModalOpen: true,
         editData: item ? item : '',
         isEdit: isEdit
      })
   }
   addModalClose = () =>{
      this.setState({
         IsAddModalOpen: false,
         editData:{}
      },()=> {
         this.getrecordDetails()
      })
   }
   deleteModalCLose = () => {
      this.setState({
         deletedId: '',
         DeleteModalOpen: false
      })
   }

   deleteModalOpen = (id) => {
      this.setState({
         deletedId: id,
         DeleteModalOpen: true
      })
   }
   deleteGroupModal = () => {
      return (
         <div>

            <Modal
               isOpen={this.state.DeleteModalOpen}
               className="modal-sm model-header-payment"
            >
               <ModalHeader className="">  Delete Row</ModalHeader>
               <ModalBody>
                  <Row>
                     <Col md={12}>
                        <div className="ask-text" >
                           Are you sure you want to delete this row?</div>
                     </Col>
                  </Row>
               </ModalBody>
               <ModalFooter className="request-footer">
                  <button className="btn btn-primary" onClick={this.deleteModalCLose} >No</button>
                  <button
                     onClick={this.deleteGroup}
                     className={`btn btn-secondary-outline yes-wd-cls`}>Yes</button>
               </ModalFooter>
            </Modal>
         </div>
      )
   }

   deleteGroup = () => {
      let { deletedId } = this.state
      let params = {
         "id": deletedId,
      }
      DELETE_RECORD_WHATSNEW(params).then(ResponseJson => {
         if (ResponseJson.response_code == NC.successCode) {
            this.deleteModalCLose()
            notify.show(ResponseJson.message, "success", 3000)
            setTimeout(() => {
               this.getrecordDetails()
            }, 10);
         } else {
            this.deleteModalCLose()
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
         }
      }
      ).catch(error => {
         notify.show(NC.SYSTEM_ERROR, "error", 3000)
      })
   }

 

   render() {
      const { WhatsNewData, CURRENT_PAGE, ITEMS_PERPAGE, ListPosting, noRecord,IsAddModalOpen,ActionPosting,editData,isEdit,Total} = this.state;
      return (
         <React.Fragment>
            <div className="whatsnew-table-booster">
               <div className=" animate-left">
               {this.deleteGroupModal()}
                  <Row>
                     <Col md={12}>
                        <div className="whatsnew-details-header">
                           <div className="whatsnew-text-main-heading">What’s New</div>
                           <Button className="btn-secondary-outline"  onClick={() => this.addModalOpen()}>Add New Page</Button>
                        </div>
                     </Col>
                  </Row>

                  {(WhatsNewData.length > 0 && !ListPosting || noRecord) &&
                     <>
                        <Row className="whats-new-table">
                           <Col md={12} className="table-responsive common-table ">
                              <Table striped hover className="common-table  mb-0">
                                 <thead>
                                    <tr>
                                       <th>S.No</th>
                                       <th>Title</th>
                                       <th>Description</th>
                                       <th>Image</th>
                                       <th>Status</th>
                                       <th>Action</th>
                                    </tr>
                                 </thead>


                                 <>
                                    {
                                       _Map(WhatsNewData, (item, idx) => {
                                          return (
                                             <tbody key={idx}>
                                                <tr className={item.status == 0 ? "disabled-row" : ""}>
                                                   <td>{idx + 1}</td>
                                                   {/* <td>{((CURRENT_PAGE - 1) * ITEMS_PERPAGE) + (idx + 1)
                                                   }.</td> */}
                                                   <td >{item.name}</td>
                                                   <td style={{width:"276px"}}><div className="line-manage">{item.description}</div></td>
                                                   <td className="whatsnew-add-image-view">
                                                      <img src={(item.image) ? NC.S3 + NC.WHATSNEW_IMG_PATH + item.image : Images.no_image} />
                                                   </td>
                                                   <td className="active-deactive-view">
                                                      {item.status == 1 ?
                                                         <div className="active-view"></div>
                                                         : <i className="icon-inactive" />}
                                                   </td>
                                                   <td>
                                                            <UncontrolledDropdown>
                                                                <DropdownToggle disabled={ActionPosting} className="icon-action" />
                                                                <DropdownMenu>
                                                                    {item.status == 1
                                                                        ?
                                                                        <DropdownItem onClick={() => this.updateActivateStatus(idx, item.id, 0)}><i className="icon-inactive text-danger"/> Deactivate</DropdownItem>
                                                                        :
                                                                        <DropdownItem onClick={() => this.updateActivateStatus(idx, item.id, 1)}> <i className="icon-verified text-success"/> Activate</DropdownItem>
                                                                    }
                                                                    <DropdownItem
                                                                    onClick={() => this.deleteModalOpen(item.id)}><i className="icon-delete"/> Delete</DropdownItem>
                                                                    {item.status == 1 && 
                                                                    <DropdownItem 
                                                                    onClick={() => this.addModalOpen(item,true)}><i className="icon-edit"/> Edit</DropdownItem>}
                                                                </DropdownMenu>
                                                            </UncontrolledDropdown>
                                                        </td>
                                                </tr>
                                             </tbody>
                                          )
                                       })
                                    }
                                 </>

                              </Table>
                           </Col>
                        </Row>

                     </>
                  }
{ WhatsNewData.length == 0 && !ListPosting &&
    <div className="whatsnew-container">
    <Row>
       <Col md={12}>
          <div className="whatsnew-text">You have not added any Content yet.
             Start adding Content now.</div>
       </Col>
    </Row>
    <Row>
       <Col md={12}>
          <div className="whatsnew-screen">
             <img src={Images.NO_WHATSNEW} />
          </div>
       </Col>
    </Row>

    <Row className="add-whatsnew-button text-center">
       <Col md={12}>
          <Button onClick={() => this.addModalOpen()}>
             Add New Page
          </Button>
       </Col>
    </Row>
 </div>
}
                  {
                     IsAddModalOpen &&
                     <AddWhatsNew addModalShow={IsAddModalOpen} addModalhHide={this.addModalClose} editData={editData} isEdit={isEdit}/>
                  }   

<Row>
                     <Col md={12}>
                        {
                           Total > ITEMS_PERPAGE &&
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
            </div>
         </React.Fragment>
      )
   }
}
export default WhatsNew;