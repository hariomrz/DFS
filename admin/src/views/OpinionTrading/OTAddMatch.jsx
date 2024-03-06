import React, { Component } from 'react';
import { Col, Row, Button, Input, Tooltip } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import _, { isUndefined } from 'lodash';
import LS from 'local-storage';
import { notify } from 'react-notify-toast';
import HF, { _times, _Map, _isUndefined, _isEmpty, _cloneDeep, _isNull } from "../../helper/HelperFunction";
import Images from '../../components/images';
import { PT_TIE_BREAKER } from '../../helper/Message';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import moment from "moment";
export default class OTAddMatch extends Component {
    constructor(props) {
        super(props);
        let filter = {
            current_page: 1,
            items_perpage: 50,
            type: 1
        }
        this.state = {
            league_id: '',
            season_id: '',
            sports_id: '',
            season_game_uid: '',
            away_flag: '',
            away_id: '',
            home_id: '',
            home_flag: '',
            correct: '',
            // question: '',
            wrong: '',
            league_name: '',
            match: '',
            scheduled_date: '',
            modified_date: '',
            question: [],
            queFilled: 0,
            draftQuestion: false,
            tieBreakerShow: false,
            checkedTie: false,
            tieBreakerQue: {},
            startRange: '',
            endRange: '',
            isStatsValid: true,
            showQueInfo: false,
            checkSDPosting: true,
            checkPMPosting: true,
            checkedMatch: false,
            EndDate: new Date(),       
            StartDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
            rows: [{question:'', ExpireDate:""}],
            // SeasonDetails: this.props.location.state && this.props.location.state.selectedObj
            SeasonDetails: [],
            savePosting: false,
            
        };
        // this.handleChange = this.handleChange.bind(this);
    }

