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
import { _Map, _isEmpty, getPropsName } from '../../../Utilities/Utilities';

const API = {
    GET_MY_JOINED_TEAM: WSC.propsURL + "props/lobby/get_my_joined_team",
}

const UpcomingProps = ({ selectedTab, goLobby, MESSAGE_1, MESSAGE_2, ...props }) => {
    const [posting, setPosting] = useState(true)
    const [team_list, setTeamList] = useState([])
    const [team_player, setTeamPlayer] = useState([])
    const [expandedItem,setExpandedItem] = useState('')
    const [openExpand, setOpenExpand] = useState(true)
    const getMyJoinedTeam = (status) => {
        const params = {
            "sports_id": AppSelectedSport,
            "status": status
        }
        WSManager.Rest(API.GET_MY_JOINED_TEAM, params).then(({ response_code, data, ...res }) => {
            if (response_code == WSC.successCode) {
                setTeamList(data.team_list)
                setTeamPlayer(data.team_player)
                if(data.team_list && data.team_list[0] ) {
                    setExpandedItem(data.team_list[0].user_team_id)
                }
            }
            setPosting(false)
        });
    }

    useEffect(() => {
        if (selectedTab == 0) {
            getMyJoinedTeam(selectedTab)
        }
        return () => { }
    }, [selectedTab, AppSelectedSport])

    const gotoTeamDetails = ({user_team_id,isFromProps}) => {
        if((user_team_id == expandedItem && !isFromProps)){
            setExpandedItem('')
            setOpenExpand(false)
        }else{
            setExpandedItem(user_team_id)
            
        }

    }
    return (
        <div className='props-contest-list'>
            {
                _isEmpty(team_list) && !posting && selectedTab == 0 &&
                <NoDataView
                    BG_IMAGE={Images.no_data_bg_image}
                    CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                    MESSAGE_1={MESSAGE_1}
                    // MESSAGE_2={''}
                    BUTTON_TEXT={AppLabels.GO_TO_LOBBY}
                    onClick={goLobby}
                />
            }
            {
                _Map(team_list, (item, idx) => {
                    return (
                        <PropsTeamCard isUpcoming {...{
                            item,
                            team_player,
                            key: idx,
                            expandedItem : expandedItem,
                            onClickHandler: gotoTeamDetails,
                            index: idx
                        }} />
                    )
                })
            }
        </div>
    )
}
export default withRouter(UpcomingProps);