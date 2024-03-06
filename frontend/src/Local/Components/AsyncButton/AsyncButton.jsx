import { Utilities } from 'Utilities/Utilities';
import WSManager from 'WSHelper/WSManager';
import React, { useState } from 'react';
import { Button } from 'react-bootstrap';
import { useHistory } from 'react-router-dom';

const AsyncButton = (props) => {
    let { btnProps } = props
    const History = useHistory();
    let { aadhar = false } = btnProps
    let login = WSManager.loggedIn()
    let { a_aadhar } = Utilities.getMasterData()
    let { aadhar_status, aadhar_detail } = WSManager.getProfile()

    // console.log(btnProps, 'btnProps');
    // btnProps====
    // className: ``,// btn-rounded
    // bsStyle: 'primary',
    // bsSize: 'small',//large, xsmall, or blank
    // type: 'submit',
    // block: true

    const handler = (event) => {
        event.stopPropagation();
        // console.log(event, aadhar_status, aadhar_detail);
        // if(a_aadhar == "1" && aadhar_status == '1' && aadhar_detail.aadhar_id) {
        //     console.log(History, 'History');
        // } else {
        //     props.onClick(event)
        // }
        props.onClick(event)
    }

    return (
        <Button {...btnProps} disabled={btnProps.disabled} onClick={
            (event) => {
                btnProps.aadhar ? handler(event) : props.onClick(event);
            }
        }>
            {props.children}
        </Button>
    );
};

export default AsyncButton;
