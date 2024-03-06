import React, { Suspense, lazy } from 'react';
import { Modal } from 'react-bootstrap';
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import {  Tab, Row, Col, Nav, NavItem } from 'react-bootstrap';
import Images from '../../components/images';
import { Utilities, _Map } from '../../Utilities/Utilities';
import { MomentDateComponent } from '../CustomComponent';
import WSManager from "../../WSHelper/WSManager";
const ReactSlickSlider = lazy(()=>import('../CustomComponent/ReactSlickSlider'));

export default class DFSPrizeRulesModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
        };

    }

    componentDidMount() {
    }

    render() {
        var settings = {
            touchThreshold: 10,
            infinite: false,
            slidesToScroll: 1,
            slidesToShow: 1,
            variableWidth: false,
            initialSlide: 0,
            dots: false,
            autoplay:false,
            autoplaySpeed:5000,
            centerMode: true,
            centerPadding: "13px",
            beforeChange: this.BeforeChange,
            responsive: [
                {
                    breakpoint: 500,
                    settings: {
                        className: "center",
                        centerPadding: "13px",
                    }
    
                },
                {
                    breakpoint: 360,
                    settings: {
                        className: "center",
                        centerPadding: "13px",
                    }
    
                }
            ]
        };

        const { isShow,isHide,data,TourRules } = this.props;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={isShow}
                        dialogClassName="custom-modal tour-rules-modal"
                        className="center-modal full-screen-modal"
                    >
                        <Modal.Header className={data.name.length > 26 ? ' max-leng' : ''} >
                            <a href onClick={isHide}>
                                <i className="icon-close"></i>
                            </a>
                            <div className='Confirm-header'>
                                <div className="label">
                                   {data.name}
                                </div>
                                <div className="contest-date">
                                    <MomentDateComponent data={{ date: data.start_date, format: "D MMM" }} /> - <MomentDateComponent data={{ date: data.end_date, format: "D MMM" }} />
                                </div>
                            </div>
                        </Modal.Header>

                        <Modal.Body className={data.name.length > 26 ? ' max-leng' : ''} >
                            <Tab.Container id="tabs-with-dropdown" defaultActiveKey="first">
                                <Row className="clearfix">
                                    <Col sm={12}>
                                        <Nav bsStyle="tabs">
                                            <NavItem eventKey="first">{AL.PRIZES}</NavItem>
                                            <NavItem eventKey="second">{AL.RULES}</NavItem>
                                        </Nav>
                                    </Col>
                                    <Col sm={12} className="rules-scroll-div">
                                        {
                                            data.banner && data.banner.length > 1 &&
                                            <div className="tour-sponser-wrap">
                                                <Suspense fallback={<div />} ><ReactSlickSlider settings = {settings}> 
                                                    {
                                                        _Map(data.banner,(item,idx)=>{
                                                            return(
                                                                <div className="slider-banner-item">
                                                                    <div className="slider-inner-item">
                                                                        <img src={Utilities.getDFSTourSponsor(item)} alt=""/>
                                                                    </div>
                                                                </div>
                                                            )
                                                        })
                                                    }                       
                                                </ReactSlickSlider></Suspense>
                                            </div>
                                        }
                                        {
                                            data.banner && data.banner.length == 1 &&
                                            <div className="tour-sponser-wrap single-tour-sponser-wrap">                               
                                                <div className="slider-banner-item">
                                                    <div className="slider-inner-item">
                                                        <img src={Utilities.getDFSTourSponsor(data.banner[0])} alt=""/>
                                                    </div>
                                                </div>
                                            </div>
                                        }
                                        <Tab.Content animation>
                                            <Tab.Pane eventKey="first">
                                                <div className="tab-wrap">
                                                    <div className="prizes-sec">
                                                        <div className="prize-strip">
                                                            <span>{AL.ALL_PRIZES}</span>
                                                        </div>
                                                        {
                                                            data.prize_detail && data.prize_detail.length > 0 && data.prize_detail[0].amount != 0 ?
                                                            <div className="table-wrap">
                                                                <table>
                                                                    <tbody>
                                                                        {
                                                                            _Map(data.prize_detail,(item,idx)=>{
                                                                                return(
                                                                                    <tr>
                                                                                        <td>{item.min == item.max ? item.min : item.min + " - " + item.max}</td>
                                                                                        <td>
                                                                                            {
                                                                                                item.prize_type == 0 ?
                                                                                                <>
                                                                                                    <i className="icon-bonus"></i> {item.amount}
                                                                                                </>
                                                                                                :
                                                                                                item.prize_type == 1 ?
                                                                                                <>
                                                                                                    <span className="currency-icon-prize">{Utilities.getMasterData().currency_code}</span>
                                                                                                    {item.amount}
                                                                                                </>
                                                                                                :
                                                                                                item.prize_type == 2 ?
                                                                                                <>
                                                                                                    <img className="contest-prizes padding-contest-detail" style={{ height: '12px', marginTop: '-2px', width: '12px' }} alt="" src={Images.IC_COIN} /> {item.amount}
                                                                                                </>
                                                                                                :
                                                                                                item.prize_type == 3 ?
                                                                                                <>
                                                                                                    {
                                                                                                        data.merchandise_list && data.merchandise_list.length > 0 && data.merchandise_list.map((merchandise, index) => {
                                                                                                            return (
                                                                                                                <React.Fragment key={index}>
                                                                                                                    {item.amount == merchandise.merchandise_id &&
                                                                                                                        <>{merchandise.name}</>
                                                                                                                    }
                                                                                                                </React.Fragment>
                                                                                                            );
                                                                                                        })
                                                                                                    }
                                                                                                </>
                                                                                                :
                                                                                                0
                                                                                            }
                                                                                        </td>
                                                                                    </tr>
                                                                                )
                                                                            })
                                                                        }
                                                                        {/* <tr>
                                                                            <td>2</td>
                                                                            <td><img src={Images.IC_COIN} alt=""/> 1000</td>
                                                                        </tr> */}
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            :
                                                            <div className="no-prize-text">{AL.NO_PRIZES_IN_THIS_TOURNAMENT}</div>
                                                        }
                                                    </div>
                                                </div>
                                            </Tab.Pane>
                                            <Tab.Pane eventKey="second">
                                                <div className="tab-wrap">
                                                    <div className="rules-que-wrap">
                                                        <div dangerouslySetInnerHTML={{ __html: TourRules[WSManager.getAppLang() + '_note']}}></div>
                                                        <div dangerouslySetInnerHTML={{ __html: TourRules[WSManager.getAppLang() + '_para1']}}></div>
                                                        <div dangerouslySetInnerHTML={{ __html: TourRules[WSManager.getAppLang() + '_para2']}}></div>
                                                        <div dangerouslySetInnerHTML={{ __html: TourRules[WSManager.getAppLang() + '_para3']}}></div>
                                                        <div dangerouslySetInnerHTML={{ __html: TourRules[WSManager.getAppLang() + '_para4']}}></div>
                                                    </div>
                                                </div>
                                            </Tab.Pane>
                                        </Tab.Content>
                                    </Col>
                                </Row>
                            </Tab.Container>
                        </Modal.Body>

                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}