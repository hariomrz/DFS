import React, { Component } from 'react';
import { Col, Container, Row } from 'reactstrap';

class Page401 extends Component {
  render() {
    return (
      <div className="app flex-row align-items-center">
        <Container>
          <Row className="justify-content-center">
            <Col md="6">
              <div className="clearfix">
                <h1 className="float-left display-3 mr-4">401</h1>
                <h4 className="pt-3">Oops! You're lost.</h4>
                <p className="text-muted float-left">You are unauthorized to access the page.</p>
              </div>
            </Col>
          </Row>
        </Container>
      </div>
    );
  }
}

export default Page401;
