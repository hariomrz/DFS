import { createSlice } from '@reduxjs/toolkit';

const initialState = {
};

const dfsSlice = createSlice({
    name: 'dfs',
    initialState,
    reducers: {

    }
});

export const Actions = dfsSlice.actions;
export default dfsSlice.reducer;