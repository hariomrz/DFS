import React from 'react';
import { Tabs, Tab } from 'react-bootstrap';
import { Modal } from 'react-bootstrap';
import { _Map, Utilities } from "../../Utilities/Utilities";
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import { IsDynamicStockRules } from '../../helper/Constants';
import { MomentDateComponent } from '../CustomComponent';

class LFRulesScoringModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            rules: {
                daily: [
                    AL.RULES_ONE,
                    AL.RULES_TWO,
                    AL.RULES_THREE,
                    AL.RULES_FOUR,

                    
                ],
                weekly: [
                    AL.STOCK_WR1,
                    AL.STOCK_WR2,
                    AL.STOCK_WR3,
                    AL.STOCK_WR4,
                    AL.STOCK_WR5,
                    AL.STOCK_WR6,
                    AL.STOCK_WR7,
                    AL.STOCK_WR8,
                    AL.STOCK_WR9,
                    AL.STOCK_WR10,
                    AL.STOCK_WR11
                ],
                monthly: [
                    AL.STOCK_MR1,
                    AL.STOCK_MR2,
                    AL.STOCK_MR3,
                    AL.STOCK_MR4,
                    AL.STOCK_MR5,
                    AL.STOCK_MR6,
                    AL.STOCK_MR7,
                    AL.STOCK_MR8
                ]
            },
        };

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
                    <div>{AL.STCK_PS_10}<span className="bold-text">*100= {AL.STCK_PS_27}</span></div>
                    <div><span className="bold-text">2X</span> {AL.STCK_PS_12}</div>
                    <div><span className="bold-text">1.5X</span> {AL.STCK_PS_11}</div>
                </div>
                <div>
                    <div>{AL.STCK_PS_13}:</div>
                    <div>{AL.STCK_PS_14}</div>
                </div>
                <div>{AL.STCK_PS_15} (<span className="bold-text">{AL.STCK_PS_2}</span>){AL.STCK_PS_16} (<span className="bold-text">{AL.STCK_PS_3}</span>){AL.STCK_PS_17} (<span className="bold-text">{AL.STCK_PS_2}</span>){AL.STCK_PS_18}</div>
                <div>
                    <div>{AL.STCK_PS_19}- <span className="succ-text">{AL.STCK_PS_22} 15%</span></div>
                    <div>{AL.STCK_PS_20}- <span className="succ-text">{AL.STCK_PS_22} 15%</span></div>
                    <div>{AL.STCK_PS_21}- <span className="succ-text">{AL.STCK_PS_22} 3%</span></div>
                </div>
                <div>
                    <div>{AL.STCK_PS_23}</div>
                    <div>{AL.STCK_PS_24} % * 100</div>
                </div>
                <div>{AL.STCK_PS_25} <span className="succ-text">{AL.STCK_PS_2}</span>{AL.STCK_PS_26} {AL.STCK_PS_22} (Profit 15%)= <span className="bold-text">1500 {AL.STCK_PS_27}</span></div>
                <div>{AL.STCK_PS_20}- <span className="dang-text">{AL.STCK_PS_3}</span> - {AL.STCK_PS_22} - Less-7%=  <span className="bold-text">-700 {AL.STCK_PS_27}</span></div>
                <div>{AL.STCK_PS_21}- <span className="succ-text">{AL.STCK_PS_2}</span>- {AL.STCK_PS_22}- %3=  <span className="bold-text">300 {AL.STCK_PS_27}</span></div>
                <div>
                    <div>{AL.STCK_PS_31}</div>
                    <div>{AL.STCK_PS_32}</div>
                </div>
                <div>
                    <div>{AL.STCK_PS_30}</div>
                    <div>{AL.STCK_PS_35}</div>
                </div>
                <div>{AL.STCK_PS_33} 2*1500=<span className="bold-text">3000</span></div>
                <div>{AL.STCK_PS_36} 1.5*300=<span className="bold-text">450</span></div>
                <div>
                    <div className="bold-text">{AL.STCK_PS_34}</div>
                    <div className="bold-text">3000+450-700=2750 {AL.STCK_PS_27}</div>
                </div>
            </div>
        )
    }

    showRulesList = (ruleType, stockSetting) => {
        let CONFIG_DATA = stockSetting && stockSetting.config_data ? stockSetting.config_data : '10';
        let STK_SET = Utilities.getMasterData().asf_setting;

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
                <li><span className="disc"></span>{AL.STOCK_NDR1}</li>
                <li><span className="disc"></span>{AL.STOCK_NDR18}</li>
                <li><span className="disc"></span>{AL.STOCK_NDR2} "<span className="succ-text">{AL.BUY}</span>" {AL.or} "<span className="dang-text">{AL.SELL}</span>"
                    <div className="mt-2"> <span className="bold-text">{AL.STOCK_NDR3}:</span></div>
                    {AL.STOCK_NDR4}</li>
                <li><span className="disc"></span>{AL.STOCK_NDR5}</li>
                <li><span className="disc"></span>
                    {AL.SELECT} <span className="bold-text">{AL.CORE_STOCK}</span> {AL.STOCK_NDR6} <span className="bold-text">2X</span> {AL.STOCK_NDR7}
                </li>
                <li><span className="disc"></span>{AL.STOCK_NDR7}
                    {AL.SELECT} <span className="bold-text">{AL.SATELLITE_STOCK}</span> {AL.STOCK_NDR6} <span className="bold-text">1.5X</span> {AL.STOCK_NDR7}
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
        let activeTab = showOnlyTab && showOnlyTab == 'weekly' ? 2 : showOnlyTab && showOnlyTab == 'monthly' ? 3 : 1
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
                                        {

                                            <Tabs defaultActiveKey={activeTab}
                                                id="controlled-tab-example" className={"custom-nav-tabs stk hide-tab" + (showOnlyTab && showOnlyTab != '' ? " hide-tab" : "")}
                                            >
                                                <Tab style={{marginTop:-20}} eventKey={1} title={AL.DAILY}>
                                                    {
                                                        !showPtsOnly &&
                                                        <>
                                                            {/* <div className="rules-system-head pt-0">{AL.CONTEST_JOINING}</div> */}
                                                            <ul  className="scoring-chart">
                                                                {
                                                                    this.state.rules &&
                                                                    _Map(this.state.rules.daily, (item, idx) => {
                                                                        return (
                                                                            <li key={idx}>
                                                                                <div className="display-table">
                                                                                    <div className="text-block text-left">{item}</div>
                                                                                </div>
                                                                            </li>
                                                                        );
                                                                    })
                                                                }
                                                            </ul>
                                                        </>
                                                    }
                                                    {/* <div className={"rules-system-head" + (showPtsOnly ? ' pt-0' : '')}>{AL.POINT_SYSTEM}</div>
                                                    {this.showPointSystem()} */}
                                                </Tab>

                                            </Tabs>
                                        }
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
export default LFRulesScoringModal;