document.addEventListener("DOMContentLoaded", () => {
  const nav = document.querySelector("nav");
  const menuBtn = document.querySelector("#menu-btn");
  const header = document.querySelector("header");
  const firstSection = document.querySelector("main > div");
  let lastScrollY = window.scrollY;

  // Menu Button Click Event
  if (menuBtn) {
    menuBtn.addEventListener("click", () => {
      const isMenuVisible =
        menuBtn.getAttribute("aria-expanded") === "true" ||
        menuBtn.getAttribute("data-state") === "opened";

      if (!isMenuVisible) {
        // Open menu
        nav.classList.remove("menu-hidden");
        nav.classList.add("menu-visible");
        menuBtn.setAttribute("data-state", "opened");
        menuBtn.setAttribute("aria-expanded", "true");
      } else {
        // Close menu
        nav.classList.remove("menu-visible");
        nav.classList.add("menu-hidden");
        menuBtn.setAttribute("data-state", "closed");
        menuBtn.setAttribute("aria-expanded", "false");
      }
    });
  }
  // Funktion zur Überprüfung der Viewport-Größe und des Menüzustands
  function checkViewportAndMenuState() {
    const isMenuOpened = menuBtn.getAttribute("data-state") === "opened";
    const viewportWidth = window.innerWidth;

    if (viewportWidth < 1024) {
      // 64rem in px (bei 16px Basisgröße)
      if (!isMenuOpened) {
        nav.classList.add("menu-hidden");
        nav.classList.remove("menu-visible");
      }
    } else {
      // Wenn der Viewport wieder größer wird, Klasse .menu-hidden entfernen
      nav.classList.remove("menu-hidden");
    }
  }

  // Initialprüfung beim Laden der Seite
  checkViewportAndMenuState();

  // Nav ausblenden bei Klick auf Nav Links
  // document.querySelectorAll("a").forEach((link) => {
  //   link.addEventListener("click", () => {
  //     // Entferne die Menü-Sichtbarkeit
  //     nav.classList.remove("menu-visible");
  //     menuBtn.setAttribute("data-state", "closed");
  //     menuBtn.setAttribute("aria-expanded", "false");

  //     // Verzögere die Header-Transformation um 750ms
  //     setTimeout(() => {
  //       header.classList.add("header-hidden");
  //     }, 1500);
  //   });
  // });

  // Sticky Header
  // const calculateStickyPoint = () => {
  //   return firstSection.getBoundingClientRect().bottom + window.scrollY;
  // };

  // let stickyPoint = calculateStickyPoint();

  // window.addEventListener("resize", () => {
  //   stickyPoint = calculateStickyPoint();
  // });

  // window.addEventListener("scroll", () => {
  //   const currentScrollY = window.scrollY;

  //   if (currentScrollY > stickyPoint) {
  //     if (currentScrollY < lastScrollY) {
  //       // Nach oben scrollen
  //       header.classList.add("header-small");
  //       header.classList.remove("header-hidden");
  //     } else {
  //       // Nach unten scrollen
  //       header.classList.add("header-hidden");
  //     }
  //   } else {
  //     // Am Anfang der Seite
  //     header.classList.remove("header-small");
  //     header.classList.remove("header-hidden");
  //   }

  //   lastScrollY = currentScrollY;
  // });

  // Bildergalerie
  const slider = document.querySelector(".slider");
  const images = document.querySelectorAll(".slider img");
  
  let currentIndex = 0;
  let autoScrollInterval;
  let autoScrollTimeout;
  
  // Funktion zum Aktualisieren der aktiven Bilder
  const updateActiveImage = () => {
    images.forEach((img, index) => {
      img.classList.toggle("img-active", index === currentIndex);
    });
  };
  
  // Funktion zum Starten des automatischen Bildwechsels
  const startAutoScroll = () => {
    stopAutoScroll(); // Verhindert mehrfache Intervallstarts
    autoScrollInterval = setInterval(() => {
      currentIndex = (currentIndex + 1) % images.length;
      updateActiveImage();
    }, 5000); // 5 Sekunden pro Bild
  };
  
  // Funktion zum Stoppen des automatischen Bildwechsels
  const stopAutoScroll = () => {
    clearInterval(autoScrollInterval);
  };
  
  // Starte das automatische Wechseln
  startAutoScroll();
  
  // Wisch-Gesten für den Slider
  let touchStartX = 0;
  
  slider.addEventListener("touchstart", (e) => {
    touchStartX = e.touches[0].clientX;
  });
  
  slider.addEventListener("touchend", (e) => {
    const touchEndX = e.changedTouches[0].clientX;
    const touchDiff = touchStartX - touchEndX;
  
    if (Math.abs(touchDiff) > 50) {
      // Schwellwert für das Wischen
      if (touchDiff > 0) {
        // Wisch nach links
        currentIndex = (currentIndex + 1) % images.length;
      } else {
        // Wisch nach rechts
        currentIndex = (currentIndex - 1 + images.length) % images.length;
      }
      updateActiveImage();
  
      // Stoppe die automatische Bildwechsel-Animation für 3 Sekunden
      stopAutoScroll();
      clearTimeout(autoScrollTimeout);
      autoScrollTimeout = setTimeout(startAutoScroll, 3000);
    }
  });
  
  // Infinite Slider Galerie
  const sliderElement = document.querySelector(".infinite-slider");
  const sliderImages = sliderElement.querySelectorAll("img");

  let totalSliderWidth = 0;
  let currentOffset = 0;
  let autoScrollAnimationId;
  let isPausedInfinite = false; // Variable für Infinite Slider Pausenzustand

  if (sliderElement && sliderImages.length > 0) {
    window.onload = () => {
      sliderImages.forEach((img) => {
        totalSliderWidth += img.offsetWidth;
      });

      for (let i = 0; i < 3; i++) {
        // 3 mal klonen für insgesamt 3 Sets
        sliderImages.forEach((img) => {
          const clonedImage = img.cloneNode(true);
          sliderElement.appendChild(clonedImage);
        });
      }

      sliderElement.style.width = `${totalSliderWidth * 3}px`; // 3 mal die Gesamtbreite

      // Setze will-change und transform zur Hardware-Beschleunigung
      sliderElement.style.willChange = "transform";
      sliderElement.style.transform = "translateZ(0)";

      const animateInfiniteSlider = () => {
        if (!isPausedInfinite) {
          currentOffset -= 0.5; // Reduziere die Geschwindigkeit
          sliderElement.style.transform = `translateX(${currentOffset}px)`;

          if (Math.abs(currentOffset) >= totalSliderWidth * 2) {
            currentOffset = 0;
            sliderElement.style.transform = `translateX(${currentOffset}px)`;
          }

          autoScrollAnimationId = requestAnimationFrame(animateInfiniteSlider);
        }
      };

      const startInfiniteSlider = () => {
        animateInfiniteSlider();
        // Stoppe die Animation nach 30 Sekunden und starte sie neu
        setTimeout(() => {
          cancelAnimationFrame(autoScrollAnimationId);
          currentOffset = 0; // Setze den Slider auf Anfang zurück
          sliderElement.style.transform = `translateX(${currentOffset}px)`;
          startInfiniteSlider(); // Starte die Animation erneut
        }, 30000); // 30 Sekunden
      };

      startInfiniteSlider();

      sliderElement.addEventListener("click", () => {
        if (isPausedInfinite) {
          isPausedInfinite = false;
          startInfiniteSlider();
        } else {
          isPausedInfinite = true;
          cancelAnimationFrame(autoScrollAnimationId);
        }
      });
    };
  }
  // Preise
  const listItems = document.querySelectorAll("#preise .grid li");
  const totalPriceContainer = document.querySelector("#total-price");

  let total = 0;
  let activeItemsCount = 0; // Zählt die Anzahl der aktiven Listenelemente

  listItems.forEach((item) => {
    item.addEventListener("click", () => {
      const priceSpan = item.querySelector(".price");
      const priceValue = parseFloat(
        priceSpan.textContent.replace("€", "").trim()
      );

      if (item.classList.contains("active")) {
        // Remove from total
        total -= priceValue;
        item.classList.remove("active");
        activeItemsCount--; // Dekrementieren der Anzahl aktiver Elemente
      } else {
        // Add to total
        total += priceValue;
        item.classList.add("active");
        activeItemsCount++; // Inkrementieren der Anzahl aktiver Elemente
      }

      // Update the total price display
      if (activeItemsCount >= 2) {
        totalPriceContainer.classList.remove("hide");
        totalPriceContainer.classList.add("show");
        totalPriceContainer.querySelector(
          "span:last-of-type"
        ).textContent = `€${total.toFixed(2)}`;
      } else {
        totalPriceContainer.classList.remove("show");
        totalPriceContainer.classList.add("hide");
      }
    });
  });

  // Testimonials

  // Funktion zum Setzen von Cookies
  function setCookie(name, value, days, sameSite = "Lax") {
    let expires = "";
    if (days) {
      const date = new Date();
      date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
      expires = "; expires=" + date.toUTCString();
    }
    let cookieString =
      name + "=" + (value || "") + expires + "; path=/; SameSite=" + sameSite;
    // Uncomment the line below if using HTTPS
    // cookieString += "; Secure";
    document.cookie = cookieString;
    console.log("Cookie gesetzt: " + document.cookie); // Debugging
  }

  // Funktion zum Abrufen von Cookies
  function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(";");
    for (let i = 0; i < ca.length; i++) {
      let c = ca[i];
      while (c.charAt(0) === " ") c = c.substring(1, c.length);
      if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
  }

  // Funktion zum Akzeptieren der Cookies
  function acceptCookies() {
    setCookie("cookieConsent", "true", 30, "Lax"); // Cookie für 30 Tage setzen
    const cookieBanner = document.getElementById("cookie-banner");
    cookieBanner.classList.remove("show-banner");
    cookieBanner.classList.add("hide-banner");

    setTimeout(() => {
      cookieBanner.style.display = "none";
    }, 500); // Die Dauer sollte mit der CSS-Transition übereinstimmen
  }

  // Toggle-Funktion für den Cookie-Banner
  const toggleCookies = document.querySelector("#toggleCookies");
  const closeCookies = document.querySelector("#closeCookies");
  const cookieBanner = document.getElementById("cookie-banner");

  toggleCookies.addEventListener("click", (event) => {
    event.preventDefault(); // Verhindert das Standardverhalten des Ankers

    if (cookieBanner.classList.contains("show-banner")) {
      cookieBanner.classList.remove("show-banner");
      cookieBanner.classList.add("hide-banner");

      // Nach Ablauf der Animation, das Banner ausblenden
      setTimeout(() => {
        cookieBanner.style.display = "none";
      }, 500);
    } else {
      cookieBanner.style.display = "grid"; // Zuerst das Banner sichtbar machen
      setTimeout(() => {
        cookieBanner.classList.remove("hide-banner");
        cookieBanner.classList.add("show-banner");
      }, 10); // Kurze Verzögerung, um die Display-Änderung wirksam zu machen
    }
  });
  closeCookies.addEventListener("click", () => {
    cookieBanner.classList.remove("show-banner");
    cookieBanner.classList.add("hide-banner");
  });
  // Funktion zur Überprüfung des Cookies
  function checkCookieConsent() {
    const cookieConsent = getCookie("cookieConsent");

    if (cookieConsent === "true") {
      document.getElementById("cookie-banner").style.display = "none";
    } else {
      const cookieBanner = document.getElementById("cookie-banner");
      cookieBanner.style.display = "grid";
      setTimeout(() => {
        cookieBanner.classList.add("show-banner");
      }, 10); // Kurze Verzögerung, um die Display-Änderung wirksam zu machen
    }
  }

  // Bindet die acceptCookies Funktion an das window Objekt, damit sie im globalen Kontext verfügbar ist
  window.acceptCookies = acceptCookies;

  // Cookie-Überprüfung beim Laden der Seite
  checkCookieConsent();

  // Smoothe Sprungmarken
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault();

      const target = document.querySelector(this.getAttribute("href"));
      const targetPosition =
        target.getBoundingClientRect().top + window.pageYOffset;
      const startPosition = window.pageYOffset;
      const distance = targetPosition - startPosition;
      const duration = 750; // Scroll-Dauer in Millisekunden
      let startTime = null;

      function scrollAnimation(currentTime) {
        if (startTime === null) startTime = currentTime;
        const timeElapsed = currentTime - startTime;
        const run = ease(timeElapsed, startPosition, distance, duration);
        window.scrollTo(0, run);
        if (timeElapsed < duration) requestAnimationFrame(scrollAnimation);
      }

      function ease(t, b, c, d) {
        t /= d / 2;
        if (t < 1) return (c / 2) * t * t + b;
        t--;
        return (-c / 2) * (t * (t - 2) - 1) + b;
      }

      requestAnimationFrame(scrollAnimation);
    });
  });

  // Terminreservierung
  // Funktion zum Laden der Time-Slots für ein bestimmtes Datum
  function loadTimeSlots(dateStr) {
    fetch(`get_timeslots.php?date=${dateStr}`)
      .then((response) => {
        if (!response.ok) {
          throw new Error(
            "Netzwerkantwort war nicht ok: " + response.statusText
          );
        }
        return response.json();
      })
      .then((data) => {
        const timeSlotsDiv = document.getElementById("timeSlots");
        timeSlotsDiv.innerHTML = ""; // Leeren, bevor neue Daten geladen werden

        data.timeslots.forEach((slot) => {
          const slotButton = document.createElement("button");

          // Zeit im Format hh:mm formatieren
          const [hours, minutes] = slot.time.split(":"); // Split in Stunden und Minuten
          const formattedTime = `${hours}:${minutes}`; // Formatieren

          slotButton.textContent = formattedTime; // Setze den formatierten Text
          slotButton.disabled = slot.booked; // Deaktivieren, wenn der Slot besetzt ist

          // Event-Listener für die Button-Klicks hinzufügen
          slotButton.addEventListener("click", (event) => {
            event.preventDefault();
            const activeButton = timeSlotsDiv.querySelector(".timeslot-active");
            if (activeButton) {
              activeButton.classList.remove("timeslot-active");
            }
            slotButton.classList.add("timeslot-active");

            document.getElementById("time").value = slot.time; // Behalte den Originalwert für das Input-Feld
          });

          timeSlotsDiv.appendChild(slotButton);
        });
      })
      .catch((error) => {
        console.error("Es gab ein Problem mit der Fetch-Anfrage:", error);
      });
  }

  // Flatpickr initialisieren
  // Bestimmt das heutige Datum und ob es ein Sonntag ist
  const today = new Date();
  const isSunday = today.getDay() === 0;
  if (isSunday) {
    // Falls heute Sonntag ist, wird das Datum auf Montag gesetzt
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
      function (date) {
        return date.getDay() === 0; // Sonntag deaktivieren
      },
    ],
    onChange: function (selectedDates, dateStr) {
      loadTimeSlots(dateStr); // Neue Fetch-Anfrage bei Datumsauswahl
    },
  });

  // Initialen Fetch-Aufruf für heutiges Datum bei Seitenladung
  loadTimeSlots(today.toISOString().split("T")[0]); // Format: YYYY-MM-DD

  // Event Listener für Service-Buttons
  const serviceButtons = document.querySelectorAll("#services button");
  const selectedServiceInput = document.getElementById("selectedService");

  serviceButtons.forEach((button) => {
    button.addEventListener("click", () => {
      // Entferne die Klasse von anderen Buttons
      serviceButtons.forEach((btn) => btn.classList.remove("service-active"));

      // Füge die aktive Klasse zum geklickten Button hinzu
      button.classList.add("service-active");

      // Setze den Service-Wert im versteckten Input-Feld
      selectedServiceInput.value = button.getAttribute("data-service");
    });
  });
  document
    .getElementById("reservationForm")
    .addEventListener("submit", function (event) {
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
        resMessageDiv.scrollIntoView({ behavior: "smooth", block: "center" });
        resMessageDiv.classList.add("error");
      }
    });
});
