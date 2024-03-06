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
import HF, { _isUndefined } from '../../helper/HelperFunction';
import { MomentDateComponent } from "../../components/CustomComponent";
import { SCRATCH_WIN } from '../../helper/Message';
import ESF_FixtureCard from './ESF_FixtureCard';
class ESF_CreateTemplateContest extends Component {
    constructor(props) {
        super(props);
        this.state = {
            collection_id: (this.props.match.params.collection_id) ? this.props.match.params.collection_id : '1',
            ActiveFxType: (this.props.match.params.category) ? this.props.match.params.category : '1',
            ActiveTab: (this.props.match.params.activeTab) ? this.props.match.params.activeTab : '1',
            FixtureValue: (this.props.match.params.fxvalue) ? this.props.match.params.fxvalue : '1',
            selected_sport: (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId,

            fixtureDetail: {},
            templateList: [],
            posting: false,
            prize_modal: false,
            templateObj: {},
            selectedTemplate: [],
            TotalTemplates: 0,
            BackTab: (this.props.match.params.tab) ? this.props.match.params.tab : 2,
            ScheduledDate: new Date(),
        };
    }

    componentDidMount() {
        if (HF.allowEquityFantasy() == '0') {
            notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        this.GetFixtureDetail();
        this.GetFixtureTemplates();
    }
    redirectToCreateContest = () => {
        if (!_.isEmpty(this.state.collection_master_id)) {
            this.props.history.push({ pathname: '/contest/createcollectioncontest/' + this.state.league_id + '/' + this.state.collection_master_id })
        } else {
            this.props.history.push({ pathname: '/contest/createcontest/' + this.state.league_id + '/' + this.state.season_game_uid })
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
            prize_text = HF.getCurrencyCode() + parseFloat(prizeAmount.real).toFixed(2);
        } else if (is_tie_breaker == 0 && prizeAmount.bonus > 0) {
            prize_text = '<i class="icon-bonus"></i>' + parseFloat(prizeAmount.bonus).toFixed(2);
        } else if (is_tie_breaker == 0 && prizeAmount.point > 0) {
            prize_text = '<img src="' + Images.COINIMG + '" alt="coin-img" />' + parseFloat(prizeAmount.point).toFixed(2);
        }
        return { __html: prize_text };

    }

    GetFixtureTemplates = () => {
        this.setState({ posting: true })
        let params = {
            "collection_id": this.state.collection_id
        };
        WSManager.Rest(NC.baseURL + NC.ESF_GET_FIXTURE_TEMPLATE, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                responseJson = responseJson.data;


                var totTemp = 0
                responseJson.map((template) => {
                    totTemp += parseInt(template.template_list.length)
                })

                this.setState({
                    templateList: responseJson,
                    TotalTemplates: totTemp,
                    ScheduledDate: responseJson.data ? responseJson.data.scheduled_date : new Date(),
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
        if (selectedTemplate.indexOf(templateObj.template_id) == -1) {
            selectedTemplate.push(templateObj.template_id);
        } else {
            var item_index = selectedTemplate.indexOf(templateObj.template_id);
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
                if (selectedTemplate.indexOf(templist.template_id) == -1 && templist.stock_type == 2) {
                    selectedTemplate.push(templist.template_id);
                    this.setState({ SelectAllTemp: true })
                }
            })
        ))
        this.setState({ selectedTemplate: selectedTemplate });
    }

    createTemplateContest = () => {
        let { selectedTemplate, collection_id, ActiveFxType, ActiveTab, FixtureValue } = this.state
        if (selectedTemplate.length <= 0) {
            notify.show("Please select atleast one template.", "error", 3000);
            return false;
        }

        if (window.confirm("Are you sure want to create contest of selected template ?")) {
            this.setState({ posting: true })

            let params = {
                "collection_id": collection_id,
                "selected_templates": selectedTemplate
            };

            WSManager.Rest(NC.baseURL + NC.ESF_CREATE_TEMPLATE_CONTEST, params).then((responseJson) => {
                if (responseJson.response_code === NC.successCode) {
                    notify.show(responseJson.message, "success", 5000);

                    this.props.history.push({ pathname: '/equitysf/fixturecontest/' + ActiveFxType + '/' + ActiveTab + '/' + collection_id });

                } else {
                    notify.show(responseJson.message, "error", 3000);
                }
                this.setState({ posting: false })
            })
        } else {
            return false;
        }
    }

    SWinToggle = (g_idx, idx) => {
        if (!_isUndefined(g_idx) && !_isUndefined(idx)) {
            let templateList = this.state.templateList
            let flag = templateList[g_idx]['template_list'][idx]['swin_tt']
            templateList[g_idx]['template_list'][idx]['swin_tt'] = !flag
            this.setState({ templateList });
        }
    }

    GetFixtureDetail = () => {
        let param = {
            "collection_id": this.state.collection_id,
        }
        WSManager.Rest(NC.baseURL + NC.ESF_GET_COLLECTION_DETAILS, param).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({
                    fixtureDetail: responseJson.data,
                });
            }
            else if (responseJson.response_code == NC.sessionExpireCode) {
                WSManager.logout();
                this.props.history.push('/login');
            }
        })
    }

    render() {
        let {
            SelectAllTemp,
            fixtureDetail,
            templateList,
            templateObj,
            selectedTemplate,
            BackTab,
            ActiveFxType,
            ActiveTab,
            FixtureValue,
            ScheduledDate,
        } = this.state
        let fx_item = {
            scheduled_date: fixtureDetail.scheduled_date,
            week: fixtureDetail.week,
            month: fixtureDetail.month,
            name: fixtureDetail.name,
        }
        return (
            <div className="animated fadeIn sf-create-temp-contest">
                <Row className="mt-30">
                    <Col md={12}>
                        <ESF_FixtureCard
                            callfrom={'2'}
                            activeFxTab={ActiveFxType}
                            activeTab={ActiveTab}
                            edit={false}
                            item={fx_item}
                            redirectToTemplate={null}
                            redirectToStockReview={null}
                            redirectToUpdateStock={null}
                            openMsgModal={null}
                            openDelayModal={null}
                            show_flag={true}
                        />
                    </Col>
                </Row>
                <Col lg={12}>
                    <Row className="dfsrow border-bottom">
                        <label className="dfssports">Daily Stock Fantasy</label>
                        {
                            this.state.collection_id &&
                            <label className="back-to-fixtures" onClick={() => this.props.history.push('/equitysf/fixture?pctab=' + ActiveFxType + '&tab=' + ActiveTab)}> {'<'} Back to Fixtures</label>
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
                                <>
                                {
                                     template.stock_type == 2 &&
                                    <Col md="4" key={idx}>
                                        <div className="contest-group" onClick={() => this.selectTemplate(template, idx)}>
                                            <div className="contest-list-wrapper">
                                                <div className={selectedTemplate.indexOf(template.template_id) != -1 ? "contest-card more-contest-card contest-selected" : "contest-card more-contest-card"}>
                                                    <div className="contest-list contest-card-body">
                                                        <div className="contest-list-header">
                                                            <div className="contest-heading">
                                                                <div className="contest-name">{template.contest_name}</div>
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
                                                                            template.is_reverse == "1" &&
                                                                            <li className="action-item">
                                                                                <img className="reverse-contest" title="Reverse contest" src={Images.REVERSE_FANTASY} />
                                                                            </li>
                                                                        }
                                                                        {/* {
                                                                            (HF.allowScratchWin() == '1') &&
                                                                            <li className="action-item">
                                                                                <i id={"swin_" + group_index + '_' + idx} className={`icon-SW contest-type ${(template.is_scratchwin == "1") ? '' : 'not-active'}`}>
                                                                                    <span className="btn-information">
                                                                                        <Tooltip placement="right" isOpen={template.swin_tt} target={"swin_" + group_index + '_' + idx} toggle={() => this.SWinToggle(group_index, idx)}>{SCRATCH_WIN}</Tooltip>
                                                                                    </span>
                                                                                </i>
                                                                            </li>
                                                                        } */}
                                                                    </ul>
                                                                </div>
                                                                {/* <div className="alphabets-icon">
                                    {
                                    template.guaranteed_prize == '2' &&
                                    <i className="icon-icon-g contest-type"></i>
                                    }
                                    {
                                    template.multiple_lineup > 1 &&
                                    <i className="icon-icon-m contest-type"></i>
                                    }
                                    {
                                    template.is_auto_recurring == "1" &&
                                    <i className="icon-icon-r contest-type"></i>
                                    }
                                    {
                                    template.is_reverse == "1" &&
                                    <img className="reverse-contest" title="Reverse contest" src={Images.REVERSE_FANTASY} />
                                    }
                                    {
                                    (HF.allowScratchWin() == '1' && template.is_scratchwin == "1") &&
                                    <i id={"swin_" + group_index + '_' + idx} className="icon-SW contest-type">
                                        <span className="btn-information">
                                        <Tooltip placement="right" isOpen={template.swin_tt} target={"swin_" + group_index + '_' + idx} toggle={() => this.SWinToggle(group_index, idx)}>{SCRATCH_WIN}</Tooltip>
                                        </span>
                                    </i>
                                    }
                                </div> */}
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
                                }
                                </>
                            ))}
                        </Row>
                    ))}
                </div>

                <div className="createtempcontest">
                    <Row>
                        <Col lg={12}>
                            <div className="bottom-container">
                                <p>Once created, filled contests cannot be edited</p>
                                <Button disabled={selectedTemplate.length <= 0} onClick={() => this.createTemplateContest()} className="btn-secondary-outline">Create {selectedTemplate.length} Contest</Button>
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

export default ESF_CreateTemplateContest;