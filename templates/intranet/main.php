<div class="post">
	<img class="post_user_logo" src="/images/ilya_logo.jpg" />
	<div id="post_form" class="post_form" contenteditable="true">Что у вас нового?</div>
	<div id="post_photos_preview" class="post_photos_preview"></div>
	<div class="post_foot">
		<div class="post_elements">
			<div class="photik">
				<input type='file' id="files" name="files[]" class="photik" value="" multiple />
			</div>
		</div>
		<div class="post_buttons">
			<button class="post_public">Опубликовать</button>
		</div>
		<div class="clear"><div>
	</div>
<!--<div>------------</div>
<a class='krestik'></a>-->
<script>
var post_photo_thumbnail=1
var max_photo_thumbnail=4
var post_form_focus=false
var blackouts
const TXT_EMTY_MESSAGE='Что у вас нового?'
function handleFileSelect(evt) {
	var files = evt.target.files; // FileList object

    // Loop through the FileList and render image files as thumbnails. 
    for (var i = 0, f; f = files[i]; i++) {

      // Only process image files.
      if (!f.type.match('image.*')) {
        continue;
      }

      var reader = new FileReader();

      // Closure to capture the file information.
      reader.onload = (function(theFile){
			return function(e) {
				if(window.post_photo_thumbnail==1){
					//Create clear box
					div_clear = document.createElement('div');
					div_clear.id="div_photo_clear";
					div_clear.className="clear";
					
					//Insert HTML
					post_photos_preview=document.getElementById('post_photos_preview')
					post_photos_preview.insertBefore(div_clear, null)
					window.post_photos_preview=post_photos_preview
					window.div_clear=div_clear
					
					//Create plusik box and handler
					add_plusik()
				}
				
				if(window.post_photo_thumbnail<=window.max_photo_thumbnail){
					//Build blackout
					blackout=document.createElement('div')
					blackout.id='blackout'+window.post_photo_thumbnail.toString()
					blackout.className='blackout'
					
					//Build krestik
					krestik=document.createElement('a')
					krestik.className='krestik'
					krestik.id='krestik'+window.post_photo_thumbnail.toString()
			
					//Create photo preview
					photo_preview = document.createElement('div')
					photo_preview.id='photo_preview'+window.post_photo_thumbnail.toString()
					photo_preview.className='post_photo_preview'
					photo_preview.setAttribute('draggable', true)
					photo_preview.innerHTML = ['<img class="post_photo_preview" src="', e.target.result,
									'" title="', escape(theFile.name), '"/>'].join('');
					post_photos_preview.style="border-top:1px solid #CCC;"						
					
					//Define before which element I should add a child
					if(window.post_photo_thumbnail<window.max_photo_thumbnail){
						add_before_id='post_preview_plusik'
					}else{
						add_before_id='div_photo_clear'
						post_photos_preview.removeChild(document.getElementById('post_preview_plusik'))
					}
					
					//Set blackout event and krestik events
					set_blackout_event(photo_preview, blackout)
					set_krestik_event(krestik)
					
					//Insert html
					post_photos_preview.insertBefore(photo_preview, document.getElementById(add_before_id))
					photo_preview.insertBefore(blackout, photo_preview.firstChild)
					blackout.insertBefore(krestik, blackout.firstChild)
					
					//Set krestik 'onclick' event
					set_krestik_onclick_event(krestik, photo_preview)
					
					//Increase number of thumbnail
					window.post_photo_thumbnail=window.post_photo_thumbnail+1
					
					//Init drag boxes
					initDrag(photo_preview)
				}
	
								
			};
	  })(f);

      // Read in the image file as a data URL.
      reader.readAsDataURL(f);
    }
}

//Handle uploading files
document.getElementById('files').addEventListener('change', handleFileSelect, false);

