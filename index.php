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

// Prüfen, ob das Reservierungsformular abgesendet wurde
if (isset($_POST['reservationFormSubmitted'])) {
	$secretKey = "";
	$responseKey = $_POST['g-recaptcha-response'];
	$userIP = $_SERVER['REMOTE_ADDR'];
	$url = "https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$responseKey&remoteip=$userIP";
	$response = file_get_contents($url);
	$response = json_decode($response);

	if ($response->success) {
		// Erfolgreiche Validierung
		$name = htmlspecialchars($_POST['name']);
		$email = htmlspecialchars($_POST['email']);
		$phone = htmlspecialchars($_POST['phone']);
		$service = htmlspecialchars($_POST['service']);
		$date = htmlspecialchars($_POST['date']);
		$time = htmlspecialchars($_POST['time']);

		// Prepared Statement
		$stmt = $conn->prepare("INSERT INTO tbl_reservierungen (name, email, phone, service, date, time) VALUES (?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("ssssss", $name, $email, $phone, $service, $date, $time);

		if ($stmt->execute()) {
			// E-Mail versenden
			$mail = new PHPMailer(true); // Instanziieren der PHPMailer-Klasse
			try {
				// Servereinstellungen
				$mail->isSMTP(); // Verwende SMTP
				$mail->Host = ''; // Setze den SMTP-Server
				$mail->SMTPAuth = true; // Aktiviere SMTP-Authentifizierung
				$mail->Username = ''; // SMTP-Benutzername
				$mail->Password = ''; // SMTP-Passwort
				$mail->SMTPSecure = 'tls'; // Aktiviere TLS-Verschlüsselung
				$mail->Port = 587; // TCP-Port
				$mail->CharSet = 'UTF-8';

				// Empfänger
				$mail->setFrom('', 'Barbershop KLM'); // Absender
				$mail->addAddress($email, $name); // Empfänger

				// Inhalt der E-Mail
				$mail->isHTML(true); // Setze E-Mail-Format auf HTML
				$mail->Subject = 'Terminbestätigung';
				$mail->Body    = "Hallo $name,<br><br>Ihr Termin für $service am $date um $time wurde erfolgreich gebucht!<br><br>Vielen Dank!";
				$mail->AltBody = "Hallo $name,\n\nIhr Termin für $service am $date um $time wurde erfolgreich gebucht!\n\nVielen Dank!";

				$mail->send();
				$msgRes = "<p class='success'>Termin erfolgreich gebucht! Eine Bestätigungs E-Mail wurde gesendet.</p>";
			} catch (Exception $e) {
				$msgRes = "<p class='success'>Termin erfolgreich gebucht, aber die Bestätigungs E-Mail konnte nicht gesendet werden. Fehler: {$mail->ErrorInfo}</p>";
			}
		} else {
			$msgRes = "Fehler: " . $sql . "<br>" . $conn->error;
		}
	} else {
		// Validierung fehlgeschlagen
		$msgRes = "<p class='error'>Der Termin konnte nicht gebucht werden. Die reCaptcha Überprüfung ist fehlgeschlagen.</p>";
	}
}

$conn->close();

// Prüfen, ob das Kontaktformular abgesendet wurde
if (isset($_POST['contactFormSubmitted'])) {
	$secretKey = "";
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
			$mail->Username   = '';
			$mail->Password   = '';
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
			$mail->Port       = 587; // Port für TLS

			// Absender
			$mail->setFrom('', 'Barbershop');

			// Empfänger (die E-Mail wird an dich gesendet)
			$mail->addAddress('', 'Your Name');

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
<html lang="de">

<head>
	<meta charset="UTF-8">
	<meta name="robots" content="index, follow">
	<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, user-scalable=yes">
	<meta name="description" content="Barber Shop for Gentlemen | Barbershop KLM">
	<title>Barbershop KLM</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
	<link rel="stylesheet" href="css/importer.css">
	<link rel="stylesheet" href="css/reset.css">
	<link rel="stylesheet" href="css/config.css">
	<link rel="stylesheet" href="css/hover.css">
	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" href="css/common.css">
	<link rel="stylesheet" href="css/media.css">
	<link rel="stylesheet" href="css/grid.css">
	<link rel="icon" href="media/icon.svg">
	<script async src="https://kit.fontawesome.com/7933e77e42.js" crossorigin="anonymous"></script>
	<script defer src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"
		integrity="sha512-7eHRwcbYkK4d9g/6tD/mhkf++eoTHwpNM9woBxtPUBWm67zeAfFC+HrdoE2GanKeocly/VxeLvIqwvCdk7qScg=="
		crossorigin="anonymous" referrerpolicy="no-referrer"></script>
	<script src="https://www.google.com/recaptcha/api.js" async defer></script>
	<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
	<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/de.js"></script>
	<script src="js/main.js"></script>
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
			<?php if ($msgCon): ?>
				const contactMessageDiv = document.getElementById('contact-message-container');
				if (contactMessageDiv && contactMessageDiv.childElementCount > 0) {
					contactMessageDiv.scrollIntoView({
						behavior: 'smooth',
						block: 'center'
					});
				}
			<?php endif; ?>
		});
	</script>
	<script>
		document.addEventListener('keydown', function(event) {
			// Prüfen, ob Strg (oder Cmd) + Alt + A gedrückt wird
			if ((event.ctrlKey || event.metaKey) && event.altKey && event.key === 'a') {
				// Hier die URL zum Admin-Bereich einfügen
				window.location.href = 'view_reservations.php';
			}
		});
	</script>
