import { withRedux } from "ReduxLib";
import React, { useState, useEffect } from "react";
import "./Gameshub.scss";
import Images from "../../components/images";
import { Utilities, _Map } from "Utilities/Utilities";
import { Row, Col } from "react-bootstrap";
import { Helper } from "Local";
import InfoDetailsModal from "./InfoDetailsModal/InfoDetailsModal";
import ls from 'local-storage';
const { Trans, Utils} = Helper;

const Gameshub = ({history, sh_list, selectGameType, infoSportsDetailShow, ...props}) => {


  // const [sh_list, setSh_list] = useState(Utilities.getMasterData().sports_hub);
  // //const [infoModal, setInfoModal] = useState(false);
  // const [nameConfirmation, setNameConfirmation] = useState("");
  // const [dataForInfoDetail, setDataForInfoDetail] = useState([]);
  // const [infoModal, setInfoModal] = useState(false);
  const TempCard = '2';
  // // const []

  // const infoSportsDetailShow = (e, item, name) => {
  //   e.stopPropagation()
  //   setInfoModal(true);
  //   setDataForInfoDetail(item);
  //   setNameConfirmation(item.game_key);
  //   setInfoModal(true);
  // };

  // const selectGameType = (item) => {
  //   let sport = ls.get('selectedSports');
  //   console.log(item);
  //   console.log(sport);

  //   Utils.setPickedGameType(item.game_key)
  //   if(item.is_desktop == 0) {
  //     window.location.replace("/")
  //   } else {
  //     history.push("/lobby")
  //   }
  // }

  return (
    <>
      <div className="gameshubs-wrap">
        <div className="container">
          {/* <div className="game-bnr-slider">
                        <div className="spt-heading"><h3>Spotlight</h3></div>
                    </div> */}
          {/* <div className="sports-hub"> */}
          <Row>
            {sh_list.map((item, idx) => {
              return (
                <>
                  <Col md={6}>
                    <div className="sports-hub" key={idx}>
                      <div
                        className="sports-large "
                        onClick={() => selectGameType(item)}
                        style={{
                          backgroundImage: `url(${Utilities.getSettingURL(
                            item.image
                          )})`,
                        }}
                      >
                        <i
                          className="icon-info"
                          onClick={(e) =>
                            infoSportsDetailShow(e, item)
                          }
                        ></i>
                      </div>
                    </div>
                  </Col>

                  {idx == 1 && (
                    <Col md={12}>
                      <div {...{
                        className: `sports-row ${TempCard == 3 ? 'two' : 'three'}`
                      }}>
                        <div className="quiz-view-card">
                          <img
                            className="img-fluid"
                            src={Images.Sports_Hub_Quiz_Img}
                            alt=""
                          />
                          <div className="quiz-text-view">
                            <Trans>Play Quiz and win coins</Trans>
                          </div>
                        </div>
                        <div className="quiz-view-card xp-level">
                          <div className="text-for-current-level">
                            <Trans>your current xp level</Trans>
                          </div>
                          <div className="sports-card-slider">
                            <div
                              className="progress-bar progress-bar-new"
                              style={{ width: "49.67%" }}
                            ></div>
                            <span>5</span>
                            <span className="next-lvl">6</span>
                          </div>
                          <div className="earn-button">
                            <div className="button-view">
                              <img
                                src={Images.EARN_XPPOINTS}
                                alt=""
                              />
                              <Trans>Earn XP</Trans>
                            </div>
                          </div>
                        </div>
                        <div className="quiz-view-card">
                          <img
                            className="img-fluid"
                            src={Images.Sports_Hub_Quiz_Img}
                            alt=""
                          />
                          <div className="quiz-text-view">
                            <Trans>Keep Playing & Be Rewarded!</Trans>
                          </div>
                        </div>
                      </div>
                    </Col>
                  )}
                </>
              );
            })}
          </Row>
        </div>
      </div>
      {/* {<InfoDetailsModal show={infoModal} onHide={() => setInfoModal(false)} />} */}
    </>
  );
};

export default withRedux(Gameshub);
