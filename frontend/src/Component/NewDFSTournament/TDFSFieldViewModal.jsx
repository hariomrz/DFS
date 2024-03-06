import React from 'react';
import { Modal } from 'react-bootstrap';
import { _Map } from "../../Utilities/Utilities";
import { MyContext } from '../../InitialSetup/MyProvider';
import { AppSelectedSport} from '../../helper/Constants';
import FieldView from "../../views/FieldView";

class TDFSFieldViewModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
        };

    }

    render() {

        const { mShow, mHide,AllLineUPData,hideFieldV,showFieldV,activeUserDetail } = this.props;
       
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        onHide={mHide}
                        dialogClassName="custom-modal tour-fieldview"
                        className="center-modal"
                        backdropClassName='tour-fieldview-backdrop'
                    >
                        <Modal.Body>
                            <FieldView
                                SelectedLineup={AllLineUPData ? AllLineUPData.lineup : ''}
                                MasterData={AllLineUPData || ''}
                                isFrom={'rank-view'}
                                showTeamCount={true}
                                // isFromLBPoints={true}
                                team_name={AllLineUPData ? (AllLineUPData.team_info.team_name || '') : ''}
                                showFieldV={showFieldV}
                                userName={activeUserDetail.user_name}
                                hideFieldV={hideFieldV}
                                current_sport={AppSelectedSport}
                                team_count={AllLineUPData ? AllLineUPData.team_count : []}
                            />
                        </Modal.Body>
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}
export default TDFSFieldViewModal;