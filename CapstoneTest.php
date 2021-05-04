<!DOCTYPE html>
<html>
<body>

<h2 id="demo"></h2>
<button style="font-size:24px;" onclick="uploadEx()">
	Upload Picture
	</button>
	</br><br>
	
<?php
	// This connects to a personal database used for testing. This connection file isn't included
  include('DatabaseConnection.php');
  $conn = OpenCon();
	$sql = "SELECT * FROM CapstoneGeofences";
  $result = $conn->query($sql);
	echo "<select id='GeofenceSelect' name='geofenceSelect' onchange='geofenceSelectChange()'>";
	
	// Gets the example geofences to select from and test distance calculations with location data
	$geofences = array();
	$num = 0;
	while( $row = $result->fetch_assoc() )
	{
		array_push( $geofences, array( $row["geofence_id"], $row["latitude"], $row["longitude"], $row["name"], $row["latestPicture"] ) );
		echo "<option value=" . $num . ">" . $row["name"] . "</option>";
		$num++;
	}
	echo "</select>";
	echo "<table><tr><td style='padding: 15px;'>";
	echo "<h2 id='lt'>Latitude: ";
	echo $geofences[0][1];
	echo "</h2>";
	echo "<h2 id='lg'>Longitude: ";
	echo $geofences[0][2];
	echo "</h2></td>";
	echo "<td style='padding: 15px;'><h2 id='pictureInfo'>Latest picture at: </h2><img id='pictureDisplay' style='width:auto;height:130px;'></td></tr><table>";
  
  CloseCon($conn);
	
	?>
  <h2 id="geo"></h2>
	</br><br>
  
<!-- Setup video streaming and canvas to place taken picture -->
<video id="camera--view" autoplay playsinline></video>
<canvas id="camera--sensor"></canvas>
<img src="//:0" alt="" id="camera--output">
<form method="post" accept-charset="utf-8" name="form1">
			<input name="hidden_data" id='hidden_data' type="hidden"/>
			<input name="hidden_name" id='hidden_name' type="hidden"/>
		</form>

<script>
	
// Variables to easily change displays
var x = document.getElementById("demo");
var geo = document.getElementById("geo");
var lt = document.getElementById("lt");
var lg = document.getElementById("lg");
var pi = document.getElementById("pictureInfo");
var pd = document.getElementById("pictureDisplay");
var geofences = <?php echo json_encode( $geofences ); ?>;
var insideCurrentGeofence = false;
	
// Get location of device
if (navigator.geolocation) {
    navigator.geolocation.watchPosition(showPosition);
  } else { 
    x.innerHTML = "Geolocation is not supported by this browser.";
  }
	
// When you select a different geofence it updates the displayed information
function geofenceSelectChange()
{
	lt.innerHTML = "Latitude: " + geofences[document.getElementById("GeofenceSelect").value][1];
	lg.innerHTML = "Longitude: " + geofences[document.getElementById("GeofenceSelect").value][2];
	pi.innerHTML = "Last picture: ";
	
	// This file location is specific to the test setup and will be different on other websites
	pd.src = "CapstoneImages/" + geofences[document.getElementById("GeofenceSelect").value][3] + ".png";
}

// Function to check position called by navigator.geolocation.watchPosition()
function showPosition(position) {
  var lat = Math.round(position.coords.latitude*1000000)/1000000;
  var lon = Math.round(position.coords.longitude*1000000)/1000000;
  x.innerHTML = "Latitude: " + lat + 
  "<br>Longitude: " + lon;
  
	checkAllGeofences( lat, lon );
}
	
function isInGeoFence( lat, lon, c1, c2, c3, c4 )
{
	return ( lat < c1 && lat > c2 && lon < c3 && lon > c4 );
}
	
// Calcuate whether the position is close enough to the geofence measured in meters
function isInGeoFenceRange( lat, lon, targetLat, targetLon, radius )
{
	const R = 6371e3; // metres
	const c1 = lat * Math.PI/180; // φ, λ in radians
	const c2 = lat * Math.PI/180;
	const dc = (targetLat-lat) * Math.PI/180;
	const dr = (targetLon-lon) * Math.PI/180;

	const a = Math.sin(dc/2) * Math.sin(dc/2) +
						Math.cos(c1) * Math.cos(c2) *
						Math.sin(dr/2) * Math.sin(dr/2);
	const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

	const d = R * c; // in metres
	return ( d < radius );
}
	
// Function to check all available geofences
function checkAllGeofences( lat, lon )
{
		for( let i = 0; i < geofences.length; i++ )
		{
			if( isInGeoFenceRange( lat, lon, geofences[i][1], geofences[i][2], 500 ) )
			{
				 alert( "Inside " + geofences[i][3] );
			}
		}
}
	
/* Upload picture into website files from canvas if in the current geofence
   Right now this is connected to a button press, but for real use it would be connected to if the checkAllGeofences() found that it was in range
*/
function uploadEx() {
	if( insideCurrentGeofence )
	{
				var canvas = document.getElementById("camera--sensor");
				var dataURL = canvas.toDataURL("image/png");
				document.getElementById('hidden_data').value = dataURL;
				document.getElementById('hidden_name').value = geofences[document.getElementById("GeofenceSelect").value][3];
				var fd = new FormData(document.forms["form1"]);

				var xhr = new XMLHttpRequest();
		
				xhr.open('POST', 'CapstoneUploadPicture.php', true);

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

// Set to take a picture from the streaming video every 3 seconds and put it in the canvas
setInterval(function() {
    cameraSensor.width = cameraView.videoWidth;
    cameraSensor.height = cameraView.videoHeight;
    cameraSensor.getContext("2d").drawImage(cameraView, 0, 0);
}, 3000);
  
// Start the video stream when the window loads
window.addEventListener("load", cameraStart, false);
  
</script>

</body>
</html>