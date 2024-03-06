import React, { Component } from 'react'
import { MomentDateComponent } from '../CustomComponent'
import { Utilities } from '../../Utilities/Utilities'
import { MyContext } from '../../views/Dashboard'
import * as AL from '../../helper/AppLabels'
import Images from '../../components/images'
import CountdownTimer from '../../views/CountDownTimer'
import { OverlayTrigger, Tooltip } from 'react-bootstrap'
import { GameType, SELECTED_GAMET } from '../../helper/Constants'

class StockFixtureCard extends Component {
  constructor(props) {
    super(props)
    this.state = {}
  }

  renderOpenAt = (icon, name, item, btnAction, showHTPModal) => {
    let msg = AL.OPEN_AT.replace('##', name)
    let date = Utilities.getUtcToLocal(item.published_date)
    msg = msg.split('##')
    return (
      <div className="tour-body upcoming">
        <div className="spon-img-sec">
          <img src={icon} alt="" />
        </div>
        <div className="tour-info-text">
          <div className="tour-nm">
            {msg.length > 0 ? msg[0] : ''}
            <time>
              {Utilities.getFormatedDate({
                date: date,
                format: 'DD MMM - hh:mm a',
              })}
            </time>
            {msg.length > 0 ? msg[1] : ''}
          </div>
          <a href onClick={showHTPModal}>
            {AL.HOW_TO_PLAY_FREE}
          </a>
        </div>
      </div>
    )
  }

  renderTimeView = (item, game_starts_in) => {
    return (
      <>
        {
          Utilities.showCountDown({ game_starts_in: game_starts_in }) ? (
            <div className="countdown time-line">
              <CountdownTimer
                timerCallback={() => console.log('timerCallback')}
                deadlineTimeStamp={game_starts_in}
              />
            </div>
          ) : parseInt(item.is_live || '0') === 1 ? (
            <div className="live-status">
              <span></span> {AL.LIVE}
            </div>
          ) : (
            parseInt(item.status || '0') > 1 && (
              <span className="com-status">
                <MomentDateComponent
                  data={{ date: item.end_date, format: 'DD MMM' }}
                />
                <span>{' - ' + AL.COMPLETED}</span>
              </span>
            )
          )
          // :
          // <MomentDateComponent data={{ date: item.scheduled_date, format: "DD MMM - hh:mm a" }} />
        }
      </>
    )
  }

  renderWinningSec = (item, name) => {
    return (
      <div className="tour-footer tour-footer-winning-sec ar-update">
        <div className="league-name">
          {name} {AL.CONTEST_TEXT} ({item.total_contest})
        </div>
        <div className={'rank-sec winning-sec'}>
          {AL.WINNINGS}
          <br />
          {item.prize_type.toString() === '0' ? (
            <span className="fz-bold">
              <i
                style={{
                  display: 'inlineBlock',
                  position: 'relative',
                  top: -1,
                }}
                className="icon-bonus"
              ></i>
              {item.total_prize_pool}
            </span>
          ) : item.prize_type.toString() === '1' ? (
            <span className="fz-bold">
              {Utilities.getMasterData().currency_code}{' '}
              {Utilities.getPrizeInWordFormat(item.total_prize_pool)}
            </span>
          ) : item.prize_type.toString() === '2' ? (
            <span className="fz-bold">
              <img
                alt=""
                style={{ marginBottom: '2px' }}
                src={Images.IC_COIN}
                width="20px"
                height="20px"
              />{' '}
              {item.total_prize_pool}
            </span>
          ) : item.prize_type.toString() === '3' ? (
            <span>{item.name}</span>
          ) : (
            <span>0</span>
          )}
        </div>
      </div>
    )
  }

  renderJoinedSec = (item) => {
    return (
      <div className="tour-footer tour-footer-winning-sec ar-update">
        <div className="league-name">
          {item.team_count} {AL.PORTFOLIOS}
        </div>
        <div className={'rank-sec winning-sec'}>
          {item.contest_count} {AL.CONTESTS_POPUP}
        </div>
      </div>
    )
  }

