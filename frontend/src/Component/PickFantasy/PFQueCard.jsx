import React from 'react';
import { Col, Row, ProgressBar } from 'react-bootstrap';
import { Utilities, _Map } from '../../Utilities/Utilities';
import * as AL from "../../helper/AppLabels";

export default class PFQueCard extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            showLastState : false
        }
    }

    selAns=(content,opt)=>{
        this.props.selectAns(content,opt)
    }

    showdetail=(data)=>{
        this.props.showDetailFn(data)
    }

    callJsonParser=(data)=>{
        try {
            return JSON.parse(data)
        } catch {
            return data
        }
    }

    ShowProgressBar = (join, total) => {
        return parseInt(join) * 100 / parseInt(total);
    }

    showStatsSection=(optname,item,maxStatsVal, clsnm)=>{
       
        return (
            <div className={`stats-wrap ${clsnm}`}>
                <p>{optname} <span>{item}</span></p>
                <ProgressBar now={this.ShowProgressBar(item, maxStatsVal)} />
            </div>
        )
    }

    getMaxStatsValue=(statsData)=>{
        if(parseInt(statsData.option_3) && parseInt(statsData.option_3) > parseInt(statsData.option_2) && parseInt(statsData.option_3) > parseInt(statsData.option_1)){
            return parseInt(statsData.option_3)
        }
        if(parseInt(statsData.option_2) && parseInt(statsData.option_2) > parseInt(statsData.option_1)){
            return parseInt(statsData.option_2)
        }
        return parseInt(statsData.option_1)
    }

    stateGameShow = () => {
        const {showLastState} = this.state;
        this.setState({showLastState: !showLastState})
    }

    render() {
        const {showLastState
        } = this.state;
        const {
            content,queNo
        } = this.props.data;
        let imgData = this.callJsonParser(content.option_images) || {}
        let statsData = this.callJsonParser(content.option_stats) || {}
        let maxStatsVal = statsData && statsData.option_1 ? this.getMaxStatsValue(statsData) : ''
        return (
            <div className="que-box">
                {
                    content.details &&
                    <>
                        <div className="que-info" onClick={()=>this.showdetail(content)}>
                            <i className={content.showDetail ? "icon-info-solid" : "icon-info"}></i>
                        </div>
                        {
                            content.showDetail &&
                            <div className="que-box-header">
                                <div className="que-info-sec">
                                    {content.details}
                                </div>
                            </div>
                        }
                    </>
                }
                <div className="que-info-body">
                    <div className="que">
                        {queNo}. {" "} {content.questions}
                    </div>
                    {
                        imgData && imgData.option_1 ?
                        <div className={`option-wrap with-img-opt ${imgData.option_3 ? ' ':' dual-opt'}`}>
                            <Row>
                                <Col xs={12} >
                                    <ul>
                                        <li className={`opt opt-img-view ${content.answer == 1 ? ' selected' : ''}`} onClick={()=>this.selAns(content,1)}>
                                            <div className="opt-img-sec">
                                                <img src={Utilities.getPickImg(imgData.option_1)} alt="" />
                                            </div>
                                            <span className='opt-nm'>{content.option_1}</span>
                                        </li>
                                        <li className={`opt opt-img-view ${content.answer == 2 ? ' selected' : ''}`} onClick={()=>this.selAns(content,2)}>
                                            <div className="opt-img-sec">
                                                <img src={Utilities.getPickImg(imgData.option_2)} alt="" />
                                            </div>
                                            <span className='opt-nm'>{content.option_2}</span>
                                        </li>
                                        {
                                            imgData.option_3 &&
                                            <li className={`opt opt-img-view ${content.answer == 3 ? ' selected' : ''}`} onClick={()=>this.selAns(content,3)}>
                                                <div className="opt-img-sec">
                                                    <img src={Utilities.getPickImg(imgData.option_3)} alt="" />
                                                </div>
                                                <span className='opt-nm'>{content.option_3}</span>
                                            </li>
                                        }
                                    </ul>
                                </Col>
                            </Row>
                            {
                                statsData && statsData.option_1 &&
                                <Row>
                                    <Col xs={12} className='opt-stats'>
                                        <div className="last-game-state-view">
                                            <div className="heading">{content.stats_text ?  content.stats_text : AL.LAST_GAME_STATS}</div>
                                            <div className="heading" onClick={() => this.stateGameShow()}><i className={showLastState ? "icon-arrow-up" : "icon-arrow-down" }/></div>
                                        </div>
                                        {showLastState && <>
                                            {
                                            statsData && statsData.option_1 && this.showStatsSection(content.option_1,statsData.option_1, maxStatsVal, 'first')
                                        }
                                        {
                                            statsData && statsData.option_2 && this.showStatsSection(content.option_2,statsData.option_2, maxStatsVal, 'second')
                                        }
                                        {
                                            statsData && statsData.option_3 && this.showStatsSection(content.option_3,statsData.option_3, maxStatsVal, 'third')
                                        }
                                        </> }
                                       
                                        {/* <div className="stats-wrap">
                                            <p>{questions.option_1} <span>{statsData.option_1}</span></p>
                                            <ProgressBar now={this.ShowProgressBar(contest.total_user_joined, contest.minimum_size)} className={parseInt(contest.total_user_joined) >= parseInt(contest.minimum_size) ? '' : 'danger-area'} />
                                        </div> */}
                                    </Col>
                                </Row>
                            }
                        </div>
                        :
                        <div className="option-wrap">
                            <Row>
                                <Col xs={12} onClick={()=>this.selAns(content,1)}>
                                    <div className={`opt ${content.answer == 1 ? ' selected' : ''}`}>
                                        {content.option_1}
                                    </div>
                                </Col>
                            </Row>
                            <Row>
                                <Col xs={12} onClick={()=>this.selAns(content,2)}>
                                    <div className={`opt ${content.answer == 2 ? ' selected' : ''}`}>
                                        {content.option_2}
                                    </div>
                                </Col>
                            </Row>
                            {
                                content.option_3 &&
                                <Row>
                                    <Col xs={12} onClick={()=>this.selAns(content,3)}>
                                        <div className={`opt ${content.answer == 3 ? ' selected' : ''}`}>
                                            {content.option_3}
                                        </div>
                                    </Col>
                                </Row>
                            }
                            {
                                content.option_4 &&
                                <Row>
                                    <Col xs={12} onClick={()=>this.selAns(content,4)}>
                                        <div className={`opt ${content.answer == 4 ? ' selected' : ''}`}>
                                            {content.option_4}
                                        </div>
                                    </Col>
                                </Row>
                            }
                            {
                                statsData && statsData.option_1 &&
                                <Row>
                                    <Col xs={12} className='opt-stats'>
                                        <div className="last-game-state-view mt10">
                                            <div className="heading">{content.stats_text ?  content.stats_text : AL.LAST_GAME_STATS}</div>
                                            <div className="heading" onClick={() => this.stateGameShow()}><i className={showLastState ? "icon-arrow-up" : "icon-arrow-down" }/></div>
                                        </div>
                                        {showLastState && <>
                                            {
                                            statsData && statsData.option_1 && this.showStatsSection(content.option_1,statsData.option_1, maxStatsVal, 'first')
                                        }
                                        {
                                            statsData && statsData.option_2 && this.showStatsSection(content.option_2,statsData.option_2, maxStatsVal, 'second')
                                        }
                                        {
                                            statsData && statsData.option_3 && this.showStatsSection(content.option_3,statsData.option_3, maxStatsVal, 'third')
                                        }
                                        {
                                            statsData && statsData.option_4 && this.showStatsSection(content.option_4,statsData.option_4, maxStatsVal, 'fourth')
                                        }
                                        
                                        </> }
                                       
                                        {/* <div className="stats-wrap">
                                            <p>{questions.option_1} <span>{statsData.option_1}</span></p>
                                            <ProgressBar now={this.ShowProgressBar(contest.total_user_joined, contest.minimum_size)} className={parseInt(contest.total_user_joined) >= parseInt(contest.minimum_size) ? '' : 'danger-area'} />
                                        </div> */}
                                    </Col>
                                </Row>
                            }
                        </div>
                    }
                </div>
            </div>        
        )
    }
}