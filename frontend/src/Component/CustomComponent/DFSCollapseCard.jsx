import React from "react";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Panel } from "react-bootstrap";


const DFSCollapseCard = () => {

    function triggerff() {
        return (
            <p>hi</p>
        )
    }

    return (
        <MyContext.Consumer>
            {(context) => (
                <div className="transparent-header web-container tab-two-height pb0 DFS-tour-lobby">
                    <Panel id="collapsible-panel-example-2">
                        <Panel.Heading>
                            <Panel.Title toggle>
                                1
                            </Panel.Title>
                        </Panel.Heading>
                        <Panel.Collapse>
                            <Panel.Body>
                                111

                            </Panel.Body>
                        </Panel.Collapse>
                    </Panel>
                </div>
            )}
        </MyContext.Consumer>
    )
}

export default DFSCollapseCard;




// https://www.freecodecamp.org/news/build-accordion-menu-in-react-without-external-libraries/