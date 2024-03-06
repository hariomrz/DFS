import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { Helmet } from "react-helmet";
import { Utilities, _Map } from '../../Utilities/Utilities';
import { getFeedbackQA, saveFeedback } from '../../WSHelper/WSCallings';
import { NoDataView } from '../CustomComponent';
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import CustomHeader from '../../components/CustomHeader';
import MD from "../../helper/MetaData";
import Images from '../../components/images';
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import { DARK_THEME_ENABLE } from "../../helper/Constants";

class FeedbackQA extends Component {
    constructor(props) {
        super(props)
        this.state = {
            FDBLIST: [],
            ISLOAD: false,
            isApiCalling: false,
            sentIndex: -1
        }
    }

    componentDidMount() {
        this.callApiFBQAList();
    }

    callApiFBQAList = () => {
        let param = {}
        this.setState({ ISLOAD: true })
        getFeedbackQA(param).then((responseJson) => {
            this.setState({ ISLOAD: false })
            if (responseJson.response_code === WSC.successCode) {
                let listdata = responseJson.data.questions || [];
                this.setState({
                    FDBLIST: listdata
                })
            }
        })
    }

    btnAction = (value, idx) => {
        if (value.answer && value.answer.length > 1 && !this.state.isApiCalling) {
            let param = {
                feedback_question_id: value.feedback_question_id['$oid'],
                answer: value.answer
            }
            this.setState({ isApiCalling: true, sentIndex: idx });

            Utilities.gtmEventFire('send_feedback', {
                feedback_que_id: value.feedback_question_id['$oid']
            })

            saveFeedback(param).then((responseJson) => {
                if (responseJson.response_code === WSC.successCode) {
                    let tmpArray = this.state.FDBLIST;
                    tmpArray[idx]['submitted'] = true;
                    this.setState({ FDBLIST: tmpArray })
                }
                this.setState({ isApiCalling: false });
            })
        }
    }

    onChangeText = (e) => {
        let value = e.target.value;
        let index = e.target.id;
        let tmpArray = this.state.FDBLIST;
        tmpArray[index]['answer'] = value;
        this.setState({ FDBLIST: tmpArray });
    }

    renderListItem = (item, idx) => {
        let submitted = item.submitted;
        return (
            <li key={idx} >
                {
                    !submitted && <React.Fragment>
                        {item.coins > 0 && <div className="top-price">
                            <span>{AL.GET}<img src={Images.IC_COIN} alt="" />{item.coins}</span>
                            <img className="img-shape" src={Images.COINS_BACK_STRIPE} alt="" />
                        </div>}
                        <p className="feedback-text mb30" >{AL.FEEDBACK}</p>
                        <div className="q-view">
                            <p className="question" >{item.question}</p>
                            <textarea onChange={this.onChangeText} placeholder="Enter your suggestion" rows="4" name="answer" id={idx} className="ans-input"></textarea>
                            <a href className="send-btn" id={"send-btn" + idx} onClick={() => this.btnAction(item, idx)} >
                                <i className={"icon-send" + (this.state.sentIndex === idx ? ' animate' : '')} />
                            </a>
                        </div>
                    </React.Fragment>
                }
                {
                    submitted && <div className="submited-v">
                        <p className="feedback-text m-0 text-left" >{AL.FEEDBACK}</p>
                        <img src={Images.FB_THUMB} alt="" className="thumb-img" />
                        <p className="coin-text">{item.coins > 0 && <><img src={Images.IC_COIN} alt="" /> +{item.coins} {AL.COINS}</>}</p>
                    </div>
                }
                <p className={"hint-text" + (submitted ? " m-0" : '')}>{item.coins > 0 ? AL.FB_HINT : ''}</p>
            </li>
        )
    }

    Shimmer = (index) => {
        return (
            <SkeletonTheme color={DARK_THEME_ENABLE ? "#161920" : null} highlightColor={DARK_THEME_ENABLE ? "#0E2739" : null}>
                <div key={index} className="contest-list border">
                    <div className="shimmer-container">
                        <Skeleton height={9} width={'30%'} />
                        <div className="shimmer-line m-t-20 m-b-sm">
                            <Skeleton height={6} width={'95%'} />
                            <Skeleton height={6} width={'70%'} />
                        </div>
                        <div className="shimmer-image m-b">
                            <Skeleton width={"100%"} height={80} />
                        </div>
                        <Skeleton height={6} width={"80%"} />
                    </div>
                </div >
            </SkeletonTheme>
        )
    }

    render() {
        const { FDBLIST, ISLOAD } = this.state;

        const HeaderOption = {
            back: true,
            title: AL.FEEDBACK,
            isPrimary: DARK_THEME_ENABLE ? false : true,
            notification: true
        }

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container feedback-c">
                        <Helmet titleTemplate={`${MD.template} | %s`}>
                            <title>{MD.ECFEEDBAK.title}</title>
                            <meta name="description" content={MD.ECFEEDBAK.description} />
                            <meta name="keywords" content={MD.ECFEEDBAK.keywords}></meta>
                        </Helmet>
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        <ul className="list-type">
                            {
                                _Map(FDBLIST, (item, idx) => {
                                    return this.renderListItem(item, idx)
                                })
                            }
                            {
                                FDBLIST.length === 0 && !ISLOAD && <NoDataView
                                    BG_IMAGE={Images.no_data_bg_image}
                                    // CENTER_IMAGE={Images.BRAND_LOGO_FULL}
                                    CENTER_IMAGE={Images.NO_DATA_VIEW}
                                    MESSAGE_1={AL.NO_DATA_AVAILABLE}
                                    CLASS='pt40-per'
                                />
                            }
                            {
                                FDBLIST.length === 0 && ISLOAD &&
                                [1, 1, 1, 1, 1, 1].map((item, index) => {
                                    return this.Shimmer(index)
                                })
                            }
                        </ul>
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}

export default FeedbackQA;