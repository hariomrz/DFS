import React, { useEffect, useState } from 'react'
import { withRouter } from 'react-router-dom';
import * as WSC from "../../../WSHelper/WSConstants";
import WSManager from '../../../WSHelper/WSManager';
import PropsTeamCard from './PropsTeamCard';
import { AppSelectedSport } from '../../../helper/Constants';
import { NoDataView } from '../../CustomComponent';
import Images from '../../../components/images';
import * as Constants from "../../../helper/Constants";
import * as AppLabels from "../../../helper/AppLabels";
import { Utilities, _Map, _isEmpty } from '../../../Utilities/Utilities';

const API = {
    GET_MY_JOINED_TEAM: WSC.propsURL + "props/lobby/get_my_joined_team"
}

const LiveProps = ({ selectedTab, goLobby, MESSAGE_1, MESSAGE_2, ...props }) => {
    const [posting, setPosting] = useState(true)
    const [team_list, setTeamList] = useState([])
    const [team_player, setTeamPlayer] = useState([])
    const [propsIDs, setPropsIds] = useState({})
    const getMyJoinedTeam = (status) => {
        const params = {
            "sports_id": AppSelectedSport,
            "status": status,
            ...(_isEmpty(Utilities.getPropsIds(AppSelectedSport)) ? { is_props: 1 } : {})
        }
        WSManager.Rest(API.GET_MY_JOINED_TEAM, params).then(({ response_code, data, ...res }) => {
            if (response_code == WSC.successCode) {
                setTeamList(data.team_list)
                setTeamPlayer(data.team_player)
                if(_isEmpty(Utilities.getPropsIds(AppSelectedSport))) {
                    setPropsIds(data.props)
                    Utilities.setPropsIds(data.props, AppSelectedSport)
                }
            }
            setPosting(false)
        });
    }

    const gotoTeamDetails = ({user_team_id}) => {
        props.history.push({ pathname: `/props-fantasy/team-details/${selectedTab}/${user_team_id}` })
    }

    useEffect(() => {
        if (selectedTab == 1) {
            getMyJoinedTeam(selectedTab)
        }
        return () => { }
    }, [selectedTab])

    useEffect(() => {
        setPropsIds(Utilities.getPropsIds(AppSelectedSport))
        return () => { }
    }, [AppSelectedSport])
    
    return (
        <div className='props-contest-list'>
            {
                _isEmpty(team_list) && !posting && selectedTab == 1 &&
                <NoDataView
                    BG_IMAGE={Images.no_data_bg_image}
                    CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                    MESSAGE_1={MESSAGE_1 + ' ' + MESSAGE_2}
                    MESSAGE_2={''}
                    BUTTON_TEXT={AppLabels.GO_TO_LOBBY}
                    onClick={goLobby}
                />
            }
            {
                _Map(team_list, (item, idx) => {
                    return (
                        <PropsTeamCard isLive {...{
                            item,
                            team_player,
                            key: idx,
                            propsIDs,
                            onClickHandler: gotoTeamDetails,
                            isKeyWinning : true
                        }} />
                    )
                })
            }
        </div>
    )
}
export default withRouter(LiveProps);