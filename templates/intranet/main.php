<div class="post">
	<img class="post_user_logo" src="/images/ilya_logo.jpg" />
	<div class="post_form" contenteditable="true">
		Test message
	</div>
	<div id="post_photos_preview" class="post_photos_preview">
		
	</div>
	<div class="post_foot">
		<div class="post_elements">
			<div class="photik">
				<input type='file' id="files" name="files[]" class="photik" value="" multiple />
			</div>
		</div>
		<div class="post_buttons">
			<button class="post_public">������������</button>
		</div>
		<div class="clear"><div>
	</div>
	



<script>
  var post_photo_thumbnail=0
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
			if(window.post_photo_thumbnail==0){
				//Create clear box
				div_clear = document.createElement('div');
				div_clear.id="div_photo_clear";
				div_clear.className="clear";
				document.getElementById('post_photos_preview').insertBefore(div_clear, null);

				//Create plusik box
				div_plusik = document.createElement('div');
				div_plusik.id="post_preview_plusik";
				div_plusik.className="post_preview_plusik";
				div_plusik.innerHTML= '<input type="file" id="filesnew" name="files[]" class="photik photik_big" value="" multiple />'
				document.getElementById('post_photos_preview').insertBefore(div_plusik, div_clear);
				document.getElementById('filesnew').addEventListener('change', handleFileSelect, false);
			}
			
			var div = document.createElement('div');
			div.className='post_photo_preview';
			div.setAttribute('draggable', true);
			div.innerHTML = ['<img class="post_photo_preview" src="', e.target.result,
							'" title="', escape(theFile.name), '"/>'].join('');
			document.getElementById('post_photos_preview').style="border-top:1px solid #CCC;";
			document.getElementById('post_photos_preview').insertBefore(div, document.getElementById('post_preview_plusik'));
			
			//Increase number of thumbnail
			window.post_photo_thumbnail=window.post_photo_thumbnail+1
        };
      })(f);

      // Read in the image file as a data URL.
      reader.readAsDataURL(f);
    }
  }

  document.getElementById('files').addEventListener('change', handleFileSelect, false);
  
  
  
//Drag boxes



function handleDragStart(e) {
  this.style.opacity = '0.4';  // this / e.target is the source node.
}

var cols = document.querySelectorAll('#post_photos_preview .post_photo_preview');
[].forEach.call(cols, function(col) {
  col.addEventListener('dragstart', handleDragStart, false);
});

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
  this.classList.add('over');
}

function handleDragLeave(e) {
  this.classList.remove('over');  // this / e.target is previous target element.
}

var cols = document.querySelectorAll('#columns .post_photo_preview');
[].forEach.call(cols, function(col) {
  col.addEventListener('dragstart', handleDragStart, false);
  col.addEventListener('dragenter', handleDragEnter, false);
  col.addEventListener('dragover', handleDragOver, false);
  col.addEventListener('dragleave', handleDragLeave, false);
});

//}


function handleDrop(e) {
  // this / e.target is current target element.

  if (e.stopPropagation) {
    e.stopPropagation(); // stops the browser from redirecting.
  }

  // See the section on the DataTransfer object.

  return false;
}

function handleDragEnd(e) {
  // this/e.target is the source node.

  [].forEach.call(cols, function (col) {
    col.classList.remove('over');
  });
}

var cols = document.querySelectorAll('#columns .post_photo_preview');
[].forEach.call(cols, function(col) {
  col.addEventListener('dragstart', handleDragStart, false);
  col.addEventListener('dragenter', handleDragEnter, false)
  col.addEventListener('dragover', handleDragOver, false);
  col.addEventListener('dragleave', handleDragLeave, false);
  col.addEventListener('drop', handleDrop, false);
  col.addEventListener('dragend', handleDragEnd, false);
});


//Drag transfer{
var dragSrcEl = null;

function handleDragStart(e) {
  // Target (this) element is the source node.
  this.style.opacity = '0.4';

  dragSrcEl = this;

  e.dataTransfer.effectAllowed = 'move';
  e.dataTransfer.setData('text/html', this.innerHTML); 
}


function handleDrop(e) {
  // this/e.target is current target element.

  if (e.stopPropagation) {
    e.stopPropagation(); // Stops some browsers from redirecting.
  }

  // Don't do anything if dropping the same column we're dragging.
  if (dragSrcEl != this) {
    // Set the source column's HTML to the HTML of the columnwe dropped on.
    dragSrcEl.innerHTML = this.innerHTML;
    this.innerHTML = e.dataTransfer.getData('text/html');
  }

  return false;
}	
//}

 
</script>