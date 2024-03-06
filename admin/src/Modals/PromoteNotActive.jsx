import React from 'react';
import { Modal, ModalHeader, ModalBody, Col, Row } from 'reactstrap';
import _ from 'lodash';
export default class PromoteNotActive extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      promote_model: this.props.IsPromoteShow || false,
    };
  }

  togglePromoteModal = (key, val) => {
    this.setState({
      promote_model: !this.state.promote_model,
    });
    this.props.IsPromoteHide();
  }

  render() {

    const { IsPromoteHide } = this.props;

    return (
      <Modal isOpen={this.state.promote_model} toggle={this.togglePromoteModal} className={this.props.className}
      >
        <ModalHeader toggle={IsPromoteHide} className="resend">
          <Col lg={12}>
            <h5 className="resend title"> Oops! You do not have access to the state-of-art communication module</h5></Col>
        </ModalHeader>
        <ModalBody>
          {
            <Row className="usertime">

              <Col lg={12} className="resendvs">
                Please contact the admin at VSports Fantasy if you wish to enable it on your platform.
                        </Col>
            </Row>
          }
        </ModalBody>
      </Modal>
    );
  }
}