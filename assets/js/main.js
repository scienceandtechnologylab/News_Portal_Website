// Main JavaScript for Bangladesh Protidin

document.addEventListener("DOMContentLoaded", function () {
  const translations = window.siteTranslations || {};

  // Mobile menu toggle
  const navbarToggler = document.querySelector(".navbar-toggler");
  const navbarCollapse = document.querySelector(".navbar-collapse");

  if (navbarToggler) {
    navbarToggler.addEventListener("click", function () {
      this.classList.toggle("active");
    });
  }

  // News search enhancement
  const searchForms = document.querySelectorAll('form[action="search.php"]');
  searchForms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      const searchInput = this.querySelector('input[name="q"]');
      if (searchInput.value.trim().length < 2) {
        e.preventDefault();
        showAlert(translations.searchMinimumLength || "Please enter at least two characters.", "warning");
        searchInput.focus();
      }
    });
  });

  document.querySelectorAll(".newsletter-subscribe-form").forEach((subscribeForm) => {
    const subscribeStatus = subscribeForm.querySelector(".subscribe-status");
    const subscribeButton = subscribeForm.querySelector(".subscribe-submit");
    const subscribeButtonLabel = subscribeForm.querySelector(".subscribe-submit-label");
    const originalButtonLabel = subscribeButtonLabel.textContent;
    subscribeForm.addEventListener("submit", async function (event) {
      event.preventDefault();
      subscribeStatus.classList.add("d-none");
      subscribeButton.disabled = true;
      subscribeButtonLabel.textContent = translations.subscribeLoading || "Sending...";

      try {
        const response = await fetch(subscribeForm.action, {
          method: "POST",
          body: new FormData(subscribeForm),
          headers: { Accept: "application/json" },
        });
        const data = await response.json();

        subscribeStatus.textContent =
          data.message || translations.subscribeFailed || "Subscription could not be completed.";
        subscribeStatus.className = `alert alert-${
          data.success ? "success" : response.ok ? "warning" : "danger"
        }`;

        if (data.success) {
          subscribeForm.reset();
        }
      } catch (error) {
        console.error("Newsletter subscription failed:", error);
        subscribeStatus.textContent =
          translations.connectionFailed || "Could not connect to the server. Please try again.";
        subscribeStatus.className = "alert alert-danger";
      } finally {
        subscribeButton.disabled = false;
        subscribeButtonLabel.textContent = originalButtonLabel;
      }
    });
  });

  // Auto hide alerts
  const alerts = document.querySelectorAll(".alert");
  alerts.forEach((alert) => {
    setTimeout(() => {
      alert.style.transition = "opacity 0.5s";
      alert.style.opacity = "0";
      setTimeout(() => {
        if (alert.parentNode) {
          alert.parentNode.removeChild(alert);
        }
      }, 500);
    }, 5000);
  });

  // Back to top button
  const backToTop = document.createElement("button");
  backToTop.innerHTML = '<i class="fas fa-arrow-up"></i>';
  backToTop.className = "btn btn-danger btn-lg back-to-top";
  backToTop.style.position = "fixed";
  backToTop.style.bottom = "20px";
  backToTop.style.right = "20px";
  backToTop.style.zIndex = "1000";
  backToTop.style.display = "none";
  backToTop.style.borderRadius = "50%";
  backToTop.style.width = "50px";
  backToTop.style.height = "50px";
  document.body.appendChild(backToTop);

  backToTop.addEventListener("click", () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
  });

  window.addEventListener("scroll", () => {
    if (window.pageYOffset > 300) {
      backToTop.style.display = "block";
    } else {
      backToTop.style.display = "none";
    }
  });

  // Image lazy loading
  if ("IntersectionObserver" in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const img = entry.target;
          img.src = img.dataset.src;
          img.classList.add("loaded");
          observer.unobserve(img);
        }
      });
    });

    document.querySelectorAll("img[data-src]").forEach((img) => {
      imageObserver.observe(img);
    });
  }

  // News share functionality
  function shareNews(title, url) {
    if (navigator.share) {
      navigator
        .share({
          title: title,
          url: url,
        })
        .then(() => console.log("Shared successfully"))
        .catch((error) => console.log("Error sharing:", error));
    } else {
      // Fallback for browsers that don't support Web Share API
      const shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
      window.open(shareUrl, "_blank", "width=600,height=400");
    }
  }

  // Initialize share buttons
  document.querySelectorAll(".share-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      const title = this.dataset.title || document.title;
      const url = this.dataset.url || window.location.href;
      shareNews(title, url);
    });
  });

  // Theme icons only — persistence handled in header (cookie + localStorage on toggle)
  if (typeof window.syncThemeIcons === "function") {
    window.syncThemeIcons();
  }

  window
    .matchMedia("(prefers-color-scheme: dark)")
    .addEventListener("change", function () {
      var hasCookie = document.cookie.indexOf("np_theme=") !== -1;
      var hasLocal = false;
      try {
        hasLocal = !!localStorage.getItem("theme");
      } catch (e) {}
      if (
        !hasCookie &&
        !hasLocal &&
        document.documentElement.getAttribute("data-default-theme") === "system" &&
        typeof window.applySiteTheme === "function"
      ) {
        var theme = window.matchMedia("(prefers-color-scheme: dark)").matches
          ? "dark"
          : "light";
        window.applySiteTheme(theme, false);
      }
    });

  // Font size adjustment
  const fontSizeControls = document.querySelectorAll(".font-size-control");
  fontSizeControls.forEach((control) => {
    control.addEventListener("click", function () {
      const action = this.dataset.action;
      const currentSize = parseInt(getComputedStyle(document.body).fontSize);

      if (action === "increase" && currentSize < 20) {
        document.body.style.fontSize = currentSize + 1 + "px";
      } else if (action === "decrease" && currentSize > 14) {
        document.body.style.fontSize = currentSize - 1 + "px";
      } else if (action === "reset") {
        document.body.style.fontSize = "16px";
      }

      localStorage.setItem("fontSize", document.body.style.fontSize);
    });

    // Load saved font size
    const savedFontSize = localStorage.getItem("fontSize");
    if (savedFontSize) {
      document.body.style.fontSize = savedFontSize;
    }
  });

  // Utility function to show alerts
  window.showAlert = function (message, type = "info") {
    const alertDiv = document.createElement("div");
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

    document.querySelector(".container").prepend(alertDiv);

    setTimeout(() => {
      alertDiv.classList.remove("show");
      setTimeout(() => {
        if (alertDiv.parentNode) {
          alertDiv.parentNode.removeChild(alertDiv);
        }
      }, 500);
    }, 5000);
  };

  // Initialize tooltips
  const tooltipTriggerList = document.querySelectorAll(
    '[data-bs-toggle="tooltip"]',
  );
  const tooltipList = [...tooltipTriggerList].map(
    (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl),
  );
});

