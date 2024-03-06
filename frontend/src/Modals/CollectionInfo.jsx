
import React from 'react';
import { Modal, Button } from 'react-bootstrap';
import Images from '../components/images';
import { MyContext } from '../InitialSetup/MyProvider';
import * as AppLabels from "../helper/AppLabels";
import {DARK_THEME_ENABLE} from "../helper/Constants";

export default class CollectionInfoModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            show: false,
        };
    }

    render() {
        const { IsCollectionInfoShow, IsCollectionInfoHide } = this.props;
        
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div>
                        <Modal show={IsCollectionInfoShow} onHide={IsCollectionInfoHide} bsSize="large" dialogClassName="collection-modal" className="center-modal overflowy-hidden">
                            <Modal.Header closeButton>
                            </Modal.Header>
                            <Modal.Body>
                                <div className="text-center ">
                                <div className="multi-game-info">
                                <h2>{AppLabels.MULTIGAME_TITLE}</h2>
                                <div className="multi-game-detail">
                                {AppLabels.MULTIGAME_DETAIL} </div>
                                <div className="collection-image">
                                <img src={DARK_THEME_ENABLE ? Images.DT_MULTIGAME_INFO : Images.MULTIGAME_INFO} alt=""/>
                                <div className="select-multigame">
                                {AppLabels.SELECT_MULTIGAME_CONTEST_TITLE} </div>
                                </div>
                                <div className="select-multigame-detail">
                                {AppLabels.SELECT_MULTIGAME_CONTEST_DETAIL} 
                                </div>
                                <div className="collection-image collection-image2">
                                <img src={DARK_THEME_ENABLE ? Images.DT_MULTIGAME_INFO_CREATE_TEAM_LOGO : Images.MULTIGAME_INFO_CREATE_TEAM_LOGO} alt=""/>
                                </div>
                                <div className="select-multigame">
                                {AppLabels.CREATE_TEAM} </div>
                                <div className="select-multigame-detail">
                                {AppLabels.CHOOSE_PLAYER} 
                                </div>


                                </div>
                                    <Button className="btn btn-rounded btn-primary" onClick={IsCollectionInfoHide}>{AppLabels.GOT_IT}</Button>
                                </div>
                            </Modal.Body>
                        </Modal>
                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}

