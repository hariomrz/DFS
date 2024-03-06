import React, { Component, Fragment } from "react";
import { Row, Col, Table, Button, FormGroup, Tooltip, Input, InputGroup, Label, Modal, ModalBody, ModalFooter, ModalHeader } from "reactstrap";
import _ from 'lodash';
import Images from "../../components/images";
import HF, { _Map } from "../../helper/HelperFunction";
import { notify } from 'react-notify-toast';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import LS from 'local-storage';
import Pagination from "react-js-pagination";
import { TRADE_TEMPLATE_MANAGMENT_TABLE } from '../../helper/WSCalling';
import Select from 'react-select';
import moment from "moment-timezone";
// import FeaturedMarkPopup from "./FeaturedMarkPopup";
import HelperFunction, { _filter } from "../../helper/HelperFunction";
import SelectDate from "../../components/SelectDate";
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
class LeagueManagment extends Component {
   constructor(props) {
      super(props)
      let filter = {
         keyword: ''
      }
      this.state = {
         selected_sports_id:  NC.sportsId,
         templateData: [],
         CURRENT_PAGE: 1,
         ITEMS_PERPAGE: 20,
         filter: filter,
         noRecord: false,
         ListPosting: false,
         Total: 0,
         featuresItem: '',
         IsFeaturedModalOpen: false,
         isShowAutoToolTip: false,
         IsFeaturedData: [],
         IsOrder: true,
         sportsOptions: [],
         selected_sport:'',
         TodayDate: new Date(),
         EndDate: new Date(),       
         StartDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
      }
      this.handleChange = this.handleChange.bind(this);
      this.handleChangeEnd = this.handleChangeEnd.bind(this);
   }

   templateManagementDetails = async (pageStatus, offset) => {
      const { StartDate,EndDate,ITEMS_PERPAGE, CURRENT_PAGE, filter, selected_sports_id,IsOrder } = this.state

      this.setState({
         ListPosting: true
      })
      let params = {
         "sports_id": selected_sports_id,
         // "from_date": moment(StartDate).format("YYYY-MM-DD"),
       
         // "to_date": moment(EndDate).format("YYYY-MM-DD"),
         // "keyword": filter.keyword,
         // "sort_field": "sport_name",
         "sort_order": IsOrder ? 'ASC': "DESC" ,
         "page": CURRENT_PAGE,
         "limit": ITEMS_PERPAGE,
      }

      // console.log(params); return false;
      
      TRADE_TEMPLATE_MANAGMENT_TABLE(params).then(ResponseJson => {
         // console.log(ResponseJson);return false
         if (ResponseJson.response_code == NC.successCode) {
            this.setState({
               templateData: ResponseJson.data.result,
               Total: ResponseJson.data.total ? ResponseJson.data.total : 0,
               IsFeaturedData:ResponseJson.data.count_featured
            }, () => {
            //    let filterData = _filter(ResponseJson.data.result, (obj) => {
            //       return obj.is_featured == 1
            //   });
               this.setState({
                  ListPosting: false,
                  // filterData:filterData
               })
            })
         } else {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
         }
      }).catch(error => {
         notify.show(NC.SYSTEM_ERROR, "error", 3000)
      })
   };

   clearFilter = () => {
      const { ITEMS_PERPAGE, CURRENT_PAGE, filter, selected_sports_id, IsOrder } = this.state
      // console.log('yes');
      filter['keyword'] = '';
      this.setState({       
         filter: filter,
         EndDate: new Date(moment().format('D MMM YYYY')),
         StartDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
         CURRENT_PAGE: 1    

      }, () => {
         this.templateManagementDetails()
      }
      )
   }

   componentDidMount() {
      // this.templateManagementDetails()
      this.getSports();
   }

   handlePageChange(current_page) {
      if (current_page != this.state.CURRENT_PAGE) {
         this.setState({
            CURRENT_PAGE: current_page
         }, () => {
            this.templateManagementDetails()
         });
      }
   }

