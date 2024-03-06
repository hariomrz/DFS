import { configureStore } from '@reduxjs/toolkit';
import rootReducer from './reducers';
import thunkMiddleware from 'redux-thunk';

const store = configureStore({
    reducer: rootReducer,
    middleware: (getDefaultMiddleware) =>
        getDefaultMiddleware().concat(thunkMiddleware),
});

export default store;