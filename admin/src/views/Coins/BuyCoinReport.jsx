import React, { Fragment } from "react";
import { Row, Col, Table, Input } from 'reactstrap';
import _ from 'lodash';
import Loader from '../../components/Loader';
import * as NC from '../../helper/NetworkingConstants';
import Images from "../../components/images";
import { withRouter } from 'react-router'
import HF from '../../helper/HelperFunction';
var createReactClass = require('create-react-class');
var BuyCoinReport = createReactClass({
    render() {
        let { RewardList, Total, RewardListPosting, isDescOrder, sortField } = this.props
        return (
            <Fragment>
                <Row>
                    <div className="search-input">
                        <Input
                            name="search-user"
                            id="search-user"
                            className="search-input"
                            placeholder="Search by Title"
                            onChange={e => this.props.handleSearch(e)}
                        />
                    </div>
                </Row>
                <Row>
                    <Col md={12}>
                        <div className="table-responsive common-table">
                            <div className="tbl-min-hgt">
                                <Table>
                                    <thead>
                                        <tr>
                                            <th onClick={() => this.props.sortByColumn('package_name', isDescOrder)}>
                                                Title
                                                <div className={`d-inline-block ${(sortField === 'package_name' && isDescOrder) ? '' : 'rotate-icon'}`}>
                                                    <i className="icon-Shape"></i>
                                                </div>
                                            </th>
                                            <th onClick={() => this.props.sortByColumn('amount', isDescOrder)}>
                                                Price
                                                <div className={`d-inline-block ${(sortField === 'amount' && isDescOrder) ? '' : 'rotate-icon'}`}>
                                                    <i className="icon-Shape"></i>
                                                </div>
                                            </th>
                                            <th onClick={() => this.props.sortByColumn('coins', isDescOrder)}>
                                                Coins
                                                <div className={`d-inline-block ${(sortField === 'coins' && isDescOrder) ? '' : 'rotate-icon'}`}>
                                                    <i className="icon-Shape"></i>
                                                </div>
                                            </th>
                                            <th>Total Purchase</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    {
                                        Total > 0 ?
                                            _.map(RewardList, (item, idx) => {
                                                return (
                                                    <tbody key={idx}>
                                                        <tr>
                                                            <td className="xtext-ellipsis">{item.package_name}</td>
                                                            <td>
                                                                <span className="icon-rupess"></span>
                                                                {HF.getNumberWithCommas(item.amount)}
                                                            </td>
                                                            <td>
                                                                <img className="coin-sty" src={Images.COINS_IMG} alt="" />
                                                                {HF.getNumberWithCommas(item.coins)}
                                                            </td>
                                                            <td className="pl-5">
                                                                {item.reddem_users}
                                                            </td>
                                                            <td>
                                                                <a
                                                                    onClick={() => this.props.history.push('/coins/buy-coins-report/' + item.coin_package_id)}
                                                                    className="bc-detail-view">
                                                                    Detail View
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                )
                                            })
                                            :
                                            <tbody>
                                                <tr>
                                                    <td colSpan="8">
                                                        {(Total == 0 && !RewardListPosting) ?
                                                            <div className="no-records">
                                                                {NC.NO_RECORDS}</div>
                                                            :
                                                            <Loader />
                                                        }
                                                    </td>
                                                </tr>
                                            </tbody>
                                    }
                                </Table>
                            </div>
                        </div>
                    </Col>
                </Row>
            </Fragment>
        )
    }
})

export default withRouter(BuyCoinReport)
