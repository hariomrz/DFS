import React from 'react';
import { Modal } from 'react-bootstrap';
import * as AppLabels from "../../helper/AppLabels";
import { MyContext } from '../../views/Dashboard';
import Images from '../../components/images';
import { Utilities } from '../../Utilities/Utilities';
import WSManager from '../../WSHelper/WSManager';
import * as WSC from "../../WSHelper/WSConstants";
import { redeemRewards } from '../../WSHelper/WSCallings';
import CustomHeader from '../../components/CustomHeader';
export default class ConfrimRedeem extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      userCoinB: (WSManager.getBalance().point_balance || 0),
      userCoinBalnc: (WSManager.getBalance().point_balance || 0),
      isApiCalling: false,
    };
  }

  btnAction = (value) => {
    if (!this.state.isApiCalling) {
        let preBal = parseInt(this.state.userCoinB);
        let updatedBal = preBal - parseInt(value.redeem_coins);
        let param = {
            coin_reward_id: value.coin_reward_id['$oid']
        }
        this.setState({
            isApiCalling: true
        })
        redeemRewards(param).then((responseJson) => {
            this.setState({
                isApiCalling: false
            })
            if (responseJson.response_code === WSC.successCode) {
              this.props.preData.mHide(updatedBal);
                
            CustomHeader.updateCoinBalance(updatedBal);
            this.setState({ userCoinB: updatedBal });
            CustomHeader.showRSuccess(value);
            }
        })
    }
} 
  render() {
    const { mShow, mHide, cpData } = this.props.preData;
    return (
      <MyContext.Consumer>
        {(context) => (
          <>
            <Modal
              show={mShow}
              onHide={mHide}
              dialogClassName="custom-modal thank-you-modal redeem-confirm-modal "
              className="center-modal custom-bg-modal-open"
            >
              <a href className="close-header"  onClick={mHide}><i className="icon-close"></i></a>
              <div>
                <Modal.Body>
                  <div className="confirm-redeem-coin">
                    <div className="img-container">
                      <img src={cpData.type === "3" ? Utilities.getRewardsURL(cpData.image) : (cpData.type === "1" ? Images.REDEEM_COIN_IMG : Images.REDEEM_CASH_IMG1)} alt=""/>
                    </div>
                    <div className='detail-text-container'>
                    {
                            cpData.type === "1" ?
                            <>{AppLabels.REDEEM + ' ' + cpData.value + ' ' + AppLabels.BONUS_CASH_LOWER}</> :
                            cpData.type === "3" ?
                            <>{cpData.detail}</> :
                            <>{AppLabels.REDEEM + ' ' +  (Utilities.getMasterData().currency_code) + cpData.value + ' ' + AppLabels.REAL_MONEY}</>
                        }
                    </div>
                    <div className='notes-container'>
                        {AppLabels.NOTES}
                        <div className="notes-text">{AppLabels.REDEEM_NOTE_TEXT1}</div>
                        <div className="notes-text">{AppLabels.REDEEM_NOTE_TEXT2}</div>
                    </div>
                    <div className='button-container' 
                    //  onClick={() =>  this.props.preData.btnAction(cpData)}
                    onClick={() => this.btnAction(cpData)}
                    >
                      {AppLabels.REDEEM_FOR}
                    <img className="coin-img" src={parseInt(cpData.redeem_coins || '0') > parseInt(this.state.userCoinB) ? Images.IC_COIN_GRAY : Images.IC_COIN} alt="" />{cpData.redeem_coins}
                    
                    </div>
                    <div className='avbl-conatainer'>
                      {AppLabels.AVAIL_BAL} <img className="coin-img" src={Images.IC_COIN} /> {Utilities.numberWithCommas(Utilities.kFormatter(this.state.userCoinBalnc))}
                    </div>
                  </div>

                </Modal.Body>

              </div>
            </Modal>
          </>
        )}
      </MyContext.Consumer>
    );
  }
}
