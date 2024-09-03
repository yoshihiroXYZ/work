
$(function(){
    $('.left').on('click',function(){
      console.log(1);
      $($(this).data("target")).modal({});
    });
  });

const loading = document.querySelector('#loading');

window.addEventListener('load', () => {
  loading.classList.add('loaded');
});