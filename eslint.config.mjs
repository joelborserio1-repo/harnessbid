import next from "eslint-config-next/core-web-vitals"

/** Flat ESLint config built on Next.js' core-web-vitals ruleset. */
const eslintConfig = [
  {
    ignores: [
      ".next/**",
      "node_modules/**",
      "legacy-xcloud-fixes/**",
      "deploy/**",
      "public/**",
      "next-env.d.ts",
      "*.config.mjs",
      "*.config.cjs",
      "playwright.config.ts",
      "e2e/**",
      "tests/**",
    ],
  },
  ...(Array.isArray(next) ? next : [next]),
  {
    rules: {
      // Intentional subscription/initialisation effects (auth state, media
      // query) legitimately set state on mount; keep as a warning rather than
      // a hard error so lint stays green.
      "react-hooks/set-state-in-effect": "warn",
      // shadcn/ui primitives (e.g. sidebar) use Math.random for ids during
      // render; treat purity findings as warnings rather than hard errors.
      "react-hooks/purity": "warn",
    },
  },
]

export default eslintConfig
