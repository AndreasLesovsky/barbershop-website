document.addEventListener("DOMContentLoaded", () => {
  const nav = document.querySelector("nav");
  const menuBtn = document.querySelector("#menu-btn");
  // const header = document.querySelector("header");
  // const firstSection = document.querySelector("main > div");
  // let lastScrollY = window.scrollY;

  // Menu Button Click Event
  menuBtn.addEventListener("click", () => {
    const isMenuVisible = nav.classList.contains("menu-visible");

    if (!isMenuVisible) {
      // Menü öffnen
      nav.classList.add("menu-visible");
      gsap.fromTo(
        nav,
        {
          y: -500,
          zIndex: -10, // Sicherstellen, dass der z-index niedrig ist, um den Header nicht zu überdecken
        },
        {
          y: 0,
          duration: 0.75,
          ease: "power3.out",
        }
      );
      menuBtn.setAttribute("data-state", "opened");
      menuBtn.setAttribute("aria-expanded", "true");
    } else {
      // Menü schließen
      gsap.to(nav, {
        y: -500,
        duration: 0.75,
        ease: "power3.in",
        onComplete: () => {
          nav.classList.remove("menu-visible");
          menuBtn.setAttribute("data-state", "closed");
          menuBtn.setAttribute("aria-expanded", "false");
        },
      });
    }
  });

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
  //       header.classList.add("small");
  //       header.style.position = "fixed";
  //       header.style.transform = "translateY(0)";
  //     } else {
  //       // Nach unten scrollen
  //       header.style.transform = "translateY(-100vh)";
  //     }
  //   } else {
  //     // Am Anfang der Seite
  //     header.classList.remove("small");
  //     header.style.position = "absolute";
  //     header.style.transform = "translateY(0)";
  //   }

  //   lastScrollY = currentScrollY;
  // });

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
});
