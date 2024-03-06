import React from 'react';
import { Modal } from 'react-bootstrap';
import { Utilities, _Map } from "../Utilities/Utilities";
import * as AL from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';
import Images from '../components/images';
import { AppSelectedSport } from '../helper/Constants';



export default class TournamentLeaderboardModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {

        };

    }




    gotoTourDetail = (id) => {
        this.props.history.push({
            pathname: '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/dfs-tournament-detail/' + id,
            state: {
                tourId: id,
            }
        })
    }




    render() {

        const { showTourLeadModal, closeMoreTour, TourFilter } = this.props;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={showTourLeadModal}
                        onHide={closeMoreTour}
                        dialogClassName="custom-modal rules-scoring-modal header-circular-modal overflow-hidden"
                        className="center-modal"
                    >
                        <Modal.Header >
                            <div className="modal-img-wrap">
                                <div className="wrap">
                                    <img src={Images.TOUR_LEADERBOARD} width="28" />
                                </div>
                            </div>
                            {AL.AVAILABLE_TOURNAMENTS}
                        </Modal.Header>

                        <Modal.Body className="static-page">
                            <React.Fragment>
                                {TourFilter.map((item) => {
                                    return (
                                        <div className='tour-lead-wrap' onClick={() => this.gotoTourDetail(item.tournament_id)}>
                                            <h3 className='tour-lead-heading'>{item.name}</h3>
                                            <h6 className='tour-lead-detail'>
                                                {item.no_of_fixture != "0" ?
                                                    <>
                                                        {AL.TOP_NEW_N_FIXTURES1} {item.no_of_fixture} {AL.TOP_NEW_N_FIXTURES2}
                                                    </>
                                                    :
                                                    <>
                                                        {
                                                            item.is_top_team == "0" ?
                                                                <>{AL.ALL_TEAM_ALL_FIXTURES}</> :
                                                                <>{AL.TOP_TEAM_ALL_FIXTURE}</>
                                                        }
                                                    </>
                                                }

                                            </h6>
                                        </div>)
                                })}
                            </React.Fragment>
                        </Modal.Body>
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}