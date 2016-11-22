//Перетаскивание мышью
function agent(v){
	return(Math.max(navigator.userAgent.toLowerCase().indexOf(v),0));
}

function xy(e,v){
	return(v?(agent('msie')?event.clientY+document.body.scrollTop:e.pageY):(agent('msie')?event.clientX+document.body.scrollTop:e.pageX));
}

function dragOBJ(d,e) {
	e=window.event
	d=document.getElementById('window')
    function drag(e){
		if(!stop){
			d.style.top=(tX=xy(e,1)+oY-eY+'px')
			d.style.left=(tY=xy(e)+oX-eX+'px')
		}
	}

    var oX=parseInt(d.style.left)
	var oY=parseInt(d.style.top)
	var eX=xy(e)
	var eY=xy(e,1)
	var tX
	var tY
	var stop

    document.onmousemove=drag;
	document.onmouseup=function(){
		stop=1;
		document.onmousemove='';
		document.onmouseup='';
	};
}