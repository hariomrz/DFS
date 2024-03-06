import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { Modal } from 'react-bootstrap';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import ls from 'local-storage';
import { Swipeable } from 'react-swipeable';

import Highcharts from 'highcharts';
import HighchartsReact from 'highcharts-react-official';
import highcharts3d from "highcharts/highcharts-3d";
highcharts3d(Highcharts);


class CMStkRosterEqModal extends Component {
    constructor(props) {
        super(props)
        this.state = {
            ANMTC: '',
            indexPage: 10,//0,
            showStats: false,
            sourcePieData:{}
        }
    }

    componentWillMount() {{
        this.setChartData()
    }}

    componentDidMount() {
        ls.set('stkeq-roster', 1)

        setTimeout(() => {
            this.setState({ ANMTC: "animate-v" });
        }, 100);
    }
    hideCoachMark = () => {
        ls.set('stkeq-roster', 1)
        this.props.cmData.mHide();
    }
    showPreviouPage = (e, index) => {
        e.stopPropagation();
        this.setState({ indexPage: index })

    }
    showNextPage = (e, index) => {
        e.stopPropagation();
        this.setState({ indexPage: index })


    }
    circleTransitions = (e, index) => {
        e.stopPropagation();
        this.setState({ indexPage: index })

    }
    onSwiped = (eventData) => {
        if (eventData && eventData.dir === "Left") {
            if (this.state.indexPage == 10) {
                this.setState({ indexPage: 0 })
            }
            else if (this.state.indexPage == 0) {
                this.setState({ indexPage: 1 })
            }
            else if (this.state.indexPage == 1) {
                this.setState({ indexPage: 2 })
            }
        }
        if (eventData && eventData.dir === "Right") {
            if (this.state.indexPage == 2) {
                this.setState({ indexPage: 1 })
            }
            else if (this.state.indexPage == 1) {
                this.setState({ indexPage: 0 })
            }
            else if (this.state.indexPage == 0) {
                this.setState({ indexPage: 10 })
            }
        }
    }
    setChartData = (eventData) => {
        this.setState({sourcePieData:{
            chart: {
              type: 'pie',
              options3d: {
                enabled: true,
                alpha: 45,
                beta: 0
              }
            },
            title: {
              text: ''
            },
            accessibility: {
              point: {
                valueSuffix: ''
              }
            },
            tooltip: {
              pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            plotOptions: {
              pie: {
                size:200,
                innerSize: 100,
                allowPointSelect: true,
                cursor: 'pointer',
                depth: 35,
                dataLabels: {
                  enabled: true,
                  format: '{point.name}',

                }
              }
            },
            credits: {
              enabled: false,
            },
            series: [{
              type: 'pie',
              name: 'Stocks',
              data: [
                  {
                    'action': 3,
                    'color': "#D8D8D8",
                    'name': "Unused",
                    'y': 236890.3
                  },
                  {
                    'action': 2,
                    'color': "#EB4A3C",
                    'name': "AXIS BANK",
                    'y': 27426
                  },
                  {
                    'action': 2,
                    'color': "#EB4A3C",
                    'name': "DIVISLAB",
                    'y': 28579.800000000003
                  },
                  {
                    'action': 2,
                    'color': "#E0B4A3C",
                    'name': "INDUSINDBK",
                    'y': 33365.9
                  },
                  {
                    'action': 1,
                    'color': "#5DBE7D",
                    'name': "ADANIPORTS",
                    'y': 25834.9
                  },
                  {
                    'action': 1,
                    'color': "#5DBE7D",
                    'name': "BHEL",
                    'y': 29820
                  },
                  {
                    'action': 1,
                    'color': "#5DBE7D",
                    'name': "BRITANNIA",
                    'y': 25635.05
                  },
                  {
                    'action': 1,
                    'color': "#5DBE7D",
                    'name': "CIPLA",
                    'y': 31871
                  },
                  {
                    'action': 1,
                    'color': "#5DBE7D",
                    'name': "EICHERMOT",
                    'y': 25992
                  },
                  {
                    'action': 1,
                    'color': "#5DBE7D",
                    'name': "ICICIBANK",
                    'y': 34585.1
                  }
                ]
            }]
          }
        })
    }
    render() {
        const { mShow } = this.props.cmData;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        dialogClassName={"contest-detail-modal prop-coin-coachmark coin-coachmark xwallet-coins-coachmark coachmark-modal " + this.state.ANMTC}
                        className="contest-detail-dialog"
                        animation={false}
                    >
                        <Modal.Header >

                            <div className='skip-header-view'>

                                {/* <a style={{ left: 20, right: 'auto' }} href onClick={this.hideCoachMark} className="modal-close">
                            </a> */}
                                <span style={{ left: 20 }} onClick={this.hideCoachMark} className='skip-close'>{AL.SKIP_STEP}</span>

                                <div className="walkthrough-circle ">
                                    
                                    <div onClick={(e) => this.circleTransitions(e, 10)} className={"circle circle-one" + (this.state.indexPage == 10 ? ' selected-page' : '')}></div>
                                    <div onClick={(e) => this.circleTransitions(e, 0)} className={"circle circle-one" + (this.state.indexPage == 0 ? ' selected-page' : '')}></div>
                                    <div onClick={(e) => this.circleTransitions(e, 1)} className={"circle circle-two" + (this.state.indexPage == 1 ? ' selected-page' : '')}></div>
                                    <div onClick={(e) => this.circleTransitions(e, 2)} className={"circle circle-three" + (this.state.indexPage == 2 ? ' selected-page' : '')}></div>
                                   
                                </div>
                            </div>
                        </Modal.Header>
                        <Modal.Body>
                            <Swipeable onSwiped={this.onSwiped}>
                                <div className={"v-container xroster-coachmark rstk-cm" + (this.state.indexPage == 1 ? ' CM-second' :
                                    this.state.indexPage == 2 ? ' CM-second CM-sec-new' : this.state.indexPage == 10 ? ' CM-new-first' : ' CM-first')}>
                                    {
                                        this.state.indexPage == 10 &&
                                        <div className="page-new-one">

                                            <div className="bg-highlighter">
                                                <div className="search-stk-sec"> <img  alt='' src={Images.search_dark} className='search-icon'></img>
                                                {AL.SEARCH_STOCK}</div>   
                                            </div>
                                            <div className="image-strip">
                                                <img src={Images.SINGLE_LINE} alt="" className="line-img-prop" />
                                            </div>
                                            <div className="coachmark-heading">{AL.SEARCH_STOCK}</div>
                                            <div className="coachmark-text">
                                                {AL.STK_RS_CM_TEXT10}
                                            </div>
                                            <div className={"navigation-view"}>
                                                <div onClick={(e) => this.showPreviouPage(e, 10)} className={"rectangle-left mr10" + (this.state.indexPage == 10 ? ' is-disable' : ' ')}>
                                                    <span style={{ fontSize: 18 }} class="icon-arrow-left"></span>
                                                </div>
                                                <div onClick={(e) => this.showNextPage(e, 0)} className="rectangle-right">
                                                    <div style={{ fontSize: 18 }} className="icon-arrow-right"></div>
                                                </div>

                                            </div>

                                        </div>
                                    }
                                    {
                                        this.state.indexPage == 0 &&
                                        <div className="page-one">

                                            <div className="bg-highlighter">
                                                <div class="buy-sell-btn"><a class="btn-v-buy"> {AL.BUY}</a><a class="btn-v-sell"> {AL.SELL}</a></div>
                                            </div>
                                            <div className="image-strip">
                                                <img src={Images.SINGLE_LINE} alt="" className="line-img-prop" />
                                            </div>
                                            <div className="coachmark-heading">{AL.STK_RS_CM_LABEL1}</div>
                                            <div className="coachmark-text">
                                                {AL.STK_RS_CM_TEXT11} <br/>
                                                {AL.STK_RS_CM_TEXT12} <br/>
                                                {AL.STK_RS_CM_TEXT1}
                                            </div>
                                            <div className={"navigation-view"}>
                                                <div onClick={(e) => this.showPreviouPage(e, 10)} className="rectangle-left mr10">
                                                    <span style={{ fontSize: 18 }} class="icon-arrow-left"></span>
                                                </div>
                                                <div onClick={(e) => this.showNextPage(e, 1)} className="rectangle-right">
                                                    <div style={{ fontSize: 18 }} className="icon-arrow-right"></div>
                                                </div>

                                            </div>

                                        </div>
                                    }

                                    {
                                        this.state.indexPage == 1 &&
                                        <div className="page-two">
                                            <div className="bg-highlighter">
                                                <div className="bid-share-val-sec">
                                                    <div className="header">
                                                        <div className="header-crlc">
                                                            <div className="img-sec">
                                                                <img src={Images.STK_ICICI_IMG} alt="" />
                                                            </div>
                                                        </div>
                                                        <div className="share-nm">ICICI</div>
                                                        <div className="share-val">₹1,000.00</div>
                                                    </div>
                                                    <div className="share-inp">
                                                        <div className="left-sec">
                                                            <div className="lbl">{AL.ENTER_NUMBER_OF_SHARE}</div>
                                                            <div className="val">₹25,000</div>
                                                        </div>
                                                        <div className="right-sec">
                                                            <div className="lbl"> {AL.HAS_OF_SHARES}</div>
                                                            <div className="val"><span>25</span></div>
                                                        </div>
                                                    </div>
                                                    <div className="sb-text">{AL.BUY_SHARE_BETWEEN} ₹25,000 - ₹1,00,000</div>
                                                    <div className="trs-rem-sec">
                                                        <div className="trs-sec">
                                                            <div className="lbl">{AL.VALUE_OF_TRANSACTION}</div>
                                                            <div className="val">₹24,998</div>
                                                        </div>
                                                        <div className="rem-sec">
                                                            <div className="lbl">{AL.REMAINING_BUDGET}</div>
                                                            <div className="val">₹4,75,002</div>
                                                        </div>
                                                    </div>
                                                    <div className="btn">Done</div>
                                                </div>
                                            </div>

                                            <div className="image-strip">
                                                <img src={Images.SINGLE_LINE} alt="" className="line-img-prop" />
                                            </div>
                                            <div className="coachmark-heading">{AL.STK_RS_CM_LABEL2}</div>
                                            <div className="coachmark-text">{AL.STK_RS_CM_TEXT2}</div>

                                            <div className="navigation-view xnavigation-view-two">
                                                <div onClick={(e) => this.showPreviouPage(e, 0)} className="xrectangle-left-two rectangle-left mr10" >
                                                    <span style={{ fontSize: 18 }} class="icon-arrow-left"></span>
                                                </div>
                                                <div onClick={(e) => this.showNextPage(e, 2)} className="xrectangle-right-two rectangle-right">
                                                    <div style={{ fontSize: 18 }} className="icon-arrow-right"></div>
                                                </div>

                                            </div>
                                            


                                        </div>
                                    }

                                    {
                                        this.state.indexPage == 2 &&
                                        <div className="page-three">
                                            <div className="bg-highlighter"> 
                                            <div className="overlay-sec"></div>
                                            <div className="bid-share-val-sec">
                                                    <div className="header">
                                                        <div className="header-crlc">
                                                            <div className="img-sec">
                                                                <img src={Images.STK_ICICI_IMG} alt="" />
                                                            </div>
                                                        </div>
                                                        <div className="share-nm">ICICI</div>
                                                        <div className="share-val">₹1000.00</div>
                                                    </div>
                                                    <div className="share-inp">
                                                        <div className="left-sec">
                                                            <div className="lbl">{AL.ENTER_NUMBER_OF_SHARE}</div>
                                                            <div className="val">₹25,000</div>
                                                        </div>
                                                        <div className="right-sec">
                                                            <div className="lbl"> {AL.HAS_OF_SHARES}</div>
                                                            <div className="val"><span>25</span></div>
                                                        </div>
                                                    </div>
                                                    <div className="sb-text">{AL.BUY_SHARE_BETWEEN} ₹25,000 - ₹1,00,000</div>
                                                    <div className="trs-rem-sec">
                                                        <div className="trs-sec">
                                                            <div className="lbl">{AL.VALUE_OF_TRANSACTION}</div>
                                                            <div className="val">₹24,998</div>
                                                        </div>
                                                        <div className="rem-sec">
                                                            <div className="lbl">{AL.REMAINING_BUDGET}</div>
                                                            <div className="val">₹4,75,002</div>
                                                        </div>
                                                    </div>
                                                    <div className="btn">Done</div>
                                                </div>
                                            </div>

                                            <div className="image-strip">
                                                <img src={Images.SINGLE_LINE} alt="" className="line-img-prop" />
                                            </div>
                                            <div className="coachmark-heading">{AL.STK_LB_CM_LABEL13}</div>
                                            <div className="coachmark-text">{AL.STK_RS_CM_TEXT13}</div>


                                            <div className="navigation-view">
                                                <div onClick={(e) => this.showPreviouPage(e, 1)} className="rectangle-left">
                                                    <span style={{ fontSize: 18 }} class="icon-arrow-left"></span>
                                                </div>
                                                {/* <div onClick={(e) => this.showPreviouPage(e, 3)} className={"rectangle-right"}>
                                                    <div style={{ fontSize: 18 }} className="icon-arrow-right"></div>
                                                </div> */}

                                            </div>
                                            <div className="bottomView">
                                                <div className="preview-bg">
                                                    <div onClick={this.hideCoachMark} className="innerbox-preview">
                                                        <div className="team-preview-text">{AL.STK_LB_BTN_LABEL13}</div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    }


                                </div>
                            </Swipeable>

                        </Modal.Body>
                    </Modal>
                )}
            </MyContext.Consumer>
        )
    }
}

export default CMStkRosterEqModal;
