import React, { Component, Fragment } from "react";
import { Row, Col, Button, Table, Input, UncontrolledDropdown, DropdownToggle, DropdownMenu, DropdownItem, Modal, ModalBody, ModalHeader } from 'reactstrap';
import SelectDate from "../../components/SelectDate";
import WSManager from '../../helper/WSManager';
import * as NC from '../../helper/NetworkingConstants';
import Loader from '../../components/Loader';
import SelectDropdown from "../../components/SelectDropdown";
import Pagination from "react-js-pagination";
import { getErpMasterData, getErpTransactionList, saveErpTransaction, updateErpTransaction, deleteErpTransaction, getErpCategoryList, ErpSaveCategory, ErpUpdateCategory } from '../../helper/WSCalling';
import { notify } from 'react-notify-toast';
// import moment from 'moment';
import { _Map, _remove, _times, _isEmpty, _isUndefined } from '../../helper/HelperFunction';
import { MomentDateComponent } from "../../components/CustomComponent";
import HF from "../../helper/HelperFunction";
import ActionRequestModal from '../../components/ActionRequestModal/ActionRequestModal';
import { MSG_DEL_TRANS } from "../../helper/Message";
import moment from "moment-timezone";

const cateOptions = [
    { value: 1, label: 'Custom' },
    { value: 2, label: 'Current Week' },
    { value: 3, label: 'Current Month' },
    { value: 4, label: 'Last Month' },
]
class ERPTransactions extends Component {
    constructor(props) {
        super(props)
        this.state = {
            CURRENT_PAGE: 1,
            PERPAGE: NC.ITEMS_PERPAGE,
            FromDate: new Date(Date.now() - 10 * 24 * 60 * 60 * 1000),
            ToDate: new Date(moment().format('D MMM YYYY')),
            AddDate: new Date(moment().format('D MMM YYYY')),
            ListPosting: false,
            Total: 0,
            CatModalOpen: false,
            CategoriesType: '',
            CategoriesTypeOpt: [],
            selectedCateType: '',
            CategoryOpt: [],
            addTranPosting: false,
            Amount: '',
            Description: '',
            ModalCateType: '1',
            saveCatPosting: false,
            SortField: 'record_date',
            IsOrder: true,
        }
    }

    componentDidMount = () => {
        this.getMasterData()
        this.getTranList()
    }

    getTranList = () => {
        this.setState({ ListPosting: true })
        let { CURRENT_PAGE, PERPAGE, FromDate, ToDate, IsOrder, SortField, } = this.state
        let params = {
            "type": "",
            "category_id": "",
            "order_by": "DESC",
            "items_perpage": PERPAGE,
            "current_page": CURRENT_PAGE,
            "from_date": FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '',
            "to_date": ToDate ? moment(ToDate).format('YYYY-MM-DD') : '',
            "order_by": IsOrder ? "ASC" : 'DESC',
            "order_field": SortField,
        }
        getErpTransactionList(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    TransList: ResponseJson.data.result,
                    Total: ResponseJson.data.total,
                    ListPosting: false
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    exportList = () => {
        let { FromDate, ToDate, SortField, IsOrder } = this.state
        var query_string = ''

        let from_date = (FromDate) ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : ''
        let to_date = (ToDate) ? moment(ToDate).format("YYYY-MM-DD") : ''
        let order = IsOrder ? "ASC" : 'DESC'
        query_string = 'from_date=' + from_date + '&to_date=' + to_date + '&order_field=' + SortField + '&order_by=' + order;

        let sessionKey = WSManager.getToken();
        query_string += "&Sessionkey" + "=" + sessionKey;

        console.log('>>>>>>>', query_string)

        // window.open(NC.baseURL + 'adminapi/finance_erp/export_transaction?' + query_string, '_blank');
    }

    handlePageChange(current_page) {
        if (current_page != this.state.CURRENT_PAGE) {
            this.setState({
                CURRENT_PAGE: current_page
            }, () => {
                this.getTranList()
            });
        }
    }

    editTrans = (item) => {
        if (!_isEmpty(item)) {
            this.getCatList()
        }
        this.setState({
            Finance_id: item.finance_id,
            AddDate: new Date(item.record_date),
            Amount: item.amount,
            Description: item.description,
            selectedCate: item.category_id,
            selectedCateType: item.type,
        }, () => {
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            })
        })
    }

    handleCatType = (val) => {
        this.setState({
            ModalCateType: val
        })
    }

