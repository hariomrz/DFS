import React, { Component } from "react";
import { Row, Col, Button, Table, Input } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import HF from '../../helper/HelperFunction';
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Select from 'react-select';
import "react-datepicker/dist/react-datepicker.css";
import Pagination from "react-js-pagination";
import Images from "../../components/images";
import { _debounce, _filter, _isEmpty } from "../../helper/HelperFunction";
import TDSDocModal from "./TDSDocModal";

export default class TDSDocument extends Component {
    constructor(props) {
        super(props);
        this.state = {
            posting: true,
            TotalUser: 0,
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            FinancialType: [],
            SelectedFinancialYear: '',
            ReportList: [],
            Keyword: '',
            actionPosting: false,
            TDSModalShow: false,
            CurrentFY: {}
        }
        this.SearchCodeReq = _debounce(this.SearchCodeReq.bind(this), 500);
    }

    componentDidMount() {
        this.getFilter()
    }

    // API Calling 
    getFilter = () => {
        WSManager.Rest(NC.baseURL + NC.GET_FILTER_LIST_TDS, {}).then(ResponseJson => {
            console.log(ResponseJson);
            if (ResponseJson.response_code == NC.successCode) {
                const Temp = []

                _.map(ResponseJson.data.fy, (it, idx) => {
                    Temp.push({
                        value: it, label: idx
                    })
                })
                const _date = new Date();
                let _year = _date.getFullYear();
                let currentFy = _filter(Temp, o => o.label.includes(_year))[0]
                this.setState({
                    FinancialType: Temp,
                    SelectedFinancialYear: currentFy,
                    CurrentFY: currentFy,
                }, () => {
                    this.getTDSDoc();
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    getTDSDoc = (isFilter = false) => {
        this.setState({ posting: true })
        const { PERPAGE, CURRENT_PAGE, Keyword, SelectedFinancialYear } = this.state
        
        let params = {
            limit: PERPAGE,
            page: isFilter ? 1 : CURRENT_PAGE,
            fy: SelectedFinancialYear.label,
            keyword: Keyword || ""
        }
        WSManager.Rest(NC.baseURL + NC.GET_TDS_DOCUMENT, params).then(({ response_code, data }) => {
            if (response_code == NC.successCode) {
                this.setState({
                    posting: false,
                    ReportList: data.result,
                    TableFields: data.table_field,
                    TotalUser: data.total,
                    ...(isFilter ? { CURRENT_PAGE: 1 } : {})
                })
            } else {
                this.setState({ posting: false })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })

    }

    deleteDoc = (item) => {
        if (window.confirm("Are you sure to delete this record?")) {
            this.setState({ actionPosting: true });
            WSManager.Rest(NC.baseURL + NC.DELETE_TDS_DOCUMENT, {
                id: item.id
            }).then(({ message }) => {
                const { ReportList } = this.state
                const _ReportList = _filter(ReportList, o => o.id != item.id)
                this.setState({ ReportList: _ReportList, actionPosting: false });
                notify.show(message, "success", 3000)
            }).catch(error => {
                this.setState({ actionPosting: false });
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            })
        }
    }
    uploadDocCallback = () => {

    }
    // Handler(s)
    handlePageChange(current_page) {
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            this.getTDSDoc();
        });
    }

    clearFilter = () => {
        const { CurrentFY } = this.state
        this.setState({
            Keyword: '',
            SelectedFinancialYear: CurrentFY,
        }, () => {
            this.getTDSDoc()
        })
    }

    handleTypeChange = (value, name) => {
        if (value != null)
            this.setState({ [name]: value }, () => {
                this.getTDSDoc(true)
            })
    }

    searchByUser = (e) => {
        this.setState({ Keyword: e.target.value })
    }

    SearchCodeReq() {
        const { Keyword } = this.state
        if (Keyword.length > 2 || Keyword.length == 0)
            this.getTDSDoc()
    }

    TDSModalHandle = () => {
        console.log(this.state.TDSModalShow);
        this.setState({ TDSModalShow: !this.state.TDSModalShow });
    }

    handleDownload = (item) => {
        try {
            const { REACT_APP_S3URL } = process.env
            const pdfUrl = `${REACT_APP_S3URL}upload/tds/${item.file_name}`
            fetch(pdfUrl)
                .then(response => {
                    // Create a blob from the response data
                    return response.blob();
                })
                .then(blob => {
                    // Create a temporary URL object from the blob
                    const url = window.URL.createObjectURL(new Blob([blob]));

                    // Create a link element and simulate a click to download the file
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', item.file_name);
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                })
                .catch(error => {
                    console.error(error);
                });
        } catch (error) {
            console.log(error);
        }
    }

    render() {
        const { CURRENT_PAGE, PERPAGE, TotalUser, Keyword, ReportList, TableFields, FinancialType, SelectedFinancialYear, actionPosting, TDSModalShow, posting } = this.state

        const TDSDocModalProps = {
            ...this.props,
            isOpen: TDSModalShow,
            toggle: this.TDSModalHandle,
            SelectedFinancialYear: SelectedFinancialYear,
            callback: this.getTDSDoc
        }
        return (
            <div className="animated fadeIn mt-4 contest-template">
                <Row className="tds-head">
                    <Col md={6}>
                        <h2 className="h2-cls">TDS Document</h2>
                    </Col>
                    <Col md={6} className="jc-flex-end">
                        <label className="backtofixtures" onClick={() => { this.props.history.push('/accounting/tds-reports') }}> &lt; Back to TDS Report</label>
                    </Col>
                </Row>
                <div className="user-deposit-amount">
                    <Row className="mt-2 align-items-flex-end mb-30">
                        <Col md={2}>
                            <div>
                                <label className="filter-label">Financial Year</label>
                                <Select
                                    searchable={false}
                                    clearable={false}
                                    class="form-control"
                                    options={FinancialType}
                                    value={SelectedFinancialYear}
                                    onChange={e => this.handleTypeChange(e, 'SelectedFinancialYear')}
                                />
                            </div>
                        </Col>
                        <Col md={2}>
                            <div className="search-box">
                                <label className="filter-label">Search User</label>
                                <Input
                                    placeholder={HF.getIntVersion() != 1 ? "PAN, Name, Email, Mobile" : "ID, Name, Email, Mobile"}
                                    name='code'
                                    value={Keyword}
                                    onChange={this.searchByUser}
                                />
                            </div>
                        </Col>
                        <Col md={5}>
                            <div className="tds-filter-btns">
                                <Button className="btn-secondary" onClick={() => this.getTDSDoc(true)}>Apply</Button>
                                <Button className="btn-secondary btn-secondary-outline" onClick={() => this.clearFilter()}>Clear</Button>
                            </div>
                            {/* {
                                (!_isEmpty(SelectedFinancialYear) && !_isEmpty(FinancialType)) && (SelectedFinancialYear.label != FinancialType[0].label || Keyword != '') &&
                                <a className="tdc-clear-link" onClick={() => this.clearFilter()}>Clear</a>
                            } */}
                        </Col>

                        <Col md={3} className="jc-flex-end">
                            <Button className='btn-secondary-outline' onClick={() => this.TDSModalHandle()}>Upload Document</Button>
                        </Col>
                    </Row>

                    <Row>
                        <Col md={12} className="table-responsive common-table gst-table">
                            {
                                !_.isEmpty(ReportList) && (ReportList.length > 0) ?
                                    <Table>
                                        <thead>
                                            <tr>
                                                {
                                                    _.map(TableFields, (item, idx) => {
                                                        return (
                                                            <th key={idx}>{item}</th>
                                                        )
                                                    })
                                                }
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {
                                                !_.isEmpty(ReportList) && (ReportList.length > 0) ?
                                                    <>
                                                        {
                                                            _.map(ReportList, (item, ind) => {
                                                                return (
                                                                    <tr key={ind}>
                                                                        {
                                                                            _.map(TableFields, (fieldname, idx) => {
                                                                                return (
                                                                                    <td key={idx}>
                                                                                        {
                                                                                            idx == "date_added" ?
                                                                                                <>{HF.getFormatedDateTime(item[idx], 'DD-MMM-YYYY | hh:mm A')}</>
                                                                                                :
                                                                                                <>{item[idx]}</>
                                                                                        }
                                                                                    </td>
                                                                                )
                                                                            })
                                                                        }
                                                                        <td>
                                                                            <span className="d-flex jc-center">

                                                                                <i className="icon-delete action-btn" onClick={!actionPosting ? () => this.deleteDoc(item) : null} />
                                                                                <i className="icon-export action-btn ml-2" onClick={() => this.handleDownload(item)} />
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                )
                                                            })
                                                        }
                                                    </>
                                                    :
                                                    <></>
                                            }
                                        </tbody>
                                    </Table>
                                    :
                                    posting ?
                                    <></>
                                    :
                                    <div className="tds-no-data">
                                        <div><img src={Images.NO_DATA_SHADE} alt="" /></div>
                                        <div className="no-records">No Records Found</div>
                                    </div>
                            }

                        </Col>
                    </Row>

                    {TotalUser > 0 && (
                        <div className="custom-pagination lobby-paging">
                            <Pagination
                                activePage={CURRENT_PAGE}
                                itemsCountPerPage={PERPAGE}
                                totalItemsCount={TotalUser}
                                pageRangeDisplayed={5}
                                onChange={e => this.handlePageChange(e)}
                            />
                        </div>
                    )
                    }

                </div>
                {
                    TDSModalShow &&
                    <TDSDocModal {...TDSDocModalProps} />
                }
            </div>
        )
    }
}