     seasonDetail = () => {
        let { season_id, league_id } = this.state 
 
        let params = {           
            season_id: this.props.match.params.season_id,
            league_id: this.props.match.params.league_id
        }
        
        WSManager.Rest(NC.baseURL + NC.TRADE_SEASON_DETAIL, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                // console.log(Response.data,'Response')
                // this.props.history.push({ pathname: '/picksfantasy/createtemplatecontest/' + league_id + '/' + season_id })

                 this.setState({
                     SeasonDetails : Response.data
                  })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
            // this.setState({
            //     checkPMPosting: true
            // })
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }


    componentDidMount = () => {

      
        let params = {           
            season_id: this.props.match.params.season_id,
            league_id: this.props.match.params.league_id
        }
        
        WSManager.Rest(NC.baseURL + NC.TRADE_SEASON_DETAIL, params).then(Response => {
            if (Response.response_code == NC.successCode) {
               
                 this.setState({
                     SeasonDetails : Response.data
                  })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        },[])

    }


    handleInputChange = (event, item, idx) => {
        let name = event.target.name
        let value = event.target.value
        let tmpArray = this.state.question
        tmpArray[idx][name] = value
        this.setState({ question: tmpArray }, () => {
            // LS.set('queList',tmpArray)
            this.validateQueList()
        })
    }

    callJsonParser = (data) => {
        try {
            return JSON.parse(data)
        }
        catch {
            return data
        }
    }

    componentWillUnmount = () => {
    }


    publishFixture = () => {
        let { season_id, league_id } = this.state
        let tmpArray = []
        this.setState({
            checkPMPosting: false
        })
        for (let obj of this.state.question) {
            if (obj.name != '' || obj.option_1 != '' || obj.option_2 != '') {
                tmpArray.push(obj)
            }
        }
        let params = {
            question: this.state.question,
            season_id: this.state.season_id,
            tie_breaker_question: this.state.tieBreakerQue
        }
        WSManager.Rest(NC.baseURL + NC.PF_PUBLISH_FIXTURE, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.props.history.push({ pathname: '/picksfantasy/createtemplatecontest/' + league_id + '/' + season_id })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
            this.setState({
                checkPMPosting: true
            })
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }



 

    checkValid = () => {
        if (!this.state.checkedTie) {
            return this.state.queFilled == 0 && !this.state.draftQuestion
        }
        else {
            return this.state.queFilled == 0 && !this.state.draftQuestion && (this.state.endRange == '' || this.state.startRange == '' || this.state.tieBreakerQue == '')
        }
    }



    // ghjkloiuytrfghjkmnbvcdxszaqwer
    handleImgInputChange = (event, item, idx) => {
        let name = event.target.name
        let value = event.target.value
        let tmpArray = this.state.question
        if (value.length > 30) {
            notify.show('Option length should not be greater than 30 ', "error", 5000);
        }
        else {
            tmpArray[idx][name] = value
            this.setState({ question: tmpArray }, () => {
                // LS.set('queList',tmpArray)
                this.validateQueList()
            })
        }
    }

    handleStatsChecked = (value, idx) => {
        let QList = this.state.question
        QList[idx]['is_stats'] = value == 1 ? 0 : 1
        if (QList[idx]['is_stats'] == 0) {
            QList[idx]['option_stats'] = {}
        }
        this.setState({
            question: QList
        }, () => {
            this.validateQueList()
        })
    }
    toggleQueInfo = () => {
        this.setState({ showQueInfo: !this.state.showQueInfo })
    }

     handleAutomatchAddition = (e) => {

        let { checkedMatch } = this.state
      
        this.setState({
            checkedMatch: !this.state.checkedMatch
        })
    
    }
   

 addRow = (e) => {

    const {rows} = this.state

     let temparray = rows.filter(rows => rows.question)     
     let tempdate = rows.filter(rows => rows.ExpireDate) 
     
   

     if(rows.length != temparray.length ){

       
        notify.show('Question field is required ', "error", 5000);
        return false;
     }

    if(rows.length != tempdate.length ){        
        notify.show('Date field is required ', "error", 5000);
        return false;
     }

    let temp = rows
    
    temp.push({
            question:'', ExpireDate:""
         })
       
        this.setState({rows:temp});     
    
    };

   handleInputChange = (idx,e) => {
      let temp = this.state.rows;
      let name = e.target.name
      let value = e.target.value

      temp[idx]['question'] = value
      temp[idx]['isChanged'] = true  

    //  let temp = this.state.rows;

    //  temp[idx]['value'] = value
this.setState({
         [name]: value,
     
        rows: temp
      },()=>{console.log(this.state.rows);})
      let btnAction = false
      if (value.length < 3 || value.length > 400)
         btnAction = true

     
   }
   submit = (e) => {
    // let currentDate = HF.getFormatedDateTime(Date.now());

    let currentDate = WSManager.getLocalToUtcFormat(Date.now(), 'YYYY-MM-DD HH:mm:ss');
    
       const {rows} = this.state

       let temparray = rows.filter(rows => rows.question)     
       let tempdate = rows.filter(rows => rows.ExpireDate) 
       
       
       let GreaterDate =  rows.filter(rows => rows.expire <  currentDate)
       if(GreaterDate.length > 0 ){
           notify.show('Expired date should be future date', "error", 5000);        
           return false;
        }
    
        

        if(rows.length != temparray.length ){
         notify.show('Question field is required ', "error", 5000);
         
         return false;
        }
        
        if(rows.length != tempdate.length ){
            notify.show('Date field is required', "error", 5000);
            
            return false;
        }
        
        this.setState({ savePosting : true })

     let params = {
        "season_id": this.state.SeasonDetails.season_id,      
        "sports_id": this.state.SeasonDetails.sports_id,      
        "data": rows
      
      };     
      
    //   console.log(params); return false;
       

       WSManager.Rest(NC.baseURL + NC.OT_ADD_QUESTION, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                 notify.show(Response.message, "success", 3000)
                 setTimeout(() => {
                    this.props.history.push({ pathname: '/opinionTrading/publish_match/' + this.state.SeasonDetails.league_id + '/' + this.state.SeasonDetails.season_id + '/' + this.props.match.params.tab})
                 
                }, 1000)
                 this.setState({ savePosting : true })
                
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
          
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })

   }
   removeRow =(index)=>{      
        const {rows} = this.state
        let temp = rows 
        delete temp[index]['isChanged'];         
        // console.log(rows.length,'nilesh rows length');
        if(rows.length < 2){
            alert('one field is required');
            return false;
        }   
        this.setState({rows:temp});
        this.newRow();
       
   }
   newRow =()=>{   
     let tempRowArray = this.state.rows.filter(obj => obj.isChanged)
      this.setState({rows:tempRowArray});
   }

    handleDateFilter(date, datetype ,idx) { 
        // alert();return 
         let currentDate = HF.getFormatedDateTime(Date.now()); 
         let new_date = HF.getFormatedDateTime(date); 

        //  console.log(currentDate,'currentDate');

      

        let temp = this.state.rows;
        let name = datetype
        // let value = e.target.value

        

        // temp[idx]['ExpireDate'] = moment(date).format("YYYY-MM-DD HH:mm:ss")
        temp[idx]['ExpireDate'] =  new Date(moment(date).format('YYYY-MM-DD HH:mm:ss'))
        temp[idx]['isChanged'] = true     
        // temp[idx]['expire'] = moment(date).format('YYYY-MM-DD HH:mm:ss')  
        temp[idx]['expire'] = WSManager.getLocalToUtcFormat(date, 'YYYY-MM-DD HH:mm:ss')    


        //   console.log(new_date,'new_date')
        // if (new_date < currentDate) {
        //     alert('please select  greater than current date');
        //     return false;            
        // }
        
        // console.log(temp, 'temp');

        this.setState({
         [name]: date,     
         rows: temp
      },()=>{})
        
       

      this.setState({
         // Validedate: this.validateDate(date)
         StartDate: date,
         CURRENT_PAGE: 1
      }, () => {
       
      })
    }

    render() {
        const {SeasonDetails,rows,EndDate, StartDate,home_flag, away_flag, match, scheduled_date, league_name, question, questionCount, queFilled, draftQuestion, tieBreakerShow, tieBreakerQue, showQueInfo, checkSDPosting, checkPMPosting } = this.state
       
        return (
            <>  
             <Row className='mt-30'>
             <Col className="heading-box"> 
             <div className="fixture-head-add-contest">            
                <label className="backtofixtures" > Add Question(s)</label>           
          
            </div>    

          <div className="fixture-back-contest">            
            <label className="backtofixtures" onClick={() =>  this.props.history.push({ pathname: '/opinionTrading/publish_match/' + this.state.SeasonDetails.league_id + '/' + this.state.SeasonDetails.season_id + '/' +this.props.match.params.tab})}> {'<'} Back to Fixtures</label>           
          
          </div>
        </Col>
        </Row>
            <Row className='question-section mt-30 '>    

             <Col md={12}>
                                <div className="season-data-container season-trade-section">
                                    {/* {NC.S3 + NC.FLAG + Roster_Data.home_flag} */}
                                    <img className="flags-logo" src={NC.S3 + NC.FLAG + SeasonDetails.home_flag} alt="" />
                                    <div className="season-data">
                                        <div className="fixture-details">
                                            {SeasonDetails.home} 
                                            {' vs '}
                                            
                                             {SeasonDetails.away}
                                        </div>
                                        <div className="season-duration">

                                            {/* <MomentDateComponent data={{ date: SeasonDetails.scheduled_date, format: "D MMM - hh:mm a" }} />(IST) */}
                    {HF.getFormatedDateTime(SeasonDetails.scheduled_date, "D MMM - hh:mm a")}
                                        
                                        </div>
                                        {/* <div class="com-fixture-title">ECS Rome T10</div> */}
                                        <div className="season-duration">
                                            {/* title */}
                                            {SeasonDetails.league_name}
                                        </div>
                                    </div>
                                    
                                    <img className="flags-logo" src={NC.S3 + NC.FLAG + SeasonDetails.away_flag} alt="" />
                                </div>
                            </Col>
                <Col sm={12}>

                {
                    // <div className="fixed-set-prize show-grid mt-10">
                    //     <div className="pt-setp-stype">
                    //         <div className="set-prizes-title title-trade-text">Automatic Match Addition                            

                    //         </div>
                    //         <div className="select-prize-op mb-0">
                    //             <div className="common-cus-checkbox">
                    //                 <label className="com-chekbox-container">
                    //                     <span className="opt-text">Yes</span>
                    //                     <input
                                           
                    //                         type="checkbox"
                    //                         name="auto_match_publish"
                    //                         checked={this.state.checkedMatch}
                                        
                    //                         onChange={(e) => this.handleAutomatchAddition()}
                    //                     />
                    //                     <span className="com-chekbox-checkmark"></span>
                    //                 </label>
                    //             </div>
                    //         </div>
                    //     </div>
                 
                    // </div>
                    
                }

                <div className="fixed-set-prize show-grid mt-10">
                    <div className="pt-setp-stype">                    
                        <div className="set-prizes-title title-trade-text">Add Question(s) </div>                        
                    </div>  

                     {rows.map((row, index) => (  
                        <>                   
                        <Row id = {index} className={'question_list_'+index}>                            
                            <Col sm={7}>
                            <Input
                                required= "required"
                                spellcheck="false"
                                minLength="3"
                                maxLength="400"
                                className ={'cancel_reason backgrnd '}
                                rows={3}
                                type="textarea"
                                name={'question_filed_'+index}
                                value = {row.question}
                                // placeholder="Write a reason to disable...."
                                onChange={(e) => this.handleInputChange(index,e)}
                            />
                            </Col>
                            <Col sm={4}>                        
                                <div className='qustion-cs'>
                                     <label className="filter-label" htmlFor="CandleDetails">Expires On</label>                                                                                 
                                             <DatePicker
                                                minDate={new Date()}
                                                className="Select-control inPut icon-calender_ex backgrnd"
                                                showYearDropdown='true'
                                                name= {'question_date_'+index}
                                                selected={row.ExpireDate}
                                                // onChange={this.handleChange}
                                                // onChange={(e) => this.handleChange(e)}
                                                onChange={e => this.handleDateFilter(e, 'question_date_'+index,index)}
                                                placeholderText="Expires on"
                                                
                                                showTimeSelect="true"
                                                timeFormat="HH:mm"
                                                timeIntervals="5"
                                                timeCaption="time"
                                                dateFormat='dd/MM/yyyy h:mm aa'
                                             />
                                          <i className='icon-calender Ccalenderexpire '></i>                                      
                                </div>
                            </Col>
                            <Col sm={1}> 
                            {
                                rows.length > 1 &&
                            <i className="cancel-container-trade icon-cross icon-style" onClick={() => this.removeRow(index )}></i>
                            }
                            </Col>
                            
                        </Row>
                        </>  
                        ))}  
                        <Row>
                            <Col sm = {12}>
                                {
                                //    rows.length > 1 && <div className="cancel-container-trade">
                                //         <span className="dis-each remove-row">Remove Row</span>

                                //         {
                                //           <i className="icon-cross icon-style" onClick={() => this.removeRow( )}></i>
                                //         }

                                //     </div>
                                }
                            </Col>
                        </Row>                  
                
            </div>
                   <div className="add-btn-footer">
                            <div className="pt-btn-wdt-trade">
                                <div
                                    className="add-question-btn"
                                    onClick={() => this.addRow()}>
                                    Add Question
                                    <i className="icon-plus icon-styles ml-2"></i>
                                </div>
                            </div>
                            {/* <div className="pt-total-dis">
                                <div className="pt-tot-dtl pt-tot-sty">{HF.getCurrencyCode()}{TotalRealDistri}</div>
                                <div className="pt-total-prz">
                                    <div className="pt-tot-dtl">Total Prize</div>
                                    <div className="pt-tot-dtl pt-real">(only real cash)</div>
                                </div>
                            </div> */}
                        </div>
                        </Col>

              
                    <Col sm={12}>
                        <div className='QusBtn mb-30'>
                            {/* this.state.checkedTie ? (this.state.endRange != '' && this.state.startRange != '' && this.state.tieBreakerQue != '' */}
                           
                            <Button  disabled={this.state.savePosting} onClick={() => this.submit()} className="btn-secondary-outline btn btn-secondary  ml-3">Submit</Button>
                        </div>
                    </Col>
                </Row>


            </>
        )
    }
}