// News view counter (AJAX)
function incrementViewCount(newsId) {
  if (newsId) {
    fetch(`api/increment-view.php?id=${newsId}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          const viewElement = document.querySelector(".view-count");
          if (viewElement) {
            viewElement.textContent = data.views;
          }
        }
      })
      .catch((error) => console.error("Error:", error));
  }
}

// Weather widget (example)
function loadWeather() {
  const weatherElement = document.querySelector(".weather-widget");
  if (weatherElement) {
    // This would typically call a weather API
    const dhakaWeather = {
      temp: 28,
      condition: (window.siteTranslations && window.siteTranslations.partlyCloudy) || "Partly cloudy",
      humidity: "78%",
    };

    weatherElement.innerHTML = `
            <div class="weather-info">
                <i class="fas fa-cloud-sun"></i>
                <span>${(window.siteTranslations && window.siteTranslations.dhaka) || "Dhaka"}: ${dhakaWeather.temp}°C, ${dhakaWeather.condition}</span>
            </div>
        `;
  }
}

// Currency rates (example)
function loadCurrencyRates() {
  const currencyElement = document.querySelector(".currency-rates");
  if (currencyElement) {
    // This would typically call a currency API
    const rates = {
      usd: 109.5,
      eur: 119.2,
      gbp: 136.8,
    };

    currencyElement.innerHTML = `
            <div class="currency-info">
                <span>USD: ${rates.usd} BDT</span> |
                <span>EUR: ${rates.eur} BDT</span> |
                <span>GBP: ${rates.gbp} BDT</span>
            </div>
        `;
  }
}

// Initialize widgets when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  loadWeather();
  loadCurrencyRates();
});

// Scroll effect for shadow
window.addEventListener("scroll", function () {
  const navbar = document.querySelector(".sticky-top");
  if (window.scrollY > 20) {
    navbar.classList.add("scrolled");
  } else {
    navbar.classList.remove("scrolled");
  }
});
