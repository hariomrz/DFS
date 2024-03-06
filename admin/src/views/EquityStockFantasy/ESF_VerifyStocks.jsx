import React, { Component, Fragment } from "react";
import { Row, Col, Button, Input, Table, Modal, ModalBody, ModalFooter, ModalHeader } from 'reactstrap';
import HF, { _remove, _Map, _debounce, _isEmpty, _isUndefined, _isNull, _cloneDeep } from "../../helper/HelperFunction";
import Loader from '../../components/Loader';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import SelectDropdown from "../../components/SelectDropdown";
import PromptModal from '../../components/Modals/PromptModal';
import { ESF_getStockVerify, ESF_publishFixture, ESF_save, ESF_update, ESF_updateFixture } from '../../helper/WSCalling';
import { XP_DELETE_LEVEL, XP_DELETE_LEVEL_SUB } from "../../helper/Message";
import Images from '../../components/images';
import Pagination from "react-js-pagination";
import ESF_FixtureCard from './ESF_FixtureCard';
import { TITLE_PUBLISH_MATCH, MSG_PUBLISH_MATCH } from "../../helper/Message";
import moment from 'moment';
class ESF_VerifyStocks extends Component {
    constructor(props) {
        super(props)
        this.state = {
            collection_id: (this.props.match.params.collection_id) ? this.props.match.params.collection_id : '1',
            ActiveFxType: (this.props.match.params.category) ? this.props.match.params.category : '1',
            ActiveTab: (this.props.match.params.activeTab) ? this.props.match.params.activeTab : '1',
            FixtureValue: (this.props.match.params.fxvalue) ? this.props.match.params.fxvalue : '1',
            FixtureName: (this.props.match.params.fxname) ? this.props.match.params.fxname : '1',
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            sortField: 'display_name',
            isDescOrder: false,
            StockList: [],
            ListPosting: true,
            StockDispName: '',
            CreatePosting: false,
            newIdArr: [],
            selectedUsers: [],
            PublishModalIsOpen: false,
            FixturePosting: false,
            ScheduledDate: new Date(),
            SelectAllStk: false,
        }
    }

