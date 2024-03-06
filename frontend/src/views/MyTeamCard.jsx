import React from "react";
import { Dropdown, MenuItem } from "react-bootstrap";
import * as AppLabels from "helper/AppLabels";
import { Utilities } from "../Utilities/Utilities";

const MyTeamCard = (props) => {
    const {
        item = {},
        selectedTeams = [],
        onSelectTeam = () => { },
        goToBench = () => { },
        openRosterCollection = () => { },
        openLineup = () => { },
        cloneLineup = () => { },
        showBooster = false,
        showBench = false,
        CollectionData = {},
        TotalTeam = [],
        isSecIn,

    } = props

    // let isPlayingAnnounced = props.LobyyData.playing_announce;
    // let boosterid = props.LobyyData && props.LobyyData.booster ? props.LobyyData.booster : item.booster;
    // let isBoosterEnable = !showDfsMulti && !isSecIn && Utilities.getMasterData().booster == 1 && boosterid && boosterid != '' && item.booster_id && parseInt(item.booster_id) == 0;
    // let showBooster =  !showDfsMulti && Utilities.getMasterData().booster == 1 && boosterid && boosterid != '' && !isSecIn ;
    // let showBench = (!showDfsMulti && isBenchEnable && !isSecIn && SELECTED_GAMET == GameType.DFS && ( isPlayingAnnounced == 0 || (isPlayingAnnounced == 1 && item.bench_applied == 1))) ? true : false;
    // let showBenchErr = !showDfsMulti && isBenchEnable && !isSecIn && SELECTED_GAMET == GameType.DFS && isPlayingAnnounced != 1 && item.bench_applied != 1 && !is_tour_game ;
    console.log(props);

    return (
        <div className="my-team-card-sm">
            <div className="my-team-header">
                <div className="mth-title">
                    <h2>Dreamsquad</h2>
                    <div className="subtitile">5 contests joined</div>
                </div>
                <div className="mth-ctrl">

                    {(TotalTeam && TotalTeam.length < parseInt(Utilities.getMasterData().a_teams)) &&
                        <i id='clone-button' title="Clone this team" className="icon-copy-ic icn-action" onClick={(e) => cloneLineup(CollectionData, item, e)} />
                    }



                    {
                        (showBooster || showBench) ?
                            <Dropdown id="dropdown-custom-1" className="more-option-dp" onClick={(e) => e.stopPropagation()}>
                                <Dropdown.Toggle>
                                    <i className="icon-more-large icn-action" />
                                </Dropdown.Toggle>
                                <Dropdown.Menu className="super-colors">
                                    {
                                        showBench &&
                                        <MenuItem eventKey="1" onClick={(e) => goToBench(CollectionData, item, e)}>
                                            {/* //,childItem,teamItem */}
                                            <i className="icon-bench"></i>
                                            <span className='fs8'>{AppLabels.BENCH}</span>
                                        </MenuItem>
                                    }
                                    {
                                        showBooster &&
                                        <MenuItem eventKey="2" onClick={(e) => openRosterCollection(CollectionData, item, e)}>
                                            <i className="icon-booster"></i>
                                            <span className='fs8'>
                                                {AppLabels.BOOSTERS}
                                            </span>
                                        </MenuItem>
                                    }
                                    <MenuItem eventKey="3" onClick={(e) => openLineup(CollectionData, CollectionData, item, true, null, false, e)}>
                                        <i className="icon-edit-line"></i>
                                        <span className='fs8'>{AppLabels.EDIT}</span>
                                    </MenuItem>
                                </Dropdown.Menu>
                            </Dropdown>
                            :
                            <i title="Edit this team" className="icon-edit-line icn-action" onClick={(e) => openLineup(CollectionData, CollectionData, item, true, null, false, e)} />
                    }
                    <div onClick={(e) => onSelectTeam(e, item)} className={"select-team-checkbox icn-action selected" + (selectedTeams.includes(item) ? 'selected' : '')}>
                        <i className="icon-tick-ic" />
                    </div>
                </div>
            </div>


        </div>
    );
}
export default MyTeamCard