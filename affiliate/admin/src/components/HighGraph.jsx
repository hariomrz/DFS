import React, { Component } from 'react';
import NumberFormat from 'react-number-format';

import { Badge, ButtonDropdown, Card, CardBody, CardFooter, CardHeader, Col,
  DropdownItem, DropdownMenu, DropdownToggle,
  Row, Collapse, Fade ,
  Form,
  FormGroup,
  FormText,
  FormFeedback,
  Input,
  InputGroup,
  InputGroupAddon,
  InputGroupText,
  Button,ButtonGroup,
  Label,
  Pagination, PaginationItem, PaginationLink, Table,TableComponent,
  Modal, ModalBody, ModalFooter, ModalHeader,Media,
} from 'reactstrap';
import ReactDOM from 'react-dom';
import Highcharts from 'highcharts'
import HighchartsReact from 'highcharts-react-official'

  

export default class HighGraph extends Component {
	
	onDepositRadioBtnClick(rSelected) { 
		//this.setState({ filtertype : rSelected },()=>{ this.getTimelines(); });
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
					{ HighGraphConfigOption.LineData.map((linedata, index) => (
						         
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
