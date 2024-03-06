import React from "react";
import { Images } from "OpinionTrade/Lib";

const EmptyScreen = ({title,toggle=true,btnTitle='',actionBtn}) => {
    return (
        <>
           <div className="empty-container">
                <img alt="" src={Images.BG_EMPTY}/>
                <div className="span-text">{title}</div>
                {
                    !toggle &&
                        <div onClick={()=>actionBtn('')} className="view-btn">
                            <div className="span-btn-text">{btnTitle}</div>
                        </div>
                }
           </div>
        </>
    )
   
};

export default EmptyScreen;
