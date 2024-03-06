import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { Modal } from 'react-bootstrap';
import { MomentDateComponent } from '../CustomComponent';
import * as AL from "../../helper/AppLabels";
import app_config from "../../InitialSetup/AppConfig";

class ViewTDSFileModal extends Component {
    constructor(props) {
        super(props)
        this.state = {
            posting: false
        }
    }


    render() {
        const { mShow, mHide, selectedItem, downloadFile } = this.props.preData;
        let pdfUrl = `${app_config.s3.BUCKET}upload/tds/${selectedItem.file_name}#toolbar=0&navpanes=0&scrollbar=0&view=fitH`
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        bsSize="xs"
                        className="modal-tds">
                        <Modal.Body >
                            <div className='tds-modal-head'>
                                <div className='head-tds'>
                                    {AL.TDS_CERTIFICATE}
                                </div>
                                <div className='date-tds'>
                                    <span>{AL.DATE}</span>
                                    <MomentDateComponent data={{ date: selectedItem.date_added, format: "DD/MM/YY" }} />
                                </div>
                            </div>
                            <iframe className='fram-border' src={pdfUrl} frameborder="0" width="100%" />
                            <div className='multi-btn-f'>
                                <button className={"btn btn-outline-border "} onClick={() => mHide()}>{AL.CANCEL}</button>
                                <button className={"btn btn-primary "} onClick={() => downloadFile(selectedItem.file_name)}>{AL.DOWNLOAD}</button>
                            </div>

                        </Modal.Body>
                    </Modal>
                )}
            </MyContext.Consumer>
        )
    }
}

export default ViewTDSFileModal;