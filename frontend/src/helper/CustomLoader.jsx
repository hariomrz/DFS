import React from 'react';
export default class CustomLoader extends React.Component {
    render() {
        const {isFrom}=this.props
        return (
            <div className="loader-back">
                <div className={"loader-custom" +(isFrom=="Guru" ? ' guru': '')}></div>
            </div>
        );
    }
}