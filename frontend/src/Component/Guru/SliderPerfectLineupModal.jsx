import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { _Map, Utilities } from '../../Utilities/Utilities';
import { Swipeable } from 'react-swipeable'
import { SELECTED_GAMET, OnlyCoinsFlow } from '../../helper/Constants';
import { updateUserSettings } from '../../WSHelper/WSCallings';
import CustomHeader from '../../components/CustomHeader';
import Images from '../../components/images';
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import WSManager from '../../WSHelper/WSManager';
import { Modal } from 'react-bootstrap';

class SliderPerfectLineupModal extends Component {
    constructor(props) {
        super(props);
        this.isFirst = (props.location && props.location.state) ? props.location.state.isFirst : false;
        this.state = {
            HOS: {
                back: !this.isFirst,
                title: AL.WHATSNEW,
                skip: true,
                skipAction: this.skipBtnClicked
            },
            SIN: 'slide-in-r',
            CSI: 0,
            FLOAD: 0,
            gameType: SELECTED_GAMET,
            SLIDRD: [
                {
                    title: AL.PL_1_TITLE,
                    decription: AL.PL_1_DESCRIPTION,
                    image: Images.PL_NEW
                },
                {
                    title: AL.PL_2_TITLE,
                    decription: AL.PL_2_DESCRIPTION,
                    image: Images.PL2
                },
                {
                    title: AL.PL_3_TITLE,
                    decription: AL.PL_3_DESCRIPTION,
                    image: Images.PL3
                },
                {
                    title: AL.PL_4_TITLE,
                    decription: AL.PL_4_DESCRIPTION,
                    image: Images.PL4
                },
                {
                    title: AL.PL_5_TITLE,
                    decription: AL.PL_5_DESCRIPTION,
                    image: Images.PL5
                }
            ]
        }
    }
   
  
    componentDidMount() {
     }

    UNSAFE_componentWillMount() {
        if (this.state.gameType && this.isFirst) {
            WSManager.removeLSItem('SHGT');
        }
    }


    completedWhatsNew = () => {
        let profile = WSManager.getProfile();
        let param = profile.user_setting || {};
        if (param.earn_coin == "1") {
            this.props.history.push({ pathname: '/' });
        } else {
            param["earn_coin"] = "1";
            param["user_id"] = undefined;
            param["_id"] = undefined;

            profile['user_setting'] = param;
            WSManager.setProfile(profile);
            if (this.state.gameType) {
                setTimeout(() => {
                    CustomHeader.showSHSCM();
                }, 100);
                WSManager.setPickedGameType(this.state.gameType);
            }
            setTimeout(() => {
                this.props.history.push({ pathname: '/' });
            }, 50);

            updateUserSettings(param).then((responseJson) => {
                if (responseJson.response_code == WSC.successCode) {
                    CustomHeader.showCoinCM();
                }
            })
        }
    }

    skipBtnClicked = () => {
        if (this.isFirst) {
            this.completedWhatsNew();
        } else {
            this.props.history.goBack();
        }
    }

    nextBtnAction = () => {
        const { CSI, SLIDRD } = this.state;
        const length = (SLIDRD.length - 1);
        this.changleSlider(CSI < length ? (CSI + 1) : CSI)
        if (CSI === length) {
            if (!this.isFirst) {
                this.props.history.goBack();
            } else {
                this.completedWhatsNew();
            }
        }
    }

    preBtnAction = () => {
        const { CSI } = this.state;
        this.changleSlider(CSI > 0 ? (CSI - 1) : CSI)
    }

    onSwiped = (eventData) => {
        const { CSI, SLIDRD } = this.state;
        if (eventData && eventData.dir === "Left" && CSI < (SLIDRD.length - 1)) {
            this.nextBtnAction();
        }
        if (eventData && eventData.dir === "Right") {
            this.preBtnAction();
        }
    }

    changleSlider = (value) => {
        const length = (this.state.SLIDRD.length - 1)
        if (this.state.CSI != value) {
            this.setState({
                CSI: value,
                FLOAD: this.state.FLOAD < value ? value : this.state.FLOAD,
                SIN: value >= this.state.CSI ? 'slide-in-r' : 'slide-in-l',
                HOS: {
                    back: !this.isFirst,
                    title: AL.WHATSNEW,
                    skip: value < length,
                    skipAction: this.skipBtnClicked
                }
            })
        }
    }

