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
import HighchartsReact from 'highcharts-react-official'
import { HighchartsChart, withHighcharts, Title, FunnelSeries } from 'react-jsx-highcharts';

  

import Highcharts from 'highcharts';
import addFunnelModule from 'highcharts/modules/funnel';

// Apply Funnel Module
addFunnelModule(Highcharts);

const plotOptions = {
  series: {
    dataLabels: {
      enabled: true,
      format: '<b>{point.name}</b> ({point.y:,.0f})',
      softConnector: false
    },
    center: ['40%', '50%'],
    neckWidth: '30%',
    neckHeight: '0%',
	width: '80%',
	defaultSeriesType:'line'
  }
};

const funnelData = [
  ['Website visits', 15654],
  ['Downloads', 4064],
  ['Requested price list', 1987],
  ['Invoice sent', 976],
  ['Finalized', 846]
];

const Funnel = () => (
  <div className="app">
    <HighchartsChart plotOptions={plotOptions}>
      <Title>Sales funnel</Title>

      <FunnelSeries id="unique-users" name="Unique users" data={funnelData} />
    </HighchartsChart>

    <div name="Funnel"></div>
  </div>
);

export default withHighcharts(Funnel, Highcharts);