import React, { Component, Fragment } from "react";
import { Row, Col, Button, Input, Table, Modal, ModalBody, ModalFooter, ModalHeader } from 'reactstrap';
import HF, { _remove, _Map, _debounce, _isEmpty, _isUndefined, _isNull } from "../../helper/HelperFunction";
import Loader from '../../components/Loader';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import SelectDropdown from "../../components/SelectDropdown";
import PromptModal from '../../components/Modals/PromptModal';
import { SF_uploadStockLogo, SF_list, SF_lot, SF_delete, SF_save, SF_update, SF_autoSuggetionList } from '../../helper/WSCalling';
import { XP_DELETE_LEVEL, XP_DELETE_LEVEL_SUB } from "../../helper/Message";
import Images from '../../components/images';
import Pagination from "react-js-pagination";
class SF_ManageStock extends Component {
    constructor(props) {
        super(props)
        this.state = {
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            sortField: 'display_name',
            isDescOrder: false,
            StockList: [],
            ListPosting: true,
            addStockModalOpen: false,
            SearchStock : '',
            StockDispName : '',
            LotSize : '',
            Keyword : '',
            LotList : [],
            SelectedLot : '',
            StockIconName : '',
            SuggestionsList : [],
            CreatePosting : false,
            StockLimit : '',
        }
        this.SearchCodeReq = _debounce(this.SearchCodeReq.bind(this), 500);
    }

    componentDidMount() {
        if (HF.allowStockFantasy() == '0') {
            notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        this.getStockList();
        this.getLot();
    }

    getStockList = () => {
        this.setState({ ListPosting: true })
        const { PERPAGE, CURRENT_PAGE, isDescOrder, sortField, Keyword, SelectedLot } = this.state
        let params = {
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
            sort_order: isDescOrder ? 'DESC' : 'ASC',
            sort_field: sortField,
            keyword: Keyword,
            // lot_size: SelectedLot,
        }
        
        SF_list(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    StockList: ResponseJson.data ? ResponseJson.data.stock_list : [],
                    Total: ResponseJson.data.total ? ResponseJson.data.total : 0,
                    ListPosting: false,
                    StockLimit: ResponseJson.data.stock_limit ? ResponseJson.data.stock_limit : 0,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }
    
    getLot = () => {
        SF_lot({}).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                const Temp = []
                _Map(ResponseJson.data, (item) => {
                    Temp.push({
                        value: item.lot_size, label: item.lot_size
                    })
                })
                this.setState({ LotList: Temp })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    searchByUser = (e) => {
        this.setState({ Keyword: e.target.value, CURRENT_PAGE : 1 }, this.SearchCodeReq)
    }

    SearchCodeReq() {
        this.getStockList()
    }

    handleLotChange = (value) => {
        this.setState({ SelectedLot: value.value, CURRENT_PAGE: 1 }, () => {
            this.getStockList()
        })
    }

    deleteToggle = (del_id, idx) => {
        this.setState(prevState => ({
            delIdx: idx,
            DELETE_S_ID: del_id,
            DeleteModalOpen: !prevState.DeleteModalOpen
        }));
    }

    deleteStock = () => {
        this.setState({ DeletePosting: true })
        const { delIdx, DELETE_S_ID, StockList } = this.state
        const param = { stock_id: DELETE_S_ID }
        let t_stk_list = StockList

        SF_delete(param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                _remove(t_stk_list, function (item, idx) {
                    return idx == delIdx
                })
                notify.show(responseJson.message, "success", 5000);
                this.setState({ StockList: t_stk_list })
            }
            this.setState({
                DeletePosting: false,
                DeleteModalOpen: false,
            })
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 5000);
        })
    }

