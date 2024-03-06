import React, { Component } from "react";
import { Modal, ModalHeader, ModalBody, ModalFooter, Table } from 'reactstrap';
import { MomentDateComponent } from "../../components/CustomComponent";
import HF from '../../helper/HelperFunction';



class ViewFixtureModal extends Component {
    constructor(props) {
        super(props)
        this.state = { StatusmodalIsOpen: false }

    }


    render() {
        const { openViewModal, viewModalData } = this.props;
        return (
            <div>
                <Modal
                    isOpen={openViewModal}
                    className="inactive-modal view-modal-data-view"
                >
                    <ModalHeader className="modal-header">
                        <div>Fixtures </div>
                        <div onClick={() => this.props.closeViewModalReq()} className="cursor-pointer">X</div>
                    </ModalHeader>
                    <ModalBody>
                        {viewModalData.length > 0 && viewModalData.map((item) => {
                            return (
                                <div className="data-view">
                                    <p className="home-away">{item.home} vs {item.away}</p>
                                    <p className="sch-date">
                                        {/* <MomentDateComponent data={{ date: item.scheduled_date, format: "D MMM hh:mmA" }} /> */}
                                        {HF.getFormatedDateTime(item.scheduled_date, "D MMM hh:mmA")}
                                        </p>
                                </div>
                            )
                        })}
                    </ModalBody>
                </Modal>
            </div>
        )
    }
}
export default ViewFixtureModal
