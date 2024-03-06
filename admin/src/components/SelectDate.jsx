import React, { Component } from 'react';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import { _isNull } from '../helper/HelperFunction';
import { _isUndefined } from '../helper/HelperFunction';
class SelectDate extends Component {

    openDatepicker = () => this._calendar.setOpen(true);

    render() {
        let { disabled_date, min_date, max_date, class_name, year_dropdown, month_dropdown, sel_date, date_key, place_holder, show_time_select, time_format, time_intervals, time_caption, date_format, popup_placement, show_cal_icon, cal_class,selectID } = this.props.DateProps

        return (
            <div>
                <DatePicker
                id={selectID || ''}
                    disabled={disabled_date}
                    minDate={!_isNull(min_date) ? new Date(min_date) : null}
                    maxDate={!_isNull(max_date) ? new Date(max_date) : null}
                    className={class_name}
                    showYearDropdown={year_dropdown}
                    showMonthDropdown={month_dropdown}
                    selected={!_isNull(sel_date) ? new Date(sel_date) : null}
                    onChange={dap => this.props.DateProps.handleCallbackFn(dap, date_key)}
                    placeholderText={place_holder}
                    showTimeSelect={show_time_select}
                    timeFormat={time_format}
                    timeIntervals={time_intervals}
                    timeCaption={time_caption}
                    dateFormat={date_format}
                    popperPlacement={popup_placement ? popup_placement : 'bottom-start'}
                    ref={(c) => this._calendar = c}
                />
                {
                    (!_isUndefined(show_cal_icon) && show_cal_icon) &&
                    <i className={`icon-calender ${cal_class}`} onClick={this.openDatepicker}></i>
                }
            </div>
        )
    }
}

export default SelectDate
