import React, { Component } from 'react';
import Select from 'react-select';
import _ from 'lodash';
class SelectDropdown extends Component {
    render() {
        let { is_searchable, is_clearable, class_name, sel_options, select_name, select_id, menu_is_open, is_disabled, selected_value, place_holder } = this.props.SelectProps
       
        return (
            <Select
                name={select_name}
                id={select_id}
                disabled={is_disabled}
                menuIsOpen={menu_is_open}
                searchable={is_searchable}
                clearable={is_clearable}
                className={class_name}
                options={sel_options}
                value={selected_value}
                placeholder={place_holder}
                onChange={e => this.props.SelectProps.modalCallback(e, select_name)}
                isOptionDisabled={(sel_options) => sel_options.disabled === true}
            />
        )
    }
}

export default SelectDropdown