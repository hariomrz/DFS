import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { _Map, Utilities } from '../../Utilities/Utilities';
import { getPredictionDetail } from '../../WSHelper/WSCallings';
import { MomentDateComponent } from '../CustomComponent';
import Helmet from 'react-helmet';
import ConfirmPrediction from './ConfirmPrediction';
import MetaData from '../../helper/MetaData';
import WSManager from '../../WSHelper/WSManager';
import CustomHeader from '../../components/CustomHeader';
import CountdownTimer from '../../views/CountDownTimer';
import Images from '../../components/images';
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import { AppSelectedSport, MATCH_TYPE,GameType, DARK_THEME_ENABLE } from '../../helper/Constants';

class SharePrediction extends Component {
    constructor(props) {
        super(props)
        this.state = {
            HOS: {
                back: this.props.history.length > 2,
                fixture: false,
                title: '',
                hideShadow: false,
                isPrimary: DARK_THEME_ENABLE ? false : true
            },
            LData: '',
            detail: '',
            showCP: false
        }
    }

    UNSAFE_componentWillMount() {
        Utilities.setScreenName('PRDSHARE')
        
        WSManager.setShareContestJoin(true);
        WSManager.setPickedGameType(GameType.Pred);
        if (this.props.match && this.props.match.params) {
            const matchParam = this.props.match.params;
            let pmid = atob(matchParam.prediction_master_id)
            this.getDetail(matchParam.season_game_uid, pmid);
        }
    }

    getDetail(season_game_uid, prediction_master_id) {
        let param = {
            "season_game_uid": season_game_uid,
            "prediction_master_id": prediction_master_id
        }
        getPredictionDetail(param).then((responseJson) => {
            if (responseJson.response_code === WSC.successCode) {
                if (responseJson.data.prediction) {
                    this.setState({
                        detail: responseJson.data.prediction[0] || '',
                        LData: responseJson.data.match_data,
                        HOS: {
                            back: this.props.history.length > 2,
                            fixture: true,
                            title: '',
                            hideShadow: false,
                            showColorHeader:true
                        }
                    });
                } else {

                    Utilities.showToast(AL.P_EXP_MSG, 1000);
                    setTimeout(() => {
                        if (this.props.history.length > 2) {
                            this.props.history.goBack();
                        } else {
                            this.gotoLobby()
                        }
                    }, 1000);
                }
            }
        })
    }

    onSelectPredict = (optionIndex, option) => {
        let item = this.state.detail;
        _Map(item['option'], (obj, idx) => {
            if (idx === optionIndex) {
                obj['user_selected_option'] = option.prediction_option_id;
                item['option_predicted'] = option
            } else {
                obj['user_selected_option'] = null;
            }
        })
        this.setState({
            detail: item
        }, () => {
            setTimeout(() => {
                this.onMakePrediction()
            }, 50);
        })
    }

    onMakePrediction = () => {
        if (WSManager.loggedIn()) {
            this.setState({
                showCP: true
            })
        } else {
            this.goToSignup()
        }
    }

    hideCP = () => {
        this.setState({
            showCP: false
        })
    }

    goToSignup = () => {
        const matchParam = this.props.match.params;
        let pathName = '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + "/prediction-details/" + matchParam.season_game_uid + '/' + matchParam.prediction_master_id
        this.props.history.push({
            pathname: '/signup', state: {
                joinContest: true,
                lineupPath: pathName,
                FixturedContest: this.state.LData,
                LobyyData: this.state.LData
            }
        })
    }

    timerCallback = () => {

    }

    gotoLobby = () => {
        this.props.history.push("/lobby#" + Utilities.getSelectedSportsForUrl())
    }

    successAction = () => {
        setTimeout(() => {
            if (this.props.history.length > 2) {
                this.props.history.goBack();
            } else {
                this.gotoLobby()
            }
        }, 1500);
    }

    renderFilledBar = (opt, idx) => {
        const { detail } = this.state;
        let predictedPer = detail.total_predictions === 0 ? 0 : ((opt.option_total_coins / detail.total_pool) * 100).toFixed(2);
        let isOptSelected = (opt.user_selected_option === opt.prediction_option_id);
        return (
            <div key={idx} onClick={() => this.onSelectPredict(idx, opt)} className={"prediction-bar" + (isOptSelected ? ' selected' : '')}>
                <div className="filled-bar" style={{ width: predictedPer + '%', animationDelay: (0.05 * idx) + 's' }} />
                <p className="answer">{opt.option}</p>
                <div className="corrected-ans">
                    <p>{predictedPer > 0 ? (predictedPer + '%') : ''}</p>
                </div>
            </div>
        )
    }


    render() {
        const { HOS, LData, detail, showCP } = this.state;
        let game_starts_in = detail.deadline_time / 1000;
        let betCoin = 0;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container sport-pred prediction-detail-wrap">
                        <img className="bg-c-img" src={Images.CARD_BACK_IMG_DETAIL} alt="" />
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.PRDSHARE.title}</title>
                            <meta name="description" content={MetaData.PRDSHARE.description} />
                            <meta name="keywords" content={MetaData.PRDSHARE.keywords}></meta>
                        </Helmet>
                        <CustomHeader {...this.props} LobyyData={LData} HeaderOption={HOS} />
                        {
                            detail !== '' && <div className="pred-detail-v">
                                <p className="questions mb-3">{detail.desc}</p>
                                {
                                    _Map(detail.option, (opt, idx) => {
                                        betCoin = (opt.user_selected_option === opt.prediction_option_id) ? opt.bet_coins : betCoin
                                        return this.renderFilledBar(opt, idx);
                                    })
                                }
                                <div className="footer-vc">
                                    <div className="match-timing league-n">
                                        <div className="leag-name">{LData.league_name || LData.league_abbr}</div>
                                        {
                                            AppSelectedSport === '7' && <div> - {MATCH_TYPE[LData.format]}</div>
                                        }
                                    </div>
                                    <div>
                                        {
                                            detail.prize_pool > 0 && <p className="price-pool"><span className="price-pool-first">{AL.PRIZE_POOL}</span><img src={Images.IC_COIN} alt="" />{Utilities.numberWithCommas(detail.prize_pool)}</p>
                                        }
                                        {
                                            detail.prize_pool === 0 && <p className="price-pool-first">{AL.BE_FIRST}</p>
                                        }
                                        <div className="date-v">
                                            <div className="match-timing">
                                                {
                                                    Utilities.showCountDown({ game_starts_in: game_starts_in }) ?
                                                        <span className="d-flex">
                                                            <div className="countdown time-line">
                                                                {
                                                                    game_starts_in && <CountdownTimer timerCallback={this.timerCallback} deadlineTimeStamp={game_starts_in} />
                                                                }
                                                            </div>
                                                            {AL.REMAINING}
                                                        </span> :
                                                        <span> <MomentDateComponent data={{ date: detail.deadline_date, format: "D MMM - hh:mm A " }} /></span>
                                                }
                                            </div>

                                        </div>
                                    </div>
                                    <div style={{bottom:50}}  className="lobby-go">
                                        
                                        {<div className='lobby-text'  onClick={()=>this.gotoLobby()}>{AL.GO_TO_LOBBY}</div>}
                                    </div>
                                </div>
                            </div>
                        }
                        {
                            showCP && <ConfirmPrediction {...this.props} preData={{
                                mShow: showCP,
                                mHide: this.hideCP,
                                cpData: detail,
                                successAction: this.successAction
                            }} />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}

export default SharePrediction;
