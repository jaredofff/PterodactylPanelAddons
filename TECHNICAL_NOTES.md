# Technical Notes - Pterodactyl v1.12.2 Compatibility

This document outlines the specific adjustments made to ensure the Ultimate Suite is fully compatible with the latest Pterodactyl Panel (v1.12.2+).

## 🛣️ 1. Router Path Changes
In recent versions, the main server routing file has moved:
- **Old Path**: `resources/scripts/components/server/ServerRouter.tsx`
- **New Path (v1.12.2)**: `resources/scripts/routers/ServerRouter.tsx`
*The installer and manual guides have been updated to reflect this change.*

## ⚛️ 2. React Export Patterns
Pterodactyl has shifted some internal components from named exports to default exports:
- **useFlash**: Now requires `import useFlash from '@/plugins/useFlash'` instead of `{ useFlash }`.
- **Spinner**: Now requires `import Spinner from '@/components/elements/Spinner'` instead of `{ Spinner }`.
*Our components have been patched to support these new patterns.*

## 🛠️ 3. Build System (Yarn & Node)
While the panel can be managed with NPM, the build pipeline heavily relies on Yarn.
- **Node Version**: We recommend **Node.js 18** for stability.
- **Yarn Engine Check**: If Yarn blocks the build due to Node version mismatch, use:
  ```bash
  yarn config set ignore-engines true
  ```
- **Production Build**: Always use `npm run build:production` to ensure assets are properly minified and the manifest is updated.

## 🎨 4. Sidebar Wrapper
In v1.12.2, the sidebar links should be wrapped in the correct component to maintain layout integrity. 
- Use the updated `ServerRouter.tsx` template provided in the documentation to ensure the "Ultimate Suite" section appears with the correct styling and cyan highlights.

---
*Maintained by Jared - Ultimate Suite Team*
