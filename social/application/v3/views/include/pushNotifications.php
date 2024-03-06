
<div id="not-status"></div>


<script type="text/javascript">

//Track state of push permission
var pushStatus;
var sw_support=false;

// window.onload = function() {
  console.log('dsdsdsd');
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('sw.js').then(function(registration) {
      // Registration was successful
      console.log('ServiceWorker registration successful with scope: ',    registration.scope);

      //show interface
      document.getElementById('not-status').innerHTML = '<h4>mobiForge notifications</h4><input type="checkbox" name="pushStatus" id="pushStatus" value="false" /><label for="pushStatus">Receive push notifications for new content</label><div id="pushStatusMsg"></div>';

        //Check subscription state
        checkSubscription();

        //Attache listener
        document.getElementById("pushStatus").addEventListener('click', function(){
          subUnsubPush();
        });
    }).catch(function(err) {
      // registration failed :(
      console.log('ServiceWorker registration failed: ', err);
    });
  }
  else {
    console.log("ServiceWorker not supported :-(");
    document.getElementById('not-status').innerHTML = 'ServiceWorker not supported :-(';
  }
// };



function subUnsubPush(e) {
  console.log('Sub/unsub');
  
  //Get current status
  
console.log(pushStatus);
  if(!pushStatus) subscribePush();
  else unsubscribePush();

}

function subscribePush() {
  console.log('subscribing...');
  navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
    console.log('//Need to check notification permission?');
    //Need to check notification permission?
    serviceWorkerRegistration.pushManager.subscribe({userVisibleOnly:true})
      .then(function(pushSubscription) {
        console.log(pushSubscription);
        console.log(pushSubscription.subscriptionId);
        //sendSubscription(pushSubscription);
        sendSub(pushSubscription);
        document.getElementById("pushStatus").checked = true;
        document.getElementById("pushStatusMsg").innerHTML = '<span style="color:#0a0;font-weight:bold">You are subscribed!</span>';
        pushStatus = true;
      })
      .catch(function(e) {
        console.error('Unable to register for push', e);
      });
  });
}

function unsubscribePush() {
  console.log('unsubscribing...');
  navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {  
 
    serviceWorkerRegistration.pushManager.getSubscription().then(  
      function(pushSubscription) {  
        // Check we have a subscription to unsubscribe  
        if (!pushSubscription) {  
          // Nothing to unsubscribe
          document.getElementById("pushStatus").checked = false;
          document.getElementById("pushStatusMsg").innerHTML = '<span style="color:#a00;font-weight:bold">You are not subscribed!</span>';
          pushStatus = false;
          return;  
        }  

        //Remove from application server
        cancelSub(pushSubscription);

        // We have a subscription, so unsubscribe  it  
        pushSubscription.unsubscribe().then(function() {  
          document.getElementById("pushStatus").checked = false;
          document.getElementById("pushStatusMsg").innerHTML = '<span style="color:#a00;font-weight:bold">You are not subscribed!</span>';
          pushStatus = false;
          console.log(pushSubscription);
        }).catch(function(e) {  
          console.log('Error unsubscribing: ', e);  
        });  
      }).catch(function(e) {  
        console.error('Error unsubscribing.', e);  
      });  
  });  
}

function sendSub(pushSubscription) {
console.log(pushSubscription);
//get endpoint
const endPoint = pushSubscription.endpoint.slice(pushSubscription.endpoint.lastIndexOf('/')+1);
console.log(endPoint);
  //fetch("https://mobiforge.com/push/subscribe.php?sid=yyy"+pushSubscription.endpoint+"&act=sub").then(function(res) {
 fetch("http://localhost:3000/notifications/subcribe?sid="+endPoint+"&act=sub").then(function(res) {
    res.json().then(function(data) {
          // Show notification
      console.log(data);
          //self.registration.showNotification(data.title, {
          //body: data.body,
          //icon: data.icon,
          //tag: data.tag
        //})
    }).catch(function(e) {
        console.error('Error sending subscription to server:', e);
      }); 
  })
}

function checkSubscription() {
  navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
    serviceWorkerRegistration.pushManager.getSubscription().then(
      function(pushSubscription) {
        if(!!pushSubscription) {
          console.log(pushSubscription);
          pushStatus = true;
          document.getElementById("pushStatus").checked = true;
          document.getElementById("pushStatusMsg").innerHTML = '<span style="color:#0a0;font-weight:bold">You are subscribed!</span>';
          sendSub(pushSubscription);
        }
        else {
          pushStatus = false;
          document.getElementById("pushStatus").checked = false;
          document.getElementById("pushStatusMsg").innerHTML = '<span style="color:#a00;font-weight:bold">You are not subscribed!</span>';
        }
        
      }.bind(this)).catch(function(e) {
        console.error('Error getting subscription', e);
      });
  });
}


function disableNotifications() {
  navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
    serviceWorkerRegistration.pushManager.getSubscription().then(
      function(pushSubscription) {
        if(pushSubscription) {
          pushSubscription.unsubscribe().then(function(successful) {
            cancelSub(pushSubscription);
          }).catch(function(e) {
            console.log('Disabling push notifications failed: ', e);
          });
        }
      }).catch(function(e) {
        console.error('Error thrown while unsubscribing from push messaging.', e);
      }); 
  }); 
}


function cancelSub(pushSubscription) {
  const endPoint = pushSubscription.endpoint.slice(pushSubscription.endpoint.lastIndexOf('/')+1);
  fetch("http://localhost:3000/notifications/subcribe?sid="+endPoint+"&act=unsub").then(function(res) {
    res.json().then(function(data) {
          // Show notification
      console.log(data);
          //self.registration.showNotification(data.title, {
          //body: data.body,
          //icon: data.icon,
          //tag: data.tag
        //})
    })
  })
}
this.showDesktopNotification = function (message , options) {
    console.log(message);
    if(message) {
      Push.create(
        message, 
        {
          body: options.body || '',
          icon: options.icon || {},
          onClick: options.onClick || function () {},
          onClose: options.onClose || function () {},
          onError: options.onError || function () {},
          onShow: options.onShow || function () {},
          timeout: 3600000, //1 hour
          tag: options.tag || ''
        }
      );
    }
  };

</script>

<style>
  #not-status {background-color: #efefef;padding: 10px;}
  #not-status label {font-size:18px;font-weight:normal;vertical-align: middle;}
  #pushStatusMsg {margin-left:30px;}
  #pushStatus {width: 30px;height: 30px;vertical-align: middle;}
</style>