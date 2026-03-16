import { test, expect, type Page } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const wcagTags = ['wcag2a', 'wcag2aa', 'wcag22aa'];

function createAxeBuilder(page: Page): AxeBuilder {
    return new AxeBuilder({ page })
        .withTags(wcagTags)
        .exclude('[id^="phpdebugbar"]')
        .exclude('.phpdebugbar-badge');
}

async function navigateAndWait(page: Page, path: string): Promise<void> {
    await page.goto(path, { waitUntil: 'networkidle' });
    // Disable all CSS animations/transitions so axe reads final computed colors
    await page.addStyleTag({
        content: '*, *::before, *::after { animation: none !important; transition: none !important; }',
    });
    // Allow one frame for styles to apply
    await page.waitForTimeout(50);
}

test.describe('Accessibility – Frontend (WCAG 2.2 AA)', () => {
    test('homepage', async ({ page }) => {
        await navigateAndWait(page, '/');
        const { violations } = await createAxeBuilder(page).analyze();

        expect(violations).toEqual([]);
    });

    test('blog post', async ({ page }) => {
        await navigateAndWait(page, '/blog/willkommen-auf-meinem-blog');
        const { violations } = await createAxeBuilder(page).analyze();

        expect(violations).toEqual([]);
    });

    test('static page', async ({ page }) => {
        await navigateAndWait(page, '/seite/impressum');
        const { violations } = await createAxeBuilder(page).analyze();

        expect(violations).toEqual([]);
    });

    test('404 page', async ({ page }) => {
        await navigateAndWait(page, '/nicht-vorhanden');
        const { violations } = await createAxeBuilder(page).analyze();

        expect(violations).toEqual([]);
    });

    test('archive page', async ({ page }) => {
        await navigateAndWait(page, '/archiv');
        const { violations } = await createAxeBuilder(page).analyze();

        expect(violations).toEqual([]);
    });
});
