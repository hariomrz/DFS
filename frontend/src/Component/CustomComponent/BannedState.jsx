import React from 'react';
import Images from '../../components/images';
import { createBrowserHistory } from 'history';
import { Utilities } from '../../Utilities/Utilities';
const history = createBrowserHistory();
const location = history.location;
const queryString = require('query-string');
const parsed = queryString.parse(location.search);

class BannedState extends React.Component {

    constructor(props, context) {
        super(props, context);
        this.state = {
            dateNow: Date.now(),
            duration: parsed.yqtm ? atob(parsed.yqtm) : 0,
            message: parsed.yqmsg ? atob(parsed.yqmsg) : '',
            isCompleted: false
        };
    }

    onRefresh = () => {
        window.location.assign('/lobby')
    }

    render() {

        let support_id = Utilities.getMasterData().support_id ? Utilities.getMasterData().support_id : 'Send Email'

        return (
            <div className='no-network-container'>
                <div className='child-item'>
                    <div className="no-data-container">
                        <div>
                            <img alt="" src={Images.BANNED_STATE} />
                        </div>
                        <p className='pl-2 pr-2'>
                            It seems that you belong to the a country/state where online Fantasy games are banned.
                        </p>
                        <p className='pl-2 pr-2'>
                            Please reach out to us on
                            <a href={'mailto:' + support_id}>
                               {' '} {support_id} {' '}
                            </a>
                            for any further query.
                        </p>
                        <div onClick={this.onRefresh} className={"btn-primary mt30 no-data-button"}>
                            <span>Try again</span>
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}

export default BannedState;