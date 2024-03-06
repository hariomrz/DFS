import React, { Component, Fragment } from "react";
import { Row, Col, Table, Button, FormGroup, Tooltip, Input, InputGroup, Label, Modal, ModalBody, ModalFooter, ModalHeader } from "reactstrap";
import _, { isEmpty } from 'lodash';
import Images from "../../components/images";
import HF, { _Map, _isEmpty } from "../../helper/HelperFunction";
import { notify } from 'react-notify-toast';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import LS from 'local-storage';
import Pagination from "react-js-pagination";
import { PROPS_PLAYER_LIST } from '../../helper/WSCalling';
import Select from 'react-select';
import moment from "moment-timezone";
// import FeaturedMarkPopup from "./FeaturedMarkPopup";
import HelperFunction, { _filter } from "../../helper/HelperFunction";
import SelectDate from "../../components/SelectDate";
class PropsPlayer extends Component {
   constructor(props) {
      super(props)
      let filter = {
         keyword: ''
      }
      this.state = {
         selected_sports_id:  NC.sportsId,
         PropsData: [],
         AllPropsData: [],
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
         FilterPlayer : '',
         matchOptions :[],
         selected_match:'',
         propsOptions :[],
         selected_props:'',
         updateArr: [],
         UpdateLoading: false,
         selected_match_id : '',
         selected_prop_id : ''

      }
   }

