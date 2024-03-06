import React, { Component } from 'react';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import { DATE_FORMAT} from "../helper/NetworkingConstants";
import { _isUndefined } from '../helper/HelperFunction';

// import { registerLocale } from "react-datepicker";
// import enIN from 'date-fns/locale/en-IN';
// registerLocale('enIN', enIN)

class SelectDate extends Component {
    render() {
        let { disabled_date, min_date, max_date, class_name, year_dropdown, month_dropdown, sel_date, date_key, place_holder, show_time_select, time_format, time_intervals, time_caption, date_format, popup_placement } = this.props.DateProps

        // console.log("---enIN---", enIN);
        
        return (
            <DatePicker
                disabled={disabled_date}
                minDate={new Date(min_date)}
                maxDate={new Date(max_date)}
                className={class_name}
                showYearDropdown={year_dropdown}
                showMonthDropdown={month_dropdown}
                selected={new Date(sel_date)}
                onChange={(dap) => this.props.DateProps.handleCallbackFn(dap, date_key)}
                placeholderText={place_holder}
                showTimeSelect={show_time_select}
                timeFormat={time_format}
                timeIntervals={time_intervals}
                timeCaption={time_caption}
                dateFormat={DATE_FORMAT}
                popperPlacement={popup_placement ? popup_placement : 'bottom-start'}
                // locale="enIN"
            />
        )
    }
}

export default SelectDate
