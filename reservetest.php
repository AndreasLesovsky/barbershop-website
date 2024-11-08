<?php
require("includes/common.inc.php");
require("includes/db.inc.php");
require("includes/config.inc.php");
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$msgRes = '';
$msgCon = '';
$conn = dbConnect();

if (isset($_POST['reservationFormSubmitted'])) {
    // $secretKey = "6Lf0rm4qAAAAAMFjZzaJ91736aotC4ydgsZrv3hL";
    // $responseKey = $_POST['g-recaptcha-response'];
    // $userIP = $_SERVER['REMOTE_ADDR'];
    // $url = "https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$responseKey&remoteip=$userIP";
    // $response = file_get_contents($url);
    // $response = json_decode($response);

    // if ($response->success) {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $phone = htmlspecialchars($_POST['phone']);
    $service_id = htmlspecialchars($_POST['service_id']);
    $date = htmlspecialchars($_POST['date']);
    $time = htmlspecialchars($_POST['time']);

    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM tbl_reservierungen WHERE date = ? AND time = ?");
    $checkStmt->bind_param("ss", $date, $time);
    $checkStmt->execute();
    $checkStmt->store_result(); // Ergebnisse speichern
    $checkStmt->bind_result($count);
    $checkStmt->fetch();

    if ($count > 0) {
        $msgRes = "<p class='error'>Dieser Termin ist bereits gebucht.</p>";
    } else {
        // Führe die Einfügung durch
        if ($stmt = $conn->prepare("INSERT INTO tbl_reservierungen (name, email, phone, service_id, date, time) VALUES (?, ?, ?, ?, ?, ?)")) {
            $stmt->bind_param("sssiss", $name, $email, $phone, $service_id, $date, $time);
            if ($stmt->execute()) {
                // Erfolgreiche Ausführung
                // E-Mail versenden
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = '';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'test@test.at';
                    $mail->Password = 'xxxxxxxxxxxxxxxx';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;
                    $mail->CharSet = 'UTF-8';

                    $mail->setFrom('barbershop@andreas-lesovsky-web.dev', 'Barbershop KLM');
                    $mail->addAddress($email, $name);

                    $mail->isHTML(true);
                    $mail->Subject = 'Terminbestätigung';
                    $mail->Body    = "Hallo $name,<br><br>Ihr Termin für $service_id am $date um $time wurde erfolgreich gebucht!<br><br>Vielen Dank!";
                    $mail->AltBody = "Hallo $name,\n\nIhr Termin für $service_id am $date um $time wurde erfolgreich gebucht!\n\nVielen Dank!";

                    $mail->send();
                    $msgRes = "<p class='success'>Termin erfolgreich gebucht! Eine Bestätigungs E-Mail wurde gesendet.</p>";
                } catch (Exception $e) {
                    $msgRes = "<p class='success'>Termin erfolgreich gebucht, aber die Bestätigungs E-Mail konnte nicht gesendet werden. Fehler: {$mail->ErrorInfo}</p>";
                }
            } else {
                $msgRes = "Fehler: " . $stmt->error;
            }
        } else {
            $msgRes = "Fehler bei der Vorbereitung der Anfrage: " . $conn->error;
        }
    }

    // } else {
    //     $msgRes = "<p class='error'>Der Termin konnte nicht gebucht werden. Die reCaptcha Überprüfung ist fehlgeschlagen.</p>";
    // }
}

$conn->close();


