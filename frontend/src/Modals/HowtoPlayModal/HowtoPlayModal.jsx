import React from "react";
import { Modal, ModalHeader } from "react-bootstrap";

import { Helper } from "Local";
import Slider from "react-slick";
import Images from "components/images";

import { useState } from "react";

// const { Trans } = Helper;

const HowtoPlayModal = (props) => {
  //const [activeSlide, setActiveSlide] = useState(0);
  const [activeSlide2, setActiveSlide2] = useState(1);

  const settings = {
    arrows: true,
    dots: false,
    infinite: false,
    speed: 500,
    slidesToShow: 1,
    slidesToScroll: 1,
    // beforeChange: (current, next) => setActiveSlide(next),
    afterChange: (current) => setActiveSlide2(current + 1),
  };
  const sliderDataCustomSld = [
    {
      SlideHeading: "How to Play Opinion Trading?",
      SlideIMG: Images.HOW_TO_SLIDE_1,
      SlideBttmHeading: "Choose Question",
      SlideText:
        "Select YES if you think the question will end in yes, otherwise select NO",
    },
    {
      SlideHeading: "Set The Price",
      SlideIMG: Images.HOW_TO_SLIDE_2,
      SlideBttmHeading: "Select a contest",
      SlideText:
        "Higher chance of getting an opponent if your price is closer to trending price",
    },
    {
      SlideHeading: "How to Play Daily Fantasy?",
      SlideIMG: Images.HOW_TO_SLIDE_3,
      SlideBttmHeading: "Opponent Matched",
      SlideText:
        "You win only if your opinion is matched. So set the price closer to trending price",
    },
    {
      SlideHeading: "How to Play Daily Fantasy?",
      SlideIMG: Images.HOW_TO_SLIDE_4,
      SlideBttmHeading: "Cancel Your Orders",
      SlideText:
        "Any unmatched orders can be cancelled to get full refund",
    },
  ];
  const result = sliderDataCustomSld.length;

  return (
    <div>
      <Modal
        {...props}
        size="xl"
        className="op-howtoplay-wrap-modal"
        onHide={props.hide}
        // closeButton
      >
        <ModalHeader closeButton />
        <Modal.Body className="modal-invite">
          <Slider {...settings}>
            {sliderDataCustomSld.map((data, index) => (
              <div key={index}>
                <div className="how-heading-text">{data.SlideHeading}</div>
                <div className="how-play-img">
                  <img className="img-fluid" src={data.SlideIMG} alt="" />
                </div>
                <div className="how-bottom-text">
                  <div className="text-heading-bttm">
                    {data.SlideBttmHeading}
                  </div>
                  <div className="text-desc-bttm">{data.SlideText}</div>
                </div>
              </div>
            ))}
          </Slider>
          <div className="slider-number">
            <span className="slide-number">{activeSlide2}</span>/{result}
          </div>
          {/* <div className="slider-bttm-text-view">
              <Trans>See Daily Fantasy</Trans> <span><Trans>Scoring Rules</Trans></span>
            </div> */}
        </Modal.Body>
      </Modal>
    </div>
  );
};

export default HowtoPlayModal;
