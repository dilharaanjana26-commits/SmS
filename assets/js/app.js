document.querySelectorAll('[data-search-target]').forEach((input)=>{
  input.addEventListener('input',()=>{
    const target=document.querySelector(input.dataset.searchTarget);
    if(!target)return;
    const q=input.value.toLowerCase();
    target.querySelectorAll('tbody tr').forEach((tr)=>{
      tr.style.display=tr.innerText.toLowerCase().includes(q)?'':'none';
    });
  });
});
