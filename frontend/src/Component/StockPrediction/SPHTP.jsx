import React, { lazy, Suspense } from "react";
import { Modal } from "react-bootstrap";
import * as AL from "../../helper/AppLabels";
import * as AppLabels from "../../helper/AppLabels";
import { _Map } from "../../Utilities/Utilities";
import { MyContext } from "../../InitialSetup/MyProvider";
import Images from "../../components/images";
// const ReactSlickSlider = lazy(() =>
//   import("../CustomComponent/ReactSlickSlider")
// );
 import Slider from "react-slick";
const SPRules = lazy(() => import("./SPFantasyRules"));

export default class SPFHTP extends React.Component {
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
          img: Images.STOCK_PREDICT_IMG,
          heading: AppLabels.STOCK_PREDICT,
          status: 1,
          text: AppLabels.SP_LBY_CM_DETAIL1,
        },
        {
          img: Images.STOCK_PREDICT_IMAGES1,
          heading: AppLabels.HTP_DFS_LB1,
          status: 2,
          text: AppLabels.SP_LBY_CM_DETAIL2,
        },
        {
          img: Images.STOCK_PREDICT_IMAGES2,
          heading: AppLabels.SP_LBY_CM_HEADING3,
          status: 3,
          text: AppLabels.SP_LBY_CM_DETAIL3,
        },
        {
          img: Images.STOCK_PREDICT_IMAGES3,
          heading: AppLabels.SP_LBY_CM_HEADING4,
          status: 4,
          text: AppLabels.SP_LBY_CM_DETAIL4,
        },
        {
          img: Images.STOCK_PREDICT_IMAGES4,
          heading: AppLabels.SP_LBY_CM_HEADING5,
          status: 5,
          text: AppLabels.SP_LBY_CM_DETAIL5,
        },
        {
          img: Images.STOCK_PREDICT_IMAGES5,
          heading: AppLabels.MY_CONTEST,
          status: 6,
          text: AppLabels.SP_LBY_CM_DETAIL6,
        },
        {
          img: Images.STOCK_PREDICT_IMAGES6,
          heading: AppLabels.SP_LBY_CM_HEADING6,
          status: 7,
          text: AppLabels.SP_LBY_CM_DETAIL7,
        },
      ]
    };
    // this.next = this.next.bind(this);
    // this.previous = this.previous.bind(this);
  }
  //   next() {
  //     this.slider.slickNext();
  //   }
  //   previous() {
  //     this.slider.slickPrev();
  //   }

  //   state = { index: 0 };

    //   beforeChange = (prev, next) => {
    //     this.setState({ index: next });
    //   };

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
            {AppLabels.HOW_TO_PLAY} {AppLabels.STOCK_PREDICT}?
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
                            <div className="slider-bottom" >
                              <div className="slider-number">
                                <span className="stock_predict_number">
                                  {(index || 0) + 1}
                                </span>
                                /7
                              </div>
                              <div className="buttons">
                                <button
                                  type="button"
                                  className="button-prev"
                                  disabled={(index || 0) === 0}
                                  onClick={this.previous}
                                >
                                  <i className="icon-left-arrow"></i>
                                </button>

                                <button
                                  type="button"
                                  className="button-next"
                                  disabled={index === 6}
                                  onClick={this.next}
                                >
                                  <i className="icon-left-arrow"></i>
                                </button>
                              </div>
                            </div>
                            <div className="bottom-text-view">
                              {AppLabels.SEE_STOCK_PREDICT}{" "}
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
              <SPRules
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
