import firebase from 'firebase';

export const initializeFirebase = () => {
    firebase.initializeApp({
        apiKey: "AIzaSyDCMAE0l_JgM-QeyUw8ku5dALw7gT7f9_4",
        authDomain: "pbl-local.firebaseapp.com",
        databaseURL: "https://pbl-local.firebaseio.com",
        storageBucket: "pbl-local.appspot.com",
        messagingSenderId: "104537282542",
    });
  }

  export const askForPermissioToReceiveNotifications = async () => {
      try {
          
          const messaging = firebase.messaging();
        
        messaging
            .requestPermission()
            .then(function () {
               // MsgElem.innerHTML = "Notification permission granted." 
                console.log("Notification permission granted.");

                // get the token in the form of promise
                return messaging.getToken()
            })
            .then(function(token) {
                
                console.log("token is : " + token);
                ///TokenElem.innerHTML = "token is : " + token;
            })
            .catch(function (err) {
                //ErrElem.innerHTML =  ErrElem.innerHTML + "; " + err
                console.log("Unable to get permission to notify.", err);
            });  





    } catch (error) {
        console.log("catch");  
      console.error("called...",error);
    }
  }  
