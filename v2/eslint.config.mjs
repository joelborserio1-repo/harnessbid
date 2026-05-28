import nextConfig from "eslint-config-next"

const config = [
  ...nextConfig,
  {
    ignores: ["components/ui/**"],
  },
]

export default config
