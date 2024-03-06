import React, { lazy, Suspense } from "react";
import { Modal } from "react-bootstrap";
import * as AL from "../../helper/AppLabels";
import { _Map } from "../../Utilities/Utilities";
import { MyContext } from "../../InitialSetup/MyProvider";
import StockFantasyRules from "./StockFantasyRules";
import StockEquityFRules from "../StockFantasyEquity/StockEquityFRules";
import { GameType, SELECTED_GAMET, STKHTPSlider } from "../../helper/Constants";
import Images from "../../components/images";
import * as AppLabels from "../../helper/AppLabels";
// const ReactSlickSlider = lazy(() =>
//   import("../CustomComponent/ReactSlickSlider")
// );
import Slider from "react-slick";
export default class StockFHTP extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      showRules: false,
      mShow: this.props.mShow,
      slideIndexCrt: 0,
      slideIndexNxt: 0,
      updateCount: 0,
      sliderData : [
        {
          img: Images.STOCK_FANTASY_IMG1,
          heading: AppLabels.STOCK_FANTASY,
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

  gotoRules = () => {
    this.setState({ showRules: true, mShow: false });
  };

  renderItem = (item) => {
    return (
      <>
        <div className="how_to_play_view">
          <div className="How_header_text" style={{padding:"0"}}>
          {AppLabels.HOW_TO_PLAY} {AppLabels.STOCK_FANTASY}?
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
        beforeChange:this.beforeChange
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
              dialogClassName={
                "stock-htp-modal" + (STKHTPSlider ? " stock-htp-modal-new" : "")
              }
              className="stock-f"
            >
              {STKHTPSlider ? (
                <Modal.Body onClick={mHide}>
                  {/* <a href className="modal-close" onClick={mHide}>
                    <i className="icon-close"></i>
                  </a> */}
                  <div className="header-sec" style={{paddingTop:"10px"}}>
                    <i onClick={mHide} className="icon-close" style={{cursor:"pointer", top:"20px",zIndex:"2"}}></i>
                  </div>
                  {/* <a href className="modal-close" >
                  <i className="icon-close" onClick={mHide} style={{ color: "#212121" }}></i>
                </a> */}
                  <div
                    onClick={(e) => e.stopPropagation()}
                    className={"c-view"}
                  >
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
              ) : (
                <Modal.Body>
                  <div className="header-sec">
                    <i onClick={mHide} className="icon-close"></i>
                    <h2>{AL.HTP_STOCK}</h2>
                  </div>
                  <div className="step-sec-body">
                    <div className="step-sec">
                      <div className="img-circle">
                        <i className="icon-trophy"></i>
                      </div>
                      <div className="label">{AL.WRF_HEAD1}</div>
                      <div className="value">{AL.STOCK_SEL_TYPE}</div>
                    </div>
                    <div className="step-sec">
                      <div className="img-circle">
                        <i className="icon-tshirt"></i>
                      </div>
                      <div className="label">
                        {AL.WRF_HEAD2.replace(AL.Team, AL.PORTFOLIO)}
                      </div>
                      <div className="value">
                        {STOCK_SEL_STOCK.replace(AL.CAPTAIN, AL.CORE)}
                      </div>
                    </div>
                    <div className="step-sec">
                      <div className="img-circle">
                        <i className="icon-step"></i>
                      </div>
                      <div className="label">{AL.CHECK_LEADERBOARD}</div>
                      <div className="value">{AL.CHECK_LEADERBOARD_DESC}</div>
                    </div>
                  </div>
                  <div className="footer-msg">
                    {AL.STOCK_F_RULES}{" "}
                    <a
                      href
                      onClick={() =>
                        this.setState({ showRules: true, mShow: false })
                      }
                    >
                      {AL.RULES}
                    </a>
                  </div>
                </Modal.Body>
              )
              }
            </Modal>
            {this.state.showRules && (
              <>
                {SELECTED_GAMET == GameType.StockFantasyEquity ? (
                  <StockEquityFRules
                    mShow={this.state.showRules}
                    mHide={() =>
                      this.setState({ showRules: false }, () => {
                        mHide();
                      })
                    }
                    stockSetting={stockSetting}
                  />
                ) : (
                  <StockFantasyRules
                    mShow={this.state.showRules}
                    mHide={() =>
                      this.setState({ showRules: false }, () => {
                        mHide();
                      })
                    }
                    // stockSetting={stockSetting}
                  />
                )}
              </>
            )}
          </React.Fragment>
        )}
      </MyContext.Consumer>
    );
  }
}
