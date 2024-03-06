import React from 'react';
import Images from '../../components/images';
import { createBrowserHistory } from 'history';
import Countdown from 'react-countdown-now';
const history = createBrowserHistory();
const location = history.location;
const queryString = require('query-string');
const parsed = queryString.parse(location.search);

class YouRInQueue extends React.Component {

    constructor(props, context) {
        super(props, context);
        this.state = {
            dateNow: Date.now(),
            duration: parsed.yqtm ? atob(parsed.yqtm) : 0,
            message: parsed.yqmsg ? atob(parsed.yqmsg) : '',
            isCompleted: false
        };
    }

    onRefresh = () => {
        window.location.assign('/lobby')
    }

    renderer = ({ hours, minutes, seconds, completed }) => {
        if (completed) {
            this.setState({ isCompleted: completed })
            return false;
        } else {
            return (
                <span className="timer-resend m-0 mt20">
                    {hours}:{minutes}:{seconds}
                </span>
            );
        }
    };

    render() {
        const { dateNow, duration, message, isCompleted } = this.state;

        return (
            <div className='no-network-container'>
                <div className='child-item'>
                    <div className="no-data-container">
                        <div className="background-image">
                            <img alt="" className="center-image site-logo" src={Images.NO_INTERNET} />
                        </div>
                        <h2>{message}</h2>
                        {!isCompleted && <Countdown date={dateNow + (duration * 60000)} renderer={this.renderer} />}
                        <div onClick={this.onRefresh} className={"btn-primary mt30 no-data-button" + (!isCompleted ? ' disabled' : '')}>
                            <span>Try again</span>
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}

export default YouRInQueue;