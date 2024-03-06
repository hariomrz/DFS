import React from "react";
import Highcharts from 'highcharts'
import HighchartsReact from 'highcharts-react-official'
function ReactHighchart(props) {
    return (
        <HighchartsReact
            containerProps={props.style}
            highcharts={Highcharts}
            options={props.data}
        />
    );
}

export default ReactHighchart;
