import React, { Component, Fragment } from 'react';
import { Card, Col, Row, Modal, ModalBody, ModalHeader, ModalFooter, Input, Button, Tooltip } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { Progress } from 'reactstrap';
import { notify } from 'react-notify-toast';
import LS from 'local-storage';
import Images from '../../components/images';
import Slider from "react-slick";
import HF, { _remove, _Map, _debounce, _isEmpty, _isUndefined, _isNull, _cloneDeep } from '../../helper/HelperFunction';
import { MomentDateComponent } from "../../components/CustomComponent";
import { SCRATCH_WIN, SECOND_INNING } from '../../helper/Message';
import queryString from 'query-string';
import Loader from '../../components/Loader';
class PFCreatetemplatecontest extends Component {
  constructor(props) {
    super(props);
    this.state = {
      // selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
      league_id: '',
      season_game_uid: (this.props.match.params.season_game_uid) ? this.props.match.params.season_game_uid : '',
      collection_id: '',
      scheduled_date: '',
      fixtureDetail:[],
      templateList: [],
      posting: false,
      prize_modal: false,
      templateObj: {},
      selectedTemplate: [],
      TotalTemplates: 0,
      BackTab: (this.props.match.params.tab) ? this.props.match.params.tab : 2,
      FromFixture: this.props.match.params.fromfixture,
      IsH2h: 0,
      saveLoad: false,
      contestUserData: {},
      collection_name: '',

      league_id:'',
      season_id:'',
      sports_id:'',
      season_game_uid:'',
      away_flag:'', 
      away_id: '', 
      home_id: '', 
      home_flag: '',
      league_name:'',
      match: '',
      scheduled_date: '',
      modified_date: '',
      scheduled_date: '',
      modified_date: '',
     
      //collection_id: (this.props.match.params.collection_id) ? this.props.match.params.collection_id : '',
    };
  }

  componentDidMount() {
    this.getLocaltionProps();
   
  }
  getLocaltionProps=()=>{
      let matchDetails = JSON.parse(localStorage.getItem("matchDetails"));
      this.setState({
        league_id: matchDetails[0].league_id,
        season_id: matchDetails[0].season_id,
        sports_id: matchDetails[0].sports_id,
        season_game_uid: matchDetails[0].season_game_uid,
        away_flag: matchDetails[0].away_flag, 
        away_id: matchDetails[0].away_id, 
        home_id: matchDetails[0].home_id, 
        home_flag: matchDetails[0].home_flag,
        league_name: matchDetails[0].league_name,
        match: matchDetails[0].match,
        scheduled_date: matchDetails[0].scheduled_date,
        modified_date: matchDetails[0].modified_date,
      },()=>{
         console.log('league_id', this.state.league_id) 
        this.GetFixtureTemplates()})
  }

//   getContestStatus = (collectionId) =>{
//     let params ={
//       'collection_id': collectionId
//     };
//     console.log(params)
//     WSManager.Rest(NC.baseURL + NC.PF_GET_CONTEST_STATUS, params).then((responseJson) => {
//       if (responseJson.response_code === NC.successCode) {
//       this.setState({
//         contestUserData: responseJson.data 
//       }, ()=>{
//         console.log('contestUserData', this.state.contestUserData)
//       })
//     }
//   })
// }

  // redirectToCreateContest = () => {
  //   this.props.history.push({ pathname: '/livefantasy/createcontest/' + this.state.league_id + '/' + this.state.season_game_uid })    
  // }

