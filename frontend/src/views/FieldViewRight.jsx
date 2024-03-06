
import React from 'react';
import FieldView from "./FieldView";

class FieldViewRight extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            reloadFieldView: true,
            nextProps: ''
        }
    }

    componentDidMount = () => {
        if(this.props.isFromUpcoming || this.props.isFromLeaderboard){
            this.setState({
                nextProps: this.props,
                reloadFieldView: false
            },()=>{
                this.setState({
                    reloadFieldView: true
                })
            })
        }
    }
    
    UNSAFE_componentWillReceiveProps(nextProps){
        if(nextProps.updateTeamDetails != this.props.updateTeamDetails) {
            this.setState({
                nextProps: nextProps,
                reloadFieldView: false
            },()=>{
                this.setState({
                    reloadFieldView: true
                })
            })
        }
    }

    render(){
        const { updateTeamDetails } = this.props
        return(
            <div className={`field-view-right hide-sm-below ${this.state.nextProps.show ? 'show-modal-view' : ''}`}>
                {this.state.reloadFieldView &&
                    <FieldView 
                        SelectedLineup= {this.state.nextProps.SelectedLineup}
                        MasterData= {this.state.nextProps.MasterData}
                        LobyyData= {this.state.nextProps.LobyyData}
                        FixturedContest= {this.state.nextProps.FixturedContest}
                        isFrom={this.state.nextProps.isFrom}
                        isFromUpcoming={this.state.nextProps.isFromUpcoming}
                        rootDataItem={this.state.nextProps.rootDataItem}
                        team={this.state.nextProps.team}
                        team_name={this.state.nextProps.team_name}
                        resetIndex={this.state.nextProps.resetIndex} 
                        TeamMyContestData={this.state.nextProps.TeamMyContestData}
                        isFromMyTeams={this.state.nextProps.isFromMyTeams}
                        ifFromSwitchTeamModal={this.state.nextProps.ifFromSwitchTeamModal}
                        rootitem={this.state.nextProps.rootitem}
                        sideViewHide={this.state.nextProps.sideViewHide}
                        isFromLeaderboard={this.state.nextProps.isFromLeaderboard}
                        current_sport={this.state.nextProps.current_sports_id}
                        isFromTeamComp={this.state.nextProps.isFromTeamComp || false}
                        benchPlayer={this.state.nextProps.benchPlayer || []}
                        isReverseF= {this.state.nextProps.isReverseF}
                        isSecIn= {this.state.nextProps.isSecIn}
                        isPlayingAnnounced= {this.state.nextProps.isPlayingAnnounced || 0}
                        isBenchUC= {this.state.nextProps.isBenchUC || false}
                        isBSOFV= {this.state.nextProps.isBSOFV || false}
                        isNF= {this.state.nextProps.isNF || false}
                        team_count={this.state.nextProps.team_count ? this.state.nextProps.team_count : this.props.team_count ? this.props.team_count :  false}
                        lData={this.state.nextProps.rootitem}
                        fixtureData={this.state.nextProps.fixtureData || []}
                        isFromtab={this.state.nextProps.isFromtab || ''}
                        from={this.state.nextProps.from}
                        updateTeamDetails={updateTeamDetails}

                    />
                }
            </div>
        );
    }
}


FieldViewRight.defaultProps = {
    updateTeamDetails: new Date().valueOf()
}

export default FieldViewRight