import React, { Component } from 'react';

class CircularProgressBar extends Component {
    constructor(props) {
        super(props)
        this.state = {
            showSelected: '',
            showCP: false,
        }
    }

    render() {
        const { progressPer, isSF,maxPlayers } = this.props;
        return (
            <div className="circular-progress" data-percentage={(progressPer || 0) * (isSF ? (maxPlayers ? maxPlayers : 10) : 1)}>
                <span className="progress-left">
                    <span className="progress-bar"></span>
                </span>
                <span className="progress-right">
                    <span className="progress-bar"></span>
                </span>
                <div className="progress-value">
                    <div>
                    {/* <img src={Utilities.teamFlagURL(detail.home_flag)} alt=""/> */}
                        {progressPer || 0}<span>%</span>
                    </div>
                </div>
            </div>
        )
    }
}

export default CircularProgressBar;