import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { MomentDateComponent } from '../CustomComponent';
import { Utilities, _Map } from '../../Utilities/Utilities';
import { CONTESTS_LIST, CONTEST_COMPLETED, CONTEST_LIVE } from '../../helper/Constants';
import { OverlayTrigger, Tooltip } from 'react-bootstrap';
import CountdownTimer from '../../views/CountDownTimer';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import WSManager from '../../WSHelper/WSManager';

class PredictionCardGameCenter extends Component {
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

    renderGameCenterHeader=(item,pData)=>{
    let fillTab = this.props.data.pData.user_predicted && this.props.data.pData.user_predicted.length > 0 ? true :false

        return(
            <React.Fragment>
                <div className='header-game-center-pred'>
                    <div className='coin-pred-play-container'>
                        <div className='prediction-play-inner'>
                            <div className='count-played'>{pData && pData.user_predicted && pData.user_predicted.length ? pData.user_predicted.length:0}</div>
                            <div className='prediction-played-label'>{AL.PREDICTION_PLAYED}</div>

                        </div>
                        <div className='vertical-line'></div>
                        <div className='coin-balance-container'>
                            <div className='coins-balance'>{AL.COINS_BALANCE}</div>
                            <div className='coin-value'>
                            <img style={{width:14,height:14}} className="coin-img" src={Images.IC_COIN} alt="" />
                                {WSManager.getBalance().point_balance || 0}
                                </div>

                        </div>
                    </div>
                    {
                        fillTab ? 
                        <div className="player-count-slider">
                        {this.showSlider()}
                    </div> 
                    :
                    <div className='horizontal-line'></div>

                    }
                                       
                    {/* <div onClick={(e) => this.skipNext(item)} className='skip-next'>{AL.SKIP_AND_NEXT}</div> */}
                </div>
            </React.Fragment>
        )
    }

