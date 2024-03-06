/* eslint-disable */
const fs = require('fs');
const filePath = "./package.json";
const packageJson = JSON.parse(fs.readFileSync(filePath).toString());
packageJson.buildDate = Date.now();
fs.writeFileSync(filePath, JSON.stringify(packageJson, null, 2));
const jsonData = {
  buildDate: packageJson.buildDate
};
var jsonContent = JSON.stringify(jsonData);
fs.writeFile('./public/meta.json', jsonContent, 'utf8', function (err) {
  if (err) {
    console.log('An error occured while writing JSON Object to meta.json');
    return console.log(err);
  }

  console.log('meta.json file has been saved with latest version number');
});
/* eslint-enable */
