import React, { Component, Fragment } from 'react';
import { Card, Col, Row, Modal, ModalBody, ModalHeader, ModalFooter, Input, Button, Tooltip } from 'reactstrap';
import _, { isUndefined } from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { Progress } from 'reactstrap';
import { notify } from 'react-notify-toast';
import LS from 'local-storage';
import Images from '../../components/images';
import Slider from "react-slick";
import HF, { _isUndefined, _isEmpty } from '../../helper/HelperFunction';
import { MomentDateComponent } from "../../components/CustomComponent";
import { SCRATCH_WIN, SECOND_INNING } from '../../helper/Message';
import queryString from 'query-string';
import Loader from '../../components/Loader';
class Createtemplatecontest extends Component {
  constructor(props) {
    super(props);
    this.state = {
      selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
      league_id: (this.props.league_id) ? this.props.league_id : this.props.match.params.league_id,
      season_id: (this.props.match.params.season_id) ? this.props.match.params.season_id : '',
      collection_master_id: (this.props.match.params.collection_master_id) ? this.props.match.params.collection_master_id : '',
      fixtureDetail: {},
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
      isDFS: this.props && this.props.location && this.props.location.state && this.props.location.state.ismultigame ? false : true,
      // isDFS: (this.props.location.state && this.props.location.state.isDFS) ? this.props.location.state.isDFS : false,
    };
  }

  componentDidMount() {
    console.log('this.props',this.props)
    // if(isUndefined(this.props.location.state.ismultigame) || (this.props && this.props.location && this.props.location.state && this.props.location.state.ismultigame == false)){
    //   LS.set('isMGEnable', 0)
    //   this.checkIsH2H()
    // }
    // else{
    //   LS.set('isMGEnable', 1)
    // }
    console.log('LS.getisMGEnable',LS.get('isMGEnable'))
    if(LS.get('isMGEnable') == 1 || (this.props && this.props.location && this.props.location.state && this.props.location.state.ismultigame && this.props.location.state.ismultigame == true)){
      LS.set('isMGEnable', 1)
      this.setState({
        isDFS: false
      })
    }
    else{
      LS.set('isMGEnable', 0)
      this.checkIsH2H()
      this.setState({
        isDFS: true
      })
    }
    this.GetFixtureDetail();
  }
  
  checkIsH2H = () => {
    let st = this.props.location.state
    let h2hdata = st ? st.h2h_template : {}
    if (h2hdata == 1 || h2hdata == 0) {
      this.setState({ IsH2h: h2hdata })
    }
    // else{
    //   notify.show('Please select fixture', 'error', 5000)
    //   this.props.history.push('/game_center/DFS?tab=2')
    // }
  }