    showSlider = () => {
        let i = 0;
        let tempArry = [];
        let userP= this.props.data.pData.user_predicted &&  this.props.data.pData.user_predicted
       // let userP= [{"prediction_master_id":176,"is_correct":1,"status":2},{"prediction_master_id":170,"is_correct":0,"status":2},{"prediction_master_id":175,"is_correct":0,"status":0},{"prediction_master_id":169,"is_correct":0,"status":2}]

        let finalArrayUserP = userP && userP.sort((a, b) => (this.state.sort_order == 'ASC' ? a.is_correct - b.is_correct : b.is_correct - a.is_correct))
        let finalArray = finalArrayUserP && finalArrayUserP.sort((a, b) => (this.state.sort_order == 'ASC' ? a.status - b.status : b.status - a.status))

        let finalArrayList=[...finalArray]
        let divStyle = { width: `calc(100%/${finalArrayList.length})` };
        for (i; i < finalArrayList.length; i++) {
            let data =finalArrayList[i]
            tempArry.push(
                <div  onClick={()=> this.OnItemClick(data)} key={i}  className={ (data.status == 2 && data.is_correct == 1 ? "active" : data.status == 2 &&  data.is_correct == 0 ? ' active-sell' :'')   } style={divStyle} >
                    <span>{i + 1}</span>
                </div>
            )
        }
        return tempArry;
    }
    OnItemClick =(data)=>{
        if( data.status == 0){
            Utilities.showToast(AL.RESULT_PENDING,3000, Images.PREDICTION_IC)
        }
     
    }
    skipNext=(item)=>{
        this.props.data.timerCallback(item)
    }
    renderFilledBar = (opt, idx) => {
        const { item, status, itemIndex } = this.props.data;
        let predictedPer = item.total_predictions == 0 ? 0 : ((opt.option_total_coins / item.total_pool) * 100).toFixed(2);
        let isOptSelected = (opt.user_selected_option == opt.prediction_option_id);
        let userCorrect = (isOptSelected && opt.is_correct == 1);
        let isCompleted = (status === CONTEST_COMPLETED);
        return (
            <React.Fragment key={idx}>
                <div onClick={() => (status == CONTESTS_LIST && this.onPredictionSelect(itemIndex, idx, opt))} className={
                    "prediction-bar gc-margin" + (isOptSelected ? ' selected' : '') +
                    ((status != CONTESTS_LIST && !isCompleted && isOptSelected) ? ' mb-1' : '') +
                    (isCompleted ? (userCorrect ? ' success' : (isOptSelected ? ' failure' : '')) : '')
                }>
                    <div className="filled-bar" style={{ width: predictedPer + '%', animationDelay: (0.05 * idx) + 's' }} />
                    <p className="answer">{opt.option}</p>
                    <div className="corrected-ans">
                        {
                            isCompleted && <React.Fragment>
                                {opt.is_correct == 1 && !isOptSelected && <span>{AL.CORRECT_ANS}</span>}
                                {isOptSelected && <i className={userCorrect ? "icon-tick" : "icon-close"} />}
                            </React.Fragment>
                        }
                        <p>{predictedPer > 0 ? (predictedPer + '%') : ''}</p>
                    </div>
                </div>
                {
                    (status != CONTESTS_LIST && !isCompleted && isOptSelected) && <div className="estimate-win">
                        <p className="est-price-pool"><img src={Images.IC_COIN} alt="" /><span className="value">
                            {Utilities.numberWithCommas(Utilities.kFormatter(item.estimated_winning))}</span> {AL.EST_WIN}
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
        let mURL = Utilities.getSelectedSportsForUrl().toLowerCase() + "/prediction/participants/" + btoa(prediction_master_id);
        let isLiveCom = ((status == CONTEST_COMPLETED) || (status == CONTEST_LIVE));
        this.props.history.push({ pathname: '/' + mURL, state: { isLeader: isLiveCom } });
    }

    render() {
        const { pData,item, status, timerCallback, onMakePrediction, shareContest } = this.props.data;
        let game_starts_in = item.deadline_time / 1000;
        let betCoin = 0;
        let isCompleted = (status == CONTEST_COMPLETED);
        return (
            <MyContext.Consumer>
                {(context) => (
                    <li style={{padding:0}} key={item.prediction_master_id + item.season_game_uid} className={parseInt(item.is_pin) != 0 ? ' pinned':''}>
                        {
                            this.renderGameCenterHeader(item,pData)
                        }
                        {/* {status == CONTESTS_LIST && <i onClick={(e) => shareContest(e, item)} className="icon-share" />} */}
                        {
                            parseInt(item.is_pin) != 0 && <div className="contest-pin">
                                 {/* <img src={Images.pinned_ic} alt="" /> */}
                                 <i className="icon-pinned-ic"></i>
                            </div>
                        }
                        <p className="questions gc">{item.desc}</p>
                        {
                            _Map(item.option, (opt, idx) => {
                                betCoin = (opt.user_selected_option == opt.prediction_option_id) ? opt.bet_coins : betCoin
                                return this.renderFilledBar(opt, idx);
                            })
                        }
                        {
                            status == CONTESTS_LIST && <div className="footer-vc gc">
                                <div>
                                    <div className="date-v new-fc">
                                        <div className="match-timing">
                                            {
                                                Utilities.showCountDown({ game_starts_in: game_starts_in }) ?
                                                    <span className="d-flex">
                                                        <i className="icon-stop-watch"></i>
                                                        <div className="countdown time-line">
                                                            {
                                                                game_starts_in && <CountdownTimer timerCallback={timerCallback} deadlineTimeStamp={game_starts_in} />
                                                            }
                                                        </div>
                                                    {/* {AL.REMAINING} */}
                                                    </span> :
                                                    <span> <MomentDateComponent data={{ date: item.deadline_date, format: "D MMM - hh:mm A " }} /></span>
                                            }
                                            
                                        </div>
                                    </div>
                                </div>
                                {
                                    item.prize_pool > 0 && <p className="price-pool"><span className="price-pool-first">{AL.PRIZE_POOL}</span><img src={Images.IC_COIN} alt="" /><span className='coin-number-text'>{Utilities.numberWithCommas(item.prize_pool)}</span></p>
                                }
                                {
                                    item.prize_pool == 0 && <p className="price-pool-first">{AL.BE_FIRST}</p>
                                }

                            </div>
                        }
                        <div className='skip-next-view' > 
                        <div onClick={(e) => this.skipNext(item)} className='skip-next'>{AL.SKIP_AND_NEXT}</div></div>
                        {
                            status != CONTESTS_LIST && <div className="footer-vc">
                                <div>
                                    <p className="price-pool">{AL.PRIZE_POOL}<img src={Images.IC_COIN} alt="" />{Utilities.numberWithCommas(item.prize_pool)}</p>
                                    <div className="date-v">
                                        <span onClick={this.viewParticipants} className="match-timing view-part">{item.total_predictions} {AL.PREDICTED}</span>
                                    </div>
                                </div>
                                <div className="price-container">
                                    {
                                        isCompleted && item.win_coins > 0 && <div className="my-pre-date won">
                                            <p className="price-pool"><img src={Images.IC_COIN} alt="" />{item.win_coins || 0}</p>
                                            <div className="date-v">
                                                <span className="match-timing view-part">{AL.WON}</span>
                                            </div>
                                        </div>
                                    }
                                    <div className="my-pre-date">
                                        <p className="price-pool"><img src={Images.IC_COIN} alt="" />{Utilities.numberWithCommas(betCoin)}</p>
                                        <div className="date-v">
                                            <span className="match-timing view-part">{AL.YOUR_BET}</span>
                                        </div>
                                    </div>
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

export default PredictionCardGameCenter;