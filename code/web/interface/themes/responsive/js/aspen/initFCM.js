import { initializeApp } from 'https://www.gstatic.com/firebasejs/12.1.0/firebase-app.js';
import { getMessaging, getToken } from "https://www.gstatic.com/firebasejs/12.1.0/firebase-messaging.js";
import * as UAP from "../lib/ua-parser-min.js";

var appToken = "default";
function getModelName() {
	let parser = new UAParser(window.navigator.userAgent);
	let result = parser.getResult();
	let modelName = result.device.vendor ? result.device.vendor + " " : "";
	modelName += result.device.model ? result.device.model + " " : "";
	modelName += result.os.name ? result.os.name + " " : "";
	modelName += result.os.version ? result.os.version + " " : "";
	modelName += result.cpu.architecture ? result.cpu.architecture + " " : "";
	modelName += result.browser.name ? result.browser.name + " " : "";
	modelName ||= "Unknown ";
	modelName += "PWA";
	return modelName;
}
function initialize() {
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
			getToken(messaging).then((currentToken) => {
				if (currentToken) {
					appToken = currentToken;
					if (Notification.permission === 'granted') {
						let parser = new UAParser(window.navigator.userAgent);
						let result = parser.getResult();
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

function handleAllowNotifications() {
	var allow = $("#allowNotifications").is(":checked");
	if(allow)
	{
		Notification.requestPermission().then((permission) => {
			if(permission === 'granted')
			{
				initialize();
				$(".notification-permission-controls").show();
			}
		});
	} else {
		$(".notification-permission-controls").hide();
		const postData = {
			"pushToken": appToken
		}

		fetch("/AspenPWA/AJAX?method=deleteNotificationPushToken", {
			method: "POST",
			headers: {
				'Cache-Control': 'no-cache'
			},
			body: new URLSearchParams(postData)
		});
	}
}
function handleNotificationControls(type) {
	console.log(type + " :: " + $("#"+type).is(":checked"));
	console.log(appToken);
	let value = $("#"+type).is(":checked");
	let postData = {
		"pushToken": appToken,
		"type": type,
		"value": value
	};
	fetch("/AspenPWA/AJAX?method=setNotificationPreference", {
		method: "POST",
		headers: {
			'Cache-Control': 'no-cache'
		},
		body: new URLSearchParams(postData)
	}).then(function (response) {
		console.log(response.json());
	});
}

$(document).ready(function(){
	$(function(){ $('input[type="checkbox"][data-switch]').bootstrapSwitch()});
	$("#notifySavedSearch").on('switchChange.bootstrapSwitch', function(){handleNotificationControls('notifySavedSearch')});
	$("#notifyAccount").on('switchChange.bootstrapSwitch', function(){handleNotificationControls('notifyAccount')});
	$("#notifyCustom").on('switchChange.bootstrapSwitch', function(){handleNotificationControls('notifyCustom')});
	$("#allowNotifications").on('switchChange.bootstrapSwitch',handleAllowNotifications);

	let modelName = getModelName();
	let token = $('[data-device="'+modelName+'"]');
	let notifySavedSearch = token.data("notifySavedSearch");
	let notifyCustom = token.data("notifyCustom");
	let notifyAccount = token.data("notifyAccount");
	appToken = token.data("token");

	//if we dont have a token or dont have permissions we need
	//to request
	if(token.length && Notification.permission === "granted")
	{
		$("#allowNotifications").prop("checked", true).trigger("change");
	} else {
		$(".notification-permission-controls").hide();
	}

	$("#notifySavedSearch").prop("checked", notifySavedSearch).trigger("change");
	$("#notifyCustom").prop("checked", notifyCustom).trigger("change");
	$("#notifyAccount").prop("checked", notifyAccount).trigger("change");

	
});