//Start: Handle post form focus and blur
document.getElementById('post_form').addEventListener('focus', function(){
	if(window.post_form_focus==false || this.innerHTML==TXT_EMTY_MESSAGE){
		this.innerHTML=''
		window.post_form_focus=true
	}
}, false);
document.getElementById('post_form').addEventListener('blur', function(){
	if(this.innerHTML==''){this.innerHTML=TXT_EMTY_MESSAGE}
}, false);
//End: Handle post form focus and blur

//Start: Protect from unintentional page closing
window.onbeforeunload = function (evt) {
	var message = "Document 'foo' is not saved. You will lost the changes if you leave the page.";
	if (typeof evt == "undefined") {
		evt = window.event;
	}
	if (evt) {
		evt.returnValue = message;
	}
	return message;
}
//End: Protect from unintentional page closing 
  
var dragSrcEl = null;
function initDrag(col){
	col.addEventListener('dragstart', handleDragStart, false)
	col.addEventListener('dragenter', handleDragEnter, false)
	col.addEventListener('dragover', handleDragOver, false)
	col.addEventListener('dragleave', handleDragLeave, false)
	col.addEventListener('drop', handleDrop, false)
	col.addEventListener('dragend', handleDragEnd, false)
}

function handleDragStart(e) {
	// Target (this) element is the source node.
	dragSrcEl = this;

	//Set type of transfer
	e.dataTransfer.effectAllowed = 'move';

	//Set transported content and its type
	e.dataTransfer.setData('text/html', this.innerHTML);


}

//Punktir border over{
function handleDragOver(e) {
  if (e.preventDefault) {
    e.preventDefault(); // Necessary. Allows us to drop.
  }

  e.dataTransfer.dropEffect = 'move';  // See the section on the DataTransfer object.

  return false;
}

function handleDragEnter(e) {
  // this / e.target is the current hover target.
  //this.classList.add('over');
}

function handleDragLeave(e) {
  //this.classList.remove('over');  // this / e.target is previous target element.
  
}
//}

function handleDrop(e) {
	if (e.stopPropagation) {
		e.stopPropagation(); // Stops some browsers from redirecting.
	}

	//Don't do anything if dropping the same DIV we're dragging
	if (dragSrcEl != this){
		//Set the source DIV HTML to the HTML of the DIV dropped on
		dragSrcEl.innerHTML = this.innerHTML;
		this.innerHTML = e.dataTransfer.getData('text/html');

		//Set blackouts events
		var i
		for(i=1;i<=window.max_photo_thumbnail;i++){
			var blackout=document.getElementById('blackout'+i.toString())
			set_blackout_event(blackout.parentElement, blackout)
			set_krestik_event(blackout.childNodes[0])
			set_krestik_onclick_event(blackout.childNodes[0], blackout.parentElement)
		}
	}
	
  return false;
}

function handleDragEnd(e){
  [].forEach.call(cols, function (col){
    col.classList.remove('over');
  });
}

//Set a blackout event
function set_blackout_event(obje, obja){
	obje.onmouseover=function(){
		obja.style.display='block'
	}
	obje.onmouseout=function(){
		obja.style.display='none'
	}
}
//Set a krestik event
function set_krestik_event(obj){
	obj.onmouseover=function(){
		obj.style.backgroundPosition='-405px -220px'
	}
	obj.onmouseout=function(){
		obj.style.backgroundPosition='-392px -220px'
	}
}

function set_krestik_onclick_event(krestik, photo_preview1){
	krestik.onclick=function(){
		photo_preview1.remove()
		if(window.post_photo_thumbnail==window.max_photo_thumbnail+1){
			add_plusik()
		}
		window.post_photo_thumbnail=window.post_photo_thumbnail-1
		
	}
}
function add_plusik(){
	div_plusik = document.createElement('div');
	div_plusik.id="post_preview_plusik";
	div_plusik.className="post_preview_plusik";
	div_plusik.innerHTML= '<input type="file" id="filesnew" name="files[]" class="photik photik_big" value="" multiple />'
	window.post_photos_preview.insertBefore(div_plusik, window.div_clear)
	document.getElementById('filesnew').addEventListener('change', handleFileSelect, false);
}
</script>