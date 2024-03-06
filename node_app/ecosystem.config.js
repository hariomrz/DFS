module.exports = {
  apps : [{
    name   : "app1",
    script : "./app.js",
   watch       : true,
   max_memory_restart:"300M",
   out_file: "/dev/null",
    error_file: "/dev/null",
    env: {
      "NODE_ENV": "development",
    },
    env_testing: {
      "NODE_ENV": "testing",
    },
    env_production : {
       "NODE_ENV": "production"
    }
  }]
}
//pm2 restart ecosystem.config.js --env testing --node-args="--max-old-space-size=600"


