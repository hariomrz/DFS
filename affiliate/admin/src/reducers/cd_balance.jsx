import {
  SET_CD_BALANCE,
  UPDATE_EMAIL_BALANCE,
  UPDATE_SMS_BALANCE,
  UPDATE_NOTIFICATION_BALANCE
} from "../appConstants/ActionTypes";

const initialState = {
  cd_balance:{
    email_balance: 0,
    sms_balance: 0,
    notification_balance: 0
  }
  
};

export default function cd_balance_update(state = initialState, action) {
  switch (action.type) {
    case SET_CD_BALANCE:
      return {
        ...state,
        cd_balance:action.cd_balance
      };
    case UPDATE_EMAIL_BALANCE:
      return  {
        ...state,
        cd_balance:{
          ...state.cd_balance,
          email_balance:parseInt(state.cd_balance.email_balance)+parseInt(action.value) 
        }
      };
      case UPDATE_SMS_BALANCE:
      return {
        ...state,
        cd_balance:{
          ...state.cd_balance,
          sms_balance:parseInt(state.cd_balance.sms_balance)+parseInt(action.value) 
        }
      };
      case UPDATE_NOTIFICATION_BALANCE:
      return {
        ...state,
        cd_balance:{
          ...state.cd_balance,
          notification_balance:parseInt(state.cd_balance.notification_balance)+parseInt(action.value) 
        }
      };
    default:
      return state;
  }
}

export const initCDBalance = () => {
  return dispatch => {

  }
}
