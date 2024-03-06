import React,{useEffect, useState} from 'react';
import {Tabs, Tab} from 'react-tabs-scrollable';
// import 'react-tabs-scrollable/dist/rts.css'
// import {ShimmerButton} from "react-shimmer-effects";
import { Helper } from 'Local';

const {_} = Helper
const ScrollableTabs = ({
                            selected,
                            children,
                            tabsContainerClassName = '',
                            tabsClassName = '',
                            rightBtnIcon = <i className="icon-arrow-right"/>,
                            leftBtnIcon = <i className="icon-arrow-left"/>,
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
    return !_.isEmpty(list) ? (
        <Tabs
            activeTab={activeTab}
            onTabClick={clickHandler}
            tabsContainerClassName={tabsContainerClassName}
            tabsClassName={tabsClassName}
            rightBtnIcon={rightBtnIcon}
            leftBtnIcon={leftBtnIcon}
            tabsScrollAmount={tabsScrollAmount}
        >
            {
                children({Tab})
            }
        </Tabs>
    ) : (
        <div className={`rts___tabs___placeholder${simmer != '' ? '_' + simmer : ''}`}>
            <div>Loading</div>
            {/* {_.map('123', (item, idx) => <ShimmerButton key={idx} size={"sm"}/>)} */}
        </div>
    )
}
export default ScrollableTabs;
