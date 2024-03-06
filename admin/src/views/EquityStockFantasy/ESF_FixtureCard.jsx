import React, { Component, Fragment } from 'react';
import Images from '../../components/images';
import * as NC from "../../helper/NetworkingConstants";
import { MomentDateComponent } from "../../components/CustomComponent";
import HF, { _isUndefined, _isEmpty } from '../../helper/HelperFunction';
import WSManager from "../../helper/WSManager";
import { withRouter } from 'react-router'
class ESF_FixtureCard extends Component {
    constructor(props) {
        super(props);
        this.state = {}
    }
    getMatchMsg = (status, status_overview) => {
        let msg = ''
        if (status == '0' || '1' || '2' || '3' || '4' || '5') {
            if (status_overview == '1') {
                msg = 'Rain Delay/Suspended';
            }
            else if (status_overview == '2') {
                msg = 'Abandoned';
            }
            else if (status_overview == '3') {
                msg = 'Canceled';
            }
        }
        return msg
    }

    getTitle = (activeFxTab, name) => {
        var str = ''
        if (!_isEmpty(name))
            str = name
        else if (activeFxTab == '1')
            str = 'Daily'
        else if (activeFxTab == '2')
            str = 'Weekly'
        else if (activeFxTab == '3')
            str = 'Monthly'
        return str
    }	

    render() {
        let { item, activeTab, activeFxTab, callfrom, show_flag } = this.props        
        return (
            <div className="esf-fx-card animate-right">
                <div className="sf-img">
                    <img src={activeTab == '0' ? Images.ESF_ORANGE : activeTab == '1' ? Images.ESF_BLUE : activeTab == '2' ? Images.ESF_GREEN : ''} alt="" />
                </div>
                <div className="sf-date-box">
                    <div className="sf-verify-title">
                        {
                            callfrom == '2' &&
                            <Fragment>
                                {this.getTitle(activeFxTab, item.name)}
                            </Fragment>
                        }
                        {
                            callfrom == '1' && item.name
                        }
                    </div>
                    <div
                        className={`sf-fx-date ${callfrom == '1' ? 'sf-hover' : ''}`}
                        onClick={() => callfrom == '1' ? this.props.redirectToUpdateStock(item) : null}
                    >
                        {
                            (activeFxTab == '1' && !_isEmpty(item.scheduled_date)) &&
                            // <MomentDateComponent data={{ date: item.scheduled_date, format: "D-MMM-YYYY" }} />
                            <>{HF.getFormatedDateTime(item.scheduled_date, "D-MMM-YYYY")}</>

                        }
                        {
                            (activeFxTab == '2' && !_isEmpty(item.week)) &&
                            'Week ' + item.week
                        }
                        {
                            (activeFxTab == '3' && !_isEmpty(item.month)) &&
                            item.month
                        }
                    </div>
                    <div className="sf-fx-time">
                        {
                            // (!_isEmpty(item.scheduled_date)) &&
                            show_flag &&
                            // <MomentDateComponent data={{ date: item.scheduled_date, format: "hh:mm A" }} />
                            <>{HF.getFormatedDateTime(item.scheduled_date, "hh:mm A")}</>

                        }
                    </div>
                </div>
                {
                    callfrom == '1' &&
                    // ((item.status == "0" || item.status == "1" || item.status == "2") && (item.status_overview == "0" || item.status_overview == "4")) ?
                    <ul className="fx-action-list">
                        {
                            activeTab == '1' &&
                            <Fragment>
                                <li className="fx-action-item">
                                    <i
                                        title="Update Stock"
                                        className="icon-Salary-update"
                                        onClick={() => this.props.redirectToStockReview(item)}
                                    ></i>
                                </li>
                                {/* <li className="fx-action-item">
                                    <i
                                        title="Mark match delay"
                                        className="icon-delay"
                                        onClick={() => this.props.openDelayModal(item)}
                                    ></i>
                                </li> */}
                                <li className="fx-action-item">
                                    <i
                                        title="Published"
                                        className="icon-fixture-contest"
                                        onClick={() => this.props.redirectToUpdateStock(item)}
                                    ></i>
                                </li>
                                <li className="fx-action-item">
                                    <i
                                        title="Contest Template"
                                        className="icon-template"
                                        onClick={() => this.props.redirectToTemplate(item)}
                                    ></i>
                                </li>
                            </Fragment>
                        }

                        {
                            activeTab != '2' &&
                            <li
                                title="Add alert message"
                                className="fx-action-item"
                                onClick={() => this.props.openMsgModal(item)}
                            >
                                <i className="icon-email_verified" title="Add alert message"></i>
                            </li>
                        }
                        {
                            activeTab != '1' &&
                            <li className="fx-action-item">
                                <i
                                    title="Match stats"
                                    className="icon-stats"
                                    onClick={() => this.props.redirectToStats(item)}
                                    onClick={() => this.props.history.push({ pathname: '/equitysf/nsestats/' + activeFxTab+ '/' + activeTab + '/' + item.collection_id })}
                                ></i>
                            </li>
                        }
                    </ul>
                    // :
                    // <div className="sf-fx-status">{this.getMatchMsg(item.status, item.status_overview)}</div>
                }
            </div>
        )
    }
}
export default withRouter(ESF_FixtureCard)
