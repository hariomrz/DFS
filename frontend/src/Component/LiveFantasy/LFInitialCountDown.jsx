import React, { Component } from 'react'
import ls from 'local-storage';
import { CountdownCircleTimer } from 'react-countdown-circle-timer';
import _Map from 'lodash/map';
import _filter from 'lodash/filter';

import PageVisibility from 'react-page-visibility';
import { Utilities } from '../../Utilities/Utilities';



class LFInitialCountDown extends Component {
    constructor(props) {
        super(props);
        this.state = {
            isVisible: true
        }
    }

    render() {
        const {
            size,
            duration,
            onComplete,
            show,
            
        } = this.props;
        return (
            <div className={'timer-holder-lf height-t'}>
                                                {
                                                    // Defalut 3 sec for initiate Game center
                                                    show &&
                                                    <CountdownCircleTimer
                                                        style={{ marinTop: 5 }}
                                                        isPlaying={true}
                                                        duration={duration}
                                                        size={size}
                                                        strokeWidth={5}
                                                        initialRemainingTime={duration}
                                                        colors={[
                                                            ['#009933', 0.33],
                                                            ['#e6e600', 0.33],
                                                            ['#cc3300', 0.33],
                                                        ]}
                                                        onComplete={() => onComplete()}
                                                        >
                                                        {({ remainingTime }) => (<><div className="timer-value">{remainingTime} <div className="timer-text">Sec</div></div>

                                                        </>)}
                                                    </CountdownCircleTimer>
                                                }

                                            </div>

        )
    }
}

LFInitialCountDown.defaultProps = {
    size: 250,
    duration: 3,
    onComplete: () => { },
    data: {},
    show: false
}
export default LFInitialCountDown