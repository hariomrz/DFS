import React, { Component, Fragment } from 'react';
import Images from '../../components/images';
import * as NC from "../../helper/NetworkingConstants";
import { MomentDateComponent } from "../../components/CustomComponent";
import { _isUndefined, _isEmpty } from '../../helper/HelperFunction';
import { withRouter } from 'react-router'
import HF from "../../helper/HelperFunction";

class LSF_FixtureCard extends Component {
    constructor(props) {
        super(props);
        this.state = {}
    }

    render() {
        let { item, activeTab, callfrom, show_flag } = this.props

        return (
            <div className="SpCndlCard animate-right LsfCndlCrd">
                {/* <div className="sf-img">
                    <img src={activeTab == '0' ? Images.SF_ORANGE : activeTab == '1' ? Images.SF_BLUE : activeTab == '2' ? Images.SF_GREEN : ''} alt="" />
                </div> */}
                <div className="sf-date-box">
                    <div className="lbl">
                        <div className="lft-sec">Start Date</div>
                        <div className="rgt-sec">End Date</div>
                    </div>
                    <div className="date-blk " onClick={() => callfrom == '1' ? this.props.redirectToUpdateStock(item) : null}>
                        <div className="lft-sec cursor-pointer">
                            {
                                !_isEmpty(item.scheduled_date) &&
                                // <MomentDateComponent data={{ date: item.scheduled_date, format: "D-MMM-YYYY" }} />
                            <>{HF.getFormatedDateTime(item.scheduled_date, "D-MMM-YYYY")}</>
                            }
                        </div>
                        <span></span>
                        <div className="rgt-sec cursor-pointer">
                            {
                                !_isEmpty(item.end_date) &&
                                // <MomentDateComponent data={{ date: item.end_date, format: "D-MMM-YYYY" }} />
                            <>{HF.getFormatedDateTime(item.end_date, "D-MMM-YYYY")}</>

                            }
                        </div>
                    </div>
                    <div className="time">
                        <div className="lft-sec">{HF.getFormatedDateTime(item.scheduled_date, 'hh:mm A')}</div>
                        <div className="rgt-sec"> {HF.getFormatedDateTime(item.end_date, 'hh:mm A')}</div>
                    </div>
                    {/* <div
                        className={`sf-fx-date ${callfrom == '1' ? 'sf-hover' : ''}`}
                        onClick={() => callfrom == '1' ? this.props.redirectToUpdateStock(item) : null}
                    >
                        {
                            !_isEmpty(item.scheduled_date) &&
                            <MomentDateComponent data={{ date: item.scheduled_date, format: "D-MMM-YYYY" }} />
                        }
                    </div>
                    <div className="sf-fx-time">
                        {
                            show_flag &&
                            <Fragment>                                
                                {HF.getFormatedDateTime(item.scheduled_date, 'hh:mm A')}
                                {' - '}
                                {HF.getFormatedDateTime(item.end_date, 'hh:mm A')}
                            </Fragment>
                        }
                    </div> */}
                </div>
                {
                    callfrom == '1' &&
                    <ul className="fx-action-list">
                        {
                            activeTab != '2' &&
                            <li
                                title="Add alert message"
                                className="fx-action-item"
                                onClick={() => this.props.openMsgModal(item)}
                            >
                                <i className="icon-megaphone" title="Add alert message"></i>
                            </li>
                        }
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
                            activeTab != '1' &&
                            <li className="fx-action-item">
                                <i
                                    title="Candle stats"
                                    className="icon-stats"
                                    onClick={() => this.props.history.push({ pathname: '/livestockfantasy/nsestats/' + activeTab + '/' + item.collection_id, state: { cndl_data: item } })}
                                ></i>
                            </li>
                        }
                    </ul>
                }
            </div>
        )
    }
}
export default withRouter(LSF_FixtureCard)
