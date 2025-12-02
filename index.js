const sideMenu = document.querySelector("aside");
const menuBtn = document.querySelector("#menu-btn");
const closeBtn = document.querySelector("#close-btn");
const themeToggler = document.querySelector(".theme-toggler");

//mostrar sidebar
menuBtn.addEventListener("click", () => {
  sideMenu.style.display = "block";
});

//fehcar sidebar
closeBtn.addEventListener("click", () => {
  sideMenu.style.display = "none";
});

//mudar tema
themeToggler.addEventListener("click", () => {
  document.body.classList.toggle("dark-theme-variables");
  const isDark = document.body.classList.contains("dark-theme-variables");
  // persist preference
  localStorage.setItem("darkTheme", isDark ? "true" : "false");

  themeToggler.querySelector("span:nth-child(1)").classList.toggle("active");
  themeToggler.querySelector("span:nth-child(2)").classList.toggle("active");
});

// Restore persisted theme on load
document.addEventListener("DOMContentLoaded", () => {
  const saved = localStorage.getItem("darkTheme");

  if (saved === "true") {
    document.body.classList.add("dark-theme-variables");
    const s = themeToggler.querySelector("span:nth-child(1)");
    const m = themeToggler.querySelector("span:nth-child(2)");
    if (s) s.classList.remove("active");
    if (m) m.classList.add("active");
  } else if (saved === "false") {
    // ensure the default state is light
    document.body.classList.remove("dark-theme-variables");
    const s = themeToggler.querySelector("span:nth-child(1)");
    const m = themeToggler.querySelector("span:nth-child(2)");
    if (s) s.classList.add("active");
    if (m) m.classList.remove("active");
  }
});

/* Sidebar: ensure only one item is marked active (panel) and persist active by pathname */
document.addEventListener("DOMContentLoaded", function () {
  const sidebarLinks = document.querySelectorAll("aside .sidebar a");
  if (!sidebarLinks.length) return;

  // Helper to remove active marks
  const clearActive = () =>
    sidebarLinks.forEach((l) => l.classList.remove("panel", "active"));

  // Determine current path (filename)
  const currentPath = window.location.pathname.split("/").pop() || "index.html";

  // Try to highlight by matching href to current path
  let matched = false;
  sidebarLinks.forEach((link) => {
    const href = link.getAttribute("href")
      ? link.getAttribute("href").split("/").pop()
      : "";
    if (href && href === currentPath) {
      clearActive();
      link.classList.add("panel");
      matched = true;
    }
  });

  // If no match by path, try the saved href from localStorage
  if (!matched) {
    const saved = localStorage.getItem("sidebarActiveHref");
    if (saved) {
      const savedLink = Array.from(sidebarLinks).find(
        (l) => l.getAttribute("href") === saved
      );
      if (savedLink) {
        clearActive();
        savedLink.classList.add("panel");
      }
    }
  }

  // Attach click handlers to persist selection and update UI immediately
  sidebarLinks.forEach((link) => {
    link.addEventListener("click", function () {
      clearActive();
      this.classList.add("panel");
      if (this.getAttribute("href")) {
        localStorage.setItem("sidebarActiveHref", this.getAttribute("href"));
      }
    });
  });
});
