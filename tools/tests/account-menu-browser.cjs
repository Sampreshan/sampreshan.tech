/** Authenticated read/interaction checks. Supply a private short-lived QA session; notification read receipts are mocked. Never commit credentials. */
const fs=require('fs'),path=require('path'),assert=require('node:assert/strict');
let pw;try { pw=require('playwright'); } catch (_) { pw=require(process.env.PLAYWRIGHT_PATH); }
const root=path.resolve(__dirname,'../..'),out=process.env.SP_ACCOUNT_EVIDENCE || '/tmp/sp-account-menu-evidence';fs.mkdirSync(out,{recursive:true});
const auth=JSON.parse(fs.readFileSync(process.env.SP_ACCOUNT_SESSION_FILE));
const live=process.env.SP_LIVE==='1',widths=[320,360,375,390,430,768,820,1024,1180,1366,1920],routes=['/','/dashboard/','/profile/','/settings/','/community-center/','/sanatan-quiz/'];
const report={mode:live?'live':'preview',observations:[],errors:[],checks:0};
(async()=>{for(const engine of ['chromium','webkit']){
 const browser=await pw[engine].launch(engine==='chromium'?{headless:true,...(process.env.CHROME_PATH?{executablePath:process.env.CHROME_PATH}:{})}:{headless:true,...(process.env.WEBKIT_PATH?{executablePath:process.env.WEBKIT_PATH}:{})});
 const ctx=await browser.newContext({viewport:{width:390,height:900},reducedMotion:'reduce'});
 await ctx.addCookies([{name:auth.cookie_name,value:auth.cookie,domain:'sampreshan.tech',path:'/',httpOnly:true,secure:true,expires:auth.expires,sameSite:'Lax'}]);
 try{const page=await ctx.newPage();page.on('requestfailed',r=>{if(report.errors.length<15)report.errors.push({request:new URL(r.url()).pathname,error:r.failure()?.errorText});});page.on('pageerror',e=>report.errors.push({engine,message:e.message}));
 await page.route('**/wp-admin/admin-ajax.php',async r=>{if((r.request().postData()||'').includes('sp_notifications_read'))return r.fulfill({json:{success:true,data:{unread:0}}});return r.continue();});
 for(const route of routes){await page.goto('https://sampreshan.tech'+route+'?sp_menu_qa=20261116',{waitUntil:'domcontentloaded',timeout:60000});await page.mouse.move(30,100);await page.keyboard.press('Tab');
 await page.waitForFunction(()=>document.styleSheets.length>10 && document.querySelector('#masthead')?.getBoundingClientRect().height<200,null,{timeout:30000});
 if(!live){await page.addStyleTag({content:'a[href*="wp-login.php"][href*="action=logout"]{display:flex!important}'});await page.evaluate(markup=>{const holder=document.createElement('div');holder.innerHTML=markup;document.body.append(...holder.childNodes);},auth.markup);
 await page.addStyleTag({content:fs.readFileSync(root+'/buddyboss-theme-child/assets/css/account-menu.css','utf8')});
 await page.addStyleTag({content:fs.readFileSync(root+'/buddyboss-theme-child/assets/css/notifications.css','utf8')});
 await page.addScriptTag({content:fs.readFileSync(root+'/buddyboss-theme-child/assets/js/navigation.js','utf8')});
 await page.addScriptTag({content:fs.readFileSync(root+'/buddyboss-theme-child/assets/js/notifications.js','utf8')});}
 const menu=page.locator('[data-sp-account-menu]:visible').first(),toggle=menu.locator('summary');await toggle.waitFor({state:'visible'});assert.deepEqual(await menu.locator('nav a,nav button').allTextContents().then(a=>a.map(t=>t.trim())),['Dashboard','Account','Activity','Notifications','Logout']);report.checks++;
 for(const width of widths){await page.setViewportSize({width,height:900});if(await menu.getAttribute('open')!==null)await toggle.click();await toggle.click();await page.waitForTimeout(30);
 const bounds=await menu.locator('nav').boundingBox();assert(bounds && bounds.x>=0 && bounds.x+bounds.width<=width+1,JSON.stringify({engine,route,width,bounds}));
 const overflow=await page.evaluate(()=>({width:innerWidth,scroll:document.documentElement.scrollWidth,bad:Array.from(document.querySelectorAll('body *')).filter(e=>{let r=e.getBoundingClientRect();return r.width&&r.right>innerWidth+1&&getComputedStyle(e).position!=='fixed';}).slice(0,8).map(e=>({tag:e.tagName,cls:e.className,right:e.getBoundingClientRect().right}))}));if(overflow.scroll>width+1)await page.screenshot({path:path.join(out,'overflow.png')});assert(overflow.scroll<=width+1,JSON.stringify({engine,route,width,overflow}));report.checks+=2;report.observations.push({engine,route,width,menuWithinViewport:true,pageOverflow:false});
 for(const item of await menu.locator('nav a,nav button').all()){assert((await item.boundingBox())?.height>=44,JSON.stringify({engine,route,width,item:await item.textContent(),open:await menu.getAttribute('open'),style:await item.evaluate(e=>({display:getComputedStyle(e).display,visibility:getComputedStyle(e).visibility,height:getComputedStyle(e).height,hidden:e.hidden,parentHidden:e.parentElement.hidden}))}));report.checks++;}
 await page.keyboard.press('Escape');assert.equal(await menu.getAttribute('open'),null);assert(await toggle.evaluate(e=>e===document.activeElement));report.checks+=2;
 await page.keyboard.press('Enter');await page.waitForTimeout(20);assert.notEqual(await menu.getAttribute('open'),null);report.checks++;
 await page.locator('main').first().click({position:{x:8,y:8},force:true});assert.equal(await menu.getAttribute('open'),null);report.checks++;
 }
 await toggle.click();await menu.locator('[data-sp-open-notifications]').click();assert(await page.locator('#sp-notif-panel').isVisible());assert.equal(await menu.getAttribute('open'),null);report.checks+=2;await page.keyboard.press('Escape');
 await toggle.click();if(route==='/')await page.screenshot({path:path.join(out,`${report.mode}-${engine}-menu.png`)});await page.keyboard.press('Escape');
 const logout=await menu.locator('a').last().getAttribute('href');assert(logout.includes('action=logout')&&logout.includes('_wpnonce='));report.checks++;
 }

 }finally{await ctx.close();await browser.close();}
}report.status='PASS';fs.writeFileSync(path.join(out,report.mode+'-browser.json'),JSON.stringify(report,null,2));console.log(JSON.stringify({status:report.status,observations:report.observations.length,checks:report.checks,errors:report.errors}));})().catch(e=>{report.status='FAIL';report.failure=e.message;fs.writeFileSync(path.join(out,report.mode+'-browser.json'),JSON.stringify(report,null,2));console.error(e.message);process.exitCode=1;});
