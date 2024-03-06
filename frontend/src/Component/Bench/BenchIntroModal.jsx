import React from 'react';
import { Checkbox, FormGroup, Modal } from 'react-bootstrap';
import { _Map, Utilities } from "../../Utilities/Utilities";
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import Images from '../../components/images';


export default class BenchIntroModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {

        };

    }

    componentDidMount() {

    }

    render() {

        const { MShow, MHide,dontShowAgain } = this.props;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={MShow}
                        onHide={MHide}
                        dialogClassName="custom-modal bench-intro-modal"
                        className="center-modal"
                    //dialogClassName="custom-modal thank-you-modal confirmation-modal"
                    >
                        <Modal.Body>
                            <div className="header-sec">
                                <img src={Images.BENCH_INTO_IMG} alt=""/>
                            </div>
                            <div className="text-sec">
                                <div className="intro-heading">{AL.BENCH_INTRO}</div>
                                <div className="intro-desc">{AL.BENCH_SUB_TEXT}</div>
                                <div className="text-center">
                                    <a href className="btn btn-rounded btn-primary btn-block" onClick={MHide}>{AL.OKAY}</a>
                                </div>
                                <div className="dont-show">
                                    <div className="text-small sms-checkbox" >
                                        <FormGroup>
                                            <Checkbox className="custom-checkbox text-center" value=""
                                                onClick={dontShowAgain}
                                                name="all_leagues" id="all_leagues">
                                                <span className="auth-txt">
                                                    {AL.DONT_SHOW_ME_AGAIN}
                                                </span>
                                            </Checkbox>
                                        </FormGroup>
                                    </div>
                                </div>
                            </div>
                        </Modal.Body>
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}