import React, { Component } from "react";
import { Modal, ModalBody, ModalHeader, ModalFooter, Button } from 'reactstrap';
import Images from "../images";
import HF, { _isEmpty, _isUndefined } from "../../helper/HelperFunction";
export default class ViewWinners extends Component {
    constructor(props) {
        super(props)
        this.state = {}
    }

    render() {
        let { templateObj, modalisOpen, merchandise_list } = this.props
        return (
            <div className="winners-modal-container">
                <Modal isOpen={modalisOpen} toggle={() => this.props.PrizeModelCallback()} className="winning-modal">
                    <ModalHeader>Winnings Distribution</ModalHeader>
                    <ModalBody>
                        <div className="distribution-container">
                            {
                                templateObj.prize_detail &&
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Rank</th>
                                            <th style={{ width: "100px", textAlign: "center" }}>Min</th>
                                            <th style={{ width: "100px", textAlign: "center" }}>Max</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {templateObj.prize_detail.map((prize, idx) => (
                                            <tr key={idx}>
                                                <td className="text-left">
                                                    {prize.min}
                                                    {
                                                        prize.min != prize.max &&
                                                        <span>-{prize.max}</span>
                                                    }
                                                </td>
                                                <td className="text-center">
                                                    {
                                                        prize.prize_type == '0' &&
                                                        <i className="icon-bonus"></i>
                                                    }
                                                    {
                                                        (!prize.prize_type || prize.prize_type == '1') &&
                                                        HF.getCurrencyCode()
                                                    }
                                                    {
                                                        prize.prize_type == '2' &&
                                                        <img src={Images.COINIMG} alt="coin-img" />
                                                    }
                                                    {
                                                        prize.prize_type == '3' &&
                                                        HF.getMerchandiseName(merchandise_list, prize.amount)
                                                    }
                                                    {
                                                        prize.prize_type != '3' &&
                                                        HF.getNumberWithCommas(prize.amount)
                                                    }
                                                </td>
                                                <td className="text-center">
                                                    {
                                                        prize.prize_type == '0' &&
                                                        <i className="icon-bonus"></i>
                                                    }
                                                    {
                                                        (!prize.prize_type || prize.prize_type == '1') &&
                                                        HF.getCurrencyCode()
                                                    }
                                                    {
                                                        prize.prize_type == '2' &&
                                                        <img src={Images.COINIMG} alt="coin-img" />
                                                    }
                                                    {
                                                        prize.prize_type == '3' &&
                                                        HF.getMerchandiseName(merchandise_list, prize.amount)
                                                    }
                                                    {
                                                        prize.prize_type != '3' &&
                                                        HF.getNumberWithCommas(prize.amount)
                                                    }
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            }
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <Button className="close-btn" color="secondary" onClick={() => this.props.PrizeModelCallback()}>Close</Button>
                    </ModalFooter>
                </Modal>
            </div>            
        )
    }
}