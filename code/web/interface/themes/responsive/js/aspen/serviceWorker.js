//not bundled because we only want to include this if Aspen Mobile is turned on
//importScripts("https://www.gstatic.com/firebasejs/12.1.0/firebase-messaging-sw.js");
importScripts('https://www.gstatic.com/firebasejs/12.1.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/12.1.0/firebase-messaging-compat.js');
const CACHE_NAME = 'aspen-mobile';

const PRECACHE_ASSETS = [

];
//sample messaging structure: 
//{"notification": {
// 	"title":"Test push message from DevTools.",
// 	"body":"test body", 
// 	"path": "/myAccount/Home"}
//}
fetch("/API/SystemAPI?method=getFirebaseSettings").then(function (response) {
	return response.json();
}).then(function (data) {
	if(data.result?.success)
	{
		//do things for getting settings here. 
		console.log(data.result.settings);
		const firebaseConfig = data.result.settings;
		// Initialize Firebase
		const app = firebase.initializeApp(firebaseConfig);
		const messaging = firebase.messaging();

		console.log(messaging);
		messaging.onMessage(function(payload) {
			console.log('message recieved');
		});
		messaging.onBackgroundMessage(function(payload) {
			console.log('[firebase-messaging-sw.js] Received background message ', payload);
			// Customize notification here
			const notificationTitle = 'Background Message Title';
			const notificationOptions = {
			body: 'Background Message body.',
			icon: '/firebase-logo.png'
			};
		
			self.registration.showNotification(notificationTitle,
			notificationOptions);
		});
		console.log(messaging);
		console.log(self);
	}
});//TODO truncated add full thing later

self.addEventListener('install', event => {
	console.log("install fired");
	event.waitUntil((async () => {
		const cache = await caches.open(CACHE_NAME);
		cache.addAll(PRECACHE_ASSETS);
	})());
});

self.addEventListener('activate', event => {
	console.log("activate fired");
	event.waitUntil(self.clients.claim());
});
self.addEventListener('onmessage', event => {
	console.log("message:", event);
});
self.addEventListener('onMessage', event => {
	console.log("message?", event);
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

self.addEventListener('push', (event) => {
	event_json = event.data.json();
	notification = event_json.notification;
	path = event_json.data?.path;
	event.waitUntil(
		self.registration.showNotification(notification.title, {
			body: notification.body,
			data: {
				"path": path
			},
			icon: notification.image,
		})
	);
});

self.addEventListener('notificationclick', (event) => {
	console.log("notification clicked");
	console.log(event);
	event.notification.close();
	var fullPath = self.location.origin + event.notification.data.path;
	clients.openWindow(fullPath);
});
