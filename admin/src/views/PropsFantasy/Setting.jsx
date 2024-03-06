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
import { PROPS_PAYOUT_MANAGMENT_TABLE, PROPS_SAVE_SETTING } from '../../helper/WSCalling';
import Select from 'react-select';
import moment from "moment-timezone";
// import FeaturedMarkPopup from "./FeaturedMarkPopup";
import HelperFunction, { _filter } from "../../helper/HelperFunction";
import SelectDate from "../../components/SelectDate";
class Setting extends Component {
   constructor(props) {
      super(props)
      let filter = {
         keyword: ''
      }
      this.state = {
         selected_sports_id: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
         payoutData: [],
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
         EndDate: null,
         StartDate: null,
         updateArr: [],
         RequestArr: [],
         UpdateLoading: false,
         maxmumbet:'',
         minimumbet:''
      }
   }

   propsManagementDetails = async (pageStatus, offset) => {
      const { maxmumbet,minimumbet,StartDate,EndDate,ITEMS_PERPAGE, CURRENT_PAGE, filter, selected_sports_id,IsOrder } = this.state
      this.setState({
         ListPosting: true
      })
      let params = {
         "sports_id": selected_sports_id,
         "from_date": StartDate,
         "to_date": EndDate,
         "keyword": filter.keyword,
         "sort_field": "league_name",
         "sort_order": IsOrder ? 'DESC': "ASC" ,
         "page": pageStatus == 1 ? 1 : CURRENT_PAGE,
         "limit": ITEMS_PERPAGE,
      }
      
      PROPS_PAYOUT_MANAGMENT_TABLE(params).then(ResponseJson => {
         // console.log(ResponseJson);return false
         if (ResponseJson.response_code == NC.successCode) {
            // console.log(ResponseJson.data.props_config.max_bet);return false;
            this.setState({
               payoutData: ResponseJson.data.payout,
               minimumbet: ResponseJson.data.props_config.min_bet,
               maxmumbet: ResponseJson.data.props_config.max_bet
              
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


   componentDidMount() {
      this.propsManagementDetails()
   }

   handlePageChange(current_page) {
      if (current_page != this.state.CURRENT_PAGE) {
         this.setState({
            CURRENT_PAGE: current_page
         }, () => {
            this.propsManagementDetails()
         });
      }
   }

   handleSearch(keyword) {

      let filter = this.state.filter;
      filter['keyword'] = keyword.target.value;
      this.setState({
         filter: filter,
         noRecord: keyword.target.value != '' ? true : false
      },
         this.SearchCodeReq
      );
   }
   SearchCodeReq() {
      this.propsManagementDetails(1)
   }

   featuredModalClose = () => {
      this.setState({
         featuresItem: {},
         IsFeaturedModalOpen: false
      }, () => { this.propsManagementDetails() })
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
          IsOrder: Order,
          CURRENT_PAGE: 1,
      }, () => { this.propsManagementDetails()}
      )
  }

   handleModuleChange = (status, payout_id) => {
      let params = {
         "payout_id": payout_id,       
      }
     
      WSManager.Rest(NC.baseURL + NC.PROPS_UPDATE_PAYOUT_STATUS, params).then((responseJson) => {
         if (responseJson.response_code === NC.successCode) {
            notify.show(responseJson.message, "success", 3000)
           
         } else {
            notify.show(responseJson.message, "error", 3000)
            this.setState({ posting: false })
         }
         this.propsManagementDetails()

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
      }, () => {

      
         this.propsManagementDetails()
      })
   }

   savePropsSetting = () => {
      const { maxmumbet, minimumbet } = this.state;  

      let params = {
         "min_bet": minimumbet,
         "max_bet": maxmumbet,         
      }
      // console.log(params);return false;
      WSManager.Rest(NC.baseURL + NC.PROPS_SAVE_SETTING, params).then((responseJson) => {
         console.log(responseJson,'kkkkk')
         if (responseJson.response_code == NC.successCode) {
            notify.show(responseJson.message, "success", 3000)
            
            this.setState({
               maxmumbet: maxmumbet,
               minimumbet: minimumbet
            }, () => {
             
               this.propsManagementDetails()
            })
         }
      }).catch(error => {
         notify.show(NC.SYSTEM_ERROR, "error", 3000)
      })
   }



   pointChange = (idx, value) => {
      let temp = this.state.payoutData;
      console.log(temp);
      console.log(idx);
      // console.log(value.length,'nilesh.length');

      if (isNaN(value) == true){
         notify.show('Number Only', "error", 3000);
         return false;
          
      }
      
      value = (value.indexOf(".") >= 0) ? (value.substr(0, value.indexOf(".")) + value.substr(value.indexOf("."), 3)) : value;

      temp[idx]['points'] = value
      temp[idx]['isChanged'] = true    

      this.setState({         
         UpdateLoading: true,
         updateArr: temp
      },()=>{

      })
   }

   updatePayout() {
      let temparray = this.state.updateArr.filter(obj => obj.isChanged);    

      let temp = []
      temparray.map((item,idx) => {
         temp.push({
            'payout_id': item.payout_id,
            'points': item.points,
         })
      })
      let RequestArr = temp
      // console.log(RequestArr,'RequestArr');return false;
      this.setState({ UpdateLoading: false })
      WSManager.Rest(NC.baseURL + NC.UPDATE_MASTER_PAYOUT_POINTS, RequestArr).then((responseJson) => {
         if (responseJson.response_code === NC.successCode) {
            notify.show(responseJson.message, "success", 3000);
            this.setState({ UpdateLoading: true }, ()=>{
               this.propsManagementDetails()
            })
         }
      }).catch((error) => {
         notify.show(NC.SYSTEM_ERROR, "error", 3000);
      })
   }

   handleFieldVal = (e) => {
      if (e) {
         let name = e.target.name
         let value = e.target.value

         this.setState({ [name]: value })
      }
   }
   

   render() {
    

      const { minimumbet, maxmumbet,TodayDate, IsOrder, IsFeaturedData, payoutData, CURRENT_PAGE, ITEMS_PERPAGE, Total, IsFeaturedModalOpen, featuresItem, isShowAutoToolTip } = this.state;
      
      return (
         <React.Fragment>
            <div className="league-table-booster">
               <div className="animate-left">
                  <Row>
                     <Col md={12}>
                        <div className="league-details-header">
                           <div className="league-text-main-heading">Props Settings </div>
                       
                        </div>
                     </Col>
                  </Row>
                  <>
                     <Row>
                        <Col md={12}>
                           {/* <div className="league-management-inner-header"> */}
                              <div className="props-title"> <h5>Entry account limit</h5></div>
                              <div className="setting-management-filter-details-heading">
                              <Row>
                                 <Col md={12}>
                                    <div className = "setting_heading">
                                     
                                       <p>(* Note: Universal entry limit for single entry)</p>
                                    </div>
                                 </Col>
                                 </Row>
                                 <Row>
                                    <Col md={3}>
                                       <div>
                                          <label className="filter-label">Minimum bet </label>
                                       <Input
                                          maxLength="50"
                                          className={"required"}
                                          id="minimumbet"
                                          name="minimumbet"
                                          value={minimumbet}                                         
                                          onChange={(e) => this.handleFieldVal(e)}
                                          placeholder="Enter Value"
                                       />
                                       </div>
                                    </Col>
                                 <Col md={3}>
                                       <label className="filter-label">Maximum bet</label>
                                    <Input
                                       maxLength="50"
                                       className={"required"}
                                       id="maxmumbet"
                                       name="maxmumbet"
                                       value={maxmumbet}
                                       onChange={(e) => this.handleFieldVal(e)}
                                       placeholder="Enter Value"
                                    />
                                 </Col>
                                 <Col md={3}>
                                    <div className='save-button'>
                                       <Button
                                          // disabled={subPosting}
                                          onClick={() => this.savePropsSetting()}                                          
                                          className='save_btn'>
                                          Save
                                       </Button>
                                    </div>
                                    
                                 </Col>
                                 <Col md={4}>
                                  
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

                     <Row>
                        <Col md={12}>
                           <div className="league-details-header manage-header-payout">
                              <div className="league-text-main-heading">Manage Payout </div>

                           </div>
                        </Col>
                     </Row>

                     

                     <Row className="league-table">
                        <Col md={12} className="table-responsive common-table ">
                           <Table striped hover className="common-table  mb-0">
                              <thead>
                                 <tr>
                                    <th>Type</th>
                                    {/* <th className="cursor-pointer" onClick={() => this.sortLeagueManagement('league_name', IsOrder)}>League Name <i className="icon-combined-ic"/></th> */}
                                    <th>picks</th>
                                    <th>Correct Picks</th>
                                    <th>Multiplier</th>
                                    <th>Action</th>
                                 </tr>
                              </thead>

                              {
                                 payoutData.length > 0 ?
                                    <>
                                       {
                                          _Map(payoutData, (item, idx) => {

                                             if (item.payout_type == 1){
                                                 
                                                var typeName = 'Flex';
                                             }else{
                                                var typeName = 'Powerplay';
                                             }
 
                                             return (
                                                <tbody key={idx}>
                                                   <tr>
                                                 
                                                      <td className="league-text-view">{typeName}</td>
                                                      <td>{item.picks}</td>
                                                      <td>{item.correct}</td>
                                                      <td>  <Input                                                         
                                                         type="text"
                                                         name={`points_${idx}`}
                                                         className="form-control"
                                                         value={item.points}
                                                         onChange={(e) => this.pointChange(idx, e.target.value)}
                                                         
                                                      /></td>
                                                     

                                                      <td>
                                                         <div className="activate-module float-right">
                                                            {
                                                               <label className="global-switch props-switch">
                                                                  <input
                                                                     type="checkbox"
                                                                     checked={item.status == "1" ? false : true}                                                                     
                                                                     // onChange={this.handleModuleChange}
                                                                     onChange={() => this.handleModuleChange(item.status, item.payout_id)}
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

                     
                           <div className='save-payout-button'>
                              <Button
                              disabled={!this.state.UpdateLoading}
                              onClick={() => this.updatePayout()}
                                 className='save_payout_btn'>
                                 Save
                              </Button>
                           </div>

                        {/* {
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
                        } */}
                     </Col>
                  </Row>
               </div>
            </div>
            {/* <FeaturedMarkPopup IsFeaturedModalOpen={IsFeaturedModalOpen} modalClose={this.featuredModalClose} featuresItem={featuresItem} /> */}
         </React.Fragment>
      )
   }
}
export default Setting;
