module.exports = {
  apps: [
    {
      name: "harnessbid-v2",
      script: "node_modules/next/dist/bin/next",
      args: "start -p 3001",
      env: {
        NODE_ENV: "production",
        // Empty = served at site root. deploy.sh exports NEXT_PUBLIC_BASE_PATH
        // (from DEPLOY_BASE_PATH) and passes --update-env, so this is just the
        // fallback when starting PM2 directly.
        NEXT_PUBLIC_BASE_PATH: process.env.NEXT_PUBLIC_BASE_PATH || "",
      },
    },
  ],
}
