import React, { Component } from 'react';
import Select from 'react-select';

export default class ReactSelectDD extends Component {   
    render() {
        const { id, onChange, classNamePrefix, className, options, value,menuPlacement, arrowRenderer, placeholder, isSearchable, isClearable, theme } = this.props;
        return (
            <Select
                id={id || ''}
                onChange={onChange || ''}
                classNamePrefix={classNamePrefix || ''}
                className={className || ''}
                options={options || []}
                value={value || ''}
                menuPlacement={menuPlacement || 'auto'}
                arrowRenderer={arrowRenderer || ''}
                placeholder={placeholder || ''}
                isSearchable={isSearchable || false}
                isClearable={isClearable || false}
                theme={theme || {}}
            />
        )
    }
}