    addStockModal = () => {
        let { StockIcon, CreatePosting, StockDispName, EditFlag, LotSize, SearchStock } = this.state
        return (
            <div>
                <Modal className="sf-add-modal sf-addcon-cat-mod" isOpen={this.state.addStockModalOpen}
                    toggle={this.addStockToggle}>
                    <ModalHeader>{EditFlag ? StockDispName : 'Add Stock'}</ModalHeader>
                    <ModalBody>
                        {
                            !EditFlag &&
                            <Row>
                                <Col md={12}>
                                    <label className="mt-0">Search Stock</label>
                                    <Input
                                        maxLength="25"
                                        type="text"
                                        name="SearchStock"
                                        value={SearchStock}
                                        placeholder="Search Stock"
                                        onChange={(e) => this.handleSearchStock(e)}
                                    />
                                    <i className="icon-search"></i>
                                    {!_isEmpty(this.state.SuggestionsList) && this.searchList()}
                                </Col>
                            </Row>
                        }
                        <Row>
                            <Col md={12}>
                                <label htmlFor="Redeem">Upload Logo <span className="i-size"> (Max 200x200)</span></label>
                                     <div className="sf-image">
                                            {!_isEmpty(StockIcon) ?
                                                <Fragment>
                                                    <i onClick={this.resetFile} className="icon-close"></i>
                                                    <img className="img-cover" src={StockIcon} />
                                                </Fragment>
                                                :
                                                <Fragment>                                                    
                                                    <img className="def-addphoto" src={Images.DEF_ADDPHOTO} alt="" />
                                                    <div className="sf-icon-txt">Drop your image here, or  
                                                        <span className="sf-browse">browse</span>
                                                        <Input
                                                        accept="image/x-png,
                                                        image/jpeg,image/jpg"
                                                        type="file"
                                                        name='StockIcon'
                                                        id="StockIcon"
                                                        onChange={this.onChangeImage} />
                                                    </div>
                                                </Fragment>
                                            }
                                        </div>
                                </Col>
                        </Row>
                        <Row>
                            <Col md={12}>
                                <label>Display Name</label>
                                <Input
                                    maxLength="50"
                                    type="text"
                                    name="StockDispName"
                                    value={StockDispName}
                                    placeholder='Enter Display Name'
                                    onChange={(e) => this.handleInputChange(e)}
                                />
                            </Col>
                        </Row>
                        {/* <Row>
                            <Col md={12}>
                            <label>Lot Size</label>
                                <Input
                                    type="number"
                                    placeholder="Enter Lot Size"
                                    name='LotSize'
                                    data-inp='Coins points'
                                    value={LotSize}
                                    onChange={(e) => this.handleInputChange(e)}
                                />
                            </Col>
                        </Row> */}
                    </ModalBody>
                    <ModalFooter>
                        <Button className="btn-default-gray" onClick={this.addStockToggle}>Cancel</Button>
                        <Button className="btn-secondary-outline"
                            disabled={CreatePosting}
                            onClick={this.addStock}>{EditFlag ? 'Update' :'Done'}</Button>{' '}
                    </ModalFooter>
                </Modal>
            </div>
        )
    }

    addStockToggle = (index, item) => {       
        
        if (!_isUndefined(item)) {
            this.setState({
                CreatePosting: true,
                EditFlag: true,
                EditIndex: index,
                StockDispName: item.display_name,
                StockIconName: item.logo,
                LotSize: item.lot_size,
                StockId: item.stock_id,
                StockIcon: (!_isNull(item.logo) && !_isEmpty(item.logo)) ? NC.S3 + NC.STOCK_PATH + item.logo : '',
            })
        }
        this.setState({ addStockModalOpen: !this.state.addStockModalOpen }, () => {
            if (!this.state.addStockModalOpen) {
                this.setState({
                    EditFlag: false,
                    EditIndex: '',
                    StockDispName: '',
                    LotSize: '',
                    GroupId: '',
                    StockIcon: '',
                    StockIconName: '',
                    SearchStock: '',
                    MasterStockId: '',
                    SuggestionsList: [],
                })
            }
        })
    }

    handleInputChange = (event) => {
        let name = event.target.name
        let value = event.target.value
        if (name === 'StockDispName') {
            value = value.replace(/  +/g, ' ')
        }
        // if (name == 'LotSize' && value < 1 || value > 99999) {
        //     let msg = 'Lot size should be in the range of 1 to 99999'
        //     notify.show(msg, 'error', 3000)
        //     value = ''
        // }
        this.setState({ [name]: value, CreatePosting : false })
    }

