import React, { Component, Fragment } from "react";
import { Row, Col, Table, Button, FormGroup, Tooltip, Input, InputGroupAddon, InputGroupText, InputGroup, Label, Modal, ModalBody, ModalFooter, ModalHeader } from "reactstrap";
import _ from 'lodash';
import Images from "../../components/images";
import HF, { _Map } from "../../helper/HelperFunction";
import { notify } from 'react-notify-toast';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import LS from 'local-storage';
import Pagination from "react-js-pagination";
import { PROPS_ALL_USER_REPORT } from '../../helper/WSCalling';
import Select from 'react-select';
import moment from "moment-timezone";
// import FeaturedMarkPopup from "./FeaturedMarkPopup";
import HelperFunction, { _filter } from "../../helper/HelperFunction";
import SelectDate from "../../components/SelectDate";
import PromptModal from '../../components/Modals/PromptModal';
class UserReport extends Component {
   constructor(props) {
      super(props)
      let filter = {
         keyword: ''
      }
      this.state = {
         selected_sports_id: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
         userData: [],
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
         // EndDate: null,
         // StartDate: null,
         EndDate: new Date(),
         // EndDate: null,
         StartDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
         CancelModalIsOpen: false,
         WinningModalIsOpen: false,
         USERSTATUS : '',
         CancelPosting: true,
         CancelReason :'',
         UserID :'',
         WinningCap:'',
         SortField:'',
         CancelWinningPosting :false,
         AllowCoin : 0,
         AllowReal : 0

      }
   }

   userDetails = async (pageStatus, offset) => {
      const { StartDate, EndDate, ITEMS_PERPAGE, CURRENT_PAGE, filter, selected_sports_id, IsOrder, SortField } = this.state
      this.setState({
         ListPosting: true
      })
      let params = {        
         // "from_date": HF.getFormatedDateTime(StartDate, 'yyyy-MM-DD'),
         // "to_date": HF.getFormatedDateTime(EndDate, 'yyyy-MM-DD'),
        
         "from_date": moment(StartDate).format("YYYY-MM-DD"),        
         "to_date": moment(EndDate).format("YYYY-MM-DD"),
         "keyword": filter.keyword,
         "sort_field": SortField,
         "sort_order": IsOrder ? 'ASC': "DESC" ,
         "page": CURRENT_PAGE,
         "limit": ITEMS_PERPAGE    
      }
      
      PROPS_ALL_USER_REPORT(params).then(ResponseJson => {
         
         if (ResponseJson.response_code == NC.successCode) {
            this.setState({
               userData: ResponseJson.data.result,
               Total: ResponseJson.data.total ? ResponseJson.data.total : 0,
               AllowReal: ResponseJson.data.total ? ResponseJson.data.real_cash : 0,
               AllowCoin: ResponseJson.data.total ? ResponseJson.data.coins : 0,
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
     
      filter['keyword'] = '';
      this.setState({       
         filter: filter,
         EndDate: new Date(moment().format('D MMM YYYY')),
         // EndDate: null,
         StartDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
         CURRENT_PAGE:1    

      }, () => {
         this.userDetails()
      }
      )
   }

   componentDidMount() {
      this.userDetails()
     
   }

   handlePageChange(current_page) {
      if (current_page != this.state.CURRENT_PAGE) {
         this.setState({
            CURRENT_PAGE: current_page
         }, () => {
            this.userDetails()
         });
      }
   }

   handleSearch(keyword) {

      let filter = this.state.filter;
      filter['keyword'] = keyword.target.value;
      this.setState({
         filter: filter,
         noRecord: keyword.target.value != '' ? true : false,
         CURRENT_PAGE:1
      },
         this.SearchCodeReq
      );
   }
   SearchCodeReq() {
      this.userDetails(1)
   }

   featuredModalClose = () => {
      this.setState({
         featuresItem: {},
         IsFeaturedModalOpen: false
      }, () => { this.userDetails() })
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
      }, () => { this.userDetails()}
      )
  }

   enableStatusChange = (user_id) => {
      let params = {
         "user_id": user_id, 
         "note":''      
      }

      
     
      WSManager.Rest(NC.baseURL + NC.PROPS_UPDATE_USER_STATUS, params).then((responseJson) => {
         if (responseJson.response_code === NC.successCode) {
            notify.show(responseJson.message, "success", 3000)
           
         } else {
            notify.show(responseJson.message, "error", 3000)
            this.setState({ posting: false })
         }
         this.userDetails()

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

      
         this.userDetails()
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
            }, () => {
               LS.set('selectedSport', this.state.selected_sport)
               this.userDetails()
            })
         }
      }).catch(error => {
         notify.show(NC.SYSTEM_ERROR, "error", 3000)
      })
   }

   

