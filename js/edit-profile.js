console.log("Debug: edit-profile.js loaded successfully");

const button = document.getElementById("profileButton");
const menu = document.getElementById("dropdownMenu");
const editProfileLink = document.getElementById("editProfileLink");

// Debug: Check DOM elements
console.log("Debug: profileButton", button ? "found" : "not found");
console.log("Debug: dropdownMenu", menu ? "found" : "not found");
console.log("Debug: editProfileLink", editProfileLink ? "found" : "not found");

// Modal toggle functionality - Define as window function for global access
window.toggleModal = function (modalId) {
  console.log(`Debug: toggleModal called with modalId=${modalId}`);
  const modal = document.getElementById(modalId);

  if (!modal) {
    console.error(`Error: Modal ${modalId} not found`);
    return;
  }

  console.log(`Debug: Modal ${modalId}`, modal ? "found" : "not found");

  const isHidden = modal.classList.contains("hidden");
  console.log(`Debug: Modal ${modalId} is ${isHidden ? "hidden" : "visible"}`);

  if (isHidden) {
    // Opening modal
    console.log(`Debug: Opening modal ${modalId}`);
    modal.classList.remove("hidden");
    document.body.classList.add("overflow-hidden");

    // Special handling for modals with content wrappers (like editProfileModal)
    const modalContent = document.getElementById(modalId + "Content");
    if (modalContent) {
      // Add slight delay for smooth animation
      setTimeout(() => {
        modalContent.classList.remove("scale-95", "opacity-0");
        modalContent.classList.add("scale-100", "opacity-100");
      }, 10);
    }
  } else {
    // Closing modal
    console.log(`Debug: Closing modal ${modalId}`);
    const modalContent = document.getElementById(modalId + "Content");

    if (modalContent) {
      modalContent.classList.remove("scale-100", "opacity-100");
      modalContent.classList.add("scale-95", "opacity-0");

      // Wait for animation before hiding
      setTimeout(() => {
        modal.classList.add("hidden");
        document.body.classList.remove("overflow-hidden");
      }, 200);
    } else {
      // For modals without content wrappers (like adminHelpModal)
      modal.classList.add("hidden");
      document.body.classList.remove("overflow-hidden");
    }
  }
};

// Dropdown functionality
if (button && menu) {
  button.addEventListener("click", (e) => {
    e.stopPropagation();
    console.log("Debug: Profile button clicked");
    const isOpen = menu.classList.contains("opacity-100");
    if (isOpen) {
      console.log("Debug: Closing dropdown");
      menu.classList.remove("opacity-100", "scale-100", "pointer-events-auto");
      menu.classList.add("opacity-0", "scale-95", "pointer-events-none");
    } else {
      console.log("Debug: Opening dropdown");
      menu.classList.remove("opacity-0", "scale-95", "pointer-events-none");
      menu.classList.add("opacity-100", "scale-100", "pointer-events-auto");
    }
  });

  // Close dropdown when clicking outside
  document.addEventListener("click", (e) => {
    if (!button.contains(e.target) && !menu.contains(e.target)) {
      console.log("Debug: Clicking outside, closing dropdown");
      menu.classList.remove("opacity-100", "scale-100", "pointer-events-auto");
      menu.classList.add("opacity-0", "scale-95", "pointer-events-none");
    }
  });

  // Add smooth transitions to dropdown items
  const dropdownItems = document.querySelectorAll("#dropdownMenu a");
  console.log("Debug: dropdownItems found:", dropdownItems.length);
  dropdownItems.forEach((item, index) => {
    item.addEventListener("mouseenter", () => {
      console.log(`Debug: Hovering dropdown item ${index}`);
      item.style.transform = "translateX(4px)";
      item.style.transition = "transform 0.2s ease";
    });
    item.addEventListener("mouseleave", () => {
      console.log(`Debug: Leaving dropdown item ${index}`);
      item.style.transform = "translateX(0)";
    });
  });
} else {
  console.error("Debug: Dropdown elements missing", { button, menu });
}

// Handle Edit Profile click
if (editProfileLink) {
  editProfileLink.addEventListener("click", (e) => {
    e.preventDefault();
    e.stopPropagation();
    console.log("Debug: Edit Profile link clicked, opening modal");

    // Close dropdown first
    if (menu) {
      menu.classList.remove("opacity-100", "scale-100", "pointer-events-auto");
      menu.classList.add("opacity-0", "scale-95", "pointer-events-none");
    }

    // Then open modal
    window.toggleModal("editProfileModal");
  });
} else {
  console.error("Debug: editProfileLink not found");
}

// Password toggle functionality - Make it global
window.toggleModalPassword = function () {
  console.log("Debug: toggleModalPassword called");
  const passwordInput = document.getElementById("vetPassword");
  const toggleIcon = document.getElementById("modalPasswordToggle");
  console.log("Debug: vetPassword", passwordInput ? "found" : "not found");
  console.log("Debug: modalPasswordToggle", toggleIcon ? "found" : "not found");

  if (passwordInput && toggleIcon) {
    passwordInput.type =
      passwordInput.type === "password" ? "text" : "password";
    toggleIcon.className =
      passwordInput.type === "password" ? "fas fa-eye" : "fas fa-eye-slash";
    console.log(`Debug: Password input type changed to ${passwordInput.type}`);
  } else {
    console.error("Debug: Password toggle elements missing", {
      passwordInput,
      toggleIcon,
    });
  }
};

