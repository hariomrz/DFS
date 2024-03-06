import React, { Component } from 'react';
import Images from '../../../components/images';
import * as NC from "../../../helper/NetworkingConstants";
import { MomentDateComponent } from "../../../components/CustomComponent";
import { _isUndefined, _isEmpty } from '../../../helper/HelperFunction';
class DfsTMatchCard extends Component {
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

    render() {
        let { item, activeTab } = this.props
        return (
            <div className="dfst common-fixture">
                <div className="bg-card">
                    <div className="clearfix">
                        <img className="com-fixture-flag float-left" src={item.home_flag ? NC.S3 + NC.FLAG + item.home_flag : Images.no_image} />

                        <img className="com-fixture-flag float-right" src={item.away_flag ? NC.S3 + NC.FLAG + item.away_flag : Images.no_image} />

                        <div className="com-fixture-container">
                            <div className="com-fixture-name">{(item.home) ? item.home : 'TBA'} VS {(item.away) ? item.away : 'TBA'}</div>
                            <div className="com-fixture-title">
                                {
                                    item.season_scheduled_date ?
                                        <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D-MMM-YYYY hh:mm A" }} />
                                        :
                                        '--'
                                }
                            </div>
                        </div>
                    </div>
                    {
                        ((item.status == "0" || item.status == "1" || item.status == "2") && (item.status_overview == "0" || item.status_overview == "4")) ?
                            <ul className="fx-action-list">
                                {/* {
                                activeTab == '2' &&
                                <li className="fx-action-item">
                                    <i
                                        title="Mark match delay"
                                        className="icon-delay"
                                        onClick={() => this.props.openDelayModal(item)}
                                    ></i>
                                </li>}

                            {
                                (activeTab == '1' || activeTab == '2') &&
                                <li
                                    title="Add alert message"
                                    className="fx-action-item"
                                    onClick={() => this.props.openMsgModal(item)}
                                >
                                    <i className="icon-email_verified" title="Add alert message"></i>
                                </li>
                            } */}
                                {
                                    (activeTab == '1' || activeTab == '2') &&
                                    <li
                                        title="Delete match"
                                        className="fx-action-item"
                                        onClick={() => this.props.deleteMatchToggle(item)}
                                    >
                                        <i className="icon-delete" title="Delete match"></i>
                                    </li>
                                }
                            </ul>
                            :
                            <div className="fx-match-cancel">{this.getMatchMsg(item.status, item.status_overview)}</div>
                    }
                </div>
                <div className="dfst-parti-box">
                    <div
                        className="pt-parti float-left"
                        onClick={() => this.props.partiModalCallback(item)}
                    >
                        {!_isUndefined(item.total_user_joined) ? item.total_user_joined : 0} Participant
                        </div>
                    {
                        activeTab != '2' &&
                        <div
                            className="pt-parti float-right"
                            onClick={() => this.props.ldrbrdModalCallback(item)}
                        >
                            Leaderboard
                        </div>
                    }
                    {
                        activeTab == '2' &&
                        <div
                            className="pt-parti float-right"
                            onClick={() => this.props.promote(item)}
                        >
                            <span>Promote</span>
                        </div>
                    }
                </div>
            </div>
        )
    }
}
export default DfsTMatchCard