   handleDate1 = (date, dateType) => { 

      // var date = new Date(WSManager.getUtcToLocal(date))
      // var dates = HF.getFormatedDateTime(new Date(WSManager.getUtcToLocal(date)), 'yyyy-MM-DD')
      // var dates = HF.getFormatedDateTime(date, 'yyyy-MM-DD')
 
      this.setState({
        
         StartDate: date,
         CURRENT_PAGE: 1
      },() => {
        
         this.userDetails()
      })
      
    
   }

   handleDate2 = (date, dateType) => {
    
      //  var date = new Date(WSManager.getUtcToLocal(date))
      // var dates = HF.getFormatedDateTime(new Date(WSManager.getUtcToLocal(date)), 'yyyy-MM-DD')
      // var dates = HF.getFormatedDateTime(date, 'yyyy-MM-DD')
    

      this.setState({
        
         EndDate: date,
         CURRENT_PAGE: 1
      }, () => {

         this.userDetails()
      })

 
   }

   

   cancelMatchModalToggle = (user_id,user_status) => {
         
      this.setState({
         CancelModalIsOpen: true,
         UserID: user_id,
         USERSTATUS: user_status
      });    
   }

   closeMatchPopup = () => {      
      this.setState({
         CancelModalIsOpen: false  ,
         CancelPosting: true
              
      });
   }

   

   winningModalToggle = (user_id ,winning_cap) => {
      this.setState({
         WinningModalIsOpen: true,
         UserID: user_id  ,
         WinningCap: winning_cap     
      });
   }

   closeWinningPopup = () => {
      this.setState({
         WinningModalIsOpen: false
      });
   }

