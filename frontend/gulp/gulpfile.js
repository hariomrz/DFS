const dotenv = require("dotenv"); // Used to set .env variable to node process env
var gulp = require("gulp");
const s3upload = require("gulp-s3-upload")();
dotenv.config({ path: "../../.env" });

var Cachecontrol = "max-age=315360000, no-transform, public";

var expires = new Date();
expires.setUTCFullYear(expires.getFullYear() + 10);

const s3MediaConfig = {
  endpoint: process.env.BUCKET_REGION + ".digitaloceanspaces.com/assets/img/", // note no https://
  region: process.env.BUCKET_REGION,
  accessKeyId: process.env.BUCKET_ACCESS_KEY,
  secretAccessKey: process.env.BUCKET_SECRET_KEY,
};

const s3LangConfig = {
  endpoint: process.env.BUCKET_REGION + ".digitaloceanspaces.com/assets/i18n/", // note no https://
  region: process.env.BUCKET_REGION,
  accessKeyId: process.env.BUCKET_ACCESS_KEY,
  secretAccessKey: process.env.BUCKET_SECRET_KEY,
};

const s3BuildConfig = {
  endpoint: process.env.BUCKET_REGION + ".digitaloceanspaces.com/static/", // note no https://
  region: process.env.BUCKET_REGION,
  accessKeyId: process.env.BUCKET_ACCESS_KEY,
  secretAccessKey: process.env.BUCKET_SECRET_KEY,
};

const s3AvatarMediaConfig = {
  endpoint: process.env.BUCKET_REGION + ".digitaloceanspaces.com/upload/profile/thumb/", // note no https://
  region: process.env.BUCKET_REGION,
  accessKeyId: process.env.BUCKET_ACCESS_KEY,
  secretAccessKey: process.env.BUCKET_SECRET_KEY,
};

const s3NotificationMediaConfig = {
  endpoint: process.env.BUCKET_REGION + ".digitaloceanspaces.com/upload/notification/", // note no https://
  region: process.env.BUCKET_REGION,
  accessKeyId: process.env.BUCKET_ACCESS_KEY,
  secretAccessKey: process.env.BUCKET_SECRET_KEY,
};

gulp.task("upload-images", () =>
  gulp.src("../src/assets/img/**").pipe(
    s3upload(
      {
        Bucket: process.env.BUCKET,
        ACL: "public-read",
        Metadata: {
          uploadedVia: "gulp-s3-upload",
          "content-encoding": 'gzip'
        },
        Expires: expires,
        CacheControl: Cachecontrol,
      },
      s3MediaConfig
    )
  )
);

gulp.task("upload-avatar", () =>
  gulp.src("../src/assets/img/avatar/**").pipe(
    s3upload(
      {
        Bucket: process.env.BUCKET,
        ACL: "public-read",
        Metadata: {
          uploadedVia: "gulp-s3-upload",
          "content-encoding": 'gzip'
        },
        Expires: expires,
        CacheControl: Cachecontrol,
      },
      s3AvatarMediaConfig
    )
  )
);

gulp.task("upload-notification", () =>
  gulp.src("../src/assets/img/notification/**").pipe(
    s3upload(
      {
        Bucket: process.env.BUCKET,
        ACL: "public-read",
        Metadata: {
          uploadedVia: "gulp-s3-upload",
          "content-encoding": 'gzip'
        },
        Expires: expires,
        CacheControl: Cachecontrol,
      },
      s3NotificationMediaConfig
    )
  )
);

gulp.task("upload-translation", () =>
  gulp.src("../src/assets/i18n/**").pipe(
    s3upload(
      {
        Bucket: process.env.BUCKET,
        ACL: "public-read-write",
        Metadata: {
          uploadedVia: "gulp-s3-upload",
          "content-encoding": 'gzip'
        },
        Expires: expires,
        CacheControl: Cachecontrol,
      },
      s3LangConfig
    )
  )
);

gulp.task("upload-build", () =>
  gulp.src("../../static/**").pipe(
    s3upload(
      {
        Bucket: process.env.BUCKET,
        ACL: "public-read-write",
        Metadata: {
          uploadedVia: "gulp-s3-upload",
          "content-encoding": 'gzip'
        },
        Expires: expires,
        CacheControl: Cachecontrol,
      },
      s3BuildConfig
    )
  )
);

gulp.task(
  "default",
  gulp.series("upload-images","upload-avatar", "upload-notification", "upload-translation", "upload-build")
);

//cloudjiffy minio
const minioS3 = require('gulp-minio-s3');
const minioConfig = {
  endPoint: process.env.BUCKET_REGION+'.cloudjiffy.net',
  useSSL: false,
  accessKey: process.env.BUCKET_ACCESS_KEY,
  secretKey: process.env.BUCKET_SECRET_KEY
};
gulp.task("minio-upload-images", () =>
  gulp.src("../src/assets/img/**").pipe(
    minioS3(process.env.BUCKET, minioConfig, 'assets/img')
  )
);
gulp.task("minio-upload-translation", () =>
  gulp.src("../src/assets/i18n/**").pipe(
    minioS3(process.env.BUCKET, minioConfig, 'assets/i18n')
  )
);
gulp.task("minio-upload-build", () =>
  gulp.src("../../static/**").pipe(
    minioS3(process.env.BUCKET, minioConfig, 'static')
  )
);
gulp.task(
  "minio",
  gulp.series("minio-upload-images","minio-upload-translation","minio-upload-build")
);
