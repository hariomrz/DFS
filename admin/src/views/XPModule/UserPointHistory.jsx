import React, { Component } from "react";
import { Row, Col, Table } from "reactstrap";
import * as NC from "../../helper/NetworkingConstants";
import { notify } from "react-notify-toast";
import Pagination from "react-js-pagination";
import HF from "../../helper/HelperFunction";
import { xpGetUserHistory } from "../../helper/WSCalling";
import { _Map, _isNull } from "../../helper/HelperFunction";
import Loader from "../../components/Loader";
import { Base64 } from "js-base64";
import moment from "moment";
import { number } from "prop-types";
class UserPointHistory extends Component {
  constructor(props) {
    super(props);
    this.state = {
      PERPAGE: NC.ITEMS_PERPAGE,
      CURRENT_PAGE: 1,
      UserList: [],
      sortField: "level_number",
      isDescOrder: "true",
      ListPosting: false,
      TotalXpPoint: 0,
      UserId: this.props.match.params.uid
        ? Base64.decode(this.props.match.params.uid)
        : ""
    };
  }
  componentDidMount() {
    if (HF.allowXpPoints() == "0") {
      notify.show(NC.MODULE_NOT_ENABLE, "error", 5000);
      this.props.history.push("/dashboard");
    }
    this.getUserList();
  }

  sortByColumn(sortfiled, isDescOrder) {
    let Order = isDescOrder ? false : true;
    this.setState(
      {
        sortField: sortfiled,
        isDescOrder: Order,
        CURRENT_PAGE: 1
      },
      this.getUserList
    );
  }

  getUserList = () => {
    this.setState({ ListPosting: true });
    const {
      PERPAGE,
      CURRENT_PAGE,
      isDescOrder,
      sortField,
      UserId
    } = this.state;
    let params = {
      user_id: UserId,
      items_perpage: PERPAGE,
      current_page: CURRENT_PAGE,
      sort_order: isDescOrder ? "DESC" : "ASC",
      sort_field: sortField
    };

    xpGetUserHistory(params)
      .then(ResponseJson => {
        if (ResponseJson.response_code == NC.successCode) {
          this.setState({
            UserList: ResponseJson.data ? ResponseJson.data.result : [],
            Total: ResponseJson.data ? ResponseJson.data.total : 0,
            TotalXpPoint: ResponseJson.data ? ResponseJson.data.total_point : 0,
            ListPosting: false
          });
        } else {
          notify.show(NC.SYSTEM_ERROR, "error", 3000);
        }
      })
      .catch(error => {
        notify.show(NC.SYSTEM_ERROR, "error", 3000);
      });
  };

  handlePageChange(current_page) {
    if (current_page !== this.state.CURRENT_PAGE) {
      this.setState(
        {
          CURRENT_PAGE: current_page,
          ListPosting: true,
          Total: 0
        },
        () => {
          this.getUserList();
        }
      );
    }
  }

  geTotalXp = () => {
    let tot = 0;
    if (this.state.UserList) {
      tot = this.state.UserList.reduce(function(cnt, o) {
        return cnt + parseInt(o.point);
      }, 0);
    }

    return tot;
  };

  render() {
    let {
      UserList,
      CURRENT_PAGE,
      PERPAGE,
      Total,
      isDescOrder,
      sortField,
      ListPosting,
      TotalXpPoint,
    } = this.state;

    return (
      <div className="leaderboard-level animated fadeIn">
        <Row>
          <Col md={12}>
            <div className="t-u-point">
              <span>Total XP Points -</span>
              <span>
                {TotalXpPoint ? (
                  HF.getNumberWithCommas(TotalXpPoint)
                ) : (
                  <Loader hide />
                )}
              </span>
            </div>
          </Col>
        </Row>
        <Row>
          <Col md={12} className="table-responsive common-table mt-3">
            <Table>
              <thead>
                <tr className="height-40">
                  <th
                    onClick={() => this.sortByColumn("added_date", isDescOrder)}
                  >
                    Date
                    <div
                      className={`d-inline-block ${
                        sortField === "added_date" && isDescOrder
                          ? ""
                          : "rotate-icon"
                      }`}
                    >
                      <i className="icon-Shape ml-1"></i>
                    </div>
                  </th>
                  <th
                  className="cursor-default"
                    // onClick={() => this.sortByColumn("added_date", isDescOrder)}
                  >
                    Activity Name
                    {/* <div
                      className={`d-inline-block ${
                        sortField === "added_date" && isDescOrder
                          ? ""
                          : "rotate-icon"
                      }`}
                    >
                      <i className="icon-Shape ml-1"></i>
                    </div> */}
                  </th>
                  <th onClick={() => this.sortByColumn("point", isDescOrder)}>
                    XP Points
                    <div
                      className={`d-inline-block ${
                        sortField === "point" && isDescOrder
                          ? ""
                          : "rotate-icon"
                      }`}
                    >
                      <i className="icon-Shape ml-1"></i>
                    </div>
                  </th>
                </tr>
              </thead>
              {Total > 0 ? (
                _Map(UserList, (item, idx) => {
                  return (
                    <tbody key={idx}>
                      <tr>
                        <td>{moment(item.added_date).format("DD MMM YYYY")}</td>
                        <td>{item.activity_title}</td>
                        <td>{item.point}</td>
                      </tr>
                    </tbody>
                  );
                })
              ) : (
                <tbody>
                  <tr>
                    <td colSpan="8">
                      {Total == 0 && !ListPosting ? (
                        <div className="no-records">{NC.NO_RECORDS}</div>
                      ) : (
                        <Loader />
                      )}
                    </td>
                  </tr>
                </tbody>
              )}
            </Table>
          </Col>
        </Row>
        {Total > PERPAGE && (
          <div className="custom-pagination lobby-paging">
            <Pagination
              activePage={CURRENT_PAGE}
              itemsCountPerPage={PERPAGE}
              totalItemsCount={Total}
              pageRangeDisplayed={5}
              onChange={e => this.handlePageChange(e)}
            />
          </div>
        )}
      </div>
    );
  }
}
export default UserPointHistory;
