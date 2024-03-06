import React, { Component } from 'react';
import Images from '../../components/images';
import * as NC from "../../helper/NetworkingConstants";
import { MomentDateComponent } from "../../components/CustomComponent";
import { _isUndefined, _isEmpty } from '../../helper/HelperFunction';
import HF from '../../helper/HelperFunction';

class PTCard extends Component {
    constructor(props) {
        super(props);
        this.state = {}
    }
    render() {
        let { listItem, edit, activeTab } = this.props
        return (
            <div className="pt-card animate-bottom">
                <div className="pt-info">
                    <div className="pt-info-box">
                        {edit &&
                            <div
                                className="pt-edit right-selection"
                                onClick={() => this.props.editCallback(listItem.pickem_id)}
                            >
                                <i className="icon-edit"></i>
                            </div>
                        }
                        <figure className="pt-icon">
                            <img src={listItem.image ? NC.S3 + NC.PICKEM_TR_LOGO + listItem.image : Images.no_image} className="img-cover" />
                        </figure>
                        <div className="pt-detail">
                            <div className="pt-tag">Pick’em Tournament</div>
                            <div
                                className={`pt-title ${edit ? '' : 'pt-hover'}`}
                                onClick={() => this.props.redirectCallback(listItem.pickem_id)}
                            >
                                {listItem.name}
                            </div>
                            <div className="pt-dt-fx">
                                <span className="pt-date">
                                    {/* <MomentDateComponent data={{ date: listItem.start_date, format: "DD MMM" }} /> */}
                                    {HF.getFormatedDateTime(listItem.start_date, "DD MMM")}

                                    {'-'}
                                    {/* <MomentDateComponent data={{ date: listItem.end_date, format: "DD MMM" }} /> */}
                                    {HF.getFormatedDateTime(listItem.end_date, "DD MMM")}

                                </span>
                                <span className="pt-date">{listItem.match_count ? listItem.match_count : '0'} Fixtures</span>
                            </div>
                        </div>
                    </div>
                    <div className="pt-win-box clearfix">
                        <div className="pt-leg-name float-left">
                            {listItem.league_name}
                        </div>
                        {!_isEmpty(listItem.prize_detail) && <div className="pt-leg-name float-right">
                            Winnings
                            <span
                                onClick={(e) => this.props.viewWinnersCallback(e, listItem)}
                                className="pt-prize"
                                dangerouslySetInnerHTML={this.props.getPrizeCallback(listItem.prize_detail)}
                            >
                            </span>
                        </div>}
                    </div>
                </div>
                <div className="pt-parti-box">
                    <div
                        className="pt-parti float-left"
                        onClick={() => this.props.partiModalCallback(listItem)}
                    >
                        {!_isUndefined(listItem.total_user_joined) ? listItem.total_user_joined : 0} Participant
                    </div>
                    {activeTab != '2' &&<div
                        className="pt-parti float-right"
                        onClick={() => this.props.ldrbrdModalCallback(listItem)}
                    >
                        Leaderboard
                    </div>}
                </div>
            </div>
        )
    }
}
export default PTCard