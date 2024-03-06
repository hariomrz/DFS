import React, { Component, Fragment } from 'react';
import { Row, Col, Table } from 'reactstrap';
import _ from 'lodash';
import Pagination from "react-js-pagination";
import * as NC from "../../helper/NetworkingConstants";
class joinedUserList extends Component {
    constructor(props) {
        super(props);
        this.state = {
            HOME_CURRENT_PAGE: 1,
            AWAY_CURRENT_PAGE: 1,
            PERPAGE: NC.ITEMS_PERPAGE,
            PERPAGE: 4,
            Total: 20
        };
    }

    handlePageChange(current_page, match_type) {
        if (match_type == 1)
            this.setState({ HOME_CURRENT_PAGE: current_page });
        else if (match_type == 2)
            this.setState({ AWAY_CURRENT_PAGE: current_page });
    }

    render() {
        let { HOME_CURRENT_PAGE, AWAY_CURRENT_PAGE, PERPAGE, Total } = this.state
        return (
            <Fragment>
                <div className="joineduser-list">
                    <Row>
                        <Col md={12}>
                            <h2 className="h2-cls">MUN vs LIV, 21 Feb 2020 </h2>
                            <div className="t-user-subhead">Total : 956 Users Joined</div>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={6}>
                            <div className="u-joined-count">MUN (500 Users Joined)</div>
                            <div className="table-responsive common-table">
                                <div className="tbl-min-hgt">
                                    <Table>
                                        <thead>
                                            <tr>
                                                <th>S.No.</th>
                                                <th>Users</th>
                                                <th>Team</th>
                                            </tr>
                                        </thead>
                                        {
                                            // TotalHistory > 0 ?
                                            _.times(14, (item, idx) => {
                                                return (
                                                    <tbody key={idx}>
                                                        <tr>
                                                            <td>{item + 1}</td>
                                                            <td>lucy.duncan</td>
                                                            <td>MUN</td>
                                                        </tr>
                                                    </tbody>
                                                )
                                            })
                                            // :
                                            // <tbody>
                                            //     <tr>
                                            //         <td colSpan="8">
                                            //             {(TotalHistory == 0 && !HistoryPosting) ?
                                            //                 <div className="no-records">
                                            //                     {NC.NO_RECORDS}</div>
                                            //                 :
                                            //                 <Loader />
                                            //             }
                                            //         </td>
                                            //     </tr>
                                            // </tbody>
                                        }
                                    </Table>
                                </div>
                                {/* {TotalHistory > 0 && ( */}
                                <div className="custom-pagination float-right">
                                    <Pagination
                                        activePage={HOME_CURRENT_PAGE}
                                        itemsCountPerPage={PERPAGE}
                                        totalItemsCount={Total}
                                        pageRangeDisplayed={5}
                                        onChange={e => this.handlePageChange(e, 1)}
                                    />
                                </div>
                                {/* )
            } */}
                            </div>
                        </Col>
                        <Col md={6}>
                            <div className="u-joined-count">LIV (456 Users Joined)</div>
                            <div className="table-responsive common-table">
                                <div className="tbl-min-hgt">
                                    <Table>
                                        <thead>
                                            <tr>
                                                <th>S.No.</th>
                                                <th>Users</th>
                                                <th>Team</th>
                                            </tr>
                                        </thead>
                                        {
                                            // TotalHistory > 0 ?
                                            _.times(14, (item, idx) => {
                                                return (
                                                    <tbody key={idx}>
                                                        <tr>
                                                            <td>{item + 1}</td>
                                                            <td>lucy.duncan</td>
                                                            <td>MUN</td>
                                                        </tr>
                                                    </tbody>
                                                )
                                            })
                                            // :
                                            // <tbody>
                                            //     <tr>
                                            //         <td colSpan="8">
                                            //             {(TotalHistory == 0 && !HistoryPosting) ?
                                            //                 <div className="no-records">
                                            //                     {NC.NO_RECORDS}</div>
                                            //                 :
                                            //                 <Loader />
                                            //             }
                                            //         </td>
                                            //     </tr>
                                            // </tbody>
                                        }
                                    </Table>
                                </div>
                                {/* {TotalHistory > 0 && ( */}
                                <div className="custom-pagination float-right">
                                    <Pagination
                                        activePage={AWAY_CURRENT_PAGE}
                                        itemsCountPerPage={PERPAGE}
                                        totalItemsCount={Total}
                                        pageRangeDisplayed={5}
                                        onChange={e => this.handlePageChange(e, 2)}
                                    />
                                </div>
                                {/* )
            } */}
                            </div>
                        </Col>
                    </Row>
                </div>
            </Fragment>
        )
    }
}
export default joinedUserList