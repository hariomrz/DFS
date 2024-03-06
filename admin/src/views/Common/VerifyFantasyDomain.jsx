import React, { Component } from 'react';
import { Col, Container, CardBody, Row } from 'reactstrap';
import 'spinkit/css/spinkit.css';

 class VerifyFantasyDomain extends Component {
  constructor(props){
    super(props)
    this.state = {
      encoded_auth_key: this.props.match.params.encoded_auth_key
    }    
    var auth_key = atob(atob(this.props.match.params.encoded_auth_key))
    localStorage.setItem('admin_id_token', auth_key);
    this.props.history.push('/twitter_feed');
  }


  render() {
    return (
      <div className="app flex-row align-items-center">
        <Container>
          <Row className="justify-content-center">
            <Col md="6">
              <CardBody>
              <div className="sk-wandering-cubes">
                  <div className="sk-cube sk-cube1"></div>
                  <div className="sk-cube sk-cube2"></div>
                </div>
              </CardBody>
            </Col>
          </Row>
        </Container>
      </div>
    );
  }
}
export default VerifyFantasyDomain;
