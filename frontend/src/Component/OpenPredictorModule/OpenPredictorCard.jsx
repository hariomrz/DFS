import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { MomentDateComponent } from '../CustomComponent';
import { Utilities, _Map } from '../../Utilities/Utilities';
import { CONTESTS_LIST, CONTEST_COMPLETED, CONTEST_LIVE, CONTEST_UPCOMING } from '../../helper/Constants';
import { OverlayTrigger, Tooltip } from 'react-bootstrap';
import CountdownTimer from '../../views/CountDownTimer';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";

class OpenPredictorCard extends Component {
    constructor(props) {
        super(props)
        this.state = {
        }
    }

    onPredictionSelect = (itemIndex, idx, opt) => {
        const { item, onSelectPredict, onMakePrediction } = this.props.data;
        onSelectPredict(itemIndex, idx, opt);
        setTimeout(() => {
            onMakePrediction(item)
        }, 50);
    }

    renderFilledBar = (opt, idx, data) => {
        const { item, status, itemIndex } = this.props.data;
        let predictedPer = item.total_predictions == 0 ? 0 : ((opt.option_total_coins / item.total_pool) * 100).toFixed(2);

        let checkPredictedPer = (predictedPer % 1) == 0 ? Math.floor(predictedPer) : predictedPer;

        predictedPer = checkPredictedPer;

        let isOptSelected = (opt.user_selected_option == opt.prediction_option_id);
        let userCorrect = (isOptSelected && opt.is_correct == 1);
        let isCompleted = (status === CONTEST_COMPLETED);
        return (
            <React.Fragment key={idx}>
                <div onClick={() => (status == CONTESTS_LIST && this.onPredictionSelect(itemIndex, idx, opt))} className={
                    "prediction-bar" + (isOptSelected ? ' selected' : '') +
                    ((status != CONTESTS_LIST && !isCompleted && isOptSelected && item.entry_type == 0) ? ' mb-1' : '') +
                    (isCompleted ? (userCorrect ? ' success' : (isOptSelected ? ' failure' : '')) : '')
                }>
                    <div className="filled-bar filled-bar-open" style={{ width: data.entry_type == 1 ? (isOptSelected ? '100%' : '0') : (predictedPer + '%'), animationDelay: (0.05 * idx) + 's' }} />
                    <p className="answer">{opt.option}</p>
                    <div className="corrected-ans corrected-ans-open">
                        {
                            isCompleted && <React.Fragment>
                                {opt.is_correct == 1 && !isOptSelected && <span>{AL.CORRECT_ANS}</span>}
                                {isOptSelected && <i className={userCorrect ? "icon-tick" : "icon-close"} />}
                            </React.Fragment>
                        }
                        {
                            data.entry_type == 0 &&
                            <p>{predictedPer > 0 ? ((predictedPer + '%')) : ''}</p>
                        }
                    </div>
                </div>
                {
                    (status != CONTESTS_LIST && !isCompleted && isOptSelected && item.entry_type == 0) && <div className="estimate-win">
                        <p className="est-price-pool"><img src={Images.IC_COIN} alt="" /><span className="value">
                            {Utilities.kFormatter(item.estimated_winning)}</span> {AL.EST_WIN}
                            <OverlayTrigger rootClose trigger={['click']} placement={'bottom'} overlay={
                                <Tooltip id="tooltip">
                                    <strong>{AL.EST_WIN_FORMULA}</strong>
                                </Tooltip>
                            }>
                                <i className="icon-info" />
                            </OverlayTrigger>
                        </p>
                    </div>
                }
            </React.Fragment>
        )
    }

    viewParticipants = () => {
        const { item, status } = this.props.data;
        let prediction_master_id = item.prediction_master_id;
        let mURL = Utilities.getSelectedSportsForUrl().toLowerCase() + "/open-predictor/participants/" + btoa(prediction_master_id);
        let isLiveCom = ((status == CONTEST_COMPLETED) || (status == CONTEST_LIVE));
        this.props.history.push({ pathname: '/' + mURL, state: { isLeader: isLiveCom } });
    }

    ShowModal = (item) => {
        this.props.data.ShowProofModalFn(item);
    }

    callNativeRedirection(item) {
        let data = {
            action: 'predictionLink',
            targetFunc: 'predictionLink',
            type: 'link',
            url: item.source_url,
            detail: item
        }
        window.ReactNativeWebView.postMessage(JSON.stringify(data));
    }


