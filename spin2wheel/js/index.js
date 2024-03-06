var count = 0;

function scriptLoaded() {
  var done = false;
  if (
    !done &&
    (!this.readyState ||
      this.readyState == "loaded" ||
      this.readyState == "complete")
  ) {
    done = true;
    count = count + 1;
    if (count === 5) {
      var intialData = window.spin2WheelLoaded();
      var jsonData = intialData.loadJSON;

      //create a new instance of Spin2Win Wheel and pass in the vars object
      var myWheel = new Spin2WinWheel();
      window.Spin2WinWheel = Spin2WinWheel;

      //WITH your own button
      myWheel.init({
        data: jsonData,
        onResult: intialData.myResult,
        onGameEnd: intialData.myGameEnd,
        onError: intialData.myError,
        spinTrigger: intialData.btnRef || null, //null for WITHOUT your own button
      });
    }
  }
}
function initSpin() {
  var link = document.createElement("link");
  link.href = 'https://fonts.googleapis.com/css?family=Fjalla+One';
  link.rel = 'stylesheet';
  link.type = 'text/css';
  document.getElementsByTagName("head")[0].appendChild(link);
  
  var link1 = document.createElement("link");
  link1.href = './spin2wheel/css/style.css';
  link1.rel = 'stylesheet';
  document.getElementsByTagName("head")[0].appendChild(link1);
  var scripts = [
    "./spin2wheel/js/TweenMax.min.js",
    "./spin2wheel/js/Draggable.min.js",
    "./spin2wheel/js/ThrowPropsPlugin.min.js",
    "./spin2wheel/js/TextPlugin.min.js",
    "./spin2wheel/js/Spin2WinWheel.js",
  ];

  for (var index = 0; index < scripts.length; ++index) {
    var script = document.createElement("script");
    script.src = scripts[index];
    script.type = "text/javascript";
    script.onload = script.onreadystatechange = scriptLoaded;
    document.getElementsByTagName("body")[0].appendChild(script);
  }
}
