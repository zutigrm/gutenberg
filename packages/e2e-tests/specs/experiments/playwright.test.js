/**
 * External dependencies
 */
import { expect, chromium } from '@playwright/test';

let browser;
let page;

beforeAll( async () => {
	browser = await chromium.launch();
	page = await browser.newPage();
} );

afterAll( async () => {
	await browser.close();
} );

it( 'test Playwright with Jest', async () => {
	await page.goto( 'http://whatsmyuseragent.org/' );

	const ipAddressLocator = page.locator( '.ip-address' );
	await expect( ipAddressLocator ).toHaveText(
		/My IP Address: \d+\.\d+\.\d+\.\d+/
	);
} );
