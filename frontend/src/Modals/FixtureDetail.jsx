import React, { Component } from 'react';
// import { getTournamentMatchList } from "../WSHelper/WSCallings";
import { _Map } from '../Utilities/Utilities';
import { MyContext } from '../views/Dashboard';
import { Modal } from 'react-bootstrap';
import Images from '../components/images';
import * as AL from "../helper/AppLabels";
import WSManager from '../WSHelper/WSManager';
import { MomentDateComponent } from "../Component/CustomComponent";
import ScheduleComponent from './ScheduleComponent';
import * as Constants from "../helper/Constants";

class FixtureDetail extends Component {
    constructor(props) {
        super(props)
        this.state = {
            TData: [],
            matchList: []
        }
    }

    componentDidMount() {
        // this.getMatchList()
        this.setState({
            TData: this.props.data,
            matchList: this.props.data.match_list
        })
    }

    componentWillReceiveProps(nextProps) {
        // if (this.state.completedContestList !== nextProps.completedContestList) {
        //     this.setState({ completedContestList: nextProps.completedContestList })
        // }
    }

    // getMatchList = async (offset) => {
    //     let param = {
    //         "sports_id": Constants.AppSelectedSport,
    //         "tournament_id": this.props.TournamentId
    //     }

    //     this.setState({ isLoaderShow: true, isListLoading: true })

    //     var api_response_data = await getTournamentMatchList(param);

    //     if (api_response_data && api_response_data.data) {
    //         let matchListArray = api_response_data.data.match_list ? this.getGroupArray(api_response_data.data.match_list) : [];
    //         this.setState({
    //             TData: api_response_data.data,
    //             matchList: matchListArray
    //         })
    //     }
    // }

    groupData = (data) => {
        let result = data.reduce((groups, match) => {
            const date = match.season_scheduled_date.split(' ')[0];
            if (!groups[date]) {
                groups[date] = [];
            }
            groups[date].push(match);
            return groups;
        }, {});

        return result;
    }

    getGroupArray = (match_list) => {
        let groups = this.groupData(match_list);
        let groupArrays = Object.keys(groups).map((date) => {
            return {
                date,
                matches: groups[date]
            };
        });
        return groupArrays;
    }

    renderItem = (obj) => {
        return <ScheduleComponent key={obj.season_game_uid} item={obj} date={obj.date} />
    }


    render() {
        const { mShow, mHide, LobyyData } = this.props;
        // const { matchList, TData } = this.state;
        let TData = this.props.data;
        let matchList = this.props.LobyyData.match_list ? this.getGroupArray(this.props.LobyyData.match_list) : [];
        console.log('TData', matchList)
        return (
           <React.Fragment>
               {
                   _Map(matchList, (obj) => {
                       return this.renderItem(obj)
                   })

               }
           </React.Fragment>
        )
    }
}

export default FixtureDetail;
