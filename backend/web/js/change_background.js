function changeBackground(color) {
    document.body.style.background = color;
    document.getElementsByTagName('html')[0].style.background = color;
 }
 
 window.addEventListener("load",function() { changeBackground('white'); });