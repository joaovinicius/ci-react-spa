# Project Guidelines (ci-react-spa)

These notes target advanced contributors working on this CodeIgniter 4 + React (Vite) monorepo. They focus on project-specific setup, build, testing, and development conventions.

## Stack Overview
- Backend: PHP 8.1+ (CodeIgniter 4), Composer-managed, Dockerized (php-apache + MySQL 5).
- Frontend: React 18 + Vite 6, TypeScript 5.8, Tailwind CSS 4.
- Tests: 
  - Frontend unit/integration via Vitest (happy-dom + Testing Library).
  - Backend tests via PHPUnit (CI4’s test bootstrap), preferably executed inside Docker.

Paths of note:
- Frontend source: src/frontend
- Backend (CI4) app/config: src/backend
- CI4 CLI: src/spark
- Public web root for built SPA: public_html

---

## Build and Configuration

### Frontend (Vite)
- Root is src/frontend; built assets emit to public_html.
- Env loading: vite.config.ts uses loadEnv(mode, path.resolve(process.cwd(), 'src'), ''). This means .env files are read from the src/ directory, not repo root. Typical flow:
  1) cp src/.env.example src/.env (or use npm run copy-env)
  2) Put VITE_* vars there (e.g., VITE_API_BASE_URL, VITE_BASENAME). Vite will inject these into import.meta.env.
- Base path: vite.config.ts computes base using NODE_ENV and VITE_BASENAME only when command === 'build'. For local dev (vite server) the base is '/'. For production builds, set both NODE_ENV=development|production and VITE_BASENAME to ensure asset URLs resolve correctly when served behind a sub-path.
- Common commands:
  - npm run frontend:dev — start dev server on :3000
  - npm run frontend:build — prod build to public_html
  - npm run frontend:build-local — development build to public_html with base from VITE_BASENAME
  - npm run frontend:preview — preview built app

Notes:
- Alias '@' -> ./src/frontend
- Tailwind is configured via @tailwindcss/vite plugin; standard Tailwind v4 file-based setup.

### Backend (CodeIgniter 4)
- Run backend tasks via Docker. The main service is php-apache exposed on ports 80/443 and mounted to /var/www/html.
- MySQL 5 container is provided with defaults via docker-compose.yml (MYSQL_DATABASE=development, user/password mysql). Data persists to ./data/mysql/dbdata.
- CI4 migrations config uses timestamp format 'Y-m-d-His_' (see src/backend/Config/Migrations.php). Ensure migration filenames comply (e.g., 2025-06-21-021332_AddSomething.php). Migrations and seeds live under src/backend/Database/{Migrations,Seeds}.
- Initial DB bootstrap:
  - npm run setup — copies env (src/.env.example -> src/.env), runs composer install (in container), migrates, then seeds InitialSeed.
  - Individual commands:
    - npm run backend:composer install
    - npm run backend:migrate
    - npm run backend:migrate:rollback
    - npm run backend:spark <cmd>

Important:
- The CI4 front controller is expected under public (phpunit.xml.dist sets PUBLICPATH=./public/); however our SPA build output is under public_html. Apache inside the container is configured by the Docker image in build/php (not included here) to serve the project; typically it will point to public_html for SPA and route API to CI4. If changing docroots, keep this mapping in sync across Docker and Vite base.

---

## Testing

### Frontend (Vitest)
- Config: vitest.config.ts
  - Environment: happy-dom
  - Globals: true (no need to import describe/it/expect explicitly, but it is allowed)
  - Setup file: ./test/setup.ts (adds @testing-library/jest-dom and cleans up between tests)
  - Test inclusion: src/frontend/**/*.test.{ts,tsx}
  - Coverage: v8 (text + html), excludes stories and setup
- NPM scripts:
  - npm run frontend:test — run once
  - npm run frontend:test:watch — watch mode
  - npm run frontend:test:coverage — with coverage

Add tests by placing *.test.ts(x) files under src/frontend. For React components, import from '@/...' thanks to alias.

Example (sanity):
- Temporary test we verified locally:
  - File: src/frontend/example.test.ts
  - Content:
    - describe('sanity', () => { it('adds numbers', () => expect(1+2).toBe(3) ) })
- Command: npm run frontend:test
- Result: 1 passed (verified). This file was removed after validation; reproduce as needed.

### Backend (PHPUnit)
- Configuration in phpunit.xml.dist; bootstrap system/Test/bootstrap.php, tests in ./tests.
- Tests rely on CI4 paths and optional DB config. The <php> block sets HOMEPATH, CONFIGPATH, and PUBLICPATH constants for the test environment.
- Run back-end tests in Docker (preferred dev path in this repo):
  - npm run backend:phpunit — executes ./vendor/bin/phpunit inside php-apache container
- Alternative (host):
  - ./vendor/bin/phpunit (requires PHP 8.1+ on host). If php is not available locally, use Docker as above.

Existing example tests:
- tests/unit/HealthTest.php — baseline CI4 app checks
- tests/session/ExampleSessionTest.php — sessions
- tests/database/ExampleDatabaseTest.php — DB behaviors

DB in tests:
- tests/README.md explains configuring the tests group in src/backend/Config/Database.php or .env and enabling coverage via Xdebug if desired.

---

## Code Generation and API Client
- OpenAPI to Zod types:
  - npm run frontend:codegen — chains two steps:
    - npm run frontend:codegen:openapi — curl http://localhost/api/v1/docs/generate > src/frontend/api/openapi.json
    - npm run frontend:codegen:zod — npx openapi-zod-client ./src/frontend/api/openapi.json -o ./src/frontend/api/schemas.ts --export-schemas --export-types --strict-objects
- Ensure the backend is running and the docs endpoint is reachable at http://localhost/api/v1/docs/generate before running codegen.

## Developer Notes
- Node/TypeScript versions: see devDependencies (TypeScript ~5.8.3, Vite ^6.3, Vitest ^3.1). Testing environment uses happy-dom 15.10.2; favor its capabilities/limitations over jsdom when stubbing browser APIs.
- ESLint:
  - eslint.config.js is present; run npm run frontend:lint to lint src/frontend.
- React Router v7 is in use; check route config and ensure VITE_BASENAME alignment when deploying into a subdirectory.
- State management: zustand. Forms: react-hook-form with @hookform/resolvers and zod.
- Date/time: luxon; prefer to keep Date conversions pure and time-zone aware.
- DB migrations: timestamp format 'Y-m-d-His_' is non-default but supported; ensure new migration files use this format so the runner picks them up.
- Seeding: InitialSeed inserts baseline org/tenant/user rows; review and adjust before running in non-dev environments.
- Docker MySQL: image mysql:5 with default credentials; update for production and consider newer MySQL if compatible.

## Common Flows
- First-time setup:
  1) docker compose up -d
  2) npm run setup
  3) npm run frontend:dev (for dev) or npm run frontend:build (for build to public_html)
- Running tests quickly:
  - Frontend: npm run frontend:test
  - Backend: npm run backend:phpunit (Docker) or ./vendor/bin/phpunit (host with PHP)

## Troubleshooting
- phpunit fails on host with '/usr/bin/env: php: No such file or directory': use npm run backend:phpunit to run inside Docker.
- Frontend .env not picked up: ensure it resides under src/.env (not repo root). Vite only loads from src per current config.
- Built assets missing at runtime: verify VITE_BASENAME and base in vite.config.ts for build mode, clear public_html/assets before rebuilding (npm run frontend:remove-public_html-assets-folder).

