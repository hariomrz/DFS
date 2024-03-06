import React, { lazy, Suspense } from "react";
import { Modal } from "react-bootstrap";
import * as AL from "../../helper/AppLabels";
import * as AppLabels from "../../helper/AppLabels";
import { _Map, Utilities } from "../../Utilities/Utilities";
import { MyContext } from "../../InitialSetup/MyProvider";
import Images from "../../components/images";
import Slider from "react-slick";

export default class DFSTourHTPModal extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      showRules: false,
      mShow: this.props.mShow,
      sliderData : [
        {
          img: Images.DFSINT1,
          //Images.DFS_TOUR_IMG_1,
          heading: AppLabels.DFST_HTP_TITLE1,
          status: 1,
          text: AppLabels.DFST_HTP_DESC1,
        },
        {
          img: Images.DFSINT2,
          // Images.DFS_TOUR_IMG_2,
          heading: AppLabels.DFST_HTP_TITLE2,
          status: 2,
          text: Utilities.getMasterData().int_version == "1" ? AppLabels.DFST_HTP_DESC2_INT : AppLabels.DFST_HTP_DESC2,
        },
        {
          img: Images.DFSINT3,
          // Images.DFS_TOUR_IMG_3,
          heading: Utilities.getMasterData().int_version == "1" ? AppLabels.DFST_HTP_TITLE3_INT : AppLabels.DFST_HTP_TITLE3,
          status: 3,
          text: Utilities.getMasterData().int_version == "1" ? AppLabels.DFST_HTP_DESC3_INT :  AppLabels.DFST_HTP_DESC3,
        },
        {
          img: Images.DFSINT4,
          // Images.DFS_TOUR_IMG_4,
          heading: AppLabels.DFST_HTP_TITLE4,
          status: 4,
          text: Utilities.getMasterData().int_version == "1" ? AppLabels.DFST_HTP_DESC4_INT : AppLabels.DFST_HTP_DESC4,
        },
        {
          img: Images.DFSINT5,
          // Images.DFS_TOUR_IMG_5,
          heading: AppLabels.DFST_HTP_TITLE5,
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
          {AppLabels.HTP_DFS_TOURNAMENT}
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
            dialogClassName="stock-htp-modal stock-htp-modal-new"
              className="stock-f"
            >
              <Modal.Body>
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
                                /{sliderData.length}
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
                                  disabled={index === 5}
                                  onClick={this.next}
                                >
                                  <i className="icon-left-arrow"></i>
                                </button>
                              </div>
                            </div>
                            <div className="bottom-text-view">
                              {AppLabels.SEE_DFS_TOURNAMENT}{" "}
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
