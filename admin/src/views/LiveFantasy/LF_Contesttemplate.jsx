import React, { Component } from 'react';
import { Col, Row, Modal, ModalBody, ModalHeader, ModalFooter, FormGroup, Input, InputGroup, Button, Tooltip } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { Progress } from 'reactstrap';
import { notify } from 'react-notify-toast';
import Select from 'react-select';
import LS from 'local-storage';
import Images from '../../components/images';
import HF, { _isUndefined } from '../../helper/HelperFunction';
import { SCRATCH_WIN, SECOND_INNING } from '../../helper/Message';
import { Base64 } from 'js-base64';
class LF_Contesttemplate extends Component {
  constructor(props) {
    super(props);
    this.state = {
      selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,
      keyword: '',
      league_id: '',
      template_league_id: '',
      templateList: [],
      leagueList: [],
      tempLeagueList: [],
      posting: false,
      loadMoring: true,
      prize_modal: false,
      templateObj: {},
      selectedTemplate: [],
      SelectAllTemp: false,
      TotalTemplates: 0,
      SWinToolTip: false,
    };
  }

  componentDidMount() {
    this.GetSportLeagueList();
    this.GetContestTemplates();
  }

  handleSelect(eleObj, dropName) {
    if(eleObj){
      if (dropName == "league_id") {
        this.setState({ 'league_id': eleObj.value }, function () {
          this.GetContestTemplates();
        });
      } else {
        this.setState({ 'template_league_id': eleObj.value }, function () { });
      }
    }
  }

  handleFieldVal = (e, tindex) => {
    if (e) {
      let value = e.target.value;
      this.setState({ 'keyword': value }, function () { });
    }
  }

