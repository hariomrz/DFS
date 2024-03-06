import React, { Component } from "react";
import Highcharts from 'highcharts'
import HighchartsReact from 'highcharts-react-official'

export default class AnalyticsBarChart extends Component {
    constructor(props) {
        super(props)
        this.state = {}
    }
    render() {
        let { graph_data, graph_height, graph_width } = this.props
        return (
            <HighchartsReact
                containerProps={{ style: { height: graph_height, width: graph_width } }}
                highcharts={Highcharts}
                options={graph_data}
            />
        )
    }
}