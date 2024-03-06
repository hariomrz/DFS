import React, {useEffect, useState} from 'react';
import { useHistory } from 'react-router-dom'
import CustomLoader from "../../helper/CustomLoader";
import _ from "lodash";
const DirectpayIpg = require('directpay-ipg-js')


const DirectPayPG = (props) => {
    const location = props.location
    const [isLoading, setIsLoading] = useState(true);

    const [finishStatus, setfinishStatus] = useState(false);
    const history = useHistory()


    const onBackButtonEvent = (e) => {
        e.preventDefault();
        if (!finishStatus) {
            if (window.confirm("Do you want to go back?")) {
                setfinishStatus(true)
                // your logic
                props.history.push("/");
            } else {
                window.history.pushState(null, null, window.location.pathname);
                setfinishStatus(false)
            }
        }
    }


    useEffect(() => {
        if (location.state) {
            console.log(location.state);
            let payload = location.state
            const dp = new DirectpayIpg.Init({
                signature: payload.signature,
                dataString: payload.encoded_payload,
                stage: payload.stage == "TEST" ? 'DEV' : 'PROD',
                container: 'directpay_container'
            })

            dp.doInContainerCheckout().then((data) => {
                console.log('client-res', JSON.stringify(data))
            }).catch((error) => {
                window.location.href = payload.purl
                // props.history.push({pathname: '/payment-method', search: '?status=pending'})
                console.log('client-error', JSON.stringify(error))
            })
            setIsLoading(false)
        } else {
            props.history.goBack()
        }


        // window.history.pushState(null, null, window.location.pathname);
        // window.addEventListener('popstate', onBackButtonEvent);
        // return () => {
        //     window.removeEventListener('popstate', onBackButtonEvent);
        // };
    }, []);

    return (
        <div className="d-flex p-0 pos-r web-container flex-column">
            {
                isLoading && <CustomLoader/>
            }
           <div id="directpay_container" />
        </div>
    );
}

export default DirectPayPG;