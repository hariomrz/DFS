import React, { Component } from 'react';
import { Card, CardBody, Col, Row, Modal, ModalBody, ModalHeader, ModalFooter, FormGroup, Input, InputGroup, Button, Table } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Select from 'react-select';
import LS from 'local-storage';
import queryString from 'query-string';
import Images from '../../components/images';
import * as MODULE_C from "../Marketing/Marketing.config";
import PromoteContestModal from '../../Modals/PromoteContest';
import PromoteNotActive from '../../Modals/PromoteNotActive';
import moment from 'moment';
import Pagination from "react-js-pagination";
import HF from '../../helper/HelperFunction';
import { MomentDateComponent } from "../../components/CustomComponent";
class SP_ContestList extends Component {
  constructor(props) {
    super(props);
    this.state = {
      contestParams: { 'category_id': '', 'group_id': '', 'status': '', 'keyword': '', 'sort_field': 'season_scheduled_date', 'sort_order': 'DESC', 'pagesCount': 1, 'current_page': 1, 'items_perpage': NC.ITEMS_PERPAGE },
      leagueList: [],
      groupList: [],
      statusList: [],
      contestList: [],
      contestObj: {},
      keyword: '',
      posting: false,
      contest_promote_model: false,
      contestPromoteParam: {
        email_contest_model: false,
        message_contest_model: false,
        notification_contest_model: false
      },
      promote_model: false,
      minPage: 1,
      maxPage: 5,
      fixtureList: [],
      CategoryList: [],
    };

  }

  componentDidMount() {
    if (HF.allowStockPredict() == '0') {
      notify.show(NC.MODULE_NOT_ENABLE, 'error', 5000)
      this.props.history.push('/dashboard')
    }
    this.GetContestFilterData();
  }

  handleFieldVal = (e, tindex) => {
    if (e) {
      let value = e.target.value;
      let contestParams = this.state.contestParams;
      contestParams['keyword'] = value;
      this.setState({ 'contestParams': contestParams }, function () { });
    }
  }

  handleSelect = (eleObj, dropName) => {
    let contestParams = this.state.contestParams;
    contestParams[dropName] = (eleObj != null) ? eleObj.value : '';
    this.setState({
      'contestParams': contestParams,
    }, function () {
      this.SearchContest();
    });
  }

