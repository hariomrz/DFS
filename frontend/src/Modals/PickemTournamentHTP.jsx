import React, { lazy, Suspense } from "react";
import { Modal } from "react-bootstrap";
import * as AL from "../helper/AppLabels";
import * as AppLabels from "../helper/AppLabels";
import { _Map } from "../Utilities/Utilities";
import { MyContext } from "../InitialSetup/MyProvider";
import Images from "../components/images";
import Slider from "react-slick";

export default class PickemTournamentHTP extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      showRules: false,
      mShow: this.props.mShow,
      sliderData : [
        {
          img: Images.PICKEM_HTP_IMG1,
          heading: AppLabels.PICKEM_TOURNAMENT,
          status: 1,
          text: AppLabels.HTP_PICKEM_TEXT_1,
        },
        {
          img: Images.PICKEM_HTP_IMG2,
          heading: AppLabels.DFST_HTP_TITLE2,
          status: 2,
          text: AppLabels.HTP_PICKEM_TEXT_2,
        },
        {
          img: Images.PICKEM_HTP_IMG3,
          heading: AppLabels.MAKE_YOUR_PICKS,
          status: 3,
          text: AppLabels.HTP_PICKEM_TEXT_3,
        },
        {
          img: Images.PICKEM_HTP_IMG4,
          heading: AppLabels.CHECK_YOUR_PROGRESS,
          status: 4,
          text: AppLabels.HTP_PICKEM_TEXT_4,
        },
        {
          img: Images.PICKEM_HTP_IMG5,
          heading: AppLabels.WHERE_YOU_STAND_PICKS,
          status: 5,
          text: AppLabels.DFST_HTP_DESC5,
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
          <div className="How_header_text" >
          {AppLabels.HTP_PICKEM_TOURNAMENT}
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
    const index = this.state.index;
    const { mHide } = this.props;
    const { sliderData } = this.state;
    const settings = {
        dots: false,
        infinite: false,
        speed: 500,
        slidesToShow: 1,
        slidesToScroll: 1,
        beforeChange :this.beforeChange
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
            //   dialogClassName="stock-htp-modal"
            //   className="stock-f"
            dialogClassName="stock-htp-modal stock-htp-modal-new"
              className="stock-f"
            >
              <Modal.Body>
                {/* <div className="header-sec">
                  <i onClick={mHide} className="icon-close"></i>
                </div> */}

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
                                <span className="pickem_number">
                                {(index || 0) + 1}
                                </span>
                                /5
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
                                  disabled={index === 4}
                                  onClick={this.next}
                                >
                                  <i className="icon-left-arrow"></i>
                                </button>
                              </div>
                            </div>
                            <div className="bottom-text-view">
                             {AppLabels.SEE} {AppLabels.PICKEM_TOURNAMENT}{" "}
                              <span onClick={this.props.rulesModal}>
                                {AL.SCORING_RULES}
                              </span>
                            </div>
                  </Suspense>
                </div>
              </Modal.Body>
            </Modal>
          </React.Fragment>
        )}
      </MyContext.Consumer>
    );
  }
}