</head>

<body>
	<header>
		<a href="index.php" class="logo">
			<img src="media/barbershop-logo.svg" alt="Logo">
		</a>

		<nav id="primary-navigation">
			<ul>
				<li><a href="index.php">Home</a></li>
				<li><a href="#preise">Services</a></li>
				<li><a href="#contact">Kontakt</a></li>
			</ul>
		</nav>
		<a href="#reserve" class="header-cta btn-primary hvr-bounce-to-bottom">
			<span>
				<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"
					fill="#e8eaed">
					<path
						d="M200-640h560v-80H200v80Zm0 0v-80 80Zm0 560q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-80h80v80h320v-80h80v80h40q33 0 56.5 23.5T840-720v227q-19-9-39-15t-41-9v-43H200v400h252q7 22 16.5 42T491-80H200Zm520 40q-83 0-141.5-58.5T520-240q0-83 58.5-141.5T720-440q83 0 141.5 58.5T920-240q0 83-58.5 141.5T720-40Zm67-105 28-28-75-75v-112h-40v128l87 87Z" />
				</svg>
			</span>
			Termin
		</a>
		<div>

			<a href="tel:+431234567891" class="phone-btn">
				<span>
					<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"
						fill="#eeeeee">
						<path
							d="M798-120q-125 0-247-54.5T329-329Q229-429 174.5-551T120-798q0-18 12-30t30-12h162q14 0 25 9.5t13 22.5l26 140q2 16-1 27t-11 19l-97 98q20 37 47.5 71.5T387-386q31 31 65 57.5t72 48.5l94-94q9-9 23.5-13.5T670-390l138 28q14 4 23 14.5t9 23.5v162q0 18-12 30t-30 12ZM241-600l66-66-17-94h-89q5 41 14 81t26 79Zm358 358q39 17 79.5 27t81.5 13v-88l-94-19-67 67ZM241-600Zm358 358Z" />
					</svg>
				</span>
			</a>

			<button id="menu-btn" aria-controls="primary-navigation" aria-expanded="false">
				<svg stroke="var(--col-font)" fill="none" class="hamburger" viewBox="-10 -10 120 120" width="250">
					<path class="line" stroke-width="10" stroke-linecap="round" stroke-linejoin="round"
						d="m 20 40 h 60 a 1 1 0 0 1 0 20 h -60 a 1 1 0 0 1 0 -40 h 30 v 70">
					</path>
				</svg>
			</button>
		</div>
	</header>

	<main>
		<div id="top" class="placeholder"></div>

		<section>
			<div>
				<h1>Barbershop for <span class="highlight">Gentlemen</span></h1>
				<p>
					Willkommen beim <em><span class="highlight">Nr 1 Barbershop</span></em> in Linz-Kleinmünchen! Mit über 15 Jahren <em>Erfahrung</em> bieten wir
					<span class="highlight">erstklassige Haar- und Bartpflege</span> für echte Gentlemen.
				</p>
				<div class="hero-buttons">
					<a href="#reserve" class="btn-primary hvr-bounce-to-bottom">
						<span>
							<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"
								fill="#e8eaed">
								<path
									d="M200-640h560v-80H200v80Zm0 0v-80 80Zm0 560q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-80h80v80h320v-80h80v80h40q33 0 56.5 23.5T840-720v227q-19-9-39-15t-41-9v-43H200v400h252q7 22 16.5 42T491-80H200Zm520 40q-83 0-141.5-58.5T520-240q0-83 58.5-141.5T720-440q83 0 141.5 58.5T920-240q0 83-58.5 141.5T720-40Zm67-105 28-28-75-75v-112h-40v128l87 87Z" />
							</svg>
						</span>
						Jetzt Termin buchen
					</a>
					<a href="#preise" class="btn-primary hvr-bounce-to-bottom">
						<span>
							<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"
								fill="#e8eaed">
								<path
									d="M280-600v-80h560v80H280Zm0 160v-80h560v80H280Zm0 160v-80h560v80H280ZM160-600q-17 0-28.5-11.5T120-640q0-17 11.5-28.5T160-680q17 0 28.5 11.5T200-640q0 17-11.5 28.5T160-600Zm0 160q-17 0-28.5-11.5T120-480q0-17 11.5-28.5T160-520q17 0 28.5 11.5T200-480q0 17-11.5 28.5T160-440Zm0 160q-17 0-28.5-11.5T120-320q0-17 11.5-28.5T160-360q17 0 28.5 11.5T200-320q0 17-11.5 28.5T160-280Z" />
							</svg>
						</span>
						Preisliste
					</a>
				</div>
				<div class="slider-container">
					<div class="slider">
						<img src="media/shop2.png" alt="station1 photo2" class="img-active" loading="lazy">
						<img src="media/shop3.png" alt="station1 photo3" loading="lazy">
						<img src="media/shop4.png" alt="station1 photo4" loading="lazy">
					</div>
				</div>
			</div>
		</section>

		<section id="haircuts">
			<div class="grid">
				<div class="img-container col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12">
					<div>
						<picture>
							<source srcset="media/hair3.jpg" media="(max-width: 768px)">
							<img src="media/hair1.jpg" alt="Haircut 1" loading="lazy">
						</picture>

					</div>
					<div>
						<img src="media/hair2.jpg" alt="Barber Equipment 1" loading="lazy">
					</div>
				</div>
				<div class="content-container col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12">
					<h2 class="section-heading">HAARSCHNITT UND STYLING</h2>
					<p class="section-sub-heading">Über das Grundlegende hinaus: Spezialisiertes Herren-Hairstyling</p>
					<p>Entdecken Sie unsere Spezialität: individuelles Herren-Hairstyling, das über das Gewöhnliche hinausgeht. </p>
					<a href="#preise" class="btn-primary hvr-bounce-to-bottom">
						Entdecken Sie mehr
					</a>
				</div>
			</div>
		</section>

		<section id="book-call-to-action">
			<div class="grid">
				<h2 class="section-sub-heading col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">Buchen Sie Ihren Friseurtermin.</h2>
				<p class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">Vermeiden Sie das Warten und buchen Sie einen Online-Termin</p>
				<a href="#reserve" class="btn-primary hvr-bounce-to-bottom col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<span>
						<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"
							fill="#e8eaed">
							<path
								d="M200-640h560v-80H200v80Zm0 0v-80 80Zm0 560q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-80h80v80h320v-80h80v80h40q33 0 56.5 23.5T840-720v227q-19-9-39-15t-41-9v-43H200v400h252q7 22 16.5 42T491-80H200Zm520 40q-83 0-141.5-58.5T520-240q0-83 58.5-141.5T720-440q83 0 141.5 58.5T920-240q0 83-58.5 141.5T720-40Zm67-105 28-28-75-75v-112h-40v128l87 87Z" />
						</svg>
					</span>
					Jetzt Termin buchen
				</a>
			</div>
		</section>

		<section id="beards">
			<div class="grid">
				<div class="content-container col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12">
					<h2 class="section-heading">BARTSCHNITT & STYLING</h2>
					<p class="section-sub-heading">Verfeinern Sie Ihren Look: Professionelles Bartstyling</p>
					<ul>
						<li>Exzellenz im Bartstyling</li>
						<li>Skulptierte Raffinesse für Ihren Bart</li>
						<li>Skulptierte Perfektion für Ihren Bart</li>
					</ul>
					<a href="#preise" class="btn-primary hvr-bounce-to-bottom">
						Entdecken Sie mehr
					</a>
				</div>
				<div class="img-container col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12">
					<img src="media/beard1_cropped.png" alt="Barber Equipment 2" loading="lazy">
					<img src="media/beard2_cropped.png" alt="Beard Shave" loading="lazy">
				</div>
			</div>
		</section>

		<section id="preise">
			<div class="grid">
				<h2 class="section-heading col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">Preisliste</h2>
				<p class="section-sub-heading col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">Top-Qualität zu besten Preisen</p>
				<img src="media/barbershop-logo.svg" alt="Logo" class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 grid">
				<ul class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 grid">
					<li class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<span class="service">Haarschnitt</span>
						<span class="whitespace"></span>
						<span class="price">€18.00</span>
					</li>
					<li class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<span class="service">Bart Styling</span>
						<span class="whitespace"></span>
						<span class="price">€12.00</span>
					</li>
					<li class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<span class="service">Kinder Haarschnitt (bis 12 Jahre)</span>
						<span class="whitespace"></span>
						<span class="price">€12.00</span>
					</li>
					<li class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<span class="service">Haare waschen</span>
						<span class="whitespace"></span>
						<span class="price">€5.00</span>
					</li>
				</ul>
				<ul class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 grid">
					<li class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<span class="service">Augenbrauen zupfen + threading</span>
						<span class="whitespace"></span>
						<span class="price">€5.00</span>
					</li>
					<li class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<span class="service">Gesichtsmaske</span>
						<span class="whitespace"></span>
						<span class="price">€5.00</span>
					</li>
					<li class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<span class="service">Professional Wax 150ml</span>
						<span class="whitespace"></span>
						<span class="price">€7.00</span>
					</li>

					<li id="total-price" class="total-price col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<span>Gesamtpreis: </span>
						<span class="price"></span>
					</li>
				</ul>

			</div>
		</section>
		<section id="reserve">
			<div class="grid">
				<h2 class="section-heading col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">Buchen Sie einen Termin</h2>
				<p class="section-sub-heading col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">Lassen Sie uns Ihren Termin vereinbaren.</p>

				<form id="reservationForm" class="grid col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12" method="POST">

					<div class="grid col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<label for="name" class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">Name:</label>
						<input type="text" id="name" class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12" placeholder="Max Mustermann" name="name" required>

						<label for="email" class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">E-Mail:</label>
						<input type="email" id="email" class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12" placeholder="max.mustermann@muster.at" name="email" required>

						<label for="phone" class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">Telefonnummer:</label>
						<input type="tel" id="phone" class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12" placeholder="+43 699 ... ... .." name="phone" required>
					</div>

					<div class="grid col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<label for="date" hidden>Datum:</label>
						<input type="text" id="datepicker" class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12" name="date" required readonly hidden>
						<label for="time" hidden>Uhrzeit:</label>
						<input type="time" id="time" name="time" hidden>
					</div>

					<div id="timeSlots" class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12"></div>

					<div class="grid col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<label for="selectedService" class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">Dienstleistung:</label>
						<div id="services" class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<button type="button" data-service="Haarschnitt">
								<span class="service">Haarschnitt <span class="duration">Dauer: ca. 30min</span></span>
								<span class="price">€18.00</span>
							</button>
							<button type="button" data-service="Bart Styling">
								<span class="service">Bart Styling <span class="duration">Dauer: ca. 30min</span></span>
								<span class="price">€12.00</span>
							</button>
							<button type="button" data-service="Kinder Haarschnitt">
								<span class="service">Kinder Haarschnitt (bis 12 Jahre) <span class="duration">Dauer: ca. 30min</span></span>
								<span class="price">€12.00</span>
							</button>
						</div>
						<!-- Verstecktes Input-Feld für den Service-Wert -->
						<input type="text" id="selectedService" name="service">
					</div>

					<div class="g-recaptcha-container col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<div class="g-recaptcha"
							data-sitekey=""></div>
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

		<section id="galerie">
			<div class="grid">
				<h2 class="section-heading col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">Impressionen</h2>
				<p class="section-sub-heading col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">Ein Auszug aus unserem Katalog</p>
				<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div class="infinite-slider">
						<img src="media/photo1.png" alt="haircut1">
						<img src="media/photo2.png" alt="haircut2">
						<img src="media/photo3.png" alt="haircut3">
						<img src="media/photo4.png" alt="haircut4">
						<img src="media/photo5.png" alt="haircut5">
						<img src="media/photo6.png" alt="haircut6">
					</div>
				</div>

				<a href="#reserve" class="btn-primary hvr-bounce-to-bottom col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<span>
						<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"
							fill="#e8eaed">
							<path
								d="M200-640h560v-80H200v80Zm0 0v-80 80Zm0 560q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-80h80v80h320v-80h80v80h40q33 0 56.5 23.5T840-720v227q-19-9-39-15t-41-9v-43H200v400h252q7 22 16.5 42T491-80H200Zm520 40q-83 0-141.5-58.5T520-240q0-83 58.5-141.5T720-440q83 0 141.5 58.5T920-240q0 83-58.5 141.5T720-40Zm67-105 28-28-75-75v-112h-40v128l87 87Z" />
						</svg>
					</span>
					Jetzt Termin buchen
				</a>
			</div>
		</section>



		<section id="testimonials">
			<div class="grid">
				<h2 class="section-heading col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">Kundenreferenzen</h2>
				<p class="section-sub-heading col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">Was unsere Kunden sagen</p>
				<div class="slideshow-container col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">

					<div class="mySlides fade">
						<article class="testimonial">
							<img src="media/marvin-meyer-HrfZ1yOQw_E-unsplash.png" alt="profile picture 1" class="profile-picture">
							<div class="testimonial-author">
								<p>Markus S.</p>
								<p><img class="stars" src="media/stars5.png" alt="5 Sterne"> 5 / 5</p>
							</div>
							<p class="testimonial-text">
								"Ohne großen Plan bin ich in den Laden gekommen und wurde sofort freundlich empfangen. Der
								Bartschnitt war erstklassig, und ich bin mit dem Ergebnis mehr als zufrieden. Der Service ist
								wirklich top, und ich werde definitiv wiederkommen!"
							</p>

							<p class="testimonial-source">
								<small>Rezension aus <i class="fa-brands fa-google google-icon"></i>Google</small>
							</p>
						</article>
						<div class="numbertext">1 / 3</div>
					</div>

					<div class="mySlides fade">
						<article class="testimonial">
							<img src="media/alex-robinson-vpwDZgKtgVk-unsplash.png" alt="profile picture 2" class="profile-picture">
							<div class="testimonial-author">
								<p>Maximilian B.</p>
								<p><img class="stars" src="media/stars5.png" alt="5 Sterne"> 5 / 5</p>
							</div>
							<p class="testimonial-text">
								"Heute war ich zum ersten Mal hier, und ich wurde direkt bedient. Der Barber war sehr freundlich
								und professionell. Das Ergebnis hat mich voll überzeugt.
								Es ist klar, dass hier mit Leidenschaft gearbeitet wird. Ich komme auf jeden Fall wieder!"
							</p>

							<p class="testimonial-source">
								<small>Rezension aus <i class="fa-brands fa-google google-icon"></i>Google</small>
							</p>
						</article>
						<div class="numbertext">2 / 3</div>
					</div>

					<div class="mySlides fade">
						<article class="testimonial">
							<img src="media/drew-hays-agGIKYs4mYs-unsplash.png" alt="profile picture 3" class="profile-picture">
							<div class="testimonial-author">
								<p>Sebastian K.</p>
								<p><img class="stars" src="media/stars5.png" alt="5 Sterne"> 5 / 5</p>
							</div>
							<p class="testimonial-text">
								"Beste Qualität in der Stadt zu einem fairen Preis. Der Laden ist sauber und gut gepflegt. Der Termin wurde pünktlich eingehalten, und
								der Service war schnell, freundlich und professionell. Ich kann diesen Barbershop nur wärmstens
								weiterempfehlen!"
							</p>

							<p class="testimonial-source">
								<small>Rezension aus <i class="fa-brands fa-google google-icon"></i>Google</small>
							</p>
						</article>
						<div class="numbertext">3 / 3</div>
					</div>

				</div>

				<a class="prev" onclick="plusSlides(-1)">&#10094;</a>
				<a class="next" onclick="plusSlides(1)">&#10095;</a>
				<br>

				<div class="dots col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<span class="dot" onclick="currentSlide(1)"></span>
					<span class="dot" onclick="currentSlide(2)"></span>
					<span class="dot" onclick="currentSlide(3)"></span>
				</div>
			</div>
		</section>

		<section id="location">
			<div class="grid">
				<h2 class="section-heading col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">Anfahrt</h2>
				<p class="section-sub-heading col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">Hier finden Sie uns.</p>
				<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<p>
						Musterstraße 27<br>
						4020 Linz<br>
						<a href="mailto:office@barber.at">office@barbershop.at</a><br>
						<a href="tel:+43123456789">43 123 456789</a>
					</p>
					<p>
						Öffnungszeiten:<br>
						MO - FR: <strong class="open">09:00 - 18:00 Uhr</strong><br>
						SA: <strong class="open">09:00 - 13:00 Uhr</strong><br>
						SO: <strong class="open">Geschlossen</strong>
					</p>
				</div>
				<iframe class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12"
					src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d16375.249202666386!2d14.296651782557536!3d48.293063929883!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2sat!4v1725372297547!5m2!1sen!2sat"
					style="border:0;" allowfullscreen="" loading="lazy"
					referrerpolicy="no-referrer-when-downgrade"></iframe>
			</div>
		</section>

		<section id="contact">
			<div class="grid">
				<h2 class="section-heading col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">Kontakt</h2>
				<p class="section-sub-heading col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">Schreiben sie uns eine Nachricht.</p>
				<form method="POST" id="contact-form"
					class="grid col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">

					<label for="name" class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
						Name
					</label>

					<input type="text" id="name" name="Name" placeholder="Max Mustermann"
						class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">

					<label for="email" class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
						Emailadresse
					</label>

					<input type="email" id="email" name="Email" placeholder="max.mustermann@muster.at"
						class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">

					<label for="nachricht" class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
						Nachricht
					</label>

					<textarea id="nachricht" name="Nachricht" placeholder="Hallo Barbershop KLM Team! ..."
						class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12" required></textarea>

					<div class="g-recaptcha-container col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<div class="g-recaptcha"
							data-sitekey="" data-theme="dark">
						</div>
					</div>

					<input type="hidden" name="contactFormSubmitted" value="1">

					<button type="submit" id="submit"
						class="btn-primary hvr-bounce-to-bottom col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
							<path d="M120-160v-640l760 320-760 320Zm80-120 474-200-474-200v140l240 60-240 60v140Zm0 0v-400 400Z" />
						</svg>
						Senden
					</button>
				</form>
				<div id="contact-message-container" class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<?php echo $msgCon; ?>
				</div>
			</div>
		</section>
	</main>

	<footer id="footer">
		<div class="grid">
			<a href="index.php" class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-xs-12">
				<img src="media/barbershop-logo.svg" alt="Logo">
			</a>
			<div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-xs-12">
				<h3>Sitemap</h3>
				<ul>
					<li><a href="index.php">Home</a></li>
					<li><a href="#preise">Services</a></li>
					<li><a href="#contact">Kontakt</a></li>
					<li><a href="#reserve">Termin</a></li>
				</ul>
			</div>
			<div class="col-xl-5 col-lg-5 col-md-6 col-sm-12 col-xs-12">
				<h3>Rechtliche Informationen</h3>
				<ul>
					<li><a href="impressum-datenschutz.html#impressum">Impressum</a></li>
					<li><a href="impressum-datenschutz.html#datenschutz">Datenschutz</a></li>
					<li><a href="javascript:void(0);" id="toggleCookies">Cookies</a></li>
				</ul>
			</div>
			<ul class="social col-xl-2 col-lg-2 col-md-6 col-sm-12 col-xs-12">
				<li><a href="#" target="_blank" class="hvr-grow"><i class="fa-brands fa-facebook-f"></i></a></li>
				<li><a href="#" target="_blank" class="hvr-grow"><i class="fa-brands fa-instagram"></i></a></li>
				<li><a href="#" target="_blank" class="hvr-grow"><i class="fa-brands fa-google"></i></a></li>
			</ul>
			<p class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12">
				Copyright © 2024 <span class="highlight">Barbershop KLM</span><span><br></span> | Alle Rechte Vorbehalten.
			</p>
		</div>
	</footer>

	<div class="cookie-banner" id="cookie-banner">
		<p>Wir verwenden Cookies, um die Nutzung unserer Webseite zu verbessern. Durch die weitere Nutzung der Seite
			stimmen Sie der Verwendung von Cookies zu.</p>
		<div class="cookie-btns">
			<a class="btn-primary" href="impressum-datenschutz.html#datenschutz">Mehr erfahren</a>

			<button class="btn-primary" onclick="acceptCookies()">Akzeptieren</button>
		</div>
		<button id="closeCookies" onclick="acceptCookies()">
			<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#eeeeee">
				<path
					d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z" />
			</svg>
		</button>
	</div>
	<a href="#top" id="to-top-btn">
		<svg xmlns="http://www.w3.org/2000/svg" height="32px" viewBox="0 -960 960 960" width="32px"
			fill="var(--col-font-inverted)">
			<path d="M480-528 296-344l-56-56 240-240 240 240-56 56-184-184Z" />
		</svg>
	</a>
	<script>
		let slideIndex = 1;
		showSlides(slideIndex);

		// Next/previous controls
		function plusSlides(n) {
			showSlides(slideIndex += n);
		}

		// Thumbnail image controls
		function currentSlide(n) {
			showSlides(slideIndex = n);
		}

		function showSlides(n) {
			let i;
			let slides = document.getElementsByClassName("mySlides");
			let dots = document.getElementsByClassName("dot");
			if (n > slides.length) {
				slideIndex = 1
			}
			if (n < 1) {
				slideIndex = slides.length
			}
			for (i = 0; i < slides.length; i++) {
				slides[i].style.display = "none";
			}
			for (i = 0; i < dots.length; i++) {
				dots[i].className = dots[i].className.replace(" dot-active", "");
			}
			slides[slideIndex - 1].style.display = "block";
			dots[slideIndex - 1].className += " dot-active";
		}
	</script>
</body>

</html>