// Reset form functionality - Make it global
window.resetForm = function () {
  console.log("Debug: resetForm called");
  if (typeof Swal === "undefined") {
    console.error("Error: SweetAlert2 not loaded");
    return;
  }

  Swal.fire({
    title: "Are you sure?",
    text: "All changes will be reset.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3b82f6",
    cancelButtonColor: "#ef4444",
    confirmButtonText: "Yes, reset",
  }).then((result) => {
    if (result.isConfirmed) {
      console.log("Debug: Reset confirmed");
      const form = document.querySelector("#editProfileForm");
      if (form) {
        form.reset();
        console.log("Debug: Form reset successfully");
        const passwordInput = document.getElementById("vetPassword");
        if (passwordInput) {
          passwordInput.value = "";
          console.log("Debug: Password input cleared");
        }
      } else {
        console.error("Error: Form #editProfileForm not found");
      }
    } else {
      console.log("Debug: Reset cancelled");
    }
  });
};

// Close modal and dropdown on Escape key
document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") {
    console.log("Debug: Escape key pressed");
    if (menu) {
      console.log("Debug: Closing dropdown on Escape");
      menu.classList.remove("opacity-100", "scale-100", "pointer-events-auto");
      menu.classList.add("opacity-0", "scale-95", "pointer-events-none");
    }
    const modals = ["editProfileModal", "vetHelpModal"];
    modals.forEach((modalId) => {
      const modal = document.getElementById(modalId);
      const modalContent = document.getElementById(modalId + "Content");
      if (modal && !modal.classList.contains("hidden")) {
        console.log(`Debug: Closing modal ${modalId} on Escape`);
        if (modalContent) {
          modalContent.classList.remove("scale-100", "opacity-100");
          modalContent.classList.add("scale-95", "opacity-0");
        }
        setTimeout(() => {
          modal.classList.add("hidden");
          document.body.classList.remove("overflow-hidden");
        }, 200);
      }
    });
  }
});

// AJAX form submission for edit profile
document.addEventListener("DOMContentLoaded", () => {
  console.log("Debug: DOMContentLoaded event fired");
  const form = document.querySelector("#editProfileForm");
  console.log("Debug: editProfileForm", form ? "found" : "not found");

  if (form) {
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      console.log("Debug: Form submitted");
      const formData = new FormData(form);
      console.log("Debug: Form data", Object.fromEntries(formData));

      fetch("/Pet-Track/functions/profile-handler.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => {
          console.log("Debug: Fetch response status", response.status);
          if (!response.ok) {
            throw new Error(`HTTP error: ${response.status}`);
          }
          return response.text(); // Get as text first
        })
        .then((text) => {
          console.log("Debug: Raw response text", text);
          try {
            const data = JSON.parse(text); // Then try to parse as JSON
            console.log("Debug: AJAX response", data);
            if (data.success) {
              if (typeof Swal !== "undefined") {
                Swal.fire(
                  "Success",
                  "Profile updated successfully!",
                  "success"
                );
              } else {
                alert("Profile updated successfully!");
              }
              console.log("Debug: Profile update successful, closing modal");
              window.toggleModal("editProfileModal");

              // Optional: Reload page to reflect changes
              setTimeout(() => {
                window.location.reload();
              }, 1500);
            } else {
              if (typeof Swal !== "undefined") {
                Swal.fire(
                  "Error",
                  data.message || "Profile update failed.",
                  "error"
                );
              } else {
                alert(data.message || "Profile update failed.");
              }
              console.error("Debug: Profile update failed", data.message);
            }
          } catch (parseError) {
            console.error("Debug: JSON parse error", parseError);
            console.error("Debug: Response was not JSON:", text);
            if (typeof Swal !== "undefined") {
              Swal.fire(
                "Error",
                "Server returned an invalid response. Check console for details.",
                "error"
              );
            } else {
              alert(
                "Server returned an invalid response. Check console for details."
              );
            }
          }
        })
        .catch((error) => {
          console.error("Debug: AJAX error", error.message);
          if (typeof Swal !== "undefined") {
            Swal.fire("Error", "An error occurred: " + error.message, "error");
          } else {
            alert("An error occurred: " + error.message);
          }
        });
    });
  } else {
    console.error("Error: Form #editProfileForm not found on DOMContentLoaded");
  }
});

// Additional modal close functionality for backdrop clicks
document.addEventListener("click", (e) => {
  const modals = ["editProfileModal", "vetHelpModal"];
  modals.forEach((modalId) => {
    const modal = document.getElementById(modalId);
    if (modal && e.target === modal && !modal.classList.contains("hidden")) {
      console.log(`Debug: Backdrop clicked, closing modal ${modalId}`);
      window.toggleModal(modalId);
    }
  });
});

console.log(
  "Debug: toggleModal function",
  typeof window.toggleModal === "function"
    ? "defined globally"
    : "not defined globally"
);
