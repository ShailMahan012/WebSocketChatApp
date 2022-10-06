var display_msg_div = document.getElementById("display_msg")
var msg_input = document.getElementById("msg")

var host = 'ws://127.0.0.1:8080/WebSocketChatApp/websockets.php';
var socket = new WebSocket(host);


function send() {
    var msg = msg_input.value;
}


socket.onopen = function(event) { 
    console.log("Connection Established!");
    socket.send("Message 1 from client");
    socket.send("Message 2 from client");
}
socket.onmessage = function(e) {
    console.log("MESSAGE: ");
    console.log(e.data);
};
    socket.onerror = function(e) {
    console.log(e);
}