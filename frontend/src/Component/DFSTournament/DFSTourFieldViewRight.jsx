
import React from 'react';
import DFSTourFieldView from "./DFSTourFieldview";

export default class DFSTourFieldViewRight extends React.Component {
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
        if(nextProps && (nextProps.MasterData != this.state.nextProps.MasterData || nextProps.LobyyData != this.state.nextProps.LobyyData || nextProps.SelectedLineup != this.state.nextProps.SelectedLineup)){
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
        // const {SelectedLineup,MasterData, LobyyData,FixturedContest,isFrom,team,rootDataItem,isFromMyTeams,ifFromSwitchTeamModal} = this.props
        return(
            <div className="field-view-right hide-sm-below">
                {this.state.reloadFieldView &&
                    <DFSTourFieldView 
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
                        userTeamInfo={this.state.nextProps.userTeamInfo}
                    />
                }
            </div>
        );
    }
}