    createCatModal() {
        let { CategoryName, ModalCateType, CustomCategory, saveCatPosting, Category_id } = this.state
        return (
            <div>
                <Modal isOpen={this.state.CatModalOpen} toggle={() => this.catModalToggle()} className="erp-create-cat modal-md">
                    <ModalHeader>Create Categories</ModalHeader>
                    <ModalBody>
                        <Row>
                            <Col xs={6}>
                                <lable>Categories Name</lable>
                                <Input
                                    maxLength={30}
                                    type="text"
                                    name="CategoryName"
                                    value={CategoryName}
                                    onChange={(e) => this.handleInputChange(e)}
                                />
                                <div className="erp-t-max">Min 3 - Max 30 Character</div>
                            </Col>
                            <Col xs={6}>
                                <lable>Categories Type</lable>
                                <div className="input-box">
                                    <ul className="coupons-option-list">
                                        <li className="coupons-option-item">
                                            <div className="custom-radio mr-5">
                                                <input
                                                    type="radio"
                                                    className="custom-control-input"
                                                    name="ModalCateType"
                                                    value="1"
                                                    checked={ModalCateType === '1'}
                                                    onChange={() => this.handleCatType("1")}
                                                />
                                                <label className="custom-control-label">
                                                    <span className="input-text">Income</span>
                                                </label>
                                            </div>
                                        </li>
                                        <li className="coupons-option-item">
                                            <div className="custom-radio">
                                                <input
                                                    type="radio"
                                                    className="custom-control-input"
                                                    name="ModalCateType"
                                                    value="0"
                                                    checked={ModalCateType === '0'}
                                                    onChange={() => this.handleCatType("0")}
                                                />
                                                <label className="custom-control-label">
                                                    <span className="input-text">Expenses</span>
                                                </label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </Col>
                        </Row>
                        <Row className="mt-4 text-center">
                            <Col md={12}>
                                {
                                    !_isEmpty(Category_id) &&
                                    <Button
                                        disabled={saveCatPosting}
                                        className="btn-secondary-outline erp-add-cat"
                                        onClick={() => this.CancelCatUpdate()}>
                                        Cancel
                                    </Button>
                                }
                                <Button
                                    disabled={saveCatPosting}
                                    className="btn-secondary-outline erp-add-cat ml-2"
                                    onClick={() => this.saveCategory()}>
                                    {!_isEmpty(Category_id) ? 'Update' : 'Add'}
                                </Button>
                            </Col>
                        </Row>
                        <Row className="mb-5">
                            <Col md={12}>
                                <div className="erp-recent-cat">Recent Categories</div>
                                <div className="erp-c-list">
                                    <ul className="erp-c-box">
                                        {
                                            _Map(CustomCategory, (item, idx) => {
                                                return (
                                                    <li key={idx} className="erp-c-row clearfix">
                                                        <div className="erp-c-info">
                                                            <div className="erp-c-name float-left">{item.name}</div>
                                                            <div className={`erp-c-name ${item.type == 0 ? 'exp' : 'income'}`}>
                                                                {item.type == '0' && 'Expenses'}
                                                                {item.type == '1' && 'Income'}
                                                                {item.type == '2' && 'Liabilities'}
                                                            </div>
                                                            <div className="erp-c-edit">
                                                                <i onClick={() => this.editCategory(item)} className="icon-edit"></i>
                                                            </div>
                                                        </div>
                                                    </li>
                                                )
                                            })
                                        }
                                    </ul>
                                </div>
                            </Col>
                        </Row>
                    </ModalBody>
                </Modal>
            </div>
        )
    }

    catModalToggle() {
        this.setState({
            CatModalOpen: !this.state.CatModalOpen
        }, () => {
            if (this.state.CatModalOpen) {
                this.getCatList()
            }
        });
    }

    toggleActionPopup = (finance_id, idx) => {
        this.setState({
            Message: MSG_DEL_TRANS,
            idxVal: idx,
            FinID: finance_id,
            ActionPopupOpen: !this.state.ActionPopupOpen
        })
    }

