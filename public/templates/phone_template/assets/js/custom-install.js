(function($){
  $(function(){

$(document).ready(function(){

	if (window.matchMedia('(display-mode: standalone)').matches) {  
		$('div#install').css("display","none");
	}else{
		 
		setTimeout(function () {
			$('div#install').delay(10).fadeOut('slow');
		}, 10000);
	}
	
	
	
});
  }); // end of document ready
})(jQuery);

/* Checks if the required feature is supported */
function isSupported(apiStr, apiParent) {
  if (apiStr in apiParent) {
	 
    return true;
  } 
  const divNotSupported = document.getElementById('notSupported');
  divNotSupported.classList.toggle('hidden', false);
  divNotSupported.querySelector('code').textContent = apiStr;
  return false;
}

/* Custom Install */ 
let installSource;
let deferredPrompt;
const btnAdd = document.getElementById('butInstall');
const divInstallStatus = document.getElementById('installAvailable');

// Handle install available
window.addEventListener('beforeinstallprompt', (e) => {
	
	setTimeout(function () {
		$('div#install').delay(10).fadeIn('slow');
		}, 2000);
		
  // Prevent Chrome 67 and earlier from automatically showing the prompt
  e.preventDefault();
  // Show install promo and set global deferredPrompt to `e`
  showInstallPromo(e);
  // Log to console
  // console.log('INSTALL: Available');
});

// Handle user request to install
btnAdd.addEventListener('click', (e) => {
  // Log to console
  console.log('INSTALL: Clicked');
  // hide our user interface that shows our A2HS button
  btnAdd.setAttribute('disabled', true);
  $('div#install').css("display","none");
  // Set the install source to this button
  installSource = 'butAdd';
  // Show the prompt
  deferredPrompt.prompt();
  // Wait for the user to respond to the prompt
  deferredPrompt.userChoice.then((resp) => {
    divInstallStatus.textContent = JSON.stringify(resp);
    console.log('INSTALL_PROMPT_RESPONSE:', resp);
    // If the user dismissed the prompt, clear the installSource
    if (resp.outcome === 'dismissed') {
      installSource = null;
    }
  });
});

// Log install event for analytics.
window.addEventListener('appinstalled', (evt) => {
	$('div#install').css("display","none");
  hideInstallPromo();
  
  deferredPrompt = null;
  if (document.hidden) {
    return;
  }
  
  const source = installSource ? installSource : 'browser';
  console.log(`INSTALL: Success (${source})`);
});

function showInstallPromo(e) {
  // Stash the event so it can be triggered later.
  deferredPrompt = e;
  divInstallStatus.textContent = 'true';
  // Update UI notify the user they can add to home screen
  btnAdd.removeAttribute('disabled');  
}

function hideInstallPromo() {
  divInstallStatus.textContent = 'false';
  $('div#install').css("display","none");
  // Update UI to disable to install promo
  btnAdd.setAttribute('disabled', 'disabled'); 
}
