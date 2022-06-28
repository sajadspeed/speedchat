$(document).ready(()=>{
	navigator.getUserMedia(
		{ video: true, audio: true },
		stream => {
			console.log(stream.getVideoTracks());
		  const localVideo = document.getElementById("local-video");
		  const remoteVideo = document.getElementById("remote-video");
		  if (localVideo) {
			localVideo.srcObject = stream;
			remoteVideo.srcObject = stream;
		  }
		},
		error => {
		  console.warn(error.message);
		}
	);
})