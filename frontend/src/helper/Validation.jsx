import isEmail from 'validator/lib/isEmail';
import isEmpty from 'validator/lib/isEmpty';

export default class Validation {
    static validate(type, value) {
        let isValidate = null;
        if (value && value.length > 0) {
            switch (type) {
                case 'required':
                    isValidate = isEmpty(value) ? 'error' : 'success';
                    break;
                case 'email':
                    isValidate = isEmail(value) ? 'success' : 'error';
                    break;
                case 'password':
                    isValidate = value.length >= 8 ? 'success' : 'error';
                    break;
                case 'fName':
                    isValidate = (value.length >= 3 && value.match(/^[a-zA-Z\s]*$/g) ? 'success' : 'error');
                    break;
                case 'lName':
                    isValidate = (value.length >= 3 && value.match(/^[a-zA-Z\s]*$/g) ? 'success' : 'error');
                    break;
                case 'team_name':
                    isValidate = value.length >= 4 ? 'success' : 'error';
                    break;
                case 'userName':
                    isValidate = value.length >= 3 ? 'success' : 'error';
                    break;
                case 'referral':
                    isValidate = value.length >= 3 ? 'success' : 'error';
                    break;
                case 'user_name':
                    isValidate = value.length >= 3 ? 'success' : 'error';
                    // isValidate = (value.length >= 3 && value.match(/^[0-9a-zA-Z_.]*$/g) ? 'success' : 'error');
                    break;
                case 'city':
                    isValidate = (value.length >= 25 && value.match(/^[a-zA-Z_.]*$/g) ? 'success' : 'error');
                    break;
                case 'address':
                    isValidate = (value.length >= 100 && value.match(/^[0-9a-zA-Z_.]*$/g) ? 'success' : 'error');
                    break;
                case 'phone_no':
                    isValidate = value.match(/^(?:(\+)?([0-9]{1,3})?[-.● ]?)?\(?([0-9]{3})\)?[-.● ]?([0-9]{3})[-.● ]?([0-9]{4})$/) ? 'success' : 'error';
                    break;
                case 'otp':
                    isValidate = value.match(/^[0-9]*$/g) ? 'success' : 'error';
                    break;
                case 'pan_card':
                    isValidate = value.match(/^([a-zA-Z]{5})(\d{4})([a-zA-Z]{1})$/) ? 'success' : 'error';
                    break;
                case 'gstNumber':
                    isValidate = value.match(/^(\d{2})([a-zA-Z]{5})(\d{4})([a-zA-Z]{1})(\d{1})([a-zA-Z]{1})(\d{1})$/) ? 'success' : 'error';
                    break;
                case 'bankName':
                    isValidate = (value.length >= 3 && value.length < 50 && value.match(/^[a-zA-Z\s]*$/g) ? 'success' : 'error');
                    break;
                case 'accountNo':
                    isValidate = (value.length >= 9 && value.length < 20 && value.match(/^[0-9]*$/g) ? 'success' : 'error');
                    break;
                case 'ifscCode':
                    isValidate = value.match(/^[A-Za-z]{4}0[A-Z0-9a-z]{6}$/g) ? 'success' : 'error';
                    // isValidate = (value.length >= 9 && value.length < 20 && value.match(/^[a-zA-Z0-9]*$/g) ? 'success' : 'error');
                    break;
                case 'pan_userName':
                    isValidate = (value.length >= 3 && value.match(/^[a-zA-Z ]*$/g)) ? 'success' : 'error';
                    break;
                case 'upi_id':
                    isValidate = (value.length >= 5 && value.match(/^[\w.-]+@[\w.-]+$/) ? 'success' : 'error');
                    break;
                default:
                    break;
            }
        }
        return isValidate;
    }
}