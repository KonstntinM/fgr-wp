const puppeteer = require('puppeteer');
const fs = require('fs');

(async () => {
    console.log('--- Berlinale Scraper Started ---');

    const browser = await puppeteer.launch({
        headless: true,
        executablePath: '/usr/bin/chromium',
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--window-size=1920,1080'
        ]
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080 });
    await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

    // --- COOKIE INJECTION ---
    await page.setCookie({
        name: 'ifb-cookie-consent',
        value: '%7B%22necessary%22:true%7D',
        domain: '.berlinale.de',
        path: '/',
        secure: true
    });

    const allFilmLinks = new Set();
    let currentPage = 1;
    let hasMorePages = true;

    // --- STEP 1: PAGINATION LOOP ---
    console.log('Step 1: Gathering all film links across pages...');

    while (hasMorePages) {
        const listUrl = `https://www.berlinale.de/de/programm/berlinale-programm.html/f=52/o=asc/rp=25?page=${currentPage}&section_id=52&screenings=efm_festival`;

        console.log(`Checking Page ${currentPage}...`);
        await page.goto(listUrl, { waitUntil: 'networkidle2', timeout: 60000 });

        const content = await page.content();

        // Check for the "No Results" message
        if (content.includes("Ihre Suche hat keine Ergebnisse erzielt.")) {
            console.log(`Reached the end at page ${currentPage}.`);
            hasMorePages = false;
            break;
        }

        // Extract links using your requested regex method
        const linksOnPage = await page.evaluate(() => {
            return Array.from(document.querySelectorAll('a[href*="/programm/"]'))
                .map(a => a.href)
                .filter(href => /\d{9,}/.test(href));
        });

        if (linksOnPage.length === 0) {
            hasMorePages = false;
        } else {
            linksOnPage.forEach(link => allFilmLinks.add(link));
            console.log(`Added ${linksOnPage.length} links from page ${currentPage}.`);
            currentPage++;
            // Small delay to be polite during pagination
            await new Promise(r => setTimeout(r, 100));
        }
    }

    console.log(`Step 2: Starting scrape for ${allFilmLinks.size} unique films.`);

    // --- STEP 2: SCRAPE INDIVIDUAL FILMS ---
    const films = [];
    const uniqueLinksArray = Array.from(allFilmLinks);

    for (const filmUrl of uniqueLinksArray) {
        console.log(`Scraping: ${filmUrl}`);
        const filmPage = await browser.newPage();

        let filmData = {
            link: filmUrl,
            title: '',
            title_de: '',
            section: '',
            description: '',
            description_en: '',
            director: '',
            image: ''
        };

        try {
            // 1. Scrape German Page
            await filmPage.goto(filmUrl, { waitUntil: 'networkidle2' });
            const dataDe = await filmPage.evaluate(() => ({
                title_original: document.querySelector('h1')?.innerText.trim() || '',
                title_de_en: document.querySelector('.ft__other-title')?.innerText.trim() || '',
                section: document.querySelector('.section-tag')?.innerText.trim() || '',
                description: document.querySelector('#ds-readmoreItem')?.innerText.trim() || '',
                director: Array.from(document.querySelectorAll('strong, span')).find(el => el.innerText.includes('von '))?.innerText.replace('von ', '').trim() || '',
                image: document.querySelector('img[src*="filmstills"]')?.src || ''
            }));

            filmData = { ...filmData, ...dataDe };

            // 2. Generate and Scrape English Page
            const enUrl = filmUrl.replace('/de/', '/en/').replace('/programm/', '/programme/');
            filmData.link_en = enUrl;

            await filmPage.goto(enUrl, { waitUntil: 'networkidle2' });
            const descEn = await filmPage.evaluate(() => {
                return document.querySelector('#ds-readmoreItem')?.innerText.trim() || '';
            });

            filmData.description_en = descEn;
            films.push(filmData);

        } catch (e) {
            console.error(`Error scraping ${filmUrl}: ${e.message}`);
            if (filmData.title) films.push(filmData);
        }

        await filmPage.close();
        await new Promise(r => setTimeout(r, 500));

        // Optional: Remove or adjust the test break below to scrape everything
        if (films.length >= 10) {
            //console.log("Stopping at 10 films for testing purposes.");
            //break;
        }
    }

    fs.writeFileSync('./films.json', JSON.stringify(films, null, 2));
    console.log(`Success! Saved ${films.length} films to films.json.`);

    await browser.close();
})();