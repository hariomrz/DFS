import React, { Component } from 'react';
import { Modal } from 'react-bootstrap';
import { MyContext } from '../views/Dashboard';
import CustomLoader from '../helper/CustomLoader';

class PLPlayerCardModal extends Component {
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
                        dialogClassName="custom-modal view-proof-modal source-url-modal"
                        className={"url-modal url-full-modal" 
                            // + (showFullscreen ? ' url-full-modal' : '')
                        }
                    >
                        {/* <div className="modal-header">
                            <a 
                                href
                                className="modal-close "
                                onClick={mHide}>
                                <i className="icon-close"></i>
                            </a>
                            {
                                showUrlName &&
                                <div className="url-name">{UrlData}</div>
                            }
                        </div> */}
                        <a 
                            href
                            className="modal-close pl-close"
                            onClick={mHide}>
                            <i className="icon-close"></i>
                        </a>
                        {
                            showLoader &&
                            <CustomLoader />
                        }
                        <iframe load title='cricjam' class="iframe-source" src={UrlData} onLoad={()=>this.hideSpinner()}></iframe>

                    </Modal>
                )}
            </MyContext.Consumer>
        )
    }
}

export default PLPlayerCardModal;


