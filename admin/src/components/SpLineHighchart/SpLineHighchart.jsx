import React from "react";
import Highcharts from 'highcharts'
import HighchartsReact from 'highcharts-react-official'
function SpLineHighchart(props) {
    return (
        <HighchartsReact
            containerProps={props.style}
            highcharts={Highcharts}
            options={props.data}
        />
    );
}

export default SpLineHighchart;