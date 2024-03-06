import React, { Component } from 'react';
import { OverlayTrigger, Tooltip } from 'react-bootstrap';
import { MyContext } from '../../views/Dashboard';
import { _Map, Utilities } from '../../Utilities/Utilities';
import { getFPPOpenPredictionDetail } from '../../WSHelper/WSCallings';
import { MomentDateComponent } from '../CustomComponent';
import Helmet from 'react-helmet';
import ConfirmFPPOpenPredictor from './ConfirmFPPOpenPredictor';
import MetaData from '../../helper/MetaData';
import WSManager from '../../WSHelper/WSManager';
import CustomHeader from '../../components/CustomHeader';
import CountdownTimer from '../../views/CountDownTimer';
import Images from '../../components/images';
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import { AppSelectedSport, MATCH_TYPE , GameType, DARK_THEME_ENABLE} from '../../helper/Constants';

class ShareFPPOpenPredictor extends Component {
    constructor(props) {
        super(props)
        this.state = {
            HOS: {
                back: this.props.history.length > 2,
                fixture: false,
                title: '',
                hideShadow: false,
                MLogo: true,
                isPrimary: DARK_THEME_ENABLE ? false : true
            },
            LData: '',
            detail: '',
            showCP: false,
            sourceUrlShow: false
        }
    }

    UNSAFE_componentWillMount() {
        Utilities.setScreenName('PRDSHARE')
        
        WSManager.setShareContestJoin(true);
        WSManager.setPickedGameType(GameType.OpenPred);
        if (this.props.match && this.props.match.params) {
            const matchParam = this.props.match.params;
            let pmid = atob(matchParam.prediction_master_id)
            this.getDetail(matchParam.category_id, pmid);
        }
    }

    getDetail(category_id, prediction_master_id) {
        let param = {
            "category_id": category_id,
            "prediction_master_id": prediction_master_id
        }
        getFPPOpenPredictionDetail(param).then((responseJson) => {
            if (responseJson.response_code === WSC.successCode) {
                if (responseJson.data.prediction) {
                    this.setState({
                        detail: responseJson.data.prediction[0] || '',
                        LData: responseJson.data.category_data,
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
        let pathName = '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + "/open-predictor-leaderboard-details/" + matchParam.category_id + '/' + matchParam.prediction_master_id
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
        this.props.history.push("/lobby#" + Utilities.getSelectedSportsForUrl() + "#open-predictor-leaderboard")
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
        let checkPredictedPer = (predictedPer % 1) === 0 ? Math.floor(predictedPer) : predictedPer;
        predictedPer = checkPredictedPer;
        let isOptSelected = (opt.user_selected_option === opt.prediction_option_id);
        return (
            <div key={idx} onClick={() => this.onSelectPredict(idx, opt)} className={"prediction-bar" + (isOptSelected ? ' selected' : '')}>
                <div className="filled-bar" style={{ width: detail.entry_type == 1 ? (isOptSelected ? '100%' : '0') : predictedPer + '%', animationDelay: (0.05 * idx) + 's' }} />
                <p className="answer">{opt.option}</p>
                {
                    detail.entry_type == 0 &&
                    <div className="corrected-ans">
                        <p>{predictedPer > 0 ? (predictedPer + '%') : ''}</p>
                    </div>
                }
            </div>
        )
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
        const { HOS, LData, detail, showCP } = this.state;
        let game_starts_in = detail.deadline_time / 1000;
        let betCoin = 0;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container prediction-detail-wrap">
                        <img className="bg-c-img" src={Images.OPEN_CARD_IMG_DETAIL} alt="" />
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.PRDSHARE.title}</title>
                            <meta name="description" content={MetaData.PRDSHARE.description} />
                            <meta name="keywords" content={MetaData.PRDSHARE.keywords}></meta>
                        </Helmet>
                        <CustomHeader {...this.props} LobyyData={LData} HeaderOption={HOS} openPage={this.openPage} />
                        {
                            detail !== '' && <div className="pred-detail-v ">
                                <p className="questions">
                                    {detail.desc}
                                </p>
                                {
                                    (detail.source_desc || detail.source_url) &&
                                    <div className="que-desc">
                                        {
                                            detail.source_url &&
                                            <React.Fragment>
                                                {
                                                    window.ReactNativeWebView ?
                                                        <a
                                                            href
                                                            onClick={() => this.callNativeRedirection(detail)}
                                                            className="attached-url">
                                                            <i className="icon-link"></i>
                                                        </a>
                                                        :
                                                        <a
                                                            href={detail.source_url}
                                                            target='_blank'
                                                            className="attached-url ">
                                                            <i className="icon-link"></i>
                                                        </a>
                                                }
                                            </React.Fragment>
                                        }

                                        {
                                            detail.source_desc &&
                                            <OverlayTrigger rootClose trigger={['click']} placement={'right'} overlay={
                                                <Tooltip id="tooltip">
                                                    <strong>{detail.source_desc}</strong>
                                                </Tooltip>
                                            }>
                                                <i className="icon-ic-info que-info" />
                                            </OverlayTrigger>
                                        }

                                    </div>
                                }
                                {
                                    _Map(detail.option, (opt, idx) => {
                                        betCoin = (opt.user_selected_option === opt.prediction_option_id) ? opt.bet_coins : betCoin
                                        return this.renderFilledBar(opt, idx);
                                    })
                                }
                                <div className="footer-vc">
                                    <div className="match-timing league-n">
                                        <div className="leag-name">{LData.name}</div>
                                    </div>
                                    <div>
                                        
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
                                    <div className="lobby-go">
                                        
                                        {this.props.history.length <= 2 && <a href onClick={this.gotoLobby}>{AL.GO_TO_LOBBY}</a>}
                                    </div>
                                </div>
                            </div>
                        }
                        {
                            showCP && <ConfirmFPPOpenPredictor {...this.props} preData={{
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

export default ShareFPPOpenPredictor;
