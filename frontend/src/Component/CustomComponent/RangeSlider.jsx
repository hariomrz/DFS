import React, { Component } from 'react'
import Range from 'rc-slider/lib/Range';
export default class RangeSlider extends Component {
    render() {
        const { defaultValue, min, max, onAfterChange } = this.props;
        return (
            <Range defaultValue={defaultValue} min={min} max={max} onAfterChange={onAfterChange}/>
        )
    }
}
