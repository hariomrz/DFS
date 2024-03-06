import React, { Component } from 'react';
class Loader extends Component {
    render() {
        return <div className={`component-loader${this.props.className ? ' ' + this.props.className : ''}`}>
            {
                !this.props.hide &&
                <span>
                    {this.props.label || 'Loading...'}
                </span>
            }
        </div>;
    }
}
Loader.propTypes = {};
export default Loader;
