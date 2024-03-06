import React, { Component } from 'react'
import PropTypes from 'prop-types'
import Facebook from 'react-facebook-login';
import SocialButton from './SocialButton'

export default class FacebookLogin extends Component {

    render() {
        const { appId, autoLoad, cookie, callback, onFailure, cssClass, redirectUri, fields, scope, className, icon, textButton } = this.props;
        return (
            <SocialButton
                provider='facebook'
                appId={appId}
                onLoginSuccess={(res) => {
                    callback({...res, 
                        email: res._profile.email || '', 
                        id: res._profile.id,
                        accessToken: res._token.accessToken,
                    })
                }}
                // onLoginSuccess={callback}
                onLoginFailure={onFailure}
                onLogoutSuccess={() => { }}
                key={'facebook'}
                onInternetFailure={() => { return true }}
                className={cssClass}
                >
                <i className="icon-facebook facebook" />
            </SocialButton>

        )
    }
}