   handleSearch(keyword) {

      let filter = this.state.filter;
      filter['keyword'] = keyword.target.value;
      this.setState({
         filter: filter,
         noRecord: keyword.target.value != '' ? true : false,
         CURRENT_PAGE: 1
      },
         this.SearchCodeReq
      );
   }
   SearchCodeReq() {
      this.templateManagementDetails(1)
   }

   featuredModalClose = () => {
      this.setState({
         featuresItem: {},
         IsFeaturedModalOpen: false
      }, () => { this.templateManagementDetails() })
   }

   featuredModalOpen = (item) => {
      this.setState({
         featuresItem: item,
         IsFeaturedModalOpen: true
      })
   }
   AutoToolTipToggle = () => {
      this.setState({ isShowAutoToolTip: !this.state.isShowAutoToolTip });
   }

   sortLeagueManagement = (sortfiled, IsOrder) => {
      let Order = ((sortfiled == this.state.SortField)) ? !IsOrder : IsOrder
      this.setState({
          SortField: sortfiled,
         IsOrder: !IsOrder,
          CURRENT_PAGE: 1,
      },() =>{this.templateManagementDetails()}
      )
  }

   handleModuleChange = (status,template_id) => {
      let params = {
         "template_id": template_id,       
      }
     
      WSManager.Rest(NC.baseURL + NC.TRADE_UPDATE_TEMPLATE_STATUS, params).then((responseJson) => {
         if (responseJson.response_code === NC.successCode) {
            notify.show(responseJson.message, "success", 3000)
           
         } else {
            notify.show(responseJson.message, "error", 3000)
            this.setState({ posting: false })
         }
         this.templateManagementDetails()

      })
         .catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
         });
   }

   handleSports = (e,name) => {   
      const value = e.value
      const Labels = e.label

      // [name] = Labels   
      this.setState({
         sports_id: value,
         selected_sports_id: value,
         selected_sport: e.value,
         CURRENT_PAGE : 1
      }, () => {

      
         this.templateManagementDetails()
      })
   }

   getSports = () => {
      let params = {}
      WSManager.Rest(NC.baseURL + NC.TRADE_ALL_SPORTS_LIST, params).then((responseJson) => {
         
         if (responseJson.response_code == NC.successCode) {

            let sportsOptions = [];
            _.map(responseJson.data, function (data) {
               sportsOptions.push({
                  value: data.sports_id,
                  label: data.sports_name,
               })
            })
            this.setState({
               sportsOptions: sportsOptions,
               selected_sport: this.state.selected_sport ? this.state.selected_sport : sportsOptions[0].value,
            }, () => {
               LS.set('selectedSport', this.state.selected_sport)
               this.templateManagementDetails()
            })
         }
      }).catch(error => {
         notify.show(NC.SYSTEM_ERROR, "error", 3000)
      })
   }

  

   handleDate2 = (date, dateType) => {
 
 

      var dateNewEnd = moment().format("yyyy-MM-DD")

      // console.log(dateNewEnd, 'dateNewEnd'); 

      this.setState({
         // Validedate: this.validateDate(date)
         EndDate: date,
         CURRENT_PAGE: 1
      }, () => {

         this.templateManagementDetails()
      })

      
   }





   handleChange(date) {
     

      this.setState({
         // Validedate: this.validateDate(date)
         StartDate: date,
         CURRENT_PAGE: 1
      }, () => {

         this.templateManagementDetails()
      })



   }

   handleChangeEnd(date) {
    

      this.setState({
         // Validedate: this.validateDate(date)
         EndDate: date,
         CURRENT_PAGE: 1
      }, () => {

         this.templateManagementDetails()
      })

   }

   

   render() {
    

      const {StartDate,EndDate,TodayDate,IsOrder,IsFeaturedData, templateData, CURRENT_PAGE, ITEMS_PERPAGE, Total, IsFeaturedModalOpen, featuresItem, isShowAutoToolTip } = this.state;
    
     
      return (
         <React.Fragment>
            <div className="league-table-booster">
               <div className="animate-left">
                  <Row>
                     <Col md={12}>
                        <div className="league-details-header">
                           <div className="league-text-main-heading">Template </div>
                       
                        </div>
                     </Col>
                  </Row>
                  <>
                     <Row>
                        <Col md={12}>
                           {/* <div className="league-management-inner-header"> */}
                              <div className="league-management-filter-details-heading">
                                 <Row>
                                    <Col md={2}>
                                       <div>
                                          <label className="filter-label">Select Sports </label>
                                          <Select
                                             className="mr-15"
                                             id="selected_sport"
                                             name="selected_sport"
                                             placeholder="Select Sport"
                                             value={this.state.selected_sport}
                                             options={this.state.sportsOptions}
                                          onChange={(e) => this.handleSports(e)}
                                          />
                                       </div>
                                    </Col>
                                
                               
                               
                                 
                                    </Row>
                              </div>
                              {/* <FormGroup className="">
                                 <InputGroup className="search-input-view">
                                    <i className="icon-search"></i>
                                    <Input type="text" id="keyword" name="keyword" placeholder="Search League"
                                       value={this.state.filter.keyword}
                                       onChange={e => this.handleSearch(e)}
                                    />
                                 </InputGroup>
                              </FormGroup> */}
                           {/* </div> */}
                        </Col>
                     </Row>

                     <Row className="league-table">
                        <Col md={12} className="table-responsive common-table ">
                           <Table striped hover className="common-table  mb-0">
                              <thead>
                                 <tr>
                                    <th>S.No</th>
                                    <th >Sports </th>
                                    <th>Question</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                 </tr>
                              </thead>

                              {
                                 templateData.length > 0 ?
                                    <>
                                       {
                                          _Map(templateData, (item, idx) => {                                     
                                                                                  

                                         
                                             return (
                                                <tbody key={idx}>
                                                   <tr>
                                                      <td className="league-text-view">{((CURRENT_PAGE - 1) * ITEMS_PERPAGE) + (idx + 1)
                                                      }.</td>
                                                      <td className="league-text-view">{item.sports_name}</td>
                                                      <td className="league-text-view">
                                                        {item.name}
                                                      </td>
                                                      <td className="league-text-view">                                                        
                                                         {item.status == 1 ? 'Active' :'Inactive'}                                                        
                                                      </td>
                                                     
                                                      <td>
                                                         <div className="activate-module float-right">
                                                            {
                                                               <label className="global-switch league-switch">
                                                                  <input
                                                                     type="checkbox"
                                                                     checked={item.status == "1" ? false : true}                                                                     
                                                                     // onChange={this.handleModuleChange}
                                                                     onChange={() => this.handleModuleChange(item.status ,item.template_id)}
                                                                  />
                                                                  <span className="switch-slide round">
                                                                     <span className={`switch-on ${item.status == "1" ? 'active' : ''}`}>Enable</span>
                                                                     <span className={`switch-off ${item.status == "0" ? 'active' : ''}`}>Disable</span>
                                                                  </span>
                                                               </label>
                                                            }
                                                         </div>
                                                      </td>

                                                   </tr>
                                                </tbody>
                                             )
                                          })
                                       }
                                    </>
                                    :
                                    <tbody>
                                       <tr>
                                          <td colSpan="8">
                                             <div className="no-records">
                                                No Record Found.</div>
                                          </td>
                                       </tr>
                                    </tbody>
                              }
                           </Table>
                        </Col>
                     </Row>

                  </>


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
            {/* <FeaturedMarkPopup IsFeaturedModalOpen={IsFeaturedModalOpen} modalClose={this.featuredModalClose} featuresItem={featuresItem} /> */}
         </React.Fragment>
      )
   }
}
export default LeagueManagment;
