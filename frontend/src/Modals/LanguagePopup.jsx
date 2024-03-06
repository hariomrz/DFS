import React from 'react';
import { Modal, Row, Col } from 'react-bootstrap';
import Images from '../components/images';
import * as AppLabels from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';
import WSManager from "../WSHelper/WSManager";
import { changeLanguageString } from "../helper/AppLabels";

export default class LanguagePopup extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            language: this.props.DefaultLanguage,
            isChanges: false
        };
    }

    handleChange = (selectedLang) => {
        this.setState({
            language: selectedLang.value,
            isChanges: true
        })
    };

    submitLanguage() {
        let selectedLang = this.state.language;
        if (this.props.i18n.language != selectedLang) {
            if (window.ReactNativeWebView) {
                let data = {
                    action: 'back',
                    locale:selectedLang,
                    targetFunc:'handleLanguageChange'
                }
                window.ReactNativeWebView.postMessage(JSON.stringify(data));
            }
            this.props.i18n.changeLanguage(selectedLang);
            WSManager.setAppLang(selectedLang);
            changeLanguageString();
            window.location.reload();
        } else {
            this.props.IsLanguagePopupHide();
        }
    }

    render() {

        const { IsLanguagePopupShow, IsLanguagePopupHide, LanguageList } = this.props;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div>
                        <Modal show={IsLanguagePopupShow} onHide={IsLanguagePopupHide} bsSize="large" dialogClassName="language-modal" className="center-modal">
                            <Modal.Header closeButton>
                            </Modal.Header>
                            <Modal.Body>
                                <div className="text-center center-section">
                                    {/* <div className="language-header">
                                        <img src={Images.LANGUAGE_IMG} alt="" />
                                        <div className="m-t-20">{AppLabels.SELECT_YOUR_LANGUAGE}</div>
                                    </div> */}
                                    <div className="language-modal-body">
                                        <Row>
                                            <Col sm={2}></Col>
                                            <Col sm={8}>
                                                <Row className="pb20">
                                                    {LanguageList && LanguageList.map((item, idx) => {
                                                        return (
                                                            <Col key={idx} xs={6} className="text-center">
                                                                <div
                                                                    onClick={() => this.handleChange(item)}
                                                                    className={"language-box" + (item.value == this.state.language ? ' selected' : '')}>
                                                                    <div className="language-text">{item.label}</div>
                                                                    <div className="language-sub-text">{item.desc}</div>
                                                                </div>
                                                            </Col>
                                                        )
                                                    })
                                                    }
                                                </Row>
                                            </Col>
                                        </Row>
                                    </div>
                                    <Row>
                                        <Col xs={12} className="text-center">
                                            <button disabled={!this.state.isChanges} className="submit-otp" type='submit' onClick={() => this.submitLanguage()}><i className="icon-check icon-next-btn"></i></button>
                                        </Col>
                                    </Row>
                                </div>
                            </Modal.Body>
                        </Modal>
                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}