  render() {
    const { item, isFrom, btnAction, showHTPModal } = this.props.data
    let cDate = new Date()
    let pDate = new Date(Utilities.getUtcToLocal(item.published_date))
    let sDate = new Date(Utilities.getUtcToLocal(item.scheduled_date))
    let isOpenAt = Date.parse(pDate) - Date.parse(cDate) > 0
    let name =
      item.category_id.toString() === '1'
        ? AL.DAILY
        : item.category_id.toString() === '2'
        ? AL.WEEKLY
        : AL.MONTHLY
    let icon =
      item.category_id.toString() === '1'
        ? Images.stock_24
        : item.category_id.toString() === '2'
        ? Images.stock_mon
        : Images.stock_cal
    let icon_g =
      item.category_id.toString() === '1'
        ? Images.daily_g
        : item.category_id.toString() === '2'
        ? Images.weekly_g
        : Images.monthly_g
    let game_starts_in = Date.parse(sDate)
    return (
      <MyContext.Consumer>
        {(context) => (
          <div className="bg-img">
            <div
              onClick={() =>
                isOpenAt && isFrom !== 'LSlider' ? null : btnAction()
              }
              className="card-internals"
            >
              {isOpenAt && isFrom !== 'LSlider' ? (
                this.renderOpenAt(icon, name, item, btnAction, showHTPModal)
              ) : (
                <>
                  <div className="tour-body">
                    <div className="tour-info-text">
                      <div className="tour-nm">
                        {item.collection_name && item.collection_name != ''
                          ? item.collection_name
                          : name}{' '}
                        {SELECTED_GAMET == GameType.StockFantasy
                          ? AL.STOCK_FANTASY
                          : ''}
                      </div>
                      <div className="countdown-div">
                        {isFrom === 'LobbyCM' && (
                          <div class="countdown time-line">
                            <span>
                              <strong>16</strong>
                              <span>:</span>
                              <strong>48</strong>
                              <span>:</span>
                              <strong>05</strong>
                              <span></span>
                            </span>
                          </div>
                        )}
                        {this.renderTimeView(item, game_starts_in)}
                        {isFrom !== 'Lobby' && isFrom !== 'LobbyCM' && (
                          <a href className="tour-btn" onClick={btnAction}>
                            {parseInt(item.is_live || '0') === 1 ||
                            parseInt(item.status || '0') > 1
                              ? AL.VIEW
                              : AL.EDIT}
                          </a>
                        )}
                        <div className="sch-date">
                          <MomentDateComponent
                            data={{
                              date: item.season_scheduled_date,
                              format: 'D MMM hh:mm a ',
                            }}
                          />
                          {item.category_id.toString() === '1' ? (
                            <MomentDateComponent
                              data={{
                                date: item.end_date,
                                format: ' - hh:mm a ',
                              }}
                            />
                          ) : (
                            <MomentDateComponent
                              data={{
                                date: item.end_date,
                                format: ' - D MMM hh:mm a ',
                              }}
                            />
                          )}
                        </div>
                      </div>
                    </div>
                  </div>

                  {isFrom === 'Lobby' || isFrom === 'LobbyCM'
                    ? this.renderWinningSec(item, name)
                    : this.renderJoinedSec(item)}
                  {(isFrom === 'Lobby' || isFrom === 'LobbyCM') && (
                    <div className="entry-btn" onClick={btnAction}>
                      <a href className="btn btn-rounded">
                        {AL.PLAY_NOW + '!'}
                      </a>
                    </div>
                  )}
                </>
              )}
              {item.custom_message && (
                <div className="announcement-custom-msg-wrapper">
                  <OverlayTrigger
                    rootClose
                    trigger={['click']}
                    placement="left"
                    overlay={
                      <Tooltip id="tooltip" className="tooltip-featured">
                        <strong>{item.custom_message} </strong>
                      </Tooltip>
                    }
                  >
                    <i
                      className="icon-megaphone"
                      onClick={(e) => e.stopPropagation()}
                    ></i>
                  </OverlayTrigger>
                </div>
              )}
            </div>
          </div>
        )}
      </MyContext.Consumer>
    )
  }
}

export default StockFixtureCard
