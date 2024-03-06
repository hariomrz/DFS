import React, { lazy, Suspense } from 'react';
import { Modal } from 'react-bootstrap';
import * as AL from "../../helper/AppLabels";
import { _Map } from '../../Utilities/Utilities';
import { MyContext } from '../../InitialSetup/MyProvider';
import { GameType, SELECTED_GAMET } from "../../helper/Constants";
import Images from '../../components/images';
const ReactSlickSlider = lazy(() => import('../CustomComponent/ReactSlickSlider'));
const LFRulesScoringModal = lazy(() => import('./LFRulesScoringModal'));

export default class LFHTP extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            showRules: false,
            mShow: this.props.mShow,
            lData: [
                {
                    title: AL.LTHTP_LB1,
                    step_txt: AL.STEP + ' 1',
                    img: Images.LF_HTP1,
                    step_desc: [
                        AL.LTHTP_TXT11,
                        AL.LTHTP_TXT12,
                        AL.LTHTP_TXT13
                    ]
                },
                {
                    title: AL.LTHTP_LB2,
                    step_txt: AL.STEP + ' 2',
                    img: Images.LF_HTP2,
                    step_desc: [
                        AL.LTHTP_TXT21,
                        AL.LTHTP_TXT22,
                        AL.LTHTP_TXT23
                    ]
                },
                {
                    title: AL.LTHTP_LB3,
                    step_txt: AL.STEP + ' 3',
                    img: Images.LF_HTP3,
                    step_desc: [
                        AL.LTHTP_TXT31,
                        AL.LTHTP_TXT32,
                        AL.LTHTP_TXT33
                    ]
                },
                {
                    title: AL.LTHTP_LB4,
                    step_txt: AL.STEP + ' 4',
                    img: Images.LF_HTP4,
                    step_desc: [
                        AL.LTHTP_TXT41,
                        AL.LTHTP_TXT42
                    ]
                }
            ],
            slideIndexCrt: 0,
            slideIndexNxt: 0,
            updateCount: 0
        };

    }

    gotoRules = () => {
        this.setState({ showRules: true, mShow: false })
    }

    renderItem = (item) => {
        return (
            <div className="section-HTP">
                <div className="top-HTP-sec">
                    <h2>{AL.How_to_Play} {AL.LIVE_FANTASY}?</h2>
                    <div className="img-sec">
                        <img src={item.img} alt="" />
                    </div>
                </div>
                <div className="HTP-step-sec">
                    <div className="step-ct">{item.step_txt}</div>
                    <div className="HTP-step-heading">{item.title}</div>
                    <div className="HTP-step-desc">
                        {
                            item.step_desc.length > 1 ?
                                <ul>
                                    {_Map(item.step_desc, (data, idx) => {
                                        return (
                                            <li> <span></span> {data}</li>
                                        )
                                    })}
                                </ul>
                                :
                                <div className="single-step-desc">
                                    {
                                        item.step_desc[0]
                                    }
                                </div>
                        }
                    </div>
                </div>
            </div>
        )
    }

    render() {

        const { mHide, stockSetting } = this.props;
        const { lData } = this.state;
        let STOCK_SEL_STOCK = AL.STOCK_SEL_STOCK.replace(AL.Team.toLowerCase(), AL.PORTFOLIO.toLowerCase());
        var settings = {
            touchThreshold: 10,
            infinite: false,
            slidesToScroll: 1,
            slidesToShow: 1,
            variableWidth: false,
            initialSlide: 0,
            dots: true,
            centerMode: false,
            className: "center", afterChange: () =>
                this.setState(state => ({ updateCount: state.updateCount + 1 })),
            beforeChange: (current, next) => this.setState({ slideIndexCrt: current, slideIndexNxt: next }, () => {
                
            })

        };
        return (
            <MyContext.Consumer>
                {(context) => (
                    <React.Fragment>
                        <Modal
                            show={this.state.mShow}
                            onHide={mHide}
                            bsSize="large"
                            dialogClassName="stock-htp-modal stock-htp-modal-new live-fant-htp"
                            className="stock-f"
                        >
                            <Modal.Body onClick={mHide}>
                                <a href className="modal-close" onClick={mHide}>
                                    <i className="icon-close"></i>
                                </a>
                                {/* LTHTP */}
                                <div onClick={(e) => e.stopPropagation()} className={"c-view"}>
                                    <Suspense fallback={<div />} ><ReactSlickSlider settings={settings}>
                                        {
                                            _Map(lData, (item, idx) => {
                                                return <>
                                                    {this.renderItem(item)}
                                                    <div className="btm-rules-sec">
                                                        <span> {AL.SEE_LIVE_FANTASY} <a href onClick={() => this.setState({ showRules: true, mShow: false })}>{AL.SCORING_RULES}</a></span>
                                                    </div>
                                                </>
                                            })
                                        }
                                    </ReactSlickSlider></Suspense>
                                </div>
                            </Modal.Body>
                        </Modal>
                        {this.state.showRules &&
                            <LFRulesScoringModal mShow={this.state.showRules} mHide={() => this.setState({ showRules: false }, () => {
                                mHide()
                            })} />
                        }
                    </React.Fragment>
                )}
            </MyContext.Consumer>
        );
    }
}
