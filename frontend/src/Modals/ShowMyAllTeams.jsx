import React from 'react';
import {  Modal } from 'react-bootstrap';
import Images from '../components/images';
import * as AppLabels from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';
import { _Map } from '../Utilities/Utilities';

export default class ShowMyAllTeams extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            
        };
    }

    render() {

        const { show, hide,data } = this.props;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={show}
                        onHide={hide}
                        dialogClassName="custom-modal thank-you-modal my-team-modal" 
                        className="center-modal"
                        backdropClassName="transparent-backdrop"
                    >
                        <Modal.Header>
                            {/* <a href
                                onClick={()=>this.props.hide()}
                                >
                                    <i className="icon-close"></i>
                                </a> */}
                        </Modal.Header>
                        <form>
                            <Modal.Body>
                                <div className="my-teams-section">
                                    <div className="heading">{AppLabels.MYTEAMS}</div>
                                    {
                                        _Map(data,(item,idx)=>{
                                            return(
                                                <div>{item.team_name}</div>
                                            )
                                        })
                                    }
                                </div>
                            </Modal.Body>
                        </form>
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}