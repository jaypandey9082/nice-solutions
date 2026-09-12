/** Real native editor QA. Run with node platform/tests/cms-admin.mjs. */
import { readFileSync } from 'node:fs';
import { execFileSync } from 'node:child_process';
import { resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { chromium } from '/Users/jaypandey/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright/index.mjs';

const root = fileURLToPath(new URL('../../', import.meta.url));
const credentials = JSON.parse(readFileSync(resolve(root, 'platform/.runtime/main/private.json'), 'utf8'));
const manifest = JSON.parse(readFileSync(resolve(root, 'platform/instances/main.json'), 'utf8'));
const origin = `http://localhost:${manifest.port}`;
const wp = (...args) => execFileSync('bash', ['platform/scripts/runtime.sh', 'wp', 'main', ...args], { cwd: root, encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe'] }).trim();
let browser;
let fixture;
let stage = 'setup';
let checks = 0;
const assert = (value, message) => { if (!value) throw new Error(message); checks++; };
const redact = (value) => String(value).replaceAll(credentials.admin_password, '[redacted]').replaceAll(credentials.admin_user, '[redacted]');

const revealMetaBox = async (page, box) => {
  await box.waitFor({ state: 'attached' });
  if (await box.isVisible()) return;
  const screenOptions = page.getByRole('button', { name: /screen options/i });
  if (await screenOptions.count() && await screenOptions.isVisible()) {
    await screenOptions.click();
    const option = page.getByRole('checkbox', { name: 'NICE Page Content and Media', exact: true });
    if (await option.count() && !await option.isChecked()) await option.check();
    await screenOptions.click();
  }
  const metaBoxes = page.getByRole('button', { name: 'Meta Boxes', exact: true });
  if (await metaBoxes.count() && await metaBoxes.isVisible()) {
    await metaBoxes.focus();
    await page.keyboard.press('Enter');
    await page.waitForTimeout(500);
  }
  if (await box.isVisible()) return;
  const toggle = page.locator('[aria-controls="nice-platform-page"]:visible').first();
  if (await toggle.count()) await toggle.click();
  if (!await box.isVisible()) {
    await page.screenshot({ path: resolve(root, 'platform/tests/browser-output/cms-admin-hidden.png'), fullPage: false });
    const visibility = await box.evaluate((node) => {
      const trail = [];
      for (let current = node; current && trail.length < 8; current = current.parentElement) {
        const style = getComputedStyle(current);
        trail.push({ tag: current.tagName, id: current.id, class: current.className, display: style.display, visibility: style.visibility, height: current.getBoundingClientRect().height });
      }
      return trail;
    });
    const controls = await page.locator('button, [role="button"], a, [role="menuitem"]').evaluateAll((nodes) => nodes.filter((node) => /NICE Page Content|meta box|additional|enable/i.test(`${node.textContent} ${node.getAttribute('aria-label') || ''}`)).map((node) => ({ tag: node.tagName, text: node.textContent.trim(), role: node.getAttribute('role'), aria: node.getAttribute('aria-label'), controls: node.getAttribute('aria-controls'), visible: Boolean(node.getClientRects().length), outer: node.outerHTML.slice(0, 500) })));
    throw new Error(`NICE panel remains hidden: ${JSON.stringify({ visibility, controls })}`);
  }
  await box.scrollIntoViewIfNeeded();
  await box.waitFor({ state: 'visible' });
};

try {
  const attachments = JSON.parse(wp('post', 'list', '--post_type=attachment', '--post_status=inherit', '--fields=ID,post_mime_type', '--format=json')).filter((item) => item.post_mime_type.startsWith('image/'));
  assert(attachments.length >= 2, 'Two existing reference image attachments are needed.');
  fixture = Number(wp('post', 'create', '--post_type=page', '--post_status=draft', `--post_title=CMS media QA ${Date.now()}`, '--porcelain'));
  assert(Number.isInteger(fixture) && fixture > 0, 'Temporary draft Page created.');
  browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
  page.setDefaultTimeout(15000);
  stage = 'login';
  await page.goto(`${origin}/wp-login.php`);
  await page.locator('#loginform').evaluate((form, values) => {
    form.setAttribute('autocomplete', 'off');
    const setter = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value').set;
    for (const [id, value] of Object.entries(values)) {
      const input = form.querySelector(`#${id}`);
      input.setAttribute('autocomplete', 'off');
      setter.call(input, value);
      input.dispatchEvent(new Event('input', { bubbles: true }));
      input.dispatchEvent(new Event('change', { bubbles: true }));
    }
  }, { user_login: credentials.admin_user, user_pass: credentials.admin_password });
  const loginValues = { user: await page.locator('#user_login').inputValue(), pass: await page.locator('#user_pass').inputValue() };
  assert(loginValues.user === credentials.admin_user, `Private local admin username populated the native login form (${loginValues.user.length}/${credentials.admin_user.length} characters).`);
  assert(loginValues.pass === credentials.admin_password, `Private local admin password populated the native login form (${loginValues.pass.length}/${credentials.admin_password.length} characters).`);
  await page.locator('#user_pass').press('Enter');
  try {
    await page.waitForURL(/\/wp-admin(?:\/|$)/, { waitUntil: 'domcontentloaded', timeout: 30000 });
  } catch (error) {
    const loginError = await page.locator('#login_error').textContent().catch(() => '');
    throw new Error(`Admin login did not complete (${page.url()}): ${loginError || error.message}`);
  }
  stage = 'editor inspection';
  await page.goto(`${origin}/wp-admin/post.php?post=${fixture}&action=edit`);
  const welcomeHeading = page.getByText('Welcome to the editor', { exact: true });
  if (await welcomeHeading.count() && await welcomeHeading.isVisible()) {
    await page.keyboard.press('Escape');
    await welcomeHeading.waitFor({ state: 'hidden' });
  }
  const box = page.locator('#nice-platform-page');
  await revealMetaBox(page, box);
  console.log('CMS controls snapshot:\n' + await box.ariaSnapshot());
  const slot = box.locator('.nice-platform-slot').first();
  const desktop = slot.locator('[data-nice-picker]').nth(0);
  const mobile = slot.locator('[data-nice-picker]').nth(1);

  const choose = async (picker, id) => {
    await picker.getByRole('button', { name: /^(Select|Replace)$/ }).click();
    const dialog = page.getByRole('dialog').last();
    await dialog.waitFor({ state: 'visible' });
    const library = dialog.getByRole('tab', { name: 'Media Library', exact: true });
    if (await library.count()) await library.click();
    console.log('Media modal snapshot:\n' + await dialog.ariaSnapshot());
    // The attachment DOM is inspected before using its native selectable element.
    await dialog.locator(`.attachment[data-id="${id}"]`).waitFor({ state: 'visible' });
    const candidates = await dialog.locator('.attachment[data-id]').evaluateAll((nodes) => nodes.map((node) => ({ id: node.dataset.id, role: node.getAttribute('role') })));
    assert(candidates.some((item) => Number(item.id) === Number(id)), 'Requested attachment is present in the native Media Library.');
    await dialog.locator(`.attachment[data-id="${id}"]`).click();
    await dialog.getByRole('button', { name: 'Use image', exact: true }).click();
    await dialog.waitFor({ state: 'hidden' });
    assert(Number(await picker.locator('[data-nice-media-id]').inputValue()) === Number(id), 'Native selector populated the attachment ID.');
  };

  const save = async (expected) => {
    const saveButton = page.getByRole('button', { name: 'Save', exact: true });
    const saveDraft = page.getByRole('button', { name: 'Save draft', exact: true });
    if (await saveButton.count()) await saveButton.click();
    else await saveDraft.click();
    for (let attempt = 0; attempt < 30; attempt++) {
      const value = JSON.parse(wp('post', 'meta', 'list', String(fixture), '--format=json'));
      const map = Object.fromEntries(value.map((item) => [item.meta_key, item.meta_value]));
      if (Object.entries(expected).every(([key, wanted]) => String(map[key]) === String(wanted))) return;
      await page.waitForTimeout(200);
    }
    throw new Error('Native editor save did not persist expected media metadata.');
  };

  stage = 'select and replace';
  await choose(desktop, attachments[0].ID);
  await choose(desktop, attachments[1].ID);
  stage = 'mobile and focal controls';
  await choose(mobile, attachments[0].ID);
  await slot.locator('input[name="nice_media[hero][x]"]').fill('22');
  await slot.locator('input[name="nice_media[hero][y]"]').fill('73');
  await slot.getByRole('checkbox', { name: 'Temporary reference imagery' }).check();
  await box.locator('#nice-hero_title').fill('CMS QA editable title');
  await box.locator('#nice-clients').fill('Voltas\nCRISIL');
  stage = 'save and reload';
  await save({ _nice_media_hero_id: attachments[1].ID, _nice_media_hero_mobile_id: attachments[0].ID, _nice_media_hero_x: 22, _nice_media_hero_y: 73, _nice_media_hero_reference: '1', _nice_home_hero_title: 'CMS QA editable title', _nice_client_names: 'Voltas\nCRISIL' });
  await page.reload();
  await revealMetaBox(page, box);
  assert(Number(await desktop.locator('[data-nice-media-id]').inputValue()) === Number(attachments[1].ID), 'Replaced desktop image persists after reload.');
  assert(Number(await mobile.locator('[data-nice-media-id]').inputValue()) === Number(attachments[0].ID), 'Mobile image persists after reload.');
  assert(await slot.locator('input[name="nice_media[hero][x]"]').inputValue() === '22', 'Focal point persists after reload.');
  stage = 'remove and blank persistence';
  await desktop.getByRole('button', { name: 'Remove', exact: true }).click();
  await mobile.getByRole('button', { name: 'Remove', exact: true }).click();
  await slot.getByRole('checkbox', { name: 'Temporary reference imagery' }).uncheck();
  await box.locator('#nice-hero_title').fill('');
  await box.locator('#nice-clients').fill('');
  await save({ _nice_media_hero_id: '0', _nice_media_hero_mobile_id: '0', _nice_media_hero_reference: '', _nice_home_hero_title: '', _nice_client_names: '' });
  await page.reload();
  await revealMetaBox(page, box);
  assert(await desktop.locator('[data-nice-media-id]').inputValue() === '0' && await mobile.locator('[data-nice-media-id]').inputValue() === '0', 'Removed desktop and mobile images remain cleared.');
  assert(await box.locator('#nice-hero_title').inputValue() === '', 'Cleared title stays blank in the editor.');
  assert(await box.locator('#nice-clients').inputValue() === '', 'Cleared client list stays blank.');
  console.log(`Success: ${checks} real WordPress admin media checks passed.`);
} catch (error) {
  console.error(`CMS admin QA failed during ${stage}: ${redact(error.message)}`);
  process.exitCode = 1;
} finally {
  if (browser) await browser.close();
  if (fixture) {
    wp('post', 'delete', String(fixture), '--force');
    console.log('Temporary QA draft deleted; homepage and attachments were not modified.');
  }
}
