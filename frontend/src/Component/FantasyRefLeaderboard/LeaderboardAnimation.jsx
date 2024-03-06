import React from 'react';
import {  } from 'react-bootstrap';
import Images from '../../components/images';

export default class LBAnimation extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
        };

    }

    componentDidMount() {
    }

    render() {


        return (
            <div className="lead-ani-sec">
                <div className="circular-sec">
                    <img src={Images.CENTER_LEAD_IMG} alt="" className='center-img' />
                    <div className="feature-box fb1">
                        <div className="inn-box">
                            <p>Analyze your game performance</p>
                            <img src={Images.LEAD_STEP1} alt="" className="feature-img" />
                            <img src={Images.LEAD_STEP_CIR1} alt="" className="circle-bg" />
                        </div>
                    </div>
                    <div className="feature-box fb2">
                        <div className="inn-box">
                            <p>Track your performance against your competitors</p>
                            <img src={Images.LEAD_STEP2} alt="" className="feature-img" />
                            <img src={Images.LEAD_STEP_CIR2} alt="" className="circle-bg" />
                        </div>
                    </div>
                    <div className="feature-box fb3">
                        <div className="inn-box">
                            <p>View your progress over various time frames</p>
                            <img src={Images.LEAD_STEP3} alt="" className="feature-img" />
                            <img src={Images.LEAD_STEP_CIR3} alt="" className="circle-bg" />
                        </div>
                    </div>
                    <div className="feature-box fb4">
                        <div className="inn-box">
                            <p>Identify leaders in your space and learn from their content</p>
                            <img src={Images.LEAD_STEP4} alt="" className="feature-img" />
                            <img src={Images.LEAD_STEP_CIR4} alt="" className="circle-bg" />
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}