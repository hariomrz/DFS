export function autoReadSMS(cb) {
  // used AbortController with setTimeout so that WebOTP API (Autoread sms) will get disabled after 1min
  const signal = new AbortController();
  setTimeout(() => {
    signal.abort();
  }, 1 * 60 * 1000);
  async function main() {
    if ('OTPCredential' in window) {
      try {
        if (navigator.credentials) {
          try {
            await navigator.credentials
              .get({ abort: signal, otp: { transport: ['sms'] } })
              .then(content => {
                if (content && content.code) {
                  cb(content.code);
                }
              })
              .catch(e => console.log(e));
          }
          catch (e) {
            return;
          }
        }
      }
      catch (err) {
        console.log(err);
      }
    }
  }
  main();
}
