import React from 'react';
import { Tabs, Tab } from 'react-bootstrap';
import { Modal } from 'react-bootstrap';
import { _Map, Utilities } from "../../Utilities/Utilities";
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import { IsDynamicStockRules } from '../../helper/Constants';
import { MomentDateComponent } from '../CustomComponent';

class SPRules extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
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

    render() {

        const { mShow, mHide } = this.props;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        onHide={mHide}
                        dialogClassName="custom-modal rules-scoring-modal header-circular-modal overflow-hidden stock-fm sp-rules"
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
                                            <>
                                                <div className="rules-system-head pt-0">{AL.CONTEST_JOINING}</div>
                                                <ul className="scoring-chart">
                                                    <li>
                                                        <div className="display-table">
                                                            <div className="text-block text-left">{AL.SP_RULES_CJ1}</div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div className="display-table">
                                                            <div className="text-block text-left">{AL.SP_RULES_CJ2}</div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div className="display-table">
                                                            <div className="text-block text-left">{AL.SP_RULES_CJ3}</div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div className="display-table">
                                                            <div className="text-block text-left">{AL.SP_RULES_CJ4}</div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div className="display-table">
                                                            <div className="text-block text-left">{AL.SP_RULES_CJ5}</div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div className="display-table">
                                                            <div className="text-block text-left">{AL.SP_RULES_CJ6}</div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div className="display-table">
                                                            <div className="text-block text-left">{AL.SP_RULES_CJ7}</div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div className="display-table">
                                                            <div className="text-block text-left">{AL.SP_RULES_CJ8}</div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div className="display-table">
                                                            <div className="text-block text-left">{AL.SP_RULES_CJ9}</div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div className="display-table">
                                                            <div className="text-block text-left">{AL.SP_RULES_CJ10}</div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </>
                                        }
                                        <div className="rules-system-head">{AL.POINT_SYSTEM}</div>
                                        <div className="pts-sys-sec">
                                            <div>{AL.SP_RULES_PS1}</div>
                                            <div>{AL.SP_RULES_PS2}</div>
                                            <div>
                                                {AL.SP_RULES_PS3}<span className="bold-text">0.01%</span>
                                            </div>
                                            <div>{AL.SP_RULES_PS4}</div>
                                            <div>{AL.SP_RULES_PS5}</div>
                                            <div>
                                                {AL.SP_RULES_PS6}<span className="bold-text">0.01%</span> {AL.SP_RULES_PS8}
                                            </div>
                                            <div>
                                                {AL.SP_RULES_PS7}<span className="bold-text">0.01%</span> {AL.SP_RULES_PS8}
                                            </div>
                                            <div>
                                                <div>{AL.SP_RULES_EX}</div>
                                                <div>{AL.SP_RULES_EX1}</div>
                                            </div>
                                            <div>{AL.SP_RULES_EX2}</div>
                                            <div>{AL.SP_RULES_EX3}<span className="bold-text">102</span></div>
                                            <div>{AL.SP_RULES_EX4}<span className="bold-text">105</span></div>
                                            <div>{AL.SP_RULES_EX5}</div>
                                            <div>P = (102-105)/102 x 100 = 100 – 2.941 = <span className="bold-text">97.058%</span></div>
                                            <div>P = (105-105)/105 x 100 = 100 – 0 = <span className="bold-text">100%</span></div>
                                            <div>P = (107-105)/107 x 100 = 100 – 1.869 = 98.131% => <span className="bold-text">-98.131%</span></div>
                                            <div>{AL.SP_RULES_EX6}</div>
                                        </div>
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
export default SPRules;