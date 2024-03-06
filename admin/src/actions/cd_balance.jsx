import {
  UPDATE_EMAIL_BALANCE,
  UPDATE_SMS_BALANCE,
  UPDATE_NOTIFICATION_BALANCE,

} from "../appConstants/ActionTypes";

export function update_email_balance(amount) {
  return {
    type: UPDATE_EMAIL_BALANCE,
    payload: {
      value:amount
    }
  };
}

export function update_sms_balance(amount) {
  return {
    type: UPDATE_SMS_BALANCE,
    payload: {
      value:amount
    }
  };
}

export function update_notification_balance(amount) {
  return {
    type: UPDATE_NOTIFICATION_BALANCE,
    payload: {
      value:amount
    }
  };
}



