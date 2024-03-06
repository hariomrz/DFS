importScripts('https://www.gstatic.com/firebasejs/3.9.0/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/3.9.0/firebase-messaging.js');

// Initialize the Firebase app in the service worker by passing in the
// messagingSenderId.
firebase.initializeApp({
   'messagingSenderId': '104537282542'
});

// Retrieve an instance of Firebase Messaging so that it can handle background
// messages.
const messaging = firebase.messaging();

messaging.setBackgroundMessageHandler(function(payload) {
  var temp_payload = JSON.parse(payload.data.notification);
  console.log('[firebase-messaging-sw.js] Received background message : ',temp_payload);
  // Customize notification here
  const notificationTitle = temp_payload.title;
  const notificationOptions = {
    body: temp_payload.body,
    icon: '/android-chrome-192x192.png'
  };

  //return self.registration.showNotification('title', {body: 'message'})

  return self.registration.showNotification(notificationTitle,
      notificationOptions);
});









if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('/firebase-messaging-sw.js')
  .then(function(registration) {
    console.log('Registration successful, scope is:', registration.scope);
  }).catch(function(err) {
    console.log('Service worker registration failed, error:', err);
  });
}