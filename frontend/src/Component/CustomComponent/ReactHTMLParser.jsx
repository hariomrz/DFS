import React from 'react'
import parse from 'html-react-parser';
export default class ReactHTMLParser extends React.Component {
    render() {
        const { content } = this.props;
        return parse(content)
    }
}