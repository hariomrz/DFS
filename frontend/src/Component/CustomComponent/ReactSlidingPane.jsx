import React, { Component } from 'react';
import SlidingPane from 'react-sliding-pane';
import 'react-sliding-pane/dist/react-sliding-pane.css';

export default class ReactSlidingPane extends Component {
    render() {
        const { isOpen, from, width, overlayClassName, onRequestClose, children } = this.props;
        return (
            <SlidingPane
                isOpen={isOpen}
                from={from}
                width={width}
                overlayClassName={overlayClassName}
                onRequestClose={onRequestClose}
            >{children}</SlidingPane>
        )
    }
}
