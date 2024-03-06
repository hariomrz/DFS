
import React, { Component, useEffect, useState } from 'react';
import Slider from "react-slick";
import { Tabs, Tab } from 'react-tabs-scrollable'
import 'react-tabs-scrollable/dist/rts.css'
import { _Map } from '../../../Utilities/Utilities';


export const SportsNavigation = ({
    selected,
    children,
    tabsContainerClassName = '',
    tabsClassName = '',
    rightBtnIcon = <i className="icon-arrow-right" />,
    leftBtnIcon = <i className="icon-arrow-left" />,
    tabsScrollAmount = 2,
    list,
    simmer = ''
}) => {
    const [activeTab, setActiveTab] = useState(0);
    const clickHandler = (e, index) => {
        setActiveTab(index)
    }

    useEffect(() => {
        if (selected) setActiveTab(selected)
    }, [selected]);
    return (
        <Tabs
            activeTab={activeTab}
            onTabClick={clickHandler}
            tabsContainerClassName={tabsContainerClassName}
            tabsClassName={tabsClassName}
            rightBtnIcon={rightBtnIcon}
            leftBtnIcon={leftBtnIcon}
            tabsScrollAmount={tabsScrollAmount}
            scrollSelectedToEnd={true}
        >
            {
                children({ Tab })
            }
        </Tabs>
    )
}

class ReactSlickSlider extends Component {
    render() {
        const { settings, children } = this.props;
        return <Slider {...settings} >{children}</Slider>
    }
}


export default ReactSlickSlider