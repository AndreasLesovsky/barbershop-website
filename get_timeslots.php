<?php
require("includes/common.inc.php");
require("includes/db.inc.php");
require("includes/config.inc.php");

$conn = dbConnect();

if (isset($_GET['date'])) {
    header('Content-Type: application/json');

    $date = $_GET['date'];

    // Liste mit möglichen Zeitfenstern
    $timeslots = [
        '09:00:00', '09:30:00', '10:00:00', '10:30:00', '11:00:00',
        '11:30:00', '12:00:00', '12:30:00', '13:00:00', '13:30:00',
        '14:00:00', '14:30:00', '15:00:00', '15:30:00', '16:00:00',
        '16:30:00', '17:00:00', '17:30:00'
    ];

    // Vorbereitete Anweisung verwenden, um die gebuchten Slots zu erhalten
    $stmt = $conn->prepare("SELECT time, service_id FROM tbl_reservierungen WHERE date = ?");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();

    $booked_slots = [];
    while ($row = $result->fetch_assoc()) {
        $booked_slots[] = $row;
    }

    $response = ['timeslots' => []];

    // Array zur Verfolgung der gebuchten Slots
    $bookedTimes = [];
    foreach ($booked_slots as $booked) {
        $bookedTimes[$booked['time']] = $booked['service_id'];
    }

    foreach ($timeslots as $index => $slot) {
        $isBooked = isset($bookedTimes[$slot]);
        
        // Prüfe, ob es sich um eine Reservierung mit `service_id = 4` handelt
        if ($isBooked && $bookedTimes[$slot] == 4) {
            // Setze den aktuellen Slot als gebucht
            $response['timeslots'][] = [
                'time' => $slot,
                'booked' => true
            ];

            // Der nächste Slot, wenn er existiert
            if (isset($timeslots[$index + 1])) {
                $nextSlot = $timeslots[$index + 1];
                // Setze den nächsten Slot ebenfalls als gebucht
                $response['timeslots'][] = [
                    'time' => $nextSlot,
                    'booked' => true
                ];
            }

            // Überspringe den nächsten Slot im Loop, da er bereits hinzugefügt wurde
            continue;
        }

        // Füge den Status des aktuellen Slots hinzu, wenn kein Überspringen stattfindet
        $response['timeslots'][] = [
            'time' => $slot,
            'booked' => $isBooked
        ];
    }

    echo json_encode($response);
    exit;
}
?>
