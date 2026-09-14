// Application-specific JavaScript will be added with approved modules.
(() => {
  document.querySelector('[data-app-sidebar]')?.addEventListener('click', () => document.querySelector('#app-sidebar')?.classList.toggle('show'));
  const chart = document.querySelector('#sales-chart');
  if (chart && window.Chart) new Chart(chart, { type: 'line', data: { labels: JSON.parse(chart.dataset.labels || '[]'), datasets: [{ data: JSON.parse(chart.dataset.values || '[]'), borderColor: '#6d3df5', backgroundColor: '#6d3df522', fill: true, tension: .35, pointRadius: 3 }] }, options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { callback: v => '₹' + v } } } } });
  const purchaseSelect = document.querySelector('#purchase-product');
  const purchaseAdd = document.querySelector('#add-purchase-line');
  const purchaseBody = document.querySelector('#purchase-lines');
  const purchaseEncoded = document.querySelector('#purchase-lines-json');
  if (purchaseSelect && purchaseAdd && purchaseBody && purchaseEncoded) {
    const lines = [];
    const draw = () => { purchaseBody.innerHTML = lines.map((x, i) => `<tr><td>${x.name}</td><td><input class="form-control form-control-sm pqty" data-i="${i}" type="number" step=".001" min=".001" value="${x.quantity}"></td><td><input class="form-control form-control-sm pprice" data-i="${i}" type="number" step=".01" min="0" value="${x.price}"></td><td class="text-end">₹${(x.quantity*x.price).toFixed(2)}</td><td><button type="button" class="btn btn-sm btn-outline-danger prem" data-i="${i}">×</button></td></tr>`).join(''); purchaseEncoded.value = JSON.stringify(lines.map(({id,quantity,price}) => ({id,quantity,price}))); };
    purchaseAdd.addEventListener('click', () => { const o=purchaseSelect.selectedOptions[0]; if(!o?.value)return; lines.push({id:Number(o.value),name:o.dataset.name,price:Number(o.dataset.price),quantity:1}); purchaseSelect.value='';draw(); });
    purchaseBody.addEventListener('input', e => { const x=lines[Number(e.target.dataset.i)];if(!x)return;if(e.target.classList.contains('pqty'))x.quantity=Math.max(.001,Number(e.target.value)||1);if(e.target.classList.contains('pprice'))x.price=Math.max(0,Number(e.target.value)||0);draw(); });
    purchaseBody.addEventListener('click', e => { if(e.target.classList.contains('prem')){lines.splice(Number(e.target.dataset.i),1);draw();} });
  }
  const select = document.querySelector('#item-select');
  const add = document.querySelector('#add-line');
  const body = document.querySelector('#lines');
  const total = document.querySelector('#total');
  const encoded = document.querySelector('#lines-json');
  const staff = document.querySelector('#line-staff');
  const discount = document.querySelector('#discount');
  const discountPreview = document.querySelector('#discount-preview');
  if (!select || !add || !body || !total || !encoded) return;
  const lines = [];
  const render = () => {
    let subtotal = 0;
    body.innerHTML = lines.map((line, i) => {
      const amount = line.quantity * line.price; subtotal += amount;
      return `<tr><td>${line.name}</td><td><input data-i="${i}" class="form-control form-control-sm qty" type="number" step=".001" min=".001" value="${line.quantity}"></td><td>₹${amount.toFixed(2)}</td><td><button type="button" data-i="${i}" class="btn btn-sm btn-outline-danger remove">×</button></td></tr>`;
    }).join('');
    const reduced = Math.min(Math.max(0, Number(discount?.value) || 0), subtotal);
    const tax = lines.reduce((value, line) => { const net = line.quantity * line.price; return value + ((net - (subtotal ? reduced * net / subtotal : 0)) * line.tax / 100); }, 0);
    total.textContent = (subtotal - reduced + tax).toFixed(2);
    if (discountPreview) discountPreview.textContent = reduced.toFixed(2);
    encoded.value = JSON.stringify(lines.map(({type,id,quantity,staff_id}) => ({type,id,quantity,staff_id})));
  };
  add.addEventListener('click', () => { const option = select.options[select.selectedIndex]; if (!option.value) return; const [type, id] = option.value.split(':'); lines.push({type, id:Number(id), name:option.dataset.name, price:Number(option.dataset.price), tax:Number(option.dataset.tax), quantity:1, staff_id:type === 'service' && staff?.value ? Number(staff.value) : undefined}); select.value=''; render(); });
  body.addEventListener('input', e => { if(e.target.classList.contains('qty')) { lines[Number(e.target.dataset.i)].quantity = Math.max(.001, Number(e.target.value)||1); render(); }});
  body.addEventListener('click', e => { if(e.target.classList.contains('remove')) { lines.splice(Number(e.target.dataset.i),1); render(); }});
  discount?.addEventListener('input', render);
})();
