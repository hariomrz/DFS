import React, { Component,Suspense } from 'react';
import Images from '../../components/images';
import { Modal } from "react-bootstrap";
import * as AL from "../../helper/AppLabels";
import * as AppLabels from "../../helper/AppLabels";
import { _Map } from "../../Utilities/Utilities";
import { MyContext } from "../../InitialSetup/MyProvider";
import Slider from "react-slick";
import StockEquityFRules from './StockEquityFRules';

export default class StockEquity extends Component {
      constructor(props, context) {
        super(props, context);
        this.state = {
          showRules: false,
          mShow: this.props.mShow,
          //   index:0,
          slideIndexCrt: 0,
          slideIndexNxt: 0,
          updateCount: 0,
          sliderData : [
            {
              img: Images.STOCK_FANTASY_IMG1,
              heading: AppLabels.STOCK_EQUITY,
              status: 1,
              text: AppLabels.STOCK_FANTASY_DETAILS1,
            },
            {
              img: Images.STOCK_FANTASY_IMG2,
              heading: AppLabels.HTP_DFS_LB1,
              status: 2,
              text: AppLabels.STOCK_FANTASY_DETAILS2,
            },
            {
              img: Images.STOCK_FANTASY_IMG3,
              heading: AppLabels.SP_LBY_CM_HEADING3,
              status: 3,
              text: AppLabels.STOCK_FANTASY_DETAILS3,
            },
            {
              img: Images.STOCK_FANTASY_IMG4,
              heading: AppLabels.CORE_AND_SATELITE_STOCKES,
              status: 4,
              text: AppLabels.STOCK_FANTASY_DETAILS4,
            },
            {
              img: Images.STOCK_FANTASY_IMG5,
              heading: AppLabels.SP_LBY_CM_HEADING5,
              status: 5,
              text: AppLabels.SP_LBY_CM_DETAIL5,
            },
            {
              img: Images.STOCK_FANTASY_IMG6,
              heading: AppLabels.MY_CONTEST,
              status: 6,
              text: AppLabels.STOCK_FANTASY_DETAILS5,
            },
            {
              img: Images.STOCK_FANTASY_IMG7,
              heading: AppLabels.SP_LBY_CM_HEADING6,
              status: 7,
              text: AppLabels.STOCK_FANTASY_DETAILS6,
            },
            ]
        };
        
      }
     
       state = { index: 0 };
      next = () => {
        this.slider.slickNext();
      };
      previous = () => {
        this.slider.slickPrev();
      };
      beforeChange = (prev, next) => {
        this.setState({ index: next });
      };
    
      
    
      renderItem = (item) => {
        return (
          <>
            <div className="how_to_play_view">
              <div className="How_header_text">
                {AppLabels.HOW_TO_PLAY} {AppLabels.STOCK_EQUITY}?
              </div>
              <div className="How_play_img">
                <img src={item.img} alt="" />
              </div>
    
              <div className="bottom_text">
                <div className="text_heading_bottom">{item.heading}</div>
                <div className="text_detai_bottom">{item.text}</div>
              </div>
            </div>
          </>
        );
      };
    
      render() {
        const { sliderData } = this.state;
         const index = this.state.index;
        // const { index, disabledNext, disabledPrev } = this.state;
        const { mHide, stockSetting } = this.props;
        const { lData } = this.state;
        let STOCK_SEL_STOCK = AL.STOCK_SEL_STOCK.replace(
          AL.Team.toLowerCase(),
          AL.PORTFOLIO.toLowerCase()
        );
    
        const settings = {
          dots: false,
          infinite: false,
          speed: 500,
          slidesToShow: 1,
          slidesToScroll: 1,
          beforeChange: this.beforeChange
          // swipe: false,
        };
    
        return (
          <MyContext.Consumer>
            {(context) => (
              <React.Fragment>
                <Modal
                  show={this.state.mShow}
                  onHide={mHide}
                  bsSize="large"
                  dialogClassName="stock-htp-modal stock-htp-modal-new"
                  className="stock-f"
                >
                  <Modal.Body onClick={mHide}>
                    <a href className="modal-close" onClick={mHide}>
                      <i className="icon-close" style={{ color: "#212121" }}></i>
                    </a>
                    <div onClick={(e) => e.stopPropagation()} className={"c-view"}>
                      <Suspense fallback={<div />}>
                      <Slider ref={(c) => (this.slider = c)} {...settings}>
                          {_Map(sliderData, (item, idx) => {
                            return (
                                this.renderItem(item)
                            );
                          })}
                        </Slider>
                        <div className="slider-bottom">
                                <div className="slider-number">
                                  <span className="stock_fantasy_number">
                                  {(index || 0) + 1}
                                  </span>
                                  /7
                                </div>
                                <div className="buttons_stock_fantasy">
                                  <button
                                    type="button"
                                    className="button_prev_stock_fantasy"
                                    disabled={(index || 0) === 0}
                                    onClick={this.previous}
                                  >
                                    <i className="icon-left-arrow"></i>
                                  </button>

                                  <button
                                    type="button"
                                    className="button_next__stock_fantasy"
                                    disabled={index === 6}
                                    onClick={this.next}
                                  >
                                    <i className="icon-left-arrow"></i>
                                  </button>
                                </div>
                              </div>
                              <div className="bottom-text-view">
                                {AppLabels.SEE_STOCK_FANTASY}{" "}
                                <span
                                  onClick={() =>
                                    this.setState({
                                      showRules: true,
                                      mShow: false,
                                    })
                                  }
                                >
                                  {AL.SCORING_RULES}
                                </span>
                              </div>
                      </Suspense>
                    </div>
                  </Modal.Body>
                </Modal>
                {/* {this.state.showRules && (
              <SPRules
                mShow={this.state.showRules}
                mHide={() =>
                  this.setState({ showRules: false }, () => {
                    mHide();
                  })
                }
                stockSetting={stockSetting}
              />
            )} */}
            {this.state.showRules && (
               <StockEquityFRules
                    mShow={this.state.showRules}
                    mHide={() =>
                      this.setState({ showRules: false }, () => {
                        mHide();
                      })
                    }
                    stockSetting={stockSetting}
                  />
            )}
            
              </React.Fragment>
            )}
          </MyContext.Consumer>
        );
      }
    }
