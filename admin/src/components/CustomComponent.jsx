import React from "react";
import Moment from "react-moment";
import WSManager from '../helper/WSManager';
export function MomentDateComponent({ data }) {
    let date = data.date;
    let format = data.format;
    return (date ? <Moment date={WSManager.getUtcToLocal(date)} format={format} /> : '')
}