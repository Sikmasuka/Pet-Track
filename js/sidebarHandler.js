document.addEventListener("DOMContentLoaded", function () {
  console.log("sidebarHandler.js loaded");

  const sidebarToggleBtn = document.getElementById("sidebar-toggle");
  const sidebar = document.getElementById("sidebar");
  const overlay = document.getElementById("overlay");
  const closeSidebarBtn = document.getElementById("closeSidebarBtn");

  console.log("sidebarToggleBtn:", sidebarToggleBtn);
  console.log("sidebar:", sidebar);
  console.log("overlay:", overlay);
  console.log("closeSidebarBtn:", closeSidebarBtn);

  function openSidebar() {
    sidebar.classList.remove("-translate-x-full");
    overlay.classList.remove("hidden");
    if (sidebarToggleBtn) sidebarToggleBtn.classList.add("hidden");
    document.body.style.overflow = "hidden";
  }

  function closeSidebar() {
    sidebar.classList.add("-translate-x-full");
    overlay.classList.add("hidden");
    if (sidebarToggleBtn) sidebarToggleBtn.classList.remove("hidden");
    document.body.style.overflow = "auto";
  }

  if (sidebarToggleBtn) sidebarToggleBtn.addEventListener("click", openSidebar);
  if (closeSidebarBtn) closeSidebarBtn.addEventListener("click", closeSidebar);
  if (overlay) overlay.addEventListener("click", closeSidebar);

  // Close sidebar when clicking outside on mobile
  document.addEventListener("click", function (event) {
    if (window.innerWidth < 1024) {
      if (
        !sidebar.contains(event.target) &&
        sidebarToggleBtn &&
        !sidebarToggleBtn.contains(event.target)
      ) {
        closeSidebar();
      }
    }
  });

  // Handle window resize
  window.addEventListener("resize", function () {
    if (window.innerWidth >= 1024) {
      closeSidebar();
    }
  });
});