// Prüfen, ob das Kontaktformular abgesendet wurde
if (isset($_POST['contactFormSubmitted'])) {
    $secretKey = "6Lf0rm4qAAAAAMFjZzaJ91736aotC4ydgsZrv3hL";
    $responseKey = $_POST['g-recaptcha-response'];
    $userIP = $_SERVER['REMOTE_ADDR'];
    $url = "https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$responseKey&remoteip=$userIP";
    $response = file_get_contents($url);
    $response = json_decode($response);

    if ($response->success) {
        // Erfolgreiche Validierung
        // Benutzereingaben sicher verarbeiten
        $name = htmlspecialchars(trim($_POST['Name']));
        $email = htmlspecialchars(trim($_POST['Email']));
        $message = htmlspecialchars(trim($_POST['Nachricht']));

        $mail = new PHPMailer(true);

        try {
            // SMTP-Server-Setup
            $mail->isSMTP();
            $mail->Host       = '';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'test@test.at';
            $mail->Password   = 'xxxxxxxxxxxxxx';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587; // Port für TLS

            // Absender
            $mail->setFrom('test@test.at', 'Barbershop');

            // Empfänger (die E-Mail wird an dich gesendet)
            $mail->addAddress('test@test.at', 'Your Name');

            // E-Mail-Inhalt
            $mail->isHTML(true);
            $mail->Subject = 'Neue Nachricht vom Barbershop Kontaktformular';
            $mail->Body    = "<p><strong>Name:</strong> $name</p>
                           <p><strong>E-Mail:</strong> $email</p>
                           <p><strong>Nachricht:</strong><br>$message</p>";
            $mail->AltBody = "Name: $name\nE-Mail: $email\nNachricht:\n$message";

            // E-Mail senden
            $mail->send();
            $msgCon = "<p class='success'>Vielen Dank! Ihre Nachricht wurde erfolgreich versendet.</p>";
        } catch (Exception $e) {
            $msgCon = "<p class='error'>Die Nachricht konnte nicht gesendet werden. Fehler: {$mail->ErrorInfo}</p>";
        }
    } else {
        // Validierung fehlgeschlagen
        $msgCon = "<p class='error'>Die Nachricht konnte nicht gesendet werden. Die reCaptcha Überprüfung ist fehlgeschlagen.</p>";
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminreservierung</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="css/importer.css">
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/config.css">
    <link rel="stylesheet" href="css/hover.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/media.css">
    <link rel="stylesheet" href="css/grid.css">

    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/de.js"></script>
    <style>
        .timeslot-active {
            background-color: #E5D5B3;
            /* Beispielhintergrundfarbe */
            color: white;
            /* Beispieltextfarbe */
            border: 2px solid #ffffff;
            /* Beispielrahmen */
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            <?php if ($msgRes): ?>
                const resMessageDiv = document.getElementById('reservation-message-container');
                if (resMessageDiv && resMessageDiv.childElementCount > 0) {
                    resMessageDiv.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            <?php endif; ?>
        });
    </script>
</head>

<body>
    <section id="reserve">
        <div class="grid">
            <h2 class="section-heading col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">Buchen Sie einen Termin</h2>
            <p class="section-sub-heading col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">Lassen Sie uns Ihren Termin vereinbaren.</p>

            <form id="reservationForm" class="grid col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12" method="POST">

                <div class="grid col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12">
                    <label for="name" class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">Name:</label>
                    <input type="text" id="name" class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12" placeholder="Max Mustemann" name="name" required>

                    <label for="email" class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">E-Mail:</label>
                    <input type="email" id="email" class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12" placeholder="max.mustermann@muster.at" name="email" required>

                    <label for="phone" class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">Telefonnummer:</label>
                    <input type="tel" id="phone" class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12" placeholder="+43 699 ... ... .." name="phone" required>
                </div>

                <div class="grid col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <label for="date" hidden>Datum:</label>
                    <input type="text" id="datepicker" class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12" name="date" required readonly hidden>
                    <label for="time" hidden>Uhrzeit:</label>
                    <input type="time" id="time" name="time" hidden>
                </div>

                <div id="timeSlots" class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12"></div>

                <div class="grid col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12">
                    <label for="selectedServiceId" class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">Dienstleistung:</label>
                    <div id="services" class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <button type="button" data-service-id="1">
                            <span class="service">Haarschnitt <span class="duration">Dauer: ca. 30min</span></span>
                            <span class="price">€18.00</span>
                        </button>
                        <button type="button" data-service-id="2">
                            <span class="service">Bart Styling <span class="duration">Dauer: ca. 30min</span></span>
                            <span class="price">€12.00</span>
                        </button>
                        <button type="button" data-service-id="3">
                            <span class="service">Kinder Haarschnitt (bis 12 Jahre) <span class="duration">Dauer: ca. 30min</span></span>
                            <span class="price">€12.00</span>
                        </button>
                        <button type="button" data-service-id="4">
                            <span class="service">Haarschnitt + Bart Styling <span class="duration">Dauer: ca. 60min</span></span>
                            <span class="price">€30.00</span>
                        </button>
                    </div>
                    <!-- Verstecktes Input-Feld für den Service-Wert -->
                    <input id="selectedServiceId" name="service_id">
                </div>


                <!-- <div class="g-recaptcha-container col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="g-recaptcha"
                        data-sitekey="6Lf0rm4qAAAAAII2mqKznWe97fKzzbaA8Q3_aC9M"></div> -->
        </div>
        <input type="hidden" name="reservationFormSubmitted" value="1">
        <button type="submit" class="btn-primary hvr-bounce-to-bottom col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px">
                <path d="M120-160v-640l760 320-760 320Zm80-120 474-200-474-200v140l240 60-240 60v140Zm0 0v-400 400Z" />
            </svg>
            Termin buchen
        </button>
        </form>
        <div id="reservation-message-container" class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <?php echo $msgRes; ?>
        </div>
        </div>



    </section>

    <script>
        // Terminreservierung
        function loadTimeSlots(dateStr) {
            fetch(`get_timeslots.php?date=${dateStr}`)
                .then((response) => {
                    if (!response.ok) {
                        throw new Error("Netzwerkantwort war nicht ok: " + response.statusText);
                    }
                    return response.json();
                })
                .then((data) => {
                    const timeSlotsDiv = document.getElementById("timeSlots");
                    timeSlotsDiv.innerHTML = ""; // Leeren, bevor neue Daten geladen werden

                    const serviceId = document.getElementById("selectedServiceId").value; // Service-ID abrufen
                    const serviceDuration = getServiceDuration(serviceId); // Dauer ermitteln

                    data.timeslots.forEach((slot, index) => {
                        const slotButton = document.createElement("button");
                        const [hours, minutes] = slot.time.split(":");
                        slotButton.textContent = `${hours}:${minutes}`;

                        // Standardmäßig nicht deaktiviert
                        slotButton.disabled = false;

                        // Deaktivieren, wenn der Slot besetzt ist
                        if (slot.booked) {
                            slotButton.disabled = true;
                        }

                        // Überprüfen, ob die Dienstleistung 60 Minuten dauert
                        if (serviceDuration === 60) {
                            // Deaktivieren der Slots, die nur ein freies Slot haben
                            if (index < data.timeslots.length - 1) {
                                const nextSlot = data.timeslots[index + 1];
                                // Aktuellen Slot deaktivieren, wenn der nächste besetzt ist
                                if (nextSlot.booked) {
                                    slotButton.disabled = true;
                                }
                            } else {
                                // Deaktivieren des letzten Slots (17:30) wenn es nur bis 18:00 Uhr geht
                                if (slot.time === "17:30:00") {
                                    slotButton.disabled = true;
                                }
                            }
                        }

                        // Event-Listener hinzufügen
                        slotButton.addEventListener("click", (event) => {
                            event.preventDefault();
                            const activeButton = timeSlotsDiv.querySelector(".timeslot-active");
                            if (activeButton) {
                                activeButton.classList.remove("timeslot-active");
                            }
                            slotButton.classList.add("timeslot-active");
                            document.getElementById("time").value = slot.time; // Setze den Originalwert
                        });

                        slotButton.setAttribute("data-time", slot.time); // Datenattribut für späteren Zugriff
                        timeSlotsDiv.appendChild(slotButton);
                    });
                })
                .catch((error) => {
                    console.error("Es gab ein Problem mit der Fetch-Anfrage:", error);
                });
        }





        function getServiceDuration(serviceId) {
            const services = {
                1: 30,
                2: 30,
                3: 30,
                4: 60 // Beispielhafte Dienste
            };
            return services[serviceId] || 0; // Gibt die Dauer des Dienstes zurück oder 0, wenn nicht gefunden
        }

        // Flatpickr initialisieren
        const today = new Date();
        const isSunday = today.getDay() === 0;
        if (isSunday) {
            today.setDate(today.getDate() + 1);
        }
        flatpickr("#datepicker", {
            locale: {
                firstDayOfWeek: 1,
                weekdays: {
                    shorthand: ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa"],
                    longhand: [
                        "Sonntag",
                        "Montag",
                        "Dienstag",
                        "Mittwoch",
                        "Donnerstag",
                        "Freitag",
                        "Samstag",
                    ],
                },
                months: {
                    shorthand: [
                        "Jan",
                        "Feb",
                        "Mär",
                        "Apr",
                        "Mai",
                        "Jun",
                        "Jul",
                        "Aug",
                        "Sep",
                        "Okt",
                        "Nov",
                        "Dez",
                    ],
                    longhand: [
                        "Januar",
                        "Februar",
                        "März",
                        "April",
                        "Mai",
                        "Juni",
                        "Juli",
                        "August",
                        "September",
                        "Oktober",
                        "November",
                        "Dezember",
                    ],
                },
            },
            inline: true,
            minDate: "today", // Setzt das minimale Datum auf das heutige Datum
            defaultDate: today, // Setzt das heutige Datum als Standarddatum
            disable: [
                function(date) {
                    return date.getDay() === 0; // Sonntag deaktivieren
                },
            ],
            onChange: function(selectedDates, dateStr) {
                loadTimeSlots(dateStr); // Neue Fetch-Anfrage bei Datumsauswahl
            },
        });

        // Initialen Fetch-Aufruf für heutiges Datum bei Seitenladung
        loadTimeSlots(today.toISOString().split("T")[0]); // Format: YYYY-MM-DD

        // Event Listener für Service-Buttons
        const serviceButtons = document.querySelectorAll("#services button");
        serviceButtons.forEach((button) => {
            button.addEventListener("click", () => {
                serviceButtons.forEach((btn) => btn.classList.remove("service-active"));
                button.classList.add("service-active");
                const serviceId = button.getAttribute('data-service-id');
                document.getElementById('selectedServiceId').value = serviceId;

                // Datum abrufen und Fetch nur für Service-Buttons ausführen
                
            });
        });

        document
            .getElementById("reservationForm")
            .addEventListener("submit", function(event) {
                const selectedService = document.getElementById("selectedService").value;
                const timeInput = document.getElementById("time").value; // Zeitfeld abrufen
                const resMessageDiv = document.getElementById(
                    "reservation-message-container"
                );

                // Fehlernachricht zurücksetzen
                resMessageDiv.style.display = "none";
                resMessageDiv.textContent = "";

                let errorMessage = "";

                if (!selectedService) {
                    errorMessage = "Bitte wählen Sie eine Dienstleistung aus. ";
                }
                if (!timeInput) {
                    errorMessage = "Bitte wählen Sie eine Uhrzeit aus.";
                }
                if (!timeInput && !selectedService) {
                    errorMessage =
                        "Bitte wählen Sie eine Dienstleistung und eine Uhrzeit aus.";
                }

                if (errorMessage) {
                    event.preventDefault(); // Verhindert das Absenden des Formulars
                    resMessageDiv.textContent = errorMessage.trim(); // Fehlernachricht setzen
                    resMessageDiv.style.display = "block"; // Fehlernachricht anzeigen
                    resMessageDiv.scrollIntoView({
                        behavior: "smooth",
                        block: "center"
                    });
                    resMessageDiv.classList.add("error");
                }
            });
    </script>

</body>

</html>