  getPrizeAmount = (prize_data) => {
    let prize_text = "Prizes";
    let is_tie_breaker = 0;
    let prizeAmount = { 'real': 0, 'bonus': 0, 'point': 0 };
    prize_data.map(function (lObj, lKey) {
      var amount = 0;
      if (lObj.max_value) {
        amount = parseFloat(lObj.max_value);
      } else {
        amount = parseFloat(lObj.amount);
      }
      if (lObj.prize_type == 3) {
        is_tie_breaker = 1;
      }
      if (lObj.prize_type == 0) {
        prizeAmount['bonus'] = parseFloat(prizeAmount['bonus']) + amount;
      } else if (lObj.prize_type == 2) {
        prizeAmount['point'] = parseFloat(prizeAmount['point']) + amount;
      } else {
        prizeAmount['real'] = parseFloat(prizeAmount['real']) + amount;
      }
    });
    if (is_tie_breaker == 0 && prizeAmount.real > 0) {
      prize_text = HF.getCurrencyCode() + parseFloat(prizeAmount.real).toFixed(2);
    } else if (is_tie_breaker == 0 && prizeAmount.bonus > 0) {
      prize_text = '<i class="icon-bonus"></i>' + parseFloat(prizeAmount.bonus).toFixed(2);
    } else if (is_tie_breaker == 0 && prizeAmount.point > 0) {
      prize_text = '<img src="' + Images.COINIMG + '" alt="coin-img" />' + parseFloat(prizeAmount.point).toFixed(2);
    }
    return { __html: prize_text };

  }

//   GetFixtureDetail = (collectionId) => {
//     let params = {
//       "collection_id": collectionId
//     }
//     // this.setState({ posting: true });
//     WSManager.Rest(NC.baseURL + NC.PF_GET_COLLECTION_DETAIL, params).then((responseJson) => {
//       if (responseJson.response_code === NC.successCode) {
//         console.log('responseJson.data', responseJson.data)
//         let scheduled_date = responseJson.data.scheduled_date;
//         let league_id =  responseJson.data.league_id;
//         let match_list = responseJson.data.match_list;
        

//         let tmpArry= [];
//         _.map(match_list, (item, index) => {
//           tmpArry.push(item.season_id);
//         })

//         let collection_name = responseJson.data.name;
//         this.setState({
//           fixtureDetail: match_list,
//           league_id: league_id,
//           scheduled_date: scheduled_date,
//           selected_season: tmpArry,
//           collection_name: collection_name,
//         }, ()=> this.GetFixtureTemplates())
//       } 
//     })
//   }

  GetFixtureTemplates = () => {
    this.setState({ posting: true })
    let params = {
      'sports_id': this.state.sports_id ? this.state.sports_id :'1', 
      'league_id': this.state.league_id, 
      'season_id': this.state.season_id, 
      
    }
  
   
    WSManager.Rest(NC.baseURL + NC.PF_GET_FIXTURE_TEMPLATE, params).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        responseJson = responseJson.data;
        var totTemp = 0
        responseJson.map((template) => {
          totTemp += parseInt(template.template_list.length)
        })
        this.setState({
          templateList: responseJson,
          TotalTemplates: totTemp
        })
      }
      this.setState({ posting: false })
    })
  }

  getWinnerCount(TemplateItem) {
    if (TemplateItem.prize_distibution_detail.length > 0) {
      if ((TemplateItem.prize_distibution_detail[TemplateItem.prize_distibution_detail.length - 1].max) > 1) {
        return TemplateItem.prize_distibution_detail[TemplateItem.prize_distibution_detail.length - 1].max + " Winners"
      } else {
        return TemplateItem.prize_distibution_detail[TemplateItem.prize_distibution_detail.length - 1].max + " Winner"
      }
    }
  }

  viewWinners = (e, templateObj) => {
    e.stopPropagation();
    this.setState({ 'prize_modal': true, 'templateObj': templateObj });
  }

  closePrizeModel = () => {
    this.setState({ 'prize_modal': false, 'templateObj': {} });
  }

  selectTemplate = (templateObj) => {
    let selectedTemplate = _.cloneDeep(this.state.selectedTemplate);
    if (selectedTemplate.indexOf(templateObj.contest_template_id) == -1) {
      selectedTemplate.push(templateObj.contest_template_id);
    } else {
      var item_index = selectedTemplate.indexOf(templateObj.contest_template_id);
      selectedTemplate.splice(item_index, 1);
      this.setState({ SelectAllTemp: false })
    }
    this.setState({ selectedTemplate: selectedTemplate }, () => {
      if (this.state.TotalTemplates == this.state.selectedTemplate.length) {
        this.setState({ SelectAllTemp: true })
      }
    });
  }

  selectAllTemplate = () => {
    if (this.state.SelectAllTemp == true) {
      this.setState({ selectedTemplate: [], SelectAllTemp: false });
      return false;
    }
    let { templateList, fixtureDetail } = this.state
    let selectedTemplate = _.cloneDeep(this.state.selectedTemplate);
    templateList.map((template) => (
      _.map(template.template_list, (templist) => {
        if (selectedTemplate.indexOf(templist.contest_template_id) == -1) {
          /* Second inning check*/
          // if ((fixtureDetail.format == 1 || fixtureDetail.format == 3) || ((fixtureDetail.format != 1 || fixtureDetail.format != 3) && templist.is_2nd_inning != '1')){
          selectedTemplate.push(templist.contest_template_id);
          this.setState({ SelectAllTemp: true })
          // }
        }
      })
    ))
    this.setState({ selectedTemplate: selectedTemplate });
  }

