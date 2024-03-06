/* eslint-disable eqeqeq */
const packageJson = require('../package.json');
const { __dirpath, __filepath, __repopath, __metafilepath } = require('./exec.json');
const {
    exec,
    print,
    isGitSync,
    branchName,

    child_process,
    process,
    path,
    fs,
    copyFilePromise,
    chmodSyncPromise,
    dotenv,
    DomParser
} = require('./utilities.js')

const  
    dir = __dirpath,
    _current = '.',
    appVersion = packageJson.version,
    current_directory = process.cwd()

print.info('Please wait...')

fs.access(_current, fs.constants.R_OK | fs.constants.W_OK, (err) => {
    if (err) {
        console.log("%s doesn't exist", _current);
        console.log("\nGranting read and write access to user"); 
        chmodSyncPromise(_current, 0o777);
    } else {
        console.log('can read/write %s', _current);
    }
});

function copyFiles(srcDir, destDir, files, method = 'get') {
    return Promise.all(files.map(f => {
        if (method === 'get') return copyFilePromise(path.join(srcDir, f.src), path.join(destDir, f.dest))
        else return copyFilePromise(path.join(srcDir, f.dest), path.join(destDir, f.src))
    }));
}

function getEnvfile() {
    return new Promise((resolve, reject) => {
        process.chdir(current_directory);
        console.log(process.cwd() + '\n');
        branchName().then(name => {
            let files = [
                {
                    src: name === 'devops_node_script' ? `env.${name}.${appVersion}.example` : `env.${name}.${appVersion}.example`,
                    dest: `.env`
                }
            ]
            fs.access(path.join(__filepath, files[0].src), fs.F_OK, (err) => {
                if (err) {
                  console.error(err)
                  return
                }
                //file exists
                copyFiles(__filepath, '.', files).then(() => {
                    print.success(`"env" file has been generated for "${name}" successfully!\n`);
                    resolve(true)
                }).catch(err => {
                    reject(false)
                    print.error(err);
                });
              })
        })
    })
}

function gitClone() {
    exec('git clone -b wl_configuration --single-branch ' + __repopath + ' ' + dir)
        .then((text) => {
            getEnvfile().then(res => {
                getMetaFile()
            }).catch(err => {
                print.error(err.code)
            })
        })
}

function getMetaFile() {
    return new Promise((resolve, reject) => {
        process.chdir(current_directory);
        console.log(process.cwd() + '\n');
        branchName().then(name => {
            let files = [
                {
                    src: name === 'devops_node_script' ? `meta.${name}.jsx` : `meta.${name}.jsx`,
                    dest: `meta.jsx`
                }
            ]

            fs.access(path.join(__metafilepath, files[0].src), fs.F_OK, (err) => {
                if (err) {
                    var writeStream = fs.createWriteStream("meta.jsx");
                    writeStream.write(`const Meta = {}\nexport default Meta;`);
                    writeStream.end();
                    print.success(`"meta" file has been generated for "${name}" successfully!\n`);
                  return
                }
                //file exists
                copyFiles(__metafilepath, './src', files).then(() => {
                    print.success(`"meta" file has been generated for "${name}" successfully!\n`);
                    resolve(true)
                }).catch(err => {
                    reject(false)
                    print.error(err);
                });
              })
        })
    })
}

function gtmUpdate() {
    const  
    current_directory = process.cwd(),
    app_directory = path.resolve( "../" ),
    env_file = path.join(current_directory+'/.env'),
    app_index = path.join(app_directory+'/app/index.html')
    
    try {
        dotenv.config({ path: env_file })
        if(process.env.REACT_APP_GTM_ID != '') {
            const head_script = `<script id="HeadScript">
                (function (w, d, s, l, i) {
                    w[l] = w[l] || []; w[l].push({
                        'gtm.start':
                            new Date().getTime(), event: 'gtm.js'
                    }); var f = d.getElementsByTagName(s)[0],
                        j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : ''; j.async = true; j.src =
                            'https://www.googletagmanager.com/gtm.js?id=' + i + dl; f.parentNode.insertBefore(j, f);
                })(window, document, 'script', 'dataLayer', '${process.env.REACT_APP_GTM_ID}');
            </script>`
            
            const body_script = `<noscript id="BodyScript"><iframe src="https://www.googletagmanager.com/ns.html?id=${process.env.REACT_APP_GTM_ID}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>`
            fs.readFile(app_index, 'utf8', function (err, data) {
                if (err) {
                    return console.log(err);
                }
                if(data.search(/<meta head_script \/>/g) == -1) {
                    var parser = new DomParser();
                    var dom = parser.parseFromString(data);
                    let _hs = dom.getElementById("HeadScript").innerHTML
                    let _bs = dom.getElementById("BodyScript").innerHTML
                    let _imgOnClick = dom.getElementById("apkLink").getAttribute('onclick')
                    let result = data.replace(_hs, '').replace('<script id="HeadScript"></script>', head_script);
                        result = result.replace(_bs, '').replace('<noscript id="BodyScript"></noscript>', body_script);
                        result = result.replace(_imgOnClick, `myFunction('${process.env.REACT_APK_PATH}')`);
                    fs.writeFile(app_index, result, 'utf8', function (err) {
                        if (err) return console.log(err);
                    });
                } else {
                    let result = data.replace(/<meta head_script \/>/g, head_script);
                        result = result.replace(/<body_script><\/body_script>/g, body_script);
                        result = result.replace(/REACT_APK_PATH/g, process.env.REACT_APK_PATH);
                    fs.writeFile(app_index, result, 'utf8', function (err) {
                        if (err) return console.log(err);
                    });
                }
            });
        }
    } catch (error) {
        print.error("error occured while update gtm id: " + error);
    }
}

try {
    if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir);
        console.log('\nDirectory Created!')
        gitClone()
    } else if (!isGitSync(dir)) {
        gitClone()
    } else {
        child_process("git pull", (err, stdout, stderr) => {
            if (err) {
                print.error(stdout)
            }
            print.info(stdout)
            getEnvfile().then(res => {
                // console.log(res)
                getMetaFile()
                gtmUpdate()
            }).catch(err => {
                print.error(err.code)
            })
        })
    }
} catch (error) {
    print.error("error occured while changing directory: " + error);
}