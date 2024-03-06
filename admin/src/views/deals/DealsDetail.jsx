import React, { Component, Fragment } from "react";
import { Row, Col, Table, Modal, ModalBody } from 'reactstrap';
import _ from 'lodash';
import Pagination from "react-js-pagination";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import * as NC from "../../helper/NetworkingConstants";
import "react-datepicker/dist/react-datepicker.css";
import Images from '../../components/images';
import HF, { _isNull } from '../../helper/HelperFunction';
import { MomentDateComponent } from "../../components/CustomComponent";

export default class DealsDetail extends Component {
  constructor(props) {
    super(props)
    this.state = {
      Total: 0,
      PERPAGE: 20,
      CURRENT_PAGE: 1,
      deal_unique_id: this.props.match.params.deal_unique_id,
      total_count: [],
      deal_detail: [],
      user_deal_data: []

    }
  }
  componentDidMount() {
    this.getDealsDetail()
  }


  getDealsDetail = () => {
    const { PERPAGE, CURRENT_PAGE } = this.state
    let params = {
      deal_unique_id: this.state.deal_unique_id,
      items_perpage: PERPAGE,
      total_items: 0,
      current_page: CURRENT_PAGE,
    }
    WSManager.Rest(NC.baseURL + NC.DEALS_DETAILS, params).then(ResponseJson => {
 
      if (ResponseJson.response_code == NC.successCode) {
        this.setState({
          total_count: ResponseJson.data.total_count,
          deal_detail: ResponseJson.data.deal_detail,
          user_deal_data: ResponseJson.data.user_deal_data
        }, () => {
          // this.getTotMerchandiseDist()
          // this.getGameLinupDetail(this.state.gameDetail)
        })
      } else {
        notify.show(NC.SYSTEM_ERROR, "error", 3000)
      }
    }).catch(error => {
      notify.show(NC.SYSTEM_ERROR, "error", 3000)
    })
  }

  handlePageChange(current_page) {
    this.setState({
      CURRENT_PAGE: current_page
    }, () => {
      this.getDealsDetail();
    });
  }

  render() {
    const { total_count, deal_detail, user_deal_data, PERPAGE, CURRENT_PAGE } = this.state
    return (
      <Fragment>
        <div className="contest-d-main">
          <Row className="mt-3 mb-3">
            <Col md={8}>
              <h1 className="h1-cls">Deals Detail Informations</h1>
            </Col>
            <Col md={4}>
              <div className="back-dashboard-text" onClick={() => this.props.history.push('/deals/deal_list')}>{'<'} Back to Deals</div>
            </Col>
          </Row>
          <div className="details-box">
            <Row className="box-items mt-3">
              <Col md={3}>
                <label>Deal Amount</label>
                <div className="user-value">
                {HF.getCurrencyCode()}{deal_detail.amount}
                </div>
              </Col>
              <Col md={3}>
                <label>Real</label>
                <div className="user-value">
                {HF.getCurrencyCode()}{deal_detail.cash}
                </div>
              </Col>
              <Col md={3}>
                <label>Bonus</label>
                <div className="user-value">
                  <i className="icon-bonus icon-ic-view"/>{deal_detail.bonus}
                </div></Col>

                
                {HF.allowCoin() == '1' &&  <Col md={3}>
                <label>Coins </label>
                <div className="user-value">
                <img src={Images.COINIMG} alt="coin-img" />{deal_detail.coin} 
                </div>
              </Col>}
              
             
            </Row>
            <Row className="box-items mt-3">
            <Col md={3}>
                <label> Date Created</label>
                <div className="user-value">
                  {/* <MomentDateComponent data={{ date: deal_detail.added_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                  {HF.getFormatedDateTime(deal_detail.added_date, 'D-MMM-YYYY hh:mm A')}
                </div>
              </Col>
              <Col md={3}>
                <label>Status</label>
                <div className="user-value">
                  {deal_detail.status == 0 && "Inactive"}
                  {deal_detail.status == 1 && "Active"}
                </div>
              </Col>
            </Row>
          </div>

          <div className="deal-user-view">
            <div className="h1-cls ">Deal Users</div>
            <div className="h1-cls ">Total Count : {total_count} </div>
          </div>

          <Row>
            <Col md={12} className="table-responsive common-table">
              <Table>
                <thead>
                  <tr>
                    <th>Username</th>
                    <th>Amount Added</th>
                    <th> Deal Benefit</th>
                    <th>Actual Paid</th>
                    <th>Used On</th>

                  </tr>
                </thead>
                {user_deal_data.length > 0 &&
                  <>
                    {
                      _.map(user_deal_data, (item, idx) => {
                        return (
                          <tbody key={idx}>
                            <tr>
                              <td>{item.user_name}</td>
                              <td>{HF.getCurrencyCode()}{item.amount_added}</td>
                              <td>{HF.allowCoin() == '1' && <> <img src={Images.COINIMG} alt="coin-img" />{item.coin} / </>}<i className="icon-bonus icon-ic-view"/>{item.bonus} / {HF.getCurrencyCode()}{item.cash}</td>
                              <td>{HF.getCurrencyCode()}{item.actual_paid}</td>
                              <td> 
                                {/* <MomentDateComponent data={{ date: item.added_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                                {HF.getFormatedDateTime(item.added_date, "D-MMM-YYYY hh:mm A")}
                                </td>
                            </tr>
                          </tbody>
                        )
                      })
                    }
                  </>}
                {user_deal_data == "" &&

                  <tbody>
                    <tr>
                      <td colSpan="12">
                        <div className="no-records">No Details Found.</div>
                      </td>
                    </tr>
                  </tbody>
                }
              </Table>
            </Col>
          </Row>
          {total_count > PERPAGE &&
            <div className="custom-pagination userlistpage-paging float-right mb-5">
              <Pagination
                activePage={CURRENT_PAGE}
                itemsCountPerPage={PERPAGE}
                totalItemsCount={total_count}
                pageRangeDisplayed={5}
                onChange={e => this.handlePageChange(e)}
              />
            </div>
          }
        </div>

      </Fragment>
    )
  }
}
