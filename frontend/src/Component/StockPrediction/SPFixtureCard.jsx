import React, { Component } from 'react';
import { ProgressBar, OverlayTrigger, Tooltip } from 'react-bootstrap';
import { Utilities } from '../../Utilities/Utilities';
import { MyContext } from '../../views/Dashboard';
import CountdownTimer from '../../views/CountDownTimer';
import * as AL from "../../helper/AppLabels";
import Images from '../../components/images';
import moment from 'moment';
import { MomentDateComponent } from "../../Component/CustomComponent";
import * as AppLabels from "../../helper/AppLabels";

class SPFixtureCard extends Component {
    constructor(props) {
        super(props)
        this.state = {
        }
    }

    ShowProgressBar = (join, total) => {
        return join * 100 / total;
    }

    getPrizeAmount = (prize_data) => {
        let prizeAmount = this.getWinCalculation(prize_data.prize_distibution_detail);
        return (
            <React.Fragment>
                {
                    prizeAmount.is_tie_breaker == 0 && prizeAmount.real > 0 ?
                        <span>
                            {Utilities.getMasterData().currency_code}
                            {Utilities.getPrizeInWordFormat(prizeAmount.real)}
                        </span>
                        : prizeAmount.is_tie_breaker == 0 && prizeAmount.bonus > 0 ? <span> <i className="icon-bonus" />{Utilities.numberWithCommas(parseFloat(prizeAmount.bonus).toFixed(0))}</span>
                            : prizeAmount.is_tie_breaker == 0 && prizeAmount.point > 0 ? <span> <img className="img-coin" alt='' src={Images.IC_COIN} />{Utilities.numberWithCommas(parseFloat(prizeAmount.point).toFixed(0))}</span>
                                : AL.PRIZES
                }
            </React.Fragment>
        )
    }

    getWinCalculation = (prize_data) => {
        let prizeAmount = { 'real': 0, 'bonus': 0, 'point': 0, 'is_tie_breaker': 0 };
        prize_data && prize_data.map(function (lObj, lKey) {
            var amount = 0;
            if (lObj.max_value) {
                amount = parseFloat(lObj.max_value);
            } else {
                amount = parseFloat(lObj.amount);
            }
            if (lObj.prize_type == 3) {
                prizeAmount['is_tie_breaker'] = 1;
            }
            if (lObj.prize_type == 0) {
                prizeAmount['bonus'] = parseFloat(prizeAmount['bonus']) + amount;
            } else if (lObj.prize_type == 2) {
                prizeAmount['point'] = parseFloat(prizeAmount['point']) + amount;
            } else {
                prizeAmount['real'] = parseFloat(prizeAmount['real']) + amount;
            }
        })
        return prizeAmount;
    }

    addLeadingZeros(value) {
        value = String(value);
        while (value.length < 2) {
            value = '0' + value;
        }
        return value;
    }

    addZerosAtEnd(value) {
        value = String(value);
        while (value.length < 2) {
            value = value + '0';
        }
        return value;
    }

    getDifferenceInMinutes = (date1, date2) => {
        let currentDate = Utilities.getFormatedDateTime(date2)//'2021-12-16 14:30:00');
        let scheduleDate = Utilities.getFormatedDateTime(date1)//'2021-12-16 14:00:00');
        var now = moment(currentDate);
        var end = moment(scheduleDate);
        var duration = moment.duration(now.diff(end));
        var hours = duration._data.hours;
        var HLen = this.addLeadingZeros(hours)
        var min = duration._data.minutes;
        var MLen = this.addZerosAtEnd(min);
        return (HLen + ':' + MLen + ' Hrs');
    }

    aadharConfirmation = () => {
        Utilities.showToast(AppLabels.YOU_CANNOT_JOIN_CONTEST_VERIFICATION, 3000);
        this.props.history.push({ pathname: '/aadhar-verification' })
    }

