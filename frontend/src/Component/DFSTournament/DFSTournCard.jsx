import React, { Component } from 'react';
import { MomentDateComponent } from "../CustomComponent";
import { Utilities, _Map } from '../../Utilities/Utilities';
import { MyContext } from '../../views/Dashboard';
import * as AL from "../../helper/AppLabels";
import Images from '../../components/images';
import { getUserAadharDetail } from '../../WSHelper/WSCallings';
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import WSManager from "../../WSHelper/WSManager";

class DFSTourCard extends Component {
    constructor(props) {
        super(props)
        this.state = {
            aadharData: ''
        }
    }

    componentDidMount() {
        if (WSManager.loggedIn() && Utilities.getMasterData().a_aadhar == "1") {
            if(WSManager.getProfile().aadhar_status != 1){
                getUserAadharDetail().then((responseJson) => {
                    if (responseJson && responseJson.response_code == WSC.successCode) {
                        this.setState({ aadharData: responseJson.data },()=>{
                            WSManager.updateProfile(this.state.aadharData)
                        });
                    }
                })
            }
            else{
                let aadarData = {
                    'aadhar_status': WSManager.getProfile().aadhar_status,
                    "aadhar_id": WSManager.getProfile().aadhar_detail.aadhar_id
                }
                this.setState({ aadharData: aadarData });
            }
        }
    }

    getWinCalculation = (prize_data) => {
        let prizeAmount = { 'real': 0, 'bonus': 0, 'point': 0, 'merchandise': 0 };
        prize_data && prize_data.map(function (lObj, lKey) {
            var amount = 0;
            // if (lObj.max_value) {
            //     amount = parseFloat(lObj.max_value);
            // } else {
            //     amount = parseFloat(lObj.amount);
            // }
            if (lObj.prize_type == 3) {
                amount = lObj.amount;
                if (!prizeAmount.merchandise) {
                    prizeAmount['merchandise'] = amount;
                }
            }
            else {
                amount += lObj.amount ? (parseInt(lObj.amount) * ((parseInt(lObj.max) - parseInt(lObj.min)) + 1)) : 0
                if (lObj.prize_type == 0) {
                    prizeAmount['bonus'] = parseFloat(prizeAmount['bonus']) + amount;
                } else if (lObj.prize_type == 2) {
                    prizeAmount['point'] = parseFloat(prizeAmount['point']) + amount;
                } else {
                    prizeAmount['real'] = parseFloat(prizeAmount['real']) + amount;
                }
            }
        })
        return prizeAmount;
    }

    isShowPrize = (prize_data) => {
        let prizeAmount = this.getWinCalculation(prize_data);
        let showPrizeSec = false;
        if (prizeAmount.merchandise > 0 || prizeAmount.real > 0 || prizeAmount.bonus > 0 || prizeAmount.point > 0) {
            showPrizeSec = true;
        }
        return showPrizeSec
    }

    showPrize = (prize_data) => {
        let prizeAmount = this.getWinCalculation(prize_data);
        let merchandiseList = this.props.data.MerchandiseList;
        return (
            <React.Fragment>
                {
                    prizeAmount.merchandise ?
                        merchandiseList && merchandiseList.map((merchandise, index) => {
                            return (
                                <React.Fragment key={index}>
                                    {prizeAmount.merchandise == merchandise.merchandise_id &&
                                        <>{merchandise.name}</>
                                    }
                                </React.Fragment>
                            );
                        })
                        :
                        prizeAmount.real > 0 ?
                            <>
                                {Utilities.getMasterData().currency_code}
                                {Utilities.getPrizeInWordFormat(prizeAmount.real)}
                            </>
                            :
                            prizeAmount.bonus > 0 ?
                                <><i className="icon-bonus" />{Utilities.numberWithCommas(parseFloat(prizeAmount.bonus).toFixed(0))}</>
                                :
                                prizeAmount.point > 0 ?
                                    <> <img style={{ marginBottom: '2px' }} src={Images.IC_COIN} width="12px" height="12px" />{Utilities.numberWithCommas(parseFloat(prizeAmount.point).toFixed(0))}</>
                                    :
                                    0
                }
            </React.Fragment>
        )


    }

