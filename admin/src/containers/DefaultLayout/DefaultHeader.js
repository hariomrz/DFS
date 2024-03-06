import React, { Component } from 'react';
import { Nav } from 'reactstrap';
import PropTypes from 'prop-types';
import { AppHeaderDropdown, AppNavbarBrand, AppSidebarToggler } from '@coreui/react';
import logo from '../../assets/img/brand/logo.png'
import sygnet from '../../assets/img/brand/sygnet.svg'
import MasterSportSelection from '../../views/Common/MasterSportSelection';
import * as NC from '../../helper/NetworkingConstants';
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import HeaderNotification from '../../components/HeaderNotification';
import _ from 'lodash';
import HF, { _isEmpty } from '../../helper/HelperFunction';
import { SOCIAL_ADMIN } from '../../helper/Message';

const propTypes = {
  children: PropTypes.node,
};

const defaultProps = {};

class DefaultHeader extends Component {
  constructor(props){
    super(props)
    this.state={
      classAdd : true,
      socialBtn: false,
    }
  }

  componentDidMount(){
    let modAccess = WSManager.getKeyValueInLocal("module_access")
    if (!_.isNull(modAccess)) {
      if (modAccess.includes('user_wallet_manage')) {
        this.getNotificationCount()
      }
    }
  }

  onMasterSportsChange = (selected_sport) => {
    
  }
  
  addClassToAppBody = () =>{
    let shadesEl = document.querySelector('.app-body');
    if (this.state.classAdd)
    {     
      this.setState({ classAdd: false })
      shadesEl.classList.add('true');
    }
    else{
      this.setState({ classAdd: true })
      shadesEl.classList.remove('true')
    };  
    
  }

  getNotificationCount(){
    WSManager.Rest(NC.baseURL + NC.GET_PENDING_COUNTS, {}).then(Response => {
      if (Response.response_code == NC.successCode) {
        this.setState({
          pending_pan_card_count: Response.data.pending_pan_card_count,
          pending_bank_document_count: Response.data.pending_bank_document_count,
          feedback_pending_count: Response.data.feedback_pending_count,
        })
      }
    }).catch(error => {
      notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    })
  }

  _socialLogin = () => {
    this.setState({ socialBtn : true })
    WSManager.Rest(NC.baseURL + NC.SOCIAL_LOGIN, {}).then(Response => {
      if (Response.response_code == NC.successCode) {
        let d = !_.isEmpty(Response.data) ? Response.data : []
        if(!_.isEmpty(d.Url))
        {
          window.open(d.Url, '_blank').focus();
        }else{
          notify.show(SOCIAL_ADMIN, 'error', 5000)
        }
      }
      this.setState({ socialBtn : false })
    }).catch(error => {
      notify.show(NC.SYSTEM_ERROR, 'error', 5000)
    })
  }

  render() {

    // eslint-disable-next-line
    const { children, ...attributes } = this.props;
    let modAcc = WSManager.getKeyValueInLocal("module_access")
    return (
      <React.Fragment>
        <AppSidebarToggler className="d-lg-none" display="md" mobile />
       
        <AppNavbarBrand
          full={{ src: logo, width: 89, height: 25, alt: 'CoreUI Logo' }}
          minimized={{ src: sygnet, width: 30, height: 30, alt: 'CoreUI Logo' }}
        />
        <AppSidebarToggler className="d-md-down-none" display="lg" />        
             <div className="sports-selector-container">
                 <MasterSportSelection  masterSportsChange = {(e)=>this.onMasterSportsChange(e)} testdata={'helloo'} {...this.props} />
            </div>
        <Nav className="ml-auto top-navigation" navbar>
          <AppHeaderDropdown>
            {
              (!_.isNull(modAcc) && !_.isUndefined(modAcc) && modAcc.includes("dashboard")) &&
              <HeaderNotification />
            }
          </AppHeaderDropdown>
          {
            HF.allowSocial() == "1" &&
            <button
            disabled = {this.state.socialBtn}
            className="btn btn-default"
            style={{ color: '#fff' }}
            onClick={this._socialLogin}>Social Login</button>
          }
        <a className="btn btn-default" style={{ color: '#fff' }} href="#/logout">Log Out</a>
        </Nav>
      </React.Fragment>
    );
  }
}

DefaultHeader.propTypes = propTypes;
DefaultHeader.defaultProps = defaultProps;

export default DefaultHeader;
