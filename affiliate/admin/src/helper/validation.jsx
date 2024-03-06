import validator from 'validator';
import _ from "lodash";
// console.log(validator);

export default class Validation {
    static validate(type, value) {
        let isValidate = null;
        switch (type) {
            case 'required':
                isValidate = validator.isEmpty(value) ? 'error' : 'success';
                break;
            case 'email':
                isValidate = validator.isEmail(value) ? 'success' : 'error';
                break;
            case 'password':
                isValidate = value.length >= 8 ? 'success' : 'error';
                break;
            case 'first_name':
            case 'last_name':
                isValidate = validator.isAlpha(value) ? 'success' : 'error';
                break;
            case 'team_name':
                isValidate = value.length >= 4 ? 'success' : 'error';
                break;
                case 'userName':
                isValidate = value.length >= 3 ? 'success' : 'error';
                break;
            case 'referral':
                isValidate = value.length >= 6 ? 'success' : 'error';
                break;

            case 'user_name':
                isValidate = value.match(/^[a-zA-Z]*[a-zA-Z0-9]*[a-zA-Z0-9_.-]*$/) ? 'success' : 'error';
                break;
            case 'phone_no':
                isValidate = value.match(/^(?:(\+)?([0-9]{1,3})?[-.● ]?)?\(?([0-9]{3})\)?[-.● ]?([0-9]{3})[-.● ]?([0-9]{4})$/) ? 'success' : 'error';
                break;
            default:
                break;
        }
        return isValidate;
    }
}