import React from 'react';

class AdSenseComponent extends React.Component {

    componentDidMount() {
        (window.adsbygoogle = window.adsbygoogle || []).push({});
    }

    render() {
        return (
            <ins
                className='adsbygoogle'
                style={{ display: 'inline-block', width: '100%', height: 90 }}
                data-adtest='on'
                data-ad-client={process.env.REACT_APP_ADSENSE_CLIENT_KEY}
                data-ad-slot={process.env.REACT_APP_ADSENSE_SLOT_KEY}
            />
        );
    }
}
export default AdSenseComponent;