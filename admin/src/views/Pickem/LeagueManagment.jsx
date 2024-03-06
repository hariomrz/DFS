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
import { PICKEM_LEAGUE_MANAGMENT_TABLE } from '../../helper/WSCalling';
import FeaturedMarkPopup from "./FeaturedMarkPopup";
import HelperFunction, { _filter } from "../../helper/HelperFunction";
class LeagueManagment extends Component {
   constructor(props) {
      super(props)
      let filter = {
         keyword: ''
      }
      this.state = {
         selected_sports_id: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
         leagueData: [],
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
         is_featur: 0
      }
   }

   leagueManagementDetails = async (pageStatus, offset) => {
      const {is_featur, ITEMS_PERPAGE, CURRENT_PAGE, filter, selected_sports_id,IsOrder } = this.state
      this.setState({
         ListPosting: true
      })
      let params = {
         "sports_id": selected_sports_id,
         "keyword": filter.keyword,
         "sort_field": "league_name",
         "sort_order": IsOrder ? 'DESC': "ASC" ,
         "current_page": pageStatus == 1 ? 1 : CURRENT_PAGE,
         "items_perpage": ITEMS_PERPAGE,
         "is_featured" : is_featur
      }
      
      PICKEM_LEAGUE_MANAGMENT_TABLE(params).then(ResponseJson => {
         if (ResponseJson.response_code == NC.successCode) {
            this.setState({
               leagueData: ResponseJson.data.result,
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

   componentDidMount() {
      this.leagueManagementDetails()
   }

   handlePageChange(current_page) {
      if (current_page != this.state.CURRENT_PAGE) {
         this.setState({
            CURRENT_PAGE: current_page
         }, () => {
            this.leagueManagementDetails()
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
      this.leagueManagementDetails(1)
   }

   featuredModalClose = () => {

      // console.log('nilesh here')
      this.setState({
         // featuresItem: {},
         IsFeaturedModalOpen: false
      }, () => { this.leagueManagementDetails() })
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
      },() =>{this.leagueManagementDetails()}
      )
  }

  handlefeatureChange = (is_featured) => {
     
      if(is_featured == 0){
         var featured = 1;
      }else{
          var featured = 0;
      }
       this.setState({ is_featur: featured,CURRENT_PAGE: 1  })

       let params = {
         "sports_id": this.state.selected_sports_id,
         "keyword": this.state.filter.keyword,
         "sort_field": "league_name",
         "sort_order": this.state.IsOrder ? 'DESC': "ASC" ,
         "current_page": 1,
         "items_perpage": this.state.ITEMS_PERPAGE,
         "is_featured": featured
      }
      
      PICKEM_LEAGUE_MANAGMENT_TABLE(params).then(ResponseJson => {
         if (ResponseJson.response_code == NC.successCode) {
            this.setState({
               leagueData: ResponseJson.data.result,
               Total: ResponseJson.data.total ? ResponseJson.data.total : 0,
               IsFeaturedData:ResponseJson.data.count_featured
            }, () => {
            //    let filterData = _filter(ResponseJson.data.result, (obj) => {
            //       return obj.is_featured == 1
            //   });
               this.setState({
                  // ListPosting: false,
                  // filterData:filterData
               })
            })
         } else {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
         }
      }).catch(error => {
         notify.show(NC.SYSTEM_ERROR, "error", 3000)
      })
    
   } 

   render() {
      const {IsOrder,IsFeaturedData, leagueData, CURRENT_PAGE, ITEMS_PERPAGE, Total, IsFeaturedModalOpen, featuresItem, isShowAutoToolTip } = this.state;
      return (
         <React.Fragment>
            <div className="league-table-booster">
               <div className="animate-left">
                  <Row>
                     <Col md={12}>
                        <div className="league-details-header">
                           <div className="league-text-main-heading">League Management <i className="icon-info-border cursor-pointer" id="AutoTooltip" /></div>
                           <Tooltip
                              placement="right"
                              isOpen={isShowAutoToolTip} target="AutoTooltip"
                              toggle={() => this.AutoToolTipToggle(1)}
                           >You can mark upto 3 leagues in featured</Tooltip>
                        </div>
                     </Col>
                  </Row>
                  <>
                     <Row>
                        <Col md={5}>
                           <div className="league-management-inner-header">
                              <div className="league-management-details-heading">Featured Leagues: {IsFeaturedData}</div>
                                </div>
                           </Col>
                           <Col md={4}>
                             <div className="inline-div-style">

                              
                               <div className="activate-module flaot-css">
                                  {/* <div className="league-management-details-headinga">Featured</div> */}
                                  <label className="fetured-label">Featured</label>
                                    {
                                       <label className="global-switch">
                                          <input
                                             type="checkbox"
                                             checked={this.state.is_featur == "1" ? false : true}
                                             onChange={() => this.handlefeatureChange(this.state.is_featur)}
                                             // onChange={this.handleModuleChange}
                                          />
                                          <span className="switch-slide round">
                                             <span className={`switch-on ${this.state.is_featur == "1" ? 'active' : ''}`}>YES</span>
                                             <span className={`switch-off ${this.state.is_featur == "0" ? 'active' : ''}`}>NO</span>
                                          </span>
                                       </label>
                                    }
                                 </div>
                                 </div>
                              </Col>
                            <Col md={3}>
                               <div className="inline-div-style">
                              <FormGroup className="">
                                 <InputGroup className="search-input-view">
                                    <i className="icon-search"></i>
                                    <Input type="text" id="keyword" name="keyword" placeholder="Search League"
                                       value={this.state.filter.keyword}
                                       onChange={e => this.handleSearch(e)}
                                    />
                                 </InputGroup>
                              </FormGroup>
                              </div>
                              </Col>
                           {/* </div> */}
                     
                     </Row>

                     <Row className="league-table">
                        <Col md={12} className="table-responsive common-table ">
                           <Table striped hover className="common-table  mb-0">
                              <thead>
                                 <tr>
                                    <th>S.No</th>
                                    <th className="cursor-pointer" onClick={() => this.sortLeagueManagement('league_name', IsOrder)}>League Name <i className="icon-combined-ic"/></th>
                                    <th>League Duration</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                 </tr>
                              </thead>

                              {
                                 leagueData.length > 0 ?
                                    <>
                                       {
                                          _Map(leagueData, (item, idx) => {
                                             return (
                                                <tbody key={idx}>
                                                   <tr>
                                                      <td className="league-text-view">{((CURRENT_PAGE - 1) * ITEMS_PERPAGE) + (idx + 1)
                                                      }.</td>
                                                      <td className="league-text-view">{item.league_name}</td>
                                                      <td className="league-text-view">
                                                         {/* {WSManager.getUtcToLocalFormat(new Date(item.league_schedule_date), 'DD/MM/YY ')} */}
                                                         {HF.getFormatedDateTime(item.league_schedule_date, 'DD/MM/YY ')}
                                                         -
                                                         {/* {WSManager.getUtcToLocalFormat(new Date(item.league_last_date), 'DD/MM/YYYY')} */}
                                                         {HF.getFormatedDateTime(item.league_last_date, 'DD/MM/YYYY')}

                                                      </td>
                                                      <td className="league-text-view">
                                                         {item.is_featured == 1 ? <span> Featured</span> : "--"}
                                                      </td>
                                                      <td className="fetaured-mark-view cursor-pointer"
                                                         onClick={() => this.featuredModalOpen(item)}
                                                      >
                                                         {item.is_featured == 1 ? <> <i className="icon-close-ic" /> <span>Remove from Featured</span></>
                                                            :
                                                            <div className="mr-5"> <i className="icon-check-ic" /> <span>Mark as Featured</span></div>
                                                         }
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
            <FeaturedMarkPopup IsFeaturedModalOpen={IsFeaturedModalOpen} modalClose={this.featuredModalClose} featuresItem={featuresItem} />
         </React.Fragment>
      )
   }
}
export default LeagueManagment;
