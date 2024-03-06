import React, { useEffect, useState } from "react";
import { Modal } from 'react-bootstrap';
import { CommonLabels } from "../../../../helper/AppLabels";
import * as AppLabels from "../../../../helper/AppLabels";
import { Utilities } from "../../../../Utilities/Utilities";


const PropsLobbyFilter = (props) => {
    const { filteredList, filterDatabyMatch, filteredData, submitFilter, selectedFilteredArray, hideFilteredData, filterDatabyLeague, filteredLeague, filterbyLeague } = props
    const [filter, setFilter] = useState(0)
    const [matchFilter, setMatchFilter] = useState([])
    const [leagueFilter, setLeagueFilter] = useState([])

    const _filterDatabyMatch = (id) => {
        let temp = [...matchFilter]
        if (temp.includes(id)) {
            let sfd = temp.indexOf(id)
            temp.splice(sfd, 1)
        }
        else {
            temp['is_selected'] = 1
            temp.push(id)
        }
        setMatchFilter(temp)
    }

    const _filterDatabyLeague = (id) => {
        let temp = [...leagueFilter]
        if (temp.includes(id)) {
            let sfd = temp.indexOf(id)
            temp.splice(sfd, 1)
        }
        else {
            temp[0] = id
        }
        setLeagueFilter(temp)
    }


    const ActiveFilter = (item) => {
        setFilter(item)
    }

    useEffect(() => {
        if (filteredData) {
            setMatchFilter(selectedFilteredArray)
            setLeagueFilter(filterbyLeague)
        }
        return () => { }
    }, [filteredData])


    return (
        <Modal
            show={props.filteredData}
            className="inactive-modal gps props-filter-data"
            style={{ zIndex: 2000, background: 'none' }}
            onHide={hideFilteredData}
        >
            <Modal.Header>
                <div className="fil-head">
                    <i className="icon-reload props-reload" onClick={() => submitFilter()}></i>
                    <span className="props-fil">{AppLabels.FILTERS}</span>
                    <span className={`props-done ${(matchFilter.length > 0 || leagueFilter.length > 0) ? '' : 'disabled'}`}
                        onClick={filter == '1' ? () => filterDatabyMatch(matchFilter) : () => filterDatabyLeague(leagueFilter)}
                    >{AppLabels.DONE}</span>
                </div>
            </Modal.Header>
            <Modal.Body>
                <div className="filter-tab-view">
                    <div className="filter-view">
                        <div className={`league-match-container ${filter == '0' ? " active" : ''}`} onClick={() => ActiveFilter('0')}>{AppLabels.LEAGUE}</div>
                        <div className={`league-match-container ${filter == '1' ? " active" : ''}`} onClick={() => ActiveFilter('1')}>{CommonLabels.MATCH_TEXT}</div>
                    </div>
                </div>
                {filter == '0' && filteredLeague && filteredLeague.map((item) => {
                    return (
                        <div className="wrap" onClick={() => _filterDatabyLeague(item.league_id)}>
                            <div><span className="matches">{item.league_name}</span></div>
                            <div className={`circle ${leagueFilter.includes(item.league_id) ? 'active' : ''}`}>
                                <i className='icon-tick-ic'></i>
                            </div>
                        </div>
                    )
                })
                }

                {filter == '1' && filteredList && filteredList.map((item) => {
                    return (
                        <div className="wrap" onClick={() => _filterDatabyMatch(item.season_id)}>
                            <div><span className="matches">{item.match}</span>  <span className="dates"> | {Utilities.getFormatedDateTime(item.date, 'MMM DD, hh:mm a')}</span></div>
                            <div className={`circle ${matchFilter.includes(item.season_id) ? 'active' : ''}`}>
                                <i className='icon-tick-ic'></i>
                            </div>
                        </div>
                    )
                })
                }
            </Modal.Body>
        </Modal>
    )
}

export default PropsLobbyFilter