import React from 'react';
import { Modal, Table } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import WSManager from "../../WSHelper/WSManager";
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import { AppSelectedSport } from '../../helper/Constants';
import { Utilities } from '../../Utilities/Utilities';
import { getUserLineUpDetail } from '../../WSHelper/WSCallings';

export default class MyTeamViewAllModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            show: false,
            openTeam: '',
            teamPlayerData: {},
            allPosition: '',
            CollectionData: '',
        };


    }

    componentDidMount() {
        this.setState({ openTeam: this.props.openTeam, CollectionData: this.props.CollectionData, teamPlayerData: (this.props.teamPlayerData || {}) }, () => {
            this.getTeamPlayers(this.state.openTeam)
        })
    }

    getTeamPlayers = (item) => {

        let param = {
            "lineup_master_id": item.lineup_master_id,
            "collection_master_id": this.state.CollectionData.collection_master_id,
            "sports_id": AppSelectedSport,
        }

        this.setState({ isLoaderShow: true })
        getUserLineUpDetail(param).then((responseJson) => {

            this.setState({ isLoaderShow: false })

            if (responseJson.response_code == WSC.successCode) {

                let { teamPlayerData } = this.state;

                teamPlayerData['lineup_master_id'] = item.lineup_master_id;
                teamPlayerData.allPosition = responseJson.data.all_position;
                teamPlayerData.teamPlayerList = this.getPlayerOrder(responseJson.data.lineup, responseJson.data.all_position);

                this.setState({ teamPlayerData })
            }
        })
    }

    getPlayerOrder(lineupList, allPositionList) {

        for (let i = 0; i < lineupList.length; i++) {

            for (let j = 0; j < allPositionList.length; j++) {

                if (lineupList[i].position_name == allPositionList[j].position) {

                    lineupList[i]['position_display_name'] = allPositionList[j].position_display_name;
                    lineupList[i]['position_order'] = allPositionList[j].position_order;
                    break;
                }
            }
        }

        lineupList.sort(this.getSortOrder("position_order"));

        return lineupList;
    }

    //Comparer Function  
    getSortOrder(prop) {
        return function (a, b) {
            if (a[prop] > b[prop]) {
                return 1;
            } else if (a[prop] < b[prop]) {
                return -1;
            }
            return 0;
        }
    }

    render() {
        const { isViewAllShown, onViewAllHide } = this.props;
        const { teamPlayerData, openTeam } = this.state;
        let int_version = Utilities.getMasterData().int_version


        return (
            <MyContext.Consumer>
                {(context) => (
                    <div>
                        <Modal show={isViewAllShown} onHide={onViewAllHide} bsSize="large" dialogClassName="my-team-view-all-modal">
                            <Modal.Header>
                                <Modal.Title>
                                    <a onClick={onViewAllHide} className="modal-close">
                                        <i className="icon-close"></i>
                                    </a>
                                    <div className="name-container">
                                        <div className="team-name">{openTeam.team_name}</div>
                                        <div className="contests-joined">{openTeam.contest_joined_count} {AppLabels.CONTEST_JOINED}</div>
                                    </div>

                                </Modal.Title>
                            </Modal.Header>
                            <Modal.Body>
                                <div className="player-list-container">
                                    {
                                        teamPlayerData.teamPlayerList && teamPlayerData.teamPlayerList.map((item, index) => {
                                            return (
                                                <div className="player-list-item">
                                                    {
                                                        (index == 0 ? true : (item.position_name != teamPlayerData.teamPlayerList[index - 1].position_name)) &&

                                                        <div className="item-header">
                                                            <span>{item.position_display_name}</span>
                                                        </div>
                                                    }
                                                    <div className="item">
                                                        <Table>
                                                            <tbody>
                                                                <tr>
                                                                    <td className="left">
                                                                        <div className="image-container">
                                                                            <img className="player-image" src={Utilities.playerJersyURL(item.jersey)} alt="" />
                                                                            {
                                                                                this.state.openTeam.c_id == item.player_team_id ?
                                                                                    <span className='player-post'>{AppLabels.C}</span> :
                                                                                    this.state.openTeam.vc_id == item.player_team_id ?
                                                                                        <span className='player-post'>{AppLabels.VC}</span> :
                                                                                        ""
                                                                            }
                                                                        </div>

                                                                        <div className="player-name-container">
                                                                            <div className="player-name">{item.full_name}</div>
                                                                            <div className="team-vs-team">{item.team_abbreviation || item.team_abbr}</div>
                                                                        </div>
                                                                    </td>

                                                                    <td className="right">
                                                                        <div className="credit-container">
                                                                            <div className="credit-amount">{Utilities.numberWithCommas(item.salary)}</div>
                                                                            <div className="credit-text">{Utilities.getMasterData().int_version == "1" ? AppLabels.SALARIES : AppLabels.credit}</div>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </Table>
                                                    </div>
                                                </div>
                                            )
                                        })
                                    }
                                </div>
                            </Modal.Body>
                        </Modal>
                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}

