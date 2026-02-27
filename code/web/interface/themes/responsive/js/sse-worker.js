// sse-worker.js
let eventSource = null;
const ports = new Set();

onconnect = function (e) {
  const port = e.ports[0];
  ports.add(port);

  port.onmessage = function (msg) {
    // Start the SSE only once
    if (msg.data.action === "start" && !eventSource) {
      eventSource = new EventSource(msg.data.url);
      eventSource.addEventListener(msg.data.eventName, (e) => {
        // Send the data to all open tabs
        ports.forEach((p) => p.postMessage(e.data));
      });
    }
  };
  port.start();
};
