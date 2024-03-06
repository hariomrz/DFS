import React from 'react';
import { PanelGroup,Panel,Row,Col } from 'react-bootstrap';
import { Utilities } from '../../Utilities/Utilities';
import * as AL from "../../helper/AppLabels";

export default class PFViewQueCard extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
        }
    }
    
    renderSelOptonValue=(item,selOpt)=>{
        if(selOpt == 4){
            return item.option_4
        }
        if(selOpt == 3){
            return item.option_3
        }
        if(selOpt == 2){
            return item.option_2
        }
        if(selOpt == 1){
            return item.option_1
        }
    }

    selectBooster=(item,isFor)=>{
        //isFor 0 for doubler, 1 for no negative
        this.props.applyBooster(item,isFor)
    }

    callJsonParser=(data)=>{
        try {
            return JSON.parse(data)
        } catch {
            return data
        }
    }

    render() {
        const {
            addBooster,
            viewPicks,
            que,
            queNo,
            isLeaderboard,
            selOpt,
            picks_data
        } = this.props.data;
        let optionImg = this.callJsonParser(que.option_images) || {}
        return (
            <div className={"pick-card-wrap " + (addBooster ? 'apply-booster-card' : '')}>
                {
                    viewPicks &&
                    <React.Fragment>
                        <div className="picks-header">
                            <div className="left-part">
                                {AL.QUESTION} {queNo}
                            </div>
                            <div className="right-part">
                                {
                                   ( (que.nn && que.nn == 1) || (que.db && que.db == 1)) &&
                                    <React.Fragment>
                                        <span>{AL.POWERUP_USED}</span>
                                        {
                                            que.db && que.db == 1 &&
                                            <i className="icon-2x-point"></i>
                                        }
                                        {
                                            que.nn && que.nn == 1 &&
                                            <i className="icon-nn-ic"></i>
                                        }
                                    </React.Fragment>
                                }
                            </div>
                        </div>

                        <div className={`pick-info-body ${que.explaination ? ' with-detail' : ''}`}>
                            <div className="que">
                                {que.questions}
                            </div>
                            {
                                optionImg && optionImg.option_1 ?
                                <div className={`option-wrap opt-img-wrap`}>
                                    <div className={`opt ${isLeaderboard ? (que.correct_answer != "0" && que.user_answer == 1 ? (que.correct_answer == 1 ? ' correct-sel' : ' incorrect-sel') : (que.answer == 1 ? ' selected' : '')) : que.answer == 1 ? ' selected' : ''}`}>
                                            <div className="opt-img-sec">
                                                <img src={Utilities.getPickImg(optionImg.option_1)} alt="" />
                                            </div>
                                            <span className="opt-text opt-text-new">
                                                {que.option_1}
                                                {isLeaderboard && que.correct_answer == 1 &&
                                                    <span>
                                                        {
                                                            que.is_captain == 1 ?
                                                                <>
                                                                    {
                                                                        que.user_answer == 1 &&
                                                                        <span className='over-score-sec'>
                                                                            <span className='exct-score'>+{que.score} Pts</span>
                                                                            <span className='prv-score'>+{picks_data.correct} Pts</span>
                                                                        </span>
                                                                    }
                                                                    <i className="icon-tick-ic"></i>
                                                                </>
                                                                :
                                                                <>
                                                                    {
                                                                        que.user_answer == 1 &&
                                                                        <span className='scr-txt'>+{que.score} Pts</span>
                                                                    }
                                                                    <i className="icon-tick-ic"></i>
                                                                </>
                                                        }
                                                    </span>
                                                }
                                                {isLeaderboard && que.correct_answer != 1 && que.correct_answer != 0 && que.user_answer == 1 &&
                                                    <>
                                                        {
                                                            que.is_vc == 1 ?
                                                                <span className='over-score-sec'>
                                                                    <span className='wrng-score'>{que.score} Pts</span>
                                                                    <span className='prv-score'>-{picks_data.wrong} Pts</span>
                                                                </span>
                                                                :
                                                                <span className='txt-danger'>{que.score} Pts</span>
                                                        }
                                                    </>
                                                }
                                                {/* {(que.correct_answer == "1" && que.user_answer != "1") && <i className="icon-tick-ic"></i>} */}
                                            </span>
                                    </div>
                                    <div className={`opt ${isLeaderboard ? (que.correct_answer != "0" && que.user_answer == 2 ? (que.correct_answer == 2 ? ' correct-sel' : ' incorrect-sel') : (que.answer == 2 ? ' selected' : '')) : que.answer == 2 ? ' selected' : ''}`}>
                                            <div className="opt-img-sec">
                                                <img src={Utilities.getPickImg(optionImg.option_2)} alt="" />
                                            </div>
                                            <span className="opt-text">
                                                {que.option_2}
                                                {isLeaderboard && que.correct_answer == 2 &&
                                                    <span>
                                                        {
                                                            que.is_captain == 1 ?
                                                                <>
                                                                    {
                                                                        que.user_answer == 2 &&
                                                                        <span className='over-score-sec'>
                                                                            <span className='exct-score'>+{que.score} Pts</span>
                                                                            <span className='prv-score'>+{picks_data.correct} Pts</span>
                                                                        </span>
                                                                    }
                                                                    <i className="icon-tick-ic"></i>
                                                                </>
                                                                :
                                                                <>
                                                                    {
                                                                        que.user_answer == 2 &&
                                                                        <span className='scr-txt'>+{que.score} Pts</span>
                                                                    }
                                                                    <i className="icon-tick-ic"></i>
                                                                </>
                                                        }
                                                    </span>
                                                }
                                                {isLeaderboard && que.correct_answer != 2 && que.correct_answer != 0 && que.user_answer == 2 &&
                                                    <>
                                                        {
                                                            que.is_vc == 1 ?
                                                                <span className='over-score-sec'>
                                                                    <span className='wrng-score'>{que.score} Pts</span>
                                                                    <span className='prv-score'>-{picks_data.wrong} Pts</span>
                                                                </span>
                                                                :
                                                                <span className='txt-danger'>{que.score} Pts</span>
                                                        }
                                                    </>
                                                }
                                                {/* {(que.correct_answer == "2" && que.user_answer != "2") && <i className="icon-tick-ic"></i>} */}
                                            </span>
                                    </div>
                                    {
                                        que.option_3 &&
                                        <div className={`opt ${isLeaderboard ? (que.correct_answer != "0" && que.user_answer == 3 ? (que.correct_answer == 3 ? ' correct-sel' : ' incorrect-sel') : (que.answer == 3 ? ' selected' : '')) : que.answer == 3 ? ' selected' : ''}`}>
                                            <div className="opt-img-sec">
                                                <img src={Utilities.getPickImg(optionImg.option_3)} alt="" />
                                            </div>
                                            <span className="opt-text">
                                                <span>{que.option_3} </span>
                                                {isLeaderboard && que.correct_answer == 3 &&
                                                    <span>
                                                        {
                                                            que.is_captain == 1 ?
                                                                <>
                                                                    {
                                                                        que.user_answer == 3 &&
                                                                        <span className='over-score-sec'>
                                                                            <span className='exct-score'>
                                                                                +{que.score} Pts</span>
                                                                            <span className='prv-score'>+{picks_data.correct} Pts</span>
                                                                        </span>
                                                                    }
                                                                        <i className="icon-tick-ic"></i>
                                                                </>
                                                                :
                                                                <>
                                                                    {
                                                                        que.user_answer == 3 &&
                                                                        <span className='scr-txt'>+{que.score} Pts</span>
                                                                    }
                                                                    <i className="icon-tick-ic"></i>
                                                                </>
                                                        }


                                                        {/* {(que.correct_answer == "3" && que.user_answer != "3") && <i className="icon-tick-ic"></i>} */}
                                                    </span>
                                                }
                                                {isLeaderboard && que.correct_answer != 3 && que.correct_answer != 0 && que.user_answer == 3 &&
                                                    <>
                                                        {
                                                            que.is_vc == 1 ?
                                                                <span className='over-score-sec'>
                                                                    <span className='wrng-score'>{que.score} Pts</span>
                                                                    <span className='prv-score'>-{picks_data.wrong} Pts</span>
                                                                </span>
                                                                :
                                                                <span className='txt-danger-d'>{que.score} Pts</span>
                                                        }
                                                    </>
                                                }
                                            </span>
                                        </div>
                                    }
                                </div>
                                :
                                <div className={`option-wrap`}>
                                    <Row>
                                        <Col xs={12}>
                                            <div className={`opt ${isLeaderboard ? (que.correct_answer != "0" && que.user_answer == 1 ? (que.correct_answer == 1 ? ' correct-sel' : ' incorrect-sel') : (que.answer == 1 ? ' selected' : '')) : que.answer == 1 ? ' selected' : ''}`}>
                                                <span className="que-text">{que.option_1} </span>
                                                {isLeaderboard && que.correct_answer == 1 &&
                                                    <span className='pts-span'>
                                                        {
                                                            que.is_captain == 1 ?
                                                                <>
                                                                    <i className="icon-tick-ic"></i>
                                                                    {
                                                                        que.user_answer == 1 &&
                                                                        <span className='over-score-sec'>
                                                                            <span className='exct-score'>+{que.score} Pts</span>
                                                                            <span className='prv-score'>+{picks_data.correct} Pts</span>
                                                                        </span>
                                                                    }
                                                                </>
                                                                :
                                                                <>
                                                                    <i className="icon-tick-ic"></i>
                                                                    {
                                                                        que.user_answer == 1 &&
                                                                        <span className='succ-txt'>+{que.score} Pts</span>
                                                                    }
                                                                </>
                                                        }
                                                    </span>
                                                }
                                                {isLeaderboard && que.correct_answer != 1 && que.correct_answer != 0 && que.user_answer == 1 &&
                                                    // <span>{que.score} Pts</span>
                                                    <>
                                                        {
                                                            que.is_vc == 1 ?
                                                                <span className='over-score-sec'>
                                                                    <span className='wrng-score'>{que.score} Pts</span>
                                                                    <span className='prv-score'>-{picks_data.wrong} Pts</span>
                                                                </span>
                                                                :
                                                                <span className='txt-danger-d'>{que.score} Pts</span>
                                                        }
                                                    </>
                                                }
                                            </div>
                                        </Col>
                                    </Row>
                                    <Row>
                                        <Col xs={12}>
                                            <div className={`opt ${isLeaderboard ? (que.correct_answer != "0" && que.user_answer == 2 ? (que.correct_answer == 2 ? ' correct-sel' : ' incorrect-sel') : (que.answer == 2 ? ' selected' : '')) : que.answer == 2 ? ' selected' : ''}`}>
                                                {que.option_2}
                                                {isLeaderboard && que.correct_answer == 2 &&
                                                    <span className='pts-span'>
                                                        {/* <i className="icon-tick-ic"></i>
                                                    {
                                                        que.user_answer == 2 && 
                                                        <span>+{que.score} Points</span>
                                                    } */}
                                                        {
                                                            que.is_captain == 1 ?
                                                                <>
                                                                    <i className="icon-tick-ic"></i>
                                                                    {
                                                                        que.user_answer == 2 &&
                                                                        <span className='over-score-sec'>
                                                                            <span className='exct-score'>+{que.score} Pts</span>
                                                                            <span className='prv-score'>+{picks_data.correct} Pts</span>
                                                                        </span>
                                                                    }
                                                                </>
                                                                :
                                                                <>
                                                                    <i className="icon-tick-ic"></i>
                                                                    {
                                                                        que.user_answer == 2 &&
                                                                        <span className='succ-txt'>+{que.score} Pts</span>
                                                                    }
                                                                </>
                                                        }
                                                    </span>
                                                }
                                                {isLeaderboard && que.correct_answer != 2 && que.correct_answer != 0 && que.user_answer == 2 &&
                                                    // <span>{que.score} Points</span>
                                                    <>
                                                        {
                                                            que.is_vc == 1 ?
                                                                <span className='over-score-sec'>
                                                                    <span className='wrng-score'>{que.score} Pts</span>
                                                                    <span className='prv-score'>-{picks_data.wrong} Pts</span>
                                                                </span>
                                                                :
                                                                <span className='txt-danger-d'>{que.score} Pts</span>
                                                        }
                                                    </>
                                                }
                                            </div>
                                        </Col>
                                    </Row>
                                    {
                                        que.option_3 &&
                                        <Row>
                                            <Col xs={12}>
                                                <div className={`opt ${isLeaderboard ? (que.correct_answer != "0" && que.user_answer == 3 ? (que.correct_answer == 3 ? ' correct-sel' : ' incorrect-sel') : (que.answer == 3 ? ' selected' : '')) : que.answer == 3 ? ' selected' : ''}`}>
                                                    {que.option_3}
                                                    {isLeaderboard && que.correct_answer == 3 &&
                                                        <span className='pts-span'>
                                                            {/* <i className="icon-tick-ic"></i>
                                                        {
                                                            que.user_answer == 3 && 
                                                            <span>+{que.score} Points</span>
                                                        } */}
                                                            {
                                                                que.is_captain == 1 ?
                                                                    <>
                                                                        <i className="icon-tick-ic"></i>
                                                                        {
                                                                            que.user_answer == 3 &&
                                                                            <span className='over-score-sec'>
                                                                                <span className='exct-score'>+{que.score} Pts</span>
                                                                                <span className='prv-score'>+{picks_data.correct} Pts</span>
                                                                            </span>
                                                                        }
                                                                    </>
                                                                    :
                                                                    <>
                                                                        <i className="icon-tick-ic"></i>
                                                                        {
                                                                            que.user_answer == 3 &&
                                                                            <span className='succ-txt'>+{que.score} Pts</span>
                                                                        }
                                                                    </>
                                                            }
                                                        </span>
                                                    }
                                                    {isLeaderboard && que.correct_answer != 3 && que.correct_answer != 0 && que.user_answer == 3 &&
                                                        // <span>{que.score} Points</span>
                                                        <>
                                                            {
                                                                que.is_vc == 1 ?
                                                                    <span className='over-score-sec'>
                                                                        <span className='wrng-score'>{que.score} Pts</span>
                                                                        <span className='prv-score'>-{picks_data.wrong} Pts</span>
                                                                    </span>
                                                                    :
                                                                    <span className='txt-danger-d'>{que.score} Pts</span>
                                                            }
                                                        </>
                                                    }
                                                </div>
                                            </Col>
                                        </Row>
                                    }
                                    {
                                        que.option_4 &&
                                        <Row>
                                            <Col xs={12}>
                                                <div className={`opt ${isLeaderboard ? (que.correct_answer != "0" && que.user_answer == 4 ? (que.correct_answer == 4 ? ' correct-sel' : ' incorrect-sel') : (que.answer == 4 ? ' selected' : '')) : que.answer == 4 ? ' selected' : ''}`}>
                                                    {que.option_4}
                                                    {isLeaderboard && que.correct_answer == 4 &&
                                                        <span className='pts-span'>
                                                            {
                                                                que.is_captain == 1 ?
                                                                    <>
                                                                        <i className="icon-tick-ic"></i>
                                                                        {
                                                                            que.user_answer == 4 &&
                                                                            <span className='over-score-sec'>
                                                                                <span className='exct-score'>+{que.score} Pts</span>
                                                                                <span className='prv-score'>+{picks_data.correct} Pts</span></span>
                                                                        }
                                                                    </>
                                                                    :
                                                                    <>
                                                                        <i className="icon-tick-ic"></i>
                                                                        {
                                                                            que.user_answer == 4 &&
                                                                            <span className='succ-txt'>+{que.score} Pts</span>
                                                                        }
                                                                    </>
                                                            }
                                                            {/* <i className="icon-tick-ic"></i>
                                                        {
                                                            que.user_answer == 4 && 
                                                            <span>+{que.score} Points</span>
                                                        } */}
                                                        </span>
                                                    }
                                                    {isLeaderboard && que.correct_answer != 4 && que.correct_answer != 0 && que.user_answer == 4 &&
                                                        // <span>{que.score} Points</span>
                                                        <>
                                                            {
                                                                que.is_vc == 1 ?
                                                                    <span className='over-score-sec'>
                                                                        <span className='wrng-score'>{que.score} Pts</span>
                                                                        <span className='prv-score'>-{picks_data.wrong} Pts</span>
                                                                    </span>
                                                                    :
                                                                    <span className='txt-danger-d'>{que.score} Pts</span>
                                                            }
                                                        </>
                                                    }
                                                </div>
                                            </Col>
                                        </Row>
                                    }
                                </div>
                            }
                        </div>
                        {
                            que.explaination &&
                            <div className="picks-footer">
                                <PanelGroup accordion id="accordion-example">
                                    <Panel eventKey="1">
                                        <Panel.Heading>
                                        <Panel.Title toggle>{AL.ANSWER_EXPLANATION} <i className="icon-arrow-down"></i></Panel.Title>
                                        </Panel.Heading>
                                        <Panel.Body collapsible>
                                            {que.explaination_image && 
                                                <div className="img-blk">
                                                    <img src={Utilities.getPickImg(que.explaination_image)} alt="" />
                                                </div>
                                            }
                                            {que.explaination}
                                        </Panel.Body>
                                    </Panel>
                                </PanelGroup>
                            </div>
                        }
                        {/* {
                            que.details &&
                            <div className="picks-footer">
                                <PanelGroup accordion id="accordion-example">
                                    <Panel eventKey="1">
                                        <Panel.Heading>
                                        <Panel.Title toggle>Answer Explaination <i className="icon-arrow-down"></i></Panel.Title>
                                        </Panel.Heading>
                                        <Panel.Body collapsible>
                                            {que.details}
                                        </Panel.Body>
                                    </Panel>
                                </PanelGroup>
                            </div>
                        } */}
                    </React.Fragment>
                }
                {
                    addBooster &&
                    <React.Fragment>
                        <div className="picks-header">
                            <div className="left-part">
                                {AL.QUESTION} {queNo}
                            </div>
                            <div className="right-part">
                                <div className="que-mark">{AL.CORRECT_TEXT} <span>{que.correct ? que.correct : picks_data.correct} Pts</span></div>
                                <div className="que-mark">{AL.INCORRECT_TEXT}
                                    <span>
                                        {que.wrong ?
                                            <>{parseInt(que.wrong) > 0 && '-' + que.wrong || ' ' + 0}</> :
                                            <>{parseInt(picks_data.wrong) > 0 && '-' + picks_data.wrong || ' ' + 0}</>
                                        } Pts
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div className="picks-body">
                            <div className="pick-que">{que.questions}</div>
                            <div className="booster-sec">
                                <div className="sel-pick">Your pick - <span>{this.renderSelOptonValue(que, selOpt)}</span></div>
                                <a href className={que.nn ? "active" : ''} onClick={() => this.selectBooster(que, 1)}>
                                    <i className="icon-no-negative"></i>
                                </a>
                                <a href className={que.db ? "active" : ''} onClick={() => this.selectBooster(que, 0)}>
                                    <i className="icon-2x-point"></i>
                                </a>
                            </div>
                        </div>
                    </React.Fragment>
                }
            </div>
        )
    }
}
