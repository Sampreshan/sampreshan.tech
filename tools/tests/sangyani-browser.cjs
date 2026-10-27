/* Browser regression uses mocked API fixtures; never creates a public certificate. */
const fs = require('fs'), path = require('path'), cp = require('child_process'), assert = require('node:assert/strict');
let pw; try { pw = require('playwright'); } catch (_) { pw = require(process.env.PLAYWRIGHT_PATH); }
const root = path.resolve(__dirname, '../..');
const html = cp.execFileSync('php', [path.join(__dirname,'sangyani-render.php')], {encoding:'utf8'});
const script = fs.readFileSync(path.join(root,'buddyboss-theme-child/assets/js/quiz.js'),'utf8');
(async () => {
 const browser = await pw.chromium.launch({executablePath: process.env.CHROME_PATH || '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome', headless:true});
 const evidence=process.env.SANGYANI_EVIDENCE || '/tmp/sangyani-browser';fs.mkdirSync(evidence,{recursive:true});
 let checks=0;
 try {
  for (const width of [320,390,768,1440]) {
   const ctx=await browser.newContext({viewport:{width,height:900},reducedMotion:'reduce'}), page=await ctx.newPage();
   let failAnswer=false, feedback=null;
   const token='a'.repeat(40), question={id:'q1',key:'L01-Q001',text:'What does the word Sanatana mean?',options:['Eternal','New','Temple','Kingdom'],remaining:null};
   const run={token,player:'Fixture Learner',level:1,levels:10,levelTitle:'Sanatan Foundations',levelDone:0,levelTotal:50,levelsCleared:0,score:0,status:'active',seconds:0,mode:'learn',cert:null,review:[]};
   await page.route('https://fixture.invalid/**',async route=>{
    if(route.request().url()==='https://fixture.invalid/') return route.fulfill({contentType:'text/html',body:html});
    const body=route.request().postDataJSON();
    if(route.request().url().endsWith('/answer')) {
     if(failAnswer) return route.abort('failed');
     feedback={questionId:'q1',correct:true,expired:false,answer:0,points:10,explanation:'Sanatana means eternal; the term points to continuity of dharma.',checkpoint:false,cleared:false,shown:question,run:{...run,score:10}};return route.fulfill({json:feedback});
    }
    return route.fulfill({json:{run:feedback?feedback.run:run,question,feedback}});
   });
   await page.goto('https://fixture.invalid/'); await page.evaluate(()=>{window.spQuiz={api:'https://fixture.invalid/api/',nonce:'fixture',loggedIn:false,name:'',audio:{}};});await page.addScriptTag({content:script});
   assert(await page.locator('input[data-calm]').isChecked());checks++;
   assert(!(await page.locator('[data-sound]').isChecked()));checks++;
   assert(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth));checks++;
   assert((await page.locator('[data-start-btn]').boundingBox()).y < 700);checks++;
   await page.locator('[name=name]').fill('Fixture Learner');await page.locator('[data-start-btn]').click();await page.locator('[data-question]').waitFor({state:'visible'});
   assert(!(await page.locator('[data-next]').isVisible()));checks++;
   assert(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth));checks++;
   failAnswer=true;await page.locator('[data-choice="0"]').click();await page.locator('[data-play-error]').waitFor({state:'visible'});
   assert.equal(await page.evaluate(()=>localStorage.getItem('spQuizToken')),token);checks++;
   assert(await page.locator('[data-recover]').isVisible());checks++;
   failAnswer=false;await page.locator('[data-recover]').click();await page.locator('[data-choice="0"]').click();await page.locator('[data-explanation]').waitFor({state:'visible'});
   assert((await page.locator('[data-explanation]').textContent()).includes('eternal'));checks++;
   assert(await page.locator('[data-next]').isVisible());checks++;
   await page.screenshot({path:path.join(evidence,`play-${width}.png`),fullPage:true});
   await page.locator('[data-quit]').click();assert.equal(await page.evaluate(()=>localStorage.getItem('spQuizToken')),token);checks++;
   await page.locator('[data-start-btn]').click();await page.locator('[data-explanation]').waitFor({state:'visible'});assert.equal(await page.locator('[data-hud-score]').textContent(),'10');checks++;
   await ctx.close();
  }
  // Blocked browser storage + interrupted expired-card answer still recovers in the open tab.
  {
   const ctx=await browser.newContext({reducedMotion:'reduce'}), p=await ctx.newPage();let starts=0,answers=0,recoveryToken;
   const token='b'.repeat(40),question={id:'expired',key:'L01-Q001',text:'What does Sanatana mean?',options:['Eternal','New','Temple','Kingdom'],remaining:0};
   const run={token,player:'Storage Fixture',level:1,levels:10,levelTitle:'Sanatan Foundations',levelDone:0,levelTotal:50,levelsCleared:0,score:0,status:'active',seconds:30,mode:'challenge',cert:null,review:[]};
   await ctx.addInitScript(()=>{Storage.prototype.getItem=function(){throw new DOMException('Blocked','SecurityError');};Storage.prototype.setItem=function(){throw new DOMException('Blocked','SecurityError');};});
   await p.route('https://storage.invalid/**',async route=>{
    if(route.request().url()==='https://storage.invalid/') return route.fulfill({contentType:'text/html',body:html});
    const body=route.request().postDataJSON();
    if(route.request().url().endsWith('/answer')) {
     answers++;if(answers===1)return route.abort('failed');
     return route.fulfill({json:{questionId:'expired',correct:false,expired:true,answer:0,points:-5,explanation:'Sanatana means eternal. Review the meaning before continuing.',checkpoint:false,cleared:false,shown:question,run:{...run,score:-5}}});
    }
    starts++;if(starts===2)recoveryToken=body.token;
    return route.fulfill({json:{run,question}});
   });
   await p.goto('https://storage.invalid/');await p.evaluate(()=>{window.spQuiz={api:'https://storage.invalid/api/',nonce:'fixture',loggedIn:false,name:'',audio:{}};});await p.addScriptTag({content:script});
   await p.locator('[name=name]').fill('Storage Fixture');await p.locator('[data-start-btn]').click();
   await p.waitForFunction(()=>document.querySelector('[data-play-error]').textContent.includes('Connection interrupted'));
   await p.locator('[data-recover]').click();await p.locator('[data-explanation]').waitFor({state:'visible'});
   assert.equal(recoveryToken,token);checks++;assert.equal(answers,2);checks++;assert((await p.locator('[data-feedback]').textContent()).includes('Time has ended'));checks++;
   await ctx.close();
  }
  // Extreme name width and print visibility, with a fixture certificate only.
  const page=await browser.newPage({viewport:{width:390,height:844}});
  const cert={id:'SBT-FIXTURE1',name:'A'.repeat(60),score:5000,date:'27 October 2026',url:'https://fixture.invalid/?certificate=SBT-FIXTURE1'};
  await page.route('https://fixture.invalid/**',r=>r.request().url().includes('/certificate/')?r.fulfill({json:cert}):r.fulfill({contentType:'text/html',body:html}));
  await page.goto('https://fixture.invalid/?certificate=SBT-FIXTURE1');await page.evaluate(()=>{window.spQuiz={api:'https://fixture.invalid/api/',nonce:'fixture',loggedIn:false,audio:{}};});
  await page.evaluate(()=>{window.__nameBounds=null;const f=CanvasRenderingContext2D.prototype.fillText;CanvasRenderingContext2D.prototype.fillText=function(t,x,y,...a){if(t==='A'.repeat(60))window.__nameBounds={width:this.measureText(t).width,x};return f.call(this,t,x,y,...a);};});
  await page.addScriptTag({content:script});await page.locator('[data-cert-text]').waitFor({state:'visible'});
  assert((await page.evaluate(()=>window.__nameBounds.width))<=1180);checks++;
  assert((await page.locator('[data-cert-text]').textContent()).includes('500 cards'));checks++;
  await page.screenshot({path:path.join(evidence,'certificate-long-name.png'),fullPage:true});
  await page.emulateMedia({media:'print'});assert(await page.locator('[data-cert-canvas]').isVisible());checks++;
  assert(!(await page.locator('[data-download-png]').isVisible()));checks++;
  console.log(JSON.stringify({status:'PASS',checks,widths:[320,390,768,1440],fixtureOnly:true,publicCertificatesIssued:0,evidence},null,2));
 } finally {await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1;});
