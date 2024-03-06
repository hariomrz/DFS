import React, {useEffect, useState} from 'react';
import * as AppLabels from "../../helper/AppLabels";
import CustomLoader from "../../helper/CustomLoader";
import _ from "lodash";
import { cashfreeSandbox, cashfreeProd } from "cashfree-pg-sdk-javascript";

const CashfreePG = (props) => {
    const location = props.location
    const [isLoading, setIsLoading] = useState(true);
    const components = [
        "order-details",
        "card",
        "upi",
        "app",
        "netbanking"
    ];
    const style = {
        theme: "light", color: "#0CBFEB"
    };

    const cbs = (data) => {
        if (data.order && data.order.status === 'PAID') {
            const {
                order_meta,
                order_id
            } = location.state;

            window.location.href = _.replace(order_meta.return_url, '{order_id}', order_id );
        }
    };

    const cbf = (data) => {
        const {
            order_meta,
            order_id
        } = location.state;
        window.location.href = _.replace(order_meta.return_url, '{order_id}', order_id);
    };

    const renderDropin = (paymentSessionId, prod) => {
        let parent = document.getElementById("drop_in_container");
        parent.innerHTML = "";
        let cashfree = prod == 1 ? new cashfreeProd.Cashfree(paymentSessionId) : new cashfreeSandbox.Cashfree(paymentSessionId);
        cashfree.drop(parent, {
            onSuccess: cbs,
            onFailure: cbf,
            components,
            style,
        });
        setIsLoading(false)
        // cashfree.redirect();
    }

    useEffect(() => {
        if (location.state) {
            if(location.state.type && location.state.type == "invalid_request_error") {
                alert(location.state.message)
            }
            const {
                payment_session_id,
                prod
            } = location.state;
            renderDropin(payment_session_id, prod)
        } else {
            props.history.goBack()
        }
    }, []);

    return (
        <div className="web-container web-container-fixed trans-web-container p-0 pos-r">
            {
                isLoading && <CustomLoader/>
            }
            {
                !isLoading &&
                <a className={'dropin-parent-cancel'} onClick={() => props.history.goBack()}>Cancel</a>
            }
            <div
                className="dropin-parent"
                id="drop_in_container"
            />
        </div>
    );
}

export default CashfreePG;