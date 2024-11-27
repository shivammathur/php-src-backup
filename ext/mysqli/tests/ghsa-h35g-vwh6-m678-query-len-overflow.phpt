--TEST--
GHSA-h35g-vwh6-m678 (mysqlnd leaks partial content of the heap - stmt row no space for the field)
--EXTENSIONS--
mysqli
--FILE--
<?php
require_once 'fake_server.inc';

$port = 33305;
$servername = "127.0.0.1";
$username = "root";
$password = "";

$process = run_fake_server_in_background('query_response_row_length_overflow', $port);
$process->wait();

$conn = new mysqli($servername, $username, $password, "", $port);

echo "[*] Query the fake server...\n";
$sql = "SELECT strval, strval FROM data";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        var_dump($row['strval']);
    }
}
$conn->close();

$process->terminate(true);

print "done!";
?>
--EXPECTF--
[*] Server started
[*] Connection established
[*] Sending - Server Greeting: 580000000a352e352e352d31302e352e31382d4d6172696144420003000000473e3f6047257c6700fef7080200ff81150000000000000f0000006c6b55463f49335f686c6431006d7973716c5f6e61746976655f70617373776f7264
[*] Received: 6900000185a21a00000000c0080000000000000000000000000000000000000000000000726f6f7400006d7973716c5f6e61746976655f70617373776f7264002c0c5f636c69656e745f6e616d65076d7973716c6e640c5f7365727665725f686f7374093132372e302e302e31
[*] Sending - Server OK: 0700000200000002000000
[*] Query the fake server...
[*] Received: 200000000353454c4543542073747276616c2c2073747276616c2046524f4d2064617461
[*] Sending - Malicious Query Response for data strval field [length overflow]: 01000001023200000203646566087068705f74657374046461746104646174610673747276616c0673747276616c0ce000c8000000fd01100000003200000303646566087068705f74657374046461746104646174610673747276616c0673747276616c0ce000c8000000fd011000000005000004fe000022000a0000050474657374fefefefefe05000006fe00002200

Warning: mysqli_result::fetch_assoc(): Malformed server packet. Field length pointing after end of packet in %s on line %A
[*] Received: 0100000001
[*] Server finished
done!
