import { Component } from 'react';
import WSManager from "../../../helper/WSManager";
class Logout extends Component {
  constructor(props) {
    super(props);
      WSManager.logout();
      this.props.history.push('/login')

  }

  logout = () => { 
      WSManager.logout();
      this.props.history.push('/login')
  }

  render() {
    return ('')
  }  
}
export default Logout;
