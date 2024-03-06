import React from 'react';
import { Helper } from 'Local';
import { AsyncButton } from '..';
import NOdataImg from './no-data-view.png';
import { useHistory, useParams } from 'react-router-dom/cjs/react-router-dom.min';

const { Trans } = Helper
const NoDataView = (props) => {
  const params = useParams()
  const history = useHistory()
  const {msg,btntxt=''} = props

   const btnClick=()=>{
    history.push(`/dfs/lobby/${params.sports_id}`)
   }
  return <div className='no-data-container'>
      <div className="no-data-txt">
        {msg ? msg : <Trans> No Data</Trans>}
      </div>
      <div className="no-data-img">
        <img src={NOdataImg} alt=""  />
      </div>
      {
        btntxt  && btntxt != '' &&
        <AsyncButton
          btnProps={{
            className: ` btn-rounded`,
            bsStyle: 'primary',
            bsSize: 'small',
            type: 'submit',
            block: true
          }}
          onClick={btnClick}
        ><Trans>{btntxt}</Trans></AsyncButton>
      }
  </div>;
};

export default NoDataView;
