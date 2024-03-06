import React from 'react';


export default class XPNotification extends React.Component {

    constructor(props) {
        super(props);
        this.state = {

        }
    }
    
    render() {
       
        return (
            
                    <div className="list-card  card-notification congrats-notify">
                        <div className="media">
                            <div className="media-left media-middle">
                                <i className="icon-gift"></i>
                            </div>
                            <div className="media-body">
                                <h4 className="media-heading">Congratulations!</h4>
                                <p>You have reached <span class="highlighted-text">Level-4</span>. Exciting rewards unlocked, <span class="highlighted-text">learn more</span></p>
                                <div className="btm-info media">
                                    <div className="notification-timing">
                                    7 May 2.00 pm
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                
            
        )
    }
}