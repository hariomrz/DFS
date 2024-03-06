const {
    print,
    process,
    path,
    dotenv,
    fs
} = require('./utilities.js')

const requiredEnvVariables = [
    'NODE_PATH',
    'SKIP_PREFLIGHT_CHECK',
    'GENERATE_SOURCEMAP',
    'REACT_APP_GTM_ID',
    'REACT_APP_BUCKET_STATIC_DATA_ALLOWED',
    'REACT_APP_PRIMARY_COLOR'
];
const optionalEnvVariables = [
    'REACT_APP_DEEPLINK_SCHEMA',
    'REACT_APP_FB_APP_ID',
    'REACT_APP_GOOGLE_CLIENT_ID'
];
const
    current_directory = process.cwd(),
    env_file = path.join(current_directory + '/.env');

let SuggestionArr = []

/* 
 * Confrimation to continue the process 
 */
function needConfirmation(msz) {
    print.info('Press "Y" to continue, or any other key to exit');
    process.stdin.setRawMode(true);
    process.stdin.resume();
    process.stdin.on('data', (data) => {
        if (data.toString().toLowerCase() === 'y') {
            print.success('Continuing...');
            process.exit(0) 
        } else {
            print.error("\n An error occurred"+ msz);
            process.exit(1);
        }
    });
}
/* 
 * SCSS file generate for theme
 */
const generateThemeScss = function (env) {
    let _theme =
        `$primary-color: ${env.REACT_APP_PRIMARY_COLOR};`
    fs.writeFile("src/_theme.scss", _theme, function (err) {
        if (err) {
            return console.log(error(err));
        }
    });
}


try {
    dotenv.config({ path: env_file })


    let ReArr = []
    let OpArr = []
    for (const envVariable of requiredEnvVariables) {
        if (!process.env[envVariable]) {
            ReArr.push(envVariable)
            switch (envVariable) {
                case 'NODE_PATH':
                    SuggestionArr.push('NODE_PATH=src/')
                    break;
                case 'SKIP_PREFLIGHT_CHECK':
                    SuggestionArr.push('SKIP_PREFLIGHT_CHECK=true')
                    break;
                case 'GENERATE_SOURCEMAP':
                    SuggestionArr.push('GENERATE_SOURCEMAP=false')
                    break;
                case 'REACT_APP_GTM_ID':
                    SuggestionArr.push(`REACT_APP_GTM_ID='' # or please confirm with team member`)
                    break;
                case 'REACT_APP_BUCKET_STATIC_DATA_ALLOWED':
                    SuggestionArr.push(`REACT_APP_BUCKET_STATIC_DATA_ALLOWED='1' # 1: enable, 0: disabled `)
                    break;
                default:
                    break;
            }
        }
    }
    for (const envVariable of optionalEnvVariables) {
        if (!process.env[envVariable]) {
            OpArr.push(envVariable)
        }
    }
    if (ReArr.length > 0) {
        throw new Error(`Missing required environment variable:\n -> ${ReArr.join('\n -> ')}`);
    } else {
        generateThemeScss(process.env)
    }
    if (OpArr.length > 0) {
        let _txt = "\n " + `The optional environment variable are missing: \n -> ${OpArr.join('\n -> ')}` + "\n "
        print.warn(_txt);
        if(process.env.DEVOPS_BUILD != 1) {
            needConfirmation(_txt)
        }
    }
} catch (error) {
    print.error("\n " + error + "\n ");
    if(SuggestionArr.length > 0) {
        print.success("\n " + `You should add following variable in you env file: \n    ${SuggestionArr.join('\n    ')}` + "\n ");
        process.exit(1);
    }
}

