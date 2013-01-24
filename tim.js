$(function() {

	var img1_set;
	var img2_set;
	var img3_set;

	function spacing() {
		if (img1_set == false) { img1_width = 0; }
		else { img1_width = $('#img1').width();	}
		
		if (img2_set == false) { img2_width = 0; }
		else { img2_width = $('#img2').width(); }
		
		$('#img2').css('margin-left', img1_width);
		$('#img3').css('margin-left', img1_width+img2_width);
	}
	
	spacing();

	function imageImport(val, name) {
	  if (val.length > 0) { 
      // resets 
			$('#final_image').hide();
			$('[name='+name+']').siblings('label').text('');
		
			if (val.indexOf(".jpg") >= 0 || val.indexOf(".png") >= 0 || val.indexOf(".gif") >= 0 ||
			    val.indexOf(".JPG") >= 0 || val.indexOf(".PNG") >= 0 || val.indexOf(".GIF") >= 0) {
				$('#'+name).attr('src', val).show();
				switch(name) {
					case 'img1': img1_set = true; break;
					case 'img2': img2_set = true; break;
					case 'img3': img3_set = true; break;
					default: break; }
				spacing();
				ready();
			} else {
        $('[name='+name+']').siblings('label').text('No image detected');
				$('#'+name).hide();
				$('[name='+name+']').val('');
				spacing();
			}
		} else {
			$('#'+name).hide();
			switch(name) {
				case 'img1': img1_set = false; break;
				case 'img2': img2_set = false; break;
				case 'img3': img3_set = false; break;
				default: break; }
			spacing();
		}		
	};
  
  $('.img_input').change(function() {
   $(this).siblings('label').text('');
   var val = $(this).val();
   var name = $(this).attr('name');
   imageImport(val, name);
  });
  
   
	function ready() {
		if (img1_set && img2_set && img3_set) {
			submit_form();
		}
	}
	
	function show_final_image(result) {
		$('#img1, #img2, #img3').fadeOut(200);
		$('#final_image').show();
		$('#final_url').fadeIn(100).attr('value', result);
	}
	
	$('#submit').click(function () {
		submit_form();
	});


	function submit_form() {
		
		$('#loader').fadeIn(200);
		$('#submit').text('Working...').addClass('working');
		
		var img1 = $('input[name=\'img1\']').val();
		var img2 = $('input[name=\'img2\']').val();
		var img3 = $('input[name=\'img3\']').val();
		
		$.ajax({
			url: 'image_merge.php',
			type: 'GET',
			data: 'img1=' + img1 + '&img2=' + img2 + '&img3=' + img3,

			success: function(result) {
				$('#loader').fadeOut(200);
				$('#submit').text('Done').removeClass('working');
				
				if (result.indexOf(".jpg") >= 0) {
					$('#final_image').attr('src', result);
					$('#img2').animate({
							marginLeft: 30,
					  }, 600, 'swing');
					$('#img3').animate({
					 		marginLeft: 60,
					  }, 600, 'swing', function() {
							show_final_image(result);
					});
					
				} else if (result.indexOf("Bad original image size") >= 0 && $('aside p').length == 0) {
					$('aside').append('<p>Error: One or more of the Images could not be accessed.<br><a href=\"\">Reset form</a></p>')
				} else if ($('aside p').length == 0) {
					$('aside').append('<p>'+result+'<br><a href=\"\">Reset form</a></p>')
				}				
			}
			
		});
	}
	
	
  // DRAG AND DROP YO
  function handleURL(evt) {
    evt.stopPropagation();
    evt.preventDefault();

    // assume text
    var data = evt.dataTransfer.getData('text');
    if (data !== '') {
      url = data.toString();
    } else {
      // parse html
      data = evt.dataTransfer.getData('text/html');
      var html = data.split('img src=\"');
      var url = html[1].split('\"')[0];
    }
    
    if (img1_set && img2_set) {
      $('[name=img3]').val(url);
      imageImport(url, 'img3');
    } else if (img1_set) {
      $('[name=img2]').val(url);
      imageImport(url, 'img2');
    } else {
      $('[name=img1]').val(url);
      imageImport(url, 'img1');
    }
    
  }

  function handleDragOver(evt) {
    evt.stopPropagation();
    evt.preventDefault();
    evt.dataTransfer.dropEffect = 'copy';
  }

  document.addEventListener('dragover', handleDragOver, false);
  document.addEventListener('drop', handleURL, false);
	
	
});