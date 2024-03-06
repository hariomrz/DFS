import React, { Component, Fragment } from "react";
import { Button, Row, Col, Table, Input, Modal, ModalBody, ModalHeader, ModalFooter } from 'reactstrap';
import WSManager from '../../helper/WSManager';
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import _ from 'lodash';
import Loader from '../../components/Loader';
import Highcharts from 'highcharts'
import HighchartsReact from 'highcharts-react-official'
import moment from "moment";
import Moment from 'react-moment';
import { Base64 } from 'js-base64';
import Pagination from "react-js-pagination";
import Affiliate from "../Affiliate/Affiliate";
import SelectDropdown from "../../components/SelectDropdown";
import HF from "../../helper/HelperFunction";
const statusOptions = [
    { value: '', label: 'All' },
    { value: 1, label: 'Approved' },
    { value: 2, label: 'Pending' },
    { value: 3, label: 'Blocked' },
    { value: 4, label: 'Rejected' },
]
class AffiliateDashboard extends Component {
    constructor(props) {
        super(props)
        this.state = {
            CURRENT_PAGE: 1,
            PERPAGE: NC.ITEMS_PERPAGE,
            PenFromDate: HF.getFirstDateOfMonth(),
            PenToDate: new Date(),
            GrpFromDate: HF.getFirstDateOfMonth(),
            GrpToDate: new Date(),
            UsersList: [],
            ListPosting: false,
            Total: 10,
            SignupGraphData: {},
            DepositGraphData: {},
            CommisionGraphData: {},
            IsPendingAffi: true,
            SelectedStatus: '',
            Keyword: '',
            addMoreModalOpen: false,
            SiteRakeStatus: 0,
            UserUniqueId: 0,
            TotalSiteRakeCommssion:0,
            oldcom: 0
        }
        this.SearchCodeReq = _.debounce(this.SearchCodeReq.bind(this), 500);
    }

    componentDidMount() {
        this.getSignupGraph()
        this.getDepositGraph()
        this.commisionGraph()
        this.getPendingAffi()
         this.TotalSiteRake()
    }


