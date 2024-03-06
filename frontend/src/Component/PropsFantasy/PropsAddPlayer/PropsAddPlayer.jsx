import React from 'react';
import PropsLobby from '../PropsLobby';
import CustomHeader from '../../../components/CustomHeader';
import { useLocation, useParams } from 'react-router-dom';
import { CommonLabels } from "../../../helper/AppLabels";
const PropsAddPlayer = (props) => {
  const { user_team_id } = useParams()
  const HeaderOption = {
    back: false,
    notification: false,
    hideShadow: true,
    isPrimary: true,
    title: CommonLabels.ADD_PLAYERS.toUpperCase()
  }
  return (
    <div className='web-container web-container-fixed top57'>
      <CustomHeader HeaderOption={HeaderOption} {...props} />
      <PropsLobby {...props} is_add_screen user_team_id={user_team_id}
        isFrom='add_player' />
    </div>
  );
};

export default PropsAddPlayer;
