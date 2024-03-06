import React, { useEffect } from 'react';
import GuruFiledView from './GuruFiledView';

const GuruFieldViewModal = (props) => {
    useEffect(() => {
        if(document) {
            document.body.style.overflow = 'hidden';
        }
      return () => {
        document.body.style.overflow = '';
      }
    }, [document])
    
    return (
        <div className='guru-field-view-modal'>
            <GuruFiledView {...props}/>        
        </div>
    );
}

export default GuruFieldViewModal