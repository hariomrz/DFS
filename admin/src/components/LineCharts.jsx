import React, { Component } from "react";
import { Row, Col, Tooltip } from "reactstrap";
import * as NC from "../helper/NetworkingConstants";
import { notify } from 'react-notify-toast';
import WSManager from "../helper/WSManager";
import LS from 'local-storage';
import Highcharts from 'highcharts'
import HighchartsReact from 'highcharts-react-official'
import HF from '../helper/HelperFunction';
import moment from "moment";
import "./LineCharts.scss";
class PredectionGraph extends Component {
    constructor(props) {
        super(props)
        this.state = {
            // toggleToolTip: false
        }
    }

    CoinUserGraphToggle = () => {
        this.setState({
            toggleToolTip: !this.state.toggleToolTip
        });
    }

    render() {
        let { GraphData, Title } = this.props
        return (
            <React.Fragment>
                <div className="line-charts-box line-high-grp">
                    <Row className="mb-30">
                        <Col sm={9}>
                            <div className="grp-heading">{Title}</div>
                        </Col>
                        <Col sm={3}>
                            {/* <div className="info-icon-wrapper text-right">
                                <i className="icon-info" id="coin_vs_graph">
                                    <Tooltip placement="top" isOpen={this.state.toggleToolTip} target="coin_vs_graph" toggle={this.CoinUserGraphToggle}>{Title}</Tooltip>
                                </i>
                            </div> */}
                        </Col>
                    </Row>
                    <HighchartsReact
                        highcharts={Highcharts}
                        options={GraphData}
                    />
                    {
                        GraphData &&
                        <Row className="graph-footer">
                            {GraphData.LineData.map((linedata, index) => (
                                <Col sm={6} key={index}>
                                    <div className={`legend-counts ${index == 1 ? "float-right" : ""}`}>
                                        <div className="legend-lable" >{linedata.title}</div>
                                        <div className="amount">
                                            {HF.getNumberWithCommas(linedata.value)}
                                        </div>
                                    </div>
                                </Col>
                            ))
                            }
                        </Row>
                    }
                </div>
            </React.Fragment >
        )
    }
}
export default PredectionGraph