import {
    combineReducers
} from 'redux';

import updateBalance from './cd_balance';
const rootReducer = combineReducers({
    updateBalance
});
export default rootReducer;