  GetContestFilterData = () => {
    this.setState({ posting: true })
    let params = {};
    WSManager.Rest(NC.baseURL + NC.ESF_GET_CONTEST_FILTER, params).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        responseJson = responseJson.data;

        let tempCateList = [{ 'value': '', 'label': 'All' }];
        if (responseJson.category_list) {
          responseJson.category_list.map(function (lObj) {
            tempCateList.push({ value: lObj.category_id, label: lObj.name });
          });
        }
        let tempGroupList = [{ 'value': '', 'label': 'All' }];
        if (responseJson.group_list) {
          responseJson.group_list.map(function (lObj) {
            tempGroupList.push({ value: lObj.group_id, label: lObj.group_name });
          });
        }
        this.setState({ CategoryList: tempCateList, groupList: tempGroupList, statusList: responseJson.status_list });

        this.GetContestList();
      }
      this.setState({ posting: false })
    })
  }

  SearchContest = () => {
    let contestParams = this.state.contestParams;
    contestParams["current_page"] = 1;
    contestParams["pagesCount"] = 1;
    this.setState({ 'contestParams': contestParams }, function () {
      this.GetContestList();
    });
  }

  GetContestList = () => {
    this.setState({ posting: true })
    let params = this.state.contestParams;
    params.stock_type = "3";
    WSManager.Rest(NC.baseURL + NC.ESF_GET_CONTEST_LIST, params).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        var responseJsonData = responseJson.data.result;
        this.setState({
          contestList: responseJsonData,
          Total_Records: responseJson.data.total,

          contestParams: { ...this.state.contestParams, pagesCount: Math.ceil(responseJson.data.total / this.state.contestParams.pageSize), totalRecords: responseJson.data.total },
        })
      }
      this.setState({ posting: false })
    })
  }

  sortContestList = (e, sort_field) => {
    let contestParams = _.cloneDeep(this.state.contestParams);
    let sort_order = contestParams.sort_order;
    if (contestParams.sort_field == sort_field) {
      if (sort_order == "DESC") {
        sort_order = "ASC";
      } else {
        sort_order = "DESC";
      }
    } else {
      sort_order = "DESC";
    }

    contestParams['sort_field'] = sort_field;
    contestParams['sort_order'] = sort_order;
    this.setState({ 'contestParams': contestParams }, function () {
      this.GetContestList();
    });
  }

  toggleContestPromoteModal = (key, val) => {
    if (!NC.ALLOW_COMMUNICATION_DASHBOARD) {
      this.setState({
        promote_model: true
      });
      return false;
    }
    var params = {};
    let TempDate = moment(WSManager.getUtcToLocal(val.season_scheduled_date)).format("D-MMM-YYYY hh:mm A")
    params.email_template_id = 2;
    params.contest_id = val.contest_id;
    params.all_user = 1;

    params.for_str = 'for Contest ' + val.contest_name + '(' + val.home + ' vs ' + val.away + ' ' + TempDate + ')';
    const stringified = queryString.stringify(params);
    this.props.history.push(`/marketing/new_campaign?${stringified}`);
    return false;
  }

  PromoteContestHide = () => {
    this.setState({
      contest_promote_model: false
    });
  }

  PromoteHide = () => {
    this.setState({
      promote_model: false
    });
  }

  handlePageChange(current_page) {
    let contestParams = this.state.contestParams;
    if (contestParams['current_page'] != current_page) {
      contestParams['current_page'] = current_page;
      this.setState({
        contestParams: contestParams,
      },
        function () {
          this.GetContestList();
        });
    }

  }

  exportUser = (contestId) => {
    var query_string = 'contest_id=' + contestId;
    let sessionKey = WSManager.getToken();
    query_string += "&Sessionkey" + "=" + sessionKey;
    window.open(NC.baseURL + 'stock/admin/contest/export_contest_winners?' + query_string, '_blank');
  }

  render() {
    let {
      groupList,
      statusList,
      contestList,
      contestObj,
      CategoryList,
      Total_Records,
    } = this.state

    return (
      <div className="animated fadeIn esf-contestlist-dashboard">
        <Col lg={12}>
          <Row className="dfsrow">
            <h2 className="h2-cls">Contests Dashboard</h2>
          </Row>
        </Col>
        <Row>
          <Col xs="12" sm="12" md="12" className="contest-dashboard-dropdown">
            <FormGroup className="league-filter select-wrapper">
              <label htmlFor="" className="label">Select Status</label>
              <Select
                className=""
                id="status"
                name="status"
                placeholder="Select Status"
                value={this.state.contestParams.status}
                options={statusList}
                onChange={(e) => this.handleSelect(e, 'status')}
              />
            </FormGroup>
            <FormGroup className="league-filter select-wrapper">
              <label htmlFor="" className="label">Search</label>
              <InputGroup className="search-wrapper">
                <i className="icon-search" onClick={() => this.SearchContest()}></i>
                <Input type="text" id="keyword" name="keyword" value={this.state.contestParams.keyword} onChange={(e) => this.handleFieldVal(e, 'keyword')} onKeyPress={event => { if (event.key === 'Enter') { this.SearchContest() } }} placeholder="Contest name" />
              </InputGroup>
            </FormGroup>
          </Col>
        </Row>
        <Row>
          <Col xs="12" lg="12" >
            <div className="table-responsive common-table">


              <Table className="xcommunication-table">
                <thead>
                  <tr>
                    <th rowSpan="2" className="contest-column">
                      <div onClick={(e) => this.sortContestList(e, 'contest_name')}>
                        <div id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          Contest Name
                              </div>
                        {
                          this.state.contestParams.sort_field == 'contest_name' && this.state.contestParams.sort_order == 'DESC' &&
                          <i className="fa fa-sort-desc"></i>
                        }
                        {
                          this.state.contestParams.sort_field == 'contest_name' && this.state.contestParams.sort_order == 'ASC' &&
                          <i className="fa fa-sort-asc"></i>
                        }
                      </div>
                    </th>

                    <th rowSpan="2" onClick={(e) => this.sortContestList(e, 'entry_fee')}>
                      <div>
                        <div id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          Entry Fee
                              </div>
                        {
                          this.state.contestParams.sort_field == 'entry_fee' && this.state.contestParams.sort_order == 'DESC' &&
                          <i className="fa fa-sort-desc"></i>
                        }
                        {
                          this.state.contestParams.sort_field == 'entry_fee' && this.state.contestParams.sort_order == 'ASC' &&
                          <i className="fa fa-sort-asc"></i>
                        }
                      </div>
                    </th>

                    <th rowSpan="2" onClick={(e) => this.sortContestList(e, 'minimum_size')}>
                      <div>
                        <div id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          Participants
                          </div>
                        {
                          this.state.contestParams.sort_field == 'minimum_size' && this.state.contestParams.sort_order == 'DESC' &&
                          <i className="fa fa-sort-desc"></i>
                        }
                        {
                          this.state.contestParams.sort_field == 'minimum_size' && this.state.contestParams.sort_order == 'ASC' &&
                          <i className="fa fa-sort-asc"></i>
                        }
                      </div>
                    </th>

                    <th>
                      <div>
                        <div id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          Team joined
                          </div>
                      </div>
                    </th>

                    <th rowSpan="2" onClick={(e) => this.sortContestList(e, 'spot_left')}>
                      <div>
                        <div id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          Spots Left
                          </div>
                        {
                          this.state.contestParams.sort_field == 'spot_left' && this.state.contestParams.sort_order == 'DESC' &&
                          <i className="fa fa-sort-desc"></i>
                        }
                        {
                          this.state.contestParams.sort_field == 'spot_left' && this.state.contestParams.sort_order == 'ASC' &&
                          <i className="fa fa-sort-asc"></i>
                        }
                      </div>
                    </th>

                    <th rowSpan="2" onClick={(e) => this.sortContestList(e, 'current_earning')}>
                      <div>
                        <div id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          Current <br /> Earning
                          </div>
                        {
                          this.state.contestParams.sort_field == 'current_earning' && this.state.contestParams.sort_order == 'DESC' &&
                          <i className="fa fa-sort-desc"></i>
                        }
                        {
                          this.state.contestParams.sort_field == 'current_earning' && this.state.contestParams.sort_order == 'ASC' &&
                          <i className="fa fa-sort-asc"></i>
                        }
                      </div>
                    </th>

                    <th rowSpan="2" onClick={(e) => this.sortContestList(e, 'potential_earning')}>
                      <div>
                        <div id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          Potential <br /> Earning
                          </div>
                        {
                          this.state.contestParams.sort_field == 'potential_earning' && this.state.contestParams.sort_order == 'DESC' &&
                          <i className="fa fa-sort-desc"></i>
                        }
                        {
                          this.state.contestParams.sort_field == 'potential_earning' && this.state.contestParams.sort_order == 'ASC' &&
                          <i className="fa fa-sort-asc"></i>
                        }
                      </div>
                    </th>
                    <th>Site Rake</th>
                    <th>Action</th>
                  </tr>
                </thead>
                {
                  _.map(contestList, (item, contest_index) => {
                    var mEndDate = new Date(WSManager.getUtcToLocal(item.season_scheduled_date));
                    var curDate = new Date();

                    let compDate = false;
                    if (curDate >= mEndDate) {
                      compDate = true;
                    }
                    return (
                      <tbody key={contest_index}>
                        <tr >
                          <td className="contest-column">
                            <div>
                              <div style={{ WebkitBoxOrient: 'vertical' }}> {item.contest_name}</div>
                              <div className="contest-table-p">
                                <div className="alphabets-icon">
                                  {
                                    item.guaranteed_prize == '2' &&
                                    <i className="icon-icon-g contest-type"></i>
                                  }
                                  {
                                    item.multiple_lineup > 1 &&
                                    <i className="icon-icon-m contest-type"></i>
                                  }
                                  {
                                    item.is_auto_recurring == "1" &&
                                    <i className="icon-icon-r contest-type"></i>
                                  }
                                </div>
                              </div>
                              <div className="carddiv contest-listtable">
                                {/* <MomentDateComponent data={{ date: item.scheduled_date, format: "D-MMM-YYYY" }} /> */}
                                {HF.getFormatedDateTime(item.scheduled_date, "D-MMM-YYYY")}
                              </div>
                              <div className="carddiv contest-listtable font-weight-bold">
                                {HF.getFormatedDateTime(item.scheduled_date, 'hh:mm A')}
                                {' - '}
                                {HF.getFormatedDateTime(item.end_date, 'hh:mm A')}
                              </div>
                            </div>
                          </td>
                          <td>
                            {
                              item.currency_type == '0' &&
                              <i className="icon-bonus"></i>
                            }
                            {
                              item.currency_type == '1' &&
                              HF.getCurrencyCode()
                            }
                            {
                              item.currency_type == '2' &&
                              <img src={Images.COINIMG} alt="coin-img" />
                            }
                            {item.entry_fee}
                          </td>
                          <td>{item.minimum_size + '-' + item.size}</td>
                          <td>{item.total_user_joined}</td>
                          <td>{item.spot_left}</td>
                          <td>{item.current_earning}</td>
                          <td>{item.potential_earning}</td>
                          <td>{item.site_rake ? item.site_rake : '--'}</td>
                          <td>
                            {/* {
                              (item.status == "0" && !compDate) &&
                              <Button onClick={() => this.toggleContestPromoteModal(contest_index, item)} className='cd-act-btns' outline disabled={item.status >= 1} color="danger">Promote</Button>
                            } */}
                            {
                              item.status == "2" ?
                              <Button
                                onClick={e => this.exportUser(item.contest_id)}
                                className='cd-act-btns'
                                outline
                                color="danger"
                              >
                                Export Winners
                              </Button>
                                :
                                '--'
                            }
                          </td>
                          
                        </tr>
                      </tbody>
                    )
                  })
                }
              </Table>
            </div>
          </Col>
        </Row>
        {contestList.length <= 0 &&
          <div className="no-records">No Record Found.</div>
        }
        {contestList.length > 0 &&
          <Col>
            <div className="custom-pagination lobby-paging">
              <Pagination
                activePage={this.state.contestParams.current_page}
                itemsCountPerPage={this.state.contestParams.items_perpage}
                totalItemsCount={Total_Records}
                pageRangeDisplayed={5}
                onChange={e => this.handlePageChange(e)}
              />
            </div>
          </Col>
        }
        {
          this.state.contest_promote_model &&
          <PromoteContestModal IsPromoteContestShow={this.state.contest_promote_model} IsPromoteContestHide={this.PromoteContestHide} ContestData={{ contestObj: contestObj }} />}

        {
          this.state.promote_model &&
          <PromoteNotActive IsPromoteShow={this.state.promote_model} IsPromoteHide={this.PromoteHide} />}
      </div>
    );
  }
}

export default SP_ContestList;