    deleteTrans = () => {
        let { FinID } = this.state
        this.setState({ delPosting: true })
        let params = {
            finance_id: FinID,
        }
        let TempQList = this.state.TransList
        deleteErpTransaction(params).then(Response => {
            if (Response.response_code == NC.successCode) {
                _remove(TempQList, (item) => {
                    return item.finance_id == FinID
                })
                this.setState({
                    TransList: TempQList,
                    delPosting: false,
                    ActionPopupOpen: false
                })

                notify.show(Response.message, 'success', 5000)
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    handleDate = (date, dateType) => {
        this.setState({ [dateType]: date, CURRENT_PAGE: 1 }, () => {
            if (dateType != 'AddDate')
                this.getTranList()
        })
    }

    handleInputChange = (e) => {
        if (e) {
            let name = e.target.name
            let value = e.target.value
            this.setState({ [name]: value })
            if (name == 'Amount' && (value.length < 1 || value.length > 8)) {
                this.setState({ [name]: '' })
                notify.show("Please enter amount between 1 to 8 digit", "error", 3000)
            }
            else if (name == 'Description' && (_isEmpty(value) && value.length < 3)) {
                notify.show("Please enter description between 3 to 150 chanracter", "error", 3000)
            }
            else if (name == 'CategoryName' && ((_isEmpty(value) || value.length < 3 || value.length > 30))) {
                notify.show("Please enter category name between 3 to 30 chanracter", "error", 3000)
            }
        }
    }

    getMasterData = () => {
        let params = {}
        getErpMasterData(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                let pCate = []
                if (!_isUndefined(ResponseJson.data.type)) {
                    _Map(ResponseJson.data.type, function (CFormat) {
                        if (CFormat.id != '2') {
                            pCate.push({
                                value: CFormat.id,
                                label: CFormat.name
                            });
                        }
                    })

                }
                this.setState({
                    CategoriesTypeOpt: pCate
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    getCatList = () => {
        let { selectedCateType } = this.state
        let params = {
            "type": selectedCateType,
            "is_custom": "1",
            "order_by": "DESC"
        }
        getErpCategoryList(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                let pCateL = []
                if (!_isUndefined(ResponseJson.data)) {
                    _Map(ResponseJson.data, function (CFormat) {
                        pCateL.push({
                            value: CFormat.category_id,
                            label: CFormat.name
                        });
                    })
                }
                this.setState({
                    CategoryOpt: pCateL,
                    CustomCategory: ResponseJson.data
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    handleCatChange = (value) => {
        this.setState({ selectedCateType: value.value }, () => {
            this.getCatList()
        })
    }

    handleCatTypeChange = (value) => {
        this.setState({ selectedCate: value.value })
    }

    AddTrans = () => {
        let { selectedCateType, selectedCate, Amount, Description, AddDate, Finance_id } = this.state

        if (Amount.length < 1 || Amount.length > 8) {
            notify.show("Please enter amount between 1 to 8 digit", "error", 3000)
            return false
        }
        else if (_isEmpty(Description) || Description.length < 3) {
            notify.show("Please enter description between 3 to 150 chanracter", "error", 3000)
            return false
        }
        else if (_isEmpty(selectedCateType)) {
            notify.show("Please select category type", "error", 3000)
            return false
        }
        else if (_isEmpty(selectedCate)) {
            notify.show("Please select category", "error", 3000)
            return false
        }

        let params = {
            "category_id": selectedCate,
            "amount": Amount,
            "description": Description,
            "record_date": AddDate ? moment(AddDate).format('YYYY-MM-DD') : ''
        }
        let Api = saveErpTransaction
        if (!_isEmpty(Finance_id)) {
            params.finance_id = Finance_id
            Api = updateErpTransaction
        }
        this.setState({ addTranPosting: true })
        Api(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                notify.show(ResponseJson.message, "success", 3000)
                this.setState({
                    selectedCateType: '',
                    selectedCate: '',
                    Amount: '',
                    Description: '',
                    AddDate: new Date(moment().format('YYYY-MM-DD')),
                    Finance_id: '',
                    addTranPosting: false
                })
                this.getTranList()
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    saveCategory = () => {
        let { Category_id, CategoryName, ModalCateType } = this.state

        if (_isEmpty(CategoryName) || CategoryName.length < 3) {
            notify.show("Please enter category name between 3 to 30 chanracter", "error", 3000)
            return false
        }

        let params = {
            "name": CategoryName,
            "type": ModalCateType
        }
        this.setState({ saveCatPosting: true })

        let Api = ErpSaveCategory
        if (!_isEmpty(Category_id)) {
            params.category_id = Category_id
            Api = ErpUpdateCategory
        }
        Api(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                notify.show(ResponseJson.message, "success", 3000)
                this.getCatList()
                this.setState({
                    CategoryName: '',
                    ModalCateType: '1',
                    Category_id: '',
                    saveCatPosting: false
                })
                this.getTranList()
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    editCategory = (item) => {
        this.setState({
            Category_id: item.category_id,
            ModalCateType: item.type,
            CategoryName: item.name,
        }, () => {
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            })
        })
    }

    CancelUpdate = () => {
        this.setState({
            Finance_id: '',
            AddDate: new Date(),
            Amount: '',
            Description: '',
            selectedCate: '',
            selectedCateType: '',
        });
    }

    CancelCatUpdate = () => {
        this.setState({
            ModalCateType: '1',
            Category_id: '',
            CategoryName: '',
        });
    }

    sortTransList = (sortfiled, IsOrder) => {
        let Order = (sortfiled == this.state.SortField) ? !IsOrder : IsOrder
        this.setState({
            SortField: sortfiled,
            IsOrder: Order,
            CURRENT_PAGE: 1,
        }, this.getTranList
        )
    }

    render() {
        let { FromDate, ToDate, Total, ListPosting, selectedCateType, AddDate, CURRENT_PAGE, PERPAGE, TransList, delPosting, Message, ActionPopupOpen, Amount, Description, CategoriesType, CategoriesTypeOpt, CategoryOpt, selectedCate, addTranPosting, Finance_id, IsOrder, SortField } = this.state
        // moment.tz.setDefault(HF.getMasterData().timezone);
        var todaysDate = moment().format('D MMM YYYY');

        const ActionCallback = {
            posting: delPosting,
            Message: Message,
            modalCallback: this.toggleActionPopup,
            ActionPopupOpen: ActionPopupOpen,
            modalActioCallback: this.deleteTrans,
        }

        const SelectCatType_Props = {
            is_disabled: false,
            is_searchable: true,
            is_clearable: false,
            menu_is_open: false,
            class_name: "",
            sel_options: CategoriesTypeOpt,
            place_holder: "",
            selected_value: selectedCateType,
            modalCallback: this.handleCatChange
        }

        const SelectCat_Props = {
            is_disabled: false,
            is_searchable: true,
            is_clearable: false,
            menu_is_open: false,
            class_name: "",
            sel_options: CategoryOpt,
            place_holder: "",
            selected_value: selectedCate,
            modalCallback: this.handleCatTypeChange
        }

        const sameDateProp = {
            disabled_date: false,
            show_time_select: false,
            time_format: false,
            time_intervals: false,
            time_caption: false,
            date_format: 'dd/MM/yyyy',
            handleCallbackFn: this.handleDate,
            class_name: 'epr-datep mr-3',
            year_dropdown: true,
            month_dropdown: true,
        }
        const FromDateProps = {
            ...sameDateProp,
            min_date: false,
            max_date: new Date(ToDate),
            sel_date: new Date(FromDate),
            date_key: 'FromDate',
            place_holder: 'From Date',
        }
        const ToDateProps = {
            ...sameDateProp,
            min_date: new Date(FromDate),
            max_date: todaysDate,
            sel_date: ToDate,
            date_key: 'ToDate',
            place_holder: 'To Date',
        }

        const AddDateProps = {
            ...sameDateProp,
            min_date: false,
            max_date: todaysDate,
            sel_date: AddDate,
            date_key: 'AddDate',
            place_holder: '',
        }
        return (
            <div className="erp-trans">
                <ActionRequestModal {...ActionCallback} />
                {this.createCatModal()}
                <Row>
                    <Col md={12}>
                        <div className="float-left">
                            <h2 className="h2-cls mt-2">Custom Transactions</h2>
                        </div>
                        <div className="float-right">
                            <Button
                                className="btn-secondary-outline"
                                onClick={() => this.catModalToggle()}>
                                Create Categories
                            </Button>
                        </div>
                    </Col>
                </Row>
                <Row className="mt-30">
                    <Col md={12}>
                        <div className="float-left">
                            <div className="float-left">
                                <label htmlFor="fquaters">Date From</label>
                                <SelectDate
                                    DateProps={FromDateProps} />
                            </div>
                            <div className="float-left">
                                <label htmlFor="fquaters">Date To</label>
                                <SelectDate DateProps={ToDateProps} />
                            </div>
                        </div>
                        <div className="cursor-pointer float-right">
                            <i className="export-list icon-export" onClick={e => this.exportList()}></i>
                        </div>
                    </Col>
                </Row>
                <Row>
                    <Col md={12} className="table-responsive common-table">
                        <Table className="mb-0">
                            <thead>
                                <tr>
                                    <th
                                        className="left-th pl-3 pointer"
                                        onClick={() => this.sortTransList('record_date', IsOrder)}
                                    >
                                        Date<span className={(IsOrder && SortField === 'record_date') ? "arrow-up" : "arrow-down"}></span>
                                    </th>
                                    <th
                                        className="pointer"
                                        onClick={() => this.sortTransList('amount', IsOrder)}>
                                        Amount
                                        <span className={(IsOrder && SortField === 'amount') ? "arrow-up" : "arrow-down"}></span>
                                    </th>
                                    <th>Descriptions</th>
                                    <th
                                        className="text-center pointer"
                                        onClick={() => this.sortTransList('type', IsOrder)}
                                    >Categories Type
                                        <span className={(IsOrder && SortField === 'type') ? "arrow-up" : "arrow-down"}></span>
                                    </th>
                                    <th className="text-center">
                                        Categories
                                    </th>
                                    <th className="right-th pl-20"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td className="pl-3">
                                        <SelectDate
                                            DateProps={AddDateProps} />
                                    </td>
                                    <td>
                                        <Input
                                            name="Amount"
                                            type="number"
                                            value={Amount}
                                            onChange={(e) => this.handleInputChange(e)}
                                        />
                                    </td>
                                    <td>
                                        <Input
                                            maxLength="150"
                                            name="Description"
                                            type="text"
                                            value={Description}
                                            onChange={(e) => this.handleInputChange(e)}
                                        />
                                        <div className="erp-t-max">Max 150 Character</div>
                                    </td>
                                    <td><SelectDropdown SelectProps={SelectCatType_Props} /></td>
                                    <td className="text-center wdt-box">
                                        <SelectDropdown SelectProps={SelectCat_Props} />
                                    </td>
                                    <td className="tran-add-btn">
                                        {
                                            !_isEmpty(Finance_id) &&
                                            <Button
                                                disabled={addTranPosting}
                                                className="btn-secondary-outline"
                                                onClick={() => this.CancelUpdate()}>
                                                Cancel
                                            </Button>
                                        }
                                        <Button
                                            disabled={addTranPosting}
                                            className="btn-secondary-outline ml-2"
                                            onClick={() => this.AddTrans()}>
                                            {!_isEmpty(Finance_id) ? 'Update' : 'Add'}
                                        </Button>
                                    </td>
                                </tr>
                            </tbody>
                            {
                                Total > 0 ?
                                    _Map(TransList, (item, idx) => {
                                        return (
                                            <tbody key={idx}>
                                                <tr>
                                                    <td className="pl-3">
                                                        {/* <MomentDateComponent data={{ date: item.record_date, format: "D-MMM-YYYY" }} /> */}
                                                        {HF.getFormatedDateTime(item.record_date, "D-MMM-YYYY")}

                                                    </td>
                                                    <td>
                                                        {HF.getCurrencyCode()}
                                                        {item.amount != 0 ? HF.getNumberWithCommas(item.amount) : 0}
                                                    </td>
                                                    <td>
                                                        <div className="erp-desc">
                                                            {item.description}
                                                        </div>
                                                    </td>
                                                    <td className={`erp-tr-type ${item.type == 0 ? 'exp' : 'income'}`}>
                                                        {item.type == '0' && 'Expenses'}
                                                        {item.type == '1' && 'Income'}
                                                        {item.type == '2' && 'Liabilities'}
                                                    </td>
                                                    <td className="text-center">{item.category_name}</td>
                                                    <td>
                                                        <div className="erp-t-action text-center">
                                                            <UncontrolledDropdown direction="left">
                                                                <DropdownToggle tag="i" caret={false} className="icon-more cursor-pointer">
                                                                </DropdownToggle>
                                                                <DropdownMenu>
                                                                    {
                                                                        <DropdownItem
                                                                            onClick={() => this.editTrans(item, idx)}
                                                                        >Edit
                                                                        </DropdownItem>
                                                                    }
                                                                    <DropdownItem
                                                                        onClick={() => this.toggleActionPopup(item.finance_id, idx)}
                                                                    >Delete
                                                                    </DropdownItem>
                                                                </DropdownMenu>
                                                            </UncontrolledDropdown>
                                                        </div>
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
            </div>
        )
    }
}
export default ERPTransactions