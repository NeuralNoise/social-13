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
			<button class="post_public">Опубликовать</button>
		</div>
		<div class="clear"><div>
	</div>
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
  
</script>