  GetSportLeagueList = () => {
    this.setState({ posting: true })
    WSManager.Rest(NC.baseURL + NC.LF_GET_SPORT_LEAGUES, { "sports_id": this.state.selected_sport }).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        responseJson = responseJson.data ? responseJson.data : [];
        let tempArr = [];
        let tempLeagueList = [];
        responseJson.map(function (lObj, lKey) {
          tempArr.push({ value: lObj.league_id, label: lObj.league_abbr });
          tempLeagueList[lObj.league_id] = lObj.league_abbr;
        });
        this.setState({ leagueList: tempArr, tempLeagueList: tempLeagueList });
      }
      this.setState({ posting: false })
    })
  }

  GetContestTemplates = () => {
    this.setState({ posting: true })
    let params = { "sports_id": this.state.selected_sport, 'league_id': this.state.league_id, 'keyword': this.state.keyword, 'sort_field': 'CT.group_id', 'sort_order': 'ASC' };
    WSManager.Rest(NC.baseURL + NC.LF_GET_CONTEST_TEMPLATE_LIST, params).then((responseJson) => {
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

  deleteContestTemplate = (e, templateObj, index) => {
    e.stopPropagation();
    if (window.confirm("Are you sure want to delete this template ?")) {
      this.setState({ posting: true })
      let params = { "contest_template_id": templateObj.contest_template_id };
      WSManager.Rest(NC.baseURL + NC.LF_DELETE_TEMPLATE, params).then((responseJson) => {
        if (responseJson.response_code === NC.successCode) {
          this.GetContestTemplates();
          notify.show(responseJson.message, "success", 5000);
        } else {
          notify.show(responseJson.message, "error", 3000);
        }
        this.setState({ posting: false })
      })
    } else {
      return false;
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
    let { templateList } = this.state
    let selectedTemplate = _.cloneDeep(this.state.selectedTemplate);
    templateList.map((template) => (
      _.map(template.template_list, (templist) => {
        if (selectedTemplate.indexOf(templist.contest_template_id) == -1) {
          selectedTemplate.push(templist.contest_template_id);
          this.setState({ SelectAllTemp: true })
        }
      })
    ))
    this.setState({ selectedTemplate: selectedTemplate });
  }

  applyTemplateToLeague = () => {
    if (this.state.selectedTemplate.length <= 0) {
      notify.show("Please select atleast one template.", "error", 3000);
      return false;
    }
    if (!this.state.template_league_id || this.state.template_league_id == "") {
      notify.show("Please select league for apply template.", "error", 3000);
      return false;
    }
    if (window.confirm("Are you sure want to apply selected template on selected league ?")) {
      this.setState({ posting: true })
      let params = { "selected_templates": this.state.selectedTemplate, 'league_id': this.state.template_league_id };
      WSManager.Rest(NC.baseURL + NC.LF_APPLY_TEMPLATE_TO_LEAGUE, params).then((responseJson) => {
        if (responseJson.response_code === NC.successCode) {
          this.setState({ 'selectedTemplate': [], 'template_league_id': '', SelectAllTemp: false });
          notify.show(responseJson.message, "success", 5000);
          this.GetContestTemplates();
        } else {
          notify.show(responseJson.message, "error", 3000);
        }
        this.setState({ posting: false })
      })
    } else {
      return false;
    }
  }

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
      // prize_text = HF.getCurrencyCode() + parseFloat(prizeAmount.real).toFixed(2);
      prize_text = HF.getCurrencyCode() + HF.getPrizeInWordFormat(prizeAmount.real);


    } else if (is_tie_breaker == 0 && prizeAmount.bonus > 0) {

      // prize_text = '<i class="icon-bonus"></i>' + parseFloat(prizeAmount.bonus).toFixed(2);
      prize_text = '<i class="icon-bonus"></i>' + HF.getPrizeInWordFormat(prizeAmount.bonus);

    } else if (is_tie_breaker == 0 && prizeAmount.point > 0) {
      // prize_text = '<img src="' + Images.COINIMG + '" alt="coin-img" />' + parseFloat(prizeAmount.point).toFixed(2);
      prize_text = '<img src="' + Images.COINIMG + '" alt="coin-img" />' + HF.getPrizeInWordFormat(prizeAmount.point)
    }
    return { __html: prize_text };

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
      templateList,
      leagueList,
      tempLeagueList,
      templateObj,
      selectedTemplate,
      SWinToolTip,
      selected_sport,
    } = this.state

    return (
      <div className="animated fadeIn fk-contest-template">
        <Col lg={12}>
          <Row className="dfsrow">
            <h2 className="h2-cls">Contest template list</h2>
            <Button className='btn-secondary-outline' onClick={() => {
              this.props.history.push({
                pathname: '/livefantasy/createcontesttemplate'
              })
            }}>Create New Template</Button>
          </Row>
        </Col>
        <Row>
          <Col xs="12" sm="12" md="12">
            {/* <label className="float-left form-group filter-label">Filter By - </label>
            <FormGroup className="float-left league-filter select-wrapper">
              <Select class="form-control"
                className=""
                id="league_id"
                name="league_id"
                placeholder="All League"
                value={this.state.league_id}
                options={leagueList}
                onChange={(e) => this.handleSelect(e, 'league_id')}
                menuPlacement="auto"

              >
              </Select>
            </FormGroup> */}
            <FormGroup className="float-right">
              <InputGroup className="search-wrapper">
                <i className="icon-search" onClick={() => this.GetContestTemplates()}></i>
                <Input type="text" id="keyword" name="keyword" value={this.state.keyword} onChange={(e) => this.handleFieldVal(e, 'keyword')} onKeyPress={event => { if (event.key === 'Enter') { this.GetContestTemplates() } }} placeholder="Enter Contest name" />
              </InputGroup>
            </FormGroup>
          </Col>
        </Row>

        <Row>
          {/* <Col md={12}>
            <label className="select-all-checkbox">
              <Input
                type="checkbox"
                name="SelectAllTemp"
                checked={SelectAllTemp}
                onChange={() => this.selectAllTemplate()}
              />
              <span>Select All Template</span>
            </label>
          </Col> */}
        </Row>

        <div className="contest-group-container contest-listing contest-height">
          {templateList.map((item, group_index) => (
            <Row key={group_index}>
              <Col xs="12" sm="12" md="12">
                <h4 className="contest-listing-h4">{item.group_name}</h4>
              </Col>
              {item.template_list.map((template, idx) => (
                <Col md="4" key={idx}>
                  <div className="contest-group">
                    <div className="contest-list-wrapper">
                      <div className={selectedTemplate.indexOf(template.contest_template_id) != -1 ? "contest-card more-contest-card contest-selected" : "contest-card more-contest-card"}>
                        <div className="contest-list contest-card-body">
                          {/* onClick={() => this.selectTemplate(template, idx)} */}
                          <div className="contest-list-header">
                            <div className="contest-heading">
                              <div 
                                onClick={() => {
                                  this.props.history.push({
                                    pathname: '/livefantasy/contest_template_detail/' + template.contest_template_id,
                                    state: { templateGroup: item, template_list: template, leagueId: template.template_leagues['0'] }
                                  })
                                }}

                              className="contest-name text-ellipsis">{template.template_name}</div>
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
                                  {
                                    <li
                                      className="action-item"
                                      onClick={() => { this.props.history.push({ pathname:'/livefantasy/copycreatecontesttemplate/' + Base64.encode(template.contest_template_id)})}}
                                    >
                                      <i className="icon-copy contest-type" title="Copy Template"></i>
                                    </li>
                                  }
                                </ul>
                              </div>
                              <div className="clearfix">
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
                              </div>
                              <div className="text-small-italic">
                                <span onClick={(e) => this.viewWinners(e, template)}>{this.getWinnerCount(template)}</span>
                                <span className="fk-b-allow">{template.max_bonus_allowed ? template.max_bonus_allowed : '0'}% Bonus allowed</span>
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
                                    <span>
                                      <i className="icon-bonus"></i>
                                      {/* {template.entry_fee} */}
                                      {HF.getPrizeInWordFormat(parseInt(template.entry_fee))}
                                    </span>
                                  }
                                  {
                                    template.currency_type == '1' && template.entry_fee > 0 &&
                                    // <span><i className="icon-rupess"></i>{template.entry_fee} </span>
                                    <span>
                                      {HF.getCurrencyCode()}
                                      {HF.getPrizeInWordFormat(parseInt(template.entry_fee))}
                                      {/* {template.entry_fee} */}
                                    </span>
                                  }
                                  {
                                    template.currency_type == '2' && template.entry_fee > 0 &&
                                    <span>
                                      <img src={Images.COINIMG} alt="coin-img" />
                                      {HF.getPrizeInWordFormat(parseInt(template.entry_fee))}
                                      {/* {template.entry_fee} */}
                                    </span>
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
                          <div className="sponsor-box league-listing-wrapper">
                            <div className="league-listing-container">
                              {/* <div className="sponsor-name">
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
                        {/* <div className="league-listing-wrapper">
                          <div className="league-listing-container">
                            <div className="league-ul-wrapper">
                              <ul>
                                <li>
                                  <a className="league-name">{tempLeagueList[template.template_leagues['0']]}</a>
                                  {
                                    (template.total_league > 1) &&
                                    <span>and {template.total_league - 1} more league(s)</span>
                                  }
                                </li>
                              </ul>
                            </div>
                            <div className="league-edit-container">
                              <i className="icon-delete" onClick={(e) => this.deleteContestTemplate(e, template, idx)}></i>
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
        {templateList.length <= 0 &&
          <div className="no-records">There is no template created yet.</div>
        }
        {/* {templateList.length > 0 &&

          <Row>
            <Col sm="12">
              <div className="fixed-bottom-contest-list">
                <label className="float-left form-group filter-label">Apply Selected Template To: </label>
                <FormGroup className="float-left league-filter">
                  <Select class="form-control"
                    className=""
                    id="template_league_id"
                    name="template_league_id"
                    placeholder="Select League"
                    value={this.state.template_league_id}
                    options={leagueList}
                    onChange={(e) => this.handleSelect(e, 'template_league_id')}
                    menuPlacement="auto"
                  >
                  </Select>
                </FormGroup>
                <FormGroup className="float-right">
                  <InputGroup className="search-wrapper">
                    <Button disabled={this.state.template_league_id == "" || selectedTemplate.length <= 0} onClick={() => this.applyTemplateToLeague()} className='pull-right create-template-btn'>CONFIRM</Button>
                  </InputGroup>
                </FormGroup>
              </div>
            </Col>
          </Row>


        } */}
        <div className="winners-modal-container">
          <Modal isOpen={this.state.prize_modal} toggle={() => this.closePrizeModel()} className="winning-modal">
            <ModalHeader toggle={this.toggle}>Winnings Distribution</ModalHeader>
            <ModalBody>
              <div className="distribution-container">
                {
                  templateObj.prize_distibution_detail &&
                  <table>
                    <thead>
                      <tr>
                        <th>Rank</th>
                        <th style={{ width: "100px", textAlign: "center" }}>Min</th>
                        <th style={{ width: "100px", textAlign: "center" }}>Max</th>
                      </tr>
                    </thead>
                    <tbody>
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
                              HF.getNumberWithCommas(prize.min_value)
                              // parseFloat(prize.min_value).toFixed(2)
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
                              HF.getNumberWithCommas(prize.max_value)
                              // parseFloat(prize.max_value).toFixed(2)
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

export default LF_Contesttemplate;