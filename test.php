<script>
function $(v) { return(document.getElementById(v)); }
function agent(v) { return(Math.max(navigator.userAgent.toLowerCase().indexOf(v),0)); }
function xy(e,v) { return(v?(agent('msie')?event.clientY+document.body.scrollTop:e.pageY):(agent('msie')?event.clientX+document.body.scrollTop:e.pageX)); }

function dragOBJ(d,e) {

    function drag(e) { if(!stop) { d.style.top=(tX=xy(e,1)+oY-eY+'px'); d.style.left=(tY=xy(e)+oX-eX+'px'); } }

    var oX=parseInt(d.style.left),oY=parseInt(d.style.top),eX=xy(e),eY=xy(e,1),tX,tY,stop;

    document.onmousemove=drag; document.onmouseup=function(){ stop=1; document.onmousemove=''; document.onmouseup=''; };

}
</script>
<div style="position: relative; top: 0; left: 0" onmousedown="dragOBJ(this,event); return false;">test</div>