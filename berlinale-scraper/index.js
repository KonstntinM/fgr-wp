const puppeteer = require('puppeteer');

async function scrapeFilmTitlesAndLinks() {
    const browser = await puppeteer.launch();
    const page = await browser.newPage();
    await page.goto('https://www.berlinale.de/de/programm/berlinale-programm.html?page=2&film_nums=23&searchText=&day_id=&section_id=52&distributor=&cinema_id=&country_id=&date_id=&time_id=&genre=&documentary=&order_by=1&screenings=efm_festival');

    const collapseButtonSelector = '.a-collapse .collapsed';

    // Click all the collapse buttons
    const collapseButtons = await page.$$(collapseButtonSelector);
    for (let i = 0; i < collapseButtons.length; i++) {
        await collapseButtons[i].click();
    }

    // Find all a tags on the page, that have an href attribute, that starts with /de/programm/detail.html
    const filmLinks = await page.$$eval('a[href^="/de/programm/detail.html"]', links => links.map(link => link.href));

    // Remove duplicates
    const uniqueFilmLinks = [...new Set(filmLinks)];

    // Scrape all fil data
    const promises = uniqueFilmLinks.map(scrapeSingleFilm);
    // Wait for all promises to resolve, ignore the rejected ones
    const films = await Promise.allSettled(promises)
    // write films to a json file
    const fs = require('fs');
    fs.writeFileSync('films_2.json', JSON.stringify(films, null, 2));


    // Log them
    console.log(uniqueFilmLinks);

    await browser.close();
}

scrapeFilmTitlesAndLinks()

const filmLinks = [
    'https://www.berlinale.de/de/programm/detail.html?film_id=202401040',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202404350',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202403865',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202412874',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202403698',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202403050',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202401056',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202412892',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202403456',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202401008',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202402994',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202407066',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202410034',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202404801',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202410405',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202406563',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202412801',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202411929',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202414163',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202403921',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202403617',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202411276',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202413056',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202403603',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202405846',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202414269',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202410707',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202406370',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202408439',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202403796',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202402016',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202409035',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202405933',
    'https://www.berlinale.de/de/programm/detail.html?film_id=202412978'
  ]

async function scrapeSingleFilm (url) {

    const browser = await puppeteer.launch();
    const page = await browser.newPage();
    await page.goto(url);

        // Get titel from h1
        let title = await page.$eval('h1', h1 => h1.textContent);
        title = title.trim();

        if (title.toLocaleLowerCase().includes("preisträgerfilme")) return Promise.reject("Film is not a feature film.");

        // Get the description
        const descriptionContainerSelector = ".ds-readmoreItem";
        let germanDescription = await page.$eval(descriptionContainerSelector, description => description.textContent);
        germanDescription = germanDescription.trim();

        // Get english description by replacing /de/ with /en/
        const englishUrl = url.replace('/de/', '/en/');
        await page.goto(englishUrl);
        let englishDescription = await page.$eval(descriptionContainerSelector, description => description.textContent);
        englishDescription = englishDescription.trim();

        const description = germanDescription + "<br><br>" + englishDescription;

        // Get the image from the data-src attribute
        const imageSelector = "img.img-fluid";
        let image = await page.$eval(imageSelector, image => image.getAttribute('data-src'));
        image = "https://www.berlinale.de" + image;

        // Get section
        const sectionSelector = ".section-tag";
        let section = await page.$eval(sectionSelector, section => section.textContent);
        section = section.trim();
        
        // Get director
        const directorSelector = ".ds__staff .table-no-styles tr:first-child td:last-child";
        const director = await page.$eval(directorSelector, director => director.textContent);

        const film = { title, description, image, section, director, link: filmLinks[i] };
        console.log(film);
        return Promise.resolve(film);
}

async function scrapeFilmDetails() {
    const browser = await puppeteer.launch();
    const page = await browser.newPage();

    const films = [];

    for (let i = 0; i < filmLinks.length; i++) {
        await page.goto(filmLinks[i]);

        // Get titel from h1
        let title = await page.$eval('h1', h1 => h1.textContent);
        title = title.trim();

        // Get the description
        const descriptionContainerSelector = ".ds-readmoreItem";
        let description = await page.$eval(descriptionContainerSelector, description => description.textContent);
        description = description.trim();

        // Get the image from the data-src attribute
        const imageSelector = "img.img-fluid";
        let image = await page.$eval(imageSelector, image => image.getAttribute('data-src'));
        image = "https://www.berlinale.de" + image;

        // Get section
        const sectionSelector = ".section-tag";
        let section = await page.$eval(sectionSelector, section => section.textContent);
        section = section.trim();
        
        // Get director
        const directorSelector = ".ds__staff .table-no-styles tr:first-child td:last-child";
        const director = await page.$eval(directorSelector, director => director.textContent);

        const film = { title, description, image, section, director, link: filmLinks[i] };
        console.log(film);
        films.push(film);
    }

    // write films to a json file
    const fs = require('fs');
    fs.writeFileSync('films.json', JSON.stringify(films, null, 2));

    await browser.close();
}
