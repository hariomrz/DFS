import React, { Component } from 'react'
// import Google from 'react-google-login';
import { GoogleLogin, GoogleOAuthProvider } from '@react-oauth/google';
import jwt_decode from "jwt-decode";

const GoogleLoginCom  = ({ children, clientId, buttonText, scope, autoLoad, icon, fetchBasicProfile, redirectUri, className, onSuccess, onFailure }) => {
    return (
        <>
            <GoogleOAuthProvider clientId={clientId}>
                <GoogleLogin
                    type="icon"
                    scope="email"
                    onSuccess={response => {
                        const userObject = jwt_decode(response.credential);
                        const { email, sub } = userObject;
                        onSuccess({
                            tokenId: response.credential,
                            googleId: sub,
                            profileObj: {
                                email: email || ''
                            }
                        }, true)
                    }}
                    onError={(user) => onFailure(user, false)}
                />
            </GoogleOAuthProvider>
        </>
    )
}


export default GoogleLoginCom