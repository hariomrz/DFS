import React, { Component } from 'react';
import { Modal } from 'react-bootstrap';
import { claimSpinTheWheel } from '../WSHelper/WSCallings';
import { Utilities } from '../Utilities/Utilities';
import WSManager from '../WSHelper/WSManager';
import * as WSC from "../WSHelper/WSConstants";
import { setValue } from '../helper/Constants';
import * as AL from "../helper/AppLabels";
import _ from "lodash";

class SpeenWheelModal extends Component {
  constructor(props) {
    super(props);
    this.state = {
      showModal: false,
      hideSkip: false
    };
  }
  componentDidMount() {
    this.loadWheel()
  }

  // componentWillReceiveProps(nextProps) {
  //   console.log('next')
  //   if(nextProps.preData.showSpinWheel != this.state.showModal){
  //   //   console.log('next12')
  //   //   this.setState({
  //   //     showModal: nextProps.preData.showSpinWheel || this.state.showModal
  //   //   },()=>{
  //   //     if(this.state.showModal){
  //         // this.loadWheel()
  //   //     }
  //   //   })
  //   }
  // }
  
  loadWheel=()=>{
    if (this.props.preData) {
      const { data } = this.props.preData;
      window.spin2WheelLoaded = () => {
        this.setState({
          showModal: true
        })
        return {
          loadJSON: data.wheel_data,
          myResult: this.wheelResult,
          myError: this.wheelError,
          myGameEnd: this.wheelEnd,
          btnRef: this.spinBtn
        }
      }
      if (window.initSpin) {
        window.initSpin();
      } else {
        var script = document.createElement("script");
        script.src = "./spin2wheel/js/index.js";
        script.type = "text/javascript";
        script.onload = script.onreadystatechange = () => {
          window.initSpin();
        };
        document.getElementsByTagName("body")[0].appendChild(script);
      }
    }
  }

  wheelResult = (e) => {
    //e is the result object
    // console.log('Spin Count: ' + e.spinCount + ' - ' + 'Win: ' + e.win + ' - ' + 'Message: ' + e.msg);
    if (e.userData) {
      console.log('User defined score: ' + e)
      this.claimTodaysWheel(e);
    }

    /*  if(e.spinCount == 3){
        show the game progress when the spinCount is 3
        console.log(e.target.getGameProgress());
        restart it if you like
        e.target.restart();
      }*/

  }

  wheelEnd = (e) => {
    //e is gameResultsArray
    console.log(e);
  }

  wheelError = (e) => {
    //e is error object
    console.log('Error: Spin Count: ' + e.spinCount + ' - ' + 'Message: ' + e.msg);
  }

  claimTodaysWheel = (e) => {
    let param = {
      spinthewheel_id: e.userData.spinthewheel_id
    }
    claimSpinTheWheel(param).then((responseJson) => {
      if (responseJson.response_code == WSC.successCode) {
        
        Utilities.showToast(e.msg || responseJson.message || '', 3000, 'icon-user');
        const data = {};
        let todayString = new Date().toDateString();
        data['day_string'] = todayString;
        data['claimed'] = 1;
        WSManager.setWheelData(data);

        const { wheel_data } = this.props.preData.data
        let fltData = _.filter(wheel_data.segmentValuesArray, (obj) => {
          if(obj.userData.spinthewheel_id == e.userData.spinthewheel_id) return obj.userData
        })
        let _data = fltData[0].value.split(' ')

        setTimeout(() => {
          Utilities.gtmEventFire('spin_wheel', {
            "prize": _data[0]
          })
          this.props.preData.succSpinWheel();
        }, 10)
      }
      this.setState({
        showModal: false
      }, () => {
        this.props.preData.updateUserBal()
        this.props.preData.mHide();
      })
    })
  }

  skipSpinWheel=()=>{
    setValue.skipSpinWheel();
    this.props.preData.mHide();
  }

  componentWillUnmount() {
    this.setState = () => {
      return;
    };
  }

  spinClick = () => {
    this.setState({
      hideSkip : true
    })
  }

  render() {
    return (
      <Modal
        show={this.state.showModal}
        className="spin2win-modal"
        bsSize="large"
      >
        <Modal.Body>
          <div id="container">
            <div className="wheelContainer">
              {!this.state.hideSkip && <a href className="skip-spinwheel" onClick={()=>this.skipSpinWheel()}>{AL.SKIP_STEP}</a>}
              <div className="spin-wheel-heading">{AL.SPIN_THE_WHEEL_TEXT}</div>
              <svg className="wheelSVG" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlnsXlink="http://www.w3.org/1999/xlink" textRendering="optimizeSpeed" preserveAspectRatio="xMidYMin meet">
                <defs>
                  <filter id="shadow" x="-100%" y="-100%" width="550%" height="550%">
                    <feOffset in="SourceAlpha" dx="0" dy="0" result="offsetOut"></feOffset>
                    <feGaussianBlur stdDeviation="9" in="offsetOut" result="drop" />
                    <feColorMatrix in="drop" result="color-out" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 .3 0" />
                    <feBlend in="SourceGraphic" in2="color-out" mode="normal" />
                  </filter>
                </defs>
                <g className="mainContainer">
                  <g className="wheel">
                  </g>
                </g>
                <g className="centerCircle" />
                <g className="wheelOutline" />
                <g className="pegContainer" opacity="1">
                  <path className="peg" fill="#EEEEEE" d="M22.139,0C5.623,0-1.523,15.572,0.269,27.037c3.392,21.707,21.87,42.232,21.87,42.232 s18.478-20.525,21.87-42.232C45.801,15.572,38.623,0,22.139,0z" />
                </g>
                <g className="valueContainer" />
                <g className="centerCircleImageContainer" />
              </svg>
              <div className="toast toast-msg">
                <p></p>
              </div>
              <button onClick={this.spinClick} ref={(ref) => this.spinBtn = ref} className=" spinBtn">{AL.SPIN}</button>
            </div>
          </div>
        </Modal.Body>
      </Modal>
    );
  }
}

export default SpeenWheelModal;