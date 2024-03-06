const packageJson = require('./package.json');
const { __dirpath, __filepath, __repopath } = require('./exec.json');
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
    chmodSyncPromise
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
                    src: name === 'devops_node_script' ? `env.default.0.0.0.example` : `env.${name}.${appVersion}.example`,
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
            getEnvfile()
        })
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
            }).catch(err => {
                print.error(err.code)
            })
        })
    }
} catch (error) {
    print.error("error occured while changing directory: " + error);
}