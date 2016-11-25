<script>
	function $(v){ 
		return(document.getElementById(v)); 
	}
	
	function agent(v){
		return(Math.max(navigator.userAgent.toLowerCase().indexOf(v),0));
	}
	
	function xy(e,v) {
		return(v?(agent('msie')?event.clientY+document.body.scrollTop:e.pageY):(agent('msie')?event.clientX+document.body.scrollTop:e.pageX));
	}

	function dragOBJ(obj,e){
		function drag(e){
			if(!stop) {
				obj.style.top=(tX=xy(e,1)+oY-eY+'px')
				obj.style.left=(tY=xy(e)+oX-eX+'px')
			}
		}
		
		var oX=parseInt(obj.style.left)
		var oY=parseInt(obj.style.top)
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
		}

	}
</script>

<div style="position: relative; top:0; left: 0" onmousedown="dragOBJ(this,event);return false;">
	test
</div>