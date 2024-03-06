import { createSlice } from '@reduxjs/toolkit';
import Utils from 'Local/Helper/Utils';
import ls from 'local-storage';

const initialState = {
    headerProps: {
        nav: [],
        option: {
            isShow: true
        }
    },
    isAuth: false,
    GameType: Utils.getPickedGameType() || "",
    HeaderMore: {},
    SelectedSport: ls.get("selectedSports"),

    showBanStateModal: false,
    showSpeenWheelModal: false,
    showBanner: false,
    showDailyCheckinBonus: false,
    showRGIModal: false,
    headerBalTimestamp: 0,
    headerProfileTimestamp: 0,
    headerNotifTimestamp: 0,
};

const masterSlice = createSlice({
    name: 'master',
    initialState,
    reducers: {
        setHeaderProps: (state, action) => {
            state.headerProps = action.payload;
        },
        setHeaderMore: (state, action) => {
            state.HeaderMore = action.payload;
        },
        clearData: (state) => {
            state.headerProps = {};
        },
        setAuth: (state, action) => {
            state.isAuth = action.payload;
        },
        gameTypeHandler: (state, action) => {
            state.GameType = action.payload;
        },
        setAppSelectedSport: (state, action) => {
            state.SelectedSport = action.payload;
        },
        modalToggle: (state, action) => {
            state[action.payload.name] = action.payload.action;
        },
        headerBalUpdate: (state, action) => {
            state.headerBalTimestamp = new Date().valueOf();
        },
        headerProfileUpdate: (state, action) => {
            state.headerProfileTimestamp = new Date().valueOf();
        },
        headerNotifUpdate: (state, action) => {
            state.headerNotifTimestamp = new Date().valueOf();
        },
    }
});

export const Actions = masterSlice.actions;
export default masterSlice.reducer;