module.exports = {
  apps: [
    {
      name: "harnessbid-v2",
      script: "node_modules/next/dist/bin/next",
      args: "start -p 3001",
      env: {
        NODE_ENV: "production",
        NEXT_PUBLIC_BASE_PATH: "/v2",
      },
    },
  ],
}
