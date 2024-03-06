import { Utilities } from 'Utilities/Utilities';
import React, { PureComponent } from 'react';
const secInDay = 86400;
const timeS = 365.25 * secInDay;

class Countdown extends PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            currentTimeStamp: 0,
            days: 0,
            hours: 0,
            min: 0,
            sec: 0,
        }
    }

    componentDidMount() {
        this.setState({ currentTimeStamp: Date.now() }, () => {
            this.startTimer(this.props.deadlineTimeStamp)
        })
    }

    componentWillUnmount() {
        this.stop(true);
        this.setState = () => {
            return;
        };
    }

    calculateCountdown(endDate) {
        let diff = (endDate - Date.now()) / 1000;
        // clear countdown when date is reached

        if (diff <= 0) return false;

        const timeLeft = {
            years: 0,
            days: 0,
            hours: 0,
            min: 0,
            sec: 0,
            millisec: 0,
        };

        // calculate time difference between now and expected date
        if (diff >= timeS) { // 365.25 * 24 * 60 * 60
            timeLeft.years = Math.floor(diff / timeS);
            diff -= timeLeft.years * timeS;
        }
        if (diff >= secInDay) { // 24 * 60 * 60
            timeLeft.days = Math.floor(diff / secInDay);
            diff -= timeLeft.days * secInDay;
        }
        if (diff >= 3600) { // 60 * 60
            timeLeft.hours = Math.floor(diff / 3600);
            diff -= timeLeft.hours * 3600;
        }
        if (diff >= 60) {
            timeLeft.min = Math.floor(diff / 60);
            diff -= timeLeft.min * 60;
        }
        timeLeft.sec = diff;

        return timeLeft;
    }

    setTimerValue(timeLeft) {
        this.setState({
            days: timeLeft.days,
            hours: timeLeft.hours,
            min: timeLeft.min,
            sec: parseInt(timeLeft.sec),
            currentTimeStamp: Date.now() + 1000
        })
    }

    startTimer(deadlineTimeStamp) {
        const date = this.calculateCountdown(deadlineTimeStamp);
        date ? this.setTimerValue(date) : this.stop();

        this.interval = setInterval(() => {
            const date = this.calculateCountdown(deadlineTimeStamp);
            date ? this.setTimerValue(date) : this.stop();
        }, 1000);
    }

    stop(isWillUnmount) {
        if (this.props.timerCallback && !isWillUnmount) {
            this.props.timerCallback(true)
        }
        this.setState({
            days: 0,
            hours: 0,
            min: 0,
            sec: 0
        })
        clearInterval(this.interval);
    }

    addLeadingZeros(value) {
        value = String(value);
        while (value.length < 2) {
            value = '0' + value;
        }
        return value;
    }

    render() {
        const {isCompleted} = this.props
        let cTime = this.state.hours + ':' + this.state.min + ':' + this.state.sec;
        return (
            (cTime !== '0:0:0' && this.state.days <= 0 && !isCompleted) ?
                <span style={{color:"red"}} >
                   {!this.props.hideHrs && <> <strong>{this.addLeadingZeros(this.state.hours)}</strong>
                    <span>:</span></>}
                    <strong>{this.addLeadingZeros(this.state.min)}</strong>
                    {!this.props.hideSecond && <>
                    <span>:</span>
                    <strong>{this.addLeadingZeros(this.state.sec)}</strong>
                    </>}
                    {/* <span></span> */}
                </span>
                :
                <span>{Utilities.getFormatedDateTime(this.props.scheduled_date,'DD MMM , hh:mm A')}</span>
        );
    }
}

export default Countdown;