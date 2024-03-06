import React, { Component } from "react";
import Highcharts from 'highcharts'
import HighchartsReact from 'highcharts-react-official'

export default class LineHighchart extends Component {
    render() {
        let { GraphData } = this.props
        return (
            <HighchartsReact
                highcharts={Highcharts}
                options={GraphData}
            />
        )
    }
}