    componentDidMount() {

        if (HF.allowEquityFantasy() == '0') {
            notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        this.getStockList();
    }

    getStockList = () => {
        this.setState({ ListPosting: true })
        let params = {
            collection_id: this.state.collection_id
        }
        ESF_getStockVerify(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                let slist = ResponseJson.data ? ResponseJson.data.stocks : []

                _Map(slist, (item, idx) => {
                    if (item['is_published'] == "1") {
                        slist[idx]['disabled'] = true
                    } else {
                        slist[idx]['disabled'] = false
                    }
                })
                this.setState({
                    StockList: slist,
                    Total: ResponseJson.data.total ? ResponseJson.data.total : 0,
                    ListPosting: false,
                    ScheduledDate: ResponseJson.data.scheduled_date ? ResponseJson.data.scheduled_date : this.state.FixtureValue,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    handleInputChange = (event, idx) => {
        let stk_list = this.state.StockList
        let name = event.target.name
        let value = event.target.value

        if (name == 'display_name') {
            value = value.replace(/  +/g, ' ')
        }

        // if (name == 'lot_size' && value < 1 || value > 99999) {
        //     let msg = 'Lot size should be in the range of 1 to 99999'
        //     notify.show(msg, 'error', 3000)
        //     value = ''
        // }
        else if (name == 'display_name' && (value.length < 3 || value.length > 50)) {
            let msg = 'Display name should be in the range of 3 to 50'
            notify.show(msg, 'error', 3000)
        }

        stk_list[idx][name] = value
        this.setState({ StockList: stk_list })
    }

    validForm = () => {
        let { LotSize, StockDispName, StockIconName, SearchStock, EditFlag } = this.state
        let r_flag = true
        if (!EditFlag && _isEmpty(SearchStock)) {
            let msg = 'Please select stock'
            notify.show(msg, 'error', 3000)
            r_flag = false
        }
        else if (_isEmpty(StockIconName)) {
            let msg = 'Please upload stock logo'
            notify.show(msg, 'error', 3000)
            r_flag = false
        }
        else if (_isEmpty(StockDispName) || StockDispName.length < 3 || StockDispName.length > 50) {
            let msg = 'Display name should be in the range of 3 to 50'
            notify.show(msg, 'error', 3000)
            r_flag = false
        }
        // else if (LotSize < 1 || LotSize > 99999) {
        //     let msg = 'Lot size should be in the range of 1 to 99999'
        //     notify.show(msg, 'error', 3000)
        //     r_flag = false
        // }
        return r_flag
    }

    handlePageChange(current_page) {
        if (current_page != this.state.CURRENT_PAGE) {
            this.setState({
                CURRENT_PAGE: current_page
            }, () => {
                this.getStockList()
            });
        }
    }

    selectOneUser = (idx) => {
        let tempStkArr = _cloneDeep(this.state.StockList);
        if (tempStkArr[idx]['is_published'] == "1") {
            tempStkArr[idx]['is_published'] = "0"
            tempStkArr[idx]['publish_flag'] = false
            this.setState({ SelectAllStk: false });
        } else {
            tempStkArr[idx]['is_published'] = "1"
            tempStkArr[idx]['publish_flag'] = true
            this.setState({ SelectAllStk: true });
            _Map(tempStkArr, (sl, idx) => {
                if (sl.is_published == "0")
                    this.setState({ SelectAllStk: false });
            })
        }
        this.setState({ StockList: tempStkArr });
    }

    PublishMatchModalToggle = () => {
        this.setState({
            PublishModalIsOpen: !this.state.PublishModalIsOpen,
        });
    }

    publishMatchModal = () => {
        let { FixturePosting } = this.state
        return (
            <div>
                <Modal
                    isOpen={this.state.PublishModalIsOpen}
                    toggle={this.PublishMatchModalToggle}
                    className="cancel-match-modal"
                >
                    <ModalHeader>{TITLE_PUBLISH_MATCH}</ModalHeader>
                    <ModalBody>
                        <div className="confirm-msg">{MSG_PUBLISH_MATCH}</div>
                    </ModalBody>
                    <ModalFooter>
                        <Button
                            color="secondary"
                            onClick={this.publishFixture}
                            disabled={FixturePosting}
                        >Yes</Button>{' '}
                        <Button color="primary" onClick={this.PublishMatchModalToggle}>No</Button>
                    </ModalFooter>
                </Modal>
            </div>
        )
    }

    publishFixture = () => {
        let { StockList, ActiveFxType, ActiveTab, FixtureValue, collection_id, FixtureName } = this.state
        let st_list = StockList        
        let p_arr = []
        let obj = {}
        let ret_flag = false
        _Map(st_list, (itm) => {
            // if (_isEmpty(itm.lot_size) || itm.lot_size < 1 || itm.lot_size > 99999) {
            //     let msg = 'Lot size should be in the range of 1 to 99999'
            //     notify.show(msg, 'error', 3000)
            //     ret_flag = true
            // }
            // else if (_isEmpty(itm.display_name) || itm.display_name.length < 3) {
            if (_isEmpty(itm.display_name) || itm.display_name.length < 3) {
                let msg = 'Display name should be in the range of 3 to 50'
                notify.show(msg, 'error', 3000)
                ret_flag = true
            }

            if (itm.publish_flag) {
                obj = {
                    "stock_id": itm.stock_id,
                    "name": itm.display_name,
                    // "lot_size": itm.lot_size
                }
                p_arr.push(obj)
            }
        })

        if (collection_id == '0' && (p_arr.length < 1)) {
            this.setState({ PublishModalIsOpen: false })
            notify.show("Please select minimum 1 stock", "error", 3000)
            return false
        }
        else if (ret_flag) {
            this.setState({ PublishModalIsOpen: false })
            return false
        }
        let fx_val = FixtureValue
        if (ActiveFxType == '1')
        {
            fx_val = moment(FixtureValue).format('YYYY-MM-DD')
        }
        let URL = '';
        let params = {
            "name": this.getFxName(FixtureName),
            "stocks": p_arr,
            "stock_type": "2",
        }        
        if(collection_id == '0')
        {
            URL = ESF_publishFixture
            params.category_id = parseInt(ActiveFxType)
            params.value = fx_val
        }else{
            URL = ESF_updateFixture
            params.collection_id = collection_id
        }
        this.setState({ FixturePosting: true })
        URL(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    PublishModalIsOpen: false,
                    // FixturePosting: false,
                })
                if (collection_id == '0') {
                    this.props.history.push({ pathname: '/equitysf/createtemplatecontest/' + ActiveFxType + '/' + ActiveTab + '/' + FixtureValue + '/' + ResponseJson.data.collection_id });
                } else {
                    this.props.history.push('/equitysf/fixture?pctab=' + ActiveFxType + '&tab=' + ActiveTab)                
                }
            } else {
                this.setState({ PublishModalIsOpen: false })
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
            this.setState({ FixturePosting: false })
        }).catch(error => {
            this.setState({ PublishModalIsOpen: false })
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    selectAllStk = () => {
        let { StockList } = this.state
        let tempStkArr = _cloneDeep(StockList);
        _Map(tempStkArr, (templist, idx) => {
            if (!tempStkArr[idx]['disabled'] && tempStkArr[idx]['is_published'] == "1") {
                if (this.state.SelectAllStk) {
                    tempStkArr[idx]['is_published'] = "0"
                    tempStkArr[idx]['publish_flag'] = false
                }

                this.setState({ SelectAllStk: false });
            } else {
                tempStkArr[idx]['is_published'] = "1"
                this.setState({ SelectAllStk: true });
                if (!tempStkArr[idx]['disabled'])
                    tempStkArr[idx]['publish_flag'] = true
            }
        })
        this.setState({ StockList: tempStkArr });
    }
    getFxName = (FixtureName) => {
        return FixtureName == 0 ? '' : FixtureName
    }

    render() {
        let { StockList, Total, ListPosting, DeleteModalOpen, DeletePosting, CURRENT_PAGE, PERPAGE, ActiveFxType, ActiveTab, FixtureValue, collection_id, ScheduledDate, SelectAllStk, FixtureName } = this.state

        let DeleteModalProps = {
            publishModalOpen: DeleteModalOpen,
            publishPosting: DeletePosting,
            modalActionNo: this.deleteToggle,
            modalActionYes: this.deleteStock,
            MainMessage: XP_DELETE_LEVEL,
            SubMessage: XP_DELETE_LEVEL_SUB,
        }

        let fx_item = {
            scheduled_date: ScheduledDate,
            week: FixtureValue,
            month: FixtureValue,
            name: this.getFxName(FixtureName),
        }
        return (
            <div className="verify-stocks">
                {DeleteModalOpen && <PromptModal {...DeleteModalProps} />}
                {this.publishMatchModal()}
                <Row className="mt-30">
                    <Col md={6}>
                        <ESF_FixtureCard
                            callfrom={'2'}
                            activeFxTab={ActiveFxType}
                            activeTab={ActiveTab}
                            edit={false}
                            item={fx_item}
                            redirectToTemplate={null}
                            redirectToStockReview={null}
                            redirectToUpdateStock={null}
                            openMsgModal={null}
                            openDelayModal={null}
                            show_flag={collection_id == '0' ? false : true}
                        />
                    </Col>
                    <Col md={6}>
                        <label className="back-to-fixtures" onClick={() => this.props.history.push('/equitysf/fixture?pctab=' + ActiveFxType + '&tab=' + ActiveTab)}> {'<'} Back to Fixtures</label>
                    </Col>
                </Row>
                <Row className="mt-30">
                    <Col md={12}>
                        <h2 className="h2-cls">Verify NSE Stocks</h2>
                    </Col>
                </Row>
                <Row>
                    <Col md={12}>
                        <label className="select-all-checkbox">
                            <Input
                                type="checkbox"
                                name="SelectAllStk"
                                checked={SelectAllStk}
                                onChange={() => this.selectAllStk()}
                            />
                            <span>Select All Stock</span>
                        </label>
                    </Col>
                </Row>
                <Row className="mt-30">
                    <Col md={12} className="table-responsive common-table">
                        <Table>
                            <thead className="height-40">
                                <tr>
                                    <th>Select</th>
                                    <th>Stock Name</th>
                                    <th>Trading symbol</th>
                                    <th>Display Name</th>
                                    {/* <th>Token</th> */}
                                    <th>Logo</th>
                                    {/* <th>Lot Size</th> */}
                                </tr>
                            </thead>
                            {
                                (!_isEmpty(StockList) && StockList.length > 0) ?
                                    _Map(StockList, (item, idx) => {
                                        return (
                                            <tbody key={idx}>
                                                <tr>
                                                    <td>
                                                        <Input
                                                            disabled={item.disabled}
                                                            type="checkbox"
                                                            name="SelectUsers"
                                                            checked={item.is_published == '1' ? true : false}
                                                            onClick={() => this.selectOneUser(idx)}
                                                        />
                                                    </td>
                                                    <td>{item.name}</td>
                                                    <td>{item.trading_symbol}</td>
                                                    <td>
                                                        {
                                                            item.disabled ?
                                                                item.display_name
                                                                :
                                                                <Input
                                                                    maxLength="50"
                                                                    className="salary-input w-100"
                                                                    type="text"
                                                                    value={item.display_name}
                                                                    name='display_name'
                                                                    onChange={e => this.handleInputChange(e, idx)}
                                                                />
                                                        }
                                                    </td>
                                                    {/* <td>{item.exchange_token}</td> */}
                                                    <td>
                                                        <div className="s-logo">
                                                            <img src={item.logo ? NC.S3 + NC.STOCK_PATH + item.logo : Images.no_image} className="img-cover" alt="" />
                                                        </div>
                                                    </td>
                                                    {/* <td>
                                                        {
                                                            item.disabled ?
                                                                item.lot_size
                                                                :
                                                                <Input
                                                                    className="salary-input w-100"
                                                                    type="text"
                                                                    value={item.lot_size}
                                                                    name='lot_size'
                                                                    onChange={e => this.handleInputChange(e, idx)}
                                                                />
                                                        }
                                                    </td> */}
                                                </tr>
                                            </tbody>
                                        )
                                    })
                                    :
                                    <tbody>
                                        <tr>
                                            <td colSpan="8">
                                                {((!_isEmpty(StockList) && StockList.length == 0) && !ListPosting) ?
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
                            Total > PERPAGE &&
                            (<div className="custom-pagination float-right mt-5">
                                <Pagination
                                    activePage={CURRENT_PAGE}
                                    itemsCountPerPage={PERPAGE}
                                    totalItemsCount={Total}
                                    pageRangeDisplayed={5}
                                    onChange={e => this.handlePageChange(e)}
                                />
                            </div>)
                        }
                    </Col>
                </Row>
                <Row className="text-center mt-56">
                    <Col md={12}>
                        <Button
                            className="btn-secondary-outline rebuplish-btn"
                            onClick={this.PublishMatchModalToggle}
                        >
                            {collection_id != "0" ?
                                'Republish'
                                :
                                'Verify and Publish'
                            }
                        </Button>
                    </Col>
                </Row>
            </div>
        )
    }
}
export default ESF_VerifyStocks

