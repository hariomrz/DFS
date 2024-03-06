import React, { Component } from "react";
import { Row, Col } from 'reactstrap';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import _ from 'lodash';
export default class PrizeCron extends Component {
    constructor(props) {
        super(props)
        this.state = {
            PrizeCron: []
        }
    }
    componentDidMount() {
        this.getPrizeCronSetting()
    }

    getPrizeCronSetting() {
        WSManager.Rest(NC.baseURL + NC.GET_PRIZE_CRON_SETTING, {}).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    PrizeCron: !_.isEmpty(Response.data) ? Response.data : []
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    handleModuleChange = (indx) => {
        let { PrizeCron } = this.state
        PrizeCron[indx]['key_value'] = PrizeCron[indx]['key_value'] == "1" ? "0" : "1";
        this.setState({
            PrizeCron
        }, () =>
            this.updateSetting(indx)
        )
    }

    updateSetting = (indx) => {
        let { PrizeCron } = this.state
        let params = {
            "key_name": PrizeCron[indx].key_name,
            "status": PrizeCron[indx].key_value
        }
        WSManager.Rest(NC.baseURL + NC.SAVE_PRIZE_CRON_STATUS, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                notify.show(responseJson.message, "success", 3000)
            }
        }).catch((error) => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        })
    }

    render() {
        let { PrizeCron } = this.state
        return (
            <div className="min-wdl-page hub-page">
                <Row>
                    <Col md={12}>
                        <h2 className="h2-cls">Prize Distribution Cron Setting</h2>
                    </Col>
                </Row>
                <div className="hp-dy-banners hp-bg-box">                    
                    {
                        _.map(PrizeCron, (item, idx) => {
                            return (
                                <Row key={idx}>
                                    <Col sm={12}>
                                        <label htmlFor="language" className="float-left mb-0 mt-1">{item.name}</label>
                                        <div className="activate-module float-left ml-5">
                                            <label className="global-switch">
                                                <input
                                                    type="checkbox"
                                                    checked={item.key_value == "1" ? false : true}
                                                    onChange={() => this.handleModuleChange(idx)}
                                                />
                                                <span className="switch-slide round">
                                                    <span className={`switch-on ${item.key_value == "1" ? 'active' : ''}`}>ON</span>
                                                    <span className={`switch-off ${item.key_value == "0" ? 'active' : ''}`}>OFF</span>
                                                </span>
                                            </label>
                                        </div>
                                    </Col>
                                </Row>
                            )
                        })
                    }
                </div>
            </div>
        )
    }
}