const dotenv = require("dotenv"); // Used to set .env variable to node process env
dotenv.config({ path: "../../.env" }); //Set your .env file path
var expires = new Date();
expires.setUTCFullYear(expires.getFullYear() + 10);

var header_params = {
  Expires: expires,
  CacheControl: "max-age=315360000, no-transform, public",
  // ContentEncoding: "gzip",
  Metadata: {
    "content-encoding": 'gzip'
  }
};

module.exports = function(grunt) {
  // Define the configuration for all the tasks
  grunt.initConfig({
    aws_s3: {
      options: {
        debug: false, // Set 'true' to test script working fine or not
        differential: true, // Only uploads the files that have changed
        accessKeyId: process.env.BUCKET_ACCESS_KEY,
        secretAccessKey: process.env.BUCKET_SECRET_KEY,
        bucket: process.env.BUCKET,
        region: process.env.BUCKET_REGION,
        params: header_params,
        gzip: true,
        // excludeFromGzip: ["*.png", "*.jpg", "*.jpeg"],
      },
      static: {
        files: [
          {
            expand: true,
            cwd: "../../static",
            src: ["**"],
            dest: "/static",
          },
          {
            expand: true,
            cwd: "../src/assets/i18n",
            src: ["**"],
            dest: "/assets/i18n",
          },
          {
            expand: true,
            cwd: "../src/assets/img",
            src: ["**"],
            dest: "/assets/img",
          },
          {
            expand: true,
            cwd: "../src/assets/img/badges",
            src: ["**"],
            dest: "/upload/badges",
          },
          {
            expand: true,
            cwd: "../src/assets/img/avatar",
            src: ["**"],
            dest: "/upload/profile/thumb",
          },
          {
            expand: true,
            cwd: "../src/assets/img/notification",
            src: ["**"],
            dest: "/upload/notification",
          },
	        {
            expand: true,
            cwd: "../src/assets/img/avatar",
            src: ["**"],
            dest: "/upload/profile",
          },
        ],
      },
    },
  });
  grunt.loadNpmTasks("grunt-aws-s3");
  grunt.registerTask("deploy", ["aws_s3:static"]);
  // run--> 'grunt deploy' (deploy is task name) to upload files on destination folder. If task name is default, then run---> grunt
  // Using npm script 'npm run deploy'. This script is added in package.json, internally it will run above command.
};
