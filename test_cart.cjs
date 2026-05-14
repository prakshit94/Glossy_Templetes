const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage();
  
  page.on('console', msg => console.log('BROWSER CONSOLE:', msg.text()));
  page.on('pageerror', err => console.log('BROWSER ERROR:', err.message));

  console.log('Navigating to page...');
  await page.goto('http://127.0.0.1:8000/customers/10', { waitUntil: 'networkidle' });

  // Wait for Alpine to initialize
  await page.waitForTimeout(1000);

  // Take screenshot before click
  await page.screenshot({ path: 'before_click.png' });

  console.log('Clicking Cart button...');
  try {
      await page.click('button:has-text("Cart")');
      await page.waitForTimeout(1000);
      await page.screenshot({ path: 'after_click.png' });
      console.log('Click successful, screenshots taken.');
  } catch (e) {
      console.log('Failed to click:', e.message);
  }

  await browser.close();
})();
