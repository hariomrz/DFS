import React, { Component } from 'react'
import {
  Row,
  Col,
  Table,
  Button,
  Modal,
  ModalBody,
  ModalFooter,
} from 'reactstrap'
import _ from 'lodash'
import { rolesList, deleteRoles } from '../../helper/WSCalling'
import * as NC from '../../helper/NetworkingConstants'
import Pagination from 'react-js-pagination'
import { notify } from 'react-notify-toast'
import Loader from '../../components/Loader'
import HF from '../../helper/HelperFunction'
import { MSG_DELETE_USER } from '../../helper/Message'
class AdminRoleList extends Component {
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
    }
  }

  componentDidMount = () => {
    this.getUsers()
  }

  getUsers = () => {
    this.setState({ ListPosting: true })
    let { CURRENT_PAGE, ITEMS_PERPAGE } = this.state
    let params = {
      current_page: CURRENT_PAGE,
      keyword: '',
      items_perpage: ITEMS_PERPAGE,
      sort_field: 'added_date',
      sort_order: 'DESC',
    }

    rolesList(params)
      .then((ResponseJson) => {
        if (ResponseJson.response_code == NC.successCode) {
          if (CURRENT_PAGE == 1)
            this.setState({
              Total: ResponseJson.data.total,
            })
          this.setState({
            UsersList: ResponseJson.data.result,
            ListPosting: false,
          })
        } else {
          notify.show(NC.SYSTEM_ERROR, 'error', 3000)
        }
      })
      .catch((error) => {
        notify.show(NC.SYSTEM_ERROR, 'error', 3000)
      })
  }

  deleteUserToggle = (d_admin_idx, admin_id) => {
    this.setState({
      AdminId: admin_id,
      deleteModalOpen: !this.state.deleteModalOpen,
    })
  }

  deleteUserModal = () => {
    let { deletePosting } = this.state
    return (
      <div>
        <Modal
          className="addmore-su-modal"
          isOpen={this.state.deleteModalOpen}
          toggle={this.deleteUserToggle}
        >
          <ModalBody className="text-center">
            <h5>{MSG_DELETE_USER}</h5>
          </ModalBody>
          <ModalFooter className="justify-content-center">
            <Button
              className="btn-default-gray"
              onClick={this.deleteUserToggle}
            >
              No
            </Button>
            <Button
              className="btn-secondary-outline"
              disabled={deletePosting}
              onClick={this.deleteSystemUser}
            >
              Yes
            </Button>{' '}
          </ModalFooter>
        </Modal>
      </div>
    )
  }

  deleteSystemUser = () => {
    let { AdminId, UsersList } = this.state

    this.setState({ deletePosting: true })
    let params = {
      admin_id: AdminId,
    }
    deleteRoles(params)
      .then((ResponseJson) => {
        if (ResponseJson.response_code == NC.successCode) {
          this.deleteUserToggle()
          notify.show(ResponseJson.message, 'success', 3000)
          _.remove(UsersList, (item) => {
            return item.admin_id == AdminId
          })
          this.setState({
            UsersList: UsersList,
            deletePosting: false,
          })
        } else {
          this.deleteUserToggle()
          this.setState({ deletePosting: false })
          notify.show(NC.SYSTEM_ERROR, 'error', 3000)
        }
      })
      .catch((error) => {
        this.setState({ deletePosting: false })
        notify.show(NC.SYSTEM_ERROR, 'error', 3000)
      })
  }

  handlePageChange(current_page) {
    this.setState(
      {
        CURRENT_PAGE: current_page,
      },
      () => {
        this.getUsers()
      },
    )
  }

  render() {
    let {
      UsersList,
      Total,
      ListPosting,
      CURRENT_PAGE,
      ITEMS_PERPAGE,
    } = this.state
    return (
      <React.Fragment>
        <div className="system-userlist">
          {this.deleteUserModal()}
          <Row>
            <Col md={12}>
              <div className="float-left">
                <h2 className="h2-cls mt-2">Admin Role</h2>
              </div>
            </Col>
          </Row>

          <Row>
            <Col md={12} className="table-responsive common-table">
              <Table className="mb-0">
                <thead>
                  <tr>
                    <th className="left-th pl-3">First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>2FA</th>
                    <th>Roles</th>

                    <th className="right-th pl-20">Action </th>
                  </tr>
                </thead>
                {Total > 0 ? (
                  _.map(UsersList, (item, idx) => {
                    let acList = Object.values(item.access_list)
                    let tempArray = []
                    _.map(acList, (item) => {
                      let capName = item ? HF.capitalFirstLetter(item) : ''
                      let itemName = item ? capName.replace(/_/g, ' ') : ''
                      tempArray.push(itemName)
                    })
                    let new_access_list = !_.isEmpty(tempArray)
                      ? tempArray.join(', ')
                      : ''

                    return (
                      <tbody key={idx}>
                        <tr>
                          <td className="pl-3">
                            {item.firstname ? item.firstname : '--'}
                          </td>
                          <td className="pl-3">
                            {item.lastname ? item.lastname : '--'}
                          </td>
                          <td className="pl-3">
                            {item.email ? item.email : '--'}
                          </td>
                          <td>
                            {item.two_fa && item.two_fa == '0'
                              ? 'Off'
                              : item.two_fa && item.two_fa == '1'
                              ? 'On'
                              : '--'}
                          </td>
                          <td>{new_access_list}</td>
                          <td>
                            <i
                              onClick={() =>
                                this.deleteUserToggle(idx, item.admin_id)
                              }
                              className="icon-delete"
                            ></i>
                            <i
                              onClick={() =>
                                this.props.history.push(
                                  '/admin-role/add-role?admin=' + item.admin_id,
                                )
                              }
                              className="icon-edit ml-4"
                            ></i>
                          </td>
                        </tr>
                      </tbody>
                    )
                  })
                ) : (
                  <tbody>
                    <tr>
                      <td colSpan="8">
                        {Total == 0 && !ListPosting ? (
                          <div className="no-records">{NC.NO_RECORDS}</div>
                        ) : (
                          <Loader />
                        )}
                      </td>
                    </tr>
                  </tbody>
                )}
              </Table>
            </Col>
          </Row>
          <Row>
            <Col md={12}>
              {Total > NC.ITEMS_PERPAGE && (
                <div className="custom-pagination float-right mt-5">
                  <Pagination
                    activePage={CURRENT_PAGE}
                    itemsCountPerPage={ITEMS_PERPAGE}
                    totalItemsCount={Total}
                    pageRangeDisplayed={5}
                    onChange={(e) => this.handlePageChange(e)}
                  />
                </div>
              )}
            </Col>
          </Row>
          {}
        </div>
      </React.Fragment>
    )
  }
}
export default AdminRoleList
