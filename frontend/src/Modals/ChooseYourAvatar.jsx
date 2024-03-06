import React from 'react';
import { Modal } from 'react-bootstrap';
import { _Map, Utilities } from '../Utilities/Utilities';
import { getAvatarList, setUserAvatar } from '../WSHelper/WSCallings';
import Images from '../components/images';
import * as WSC from "../WSHelper/WSConstants";
import * as AL from "../helper/AppLabels";
import WSManager from '../WSHelper/WSManager';

class ChooseYourAvatar extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            avatarArray: [''],
            selectedAvatar: '',
            isLoading: false
        };
    }

    componentDidMount() {
        this.getAvatarListCall()
    }

    getAvatarListCall = () => {
        var param = {
        }
        this.setState({ isLoading: true })
        getAvatarList(param).then((responseJson) => {
            this.setState({ isLoading: false })
            if (responseJson.response_code == WSC.successCode) {
                this.setState({ avatarArray: [...[''], ...responseJson.data] })
            }
        })
    }

    updateUserAvatar = () => {
        let param = {
            'image': this.state.selectedAvatar
        }
        this.setState({ isLoading: true });
        setUserAvatar(param).then((responseJson) => {
            this.setState({ isLoading: false });
            if (responseJson.response_code == WSC.successCode) {
                const { onSelectAvater } = this.props.data;
                onSelectAvater(param.image);
                let lsProfile = WSManager.getProfile();
                WSManager.setProfile({ ...lsProfile, ...param });
                Utilities.showToast(responseJson.message, 5000,'icon-user');
            }
        })
    }

    onChooseAvatar = (value) => {
        this.setState({
            selectedAvatar: value
        });
    }

    render() {
        const { onHide, showModal, onClickCamera } = this.props.data;
        const { avatarArray, selectedAvatar, isLoading } = this.state;
        return (
            <Modal
                show={showModal}
                dialogClassName="contest-detail-modal"
                className="contest-detail-dialog avatar-modal"
                bsSize="large"
            >
                <Modal.Header>
                    <a href onClick={onHide} className="modal-header-icon">
                        <i className="icon-close"></i>
                    </a>
                    <span className="modal-header-title">{AL.CHOOSE_AVATAR}</span>
                    <a href onClick={selectedAvatar && !isLoading ? this.updateUserAvatar : null} className={"modal-header-icon tick" + (selectedAvatar ? ' active' : '')}>
                        <i className="icon-tick"></i>
                    </a>
                </Modal.Header>
                <Modal.Body>
                    <div className="avatar-grid">
                        {
                            _Map(avatarArray, (item, idx) => {
                                return (
                                    <div key={item.id + item.name} className={"avatar-item" + (item.name == selectedAvatar && idx !== 0 ? " selected" : '')}>
                                        {
                                            idx === 0
                                                ?
                                                <div className="avatar-container" onClick={!isLoading ? onClickCamera : null} ><i className="icon-camera-fill" /></div>
                                                :
                                                <div className="avatar-container" onClick={() => !isLoading ? this.onChooseAvatar(item.name) : ''}>
                                                    <img className="img" src={Utilities.getThumbURL(item.name)} alt="" />
                                                </div>
                                        }
                                        <img src={Images.TICK_IC} alt="" className="circle-tick" />
                                    </div>
                                )
                            })
                        }
                    </div>
                </Modal.Body>

            </Modal>
        );
    }
}

export default ChooseYourAvatar;