   playerDetails = async (pageStatus, offset) => {
      const { StartDate, EndDate, ITEMS_PERPAGE, CURRENT_PAGE, filter, selected_sports_id, selected_match_id, selected_prop_id,IsOrder } = this.state
      this.setState({
         ListPosting: true
      })
      let params = {
         "sports_id": selected_sports_id,
         "prop_id": selected_prop_id,
         "season_id": selected_match_id,     
         // "from_date": HF.getFormatedDateTime(StartDate, 'yyyy-MM-DD'),
         "from_date": moment(StartDate).format("YYYY-MM-DD"),
         // "to_date": HF.getFormatedDateTime(EndDate, 'yyyy-MM-DD'),
         "to_date": moment(EndDate).format("YYYY-MM-DD"),
         "keyword": filter.keyword,
         "sort_field": "full_name",
         "sort_order": IsOrder ? 'ASC': "DESC" ,
         "page": pageStatus == 1 ? 1 : CURRENT_PAGE,
         "limit": ITEMS_PERPAGE,
      }
      
      PROPS_PLAYER_LIST(params).then(ResponseJson => {
         // console.log(ResponseJson);return false
         if (ResponseJson.response_code == NC.successCode) {
            this.setState({
               PropsData: ResponseJson.data.result,
               AllPropsData: ResponseJson.data.result,
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
      const { ITEMS_PERPAGE, CURRENT_PAGE, filter, selected_sports_id, IsOrder, selected_props, selected_match, selected_sport } = this.state
      console.log('yes');
      filter['keyword'] = '';
      this.setState({       
         filter: filter,
         EndDate: new Date(moment().format('D MMM YYYY')),
         StartDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
         selected_prop_id : '',
         selected_match_id : '',         
         selected_props :'',
         selected_match : '',
         CURRENT_PAGE: 1
        
      }, () => {
         this.playerDetails()
      }
      )
   }

   componentDidMount() {
      this.getSports();
      this.playerDetails()      
      this.getFilterData();
      // this.clearFilter();
   }

   handlePageChange(current_page) {
      if (current_page != this.state.CURRENT_PAGE) {
         this.setState({
            CURRENT_PAGE: current_page
         }, () => {
            this.playerDetails()
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
      this.playerDetails(1)
   }

   featuredModalClose = () => {
      this.setState({
         featuresItem: {},
         IsFeaturedModalOpen: false
      }, () => { this.playerDetails() })
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
      }, () => { this.playerDetails()}
      )
  }

   handleModuleChange = (status,season_prop_id) => {

      console.log(status,'status');
      console.log(season_prop_id,'season_prop_id');
     
   }

   handleSports = (e,name) => {   
      const value = e.value
      const Labels = e.label

      // [name] = Labels   
      this.setState({
         // sports_id: value,
         selected_sports_id: value,
         selected_sport: e.value,
         selected_prop_id: '',
         selected_match_id: '',
         CURRENT_PAGE :1
      }, () => {

      
         this.playerDetails()
         this.getFilterData()
      })
   }

   handleMatch = (e, name) => {
      const value = e.value
      const Labels = e.label

      // [name] = Labels   
      this.setState({       e,
         selected_match_id: value,
         selected_match: e.value,
         CURRENT_PAGE: 1
         
      }, () => {


         this.playerDetails()
      })
   }

   handleProps = (e) => {
      // console.log('handleprops'); return false;
      const value = e.value
      const Labels = e.label

      // [name] = Labels   
      this.setState({       
         selected_prop_id: value,
         selected_props: e.value,
         CURRENT_PAGE: 1
      }, () => {
         let PropsData = this.state.AllPropsData.filter(obj => obj.prop_id == value)
         this.setState({
            PropsData: PropsData
         })
         this.playerDetails()
      })
   }

   

   getSports = () => {
      let params = {}
      WSManager.Rest(NC.baseURL + NC.PROPS_ALL_SPORTS_LIST, params).then((responseJson) => {        
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
               selected_prop_id: '',
               selected_match_id: '',
               selected_props: '',
               selected_match: '',
            }, () => {
               LS.set('selectedSport', this.state.selected_sport)
            
            })
         }
      }).catch(error => {
         notify.show(NC.SYSTEM_ERROR, "error", 3000)
      })
   }


   getFilterData = () => {
      let params = { sports_id: this.state.selected_sports_id }
      // console.log(params);return false;
      WSManager.Rest(NC.baseURL + NC.PROPS_FILTER_DATA, params).then((responseJson) => {
         
         if (responseJson.response_code == NC.successCode) {

            // console.log(responseJson.data.match, 'response');return false;

          
            let matchOptions = [{"label":"All","value":""}];
            _.map(responseJson.data.match, function (data) {
               matchOptions.push({
                  value: data.season_id,
                  label: data.home + ' vs ' + data.away + ' - ' + HF.getFormatedDateTime(data.scheduled_date, 'YYYY-MM-DD hh:mm A') 
               })
            })

            let propsOptions = [{"label":"All","value":""}];
            _.map(responseJson.data.props, function (data) {
               propsOptions.push({
                  value: data.prop_id,
                  label: data.name,
               })
            })

            // this.setState({ leagueList: tempLeagueList, groupList: tempGroupList, statusList: responseJson.status_list });
            this.setState({
               matchOptions: matchOptions,
               propsOptions: propsOptions,
               // selected_match: this.state.selected_match ? this.state.selected_match : matchOptions[0].value,
               // selected_props: this.state.selected_props ? this.state.selected_props : propsOptions[0].value,

               selected_match: this.state.selected_match ,
               selected_props: this.state.selected_props ,
            }, () => {
               LS.set('selectedSport', this.state.selected_sport)
               this.playerDetails()
            })
         }
      }).catch(error => {
         notify.show(NC.SYSTEM_ERROR, "error", 3000)
      })
   }


   handleDate1 = (date, dateType) => {
    

      // var dates = HF.getFormatedDateTime(new Date(WSManager.getLocalToUtcFormat(date)), 'yyyy-MM-DD')
      // var dates = HF.getFormatedDateTime(date, 'yyyy-MM-DD')
      var dates = date
   
      this.setState({
         // Validedate: this.validateDate(date)
         StartDate: dates,
         CURRENT_PAGE: 1
      },() => {
        
         this.playerDetails()
      })    
     
   }

   handleDate2 = (date, dateType) => {
    
      // var dates = HF.getFormatedDateTime(new Date(WSManager.getLocalToUtcFormat(date)), 'yyyy-MM-DD')
      // var dates = HF.getFormatedDateTime(date, 'yyyy-MM-DD')  
      var dates = date 

      this.setState({
         // Validedate: this.validateDate(date)
         EndDate: dates,
         CURRENT_PAGE: 1
      }, () => {

         this.playerDetails()
      })      
   }

   pointChange = (idx, value) => {
      let temp = this.state.PropsData;    

      if (value == 0){
         var newStatus = 1;
      }else{
         var newStatus = 0;
      }
      temp[idx]['status'] = newStatus
      temp[idx]['isChanged'] = true

      this.setState({
         UpdateLoading: true,
         updateArr: temp
      }, () => {
         this.updatePlayerStatus();
      })
   }

   updatePlayerStatus() {

      let temparray = this.state.updateArr.filter(obj => obj.isChanged);

      let temp = []
      temparray.map((item, idx) => {
         temp.push({
            'season_prop_id': item.season_prop_id,
            'status': item.status,
         })
      })  

      let RequestArr = temp

      this.setState({ UpdateLoading: false })
      WSManager.Rest(NC.baseURL + NC.UPDATE_PLAYER_STATUS, RequestArr).then((responseJson) => {
         if (responseJson.response_code === NC.successCode) {
            notify.show(responseJson.message, "success", 3000);
            this.setState({ UpdateLoading: true })
         }
      }).catch((error) => {
         notify.show(NC.SYSTEM_ERROR, "error", 3000);
      })

   }

   

   render() {
    

      const { StartDate, EndDate, FilterPlayer, TodayDate, IsOrder, IsFeaturedData, PropsData, CURRENT_PAGE, ITEMS_PERPAGE, Total, IsFeaturedModalOpen, featuresItem, isShowAutoToolTip } = this.state;
     
   

      const sameDateProp1 = {
     
         date_format: 'dd/MM/yyyy',
         handleCallbackFn: this.handleDate1,
         class_name: 'Select-control inPut icon-calender',
         year_dropdown: true,
         month_dropdown: true,
         className: ''
      }


      const sameDateProp2 = {

         date_format: 'dd/MM/yyyy',
         handleCallbackFn: this.handleDate2,
         class_name: 'Select-control inPut icon-calender',
         year_dropdown: true,
         month_dropdown: true,
         className: ''
      }

      const StartDateProps = {
         ...sameDateProp1,
         min_date:false,
         max_date: new Date(EndDate),
         sel_date: StartDate,
         date_key: 'StartDate',
         place_holder: 'From',
         // className: 'icon-calender Ccalender'
      }

      const EndDateProps = {
         ...sameDateProp2,
         min_date: new Date(StartDate),
         max_date: null,
         sel_date: EndDate,
         date_key: 'EndDate',
         place_holder: 'To',
         // className: 'icon-calender Ccalender'
      }

    
      return (
         <React.Fragment>
            <div className="league-table-booster">
               <div className="animate-left">
                  <Row>
                     <Col md={12}>
                        <div className="league-details-header">
                           <div className="league-text-main-heading">Players </div>
                       
                        </div>
                     </Col>
                  </Row>
                  <>

                     <Row>
                        <Col md={12}>
                           {/* <div className="league-management-inner-header"> */}
                           <div className="league-management-filter-details-heading">
                              <ul>
                                 <li>
                                    <div>
                                       <label className="filter-label">Select Sports </label>
                                       <Select
                                          className="mr-15"
                                          id="selected_sport"
                                          name="selected_sport"
                                          placeholder="All"
                                          value={this.state.selected_sport}
                                          options={this.state.sportsOptions}
                                          onChange={(e) => this.handleSports(e)}
                                       />
                                    </div>
                                 </li>
                                 <li>
                                    <div>
                                  
                                       <label className="filter-label">Props </label>
                                       <Select
                                          className="mr-15"
                                          id="selected_props"
                                          name="selected_props"
                                          placeholder="All"
                                          value={this.state.selected_props}
                                          options={this.state.propsOptions}
                                          onChange={(e) => this.handleProps(e)}
                                       />
                                    </div>
                                 </li>
                                 <li>
                                    <div>
                                       <label className="filter-label">Fixture </label>
                                       <Select
                                          className="mr-15"
                                          id="selected_match"
                                          name="selected_match"
                                          placeholder="All"
                                          value={this.state.selected_match}
                                          options={this.state.matchOptions}
                                          onChange={(e) => this.handleMatch(e)}
                                       />
                                    </div>
                                 </li>
                                 <li>
                                    <div>
                                    <label className="filter-label">Search</label>
                                    <FormGroup className="">
                                       <InputGroup className="search-input-view">
                                          <i className="icon-search"></i>
                                          <Input type="text" id="keyword" name="keyword" placeholder="Search"
                                             value={this.state.filter.keyword}
                                             onChange={e => this.handleSearch(e)}
                                          />
                                       </InputGroup>
                                    </FormGroup>
                                    </div>
                                 </li>
                                 <li>
                                    <div className='inputFields inPutBg plyer'>
                                       <div className="fix-div">

                                          <label className="filter-label" htmlFor="CandleDetails">Date</label>
                                          <>
                                             <SelectDate DateProps={StartDateProps} />
                                             <i className='icon-calender Ccalender trntcalender'></i>
                                          </>
                                       </div> 
                                       </div>                                    

                                 </li>

                                 <li>
                                    <div className='inputFields inPutBg plyer'>
                                       
                                       <div className="fix-div">

                                          <label className="filter-label end_label" htmlFor="CandleDetails">End Date</label>
                                          <>
                                             <SelectDate DateProps={EndDateProps} />
                                             <i className='icon-calender Ccalender trntcalender'></i>
                                          </>
                                       </div>


                                    </div>

                                 </li>
                                 <li >
                                    <div>
                                       <label className="filter-label end_label" htmlFor="CandleDetails">clear</label>
                                    <div className='clear-filter-buttons'>
                                       <Button className="btn-secondary" onClick={() => this.clearFilter()}>Clear Filters</Button>
                                    </div>
                                    </div>
                                 </li>

                              </ul>
                           </div>
                         
                        </Col>
                     </Row>

                     <Row className="league-table">
                        <Col md={12} className="table-responsive common-table ">
                           <Table striped hover className="common-table  mb-0">
                              <thead>
                                 <tr>
                                    <th>Player Name</th>
                                    <th>Display Name</th>
                                    <th>Fixture</th>
                                    <th>Position</th>
                                    <th>Props</th>
                                    <th>Props Value</th>
                                    <th>Action</th>
                                 </tr>
                              </thead>

                              {
                                 PropsData.length > 0 ?
                                    <>
                                       {
                                          _Map(PropsData, (item, idx) => {
                                             let PropName = this.state.propsOptions.filter(obj => obj.value == item.prop_id)

                                             // console.log(PropName,'PropName'); return false;
                                             if (_isEmpty(PropName)){
                                                var PropNames =  '-';
                                             }else{
                                                var PropNames = PropName[0].label;
                                             }

                                             // var dateOnly = HF.getFormatedDateTime(item.scheduled_date, 'DD/MM/YY - HH:mm A')
                                             var dateOnly = HF.getFormatedDateTime(item.scheduled_date, 'DD/MM/YY - hh:mm A')

                                             // { HF.getFormatedDateTime(item.season_scheduled_date, 'D-MMM-YYYY hh:mm A') }

                                             // { HF.getFormatedDateTime(item.item.scheduled_date, 'DD/MM/YY - HH:mm A') }
                                                                                   
                                             return (
                                                <tbody key={idx}>
                                                   <tr>
                                                      <td>{item.full_name}</td>
                                                      <td>{item.display_name}</td>                                                  
                                                      <td>{item.match_name} | {dateOnly}</td>
                                                      <td>{item.position}</td>
                                                      <td>{PropNames}</td>
                                                      <td>{item.points}</td>                                                

                                                      <td>
                                                         <div className="activate-module float-right">
                                                            {
                                                               <label className="global-switch props-switch">
                                                                  <input
                                                                     type="checkbox"
                                                                     checked={item.status == "1" ? false : true}                                                                     
                                                                     // onChange={this.handleModuleChange}
                                                                     onChange={() => this.pointChange(idx, item.status)}
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

                  {/* <Row>
                     <Col md={12}>


                        <div className='save-payout-button'>
                           <Button
                              disabled={!this.state.UpdateLoading}
                              onClick={() => this.updatePlayerStatus()}
                              className='save_payout_btn'>
                              Save
                           </Button>
                        </div>

                     </Col>
                  </Row> */}
               </div>
            </div>
            {/* <FeaturedMarkPopup IsFeaturedModalOpen={IsFeaturedModalOpen} modalClose={this.featuredModalClose} featuresItem={featuresItem} /> */}
         </React.Fragment>
      )
   }
}
export default PropsPlayer;
