let startTime, endTime;
let imageSize = "";
let image = new Image();
let bitOutput = document.getElementById("bits");
let kboutput = document.getElementById("kbs");
let mboutput = document.getElementById("mbs");

//Gets random image from unsplash.com
let imageLink = "https://source.unsplash.com/random?topics=nature";
function checkInternet(){	
	//When image loads
	image.onload = async function () {
	  endTime = new Date().getTime();
	  //Get image size
	  await fetch(imageLink).then((response) => {
		imageSize = response.headers.get("content-length");
		calculateSpeed();
	  });
	};
}
//Function to calculate speed
function calculateSpeed() {
  //Time taken in seconds
  let timeDuration = (endTime - startTime) / 1000;
  //total bots
  let loadedBits = imageSize * 8;
  let speedInBps = (loadedBits / timeDuration).toFixed(2);
  let speedInKbps = (speedInBps / 1024).toFixed(2);
  let speedInMbps = (speedInKbps / 1024).toFixed(2);
	if(speedInKbps<5){
	   $('.offline-background').delay(10).fadeIn('slow');
	  }else{
	   $('.offline-background').delay(10).fadeOut('slow');
	}
	console.log(speedInKbps);
}
const initCheckInternet = async () => {
	if(navigator.onLine==false){
	   $('.offline-background').delay(10).fadeIn('slow');
	}else{
	   startTime = new Date().getTime();
	   image.src = imageLink;
	   checkInternet();
	}
};