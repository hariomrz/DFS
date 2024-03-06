import React, { Suspense, lazy } from 'react';
import { FormGroup, Button, Checkbox } from 'react-bootstrap';
import Modal from 'react-modal';
import * as AppLabels from "../helper/AppLabels";
import { _isEmpty } from "../Utilities/Utilities";
import { MyContext } from './../InitialSetup/MyProvider';
import { GameType, SELECTED_GAMET } from '../helper/Constants';
const ReactSlidingPane = lazy(()=>import('../Component/CustomComponent/ReactSlidingPane'));
export default class FilterByTeam extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            isPaneOpen: false,
            isPaneOpenLeft: false,
            isPaneOpenBottom: true,
            checkbox: false,
            selectedTeamOption: this.props.selectedTeamOption
        };
    }

    componentDidMount() {
        Modal.setAppElement(this.el);
    }

    handleTeamChange = (item) => {
        this.setState({
            selectedTeamOption: item
        })
    }

    render() {

        const { teamName, onSelected } = this.props;


        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="filter-container">
                        <div ref={ref => this.el = ref} >
                            <Suspense fallback={<div />} ><ReactSlidingPane
                                isOpen={this.state.isPaneOpenBottom}
                                from='bottom'
                                width='100%'
                                onRequestClose={this.handleFilterClose}
                            >
                                <div className="filter-header shadow">
                                    <i className="icon-reload" onClick={() => onSelected(teamName[0])}></i>
                                    {AppLabels.Filters}
                                    <Button className="done-btn active" onClick={() => onSelected(this.state.selectedTeamOption)}>{AppLabels.DONE}</Button>
                                </div>
                                <div className="filter-body">
                                    <ul className='pt10'>
                                        {
                                            !_isEmpty(teamName)
                                                ?
                                                teamName.map((item, index) => {
                                                    return (
                                                        <li className='pt10 pb10 pl15 pr15 bottom-padding' key={"leagueList" + index}>
                                                            {
                                                                SELECTED_GAMET == GameType.StockFantasy ?
                                                                <FormGroup>
                                                                    <Checkbox className="custom-checkbox" value={item.value.team_league_id} onChange={() => this.handleTeamChange(item)} checked={this.state.selectedTeamOption ? (this.state.selectedTeamOption.value.team_league_id == item.value.team_league_id) : index == 0 && true} name="lobby_filter_leagues" id={"lobbyfilter-" + item.value.team_league_id}>
                                                                        <span>{item.label}</span>
                                                                    </Checkbox>
                                                                </FormGroup>
                                                                :
                                                                <FormGroup>
                                                                    <Checkbox className="custom-checkbox" value={item.value.team_id} onChange={() => this.handleTeamChange(item)} checked={this.state.selectedTeamOption ? (this.state.selectedTeamOption.value.team_id == item.value.team_id) : index == 0 && true} name="lobby_filter_leagues" id={"lobbyfilter-" + item.value.team_id}>
                                                                        <span>{item.label}</span>
                                                                    </Checkbox>
                                                                </FormGroup>
                                                            }
                                                        </li>
                                                    );


                                                })


                                                :
                                                <li></li>

                                        }

                                    </ul>


                                </div>
                            </ReactSlidingPane></Suspense>
                        </div>

                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}