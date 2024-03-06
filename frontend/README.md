This project was bootstrapped with [Create React App](https://github.com/facebook/create-react-app).

## Available Scripts

In the project directory, you can run:

### `npm start`

Runs the app in the development mode.<br>
Open [http://localhost:3000](http://localhost:3000) to view it in the browser.

The page will reload if you make edits.<br>
You will also see any lint errors in the console.

### `npm test`

Launches the test runner in the interactive watch mode.<br>
See the section about [running tests](https://facebook.github.io/create-react-app/docs/running-tests) for more information.

### `npm run build`

Builds the app for production to the `build` folder.<br>
It correctly bundles React in production mode and optimizes the build for the best performance.

# Creating component CLI

## Installation
``` base
$ npm install --save-dev create-react-component-folder
```
## Creating single component
``` base
$ npx crcf myComponent
$ npx crcf components/myComponent
```
## Creating multiple components
``` base
$ npx crcf components/header footer button navigation
```
## Creating index.js file for multple component imports
``` base
$ npx crcf --createindex .
$ npx crcf --createindex [pathname]
```
### Output in index.js file for multple component imports

``` jsx
import Login from './Login';
import Register from './Register';

export { Login, Register };
```

## Options
``` base
$ npx crcf --help
 
  Usage: index [options]
 
  Options:
 
    -V, --version     output the version number
    --typescript      Creates Typescript component and files
    --nocss           No css file
    --notest          No test file
    --reactnative     Creates React Native components
    --createindex     Creates index.js file for multple component imports
    -f, --functional  Creates React stateless functional component
    -j, --jsx         Creates the component file with .jsx extension
    -l, --less        Adds .less file to component
    -s, --scss        Adds .scss file to component
    -p, --proptypes   Adds prop-types to component
    -u, --uppercase   Component files start on uppercase letter
    -h, --help        output usage information
```

See the section about [create-react-component-folder](https://www.npmjs.com/package/create-react-component-folder) for more information.

## Folder structure
``` base
src
├── index.js
├── App.jsx
├── App.css
├── App.test.js
├── serviceWorker.js
├── assets
    ├── fonts
    ├── scss
├── Component
    ├── CustomComponent
    ├── Modal
    ├── OnBoarding
├── Constants
├── InitialSetup
├── JsonFiles
├── Utilities
├── WSHelper
```


 ## Social login account used 
 * Facebook -9039577130 Vtech@2012
 * Google ravib.vinfotech@gmail.com
 