    PickAction = (item, isFrom) => {
        this.props.data.joinTournament(item, isFrom)
    }

    render() {
        const {
            item, isFrom, showHTPModal, joinTournament, history
        } = this.props.data;
        const { aadharData } = this.state;
        let aadhar_data = WSManager.getProfile()
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="tour-card">
                        <div className="tour-body">
                            {
                                isFrom == 'Lobby' && item.is_live == 1 &&
                                <div className="showstatus"><span></span> {AL.LIVE}</div>
                            }
                            <div className="spon-img-sec">
                                <img src={item.image ? Utilities.getDFSTourLogo(item.image) : Images.DEFAULT_DFS_TOUR_IMG} alt="" />
                            </div>
                            <div className="tour-info-text">
                                {
                                    isFrom == 'Lobby' &&
                                    <div>
                                        <div href className="tour-rules" onClick={showHTPModal}>{AL.DFS_TOURNAMENT}
                                            <span>{AL.DFS_TOURNAMENT}</span>
                                        </div>
                                    </div>
                                }
                                {
                                    isFrom == 'Lobby' ?
                                        <div className="tour-nm">{item.name}</div>
                                        :
                                        <>
                                            <div className="tour-nm show-ellip">{item.name}</div>
                                            <div className="tour-text">{AL.DFS_TOURNAMENT}</div>
                                        </>
                                }
                                {
                                    (isFrom == 'Lobby' || (isFrom != 'Lobby' && item.status != 2 && item.status != 3 && item.is_live != 1 && isFrom != 1 && isFrom != 2)) ?
                                        <div className="fixture-info">
                                            {
                                                <div className="tour-status">
                                                    <MomentDateComponent data={{ date: item.start_date, format: "D MMM " }} /> -
                                                    <MomentDateComponent data={{ date: item.end_date, format: "D MMM " }} />
                                                </div>
                                            }
                                            {
                                                isFrom == 'Lobby' ?
                                                    <>
                                                        <>
                                                            <span className="slash">|</span>
                                                            {item.match_count && <span className="no-of-fix">{item.match_count} {AL.FIXTURES}</span>}
                                                        </>
                                                        {
                                                            item.max_bonus_allowed && item.max_bonus_allowed > 0 &&
                                                            <>
                                                                <span className="slash">|</span>
                                                                {item.max_bonus_allowed && <span className="no-of-fix">{item.max_bonus_allowed}% {AL.BONUS_ALLOWED}</span>}
                                                            </>
                                                        }
                                                    </>
                                                    :
                                                    <>
                                                        {
                                                            item.new_fixture_count && item.new_fixture_count > 0 &&
                                                            <>
                                                                <span className="slash">|</span>
                                                                {item.new_fixture_count && <span className="no-of-fix">{item.new_fixture_count} {AL.FIXTURES}</span>}
                                                            </>
                                                        }
                                                    </>
                                            }
                                            {/* <span className="slash">|</span>
                                        <span className="no-of-fix">{isFrom == 'Lobby' ? item.match_count : item.new_fixture_count} {AL.FIXTURES}</span> */}
                                            {
                                                isFrom != 'Lobby' &&
                                                // isFrom == 0 &&
                                                <a href className="tour-btn" onClick={() => this.PickAction(item, isFrom)}>
                                                    {
                                                        item.new_fixture_count > 0 ?
                                                            AL.ENTER_TEAM
                                                            :
                                                            AL.VIEW
                                                    }
                                                </a>
                                            }
                                        </div>
                                        :
                                        <>
                                            <div className={"fixture-info " + ((item.status == 2 || item.status == 3 || isFrom == 2) ? "comp-status" : (item.is_live == 1 || isFrom == 1) ? "live-status" : "")}>
                                                <div className="tour-status">
                                                    {
                                                        (item.status == 2 || item.status == 3 || isFrom == 2) ?
                                                            <>
                                                                <span></span>{AL.COMPLETED}
                                                            </>
                                                            :
                                                            (item.is_live == 1 || isFrom == 1) &&
                                                            <>
                                                                <span></span>  {AL.LIVE}
                                                            </>
                                                    }
                                                </div>
                                                {
                                                    (item.status != 2 && item.status != 3 && isFrom != 2) &&
                                                    <>
                                                        {
                                                            item.new_fixture_count && item.new_fixture_count > 0 &&
                                                            <>
                                                                <span className="slash">|</span>
                                                                <span className="no-of-fix">{item.new_fixture_count} {AL.NEW_FIXTURES}</span>
                                                            </>
                                                        }
                                                    </>
                                                }
                                            </div>
                                            {
                                                (item.status == 2 || item.status == 3 || isFrom == 2) ?
                                                    <a href className="tour-btn tour-btn-result" onClick={() => this.PickAction(item, isFrom)}>
                                                        {AL.VIEW_RESULT}
                                                    </a>
                                                    :
                                                    <a href className="tour-btn" onClick={() => this.PickAction(item, isFrom)}>
                                                        {
                                                            item.new_fixture_count > 0 ?
                                                                AL.ENTER_TEAM
                                                                :
                                                                AL.VIEW
                                                        }
                                                    </a>
                                            }
                                        </>
                                }
                            </div>
                        </div>
                        {
                            isFrom == 'Lobby' &&
                            <div className="entry-btn"
                                onClick={Utilities.getMasterData().a_aadhar == 1 ?
                                    (aadharData && aadharData.aadhar_status == "1") ?
                                        joinTournament
                                        :
                                        Utilities.aadharConfirmation(aadhar_data, this.props)
                                    :
                                    joinTournament}
                            >

                                <a href className="btn btn-rounded">
                                    {
                                        item.entry_fee == 0 ?
                                            AL.JOIN_FOR_FREE
                                            :
                                            <>
                                                {AL.JOIN_FOR}
                                                {
                                                    item.currency_type == 2 ?
                                                        <img className="img-coin" alt='' src={Images.IC_COIN} />
                                                        :
                                                        <span>
                                                            {Utilities.getMasterData().currency_code}
                                                        </span>
                                                }
                                                {item.entry_fee}
                                            </>
                                    }
                                </a>
                            </div>
                        }
                        {
                            <div className={"tour-footer" + ((item.status == 2 || item.status == 3 || (item.is_live == 1 && item.status != 0) || isFrom == 1 || isFrom == 2) ? '' : ' tour-footer-winning-sec')}>
                                <div className="league-name">{item.league_name}</div>
                                <div className={"rank-sec" + ((item.status == 2 || item.status == 3 || (item.is_live == 1 && item.status != 0) || isFrom == 1 || isFrom == 2 || (isFrom == 'LSlider' && item.is_live == 1)) ? '' : ' winning-sec')}>
                                    {
                                        (item.status == 2 || item.status == 3 || (item.is_live == 1 && item.status != 0) || isFrom == 1 || isFrom == 2 || (isFrom == 'LSlider' && item.is_live == 1)) ?
                                            <>
                                                {AL.YOUR_RANK} <span>{item.game_rank || 0}</span>
                                            </>
                                            :
                                            <>
                                                {
                                                    item.prize_detail && item.prize_detail.length > 0 &&
                                                    <>
                                                        {
                                                            this.isShowPrize(item.prize_detail) > 0 &&
                                                            <>
                                                                {AL.WINNINGS}
                                                                <span>
                                                                    {this.showPrize(item.prize_detail)}
                                                                </span>
                                                            </>
                                                        }
                                                    </>
                                                }
                                            </>
                                    }
                                </div>
                            </div>
                        }
                    </div>
                )
                }
            </MyContext.Consumer>
        )
    }
}

export default DFSTourCard;