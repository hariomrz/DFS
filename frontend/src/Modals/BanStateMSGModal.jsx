import React from 'react';
import { Modal, OverlayTrigger, Tooltip } from 'react-bootstrap';
import { MyContext } from '../InitialSetup/MyProvider';
import { Utilities } from '../Utilities/Utilities';


class BanStateMSGModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {

        };
    }

    hideModal = () => {
        this.props.mHide();
        setTimeout(() => {
            this.props.history.goBack();
        }, 50);
    }

    okButtonClick = () => {
        const { mHide, banStateMSGData } = this.props;
        let mData = banStateMSGData || {};
        if (mData.isFrom == 'addFunds') {
            this.hideModal()
        } 
        else if(banStateMSGData.isFromShare){
            this.props.history.push('/')
        }
        else {
            mHide();
        }
    }

    render() {
        const { mShow, banStateMSGData } = this.props;
        let banStates = Object.values(Utilities.getMasterData().banned_state || {});
        let mData = banStateMSGData || {};
        let bsL = banStates.length;
        return (
            <MyContext.Consumer>
                {(context) => (

                    <Modal
                        show={mShow}
                        dialogClassName=""
                        className="center-modal declaration-modals"
                    >
                        <Modal.Header>
                            <div className="icon-section">
                                <i className="icon-warning"></i>
                            </div>
                            <h2 className="header-title">{mData.title}</h2>
                        </Modal.Header>
                        <Modal.Body>
                            <p className="declar-msg">{mData.Msg1}<span> {banStates.slice(0, bsL > 5 ? 5 : bsL).join(', ')}
                                {bsL > 5 && <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                    <Tooltip id="tooltip" className="tooltip-featured">
                                        <strong>{banStates.join(', ')}</strong>
                                    </Tooltip>
                                }><i style={{ padding: 3 }} className="icon-info" /></OverlayTrigger>}</span>{mData.Msg2}.</p>
                            <div onClick={this.okButtonClick} className={"button button-primary button-block btm-fixed"}>OKAY</div>
                            <div onClick={() => this.props.history.push({ pathname: '/contact-us', state: {} })} className="contact-us">Contact Us</div>
                        </Modal.Body>
                    </Modal>
                )}
            </MyContext.Consumer>
        );
    }
}

export default BanStateMSGModal;