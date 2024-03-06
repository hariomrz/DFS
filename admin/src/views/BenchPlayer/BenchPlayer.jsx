import React, { Component, Fragment } from "react";
import HF, { _isUndefined, _isEmpty, _Map } from "../../helper/HelperFunction";
import { Row, Col, Table } from 'reactstrap';
class BenchPlayer extends Component {
    constructor(props) {
        super(props)
        this.state = {}
    }
    render() {
        let { data } = this.props
        return (
            <Fragment>
                <div className="bench-player">
                    {
                        !_isEmpty(data) &&
                        <Fragment>
                            <Row className="bench-heading">
                                <Col md={12}>
                                    <h2 className="h2-cls">Bench player</h2>
                                </Col>
                            </Row>
                            <div className="table-responsive common-table">
                                <Table>
                                    <thead>
                                        <tr>
                                            <th>Position</th>
                                            <th>Player Name</th>
                                            <th>Team Name</th>
                                        </tr>
                                    </thead>
                                    {
                                        data.length > 0 ?
                                            _Map(data, (item, idx) => {
                                                return (
                                                    <tbody key={idx}>
                                                        <tr>
                                                            <td className="pl-4">{item.position}</td>
                                                            <td>
                                                                {item.full_name}
                                                                {
                                                                    (item.status == '1') &&
                                                                    <span className="bench-out">Sub Out</span>
                                                                }
                                                                {
                                                                    (item.status == '2') &&
                                                                    <span className="bench-failed">{item.reason}</span>
                                                                }
                                                            </td>
                                                            <td>{item.team_abbr}</td>
                                                        </tr>
                                                    </tbody>
                                                )
                                            })
                                            :
                                            <tbody>
                                                <tr>
                                                    <td colSpan='3'>
                                                        <div className="no-records p-3">No Bench Player</div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                    }
                                </Table>
                            </div>
                        </Fragment>
                    }
                </div>
            </Fragment>
        )
    }
}
export default BenchPlayer

