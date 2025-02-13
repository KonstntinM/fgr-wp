const puppeteer = require('puppeteer');
const fs = require('fs');

(async () => {
    // Launch Puppeteer in headless mode
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();

    // URL for the Berlinale Generation list page
    const listUrl = 'https://www.berlinale.de/de/programm/berlinale-programm.html/f=52/o=asc/p=2/rp=25?page=2&film_nums=23&searchText=&day_id=&section_id=52&distributor=&cinema_id=&country_id=&date_id=&time_id=&genre=&documentary=&order_by=1&screenings=efm_festival';
    await page.goto(listUrl, { waitUntil: 'networkidle2' });

    // Extract film detail page links using the new selector
    const filmLinks = await page.evaluate(() => {
        // Select all links with the new class
        const anchors = Array.from(document.querySelectorAll('a.c-program-item__link'));
        // Map to full URLs by checking if the href is relative
        return anchors
            .map(a => {
                const href = a.getAttribute('href');
                // Prepend Berlinale domain if URL is relative
                return href.startsWith('http') ? href : 'https://www.berlinale.de' + href;
            })
            // Remove duplicates if any
            .filter((value, index, self) => self.indexOf(value) === index);
    });
    console.log(`Found ${filmLinks.length} film links.`);

    const films = [];

    // Process each film detail page
    for (const filmUrl of filmLinks) {
        console.log(`Processing film: ${filmUrl}`);
        const filmPage = await browser.newPage();
        await filmPage.goto(filmUrl, { waitUntil: 'networkidle2' });

        const filmData = await filmPage.evaluate(() => {
            // Helper function: safely extract innerText from an element
            const getText = selector => {
                const el = document.querySelector(selector);
                return el ? el.innerText.trim() : '';
            };

            // --- Title ---
            // Assume the title is in an <h1> element
            // --- Title ---
            const titleEl = document.querySelector('h1');
            const title = titleEl ? titleEl.innerText.trim() : '';

            // --- Section ---
            // Look for any text element that contains "Generation", then remove any year
            let section = '';
            const sectionEl = document.querySelector('.c-section-label__text');
            if (sectionEl) {
                section = sectionEl.innerText.trim();
            }

            // --- Image ---
            // Pick the first image with "media/filmstills" in its src for the high‑resolution image
            let image = '';
            const imgs = Array.from(document.querySelectorAll('img'));
            const filmImg = imgs.find(img => img.src.includes('media/filmstills'));
            if (filmImg) {
                image = filmImg.src;
            } else if (imgs.length) {
                image = imgs[0].src;
            }

            // --- Description ---
            // Try several common selectors; fallback to the first <p> element if none match.
            let description = '';
            const descContainer = document.querySelector('div.c-stage-content__content > p')
            if (descContainer) {
                description = descContainer.innerText.trim();
            }

            // --- Director ---
            // Locate an element containing the word "von" and extract the text after it.
            let director = '';
            const xpathExpression = '//li[span[@class="c-table__list-label" and normalize-space(text())="Regie"]]//ul[contains(@class,"c-table__list--inner")]//li//strong';
            const result = document.evaluate(xpathExpression, document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null);
            if (result.singleNodeValue) {
                director = result.singleNodeValue.textContent.trim();
            }

            return {
                title,
                description,
                image,
                section,
                director,
                link: window.location.href
            };
        });

        films.push(filmData);
        await filmPage.close();
    }

    // Write the film data to films.json with proper formatting.
    fs.writeFileSync('./films.json', JSON.stringify(films, null, 2));
    console.log(`Saved ${films.length} films to films.json`);

    await browser.close();
})();
