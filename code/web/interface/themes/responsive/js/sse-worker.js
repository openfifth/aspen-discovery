// sse-worker.js
let eventSource = null;
const ports = new Set();

onconnect = function (e) {
  const port = e.ports[0];
  ports.add(port);

  port.onmessage = function (msg) {
    if (msg.data.action === "start") {
      if (!eventSource) {
        eventSource = new EventSource(msg.data.url);
        eventSource.addEventListener(msg.data.eventName, (event) => {
          // Broadcast raw string to all tabs
          ports.forEach((p) => p.postMessage(event.data));
        });
      }
    }
  };
  port.start();
};