  redirectToCreateContest = () => {
    console.log('this.state.isDFS',this.state.isDFS)
    if (!this.state.isDFS) {
      this.props.history.push({ pathname: '/contest/createcollectioncontest/' + this.state.collection_master_id })
    } else {
      this.props.history.push({ pathname: '/contest/createcontest/' + this.state.collection_master_id + '/' + this.state.season_id })
    }
  }
  handleJsonData=(data)=>{
    try{
      return JSON.parse(data)
    }
    catch{
      return data
    }
  }
  getPrizeAmount = (pdata) => {
    let prize_data = this.handleJsonData(pdata)
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

  GetFixtureDetail = () => {
    let param = {
      // "league_id": this.state.league_id,
      // "sports_id": this.state.selected_sport,
      // "season_id": this.state.season_id,
      "collection_master_id": this.state.collection_master_id,
    }
    this.setState({ posting: true });
console.log(' this.state.season_game_uid=========', this.state.isDFS)
// WSManager.Rest((this.state.isDFS) ? NC.baseURL + NC.DFS_GET_FIXTURE_DETAILS : NC.baseURL + NC.GET_COLLECTION_SEASON_DETAILS, param).then((responseJson) => {
    WSManager.Rest(NC.baseURL + NC.DFS_GET_FIXTURE_DETAILS, param).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        let fixtureDetail = responseJson.data;
        this.setState({ posting: false, fixtureDetail: fixtureDetail }, function () {
          this.GetFixtureTemplates();
        });
      }
      else if (responseJson.response_code == NC.sessionExpireCode) {
        WSManager.logout();
        this.props.history.push('/login');
      }
    })
  }

  GetFixtureTemplates = () => {
    this.setState({ posting: true })
    let params = {
      "collection_master_id": this.state.collection_master_id,
      "sports_id": this.state.selected_sport,
      // 'league_id': this.state.league_id,
      "season_id": this.state.season_id,
      "is_h2h": this.state.IsH2h,
    };
    // {"sports_id":"7","collection_master_id":"625","is_h2h":"0"}
    // WSManager.Rest((this.state.isDFS) ? NC.baseURL + NC.GET_FIXTURE_TEMPLATE : NC.baseURL + NC.GET_COLLECTION_FIXTURE_TEMPLATE, params).then((responseJson) => {
    WSManager.Rest(NC.baseURL + NC.GET_FIXTURE_TEMPLATE , params).then((responseJson) => {
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
    let PrizeDisDetail = this.handleJsonData(TemplateItem.prize_distibution_detail)
    if (PrizeDisDetail.length > 0) {
      if ((PrizeDisDetail[PrizeDisDetail.length - 1].max) > 1) {
        return PrizeDisDetail[PrizeDisDetail.length - 1].max + " Winners"
      } else {
        return PrizeDisDetail[PrizeDisDetail.length - 1].max + " Winner"
      }
    }
  }

  viewWinners = (e, templateObj) => {
    e.stopPropagation();
    let tmpObj = templateObj
    tmpObj['prize_distibution_detail'] = this.handleJsonData(templateObj.prize_distibution_detail)
    this.setState({ 'prize_modal': true, 'templateObj': tmpObj });
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

  createTemplateContest = () => {
    let { FromFixture, BackTab, fixtureDetail, collection_master_id, IsH2h,isDFS } = this.state
    if (this.state.selectedTemplate.length <= 0) {
      notify.show("Please select atleast one template.", "error", 3000);
      return false;
    }

    if (window.confirm("Are you sure want to create contest of selected template ?")) {
      this.setState({ saveLoad: true })
      let collection_name = fixtureDetail.home + ' vs ' + fixtureDetail.away;
      let params = {
        "collection_master_id": collection_master_id,
        "selected_templates": this.state.selectedTemplate,
        ...(IsH2h != "1" &&  !isDFS && {
          "sports_id": this.state.selected_sport,
          "season_scheduled_date": (fixtureDetail.season_scheduled_date) ? fixtureDetail.season_scheduled_date : fixtureDetail[0].season_scheduled_date,
          // 'league_id': this.state.league_id,
          "season_id": this.state.season_id,
          "collection_name": collection_name
        })
       
      };
      let URL = '';
      if (IsH2h == "1") {
        URL = NC.DFS_H2H_SAVE_FIXTURE_H2H_TEMPLATE
      } else {
        URL = NC.CREATE_TEMPLATE_CONTEST
      }

      WSManager.Rest(NC.baseURL + URL, params).then((responseJson) => {
        if (responseJson.response_code === NC.successCode) {
          notify.show(responseJson.message, "success", 5000);
          /**Booster redirection */
          if (this.state.season_id && HF.allowBooster() == '1' && HF.allowBoosterInSports(this.state.selected_sport) && FromFixture === '0') {
console.log('111')
            // this.props.history.push({ pathname: '/contest/booster/' + this.props.match.params.league_id + '/' + this.props.match.params.season_id + '/2/0' });
            this.props.history.push({ pathname: '/contest/booster/' + this.props.match.params.collection_master_id + '/' + this.props.match.params.season_id + '/2/0' });

          }
          // else if (this.state.season_id) {
          else if (this.state.isDFS) {
            console.log('222')
            // this.props.history.push({ pathname: '/contest/fixturecontest/' + this.state.league_id + '/' + this.state.season_id + '/' + this.state.BackTab })
            this.props.history.push({ 
              pathname: '/contest/fixturecontest/' + this.state.collection_master_id + '/' + this.state.season_id + '/' + this.state.BackTab ,
              isDFS: this.state.isDFS
            })

          } else {console.log('333')
            // this.props.history.push({ pathname: '/contest/collectioncontest/' + this.state.league_id + '/' + this.state.collection_master_id })
            this.props.history.push({ pathname: '/contest/collectioncontest/' + this.state.league_id + '/' + this.state.collection_master_id })
          }

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

  render() {
    let {
      SelectAllTemp,
      fixtureDetail,
      templateList,
      templateObj,
      selectedTemplate,
      BackTab,
      selected_sport,
      IsH2h,
      saveLoad,
    } = this.state
    const settings = {
      dots: false,
      infinite: false,
      speed: 500,
      slidesToShow: 4,
      slidesToScroll: 1,
      arrows: false,
      responsive: [
        {
            breakpoint: 1400,
            settings: {
                slidesToShow: 3
            }
        }
      ]
    };
    let { int_version } = HF.getMasterData()

    return (
      <div className="animated fadeIn create-temp-contest fixture-contest-main1">
        {console.log('fixtureDetail',fixtureDetail)}
        {!_.isEmpty(fixtureDetail) && this.state.season_id &&
          <Fragment>
            <Row>
              <Col lg={6}>
                
                  {fixtureDetail.is_tour_game == "1"
                  ?
                  <div className="bg-card-motor-sports carddiv pull-left temp-max-wdt">
                                    <div className="motor-sports-container">
                                        <div className="top-view-motor-sports">
                                            <div className={`car-type-view ${fixtureDetail.league_name == "Formula 1" ? " formula-one" : fixtureDetail.league_name == "Moto GP" ? " moto-gp" : fixtureDetail.league_name == "Desert racing" ? " desert-racing" : " other-league-abbr"}`}>{fixtureDetail.league_name}</div>
                                        </div>
                                        <div className="motor-sports-view">
                                            <img className="img-colum-view" src={NC.S3 + NC.MOTOR_SPORTS_IMG + fixtureDetail.league_image} alt=""
                                            ></img>
                                            <div className="inner-view-motor-sports">
                                                <div className="tournament-name-view">{fixtureDetail.tournament_name}</div>
                                                <div className="events-view">{fixtureDetail.match_event} events</div>
                                                <div className="date-view">
                                                  {/* {WSManager.getUtcToLocalFormat(fixtureDetail.season_scheduled_date, 'D MMM YYYY hh:mm A')}  */}
                                                  {HF.getFormatedDateTime(fixtureDetail.season_scheduled_date, 'D MMM YYYY hh:mm A')}
                                                  
                                                  to 
                                                  {/* {WSManager.getUtcToLocalFormat(fixtureDetail.end_scheduled_date, 'D MMM YYYY hh:mm A')}  */}
                                                  {HF.getFormatedDateTime(fixtureDetail.end_scheduled_date, 'D MMM YYYY hh:mm A')}
                                                  
                                                  </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                   :
                   <div className="carddiv pull-left temp-max-wdt">
                  <div>
                    <img className="cardimgdfs float-left" src={NC.S3 + NC.FLAG + (fixtureDetail.home_flag ? fixtureDetail.home_flag : fixtureDetail.match[0].home_flag)}></img>
                    <div className="inner-div-container">
                      <h3 className="livcardh3dfs">{(fixtureDetail.home) ? fixtureDetail.home : fixtureDetail.match[0].home} VS {(fixtureDetail.away) ? fixtureDetail.away : fixtureDetail.match[0].away}</h3>
                      <h6 className="livcardh6dfs">
                        {HF.getFormatedDateTime(fixtureDetail.season_scheduled_date, 'D-MMM-YYYY hh:mm A')}
                      </h6>

                      <h6 className="livcardh6dfs">{fixtureDetail.league_name}</h6>
                    </div>
                    <img className="cardimgdfs" src={NC.S3 + NC.FLAG + (fixtureDetail.away_flag ? fixtureDetail.away_flag : fixtureDetail.match[0].away_flag)}></img>
                  </div>
                  </div>
                  }
                  
               
              </Col>
              <Col md={6}>
                {
                  (fixtureDetail.match_started == 0 && IsH2h == '0') &&
                  <Button className='pull-right create-template-btn w-auto' outline color="danger" onClick={() => this.redirectToCreateContest()}>Create New Contest</Button>
                }
              </Col>
            </Row>
          </Fragment>
        }
        {
          !this.state.isDFS &&
          <Fragment>
            <Row>
              <Col lg={12}>
                {
                  fixtureDetail && fixtureDetail.collection_name &&
                  <div><h2 class="h2-cls mb-20">{ fixtureDetail.collection_name}</h2></div>
                }
                <Slider {...settings}>

                  {
                    !_.isEmpty(fixtureDetail)
                      ?
                      _.map(fixtureDetail, (fixtureitem, fixtureindex) => {
                        if (typeof fixtureitem.season_id == 'undefined') return false;
                        return (
                          <Card className="livecard">
                            <div className="carddiv" >
                              <Col>
                                <img className="cardimg" src={NC.S3 + NC.FLAG + fixtureitem.home_flag}></img>
                              </Col>
                              <Col>
                                <h4 className="livcardh3">{(fixtureitem.home) ? fixtureitem.home : 'TBA'} VS {(fixtureitem.away) ? fixtureitem.away : 'TBA'}</h4>
                                <h6 className="livcardh6dfs">
                                  {/* <MomentDateComponent data={{ date: fixtureitem.season_scheduled_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                                  {HF.getFormatedDateTime(fixtureitem.season_scheduled_date, "D-MMM-YYYY hh:mm A")}
                                
                                </h6>
                                {<h6 className="livcardh6dfs">{fixtureitem.league_name} -{fixtureitem.format_str} </h6>}
                              </Col>
                              <Col>
                                <img className="cardimg" src={NC.S3 + NC.FLAG + fixtureitem.away_flag}></img>
                              </Col>
                            </div>
                          </Card>
                        )
                      }) : ''
                  }
                </Slider>
              </Col>
            </Row>
          </Fragment>
        }
        <Col lg={12}>
          <Row className="dfsrow border-bottom">
            <label className="dfssports">Daily Fantasy Sports</label>
            {
              this.state.isDFS &&
              <label
                className="backtofixtures"
                onClick={() => this.props.history.push('/game_center/DFS?tab=' + BackTab)}> {'<'} {int_version == "1" ? "Back to Games" : "Back to Fixtures"}</label>
            }
            {
              !this.state.isDFS &&
              <label className="backtofixtures" onClick={() => this.props.history.push({ pathname: '/multigame/Fixtures' })}> {'<'} Back to list</label>
            }
          </Row>
        </Col>
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
            <label className="select-all-checkbox">
              <Input
                type="checkbox"
                name="SelectAllTemp"
                checked={SelectAllTemp}
                onClick={() => this.selectAllTemplate()}
              />
              <span>Select All Template</span>
            </label>
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
                                    (HF.allowSecondInni() == '1' && template.is_2nd_inning == "1") &&
                                    <li className="action-item">
                                      <i
                                        className="icon-snd"
                                        id={"sinni_tt_" + group_index + '_' + idx}>
                                        <span>
                                          <Tooltip
                                            placement="left"
                                            isOpen={template.sinni_tt}
                                            target={"sinni_tt_" + group_index + '_' + idx}
                                            toggle={() => this.SWinToggle(group_index, idx, 'sinni_tt')}>
                                            {SECOND_INNING}
                                          </Tooltip>
                                        </span>
                                      </i>
                                    </li>
                                  }
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
                                  {
                                    template.is_reverse == "1" &&
                                    <li className="action-item">
                                      <img className="reverse-contest" title="Reverse contest" src={Images.REVERSE_FANTASY} />
                                    </li>
                                  }
                                  {
                                    (HF.allowScratchWin() == '1') &&
                                    <li className="action-item">
                                      <i id={"swin_" + group_index + '_' + idx} className={`icon-SW contest-type ${(template.is_scratchwin == "1") ? '' : 'not-active'}`}>
                                        <span className="btn-information">
                                          <Tooltip placement="right" isOpen={template.swin_tt} target={"swin_" + group_index + '_' + idx} toggle={() => this.SWinToggle(group_index, idx, 'swin_tt')}>{SCRATCH_WIN}</Tooltip>
                                        </span>
                                      </i>
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
                              {/* <div className="sponsor-name float-left">
                              <div>{template.sponsor_logo ? "Sponsored by :" : 'Sponsor not assigned'}</div>
                            </div> */}
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
                        <div className="league-listing-wrapper details-para">
                          <div className="league-listing-container">
                            <div className="contest-details-para">
                              <p>{template.template_description}</p>
                            </div>
                          </div>
                        </div>
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

export default Createtemplatecontest;