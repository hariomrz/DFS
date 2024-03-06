import React, { Component, Fragment } from "react";
import { Row, Col, Table, Input, Button, UncontrolledDropdown, DropdownToggle, DropdownMenu, DropdownItem, Modal, ModalHeader, ModalBody, ModalFooter } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import _ from 'lodash';
import Pagination from "react-js-pagination";
import * as MODULE_DEAL from "../deals/Deals.config";
import { MODULE_NOT_ENABLE } from "../../helper/Message";
import HF from '../../helper/HelperFunction';
import queryString from 'query-string';
import { Base64 } from 'js-base64';
export default class DealList extends Component {
    constructor(props) {
        super(props)
        this.state = {
            // Totaldeals: 0,
            PERPAGE: 10,
            CURRENT_PAGE: 1,
            NewDealToggle: false,
            title: '',
            link: '',
            fileUplode: '',
            fileName: '',
            validURL: false,
            dropdownOpen: false,
            ActionPosting: false,
            DeleteActionPosting: false,
            DeleteModalOpen: false,
            DealList: [],
            newDealObj: {
                amount: '',
                real: '',
                bonus: '',
                coins: ''

            },
            DealCatId: '',
            DealCatTempId: '',
        }
    }
    componentDidMount() {
        if (HF.getMasterData().allow_deal != '1') {
            notify.show(MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        this.getDeals()
    }

    getDeals() {
        const { PERPAGE, CURRENT_PAGE } = this.state
        const param = {
            items_perpage: PERPAGE,
            total_items: 0,
            current_page: CURRENT_PAGE,
            sort_order: "DESC",
            sort_field: "AB.created_date",
            DealList: []
        }

        WSManager.Rest(NC.baseURL + MODULE_DEAL.GET_DEALS, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({
                    DealList: responseJson.data.result ? responseJson.data.result : [],
                    TotalDeals: responseJson.data.total ? responseJson.data.total : 0,
                    DealCatId: responseJson.data.category_id ? responseJson.data.category_id : '',
                    DealCatTempId: responseJson.data.category_template_id ? responseJson.data.category_template_id : '',
                });
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }
    NewDealTogle(flag) {
        this.setState({
            NewDealToggle: flag,
            newDealObj: {
                amount: '',
                real: '',
                bonus: '',
                coins: ''
            }
        })
    }
    handleNameChange = e => {
        const name = e.target.name;
        const value = e.target.value;
        var dealObj = this.state.newDealObj;
        dealObj[name] = parseInt(value);
        this.setState({ newDealObj: dealObj })
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


    validateForm() {
        const { amount, real, bonus, coins } = this.state
        this.setState({ AddDealPosting: false })
        if ((!Number.isInteger(amount)) && !Number.isInteger(bonus) && !Number.isInteger(real) && !Number.isInteger(coins)) {
            this.setState({ AddDealPosting: true })
        }
    }

    createDeal() {
        const { newDealObj } = this.state
        this.setState({ AddDealPosting: false })

        WSManager.Rest(NC.baseURL + MODULE_DEAL.ADD_DEAL, newDealObj).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                notify.show(responseJson.message, "success", 3000);
                this.setState({
                    newDealObj: {}
                }, function () {
                    this.NewDealTogle(false);
                    this.getDeals();
                });
            }
            this.setState({ AddDealPosting: true })
        }).catch((error) => {
            this.setState({ AddDealPosting: true })
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        })
    }
    handlePageChange(current_page) {
        if (this.state.CURRENT_PAGE != current_page) {
            this.setState({
                CURRENT_PAGE: current_page
            }, () => {
                this.getDeals();
            });
        }
    }
    updateDealStatus = (idx, deal_id, status) => {
        this.setState({ ActionPosting: true })
        const param = {
            status: status,
            deal_unique_id: deal_id
        }
        WSManager.Rest(NC.baseURL + MODULE_DEAL.UPDATE_DEAL_STATUS, param).then((responseJson) => {
            this.setState({ ActionPosting: false })
            if (responseJson.response_code === NC.successCode) {
                notify.show(responseJson.message, "success", 5000);

                this.getDeals();
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    deleteToggle = (setFalg, idx, item, ) => {
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

    deleteDeal = () => {
        const { deleteIndex, deleteItem, DealList } = this.state
        this.setState({ DeleteActionPosting: true })
        const param = {
            deal_unique_id: deleteItem.deal_unique_id,
        }

        let tempDealList = DealList
        WSManager.Rest(NC.baseURL + MODULE_DEAL.DELETE_DEAL, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                _.remove(tempDealList, function (item, idx) {
                    return idx == deleteIndex
                })
                this.deleteToggle(false, {}, {})
                notify.show(responseJson.message, "success", 5000);
                this.setState({
                    DealList: tempDealList,
                })
            }
            this.setState({ DeleteActionPosting: false })
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    promoteDeal = (val) => {
        var params = {};
        params.promo_code_template_id = val.deal_template_id ? val.deal_template_id : '31';
        params.email_template_id = this.state.DealCatId;
        params.all_user = 1;
        params.deal_id = Base64.encode(val.deal_id);
        params.amt = Base64.encode(val.amount);
        params.for_str = ' For Deal '

        const stringified = queryString.stringify(params);
        this.props.history.push(`/marketing/new_campaign?${stringified}`);
        return false;
    }
    gotoDetails = (item) =>{
        this.props.history.push({ pathname: '/deals/deal_list/detail/' + item.deal_unique_id , state: { isCollection: item }})
  
    }

    render() {
        const { NewDealToggle, AddDealPosting, newDealObj, DealList, CURRENT_PAGE, TotalDeals, PERPAGE, ActionPosting, DeleteActionPosting } = this.state
        return (
            <Fragment>
                {
                    !NewDealToggle &&
                    <div className="mt-4 app-banner">
                        <Row>
                            <Col md={12}>
                                <h1 className="h1-cls">Add Deal</h1>
                            </Col>
                        </Row>
                        <Row className="filters-box">
                            <Col md={12}>
                                <div className="filters-area">
                                    <Button className="btn-secondary" onClick={() => this.NewDealTogle(true)}>New Deal</Button>
                                </div>
                            </Col>
                        </Row>
                        <Row className="animated fadeIn">
                            <Col md={12} className="table-responsive common-table">
                                <Table>
                                    <thead>
                                        <tr>
                                            <th className="left-th pl-4">Amount</th>
                                            <th>Real</th>
                                            <th>Bonus</th>
                                            {HF.allowCoin() == '1' && <th>Coins</th>}
                                            <th>Status</th>
                                            <th>Action</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    {
                                        _.map(DealList, (item, idx) => {
                                            return (
                                                <tbody key={idx}>
                                                    <tr>
                                                        <td className="pl-4">{item.amount}</td>
                                                        <td>
                                                            {item.cash}
                                                        </td>
                                                        <td>{item.bonus}</td>
                                                        {HF.allowCoin() == '1' && <td>{item.coin}</td>}
                                                        <td>
                                                            {item.status == 1 ?
                                                                <i className="icon-verified active"></i>
                                                                :
                                                                <i className="icon-inactive"></i>
                                                            }
                                                        </td>
                                                        <td>
                                                        <span className="details-view-text"
                                                        onClick={() => {this.gotoDetails(item)}}
                                                        // onClick={() => this.props.history.push('/deals/deal_list/detail' )}
                                                        >Detail View</span>
                                                            <UncontrolledDropdown>
                                                                <DropdownToggle disabled={ActionPosting} className="icon-action" />
                                                                <DropdownMenu>
                                                                    {item.status == 1
                                                                        ?
                                                                        <DropdownItem onClick={() => this.updateDealStatus(idx, item.deal_unique_id, 0)}>Inactive</DropdownItem>
                                                                        :
                                                                        <DropdownItem onClick={() => this.updateDealStatus(idx, item.deal_unique_id, 1)}>Active</DropdownItem>
                                                                    }
                                                                    <DropdownItem onClick={() => { this.deleteToggle(true, idx, item) }}>Delete</DropdownItem>
                                                                </DropdownMenu>
                                                            </UncontrolledDropdown>
                                                        </td>
                                                        <td>
                                                            {
                                                                item.status == 1 ?
                                                                    <span
                                                                        className={`btn-promote ${item.status == 1 ? '' : 'cursor-dis'}`}
                                                                        onClick={() => item.status == 1 ? this.promoteDeal(item) : null}
                                                                    >Promote</span>
                                                                    :
                                                                    <span>--</span>
                                                            }
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            )
                                        })
                                    }
                                </Table>
                            </Col>
                        </Row>
                        <div className="custom-pagination userlistpage-paging float-right">
                            <Pagination
                                activePage={CURRENT_PAGE}
                                itemsCountPerPage={PERPAGE}
                                totalItemsCount={TotalDeals ? TotalDeals : 0}
                                pageRangeDisplayed={5}
                                onChange={e => this.handlePageChange(e)}
                            />
                        </div>
                    </div>
                }
                {
                    NewDealToggle &&
                    <div className="mt-4">
                        <Row>
                            <Col md={12}>
                                <h1 className="h1-cls">New Deal</h1>
                            </Col>
                        </Row>
                        <div className="animated fadeIn new-banner">
                            <Col md={12} className="input-row">
                                <Row>
                                    <Col md={3} className="b-input-label">Amount<span className="asterrisk">*</span></Col>
                                    <Col md={9}>
                                        <Input
                                            type="number"
                                            name='amount'
                                            placeholder="Amount"
                                            onChange={this.handleNameChange}
                                            value={newDealObj.amount}
                                        />
                                    </Col>
                                </Row>
                            </Col>
                            <Col md={12} className="input-row">
                                <Row>
                                    <Col md={3} className="b-input-label">Real<span className="asterrisk">*</span></Col>
                                    <Col md={9}>
                                        <Input
                                            type="number"
                                            name='real'
                                            placeholder="Real"
                                            onChange={this.handleNameChange}
                                            value={newDealObj.real}
                                        />
                                    </Col>
                                </Row>
                            </Col>

                            <Col md={12} className="input-row">
                                <Row>
                                    <Col md={3} className="b-input-label">Bonus<span className="asterrisk">*</span></Col>
                                    <Col md={9}>
                                        <Input
                                            type="number"
                                            name='bonus'
                                            placeholder="Bonus"
                                            onChange={this.handleNameChange}
                                            value={newDealObj.bonus}
                                        />
                                    </Col>
                                </Row>
                            </Col>
                            {
                                HF.allowCoin() == '1' &&
                                <Col md={12} className="input-row">
                                    <Row>
                                        <Col md={3} className="b-input-label">Coins<span className="asterrisk">*</span></Col>
                                        <Col md={9}>
                                            <Input
                                                type="number"
                                                name='coins'
                                                placeholder="Coins"
                                                onChange={this.handleNameChange}
                                                value={newDealObj.coins}
                                            />
                                        </Col>
                                    </Row>
                                </Col>
                            }

                            <Col md={12} className="banner-action">
                                <Button disabled={!AddDealPosting} className="btn-secondary mr-3" onClick={() => this.createDeal()}>Save</Button>
                                <Button className="btn-secondary-outline" onClick={() => this.NewDealTogle(false)}>Cancel</Button>
                            </Col>
                        </div>
                    </div>
                }
                <div>
                    <Modal isOpen={this.state.DeleteModalOpen} toggle={this.deleteToggle}>
                        <ModalHeader>Delete Deal</ModalHeader>
                        <ModalBody>Are you sure to delete this Deal?</ModalBody>
                        <ModalFooter>
                            <Button disabled={DeleteActionPosting} color="secondary" onClick={() => this.deleteDeal()}>Yes</Button>{' '}
                            <Button color="primary" onClick={this.deleteToggle}>No</Button>
                        </ModalFooter>
                    </Modal>
                </div>
            </Fragment>
        )
    }
}