import React from 'react';
import {useTimer} from "react-timer-hook";
import { Helper } from 'Local';

const {Utils} = Helper
const Timer = ({date, callback = () => {}, data}) => {
    const {
        days,
        hours,
        seconds,
        minutes
    } = useTimer({
        expiryTimestamp: Utils.getTodayTimestamp(date),
        onExpire: callback,
        autoStart: true
    });
    return (
        days === 0 ?
            <div
                className={`timer ${data?'link-normal':'link-danger'}`}>{Utils.digit(hours)}{' : '}{Utils.digit(minutes)}{' : '}{Utils.digit(seconds)}</div>
            :
            <>{Utils.getUtcToLocal(date)}</>
    )
}

export default Timer;
