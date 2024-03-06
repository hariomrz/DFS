import React, { Component, Fragment } from 'react';
import { DropdownToggle, UncontrolledDropdown, DropdownMenu, DropdownItem } from 'reactstrap';
import WSManager from "../helper/WSManager";
import * as NC from '../helper/NetworkingConstants';
import Images from "../components/images";
import { notify } from 'react-notify-toast';
import { createHashHistory } from 'history'
import _ from 'lodash';
import HF from '../helper/HelperFunction';
export const history = createHashHistory()
var HEaderNotification_this = null
class HeaderNotification extends Component {
    constructor(props) {
        super(props)
        this.state = {
            classAdd: true,
            ALLOW_COIN_MODULE: HF.allowCoin(),
            AUTO_KYC_ALLOW: !_.isNull(WSManager.getKeyValueInLocal('AUTO_KYC_ALLOW')) ? WSManager.getKeyValueInLocal('AUTO_KYC_ALLOW') : 0
        }
    }
    static reloadData(ModuleSetting) {
        if (ModuleSetting == '1') {
            HEaderNotification_this.setState({ ALLOW_COIN_MODULE: '0' })
        } else {
            HEaderNotification_this.setState({ ALLOW_COIN_MODULE: '1' })
        }
    }
    static reloadNotCount() {
        HEaderNotification_this.getNotificationCount()
    }
    componentDidMount() {
        this.getNotificationCount()
    }
    getNotificationCount() {
        WSManager.Rest(NC.baseURL + NC.GET_PENDING_COUNTS, {}).then(Response => {
            if (Response.response_code == NC.successCode) {
                HEaderNotification_this.setState({
                    pending_pan_card_count: Response.data.pending_pan_card_count,
                    pending_bank_document_count: Response.data.pending_bank_document_count,
                    feedback_pending_count: Response.data.feedback_pending_count,
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }
    render() {
        let { feedback_pending_count, pending_bank_document_count, pending_pan_card_count, ALLOW_COIN_MODULE, AUTO_KYC_ALLOW } = this.state
        HEaderNotification_this = this;
        return (
            <UncontrolledDropdown>
                <DropdownToggle
                    tag="span"
                >
                    <span className="notification-alert circle"></span>
                    <img src={Images.BELL} className="nav-icon" alt="" />
                </DropdownToggle>
                <DropdownMenu right>
                    <DropdownItem>
                        <ul className="bell-items mb-0">
                            {
                                ALLOW_COIN_MODULE == 1 && (
                                    <li className="bell-list" onClick={() => history.push('/coins/promotions/1')}>
                                        <img src={Images.FEEDBACK} />
                                        <a className="bell-text"><b>{feedback_pending_count ? feedback_pending_count : '0'}</b> Feedback<br /> Approval Pending</a>
                                    </li>
                                )
                            }
                            {
                                AUTO_KYC_ALLOW == "0" &&
                                <Fragment>
                                    <li className="bell-list" onClick={() => history.push('/manage_user?pending=1')}>
                                        <img src={Images.CARD} />
                                        <a className="bell-text"><b>{pending_pan_card_count ? pending_pan_card_count : '0'}</b> Pan Card<br /> Approval Pending</a>
                                    </li>
                                    <li className="bell-list" onClick={() => history.push('/manage_user?pending=1')}>
                                        <img src={Images.BANK_DOCUMENT} />
                                        <a className="bell-text"><b>{pending_bank_document_count ? pending_bank_document_count : '0'}</b> Bank Document<br /> Approval Pending</a>
                                    </li>
                                </Fragment>
                            }
                        </ul>
                    </DropdownItem>
                </DropdownMenu>
            </UncontrolledDropdown>
        )
    }
}

export default HeaderNotification