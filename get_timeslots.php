<?php
require("includes/common.inc.php");
require("includes/db.inc.php");
require("includes/config.inc.php");

$conn = dbConnect();

if (isset($_GET['date'])) {
    header('Content-Type: application/json');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');

    $date = $_GET['date'];

    // Liste mit möglichen Zeitfenstern
    $timeslots = [
        '09:00:00', '09:30:00', '10:00:00', '10:30:00', '11:00:00',
        '11:30:00', '12:00:00', '12:30:00', '13:00:00', '13:30:00',
        '14:00:00', '14:30:00', '15:00:00', '15:30:00', '16:00:00',
        '16:30:00', '17:00:00', '17:30:00'
    ];

    // Vorbereitete Anweisung verwenden, um die gebuchten Slots zu erhalten
    $stmt = $conn->prepare("SELECT time FROM tbl_reservierungen WHERE date = ?");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();

    // Gebuchte Slots in einem Array speichern
    $booked_slots = [];
    while ($row = $result->fetch_assoc()) {
        $booked_slots[$row['time']] = true; // Zeit als Schlüssel speichern
    }

    $response = ['timeslots' => []];

    // Überprüfen, ob die Slots verfügbar oder gebucht sind
    foreach ($timeslots as $slot) {
        $response['timeslots'][] = [
            'time' => $slot,
            'booked' => isset($booked_slots[$slot]) // Überprüfung der Schlüssel
        ];
    }

    echo json_encode($response);
    exit;
}
?>