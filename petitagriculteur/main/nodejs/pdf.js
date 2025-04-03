'use strict';

const argv = require('minimist')(process.argv.slice(2));
const puppeteer = require('puppeteer-core');

// CLI Args
const url = argv.url;
const destination = argv.destination;
const headerTemplate = argv.header || null;
const footerTemplate = argv.footer || null;
const headerFooterArgs = {
	...headerTemplate ? {headerTemplate: decodeURIComponent((headerTemplate + '').replace(/\+/g, '%20'))} : {},
	...footerTemplate ? {footerTemplate: decodeURIComponent((footerTemplate + '').replace(/\+/g, '%20'))} : {},
	...(headerTemplate || footerTemplate) ? {displayHeaderFooter : true, margin: {top: '150px'}} : {},
};
console.log(url, destination, headerFooterArgs);
(async() => {

	const browser = await puppeteer.launch({
		executablePath: '/var/www/chrome-linux/chrome',
		args: ['--no-sandbox']
	});

	const page = await browser.newPage();

	await page.goto(url, {
		waitUntil: 'networkidle0'
	});

	await page.pdf({
		path: destination,
		printBackground: true,
		format: 'A4',
		...headerFooterArgs,
	});

	await browser.close();


})();
