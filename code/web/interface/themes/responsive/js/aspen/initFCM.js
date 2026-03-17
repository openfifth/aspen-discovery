import { initializeApp } from 'https://www.gstatic.com/firebasejs/12.1.0/firebase-app.js';
import { getMessaging } from "https://www.gstatic.com/firebasejs/12.1.0/firebase-messaging-sw.js";
import { getToken } from "https://www.gstatic.com/firebasejs/12.1.0/firebase-messaging.js";
import * as UAP from "../lib/ua-parser-min.js";
//import '../lib/apisauce.min.js';
export var appToken = "default";
export function initialize() {
	fetch("/API/SystemAPI?method=getFirebaseMessagingConfig").then(function (response) {
		return response.json();
	}).then(function (data) {
		if (Globals.loggedIn && data.result?.success) {
			//do things for getting settings here. 
			const firebaseConfig = data.result.settings;
			// Initialize Firebase
			const app = initializeApp(firebaseConfig);

			// Initialize Firebase Cloud Messaging and get a reference to the service
			const messaging = getMessaging(app);
			getToken(messaging, { vapidKey: firebaseConfig['vapidKey'] }).then((currentToken) => {
				if (currentToken) {
					appToken = currentToken;
					Notification.requestPermission().then((permission) => {
						if (permission === 'granted') {
							$(".grant-notification-permissions").hide();
							$(".notification-permission-controls").show();
							let parser = new UAParser(window.navigator.userAgent);
							let result = parser.getResult();
							console.log(result);
							let modelName = result.device.vendor ? result.device.vendor + " " : "";
							modelName += result.device.model ? result.device.model + " " : "";
							modelName += result.os.name ? result.os.name + " " : "";
							modelName += result.os.version ? result.os.version + " " : "";
							modelName += result.cpu.architecture ? result.cpu.architecture + " " : "";
							modelName += result.browser.name ? result.browser.name + " " : "";
							modelName ||= "Unknown ";
							modelName += "PWA";
							const postData = {
								"pushToken": currentToken,
								"deviceModel": modelName,
								"tokenType": "firebase"
							}

							fetch("/AspenPWA/AJAX?method=saveNotificationPushToken", {
								method: "POST",
								headers: {
									'Cache-Control': 'no-cache'
								},
								body: new URLSearchParams(postData)
							});
						}
					});
				} 
				else {
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