    validForm = () => {
        let { LotSize, StockDispName, StockIconName, SearchStock, EditFlag } = this.state
        let r_flag = true
        if (!EditFlag && _isEmpty(SearchStock)) {
            let msg = 'Please select stock'
            notify.show(msg, 'error', 3000)
            r_flag = false
        }
        // else if (_isEmpty(StockIconName)) {
        //     let msg = 'Please upload stock logo'
        //     notify.show(msg, 'error', 3000)
        //     r_flag = false
        // }
        else if (_isEmpty(StockDispName) || StockDispName.length < 3 || StockDispName.length > 50) {
            let msg = 'Display name should be in the range of 3 to 50'
            notify.show(msg, 'error', 3000)
            r_flag = false
        }
        // else if (LotSize < 1 || LotSize > 99999)
        // {
        //     let msg = 'Lot size should be in the range of 1 to 99999'
        //     notify.show(msg, 'error', 3000)
        //     r_flag = false
        // }
        return r_flag
    }

    addStock = () => {
        this.setState({ CreatePosting: true })
        let { StockDispName, LotSize, StockIconName, StockId, EditFlag, MasterStockId } = this.state
        
        if (!this.validForm())
        {
            return false
        }
        let params = {
            "master_stock_id": "4",
            "lot_size": LotSize,
            "display_name": StockDispName,
            "logo": StockIconName,
            "stock_type": 1,
        }
        let url = ''
        if(EditFlag)
        {
            params.stock_id = StockId
            url = SF_update
        }else{
            params.master_stock_id = MasterStockId
            url = SF_save
        }


        url(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.addStockToggle()
                this.getStockList()
                notify.show(ResponseJson.message, "success", 3000)
                this.setState({
                    StockDispName: '',
                    StockIcon: '',
                    StockIconName: '',
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
            this.setState({ CreatePosting: false })
        }).catch(error => {
            this.setState({ CreatePosting: false })
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    onChangeImage = (event) => {
        this.setState({
            StockIcon: URL.createObjectURL(event.target.files[0]),
            CreatePosting: true
        });
        const file = event.target.files[0];
        if (!file) {
            return;
        }
        var data = new FormData();
        data.append("userfile", file);
        SF_uploadStockLogo(data).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    StockIconName: Response.data.file_name,
                });

                if (!_isEmpty(this.state.StockIconName))
                    this.setState({ CreatePosting: false })
            } else {
                this.setState({
                    StockIcon: null,
                });
            }
        }).catch(error => {            
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        });
    }

    resetFile = () => {
        this.setState({
            StockIcon: null,
            StockIconName: '',
            CreatePosting: true,
        });
    }

    handlePageChange(current_page) {
        if (current_page != this.state.CURRENT_PAGE)
        {
            this.setState({
                CURRENT_PAGE: current_page
            }, () => {
                this.getStockList()
            });
        }
    }

    handleSearchStock = (event) => {
        let value = event.target.value        
        this.setState({ SearchStock: value, SuggestionsList : [] },()=>{
            // if (this.state.SearchStock.length > 1)
            // {
                this.getStockSuggestion()
            // }
        })
    }

    getStockSuggestion = () => {
        let params = {
            "keyword": this.state.SearchStock
        }
        SF_autoSuggetionList(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({ SuggestionsList: ResponseJson.data })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    onClick = (item) => {
        let dflag= !_isEmpty(item.name) ? '-' : ''
        this.setState({
            MasterStockId: item.master_stock_id,
            SearchStock: item.name + dflag + item.trading_symbol,
            StockDispName: item.display_name,
            StockIconName: item.logo,
            LotSize: item.lot_size,
            StockIcon: (!_isNull(item.logo) && !_isEmpty(item.logo)) ? NC.S3 + NC.STOCK_PATH + item.logo : '',
            SuggestionsList : [],
        });
    };

    searchList = () => {
        return(
            <ul className="suggestions">
                {
                    _Map(this.state.SuggestionsList, (item, index) => {
                        {                            
                            let className;
                            // Flag the active suggestion with a class
                            if (index === this.state.activeSuggestion) {
                                className = "suggestion-active";
                            }
                            return (
                                <li data_id={item.master_stock_id} key={item.name} className={className} onClick={()=>this.onClick(item)}>
                                    {item.name}
                                    {!_isEmpty(item.name) &&'-'}{item.trading_symbol}
                                </li>
                            );
                        }
                    })
                }        
            </ul>
        )
    }

    render() {
        let { StockList, Total, ListPosting, Keyword, SelectedLot, DeleteModalOpen, DeletePosting, addStockModalOpen, CURRENT_PAGE, PERPAGE, LotList, StockLimit } = this.state
        const Select_Props = {
            is_disabled: false,
            is_searchable: true,
            is_clearable: false,
            menu_is_open: false,
            class_name: "",
            sel_options: LotList,
            place_holder: "Select",
            selected_value: SelectedLot,
            modalCallback: this.handleLotChange
        }

        let DeleteModalProps = {
            publishModalOpen: DeleteModalOpen,
            publishPosting: DeletePosting,
            modalActionNo: this.deleteToggle,
            modalActionYes: this.deleteStock,
            MainMessage: XP_DELETE_LEVEL,
            SubMessage: XP_DELETE_LEVEL_SUB,
        }

        return (
            <div className="manage-stocks">
                {DeleteModalOpen && <PromptModal {...DeleteModalProps} />}
                {addStockModalOpen && this.addStockModal()}
                <Row className="mt-30">
                    <Col md={12}>
                        <h2 className="h2-cls">Nifty 50</h2>
                    </Col>
                </Row>
                <Row className="mt-30">
                    <Col md={3}>
                        <label className="filter-label">Search</label>
                        <Input
                            placeholder="Search Name"
                            name='code'
                            value={Keyword}
                            onChange={this.searchByUser}
                        />
                    </Col>
                    <Col md={3}>
                        <label className="filter-label">Total stock count</label>
                        <div className="ms-stk-count">{Total}</div>
                    </Col>
                    <Col md={3}>
                        {/* <label className="filter-label">Lot Size</label>
                        <SelectDropdown SelectProps={Select_Props} /> */}
                    </Col>                    
                    <Col md={3}>
                        <Button 
                            disabled={StockLimit == Total}
                            onClick={this.addStockToggle}
                            className="btn-secondary add-stock"
                        >Add Stock</Button>
                    </Col>
                </Row>
                <Row className="mt-30">
                    <Col md={12} className="table-responsive common-table">
                        <Table>
                            <thead className="height-40">
                                <tr>
                                    <th>Stock Name</th>
                                    <th>Trading symbol</th>
                                    <th>Display Name</th>
                                    {/* <th>Token</th> */}
                                    <th>Logo</th>
                                    {/* <th>Lot Size</th> */}
                                    <th>Action</th>
                                </tr>
                            </thead>
                            {
                                Total > 0 ?
                                    _Map(StockList, (item, idx) => {
                                        return (
                                            <tbody key={idx}>
                                                <tr>
                                                    <td>{item.name}</td>
                                                    <td>{item.trading_symbol}</td>                                                    
                                                    <td>{item.display_name}</td>
                                                    {/* <td>{item.exchange_token}</td> */}
                                                    <td>
                                                        <div className="s-logo">
                                                            <img src={item.logo ? NC.S3 + NC.STOCK_PATH + item.logo : Images.no_image} className="img-cover" alt="" />
                                                        </div>
                                                    </td>
                                                    {/* <td>{item.lot_size}</td> */}
                                                    <td>
                                                        <i
                                                            onClick={() => this.deleteToggle(item.stock_id, idx)}
                                                            className="icon-delete"></i>
                                                        <i
                                                            onClick={() => this.addStockToggle(idx, item)}
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
export default SF_ManageStock

