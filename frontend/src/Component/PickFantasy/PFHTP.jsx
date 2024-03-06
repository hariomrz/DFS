import React, { lazy, Suspense } from "react";
import { Modal } from "react-bootstrap";
import * as AppLabels from "../../helper/AppLabels";
import { _Map } from "../../Utilities/Utilities";
import { MyContext } from "../../InitialSetup/MyProvider";
import Images from "../../components/images";
import Slider from "react-slick";

export default class PFHTP extends React.Component {
  constructor(props, context) {
    super(props, context);
    this.state = {
      showRules: false,
      mShow: this.props.mShow,
      sliderData : [
        {
          img: Images.PICKS_FANTASY_IMG_1 ,
          heading: AppLabels.PICKS_FANTASY,
          status: 1,
          text: AppLabels.PICKS_FANTASY_DETAILS_1,
        },
        {
          img: Images.PICKS_FANTASY_IMG_2,
          heading: AppLabels.SELECT_A_FIXTURE,
          status: 2,
          text: AppLabels.PICKS_FANTASY_DETAILS_2,
        },
        {
          img: Images.PICKS_FANTASY_IMG_3,
          heading: AppLabels.CREATE_PICKSS,
          status: 3,
          text: AppLabels.PICKS_FANTASY_DETAILS_3,
        },
        {
          img: Images.PICKS_FANTASY_IMG_4,
          heading: AppLabels.SELECT_BOOSTERS,
          status: 4,
          text: AppLabels.PICKS_FANTASY_DETAILS_4,
        },
        {
          img: Images.PICKS_FANTASY_IMG_5,
          heading: AppLabels.MY_CONTEST_PICKS,
          status: 5,
          text: AppLabels.PICKS_FANTASY_DETAILS_5,
        },
        {
          img: Images.PICKS_FANTASY_IMG_6,
          heading: AppLabels.WHERE_YOU_STAND_PICKS,
          status: 6,
          text: AppLabels.PICKS_FANTASY_DETAILS_6,
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
          {AppLabels.HOW_TO_PLAY_PICKS_FANTASY}
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
                                <span className="picks_fantasy_number">
                                {(index || 0) + 1}
                                </span>
                                /6
                              </div>
                              <div className="buttons_picks_fantasy">
                                <button
                                  type="button"
                                  className="button_prev_picks_fantasy"
                                  disabled={(index || 0) === 0}
                                  onClick={this.previous}
                                >
                                  <i className="icon-left-arrow"></i>
                                </button>

                                <button
                                  type="button"
                                  className="button_next__picks_fantasy"
                                  disabled={index === 5}
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
          </React.Fragment>
        )}
      </MyContext.Consumer>
    );
  }
}
