import React, { useEffect, useState } from 'react';
import ls from 'local-storage';

export default function Settings({ match, history }) {
    const [isCacheAPI, setCacheAPI] = useState(ls.get('iscapi') || false)
    useEffect(() => {
        if (match.params.settingId != 'VSPADMIN' || process.env.NODE_ENV !== 'development') {
            ls.set('iscapi', false)
            history.push('/')
        }
        return () => { }
    }, [])

    useEffect(() => {
        if (!isCacheAPI) {
            sessionStorage.clear()
        }
        return () => { }
    }, [isCacheAPI])

    return (
        <div className="web-container with-bg-white">
            <div className="app-header-style">
                <div className="app-header-text">Settings</div>
            </div>
            <div className="download-app-body" style={{ marginTop: 56 }}>
                <div className="player-pick-info" style={{ textAlign: 'left', paddingLeft: 20, paddingRight: 20 }}>
                    <span>API Cache</span>
                    <div className="switch-container">
                        <label>
                            <span className={"playing-text" + (isCacheAPI ? ' all-p' : '')}>{!isCacheAPI ? 'On' : 'Off'}</span>
                            <input
                                checked={isCacheAPI}
                                onChange={() => [setCacheAPI(!isCacheAPI), ls.set('iscapi', !isCacheAPI)]}
                                className="switch" type="checkbox" />
                            <div>
                                <div></div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    )
}