   winningCapModal = () => {
      let { CancelWinningPosting, WinningCap } = this.state
      if (WinningCap > 0 ){
         // console.log(WinningCap,'in'); return false;
         CancelWinningPosting = false
      }

      return (
         <div>
            <Modal
               isOpen={this.state.WinningModalIsOpen}
               // toggle={this.winningModalToggle}
               className="cancel-match-modal"
            >             

               <ModalHeader>
                  <div className="modalcancel-close">
                     <div>Put Winning Limit </div>
                     <div onClick={this.closeWinningPopup}><span class="icon-close"></span></div>
                  </div>

               </ModalHeader>
               <ModalBody>
                  {/* <div className="confirm-msg">{MSG_CANCEL_REQ}</div> */}
                  <div className="inputform-box">
                     <label><span className="label-first">Monthly winning limit for this user</span> <span className="label-two"> (Reset every month's 1st)</span></label>
                    
                     {/* <InputGroup>
                        <InputGroupAddon addonType="prepend">
                           <InputGroupText>
                              {HF.getCurrencyCode()} 
                           </InputGroupText>
                        </InputGroupAddon> */}
                        <Input
                           placeholder="Enter Limit"
                           maxLength={7}
                           type="number"
                           name='winningCap'
                           value={WinningCap}
                           onChange={this.handleWinningInputChange}
                        />
                     {/* </InputGroup> */}
                  </div>
                  <div className="model_down_text">
                     <p className="note-text">To remove the limit, please put the input field value 0 and click on the save button</p>
                
                  </div>
               </ModalBody>
               <ModalFooter>
                  <Button
                     color="secondary"
                     onClick={this.winningCap}
                     disabled={CancelWinningPosting}
                  >Save</Button>{' '}
                  {/* <Button color="primary" onClick={this.winningModalToggle}>No</Button> */}
               </ModalFooter>
            </Modal>
         </div>
      )
   }



   handleInputChange = (e) => {
      let name = e.target.name
      let value = e.target.value

      let btnAction = false
      if (value.length < 3 || value.length > 400)
         btnAction = true

      this.setState({
         [name]: value,
         CancelPosting: btnAction,
         CancelReason: value
      })
   }

   handleWinningInputChange = (e) => {
      let name = e.target.name
      let value = e.target.value

      let btnAction = false
      if (value.length < 1 || value.length > 160)
         btnAction = true

      this.setState({
         [name]: value,
         CancelWinningPosting: btnAction,
         WinningCap: value
      })
   }

   cancelMatchModal = () => {
      let { CancelPosting } = this.state
      
      return (
         <div>
            <Modal
               isOpen={this.state.CancelModalIsOpen}
               // toggle={this.cancelMatchModalToggle}
               className="cancel-match-modal"
            >
               <ModalHeader><div className="modalcancel-close">
                  <div>Disable
                  </div>

                  <div onClick={this.closeMatchPopup}><span class="icon-close"></span></div>
                     
                  </div>
                  
                  </ModalHeader>
               <ModalBody>
                  {/* <div className="confirm-msg">{MSG_CANCEL_REQ}</div> */}
                  <div className="inputform-box">
                     <label>Reason</label>
                     <Input
                        required= "required"
                        minLength="3"
                        maxLength="400"
                        className ="cancel_reason"
                        rows={6}
                        type="textarea"
                        name="CancelReason"
                        placeholder="Write a reason to disable...."
                        onChange={(e) => this.handleInputChange(e)}
                     />
                  </div>
                  <div className="model_down_text">
                   <p className="note-text">(*Note :this will be displayed to the user)</p>
                   <p className="charact-limit">400 Character limit</p>
                  </div>
               </ModalBody>
               <ModalFooter>
                  <Button
                     color="secondary"
                     onClick={this.cancelMatch}
                     disabled={CancelPosting}
                  >Yes</Button>{' '}
                  <Button color="primary" onClick={this.closeMatchPopup}>No</Button>
               </ModalFooter>
            </Modal>
         </div>
      )
   }

   cancelMatch = () => {
      let { UserID, CancelReason } = this.state
      this.setState({ CancelPosting: false });

      let param = {
         user_id: UserID,
         note: CancelReason
      };     

      WSManager.Rest(NC.baseURL + NC.PROPS_UPDATE_USER_STATUS, param).then((responseJson) => {
         if (responseJson.response_code === NC.successCode) {
         
            this.setState({
               CancelPosting: false,
               CancelReason: ''
            })
           
            notify.show(responseJson.message, "success", 5000);
         }
         else if (responseJson.response_code == NC.sessionExpireCode) {
            notify.show(responseJson.message, "error", 5000);
         }
         this.userDetails()

         this.closeMatchPopup()
      })
   }

   
   winningCap = () => {
      let { UserID, WinningCap } = this.state
      this.setState({ CancelWinningPosting: false });

      let param = {
         user_id: UserID,
         winning_cap: WinningCap
      };

      // console.log(param); return false;

      WSManager.Rest(NC.baseURL + NC.PROPS_UPDATE_USER_LIMIT, param).then((responseJson) => {
         if (responseJson.response_code === NC.successCode) {

            this.setState({
               CancelWinningPosting: false,
               winning_cap: WinningCap
            })

            notify.show(responseJson.message, "success", 5000);
         }
         else if (responseJson.response_code == NC.sessionExpireCode) {
            notify.show(responseJson.message, "error", 5000);
         }
         this.userDetails()

         this.closeWinningPopup()
      })
   }


   exportReport_Get = () => {
      const { StartDate, EndDate,  filter } = this.state
      let tempFromDate = ''
      let tempToDate = ''
     
      // let sOrder = isDescOrder ? "ASC" : 'DES'
      if (StartDate != '' && EndDate != '') {
         tempFromDate = StartDate ? WSManager.getLocalToUtcFormat(StartDate, 'YYYY-MM-DD') : ''
         tempToDate = EndDate ? moment(EndDate).format("YYYY-MM-DD") : '';
      }
      //debugger;
      var query_string = 'keyword=' + filter.keyword + '&from_date=' + tempFromDate + '&to_date=' + tempToDate +'&role=2';
      var export_url = 'props/admin/report/get_user_report?';
      // console.log('query_string', query_string); return false;
      HF.exportFunction(query_string, export_url)
   }


   

   render() {
    

      const {StartDate,EndDate,TodayDate,IsOrder,IsFeaturedData, userData, CURRENT_PAGE, ITEMS_PERPAGE, Total, IsFeaturedModalOpen, featuresItem, isShowAutoToolTip } = this.state;

    
    
      const sameDateProp1 = {
         // show_time_select: false,
         // time_format: "HH:mm",
         // time_intervals: 5,
         // time_caption: "time",
         date_format: 'dd/MM/yyyy',
         handleCallbackFn: this.handleDate1,
         class_name: 'Select-control inPut icon-calender',
         year_dropdown: true,
         month_dropdown: true,
         className: ''
      }


      const sameDateProp2 = {
         // show_time_select: false,
         // time_format: "HH:mm",
         // time_intervals: 5,
         // time_caption: "time",
         date_format: 'dd/MM/yyyy',
         handleCallbackFn: this.handleDate2,
         class_name: 'Select-control inPut icon-calender',
         year_dropdown: true,
         month_dropdown: true,
         className: ''
      }

      const StartDateProps = {
         ...sameDateProp1,
         min_date: false,
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
                  {this.state.CancelModalIsOpen && this.cancelMatchModal()}
                  {this.state.WinningModalIsOpen && this.winningCapModal()}
                  <Row>
                     <Col md={12}>
                        <div className="league-details-header">
                           <div className="league-text-main-heading">User Reports </div>
                       
                        </div>
                     </Col>
                  </Row>
                  <>
                     <Row>
                        <Col md={12}>
                           {/* <div className="league-management-inner-header"> */}
                              <div className="league-management-filter-details-heading">
                                 <Row>
                                   
                                 <Col md={3}>
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
                                 </Col>
                                 <Col md={4}>
                                    <div className='inputFields inPutBg usr-clnder '>
                                       <div className = "fix-div">

                                       <label className="filter-label" htmlFor="CandleDetails">Date</label>
                                       <>
                                             <SelectDate DateProps={StartDateProps} />
                                          <i className='icon-calender Ccalender trntcalender'></i>
                                       </>
                                       </div>
                                       <div className="fix-div">

                                       <label className="filter-label end_label" htmlFor="CandleDetails">End Date</label>
                                       <>
                                             <SelectDate DateProps={EndDateProps} />
                                          <i className='icon-calender Ccalender trntcalender'></i>
                                       </>
                                       </div>


                                    </div>  
                                    
                                 </Col>
                                 <Col md={5}>
                                    {/* <div className = 'clear-filter-button'>
                                      <Button className="btn-secondary" onClick={() => this.clearFilter()}>Clear Filters</Button>
                                 </div> */}
                                    <label style={{visibility:"hidden"}} className="filter-label">Export</label>

                                    <i className="export-list icon-export"
                                       onClick={e =>  this.exportReport_Get()}></i>
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
                                    <th>User Name</th>
                                    <th className="cursor-pointer" onClick={() => this.sortLeagueManagement('total_team', IsOrder)}>Total entries <i className="icon-combined-ic"/></th>

                                    {this.state.AllowReal == 1 &&
                                       <th className="cursor-pointer" onClick={() => this.sortLeagueManagement('real_entry', IsOrder)}>Total Stake ( {HF.getCurrencyCode()} ) <i className="icon-combined-ic" /></th>}

                                    {this.state.AllowReal == 1 &&
                                    <th className="cursor-pointer" onClick={() => this.sortLeagueManagement('real_winning', IsOrder)}>Total Winning ( {HF.getCurrencyCode()} )<i className="icon-combined-ic" /></th> }

                                    {this.state.AllowReal ==1 &&
                                    <th className="cursor-pointer" onClick={() => this.sortLeagueManagement('real_profit', IsOrder)}>Operator Profit ( {HF.getCurrencyCode()} )  <i className="icon-combined-ic" /></th>}

                                    {this.state.AllowCoin ==1 && 
                                    <th className="cursor-pointer" onClick={() => this.sortLeagueManagement('coin_entry', IsOrder)}>Total Stake ( <img src={Images.COINIMG} alt="coin-img" /> ) <i className="icon-combined-ic" /></th> }

                                    {this.state.AllowCoin == 1 &&
                                    <th className="cursor-pointer" onClick={() => this.sortLeagueManagement('coin_winning', IsOrder)}>Total Winning ( <img src={Images.COINIMG} alt="coin-img" /> )<i className="icon-combined-ic" /></th> }

                                    {this.state.AllowCoin == 1 &&
                                    <th className="cursor-pointer" onClick={() => this.sortLeagueManagement('coin_profit', IsOrder)}>Operator Profit ( <img src={Images.COINIMG} alt="coin-img" /> )  <i className="icon-combined-ic" /></th> }

                                    <th className="cursor-pointer" >Winning Limit ( {this.state.AllowReal == 1 && HF.getCurrencyCode()} {this.state.AllowCoin == 1 && <img src={Images.COINIMG} alt="coin-img" />} ) </th>

                                    <th className="cursor-pointer">Action</th>
                            
                                 </tr>
                              </thead>

                              {
                                 userData.length > 0 ?
                                    <>
                                       {
                                          _Map(userData, (item, idx) => {

                                             if (item.user_status == 1){
                                                var status = 'Enabled';
                                             }else{
                                                var status = 'Disabled'
                                             }

                                             if(item.winning_cap == 0){
                                                var winningCap = 'Put Limit' ;
                                             }else{

                                                var winningCap = item.winning_cap ;

                                             }

                                                                                      
                                             return (
                                                <tbody key={idx}>
                                                   <tr>
                                                      <td><a onClick={() => this.props.history.push({ pathname: "/profile/" + item.user_unique_id, state: { activeTabId: '2' } })} className="text-click">{item.user_name}</a></td>
                                                      {/* <td>{item.user_name}</td> */}
                                                      <td>{item.total_team}</td>
                                                      {this.state.AllowReal == 1 && <td>{item.real_entry}</td> }
                                                      {this.state.AllowReal == 1 && <td>{item.real_winning}</td> }
                                                      {this.state.AllowReal == 1 && <td>{item.real_profit}</td> }

                                                      {this.state.AllowCoin == 1 && <td>{item.coin_entry}</td> }
                                                      {this.state.AllowCoin == 1 && <td>{item.coin_winning}</td> }
                                                      {this.state.AllowCoin == 1 && <td>{item.coin_profit}</td> }
                                                      <td className={`${item.winning_cap == 0 ? 'winnigColorChange' : 'grey-status'}`}><a onClick={() => this.winningModalToggle(item.user_id, item.winning_cap) }>{winningCap}</a></td>
                                                      <td className={`${item.user_status == 1 ? 'green-status' : 'grey-status'}`}>
                                                         {item.user_status == 1 && <a onClick={() => this.cancelMatchModalToggle(item.user_id, item.user_status)}>{status}</a>}
                                                         {item.user_status == 0 && <a onClick={() => this.enableStatusChange(item.user_id)}>{status}</a>}
                                                      </td>
                                                      
                                                     

                                                      {/* <td>
                                                         <div className="activate-module float-right">
                                                            {
                                                               <label className="global-switch">
                                                                  <input
                                                                     type="checkbox"
                                                                     checked={item.status == "1" ? false : true}                                                                     
                                                                     // onChange={this.handleModuleChange}
                                                                     onChange={() => this.handleModuleChange(item.status ,item.league_id)}
                                                                  />
                                                                  <span className="switch-slide round">
                                                                     <span className={`switch-on ${item.status == "1" ? 'active' : ''}`}>ON</span>
                                                                     <span className={`switch-off ${item.status == "0" ? 'active' : ''}`}>OFF</span>
                                                                  </span>
                                                               </label>
                                                            }
                                                         </div>
                                                      </td> */}

                                                   </tr>
                                                </tbody>
                                             )
                                          })
                                       }
                                    </>
                                    :
                                    <tbody>
                                       <tr>
                                          <td colSpan="10">
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
export default UserReport;
