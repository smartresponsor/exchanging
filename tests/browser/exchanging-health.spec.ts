import { expect, test } from '@playwright/test';

test('standalone health endpoint is browser-reachable and healthy', async ({ page }) => {
  const response = await page.goto('/exchanging/health');

  expect(response).not.toBeNull();
  expect(response?.status()).toBe(200);

  const payload = await page.locator('body').innerText();
  expect(payload).toContain('"healthy":true');
  expect(payload).toContain('"local_rate_provider_registry"');
  expect(payload).toContain('"operational_status"');
});
