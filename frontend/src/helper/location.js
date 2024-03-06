import { useEffect } from 'react';
import { useLocation } from "react-router-dom";
import WSManager from "../WSHelper/WSManager";

const Location = () => {
  const location = useLocation();
  const path = location.pathname;
  const store = window.localStorage;
  let url = '';
  let prevUrl = '';

  url = store.getItem('url');
  store.setItem('prevUrl', url);
  store.setItem('url', path);

  url = store.getItem('url');
  prevUrl = store.getItem('prevUrl');
  return { url, prevUrl };
}

const UnreadNotification = (props) => {
  const { prevUrl } = Location()
  useEffect(() => {
      if (WSManager.loggedIn()) {
          setTimeout(() => {
              if (props.rule && prevUrl != '/notification') {
                  props.getAPiNotificationCount();
              }
          }, 2500);
      }
      return () => { }
  }, [])
  return ''
}


export default UnreadNotification