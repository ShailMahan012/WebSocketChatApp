<?php
    $address = "127.0.0.1";
    $port = "8080";
    $conn_num = 0;
    $server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    socket_set_option($server, SOL_SOCKET, SO_REUSEADDR, 1);

    socket_bind($server, $address, $port);
    socket_listen($server);

    while (true) {
        $client = socket_accept($server);
        sleep(3);
        echo "CONNECTION: " . $conn_num . "\n";

        // Send WebSocket handshake headers.
        $request = socket_read($client, 5000);
        preg_match('#Sec-WebSocket-Key: (.*)\r\n#', $request, $matches);
        $key = base64_encode(pack(
            'H*',
            sha1($matches[1] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')
        ));
        $headers = "HTTP/1.1 101 Switching Protocols\r\n";
        $headers .= "Upgrade: websocket\r\n";
        $headers .= "Connection: Upgrade\r\n";
        $headers .= "Sec-WebSocket-Version: 13\r\n";
        $headers .= "Sec-WebSocket-Accept: $key\r\n\r\n";
        socket_write($client, $headers, strlen($headers));

        echo "Trying to receive data\n";
        // $res = socket_read($client, 5000, PHP_NORMAL_READ);
        socket_recv($client, $buff, 1024, 0);
        echo "->> " . unmask($buff) . "\n";
        socket_recv($client, $buff, 1024, 0);
        echo "->> " . unmask($buff) . "\n";
        echo "Data Received\n";
        $content = 'Now: ' . time();
        $response = chr(129) . chr(strlen($content)) . $content;
        
        // Send Data
        socket_write($client, $response);
        socket_write($client, $response);

        // Close Client Connection
        socket_close($client);
        echo "CONNECTION: " . $conn_num++ . " CLOSED!\n\n";
    }

    function unmask($text) {
        $length = ord($text[1]) & 127;
        if($length == 126) {
            $masks = substr($text, 4, 4);
            $data = substr($text, 8);
        }
        else if($length == 127) {
            $masks = substr($text, 10, 4);
            $data = substr($text, 14);
        }
        else {
            $masks = substr($text, 2, 4);
            $data = substr($text, 6);
        }
        $text = "";
        for ($i = 0; $i < strlen($data); ++$i) {
            $text .= $data[$i] ^ $masks[$i%4];
        }
        return $text;
}
?>