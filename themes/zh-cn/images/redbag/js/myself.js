// JavaScript Document
window.onload=function(){
  var oflink = document.getElementById('sel');
  var aDt = oflink.getElementsByTagName('dt');
  var aUl = oflink.getElementsByTagName('ul');
  var aH3= oflink.getElementsByTagName('h3');
   
  for(var i=0;i<aDt.length;i++){
    aDt[i].index = i;
    aDt[i].onclick = function(ev){
      var ev = ev || window.event;
      for(var i=0;i<aUl.length;i++){
        aUl[i].style.display = 'none';
      }
           
      aUl[this.index].style.display = 'block';
      document.onclick = function(){
        aUl[This.index].style.display = 'none';
      };
      ev.cancelBubble = true;
    };
  }
  for(var i=0;i<aUl.length;i++){
    aUl[i].index = i;
    (function(ul){
      var iLi = ul.getElementsByTagName('li');
      for(var i=0;i<iLi.length;i++){
        iLi[i].onmouseover = function(){
          this.className = 'hover';
        };
        iLi[i].onmouseout = function(){
          this.className = '';
        };
        iLi[i].onclick = function(ev){
          var ev = ev || window.event;
          aH3[this.parentNode.index].innerHTML = this.innerHTML;
          ev.cancelBubble = true;
          this.parentNode.style.display = 'none';
        };
      }
    })(aUl[i]);
  }
}