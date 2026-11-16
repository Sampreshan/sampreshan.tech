/* Isolated meaning-control regression: all requests are mocked, no production records. */
const fs = require('fs'), path = require('path'), cp = require('child_process'), assert = require('node:assert/strict');
let pw; try { pw = require('playwright'); } catch (_) { pw = require(process.env.PLAYWRIGHT_PATH); }
const root = path.resolve(__dirname, '../..');
const html = cp.execFileSync('php', [path.join(__dirname, 'sangyani-render.php')], {encoding:'utf8'});
const script = fs.readFileSync(path.join(root, 'buddyboss-theme-child/assets/js/quiz.js'), 'utf8');
(async () => {
 const engine = process.env.BROWSER_ENGINE || 'chromium';
 const browser = await pw[engine].launch(engine === 'chromium' ? {executablePath:process.env.CHROME_PATH || '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome', headless:true} : {headless:true, ...(process.env.WEBKIT_PATH ? {executablePath:process.env.WEBKIT_PATH} : {})});
 const widths = [320,360,375,390,430,768,820,1024,1180,1366,1920];
 let checks = 0; const errors = [];
 try {
  for (const width of widths) {
   const ctx = await browser.newContext({viewport:{width,height:900},hasTouch:true,reducedMotion:'reduce'}), page = await ctx.newPage();
   page.on('pageerror', e => errors.push(e.message));
   const question = {id:'q1',key:'L01-Q012',text:'Which sanskara is the first haircut of a child?',options:['Chudakarana (Mundan)','Vidyarambha','Karnavedha','Namakarana'],optionInfo:["A child's first haircut ceremony.",'A ceremony marking the beginning of learning or study.','The ear-piercing ceremony.','The naming ceremony.'],remaining:null};
   const run = {token:'a'.repeat(40),player:'Meaning Fixture',level:1,levels:10,levelTitle:'Sanatan Foundations',levelDone:0,levelTotal:50,levelsCleared:0,score:0,status:'active',seconds:0,mode:'learn',cert:null,review:[]};
   let answers = 0, actions = 0;
   await page.route('https://meanings.invalid/**', route => {
    const url = route.request().url();
    if (url === 'https://meanings.invalid/') return route.fulfill({contentType:'text/html',body:html});
    if (url.endsWith('/answer')) { answers++; return route.fulfill({json:{run,questionId:'q1',correct:true,answer:0,explanation:'Fixture explanation.',checkpoint:false,cleared:false}}); }
    if (url.endsWith('/action')) actions++;
    return route.fulfill({json:{run,question}});
   });
   await page.goto('https://meanings.invalid/');
   await page.evaluate(() => { window.spQuiz = {api:'https://meanings.invalid/api/',nonce:'fixture',loggedIn:false,name:'',audio:{}}; });
   await page.addScriptTag({content:script});
   await page.locator('[name=name]').fill('Meaning Fixture'); await page.locator('[data-start-btn]').click();
   const info = page.locator('.sp-quiz__info-btn'), popovers = page.locator('.sp-quiz__info-popover');
   await info.first().waitFor(); assert.equal(await info.count(),4); checks++;
   await info.first().tap(); assert.equal(await info.first().getAttribute('aria-expanded'),'true'); checks++;
   assert.equal(await popovers.first().textContent(),question.optionInfo[0]); checks++;
   assert.equal(answers,0); checks++;
   await info.nth(1).tap(); assert.equal(await page.locator('.sp-quiz__info-popover:visible').count(),1); checks++;
   assert.equal(await info.first().getAttribute('aria-expanded'),'false'); checks++;
   await info.nth(1).tap(); assert.equal(await page.locator('.sp-quiz__info-popover:visible').count(),0); checks++;
   await info.nth(2).tap(); await page.keyboard.press('Escape'); assert.equal(await info.nth(2).getAttribute('aria-expanded'),'false'); checks++;
   await info.nth(3).tap(); await page.locator('[data-question]').click(); assert.equal(await popovers.nth(3).isVisible(),false); checks++;
   // Keyboard focus opens a description; Escape dismisses without moving focus.
   await page.locator('[data-choice="0"]').focus(); await page.keyboard.press('Tab');
   assert.equal(await info.first().getAttribute('aria-expanded'),'true'); checks++;
   assert.equal(await info.first().getAttribute('aria-describedby'),await popovers.first().getAttribute('id')); checks++;
   await page.keyboard.press('Escape'); assert(await info.first().evaluate(el => el === document.activeElement)); checks++;
   // Desktop hover and first click both expose a meaning; a second click closes it.
   await page.locator('[data-question]').focus(); await info.first().hover(); assert(await popovers.first().isVisible()); checks++;
   await info.first().click(); assert(await popovers.first().isVisible()); checks++;
   await info.first().click(); assert(!(await popovers.first().isVisible())); checks++;
   await info.last().tap();
   const bounds = await popovers.last().boundingBox(); assert(bounds.x>=0 && bounds.x+bounds.width<=width); checks++;
   assert(await page.evaluate(() => document.documentElement.scrollWidth<=innerWidth)); checks++;
   if (process.env.SANGYANI_EVIDENCE) { fs.mkdirSync(process.env.SANGYANI_EVIDENCE,{recursive:true}); await page.screenshot({path:path.join(process.env.SANGYANI_EVIDENCE,`meanings-${width}.png`),fullPage:true}); }
   assert.equal(answers,0); checks++;
   await page.locator('[data-choice="0"]').tap(); await page.locator('[data-explanation]').waitFor({state:'visible'});
   assert.equal(answers,1); checks++;
   assert(await page.locator('[data-choice="0"]').isDisabled()); checks++;
   await info.last().tap(); assert(await popovers.last().isVisible()); checks++;
   await page.waitForTimeout(2500); assert.equal(actions,0,'Reading a meaning cancels automatic advancement'); checks++;
   await page.locator('[data-next]').tap(); await page.waitForFunction(() => document.querySelector('[data-next]').hidden);
   assert.equal(await page.locator('.sp-quiz__info-popover:visible').count(),0, JSON.stringify(await page.locator('.sp-quiz__info-btn').evaluateAll(es=>es.map(e=>({expanded:e.getAttribute('aria-expanded'),focus:e===document.activeElement,hover:e.matches(':hover')}))))); checks++;
   await ctx.close();
  }
  assert.deepEqual(errors,[]); checks++;
  console.log(JSON.stringify({status:'PASS',checks,engine,widths,productionWrites:0},null,2));
 } finally { await browser.close(); }
})().catch(e => {console.error(e);process.exitCode=1;});