    getPendingAffi = () => {
        this.setState({ ListPosting: false })
        let { PenFromDate, PenToDate, PERPAGE, CURRENT_PAGE, SelectedStatus, Keyword } = this.state
        let params = {
            "items_perpage": PERPAGE,
            "current_page": CURRENT_PAGE,
            "sort_field": "modified_date",
            "sort_order": "DESC",
            "from_date": PenFromDate ? moment(PenFromDate).format("YYYY-MM-DD") : '',
            "to_date": PenToDate ? moment(PenToDate).format("YYYY-MM-DD") : '',
            "is_affiliate": SelectedStatus,
            "keyword": Keyword,
            "action": '1',
            "csv": 0,
        }

        WSManager.Rest(NC.baseURL + NC.AFFI_GET_PENDING_AFFILIATE, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    ListPosting: true,
                    PendingAffiData: ResponseJson.data.result,
                    TotalPendingAffi: ResponseJson.data.total,
                    IsPendingAffi: (ResponseJson.data.is_pending_affiliate && ResponseJson.data.is_pending_affiliate == '1') ? true : false,
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    //Api call for coin distribution graph
    getSignupGraph = () => {
        let { GrpFromDate, GrpToDate } = this.state
        let params = {
            from_date: GrpFromDate ? moment(GrpFromDate).format("YYYY-MM-DD") : '',
            to_date: GrpToDate ? moment(GrpToDate).format("YYYY-MM-DD") : '',
            "user_id": null,
            "filter": null
        }

        WSManager.Rest(NC.baseURL + NC.AFFI_GET_SIGNUP_GRAPH, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    currency: ResponseJson.data.currency,
                    xAxisSeries: ResponseJson.data.series,
                    xAxisCategories: ResponseJson.data.dates,

                    totalCoinsDistributed: ResponseJson.data.total_coins_distributed,
                    closingBalance: ResponseJson.data.closing_balance,
                }, () => {
                    //Start Coin Distributed Graph                    
                    this.setState({
                        SignupGraphData: {
                            title: {
                                text: ''
                            },
                            chart: {
                                height: '190px',
                            },
                            plotOptions: {
                                series: {
                                    marker: { symbol: 'circle' }
                                }
                            },
                            xAxis: {
                                categories: this.state.xAxisCategories,
                                min: 0,
                                tickWidth: 0,
                                crosshair: false,
                                lineWidth: 2,
                                gridLineWidth: 0,
                                title: '',
                                lineColor: '#D8D8D8',
                                title: {
                                    text: ''
                                }
                            },
                            yAxis: [
                                {
                                    labels: {
                                        format: '{value}'
                                    },
                                    title: {
                                        text: ''
                                    },
                                    min: 0,
                                    tickWidth: 0,
                                    crosshair: false,
                                    lineWidth: 1,
                                    gridLineWidth: 0,
                                    lineColor: '#D8D8D8',
                                    allowDecimals: false,
                                },
                                {
                                    title: {
                                        text: ''
                                    },
                                    labels: {
                                        format: '50'
                                    },
                                    opposite: true,
                                    min: 0,
                                    tickWidth: 0,
                                    crosshair: false,
                                    lineWidth: 0,
                                    gridLineWidth: 0,
                                    lineColor: '#D8D8D8'
                                }],
                            allowPointSelect: true,
                            series: this.state.xAxisSeries,
                            credits: {
                                enabled: false,
                            },
                            legend: {
                                enabled: false
                            },
                        }
                    })
                    //End Coin Distributed Graph
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    getDepositGraph = () => {
        let { GrpFromDate, GrpToDate } = this.state
        let params = {
            "user_id": null,
            "filter": null,
            from_date: GrpFromDate ? moment(GrpFromDate).format("YYYY-MM-DD") : '',
            to_date: GrpToDate ? moment(GrpToDate).format("YYYY-MM-DD") : '',
        }

        WSManager.Rest(NC.baseURL + NC.AFFI_GET_DEPOSIT_GRAPH, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    currency: ResponseJson.data.currency,
                    xAxisSeries: ResponseJson.data.series,
                    xAxisCategories: ResponseJson.data.dates,

                    totalCoinsDistributed: ResponseJson.data.total_coins_distributed,
                    closingBalance: ResponseJson.data.closing_balance,
                }, () => {
                    //Start Coin Distributed Graph                    
                    this.setState({
                        DepositGraphData: {
                            title: {
                                text: ''
                            },
                            chart: {
                                height: '190px',
                            },
                            plotOptions: {
                                series: {
                                    marker: { symbol: 'circle' }
                                }
                            },
                            xAxis: {
                                categories: this.state.xAxisCategories,
                                min: 0,
                                tickWidth: 0,
                                crosshair: false,
                                lineWidth: 2,
                                gridLineWidth: 0,
                                title: '',
                                lineColor: '#D8D8D8',
                                title: {
                                    text: ''
                                }
                            },
                            yAxis: [
                                {
                                    labels: {
                                        format: this.state.currency + ' {value}'
                                    },
                                    title: {
                                        text: ''
                                    },
                                    min: 0,
                                    tickWidth: 0,
                                    crosshair: false,
                                    lineWidth: 1,
                                    gridLineWidth: 0,
                                    lineColor: '#D8D8D8'
                                },
                                {
                                    title: {
                                        text: ''
                                    },
                                    labels: {
                                        format: '50'
                                    },
                                    opposite: true,
                                    min: 0,
                                    tickWidth: 0,
                                    crosshair: false,
                                    lineWidth: 0,
                                    gridLineWidth: 0,
                                    lineColor: '#D8D8D8'
                                }],
                            allowPointSelect: true,
                            series: this.state.xAxisSeries,
                            credits: {
                                enabled: false,
                            },
                            legend: {
                                enabled: false
                            },
                        }
                    })
                    //End Coin Distributed Graph
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    //Api call for coin redeemed graph
    commisionGraph = () => {
        let { GrpFromDate, GrpToDate } = this.state
        let params = {
            "user_id": null,
            from_date: GrpFromDate ? moment(GrpFromDate).format("YYYY-MM-DD") : '',
            to_date: GrpToDate ? moment(GrpToDate).format("YYYY-MM-DD") : '',
        }

        WSManager.Rest(NC.baseURL + NC.AFFI_GET_COMMISSION_GRAPH, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    redeemedSeries: ResponseJson.data.series_data,
                    totalCoinRedeem: ResponseJson.data.total_coin_redeem,
                }, () => {
                    //Start Coin Redeemed Graph
                    this.setState({
                        CommisionGraphData: {
                            title: {
                                text: ''
                            },
                            chart: {
                                type: 'pie',
                                height: '190px',
                            },
                            plotOptions: {
                                pie: {
                                    borderWidth: 4,
                                    dataLabels: false,
                                    innerSize: '64%',
                                    allowPointSelect: true,
                                    cursor: 'pointer',
                                    dataLabels: {
                                        enabled: true,
                                        color: '#93989F',
                                        useHTML: true,
                                        style: {
                                            fontSize: '10px',
                                            fontFamily: "MuliRegular",
                                            textAlign: 'right',
                                            lineHeight: '18px'
                                        },
                                        format: '<div><div class="clearfix slice-color"></div><div class="aff-tot-signup">{point.currency}{point.commission}</div><div class="aff-tot-prc">({point.percentage:.1f} %)</div><span style="background-color: {point.color}" class="indicator"></span><span>{point.name}</span></div>',

                                        connectorColor: 'transparent',
                                        connectorPadding: 0,
                                        distance: 6,
                                        y: 0,
                                        x: 0,
                                    },
                                    stacking: 'normal'
                                }
                            },
                            series: [{
                                data: this.state.redeemedSeries
                            }],
                            LineData: [],
                            GraphHeaderTitle: [],
                            credits: {
                                enabled: false,
                            },
                            legend: {
                                enabled: false
                            }
                        }
                    })
                    //End Coin Redeemed Graph
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }


      TotalSiteRake = () => {    
        
         let params = {
           
        }
              
        WSManager.Rest(NC.baseURL + NC.GET_TOTAL_SITE_RAKE_COMMISSION, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
               
                this.setState({
                    TotalSiteRakeCommssion: ResponseJson.data['total_commision']   ,              
                                
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    handleGrpDateChange = (date, dateType) => {
        this.setState({ [dateType]: date }, () => {
            this.getSignupGraph()
            this.getDepositGraph()
            this.commisionGraph()
        })
    }

    handlePenDateChange = (date, dateType) => {
        this.setState({ [dateType]: date, CURRENT_PAGE: 1 }, () => {
            this.getPendingAffi()
        })
    }

    handlePageChange(current_page) {
        if (this.state.CURRENT_PAGE != current_page) {
            this.setState({
                CURRENT_PAGE: current_page
            }, () => {
                this.getPendingAffi();
            });
        }
    }

    handleStatusChange = (value) => {
        this.setState({ SelectedStatus: value.value }, () => {
            this.getPendingAffi()
        })
    }

    exportReport_Get = () => {
        let { Keyword, PenFromDate, PenToDate, SelectedStatus } = this.state
        let tempFromDate = ''
        let tempToDate = ''
        if (PenFromDate != '' && PenToDate != '') {
            tempFromDate = PenFromDate ? HF.getFormatedDateTime(PenFromDate, 'YYYY-MM-DD') : '';
            tempToDate = PenToDate ? HF.getFormatedDateTime(PenToDate, 'YYYY-MM-DD') : '';
        }

        var query_string = '&action=1&csv=1&keyword=' + Keyword + '&from_date=' + tempFromDate + '&to_date=' + tempToDate + '&is_affiliate=' + SelectedStatus;
        var export_url = 'adminapi/affiliate_users/get_affilicate_list?';

        HF.exportFunction(query_string, export_url)
    }

    searchByUser = (e) => {
        this.setState({ Keyword: e.target.value }, this.SearchCodeReq)
    }

    SearchCodeReq() {
        this.getPendingAffi()
    }

    handlecomission = (e,oldcom) => {        
        let name = e.target.name
        let value = e.target.value
        value = (value.indexOf(".") >= 0) ? (value.substr(0, value.indexOf(".")) + value.substr(value.indexOf("."), 3)) : value;
         
        if (value > 100) {
            notify.show("Not greater than 100", 'error', 5000)
            return false;
        }
        this.setState({ CommissionSiteRake : value})       
    }
 

     addMoreToggle = (user_unique_id ="") => {   
     
        this.setState({ addMoreModalOpen: !this.state.addMoreModalOpen , UserUniqueId:user_unique_id})
   
        if(this.state.addMoreModalOpen == false){
          
                let params = {
                    "keyword": user_unique_id,
                    "action": this.state.AffiPhone == '0' ? 1 : 2,
                }

            
                WSManager.Rest(NC.baseURL + NC.AFFI_USERS, params).then(ResponseJson => {
                    if (ResponseJson.response_code == NC.successCode) {
                        this.setState({
                                SiteRakeStatus: ResponseJson.data.site_rake_status,                     
                                CommissionSiteRake: ResponseJson.data.site_rake_commission,                       
                                oldcom: ResponseJson.data.site_rake_commission                       
                        })
                        
                    
                    }
                }).catch(error => {
                    notify.show(NC.SYSTEM_ERROR, 'error', 5000)
                })
            }

    }
    UpdateRake = ()=>{
       const{UserUniqueId,SiteRakeStatus,CommissionSiteRake,TotalSiteRakeCommssion,TotalSiteRakeCommssionCheck,oldcom} = this.state
             if(SiteRakeStatus == 1){
                if(CommissionSiteRake <= 0){
                            notify.show('Commssion should be greater than 0', 'error', 5000);
                            return false;
                }
            }
         
            if(CommissionSiteRake < oldcom || CommissionSiteRake > oldcom){
                let num3 = Number(TotalSiteRakeCommssion);
                let num4 = Number(oldcom);

                let newData = num3 - num4;

               let num5 = Number(CommissionSiteRake);
               let newcommsion = newData + num5;

                if (newcommsion > 100) {
                    notify.show('Commssion will not greater than 100%', 'error', 5000);
                    return false;
                }
            }
        let params = {
                    "user_unique_id": UserUniqueId,
                    "siterake_status": SiteRakeStatus,
                    "siterake_commission":SiteRakeStatus == 1 ? CommissionSiteRake : 0
                  
                }
            
                WSManager.Rest(NC.baseURL + NC.AFFI_RAKE_UPDATE, params).then(ResponseJson => {
                    if (ResponseJson.response_code == NC.successCode) {
                         notify.show(ResponseJson.message, "success", 5000);
                        this.setState({
                                SiteRakeStatus: ResponseJson.data.site_rake_status,                     
                                CommissionSiteRake: ResponseJson.data.site_rake_commission                       
                        })
                        this.addMoreToggle()
                        this.getPendingAffi()
                        this.TotalSiteRake()
                    }
                }).catch(error => {
                    notify.show(NC.SYSTEM_ERROR, 'error', 5000)
                })
    }

      handleCommiChange = (event) => {
            let name = event.target.name
            let value = event.target.value  

            if(name == 'site_rake_status'){
                this.setState({ SiteRakeStatus : value })                
            }

            this.setState({ [name]: value, appRejPosting: false })
        }
        addMoreModal = () => {
        let {  } = this.state
        return (
            <div>
                <Modal className="addmore-su-modal addPlayerModel aff_model" isOpen={this.state.addMoreModalOpen}
                    toggle={this.addMoreToggle}>
                    <ModalHeader>
                         <Row>
                            <Col md={12} className='simpleflex'>
                                <h3 className="h3-cls">Update User Commission</h3>
                            </Col>
                        </Row>
                    </ModalHeader> 
                       
                    <ModalBody>
                       <Row>
                            <Col md={12}>
                                <div className="input-box p-0 w-100 commisson_box">
                                    <label className="aff-search-lbl mb-3">Commission on Site Rake %</label>
                                    <ul className="coupons-option-lists">
                                        <li className="coupons-option-item">
                                            <div className="custom-radio">
                                                <input
                                                    type="radio"
                                                    className="custom-control-input"
                                                    name="site_rake_status"
                                                    value="1"
                                                    checked={this.state.SiteRakeStatus === '1'}
                                                    onChange={this.handleCommiChange}
                                                />
                                                <label className="custom-control-label">
                                                    <span className="input-text">Yes</span>
                                                </label>
                                            </div>
                                        </li>
                                        <li className="coupons-option-item">
                                            <div className="custom-radio">
                                                <input
                                                    type="radio"
                                                    className="custom-control-input"
                                                    name="site_rake_status"
                                                    value="0"
                                                    checked={this.state.SiteRakeStatus === '0'}
                                                    onChange={this.handleCommiChange}
                                                />
                                                <label className="custom-control-label">
                                                    <span className="input-text">No</span>
                                                </label>
                                            </div>
                                        </li>

                                            { this.state.SiteRakeStatus == 1 &&  <p> <div>
                                                <Input
                                                    type="text"
                                                    name="site_rake_commission"
                                                    placeholder=''
                                                    value={this.state.CommissionSiteRake}
                                                    onChange={(e) => this.handlecomission(e,this.state.CommissionSiteRake)}
                                                />
                                                 <p className = "text-color-change"><i className="icon-info icon-color-changes"></i> Total affiliate distribution: {this.state.TotalSiteRakeCommssion} %</p>
                                            </div></p>
                                        } 

                                    </ul>
                                </div>
                            </Col>
                        </Row>
                   
                 
                    
                    </ModalBody>
                    <ModalFooter>
                        <Button className="btn-secondary-outline" onClick ={this.UpdateRake}> updates
                           </Button>{' '}

                         <Button className="btn-default-gray" onClick={this.addMoreToggle}>Cancel</Button>    
                    </ModalFooter>
                </Modal>
            </div>
        )
    }

     handleCommiChange = (event) => {


        let name = event.target.name
        let value = event.target.value  

        if(name == 'site_rake_status'){
            this.setState({ SiteRakeStatus : value }) 
             
        }
    

        this.setState({ [name]: value, appRejPosting: false })
    }

    render() {
        let { PenFromDate, PenToDate, PendingAffiData, TotalPendingAffi, ListPosting, SignupGraphData, DepositGraphData, CommisionGraphData, GrpFromDate, GrpToDate, CURRENT_PAGE, PERPAGE, IsPendingAffi, SelectedStatus, Keyword } = this.state
        const Select_Props = {
            is_disabled: false,
            is_searchable: true,
            is_clearable: false,
            menu_is_open: false,
            class_name: "aff-status-filter",
            sel_options: statusOptions,
            place_holder: "Select",
            selected_value: SelectedStatus,
            modalCallback: this.handleStatusChange
        }
        return (
            <Fragment>
                <div className="affiliate-wrapper affiliate-dashboard">
                     {this.addMoreModal()}
                    {
                        !IsPendingAffi && <Affiliate />
                    }
                    {
                        IsPendingAffi &&
                        <Fragment>
                            <Row>
                                <Col md={12}>
                                    <div className="float-left">
                                        <h2 className="h2-cls mt-2">Dashboard</h2>
                                    </div>
                                    <Button
                                        className="btn-secondary-outline aff-btn"
                                        onClick={() => this.props.history.push("/add-affiliate/" + Base64.encode('0'))}>
                                        Add New
                            </Button>
                                </Col>
                            </Row>
                            <Row className="aff-graph-date">
                                <Col md={12}>
                                    <div className="float-left mr-3">
                                        <label className="filter-label">Date</label>
                                        <DatePicker
                                            maxDate={new Date(GrpToDate)}
                                            className="filter-date"
                                            showYearDropdown='true'
                                            selected={new Date(GrpFromDate)}
                                            onChange={e => this.handleGrpDateChange(e, "GrpFromDate")}
                                            placeholderText="From"
                                            dateFormat='dd/MM/yyyy'
                                        />
                                    </div>
                                    <div className="float-left mt-4">
                                        <DatePicker
                                            minDate={new Date(GrpFromDate)}
                                            maxDate={new Date()}
                                            className="filter-date"
                                            showYearDropdown='true'
                                            selected={new Date(GrpToDate)}
                                            onChange={e => this.handleGrpDateChange(e, "GrpToDate")}
                                            placeholderText="To"
                                            dateFormat='dd/MM/yyyy'
                                        />
                                    </div>
                                </Col>
                            </Row>
                            <Row className="mt-30">
                                <Col md={4}>
                                    <div className="affi-graph-box">
                                        <div className="affi-g-title">Signup</div>
                                        <HighchartsReact
                                            highcharts={Highcharts}
                                            options={SignupGraphData}
                                        />
                                    </div>
                                </Col>
                                <Col md={4}>
                                    <div className="affi-graph-box">
                                        <div className="affi-g-title">Deposit</div>
                                        <HighchartsReact
                                            highcharts={Highcharts}
                                            options={DepositGraphData}
                                        />
                                    </div>
                                </Col>
                                <Col md={4}>
                                    <div className="affi-graph-box">
                                        <div className="affi-g-title">Commission</div>
                                        <HighchartsReact
                                            highcharts={Highcharts}
                                            options={CommisionGraphData}
                                        />
                                    </div>
                                </Col>
                            </Row>
                            <Row className="mt-5 mb-20">
                                <Col md={12}>
                                    <div className="float-left mt-4">
                                        <h2 className="h2-cls mt-2">All Request</h2>
                                    </div>
                                    <div className="float-right mt-4">
                                        <i className="export-list icon-export"
                                            onClick={e => this.exportReport_Get()}></i>
                                    </div>
                                    <div className="float-right mr-4">
                                        <div className="search-box">
                                            <label className="filter-label">Search</label>
                                            <Input
                                                placeholder="Search"
                                                name='Keyword'
                                                value={Keyword}
                                                onChange={this.searchByUser}
                                            />
                                        </div>
                                    </div>
                                    <div className="float-right">
                                        <label className="filter-label ml-4">Status</label>
                                        <SelectDropdown SelectProps={Select_Props} />
                                    </div>
                                    <div className="float-right">
                                        <div className="float-left mr-3">
                                            <label className="filter-label">Date</label>
                                            <DatePicker
                                                maxDate={new Date(PenToDate)}
                                                className="filter-date"
                                                showYearDropdown='true'
                                                selected={new Date(PenFromDate)}
                                                onChange={e => this.handlePenDateChange(e, "PenFromDate")}
                                                placeholderText="From"
                                                dateFormat='dd/MM/yyyy'
                                            />
                                        </div>
                                        <div className="float-left mt-4">
                                            <DatePicker
                                                popperPlacement="bottom-end"
                                                minDate={new Date(PenFromDate)}
                                                maxDate={new Date()}
                                                className="filter-date"
                                                showYearDropdown='true'
                                                selected={new Date(PenToDate)}
                                                onChange={e => this.handlePenDateChange(e, "PenToDate")}
                                                placeholderText="To"
                                                dateFormat='dd/MM/yyyy'
                                            />
                                        </div>
                                    </div>
                                </Col>
                            </Row>
                            <Row>
                                <Col md={12} className="table-responsive common-table tab-d-center">
                                    <Table className="mb-0">
                                        <thead>
                                            <tr>
                                                <th className="left-th pl-3">Date</th>
                                                <th>User Name</th>
                                                <th>Website</th>
                                                <th>Mobile</th>
                                                <th>City</th>
                                                <th className="right-th pl-20">Action </th>
                                            </tr>
                                        </thead>
                                        {
                                            TotalPendingAffi > 0 ?
                                                _.map(PendingAffiData, (item, idx) => {

                                                    const url_web = item.user_affiliated_website;
                                                    if(url_web != null){

                                                    if (url_web.startsWith('http://') || url_web.startsWith('https://')) {
                                                      
                                                        var new_url = item.user_affiliated_website;
                                                        } else {
                                                
                                                        var new_url = 'http://'+item.user_affiliated_website;
                                                        }
                                                    }
                                                    // if (url_web.indexOf("http://") == 0 || url_web.indexOf("https://") == 0) {
                                                    // }else{
                                                    // }
                                                                                                
                                                   
                                                    return (
                                                        <tbody key={idx}>
                                                            <tr>
                                                                <td className="pl-3">
                                                                    <Moment date={WSManager.getUtcToLocal(item.modified_date)} format="D-MMM-YYYY hh:mm A" />
                                                                </td>

                                                                <td className={`pl-3 ${item.is_affiliate === '1' ? 'aff-name' : ''}`}>
                                                                    <span
                                                                        onClick={() => item.is_affiliate === '1' ? this.props.history.push("/affiliates-users/" + Base64.encode(item.user_id)) : null}>
                                                                        {item.user_name ? item.user_name : '--'}
                                                                    </span>

                                                                </td>
                                                                <td>{item.user_affiliated_website ? 
                                                                <a  href={new_url}  target="_blank" >{item.user_affiliated_website?new_url:'-'}</a>
                                                                : '-'}</td>
                                                                <td className="pl-3">{item.phone_no ? item.phone_no : '--'}</td>
                                                                <td>{item.city ? item.city : '--'}</td>
                                                                <td>
                                                                    {
                                                                        item.is_affiliate === '2' &&
                                                                        <Button
                                                                            className="btn-secondary-outline vrfy-btn"
                                                                            onClick={() => this.props.history.push("/add-affiliate/" + Base64.encode(item.user_unique_id) + "?up=true")}>
                                                                            Verify
                                                                        </Button>
                                                                    }
                                                                    {
                                                                        item.is_affiliate === '1' &&
                                                                       <div>
                                                                         <span className="text-green">Approved</span>
                                                                         { HF.allowAffiliateCommssion() == 1 &&
                                                                         <i
                                                                            onClick={() => this.addMoreToggle(item.user_unique_id)}
                                                                            className="icon-edit ml-4"></i> 
                                                                         }
                                                                        </div>
                                                                    }
                                                                    {
                                                                        item.is_affiliate === '3' &&
                                                                        <span className="text-red">Blocked</span>
                                                                    }
                                                                    {
                                                                        item.is_affiliate === '4' &&
                                                                        <span className="text-orange">Rejected</span>
                                                                    }
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    )
                                                })
                                                :
                                                <tbody>
                                                    <tr>
                                                        <td colSpan="8">
                                                            {(TotalPendingAffi == 0 && ListPosting) ?
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
                            <Row className="float-right">
                                <Col md={12}>
                                    {
                                        TotalPendingAffi > PERPAGE && (
                                            <div className="custom-pagination mt-30">
                                                <Pagination
                                                    activePage={CURRENT_PAGE}
                                                    itemsCountPerPage={PERPAGE}
                                                    totalItemsCount={TotalPendingAffi}
                                                    pageRangeDisplayed={5}
                                                    onChange={e => this.handlePageChange(e)}
                                                />
                                            </div>
                                        )
                                    }
                                </Col>
                            </Row>
                        </Fragment>
                    }
                </div>
            </Fragment>
        )
    }
}
export default AffiliateDashboard