import React, { Component, Suspense } from "react";
import { MyContext } from "../../views/Dashboard";
import { Modal } from "react-bootstrap";
import Images from "../../components/images";
import * as AL from "../../helper/AppLabels";
import * as AppLabels from "../../helper/AppLabels";
import { _Map } from "../../Utilities/Utilities";
import Slider from "react-slick";

class OpenPredictorLearnMore extends Component {
  constructor(props) {
    super(props);
    this.state = {
      sliderData : [
        {
          img: Images.PREDICTOPOOL_IMG_1,
          heading: AppLabels.PREDICTOR_PP_HEADING1,
          status: 1,
          text: AppLabels.PREDICTOR_PP_DETAILS1,
        },
        {
          img: Images.PREDICTOPOOL_IMG_2,
          heading: AppLabels.PREDICTOR_PP_HEADING2,
          status: 2,
          text: AppLabels.JUST_GUESS_MSG,
        },
        {
          img: Images.PREDICTOPOOL_IMG_3,
          heading: AppLabels.PLACE_PRE,
          status: 3,
          text: AppLabels.PREDICTOR_PP_DETAILS3,
        },
        {
          img: Images.PREDICTOPOOL_IMG_4,
          heading: AppLabels.SP_LBY_CM_HEADING6,
          status: 4,
          text: AppLabels.PICKEM_DETAILS_5,
        }
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
            {AppLabels.HOW_TO_PLAY_PREDICTOR_PP}
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
    const { mShow, mHide } = this.props.preData;
    const index = this.state.index;
    const settings = {
      dots: false,
      infinite: false,
      speed: 500,
      slidesToShow: 1,
      slidesToScroll: 1,
      beforeChange: this.beforeChange,
      // swipe: false,
    };

    return (
      <MyContext.Consumer>
        {(context) => (
          <Modal
            show={mShow}
            onHide={mHide}
            bsSize="large"
            // dialogClassName="modal-full-screen"
            // className="modal-pre-lm"
            dialogClassName="stock-htp-modal stock-htp-modal-new"
              className="stock-f"   
           
          >
            <Modal.Body  >
              <a
                href
                onClick={mHide}
                className="modal-close"
                style={{ zIndex: "5" }}
              >
                <i className="icon-close" style={{color:"#212121"}}></i>
              </a>
              <div onClick={(e) => e.stopPropagation()} className={"c-view"} >
                <Suspense fallback={<div />}>
                <Slider ref={(c) => (this.slider = c)} {...settings}>
                      {_Map(sliderData, (item, idx) => {
                        return (
                            this.renderItem(item)
                        );
                      })}
                    </Slider>
                  <div className="slider-bottom"  >
                    <div className="slider-number">
                      <span className="pickem_number">
                        {(index || 0) + 1}
                      </span>
                      /4
                    </div>
                    <div className="buttons_pickem">
                      <button
                        type="button"
                        className="button_prev_pickem"
                        disabled={(index || 0) === 0}
                        onClick={this.previous}
                      >
                        <i className="icon-left-arrow"></i>
                      </button>

                      <button
                        type="button"
                    
                        className="button_next_pickem"
                        disabled={index === 3}
                        onClick={this.next}
                      >
                        <i className="icon-left-arrow"></i>
                      </button>
                    </div>
                  </div>
                </Suspense>
              </div>
            </Modal.Body>
          </Modal>
        )}
      </MyContext.Consumer>
    );
  }
}

export default OpenPredictorLearnMore;
