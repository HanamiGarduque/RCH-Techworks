// File: /js/admin_toast.js

// Ensure the toast container exists
if (!document.getElementById("toast-container")) {
  const container = document.createElement("div");
  container.id = "toast-container";
  container.style = `position: fixed; 
        top: 20px; 
        right: 20px; 
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 10px;
    `;
  document.body.appendChild(container);
}

// Function to show a toast notification
function showToast({ title, message, link }) {
  const container = document.getElementById("toast-container");
  if (!container) return;

  const toast = document.createElement("div");
  toast.className = "admin-toast";
  toast.innerHTML = `<strong>${title}</strong><br>${message}`;
  toast.onclick = () => {
    if (link) window.location.href = link;
  };

  container.appendChild(toast);

  // Animate slide in
  requestAnimationFrame(() => {
    toast.style.transform = "translateX(0)";
    toast.style.opacity = "1";
  });

  // Remove after 5 seconds
  setTimeout(() => {
    toast.style.transform = "translateX(100%)";
    toast.style.opacity = "0";
    toast.addEventListener("transitionend", () => toast.remove());
  }, 5000);
}

// Polling for new notifications
function pollNotifications() {
  fetch("/notifications/check_new_notifications.php")
    .then((res) => res.json())
    .then((data) => {
      if (data.length) {
        data.forEach((notif) => {
          showToast(notif);
          fetch(`/notifications/mark_read.php?id=${notif.id}`);
        });
      }
    })
    .catch((err) => console.error("Notification polling error:", err));
}

// Start polling
pollNotifications();
setInterval(pollNotifications, 10000); // every 10 seconds

// CSS for toast animations and stacking
const style = document.createElement("style");
style.innerHTML = `#toast-container .admin-toast {
    background: #2563EB;
    color: white;
    padding: 15px 20px;
    border-radius: 6px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    cursor: pointer;
    min-width: 250px;
    font-family: Arial, sans-serif;
    transform: translateX(100%);
    opacity: 0;
    transition: transform 0.5s ease, opacity 0.5s ease;
}`;
document.head.appendChild(style);
