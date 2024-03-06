import React, { Component } from 'react';
import NumberFormat from 'react-number-format';
import { Col, Row, } from 'reactstrap';
import Highcharts from 'highcharts'
import HighchartsReact from 'highcharts-react-official'
export default class HighGraph extends Component {	
	onDepositRadioBtnClick(rSelected) { 
		this.setState({ 
			Dashboard: [...this.state.Dashboard, { filtertype : rSelected}] });
	}
	onSiteRakeRadioBtnClick(rSelected) { 
	this.setState({ filtertype : rSelected },()=>{ this.getTimelines(); });
	}
	onSiteFreePaidBtnClick(rSelected) { 
	this.setState({ filtertype : rSelected },()=>{ this.getTimelines(); });
	}
	onSiteFreePaidBtnClick(rSelected) { 
	this.setState({ filtertype : rSelected },()=>{ this.getTimelines(); });
	}
    render() 
    {  const { HighGraphConfigOption} = this.props;	
       return (
       		   <div>
       		        <HighchartsReact highcharts={Highcharts}  options={HighGraphConfigOption} />
					
					<Row className="sh">
					{HighGraphConfigOption.LineData && HighGraphConfigOption.LineData.map((linedata, index) => (
						         
					        <Col sm={6} className="legend-col" key={index}> 
							<div className="legend-counts">
									<div className="" >{linedata.title}</div>
									<div className="amount valuecolor">
										<NumberFormat value={linedata.value} displayType={'text'} thousandSeparator={true} prefix={''} />
									</div>

							</div>
							</Col>
						    ))
				    }
					</Row>
				</div>  
       	)
  	} 
}
