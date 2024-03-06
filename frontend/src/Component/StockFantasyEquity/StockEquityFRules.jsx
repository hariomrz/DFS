import React from 'react';
import { Tabs, Tab } from 'react-bootstrap';
import { Modal } from 'react-bootstrap';
import { _Map, Utilities } from "../../Utilities/Utilities";
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import { IsDynamicStockRules } from '../../helper/Constants';
import { MomentDateComponent } from '../CustomComponent';
import { getStockLobbySetting } from "../../WSHelper/WSCallings";
import WSManager from "../../WSHelper/WSManager";

class StockEquityFRules extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
        };

    }

    componentDidMount() {
        if(WSManager.getStockSetting()){
            this.setState({ stockSetting: WSManager.getStockSetting() })
        }
        else{
            getStockLobbySetting().then((responseJson) => {
                let data = responseJson.data
                WSManager.setStockSetting(data)
                this.setState({ stockSetting: responseJson.data })
            })
        }
    }

    showPointSystem = () => {
        return (
            <div className="pts-sys-sec">
                <div className="buy-box">{AL.BUY}</div>
                <div className="bold-text">
                    {AL.STCK_PS_1} "<span className="succ-text">{AL.STCK_PS_2}</span>" {AL.STCK_PS_4}
                </div>
                <div>
                    <span className="bold-text">+1</span> {AL.STCK_PS_5} <span className="bold-text">0.01%</span>
                </div>
                <div>
                    <span className="bold-text">-1</span> {AL.STCK_PS_6} <span className="bold-text">0.01%</span> {AL.STCK_PS_7} <span className="bold-text"></span>-1 {AL.STCK_PS_8}
                </div>
                <div className="sell-box">{AL.SELL}</div>
                <div className="bold-text">
                    {AL.STCK_PS_1} "<span className="dang-text">{AL.STCK_PS_3}</span>" {AL.STCK_PS_4}
                </div>
                <div>
                    <span className="bold-text">+1</span> {AL.STCK_PS_6} <span className="bold-text">0.01%</span> {AL.STCK_PS_7} <span className="bold-text">+1</span> {AL.STCK_PS_8}
                </div>
                <div>
                    <span className="bold-text">-1</span> {AL.STCK_PS_5} <span className="bold-text">0.01%</span> {AL.STCK_PS_7} <span className="bold-text">-1</span> {AL.STCK_PS_8}
                </div>
                <div>
                    <div className="bold-text">{AL.STCK_PS_9}:</div>
                    <div>{AL.STOCK_EQ_RULES_FRLA}</div>
                    <div><span className="bold-text">2X</span> {AL.STCK_PS_12}</div>
                    <div><span className="bold-text">1.5X</span> {AL.STCK_PS_11}</div>
                </div>
                <div>
                    <div>{AL.STCK_PS_13}:</div>
                    <div>{AL.STCK_PS_14}</div>
                </div>
                <div>{AL.STCK_PS_15} (<span className="bold-text">{AL.STCK_PS_2}</span>){AL.STCK_PS_16} (<span className="bold-text">{AL.STCK_PS_3}</span>){AL.STCK_PS_17} (<span className="bold-text">{AL.STCK_PS_2}</span>){AL.STCK_PS_18}</div>
                <div>
                    <div>{AL.STCK_PS_19}- <span className="succ-text">{AL.STCK_PS_22} +100</span></div>
                    <div>{AL.STCK_PS_20}- <span className="succ-text">{AL.STCK_PS_22} +15</span></div>
                    <div>{AL.STCK_PS_21}- <span className="succ-text">{AL.STCK_PS_22} +3</span></div>
                </div>
                <div>
                    <div>{AL.STOCK_EQ_RULES_FRLA}</div>
                </div>
                <div>
                    <div>
                        {AL.STCK_PS_25} <span className="succ-text">{AL.STCK_PS_2}</span>{AL.STCK_PS_26} {AL.STCK_PS_22} <span className="bold-text">(+100)</span>
                    </div>
                    <div>{AL.STOCK_EQ_RULES_PT1}: <span className="bold-text">20</span></div>
                    <div>{AL.STOCK_EQ_RULES_PT2}= 1250*20= <span className="bold-text">25,000</span></div>
                    <div>{AL.STOCK_EQ_RULES_PT3}= 1350*20= <span className="bold-text">27,000</span></div>
                    <div>{AL.STOCK_EQ_RULES_PT4}= <span className="bold-text">+2000</span></div>
                </div>
                <div>
                    <div>{AL.STCK_PS_20}- {AL.STOCK_EQ_RULES_PT5} <span className="dang-text">{AL.SELL}</span> {AL.STCK_PS_26} {AL.STCK_PS_22} - Less-7%=  <span className="bold-text"> (+15)</span></div>
                    <div>{AL.STOCK_EQ_RULES_PT1}: <span className="bold-text">168</span></div>
                    <div>{AL.STOCK_EQ_RULES_PT2}= 150*168= <span className="bold-text">25,200</span></div>
                    <div>{AL.STOCK_EQ_RULES_PT3}= 165*168= <span className="bold-text">27,720</span> </div>
                    <div>{AL.STOCK_EQ_RULES_PT4}= <span className="bold-text">-2,520</span></div>
                </div>
                <div>
                    <div>{AL.STCK_PS_21}- <span className="succ-text">{AL.STCK_PS_2}</span>- {AL.STCK_PS_22}- <span className="bold-text">+3</span></div>
                    <div>{AL.STOCK_EQ_RULES_PT1}: <span className="bold-text">76</span></div>
                    <div>{AL.STOCK_EQ_RULES_PT2}= 333*76 = <span className="bold-text">25,308</span></div>
                    <div>{AL.STOCK_EQ_RULES_PT3}= 336*76 = <span className="bold-text">25,536</span> </div>
                    <div>{AL.STOCK_EQ_RULES_PT4}= <span className="bold-text">+228</span></div>
                </div>
                <div>
                    <div>{AL.STCK_PS_31}</div>
                    <div>2* {AL.STOCK_EQ_RULES_PT6}</div>
                </div>
                <div>
                    <div>{AL.STCK_PS_30}</div>
                    <div>{AL.STCK_PS_35}</div>
                </div>
                <div>{AL.STCK_PS_33} 2*2000=<span className="bold-text">4,000</span></div>
                <div>{AL.STCK_PS_36} 1.5*288=<span className="bold-text">342</span></div>
                <div>
                    <div className="bold-text">{AL.TOTAL_AMOUNT}</div>
                    <div className="bold-text">27,000-27,520+342= Rs. 80,256</div>
                </div>
            </div>
        )
    }

    showRulesList = (ruleType, stockSetting) => {
        let CONFIG_DATA = stockSetting && stockSetting.config_data ? stockSetting.config_data : '10';
        let STK_SET = Utilities.getMasterData().ae_setting;

        let StrtDATE = "2021-09-16 " + STK_SET.contest_publish_time;
        let EndDATE = "2021-09-16 " + STK_SET.contest_end_time;

        return (
            <ul className="new-rules-list">
                {
                    ruleType == '2' ?
                        <li> <span className="disc"></span> {AL.STOCK_NDR14} <span className="bold-text"><MomentDateComponent data={{ date: EndDATE, format: "hh:mm a " }} /> </span>
                            {AL.STOCK_NDR15} <span className="bold-text"><MomentDateComponent data={{ date: StrtDATE, format: "hh:mm a" }} /> </span></li>
                        :
                        ruleType == '3' ?
                            <li><span className="disc"></span>{AL.STOCK_NDR16} <span className="bold-text"><MomentDateComponent data={{ date: EndDATE, format: "hh:mm a" }} /> </span>
                                {AL.STOCK_NDR17} <span className="bold-text"><MomentDateComponent data={{ date: StrtDATE, format: "hh:mm a" }} /> </span></li>
                            :
                            <li><span className="disc"></span>{AL.STOCK_NDR11} <span className="bold-text"><MomentDateComponent data={{ date: EndDATE, format: "hh:mm a " }} /> </span>
                                {AL.TILL} <span className="bold-text"><MomentDateComponent data={{ date: StrtDATE, format: "hh:mm a " }} /></span>  {AL.STOCK_NDR12} {CONFIG_DATA.tc || 10} {AL.STOCK_NDR13}</li>
                }
                {/* <li><span className="disc"></span>{AL.STOCK_NDR1}</li> */}
                <li><span className="disc"></span>{AL.STOCK_NDR18}</li>
                
                <li><span className="disc"></span>
                    {AL.STOCK_EQ_RULES1}
                </li>
                <li><span className="disc"></span>
                    {AL.STOCK_NDR8}
                </li>
                <li><span className="disc"></span>{AL.STOCK_NDR9}</li>
                <li><span className="disc"></span>{AL.STOCK_NDR10}</li>
            </ul>
        )
    }

    render() {

        const { mShow, mHide, stockSetting, showPtsOnly, showOnlyTab } = this.props; // showOnlyTab possible value 'daily','weekly','monthly'
        let activeTab = showOnlyTab && showOnlyTab== 'weekly' ? 2 : showOnlyTab && showOnlyTab== 'monthly' ? 3 : 1
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        onHide={mHide}
                        dialogClassName="custom-modal rules-scoring-modal header-circular-modal overflow-hidden stock-fm"
                        className="center-modal"
                    >
                        <Modal.Header >
                            <div className="modal-img-wrap">
                                <div className="wrap">
                                    <i className="icon-note"></i>
                                </div>
                            </div>
                            {AL.RULES}
                        </Modal.Header>

                        <Modal.Body className="static-page">
                            <React.Fragment>
                                <div className="webcontainer-inner mt-0">
                                    <div className="page-body rules-scoring-body p-0">
                                        <Tabs defaultActiveKey={activeTab} 
                                            id="controlled-tab-example" className={"custom-nav-tabs stk dynamic-stk-rules hide-tab" + (showOnlyTab && showOnlyTab!= '' ? " hide-tab" : "")}
                                        >
                                            <Tab eventKey={1} title={AL.DAILY}>
                                                <div className="stk-dyn-rules-text">
                                                    {
                                                        !showPtsOnly &&
                                                        <>
                                                            <div className="rules-system-head pt-0">{AL.CONTEST_JOINING}</div>
                                                            {this.showRulesList('1', stockSetting)}
                                                        </>
                                                    }
                                                    <div className={"rules-system-head" + (showPtsOnly ? ' pt-0' : '')}>{AL.POINT_SYSTEM}</div>
                                                    {this.showPointSystem()}
                                                </div>
                                            </Tab>
                                            <Tab eventKey={2} title={AL.WEEKLY} >
                                                <div className="stk-dyn-rules-text">
                                                    {
                                                        !showPtsOnly &&
                                                        <>
                                                            <div className="rules-system-head pt-0">{AL.CONTEST_JOINING}</div>
                                                            {this.showRulesList('2', stockSetting)}
                                                        </>
                                                    }
                                                    <div className={"rules-system-head" + (showPtsOnly ? ' pt-0' : '')}>{AL.POINT_SYSTEM}</div>
                                                    {this.showPointSystem()}
                                                </div>
                                            </Tab>
                                            <Tab eventKey={3} title={AL.MONTHLY}>
                                                <div className="stk-dyn-rules-text">
                                                    {
                                                        !showPtsOnly &&
                                                        <>
                                                            <div className="rules-system-head pt-0">{AL.CONTEST_JOINING}</div>
                                                            {this.showRulesList('3', stockSetting)}
                                                        </>
                                                    }
                                                    <div className={"rules-system-head" + (showPtsOnly ? ' pt-0' : '')}>{AL.POINT_SYSTEM}</div>
                                                    {this.showPointSystem()}
                                                </div>
                                            </Tab>
                                        </Tabs>
                                        
                                    </div>
                                </div>
                            </React.Fragment>
                        </Modal.Body>
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}
export default StockEquityFRules;