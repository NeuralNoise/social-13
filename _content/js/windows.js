/*Автор: Домышев Илья*/

//Так выглядела строчка описывающая div окна. Сейчас уже не знаю, зачем все эти события были нужны. Работает и без них.
//<div id='window' class='windows' onMouseOver='add_form_over=true;' onMouseOut='add_form_out=true;' onClick="event.stopPropagation();">

//---Окно 1---
if(window_close=document.getElementById('window_close')){
	set_active=function(){
		window_close.style.background="url(/_content/img/mac_close_active.png)"
	}
	set_passive=function(){
		window_close.style.background="url(/_content/img/mac_close_passive.png)"
	}
	close_window=function(){
		document.getElementById('window').style.display='none'
	}
	window_close.addEventListener("mouseover", set_active, false)
	window_close.addEventListener("mouseout", set_passive, false)
	window_close.addEventListener("click", close_window, false)
}

//---Окно 2---
if(window_close2=document.getElementById('window_close2')){
	set_active2=function(){
		window_close2.style.background="url(/_content/img/mac_close_active.png)"
	}
	set_passive2=function(){
		window_close2.style.background="url(/_content/img/mac_close_passive.png)"
	}
	close_window2=function(){
		document.getElementById('window2').style.display='none'
	}
	window_close2.addEventListener("mouseover", set_active2, false)
	window_close2.addEventListener("mouseout", set_passive2, false)
	window_close2.addEventListener("click", close_window2, false)
}

