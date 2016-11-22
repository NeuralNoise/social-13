<script>
	/*function photos_on_change(){
		var control = document.getElementById("post_photos");
		files = control.files;
		len = files.length;
		alert(len);
		//alert(files[0].name)
		alert(files[0].readAsDataURL)
	}*/
</script>

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
				<input type='file' id="files" name="files[]" class="photik" value="" onchange="photos_on_change();" multiple /> 
			</div>
		</div>
		<div class="post_buttons">
			<button class="post_public">Опубликовать</button>
		</div>
		<div class="clear"><div>
	</div>
</div>

<script>
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
      reader.onload = (function(theFile) {
        return function(e) {
          // Render thumbnail.
          var span = document.createElement('span');
          span.innerHTML = ['<img class="thumb" src="', e.target.result,
                            '" title="', escape(theFile.name), '"/>'].join('');
		  document.getElementById('post_photos_preview').style="border-top:1px solid #CCC;";
          document.getElementById('post_photos_preview').insertBefore(span, null);
        };
      })(f);

      // Read in the image file as a data URL.
      reader.readAsDataURL(f);
    }
  }

  document.getElementById('files').addEventListener('change', handleFileSelect, false);
</script>