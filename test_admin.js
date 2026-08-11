const fs = require('fs');
const jsdom = require("jsdom");
const { JSDOM } = jsdom;
const html = fs.readFileSync('admin.html', 'utf8');
const dom = new JSDOM(html, { runScripts: "dangerously" });

try {
    const code = fs.readFileSync('admin.js', 'utf8');
    dom.window.eval(code);
    
    // Trigger DOMContentLoaded
    const event = dom.window.document.createEvent('Event');
    event.initEvent('DOMContentLoaded', true, true);
    dom.window.document.dispatchEvent(event);
    console.log("No errors on DOMContentLoaded!");
} catch (e) {
    console.error("Error evaluating admin.js:", e);
}
