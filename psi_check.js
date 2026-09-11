const https = require('https');
const url = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=https://nagachethanapucollege.in&strategy=mobile&category=performance&category=accessibility&category=best-practices&category=seo';

https.get(url, (res) => {
  let body = '';
  res.on('data', chunk => body += chunk);
  res.on('end', () => {
    const data = JSON.parse(body);
    if (!data.lighthouseResult) { console.log('API error:', JSON.stringify(data).slice(0,500)); return; }
    const cats = data.lighthouseResult.categories;
    const audits = data.lighthouseResult.audits;

    console.log('=== SCORES ===');
    Object.entries(cats).forEach(([k,v]) => console.log(k + ':', Math.round(v.score * 100)));

    console.log('\n=== CORE WEB VITALS ===');
    ['first-contentful-paint','largest-contentful-paint','total-blocking-time','cumulative-layout-shift','speed-index','interactive'].forEach(id => {
      if (audits[id]) console.log(audits[id].title + ':', audits[id].displayValue, '| score:', Math.round((audits[id].score||0)*100));
    });

    console.log('\n=== FAILED / NEEDS IMPROVEMENT ===');
    Object.values(audits)
      .filter(a => a.score !== null && a.score < 0.9 && a.scoreDisplayMode !== 'informative' && a.scoreDisplayMode !== 'notApplicable')
      .sort((a,b) => a.score - b.score)
      .forEach(a => console.log('[' + (a.score === 0 ? 'FAIL' : Math.round(a.score*100)) + ']', a.title, a.displayValue ? '— ' + a.displayValue : ''));
  });
}).on('error', e => console.error('Error:', e.message));
