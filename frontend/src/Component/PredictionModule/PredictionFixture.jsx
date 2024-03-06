import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { MomentDateComponent } from '../CustomComponent';
import { MATCH_TYPE, AppSelectedSport } from '../../helper/Constants';
import { Utilities, parseURLDate } from '../../Utilities/Utilities';
import CountdownTimer from '../../views/CountDownTimer';
import { OverlayTrigger, Tooltip } from 'react-bootstrap';
import * as AL from "../../helper/AppLabels";

class PredictionFixture extends Component {
    constructor(props) {
        super(props)
        this.state = {
            isActive: this.props.isActive || false,
            isLive: false
        }
    }
    UNSAFE_componentWillReceiveProps(nextProps) {
        if (nextProps.isActive != this.props.isActive) {
            this.setState({
                isActive: nextProps.isActive
            })
        }
    }

    timerCallback = () => {
        this.setState({
            isLive: true
        })
    }

    onSelect = (e) => {
        if (e) {
            e.stopPropagation();
            e.preventDefault();
        }
        const { item, onSelect } = this.props;
        if (this.state.isActive) {
            // onSelect('')
        } else {
            onSelect(item)
        }

    }

    renderLeagueName = (item) => {
        return (
            <OverlayTrigger delay={500} trigger={['hover']} placement="bottom" overlay={
                <Tooltip id="tooltip" className="tool-tip-league">
                    <strong>{item.league_name || item.league_abbr}</strong>
                </Tooltip>
            }>
                <div className="match-timing league-n">
                    <div className="leag-name">{item.league_name || item.league_abbr}</div>
                    {
                        AppSelectedSport === '7' && <div> - {MATCH_TYPE[item.format]}</div>
                    }
                </div>
            </OverlayTrigger>
        )
    }

    render() {
        const { item, isSP } = this.props;
        const { isActive, isLive } = this.state;
        let game_starts_in = item.game_starts_in;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <li onClick={this.onSelect} key={item.season_game_uid} className={"slider-fixture-card fixture-card-wrapper prediction-card-wrapper pointer-cursor" + (isActive ? ' active-item' : '')}>

                        <div className="fixture-card-body display-table">
                            <div className="match-info-section">
                                <div className="section-left">
                                    <img src={Utilities.teamFlagURL(item.home_flag)} alt="" className="home-team-flag" />
                                </div>
                                <div className="section-middle">
                                    <div className="team-n-m">
                                        <span className="team-home">{item.home}</span>
                                        <span className="vs-text">{AL.VERSES}</span>
                                        <span className="team-away">{item.away}</span>
                                    </div>
                                    {
                                        (Utilities.showCountDown({ game_starts_in: game_starts_in }) && !isLive) ?
                                            <div className="countdown time-line">
                                                {item.game_starts_in && (Utilities.minuteDiffValue({ date: item.game_starts_in }) <= 0) && <CountdownTimer timerCallback={this.timerCallback} deadlineTimeStamp={item.game_starts_in} />}
                                            </div> :
                                            (Utilities.minuteDiffValue({ date: game_starts_in }) < 0) && <div className="match-timing">
                                                <span> <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM - hh:mm A" }} /></span>
                                            </div>
                                    }
                                    {
                                        (Utilities.minuteDiffValue({ date: game_starts_in }) > 0 || isLive) && <span className="status-live">
                                            <span className="live-indicator" />
                                            <span className="status-text">{AL.LIVE}</span>
                                        </span>
                                    }
                                    {
                                        item.league_name && isSP && this.renderLeagueName(item)
                                    }
                                </div>
                                <div className="section-right">
                                    <img src={Utilities.teamFlagURL(item.away_flag)} alt="" className="away-team-flag" />
                                </div>
                            </div>
                            {
                                item.league_name && !isSP && this.renderLeagueName(item)
                            }
                        </div>
                    </li>
                )}
            </MyContext.Consumer>
        )
    }
}

export default PredictionFixture;