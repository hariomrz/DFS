import React, { useState, useEffect, useCallback } from 'react';
import "./SwitchComponent.scss";

const SwitchComponent = ({breakpoint}) => {
  const [isSwitch, setSwitch] = useState(null);
  const handleResize = useCallback(() => {
    let width = window.innerWidth
    let min = breakpoint - 20
    let max = breakpoint + 20
    if((width > min && width < max)) {
      setSwitch(true)
    }
  }, []);

  useEffect(() => {
    window.addEventListener('resize', handleResize);
    return () => {
      window.removeEventListener('resize', handleResize);
    }
  }, [handleResize])

  return isSwitch ? (
    <div className='switch-component'>
      <div className="center-switch">Phasellus nec sagittis velit.</div>
      <button className='center-switch-btn' onClick={() => window.location.replace("/")}>Reload</button>
    </div>
  ) : null
};

export default SwitchComponent;