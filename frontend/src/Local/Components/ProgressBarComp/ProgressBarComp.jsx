import React from 'react';
import { ProgressBar } from 'react-bootstrap';

const ProgressBarComp = (props) => {
  const {total,min,size, p_eff} = props.data

  const ShowProgressBar = (join, totalSize) => {
    return join * 100 / totalSize;
}

  return (
    <div className={!p_eff?'progress-bar-sec':'prgs-sec'}>
       <ProgressBar now={ShowProgressBar(total,size)} className={parseInt(total) >= parseInt(min) ? '' : 'danger-area'} />
       <div className='pb-info'>
        <div><span>{total}</span> / {size}</div>
        <div>Min {min}</div>
       </div>
    </div>
  );
};

export default ProgressBarComp;