    render() {
        const { item, status, shareContest, LobbyData, timerCallback, itemIndex } = this.props.data;
        let game_starts_in = item.deadline_time / 1000;
        let betCoin = 0;
        let isCompleted = (status == CONTEST_COMPLETED);
        return (
            <MyContext.Consumer>
                {(context) => (
                    <li style={{animation: (itemIndex > 10 ? 'none' : ''), transition : (itemIndex > 10 ? 'none' : ''), transform: (itemIndex > 10 ? 'rotateY(0deg)' : '') }}  key={item.prediction_master_id + item.season_game_uid} className={parseInt(item.is_pin) != 0 ? ' pinned' : ''}>
                        {/* {status == CONTESTS_LIST && <i onClick={(e) => shareContest(e, item)} className="icon-share" />} */}
                        {
                            parseInt(item.is_pin) != 0 && <div className="contest-pin">
                                <i className="icon-pinned-ic"></i>
                            </div>
                        }
                        {/* {item.source_desc &&
                            <OverlayTrigger rootClose trigger={['click']} placement={'left'} overlay={
                                <Tooltip id="tooltip1">
                                    <strong>{item.source_desc}</strong>
                                </Tooltip>
                            }>
                                <i className="icon-ic-info que-info" />
                            </OverlayTrigger>
                        } */}
                        {
                            item.source_url &&
                            <React.Fragment>
                                {
                                    window.ReactNativeWebView ?
                                        <a
                                            href
                                            onClick={() => this.callNativeRedirection(item)}
                                            className={`attached-url ${(isCompleted ? " d-none" : "" )}`}>
                                            <img src={Images.ATTACHMENT_ICON} alt="" />
                                        </a>
                                        :
                                        <a
                                            href={item.source_url}
                                            target='_blank'
                                            className={`attached-url ${(isCompleted ? " d-none" : "" )}`}>
                                            <img src={Images.ATTACHMENT_ICON} alt="" />
                                        </a>
                                }
                            </React.Fragment>
                        }
                        {
                            // (!LobbyData) && 
                            <span className="category_name">{item.category_name ? item.category_name : LobbyData.category_name }</span>
                        }

                        <p className={"questions open-questions" + (!LobbyData ? ' ' : '')}>{item.desc}</p>
                        
                        {
                            _Map(item.option, (opt, idx) => {
                                betCoin = (opt.user_selected_option == opt.prediction_option_id) ? opt.bet_coins : betCoin
                                return this.renderFilledBar(opt, idx, item);
                            })
                        }
                        {
                            status == CONTESTS_LIST && <div className="footer-vc">
                                <div>
                                    <div className="date-v new-fc">
                                        <div className="match-timing">
                                            {
                                                Utilities.showCountDown({ game_starts_in: game_starts_in }) ?
                                                    <span className="match-timing-time-raimainig">
                                                        <div className="countdown time-line">
                                                            {
                                                                game_starts_in && <CountdownTimer timerCallback={timerCallback} deadlineTimeStamp={game_starts_in} />
                                                            }
                                                        </div>
                                                        <div className='remaining-text'>{AL.REMAINING}</div>
                                                    </span> :
                                                    <span> <MomentDateComponent data={{ date: item.deadline_date, format: "D MMM - hh:mm A " }} /></span>
                                            }
                                        </div>
                                    </div>
                                </div>
                                {
                                    (item.entry_type == 0 && item.prize_pool > 0) &&
                                    <p className="price-pool"><span className="price-pool-first">{AL.WIN}</span><img src={Images.IC_COIN} alt="" />{item.prize_pool}</p>
                                }
                                {
                                    (item.entry_type == 0 && item.prize_pool == 0) &&
                                    <p className="price-pool-first ">{AL.BE_FIRST}</p>
                                }
                                {
                                    item.entry_type == 1 &&
                                    <p className="price-pool"><span className="price-pool-first">{AL.WIN}</span><img src={Images.IC_COIN} alt="" />{item.win_prize}</p>
                                }
                            </div>
                        }
                        {
                            status != CONTESTS_LIST && <div className="footer-vc">
                                <div className="price-container">
                                    <div className="my-pre-date">
                                        {
                                            isCompleted && item.win_coins > 0 && <div className="my-pre-date won">
                                                <p className="price-pool">
                                                    <span className="price-pool-first">{AL.WON}</span>
                                                    <img src={Images.IC_COIN} alt="" />
                                                    {item.win_coins || 0}
                                                </p>
                                            </div>
                                        }
                                        <p className="price-pool">
                                            <span className="price-pool-first text-capitalize">
                                                {
                                                    item.entry_type == 0 ? AL.YOUR_BET : (item.entry_fee > 0 ? AL.Entry_fee : '')
                                                }
                                            </span>
                                            {(item.entry_type == 0 || item.entry_fee > 0) && <img src={Images.IC_COIN} alt="" />}
                                            {item.entry_type == 0 ? betCoin : (item.entry_fee > 0 ? item.entry_fee : AL.FREE_ENTRY)}
                                        </p>
                                        {
                                            status == CONTEST_UPCOMING && <div className="date-v">
                                                <div className="match-timing price-pool-first">
                                                    {
                                                        Utilities.showCountDown({ game_starts_in: game_starts_in }) ?
                                                            <span className="match-timing-time-raimainig">
                                                                <div className="countdown time-line text-left">
                                                                    {
                                                                        game_starts_in && <CountdownTimer timerCallback={timerCallback} deadlineTimeStamp={game_starts_in} />
                                                                    }
                                                                </div>
                                                                <div className='remaining-text'>{AL.REMAINING}</div>
                                                            </span> :
                                                            <span> <MomentDateComponent data={{ date: item.deadline_date, format: "D MMM - hh:mm A " }} /></span>
                                                    }
                                                </div>
                                            </div>
                                        }
                                        {
                                            isCompleted && (item.proof_desc || item.proof_image) &&
                                            <p className="view-proof-section" onClick={() => this.ShowModal(item)}>
                                                {AL.VIEW_PROOF}
                                            </p>
                                        }
                                    </div>
                                </div>
                                <div>
                                    {
                                        item.entry_type == 0 &&
                                        <p className="price-pool"><span className="price-pool-first">{AL.WIN}</span><img src={Images.IC_COIN} alt="" />{item.prize_pool}</p>
                                    }
                                    {
                                        item.entry_type == 1 &&
                                        <p className="price-pool"><span className="price-pool-first">{AL.WIN}</span><img src={Images.IC_COIN} alt="" />{item.win_prize}</p>
                                    }
                                    {
                                        item.entry_type == 0 && <div className="date-v right-side">
                                            <span onClick={this.viewParticipants} className="price-pool-first pointer-cursor font-12">{item.total_predictions} {AL.PREDICTED}</span>
                                        </div>
                                    }
                                </div>
                            </div>
                        }
                    </li>
                )
                }
            </MyContext.Consumer>
        )
    }
}

export default OpenPredictorCard;
