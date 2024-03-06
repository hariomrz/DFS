import React, { Component, Fragment } from "react";
import { Row, Col } from 'reactstrap';
import Images from '../../components/images';
import { _isUndefined, _isEmpty } from "../../helper/HelperFunction";
import * as NC from "../../helper/NetworkingConstants";
class BoosterShow extends Component {
    constructor(props) {
        super(props)
        this.state = {}
    }
    render() {
        let { data } = this.props
        
        return (
            <Fragment>
                <div className="lnup-booster">
                    {
                        !_isEmpty(data) &&
                        <Fragment>
                            <Row className="apply-booster">
                                <Col md={12}>
                                    <h2 className="h2-cls">Applied Boosters</h2>
                                </Col>
                            </Row>
                            <ul className="bstr-list">
                                <li className="bstr-itm">
                                    <div className="b-icon">
                                        <img
                                            src={data.image_name ? NC.S3 + NC.BOOSTER + data.image_name : Images.no_image}
                                            className="img-cover" alt=""
                                        />
                                    </div>
                                </li>
                                <li className="bstr-itm">
                                        <div className="bstr-type">{data.name}</div>
                                        <div className="bstr-pos">{data.position}</div>
                                </li>
                                <li className="bstr-itm">
                                        <div className="bstr-points">+{data.score}</div>
                                    <div className="bstr-pos">Points added</div>
                                </li>
                            </ul>
                        </Fragment>
                    }
                </div>
            </Fragment>
        )
    }
}
export default BoosterShow

