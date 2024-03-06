import React, { Component } from "react";
import { Row, Col } from "reactstrap";
import Coins from '../UserManagement/Profile/Coins/Coins';
import TopEarner from './TopEarner';
class CoinsDashboard extends Component {
    constructor(props) {
        super(props)
    }

    render() {
        let EarnerProps = {
            FromDashboard: false,
            viewType: 'topearner',
            ...this.props
        }
        let RedeemProps = {
            FromDashboard: false,
            viewType: 'topredeemer',
            ...this.props
        }
        return (
            <React.Fragment>
                {!this.props.FromDashboard && (
                <Row>
                    <Col md={12} className="mt-4">
                            <h2 className="h2-cls float-left">Coins Dashboard</h2>
                        <div className="coins-setting-box float-right">
                                <i onClick={() => this.props.history.push('/coins/setting')} className="icon-setting pointer"></i>
                        </div>
                    </Col>
                </Row>
                )}  
                <Coins FromDashboard={this.props.FromDashboard}/>
                {!this.props.FromDashboard && (<Row>
                    <Col md={6}>
                        <TopEarner {...EarnerProps} />
                    </Col>
                    <Col md={6}>
                        <TopEarner {...RedeemProps} />
                    </Col>
                </Row>)}              
            </React.Fragment>
        )
    }
}
export default CoinsDashboard