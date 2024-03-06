import React from 'react';
import { Modal } from 'react-bootstrap';
import * as AppLabels from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';
import { Utilities } from '../Utilities/Utilities';

export default class Banner extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            bannerData: Utilities.getMasterData().banner || '',
        };
    }


    checkUrlDomain = (link, baseUrl) => {
        var isPathSame = false;
        const linkPath = new URL('', link);
        const baseUrlPath = new URL('', baseUrl);
        if (linkPath.hostname == baseUrlPath.hostname) {
            isPathSame = true;
        }
        return isPathSame;

    }

    loadInAppLink=(link,baseUrl)=>{
        let linkPath = link.split(baseUrl)[1];
        this.props.history.push({ pathname: linkPath.toLowerCase()})
        this.props.onBannerHide()
    }

    render() {

        const { isBannerShow, onBannerHide } = this.props;
        const { bannerData } = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={isBannerShow}
                        onHide={() => onBannerHide(false)}
                        dialogClassName="custom-modal banner-modal confirmation-modal"
                    >
                        <Modal.Header >
                            <div className='Confirm-header banner-app'> {bannerData.banner_title} </div>
                        </Modal.Header>

                        <Modal.Body className="p-0">
                            {
                                bannerData.banner_link.includes('http') && this.checkUrlDomain(bannerData.banner_link,process.env.REACT_APP_BASE_URL) ?
                                <a onClick={()=>this.loadInAppLink(bannerData.banner_link,process.env.REACT_APP_BASE_URL)} href>
                                    <img alt="" className="banner-image" src={Utilities.getAppBannerURL(bannerData.banner_image)}/>
                                </a>
                                :
                                <a onClick={()=> bannerData.banner_link.includes('http') && onBannerHide()} href={bannerData.banner_link.includes('http') && bannerData.banner_link} target={'_blank'}>
                                    <img alt="" className="banner-image" src={Utilities.getAppBannerURL(bannerData.banner_image)}/>
                                </a>
                            }
                            
                        </Modal.Body>
                        <Modal.Footer className="custom-modal-footer dissmiss-btn-footer">
                            <a href className='my-alert-button-text' onClick={() => onBannerHide()}>{AppLabels.DISMISS}</a>
                        </Modal.Footer>

                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}