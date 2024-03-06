import React, { Component } from 'react';
import Select from 'react-select';
import _ from 'lodash';
import HF from '../helper/HelperFunction';
class SportsDropdown extends Component {
    constructor(props) {
        super(props)
        this.state = {
            SelectedSports: "7",
            sports_list: HF.getSportsData() ? HF.getSportsData() : [],
        }
    }

    handleTypeChange = (value) => {
        this.setState({
            SelectedSports: value.value
        }, () => {
                this.props.SportsProps.modalCallback(this.state.SelectedSports)
        })
    }

    render() {
        return (
                <Select
                    searchable={false}
                    clearable={false}
                    class="form-control"
                    options={this.state.sports_list}
                    value={this.state.SelectedSports}
                    onChange={e => this.handleTypeChange(e)}
                />
        )
    }
}

export default SportsDropdown