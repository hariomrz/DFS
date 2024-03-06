import React, { useEffect, useState } from 'react'
import { Modal, OverlayTrigger, Tooltip } from 'react-bootstrap';
import * as AppLabels from '../../helper/AppLabels'
import * as WSC from "../../WSHelper/WSConstants";
import { Utilities } from '../../Utilities/Utilities';
import { getTDSBreakup } from "../../WSHelper/WSCallings";

export default function TDSBreakupModal(props) {
    const { show, onHide, masterData, net_winning } = props
    const [tdsData, setData] = useState([])
    const [posting, setPosting] = useState(true)

    useEffect(() => {
        getTDSBreakup().then(({ response_code, data }) => {
            if (response_code == WSC.successCode) {
                setData(data)
                setPosting(false)
            }
        })
        return () => { }
    }, [])

    const getLiableTDS = (winnings, format = true) => {
        let per = ((Number(winnings) / 100) * Number(masterData.allow_tds.percent)).toFixed(2)
          let send =   per > 0 ? per : '0.00'
        return format ? Utilities.numberWithCommas(send) : Number(send).toFixed(2);
    }

    const getCurrentTDSLiability = ({ tds_paid }) => {
        let tdl = net_winning > 0 ? ((getLiableTDS(net_winning, false) - Number(tds_paid)).toFixed(2)) : '0.00'
        return posting ? '--' : Utilities.numberWithCommas(masterData.currency_code + ' ' + tdl)
    }


    return (
        <>
            <Modal
                show={show}
                onHide={onHide}
                dialogClassName="custom-modal tds-breakup-modal"
                className="center-modal"
            >
                <Modal.Header>
                    {AppLabels.TDS_BREAKUP}
                </Modal.Header>
                <Modal.Body>
                    <div className="current-tds-liability">
                        <div className="tds-cell">
                            <div className="tds-cell-title">
                                {AppLabels.CURRENT_TDS_LIABILITY}
                            </div>
                            <div className="tds-cell-subtitle">
                                {AppLabels.AS_ON}{" "}{Utilities.getFormatedDateTime(new Date(), 'hh:mm A, DD MMMM, YYYY')}
                            </div>
                        </div>
                        <div className="tds-cell amount">
                            {/* {getCurrentTDSLiability(tdsData)} */}
                            {posting ? '--' : <>{masterData.currency_code}{" "}{getLiableTDS(net_winning)}</>}
                        </div>
                    </div>
                    <div className="tds-liability-tbl">
                        <div className="tds-row">
                            <div className="tds-cell">
                                {AppLabels.TOTAL_NET_WINNINGS}


                                <OverlayTrigger trigger={['hover', 'focus']} placement="right" overlay={
                                    <Tooltip id="tooltip">
                                        {AppLabels.NET_WINNINGS_INFO}
                                    </Tooltip>
                                }>
                                    <span className="icon-info" onClick={(e) => e.stopPropagation()} />
                                </OverlayTrigger>
                            </div>
                            <div className="tds-cell">
                                {
                                    posting ? '--' : <>{masterData.currency_code}{" "}{Utilities.numberWithCommas(Number(tdsData.total_net_winning).toFixed(2))}</>
                                }

                            </div>
                        </div>
                        <div className="tds-row">
                            <div className="tds-cell">
                                <span className="highlight-text">{AppLabels.TDS_PAID}</span>
                                <OverlayTrigger trigger={['hover', 'focus']} placement="right" overlay={
                                    <Tooltip id="tooltip">
                                        {AppLabels.TDS_PAID_INFO}
                                    </Tooltip>
                                }>
                                    <span className="icon-info" onClick={(e) => e.stopPropagation()} />
                                </OverlayTrigger>
                            </div>
                            <div className="tds-cell">
                                <span className="highlight-text no-bg">
                                    {
                                        posting ? '--' : <>{masterData.currency_code}{" "}{Utilities.numberWithCommas(Number(tdsData.tds_paid).toFixed(2))}</>
                                    }
                                </span>
                            </div>
                        </div>

                        <div className="tds-row">
                            <div className="tds-cell">
                                {AppLabels.NET_WINNING_TEXT}
                            </div>
                            <div className="tds-cell">
                                {
                                    posting ? '--' : <>{masterData.currency_code}{" "}{Utilities.numberWithCommas(Number(net_winning).toFixed(2))}</>
                                }
                            </div>
                        </div>


                        <div className="tds-row">
                            <div className="tds-cell">
                                {masterData.allow_tds.percent}{"% "}{AppLabels.TDS_ON_NET_WINNINGS}
                            </div>
                            <div className="tds-cell">
                                {posting ? '--' : <>{masterData.currency_code}{" "}{getLiableTDS(net_winning)}</>}
                            </div>
                        </div>
                        <div className="tds-row footer">
                            <div className="tds-cell">
                                {AppLabels.CURRENT_TDS_LIABILITY}
                            </div>
                            <div className="tds-cell">
                                {/* {getCurrentTDSLiability(tdsData)} */}
                                {posting ? '--' : <>{masterData.currency_code}{" "}{getLiableTDS(net_winning)}</>}
                                
                            </div>
                        </div>
                    </div>
                    <a className="button button-primary-rounded btn-verify text-center"
                        onClick={() => onHide()}>
                        {AppLabels.replace_PANTOID(AppLabels.OKAY)}
                    </a>

                </Modal.Body>
            </Modal>
        </>
    )
}

TDSBreakupModal.defaultProps = {
    show: false,
    onHide: () => { }
}