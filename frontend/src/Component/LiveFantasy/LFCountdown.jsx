import React, { Component } from 'react'
import ls from 'local-storage';
import { CountdownCircleTimer } from 'react-countdown-circle-timer';
import _Map from 'lodash/map';
import _filter from 'lodash/filter';

import PageVisibility from 'react-page-visibility';
import { Utilities } from '../../Utilities/Utilities';



class LFCountdown extends Component {
    constructor(props) {
        super(props);
        this.state = {
            isVisible: true
        }
    }

    approxeq = (svr_time, epsilon = null) => {
        let local_time = Math.round(Date.now() / 1000)
        return new Promise((resolve, reject) => {
            resolve(true)
            // if (epsilon == null) {
            //     epsilon = 4;
            // }
            // Math.abs(local_time - svr_time) < epsilon ? resolve(true) : reject(false)
        })
    }
    
    getSocketTime = (collection_id) => {
        let _skevArr = ls.get('_skev')
        console.log(_skevArr);
        let _skev = _filter(_skevArr, o => o.collection_id == collection_id)[0];
        let _timer = 0
        if (_skev) {
            const { timer_date } = _skev;

            if(timer_date) {
                let _new_time = Math.round(Date.now() / 1000)
                if (_new_time < timer_date) _timer = timer_date - _new_time;
                else {
                    console.log('Times up!!', timer_date);
                    Utilities.removeSoketEve(collection_id)
                }
            }
        }
        return _timer
    }

    handleVisibilityChange = (isVisible) => {
        this.setState({
            isVisible: isVisible
        })
    }

    render() {
        const {
            size,
            duration,
            onComplete,
            data,
            show,
            isFromMyContest
        } = this.props;
        const { isVisible } = this.state;
        return (
            <PageVisibility onChange={this.handleVisibilityChange}>
                {
                    show && isVisible ?
                        <CountdownCircleTimer
                            style={{ marinTop: 5 }}
                            isPlaying={true}
                            duration={duration}
                            size={size}
                            initialRemainingTime={this.getSocketTime(data.collection_id) || 0}
                            strokeWidth={3}
                            colors={[
                                ['#009933', 0.33],
                                ['#e6e600', 0.33],
                                ['#cc3300', 0.33],
                            ]}
                            onComplete={() => onComplete(data)}
                        >
                            {({ remainingTime }) => (<>
                            <div className="timer-value">{remainingTime}
                            {
                                !isFromMyContest &&  <div className="timer-text">Sec</div>
                            }
                            
                             </div>

                            </>)}
                        </CountdownCircleTimer>
                        : ''
                }
            </PageVisibility>

        )
    }
}

LFCountdown.defaultProps = {
    size: 40,
    duration: 15,
    onComplete: () => { },
    data: {},
    show: false
}
export default LFCountdown