    render() {
        const {
            item,
            isFrom
        } = this.props.data;
        const { profileData } = this.props;
        console.log('profileData', profileData)
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="sp-fixture-card">
                        <div className="crd-hdr">
                            <span className="hrs">
                                {this.getDifferenceInMinutes(item.scheduled_date, item.end_date)}
                            </span>
                            {
                                item.guaranteed_prize == 2 && parseInt(item.total_user_joined) >= parseInt(item.minimum_size) &&
                                <OverlayTrigger rootClose trigger={['click']} placement="right" overlay={
                                    <Tooltip id="tooltip" className="tooltip-featured">
                                        <strong>{AL.GUARANTEED_DESCRIPTION}</strong>
                                    </Tooltip>
                                }>
                                    <span className="sp-guar">G</span>
                                </OverlayTrigger>

                            }
                            {
                                item.is_confirmed == 1 && parseInt(item.total_user_joined) >= parseInt(item.minimum_size) &&
                                <OverlayTrigger trigger={['click']} placement="right" overlay={
                                    <Tooltip id="tooltip" className="tooltip-featured">
                                        <strong>{AL.CONFIRM_DESCRIPTION}</strong>
                                    </Tooltip>
                                }>
                                    <span className="sp-guar confirm">C</span>
                                </OverlayTrigger>

                            }
                            {
                                item.multiple_lineup > 1 &&
                                <OverlayTrigger rootClose trigger={['click']} placement="right" overlay={
                                    <Tooltip id="tooltip" className="tooltip-featured">
                                        <strong>{AL.MAX_TEAM_FOR_MULTI_ENTRY} {item.multiple_lineup} {AL.PORTFOLIOS}</strong>
                                    </Tooltip>
                                }>
                                    <span className="sp-multi">M</span>
                                </OverlayTrigger>

                            }
                            <span className="shw-scrbrd" onClick={this.props.showRulesModal}>
                                <i className="icon-note"></i>
                            </span>
                        </div>
                        <div className="crd-body" onClick={(event) => { isFrom != 'SPLobbyMyContest' && this.props.ContestDetailShow(item, 2, event) }}>
                            <div className="crd-bd-hd" onClick={(event) => { isFrom != 'SPLobbyMyContest' && this.props.ContestDetailShow(item, 1, event) }}>
                                <span className="win-amt">{AL.WIN} {this.getPrizeAmount(item)}</span>
                                <span className="candel-nm">{item.contest_title ? " - " + item.contest_title : ""}</span>
                            </div>
                            <div className={`candel-dt ${isFrom == 'SPLobbyMyContest' ? ' mb-0' : ''}`} >
                                {
                                    Utilities.showCountDown(item, true) ?
                                        <>
                                            <div className="countdown time-line">
                                                {item.game_starts_in && <CountdownTimer
                                                    deadlineTimeStamp={item.game_starts_in}
                                                    timerCallback={this.props.timerCallback}
                                                    hideHrs={true}
                                                // hideSecond={true}
                                                />}
                                            </div>
                                            {
                                                isFrom != 'SPMyContest' &&
                                                <><MomentDateComponent data={{ date: item.scheduled_date, format: "hh:mm A " }} /> - <MomentDateComponent data={{ date: item.end_date, format: "hh:mm A " }} /></>
                                            }
                                        </>
                                        :
                                        <><MomentDateComponent data={{ date: item.scheduled_date, format: "D MMM hh:mm A " }} /> - <MomentDateComponent data={{ date: item.end_date, format: "hh:mm A " }} /></>
                                }
                                {
                                    isFrom == 'SPLobbyMyContest' &&
                                    <a href className="btn btn-primary btn-rounded brd-only" onClick={(e) => this.props.onEdit(e, item)}>
                                        {item.is_upcoming == 1 ? AL.EDIT : AL.VIEW}
                                    </a>
                                }
                            </div>
                            {
                                (isFrom == 'SPLobby' || isFrom == 'SPMyContest') &&
                                <div className="sp-crd-act">
                                    <div className="progress-bar-default" onClick={(event) => { isFrom != 'SPLobbyMyContest' && this.props.ContestDetailShow(item, 3, event) }}>
                                        <ProgressBar className={parseInt(item.total_user_joined) < parseInt(item.minimum_size) ? 'danger-area' : ''} now={this.ShowProgressBar(item.total_user_joined, item.minimum_size)} />
                                        <div className="progress-bar-value">
                                            <span className="total-output"> {console.log('item.total_user_joined', item.total_user_joined)}
                                                {item.total_user_joined && item.total_user_joined == 0 ? 0 : Utilities.numberWithCommas(parseInt(item.total_user_joined || 0))}
                                            </span>
                                            /<span className="total-entries">{Utilities.numberWithCommas(parseInt(item.size))} {AL.ENTRIES}</span>
                                            {
                                                isFrom == 'SPLobby' &&
                                                <span class="min-entries">{AL.MIN} {Utilities.numberWithCommas(item.minimum_size)}</span>
                                            }
                                        </div>
                                    </div>
                                    <div className="btn-sec">
                                        {
                                            isFrom == 'SPMyContest' ?
                                                // <a href className={`btn btn-primary btn-rounded ${!((parseInt(item.user_joined_count) < parseInt(item.multiple_lineup)) && (parseInt(item.size) > parseInt(item.total_user_joined))) ? ' disabled' : ''}`} onClick={(e)=>this.props.check(e,item)} >
                                                <a href className={`btn btn-primary btn-rounded ${!((parseInt(item.user_joined_count) < parseInt(item.multiple_lineup)) && (parseInt(item.size) > parseInt(item.total_user_joined))) ? ' disabled' : ''} ${!Utilities.minuteDiffValueStock({ date: item.game_starts_in }, -5) ? ' disabled' : ''}`}




                                                    onClick={Utilities.getMasterData().a_aadhar == 1 ?
                                                        this.props.profileData && this.props.profileData.aadhar_status == "1" ?
                                                            (e) => this.props.check(e, item) :
                                                            () => this.aadharConfirmation() :
                                                        (e) => this.props.check(e, item)
                                                    }>


                                                    {
                                                        item.entry_fee > 0 ?
                                                            <React.Fragment>
                                                                {AL.ENTRY} {
                                                                    item.currency_type == 2 ?
                                                                        <img className="img-coin" alt='' src={Images.IC_COIN} />
                                                                        :
                                                                        <span>
                                                                            {Utilities.getMasterData().currency_code}
                                                                        </span>
                                                                }
                                                                {Utilities.numberWithCommas(item.entry_fee)}
                                                            </React.Fragment>
                                                            : AL.FREE
                                                    }
                                                </a>
                                                :
                                                // <a href className="btn btn-primary btn-rounded " onClick={(e)=>this.props.check(e,item)}>
                                                <a href className={"btn btn-primary btn-rounded " + (!Utilities.minuteDiffValueStock({ date: item.game_starts_in }, -5) ? ' disabled' : '') + (item.entry_fee > 0 ? (item.currency_type == 2 ? ' coin-btn' : '') : ' free-btn')}
                                                onClick={Utilities.getMasterData().a_aadhar == 1 ?
                                                    this.props.profileData && this.props.profileData.aadhar_status == "1" ?
                                                        (e) => this.props.check(e, item) :
                                                        () => this.aadharConfirmation() :
                                                    (e) => this.props.check(e, item)
                                                }>
                                                    {
                                                        item.entry_fee > 0 ?
                                                            <React.Fragment>
                                                                {/* {AL.ENTRY} */}
                                                                {
                                                                    item.currency_type == 2 ?
                                                                        <img className="img-coin" alt='' src={Images.IC_COIN} />
                                                                        :
                                                                        <span>
                                                                            {Utilities.getMasterData().currency_code}
                                                                        </span>
                                                                }
                                                                {Utilities.numberWithCommas(item.entry_fee)}
                                                            </React.Fragment>
                                                            : AL.FREE
                                                    }
                                                </a>
                                        }
                                    </div>
                                </div>
                            }
                        </div>
                    </div>
                )
                }
            </MyContext.Consumer>
        )
    }
}

export default SPFixtureCard;