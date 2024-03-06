import React from 'react';
import { CommonLabels } from "../../../../helper/AppLabels";
import { Utilities, withRouter } from '../../../../Utilities/Utilities';
import { useParams } from 'react-router-dom';


const PropsEntryStripe = ({ count, list, is_add_screen,pickRange, payoutData, team_details = {}, ...props }) => {
    const { user_team_id } = useParams()
    const {
        allow_props
    } = Utilities.getMasterData()
    let min_picks = parseInt(allow_props.min_picks)
    let max_picks = parseInt(allow_props.max_picks)

    const goToPropsMyEntry = () => {
        // let fil = payoutData.filter((obj) => obj.picks != count ? true : false)
        let fil = payoutData.some((obj) => parseInt(obj.picks) == count)

        if (!fil) {
            Utilities.showToast(count + ' ' + CommonLabels.PICK_POWER_PLAY_ERROR + " " + payoutData.map((item) => {
                return(
                    item.picks
                )
            }) + ' ' + CommonLabels.PICKS_TEXT)
        }
        else {
            if (user_team_id) {
                props.history.push({ pathname: `/props-fantasy/team/${user_team_id}`, state: { props_list: list, team_details: team_details, apicall: true, pickRange: pickRange, user_team_id } })
            } else {
                props.history.push({ pathname: '/props-fantasy/my-entries', state: { props_list: list, pickRange: pickRange } })
            }
        }
    }
    return (
        <div className={`props-entry-footer ${is_add_screen ? 'is_add_screen' : ''} ${(count < min_picks || count > max_picks) ? 'grey-out' : ''}`} onClick={() => (count >= min_picks && count <= max_picks) && goToPropsMyEntry()}>
            <div className='finalize'>
                {CommonLabels.FINALIZE_ENTRY}
            </div>
            <div className='selected'>
                <span className='v-selected'>{count} {CommonLabels.PLAYERS_SELECTED}</span>  <span class="icon-move-arrow"></span>
            </div>
        </div>
    );
};

export default withRouter(PropsEntryStripe);
