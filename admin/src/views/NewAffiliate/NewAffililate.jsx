import React, { Component } from 'react';
import WSManager from '../../helper/WSManager';
import * as NC from '../../helper/NetworkingConstants';
class NewAffililate extends Component {

    componentDidMount = ()=>{
        window.open(NC.baseURLAffiliate+'/affiliate?token='+WSManager.getToken()+'&role='+WSManager.getRole());
        this.props.history.replace('/dashboard');
        // this.props.history.pop('/affiliate');
    }

    render() {
        return (
            <div>
                
            </div>
        );
    }
}

export default NewAffililate;