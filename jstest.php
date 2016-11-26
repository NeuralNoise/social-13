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

	function dragOBJ(obj,evnt){
		function drag(evnt){
			if(!stop) {
				obj.style.top=(tX=xy(evnt,1)+oY-eY+'px')
				obj.style.left=(tY=xy(evnt)+oX-eX+'px')
			}
		}
		
		var oX=parseInt(obj.style.left)
		var oY=parseInt(obj.style.top)
		var eX=xy(evnt)
		var eY=xy(evnt,1)
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

<div id="container">
	<div id="box1">
	</div>
	<div id="box2">
	</div>
	<div class="clear"></div>
</div>

<style>
#box1, #box2{  
	position:relative;
	float:left;
	border:1px solid #333;
	width:100px;
	height:100px;
	background:#777;
}
#box1{
	left:20px;
}
.clear{
	clear:both;
}
</style>