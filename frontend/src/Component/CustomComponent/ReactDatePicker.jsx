import React, { Component } from 'react';
import DatePicker from 'react-date-picker';

export default class ReactDatePicker extends Component {
    render() {
        const { id, className, required, disabled, activeStartDate, minDetail, locale, onChange, maxDate, value, isOpen } = this.props;
        return (
            <DatePicker
                id={id}
                className={className}
                required={required}
                disabled={disabled}
                activeStartDate={activeStartDate}
                minDetail={minDetail}
                locale={locale}
                onChange={onChange}
                maxDate={maxDate}
                value={value}
                isOpen={isOpen}
            />
        )
    }
}
