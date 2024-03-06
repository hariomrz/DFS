import React, { Component } from 'react';
import * as NC from "../../../helper/NetworkingConstants";
import WSManager from "../../../helper/WSManager";

class Logout extends Component {

  constructor(props) {
    super(props);
      WSManager.logout();
      //this.props.history.push('/logout'); 
      this.props.history.push('/login')

  }
  


  logout = () => { 
    console.log('logout');
    
      WSManager.logout();
      //this.props.history.push('/logout'); 
      this.props.history.push('/login')
    

  }
  render() {
    return ('')
  }

  
}

export default Logout;
