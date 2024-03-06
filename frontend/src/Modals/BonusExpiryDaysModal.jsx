import React from 'react';
import { MomentDateComponent, NoDataView } from '../Component/CustomComponent';
import { MyContext } from '../InitialSetup/MyProvider';
import { Modal } from 'react-bootstrap';
import { _Map } from '../Utilities/Utilities';
import Images from '../components/images';
import * as AppLabels from "../helper/AppLabels";

class BonusExpiryDaysModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
        };
    }

    renderSevenDayItem = (item) => {
        return <div className="seven-day-row">
            <div><MomentDateComponent data={{date:item.bonus_expiry_date,format:"DD MMM"}} /></div>
            <span><i className="icon-bonus ic-bns" /> {item.bamt}</span>
        </div>
    }

    render() {

        const { mHide, be_data } = this.props;
        return (
            <MyContext.Consumer>
                {(context) => (

                    <Modal
                        show={true}
                        bsSize="large"
                        dialogClassName="how-to-play-modal bonus-exp-m"
                        className="center-modal"
                        onHide={mHide}
                    >
                        <Modal.Header closeButton>
                        </Modal.Header>
                        <Modal.Body>
                            <div className="header-modalbg">
                                <div className="custom-bonus-ic"><i className="icon-bonus" /></div>
                                <h4 className="title">{AppLabels.BONUS_EXPIRATION}</h4>
                            </div>
                            {
                                be_data.data && be_data.data.length > 0 && <div className="thank-you-body">
                                    <div className="exp-strip">{AppLabels.BONUS_EXP_NEXT_7}</div>
                                    {
                                        _Map(be_data.data, (item) => {
                                            return this.renderSevenDayItem(item)
                                        })
                                    }
                                </div>
                            }
                            {
                                (!be_data.data || be_data.data.length === 0) && <NoDataView
                                    BG_IMAGE={Images.no_data_bg_image}
                                    // CENTER_IMAGE={Images.BRAND_LOGO_FULL}
                                    CENTER_IMAGE={Images.NO_DATA_VIEW}
                                    MESSAGE_1={AppLabels.NO_DATA_AVAILABLE}
                                />
                            }
                            <div className="exp-strip bottom-msg">{AppLabels.B_EXP_MSG}{be_data.exvldt}{AppLabels.B_EXP_MSG1}</div>
                        </Modal.Body>
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}

export default BonusExpiryDaysModal;
