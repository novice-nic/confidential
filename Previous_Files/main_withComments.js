"use strict";

(function () {
  // Storage keys for remembering sidebar mini state and color theme
  var sidebarStorageKey = "adminHMD.sidebarMini";
  var themeStorageKey = "adminHMD.colorTheme";
  // Media query for desktop screens (992px and up)
  var desktopMedia = "(min-width: 992px)";

  // Run a callback when the DOM is fully ready
  function onReady(callback) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", callback);
      return;
    }
    callback();
  }

  // Check if the current screen size is desktop
  function isDesktop() {
    return window.matchMedia(desktopMedia).matches;
  }

  // Test whether localStorage is available (some browsers block it)
  function canUseStorage() {
    try {
      var testKey = sidebarStorageKey + ".test";
      window.localStorage.setItem(testKey, "1");
      window.localStorage.removeItem(testKey);
      return true;
    } catch (error) {
      return false;
    }
  }

  // Read the saved "sidebar mini" preference from localStorage
  function getSavedMiniState(storageAvailable) {
    if (!storageAvailable) {
      return false;
    }
    return window.localStorage.getItem(sidebarStorageKey) === "true";
  }

  // Save the "sidebar mini" preference to localStorage
  function saveMiniState(storageAvailable, isMini) {
    if (storageAvailable) {
      window.localStorage.setItem(sidebarStorageKey, String(isMini));
    }
  }

  // Decide which theme to use (saved preference or default to light)
  function getPreferredTheme(storageAvailable) {
    var savedTheme = storageAvailable ? window.localStorage.getItem(themeStorageKey) : "";

    if (savedTheme === "dark" || savedTheme === "light") {
      return savedTheme;
    }

    // System preference check is currently commented out
    // if (window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches) {
    //   return "dark";
    // }

    return "light";
  }

  // Main initialization – runs once the page is ready
  onReady(function () {
    var body = document.body;
    var sidebarToggle = document.querySelector("[data-sidebar-toggle]");
    var themeToggles = document.querySelectorAll("[data-theme-toggle]");
    var themeIcons = document.querySelectorAll("[data-theme-icon]");
    var closeButtons = document.querySelectorAll("[data-sidebar-close]");
    var sidebarLinks = document.querySelectorAll(".sidebar-nav .nav-link");
    var mediaQuery = window.matchMedia(desktopMedia);
    var storageAvailable = canUseStorage();

    // Enable Bootstrap-style form validation on forms with the .needs-validation class
    function initValidation() {
      var forms = document.querySelectorAll(".needs-validation");

      Array.prototype.forEach.call(forms, function (form) {
        form.addEventListener("submit", function (event) {
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
          }
          form.classList.add("was-validated");
        });
      });
    }

    // Make table search boxes filter rows as the user types
    function initTableSearch() {
      var searchInputs = document.querySelectorAll("[data-table-search]");

      Array.prototype.forEach.call(searchInputs, function (input) {
        var tableId = input.getAttribute("data-table-search");
        var table = document.getElementById(tableId);

        if (!table) {
          return;
        }

        input.addEventListener("input", function () {
          var query = input.value.trim().toLowerCase();
          var rows = table.querySelectorAll("tbody tr");

          Array.prototype.forEach.call(rows, function (row) {
            // Hide rows that do not contain the search text
            row.hidden = query !== "" && row.textContent.toLowerCase().indexOf(query) === -1;
          });
        });
      });
    }

    // Update the theme toggle button label and icon
    function updateThemeControls(theme) {
      var nextTheme = theme === "dark" ? "light" : "dark";
      var label = "Switch to " + nextTheme + " mode";
      var iconClass = theme === "dark" ? "bi bi-sun" : "bi bi-moon-stars";

      Array.prototype.forEach.call(themeToggles, function (button) {
        button.setAttribute("aria-label", label);
        button.setAttribute("title", label);
      });

      Array.prototype.forEach.call(themeIcons, function (icon) {
        icon.className = iconClass;
      });
    }

    // Apply a theme (dark or light) to the page and remember it
    function applyTheme(theme) {
      document.documentElement.setAttribute("data-theme", theme);
      document.documentElement.setAttribute("data-bs-theme", theme);

      if (storageAvailable) {
        window.localStorage.setItem(themeStorageKey, theme);
      }

      updateThemeControls(theme);
    }

    // Set up the theme toggle buttons
    function initThemeToggle() {
      applyTheme(getPreferredTheme(storageAvailable));

      Array.prototype.forEach.call(themeToggles, function (button) {
        button.addEventListener("click", function () {
          var currentTheme = document.documentElement.getAttribute("data-theme") === "dark" ? "dark" : "light";
          applyTheme(currentTheme === "dark" ? "light" : "dark");
        });
      });
    }

    // Run the form validation and table search initializers
    initValidation();
    initTableSearch();
    initThemeToggle();

    // Fill in the user name, workspace and avatar from window.adminHMDUser (or defaults)
    function initUserProfile() {
      var user = window.adminHMDUser || { name: "Admin", workspace: "", avatar: "" };

      var sidebarNameEl = document.querySelector(".sidebar-user strong");
      var sidebarWorkspaceEl = document.querySelector(".sidebar-user small");
      var sidebarAvatar = document.querySelector(".sidebar-user .avatar-img");
      var profileNameEls = document.querySelectorAll(".profile-name");
      var profileAvatarEls = document.querySelectorAll(".profile-button .avatar-img, .profile-button img");

      if (sidebarNameEl) sidebarNameEl.textContent = user.name;
      if (sidebarWorkspaceEl) sidebarWorkspaceEl.textContent = user.workspace;
      if (sidebarAvatar && user.avatar) {
        sidebarAvatar.src = user.avatar;
        sidebarAvatar.alt = user.name;
      }

      Array.prototype.forEach.call(profileNameEls, function (el) {
        el.textContent = user.name;
      });
      Array.prototype.forEach.call(profileAvatarEls, function (img) {
        if (user.avatar) img.src = user.avatar;
        if (user.name) img.alt = user.name;
      });
    }

    initUserProfile();

    // If there is no sidebar toggle button, stop here
    if (!sidebarToggle) {
      return;
    }

    // Helper: add or remove a CSS class
    function setClass(element, className, enabled) {
      if (enabled) {
        element.classList.add(className);
      } else {
        element.classList.remove(className);
      }
    }

    // Update the aria-expanded attribute on the sidebar toggle
    function setToggleExpanded() {
      var expanded = isDesktop()
        ? !body.classList.contains("sidebar-mini")
        : body.classList.contains("sidebar-open");

      sidebarToggle.setAttribute("aria-expanded", String(expanded));
    }

    // Close the mobile sidebar
    function closeMobileSidebar() {
      body.classList.remove("sidebar-open");
      setToggleExpanded();
    }

    // Toggle the sidebar (mini on desktop, open/closed on mobile)
    function toggleSidebar() {
      if (isDesktop()) {
        body.classList.toggle("sidebar-mini");
        saveMiniState(storageAvailable, body.classList.contains("sidebar-mini"));
      } else {
        body.classList.toggle("sidebar-open");
      }
      setToggleExpanded();
    }

    // Attach click handlers that close the mobile sidebar
    function addCloseHandlers(items) {
      Array.prototype.forEach.call(items, function (item) {
        item.addEventListener("click", function () {
          if (!isDesktop()) {
            closeMobileSidebar();
          }
        });
      });
    }

    // Restore the previously saved mini-sidebar state on desktop
    if (getSavedMiniState(storageAvailable) && isDesktop()) {
      body.classList.add("sidebar-mini");
    }

    // Wire up the sidebar toggle and close buttons
    sidebarToggle.addEventListener("click", toggleSidebar);
    addCloseHandlers(closeButtons);
    addCloseHandlers(sidebarLinks);
    setToggleExpanded();

    // Handle screen-size changes (desktop ↔ mobile)
    function handleBreakpointChange() {
      if (isDesktop()) {
        body.classList.remove("sidebar-open");
        setClass(body, "sidebar-mini", getSavedMiniState(storageAvailable));
      } else {
        body.classList.remove("sidebar-mini");
      }
      setToggleExpanded();
    }

    // Listen for media-query changes (modern + older browsers)
    if (mediaQuery.addEventListener) {
      mediaQuery.addEventListener("change", handleBreakpointChange);
    } else if (mediaQuery.addListener) {
      mediaQuery.addListener(handleBreakpointChange);
    }
  });
})();