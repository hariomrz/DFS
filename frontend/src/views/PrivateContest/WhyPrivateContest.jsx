import React from "react";
import { MyContext } from "../../InitialSetup/MyProvider";
import Images from "../../components/images";
import * as AppLabels from "../../helper/AppLabels";
import { Utilities,parseURLDate} from '../../Utilities/Utilities';

export default class WhyPrivateContest extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      LobyyData:null,
      isStockF: false
    };
  }

  componentDidMount(){
    let {LobyyData, isSecIn, isStockF} = this.props.location.state;
    this.setState({LobyyData:LobyyData, isSecIn: isSecIn, isStockF: isStockF})
  }

  goToPrivateContest() {
    if (this.state.isStockF) {
      let mSports = this.state.LobyyData.collection_master_id;
      let cat_id = this.state.LobyyData.category_id || ''
      let name = cat_id.toString() === "1" ? 'daily' : cat_id.toString() === "2" ? 'weekly' : 'monthly';
      let contestListingPath = '/stock-fantasy/' + mSports + '/' + name;
      this.props.history.replace({ pathname: contestListingPath + '/private-contest', state: { LobyyData: this.state.LobyyData, isStockF: true } });
    } else {
      let mSports = Utilities.getSelectedSportsForUrl().toLowerCase();
      let data = this.state.LobyyData;
      let dateformaturl = parseURLDate(data.season_scheduled_date);
      let contestListingPath = '/' + data.collection_master_id + '/' + data.home + "-vs-" + data.away + "-" + dateformaturl;
      this.props.history.replace({ pathname: '/' + mSports + contestListingPath + '/private-contest', state: { LobyyData: this.state.LobyyData, isSecIn: this.state.isSecIn } });
    }
  }

  render() {
    return (
      <MyContext.Consumer>
        {(context) => (
          <div className="web-container private-contest-banner-contener ">
              <div className='banner-container'>
                {/* <img className='group-banner' src={Images.PRIVATE_CONTEST_BANNER} alt="" resizeMode='cover'></img> */}
                <div 
                className="banner-text-new"
                // className='banner-text'
                >{AppLabels.BANNER_MSG1} <br/>{AppLabels.BANNER_MSG2}<br/> {AppLabels.BANNER_MSG3}</div>
                <i onClick={()=>this.props.history.goBack()} className='icon-close'></i>
              </div>
              <div className='bottom-container'>
                  <div className='bg-bar-container bg-bar-container-new'></div>
                  <div className='banifits-list'>
                      <div className='banifits-container banifits-container-new first-child-new'>
                          {/* <img src={Images.PRIVATE_CONTEST_FRIENDS} alt="" resizeMode='contain'></img> */}
                        <div className="private-contest-shadow">  <i className='icon-friends'></i></div>
                          <div className='banifits-label'>{AppLabels.PLAY_WITH_FRIENDS}</div>
                      </div>
                      <div className='banifits-container second-child banifits-container-new second-child-new'>
                              <div className='banifits-label'>{AppLabels.ENGAGE_IN_CHAT}</div>
                              {/* <img src={Images.PRIVATE_CONTEST_CHAT} alt="" resizeMode='contain'></img> */}
                              <div className="private-contest-shadow"><i className='icon-chat'></i></div>
                      </div>
                      <div className='banifits-container third-child banifits-container-new third-child-new'>
                          {/* <img src={Images.PRIVATE_CONTEST_COMMISION} alt="" resizeMode='contain'></img> */}
                          <div className="private-contest-shadow"><i className='icon-prize-breakup'></i></div>
                          <div className='banifits-label'>{AppLabels.EARN_COMMISION}</div>
                      </div>
                      <div onClick={()=>this.goToPrivateContest()} className="btn-create">
                          <span>{AppLabels.CREATE_CONTEST}</span>
                      </div>
                      <div className='terms-text'>{AppLabels.BY_CLICK}
                          <span>
                            <a className='primary' target='_blank' href="/terms-condition"> {AppLabels.TC} </a>
                          </span>
                      </div>
                  </div>
              </div>
          </div>
        )}
      </MyContext.Consumer>
    );
  }
}