var xhr = new XMLHttpRequest();
xhr.open("GET", "home/userSettings", true);
xhr.onload = function (e) {
  if (xhr.readyState === 4) {
    if (xhr.status === 200) {
      var userSettingsObj = JSON.parse(xhr.responseText);
      
      for(var objKey in userSettingsObj) {          
          
          window[objKey] = userSettingsObj[objKey];
      }
            
    } else {
      console.error(xhr.statusText);
    }
  }
};
xhr.onerror = function (e) {
  console.error(xhr.statusText);
};
xhr.send(null);