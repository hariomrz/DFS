import React, { Component } from 'react';
import { Tabs, Tab } from 'react-tabs-scrollable'
class ScrollSportstab extends Component {
  constructor(props) {
    super(props);
    this.state = {
      activeTab: 0,
    }
  }

  clickHandler = (e, index) => {
    this.setState({
      activeTab: index
    // },()=>this.props.onTabClick(index))
    })
  }

  render() {
    const {
      children,
      tabsContainerClassName,
      tabsClassName,
      rightBtnIcon = <i className="icon-arrow-right" />,
      leftBtnIcon = <i className="icon-arrow-left" />,
      tabsScrollAmount = 1
    } = this.props
    const { activeTab } = this.state
    return (
      <Tabs
        activeTab={activeTab}
        onTabClick={this.clickHandler}
        tabsContainerClassName={tabsContainerClassName}
        tabsClassName={tabsClassName}
        rightBtnIcon={rightBtnIcon}
        leftBtnIcon={leftBtnIcon}
        tabsScrollAmount={tabsScrollAmount}
      >
        {
          children({ Tab })
        }
      </Tabs>
    );
  }
}

export default ScrollSportstab;
