const fs = require('fs');
const path = require('path');
const dir = 'd:/Projects/Sina Rezadoost Website';

let s = fs.readFileSync(path.join(dir, 'index.html'), 'utf8');

// slice from the fonts <link> (in <head>) through just before </body>
const start = s.indexOf('<link rel="stylesheet" href="https://fonts.googleapis.com');
let body = s.slice(start).replace('</body>\n</html>\n', '');

// move <title>+link+style intact; wrap the rest in an rtl container
const he = body.indexOf('</style>') + '</style>'.length;
let css = body.slice(0, he).replace(
  ':root{\n  color-scheme: dark;',
  ':root{\n  color-scheme: dark;\n  direction: rtl;'
);
let rest = body.slice(he);

// inline local images as data URIs (artifact CSP blocks external/relative image loads)
function dataUri(rel) {
  const buf = fs.readFileSync(path.join(dir, rel));
  return 'data:image/jpeg;base64,' + buf.toString('base64');
}
rest = rest
  .replace('assets/sina-rezadoost.jpg', dataUri('assets/sina-rezadoost.jpg'))
  .replace('assets/catalyzer-logo.jpg', dataUri('assets/catalyzer-logo.jpg'));

const out =
  '<title>کاتالیزور — سینا رضادوست</title>\n' +
  css +
  '\n<div dir="rtl" lang="fa">\n' +
  rest +
  '\n</div>\n';

fs.writeFileSync(path.join(dir, 'catalyzer-preview.html'), out);
console.log('wrote catalyzer-preview.html', (out.length / 1024).toFixed(0) + ' KB');
