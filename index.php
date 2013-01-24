<!DOCTYPE html>
<html>
	<!-- Made by Jonathan Dallas, Pixel Farmer. In the year 2012 -->	
	
	<head>
		<title>Tim</title>
	   <link rel="stylesheet" type="text/css" href="style.css" />
		<!--[if lt IE 9]>
		<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
	</head>
	<body>
		<div id="main">
			<header>Topic Image Merger<img id="loader" src="loader.gif" style="display:none"></header>
			<form name="imgForm">
				<fieldset>
					<input type="text" name="img1" class="img_input" placeholder="First book">
					<label></label>
				</fieldset>

				<fieldset>
					<input type="text" name="img2" class="img_input" placeholder="Second Book">
					<label></label>
				</fieldset>
			
				<fieldset>
					<input type="text" name="img3" class="img_input" placeholder="Last Book">
					<label></label>
				</fieldset>
				
				<button id="submit" type="button">Done</button><a href="">Reset</a>
			
			</form>
			
			<aside>
				<img id="img1" height=260px title="First Book" src="" style="display:none">
				<img id="img2" height=260px alt="Second Book" src="" style="display:none">
				<img id="img3" height=260px alt="Last Book" src="" style="display:none">
				<img id="final_image" src="" style="display:none">
				<input id="final_url" type="text" style="display:none">
			</aside>

		</div>
		<script src="lib/jquery-1.8.3.min.js"></script>
		<script src="tim.js"></script>
		
	</body>
</html>