document.querySelectorAll('[data-confirm]').forEach(el=>el.addEventListener('click',e=>{if(!confirm(el.dataset.confirm)){e.preventDefault();}}));
document.querySelectorAll('[data-naira]').forEach(el=>{const format=()=>{const n=Number(el.value);el.dataset.validAmount=Number.isFinite(n)&&n>=0?'1':'0';};el.addEventListener('input',format);});
