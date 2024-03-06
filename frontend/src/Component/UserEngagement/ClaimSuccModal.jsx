import React from 'react';
import { Modal } from 'react-bootstrap';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities } from '../../Utilities/Utilities';
import $ from 'jquery'; 
import CountUp from 'react-countup';

export default class ClaimSuccModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            init: false,
            collectionStart: false
        };

    }

    componentDidMount() {
        setTimeout(() => {
            this.setState({
                init: true
            },()=>{
                setTimeout(() => {
                    this.InitCoinAnimation() 
                }, 1500);
            })
        }, 1500);
        setTimeout(() => {
            this.props.isHide()
        }, 6500);
    }

    InitCoinAnimation = () => {

    const _this = this
    let $cart = $("#CoinCart");
    let $btn = $("#CoinSource");
    const { collectionStart } = this.state
    if (collectionStart === false) {
      _this.setState({
        collectionStart: true
      });
      let collectCoin = setInterval(function () {
        var $coin = $('<div class="floating-coin">')
          .insertAfter($btn)
          .css({
            "left": $btn.offset().left,
            "top": $btn.offset().top
          })
          .animate({
            "top": $cart.offset().top,
            "left": $cart.offset().left
          }, 1000, function () {
            $coin.remove();
          });
      }, 100)
      setTimeout(function () {
        clearInterval(collectCoin);
        _this.setState({
          collectionStart: false
        });  
      }, 2000)
    } else {
      _this.setState({
        collectionStart: false
      });
      $('<div class="floating-coin">').remove()
    }
  }

    render() {

        const { isShow, isHide, totalWonAmt , userCoinBalnc} = this.props;
        const { init } = this.state;
        let endCoinBal = parseInt(this.props.userCoinBalnc) + parseInt(totalWonAmt)
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={isShow}
                        onHide={isHide}
                        dialogClassName="claim-succ-modal"
                        className="xcenter-modal"
                    >
                        <Modal.Body>
                            <div className="mystery-main-wrap">
                                <div style={{width: '100%'}}>
                                    <div className="top-coin-bal-sec" id="CoinCart">
                                        <img className="coin-img" src={Images.IC_COIN} alt="" />
                                        <CountUp
                                            end={endCoinBal}
                                            duration="1.5"
                                            start={userCoinBalnc}
                                            delay="4"
                                        />
                                    </div>
                                </div>
                                <div className="centered-sec">
                                    {/* <img src={Images.TREASURE_BOX} alt="" /> */}
                                    <div className="box-body-back-bg" id="CoinSource"></div>
                                    <div className={`box-body ${init ? 'init' : ''}`} >
                                        <div className="box-lid">
                                            <div className="box-bowtie"></div>
                                        </div>
                                    </div>
                                    <div className="succ-msg">{AL.CONGRATULATIONS_YOU_GOT}</div>
                                    <div className="succ-won-amt"> <img src={Images.IC_COIN} />{totalWonAmt}</div>
                                </div>
                            </div>
                        </Modal.Body>

                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}