    styleCss(CSI, index) {
        const { SIN } = this.state;
        if (CSI === index) {
            return ('active ' + SIN)
        } else if (CSI > index && index < 2) {
            return ('slide-out-r')
        }
        else if (CSI < index && index > 0) {
            return 'slide-out-l' + (this.state.FLOAD < index ? ' no-anim' : '');
        }

    }
    goToPerFectLineup=()=>{
        window.open('www.google.com', "_blank")  
      }
 renderTagMessage=(msg)=>{
        if (msg.includes('multiple teams')) {
            msg = msg.replace("multiple teams", '<span style={{"color":"#FA274E","text-transform":"uppercase"}} class="highlighted-text">' + 'multiple teams' + '</span>');
            //msg = msg.replace("BEST TEAM", '<span style={{"color":"#FA274E"}} class="highlighted-text">' + 'BEST TEAM' + '</span>');

        }
        else if(msg.includes('player’s form')){
            msg = msg.replace("player’s form", '<span style={{"color":"#FA274E","text-transform":"uppercase"}} class="highlighted-text">' + 'player’s form' + '</span>');

        }
        else if(msg.includes('comparisons')){
            msg = msg.replace("comparisons", '<span style={{"color":"#FA274E","text-transform":"uppercase"}} class="highlighted-text">' + 'comparisons' + '</span>');

        }
        else if(msg.includes('stats')){
            msg = msg.replace("stats", '<span style={{"color":"#FA274E","text-transform":"uppercase"}} class="highlighted-text">' + 'stats' + '</span>');

        }
        else if(msg.includes('graphs')){
            msg = msg.replace("graphs", '<span style={{"color":"#FA274E","text-transform":"uppercase"}} class="highlighted-text">' + 'graphs' + '</span>');

        }
        
        return msg

      }

    renderSPage = (isDescription) => {
        const { SLIDRD, CSI } = this.state;
        return (
            <React.Fragment>
                {
                    _Map(SLIDRD, (item, index) => {
                        return (
                            <div name={"slide-" + item.title} key={index} className={"c-slide " + this.styleCss(CSI, index)}>
                                {
                                    !isDescription ? 
                                     <div className="slider-title">
                                    {/* {item.title} */}
                                    <p dangerouslySetInnerHTML={{ __html: this.renderTagMessage(item.title) || '--' }}></p>

                                </div>
                                :
                                <div className="slider-desc">
                                    {item.decription}
                                </div>

                                }
                                {
                                  !isDescription  &&  <img alt="" src={item.image} />
                                }
                                {
                                    isDescription &&
                                <div  className='click-here-pl'>{AL.CLICK_HERE}</div>
                                }
                               
                            </div>
                        )
                    })
                }
            </React.Fragment>
        )
    }
    renderSDots = () => {
        const { SLIDRD, CSI } = this.state;
        return (
            <ul className="slider-dots-ul">
                {
                    _Map(SLIDRD, (item, index) => {
                        return <li name={item.title} key={index} value={index} onClick={(e) => this.changleSlider(e.target.value)} className={CSI === index ? "active" : ""} />
                    })
                }
            </ul>
        )
    }



    renderFooterBtns = () => {
        const { CSI, SLIDRD } = this.state;
        const length = (SLIDRD.length - 1)
        return (
            <div className="footer-btns" >
                <a href onClick={this.preBtnAction} name={CSI + 'prev'} className={"header-action skip-step" + (CSI > 0 ? '' : ' btn-hide')}>
                    {AL.PREV}
                </a>
                <a href onClick={this.nextBtnAction} name={CSI + 'next'} className="header-action skip-step active">
                    {CSI === length ? AL.GOTIT : AL.NEXT}
                </a>
            </div>
        )
    }

    render() {
        const { HOS } = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (

                    <Modal
                        show={this.props.IsPerfectLineupSliderShow}
                        onHide={this.props.IsperfectLineupSliderHide}
                        dialogClassName="custom-modal pl-modal"
                        className="center-modal"
                    >


                        <Modal.Body>
                            <div>
                            <div className="pl-slider">
                                <Swipeable className="swipe-view" onSwiped={this.onSwiped} >
                                    <div onClick={(e) => this.props.goToPerFectLineup()}  className="slider-container">
                                        <div className="slides">
                                            {this.renderSPage(false)}
                                        </div>
                                        {this.renderSDots()}
                                        <div className="slides">
                                            {this.renderSPage(true)}
                                        </div>
                                        {/* {this.renderFooterBtns()} */}
                                    </div>
                                </Swipeable>
                                <div className='bottom-view'></div>


                            </div>

                            </div>
                           



                        </Modal.Body>
                        <Modal.Footer className='custom-modal-footer dual-btn-footer'>

                        </Modal.Footer>
                    </Modal>
                )}
            </MyContext.Consumer>
        )
    }
}

export default SliderPerfectLineupModal;