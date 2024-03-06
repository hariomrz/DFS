import React, { Component,Suspense,lazy } from 'react';
import Images from '../../components/images';
import { Modal } from "react-bootstrap";
import * as AL from "../../helper/AppLabels";
import * as AppLabels from "../../helper/AppLabels";
import { _Map } from "../../Utilities/Utilities";
import { MyContext } from "../../InitialSetup/MyProvider";
import Slider from "react-slick";
const LSFRules = lazy(() => import('./LSFRules'));

export default class LSFHTPP extends Component {
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
              img: Images.LIVE_STOCK_FANASY_IMG_1,
              heading: AppLabels.LIVE_STOCK_FANTASY,
              status: 1,
              text: AppLabels.LIVE_STOCK_F_DTAILS1,
            },
            {
              img: Images.LIVE_STOCK_FANASY_IMG_2,
              heading: AppLabels.HTP_DFS_LB1,
              status: 2,
              text: AppLabels.LIVE_STOCK_F_DTAILS2,
            },
            {
              img: Images.LIVE_STOCK_FANASY_IMG_3,
              heading: AppLabels.TRADE_LIVE,
              status: 3,
              text: AppLabels.LIVE_STOCK_F_DTAILS3,
            },
            {
              img: Images.LIVE_STOCK_FANASY_IMG_4,
              heading: AppLabels.THE_TRANSACATIONS,
              status: 4,
              text: AppLabels.LIVE_STOCK_F_DTAILS4,
            },
            {
              img: Images.LIVE_STOCK_FANASY_IMG_5,
              heading: AppLabels.SP_LBY_CM_HEADING5,
              status: 5,
              text: AppLabels.LIVE_STOCK_F_DTAILS5,
            },
            {
              img: Images.LIVE_STOCK_FANASY_IMG_6,
              heading: AppLabels.MY_CONTEST,
              status: 6,
              text: AppLabels.LIVE_STOCK_F_DTAILS6,
            },
            {
              img: Images.LIVE_STOCK_FANASY_IMG_7,
              heading: AppLabels.SP_LBY_CM_HEADING6,
              status: 7,
              text: AppLabels.LIVE_STOCK_F_DTAILS7,
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
                {AppLabels.HOW_TO_PLAY} {AppLabels.LIVE_STOCK_FANTASY}?
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
        const { mHide, stockSetting } = this.props;
   
     
    
        const settings = {
          dots: false,
          infinite: false,
          speed: 500,
          slidesToShow: 1,
          slidesToScroll: 1,
          beforeChange: this.beforeChange
         
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
               
            {this.state.showRules && (
               <LSFRules
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
