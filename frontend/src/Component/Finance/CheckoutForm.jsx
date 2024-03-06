import React, { Component } from 'react';
// import { CardElement, injectStripe, IbanElement, } from 'react-stripe-elements';
import { Form, FormGroup, Label ,Modal} from 'react-bootstrap';

import _ from 'lodash';
import Images from '../../components/images';
import { MyContext } from '../../InitialSetup/MyProvider';
import * as AppLabels from "../../helper/AppLabels";
import { Utilities } from '../../Utilities/Utilities';
import ls from 'local-storage';

class CheckoutForm extends Component {
  constructor(props) {
    super(props);
    this.state = {
      complete: false,
      mToken: '',
      isSubmited: false,
      cardNum:'',
      subscriptionId:'',
      groupConfig:false,
      profileDetail: ls.get('profile') || '',
    };
  }

    componentDidMount(){
      this.setState({subscriptionId:this.props.subscriptionId})
    }
    componentWillReceiveProps(nextProps){
        if(this.state.subscriptionId!=nextProps.subscriptionId){
          this.setState({subscriptionId:nextProps.subscriptionId})
        }
    }

  handleClick = (ev) => 
  {
      if (this.state.isSubmited) {
        return;
    }

   
      this.setState({isSubmited: true}, () => {
        // setTimeout(() => {
          this.handleSubmit(ev)  
        // }, 200);
        
      });
   
  }
  handleSubmit = (ev) => {
    const {profileDetail} = this.state;
    // We don't want to let default form submission happen here, which would refresh the page.
    ev.preventDefault();
    // Within the context of `Elements`, this call to createToken knows which Element to
    // tokenize, since there's only one in this group.
    let user_name = profileDetail.user_name ? profileDetail.user_name:profileDetail.user_unique_id 
    this.props.stripe.createToken({ type: 'card', name: user_name}).then(({ token }) => {
      if (token === undefined) {
        //alert('Please fill your card details and then submit');
        this.setState({isSubmited: false})
        Utilities.showToast("Please try again with valid card details")

      }
      else {
        this.props.getTokenToValidate(token)
      }
    });
    //document.getElementById("create-course-form").reset();
    





    // However, this line of code will do the same thing:
    //
    // this.props.stripe.createToken({type: 'card', name: 'Jenny Rosen'});

    // You can also use createSource to create Sources. See our Sources
    // documentation for more: https://stripe.com/docs/stripe-js/reference#stripe-create-source
    //
    // this.props.stripe.createSource({type: 'card', name: 'Jenny Rosen'});
  };
  
  render() {
    const {
      amountPayable,
      showStripePG,
      hideStripePG,
    } = this.props
    if (this.state.complete) return <h1>Purchase Complete</h1>;

    return (
        <MyContext.Consumer>
                {(context) => (
                    <>
                     <Modal show={showStripePG} onHide={() => hideStripePG()} bsSize="medium" dialogClassName="stripe-pg-modal" className="center-modal">

              <Modal.Header closeButton>
                <div style={{ color: '#000000', fontSize: '16px', textAlign: "center" }}>{AppLabels.PAYABLE_AMOUNT + " " + Utilities.getMasterData().currency_code + this.props.amount}</div>
              </Modal.Header>
              <div>
                            <Modal.Body>
                                <div className="row">
                                    <div className="col-sm-12 col-sm-offset-3">
                                        <div className="checkout_container">
                                            <div className="checkout_container checkoutbox_container">
                                                <div className="form-payment" style={{ marginTop: '10px',marginBottom:'10px' }}>
                                                    <Form id="create-course-form">

                                                        {/* <FormGroup>

                                                            <Label for="name">Customer Name</Label>
                                                            <input  className="form__input" type="text" name="name" id="name" />
                                                        </FormGroup>
                                                        <FormGroup>
                                                            <Label for="address_line1">Address</Label>
                                                            <input className="form__input" type="text" name="address_line1" id="address_line1" />
                                                        </FormGroup> */}
                                                        <FormGroup>
                                                            <Label>{AppLabels.CARD_DETAILS}</Label>
                                                            <div className="form-control">
                                                                <CardElement hidePostalCode={true} onReady={(element) => this._element = element} />
                                                            </div>
                                                        </FormGroup>
                                                        {/* <FormGroup>
                                                            <Label for="custEmail">Card Number</Label>
                                                            <CardNumberElement className='card_item form-control' style={{ base: { fontSize: '14px' } }} placeholder="" value={this.state.cardNum} />
                                                        </FormGroup>
                                                        <FormGroup>
                                                            <Label for="custEmail">Expiry Date</Label>
                                                            <CardExpiryElement className='card_item form-control' style={{ base: { fontSize: '14px' } }} placeholder="" />
                                                        </FormGroup>
                                                        <FormGroup>
                                                            <Label for="custEmail">CVV Number</Label>
                                                            <CardCVCElement className='card_item form-control' style={{ base: { fontSize: '14px' } }} placeholder="" />
                                                        </FormGroup> */}
                                                        <FormGroup>
                                                      <Label for="custEmail">{AppLabels.PAY_SECURLY}</Label>
                                         
                                                        </FormGroup>

                                                    </Form>


                                                </div>






                                                <IbanElement className='card_item' style={{ base: { fontSize: '18px' } }} />
                                               

                                                <div style={{marginTop:10}} disabled={this.state.isSubmited}
                                                    className={'btn btn-primary btn-block btn-lg ' + ((this.state.isSubmited) ? 'disabled' : '')}
                                                    onClick={(ev) => {
                                                        this.handleClick(ev)
                                                    }

                                                    }>{AppLabels.PAY_NOW}</div>
                                            </div>
                                        </div>



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

export default injectStripe(CheckoutForm);