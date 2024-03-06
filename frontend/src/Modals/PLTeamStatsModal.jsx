import React, { Component } from 'react';
import { Modal } from 'react-bootstrap';
import { MyContext } from '../views/Dashboard';
import CustomLoader from '../helper/CustomLoader';

class PLTeamStatsModal extends Component {
    constructor(props) {
        super(props)
        this.state = {
            showLoader: true
        }
    }
    
    hideSpinner=()=>{
        this.setState({
            showLoader: false,
            showFullscreen : true,
            showUrlName: false
        })
    }
   

    render() {
        const { mShow, mHide , UrlData} = this.props;
        const { showLoader,showUrlName,showFullscreen } = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        onHide={mHide}
                        backdropClassName={'pl-team-stats-backdrop'}
                        dialogClassName="custom-modal view-proof-modal source-url-modal pl-team-stats"
                        className={"url-modal url-full-modal pl-team-stats"}
                    >
                        {/* <div className="modal-header">
                            <a 
                                href
                                className="modal-close"
                                onClick={mHide}>
                                <i className="icon-close"></i>
                            </a>
                            {
                                showUrlName &&
                                <div className="url-name">{UrlData}</div>
                            }
                        </div> */}
                        {
                            showLoader &&
                            <CustomLoader />
                        }
                        <a 
                            href
                            className="modal-close team-pl-close pl-close"
                            onClick={mHide}>
                            <i className="icon-close"></i>
                        </a>
                        <iframe load title='cricjam' class="iframe-source" src={UrlData} onLoad={()=>this.hideSpinner()}></iframe>

                    </Modal>
                )}
            </MyContext.Consumer>
        )
    }
}

export default PLTeamStatsModal;


