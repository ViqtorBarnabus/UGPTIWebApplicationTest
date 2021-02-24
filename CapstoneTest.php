<!DOCTYPE html>
<html>
<body>
  
<!--<button onclick="getLocation()">Try It</button>-->

<h2 id="demo"></h2>
	
<!--<button style="font-size:24px;" onclick="changePhotos()" id="PhotoChange" value="Don't Upload Photos">
	Don't Upload Photos
	</button>
	</br><br>-->
<button style="font-size:24px;" onclick="uploadEx()">
	Upload Picture
	</button>
	</br><br>
	
<?php
  include('DatabaseConnection.php');
  $conn = OpenCon();
	$sql = "SELECT * FROM CapstoneGeofences";
  $result = $conn->query($sql);
	echo "<select id='GeofenceSelect' name='geofenceSelect' onchange='geofenceSelectChange()'>";
	$geofences = array();
	$num = 0;
	while( $row = $result->fetch_assoc() )
	{
		array_push( $geofences, array( $row["geofence_id"], $row["upperLat"], $row["lowerLat"], $row["upperLon"], $row["lowerLon"], $row["name"], $row["latestPicture"] ) );
		echo "<option value=" . $num . ">" . $row["name"] . "</option>";
		$num++;
	}
	echo "</select>";
	echo "<table><tr><td style='padding: 15px;'>";
	echo "<h2 id='ult'>Upper Latitude: ";
	echo $geofences[0][1];
	echo "</h2>";
	echo "<h2 id='llt'>Lower Latitude: ";
	echo $geofences[0][2];
	echo "</h2>";
	echo "<h2 id='ulg'>Upper Longitude: ";
	echo $geofences[0][3];
	echo "</h2>";
	echo "<h2 id='llg'>Lower Longitude: ";
	echo $geofences[0][4];
	echo "</h2></td>";
	echo "<td style='padding: 15px;'><h2 id='pictureInfo'>Latest picture at: </h2><img id='pictureDisplay' style='width:auto;height:130px;'></td></tr><table>";
  
  CloseCon($conn);
	
	?>
  <h2 id="geo"></h2>
	</br><br>
  
<!--<video id="video" width="640" height="480" autoplay></video>
<button id="snap">Snap Photo</button>
<canvas id="canvas" width="640" height="480"></canvas>-->
  
<video id="camera--view" autoplay playsinline></video>
<canvas id="camera--sensor"></canvas>
<img src="//:0" alt="" id="camera--output">
<form method="post" accept-charset="utf-8" name="form1">
			<input name="hidden_data" id='hidden_data' type="hidden"/>
			<input name="hidden_name" id='hidden_name' type="hidden"/>
		</form>
<!--<button id="camera--trigger">Take a picture</button>-->

<script>
var x = document.getElementById("demo");
var geo = document.getElementById("geo");
var ult = document.getElementById("ult");
var llt = document.getElementById("llt");
var ulg = document.getElementById("ulg");
var llg = document.getElementById("llg");
var pi = document.getElementById("pictureInfo");
var pd = document.getElementById("pictureDisplay");
var geofences = <?php echo json_encode( $geofences ); ?>;
var insideCurrentGeofence = false;
	
function changePhotos()
{
	/*if( document.getElementById("PhotoChange").value == "Don't Upload Photos" )
	{
		document.getElementById("PhotoChange").value = "Upload Photos";
		document.querySelector('#PhotoChange').innerHTML = 'Upload Photos';
	}
	else
	{
		document.getElementById("PhotoChange").value = "Don't Upload Photos";
		document.querySelector('#PhotoChange').innerHTML = "Don't Upload Photos";
	}*/
}
	
function geofenceSelectChange()
{
	ult.innerHTML = "Upper Latitude: " + geofences[document.getElementById("GeofenceSelect").value][1];
	llt.innerHTML = "Lower Latitude: " + geofences[document.getElementById("GeofenceSelect").value][2];
	ulg.innerHTML = "Upper Longitude: " + geofences[document.getElementById("GeofenceSelect").value][3];
	llg.innerHTML = "Lower Longitude: " + geofences[document.getElementById("GeofenceSelect").value][4];
	llg.innerHTML = "Lower Longitude: " + geofences[document.getElementById("GeofenceSelect").value][4];
	//pi.innerHTML = "Latest picture at: " + geofences[document.getElementById("GeofenceSelect").value][6];
	pi.innerHTML = "Last picture: ";
	pd.src = "CapstoneImages/" + geofences[document.getElementById("GeofenceSelect").value][5] + ".png";
}

/*function getLocation() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(showPosition);
  } else { 
    x.innerHTML = "Geolocation is not supported by this browser.";
  }
}*/

function showPosition(position) {
  var lat = Math.round(position.coords.latitude*1000000)/1000000;
  var lon = Math.round(position.coords.longitude*1000000)/1000000;
  //x.innerHTML = "Latitude: " + position.coords.latitude + 
  //"<br>Longitude: " + position.coords.longitude;
  x.innerHTML = "Latitude: " + lat + 
  "<br>Longitude: " + lon;
	
	var tulat = geofences[document.getElementById("GeofenceSelect").value][1];
	var tllat = geofences[document.getElementById("GeofenceSelect").value][2];
	var tulon = geofences[document.getElementById("GeofenceSelect").value][3];
	var tllon = geofences[document.getElementById("GeofenceSelect").value][4];
	
	if( isInGeoFence( lat, lon, tulat, tllat, tulon, tllon ) )
	{
		geo.innerHTML = "Currently inside this geofence";
		insideCurrentGeofence = true;
	}
	else
	{
		geo.innerHTML = "Currently outside this geofence";
		insideCurrentGeofence = false;
	}
  
}
	
