import React, { Component } from "react";
class WelcomeAdmin extends Component {
    constructor(props) {
        super(props)
        this.state = {}
    }
    render(){
        return(
            <div className="wc-new-admin">
                <h1>Welcome Admin!</h1>                
            </div>
        )
    }
}
export default WelcomeAdmin
