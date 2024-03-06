import React, { Component } from 'react'

export class DeleteMatchConfirmPopup extends Component {
  constructor(props) {
    super(props)
    this.state = {
        Posting: false,
    }
}
  render() {
    const { liveList,tournDetails } = this.props;
    return (
      <div>
        
      </div>
    )
  }
}

export default DeleteMatchConfirmPopup
