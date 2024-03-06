import React, {lazy, Suspense} from 'react';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
const ComingSoonModal =  lazy(()=>import('./ComingSoonModal'));

export default class DailyCheckInComingSoonModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
        };

    }

    componentDidMount() {
    }

    render() {

        const { isShow, isHide } = this.props;

        return <Suspense fallback={<div />} >
                <ComingSoonModal 
                    {...this.props} 
                    isShow={isShow} 
                    isHide={isHide} 
                    heading={AL.OOPS_CAP + "!"}
                    subHeading={AL.YOU_CAUGHT_US + "!"}
                    text1={AL.YOU_WILL_ERN_SOON_DCB}
                    text2={AL.COME_BACK_IN_FEW_DAYS}
                    headImg={Images.IC_COIN}
                />
            </Suspense>
    
    }
}