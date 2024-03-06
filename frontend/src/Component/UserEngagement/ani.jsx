import React, { Component, Suspense } from 'react';
import { Col, Row, OverlayTrigger, Tooltip } from 'react-bootstrap';
import { Helmet } from "react-helmet";
import Skeleton,{SkeletonTheme} from 'react-loading-skeleton';
import { MyContext } from '../../views/Dashboard';
import { _Map } from '../../Utilities/Utilities';
import * as AL from "../../helper/AppLabels";


class Ani extends Component {
    constructor(props) {
        super(props)
        this.state = {
            isReady: true,
            isLoading: false,
            isComplete: false
        }
    }

    componentDidMount() {
        // window.addEventListener('scroll', this.onScrollList);
    }

    clickButton=()=>{
        this.props.claimTodaysCoins()
        this.setState({
            isReady: false,
            isLoading: true
        },()=>{
            setTimeout(() => {
                this.setState({
                    isLoading: false,
                    isComplete: true
                },()=>{
                    this.props.handleOnClick()
                })
                setTimeout(() => {
                    setTimeout(() => {
                        this.setState({
                            isComplete: false,
                            isReady: true
                        })
                    }, 7000);
                }, 320);
            }, 1000);
        })
    }
    
    render() {
        const {isReady,isLoading,isComplete} = this.state;
        return (
            <button id="button" className={"ani-btn btn-claim " + (isReady ? "ready" : isLoading ? "loading" : isComplete ? "complete" : "")} onClick={()=> this.props.isClaimed && this.clickButton()}>
                <div className="message submitMessage">
                    {
                        isReady &&
                        <span className="button-text">{AL.CLAIM}</span>
                    } 
                </div>
                
                <div className="message loadingMessage">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 19 17">
                    <circle className="loadingCircle" cx="2.2" cy="10" r="1.6"/>
                    <circle className="loadingCircle" cx="9.5" cy="10" r="1.6"/>
                    <circle className="loadingCircle" cx="16.8" cy="10" r="1.6"/>
                    </svg>
                </div>
                
                <div className="message successMessage">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 13 11">
                    <polyline stroke="currentColor" points="1.4,5.8 5.1,9.5 11.6,2.1 "/>
                    </svg> 
                    {
                        isComplete &&
                        <span className="button-text">{AL.SUCCESS}</span>
                    }
                </div>
            </button>
        )
    }
}

export default Ani;
