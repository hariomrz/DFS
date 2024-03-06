import React from 'react';
import Images from '../../components/images';
import { NoDataView } from '../../Component/CustomComponent';

export default class NoNetwork extends React.Component {

    onRefresh = () => {
        window.location.reload()
    }

    render() {
        return (
            <div className='no-network-container'>

                <div className='child-item'>
                    <NoDataView
                        BG_IMAGE={Images.no_data_bg_image}
                        CENTER_IMAGE={Images.NO_INTERNET}
                        MESSAGE_1={'No internet'}
                        MESSAGE_2={'Check your connection and try again.'}
                        BUTTON_TEXT={'Reload'}
                        onClick={this.onRefresh}
                    />
                </div>
            </div>
        )
    }
}