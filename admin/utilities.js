/* eslint-disable eqeqeq */
const { exec } = require('child_process')
const { promisify } = require('util')
const process = require('process');
const path = require('path');
const fs = require('fs');

var exports = module.exports = {};


// Other Modules
exports.child_process = promisify(require('child_process').exec)
exports.process = process
exports.path = path
exports.fs = fs
exports.copyFilePromise = promisify(fs.copyFile);
exports.chmodSyncPromise = promisify(fs.chmodSync);

// Print `Message`
const Reset = "\x1b[0m"
const FgRed = "\x1b[31m"
const FgGreen = "\x1b[32m"
const FgCyan = "\x1b[36m"

exports.print = {
    info(msz) { console.log(FgCyan, msz, Reset) },
    error(msz) { console.log(FgRed, msz, Reset) },
    success(msz) { console.log(FgGreen, msz, Reset) }
}

// Git `Methods`
exports.exec = function (command) {
    return new Promise((resolve, reject) => {
        const process = exec(command)
        
        process.stdout.on('data', (data) => {
            console.log('stdout: ' + data.toString())
        })
    
        process.stderr.on('data', (data) => {
            console.log('stderr: ' + data.toString())
        })
    
        process.on('exit', (code) => {
            if (code == 0) {
                resolve(true)
            } else {
                reject(code)
                console.log('child process exited with code ' + code.toString())
            }
        })
    })

}

exports.isGitSync = function isGitSync(dir) {
    return fs.existsSync(path.join(dir, '.git'))
}

exports.branchName = function branchName() {
    return new Promise((resolve, reject) => {
        exports.child_process('git rev-parse --abbrev-ref HEAD', (err, stdout, stderr) => {
            if (err) {
                // handle your error
            }
            resolve(stdout.trim());
        });
    })
}

