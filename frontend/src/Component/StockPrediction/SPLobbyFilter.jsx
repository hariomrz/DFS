import React from 'react';
import { Modal } from 'react-bootstrap';
import * as AL from "../../helper/AppLabels";
import { Utilities, _Map } from '../../Utilities/Utilities';
// import Moment from "react-moment";
// import {  MomentDateComponent } from "../../Component/CustomComponent";

export default class SPLobbyFilter extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            minCT: "",
            maxCT: "",
            minFee: "",
            maxFee: "",
            minEnt: "",
            maxEnt: "",
            minWin: "",
            maxWin: "",
            isFilerApplied: false
        };

    }

    componentDidMount() {
        if(this.props){
            this.setPropData(this.props)
        }
    }

    setPropData=(value)=>{
        let selFilVal = value.selFilVal
        this.setState({
            minCT: selFilVal.minCT,
            maxCT: selFilVal.maxCT,
            minFee: selFilVal.minFee,
            maxFee: selFilVal.maxFee,
            minEnt: selFilVal.minEnt,
            maxEnt: selFilVal.maxEnt,
            minWin: selFilVal.minWin,
            maxWin: selFilVal.maxWin
       })
    }

    setFilter=(isfor,minVal,maxVal)=>{
        const {minCT, maxCT, minFee, maxFee, minEnt, maxEnt, minWin, maxWin} = this.state;
        if(isfor == 1){ // 1 is for contest time
            this.setState({
                minCT: minCT == minVal ? "" : minVal,
                maxCT: maxCT == maxVal ? "" : maxVal,
                isFilerApplied: true
            })
        }
        else if(isfor == 2){ // 2 is for entry fee
            this.setState({
                minFee: minFee == minVal ? "" : minVal, 
                maxFee: maxFee == maxVal ? "" : maxVal,
                isFilerApplied: true
            })
        }
        else if(isfor == 3){ //3 is for entries
            this.setState({
                minEnt: minEnt == minVal ? "" : minVal,
                maxEnt: maxEnt == maxVal ? "" : maxVal,
                isFilerApplied: true
            })
        }
        else if(isfor == 4){ //3 is for winning
            this.setState({
                minWin: minWin == minVal ? "" : minVal,
                maxWin: maxWin == maxVal ? "" : maxVal,
                isFilerApplied: true
            })
        }
        else if(isfor == 0){ //0 to clear filter
            this.setState({
                minCT: "",
                maxCT: "",
                minFee: "",
                maxFee: "",
                minEnt: "",
                maxEnt: "",
                minWin: "",
                maxWin: "",
                isFilerApplied: false
            },()=>{
                this.props.setFilter(this.state.minCT, this.state.maxCT, this.state.minFee, this.state.maxFee, this.state.minEnt, this.state.maxEnt, this.state.minWin, this.state.maxWin,this.state.isFilerApplied)
            })
        }


        // this.props.setFilter(isfor,minVal,maxVal)
    }

    callApplyFilter=()=>{
        this.props.setFilter(this.state.minCT, this.state.maxCT, this.state.minFee, this.state.maxFee, this.state.minEnt, this.state.maxEnt, this.state.minWin, this.state.maxWin,this.state.isFilerApplied)
        this.props.ApplyFilter()
    }

    render() {

        const { isHide,isShow,filterList,selFilVal,ApplyFilter} = this.props;
        const { minCT,minFee,minEnt,minWin} = this.state;
        var CTMin = Object.keys(filterList.time);
        let CTList = filterList.time
        var WMin = Object.keys(filterList.winning);
        let WList = filterList.winning
        var EFMin = Object.keys(filterList.entry_fee);
        let EFList = filterList.entry_fee
        var ERMin = Object.keys(filterList.entries);
        let ERList = filterList.entries
        return (
            <Modal
                show={isShow}
                dialogClassName="sp-filter"
                className="center-modal sp-filter-modal" 
            >
                <Modal.Header >
                    <div className='Confirm-header'>
                        <a href className="reload" onClick={()=>this.setFilter(0)}>
                            <i className="icon-reload"></i>
                        </a>
                        <span>{AL.FILTERS} </span>
                        <a href className="mclose" onClick={isHide}>
                            <i className="icon-close"></i>
                        </a>
                    </div>
                </Modal.Header>
                <Modal.Body>
                    <div className="filter-sec">
                        <div className="filter-by">{AL.CONTEST_TIME}</div>
                        <div className="filter-val-sec">
                        {/* <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM - hh:mm A " }} /> */}
                            {
                                _Map(CTMin, (item, idx) => {
                                    let Tdate = Utilities.getFormatedDate({date: new Date(), format:'D MMM'})
                                    let Fitem = Utilities.getFormatedDate({date:Tdate + ' ' + item, format:'hh:mm a'})
                                    let FCitem = Utilities.getFormatedDate({date:Tdate + ' ' + CTList[item], format:'hh:mm a'})
                                    return(
                                        <a href key={item + idx} className={`filter-val ${minCT == item ? ' active' : ''}`} onClick={()=>this.setFilter(1,item,CTList[item])}>
                                            {Fitem} - {FCitem}
                                        </a>
                                    )
                                })
                            }
                        </div>
                    </div>
                    <div className="filter-sec">
                        <div className="filter-by">{AL.Entry_fee}</div>
                        <div className="filter-val-sec">
                            {
                                _Map(EFMin, (item, idx) => {
                                    return(
                                        <a href key={item + idx} className={`filter-val ${minFee == item ? ' active' : ''}`} onClick={()=>this.setFilter(2,item,EFList[item])}>{item} - {EFList[item]}</a>
                                    )
                                })
                            }
                        </div>
                    </div>
                    <div className="filter-sec">
                        <div className="filter-by">{AL.ENTRIES}</div>
                        <div className="filter-val-sec">
                            {
                                _Map(ERMin, (item, idx) => {
                                    return(
                                        <a href key={item + idx} className={`filter-val ${minEnt == item ? ' active' : ''}`} onClick={()=>this.setFilter(3,item,ERList[item])}>{item} - {ERList[item]}</a>
                                    )
                                })
                            }
                        </div>
                    </div>
                    <div className="filter-sec">
                        <div className="filter-by">{AL.WINNING}</div>
                        <div className="filter-val-sec">
                            {
                                _Map(WMin, (item, idx) => {
                                    return(
                                        <a href key={item + idx} className={`filter-val ${minWin == item ? ' active' : ''}`} onClick={()=>this.setFilter(4,item,WList[item])}>{item} - {WList[item]}</a>
                                    )
                                })
                            }
                        </div>
                    </div>
                    <div className="btm-fx-btn" onClick={()=>this.callApplyFilter()}>
                        {AL.APPLY}
                    </div>
                </Modal.Body>
            </Modal>
        );
    }
}