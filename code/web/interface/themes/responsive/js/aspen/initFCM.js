import { initializeApp } from 'https://www.gstatic.com/firebasejs/12.1.0/firebase-app.js';
import { getMessaging } from "https://www.gstatic.com/firebasejs/12.1.0/firebase-messaging-sw.js";
import { getToken } from "https://www.gstatic.com/firebasejs/12.1.0/firebase-messaging.js";
import * as UAP from "../lib/ua-parser-min.js";
//import '../lib/apisauce.min.js';

function initialize() {
	fetch("/API/SystemAPI?method=getFirebaseSettings").then(function (response) {
		return response.json();
	}).then(function (data) {
		if (Globals.loggedIn && data.result?.success) {
			//do things for getting settings here. 
			console.log(data.result.settings);
			const firebaseConfig = data.result.settings;
			// Initialize Firebase
			const app = initializeApp(firebaseConfig);
			console.log(app);

			// Initialize Firebase Cloud Messaging and get a reference to the service
			const messaging = getMessaging(app);
			console.log(messaging);
			getToken(messaging, { vapidKey: firebaseConfig['vapidKey'] }).then((currentToken) => {
				console.log(currentToken);
				if (currentToken) {
					// TODO send the token to your server and update the UI if necessary
					//https://firebase.google.com/docs/cloud-messaging/js/first-message#web
					//https://console.firebase.google.com/project/aspen-pwa-test/notification/compose
					console.log(currentToken);
					Notification.requestPermission().then((permission) => {
						if (permission === 'granted') {
							console.log('Notification permission granted.');
							// TODO change this to use UserAPI savePushNotification instead
							//saveNotificationPushToken
							// how do we get the device?
							let parser = new UAParser(window.navigator.userAgent);
							let result = parser.getResult();
							let modelName = result.device.model || "Unknown";
							const postData = {
								"pushToken": currentToken,
								"deviceModel": modelName,
								"tokenType": "firebase"
							}

							fetch("/AspenMobile/AJAX?method=saveNotificationPushToken", {
								method: "POST",
								headers: {
									'Cache-Control': 'no-cache'
								},
								body: new URLSearchParams(postData)
							}).then(function (response) {
								console.log(response.json());
							});
						}
					});
				} else {
					//show permission request UI
					// QUESTION when do we get here? when is token falsey
					console.log('no registration token available. request permission to generate one.');
				}
			}).catch((err) => {
				console.log('an error occured while retrieving token. ', err);
			});
		}
		else {
			//we failed to get settings here. 
			console.log("We ran into a snag getting settings");
			console.log(data.result.error)
		}
	});
}

$(document).ready(function(){
	if(Globals.loggedIn)
	{
		initialize();
	}
});
self.addEventListener('fetch', event => {
	console.log("fetch...");
	console.log(event);
	event.respondWith(async () => {
		const cache = await caches.open(CACHE_NAME);

		// match the request to our cache
		const cachedResponse = await cache.match(event.request);

		// check if we got a valid response
		if (cachedResponse !== undefined) {
			// Cache hit, return the resource
			return cachedResponse;
		} else {
			// Otherwise, go to the network
			return fetch(event.request)
		};
	});
});