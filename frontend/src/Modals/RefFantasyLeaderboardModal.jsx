import React, { lazy, Suspense } from 'react';
import { Modal } from 'react-bootstrap';
import * as AL from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';
import { _Map } from '../Utilities/Utilities';
import * as WSC from "../WSHelper/WSConstants";
import FLRulesModal from '../Component/FantasyRefLeaderboard/FLRulesModal';
const ReactSlickSlider = lazy(() => import('../Component/CustomComponent/ReactSlickSlider'));

export default class RefFantasyLeaderboardModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            showRules: false,
            mShow: this.props.mShow
        };

    }

    gotoRules = () => {
        // this.props.mHide()
        // this.props.history.push('/terms-condition')
        this.setState({ showRules: true, mShow: false })
    }

    renderItem = (item) => {
        return (
            <div key={item.category_id} className='card-vc' >
                <div className="main-heading">{item.category_id == '1' ? AL.REFERRAL : item.category_id == '2' ? AL.FANTASY : item.name} {AL.LEADERBOARD}</div>
                <div className="main-sub-heading">{AL.HOW_IT_WORKS}</div>
                <div className='icon-v' >
                    <i className={item.category_id == '1' ? "icon-share" : "icon-trophy"}></i>
                    {item.category_id == '1' ? <div className='title-v'>
                        {AL.INVIT_FR}<span>{AL.BRING_FR} {WSC.AppName}</span>
                    </div> : item.category_id == '2' ? <div className='title-v'>
                        {AL.JOIN_CONTEST}<span>{AL.PARTICIPATE_L_E}</span>
                    </div> : '--'}
                </div>
                <div className='icon-v' >
                    <i className="icon-step"></i>
                    {item.category_id == '1' ? <div className='title-v'>
                        {AL.MOVE_UP_L}<span>{AL.INCREASE_SIGNUP_COUNT}</span>
                    </div> : item.category_id == '2' ? <div className='title-v'>
                        {AL.MOVE_UP_L}<span>{AL.HIGHS_SCORE_TEAM}</span>
                    </div> : '--'}
                </div>
                <div className='icon-v last' >
                    <i className="icon-my-contests"></i>
                    {item.category_id == '1' ? <div className='title-v'>
                        {AL.GLORY_AWAITS}<span>{AL.WIN_REF_POS}</span>
                    </div> : item.category_id == '2' ? <div className='title-v'>
                        {AL.GLORY_AWAITS}<span>{AL.WIN_F_POINT_POS}</span>
                    </div> : '--'}
                </div>
                <div className="footer-msg">{AL.SEE} {item.category_id == '1' ? AL.REFERRAL : item.category_id == '2' ? AL.FANTASY : item.name} {AL.LEADERBOARD} <a href onClick={this.gotoRules}>{AL.RULES}</a></div>
            </div>
        )
    }

    render() {

        const { mHide, lData } = this.props;

        var settings = {
            touchThreshold: 10,
            infinite: false,
            slidesToScroll: 1,
            slidesToShow: 1,
            variableWidth: true,
            initialSlide: 0,
            dots: false,
            centerMode: true,
            className: "center",
            centerPadding: "15px",
        };

        return (
            <MyContext.Consumer>
                {(context) => (
                    <React.Fragment>
                        <Modal
                            show={this.state.mShow}
                            onHide={mHide}
                            dialogClassName={"custom-modal leaderboard-modal ref" + (lData.length > 1 ? '' : ' single')}
                            className="modal-full-screen overflowy-hidden"
                            animation={false}
                        >
                            <Modal.Body onClick={mHide}>
                                <a href className="modal-close" onClick={mHide}>
                                    <i className="icon-close"></i>
                                </a>
                                <div onClick={(e) => e.stopPropagation()} className="c-view">
                                    <Suspense fallback={<div />} ><ReactSlickSlider settings={settings}>
                                        {
                                            _Map(lData, (item, idx) => {
                                                return this.renderItem(item)
                                            })
                                        }
                                    </ReactSlickSlider></Suspense>
                                </div>
                            </Modal.Body>
                        </Modal>
                        {this.state.showRules &&
                            <FLRulesModal lData={lData} mShow={this.state.showRules} mHide={() => this.setState({ showRules: false }, () => {
                                mHide()
                            })} />
                        }
                    </React.Fragment>
                )}
            </MyContext.Consumer>
        );
    }
}