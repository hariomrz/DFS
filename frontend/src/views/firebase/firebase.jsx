import * as firebase from 'firebase';

const firebaseConfig = {
    apiKey: "AIzaSyAa4Xnh_OamfUR8db1wxX87uQp3uMzTKKA",
    authDomain: "fantasy-sports-282706.firebaseapp.com",
    databaseURL: "https://fantasy-sports-282706.firebaseio.com",
    projectId: "fantasy-sports-282706",
    storageBucket: "fantasy-sports-282706.appspot.com",
    messagingSenderId: "319507097143",
    appId: "1:319507097143:web:4599690b2d5ebb12f901b2",
    // measurementId: "G-H7VKQEMCJH"
  };
if (!firebase.apps.length) {
    firebase.initializeApp(firebaseConfig);
    firebase.analytics();
}


export default firebase;