//   getCollectionDetail = () => {
//     let { league_id, collection_id } = this.state
//     if(collection_id!= '' && collection_id!= undefined && collection_id !=0){
//       let param = {
//         "collection_id": collection_id,
//         "league_id": league_id,      
//       }
//       WSManager.Rest(NC.baseURL + NC.PF_GET_COLLECTION_DETAIL, param).then((responseJson) => {
//         if (responseJson.response_code === NC.successCode) {
//           this.setState({ collectionData: responseJson.data });
//         }
//       }).catch((error) => {
//         notify.show(NC.SYSTEM_ERROR, "error", 5000);
//       })
//     }
  
//   }

  createTemplateContest = () => {
    let { IsH2h, selected_sport, collection_id, BackTab } = this.state
    if (this.state.selectedTemplate.length <= 0) {
      notify.show("Please select atleast one template.", "error", 3000);
      return false;
    }

    if (window.confirm("Are you sure want to create contest of selected template ?")) {
      this.setState({ saveLoad: true })
      let params = {
        "season_id": this.state.season_id,
        "sports_id": this.state.sports_id ? this.state.sports_id : '1',
        "league_id": this.state.league_id,
        "selected_templates": this.state.selectedTemplate,
      };
      console.log('params Contest', params)
      WSManager.Rest(NC.baseURL + NC.PF_CREATE_CONTEST,params).then((responseJson) => {
        if (responseJson.response_code === NC.successCode) {
             this.props.history.push({ 
              pathname: '/picksfantasy/contest-list/'+this.state.league_id+ '/'+this.state.season_id , 
              state: {activeTab: '1'}
            })
        } else {
          notify.show(responseJson.message, "error", 3000);
        }
        this.setState({ saveLoad: false })
      })
    } else {
      return false;
    }
  }

  SWinToggle = (g_idx, idx, name) => {
    if (!_isUndefined(g_idx) && !_isUndefined(idx)) {
      let templateList = this.state.templateList
      let flag = templateList[g_idx]['template_list'][idx][name]
      templateList[g_idx]['template_list'][idx][name] = !flag
      this.setState({ templateList });
    }
  }

  goToNextScreen = () =>{
    this.props.history.push({ pathname: '/propsfantasy/createcontest/' + this.state.league_id + '/' + this.state.season_id })
  }

  render() {
    let {
      collection_name,
      SelectAllTemp,
      fixtureDetail,
      templateList,
      templateObj,
      selectedTemplate,
      BackTab,
      selected_sport,
      IsH2h,
      saveLoad, contestUserData, league_name, scheduled_date, match, home_flag, away_flag
    } = this.state
    const settings = {
      dots: false,
      infinite: false,
      speed: 500,
      slidesToShow: 4,
      slidesToScroll: 1,
      arrows: false
    };

    return (
      <div className="animated fadeIn fk-create-temp-contest fk-fixture-contest-main1" style={{marginTop: '0px'}}>
        <Row>
            <Col sm={12}>
                <div className='SelectContestSr'>
                  <div className='singleFixture'>
                      <div>
                          <img className="matchLogo" src={NC.S3 + NC.FLAG + home_flag}></img>
                      </div>
                      <div className='matchDetails'>
                          <span className='fixture-name'>{match ? match : 'TBA VS TBA'}</span>
                          <span className='fixture-time'>{scheduled_date && 
                          // WSManager.getUtcToLocalFormat(scheduled_date, 'D-MMM-YYYY hh:mm A')
                          HF.getFormatedDateTime(scheduled_date, 'D-MMM-YYYY hh:mm A')
                          }</span>                        
                          <span className='fixture-title'>{league_name && league_name}</span>
                      </div>
                      <div>
                          <img className="matchLogo" src={NC.S3 + NC.FLAG + away_flag}></img>
                      </div>
                  </div>
                  <div className='contestBtn-right'>
                      <Button className='btn-secondary-outline' onClick={() => {
                        this.props.history.push({
                          pathname: '/picksfantasy/create-form-contest/'+this.state.league_id +'/'+ this.state.season_id,
                        })
                      }}>Create Contest</Button>
                  </div>
                </div>  
            </Col>
        </Row>
        <Row>
            <Col sm={12}>
                <div className='heading-flex'>
                  <span className='set-picks'>Picks Fantasy</span>
                  <span className='back-to-fixture' onClick={() => {
                        this.props.history.push({
                          pathname: '/picksfantasy/fixture'
                        })
                      }}>Back to fixture</span>
                </div>
            </Col>
        </Row>
        <Row>
          {
          _Map(this.state.fixtureDetail, (item, idx) => {
              return (
                <Col md={4} sm={4} key={idx}>
                    <div className="dfst common-fixture">
                      <div className="bg-card">
                        {   
                          item.is_selected && 
                            <div className="right-selection" onClick={() => {this.state.selFixCount > 1 && this.FixtureListRemoved(item.season_id)}}>
                              <img src={Images.tick} className="rght-img" />
                            </div>
                        }
                        <div>
                            <img className="com-fixture-flag float-left" src={item.home_logo ? NC.S3 + NC.FLAG + item.home_logo : Images.no_image} />
                            <img className="com-fixture-flag float-right" src={item.away_logo ? NC.S3 + NC.FLAG + item.away_logo : Images.no_image} />                                                        <div className="com-fixture-container">
                                <div className="com-fixture-name">{(item.home) ? item.home : 'TBA'} VS {(item.away) ? item.away : 'TBA'}</div>
                                <div className="com-fixture-title">
                                    {
                                        // <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D-MMM-YYYY hh:mm A" }} />
                                        <>
                                          {HF.getFormatedDateTime(item.season_scheduled_date, "D-MMM-YYYY hh:mm A")}
                                        </>
                                    }
                                </div>
                            </div>
                        </div>
                      </div>
                    </div>
                </Col>
              )
            })
          }
        </Row>
        <Row className="dfsrow" style={{marginTop: '0px'}}>
          <Col lg={12} className='contest-header'>
            <h3 className="h2-cls">{collection_name && collection_name}</h3>
          </Col>  
        </Row>
        <hr />
        <Row className="mt-3">
          <Col lg={6}>
            <div className="contest-tempalte-wrapper">
              <h3 className="h3-cls">Contest Template</h3>
              <p className="info-text">Create contests from predefined templates</p>
            </div>
          </Col>
          <Col lg={6}></Col>
        </Row>

        <Row>
          <Col md={12}>
          <div className="fixture-view-header">
            <div className="select-all-template">
              
            <label className="select-all-checkbox">
              <Input
              className='dfs-select-all'
                type="checkbox"
                name="SelectAllTemp"
                checked={SelectAllTemp}
                onClick={() => this.selectAllTemplate()}
              />
              <span className='ml5'>Select All</span>
            </label>
            </div>
            </div>
          </Col>
        </Row>

        <div className="contest-group-container dfs-template">
          {templateList.map((item, group_index) => (
            <Row key={group_index}>
              <Col xs="12" sm="12" md="12">
                <h4>{item.group_name}</h4>
              </Col>
              {item.template_list.map((template, idx) => (
                // ((fixtureDetail.format == 1 || fixtureDetail.format == 3) || ((fixtureDetail.format != 1 || fixtureDetail.format != 3) && template.is_2nd_inning != '1')) &&
                <Col md="4" key={idx}>
                  <div className="contest-group" onClick={() => this.selectTemplate(template, idx)}>
                    <div className="contest-list-wrapper">
                      <div className={selectedTemplate.indexOf(template.contest_template_id) != -1 ? "contest-card more-contest-card contest-selected" : "contest-card more-contest-card"}>
                        <div className="contest-list contest-card-body">
                          <div className="contest-list-header">
                            <div className="contest-heading">
                              <div className="contest-name">{template.template_name}</div>
                              <div className="clearfix">
                                <ul className="ul-action con-action-list">
                                  {
                                    template.guaranteed_prize == '2' &&
                                    <li className="action-item">
                                      <i className="icon-icon-g contest-type"></i>
                                    </li>
                                  }
                                  {
                                    template.multiple_lineup > 1 &&
                                    <li className="action-item">
                                      <i className="icon-icon-m contest-type"></i>
                                    </li>
                                  }
                                  {
                                    template.is_auto_recurring == "1" &&
                                    <li className="action-item">
                                      <i className="icon-icon-r contest-type"></i>
                                    </li>
                                  }
                                </ul>
                              </div>
                              <h3 className="win-type">
                                {
                                  (!_.isUndefined(template.template_title) && !_.isEmpty(template.template_title) && !_.isNull(template.template_title)) ?
                                    <span className="prize-pool-value">{template.template_title}</span>
                                    :
                                    <span>
                                      <span className="prize-pool-text">WIN </span>
                                      <span className="prize-pool-value" dangerouslySetInnerHTML={this.getPrizeAmount(template.prize_distibution_detail)}>
                                      </span>
                                    </span>
                                }
                              </h3>
                              <div className="text-small-italic">
                                <span onClick={(e) => this.viewWinners(e, template)}>{this.getWinnerCount(template)}</span>
                                <span className="b-allow">{template.max_bonus_allowed ? template.max_bonus_allowed : '0'}% Bonus allowed</span>
                              </div>
                            </div>
                            <div className="display-table clearfix">
                              <div className="progress-bar-default display-table-cell v-mid">
                                <div className="danger-area progress">
                                  <div className="text-center"></div>
                                  <Progress />
                                </div>
                                <div className="progress-bar-value"><span className="user-joined">0</span><span className="total-entries"> / {template.size} Entries</span><span className="min-entries">min {template.minimum_size}</span></div>
                              </div>
                              <div className="display-table-cell v-mid entry-criteria">
                                <button type="button" className="white-base btnStyle btn-rounded btn btn-primary">
                                  {
                                    template.currency_type == '0' && template.entry_fee > 0 &&
                                    <span><i className="icon-bonus"></i>{template.entry_fee}</span>
                                  }
                                  {
                                    template.currency_type == '1' && template.entry_fee > 0 &&
                                    <span>{HF.getCurrencyCode()}{template.entry_fee} </span>
                                  }
                                  {
                                    template.currency_type == '2' && template.entry_fee > 0 &&
                                    <span><img src={Images.COINIMG} alt="coin-img" />{template.entry_fee}</span>
                                  }
                                  {/* currency_type == '3' */}
                                  {
                                    template.currency_type == '3' && template.entry_fee > 0 &&
                                    <span><img src={Images.COINIMG} alt="coin-img" />{template.entry_fee}</span>
                                  }
                                  {template.entry_fee == 0 &&

                                    <span>Free</span>

                                  }
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>
                        {
                          template.sponsor_logo &&
                          <div className="league-listing-wrapper details-para">
                            <div className="league-listing-container">
                              <div className="spr-card-img">
                                {
                                  (template.sponsor_logo && template.sponsor_link) &&
                                  <a target="_blank" href={template.sponsor_link}>
                                    <img src={NC.S3 + NC.SPONSER_IMG_PATH + template.sponsor_logo} alt="" />
                                  </a>
                                }
                                {
                                  (template.sponsor_logo && template.sponsor_link == null) &&
                                  <img src={NC.S3 + NC.SPONSER_IMG_PATH + template.sponsor_logo} alt="" />
                                }
                              </div>
                            </div>
                          </div>
                        }
                        {/* <div className="league-listing-wrapper details-para">
                          <div className="league-listing-container">
                            <div className="contest-details-para">
                              <p>{template.template_description}</p>
                            </div>
                          </div>
                        </div> */}
                      </div>
                    </div>
                  </div>
                </Col>
              ))}
            </Row>
          ))}
        </div>

        <div className="createtempcontest">
          <Row>
            <Col lg={12}>
              <div className="bottom-container">
                <p>Once created, filled contests cannot be edited</p>
                {
                  saveLoad ?
                    <Loader />
                    :
                    <Button
                      disabled={selectedTemplate.length <= 0}
                      onClick={() => this.createTemplateContest()} className="btn-secondary-outline">
                      Create {selectedTemplate.length} Contest
                </Button>
                }
              </div>
            </Col>
          </Row>
        </div>



        <div className="winners-modal-container">
          <Modal isOpen={this.state.prize_modal} toggle={() => this.closePrizeModel()} className="winning-modal">
            <ModalHeader toggle={this.toggle}>Winnings Distribution</ModalHeader>
            <ModalBody>
              <div className="distribution-container">
                {
                  templateObj.prize_distibution_detail &&
                  <table>
                    <tbody>
                      <tr>
                        <th>Rank</th>
                        <th style={{ width: "100px", textAlign: "center" }}>Min</th>
                        <th style={{ width: "100px", textAlign: "center" }}>Max</th>
                      </tr>
                      {templateObj.prize_distibution_detail.map((prize, idx) => (
                        <tr>
                          <td className="text-left">
                            {prize.min}
                            {
                              prize.min != prize.max &&
                              <span>-{prize.max}</span>
                            }
                          </td>
                          <td className="text-center">
                            {
                              prize.prize_type == '0' &&
                              <i className="icon-bonus"></i>
                            }
                            {
                              (!prize.prize_type || prize.prize_type == '1') &&
                              HF.getCurrencyCode()
                            }
                            {
                              prize.prize_type == '2' &&
                              <img src={Images.COINIMG} alt="coin-img" />
                            }
                            {
                              prize.prize_type == '3' &&
                              prize.min_value
                            }
                            {
                              prize.prize_type != '3' &&
                              parseFloat(prize.min_value).toFixed(2)
                            }
                          </td>
                          <td className="text-center">
                            {
                              prize.prize_type == '0' &&
                              <i className="icon-bonus"></i>
                            }
                            {
                              (!prize.prize_type || prize.prize_type == '1') &&
                              HF.getCurrencyCode()
                            }
                            {
                              prize.prize_type == '2' &&
                              <img src={Images.COINIMG} alt="coin-img" />
                            }
                            {
                              prize.prize_type == '3' &&
                              prize.max_value
                            }
                            {
                              prize.prize_type != '3' &&
                              parseFloat(prize.max_value).toFixed(2)
                            }
                          </td>
                        </tr>

                      ))}
                    </tbody>
                  </table>
                }
              </div>
            </ModalBody>
            <ModalFooter>
              <Button className="close-btn" color="secondary" onClick={() => this.closePrizeModel()}>Close</Button>
            </ModalFooter>
          </Modal>
        </div>
      </div>
    );
  }
}

export default PFCreatetemplatecontest;