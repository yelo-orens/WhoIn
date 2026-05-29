/* ============================================
   WhoIn — Main Application Entry Point
   ============================================ */

document.addEventListener('DOMContentLoaded', () => {
  // Init systems
  ToastManager.init();
  initNavigation();
  initScrollAnimations();
  initEnterKeySupport();

  // Load active sessions
  loadActiveSessions();

  // Auto-refresh every 30 seconds (no hard reload)
  setInterval(loadActiveSessions, 30000);

  // Animate hero elements on load
  const heroElements = document.querySelectorAll('.hero .animate-on-scroll');
  heroElements.forEach((el, i) => {
    setTimeout(() => {
      el.classList.add('visible');
    }, 200 + i * 150);
  });
});