function isInGeoFence( lat, lon, c1, c2, c3, c4 )
{
	return ( lat < c1 && lat > c2 && lon < c3 && lon > c4 );
}
	
function uploadEx() {
	if( insideCurrentGeofence )
	{
				var canvas = document.getElementById("camera--sensor");
				var dataURL = canvas.toDataURL("image/png");
				document.getElementById('hidden_data').value = dataURL;
				document.getElementById('hidden_name').value = geofences[document.getElementById("GeofenceSelect").value][5];
				var fd = new FormData(document.forms["form1"]);

				var xhr = new XMLHttpRequest();
				xhr.open('POST', 'CapstoneUploadPicture.php', true);

				/*xhr.upload.onprogress = function(e) {
					if (e.lengthComputable) {
						var percentComplete = (e.loaded / e.total) * 100;
						console.log(percentComplete + '% uploaded');
						alert('Succesfully uploaded');
					}
				};*/

				xhr.onload = function() {

				};
				xhr.send(fd);
		    alert( "Uploaded Picture" );
	}
	else
	{
		 alert( "Not in current geofence" );
	}
			};
  
//setInterval(getLocation(), 1000);
setInterval(function() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(showPosition);
  } else { 
    x.innerHTML = "Geolocation is not supported by this browser.";
  }
}, 1000);
  
// Grab elements, create settings, etc.
/*var video = document.getElementById('video');

// Get access to the camera!
if(navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
    // Not adding `{ audio: true }` since we only want video now
    navigator.mediaDevices.getUserMedia({ video: true }).then(function(stream) {
        //video.src = window.URL.createObjectURL(stream);
        video.srcObject = stream;
        video.play();
    });
}
  
// Elements for taking the snapshot
var canvas = document.getElementById('canvas');
var context = canvas.getContext('2d');
var video = document.getElementById('video');

// Trigger photo take
document.getElementById("snap").addEventListener("click", function() {
	context.drawImage(video, 0, 0, 640, 480);
  if(navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
    // Not adding `{ audio: true }` since we only want video now
    navigator.mediaDevices.getUserMedia({ video: true }).then(function(stream) {
        //video.src = window.URL.createObjectURL(stream);
        video.srcObject = stream;
        video.play();
    });
}
	//context.drawImage(video, 0, 0, 640, 480);
});*/

// Set constraints for the video stream
var constraints = { video: { facingMode: "environment" }, audio: false };
// Define constants
const cameraView = document.querySelector("#camera--view"),
    cameraOutput = document.querySelector("#camera--output"),
    cameraSensor = document.querySelector("#camera--sensor"),
    cameraTrigger = document.querySelector("#camera--trigger")
// Access the device camera and stream to cameraView
function cameraStart() {
    navigator.mediaDevices
        .getUserMedia(constraints)
        .then(function(stream) {
        track = stream.getTracks()[0];
        cameraView.srcObject = stream;
    })
    .catch(function(error) {
        console.error("Oops. Something is broken.", error);
    });
}
// Take a picture when cameraTrigger is tapped
/*cameraTrigger.onclick = function() {
    cameraSensor.width = cameraView.videoWidth;
    cameraSensor.height = cameraView.videoHeight;
    cameraSensor.getContext("2d").drawImage(cameraView, 0, 0);
    cameraOutput.src = cameraSensor.toDataURL("image/webp");
    cameraOutput.classList.add("taken");
};*/

setInterval(function() {
    cameraSensor.width = cameraView.videoWidth;
    cameraSensor.height = cameraView.videoHeight;
    cameraSensor.getContext("2d").drawImage(cameraView, 0, 0);
	
	  // If in geofence and uploading pictures is on and the curret picture has not been taken in a day then upload picture 
	  if( false )//insideCurrentGeofence && document.getElementById("PhotoChange").value == "Upload Photos" )
		{
	     /*var d = new Date();
	     var oD = new Date( geofences[document.getElementById("GeofenceSelect").value][6] );
       var seconds = Math.round(d.getTime() / 1000);
	     var takenTime = Math.round(oD.getTime() / 1000);
			 if( takenTime < ( seconds - 86400 ) )
			 {
					//uploadEx();
				 var dateCorrectFormat = d.getFullYear() + "-" + ( d.getMonth() + 1 ) + "-" + d.getDate() + " " + d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds();
				 //alert( dateCorrectFormat );
				 //alert( geofences[document.getElementById("GeofenceSelect").value][0] );
				 geofences[document.getElementById("GeofenceSelect").value][6] = dateCorrectFormat;
				 var instruction = "UPDATE CapstoneGeofences SET latestPicture='" + dateCorrectFormat + "' WHERE geofence_id=" + geofences[document.getElementById("GeofenceSelect").value][0];
         sendSQL(instruction);
			 }*/
		}
	
    //cameraOutput.src = cameraSensor.toDataURL("image/webp");
    //cameraOutput.classList.add("taken");
}, 3000);
  
// Start the video stream when the window loads
window.addEventListener("load", cameraStart, false);
  
</script>

</body>
</html>