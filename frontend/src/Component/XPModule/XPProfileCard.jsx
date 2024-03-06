import React from 'react';
import { Button } from "react-bootstrap";
import * as AL from "../../helper/AppLabels";
import Images from '../../components/images';
export default class XPProfileCard extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
        }
    }
        /**
     * @description Method to show progress bar
     * @param {*} point - current point that user have
     * @param {*} total - points for next level
     */
    calcPer = (point, total) => {
        point = parseInt(point);
        total = parseInt(total);
        let per = ((point / total) * 100).toFixed(2)+ '%';
        return per;
    }

    goToPage=(pathname)=>{
        this.props.history.push({ pathname: pathname, state: {goBackProfile: true, userXPDetail: this.props.userXPDetail } });
    }
    
    render() {
        const {userXPDetail,publicProfile} = this.props;
        let isMaxPt = userXPDetail.max_level == userXPDetail.level_number;
        let total = isMaxPt ? parseInt(userXPDetail.max_end_point) - parseInt(userXPDetail.start_point) : parseInt(userXPDetail.next_level_start_point) - parseInt(userXPDetail.start_point);
        let point = parseInt(userXPDetail.point) - parseInt(userXPDetail.start_point); 
        let maxExc = (userXPDetail.max_end_point && parseInt(userXPDetail.point) > parseInt(userXPDetail.max_end_point)) ? true : false;
        return ( 
                    
            // {/* <div className="xpprofile-card">
            // <div className="border-box-inn-sec">
            //     <div className="xpprofile-card-body">
            //         {
            //             !publicProfile &&
            //             <a href className="share-public-profile" onClick={() => this.props.shareProfile()}><i className="icon-share"></i></a>
            //         }
            //         <div className="level-heading-text">
            //         <h4>{((maxExc && isMaxPt) || !isMaxPt) && <span className='lewel-text'>{AL.LEVEL} {userXPDetail.next_level}</span>} {maxExc ? AL.REACHED_MAXIMUM_LEVEL : isMaxPt ? AL.MAXIMUM_LEVEL_REACHED : AL.IS_CLOSER_THAN_YOU_THINK}</h4>
            //         </div>
            //         <div className="xpprofile-card-slider">
            //             <div className="progress-bar" style={{ width: (maxExc ? '100%' : this.calcPer(point,total)) }}></div>
            //             {!maxExc &&<span>{userXPDetail.level_number}</span>}
            //             {
            //                 maxExc ?
            //                 <span className="next-lvl">{userXPDetail.level_number}{maxExc && <>+</>}</span>
            //                 :
            //                 <span className="next-lvl">{userXPDetail.next_level}</span>
            //             }
            //         </div>
            //          <div className='xpprofile-level-view'>
            //          {!maxExc &&<div className='xpprofile-img-text'><img className='xp-level-with-name' src={userXPDetail.level_number == 1 ? Images.XP_BRONZE : userXPDetail.level_number == 2 ? Images.XP_SILVER : userXPDetail.level_number == 3 ? Images.XP_GOLD : userXPDetail.level_number == 4 ? Images.XP_PLATINUM : userXPDetail.level_number == 5 ? Images.XP_DIAMOND : userXPDetail.level_number == 6 ? Images.XP_ELITE : Images.XP_DEFAULT_BADGE} alt="" /><span className='level-text-one'>{AL.LEVEL}  {userXPDetail.level_number}</span></div>}
            //         <div className="xpprofile-card-slider">
            //             <div className="progress-bar" style={{ width: (maxExc ? '100%' : this.calcPer(point,total)) }}></div>
            //         </div>
            //         {
            //                 maxExc ?
            //                 <div className='xpprofile-img-text'><img className='xp-level-with-name' src={userXPDetail.level_number == 1 ? Images.XP_BRONZE : userXPDetail.level_number == 2 ? Images.XP_SILVER : userXPDetail.level_number == 3 ? Images.XP_GOLD : userXPDetail.level_number == 4 ? Images.XP_PLATINUM : userXPDetail.level_number == 5 ? Images.XP_DIAMOND : userXPDetail.level_number == 6 ? Images.XP_ELITE : Images.XP_DEFAULT_BADGE} alt="" /><span className="next-lvl next-level-text">{AL.LEVEL} {userXPDetail.level_number}{maxExc && <>+</>}</span></div>
            //                 :
            //                 <div className='xpprofile-img-text'><img className='xp-level-with-name' src={userXPDetail.next_level == 1 ? Images.XP_BRONZE : userXPDetail.next_level == 2 ? Images.XP_SILVER : userXPDetail.next_level == 3 ? Images.XP_GOLD : userXPDetail.next_level == 4 ? Images.XP_PLATINUM : userXPDetail.next_level == 5 ? Images.XP_DIAMOND : userXPDetail.next_level == 6 ? Images.XP_ELITE : Images.XP_DEFAULT_BADGE} alt="" /><span className="next-lvl next-level-text">{AL.LEVEL} {userXPDetail.next_level}</span></div>
            //             }
            //          </div>

            //         {
            //             !isMaxPt &&
            //             <div className="xpprofile-card-details">
            //                 <div><span>{userXPDetail.point}</span>/{userXPDetail.next_level_start_point} {AL.XP_TO_LEVEL} {userXPDetail.next_level}</div>
            //             </div>
            //         }
            //     </div>
            //     {
            //         !publicProfile &&
            //         <div className="xpprofile-card-footer">
            //             <div>
            //                 <span className={'text-uppercase ' + (userXPDetail.point && userXPDetail.point.length > 5 ? 'font-sm' : '')}><img src={Images.EARN_XPPOINTS} alt="" width="20px" /> {userXPDetail.point} {AL.XP}</span>
            //                 <a href onClick={()=>this.goToPage('/experience-points-history')}>{AL.XP_POINTS_HISTORY}</a>
            //             </div>
            //             <div>
            //                 <Button className="button button-primary-rounded-sm" onClick={()=>this.goToPage('/experience-points')}>{AL.EARN_XP}</Button>
            //                 <Button className="button button-primary-rounded-sm" onClick={()=>this.goToPage('/experience-points-levels')}>{AL.SEE_LEVELS}</Button>
            //             </div>
                        
            //         </div>
            //     }
            // </div>
            // </div>   */}
            <div className="xpprofile-card">
            <div className="border-box-inn-sec border-box-inn-sec-new">
                <div className="xpprofile-card-body">
                    
                    <div className="level-text-heading">
                    {AL.YOUR_CURRENT_XP_LEVEL}
                    </div>
                    <div className="xpprofile-card-slider xpprofile-card-slider-new">
                        <div className="progress-bar progress-bar-new" style={{ width: (maxExc ? '100%' : this.calcPer(point,total)) }}></div>
                        {!maxExc &&<span>{userXPDetail.level_number}</span>}
                        {
                            maxExc ?
                            <span className="next-lvl">{userXPDetail.level_number}{maxExc && <>+</>}</span>
                            :
                            <span className="next-lvl">{userXPDetail.next_level}</span>
                        }
                    </div>
                    
                    <div className='earn-xp-button'>
                            <Button className="button button-primary-rounded-sm" onClick={()=>this.goToPage('/experience-points')}><img src={Images.EARN_XPPOINTS} alt="" width="16px" /> {AL.EARN_XP}</Button>
                            
                        </div>
                   
                </div>
            </div>
            </div>  
           
        )
    }
}