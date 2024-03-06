import React, { Component, Fragment } from "react";
import { Row, Col } from "reactstrap";
import Highcharts from 'highcharts'
import HighchartsReact from 'highcharts-react-official'
export default class GamestatsGraph extends Component {
    constructor(props) {
        super(props)
        this.state = {}
    }
    render() {
        const { TotalContestGraph, FreeGraphOption, SportPreferencesGraph } = this.props
        return (
            <Fragment>
                <Col md={12} className="mt-3">
                    <Row>
                        <h3 className="h3-cls">Game Stats</h3>
                    </Row>
                </Col>
                <Col md={12} className="stats-graph">
                    <Row>
                        <Col md={4}>
                            <div className="graph-title">Total Contest</div>
                            {TotalContestGraph &&
                                <HighchartsReact
                                    highcharts={Highcharts}
                                    options={TotalContestGraph}
                                />
                            }
                        </Col>
                        <Col md={4}>
                            <div className="graph-title">Free vs Paid</div>
                            {FreeGraphOption && 
                            <HighchartsReact
                                highcharts={Highcharts}
                                options={FreeGraphOption}
                            />}
                        </Col>
                        <Col md={4}>
                            <div className="graph-title">Sport Preferences</div>
                            {SportPreferencesGraph &&<HighchartsReact
                                highcharts={Highcharts}
                                options={SportPreferencesGraph}
                            />}
                        </Col>
                    </Row>
                </Col>
            </Fragment>
        )
    }
}
