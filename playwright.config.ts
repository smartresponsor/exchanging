import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './tests/browser',
  fullyParallel: false,
  retries: 0,
  reporter: 'line',
  use: {
    baseURL: 'http://127.0.0.1:9081',
    channel: 'chrome',
    headless: true,
  },
  webServer: {
    command: 'php -S 127.0.0.1:9081 -t public public/index.php',
    url: 'http://127.0.0.1:9081/exchanging/health',
    reuseExistingServer: true,
    timeout: 30_000,
  },
});
