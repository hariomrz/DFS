import React, { Component, Fragment } from "react";
import Images from '../../components/images';
import { Button, Row, Col } from 'reactstrap';
import { Base64 } from 'js-base64';
import { withRouter } from 'react-router'
class Affiliate extends Component {
    constructor(props) {
        super(props)
    }

    render() {
        return (
            <Fragment>
                <div className="affiliate-wrapper">
                    <Row>
                        <Col md={12}>
                            <div className="float-left">
                                <h2 className="h2-cls mt-2">Affiliates</h2>
                            </div>
                            <Button className="btn-secondary-outline aff-btn" onClick={() => this.props.history.push("/add-affiliate/" + Base64.encode('0'))}>Add New</Button>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <div className="no-affi">
                                <img src={Images.NO_AFFILIATE} alt="" className="img-cover" />
                                <div className="xfloat-left">
                                    <h2 className="h2-cls mt-56">No Affiliates Found</h2>
                                </div>
                            </div>
                        </Col>
                    </Row>
                </div>
            </Fragment>
        )
    }
}
export default withRouter(Affiliate)