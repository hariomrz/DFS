import React, { useState, useEffect } from 'react';
import {
    CardElement,
    Elements,
    useStripe,
    useElements
} from '@stripe/react-stripe-js';
import CustomLoader from '../../helper/CustomLoader';
import * as AppLabels from "../../helper/AppLabels";
import { Utilities } from '../../Utilities/Utilities';
import {depositStripe } from '../../WSHelper/WSCallings';
import * as WSC from "../../WSHelper/WSConstants";
const { REACT_APP_STRIPE_KEY }= process.env

const CheckoutForm = ({ location, history }) => {
  
    const stripe = useStripe();
    const elements = useElements();
    
    const [isPosting, setPosting] = useState(true)
    const [paymentData] = useState(location.state)
    
    // const handleSubmit = async (event) => {
    //     event.preventDefault();
    //     setPosting(true);
    
    //     try {
    //         if (!stripe || !elements || !paymentData) {
    //             setPosting(false);
    //             return;
    //         }
    //         const { error, paymentMethod } = await stripe.createPaymentMethod({
    //             type: 'card',
    //             card: elements.getElement(CardElement),
    //         });
            
    //         if (error) {
    //             setPosting(false);
    //         } else {
    //             const cardElement = elements.getElement(CardElement);
    //             const { token } = await stripe.createToken(cardElement);
    //             let param = { ...paymentData, source: token.id };
    //             const responseJson = await depositStripe(param);
    
    //             if (responseJson.response_code === WSC.successCode) {
    //                 history.replace({
    //                     pathname: '/payment-method',
    //                     search: `?status=success&amount=${param.amount}`,
    //                     state: { from_stripe_pg: true, stripe_status: true },
    //                 });
    //                 Utilities.showToast(responseJson.message, 3000)

    //             }
    //              else if (responseJson.response_code === 500) {
    //                 history.replacel({
    //                     pathname: '/my-wallet',
    //                     search: `?status=pending&amount=${param.amount}`,
    //                     state: { from_stripe_pg: true, stripe_status: false },
    //                 });
    //                 Utilities.showToast(responseJson.message, 3000)
    //             }
    //             // else{
    //             //     Utilities.showToast(responseJson.message, 3000)
    //             // }
    //         } 
    //     } catch (error) {
    //         console.error('Error occurred during payment processing:', error);
    //         // Handle the error as needed
    //     } finally {
    //         setPosting(false);
    //     }
    // };

    const handleSubmit = async (event) => {
        event.preventDefault();
        setPosting(true)

        if (!stripe || !elements || !paymentData) {
            setPosting(false)
            return;
        }

        const { error, paymentMethod } = await stripe.createPaymentMethod({
            type: 'card',
            card: elements.getElement(CardElement),
        });

        
        if(error) {
            setPosting(false)
        } else {
            const cardElement = elements.getElement(CardElement);
            const {token} = await stripe.createToken(cardElement);
            let param = {...paymentData, source: token.id}

            depositStripe(param).then((responseJson) => {
                if (responseJson.response_code == WSC.successCode) {
                    history.replace({
                        pathname: '/payment-method', 
                        search: `?status=success&amount=${param.amount}`,
                        state: { from_stripe_pg: true, stripe_status: true}
                    })

                }
                else if (responseJson.response_code == 500) {
                    history.replace({
                        pathname: '/my-wallet', 
                        search: `?status=failure&amount=${param.amount}`,
                        state: { from_stripe_pg: true, stripe_status: false}}
                        )
                        Utilities.showToast(responseJson.error.amount, 3000)

                }
            })
        }

    };
    

    return (
        <>
            <form onSubmit={handleSubmit}>
                <div className='paymeny-title'>{AppLabels.PAYABLE_AMOUNT + " " + Utilities.getMasterData().currency_code + paymentData.amount}</div>
                <label>
                    Card details
                </label>
                <CardElement
                    options={{
                        style: {
                        base: {
                            fontSize: '16px',
                            color: '#424770',
                            '::placeholder': {
                            color: '#aab7c4',
                            },
                        },
                        invalid: {
                            color: '#9e2146',
                        },
                        },
                    }}
                    hidePostalCode={true}
                    onReady={() => {
                        setPosting(false)
                    }}
                />

                <button type="submit" disabled={!stripe || !elements || isPosting} className="btn-block btn-primary">
                    Pay Now
                </button>
            </form>
            {
                isPosting &&
                <CustomLoader />
            }
        </>
    );
};

const StripePG = (props) => {
    const [stripePromise, setStripePromise] = useState(null)
    
    useEffect(() => {
        if(REACT_APP_STRIPE_KEY && REACT_APP_STRIPE_KEY !='') {
            import('@stripe/stripe-js/pure').then(({loadStripe}) => {
                loadStripe.setLoadParameters({advancedFraudSignals: false});
                setStripePromise(loadStripe(REACT_APP_STRIPE_KEY || ''))
            })
        }
      return () => {
        setStripePromise(null)
      }
    }, [REACT_APP_STRIPE_KEY])

    return (
        <div className="web-container web-container-fixed trans-web-container p-0 pos-r">
            <div className="app-header-style hide-shadow coin-headr">
                <div className="row-container">
                    <div className="section-min section-left">
                        <a className="header-action" onClick={() => props.history.goBack()}>
                            <i class="icon-left-arrow" />
                        </a>
                    </div>
                    <div className="section-middle">
                        <div className="app-header-text">Stripe Payment</div>
                    </div>
                    <div className="section-min section-right"></div>
                </div>
            </div>
            <div className="stripe-wrapper">
                <div className="stripe-ele">
                    {
                        stripePromise &&
                        <Elements stripe={stripePromise}>
                            <CheckoutForm {...props} />
                        </Elements>
                    }
                </div>
            </div>
        </div>
    )
}

export default StripePG;