import React, { Component, Fragment } from "react";
import Images from '../../components/images';
import { Button, Row, Col } from 'reactstrap';
import WSManager from '../../helper/WSManager';
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
class PredictionType extends Component {
    constructor(props) {
        super(props)
        this.state = {
            ModuleSetting: "",
        }
    }

    componentDidMount() {
        this.getPredictionModule()
    }

    getPredictionModule = () => {
        WSManager.Rest(NC.baseURL + NC.GET_PREDICTION_STATUS, {}).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({ ModuleSetting: Response.data.allow_prediction })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    updatePredictionModule = () => {
        let { ModuleSetting } = this.state
        let params = {
            status: ModuleSetting == 1 ? 0 : 1            
        }
        WSManager.Rest(NC.baseURL + NC.UPDATE_PREDICTION_STATUS, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                
                if (ModuleSetting == 0)
                this.props.history.push('/prediction/fixture')
                notify.show(Response.global_error, 'success', 5000)
                this.setState({ ModuleSetting: ModuleSetting == 1 ? 0 : 1 })
                WSManager.setKeyValueInLocal('ALLOW_PREDICTION_MODULE', 1);
                setTimeout(() => {
                    window.location.reload()
                }, 1000);
                
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    render() {
        let { ModuleSetting} = this.state
        return (
            <Fragment>
                <div className="prediction-module">
                    <Row>
                        <Col md={12}>
                            <div className="pre-heading text-center">Prediction Module Benefits</div>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <div className="container">
                                <ul className="prediction-list">
                                    <li className="prediction-item float-left">
                                        <figure className="pre-img-container pr-20">
                                            <img src={Images.PREDICTION_1} alt="" />
                                        </figure>
                                        <div className="pre-info-box text-left">
                                            <div className="pre-title">Game Type For All Users</div>
                                            <div className="pre-sub-title">No fantasy skills required, just predict the outcome & win coins</div>
                                        </div>
                                    </li>
                                    <li className="prediction-item float-right">
                                        <div className="pre-info-box text-right">
                                            <div className="pre-title">Live Match Engagement</div>
                                            <div className="pre-sub-title">Exciting questions during live match, increase user engagement during live match</div>
                                        </div>
                                        <figure className="pre-img-container pl-20">
                                            <img src={Images.PREDICTION_1} alt="" className="img-cover" />
                                        </figure>
                                    </li>
                                    <li className="prediction-item float-left">
                                        <figure className="pre-img-container pr-20">
                                            <img src={Images.PREDICTION_1} alt="" />
                                        </figure>
                                        <div className="pre-info-box text-left">
                                            <div className="pre-title">Store For Users</div>
                                            <div className="pre-sub-title">Coins can be redeemed in real cash, merchandise, vouchers etc.</div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <div className="pre-btn">
                                <Button onClick={this.updatePredictionModule} className="btn-secondary-outline">
                                    {
                                        ModuleSetting == 1 ? 'Deactivate Prediction' : 'Activate Prediction Now'
                                    }
                                    </Button>
                            </div>
                        </Col>
                    </Row>
                </div>
            </Fragment>
        )
    }
}
export default PredictionType