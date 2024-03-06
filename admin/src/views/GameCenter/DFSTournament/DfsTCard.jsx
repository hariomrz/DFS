import React, { Component } from 'react';
import Images from '../../../components/images';
import * as NC from "../../../helper/NetworkingConstants";
import { MomentDateComponent } from "../../../components/CustomComponent";
import HF, { _isUndefined, _isEmpty } from '../../../helper/HelperFunction';
import WSManager from '../../../helper/WSManager';
class DfsTCard extends Component {
    constructor(props) {
        super(props);
        this.state = {}
    }

    renderPrizeDetail=(data)=>{
        try {
            return JSON.parse(data);
        } catch (e) {
            return data;
        }
    }
    manageFixture=(listItem)=>{
        this.props.manageFixture(listItem)
    }
    cancleTournament=(listItem)=>{
        this.props.cancleTournament(listItem)
    }
    showTopPrize=(data)=>{
        return (<>
        {
            data && data.prize_type &&
            <span>
                {data.prize_type == '0' && <i class="icon-bonus"></i>}
                {data.prize_type == '2' && <img src={Images.COINIMG} alt="coin-img" width={15} />}
                {data.prize_type == '1' && HF.getCurrencyCode()}
                {data.amount}
            </span>
        }
        </>)
    }

    pinContest=(listItem)=>{
        this.props.pinContest(listItem)
    }

    render() {
        let { listItem, edit, activeTab } = this.props  
        listItem['prize_detail'] = this.renderPrizeDetail(listItem.prize_detail)
        let { int_version } = HF.getMasterData()

        return (
            <div className="dfst-card animate-bottom">
                <div className="pt-info">
                    <div className="pt-info-box">
                        {activeTab != '3' &&
                            <>
                                {
                                    listItem.is_pin == '1' ?
                                    <img onClick={() => this.pinContest(listItem)} src={Images.PIN_ACTIVE} alt="" className="pinned-active" />
                                    : <span className="pin-tour" onClick={()=>this.pinContest(listItem)}>
                                        <i className="icon-pinned"></i>
                                    </span>
                                }
                            </>
                        }
                        {edit &&
                            <div
                                className="pt-edit right-selection"
                                onClick={() => this.props.editCallback(listItem.tournament_id)}
                            >
                                <i className="icon-edit"></i>
                            </div>
                        }
                        {activeTab !='3' &&
                            <div className="card-sett">
                                <span onClick={()=>this.manageFixture(listItem)}><i className="icon-setting"></i>Manage</span>
                                {
                                    listItem.status == 0 &&   
                                    <i className="icon-cancel rrr" onClick={()=>this.cancleTournament(listItem)}></i>
                                }
                                {
                                    listItem.status == 1 &&   
                                    <span className="cancel-txt">Canceled</span>
                                }
                            </div>
                        }
                        {activeTab =='3' &&
                            <div className="card-sett">
                                {
                                    listItem.status == 1 &&   
                                    <span className="cancel-txt">Canceled</span>
                                }
                            </div>
                        }
                        <div className="pt-icon">
                            <figure >
                                <img src={listItem.image ? NC.S3 + NC.DFST_LOGO + listItem.image : Images.no_image} className="img-cover" />
                            </figure>
                        </div>
                        <div className="pt-detail">
                            {/* <div className="pt-tag">DFS Tournament</div> */}
                            <div
                                className={`pt-title ${edit ? '' : 'pt-hover'}`}
                                onClick={() => this.props.redirectCallback(listItem.tournament_id)}
                            >
                                {listItem.name}
                            </div>
                            <div className="pt-dt-fx">
                                <span className="pt-date">
                                    {/* {WSManager.getUtcToLocalFormat(listItem.start_date, 'D-MMM, hh:mm A')} */}
                                    {/* <MomentDateComponent data={{ date: listItem.start_date, format: "DD MMM" }} /> */}
                                    {HF.getFormatedDateTime(listItem.start_date, 'D-MMM, hh:mm A')}
                                    {'-'}
                                    {/* {WSManager.getUtcToLocalFormat(listItem.end_date, 'D-MMM, hh:mm A')} */}
                                    {HF.getFormatedDateTime(listItem.end_date, 'D-MMM, hh:mm A')}
                                    {/* <MomentDateComponent data={{ date: listItem.end_date, format: "DD MMM" }} /> */}
                                </span>
                                <span className="pt-date no-sep">{listItem.match_count ? listItem.match_count : '0'} {int_version == "1" ? "Games" : "Fixtures"}</span>
                            </div>
                            {/* <div className="pt-dt-fx">{listItem.max_bonus_allowed}% Bonus Allowed</div> */}
                        </div>
                    </div>
                    {/* <div className="pt-entry clearfix">
                        <button type="button" className="btn btn-primary">
                            {
                                listItem.currency_type == '0' && listItem.entry_fee > 0 &&
                                <span>
                                    <i className="icon-bonus"></i>
                                    {HF.getPrizeInWordFormat(parseInt(listItem.entry_fee))}
                                </span>
                            }
                            {
                                listItem.currency_type == '1' && listItem.entry_fee > 0 &&
                                <span>
                                    {HF.getCurrencyCode()}
                                    {HF.getPrizeInWordFormat(parseInt(listItem.entry_fee))}
                                </span>
                            }
                            {
                                listItem.currency_type == '2' && listItem.entry_fee > 0 &&
                                <span>
                                    <img src={Images.COINIMG} alt="coin-img" className="coin-entry coin-img" />
                                    {HF.getPrizeInWordFormat(parseInt(listItem.entry_fee))}
                                </span>
                            }
                            {listItem.entry_fee == 0 &&
                                <span>Free</span>
                            }
                        </button>
                    </div> */}
                    <div className="pt-win-box clearfix">
                        <div className="pt-leg-name float-left">
                            {listItem.league_name}
                        </div>
                        {
                            !_isEmpty(listItem.prize_detail) && 
                            <div className="pt-leg-name float-right">
                                Winnings
                                <span className="pt-prize">
                                    {listItem.prize_detail && this.showTopPrize(listItem.prize_detail[0])}
                                </span>
                            </div>
                        }
                    </div>
                </div>
            </div>
        )
    }
}
export default DfsTCard
