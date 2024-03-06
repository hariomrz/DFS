import React, { Component } from 'react';
import { Modal } from 'react-bootstrap';
import { MyContext } from '../../views/Dashboard';
import CustomLoader from '../../helper/CustomLoader';

class OpenFPPSourceUrl extends Component {
    constructor(props) {
        super(props)
        this.state = {
            showLoader: true
        }
    }
    
    hideSpinner=()=>{
        this.setState({
            showLoader: false
        })
    }

    render() {
        const { mShow, mHide , UrlData} = this.props;
        const { showLoader } = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        onHide={mHide}
                        dialogClassName="custom-modal view-proof-modal source-url-modal"
                        className="url-modal"
                    >
                        <div className="modal-header">
                            <a 
                                href
                                className="modal-close"
                                onClick={mHide}>
                                <i className="icon-close"></i>
                            </a>
                            <div className="url-name">{UrlData.source_url}</div>
                        </div>
                        {
                            showLoader &&
                            <CustomLoader />
                        }
                        <iframe load title='cricjam' className="iframe-source" src={UrlData.source_url} onLoad={()=>this.hideSpinner()}></iframe>
                    </Modal>
                )}
            </MyContext.Consumer>
        )
    }
}

export default OpenFPPSourceUrl;


